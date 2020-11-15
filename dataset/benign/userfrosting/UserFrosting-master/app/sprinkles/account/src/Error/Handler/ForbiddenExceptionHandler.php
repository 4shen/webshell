<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Error\Handler;

use UserFrosting\Sprinkle\Core\Error\Handler\HttpExceptionHandler;
use UserFrosting\Support\Message\UserMessage;

/**
 * Handler for ForbiddenExceptions.  Only really needed to override the default error message.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class ForbiddenExceptionHandler extends HttpExceptionHandler
{
    /**
     * Resolve a list of error messages to present to the end user.
     *
     * @return array
     */
    protected function determineUserMessages()
    {
        return [
            new UserMessage('ACCOUNT.ACCESS_DENIED'),
        ];
    }
}
