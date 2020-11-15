<?php

final class DifferentialProjectReviewersField
  extends DifferentialCustomField {

  public function getFieldKey() {
    return 'differential:project-reviewers';
  }

  public function getFieldName() {
    return pht('Group Reviewers');
  }

  public function getFieldDescription() {
    return pht('Display project reviewers.');
  }

  public function shouldAppearInPropertyView() {
    return true;
  }

  public function canDisableField() {
    return false;
  }

  public function renderPropertyViewLabel() {
    return $this->getFieldName();
  }

  public function getRequiredHandlePHIDsForPropertyView() {
    return mpull($this->getProjectReviewers(), 'getReviewerPHID');
  }

  public function renderPropertyViewValue(array $handles) {
    $reviewers = $this->getProjectReviewers();
    if (!$reviewers) {
      return null;
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

  private function getProjectReviewers() {
    $reviewers = array();
    foreach ($this->getObject()->getReviewers() as $reviewer) {
      if (!$reviewer->isUser()) {
        $reviewers[] = $reviewer;
      }
    }
    return $reviewers;
  }

}
