/*
 * Copyright (c) 2015
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */

/* global dav */

(function(OC, FileInfo) {
	/**
	 * @class OC.Files.Client
	 * @classdesc Client to access files on the server
	 *
	 * @param {Object} options
	 * @param {String} options.host host name including port
	 * @param {boolean} [options.useHTTPS] whether to use https
	 * @param {String} [options.root] root path
	 * @param {String} [options.userName] user name
	 * @param {String} [options.password] password
	 *
	 * @since 8.2
	 */
	var Client = function(options) {
		this._root = options.root;
		if (this._root.charAt(this._root.length - 1) === '/') {
			this._root = this._root.substr(0, this._root.length - 1);
		}

		var url = Client.PROTOCOL_HTTP + '://';
		if (options.useHTTPS) {
			url = Client.PROTOCOL_HTTPS + '://';
		}

		url += options.host + this._root;
		this._host = options.host;
		this._defaultHeaders = options.defaultHeaders || {
				'X-Requested-With': 'XMLHttpRequest',
				'requesttoken': OC.requestToken
			};
		this._baseUrl = url;

		this._rootSections = _.filter(this._root.split('/'), function(section) { return section !== '';});
		this._rootSections = _.map(this._rootSections, window.decodeURIComponent);

		var clientOptions = {
			baseUrl: this._baseUrl,
			xmlNamespaces: {
				'DAV:': 'd',
				'http://owncloud.org/ns': 'oc'
			}
		};
		if (options.userName) {
			clientOptions.userName = options.userName;
		}
		if (options.password) {
			clientOptions.password = options.password;
		}
		this._client = new dav.Client(clientOptions);
		this._client.xhrProvider = _.bind(this._xhrProvider, this);
	};

	Client.NS_OWNCLOUD = 'http://owncloud.org/ns';
	Client.NS_DAV = 'DAV:';

	Client.PROPERTY_GETLASTMODIFIED	= '{' + Client.NS_DAV + '}getlastmodified';
	Client.PROPERTY_GETETAG	= '{' + Client.NS_DAV + '}getetag';
	Client.PROPERTY_GETCONTENTTYPE	= '{' + Client.NS_DAV + '}getcontenttype';
	Client.PROPERTY_RESOURCETYPE	= '{' + Client.NS_DAV + '}resourcetype';
	Client.PROPERTY_INTERNAL_FILEID	= '{' + Client.NS_OWNCLOUD + '}fileid';
	Client.PROPERTY_PERMISSIONS	= '{' + Client.NS_OWNCLOUD + '}permissions';
	Client.PROPERTY_SIZE	= '{' + Client.NS_OWNCLOUD + '}size';
	Client.PROPERTY_GETCONTENTLENGTH	= '{' + Client.NS_DAV + '}getcontentlength';

	Client.PROTOCOL_HTTP	= 'http';
	Client.PROTOCOL_HTTPS	= 'https';

	Client._PROPFIND_PROPERTIES = [
		/**
		 * Modified time
		 */
		[Client.NS_DAV, 'getlastmodified'],
		/**
		 * Etag
		 */
		[Client.NS_DAV, 'getetag'],
		/**
		 * Mime type
		 */
		[Client.NS_DAV, 'getcontenttype'],
		/**
		 * Resource type "collection" for folders, empty otherwise
		 */
		[Client.NS_DAV, 'resourcetype'],
		/**
		 * File id
		 */
		[Client.NS_OWNCLOUD, 'fileid'],
		/**
		 * Letter-coded permissions
		 */
		[Client.NS_OWNCLOUD, 'permissions'],
		//[Client.NS_OWNCLOUD, 'downloadURL'],
		/**
		 * Folder sizes
		 */
		[Client.NS_OWNCLOUD, 'size'],
		/**
		 * File sizes
		 */
		[Client.NS_DAV, 'getcontentlength']
	];

	/**
	 * @memberof OC.Files
	 */
	Client.prototype = {

		/**
		 * Root path of the Webdav endpoint
		 *
		 * @type string
		 */
		_root: null,

		/**
		 * Client from the library
		 *
		 * @type dav.Client
		 */
		_client: null,

		/**
		 * Array of file info parsing functions.
		 *
		 * @type Array<OC.Files.Client~parseFileInfo>
		 */
		_fileInfoParsers: [],

		/**
		 * Returns the configured XHR provider for davclient
		 * @return {XMLHttpRequest}
		 */
		_xhrProvider: function() {
			var headers = this._defaultHeaders;
			var xhr = new XMLHttpRequest();
			var oldOpen = xhr.open;
			// override open() method to add headers
			xhr.open = function() {
				var result = oldOpen.apply(this, arguments);
				_.each(headers, function(value, key) {
					xhr.setRequestHeader(key, value);
				});
				return result;
			};

			OC.registerXHRForErrorProcessing(xhr);
			return xhr;
		},

		/**
		 * Prepends the base url to the given path sections
		 *
		 * @param {...String} path sections
		 *
		 * @return {String} base url + joined path, any leading or trailing slash
		 * will be kept
		 */
		_buildUrl: function() {
			var path = this._buildPath.apply(this, arguments);
			if (path.charAt([path.length - 1]) === '/') {
				path = path.substr(0, path.length - 1);
			}
			if (path.charAt(0) === '/') {
				path = path.substr(1);
			}
			return this._baseUrl + '/' + path;
		},

		/**
		 * Append the path to the root and also encode path
		 * sections
		 *
		 * @param {...String} path sections
		 *
		 * @return {String} joined path, any leading or trailing slash
		 * will be kept
		 */
		_buildPath: function() {
			var path = OC.joinPaths.apply(this, arguments);
			var sections = path.split('/');
			var i;
			for (i = 0; i < sections.length; i++) {
				sections[i] = encodeURIComponent(sections[i]);
			}
			path = sections.join('/');
			return path;
		},

		/**
		 * Parse headers string into a map
		 *
		 * @param {string} headersString headers list as string
		 *
		 * @return {Object.<String,Array>} map of header name to header contents
		 */
		_parseHeaders: function(headersString) {
			var headerRows = headersString.split('\n');
			var headers = {};
			for (var i = 0; i < headerRows.length; i++) {
				var sepPos = headerRows[i].indexOf(':');
				if (sepPos < 0) {
					continue;
				}

				var headerName = headerRows[i].substr(0, sepPos);
				var headerValue = headerRows[i].substr(sepPos + 2);

				if (!headers[headerName]) {
					// make it an array
					headers[headerName] = [];
				}

				headers[headerName].push(headerValue);
			}
			return headers;
		},

		/**
		 * Parses the etag response which is in double quotes.
		 *
		 * @param {string} etag etag value in double quotes
		 *
		 * @return {string} etag without double quotes
		 */
		_parseEtag: function(etag) {
			if (etag.charAt(0) === '"') {
				return etag.split('"')[1];
			}
			return etag;
		},

		/**
		 * Returns the relative path from the given absolute path based
		 * on this client's base URL.
		 *
		 * @param {String} path href path
		 * @return {String} sub-path section or null if base path mismatches
		 *
		 * @since 10.1.0
		 */
		getRelativePath: function(path) {
			return this._extractPath(path);
		},

		/**
		 * Parse sub-path from href
		 *
		 * @param {String} path href path
		 * @return {String} sub-path section
		 */
		_extractPath: function(path) {
			var pathSections = path.split('/');
			pathSections = _.filter(pathSections, function(section) { return section !== '';});

			var i = 0;
			for (i = 0; i < this._rootSections.length; i++) {
				if (this._rootSections[i] !== decodeURIComponent(pathSections[i])) {
					// mismatch
					return null;
				}
			}

			// build the sub-path from the remaining sections
			var subPath = '';
			while (i < pathSections.length) {
				subPath += '/' + decodeURIComponent(pathSections[i]);
				i++;
			}
			return subPath;
		},

		/**
		 * Parse Webdav result
		 *
		 * @param {Object} response XML object
		 *
		 * @return {Array.<FileInfo>} array of file info
		 */
		_parseFileInfo: function(response) {
			var path = this._extractPath(response.href);
			// invalid subpath
			if (path === null) {
				return null;
			}

			if (response.propStat.length === 0 || response.propStat[0].status !== 'HTTP/1.1 200 OK') {
				return null;
			}

			var props = response.propStat[0].properties;

			var data = {
				id: props[Client.PROPERTY_INTERNAL_FILEID],
				path: OC.dirname(path) || '/',
				name: OC.basename(path),
				mtime: (new Date(props[Client.PROPERTY_GETLASTMODIFIED])).getTime()
			};

			var etagProp = props[Client.PROPERTY_GETETAG];
			if (!_.isUndefined(etagProp)) {
				data.etag = this._parseEtag(etagProp);
			}

			var sizeProp = props[Client.PROPERTY_GETCONTENTLENGTH];
			if (!_.isUndefined(sizeProp)) {
				data.size = parseInt(sizeProp, 10);
			}

			sizeProp = props[Client.PROPERTY_SIZE];
			if (!_.isUndefined(sizeProp)) {
				data.size = parseInt(sizeProp, 10);
			}

			var contentType = props[Client.PROPERTY_GETCONTENTTYPE];
			if (!_.isUndefined(contentType)) {
				data.mimetype = contentType;
			}

			var resType = props[Client.PROPERTY_RESOURCETYPE];
			var isFile = true;
			if (!data.mimetype && resType) {
				var xmlvalue = resType[0];
				if (xmlvalue.namespaceURI === Client.NS_DAV && xmlvalue.nodeName.split(':')[1] === 'collection') {
					data.mimetype = 'httpd/unix-directory';
					isFile = false;
				}
			}

			data.permissions = OC.PERMISSION_READ;
			var permissionProp = props[Client.PROPERTY_PERMISSIONS];
			if (!_.isUndefined(permissionProp)) {
				var permString = permissionProp || '';
				data.mountType = null;
				for (var i = 0; i < permString.length; i++) {
					var c = permString.charAt(i);
					switch (c) {
						// FIXME: twisted permissions
						case 'C':
						case 'K':
							data.permissions |= OC.PERMISSION_CREATE;
							if (!isFile) {
								data.permissions |= OC.PERMISSION_UPDATE;
							}
							break;
						case 'W':
							data.permissions |= OC.PERMISSION_UPDATE;
							break;
						case 'D':
							data.permissions |= OC.PERMISSION_DELETE;
							break;
						case 'R':
							data.permissions |= OC.PERMISSION_SHARE;
							break;
						case 'M':
							if (!data.mountType) {
								// TODO: how to identify external-root ?
								data.mountType = 'external';
							}
							break;
						case 'S':
							// TODO: how to identify shared-root ?
							data.mountType = 'shared';
							break;
					}
				}
			}

			// extend the parsed data using the custom parsers
			_.each(this._fileInfoParsers, function(parserFunction) {
				_.extend(data, parserFunction(response) || {});
			});

			return new FileInfo(data);
		},

		/**
		 * Parse Webdav multistatus
		 *
		 * @param {Array} responses
		 */
		_parseResult: function(responses) {
			var self = this;
			var fileInfos = [];
			for (var i = 0; i < responses.length; i++) {
				var fileInfo = self._parseFileInfo(responses[i]);
				if (fileInfo !== null) {
					fileInfos.push(fileInfo);
				}
			}
			return fileInfos;
		},

		/**
		 * Returns whether the given status code means success
		 *
		 * @param {int} status status code
		 *
		 * @return true if status code is between 200 and 299 included
		 */
		_isSuccessStatus: function(status) {
			return status >= 200 && status <= 299;
		},

		/**
		 * Parse the Sabre exception out of the given response, if any
		 *
		 * @param {Object} response object
		 * @return {Object} array of parsed message and exception (only the first one)
		 */
		_getSabreException: function(response) {
			var result = {};
			if (!response.body) {
				return result;
			}
			var xml = response.xhr.responseXML;
			var messages = xml.getElementsByTagNameNS('http://sabredav.org/ns', 'message');
			var exceptions = xml.getElementsByTagNameNS('http://sabredav.org/ns', 'exception');
			if (messages.length) {
				result.message = messages[0].textContent;
			}
			if (exceptions.length) {
				result.exception = exceptions[0].textContent;
			}
			return result;
		},

		/**
		 * Returns the default PROPFIND properties to use during a call.
		 *
		 * @return {Array.<Object>} array of properties
		 */
		getPropfindProperties: function() {
			if (!this._propfindProperties) {
				this._propfindProperties = _.map(Client._PROPFIND_PROPERTIES, function(propDef) {
					return '{' + propDef[0] + '}' + propDef[1];
				});
			}
			return this._propfindProperties;
		},

		/**
		 * Lists the contents of a directory
		 *
		 * @param {String} path path to retrieve
		 * @param {Object} [options] options
		 * @param {boolean} [options.includeParent=false] set to true to keep
		 * the parent folder in the result list
		 * @param {Array} [options.properties] list of Webdav properties to retrieve
		 *
		 * @return {Promise} promise
		 */
		getFolderContents: function(path, options) {
			if (!path) {
				path = '';
			}
			options = options || {};
			var self = this;
			var deferred = $.Deferred();
			var promise = deferred.promise();
			var properties;
			if (_.isUndefined(options.properties)) {
				properties = this.getPropfindProperties();
			} else {
				properties = options.properties;
			}

			this._client.propFind(
				this._buildUrl(path),
				properties,
				1
			).then(function(result) {
				if (self._isSuccessStatus(result.status)) {
					var results = self._parseResult(result.body);
					if (!options || !options.includeParent) {
						// remove root dir, the first entry
						results.shift();
					}
					deferred.resolve(result.status, results);
				} else {
					result = _.extend(result, self._getSabreException(result));
					deferred.reject(result.status, result);
				}
			});
			return promise;
		},

		/**
		 * Fetches a flat list of files filtered by a given filter criteria.
		 * (currently only system tags is supported)
		 *
		 * @param {Object} filter filter criteria
		 * @param {Object} [filter.systemTagIds] list of system tag ids to filter by
		 * @param {bool} [filter.favorite] set it to filter by favorites
		 * @param {Object} [options] options
		 * @param {Array} [options.properties] list of Webdav properties to retrieve
		 *
		 * @return {Promise} promise
		 */
		getFilteredFiles: function(filter, options) {
			options = options || {};
			var self = this;
			var deferred = $.Deferred();
			var promise = deferred.promise();
			var properties;
			if (_.isUndefined(options.properties)) {
				properties = this.getPropfindProperties();
			} else {
				properties = options.properties;
			}

			if (!filter || (!filter.systemTagIds && _.isUndefined(filter.favorite))) {
				throw 'Missing filter argument';
			}

			// root element with namespaces
            var body = '<oc:filter-files ';
			var namespace;
			for (namespace in this._client.xmlNamespaces) {
				body += ' xmlns:' + this._client.xmlNamespaces[namespace] + '="' + namespace + '"';
			}
			body += '>\n';

			// properties query
			body += '    <' + this._client.xmlNamespaces['DAV:'] + ':prop>\n';
			_.each(properties, function(prop) {
				var property = self._client.parseClarkNotation(prop);
                body += '        <' + self._client.xmlNamespaces[property.namespace] + ':' + property.name + ' />\n';
			});

			body += '    </' + this._client.xmlNamespaces['DAV:'] + ':prop>\n';

			// rules block
			body +=	'    <oc:filter-rules>\n';
			_.each(filter.systemTagIds, function(systemTagIds) {
				body += '        <oc:systemtag>' + escapeHTML(systemTagIds) + '</oc:systemtag>\n';
			});
			if (filter.favorite) {
				body += '        <oc:favorite>' + (filter.favorite ? '1': '0') + '</oc:favorite>\n';
			}
			body += '    </oc:filter-rules>\n';

			// end of root
			body += '</oc:filter-files>\n';

			this._client.request(
				'REPORT',
				this._buildUrl(),
				{},
				body
			).then(function(result) {
				if (self._isSuccessStatus(result.status)) {
					var results = self._parseResult(result.body);
					deferred.resolve(result.status, results);
				} else {
					result = _.extend(result, self._getSabreException(result));
					deferred.reject(result.status, result);
				}
			});
			return promise;
		},

		/**
		 * Returns the file info of a given path.
		 *
		 * @param {String} path path
		 * @param {Array} [options.properties] list of Webdav properties to retrieve
		 *
		 * @return {Promise} promise
		 */
		getFileInfo: function(path, options) {
			if (!path) {
				path = '';
			}
			options = options || {};
			var self = this;
			var deferred = $.Deferred();
			var promise = deferred.promise();
			var properties;
			if (_.isUndefined(options.properties)) {
				properties = this.getPropfindProperties();
			} else {
				properties = options.properties;
			}

			// TODO: headers
			this._client.propFind(
				this._buildUrl(path),
				properties,
				0
			).then(
				function(result) {
					if (self._isSuccessStatus(result.status)) {
						deferred.resolve(result.status, self._parseResult([result.body])[0]);
					} else {
						result = _.extend(result, self._getSabreException(result));
						deferred.reject(result.status, result);
					}
				}
			);
			return promise;
		},

		/**
		 * Returns the contents of the given file.
		 *
		 * @param {String} path path to file
		 *
		 * @return {Promise}
		 */
		getFileContents: function(path) {
			if (!path) {
				throw 'Missing argument "path"';
			}
			var self = this;
			var deferred = $.Deferred();
			var promise = deferred.promise();

			this._client.request(
				'GET',
				this._buildUrl(path)
			).then(
				function(result) {
					if (self._isSuccessStatus(result.status)) {
						deferred.resolve(result.status, result.body);
					} else {
						result = _.extend(result, self._getSabreException(result));
						deferred.reject(result.status, result);
					}
				}
			);
			return promise;
		},

		/**
		 * Puts the given data into the given file.
		 *
		 * @param {String} path path to file
		 * @param {String} body file body
		 * @param {Object} [options]
		 * @param {String} [options.contentType='text/plain'] content type
		 * @param {bool} [options.overwrite=true] whether to overwrite an existing file
		 *
		 * @return {Promise}
		 */
		putFileContents: function(path, body, options) {
			if (!path) {
				throw 'Missing argument "path"';
			}
			var self = this;
			var deferred = $.Deferred();
			var promise = deferred.promise();
			options = options || {};
			var headers = {};
			var contentType = 'text/plain;charset=utf-8';
			if (options.contentType) {
				contentType = options.contentType;
			}

			headers['Content-Type'] = contentType;

			if (_.isUndefined(options.overwrite) || options.overwrite) {
				// will trigger 412 precondition failed if a file already exists
				headers['If-None-Match'] = '*';
			}

			this._client.request(
				'PUT',
				this._buildUrl(path),
				headers,
				body || ''
			).then(
				function(result) {
					if (self._isSuccessStatus(result.status)) {
						deferred.resolve(result.status, result);
					} else {
						result = _.extend(result, self._getSabreException(result));
						deferred.reject(result.status, result);
					}
				}
			);
			return promise;
		},

		_simpleCall: function(method, path) {
			if (!path) {
				throw 'Missing argument "path"';
			}

			var self = this;
			var deferred = $.Deferred();
			var promise = deferred.promise();

			this._client.request(
				method,
				this._buildUrl(path)
			).then(
				function(result) {
					if (self._isSuccessStatus(result.status)) {
						deferred.resolve(result.status, result);
					} else {
						result = _.extend(result, self._getSabreException(result));
						deferred.reject(result.status, result);
					}
				}
			);
			return promise;
		},

		_moveOrCopy: function(operation, path, destinationPath, allowOverwrite, headers, options) {
			if (!path) {
				throw 'Missing argument "path"';
			}
			if (!destinationPath) {
				throw 'Missing argument "destinationPath"';
			}
			if (operation !== 'MOVE' && operation !== 'COPY') {
				throw 'Invalid operation';
			}

			var self = this;
			var deferred = $.Deferred();
			var promise = deferred.promise();
			options = _.extend({
				'pathIsUrl' : false,
				'destinationPathIsUrl' : false
			}, options);
			headers = _.extend({}, headers, {
				'Destination' : options.destinationPathIsUrl ? destinationPath : this._buildUrl(destinationPath)
			});

			if (!allowOverwrite) {
				headers['Overwrite'] = 'F';
			}

			this._client.request(
				operation,
				options.pathIsUrl ? path : this._buildUrl(path),
				headers
			).then(
				function(result) {
					if (self._isSuccessStatus(result.status)) {
						deferred.resolve(result.status, result);
					} else {
						result = _.extend(result, self._getSabreException(result));
						deferred.reject(result.status, result);
					}
				}
			);
			return promise;
		},

		lock: function(path, options) {
			if (!path) {
				throw 'Missing argument "path"';
			}
			var self = this;
			var deferred = $.Deferred();
			var promise = deferred.promise();

			options = _.extend({
				'pathIsUrl' : false
			}, options);

			const lockBody = "<?xml version='1.0' encoding='UTF-8'?>\n" +
				"<d:lockinfo xmlns:d='DAV:'>\n" +
				"	<d:lockscope>\n" +
				"		<d:exclusive/>\n" +
				"	</d:lockscope>\n" +
				"</d:lockinfo>\n";

			this._client.request(
				'LOCK',
				options.pathIsUrl ? path : this._buildUrl(path),
				{},
				lockBody
			).then(
				function(result) {
					if (self._isSuccessStatus(result.status)) {
						deferred.resolve(result.status, result);
					} else {
						result = _.extend(result, self._getSabreException(result));
						deferred.reject(result.status, result);
					}
				}
			);
			return promise;
		},

		/**
		 * Creates a directory
		 *
		 * @param {String} path path to create
		 *
		 * @return {Promise}
		 */
		createDirectory: function(path) {
			return this._simpleCall('MKCOL', path);
		},

		/**
		 * Deletes a file or directory
		 *
		 * @param {String} path path to delete
		 *
		 * @return {Promise}
		 */
		remove: function(path) {
			return this._simpleCall('DELETE', path);
		},

		/**
		 * Moves path to another path
		 *
		 * @param {String} path path to move
		 * @param {String} destinationPath destination path
		 * @param {boolean} [allowOverwrite=false] true to allow overwriting,
		 * false otherwise
		 * @param {Object} [headers=null] additional headers
		 *
		 * @return {Promise} promise
		 */
		move: function(path, destinationPath, allowOverwrite, headers, options) {
			return this._moveOrCopy('MOVE', path, destinationPath, allowOverwrite, headers, options);
		},

		/**
		 * Copies path to another path
		 *
		 * @param {String} path path to copy
		 * @param {String} destinationPath destination path
		 * @param {boolean} [allowOverwrite=false] true to allow overwriting,
		 * false otherwise
		 * @param {Object} [headers=null] additional headers
		 *
		 * @return {Promise} promise
		 * @since 10.0.5
		 */
		copy: function(path, destinationPath, allowOverwrite, headers, options) {
			return this._moveOrCopy('COPY', path, destinationPath, allowOverwrite, headers, options);
		},

		/**
		 * Add a file info parser function
		 *
		 * @param {OC.Files.Client~parseFileInfo>}
		 */
		addFileInfoParser: function(parserFunction) {
			this._fileInfoParsers.push(parserFunction);
		},

		/**
		 * Returns the dav.Client instance used internally
		 *
		 * @since 10.0
		 * @return {dav.Client}
		 */
		getClient: function() {
			return this._client;
		},

		/**
		 * Returns the user name
		 *
		 * @since 10.0
		 * @return {String} userName
		 */
		getUserName: function() {
			return this._client.userName;
		},

		/**
		 * Returns the password
		 *
		 * @since 10.0
		 * @return {String} password
		 */
		getPassword: function() {
			return this._client.password;
		},

		/**
		 * Returns the base URL
		 *
		 * @since 10.0
		 * @return {String} base URL
		 */
		getBaseUrl: function() {
			return this._client.baseUrl;
		},

		/**
		 * Returns the host
		 *
		 * @since 10.0.3
		 * @return {String} base URL
		 */
		getHost: function() {
			return this._host;
		}
	};

	/**
	 * File info parser function
	 *
	 * This function receives a list of Webdav properties as input and
	 * should return a hash array of parsed properties, if applicable.
	 *
	 * @callback OC.Files.Client~parseFileInfo
	 * @param {Object} XML Webdav properties
     * @return {Array} array of parsed property values
	 */

	if (!OC.Files) {
		/**
		 * @namespace OC.Files
		 *
		 * @since 8.2
		 */
		OC.Files = {};
	}

	/**
	 * Returns the default instance of the files client
	 *
	 * @return {OC.Files.Client} default client
	 *
	 * @since 8.2
	 */
	OC.Files.getClient = function() {
		if (OC.Files._defaultClient) {
			return OC.Files._defaultClient;
		}

		var client = new OC.Files.Client({
			host: OC.getHost(),
			root: OC.linkToRemoteBase('dav') + '/files/' + encodeURIComponent(OC.getCurrentUser().uid) + '/',
			useHTTPS: OC.getProtocol() === 'https'
		});
		OC.Files._defaultClient = client;
		return client;
	};

	OC.Files.Client = Client;
})(OC, OC.Files.FileInfo);
