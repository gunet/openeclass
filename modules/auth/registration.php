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
$tool_content .= "<p class='title1'>$langUserAccount ";

$auth = get_auth_active_methods();

// check for close user registration
if (isset($close_user_registration) and $close_user_registration) {
        $newuser = "formuser.php";
        $tool_content .= $langUserAccountInfo1;
} else {
        $newuser = "newuser.php";
        $tool_content .= $langUserAccountInfo2;
}

$tool_content .= "</p>
  <p><img style='border:0px;' src='${urlServer}/template/classic/img/arrow.png' title='bullet' alt='bullet'>&nbsp;&nbsp;<a href=\"$newuser\">$langNewAccount</a></p>";

if(!empty($auth)) {
        if (count($auth) > 1) {
                $tool_content .= "\n  <p class='sub_title1'>$langUserAccountInfo3&nbsp;: </p>";
        }
        foreach($auth as $k => $v) {
                if ($v == 1) {	// bypass the eclass auth method, as it has already been displayed
                        continue;
                } else {
                        $tool_content .= "<p><img src='../../template/classic/img/arrow.png' title='bullet' alt='bullet'>&nbsp;&nbsp;$langNewAccount&nbsp;";
                        if ($v == 6)  { // shibboleth method
                                $tool_content .= "(<a href='{$urlServer}secure/index.php'>".get_auth_info($v)."</a>)";
                        } else {
                                $tool_content .= "(<a href='ldapnewuser.php?auth=".$v."'>".get_auth_info($v)."</a>)";
                        }
                        $tool_content .= "</p>";
                }
                
        }
}

$tool_content .= "\n
 
  <p class='title1'>".$langProfAccount." ".$langUserAccountInfo1."</p>";

if(!empty($auth)) {
        $tool_content .= "<p><img style='border:0px;' src='${urlServer}/template/classic/img/arrow.png' title='bullet'  alt='bullet'>&nbsp;&nbsp;<a href=\"newprof.php\">$langNewAccount</a></p>";
        if (count($auth) > 1) {
                $tool_content .= "  <p class='sub_title1'>$langUserAccountInfo3&nbsp;:</p>";
        }
        foreach($auth as $k=>$v) {
                if ($v == 1) {	// bypass the eclass auth method, as it has already been displayed
                        continue;
                } else {
                        $tool_content .= "<p><img src='../../template/classic/img/arrow.png  alt='bullet''>&nbsp;&nbsp;$langNewAccount&nbsp;";
                        if ($v == 6)  { // shibboleth method
                                $tool_content .= "(<a href='{$urlServer}secure/index.php'>".get_auth_info($v)."</a>)";
                        } else {
                                $tool_content .= "(<a href='ldapnewuser.php?p=TRUE&amp;auth=".$v."'>".get_auth_info($v)."</a>)";
                        }
                        $tool_content .= "</p>";
                }
        }
} else {
        $tool_content .= "<p>$langCannotUseAuthMethods </p>";
}

draw($tool_content, 0);
