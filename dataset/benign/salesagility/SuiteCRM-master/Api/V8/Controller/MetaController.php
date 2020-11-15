<?php
/**
 *
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
 *
 * SuiteCRM is an extension to SugarCRM Community Edition developed by SalesAgility Ltd.
 * Copyright (C) 2011 - 2018 SalesAgility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo and "Supercharged by SuiteCRM" logo. If the display of the logos is not
 * reasonably feasible for technical reasons, the Appropriate Legal Notices must
 * display the words "Powered by SugarCRM" and "Supercharged by SuiteCRM".
 */

namespace Api\V8\Controller;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use Api\V8\Param\GetFieldListParams;
use Api\V8\Service\MetaService;
use Api\V8\Service\UserService;
use Exception;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * MetaController
 */
class MetaController extends BaseController
{

    /**
     * @var UserService
     */
    private $metaService;

    /**
     * @param MetaService $metaService
     */
    public function __construct(MetaService $metaService)
    {
        $this->metaService = $metaService;
    }

    /**
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getModuleList(Request $request, Response $response, array $args)
    {
        try {
            $jsonResponse = $this->metaService->getModuleList($request);

            return $this->generateResponse($response, $jsonResponse, 200);
        } catch (Exception $exception) {
            return $this->generateErrorResponse($response, $exception, 400);
        }
    }

    /**
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @param GetFieldListParams $fieldListParams
     * @return Response
     */
    public function getFieldList(Request $request, Response $response, array $args, GetFieldListParams $fieldListParams)
    {
        try {
            $jsonResponse = $this->metaService->getFieldList($request, $fieldListParams);

            return $this->generateResponse($response, $jsonResponse, 200);
        } catch (Exception $exception) {
            return $this->generateErrorResponse($response, $exception, 400);
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function getSwaggerSchema(Request $request, Response $response)
    {
        try {
            $jsonResponse = $this->metaService->getSwaggerSchema();

            return $this->generateResponse($response, $jsonResponse, 200);
        } catch (Exception $exception) {
            return $this->generateErrorResponse($response, $exception, 400);
        }
    }
}
