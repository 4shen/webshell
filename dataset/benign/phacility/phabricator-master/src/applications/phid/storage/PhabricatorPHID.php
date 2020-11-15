<?php

final class PhabricatorPHID extends Phobject {

  public static function generateNewPHID($type, $subtype = null) {
    if (!$type) {
      throw new Exception(pht('Can not generate PHID with no type.'));
    }

    if ($subtype === null) {
      $uniq_len = 20;
      $type_str = "{$type}";
    } else {
      $uniq_len = 15;
      $type_str = "{$type}-{$subtype}";
    }

    $uniq = Filesystem::readRandomCharacters($uniq_len);
    return "PHID-{$type_str}-{$uniq}";
  }

}
