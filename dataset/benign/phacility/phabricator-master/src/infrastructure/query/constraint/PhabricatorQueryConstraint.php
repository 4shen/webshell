<?php

final class PhabricatorQueryConstraint extends Phobject {

  const OPERATOR_AND = 'and';
  const OPERATOR_OR = 'or';
  const OPERATOR_NOT = 'not';
  const OPERATOR_NULL = 'null';
  const OPERATOR_ANCESTOR = 'ancestor';
  const OPERATOR_EMPTY = 'empty';
  const OPERATOR_ONLY = 'only';
  const OPERATOR_ANY = 'any';

  private $operator;
  private $value;

  public function __construct($operator, $value) {
    $this->operator = $operator;
    $this->value = $value;
  }

  public function setOperator($operator) {
    $this->operator = $operator;
    return $this;
  }

  public function getOperator() {
    return $this->operator;
  }

  public function setValue($value) {
    $this->value = $value;
    return $this;
  }

  public function getValue() {
    return $this->value;
  }

}
