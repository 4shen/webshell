<?php

/*
 * Global constants
 */

// Define our install type
if (file_exists(__DIR__ . '/../../../../../vendor/bolt/bolt/')) {
    $installType = 'composer';
} else {
    $installType = 'git';
}

// Create a constant that defines the Codeception location
if (!defined('CODECEPTION_ROOT')) {
    define('CODECEPTION_ROOT', realpath(__DIR__));
}

// Create a constant that defines the Codeception data directory location
if (!defined('CODECEPTION_DATA')) {
    define('CODECEPTION_DATA', realpath(codecept_data_dir()));
}

// Create a constant that defines the root location
if (!defined('INSTALL_ROOT')) {
    if ($installType === 'composer') {
        define('INSTALL_ROOT', realpath(CODECEPTION_ROOT . '/../../../../..'));
    } elseif ($installType === 'git') {
        define('INSTALL_ROOT', realpath(CODECEPTION_ROOT . '/../..'));
    }
}

// Create a constant that defines the Bolt code location
if (!defined('BOLT_ROOT')) {
    if ($installType === 'composer') {
        define('BOLT_ROOT', realpath(INSTALL_ROOT . '/vendor/bolt/bolt'));
    } elseif ($installType === 'git') {
        define('BOLT_ROOT', realpath(INSTALL_ROOT));
    }
}

$verbose = false;
foreach ($GLOBALS['argv'] as $value) {
    if (preg_match('/^-[-]{0,1}v/', $value) === 1) {
        $verbose = true;
    }
}
if ($verbose) {
    echo 'Codeception bootstrapped:' . PHP_EOL;
    echo '    Install type:     ' . $installType . PHP_EOL;
    echo '    Install root:     ' . INSTALL_ROOT . PHP_EOL;
    echo '    Bolt code root:   ' . BOLT_ROOT . PHP_EOL;
    echo '    Codeception root: ' . CODECEPTION_ROOT . PHP_EOL;
}
