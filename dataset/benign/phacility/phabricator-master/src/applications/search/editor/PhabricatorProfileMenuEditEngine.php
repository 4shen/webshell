<?php

final class PhabricatorProfileMenuEditEngine
  extends PhabricatorEditEngine {

  const ENGINECONST = 'search.profilemenu';

  private $menuEngine;
  private $profileObject;
  private $customPHID;
  private $newMenuItemConfiguration;
  private $isBuiltin;

  public function isEngineConfigurable() {
    return false;
  }

  public function setMenuEngine(PhabricatorProfileMenuEngine $engine) {
    $this->menuEngine = $engine;
    return $this;
  }

  public function getMenuEngine() {
    return $this->menuEngine;
  }

  public function setProfileObject($profile_object) {
    $this->profileObject = $profile_object;
    return $this;
  }

  public function getProfileObject() {
    return $this->profileObject;
  }

  public function setCustomPHID($custom_phid) {
    $this->customPHID = $custom_phid;
    return $this;
  }

  public function getCustomPHID() {
    return $this->customPHID;
  }

  public function setNewMenuItemConfiguration(
    PhabricatorProfileMenuItemConfiguration $configuration) {
    $this->newMenuItemConfiguration = $configuration;
    return $this;
  }

  public function getNewMenuItemConfiguration() {
    return $this->newMenuItemConfiguration;
  }

  public function setIsBuiltin($is_builtin) {
    $this->isBuiltin = $is_builtin;
    return $this;
  }

  public function getIsBuiltin() {
    return $this->isBuiltin;
  }

  public function getEngineName() {
    return pht('Profile Menu Items');
  }

  public function getSummaryHeader() {
    return pht('Edit Profile Menu Item Configurations');
  }

  public function getSummaryText() {
    return pht('This engine is used to modify menu items on profiles.');
  }

  public function getEngineApplicationClass() {
    return 'PhabricatorSearchApplication';
  }

  protected function newEditableObject() {
    if (!$this->newMenuItemConfiguration) {
      throw new Exception(
        pht(
          'Profile menu items can not be generated without an '.
          'object context.'));
    }

    return clone $this->newMenuItemConfiguration;
  }

  protected function newObjectQuery() {
    return id(new PhabricatorProfileMenuItemConfigurationQuery());
  }

  protected function getObjectCreateTitleText($object) {
    if ($this->getIsBuiltin()) {
      return pht('Edit Builtin Item');
    } else {
      return pht('Create Menu Item');
    }
  }

  protected function getObjectCreateButtonText($object) {
    if ($this->getIsBuiltin()) {
      return pht('Save Changes');
    } else {
      return pht('Create Menu Item');
    }
  }

  protected function getObjectEditTitleText($object) {
    $object->willGetMenuItemViewList(array($object));
    return pht('Edit Menu Item: %s', $object->getDisplayName());
  }

  protected function getObjectEditShortText($object) {
    return pht('Edit Menu Item');
  }

  protected function getObjectCreateShortText() {
    return pht('Edit Menu Item');
  }

  protected function getObjectName() {
    return pht('Menu Item');
  }

  protected function getObjectCreateCancelURI($object) {
    return $this->getMenuEngine()->getConfigureURI();
  }

  protected function getObjectViewURI($object) {
    return $this->getMenuEngine()->getConfigureURI();
  }

  protected function buildCustomEditFields($object) {
    $item = $object->getMenuItem();
    $fields = $item->buildEditEngineFields($object);

    $type_property =
      PhabricatorProfileMenuItemConfigurationTransaction::TYPE_PROPERTY;

    foreach ($fields as $field) {
      $field
        ->setTransactionType($type_property)
        ->setMetadataValue('property.key', $field->getKey());
    }

    return $fields;
  }

  protected function getValidationExceptionShortMessage(
    PhabricatorApplicationTransactionValidationException $ex,
    PhabricatorEditField $field) {

    // Menu item properties all have the same transaction type, so we need
    // to make sure errors about a specific property (like the URI for a
    // link) are only applied to the field for that particular property. If
    // we don't do this, the red error text like "Required" will display
    // next to every field.

    $property_type =
      PhabricatorProfileMenuItemConfigurationTransaction::TYPE_PROPERTY;

    $xaction_type = $field->getTransactionType();
    if ($xaction_type == $property_type) {
      $field_key = $field->getKey();
      foreach ($ex->getErrors() as $error) {
        if ($error->getType() !== $xaction_type) {
          continue;
        }

        $xaction = $error->getTransaction();
        if (!$xaction) {
          continue;
        }

        $xaction_setting = $xaction->getMetadataValue('property.key');
        if ($xaction_setting != $field_key) {
          continue;
        }

        $short_message = $error->getShortMessage();
        if ($short_message !== null) {
          return $short_message;
        }
      }

      return null;
    }

    return parent::getValidationExceptionShortMessage($ex, $field);
  }

}
