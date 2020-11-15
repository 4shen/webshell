<?php

final class HeraldContentSourceField extends HeraldField {

  const FIELDCONST = 'contentsource';

  public function getHeraldFieldName() {
    return pht('Content source');
  }

  public function getFieldGroupKey() {
    return HeraldEditFieldGroup::FIELDGROUPKEY;
  }

  public function getHeraldFieldValue($object) {
    return $this->getAdapter()->getContentSource()->getSource();
  }

  public function getHeraldFieldConditions() {
    return array(
      HeraldAdapter::CONDITION_IS,
      HeraldAdapter::CONDITION_IS_NOT,
    );
  }

  public function getHeraldFieldValueType($condition) {
    $map = PhabricatorContentSource::getAllContentSources();
    $map = mpull($map, 'getSourceName');
    asort($map);

    return id(new HeraldSelectFieldValue())
      ->setKey(self::FIELDCONST)
      ->setDefault(PhabricatorWebContentSource::SOURCECONST)
      ->setOptions($map);
  }

  public function supportsObject($object) {
    return true;
  }

}
