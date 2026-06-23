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

$require_login = true;
$require_help = true;
$helpTopic = 'Gradebook';

require_once '../include/baseTheme.php';
require_once 'modules/progress/process_functions.php';
require_once 'modules/progress/Game.php';

if (is_module_disable(MODULE_ID_PROGRESS,MODULE_ID_PROGRESS)) {
    redirect_to_home_page();
}

$toolName = $langPortfolio;
$pageName = $langMyCertificates;
$content = false;

if (get_config('eportfolio_enable')) {
    $head_content .= 
        '<script>
            $(document).on(\'click\', \'a.list-group-item[href*="resources.php"]\', function(e) {
                e.preventDefault();

                const href = $(this).attr(\'href\');
                const url = new URL(href, window.location.origin);
                const rid = url.searchParams.get(\'rid\');

                if (href.includes("my_certificates")) {
                    const modalId = `modal_certificate_${rid}`;
                    const modalElement = document.getElementById(modalId);

                    if (modalElement) {
                        const Modal = new bootstrap.Modal(modalElement);
                        Modal.show();

                        const formSelector = `#vis_form_certificate_${rid}`;
                        $(formSelector).attr(\'action\', href);
                    } else {
                        console.warn(\'Certificate Modal with ID\', modalId, \'not found\');
                    }
                } else if (href.includes("my_badges")) {
                    const modalId = `modal_badge_${rid}`;
                    const modalElement = document.getElementById(modalId);

                    if (modalElement) {
                        const Modal = new bootstrap.Modal(modalElement);
                        Modal.show();

                        const formSelector = `#vis_form_badge_${rid}`;
                        $(formSelector).attr(\'action\', href);
                    } else {
                        console.warn(\'Modal with ID\', modalId, \'not found\');
                    }
                }
            });
        </script>';
}

// Add completed badge to my profile
if (isset($_GET['action']) && $_GET['action'] == 'add_badge_my_profile') {
    $badgeId = intval($_GET['badge_id']) ?? 0;
    if ($badgeId > 0) {
        $check_exists = Database::get()->querySingle("SELECT add_my_profile FROM user_badge WHERE user = ?d AND badge = ?d", $uid, $badgeId)->add_my_profile;
        if (!$check_exists) {
            $completedBadge = Database::get()->querySingle("SELECT completed FROM user_badge WHERE user = ?d AND badge = ?d", $uid, $badgeId)->completed;
            if ($completedBadge > 0) {
                Database::get()->query("UPDATE user_badge SET add_my_profile = ?d WHERE user = ?d AND badge = ?d", 1, $uid, $badgeId);
                Session::flash('message', $langBadgeAddedToMyProfile);
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page("main/mycertificates.php");
            } else {
                Session::flash('message', $langIncomplete);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page("main/mycertificates.php");
            }
        } else {
                Session::flash('message', $langResourceExists);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page("main/mycertificates.php");
        }
    } else {
        redirect_to_home_page("main/mycertificates.php");
    }
}

// Remove completed badge from my profile
if (isset($_GET['action']) && $_GET['action'] == 'del_badge_my_profile') {
    if (!isset($_GET['token']) || !validate_csrf_token($_GET['token'])) csrf_token_error();
    $badgeId = intval($_GET['badge_id']) ?? 0;
    if ($badgeId > 0) {
        Database::get()->query("UPDATE user_badge SET add_my_profile = ?d WHERE user = ?d AND badge = ?d", 0, $uid, $badgeId);
        Session::flash('message', $langBadgeRemovedToMyProfile);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("main/mycertificates.php");
    } else {
        redirect_to_home_page("main/mycertificates.php");
    }
}

// Add completed certificate to my profile
if (isset($_GET['action']) && $_GET['action'] == 'add_cert_my_profile') {
    $certId = intval($_GET['cert_id']) ?? 0;
    if ($certId > 0) {
        $check_exists = Database::get()->querySingle("SELECT add_my_profile FROM certified_users WHERE user_id = ?d AND cert_id = ?d", $uid, $certId)->add_my_profile;
        if (!$check_exists) {
            $completedCert = Database::get()->querySingle("SELECT completed FROM user_certificate WHERE user = ?d AND certificate = ?d", $uid, $certId)->completed;
            if ($completedCert > 0) {
                Database::get()->query("UPDATE certified_users SET add_my_profile = ?d WHERE user_id = ?d AND cert_id = ?d", 1, $uid, $certId);
                Session::flash('message', $langCertAddedToMyProfile);
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page("main/mycertificates.php");
            } else {
                Session::flash('message', $langIncomplete);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page("main/mycertificates.php");
            }
        } else {
            Session::flash('message', $langResourceExists);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("main/mycertificates.php");
        }
    } else {
        redirect_to_home_page("main/mycertificates.php");
    }
}

// Remove completed certificate from my profile
if (isset($_GET['action']) && $_GET['action'] == 'del_cert_my_profile') {
    if (!isset($_GET['token']) || !validate_csrf_token($_GET['token'])) csrf_token_error();
    $certId = intval($_GET['cert_id']) ?? 0;
    if ($certId > 0) {
        Database::get()->query("UPDATE certified_users SET add_my_profile = ?d WHERE user_id = ?d AND cert_id = ?d", 0, $uid, $certId);
        Session::flash('message', $langCertRemovedToMyProfile);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("main/mycertificates.php");
    } else {
        redirect_to_home_page("main/mycertificates.php");
    }
}

$certificate_content = '';
$badge_content = '';
$courses = Database::get()->queryArray("SELECT course.id course_id, code, title
                FROM course, course_user, user, course_module
                    WHERE course.id = course_user.course_id
                      AND course.visible <> " . COURSE_INACTIVE . "
                      AND (course.start_date IS NULL OR course.start_date < " . DBHelper::timeAfter() . ") 
                      AND (course.end_date IS NULL OR course.end_date > " . DBHelper::timeAfter() . ")
                      AND course_module.course_id = course_user.course_id
                      AND module_id = " . MODULE_ID_PROGRESS . "
                      AND course_module.visible <> 0
                      AND course_user.user_id = ?d
                      AND user.id = ?d", $uid, $uid);


if (count($courses) > 0) {

    // get completed certificates with public url
    $sql = Database::get()->queryArray("SELECT course_title, cert_title, cert_id, identifier, add_my_profile, template_id, cert_issuer "
                                        . "FROM certified_users "
                                        . "WHERE user_fullname = ?s", uid_to_name($uid, 'fullname'));
    if (count($sql) > 0) {
        foreach ($sql as $data) {

            if (get_config('eportfolio_enable')) {
                $certificate_modal = '<div class="modal fade" id="modal_certificate_'.$data->cert_id.'" tabindex="-1" aria-labelledby="certificateModalLabel_'.$data->cert_id.'" aria-hidden="true">
                    <div class="modal-dialog">
                    <div class="modal-content">
                
                        <div class="modal-header">
                        <h5 class="modal-title" id="certificateModalLabel_'.$data->cert_id.'">'.$langAddResePortfolio.' - '.$data->cert_title.'</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="'.$langClose.'"></button>
                        </div>
                
                        <div class="modal-body">
                        <form id="vis_form_certificate_'.$data->cert_id.'" name="vis_form_certificate_'.$data->cert_id.'" action="" method="post">
                            <div class="mb-3">
                                <label for="vis_form_certificate_'.$data->cert_id.'_select" class="form-label">'.$langePortfolioFieldsVisibilitySettings.'</label>
                                <select class="form-select" name="visibility" id="vis_form_certificate_'.$data->cert_id.'_select">
                                <option value="'.EPF_VISIBLE_PUBLIC.'">'.$langPublicePortfolioField.'</option>
                                <option value="'.EPF_VISIBLE_USERS.'">'.$langOpenToRegisteredUsers.'</option>
                                <option value="'.EPF_VISIBLE_PRIVATE.'">'.$langProfileInfoPrivate.'</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="vis_form_certificate_'.$data->cert_id.'_textarea" class="form-label">'.$langePortfolioPromptAddReflComments.'</label>
                                <textarea class="form-control" name="reflection_comments" id="vis_form_certificate_'.$data->cert_id.'_textarea"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">'.$langSubmit.'</button>
                        </form>
                        </div>
                
                    </div>
                    </div>
                </div>';
            } else {
                $certificate_modal = '';
            }

            $ePortfolioExists = Database::get()->querySingle("SELECT id FROM eportfolio_resource 
                                                                WHERE user_id = ?d
                                                                AND resource_id = ?d
                                                                AND resource_type = ?s", $uid, $data->cert_id, 'my_certificates');
            $html_cert_ep = '';
            if ($ePortfolioExists) {
                $html_cert_ep = "
                    <span class='badge Primary-600-bg'>
                        $langMyePortfolio
                    </span>
                ";
            }

            $myProfileExists = Database::get()->querySingle("SELECT id FROM certified_users 
                                                                WHERE user_id = ?d
                                                                AND cert_id = ?d
                                                                AND add_my_profile = ?d", $uid, $data->cert_id, 1);
            $html_cert_pr = '';
            if ($myProfileExists) {
                $html_cert_pr = "
                    <span class='badge Primary-600-bg'>
                        $langMyProfile
                    </span>
                ";
            }

            // get cert icon
            $CertThumb = get_cert_template($data->template_id);

            $certificate_content .= "<div class='col'>";
                $certificate_content .= "<div class='card reward-list-card h-100'>";
                    $certificate_content .=" 
                                            $certificate_modal
                                            <div class='d-flex justify-content-between align-items-start gap-4'>
                                                <div class='d-flex justify-content-start align-items-start gap-3'>
                                                    <div><img style='width: 50px; height: 50px; margin-top: 5px;' src='{$CertThumb}'></div>
                                                    <div>
                                                        <a class='TextBold' target='_blank' href='{$urlServer}main/out.php?i=$data->identifier'>$data->cert_title</a>
                                                        <p class='small-text text-muted'>" . $data->course_title . "</p>
                                                        <p class='small-text text-muted'>" . $data->cert_issuer . "</p>
                                                    </div>
                                                </div>
                                                <div>
                                                    ". action_button(array(
                                                        array(
                                                            'title' => $langAddResePortfolio,
                                                            'url' => "$urlServer"."main/eportfolio/resources.php?action=add&amp;type=my_certificates&amp;rid=".$data->cert_id,
                                                            'icon' => 'fa-star',
                                                            'show' => (get_config('eportfolio_enable'))
                                                        ),
                                                        array(
                                                            'title' => $langAddToMyProfile,
                                                            'url' => "$_SERVER[SCRIPT_NAME]" . "?action=add_cert_my_profile&amp;cert_id=".$data->cert_id,
                                                            'icon' => 'fa-star',
                                                            'show' => !$data->add_my_profile
                                                        ),
                                                        array(
                                                            'title' => $langDelFromMyProfile,
                                                            'url' => "$_SERVER[SCRIPT_NAME]" . "?action=del_cert_my_profile&amp;cert_id=".$data->cert_id."&amp;token=$_SESSION[csrf_token]",
                                                            'icon' => 'fa-solid fa-xmark',
                                                            'class' => 'Accent-200-cl',
                                                            'show' => $data->add_my_profile
                                                        ),
                                                    ))."
                                                </div>
                                            </div>
                                            <div class='d-flex gap-3 mt-2'>$html_cert_ep $html_cert_pr</div>";
              $certificate_content .= " </div>
                                    </div>";


        }
    }

    $counter_game_certificate = 0;
    $counter_game_badge = 0;
    foreach ($courses as $course1) {
        $course_id = $course1->course_id;
        $code = $course1->code;

        // check for completeness in order to refresh user data
        Game::checkCompleteness($uid, $course_id);
        $iter = array('certificate', 'badge');
        foreach ($iter as $key) {
            ${'game_'.$key} = array();
        }
        // populate with data
        foreach ($iter as $key) {
            $gameQ = "SELECT a.*, b.title,"
                    . " b.description, b.issuer, b.active, b.created, b.id"
                    . " FROM user_{$key} a "
                    . " JOIN {$key} b ON (a.{$key} = b.id) "
                    . " WHERE a.user = ?d "
                    . "AND b.course_id = ?d "
                    . "AND b.active = 1 "
                    . "AND b.bundle != -1 "
                    . "AND (b.expires IS NULL OR b.expires > NOW())";
        $sql = Database::get()->queryArray($gameQ, $uid, $course_id);
        foreach ($sql as $game) {
            if ($key == 'badge') { // get badge icon
                $badge_filename = Database::get()->querySingle("SELECT filename FROM badge_icon WHERE id = 
                                                         (SELECT icon FROM badge WHERE id = ?d)", $game->id)->filename;
                }
                ${'game_'.$key}[] = $game;
            }
        }
        // get incomplete certificates
        $cert_content = '';
        if (count($game_certificate) > 0) {
            $counter_game_certificate++;
            foreach ($game_certificate as $key => $certificate) {
                $cert_content = round($certificate->completed_criteria / $certificate->total_criteria * 100, 0);
                if ($certificate->completed == 1) {
                    continue;
                }
                $certTemplate = Database::get()->querySingle("SELECT `template` FROM `certificate` WHERE id = ?d", $certificate->certificate)->template;
                // get cert icon
                $CertThumb = get_cert_template($certTemplate);
                $certificate_content .= "<div class='col'>
                                            <div class='card reward-list-card h-100'>";
                        $certificate_content .= "<div class='d-flex justify-content-between align-items-center gap-3 no-completed-cert-div'>
                                                    <div class='w-75 d-flex justify-content-start align-items-start gap-3 no-completed-cert-col'>
                                                        <img style='width: 50px; height: 50px; margin-top: 5px;' src='{$CertThumb}'>
                                                        <div>
                                                            <a class='TextBold' href= '{$urlServer}modules/progress/index.php?course=$code&amp;certificate_id=$certificate->certificate&amp;u=$uid'>$certificate->title</a> 
                                                            <p class='small-text text-muted'>$course1->title</p>
                                                            <p class='small-text text-muted'>$certificate->issuer</p>
                                                        </div>
                                                    </div>
                                                    <div class='w-25'>
                                                        <div class='progress progress_bar_certificates w-100'>
                                                            <div class='progress-bar progress_bar_fill_certificates' role='progressbar' style='width: $cert_content%;' aria-valuenow='$cert_content' aria-valuemin='0' aria-valuemax='100'></div>
                                                        </div>
                                                        <p class='small-text text-muted text-end'>$cert_content %</p>
                                                    </div>
                                                </div>
                                           </div>
                                        </div>";
            }
        }

        // get badges
        if (count($game_badge) > 0) {
            $counter_game_badge++;
            foreach ($game_badge as $key => $badge) {
                $cert_content = round($badge->completed_criteria / $badge->total_criteria * 100, 0);
                if (get_config('eportfolio_enable')) {
                    $badge_modal = '<div class="modal fade" id="modal_badge_'.$badge->badge.'" tabindex="-1" aria-labelledby="badgeModalLabel_'.$badge->badge.'" aria-hidden="true">
                        <div class="modal-dialog">
                        <div class="modal-content">
                    
                            <div class="modal-header">
                            <h5 class="modal-title" id="badgeModalLabel_'.$badge->badge.'">'.$langAddResePortfolio.' - '.$badge->title.'</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="'.$langClose.'"></button>
                            </div>
                    
                            <div class="modal-body">
                            <form id="vis_form_badge_'.$badge->badge.'" name="vis_form_badge_'.$badge->badge.'" action="" method="post">
                                <div class="mb-3">
                                    <label for="vis_form_badge_'.$badge->badge.'_select" class="form-label">'.$langePortfolioFieldsVisibilitySettings.'</label>
                                    <select class="form-select" name="visibility" id="vis_form_badge_'.$badge->badge.'_select">
                                    <option value="'.EPF_VISIBLE_PUBLIC.'">'.$langPublicePortfolioField.'</option>
                                    <option value="'.EPF_VISIBLE_USERS.'">'.$langOpenToRegisteredUsers.'</option>
                                    <option value="'.EPF_VISIBLE_PRIVATE.'">'.$langProfileInfoPrivate.'</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="vis_form_badge_'.$badge->badge.'_textarea" class="form-label">'.$langePortfolioPromptAddReflComments.'</label>
                                    <textarea class="form-control" name="reflection_comments" id="vis_form_badge_'.$badge->badge.'_textarea"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">'.$langSubmit.'</button>
                            </form>
                            </div>
                    
                        </div>
                        </div>
                    </div>';
                } else {
                    $badge_modal = '';
                }

                $ePortfolioExists = Database::get()->querySingle("SELECT id FROM eportfolio_resource 
                                                                  WHERE user_id = ?d
                                                                  AND resource_id = ?d
                                                                  AND resource_type = ?s", $uid, $badge->badge, 'my_badges');
                $html_cert_ep = '';
                if ($ePortfolioExists) {
                    $html_cert_ep = "
                        <span class='badge Primary-600-bg'>
                            $langMyePortfolio
                        </span>
                    ";
                }

                $myProfileExists = Database::get()->querySingle("SELECT id FROM user_badge 
                                                                  WHERE user = ?d
                                                                  AND badge = ?d
                                                                  AND add_my_profile = ?d", $uid, $badge->badge, 1);
                $html_cert_pr = '';
                if ($myProfileExists) {
                    $html_cert_pr = "
                        <span class='badge Primary-600-bg'>
                            $langMyProfile
                        </span>
                    ";
                }

                $iconBadge = get_icon_badge($badge->badge);

                $badge_content .= " <div class='col'>";
                    $badge_content .= " <div class='card reward-list-card h-100'>";
                        $badge_content .= " 
                                            $badge_modal
                                            <div class='d-flex justify-content-between align-items-center gap-3 no-completed-cert-div'>
                                                <div class='w-75 d-flex justify-content-start align-items-start gap-3 no-completed-cert-col'>
                                                    <img style='width: 50px; height: 50px; margin-top: 5px;' src='{$iconBadge}'>
                                                    <div>
                                                        <a class='TextBold' href= '{$urlServer}modules/progress/index.php?course=$code&amp;badge_id=$badge->badge&amp;u=$uid'>$badge->title</a> 
                                                        <p class='small-text text-muted'>$course1->title</p>
                                                        <p class='small-text text-muted'>$badge->issuer</p>
                                                    </div>
                                                </div>
                                                <div class='w-25'>";
                                                if ($badge->completed) {
                                                    $badge_content .= " 
                                                    <div class='text-end'>
                                                        ". action_button(array(
                                                            array(
                                                                'title' => $langAddResePortfolio,
                                                                'url' => "$urlServer"."main/eportfolio/resources.php?action=add&amp;type=my_badges&amp;rid=".$badge->badge,
                                                                'icon' => 'fa-star',
                                                                'show' => (get_config('eportfolio_enable'))
                                                            ),
                                                            array(
                                                                'title' => $langAddToMyProfile,
                                                                'url' => "$_SERVER[SCRIPT_NAME]" . "?action=add_badge_my_profile&amp;badge_id=".$badge->badge,
                                                                'icon' => 'fa-star',
                                                                'show' => !$badge->add_my_profile
                                                            ),
                                                            array(
                                                                'title' => $langDelFromMyProfile,
                                                                'url' => "$_SERVER[SCRIPT_NAME]" . "?action=del_badge_my_profile&amp;badge_id=".$badge->badge."&amp;token=$_SESSION[csrf_token]",
                                                                'icon' => 'fa-solid fa-xmark',
                                                                'class' => 'Accent-200-cl',
                                                                'show' => $badge->add_my_profile
                                                            )
                                                        ))."
                                                    </div>";
                                                } else {
                                  $badge_content .= "<div class='progress progress_bar_certificates w-100'>
                                                        <div class='progress-bar progress_bar_fill_certificates' role='progressbar' style='width: $cert_content%;' aria-valuenow='$cert_content' aria-valuemin='0' aria-valuemax='100'></div>
                                                    </div>
                                                    <p class='small-text text-muted text-end'>$cert_content %</p>";
                                                }
                               $badge_content .="</div>
                                            </div>
                                            <div class='d-flex gap-3 mt-2'>$html_cert_ep $html_cert_pr</div>";
                $badge_content .= "     </div>
                                    </div>";
            }
        }
    }

    // ui certificates and badges
    if (count($sql) == 0 && $counter_game_certificate == 0 && $counter_game_badge == 0) {
        $tool_content .= "
            <div class='col-12'>
                <div class='alert alert-warning'>
                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                    <span>$langNoInfoAvailable</span>
                </div>
            </div>
        ";
    } else {
        $tool_content .= "
        <div class='col-12'>
            <div class='card panelCard px-lg-4 py-lg-3'>
                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                    <h2 class='text-heading-h3'>$langCertificates</h2>
                </div>
                <div class='card-body d-flex justify-content-between align-items-center gap-3 flex-wrap'>";
                if ($counter_game_certificate == 0) {
                    $tool_content .= "
                    <div class='alert alert-warning'>
                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                        <span>$langNoInfoAvailable</span>
                    </div>";
                } else {
                    $tool_content .= "    
                    <div class='row row-cols-md-2 row-cols-1 g-3'>
                        $certificate_content
                    </div>";
                }
                    
        $tool_content .= "  
                </div>
            </div>
        </div>";

        $tool_content .= "
        <div class='col-12 mt-4'>
            <div class='card panelCard px-lg-4 py-lg-3'>
                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                    <h2 class='text-heading-h3'>$langBadges</h2>
                </div>
                <div class='card-body d-flex justify-content-between align-items-center gap-3 flex-wrap'>";
                if ($counter_game_badge == 0) {
                    $tool_content .= "
                    <div class='alert alert-warning'>
                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                        <span>$langNoInfoAvailable</span>
                    </div>";
                } else {
                    $tool_content .= "      
                    <div class='row row-cols-md-2 row-cols-1 g-3'>
                        $badge_content
                    </div>";
                }
                    
        $tool_content .= "  
                </div>
            </div>
        </div>";
        
    }
    
} else {
    $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoCertBadge</span></div>";
}

draw($tool_content, 1);


function get_cert_template($templateId) {
    global $urlServer;

    $q = Database::get()->querySingle("SELECT id, `filename` FROM certificate_template WHERE id = ?d", $templateId);
    
    if (!str_contains($q->filename, '.html')) { // new way
        $cert_file = getFilenames(true, $q->id, 'thumbnail');
    } else { // old way
        $f = explode('.html', $q->filename);
        if (count($f) > 0) {
            $cert_file = $urlServer . "courses/user_progress_data/cert_templates/" . $f[0] . "_thumbnail.png";
        } else {
            $cert_file = '';
        } 
    }

    return $cert_file;

}

function get_icon_badge($badgeId) {
    global $urlServer;

    $q = Database::get()->querySingle("SELECT bi.filename FROM badge_icon bi 
                                        JOIN badge b ON b.icon=bi.id
                                        WHERE b.id = ?d", $badgeId);
    

    if ($q) {
        $icon = $urlServer . "courses/user_progress_data/badge_templates/" . $q->filename;
    } else { // old way
        $icon = '';
    }

    return $icon;

}