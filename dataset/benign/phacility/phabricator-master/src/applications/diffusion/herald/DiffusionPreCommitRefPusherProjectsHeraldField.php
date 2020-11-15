<?php

final class DiffusionPreCommitRefPusherProjectsHeraldField
  extends DiffusionPreCommitRefHeraldField {

  const FIELDCONST = 'diffusion.pre.ref.pusher.projects';

  public function getHeraldFieldName() {
    return pht("Pusher's projects");
  }

  public function getHeraldFieldValue($object) {
    return $this->getAdapter()
      ->getHookEngine()
      ->loadViewerProjectPHIDsForHerald();
  }

  protected function getHeraldFieldStandardType() {
    return HeraldField::STANDARD_PHID_LIST;
  }

  protected function getDatasource() {
    return new PhabricatorProjectDatasource();
  }

}
