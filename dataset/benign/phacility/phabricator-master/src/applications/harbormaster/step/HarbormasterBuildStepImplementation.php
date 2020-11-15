<?php

/**
 * @task autotarget Automatic Targets
 */
abstract class HarbormasterBuildStepImplementation extends Phobject {

  private $settings;
  private $currentWorkerTaskID;

  public function setCurrentWorkerTaskID($id) {
    $this->currentWorkerTaskID = $id;
    return $this;
  }

  public function getCurrentWorkerTaskID() {
    return $this->currentWorkerTaskID;
  }

  public static function getImplementations() {
    return id(new PhutilClassMapQuery())
      ->setAncestorClass(__CLASS__)
      ->execute();
  }

  public static function getImplementation($class) {
    $base = idx(self::getImplementations(), $class);

    if ($base) {
      return (clone $base);
    }

    return null;
  }

  public static function requireImplementation($class) {
    if (!$class) {
      throw new Exception(pht('No implementation is specified!'));
    }

    $implementation = self::getImplementation($class);
    if (!$implementation) {
      throw new Exception(pht('No such implementation "%s" exists!', $class));
    }

    return $implementation;
  }

  /**
   * The name of the implementation.
   */
  abstract public function getName();

  public function getBuildStepGroupKey() {
    return HarbormasterOtherBuildStepGroup::GROUPKEY;
  }

  /**
   * The generic description of the implementation.
   */
  public function getGenericDescription() {
    return '';
  }

  /**
   * The description of the implementation, based on the current settings.
   */
  public function getDescription() {
    return $this->getGenericDescription();
  }

  public function getEditInstructions() {
    return null;
  }

  /**
   * Run the build target against the specified build.
   */
  abstract public function execute(
    HarbormasterBuild $build,
    HarbormasterBuildTarget $build_target);

  /**
   * Gets the settings for this build step.
   */
  public function getSettings() {
    return $this->settings;
  }

  public function getSetting($key, $default = null) {
    return idx($this->settings, $key, $default);
  }

  /**
   * Loads the settings for this build step implementation from a build
   * step or target.
   */
  final public function loadSettings($build_object) {
    $this->settings = $build_object->getDetails();
    return $this;
  }

  /**
   * Return the name of artifacts produced by this command.
   *
   * Future steps will calculate all available artifact mappings
   * before them and filter on the type.
   *
   * @return array The mappings of artifact names to their types.
   */
  public function getArtifactInputs() {
    return array();
  }

  public function getArtifactOutputs() {
    return array();
  }

  public function getDependencies(HarbormasterBuildStep $build_step) {
    $dependencies = $build_step->getDetail('dependsOn', array());

    $inputs = $build_step->getStepImplementation()->getArtifactInputs();
    $inputs = ipull($inputs, null, 'key');

    $artifacts = $this->getAvailableArtifacts(
      $build_step->getBuildPlan(),
      $build_step,
      null);

    foreach ($artifacts as $key => $type) {
      if (!array_key_exists($key, $inputs)) {
        unset($artifacts[$key]);
      }
    }

    $artifact_steps = ipull($artifacts, 'step');
    $artifact_steps = mpull($artifact_steps, 'getPHID');

    $dependencies = array_merge($dependencies, $artifact_steps);

    return $dependencies;
  }

  /**
   * Returns a list of all artifacts made available in the build plan.
   */
  public static function getAvailableArtifacts(
    HarbormasterBuildPlan $build_plan,
    $current_build_step,
    $artifact_type) {

    $steps = id(new HarbormasterBuildStepQuery())
      ->setViewer(PhabricatorUser::getOmnipotentUser())
      ->withBuildPlanPHIDs(array($build_plan->getPHID()))
      ->execute();

    $artifacts = array();

    $artifact_arrays = array();
    foreach ($steps as $step) {
      if ($current_build_step !== null &&
        $step->getPHID() === $current_build_step->getPHID()) {

        continue;
      }

      $implementation = $step->getStepImplementation();
      $array = $implementation->getArtifactOutputs();
      $array = ipull($array, 'type', 'key');
      foreach ($array as $name => $type) {
        if ($type !== $artifact_type && $artifact_type !== null) {
          continue;
        }
        $artifacts[$name] = array('type' => $type, 'step' => $step);
      }
    }

    return $artifacts;
  }

  /**
   * Convert a user-provided string with variables in it, like:
   *
   *   ls ${dirname}
   *
   * ...into a string with variables merged into it safely:
   *
   *   ls 'dir with spaces'
   *
   * @param string Name of a `vxsprintf` function, like @{function:vcsprintf}.
   * @param string User-provided pattern string containing `${variables}`.
   * @param dict   List of available replacement variables.
   * @return string String with variables replaced safely into it.
   */
  protected function mergeVariables($function, $pattern, array $variables) {
    $regexp = '@\\$\\{(?P<name>[a-z\\./_-]+)\\}@';

    $matches = null;
    preg_match_all($regexp, $pattern, $matches);

    $argv = array();
    foreach ($matches['name'] as $name) {
      if (!array_key_exists($name, $variables)) {
        throw new Exception(pht("No such variable '%s'!", $name));
      }
      $argv[] = $variables[$name];
    }

    $pattern = str_replace('%', '%%', $pattern);
    $pattern = preg_replace($regexp, '%s', $pattern);

    return call_user_func($function, $pattern, $argv);
  }

  public function getFieldSpecifications() {
    return array();
  }

  protected function formatSettingForDescription($key, $default = null) {
    return $this->formatValueForDescription($this->getSetting($key, $default));
  }

  protected function formatValueForDescription($value) {
    if (strlen($value)) {
      return phutil_tag('strong', array(), $value);
    } else {
      return phutil_tag('em', array(), pht('(null)'));
    }
  }

  public function supportsWaitForMessage() {
    return false;
  }

  public function shouldWaitForMessage(HarbormasterBuildTarget $target) {
    if (!$this->supportsWaitForMessage()) {
      return false;
    }

    $wait = $target->getDetail('builtin.wait-for-message');
    return ($wait == 'wait');
  }

  protected function shouldAbort(
    HarbormasterBuild $build,
    HarbormasterBuildTarget $target) {

    return $build->getBuildGeneration() !== $target->getBuildGeneration();
  }

  protected function resolveFutures(
    HarbormasterBuild $build,
    HarbormasterBuildTarget $target,
    array $futures) {

    $did_close = false;
    $wait_start = PhabricatorTime::getNow();

    $futures = new FutureIterator($futures);
    foreach ($futures->setUpdateInterval(5) as $key => $future) {
      if ($future !== null) {
        continue;
      }

      $build->reload();
      if ($this->shouldAbort($build, $target)) {
        throw new HarbormasterBuildAbortedException();
      }

      // See PHI916. If we're waiting on a remote system for a while, clean
      // up database connections to reduce the cost of having a large number
      // of processes babysitting an `ssh ... ./run-huge-build.sh` process on
      // a build host.
      if (!$did_close) {
        $now = PhabricatorTime::getNow();
        $elapsed = ($now - $wait_start);
        $idle_limit = 5;

        if ($elapsed >= $idle_limit) {
          LiskDAO::closeIdleConnections();
          $did_close = true;
        }
      }
    }

  }

  protected function logHTTPResponse(
    HarbormasterBuild $build,
    HarbormasterBuildTarget $build_target,
    BaseHTTPFuture $future,
    $label) {

    list($status, $body, $headers) = $future->resolve();

    $header_lines = array();

    // TODO: We don't currently preserve the entire "HTTP" response header, but
    // should. Once we do, reproduce it here faithfully.
    $status_code = $status->getStatusCode();
    $header_lines[] = "HTTP {$status_code}";

    foreach ($headers as $header) {
      list($head, $tail) = $header;
      $header_lines[] = "{$head}: {$tail}";
    }
    $header_lines = implode("\n", $header_lines);

    $build_target
      ->newLog($label, 'http.head')
      ->append($header_lines);

    $build_target
      ->newLog($label, 'http.body')
      ->append($body);
  }

  protected function logSilencedCall(
    HarbormasterBuild $build,
    HarbormasterBuildTarget $build_target,
    $label) {

    $build_target
      ->newLog($label, 'silenced')
      ->append(
        pht(
          'Declining to make service call because `phabricator.silent` is '.
          'enabled in configuration.'));
  }

  public function willStartBuild(
    PhabricatorUser $viewer,
    HarbormasterBuildable $buildable,
    HarbormasterBuild $build,
    HarbormasterBuildPlan $plan,
    HarbormasterBuildStep $step) {
    return;
  }


/* -(  Automatic Targets  )-------------------------------------------------- */


  public function getBuildStepAutotargetStepKey() {
    return null;
  }

  public function getBuildStepAutotargetPlanKey() {
    throw new PhutilMethodNotImplementedException();
  }

  public function shouldRequireAutotargeting() {
    return false;
  }

}
