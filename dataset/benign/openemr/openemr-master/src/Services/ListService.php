<?php

/**
 * ListService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use Particle\Validator\Validator;

class ListService
{

  /**
   * Default constructor.
   */
    public function __construct()
    {
    }

    public function validate($list)
    {
        $validator = new Validator();

        $validator->required('title')->lengthBetween(2, 255);
        $validator->required('type')->lengthBetween(2, 255);
        $validator->required('pid')->numeric();
        $validator->optional('diagnosis')->lengthBetween(2, 255);
        $validator->required('begdate')->datetime('Y-m-d');
        $validator->optional('enddate')->datetime('Y-m-d');

        return $validator->validate($list);
    }

    public function getAll($pid, $list_type)
    {
        $sql = "SELECT * FROM lists WHERE pid=? AND type=? ORDER BY date DESC";

        $statementResults = sqlStatement($sql, array($pid, $list_type));

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function getOptionsByListName($list_name)
    {
        $sql = "SELECT * FROM list_options WHERE list_id = ?";

        $statementResults = sqlStatement($sql, array($list_name));

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function getOne($pid, $list_type, $list_id)
    {
        $sql = "SELECT * FROM lists WHERE pid=? AND type=? AND id=? ORDER BY date DESC";

        return sqlQuery($sql, array($pid, $list_type, $list_id));
    }

    public function insert($data)
    {
        $sql  = " INSERT INTO lists SET";
        $sql .= "     date=NOW(),";
        $sql .= "     activity=1,";
        $sql .= "     pid=?,";
        $sql .= "     type=?,";
        $sql .= "     title=?,";
        $sql .= "     begdate=?,";
        $sql .= "     enddate=?,";
        $sql .= "     diagnosis=?";

        return sqlInsert(
            $sql,
            array(
                $data['pid'],
                $data['type'],
                $data["title"],
                $data["begdate"],
                $data["enddate"],
                $data["diagnosis"]
            )
        );
    }

    public function update($data)
    {
        $sql  = " UPDATE lists SET";
        $sql .= "     title=?,";
        $sql .= "     begdate=?,";
        $sql .= "     enddate=?,";
        $sql .= "     diagnosis=?";
        $sql .= " WHERE id=?";

        return sqlStatement(
            $sql,
            array(
                $data["title"],
                $data["begdate"],
                $data["enddate"],
                $data["diagnosis"],
                $data["id"]
            )
        );
    }

    public function delete($pid, $list_id, $list_type)
    {
        $sql  = "DELETE FROM lists WHERE pid=? AND id=? AND type=?";

        return sqlStatement($sql, array($pid, $list_id, $list_type));
    }
}
