<?php

/**
 * @task objectpolicy Implementing Object Policies
 */
abstract class PhabricatorPolicyRule extends Phobject {

  const CONTROL_TYPE_TEXT       = 'text';
  const CONTROL_TYPE_SELECT     = 'select';
  const CONTROL_TYPE_TOKENIZER  = 'tokenizer';
  const CONTROL_TYPE_NONE       = 'none';

  abstract public function getRuleDescription();

  public function willApplyRules(
    PhabricatorUser $viewer,
    array $values,
    array $objects) {
    return;
  }

  abstract public function applyRule(
    PhabricatorUser $viewer,
    $value,
    PhabricatorPolicyInterface $object);

  public function getValueControlType() {
    return self::CONTROL_TYPE_TEXT;
  }

  public function getValueControlTemplate() {
    return null;
  }

  /**
   * Return `true` if this rule can be applied to the given object.
   *
   * Some policy rules may only operation on certain kinds of objects. For
   * example, a "task author" rule can only operate on tasks.
   */
  public function canApplyToObject(PhabricatorPolicyInterface $object) {
    return true;
  }

  protected function getDatasourceTemplate(
    PhabricatorTypeaheadDatasource $datasource) {

    return array(
      'markup' => new AphrontTokenizerTemplateView(),
      'uri' => $datasource->getDatasourceURI(),
      'placeholder' => $datasource->getPlaceholderText(),
      'browseURI' => $datasource->getBrowseURI(),
    );
  }

  public function getRuleOrder() {
    return 500;
  }

  public function getValueForStorage($value) {
    return $value;
  }

  public function getValueForDisplay(PhabricatorUser $viewer, $value) {
    return $value;
  }

  public function getRequiredHandlePHIDsForSummary($value) {
    $phids = array();

    switch ($this->getValueControlType()) {
      case self::CONTROL_TYPE_TOKENIZER:
        $phids = $value;
        break;
      case self::CONTROL_TYPE_TEXT:
      case self::CONTROL_TYPE_SELECT:
      case self::CONTROL_TYPE_NONE:
      default:
        if (phid_get_type($value) !=
            PhabricatorPHIDConstants::PHID_TYPE_UNKNOWN) {
          $phids = array($value);
        } else {
          $phids = array();
        }
        break;
    }

    return $phids;
  }

  /**
   * Return `true` if the given value creates a rule with a meaningful effect.
   * An example of a rule with no meaningful effect is a "users" rule with no
   * users specified.
   *
   * @return bool True if the value creates a meaningful rule.
   */
  public function ruleHasEffect($value) {
    return true;
  }


/* -(  Transaction Hints  )-------------------------------------------------- */


  /**
   * Tell policy rules about upcoming transaction effects.
   *
   * Before transaction effects are applied, we try to stop users from making
   * edits which will lock them out of objects. We can't do this perfectly,
   * since they can set a policy to "the moon is full" moments before it wanes,
   * but we try to prevent as many mistakes as possible.
   *
   * Some policy rules depend on complex checks against object state which
   * we can't set up ahead of time. For example, subscriptions require database
   * writes.
   *
   * In cases like this, instead of doing writes, you can pass a hint about an
   * object to a policy rule. The rule can then look for hints and use them in
   * rendering a verdict about whether the user will be able to see the object
   * or not after applying the policy change.
   *
   * @param PhabricatorPolicyInterface Object to pass a hint about.
   * @param PhabricatorPolicyRule Rule to pass hint to.
   * @param wild Hint.
   * @return void
   */
  public static function passTransactionHintToRule(
    PhabricatorPolicyInterface $object,
    PhabricatorPolicyRule $rule,
    $hint) {

    $cache = PhabricatorCaches::getRequestCache();
    $cache->setKey(self::getObjectPolicyCacheKey($object, $rule), $hint);
  }

  final protected function getTransactionHint(
    PhabricatorPolicyInterface $object) {

    $cache = PhabricatorCaches::getRequestCache();
    return $cache->getKey(self::getObjectPolicyCacheKey($object, $this));
  }

  private static function getObjectPolicyCacheKey(
    PhabricatorPolicyInterface $object,
    PhabricatorPolicyRule $rule) {

    // NOTE: This is quite a bit of a hack, but we don't currently have a
    // better way to carry hints from the TransactionEditor into PolicyRules
    // about pending policy changes.

    // Put some magic secret unique value on each object so we can pass
    // information about it by proxy. This allows us to test if pending
    // edits to an object will cause policy violations or not, before allowing
    // those edits to go through.

    // Some better approaches might be:
    //   - Use traits to give `PhabricatorPolicyInterface` objects real
    //     storage (requires PHP 5.4.0).
    //   - Wrap policy objects in a container with extra storage which the
    //     policy filter knows how to unbox (lots of work).

    // When this eventually gets cleaned up, the corresponding hack in
    // LiskDAO->__set() should also be cleaned up.
    static $id = 0;
    if (!isset($object->_hashKey)) {
      @$object->_hashKey = 'object.id('.(++$id).')';
    }

    return $object->_hashKey;
  }


/* -(  Implementing Object Policies  )--------------------------------------- */


  /**
   * Return a unique string like "maniphest.author" to expose this rule as an
   * object policy.
   *
   * Object policy rules, like "Task Author", are more advanced than basic
   * policy rules (like "All Users") but not as powerful as custom rules.
   *
   * @return string Unique identifier for this rule.
   * @task objectpolicy
   */
  public function getObjectPolicyKey() {
    return null;
  }

  final public function getObjectPolicyFullKey() {
    $key = $this->getObjectPolicyKey();

    if (!$key) {
      throw new Exception(
        pht(
          'This policy rule (of class "%s") does not have an associated '.
          'object policy key.',
          get_class($this)));
    }

    return PhabricatorPolicyQuery::OBJECT_POLICY_PREFIX.$key;
  }

  public function getObjectPolicyName() {
    throw new PhutilMethodNotImplementedException();
  }

  public function getObjectPolicyShortName() {
    return $this->getObjectPolicyName();
  }

  public function getObjectPolicyIcon() {
    return 'fa-cube';
  }

  public function getPolicyExplanation() {
    throw new PhutilMethodNotImplementedException();
  }

}
