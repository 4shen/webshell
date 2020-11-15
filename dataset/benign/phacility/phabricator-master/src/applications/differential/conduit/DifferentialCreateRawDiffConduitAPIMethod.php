<?php

final class DifferentialCreateRawDiffConduitAPIMethod
  extends DifferentialConduitAPIMethod {

  public function getAPIMethodName() {
    return 'differential.createrawdiff';
  }

  public function getMethodDescription() {
    return pht('Create a new Differential diff from a raw diff source.');
  }

  protected function defineParamTypes() {
    return array(
      'diff' => 'required string',
      'repositoryPHID' => 'optional string',
      'viewPolicy' => 'optional string',
    );
  }

  protected function defineReturnType() {
    return 'nonempty dict';
  }

  protected function execute(ConduitAPIRequest $request) {
    $viewer = $request->getUser();
    $raw_diff = $request->getValue('diff');

    $repository_phid = $request->getValue('repositoryPHID');
    if ($repository_phid) {
      $repository = id(new PhabricatorRepositoryQuery())
        ->setViewer($viewer)
        ->withPHIDs(array($repository_phid))
        ->executeOne();
      if (!$repository) {
        throw new Exception(
          pht('No such repository "%s"!', $repository_phid));
      }
    }

    $parser = new ArcanistDiffParser();
    $changes = $parser->parseDiff($raw_diff);
    $diff = DifferentialDiff::newFromRawChanges($viewer, $changes);

    // We're bounded by doing INSERTs for all the hunks and changesets, so
    // estimate the number of inserts we'll require.
    $size = 0;
    foreach ($diff->getChangesets() as $changeset) {
      $hunks = $changeset->getHunks();
      $size += 1 + count($hunks);
    }

    $raw_limit = 10000;
    if ($size > $raw_limit) {
      throw new Exception(
        pht(
          'The raw diff you have submitted is too large to parse (it affects '.
          'more than %s paths and hunks).',
          new PhutilNumber($raw_limit)));
    }

    $diff_data_dict = array(
      'creationMethod' => 'web',
      'authorPHID' => $viewer->getPHID(),
      'repositoryPHID' => $repository_phid,
      'lintStatus' => DifferentialLintStatus::LINT_SKIP,
      'unitStatus' => DifferentialUnitStatus::UNIT_SKIP,
    );

    $xactions = array(
      id(new DifferentialDiffTransaction())
        ->setTransactionType(DifferentialDiffTransaction::TYPE_DIFF_CREATE)
        ->setNewValue($diff_data_dict),
    );

    if ($request->getValue('viewPolicy')) {
      $xactions[] = id(new DifferentialDiffTransaction())
        ->setTransactionType(PhabricatorTransactions::TYPE_VIEW_POLICY)
        ->setNewValue($request->getValue('viewPolicy'));
    }

    id(new DifferentialDiffEditor())
      ->setActor($viewer)
      ->setContentSource($request->newContentSource())
      ->setContinueOnNoEffect(true)
      ->setLookupRepository(false) // respect user choice
      ->applyTransactions($diff, $xactions);

    return $this->buildDiffInfoDictionary($diff);
  }

}
