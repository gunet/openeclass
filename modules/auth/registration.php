<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*                       Yannis Exidaridis <jexi@noc.uoa.gr>
*                       Alexandros Diamantidis <adia@noc.uoa.gr>
*                       Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address:     GUnet Asynchronous eLearning Group,
*                       Network Operations Center, University of Athens,
*                       Panepistimiopolis Ilissia, 15784, Athens, Greece
*                       eMail: info@openeclass.org
* =========================================================================*/


/*===========================================================================
  newuser_info.php
 * @version $Id$

 @authors list: Karatzidis Stratos <kstratos@uom.gr>
 Vagelis Pitsioygas <vagpits@uom.gr>
 ==============================================================================
 @Description: Display all the available auth methods for user registration

 Purpose: TDisplay all the available auth methods for user registration

 ==============================================================================
 */

include '../../include/baseTheme.php';
include 'auth.inc.php';

$nameTools = $langNewUser;
$tool_content = "";
$tool_content .= "<table width=\"99%\" style=\"border: 1px solid #edecdf;\">
	<thead><tr><td>
	<table class=\"FormData\" width=\"99%\" align=\"left\">
	<thead>
	<tr><th width=\"120\" rowspan=\"5\">&nbsp;</th>
	<td><strong><font style=\"color: #a33033;\">$langUserAccount ";

$auth = get_auth_active_methods();
$e = 1;

// check for close user registration
if (isset($close_user_registration) and $close_user_registration) {
        $newuser = "formuser.php";
        $tool_content .= $langUserAccountInfo1;
} else {
        $newuser = "newuser.php";
        $tool_content .= $langUserAccountInfo2;
}

$tool_content .= "</font></strong></td>
  </tr>
    <td><p><img style='border:0px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'>&nbsp;&nbsp;<a href=\"$newuser\">$langNewAccount</a></p>";

if(!empty($auth)) {
        if (count($auth) > 1) {
                $tool_content .= "\n      <br>\n      <p><span style='border-bottom: 1px dotted silver;'>$langUserAccountInfo3&nbsp;</span>: </p>";
        }

        foreach($auth as $k => $v) {
                if($v!=1) {
                        $tool_content .= "\n      <p><img src='../../images/arrow_blue.gif'>&nbsp;&nbsp;$langNewAccount&nbsp;(<a href=\"ldapnewuser.php?auth=".$v."\">".get_auth_info($v)."</a>)</p>";
                } else {
                        continue;
                }
        }
}

$tool_content .= "\n<br></td></tr><tr><td>&nbsp;</td></tr><tr>
	<td><strong><font style='color: #a33033;'>".$langProfAccount." ".$langUserAccountInfo1."</font></strong></td>
	</tr><tr><td>";

if(!empty($auth)) {
        $tool_content .= "<p><img style='border:0px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'>&nbsp;&nbsp;<a href=\"newprof.php\">$langNewAccount</a></p>";
        if (count($auth) > 1) {
                $tool_content .= "<br><p><span style=\"border-bottom: 1px dotted silver;\">$langUserAccountInfo3</span>&nbsp;:</p>";
        }
        foreach($auth as $k=>$v) {
                if ($v == 1) {	// bypass the eclass auth method, as it has already been displayed
                        continue;
                } else {
                        $auth_method_settings = get_auth_settings($v);
                        $tool_content .= "<p><img src='../../images/arrow_blue.gif'>&nbsp;&nbsp;
                        $langNewAccount
                                &nbsp;(<a href='ldapnewuser.php?p=TRUE&auth=".$v."'>".get_auth_info($v)."</a>)</p>";
                }
        }
} else {
        $tool_content .= "<p>$langCannotUseAuthMethods </p>";
}

$tool_content .= "<br></td></tr></thead></table></td></tr></thead></table>";
draw($tool_content, 0, 'auth');
?>
