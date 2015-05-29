<?php
require_once '../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';

// if we are logged in there is no need to access this page
if (isset($_SESSION['uid'])) {
    redirect_to_home_page();
}

$warning = '';

$next = isset($_GET['next'])?
    ("<input type='hidden' name='next' value='" . q($_GET['next']) . "'>"):
    '';

$userValue = isset($_GET['user'])? (" value='" . q($_GET['user']) . "' readonly"): '';

$authLink = array();
$extAuthMethods = array('cas', 'shibboleth');
$loginFormEnabled = false;
$q = Database::get()->queryArray("SELECT auth_name, auth_default, auth_title
    FROM auth WHERE auth_default <> 0
    ORDER BY auth_default DESC, auth_id");
foreach ($q as $l) {
    $extAuth = in_array($l->auth_name, $extAuthMethods);
    if ($extAuth) {
        $authUrl = $urlServer . 'secure/' . ($l->auth_name == 'cas'? 'cas.php': '');
        $authTitle = empty($l->auth_title)? "<b>$langLogInWith</b><br>{$l->auth_name}": q($l->auth_title);
        $authLink[] = array(false, "
            <div class='col-sm-6'>
                <p>$authTitle</p>
            </div>
            <div class='col-sm-offset-1 col-sm-5'>
                <a class='btn btn-primary btn-block' href='$authUrl'>$langEnter</a>
            </div>");
    } elseif (!$loginFormEnabled) {
        $loginFormEnabled = true;
        $authLink[] = array(true, "
            <form class='form-horizontal' role='form' action='$urlServer?login_page=1' method='post'>
                $next
                <div class='form-group'>
                    <div class='col-xs-12'>
                        <input class='form-control' name='uname' placeholder='$langUsername'$userValue>
                    </div>
                </div>
                <div class='form-group'>
                    <div class='col-xs-12'>
                        <input class='form-control' name='pass' type='password' placeholder='$langPass'>
                    </div>
                </div>
                <div class='form-group'>
                    <div class='col-xs-3'>
                        <button class='btn btn-primary margin-bottom-fat' type='submit' name='submit' value='$langEnter'>$langEnter</button>
                    </div>
                    <div class='col-xs-9 text-right'>
                        <a href='{$urlAppend}modules/auth/lostpass.php'>$lang_forgot_pass</a>
                    </div>
                </div>
            </form>");
    }
}

$columns = 12 / count($authLink);

$pageName = $langUserLogin;
$tool_content .= action_bar(array(
    array('title' => $langBack,
          'url' => "$urlServer",
          'icon' => 'fa-reply',
          'level' => 'primary-label',
          'button-class' => 'btn-default')), false);
$tool_content .= "<div class='login-page'>
                    <div class='row'>";
$boxTitle = $langUserLogin;
foreach ($authLink as $authInfo) {
    $tool_content .= "
      <div class='col-sm-$columns'>
        <div class='panel panel-default '>
          <div class='panel-heading'><span>$boxTitle</span></div>
            <div class='panel-body login-page-option'>" .
              $authInfo[1];
    if (Session::has('login_error') and $authInfo[0]) {
        $tool_content .= "<div class='alert alert-warning' role='alert'>".Session::get('login_error')."</div>";
    }
    $tool_content .= "
                                </div>
                            </div>
                        </div>";
    $boxTitle = $langAlternateLogin;
}
$tool_content .= "</div></div>";

draw($tool_content, 0);
