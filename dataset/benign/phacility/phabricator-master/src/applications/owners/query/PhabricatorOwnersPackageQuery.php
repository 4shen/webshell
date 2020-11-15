<?php

final class PhabricatorOwnersPackageQuery
  extends PhabricatorCursorPagedPolicyAwareQuery {

  private $ids;
  private $phids;
  private $ownerPHIDs;
  private $authorityPHIDs;
  private $repositoryPHIDs;
  private $paths;
  private $statuses;

  private $controlMap = array();
  private $controlResults;

  private $needPaths;


  /**
   * Query owner PHIDs exactly. This does not expand authorities, so a user
   * PHID will not match projects the user is a member of.
   */
  public function withOwnerPHIDs(array $phids) {
    $this->ownerPHIDs = $phids;
    return $this;
  }

  /**
   * Query owner authority. This will expand authorities, so a user PHID will
   * match both packages they own directly and packages owned by a project they
   * are a member of.
   */
  public function withAuthorityPHIDs(array $phids) {
    $this->authorityPHIDs = $phids;
    return $this;
  }

  public function withPHIDs(array $phids) {
    $this->phids = $phids;
    return $this;
  }

  public function withIDs(array $ids) {
    $this->ids = $ids;
    return $this;
  }

  public function withRepositoryPHIDs(array $phids) {
    $this->repositoryPHIDs = $phids;
    return $this;
  }

  public function withPaths(array $paths) {
    $this->paths = $paths;
    return $this;
  }

  public function withStatuses(array $statuses) {
    $this->statuses = $statuses;
    return $this;
  }

  public function withControl($repository_phid, array $paths) {
    if (empty($this->controlMap[$repository_phid])) {
      $this->controlMap[$repository_phid] = array();
    }

    foreach ($paths as $path) {
      $path = (string)$path;
      $this->controlMap[$repository_phid][$path] = $path;
    }

    // We need to load paths to execute control queries.
    $this->needPaths = true;

    return $this;
  }

  public function withNameNgrams($ngrams) {
    return $this->withNgramsConstraint(
      new PhabricatorOwnersPackageNameNgrams(),
      $ngrams);
  }

  public function needPaths($need_paths) {
    $this->needPaths = $need_paths;
    return $this;
  }

  public function newResultObject() {
    return new PhabricatorOwnersPackage();
  }

  protected function willExecute() {
    $this->controlResults = array();
  }

  protected function loadPage() {
    return $this->loadStandardPage($this->newResultObject());
  }

  protected function willFilterPage(array $packages) {
    $package_ids = mpull($packages, 'getID');

    $owners = id(new PhabricatorOwnersOwner())->loadAllWhere(
      'packageID IN (%Ld)',
      $package_ids);
    $owners = mgroup($owners, 'getPackageID');
    foreach ($packages as $package) {
      $package->attachOwners(idx($owners, $package->getID(), array()));
    }

    return $packages;
  }

  protected function didFilterPage(array $packages) {
    $package_ids = mpull($packages, 'getID');

    if ($this->needPaths) {
      $paths = id(new PhabricatorOwnersPath())->loadAllWhere(
        'packageID IN (%Ld)',
        $package_ids);
      $paths = mgroup($paths, 'getPackageID');

      foreach ($packages as $package) {
        $package->attachPaths(idx($paths, $package->getID(), array()));
      }
    }

    if ($this->controlMap) {
      foreach ($packages as $package) {
        // If this package is archived, it's no longer a controlling package
        // for any path. In particular, it can not force active packages with
        // weak dominion to give up control.
        if ($package->isArchived()) {
          continue;
        }

        $this->controlResults[$package->getID()] = $package;
      }
    }

    return $packages;
  }

  protected function buildJoinClauseParts(AphrontDatabaseConnection $conn) {
    $joins = parent::buildJoinClauseParts($conn);

    if ($this->shouldJoinOwnersTable()) {
      $joins[] = qsprintf(
        $conn,
        'JOIN %T o ON o.packageID = p.id',
        id(new PhabricatorOwnersOwner())->getTableName());
    }

    if ($this->shouldJoinPathTable()) {
      $joins[] = qsprintf(
        $conn,
        'JOIN %T rpath ON rpath.packageID = p.id',
        id(new PhabricatorOwnersPath())->getTableName());
    }

    return $joins;
  }

  protected function buildWhereClauseParts(AphrontDatabaseConnection $conn) {
    $where = parent::buildWhereClauseParts($conn);

    if ($this->phids !== null) {
      $where[] = qsprintf(
        $conn,
        'p.phid IN (%Ls)',
        $this->phids);
    }

    if ($this->ids !== null) {
      $where[] = qsprintf(
        $conn,
        'p.id IN (%Ld)',
        $this->ids);
    }

    if ($this->repositoryPHIDs !== null) {
      $where[] = qsprintf(
        $conn,
        'rpath.repositoryPHID IN (%Ls)',
        $this->repositoryPHIDs);
    }

    if ($this->authorityPHIDs !== null) {
      $authority_phids = $this->expandAuthority($this->authorityPHIDs);
      $where[] = qsprintf(
        $conn,
        'o.userPHID IN (%Ls)',
        $authority_phids);
    }

    if ($this->ownerPHIDs !== null) {
      $where[] = qsprintf(
        $conn,
        'o.userPHID IN (%Ls)',
        $this->ownerPHIDs);
    }

    if ($this->paths !== null) {
      $where[] = qsprintf(
        $conn,
        'rpath.pathIndex IN (%Ls)',
        $this->getFragmentIndexesForPaths($this->paths));
    }

    if ($this->statuses !== null) {
      $where[] = qsprintf(
        $conn,
        'p.status IN (%Ls)',
        $this->statuses);
    }

    if ($this->controlMap) {
      $clauses = array();
      foreach ($this->controlMap as $repository_phid => $paths) {
        $indexes = $this->getFragmentIndexesForPaths($paths);

        $clauses[] = qsprintf(
          $conn,
          '(rpath.repositoryPHID = %s AND rpath.pathIndex IN (%Ls))',
          $repository_phid,
          $indexes);
      }
      $where[] = qsprintf($conn, '%LO', $clauses);
    }

    return $where;
  }

  protected function shouldGroupQueryResultRows() {
    if ($this->shouldJoinOwnersTable()) {
      return true;
    }

    if ($this->shouldJoinPathTable()) {
      return true;
    }

    return parent::shouldGroupQueryResultRows();
  }

  public function getBuiltinOrders() {
    return array(
      'name' => array(
        'vector' => array('name'),
        'name' => pht('Name'),
      ),
    ) + parent::getBuiltinOrders();
  }

  public function getOrderableColumns() {
    return parent::getOrderableColumns() + array(
      'name' => array(
        'table' => $this->getPrimaryTableAlias(),
        'column' => 'name',
        'type' => 'string',
        'unique' => true,
        'reverse' => true,
      ),
    );
  }

  protected function newPagingMapFromPartialObject($object) {
    return array(
      'id' => (int)$object->getID(),
      'name' => $object->getName(),
    );
  }

  public function getQueryApplicationClass() {
    return 'PhabricatorOwnersApplication';
  }

  protected function getPrimaryTableAlias() {
    return 'p';
  }

  private function shouldJoinOwnersTable() {
    if ($this->ownerPHIDs !== null) {
      return true;
    }

    if ($this->authorityPHIDs !== null) {
      return true;
    }

    return false;
  }

  private function shouldJoinPathTable() {
    if ($this->repositoryPHIDs !== null) {
      return true;
    }

    if ($this->paths !== null) {
      return true;
    }

    if ($this->controlMap) {
      return true;
    }

    return false;
  }

  private function expandAuthority(array $phids) {
    $projects = id(new PhabricatorProjectQuery())
      ->setViewer($this->getViewer())
      ->withMemberPHIDs($phids)
      ->execute();
    $project_phids = mpull($projects, 'getPHID');

    return array_fuse($phids) + array_fuse($project_phids);
  }

  private function getFragmentsForPaths(array $paths) {
    $fragments = array();

    foreach ($paths as $path) {
      foreach (PhabricatorOwnersPackage::splitPath($path) as $fragment) {
        $fragments[$fragment] = $fragment;
      }
    }

    return $fragments;
  }

  private function getFragmentIndexesForPaths(array $paths) {
    $indexes = array();

    foreach ($this->getFragmentsForPaths($paths) as $fragment) {
      $indexes[] = PhabricatorHash::digestForIndex($fragment);
    }

    return $indexes;
  }


/* -(  Path Control  )------------------------------------------------------- */


  /**
   * Get a list of all packages which control a path or its parent directories,
   * ordered from weakest to strongest.
   *
   * The first package has the most specific claim on the path; the last
   * package has the most general claim. Multiple packages may have claims of
   * equal strength, so this ordering is primarily one of usability and
   * convenience.
   *
   * @return list<PhabricatorOwnersPackage> List of controlling packages.
   */
  public function getControllingPackagesForPath(
    $repository_phid,
    $path,
    $ignore_dominion = false) {
    $path = (string)$path;

    if (!isset($this->controlMap[$repository_phid][$path])) {
      throw new PhutilInvalidStateException('withControl');
    }

    if ($this->controlResults === null) {
      throw new PhutilInvalidStateException('execute');
    }

    $packages = $this->controlResults;
    $weak_dominion = PhabricatorOwnersPackage::DOMINION_WEAK;

    $path_fragments = PhabricatorOwnersPackage::splitPath($path);
    $fragment_count = count($path_fragments);

    $matches = array();
    foreach ($packages as $package_id => $package) {
      $best_match = null;
      $include = false;

      $repository_paths = $package->getPathsForRepository($repository_phid);
      foreach ($repository_paths as $package_path) {
        $strength = $package_path->getPathMatchStrength(
          $path_fragments,
          $fragment_count);
        if ($strength > $best_match) {
          $best_match = $strength;
          $include = !$package_path->getExcluded();
        }
      }

      if ($best_match && $include) {
        if ($ignore_dominion) {
          $is_weak = false;
        } else {
          $is_weak = ($package->getDominion() == $weak_dominion);
        }
        $matches[$package_id] = array(
          'strength' => $best_match,
          'weak' => $is_weak,
          'package' => $package,
        );
      }
    }

    // At each strength level, drop weak packages if there are also strong
    // packages of the same strength.
    $strength_map = igroup($matches, 'strength');
    foreach ($strength_map as $strength => $package_list) {
      $any_strong = false;
      foreach ($package_list as $package_id => $package) {
        if (!$package['weak']) {
          $any_strong = true;
          break;
        }
      }
      if ($any_strong) {
        foreach ($package_list as $package_id => $package) {
          if ($package['weak']) {
            unset($matches[$package_id]);
          }
        }
      }
    }

    $matches = isort($matches, 'strength');
    $matches = array_reverse($matches);

    $strongest = null;
    foreach ($matches as $package_id => $match) {
      if ($strongest === null) {
        $strongest = $match['strength'];
      }

      if ($match['strength'] === $strongest) {
        continue;
      }

      if ($match['weak']) {
        unset($matches[$package_id]);
      }
    }

    return array_values(ipull($matches, 'package'));
  }

}
