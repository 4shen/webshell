<?php

final class PhabricatorPholioApplication extends PhabricatorApplication {

  public function getName() {
    return pht('Pholio');
  }

  public function getBaseURI() {
    return '/pholio/';
  }

  public function getShortDescription() {
    return pht('Review Mocks and Design');
  }

  public function getIcon() {
    return 'fa-camera-retro';
  }

  public function getTitleGlyph() {
    return "\xE2\x9D\xA6";
  }

  public function getFlavorText() {
    return pht('Things before they were cool.');
  }

  public function getRemarkupRules() {
    return array(
      new PholioRemarkupRule(),
    );
  }

  public function getRoutes() {
    return array(
      '/M(?P<id>[1-9]\d*)(?:/(?P<imageID>\d+)/)?' => 'PholioMockViewController',
      '/pholio/' => array(
        '(?:query/(?P<queryKey>[^/]+)/)?' => 'PholioMockListController',
        'new/'                  => 'PholioMockEditController',
        'create/'               => 'PholioMockEditController',
        'edit/(?P<id>\d+)/'     => 'PholioMockEditController',
        'archive/(?P<id>\d+)/'  => 'PholioMockArchiveController',
        'comment/(?P<id>\d+)/'  => 'PholioMockCommentController',
        'inline/' => array(
          '(?:(?P<id>\d+)/)?' => 'PholioInlineController',
          'list/(?P<id>\d+)/' => 'PholioInlineListController',
        ),
        'image/' => array(
          'upload/' => 'PholioImageUploadController',
        ),
      ),
    );
  }

  protected function getCustomCapabilities() {
    return array(
      PholioDefaultViewCapability::CAPABILITY => array(
        'template' => PholioMockPHIDType::TYPECONST,
        'capability' => PhabricatorPolicyCapability::CAN_VIEW,
      ),
      PholioDefaultEditCapability::CAPABILITY => array(
        'template' => PholioMockPHIDType::TYPECONST,
        'capability' => PhabricatorPolicyCapability::CAN_EDIT,
      ),
    );
  }

  public function getMailCommandObjects() {
    return array(
      'mock' => array(
        'name' => pht('Email Commands: Mocks'),
        'header' => pht('Interacting with Pholio Mocks'),
        'object' => new PholioMock(),
        'summary' => pht(
          'This page documents the commands you can use to interact with '.
          'mocks in Pholio.'),
      ),
    );
  }

  public function getApplicationSearchDocumentTypes() {
    return array(
      PholioMockPHIDType::TYPECONST,
    );
  }

}
