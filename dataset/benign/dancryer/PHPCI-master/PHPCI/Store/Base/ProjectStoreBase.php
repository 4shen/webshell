<?php

/**
 * Project base store for table: project
 */

namespace PHPCI\Store\Base;

use b8\Database;
use b8\Exception\HttpException;
use PHPCI\Store;
use PHPCI\Model\Project;

/**
 * Project Base Store
 */
class ProjectStoreBase extends Store
{
    protected $tableName   = 'project';
    protected $modelName   = '\PHPCI\Model\Project';
    protected $primaryKey  = 'id';

    /**
     * Get a Project by primary key (Id)
     */
    public function getByPrimaryKey($value, $useConnection = 'read')
    {
        return $this->getById($value, $useConnection);
    }

    /**
     * Get a single Project by Id.
     * @return null|Project
     */
    public function getById($value, $useConnection = 'read')
    {
        if (is_null($value)) {
            throw new HttpException('Value passed to ' . __FUNCTION__ . ' cannot be null.');
        }

        $query = 'SELECT * FROM `project` WHERE `id` = :id LIMIT 1';
        $stmt = Database::getConnection($useConnection)->prepare($query);
        $stmt->bindValue(':id', $value);

        if ($stmt->execute()) {
            if ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                return new Project($data);
            }
        }

        return null;
    }

    /**
     * Get multiple Project by Title.
     * @return array
     */
    public function getByTitle($value, $limit = 1000, $useConnection = 'read')
    {
        if (is_null($value)) {
            throw new HttpException('Value passed to ' . __FUNCTION__ . ' cannot be null.');
        }


        $query = 'SELECT * FROM `project` WHERE `title` = :title LIMIT :limit';
        $stmt = Database::getConnection($useConnection)->prepare($query);
        $stmt->bindValue(':title', $value);
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

    /**
     * Get multiple Project by GroupId.
     * @return array
     */
    public function getByGroupId($value, $limit = 1000, $useConnection = 'read')
    {
        if (is_null($value)) {
            throw new HttpException('Value passed to ' . __FUNCTION__ . ' cannot be null.');
        }


        $query = 'SELECT * FROM `project` WHERE `group_id` = :group_id LIMIT :limit';
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
