<?php

final class PhabricatorProjectsExportEngineExtension
  extends PhabricatorExportEngineExtension {

  const EXTENSIONKEY = 'projects';

  public function supportsObject($object) {
    return ($object instanceof PhabricatorProjectInterface);
  }

  public function newExportFields() {
    return array(
      id(new PhabricatorPHIDListExportField())
        ->setKey('tagPHIDs')
        ->setLabel(pht('Tag PHIDs')),
      id(new PhabricatorStringListExportField())
        ->setKey('tags')
        ->setLabel(pht('Tags')),
    );
  }

  public function newExportData(array $objects) {
    $viewer = $this->getViewer();

    $object_phids = mpull($objects, 'getPHID');

    $projects_query = id(new PhabricatorEdgeQuery())
      ->withSourcePHIDs($object_phids)
      ->withEdgeTypes(
        array(
          PhabricatorProjectObjectHasProjectEdgeType::EDGECONST,
        ));
    $projects_query->execute();

    $handles = $viewer->loadHandles($projects_query->getDestinationPHIDs());

    $map = array();
    foreach ($objects as $object) {
      $object_phid = $object->getPHID();

      $project_phids = $projects_query->getDestinationPHIDs(
        array($object_phid),
        array(PhabricatorProjectObjectHasProjectEdgeType::EDGECONST));

      $handle_list = $handles->newSublist($project_phids);
      $handle_list = iterator_to_array($handle_list);
      $handle_names = mpull($handle_list, 'getName');
      $handle_names = array_values($handle_names);

      $map[] = array(
        'tagPHIDs' => $project_phids,
        'tags' => $handle_names,
      );
    }

    return $map;
  }

}
