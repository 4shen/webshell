<?php
/**
 * Holds the PhpMyAdmin\Controllers\Table\AbstractController
 */

declare(strict_types=1);

namespace PhpMyAdmin\Controllers\Table;

use PhpMyAdmin\Controllers\AbstractController as Controller;
use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Response;
use PhpMyAdmin\Template;

/**
 * Handles table related logic
 */
abstract class AbstractController extends Controller
{
    /** @var string */
    protected $db;

    /** @var string */
    protected $table;

    /**
     * @param Response          $response Response object
     * @param DatabaseInterface $dbi      DatabaseInterface object
     * @param Template          $template Template object
     * @param string            $db       Database name
     * @param string            $table    Table name
     */
    public function __construct(
        $response,
        $dbi,
        Template $template,
        $db,
        $table
    ) {
        parent::__construct($response, $dbi, $template);
        $this->db = $db;
        $this->table = $table;
    }
}
