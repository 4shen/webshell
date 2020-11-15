#!/usr/bin/env php
<?php

// This is a wrapper script for Git, Mercurial, and Subversion. It primarily
// serves to inject "-o StrictHostKeyChecking=no" into the SSH arguments.

// In some cases, Subversion sends us SIGTERM. If we don't catch the signal and
// react to it, we won't run object destructors by default and thus won't clean
// up temporary files. Declare ticks so we can install a signal handler.
if (function_exists('pcntl_async_signals')) {
  pcntl_async_signals(true);
} else {
  declare(ticks = 1);
}

$root = dirname(dirname(dirname(__FILE__)));
require_once $root.'/scripts/__init_script__.php';

// Contrary to the documentation, Git may pass a "-p" flag. If it does, respect
// it and move it before the "--" argument.
$args = new PhutilArgumentParser($argv);
$args->parsePartial(
  array(
    array(
      'name' => 'port',
      'short' => 'p',
      'param' => pht('port'),
      'help' => pht('Port number to connect to.'),
    ),
    array(
      'name' => 'options',
      'short' => 'o',
      'param' => pht('options'),
      'repeat' => true,
      'help' => pht('SSH options.'),
    ),
  ));

$unconsumed_argv = $args->getUnconsumedArgumentVector();

if (function_exists('pcntl_signal')) {
  pcntl_signal(SIGTERM, 'ssh_connect_signal');
}

function ssh_connect_signal($signo) {
  // This is just letting destructors fire. In particular, we want to clean
  // up any temporary files we wrote. See T10547.
  exit(128 + $signo);
}

$pattern = array();
$arguments = array();

$pattern[] = 'ssh';

$pattern[] = '-o';
$pattern[] = 'StrictHostKeyChecking=no';

// This prevents "known host" failures, and covers for issues where HOME is set
// to something unusual.
$pattern[] = '-o';
$pattern[] = 'UserKnownHostsFile=/dev/null';

$as_device = getenv('PHABRICATOR_AS_DEVICE');
$credential_phid = getenv('PHABRICATOR_CREDENTIAL');

if ($as_device) {
  $device = AlmanacKeys::getLiveDevice();
  if (!$device) {
    throw new Exception(
      pht(
        'Attempting to create an SSH connection that authenticates with '.
        'the current device, but this host is not configured as a cluster '.
        'device.'));
  }

  if ($credential_phid) {
    throw new Exception(
      pht(
        'Attempting to proxy an SSH connection that authenticates with '.
        'both the current device and a specific credential. These options '.
        'are mutually exclusive.'));
  }
}

if ($credential_phid) {
  $viewer = PhabricatorUser::getOmnipotentUser();
  $key = PassphraseSSHKey::loadFromPHID($credential_phid, $viewer);

  $pattern[] = '-l %P';
  $arguments[] = $key->getUsernameEnvelope();
  $pattern[] = '-i %P';
  $arguments[] = $key->getKeyfileEnvelope();
}

if ($as_device) {
  $pattern[] = '-l %R';
  $arguments[] = AlmanacKeys::getClusterSSHUser();
  $pattern[] = '-i %R';
  $arguments[] = AlmanacKeys::getKeyPath('device.key');
}

// Subversion passes us a host in the form "domain.com:port", which is not
// valid for normal SSH but which we can parse into a valid "-p" flag.

$passthru_args = $unconsumed_argv;
$host = array_shift($passthru_args);
$parts = explode(':', $host, 2);
$host = $parts[0];

$port = $args->getArg('port');

if (!$port) {
  if (count($parts) == 2) {
    $port = $parts[1];
  }
}

if ($port) {
  $pattern[] = '-p %d';
  $arguments[] = $port;
}

$options = $args->getArg('options');
$allowed_ssh_options = array('SendEnv=GIT_PROTOCOL');

if (!empty($options)) {
  foreach ($options as $option) {
    if (array_search($option, $allowed_ssh_options) !== false) {
      $pattern[] = '-o %s';
      $arguments[] = $option;
    } else {
      throw new Exception(
        pht(
          'Disallowed ssh option "%s" given with "-o". '.
          'Allowed options are: %s.',
          $option,
          implode(', ', $allowed_ssh_options)));
    }
  }
}

$pattern[] = '--';

$pattern[] = '%s';
$arguments[] = $host;

foreach ($passthru_args as $passthru_arg) {
  $pattern[] = '%s';
  $arguments[] = $passthru_arg;
}

$pattern = implode(' ', $pattern);
array_unshift($arguments, $pattern);

$err = newv('PhutilExecPassthru', $arguments)
  ->execute();

exit($err);
