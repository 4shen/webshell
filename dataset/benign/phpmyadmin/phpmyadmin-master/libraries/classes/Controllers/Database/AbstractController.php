<?php
/**
 * Holds the PhpMyAdmin\Controllers\Database\AbstractController
 */

declare(strict_types=1);

namespace PhpMyAdmin\Controllers\Database;

use PhpMyAdmin\Controllers\AbstractController as Controller;
use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Response;
use PhpMyAdmin\Template;

/**
 * Handles database related logic
 */
abstract class AbstractController extends Controller
{
    /** @var string */
    protected $db;

    /**
     * @param Response          $response Response object
     * @param DatabaseInterface $dbi      DatabaseInterface object
     * @param Template          $template Template object
     * @param string            $db       Database name
     */
    public function __construct($response, $dbi, Template $template, $db)
    {
        parent::__construct($response, $dbi, $template);
        $this->db = $db;
    }
}
