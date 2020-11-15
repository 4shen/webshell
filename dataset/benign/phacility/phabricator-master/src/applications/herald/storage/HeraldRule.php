<?php

final class HeraldRule extends HeraldDAO
  implements
    PhabricatorApplicationTransactionInterface,
    PhabricatorFlaggableInterface,
    PhabricatorPolicyInterface,
    PhabricatorDestructibleInterface,
    PhabricatorIndexableInterface,
    PhabricatorSubscribableInterface {

  const TABLE_RULE_APPLIED = 'herald_ruleapplied';

  protected $name;
  protected $authorPHID;

  protected $contentType;
  protected $mustMatchAll;
  protected $repetitionPolicy;
  protected $ruleType;
  protected $isDisabled = 0;
  protected $triggerObjectPHID;

  protected $configVersion = 38;

  // PHIDs for which this rule has been applied
  private $ruleApplied = self::ATTACHABLE;
  private $validAuthor = self::ATTACHABLE;
  private $author = self::ATTACHABLE;
  private $conditions;
  private $actions;
  private $triggerObject = self::ATTACHABLE;

  const REPEAT_EVERY = 'every';
  const REPEAT_FIRST = 'first';
  const REPEAT_CHANGE = 'change';

  protected function getConfiguration() {
    return array(
      self::CONFIG_AUX_PHID => true,
      self::CONFIG_COLUMN_SCHEMA => array(
        'name' => 'sort255',
        'contentType' => 'text255',
        'mustMatchAll' => 'bool',
        'configVersion' => 'uint32',
        'repetitionPolicy' => 'text32',
        'ruleType' => 'text32',
        'isDisabled' => 'uint32',
        'triggerObjectPHID' => 'phid?',
      ),
      self::CONFIG_KEY_SCHEMA => array(
        'key_name' => array(
          'columns' => array('name(128)'),
        ),
        'key_author' => array(
          'columns' => array('authorPHID'),
        ),
        'key_ruletype' => array(
          'columns' => array('ruleType'),
        ),
        'key_trigger' => array(
          'columns' => array('triggerObjectPHID'),
        ),
      ),
    ) + parent::getConfiguration();
  }

  public function generatePHID() {
    return PhabricatorPHID::generateNewPHID(HeraldRulePHIDType::TYPECONST);
  }

  public function getRuleApplied($phid) {
    return $this->assertAttachedKey($this->ruleApplied, $phid);
  }

  public function setRuleApplied($phid, $applied) {
    if ($this->ruleApplied === self::ATTACHABLE) {
      $this->ruleApplied = array();
    }
    $this->ruleApplied[$phid] = $applied;
    return $this;
  }

  public function loadConditions() {
    if (!$this->getID()) {
      return array();
    }
    return id(new HeraldCondition())->loadAllWhere(
      'ruleID = %d',
      $this->getID());
  }

  public function attachConditions(array $conditions) {
    assert_instances_of($conditions, 'HeraldCondition');
    $this->conditions = $conditions;
    return $this;
  }

  public function getConditions() {
    // TODO: validate conditions have been attached.
    return $this->conditions;
  }

  public function loadActions() {
    if (!$this->getID()) {
      return array();
    }
    return id(new HeraldActionRecord())->loadAllWhere(
      'ruleID = %d',
      $this->getID());
  }

  public function attachActions(array $actions) {
    // TODO: validate actions have been attached.
    assert_instances_of($actions, 'HeraldActionRecord');
    $this->actions = $actions;
    return $this;
  }

  public function getActions() {
    return $this->actions;
  }

  public function saveConditions(array $conditions) {
    assert_instances_of($conditions, 'HeraldCondition');
    return $this->saveChildren(
      id(new HeraldCondition())->getTableName(),
      $conditions);
  }

  public function saveActions(array $actions) {
    assert_instances_of($actions, 'HeraldActionRecord');
    return $this->saveChildren(
      id(new HeraldActionRecord())->getTableName(),
      $actions);
  }

  protected function saveChildren($table_name, array $children) {
    assert_instances_of($children, 'HeraldDAO');

    if (!$this->getID()) {
      throw new PhutilInvalidStateException('save');
    }

    foreach ($children as $child) {
      $child->setRuleID($this->getID());
    }

    $this->openTransaction();
      queryfx(
        $this->establishConnection('w'),
        'DELETE FROM %T WHERE ruleID = %d',
        $table_name,
        $this->getID());
      foreach ($children as $child) {
        $child->save();
      }
    $this->saveTransaction();
  }

  public function delete() {
    $this->openTransaction();
      queryfx(
        $this->establishConnection('w'),
        'DELETE FROM %T WHERE ruleID = %d',
        id(new HeraldCondition())->getTableName(),
        $this->getID());
      queryfx(
        $this->establishConnection('w'),
        'DELETE FROM %T WHERE ruleID = %d',
        id(new HeraldActionRecord())->getTableName(),
        $this->getID());
      $result = parent::delete();
    $this->saveTransaction();

    return $result;
  }

  public function hasValidAuthor() {
    return $this->assertAttached($this->validAuthor);
  }

  public function attachValidAuthor($valid) {
    $this->validAuthor = $valid;
    return $this;
  }

  public function getAuthor() {
    return $this->assertAttached($this->author);
  }

  public function attachAuthor(PhabricatorUser $user) {
    $this->author = $user;
    return $this;
  }

  public function isGlobalRule() {
    return ($this->getRuleType() === HeraldRuleTypeConfig::RULE_TYPE_GLOBAL);
  }

  public function isPersonalRule() {
    return ($this->getRuleType() === HeraldRuleTypeConfig::RULE_TYPE_PERSONAL);
  }

  public function isObjectRule() {
    return ($this->getRuleType() == HeraldRuleTypeConfig::RULE_TYPE_OBJECT);
  }

  public function attachTriggerObject($trigger_object) {
    $this->triggerObject = $trigger_object;
    return $this;
  }

  public function getTriggerObject() {
    return $this->assertAttached($this->triggerObject);
  }

  /**
   * Get a sortable key for rule execution order.
   *
   * Rules execute in a well-defined order: personal rules first, then object
   * rules, then global rules. Within each rule type, rules execute from lowest
   * ID to highest ID.
   *
   * This ordering allows more powerful rules (like global rules) to override
   * weaker rules (like personal rules) when multiple rules exist which try to
   * affect the same field. Executing from low IDs to high IDs makes
   * interactions easier to understand when adding new rules, because the newest
   * rules always happen last.
   *
   * @return string A sortable key for this rule.
   */
  public function getRuleExecutionOrderSortKey() {

    $rule_type = $this->getRuleType();

    switch ($rule_type) {
      case HeraldRuleTypeConfig::RULE_TYPE_PERSONAL:
        $type_order = 1;
        break;
      case HeraldRuleTypeConfig::RULE_TYPE_OBJECT:
        $type_order = 2;
        break;
      case HeraldRuleTypeConfig::RULE_TYPE_GLOBAL:
        $type_order = 3;
        break;
      default:
        throw new Exception(pht('Unknown rule type "%s"!', $rule_type));
    }

    return sprintf('~%d%010d', $type_order, $this->getID());
  }

  public function getMonogram() {
    return 'H'.$this->getID();
  }

  public function getURI() {
    return '/'.$this->getMonogram();
  }

  public function getEditorSortVector() {
    return id(new PhutilSortVector())
      ->addInt($this->getIsDisabled() ? 1 : 0)
      ->addString($this->getName());
  }

  public function getEditorDisplayName() {
    $name = pht('%s %s', $this->getMonogram(), $this->getName());

    if ($this->getIsDisabled()) {
      $name = pht('%s (Disabled)', $name);
    }

    return $name;
  }


/* -(  Repetition Policies  )------------------------------------------------ */


  public function getRepetitionPolicyStringConstant() {
    return $this->getRepetitionPolicy();
  }

  public function setRepetitionPolicyStringConstant($value) {
    $map = self::getRepetitionPolicyMap();

    if (!isset($map[$value])) {
      throw new Exception(
        pht(
          'Rule repetition string constant "%s" is unknown.',
          $value));
    }

    return $this->setRepetitionPolicy($value);
  }

  public function isRepeatEvery() {
    return ($this->getRepetitionPolicyStringConstant() === self::REPEAT_EVERY);
  }

  public function isRepeatFirst() {
    return ($this->getRepetitionPolicyStringConstant() === self::REPEAT_FIRST);
  }

  public function isRepeatOnChange() {
    return ($this->getRepetitionPolicyStringConstant() === self::REPEAT_CHANGE);
  }

  public static function getRepetitionPolicySelectOptionMap() {
    $map = self::getRepetitionPolicyMap();
    return ipull($map, 'select');
  }

  private static function getRepetitionPolicyMap() {
    return array(
      self::REPEAT_EVERY => array(
        'select' => pht('every time this rule matches:'),
      ),
      self::REPEAT_FIRST => array(
        'select' => pht('only the first time this rule matches:'),
      ),
      self::REPEAT_CHANGE => array(
        'select' => pht('if this rule did not match the last time:'),
      ),
    );
  }


/* -(  PhabricatorApplicationTransactionInterface  )------------------------- */


  public function getApplicationTransactionEditor() {
    return new HeraldRuleEditor();
  }

  public function getApplicationTransactionTemplate() {
    return new HeraldRuleTransaction();
  }


/* -(  PhabricatorPolicyInterface  )----------------------------------------- */


  public function getCapabilities() {
    return array(
      PhabricatorPolicyCapability::CAN_VIEW,
      PhabricatorPolicyCapability::CAN_EDIT,
    );
  }

  public function getPolicy($capability) {
    if ($capability == PhabricatorPolicyCapability::CAN_VIEW) {
      return PhabricatorPolicies::getMostOpenPolicy();
    }

    if ($this->isGlobalRule()) {
      $app = 'PhabricatorHeraldApplication';
      $herald = PhabricatorApplication::getByClass($app);
      $global = HeraldManageGlobalRulesCapability::CAPABILITY;
      return $herald->getPolicy($global);
    } else if ($this->isObjectRule()) {
      return $this->getTriggerObject()->getPolicy($capability);
    } else {
      return $this->getAuthorPHID();
    }
  }

  public function hasAutomaticCapability($capability, PhabricatorUser $viewer) {
    return false;
  }

  public function describeAutomaticCapability($capability) {
    if ($capability == PhabricatorPolicyCapability::CAN_VIEW) {
      return null;
    }

    if ($this->isGlobalRule()) {
      return pht(
        'Global Herald rules can be edited by users with the "Can Manage '.
        'Global Rules" Herald application permission.');
    } else if ($this->isObjectRule()) {
      return pht('Object rules inherit the edit policies of their objects.');
    } else {
      return pht('A personal rule can only be edited by its owner.');
    }
  }


/* -(  PhabricatorSubscribableInterface  )----------------------------------- */


  public function isAutomaticallySubscribed($phid) {
    return $this->isPersonalRule() && $phid == $this->getAuthorPHID();
  }


/* -(  PhabricatorDestructibleInterface  )----------------------------------- */


  public function destroyObjectPermanently(
    PhabricatorDestructionEngine $engine) {

    $this->openTransaction();
    $this->delete();
    $this->saveTransaction();
  }

}
