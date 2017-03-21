<?php
/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

/*
 * @file login_form.inc.php
 *
 * @abstract Login form
 *
 */

require_once 'modules/auth/auth.inc.php';

function login_form($format='main') {
    global $head_content, $urlServer, $urlAppend, $langLogInWith, $langEnter,
        $langViaSocialNetwork, $langUsername, $langPass, $langUserLogin,
        $langOr, $lang_forgot_pass, $langAlternateLogin, $extAuthMethods,
        $hybridAuthMethods, $langOrLoginWith;

    // check authentication methods
    $aid = null;
    $hybridLinkId = null;
    $hybridProviders = array();
    $authLink = array();
    $loginFormEnabled = false;
    $loginForm = '';
    $q = Database::get()->queryArray("SELECT auth_id, auth_name, auth_default, auth_title
                FROM auth WHERE auth_default <> 0
                ORDER BY auth_default DESC, auth_id");
    foreach ($q as $l) {
        if (in_array($l->auth_name, $extAuthMethods)) {
            $loginUrl = $urlServer . ($l->auth_name == 'cas'? 'modules/auth/cas.php': 'secure/');
            $loginTitle = empty($l->auth_title)? "$langLogInWith<br>{$l->auth_name}": q(getSerializedMessage($l->auth_title));
            if ($format == 'main') {
                $authLink[] = array(
                    'showTitle' => true,
                    'class' => 'login-option login-option-sso',
                    'title' => $loginTitle,
                    'html' => "<a class='btn btn-default btn-login' href='$loginUrl'>$langEnter</a><br>");
            } else {
                $authLink[] = "<a class='btn btn-block btn-primary' href='$loginUrl'>$loginTitle</a>";
            }
        } elseif (in_array($l->auth_name, $hybridAuthMethods)) {
            $hybridProviders[] = $l->auth_name;
            $font = $class = $l->auth_name;
            if ($class === 'live') {
                $class = 'microsoft';
                $font = 'windows';
            }
            if ($format == 'main') {
                $providerIcon[$l->auth_name] = array($class, $font);
                if (is_null($hybridLinkId)) {
                    $authLink[] = array(
                        'showTitle' => true,
                        'class' => 'login-option',
                        'title' => $langViaSocialNetwork);
                    $hybridLinkId = count($authLink) - 1;
                }
            } else {
                $authLink[] = "<a class='btn btn-block btn-social btn-$class' href='{$urlServer}index.php?provider={$l->auth_name}'><span class='fa fa-$font'></span>" . ucfirst($l->auth_name) . "</a>";
            }
        } elseif (!$loginFormEnabled) {
            $autofocus = count($authLink)? '' : 'autofocus' ;
            $loginFormEnabled = true;
            if ($format == 'main') {
                $authLink[] = array(
                    'showTitle' => false,
                    'class' => 'login-option',
                    'title' => empty($l->auth_title)? "$langLogInWith<br>Credentials": q(getSerializedMessage($l->auth_title)),
                    'html' => "<form action='$urlServer' method='post'>
                             <div class='form-group'>
                                <label for='uname' class='sr-only'>$langUsername</label>
                                <input type='text' id='uname' name='uname' placeholder='$langUsername' $autofocus><span class='col-xs-2 col-sm-2 col-md-2 fa fa-user'></span>
                             </div>
                             <div class='form-group'>
                                <label for='pass' class='sr-only'>$langPass</label>
                                <input type='password' id='pass' name='pass' placeholder='$langPass'><span id='revealPass' class='fa fa-eye' style='margin-left: -20px; color: black;'></span>&nbsp;&nbsp;<span class='col-xs-2 col-sm-2 col-md-2 fa fa-lock'></span>
                             </div>
                             <button type='submit' name='submit' class='btn btn-login'>$langEnter</button>
                           </form>
                           <div class='text-right'>
                             <a href='modules/auth/lostpass.php'>$lang_forgot_pass</a>
                           </div>");
            }
        }

        if (count($hybridProviders) or ($format != 'main' and count($authLink))) {
            $head_content .= "<link rel='stylesheet' type='text/css' href='{$urlServer}template/default/CSS/bootstrap-social.css'>";
        }
        if ($format == 'main' and count($hybridProviders)) {
            $authLink[$hybridLinkId]['html'] = '<div style="padding-top: 10px;">';
            $beginHybridHTML = true;
            foreach ($hybridProviders as $provider) {
                if ($beginHybridHTML) {
                    $beginHybridHTML = false;
                } else {
                    $authLink[$hybridLinkId]['html'] .= '<br>';
                }
                list($providerClass, $providerFont) = $providerIcon[$provider];
                $authLink[$hybridLinkId]['html'] .=
                    "<a class='btn btn-block btn-social btn-$providerClass' href='{$urlServer}index.php?provider=$provider'><span class='fa fa-$providerFont'></span>" . ucfirst($provider) . "</a>";
            }
            $authLink[$hybridLinkId]['html'] .= '</div>';
        }

        $head_content .= "
      <script>
        $(function() {
            $('#revealPass').mousedown(function () {
                $('#pass').attr('type', 'text');
            }).mouseup(function () {
                $('#pass').attr('type', 'password');
            })
        });
      </script>";
    }

    if ($format != 'main') {
        $altLoginTitle = '';
        if ($loginFormEnabled) {
            $loginForm .= "
                <div class='col-sm-6'>
                    <form action='$urlServer' method='post' id='loginForm'>
                        <input class='nextUrl' name='next' type='hidden' value='/'>
                        <div class='form-group'>
                            <label class='hidden' for='uname'>$langUsername</label>
                            <input name='uname' type='text' class='form-control' id='uname' placeholder='$langUsername'>
                        </div>
                        <div class='form-group'>
                            <label class='hidden' for='pass'>$langPass</label>
                            <input name='pass' type='password' class='form-control' id='pass' placeholder='$langPass'>
                        </div>
                        <button type='submit' name='submit' class='btn btn-primary btn-block'>$langEnter</button>
                    </form>
                </div>";
            if (count($authLink)) {
                $altLoginTitle = "<h5 class='text-center'>$langOrLoginWith:</h5>";
            }
        }
        if (count($authLink)) {
            $loginForm .= "
            <div class='col-sm-6 alt-login'>
                $altLoginTitle";
            foreach ($authLink as $html) {
                $loginForm .= $html;
            }
            $loginForm .= "
            </div>";
        }
        return $loginForm;
    }

    $loginForm .= "
        <div class='col-xs-12 col-sm-6 col-md-5 col-lg-4 pull-right login-form'>
            <div class='wrapper-login-option'>";

    $show_seperator = count($authLink) > 1;
    if (count($authLink) > 3) {
        // home page login form with more than 3 buttons not supported
        $authLink = array($authLink[0]);
        $show_buttons = false;
    } else {
        $show_buttons = true;
    }

    foreach ($authLink as $i => $l) {
        $loginForm .= "
                <div class='$l[class]'>
                    <h2>$langUserLogin</h2>
                    <div>" . ($l['showTitle']? "<span class='head-text' style='font-size:14px;'>$l[title]</span>": '') .
                        $l['html'] . "
                    </div>";
        if ($show_seperator) {
            $loginForm .= "
                    <div class='login-settings row'>
                        <div class='or-separator'><span>$langOr</span></div>
                        <div class='alt_login text-center'>
                            <span>";
            if ($show_buttons) {
                foreach ($authLink as $j => $otherAuth) {
                    if ($j != $i) {
                        $loginForm .= "<button type='button' data-target='$j' class='option-btn-login hide'>$otherAuth[title]</button>";
                    }
                }
            } else {
                $loginForm .= "<a href='{$urlAppend}main/login_form.php' class='btn btn-default option-btn-login'>$langAlternateLogin</a>";
            }
            $loginForm .= "
                            </span>
                        </div>
                    </div>";
        }
        $loginForm .= "
                </div>";
    }
    $loginForm .= "
            </div>
        </div>";

    return $loginForm;
}
