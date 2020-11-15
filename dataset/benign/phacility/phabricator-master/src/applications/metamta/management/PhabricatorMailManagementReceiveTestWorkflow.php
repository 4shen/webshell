<?php

final class PhabricatorMailManagementReceiveTestWorkflow
  extends PhabricatorMailManagementWorkflow {

  protected function didConstruct() {
    $this
      ->setName('receive-test')
      ->setSynopsis(
        pht(
          'Simulate receiving mail. This is primarily useful if you are '.
          'developing new mail receivers.'))
      ->setExamples(
        '**receive-test** --as alincoln --to D123 < body.txt')
      ->setArguments(
        array(
          array(
            'name'    => 'as',
            'param'   => 'user',
            'help'    => pht('Act as the specified user.'),
          ),
          array(
            'name'    => 'from',
            'param'   => 'email',
            'help'    => pht('Simulate mail delivery "From:" the given user.'),
          ),
          array(
            'name'    => 'to',
            'param'   => 'object',
            'help'    => pht('Simulate mail delivery "To:" the given object.'),
          ),
          array(
            'name' => 'cc',
            'param' => 'address',
            'help' => pht('Simulate a mail delivery "Cc:" address.'),
            'repeat' => true,
          ),
        ));
  }

  public function execute(PhutilArgumentParser $args) {
    $viewer = $this->getViewer();
    $console = PhutilConsole::getConsole();

    $to = $args->getArg('to');
    if (!$to) {
      throw new PhutilArgumentUsageException(
        pht(
          "Use '%s' to specify the receiving object or email address.",
          '--to'));
    }

    $to_application_email = id(new PhabricatorMetaMTAApplicationEmailQuery())
      ->setViewer($this->getViewer())
      ->withAddresses(array($to))
      ->executeOne();

    $as = $args->getArg('as');
    if (!$as && $to_application_email) {
      $default_phid = $to_application_email->getConfigValue(
        PhabricatorMetaMTAApplicationEmail::CONFIG_DEFAULT_AUTHOR);
      if ($default_phid) {
        $default_user = id(new PhabricatorPeopleQuery())
          ->setViewer($this->getViewer())
          ->withPHIDs(array($default_phid))
          ->executeOne();
        if ($default_user) {
          $as = $default_user->getUsername();
        }
      }
    }

    if (!$as) {
      throw new PhutilArgumentUsageException(
        pht("Use '--as' to specify the acting user."));
    }

    $user = id(new PhabricatorPeopleQuery())
      ->setViewer($this->getViewer())
      ->withUsernames(array($as))
      ->executeOne();
    if (!$user) {
      throw new PhutilArgumentUsageException(
        pht("No such user '%s' exists.", $as));
    }


    $from = $args->getArg('from');
    if (!$from) {
      $from = $user->loadPrimaryEmail()->getAddress();
    }

    $cc = $args->getArg('cc');

    $console->writeErr("%s\n", pht('Reading message body from stdin...'));
    $body = file_get_contents('php://stdin');

    $received = new PhabricatorMetaMTAReceivedMail();
    $header_content = array(
      'Message-ID' => Filesystem::readRandomCharacters(12),
      'From'       => $from,
      'Cc' => implode(', ', $cc),
    );

    if (preg_match('/.+@.+/', $to)) {
      $header_content['to'] = $to;
    } else {

      // We allow the user to use an object name instead of a real address
      // as a convenience. To build the mail, we build a similar message and
      // look for a receiver which will accept it.

      // In the general case, mail may be processed by multiple receivers,
      // but mail to objects only ever has one receiver today.

      $pseudohash = PhabricatorObjectMailReceiver::computeMailHash('x', 'y');

      $raw_target = $to.'+1+'.$pseudohash;
      $target = new PhutilEmailAddress($raw_target.'@local.cli');

      $pseudomail = id(new PhabricatorMetaMTAReceivedMail())
        ->setHeaders(
          array(
            'to' => $raw_target,
          ));

      $receivers = id(new PhutilClassMapQuery())
        ->setAncestorClass('PhabricatorMailReceiver')
        ->setFilterMethod('isEnabled')
        ->execute();

      $receiver = null;
      foreach ($receivers as $possible_receiver) {
        $possible_receiver = id(clone $possible_receiver)
          ->setViewer($viewer)
          ->setSender($user);

        if (!$possible_receiver->canAcceptMail($pseudomail, $target)) {
          continue;
        }
        $receiver = $possible_receiver;
        break;
      }

      if (!$receiver) {
        throw new Exception(
          pht("No configured mail receiver can accept mail to '%s'.", $to));
      }

      if (!($receiver instanceof PhabricatorObjectMailReceiver)) {
        $class = get_class($receiver);
        throw new Exception(
          pht(
            "Receiver '%s' accepts mail to '%s', but is not a ".
            "subclass of PhabricatorObjectMailReceiver.",
            $class,
            $to));
      }

      $object = $receiver->loadMailReceiverObject($to, $user);
      if (!$object) {
        throw new Exception(pht("No such object '%s'!", $to));
      }

      $mail_key = PhabricatorMetaMTAMailProperties::loadMailKey($object);

      $hash = PhabricatorObjectMailReceiver::computeMailHash(
        $mail_key,
        $user->getPHID());

      $header_content['to'] = $to.'+'.$user->getID().'+'.$hash.'@test.com';
    }

    $received->setHeaders($header_content);
    $received->setBodies(
      array(
        'text' => $body,
      ));

    $received->save();
    $received->processReceivedMail();

    $console->writeErr(
      "%s\n\n    phabricator/ $ ./bin/mail show-inbound --id %d\n\n",
      pht('Mail received! You can view details by running this command:'),
      $received->getID());
  }

}
