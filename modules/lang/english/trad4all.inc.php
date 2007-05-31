<?
/*
      +----------------------------------------------------------------------+
      | GUnet eClass 2.0                                                     |
      | Asychronous Teleteaching Platform                                    |
      +----------------------------------------------------------------------+
      | Copyright (c) 2003-2007  GUnet                                       |
      +----------------------------------------------------------------------+
      |                                                                      |
      | GUnet eClass 2.0 is an open platform distributed in the hope that    |
      | it will be useful (without any warranty), under the terms of the     |
      | GNU License (General Public License) as published by the Free        |
      | Software Foundation. The full license can be read in "license.txt".  |
      |                                                                      |
      | Main Developers Group: Costas Tsibanis <k.tsibanis@noc.uoa.gr>       |
      |                        Yannis Exidaridis <jexi@noc.uoa.gr>           |
      |                        Alexandros Diamantidis <adia@noc.uoa.gr>      |
      |                        Tilemachos Raptis <traptis@noc.uoa.gr>        |
      |                                                                      |
      | For a full list of contributors, see "credits.txt".                  |
      |                                                                      |
      +----------------------------------------------------------------------+
      | Contact address: Asynchronous Teleteaching Group (eclass@gunet.gr),  |
      |                  Network Operations Center, University of Athens,    |
      |                  Panepistimiopolis Ilissia, 15784, Athens, Greece    |
      +----------------------------------------------------------------------+
*/



$langYes = "Yes";
$langNo = "No";

$iso639_2_code = "en";
$iso639_1_code = "eng";

$langNameOfLang['english']="english";
$langNameOfLang['french']="french";
$langNameOfLang['greek']="greek";

$charset = 'iso-8859-7';
$text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)

$langDay_of_weekNames['init'] = array('S', 'M', 'T', 'W', 'T', 'F', 'S');
$langDay_of_weekNames['short'] = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
$langDay_of_weekNames['long'] = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thu', 'Friday', 'Saturday');

$langMonthNames['init']  = array('J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D');
$langMonthNames['short'] = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
$langMonthNames['long'] = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
$langMonthNames['fine'] = $langMonthNames['long'];

$dateFormatShort =  "%b %d, %y";
$dateFormatLong  = '%A %B %d, %Y';
$dateTimeFormatLong  = '%B %d, %Y at %I:%M %p';
$timeNoSecFormat = '%I:%M %p';

$langBack="Back";
$langBackHome="Back to home";
$langBackList="Return to the list";

$langModify="Modify";
$langDelete="Delete";
$langTitle="Title";
$langHelp="Help";
$langOk="Ok";
$langAddIntro="Add introduction text";
$langUser = "User:";
$langProfessor = "Professor";
$langLogout = "Logout";

$langUserBriefcase = "User portfolio";
$langPersonalisedBriefcase = "Personalised portfolio";
$langEclass = "e-Class learning management system";
$langCopyrightFooter="Copyright notice";

$langGreek="Greek";
$langEnglish="English";

$langSearch="Search";
$langAdvancedSearch="Advanced search";
$langHomePage = "Home page";

$langNoAdminAccess = '
		<p><b>The page you tried to access requires a valid username and password.</b><br/>
		The system has automatically redirect you to the start page to login. This could have been caused
		by a mistyped URL or due to session timeout.</p>
';

$langLoginRequired = '
		<p><b>You are not enrolled to the lesson you are trying to access.</b><br/>
		The system has automatically redirect you to the start page to enroll to the lesson (if the lesson is open for registration).
		</p>
';

$langSessionIsLost = "
		<p><b>Your session has timed-out. </b><br/>The system has automatically redirect
		you to the start page to login again.</p>
			";

$langCheckProf = "
		<p><b>Your action requires professor privileges. </b><br/>
		The system has automatically redirect you to the start page to login (if you are
		the lesson's professor you will be allowed access tou the course administration tools).</p>
";

$langLessonDoesNotExist = "
	<p><b>The lesson you are trying to access does not exist.</b><br/>
	This could have been caused by a not allowed action or a platform error.</p>
";

$langCheckAdmin = "
		<p><b>Your action requires administrator privileges. </b><br/>
		The system has automatically redirect you to the start page to login (if you are
		the platform administrator you will be allowed access tou the administration tools).</p>
";

$langCheckGuest = "
		<p><b>The action you attempted to execute is not possible with guest user privileges. </b><br/>
		For security reasons the system has automatically redirect you to the start page to login again.</p>
";

$langCheckPublicTools = "
		<p><b>You tried to gain access to an inactive lesson module. </b><br/>
		For security reasons the system has automatically redirect you to the start page to login again.</p>
";
?>
