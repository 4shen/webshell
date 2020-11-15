<?php

final class PhabricatorProjectMembersAddController
  extends PhabricatorProjectController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();
    $id = $request->getURIData('id');

    $project = id(new PhabricatorProjectQuery())
      ->setViewer($viewer)
      ->withIDs(array($id))
      ->requireCapabilities(
        array(
          PhabricatorPolicyCapability::CAN_VIEW,
          PhabricatorPolicyCapability::CAN_EDIT,
        ))
      ->executeOne();
    if (!$project) {
      return new Aphront404Response();
    }

    $this->setProject($project);
    $done_uri = "/project/members/{$id}/";

    if (!$project->supportsEditMembers()) {
      $copy = pht('Parent projects and milestones do not support adding '.
        'members. You can add members directly to any non-parent subproject.');

      return $this->newDialog()
        ->setTitle(pht('Unsupported Project'))
        ->appendParagraph($copy)
        ->addCancelButton($done_uri);
    }

    if ($request->isFormPost()) {
      $member_phids = $request->getArr('memberPHIDs');

      $type_member = PhabricatorProjectProjectHasMemberEdgeType::EDGECONST;

      $xactions = array();

      $xactions[] = id(new PhabricatorProjectTransaction())
        ->setTransactionType(PhabricatorTransactions::TYPE_EDGE)
        ->setMetadataValue('edge:type', $type_member)
        ->setNewValue(
          array(
            '+' => array_fuse($member_phids),
          ));

      $editor = id(new PhabricatorProjectTransactionEditor())
        ->setActor($viewer)
        ->setContentSourceFromRequest($request)
        ->setContinueOnNoEffect(true)
        ->setContinueOnMissingFields(true)
        ->applyTransactions($project, $xactions);

      return id(new AphrontRedirectResponse())
        ->setURI($done_uri);
    }

    $form = id(new AphrontFormView())
      ->setUser($viewer)
      ->appendControl(
        id(new AphrontFormTokenizerControl())
          ->setName('memberPHIDs')
          ->setLabel(pht('Members'))
          ->setDatasource(new PhabricatorPeopleDatasource()));

    return $this->newDialog()
      ->setTitle(pht('Add Members'))
      ->appendForm($form)
      ->addCancelButton($done_uri)
      ->addSubmitButton(pht('Add Members'));
  }

}
