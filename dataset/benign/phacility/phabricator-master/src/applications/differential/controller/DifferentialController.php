<?php

abstract class DifferentialController extends PhabricatorController {

  private $packageChangesetMap;
  private $pathPackageMap;
  private $authorityPackages;

  public function buildSideNavView($for_app = false) {
    $viewer = $this->getRequest()->getUser();

    $nav = new AphrontSideNavFilterView();
    $nav->setBaseURI(new PhutilURI($this->getApplicationURI()));

    id(new DifferentialRevisionSearchEngine())
      ->setViewer($viewer)
      ->addNavigationItems($nav->getMenu());

    $nav->selectFilter(null);

    return $nav;
  }

  public function buildApplicationMenu() {
    return $this->buildSideNavView(true)->getMenu();
  }

  protected function buildPackageMaps(array $changesets) {
    assert_instances_of($changesets, 'DifferentialChangeset');

    $this->packageChangesetMap = array();
    $this->pathPackageMap = array();
    $this->authorityPackages = array();

    if (!$changesets) {
      return;
    }

    $viewer = $this->getViewer();

    $have_owners = PhabricatorApplication::isClassInstalledForViewer(
      'PhabricatorOwnersApplication',
      $viewer);
    if (!$have_owners) {
      return;
    }

    $changeset = head($changesets);
    $diff = $changeset->getDiff();
    $repository_phid = $diff->getRepositoryPHID();
    if (!$repository_phid) {
      return;
    }

    if ($viewer->getPHID()) {
      $packages = id(new PhabricatorOwnersPackageQuery())
        ->setViewer($viewer)
        ->withStatuses(array(PhabricatorOwnersPackage::STATUS_ACTIVE))
        ->withAuthorityPHIDs(array($viewer->getPHID()))
        ->execute();
      $this->authorityPackages = $packages;
    }

    $paths = mpull($changesets, 'getOwnersFilename');

    $control_query = id(new PhabricatorOwnersPackageQuery())
      ->setViewer($viewer)
      ->withStatuses(array(PhabricatorOwnersPackage::STATUS_ACTIVE))
      ->withControl($repository_phid, $paths);
    $control_query->execute();

    foreach ($changesets as $changeset) {
      $changeset_path = $changeset->getOwnersFilename();

      $packages = $control_query->getControllingPackagesForPath(
        $repository_phid,
        $changeset_path);

      // If this particular changeset is generated code and the package does
      // not match generated code, remove it from the list.
      if ($changeset->isGeneratedChangeset()) {
        foreach ($packages as $key => $package) {
          if ($package->getMustMatchUngeneratedPaths()) {
            unset($packages[$key]);
          }
        }
      }

      $this->pathPackageMap[$changeset_path] = $packages;
      foreach ($packages as $package) {
        $this->packageChangesetMap[$package->getPHID()][] = $changeset;
      }
    }
  }

  protected function getAuthorityPackages() {
    if ($this->authorityPackages === null) {
      throw new PhutilInvalidStateException('buildPackageMaps');
    }
    return $this->authorityPackages;
  }

  protected function getChangesetPackages(DifferentialChangeset $changeset) {
    if ($this->pathPackageMap === null) {
      throw new PhutilInvalidStateException('buildPackageMaps');
    }

    $path = $changeset->getOwnersFilename();
    return idx($this->pathPackageMap, $path, array());
  }

  protected function getPackageChangesets($package_phid) {
    if ($this->packageChangesetMap === null) {
      throw new PhutilInvalidStateException('buildPackageMaps');
    }

    return idx($this->packageChangesetMap, $package_phid, array());
  }

  protected function buildTableOfContents(
    array $changesets,
    array $visible_changesets,
    array $coverage) {
    $viewer = $this->getViewer();

    $toc_view = id(new PHUIDiffTableOfContentsListView())
      ->setViewer($viewer)
      ->setBare(true)
      ->setAuthorityPackages($this->getAuthorityPackages());

    foreach ($changesets as $changeset_id => $changeset) {
      $is_visible = isset($visible_changesets[$changeset_id]);
      $anchor = $changeset->getAnchorName();

      $filename = $changeset->getFilename();
      $coverage_id = 'differential-mcoverage-'.md5($filename);

      $item = id(new PHUIDiffTableOfContentsItemView())
        ->setChangeset($changeset)
        ->setIsVisible($is_visible)
        ->setAnchor($anchor)
        ->setCoverage(idx($coverage, $filename))
        ->setCoverageID($coverage_id);

      $packages = $this->getChangesetPackages($changeset);
      $item->setPackages($packages);

      $toc_view->addItem($item);
    }

    return $toc_view;
  }

  protected function loadDiffProperties(array $diffs) {
    $diffs = mpull($diffs, null, 'getID');

    $properties = id(new DifferentialDiffProperty())->loadAllWhere(
      'diffID IN (%Ld)',
      array_keys($diffs));
    $properties = mgroup($properties, 'getDiffID');

    foreach ($diffs as $id => $diff) {
      $values = idx($properties, $id, array());
      $values = mpull($values, 'getData', 'getName');
      $diff->attachDiffProperties($values);
    }
  }


  protected function loadHarbormasterData(array $diffs) {
    $viewer = $this->getViewer();

    $diffs = mpull($diffs, null, 'getPHID');

    $buildables = id(new HarbormasterBuildableQuery())
      ->setViewer($viewer)
      ->withBuildablePHIDs(array_keys($diffs))
      ->withManualBuildables(false)
      ->needBuilds(true)
      ->needTargets(true)
      ->execute();

    $buildables = mpull($buildables, null, 'getBuildablePHID');
    foreach ($diffs as $phid => $diff) {
      $diff->attachBuildable(idx($buildables, $phid));
    }

    $target_map = array();
    foreach ($diffs as $phid => $diff) {
      $target_map[$phid] = $diff->getBuildTargetPHIDs();
    }
    $all_target_phids = array_mergev($target_map);

    if ($all_target_phids) {
      $unit_messages = id(new HarbormasterBuildUnitMessageQuery())
        ->setViewer($viewer)
        ->withBuildTargetPHIDs($all_target_phids)
        ->execute();
      $unit_messages = mgroup($unit_messages, 'getBuildTargetPHID');
    } else {
      $unit_messages = array();
    }

    foreach ($diffs as $phid => $diff) {
      $target_phids = idx($target_map, $phid, array());
      $messages = array_select_keys($unit_messages, $target_phids);
      $messages = array_mergev($messages);
      $diff->attachUnitMessages($messages);
    }

    // For diffs with no messages, look for legacy unit messages stored on the
    // diff itself.
    foreach ($diffs as $phid => $diff) {
      if ($diff->getUnitMessages()) {
        continue;
      }

      if (!$diff->hasDiffProperty('arc:unit')) {
        continue;
      }

      $legacy_messages = $diff->getProperty('arc:unit');
      if (!$legacy_messages) {
        continue;
      }

      // Show the top 100 legacy lint messages. Previously, we showed some
      // by default and let the user toggle the rest. With modern messages,
      // we can send the user to the Harbormaster detail page. Just show
      // "a lot" of messages in legacy cases to try to strike a balance
      // between implementation simplicity and compatibility.
      $legacy_messages = array_slice($legacy_messages, 0, 100);

      $messages = array();
      foreach ($legacy_messages as $message) {
        $messages[] = HarbormasterBuildUnitMessage::newFromDictionary(
          new HarbormasterBuildTarget(),
          $this->getModernUnitMessageDictionary($message));
      }

      $diff->attachUnitMessages($messages);
    }
  }

  private function getModernUnitMessageDictionary(array $map) {
    // Strip out `null` values to satisfy stricter typechecks.
    foreach ($map as $key => $value) {
      if ($value === null) {
        unset($map[$key]);
      }
    }

    // Cast duration to a float since it used to be a string in some
    // cases.
    if (isset($map['duration'])) {
      $map['duration'] = (double)$map['duration'];
    }

    return $map;
  }

  protected function getDiffTabLabels(array $diffs) {
    // Make sure we're only going to render unique diffs.
    $diffs = mpull($diffs, null, 'getID');
    $labels = array(pht('Left'), pht('Right'));

    $results = array();

    foreach ($diffs as $diff) {
      if (count($diffs) == 2) {
        $label = array_shift($labels);
        $label = pht('%s (Diff %d)', $label, $diff->getID());
      } else {
        $label = pht('Diff %d', $diff->getID());
      }

      $results[] = array(
        $label,
        $diff,
      );
    }

    return $results;
  }


}
