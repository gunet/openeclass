<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * ======================================================================== 
 */

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
header('Content-Type: application/json; charset=utf-8');

$tree = new Hierarchy();
$requestId = intval($_REQUEST['id']);
$nodes = array();
$data = array();

// initialize options
$options = (isset($_POST['options']) && is_array($_POST['options'])) ? $_POST['options'] : array();
$tree_array = (array_key_exists('tree', $options) && is_array($options['tree']) && $options['tree'] != null) ? $options['tree'] : array();
$exclude = (array_key_exists('exclude', $options)) ? intval($options['exclude']) : null;
$where = (array_key_exists('where', $options)) ? $options['where'] : '';
$codesuffix = (array_key_exists('codesuffix', $options) && $options['codesuffix'] === 'true');
$defaults = (array_key_exists('defaults', $options)) ? $options['defaults'] : array();
$allowables = (array_key_exists('allowables', $options) && is_array($options['allowables']) && $options['allowables'] != null) ? $options['allowables'] : array();
$allow_only_defaults = (array_key_exists('allow_only_defaults', $options) && $options['allow_only_defaults'] === 'true');
$mark_allow_user = (strstr($where, 'allow_user') !== false);
$mark_allow_course = (strstr($where, 'allow_course') !== false);

// preload all nodes
$allnodes = array();
Database::get()->queryFunc("select * from hierarchy order by lft", function($row) use (&$allnodes) {
    $allnodes[] = $row;
});

// initialize vars
$defs = (is_array($defaults)) ? $defaults : array(intval($defaults));
$subdefs = ($allow_only_defaults) ? $tree->buildSubtrees($defs, $allnodes) : array();
$suballowed = ($allowables != null) ? $tree->buildSubtrees($allowables, $allnodes) : null;
$excludeLft = 0;
$excludeRgt = 0;
$fetchNodeById = "select * from hierarchy where id = ?d";

if ($requestId <= 0) {
    $nodes = $tree->buildRootsArray();
    
    foreach ($tree_array as $key => $value) {
        $data[] = array(
            "id" => $key,
            "text" => $value
        );
    }
} else {
    // calculate 1st child's lft value, if exists
    $searchLft = 0;
    $parent = Database::get()->querySingle($fetchNodeById, $requestId);
    if ($parent && ($parent->rgt - $parent->lft) > 1) {
        $searchLft = intval($parent->lft) + 1;
    }
    
    // get 1st child's all neighbour elements
    if ($searchLft > 0) {
        $tree->loopTree(function($node) use (&$nodes, &$searchLft) {
            $nlft = intval($node->lft);
            if ($nlft === $searchLft) {
                $nodes[] = $node;
                $searchLft = intval($node->rgt) + 1;
            }
        }, $allnodes);
    }
    
    if ($exclude != null) {
        $exnode = Database::get()->querySingle($fetchNodeById, $exclude);
        if ($exnode) {
            $excludeLft = intval($exnode->lft);
            $excludeRgt = intval($exnode->rgt);
        }
    }
}

foreach ($nodes as $node) {
    $disabled = false;
    if (($mark_allow_course && !$node->allow_course) ||
        ($mark_allow_user && !$node->allow_user) ||
        ($allow_only_defaults && !in_array($node->id, $subdefs)) ||
        ($suballowed != null && !in_array($node->id, $suballowed))) {
        $disabled = true;
    }
    // exclude
    if ($node->lft >= $excludeLft && $node->rgt <= $excludeRgt) {
        $disabled = true;
    }
    
    $class = $disabled ? 'nosel' : '';
    $valcode = ($codesuffix && strlen($node->code) > 0) ? ' (' . $node->code . ')' : '';
    
    $data[] = array(
        "id" => $node->id,
        "text" => Hierarchy::unserializeLangField($node->name) . $valcode,
        "state" => array("disabled" => $disabled),
        "children" => (($node->rgt - $node->lft) > 1),
        "li_attr" => array(
            "tabindex" => intval($node->order_priority)
        ),
        "a_attr" => array(
            "class" => $class
        )
    );
}

echo json_encode($data);
exit();
