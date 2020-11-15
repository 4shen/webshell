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
 * The "realms" collection of methods.
 * Typical usage is:
 *  <code>
 *   $gameservicesService = new Google_Service_GameServices(...);
 *   $realms = $gameservicesService->realms;
 *  </code>
 */
class Google_Service_GameServices_Resource_ProjectsLocationsRealms extends Google_Service_Resource
{
  /**
   * Creates a new Realm in a given project and Location. (realms.create)
   *
   * @param string $parent Required. The parent resource name. Uses the form:
   * `projects/{project}/locations/{location}`.
   * @param Google_Service_GameServices_Realm $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string realmId Required. The ID of the Realm resource to be
   * created.
   * @return Google_Service_GameServices_Operation
   */
  public function create($parent, Google_Service_GameServices_Realm $postBody, $optParams = array())
  {
    $params = array('parent' => $parent, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_GameServices_Operation");
  }
  /**
   * Deletes a single Realm. (realms.delete)
   *
   * @param string $name Required. The name of the Realm to delete. Uses the form:
   * `projects/{project}/locations/{location}/realms/{realm}`.
   * @param array $optParams Optional parameters.
   * @return Google_Service_GameServices_Operation
   */
  public function delete($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_GameServices_Operation");
  }
  /**
   * Gets details of a single Realm. (realms.get)
   *
   * @param string $name Required. The name of the Realm to retrieve. Uses the
   * form: `projects/{project}/locations/{location}/realms/{realm}`.
   * @param array $optParams Optional parameters.
   * @return Google_Service_GameServices_Realm
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_GameServices_Realm");
  }
  /**
   * Lists Realms in a given project and Location.
   * (realms.listProjectsLocationsRealms)
   *
   * @param string $parent Required. The parent resource name. Uses the form:
   * `projects/{project}/locations/{location}`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string pageToken Optional. The next_page_token value returned from
   * a previous List request, if any.
   * @opt_param string orderBy Optional. Specifies the ordering of results
   * following syntax at
   * https://cloud.google.com/apis/design/design_patterns#sorting_order.
   * @opt_param int pageSize Optional. The maximum number of items to return.  If
   * unspecified, server will pick an appropriate default. Server may return fewer
   * items than requested. A caller should only rely on response's next_page_token
   * to determine if there are more Realms left to be queried.
   * @opt_param string filter Optional. The filter to apply to list results.
   * @return Google_Service_GameServices_ListRealmsResponse
   */
  public function listProjectsLocationsRealms($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_GameServices_ListRealmsResponse");
  }
  /**
   * Patches a single Realm. (realms.patch)
   *
   * @param string $name The resource name of the Realm. Uses the form:
   * `projects/{project}/locations/{location}/realms/{realm}`. For example,
   * `projects/my-project/locations/{location}/realms/my-realm`.
   * @param Google_Service_GameServices_Realm $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string updateMask Required. The update mask applies to the
   * resource. For the `FieldMask` definition, see
   *
   * https: //developers.google.com/protocol-buffers //
   * /docs/reference/google.protobuf#fieldmask
   * @return Google_Service_GameServices_Operation
   */
  public function patch($name, Google_Service_GameServices_Realm $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_GameServices_Operation");
  }
  /**
   * Previews patches to a single Realm. (realms.previewUpdate)
   *
   * @param string $name The resource name of the Realm. Uses the form:
   * `projects/{project}/locations/{location}/realms/{realm}`. For example,
   * `projects/my-project/locations/{location}/realms/my-realm`.
   * @param Google_Service_GameServices_Realm $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string updateMask Required. The update mask applies to the
   * resource. For the `FieldMask` definition, see
   *
   * https: //developers.google.com/protocol-buffers //
   * /docs/reference/google.protobuf#fieldmask
   * @opt_param string previewTime Optional. The target timestamp to compute the
   * preview.
   * @return Google_Service_GameServices_PreviewRealmUpdateResponse
   */
  public function previewUpdate($name, Google_Service_GameServices_Realm $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('previewUpdate', array($params), "Google_Service_GameServices_PreviewRealmUpdateResponse");
  }
}
