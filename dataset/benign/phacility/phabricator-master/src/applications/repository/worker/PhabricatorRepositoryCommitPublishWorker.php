<?php

final class PhabricatorRepositoryCommitPublishWorker
  extends PhabricatorRepositoryCommitParserWorker {

  protected function getImportStepFlag() {
    return PhabricatorRepositoryCommit::IMPORTED_PUBLISH;
  }

  public function getRequiredLeaseTime() {
    // Herald rules may take a long time to process.
    return phutil_units('4 hours in seconds');
  }

  protected function parseCommit(
    PhabricatorRepository $repository,
    PhabricatorRepositoryCommit $commit) {

    if (!$this->shouldSkipImportStep()) {
      $this->publishCommit($repository, $commit);
      $commit->writeImportStatusFlag($this->getImportStepFlag());
    }

    // This is the last task in the sequence, so we don't need to queue any
    // followup workers.
  }

  private function publishCommit(
    PhabricatorRepository $repository,
    PhabricatorRepositoryCommit $commit) {
    $viewer = PhabricatorUser::getOmnipotentUser();

    $publisher = $repository->newPublisher();
    if (!$publisher->shouldPublishCommit($commit)) {
      return;
    }

    $commit_phid = $commit->getPHID();

    // Reload the commit to get the commit data, identities, and any
    // outstanding audit requests.
    $commit = id(new DiffusionCommitQuery())
      ->setViewer($viewer)
      ->withPHIDs(array($commit_phid))
      ->needCommitData(true)
      ->needIdentities(true)
      ->needAuditRequests(true)
      ->executeOne();
    if (!$commit) {
      throw new PhabricatorWorkerPermanentFailureException(
        pht(
          'Failed to reload commit "%s".',
          $commit_phid));
    }

    $xactions = array(
      $this->newAuditTransactions($commit),
      $this->newPublishTransactions($commit),
    );
    $xactions = array_mergev($xactions);

    $acting_phid = $this->getPublishAsPHID($commit);
    $content_source = $this->newContentSource();

    $revision = DiffusionCommitRevisionQuery::loadRevisionForCommit(
      $viewer,
      $commit);

    // Prevent the commit from generating a mention of the associated
    // revision, if one exists, so we don't double up because of the URI
    // in the commit message.
    $unmentionable_phids = array();
    if ($revision) {
      $unmentionable_phids[] = $revision->getPHID();
    }

    $editor = $commit->getApplicationTransactionEditor()
      ->setActor($viewer)
      ->setActingAsPHID($acting_phid)
      ->setContinueOnNoEffect(true)
      ->setContinueOnMissingFields(true)
      ->setContentSource($content_source)
      ->addUnmentionablePHIDs($unmentionable_phids);

    try {
      $raw_patch = $this->loadRawPatchText($repository, $commit);
    } catch (Exception $ex) {
      $raw_patch = pht('Unable to generate patch: %s', $ex->getMessage());
    }
    $editor->setRawPatch($raw_patch);

    $editor->applyTransactions($commit, $xactions);
  }

  private function getPublishAsPHID(PhabricatorRepositoryCommit $commit) {
    if ($commit->hasCommitterIdentity()) {
      return $commit->getCommitterIdentity()->getIdentityDisplayPHID();
    }

    if ($commit->hasAuthorIdentity()) {
      return $commit->getAuthorIdentity()->getIdentityDisplayPHID();
    }

    return id(new PhabricatorDiffusionApplication())->getPHID();
  }

  private function newPublishTransactions(PhabricatorRepositoryCommit $commit) {
    $data = $commit->getCommitData();

    $xactions = array();

    $xactions[] = $commit->getApplicationTransactionTemplate()
      ->setTransactionType(PhabricatorAuditTransaction::TYPE_COMMIT)
      ->setDateCreated($commit->getEpoch())
      ->setNewValue(
        array(
          'description'   => $data->getCommitMessage(),
          'summary'       => $data->getSummary(),
          'authorName'    => $data->getAuthorName(),
          'authorPHID'    => $commit->getAuthorPHID(),
          'committerName' => $data->getCommitDetail('committer'),
          'committerPHID' => $data->getCommitDetail('committerPHID'),
        ));

    return $xactions;
  }

  private function newAuditTransactions(PhabricatorRepositoryCommit $commit) {
    $viewer = PhabricatorUser::getOmnipotentUser();

    $repository = $commit->getRepository();

    $affected_paths = PhabricatorOwnerPathQuery::loadAffectedPaths(
      $repository,
      $commit,
      PhabricatorUser::getOmnipotentUser());

    $affected_packages = PhabricatorOwnersPackage::loadAffectedPackages(
      $repository,
      $affected_paths);

    $commit->writeOwnersEdges(mpull($affected_packages, 'getPHID'));

    if (!$affected_packages) {
      return array();
    }

    $data = $commit->getCommitData();

    $author_phid = $commit->getEffectiveAuthorPHID();

    $revision = DiffusionCommitRevisionQuery::loadRevisionForCommit(
      $viewer,
      $commit);

    $requests = $commit->getAudits();
    $requests = mpull($requests, null, 'getAuditorPHID');

    $auditor_phids = array();
    foreach ($affected_packages as $package) {
      $request = idx($requests, $package->getPHID());
      if ($request) {
        // Don't update request if it exists already.
        continue;
      }

      $should_audit = $this->shouldTriggerAudit(
        $commit,
        $package,
        $author_phid,
        $revision);
      if (!$should_audit) {
        continue;
      }

      $auditor_phids[] = $package->getPHID();
    }

    // If none of the packages are triggering audits, we're all done.
    if (!$auditor_phids) {
      return array();
    }

    $audit_type = DiffusionCommitAuditorsTransaction::TRANSACTIONTYPE;

    $xactions = array();
    $xactions[] = $commit->getApplicationTransactionTemplate()
      ->setTransactionType($audit_type)
      ->setNewValue(
        array(
          '+' => array_fuse($auditor_phids),
        ));

    return $xactions;
  }

  private function shouldTriggerAudit(
    PhabricatorRepositoryCommit $commit,
    PhabricatorOwnersPackage $package,
    $author_phid,
    $revision) {

    $audit_uninvolved = false;
    $audit_unreviewed = false;

    $rule = $package->newAuditingRule();
    switch ($rule->getKey()) {
      case PhabricatorOwnersAuditRule::AUDITING_NONE:
        return false;
      case PhabricatorOwnersAuditRule::AUDITING_ALL:
        return true;
      case PhabricatorOwnersAuditRule::AUDITING_NO_OWNER:
        $audit_uninvolved = true;
        break;
      case PhabricatorOwnersAuditRule::AUDITING_UNREVIEWED:
        $audit_unreviewed = true;
        break;
      case PhabricatorOwnersAuditRule::AUDITING_NO_OWNER_AND_UNREVIEWED:
        $audit_uninvolved = true;
        $audit_unreviewed = true;
        break;
    }

    // If auditing is configured to trigger on unreviewed changes, check if
    // the revision was "Accepted" when it landed. If not, trigger an audit.

    // We may be running before the revision actually closes, so we'll count
    // either an "Accepted" or a "Closed, Previously Accepted" revision as
    // good enough.

    if ($audit_unreviewed) {
      $commit_unreviewed = true;
      if ($revision) {
        if ($revision->isAccepted()) {
          $commit_unreviewed = false;
        } else {
          $was_accepted = DifferentialRevision::PROPERTY_CLOSED_FROM_ACCEPTED;
          if ($revision->isPublished()) {
            if ($revision->getProperty($was_accepted)) {
              $commit_unreviewed = false;
            }
          }
        }
      }

      if ($commit_unreviewed) {
        return true;
      }
    }

    // If auditing is configured to trigger on changes with no involved owner,
    // check for an owner. If we don't find one, trigger an audit.
    if ($audit_uninvolved) {
      $owner_involved = $this->isOwnerInvolved(
        $commit,
        $package,
        $author_phid,
        $revision);
      if (!$owner_involved) {
        return true;
      }
    }

    // We can't find any reason to trigger an audit for this commit.
    return false;
  }

  private function isOwnerInvolved(
    PhabricatorRepositoryCommit $commit,
    PhabricatorOwnersPackage $package,
    $author_phid,
    $revision) {

    $owner_phids = PhabricatorOwnersOwner::loadAffiliatedUserPHIDs(
      array(
        $package->getID(),
      ));
    $owner_phids = array_fuse($owner_phids);

    // For the purposes of deciding whether the owners were involved in the
    // revision or not, consider a review by the package itself to count as
    // involvement. This can happen when human reviewers force-accept on
    // behalf of packages they don't own but have authority over.
    $owner_phids[$package->getPHID()] = $package->getPHID();

    // If the commit author is identifiable and a package owner, they're
    // involved.
    if ($author_phid) {
      if (isset($owner_phids[$author_phid])) {
        return true;
      }
    }

    // Otherwise, we need to find an owner as a reviewer.

    // If we don't have a revision, this is hopeless: no owners are involved.
    if (!$revision) {
      return true;
    }

    $accepted_statuses = array(
      DifferentialReviewerStatus::STATUS_ACCEPTED,
      DifferentialReviewerStatus::STATUS_ACCEPTED_OLDER,
    );
    $accepted_statuses = array_fuse($accepted_statuses);

    $found_accept = false;
    foreach ($revision->getReviewers() as $reviewer) {
      $reviewer_phid = $reviewer->getReviewerPHID();

      // If this reviewer isn't a package owner or the package itself,
      // just ignore them.
      if (empty($owner_phids[$reviewer_phid])) {
        continue;
      }

      // If this reviewer accepted the revision and owns the package (or is
      // the package), we've found an involved owner.
      if (isset($accepted_statuses[$reviewer->getReviewerStatus()])) {
        $found_accept = true;
        break;
      }
    }

    if ($found_accept) {
      return true;
    }

    return false;
  }

  private function loadRawPatchText(
    PhabricatorRepository $repository,
    PhabricatorRepositoryCommit $commit) {
    $viewer = PhabricatorUser::getOmnipotentUser();

    $identifier = $commit->getCommitIdentifier();

    $drequest = DiffusionRequest::newFromDictionary(
      array(
        'user' => $viewer,
        'repository' => $repository,
      ));

    $time_key = 'metamta.diffusion.time-limit';
    $byte_key = 'metamta.diffusion.byte-limit';
    $time_limit = PhabricatorEnv::getEnvConfig($time_key);
    $byte_limit = PhabricatorEnv::getEnvConfig($byte_key);

    $diff_info = DiffusionQuery::callConduitWithDiffusionRequest(
      $viewer,
      $drequest,
      'diffusion.rawdiffquery',
      array(
        'commit' => $identifier,
        'linesOfContext' => 3,
        'timeout' => $time_limit,
        'byteLimit' => $byte_limit,
      ));

    if ($diff_info['tooSlow']) {
      throw new Exception(
        pht(
          'Patch generation took longer than configured limit ("%s") of '.
          '%s second(s).',
          $time_key,
          new PhutilNumber($time_limit)));
    }

    if ($diff_info['tooHuge']) {
      $pretty_limit = phutil_format_bytes($byte_limit);
      throw new Exception(
        pht(
          'Patch size exceeds configured byte size limit ("%s") of %s.',
          $byte_key,
          $pretty_limit));
    }

    $file_phid = $diff_info['filePHID'];
    $file = id(new PhabricatorFileQuery())
      ->setViewer($viewer)
      ->withPHIDs(array($file_phid))
      ->executeOne();
    if (!$file) {
      throw new Exception(
        pht(
          'Failed to load file ("%s") returned by "%s".',
          $file_phid,
          'diffusion.rawdiffquery'));
    }

    return $file->loadFileData();
  }
}
