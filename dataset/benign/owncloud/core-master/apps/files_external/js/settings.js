/*
 * Copyright (c) 2014
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */

(function(){

// TODO: move to a separate file
var MOUNT_OPTIONS_DROPDOWN_TEMPLATE =
	'<div class="drop dropdown mountOptionsDropdown">' +
	// FIXME: options are hard-coded for now
	'	<div class="optionRow">' +
	'		<input id="mountOptionsEncrypt" name="encrypt" type="checkbox" value="true" checked="checked"/>' +
	'		<label for="mountOptionsEncrypt">{{t "files_external" "Enable encryption"}}</label>' +
	'	</div>' +
	'	<div class="optionRow">' +
	'		<input id="mountOptionsReadonly" name="read_only" type="checkbox" value="true"/>' +
	'		<label for="mountOptionsReadonly">{{t "files_external" "Set read-only"}}</label>' +
	'	</div>' +
	'	<div class="optionRow">' +
	'		<input id="mountOptionsPreviews" name="previews" type="checkbox" value="true" checked="checked"/>' +
	'		<label for="mountOptionsPreviews">{{t "files_external" "Enable previews"}}</label>' +
	'	</div>' +
	'	<div class="optionRow">' +
	'		<input id="mountOptionsSharing" name="enable_sharing" type="checkbox" value="true"/>' +
	'		<label for="mountOptionsSharing">{{t "files_external" "Enable sharing"}}</label>' +
	'	</div>' +
	'	<div class="optionRow">' +
	'		<label for="mountOptionsFilesystemCheck">{{t "files_external" "Check for changes"}}</label>' +
	'		<select id="mountOptionsFilesystemCheck" name="filesystem_check_changes" data-type="int">' +
	'			<option value="0">{{t "files_external" "Never"}}</option>' +
	'			<option value="1" selected="selected">{{t "files_external" "Once every direct access"}}</option>' +
	'		</select>' +
	'	</div>' +
	'	<div class="optionRow">' +
	'		<input id="mountOptionsEncoding" name="encoding_compatibility" type="checkbox" value="true"/>' +
	'		<label for="mountOptionsEncoding">{{mountOptionsEncodingLabel}}</label>' +
	'	</div>' +
	'</div>';

/**
 * Returns the selection of applicable users in the given configuration row
 *
 * @param $row configuration row
 * @return array array of user names
 */
function getSelection($row) {
	var values = $row.find('.applicableUsers').select2('val');
	if (!values || values.length === 0) {
		values = [];
	}
	return values;
}

function highlightBorder($element, highlight) {
	$element.toggleClass('warning-input', highlight);
	return highlight;
}

function isInputValid($input) {
	var optional = $input.hasClass('optional');
	switch ($input.attr('type')) {
		case 'text':
		case 'password':
			if ($input.val() === '' && !optional) {
				return false;
			}
			break;
	}
	return true;
}

function highlightInput($input) {
	switch ($input.attr('type')) {
		case 'text':
		case 'password':
			return highlightBorder($input, !isInputValid($input));
	}
}

/**
 * Initialize select2 plugin on the given elements
 *
 * @param {Array<Object>} array of jQuery elements
 * @param {int} userListLimit page size for result list
 */
function addSelect2 ($elements, userListLimit) {
	if (!$elements.length) {
		return;
	}
	$elements.select2({
		placeholder: t('files_external', 'All users. Type to select user or group.'),
		allowClear: true,
		multiple: true,
		//minimumInputLength: 1,
		ajax: {
			url: OC.generateUrl('apps/files_external/applicable'),
			dataType: 'json',
			quietMillis: 100,
			data: function (term, page) { // page is the one-based page number tracked by Select2
				return {
					pattern: term, //search term
					limit: userListLimit, // page size
					offset: userListLimit*(page-1) // page number starts with 0
				};
			},
			results: function (data) {
				if (data.status === 'success') {

					var results = [];
					var userCount = 0; // users is an object

					// add groups
					$.each(data.groups, function(i, group) {
						results.push({name:group+'(group)', displayname:group, type:'group' });
					});
					// add users
					$.each(data.users, function(id, user) {
						userCount++;
						results.push({name:id, displayname:user, type:'user' });
					});


					var more = (userCount >= userListLimit) || (data.groups.length >= userListLimit);
					return {results: results, more: more};
				} else {
					//FIXME add error handling
				}
			}
		},
		initSelection: function(element, callback) {
			var users = {};
			users['users'] = [];
			var toSplit = element.val().split(",");
			for (var i = 0; i < toSplit.length; i++) {
				users['users'].push(toSplit[i]);
			}

			$.ajax(OC.generateUrl('displaynames'), {
				type: 'POST',
				contentType: 'application/json',
				data: JSON.stringify(users),
				dataType: 'json'
			}).done(function(data) {
				var results = [];
				if (data.status === 'success') {
					$.each(data.users, function(user, displayname) {
						if (displayname !== false) {
							results.push({name:user, displayname:displayname, type:'user'});
						}
					});
					callback(results);
				} else {
					//FIXME add error handling
				}
			});
		},
		id: function(element) {
			return element.name;
		},
		formatResult: function (element) {
			var $result = $('<span><div class="avatardiv"/><span>'+escapeHTML(element.displayname)+'</span></span>');
			var $div = $result.find('.avatardiv')
				.attr('data-type', element.type)
				.attr('data-name', element.name)
				.attr('data-displayname', element.displayname);
			if (element.type === 'group') {
				var url = OC.imagePath('core','places/contacts-dark'); // TODO better group icon
				$div.html('<img width="32" height="32" src="'+url+'">');
			}
			return $result.get(0).outerHTML;
		},
		formatSelection: function (element) {
			if (element.type === 'group') {
				return '<span title="'+escapeHTML(element.name)+'" class="group">'+escapeHTML(element.displayname+' '+t('files_external', '(group)'))+'</span>';
			} else {
				return '<span title="'+escapeHTML(element.name)+'" class="user">'+escapeHTML(element.displayname)+'</span>';
			}
		},
		escapeMarkup: function (m) { return m; } // we escape the markup in formatResult and formatSelection
	}).on('select2-loaded', function() {
		$.each($('.avatardiv'), function(i, div) {
			var $div = $(div);
			if ($div.data('type') === 'user') {
				$div.avatar($div.data('name'),32);
			}
		});
	});
}

/**
 * @class OCA.External.Settings.StorageConfig
 *
 * @classdesc External storage config
 */
var StorageConfig = function(id) {
	this.id = id;
	this.backendOptions = {};
};
// Keep this in sync with \OC_Mount_Config::STATUS_*
StorageConfig.Status = {
	IN_PROGRESS: -1,
	SUCCESS: 0,
	ERROR: 1,
	INDETERMINATE: 2
};
StorageConfig.Visibility = {
	NONE: 0,
	PERSONAL: 1,
	ADMIN: 2,
	DEFAULT: 3
};
/**
 * @memberof OCA.External.Settings
 */
StorageConfig.prototype = {
	_url: null,

	/**
	 * Storage id
	 *
	 * @type int
	 */
	id: null,

	/**
	 * Mount point
	 *
	 * @type string
	 */
	mountPoint: '',

	/**
	 * Backend
	 *
	 * @type string
	 */
	backend: null,

	/**
	 * Authentication mechanism
	 *
	 * @type string
	 */
	authMechanism: null,

	/**
	 * Backend-specific configuration
	 *
	 * @type Object.<string,object>
	 */
	backendOptions: null,

	/**
	 * Mount-specific options
	 *
	 * @type Object.<string,object>
	 */
	mountOptions: null,

	/**
	 * Creates or saves the storage.
	 *
	 * @param {Function} [options.success] success callback, receives result as argument
	 * @param {Function} [options.error] error callback
	 */
	save: function(options) {
		var self = this;
		var url = OC.generateUrl(this._url);
		var method = 'POST';
		if (_.isNumber(this.id)) {
			method = 'PUT';
			url = OC.generateUrl(this._url + '/{id}', {id: this.id});
		}

		$.ajax({
			type: method,
			url: url,
			contentType: 'application/json',
			data: JSON.stringify(this.getData()),
			success: function(result) {
				self.id = result.id;
				if (_.isFunction(options.success)) {
					options.success(result);
				}
			},
			error: options.error
		});
	},

	/**
	 * Returns the data from this object
	 *
	 * @return {Array} JSON array of the data
	 */
	getData: function() {
		var data = {
			mountPoint: this.mountPoint,
			backend: this.backend,
			authMechanism: this.authMechanism,
			backendOptions: this.backendOptions,
			testOnly: true
		};
		if (this.id) {
			data.id = this.id;
		}
		if (this.mountOptions) {
			data.mountOptions = this.mountOptions;
		}
		return data;
	},

	/**
	 * Recheck the storage
	 *
	 * @param {Function} [options.success] success callback, receives result as argument
	 * @param {Function} [options.error] error callback
	 */
	recheck: function(options) {
		if (!_.isNumber(this.id)) {
			if (_.isFunction(options.error)) {
				options.error();
			}
			return;
		}
		$.ajax({
			type: 'GET',
			url: OC.generateUrl(this._url + '/{id}', {id: this.id}),
			data: {'testOnly': true},
			success: options.success,
			error: options.error
		});
	},

	/**
	 * Deletes the storage
	 *
	 * @param {Function} [options.success] success callback
	 * @param {Function} [options.error] error callback
	 */
	destroy: function(options) {
		if (!_.isNumber(this.id)) {
			// the storage hasn't even been created => success
			if (_.isFunction(options.success)) {
				options.success();
			}
			return;
		}
		$.ajax({
			type: 'DELETE',
			url: OC.generateUrl(this._url + '/{id}', {id: this.id}),
			success: options.success,
			error: options.error
		});
	},

	/**
	 * Validate this model
	 *
	 * @return {boolean} false if errors exist, true otherwise
	 */
	validate: function() {
		if (this.mountPoint === '') {
			return false;
		}
		if (!this.backend) {
			return false;
		}
		if (this.errors) {
			return false;
		}
		return true;
	}
};

/**
 * @class OCA.External.Settings.GlobalStorageConfig
 * @augments OCA.External.Settings.StorageConfig
 *
 * @classdesc Global external storage config
 */
var GlobalStorageConfig = function(id) {
	this.id = id;
	this.applicableUsers = [];
	this.applicableGroups = [];
};
/**
 * @memberOf OCA.External.Settings
 */
GlobalStorageConfig.prototype = _.extend({}, StorageConfig.prototype,
	/** @lends OCA.External.Settings.GlobalStorageConfig.prototype */ {
	_url: 'apps/files_external/globalstorages',

	/**
	 * Applicable users
	 *
	 * @type Array.<string>
	 */
	applicableUsers: null,

	/**
	 * Applicable groups
	 *
	 * @type Array.<string>
	 */
	applicableGroups: null,

	/**
	 * Storage priority
	 *
	 * @type int
	 */
	priority: null,

	/**
	 * Returns the data from this object
	 *
	 * @return {Array} JSON array of the data
	 */
	getData: function() {
		var data = StorageConfig.prototype.getData.apply(this, arguments);
		return _.extend(data, {
			applicableUsers: this.applicableUsers,
			applicableGroups: this.applicableGroups,
			priority: this.priority,
		});
	}
});

/**
 * @class OCA.External.Settings.UserStorageConfig
 * @augments OCA.External.Settings.StorageConfig
 *
 * @classdesc User external storage config
 */
var UserStorageConfig = function(id) {
	this.id = id;
};
UserStorageConfig.prototype = _.extend({}, StorageConfig.prototype,
	/** @lends OCA.External.Settings.UserStorageConfig.prototype */ {
	_url: 'apps/files_external/userstorages'
});

/**
 * @class OCA.External.Settings.UserGlobalStorageConfig
 * @augments OCA.External.Settings.StorageConfig
 *
 * @classdesc User external storage config
 */
var UserGlobalStorageConfig = function (id) {
	this.id = id;
};
UserGlobalStorageConfig.prototype = _.extend({}, StorageConfig.prototype,
	/** @lends OCA.External.Settings.UserStorageConfig.prototype */ {

	_url: 'apps/files_external/userglobalstorages'
});

/**
 * @class OCA.External.Settings.MountOptionsDropdown
 *
 * @classdesc Dropdown for mount options
 *
 * @param {Object} $container container DOM object
 */
var MountOptionsDropdown = function() {
};
/**
 * @memberof OCA.External.Settings
 */
MountOptionsDropdown.prototype = {
	/**
	 * Dropdown element
	 *
	 * @var Object
	 */
	$el: null,

	/**
	 * Show dropdown
	 *
	 * @param {Object} $container container
	 * @param {Object} mountOptions mount options
	 * @param {Array} visibleOptions enabled mount options
	 */
	show: function($container, mountOptions, visibleOptions) {
		if (MountOptionsDropdown._last) {
			MountOptionsDropdown._last.hide();
		}

		var template = MountOptionsDropdown._template;
		if (!template) {
			template = Handlebars.compile(MOUNT_OPTIONS_DROPDOWN_TEMPLATE);
			MountOptionsDropdown._template = template;
		}

		var $el = $(template({
			mountOptionsEncodingLabel: t('files_external', 'Compatibility with Mac NFD encoding (slow)'),
		}));
		this.$el = $el;

		this.setOptions(mountOptions, visibleOptions);

		this.$el.appendTo($container);
		MountOptionsDropdown._last = this;

		this.$el.trigger('show');
	},

	hide: function() {
		if (this.$el) {
			this.$el.trigger('hide');
			this.$el.remove();
			this.$el = null;
			MountOptionsDropdown._last = null;
		}
	},

	/**
	 * Returns the mount options from the dropdown controls
	 *
	 * @return {Object} options mount options
	 */
	getOptions: function() {
		var options = {};

		this.$el.find('input, select').each(function() {
			var $this = $(this);
			var key = $this.attr('name');
			var value = null;
			if ($this.attr('type') === 'checkbox') {
				value = $this.prop('checked');
			} else {
				value = $this.val();
			}
			if ($this.attr('data-type') === 'int') {
				value = parseInt(value, 10);
			}
			options[key] = value;
		});
		return options;
	},

	/**
	 * Sets the mount options to the dropdown controls
	 *
	 * @param {Object} options mount options
	 * @param {Array} visibleOptions enabled mount options
	 */
	setOptions: function(options, visibleOptions) {
		var $el = this.$el;
		_.each(options, function(value, key) {
			var $optionEl = $el.find('input, select').filterAttr('name', key);
			if ($optionEl.attr('type') === 'checkbox') {
				if (_.isString(value)) {
					value = (value === 'true');
				}
				$optionEl.prop('checked', !!value);
			} else {
				$optionEl.val(value);
			}
		});
		$el.find('.optionRow').each(function(i, row){
			var $row = $(row);
			var optionId = $row.find('input, select').attr('name');
			if (visibleOptions.indexOf(optionId) === -1) {
				$row.hide();
			} else {
				$row.show();
			}
		});
	}
};

/**
 * @class OCA.External.Settings.MountConfigListView
 *
 * @classdesc Mount configuration list view
 *
 * @param {Object} $el DOM object containing the list
 * @param {Object} [options]
 * @param {int} [options.userListLimit] page size in applicable users dropdown
 */
var MountConfigListView = function($el, options) {
	this.initialize($el, options);
};

MountConfigListView.ParameterFlags = {
	OPTIONAL: 1,
	USER_PROVIDED: 2
};

MountConfigListView.ParameterTypes = {
	TEXT: 0,
	BOOLEAN: 1,
	PASSWORD: 2,
	HIDDEN: 3
};

/**
 * @memberOf OCA.External.Settings
 */
MountConfigListView.prototype = _.extend({

	/**
	 * jQuery element containing the config list
	 *
	 * @type Object
	 */
	$el: null,

	/**
	 * Storage config class
	 *
	 * @type Class
	 */
	_storageConfigClass: null,

	/**
	 * Flag whether the list is about user storage configs (true)
	 * or global storage configs (false)
	 *
	 * @type bool
	 */
	_isPersonal: false,

	/**
	 * Page size in applicable users dropdown
	 *
	 * @type int
	 */
	_userListLimit: 30,

	/**
	 * List of supported backends
	 *
	 * @type Object.<string,Object>
	 */
	_allBackends: null,

	/**
	 * List of all supported authentication mechanisms
	 *
	 * @type Object.<string,Object>
	 */
	_allAuthMechanisms: null,

	_encryptionEnabled: false,

	/**
	 * @param {Object} $el DOM object containing the list
	 * @param {Object} [options]
	 * @param {int} [options.userListLimit] page size in applicable users dropdown
	 */
	initialize: function($el, options) {
		this.$el = $el;
		this._isPersonal = ($el.data('admin') !== true);
		if (this._isPersonal) {
			this._storageConfigClass = OCA.External.Settings.UserStorageConfig;
		} else {
			this._storageConfigClass = OCA.External.Settings.GlobalStorageConfig;
		}

		if (options && !_.isUndefined(options.userListLimit)) {
			this._userListLimit = options.userListLimit;
		}

		this._encryptionEnabled = options.encryptionEnabled;
		this._allowUserMountSharing = options.allowUserMountSharing;

		// read the backend config that was carefully crammed
		// into the data-configurations attribute of the select
		this._allBackends = this.$el.find('.selectBackend').data('configurations');
		this._allAuthMechanisms = this.$el.find('#addMountPoint .authentication').data('mechanisms');
	},

	/**
	 * Custom JS event handlers
	 * Trigger callback for all existing configurations
	 */
	whenSelectBackend: function(callback) {
		this.$el.find('tbody tr:not(#addMountPoint)').each(function(i, tr) {
			var backend = $(tr).find('.backend').data('identifier');
			callback($(tr), backend);
		});
		this.on('selectBackend', callback);
	},
	whenSelectAuthMechanism: function(callback) {
		var self = this;
		this.$el.find('tbody tr:not(#addMountPoint)').each(function(i, tr) {
			var authMechanism = $(tr).find('.selectAuthMechanism').val();
			if (authMechanism !== undefined) {
				var onCompletion = jQuery.Deferred();
				callback($(tr), authMechanism, self._allAuthMechanisms[authMechanism]['scheme'], onCompletion);
				onCompletion.resolve();
			}
		});
		this.on('selectAuthMechanism', callback);
	},

	/**
	 * Initialize DOM event handlers
	 */
	_initEvents: function() {
		var self = this;

		var onChangeHandler = _.bind(this._onChange, this);
		//this.$el.on('input', 'td input', onChangeHandler);
		this.$el.on('keyup', 'td input', onChangeHandler);
		this.$el.on('paste', 'td input', onChangeHandler);
		this.$el.on('change', 'td input:checkbox', onChangeHandler);
		this.$el.on('change', '.applicable', onChangeHandler);

		this.$el.on('click', '.status>span', function() {
			self.recheckStorageConfig($(this).closest('tr'));
		});

		this.$el.on('click', 'td.remove>img', function() {
			self.deleteStorageConfig($(this).closest('tr'));
		});

		this.$el.on('click', 'td.mountOptionsToggle>img', function() {
			self._showMountOptionsDropdown($(this).closest('tr'));
		});

		this.$el.on('change', '.selectBackend', _.bind(this._onSelectBackend, this));
		this.$el.on('change', '.selectAuthMechanism', _.bind(this._onSelectAuthMechanism, this));
	},

	_onChange: function(event) {
		var self = this;
		var $target = $(event.target);
		if ($target.closest('.dropdown').length) {
			// ignore dropdown events
			return;
		}
		highlightInput($target);
		var $tr = $target.closest('tr');
		this.updateStatus($tr, null);

		var timer = $tr.data('save-timer');
		clearTimeout(timer);
		timer = setTimeout(function() {
			self.saveStorageConfig($tr, null, timer);
		}, 2000);
		$tr.data('save-timer', timer);
	},

	_onSelectBackend: function(event) {
		var $target = $(event.target);
		var $tr = $target.closest('tr');

		var storageConfig = new this._storageConfigClass();
		storageConfig.mountPoint = $tr.find('.mountPoint input').val();
		storageConfig.backend = $target.val();
		$tr.find('.mountPoint input').val('');

		var onCompletion = jQuery.Deferred();
		$tr = this.newStorage(storageConfig, onCompletion);
		onCompletion.resolve();

		$tr.find('td.configuration').children().not('[type=hidden]').first().focus();
		this.saveStorageConfig($tr);
	},

	_onSelectAuthMechanism: function(event) {
		var $target = $(event.target);
		var $tr = $target.closest('tr');
		var authMechanism = $target.val();

		var onCompletion = jQuery.Deferred();
		this.configureAuthMechanism($tr, authMechanism, onCompletion);
		onCompletion.resolve();

		this.saveStorageConfig($tr);
	},

	/**
	 * Execute the target callback when all the deferred objects have been
	 * finished, either resolved or rejected
	 *
	 * @param {Deferred[]} deferreds array with all the deferred objects to check
	 * this will usually be a list of ajax requests, but other deferred
	 * objects can be included
	 * @param {callback} callback the callback to be executed after all the
	 * deferred object have finished
	 */
	_executeCallbackWhenFinished: function(deferreds, callback) {
		var self = this;

		$.when.apply($, deferreds).always(function() {
			var pendingDeferreds = [];

			// some deferred objects can still be pending to be resolved
			// or rejected. Get all of those which are still pending so we can
			// make another call
			if (deferreds.length > 0) {
				for (i = 0 ; i < deferreds.length ; i++) {
					if (deferreds[i].state() === 'pending') {
						pendingDeferreds.push(deferreds[i]);
					}
				}
			}

			if (pendingDeferreds.length > 0) {
				self._executeCallbackWhenFinished(pendingDeferreds, callback);
			} else {
				callback();
			}
		});
	},

	/**
	 * Configure the storage config with a new authentication mechanism
	 *
	 * @param {jQuery} $tr config row
	 * @param {string} authMechanism
	 * @param {jQuery.Deferred} onCompletion
	 */
	configureAuthMechanism: function($tr, authMechanism, onCompletion) {
		var authMechanismConfiguration = this._allAuthMechanisms[authMechanism];
		var $td = $tr.find('td.configuration');
		$td.find('.auth-param').remove();

		$.each(authMechanismConfiguration['configuration'], _.partial(
			this.writeParameterInput, $td, _, _, ['auth-param']
		).bind(this));

		this.trigger('selectAuthMechanism',
			$tr, authMechanism, authMechanismConfiguration['scheme'], onCompletion
		);
	},

	/**
	 * Create a config row for a new storage
	 *
	 * @param {StorageConfig} storageConfig storage config to pull values from
	 * @param {jQuery.Deferred} onCompletion
	 * @return {jQuery} created row
	 */
	newStorage: function(storageConfig, onCompletion) {
		var mountPoint = storageConfig.mountPoint;
		var backend = this._allBackends[storageConfig.backend];
		var isInvalidAuth = false;

		if (!backend) {
			backend = {
				name: 'Unknown backend: ' + storageConfig.backend,
				invalid: true
			};
		}
		if (backend && storageConfig.authMechanism && !this._allAuthMechanisms[storageConfig.authMechanism]) {
			// mark invalid to prevent editing
			backend.invalid = true;
			isInvalidAuth = true;
		}

		// FIXME: Replace with a proper Handlebar template
		var $tr = this.$el.find('tr#addMountPoint');
		var $trCloned = $tr.clone();
		$trCloned.find('td.mountPoint input').removeAttr('disabled');
		this.$el.find('tbody').append($trCloned);

		$tr.data('storageConfig', storageConfig);
		$tr.show();
		$tr.find('td').last().attr('class', 'remove');
		$tr.find('td.mountOptionsToggle').removeClass('hidden');
		$tr.find('td').last().removeAttr('style');
		$tr.removeAttr('id');
		addSelect2($tr.find('.applicableUsers'), this._userListLimit);

		if (storageConfig.id) {
			$tr.data('id', storageConfig.id);
		}

		$tr.find('.backend').text(backend.name);
		if (mountPoint === '') {
			mountPoint = this._suggestMountPoint(backend.name);
		}
		$tr.find('.mountPoint input').val(mountPoint);
		$tr.addClass(backend.identifier);
		$tr.find('.backend').data('identifier', backend.identifier);

		if (backend.invalid || isInvalidAuth) {
			$tr.addClass('invalid');
			$tr.find('[name=mountPoint]').prop('disabled', true);
			$tr.find('.applicable,.mountOptionsToggle').empty();
			this.updateStatus($tr, false, 'Unknown backend: ' + backend.name);
			if (isInvalidAuth) {
				$tr.find('td.authentication').append('<span class="error-invalid">' +
					t('files_external', 'Unknown auth backend "{b}"', {b: storageConfig.authMechanism}) +
					'</span>'
				);
			}

			$tr.find('td.configuration').append('<span class="error-invalid">' +
				t('files_external', 'Please make sure that the app that provides this backend is installed and enabled') +
				'</span>'
			);

			return $tr;
		}

		var selectAuthMechanism = $('<select class="selectAuthMechanism"></select>');
		var neededVisibility = (this._isPersonal) ? StorageConfig.Visibility.PERSONAL : StorageConfig.Visibility.ADMIN;
		$.each(this._allAuthMechanisms, function(authIdentifier, authMechanism) {
			if (backend.authSchemes[authMechanism.scheme] && (authMechanism.visibility & neededVisibility)) {
				selectAuthMechanism.append(
					$('<option value="'+authMechanism.identifier+'" data-scheme="'+authMechanism.scheme+'">'+authMechanism.name+'</option>')
				);
			}
		});
		if (storageConfig.authMechanism) {
			selectAuthMechanism.val(storageConfig.authMechanism);
		} else {
			storageConfig.authMechanism = selectAuthMechanism.val();
		}
		$tr.find('td.authentication').append(selectAuthMechanism);

		var $td = $tr.find('td.configuration');
		$.each(backend.configuration, _.partial(this.writeParameterInput, $td).bind(this));

		this.trigger('selectBackend', $tr, backend.identifier, onCompletion);
		this.configureAuthMechanism($tr, storageConfig.authMechanism, onCompletion);

		if (storageConfig.backendOptions) {
			$td.find('input, select').each(function() {
				var input = $(this);
				var val = storageConfig.backendOptions[input.data('parameter')];
				if (val !== undefined) {
					if(input.is('input:checkbox')) {
						input.prop('checked', val);
					}
					input.val(storageConfig.backendOptions[input.data('parameter')]);
					highlightInput(input);
				}
			});
		}

		var applicable = [];
		if (storageConfig.applicableUsers) {
			applicable = applicable.concat(storageConfig.applicableUsers);
		}
		if (storageConfig.applicableGroups) {
			applicable = applicable.concat(
				_.map(storageConfig.applicableGroups, function(group) {
					return group+'(group)';
				})
			);
		}
		$tr.find('.applicableUsers').val(applicable).trigger('change');

		var priorityEl = $('<input type="hidden" class="priority" value="' + backend.priority + '" />');
		$tr.append(priorityEl);

		if (storageConfig.mountOptions) {
			$tr.find('input.mountOptions').val(JSON.stringify(storageConfig.mountOptions));
		} else {
			// FIXME default backend mount options
			$tr.find('input.mountOptions').val(JSON.stringify({
				'encrypt': true,
				'read_only': false,
				'previews': true,
				'enable_sharing': false,
				'filesystem_check_changes': 1,
				'encoding_compatibility': false
			}));
		}

		return $tr;
	},

	/**
	 * Load storages into config rows
	 */
	loadStorages: function() {
		var self = this;
		var ajaxRequests = [];

		if (this._isPersonal) {
			// load userglobal storages
			var fixedStoragesRequest = $.ajax({
				type: 'GET',
				url: OC.generateUrl('apps/files_external/userglobalstorages'),
				data: {'testOnly' : true},
				contentType: 'application/json',
				success: function(result) {
					var onCompletion = jQuery.Deferred();
					$.each(result, function(i, storageParams) {
						var storageConfig;
						var isUserGlobal = storageParams.type === 'system' && self._isPersonal;
						storageParams.mountPoint = storageParams.mountPoint.substr(1); // trim leading slash
						if (isUserGlobal) {
							storageConfig = new UserGlobalStorageConfig();
						} else {
							storageConfig = new self._storageConfigClass();
						}
						_.extend(storageConfig, storageParams);
						var $tr = self.newStorage(storageConfig, onCompletion);

						// userglobal storages must be at the top of the list
						$tr.detach();
						self.$el.prepend($tr);

						var $authentication = $tr.find('.authentication');
						$authentication.text($authentication.find('select option:selected').text());

						// disable any other inputs
						$tr.find('.mountOptionsToggle, .remove').empty();
						$tr.find('input:not(.user_provided), select:not(.user_provided)').attr('disabled', 'disabled');

						if (isUserGlobal) {
							$tr.find('.configuration').find(':not(.user_provided)').remove();
						} else {
							// userglobal storages do not expose configuration data
							$tr.find('.configuration').text(t('files_external', 'Admin defined'));
						}
					});
					onCompletion.resolve();
				}
			});
			ajaxRequests.push(fixedStoragesRequest);
		}

		var url = this._storageConfigClass.prototype._url;

		var changeableStoragesRequest = $.ajax({
			type: 'GET',
			url: OC.generateUrl(url),
			contentType: 'application/json',
			success: function(result) {
				var onCompletion = jQuery.Deferred();
				$.each(result, function(i, storageParams) {
					storageParams.mountPoint = storageParams.mountPoint.substr(1); // trim leading slash
					var storageConfig = new self._storageConfigClass();
					_.extend(storageConfig, storageParams);
					var $tr = self.newStorage(storageConfig, onCompletion);
					self.recheckStorageConfig($tr);
				});
				onCompletion.resolve();
			}
		});
		ajaxRequests.push(changeableStoragesRequest);

		this._executeCallbackWhenFinished(ajaxRequests, function() {
			$('#body-settings').trigger('mountConfigLoaded');
		});
	},

	/**
	 * @param {jQuery} $td
	 * @param {string} parameter
	 * @param {string} placeholder
	 * @param {Array} classes
	 * @return {jQuery} newly created input
	 */
	writeParameterInput: function($td, parameter, placeholder, classes) {
		var hasFlag = function(flag) {
			return (placeholder.flags & flag) === flag;
		};
		classes = $.isArray(classes) ? classes : [];
		classes.push('added');
		if (hasFlag(MountConfigListView.ParameterFlags.OPTIONAL)) {
			classes.push('optional');
		}

		if (hasFlag(MountConfigListView.ParameterFlags.USER_PROVIDED)) {
			if (this._isPersonal) {
				classes.push('user_provided');
			} else {
				return;
			}
		}

		var newElement;

		var trimmedPlaceholder = placeholder.value;
		if (placeholder.type === MountConfigListView.ParameterTypes.PASSWORD) {
			newElement = $('<input type="password" class="'+classes.join(' ')+'" data-parameter="'+parameter+'" placeholder="'+ trimmedPlaceholder+'" autocomplete="off" />');
		} else if (placeholder.type === MountConfigListView.ParameterTypes.BOOLEAN) {
			var checkboxId = _.uniqueId('checkbox_');
			newElement = $('<div><label><input type="checkbox" id="'+checkboxId+'" class="'+classes.join(' ')+'" data-parameter="'+parameter+'" />'+ trimmedPlaceholder+'</label></div>');
		} else if (placeholder.type === MountConfigListView.ParameterTypes.HIDDEN) {
			newElement = $('<input type="hidden" class="'+classes.join(' ')+'" data-parameter="'+parameter+'" />');
		} else {
			newElement = $('<input type="text" class="'+classes.join(' ')+'" data-parameter="'+parameter+'" placeholder="'+ trimmedPlaceholder+'" />');
		}
		highlightInput(newElement);
		$td.append(newElement);
		return newElement;
	},

	/**
	 * Gets the storage model from the given row
	 *
	 * @param $tr row element
	 * @return {OCA.External.StorageConfig} storage model instance
	 */
	getStorageConfig: function($tr) {
		var storageId = $tr.data('id');
		if (!storageId) {
			// new entry
			storageId = null;
		}

		var storage = $tr.data('storageConfig');
		if (!storage) {
			storage = new this._storageConfigClass(storageId);
		}
		storage.errors = null;
		storage.mountPoint = $tr.find('.mountPoint input').val();
		storage.backend = $tr.find('.backend').data('identifier');
		storage.authMechanism = $tr.find('.selectAuthMechanism').val();

		var classOptions = {};
		var configuration = $tr.find('.configuration input');
		var missingOptions = [];
		$.each(configuration, function(index, input) {
			var $input = $(input);
			var parameter = $input.data('parameter');
			if ($input.attr('type') === 'button') {
				return;
			}
			if (!isInputValid($input) && !$input.hasClass('optional')) {
				missingOptions.push(parameter);
				return;
			}
			if ($(input).is(':checkbox')) {
				if ($(input).is(':checked')) {
					classOptions[parameter] = true;
				} else {
					classOptions[parameter] = false;
				}
			} else {
				classOptions[parameter] = $(input).val();
			}
		});

		storage.backendOptions = classOptions;
		if (missingOptions.length) {
			storage.errors = {
				backendOptions: missingOptions
			};
		}

		// gather selected users and groups
		if (!this._isPersonal) {
			var groups = [];
			var users = [];
			var multiselect = getSelection($tr);
			$.each(multiselect, function(index, value) {
				var pos = (value.indexOf)?value.indexOf('(group)'): -1;
				if (pos !== -1) {
					groups.push(value.substr(0, pos));
				} else {
					users.push(value);
				}
			});
			// FIXME: this should be done in the multiselect change event instead
			$tr.find('.applicable')
				.data('applicable-groups', groups)
				.data('applicable-users', users);

			storage.applicableUsers = users;
			storage.applicableGroups = groups;

			storage.priority = parseInt($tr.find('input.priority').val() || '100', 10);
		}

		var mountOptions = $tr.find('input.mountOptions').val();
		if (mountOptions) {
			storage.mountOptions = JSON.parse(mountOptions);
		}

		return storage;
	},

	/**
	 * Deletes the storage from the given tr
	 *
	 * @param $tr storage row
	 * @param Function callback callback to call after save
	 */
	deleteStorageConfig: function($tr) {
		var self = this;
		var configId = $tr.data('id');
		if (!_.isNumber(configId)) {
			// deleting unsaved storage
			$tr.remove();
			return;
		}
		var storage = new this._storageConfigClass(configId);
		this.updateStatus($tr, StorageConfig.Status.IN_PROGRESS);

		storage.destroy({
			success: function() {
				$tr.remove();
			},
			error: function() {
				self.updateStatus($tr, StorageConfig.Status.ERROR);
			}
		});
	},

	/**
	 * Saves the storage from the given tr
	 *
	 * @param $tr storage row
	 * @param Function callback callback to call after save
	 * @param concurrentTimer only update if the timer matches this
	 */
	saveStorageConfig:function($tr, callback, concurrentTimer) {
		var self = this;
		var storage = this.getStorageConfig($tr);
		if (!storage || !storage.validate()) {
			return false;
		}

		this.updateStatus($tr, StorageConfig.Status.IN_PROGRESS);
		storage.save({
			success: function(result) {
				if (concurrentTimer === undefined
					|| $tr.data('save-timer') === concurrentTimer
				) {
					self.updateStatus($tr, result.status);
					$tr.data('id', result.id);

					if (_.isFunction(callback)) {
						callback(storage);
					}
				}
			},
			error: function() {
				if (concurrentTimer === undefined
					|| $tr.data('save-timer') === concurrentTimer
				) {
					self.updateStatus($tr, StorageConfig.Status.ERROR);
				}
			}
		});
	},

	/**
	 * Recheck storage availability
	 *
	 * @param {jQuery} $tr storage row
	 * @return {boolean} success
	 */
	recheckStorageConfig: function($tr) {
		var self = this;
		var storage = this.getStorageConfig($tr);
		if (!storage.validate()) {
			return false;
		}

		this.updateStatus($tr, StorageConfig.Status.IN_PROGRESS);
		storage.recheck({
			success: function(result) {
				self.updateStatus($tr, result.status, result.statusMessage);
			},
			error: function() {
				self.updateStatus($tr, StorageConfig.Status.ERROR);
			}
		});
	},

	/**
	 * Update status display
	 *
	 * @param {jQuery} $tr
	 * @param {int} status
	 * @param {string} message
	 */
	updateStatus: function($tr, status, message) {
		var $statusSpan = $tr.find('.status span');
		$statusSpan.removeClass('loading-small success indeterminate error');
		switch (status) {
			case null:
				// remove status
				break;
			case StorageConfig.Status.IN_PROGRESS:
				$statusSpan.addClass('loading-small');
				break;
			case StorageConfig.Status.SUCCESS:
				$statusSpan.addClass('success');
				break;
			case StorageConfig.Status.INDETERMINATE:
				$statusSpan.addClass('indeterminate');
				break;
			default:
				$statusSpan.addClass('error');
		}
		$statusSpan.attr('data-original-title', (typeof message === 'string') ? message : '');
	},

	/**
	 * Suggest mount point name that doesn't conflict with the existing names in the list
	 *
	 * @param {string} defaultMountPoint default name
	 */
	_suggestMountPoint: function(defaultMountPoint) {
		var $el = this.$el;
		var pos = defaultMountPoint.indexOf('/');
		if (pos !== -1) {
			defaultMountPoint = defaultMountPoint.substring(0, pos);
		}
		defaultMountPoint = defaultMountPoint.replace(/\s+/g, '');
		var i = 1;
		var append = '';
		var match = true;
		while (match && i < 20) {
			match = false;
			$el.find('tbody td.mountPoint input').each(function(index, mountPoint) {
				if ($(mountPoint).val() === defaultMountPoint+append) {
					match = true;
					return false;
				}
			});
			if (match) {
				append = i;
				i++;
			} else {
				break;
			}
		}
		return defaultMountPoint + append;
	},

	/**
	 * Toggles the mount options dropdown
	 *
	 * @param {Object} $tr configuration row
	 */
	_showMountOptionsDropdown: function($tr) {
		if (this._preventNextDropdown) {
			// prevented because the click was on the toggle
			this._preventNextDropdown = false;
			return;
		}
		var self = this;
		var storage = this.getStorageConfig($tr);
		var $toggle = $tr.find('.mountOptionsToggle');
		var dropDown = new MountOptionsDropdown();
		var visibleOptions = [
			'read_only',
			'previews',
			'filesystem_check_changes',
			'encoding_compatibility'
		];
		if (this._encryptionEnabled) {
			visibleOptions.push('encrypt');
		}
		if (!this._isPersonal || this._allowUserMountSharing) {
			visibleOptions.push('enable_sharing');
		}

		dropDown.show($toggle, storage.mountOptions || [], visibleOptions);
		$('body').on('mouseup.mountOptionsDropdown', function(event) {
			var $target = $(event.target);
			if ($toggle.has($target).length) {
				// why is it always so hard to make dropdowns behave ?
				// this prevents the click on the toggle to cause
				// the dropdown to reopen itself
				// (preventDefault doesn't work here because the click
				// event is already in the queue and cannot be cancelled)
				self._preventNextDropdown = true;
			}
			if ($target.closest('.dropdown').length) {
				return;
			}
			dropDown.hide();
		});

		dropDown.$el.on('hide', function() {
			var mountOptions = dropDown.getOptions();
			$('body').off('mouseup.mountOptionsDropdown');
			$tr.find('input.mountOptions').val(JSON.stringify(mountOptions));
			self.saveStorageConfig($tr);
		});
	}
}, OC.Backbone.Events);

$(document).ready(function() {
	var enabled = $('#files_external').attr('data-encryption-enabled');
	var encryptionEnabled = (enabled ==='true')? true: false;
	var mountConfigListView = new MountConfigListView($('#externalStorage'), {
		encryptionEnabled: encryptionEnabled,
		allowUserMountSharing: (parseInt($('#allowUserMountSharing').val(), 10) === 1)
	});

	// init the mountConfigListView events after the storages are loaded
	$('#body-settings').on('mountConfigLoaded', function() {
		mountConfigListView._initEvents();
	});
	mountConfigListView.loadStorages();

	// TODO: move this into its own View class
	var $allowUserMounting = $('#allowUserMounting');
	$allowUserMounting.bind('change', function() {
		OC.msg.startSaving('#userMountingMsg');
		if (this.checked) {
			OC.AppConfig.setValue('files_external', 'allow_user_mounting', 'yes');
			$('input[name="allowUserMountingBackends\\[\\]"]').prop('checked', true);
			$('#userMountingBackends').removeClass('hidden');
			$('input[name="allowUserMountingBackends\\[\\]"]').eq(0).trigger('change');
		} else {
			OC.AppConfig.setValue('files_external', 'allow_user_mounting', 'no');
			$('#userMountingBackends').addClass('hidden');
		}
		OC.msg.finishedSaving('#userMountingMsg', {status: 'success', data: {message: t('files_external', 'Saved')}});
	});

	var $allowUserMountSharing = $('#allowUserMountSharing');
	$allowUserMountSharing.bind('change', function() {
		OC.msg.startSaving('#userMountSharingMsg');
		OC.AppConfig.setValue('core', 'allow_user_mount_sharing', this.checked ? 'yes' : 'no');
		OC.msg.finishedSaving('#userMountSharingMsg', {status: 'success', data: {message: t('files_external', 'Saved')}});
	});

	$('input[name="allowUserMountingBackends\\[\\]"]').bind('change', function() {
		OC.msg.startSaving('#userMountingMsg');

		var userMountingBackends = $('input[name="allowUserMountingBackends\\[\\]"]:checked').map(function(){
			return $(this).val();
		}).get();
		var deprecatedBackends = $('input[name="allowUserMountingBackends\\[\\]"][data-deprecate-to]').map(function(){
			if ($.inArray($(this).data('deprecate-to'), userMountingBackends) !== -1) {
				return $(this).val();
			}
			return null;
		}).get();
		userMountingBackends = userMountingBackends.concat(deprecatedBackends);

		OC.AppConfig.setValue('files_external', 'user_mounting_backends', userMountingBackends.join());
		OC.msg.finishedSaving('#userMountingMsg', {status: 'success', data: {message: t('files_external', 'Saved')}});

		// disable allowUserMounting
		if(userMountingBackends.length === 0) {
			$allowUserMounting.prop('checked', false);
			$allowUserMounting.trigger('change');

		}
	});

	$('#files_external input[name=enableExternalStorage]').on('change', function(event) {
		var $target = $(event.target);
		var checked = $target.is(':checked');

		var saveSetting = function(checked) {
			var value = checked ? 'yes' : 'no';
			OC.AppConfig.setValue('core', 'enable_external_storage', value);
			$('#files_external_settings').toggleClass('hidden', !checked);
		};

		if (checked === false) {
			OC.dialogs.confirm(
				t('files_external', 'Disabling external storage will unmount all storages for all users, are you sure ?'),
				t('files_external', 'Disable external storage'),
				function(confirmation) {
					if (confirmation) {
						saveSetting(false);
					} else {
						$target.prop('checked', true);
					}
				},
				true
			);
		} else {
			saveSetting(true);
		}
	});

	// global instance
	OCA.External.Settings.mountConfig = mountConfigListView;

	/**
	 * Legacy
	 *
	 * @namespace
	 * @deprecated use OCA.External.Settings.mountConfig instead
	 */
	OC.MountConfig = {
		saveStorage: _.bind(mountConfigListView.saveStorageConfig, mountConfigListView)
	};
});

// export

OCA.External = OCA.External || {};
/**
 * @namespace
 */
OCA.External.Settings = OCA.External.Settings || {};

OCA.External.Settings.GlobalStorageConfig = GlobalStorageConfig;
OCA.External.Settings.UserStorageConfig = UserStorageConfig;
OCA.External.Settings.MountConfigListView = MountConfigListView;

/**
 * @namespace OAuth2 namespace which is used to verify a storage adapter
 *            using AuthMechanism as oauth2::oauth2
 */
OCA.External.Settings.OAuth2 = OCA.External.Settings.OAuth2 || {};

/**
 * This function sends a request to the given backendUrl and gets the OAuth2 URL
 * for any given backend storage, executes the callback if any, set the data-* parameters
 * of the storage and REDIRECTS the client to Authentication page
 *
 * @param  {String}   backendUrl The backend URL to which request will be sent
 * @param  {Object}   data       Keys -> (backend_id, client_id, client_secret, redirect, tr)
 */
OCA.External.Settings.OAuth2.getAuthUrl = function (backendUrl, data) {
	var $tr = data['tr'];
	var configured = $tr.find('[data-parameter="configured"]');
	var token = $tr.find('.configuration [data-parameter="token"]');

	$.post(backendUrl, {
			step: 1,
			client_id: data['client_id'],
			client_secret: data['client_secret'],
			redirect: data['redirect'],
		}, function (result) {
			if (result && result.status == 'success') {
				$(configured).val('false');
				$(token).val('false');

				OCA.External.Settings.mountConfig.saveStorageConfig($tr, function(status) {
					if (!result.data.url) {
						OC.dialogs.alert('Auth URL not set',
							t('files_external', 'No URL provided by backend ' + data['backend_id'])
						);
						$tr.trigger('oauth_step1_finished', [{
							success: false,
							tr: $tr,
							data: data
						}]);
					} else {
						$tr.trigger('oauth_step1_finished', [{
							success: true,
							tr: $tr,
							data: data
						}]);
						window.location = result.data.url;
					}
				});
			} else {
				OC.dialogs.alert(result.data.message,
					t('files_external', 'Error getting OAuth2 URL for ' + data['backend_id'])
				);
				$tr.trigger('oauth_step1_finished', [{
					success: false,
					tr: $tr,
					data: data
				}]);
			}
		}
	);
}

/**
 * This function verifies the OAuth2 code returned to the client after verification
 * by sending request to the backend with the given CODE and if the code is verified
 * it sets the data-* params to configured and disables the authorize buttons
 *
 * @param  {String}   backendUrl The backend URL to which request will be sent
 * @param  {Object}   data       Keys -> (backend_id, client_id, client_secret, redirect, tr, code)
 * @return {Promise} jQuery Deferred Promise object
 */
OCA.External.Settings.OAuth2.verifyCode = function (backendUrl, data) {
	var $tr = data['tr'];
	var configured = $tr.find('[data-parameter="configured"]');
	var token = $tr.find('.configuration [data-parameter="token"]');
	var statusSpan = $tr.find('.status span');
	statusSpan.removeClass().addClass('waiting');

	var deferredObject = $.Deferred();
	$.post(backendUrl, {
			step: 2,
			client_id: data['client_id'],
			client_secret: data['client_secret'],
			redirect: data['redirect'],
			code: data['code'],
		}, function (result) {
			if (result && result.status == 'success') {
				$(token).val(result.data.token);
				$(configured).val('true');	
				
				OCA.External.Settings.mountConfig.saveStorageConfig($tr, function(status) {
					if (status) {
						$tr.find('.configuration input.auth-param')
							.attr('disabled', 'disabled')
							.addClass('disabled-success')
					}
					deferredObject.resolve(status);
				});
			} else {
				deferredObject.reject(result.data.message);
			}
		}
	);
	return deferredObject.promise();
}

})();
