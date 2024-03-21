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
$q = Database::get()->queryArray("SELECT auth_name, auth_default, auth_title, auth_instructions
    FROM auth WHERE auth_default > 0
    ORDER BY auth_default DESC, auth_id");
foreach ($q as $l) {
    $authTitle = empty($l->auth_title)? "$langLogInWith {$l->auth_name}": getSerializedMessage($l->auth_title);
    $authInstructions = empty($l->auth_instructions) ? "" : getSerializedMessage($l->auth_instructions);
    if (in_array($l->auth_name, $extAuthMethods)) {
        $authUrl = $urlServer . ($l->auth_name == 'cas'? 'modules/auth/cas.php': 'secure/');
        if (isset($_GET['next'])) {
            $authUrl .= '?next=' . urlencode($_GET['next']);
        }
        $authLink[] = array(false, "

              <div class='col-12 d-flex justify-content-center align-items-center'>
                <a class='btn submitAdminBtnDefault sso-btn d-inline-flex' href='$authUrl'>
                  ".(!empty($authTitle) ? $authTitle : $langEnter)."
                </a>
              </div>
            ", $authTitle, $authInstructions);
    } elseif (in_array($l->auth_name, $hybridAuthMethods)) {
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
    } elseif (!$loginFormEnabled) {
        $loginFormEnabled = true;
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
                    <input id='password_id' class='login-input border-end-0 w-100 mt-0' placeholder='&#xf084' type='password' name='pass' autocomplete='on'>
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
        </div>", $authTitle, $authInstructions);
    }
}

$columns = 12 / count($authLink);
$pageName = $langUserLogin;
$Position = '';
$PositionForm = 'd-lg-block';
$themeId = get_config('theme_options_id');
$login_img = $urlAppend . 'template/modern/img/loginIMG.png';
if($themeId > 0){
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
$auth_enabled_method = 0;
$active_method = Database::get()->queryArray("SELECT * FROM auth WHERE auth_default = ?d OR auth_default = ?d",1,2);
if(count($active_method) > 0){
    $auth_enabled_method = 1;
}
$class_login_img = '';
if($auth_enabled_method == 1 && count($authLink) > 1){ 
  $class_login_img = 'jumbotron-image-auth-default'; 
}
$tool_content .= "<h1>$langUserLogin</h1>";
$tool_content .= "
                  <div class='padding-default mt-4'>
                    <div class='row row-cols-1 row-cols-lg-2 g-4'>
                      <div class='col $Position'>";
                        if($auth_enabled_method == 1){
                          if(count($authLink) > 0){
          $tool_content .= "<div class='card form-homepage-login border-card h-100 px-lg-4 py-lg-3 p-3'>
                              <div class='card-body d-flex justify-content-center align-items-center'>";
                                  $i = 0;
                                  $j = 0;
                $tool_content .= "<div class='w-100 h-100'>
                                      <div class='col-12 container-pages d-flex align-items-center h-100'>";
                                         
                                          foreach($authLink as $authInfo){
                                              if($i==0){
                                                $Page = 'slide-page';
                                              }elseif($i == 1){
                                                $Page = 'next-page-1';
                                              }elseif($i == 2){
                                                $Page = 'next-page-2';
                                              }
                                              
                            $tool_content .= "<div class='col-12 page $Page h-100'>
                                                  <div class='row h-100'>
                                                      <div class='col-12 align-self-start'>
                                                          <div class='d-flex justify-content-between align-items-center flex-wrap gap-2'>
                                                              <h2 class='mb-3'>".(!empty($authInfo[2]) ? $authInfo[2] : $langLogin)."</h2>";
                                                              if(!empty($authInfo[3])){
                                               $tool_content .= " <a href='#' class='text-decoration-underline vsmall-text mb-3' data-bs-toggle='modal' data-bs-target='#authInstruction".$j."'>
                                                                      $langInstructions
                                                                  </a>
                                                                  <div class='modal fade' id='authInstruction".$j."' tabindex='-1' role='dialog' aria-labelledby='authInstructionLabel' aria-hidden='true'>
                                                                      <div class='modal-dialog'>
                                                                          <div class='modal-content'>
                                                                              <div class='modal-header'>
                                                                                  <div class='modal-title' id='authInstructionLabel'>$langInstructionsAuth</div>
                                                                                  <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'>
                                                                                      <span class='fa-solid fa-xmark fa-lg Accent-200-cl' aria-hidden='true'></span>
                                                                                  </button>
                                                                              </div>
                                                                              <div class='modal-body'>
                                                                                  <div class='col-12'>
                                                                                      <div class='alert alert-info'>
                                                                                          <i class='fa-solid fa-circle-info fa-lg'></i>
                                                                                          <span>".$authInfo[3]."</span>
                                                                                      </div>
                                                                                  </div>
                                                                              </div>
                                                                          </div>
                                                                      </div>
                                                                  </div>";
                                                              }
                                      $tool_content .= "</div>
                                                      </div>
                                                  
                                                      <div class='col-12 align-self-center'>
                                                          <div class='text-center'>".$authInfo[1]."</div>
                                                      </div>
                                                  
                                                
                                                      <div class='col-12 align-self-end'>";
                                                        if(count($authLink) > 1){
                                                            $tool_content .= " <div id='or' class='ms-auto me-auto mb-2'>$langOr</div>";  
                                                        }  
                                                        if(count($authLink) == 2){
                                            $tool_content .= "<div class='d-flex justify-content-center align-items-center gap-3 flex-wrap'>
                                                                <button class='btn submitAdminBtn " . ( ($i==0) ? 'firstNext' : 'prev-'.$i ) . " next'>";
                                                                    if($i==0){
                                                                        if(!empty($authLink[1][2])){
                                                                            $tool_content .= "<span class='TextBold'>".$authLink[1][2]."</span>";
                                                                        }else{
                                                                            
                                                                            $tool_content .= "<span class='TextBold'>".$langLogin."</span>";
                                                                        }
                                                                    }
                                                                    if($i==1){
                                                                        if(!empty($authLink[0][2])){
                                                                            $tool_content .= "<span class='TextBold'>".$authLink[0][2]."</span>";
                                                                        }else{
                                                                          $tool_content .= "<span class='TextBold'>".$langLogin."</span>";
                                                                        }
                                                                        
                                                                    }

                                                                    
                                                                    
                                            $tool_content .= " </button>
                                                            </div>";
                                                        }

                                                        if(count($authLink) >= 3){
                                        $tool_content .= "<div class='d-flex justify-content-md-between justify-content-center align-items-center gap-3 flex-wrap'>";
                                                                
                                                                    if($i==0){
                                                       $tool_content .= "<button class='btn submitAdminBtn firstNext next'>
                                                                            
                                                                            ".(!empty($authLink[1][2]) ? $authLink[1][2] : $langLogin)."
                                                                        </button>
                                                                        <button class='btn submitAdminBtn next-1 next'>
                                                                            
                                                                            ".(!empty($authLink[2][2]) ? $authLink[2][2] : $langLogin)."
                                                                        </button>";
                                                                        
                                                                    }

                                                                    if($i==1){
                                                       $tool_content .= "<button class='btn submitAdminBtn prev-1 next'>
                                                                            
                                                                            ".(!empty($authLink[0][2]) ? $authLink[0][2] : $langLogin)."
                                                                        </button>
                                                                        <button class='btn submitAdminBtn next-2 next'>
                                                                            
                                                                            ".(!empty($authLink[2][2]) ? $authLink[2][2] : $langLogin)."
                                                                        </button>";
                                                                        
                                                                    }

                                                                    if($i==2){
                                                      $tool_content .= "<button class='btn submitAdminBtn prev-2 next'>
                                                                            
                                                                            ".(!empty($authLink[1][2]) ? $authLink[1][2] : $langLogin)."
                                                                        </button>
                                                                        <button class='btn submitAdminBtn next-3 next'>
                                                                            
                                                                            ".(!empty($authLink[0][2]) ? $authLink[0][2] : $langLogin)."
                                                                        </button>";
                                                                        
                                                                    }


                                                                    if(count($authLink) > 3){
                                                      $tool_content .= "<div class='col-12'>
                                                                          <div id='oreven' class='ms-auto me-auto mb-2'>$langOrYet</div>
                                                                        </div>";  

                                                                          
                                                        $tool_content .= "
                                                                        <div class='col-12 d-flex justify-content-center align-items-center'>
                                                                          <button type='button' class='btn submitAdminBtn border-0 text-decoration-underline bg-transparent' data-bs-toggle='modal' data-bs-target='#LoginFormAnotherOption-$i'>
                                                                              ".(!empty($authLink[count($authLink)-1][2]) ? $authLink[count($authLink)-1][2] : $langLogin)."
                                                                          </button>";

                                                                          
                                                        $tool_content .= "<div class='modal fade' id='LoginFormAnotherOption-$i' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1' aria-labelledby='LoginFormAnotherOptionLabel-$i' aria-hidden='true'>
                                                                            <div class='modal-dialog'>
                                                                              <div class='modal-content'>
                                                                                <div class='modal-header'>
                                                                                  <h5 class='modal-title' id='LoginFormAnotherOptionLabel-$i'>".(!empty($authLink[count($authLink)-1][2]) ? $authLink[count($authLink)-1][2] : $langLogin)."</h5>
                                                                                  <button type='button' class='close border-0 bg-transparent' data-bs-dismiss='modal' aria-label='Close'>
                                                                                    <i class='fa-solid fa-xmark fa-lg Accent-200-cl'></i>
                                                                                  </button>
                                                                                </div>
                                                                                <div class='modal-body d-flex justify-content-center align-items-center'>
                                                                                  <div>
                                                                                    ".$authLink[count($authLink)-1][1]."
                                                                                  </div>
                                                                                </div>
                                                                              </div>
                                                                            </div>
                                                                          </div>
                                                                        </div>";
                                                                        
                                                                    } 
                                                                    
                                                                
                                          $tool_content .= "</div>";
                                                      }





                                                      
                                                      





                                    $tool_content .= "</div>
                                                    </div>
                                              </div>";
                                              $i++;
                                              $j++;


                                              if (Session::has('login_error') and $authInfo[0]) {
                              $tool_content .= "<div class='col-12'>
                                                  <input id='showWarningModal2' type='hidden' value='1'>
                                                  <div class='modal fade' id='WarningModal2' aria-hidden='true' tabindex='-1'>
                                                      <div class='modal-dialog modal-dialog-centered'>
                                                          <div class='modal-content border-0 p-0'>
                                                              <div class='modal-header d-flex justify-content-between align-items-center'>
                                                                  <h5 class='modal-title'>$langError</h5>
                                                                  <button aria-label='Close' type='button' class='close close-error border-0 bg-transparent' data-bs-dismiss='modal'>
                                                                    <i class='fa-solid fa-xmark fa-lg Accent-200-cl'></i>
                                                                  </button>
              
                                                              </div>
                                                              <div class='modal-body'>
                                                                ".Session::get('login_error')."
                                                              </div>
                                                          </div>
                                                      </div>
                                                  </div>
                                                </div>";
                                            }




                                          }
                  $tool_content .= "</div>
                                  </div>
                              </div>
                            </div>";
                          } 
                        }else{
          $tool_content .= "<div class='card cardLogin h-100 p-3'>
                              <div class='card-body py-1'>
                                <h2>$langUserLogin</h2>
                                <div class='col-12 mt-3'>
                                  <div class='alert alert-warning'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                    <span>$langAllAuthMethodsAreDisable</span>
                                  </div>
                                </div>
                              </div>
                            </div>";
                        }
    $tool_content .= "</div>

                      
                        <div class='col d-none $PositionForm'>
                            <div class='card h-100 border-0 p-0'>
                                <div class='card-body d-flex justify-content-center align-items-center p-0'>
                                    <img class='jumbotron-image-default $class_login_img' src='".$login_img."' alt='".$langLogin."'  />
                                </div>
                            </div>
                        </div>
                    </div>
                  </div>";










// $tool_content .= "<div class='col-12 mt-5'>";
//   $tool_content .= "<div class='row m-auto'>";
//     $tool_content .= "<div class='col-12 px-0'>";
//       $counter = 0;
//       $active = '';
//       print_a($authLink);
//         foreach ($authLink as $authInfo) {

//             if (Session::has('login_error') and $authInfo[0]) {
//                 $tool_content .= "<div class='col-12'>
//                                     <input id='showWarningModal2' type='hidden' value='1'>
//                                     <div class='modal fade' id='WarningModal2' aria-hidden='true' tabindex='-1'>
//                                         <div class='modal-dialog modal-dialog-centered'>
//                                             <div class='modal-content border-0 p-0'>
//                                                 <div class='modal-header d-flex justify-content-between align-items-center'>
//                                                     <h5 class='modal-title'>$langError</h5>
//                                                     <button aria-label='Close' type='button' class='close border-0 bg-transparent' data-bs-dismiss='modal'>
//                                                       <i class='fa-solid fa-xmark fa-lg Accent-200-cl'></i>
//                                                     </button>

//                                                 </div>
//                                                 <div class='modal-body'>
//                                                   ".Session::get('login_error')."
//                                                 </div>
//                                             </div>
//                                         </div>
//                                     </div>
//                                   </div>";
//             }

//             if($counter == 0){
//               $active = 'active';
//             }else{
//               $active = '';
//             }

//             if($counter == 0){
//               $tool_content .= "<ul class='nav nav-tabs ms-auto me-auto'>";
//             }

//             if($counter >= 0){
//               $tool_content .= "
//                 <li class='nav-item' role='presentation'>
//                     <button class='nav-link $active' id='reg-student$counter' data-bs-toggle='tab' data-bs-target='#regStudent$counter' type='button' role='tab' aria-controls='regStudent$counter' aria-selected='true' aria-current='page'>
//                         " . q($authInfo[2]) . "
//                     </button>
//                 </li>
//               ";
//             }

//             if($counter == count($authLink) - 1){
//               $tool_content .= "</ul>";
//             }

//             $counter++;
//         }

//         $counter = 0;
//         $active = '';

//         foreach ($authLink as $authInfo) {

//           if($counter == 0) {
//             $active = 'active show';
//           } else {
//             $active = '';
//           }

          

//           if($counter == 0) {
//             $tool_content .= "<div class='col-lg-5 col-md-6 col-12 ms-auto me-auto tab-content mt-5'>";
//           }

//           if($counter >= 0) {
//             $tool_content .= "
//                                 <div class='tab-pane fade $active' id='regStudent$counter' role='tabpanel' aria-labelledby='reg-student$counter'>";


//                 $tool_content .= " <h2 class='mb-4'>" . q($authInfo[2]) . "</h2>";
//                 if(!empty($authInfo[3])){
//                   $tool_content .= " <div class='alert alert-info mt-0 mb-4'>
//                                          <i class='fa-solid fa-circle-info fa-lg'></i>
//                                          <span><strong>$langInstructionsAuth</strong></br>" .q($authInfo[3]). "</span>
//                                      </div>";
//                                     }


//                 $tool_content .="  " . $authInfo[1] . "
                                  
//                                 </div>";
//           }

//           if($counter == count($authLink) - 1){
//               $tool_content .= "</div>";
//           }
//           $counter++;
//         }

//        $tool_content .= "
//                       </div>
//                     </div>
//                   </div>";















$head_content .= "
<script type='text/javascript'>
  $(document).ready(function() {
    if($('#showWarningModal2').val() == 1){
      var myModal = new bootstrap.Modal(document.getElementById('WarningModal2'));
      myModal.show();
    }
    $('.close-error').on('click',function(){
      window.location.reload();
    });
  });
</script>
<script>
  $(function() {
      $('#revealPass').mousedown(function () {
          $('#password_id').attr('type', 'text');
      }).mouseup(function () {
          $('#password_id').attr('type', 'password');
      })
  });
</script>
";

draw($tool_content, 0, null, $head_content);
