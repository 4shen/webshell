<?php

final class PhabricatorApplicationDatasource
  extends PhabricatorTypeaheadDatasource {

  public function getBrowseTitle() {
    return pht('Browse Applications');
  }

  public function getPlaceholderText() {
    return pht('Type an application name...');
  }

  public function getDatasourceApplicationClass() {
    return 'PhabricatorApplicationsApplication';
  }

  public function loadResults() {
    $viewer = $this->getViewer();
    $raw_query = $this->getRawQuery();

    $results = array();

    $applications = PhabricatorApplication::getAllInstalledApplications();
    foreach ($applications as $application) {
      $uri = $application->getTypeaheadURI();
      if (!$uri) {
        continue;
      }
      $is_installed = PhabricatorApplication::isClassInstalledForViewer(
        get_class($application),
        $viewer);
      if (!$is_installed) {
        continue;
      }
      $name = $application->getName().' '.$application->getShortDescription();
      $img = 'phui-font-fa phui-icon-view '.$application->getIcon();
      $results[] = id(new PhabricatorTypeaheadResult())
        ->setName($name)
        ->setURI($uri)
        ->setPHID($application->getPHID())
        ->setPriorityString($application->getName())
        ->setDisplayName($application->getName())
        ->setDisplayType($application->getShortDescription())
        ->setPriorityType('apps')
        ->setImageSprite('phabricator-search-icon '.$img)
        ->setIcon($application->getIcon())
        ->addAttribute($application->getShortDescription());
    }

    return $this->filterResultsAgainstTokens($results);
  }

}
