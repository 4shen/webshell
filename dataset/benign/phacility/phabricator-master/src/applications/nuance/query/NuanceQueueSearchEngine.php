<?php

final class NuanceQueueSearchEngine
  extends PhabricatorApplicationSearchEngine {

  public function getApplicationClassName() {
    return 'PhabricatorNuanceApplication';
  }

  public function getResultTypeDescription() {
    return pht('Nuance Queues');
  }

  public function newQuery() {
    return new NuanceQueueQuery();
  }

  protected function buildQueryFromParameters(array $map) {
    $query = $this->newQuery();

    return $query;
  }

  protected function buildCustomSearchFields() {
    return array();
  }

  protected function getURI($path) {
    return '/nuance/queue/'.$path;
  }

  protected function getBuiltinQueryNames() {
    $names = array(
      'all' => pht('All Queues'),
    );

    return $names;
  }

  public function buildSavedQueryFromBuiltin($query_key) {
    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);

    switch ($query_key) {
      case 'all':
        return $query;
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  protected function renderResultList(
    array $queues,
    PhabricatorSavedQuery $query,
    array $handles) {
    assert_instances_of($queues, 'NuanceQueue');

    $viewer = $this->requireViewer();

    $list = new PHUIObjectItemListView();
    $list->setUser($viewer);
    foreach ($queues as $queue) {
      $item = id(new PHUIObjectItemView())
        ->setObjectName(pht('Queue %d', $queue->getID()))
        ->setHeader($queue->getName())
        ->setHref($queue->getURI());
      $list->addItem($item);
    }

    $result = new PhabricatorApplicationSearchResultView();
    $result->setObjectList($list);
    $result->setNoDataString(pht('No queues found.'));

    return $result;
  }

}
