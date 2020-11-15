<?php
/**
 * Holds the PhpMyAdmin\Controllers\Server\SqlController
 */

declare(strict_types=1);

namespace PhpMyAdmin\Controllers\Server;

use PhpMyAdmin\Common;
use PhpMyAdmin\Config\PageSettings;
use PhpMyAdmin\Controllers\AbstractController;
use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Response;
use PhpMyAdmin\SqlQueryForm;
use PhpMyAdmin\Template;

/**
 * Server SQL executor
 */
class SqlController extends AbstractController
{
    /** @var SqlQueryForm */
    private $sqlQueryForm;

    /**
     * @param Response          $response     Response object
     * @param DatabaseInterface $dbi          DatabaseInterface object
     * @param Template          $template     Template that should be used (if provided, default one otherwise)
     * @param SqlQueryForm      $sqlQueryForm SqlQueryForm instance
     */
    public function __construct($response, $dbi, Template $template, SqlQueryForm $sqlQueryForm)
    {
        parent::__construct($response, $dbi, $template);
        $this->sqlQueryForm = $sqlQueryForm;
    }

    public function index(): void
    {
        $header = $this->response->getHeader();
        $scripts = $header->getScripts();
        $scripts->addFile('makegrid.js');
        $scripts->addFile('vendor/jquery/jquery.uitablefilter.js');
        $scripts->addFile('sql.js');

        PageSettings::showGroup('Sql');

        Common::server();

        $this->response->addHTML($this->sqlQueryForm->getHtml());
    }
}
