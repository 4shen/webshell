<?php

final class DifferentialBranchField
  extends DifferentialCustomField {

  public function getFieldKey() {
    return 'differential:branch';
  }

  public function getFieldName() {
    return pht('Branch');
  }

  public function getFieldDescription() {
    return pht('Shows the branch a diff came from.');
  }

  public function shouldAppearInPropertyView() {
    return true;
  }

  public function renderPropertyViewValue(array $handles) {
    return null;
  }

  public function shouldAppearInDiffPropertyView() {
    return true;
  }

  public function renderDiffPropertyViewLabel(DifferentialDiff $diff) {
    return $this->getFieldName();
  }

  public function renderDiffPropertyViewValue(DifferentialDiff $diff) {
    return $this->getBranchDescription($diff);
  }

  private function getBranchDescription(DifferentialDiff $diff) {
    $branch = $diff->getBranch();
    $bookmark = $diff->getBookmark();

    if (strlen($branch) && strlen($bookmark)) {
      return pht('%s (bookmark) on %s (branch)', $bookmark, $branch);
    } else if (strlen($bookmark)) {
      return pht('%s (bookmark)', $bookmark);
    } else if (strlen($branch)) {
      $onto = $diff->loadTargetBranch();
      if (strlen($onto) && ($onto !== $branch)) {
        return pht(
          '%s (branched from %s)',
          $branch,
          $onto);
      } else {
        return $branch;
      }
    } else {
      return null;
    }
  }

  public function getProTips() {
    return array(
      pht(
        'In Git and Mercurial, use a branch like "%s" to automatically '.
        'associate changes with the corresponding task.',
        'T123'),
    );
  }

  public function shouldAppearInTransactionMail() {
    return true;
  }

  public function updateTransactionMailBody(
    PhabricatorMetaMTAMailBody $body,
    PhabricatorApplicationTransactionEditor $editor,
    array $xactions) {

    $revision = $this->getObject();

    // Show the "BRANCH" section only if there's a new diff or the revision
    // is "Accepted".
    $is_update = (bool)$editor->getDiffUpdateTransaction($xactions);
    $is_accepted = $revision->isAccepted();
    if (!$is_update && !$is_accepted) {
      return;
    }

    $branch = $this->getBranchDescription($revision->getActiveDiff());
    if ($branch === null) {
      return;
    }

    $body->addTextSection(pht('BRANCH'), $branch);
  }

}
