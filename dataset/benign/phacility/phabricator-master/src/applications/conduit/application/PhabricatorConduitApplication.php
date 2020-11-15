<?php

final class PhabricatorConduitApplication extends PhabricatorApplication {

  public function getBaseURI() {
    return '/conduit/';
  }

  public function getIcon() {
    return 'fa-tty';
  }

  public function canUninstall() {
    return false;
  }

  public function getHelpDocumentationArticles(PhabricatorUser $viewer) {
    return array(
      array(
        'name' => pht('Conduit API Overview'),
        'href' => PhabricatorEnv::getDoclink('Conduit API Overview'),
      ),
    );
  }

  public function getName() {
    return pht('Conduit');
  }

  public function getShortDescription() {
    return pht('Developer API');
  }

  public function getTitleGlyph() {
    return "\xE2\x87\xB5";
  }

  public function getApplicationGroup() {
    return self::GROUP_DEVELOPER;
  }

  public function getApplicationOrder() {
    return 0.100;
  }

  public function getRoutes() {
    return array(
      '/conduit/' => array(
        $this->getQueryRoutePattern() => 'PhabricatorConduitListController',
        'method/(?P<method>[^/]+)/' => 'PhabricatorConduitConsoleController',
        'log/' => array(
          $this->getQueryRoutePattern() =>
            'PhabricatorConduitLogController',
          'view/(?P<view>[^/]+)/' => 'PhabricatorConduitLogController',
        ),
        'token/' => array(
          '' => 'PhabricatorConduitTokenController',
          'edit/(?:(?P<id>\d+)/)?' =>
            'PhabricatorConduitTokenEditController',
          'terminate/(?:(?P<id>\d+)/)?' =>
            'PhabricatorConduitTokenTerminateController',
        ),
        'login/' => 'PhabricatorConduitTokenHandshakeController',
      ),
      '/api/(?P<method>[^/]+)' => 'PhabricatorConduitAPIController',
    );
  }

}
