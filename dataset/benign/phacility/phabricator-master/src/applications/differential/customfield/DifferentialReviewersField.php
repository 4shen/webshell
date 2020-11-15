<?php

final class DifferentialReviewersField
  extends DifferentialCoreCustomField {

  public function getFieldKey() {
    return 'differential:reviewers';
  }

  public function getFieldName() {
    return pht('Reviewers');
  }

  public function getFieldDescription() {
    return pht('Manage reviewers.');
  }

  protected function readValueFromRevision(
    DifferentialRevision $revision) {
    return $revision->getReviewers();
  }

  public function shouldAppearInPropertyView() {
    return true;
  }

  public function renderPropertyViewLabel() {
    return $this->getFieldName();
  }

  public function getRequiredHandlePHIDsForPropertyView() {
    return mpull($this->getUserReviewers(), 'getReviewerPHID');
  }

  public function renderPropertyViewValue(array $handles) {
    $reviewers = $this->getUserReviewers();
    if (!$reviewers) {
      return phutil_tag('em', array(), pht('None'));
    }

    $view = id(new DifferentialReviewersView())
      ->setUser($this->getViewer())
      ->setReviewers($reviewers)
      ->setHandles($handles);

    $diff = $this->getActiveDiff();
    if ($diff) {
      $view->setActiveDiff($diff);
    }

    return $view;
  }

  private function getUserReviewers() {
    $reviewers = array();
    foreach ($this->getObject()->getReviewers() as $reviewer) {
      if ($reviewer->isUser()) {
        $reviewers[] = $reviewer;
      }
    }
    return $reviewers;
  }

  public function getRequiredHandlePHIDsForRevisionHeaderWarnings() {
    return mpull($this->getValue(), 'getReviewerPHID');
  }

  public function getWarningsForRevisionHeader(array $handles) {
    $revision = $this->getObject();

    if (!$revision->isNeedsReview()) {
      return array();
    }

    $all_resigned = true;
    $all_disabled = true;
    $any_reviewers = false;

    foreach ($this->getValue() as $reviewer) {
      $reviewer_phid = $reviewer->getReviewerPHID();

      $any_reviewers = true;

      if (!$handles[$reviewer_phid]->isDisabled()) {
        $all_disabled = false;
      }

      if (!$reviewer->isResigned()) {
        $all_resigned = false;
      }
    }

    $warnings = array();
    if (!$any_reviewers) {
      $warnings[] = pht(
        'This revision needs review, but there are no reviewers specified.');
    } else if ($all_disabled) {
      $warnings[] = pht(
        'This revision needs review, but all specified reviewers are '.
        'disabled or inactive.');
    } else if ($all_resigned) {
      $warnings[] = pht(
        'This revision needs review, but all reviewers have resigned.');
    }

    return $warnings;
  }

}
