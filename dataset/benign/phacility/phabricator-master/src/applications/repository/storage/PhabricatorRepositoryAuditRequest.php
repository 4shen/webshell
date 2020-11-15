<?php

final class PhabricatorRepositoryAuditRequest
  extends PhabricatorRepositoryDAO
  implements PhabricatorPolicyInterface {

  protected $auditorPHID;
  protected $commitPHID;
  protected $auditReasons = array();
  protected $auditStatus;

  private $commit = self::ATTACHABLE;

  protected function getConfiguration() {
    return array(
      self::CONFIG_TIMESTAMPS => false,
      self::CONFIG_SERIALIZATION => array(
        'auditReasons' => self::SERIALIZATION_JSON,
      ),
      self::CONFIG_COLUMN_SCHEMA => array(
        'auditStatus' => 'text64',
      ),
      self::CONFIG_KEY_SCHEMA => array(
        'commitPHID' => array(
          'columns' => array('commitPHID'),
        ),
        'auditorPHID' => array(
          'columns' => array('auditorPHID', 'auditStatus'),
        ),
        'key_unique' => array(
          'columns' => array('commitPHID', 'auditorPHID'),
          'unique' => true,
        ),
      ),
    ) + parent::getConfiguration();
  }

  public function isUser() {
    $user_type = PhabricatorPeopleUserPHIDType::TYPECONST;
    return (phid_get_type($this->getAuditorPHID()) == $user_type);
  }

  public function attachCommit(PhabricatorRepositoryCommit $commit) {
    $this->commit = $commit;
    return $this;
  }

  public function getCommit() {
    return $this->assertAttached($this->commit);
  }

  public function isActiveAudit() {
    switch ($this->getAuditStatus()) {
      case PhabricatorAuditStatusConstants::NONE:
      case PhabricatorAuditStatusConstants::AUDIT_NOT_REQUIRED:
      case PhabricatorAuditStatusConstants::RESIGNED:
      case PhabricatorAuditStatusConstants::CLOSED:
      case PhabricatorAuditStatusConstants::CC:
        return false;
    }

    return true;
  }

  public function isInteresting() {
    switch ($this->getAuditStatus()) {
      case PhabricatorAuditStatusConstants::NONE:
      case PhabricatorAuditStatusConstants::AUDIT_NOT_REQUIRED:
        return false;
    }

    return true;
  }

  public function isResigned() {
    switch ($this->getAuditStatus()) {
      case PhabricatorAuditStatusConstants::RESIGNED:
        return true;
    }

    return false;
  }


/* -(  PhabricatorPolicyInterface  )----------------------------------------- */


  public function getCapabilities() {
    return array(
      PhabricatorPolicyCapability::CAN_VIEW,
    );
  }

  public function getPolicy($capability) {
    return $this->getCommit()->getPolicy($capability);
  }

  public function hasAutomaticCapability($capability, PhabricatorUser $viewer) {
    return $this->getCommit()->hasAutomaticCapability($capability, $viewer);
  }

  public function describeAutomaticCapability($capability) {
    return pht(
      'This audit is attached to a commit, and inherits its policies.');
  }
}
