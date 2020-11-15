<?php

/**
 * interface/modules/zend_modules/module/Documents/src/Documents/Plugin/Documents.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Basil PT <basil@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Documents\Plugin;

use OpenEMR\Common\Crypto\CryptoGen;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Documents\Model\DocumentsTable;
use Application\Model\ApplicationTable;
use Application\Listener\Listener;

require_once($GLOBALS['fileroot'] . "/controllers/C_Document.class.php");
use C_Document;

class Documents extends AbstractPlugin
{
    protected $documentsTable;

  /**
   *
   * Documents Table Object
   * @param type $sm Service Manager
   **/
    public function __construct($sm)
    {
        $sm->get('Laminas\Db\Adapter\Adapter');
        $this->documentsTable = new DocumentsTable();
    }

    /**
     * couchDB - Couch DB Connection
     *               - Uses Doctrine  CouchDBClient
     * @return Object $connection
     */
    public function couchDB()
    {
        $host       = $GLOBALS['couchdb_host'];
        $port       = $GLOBALS['couchdb_port'];
        $usename    = $GLOBALS['couchdb_user'];
        $cryptoGen  = new CryptoGen();
        $password   = $cryptoGen->decryptStandard($GLOBALS['couchdb_pass']);
        $database   = $GLOBALS['couchdb_dbase'];
        $enable_log = ($GLOBALS['couchdb_log'] == 1) ? true : false;

        $options = array(
            'host'        => $host,
            'port'        => $port,
            'user'        => $usename,
            'password'    => $password,
            'logging'     => $enable_log,
            'dbname'      => $database
        );
        $connection = \Doctrine\CouchDB\CouchDBClient::create($options);
        return $connection;
    }

    /**
     * saveCouchDocument - Save Document to Couch DB
     * @param Object $connection Couch DB Connection Object
     * @param Json Encoded Data
     * @return Array
     */
    public function saveCouchDocument($connection, $data)
    {
        $couch  = $connection->postDocument($data);
        $id         = $couch[0];
        $rev        = $couch[1];
        if ($id && $rev) {
            $connection->putDocument($data, $id, $rev);
            return $couch;
        } else {
            return false;
        }
    }

    /**
     * getDocument Retieve Documents from Couch/HDD
     * @param Integer $documentId Document ID
     * @param Boolean $doEncryption Download Encrypted File
     * @param  String $encryption_key Key for Document Encryption
     * @return String File Content
     */
    public function getDocument($documentId, $doEncryption = false, $encryption_key = '')
    {
                $obj = new \C_Document();
                $document = $obj->retrieve_action("", $documentId, true, true, true);
        return $document;
    }

    public static function fetchXmlDocuments()
    {
        $obj = new ApplicationTable();
        $query = "SELECT doc.id 
	    FROM categories_to_documents AS cat_doc
	    JOIN documents AS doc ON doc.imported = 0 AND doc.id = cat_doc.document_id AND doc.mimetype = 'text/xml'
	    WHERE cat_doc.category_id = 1";
        $result = $obj->zQuery($query);
        $count  = 0;
        $module = array();
        foreach ($result as $row) {
            $content = $this->getDocument($row['id']);
            $module[$count]['doc_id']   = $row['id'];
            if (preg_match("/<ClinicalDocument/", $content)) {
                if (preg_match("/2.16.840.1.113883.3.88.11.32.1/", $content)) {
                    $module[$count]['doc_type'] = 'CCD';
                } else {
                    $module[$count]['doc_type'] = 'CCDA';
                }
            } elseif (preg_match("/<ccr:ContinuityOfCareRecord/", $content)) {
                $module[$count]['doc_type'] = 'CCR';
            }

            $count++;
        }

        return $module;
    }
}
