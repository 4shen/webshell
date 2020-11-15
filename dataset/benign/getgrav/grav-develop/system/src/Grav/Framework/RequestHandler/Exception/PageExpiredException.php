<?php

/**
 * @package    Grav\Framework\RequestHandler
 *
 * @copyright  Copyright (C) 2015 - 2019 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

declare(strict_types=1);

namespace Grav\Framework\RequestHandler\Exception;

use Psr\Http\Message\ServerRequestInterface;

class PageExpiredException extends RequestException
{
    /**
     * PageExpiredException constructor.
     * @param ServerRequestInterface $request
     * @param \Throwable|null $previous
     */
    public function __construct(ServerRequestInterface $request, \Throwable $previous = null)
    {
        parent::__construct($request, 'Page Expired', 400, $previous); // 419
    }
}
