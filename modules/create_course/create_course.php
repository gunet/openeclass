<?

/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    	Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    	Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/*===========================================================================
	create_course.php
* @version $Id$
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================        

 	The script requires some fields to be filled-in, thus it checks the
 	validity of the entries by javascripts.
==============================================================================*/


$require_login = TRUE;
$require_prof = TRUE;
$langFiles = array('create_course', 'opencours');

$require_help = TRUE;
$helpTopic = 'CreateCourse';

include '../../include/baseTheme.php';
$nameTools = $langCreateCourse . " (" . $langCreateCourseStep ." 1 " .$langCreateCourseStep2 . " 3 )" ;

/*$local_head = '
<script type="text/javascript">
function validate()
{
  if (document.forms[0].intitule.value=="")
  {
      alert("'.$langAlertTitle.'");
      return false;
  }
  if (document.forms[0].titulaires.value=="")
  {
      alert("'.$langAlertProf.'");
      return false;
  }
    return true;
}
</script>
';*/

/*
function validate() {
    if (document.forms[0].description.value==\"\") {
        alert(\"Παρακαλώ συμπληρώστε μια σύντομη περιγραφή για το μάθημα!\");
        return false;
    }
      if (document.forms[0].course_keywords.value==\"\") {
          alert(\"Παρακαλώ συμπληρώστε τις λέξεις κλειδιά του μαθήματος!\");
          return false;
    }
  return true;
}
*/

$tool_content = "";
$titulaire_probable="$prenom $nom";
$local_style = "input { font-size: 12px; }";

$tool_content .= "<form method='post' action='$_SERVER[PHP_SELF]' onsubmit=\"return validate();'>";
$tool_content .= "<input type='hidden' name='intitule' value='".htmlspecialchars($_POST['intitule'])."'>
      <input type='hidden' name='faculte' value='".htmlspecialchars($_POST['faculte'])."'>
      <input type='hidden' name='titulaires' value='".htmlspecialchars($_POST['titulaires'])."'>
      <input type='hidden' name='type' value='".htmlspecialchars($_POST['type'])."'>
      <input type='hidden' name='languageCourse' value='".htmlspecialchars($_POST['languageCourse'])."'>
      <input type='hidden' name='description' value='".htmlspecialchars($_POST['description'])."'>
      <input type='hidden' name='course_addon' value='".htmlspecialchars($_POST['course_addon'])."'>
      <input type='hidden' name='course_keywords' value='".htmlspecialchars($_POST['course_keywords'])."'>";

$tool_content .= "<input type='hidden' name='visit' value='".htmlspecialchars($_POST['visit'])."'>";

if (isset($back1) or !isset($visit)) {

// display form
$tool_content .= "
        <FIELDSET style=\"PADDING-RIGHT: 7px; PADDING-LEFT: 7px; PADDING-BOTTOM: 7px; PADDING-TOP: 7px\">
        <LEGEND>$langCreateCourseStep1Title</LEGEND>
        <table border=0><tr valign=\"top\">
        <td colspan=\"2\" valign=\"top\">
        <span class='explanationtext' style='font-weight:bold;'>$langFieldsRequ</span></td>
        </tr>
        <tr valign=\"top\">
        <td width=\"160\" valign=\"top\">
        <span class='labeltext'>$langTitle&nbsp;:</span>
        </td>
        <td valign=top>
        <span class=a>
        <input type='text' name='intitule' size='60' class='auth_input' value='$intitule'>&nbsp;
				<span class='explanationtext'>$langEx</span>
        </td></tr>
        <tr>
        <td valign=top><span class='labeltext'>$langFac&nbsp;:</span></td>
        <td valign=\"top\">";

$tool_content .= "<select name=\"faculte\" class=auth_input>";

/*
$resultFac=mysql_query("SELECT name FROM faculte ORDER BY number");
while ($myfac = mysql_fetch_array($resultFac)) {
        if($myfac['name'] == $facu)
                $tool_content .= "<option selected>$myfac[name]</option>";
        else
                $tool_content .= "<option>$myfac[name]</option>";
}
$tool_content .= "</select>&nbsp;<span class='explanationtext'>$langTargetFac</span></td></tr>";
*/

  		$resultFac=mysql_query("SELECT id,name FROM `$mysqlMainDb`.faculte ORDER BY number");
			while ($myfac = mysql_fetch_array($resultFac)) {	
				$tool_content .= "<option value=\"".$myfac['id']."--".$myfac['name']."\">$myfac[name]</option>";
			}
			$tool_content .= "</select>";

unset($repertoire);
$tool_content .= "<tr valign='top'><td width='100' valign='top'>
		        <span class='labeltext'>$langProfessors&nbsp;:</span>
    		    </td>
        		<td valign='top'>
						<input type='text' name='titulaires' size='60' value='".$titulaire_probable."' class=auth_input></td>
		        </tr>
  	    	  <tr>
    	    	<td><span class='labeltext'>$m[type]&nbsp;:</span></td>
	    	    <td>";
$tool_content .= " ".selection(array('pre' => $m['pre'], 'post' => $m['post'], 'other' => $m['other']), 'type', $type)." ";
$tool_content .= "</td></tr>
                <tr><td><span class='labeltext'>$langLn&nbsp;:</span></td>
                <td>";

$tool_content .= " ".selection(array('greek' => $langNameOfLang['greek'], 
																		'english' => $langNameOfLang['english']), 'languageCourse', $languageCourse)." ";

$tool_content .= "</td></tr></table></FIELDSET><br/>";
$tool_content .= "<input type='hidden' name='visit' value='true'>";
$tool_content .= "<input type='submit' name='create2' value='$langNextStep >'>";
}

// --------------------------------
// step 2 of creation
// --------------------------------

 elseif (isset($create2) or isset($back2))  {

	$nameTools = $langCreateCourse . " (" . $langCreateCourseStep." 2 " .$langCreateCourseStep2 . " 3 )";
	$tool_content .= "
	<FIELDSET style=\"PADDING-RIGHT: 7px; PADDING-LEFT: 7px; PADDING-BOTTOM: 7px; PADDING-TOP: 7px\">
        <LEGEND>$langCreateCourseStep2Title</LEGEND>
        <table border=0><tr valign=\"top\">
			<td colspan=\"2\" valign=\"top\">
        <span class='explanationtext' style='font-weight:bold;'>$langFieldsOptionalNote</span></td>
        </tr>
			<tr><th>$langDescrInfo:</th>   
			<td>
			<textarea name=\"description\" value='".$description."' cols=\"50\" rows=\"4\">$description</textarea>
			</td></tr>
			<tr>
			<th>$langCourseKeywords</th><td>
			<textarea name=\"course_keywords\" value='".$course_keywords."' cols=\"50\" rows=\"2\">$course_keywords</textarea>
		 </td>
			</tr>
			<tr>
			<th>$langCourseAddon</th>
			<td>
			<textarea name=\"course_addon\" value='".$course_addon."' cols=\"50\" rows=\"4\">$course_addon</textarea>
			</td>
			</tr>
			</table></fieldset>
			<br/>
			<input type='submit' name='back1' value='< $langPreviousStep '>
			<input type='submit' name='create3' value='$langNextStep >'>
		";

}  elseif (isset($create3) or isset($back2)) {
	
		$nameTools = $langCreateCourse . " (" . $langCreateCourseStep." 3 " .$langCreateCourseStep2 . " 3 )" ;
    $tool_content .= "
		<FIELDSET style=\"PADDING-RIGHT: 7px; PADDING-LEFT: 7px; PADDING-BOTTOM: 7px; PADDING-TOP: 7px\">
        <LEGEND>$langCreateCourseStep3Title</LEGEND>
    <table border=0><tr valign=\"top\">
    <td colspan=\"2\" valign=\"top\">
    <span class='explanationtext' style='font-weight:bold;'>$langFieldsOptionalNote</span></td></tr>
   	</fieldset>
		<tr><th>$langAccess</th>
    <td>
    <fieldset>
    <legend>$langAvailableTypes</legend>
    <p>
    <input name=\"formvisible\" type=\"radio\" value=\"2\" checked=\"checked\" />$langPublic<br />
    <input name=\"formvisible\" type=\"radio\" value=\"1\" />$langPrivOpen<br />
    <input name=\"formvisible\" type=\"radio\" value=\"0\" />$langPrivate</p>
    </fieldset>
    </td>";
		
//    help("CreateCourse_formvisible");
		
$tool_content .= "</tr><tr>
      <th>$langModules</th>
      <td>
			<fieldset>
    	<legend>$langSubsystems</legend>
      <table width=\"99%\">
      <tr>
      <td><input name=\"subsystems[]\" type=\"checkbox\" value=\"1\" checked=\"checked\" />$langAgenda</td>
      <td><input name=\"subsystems[]\" type=\"checkbox\" value=\"2\" checked=\"checked\" />$langLinks</td>
      <td><input name=\"subsystems[]\" type=\"checkbox\" value=\"3\" checked=\"checked\" />$langDoc</td>
      <td><input name=\"subsystems[]\" type=\"checkbox\" value=\"4\" checked=\"checked\" />$langVideo</td>
      </tr>
      <tr>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"5\" />$langWorks</td>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"7\" />$langAnnouncements</td>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"9\" />$langForums</td>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"10\" />$langExercices</td>
      </tr>
      <tr>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"15\" />$langDropBox</td>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"16\" />$langGroups</td>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"19\" />$langConference</td>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"20\" />$langCourseDesc</td>
      </tr>
      <tr>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"21\" />$langQuestionnaire</td>
      <td><input type=\"checkbox\" name=\"subsystems[]\"  value=\"23\" checked=\"checked\" />$langLearnPath</td>
      <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"26\" checked=\"checked\"/>$langWiki</td>
      <td></td>
      </tr>
      </table>
			</fieldset>
      </td>
      ";
			
//       help("CreateCourse_subsystems");
			 
$tool_content .= "<tr><td><span class='labeltext'>$langLn&nbsp;:</span></td></tr>";

// help("CreateCourse_lang");

$tool_content .= "</tr>
    </thead>
    </table>
    <br/>
		<input type='submit' name='back2' value='< $langPreviousStep '> 
		<input type='submit' name='create_course' value=\"$langFinalize\">";
} // end of create3

// create the course and the course database
if (isset($create_course)) {  

   //h metavlhth faculte periexei to fac_id kai to onoma tou tmhmatos xwrismena me dyo dashes
    //to $facid pairnei timh apo thn $faculte
    list($facid, $facname) = split("--", $faculte);
    //to $faculte ksanapairnei thn timh mono tou onomatos tou tmhmatos gia logous compability
    $faculte = $facname;

    $repertoire = new_code(find_faculty_by_name($faculte));
		$language=$languageCourse;
    include("../lang/$language/create_course.inc.php");
    if(empty($intitule) OR empty($repertoire)) {
        $tool_content .= "<tr bgcolor=\"$color2\" height=\"400\">
        <td bgcolor=\"$color2\" colspan=\"2\" valign=\"top\">
            <br>
            <font face=\"arial, helvetica\" size=\"2\">
            $langEmpty
            </font>
        </td>
    </tr>";
    } else {	// if all form fields fulfilled
        // replace lower case letters by upper case in code_cours
        $repertoire=strtoupper($repertoire);
        $faculte_lower=strtolower($faculte);

        //remove space in code_cours
        $repertoire = str_replace (" ", "", $repertoire);
        $repertoire_lower=strtolower($repertoire);

        $dbList = mysql_list_dbs();
        $cnt = 0;
        $dbNumber = mysql_num_rows($dbList);
        while ($cnt < $dbNumber) {
            $dbCode = mysql_db_name($dbList, $cnt);
            if ($dbCode == $repertoire) {
            $tool_content .= "<tr bgcolor=\"$color2\" height=\"400\">
            <td colspan=\"2\" valign=\"top\">
            <font face=\"arial, helvetica\" size=\"2\">
                $langCodeTaken.
                <br>
                <p>&nbsp;</p>
            </td></tr></table>";
            exit();
            }			// end if ($dbCode == $repertoire)
            $cnt++;
        }				// end while ($cnt < $dbNumbert)


// ---------------------------------------------------------
//  all the course db queries are inside the following script
// ---------------------------------------------------------
require "create_course_db.php";


// ------------- update main Db------------

mysql_select_db("$mysqlMainDb");

    mysql_query("INSERT INTO cours SET
        code = '$code',
        languageCourse = '$languageCourse',
        intitule = '$intitule',
        description = '$description',
        course_addon = '$course_addon',
        course_keywords = '$course_keywords',
        faculte = '$facname',
        visible = '$formvisible',
        cahier_charges = '',
        titulaires = '$titulaires',
        fake_code = '$code',
        `type` = '$type',
        faculteid = '$facid'");

    mysql_query("INSERT INTO cours_user SET
        code_cours = '$repertoire',
        user_id = '$uid',
        statut = '1',
        role = '$langProfessor',
        tutor='1'");

		mysql_query("INSERT INTO cours_faculte SET
         faculte = '$faculte',
         code = '$repertoire',
         facid = '$facid'");

// create directories
    
		umask(0);
    mkdir("../../courses/$repertoire", 0777);
    mkdir("../../courses/$repertoire/image", 0777);
    mkdir("../../courses/$repertoire/document", 0777);
    mkdir("../../courses/$repertoire/dropbox", 0777);
    mkdir("../../courses/$repertoire/page", 0777);
    mkdir("../../courses/$repertoire/work", 0777);
    mkdir("../../courses/$repertoire/group", 0777);
    mkdir("../../courses/$repertoire/temp", 0777);
    mkdir("../../courses/$repertoire/scormPackages", 0777);

    //mkdir("../../courses/$repertoire/video", 0777);
    mkdir("../../video/$repertoire", 0777);
    //symlink("../../video/$repertoire","../../courses/$repertoire/video");

    $titou='$dbname';

// ----------- main course index.php -----------
    
		$fd=fopen("../../courses/$repertoire/index.php", "w");
$string="<?php
session_start();
$titou=\"$repertoire\";
session_register(\"dbname\");
include(\"../../modules/course_home/course_home.php\");
?>";

fwrite($fd, "$string");
$status[$repertoire]=1;
session_register("status");

$tool_content .= "<tr bgcolor=$color2>
    <td colspan=3>
    <font face='arial, helvetica' size=2>
    $langJustCreated <b>$intitule</b><br><br><br>
    <a href=\"../../courses/$repertoire/index.php\">$langEnter</a><br><br><br>
    $langEnterMetadata
    </font><br>
    </td></tr>";
		
 } // else

} // end of submit

$tool_content .= "</table>";
$tool_content .= "</form>";

draw($tool_content, '1', '', $local_head);
?>
