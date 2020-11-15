<?php

final class PhabricatorUserApproveTransaction
  extends PhabricatorUserTransactionType {

  const TRANSACTIONTYPE = 'user.approve';

  public function generateOldValue($object) {
    return (bool)$object->getIsApproved();
  }

  public function generateNewValue($object, $value) {
    return (bool)$value;
  }

  public function applyInternalEffects($object, $value) {
    $object->setIsApproved((int)$value);
  }

  public function applyExternalEffects($object, $value) {
    $user = $object;

    $actor = $this->getActor();
    $title = pht(
      'Phabricator Account "%s" Approved',
      $user->getUsername());

    $body = sprintf(
      "%s\n\n  %s\n\n",
      pht(
        'Your Phabricator account (%s) has been approved by %s. You can '.
        'login here:',
        $user->getUsername(),
        $actor->getUsername()),
      PhabricatorEnv::getProductionURI('/'));

    $mail = id(new PhabricatorMetaMTAMail())
      ->addTos(array($user->getPHID()))
      ->addCCs(array($actor->getPHID()))
      ->setSubject('[Phabricator] '.$title)
      ->setForceDelivery(true)
      ->setBody($body)
      ->saveAndSend();
  }

  public function getTitle() {
    $new = $this->getNewValue();
    if ($new) {
      return pht(
        '%s approved this user.',
        $this->renderAuthor());
    } else {
      return pht(
        '%s rejected this user.',
        $this->renderAuthor());
    }
  }

  public function shouldHideForFeed() {
    return true;
  }

  public function validateTransactions($object, array $xactions) {
    $actor = $this->getActor();
    $errors = array();

    foreach ($xactions as $xaction) {
      $is_approved = (bool)$object->getIsApproved();

      if ((bool)$xaction->getNewValue() === $is_approved) {
        continue;
      }

      if (!$actor->getIsAdmin()) {
        $errors[] = $this->newInvalidError(
          pht('You must be an administrator to approve users.'));
      }
    }

    return $errors;
  }

  public function getRequiredCapabilities(
    $object,
    PhabricatorApplicationTransaction $xaction) {

    // Unlike normal user edits, approvals require admin permissions, which
    // is enforced by validateTransactions().

    return null;
  }
}
