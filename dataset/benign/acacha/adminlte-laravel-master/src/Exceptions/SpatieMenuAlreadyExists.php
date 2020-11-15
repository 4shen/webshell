<?php

namespace Acacha\AdminLTETemplateLaravel\Exceptions;

/**
 * Class SpatieMenuAlreadyExists.
 *
 * @package Acacha\AdminLTETemplateLaravel\Exceptions
 */
class SpatieMenuAlreadyExists extends \Exception
{
    /**
     * SpatieMenuAlreadyExists constructor.
     */
    public function __construct()
    {
        parent::__construct('Spatie menu already exists');
    }
}
