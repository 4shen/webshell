<?php

final class ConpherenceUpdateThreadConduitAPIMethod
  extends ConpherenceConduitAPIMethod {

  public function getAPIMethodName() {
    return 'conpherence.updatethread';
  }

  public function getMethodDescription() {
    return pht('Update an existing conpherence room.');
  }

  public function getMethodStatus() {
    return self::METHOD_STATUS_FROZEN;
  }

  public function getMethodStatusDescription() {
    return pht(
      'This method is frozen and will eventually be deprecated. New code '.
      'should use "conpherence.edit" instead.');
  }

  protected function defineParamTypes() {
    return array(
      'id' => 'optional int',
      'phid' => 'optional phid',
      'title' => 'optional string',
      'message' => 'optional string',
      'addParticipantPHIDs' => 'optional list<phids>',
      'removeParticipantPHID' => 'optional phid',
    );
  }

  protected function defineReturnType() {
    return 'bool';
  }

  protected function defineErrorTypes() {
    return array(
      'ERR_USAGE_NO_ROOM_ID' => pht(
        'You must specify a room ID or room PHID to query transactions from.'),
      'ERR_USAGE_ROOM_NOT_FOUND' => pht(
        'Room does not exist or logged in user can not see it.'),
      'ERR_USAGE_ONLY_SELF_REMOVE' => pht(
        'Only a user can remove themselves from a room.'),
      'ERR_USAGE_NO_UPDATES' => pht(
        'You must specify data that actually updates the Conpherence.'),
    );
  }

  protected function execute(ConduitAPIRequest $request) {
    $user = $request->getUser();
    $id = $request->getValue('id');
    $phid = $request->getValue('phid');
    $query = id(new ConpherenceThreadQuery())
      ->setViewer($user);
    if ($id) {
      $query->withIDs(array($id));
    } else if ($phid) {
      $query->withPHIDs(array($phid));
    } else {
      throw new ConduitException('ERR_USAGE_NO_ROOM_ID');
    }
    $conpherence = $query->executeOne();
    if (!$conpherence) {
      throw new ConduitException('ERR_USAGE_ROOM_NOT_FOUND');
    }

    $source = $request->newContentSource();
    $editor = id(new ConpherenceEditor())
      ->setContentSource($source)
      ->setActor($user);
    $xactions = array();
    $add_participant_phids = $request->getValue('addParticipantPHIDs', array());
    $remove_participant_phid = $request->getValue('removeParticipantPHID');
    $message = $request->getValue('message');
    $title = $request->getValue('title');
    if ($add_participant_phids) {
      $xactions[] = id(new ConpherenceTransaction())
        ->setTransactionType(
          ConpherenceThreadParticipantsTransaction::TRANSACTIONTYPE)
        ->setNewValue(array('+' => $add_participant_phids));
    }
    if ($remove_participant_phid) {
      if ($remove_participant_phid != $user->getPHID()) {
        throw new ConduitException('ERR_USAGE_ONLY_SELF_REMOVE');
      }
      $xactions[] = id(new ConpherenceTransaction())
        ->setTransactionType(
          ConpherenceThreadParticipantsTransaction::TRANSACTIONTYPE)
        ->setNewValue(array('-' => array($remove_participant_phid)));
    }
    if ($title) {
      $xactions[] = id(new ConpherenceTransaction())
        ->setTransactionType(
          ConpherenceThreadTitleTransaction::TRANSACTIONTYPE)
        ->setNewValue($title);
    }
    if ($message) {
      $xactions = array_merge(
        $xactions,
        $editor->generateTransactionsFromText(
          $user,
          $conpherence,
          $message));
    }

    try {
      $xactions = $editor->applyTransactions($conpherence, $xactions);
    } catch (PhabricatorApplicationTransactionNoEffectException $ex) {
      throw new ConduitException('ERR_USAGE_NO_UPDATES');
    }

    return true;
  }

}
