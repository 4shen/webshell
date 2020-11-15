<?php

namespace daos\mysql;

use DateTime;
use Monolog\Logger;

/**
 * Class for accessing persistent saved items -- mysql
 *
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>
 * @author     Harald Lapp <harald.lapp@gmail.com>
 */
class Items implements \daos\ItemsInterface {
    /** @var bool indicates whether last run has more results or not */
    protected $hasMore = false;

    /** @var class-string SQL helper */
    protected static $stmt = Statements::class;

    /** @var \daos\Database database connection */
    protected $database;

    /** @var Logger */
    private $logger;

    public function __construct(Logger $logger, \daos\Database $database) {
        $this->database = $database;
        $this->logger = $logger;
    }

    /**
     * mark item as read
     *
     * @param int $id
     *
     * @return void
     */
    public function mark($id) {
        if ($this->isValid('id', $id) === false) {
            return;
        }

        if (is_array($id)) {
            $id = implode(',', $id);
        }

        // i used string concatenation after validating $id
        $this->database->exec('UPDATE ' . \F3::get('db_prefix') . "items SET unread=? WHERE id IN ($id)", false);
    }

    /**
     * mark item as unread
     *
     * @param int $id
     *
     * @return void
     */
    public function unmark($id) {
        if (is_array($id)) {
            $id = implode(',', $id);
        } elseif (!is_numeric($id)) {
            return;
        }
        $this->database->exec('UPDATE ' . \F3::get('db_prefix') . "items SET unread=? WHERE id IN ($id)", true);
    }

    /**
     * starr item
     *
     * @param int $id the item
     *
     * @return void
     */
    public function starr($id) {
        $this->database->exec('UPDATE ' . \F3::get('db_prefix') . 'items SET starred=:bool WHERE id=:id', [
            ':bool' => true,
            ':id' => $id
        ]);
    }

    /**
     * unstarr item
     *
     * @param int $id the item
     *
     * @return void
     */
    public function unstarr($id) {
        $this->database->exec('UPDATE ' . \F3::get('db_prefix') . 'items SET starred=:bool WHERE id=:id', [
            ':bool' => false,
            ':id' => $id
        ]);
    }

    /**
     * add new item
     *
     * @param array $values
     *
     * @return void
     */
    public function add($values) {
        $this->database->exec('INSERT INTO ' . \F3::get('db_prefix') . 'items (
                    datetime,
                    title,
                    content,
                    unread,
                    starred,
                    source,
                    thumbnail,
                    icon,
                    uid,
                    link,
                    author
                  ) VALUES (
                    :datetime,
                    :title,
                    :content,
                    :unread,
                    :starred,
                    :source,
                    :thumbnail,
                    :icon,
                    :uid,
                    :link,
                    :author
                  )',
                 [
                    ':datetime' => $values['datetime'],
                    ':title' => $values['title'],
                    ':content' => $values['content'],
                    ':thumbnail' => $values['thumbnail'],
                    ':icon' => $values['icon'],
                    ':unread' => 1,
                    ':starred' => 0,
                    ':source' => $values['source'],
                    ':uid' => $values['uid'],
                    ':link' => $values['link'],
                    ':author' => $values['author']
                 ]);
    }

    /**
     * checks whether an item with given
     * uid exists or not
     *
     * @param string $uid
     *
     * @return bool
     */
    public function exists($uid) {
        $res = $this->database->exec('SELECT COUNT(*) AS amount FROM ' . \F3::get('db_prefix') . 'items WHERE uid=:uid',
            [':uid' => [$uid, \PDO::PARAM_STR]]);

        return $res[0]['amount'] > 0;
    }

    /**
     * search whether given uids are already in database or not
     *
     * @param array $itemsInFeed list with ids for checking whether they are already in database or not
     * @param int $sourceId the id of the source to search for the items
     *
     * @return array with all existing uids from itemsInFeed (array (uid => id))
     */
    public function findAll($itemsInFeed, $sourceId) {
        $itemsFound = [];
        if (count($itemsInFeed) < 1) {
            return $itemsFound;
        }

        array_walk($itemsInFeed, function(&$value) {
            $value = $this->database->quote($value);
        });
        $query = 'SELECT id, uid AS uid FROM ' . \F3::get('db_prefix') . 'items WHERE source = ' . $this->database->quote($sourceId) . ' AND uid IN (' . implode(',', $itemsInFeed) . ')';
        $res = $this->database->exec($query);
        foreach ($res as $row) {
            $uid = $row['uid'];
            $itemsFound[$uid] = $row['id'];
        }

        return $itemsFound;
    }

    /**
     * Update the time items were last seen in the feed to prevent unwanted cleanup.
     *
     * @param int[] $itemIds ids of items to update
     *
     * @return void
     */
    public function updateLastSeen(array $itemIds) {
        $stmt = static::$stmt;
        $this->database->exec('UPDATE ' . \F3::get('db_prefix') . 'items SET lastseen = CURRENT_TIMESTAMP
            WHERE ' . $stmt::intRowMatches('id', $itemIds));
    }

    /**
     * cleanup orphaned and old items
     *
     * @param ?DateTime $date date to delete all items older than this value
     *
     * @return void
     */
    public function cleanup(DateTime $date = null) {
        $stmt = static::$stmt;
        $this->database->exec('DELETE FROM ' . \F3::get('db_prefix') . 'items
            WHERE source NOT IN (
                SELECT id FROM ' . \F3::get('db_prefix') . 'sources)');
        if ($date !== null) {
            $this->database->exec('DELETE FROM ' . \F3::get('db_prefix') . 'items
                WHERE ' . $stmt::isFalse('starred') . ' AND lastseen<:date',
                    [':date' => $date->format('Y-m-d') . ' 00:00:00']
            );
        }
    }

    /**
     * returns items
     *
     * @param mixed $options search, offset and filter params
     *
     * @return mixed items as array
     */
    public function get($options = []) {
        $stmt = static::$stmt;
        $params = [];
        $where = [$stmt::bool(true)];
        $order = 'DESC';

        // only starred
        if (isset($options['type']) && $options['type'] === 'starred') {
            $where[] = $stmt::isTrue('starred');
        }

        // only unread
        elseif (isset($options['type']) && $options['type'] === 'unread') {
            $where[] = $stmt::isTrue('unread');
            if (\F3::get('unread_order') === 'asc') {
                $order = 'ASC';
            }
        }

        // search
        if (isset($options['search']) && strlen($options['search']) > 0) {
            $search = implode('%', \helpers\Search::splitTerms($options['search']));
            $params[':search'] = $params[':search2'] = $params[':search3'] = ['%' . $search . '%', \PDO::PARAM_STR];
            $where[] = '(items.title LIKE :search OR items.content LIKE :search2 OR sources.title LIKE :search3) ';
        }

        // tag filter
        if (isset($options['tag']) && strlen($options['tag']) > 0) {
            $params[':tag'] = $options['tag'];
            $where[] = 'items.source=sources.id';
            $where[] = $stmt::csvRowMatches('sources.tags', ':tag');
        }
        // source filter
        elseif (isset($options['source']) && strlen($options['source']) > 0) {
            $params[':source'] = [$options['source'], \PDO::PARAM_INT];
            $where[] = 'items.source=:source ';
        }

        // update time filter
        if (isset($options['updatedsince']) && strlen($options['updatedsince']) > 0) {
            $params[':updatedsince'] = [$options['updatedsince'], \PDO::PARAM_STR];
            $where[] = 'items.updatetime > :updatedsince ';
        }

        // seek pagination (alternative to offset)
        if (isset($options['fromDatetime'])
            && strlen($options['fromDatetime']) > 0
            && isset($options['fromId'])
            && is_numeric($options['fromId'])) {
            // discard offset as it makes no sense to mix offset pagination
            // with seek pagination.
            $options['offset'] = 0;

            $offset_from_datetime_sql = $stmt::datetime($options['fromDatetime']);
            $params[':offset_from_datetime'] = [
                $offset_from_datetime_sql, \PDO::PARAM_STR
            ];
            $params[':offset_from_datetime2'] = [
                $offset_from_datetime_sql, \PDO::PARAM_STR
            ];
            $params[':offset_from_id'] = [
                $options['fromId'], \PDO::PARAM_INT
            ];
            $ltgt = null;
            if ($order === 'ASC') {
                $ltgt = '>';
            } else {
                $ltgt = '<';
            }

            // Because of sqlite lack of tuple comparison support, we use a
            // more complicated condition.
            $where[] = "(items.datetime $ltgt :offset_from_datetime OR
                         (items.datetime = :offset_from_datetime2 AND
                          items.id $ltgt :offset_from_id)
                        )";
        }

        $where_ids = '';
        // extra ids to include in stream
        if (isset($options['extraIds'])
            && count($options['extraIds']) > 0
            // limit the query to a sensible max
            && count($options['extraIds']) <= \F3::get('items_perpage')) {
            $extra_ids_stmt = $stmt::intRowMatches('items.id', $options['extraIds']);
            if ($extra_ids_stmt !== null) {
                $where_ids = $extra_ids_stmt;
            }
        }

        // finalize items filter
        $where_sql = implode(' AND ', $where);

        // set limit
        if (!is_numeric($options['items']) || $options['items'] > 200) {
            $options['items'] = \F3::get('items_perpage');
        }

        // set offset
        if (is_numeric($options['offset']) === false) {
            $options['offset'] = 0;
        }

        // first check whether more items are available
        $result = $this->database->exec('SELECT items.id
                   FROM ' . \F3::get('db_prefix') . 'items AS items, ' . \F3::get('db_prefix') . 'sources AS sources
                   WHERE items.source=sources.id AND ' . $where_sql . '
                   LIMIT 1 OFFSET ' . ($options['offset'] + $options['items']), $params);
        $this->hasMore = count($result);

        // get items from database
        $select = 'SELECT
            items.id, datetime, items.title AS title, content, unread, starred, source, thumbnail, icon, uid, link, updatetime, author, sources.title as sourcetitle, sources.tags as tags
            FROM ' . \F3::get('db_prefix') . 'items AS items, ' . \F3::get('db_prefix') . 'sources AS sources
            WHERE items.source=sources.id AND';
        $order_sql = 'ORDER BY items.datetime ' . $order . ', items.id ' . $order;

        if ($where_ids !== '') {
            // This UNION is required for the extra explicitely requested items
            // to be included whether or not they would have been excluded by
            // seek, filter, offset rules.
            //
            // SQLite note: the 'entries' SELECT is encapsulated into a
            // SELECT * FROM (...) to fool the SQLite engine into not
            // complaining about 'order by clause should come after union not
            // before'.
            $query = "SELECT * FROM (
                        SELECT * FROM ($select $where_sql $order_sql LIMIT " . $options['items'] . ' OFFSET ' . $options['offset'] . ") AS entries
                      UNION
                        $select $where_ids
                      ) AS items
                      $order_sql";
        } else {
            $query = "$select $where_sql $order_sql LIMIT " . $options['items'] . ' OFFSET ' . $options['offset'];
        }

        return $stmt::ensureRowTypes($this->database->exec($query, $params), [
            'id' => \daos\PARAM_INT,
            'unread' => \daos\PARAM_BOOL,
            'starred' => \daos\PARAM_BOOL,
            'source' => \daos\PARAM_INT,
            'tags' => \daos\PARAM_CSV
        ]);
    }

    /**
     * returns whether more items for last given
     * get call are available
     *
     * @return bool
     */
    public function hasMore() {
        return $this->hasMore;
    }

    /**
     * Obtain new or changed items in the database for synchronization with clients.
     *
     * @param int $sinceId id of last seen item
     * @param DateTime $notBefore cut off time stamp
     * @param DateTime $since timestamp of last seen item
     * @param int $howMany
     *
     * @return array of items
     */
    public function sync($sinceId, DateTime $notBefore, DateTime $since, $howMany) {
        $stmt = static::$stmt;
        $query = 'SELECT
        items.id, datetime, items.title AS title, content, unread, starred, source, thumbnail, icon, uid, link, updatetime, author, sources.title as sourcetitle, sources.tags as tags
        FROM ' . \F3::get('db_prefix') . 'items AS items, ' . \F3::get('db_prefix') . 'sources AS sources
        WHERE items.source=sources.id
            AND (' . $stmt::isTrue('unread') .
                 ' OR ' . $stmt::isTrue('starred') .
                 ' OR datetime >= :notBefore
                )
            AND (items.id > :sinceId OR
                 (datetime < :notBefore AND updatetime > :since))
        ORDER BY items.id LIMIT :howMany';

        $params = [
            'sinceId' => [$sinceId, \PDO::PARAM_INT],
            'howMany' => [$howMany, \PDO::PARAM_INT],
            'notBefore' => [$notBefore->format('Y-m-d H:i:s'), \PDO::PARAM_STR],
            'since' => [$since->format('Y-m-d H:i:s'), \PDO::PARAM_STR]
        ];

        return $stmt::ensureRowTypes($this->database->exec($query, $params), [
            'id' => \daos\PARAM_INT,
            'unread' => \daos\PARAM_BOOL,
            'starred' => \daos\PARAM_BOOL,
            'source' => \daos\PARAM_INT
        ]);
    }

    /**
     * Lowest id of interest
     *
     * @return int lowest id of interest
     */
    public function lowestIdOfInterest() {
        $stmt = static::$stmt;
        $lowest = $stmt::ensureRowTypes(
            $this->database->exec(
                'SELECT id FROM ' . \F3::get('db_prefix') . 'items AS items
                 WHERE ' . $stmt::isTrue('unread') .
                    ' OR ' . $stmt::isTrue('starred') .
                ' ORDER BY id LIMIT 1'),
            ['id' => \daos\PARAM_INT]
        );
        if ($lowest) {
            return $lowest[0]['id'];
        }

        return 0;
    }

    /**
     * Last id in db
     *
     * @return int last id in db
     */
    public function lastId() {
        $stmt = static::$stmt;
        $lastId = $stmt::ensureRowTypes(
            $this->database->exec(
                'SELECT id FROM ' . \F3::get('db_prefix') . 'items AS items
                 ORDER BY id DESC LIMIT 1'),
            ['id' => \daos\PARAM_INT]
        );
        if ($lastId) {
            return $lastId[0]['id'];
        }

        return 0;
    }

    /**
     * return all thumbnails
     *
     * @return string[] array with thumbnails
     */
    public function getThumbnails() {
        $thumbnails = [];
        $result = $this->database->exec('SELECT thumbnail
                   FROM ' . \F3::get('db_prefix') . 'items
                   WHERE thumbnail!=""');
        foreach ($result as $thumb) {
            $thumbnails[] = $thumb['thumbnail'];
        }

        return $thumbnails;
    }

    /**
     * return all icons
     *
     * @return string[] array with all icons
     */
    public function getIcons() {
        $icons = [];
        $result = $this->database->exec('SELECT icon
                   FROM ' . \F3::get('db_prefix') . 'items
                   WHERE icon!=""');
        foreach ($result as $icon) {
            $icons[] = $icon['icon'];
        }

        return $icons;
    }

    /**
     * return all thumbnails
     *
     * @param string $thumbnail name
     *
     * @return bool true if thumbnail is still in use
     */
    public function hasThumbnail($thumbnail) {
        $res = $this->database->exec('SELECT count(*) AS amount
                   FROM ' . \F3::get('db_prefix') . 'items
                   WHERE thumbnail=:thumbnail',
                  [':thumbnail' => $thumbnail]);
        $amount = $res[0]['amount'];
        if ($amount == 0) {
            $this->logger->debug('thumbnail not found: ' . $thumbnail);
        }

        return $amount > 0;
    }

    /**
     * return all icons
     *
     * @param string $icon file
     *
     * @return bool true if icon is still in use
     */
    public function hasIcon($icon) {
        $res = $this->database->exec('SELECT count(*) AS amount
                   FROM ' . \F3::get('db_prefix') . 'items
                   WHERE icon=:icon',
                  [':icon' => $icon]);

        return $res[0]['amount'] > 0;
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

            if (is_array($value)) {
                $return = true;
                foreach ($value as $id) {
                    if (is_numeric($id) === false) {
                        $return = false;
                        break;
                    }
                }
            }
            break;
        }

        return $return;
    }

    /**
     * returns the icon of the last fetched item.
     *
     * @param int $sourceid id of the source
     *
     * @return ?string
     */
    public function getLastIcon($sourceid) {
        if (is_numeric($sourceid) === false) {
            return null;
        }

        $res = $this->database->exec('SELECT icon FROM ' . \F3::get('db_prefix') . 'items WHERE source=:sourceid AND icon!=\'\' AND icon IS NOT NULL ORDER BY ID DESC LIMIT 1',
            [':sourceid' => $sourceid]);
        if (count($res) === 1) {
            return $res[0]['icon'];
        }

        return null;
    }

    /**
     * returns the amount of entries in database which are unread
     *
     * @return int amount of entries in database which are unread
     */
    public function numberOfUnread() {
        $stmt = static::$stmt;
        $res = $this->database->exec('SELECT count(*) AS amount
                   FROM ' . \F3::get('db_prefix') . 'items
                   WHERE ' . $stmt::isTrue('unread'));

        return $res[0]['amount'];
    }

    /**
     * returns the amount of total, unread, starred entries in database
     *
     * @return array mount of total, unread, starred entries in database
     */
    public function stats() {
        $stmt = static::$stmt;
        $res = $this->database->exec('SELECT
            COUNT(*) AS total,
            ' . $stmt::sumBool('unread') . ' AS unread,
            ' . $stmt::sumBool('starred') . ' AS starred
            FROM ' . \F3::get('db_prefix') . 'items;');
        $res = $stmt::ensureRowTypes($res, [
            'total' => \daos\PARAM_INT,
            'unread' => \daos\PARAM_INT,
            'starred' => \daos\PARAM_INT
        ]);

        return $res[0];
    }

    /**
     * returns the datetime of the last item update or user action in db
     *
     * @return string timestamp
     */
    public function lastUpdate() {
        $res = $this->database->exec('SELECT
            MAX(updatetime) AS last_update_time
            FROM ' . \F3::get('db_prefix') . 'items;');

        return $res[0]['last_update_time'];
    }

    /**
     * returns the statuses of items last update
     *
     * @param DateTime $since minimal date of returned items
     *
     * @return array of unread, starred, etc. status of specified items
     */
    public function statuses(DateTime $since) {
        $stmt = static::$stmt;
        $res = $this->database->exec('SELECT id, unread, starred
            FROM ' . \F3::get('db_prefix') . 'items
            WHERE ' . \F3::get('db_prefix') . 'items.updatetime > :since;',
                [':since' => [$since->format('Y-m-d H:i:s'), \PDO::PARAM_STR]]);
        $res = $stmt::ensureRowTypes($res, [
            'id' => \daos\PARAM_INT,
            'unread' => \daos\PARAM_BOOL,
            'starred' => \daos\PARAM_BOOL
        ]);

        return $res;
    }

    /**
     * bulk update of item status
     *
     * @param array $statuses array of statuses updates
     *
     * @return void
     */
    public function bulkStatusUpdate(array $statuses) {
        $stmt = static::$stmt;
        $sql = [];
        foreach ($statuses as $status) {
            if (array_key_exists('id', $status)) {
                $id = (int) $status['id'];
                if ($id > 0) {
                    $statusUpdate = null;

                    // sanitize statuses
                    foreach (['unread', 'starred'] as $sk) {
                        if (array_key_exists($sk, $status)) {
                            if ($status[$sk] == 'true') {
                                $statusUpdate = [
                                    'sk' => $sk,
                                    'sql' => $stmt::isTrue($sk)
                                ];
                            } elseif ($status[$sk] == 'false') {
                                $statusUpdate = [
                                    'sk' => $sk,
                                    'sql' => $stmt::isFalse($sk)
                                ];
                            }
                        }
                    }

                    // sanitize update time
                    if (array_key_exists('datetime', $status)) {
                        $updateDate = new \DateTime($status['datetime']);
                    } else {
                        $updateDate = null;
                    }

                    if ($statusUpdate !== null && $updateDate !== null) {
                        $sk = $statusUpdate['sk'];
                        if (array_key_exists($id, $sql)) {
                            // merge status updates for the same entry and
                            // ensure all saved status updates have been made
                            // after the last server update for this entry.
                            if (!array_key_exists($sk, $sql[$id]['updates'])
                                || $updateDate > $sql['id']['datetime']) {
                                $sql[$id]['updates'][$sk] = $statusUpdate['sql'];
                            }
                            if ($updateDate < $sql[$id]['datetime']) {
                                $sql[$id]['datetime'] = $updateDate;
                            }
                        } else {
                            // create new status update
                            $sql[$id] = [
                                'updates' => [$sk => $statusUpdate['sql']],
                                'datetime' => $updateDate->format('Y-m-d H:i:s')
                            ];
                        }
                    }
                }
            }
        }

        if ($sql) {
            $this->database->begin();
            foreach ($sql as $id => $q) {
                $params = [
                    ':id' => [$id, \PDO::PARAM_INT],
                    ':statusUpdate' => [$q['datetime'], \PDO::PARAM_STR]
                ];
                $updated = $this->database->exec(
                    'UPDATE ' . \F3::get('db_prefix') . 'items
                    SET ' . implode(', ', array_values($q['updates'])) . '
                    WHERE id = :id AND updatetime < :statusUpdate', $params);
                if ($updated == 0) {
                    // entry status was updated in between so updatetime must
                    // be updated to ensure client side consistency of
                    // statuses.
                    $this->database->exec(
                        'UPDATE ' . \F3::get('db_prefix') . 'items
                         SET ' . $stmt::rowTouch('updatetime') . '
                         WHERE id = :id', [':id' => [$id, \PDO::PARAM_INT]]);
                }
            }
            $this->database->commit();
        }
    }
}
