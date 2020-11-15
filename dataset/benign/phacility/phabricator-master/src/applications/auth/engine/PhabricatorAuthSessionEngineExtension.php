<?php

abstract class PhabricatorAuthSessionEngineExtension
  extends Phobject {

  final public function getExtensionKey() {
    return $this->getPhobjectClassConstant('EXTENSIONKEY');
  }

  final public static function getAllExtensions() {
    return id(new PhutilClassMapQuery())
      ->setAncestorClass(__CLASS__)
      ->setUniqueMethod('getExtensionKey')
      ->execute();
  }

  abstract public function getExtensionName();

  public function didEstablishSession(PhabricatorAuthSessionInfo $info) {
    return;
  }

  public function willServeRequestForUser(PhabricatorUser $user) {
    return;
  }

  public function didLogout(PhabricatorUser $user, array $sessions) {
    return;
  }

}
