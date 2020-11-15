<?php

final class HarbormasterLintMessagesController
  extends HarbormasterController {

  public function shouldAllowPublic() {
    return true;
  }

  public function handleRequest(AphrontRequest $request) {
    $viewer = $this->getViewer();

    $buildable = id(new HarbormasterBuildableQuery())
      ->setViewer($viewer)
      ->withIDs(array($request->getURIData('id')))
      ->needBuilds(true)
      ->needTargets(true)
      ->executeOne();
    if (!$buildable) {
      return new Aphront404Response();
    }

    $id = $buildable->getID();

    $target_phids = array();
    foreach ($buildable->getBuilds() as $build) {
      foreach ($build->getBuildTargets() as $target) {
        $target_phids[] = $target->getPHID();
      }
    }

    $lint_data = array();
    if ($target_phids) {
      $lint_data = id(new HarbormasterBuildLintMessage())->loadAllWhere(
        'buildTargetPHID IN (%Ls)',
        $target_phids);
    } else {
      $lint_data = array();
    }

    $lint_table = id(new HarbormasterLintPropertyView())
      ->setUser($viewer)
      ->setLintMessages($lint_data);

    $lint = id(new PHUIObjectBoxView())
      ->setHeaderText(pht('Lint Messages'))
      ->appendChild($lint_table);

    $crumbs = $this->buildApplicationCrumbs();
    $this->addBuildableCrumb($crumbs, $buildable);
    $crumbs->addTextCrumb(pht('Lint'));
    $crumbs->setBorder(true);

    $title = array(
      $buildable->getMonogram(),
      pht('Lint'),
    );

    $header = id(new PHUIHeaderView())
      ->setHeader($title);

    $view = id(new PHUITwoColumnView())
      ->setHeader($header)
      ->setFooter(array(
        $lint,
      ));

    return $this->newPage()
      ->setTitle($title)
      ->setCrumbs($crumbs)
      ->appendChild($view);

  }

}
