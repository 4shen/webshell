<?php
/**
 * Used to render the header of PMA's pages
 */

declare(strict_types=1);

namespace PhpMyAdmin;

use PhpMyAdmin\Html\Generator;
use PhpMyAdmin\Navigation\Navigation;
use function defined;
use function gmdate;
use function header;
use function htmlspecialchars;
use function implode;
use function ini_get;
use function is_bool;
use function strlen;
use function strtolower;
use function urlencode;

/**
 * Class used to output the HTTP and HTML headers
 */
class Header
{
    /**
     * Scripts instance
     *
     * @access private
     * @var Scripts
     */
    private $_scripts;
    /**
     * PhpMyAdmin\Console instance
     *
     * @access private
     * @var Console
     */
    private $_console;
    /**
     * Menu instance
     *
     * @access private
     * @var Menu
     */
    private $_menu;
    /**
     * Whether to offer the option of importing user settings
     *
     * @access private
     * @var bool
     */
    private $_userprefsOfferImport;
    /**
     * The page title
     *
     * @access private
     * @var string
     */
    private $_title;
    /**
     * The value for the id attribute for the body tag
     *
     * @access private
     * @var string
     */
    private $_bodyId;
    /**
     * Whether to show the top menu
     *
     * @access private
     * @var bool
     */
    private $_menuEnabled;
    /**
     * Whether to show the warnings
     *
     * @access private
     * @var bool
     */
    private $_warningsEnabled;
    /**
     * Whether the page is in 'print view' mode
     *
     * @access private
     * @var bool
     */
    private $_isPrintView;
    /**
     * Whether we are servicing an ajax request.
     *
     * @access private
     * @var bool
     */
    private $_isAjax;
    /**
     * Whether to display anything
     *
     * @access private
     * @var bool
     */
    private $_isEnabled;
    /**
     * Whether the HTTP headers (and possibly some HTML)
     * have already been sent to the browser
     *
     * @access private
     * @var bool
     */
    private $_headerIsSent;

    /** @var UserPreferences */
    private $userPreferences;

    /** @var Template */
    private $template;

    /**
     * Creates a new class instance
     */
    public function __construct()
    {
        global $db, $table;

        $this->template = new Template();

        $this->_isEnabled = true;
        $this->_isAjax = false;
        $this->_bodyId = '';
        $this->_title = '';
        $this->_console = new Console();
        $this->_menu = new Menu(
            $db ?? '',
            $table ?? ''
        );
        $this->_menuEnabled = true;
        $this->_warningsEnabled = true;
        $this->_isPrintView = false;
        $this->_scripts = new Scripts();
        $this->addDefaultScripts();
        $this->_headerIsSent = false;
        // if database storage for user preferences is transient,
        // offer to load exported settings from localStorage
        // (detection will be done in JavaScript)
        $this->_userprefsOfferImport = false;
        if ($GLOBALS['PMA_Config']->get('user_preferences') == 'session'
            && ! isset($_SESSION['userprefs_autoload'])
        ) {
            $this->_userprefsOfferImport = true;
        }

        $this->userPreferences = new UserPreferences();
    }

    /**
     * Loads common scripts
     */
    private function addDefaultScripts(): void
    {
        // Localised strings
        $this->_scripts->addFile('vendor/jquery/jquery.min.js');
        $this->_scripts->addFile('vendor/jquery/jquery-migrate.js');
        $this->_scripts->addFile('vendor/sprintf.js');
        $this->_scripts->addFile('ajax.js');
        $this->_scripts->addFile('keyhandler.js');
        $this->_scripts->addFile('vendor/bootstrap/bootstrap.bundle.min.js');
        $this->_scripts->addFile('vendor/jquery/jquery-ui.min.js');
        $this->_scripts->addFile('vendor/js.cookie.js');
        $this->_scripts->addFile('vendor/jquery/jquery.mousewheel.js');
        $this->_scripts->addFile('vendor/jquery/jquery.event.drag-2.2.js');
        $this->_scripts->addFile('vendor/jquery/jquery.validate.js');
        $this->_scripts->addFile('vendor/jquery/jquery-ui-timepicker-addon.js');
        $this->_scripts->addFile('vendor/jquery/jquery.ba-hashchange-2.0.js');
        $this->_scripts->addFile('vendor/jquery/jquery.debounce-1.0.6.js');
        $this->_scripts->addFile('menu_resizer.js');

        // Cross-framing protection
        if ($GLOBALS['cfg']['AllowThirdPartyFraming'] === false) {
            $this->_scripts->addFile('cross_framing_protection.js');
        }

        $this->_scripts->addFile('rte.js');
        if ($GLOBALS['cfg']['SendErrorReports'] !== 'never') {
            $this->_scripts->addFile('vendor/tracekit.js');
            $this->_scripts->addFile('error_report.js');
        }

        // Here would not be a good place to add CodeMirror because
        // the user preferences have not been merged at this point

        $this->_scripts->addFile('messages.php', ['l' => $GLOBALS['lang']]);
        $this->_scripts->addCode($this->getVariablesForJavaScript());
        $this->_scripts->addFile('config.js');
        $this->_scripts->addFile('doclinks.js');
        $this->_scripts->addFile('functions.js');
        $this->_scripts->addFile('navigation.js');
        $this->_scripts->addFile('indexes.js');
        $this->_scripts->addFile('common.js');
        $this->_scripts->addFile('page_settings.js');
        if ($GLOBALS['cfg']['enable_drag_drop_import'] === true) {
            $this->_scripts->addFile('drag_drop_import.js');
        }
        if (! $GLOBALS['PMA_Config']->get('DisableShortcutKeys')) {
            $this->_scripts->addFile('shortcuts_handler.js');
        }
        $this->_scripts->addCode($this->getJsParamsCode());
    }

    /**
     * Returns, as an array, a list of parameters
     * used on the client side
     *
     * @return array
     */
    public function getJsParams(): array
    {
        global $db, $table;

        $pftext = $_SESSION['tmpval']['pftext'] ?? '';

        $params = [
            // Do not add any separator, JS code will decide
            'common_query' => Url::getCommonRaw([], ''),
            'opendb_url' => Util::getScriptNameForOption(
                $GLOBALS['cfg']['DefaultTabDatabase'],
                'database'
            ),
            'lang' => $GLOBALS['lang'],
            'server' => $GLOBALS['server'],
            'table' => $table ?? '',
            'db' => $db ?? '',
            'token' => $_SESSION[' PMA_token '],
            'text_dir' => $GLOBALS['text_dir'],
            'show_databases_navigation_as_tree' => $GLOBALS['cfg']['ShowDatabasesNavigationAsTree'],
            'pma_text_default_tab' => Util::getTitleForTarget(
                $GLOBALS['cfg']['DefaultTabTable']
            ),
            'pma_text_left_default_tab' => Util::getTitleForTarget(
                $GLOBALS['cfg']['NavigationTreeDefaultTabTable']
            ),
            'pma_text_left_default_tab2' => Util::getTitleForTarget(
                $GLOBALS['cfg']['NavigationTreeDefaultTabTable2']
            ),
            'LimitChars' => $GLOBALS['cfg']['LimitChars'],
            'pftext' => $pftext,
            'confirm' => $GLOBALS['cfg']['Confirm'],
            'LoginCookieValidity' => $GLOBALS['cfg']['LoginCookieValidity'],
            'session_gc_maxlifetime' => (int) ini_get('session.gc_maxlifetime'),
            'logged_in' => isset($GLOBALS['dbi']) ? $GLOBALS['dbi']->isUserType('logged') : false,
            'is_https' => $GLOBALS['PMA_Config']->isHttps(),
            'rootPath' => $GLOBALS['PMA_Config']->getRootPath(),
            'arg_separator' => Url::getArgSeparator(),
            'PMA_VERSION' => PMA_VERSION,
        ];
        if (isset($GLOBALS['cfg']['Server'], $GLOBALS['cfg']['Server']['auth_type'])) {
            $params['auth_type'] = $GLOBALS['cfg']['Server']['auth_type'];
            if (isset($GLOBALS['cfg']['Server']['user'])) {
                $params['user'] = $GLOBALS['cfg']['Server']['user'];
            }
        }

        return $params;
    }

    /**
     * Returns, as a string, a list of parameters
     * used on the client side
     */
    public function getJsParamsCode(): string
    {
        $params = $this->getJsParams();
        foreach ($params as $key => $value) {
            if (is_bool($value)) {
                $params[$key] = $key . ':' . ($value ? 'true' : 'false') . '';
            } else {
                $params[$key] = $key . ':"' . Sanitize::escapeJsString($value) . '"';
            }
        }

        return 'CommonParams.setAll({' . implode(',', $params) . '});';
    }

    /**
     * Disables the rendering of the header
     */
    public function disable(): void
    {
        $this->_isEnabled = false;
    }

    /**
     * Set the ajax flag to indicate whether
     * we are servicing an ajax request
     *
     * @param bool $isAjax Whether we are servicing an ajax request
     */
    public function setAjax(bool $isAjax): void
    {
        $this->_isAjax = $isAjax;
        $this->_console->setAjax($isAjax);
    }

    /**
     * Returns the Scripts object
     *
     * @return Scripts object
     */
    public function getScripts(): Scripts
    {
        return $this->_scripts;
    }

    /**
     * Returns the Menu object
     *
     * @return Menu object
     */
    public function getMenu(): Menu
    {
        return $this->_menu;
    }

    /**
     * Setter for the ID attribute in the BODY tag
     *
     * @param string $id Value for the ID attribute
     */
    public function setBodyId(string $id): void
    {
        $this->_bodyId = htmlspecialchars($id);
    }

    /**
     * Setter for the title of the page
     *
     * @param string $title New title
     */
    public function setTitle(string $title): void
    {
        $this->_title = htmlspecialchars($title);
    }

    /**
     * Disables the display of the top menu
     */
    public function disableMenuAndConsole(): void
    {
        $this->_menuEnabled = false;
        $this->_console->disable();
    }

    /**
     * Disables the display of the top menu
     */
    public function disableWarnings(): void
    {
        $this->_warningsEnabled = false;
    }

    /**
     * Turns on 'print view' mode
     */
    public function enablePrintView(): void
    {
        $this->disableMenuAndConsole();
        $this->setTitle(__('Print view') . ' - phpMyAdmin ' . PMA_VERSION);
        $this->_isPrintView = true;
    }

    /**
     * Generates the header
     *
     * @return string The header
     */
    public function getDisplay(): string
    {
        global $db, $table;

        if ($this->_headerIsSent || ! $this->_isEnabled) {
            return '';
        }

        $recentTable = '';
        if (empty($_REQUEST['recent_table'])) {
            $recentTable = $this->addRecentTable($db, $table);
        }

        if ($this->_isAjax) {
            return $recentTable;
        }

        $this->sendHttpHeaders();

        $baseDir = defined('PMA_PATH_TO_BASEDIR') ? PMA_PATH_TO_BASEDIR : '';
        $uniqueValue = $GLOBALS['PMA_Config']->getThemeUniqueValue();
        $themePath = $GLOBALS['pmaThemePath'];
        $version = self::getVersionParameter();

        // The user preferences have been merged at this point
        // so we can conditionally add CodeMirror
        if ($GLOBALS['cfg']['CodemirrorEnable']) {
            $this->_scripts->addFile('vendor/codemirror/lib/codemirror.js');
            $this->_scripts->addFile('vendor/codemirror/mode/sql/sql.js');
            $this->_scripts->addFile('vendor/codemirror/addon/runmode/runmode.js');
            $this->_scripts->addFile('vendor/codemirror/addon/hint/show-hint.js');
            $this->_scripts->addFile('vendor/codemirror/addon/hint/sql-hint.js');
            if ($GLOBALS['cfg']['LintEnable']) {
                $this->_scripts->addFile('vendor/codemirror/addon/lint/lint.js');
                $this->_scripts->addFile(
                    'codemirror/addon/lint/sql-lint.js'
                );
            }
        }

        $this->_scripts->addCode(
            'ConsoleEnterExecutes='
            . ($GLOBALS['cfg']['ConsoleEnterExecutes'] ? 'true' : 'false')
        );
        $this->_scripts->addFiles($this->_console->getScripts());

        if ($this->_userprefsOfferImport) {
            $this->_scripts->addFile('config.js');
        }

        if ($this->_menuEnabled && $GLOBALS['server'] > 0) {
            $nav = new Navigation(
                $this->template,
                new Relation($GLOBALS['dbi']),
                $GLOBALS['dbi']
            );
            $navigation = $nav->getDisplay();
        }

        $customHeader = Config::renderHeader();

        // offer to load user preferences from localStorage
        if ($this->_userprefsOfferImport) {
            $loadUserPreferences = $this->userPreferences->autoloadGetHeader();
        }

        if ($this->_menuEnabled && $GLOBALS['server'] > 0) {
            $menu = $this->_menu->getDisplay();
        }

        $console = $this->_console->getDisplay();
        $messages = $this->getMessage();

        return $this->template->render('header', [
            'lang' => $GLOBALS['lang'],
            'allow_third_party_framing' => $GLOBALS['cfg']['AllowThirdPartyFraming'],
            'is_print_view' => $this->_isPrintView,
            'base_dir' => $baseDir,
            'unique_value' => $uniqueValue,
            'theme_path' => $themePath,
            'version' => $version,
            'text_dir' => $GLOBALS['text_dir'],
            'server' => $GLOBALS['server'] ?? null,
            'title' => $this->getPageTitle(),
            'scripts' => $this->_scripts->getDisplay(),
            'body_id' => $this->_bodyId,
            'navigation' => $navigation ?? '',
            'custom_header' => $customHeader,
            'load_user_preferences' => $loadUserPreferences ?? '',
            'show_hint' => $GLOBALS['cfg']['ShowHint'],
            'is_warnings_enabled' => $this->_warningsEnabled,
            'is_menu_enabled' => $this->_menuEnabled,
            'menu' => $menu ?? '',
            'console' => $console,
            'messages' => $messages,
            'recent_table' => $recentTable,
        ]);
    }

    /**
     * Returns the message to be displayed at the top of
     * the page, including the executed SQL query, if any.
     */
    public function getMessage(): string
    {
        $retval = '';
        $message = '';
        if (! empty($GLOBALS['message'])) {
            $message = $GLOBALS['message'];
            unset($GLOBALS['message']);
        } elseif (! empty($_REQUEST['message'])) {
            $message = $_REQUEST['message'];
        }
        if (! empty($message)) {
            if (isset($GLOBALS['buffer_message'])) {
                $buffer_message = $GLOBALS['buffer_message'];
            }
            $retval .= Generator::getMessage($message);
            if (isset($buffer_message)) {
                $GLOBALS['buffer_message'] = $buffer_message;
            }
        }

        return $retval;
    }

    /**
     * Sends out the HTTP headers
     */
    public function sendHttpHeaders(): void
    {
        if (defined('TESTSUITE')) {
            return;
        }

        /**
         * Sends http headers
         */
        $GLOBALS['now'] = gmdate('D, d M Y H:i:s') . ' GMT';

        /* Prevent against ClickJacking by disabling framing */
        if (strtolower((string) $GLOBALS['cfg']['AllowThirdPartyFraming']) === 'sameorigin') {
            header(
                'X-Frame-Options: SAMEORIGIN'
            );
        } elseif ($GLOBALS['cfg']['AllowThirdPartyFraming'] !== true) {
            header(
                'X-Frame-Options: DENY'
            );
        }
        header(
            'Referrer-Policy: no-referrer'
        );

        $cspHeaders = $this->getCspHeaders();
        foreach ($cspHeaders as $cspHeader) {
            header($cspHeader);
        }

        // Re-enable possible disabled XSS filters
        // see https://www.owasp.org/index.php/List_of_useful_HTTP_headers
        header(
            'X-XSS-Protection: 1; mode=block'
        );
        // "nosniff", prevents Internet Explorer and Google Chrome from MIME-sniffing
        // a response away from the declared content-type
        // see https://www.owasp.org/index.php/List_of_useful_HTTP_headers
        header(
            'X-Content-Type-Options: nosniff'
        );
        // Adobe cross-domain-policies
        // see https://www.adobe.com/devnet/articles/crossdomain_policy_file_spec.html
        header(
            'X-Permitted-Cross-Domain-Policies: none'
        );
        // Robots meta tag
        // see https://developers.google.com/webmasters/control-crawl-index/docs/robots_meta_tag
        header(
            'X-Robots-Tag: noindex, nofollow'
        );
        Core::noCacheHeader();
        if (! defined('IS_TRANSFORMATION_WRAPPER')) {
            // Define the charset to be used
            header('Content-Type: text/html; charset=utf-8');
        }
        $this->_headerIsSent = true;
    }

    /**
     * If the page is missing the title, this function
     * will set it to something reasonable
     */
    public function getPageTitle(): string
    {
        if (strlen($this->_title) == 0) {
            if ($GLOBALS['server'] > 0) {
                if (strlen($GLOBALS['table'])) {
                    $temp_title = $GLOBALS['cfg']['TitleTable'];
                } elseif (strlen($GLOBALS['db'])) {
                    $temp_title = $GLOBALS['cfg']['TitleDatabase'];
                } elseif (strlen($GLOBALS['cfg']['Server']['host'])) {
                    $temp_title = $GLOBALS['cfg']['TitleServer'];
                } else {
                    $temp_title = $GLOBALS['cfg']['TitleDefault'];
                }
                $this->_title = htmlspecialchars(
                    Util::expandUserString($temp_title)
                );
            } else {
                $this->_title = 'phpMyAdmin';
            }
        }

        return $this->_title;
    }

    /**
     * Get all the CSP allow policy headers
     *
     * @return string[]
     */
    private function getCspHeaders(): array
    {
        global $cfg;

        $mapTileUrls = ' *.tile.openstreetmap.org';
        $captchaUrl = '';
        $cspAllow = $cfg['CSPAllow'];

        if (! empty($cfg['CaptchaLoginPrivateKey'])
            && ! empty($cfg['CaptchaLoginPublicKey'])
        ) {
            $captchaUrl
                = ' https://apis.google.com https://www.google.com/recaptcha/'
                . ' https://www.gstatic.com/recaptcha/ https://ssl.gstatic.com/ ';
        }

        return [

            "Content-Security-Policy: default-src 'self' "
                . $captchaUrl
                . $cspAllow . ';'
                . "script-src 'self' 'unsafe-inline' 'unsafe-eval' "
                . $captchaUrl
                . $cspAllow . ';'
                . "style-src 'self' 'unsafe-inline' "
                . $captchaUrl
                . $cspAllow
                . ';'
                . "img-src 'self' data: "
                . $cspAllow
                . $mapTileUrls
                . $captchaUrl
                . ';'
                . "object-src 'none';",

            "X-Content-Security-Policy: default-src 'self' "
                . $captchaUrl
                . $cspAllow . ';'
                . 'options inline-script eval-script;'
                . 'referrer no-referrer;'
                . "img-src 'self' data: "
                . $cspAllow
                . $mapTileUrls
                . $captchaUrl
                . ';'
                . "object-src 'none';",

            "X-WebKit-CSP: default-src 'self' "
                . $captchaUrl
                . $cspAllow . ';'
                . "script-src 'self' "
                . $captchaUrl
                . $cspAllow
                . " 'unsafe-inline' 'unsafe-eval';"
                . 'referrer no-referrer;'
                . "style-src 'self' 'unsafe-inline' "
                . $captchaUrl
                . ';'
                . "img-src 'self' data: "
                . $cspAllow
                . $mapTileUrls
                . $captchaUrl
                . ';'
                . "object-src 'none';",
        ];
    }

    /**
     * Add recently used table and reload the navigation.
     *
     * @param string $db    Database name where the table is located.
     * @param string $table The table name
     */
    private function addRecentTable(string $db, string $table): string
    {
        $retval = '';
        if ($this->_menuEnabled
            && strlen($table) > 0
            && $GLOBALS['cfg']['NumRecentTables'] > 0
        ) {
            $tmp_result = RecentFavoriteTable::getInstance('recent')->add(
                $db,
                $table
            );
            if ($tmp_result === true) {
                $retval = RecentFavoriteTable::getHtmlUpdateRecentTables();
            } else {
                $error  = $tmp_result;
                $retval = $error->getDisplay();
            }
        }

        return $retval;
    }

    /**
     * Returns the phpMyAdmin version to be appended to the url to avoid caching
     * between versions
     *
     * @return string urlenocded pma version as a parameter
     */
    public static function getVersionParameter(): string
    {
        return 'v=' . urlencode(PMA_VERSION);
    }

    private function getVariablesForJavaScript(): string
    {
        global $cfg, $pmaThemeImage;

        $maxInputVars = ini_get('max_input_vars');
        $maxInputVarsValue = $maxInputVars === false || $maxInputVars === '' ? 'false' : (int) $maxInputVars;

        return $this->template->render('javascript/variables', [
            'first_day_of_calendar' => $cfg['FirstDayOfCalendar'],
            'pma_theme_image' => $pmaThemeImage,
            'max_input_vars' => $maxInputVarsValue,
        ]);
    }
}
