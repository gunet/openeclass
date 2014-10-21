<?php
require_once '../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';

// if we are logged in there is no need to access this page
if (isset($_SESSION['uid'])) {
    redirect_to_home_page();
    exit();
}

$warning = '';
$login_user = FALSE;

$shibactive = Database::get()->querySingle("SELECT auth_default FROM auth WHERE auth_name='shibboleth'");
if ($shibactive->auth_default == 1) {
    $shibboleth_link = "<a href='{$urlServer}secure/index.php'>$langShibboleth</a><br />";
} else {
    $shibboleth_link = "";
}

$casactive = Database::get()->querySingle("SELECT auth_default FROM auth WHERE auth_name='cas'");
if ($casactive->auth_default == 1) {
    $cas_link = "<a href='{$urlServer}secure/cas.php'>$langViaCAS</a><br />";
} else {
    $cas_link = "";
}

$next = isset($_GET['next']) ?
        ("<input type='hidden' name='next' value='" . q($_GET['next']) . "'>") :
        '';

$tool_content .= "<form action='$urlSecure' method='post'>
  $next
  <table class='tbl' width='300' align='center'>
  <tr>
  <th colspan='2' class='LoginHead' align='center'><b>$langUserLogin </b></th>
  </tr>
  <tr>
  <td class='LoginData'><img src='$themeimg/login.png'></td>
    <td class='LoginData'>
        $langUsername <br />
        <input class='Login' name='uname' size='20' autocomplete='off' /><br />
        $langPass <br />
        <input class='btn btn-primary' name='pass' type='password' size='20' autocomplete='off' /><br /><br />
        <input class='btn btn-primary' name='submit' type='submit' size='20' value='$langEnter' />
	$warning</td></tr>
	   <tr><td>&nbsp;</td><td><p class='smaller'><a href='{$urlAppend}modules/auth/lostpass.php'>$lang_forgot_pass</a></p><br />
	   </td>
	 </tr>";
if (!empty($shibboleth_link) or !empty($cas_link)) {
    $tool_content .= "<tr><th colspan='2' class='LoginHead'><b>$langAlternateLogin </b></th></tr>";
}
$tool_content .= "<tr><td colspan='2' class='LoginData'>$shibboleth_link
                      $cas_link</td></tr></table></form>";

draw($tool_content, 0);
