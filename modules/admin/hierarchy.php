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
    $tool_content .= action_bar(array(
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
    $tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));
}

// Display all available nodes
if (!isset($_GET['action'])) {
    // Count available nodes
    $nodesCount = Database::get()->querySingle("SELECT COUNT(*) as count from hierarchy")->count;

    $query = "SELECT max(depth) as maxdepth FROM (SELECT  COUNT(parent.id) - 1 AS depth
                FROM `hierarchy` AS node, `hierarchy` AS parent
                    WHERE node.lft BETWEEN parent.lft AND parent.rgt
                    GROUP BY node.id
                    ORDER BY node.lft) AS hierarchydepth";
    $maxdepth = Database::get()->querySingle($query)->maxdepth;

    // Construct a table
    $tool_content .= "
    <table class='table-default'>
    <tr>
    <td colspan='" . ($maxdepth + 4) . "' class='right'>
            $langThereAre: <b>$nodesCount</b> $langFaculties
    </td>
    </tr>";

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

    $tool_content .= "<tr><td colspan='" . ($maxdepth + 4) . "'><div id='js-tree'></div></td></tr></table>";
}
// Add a new node
elseif (isset($_GET['action']) && $_GET['action'] == 'add') {
    if (isset($_POST['add'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) { csrf_token_error(); }
        $code = canonicalize_whitespace($_POST['code']);

        $names = array();
        foreach ($session->active_ui_languages as $key => $langcode) {
            $n = (isset($_POST['name-' . $langcode])) ? canonicalize_whitespace($_POST['name-' . $langcode]) : null;
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
        if (!count($names)) {
            $tool_content .= "<div class='alert alert-danger'>" . $langEmptyNodeName . "</div><br>";
            $tool_content .= action_bar(array(
                array('title' => $langReturnToAddNode,
                    'url' => $_SERVER['SCRIPT_NAME'] . "?a=1",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')));
        }
        // only latin characters are allowed
        elseif (!empty($code) && !preg_match("/^[A-Z0-9a-z_-]+$/", $code)) {
            $tool_content .= "<div class='alert alert-danger text-center'>" . $langGreekCode . "</div><br>";
            $tool_content .= action_bar(array(
                array('title' => $langReturnToAddNode,
                    'url' => $_SERVER['SCRIPT_NAME'] . "?a=1",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')));
        } else {
            // OK Create the new node
            $pid = intval($_POST['parentid']);
            validateParentId($pid, isDepartmentAdmin());
            $tree->addNode($name, $description, $tree->getNodeLft($pid), $code, $allow_course, $allow_user, $order_priority, $visible);
            $tool_content .= "<div class='alert alert-success'>" . $langAddSuccess . "</div>";
        }
    } else {
        // Display form for new node information
        $tool_content .= "<div class='form-wrapper'>
            <form role='form' class='form-horizontal' method=\"post\" action=\"" . $_SERVER['SCRIPT_NAME'] . "?action=add\" onsubmit=\"return validateNodePickerForm();\">
            <fieldset>
            <div class='form-group'>
                <label class='col-sm-3 control-label'>$langNodeCode1:</label>
                <div class='col-sm-4'>
                    <input class='form-control' type='text' name='code' placeholder='$langCodeFaculte2' maxlength='30'>
                </div>
            </div>";

        // name multi-lang field
        $i = 0;
        foreach ($session->active_ui_languages as $key => $langcode) {
            $tool_content .= "<div class='form-group'>
                    <label class='col-sm-3 control-label'>$langNodeName:</label>";
            $tdpre = ($i >= 0) ? "<div class='col-sm-9'>" : '';
            $placeholder = "$langFaculte2 (" . $langNameOfLang[langcode_to_name($langcode)] . ")";
            $tool_content .= $tdpre . "<input class='form-control' type='text' name='name-" . $langcode . "' placeholder='$placeholder'></div></div>";
            $i++;
        }

        // description multi-lang field
        $j = 0;
        foreach ($session->active_ui_languages as $key => $langcode) {
            $tool_content .= "<div class='form-group'>
                    <label class='col-sm-3 control-label'>$langNodeDescription:</label>";
            $tdpre = ($j >= 0) ? "<div class='col-sm-9'>" : '';
            $tool_content .= $tdpre . rich_text_editor('description-' . $langcode, 8, 20, '') . "</div></div>";
            $j++;
        }

        $tool_content .= "<div class='form-group'>
                        <label class='col-sm-3 control-label'>$langNodeParent:</label>
                        <div class='col-sm-9'>";
        list($js, $html) = $tree->buildNodePicker(array('params' => 'name="parentid"', 'tree' => array('0' => 'Top'), 'multiple' => false, 'defaults' => $user->getDepartmentIds($uid), 'allow_only_defaults' => (!$is_admin)));
        $head_content .= $js;
        $tool_content .= $html;
        $tool_content .= "<span class='help-block'><small>$langNodeParent2</small></span>
        </div></div>
        <div class='form-group'>
            <label class='col-sm-3 control-label'>$langNodeAllowCourse:</label>
            <div class='col-sm-9'>
                  <input type='checkbox' name='allow_course' value='1' checked='checked'><span class='help-block'><small>$langNodeAllowCourse2</small></span>
          </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-3 control-label'>$langNodeAllowUser</label>
            <div class='col-sm-9'>
                <input type='checkbox' name='allow_user' value='1' checked='checked'><span class='help-block'><small>$langNodeAllowUser2</small></span>
            </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-3 control-label'>$langNodeOrderPriority</label>
            <div class='col-sm-9'>
                <input class='form-control' type='text' name='order_priority'><span class='help-block'><small>$langNodeOrderPriority2</small></span>
            </div>
        </div>";

        $visibleChecked = array(NODE_CLOSED => '', NODE_SUBSCRIBED => '', NODE_OPEN => '');
        $visibleChecked[2] = " checked='checked'";
        $tool_content .= formVisible($visibleChecked);

        $tool_content .= "
        <div class='form-group'>
          <div class='col-sm-9 col-sm-offset-3'>".form_buttons(array(
                array(
                    'text' => $langSave,
                    'name' => 'add',
                    'value'=> $langAdd
                ),
                array(
                    'href' => "$_SERVER[SCRIPT_NAME]"
                )
            ))."
          </div>
        </div>
        </fieldset>
        ". generate_csrf_token_form_field() ."
        </form>
        </div>";
    }
}
// Delete node
elseif (isset($_GET['action']) and $_GET['action'] == 'delete') {
    $id = intval($_GET['id']);
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
    $id = intval($_REQUEST['id']);
    validateNode($id, isDepartmentAdmin());

    if (isset($_POST['edit'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        // Check for empty fields

        $names = array();
        foreach ($session->active_ui_languages as $key => $langcode) {
            $n = (isset($_POST['name-' . $langcode])) ? canonicalize_whitespace($_POST['name-' . $langcode]) : null;
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

        $code = canonicalize_whitespace($_POST['code']);
        $allow_course = (isset($_POST['allow_course'])) ? 1 : 0;
        $allow_user = (isset($_POST['allow_user'])) ? 1 : 0;
        $order_priority = (isset($_POST['order_priority']) && !empty($_POST['order_priority'])) ? intval($_POST['order_priority']) : 'null';
        $visible = (isset($_POST['visible'])) ? intval($_POST['visible']) : 2;
        if ($visible < 0 || $visible > 2) {
            $visible = 2;
        }
        if (!count($names)) {
            $tool_content .= "<div class='alert alert-danger'>" . $langEmptyNodeName . "</div><br>";
            $tool_content .= action_bar(array(
                array('title' => $langReturnToEditNode,
                    'url' => $_SERVER['SCRIPT_NAME'] . "?action=edit&amp;id=$id",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')));
        } elseif (!empty($code) && !preg_match("/^[A-Z0-9a-z_-]+$/", $code)) {
            // only latin characters are allowed
            $tool_content .= "<div class='alert alert-danger text-center'>" . $langGreekCode . "</div><br>";
            $tool_content .= action_bar(array(
                array('title' => $langReturnToEditNode,
                    'url' => $_SERVER['SCRIPT_NAME'] . "?action=edit&amp;id=$id",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')));
        } else {
            // OK Update the node
            $oldpid = intval($_POST['oldparentid']);
            $newpid = intval($_POST['newparentid']);
            validateParentId($newpid, isDepartmentAdmin());
            $tree->updateNode($id, $name, $description, $tree->getNodeLft($newpid), intval($_POST['lft']), intval($_POST['rgt']), $tree->getNodeLft($oldpid), $code, $allow_course, $allow_user, $order_priority, $visible);
            $tool_content .= "<div class='alert alert-success'>$langEditNodeSuccess</div><br />";
        }
    } else {
        // Get node information
        $id = intval($_GET['id']);
        $mynode = Database::get()->querySingle("SELECT name, description, lft, rgt, code, allow_course, allow_user, order_priority, visible FROM hierarchy WHERE id = ?d", $id);
        $parent = $tree->getParent($mynode->lft, $mynode->rgt);
        $check_course = ($mynode->allow_course == 1) ? " checked=1 " : '';
        $check_user = ($mynode->allow_user == 1) ? " checked=1 " : '';
        // Display form for edit node information
        $tool_content .= "
            <div class='form-wrapper'>
                <form role='form' class='form-horizontal' method='post' action='" . $_SERVER['SCRIPT_NAME'] . "?action=edit' onsubmit='return validateNodePickerForm();'>
                    <fieldset>
                        <div class='form-group'>
                            <label class='col-sm-3 control-label'>$langNodeCode1:</label>
                            <div class='col-sm-4'>
                                <input class='form-control' type='text' name='code' value='" . q($mynode->code) . "'  maxlength='30'>&nbsp;<i>" . $langCodeFaculte2 . "</i>
                            </div>
                        </div>";

        // name multi-lang field
        $is_serialized = false;
        $names = @unserialize($mynode->name);
        if ($names !== false) {
            $is_serialized = true;
        }
        $i = 0;
        foreach ($session->active_ui_languages as $key => $langcode) {
            $n = ($is_serialized && isset($names[$langcode])) ? $names[$langcode] : '';
            if (!$is_serialized && $key == 0) {
                $n = $mynode->name;
            }
            $tool_content .= "
                        <div class='form-group'>
                            <label class='col-sm-3 control-label'>$langNodeName:</label>";
            $tdpre = ($i >= 0) ? "<div class='col-sm-9'>" : '';
            $placeholder = "$langFaculte2 (" . $langNameOfLang[langcode_to_name($langcode)] . ")";
            $tool_content .= $tdpre . "<input class='form-control' type='text' name='name-" . q($langcode) . "' value='" . q($n) . "' placeholder='$placeholder'></div></div>";
            $i++;
        }

        // description multi-lang field
        $desc_is_ser = false;
        $descriptions = @unserialize($mynode->description);
        if ($descriptions !== false) {
            $desc_is_ser = true;
        }
        $j = 0;
        foreach ($session->active_ui_languages as $key => $langcode) {
            $d = ($desc_is_ser && isset($descriptions[$langcode])) ? $descriptions[$langcode] : '';
            if (!$desc_is_ser && $key == 0) {
                $d = $mynode->description;
            }
            $tool_content .= "
                        <div class='form-group'>
                            <label class='col-sm-3 control-label'>$langNodeDescription:</label>";
            $tdpre = ($j >= 0) ? "<div class='col-sm-9'>" : '';
            $placeholder = "$langFaculte2 (" . $langNameOfLang[langcode_to_name($langcode)] . ")";
            $tool_content .= $tdpre . rich_text_editor('description-' . $langcode, 8, 20, $d) . "</div></div>";
            $j++;
        }

        $tool_content .= "<div class='form-group'>
                        <label class='col-sm-3 control-label'>$langNodeParent:</label>
                        <div class='col-sm-9'>";

        $treeopts = array(
            'params' => 'name="newparentid"',
            'exclude' => $id,
            'tree' => array('0' => 'Top'),
            'multiple' => false);
        if (isset($parent) && isset($parent->id)) {
            $treeopts['defaults'] = $parent->id;
            $formOPid = $parent->id;
        } else {
            $formOPid = 0;
        }

        if ($is_admin) {
            list($js, $html) = $tree->buildNodePicker($treeopts);
        } else {
            $treeopts['allowables'] = $user->getDepartmentIds($uid);
            list($js, $html) = $tree->buildNodePicker($treeopts);
        }

        $head_content .= $js;
        $tool_content .= $html;
        $tool_content .= "<span class='help-block'><small>$langNodeParent2</small></span>
        </div></div>
        <div class='form-group'>
          <label class='col-sm-3 control-label'>$langNodeAllowCourse:</label>
            <div class='col-sm-9'>
                <input type='checkbox' name='allow_course' value='1' $check_course><span class='help-block'><small>$langNodeAllowCourse2</small></span>
          </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-3 control-label'>$langNodeAllowUser</label>
            <div class='col-sm-9'>
                <input type='checkbox' name='allow_user' value='1' $check_user><span class='help-block'><small>$langNodeAllowUser2</small></span>
            </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-3 control-label'>$langNodeOrderPriority</label>
            <div class='col-sm-9'>
                <input class='form-control' type='text' name='order_priority' value='" . q($mynode->order_priority) . "'><span class='help-block'><small>$langNodeOrderPriority2</small></span>
            </div>
        </div>";

        $visible = $mynode->visible;
        $visibleChecked = array(NODE_CLOSED => '', NODE_SUBSCRIBED => '', NODE_OPEN => '');
        $visibleChecked[$visible] = " checked='checked'";
        $tool_content .= formVisible($visibleChecked);

        $tool_content .= "
        <input type='hidden' name='id' value='$id' />
               <input type='hidden' name='oldparentid' value='" . $formOPid . "'/>
               <input type='hidden' name='lft' value='" . q($mynode->lft) . "'/>
               <input type='hidden' name='rgt' value='" . q($mynode->rgt) . "'/>
        <div class='form-group'>
          <div class='col-sm-9 col-sm-offset-3'>".form_buttons(array(
                array(
                    'text' => $langSave,
                    'name' => 'edit',
                    'value'=> $langAcceptChanges
                ),
                array(
                    'href' => "$_SERVER[SCRIPT_NAME]"
                )
            ))."
          </div>
        </div>
        </fieldset>
        ". generate_csrf_token_form_field() ."
        </form>
        </div>";
    }
}

draw($tool_content, 3, null, $head_content);

function formVisible($visibleChecked) {

    global $langViewHide, $langNodeHidden2, $langNodeSubscribed2,
           $langNodePublic2, $langNodeSubscribed, $langNodePublic, $langAvailableTypes;

    $ret = "
    <div class='form-group'>
        <label class='col-sm-3 control-label'>" . $langAvailableTypes . ":</label>
        <div class='col-sm-9'>
            <div class='radio'>
                <label>
                    <input id='nodeopen' type='radio' name='visible' value='2' $visibleChecked[2]>
                    <span class='fa fa-unlock fa-fw' style='font-size:23px;'></span>&nbsp;" . $langNodePublic . "
                    <span class='help-block'><small>" . $langNodePublic2 . "</small></span>
                </label>
            </div>
            <div class='radio'>
                <label>
                    <input id='nodeforsubscribed' type='radio' name='visible' value='1' $visibleChecked[1]>
                    <span class='fa fa-lock fa-fw'  style='font-size:23px;'>
                        <span class='fa fa-pencil text-danger fa-custom-lock' style='font-size:16px; position:absolute; top:13px; left:35px;'></span>
                    </span>&nbsp;" . $langNodeSubscribed . "
                    <span class='help-block'><small>" . $langNodeSubscribed2 . "</small></span>
                </label>
            </div>
            <div class='radio'>
                <label>
                    <input id='nodehidden' type='radio' name='visible' value='0' $visibleChecked[0]>
                    <span class='fa fa-lock fa-fw' style='font-size:23px;'></span>&nbsp;" . $langViewHide . "
                    <span class='help-block'><small>" . $langNodeHidden2 . "</small></span>
                </label>
            </div>
        </div>
    </div>";

    return $ret;
}
