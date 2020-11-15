/*
 * Copyright (c) 2015
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */

/* global Handlebars */

(function(OC) {
	var TEMPLATE =
		'<input class="systemTagsInputField" type="hidden" name="tags" value=""/>';

	var RESULT_TEMPLATE =
		'<span class="systemtags-item{{#if isNew}} new-item{{/if}}" data-id="{{id}}">' +
		'    <span class="checkmark icon icon-checkmark"></span>' +
		'{{#if isAdmin}}' +
		'    <span class="label">{{{tagMarkup}}}</span>' +
		'{{else}}' +
		'    <span class="label">{{name}}</span>' +
		'{{/if}}' +
		'{{#allowActions}}' +
		'    <span class="systemtags-actions">' +
		'        <a href="#" class="rename icon icon-rename" title="{{renameTooltip}}"></a>' +
		'    </span>' +
		'{{/allowActions}}' +
		'</span>';

	var SELECTION_TEMPLATE =
		'{{#if isAdmin}}' +
		'    <span class="label">{{{tagMarkup}}}</span>' +
		'{{else}}' +
		'    <span class="label">{{name}}</span>' +
		'{{/if}}';

	var RENAME_FORM_TEMPLATE =
		'<form class="systemtags-rename-form">' +
		'    <label class="hidden-visually" for="{{cid}}-rename-input">{{renameLabel}}</label>' +
		'    <input id="{{cid}}-rename-input" type="text" value="{{name}}">' +
		'    <a href="#" class="delete icon icon-delete" title="{{deleteTooltip}}"></a>' +
		'</form>';

	/**
	 * @class OC.SystemTags.SystemTagsInputField
	 * @classdesc
	 *
	 * Displays a file's system tags
	 *
	 */
	var SystemTagsInputField = OC.Backbone.View.extend(
		/** @lends OC.SystemTags.SystemTagsInputField.prototype */ {

		_rendered: false,

		_newTag: null,

		className: 'systemTagsInputFieldContainer',

		template: function(data) {
			if (!this._template) {
				this._template = Handlebars.compile(TEMPLATE);
			}
			return this._template(data);
		},

		/**
		 * Creates a new SystemTagsInputField
		 *
		 * @param {Object} [options]
		 * @param {string} [options.objectType=files] object type for which tags are assigned to
		 * @param {bool} [options.multiple=false] whether to allow selecting multiple tags
		 * @param {bool} [options.allowActions=true] whether tags can be renamed/delete within the dropdown
		 * @param {bool} [options.allowCreate=true] whether new tags can be created
		 * @param {bool} [options.isAdmin=true] whether the user is an administrator
		 * @param {Function} options.initSelection function to convert selection to data
		 */
		initialize: function(options) {
			options = options || {};

			this._multiple = !!options.multiple;
			this._allowActions = _.isUndefined(options.allowActions) || !!options.allowActions;
			this._allowCreate = _.isUndefined(options.allowCreate) || !!options.allowCreate;
			this._isAdmin = !!options.isAdmin;

			if (_.isFunction(options.initSelection)) {
				this._initSelection = options.initSelection;
			}

			this.collection = options.collection || OC.SystemTags.collection;

			var self = this;
			this.collection.on('change:name remove', function() {
				// refresh selection
				_.defer(self._refreshSelection);
			});

			_.bindAll(
				this,
				'_refreshSelection',
				'_onClickRenameTag',
				'_onClickDeleteTag',
				'_onSelectTag',
				'_onDeselectTag',
				'_onSubmitRenameTag'
			);
		},

		/**
		 * Refreshes the selection, triggering a call to
		 * select2's initSelection
		 */
		_refreshSelection: function() {
			this.$tagsField.select2('val', this.$tagsField.val());
		},

		/**
		 * Event handler whenever the user clicked the "rename" action.
		 * This will display the rename field.
		 */
		_onClickRenameTag: function(ev) {
			var $item = $(ev.target).closest('.systemtags-item');
			var tagId = $item.attr('data-id');
			var tagModel = this.collection.get(tagId);
			if (!this._renameFormTemplate) {
				this._renameFormTemplate = Handlebars.compile(RENAME_FORM_TEMPLATE);
			}

			var oldName = tagModel.get('name');
			var $renameForm = $(this._renameFormTemplate({
				cid: this.cid,
				name: oldName,
				deleteTooltip: t('core', 'Delete'),
				renameLabel: t('core', 'Rename')
			}));
			$item.find('.label').after($renameForm);
			$item.find('.label, .systemtags-actions').addClass('hidden');
			$item.closest('.select2-result').addClass('has-form');

			$renameForm.find('[title]').tooltip({
				placement: 'bottom',
				container: 'body'
			});
			$renameForm.find('input').focus().selectRange(0, oldName.length);
			return false;
		},

		/**
		 * Event handler whenever the rename form has been submitted after
		 * the user entered a new tag name.
		 * This will submit the change to the server. 
		 *
		 * @param {Object} ev event
		 */
		_onSubmitRenameTag: function(ev) {
			ev.preventDefault();
			var $form = $(ev.target);
			var $item = $form.closest('.systemtags-item');
			var tagId = $item.attr('data-id');
			var tagModel = this.collection.get(tagId);
			var newName = $(ev.target).find('input').val().trim();
			if (newName && newName !== tagModel.get('name')) {
				tagModel.save({'name': newName});
				// TODO: spinner, and only change text after finished saving
				$item.find('.label').text(newName);
			}
			$item.find('.label, .systemtags-actions').removeClass('hidden');
			$form.remove();
			$item.closest('.select2-result').removeClass('has-form');
		},

		/**
		 * Event handler whenever a tag must be deleted
		 *
		 * @param {Object} ev event
		 */
		_onClickDeleteTag: function(ev) {
			var $item = $(ev.target).closest('.systemtags-item');
			var tagId = $item.attr('data-id');
			this.collection.get(tagId).destroy();
			$item.closest('.select2-result').remove();
			// TODO: spinner
			return false;
		},

		_addToSelect2Selection: function(selection) {
			var data = this.$tagsField.select2('data');
			data.push(selection);
			this.$tagsField.select2('data', data);
		},

		/**
		 * Event handler whenever a tag is selected.
		 * Also called whenever tag creation is requested through the dummy tag object.
		 *
		 * @param {Object} e event
		 */
		_onSelectTag: function(e) {
			var self = this;
			var tag;
			if (e.object && e.object.isNew) {
				// newly created tag, check if existing
				// create a new tag
				tag = this.collection.create({
					name: e.object.name.trim(),
					userVisible: true,
					userAssignable: true,
					userEditable: true,
					canAssign: true
				}, {
					success: function(model) {
						self._addToSelect2Selection(model.toJSON());
						self.trigger('select', model);
					},
					error: function(model, xhr) {
						if (xhr.status === 409) {
							// re-fetch collection to get the missing tag
							self.collection.reset();
							self.collection.fetch({
								success: function(collection) {
									// find the tag in the collection
									var model = collection.where({name: e.object.name.trim(), userVisible: true, userAssignable: true});
									if (model.length) {
										model = model[0];
										// the tag already exists or was already assigned,
										// add it to the list anyway
										self._addToSelect2Selection(model.toJSON());
										self.trigger('select', model);
									}
								}
							});
						}
					}
				});
				this.$tagsField.select2('close');
				e.preventDefault();
				return false;
			} else {
				tag = this.collection.get(e.object.id);
			}
			this._newTag = null;
			this.trigger('select', tag);
		},

		/**
		 * Event handler whenever a tag gets deselected.
		 *
		 * @param {Object} e event
		 */
		_onDeselectTag: function(e) {
			this.trigger('deselect', e.choice.id);
		},

		/**
		 * Autocomplete function for dropdown results
		 *
		 * @param {Object} query select2 query object
		 */
		_queryTagsAutocomplete: function(query) {
			var self = this;
			this.collection.fetch({
				success: function(collection) {
					var tagModels = collection.filterByName(query.term.trim());
					if (!self._isAdmin) {
						tagModels = _.filter(tagModels, function(tagModel) {
							return tagModel.get('canAssign');
						});
					}
					query.callback({
						results: _.invoke(tagModels, 'toJSON')
					});
				}
			});
		},

		_preventDefault: function(e) {
			e.stopPropagation();
		},

		/**
		 * Returns true if tag is static tag else false
		 *
		 * @param data
		 * @returns {boolean}
		 */
		_isStaticTag: function(data) {
			return data.userEditable === false && data.userAssignable === true;
		},

		/**
		 * Formats a single dropdown result
		 *
		 * @param {Object} data data to format
		 * @return {string} HTML markup
		 */
		_formatDropDownResult: function(data) {
			if (!this._resultTemplate) {
				this._resultTemplate = Handlebars.compile(RESULT_TEMPLATE);
			}

			/**
			 * Static tags are shown if the user belongs to group which is
			 * whitelisted in the tag. Else the tag is not seen. If the tag
			 * is visible, then no edit options are availbe to users. Admin user
			 * is the only exception here. Admin user can edit, delete, assign or
			 * unassign the tag.
			 *
			 */
			this._allowActions = true;
			if (data.editableInGroup === false && this._isStaticTag(data)) {
				//No need to show the static tag as it is not viewable for the user
				return;
			}
			if (this._isStaticTag(data)) {
				//Show the name of the static tag, rename and delete actions are forbidden for the user
				this._allowActions = false;
			}

			return this._resultTemplate(_.extend({
				renameTooltip: t('core', 'Rename'),
				allowActions: this._allowActions,
				tagMarkup: this._isAdmin ? OC.SystemTags.getDescriptiveTag(data)[0].innerHTML : null,
				isAdmin: this._isAdmin
			}, data));
		},

		/**
		 * Formats a single selection item
		 *
		 * @param {Object} data data to format
		 * @return {string} HTML markup
		 */
		_formatSelection: function(data) {
			if (!this._selectionTemplate) {
				this._selectionTemplate = Handlebars.compile(SELECTION_TEMPLATE);
			}
			return this._selectionTemplate(_.extend({
				tagMarkup: this._isAdmin ? OC.SystemTags.getDescriptiveTag(data)[0].innerHTML : null,
				isAdmin: this._isAdmin
			}, data));
		},

		/**
		 * Create new dummy choice for select2 when the user
		 * types an arbitrary string
		 *
		 * @param {string} term entered term
		 * @return {Object} dummy tag
		 */
		_createSearchChoice: function(term) {
			term = term.trim();
			if (this.collection.filter(function(entry) {
					return entry.get('name') === term;
				}).length) {
				return;
			}
			if (!this._newTag) {
				this._newTag = {
					id: -1,
					name: term,
					userAssignable: true,
					userVisible: true,
					canAssign: true,
					isNew: true
				};
			} else {
				this._newTag.name = term;
			}

			return this._newTag;
		},

		_initSelection: function(element, callback) {
			var self = this;
			var ids = $(element).val().split(',');

			function modelToSelection(model) {
				var data = model.toJSON();
				if (!self._isAdmin && !data.canAssign) {
					// lock static tags for non-admins
					data.locked = true;
				}
				return data;
			}

			function findSelectedObjects(ids) {
				var selectedModels = self.collection.filter(function(model) {
					return ids.indexOf(model.id) >= 0 && (self._isAdmin || model.get('userVisible'));
				});
				return _.map(selectedModels, modelToSelection);
			}

			this.collection.fetch({
				success: function() {
					callback(findSelectedObjects(ids));
				}
			});
		},

		/**
		 * Renders this details view
		 */
		render: function() {
			var self = this;
			this.$el.html(this.template());

			this.$el.find('[title]').tooltip({placement: 'bottom'});
			this.$tagsField = this.$el.find('[name=tags]');
			this.$tagsField.select2({
				placeholder: t('core', 'Collaborative tags'),
				containerCssClass: 'systemtags-select2-container',
				dropdownCssClass: 'systemtags-select2-dropdown',
				closeOnSelect: false,
				allowClear: false,
				multiple: this._multiple,
				toggleSelect: this._multiple,
				query: _.bind(this._queryTagsAutocomplete, this),
				id: function(tag) {
					return tag.id;
				},
				initSelection: _.bind(this._initSelection, this),
				formatResult: _.bind(this._formatDropDownResult, this),
				formatSelection: _.bind(this._formatSelection, this),
				createSearchChoice: this._allowCreate ? _.bind(this._createSearchChoice, this) : undefined,
				sortResults: function(results) {
					var selectedItems = _.pluck(self.$tagsField.select2('data'), 'id');
					results.sort(function(a, b) {
						var aSelected = selectedItems.indexOf(a.id) >= 0;
						var bSelected = selectedItems.indexOf(b.id) >= 0;
						if (aSelected === bSelected) {
							return OC.Util.naturalSortCompare(a.name, b.name);
						}
						if (aSelected && !bSelected) {
							return -1;
						}
						return 1;
					});
					return results;
				}
			})
				.on('select2-selecting', this._onSelectTag)
				.on('select2-removing', this._onDeselectTag);

			var $dropDown = this.$tagsField.select2('dropdown');
			// register events for inside the dropdown
			$dropDown.on('mouseup', '.rename', this._onClickRenameTag);
			$dropDown.on('mouseup', '.delete', this._onClickDeleteTag);
			$dropDown.on('mouseup', '.select2-result-selectable.has-form', this._preventDefault);
			$dropDown.on('submit', '.systemtags-rename-form', this._onSubmitRenameTag);

			this.delegateEvents();
		},

		remove: function() {
			if (this.$tagsField) {
				this.$tagsField.select2('destroy');
			}
		},

		getValues: function() {
			this.$tagsField.select2('val');
		},

		setValues: function(values) {
			this.$tagsField.select2('val', values);
		},

		setData: function(data) {
			this.$tagsField.select2('data', data);
		}
	});

	OC.SystemTags = OC.SystemTags || {};
	OC.SystemTags.SystemTagsInputField = SystemTagsInputField;

})(OC);

