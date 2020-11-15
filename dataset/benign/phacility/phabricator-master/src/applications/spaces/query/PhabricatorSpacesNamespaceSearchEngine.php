<?php

final class PhabricatorSpacesNamespaceSearchEngine
  extends PhabricatorApplicationSearchEngine {

  public function getApplicationClassName() {
    return 'PhabricatorSpacesApplication';
  }

  public function getResultTypeDescription() {
    return pht('Spaces');
  }

  public function newQuery() {
    return new PhabricatorSpacesNamespaceQuery();
  }

  protected function buildCustomSearchFields() {
    return array(
      id(new PhabricatorSearchThreeStateField())
        ->setLabel(pht('Active'))
        ->setKey('active')
        ->setOptions(
          pht('(Show All)'),
          pht('Show Only Active Spaces'),
          pht('Hide Active Spaces')),
    );
  }

  protected function buildQueryFromParameters(array $map) {
    $query = $this->newQuery();

    if ($map['active']) {
      $query->withIsArchived(!$map['active']);
    }

    return $query;
  }

  protected function getURI($path) {
    return '/spaces/'.$path;
  }

  protected function getBuiltinQueryNames() {
    $names = array(
      'active' => pht('Active Spaces'),
      'all' => pht('All Spaces'),
    );

    return $names;
  }

  public function buildSavedQueryFromBuiltin($query_key) {
    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);

    switch ($query_key) {
      case 'active':
        return $query->setParameter('active', true);
      case 'all':
        return $query;
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  protected function renderResultList(
    array $spaces,
    PhabricatorSavedQuery $query,
    array $handles) {
    assert_instances_of($spaces, 'PhabricatorSpacesNamespace');

    $viewer = $this->requireViewer();

    $list = new PHUIObjectItemListView();
    $list->setUser($viewer);
    foreach ($spaces as $space) {
      $item = id(new PHUIObjectItemView())
        ->setObjectName($space->getMonogram())
        ->setHeader($space->getNamespaceName())
        ->setHref('/'.$space->getMonogram());

      if ($space->getIsDefaultNamespace()) {
        $item->addIcon('fa-certificate', pht('Default Space'));
      }

      if ($space->getIsArchived()) {
        $item->setDisabled(true);
      }

      $list->addItem($item);
    }

    $result = new PhabricatorApplicationSearchResultView();
    $result->setObjectList($list);
    $result->setNoDataString(pht('No spaces found.'));

    return $result;
  }

  protected function getNewUserBody() {
    $create_button = id(new PHUIButtonView())
      ->setTag('a')
      ->setText(pht('Create a Space'))
      ->setHref('/spaces/create/')
      ->setColor(PHUIButtonView::GREEN);

    $icon = $this->getApplication()->getIcon();
    $app_name =  $this->getApplication()->getName();
    $view = id(new PHUIBigInfoView())
      ->setIcon($icon)
      ->setTitle(pht('Welcome to %s', $app_name))
      ->setDescription(
        pht('Policy namespaces to segment object visibility throughout your '.
        'instance.'))
      ->addAction($create_button);

      return $view;
  }

}
