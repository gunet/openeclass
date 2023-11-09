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
            
              <div class='col-12'><a class='btn w-100 login-form-submit d-flex justify-content-center align-items-center mt-4' href='$authUrl'>$langEnter</a></div>
            ", $authTitle);
    } elseif (in_array($l->auth_name, $hybridAuthMethods)) {
        $head_content .= "<link rel='stylesheet' type='text/css' href='{$urlServer}template/modern/css/bootstrap-social.css'>";
        $providerClass = $l->auth_name;
        $providerFont = $l->auth_name;
        if ($l->auth_name === 'live') {
            $providerClass = 'microsoft';
            $providerFont = 'windows';
        }
        $hybridProviderHtml = "<a class='btn btn-block btn-social btn-$providerClass btn-sm m-2' href='{$urlServer}index.php?provider=" .
            $l->auth_name . "'><span class='fa-brands fa-$providerFont'></span>" . ucfirst($l->auth_name) . "</a>";
        if (is_null($hybridLinkId)) {
            $authLink[] = array(false, $hybridProviderHtml, $langViaSocialNetwork);
            $hybridLinkId = count($authLink) - 1;
        } else {
            $authLink[$hybridLinkId][1] .= '<br>' . $hybridProviderHtml;
        }
    } elseif (!$loginFormEnabled) {
        $loginFormEnabled = true;
        $authLink[] = array(true, "
        <div class='col-12'>
            <form class='form-horizontal' role='form' action='$urlServer?login_page=1' method='post'>
              $next
              <div>
                  <label for='username_id' class='form-label'>$langUsername</label>
                  <input id='username_id' class='login-input w-100' placeholder='&#xf007' type='text' id='uname' name='uname' autocomplete='on' />
                  <label for='password_id' class='form-label mt-4'>$langPassword&nbsp(password)</label>
                  <input id='password_id' class='login-input w-100' placeholder='&#xf084' type='password' id='pass' name='pass' autocomplete='on' />
                  <input class='btn w-100 login-form-submit mt-4' type='submit' name='submit' value='$langEnter' />
              </div>
            </form>
            <div class='col-12 text-center mt-4'>
                <a class='text-decoration-underline' href='{$urlAppend}modules/auth/lostpass.php'>$lang_forgot_pass</a>
            </div>
        </div>", $authTitle);
    }
}

$columns = 12 / count($authLink);
$pageName = $langUserLogin;

$tool_content .= "<div class='col-12'>
                    <h1>$langUserLogin</h1>
                  </div>";

$tool_content .= "<div class='col-12 mt-5'>";
  $tool_content .= "<div class='row m-auto'>";
    $tool_content .= "<div class='col-12 px-0'>";
      $counter = 0;
      $active = '';
        foreach ($authLink as $authInfo) {

            if (Session::has('login_error') and $authInfo[0]) {
                $tool_content .= "<div class='col-12'>                                 
                                    <input id='showWarningModal2' type='hidden' value='1'>
                                    <div class='modal fade bgEclass' id='WarningModal2' aria-hidden='true' tabindex='-1'>
                                        <div class='modal-dialog modal-dialog-centered'>
                                            <div class='modal-content border-0 p-0'>
                                                <div class='modal-header bgOrange d-flex justify-content-between align-items-center'>
                                                    <h5 class='modal-title text-white'>$langError</h5>
                                                    <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close'></button>
                                                </div>
                                                <div class='modal-body bg-default'>
                                                  ".Session::get('login_error')."
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                  </div>";
            }

            if($counter == 0){
              $active = 'active';
            }else{
              $active = '';
            }

            if($counter == 0){
              $tool_content .= "<ul class='nav nav-tabs ms-auto me-auto'>";
            }

            if($counter >= 0){
              $tool_content .= "
                <li class='nav-item' role='presentation'>
                    <button class='nav-link $active' id='reg-student$counter' data-bs-toggle='tab' data-bs-target='#regStudent$counter' type='button' role='tab' aria-controls='regStudent$counter' aria-selected='true' aria-current='page'>
                        " . q($authInfo[2]) . "
                    </button>
                </li>
              ";
            }

            if($counter == count($authLink) - 1){
              $tool_content .= "</ul>";
            }

            $counter++;
        }

        $counter = 0;
        $active = '';
        foreach ($authLink as $authInfo) {

          if($counter == 0) {
            $active = 'active show';
          } else {
            $active = '';
          }


          if($counter == 0) {
            $tool_content .= "<div class='col-lg-6 tab-content cardLogin p-4 ms-auto me-auto mt-5'>";
          }

          if($counter >= 0) {
            $tool_content .= "
                                <div class='tab-pane fade $active' id='regStudent$counter' role='tabpanel' aria-labelledby='reg-student$counter'>
                                  <h2 class='mb-4'>" . q($authInfo[2]) . "</h2>
                                  " . $authInfo[1] . "
                                </div>
                              ";
          }

          if($counter == count($authLink) - 1){
              $tool_content .= "</div>";
          }
          $counter++;
        }

       $tool_content .= "
                      </div>
                    </div>
                  </div>";

$head_content .= "
<script type='text/javascript'>
  $(document).ready(function() {
    if($('#showWarningModal2').val() == 1){
      var myModal = new bootstrap.Modal(document.getElementById('WarningModal2'));
      myModal.show();
    }
  });
</script>
";

draw($tool_content, 0, null, $head_content);
