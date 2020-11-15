<?php

final class PassphraseCredentialEditController extends PassphraseController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();
    $id = $request->getURIData('id');

    if ($id) {
      $credential = id(new PassphraseCredentialQuery())
        ->setViewer($viewer)
        ->withIDs(array($id))
        ->requireCapabilities(
          array(
            PhabricatorPolicyCapability::CAN_VIEW,
            PhabricatorPolicyCapability::CAN_EDIT,
          ))
        ->executeOne();
      if (!$credential) {
        return new Aphront404Response();
      }

      $type = $this->getCredentialType($credential->getCredentialType());
      $type_const = $type->getCredentialType();

      $is_new = false;
    } else {
      $type_const = $request->getStr('type');
      $type = $this->getCredentialType($type_const);

      if (!$type->isCreateable()) {
        throw new Exception(
          pht(
            'Credential has noncreateable type "%s"!',
            $type_const));
      }

      $credential = PassphraseCredential::initializeNewCredential($viewer)
        ->setCredentialType($type->getCredentialType())
        ->setProvidesType($type->getProvidesType())
        ->attachImplementation($type);

      $is_new = true;

      // Prefill username if provided.
      $credential->setUsername((string)$request->getStr('username'));

      if (!$request->getStr('isInitialized')) {
        $type->didInitializeNewCredential($viewer, $credential);
      }
    }

    $errors = array();

    $v_name = $credential->getName();
    $e_name = true;

    $v_desc = $credential->getDescription();
    $v_space = $credential->getSpacePHID();

    $v_username = $credential->getUsername();
    $e_username = true;

    $v_is_locked = false;

    $bullet = "\xE2\x80\xA2";

    $v_secret = $credential->getSecretID() ? str_repeat($bullet, 32) : null;
    if ($is_new && ($v_secret === null)) {
      // If we're creating a new credential, the credential type may have
      // populated the secret for us (for example, generated an SSH key). In
      // this case,
      try {
        $v_secret = $credential->getSecret()->openEnvelope();
      } catch (Exception $ex) {
        // Ignore this.
      }
    }

    $validation_exception = null;
    $errors = array();
    $e_password = null;
    $e_secret = null;
    if ($request->isFormPost()) {

      $v_name = $request->getStr('name');
      $v_desc = $request->getStr('description');
      $v_username = $request->getStr('username');
      $v_view_policy = $request->getStr('viewPolicy');
      $v_edit_policy = $request->getStr('editPolicy');
      $v_is_locked = $request->getStr('lock');

      $v_secret = $request->getStr('secret');
      $v_space = $request->getStr('spacePHID');
      $v_password = $request->getStr('password');
      $v_decrypt = $v_secret;

      $env_secret = new PhutilOpaqueEnvelope($v_secret);
      $env_password = new PhutilOpaqueEnvelope($v_password);

      $has_secret = !preg_match('/^('.$bullet.')+$/', trim($v_decrypt));

      // Validate and repair SSH private keys, and apply passwords if they
      // are provided. See T13454 for discussion.

      // This should eventually be refactored to be modular rather than a
      // hard-coded set of behaviors here in the Controller, but this is
      // likely a fairly extensive change.

      $is_ssh = ($type instanceof PassphraseSSHPrivateKeyTextCredentialType);

      if ($is_ssh && $has_secret) {
        $old_object = PhabricatorAuthSSHPrivateKey::newFromRawKey($env_secret);

        if (strlen($v_password)) {
          $old_object->setPassphrase($env_password);
        }

        try {
          $new_object = $old_object->newBarePrivateKey();
          $v_decrypt = $new_object->getKeyBody()->openEnvelope();
        } catch (PhabricatorAuthSSHPrivateKeyException $ex) {
          $errors[] = $ex->getMessage();

          if ($ex->isFormatException()) {
            $e_secret = pht('Invalid');
          }
          if ($ex->isPassphraseException()) {
            $e_password = pht('Invalid');
          }
        }
      }

      if (!$errors) {
        $type_name =
          PassphraseCredentialNameTransaction::TRANSACTIONTYPE;
        $type_desc =
          PassphraseCredentialDescriptionTransaction::TRANSACTIONTYPE;
        $type_username =
          PassphraseCredentialUsernameTransaction::TRANSACTIONTYPE;
        $type_destroy =
          PassphraseCredentialDestroyTransaction::TRANSACTIONTYPE;
        $type_secret_id =
          PassphraseCredentialSecretIDTransaction::TRANSACTIONTYPE;
        $type_is_locked =
          PassphraseCredentialLockTransaction::TRANSACTIONTYPE;

        $type_view_policy = PhabricatorTransactions::TYPE_VIEW_POLICY;
        $type_edit_policy = PhabricatorTransactions::TYPE_EDIT_POLICY;
        $type_space = PhabricatorTransactions::TYPE_SPACE;

        $xactions = array();

        $xactions[] = id(new PassphraseCredentialTransaction())
          ->setTransactionType($type_name)
          ->setNewValue($v_name);

        $xactions[] = id(new PassphraseCredentialTransaction())
          ->setTransactionType($type_desc)
          ->setNewValue($v_desc);

        $xactions[] = id(new PassphraseCredentialTransaction())
          ->setTransactionType($type_view_policy)
          ->setNewValue($v_view_policy);

        $xactions[] = id(new PassphraseCredentialTransaction())
          ->setTransactionType($type_edit_policy)
          ->setNewValue($v_edit_policy);

        $xactions[] = id(new PassphraseCredentialTransaction())
          ->setTransactionType($type_space)
          ->setNewValue($v_space);

        // Open a transaction in case we're writing a new secret; this limits
        // the amount of code which handles secret plaintexts.
        $credential->openTransaction();

        if (!$credential->getIsLocked()) {
          if ($type->shouldRequireUsername()) {
            $xactions[] = id(new PassphraseCredentialTransaction())
            ->setTransactionType($type_username)
            ->setNewValue($v_username);
          }

          // If some value other than a sequence of bullets was provided for
          // the credential, update it. In particular, note that we are
          // explicitly allowing empty secrets: one use case is HTTP auth where
          // the username is a secret token which covers both identity and
          // authentication.

          if ($has_secret) {
            // If the credential was previously destroyed, restore it when it is
            // edited if a secret is provided.
            $xactions[] = id(new PassphraseCredentialTransaction())
              ->setTransactionType($type_destroy)
              ->setNewValue(0);

            $new_secret = id(new PassphraseSecret())
              ->setSecretData($v_decrypt)
              ->save();

            $xactions[] = id(new PassphraseCredentialTransaction())
              ->setTransactionType($type_secret_id)
              ->setNewValue($new_secret->getID());
          }

          $xactions[] = id(new PassphraseCredentialTransaction())
            ->setTransactionType($type_is_locked)
            ->setNewValue($v_is_locked);
        }

        try {
          $editor = id(new PassphraseCredentialTransactionEditor())
            ->setActor($viewer)
            ->setContinueOnNoEffect(true)
            ->setContentSourceFromRequest($request)
            ->applyTransactions($credential, $xactions);

          $credential->saveTransaction();

          if ($request->isAjax()) {
            return id(new AphrontAjaxResponse())->setContent(
              array(
                'phid' => $credential->getPHID(),
                'name' => 'K'.$credential->getID().' '.$credential->getName(),
              ));
          } else {
            return id(new AphrontRedirectResponse())
              ->setURI('/K'.$credential->getID());
          }
        } catch (PhabricatorApplicationTransactionValidationException $ex) {
          $credential->killTransaction();

          $validation_exception = $ex;

          $e_name = $ex->getShortMessage($type_name);
          $e_username = $ex->getShortMessage($type_username);

          $credential->setViewPolicy($v_view_policy);
          $credential->setEditPolicy($v_edit_policy);
        }
      }
    }

    $policies = id(new PhabricatorPolicyQuery())
      ->setViewer($viewer)
      ->setObject($credential)
      ->execute();

    $secret_control = $type->newSecretControl();
    $credential_is_locked = $credential->getIsLocked();

    $form = id(new AphrontFormView())
      ->setUser($viewer)
      ->addHiddenInput('isInitialized', true)
      ->addHiddenInput('type', $type_const)
      ->appendChild(
        id(new AphrontFormTextControl())
          ->setName('name')
          ->setLabel(pht('Name'))
          ->setValue($v_name)
          ->setError($e_name))
      ->appendChild(
        id(new PhabricatorRemarkupControl())
          ->setUser($viewer)
          ->setName('description')
          ->setLabel(pht('Description'))
          ->setValue($v_desc))
      ->appendChild(
        id(new AphrontFormDividerControl()))
      ->appendControl(
        id(new AphrontFormPolicyControl())
          ->setName('viewPolicy')
          ->setPolicyObject($credential)
          ->setSpacePHID($v_space)
          ->setCapability(PhabricatorPolicyCapability::CAN_VIEW)
          ->setPolicies($policies))
      ->appendControl(
        id(new AphrontFormPolicyControl())
          ->setName('editPolicy')
          ->setPolicyObject($credential)
          ->setCapability(PhabricatorPolicyCapability::CAN_EDIT)
          ->setPolicies($policies))
      ->appendChild(
        id(new AphrontFormDividerControl()));

    if ($credential_is_locked) {
      $form->appendRemarkupInstructions(
        pht('This credential is permanently locked and can not be edited.'));
    }

    if ($type->shouldRequireUsername()) {
      $form->appendChild(
        id(new AphrontFormTextControl())
          ->setName('username')
          ->setLabel(pht('Login/Username'))
          ->setValue($v_username)
          ->setDisabled($credential_is_locked)
          ->setError($e_username));
    }

    $form->appendChild(
      $secret_control
        ->setName('secret')
        ->setLabel($type->getSecretLabel())
        ->setDisabled($credential_is_locked)
        ->setValue($v_secret)
        ->setError($e_secret));

    if ($type->shouldShowPasswordField()) {
      $form->appendChild(
        id(new AphrontFormPasswordControl())
          ->setDisableAutocomplete(true)
          ->setName('password')
          ->setLabel($type->getPasswordLabel())
          ->setDisabled($credential_is_locked)
          ->setError($e_password));
    }

    if ($is_new) {
      $form->appendChild(
        id(new AphrontFormCheckboxControl())
          ->addCheckbox(
            'lock',
            1,
            array(
              phutil_tag('strong', array(), pht('Lock Permanently:')),
              ' ',
              pht('Prevent the secret from being revealed or changed.'),
            ),
            $v_is_locked)
          ->setDisabled($credential_is_locked));
    }

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->setBorder(true);

    if ($is_new) {
      $title = pht('New Credential: %s', $type->getCredentialTypeName());
      $crumbs->addTextCrumb(pht('Create'));
      $cancel_uri = $this->getApplicationURI();
    } else {
      $title = pht('Edit Credential: %s', $credential->getName());
      $crumbs->addTextCrumb(
        'K'.$credential->getID(),
        '/K'.$credential->getID());
      $crumbs->addTextCrumb(pht('Edit'));
      $cancel_uri = '/K'.$credential->getID();
    }

    if ($request->isAjax()) {
      if ($errors) {
        $errors = id(new PHUIInfoView())->setErrors($errors);
      }

      return $this->newDialog()
        ->setWidth(AphrontDialogView::WIDTH_FORM)
        ->setTitle($title)
        ->appendChild($errors)
        ->appendChild($form->buildLayoutView())
        ->addSubmitButton(pht('Create Credential'))
        ->addCancelButton($cancel_uri);
    }

    $form->appendChild(
      id(new AphrontFormSubmitControl())
        ->setValue(pht('Save'))
        ->addCancelButton($cancel_uri));

    $box = id(new PHUIObjectBoxView())
      ->setHeaderText($title)
      ->setFormErrors($errors)
      ->setValidationException($validation_exception)
      ->setBackground(PHUIObjectBoxView::WHITE_CONFIG)
      ->setForm($form);

    $view = id(new PHUITwoColumnView())
      ->setFooter(array(
        $box,
      ));

    return $this->newPage()
      ->setTitle($title)
      ->setCrumbs($crumbs)
      ->appendChild($view);
  }

  private function getCredentialType($type_const) {
    $type = PassphraseCredentialType::getTypeByConstant($type_const);

    if (!$type) {
      throw new Exception(
        pht('Credential has invalid type "%s"!', $type_const));
    }

    return $type;
  }

}
