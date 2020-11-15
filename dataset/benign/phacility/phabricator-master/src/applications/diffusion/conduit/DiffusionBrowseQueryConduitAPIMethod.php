<?php

final class DiffusionBrowseQueryConduitAPIMethod
  extends DiffusionQueryConduitAPIMethod {

  public function getAPIMethodName() {
    return 'diffusion.browsequery';
  }

  public function getMethodDescription() {
    return pht(
      'File(s) information for a repository at an (optional) path and '.
      '(optional) commit.');
  }

  protected function defineReturnType() {
    return 'array';
  }

  protected function defineCustomParamTypes() {
    return array(
      'path' => 'optional string',
      'commit' => 'optional string',
      'needValidityOnly' => 'optional bool',
      'limit' => 'optional int',
      'offset' => 'optional int',
    );
  }

  protected function getResult(ConduitAPIRequest $request) {
    $result = parent::getResult($request);
    return $result->toDictionary();
  }

  protected function getGitResult(ConduitAPIRequest $request) {
    $drequest = $this->getDiffusionRequest();
    $repository = $drequest->getRepository();
    $path = $request->getValue('path');
    $commit = $request->getValue('commit');
    $offset = (int)$request->getValue('offset');
    $limit = (int)$request->getValue('limit');
    $result = $this->getEmptyResultSet();

    if ($path == '') {
      // Fast path to improve the performance of the repository view; we know
      // the root is always a tree at any commit and always exists.
      $stdout = 'tree';
    } else {
      try {
        list($stdout) = $repository->execxLocalCommand(
          'cat-file -t %s:%s',
          $commit,
          $path);
      } catch (CommandException $e) {
        // The "cat-file" command may fail if the path legitimately does not
        // exist, but it may also fail if the path is a submodule. This can
        // produce either "Not a valid object name" or "could not get object
        // info".

        // To detect if we have a submodule, use `git ls-tree`. If the path
        // is a submodule, we'll get a "160000" mode mask with type "commit".

        list($sub_err, $sub_stdout) = $repository->execLocalCommand(
          'ls-tree %s -- %s',
          $commit,
          $path);
        if (!$sub_err) {
          // If the path failed "cat-file" but "ls-tree" worked, we assume it
          // must be a submodule. If it is, the output will look something
          // like this:
          //
          //   160000 commit <hash> <path>
          //
          // We make sure it has the 160000 mode mask to confirm that it's
          // definitely a submodule.
          $mode = (int)$sub_stdout;
          if ($mode & 160000) {
            $submodule_reason = DiffusionBrowseResultSet::REASON_IS_SUBMODULE;
            $result
              ->setReasonForEmptyResultSet($submodule_reason);
            return $result;
          }
        }

        $stderr = $e->getStderr();
        if (preg_match('/^fatal: Not a valid object name/', $stderr)) {
          // Grab two logs, since the first one is when the object was deleted.
          list($stdout) = $repository->execxLocalCommand(
            'log -n2 --format="%%H" %s -- %s',
            $commit,
            $path);
          $stdout = trim($stdout);
          if ($stdout) {
            $commits = explode("\n", $stdout);
            $result
              ->setReasonForEmptyResultSet(
                DiffusionBrowseResultSet::REASON_IS_DELETED)
              ->setDeletedAtCommit(idx($commits, 0))
              ->setExistedAtCommit(idx($commits, 1));
            return $result;
          }

          $result->setReasonForEmptyResultSet(
            DiffusionBrowseResultSet::REASON_IS_NONEXISTENT);
          return $result;
        } else {
          throw $e;
        }
      }
    }

    if (trim($stdout) == 'blob') {
      $result->setReasonForEmptyResultSet(
        DiffusionBrowseResultSet::REASON_IS_FILE);
      return $result;
    }

    $result->setIsValidResults(true);
    if ($this->shouldOnlyTestValidity($request)) {
      return $result;
    }

    list($stdout) = $repository->execxLocalCommand(
      'ls-tree -z -l %s:%s',
      $commit,
      $path);

    $submodules = array();

    if (strlen($path)) {
      $prefix = rtrim($path, '/').'/';
    } else {
      $prefix = '';
    }

    $count = 0;
    $results = array();
    $lines = empty($stdout)
           ? array()
           : explode("\0", rtrim($stdout));

    foreach ($lines as $line) {
      // NOTE: Limit to 5 components so we parse filenames with spaces in them
      // correctly.
      // NOTE: The output uses a mixture of tabs and one-or-more spaces to
      // delimit fields.
      $parts = preg_split('/\s+/', $line, 5);
      if (count($parts) < 5) {
        throw new Exception(
          pht(
            'Expected "<mode> <type> <hash> <size>\t<name>", for ls-tree of '.
            '"%s:%s", got: %s',
            $commit,
            $path,
            $line));
      }

      list($mode, $type, $hash, $size, $name) = $parts;

      $path_result = new DiffusionRepositoryPath();

      if ($type == 'tree') {
        $file_type = DifferentialChangeType::FILE_DIRECTORY;
      } else if ($type == 'commit') {
        $file_type = DifferentialChangeType::FILE_SUBMODULE;
        $submodules[] = $path_result;
      } else {
        $mode = intval($mode, 8);
        if (($mode & 0120000) == 0120000) {
          $file_type = DifferentialChangeType::FILE_SYMLINK;
        } else {
          $file_type = DifferentialChangeType::FILE_NORMAL;
        }
      }

      $path_result->setFullPath($prefix.$name);
      $path_result->setPath($name);
      $path_result->setHash($hash);
      $path_result->setFileType($file_type);
      $path_result->setFileSize($size);

      if ($count >= $offset) {
        $results[] = $path_result;
      }

      $count++;

      if ($limit && $count >= ($offset + $limit)) {
        break;
      }
    }

    // If we identified submodules, lookup the module info at this commit to
    // find their source URIs.

    if ($submodules) {

      // NOTE: We need to read the file out of git and write it to a temporary
      // location because "git config -f" doesn't accept a "commit:path"-style
      // argument.

      // NOTE: This file may not exist, e.g. because the commit author removed
      // it when they added the submodule. See T1448. If it's not present, just
      // show the submodule without enriching it. If ".gitmodules" was removed
      // it seems to partially break submodules, but the repository as a whole
      // continues to work fine and we've seen at least two cases of this in
      // the wild.

      list($err, $contents) = $repository->execLocalCommand(
        'cat-file blob %s:.gitmodules',
        $commit);

      if (!$err) {
        $tmp = new TempFile();
        Filesystem::writeFile($tmp, $contents);
        list($module_info) = $repository->execxLocalCommand(
          'config -l -f %s',
          $tmp);

        $dict = array();
        $lines = explode("\n", trim($module_info));
        foreach ($lines as $line) {
          list($key, $value) = explode('=', $line, 2);
          $parts = explode('.', $key);
          $dict[$key] = $value;
        }

        foreach ($submodules as $path) {
          $full_path = $path->getFullPath();
          $key = 'submodule.'.$full_path.'.url';
          if (isset($dict[$key])) {
            $path->setExternalURI($dict[$key]);
          }
        }
      }
    }

    return $result->setPaths($results);
  }

  protected function getMercurialResult(ConduitAPIRequest $request) {
    $drequest = $this->getDiffusionRequest();
    $repository = $drequest->getRepository();
    $path = $request->getValue('path');
    $commit = $request->getValue('commit');
    $offset = (int)$request->getValue('offset');
    $limit = (int)$request->getValue('limit');
    $result = $this->getEmptyResultSet();


    $entire_manifest = id(new DiffusionLowLevelMercurialPathsQuery())
      ->setRepository($repository)
      ->withCommit($commit)
      ->withPath($path)
      ->execute();

    $results = array();

    $match_against = trim($path, '/');
    $match_len = strlen($match_against);

    // For the root, don't trim. For other paths, trim the "/" after we match.
    // We need this because Mercurial's canonical paths have no leading "/",
    // but ours do.
    $trim_len = $match_len ? $match_len + 1 : 0;

    $count = 0;
    foreach ($entire_manifest as $path) {
      if (strncmp($path, $match_against, $match_len)) {
        continue;
      }
      if (!strlen($path)) {
        continue;
      }
      $remainder = substr($path, $trim_len);
      if (!strlen($remainder)) {
        // There is a file with this exact name in the manifest, so clearly
        // it's a file.
        $result->setReasonForEmptyResultSet(
          DiffusionBrowseResultSet::REASON_IS_FILE);
        return $result;
      }

      $parts = explode('/', $remainder);
      $name = reset($parts);

      // If we've already seen this path component, we're looking at a file
      // inside a directory we already processed. Just move on.
      if (isset($results[$name])) {
        continue;
      }

      if (count($parts) == 1) {
        $type = DifferentialChangeType::FILE_NORMAL;
      } else {
        $type = DifferentialChangeType::FILE_DIRECTORY;
      }

      if ($count >= $offset) {
        $results[$name] = $type;
      }

      $count++;

      if ($limit && ($count >= ($offset + $limit))) {
        break;
      }
    }

    foreach ($results as $key => $type) {
      $path_result = new DiffusionRepositoryPath();
      $path_result->setPath($key);
      $path_result->setFileType($type);
      $path_result->setFullPath(ltrim($match_against.'/', '/').$key);

      $results[$key] = $path_result;
    }

    $valid_results = true;
    if (empty($results)) {
      // TODO: Detect "deleted" by issuing "hg log"?
      $result->setReasonForEmptyResultSet(
        DiffusionBrowseResultSet::REASON_IS_NONEXISTENT);
      $valid_results = false;
    }

    return $result
      ->setPaths($results)
      ->setIsValidResults($valid_results);
  }

  protected function getSVNResult(ConduitAPIRequest $request) {
    $drequest = $this->getDiffusionRequest();
    $repository = $drequest->getRepository();
    $path = $request->getValue('path');
    $commit = $request->getValue('commit');
    $offset = (int)$request->getValue('offset');
    $limit = (int)$request->getValue('limit');
    $result = $this->getEmptyResultSet();

    $subpath = $repository->getDetail('svn-subpath');
    if ($subpath && strncmp($subpath, $path, strlen($subpath))) {
      // If we have a subpath and the path isn't a child of it, it (almost
      // certainly) won't exist since we don't track commits which affect
      // it. (Even if it exists, return a consistent result.)
      $result->setReasonForEmptyResultSet(
        DiffusionBrowseResultSet::REASON_IS_UNTRACKED_PARENT);
      return $result;
    }

    $conn_r = $repository->establishConnection('r');

    $parent_path = DiffusionPathIDQuery::getParentPath($path);
    $path_query = new DiffusionPathIDQuery(
      array(
        $path,
        $parent_path,
      ));
    $path_map = $path_query->loadPathIDs();

    $path_id = $path_map[$path];
    $parent_path_id = $path_map[$parent_path];

    if (empty($path_id)) {
      $result->setReasonForEmptyResultSet(
        DiffusionBrowseResultSet::REASON_IS_NONEXISTENT);
      return $result;
    }

    if ($commit) {
      $slice_clause = qsprintf($conn_r, 'AND svnCommit <= %d', $commit);
    } else {
      $slice_clause = qsprintf($conn_r, '');
    }

    $index = queryfx_all(
      $conn_r,
      'SELECT pathID, max(svnCommit) maxCommit FROM %T WHERE
        repositoryID = %d AND parentID = %d
        %Q GROUP BY pathID',
      PhabricatorRepository::TABLE_FILESYSTEM,
      $repository->getID(),
      $path_id,
      $slice_clause);

    if (!$index) {
      if ($path == '/') {
        $result->setReasonForEmptyResultSet(
          DiffusionBrowseResultSet::REASON_IS_EMPTY);
      } else {

        // NOTE: The parent path ID is included so this query can take
        // advantage of the table's primary key; it is uniquely determined by
        // the pathID but if we don't do the lookup ourselves MySQL doesn't have
        // the information it needs to avoid a table scan.

        $reasons = queryfx_all(
          $conn_r,
          'SELECT * FROM %T WHERE repositoryID = %d
              AND parentID = %d
              AND pathID = %d
            %Q ORDER BY svnCommit DESC LIMIT 2',
          PhabricatorRepository::TABLE_FILESYSTEM,
          $repository->getID(),
          $parent_path_id,
          $path_id,
          $slice_clause);

        $reason = reset($reasons);

        if (!$reason) {
          $result->setReasonForEmptyResultSet(
            DiffusionBrowseResultSet::REASON_IS_NONEXISTENT);
        } else {
          $file_type = $reason['fileType'];
          if (empty($reason['existed'])) {
            $result->setReasonForEmptyResultSet(
              DiffusionBrowseResultSet::REASON_IS_DELETED);
            $result->setDeletedAtCommit($reason['svnCommit']);
            if (!empty($reasons[1])) {
              $result->setExistedAtCommit($reasons[1]['svnCommit']);
            }
          } else if ($file_type == DifferentialChangeType::FILE_DIRECTORY) {
            $result->setReasonForEmptyResultSet(
              DiffusionBrowseResultSet::REASON_IS_EMPTY);
          } else {
            $result->setReasonForEmptyResultSet(
              DiffusionBrowseResultSet::REASON_IS_FILE);
          }
        }
      }
      return $result;
    }

    $result->setIsValidResults(true);
    if ($this->shouldOnlyTestValidity($request)) {
      return $result;
    }

    $sql = array();
    foreach ($index as $row) {
      $sql[] = qsprintf(
        $conn_r,
        '(pathID = %d AND svnCommit = %d)',
        $row['pathID'],
        $row['maxCommit']);
    }

    $browse = queryfx_all(
      $conn_r,
      'SELECT *, p.path pathName
        FROM %T f JOIN %T p ON f.pathID = p.id
        WHERE repositoryID = %d
          AND parentID = %d
          AND existed = 1
        AND (%LO)
        ORDER BY pathName',
      PhabricatorRepository::TABLE_FILESYSTEM,
      PhabricatorRepository::TABLE_PATH,
      $repository->getID(),
      $path_id,
      $sql);

    $loadable_commits = array();
    foreach ($browse as $key => $file) {
      // We need to strip out directories because we don't store last-modified
      // in the filesystem table.
      if ($file['fileType'] != DifferentialChangeType::FILE_DIRECTORY) {
        $loadable_commits[] = $file['svnCommit'];
        $browse[$key]['hasCommit'] = true;
      }
    }

    $commits = array();
    $commit_data = array();
    if ($loadable_commits) {
      // NOTE: Even though these are integers, use '%Ls' because MySQL doesn't
      // use the second part of the key otherwise!
      $commits = id(new PhabricatorRepositoryCommit())->loadAllWhere(
        'repositoryID = %d AND commitIdentifier IN (%Ls)',
        $repository->getID(),
        $loadable_commits);
      $commits = mpull($commits, null, 'getCommitIdentifier');
      if ($commits) {
        $commit_data = id(new PhabricatorRepositoryCommitData())->loadAllWhere(
          'commitID in (%Ld)',
          mpull($commits, 'getID'));
        $commit_data = mpull($commit_data, null, 'getCommitID');
      } else {
        $commit_data = array();
      }
    }

    $path_normal = DiffusionPathIDQuery::normalizePath($path);

    $results = array();
    $count = 0;
    foreach ($browse as $file) {

      $full_path = $file['pathName'];
      $file_path = ltrim(substr($full_path, strlen($path_normal)), '/');
      $full_path = ltrim($full_path, '/');

      $result_path = new DiffusionRepositoryPath();
      $result_path->setPath($file_path);
      $result_path->setFullPath($full_path);
      $result_path->setFileType($file['fileType']);

      if (!empty($file['hasCommit'])) {
        $commit = idx($commits, $file['svnCommit']);
        if ($commit) {
          $data = idx($commit_data, $commit->getID());
          $result_path->setLastModifiedCommit($commit);
          $result_path->setLastCommitData($data);
        }
      }

      if ($count >= $offset) {
        $results[] = $result_path;
      }

      $count++;

      if ($limit && ($count >= ($offset + $limit))) {
        break;
      }
    }

    if (empty($results)) {
      $result->setReasonForEmptyResultSet(
        DiffusionBrowseResultSet::REASON_IS_EMPTY);
    }

    return $result->setPaths($results);
  }

  private function getEmptyResultSet() {
    return id(new DiffusionBrowseResultSet())
      ->setPaths(array())
      ->setReasonForEmptyResultSet(null)
      ->setIsValidResults(false);
  }

  private function shouldOnlyTestValidity(ConduitAPIRequest $request) {
    return $request->getValue('needValidityOnly', false);
  }

}
