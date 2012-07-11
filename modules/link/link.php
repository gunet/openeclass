<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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


/*
 * Links Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component organises the links of a lesson.
 * This module can:
 * - Organize links into categories
 * - move links up/down within a category
 * - move categories up/down
 * - expand/collapse all categories
 *
 * Based on code by Patrick Cool
 *
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Link';
$guest_allowed = true;

include '../../include/baseTheme.php';
$dbname = $_SESSION['dbname'];

require_once '../video/video_functions.php';
load_modal_box();

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action_stats = new action();
$action_stats->record('MODULE_ID_LINKS');
/**************************************/

mysql_select_db($mysqlMainDb);

$nameTools = $langLinks;

$is_in_tinymce = (isset($_REQUEST['embedtype']) && $_REQUEST['embedtype'] == 'tinymce') ? true : false;
$menuTypeID = ($is_in_tinymce) ? 5: 2;
$tinymce_params = '';

if ($is_in_tinymce) {
    
    $_SESSION['embedonce'] = true; // necessary for baseTheme
    $docsfilter = (isset($_REQUEST['docsfilter'])) ? '&amp;docsfilter='. $_REQUEST['docsfilter'] : '';
    $tinymce_params = '&amp;embedtype=tinymce'. $docsfilter;
    
    load_js('jquery');
    load_js('tinymce/jscripts/tiny_mce/tiny_mce_popup.js');
    
    $head_content .= <<<EOF
<script type='text/javascript'>
$(document).ready(function() {

    $("a.fileURL").click(function() { 
        var URL = $(this).attr('href');
        var win = tinyMCEPopup.getWindowArg("window");

        // insert information now
        win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;

        // are we an image browser
        if (typeof(win.ImageDialog) != "undefined") {
            // we are, so update image dimensions...
            if (win.ImageDialog.getImageData)
                win.ImageDialog.getImageData();

            // ... and preview if necessary
            if (win.ImageDialog.showPreviewImage)
                win.ImageDialog.showPreviewImage(URL);
        }

        // close popup window
        tinyMCEPopup.close();
        return false;
    });
});
</script>
EOF;
}

$head_content .= <<<hContent
<script type="text/javascript">
function checkrequired(which, entry) {
	var pass=true;
	if (document.images) {
		for (i=0;i<which.length;i++) {
			var tempobj=which.elements[i];
			if (tempobj.name == entry) {
				if (tempobj.type=="text"&&tempobj.value=='') {
					pass=false;
					break;
		  		}
	  		}
		}
	}
	if (!pass) {
		alert("$langEmptyLinkURL");
		return false;
	} else {
		return true;
	}
}

</script>
hContent;

if (isset($_GET['category'])) {
        $category = intval($_GET['category']);
} else {
        unset($category);
}

if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
} else {
        unset($id);
}

if (isset($_GET['urlview'])) {
        $urlview = urlencode($_GET['urlview']);
} else {
        $urlview = '';
}

include 'linkfunctions.php';

$action = isset($_GET['action'])? $_GET['action']: '';

if ($is_editor) {
        if (isset($_POST['submitLink'])) {
                submit_link();
        }
        if (isset($_POST['submitCategory'])) {
                submit_category();
        }
        switch ($action) {
                case 'deletelink': 
                        delete_link($id);
                        break;
                case 'deletecategory':
                        delete_category($id);
                        break;
        }

	if (!empty($catlinkstatus))	{
	   $tool_content .=  "<p class='success'>$catlinkstatus</p>\n";
	}

        if (!$is_in_tinymce)
        {
            $tool_content .="
            <div id='operations_container'>
            <ul id='opslist'>";
            if (isset($category))
                    $tool_content .=  "
                <li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;action=addlink&amp;category=$category&amp;urlview=$urlview'>$langLinkAdd</a></li>";
            else
                    $tool_content .=  "
                <li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;action=addlink'>$langLinkAdd</a></li>";
            if (isset($urlview))
                    $tool_content .=  "
                <li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;action=addcategory&amp;urlview=$urlview'>$langCategoryAdd</a></li>";
            else
                    $tool_content .=  "
                <li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;action=addcategory'>$langCategoryAdd</a></li>";

            $tool_content .=  "</ul></div>";
        }

	// Display the correct title and form for adding or modifying a category or link.
        if (in_array($action, array('addlink', 'editlink'))) {
                $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;urlview=$urlview' onsubmit=\"return checkrequired(this, 'urllink');\">";
                if ($action == 'editlink') {
                        $tool_content .= "<input type='hidden' name='id' value='$id' />";
                        link_form_defaults($id);
                        $form_legend = $langLinkModify;
                        $submit_label = $langLinkModify;
                } else {
                        $form_url = $form_title = $form_description = '';
                        $form_legend = $langLinkAdd;
                        $submit_label = $langAdd;
                } 

                $tool_content .= "
                      <fieldset>
                      <legend>$form_legend</legend>
                        <table width='100%' class='tbl'>
                        <tr><th>URL:</th>
                            <td><input type='text' name='urllink' size='53'$form_url /></td></tr>
                        <tr><th>$langLinkName:</th>
                            <td><input type='text' name='title' size='53'$form_title /></td></tr>
                        <tr><th>$langDescription:</th>
                            <td>" . rich_text_editor('description', 3, 50, $form_description) . "</td></tr>
                        <tr><th>$langCategory:</th>
                            <td><select name='selectcategory'>
                                <option value='0'>--</option>";
                $resultcategories = db_query("SELECT * FROM link_category WHERE course_id = $cours_id ORDER BY `order`");
                while ($myrow = mysql_fetch_array($resultcategories)) {
                        $tool_content .=  "<option value='$myrow[id]'";
                        if (isset($category) and $myrow['id'] == $category) {
                                $tool_content .= " selected='selected'";
                        }
                        $tool_content .= '>' . q($myrow['name']) . "</option>\n";
                }
                $tool_content .=  "</select>
                            </td>
                        </tr>
                        <tr>
                            <th>&nbsp;</th>
                            <td class='right'><input type='submit' name='submitLink' value='$submit_label' /></td>
                        </tr>
                        </table>
                      </fieldset>
                      </form>";

        } elseif (in_array($action, array('addcategory', 'editcategory'))) {
                $tool_content .=  "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$code_cours&urlview=$urlview'>\n";
                if ($action == 'editcategory') {
                        $tool_content .= "<input type='hidden' name='id' value='$id' />";
                        category_form_defaults($id);
                        $form_legend = $langCategoryMod;
                } else {
                        $form_name = $form_description = '';
                        $form_legend = $langCategoryAdd;
                } 
                $tool_content .=  "<fieldset><legend>$form_legend</legend>
                                   <table width='100%' class='tbl'>
                                   <tr><th>$langCategoryName:</th>
                                       <td><input type='text' name='categoryname' size='53'$form_name /></td></tr>
                                   <tr><th>$langDescription:</th>
                                       <td><textarea rows='5' cols='50' name='description'>$form_description</textarea></td></tr>
                                   <tr><th>&nbsp;</th>
                                       <td class='right'><input type='submit' name='submitCategory' value='$form_legend' /></td></tr>
                                </table></fieldset></form>";
        }
}

if (isset($_GET['down'])) {
        move_order('link', 'id', intval($_GET['down']), 'order', 'down', "course_id = $cours_id");
} elseif (isset($_GET['up'])) {
        move_order('link', 'id', intval($_GET['up']), 'order', 'up', "course_id = $cours_id");
} elseif (isset($_GET['cdown'])) {
        move_order('link_category', 'id', intval($_GET['cdown']), 'order', 'down', "course_id = $cours_id");
} elseif (isset($_GET['cup'])) {
        move_order('link_category', 'id', intval($_GET['cup']), 'order', 'up', "course_id = $cours_id");
}

$resultcategories = db_query("SELECT * FROM `link_category` WHERE course_id = $cours_id ORDER BY `order`");

if (mysql_num_rows($resultcategories) > 0) {
	// Starting the table which contains the categories
	// displaying the links which have no category (thus category = 0 or NULL), if none present this will not be displayed
	$result = db_query("SELECT * FROM `link` WHERE course_id = $cours_id AND (category = 0 OR category IS NULL)");
	$numberofzerocategory = mysql_num_rows($result);
	// making the show none / show all links. Show none means urlview=0000 (number of zeros depending on the
	// number of categories). Show all means urlview=1111 (number of 1 depending on teh number of categories).
	$resultcategories = db_query("SELECT * FROM `link_category` WHERE course_id = $cours_id ORDER BY `order`");
	$aantalcategories = mysql_num_rows($resultcategories);

	if ($aantalcategories > 0) {
		$more_less = "
                <table width='100%' class='tbl'>
		<tr>
		  <td class='bold'>$langCategorisedLinks</td>
		  <td width='1'><img src='$themeimg/folder_closed.png' title='$showall' /></td>
		  <td width='60'><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;urlview=" . str_repeat('0', $aantalcategories) . $tinymce_params .
	              "'>$shownone</a></td>
		  <td width='1'><img src='$themeimg/folder_open.png' title='$showall' /></td>
		  <td width='60'><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;urlview=" . str_repeat('1', $aantalcategories) . $tinymce_params .
		      "'>$showall</a></td>
		</tr>
                </table>";
	}


    // Edw fiaxnei ton pinaka me tis Genikes kathgories
	if ($numberofzerocategory !== 0) {
		$tool_content .= "\n
		<table width='100%' class='tbl_alt'>
		<tr>
		  <th width='15'><img src='$themeimg/folder_open.png' title='$langNoCategory' /></th>
		  <th colspan='6'><div align='left'>$langNoCategory</div></th>
		</tr>";
		showlinksofcategory(0);
		$tool_content .= "</table>";
	}

	// Edw fiaxnei to tool bar me tin emfanisi apokripsi
	$tool_content .= "<br />$more_less
	        <table width=\"100%\" class='tbl_alt'>";
	$i = 0;
	$catcounter = 1;
	while ($myrow = mysql_fetch_array($resultcategories)) {
		if (empty($urlview)) {
			// No $view set in the url, thus for each category link it should be all zeros except it's own
			$view = makedefaultviewcode($i);
		} else {
			$view = $urlview;
			$view[$i] = '1';
		}
		// if the $urlview has a 1 for this categorie, this means it is expanded and should be desplayed as a
		// - instead of a +, the category is no longer clickable and all the links of this category are displayed
		$description = standard_text_escape($myrow['description']);
		if ((isset($urlview[$i]) and $urlview[$i] == '1')) {
			$newurlview = $urlview;
			$newurlview[$i] = '0';
			$tool_content .= "
                <tr>
		  <th width='15' valign='top'><img src='$themeimg/folder_open.png' title='$shownone' /></th>
		  <th colspan='2' valign='top'><div class='left'><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;urlview=$newurlview$tinymce_params'>".q($myrow['name'])."</a>";
                        if (!empty($description)) {
                                $tool_content .= "<br />$description</div></th>";
                        }
                        if ($is_editor && !$is_in_tinymce) {
                                showcategoryadmintools($myrow["id"]);
                        } else {
                                $tool_content .=  "
                </tr>";
                        }
			showlinksofcategory($myrow["id"]);
		} else {
			$tool_content .=  "
		<tr>
		  <th width='15' valign='top'><img src='$themeimg/folder_closed.png' title='$showall' /></th>
		  <th colspan='2' valign='top'><div class='left'><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;urlview=";
			$tool_content .=  is_array($view)?implode('',$view):$view;
			$tool_content .= $tinymce_params ."'>" . q($myrow['name']) . "</a>";
		        $description = standard_text_escape($myrow['description']);
                        if (!empty($description)) {
                                $tool_content .= "<br />$description</div>
                  </th>";
                        }
			if ($is_editor && !$is_in_tinymce) {
			showcategoryadmintools($myrow["id"]);
                        } else {
                                $tool_content .=  "
                </tr>";
			}
		}
		// displaying the link of the category
		$i++;
	}
	$tool_content .=  "
                </table>";
} else {   // no category
   if (getNumberOfLinks(0) > 0) {
		$tool_content .=  "
                <table width=\"100%\">
		<tr>
		  <td width='1'><img src='$themeimg/folder_open.png' title='$langNoCategory' /></td>
		  <td colspan='4'><b>$langLinks</b></td>
		</tr>";
		showlinksofcategory(0);
		$tool_content .=  "
                </table>";
	} else {
                $tool_content .= "<p class='alert1'>$langNoLinksExist</p>";
		if ($is_editor && !$is_in_tinymce){
			// if the user is the course administrator instruct him/her
                        // what he can do to add links
                        $tool_content .= "<p class='center'>
                                <small>$langProfNoLinksExist</small></p>";
		}
	}
}

add_units_navigation(true);
draw($tool_content, $menuTypeID, null, $head_content);


function link_form_defaults($id)
{
	global $cours_id, $form_url, $form_title, $form_description, $category;

        $result = db_query("SELECT * FROM `link` WHERE course_id = $cours_id AND id = $id");
        if ($myrow = mysql_fetch_array($result)) {
                $form_url = ' value="' . q($myrow['url']) . '"';
                $form_title = ' value="' . q($myrow['title']) . '"';
                $form_description = q(purify($myrow['description']));
                $category = $myrow['category'];
        } else {
                $form_url = $form_title = $form_description = '';
        }
}

// Enter the modified info submitted from the link form into the database
function submit_link()
{
        global $cours_id, $catlinkstatus, $langLinkMod, $langLinkAdded,
               $urllink, $title, $description, $selectcategory;

        register_posted_variables(array('urllink' => true,
                                        'title' => true,
                                        'description' => true,
                                        'selectcategory' => true), 'all', 'trim');
	$urllink = canonicalize_url($urllink);
        $set_sql = "SET url = " . autoquote($urllink) . ",
                        title = " . autoquote($title) . ",
                        description = " . autoquote($description) . ",
                        category = " . intval($selectcategory);

        if (isset($_POST['id'])) {
                $id = intval($_POST['id']);
                db_query("UPDATE `link` $set_sql WHERE course_id = $cours_id AND id = $id");
                $catlinkstatus = $langLinkMod;
        } else {
                $q = db_query("SELECT MAX(`order`) FROM `link`
                                      WHERE course_id = $cours_id AND category = $selectcategory");
                list($order) = mysql_fetch_row($q);
                $order++;
                db_query("INSERT INTO `link` $set_sql, course_id = $cours_id, `order` = $order");
                $catlinkstatus = $langLinkAdded;
        }
}

function category_form_defaults($id)
{
	global $cours_id, $form_name, $form_description;

        $result = db_query("SELECT * FROM link_category WHERE course_id = $cours_id AND id = $id");
        if ($myrow = mysql_fetch_array($result)) {
                $form_name = ' value="' . q($myrow['name']) . '"';
                $form_description = q($myrow['description']);
        } else {
                $form_name = $form_description = '';
        }
}

// Enter the modified info submitted from the category form into the database
function submit_category()
{
        global $cours_id, $langCategoryAdded, $langCategoryModded,
               $categoryname, $description;

        register_posted_variables(array('categoryname' => true,
                                        'description' => true), 'all', 'trim');
        $set_sql = "SET name = " . autoquote($categoryname) . ",
                        description = " . autoquote($description);

        if (isset($_POST['id'])) {
                $id = intval($_POST['id']);
                db_query("UPDATE `link_category` $set_sql WHERE course_id = $cours_id AND id = $id");
                $catlinkstatus = $langCategoryModded;
        } else {
                $q = db_query("SELECT MAX(`order`) FROM `link_category`
                                      WHERE course_id = $cours_id");
                list($order) = mysql_fetch_row($q);
                $order++;
                db_query("INSERT INTO `link_category` $set_sql, course_id = $cours_id, `order` = $order");
                $catlinkstatus = $langCategoryAdded;
        }
}

function delete_link($id)
{
	global $cours_id, $catlinkstatus, $langLinkDeleted;

	db_query("DELETE FROM `link` WHERE course_id = $cours_id AND id = $id");
        $catlinkstatus = $langLinkDeleted;
}

function delete_category($id)
{
	global $cours_id, $catlinkstatus, $langCategoryDeleted;

	db_query("DELETE FROM `link` WHERE course_id = $cours_id AND category = $id");
	db_query("DELETE FROM `link_category` WHERE course_id = $cours_id AND id = $id");
        $catlinkstatus = $langCategoryDeleted;
}
