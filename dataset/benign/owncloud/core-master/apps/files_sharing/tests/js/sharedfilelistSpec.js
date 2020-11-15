/*
 * Copyright (c) 2014 Vincent Petry <pvince81@owncloud.com>
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */

describe('OCA.Sharing.FileList tests', function() {
	var testFiles, alertStub, notificationStub, fileList;

	beforeEach(function() {
		alertStub = sinon.stub(OC.dialogs, 'alert');
		notificationStub = sinon.stub(OC.Notification, 'show');

		// init parameters and test table elements
		$('#testArea').append(
			'<div id="app-content-container">' +
			// init horrible parameters
			'<input type="hidden" id="dir" value="/"></input>' +
			'<input type="hidden" id="permissions" value="31"></input>' +
			// dummy controls
			'<div id="controls">' +
			'   <div class="actions creatable"></div>' +
			'   <div class="notCreatable"></div>' +
			'</div>' +
			// dummy table
			// TODO: at some point this will be rendered by the fileList class itself!
			'<table id="filestable">' +
			'<thead><tr>' +
			'<th id="headerName" class="hidden column-name">' +
			'<input type="checkbox" id="select_all_files" class="select-all">' +
			'<a class="name columntitle" data-sort="name"><span>Name</span><span class="sort-indicator"></span></a>' +
			'<span class="selectedActions hidden">' +
			'</th>' +
			'<th class="hidden column-sharestate">' +
			'<a class="columntitle" data-sort="sharestate"><span class="sort-indicator"></span></a>' +
			'</th>' +
			'<th class="hidden column-mtime">' +
			'<a class="columntitle" data-sort="mtime"><span class="sort-indicator"></span></a>' +
			'</th>' +
			'<th class="column-expiration">' +
			'<a class="columntitle"><span>Expiration date</span></a>' +
			'</th>' +
			'</tr></thead>' +
			'<tbody id="fileList"></tbody>' +
			'<tfoot></tfoot>' +
			'</table>' +
			'<div id="emptycontent">Empty content message</div>' +
			'</div>'
		);

		OC.Plugins.register('OCA.Files.FileList', OCA.Files.TagsPlugin);
	});
	afterEach(function() {
		testFiles = undefined;
		fileList.destroy();
		fileList = undefined;

		notificationStub.restore();
		alertStub.restore();
	});

	describe('loading file list for incoming shares', function() {
		var ocsResponse;
		var ocsResponseRemote;

		beforeEach(function() {
			fileList = new OCA.Sharing.FileList(
				$('#app-content-container'), {
					sharedWithUser: true
				}
			);
			OCA.Sharing.Util.attach(fileList);

			fileList.reload();

			/* jshint camelcase: false */
			ocsResponse = {
				ocs: {
					meta: {
						status: 'ok',
						statuscode: 100,
						message: null
					},
					data: [{
						id: 7,
						item_type: 'file',
						item_source: 49,
						item_target: '/49',
						file_source: 49,
						file_target: '/local path/local name.txt',
						path: 'files/something shared.txt',
						permissions: 31,
						stime: 11111,
						share_type: OC.Share.SHARE_TYPE_USER,
						share_with: 'user1',
						share_with_displayname: 'User One',
						state: OC.Share.STATE_ACCEPTED,
						tags: [OC.TAG_FAVORITE],
						mimetype: 'text/plain',
						uid_owner: 'user2',
						displayname_owner: 'User Two'
					}]
				}
			};

			/* jshint camelcase: false */
			ocsResponseRemote = {
				ocs: {
					meta: {
						status: 'ok',
						statuscode: 100,
						message: null
					},
					data: [{
						id: 8,
						remote: 'https://foo.bar/',
						remote_id: 42,
						share_token: 'abc',
						name: '/a.txt',
						owner: 'user3',
						user: 'user1',
						mountpoint: '/b.txt',
						mountpoint_hash: 'def',
						accepted: 1,
						mimetype: 'text/plain',
						mtime: 22222,
						permissions: 31,
						type: 'file',
						file_id: 1337
					}]
				}
			};

		});
		it('render file shares', function() {
			expect(fakeServer.requests.length).toEqual(2);
			expect(fakeServer.requests[0].url).toEqual(
				OC.linkToOCS('apps/files_sharing/api/v1') +
				'shares?format=json&shared_with_me=true&state=all&include_tags=true'
			);

			expect(fakeServer.requests[1].url).toEqual(
				OC.linkToOCS('apps/files_sharing/api/v1') +
				'remote_shares/all?format=json&include_tags=true'
			);

			fakeServer.requests[0].respond(
				200,
				{ 'Content-Type': 'application/json' },
				JSON.stringify(ocsResponse)
			);

			fakeServer.requests[1].respond(
				200,
				{ 'Content-Type': 'application/json' },
				JSON.stringify(ocsResponseRemote)
			);

			var $rows = fileList.$el.find('tbody tr');
			expect($rows.length).toEqual(2);

			var $tr = $rows.eq(0);
			expect($tr.attr('data-id')).toEqual('1337');
			expect($tr.attr('data-type')).toEqual('file');
			expect($tr.attr('data-file')).toEqual('b.txt');
			expect($tr.attr('data-path')).toEqual('');
			expect($tr.attr('data-size')).not.toBeDefined();
			expect(parseInt($tr.attr('data-permissions'), 10))
				.toEqual(OC.PERMISSION_ALL); // read and delete
			expect($tr.attr('data-mime')).toEqual('text/plain');
			expect($tr.attr('data-mtime')).toEqual('22222000');
			expect($tr.attr('data-share-owner')).toEqual('user3@foo.bar/');
			expect($tr.attr('data-share-id')).toEqual('8');
			expect($tr.attr('data-share-state')).toEqual('' + OC.Share.STATE_ACCEPTED);
			expect($tr.attr('data-favorite')).not.toBeDefined();
			expect($tr.attr('data-tags')).toEqual('');
			expect($tr.find('a.name').attr('href')).toEqual(
				OC.webroot +
				'/remote.php/webdav/b.txt'
			);
			expect($tr.find('.nametext').text().trim()).toEqual('b.txt');

			$tr = $rows.eq(1);
			expect($tr.attr('data-id')).toEqual('49');
			expect($tr.attr('data-type')).toEqual('file');
			expect($tr.attr('data-file')).toEqual('local name.txt');
			expect($tr.attr('data-path')).toEqual('/local path');
			expect($tr.attr('data-size')).not.toBeDefined();
			expect($tr.attr('data-permissions')).toEqual('31'); // read and delete
			expect($tr.attr('data-mime')).toEqual('text/plain');
			expect($tr.attr('data-mtime')).toEqual('11111000');
			expect($tr.attr('data-share-owner')).toEqual('User Two');
			expect($tr.attr('data-share-id')).toEqual('7');
			expect($tr.attr('data-share-state')).toEqual('' + OC.Share.STATE_ACCEPTED);
			expect($tr.attr('data-favorite')).toEqual('true');
			expect($tr.attr('data-tags')).toEqual(OC.TAG_FAVORITE);
			expect($tr.find('a.name').attr('href')).toEqual(
				OC.webroot +
				'/remote.php/webdav/local%20path/local%20name.txt'
			);
			expect($tr.find('.nametext').text().trim()).toEqual('local name.txt');
		});
		it('render folder shares', function() {
			/* jshint camelcase: false */
			ocsResponse.ocs.data[0] = _.extend(ocsResponse.ocs.data[0], {
				item_type: 'folder',
				file_target: '/local path/local name',
				path: 'files/something shared',
			});

			ocsResponseRemote.ocs.data[0] = _.extend(ocsResponseRemote.ocs.data[0], {
				type: 'dir',
				mimetype: 'httpd/unix-directory',
				name: '/a',
				mountpoint: '/b'
			});

			expect(fakeServer.requests.length).toEqual(2);
			expect(fakeServer.requests[0].url).toEqual(
				OC.linkToOCS('apps/files_sharing/api/v1') +
				'shares?format=json&shared_with_me=true&state=all&include_tags=true'
			);
			expect(fakeServer.requests[1].url).toEqual(
				OC.linkToOCS('apps/files_sharing/api/v1') +
				'remote_shares/all?format=json&include_tags=true'
			);

			fakeServer.requests[0].respond(
				200,
				{ 'Content-Type': 'application/json' },
				JSON.stringify(ocsResponse)
			);
			fakeServer.requests[1].respond(
				200,
				{ 'Content-Type': 'application/json' },
				JSON.stringify(ocsResponseRemote)
			);

			var $rows = fileList.$el.find('tbody tr');
			expect($rows.length).toEqual(2);

			var $tr = $rows.eq(0);
			expect($tr.attr('data-id')).toEqual('1337');
			expect($tr.attr('data-type')).toEqual('dir');
			expect($tr.attr('data-file')).toEqual('b');
			expect($tr.attr('data-path')).toEqual('');
			expect($tr.attr('data-size')).not.toBeDefined();
			expect(parseInt($tr.attr('data-permissions'), 10))
				.toEqual(OC.PERMISSION_ALL); // read and delete
			expect($tr.attr('data-mime')).toEqual('httpd/unix-directory');
			expect($tr.attr('data-mtime')).toEqual('22222000');
			expect($tr.attr('data-share-owner')).toEqual('user3@foo.bar/');
			expect($tr.attr('data-share-id')).toEqual('8');
			expect($tr.attr('data-favorite')).not.toBeDefined();
			expect($tr.attr('data-tags')).toEqual('');
			expect($tr.find('a.name').attr('href')).toEqual(
				OC.webroot +
				'/index.php/apps/files' +
				'?dir=/b'
			);
			expect($tr.find('.nametext').text().trim()).toEqual('b');

			$tr = $rows.eq(1);
			expect($tr.attr('data-id')).toEqual('49');
			expect($tr.attr('data-type')).toEqual('dir');
			expect($tr.attr('data-file')).toEqual('local name');
			expect($tr.attr('data-path')).toEqual('/local path');
			expect($tr.attr('data-size')).not.toBeDefined();
			expect($tr.attr('data-permissions')).toEqual('31'); // read and delete
			expect($tr.attr('data-mime')).toEqual('httpd/unix-directory');
			expect($tr.attr('data-mtime')).toEqual('11111000');
			expect($tr.attr('data-share-owner')).toEqual('User Two');
			expect($tr.attr('data-share-id')).toEqual('7');
			expect($tr.attr('data-favorite')).toEqual('true');
			expect($tr.attr('data-tags')).toEqual(OC.TAG_FAVORITE);
			expect($tr.find('a.name').attr('href')).toEqual(
				OC.webroot +
				'/index.php/apps/files' +
				'?dir=/local%20path/local%20name'
			);
			expect($tr.find('.nametext').text().trim()).toEqual('local name');
		});
		it('groups link shares with regular shares when based on same file', function() {
			/* jshint camelcase: false */
			ocsResponse.ocs.data[0] = _.extend(ocsResponse.ocs.data[0], {
				item_type: 'folder',
				file_target: '/local path/local name',
				path: 'files/something shared',
				state: OC.Share.STATE_PENDING
			});
			// group share from the same source
			ocsResponse.ocs.data[1] = _.extend({}, ocsResponse.ocs.data[0], {
				id: 8,
				item_type: 'folder',
				file_target: '/local path/local name',
				path: 'files/something shared',
				share_type: OC.Share.SHARE_TYPE_GROUP,
				share_with: 'group1',
				state: OC.Share.STATE_PENDING,
				stime: 22222
			});
			ocsResponseRemote.ocs.data = [];

			expect(fakeServer.requests.length).toEqual(2);
			expect(fakeServer.requests[0].url).toEqual(
				OC.linkToOCS('apps/files_sharing/api/v1') +
				'shares?format=json&shared_with_me=true&state=all&include_tags=true'
			);
			expect(fakeServer.requests[1].url).toEqual(
				OC.linkToOCS('apps/files_sharing/api/v1') +
				'remote_shares/all?format=json&include_tags=true'
			);

			fakeServer.requests[0].respond(
				200,
				{ 'Content-Type': 'application/json' },
				JSON.stringify(ocsResponse)
			);
			fakeServer.requests[1].respond(
				200,
				{ 'Content-Type': 'application/json' },
				JSON.stringify(ocsResponseRemote)
			);

			var $rows = fileList.$el.find('tbody tr');
			var $tr = $rows.eq(0);
			expect($rows.length).toEqual(1);
			expect($tr.attr('data-id')).toEqual('49');
			expect($tr.attr('data-type')).toEqual('dir');
			expect($tr.attr('data-file')).toEqual('local name');
			expect($tr.attr('data-path')).toEqual('/local path');
			expect($tr.attr('data-size')).not.toBeDefined();
			expect($tr.attr('data-permissions')).toEqual('31'); // read and delete
			expect($tr.attr('data-mime')).toEqual('httpd/unix-directory');
			// always use the most recent stime
			expect($tr.attr('data-mtime')).toEqual('22222000');
			expect($tr.attr('data-share-owner')).toEqual('User Two');
			expect($tr.attr('data-share-id')).toEqual('7,8');
			expect($tr.attr('data-share-state')).toEqual('' + OC.Share.STATE_PENDING);
			expect($tr.attr('data-favorite')).toEqual('true');
			expect($tr.attr('data-tags')).toEqual(OC.TAG_FAVORITE);
			expect($tr.find('.nametext').text().trim()).toEqual('local name');

			expect($tr.find('a.name').attr('href')).toEqual('#');

			var file = fileList.elementToFile($tr);
			expect(file.shares).toEqual([{id: '7'}, {id: '8'}]);
			expect(file.shareState).toEqual(OC.Share.STATE_PENDING);
		});
	});
	describe('loading file list for outgoing shares', function() {
		var ocsResponse;

		beforeEach(function() {
			fileList = new OCA.Sharing.FileList(
				$('#app-content-container'), {
					sharedWithUser: false
				}
			);
			OCA.Sharing.Util.attach(fileList);

			fileList.reload();

			/* jshint camelcase: false */
			ocsResponse = {
				ocs: {
					meta: {
						status: 'ok',
						statuscode: 100,
						message: null
					},
					data: [{
						id: 7,
						item_type: 'file',
						item_source: 49,
						file_source: 49,
						path: '/local path/local name.txt',
						permissions: 27,
						stime: 11111,
						share_type: OC.Share.SHARE_TYPE_USER,
						share_with: 'user2',
						share_with_displayname: 'User Two',
						tags: [OC.TAG_FAVORITE],
						mimetype: 'text/plain',
						uid_owner: 'user1',
						displayname_owner: 'User One'
					}]
				}
			};
		});
		it('render file shares', function() {
			var request;

			expect(fakeServer.requests.length).toEqual(1);
			request = fakeServer.requests[0];
			expect(request.url).toEqual(
				OC.linkToOCS('apps/files_sharing/api/v1') +
				'shares?format=json&include_tags=true'
			);

			fakeServer.requests[0].respond(
				200,
				{ 'Content-Type': 'application/json' },
				JSON.stringify(ocsResponse)
			);

			var $rows = fileList.$el.find('tbody tr');
			var $tr = $rows.eq(0);
			expect($rows.length).toEqual(1);
			expect($tr.attr('data-id')).toEqual('49');
			expect($tr.attr('data-type')).toEqual('file');
			expect($tr.attr('data-file')).toEqual('local name.txt');
			expect($tr.attr('data-path')).toEqual('/local path');
			expect($tr.attr('data-size')).not.toBeDefined();
			expect($tr.attr('data-permissions')).toEqual('31'); // read and delete
			expect($tr.attr('data-mime')).toEqual('text/plain');
			expect($tr.attr('data-mtime')).toEqual('11111000');
			expect($tr.attr('data-share-owner')).not.toBeDefined();
			expect($tr.attr('data-share-id')).toEqual('7');
			expect($tr.attr('data-favorite')).toEqual('true');
			expect($tr.attr('data-tags')).toEqual(OC.TAG_FAVORITE);
			expect($tr.find('a.name').attr('href')).toEqual(
				OC.webroot +
				'/remote.php/webdav/local%20path/local%20name.txt'
			);
			expect($tr.find('.nametext').text().trim()).toEqual('local name.txt');
		});
		it('render folder shares', function() {
			var request;
			/* jshint camelcase: false */
			ocsResponse.ocs.data[0] = _.extend(ocsResponse.ocs.data[0], {
				item_type: 'folder',
				path: '/local path/local name',
			});

			expect(fakeServer.requests.length).toEqual(1);
			request = fakeServer.requests[0];
			expect(request.url).toEqual(
				OC.linkToOCS('apps/files_sharing/api/v1') +
				'shares?format=json&include_tags=true'
			);

			fakeServer.requests[0].respond(
				200,
				{ 'Content-Type': 'application/json' },
				JSON.stringify(ocsResponse)
			);

			var $rows = fileList.$el.find('tbody tr');
			var $tr = $rows.eq(0);
			expect($rows.length).toEqual(1);
			expect($tr.attr('data-id')).toEqual('49');
			expect($tr.attr('data-type')).toEqual('dir');
			expect($tr.attr('data-file')).toEqual('local name');
			expect($tr.attr('data-path')).toEqual('/local path');
			expect($tr.attr('data-size')).not.toBeDefined();
			expect($tr.attr('data-permissions')).toEqual('31'); // read and delete
			expect($tr.attr('data-mime')).toEqual('httpd/unix-directory');
			expect($tr.attr('data-mtime')).toEqual('11111000');
			expect($tr.attr('data-share-owner')).not.toBeDefined();
			expect($tr.attr('data-share-id')).toEqual('7');
			expect($tr.attr('data-favorite')).toEqual('true');
			expect($tr.attr('data-tags')).toEqual(OC.TAG_FAVORITE);
			expect($tr.find('a.name').attr('href')).toEqual(
				OC.webroot +
				'/index.php/apps/files' +
				'?dir=/local%20path/local%20name'
			);
			expect($tr.find('.nametext').text().trim()).toEqual('local name');
		});
		it('render link shares', function() {
			/* jshint camelcase: false */
			var request;
			ocsResponse.ocs.data[0] = {
				id: 7,
				item_type: 'file',
				item_source: 49,
				file_source: 49,
				path: '/local path/local name.txt',
				permissions: 1,
				stime: 11111,
				share_type: OC.Share.SHARE_TYPE_LINK,
				share_with: null,
				token: 'abc',
				mimetype: 'text/plain',
				uid_owner: 'user1',
				displayname_owner: 'User One',
				tags: [OC.TAG_FAVORITE]
			};
			expect(fakeServer.requests.length).toEqual(1);
			request = fakeServer.requests[0];
			expect(request.url).toEqual(
				OC.linkToOCS('apps/files_sharing/api/v1') +
				'shares?format=json&include_tags=true'
			);

			fakeServer.requests[0].respond(
				200,
				{ 'Content-Type': 'application/json' },
				JSON.stringify(ocsResponse)
			);

			var $rows = fileList.$el.find('tbody tr');
			var $tr = $rows.eq(0);
			expect($rows.length).toEqual(1);
			expect($tr.attr('data-id')).toEqual('49');
			expect($tr.attr('data-type')).toEqual('file');
			expect($tr.attr('data-file')).toEqual('local name.txt');
			expect($tr.attr('data-path')).toEqual('/local path');
			expect($tr.attr('data-size')).not.toBeDefined();
			expect($tr.attr('data-permissions')).toEqual('31'); // read and delete
			expect($tr.attr('data-mime')).toEqual('text/plain');
			expect($tr.attr('data-mtime')).toEqual('11111000');
			expect($tr.attr('data-share-owner')).not.toBeDefined();
			expect($tr.attr('data-share-id')).toEqual('7');
			expect($tr.attr('data-favorite')).toEqual('true');
			expect($tr.attr('data-tags')).toEqual(OC.TAG_FAVORITE);
			expect($tr.find('a.name').attr('href')).toEqual(
				OC.webroot + '/remote.php/webdav/local%20path/local%20name.txt'
			);

			expect($tr.find('.nametext').text().trim()).toEqual('local name.txt');
		});
		it('groups link shares with regular shares', function() {
			/* jshint camelcase: false */
			var request;
			// link share
			ocsResponse.ocs.data.push({
				id: 8,
				item_type: 'file',
				item_source: 49,
				file_source: 49,
				path: '/local path/local name.txt',
				permissions: 1,
				stime: 11111,
				share_type: OC.Share.SHARE_TYPE_LINK,
				share_with: null,
				token: 'abc',
				mimetype: 'text/plain',
				uid_owner: 'user1',
				displayname_owner: 'User One',
				tags: [OC.TAG_FAVORITE],
			});
			// another share of the same file
			ocsResponse.ocs.data.push({
				id: 9,
				item_type: 'file',
				item_source: 49,
				file_source: 49,
				path: '/local path/local name.txt',
				permissions: 27,
				stime: 22222,
				share_type: OC.Share.SHARE_TYPE_USER,
				share_with: 'user3',
				share_with_displayname: 'User Three',
				mimetype: 'text/plain',
				uid_owner: 'user1',
				displayname_owner: 'User One'
			});
			expect(fakeServer.requests.length).toEqual(1);
			request = fakeServer.requests[0];
			expect(request.url).toEqual(
				OC.linkToOCS('apps/files_sharing/api/v1') +
				'shares?format=json&include_tags=true'
			);

			fakeServer.requests[0].respond(
				200,
				{ 'Content-Type': 'application/json' },
				JSON.stringify(ocsResponse)
			);

			var $rows = fileList.$el.find('tbody tr');
			var $tr = $rows.eq(0);
			expect($rows.length).toEqual(1);
			expect($tr.attr('data-id')).toEqual('49');
			expect($tr.attr('data-type')).toEqual('file');
			expect($tr.attr('data-file')).toEqual('local name.txt');
			expect($tr.attr('data-path')).toEqual('/local path');
			expect($tr.attr('data-size')).not.toBeDefined();
			expect($tr.attr('data-permissions')).toEqual('31'); // read and delete
			expect($tr.attr('data-mime')).toEqual('text/plain');
			// always use the most recent stime
			expect($tr.attr('data-mtime')).toEqual('22222000');
			expect($tr.attr('data-share-owner')).not.toBeDefined();
			expect($tr.attr('data-share-id')).toEqual('7,8,9');
			expect($tr.attr('data-favorite')).toEqual('true');
			expect($tr.attr('data-tags')).toEqual(OC.TAG_FAVORITE);
			expect($tr.find('a.name').attr('href')).toEqual(
				OC.webroot + '/remote.php/webdav/local%20path/local%20name.txt'
			);
			expect($tr.find('.nametext').text().trim()).toEqual('local name.txt');
		});
	});
	describe('loading file list for link shares', function() {
		var ocsResponse;

		beforeEach(function() {
			fileList = new OCA.Sharing.FileList(
				$('#app-content-container'), {
					linksOnly: true
				}
			);
			OCA.Sharing.Util.attach(fileList);

			fileList.reload();

			var expirationDateInADay = moment()
				.add(1, 'days').format('YYYY-MM-DD HH:mm:ss');

			/* jshint camelcase: false */
			ocsResponse = {
				ocs: {
					meta: {
						status: 'ok',
						statuscode: 100,
						message: null
					},
					data: [{
						id: 7,
						item_type: 'file',
						item_source: 49,
						file_source: 49,
						path: '/local path/local name.txt',
						permissions: 1,
						stime: 11111,
						expiration: null,
						share_type: OC.Share.SHARE_TYPE_LINK,
						share_with: null,
						token: 'abc',
						mimetype: 'text/plain',
						uid_owner: 'user1',
						displayname_owner: 'User One',
						tags: [OC.TAG_FAVORITE]
					},{
						id: 8,
						item_type: 'file',
						item_source: 50,
						file_source: 50,
						path: '/local path2/local name2.txt',
						permissions: 1,
						stime: 11112,
						expiration: expirationDateInADay,
						share_type: OC.Share.SHARE_TYPE_LINK,
						share_with: null,
						token: 'abcd',
						mimetype: 'text/plain2',
						uid_owner: 'user2',
						displayname_owner: 'User One2'
					}]
				}
			};
		});
		it('render only link shares', function() {
			/* jshint camelcase: false */
			var request;
			ocsResponse.ocs.data.push({
				// non-link share
				id: 8,
				item_type: 'file',
				item_source: 49,
				file_source: 49,
				path: '/local path/local name.txt',
				permissions: 27,
				stime: 11111,
				share_type: OC.Share.SHARE_TYPE_USER,
				share_with: 'user2',
				share_with_displayname: 'User Two',
				mimetype: 'text/plain',
				uid_owner: 'user1',
				displayname_owner: 'User One',
				tags: [OC.TAG_FAVORITE]
			});
			expect(fakeServer.requests.length).toEqual(1);
			request = fakeServer.requests[0];
			expect(request.url).toEqual(
				OC.linkToOCS('apps/files_sharing/api/v1') +
				'shares?format=json&include_tags=true'
			);

			fakeServer.requests[0].respond(
				200,
				{ 'Content-Type': 'application/json' },
				JSON.stringify(ocsResponse)
			);

			// only renders the link share entries
			var $rows = fileList.$el.find('tbody tr');
			var $tr = $rows.eq(0);
			expect($rows.length).toEqual(2);
			expect($tr.attr('data-id')).toEqual('49');
			expect($tr.attr('data-type')).toEqual('file');
			expect($tr.attr('data-file')).toEqual('local name.txt');
			expect($tr.attr('data-path')).toEqual('/local path');
			expect($tr.attr('data-size')).not.toBeDefined();
			expect($tr.attr('data-permissions')).toEqual('31'); // read and delete
			expect($tr.attr('data-mime')).toEqual('text/plain');
			expect($tr.attr('data-mtime')).toEqual('11111000');
			expect($tr.attr('data-share-recipients')).not.toBeDefined();
			expect($tr.attr('data-share-owner')).not.toBeDefined();
			expect($tr.attr('data-share-id')).toEqual('7');
			expect($tr.attr('data-favorite')).toEqual('true');
			expect($tr.attr('data-tags')).toEqual(OC.TAG_FAVORITE);
			expect($tr.find('a.name').attr('href')).toEqual(
				OC.webroot + '/remote.php/webdav/local%20path/local%20name.txt'
			);
			expect($tr.attr('data-expiration')).toEqual('0');
			expect($tr.find('td:last-child span').text()).toEqual('');

			expect($tr.find('.nametext').text().trim()).toEqual('local name.txt');

			// change to next row
			$tr = $rows.eq(1);
			expect($tr.attr('data-id')).toEqual('50');
			expect($tr.attr('data-file')).toEqual('local name2.txt');
			expect($tr.attr('data-expiration')).not.toEqual('0');
			expect($tr.attr('data-favorite')).not.toBeDefined();
			expect($tr.attr('data-tags')).toEqual('');
			expect($tr.find('td:last-child span').text()).toEqual('in a day');
		});
		it('does not show virtual token recipient as recipient when password was set', function() {
			/* jshint camelcase: false */
			var request;
			// when a password is set, share_with contains an auth token
			ocsResponse.ocs.data[0].share_with = 'abc01234/01234abc';
			ocsResponse.ocs.data[0].share_with_displayname = 'abc01234/01234abc';
			expect(fakeServer.requests.length).toEqual(1);
			request = fakeServer.requests[0];
			expect(request.url).toEqual(
				OC.linkToOCS('apps/files_sharing/api/v1') +
				'shares?format=json&include_tags=true'
			);

			fakeServer.requests[0].respond(
				200,
				{ 'Content-Type': 'application/json' },
				JSON.stringify(ocsResponse)
			);

			// only renders the link share entry
			var $rows = fileList.$el.find('tbody tr');
			var $tr = $rows.eq(0);
			expect($rows.length).toEqual(2);
			expect($tr.attr('data-id')).toEqual('49');
			expect($tr.attr('data-type')).toEqual('file');
			expect($tr.attr('data-file')).toEqual('local name.txt');
			expect($tr.attr('data-path')).toEqual('/local path');
			expect($tr.attr('data-size')).not.toBeDefined();
			expect($tr.attr('data-permissions')).toEqual('31'); // read and delete
			expect($tr.attr('data-mime')).toEqual('text/plain');
			expect($tr.attr('data-mtime')).toEqual('11111000');
			expect($tr.attr('data-share-recipients')).not.toBeDefined();
			expect($tr.attr('data-share-owner')).not.toBeDefined();
			expect($tr.attr('data-share-id')).toEqual('7');
			expect($tr.attr('data-favorite')).toEqual('true');
			expect($tr.attr('data-tags')).toEqual(OC.TAG_FAVORITE);
			expect($tr.find('a.name').attr('href')).toEqual(
					OC.webroot +
					'/remote.php/webdav/local%20path/local%20name.txt');

			expect($tr.find('.nametext').text().trim()).toEqual('local name.txt');
		});
	});
	describe('setting share permissions for files', function () {
		beforeEach(function () {

			var $content = $('<div id="content"></div>');
			$('#testArea').append($content);
			// dummy file list
			var $div = $(
				'<div>' +
				'<table id="filestable">' +
				'<thead></thead>' +
				'<tbody id="fileList"></tbody>' +
				'</table>' +
				'</div>');
			$('#content').append($div);

			fileList = new OCA.Files.FileList($div);
			OCA.Sharing.Util.attach(fileList);
		});

		it('external storage root folder', function () {
			var $tr;
			OC.Share.statuses = {1: {link: false, path: '/subdir'}};
			fileList.setFiles([{
				id: 1,
				type: 'dir',
				name: 'One.txt',
				path: '/subdir',
				mimetype: 'text/plain',
				size: 12,
				permissions: OC.PERMISSION_READ,
				etag: 'abc',
				shareOwner: 'User One',
				recipients: 'User Two',
				mountType: 'external-root'
			}]);
			$tr = fileList.$el.find('tr:first');

			expect(parseInt($tr.attr('data-share-permissions'), 10)).toEqual(OC.PERMISSION_ALL - OC.PERMISSION_SHARE);
		});

		it('external storage root folder reshare', function () {
			var $tr;
			OC.Share.statuses = {1: {link: false, path: '/subdir'}};
			fileList.setFiles([{
				id: 1,
				type: 'dir',
				name: 'One.txt',
				path: '/subdir',
				mimetype: 'text/plain',
				size: 12,
				permissions: OC.PERMISSION_READ + OC.PERMISSION_SHARE,
				etag: 'abc',
				shareOwner: 'User One',
				recipients: 'User Two',
				mountType: 'external-root'
			}]);
			$tr = fileList.$el.find('tr:first');

			expect(parseInt($tr.attr('data-share-permissions'), 10)).toEqual(OC.PERMISSION_ALL);
		});

		it('external storage root folder file', function () {
			var $tr;
			OC.Share.statuses = {1: {link: false, path: '/subdir'}};
			fileList.setFiles([{
				id: 1,
				type: 'file',
				name: 'One.txt',
				path: '/subdir',
				mimetype: 'text/plain',
				size: 12,
				permissions: OC.PERMISSION_READ,
				etag: 'abc',
				shareOwner: 'User One',
				recipients: 'User Two',
				mountType: 'external-root'
			}]);
			$tr = fileList.$el.find('tr:first');

			expect(parseInt($tr.attr('data-share-permissions'), 10))
				.toEqual(OC.PERMISSION_ALL - OC.PERMISSION_SHARE - OC.PERMISSION_DELETE - OC.PERMISSION_CREATE);
		});
	});
	describe('sorting in shared with you view', function () {
		beforeEach(function() {
			var $content = $('<div id="content"></div>');
			$('#testArea').append($content);
			// dummy file list
			var $div = $(
				'<div>' +
				'<table id="filestable">' +
				'<thead></thead>' +
				'<tbody id="fileList"></tbody>' +
				'</table>' +
				'</div>');
			$('#content').append($div);

			fileList = new OCA.Sharing.FileList($div, {
				sharedWithUser: true
			});
		});

		it('sorts by state then name', function () {
			var $tr;
			fileList.add({
				id: '1',
				name: 'One.txt',
				mimetype: 'text/plain',
				shareState: OC.Share.STATE_REJECTED
			});
			fileList.add({
				id: '2',
				name: 'Two.txt',
				mimetype: 'text/plain',
				shareState: OC.Share.STATE_PENDING
			});
			fileList.add({
				id: '3',
				name: 'Three.txt',
				mimetype: 'text/plain',
				shareState: OC.Share.STATE_PENDING
			});
			fileList.add({
				id: '4',
				name: 'Four.txt',
				mimetype: 'text/plain',
				shareState: OC.Share.STATE_ACCEPTED
			});

			var $trs = fileList.$el.find('tbody tr');
			var ids = _.map($trs, function (tr) {
				return $(tr).attr('data-id');
			});

			expect(ids).toEqual(['3', '2' , '4', '1']);
		});
	});
});
