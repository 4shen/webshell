<?php

final class PhabricatorMacroNameTransaction
  extends PhabricatorMacroTransactionType {

  const TRANSACTIONTYPE = 'macro:name';

  public function generateOldValue($object) {
    return $object->getName();
  }

  public function applyInternalEffects($object, $value) {
    $object->setName($value);
  }

  public function getTitle() {
    return pht(
      '%s renamed this macro from %s to %s.',
      $this->renderAuthor(),
      $this->renderOldValue(),
      $this->renderNewValue());
  }

  public function getTitleForFeed() {
    return pht(
      '%s renamed %s from %s to %s.',
      $this->renderAuthor(),
      $this->renderObject(),
      $this->renderOldValue(),
      $this->renderNewValue());
  }

  public function validateTransactions($object, array $xactions) {
    $errors = array();
    $viewer = $this->getActor();

    if ($this->isEmptyTextTransaction($object->getName(), $xactions)) {
      $errors[] = $this->newRequiredError(
        pht('Macros must have a name.'));
      return $errors;
    }

    $max_length = $object->getColumnMaximumByteLength('name');
    foreach ($xactions as $xaction) {
      $old_value = $this->generateOldValue($object);
      $new_value = $xaction->getNewValue();

      $new_length = strlen($new_value);
      if ($new_length > $max_length) {
        $errors[] = $this->newInvalidError(
          pht('The name can be no longer than %s characters.',
          new PhutilNumber($max_length)));
      }

      if (!self::isValidMacroName($new_value)) {
        // This says "emoji", but the actual rule we implement is "all other
        // unicode characters are also fine".
        $errors[] = $this->newInvalidError(
          pht(
            'Macro name "%s" be: at least three characters long; and contain '.
            'only lowercase letters, digits, hyphens, colons, underscores, '.
            'and emoji; and not be composed entirely of latin symbols.',
            $new_value),
          $xaction);
      }

      // Check name is unique when updating / creating
      if ($old_value != $new_value) {
        $macro = id(new PhabricatorMacroQuery())
          ->setViewer($viewer)
          ->withNames(array($new_value))
          ->executeOne();

        if ($macro) {
        $errors[] = $this->newInvalidError(
          pht('Macro "%s" already exists.', $new_value));
        }
      }

    }

    return $errors;
  }

  public static function isValidMacroName($name) {
    if (preg_match('/^[:_-]+\z/', $name)) {
      return false;
    }

    // Accept trivial macro names.
    if (preg_match('/^[a-z0-9:_-]{3,}\z/', $name)) {
      return true;
    }

    // Reject names with fewer than 3 glyphs.
    $length = phutil_utf8v_combined($name);
    if (count($length) < 3) {
      return false;
    }

    // Check character-by-character for any symbols that we don't want.
    $characters = phutil_utf8v($name);
    foreach ($characters as $character) {
      if (ord($character[0]) > 0x7F) {
        continue;
      }

      if (preg_match('/^[^a-z0-9:_-]/', $character)) {
        return false;
      }
    }

    return true;
  }

}
