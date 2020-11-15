<?php

abstract class DiffusionAuditorsHeraldAction
  extends HeraldAction {

  const DO_AUTHORS = 'do.authors';
  const DO_ADD_AUDITORS = 'do.add-auditors';

  public function getActionGroupKey() {
    return HeraldApplicationActionGroup::ACTIONGROUPKEY;
  }

  public function supportsObject($object) {
    return ($object instanceof PhabricatorRepositoryCommit);
  }

  protected function applyAuditors(array $phids, HeraldRule $rule) {
    $adapter = $this->getAdapter();
    $object = $adapter->getObject();

    $auditors = $object->getAudits();

    // Don't try to add commit authors as auditors.
    $authors = array();
    foreach ($phids as $key => $phid) {
      if ($phid == $object->getAuthorPHID()) {
        $authors[] = $phid;
        unset($phids[$key]);
      }
    }

    if ($authors) {
      $this->logEffect(self::DO_AUTHORS, $authors);
      if (!$phids) {
        return;
      }
    }

    $current = array();
    foreach ($auditors as $auditor) {
      if ($auditor->isInteresting()) {
        $current[] = $auditor->getAuditorPHID();
      }
    }

    $allowed_types = array(
      PhabricatorPeopleUserPHIDType::TYPECONST,
      PhabricatorProjectProjectPHIDType::TYPECONST,
      PhabricatorOwnersPackagePHIDType::TYPECONST,
    );

    $targets = $this->loadStandardTargets($phids, $allowed_types, $current);
    if (!$targets) {
      return;
    }

    $phids = array_fuse(array_keys($targets));

    $xaction = $adapter->newTransaction()
      ->setTransactionType(DiffusionCommitAuditorsTransaction::TRANSACTIONTYPE)
      ->setNewValue(
        array(
          '+' => $phids,
        ));

    $adapter->queueTransaction($xaction);

    $this->logEffect(self::DO_ADD_AUDITORS, $phids);
  }

  protected function getActionEffectMap() {
    return array(
      self::DO_AUTHORS => array(
        'icon' => 'fa-user',
        'color' => 'grey',
        'name' => pht('Commit Author'),
      ),
      self::DO_ADD_AUDITORS => array(
        'icon' => 'fa-user',
        'color' => 'green',
        'name' => pht('Added Auditors'),
      ),
    );
  }

  protected function renderActionEffectDescription($type, $data) {
    switch ($type) {
      case self::DO_AUTHORS:
        return pht(
          'Declined to add commit author as auditor: %s.',
          $this->renderHandleList($data));
      case self::DO_ADD_AUDITORS:
        return pht(
          'Added %s auditor(s): %s.',
          phutil_count($data),
          $this->renderHandleList($data));
    }
  }

}
