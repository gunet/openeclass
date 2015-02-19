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

$pageName = $langUserLogin;
$tool_content .= action_bar(array(
                                array('title' => $langBack,
                                      'url' => "$urlServer",
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);
$tool_content .= "<div class='form-wrapper login-form-page'>
        <form class='form-horizontal' role='form' action='$urlSecure' method='post'>
  $next
    <div class='form-group'>       
        <div class='col-sm-8'>
            <input class='form-control' name='uname' placeholder='$langUsername'>
        </div>
    </div>
    <div class='form-group'>
        <div class='col-sm-8'>
            <input class='form-control' name='pass' type='password' placeholder='$langPass'>
        </div>
    </div>
    <div class='form-group'>
    <div class='col-sm-8'>
    <button class='btn btn-primary pull-left' type='submit' name='submit' value='$langEnter'>$langEnter</button>
        <div class='pull-right'><a href='{$urlAppend}modules/auth/lostpass.php'>$lang_forgot_pass</a></div>
        </div>
        </div>";
$tool_content .= "</form>";
    if (!isset($warning)) {
    $tool_content .= "<div class='alert alert-warning' role='alert'>$warning</div>";
        }
        
        $tool_content .= "<div class='row'><div class='col-sm-8'><hr style='margin: 5px 0 10px;'></div></div>";
    if (!empty($shibboleth_link) or !empty($cas_link)) {
     $tool_content .= "<div class='row'><div class='col-sm-8'>                
             <label>$langAlternateLogin:</label>
             <label>$shibboleth_link</label>
             <label>$cas_link</label>
          </div></div>";
    }
        $tool_content .= "</div>";

draw($tool_content, 0);
