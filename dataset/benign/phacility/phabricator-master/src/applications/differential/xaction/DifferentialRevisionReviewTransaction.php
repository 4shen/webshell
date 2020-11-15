<?php

abstract class DifferentialRevisionReviewTransaction
  extends DifferentialRevisionActionTransaction {

  protected function getRevisionActionGroupKey() {
    return DifferentialRevisionEditEngine::ACTIONGROUP_REVIEW;
  }

  public function generateNewValue($object, $value) {
    if (!is_array($value)) {
      return true;
    }

    // If the list of options is the same as the default list, just treat this
    // as a "take the default action" transaction.
    $viewer = $this->getActor();
    list($options, $default) = $this->getActionOptions($viewer, $object);

    // Remove reviewers which aren't actionable. In the case of "Accept", we
    // may allow the transaction to proceed with some reviewers who have
    // already accepted, to avoid race conditions where two reviewers fill
    // out the form at the same time and accept on behalf of the same package.
    // It's okay for these reviewers to survive validation, but they should
    // not survive beyond this point.
    $value = array_fuse($value);
    $value = array_intersect($value, array_keys($options));
    $value = array_values($value);

    sort($default);
    sort($value);

    if ($default === $value) {
      return true;
    }

    return $value;
  }

  protected function isViewerAnyReviewer(
    DifferentialRevision $revision,
    PhabricatorUser $viewer) {
    return ($this->getViewerReviewerStatus($revision, $viewer) !== null);
  }

  protected function isViewerAnyAuthority(
    DifferentialRevision $revision,
    PhabricatorUser $viewer) {

    $reviewers = $revision->getReviewers();
    foreach ($revision->getReviewers() as $reviewer) {
      if ($reviewer->hasAuthority($viewer)) {
        return true;
      }
    }

    return false;
  }

  protected function isViewerFullyAccepted(
    DifferentialRevision $revision,
    PhabricatorUser $viewer) {
    return $this->isViewerReviewerStatusFully(
      $revision,
      $viewer,
      DifferentialReviewerStatus::STATUS_ACCEPTED);
  }

  protected function isViewerFullyRejected(
    DifferentialRevision $revision,
    PhabricatorUser $viewer) {
    return $this->isViewerReviewerStatusFully(
      $revision,
      $viewer,
      DifferentialReviewerStatus::STATUS_REJECTED);
  }

  protected function getViewerReviewerStatus(
    DifferentialRevision $revision,
    PhabricatorUser $viewer) {

    if (!$viewer->getPHID()) {
      return null;
    }

    foreach ($revision->getReviewers() as $reviewer) {
      if ($reviewer->getReviewerPHID() != $viewer->getPHID()) {
        continue;
      }

      return $reviewer->getReviewerStatus();
    }

    return null;
  }

  private function isViewerReviewerStatusFully(
    DifferentialRevision $revision,
    PhabricatorUser $viewer,
    $require_status) {

    // If the user themselves is not a reviewer, the reviews they have
    // authority over can not all be in any set of states since their own
    // personal review has no state.
    $status = $this->getViewerReviewerStatus($revision, $viewer);
    if ($status === null) {
      return false;
    }

    $active_phid = $this->getActiveDiffPHID($revision);

    $status_accepted = DifferentialReviewerStatus::STATUS_ACCEPTED;
    $status_rejected = DifferentialReviewerStatus::STATUS_REJECTED;

    $is_accepted = ($require_status == $status_accepted);
    $is_rejected = ($require_status == $status_rejected);

    // Otherwise, check that all reviews they have authority over are in
    // the desired set of states.
    foreach ($revision->getReviewers() as $reviewer) {
      if (!$reviewer->hasAuthority($viewer)) {
        $can_force = false;

        if ($is_accepted) {
          if ($revision->canReviewerForceAccept($viewer, $reviewer)) {
            $can_force = true;
          }
        }

        if (!$can_force) {
          continue;
        }
      }

      $status = $reviewer->getReviewerStatus();
      if ($status != $require_status) {
        return false;
      }

      // Here, we're primarily testing if we can remove a void on the review.
      if ($is_accepted) {
        if (!$reviewer->isAccepted($active_phid)) {
          return false;
        }
      }

      if ($is_rejected) {
        if (!$reviewer->isRejected($active_phid)) {
          return false;
        }
      }

      // This is a broader check to see if we can update the diff where the
      // last action occurred.
      if ($reviewer->getLastActionDiffPHID() != $active_phid) {
        return false;
      }
    }

    return true;
  }

  protected function applyReviewerEffect(
    DifferentialRevision $revision,
    PhabricatorUser $viewer,
    $value,
    $status,
    array $reviewer_options = array()) {

    PhutilTypeSpec::checkMap(
      $reviewer_options,
      array(
        'sticky' => 'optional bool',
      ));

    $map = array();

    // When you accept or reject, you may accept or reject on behalf of all
    // reviewers you have authority for. When you resign, you only affect
    // yourself.
    $with_authority = ($status != DifferentialReviewerStatus::STATUS_RESIGNED);
    $with_force = ($status == DifferentialReviewerStatus::STATUS_ACCEPTED);

    if ($with_authority) {
      foreach ($revision->getReviewers() as $reviewer) {
        if (!$reviewer->hasAuthority($viewer)) {
          if (!$with_force) {
            continue;
          }

          if (!$revision->canReviewerForceAccept($viewer, $reviewer)) {
            continue;
          }
        }

        $map[$reviewer->getReviewerPHID()] = $status;
      }
    }

    // In all cases, you affect yourself.
    $map[$viewer->getPHID()] = $status;

    // If we're applying an "accept the defaults" transaction, and this
    // transaction type uses checkboxes, replace the value with the list of
    // defaults.
    if (!is_array($value)) {
      list($options, $default) = $this->getActionOptions($viewer, $revision);
      if ($options) {
        $value = $default;
      }
    }

    // If we have a specific list of reviewers to act on, usually because the
    // user has submitted a specific list of reviewers to act as by
    // unchecking some checkboxes under "Accept", only affect those reviewers.
    if (is_array($value)) {
      $map = array_select_keys($map, $value);
    }

    // Now, do the new write.

    if ($map) {
      $diff = $this->getEditor()->getActiveDiff($revision);
      if ($diff) {
        $diff_phid = $diff->getPHID();
      } else {
        $diff_phid = null;
      }

      $table = new DifferentialReviewer();
      $src_phid = $revision->getPHID();

      $reviewers = $table->loadAllWhere(
        'revisionPHID = %s AND reviewerPHID IN (%Ls)',
        $src_phid,
        array_keys($map));
      $reviewers = mpull($reviewers, null, 'getReviewerPHID');

      foreach (array_keys($map) as $dst_phid) {
        $reviewer = idx($reviewers, $dst_phid);
        if (!$reviewer) {
          $reviewer = id(new DifferentialReviewer())
            ->setRevisionPHID($src_phid)
            ->setReviewerPHID($dst_phid);
        }

        $old_status = $reviewer->getReviewerStatus();
        $reviewer->setReviewerStatus($status);

        if ($diff_phid) {
          $reviewer->setLastActionDiffPHID($diff_phid);
        }

        if ($old_status !== $status) {
          $reviewer->setLastActorPHID($this->getActingAsPHID());
        }

        // Clear any outstanding void on this reviewer. A void may be placed
        // by the author using "Request Review" when a reviewer has already
        // accepted.
        $reviewer->setVoidedPHID(null);

        $reviewer->setOption('sticky', idx($reviewer_options, 'sticky'));

        try {
          $reviewer->save();
        } catch (AphrontDuplicateKeyQueryException $ex) {
          // At least for now, just ignore it if we lost a race.
        }
      }
    }

  }

}
