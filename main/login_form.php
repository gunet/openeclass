<?php
require_once '../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';

// if we are logged in there is no need to access this page
if (isset($_SESSION['uid'])) {
    redirect_to_home_page('main/portfolio.php');
}

$next = isset($_GET['next'])?
    ("<input type='hidden' name='next' value='" . q($_GET['next']) . "'>"):
    '';

$userValue = isset($_GET['user'])? (" value='" . q($_GET['user']) . "' readonly"): '';

$authLink = [];
$hybridLinkId = null;
$q = Database::get()->queryArray("SELECT auth_name, auth_default, auth_title, auth_instructions
                                    FROM auth WHERE auth_default > 0
                                    ORDER BY auth_default DESC, auth_id");
foreach ($q as $l) {
    $authTitle = empty($l->auth_title)? "$langLogInWith {$l->auth_name}": getSerializedMessage($l->auth_title);
    $authInstructions = empty($l->auth_instructions) ? "" : getSerializedMessage($l->auth_instructions);

    if ($l->auth_name == 'eclass') { // standard auth method
        $authLink[] = array(true, "
                                <div class='col-12'>
                                    <form class='form-horizontal' role='form' action='$urlServer?login_page=1' method='post'>
                                      $next
                                      <div>
                                        <div class='form-group text-start'>
                                          <label for='username_id' class='form-label'>$langUsername</label>
                                          <input id='username_id' class='login-input w-100' placeholder='&#xf007' type='text' id='uname' name='uname' autocomplete='on' />
                                        </div>
                                        <div class='form-group text-start mt-3'>
                                          <label for='password_id' class='form-label mt-4'>$langPassword&nbsp;(password)</label>
                                          <div class='input-group flex-nowrap'>
                                            <input id='password_id' class='login-input border-end-0 w-100 mt-0' placeholder='&#xf084' type='password' name='pass' autocomplete='on' aria-label='reveal Password'>
                                            <span id='revealPass' class='input-group-text login-input-password-reveal border-start-0 bg-input-default input-border-color'>
                                                <i class='fa-solid fa-eye fa-md'></i>
                                            </span>
                                          </div>
                                        </div>
                                        <input class='btn w-100 login-form-submit mt-4' type='submit' name='submit' value='$langEnter'>
                                      </div>
                                    </form>
                                    <div class='col-12 text-md-start text-center mt-4'>
                                        <a class='text-decoration-underline' href='{$urlAppend}modules/auth/lostpass.php'>$lang_forgot_pass</a>
                                    </div>
                                </div>",
            $authTitle,
            $authInstructions);
    } else if (in_array($l->auth_name, $extAuthMethods)) { // defined auth methods
        $authUrl = $urlServer . ($l->auth_name == 'cas'? 'modules/auth/cas.php': 'secure/');
        if (isset($_GET['next'])) {
            $authUrl .= '?next=' . urlencode($_GET['next']);
        }
        $authLink[] = array(false, "
                                  <div class='col-12 d-flex justify-content-center align-items-center'>
                                       <a class='btn submitAdminBtnDefault sso-btn d-inline-flex' href='$authUrl'>
                                            ".(!empty($authTitle) ? $authTitle : $langEnter)."
                                       </a>
                                  </div>",
                            $authTitle,
                            $authInstructions);
    } elseif (in_array($l->auth_name, $hybridAuthMethods)) { // hybrid auth methods
        $head_content .= "<link rel='stylesheet' type='text/css' href='{$urlServer}template/modern/css/bootstrap-social.css'>";
        $providerClass = $l->auth_name;
        $providerFont = $l->auth_name;
        if ($l->auth_name === 'live') {
            $providerClass = 'microsoft';
            $providerFont = 'windows';
        }
        $hybridProviderHtml = "<a class='btn submitAdminBtnDefault btn-$providerClass social-btn m-2 d-inline-flex gap-1' href='{$urlServer}index.php?provider=" .
            $l->auth_name . "'><span class='fa-brands fa-$providerFont'></span>" . ucfirst($l->auth_name) . "</a>";
        if (is_null($hybridLinkId)) {
            $authLink[] = array(false, $hybridProviderHtml, $langViaSocialNetwork);
            $hybridLinkId = count($authLink) - 1;
        } else {
            $authLink[$hybridLinkId][1] .= '<br>' . $hybridProviderHtml;
        }
    }
}

$Position = '';
$PositionForm = 'd-lg-block';
$themeId = get_config('theme_options_id');
$login_img = $urlAppend . 'template/modern/img/loginIMG.png';
if($themeId > 0) {
  $theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $themeId);
  $theme_options_styles = unserialize($theme_options->styles);
  $urlThemeData = $urlAppend . 'courses/theme_data/' . $themeId;
  if(isset($theme_options_styles['loginImgL'])){
    $login_img = "$urlThemeData/$theme_options_styles[loginImgL]";
  }

  if (isset($theme_options_styles['FormLoginPlacement']) && $theme_options_styles['FormLoginPlacement']=='center-position') {
    $Position = 'ms-auto me-auto';
    $PositionForm = 'd-lg-none';
  }
}

$Page = '';
$class_login_img = '';
$auth_enabled_method = 0;
$active_method = Database::get()->queryArray("SELECT * FROM auth WHERE auth_default IN (1, 2)");
if (count($active_method) > 0) {
    $auth_enabled_method = 1;
    if(count($authLink) > 1){
      $class_login_img = 'jumbotron-image-auth-default';
    }
}

$data['authLink'] = $authLink;
$data['auth_enabled_method'] = $auth_enabled_method;
$data['Position'] = $Position;
$data['PositionForm'] = $PositionForm;
$data['login_img'] = $login_img;
$data['class_login_img'] = $class_login_img;
$data['menuTypeID'] = 0;

view('main.login_form', $data);
