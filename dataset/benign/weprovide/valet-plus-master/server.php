<?php

/**
 * Define the user's "~/.valet" path.
 */

define('VALET_HOME_PATH', posix_getpwuid(posix_geteuid())['dir'].'/.valet');
define('VALET_STATIC_PREFIX', '41c270e4-5535-4daa-b23e-c269744c2f45');

/**
 * Load the Valet configuration.
 */
$valetConfig = json_decode(
    file_get_contents(VALET_HOME_PATH.'/config.json'), true
);

/**
 * Parse the URI and site / host for the incoming request.
 */
$uri = urldecode(
    explode("?", $_SERVER['REQUEST_URI'])[0]
);

$siteName = basename(
    // Filter host to support xip.io feature
    $_SERVER['HTTP_HOST'],
    '.'.$valetConfig['domain']
);

if (strpos($siteName, 'www.') === 0) {
    $siteName = substr($siteName, 4);
}

/**
 * Determine a possible rewrite.
 */
if (isset($valetConfig['rewrites'])) {
    foreach ($valetConfig['rewrites'] as $site => $rewrites) {
        foreach ($rewrites as $rewrite) {
            if ($rewrite == $siteName) {
                $siteName = $site;
                break;
            }
        }
    }
}

/**
 * Determine the fully qualified path to the site.
 */
$valetSitePath = apcu_fetch('valet_site_path'.$siteName);
$domain = array_slice(explode('.', $siteName), -1)[0];

if(!$valetSitePath) {
    foreach ($valetConfig['paths'] as $path) {
        if (is_dir($path.'/'.$siteName)) {
            $valetSitePath = $path.'/'.$siteName;
            break;
        }

        if (is_dir($path.'/'.$domain)) {
            $valetSitePath = $path.'/'.$domain;
            break;
        }
    }

    if (!$valetSitePath) {
        http_response_code(404);
        require __DIR__.'/cli/templates/404.php';
        exit;
    }

    $valetSitePath = realpath($valetSitePath);

    apcu_add('valet_site_path'.$siteName, $valetSitePath, 3600);
}

/**
 * Find the appropriate Valet driver for the request.
 */
$valetDriver = null;

require __DIR__.'/cli/drivers/require.php';

$valetDriver = ValetDriver::assign($valetSitePath, $siteName, $uri);

if (! $valetDriver) {
    http_response_code(404);
    echo 'Could not find suitable driver for your project.';
    exit;
}

/**
 * ngrok uses the X-Original-Host to store the forwarded hostname.
 */
if (isset($_SERVER['HTTP_X_ORIGINAL_HOST']) && !isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $_SERVER['HTTP_X_FORWARDED_HOST'] = $_SERVER['HTTP_X_ORIGINAL_HOST'];
}

/**
 * Allow driver to mutate incoming URL.
 */
$uri = $valetDriver->mutateUri($uri);

/**
 * Determine if the incoming request is for a static file.
 */
$isPhpFile = pathinfo($uri, PATHINFO_EXTENSION) === 'php';

if ($uri !== '/' && ! $isPhpFile && $staticFilePath = $valetDriver->isStaticFile($valetSitePath, $siteName, $uri)) {
    return $valetDriver->serveStaticFile($staticFilePath, $valetSitePath, $siteName, $uri);
}

/**
 * Attempt to dispatch to a front controller.
 */
$frontControllerPath = $valetDriver->frontControllerPath(
    $valetSitePath, $siteName, $uri
);

if (! $frontControllerPath) {
    http_response_code(404);
    echo 'Did not get front controller from driver. Please return a front controller to be executed.';
    exit;
}

chdir(dirname($frontControllerPath));

unset($domain, $path, $siteName, $uri, $valetConfig, $valetDriver, $valetSitePath);

require $frontControllerPath;
