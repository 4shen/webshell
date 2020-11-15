<?php

/**
 * FhirPatientRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirPatientService;
use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\Validators\ProcessingResult;

require_once(__DIR__ . '/../../../_rest_config.php');

/**
 * Supports REST interactions with the FHIR patient resource
 */
class FhirPatientRestController
{
    private $fhirPatientService;
    private $fhirService;
    private $fhirValidate;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
        $this->fhirPatientService = new FhirPatientService();
        $this->fhirValidate = new FhirValidationService();
    }

    /**
     * Creates a new FHIR patient resource
     * @param $fhirJson The FHIR patient resource
     * @returns 201 if the resource is created, 400 if the resource is invalid
     */
    public function post($fhirJson)
    {
        $fhirValidate = $this->fhirValidate->validate($fhirJson);
        if (!empty($fhirValidate)) {
            return RestControllerHelper::responseHandler($fhirValidate, null, 400);
        }

        $processingResult = $this->fhirPatientService->insert($fhirJson);
        return RestControllerHelper::handleProcessingResult($processingResult, 201);
    }

    /**
     * Updates an existing FHIR patient resource
     * @param $fhirId The FHIR patient resource id (uuid)
     * @param $fhirJson The updated FHIR patient resource (complete resource)
     * @returns 200 if the resource is created, 400 if the resource is invalid
     */
    public function put($fhirId, $fhirJson)
    {
        $fhirValidate = $this->fhirValidate->validate($fhirJson);
        if (!empty($fhirValidate)) {
            return RestControllerHelper::responseHandler($fhirValidate, null, 400);
        }

        $processingResult = $this->fhirPatientService->update($fhirId, $fhirJson);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Queries for a single FHIR patient resource by FHIR id
     * @param $fhirId The FHIR patient resource id (uuid)
     * @returns 200 if the operation completes successfully
     */
    public function getOne($fhirId)
    {
        $processingResult = $this->fhirPatientService->getOne($fhirId, true);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR patient resources using various search parameters.
     * Search parameters include:
     * - address (street, postal code, city, or state)
     * - address-city
     * - address-postalcode
     * - address-state
     * - birthdate
     * - email
     * - family
     * - gender
     * - given (first name or middle name)
     * - name (title, first name, middle name, last name)
     * - phone (home, business, cell)
     * - telecom (email, phone)
     * @return FHIR bundle with query results, if found
     */
    public function getAll($searchParams)
    {
        $processingResult = $this->fhirPatientService->getAll($searchParams);
        $bundleEntries = array();
        foreach ($processingResult->getData() as $index => $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  \RestConfig::$REST_FULL_URL . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('Patient', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
