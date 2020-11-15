<?php

final class DrydockLogQuery extends DrydockQuery {

  private $ids;
  private $blueprintPHIDs;
  private $resourcePHIDs;
  private $leasePHIDs;
  private $operationPHIDs;

  public function withIDs(array $ids) {
    $this->ids = $ids;
    return $this;
  }

  public function withBlueprintPHIDs(array $phids) {
    $this->blueprintPHIDs = $phids;
    return $this;
  }

  public function withResourcePHIDs(array $phids) {
    $this->resourcePHIDs = $phids;
    return $this;
  }

  public function withLeasePHIDs(array $phids) {
    $this->leasePHIDs = $phids;
    return $this;
  }

  public function withOperationPHIDs(array $phids) {
    $this->operationPHIDs = $phids;
    return $this;
  }

  public function newResultObject() {
    return new DrydockLog();
  }

  protected function loadPage() {
    return $this->loadStandardPage($this->newResultObject());
  }

  protected function didFilterPage(array $logs) {
    $blueprint_phids = array_filter(mpull($logs, 'getBlueprintPHID'));
    if ($blueprint_phids) {
      $blueprints = id(new DrydockBlueprintQuery())
        ->setParentQuery($this)
        ->setViewer($this->getViewer())
        ->withPHIDs($blueprint_phids)
        ->execute();
      $blueprints = mpull($blueprints, null, 'getPHID');
    } else {
      $blueprints = array();
    }

    foreach ($logs as $key => $log) {
      $blueprint = null;
      $blueprint_phid = $log->getBlueprintPHID();
      if ($blueprint_phid) {
        $blueprint = idx($blueprints, $blueprint_phid);
      }
      $log->attachBlueprint($blueprint);
    }

    $resource_phids = array_filter(mpull($logs, 'getResourcePHID'));
    if ($resource_phids) {
      $resources = id(new DrydockResourceQuery())
        ->setParentQuery($this)
        ->setViewer($this->getViewer())
        ->withPHIDs($resource_phids)
        ->execute();
      $resources = mpull($resources, null, 'getPHID');
    } else {
      $resources = array();
    }

    foreach ($logs as $key => $log) {
      $resource = null;
      $resource_phid = $log->getResourcePHID();
      if ($resource_phid) {
        $resource = idx($resources, $resource_phid);
      }
      $log->attachResource($resource);
    }

    $lease_phids = array_filter(mpull($logs, 'getLeasePHID'));
    if ($lease_phids) {
      $leases = id(new DrydockLeaseQuery())
        ->setParentQuery($this)
        ->setViewer($this->getViewer())
        ->withPHIDs($lease_phids)
        ->execute();
      $leases = mpull($leases, null, 'getPHID');
    } else {
      $leases = array();
    }

    foreach ($logs as $key => $log) {
      $lease = null;
      $lease_phid = $log->getLeasePHID();
      if ($lease_phid) {
        $lease = idx($leases, $lease_phid);
      }
      $log->attachLease($lease);
    }

    $operation_phids = array_filter(mpull($logs, 'getOperationPHID'));
    if ($operation_phids) {
      $operations = id(new DrydockRepositoryOperationQuery())
        ->setParentQuery($this)
        ->setViewer($this->getViewer())
        ->withPHIDs($operation_phids)
        ->execute();
      $operations = mpull($operations, null, 'getPHID');
    } else {
      $operations = array();
    }

    foreach ($logs as $key => $log) {
      $operation = null;
      $operation_phid = $log->getOperationPHID();
      if ($operation_phid) {
        $operation = idx($operations, $operation_phid);
      }
      $log->attachOperation($operation);
    }

    return $logs;
  }

  protected function buildWhereClauseParts(AphrontDatabaseConnection $conn) {
    $where = parent::buildWhereClauseParts($conn);

    if ($this->ids !== null) {
      $where[] = qsprintf(
        $conn,
        'id IN (%Ls)',
        $this->ids);
    }

    if ($this->blueprintPHIDs !== null) {
      $where[] = qsprintf(
        $conn,
        'blueprintPHID IN (%Ls)',
        $this->blueprintPHIDs);
    }

    if ($this->resourcePHIDs !== null) {
      $where[] = qsprintf(
        $conn,
        'resourcePHID IN (%Ls)',
        $this->resourcePHIDs);
    }

    if ($this->leasePHIDs !== null) {
      $where[] = qsprintf(
        $conn,
        'leasePHID IN (%Ls)',
        $this->leasePHIDs);
    }

    if ($this->operationPHIDs !== null) {
      $where[] = qsprintf(
        $conn,
        'operationPHID IN (%Ls)',
        $this->operationPHIDs);
    }

    return $where;
  }

}
