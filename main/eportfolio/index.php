<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */


$require_login = false;
$guest_allowed = true;

$require_help = true;
$helpTopic = 'portfolio';
$helpSubTopic = 'e_portfolio';

include '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';
require_once 'main/eportfolio/eportfolio_functions.php';
require_once 'modules/sharing/sharing.php';

if (!get_config('eportfolio_enable')) {
    $tool_content = "<div class='alert alert-danger alert-dismissible'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langePortfolioDisabled</span><button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    if ($session->status == 0) {
        draw($tool_content, 0);
    } else {
        draw($tool_content, 1);
    }
    exit;
}

if (isset($_GET['token'])) {
    $eportf_user = Database::get()->querySingle("SELECT id FROM user WHERE eportfolio_token = ?s", $_GET['token']);
    if (empty($eportf_user)) {
        redirect_to_home_page();
        exit;
    } else {
        $id = $eportf_user->id;
        if (isset($_SESSION['uid']) && ($id == $_SESSION['uid'])) {
            $toolName = $langPortfolio;
            $pageName = $langMyePortfolio;
        } else {
            $toolName = $langUserePortfolio;
            $pageName = q(uid_to_name($id));
        }
    }
} else {
    if ($session->status == 0) {
        redirect_to_home_page();
        exit;
    } else {
        $id = $uid;
        $toolName = $langUserePortfolio;
    }
}

$userdata = Database::get()->querySingle("SELECT surname, givenname, eportfolio_enable, eportfolio_token
                                          FROM user WHERE id = ?d", $id);

$navigation[] = array("url" => "{$urlAppend}main/profile/display_profile.php", "name" => $langMyProfile);
$clipboard_link = "";

if ($userdata) {

    if ($uid == $id) {
        if (isset($_GET['toggle_val'])) {
            if ($_GET['toggle_val'] == 'on') {
                //Generate token if it is the first time that the user enables eportfolio
                if (is_null($userdata->eportfolio_token)) {
                    Database::get()->query("UPDATE user SET eportfolio_enable = ?d, eportfolio_token = ?s 
                        WHERE id = ?d", 1, rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '='), $id);
                } else {
                    Database::get()->query("UPDATE user SET eportfolio_enable = ?d WHERE id = ?d", 1, $id);
                }
            } elseif ($_GET['toggle_val'] == 'off') {
                Database::get()->query("UPDATE user SET eportfolio_enable = ?d WHERE id = ?d", 0, $id);
            }
            redirect_to_home_page("main/eportfolio/index.php");
        }

        if ($userdata->eportfolio_enable == 0) {
            $tool_content .= "<div class='col-12'><div class='alert alert-warning alert-dismissible'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langePortfolioDisableWarning</span><button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div></div>";
        } elseif ($userdata->eportfolio_enable == 1) {
            load_js('clipboard.js');
        }

        if (isset($_GET['view']) && $_GET['view'] == 'public') {
            $view_str = "?view=public";
            $preview_info_div = "<div class='col-12'><div class='alert alert-info alert-dismissible'><i class='fa-solid fa-circle-info fa-lg'></i><span>
                    $langePortfolioPreviewAsGuest</span><button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div></div>";
        } elseif (isset($_GET['view']) && $_GET['view'] == 'registered') {
            $view_str = "?view=registered";
            $preview_info_div = "<div class='col-12'><div class='alert alert-info alert-dismissible'><i class='fa-solid fa-circle-info fa-lg'></i><span>
                    $langePortfolioPreviewAsRegistered</span><button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div></div>";
        } else {
            $view_str = "";
            $preview_info_div = "";
        }
        
        $resources_url = "{$urlAppend}main/eportfolio/resources.php".$view_str;

        $action_bar = action_bar(array(
                                        array('title' => $langSee,
                                            'icon' => 'fa-solid fa-binoculars',
                                            'level' => 'primary',
                                            'options' => array(
                                                array('class' => '', 'title' => $langNotRegistered,
                                                      'url' => "{$urlAppend}main/eportfolio/index.php?view=public",
                                                      'icon' => 'fa-solid fa-globe'),
                                                array('class' => '', 'title' => $langRegisteredUsers,
                                                      'url' => "{$urlAppend}main/eportfolio/index.php?view=registered",
                                                      'icon' => 'fa-solid fa-users'),
                                                array('class' => '', 'title' => $langUser,
                                                      'url' => "{$urlAppend}main/eportfolio/index.php",
                                                      'icon' => 'fa-solid fa-lock'),
                                            )),
                                        array('title' => $userdata->eportfolio_enable ? $langViewHide : $langViewShow,
                                            'url' => $userdata->eportfolio_enable ? "{$urlAppend}main/eportfolio/index.php?toggle_val=off" : "{$urlAppend}main/eportfolio/index.php?toggle_val=on",
                                            'icon' => $userdata->eportfolio_enable ? 'fa-eye-slash' : 'fa-eye',
                                            'level' => 'primary'),
                                        array('title' => $langBio,
                                            'url' => "{$urlAppend}main/eportfolio/index.php?action=get_bio",
                                            'icon' => 'fa-solid fa-book-open',
                                            'level' => 'primary',
                                            'show' => file_exists("$webDir/courses/eportfolio/userbios/$id/bio.pdf")),
                                        array('title' => $langUploadBio,
                                            'url' => "{$urlAppend}main/eportfolio/bio_upload.php",
                                            'icon' => 'fa-upload'),
                                        array('title' => $langEditChange,
                                            'url' => "{$urlAppend}main/eportfolio/edit_eportfolio.php",
                                            'icon' => 'fa-edit' ),
                                ));
        $tool_content .= $action_bar;

        $warning = '';

        if (!file_exists("$webDir/courses/eportfolio/userbios/$id/bio.pdf")) {
            $warning = $langePortfolioAddCVPrompt;
        }

        $eportfolio_completion = calculate_eportfolio_completion($id);
        if ($eportfolio_completion < 30) {
            if (!empty($warning)) {
                $warning .= "<br><br>".$langePortfolioComplBelow30;
            } else {
                $warning = $langePortfolioComplBelow30;
            }
        } elseif ($eportfolio_completion < 60) {
            if (!empty($warning)) {
                $warning .= "<br><br>".$langePortfolioComplBelow60;
            } else {
                $warning = $langePortfolioComplBelow60;
            }
        }

        if (!empty($warning)) {
           $tool_content .= "<div class='col-12'><div class='alert alert-warning alert-dismissible'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>
                    $warning</span><button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>"; 

        }
        
        $tool_content .= $preview_info_div;

    } else {
        if ($userdata->eportfolio_enable == 0) {
            $tool_content = "<div class='col-12'><div class='alert alert-danger alert-dismissible'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langUserePortfolioDisabled</span><button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div></div>";
            if ($session->status == 0) {
                draw($tool_content, 0);
            } else {
                draw($tool_content, 1);
            }
            exit;
        }

        $resources_url = "{$urlAppend}main/eportfolio/resources.php?token=$userdata->eportfolio_token";

        $action_bar = action_bar(array(
                                        array('title' => $langBio,
                                            'url' => "{$urlAppend}main/eportfolio/index.php?action=get_bio&amp;token=$userdata->eportfolio_token",
                                            'icon' => 'fa-solid fa-book-open',
                                            'level' => 'primary-label',
                                            'show' => file_exists("$webDir/courses/eportfolio/userbios/$id/bio.pdf")),
                                    ));

        $tool_content .= $action_bar;
    }

    if (isset($_GET['action']) && $_GET['action'] == 'get_bio') {
        if (file_exists("$webDir/courses/eportfolio/userbios/$id/bio.pdf")) {
            send_file_to_client(str_replace('\\', '/', $webDir)."/courses/eportfolio/userbios/$id/bio.pdf", 'bio.pdf', null, true);
        }
    }

    $head_content .= eportfolio_alert_css();

    $head_content .= "
        <script>
        $(function() {
            var navLinks = \$('#navbar-exampleIndexPortfolio .nav-link');
            var scrollLock = false;

            navLinks.on('click', function() {
                navLinks.removeClass('active');
                \$(this).addClass('active');
                scrollLock = true;
                setTimeout(function() { scrollLock = false; }, 1000);
            });

            function updateActive() {
                if (scrollLock) return;
                var scrollTop = \$(window).scrollTop();
                var offset = 90;
                var current = null;

                \$('[id^=\"IndexPortfolio\"]').each(function() {
                    if (\$(this).offset().top - offset <= scrollTop) {
                        current = \$(this).attr('id');
                    }
                });

                navLinks.removeClass('active');
                if (current) {
                    navLinks.filter('[href=\"#' + current + '\"]').addClass('active');
                } else {
                    navLinks.first().addClass('active');
                }
            }

            \$(window).on('scroll', updateActive);
            updateActive();
        });
        </script>
    ";

    $tool_content .= render_eportfolio_profile_card($id, $resources_url ?? null, $langResourcesCollection);

    $ret_str = render_eportfolio_fields_content($id);

    if ($ret_str['panels'] == "") {
        $tool_content .= "
                    <div class='col-12'>
                        <div class='alert alert-warning alert-dismissible'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoInfoAvailable.</span><button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        </div>
                    </div>";
    } else {
        $tool_content .= "<div class='row mt-4'>";
        $tool_content .= "<div class='col-sm-9'>";
        $tool_content .= "<div class='d-flex flex-column gap-3'>" . $ret_str['panels'] . "</div>";
        $tool_content .= "</div>";
        $tool_content .= $ret_str['right_menu'];
        $tool_content .= "</div>";
    }

    if ($userdata->eportfolio_enable == 1 AND $ret_str['panels'] != "") {
        $social_share = "<div class='float-end mt-4'>".print_sharing_links($urlServer."main/eportfolio/index.php?token=$userdata->eportfolio_token", $langUserePortfolio)."</div>";
    } else {
        $social_share = '';
    }

    $tool_content .= "$social_share</div>";
}

if ($uid == $id) {
    draw($tool_content, 1, null, $head_content);
} else {
    draw($tool_content, 0, null, $head_content);
}
