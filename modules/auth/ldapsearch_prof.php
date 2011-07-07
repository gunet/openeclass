<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


/*===========================================================================
	ldapsearch.php
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================        
  @Description: This script/file tries to authenticate the user, using
  his user/pass pair and the authentication method defined by the admin
  
==============================================================================
*/

include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';
include('../../include/CAS/CAS.php');
require_once 'auth.inc.php';

// like in ldapsearch.php
if (isset($_POST['auth'])) {
	$auth = intval($_POST['auth']);
	$_SESSION['u_tmp'] = $auth;
}
if(!isset($_POST['auth'])) {
	$auth = 0;
	$auth = $_SESSION['u_tmp'];
}

$msg = "$langReqRegProf (".(get_auth_info($auth)).")";
$nameTools = $msg;
$navigation[] = array ('url' => 'registration.php', 'name'=> $langNewUser);
$navigation[] = array ('url' => "ldapnewuser.php?p=TRUE&amp;auth=$auth", 'name' => $langConfirmUser);

$lang = langname_to_code($language);

register_posted_variables(array('uname' => true, 'passwd' => true,
                                'is_submit' => true, 'submit' => true));

$lastpage = 'ldapnewuser.php?p=TRUE&amp;auth='.$auth.'&amp;uname='.urlencode($uname);
$errormessage = "<br/><p>$ldapback <a href='$lastpage'>$ldaplastpage</a></p>";
$is_valid = false;

if (!isset($_SESSION['was_validated']) or
    $_SESSION['was_validated']['auth'] != $auth or
    $_SESSION['was_validated']['uname'] != $_POST['uname']) {
        $init_auth = true;
        // If user wasn't authenticated in the previous step, try
        // an authentication step now:
        // First check for Shibboleth
        if (isset($_SESSION['shib_auth']) and $_SESSION['shib_auth'] == true) {
                $r = mysql_fetch_array(db_query("SELECT auth_settings FROM auth WHERE auth_id = 6"));
                $shibsettings = $r['auth_settings'];
                if ($shibsettings != 'shibboleth' and $shibsettings != '') {
                        $shibseparator = $shibsettings;
                }
                if (strpos($_SESSION['shib_nom'], $shibseparator)) {
                        $temp = explode($shibseparator, $_SESSION['shib_nom']);
                        $GLOBALS['auth_user_info']['firstname'] = $temp[0];
                        $GLOBALS['auth_user_info']['lastname'] = $temp[1];
                }
                $GLOBALS['auth_user_info']['email'] = $_SESSION['shib_email'];
                $is_valid = true;
        } elseif ($is_submit or ($auth == 7 and !$submit)) {
                unset($_SESSION['was_validated']);
                if ($auth !=7 and $auth != 6 and
                    ($uname === '' or $passwd === '')) {
                        $tool_content .= "<p class='caution'>$ldapempty $errormessage</p>";
                        draw($tool_content, 0);
                        exit();
                } else {
                        // try to authenticate user
                        $auth_method_settings = get_auth_settings($auth);
                        if ($auth == 6) {
                                redirect_to_home_page('secure/index_reg.php');
                        }
                        $is_valid = auth_user_login($auth, $uname, $passwd, $auth_method_settings);
                }	

                if ($auth == 7) {
                        if (phpCAS::checkAuthentication()) {
                                $uname = phpCAS::getUser();
                                $cas = get_auth_settings($auth);
                                // store CAS released attributes in $GLOBALS['auth_user_info']
                                get_cas_attrs(phpCAS::getAttributes(), $cas);
                                $is_valid = true;
                        }
                }
        }

        if ($is_valid) { // connection successful
                $_SESSION['was_validated'] = array('auth' => $auth, 'uname' => $uname);
                if (isset($GLOBALS['auth_user_info'])) {
                        $_SESSION['was_validated']['auth_user_info'] = $GLOBALS['auth_user_info'];
                }
                user_info_form();
        } else {
                $tool_content .= "<p class='caution'>$langConnNo<br/>$langAuthNoValidUser</p>" .
                                 "<p>&laquo; <a href='$lastpage'>$langBack</a></p>";
        }
} else {
        $is_valid = true;
        if (isset($_SESSION['was_validated']['auth_user_info'])) {
                $auth_user_info = $_SESSION['was_validated']['auth_user_info'];
        }
}


// -----------------------------------------
// registration
// -----------------------------------------
if ($is_valid and !isset($init_auth))  {
        $ext_info = !isset($auth_user_info);
        $ok = register_posted_variables(array('uname' => true,
                                              'email' => $ext_info,
                                              'prenom_form' => $ext_info,
                                              'nom_form' => $ext_info,
                                              'department' => true,
                                              'usercomment' => true,
                                              'userphone' => true), 'all');
	$depid = intval($department);
        if (isset($auth_user_info)) {
                $prenom_form = $auth_user_info['firstname'];
                $nom_form = $auth_user_info['lastname'];
                $email = $auth_user_info['email'];
        }
 
        if (!$ok) {
                $tool_content .= "<p class='caution'>$langFieldsMissing</p>";
                user_info_form();
		draw($tool_content,0);
		exit();
	}

	if($auth != 1) {
                $password = isset($auth_ids[$auth])? $auth_ids[$auth]: '';
	}

        db_query('INSERT INTO user_request SET
                         name = ' . autoquote($prenom_form). ',
                         surname = ' . autoquote($nom_form). ',
                         uname = ' . autoquote($uname). ",
			 password = '$password',
                         email = " . autoquote($email). ",
                         faculty_id = $depid,
                         phone = " . autoquote($userphone). ',
                         status = 1,
                         statut = 1,
                         date_open = NOW(),
                         comment = ' . autoquote($usercomment). ",
                         lang = '$lang',
                         ip_address = inet_aton('$_SERVER[REMOTE_ADDR]')",
                 $mysqlMainDb);

	// send email
        $MailMessage = $mailbody1 . $mailbody2 . "$prenom_form $nom_form\n\n" . $mailbody3
        . $mailbody4 . $mailbody5 . "$mailbody6\n\n" . "$langFaculty: " . find_faculty_by_id($depid) . "
	\n$langComments: $usercomment\n"
        . "$langProfUname : $uname\n$langProfEmail : $email\n" . "$contactphone : $userphone\n\n\n$logo\n\n";
	
	if (!send_mail('', $emailhelpdesk, $gunet, $emailhelpdesk, $mailsubject, $MailMessage, $charset)) {
		$tool_content .= "<p class='alert1'>$langMailErrorMessage &nbsp; <a href='mailto:$emailhelpdesk'>$emailhelpdesk</a></p>";
		draw($tool_content,0);
		exit();
	}

	$tool_content .= "<p class='success'>$langDearProf<br />$success<br />$infoprof</p><p>&laquo; <a href='$urlServer'>$langBack</a></p>";
}

draw($tool_content,0);

function set($name)
{
        if (isset($GLOBALS[$name]) and
            $GLOBALS[$name] !== '') {
                return " value='".q($GLOBALS[$name])."'";
        } else {
                return '';
        }
}

function user_info_form()
{
        global $tool_content, $langTheUser, $ldapfound, $langName, $langSurname, $langEmail,
               $langPhone, $langComments, $langFaculty, $langRegistration, $langLanguage,
               $langUserData, $langRequiredFields, $profreason, $auth_user_info, $auth,
               $usercomment, $depid, $init_auth;

        if (!isset($usercomment)) {
                $usercomment = '';
        }
        if (!isset($depid)) {
                $depid = 0;
        }

        $tool_content .= "
  <form action='$_SERVER[PHP_SELF]' method='post'>
    " . (isset($init_auth)? "<p class='success'>$langTheUser $ldapfound.</p>": '') . "
    <fieldset>
      <legend>$langUserData</legend>
        <table width='99%' class='tbl'>
          <tr>
            <th class='left'>$langName</th>
            <td>".(isset($auth_user_info)?
                   $auth_user_info['firstname']:
                   '<input type="text" name="prenom_form" size="38"'.set('prenom_form').'>')."
            </td>
          </tr>
          <tr>
             <th class='left'>$langSurname</th>
             <td>".(isset($auth_user_info)?
                    $auth_user_info['lastname']:
                    '<input type="text" name="nom_form" size="38"'.set('nom_form').'>')."
             </td>
          </tr>
          <tr>
             <th class='left'>$langEmail</th>
             <td>".(isset($auth_user_info)?
                    $auth_user_info['email']:
                    '<input type="text" name="email" size="38"'.set('email').'>&nbsp;&nbsp;(*)')."
             </td>
          </tr>
          <tr>
             <th class='left'>$langPhone</th>
             <td><input type='text' name='userphone' size='38'".set('userphone').">&nbsp;&nbsp;(*)</td>
          </tr>
          <tr>
             <th class='left'>$langComments</th>
             <td><textarea name='usercomment' cols='32' rows='4'>".q($usercomment)."</textarea>&nbsp;&nbsp;(*) $profreason</td>
          </tr>
          <tr>
             <th class='left'>$langFaculty:</th>
             <td>
               <select name='department'>";
        $deps = db_query("SELECT name, id FROM faculte ORDER BY id");
        while ($dep = mysql_fetch_array($deps)) {
                $selected = ($depid == $dep[1])? ' selected': '';
                $tool_content .= "\n<option value='$dep[1]'$selected>".q($dep[0])."</option>";
        }
        $tool_content .= "</select>
             </td>
           </tr>
           <tr>
             <th class='left'>$langLanguage</th>
             <td>" . lang_select_options('localize') . "</td>
           </tr>	
           <tr>
             <th class='left'>&nbsp;</th>
             <td><input type='submit' name='submit' value='$langRegistration' />";
        if (isset($_SESSION['shib_uname'])) {
                $tool_content .= "<input type='hidden' name='uname' value='".q($_SESSION['shib_uname'])."' />";
        } else {
                $tool_content .= "<input type='hidden' name='uname' value='".q($_SESSION['was_validated']['uname'])."' />";
        }
        $tool_content .= "<input type='hidden' name='auth' value='$auth' />
             </td>
           </tr>
           <tr>
             <th class='left'>&nbsp;</th>
             <td><div align='right'>$langRequiredFields</div></td>
           </tr>
         </table>
       </fieldset>
  </form>";
}
