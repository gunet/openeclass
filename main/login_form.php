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

$tool_content .= "<div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='$urlSecure' method='post'>
  $next
  <div class='form-group'>
    <label class='col-sm-offset-2 col-sm-10'>$langUserLogin</label>
  </div> 
    <div class='form-group'>       
        <label class='col-sm-2 control-label'>$langUsername</label>
        <div class='col-sm-10'>
            <input class='Login' name='uname' size='20' placeholder='$langUsername'>
        </div>
    </div>
    <div class='form-group'>
        <label class='col-sm-2 control-label'>$langPass</label>
        <div class='col-sm-10'>
            <input name='pass' type='password' size='20' placeholder='$langPass'>
        </div>
    </div>
    <p class='pull-right'>
    <input class='btn btn-primary' name='submit' type='submit' size='20' value='$langEnter' />
    $warning</p>
    <p class='smaller'><a href='{$urlAppend}modules/auth/lostpass.php'>$lang_forgot_pass</a></p><br>";
    if (!empty($shibboleth_link) or !empty($cas_link)) {
     $tool_content .= "<div class='link-pull-right'>                
             <label>$langAlternateLogin</label>
             <label>$shibboleth_link</label>
             <label>$cas_link</label>
          </div";
    }
$tool_content .= "</form></div>";

draw($tool_content, 0);
