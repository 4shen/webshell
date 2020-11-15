<?php

final class LegalpadDocumentQuery
  extends PhabricatorCursorPagedPolicyAwareQuery {

  private $ids;
  private $phids;
  private $creatorPHIDs;
  private $contributorPHIDs;
  private $signerPHIDs;
  private $dateCreatedAfter;
  private $dateCreatedBefore;
  private $signatureRequired;

  private $needDocumentBodies;
  private $needContributors;
  private $needSignatures;
  private $needViewerSignatures;

  public function withIDs(array $ids) {
    $this->ids = $ids;
    return $this;
  }

  public function withPHIDs(array $phids) {
    $this->phids = $phids;
    return $this;
  }

  public function withCreatorPHIDs(array $phids) {
    $this->creatorPHIDs = $phids;
    return $this;
  }

  public function withContributorPHIDs(array $phids) {
    $this->contributorPHIDs = $phids;
    return $this;
  }

  public function withSignerPHIDs(array $phids) {
    $this->signerPHIDs = $phids;
    return $this;
  }

  public function withSignatureRequired($bool) {
    $this->signatureRequired = $bool;
    return $this;
  }

  public function needDocumentBodies($need_bodies) {
    $this->needDocumentBodies = $need_bodies;
    return $this;
  }

  public function needContributors($need_contributors) {
    $this->needContributors = $need_contributors;
    return $this;
  }

  public function needSignatures($need_signatures) {
    $this->needSignatures = $need_signatures;
    return $this;
  }

  public function withDateCreatedBefore($date_created_before) {
    $this->dateCreatedBefore = $date_created_before;
    return $this;
  }

  public function withDateCreatedAfter($date_created_after) {
    $this->dateCreatedAfter = $date_created_after;
    return $this;
  }

  public function needViewerSignatures($need) {
    $this->needViewerSignatures = $need;
    return $this;
  }

  public function newResultObject() {
    return new LegalpadDocument();
  }

  protected function loadPage() {
    return $this->loadStandardPage($this->newResultObject());
  }

  protected function willFilterPage(array $documents) {
    if ($this->needDocumentBodies) {
      $documents = $this->loadDocumentBodies($documents);
    }

    if ($this->needContributors) {
      $documents = $this->loadContributors($documents);
    }

    if ($this->needSignatures) {
      $documents = $this->loadSignatures($documents);
    }

    if ($this->needViewerSignatures) {
      if ($documents) {
        if ($this->getViewer()->getPHID()) {
          $signatures = id(new LegalpadDocumentSignatureQuery())
            ->setViewer($this->getViewer())
            ->withSignerPHIDs(array($this->getViewer()->getPHID()))
            ->withDocumentPHIDs(mpull($documents, 'getPHID'))
            ->execute();
          $signatures = mpull($signatures, null, 'getDocumentPHID');
        } else {
          $signatures = array();
        }

        foreach ($documents as $document) {
          $signature = idx($signatures, $document->getPHID());
          $document->attachUserSignature(
            $this->getViewer()->getPHID(),
            $signature);
        }
      }
    }

    return $documents;
  }

  protected function buildJoinClauseParts(AphrontDatabaseConnection $conn) {
    $joins = parent::buildJoinClauseParts($conn);

    if ($this->contributorPHIDs !== null) {
      $joins[] = qsprintf(
        $conn,
        'JOIN edge contributor ON contributor.src = d.phid
          AND contributor.type = %d',
        PhabricatorObjectHasContributorEdgeType::EDGECONST);
    }

    if ($this->signerPHIDs !== null) {
      $joins[] = qsprintf(
        $conn,
        'JOIN %T signer ON signer.documentPHID = d.phid
          AND signer.signerPHID IN (%Ls)',
        id(new LegalpadDocumentSignature())->getTableName(),
        $this->signerPHIDs);
    }

    return $joins;
  }

  protected function shouldGroupQueryResultRows() {
    if ($this->contributorPHIDs) {
      return true;
    }

    if ($this->signerPHIDs) {
      return true;
    }

    return parent::shouldGroupQueryResultRows();
  }

  protected function buildWhereClauseParts(AphrontDatabaseConnection $conn) {
    $where = parent::buildWhereClauseParts($conn);

    if ($this->ids !== null) {
      $where[] = qsprintf(
        $conn,
        'd.id IN (%Ld)',
        $this->ids);
    }

    if ($this->phids !== null) {
      $where[] = qsprintf(
        $conn,
        'd.phid IN (%Ls)',
        $this->phids);
    }

    if ($this->creatorPHIDs !== null) {
      $where[] = qsprintf(
        $conn,
        'd.creatorPHID IN (%Ls)',
        $this->creatorPHIDs);
    }

    if ($this->dateCreatedAfter !== null) {
      $where[] = qsprintf(
        $conn,
        'd.dateCreated >= %d',
        $this->dateCreatedAfter);
    }

    if ($this->dateCreatedBefore !== null) {
      $where[] = qsprintf(
        $conn,
        'd.dateCreated <= %d',
        $this->dateCreatedBefore);
    }

    if ($this->contributorPHIDs !== null) {
      $where[] = qsprintf(
        $conn,
        'contributor.dst IN (%Ls)',
        $this->contributorPHIDs);
    }

    if ($this->signatureRequired !== null) {
      $where[] = qsprintf(
        $conn,
        'd.requireSignature = %d',
        $this->signatureRequired);
    }

    return $where;
  }

  private function loadDocumentBodies(array $documents) {
    $body_phids = mpull($documents, 'getDocumentBodyPHID');
    $bodies = id(new LegalpadDocumentBody())->loadAllWhere(
      'phid IN (%Ls)',
      $body_phids);
    $bodies = mpull($bodies, null, 'getPHID');

    foreach ($documents as $document) {
      $body = idx($bodies, $document->getDocumentBodyPHID());
      $document->attachDocumentBody($body);
    }

    return $documents;
  }

  private function loadContributors(array $documents) {
    $document_map = mpull($documents, null, 'getPHID');
    $edge_type = PhabricatorObjectHasContributorEdgeType::EDGECONST;
    $contributor_data = id(new PhabricatorEdgeQuery())
      ->withSourcePHIDs(array_keys($document_map))
      ->withEdgeTypes(array($edge_type))
      ->execute();

    foreach ($document_map as $document_phid => $document) {
      $data = $contributor_data[$document_phid];
      $contributors = array_keys(idx($data, $edge_type, array()));
      $document->attachContributors($contributors);
    }

    return $documents;
  }

  private function loadSignatures(array $documents) {
    $document_map = mpull($documents, null, 'getPHID');

    $signatures = id(new LegalpadDocumentSignatureQuery())
      ->setViewer($this->getViewer())
      ->withDocumentPHIDs(array_keys($document_map))
      ->execute();
    $signatures = mgroup($signatures, 'getDocumentPHID');

    foreach ($documents as $document) {
      $sigs = idx($signatures, $document->getPHID(), array());
      $document->attachSignatures($sigs);
    }

    return $documents;
  }

  public function getQueryApplicationClass() {
    return 'PhabricatorLegalpadApplication';
  }

  protected function getPrimaryTableAlias() {
    return 'd';
  }

}
