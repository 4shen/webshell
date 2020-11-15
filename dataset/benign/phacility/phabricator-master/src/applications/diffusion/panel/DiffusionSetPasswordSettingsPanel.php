<?php

final class DiffusionSetPasswordSettingsPanel extends PhabricatorSettingsPanel {

  public function isManagementPanel() {
    if ($this->getUser()->getIsMailingList()) {
      return false;
    }

    return true;
  }

  public function getPanelKey() {
    return 'vcspassword';
  }

  public function getPanelName() {
    return pht('VCS Password');
  }

  public function getPanelMenuIcon() {
    return 'fa-code';
  }

  public function getPanelGroupKey() {
    return PhabricatorSettingsAuthenticationPanelGroup::PANELGROUPKEY;
  }

  public function isEnabled() {
    return PhabricatorEnv::getEnvConfig('diffusion.allow-http-auth');
  }

  public function processRequest(AphrontRequest $request) {
    $viewer = $request->getUser();
    $user = $this->getUser();

    $token = id(new PhabricatorAuthSessionEngine())->requireHighSecuritySession(
      $viewer,
      $request,
      '/settings/');

    $vcs_type = PhabricatorAuthPassword::PASSWORD_TYPE_VCS;

    $vcspasswords = id(new PhabricatorAuthPasswordQuery())
      ->setViewer($viewer)
      ->withObjectPHIDs(array($user->getPHID()))
      ->withPasswordTypes(array($vcs_type))
      ->withIsRevoked(false)
      ->execute();
    if ($vcspasswords) {
      $vcspassword = head($vcspasswords);
    } else {
      $vcspassword = PhabricatorAuthPassword::initializeNewPassword(
        $user,
        $vcs_type);
    }

    $panel_uri = $this->getPanelURI('?saved=true');

    $errors = array();

    $e_password = true;
    $e_confirm = true;

    $content_source = PhabricatorContentSource::newFromRequest($request);

    // NOTE: This test is against $viewer (not $user), so that the error
    // message below makes sense in the case that the two are different,
    // and because an admin reusing their own password is bad, while
    // system agents generally do not have passwords anyway.

    $engine = id(new PhabricatorAuthPasswordEngine())
      ->setViewer($viewer)
      ->setContentSource($content_source)
      ->setObject($viewer)
      ->setPasswordType($vcs_type);

    if ($request->isFormPost()) {
      if ($request->getBool('remove')) {
        if ($vcspassword->getID()) {
          $vcspassword->delete();
          return id(new AphrontRedirectResponse())->setURI($panel_uri);
        }
      }

      $new_password = $request->getStr('password');
      $confirm = $request->getStr('confirm');

      $envelope = new PhutilOpaqueEnvelope($new_password);
      $confirm_envelope = new PhutilOpaqueEnvelope($confirm);

      try {
        $engine->checkNewPassword($envelope, $confirm_envelope);
        $e_password = null;
        $e_confirm = null;
      } catch (PhabricatorAuthPasswordException $ex) {
        $errors[] = $ex->getMessage();
        $e_password = $ex->getPasswordError();
        $e_confirm = $ex->getConfirmError();
      }

      if (!$errors) {
        $vcspassword
          ->setPassword($envelope, $user)
          ->save();

        return id(new AphrontRedirectResponse())->setURI($panel_uri);
      }
    }

    $title = pht('Set VCS Password');

    $form = id(new AphrontFormView())
      ->setUser($viewer)
      ->appendRemarkupInstructions(
        pht(
          'To access repositories hosted by Phabricator over HTTP, you must '.
          'set a version control password. This password should be unique.'.
          "\n\n".
          "This password applies to all repositories available over ".
          "HTTP."));

    if ($vcspassword->getID()) {
      $form
        ->appendChild(
          id(new AphrontFormPasswordControl())
            ->setDisableAutocomplete(true)
            ->setLabel(pht('Current Password'))
            ->setDisabled(true)
            ->setValue('********************'));
    } else {
      $form
        ->appendChild(
          id(new AphrontFormMarkupControl())
            ->setLabel(pht('Current Password'))
            ->setValue(phutil_tag('em', array(), pht('No Password Set'))));
    }

    $form
      ->appendChild(
        id(new AphrontFormPasswordControl())
          ->setDisableAutocomplete(true)
          ->setName('password')
          ->setLabel(pht('New VCS Password'))
          ->setError($e_password))
      ->appendChild(
        id(new AphrontFormPasswordControl())
          ->setDisableAutocomplete(true)
          ->setName('confirm')
          ->setLabel(pht('Confirm VCS Password'))
          ->setError($e_confirm))
      ->appendChild(
        id(new AphrontFormSubmitControl())
          ->setValue(pht('Change Password')));


    if (!$vcspassword->getID()) {
      $is_serious = PhabricatorEnv::getEnvConfig(
        'phabricator.serious-business');

      $suggest = Filesystem::readRandomBytes(128);
      $suggest = preg_replace('([^A-Za-z0-9/!().,;{}^&*%~])', '', $suggest);
      $suggest = substr($suggest, 0, 20);

      if ($is_serious) {
        $form->appendRemarkupInstructions(
          pht(
            'Having trouble coming up with a good password? Try this randomly '.
            'generated one, made by a computer:'.
            "\n\n".
            "`%s`",
            $suggest));
      } else {
        $form->appendRemarkupInstructions(
          pht(
            'Having trouble coming up with a good password? Try this '.
            'artisanal password, hand made in small batches by our expert '.
            'craftspeople: '.
            "\n\n".
            "`%s`",
            $suggest));
      }
    }

    $hash_envelope = new PhutilOpaqueEnvelope($vcspassword->getPasswordHash());

    $form->appendChild(
      id(new AphrontFormStaticControl())
        ->setLabel(pht('Current Algorithm'))
        ->setValue(
          PhabricatorPasswordHasher::getCurrentAlgorithmName($hash_envelope)));

    $form->appendChild(
      id(new AphrontFormStaticControl())
        ->setLabel(pht('Best Available Algorithm'))
        ->setValue(PhabricatorPasswordHasher::getBestAlgorithmName()));

    if (strlen($hash_envelope->openEnvelope())) {
      try {
        $can_upgrade = PhabricatorPasswordHasher::canUpgradeHash(
          $hash_envelope);
      } catch (PhabricatorPasswordHasherUnavailableException $ex) {
        $can_upgrade = false;
        $errors[] = pht(
          'Your VCS password is currently hashed using an algorithm which is '.
          'no longer available on this install.');
        $errors[] = pht(
          'Because the algorithm implementation is missing, your password '.
          'can not be used.');
        $errors[] = pht(
          'You can set a new password to replace the old password.');
      }

      if ($can_upgrade) {
        $errors[] = pht(
          'The strength of your stored VCS password hash can be upgraded. '.
          'To upgrade, either: use the password to authenticate with a '.
          'repository; or change your password.');
      }
    }

    $object_box = id(new PHUIObjectBoxView())
      ->setHeaderText($title)
      ->setBackground(PHUIObjectBoxView::WHITE_CONFIG)
      ->setForm($form)
      ->setFormErrors($errors);

    $remove_form = id(new AphrontFormView())
      ->setUser($viewer);

    if ($vcspassword->getID()) {
      $remove_form
        ->addHiddenInput('remove', true)
        ->appendRemarkupInstructions(
          pht(
            'You can remove your VCS password, which will prevent your '.
            'account from accessing repositories.'))
        ->appendChild(
          id(new AphrontFormSubmitControl())
            ->setValue(pht('Remove Password')));
    } else {
      $remove_form->appendRemarkupInstructions(
        pht(
          'You do not currently have a VCS password set. If you set one, you '.
          'can remove it here later.'));
    }

    $remove_box = id(new PHUIObjectBoxView())
      ->setHeaderText(pht('Remove VCS Password'))
      ->setBackground(PHUIObjectBoxView::WHITE_CONFIG)
      ->setForm($remove_form);

    $saved = null;
    if ($request->getBool('saved')) {
      $saved = id(new PHUIInfoView())
        ->setSeverity(PHUIInfoView::SEVERITY_NOTICE)
        ->setTitle(pht('Password Updated'))
        ->appendChild(pht('Your VCS password has been updated.'));
    }

    return array(
      $saved,
      $object_box,
      $remove_box,
    );
  }

}
