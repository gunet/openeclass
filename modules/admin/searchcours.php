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


/*===========================================================================
	searchcours.php
	@last update: 31-05-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
        @Description: A form to perform search for courses

 	This script allows the administrator to perform a search on courses by
 	title, code, type and faculte

 	The user can : - Fill the search form
 	               - Submit the search
                 - Return to course list

 	@Comments: The script is organised in three sections.

  1) Perform a search
  2) Start a new search
  3) Display all on an HTML page

==============================================================================*/

$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new hierarchy();
$user = new user();

load_js('jquery');
load_js('jquery-ui-new');
load_js('jstree');

// Define $nameTools
$nameTools = $langSearchCourse;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

// Destroy search varialbles from session
if (isset($_GET['new']) && ($_GET['new'] == "yes")) {
	unset($_SESSION['searchtitle']);
	unset($_SESSION['searchcode']);
	unset($_SESSION['searchtype']);
	unset($_SESSION['searchfaculte']);
	unset($searchtitle);
	unset($searchcode);
	unset($searchtype);
	unset($searchfaculte);
}
// Display link for new search if there is one already
if (isset($searchtitle) && isset($searchcode) && isset($searchtype) && isset($searchfaculte)) {
	$newsearch = "(<a href=\"searchcours.php?new=yes\">".$langNewSearch."</a>)";
}

// search form
$tool_content .= "
    <form action=\"listcours.php?search=yes\" method=\"post\">
    <fieldset>
      <legend>".$langSearchCriteria." ".@$newsearch."</legend>
      <table width='100%' class='tbl'>
      <tr>
        <th class='left' width='150'>$langTitle:</th>
        <td><input type=\"text\" name=\"formsearchtitle\" size=\"40\" value=\"".@$searchtitle."\"></td>
      </tr>
      <tr>
        <th class='left'><b>$langCourseCode:</b></th>
        <td><input type=\"text\" name=\"formsearchcode\" size=\"40\" value=\"".@$searchcode."\"></td>
      </tr>";

switch (@$searchcode) {
	case "2":
		$typeSel[2] = "selected";
		break;
	case "1":
		$typeSel[1] = "selected";
		break;
	case "0":
		$typeSel[0] = "selected";
		break;
        case "3":
		$typeSel[0] = "selected";
		break;
	default:
		$typeSel[-1] = "selected";
		break;
}

$tool_content .= "
      <tr>
        <th class='left'><b>$langCourseVis:</b></td>
        <td>
          <select name=\"formsearchtype\">
           <option value='-1' ".$typeSel[-1].">$langAllTypes</option>
           <option value='2' ".@$typeSel[2].">$langTypeOpen</option>
           <option value='1' ".@$typeSel[1].">$langTypeRegistration</option>
           <option value='0' ".@$typeSel[0].">$langTypeClosed</option>
           <option value='3' ".@$typeSel[3].">$langCourseInactiveShort</option>
          </select>
        </td>
      </tr>";

$tool_content .= "<tr><th class='left'><b>".$langFaculty.":</b></th><td>";

if (isDepartmentAdmin())
    list($js, $html) = $tree->buildNodePicker(array('params' => 'name="formsearchfaculte"', 'tree' => array('0' => $langAllFacultes), 'useKey' => "id", 'multiple' => false, 'allowables' => $user->getDepartmentIds($uid)));
else
    list($js, $html) = $tree->buildNodePicker(array('params' => 'name="formsearchfaculte"', 'tree' => array('0' => $langAllFacultes), 'useKey' => "id", 'multiple' => false));

$head_content .= $js;
$tool_content .= $html;
$tool_content .= "</td></tr>";

$tool_content .= "
      <tr>
        <th>&nbsp;</th>
        <td class='right'><input type='submit' name='search_submit' value='$langSearch'></td>
      </tr>";
$tool_content .= "
      </table>
      </fieldset>
      </form>";

// Display link to go back to index.php
$tool_content .= "\n    <p align=\"right\"><a href=\"index.php\">".$langBack."</a></p>";

draw($tool_content, 3, null, $head_content);
