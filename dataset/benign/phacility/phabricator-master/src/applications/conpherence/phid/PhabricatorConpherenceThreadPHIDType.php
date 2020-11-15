<?php

final class PhabricatorConpherenceThreadPHIDType extends PhabricatorPHIDType {

  const TYPECONST = 'CONP';

  public function getTypeName() {
    return pht('Conpherence Room');
  }

  public function newObject() {
    return new ConpherenceThread();
  }

  public function getPHIDTypeApplicationClass() {
    return 'PhabricatorConpherenceApplication';
  }

  protected function buildQueryForObjects(
    PhabricatorObjectQuery $query,
    array $phids) {
    return id(new ConpherenceThreadQuery())
      ->withPHIDs($phids);
  }

  public function loadHandles(
    PhabricatorHandleQuery $query,
    array $handles,
    array $objects) {

    foreach ($handles as $phid => $handle) {
      $thread = $objects[$phid];

      $title = $thread->getStaticTitle();
      $monogram = $thread->getMonogram();

      $handle->setName($title);
      $handle->setFullName(pht('%s: %s', $monogram, $title));
      $handle->setURI('/'.$monogram);
    }
  }

  public function canLoadNamedObject($name) {
    return preg_match('/^Z\d*[1-9]\d*$/i', $name);
  }

  public function loadNamedObjects(
    PhabricatorObjectQuery $query,
    array $names) {

    $id_map = array();
    foreach ($names as $name) {
      $id = (int)substr($name, 1);
      $id_map[$id][] = $name;
    }

    $objects = id(new ConpherenceThreadQuery())
      ->setViewer($query->getViewer())
      ->withIDs(array_keys($id_map))
      ->execute();
    $objects = mpull($objects, null, 'getID');

    $results = array();
    foreach ($objects as $id => $object) {
      foreach (idx($id_map, $id, array()) as $name) {
        $results[$name] = $object;
      }
    }

    return $results;
  }

}
