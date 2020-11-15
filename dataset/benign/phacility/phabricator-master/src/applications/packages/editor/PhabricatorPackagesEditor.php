<?php

abstract class PhabricatorPackagesEditor
  extends PhabricatorApplicationTransactionEditor {

  public function getEditorApplicationClass() {
    return 'PhabricatorPackagesApplication';
  }

  protected function supportsSearch() {
    return true;
  }

  protected function shouldPublishFeedStory(
    PhabricatorLiskDAO $object,
    array $xactions) {
    return true;
  }

}
