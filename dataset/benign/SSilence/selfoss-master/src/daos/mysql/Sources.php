<?php

namespace daos\mysql;

/**
 * Class for accessing persistent saved sources -- mysql
 *
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>
 */
class Sources implements \daos\SourcesInterface {
    /** @var class-string SQL helper */
    protected static $stmt = Statements::class;

    /** @var \daos\Database database connection */
    protected $database;

    public function __construct(\daos\Database $database) {
        $this->database = $database;
    }

    /**
     * add new source
     *
     * @param string $title
     * @param string[] $tags
     * @param string $filter
     * @param string $spout the source type
     * @param array $params depends from spout
     *
     * @return int new id
     */
    public function add($title, array $tags, $filter, $spout, array $params) {
        $stmt = static::$stmt;

        return $this->database->insert('INSERT INTO ' . \F3::get('db_prefix') . 'sources (title, tags, filter, spout, params) VALUES (:title, :tags, :filter, :spout, :params)', [
            ':title' => trim($title),
            ':tags' => $stmt::csvRow($tags),
            ':filter' => $filter,
            ':spout' => $spout,
            ':params' => htmlentities(json_encode($params))
        ]);
    }

    /**
     * edit source
     *
     * @param int $id the source id
     * @param string $title new title
     * @param string[] $tags new tags
     * @param string $filter
     * @param string $spout new spout
     * @param array $params the new params
     *
     * @return void
     */
    public function edit($id, $title, array $tags, $filter, $spout, array $params) {
        $stmt = static::$stmt;
        $this->database->exec('UPDATE ' . \F3::get('db_prefix') . 'sources SET title=:title, tags=:tags, filter=:filter, spout=:spout, params=:params WHERE id=:id', [
            ':title' => trim($title),
            ':tags' => $stmt::csvRow($tags),
            ':filter' => $filter,
            ':spout' => $spout,
            ':params' => htmlentities(json_encode($params)),
            ':id' => $id
        ]);
    }

    /**
     * delete source
     *
     * @param int $id
     *
     * @return void
     */
    public function delete($id) {
        $this->database->exec('DELETE FROM ' . \F3::get('db_prefix') . 'sources WHERE id=:id', [':id' => $id]);

        // delete items of this source
        $this->database->exec('DELETE FROM ' . \F3::get('db_prefix') . 'items WHERE source=:id', [':id' => $id]);
    }

    /**
     * save error message
     *
     * @param int $id the source id
     * @param string $error error message
     *
     * @return void
     */
    public function error($id, $error) {
        if (strlen($error) === 0) {
            $arr = [
                ':id' => $id
                ];
            $setarg = 'NULL';
        } else {
            $arr = [
                ':id' => $id,
                ':error' => $error
            ];
            $setarg = ':error';
        }

        $this->database->exec('UPDATE ' . \F3::get('db_prefix') . 'sources SET error=' . $setarg . ' WHERE id=:id', $arr);
    }

    /**
     * sets the last updated timestamp
     *
     * @param int $id the source id
     * @param int $lastEntry timestamp of the newest item or NULL when no items were added
     *
     * @return void
     */
    public function saveLastUpdate($id, $lastEntry) {
        $this->database->exec('UPDATE ' . \F3::get('db_prefix') . 'sources SET lastupdate=:lastupdate WHERE id=:id',
            [
                ':id' => $id,
                ':lastupdate' => time()
            ]);

        if ($lastEntry !== null) {
            $this->database->exec('UPDATE ' . \F3::get('db_prefix') . 'sources SET lastentry=:lastentry WHERE id=:id',
                [
                    ':id' => $id,
                    ':lastentry' => $lastEntry
                ]);
        }
    }

    /**
     * returns all sources
     *
     * @return mixed all sources
     */
    public function getByLastUpdate() {
        $ret = $this->database->exec('SELECT id, title, tags, spout, params, filter, error, lastupdate, lastentry FROM ' . \F3::get('db_prefix') . 'sources ORDER BY lastupdate ASC');

        return $ret;
    }

    /**
     * returns specified source (null if it doesnt exist)
     * or all sources if no id specified
     *
     * @param ?int $id specification of source id
     *
     * @return ?mixed specified source or all sources
     */
    public function get($id = null) {
        $stmt = static::$stmt;
        // select source by id if specified or return all sources
        if (isset($id)) {
            $ret = $this->database->exec('SELECT id, title, tags, spout, params, filter, error FROM ' . \F3::get('db_prefix') . 'sources WHERE id=:id', [':id' => $id]);
            $ret = $stmt::ensureRowTypes($ret, ['id' => \daos\PARAM_INT]);
            if (isset($ret[0])) {
                $ret = $ret[0];
            } else {
                $ret = null;
            }
        } else {
            $ret = $this->database->exec('SELECT id, title, tags, spout, params, filter, error FROM ' . \F3::get('db_prefix') . 'sources ORDER BY error DESC, lower(title) ASC');
            $ret = $stmt::ensureRowTypes($ret, [
                'id' => \daos\PARAM_INT,
                'tags' => \daos\PARAM_CSV
            ]);
        }

        return $ret;
    }

    /**
     * returns all sources including unread count
     *
     * @return mixed all sources
     */
    public function getWithUnread() {
        $stmt = static::$stmt;
        $ret = $this->database->exec('SELECT
            sources.id, sources.title, COUNT(items.id) AS unread
            FROM ' . \F3::get('db_prefix') . 'sources AS sources
            LEFT OUTER JOIN ' . \F3::get('db_prefix') . 'items AS items
                 ON (items.source=sources.id AND ' . $stmt::isTrue('items.unread') . ')
            GROUP BY sources.id, sources.title
            ORDER BY lower(sources.title) ASC');

        return $stmt::ensureRowTypes($ret, [
            'id' => \daos\PARAM_INT,
            'unread' => \daos\PARAM_INT
        ]);
    }

    /**
     * returns all sources including last icon
     *
     * @return mixed all sources
     */
    public function getWithIcon() {
        $stmt = static::$stmt;
        $ret = $this->database->exec('SELECT
                sources.id, sources.title, sources.tags, sources.spout,
                sources.params, sources.filter, sources.error, sources.lastentry,
                sourceicons.icon AS icon
            FROM ' . \F3::get('db_prefix') . 'sources AS sources
            LEFT OUTER JOIN
                (SELECT items.source, icon
                 FROM ' . \F3::get('db_prefix') . 'items AS items,
                      (SELECT source, MAX(id) as maxid
                       FROM ' . \F3::get('db_prefix') . 'items AS items
                       WHERE icon IS NOT NULL AND icon != \'\'
                       GROUP BY items.source) AS icons
                 WHERE items.id=icons.maxid AND items.source=icons.source
                 ) AS sourceicons
                ON sources.id=sourceicons.source
            ORDER BY ' . $stmt::nullFirst('sources.error', 'DESC') . ', lower(sources.title)');

        return $stmt::ensureRowTypes($ret, [
            'id' => \daos\PARAM_INT,
            'tags' => \daos\PARAM_CSV
        ]);
    }

    /**
     * test if the value of a specified field is valid
     *
     * @param   string      $name
     * @param   mixed       $value
     *
     * @return  bool
     */
    public function isValid($name, $value) {
        $return = false;

        switch ($name) {
        case 'id':
            $return = is_numeric($value);
            break;
        }

        return $return;
    }

    /**
     * returns all tags
     *
     * @return mixed all sources
     */
    public function getAllTags() {
        $result = $this->database->exec('SELECT tags FROM ' . \F3::get('db_prefix') . 'sources');
        $tags = [];
        foreach ($result as $res) {
            $tags = array_merge($tags, explode(',', $res['tags']));
        }
        $tags = array_unique($tags);

        return $tags;
    }

    /**
     * returns tags of a source
     *
     * @param int $id
     *
     * @return mixed tags of a source
     */
    public function getTags($id) {
        $result = $this->database->exec('SELECT tags FROM ' . \F3::get('db_prefix') . 'sources WHERE id=:id', [':id' => $id]);
        $tags = [];
        $tags = array_merge($tags, explode(',', $result[0]['tags']));
        $tags = array_unique($tags);

        return $tags;
    }

    /**
     * test if a source is already present using title, spout and params.
     * if present returns the id, else returns 0
     *
     * @param  string  $title
     * @param  string  $spout the source type
     * @param  array   $params depends from spout
     *
     * @return int id if any record is found
     */
    public function checkIfExists($title, $spout, array $params) {
        // Check if a entry exists with same title, spout and params
        $result = $this->database->exec('SELECT id FROM ' . \F3::get('db_prefix') . 'sources WHERE title=:title AND spout=:spout AND params=:params', [
            ':title' => trim($title),
            ':spout' => $spout,
            ':params' => htmlentities(json_encode($params))
        ]);
        if ($result) {
            return $result[0]['id'];
        }

        return 0;
    }
}
