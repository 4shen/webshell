<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

/**
 * The "organizations" collection of methods.
 * Typical usage is:
 *  <code>
 *   $apigeeService = new Google_Service_Apigee(...);
 *   $organizations = $apigeeService->organizations;
 *  </code>
 */
class Google_Service_Apigee_Resource_Organizations extends Google_Service_Resource
{
  /**
   * Creates an Apigee organization. See [Create an organization](/hybrid/precog-
   * provision). (organizations.create)
   *
   * @param Google_Service_Apigee_GoogleCloudApigeeV1Organization $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string parent Required. Name of the GCP project in which to
   * associate the Apigee organization. Pass the information as a query parameter
   * using the following structure in your request:   `projects/`
   * @return Google_Service_Apigee_GoogleLongrunningOperation
   */
  public function create(Google_Service_Apigee_GoogleCloudApigeeV1Organization $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Apigee_GoogleLongrunningOperation");
  }
  /**
   * Gets the profile for an Apigee organization. See
   * [Organizations](/hybrid/terminology#organizations). (organizations.get)
   *
   * @param string $name Required. Apigee organization name in the following
   * format:   `organizations/{org}`
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Organization
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Organization");
  }
  /**
   * Gets metrics for an organization. (organizations.getMetrics)
   *
   * @param string $parent Required. Name of the Apigee organization. Use the
   * following structure in your request:   `organizations/{org}`
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Metrics
   */
  public function getMetrics($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('getMetrics', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Metrics");
  }
  /**
   * Lists details for all portals. (organizations.getSites)
   *
   * @param string $parent Required. Name of the Apigee organization. Use the
   * following structure in your request:   `organizations/{org}`
   * @param array $optParams Optional parameters.
   *
   * @opt_param string zmsId
   * @opt_param string domain
   * @return Google_Service_Apigee_GoogleCloudApigeeV1SiteListResponse
   */
  public function getSites($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('getSites', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1SiteListResponse");
  }
  /**
   * Lists the service accounts with the permissions required to allow the
   * Synchronizer to download environment data from the control plane.
   *
   * An ETag is returned in the response to `getSyncAuthorization`. Pass that ETag
   * when calling [setSyncAuthorization](setSyncAuthorization) to ensure that you
   * are updating the correct version. If you don't pass the ETag in the call to
   * `setSyncAuthorization`, then the existing authorization is overwritten
   * indiscriminately.
   *
   * For more information, see [Enable Synchronizer access](/hybrid/install-sa-
   * keys). (organizations.getSyncAuthorization)
   *
   * @param string $name Required. Name of the Apigee organization. Use the
   * following structure in your request:  `organizations/{org}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1GetSyncAuthorizationRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1SyncAuthorization
   */
  public function getSyncAuthorization($name, Google_Service_Apigee_GoogleCloudApigeeV1GetSyncAuthorizationRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('getSyncAuthorization', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1SyncAuthorization");
  }
  /**
   * Gets the current state of the portal application. (organizations.getSystem)
   *
   * @param string $parent Required. Name of the Apigee organization. Use the
   * following structure in your request:   `organizations/{org}`
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1SystemState
   */
  public function getSystem($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('getSystem', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1SystemState");
  }
  /**
   * Lists the Apigee organizations and associated GCP projects that you have
   * permission to access. See [Organizations](/hybrid/terminology#organizations).
   * (organizations.listOrganizations)
   *
   * @param string $parent Required. Apigee organization name in the following
   * format:   `organizations/{org}`
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1ListOrganizationsResponse
   */
  public function listOrganizations($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1ListOrganizationsResponse");
  }
  /**
   * Sets the permissions required to allow the Synchronizer to download
   * environment data from the control plane. You must call this API to enable
   * proper functioning of hybrid.
   *
   * Pass the ETag when calling `setSyncAuthorization` to ensure that you are
   * updating the correct version. To get an ETag, call
   * [getSyncAuthorization](getSyncAuthorization). If you don't pass the ETag in
   * the call to `setSyncAuthorization`, then the existing authorization is
   * overwritten indiscriminately.
   *
   * For more information, see [Enable Synchronizer access](/hybrid/install-sa-
   * keys). (organizations.setSyncAuthorization)
   *
   * @param string $name Required. Name of the Apigee organization. Use the
   * following structure in your request:  `organizations/{org}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1SyncAuthorization $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1SyncAuthorization
   */
  public function setSyncAuthorization($name, Google_Service_Apigee_GoogleCloudApigeeV1SyncAuthorization $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('setSyncAuthorization', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1SyncAuthorization");
  }
  /**
   * Updates the properties for an Apigee organization. No other fields in the
   * organization profile will be updated. (organizations.update)
   *
   * @param string $name Required. Apigee organization name in the following
   * format:   `organizations/{org}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1Organization $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Organization
   */
  public function update($name, Google_Service_Apigee_GoogleCloudApigeeV1Organization $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Organization");
  }
}
