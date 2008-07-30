<?PHP
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
============================================================================
 * Logged Out Component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This component creates the content of the index page when the 
 * user is not logged in
 * It includes: 
 * 1. The login form, 
 * 2. an optional content below the login form, 
 * 3. The introductory message
 * 4. Platform announcements (If there are any)
 *
 */

//query for greek announcements
$sql_el ="SELECT `date`, `gr_title` , `gr_body` , `gr_comment`
		FROM `admin_announcements`
		WHERE `visible` = \"V\"
		";
//query for english announcements
$sql_en ="SELECT `date`, `en_title` , `en_body` , `en_comment`
		FROM `admin_announcements`
		WHERE `visible` = \"V\"
		";

if(session_is_registered('langswitch')) {
	$language = $_SESSION['langswitch'];
}

if ($language == "greek") 
		$sql = $sql_el;
else 
		$sql = $sql_en;

$tool_content .= <<<lCont
<div id="container_login">

<div id="wrapper">
<div id="content_login">
<p align='justify'>$langInfoAbout</p>
lCont;

$tool_content .='<br>';

 @include("./modules/lang/$language/admin.inc.php");
 @include("./modules/lang/$language/about.inc.php");

$result = db_query($sql, $mysqlMainDb);
if (mysql_num_rows($result) > 0) {
	$announceArr = array();
	while ($eclassAnnounce = mysql_fetch_array($result)) {
		array_push($announceArr, $eclassAnnounce);
	}
	$tool_content .= "
<br/>

<table width=\"99%\">
	<thead><tr><th>$langAnnouncements </th></tr></thead>
	<tbody>";

	$numOfAnnouncements = count($announceArr);

	for($i=0; $i < $numOfAnnouncements; $i++) {
		if ($i%2 == 0) 
				$rowClass = "class=\"odd\"";
		else 
				$rowClass = "";

		$tool_content .= "
		<tr $rowClass>
			<td>
				<p><b>".greek_format($announceArr[$i][0]).":</b> <u>".$announceArr[$i][1]."</u></p>
				<p>".$announceArr[$i][2]."</p>
				<p><i>".$announceArr[$i][3]."</i></p>
			</td>
		</tr>
		";
	}
	$tool_content .= "</tbody></table>";
}

$tool_content .= <<<lCont2
</div>
</div>
<div id="navigation">

 <table width="99%">
 <tr>
   <th class="LoginHead"> <b>$langUserLogin </b></th>
 </tr>
 <tr>
   <td class="LoginData">
   <form action="${urlSecure}index.php" method="post">
   $langUserName <br>
   <input class="Login" name="uname" size="20"><br>
   $langPass <br>
   <input class="Login" name="pass" type="password" size="20"><br><br>
   <input class="Login" value="$langEnter" name="submit" type="submit"><br>
   $warning<br>
   <a href="modules/auth/lostpass.php">$lang_forgot_pass</a>
   </form>
   </td>
 </tr>
</table>

</div>
<div id="extra">
<p>{ECLASS_HOME_EXTRAS_RIGHT}</p>
</div>

</div>

lCont2;

?>
