/**
 * Copyright (c) 2014 Vincent Petry <pvince81@owncloud.com>
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */
describe('OC.L10N tests', function() {
	var TEST_APP = 'jsunittestapp';

	beforeEach(function() {
		OC.appswebroots[TEST_APP] = OC.webroot + '/apps3/jsunittestapp';
	});
	afterEach(function() {
		delete OC.L10N._bundles[TEST_APP];
		delete OC.appswebroots[TEST_APP];
	});

	describe('text translation', function() {
		beforeEach(function() {
			OC.L10N.register(TEST_APP, {
				'Hello world!': 'Hallo Welt!',
				'Hello {name}, the weather is {weather}': 'Hallo {name}, das Wetter ist {weather}',
				'sunny': 'sonnig'
			}, 'nplurals=2; plural=(n != 1);');
		});
		it('returns untranslated text when no bundle exists', function() {
			delete OC.L10N._bundles[TEST_APP];
			expect(t(TEST_APP, 'unknown text')).toEqual('unknown text');
		});
		it('returns untranslated text when no key exists', function() {
			expect(t(TEST_APP, 'unknown text')).toEqual('unknown text');
		});
		it('returns translated text when key exists', function() {
			expect(t(TEST_APP, 'Hello world!')).toEqual('Hallo Welt!');
		});
		it('returns translated text with placeholder', function() {
			expect(
				t(TEST_APP, 'Hello {name}, the weather is {weather}', {name: 'Steve', weather: t(TEST_APP, 'sunny')})
			).toEqual('Hallo Steve, das Wetter ist sonnig');
		});
		it('returns text with escaped placeholder', function() {
			expect(
				t(TEST_APP, 'Hello {name}', {name: '<strong>Steve</strong>'})
			).toEqual('Hello &lt;strong&gt;Steve&lt;/strong&gt;');
		});
		it('returns text with not escaped placeholder', function() {
			expect(
				t(TEST_APP, 'Hello {name}', {name: '<strong>Steve</strong>'}, null, {escape: false})
			).toEqual('Hello <strong>Steve</strong>');
		});
		it('keeps old texts when registering existing bundle', function() {
			OC.L10N.register(TEST_APP, {
				'sunny': 'sonnig',
				'new': 'neu'
			}, 'nplurals=2; plural=(n != 1);');
			expect(t(TEST_APP, 'sunny')).toEqual('sonnig');
			expect(t(TEST_APP, 'new')).toEqual('neu');
		});
	});
	describe('plurals', function() {
		var warnStub;

		beforeEach(function() {
			warnStub = sinon.stub(console, 'warn');
		});
		afterEach(function() { 
			warnStub.restore(); 
		});
	
		function checkPlurals() {
			expect(
				n(TEST_APP, 'download %n file', 'download %n files', 0)
			).toEqual('0 Dateien herunterladen');
			expect(
				n(TEST_APP, 'download %n file', 'download %n files', 1)
			).toEqual('1 Datei herunterladen');
			expect(
				n(TEST_APP, 'download %n file', 'download %n files', 2)
			).toEqual('2 Dateien herunterladen');
			expect(
				n(TEST_APP, 'download %n file', 'download %n files', 1024)
			).toEqual('1024 Dateien herunterladen');
		}

		it('generates plural for default text when translation does not exist', function() {
			OC.L10N.register(TEST_APP, {
			});
			expect(warnStub.called).toEqual(true);
			expect(
				n(TEST_APP, 'download %n file', 'download %n files', 0)
			).toEqual('download 0 files');
			expect(
				n(TEST_APP, 'download %n file', 'download %n files', 1)
			).toEqual('download 1 file');
			expect(
				n(TEST_APP, 'download %n file', 'download %n files', 2)
			).toEqual('download 2 files');
			expect(
				n(TEST_APP, 'download %n file', 'download %n files', 1024)
			).toEqual('download 1024 files');
		});
		it('generates plural with default function when no forms specified', function() {
			OC.L10N.register(TEST_APP, {
				'_download %n file_::_download %n files_':
					['%n Datei herunterladen', '%n Dateien herunterladen']
			});
			expect(warnStub.called).toEqual(true);
			checkPlurals();
		});
		it('generates plural with generated function when forms is specified', function() {
			OC.L10N.register(TEST_APP, {
				'_download %n file_::_download %n files_':
					['%n Datei herunterladen', '%n Dateien herunterladen']
			}, 'nplurals=2; plural=(n != 1);');
			expect(warnStub.notCalled).toEqual(true);
			checkPlurals();
		});
		it('generates plural with function when forms is specified as function', function() {
			OC.L10N.register(TEST_APP, {
				'_download %n file_::_download %n files_':
					['%n Datei herunterladen', '%n Dateien herunterladen']
			}, function(n) {
				return {
					nplurals: 2,
					plural: (n !== 1) ? 1 : 0
				};
			});
			expect(warnStub.notCalled).toEqual(true);
			checkPlurals();
		});
	});
	describe('async loading of translations', function() {
		it('loads bundle for given app and calls callback', function() {
			var localeStub = sinon.stub(OC, 'getLocale').returns('zh_CN');
			var callbackStub = sinon.stub();
			var promiseStub = sinon.stub();
			OC.L10N.load(TEST_APP, callbackStub).then(promiseStub);
			expect(callbackStub.notCalled).toEqual(true);
			expect(promiseStub.notCalled).toEqual(true);
			expect(fakeServer.requests.length).toEqual(1);
			var req = fakeServer.requests[0];
			expect(req.url).toEqual(
				OC.webroot + '/apps3/' + TEST_APP + '/l10n/zh_CN.json'
			);
			req.respond(
				200,
				{ 'Content-Type': 'application/json' },
				JSON.stringify({
					translations: {'Hello world!': '你好世界!'},
					pluralForm: 'nplurals=2; plural=(n != 1);'
				})
			);

			expect(callbackStub.calledOnce).toEqual(true);
			expect(promiseStub.calledOnce).toEqual(true);
			expect(t(TEST_APP, 'Hello world!')).toEqual('你好世界!');
			localeStub.restore();
		});
		it('calls callback if translation already available', function() {
			var promiseStub = sinon.stub();
			var callbackStub = sinon.stub();
			OC.L10N.register(TEST_APP, {
				'Hello world!': 'Hallo Welt!'
			}, 'nplurals=2; plural=(n != 1);');
			OC.L10N.load(TEST_APP, callbackStub).then(promiseStub);
			expect(callbackStub.calledOnce).toEqual(true);
			expect(promiseStub.calledOnce).toEqual(true);
			expect(fakeServer.requests.length).toEqual(0);
		});
		it('calls callback if locale is en', function() {
			var localeStub = sinon.stub(OC, 'getLocale').returns('en');
			var promiseStub = sinon.stub();
			var callbackStub = sinon.stub();
			OC.L10N.load(TEST_APP, callbackStub).then(promiseStub);
			expect(callbackStub.calledOnce).toEqual(true);
			expect(promiseStub.calledOnce).toEqual(true);
			expect(fakeServer.requests.length).toEqual(0);
		});
	});
});
