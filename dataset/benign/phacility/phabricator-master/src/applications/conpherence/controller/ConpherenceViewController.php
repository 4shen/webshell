<?php

final class ConpherenceViewController extends
  ConpherenceController {

  const OLDER_FETCH_LIMIT = 5;

  public function shouldAllowPublic() {
    return true;
  }

  public function handleRequest(AphrontRequest $request) {
    $user = $request->getUser();

    $conpherence_id = $request->getURIData('id');
    if (!$conpherence_id) {
      return new Aphront404Response();
    }
    $query = id(new ConpherenceThreadQuery())
      ->setViewer($user)
      ->withIDs(array($conpherence_id))
      ->needProfileImage(true)
      ->needTransactions(true)
      ->setTransactionLimit($this->getMainQueryLimit());

    $before_transaction_id = $request->getInt('oldest_transaction_id');
    $after_transaction_id = $request->getInt('newest_transaction_id');
    $old_message_id = $request->getURIData('messageID');
    if ($before_transaction_id && ($old_message_id || $after_transaction_id)) {
      throw new Aphront400Response();
    }
    if ($old_message_id && $after_transaction_id) {
      throw new Aphront400Response();
    }

    $marker_type = 'older';
    if ($before_transaction_id) {
      $query
        ->setBeforeTransactionID($before_transaction_id);
    }
    if ($old_message_id) {
      $marker_type = 'olderandnewer';
      $query
        ->setAfterTransactionID($old_message_id - 1);
    }
    if ($after_transaction_id) {
      $marker_type = 'newer';
      $query
        ->setAfterTransactionID($after_transaction_id);
    }

    $conpherence = $query->executeOne();
    if (!$conpherence) {
      return new Aphront404Response();
    }
    $this->setConpherence($conpherence);

    $participant = $conpherence->getParticipantIfExists($user->getPHID());
    $theme = ConpherenceRoomSettings::COLOR_LIGHT;

    if ($participant) {
      $settings = $participant->getSettings();
      $theme = idx($settings, 'theme', ConpherenceRoomSettings::COLOR_LIGHT);
      if (!$participant->isUpToDate($conpherence)) {
        $write_guard = AphrontWriteGuard::beginScopedUnguardedWrites();
        $participant->markUpToDate($conpherence);
        $user->clearCacheData(PhabricatorUserMessageCountCacheType::KEY_COUNT);
        unset($write_guard);
      }
    }

    $data = ConpherenceTransactionRenderer::renderTransactions(
      $user,
      $conpherence,
      $marker_type);
    $messages = ConpherenceTransactionRenderer::renderMessagePaneContent(
      $data['transactions'],
      $data['oldest_transaction_id'],
      $data['newest_transaction_id']);
    if ($before_transaction_id || $after_transaction_id) {
      $header = null;
      $form = null;
      $content = array('transactions' => $messages);
    } else {
      $header = $this->buildHeaderPaneContent($conpherence);
      $search = $this->buildSearchForm();
      $form = $this->renderFormContent();
      $content = array(
        'header' => $header,
        'search' => $search,
        'transactions' => $messages,
        'form' => $form,
      );
    }

    $d_data = $conpherence->getDisplayData($user);
    $content['title'] = $title = $d_data['title'];

    if ($request->isAjax()) {
      $dropdown_query = id(new AphlictDropdownDataQuery())
        ->setViewer($user);
      $dropdown_query->execute();
      $content['threadID'] = $conpherence->getID();
      $content['threadPHID'] = $conpherence->getPHID();
      $content['latestTransactionID'] = $data['latest_transaction_id'];
      $content['canEdit'] = PhabricatorPolicyFilter::hasCapability(
        $user,
        $conpherence,
        PhabricatorPolicyCapability::CAN_EDIT);
      $content['aphlictDropdownData'] = array(
        $dropdown_query->getNotificationData(),
        $dropdown_query->getConpherenceData(),
      );
      return id(new AphrontAjaxResponse())->setContent($content);
    }

    $layout = id(new ConpherenceLayoutView())
      ->setUser($user)
      ->setBaseURI($this->getApplicationURI())
      ->setThread($conpherence)
      ->setHeader($header)
      ->setSearch($search)
      ->setMessages($messages)
      ->setReplyForm($form)
      ->setTheme($theme)
      ->setLatestTransactionID($data['latest_transaction_id'])
      ->setRole('thread');

    $participating = $conpherence->getParticipantIfExists($user->getPHID());

    if (!$user->isLoggedIn()) {
      $layout->addClass('conpherence-no-pontificate');
    }

    return $this->newPage()
      ->setTitle($title)
      ->setPageObjectPHIDs(array($conpherence->getPHID()))
      ->appendChild($layout);
  }

  private function renderFormContent() {

    $conpherence = $this->getConpherence();
    $user = $this->getRequest()->getUser();

    $participating = $conpherence->getParticipantIfExists($user->getPHID());
    $draft = PhabricatorDraft::newFromUserAndKey(
      $user,
      $conpherence->getPHID());
    $update_uri = $this->getApplicationURI('update/'.$conpherence->getID().'/');

    if ($user->isLoggedIn()) {
      $this->initBehavior('conpherence-pontificate');
      if ($participating) {
        $action = ConpherenceUpdateActions::MESSAGE;
        $status = new PhabricatorNotificationStatusView();
      } else {
        $action = ConpherenceUpdateActions::JOIN_ROOM;
        $status = pht('Sending a message will also join the room.');
      }

      $form = id(new AphrontFormView())
        ->setUser($user)
        ->setAction($update_uri)
        ->addSigil('conpherence-pontificate')
        ->setWorkflow(true)
        ->addHiddenInput('action', $action)
        ->appendChild(
          id(new PhabricatorRemarkupControl())
          ->setUser($user)
          ->setName('text')
          ->setSendOnEnter(true)
          ->setValue($draft->getDraft()));

      $status_view = phutil_tag(
        'div',
        array(
          'class' => 'conpherence-room-status',
          'id' => 'conpherence-room-status',
        ),
        $status);

      $view = phutil_tag_div(
        'pontificate-container', array($form, $status_view));

      return $view;

    } else {
      // user not logged in so give them a login button.
      $login_href = id(new PhutilURI('/auth/start/'))
        ->replaceQueryParam('next', '/'.$conpherence->getMonogram());
      return id(new PHUIFormLayoutView())
        ->addClass('login-to-participate')
        ->appendInstructions(pht('Log in to join this room and participate.'))
        ->appendChild(
          id(new PHUIButtonView())
          ->setTag('a')
          ->setText(pht('Log In to Participate'))
          ->setHref((string)$login_href));
    }
  }

  private function getMainQueryLimit() {
    $request = $this->getRequest();
    $base_limit = ConpherenceThreadQuery::TRANSACTION_LIMIT;
    if ($request->getURIData('messageID')) {
      $base_limit = $base_limit - self::OLDER_FETCH_LIMIT;
    }
    return $base_limit;
  }
}
