<?PHP
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
 * Course Tools Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component offers several operations regarding a course's tools.
 * The course administrator can:
 * 1. Activate/De-activate tools
 * 2. Upload external html page
 * 3. Add external links
 * 4. Delete the last two
 *
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'courseTools';
$require_prof = true;
$require_login = true;
include '../../include/baseTheme.php';

$nameTools = $langToolManagement;
add_units_navigation(TRUE);
$tool_content = "";
$head_content = <<<hCont
<script type="text/javascript">

<!-- Begin javascript menu swapper
function move(fbox, tbox) {
  var arrFbox = new Array();
  var arrTbox = new Array();
  var arrLookup = new Array();
  var i;
  for (i = 0; i < tbox.options.length; i++) {
    arrLookup[tbox.options[i].text] = tbox.options[i].value;
    arrTbox[i] = tbox.options[i].text;
  }
  var fLength = 0;
  var tLength = arrTbox.length;
  for(i = 0; i < fbox.options.length; i++) {
    arrLookup[fbox.options[i].text] = fbox.options[i].value;
    if (fbox.options[i].selected && fbox.options[i].value != "") {
      arrTbox[tLength] = fbox.options[i].text;
      tLength++;
    } else {
      arrFbox[fLength] = fbox.options[i].text;
      fLength++;
    }
  }
  arrFbox.sort();
  arrTbox.sort();
  fbox.length = 0;
  tbox.length = 0;
  var c;
  for(c = 0; c < arrFbox.length; c++) {
    var no = new Option();
    no.value = arrLookup[arrFbox[c]];
    no.text = arrFbox[c];
    fbox[c] = no;
  }
  for(c = 0; c < arrTbox.length; c++) {
    var no = new Option();
    no.value = arrLookup[arrTbox[c]];
    no.text = arrTbox[c];
    tbox[c] = no;
  }
}

function selectAll(cbList,bSelect) {
  for (var i=0; i<cbList.length; i++)
    cbList[i].selected = cbList[i].checked = bSelect
}

function reverseAll(cbList) {
  for (var i=0; i<cbList.length; i++) {
    cbList[i].checked = !(cbList[i].checked)
    cbList[i].selected = !(cbList[i].selected)
  }
}

function confirmation (name)
{
	if (confirm("$langDeleteLink " + name + "?"))
        {return true;}
    	else
        {return false;}

}
</script>
hCont;


if ($is_adminOfCourse) {
        if (isset($_GET['action'])) {
                $action = intval($_GET['action']);
        } else {
                $action = 0;
        }
	if (isset($_REQUEST['toolStatus']) ) {
		if(isset($_POST['toolStatActive'])) $tool_stat_active = $_POST['toolStatActive'];

		if (isset($tool_stat_active)) {
			$loopCount = count($tool_stat_active);
		} else  {
			$loopCount = 0;
		}
		$i =0;
		$publicTools = array();
		$tool_id = null;
		while ($i< $loopCount) {
			if (!isset($tool_id)) {
				$tool_id = " (`id` = " . $tool_stat_active[$i] .")" ;
			}
			else {
				$tool_id .= " OR (`id` = " . $tool_stat_active[$i] .")" ;
			}
			$i++;
		}

		//get the state of the agenda tool and store it in a session var. It is used to insert or delete
		//all events of the current lesson from the agenda table in the main db, used by eclass personalised
		//This way, if a lesson's agenda is inactive, any contents it might have are not diplayed in the
		//personalised interface
		$prevAgendaStateSQL = "SELECT `visible` FROM `accueil`WHERE `id` = 1";
		$res = db_query($prevAgendaStateSQL, $dbname);
		$prevAgendaStateRow = mysql_fetch_row($res);

		//reset all tools
		db_query("UPDATE `accueil` SET `visible` = 0", $dbname);

		//and activate the ones the professor wants active, if any
		if ($loopCount >0) {
			db_query("UPDATE accueil SET visible = 1 WHERE $tool_id", $dbname);
		}
		db_query("UPDATE `accueil` SET `visible` = 2 WHERE define_var = 'MODULE_ID_UNITS'", $dbname);
		
		if (isset($tool_stat_active) && is_array($tool_stat_active)) {
			if (in_array(1, $tool_stat_active)) {
				//if the agenda module is set to active
				if ($prevAgendaStateRow[0] != 1) {
					//and the agenda module was not active before, we need to parse the events to the main agenda table (main database)
					$sql = 'SELECT id, titre, contenu, day, hour, lasting
						FROM  agenda WHERE CONCAT(titre,contenu) != \'\'
						AND DATE_FORMAT(day,\'%Y %m %d\') >= \''.date("Y m d").'\'';

					//  Get all agenda events from each table & parse them to arrays
					$mysql_query_result = db_query($sql, $currentCourseID);

					$event_counter=0;
					while ($myAgenda = mysql_fetch_array($mysql_query_result)) {
						$lesson_agenda[$event_counter]['id']            = $myAgenda[0];
						$lesson_agenda[$event_counter]['title']         = $myAgenda[1];
						$lesson_agenda[$event_counter]['content']       = $myAgenda[2];
						$lesson_agenda[$event_counter]['date']          = $myAgenda[3];
						$lesson_agenda[$event_counter]['time']          = $myAgenda[4];
						$lesson_agenda[$event_counter]['duree']         = $myAgenda[5];
						$lesson_agenda[$event_counter]['lesson_code']   = $currentCourseID;
						$event_counter++;
					}

					for ($j=0; $j <$event_counter; $j++) {
						db_query("INSERT INTO agenda (lesson_event_id, titre, contenu, day, hour, lasting, lesson_code)
          						VALUES ('".$lesson_agenda[$j]['id']."',
                  				'".$lesson_agenda[$j]['title']."',
                  				'".$lesson_agenda[$j]['content']."',
                  				'".$lesson_agenda[$j]['date']."',
                  				'".$lesson_agenda[$j]['time']."',
                  				'".$lesson_agenda[$j]['duree']."',
                  				'".$lesson_agenda[$j]['lesson_code']."'
                  			)", $mysqlMainDb);
					}
				}
			} else {
				//if the agenda module is set to inactive
				if ($prevAgendaStateRow[0] != 0) {
					//and the agenda module was active before, we need to delete this lesson's events
					//from the main agenda table (main database)

					$perso_sql= "DELETE FROM $mysqlMainDb.agenda 
						WHERE lesson_code= '$currentCourseID'";
					db_query($perso_sql, $mysqlMainDb);
				}
			}
		}
	}

	if (isset($delete)) {
		$sql = "SELECT lien, define_var FROM accueil WHERE `id` = ". $delete ." ";
		$result = db_query($sql, $dbname);
		while ($res = mysql_fetch_row($result)){
			if($res[1] == "HTML_PAGE") {
				$link = explode(" ", $res[0]);
				$path = substr($link[0], 6);
				$file2Delete = $webDir . $path;
				@unlink($file2Delete);
			}
		}
		$sql = "DELETE FROM `accueil` WHERE `id` = " . $delete ." ";
		db_query($sql, $dbname);
		unset($sql);

		$tool_content .= "<p class=\"success_small\">$langLinkDeleted</p><br/>";
	}

        if (isset($_POST['submit'])) {
                // Add external link
                if ($action == 2) {
                        $link = @$_POST['link'];
                        $name_link = @$_POST['name_link'];
                        if (($link == 'http://') or ($link == 'ftp://') or empty($link) or empty($name_link))  {
                                $tool_content .= "<p class='caution_small'>$langInvalidLink<br /><a href='$_SERVER[PHP_SELF]?action=2'>$langHome</a></p><br />";
                                draw($tool_content, 2, 'course_tools');
                                exit();
                        }

                        $res = db_query('SELECT MAX(`id`) FROM `accueil`', $dbname);
                        list($mID) = mysql_fetch_row($res);

                        if ($mID<101) $mID = 101;
                        else $mID = $mID + 1;
                        $link = autoquote($link);
                        $name_link = autoquote($name_link);
                        db_query("INSERT INTO accueil VALUES ($mID, $name_link, $link, 'external_link', 1, 0, $link, '')");

                        $tool_content .= "<p class='success_small'>$langLinkAdded</p><br/>";
                } elseif ($action == 1) { 
                        $updir = "$webDir/courses/$currentCourseID/page/"; //path to upload directory
                        $size = "20971520"; //file size is 20M (1024x1024x20)
                        if (isset($file_name) and ($file_name != "") && ($file_size <= "$size") and ($link_name != "")) {

                                @copy("$file", "$updir/$file_name")
                                        or die("<p>$langCouldNot</p></tr>");

                                $sql = 'SELECT MAX(`id`) FROM `accueil` ';
                                $res = db_query($sql,$dbname);
                                while ($maxID = mysql_fetch_row($res)) {
                                        $mID = $maxID[0];
                                }

                                if($mID<101) $mID = 101;
                                else $mID = $mID+1;

                                $link_name = quote($link_name);
                                $lien = quote("../../courses/$currentCourse/page/$file_name");
                                db_query("INSERT INTO accueil VALUES (
                                                $mID,
                                                $link_name,
                                                $lien,
                                                'external_link',
                                                '1',
                                                '0',
                                                '',
                                                'HTML_PAGE'
                                                )", $currentCourse);

                                $tool_content .= "<p class=\"success_small\">$langOkSent</p><br/>";
                        } else {
                                $tool_content .= "<p class=\"caution_small\">$langTooBig<br /><a href=\"$_SERVER[PHP_SELF]?action=1\">$langHome</a></p><br />";
                                draw($tool_content, 2, 'course_tools');
                        }
                }
        } elseif ($action == 1) { // upload html file
                $nameTools = $langUploadPage;
                $navigation[]= array ("url"=>"course_tools.php", "name"=> $langToolManagement);
                $helpTopic = 'Import';

                $tool_content .= "<form method='post' action='$_SERVER[PHP_SELF]?submit=yes&action=1' enctype='multipart/form-data'>
                        <p>$langExplanation_0</p>
                        <p>$langExplanation_3</p>
                        <br />
                        <table width='99%' align='left' class='FormData'><tbody>
                        <tr><th class='left' width='220'>&nbsp;</th>
                            <td><b>$langExplanation_1</b></td>
                            <td>&nbsp;</td></tr>
                        <tr><th class='left'>$langSendPage</th>
                            <td><input type='file' name='file' size='35' accept='text/html' class='auth_input'></td>
                            <td><p align='right'><small>$langNoticeExpl</small></p></td></tr>
                        <tr><th class='left'>$langPgTitle</th>
                            <td><input type='Text' name='link_name' size='40' class='FormData_InputText'></td>
                            <td><p align='right'><small>$langExplanation_2</small></p></td></tr>
                        <tr><th class='left'>&nbsp;</th>
                            <td><input type='Submit' name='submit' value='$langAdd'></td>
                            <td>&nbsp;</td></tr>
                        </tbody></table></form>";
                draw($tool_content, 2, 'course_tools');
                exit();
        } elseif ($action == 2) { // add external link
                $nameTools = $langAddExtLink;
                $navigation[]= array ('url' => 'course_tools.php', 'name' => $langToolManagement);
                $helpTopic = 'Module';

                $tool_content .=  "<form method='post' action='$_SERVER[PHP_SELF]?action=2'>
                <br>
                <table width='99%' align='left' class='FormData'>
                <tbody>
                <tr>
                <th class='left' width='220'>&nbsp;</th>
                <td><b>$langExplanation_4</b></td>
                <td>&nbsp;</td>
                </tr>
                <tr>
                <th class='left'>$langLink&nbsp;:</th>
                <td><input type='text' name='link' size='50' value='http://' class='FormData_InputText'></td>
                <td>&nbsp;</td>
                </tr>
                <tr>
                <th class='left'>$langName&nbsp;:</th>
                <td><input type='Text' name='name_link' size='50' class='FormData_InputText'></td>
                <td>&nbsp;</td>
                </tr>
                <tr>
                <th class='left'>&nbsp;</th>
                <td><input type='submit' name='submit' value='$langAdd'></td>
                <td>&nbsp;</td>
                </tr>
                </thead>
                </table></form>";
                draw($tool_content, 2, 'course_tools');
                exit();
        }

	$activeTools = $inactiveTools = '';
	$toolArr = getSideMenu(2);
	$numOfToolGroups = count($toolArr);

	if (is_array($toolArr)) {
		$externalLinks = array(); // array used to populate the external tools table afterwards
		for ($i=1; $i< $numOfToolGroups; $i++){
			$numOfTools = count($toolArr[$i][1]);
			for ($j = 0; $j < $numOfTools; $j++){
                                if ($toolArr[$i][4][$j] < 100) {
                                        $class = '';
                                } else {
                                        $class = ' class="emphasised"';
                                        if ($i != 3) { // External links that are not admin tools
                                                array_push($externalLinks, array(
                                                        'text' => q($toolArr[$i][1][$j]),
                                                        'id' => $toolArr[$i][4][$j]));
                                        }
                                } 
				if ($i == 1) { // active tools
                                        $activeTools .= "<option$class value='" . $toolArr[$i][4][$j] . "'>" .
                                                        q($toolArr[$i][1][$j]) . "</option>\n";

				} elseif ($i == 2) { // inactive tools
                                        $inactiveTools .= "<option$class value='" . $toolArr[$i][4][$j] . "'>" .
                                                          q($toolArr[$i][1][$j]) . "</option>\n";
                                }
			}
		}
	}

	//output tool content
	$tool_content .= "
	<div id='operations_container'>
	  <ul id='opslist'>
	    <li><a href='course_tools.php?action=1'>$langUploadPage</a></li>
	    <li><a href='course_tools.php?action=2'>$langAddExtLink</a></li>
	  </ul>
	</div>";

	$tool_content .= <<<tForm
<form name="courseTools" action="$_SERVER[PHP_SELF]" method="post" enctype="multipart/form-data">
  <br/>
  <table class="FormData" align="center" width="99%" style="border: 1px solid #CAC3B5;">
  <thead>
  <tr>
    <td width="45%" style="color: #a33033;"><div align="center"><b>$langInactiveTools</b></div></td>
    <td width="10%" style="color: #727266;"><div align="center"><b>$langMove</b></div></td>
    <td width="45%" style="color: green;"><div align="center"><b>$langActiveTools</b></div></td>
  </tr>
  <tr>
    <td><div align="center">
        <select name="toolStatInactive[]" size=17 multiple class='FormData_InactiveTools'>\n$inactiveTools        </select>
        </div>
    </td>
    <td><div align="center">
        <input type="button" onClick="move(this.form.elements[0],this.form.elements[3])" value="   >>   " /><br/>
        <input type="button" onClick="move(this.form.elements[3],this.form.elements[0])" value="   <<   " />
        </div>
    </td>
    <td><div align="center">
        <select name="toolStatActive[]" size="17" multiple class='FormData_ActiveTools'>\n$activeTools        </select>
        </div>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><div align="center">
        <input type=submit value="$langSubmitChanges" name="toolStatus" onClick="selectAll(this.form.elements[3],true)" />
        </div>
        </td>
    <td>&nbsp;</td>
  </tr>
  </thead>
  </table>
</form>
tForm;

	$extToolsCount = count($externalLinks) ;
	if ($extToolsCount > 0)  {
		// show table to edit/delete external links
                $tool_content .= "<br/><br/><form method='post' action='course_tools.php'>
                        <table width='500'><tbody>
                        <tr>
                        <th rowspan='2'>&nbsp;</th>
                        <td colspan='2'><b>$langOperations</b></td>
                        </tr>
                        <tr>
                        <td class='left'><b>$langTitle</b></td>
                        <td class='left' width='20'><b>$langDelete</b></td>
                        </tr>";
		for ($i=0; $i < $extToolsCount; $i++) {
			if ($i % 2==0) {
				$tool_content .= "<tr>\n";
			} elseif ($i % 2 == 1) {
				$tool_content .= "<tr class='odd'>\n";
			}
			$tool_content .= "<th class='left' width='1'>
                                <img src='../../template/classic/img/external_link_on.gif' border='0' title='$langTitle' /></th>
                                <td class='left'>".$externalLinks[$i]['text']."</td>\n";
			$tool_content .= "<td align='center'>
                                <input type='image' src='../../template/classic/img/delete.gif' name='delete' value='" .
                                $externalLinks[$i]['id'] . "' onClick='return confirmation(\"" .
                                addslashes($externalLinks[$i]['text']) . "\");' title='$langDelete' /></td></tr>";
                }
		$tool_content .= "</tbody></table></form>";
	}
        draw($tool_content, 2,'course_tools', $head_content);
}
