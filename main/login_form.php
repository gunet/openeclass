<?php
require_once '../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';

// if we are logged in there is no need to access this page
if (isset($_SESSION['uid'])) {
    redirect_to_home_page('main/portfolio.php');
}

$warning = '';

$next = isset($_GET['next'])?
    ("<input type='hidden' name='next' value='" . q($_GET['next']) . "'>"):
    '';

$userValue = isset($_GET['user'])? (" value='" . q($_GET['user']) . "' readonly"): '';

$authLink = array();
$loginFormEnabled = false;
$hybridLinkId = null;
$q = Database::get()->queryArray("SELECT auth_name, auth_default, auth_title
    FROM auth WHERE auth_default > 0
    ORDER BY auth_default DESC, auth_id");
foreach ($q as $l) {
    $authTitle = empty($l->auth_title)? "$langLogInWith {$l->auth_name}": getSerializedMessage($l->auth_title);
    if (in_array($l->auth_name, $extAuthMethods)) {
        $authUrl = $urlServer . ($l->auth_name == 'cas'? 'modules/auth/cas.php': 'secure/');
        if (isset($_GET['next'])) {
            $authUrl .= '?next=' . urlencode($_GET['next']);
        }
        $authLink[] = array(false, "
            <div class='col-sm-8 col-sm-offset-2' style='padding-top:40px;'>
                <a class='btn btn-primary btn-block' href='$authUrl' style='line-height:40px;'>$langEnter</a>
            </div>", $authTitle);
    } elseif (in_array($l->auth_name, $hybridAuthMethods)) {
        $head_content .= "<link rel='stylesheet' type='text/css' href='{$urlServer}template/modern/css/bootstrap-social.css'>";
        $providerClass = $l->auth_name;
        $providerFont = $l->auth_name;
        if ($l->auth_name === 'live') {
            $providerClass = 'microsoft';
            $providerFont = 'windows';
        }
        $hybridProviderHtml = "<a class='btn btn-block btn-social btn-$providerClass' href='{$urlServer}index.php?provider=" .
            $l->auth_name . "'><span class='fa fa-$providerFont'></span>" . ucfirst($l->auth_name) . "</a>";
        if (is_null($hybridLinkId)) {
            $authLink[] = array(false, $hybridProviderHtml, $langViaSocialNetwork);
            $hybridLinkId = count($authLink) - 1;
        } else {
            $authLink[$hybridLinkId][1] .= '<br>' . $hybridProviderHtml;
        }
    } elseif (!$loginFormEnabled) {
        $loginFormEnabled = true;
        $authLink[] = array(true, "
          <form class='form-horizontal' role='form' action='$urlServer?login_page=1' method='post'>
            $next
            <div class='row'>
              <div class='col-12'>
                <div class='input-group mt-3'>
                    <span class='input-group-text' id='usernameIcon'><span class='fa fa-user text-dark'></span></span>
                    <input class='form-control' name='uname' aria-describedby='usernameIcon' placeholder='$langUsername'$userValue>
                </div>
              </div>
              <div class='col-12'>
                <div class='input-group mt-3'>
                    <span class='input-group-text' id='passwordIcon'><span class='fa fa-lock text-danger'></span></span>
                    <input class='form-control' name='pass' type='password' aria-describedby='passwordIcon' placeholder='$langPass'>
                </div>
              </div>
            </div>
            <div class='form-group mt-3'>
              <div class='row'>
                <div class='col-md-3 col-12 text-md-start text-center'>
                  <button class='login-main-form btn btn-primary margin-bottom-fat' type='submit' name='submit' value='$langEnter'>$langEnter</button>
                </div>
                <div class='col-md-9 col-12 text-md-end text-center mt-md-0 mt-3'>
                  <a class='btn btn-transparent text-primary fw-bold fs-6' href='{$urlAppend}modules/auth/lostpass.php'>$lang_forgot_pass</a>
                </div>
              </div>
           </div>
            </form>", $authTitle);
    }
}

$columns = 12 / count($authLink);
$pageName = $langUserLogin;
$tool_content .= action_bar(array(
    array('title' => $langBack,
          'url' => "$urlServer",
          'icon' => 'fa-reply',
          'level' => 'primary-label',
          'button-class' => 'btn-secondary')), false);
$tool_content .= "<div class='login-page'>";
foreach ($authLink as $authInfo) {
    $tool_content .= "
      <div class='col-sm-$columns'>
        <div class='panel panel-admin rounded-0'>
          <div class='panel-heading'><div class='panel-title p-0 text-white text-center'>" . q($authInfo[2]) . "</div></div>
            <div class='panel-body login-page-option rounded-0'>" .
              $authInfo[1];
    if (Session::has('login_error') and $authInfo[0]) {
        $tool_content .= "<div class='alert alert-warning' role='alert'>".Session::get('login_error')."</div>";
    }
    $tool_content .= "
                                </div>
                            </div>
                        </div>";

}
$tool_content .= "</div>";

draw($tool_content, 0, null, $head_content);
