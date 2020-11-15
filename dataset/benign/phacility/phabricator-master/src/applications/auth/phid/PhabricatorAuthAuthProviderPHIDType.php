<?php

final class PhabricatorAuthAuthProviderPHIDType extends PhabricatorPHIDType {

  const TYPECONST = 'AUTH';

  public function getTypeName() {
    return pht('Auth Provider');
  }

  public function newObject() {
    return new PhabricatorAuthProviderConfig();
  }

  public function getPHIDTypeApplicationClass() {
    return 'PhabricatorAuthApplication';
  }

  protected function buildQueryForObjects(
    PhabricatorObjectQuery $query,
    array $phids) {

    return id(new PhabricatorAuthProviderConfigQuery())
      ->withPHIDs($phids);
  }

  public function loadHandles(
    PhabricatorHandleQuery $query,
    array $handles,
    array $objects) {

    foreach ($handles as $phid => $handle) {
      $provider = $objects[$phid]->getProvider();

      if ($provider) {
        $handle->setName($provider->getProviderName());
      }
    }
  }

}
