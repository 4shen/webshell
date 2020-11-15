<?php

final class ReleephRequestReplyHandler
  extends PhabricatorApplicationTransactionReplyHandler {

  public function validateMailReceiver($mail_receiver) {
    if (!($mail_receiver instanceof ReleephRequest)) {
      throw new Exception(pht('Mail receiver is not a %s!', 'ReleephRequest'));
    }
  }

  public function getObjectPrefix() {
    return 'Y';
  }

}
