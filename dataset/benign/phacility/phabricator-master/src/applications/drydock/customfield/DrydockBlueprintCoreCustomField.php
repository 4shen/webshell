<?php

final class DrydockBlueprintCoreCustomField
  extends DrydockBlueprintCustomField
  implements PhabricatorStandardCustomFieldInterface {

  public function getStandardCustomFieldNamespace() {
    return 'drydock:core';
  }

  public function createFields($object) {
    // If this is a generic object without an attached implementation (for
    // example, via ApplicationSearch), just don't build any custom fields.
    if (!$object->hasImplementation()) {
      return array();
    }

    $impl = $object->getImplementation();
    $specs = $impl->getFieldSpecifications();

    return PhabricatorStandardCustomField::buildStandardFields($this, $specs);
  }

  public function shouldUseStorage() {
    return false;
  }

  public function readValueFromObject(PhabricatorCustomFieldInterface $object) {
    $key = $this->getProxy()->getRawStandardFieldKey();
    $this->setValueFromStorage($object->getDetail($key));
    $this->didSetValueFromStorage();
  }

  public function applyApplicationTransactionInternalEffects(
    PhabricatorApplicationTransaction $xaction) {
    $object = $this->getObject();
    $key = $this->getProxy()->getRawStandardFieldKey();

    $this->setValueFromApplicationTransactions($xaction->getNewValue());
    $value = $this->getValueForStorage();

    $object->setDetail($key, $value);
  }

  public function getBlueprintFieldValue() {
    return $this->getProxy()->getFieldValue();
  }

}
