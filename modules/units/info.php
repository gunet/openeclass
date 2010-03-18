<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

/*
Units module	
*/

$require_current_course = true;
$require_help = TRUE;
$helpTopic = 'AddCourseUnits';
include '../../include/baseTheme.php';

$nameTools = $langEditUnit;
$tool_content = $head_content = "";

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
		alert("$langEmptyUnitTitle");
		return false;
	} else {
		return true;
	}
}

</script>
hContent;

if (!$is_adminOfCourse) { // check teacher status
        $tool_content .= $langNotAllowed;
        draw($tool_content, 2, 'units', $head_content);
        exit;
}

if ($language == 'greek')
        $lang_editor = 'el';
else
        $lang_editor = 'en';

$head_content .= "<script type='text/javascript'>
_editor_url  = '$urlAppend/include/xinha/';
_editor_lang = '$lang_editor';
</script>
<script type='text/javascript' src='$urlAppend/include/xinha/XinhaCore.js'></script>
<script type='text/javascript' src='$urlAppend/include/xinha/my_config.js'></script>";

if (isset($_GET['edit'])) { // display form for editing course unit
        $id = intval($_GET['edit']); 
        $sql = db_query("SELECT id, title, comments FROM course_units WHERE id='$id'");
        $cu = mysql_fetch_array($sql);
        $unittitle = " value='" . htmlspecialchars($cu['title'], ENT_QUOTES) . "'";
        $unitdescr = $cu['comments'];
        $unit_id = $cu['id'];
        $button = $langEdit;
} else {
        $nameTools = $langAddUnit;
        $button = $langAdd;
        $unitdescr = $unittitle = '';
}

$tool_content .= "<form method='post' action='${urlServer}courses/$currentCourseID/'
        onsubmit=\"return checkrequired(this, 'unittitle');\">";
if (isset($unit_id)) {
        $tool_content .= "<input type='hidden' name='unit_id' value='$unit_id'>";
}
$tool_content .= "<table width='99%' class='FormData' align='center'><tbody>
        <tr><th width='220'>&nbsp;</th>
            <td><b>$nameTools</b></td></tr>
        <tr><th width='150' class='left'>$langUnitTitle:</th>
            <td><input type='text' name='unittitle' size='50' maxlength='255' $unittitle class='FormData_InputText'></td></tr>
        <tr><th class='left'>$langUnitDescr:</th><td>
        <table class='xinha_editor'><tr><td><textarea id='xinha' name='unitdescr'>". str_replace('{','&#123;',htmlspecialchars($unitdescr))."</textarea></td></tr>
        </table></td></tr>
        <tr><th>&nbsp;</th>
            <td><input type='submit' name='edit_submit' value='$button'></td></tr>
</tbody></table>
</form>";
draw($tool_content, 2, 'units', $head_content);

