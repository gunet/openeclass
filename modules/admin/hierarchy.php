<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */

/* ===========================================================================
  hierarchy.php
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
  ==============================================================================
  @Description: Manage Hierarchy

  This script allows the administrator to list the available hierarchical
  data tree nodes, edit/move them, delete them or add new ones.

  ============================================================================== */

$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$user = new User();

load_js('jstree3');

$toolName = $langHierarchyActions;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

if (isset($_GET['action'])) {
    $navigation[] = array('url' => $_SERVER['SCRIPT_NAME'], 'name' => $langHierarchyActions);
    switch ($_GET['action']) {
        case 'add':
            $pageName = $langNodeAdd;
            break;
        case 'delete':
            $pageName = $langNodeDel;
            break;
        case 'edit':
            $pageName = $langNodeEdit;
            break;
    }
}

// handle current lang missing from active langs
if (!in_array($language, $session->active_ui_languages)) {
    array_unshift($session->active_ui_languages, $language);
}

// link to add a new node
if (!isset($_REQUEST['action'])) {
    $data['action_bar'] = action_bar(array(
            array('title' => $langAdd,
                'url' => "$_SERVER[SCRIPT_NAME]?action=add",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success'),
        array('title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));
} else {
    $data['action_bar'] = action_bar(array(            
            array('title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));
}

// Display all available nodes
if (!isset($_GET['action'])) {
    // Count available nodes
    $data['nodesCount'] = Database::get()->querySingle("SELECT COUNT(*) as count from hierarchy")->count;

    $query = "SELECT max(depth) as maxdepth FROM (SELECT  COUNT(parent.id) - 1 AS depth
                FROM `hierarchy` AS node, `hierarchy` AS parent
                    WHERE node.lft BETWEEN parent.lft AND parent.rgt
                    GROUP BY node.id
                    ORDER BY node.lft) AS hierarchydepth";
    $data['maxdepth'] = Database::get()->querySingle($query)->maxdepth;

    $options = array('codesuffix' => true, 'defaults' => $user->getDepartmentIds($uid), 'allow_only_defaults' => (!$is_admin));
    $joptions = json_encode($options);

    $head_content .= <<<hContent
<script type="text/javascript">
/* <![CDATA[ */

$(function() {
            
    $( "#js-tree" ).jstree({
        "plugins" : ["sort", "contextmenu"],
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
        },
        "contextmenu": {
            "select_node" : true,
            "items" : customMenu
        }
    })
    .delegate("a", "click.jstree", function (e) { 
        $("#js-tree").jstree("show_contextmenu", e.currentTarget); 
    });

});

function customMenu(node) {

    var items = {
        editItem: {
            label: "$langEdit",
            action: function (obj) { document.location.href='?action=edit&id=' + node.id; }
        },
        deleteItem: {
            label: "$langDelete",
            action: function (obj) { if (confirm('$langConfirmDelete')) document.location.href='?action=delete&id=' + node.id; }
        }
    };

    if (node.a_attr.class == 'nosel') {
        delete items.editItem;
        delete items.deleteItem;
    }

    return items;
}

/* ]]> */
</script>
hContent;

    $view = 'admin.courses.hierarchy.index';
}
// Add a new node
elseif (isset($_GET['action']) && $_GET['action'] == 'add') {
    if (isset($_POST['add'])) {

        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) { csrf_token_error(); }
        $code = $_POST['code'];

        $names = array();
        foreach ($session->active_ui_languages as $key => $langcode) {
            $n = (isset($_POST['name-' . $langcode])) ? $_POST['name-' . $langcode] : null;
            if (!empty($n)) {
                $names[$langcode] = $n;
            }
        }
        $name = serialize($names);

        $descriptions = array();
        foreach ($session->active_ui_languages as $key => $langcode) {
            $d = (isset($_POST['description-' . $langcode])) ? $_POST['description-' . $langcode] : null;
            if (!empty($d)) {
                $descriptions[$langcode] = $d;
            }
        }
        $description = serialize($descriptions);

        $allow_course = (isset($_POST['allow_course'])) ? 1 : 0;
        $allow_user = (isset($_POST['allow_user'])) ? 1 : 0;
        $order_priority = (isset($_POST['order_priority']) && !empty($_POST['order_priority'])) ? intval($_POST['order_priority']) : 'null';
        $visible = (isset($_POST['visible'])) ? intval($_POST['visible']) : 2;
        if ($visible < 0 || $visible > 2) {
            $visible = 2;
        }
        // Check for empty fields
        if (empty($names)) {
            Session::Messages($langEmptyNodeName, 'alert-danger');
            redirect_to_home_page($_SERVER['SCRIPT_NAME'] . "?a=1");
        }
        // Check for greek letters
        elseif (!empty($code) && !preg_match("/^[A-Z0-9a-z_-]+$/", $code)) {
            Session::Messages($langGreekCode, 'alert-danger');
            redirect_to_home_page($_SERVER['SCRIPT_NAME'] . "?a=1");            
        } else {
            // OK Create the new node
            $pid = intval(getDirectReference($_POST['parentid']));
            validateParentId($pid, isDepartmentAdmin());
            $tree->addNode($name, $description, $tree->getNodeLft($pid), $code, $allow_course, $allow_user, $order_priority, $visible);
            Session::Messages($langAddSuccess, 'alert-success');
            redirect_to_home_page("modules/admin/hierarchy.php");               
        }
    } else {
        $data['visibleChecked'] = array(NODE_CLOSED => '', NODE_SUBSCRIBED => '', NODE_OPEN => '');
        $data['visibleChecked'][intval(NODE_OPEN)] = " checked='checked'";
        list($js, $html) = $tree->buildNodePickerIndirect(array('params' => 'name="parentid"', 'tree' => array('0' => 'Top'), 'multiple' => false, 'defaults' => $user->getDepartmentIds($uid), 'allow_only_defaults' => (!$is_admin)));
        $head_content .= $js;
        $data['html'] = $html;
        $view = 'admin.courses.hierarchy.create';
    }    
}
// Delete node
elseif (isset($_GET['action']) and $_GET['action'] == 'delete') {
    $id = intval(getDirectReference($_GET['id']));
    validateNode($id, isDepartmentAdmin());

    // locate the lft and rgt of the node we want to delete
    $node = Database::get()->querySingle("SELECT lft, rgt from hierarchy WHERE id = ?d", $id);

    if ($node !== false) {

        // locate the subtree of the node we want to delete. the subtree contains the node itself
        $subres = Database::get()->queryArray("SELECT id FROM hierarchy WHERE lft BETWEEN ?d AND ?d", intval($node->lft), intval($node->rgt));
        $c = 0;

        // for each subtree node, check if it has belonging children (courses, users)
        foreach ($subres as $subnode) {
            $c += Database::get()->querySingle("SELECT COUNT(*) AS count FROM course_department WHERE department = ?d", intval($subnode->id))->count;
            $c += Database::get()->querySingle("SELECT COUNT(*) AS count FROM user_department WHERE department = ?d", intval($subnode->id))->count;
        }

        if ($c > 0) {
            // The node cannot be deleted
            Session::Messages("$langNodeProErase<br>$langNodeNoErase", 'alert-danger');
        } else {
            // The node can be deleted
            $tree->deleteNode($id);
            Session::Messages($langNodeErase, 'alert alert-success');
        }
        redirect_to_home_page('modules/admin/hierarchy.php');
    }    
}
// Edit a node
elseif (isset($_GET['action']) and $_GET['action'] == 'edit') {
    $id = intval(getDirectReference($_REQUEST['id']));
    validateNode($id, isDepartmentAdmin());

    if (isset($_POST['edit'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) { csrf_token_error(); }
        checkSecondFactorChallenge();
        // Check for empty fields

        $names = array();
        foreach ($session->active_ui_languages as $key => $langcode) {
            $n = (isset($_POST['name-' . $langcode])) ? $_POST['name-' . $langcode] : null;
            if (!empty($n)) {
                $names[$langcode] = $n;
            }
        }
        $name = serialize($names);

        $descriptions = array();
        foreach ($session->active_ui_languages as $key => $langcode) {
            $d = (isset($_POST['description-' . $langcode])) ? $_POST['description-' . $langcode] : null;
            if (!empty($d)) {
                $descriptions[$langcode] = $d;
            }
        }
        $description = serialize($descriptions);

        $code = $_POST['code'];
        $allow_course = (isset($_POST['allow_course'])) ? 1 : 0;
        $allow_user = (isset($_POST['allow_user'])) ? 1 : 0;
        $order_priority = (isset($_POST['order_priority']) && !empty($_POST['order_priority'])) ? intval($_POST['order_priority']) : 'null';
        $visible = (isset($_POST['visible'])) ? intval($_POST['visible']) : 2;
        if ($visible < 0 || $visible > 2) {
            $visible = 2;
        }
        if (empty($name)) {
            Session::Messages($langEmptyNodeName, 'alert-danger');
            redirect_to_home_page("modules/admin/hierarchy.php?action=edit&amp;id=" . getIndirectReference($id));            
        } else {
            // OK Update the node
            $oldpid = intval(getDirectReference($_POST['oldparentid']));
            $newpid = intval(getDirectReference($_POST['newparentid']));
            validateParentId($newpid, isDepartmentAdmin());
            $tree->updateNode($id, $name, $description, $tree->getNodeLft($newpid), intval($_POST['lft']), intval($_POST['rgt']), $tree->getNodeLft($oldpid), $code, $allow_course, $allow_user, $order_priority, $visible);
            Session::Messages($langEditNodeSuccess, 'alert-success');
            redirect_to_home_page('modules/admin/hierarchy.php');
        }
    } else {
        // Get node information
        $data['id'] = $id = intval(getDirectReference($_GET['id']));
        $data['mynode'] = $mynode = Database::get()->querySingle("SELECT name, description, lft, rgt, code, allow_course, allow_user, order_priority, visible FROM hierarchy WHERE id = ?d", $id);
        $parent = $tree->getParent($mynode->lft, $mynode->rgt);
        $check_user = ($mynode->allow_user == 1) ? " checked=1 " : '';

        // name multi-lang field
        $data['is_serialized'] = false;
        $names = @unserialize($mynode->name);
        if ($names !== false) {
            $data['names'] = $names;
            $data['is_serialized'] = true;
        }

        // description multi-lang field
        $data['desc_is_ser'] = false;
        $descriptions = @unserialize($mynode->description);
        if ($descriptions !== false) {
            $data['descriptions'] = $descriptions;
            $data['desc_is_ser'] = true;
        }

        $data['formOPid'] = 0;
        $treeopts = array(
            'params' => 'name="newparentid"',
            'exclude' => getIndirectReference($id),
            'tree' => array('0' => 'Top'),
            'multiple' => false);
        if (isset($parent) && isset($parent->id)) {
            $treeopts['defaults'] = $parent->id;
            $data['formOPid'] = $parent->id;
        }
        
        if ($is_admin) {
            list($js, $html) = $tree->buildNodePickerIndirect($treeopts);
        } else {
            $treeopts['allowables'] = $user->getDepartmentIds($uid);
            list($js, $html) = $tree->buildNodePickerIndirect($treeopts);
        }

        $data['visibleChecked'] = array(NODE_CLOSED => '', NODE_SUBSCRIBED => '', NODE_OPEN => '');
        $data['visibleChecked'][intval($mynode->visible)] = " checked='checked'";
        
        $head_content .= $js;
        $data['html'] = $html;
        $view = 'admin.courses.hierarchy.create';
    }
}

// prepare javascript in head_content for rich_text_editor and for calling rich_text_editor via the view
rich_text_editor(null, null, null, null);
$data['menuTypeID'] = 3;
view($view, $data);