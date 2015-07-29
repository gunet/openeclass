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
    $authTitle = empty($l->auth_title)? "$langLogInWith {$l->auth_name}": getSerializedMessage($l->auth_title);
    if ($extAuth) {
        $authUrl = $urlServer . 'secure/' . ($l->auth_name == 'cas'? 'cas.php': '');        
        $authLink[] = array(false, "
            <div class='col-sm-8 col-sm-offset-2' style='padding-top:40px;'>
                <a class='btn btn-primary btn-block' href='$authUrl' style='line-height:40px;'>$langEnter</a>
            </div>", $authTitle);
    } elseif (!$loginFormEnabled) {
        $loginFormEnabled = true;
        $content = "
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
                </div>";
        
        //check if there are any available alternative providers for authentication and show the corresponding links on the homepage
        require_once 'modules/auth/methods/hybridauth/config.php';
        require_once 'modules/auth/methods/hybridauth/Hybrid/Auth.php';
        $config = get_hybridauth_config();

        $hybridauth = new Hybrid_Auth( $config );
        $allProviders = $hybridauth->getProviders();
        $tool_content_providers = "";

        $extAuthMethods2 = array('facebook', 'twitter', 'google', 'live', 'yahoo', 'linkedin');
        $q2 = Database::get()->queryArray("SELECT auth_id, auth_name, auth_default, auth_title, auth_enabled
                FROM auth WHERE auth_default <> 0
                ORDER BY auth_default DESC, auth_id");
        $hybrid_auth_providers_html = '';
        foreach ($q2 as $l2) {
            if($l2->auth_id > 7 && $l2->auth_id < 14) { 
                    $tool_content_providers .= "<a class='' href='{$urlServer}index.php?provider=" .
                    $l2->auth_name . "'><img src='$themeimg/$l2->auth_name.png' alt='Sign-in with $l2->auth_name' title='Sign-in with $l2->auth_name' />" . ucfirst($l2->auth_name) . "</a><br />";
            }
        }

        if($tool_content_providers) $content .= "<div class='form-group'><div class='col-sm-8'>$langProviderConnectWithAlternativeProviders<br />" . $tool_content_providers . "</div></div>";
        
        $content .= "<div class='form-group'>
                    <div class='col-xs-3'>
                        <button class='btn btn-primary margin-bottom-fat' type='submit' name='submit' value='$langEnter'>$langEnter</button>
                    </div>
                    <div class='col-xs-9 text-right'>
                        <a href='{$urlAppend}modules/auth/lostpass.php'>$lang_forgot_pass</a>
                    </div>
                </div>
            </form>";   
        $authLink[] = array(true, $content, $authTitle);
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
foreach ($authLink as $authInfo) {    
    $tool_content .= "
      <div class='col-sm-$columns'>
        <div class='panel panel-default '>
          <div class='panel-heading'><span>" . q($authInfo[2]) . "</span></div>
            <div class='panel-body login-page-option'>" .
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

draw($tool_content, 0);
