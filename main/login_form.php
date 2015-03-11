<?php
require_once '../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';

// if we are logged in there is no need to access this page
if (isset($_SESSION['uid'])) {
    redirect_to_home_page();
}

$warning = '';
$login_user = FALSE;
$eclass = $shibboleth_link = $cas_link = "";
$active_login_types = 0;

// check for eclass
$eclass = Database::get()->querySingle("SELECT auth_default FROM auth WHERE auth_name='eclass'");
if ($eclass and $eclass->auth_default == 1) {
    $active_login_types++;
}
// check for shibboleth
$shibactive = Database::get()->querySingle("SELECT auth_default FROM auth WHERE auth_name='shibboleth'");
if ($shibactive and $shibactive->auth_default) {
    $shibboleth_link = "<a class='btn btn-primary btn-block' href='{$urlServer}secure/index.php'>$langEnter</a><br />";
    $active_login_types++;
}
// check for CAS
$casactive = Database::get()->querySingle("SELECT auth_default FROM auth WHERE auth_name='cas'");
if ($casactive and $casactive->auth_default) {
    $cas_link = "<a class='btn btn-primary btn-block' href='{$urlServer}secure/cas.php'>$langEnter</a><br>";
    $active_login_types++;
}
/*// check for Social Networks
$social_networks = Database::get()->querySingle("SELECT auth_default FROM auth WHERE auth_name='social_networks'");
if ($social_networks) {
    if ($social_networks->auth_default == 1) {

    }
}*/

$columns = 12 / $active_login_types;

$next = isset($_GET['next'])?
    ("<input type='hidden' name='next' value='" . q($_GET['next']) . "'>"):
    '';

$pageName = $langUserLogin;
$tool_content .= action_bar(array(
                                array('title' => $langBack,
                                      'url' => "$urlServer",
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);
$tool_content .= "<div class='login-page'>
                    <div class='row'>
                    ";
print_r($eclass);
                if (!empty($eclass)) {
                    $tool_content .= "
                        <div class='col-sm-$columns'>
                            <div class='panel panel-default '>
                                <div class='panel-heading'><span>$langUserLogin</span></div>
                                <div class='panel-body'>
                                    <form class='form-horizontal' role='form' action='$urlServer?login_page=1' method='post'>
                                        $next
                                        <div class='form-group'>
                                            <div class='col-xs-12'>
                                                <input class='form-control' name='uname' placeholder='$langUsername'>
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
                                    </form>";
                                    if (Session::has('login_error')) {
                            $tool_content .= "<div class='alert alert-warning' role='alert'>".Session::get('login_error')."</div>";
                         }
                $tool_content .= "</div>
                            </div>
                        </div>
                        ";
                }


                //  Login with Cas
                if (!empty($cas_link)) {
                $tool_content .= "
                        <div class='col-sm-$columns'>
                            <div class='panel panel-default '>
                                <div class='panel-heading'><span>$langAlternateLogin</span></div>
                                <div class='panel-body'>
                                    <div class='col-sm-6'>
                                        <p>$langViaCAS</p>
                                    </div>
                                    <div class='col-sm-offset-1 col-sm-5'>
                                        $cas_link
                                    </div>
                                </div>
                            </div>
                        </div>
                        ";
                }

                //  Login with Sibboleth
                if (!empty($shibboleth_link)) {
                $tool_content .= "
                        <div class='col-sm-$columns'>
                            <div class='panel panel-default '>
                                <div class='panel-heading'><span>$langAlternateLogin</span></div>
                                <div class='panel-body'>
                                    <div class='col-sm-6'>
                                        <p>$langShibboleth</p>
                                    </div>
                                    <div class='col-sm-offset-1 col-sm-5'>
                                        $shibboleth_link
                                    </div>
                                </div>
                            </div>
                        </div>
                        ";
                }
         //  Login with Social Networks
        /*if (false) {
        $tool_content .= "
            <div class='col-xs-12'><div class='row'><div class='col-sm-4'>
                <div class='panel panel-default'>
                    <div class='panel-heading'><span>Social Networks</span></div>
                    <div class='panel-body'>
                        asdfadsfsda
                    </div>
                </div>
            </div></div></div>
                ";
        }*/
        $tool_content .= "</div></div>";

draw($tool_content, 0);
