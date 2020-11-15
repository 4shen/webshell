/*
 * Copyright (c) 2016
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */

(function(OC, OCA) {

	_.extend(OC.Files.Client, {
		PROPERTY_FILEID:	'{' + OC.Files.Client.NS_OWNCLOUD + '}id',
		PROPERTY_MESSAGE: 	'{' + OC.Files.Client.NS_OWNCLOUD + '}message',
		PROPERTY_ACTORTYPE:	'{' + OC.Files.Client.NS_OWNCLOUD + '}actorType',
		PROPERTY_ACTORID:	'{' + OC.Files.Client.NS_OWNCLOUD + '}actorId',
		PROPERTY_ISUNREAD:	'{' + OC.Files.Client.NS_OWNCLOUD + '}isUnread',
		PROPERTY_OBJECTID:	'{' + OC.Files.Client.NS_OWNCLOUD + '}objectId',
		PROPERTY_OBJECTTYPE:	'{' + OC.Files.Client.NS_OWNCLOUD + '}objectType',
		PROPERTY_ACTORDISPLAYNAME:	'{' + OC.Files.Client.NS_OWNCLOUD + '}actorDisplayName',
		PROPERTY_CREATIONDATETIME:	'{' + OC.Files.Client.NS_OWNCLOUD + '}creationDateTime'
	});

	/**
	 * @class OCA.Comments.CommentModel
	 * @classdesc
	 *
	 * Comment
	 *
	 */
	var CommentModel = OC.Backbone.Model.extend(
		/** @lends OCA.Comments.CommentModel.prototype */ {
		sync: OC.Backbone.davSync,

		defaults: {
			actorType: 'users',
			objectType: 'files'
		},

		davProperties: {
			'id':	OC.Files.Client.PROPERTY_FILEID,
			'message':	OC.Files.Client.PROPERTY_MESSAGE,
			'actorType':	OC.Files.Client.PROPERTY_ACTORTYPE,
			'actorId':	OC.Files.Client.PROPERTY_ACTORID,
			'actorDisplayName':	OC.Files.Client.PROPERTY_ACTORDISPLAYNAME,
			'creationDateTime':	OC.Files.Client.PROPERTY_CREATIONDATETIME,
			'objectType':	OC.Files.Client.PROPERTY_OBJECTTYPE,
			'objectId':	OC.Files.Client.PROPERTY_OBJECTID,
			'isUnread':	OC.Files.Client.PROPERTY_ISUNREAD
		},

		parse: function(data) {
			return {
				id: data.id,
				message: data.message,
				actorType: data.actorType,
				actorId: data.actorId,
				actorDisplayName: data.actorDisplayName,
				creationDateTime: data.creationDateTime,
				objectType: data.objectType,
				objectId: data.objectId,
				isUnread: (data.isUnread === 'true')
			};
		}
	});

	OCA.Comments.CommentModel = CommentModel;
})(OC, OCA);
