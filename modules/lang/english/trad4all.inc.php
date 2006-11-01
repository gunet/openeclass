<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                               |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$           |
	  |   English Translation                                                |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Andrew Lynn       <Andrew.Lynn@strath.ac.uk>                |
      +----------------------------------------------------------------------+
 */



$iso639_2_code = "en";
$iso639_1_code = "eng";

$langNameOfLang['brazilian']="brazilian";
$langNameOfLang['english']="english";
$langNameOfLang['finnish']="finnish";
$langNameOfLang['french']="french";
$langNameOfLang['german']="german";
$langNameOfLang['italian']="italian";
$langNameOfLang['japanese']="japanese";
$langNameOfLang['polish']="polish";
$langNameOfLang['simpl_chinese']="simplified chinese";
$langNameOfLang['spanish']="spanish";
$langNameOfLang['swedish']="swedish";
$langNameOfLang['thai']="thai";
$langNameOfLang['greek']="greek";

$charset = 'iso-8859-7';
$text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)
$left_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'helvetica, arial, geneva, sans-serif';
$number_thousands_separator = ',';
$number_decimal_separator = '.';
$byteUnits = array('Bytes', 'KB', 'MB', 'GB');

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

// copyright

$langCopyright = "Copyright";

// GENERIC 

$langBack="Back";
$langBackHome="Back to  home";
$langPropositions="Suggest better";
$langMaj="Update";
$langModify="Modify";
$langDelete="Delete";
$langTitle="Title";
$langHelp="Help";
$langOk="Ok";
$langAddIntro="Add introduction text";
$langBackList="Return to the list";
$langUser = "User:";
$langLogout = "Logout";

$langUserBriefcase = "User portfolio";
$langPersonalisedBriefcase = "Personalised portfolio";
$langEclass = "e-Class learning management system";
$langCopyrightFooter="Copyright notice";

$langGreek="Greek";
$langEnglish="English";

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
?>
