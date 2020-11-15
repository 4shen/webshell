<?php
/**
 * PHPCI - Continuous Integration for PHP
 *
 * @copyright    Copyright 2014, Block 8 Limited.
 * @license      https://github.com/Block8/PHPCI/blob/master/LICENSE.md
 * @link         https://www.phptesting.org/
 */

namespace PHPCI\Store;

use b8\Database;
use PHPCI\Model\Project;
use PHPCI\Store\Base\ProjectStoreBase;

/**
* Project Store
* @author       Dan Cryer <dan@block8.co.uk>
* @package      PHPCI
* @subpackage   Core
*/
class ProjectStore extends ProjectStoreBase
{
    /**
     * Returns a list of all branch names PHPCI has run builds against.
     * @param $projectId
     * @return array
     */
    public function getKnownBranches($projectId)
    {
        $query = 'SELECT DISTINCT branch from build WHERE project_id = :pid';
        $stmt = Database::getConnection('read')->prepare($query);
        $stmt->bindValue(':pid', $projectId);

        if ($stmt->execute()) {
            $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $map = function ($item) {
                return $item['branch'];
            };
            $rtn = array_map($map, $res);

            return $rtn;
        } else {
            return array();
        }
    }

    /**
     * Get a list of all projects, ordered by their title.
     * @return array
     */
    public function getAll()
    {
        $query = 'SELECT * FROM `project` ORDER BY `title` ASC';
        $stmt = Database::getConnection('read')->prepare($query);

        if ($stmt->execute()) {
            $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $map = function ($item) {
                return new Project($item);
            };
            $rtn = array_map($map, $res);

            $count = count($rtn);


            return array('items' => $rtn, 'count' => $count);
        } else {
            return array('items' => array(), 'count' => 0);
        }
    }

    /**
     * Get multiple Project by GroupId.
     * @param int $value
     * @param int $limit
     * @param string $useConnection
     * @return array
     * @throws \Exception
     */
    public function getByGroupId($value, $limit = 1000, $useConnection = 'read')
    {
        if (is_null($value)) {
            throw new \Exception('Value passed to ' . __FUNCTION__ . ' cannot be null.');
        }

        $query = 'SELECT * FROM `project` WHERE `group_id` = :group_id ORDER BY title LIMIT :limit';
        $stmt = Database::getConnection($useConnection)->prepare($query);
        $stmt->bindValue(':group_id', $value);
        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);

        if ($stmt->execute()) {
            $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $map = function ($item) {
                return new Project($item);
            };
            $rtn = array_map($map, $res);

            $count = count($rtn);

            return array('items' => $rtn, 'count' => $count);
        } else {
            return array('items' => array(), 'count' => 0);
        }
    }
}
