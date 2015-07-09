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
            $ret = Database::get()->query("CALL add_node(?s, ?d, ?s, ?d, ?d, ?d)", $name, $parentlft, $code, $allow_course, $allow_user, $order_priority)->lastInsertID;
        } else {
            $lft = $parentlft + 1;
            $rgt = $parentlft + 2;

            $this->shiftRight($parentlft);

            $query = "INSERT INTO " . $this->dbtable . " (name, lft, rgt, code, allow_course, allow_user, order_priority) "
                    . "VALUES (?s, ?d, ?d, ?s, ?d, ?d, ?d)";
            $ret = Database::get()->query($query, $name, $lft, $rgt, $code, $allow_course, $allow_user, $order_priority)->lastInsertID;
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
            $ret = Database::get()->query("CALL add_node_ext(?s, ?d, ?s, ?d, ?d, ?d, ?d, ?d)", $name, $parentlft, $code, $number, $generator, $allow_course, $allow_user, $order_priority)->lastInsertID;
        } else {
            $lft = $parentlft + 1;
            $rgt = $parentlft + 2;

            $this->shiftRight($parentlft);

            $query = "INSERT INTO " . $this->dbtable . " (name, lft, rgt, code, number, generator, allow_course, allow_user, order_priority) "
                    . "VALUES (?s, ?d, ?d, ?s, ?d, ?d, ?d, ?d, ?d)";
            $ret = Database::get()->query($query, $name, $lft, $rgt, $code, $number, $generator, $allow_course, $allow_user, $order_priority)->lastInsertID;
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
            Database::get()->query("CALL update_node(?d, ?s, ?d, ?d, ?d, ?d, ?s, ?d, ?d, ?d)", $id, $name, $nodelft, $lft, $rgt, $parentlft, $code, $allow_course, $allow_user, $order_priority);
        } else {
            $query = "UPDATE " . $this->dbtable . " SET name = ?s, lft = ?d, rgt = ?d,
                    code = ?s, allow_course = ?d, allow_user = ?d,
                    order_priority = ?d WHERE id = ?d";
            Database::get()->query($query, $name, $lft, $rgt, $code, $allow_course, $allow_user, $order_priority, $id);

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
            Database::get()->query("CALL delete_node(?d)", $id);
        } else {
            $row = Database::get()->querySingle("SELECT lft, rgt FROM " . $this->dbtable . " WHERE id = ?d", $id);
            Database::get()->query("DELETE FROM " . $this->dbtable . " WHERE id = ?d", $id);
            $this->delete($row->lft, $row->rgt);
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
        $query = "UPDATE " . $this->dbtable . " SET  lft = (lft - ?d) + ?d, rgt = (rgt - ?d) + ?d WHERE lft BETWEEN ?d AND ?d";
        Database::get()->query($query, ($lft - 1), $maxrgt, ($lft - 1), $maxrgt, $lft, $rgt);
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
        $query = "UPDATE " . $this->dbtable . " SET rgt = rgt " . $action . " ?d WHERE rgt > ?d" . ($maxrgt > 0 ? " AND rgt <= " . $maxrgt : '');
        Database::get()->query($query, $shift, $node);

        $query = "UPDATE " . $this->dbtable . " SET lft = lft " . $action . " ?d WHERE lft > ?d" . ($maxrgt > 0 ? " AND lft <= " . $maxrgt : '');
        Database::get()->query($query, $shift, $node);
    }

    /**
     * Get maximum rgt value in the tree.
     *
     * @return int
     */
    public function getMaxRgt() {
        return Database::get()->querySingle("SELECT rgt FROM " . $this->dbtable . " ORDER BY rgt desc limit 1")->rgt;
    }

    /**
     * Get a child node's parent.
     *
     * @param  int   $lft - lft of child node
     * @param  int   $rgt - rgt of child node
     * @return object     - the object of the PDO query result
     */
    public function getParent($lft, $rgt) {
        return Database::get()->querySingle("SELECT * FROM " . $this->dbtable . " WHERE lft < ?d AND rgt > ?d ORDER BY lft DESC LIMIT 1", $lft, $rgt);
    }

    /**
     * Get a child node's root parent.
     *
     * @param  int   $lft - lft of child node
     * @param  int   $rgt - rgt of child node
     * @return object     - the object of the PDO query result
     */
    public function getRootParent($lft, $rgt) {
        return Database::get()->querySingle("SELECT * FROM " . $this->dbtable . " WHERE lft < ?d AND rgt > ?d ORDER BY lft ASC LIMIT 1", $lft, $rgt);
    }

    /**
     * Get a node's lft value.
     *
     * @param  int $id - The id of the node
     * @return int
     */
    public function getNodeLft($id) {
        return intval(Database::get()->querySingle("SELECT lft FROM " . $this->dbtable . " WHERE id = ?d", $id)->lft);
    }

    /**
     * Get a node's lft and rgt value.
     *
     * @param  int $id - The id of the node
     * @return object
     */
    public function getNodeLftRgt($id) {
        return Database::get()->querySingle("SELECT lft, rgt FROM " . $this->dbtable . " WHERE id = ?d", $id);
    }

    /**
     * Get a node's (unserialized) name value.
     *
     * @param  int    $key    - The db query search pattern
     * @param  string $useKey - Match against either the id or the lft during the db query
     * @return string         - The (unserialized) node's name
     */
    public function getNodeName($key, $useKey = 'id') {
        if ($key === null || intval($key) <= 0) {
            return null;
        }

        $node = Database::get()->querySingle("SELECT name FROM " . $this->dbtable . " WHERE `" . $useKey . "` = ?d", $key);
        if ($node) {
            return self::unserializeLangField($node->name);
        }

        return null;
    }

    /**
     * Delete a subtree.
     *
     * @param int $lft - The subtree node lft value
     * @param int $rgt - The subtree node rgt value
     */
    public function delete($lft, $rgt) {
        $nodeWidth = $rgt - $lft + 1;
        Database::get()->query("DELETE FROM " . $this->dbtable . " WHERE lft BETWEEN ?d AND ?d", $lft, $rgt);
        Database::get()->query("UPDATE " . $this->dbtable . "  SET rgt = rgt - ?d WHERE rgt > ?d", $nodeWidth, $rgt);
        Database::get()->query("UPDATE " . $this->dbtable . "  SET lft = lft - ?d WHERE lft > ?d", $nodeWidth, $lft);
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

            Database::get()->query("UPDATE " . $this->dbtable . " SET rgt = (rgt - ?d) + ?d WHERE rgt > ?d", $maxrgt, $nodelft, $maxrgt);
            Database::get()->query("UPDATE " . $this->dbtable . " SET lft = (lft - ?d) + ?d WHERE lft > ?d", $maxrgt, $nodelft, $maxrgt);
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
            $row = Database::get()->querySingle("SELECT * FROM " . $this->dbtable . " WHERE id = ?d", $exclude);

            $query = "SELECT node.id, node.lft, node.name, node.code, node.allow_course, node.allow_user,
                             node.order_priority, COUNT(parent.id) - 1 AS depth
                     FROM " . $this->dbtable . " AS node, " . $this->dbtable . " AS parent
                    WHERE node.lft BETWEEN parent.lft AND parent.rgt
                    AND (node.lft < " . $row->lft . " OR node.lft > " . $row->rgt . ")
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

        $result = Database::get()->queryArray($query);

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

        foreach ($result as $row) {
            $prefix = '';
            if ($dashprefix) {
                for ($i = 0; $i < $row->depth; $i++) {
                    $prefix .= '&nbsp;-&nbsp;';
                }
            }

            $tree_array[$row->$useKey] = $prefix . self::unserializeLangField($row->name);
            $idmap[$row->$useKey] = $row->id;
            $depthmap[$row->$useKey] = $row->depth;
            $codemap[$row->$useKey] = $row->code;
            $allowcoursemap[$row->$useKey] = $row->allow_course;
            $allowusermap[$row->$useKey] = $row->allow_user;
            $orderingmap[$row->$useKey] = intval($row->order_priority);
        }

        return array($tree_array, $idmap, $depthmap, $codemap, $allowcoursemap, $allowusermap, $orderingmap);
    }

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
            if ($i == 0) {
                $start_depth = $current_depth = ($key != 0) ? $depthmap[$key] : 0;
            } else {
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
            ) {
                $rel = 'nosel';
            }
            if (!empty($rel)) {
                $rel = "rel='" . $rel . "'";
                $class = "class='nosel'";
            }

            $valcode = '';
            if ($codesuffix && strlen($codemap[$key]) > 0) {
                $valcode = ' (' . $codemap[$key] . ')';
            }

            // valid HTML requires ids starting with letters.
            // We can just use any 2 characters, all JS funcs use obj.attr("id").substring(2)
            if ($xmlout) {
                $out .= "<item id='nd" . $key . "' " . $rel . " tabindex='" . $orderingmap[$key] . "'><content><name " . $class . ">" . q($value . $valcode) . '<\/name><\/content><\/item>';
            } else {
                $out .= "<li id='nd" . $key . "' " . $rel . " tabindex='" . $orderingmap[$key] . "'><a href='#' " . $class . ">" . q($value . $valcode) . "</a></li>" . "\n";
            }

            $i++;
        }

        if (!$xmlout) {
            $out .= '</ul>';
        }

        // close remaining open tags
        $remain_depth = $current_depth - $start_depth;
        if ($remain_depth > 0) {
            for ($j = 0; $j < $remain_depth; $j++) {
                $out .= ($xmlout) ? '<\/item>' : '</li></ul>';
            }
        }

        if ($xmlout) {
            $out .= '<\/root>';
        }

        return $out;
    }

    /**
     * Compile an array with the root node ids (nodes of 0 depth).
     *
     * @return array
     */
    public function buildRootsArray() {
        $ret = array();
        $res = ($this->useProcedures()) ? Database::get()->queryArray("SELECT id FROM " . $this->dbdepth . " WHERE depth = 0") : Database::get()->queryArray("SELECT id FROM " . $this->view . " WHERE depth = 0");
        foreach ($res as $row) {
            $ret[] = $row->id;
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
        global $urlAppend, $langEmptyNodeSelect, $langEmptyAddNode, $langNodeDel;

        $params = $options['params'];
        $offset = (isset($options['defaults']) && is_array($options['defaults'])) ? count($options['defaults']) : 0; // The number of the parents that the editing child already belongs to (mainly for edit forms)
        $xmlout = (isset($options['xmlout']) && is_bool($options['xmlout'])) ? $options['xmlout'] : true;

        $xmldata = '';
        if ($xmlout) {
            $xmldata = $this->buildTreeDataSource($options);
        }
        $initopen = $this->buildJSTreeInitOpen();

        if ($offset > 0) {
            $offset -= 1;
        }

        $js = <<<jContent
<script type="text/javascript">
/* <![CDATA[ */

var countnd = $offset;

$(document).ready(function() {

    $( "#ndAdd" ).click(function() {
        $( "#treeModal" ).modal( "show" );
    });

    $( "a[href='#nodCnt']" ).click(function (e) {
        e.preventDefault();
        $(this).find('span').tooltip('destroy')
            .closest('p').remove();
        $('#dialog-set-key').val(null);
        $('#dialog-set-value').val(null);
    });

    $( ".treeModalClose" ).click(function() {
        $( "#treeModal" ).modal( "hide" );
    });

    $( "#treeModalSelect" ).click(function() {
        var newnode = $( "#js-tree" ).jstree("get_selected");
        var newnodeid = newnode.attr("id").substring(2);
        var newnodename = newnode.children("a").text();

        jQuery.getJSON('{$urlAppend}modules/hierarchy/nodefullpath.php', {nodeid : newnodeid})
        .done(function(data) {
            if (data.nodefullpath !== undefined && data.nodefullpath.length > 0) {
                newnodename = data.nodefullpath;
            }
        })
//        .fail(function(jqxhr, textStatus, error) {
//            // console.debug("jqxhr Request Failed: " + textStatus + ', ' + error);
//        })
        .always(function(dataORjqXHR, textStatus, jqXHRORerrorThrown) {
            if (!newnode.length) {
                alert("$langEmptyNodeSelect");
            } else {
                countnd += 1;
                $( "#nodCnt" ).append( '<p id="nd_' + countnd + '">'
                                     + '<input type="hidden" $params value="' + newnodeid + '" />'
                                     + newnodename
                                     + '&nbsp;<a href="#nodCnt"><span class="fa fa-times" data-toggle="tooltip" data-original-title="$langNodeDel" data-placement="top" title="$langNodeDel"><\/span><\/a>'
                                     + '<\/p>');

                $( "#dialog-set-value" ).val(newnodename);
                $( "#dialog-set-key" ).val(newnodeid);
                document.getElementById('dialog-set-key').onchange();

                $( "#treeModal" ).modal( "hide" );
            }
        });
    });

    $( "#js-tree" ).jstree({

jContent;

        if ($xmlout) {
            $xmldata = str_replace('"', '\"', $xmldata);
            $js .= <<<jContent
        "plugins" : ["xml_data", "themes", "ui", "cookies", "types", "sort"],
        "xml_data" : {
            "data" : "$xmldata",
            "xsl" : "nest"
        },
jContent;
        } else {
            $js .= <<<jContent
        "plugins" : ["html_data", "themes", "ui", "cookies", "types", "sort"],
jContent;
        }

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

            if (priorityA == priorityB) {
                return (this.get_text(a) > this.get_text(b)) ? 1 : -1;
            } else {
                return (priorityA < priorityB) ? 1 : -1;
            }
        }
    });

});

function validateNodePickerForm() {

    var nodeContainer = $( "#nodCnt" ).text();
    var inputKey = $( "#dialog-set-key" ).val();
    var inputVal = $( "#dialog-set-value" ).val();

    if (nodeContainer.length > 0 || (inputKey.length > 0 && inputVal.length > 0) ) {
        return true;
    } else {
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
        global $themeimg, $langNodeAdd, $langNodeDel, $langCancel, $langSelect;

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
                    $html .= '&nbsp;<a href="#nodCnt"><span class="fa fa-times" data-toggle="tooltip" data-original-title="'.$langNodeDel.'" data-placement="top" title="'.$langNodeDel.'"></span></a></p>';
                    $i++;
                }
            }

            $html .= '</div>';
            $html .= '<div><p><a id="ndAdd" href="#add"><i class="fa fa-plus" data-toggle="tooltip" data-placement="top" title="'.q($langNodeAdd).'"></i></a></p></div>';

            // Unused for multi usecase, however present to use a unique generic JS event function
            $html .= '<input id="dialog-set-key" type="hidden" onchange="" />';
            $html .= '<input id="dialog-set-value" type="hidden" />';
        } else {
            if (isset($defs[0])) {
                if (isset($tree_array[$defs[0]])) {
                    $def = $tree_array[$defs[0]];
                } else {
                    $def = $this->getFullPath($defs[0], true, '', $useKey);
                }
            }
            else {
                $defs[0] = '';
                $def = '';
            }

            // satisfy JS code: getElementById().onchange()
            if (stristr($params, 'onchange') === false) {
                $params .= ' onchange="" ';
            }

            $html .= '<input id="dialog-set-key" type="hidden" ' . $params . ' value="' . $defs[0] . '" />';
            $onclick = (!empty($defs[0])) ? '$( \'#js-tree\' ).jstree(\'select_node\', \'#' . $defs[0] . '\', true, null);' : '';
            $html .= '<input class="form-control" id="dialog-set-value" type="text" onclick="' . $onclick . ' $( \'#treeModal\' ).modal(\'show\');" onfocus="' . $onclick . ' $(\'#treeModal\').modal(\'show\');" value="' . $def . '" />&nbsp;';
        }

        $html .= '<div class="modal fade" id="treeModal" tabindex="-1" role="dialog" aria-labelledby="treeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close treeModalClose"><span aria-hidden="true">&times;</span><span class="sr-only">' . $langCancel . '</span></button>
                    <h4 class="modal-title" id="treeModalLabel">' . q($langNodeAdd) . '</h4>
                </div>
                <div class="modal-body">
                    <div id="js-tree">';
        if (!$xmlout) {
            $html .= $this->buildTreeDataSource($options);
        }
        $html .= '</div></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default treeModalClose">' . $langCancel . '</button>
                        <button type="button" class="btn btn-primary" id="treeModalSelect">' . $langSelect . '</button>
                    </div>
                </div>
            </div>
        </div>';

        return $html;
    }

    /**
     * Build a Tree Node Picker (UI). The method's output provides all necessary JS and HTML code.
     * The php script calling this should provide:
     * - jquery
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
            'where' => 'AND node.allow_user = true',
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
        $result = Database::get()->queryArray("SELECT id, name FROM $this->dbtable " . $where . " order by id");

        $nodes = array();

        foreach ($result as $row) {
            $nodes[$row->id] = self::unserializeLangField($row->name);
        }

        return $nodes;
    }

    /**
     * Get a node's full breadcrump-style path
     *
     * @param  int     $key       - The db query search pattern
     * @param  boolean $skipfirst - whether the first parent is ommited from the resulted full path or not
     * @param  string  $href      - If provided (and not left empty or null), then the breadcrump is clickable towards the provided href with the node's id appended to it
     * @param  string  $useKey    - Match against either the id or the lft during the db query
     * @return string  $ret       - The return HTML output
     */
    public function getFullPath($key, $skipfirst = true, $href = '', $useKey = 'id') {
        $ret = "";

        if ($key === null || intval($key) <= 0) {
            return $ret;
        }

        $node = Database::get()->querySingle("SELECT name, lft, rgt FROM " . $this->dbtable . " WHERE `" . $useKey . "` = ?d", $key);
        if (!$node) {
            return $ret;
        }

        $result = Database::get()->queryArray("SELECT * FROM $this->dbtable WHERE lft < ?d AND rgt > ?d ORDER BY lft ASC", $node->lft, $node->rgt);

        $c = 0;
        $skip = 0;
        foreach ($result as $parent) {
            if ($skipfirst && $skip == 0) {
                $skip++;
                continue;
            }

            $ret .= ($c == 0) ? '' : '» ';
            $ret .= (empty($href)) ? self::unserializeLangField($parent->name) . ' ' : "<a href='" . $href . $parent->id . "'>" . self::unserializeLangField($parent->name) . "</a> ";
            $c++;
        }

        $ret .= ($c == 0) ? '' : '» ';
        $ret .= self::unserializeLangField($node->name) . ' ';

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
            if (isset($values[$language]) && !empty($values[$language])) {
                return $values[$language];
            } else if (isset($values['en']) && !empty($values['en'])) {
                return $values['en'];
            } else {
                return array_shift($values);
            }
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

        $result = Database::get()->queryArray($sql);
        foreach ($result as $row) {
            $subs[] = $row->id;
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
        $res = Database::get()->querySingle("SHOW PROCEDURE STATUS WHERE Db = ?s AND Name = 'add_node'", $mysqlMainDb);
        return ($res) ? true : false;
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
     * @param  bool     $showEmpty     - Whether to display nodes with count == 0
     * @return string   $ret           - The returned HTML output
     */
    public function buildNodesNavigationHtml($nodes, $url, $countCallback = null, $showEmpty = true) {
        global $langAvCours, $langAvCourses;

        $ret = '';
        $res = Database::get()->queryArray("SELECT node.id, node.code, node.name
                          FROM " . $this->dbtable . " AS node
                         WHERE node.id IN (" . implode(', ', $nodes) . ")");

        if (count($res) > 0) {
            $nodenames = array();
            $nodecodes = array();

            foreach ($res as $node) {
                $nodenames[$node->id] = self::unserializeLangField($node->name);
                $nodecodes[$node->id] = $node->code;
            }
            asort($nodenames);

            foreach ($nodenames as $key => $value) {
                $count = 0;
                foreach ($this->buildSubtrees(array(intval($key))) as $subnode) {
                    if ($countCallback !== null && is_callable($countCallback)) {
                        $count += $countCallback($subnode);
                    } else {
                        $n = Database::get()->querySingle("SELECT COUNT(*) AS count
                                         FROM course, course_department
                                        WHERE course.id = course_department.course
                                          AND course_department.department = ?d", intval($subnode))->count;
                        $count += $n;
                    }
                }

                if ($showEmpty or $count > 0) {
                    $ret .= "<li class='list-group-item' ><a href='$url.php?fc=" . intval($key) . "'>" .
                            q($value) . '</a>';
                    if (strlen(q($nodecodes[$key])) > 0) {
                        $ret .= "&nbsp;(" . q($nodecodes[$key]) . ")";
                    }
                    $ret .= "<small>&nbsp;&nbsp;-&nbsp;&nbsp;" . intval($count) . "&nbsp;" .
                        ($count == 1 ? $langAvCours : $langAvCourses) . "</small></li>";
                }
            }
        }

        return $ret;
    }

    /**
     * Build an HTML table containing navigation code for a node's children nodes
     *
     * @param  int      $depid         - The node's id whose children we want to navigate to
     * @param  string   $url           - The php script to call in the navigational URLs
     * @param  function $countCallback - An optional closure that will be used for the counting
     * @param  bool     $showEmpty     - Whether to display nodes with count == 0
     * @return string   $ret           - The returned HTML output
     */
    public function buildDepartmentChildrenNavigationHtml($depid, $url, $countCallback = null, $showEmpty = true) {

        global $langNoCoursesAvailable;


        // select subnodes of the next depth level
        $res = Database::get()->queryArray("SELECT node.id FROM " . $this->dbtable . " AS node
                LEFT OUTER JOIN " . $this->dbtable . " AS parent ON parent.lft =
                                (SELECT MAX(S.lft)
                                FROM " . $this->dbtable . " AS S WHERE node.lft > S.lft
                                    AND node.lft < S.rgt)
                          WHERE parent.id = ?d", intval($depid));

        if (count($res) > 0) {
            $nodes = array();
            foreach ($res as $node) {
                $nodes[] = $node->id;
            }

            return $this->buildNodesNavigationHtml($nodes, $url, $countCallback, $showEmpty);
        } else {
            return " ";
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
        $res = Database::get()->queryArray("SELECT node.id, node.name
                          FROM " . $this->dbtable . " AS node
                         WHERE node.id IN (" . implode(', ', $this->buildRootsArray()) . ")");

        $ret = '';
        if (count($res) > 0) {
            // locate root parent of current Node
            $node0 = $this->getNodeLftRgt($currentNode);
            $parent = $this->getRootParent($node0->lft, $node0->rgt);
            $parentId = ($parent) ? $parent->id : $currentNode;

            // construct array with names
            $nodenames = array();
            foreach ($res as $node) {
                $nodenames[$node->id] = self::unserializeLangField($node->name);
            }
            asort($nodenames);

            $ret .= "<div class='col-sm-9'><select class='form-control' $params>";
            foreach ($nodenames as $id => $name) {
                $selected = ($id == $parentId) ? "selected=''" : '';
                $ret .= "<option value='" . intval($id) . "' $selected>" . q($name) . "</option>";
            }
            $ret .= "</select></div>";
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

        $ret  = "<div class='row'><div class='col-xs-12'>";
        $ret .= "<div class='form-wrapper'><form class='form-horizontal' role='form' name='depform' action='$_SERVER[SCRIPT_NAME]' method='get'>";
        $ret .= "<div class='form-group' style='margin-bottom: 0px;'>";
        $ret .= "<label class='col-sm-3 control-label'>$langSelectFac:</label>";
        $ret .= $this->buildRootsSelection($currentNode, "name='fc' onChange='document.depform.submit();'");
        $ret .= "</div></form></div></div></div>";
        return $ret;
    }

}
