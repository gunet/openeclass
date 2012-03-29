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

/*===========================================================================
	hierarchy.php
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
==============================================================================
        @Description: Manage Hierarchy

 	This script allows the administrator to list the available hierarchical
 	data tree nodes, edit/move them, delete them or add new ones.

==============================================================================*/

$require_power_user = true;
require_once('../../include/baseTheme.php');

$TBL_HIERARCHY         = 'hierarchy';
$TBL_COURSE_DEPARTMENT = 'course_department';

require_once('../../include/lib/hierarchy.class.php');

$tree = new hierarchy();


$nameTools = $langHierarchyActions;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

if (isset($_GET['action'])) {
    $navigation[] = array('url' => $_SERVER['PHP_SELF'], 'name' => $langHierarchyActions);
    switch ($_GET['action']) {
        case 'add':
            $nameTools = $langNodeAdd;
            break;
        case 'delete':
            $nameTools = $langNodeDel;
            break;
        case 'edit':
            $nameTools = $langNodeEdit;
            break;
    }
}

// link to add a new node
$tool_content .= "
    <div id='operations_container'>
     <ul id='opslist'>
      <li><a href='$_SERVER[PHP_SELF]?action=add'>".$langAdd."</a></li>
     </ul>
    </div>";

// Display all available nodes
if (!isset($_GET['action'])) {
    // Count available nodes
    $a = mysql_fetch_array(db_query("SELECT COUNT(*) FROM $TBL_HIERARCHY"));
    
    $query = "SELECT max(depth) FROM (SELECT  COUNT(parent.id) - 1 AS depth
                FROM `hierarchy` AS node, `hierarchy` AS parent 
                    WHERE node.lft BETWEEN parent.lft AND parent.rgt 
                    GROUP BY node.id 
                    ORDER BY node.lft) AS hierarchydepth";
    $maxdepth = mysql_fetch_array(db_query($query));

    // Construct a table
    $tool_content .= "
    <table width='100%' class='tbl_alt'>
    <tr>	
    <td colspan='". ($maxdepth[0] + 4) ."' class='right'>
            $langManyExist: <b>$a[0]</b> $langHierarchyNodes
    </td>
    </tr><tr>
    <th scope='col' colspan='". ($maxdepth[0] + 2) ."'><div align='left'>&nbsp;&nbsp;".$langHierarchyNode."</div></th scope='col'>
    <th scope='col' class='center'>$langCode</th>
    <th>".$langActions."</th>
    </tr>";
    
    $query = "SELECT node.id, node.lft AS lft, node.code as code, node.name,
                COUNT(parent.id) - 1 AS depth
                FROM ". $TBL_HIERARCHY ." AS node, ". $TBL_HIERARCHY ." AS parent 
                    WHERE node.lft BETWEEN parent.lft AND parent.rgt 
                    GROUP BY node.id 
                    ORDER BY node.lft";
    $sql = db_query($query);
    $k = 0;
    
    // For all nodes display some info
    for ($j = 0; $j < mysql_num_rows($sql); $j++) {
        $nodes = mysql_fetch_assoc($sql);
        if ($k%2==0) {
            $tool_content .= "\n<tr>";
        } else {
            $tool_content .= "\n<tr class='odd'>";
        }
        $tool_content .= "\n<td width='1'>
        <img src='$themeimg/arrow.png' alt='bullet' /></td>";
        
        for ($i = 1; $i <= $nodes['depth']; $i++)
            $tool_content .= "<td width='5'>&nbsp;</td>";
        $colspan = $maxdepth[0] - $nodes['depth'] + 1;
        
        $tool_content .= "\n<td colspan='$colspan'>".htmlspecialchars($nodes['name'])."</td>";
        $tool_content .= "\n<td width='100' class='smaller center'>".htmlspecialchars($nodes['code'])."</td>";
        // link to delete or edit a node
        $tool_content .= "\n<td width='50' align='center' nowrap>
        <a href='$_SERVER[PHP_SELF]?action=edit&amp;id=".$nodes['id']."'>
        <img src='$themeimg/edit.png' title='$langEdit' /></a>&nbsp;&nbsp;
        <a href='$_SERVER[PHP_SELF]?action=delete&amp;id=".$nodes['id']."' onClick=\"return confirm('Confirm delete?')\">
        <img src='$themeimg/delete.png' title='$langDelete' /></a></td>
        </tr>\n";
        $k++;
    }
    // Close table correctly
    $tool_content .= "</table>\n";
    $tool_content .= "<br /><p class='right'><a href=\"index.php\">".$langBack."</a></p>";
}
// Add a new node
elseif (isset($_GET['action']) && $_GET['action'] == 'add')  {
    if (isset($_POST['add'])) {
        $code = $_POST['code'];
        $name = $_POST['name'];
        $allow_course = (isset($_POST['allow_course'])) ? 1 : 0;
        $allow_user = (isset($_POST['allow_user'])) ? 1 : 0;
        // Check for empty fields
        if (empty($name)) {
            $tool_content .= "<p class='caution'>".$langEmptyNodeName."<br />";
            $tool_content .= "
            <a href=\"$_SERVER[PHP_SELF]?a=1\">".$langReturnToAddNode."</a></p>";
        }
        // Check for greek letters
        elseif (!empty($code) && !preg_match("/^[A-Z0-9a-z_-]+$/", $code)) {
            $tool_content .= "<p class='caution'>".$langGreekCode."<br />";
            $tool_content .= "<a href=\"$_SERVER[PHP_SELF]?a=1\">".$langReturnToAddNode."</a></p>";
        }
        // Check if node code already exists
        elseif (!empty($code) && mysql_num_rows(db_query("SELECT * from $TBL_HIERARCHY WHERE code = " . autoquote($code))) > 0) {
            $tool_content .= "<p class='caution'>".$langNCodeExists."<br />";
            $tool_content .= "<a href=\"$_SERVER[PHP_SELF]?a=1\">".$langReturnToAddNode."</a></p>";
        } else {
            // OK Create the new node
            $tree->addNode($name, intval($_POST['nodelft']), $code, $allow_course, $allow_user);
            $tool_content .= "<p class='success'>".$langAddSuccess."</p>";
        }
    } else {
        // Display form for new node information
        $tool_content .= "
    <form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?action=add\">
    <fieldset>
      <legend>$langNodeAdd</legend>
      <table width='100%' class='tbl'>
      <tr>
        <th width=\"180\" class='left'>".$langNodeCode1.":</th>
        <td><input type='text' name='code' /> <i>".$langCodeFaculte2."</i></td>
      </tr>
      <tr>
        <th class='left'>".$langNodeName.":</th>
        <td><input type='text' name='name' /> <i>".$langFaculte2."</i></td>
      </tr>
      <tr>
        <th class='left'>".$langNodeParent.":</th>
        <td>". $tree->buildHtmlSelect('name="nodelft"') ." <i>".$langNodeParent2."</i></td>
      </tr>
      <tr>
        <th class='left'>".$langNodeAllowCourse.":</th>
        <td><input type='checkbox' name='allow_course' value='1' checked=1 /> <i>".$langNodeAllowCourse2."</i></td>
      </tr>
      <tr>
        <th class='left'>".$langNodeAllowUser.":</th>
        <td><input type='checkbox' name='allow_user' value='1' checked=1 /> <i>".$langNodeAllowUser2."</i></td>
      </tr>
      <tr>
        <th>&nbsp;</th>
        <td class='right'><input type='submit' name='add' value='".$langAdd."' /></td>
      </tr>
    </table>
    </fieldset>
    </form>";
    }
    $tool_content .= "<p align='right'><a href='$_SERVER[PHP_SELF]'>".$langBack."</a></p>";
}
// Delete node
elseif (isset($_GET['action']) and $_GET['action'] == 'delete')  {
    $id = intval($_GET['id']);
    $s = db_query("SELECT * from $TBL_COURSE_DEPARTMENT WHERE department = $id");
    // Check for existing courses belonging to a node
    if (mysql_num_rows($s) > 0)  {
        // The node cannot be deleted
        $tool_content .= "<p>".$langNodeProErase."</p><br />";
        $tool_content .= "<p>".$langNodeNoErase."</p><br />";
    } else {
        // The node can be deleted
        $tree->deleteNode($id);
        $tool_content .= "<p class='success'>$langNodeErase</p>";
    }
    $tool_content .= "<p align='right'><a href='$_SERVER[PHP_SELF]'>".$langBack."</a></p>";
}
// Edit a node
elseif (isset($_GET['action']) and $_GET['action'] == 'edit')  {
    $id = intval($_REQUEST['id']);
    if (isset($_POST['edit'])) {
        // Check for empty fields
        $name = $_POST['name'];
        $code = $_POST['code'];
        $allow_course = (isset($_POST['allow_course'])) ? 1 : 0;
        $allow_user = (isset($_POST['allow_user'])) ? 1 : 0;
        if (empty($name)) {
            $tool_content .= "<p class='caution'>".$langEmptyNodeName."<br />";
            $tool_content .= "<a href='$_SERVER[PHP_SELF]?action=edit&amp;id=$id'>$langReturnToEditNode</a></p>";
        }
        // Check if node code already exists
        elseif (!empty($code) && mysql_num_rows(db_query("SELECT * from $TBL_HIERARCHY WHERE id <> $id AND code = ". autoquote($code))) > 0) {
            $tool_content .= "<p class='caution'>".$langNCodeExists."<br />";
            $tool_content .= "<a href=\"$_SERVER[PHP_SELF]?action=edit&amp;id=$id\">".$langReturnToEditNode."</a></p>";
        } else {
            // OK Update the node
            $tree->updateNode($id, $name, intval($_POST['nodelft']), 
                intval($_POST['lft']), intval($_POST['rgt']), intval($_POST['parentLft']),
                $code, $allow_course, $allow_user);
            $tool_content .= "<p class='success'>$langEditNodeSuccess</p><br />";
        }
    } else {
        // Get node information
        $id = intval($_GET['id']);
        $sql = "SELECT name, lft, rgt, code, allow_course, allow_user FROM ". $TBL_HIERARCHY ." WHERE id = '$id'";
        $result = db_query($sql);
        $myrow = mysql_fetch_assoc($result);
        $parentLft = $tree->getParent($myrow['lft'], $myrow['rgt']);
        $check_course = ($myrow['allow_course'] == 1) ? " checked=1 " : '';
        $check_user = ($myrow['allow_user'] == 1) ? " checked=1 " : '';
        // Display form for edit node information
        $tool_content .= "
       <form method='post' action='$_SERVER[PHP_SELF]?action=edit'>
       <fieldset>
       <legend>$langNodeEdit</legend>
       <table width='100%' class='tbl'>
       <tr>
           <th class='left' width='180'>".$langNodeCode1.":</th>
           <td><input type='text' name='code' value='".$myrow['code']."' />&nbsp;<i>".$langCodeFaculte2."</i></td>
       </tr>
       <tr>
           <th class='left'>".$langNodeName.":</th>
           <td><input type='text' name='name' value='".htmlspecialchars($myrow['name'], ENT_QUOTES)."' />&nbsp;<i>".$langFaculte2."</i></td>
       </tr>
       <tr>
           <th class='left'>".$langNodeParent.":</th>
           <td>". $tree->buildHtmlSelect('name="nodelft"', $parentLft['lft'], $id) ." <i>".$langNodeParent2."</i></td>
       </tr>
       <tr>
           <th class='left'>".$langNodeAllowCourse.":</th>
           <td><input type='checkbox' name='allow_course' value='1' $check_course /> <i>".$langNodeAllowCourse2."</i></td>
       </tr>
       <tr>
           <th class='left'>".$langNodeAllowUser.":</th>
           <td><input type='checkbox' name='allow_user' value='1' $check_user /> <i>".$langNodeAllowUser2."</i></td>
       </tr>
       <tr>
           <th>&nbsp;</th>
           <td class='right'><input type='hidden' name='id' value='$id' />
           <input type='hidden' name='parentLft' value='".$parentLft['lft']."'/>
           <input type='hidden' name='lft' value='".$myrow['lft']."'/>
           <input type='hidden' name='rgt' value='".$myrow['rgt']."'/>
           <input type='submit' name='edit' value='$langAcceptChanges' />
           </td>
       </tr>
       </table>
       </fieldset>
       </form>";
    }
    $tool_content .= "<p align='right'><a href='$_SERVER[PHP_SELF]'>".$langBack."</a></p>";
}

draw($tool_content, 3);
