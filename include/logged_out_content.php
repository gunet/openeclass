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

if (!defined('INDEX_START')) {
	die("Action not allowed!");
}

if (isset($_SESSION['langswitch'])) {
	$language = $_SESSION['langswitch'];
}

include('lib/textLib.inc.php');

$tool_content .= <<<lCont
<div id="container_login">

<div id="wrapper">
<div id="content_login">
<p align='justify'>$langInfoAbout</p>
lCont;

$qlang = ($language == "greek")? 'el': 'en';
$sql = "SELECT `id`, `date`, `title` , `body` FROM `admin_announcements`
        WHERE `visible` = 'V' AND lang='$qlang' ORDER BY `date` DESC";
$result = db_query($sql, $mysqlMainDb);
if (mysql_num_rows($result) > 0) {
	$announceArr = array();
	while ($eclassAnnounce = mysql_fetch_array($result)) {
		array_push($announceArr, $eclassAnnounce);
	}
        $tool_content .= "<br/>
        <table width='99%' class='AnnouncementsList'>
	<thead><tr><th width='180'>$langAnnouncements
	<span class='feed'><a href='${urlServer}rss.php'>
	<img src='${urlServer}template/classic/img/feed.png' alt='RSS Feed' title='RSS Feed' />
	</a></span></th><th>&nbsp;</th></tr></thead>
	<tbody>";

	$numOfAnnouncements = count($announceArr);
	for($i=0; $i < $numOfAnnouncements; $i++) {
		$aid = $announceArr[$i]['id'];
		$tool_content .= "<tr><td colspan='2'>
		<img style='border:0px;' src='${urlAppend}/template/classic/img/arrow_grey.gif' alt='' />
		<b><a href='modules/announcements/main_ann.php?aid=$aid'>".q($announceArr[$i]['title'])."</a></b>
		&nbsp;(".claro_format_locale_date($dateFormatLong, strtotime($announceArr[$i]['date'])).")
		<p>
		".standard_text_escape(ellipsize($announceArr[$i]['body'], 150, "<strong>&nbsp;<a href='modules/announcements/main_ann.php?aid=$aid'>....</a></strong>"))."<br /></p>
		</td>
		</tr>";
	}
	$tool_content .= "</tbody></table>";
}

$shibactive = mysql_fetch_array(db_query("SELECT auth_default FROM auth WHERE auth_name='shibboleth'"));
if ($shibactive['auth_default'] == 1) {
	$shibboleth_link = "<a href='{$urlServer}secure/index.php'>$langShibboleth</a><br /><br />";
} else {
	$shibboleth_link = "";
}

$tool_content .= "</div></div>";

if (!get_config('dont_display_login_form')) {
	$tool_content .= "<div id='navigation'>
	 <table width='99%' class='tbl'>
	 <tr>
	   <th class='LoginHead'><b>$langUserLogin </b></th>
	 </tr>
	 <tr>
	   <td class='LoginData'>
	   <form action='${urlSecure}index.php' method = 'post'>
	   $langUsername <br />
	   <input class='Login' name='uname' size='20' /><br />
	   $langPass <br />
	   <input class='Login' name='pass' type = 'password' size = '20' /><br /><br />
	   <input class='Login' name='submit' type = 'submit' size = '20' value = '$langEnter' /><br />
	   $warning<br />$shibboleth_link
	   <a href='modules/auth/lostpass.php'>$lang_forgot_pass</a>
	   </form>
	   </td>
	 </tr>
	</table>
	</div>";
}

$tool_content .= "<div id='extra'>{ECLASS_HOME_EXTRAS_RIGHT}</div></div>";
