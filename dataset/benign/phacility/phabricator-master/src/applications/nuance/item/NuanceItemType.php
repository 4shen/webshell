<?php

abstract class NuanceItemType
  extends Phobject {

  private $viewer;
  private $controller;

  public function setViewer(PhabricatorUser $viewer) {
    $this->viewer = $viewer;
    return $this;
  }

  public function getViewer() {
    return $this->viewer;
  }

  public function setController(PhabricatorController $controller) {
    $this->controller = $controller;
    return $this;
  }

  public function getController() {
    return $this->controller;
  }

  public function canUpdateItems() {
    return false;
  }

  final public function buildItemView(NuanceItem $item) {
    return $this->newItemView($item);
  }

  final public function buildItemWorkView(NuanceItem $item) {
    return $this->newItemView($item);
  }

  protected function newItemView(NuanceItem $item) {
    return null;
  }

  public function getItemTypeDisplayIcon() {
    return null;
  }

  public function getItemActions(NuanceItem $item) {
    return array();
  }

  public function getItemCurtainPanels(NuanceItem $item) {
    return array();
  }

  abstract public function getItemTypeDisplayName();
  abstract public function getItemDisplayName(NuanceItem $item);

  final public function updateItem(NuanceItem $item) {
    if (!$this->canUpdateItems()) {
      throw new Exception(
        pht(
          'This item type ("%s", of class "%s") can not update items.',
          $this->getItemTypeConstant(),
          get_class($this)));
    }

    $this->updateItemFromSource($item);
  }

  protected function updateItemFromSource(NuanceItem $item) {
    throw new PhutilMethodNotImplementedException();
  }

  final public function getItemTypeConstant() {
    return $this->getPhobjectClassConstant('ITEMTYPE', 64);
  }

  final public static function getAllItemTypes() {
    return id(new PhutilClassMapQuery())
      ->setAncestorClass(__CLASS__)
      ->setUniqueMethod('getItemTypeConstant')
      ->execute();
  }

  final protected function newItemAction(NuanceItem $item, $key) {
    $id = $item->getID();
    $action_uri = "/nuance/item/action/{$id}/{$key}/";

    return id(new PhabricatorActionView())
      ->setHref($action_uri);
  }

  final protected function newCurtainPanel(NuanceItem $item) {
    return id(new PHUICurtainPanelView());
  }

  final public function buildActionResponse(NuanceItem $item, $action) {
    return $this->handleAction($item, $action);
  }

  protected function handleAction(NuanceItem $item, $action) {
    return null;
  }

  final public function buildWorkCommands(NuanceItem $item) {
    return $this->newWorkCommands($item);
  }

  final protected function newContentSource(
    NuanceItem $item,
    $agent_phid) {
    return PhabricatorContentSource::newForSource(
      NuanceContentSource::SOURCECONST,
      array(
        'itemPHID' => $item->getPHID(),
        'agentPHID' => $agent_phid,
      ));
  }

  protected function getActingAsPHID(NuanceItem $item) {
    return id(new PhabricatorNuanceApplication())->getPHID();
  }

  protected function newCommand($command_key) {
    return id(new NuanceItemCommandSpec())
      ->setCommandKey($command_key);
  }
}
