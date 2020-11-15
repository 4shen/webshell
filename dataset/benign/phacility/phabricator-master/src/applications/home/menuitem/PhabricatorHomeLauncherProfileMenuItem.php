<?php

final class PhabricatorHomeLauncherProfileMenuItem
  extends PhabricatorProfileMenuItem {

  const MENUITEMKEY = 'home.launcher.menu';

  public function getMenuItemTypeName() {
    return pht('More Applications');
  }

  private function getDefaultName() {
    return pht('More Applications');
  }

  public function getMenuItemTypeIcon() {
    return 'fa-ellipsis-h';
  }

  public function canHideMenuItem(
    PhabricatorProfileMenuItemConfiguration $config) {
    return false;
  }

  public function canMakeDefault(
    PhabricatorProfileMenuItemConfiguration $config) {
    return false;
  }

  public function getDisplayName(
    PhabricatorProfileMenuItemConfiguration $config) {
    $name = $config->getMenuItemProperty('name');

    if (strlen($name)) {
      return $name;
    }

    return $this->getDefaultName();
  }

  public function buildEditEngineFields(
    PhabricatorProfileMenuItemConfiguration $config) {
    return array(
      id(new PhabricatorTextEditField())
        ->setKey('name')
        ->setLabel(pht('Name'))
        ->setPlaceholder($this->getDefaultName())
        ->setValue($config->getMenuItemProperty('name')),
    );
  }

  protected function newMenuItemViewList(
    PhabricatorProfileMenuItemConfiguration $config) {
    $viewer = $this->getViewer();

    $name = $this->getDisplayName($config);
    $icon = 'fa-ellipsis-h';
    $uri = '/applications/';

    $item = $this->newItemView()
      ->setURI($uri)
      ->setName($name)
      ->setIcon($icon);

    return array(
      $item,
    );
  }

}
