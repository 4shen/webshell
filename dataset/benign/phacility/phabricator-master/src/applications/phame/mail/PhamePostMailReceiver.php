<?php

final class PhamePostMailReceiver
  extends PhabricatorObjectMailReceiver {

  public function isEnabled() {
    return PhabricatorApplication::isClassInstalled(
      'PhabricatorPhameApplication');
  }

  protected function getObjectPattern() {
    return 'J[1-9]\d*';
  }

  protected function loadObject($pattern, PhabricatorUser $viewer) {
    $id = (int)substr($pattern, 1);

    return id(new PhamePostQuery())
      ->setViewer($viewer)
      ->withIDs(array($id))
      ->executeOne();
  }

  protected function getTransactionReplyHandler() {
    return new PhamePostReplyHandler();
  }

}
