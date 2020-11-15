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

namespace SuiteCRM\API\v8\Exception;

use SuiteCRM\LangException;
use SuiteCRM\LangText;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Class ApiException
 * @package SuiteCRM\API\v8\Exception
 */
class ApiException extends LangException
{
    const MSG_PREFIX = '[SuiteCRM] [API]';
    const DEFAULT_CODE = 8000;
    const HTTP_STATUS = 500;
    const DETAIL_TEXT_LABEL = 'LBL_API_EXCEPTION_DETAIL';
    
    /**
     *
     * @var array
     */
    protected $source = ['pointer' => null, 'parameter' => null];
    
    /**
     *
     * @var LangText
     */
    protected $detail;

    /**
     *
     * @param string $message
     * @param integer $code
     * @param \Exception $previous
     * @param LangText $langMessage
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null, LangText $langMessage = null)
    {
        parent::__construct((self::MSG_PREFIX === $this::MSG_PREFIX ? $this::MSG_PREFIX : self::MSG_PREFIX . ' ' . $this::MSG_PREFIX) . ' ' . $message, $code ? $code : self::DEFAULT_CODE, $previous, $langMessage);
    }

    /**
     * Gives addition details to what caused the exception
     * @return LangText
     */
    public function getDetail()
    {
        $text = $this->detail ? $this->detail : new LangText($this::DETAIL_TEXT_LABEL);
        return $text;
    }

    /**
     * @param string $detail
     */
    public function setDetail(LangText $detail)
    {
        $this->detail = $detail;
    }
    
    /**
     * @param array|string $source
     */
    public function setSource($source)
    {
        if (is_string($source)) {
            $source = ['pointer' => $source];
        }
        $this->source = $source;
    }
    
    /**
     *
     * @return array
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return int http status code that should be returned back to the client
     */
    public function getHttpStatus()
    {
        return $this::HTTP_STATUS;
    }
}
