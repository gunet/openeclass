<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

/**
 * Eclass Hierarchy Coordinating Object.
 *
 * This class does not represent a hierarchy node or tree entity, but a core logic coordinating object
 * responsible for handling hierarchy and hierarchy node related tasks.
 */
class Hierarchy {

    private $dbtable;

    /**
     * Constructor - do not use any arguments for default eclass behaviour (standard db tables).
     *
     * @param string $dbtable - Name of table with tree nodes
     */
    public function __construct($dbtable = 'hierarchy') {
        $this->dbtable = $dbtable;
    }

    /**
     * Add a node to the tree.
     *
     * @param  string $name           - The new node name
     * @param  string $description    - The new node description
     * @param  int    $parentlft      - The new node's parent lft
     * @param  string $code           - The new node code
     * @param  int    $allow_course   - Flag controlling if courses are allowed to belong to this new node
     * @param  int    $allow_user     - Flag controlling if users are allowed to belong to this new node
     * @param  int    $order_priority - Special order priority for the node, the higher the value the higher place in the displayed order
     * @param  int    $visible        - Visibility flag for the new node
     * @param string $faculty_image   - faculty image
     * @return int    $ret            - The new node id
     */
    public function addNode($name, $description, $parentlft, $code, $allow_course, $allow_user, $order_priority, $visible, $faculty_image) {
        $ret = null;

        if ($this->useProcedures()) {
            $ret = Database::get()->query("CALL add_node(?s, ?s, ?d, ?s, ?d, ?d, ?d, ?d, ?s)", $name, $description, $parentlft, $code, $allow_course, $allow_user, $order_priority, $visible, $faculty_image)->lastInsertID;
        } else {
            $lft = $parentlft + 1;
            $rgt = $parentlft + 2;

            $this->shiftRight($parentlft);

            $query = "INSERT INTO hierarchy (name, description, lft, rgt, code, allow_course, allow_user, order_priority, visible, faculty_image) "
                    . "VALUES (?s, ?s, ?d, ?d, ?s, ?d, ?d, ?d, ?d, ?s)";
            $ret = Database::get()->query($query, $name, $description, $lft, $rgt, $code, $allow_course, $allow_user, $order_priority, $visible, $faculty_image)->lastInsertID;
        }

        return $ret;
    }

    /**
     * Update a tree node.
     *
     * @param int $id - The id of the node to update
     * @param string $name - The new name for the node
     * @param string $description - The new description for the node
     * @param int $nodelft - The new parent lft value
     * @param int $lft - The node's current lft value
     * @param int $rgt - The node's current rgt value
     * @param int $parentlft - The old parent lft value
     * @param string $code - The new code for the node
     * @param int $allow_course - Flag controlling if courses are allowed to belong to this node
     * @param int $allow_user - Flag controlling if users are allowed to belong to this node
     * @param int $order_priority - Special order priority for the node, the higher the value the higher place in the displayed order
     * @param int $visible - Visibility flag for the new node
     * @param string $faculty_image - Faculty image
     */
    public function updateNode($id, $name, $description, $nodelft, $lft, $rgt, $parentlft, $code, $allow_course, $allow_user, $order_priority, $visible, $faculty_image) {

        $cache1 = new FileCache('nodes',300);
        $cache1->clear();
        $cache2 = new FileCache('coursedeps',300);
        $cache2->clear();

        if ($this->useProcedures()) {
            Database::get()->query("CALL update_node(?d, ?s, ?s, ?d, ?d, ?d, ?d, ?s, ?d, ?d, ?d, ?d, ?s)", $id, $name, $description, $nodelft, $lft, $rgt, $parentlft, $code, $allow_course, $allow_user, $order_priority, $visible, $faculty_image);
        } else {
            $query = "UPDATE hierarchy SET name = ?s, description = ?s, lft = ?d, rgt = ?d,
                    code = ?s, allow_course = ?d, allow_user = ?d,
                    order_priority = ?d, visible = ?d, faculty_image = ?s WHERE id = ?d";
            Database::get()->query($query, $name, $description, $lft, $rgt, $code, $allow_course, $allow_user, $order_priority, $visible, $faculty_image, $id);

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

        global $webDir;

        $cache1 = new FileCache('nodes', 300);
        $cache1->clear();
        $cache2 = new FileCache('coursedeps', 300);
        $cache2->clear();

        if ($this->useProcedures()) {
            $row = Database::get()->querySingle("SELECT code, faculty_image FROM hierarchy WHERE id = ?d", $id);
            Database::get()->query("CALL delete_node(?d)", $id);
            unlink("$webDir/courses/facultyimg/$row->code/image/$row->faculty_image");
        } else {
            $row = Database::get()->querySingle("SELECT lft, rgt, code, faculty_image FROM hierarchy WHERE id = ?d", $id);
            unlink("$webDir/courses/facultyimg/$row->code/image/$row->faculty_image");
            Database::get()->query("DELETE FROM hierarchy WHERE id = ?d", $id);
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
        $query = "UPDATE hierarchy SET  lft = (lft - ?d) + ?d, rgt = (rgt - ?d) + ?d WHERE lft BETWEEN ?d AND ?d";
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
        $query = "UPDATE hierarchy SET rgt = rgt " . $action . " ?d WHERE rgt > ?d" . ($maxrgt > 0 ? " AND rgt <= " . $maxrgt : '');
        Database::get()->query($query, $shift, $node);

        $query = "UPDATE hierarchy SET lft = lft " . $action . " ?d WHERE lft > ?d" . ($maxrgt > 0 ? " AND lft <= " . $maxrgt : '');
        Database::get()->query($query, $shift, $node);
    }

    /**
     * Get maximum rgt value in the tree.
     *
     * @return int
     */
    public function getMaxRgt() {
        return Database::get()->querySingle("SELECT rgt FROM hierarchy ORDER BY rgt desc limit 1")->rgt;
    }

    /**
     * Get a child node's parent.
     *
     * @param  int   $lft - lft of child node
     * @param  int   $rgt - rgt of child node
     * @return object     - the object of the PDO query result
     */
    public function getParent($lft, $rgt) {
        return Database::get()->querySingle("SELECT * FROM hierarchy WHERE lft < ?d AND rgt > ?d ORDER BY lft DESC LIMIT 1", $lft, $rgt);
    }

    /**
     * Get a child node's root parent.
     *
     * @param  int   $lft - lft of child node
     * @param  int   $rgt - rgt of child node
     * @return object     - the object of the PDO query result
     */
    public function getRootParent($lft, $rgt) {
        return Database::get()->querySingle("SELECT * FROM hierarchy WHERE lft < ?d AND rgt > ?d ORDER BY lft ASC LIMIT 1", $lft, $rgt);
    }

    /**
     * Get a node's lft value.
     *
     * @param  int $id - The id of the node
     * @return int
     */
    public function getNodeLft($id) {
        $node = Database::get()->querySingle("SELECT lft FROM hierarchy WHERE id = ?d", $id);
        if ($node) {
            return intval($node->lft);
        }
        return 0;
    }

    /**
     * Get a node's lft and rgt value.
     *
     * @param  int $id - The id of the node
     * @return object
     */
    public function getNodeLftRgt($id) {
        return Database::get()->querySingle("SELECT lft, rgt FROM hierarchy WHERE id = ?d", $id);
    }

    /**
     * Get a node's (unserialized) name value.
     *
     * @param  int    $id    - The node's id
     * @return string        - The (unserialized) node's name
     */
    public function getNodeName($id) {
        if ($id === null || intval($id) <= 0) {
            return null;
        }

        $node = Database::get()->querySingle("SELECT name FROM hierarchy WHERE `id` = ?d", $id);
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
        Database::get()->query("DELETE FROM hierarchy WHERE lft BETWEEN ?d AND ?d", $lft, $rgt);
        Database::get()->query("UPDATE hierarchy  SET rgt = rgt - ?d WHERE rgt > ?d", $nodeWidth, $rgt);
        Database::get()->query("UPDATE hierarchy  SET lft = lft - ?d WHERE lft > ?d", $nodeWidth, $lft);
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

            Database::get()->query("UPDATE hierarchy SET rgt = (rgt - ?d) + ?d WHERE rgt > ?d", $maxrgt, $nodelft, $maxrgt);
            Database::get()->query("UPDATE hierarchy SET lft = (lft - ?d) + ?d WHERE lft > ?d", $maxrgt, $nodelft, $maxrgt);
        }
    }

    /**
     * Recursive function for getting all neighbour nodes (on the same depth level) according to a starting LEFT value.
     * i.e. useful for finding all roots (depth level 0)
     *
     * @param type $lft      - the starting point lft value, this has to be well known, i.e. lft = 1 certainly belongs to a root node
     * @param type $callback - optional callback function to call for each found node
     */
    private function getNeighbourNodesByLft($lft, $callback) {
        $hasMore = 0;
        $nextRootLft = 0;
        Database::get()->queryFunc("SELECT * FROM hierarchy WHERE lft = ?d", function($row) use ($callback, &$hasMore, &$nextRootLft) {
            if (is_callable($callback)) {
                $callback($row);
            }
            $nextRootLft = intval($row->rgt) + 1;
            $hasMore = Database::get()->querySingle("SELECT COUNT(id) AS count FROM hierarchy WHERE lft = ?d", $nextRootLft)->count;
        }, $lft);

        if ($hasMore > 0) {
            return $this->getNeighbourNodesByLft($nextRootLft, $callback);
        } else {
            return;
        }
    }

    /**
     * Compile an array with the root nodes (nodes of 0 depth).
     *
     * @return array
     */
    public function buildRootsArray() {
        $roots = array();
        $cb = function($row) use (&$roots) {
            $roots[] = $row;
        };
        $this->getNeighbourNodesByLft(1, $cb);
        return $roots;
    }

    /**
     * Locate immediate subordinates of parent node with given lft and their subtrees.
     *
     * @param  integer $searchLft - the lft of the parent node upon which the searching will apply
     * @return array
     */
    private function locateSubordinatesAndSubTrees($searchLft) {
        $subords = array();
        $subtrees = array();

        $currentSubIdx = 0;
        $searchSubLft = 0;
        $searchSubRgt = 0;

        $this->loopTree(function($node) use (&$searchLft, &$currentSubIdx, &$searchSubLft, &$searchSubRgt, &$subords, &$subtrees) {
            $nlft = intval($node->lft);
            // locate immediate subordinates of parent node by searching for specific lft values
            if ($nlft === $searchLft) {
                $subords[] = $node;
                $searchLft = intval($node->rgt) + 1;
                $currentSubIdx = intval($node->id);
                $searchSubLft = intval($node->lft);
                $searchSubRgt = intval($node->rgt);
            }
            if ($nlft >= $searchSubLft && $nlft <= $searchSubRgt) {
                $subtrees[$currentSubIdx][] = $node->id;
            }
        });

        return array($subords, $subtrees);
    }

    /**
     * Loop the whole tree ordered by lft and call callback for each node.
     *
     * @param  string $callback
     * @param  array    $nodesoverride - in case we can avoid the query
     * @return array    $nodes
     */
    public function loopTree($callback, $nodesoverride = array()) {
        if (count($nodesoverride) > 0) {
            $nodes = $nodesoverride;
        } else {
            $cache = new FileCache('nodes', 300);
            $nodes = $cache->get();
            if ($nodes === false) {
                $nodes = [];
                // get all nodes
                Database::get()->queryFunc("select * from hierarchy order by lft", function($row) use (&$nodes) {
                    $nodes[] = $row;
                });
                $cache->store($nodes);
            }
        }

        foreach($nodes as $node) {
            if (is_callable($callback)) {
                $callback($node);
            }
        }

        return $nodes;
    }

    /**
     * Compile an array with the root nodes (nodes of 0 depth) and their subtrees.
     *
     * @return array
     */
    public function buildRootsWithSubTreesArray() {
        return $this->locateSubordinatesAndSubTrees(1);
    }

    /**
     * Compile an array with the root node ids (nodes of 0 depth).
     *
     * @return array
     */
    public function buildRootIdsArray() {
        $roots = array();
        $cb = function($row) use (&$roots) {
            $roots[] = $row->id;
        };
        $this->getNeighbourNodesByLft(1, $cb);
        return $roots;
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
     * 'where'               => string  - Extra filtering db query where arguments, mainly for selecting course/user allowing nodes
     * 'multiple'            => boolean - Flag controlling whether the picker will allow multiple tree nodes selection or just one (single)
     * 'allow_only_defaults' => boolean - Flag controlling whether the picker will mark non-default tree nodes as non-selectable ones
     * 'allowables'          => array   - The ids of the (parent) nodes whose subtrees are to be allowed, all others will be marked as non-selectables
     *
     * @return string $js              - The returned JS code
     */
    private function buildJSNodePicker($options) {
        global $urlAppend, $langEmptyNodeSelect, $langEmptyAddNode, $langNodeDel;

        $params = $options['params'];
        $offset = (isset($options['defaults']) && is_array($options['defaults'])) ? count($options['defaults']) : 0; // The number of the parents that the editing child already belongs to (mainly for edit forms)
        $joptions = json_encode($options);

        if ($offset > 0) {
            $offset -= 1;
        }
        $langEmptyNodeSelect = js_escape($langEmptyNodeSelect);
        $js = <<<jContent
<script type="text/javascript">
/* <![CDATA[ */

var countnd = $offset;

$(document).ready(function() {

    $( "#ndAdd" ).click(function() {
        $( "#treeModal" ).modal( "show" );
    });

    $( "#nodCnt" ).on('click', "a[href='#nodCnt']", function (e) {
        e.preventDefault();
        $(this).find('span').tooltip('dispose').closest('p').remove();
        $('#dialog-set-key').val(null);
        $('#dialog-set-value').val(null);
    });

    $( ".treeModalClose" ).click(function() {
        $( "#treeModal" ).modal( "hide" );
    });

    $( "#treeModalSelect" ).click(function() {
        var newnode = $( "#js-tree" ).jstree("get_selected", true)[0];
        if (newnode !== undefined) {
            var newnodeid = newnode.id;
            var newnodename = newnode.text;
        }

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
            if (newnode === undefined) {
                alert("$langEmptyNodeSelect");
            } else {
                countnd += 1;
                $( "#nodCnt" ).append( '<p id="nd_' + countnd + '">'
                                     + '<input type="hidden" $params value="' + newnodeid + '" />'
                                     + newnodename
                                     + '&nbsp;<a href="#nodCnt" aria-label="$langNodeDel"><span class="fa-solid fa-xmark" data-bs-toggle="tooltip" data-bs-original-title="$langNodeDel" data-bs-placement="top"><\/span><\/a>'
                                     + '<\/p>');

                $( "#dialog-set-value" ).val(newnodename);
                $( "#dialog-set-key" ).val(newnodeid);
                document.getElementById('dialog-set-key').onchange();

                $( "#treeModal" ).modal( "hide" );
            }
        });
    });

    $( "#js-tree" ).jstree({
        "plugins" : ["sort"],
        "core" : {
            "data" : {
                "url" : "{$urlAppend}modules/hierarchy/nodes.php",
                "type" : "POST",
                "data" : function(node) {
                    return { "id" : node.id, "options" : $joptions };
                }
            },
            "multiple" : false,
            "themes" : {
                "name" : "proton",
                "dots" : true,
                "icons" : false
            }
        },
        "sort" : function (a, b) {
            priorityA = this.get_node(a).li_attr.tabindex;
            priorityB = this.get_node(b).li_attr.tabindex;

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
     * 'where'               => string  - Extra filtering db query where arguments, mainly for selecting course/user allowing nodes
     * 'multiple'            => boolean - Flag controlling whether the picker will allow multiple tree nodes selection or just one (single)
     * 'allow_only_defaults' => boolean - Flag controlling whether the picker will mark non-default tree nodes as non-selectable ones
     * 'allowables'          => array   - The ids of the (parent) nodes whose subtrees are to be allowed, all others will be marked as non-selectables
     * This method uses default values, but you should at least provide 'params'.
     *
     * @return string  $html - The returned HTML code
     */
    private function buildHtmlNodePicker($options) {
        global $langNodeAdd, $langNodeDel, $langCancel, $langSelect, $langClose;

        $params = (array_key_exists('params', $options)) ? $options['params'] : '';
        $defaults = (array_key_exists('defaults', $options)) ? $options['defaults'] : '';
        $exclude = (array_key_exists('exclude', $options)) ? $options['exclude'] : null;
        $tree_array = (array_key_exists('tree', $options)) ? $options['tree'] : array('0' => 'Top');
        $where = (array_key_exists('where', $options)) ? $options['where'] : '';
        $multiple = (array_key_exists('multiple', $options)) ? $options['multiple'] : false;
        $skip_preloaded_defaults = (array_key_exists('skip_preloaded_defaults', $options)) ? $options['skip_preloaded_defaults'] : false;


        $html = '';
        $defs = (is_array($defaults)) ? $defaults : array(intval($defaults));

        if ($multiple) {
            $html .= '<div id="nodCnt">';

            if (!$skip_preloaded_defaults && is_array($defaults)) {
                $i = 0;
                foreach ($defaults as $def) {
                    $html .= '<p id="nd_' . $i . '">';
                    $html .= '<input type="hidden" ' . $params . ' value="' . $def . '" />';
                    $html .= $this->getFullPath($def);
                    $html .= '&nbsp;<a href="#nodCnt" aria-label="'.$langNodeDel.'"><span class="fa-solid fa-xmark" data-bs-toggle="tooltip" data-bs-original-title="'.$langNodeDel.'" data-bs-placement="top"></span></a></p>';
                    $i++;
                }
            }

            $html .= '</div>';
            $html .= '<div><p><a id="ndAdd" href="#add" aria-label="'.q($langNodeAdd).'"><span class="fa fa-plus" data-bs-toggle="tooltip" data-bs-placement="top" title="'.q($langNodeAdd).'"></span></a></p></div>';

            // Unused for multi usecase, however present to use a unique generic JS event function
            $html .= '<input id="dialog-set-key" type="hidden" onchange="" />';
            $html .= '<input id="dialog-set-value" type="hidden" />';
        } else {
            if (isset($defs[0])) {
                if (isset($tree_array[$defs[0]])) {
                    $def = $tree_array[$defs[0]];
                } else {
                    $def = $this->getFullPath($defs[0], true, '');
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
            $html .= '<input class="form-control" id="dialog-set-value" type="text" onclick="' . $onclick . ' $( \'#treeModal\' ).modal(\'show\');" onfocus="' . $onclick . ' $(\'#treeModal\').modal(\'show\');" value="' . js_escape($def) . '" />';
        }

        $html .= '<div class="modal fade" id="treeModal" tabindex="-1" role="dialog" aria-labelledby="treeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"> 
                    <div class="modal-title" id="treeModalLabel">' . q($langNodeAdd) . '</div>
                    <button type="button" class="close treeModalClose" aria-label="'.$langClose.'"></button>
                   
                </div>
                <div class="modal-body">
                    <div id="js-tree"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn cancelAdminBtn treeModalClose">' . $langCancel . '</button>
                    <button type="button" class="btn submitAdminBtn ms-1" id="treeModalSelect">' . $langSelect . '</button>
                </div>
            </div>
        </div></div>';

        return $html;
    }


    private function buildHtmlNodePickerIndirect($options) {
        global $langNodeAdd, $langNodeDel, $langCancel, $langSelect, $langClose;

        $params = (array_key_exists('params', $options)) ? $options['params'] : '';
        $defaults = (array_key_exists('defaults', $options)) ? $options['defaults'] : '';
        $exclude = (array_key_exists('exclude', $options)) ? $options['exclude'] : null;
        $tree_array = (array_key_exists('tree', $options)) ? $options['tree'] : array('0' => 'Top');
        $where = (array_key_exists('where', $options)) ? $options['where'] : '';
        $multiple = array_key_exists('multiple', $options) && $options['multiple'];
        $skip_preloaded_defaults = (array_key_exists('skip_preloaded_defaults', $options)) ? $options['skip_preloaded_defaults'] : false;


        $html = '';
        $defs = (is_array($defaults)) ? $defaults : array(intval($defaults));

        if ($multiple) {
            $html .= '<div id="nodCnt">';

            if (!$skip_preloaded_defaults && is_array($defaults)) {
                $i = 0;
                foreach ($defaults as $def) {
                    $html .= '<p id="nd_' . $i . '">';
                    $html .= '<input type="hidden" ' . $params . ' value="' . $def . '" />';
                    $html .= $this->getFullPath(getDirectReference($def));
                    $html .= '&nbsp;<a href="#nodCnt" aria-label="'.$langNodeDel.'"><span class="fa-solid fa-xmark" data-bs-toggle="tooltip" data-bs-original-title="'.$langNodeDel.'" data-bs-placement="bottom"></span></a></p>';
                    $i++;
                }
            }

            $html .= '</div>';
            $html .= '<div><p><a id="ndAdd" href="#add" aria-label="'.q($langNodeAdd).'"><span class="fa fa-plus" data-bs-toggle="tooltip" data-bs-placement="bottom" title="'.q($langNodeAdd).'"></span></a></p></div>';

            // Unused for multi usecase, however present to use a unique generic JS event function
            $html .= '<input id="dialog-set-key" type="hidden" onchange="" />';
            $html .= '<input id="dialog-set-value" type="hidden" />';
        } else {
            if (isset($defs[0])) {
                if (isset($tree_array[$defs[0]])) {
                    $def = $tree_array[$defs[0]];
                } else {
                    $def = $this->getFullPath($defs[0], true, '');
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

            $html .= '<input id="dialog-set-key" type="hidden" ' . $params . ' value="' . getIndirectReference($defs[0]) . '" />';
            $onclick = (!empty($defs[0])) ? '$( \'#js-tree\' ).jstree(\'select_node\', \'#' . getIndirectReference($defs[0]) . '\', true, null);' : '';
            $html .= '<input class="form-control" id="dialog-set-value" type="text" onclick="' . $onclick . ' $( \'#treeModal\' ).modal(\'show\');" onfocus="' . $onclick . ' $(\'#treeModal\').modal(\'show\');" value="' . js_escape($def) . '" />&nbsp;';
        }

        $html .= '<div class="modal fade" id="treeModal" tabindex="-1" role="dialog" aria-labelledby="treeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                     <div class="modal-title" id="treeModalLabel">' . q($langNodeAdd) . '</div>
                    <button type="button" class="close treeModalClose" aria-label="'.$langClose.'"></button>
                   
                </div>
                <div class="modal-body">
                    <div id="js-tree"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn cancelAdminBtn treeModalClose">' . $langCancel . '</button>
                    <button type="button" class="btn submitAdminBtn ms-1" id="treeModalSelect">' . $langSelect . '</button>
                </div>
            </div>
        </div></div>';

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
     * 'where'               => string  - Extra filtering db query where arguments, mainly for selecting course/user allowing nodes
     * 'multiple'            => boolean - Flag controlling whether the picker will allow multiple tree nodes selection or just one (single)
     * 'allow_only_defaults' => boolean - Flag controlling whether the picker will mark non-default tree nodes as non-selectable ones
     * 'allowables'          => array   - The ids of the (parent) nodes whose subtrees are to be allowed, all others will be marked as non-selectables
     * You must provide at least 'params' because this method does not use any default values.
     *
     * @return array - Return array containing (<js, html>) all necessary JS and HTML code
     */
    public function buildNodePicker($options) {
        $js = $this->buildJSNodePicker($options);
        $html = $this->buildHtmlNodePicker($options);

        return array($js, $html);
    }

    public function buildNodePickerIndirect($options) {
        $js = $this->buildJSNodePicker($options);
        $html = $this->buildHtmlNodePickerIndirect($options);

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
     * 'where'               => string  - Extra filtering db query where arguments, mainly for selecting course/user allowing nodes
     * 'multiple'            => boolean - Flag controlling whether the picker will allow multiple tree nodes selection or just one (single)
     * 'allow_only_defaults' => boolean - Flag controlling whether the picker will mark non-default tree nodes as non-selectable ones
     * 'allowables'          => array   - The ids of the (parent) nodes whose subtrees are to be allowed, all others will be marked as non-selectables
     * You can omit all of the above since this method uses default values.
     *
     * @return array            - Return array containing (<js, html>) all necessary JS and HTML code
     */
    public function buildCourseNodePicker($options = array()) {
        $defaults = array('params' => 'name="department[]"',
            'tree' => null,
            'where' => 'AND node.allow_course = true',
            'multiple' => get_config('course_multidep'));
        $this->populateOptions($options, $defaults);

        return $this->buildNodePicker($options);
    }

    public function buildCourseNodePickerIndirect($options = array()) {
        $defaults = array('params' => 'name="department[]"',
            'tree' => null,
            'where' => 'AND node.allow_course = true',
            'multiple' => get_config('course_multidep'));
        $this->populateOptions($options, $defaults);

        return $this->buildNodePickerIndirect($options);
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
     * 'where'               => string  - Extra filtering db query where arguments, mainly for selecting course/user allowing nodes
     * 'multiple'            => boolean - Flag controlling whether the picker will allow multiple tree nodes selection or just one (single)
     * 'allow_only_defaults' => boolean - Flag controlling whether the picker will mark non-default tree nodes as non-selectable ones
     * 'allowables'          => array   - The ids of the (parent) nodes whose subtrees are to be allowed, all others will be marked as non-selectables
     * You can omit all of the above since this method uses default values.
     *
     * @return array            - Return array containing (<js, html>) all necessary JS and HTML code
     */
    public function buildUserNodePicker($options = array()) {
        $defaults = array('params' => 'name="department[]"',
            'tree' => null,
            'where' => 'AND node.allow_user = true',
            'multiple' => get_config('user_multidep'));
        $this->populateOptions($options, $defaults);

        return $this->buildNodePicker($options);
    }

    public function buildUserNodePickerIndirect($options = array()) {
        $defaults = array('params' => 'name="department[]"',
            'tree' => null,
            'where' => 'AND node.allow_user = true',
            'multiple' => get_config('user_multidep'));
        $this->populateOptions($options, $defaults);

        return $this->buildNodePickerIndirect($options);
    }

    /**
     * Build simple tree ArrayMap
     *
     * @param  string $where - Extra filtering db query where arguments, mainly for selecting course/user allowing nodes
     * @return array  $nodes - The return tree ArrayMap in the form of <node id, node name>
     */
    public function buildSimple($where = null) {
        $result = Database::get()->queryArray("SELECT id, name FROM hierarchy " . $where . " order by id");

        $nodes = array();

        foreach ($result as $row) {
            $nodes[$row->id] = self::unserializeLangField($row->name);
        }

        return $nodes;
    }

    /**
     * Get a node's full breadcrump-style path
     *
     * @param  int     $id        - The node's id
     * @param  boolean $skipfirst - whether the first parent is ommited from the resulted full path or not
     * @param  string  $href      - If provided (and not left empty or null), then the breadcrump is clickable towards the provided href with the node's id appended to it
     * @return string  $ret       - The return HTML output
     */
    public function getFullPath($id, $skipfirst = true, $href = '') {
        $ret = "";

        if ($id === null || intval($id) <= 0) {
            return $ret;
        }

        $node = Database::get()->querySingle("SELECT name, lft, rgt FROM hierarchy WHERE `id` = ?d", $id);
        if (!$node) {
            return $ret;
        }

        $result = Database::get()->queryArray("SELECT * FROM hierarchy WHERE lft < ?d AND rgt > ?d ORDER BY lft ASC", $node->lft, $node->rgt);

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
     * @brief Get the last component of a node's path
     * @param $id
     * @return string
     */
    public function getPath($id) {
        $ret = '';

        if ($id === null || intval($id) <= 0) {
            return $ret;
        }

        $node = Database::get()->querySingle("SELECT name, lft, rgt FROM hierarchy WHERE `id` = ?d", $id);
        if (!$node) {
            return $ret;
        }
        return self::unserializeLangField($node->name) . ' ';

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
     * @param  array $nodes    - Array containing the (parent) nodes whose subtree nodes we want
     * @param  array $allnodes - Array of all tree nodes to feed into loopTree() if it's already available
     * @return array $subs     - Array containing the returned subtree nodes
     */
    public function buildSubtrees($nodes, $allnodes = array()) {
        $subs = array();
        $nodelfts = array();
        $ids = '';


        if (count($nodes) <= 0) {
            return $subs;
        }

        foreach ($nodes as $key => $id) {
            $ids .= $id . ',';
        }
        // remove last ',' from $ids
        $q = substr($ids, 0, -1);

        Database::get()->queryFunc("SELECT node.id, node.lft FROM hierarchy AS node WHERE node.id IN ($q) ORDER BY node.lft", function($row) use (&$nodelfts) {
            $nodelfts[] = $row->lft;
        });

        if (count($nodelfts) > 0) {
            $currentIdx = 0;
            $searchLft = intval($nodelfts[$currentIdx]);
            $searchSubLft = 0;
            $searchSubRgt = 0;

            $this->loopTree(function($node) use(&$subs, &$nodelfts, &$searchLft, &$currentIdx, &$searchSubLft, &$searchSubRgt) {
                $nlft = intval($node->lft);
                if ($nlft === $searchLft) {
                    $currentIdx++;
                    $searchLft = (isset($nodelfts[$currentIdx])) ? intval($nodelfts[$currentIdx]) : 0;
                    $searchSubLft = intval($node->lft);
                    $searchSubRgt = intval($node->rgt);
                }
                if ($nlft >= $searchSubLft && $nlft <= $searchSubRgt) {
                    $subs[] = $node->id;
                }
            }, $allnodes);
        }

        return $subs;
    }

    /**
     * Builds an array containing all the subtree nodes of the (parent) nodes given
     *
     * @param  array $nodes    - Array containing the (parent) nodes whose subtree nodes we want
     * @param  array $allnodes - Array of all tree nodes to feed into loopTree() if it's already available
     * @return array $subs     - Array containing the returned subtree nodes
     */
    public function buildSubtreesFull($nodes, $allnodes = array()) {
        $subs = array();
        $nodelfts = array();
        $ids = '';

        if (count($nodes) <= 0) {
            return $subs;
        }

        foreach ($nodes as $key => $id) {
            $ids .= $id . ',';
        }
        // remove last ',' from $ids
        $q = substr($ids, 0, -1);

        Database::get()->queryFunc("SELECT node.id, node.lft FROM hierarchy AS node WHERE node.id IN ($q) ORDER BY node.lft", function($row) use (&$nodelfts) {
            $nodelfts[] = $row->lft;
        });

        if (count($nodelfts) > 0) {
            $currentIdx = 0;
            $searchLft = intval($nodelfts[$currentIdx]);
            $searchSubLft = 0;
            $searchSubRgt = 0;

            $this->loopTree(function($node) use(&$subs, &$nodelfts, &$searchLft, &$currentIdx, &$searchSubLft, &$searchSubRgt) {
                $nlft = intval($node->lft);
                if ($nlft === $searchLft) {
                    $currentIdx++;
                    $searchLft = (isset($nodelfts[$currentIdx])) ? intval($nodelfts[$currentIdx]) : 0;
                    $searchSubLft = intval($node->lft);
                    $searchSubRgt = intval($node->rgt);
                }
                if ($nlft >= $searchSubLft && $nlft <= $searchSubRgt) {
                    $subs[] = $node;
                }
            }, $allnodes);
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
     * Build an HTML table containing navigation code for the given nodes
     *
     * @param  array    $nodes         - The nodes (full sql row) whose children we want to navigate to
     * @param  string   $url           - The php script to call in the navigational URLs
     * @param  function $countCallback - An optional closure that will be used for the counting
     * @param  array    $options       - Display options (showEmpty => whether to display nodes with count == 0, respectVisibility => whether to hide nodes according to visible setting)
     * @return string   $ret           - The returned HTML output
     */
    public function buildNodesNavigationHtml($nodes, $url, $countCallback = null, $options = array('showEmpty' => true, 'respectVisibility' => true), $subtrees = array()) {
        global $langAvCours, $langAvCourses, $urlServer;
        $ret = '';

        if (count($nodes) > 0) {
            $nodesWK = array();
            $coursedeps = array();

            foreach ($nodes as $node) {
                $nodesWK[$node->id] = $node;
            }
            uasort($nodesWK, function ($a, $b) {
                $priorityA = intval($a->order_priority);
                $priorityB = intval($b->order_priority);
                $nameA = Hierarchy::unserializeLangField($a->name);
                $nameB = Hierarchy::unserializeLangField($b->name);

                if ($priorityA == $priorityB) {
                    if ($nameA == $nameB) {
                        return 0;
                    } else {
                        return ($nameA > $nameB) ? 1 : -1;
                    }
                } else {
                    return ($priorityA < $priorityB) ? 1 : -1;
                }
            });

            // course department counting
            //SELECT COUNT(course_department.id) AS count,department FROM course_department
            //              JOIN course ON course_department.course=course.id
            //              WHERE course.visible != 3 GROUP BY department;
            /*Database::get()->queryFunc("select department, count(id) as count from course_department group by department", function($row) use (&$coursedeps) {
                $coursedeps[intval($row->department)] = $row->count;
            });*/
            $cache = new FileCache('coursedeps',300);
            $coursedeps = $cache->get();
            if ($coursedeps === false) {
                $coursedeps = array();
                Database::get()->queryFunc("SELECT COUNT(course_department.id) AS count,department FROM course_department
                    JOIN course ON course_department.course = course.id
                    WHERE course.visible != " . COURSE_INACTIVE . " GROUP BY department",
                    function($row) use (&$coursedeps) {
                        $coursedeps[intval($row->department)] = $row->count;
                    });
                $cache->store($coursedeps);
            }
            foreach ($nodesWK as $key => $node) {
                $id = intval($key);
                $code = $node->code;
                $name = self::unserializeLangField($node->name);
                $description = self::unserializeLangField($node->description);
                $count = 0;

                if (isset($subtrees[$id])) {
                    foreach ($subtrees[$id] as $subkey => $subnode) {
                        // TODO: callback mechanism might need further optimization to avoid repeating extra sql query foreach subnode
                        if ($countCallback !== null && is_callable($countCallback)) {
                            $count += $countCallback($subnode);
                        } else {
                            // fast count using pre-loaded array in memory
                            $count += (isset($coursedeps[$subnode])) ? $coursedeps[$subnode] : 0;
                        }
                    }
                }

                if ($options['showEmpty'] || $count > 0) {
                    if (!$this->checkVisibilityRestrictions($id, $node->visible, $options)) {
                        continue;
                    }

                    $f_img = '';
                    $faqulty_sql = Database::get()->querySingle("SELECT name,code,faculty_image FROM hierarchy WHERE id = ?d", $id);
                    if ($faqulty_sql and !empty($faqulty_sql->faculty_image)) {
                        $faq_img_path = $urlServer . "courses/facultyimg/$faqulty_sql->code/image/$faqulty_sql->faculty_image";
                        $f_img = "<img src='$faq_img_path' style='width:80px; height:80px; object-fit:cover; border-radius: 5px;' alt='$faqulty_sql->name'>";
                    }

                    $ret .= "<li class='list-group-item element'>
                                <div class='table_td_header d-flex justify-content-between align-items-center flex-wrap gap-2'>
                                    <div class='d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                                        $f_img
                                        <a class='TextBold' href='$url.php?fc=" . $id . "'>" . q($name) . '</a>';
                                $ret .= (!empty($code)) ? "<span>(" . q($code) . ")</span>" : '';
                            $ret.="</div>";
                            $ret .= "<div class='vsmall-text text-end'>" . $count . "&nbsp;" . ($count == 1 ? $langAvCours : $langAvCourses) . "</div>
                                </div>";
                    $ret .= (!empty($description)) ? "<div class='table_td_body'>$description</div>" : '';
                    $ret .= "</li>";
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
     * @param  array    $options       - Display options (showEmpty => whether to display nodes with count == 0, respectVisibility => whether to hide nodes according to visible setting)
     * @return array    $ret           - An array containing the children count and the HTML output
     */
    public function buildDepartmentChildrenNavigationHtml($depid, $url, $countCallback = null, $options = array('showEmpty' => true, 'respectVisibility' => true)) {
        $parent = Database::get()->querySingle("select lft, rgt from hierarchy where id = ?d", $depid);
        if (!$parent) {
            return " ";
        }
        $searchLft = intval($parent->lft) + 1;

        list($children, $subtrees) = $this->locateSubordinatesAndSubTrees($searchLft);
        $chCnt = count($children);

        if ($chCnt > 0) {
            return array($chCnt, $this->buildNodesNavigationHtml($children, $url, $countCallback, $options, $subtrees));
        } else {
            return array($chCnt, " ");
        }
    }

    /**
     * Build an HTML Select box for selecting among the root nodes.
     *
     * @param  int    $currentNode - The id of the current node
     * @param  array  $options     - Display options (respectVisibility => whether to hide nodes according to visible setting)
     * @return string $ret         - The returned HTML output
     */
    public function buildRootsSelection($currentNode, $params = '', $options = array('respectVisibility' => true)) {
        global $uid;

        // select root nodes
        $res = Database::get()->queryArray("SELECT node.id, node.name, node.visible
                          FROM hierarchy AS node
                         WHERE node.id IN (" . implode(', ', $this->buildRootIdsArray()) . ")");

        $ret = '';
        if (count($res) > 0) {
            // locate root parent of current Node
            $node0 = $this->getNodeLftRgt($currentNode);
            $parent = $this->getRootParent($node0->lft, $node0->rgt);
            $parentId = ($parent) ? $parent->id : $currentNode;

            // construct array with names
            $nodenames = array();
            foreach ($res as $node) {
                if (!$this->checkVisibilityRestrictions($node->id, $node->visible, $options)) {
                    continue;
                }
                $nodenames[$node->id] = self::unserializeLangField($node->name);
            }
            asort($nodenames);

            if (count($res) < 6 ) {
                $ret .= "<div class='col-sm-12 mt-3 d-flex flex-column gap-2'>";
                foreach ($nodenames as $id => $name) {
                    $ret .= "<a href='$_SERVER[SCRIPT_NAME]?fc=" . intval($id) . "' class='text-decoration-none'>- " . q($name) . "</a>";
                }
                $ret .= "</div>";
            }
            else {
                $ret .= "<div class='col-sm-12 mt-3'><select class='form-select' $params>";
                foreach ($nodenames as $id => $name) {
                    $selected = ($id == $parentId) ? "selected=''" : '';
                    $ret .= "<option value='" . intval($id) . "' $selected>" . q($name) . "</option>";
                }
                $ret .= "</select></div>";
            }

        }

        return $ret;
    }

    /**
     * Build an HTML Form/Header box for selecting among the root nodes.
     *
     * @param  int    $currentNode - The id of the current node
     * @param  array  $options     - Display options (respectVisibility => whether to hide nodes according to visible setting)
     * @return string $ret         - The returned HTML output
     */
    public function buildRootsSelectForm($currentNode, $options = array('respectVisibility' => true)) {
        global $langSelectFac;

        $ret = "<div class='col-12 mb-4'>";
        $ret .= "<div class='form-wrapper form-edit border-0'>
                    <form class='form-horizontal' role='form' name='depform' action='$_SERVER[SCRIPT_NAME]' method='get'>";
        $ret .= "<div class='form-group'>";
        $ret .= "<div class='col-12 form-label mb-0'>$langSelectFac</div>";
        $ret .= $this->buildRootsSelection($currentNode, "name='fc' onChange='document.depform.submit();'", $options);
        $ret .= "</div></form></div></div>";
        return $ret;
    }

    /**
     * Check if a user can view or access a hierarchy node.
     *
     * @global int     $uid
     * @param  int     $nodeId
     * @param  int     $nodeVisibility
     * @param  array   $options         - Display options (respectVisibility => whether to hide nodes according to visible setting)
     * @return boolean $allow
     */
    public function checkVisibilityRestrictions($nodeId, $nodeVisibility, $options = array('respectVisibility' => true)) {
        global $uid;
        $allow = true;

        // check access restrictions
        if ($options['respectVisibility'] && ($uid != 1) && $nodeVisibility != NODE_OPEN) {
            // hide if anonymous user or eponymous but node is hidden
            if ($uid < 1 || $nodeVisibility == NODE_CLOSED) {
                $allow = false;
            } else {
                // for eponymous users check subscription status
                require_once('include/lib/user.class.php');
                $user = new User();
                $depIds = $user->getDepartmentIds($uid);
                if (!in_array($nodeId, $depIds)) {
                    $allow = false;
                }
            }
        }

        return $allow;
    }

}
