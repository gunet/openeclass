<?php
define ("INDEX_START", 1);
$path2add = 0;
include 'include/baseTheme.php';
include "modules/auth/auth.inc.php";
$tool_content = "";
$warning = '';
$login_user = FALSE;

if (isset($_POST['uname'])) {
		$uname = escapeSimple(preg_replace('/ +/', ' ', trim($_POST['uname'])));
	} else {
		$uname = '';
	}
	$pass = isset($_POST['pass'])?$_POST['pass']:'';
	$submit = isset($_POST['submit'])?$_POST['submit']:'';
	$auth = get_auth_active_methods();
	$is_eclass_unique = is_eclass_unique();

if(!empty($submit)) {
	unset($uid);
	$sqlLogin= "SELECT user_id, nom, username, password, prenom, statut, email, perso, lang
		FROM user WHERE username='".$uname."'";
	$result = mysql_query($sqlLogin);
	$check_passwords = array('pop3', 'imap', 'ldap', 'db', 'cas');
	$warning = "";
	$auth_allow = 0;
	$exists = 0;
	if (!isset($_COOKIE) or count($_COOKIE) == 0) {
		// Disallow login when cookies are disabled
		$auth_allow = 5;
	} elseif (empty($pass)) {
		// Disallow login with empty password
		$auth_allow = 4;
	} else {
		while ($myrow = mysql_fetch_array($result)) {
			$exists = 1;
			if(!empty($auth)) {
				if(!in_array($myrow["password"],$check_passwords)) {
					// eclass login
					include "include/login.php"; 
				} else {
					// alternate methods login
					include "include/alt_login.php";
				}
			} else {
				$warning .= "<br>$langInvalidAuth<br>";
			}
		}
	}
	if(empty($exists) and !$auth_allow) {
		$auth_allow = 4;
	}
	if (!isset($uid)) {
		switch($auth_allow) {
			case 1 : $warning .= ""; 
				break;
			case 2 : $warning .= "<p class='alert1'>".$langInvalidId ."</p>"; 
				break;
			case 3 : $warning .= "<p class='alert1'>".$langAccountInactive1." <a href='modules/auth/contactadmin.php?userid=".$user."'>".$langAccountInactive2."</a></p>"; 
				break;
			case 4 : $warning .= "<p class='alert1'>". $langInvalidId . "</p>"; 
				break;
			case 5 : $warning .= "<p class='alert1'>". $langNoCookies . "</p>"; 
				break;
			default:
				break;
		}
	} else {
		$warning = '';
		$log = 'yes';
		$login_user = TRUE;
		$_SESSION['nom'] = $nom;
		$_SESSION['prenom'] = $prenom;
		$_SESSION['email'] = $email;
		$_SESSION['statut'] = $statut;
		$_SESSION['is_admin'] = $is_admin;
		$_SESSION['uid'] = $uid;
		db_query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
		VALUES ('$uid', '$_SERVER[REMOTE_ADDR]', NOW(), 'LOGIN')");
		//if user has activated the personalised interface
		//register a control session for it
		if (isset($_SESSION['perso_is_active']) and (isset($userPerso))) {
			$_SESSION['user_perso_active'] = $userPerso;
		}
		if ($login_user == TRUE) {
			redirect_to_home_page();
		}
	}
}  // end of user authentication

$shibactive = mysql_fetch_array(db_query("SELECT auth_default FROM auth WHERE auth_name='shibboleth'"));
if ($shibactive['auth_default'] == 1) {
	$shibboleth_link = "<a href='{$urlServer}secure/index.php'>$langShibboleth</a><br />";
} else {
	$shibboleth_link = "";
}

$casactive = mysql_fetch_array(db_query("SELECT auth_default FROM auth WHERE auth_name='cas'"));
if ($casactive['auth_default'] == 1) {
	$cas_link = "<a href='{$urlServer}secure/cas.php'>$langViaCAS</a><br />";
} else {
	$cas_link = "";
}

$tool_content .= "
  <table class='tbl' width='300' align='center'>
  <tr>
  <th colspan='2' class='LoginHead' align='center'><b>$langUserLogin </b></th>
  </tr>
  <tr>
  <td class='LoginData'><img src='{$urlServer}template/classic/img/login.png'></td>
    <td class='LoginData'>
      <form action='$_SERVER[PHP_SELF]' method='post'>
        $langUsername <br />
        <input class='Login' name='uname' size='20' /><br />
        $langPass <br />
        <input class='Login' name='pass' type='password' size='20' /><br /><br />
        <input class='Login' name='submit' type='submit' size='20' value='$langEnter' />
	$warning<br />$shibboleth_link
	<br />$cas_link 
        <a href='${urlServer}modules/auth/lostpass.php'>$lang_forgot_pass</a>
      </form>
    </td>
  </tr>
  </table>";

draw($tool_content, 0);
