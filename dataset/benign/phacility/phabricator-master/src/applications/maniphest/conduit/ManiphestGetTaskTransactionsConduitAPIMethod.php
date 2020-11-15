<?php

final class ManiphestGetTaskTransactionsConduitAPIMethod
  extends ManiphestConduitAPIMethod {

  public function getAPIMethodName() {
    return 'maniphest.gettasktransactions';
  }

  public function getMethodDescription() {
    return pht('Retrieve Maniphest task transactions.');
  }

  protected function defineParamTypes() {
    return array(
      'ids' => 'required list<int>',
    );
  }

  protected function defineReturnType() {
    return 'nonempty list<dict<string, wild>>';
  }

  public function getMethodStatus() {
    return self::METHOD_STATUS_FROZEN;
  }

  public function getMethodStatusDescription() {
    return pht(
      'This method is frozen and will eventually be deprecated. New code '.
      'should use "transaction.search" instead.');
  }

  protected function execute(ConduitAPIRequest $request) {
    $results = array();
    $task_ids = $request->getValue('ids');

    if (!$task_ids) {
      return $results;
    }

    $tasks = id(new ManiphestTaskQuery())
      ->setViewer($request->getUser())
      ->withIDs($task_ids)
      ->execute();
    $tasks = mpull($tasks, null, 'getPHID');

    $transactions = array();
    if ($tasks) {
      $transactions = id(new ManiphestTransactionQuery())
        ->setViewer($request->getUser())
        ->withObjectPHIDs(mpull($tasks, 'getPHID'))
        ->needComments(true)
        ->execute();
    }

    foreach ($transactions as $transaction) {
      $task_phid = $transaction->getObjectPHID();
      if (empty($tasks[$task_phid])) {
        continue;
      }

      $task_id = $tasks[$task_phid]->getID();

      $comments = null;
      if ($transaction->hasComment()) {
        $comments = $transaction->getComment()->getContent();
      }

      $results[$task_id][] = array(
        'taskID'  => $task_id,
        'transactionID' => $transaction->getID(),
        'transactionPHID' => $transaction->getPHID(),
        'transactionType'  => $transaction->getTransactionType(),
        'oldValue'  => $transaction->getOldValue(),
        'newValue'  => $transaction->getNewValue(),
        'comments'      => $comments,
        'authorPHID'  => $transaction->getAuthorPHID(),
        'dateCreated' => $transaction->getDateCreated(),
      );
    }

    return $results;
  }

}
