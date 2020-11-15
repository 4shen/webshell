<?php

/*

Modification information for LGPL compliance

r57813 - 2010-08-19 10:34:44 -0700 (Thu, 19 Aug 2010) - kjing - Author: John Mertic <jmertic@sugarcrm.com>
    Bug 39085 - When loading the opposite search panel via ajax on the ListViews, call the index action instead of the ListView action to avoid touching pre-MVC code by accident.

r56990 - 2010-06-16 13:05:36 -0700 (Wed, 16 Jun 2010) - kjing - snapshot "Mango" svn branch to a new one for GitHub sync

r56989 - 2010-06-16 13:01:33 -0700 (Wed, 16 Jun 2010) - kjing - defunt "Mango" svn dev branch before github cutover

r55980 - 2010-04-19 13:31:28 -0700 (Mon, 19 Apr 2010) - kjing - create Mango (6.1) based on windex

r51719 - 2009-10-22 10:18:00 -0700 (Thu, 22 Oct 2009) - mitani - Converted to Build 3  tags and updated the build system

r51634 - 2009-10-19 13:32:22 -0700 (Mon, 19 Oct 2009) - mitani - Windex is the branch for Sugar Sales 1.0 development

r51443 - 2009-10-12 13:34:36 -0700 (Mon, 12 Oct 2009) - jmertic - Bug 33332 - Made application PHP 5.3 compliant with E_DEPRECATED warnings on by:
- Changing all ereg function to either preg or simple string based ones
- No more references to magic quotes.
- Change all the session_unregister() functions to just unset() the correct session variable instead.

r50375 - 2009-08-24 18:07:43 -0700 (Mon, 24 Aug 2009) - dwong - branch kobe2 from tokyo r50372

r42807 - 2008-12-29 11:16:59 -0800 (Mon, 29 Dec 2008) - dwong - Branch from trunk/sugarcrm r42806 to branches/tokyo/sugarcrm

r39619 - 2008-09-09 13:41:34 -0700 (Tue, 09 Sep 2008) - jmertic - Bug 24827 - Remove all instances where we return a new object and assign it by reference, since this is deprecated in PHP 5 and emits E_DEPRECATED errors in PHP 5.3.
Touched:
- data/SugarBean.php
- include/domit/php_http_client_generic.php
- include/domit/php_http_connector.php
- include/domit/testing_domit.php
- include/domit/xml_domit_getelementsbypath.php
- include/domit/xml_domit_lite_parser.php
- include/domit/xml_domit_nodemaps.php
- include/domit/xml_domit_parser.php
- include/domit/xml_domit_shared.php
- include/generic/SugarWidgets/SugarWidgetField.php
- include/generic/SugarWidgets/SugarWidgetReportField.php
- include/ListView/ProcessView.php
- include/nusoap/class.soapclient.php
- include/nusoap/nusoap.php
- include/nusoap/nusoapmime.php
- include/Pear/HTML_Safe/Safe.php
- include/Pear/XML_HTMLSax3/HTMLSax3.php
- modules/Administration/RebuildWorkFlow.php
- modules/Expressions/RelateSelector.php
- modules/Reports/templates/templates_reports.php
- modules/WorkFlow/Delete.php
- modules/WorkFlow/Save.php
- modules/WorkFlow/SaveSequence.php
- modules/WorkFlow/WorkFlow.php
- modules/WorkFlowActionShells/CreateStep1.php
- modules/WorkFlowActionShells/CreateStep2.php
- modules/WorkFlowActionShells/Save.php
- modules/WorkFlowActionShells/WorkFlowActionShell.php
- modules/WorkFlowAlerts/Save.php
- modules/WorkFlowAlerts/WorkFlowAlert.php
- modules/WorkFlowAlertShells/DetailView.php
- modules/WorkFlowAlertShells/WorkFlowAlertShell.php
- modules/WorkFlowTriggerShells/CreateStep1.php
- modules/WorkFlowTriggerShells/CreateStepFilter.php
- modules/WorkFlowTriggerShells/SaveFilter.php
- modules/WorkFlowTriggerShells/WorkFlowTriggerShell.php
- soap/SoapHelperFunctions.php
- test/modules/DynamicFields/DynamicFields_Bug24095_test.php
- test/simpletest/browser.php
- test/simpletest/default_reporter.php
- test/simpletest/detached.php
- test/simpletest/eclipse.php
- test/simpletest/expectation.php
- test/simpletest/extensions/pear_test_case.php
- test/simpletest/form.php
- test/simpletest/http.php
- test/simpletest/mock_objects.php
- test/simpletest/page.php
- test/simpletest/parser.php
- test/simpletest/remote.php
- test/simpletest/shell_tester.php
- test/simpletest/simple_test.php
- test/simpletest/simpletest.php
- test/simpletest/test/acceptance_test.php
- test/simpletest/test/adapter_test.php
- test/simpletest/test/authentication_test.php
- test/simpletest/test/browser_test.php
- test/simpletest/test/collector_test.php
- test/simpletest/test/compatibility_test.php
- test/simpletest/test/detached_test.php
- test/simpletest/test/eclipse_test.php
- test/simpletest/test/encoding_test.php
- test/simpletest/test/errors_test.php
- test/simpletest/test/expectation_test.php
- test/simpletest/test/form_test.php
- test/simpletest/test/frames_test.php
- test/simpletest/test/http_test.php
- test/simpletest/test/live_test.php
- test/simpletest/test/mock_objects_test.php
- test/simpletest/test/page_test.php
- test/simpletest/test/parse_error_test.php
- test/simpletest/test/parser_test.php
- test/simpletest/test/remote_test.php
- test/simpletest/test/shell_test.php
- test/simpletest/test/shell_tester_test.php
- test/simpletest/test/simpletest_test.php
- test/simpletest/test/site/page_request.php
- test/simpletest/test/tag_test.php
- test/simpletest/test/unit_tester_test.php
- test/simpletest/test/user_agent_test.php
- test/simpletest/test/visual_test.php
- test/simpletest/test/xml_test.php
- test/simpletest/test_case.php
- test/simpletest/ui/array_reporter/test.php
- test/simpletest/ui/recorder/test.php
- test/simpletest/unit_tester.php
- test/simpletest/url.php
- test/simpletest/user_agent.php
- test/simpletest/web_tester.php
- test/spikephpcoverage/src/PEAR.php
- test/spikephpcoverage/src/util/Utility.php
- test/spikephpcoverage/src/XML/Parser.php
- test/spikephpcoverage/src/XML/Parser/Simple.php
- test/test_utilities/SugarTest_SimpleBrowser.php

r13782 - 2006-06-06 10:58:55 -0700 (Tue, 06 Jun 2006) - majed - changes entry point code

r11115 - 2006-01-17 14:54:45 -0800 (Tue, 17 Jan 2006) - majed - add entry point validation

r8991 - 2005-11-03 19:07:25 -0800 (Thu, 03 Nov 2005) - majed - fixes nusoap issue

r8846 - 2005-10-31 11:01:12 -0800 (Mon, 31 Oct 2005) - majed - new version of nusoap

r7905 - 2005-09-21 19:12:57 -0700 (Wed, 21 Sep 2005) - majed - restores old nusoap pre & with a few fixes

r7861 - 2005-09-20 15:40:25 -0700 (Tue, 20 Sep 2005) - majed - & fix for 3.5.1

r5462 - 2005-05-25 13:50:11 -0700 (Wed, 25 May 2005) - majed - upgraded nusoap to .6.9

r573 - 2004-09-04 13:03:32 -0700 (Sat, 04 Sep 2004) - sugarclint - undoing copyrights added in inadvertantly.  --clint

r546 - 2004-09-03 11:49:38 -0700 (Fri, 03 Sep 2004) - sugarmsi - removed echo count

r354 - 2004-08-02 23:00:37 -0700 (Mon, 02 Aug 2004) - sugarjacob - Adding Soap


*/


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}




/**
*
* [nu]soapclient higher level class for easy usage.
*
* usage:
*
* // instantiate client with server info
* $soapclient = new nusoap_client( string path [ ,mixed wsdl] );
*
* // call method, get results
* echo $soapclient->call( string methodname [ ,array parameters] );
*
* // bye bye client
* unset($soapclient);
*
* @author   Dietrich Ayala <dietrich@ganx4.com>
* @author   Scott Nichol <snichol@users.sourceforge.net>

* @access   public
*/
class nusoap_client extends nusoap_base
{
    public $username = '';				// Username for HTTP authentication
    public $password = '';				// Password for HTTP authentication
    public $authtype = '';				// Type of HTTP authentication
    public $certRequest = array();		// Certificate for HTTP SSL authentication
    public $requestHeaders = false;	// SOAP headers in request (text)
    public $responseHeaders = '';		// SOAP headers from response (incomplete namespace resolution) (text)
    public $responseHeader = null;		// SOAP Header from response (parsed)
    public $document = '';				// SOAP body response portion (incomplete namespace resolution) (text)
    public $endpoint;
    public $forceEndpoint = '';		// overrides WSDL endpoint
    public $proxyhost = '';
    public $proxyport = '';
    public $proxyusername = '';
    public $proxypassword = '';
    public $portName = '';				// port name to use in WSDL
    public $xml_encoding = '';			// character set encoding of incoming (response) messages
    public $http_encoding = false;
    public $timeout = 0;				// HTTP connection timeout
    public $response_timeout = 30;		// HTTP response timeout
    public $endpointType = '';			// soap|wsdl, empty for WSDL initialization error
    public $persistentConnection = false;
    public $defaultRpcParams = false;	// This is no longer used
    public $request = '';				// HTTP request
    public $response = '';				// HTTP response
    public $responseData = '';			// SOAP payload of response
    public $cookies = array();			// Cookies from response or for request
    public $decode_utf8 = true;		// toggles whether the parser decodes element content w/ utf8_decode()
    public $operations = array();		// WSDL operations, empty for WSDL initialization error
    public $curl_options = array();	// User-specified cURL options
    public $bindingType = '';			// WSDL operation binding type
    public $use_curl = false;			// whether to always try to use cURL

    /*
     * fault related variables
     */
    /**
     * @var      fault
     * @access   public
     */
    public $fault;
    /**
     * @var      faultcode
     * @access   public
     */
    public $faultcode;
    /**
     * @var      faultstring
     * @access   public
     */
    public $faultstring;
    /**
     * @var      faultdetail
     * @access   public
     */
    public $faultdetail;

    /**
    * constructor
    *
    * @param    mixed $endpoint SOAP server or WSDL URL (string), or wsdl instance (object)
    * @param    mixed $wsdl optional, set to 'wsdl' or true if using WSDL
    * @param    string $proxyhost optional
    * @param    string $proxyport optional
    * @param	string $proxyusername optional
    * @param	string $proxypassword optional
    * @param	integer $timeout set the connection timeout
    * @param	integer $response_timeout set the response timeout
    * @param	string $portName optional portName in WSDL document
    * @access   public
    */
    public function nusoap_client($endpoint, $wsdl = false, $proxyhost = false, $proxyport = false, $proxyusername = false, $proxypassword = false, $timeout = 0, $response_timeout = 30, $portName = '')
    {
        parent::nusoap_base();
        $this->endpoint = $endpoint;
        $this->proxyhost = $proxyhost;
        $this->proxyport = $proxyport;
        $this->proxyusername = $proxyusername;
        $this->proxypassword = $proxypassword;
        $this->timeout = $timeout;
        $this->response_timeout = $response_timeout;
        $this->portName = $portName;

        $this->debug("ctor wsdl=$wsdl timeout=$timeout response_timeout=$response_timeout");
        $this->appendDebug('endpoint=' . $this->varDump($endpoint));

        // make values
        if ($wsdl) {
            if (is_object($endpoint) && (get_class($endpoint) == 'wsdl')) {
                $this->wsdl = $endpoint;
                $this->endpoint = $this->wsdl->wsdl;
                $this->wsdlFile = $this->endpoint;
                $this->debug('existing wsdl instance created from ' . $this->endpoint);
                $this->checkWSDL();
            } else {
                $this->wsdlFile = $this->endpoint;
                $this->wsdl = null;
                $this->debug('will use lazy evaluation of wsdl from ' . $this->endpoint);
            }
            $this->endpointType = 'wsdl';
        } else {
            $this->debug("instantiate SOAP with endpoint at $endpoint");
            $this->endpointType = 'soap';
        }
    }

    /**
    * calls method, returns PHP native type
    *
    * @param    string $operation SOAP server URL or path
    * @param    mixed $params An array, associative or simple, of the parameters
    *			              for the method call, or a string that is the XML
    *			              for the call.  For rpc style, this call will
    *			              wrap the XML in a tag named after the method, as
    *			              well as the SOAP Envelope and Body.  For document
    *			              style, this will only wrap with the Envelope and Body.
    *			              IMPORTANT: when using an array with document style,
    *			              in which case there
    *                         is really one parameter, the root of the fragment
    *                         used in the call, which encloses what programmers
    *                         normally think of parameters.  A parameter array
    *                         *must* include the wrapper.
    * @param	string $namespace optional method namespace (WSDL can override)
    * @param	string $soapAction optional SOAPAction value (WSDL can override)
    * @param	mixed $headers optional string of XML with SOAP header content, or array of soapval objects for SOAP headers, or associative array
    * @param	boolean $rpcParams optional (no longer used)
    * @param	string	$style optional (rpc|document) the style to use when serializing parameters (WSDL can override)
    * @param	string	$use optional (encoded|literal) the use when serializing parameters (WSDL can override)
    * @return	mixed	response from SOAP call, normally an associative array mirroring the structure of the XML response, false for certain fatal errors
    * @access   public
    */
    public function call($operation, $params=array(), $namespace='http://tempuri.org', $soapAction='', $headers=false, $rpcParams=null, $style='rpc', $use='encoded')
    {
        $this->operation = $operation;
        $this->fault = false;
        $this->setError('');
        $this->request = '';
        $this->response = '';
        $this->responseData = '';
        $this->faultstring = '';
        $this->faultcode = '';
        $this->opData = array();
        
        $this->debug("call: operation=$operation, namespace=$namespace, soapAction=$soapAction, rpcParams=$rpcParams, style=$style, use=$use, endpointType=$this->endpointType");
        $this->appendDebug('params=' . $this->varDump($params));
        $this->appendDebug('headers=' . $this->varDump($headers));
        if ($headers) {
            $this->requestHeaders = $headers;
        }
        if ($this->endpointType == 'wsdl' && is_null($this->wsdl)) {
            $this->loadWSDL();
            if ($this->getError()) {
                return false;
            }
        }
        // serialize parameters
        if ($this->endpointType == 'wsdl' && $opData = $this->getOperationData($operation)) {
            // use WSDL for operation
            $this->opData = $opData;
            $this->debug("found operation");
            $this->appendDebug('opData=' . $this->varDump($opData));
            if (isset($opData['soapAction'])) {
                $soapAction = $opData['soapAction'];
            }
            if (! $this->forceEndpoint) {
                $this->endpoint = $opData['endpoint'];
            } else {
                $this->endpoint = $this->forceEndpoint;
            }
            $namespace = isset($opData['input']['namespace']) ? $opData['input']['namespace'] :	$namespace;
            $style = $opData['style'];
            $use = $opData['input']['use'];
            // add ns to ns array
            if ($namespace != '' && !isset($this->wsdl->namespaces[$namespace])) {
                $nsPrefix = 'ns' . mt_rand(1000, 9999);
                $this->wsdl->namespaces[$nsPrefix] = $namespace;
            }
            $nsPrefix = $this->wsdl->getPrefixFromNamespace($namespace);
            // serialize payload
            if (is_string($params)) {
                $this->debug("serializing param string for WSDL operation $operation");
                $payload = $params;
            } elseif (is_array($params)) {
                $this->debug("serializing param array for WSDL operation $operation");
                $payload = $this->wsdl->serializeRPCParameters($operation, 'input', $params, $this->bindingType);
            } else {
                $this->debug('params must be array or string');
                $this->setError('params must be array or string');
                return false;
            }
            $usedNamespaces = $this->wsdl->usedNamespaces;
            if (isset($opData['input']['encodingStyle'])) {
                $encodingStyle = $opData['input']['encodingStyle'];
            } else {
                $encodingStyle = '';
            }
            $this->appendDebug($this->wsdl->getDebug());
            $this->wsdl->clearDebug();
            if ($errstr = $this->wsdl->getError()) {
                $this->debug('got wsdl error: '.$errstr);
                $this->setError('wsdl error: '.$errstr);
                return false;
            }
        } elseif ($this->endpointType == 'wsdl') {
            // operation not in WSDL
            $this->appendDebug($this->wsdl->getDebug());
            $this->wsdl->clearDebug();
            $this->setError('operation '.$operation.' not present in WSDL.');
            $this->debug("operation '$operation' not present in WSDL.");
            return false;
        } else {
            // no WSDL
            //$this->namespaces['ns1'] = $namespace;
            $nsPrefix = 'ns' . mt_rand(1000, 9999);
            // serialize
            $payload = '';
            if (is_string($params)) {
                $this->debug("serializing param string for operation $operation");
                $payload = $params;
            } elseif (is_array($params)) {
                $this->debug("serializing param array for operation $operation");
                foreach ($params as $k => $v) {
                    $payload .= $this->serialize_val($v, $k, false, false, false, false, $use);
                }
            } else {
                $this->debug('params must be array or string');
                $this->setError('params must be array or string');
                return false;
            }
            $usedNamespaces = array();
            if ($use == 'encoded') {
                $encodingStyle = 'http://schemas.xmlsoap.org/soap/encoding/';
            } else {
                $encodingStyle = '';
            }
        }
        // wrap RPC calls with method element
        if ($style == 'rpc') {
            if ($use == 'literal') {
                $this->debug("wrapping RPC request with literal method element");
                if ($namespace) {
                    // http://www.ws-i.org/Profiles/BasicProfile-1.1-2004-08-24.html R2735 says rpc/literal accessor elements should not be in a namespace
                    $payload = "<$nsPrefix:$operation xmlns:$nsPrefix=\"$namespace\">" .
                                $payload .
                                "</$nsPrefix:$operation>";
                } else {
                    $payload = "<$operation>" . $payload . "</$operation>";
                }
            } else {
                $this->debug("wrapping RPC request with encoded method element");
                if ($namespace) {
                    $payload = "<$nsPrefix:$operation xmlns:$nsPrefix=\"$namespace\">" .
                                $payload .
                                "</$nsPrefix:$operation>";
                } else {
                    $payload = "<$operation>" .
                                $payload .
                                "</$operation>";
                }
            }
        }
        // serialize envelope
        $soapmsg = $this->serializeEnvelope($payload, $this->requestHeaders, $usedNamespaces, $style, $use, $encodingStyle);
        $this->debug("endpoint=$this->endpoint, soapAction=$soapAction, namespace=$namespace, style=$style, use=$use, encodingStyle=$encodingStyle");
        $this->debug('SOAP message length=' . strlen($soapmsg) . ' contents (max 1000 bytes)=' . substr($soapmsg, 0, 1000));
        // send
        $return = $this->send($this->getHTTPBody($soapmsg), $soapAction, $this->timeout, $this->response_timeout);
        if ($errstr = $this->getError()) {
            $this->debug('Error: '.$errstr);
            return false;
        }
        $this->return = $return;
        $this->debug('sent message successfully and got a(n) '.gettype($return));
        $this->appendDebug('return=' . $this->varDump($return));
            
        // fault?
        if (is_array($return) && isset($return['faultcode'])) {
            $this->debug('got fault');
            $this->setError($return['faultcode'].': '.$return['faultstring']);
            $this->fault = true;
            foreach ($return as $k => $v) {
                $this->$k = $v;
                $this->debug("$k = $v<br>");
            }
            return $return;
        } elseif ($style == 'document') {
            // NOTE: if the response is defined to have multiple parts (i.e. unwrapped),
            // we are only going to return the first part here...sorry about that
            return $return;
        }
        // array of return values
        if (is_array($return)) {
            // multiple 'out' parameters, which we return wrapped up
            // in the array
            if (count($return) > 1) {
                return $return;
            }
            // single 'out' parameter (normally the return value)
            $return = array_shift($return);
            $this->debug('return shifted value: ');
            $this->appendDebug($this->varDump($return));
            return $return;
            // nothing returned (ie, echoVoid)
        }
        return "";
    }

    /**
    * check WSDL passed as an instance or pulled from an endpoint
    *
    * @access   private
    */
    public function checkWSDL()
    {
        $this->appendDebug($this->wsdl->getDebug());
        $this->wsdl->clearDebug();
        $this->debug('checkWSDL');
        // catch errors
        if ($errstr = $this->wsdl->getError()) {
            $this->appendDebug($this->wsdl->getDebug());
            $this->wsdl->clearDebug();
            $this->debug('got wsdl error: '.$errstr);
            $this->setError('wsdl error: '.$errstr);
        } elseif ($this->operations = $this->wsdl->getOperations($this->portName, 'soap')) {
            $this->appendDebug($this->wsdl->getDebug());
            $this->wsdl->clearDebug();
            $this->bindingType = 'soap';
            $this->debug('got '.count($this->operations).' operations from wsdl '.$this->wsdlFile.' for binding type '.$this->bindingType);
        } elseif ($this->operations = $this->wsdl->getOperations($this->portName, 'soap12')) {
            $this->appendDebug($this->wsdl->getDebug());
            $this->wsdl->clearDebug();
            $this->bindingType = 'soap12';
            $this->debug('got '.count($this->operations).' operations from wsdl '.$this->wsdlFile.' for binding type '.$this->bindingType);
            $this->debug('**************** WARNING: SOAP 1.2 BINDING *****************');
        } else {
            $this->appendDebug($this->wsdl->getDebug());
            $this->wsdl->clearDebug();
            $this->debug('getOperations returned false');
            $this->setError('no operations defined in the WSDL document!');
        }
    }

    /**
     * instantiate wsdl object and parse wsdl file
     *
     * @access	public
     */
    public function loadWSDL()
    {
        $this->debug('instantiating wsdl class with doc: '.$this->wsdlFile);
        $this->wsdl = new wsdl('', $this->proxyhost, $this->proxyport, $this->proxyusername, $this->proxypassword, $this->timeout, $this->response_timeout, $this->curl_options, $this->use_curl);
        $this->wsdl->setCredentials($this->username, $this->password, $this->authtype, $this->certRequest);
        $this->wsdl->fetchWSDL($this->wsdlFile);
        $this->checkWSDL();
    }

    /**
    * get available data pertaining to an operation
    *
    * @param    string $operation operation name
    * @return	array array of data pertaining to the operation
    * @access   public
    */
    public function getOperationData($operation)
    {
        if ($this->endpointType == 'wsdl' && is_null($this->wsdl)) {
            $this->loadWSDL();
            if ($this->getError()) {
                return false;
            }
        }
        if (isset($this->operations[$operation])) {
            return $this->operations[$operation];
        }
        $this->debug("No data for operation: $operation");
    }

    /**
    * send the SOAP message
    *
    * Note: if the operation has multiple return values
    * the return value of this method will be an array
    * of those values.
    *
    * @param    string $msg a SOAPx4 soapmsg object
    * @param    string $soapaction SOAPAction value
    * @param    integer $timeout set connection timeout in seconds
    * @param	integer $response_timeout set response timeout in seconds
    * @return	mixed native PHP types.
    * @access   private
    */
    public function send($msg, $soapaction = '', $timeout=0, $response_timeout=30)
    {
        $this->checkCookies();
        // detect transport
        switch (true) {
            // http(s)
            case preg_match('/^http/', $this->endpoint):
                $this->debug('transporting via HTTP');
                if ($this->persistentConnection == true && is_object($this->persistentConnection)) {
                    $http =& $this->persistentConnection;
                } else {
                    $http = new soap_transport_http($this->endpoint, $this->curl_options, $this->use_curl);
                    if ($this->persistentConnection) {
                        $http->usePersistentConnection();
                    }
                }
                $http->setContentType($this->getHTTPContentType(), $this->getHTTPContentTypeCharset());
                $http->setSOAPAction($soapaction);
                if ($this->proxyhost && $this->proxyport) {
                    $http->setProxy($this->proxyhost, $this->proxyport, $this->proxyusername, $this->proxypassword);
                }
                if ($this->authtype != '') {
                    $http->setCredentials($this->username, $this->password, $this->authtype, array(), $this->certRequest);
                }
                if ($this->http_encoding != '') {
                    $http->setEncoding($this->http_encoding);
                }
                $this->debug('sending message, length='.strlen($msg));
                if (preg_match('/^http:/', $this->endpoint)) {
                    //if(strpos($this->endpoint,'http:')){
                    $this->responseData = $http->send($msg, $timeout, $response_timeout, $this->cookies);
                } elseif (preg_match('/^https/', $this->endpoint)) {
                    //} elseif(strpos($this->endpoint,'https:')){
                    //if(phpversion() == '4.3.0-dev'){
                    //$response = $http->send($msg,$timeout,$response_timeout);
                    //$this->request = $http->outgoing_payload;
                    //$this->response = $http->incoming_payload;
                    //} else
                    $this->responseData = $http->sendHTTPS($msg, $timeout, $response_timeout, $this->cookies);
                } else {
                    $this->setError('no http/s in endpoint url');
                }
                $this->request = $http->outgoing_payload;
                $this->response = $http->incoming_payload;
                $this->appendDebug($http->getDebug());
                $this->UpdateCookies($http->incoming_cookies);

                // save transport object if using persistent connections
                if ($this->persistentConnection) {
                    $http->clearDebug();
                    if (!is_object($this->persistentConnection)) {
                        $this->persistentConnection = $http;
                    }
                }
                
                if ($err = $http->getError()) {
                    $this->setError('HTTP Error: '.$err);
                    return false;
                } elseif ($this->getError()) {
                    return false;
                }
                    $this->debug('got response, length='. strlen($this->responseData).' type='.$http->incoming_headers['content-type']);
                    return $this->parseResponse($http->incoming_headers, $this->responseData);
                
            break;
            default:
                $this->setError('no transport found, or selected transport is not yet supported!');
            return false;
            break;
        }
    }

    /**
    * processes SOAP message returned from server
    *
    * @param	array	$headers	The HTTP headers
    * @param	string	$data		unprocessed response data from server
    * @return	mixed	value of the message, decoded into a PHP type
    * @access   private
    */
    public function parseResponse($headers, $data)
    {
        $this->debug('Entering parseResponse() for data of length ' . strlen($data) . ' headers:');
        $this->appendDebug($this->varDump($headers));
        if (!isset($headers['content-type'])) {
            $this->setError('Response not of type text/xml (no content-type header)');
            return false;
        }
        if (!strstr($headers['content-type'], 'text/xml')) {
            $this->setError('Response not of type text/xml: ' . $headers['content-type']);
            return false;
        }
        if (strpos($headers['content-type'], '=')) {
            $enc = str_replace('"', '', substr(strstr($headers["content-type"], '='), 1));
            $this->debug('Got response encoding: ' . $enc);
            if (preg_match('/^(ISO-8859-1|US-ASCII|UTF-8)$/i', $enc)) {
                $this->xml_encoding = strtoupper($enc);
            } else {
                $this->xml_encoding = 'US-ASCII';
            }
        } else {
            // should be US-ASCII for HTTP 1.0 or ISO-8859-1 for HTTP 1.1
            $this->xml_encoding = 'ISO-8859-1';
        }
        $this->debug('Use encoding: ' . $this->xml_encoding . ' when creating nusoap_parser');
        $parser = new nusoap_parser($data, $this->xml_encoding, $this->operation, $this->decode_utf8);
        // add parser debug data to our debug
        $this->appendDebug($parser->getDebug());
        // if parse errors
        if ($errstr = $parser->getError()) {
            $this->setError($errstr);
            // destroy the parser object
            unset($parser);
            return false;
        }
        // get SOAP headers
        $this->responseHeaders = $parser->getHeaders();
        // get SOAP headers
        $this->responseHeader = $parser->get_soapheader();
        // get decoded message
        $return = $parser->get_soapbody();
        // add document for doclit support
        $this->document = $parser->document;
        // destroy the parser object
        unset($parser);
        // return decode message
        return $return;
    }

    /**
    * sets user-specified cURL options
    *
    * @param	mixed $option The cURL option (always integer?)
    * @param	mixed $value The cURL option value
    * @access   public
    */
    public function setCurlOption($option, $value)
    {
        $this->debug("setCurlOption option=$option, value=");
        $this->appendDebug($this->varDump($value));
        $this->curl_options[$option] = $value;
    }

    /**
    * sets the SOAP endpoint, which can override WSDL
    *
    * @param	string $endpoint The endpoint URL to use, or empty string or false to prevent override
    * @access   public
    */
    public function setEndpoint($endpoint)
    {
        $this->debug("setEndpoint(\"$endpoint\")");
        $this->forceEndpoint = $endpoint;
    }

    /**
    * set the SOAP headers
    *
    * @param	mixed $headers String of XML with SOAP header content, or array of soapval objects for SOAP headers
    * @access   public
    */
    public function setHeaders($headers)
    {
        $this->debug("setHeaders headers=");
        $this->appendDebug($this->varDump($headers));
        $this->requestHeaders = $headers;
    }

    /**
    * get the SOAP response headers (namespace resolution incomplete)
    *
    * @return	string
    * @access   public
    */
    public function getHeaders()
    {
        return $this->responseHeaders;
    }

    /**
    * get the SOAP response Header (parsed)
    *
    * @return	mixed
    * @access   public
    */
    public function getHeader()
    {
        return $this->responseHeader;
    }

    /**
    * set proxy info here
    *
    * @param    string $proxyhost
    * @param    string $proxyport
    * @param	string $proxyusername
    * @param	string $proxypassword
    * @access   public
    */
    public function setHTTPProxy($proxyhost, $proxyport, $proxyusername = '', $proxypassword = '')
    {
        $this->proxyhost = $proxyhost;
        $this->proxyport = $proxyport;
        $this->proxyusername = $proxyusername;
        $this->proxypassword = $proxypassword;
    }

    /**
    * if authenticating, set user credentials here
    *
    * @param    string $username
    * @param    string $password
    * @param	string $authtype (basic|digest|certificate|ntlm)
    * @param	array $certRequest (keys must be cainfofile (optional), sslcertfile, sslkeyfile, passphrase, verifypeer (optional), verifyhost (optional): see corresponding options in cURL docs)
    * @access   public
    */
    public function setCredentials($username, $password, $authtype = 'basic', $certRequest = array())
    {
        $this->debug("setCredentials username=$username authtype=$authtype certRequest=");
        $this->appendDebug($this->varDump($certRequest));
        $this->username = $username;
        $this->password = $password;
        $this->authtype = $authtype;
        $this->certRequest = $certRequest;
    }
    
    /**
    * use HTTP encoding
    *
    * @param    string $enc HTTP encoding
    * @access   public
    */
    public function setHTTPEncoding($enc='gzip, deflate')
    {
        $this->debug("setHTTPEncoding(\"$enc\")");
        $this->http_encoding = $enc;
    }
    
    /**
    * Set whether to try to use cURL connections if possible
    *
    * @param	boolean $use Whether to try to use cURL
    * @access   public
    */
    public function setUseCURL($use)
    {
        $this->debug("setUseCURL($use)");
        $this->use_curl = $use;
    }

    /**
    * use HTTP persistent connections if possible
    *
    * @access   public
    */
    public function useHTTPPersistentConnection()
    {
        $this->debug("useHTTPPersistentConnection");
        $this->persistentConnection = true;
    }
    
    /**
    * gets the default RPC parameter setting.
    * If true, default is that call params are like RPC even for document style.
    * Each call() can override this value.
    *
    * This is no longer used.
    *
    * @return boolean
    * @access public
    * @deprecated
    */
    public function getDefaultRpcParams()
    {
        return $this->defaultRpcParams;
    }

    /**
    * sets the default RPC parameter setting.
    * If true, default is that call params are like RPC even for document style
    * Each call() can override this value.
    *
    * This is no longer used.
    *
    * @param    boolean $rpcParams
    * @access public
    * @deprecated
    */
    public function setDefaultRpcParams($rpcParams)
    {
        $this->defaultRpcParams = $rpcParams;
    }
    
    /**
    * dynamically creates an instance of a proxy class,
    * allowing user to directly call methods from wsdl
    *
    * @return   object soap_proxy object
    * @access   public
    */
    public function getProxy()
    {
        $r = mt_rand();
        $evalStr = $this->_getProxyClassCode($r);
        //$this->debug("proxy class: $evalStr");
        if ($this->getError()) {
            $this->debug("Error from _getProxyClassCode, so return NULL");
            return null;
        }
        // eval the class
        eval($evalStr);
        // instantiate proxy object
        eval("\$proxy = new nusoap_proxy_$r('');");
        // transfer current wsdl data to the proxy thereby avoiding parsing the wsdl twice
        $proxy->endpointType = 'wsdl';
        $proxy->wsdlFile = $this->wsdlFile;
        $proxy->wsdl = $this->wsdl;
        $proxy->operations = $this->operations;
        $proxy->defaultRpcParams = $this->defaultRpcParams;
        // transfer other state
        $proxy->soap_defencoding = $this->soap_defencoding;
        $proxy->username = $this->username;
        $proxy->password = $this->password;
        $proxy->authtype = $this->authtype;
        $proxy->certRequest = $this->certRequest;
        $proxy->requestHeaders = $this->requestHeaders;
        $proxy->endpoint = $this->endpoint;
        $proxy->forceEndpoint = $this->forceEndpoint;
        $proxy->proxyhost = $this->proxyhost;
        $proxy->proxyport = $this->proxyport;
        $proxy->proxyusername = $this->proxyusername;
        $proxy->proxypassword = $this->proxypassword;
        $proxy->http_encoding = $this->http_encoding;
        $proxy->timeout = $this->timeout;
        $proxy->response_timeout = $this->response_timeout;
        $proxy->persistentConnection = &$this->persistentConnection;
        $proxy->decode_utf8 = $this->decode_utf8;
        $proxy->curl_options = $this->curl_options;
        $proxy->bindingType = $this->bindingType;
        $proxy->use_curl = $this->use_curl;
        return $proxy;
    }

    /**
    * dynamically creates proxy class code
    *
    * @return   string PHP/NuSOAP code for the proxy class
    * @access   private
    */
    public function _getProxyClassCode($r)
    {
        $this->debug("in getProxy endpointType=$this->endpointType");
        $this->appendDebug("wsdl=" . $this->varDump($this->wsdl));
        if ($this->endpointType != 'wsdl') {
            $evalStr = 'A proxy can only be created for a WSDL client';
            $this->setError($evalStr);
            $evalStr = "echo \"$evalStr\";";
            return $evalStr;
        }
        if ($this->endpointType == 'wsdl' && is_null($this->wsdl)) {
            $this->loadWSDL();
            if ($this->getError()) {
                return "echo \"" . $this->getError() . "\";";
            }
        }
        $evalStr = '';
        foreach ($this->operations as $operation => $opData) {
            if ($operation != '') {
                // create param string and param comment string
                if (count($opData['input']['parts']) > 0) {
                    $paramStr = '';
                    $paramArrayStr = '';
                    $paramCommentStr = '';
                    foreach ($opData['input']['parts'] as $name => $type) {
                        $paramStr .= "\$$name, ";
                        $paramArrayStr .= "'$name' => \$$name, ";
                        $paramCommentStr .= "$type \$$name, ";
                    }
                    $paramStr = substr($paramStr, 0, strlen($paramStr)-2);
                    $paramArrayStr = substr($paramArrayStr, 0, strlen($paramArrayStr)-2);
                    $paramCommentStr = substr($paramCommentStr, 0, strlen($paramCommentStr)-2);
                } else {
                    $paramStr = '';
                    $paramArrayStr = '';
                    $paramCommentStr = 'void';
                }
                $opData['namespace'] = !isset($opData['namespace']) ? 'http://testuri.com' : $opData['namespace'];
                $evalStr .= "// $paramCommentStr
	function " . str_replace('.', '__', $operation) . "($paramStr) {
		\$params = array($paramArrayStr);
		return \$this->call('$operation', \$params, '".$opData['namespace']."', '".(isset($opData['soapAction']) ? $opData['soapAction'] : '')."');
	}
	";
                unset($paramStr);
                unset($paramCommentStr);
            }
        }
        $evalStr = 'class nusoap_proxy_'.$r.' extends nusoap_client {
	'.$evalStr.'
}';
        return $evalStr;
    }

    /**
    * dynamically creates proxy class code
    *
    * @return   string PHP/NuSOAP code for the proxy class
    * @access   public
    */
    public function getProxyClassCode()
    {
        $r = mt_rand();
        return $this->_getProxyClassCode($r);
    }

    /**
    * gets the HTTP body for the current request.
    *
    * @param string $soapmsg The SOAP payload
    * @return string The HTTP body, which includes the SOAP payload
    * @access private
    */
    public function getHTTPBody($soapmsg)
    {
        return $soapmsg;
    }
    
    /**
    * gets the HTTP content type for the current request.
    *
    * Note: getHTTPBody must be called before this.
    *
    * @return string the HTTP content type for the current request.
    * @access private
    */
    public function getHTTPContentType()
    {
        return 'text/xml';
    }
    
    /**
    * gets the HTTP content type charset for the current request.
    * returns false for non-text content types.
    *
    * Note: getHTTPBody must be called before this.
    *
    * @return string the HTTP content type charset for the current request.
    * @access private
    */
    public function getHTTPContentTypeCharset()
    {
        return $this->soap_defencoding;
    }

    /*
    * whether or not parser should decode utf8 element content
    *
    * @return   always returns true
    * @access   public
    */
    public function decodeUTF8($bool)
    {
        $this->decode_utf8 = $bool;
        return true;
    }

    /**
     * adds a new Cookie into $this->cookies array
     *
     * @param	string $name Cookie Name
     * @param	string $value Cookie Value
     * @return	boolean if cookie-set was successful returns true, else false
     * @access	public
     */
    public function setCookie($name, $value)
    {
        if (strlen($name) == 0) {
            return false;
        }
        $this->cookies[] = array('name' => $name, 'value' => $value);
        return true;
    }

    /**
     * gets all Cookies
     *
     * @return   array with all internal cookies
     * @access   public
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * checks all Cookies and delete those which are expired
     *
     * @return   boolean always return true
     * @access   private
     */
    public function checkCookies()
    {
        if (count($this->cookies) == 0) {
            return true;
        }
        $this->debug('checkCookie: check ' . count($this->cookies) . ' cookies');
        $curr_cookies = $this->cookies;
        $this->cookies = array();
        foreach ($curr_cookies as $cookie) {
            if (! is_array($cookie)) {
                $this->debug('Remove cookie that is not an array');
                continue;
            }
            if ((isset($cookie['expires'])) && (! empty($cookie['expires']))) {
                if (strtotime($cookie['expires']) > time()) {
                    $this->cookies[] = $cookie;
                } else {
                    $this->debug('Remove expired cookie ' . $cookie['name']);
                }
            } else {
                $this->cookies[] = $cookie;
            }
        }
        $this->debug('checkCookie: '.count($this->cookies).' cookies left in array');
        return true;
    }

    /**
     * updates the current cookies with a new set
     *
     * @param	array $cookies new cookies with which to update current ones
     * @return	boolean always return true
     * @access	private
     */
    public function UpdateCookies($cookies)
    {
        if (count($this->cookies) == 0) {
            // no existing cookies: take whatever is new
            if (count($cookies) > 0) {
                $this->debug('Setting new cookie(s)');
                $this->cookies = $cookies;
            }
            return true;
        }
        if (count($cookies) == 0) {
            // no new cookies: keep what we've got
            return true;
        }
        // merge
        foreach ($cookies as $newCookie) {
            if (!is_array($newCookie)) {
                continue;
            }
            if ((!isset($newCookie['name'])) || (!isset($newCookie['value']))) {
                continue;
            }
            $newName = $newCookie['name'];

            $found = false;
            for ($i = 0; $i < count($this->cookies); $i++) {
                $cookie = $this->cookies[$i];
                if (!is_array($cookie)) {
                    continue;
                }
                if (!isset($cookie['name'])) {
                    continue;
                }
                if ($newName != $cookie['name']) {
                    continue;
                }
                $newDomain = isset($newCookie['domain']) ? $newCookie['domain'] : 'NODOMAIN';
                $domain = isset($cookie['domain']) ? $cookie['domain'] : 'NODOMAIN';
                if ($newDomain != $domain) {
                    continue;
                }
                $newPath = isset($newCookie['path']) ? $newCookie['path'] : 'NOPATH';
                $path = isset($cookie['path']) ? $cookie['path'] : 'NOPATH';
                if ($newPath != $path) {
                    continue;
                }
                $this->cookies[$i] = $newCookie;
                $found = true;
                $this->debug('Update cookie ' . $newName . '=' . $newCookie['value']);
                break;
            }
            if (! $found) {
                $this->debug('Add cookie ' . $newName . '=' . $newCookie['value']);
                $this->cookies[] = $newCookie;
            }
        }
        return true;
    }
}

if (!extension_loaded('soap')) {
    /**
     *	For backwards compatiblity, define soapclient unless the PHP SOAP extension is loaded.
     */
    class soapclient extends nusoap_client
    {
    }
}

/**
 *	For backwards compatiblity, define nusoapclient unless the PHP SOAP extension is loaded.
 */
class nusoapclient extends nusoap_client
{
}
