<?php

final class PhabricatorOwnersPathsController
  extends PhabricatorOwnersController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getUser();

    $package = id(new PhabricatorOwnersPackageQuery())
      ->setViewer($viewer)
      ->withIDs(array($request->getURIData('id')))
      ->requireCapabilities(
        array(
          PhabricatorPolicyCapability::CAN_VIEW,
          PhabricatorPolicyCapability::CAN_EDIT,
        ))
      ->needPaths(true)
      ->executeOne();
    if (!$package) {
      return new Aphront404Response();
    }

    if ($request->isFormPost()) {
      $paths = $request->getArr('path');
      $repos = $request->getArr('repo');
      $excludes = $request->getArr('exclude');

      $path_refs = array();
      foreach ($paths as $key => $path) {
        if (!isset($repos[$key]) || !strlen($repos[$key])) {
          throw new Exception(
            pht(
              'No repository PHID for path "%s"!',
              $key));
        }

        if (!isset($excludes[$key])) {
          throw new Exception(
            pht(
              'No exclusion value for path "%s"!',
              $key));
        }

        $path_refs[] = array(
          'repositoryPHID' => $repos[$key],
          'path' => $path,
          'excluded' => (int)$excludes[$key],
        );
      }

      $type_paths = PhabricatorOwnersPackagePathsTransaction::TRANSACTIONTYPE;

      $xactions = array();
      $xactions[] = id(new PhabricatorOwnersPackageTransaction())
        ->setTransactionType($type_paths)
        ->setNewValue($path_refs);

      $editor = id(new PhabricatorOwnersPackageTransactionEditor())
        ->setActor($viewer)
        ->setContentSourceFromRequest($request)
        ->setContinueOnNoEffect(true)
        ->setContinueOnMissingFields(true);

      $editor->applyTransactions($package, $xactions);

      return id(new AphrontRedirectResponse())
        ->setURI($package->getURI());
    } else {
      $paths = $package->getPaths();
      $path_refs = mpull($paths, 'getRef');
    }

    $template = new AphrontTokenizerTemplateView();

    $datasource = id(new DiffusionRepositoryDatasource())
      ->setViewer($viewer);

    $tokenizer_spec = array(
      'markup' => $template->render(),
      'config' => array(
        'src' => $datasource->getDatasourceURI(),
        'browseURI' => $datasource->getBrowseURI(),
        'placeholder' => $datasource->getPlaceholderText(),
        'limit' => 1,
      ),
    );

    foreach ($path_refs as $key => $path_ref) {
      $path_refs[$key]['repositoryValue'] = $datasource->getWireTokens(
        array(
          $path_ref['repositoryPHID'],
        ));
    }

    $icon_test = id(new PHUIIconView())
      ->setIcon('fa-spinner grey')
      ->setTooltip(pht('Validating...'));

    $icon_okay = id(new PHUIIconView())
      ->setIcon('fa-check-circle green')
      ->setTooltip(pht('Path Exists in Repository'));

    $icon_fail = id(new PHUIIconView())
      ->setIcon('fa-question-circle-o red')
      ->setTooltip(pht('Path Not Found On Default Branch'));

    $template = new AphrontTypeaheadTemplateView();
    $template = $template->render();

    Javelin::initBehavior(
      'owners-path-editor',
      array(
        'root' => 'path-editor',
        'table' => 'paths',
        'add_button' => 'addpath',
        'input_template' => $template,
        'pathRefs' => $path_refs,
        'completeURI' => '/diffusion/services/path/complete/',
        'validateURI' => '/diffusion/services/path/validate/',
        'repositoryTokenizerSpec' => $tokenizer_spec,
        'icons' => array(
          'test' => hsprintf('%s', $icon_test),
          'okay' => hsprintf('%s', $icon_okay),
          'fail' => hsprintf('%s', $icon_fail),
        ),
        'modeOptions' => array(
          0 => pht('Include'),
          1 => pht('Exclude'),
        ),
      ));

    require_celerity_resource('owners-path-editor-css');

    $cancel_uri = $package->getURI();

    $form = id(new AphrontFormView())
      ->setUser($viewer)
      ->appendChild(
        id(new PHUIFormInsetView())
          ->setTitle(pht('Paths'))
          ->addDivAttributes(array('id' => 'path-editor'))
          ->setRightButton(javelin_tag(
              'a',
              array(
                'href' => '#',
                'class' => 'button button-green',
                'sigil' => 'addpath',
                'mustcapture' => true,
              ),
              pht('Add New Path')))
          ->setDescription(
            pht(
              'Specify the files and directories which comprise '.
              'this package.'))
          ->setContent(javelin_tag(
              'table',
              array(
                'class' => 'owners-path-editor-table',
                'sigil' => 'paths',
              ),
              '')))
      ->appendChild(
        id(new AphrontFormSubmitControl())
          ->addCancelButton($cancel_uri)
          ->setValue(pht('Save Paths')));

    $box = id(new PHUIObjectBoxView())
      ->setHeaderText(pht('Paths'))
      ->setBackground(PHUIObjectBoxView::BLUE_PROPERTY)
      ->setForm($form);

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->addTextCrumb(
      $package->getName(),
      $this->getApplicationURI('package/'.$package->getID().'/'));
    $crumbs->addTextCrumb(pht('Edit Paths'));
    $crumbs->setBorder(true);

    $header = id(new PHUIHeaderView())
      ->setHeader(pht('Edit Paths: %s', $package->getName()))
      ->setHeaderIcon('fa-pencil');

    $view = id(new PHUITwoColumnView())
      ->setHeader($header)
      ->setFooter($box);

    $title = array($package->getName(), pht('Edit Paths'));

    return $this->newPage()
      ->setTitle($title)
      ->setCrumbs($crumbs)
      ->appendChild($view);

      }

}
