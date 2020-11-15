/*
 * Copyright (c) 2018 Vincent Petry <pvince81@owncloud.com>
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */

/* global dav */
describe('OCA.Files.LockPlugin tests', function() {
	var fileList;
	var testFiles;
	var testLockData;
	var currentUserStub;

	beforeEach(function() {
		oc_appconfig = oc_appconfig || {};
		oc_appconfig.files = oc_appconfig.files || {};
		oc_appconfig.files.enable_lock_file_action = true;
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

		currentUserStub = sinon.stub(OC, 'getCurrentUser').returns({uid: 'currentuser'});

		fileList = new OCA.Files.FileList($div);
		OCA.Files.LockPlugin.attach(fileList);

		testLockData = {
			lockscope: 'exclusive',
			locktype: 'write',
			lockroot: '/owncloud/remote.php/dav/files/currentuser/basepath',
			depth: 'infinity',
			timeout: 'Second-12345',
			locktoken: 'tehtoken',
			owner: 'lock owner'
		};

		testFiles = [{
			id: '1',
			type: 'file',
			name: 'One.txt',
			path: '/subdir',
			mimetype: 'text/plain',
			size: 12,
			permissions: OC.PERMISSION_ALL,
			etag: 'abc',
			activeLocks: [testLockData]
		}];
	});
	afterEach(function() {
		fileList.destroy();
		fileList = null;
		currentUserStub.restore();
	});

	describe('FileList extensions', function() {
		it('sets active locks attribute', function() {
			var $tr;
			fileList.setFiles(testFiles);
			$tr = fileList.$el.find('tbody tr:first');
			expect(JSON.parse($tr.attr('data-activeLocks'))).toEqual([testLockData]);
		});
	});
	describe('elementToFile', function() {
		it('returns lock data', function() {
			fileList.setFiles(testFiles);
			var $tr = fileList.findFileEl('One.txt');
			var data = fileList.elementToFile($tr);
			expect(data.activeLocks).toEqual([testLockData]);
		});
		it('returns empty array when no lock data is present', function() {
			delete testFiles[0].activeLocks;
			fileList.setFiles(testFiles);
			var $tr = fileList.findFileEl('One.txt');
			var data = fileList.elementToFile($tr);
			expect(data.activeLocks).toEqual([]);
		});
	});
	describe('lock status file action', function() {
		it('renders file action if lock exists', function() {
			fileList.setFiles(testFiles);
			var $tr = fileList.findFileEl('One.txt');
			expect($tr.find('.action.action-lock-status').length).toEqual(1);
		});
		it('does not render file action if no lock exists', function() {
			delete testFiles[0].activeLocks;
			fileList.setFiles(testFiles);
			var $tr = fileList.findFileEl('One.txt');
			expect($tr.find('.action.action-lock-status').length).toEqual(0);
		});
		it('does not render file action if no lock exists (empty array)', function() {
			testFiles[0].activeLocks = [];
			fileList.setFiles(testFiles);
			var $tr = fileList.findFileEl('One.txt');
			expect($tr.find('.action.action-lock-status').length).toEqual(0);
		});
		it('opens locks sidebar tab on click', function() {
			var showDetailsViewStub = sinon.stub(OCA.Files.FileList.prototype, 'showDetailsView');
			fileList.setFiles(testFiles);
			var $tr = fileList.findFileEl('One.txt');
			$tr.find('.action.action-lock-status').click();
			expect(showDetailsViewStub.calledOnce).toEqual(true);
			expect(showDetailsViewStub.getCall(0).args[0]).toEqual('One.txt');
			expect(showDetailsViewStub.getCall(0).args[1]).toEqual('lockTabView');
			showDetailsViewStub.restore();
		});
	});
	describe('tab view', function() {
		it('registers lock tab view', function() {
			var registerTabViewStub = sinon.stub(OCA.Files.FileList.prototype, 'registerTabView');

			fileList.destroy();
			fileList = new OCA.Files.FileList($('#content'));
			OCA.Files.LockPlugin.attach(fileList);

			expect(registerTabViewStub.calledOnce).toEqual(true);
			expect(registerTabViewStub.getCall(0).args[0].id).toEqual('lockTabView');

			registerTabViewStub.restore();
		});
	});
	describe('Webdav requests', function() {
		var requestDeferred;
		var requestStub;
		beforeEach(function() {
			requestDeferred = new $.Deferred();
			requestStub = sinon.stub(dav.Client.prototype, 'propFind').returns(requestDeferred.promise());
		});
		afterEach(function() {
			requestStub.restore();
		});

		function makeLockXml(owner) {
			var xml =
				'<d:multistatus xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns" xmlns:oc="http://owncloud.org/ns">' +
				'	<d:response>' +
				'		<d:href>/owncloud/remote.php/dav/files/currentuser/basepath</d:href>' +
				'		<d:propstat>' +
				'			<d:prop>' +
				'				<oc:permissions>RDNVCK</oc:permissions>' +
				'			</d:prop>' +
				'			<d:status>HTTP/1.1 200 OK</d:status>' +
				'		</d:propstat>' +
				'   </d:response>' +
				'	<d:response>' +
				'		<d:href>/owncloud/remote.php/dav/files/currentuser/basepath/One.txt</d:href>' +
				'		<d:propstat>' +
				'			<d:prop>' +
				'				<oc:permissions>RDNVCK</oc:permissions>' +
				'				<d:lockdiscovery>' +
				'					<d:activelock>' +
				'						<d:lockscope>' +
				'							<d:exclusive/>' +
				'						</d:lockscope>' +
				'						<d:locktype>' +
				'							<d:write/>' +
				'						</d:locktype>' +
				'						<d:lockroot>' +
				'							<d:href>/owncloud/remote.php/dav/files/currentuser/basepath</d:href>' +
				'						</d:lockroot>' +
				'						<d:depth>infinity</d:depth>' +
				'						<d:timeout>Second-12345</d:timeout>' +
				'						<d:locktoken>' +
				'							<d:href>tehtoken</d:href>' +
				'						</d:locktoken>';
			if (owner !== null) {
				xml += '						<d:owner>' + owner + '</d:owner>';
			}

			xml +=
				'					</d:activelock>' +
				'				</d:lockdiscovery>' +
				'			</d:prop>' +
				'			<d:status>HTTP/1.1 200 OK</d:status>' +
				'		</d:propstat>' +
				'	</d:response>' +
				'</d:multistatus>';

			return xml;
		}

		it('parses lock information from response XML to JSON', function(done) {
			var xml = dav.Client.prototype.parseMultiStatus(makeLockXml('lock owner'));
			var promise = fileList.reload();
			requestDeferred.resolve({
				status: 207,
				body: xml
			});

			promise.then(function(status, response) {
				var $tr = fileList.findFileEl('One.txt');
				var data = fileList.elementToFile($tr);
				expect(data.activeLocks.length).toEqual(1);
				expect(data.activeLocks[0]).toEqual(testLockData);
				done();
			});
		});

		it('defaults to unknown user if owner info is not found in lock information', function(done) {
			testLockData.owner = 'Unknown user';

			var xml = dav.Client.prototype.parseMultiStatus(makeLockXml(null));
			var promise = fileList.reload();
			requestDeferred.resolve({
				status: 207,
				body: xml
			});

			promise.then(function(status, response) {
				var $tr = fileList.findFileEl('One.txt');
				var data = fileList.elementToFile($tr);
				expect(data.activeLocks.length).toEqual(1);
				expect(data.activeLocks[0]).toEqual(testLockData);
				done();
			});
		});

		it('sends lockdiscovery in PROPFIND request', function() {
			fileList.reload();

			expect(requestStub.calledOnce).toEqual(true);
			expect(requestStub.getCall(0).args[1]).toContain('{DAV:}lockdiscovery');
		});
	});
});
