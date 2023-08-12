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
            
              <div class='col-xl-8 col-lg-8 col-md-8 col-12'><a class='btn w-100 login-form-submit d-flex justify-content-center align-items-center mt-4' href='$authUrl'>$langEnter</a></div>
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

// $tool_content .= action_bar(array(
//     array('title' => $langBack,
//           'url' => "$urlServer",
//           'icon' => 'fa-reply',
//           'level' => 'primary',
//           'button-class' => 'btn-primary')), false);


$tool_content .= "<div class='col-12'>
                    <h1>$langUserLogin</h1>
                  </div>";

$tool_content .= "<div class='col-12 mt-5'>";
  $tool_content .= "<div class='row row-cols-1 row-cols-lg-2 g-lg-5 g-4'>";
    $tool_content .= "<div class='col'>";
                          $counter = 0;
                          $active = '';
      $tool_content .= "<div id='carouselLogin' class='carousel slide' data-bs-ride='carousel' data-bs-interval='false'>
                          <div class='carousel-inner'>";
                            foreach ($authLink as $authInfo) {
                                if($counter == 0){
                                  $active = 'active';
                                }else{
                                  $active = '';
                                }
                                $tool_content .= "
                              
                                  <div class='carousel-item $active'>
                                    <div class='card panelCard px-lg-4 py-lg-3 px-3 py-2 h-100'>
                                      <div class='card-header border-0 bg-white text-center'>
                                          <h2>
                                            " . q($authInfo[2]) . "
                                          </h2>
                                      </div>
                                      <div class='card-body d-flex justify-content-center align-items-start flex-wrap'>" .
                                          $authInfo[1];
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
                                                                              <div class='modal-body bg-white'>
                                                                                ".Session::get('login_error')."
                                                                              </div>
                                                                          </div>
                                                                      </div>
                                                                  </div>
                                                                </div>";
                                          }
                                $tool_content .= "
                                      </div>
                                    </div>
                                  </div>";

                              $counter++;
                            }
    $tool_content .= "    </div>";
                          if(count($authLink)>1){
          $tool_content .=" <button class='carousel-control-prev' type='button' data-bs-target='#carouselLogin' data-bs-slide='prev'>
                              <span class='carousel-control-prev-icon' aria-hidden='true'></span>
                              <span class='visually-hidden'>Previous</span>
                            </button>
                            <button class='carousel-control-next' type='button' data-bs-target='#carouselLogin' data-bs-slide='next'>
                              <span class='carousel-control-next-icon' aria-hidden='true'></span>
                              <span class='visually-hidden'>Next</span>
                            </button>";
                          }
       $tool_content .= "</div>
                      </div>

                      <div class='col'>
                        <img class='form-image' src='{$urlAppend}template/modern/img/jumbotron-eclass4.png'>
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
