/**
* ownCloud
*
* @author Vincent Petry
* @copyright Copyright (c) 2014 Vincent Petry <pvince81@owncloud.com>
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

/* global dav */

describe('Backbone Webdav extension', function() {
	var davClientRequestStub;
	var davClientPropPatchStub;
	var davClientPropFindStub;
	var deferredRequest;

	beforeEach(function() {
		deferredRequest = $.Deferred();
		davClientRequestStub = sinon.stub(dav.Client.prototype, 'request');
		davClientPropPatchStub = sinon.stub(dav.Client.prototype, 'propPatch');
		davClientPropFindStub = sinon.stub(dav.Client.prototype, 'propFind');
		davClientRequestStub.returns(deferredRequest.promise());
		davClientPropPatchStub.returns(deferredRequest.promise());
		davClientPropFindStub.returns(deferredRequest.promise());
	});
	afterEach(function() {
		davClientRequestStub.restore();
		davClientPropPatchStub.restore();
		davClientPropFindStub.restore();
	});

	describe('collections', function() {
		var TestModel;
		var TestCollection;
		beforeEach(function() {
			TestModel = OC.Backbone.Model.extend({
				sync: OC.Backbone.davSync,
				davProperties: {
					'firstName': '{http://owncloud.org/ns}first-name',
					'lastName': '{http://owncloud.org/ns}last-name',
					'age': '{http://owncloud.org/ns}age',
					'married': '{http://owncloud.org/ns}married'
				},
				parse: function(data) {
					return {
						id: data.id,
						firstName: data.firstName,
						lastName: data.lastName,
						age: parseInt(data.age, 10),
						married: data.married === 'true' || data.married === true
					};
				}
			});
			TestCollection = OC.Backbone.Collection.extend({
				sync: OC.Backbone.davSync,
				model: TestModel,
				url: 'http://example.com/owncloud/remote.php/test/'
			});
		});

		it('makes a POST request to create model into collection', function() {
			var collection = new TestCollection();
			var model = collection.create({
				firstName: 'Hello',
				lastName: 'World'
			});

			expect(davClientRequestStub.calledOnce).toEqual(true);
			expect(davClientRequestStub.getCall(0).args[0])
				.toEqual('POST');
			expect(davClientRequestStub.getCall(0).args[1])
				.toEqual('http://example.com/owncloud/remote.php/test/');
			expect(davClientRequestStub.getCall(0).args[2]['Content-Type'])
				.toEqual('application/json');
			expect(davClientRequestStub.getCall(0).args[2]['X-Requested-With'])
				.toEqual('XMLHttpRequest');
			expect(davClientRequestStub.getCall(0).args[3])
				.toEqual(JSON.stringify({
					'firstName': 'Hello',
					'lastName': 'World'
				}));

			var responseHeaderStub = sinon.stub()
				.withArgs('Content-Location')
				.returns('http://example.com/owncloud/remote.php/test/123');
			deferredRequest.resolve({
				status: 201,
				body: '',
				xhr: {
					getResponseHeader: responseHeaderStub
				}
			});

			expect(model.id).toEqual('123');
		});

		it('uses PROPFIND to retrieve collection', function() {
			var successStub = sinon.stub();
			var errorStub = sinon.stub();
			var collection = new TestCollection();
			collection.fetch({
				success: successStub,
				error: errorStub
			});

			expect(davClientPropFindStub.calledOnce).toEqual(true);
			expect(davClientPropFindStub.getCall(0).args[0])
				.toEqual('http://example.com/owncloud/remote.php/test/');
			expect(davClientPropFindStub.getCall(0).args[1])
				.toEqual([
					'{http://owncloud.org/ns}first-name',
					'{http://owncloud.org/ns}last-name',
					'{http://owncloud.org/ns}age',
					'{http://owncloud.org/ns}married'
				]);
			expect(davClientPropFindStub.getCall(0).args[2])
				.toEqual(1);
			expect(davClientPropFindStub.getCall(0).args[3]['X-Requested-With'])
				.toEqual('XMLHttpRequest');

			deferredRequest.resolve({
				status: 207,
				body: [
					// root element
					{
						href: 'http://example.org/owncloud/remote.php/test/',
						propStat: []
					},
					// first model
					{
						href: 'http://example.org/owncloud/remote.php/test/123',
						propStat: [{
							status: 'HTTP/1.1 200 OK',
							properties: {
								'{http://owncloud.org/ns}first-name': 'Hello',
								'{http://owncloud.org/ns}last-name': 'World'
							}
						}]
					},
					// second model
					{
						href: 'http://example.org/owncloud/remote.php/test/456',
						propStat: [{
							status: 'HTTP/1.1 200 OK',
							properties: {
								'{http://owncloud.org/ns}first-name': 'Test',
								'{http://owncloud.org/ns}last-name': 'Person'
							}
						}]
					}
				]
			});

			expect(collection.length).toEqual(2);

			var model = collection.get('123');
			expect(model.id).toEqual('123');
			expect(model.get('firstName')).toEqual('Hello');
			expect(model.get('lastName')).toEqual('World');

			model = collection.get('456');
			expect(model.id).toEqual('456');
			expect(model.get('firstName')).toEqual('Test');
			expect(model.get('lastName')).toEqual('Person');

			expect(successStub.calledOnce).toEqual(true);
			expect(errorStub.notCalled).toEqual(true);
		});

		function testMethodError(doCall) {
			var successStub = sinon.stub();
			var errorStub = sinon.stub();

			doCall(successStub, errorStub);

			deferredRequest.resolve({
				status: 404,
				body: ''
			});

			expect(successStub.notCalled).toEqual(true);
			expect(errorStub.calledOnce).toEqual(true);
		}

		it('calls error handler if error status in PROPFIND response', function() {
			testMethodError(function(success, error) {
				var collection = new TestCollection();
				collection.fetch({
					success: success,
					error: error
				});
			});
		});
		it('calls error handler if error status in POST response', function() {
			testMethodError(function(success, error) {
				var collection = new TestCollection();
				collection.create({
					firstName: 'Hello',
					lastName: 'World'
				}, {
					success: success,
					error: error
				});
			});
		});
	});
	describe('models', function() {
		var TestModel;
		beforeEach(function() {
			TestModel = OC.Backbone.Model.extend({
				sync: OC.Backbone.davSync,
				davProperties: {
					'firstName': '{http://owncloud.org/ns}first-name',
					'lastName': '{http://owncloud.org/ns}last-name',
					'age': '{http://owncloud.org/ns}age', // int
					'married': '{http://owncloud.org/ns}married', // bool
				},
				url: function() {
					return 'http://example.com/owncloud/remote.php/test/' + encodeURIComponent(this.id);
				},
				parse: function(data) {
					return {
						id: data.id,
						firstName: data.firstName,
						lastName: data.lastName,
						age: parseInt(data.age, 10),
						married: data.married === 'true' || data.married === true
					};
				}
			});
		});

		describe('updating', function() {
			it('makes a PROPPATCH request to update model', function() {
				var model = new TestModel({
					id: '123',
					firstName: 'Hello',
					lastName: 'World',
					age: 32,
					married: false
				});

				model.save({
					firstName: 'Hey',
					age: 33,
					married: true
				});

				expect(davClientPropPatchStub.calledOnce).toEqual(true);
				expect(davClientPropPatchStub.getCall(0).args[0])
					.toEqual('http://example.com/owncloud/remote.php/test/123');
				expect(davClientPropPatchStub.getCall(0).args[1])
					.toEqual({
						'{http://owncloud.org/ns}first-name': 'Hey',
						'{http://owncloud.org/ns}age': '33',
						'{http://owncloud.org/ns}married': 'true'
					});
				expect(davClientPropPatchStub.getCall(0).args[2]['X-Requested-With'])
					.toEqual('XMLHttpRequest');

				deferredRequest.resolve({
					status: 207,
					body: [{
						href: 'http://example.com/owncloud/remote.php/test/123',
						propStat: [{
							status: 'HTTP/1.1 200 OK',
							properties: {
								'{http://owncloud.org/ns}first-name': '',
								'{http://owncloud.org/ns}age-name': '',
								'{http://owncloud.org/ns}married': ''
							}
						}]
					}]
				});

				expect(model.id).toEqual('123');
				expect(model.get('firstName')).toEqual('Hey');
				expect(model.get('age')).toEqual(33);
				expect(model.get('married')).toEqual(true);
			});

			it('calls error callback with status code 422 in case of failed PROPPATCH properties', function() {
				var successHandler = sinon.stub();
				var errorHandler = sinon.stub();
				var model = new TestModel({
					id: '123',
					firstName: 'Hello',
					lastName: 'World',
					age: 32,
					married: false
				});

				model.save({
					firstName: 'Hey',
					lastName: 'low'
				}, {
					success: successHandler,
					error: errorHandler
				});

				deferredRequest.resolve({
					status: 207,
					body: [{
						href: 'http://example.com/owncloud/remote.php/test/123',
						propStat: [{
							status: 'HTTP/1.1 200 OK',
							properties: {
								'{http://owncloud.org/ns}last-name': ''
							}
						}, {
							status: 'HTTP/1.1 403 Forbidden',
							properties: {
								'{http://owncloud.org/ns}first-name': ''
							}
						}]
					}]
				});

				expect(davClientPropPatchStub.calledOnce).toEqual(true);

				expect(successHandler.notCalled).toEqual(true);
				expect(errorHandler.calledOnce).toEqual(true);
				expect(errorHandler.getCall(0).args[0]).toEqual(model);
				expect(errorHandler.getCall(0).args[1].status).toEqual(422);
			});

			it('calls error handler if error status in PROPPATCH response', function() {
				testMethodError(function(success, error) {
					var model = new TestModel();
					model.save({
						firstName: 'Hey'
					}, {
						success: success,
						error: error
					});
				});
			});

			it('sends all data when using wait flag', function() {
				var successHandler = sinon.stub();
				var errorHandler = sinon.stub();
				var model = new TestModel({
					id: '123',
					firstName: 'Hello',
					lastName: 'World',
					age: 32,
					married: false
				});

				model.save({
					firstName: 'Hey',
					lastName: 'low'
				}, {
					wait: true,
					success: successHandler,
					error: errorHandler
				});

				// attributes not updated yet
				expect(model.get('firstName')).toEqual('Hello');

				deferredRequest.resolve({
					status: 207,
					body: [{
						href: 'http://example.com/owncloud/remote.php/test/123',
						propStat: [{
							status: 'HTTP/1.1 200 OK',
							properties: {
								'{http://owncloud.org/ns}first-name': '',
								'{http://owncloud.org/ns}last-name': ''
							}
						}]
					}]
				});


				expect(davClientPropPatchStub.calledOnce).toEqual(true);
				// just resends everything
				expect(davClientPropPatchStub.getCall(0).args[1])
					.toEqual({
						'{http://owncloud.org/ns}first-name': 'Hey',
						'{http://owncloud.org/ns}last-name': 'low',
						'{http://owncloud.org/ns}age': '32',
						'{http://owncloud.org/ns}married': 'false',
					});

				expect(model.get('firstName')).toEqual('Hey');
				expect(successHandler.calledOnce).toEqual(true);
				expect(errorHandler.notCalled).toEqual(true);
			});
		});

		it('uses PROPFIND to fetch single model', function() {
			var model = new TestModel({
				id: '123'
			});

			model.fetch();

			expect(davClientPropFindStub.calledOnce).toEqual(true);
			expect(davClientPropFindStub.getCall(0).args[0])
				.toEqual('http://example.com/owncloud/remote.php/test/123');
			expect(davClientPropFindStub.getCall(0).args[1])
				.toEqual([
					'{http://owncloud.org/ns}first-name',
					'{http://owncloud.org/ns}last-name',
					'{http://owncloud.org/ns}age',
					'{http://owncloud.org/ns}married'
				]);
			expect(davClientPropFindStub.getCall(0).args[2])
				.toEqual(0);
			expect(davClientPropFindStub.getCall(0).args[3]['X-Requested-With'])
				.toEqual('XMLHttpRequest');

			deferredRequest.resolve({
				status: 207,
				body: {
					href: 'http://example.org/owncloud/remote.php/test/123',
					propStat: [{
						status: 'HTTP/1.1 200 OK',
						properties: {
							'{http://owncloud.org/ns}first-name': 'Hello',
							'{http://owncloud.org/ns}last-name': 'World',
							'{http://owncloud.org/ns}age': '35',
							'{http://owncloud.org/ns}married': 'true'
						}
					}]
				}
			});

			expect(model.id).toEqual('123');
			expect(model.get('firstName')).toEqual('Hello');
			expect(model.get('lastName')).toEqual('World');
			expect(model.get('age')).toEqual(35);
			expect(model.get('married')).toEqual(true);
		});
		it('makes a DELETE request to destroy model', function() {
			var model = new TestModel({
				id: '123',
				firstName: 'Hello',
				lastName: 'World'
			});

			model.destroy();

			expect(davClientRequestStub.calledOnce).toEqual(true);
			expect(davClientRequestStub.getCall(0).args[0])
				.toEqual('DELETE');
			expect(davClientRequestStub.getCall(0).args[1])
				.toEqual('http://example.com/owncloud/remote.php/test/123');
			expect(davClientRequestStub.getCall(0).args[2]['X-Requested-With'])
				.toEqual('XMLHttpRequest');
			expect(davClientRequestStub.getCall(0).args[3])
				.toBeFalsy();

			deferredRequest.resolve({
				status: 200,
				body: ''
			});
		});

		function testMethodError(doCall) {
			var successStub = sinon.stub();
			var errorStub = sinon.stub();

			doCall(successStub, errorStub);

			deferredRequest.resolve({
				status: 404,
				body: ''
			});

			expect(successStub.notCalled).toEqual(true);
			expect(errorStub.calledOnce).toEqual(true);
		}

		it('calls error handler if error status in PROPFIND response', function() {
			testMethodError(function(success, error) {
				var model = new TestModel();
				model.fetch({
					success: success,
					error: error
				});
			});
		});
	});


	describe('WebdavNode', function() {
		var NodeModel;

		beforeEach(function() {
			NodeModel = OC.Backbone.WebdavNode.extend({
				url: function() {
					return 'http://example.com/owncloud/remote.php/dav/endpoint/nodemodel/' + encodeURIComponent(this.id);
				},
				davProperties: {
					'firstName': '{http://owncloud.org/ns}first-name',
					'lastName': '{http://owncloud.org/ns}last-name'
				}
			});
		});
		it('isNew at creation time even with an id set', function() {
			var model = new NodeModel({
				id: 'someuri'
			});
			expect(model.isNew()).toEqual(true);
		});
		it('is not new as soon as fetched', function() {
			var model = new NodeModel({
				id: 'someuri'
			});
			model.fetch();

			expect(davClientPropFindStub.calledOnce).toEqual(true);
			expect(davClientPropFindStub.getCall(0).args[0])
				.toEqual('http://example.com/owncloud/remote.php/dav/endpoint/nodemodel/someuri');
			expect(davClientPropFindStub.getCall(0).args[1])
				.toEqual([
					'{http://owncloud.org/ns}first-name',
					'{http://owncloud.org/ns}last-name'
				]);
			expect(davClientPropFindStub.getCall(0).args[2])
				.toEqual(0);
			expect(davClientPropFindStub.getCall(0).args[3]['X-Requested-With'])
				.toEqual('XMLHttpRequest');

			deferredRequest.resolve({
				status: 207,
				body: {
					href: 'http://example.org/owncloud/remote.php/dav/endpoint/nodemodel/someuri',
					propStat: [{
						status: 'HTTP/1.1 200 OK',
						properties: {
							'{http://owncloud.org/ns}first-name': 'Hello',
							'{http://owncloud.org/ns}last-name': 'World'
						}
					}]
				}
			});
			expect(model.isNew()).toEqual(false);
		});
		it('saves new model with PUT', function() {
			var model = new NodeModel({
				id: 'someuri'
			});
			model.save({
				firstName: 'Hello',
				lastName: 'World',
			});

			// PUT
			expect(davClientRequestStub.calledOnce).toEqual(true);
			expect(davClientRequestStub.getCall(0).args[0])
				.toEqual('PUT');
			expect(davClientRequestStub.getCall(0).args[1])
				.toEqual('http://example.com/owncloud/remote.php/dav/endpoint/nodemodel/someuri');
			expect(davClientRequestStub.getCall(0).args[2]['X-Requested-With'])
				.toEqual('XMLHttpRequest');
			expect(davClientRequestStub.getCall(0).args[3])
				.toEqual(JSON.stringify({
					id: 'someuri',
					firstName: 'Hello',
					lastName: 'World'
				}));

			deferredRequest.resolve({
				status: 201,
				body: '',
				xhr: {
					getResponseHeader: _.noop
				}
			});

			expect(model.id).toEqual('someuri');
			expect(model.isNew()).toEqual(false);
		});
		it('updates existing model with PROPPATCH', function() {
			var model = new NodeModel({
				id: 'someuri'
			});

			model.fetch();

			// from here, the model will exist
			expect(davClientPropFindStub.calledOnce).toEqual(true);
			expect(davClientPropFindStub.getCall(0).args[0])
				.toEqual('http://example.com/owncloud/remote.php/dav/endpoint/nodemodel/someuri');
			expect(davClientPropFindStub.getCall(0).args[1])
				.toEqual([
					'{http://owncloud.org/ns}first-name',
					'{http://owncloud.org/ns}last-name'
				]);
			expect(davClientPropFindStub.getCall(0).args[2])
				.toEqual(0);
			expect(davClientPropFindStub.getCall(0).args[3]['X-Requested-With'])
				.toEqual('XMLHttpRequest');

			deferredRequest.resolve({
				status: 207,
				body: {
					href: 'http://example.com/owncloud/remote.php/dav/endpoint/nodemodel/someuri',
					propStat: [{
						status: 'HTTP/1.1 200 OK',
						properties: {
							'{http://owncloud.org/ns}first-name': 'Hello',
							'{http://owncloud.org/ns}last-name': 'World'
						}
					}]
				}
			});

			expect(model.isNew()).toEqual(false);

			model.save({
				firstName: 'Hey',
			});

			expect(davClientPropPatchStub.calledOnce).toEqual(true);
			expect(davClientPropPatchStub.getCall(0).args[0])
				.toEqual('http://example.com/owncloud/remote.php/dav/endpoint/nodemodel/someuri');
			expect(davClientPropPatchStub.getCall(0).args[1])
				.toEqual({
					'{http://owncloud.org/ns}first-name': 'Hey'
				});
			expect(davClientPropPatchStub.getCall(0).args[2]['X-Requested-With'])
				.toEqual('XMLHttpRequest');

			deferredRequest.resolve({
				status: 201,
				body: '',
				xhr: {
					getResponseHeader: _.noop
				}
			});

			expect(model.isNew()).toEqual(false);
		});
	});

	describe('WebdavCollectionNode and WebdavChildrenCollection', function() {
		var NodeModel;
		var ChildrenCollection;

		beforeEach(function() {
			ChildModel = OC.Backbone.WebdavNode.extend({
				url: function() {
					return 'http://example.com/owncloud/remote.php/dav/davcol/' + encodeURIComponent(this.id);
				},
				davProperties: {
					'firstName': '{http://owncloud.org/ns}first-name',
					'lastName': '{http://owncloud.org/ns}last-name'
				}
			});
			ChildrenCollection = OC.Backbone.WebdavChildrenCollection.extend({
				model: ChildModel
			});

			NodeModel = OC.Backbone.WebdavCollectionNode.extend({
				childrenCollectionClass: ChildrenCollection,
				url: function() {
					return 'http://example.com/owncloud/remote.php/dav/' + encodeURIComponent(this.id);
				},
				davProperties: {
					'firstName': '{http://owncloud.org/ns}first-name',
					'lastName': '{http://owncloud.org/ns}last-name'
				}
			});
		});

		it('returns the children collection pointing to the same url', function() {
			var model = new NodeModel({
				id: 'davcol'
			});

			var collection = model.getChildrenCollection();
			expect(collection instanceof ChildrenCollection).toEqual(true);

			collection.instanceCheck = true;

			// returns the same instance
			var collection2 = model.getChildrenCollection();
			expect(collection2.instanceCheck).toEqual(true);

			expect(collection.url()).toEqual(model.url());
		});

		it('resets isNew to false for every model after fetching', function() {
			var model = new NodeModel({
				id: 'davcol'
			});

			var collection = model.getChildrenCollection();
			collection.fetch();

			expect(davClientPropFindStub.calledOnce).toEqual(true);
			expect(davClientPropFindStub.getCall(0).args[0])
				.toEqual('http://example.com/owncloud/remote.php/dav/davcol');
			expect(davClientPropFindStub.getCall(0).args[1])
				.toEqual([
					'{http://owncloud.org/ns}first-name',
					'{http://owncloud.org/ns}last-name'
				]);
			expect(davClientPropFindStub.getCall(0).args[2])
				.toEqual(1);
			expect(davClientPropFindStub.getCall(0).args[3]['X-Requested-With'])
				.toEqual('XMLHttpRequest');

			deferredRequest.resolve({
				status: 207,
				body: [
					// root element
					{
						href: 'http://example.com/owncloud/remote.php/dav/davcol/',
						propStat: []
					},
					// first model
					{
						href: 'http://example.com/owncloud/remote.php/dav/davcol/hello',
						propStat: [{
							status: 'HTTP/1.1 200 OK',
							properties: {
								'{http://owncloud.org/ns}first-name': 'Hello',
								'{http://owncloud.org/ns}last-name': 'World'
							}
						}]
					},
					// second model
					{
						href: 'http://example.com/owncloud/remote.php/dav/davcol/test',
						propStat: [{
							status: 'HTTP/1.1 200 OK',
							properties: {
								'{http://owncloud.org/ns}first-name': 'Test',
								'{http://owncloud.org/ns}last-name': 'Person'
							}
						}]
					}
				]
			});

			expect(collection.length).toEqual(2);

			expect(collection.at(0).url()).toEqual('http://example.com/owncloud/remote.php/dav/davcol/hello');
			expect(collection.at(0).isNew()).toEqual(false);
			expect(collection.at(1).url()).toEqual('http://example.com/owncloud/remote.php/dav/davcol/test');
			expect(collection.at(1).isNew()).toEqual(false);
		});

		it('parses id from href if no id was queried', function() {
			var model = new NodeModel({
				id: 'davcol'
			});

			var collection = model.getChildrenCollection();
			collection.fetch();

			deferredRequest.resolve({
				status: 207,
				body: [
					// root element
					{
						href: 'http://example.com/owncloud/remote.php/dav/davcol/',
						propStat: []
					},
					// first model
					{
						href: 'http://example.com/owncloud/remote.php/dav/davcol/sub%40thing',
						propStat: [{
							status: 'HTTP/1.1 200 OK',
							properties: {
								'{http://owncloud.org/ns}first-name': 'Hello',
								'{http://owncloud.org/ns}last-name': 'World'
							}
						}]
					}
				]
			});

			expect(collection.length).toEqual(1);

			expect(collection.at(0).id).toEqual('sub@thing');
			expect(collection.at(0).url()).toEqual('http://example.com/owncloud/remote.php/dav/davcol/sub%40thing');
		});

		it('creates the Webdav collection with MKCOL', function() {
			var mkcolStub = sinon.stub(dav.Client.prototype, 'mkcol');
			mkcolStub.returns(deferredRequest.promise());
			var model = new NodeModel({
				id: 'davcol'
			});
			model.save({
				firstName: 'Hello',
				lastName: 'World',
			});

			expect(mkcolStub.calledOnce).toEqual(true);
			expect(mkcolStub.getCall(0).args[0])
				.toEqual('http://example.com/owncloud/remote.php/dav/davcol');
			expect(mkcolStub.getCall(0).args[1])
				.toEqual({
					'{http://owncloud.org/ns}first-name': 'Hello',
					'{http://owncloud.org/ns}last-name': 'World',
					'{DAV:}resourcetype': '<d:collection/>'
				});
			expect(mkcolStub.getCall(0).args[2]['X-Requested-With'])
				.toEqual('XMLHttpRequest');

			deferredRequest.resolve({
				status: 201,
				body: '',
				xhr: {
					getResponseHeader: _.noop
				}
			});

			expect(model.id).toEqual('davcol');
			expect(model.isNew()).toEqual(false);

			mkcolStub.restore();
		});
		it('updates Webdav collection properties with PROPPATCH', function() {
			var model = new NodeModel({
				id: 'davcol'
			});

			model.fetch();

			// from here, the model will exist
			expect(davClientPropFindStub.calledOnce).toEqual(true);
			expect(davClientPropFindStub.getCall(0).args[0])
				.toEqual('http://example.com/owncloud/remote.php/dav/davcol');
			expect(davClientPropFindStub.getCall(0).args[1])
				.toEqual([
					'{http://owncloud.org/ns}first-name',
					'{http://owncloud.org/ns}last-name'
				]);
			expect(davClientPropFindStub.getCall(0).args[2])
				.toEqual(0);
			expect(davClientPropFindStub.getCall(0).args[3]['X-Requested-With'])
				.toEqual('XMLHttpRequest');

			deferredRequest.resolve({
				status: 207,
				body: {
					href: 'http://example.com/owncloud/remote.php/dav/davcol',
					propStat: [{
						status: 'HTTP/1.1 200 OK',
						properties: {
							'{http://owncloud.org/ns}first-name': 'Hello',
							'{http://owncloud.org/ns}last-name': 'World'
						}
					}]
				}
			});

			expect(model.isNew()).toEqual(false);

			model.save({
				firstName: 'Hey',
			});

			expect(davClientPropPatchStub.calledOnce).toEqual(true);
			expect(davClientPropPatchStub.getCall(0).args[0])
				.toEqual('http://example.com/owncloud/remote.php/dav/davcol');
			expect(davClientPropPatchStub.getCall(0).args[1])
				.toEqual({
					'{http://owncloud.org/ns}first-name': 'Hey'
				});
			expect(davClientPropPatchStub.getCall(0).args[2]['X-Requested-With'])
				.toEqual('XMLHttpRequest');

			deferredRequest.resolve({
				status: 201,
				body: [{
					propStat: {
						status: 'HTTP/1.1 200 OK'
					}
				}],
				xhr: {
					getResponseHeader: _.noop
				}
			});

			expect(model.isNew()).toEqual(false);
		});
	});

	describe('custom method', function() {
		var TestModel;

		beforeEach(function() {
			TestModel = OC.Backbone.Model.extend({
				url: 'http://example.com/owncloud/remote.php/test/1',
				sync: OC.Backbone.davSync,
				customCall: function(data, options) {
					options = _.extend({}, options);
					options.data = data;
					return this.sync('REPORT', this, options);
				}
			});
		});

		it('serializes JSON if a JS object is passed as data', function() {
			var model = new TestModel();
			model.customCall({someData: '123'});

			expect(davClientRequestStub.calledOnce).toEqual(true);
			expect(davClientRequestStub.getCall(0).args[0])
				.toEqual('REPORT');
			expect(davClientRequestStub.getCall(0).args[1])
				.toEqual('http://example.com/owncloud/remote.php/test/1');
			expect(davClientRequestStub.getCall(0).args[2]['Content-Type'])
				.toEqual('application/json');
			expect(davClientRequestStub.getCall(0).args[2]['X-Requested-With'])
				.toEqual('XMLHttpRequest');
			expect(davClientRequestStub.getCall(0).args[3])
				.toEqual(JSON.stringify({someData: '123'}));
		});
		it('does not serializes JSON if a string is passed as data', function() {
			var model = new TestModel();
			model.customCall('whatever');

			expect(davClientRequestStub.calledOnce).toEqual(true);
			expect(davClientRequestStub.getCall(0).args[0])
				.toEqual('REPORT');
			expect(davClientRequestStub.getCall(0).args[1])
				.toEqual('http://example.com/owncloud/remote.php/test/1');
			expect(davClientRequestStub.getCall(0).args[2]['Content-Type'])
				.toEqual('text/plain');
			expect(davClientRequestStub.getCall(0).args[2]['X-Requested-With'])
				.toEqual('XMLHttpRequest');
			expect(davClientRequestStub.getCall(0).args[3])
				.toEqual('whatever');
		});
		it('sends XML mime type when passed data string is XML', function() {
			var model = new TestModel();
			var body = '<?xml version="1.0" encoding="utf-8" ?>';
			body += '<root></root>';
			model.customCall(body);

			expect(davClientRequestStub.calledOnce).toEqual(true);
			expect(davClientRequestStub.getCall(0).args[0])
				.toEqual('REPORT');
			expect(davClientRequestStub.getCall(0).args[1])
				.toEqual('http://example.com/owncloud/remote.php/test/1');
			expect(davClientRequestStub.getCall(0).args[2]['Content-Type'])
				.toEqual('application/xml');
			expect(davClientRequestStub.getCall(0).args[2]['X-Requested-With'])
				.toEqual('XMLHttpRequest');
			expect(davClientRequestStub.getCall(0).args[3])
				.toEqual(body);
		});
	});
});

