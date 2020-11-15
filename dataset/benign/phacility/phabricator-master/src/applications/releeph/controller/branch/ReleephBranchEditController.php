<?php

final class ReleephBranchEditController extends ReleephBranchController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();
    $id = $request->getURIData('branchID');

    $branch = id(new ReleephBranchQuery())
      ->setViewer($viewer)
      ->requireCapabilities(
        array(
          PhabricatorPolicyCapability::CAN_VIEW,
          PhabricatorPolicyCapability::CAN_EDIT,
        ))
      ->withIDs(array($id))
      ->executeOne();
    if (!$branch) {
      return new Aphront404Response();
    }
    $this->setBranch($branch);

    $symbolic_name = $request->getStr(
      'symbolicName',
      $branch->getSymbolicName());

    if ($request->isFormPost()) {
      $existing_with_same_symbolic_name =
        id(new ReleephBranch())
          ->loadOneWhere(
              'id != %d AND releephProjectID = %d AND symbolicName = %s',
              $branch->getID(),
              $branch->getReleephProjectID(),
              $symbolic_name);

      $branch->openTransaction();
      $branch->setSymbolicName($symbolic_name);

      if ($existing_with_same_symbolic_name) {
        $existing_with_same_symbolic_name
          ->setSymbolicName(null)
          ->save();
      }

      $branch->save();
      $branch->saveTransaction();

      return id(new AphrontRedirectResponse())
        ->setURI($this->getBranchViewURI($branch));
    }

    $phids = array();

    $phids[] = $creator_phid = $branch->getCreatedByUserPHID();
    $phids[] = $cut_commit_phid = $branch->getCutPointCommitPHID();

    $handles = id(new PhabricatorHandleQuery())
      ->setViewer($request->getUser())
      ->withPHIDs($phids)
      ->execute();

    $form = id(new AphrontFormView())
      ->setUser($request->getUser())
      ->appendChild(
        id(new AphrontFormStaticControl())
        ->setLabel(pht('Branch Name'))
        ->setValue($branch->getName()))
      ->appendChild(
        id(new AphrontFormMarkupControl())
          ->setLabel(pht('Cut Point'))
          ->setValue($handles[$cut_commit_phid]->renderLink()))
      ->appendChild(
        id(new AphrontFormMarkupControl())
          ->setLabel(pht('Created By'))
          ->setValue($handles[$creator_phid]->renderLink()))
      ->appendChild(
        id(new AphrontFormTextControl())
          ->setLabel(pht('Symbolic Name'))
          ->setName('symbolicName')
          ->setValue($symbolic_name)
          ->setCaption(pht(
            'Mutable alternate name, for easy reference, (e.g. "LATEST")')))
      ->appendChild(
        id(new AphrontFormSubmitControl())
          ->addCancelButton($this->getBranchViewURI($branch))
          ->setValue(pht('Save Branch')));

    $title = pht(
      'Edit Branch: %s',
      $branch->getDisplayNameWithDetail());

    $box = id(new PHUIObjectBoxView())
      ->setHeaderText(pht('Branch'))
      ->setBackground(PHUIObjectBoxView::BLUE_PROPERTY)
      ->appendChild($form);

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->addTextCrumb(pht('Edit'));
    $crumbs->setBorder(true);

    $header = id(new PHUIHeaderView())
      ->setHeader(pht('Edit Branch'))
      ->setHeaderIcon('fa-pencil');

    $view = id(new PHUITwoColumnView())
      ->setHeader($header)
      ->setFooter($box);

    return $this->newPage()
      ->setTitle($title)
      ->setCrumbs($crumbs)
      ->appendChild($view);
  }
}
