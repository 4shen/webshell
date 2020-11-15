<?php

final class PhabricatorFileTestCase extends PhabricatorTestCase {

  protected function getPhabricatorTestCaseConfiguration() {
    return array(
      self::PHABRICATOR_TESTCONFIG_BUILD_STORAGE_FIXTURES => true,
    );
  }

  public function testFileDirectScramble() {
    // Changes to a file's view policy should scramble the file secret.

    $engine = new PhabricatorTestStorageEngine();
    $data = Filesystem::readRandomCharacters(64);

    $author = $this->generateNewTestUser();

    $params = array(
      'name' => 'test.dat',
      'viewPolicy' => PhabricatorPolicies::POLICY_USER,
      'authorPHID' => $author->getPHID(),
      'storageEngines' => array(
        $engine,
      ),
    );

    $file = PhabricatorFile::newFromFileData($data, $params);

    $secret1 = $file->getSecretKey();

    // First, change the name: this should not scramble the secret.
    $xactions = array();
    $xactions[] = id(new PhabricatorFileTransaction())
      ->setTransactionType(PhabricatorFileNameTransaction::TRANSACTIONTYPE)
      ->setNewValue('test.dat2');

    $engine = id(new PhabricatorFileEditor())
      ->setActor($author)
      ->setContentSource($this->newContentSource())
      ->applyTransactions($file, $xactions);

    $file = $file->reload();

    $secret2 = $file->getSecretKey();

    $this->assertEqual(
      $secret1,
      $secret2,
      pht('No secret scramble on non-policy edit.'));

    // Now, change the view policy. This should scramble the secret.
    $xactions = array();
    $xactions[] = id(new PhabricatorFileTransaction())
      ->setTransactionType(PhabricatorTransactions::TYPE_VIEW_POLICY)
      ->setNewValue($author->getPHID());

    $engine = id(new PhabricatorFileEditor())
      ->setActor($author)
      ->setContentSource($this->newContentSource())
      ->applyTransactions($file, $xactions);

    $file = $file->reload();
    $secret3 = $file->getSecretKey();

    $this->assertTrue(
      ($secret1 !== $secret3),
      pht('Changing file view policy should scramble secret.'));
  }

  public function testFileIndirectScramble() {
    // When a file is attached to an object like a task and the task view
    // policy changes, the file secret should be scrambled. This invalidates
    // old URIs if tasks get locked down.

    $engine = new PhabricatorTestStorageEngine();
    $data = Filesystem::readRandomCharacters(64);

    $author = $this->generateNewTestUser();

    $params = array(
      'name' => 'test.dat',
      'viewPolicy' => $author->getPHID(),
      'authorPHID' => $author->getPHID(),
      'storageEngines' => array(
        $engine,
      ),
    );

    $file = PhabricatorFile::newFromFileData($data, $params);
    $secret1 = $file->getSecretKey();

    $task = ManiphestTask::initializeNewTask($author);

    $xactions = array();
    $xactions[] = id(new ManiphestTransaction())
      ->setTransactionType(ManiphestTaskTitleTransaction::TRANSACTIONTYPE)
      ->setNewValue(pht('File Scramble Test Task'));

    $xactions[] = id(new ManiphestTransaction())
      ->setTransactionType(
        ManiphestTaskDescriptionTransaction::TRANSACTIONTYPE)
      ->setNewValue('{'.$file->getMonogram().'}');

    id(new ManiphestTransactionEditor())
      ->setActor($author)
      ->setContentSource($this->newContentSource())
      ->applyTransactions($task, $xactions);

    $file = $file->reload();
    $secret2 = $file->getSecretKey();

    $this->assertEqual(
      $secret1,
      $secret2,
      pht(
        'File policy should not scramble when attached to '.
        'newly created object.'));

    $xactions = array();
    $xactions[] = id(new ManiphestTransaction())
      ->setTransactionType(PhabricatorTransactions::TYPE_VIEW_POLICY)
      ->setNewValue($author->getPHID());

    id(new ManiphestTransactionEditor())
      ->setActor($author)
      ->setContentSource($this->newContentSource())
      ->applyTransactions($task, $xactions);

    $file = $file->reload();
    $secret3 = $file->getSecretKey();

    $this->assertTrue(
      ($secret1 !== $secret3),
      pht('Changing attached object view policy should scramble secret.'));
  }


  public function testFileVisibility() {
    $engine = new PhabricatorTestStorageEngine();
    $data = Filesystem::readRandomCharacters(64);

    $author = $this->generateNewTestUser();
    $viewer = $this->generateNewTestUser();
    $users = array($author, $viewer);

    $params = array(
      'name' => 'test.dat',
      'viewPolicy' => PhabricatorPolicies::POLICY_NOONE,
      'authorPHID' => $author->getPHID(),
      'storageEngines' => array(
        $engine,
      ),
    );

    $file = PhabricatorFile::newFromFileData($data, $params);
    $filter = new PhabricatorPolicyFilter();

    // Test bare file policies.
    $this->assertEqual(
      array(
        true,
        false,
      ),
      $this->canViewFile($users, $file),
      pht('File Visibility'));

    // Create an object and test object policies.

    $object = ManiphestTask::initializeNewTask($author);
    $object->setViewPolicy(PhabricatorPolicies::getMostOpenPolicy());
    $object->save();

    $this->assertTrue(
      $filter->hasCapability(
        $author,
        $object,
        PhabricatorPolicyCapability::CAN_VIEW),
      pht('Object Visible to Author'));

    $this->assertTrue(
      $filter->hasCapability(
        $viewer,
        $object,
        PhabricatorPolicyCapability::CAN_VIEW),
      pht('Object Visible to Others'));

    // Attach the file to the object and test that the association opens a
    // policy exception for the non-author viewer.

    $file->attachToObject($object->getPHID());

    // Test the attached file's visibility.
    $this->assertEqual(
      array(
        true,
        true,
      ),
      $this->canViewFile($users, $file),
      pht('Attached File Visibility'));

    // Create a "thumbnail" of the original file.
    $params = array(
      'name' => 'test.thumb.dat',
      'viewPolicy' => PhabricatorPolicies::POLICY_NOONE,
      'storageEngines' => array(
        $engine,
      ),
    );

    $xform = PhabricatorFile::newFromFileData($data, $params);

    id(new PhabricatorTransformedFile())
      ->setOriginalPHID($file->getPHID())
      ->setTransform('test-thumb')
      ->setTransformedPHID($xform->getPHID())
      ->save();

    // Test the thumbnail's visibility.
    $this->assertEqual(
      array(
        true,
        true,
      ),
      $this->canViewFile($users, $xform),
      pht('Attached Thumbnail Visibility'));

    // Detach the object and make sure it affects the thumbnail.
    $file->detachFromObject($object->getPHID());

    // Test the detached thumbnail's visibility.
    $this->assertEqual(
      array(
        true,
        false,
      ),
      $this->canViewFile($users, $xform),
      pht('Detached Thumbnail Visibility'));
  }

  private function canViewFile(array $users, PhabricatorFile $file) {
    $results = array();
    foreach ($users as $user) {
      $results[] = (bool)id(new PhabricatorFileQuery())
        ->setViewer($user)
        ->withPHIDs(array($file->getPHID()))
        ->execute();
    }
    return $results;
  }

  public function testFileStorageReadWrite() {
    $engine = new PhabricatorTestStorageEngine();

    $data = Filesystem::readRandomCharacters(64);

    $params = array(
      'name' => 'test.dat',
      'storageEngines' => array(
        $engine,
      ),
    );

    $file = PhabricatorFile::newFromFileData($data, $params);

    // Test that the storage engine worked, and was the target of the write. We
    // don't actually care what the data is (future changes may compress or
    // encrypt it), just that it exists in the test storage engine.
    $engine->readFile($file->getStorageHandle());

    // Now test that we get the same data back out.
    $this->assertEqual($data, $file->loadFileData());
  }

  public function testFileStorageUploadDifferentFiles() {
    $engine = new PhabricatorTestStorageEngine();

    $data = Filesystem::readRandomCharacters(64);
    $other_data = Filesystem::readRandomCharacters(64);

    $params = array(
      'name' => 'test.dat',
      'storageEngines' => array(
        $engine,
      ),
    );

    $first_file = PhabricatorFile::newFromFileData($data, $params);

    $second_file = PhabricatorFile::newFromFileData($other_data, $params);

    // Test that the second file uses  different storage handle from
    // the first file.
    $first_handle = $first_file->getStorageHandle();
    $second_handle = $second_file->getStorageHandle();

    $this->assertTrue($first_handle != $second_handle);
  }

  public function testFileStorageUploadSameFile() {
    $engine = new PhabricatorTestStorageEngine();

    $data = Filesystem::readRandomCharacters(64);

    $hash = PhabricatorFile::hashFileContent($data);
    if ($hash === null) {
      $this->assertSkipped(pht('File content hashing is not available.'));
    }

    $params = array(
      'name' => 'test.dat',
      'storageEngines' => array(
        $engine,
      ),
    );

    $first_file = PhabricatorFile::newFromFileData($data, $params);

    $second_file = PhabricatorFile::newFromFileData($data, $params);

    // Test that the second file uses the same storage handle as
    // the first file.
    $handle = $first_file->getStorageHandle();
    $second_handle = $second_file->getStorageHandle();

    $this->assertEqual($handle, $second_handle);
  }

  public function testFileStorageDelete() {
    $engine = new PhabricatorTestStorageEngine();

    $data = Filesystem::readRandomCharacters(64);

    $params = array(
      'name' => 'test.dat',
      'storageEngines' => array(
        $engine,
      ),
    );

    $file = PhabricatorFile::newFromFileData($data, $params);
    $handle = $file->getStorageHandle();
    $file->delete();

    $caught = null;
    try {
      $engine->readFile($handle);
    } catch (Exception $ex) {
      $caught = $ex;
    }

    $this->assertTrue($caught instanceof Exception);
  }

  public function testFileStorageDeleteSharedHandle() {
    $engine = new PhabricatorTestStorageEngine();

    $data = Filesystem::readRandomCharacters(64);

    $params = array(
      'name' => 'test.dat',
      'storageEngines' => array(
        $engine,
      ),
    );

    $first_file = PhabricatorFile::newFromFileData($data, $params);
    $second_file = PhabricatorFile::newFromFileData($data, $params);
    $first_file->delete();

    $this->assertEqual($data, $second_file->loadFileData());
  }

  public function testReadWriteTtlFiles() {
    $engine = new PhabricatorTestStorageEngine();

    $data = Filesystem::readRandomCharacters(64);

    $ttl = (PhabricatorTime::getNow() + phutil_units('24 hours in seconds'));

    $params = array(
      'name' => 'test.dat',
      'ttl.absolute' => $ttl,
      'storageEngines' => array(
        $engine,
      ),
    );

    $file = PhabricatorFile::newFromFileData($data, $params);
    $this->assertEqual($ttl, $file->getTTL());
  }

  public function testFileTransformDelete() {
    // We want to test that a file deletes all its inbound transformation
    // records and outbound transformed derivatives when it is deleted.

    // First, we create a chain of transforms, A -> B -> C.

    $engine = new PhabricatorTestStorageEngine();

    $params = array(
      'name' => 'test.txt',
      'storageEngines' => array(
        $engine,
      ),
    );

    $a = PhabricatorFile::newFromFileData('a', $params);
    $b = PhabricatorFile::newFromFileData('b', $params);
    $c = PhabricatorFile::newFromFileData('c', $params);

    id(new PhabricatorTransformedFile())
      ->setOriginalPHID($a->getPHID())
      ->setTransform('test:a->b')
      ->setTransformedPHID($b->getPHID())
      ->save();

    id(new PhabricatorTransformedFile())
      ->setOriginalPHID($b->getPHID())
      ->setTransform('test:b->c')
      ->setTransformedPHID($c->getPHID())
      ->save();

    // Now, verify that A -> B and B -> C exist.

    $xform_a = id(new PhabricatorFileQuery())
      ->setViewer(PhabricatorUser::getOmnipotentUser())
      ->withTransforms(
        array(
          array(
            'originalPHID' => $a->getPHID(),
            'transform'    => true,
          ),
        ))
      ->execute();

    $this->assertEqual(1, count($xform_a));
    $this->assertEqual($b->getPHID(), head($xform_a)->getPHID());

    $xform_b = id(new PhabricatorFileQuery())
      ->setViewer(PhabricatorUser::getOmnipotentUser())
      ->withTransforms(
        array(
          array(
            'originalPHID' => $b->getPHID(),
            'transform'    => true,
          ),
        ))
      ->execute();

    $this->assertEqual(1, count($xform_b));
    $this->assertEqual($c->getPHID(), head($xform_b)->getPHID());

    // Delete "B".

    $b->delete();

    // Now, verify that the A -> B and B -> C links are gone.

    $xform_a = id(new PhabricatorFileQuery())
      ->setViewer(PhabricatorUser::getOmnipotentUser())
      ->withTransforms(
        array(
          array(
            'originalPHID' => $a->getPHID(),
            'transform'    => true,
          ),
        ))
      ->execute();

    $this->assertEqual(0, count($xform_a));

    $xform_b = id(new PhabricatorFileQuery())
      ->setViewer(PhabricatorUser::getOmnipotentUser())
      ->withTransforms(
        array(
          array(
            'originalPHID' => $b->getPHID(),
            'transform'    => true,
          ),
        ))
      ->execute();

    $this->assertEqual(0, count($xform_b));

    // Also verify that C has been deleted.

    $alternate_c = id(new PhabricatorFileQuery())
      ->setViewer(PhabricatorUser::getOmnipotentUser())
      ->withPHIDs(array($c->getPHID()))
      ->execute();

    $this->assertEqual(array(), $alternate_c);
  }

}
