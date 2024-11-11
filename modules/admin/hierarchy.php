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

/* ===========================================================================
  hierarchy.php
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
  ==============================================================================
  @Description: Manage Hierarchy

  This script allows the administrator to list the available hierarchical
  data tree nodes, edit/move them, delete them or add new ones.

  ============================================================================== */

$require_departmentmanage_user = true;
$require_help = true;
$helpTopic = 'course_administration';
$helpSubTopic = 'facutlies_departments_actions';

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$user = new User();

load_js('jstree3');

$toolName = $langAdmin;
$pageName = $langHierarchy;
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
                'button-class' => 'btn-success')
        ));
}

// Display all available nodes
if (!isset($_GET['action'])) {
    // Count available nodes
    $data['nodesCount'] = Database::get()->querySingle("SELECT COUNT(*) as count from hierarchy")->count;

    $query = "SELECT max(depth) as maxdepth FROM (SELECT  COUNT(parent.id) - 1 AS depth
                FROM `hierarchy` AS node, `hierarchy` AS parent
                    WHERE node.lft BETWEEN parent.lft AND parent.rgt
                    GROUP BY node.id, node.lft
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
        $code = canonicalize_whitespace($_POST['code']);

        $names = [];
        foreach ($session->active_ui_languages as $key => $langcode) {
            $n = (isset($_POST['name-' . $langcode])) ? canonicalize_whitespace($_POST['name-' . $langcode]) : null;
            if (!empty($n)) {
                $names[$langcode] = $n;
            }
        }
        $name = serialize($names);

        $descriptions = [];
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
            Session::flash('message', $langEmptyNodeName);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/admin/hierarchy.php?a=1");
        }
        // Check for greek letters
        elseif (!empty($code) && !preg_match("/^[A-Z0-9a-z_-]+$/", $code)) {
            Session::flash('message', $langGreekCode);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/admin/hierarchy.php?a=1");
        } else {
            // OK Create the new node

            // upload picture (if any)
            $faculty_image = '';
            if (isset($_FILES['faculty_image']) && is_uploaded_file($_FILES['faculty_image']['tmp_name'])) {
                $file_name = $_FILES['faculty_image']['name'];
                validateUploadedFile($file_name, 2);
                make_dir("$webDir/courses/facultyimg/$code/image");
                move_uploaded_file($_FILES['faculty_image']['tmp_name'], "$webDir/courses/facultyimg/$code/image/$file_name");
                $faculty_image = $file_name;
            }

            if(!empty($_POST['choose_from_list'])) {
                $imageName = $_POST['choose_from_list'];
                $imagePath = "$webDir/template/modern/images/courses_images/$imageName";
                $newPath = "$webDir/courses/facultyimg/$code/image/";
                make_dir("$newPath");
                $ext =  get_file_extension($imageName);
                $image_without_ext = preg_replace('/\\.[^.\\s]{3,4}$/', '', $imageName);
                $newName  = $newPath.$image_without_ext.".".$ext;
                $copied = copy($imagePath , $newName);
                if ((!$copied)) {
                    echo "Error : Not Copied";
                } else {
                    $faculty_image = $image_without_ext.".".$ext;
                }
            }
            $pid = intval($_POST['parentid']);
            validateParentId($pid, isDepartmentAdmin());
            $tree->addNode($name, $description, $tree->getNodeLft($pid), $code, $allow_course, $allow_user, $order_priority, $visible, $faculty_image);

            Session::flash('message', $langAddSuccess);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/admin/hierarchy.php");
        }
    } else {
        $data['names'] = $data['descriptions'] = [];
        foreach ($session->active_ui_languages as $langCode) {
            $data['names'][$langCode] = $data['descriptions'][$langCode] = '';
        }

        $data['visibleChecked'] = array(NODE_CLOSED => '', NODE_SUBSCRIBED => '', NODE_OPEN => '');
        $data['visibleChecked'][intval(NODE_OPEN)] = " checked='checked'";
        list($js, $html) = $tree->buildNodePickerIndirect(array('params' => 'name="parentid"', 'tree' => array('0' => 'Top'), 'multiple' => false, 'defaults' => $user->getDepartmentIds($uid), 'allow_only_defaults' => (!$is_admin)));
        $head_content .= $js;
        $data['html'] = $html;

        // faculty image
        $image_content = '';
        $dir_images = scandir($webDir . '/template/modern/images/courses_images');
        foreach($dir_images as $image) {
            $extension = pathinfo($image, PATHINFO_EXTENSION);
            $imgExtArr = ['jpg', 'jpeg', 'png'];
            if (in_array($extension, $imgExtArr)) {
                $image_content .= "
                    <div class='col'>
                        <div class='card panelCard card-default h-100'>
                            <img style='height:200px;' class='card-img-top' src='{$urlAppend}template/modern/images/courses_images/$image' alt='image course'/>
                            <div class='card-body'>                                
                                <input id='$image' type='button' class='btn submitAdminBtnDefault w-100 chooseFacultyImage mt-3' value='$langSelect'>
                            </div>
                        </div>
                    </div>
                ";
            }
        }
        $data['image_content'] = $image_content;

        $view = 'admin.courses.hierarchy.create';
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
            Session::flash('message',"$langNodeProErase<br>$langNodeNoErase");
            Session::flash('alert-class', 'alert-danger');
        } else {
            // The node can be deleted
            $tree->deleteNode($id);
            Session::flash('message',$langNodeErase);
            Session::flash('alert-class', 'alert-success');
        }
        redirect_to_home_page('modules/admin/hierarchy.php');
    }
}
// Edit a node
elseif (isset($_GET['action']) and $_GET['action'] == 'edit') {
    $id = intval($_REQUEST['id']);
    validateNode($id, isDepartmentAdmin());

    if (isset($_GET['delete_image'])) {
        $row = Database::get()->querySingle("SELECT code, faculty_image FROM hierarchy WHERE id = ?d", $id);
        unlink("$webDir/courses/facultyimg/$row->code/image/$row->faculty_image");
        Database::get()->querySingle("UPDATE hierarchy SET faculty_image = null WHERE id = ?d", $id);

        Session::flash('message', $langEditNodeSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page('modules/admin/hierarchy.php');
    }

    if (isset($_POST['edit'])) {

        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) { csrf_token_error(); }
        checkSecondFactorChallenge();

        // Check for empty fields
        $names = array();
        foreach ($session->active_ui_languages as $key => $langcode) {
            $n = (isset($_POST['name-' . $langcode])) ? canonicalize_whitespace($_POST['name-' . $langcode]) : '';
            if ($n !== '') {
                $names[$langcode] = $n;
            }
        }
        $name = serialize($names);

        $descriptions = array();
        foreach ($session->active_ui_languages as $key => $langcode) {
            $d = (isset($_POST['description-' . $langcode])) ? purify(canonicalize_whitespace($_POST['description-' . $langcode])) : '';
            if ($d !== '') {
                $descriptions[$langcode] = $d;
            }
        }
        $description = serialize($descriptions);

        $code = canonicalize_whitespace($_POST['code']);
        $allow_course = (isset($_POST['allow_course'])) ? 1 : 0;
        $allow_user = (isset($_POST['allow_user'])) ? 1 : 0;
        $order_priority = empty($_POST['order_priority']) ? 'null': intval($_POST['order_priority']);
        $visible = (isset($_POST['visible'])) ? intval($_POST['visible']) : 2;
        if ($visible < 0 || $visible > 2) {
            $visible = 2;
        }
        if (empty($name)) {
            Session::flash('message',$langEmptyNodeName);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/admin/hierarchy.php?action=edit&id=" . $id);
        } else {

            // OK Update the node
            $faculty_image = null;
            if (isset($_FILES['faculty_image']) && is_uploaded_file($_FILES['faculty_image']['tmp_name'])) {
                $file_name = $_FILES['faculty_image']['name'];
                validateUploadedFile($file_name, 2);
                make_dir("$webDir/courses/facultyimg/$code/image");
                move_uploaded_file($_FILES['faculty_image']['tmp_name'], "$webDir/courses/facultyimg/$code/image/$file_name");
                $faculty_image = $file_name;
            }

            if(!empty($_POST['choose_from_list'])) {
                $imageName = $_POST['choose_from_list'];
                $imagePath = "$webDir/template/modern/images/courses_images/$imageName";
                $newPath = "$webDir/courses/facultyimg/$code/image/";
                make_dir("$newPath");
                $ext =  get_file_extension($imageName);
                $image_without_ext = preg_replace('/\\.[^.\\s]{3,4}$/', '', $imageName);
                $newName  = $newPath.$image_without_ext.".".$ext;
                $copied = copy($imagePath , $newName);
                if ((!$copied)) {
                    echo "Error : Not Copied";
                } else {
                    $faculty_image = $image_without_ext.".".$ext;
                }
            }

            $oldpid = intval($_POST['oldparentid']);
            $newpid = intval($_POST['newparentid']);
            validateParentId($newpid, isDepartmentAdmin());
            $tree->updateNode($id, $name, $description, $tree->getNodeLft($newpid), intval($_POST['lft']), intval($_POST['rgt']), $tree->getNodeLft($oldpid), $code, $allow_course, $allow_user, $order_priority, $visible, $faculty_image);
            Session::flash('message',$langEditNodeSuccess);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page('modules/admin/hierarchy.php');
        }
    } else {
        // Get node information
        $data['id'] = $id;
        $data['mynode'] = $mynode = Database::get()->querySingle("SELECT name, description, lft, rgt, code, allow_course, allow_user, order_priority, visible, faculty_image FROM hierarchy WHERE id = ?d", $id);
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
        $descriptions = @unserialize($mynode->description);
        if (is_array($descriptions)) {
            $data['descriptions'] = $descriptions;
        } else {
            $data['descriptions'][get_config('default_language')] = $mynode->description;
        }
        foreach ($session->active_ui_languages as $langCode) {
            if (!isset($data['descriptions'][$langCode])) {
                $data['descriptions'][$langCode] = '';
            }
        }

        $data['formOPid'] = 0;
        $treeopts = array(
            'params' => 'name="newparentid"',
            'exclude' => $id,
            'tree' => array('0' => 'Top'),
            'multiple' => false);
        if (isset($parent) && isset($parent->id)) {
            $treeopts['defaults'] = $parent->id;
            $data['formOPid'] = $parent->id;
        }

        if ($is_admin) {
            list($js, $html) = $tree->buildNodePicker($treeopts);
        } else {
            $treeopts['allowables'] = $user->getDepartmentIds($uid);
            list($js, $html) = $tree->buildNodePicker($treeopts);
        }

        $data['visibleChecked'] = array(NODE_CLOSED => '', NODE_SUBSCRIBED => '', NODE_OPEN => '');
        $data['visibleChecked'][intval($mynode->visible)] = " checked='checked'";

        $head_content .= $js;
        $data['html'] = $html;
        $data['faculty_image'] = $faculty_image = $mynode->faculty_image;
        $data['faculty_code'] = $mynode->code;
        // faculty image
        $image_content = '';
        $dir_images = scandir($webDir . '/template/modern/images/courses_images');
        foreach($dir_images as $image) {
            $extension = pathinfo($image, PATHINFO_EXTENSION);
            $imgExtArr = ['jpg', 'jpeg', 'png'];
            if (in_array($extension, $imgExtArr)) {
                $image_content .= "
                    <div class='col'>
                        <div class='card panelCard card-default h-100'>
                            <img style='height:200px;' class='card-img-top' src='{$urlAppend}template/modern/images/courses_images/$image' alt='image course'/>
                            <div class='card-body'>                                
                                <input id='$image' type='button' class='btn submitAdminBtnDefault w-100 chooseFacultyImage mt-3' value='$langSelect'>
                            </div>
                        </div>
                    </div>
                ";
            }
        }
        $data['image_content'] = $image_content;

        $view = 'admin.courses.hierarchy.create';
    }
}

// prepare javascript in head_content for rich_text_editor and for calling rich_text_editor via the view
rich_text_editor(null, null, null, null);
view($view, $data);
