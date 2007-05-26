<?PHP
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

/**
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
 * 4. Delete the last two when inactive
 *
 */

$require_current_course = TRUE;
$langFiles = array('toolManagement', 'create_course', 'external_module', 'import');
$require_help = TRUE;
$helpTopic = 'courseTools';
$require_prof = true;
$require_login = true;
include '../../include/baseTheme.php';


$nameTools = $langToolManagement;

$tool_content = "";
$head_content = <<<hCont
<script type="text/javascript" language="JavaScript">

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
}
else {
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
//  End -->
</script>

<script type="text/javascript" language="JavaScript">

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

</script>

<script>
function confirmation (name)
{
		if (confirm("'.$langDeleteLink.' ("+ name + ") ?"))
        {return true;}
    	else
        {return false;}
    
}
</script>
hCont;


if ($is_adminOfCourse){
	global $dbname;
	if  (isset($_REQUEST['toolStatus']) ){
		if(isset($_POST['toolStatActive'])) $tool_stat_active = $_POST['toolStatActive'];
		//	dumpArray($_POST['toolStatActive']);
		//	dumpArray($_POST['toolStatInactive']);

		$hideSql = "UPDATE  `accueil` SET `visible` = 0 ";



		$loopCount = count($tool_stat_active);
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
		$prevAgendaStateSQL = "SELECT `visible`FROM `accueil`WHERE `id` =1";
		$res = db_query($prevAgendaStateSQL, $dbname);
		$prevAgendaStateRow = mysql_fetch_row($res);

		//reset all tools
		db_query($hideSql, $dbname);
		//and activate the ones the professor wants active
		$publicSql = "UPDATE  `accueil`
								SET
								`visible` = 1 WHERE $tool_id";

		db_query($publicSql, $dbname);


		if (in_array(1, $tool_stat_active)) {
			//if the agenda module is set to active
			if ($prevAgendaStateRow[0] != 1) {
				//and the agenda module was not active before, we need to parse the events to
				//the main agenda table (main database)

				$sql = 'SELECT id, titre, contenu, day, hour, lasting
                FROM  agenda WHERE CONCAT(titre,contenu) != \'\'
                AND DATE_FORMAT(day,\'%Y %m %d\') >= \''.date("Y m d").'\'';

				//  Get all agenda events from each table & parse them to arrays
				$mysql_query_result = db_query($sql, $currentCourseID);

				$event_counter=0;
				while ($myAgenda = mysql_fetch_array($mysql_query_result)) {
					$lesson_agenda[$event_counter]['id']                  = $myAgenda[0];
					$lesson_agenda[$event_counter]['title']               = $myAgenda[1];
					$lesson_agenda[$event_counter]['content']             = $myAgenda[2];
					$lesson_agenda[$event_counter]['date']                = $myAgenda[3];
					$lesson_agenda[$event_counter]['time']                = $myAgenda[4];
					$lesson_agenda[$event_counter]['duree']               = $myAgenda[5];
					$lesson_agenda[$event_counter]['lesson_code']         = $currentCourseID;

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
                             WHERE
                             lesson_code= '$currentCourseID'";

				db_query($perso_sql, $mysqlMainDb);

			}

		}

	}

	if (isset($delete)) {
		$sql = "SELECT `lien`, `define_var`
				FROM accueil
				WHERE `id` = ". $delete ." ";
		$result = db_query($sql, $dbname);
		while ($res = mysql_fetch_row($result)){
			//			dumpArray($res);
			if($res[1] == "HTML_PAGE") {
				$link = explode(" ", $res[0]);
				$path = substr($link[0], 6);
				$file2Delete = $webDir . $path;
				//			echo $file2Delete;
				@unlink($file2Delete);

			}
		}
		$sql = "DELETE FROM `accueil` WHERE `id` = " . $delete ." ";
		db_query($sql, $dbname);
		unset($sql);

		$tool_content .= "
		<table width=\"99%\">
			<tbody>
				<tr>
					<td class=\"success\">
					<p><b>$deleteSuccess</b></p>
					
					</td>
				</tr>
			</tbody>
		</table>
		<br/><br/>
		";
	}

	

	//--add external link

	if(isset($submit) &&  @$action ==2){

		if (($link == "http://") or ($link == "ftp://") or empty($link))  {
			$tool_content .= "
		<table>
			<tbody>
				<tr>
					<td class=\"caution\">
					<p><b>$langInvalidLink</b></p>
					<a href=\"../../courses/$currentCourseID/index.php\">$langHome</a>
					</td>
				</tr>
			</tbody>
		</table>
		";

			draw($tool_content, 2);
			exit();
		}

		$sql = 'SELECT MAX(`id`) FROM `accueil` ';
		$res = db_query($sql,$dbname);
		while ($maxID = mysql_fetch_row($res)) {
			$mID = $maxID[0];
		}

		if($mID<101) $mID = 101;
		else $mID = $mID+1;


		mysql_query("INSERT INTO accueil VALUES ($mID,
					'$name_link',
					'$link \"target=_blank',
					'external_link',
					'1',
					'0',
					'$link',
					''
					)");

		$tool_content .= "
		<table width=\"99%\">
			<tbody>
				<tr>
					<td class=\"success\">
					<p><b>$langAdded</b></p>
					
					</td>
				</tr>
			</tbody>
		</table>
		<br/><br/>
		";
		unset($action);
	}

	if(isset($submit) &&  @$action ==1){//upload html page
		// UPLOAD FILE TO "documents" DIRECTORY + INSERT INTO documents TABLE
		$updir = "$webDir/courses/$currentCourseID/page/"; //path to upload directory
		$size = "20000000"; //file size ex: 5000000 bytes = 5 megabytes
		if (($file_name != "") && ($file_size <= "$size" )) {

			$file_name = str_replace(" ", "", $file_name);
			$file_name = str_replace("é", "e", $file_name);
			$file_name = str_replace("è", "e", $file_name);
			$file_name = str_replace("ê", "e", $file_name);
			$file_name = str_replace("à", "a", $file_name);

			@copy("$file", "$updir/$file_name")
			or die("
		
			<p>
				$langCouldNot
			</p>
	</tr>");
			$sql = 'SELECT MAX(`id`) FROM `accueil` ';
			$res = db_query($sql,$dbname);
			while ($maxID = mysql_fetch_row($res)) {
				$mID = $maxID[0];
			}

			if($mID<101) $mID = 101;
			else $mID = $mID+1;

			db_query("INSERT INTO accueil VALUES (
					$mID,
					'$link_name',
					'../../courses/$currentCourse/page/$file_name \"target=_blank',
					'external_link',
					'1',
					'0',
					'',
					'HTML_PAGE'
					)", $currentCourse);

			$tool_content .=  "
					<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
						<p><b>$langOkSent</b></p>
					</td>
					</tr>
				</tbody>
			</table><br/>";
		} else {
			$tool_content .= "
			<table>
				<tbody>
					<tr>
						<td class=\"caution\">
					
						<p><b>$langTooBig</b></p>
					
						</td>
					</tr>
				</tbody>
			</table>
			";
			draw($tool_content, 2);
		}	// else

		unset($action);
	}

}

//------------------------------------------------------
if ($is_adminOfCourse && @$action == 1) {//upload html file

	$nameTools = $langUploadPage;
	$navigation[]= array ("url"=>"course_tools.php", "name"=> $langToolManagement);
	$helpTopic = 'Import';

	$tool_content .=  "
		<p>$langExplanation</p>
			
			
		<form method=\"POST\" action=\"$PHP_SELF?submit=yes&action=1\" enctype=\"multipart/form-data\">
			<table>
			<thead>
				<tr>
					<th>
						
							$langSendPage :
						
					</th>
					<td>
						<input type=\"file\" name=\"file\" size=\"35\" accept=\"text/html\">
					</td>
				</tr>
				<tr>
					<th>
						
							$langPgTitle :
						
					</th>
					<td>
						<input type=\"Text\" name=\"link_name\" size=\"50\">
					</td>
				</tr>
				</thead>
				</table>
				<br>
						<input type=\"Submit\" name=\"submit\" value=\"$langAddOk\">
				
</form>";

	draw($tool_content, 2);
	exit();
}

if ($is_adminOfCourse && @$action == 2) {//add external link

	$nameTools = $langAddExtLink;
	$navigation[]= array ("url"=>"course_tools.php", "name"=> $langToolManagement);
	$helpTopic = 'Module';

	$tool_content .=  "
			<form method=\"post\" action=\"$_SERVER[PHP_SELF]?submit=yes&action=2\">
			<table>
				<thead>
				<tr>
					<th>
						
							$langLink&nbsp;:
					</th>
					<td>
						<input type=\"text\" name=\"link\" size=\"50\" value=\"http://\">
					</td>
				</tr>
				<tr>
					<th>
							$langName&nbsp;:
					</th>
					<td>
						<input type=\"Text\" name=\"name_link\" size=\"50\">
					</td>
				</tr>
				</thead></table>
				<br>
					<input type=\"Submit\" name=\"submit\" value=\"$langAdd\">
				
			</form>
			";
	draw($tool_content, 2);
	exit();

}
//---------------------------------------------------------
if ($is_adminOfCourse) {

	$activeTools = $inactiveTools = '';
	$toolArr = getSideMenu(2);
	$numOfToolGroups = count($toolArr);

	if (is_array($toolArr)){
		$externalLinks = array();//array used to populate the external tools table afterwards
		for($i=0; $i< $numOfToolGroups; $i++){

			$numOfTools = count($toolArr[$i][1]);


			for($j=0; $j< $numOfTools; $j++){

				if ($i  == 0){//active tools
					if ($toolArr[$i][4][$j] < 100) {
						$activeTools .= "<option value=\"".$toolArr[$i][4][$j]."\">
										".$toolArr[$i][1][$j]."
										</option>";
					} else {

						$activeTools .= "<option class=\"emphasised\" value=\"".$toolArr[$i][4][$j]."\">
										".$toolArr[$i][1][$j]."
										</option>";
						$arr['text']=$toolArr[$i][1][$j];
						$arr['id'] = $toolArr[$i][4][$j];
						array_push($externalLinks, $arr);
					}


				}  elseif ($i ==  2) {//admin tools

				} elseif ($i == 1){//inactive tools

					//					if ($toolArr[$i][4][$j] > 100) {//if it's not a core tool give the ability to delete it
					//						$deleteExternLink = $_SERVER['PHP_SELF'] . "?delete=" . $toolArr[$i][4][$j];
					//						$delLink = " (<a href=\"$deleteExternLink\">$langDelete</a>)";
					//					}

					if ($toolArr[$i][4][$j] < 100) {
						$inactiveTools .= "<option value=\"".$toolArr[$i][4][$j]."\">
										".$toolArr[$i][1][$j]."
										</option>";
					} else {

						$inactiveTools .= "<option class=\"emphasised\" value=\"".$toolArr[$i][4][$j]."\">
										".$toolArr[$i][1][$j]."
										</option>";
						$arr['text']=$toolArr[$i][1][$j];
						$arr['id'] = $toolArr[$i][4][$j];
						array_push($externalLinks, $arr);
					}

				}

			}


		}

	}

	//output tool content
	$tool_content .= "
	<div id=\"operations_container\">
	<ul id=\"opslist\">
	<li><a href=\"".$_SERVER['PHP_SELF']."?action=1\">".$langUploadPage."</a></li>
	<li><a href=\"".$_SERVER['PHP_SELF']."?action=2\">".$langAddExtLink."</a></li>
	</ul>
	</div>
";

	$tool_content .= <<<tForm
<form name="courseTools" action="$_SERVER[PHP_SELF]" method="post" enctype="multipart/form-data">
<table>
	<thead>
		<tr>
			<th>$langInactiveTools</th>
			<th>$langMove</th>
			<th>$langActiveTools</th>
		</tr>
		<tr>
			<td>
				<select name="toolStatInactive[]" size=17 multiple>
					$inactiveTools
				</select>
			</td>
			<td align="center">
				<input type="button" onClick="move(this.form.elements[0],this.form.elements[3])" value="   >>   " />
				<br>
				<input type="button" onClick="move(this.form.elements[3],this.form.elements[0])" value="   <<   " /></td>
			<td>
			
			<select name="toolStatActive[]" size="17" multiple>
				$activeTools
			</select>
			
			</td>
		</tr>
	</thead>
</table>
<br />
<input type=submit value="$langSubmitChanges"  name="toolStatus" onClick="selectAll(this.form.elements[3],true)">
 </form>
tForm;

	$extToolsCount = count($externalLinks) ;
	if ($extToolsCount>0)  {
		//show table to edit/delete external links
		$tool_content .= "
			<br/>
			<table width=\"500\"><caption>$langOperations</caption>
			<thead>
				<tr> 
					<th align=\"left\">
						$langToolTitle
					</th>
					
					<th  width=\"100\">
						$langDelete
					</th>
				</tr>
			</thead>
			<tbody>";



		for ($i=0; $i <$extToolsCount; $i++) {

			if ($i%2==0) {
				$tool_content .= "<tr>";
			}
			elseif ($i%2==1) {
				$tool_content .= "<tr class=\"odd\">";
			}
			$tool_content .= "
				
					<td><div class=\"cellpos\">
						".$externalLinks[$i]['text']."
					</div>
					</td>
					";

			$tool_content .= "
			
	<td><div class=\"cellpos\">
	<a href=\"".$_SERVER['PHP_SELF'] . "?delete=" . $externalLinks[$i]['id']."\" onClick=\"return confirmation('".addslashes($externalLinks[$i]['text'])."');\">
	<img src=\"../../template/classic/img/delete.gif\" border=\"0\" alt=\"".$langDelete."\"></a>
	</div>
	</td>
	</tr>";


		}	// for loop
		$tool_content .= <<<tCont4
	</tbody>
	</table>
tCont4;
}

draw($tool_content, 2,'course_tools', $head_content);
}
?>