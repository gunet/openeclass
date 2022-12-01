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
            <div class='col-12 d-flex justify-content-center align-items-center'>
                <a class='btn login-form-submit rounded-pill btn-block w-50' href='$authUrl'>$langEnter</a>
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
              <div class='col-12 login-form-spacing m-auto d-block'>
                <input class='login-input w-75 rounded-pill' name='uname' aria-describedby='usernameIcon' placeholder='$langUsername &#xf007;'$userValue>
              </div>
              <div class='col-12 login-form-spacing m-auto d-block'>
                  <input class='login-input  w-75 rounded-pill mt-2' name='pass' type='password' aria-describedby='passwordIcon' placeholder='$langPass &#xf023;'>
              </div>
            </div>
            <div class='form-group mt-3'>
              <div class='row'>
                <div class='col-12 text-center'>
                  <button class='btn login-form-submit w-75 rounded-pill mt-2' type='submit' name='submit' value='$langEnter'>$langEnter</button>
                </div>
                <div class='col-12 text-center mt-4'>
                  <a class='orangeText btnlostpass' href='{$urlAppend}modules/auth/lostpass.php'>$lang_forgot_pass</a>
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
$tool_content .= "<div class='login-page'> <div class='row'>";
if($columns == 12){

  $columns = $columns - 6 ;
  $tool_content .= "<div class='col-lg-6 col-12 d-none d-md-block'>
                      <div class='col-12 h-100 left-form'></div>
                    </div>";
}
foreach ($authLink as $authInfo) {
    $tool_content .= "
   
      <div class='col-lg-$columns col-12'>
        <div class='panel panel-default rounded-0 mt-lg-0 mb-lg-0 mb-3' style='min-height:280px;'>
          <div class='panel-heading rounded-0'><div class='panel-title p-0 text-dark text-center'>
          <img src='/template/modern/img/user2.png' class='user-icon2 me-2'>" . q($authInfo[2]) . "</div></div>
            <div class='panel-body login-page-option rounded-0 bg-light d-flex justify-content-center align-items-center'>" .
              $authInfo[1];
    if (Session::has('login_error') and $authInfo[0]) {
        $tool_content .= "<div class='alert alert-warning' role='alert'>".Session::get('login_error')."</div>";
    }
    $tool_content .= "
                                </div>
                            </div>
                        </div>";

}
$tool_content .= "</div></div>";

draw($tool_content, 0, null, $head_content);
