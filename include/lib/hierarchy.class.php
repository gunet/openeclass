<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

/**
 * Eclass Hierarchy Coordinating Object.
 *
 * This class does not represent a hierarchy node or tree entity, but a core logic coordinating object
 * responsible for handling hierarchy and hierarchy node related tasks.
 */
class Hierarchy {

    private $dbtable;
    private $dbdepth;
    private $view;

    /* private $ordering_copy;
      private $ordermap; */

    /**
     * Constructor - do not use any arguments for default eclass behaviour (standard db tables).
     *
     * @param string $dbtable - Name of table with tree nodes
     */
    public function __construct($dbtable = 'hierarchy') {
        $this->dbtable = $dbtable;
        $this->dbdepth = $dbtable . '_depth';
        $this->view = " (SELECT node.*, COUNT(parent.id) - 1 AS depth
                     FROM " . $this->dbtable . " AS node,
                          " . $this->dbtable . " AS parent
                    WHERE node.lft BETWEEN parent.lft AND parent.rgt
                    GROUP BY node.id
                    ORDER BY node.lft) AS tmp ";
    }

    /**
     * Add a node to the tree.
     *
     * @param  string $name           - The new node name
     * @param  int    $parentlft      - The new node's parent lft
     * @param  string $code           - The new node code
     * @param  int    $allow_course   - Flag controlling if courses are allowed to belong to this new node
     * @param  int    $allow_user     - Flag controllinf if users are allowed to belong to this new node
     * @param  int    $order_priority - Special order priority for the node, the higher the value the higher place in the displayed order
     * @return int    $ret            - The new node id
     */
    public function addNode($name, $parentlft, $code, $allow_course, $allow_user, $order_priority) {
        $ret = null;

        if ($this->useProcedures()) {
            db_query("CALL add_node(" . quote($name) . ", $parentlft, " . quote($code) . ", $allow_course, $allow_user, $order_priority)");
            $r = mysql_fetch_array(db_query("SELECT LAST_INSERT_ID()"));
            $ret = $r[0];
        } else {
            $lft = $parentlft + 1;
            $rgt = $parentlft + 2;

            $this->shiftRight($parentlft);

            $query = "INSERT INTO " . $this->dbtable . " (name, lft, rgt, code, allow_course, allow_user, order_priority) "
                    . "VALUES (" . quote($name) . ", $lft, $rgt, " . quote($code) . ", $allow_course, $allow_user, $order_priority)";
            db_query($query);
            $ret = mysql_insert_id();
        }

        return $ret;
    }

    /**
     * Add a node to the tree requiring extra arguments (number and generator).
     *
     * @param  string $name           - The new node name
     * @param  int    $parentlft      - The new node's parent lft
     * @param  string $code           - The new node code
     * @param  int    $number         - The new node number
     * @param  int    $generator      - The new node generator
     * @param  int    $allow_course   - Flag controlling if courses are allowed to belong to this new node
     * @param  int    $allow_user     - Flag controllinf if users are allowed to belong to this new node
     * @param  int    $order_priority - Special order priority for the node, the higher the value the higher place in the displayed order
     * @return int    $ret            - The new node id
     */
    public function addNodeExt($name, $parentlft, $code, $number, $generator, $allow_course, $allow_user, $order_priority) {
        $ret = null;

        if ($this->useProcedures()) {
            db_query("CALL add_node_ext(" . quote($name) . ", $parentlft, " . quote($code) . ", $number, $generator, $allow_course, $allow_user, $order_priority)");
            $r = mysql_fetch_array(db_query("SELECT LAST_INSERT_ID()"));
            $ret = $r[0];
        } else {
            $lft = $parentlft + 1;
            $rgt = $parentlft + 2;

            $this->shiftRight($parentlft);

            $query = "INSERT INTO " . $this->dbtable . " (name, lft, rgt, code, number, generator, allow_course, allow_user, order_priority) "
                    . "VALUES (" . quote($name) . ", $lft, $rgt, " . quote($code) . ", $number, $generator, $allow_course, $allow_user, $order_priority)";
            db_query($query);
            $ret = mysql_insert_id();
        }

        return $ret;
    }

    /**
     * Update a tree node.
     *
     * @param int    $id             - The id of the node to update
     * @param string $name           - The new name for the node
     * @param int    $nodelft        - The new parent lft value
     * @param int    $lft            - The node's current lft value
     * @param int    $rgt            - The node's current rgt value
     * @param int    $parentlft      - The old parent lft value
     * @param string $code           - The new code for the node
     * @param int    $allow_course   - Flag controlling if courses are allowed to belong to this new node
     * @param int    $allow_user     - Flag controllinf if users are allowed to belong to this new node
     * @param int    $order_priority - Special order priority for the node, the higher the value the higher place in the displayed order
     */
    public function updateNode($id, $name, $nodelft, $lft, $rgt, $parentlft, $code, $allow_course, $allow_user, $order_priority) {
        if ($this->useProcedures()) {
            db_query("CALL update_node($id, " . quote($name) . ", $nodelft, $lft, $rgt, $parentlft, " . quote($code) . ", $allow_course, $allow_user, $order_priority)");
        } else {
            $query = "UPDATE " . $this->dbtable . " SET name = " . quote($name) . ",  lft = $lft, rgt = $rgt,
                    code = " . quote($code) . ", allow_course = $allow_course, allow_user = $allow_user, 
                    order_priority = $order_priority WHERE id = $id";
            db_query($query);

            if ($nodelft != $parentlft) {
                $this->moveNodes($nodelft, $lft, $rgt);
            }
        }
    }

    /**
     * Delete a node from the tree.
     *
     * @param int $id - The id of the node to delete
     */
    public function deleteNode($id) {
        if ($this->useProcedures()) {
            db_query("CALL delete_node($id)");
        } else {
            $result = db_query("SELECT lft, rgt FROM " . $this->dbtable . " WHERE id = $id");

            $row = mysql_fetch_assoc($result);

            $lft = $row['lft'];
            $rgt = $row['rgt'];

            db_query("DELETE FROM " . $this->dbtable . " WHERE id = $id");

            $this->delete($lft, $rgt);
        }
    }

    /**
     * Shift tree nodes to the right.
     *
     * @param int $node   - This is the lft of the node after which we want to shift
     * @param int $shift  - Length of shift
     * @param int $maxrgt - Maximum rgt value in the tree
     */
    public function shiftRight($node, $shift = 2, $maxrgt = 0) {
        $this->shift('+', $node, $shift, $maxrgt);
    }

    /**
     * Shift tree nodes to the left.
     *
     * @param int $node   - This is the lft of the node after which we want to shift
     * @param int $shift  - Length of shift
     * @param int $maxrgt - Maximum rgt value in the tree
     */
    public function shiftLeft($node, $shift = 2, $maxrgt = 0) {
        $this->shift('-', $node, $shift, $maxrgt);
    }

    /**
     * Shift a subtree to the end of the tree.
     *
     * @param int $lft    - The subtree current lft value
     * @param int $rgt    - The subtree current rgt value
     * @param int $maxrgt - Maximum rgt value in the tree
     */
    public function shiftEnd($lft, $rgt, $maxrgt) {
        $query = "UPDATE " . $this->dbtable . " SET  lft = (lft - " . ($lft - 1) . ")+" . $maxrgt . ", rgt = (rgt - " . ($lft - 1) . ")+" . $maxrgt . " WHERE lft BETWEEN " . $lft . " AND " . $rgt;
        db_query($query);
    }

    /**
     * Shift tree nodes.
     *
     * @param string $action - '+' for shift to the right, '-' for shift to the left
     * @param int    $node   - This is the lft of the node after which we want to shift
     * @param int    $shift  - Length of shift
     * @param int    $maxrgt - Maximum rgt value in the tree
     */
    public function shift($action, $node, $shift = 2, $maxrgt = 0) {
        $query = "UPDATE " . $this->dbtable . " SET rgt = rgt " . $action . " " . $shift . " WHERE rgt > " . $node . ($maxrgt > 0 ? " AND rgt<=" . $maxrgt : '');
        db_query($query);

        $query = "UPDATE " . $this->dbtable . " SET lft = lft " . $action . " " . $shift . " WHERE lft > " . $node . ($maxrgt > 0 ? " AND lft<=" . $maxrgt : '');
        db_query($query);
    }

    /**
     * Get maximum rgt value in the tree.
     *
     * @return int
     */
    public function getMaxRgt() {
        $result = db_query("SELECT rgt FROM " . $this->dbtable . " ORDER BY rgt desc limit 1");
        $row = mysql_fetch_assoc($result);

        return $row['rgt'];
    }

    /**
     * Get a child node's parent.
     *
     * @param  int   $lft - lft of child node
     * @param  int   $rgt - rgt of child node
     * @return array - the object/array of the mysql query result
     */
    public function getParent($lft, $rgt) {
        $query = "SELECT * FROM " . $this->dbtable . " WHERE lft < " . $lft . " AND rgt > " . $rgt . " ORDER BY lft DESC LIMIT 1";
        $result = db_query($query);

        return mysql_fetch_assoc($result);
    }

    /**
     * Get a child node's root parent.
     *
     * @param  int   $lft - lft of child node
     * @param  int   $rgt - rgt of child node
     * @return array - the object/array of the mysql query result
     */
    public function getRootParent($lft, $rgt) {
        $query = "SELECT * FROM " . $this->dbtable . " WHERE lft < " . $lft . " AND rgt > " . $rgt . " ORDER BY lft ASC LIMIT 1";
        $result = db_query($query);

        return mysql_fetch_assoc($result);
    }

    /**
     * Get a node's lft value.
     *
     * @param  int $id - The id of the node
     * @return int
     */
    public function getNodeLft($id) {
        $query = "SELECT lft FROM " . $this->dbtable . " WHERE id = " . $id;
        $res = mysql_fetch_assoc(db_query($query));

        return intval($res['lft']);
    }

    /**
     * Get a node's lft and rgt value.
     *
     * @param  int $id - The id of the node
     * @return array
     */
    public function getNodeLftRgt($id) {
        $query = "SELECT lft, rgt FROM " . $this->dbtable . " WHERE id = " . $id;
        $res = mysql_fetch_assoc(db_query($query));

        return array(intval($res['lft']), intval($res['rgt']));
    }

    /**
     * Get a node's (unserialized) name value.
     *
     * @param  string $key    - The db query search pattern
     * @param  string $useKey - Match against either the id or the lft during the db query
     * @return string         - The (unserialized) node's name
     */
    public function getNodeName($key, $useKey = 'id') {
        $query = "SELECT name FROM " . $this->dbtable . " WHERE " . $useKey . " = " . $key;
        $res = mysql_fetch_assoc(db_query($query));

        return self::unserializeLangField($res['name']);
    }

    /**
     * Delete a subtree.
     *
     * @param int $lft - The subtree node lft value
     * @param int $rgt - The subtree node rgt value
     */
    public function delete($lft, $rgt) {
        $nodeWidth = $rgt - $lft + 1;

        $query = "DELETE FROM " . $this->dbtable . "  WHERE lft BETWEEN " . $lft . " AND " . $rgt;
        db_query($query);

        $query = "UPDATE " . $this->dbtable . "  SET rgt = rgt - " . $nodeWidth . " WHERE rgt > " . $rgt;
        db_query($query);

        $query = "UPDATE " . $this->dbtable . "  SET lft = lft - " . $nodeWidth . " WHERE lft > " . $lft;
        db_query($query);
    }

    /**
     * Move a subtree to a different tree position.
     *
     * @param int $nodelft - The new subtree parent lft value
     * @param int $lft     - The subtree node current lft value
     * @param int $rgt     - The subtree node current rgt value
     */
    public function moveNodes($nodelft, $lft, $rgt) {
        $nodeWidth = $rgt - $lft + 1;
        $maxrgt = $this->getMaxRgt();

        $this->shiftEnd($lft, $rgt, $maxrgt);

        if ($nodelft == 0) {
            $this->shiftLeft($rgt, $nodeWidth);
        } else {
            $this->shiftLeft($rgt, $nodeWidth, $maxrgt);

            if ($lft < $nodelft) {
                $nodelft = $nodelft - $nodeWidth;
            }

            $this->shiftRight($nodelft, $nodeWidth, $maxrgt);

            $query = "UPDATE " . $this->dbtable . " SET rgt = (rgt - " . $maxrgt . ") + " . $nodelft . " WHERE rgt > " . $maxrgt;
            db_query($query);

            $query = "UPDATE " . $this->dbtable . " SET lft = (lft - " . $maxrgt . ") + " . $nodelft . " WHERE lft > " . $maxrgt;
            db_query($query);
        }
    }

    /**
     * Build an ArrayMap containing the tree nodes (ordered by the lft value).
     *
     * @param  array  $tree_array  - Include extra ArrayMap contents, useful for dropdowns and html-related UI selection dialogs.
     * @param  string $useKey      - Key for return array, can be 'lft' or 'id'
     * @param  int    $exclude     - The id of the subtree parent node we want to exclude from the result
     * @param  string $where       - Extra filtering db query where arguments, mainly for selecting course/user allowing nodes
     * @param  boolean $dashprefix - Flag controlling whether the resulted ArrayMap's name values will be prefixed by dashes indicating each node's depth in the tree
     * @return array               - The returned result is an array of ArrayMaps, in the form of <treeArrayMap, idMap, depthMap, codeMap, allowCourseMap, allowUserMap, orderingMap>.
     *                               The Tree map is in the form of <node key, node name>, all other byproducts (unordered) are in the following forms: <node key, node id>,
     *                               <node key, node depth>, <node key, node code>, <node key, allow_course>, <node key, allow_user> and <node key, order_priority>.
     */
    public function build($tree_array = array('0' => 'Top'), $useKey = 'lft', $exclude = null, $where = '', $dashprefix = false) {
        if ($exclude != null) {
            $result = db_query("SELECT * FROM " . $this->dbtable . " WHERE id = $exclude");
            $row = mysql_fetch_assoc($result);

            $query = "SELECT node.id, node.lft, node.name, node.code, node.allow_course, node.allow_user,
                             node.order_priority, COUNT(parent.id) - 1 AS depth
                     FROM " . $this->dbtable . " AS node, " . $this->dbtable . " AS parent
                    WHERE node.lft BETWEEN parent.lft AND parent.rgt
                    AND (node.lft < " . $row['lft'] . " OR node.lft > " . $row['rgt'] . ")
                    $where
                    GROUP BY node.id
                    ORDER BY node.lft";
        } else {
            $query = "SELECT node.id, node.lft, node.name, node.code, node.allow_course, node.allow_user,
                             node.order_priority, COUNT(parent.id) - 1 AS depth
                     FROM " . $this->dbtable . " AS node, " . $this->dbtable . " AS parent
                    WHERE node.lft BETWEEN parent.lft AND parent.rgt
                    $where
                    GROUP BY node.id
                    ORDER BY node.lft";
        }

        $result = db_query($query);

        $idmap = array();
        $depthmap = array();
        $codemap = array();
        $allowcoursemap = array();
        $allowusermap = array();
        $orderingmap = array();

        // necessary to avoid php notices for undefined offset
        $idmap[0] = 0;
        $depthmap[0] = 0;
        $codemap[0] = '';
        $allowcoursemap[0] = 1;
        $allowusermap[0] = 1;
        $orderingmap[0] = 999999;

        while ($row = mysql_fetch_assoc($result)) {
            $prefix = '';
            if ($dashprefix) {
                for ($i = 0; $i < $row['depth']; $i++) {
                    $prefix .= '&nbsp;-&nbsp;';
                }
            }

            $tree_array[$row[$useKey]] = $prefix . self::unserializeLangField($row['name']);
            $idmap[$row[$useKey]] = $row['id'];
            $depthmap[$row[$useKey]] = $row['depth'];
            $codemap[$row[$useKey]] = $row['code'];
            $allowcoursemap[$row[$useKey]] = $row['allow_course'];
            $allowusermap[$row[$useKey]] = $row['allow_user'];
            $orderingmap[$row[$useKey]] = intval($row['order_priority']);
        }

        return array($tree_array, $idmap, $depthmap, $codemap, $allowcoursemap, $allowusermap, $orderingmap);
    }

    /**
     * DEPRECATED due to performance loss, sorting moved to jstree
     * Build an ArrayMap containing the tree nodes (customly ordered per depth level).
     *
     * This is the entry-point method for building ordered ArrayMaps using recursion (each depth level is a different recursion step).
     * See also the utilized recursive method appendOrdered(). For each recursion step, the nodes are ordered and appended to the
     * tree ArrayMap.
     *
     * @param  array   $tree_array - Include extra ArrayMap contents, useful for dropdowns and html-related UI selection dialogs.
     * @param  string  $useKey     - Key for return array, can be 'lft' or 'id'
     * @param  int     $exclude    - The id of the subtree parent node we want to exclude from the result
     * @param  string  $where      - Extra filtering db query where arguments, mainly for selecting course/user allowing nodes
     * @param  boolean $dashprefix - Flag controlling whether the resulted ArrayMap's name values will be prefixed by dashes indicating each node's depth in the tree
     * @return array               - The returned result is an array of ArrayMaps, in the form of <treeArrayMap, idMap, depthMap, codeMap, allowCourseMap, allowUserMap>.
     *                               The Tree map is in the form of <node key, node name>, all other byproducts (unordered) are in the following forms: <node key, node id>,
     *                               <node key, node depth>, <node key, node code>, <node key, allow_course> and <node key, allow_user>.
     */
    /* public function buildOrdered($tree_array = array('0' => 'Top'), $useKey = 'lft', $exclude = null, $where = '', $dashprefix = true)
      {
      $res = null;
      $swhere = '';
      $excwhere = '';
      $excnwhere = '';
      $idmap = array();
      $depthmap = array();
      $codemap = array();
      $allowcoursemap = array();
      $allowusermap = array();
      $this->ordermap = array();
      $level = array();
      $mindepth = array();

      if (strstr($where, 'allow_course') !== false)
      $swhere = ' WHERE allow_course = true ';
      else if (strstr($where, 'allow_user') !== false)
      $swhere = ' WHERE allow_user  = true ';

      if ($exclude != null)
      {
      $excnode = mysql_fetch_array(db_query("SELECT * FROM ". $this->dbtable ." WHERE id = $exclude"));
      $excwhere = ' AND (lft < '. $excnode['lft'] .' OR lft > '. $excnode['rgt'] .') ';
      $excnwhere = ' AND (node.lft < '. $excnode['lft'] .' OR node.lft > '. $excnode['rgt'] .') ';
      }

      if ($this->useProcedures())
      {
      $mindepth = mysql_fetch_array(db_query("SELECT min(depth) FROM ". $this->dbdepth . $swhere));
      $res = db_query("SELECT id, lft, name, depth, code, allow_course, allow_user, order_priority FROM ". $this->dbdepth ." WHERE depth = ". $mindepth[0] . $excwhere);
      }
      else
      {
      $mindepth = mysql_fetch_array(db_query("SELECT min(depth) FROM ". $this->view . $swhere));
      $res = db_query("SELECT id, lft, name, depth, code, allow_course, allow_user, order_priority FROM ". $this->view ." WHERE depth = ". $mindepth[0] . $excwhere);
      }

      while ($row = mysql_fetch_assoc($res))
      {
      $prefix = '';
      if ($dashprefix)
      for ($i = 0; $i < $mindepth[0]; $i++)
      $prefix .= '&nbsp;-&nbsp;';

      $level[$row[$useKey]] = $prefix . self::unserializeLangField($row['name']);
      $idmap[$row[$useKey]] = $row['id'];
      $depthmap[$row[$useKey]] = $row['depth'];
      $codemap[$row[$useKey]] = $row['code'];
      $allowcoursemap[$row[$useKey]] = $row['allow_course'];
      $allowusermap[$row[$useKey]] = $row['allow_user'];
      $this->ordermap[$row[$useKey]] = intval($row['order_priority']); // the custom orderCmp function needs access to this ordermap
      }
      $this->ordering_copy = $level; // the custom orderCmp function needs a copy of the array to be ordered in order to read values from
      uksort($level, array($this, 'orderCmp'));

      $this->appendOrdered($tree_array, $level, $idmap, $depthmap, $codemap, $allowcoursemap, $allowusermap, $mindepth[0]+1, $useKey, $where, $excnwhere, $dashprefix);

      return array($tree_array, $idmap, $depthmap, $codemap, $allowcoursemap, $allowusermap);
      } */

    /**
     * DEPRECATED due to performance loss, sorting moved to jstree
     * Recursively append customly ordered entries to an existing tree ArrayMap.
     *
     * Designed to be used in combination with special entry-point methods such as buildOrdered().
     *
     * It requires as input the byproducts of buildOrdered(), passed by reference. The extra arguments are mainly for excluding
     * parts of the subtree already excluded at a previous recursion step (or the entry-point itself).
     */
    /* public function appendOrdered(&$final, &$level, &$idmap, &$depthmap, &$codemap, &$allowcoursemap, &$allowusermap, $depth, $useKey, $where = '', $excwhere = '', $dashprefix = true)
      {
      foreach ($level as $key => $value)
      {
      $final[$key] = $value;

      $res = db_query("SELECT node.* FROM ". $this->dbtable ." AS node
      LEFT OUTER JOIN ". $this->dbtable ." AS parent ON parent.lft =
      (SELECT MAX(S.lft)
      FROM ". $this->dbtable ." AS S
      WHERE node.lft > S.lft
      AND node.lft < S.rgt)
      WHERE parent.id = ". $idmap[$key] ." ". $where . $excwhere);

      if (mysql_num_rows($res) > 0)
      {
      $tmp = array();
      $this->ordermap = array();

      while($row = mysql_fetch_assoc($res))
      {
      $prefix = '';
      if ($dashprefix)
      for ($i = 0; $i < $depth; $i++)
      $prefix .= '&nbsp;-&nbsp;';

      $tmp[$row[$useKey]] = $prefix . self::unserializeLangField($row['name']);
      $idmap[$row[$useKey]] = $row['id'];
      $depthmap[$row[$useKey]] = $depth;
      $codemap[$row[$useKey]] = $row['code'];
      $allowcoursemap[$row[$useKey]] = $row['allow_course'];
      $allowusermap[$row[$useKey]] = $row['allow_user'];
      $this->ordermap[$row[$useKey]] = intval($row['order_priority']); // the custom orderCmp function needs access to this ordermap
      }
      $this->ordering_copy = $tmp; // the custom orderCmp function needs a copy of the array to be ordered in order to read values from
      uksort($tmp, array($this, 'orderCmp'));

      $this->appendOrdered($final, $tmp, $idmap, $depthmap, $codemap, $allowcoursemap, $allowusermap, $depth+1, $useKey, $where, $excwhere, $dashprefix);
      } else
      continue;
      }
      } */

    /**
     * DEPRECATED due to performance loss, sorting moved to jstree
     * Custom comparison function for ordering/sorting the tree nodes primarily according to their order priority
     * and secondarily alphabetically. To be used in conjustion with uksort()
     *
     * @param  int $a - arraymap key of node a
     * @param  int $b - arraymap key of node b
     * @return int    - < 0 if node a is less than node b, > 0 if node a is greater than node b, and 0 if they are equal.
     */
    /* private function orderCmp($a, $b)
      {
      if ($this->ordermap[$a] == $this->ordermap[$b])
      {
      // alphabetical compare
      return strcasecmp($this->ordering_copy[$a], $this->ordering_copy[$b]);
      }
      else
      {
      return ($this->ordering_copy[$a] > $this->ordering_copy[$b]) ? -1 : 1;
      }
      } */

    /**
     * Represent the tree using XML or HTML unordered list tags (<ul> and <li>) data source. Used for JSTree representation (GUI).
     *
     * @param array $options           - Options array for construction of the HTML unordered tree representation. Possible key value pairs are:
     * 'params'              => string  - Extra html tag parameters for the form's input elements (such as name)
     * 'defaults'            => array   - The ids of the already selected/added nodes. It can also be a single integer value, the code handles it automatically.
     * 'exclude'             => int     - The id of the subtree parent node we want to exclude from the result
     * 'tree'                => array   - Include (prepend) extra ArrayMap contents
     * 'useKey'              => string  - Key for return array, can be 'lft' or 'id'
     * 'where'               => string  - Extra filtering db query where arguments, mainly for selecting course/user allowing nodes
     * 'multiple'            => boolean - Flag controlling whether the picker will allow multiple tree nodes selection or just one (single)
     * 'allow_only_defaults' => boolean - Flag controlling whether the picker will mark non-default tree nodes as non-selectable ones
     * 'dashprefix'          => boolean - Flag controlling whether the resulted ArrayMap's name values will be prefixed by dashes indicating each node's depth in the tree
     * 'codesuffix'          => boolean - Flag controlling whether the resulted ArrayMap's name values will be suffixed by each node's code in parentheses
     * 'allowables'          => array   - The ids of the (parent) nodes whose subtrees are to be allowed, all others will be marked as non-selectables
     * 'xmlout'              => boolean - Flag controlling JSTree datasource
     * You can omit all of the above since this method uses default values.
     *
     * @return string $out - XML or HTML output
     */
    public function buildTreeDataSource($options = array()) {
        $tree_array = (array_key_exists('tree', $options)) ? $options['tree'] : array();
        $useKey = (array_key_exists('useKey', $options)) ? $options['useKey'] : 'id';
        $exclude = (array_key_exists('exclude', $options)) ? $options['exclude'] : null;
        $where = (array_key_exists('where', $options)) ? $options['where'] : '';
        $dashprefix = (array_key_exists('dashprefix', $options)) ? $options['dashprefix'] : false;
        $codesuffix = (array_key_exists('codesuffix', $options)) ? $options['codesuffix'] : false;
        $defaults = (array_key_exists('defaults', $options)) ? $options['defaults'] : '';
        $allowables = (array_key_exists('allowables', $options)) ? $options['allowables'] : null;
        $allow_only_defaults = (array_key_exists('allow_only_defaults', $options)) ? $options['allow_only_defaults'] : false;
        $mark_allow_user = (strstr($where, 'allow_user') !== false) ? true : false;
        $mark_allow_course = (strstr($where, 'allow_course') !== false) ? true : false;
        $xmlout = (array_key_exists('xmlout', $options)) ? $options['xmlout'] : true;

        $defs = (is_array($defaults)) ? $defaults : array(intval($defaults));
        $subdefs = ($allow_only_defaults) ? $this->buildSubtrees($defs) : array();
        $suballowed = ($allowables != null) ? $this->buildSubtrees($allowables) : null;
        $out = ($xmlout) ? '<root>' : '<ul>' . "\n";

        list($tree_array, $idmap, $depthmap, $codemap, $allowcoursemap, $allowusermap, $orderingmap) = $this->build($tree_array, $useKey, $exclude, null, $dashprefix);
        $i = 0;
        $current_depth = null;
        $start_depth = null;

        foreach ($tree_array as $key => $value) {
            if ($i == 0)
                $start_depth = $current_depth = ($key != 0) ? $depthmap[$key] : 0;
            else {
                if ($depthmap[$key] > $current_depth) {
                    $out = ($xmlout) ? substr($out, 0, -8) : substr($out, 0, -6);
                    $out .= ($xmlout) ? '' : '<ul>' . "\n";

                    $current_depth = $depthmap[$key];
                }

                if ($depthmap[$key] < $current_depth) {
                    for ($i = $current_depth; $i > $depthmap[$key]; $i--) {
                        $out .= ($xmlout) ? '<\/item>' : '</ul></li>' . "\n";
                    }

                    $current_depth = $depthmap[$key];
                }
            }

            $rel = '';
            $class = '';
            if (($mark_allow_course && !$allowcoursemap[$key]) ||
                    ($mark_allow_user && !$allowusermap[$key]) ||
                    ($allow_only_defaults && !in_array($idmap[$key], $subdefs) ) ||
                    ($suballowed != null && !in_array($idmap[$key], $suballowed) )
            )
                $rel = 'nosel';
            if (!empty($rel)) {
                $rel = "rel='" . $rel . "'";
                $class = "class='nosel'";
            }

            $valcode = '';
            if ($codesuffix && strlen($codemap[$key]) > 0)
                $valcode = ' (' . $codemap[$key] . ')';

            // valid HTML requires ids starting with letters.
            // We can just use any 2 characters, all JS funcs use obj.attr("id").substring(2)
            if ($xmlout)
                $out .= "<item id='nd" . $key . "' " . $rel . " tabindex='" . $orderingmap[$key] . "'><content><name " . $class . ">" . $value . $valcode . '<\/name><\/content><\/item>';
            else
                $out .= "<li id='nd" . $key . "' " . $rel . " tabindex='" . $orderingmap[$key] . "'><a href='#' " . $class . ">" . $value . $valcode . "</a></li>" . "\n";

            $i++;
        }

        if (!$xmlout)
            $out .= '</ul>';

        // close remaining open tags
        $remain_depth = $current_depth - $start_depth;
        if ($remain_depth > 0)
            for ($j = 0; $j < $remain_depth; $j++) {
                $out .= ($xmlout) ? '<\/item>' : '</li></ul>';
            }

        if ($xmlout)
            $out .= '<\/root>';

        return $out;
    }

    /**
     * Compile an array with the root node ids (nodes of 0 depth).
     * 
     * @return array
     */
    public function buildRootsArray() {
        $ret = array();
        $res = ($this->useProcedures()) ? db_query("SELECT id FROM " . $this->dbdepth . " WHERE depth = 0") : db_query("SELECT id FROM " . $this->view . " WHERE depth = 0");
        while ($row = mysql_fetch_assoc($res)) {
            $ret[] = $row['id'];
        }
        return $ret;
    }

    /**
     * Compile a comma seperated list with node ids. Used for setting JSTree initially_open core
     * configuration option
     *
     * @return string $initopen - The returned comma seperated list
     */
    public function buildJSTreeInitOpen() {
        // compile a comma seperated list with node ids that will be initially open (nodes of 0 depth, roots)
        $initopen = '';
        foreach ($this->buildRootsArray() as $id) {
            $initopen .= '"nd' . $id . '",';
        }
        return $initopen;
    }

    /**
     * Build the necessary Javascript code for the Node Picker UI element. This is a private method
     * utilized by the entry-point buildNodePicker(), buildCourseNodePicker() and buildUserNodePicker() methods.
     *
     * @param array $options           - Options array for construction of the HTML code for the Node picker. Possible key value pairs are:
     * 'params'              => string  - Extra html tag parameters for the form's input elements (such as name)
     * 'defaults'            => array   - The ids of the already selected/added nodes. It can also be a single integer value, the code handles it automatically.
     * 'exclude'             => int     - The id of the subtree parent node we want to exclude from the result
     * 'tree'                => array   - Include (prepend) extra ArrayMap contents
     * 'useKey'              => string  - Key for return array, can be 'lft' or 'id'
     * 'where'               => string  - Extra filtering db query where arguments, mainly for selecting course/user allowing nodes
     * 'multiple'            => boolean - Flag controlling whether the picker will allow multiple tree nodes selection or just one (single)
     * 'allow_only_defaults' => boolean - Flag controlling whether the picker will mark non-default tree nodes as non-selectable ones
     * 'allowables'          => array   - The ids of the (parent) nodes whose subtrees are to be allowed, all others will be marked as non-selectables
     * 'xmlout'              => boolean - Flag controlling JSTree datasource
     *
     * @return string $js              - The returned JS code
     */
    private function buildJSNodePicker($options) {
        global $themeimg, $langCancel, $langSelect, $langEmptyNodeSelect, $langEmptyAddNode, $langNodeDel;

        $params = $options['params'];
        $offset = (isset($options['defaults']) && is_array($options['defaults'])) ? count($options['defaults']) : 0; // The number of the parents that the editing child already belongs to (mainly for edit forms)
        $xmlout = (isset($options['xmlout']) && is_bool($options['xmlout'])) ? $options['xmlout'] : true;

        $xmldata = '';
        if ($xmlout)
            $xmldata = $this->buildTreeDataSource($options);
        $initopen = $this->buildJSTreeInitOpen();

        if ($offset > 0)
            $offset -=1;

        $js = <<<jContent
<script type="text/javascript">
/* <![CDATA[ */

var countnd = $offset;

$(function() {

    $( "#ndAdd" ).click(function() {
        $( "#dialog-form" ).dialog( "open" );
    });

    $( "#dialog-form" ).dialog({
        autoOpen: false,
        height: 600,
        width: 600,
        modal: true,
        buttons: {
            "$langSelect": function() {

                var newnode = $( "#js-tree" ).jstree("get_selected");

                if (!newnode.length)
                    alert("$langEmptyNodeSelect");
                else
                {
                    countnd += 1;
                    $( "#nodCnt" ).append( '<p id="nd_' + countnd + '">'
                                         + '<input type="hidden" $params value="' + newnode.attr("id").substring(2) + '" />'
                                         + newnode.children("a").text()
                                         + '&nbsp;<a href="#nodCnt" onclick="$( \'#nd_' + countnd + '\').remove(); $(\'#dialog-set-key\').val(null); $(\'#dialog-set-value\').val(null);"><img src="$themeimg/delete.png" title="$langNodeDel" alt="$langNodeDel"/><\/a>'
                                         + '<\/p>');

                    $( "#dialog-set-value" ).val( newnode.children("a").text() );
                    $( "#dialog-set-key" ).val(newnode.attr("id").substring(2));
                    document.getElementById('dialog-set-key').onchange();

                    $( this ).dialog( "close" );
                }
            },
            "$langCancel": function() {
                $( this ).dialog( "close" );
            }
        }
    });

    $( "#js-tree" ).jstree({

jContent;

        if ($xmlout)
            $js .= <<<jContent
        "plugins" : ["xml_data", "themes", "ui", "cookies", "types", "sort"],
        "xml_data" : {
            "data" : "$xmldata",
            "xsl" : "nest"
        },
jContent;
        else
            $js .= <<<jContent
        "plugins" : ["html_data", "themes", "ui", "cookies", "types", "sort"],
jContent;

        $js .= <<<jContent
        "core" : {
            "animation": 300,
            "initially_open" : [$initopen]
        },
        "themes" : {
            "theme" : "eclass",
            "dots" : true,
            "icons" : false
        },
        "ui" : {
            "select_limit" : 1
        },
        "types" : {
            "types" : {
                "nosel" : {
                    "hover_node" : false,
                    "select_node" : false
                }
            }
        },
        "sort" : function (a, b) {
            priorityA = this._get_node(a).attr("tabindex");
            priorityB = this._get_node(b).attr("tabindex");

            if (priorityA == priorityB)
                return this.get_text(a) > this.get_text(b) ? 1 : -1;
            else
                return priorityA < priorityB ? 1 : -1;
        }
    });

});

function validateNodePickerForm() {

    var nodeContainer = $( "#nodCnt" ).text();
    var inputKey = $( "#dialog-set-key" ).val();
    var inputVal = $( "#dialog-set-value" ).val();

    if (nodeContainer.length > 0 || (inputKey.length > 0 && inputVal.length > 0) )
        return true;
    else {
        alert('$langEmptyAddNode');
        return false;
    }
}

/* ]]> */
</script>
jContent;

        return $js;
    }

    /**
     * Build the necessary HTML code for the Node Picker UI element. This is a private method
     * utilized by the entry-point buildNodePicker(), buildCourseNodePicker() and buildUserNodePicker() methods.
     *
     * @param  array $options           - Options array for construction of the HTML code for the Node picker. Possible key value pairs are:
     * 'params'              => string  - Extra html tag parameters for the form's input elements (such as name)
     * 'defaults'            => array   - The ids of the already selected/added nodes. It can also be a single integer value, the code handles it automatically.
     * 'exclude'             => int     - The id of the subtree parent node we want to exclude from the result
     * 'tree'                => array   - Include (prepend) extra ArrayMap contents
     * 'useKey'              => string  - Key for return array, can be 'lft' or 'id'
     * 'where'               => string  - Extra filtering db query where arguments, mainly for selecting course/user allowing nodes
     * 'multiple'            => boolean - Flag controlling whether the picker will allow multiple tree nodes selection or just one (single)
     * 'allow_only_defaults' => boolean - Flag controlling whether the picker will mark non-default tree nodes as non-selectable ones
     * 'allowables'          => array   - The ids of the (parent) nodes whose subtrees are to be allowed, all others will be marked as non-selectables
     * 'xmlout'              => boolean - Flag controlling JSTree datasource
     * This method uses default values, but you should at least provide 'params'.
     *
     * @return string  $html - The returned HTML code
     */
    private function buildHtmlNodePicker($options) {
        global $themeimg, $langNodeAdd, $langNodeDel;

        $params = (array_key_exists('params', $options)) ? $options['params'] : '';
        $defaults = (array_key_exists('defaults', $options)) ? $options['defaults'] : '';
        $exclude = (array_key_exists('exclude', $options)) ? $options['exclude'] : null;
        $tree_array = (array_key_exists('tree', $options)) ? $options['tree'] : array('0' => 'Top');
        $useKey = (array_key_exists('useKey', $options)) ? $options['useKey'] : 'lft';
        $where = (array_key_exists('where', $options)) ? $options['where'] : '';
        $multiple = (array_key_exists('multiple', $options)) ? $options['multiple'] : false;
        $xmlout = (array_key_exists('xmlout', $options)) ? $options['xmlout'] : true;


        $html = '';
        $defs = (is_array($defaults)) ? $defaults : array(intval($defaults));

        if ($multiple) {
            $html .= '<div id="nodCnt">';

            if (is_array($defaults)) {
                $i = 0;
                foreach ($defaults as $def) {
                    $html .= '<p id="nd_' . $i . '">';
                    $html .= '<input type="hidden" ' . $params . ' value="' . $def . '" />';
                    $html .= $this->getFullPath($def);
                    $html .= '&nbsp;<a href="#nodCnt" onclick="$(\'#nd_' . $i . '\').remove(); $(\'#dialog-set-key\').val(null); $(\'#dialog-set-value\').val(null);"><img src="' . $themeimg . '/delete.png" title="' . $langNodeDel . '" alt="' . $langNodeDel . '"/></a></p>';
                    $html .= '</p>';
                    $i++;
                }
            }

            $html .= '</div>';
            $html .= '<div><p><a id="ndAdd" href="#add"><img src="' . $themeimg . '/add.png" title="' . $langNodeAdd . '" alt="' . $langNodeAdd . '"/></a></p></div>';

            // Unused for multi usecase, however present to use a unique generic JS event function
            $html .= '<input id="dialog-set-key" type="hidden" onchange="" />';
            $html .= '<input id="dialog-set-value" type="hidden" />';
        } else {
            if (isset($defs[0])) {
                if (isset($tree_array[$defs[0]]))
                    $def = $tree_array[$defs[0]];
                else
                    $def = $this->getNodeName($defs[0], $useKey);
            }
            else {
                $defs[0] = '';
                $def = '';
            }

            // satisfy JS code: getElementById().onchange()
            if (stristr($params, 'onchange') === false)
                $params .= ' onchange="" ';

            $html .= '<input id="dialog-set-key" type="hidden" ' . $params . ' value="' . $defs[0] . '" />';
            $onclick = (!empty($defs[0])) ? '$( \'#js-tree\' ).jstree(\'select_node\', \'#' . $defs[0] . '\', true, null);' : '';
            $html .= '<input id="dialog-set-value" type="text" onclick="' . $onclick . ' $( \'#dialog-form\' ).dialog( \'open\' );" onfocus="' . $onclick . ' $( \'#dialog-form\' ).dialog( \'open\' );" value="' . $def . '" />&nbsp;';
        }

        $html .= '<div id="dialog-form" title="' . $langNodeAdd . '"><fieldset><div id="js-tree">';
        if (!$xmlout)
            $html .= $this->buildTreeDataSource($options);
        $html .= '</div></fieldset></div>';

        return $html;
    }

    /**
     * Build a Tree Node Picker (UI). The method's output provides all necessary JS and HTML code.
     * The php script calling this should provide:
     * - jquery
     * - jquery-ui
     * - jstree
     * - (optional) onsubmit='return validateNodePickerForm();' for the form using the node Picker
     *
     * @param  array $options           - Options array for construction of the Node picker. Possible key value pairs are:
     * 'params'              => string  - Extra html tag parameters for the form's input elements (such as name)
     * 'defaults'            => array   - The ids of the already selected/added nodes. It can also be a single integer value, the code handles it automatically.
     * 'exclude'             => int     - The id of the subtree parent node we want to exclude from the result
     * 'tree'                => array   - Include (prepend) extra ArrayMap contents
     * 'useKey'              => string  - Key for return array, can be 'lft' or 'id'
     * 'where'               => string  - Extra filtering db query where arguments, mainly for selecting course/user allowing nodes
     * 'multiple'            => boolean - Flag controlling whether the picker will allow multiple tree nodes selection or just one (single)
     * 'allow_only_defaults' => boolean - Flag controlling whether the picker will mark non-default tree nodes as non-selectable ones
     * 'allowables'          => array   - The ids of the (parent) nodes whose subtrees are to be allowed, all others will be marked as non-selectables
     * 'xmlout'              => boolean - Flag controlling JSTree datasource
     * You must provide at least 'params' because this method does not use any default values.
     *
     * @return array - Return array containing (<js, html>) all necessary JS and HTML code
     */
    public function buildNodePicker($options) {
        $js = $this->buildJSNodePicker($options);
        $html = $this->buildHtmlNodePicker($options);

        return array($js, $html);
    }

    /**
     * Build a Tree Node Picker (UI) for attaching courses under tree nodes. The method's output provides all necessary JS and HTML code.
     * The php script calling this should provide:
     * - jquery
     * - jquery-ui
     * - jstree
     * - (optional) onsubmit='return validateNodePickerForm();' for the form using the node Picker
     *
     * @param  array $options           - Options array for construction of the Course Node picker. Possible key value pairs are:
     * 'params'              => string  - Extra html tag parameters for the form's input elements (such as name)
     * 'defaults'            => array   - The ids of the already selected/added nodes. It can also be a single integer value, the code handles it automatically.
     * 'exclude'             => int     - The id of the subtree parent node we want to exclude from the result
     * 'tree'                => array   - Include (prepend) extra ArrayMap contents
     * 'useKey'              => string  - Key for return array, can be 'lft' or 'id'
     * 'where'               => string  - Extra filtering db query where arguments, mainly for selecting course/user allowing nodes
     * 'multiple'            => boolean - Flag controlling whether the picker will allow multiple tree nodes selection or just one (single)
     * 'allow_only_defaults' => boolean - Flag controlling whether the picker will mark non-default tree nodes as non-selectable ones
     * 'allowables'          => array   - The ids of the (parent) nodes whose subtrees are to be allowed, all others will be marked as non-selectables
     * 'xmlout'              => boolean - Flag controlling JSTree datasource
     * You can omit all of the above since this method uses default values.
     *
     * @return array            - Return array containing (<js, html>) all necessary JS and HTML code
     */
    public function buildCourseNodePicker($options = array()) {
        $defaults = array('params' => 'name="department[]"',
            'tree' => null,
            'useKey' => 'id',
            'where' => 'AND node.allow_course = true',
            'multiple' => get_config('course_multidep'));
        $this->populateOptions($options, $defaults);

        return $this->buildNodePicker($options);
    }

    /**
     * Build a Tree Node Picker (UI) for attaching users under tree nodes. The method's output provides all necessary JS and HTML code.
     * The php script calling this should provide:
     * - jquery
     * - jquery-ui
     * - jstree
     * - (optional) onsubmit='return validateNodePickerForm();' for the form using the node Picker
     *
     * @param  array $options           - Options array for construction of the User Node picker. Possible key value pairs are:
     * 'params'              => string  - Extra html tag parameters for the form's input elements (such as name)
     * 'defaults'            => array   - The ids of the already selected/added nodes. It can also be a single integer value, the code handles it automatically.
     * 'exclude'             => int     - The id of the subtree parent node we want to exclude from the result
     * 'tree'                => array   - Include (prepend) extra ArrayMap contents
     * 'useKey'              => string  - Key for return array, can be 'lft' or 'id'
     * 'where'               => string  - Extra filtering db query where arguments, mainly for selecting course/user allowing nodes
     * 'multiple'            => boolean - Flag controlling whether the picker will allow multiple tree nodes selection or just one (single)
     * 'allow_only_defaults' => boolean - Flag controlling whether the picker will mark non-default tree nodes as non-selectable ones
     * 'allowables'          => array   - The ids of the (parent) nodes whose subtrees are to be allowed, all others will be marked as non-selectables
     * 'xmlout'              => boolean - Flag controlling JSTree datasource
     * You can omit all of the above since this method uses default values.
     *
     * @return array            - Return array containing (<js, html>) all necessary JS and HTML code
     */
    public function buildUserNodePicker($options = array()) {
        $defaults = array('params' => 'name="department[]"',
            'tree' => null,
            'useKey' => 'id',
            'where' => 'AND node.allow_course = true',
            'multiple' => get_config('user_multidep'));
        $this->populateOptions($options, $defaults);

        return $this->buildNodePicker($options);
    }

    /**
     * Build simple tree ArrayMap
     *
     * @param  string $where - Extra filtering db query where arguments, mainly for selecting course/user allowing nodes
     * @return array  $nodes - The return tree ArrayMap in the form of <node id, node name>
     */
    public function buildSimple($where = null) {
        $result = db_query("SELECT id, name FROM $this->dbtable " . $where . " order by id");

        $nodes = array();

        while ($row = mysql_fetch_assoc($result)) {
            $nodes[$row['id']] = self::unserializeLangField($row['name']);
        }

        return $nodes;
    }

    /**
     * Get a node's full breadcrump-style path
     *
     * @param  int     $nodeid    - the node's id whose full path we want
     * @param  boolean $skipfirst - whether the first parent is ommited from the resulted full path or not
     * @param  string  $href      - If provided (and not left empty or null), then the breadcrump is clickable towards the provided href with the node's id appended to it
     * @return string  $ret       - The return HTML output
     */
    public function getFullPath($nodeid, $skipfirst = true, $href = '') {
        $ret = "";

        if ($nodeid == null)
            return $ret;

        $node = mysql_fetch_assoc(db_query("SELECT * FROM $this->dbtable WHERE id = " . $nodeid));
        if (!$node)
            return $ret;

        $result = db_query("SELECT * FROM $this->dbtable WHERE lft < " . $node['lft'] . " AND rgt > " . $node['rgt'] . " ORDER BY lft ASC");

        $c = 0;
        $skip = 0;
        while ($parent = mysql_fetch_assoc($result)) {
            if ($skipfirst && $skip == 0) {
                $skip++;
                continue;
            }

            $ret .= ($c == 0) ? '' : '» ';
            $ret .= (empty($href)) ? self::unserializeLangField($parent['name']) . ' ' : "<a href='" . $href . $parent['id'] . "'>" . self::unserializeLangField($parent['name']) . "</a> ";
            $c++;
        }

        $ret .= ($c == 0) ? '' : '» ';
        $ret .= self::unserializeLangField($node['name']) . ' ';

        return $ret;
    }

    /**
     * Unserialize the given value and detect the proper localized string to returned.
     * Nodes' name field is stored as a serialized arraymap of the form <lang_code, value>, this method
     * fetches the proper value from such a serialized/localized string.
     *
     * @param  string $value - The value to act upon
     * @return string $value - The final unserialized/delocalized value
     */
    public static function unserializeLangField($value) {
        global $language;

        $values = @unserialize($value);

        if ($values !== false) {
            if (isset($values[$language]) && !empty($values[$language]))
                return $values[$language];
            else if (isset($values['en']) && !empty($values['en']))
                return $values['en'];
            else
                return array_shift($values);
        } else {
            return $value;
        }
    }

    /**
     * Populate an existing options array with default values.
     *
     * @param array $options  - Array containing options
     * @param array $defaults - Array containing default values
     */
    private function populateOptions(&$options, $defaults) {
        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $options)) {
                $options[$key] = $value;
            }
        }
    }

    /**
     * Builds an array containing all the subtree nodes of the (parent) nodes given
     *
     * @param  array $nodes - Array containing the (parent) nodes whose subtree nodes we want
     * @return array $subs  - Array containing the returned subtree nodes
     */
    public function buildSubtrees($nodes) {
        $subs = array();
        $ids = '';

        foreach ($nodes as $key => $id) {
            $ids .= $id . ',';
        }
        // remove last ',' from $ids
        $q = substr($ids, 0, -1);

        $sql = "SELECT node.id
                  FROM " . $this->dbtable . " AS node, " . $this->dbtable . " AS parent
                 WHERE node.lft BETWEEN parent.lft AND parent.rgt
                   AND parent.id IN ($q)
                 GROUP BY node.id
                 ORDER BY node.lft";

        $result = db_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $subs[] = $row['id'];
        }

        return $subs;
    }

    /**
     * Check if we can use Stored Procedures. Nothing more than a regular mysql version check for 5.0 or above/below.
     *
     * @return boolean
     */
    private function useProcedures() {
        global $mysqlMainDb;
        if (version_compare(mysql_get_server_info(), '5.0') >= 0) {
            $res = db_query("SHOW PROCEDURE STATUS WHERE Db = '" . $mysqlMainDb . "' AND Name = 'add_node'");
            if (mysql_num_rows($res) > 0)
                return true;
        }
        return false;
    }

    /**
     * Returns Hierarchy DB table
     * 
     * @return string
     */
    public function getDbtable() {
        return $this->dbtable;
    }

    /**
     * Build an HTML table containing navigation code for the given nodes
     * 
     * @param  array    $nodes         - The node ids whose children we want to navigate to
     * @param  string   $url           - The php script to call in the navigational URLs
     * @param  function $countCallback - An optional closure that will be used for the counting
     * @return string   $ret           - The returned HTML output
     */
    public function buildNodesNavigationHtml($nodes, $url, $countCallback = null) {
        global $langAvCours, $langAvCourses;

        $ret = '';
        $res = db_query("SELECT node.id, node.code, node.name 
                          FROM " . $this->dbtable . " AS node
                         WHERE node.id IN (" . implode(', ', $nodes) . ")");

        if (mysql_num_rows($res) > 0) {
            $ret .= "<table width='100%' class='tbl_border'>";
            $nodenames = array();
            $nodecodes = array();

            while ($node = mysql_fetch_array($res)) {
                $nodenames[$node['id']] = self::unserializeLangField($node['name']);
                $nodecodes[$node['id']] = $node['code'];
            }
            asort($nodenames);

            foreach ($nodenames as $key => $value) {
                $ret .= "<tr><td><a href='$url.php?fc=" . intval($key) . "'>" .
                        q($value) . "</a>&nbsp;&nbsp;<small>";
                if (strlen(q($nodecodes[$key])) > 0)
                    $ret .= "(" . q($nodecodes[$key]) . ")";

                $count = 0;
                foreach ($this->buildSubtrees(array(intval($key))) as $subnode) {
                    if ($countCallback !== null && is_callable($countCallback)) {
                        $count += $countCallback($subnode);
                    } else {
                        $n = db_query("SELECT COUNT(*)
                                         FROM course, course_department
                                        WHERE course.id = course_department.course
                                          AND course_department.department = " . intval($subnode));
                        $r = mysql_fetch_array($n);
                        $count += $r[0];
                    }
                }

                $ret .= "&nbsp;&nbsp;-&nbsp;&nbsp;" . intval($count) . "&nbsp;" . ($count == 1 ? $langAvCours : $langAvCourses) . "</small></td></tr>";
            }

            $ret .= "</table><br />";
        }

        return $ret;
    }

    /**
     * Build an HTML table containing navigation code for a node's children nodes
     * 
     * @param  int      $depid         - The node's id whose children we want to navigate to
     * @param  string   $url           - The php script to call in the navigational URLs
     * @param  function $countCallback - An optional closure that will be used for the counting
     * @return string   $ret           - The returned HTML output
     */
    public function buildDepartmentChildrenNavigationHtml($depid, $url, $countCallback = null) {
        // select subnodes of the next depth level
        $res = db_query("SELECT node.id FROM " . $this->dbtable . " AS node
                LEFT OUTER JOIN " . $this->dbtable . " AS parent ON parent.lft =
                                (SELECT MAX(S.lft)
                                FROM " . $this->dbtable . " AS S WHERE node.lft > S.lft
                                    AND node.lft < S.rgt)
                          WHERE parent.id = " . intval($depid));

        if (mysql_num_rows($res) > 0) {
            $nodes = array();
            while ($node = mysql_fetch_assoc($res)) {
                $nodes[] = $node['id'];
            }

            return $this->buildNodesNavigationHtml($nodes, $url, $countCallback);
        } else {
            return "";
        }
    }

    /**
     * Build an HTML Select box for selecting among the root nodes.
     * 
     * @param  int    $currentNode - The id of the current node
     * @return string $ret         - The returned HTML output
     */
    public function buildRootsSelection($currentNode, $params = '') {
        // select root nodes
        $res = db_query("SELECT node.id, node.name 
                          FROM " . $this->dbtable . " AS node
                         WHERE node.id IN (" . implode(', ', $this->buildRootsArray()) . ")");

        $ret = '';
        if (mysql_num_rows($res) > 0) {
            // locate root parent of current Node
            list($lft, $rgt) = $this->getNodeLftRgt($currentNode);
            $parent = $this->getRootParent($lft, $rgt);
            $parentId = ($parent != null) ? $parent['id'] : $currentNode;

            // construct array with names
            $nodenames = array();
            while ($node = mysql_fetch_array($res)) {
                $nodenames[$node['id']] = self::unserializeLangField($node['name']);
            }
            asort($nodenames);

            $ret .= "<select $params>";
            foreach ($nodenames as $id => $name) {
                $selected = ($id == $parentId) ? "selected=''" : '';
                $ret .= "<option value='" . intval($id) . "' $selected>" . q($name) . "</option>";
            }
            $ret .= "</select>";
        }

        return $ret;
    }

    /**
     * Build an HTML Form/Header box for selecting among the root nodes.
     * 
     * @param  int    $currentNode - The id of the current node
     * @return string $ret         - The returned HTML output
     */
    public function buildRootsSelectForm($currentNode) {
        global $langSelectFac;

        $ret = "<form name='depform' action='$_SERVER[SCRIPT_NAME]' method='get'>
                 <div id='operations_container'><ul id='opslist'>
                 <li>$langSelectFac:&nbsp;";
        $ret .= $this->buildRootsSelection($currentNode, "name='fc' onChange='document.depform.submit();'");
        $ret .= "</li></ul></div></form>";

        return $ret;
    }

}
