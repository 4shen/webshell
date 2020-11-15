<?php

final class HarbormasterHTTPRequestBuildStepImplementation
  extends HarbormasterBuildStepImplementation {

  public function getName() {
    return pht('Make HTTP Request');
  }

  public function getGenericDescription() {
    return pht('Make an HTTP request.');
  }

  public function getBuildStepGroupKey() {
    return HarbormasterExternalBuildStepGroup::GROUPKEY;
  }

  public function getDescription() {
    $domain = null;
    $uri = $this->getSetting('uri');
    if ($uri) {
      $domain = id(new PhutilURI($uri))->getDomain();
    }

    $method = $this->formatSettingForDescription('method', 'POST');
    $domain = $this->formatValueForDescription($domain);

    if ($this->getSetting('credential')) {
      return pht(
        'Make an authenticated HTTP %s request to %s.',
        $method,
        $domain);
    } else {
      return pht(
        'Make an HTTP %s request to %s.',
        $method,
        $domain);
    }
  }

  public function execute(
    HarbormasterBuild $build,
    HarbormasterBuildTarget $build_target) {

    $viewer = PhabricatorUser::getOmnipotentUser();

    if (PhabricatorEnv::getEnvConfig('phabricator.silent')) {
      $this->logSilencedCall($build, $build_target, pht('HTTP Request'));
      throw new HarbormasterBuildFailureException();
    }

    $settings = $this->getSettings();
    $variables = $build_target->getVariables();

    $uri = $this->mergeVariables(
      'vurisprintf',
      $settings['uri'],
      $variables);

    $method = nonempty(idx($settings, 'method'), 'POST');

    $future = id(new HTTPSFuture($uri))
      ->setMethod($method)
      ->setTimeout(60);

    $credential_phid = $this->getSetting('credential');
    if ($credential_phid) {
      $key = PassphrasePasswordKey::loadFromPHID(
        $credential_phid,
        $viewer);
      $future->setHTTPBasicAuthCredentials(
        $key->getUsernameEnvelope()->openEnvelope(),
        $key->getPasswordEnvelope());
    }

    $this->resolveFutures(
      $build,
      $build_target,
      array($future));

    $this->logHTTPResponse($build, $build_target, $future, $uri);

    list($status) = $future->resolve();
    if ($status->isError()) {
      throw new HarbormasterBuildFailureException();
    }
  }

  public function getFieldSpecifications() {
    return array(
      'uri' => array(
        'name' => pht('URI'),
        'type' => 'text',
        'required' => true,
      ),
      'method' => array(
        'name' => pht('HTTP Method'),
        'type' => 'select',
        'options' => array_fuse(array('POST', 'GET', 'PUT', 'DELETE')),
      ),
      'credential' => array(
        'name' => pht('Credentials'),
        'type' => 'credential',
        'credential.type'
          => PassphrasePasswordCredentialType::CREDENTIAL_TYPE,
        'credential.provides'
          => PassphrasePasswordCredentialType::PROVIDES_TYPE,
      ),
    );
  }

  public function supportsWaitForMessage() {
    return true;
  }

}
