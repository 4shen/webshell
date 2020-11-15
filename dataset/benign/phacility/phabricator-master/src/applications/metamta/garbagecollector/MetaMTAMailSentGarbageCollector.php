<?php

final class MetaMTAMailSentGarbageCollector
  extends PhabricatorGarbageCollector {

  const COLLECTORCONST = 'metamta.sent';

  public function getCollectorName() {
    return pht('Mail (Sent)');
  }

  public function getDefaultRetentionPolicy() {
    return phutil_units('90 days in seconds');
  }

  protected function collectGarbage() {
    $mails = id(new PhabricatorMetaMTAMail())->loadAllWhere(
      'dateCreated < %d LIMIT 100',
      $this->getGarbageEpoch());

    $engine = new PhabricatorDestructionEngine();
    foreach ($mails as $mail) {
      $engine->destroyObject($mail);
    }

    return (count($mails) == 100);
  }

}
