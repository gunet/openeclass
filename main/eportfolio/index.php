<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
    $tool_content = "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langePortfolioDisabled</span></div>";
    if ($session->status == 0) {
        draw($tool_content, 0);
    } else {
        draw($tool_content, 1);
    }
    exit;
}

if (isset($_GET['id']) && intval($_GET['id']) > 0) {
    $id = intval($_GET['id']);
    $toolName = $langUserePortfolio;
} else {
    if ($session->status == 0) {
        redirect_to_home_page();
        exit;
    } else {
        $id = $uid;
        $toolName = $langMyePortfolio;
    }
}

if (!token_validate('eportfolio' . $id, $_GET['token'])) {
    redirect_to_home_page();
}

$token = token_generate('eportfolio' . $id);

$userdata = Database::get()->querySingle("SELECT surname, givenname, eportfolio_enable
                                          FROM user WHERE id = ?d", $id);

$pageName = q("$userdata->givenname $userdata->surname");
$navigation[] = array("url" => "{$urlAppend}main/profile/display_profile.php", "name" => $langMyProfile);
$clipboard_link = "";

if ($userdata) {

    if ($uid == $id) {
        if (isset($_GET['toggle_val'])) {
            if ($_GET['toggle_val'] == 'on') {
                Database::get()->query("UPDATE user SET eportfolio_enable = ?d WHERE id = ?d", 1, $id);
            } elseif ($_GET['toggle_val'] == 'off') {
                Database::get()->query("UPDATE user SET eportfolio_enable = ?d WHERE id = ?d", 0, $id);
            }
            redirect_to_home_page("main/eportfolio/index.php?id=$id&token=$token");
        }

        if ($userdata->eportfolio_enable == 0) {
            $tool_content .= "<div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langePortfolioDisableWarning</span></div></div>";
        } elseif ($userdata->eportfolio_enable == 1) {
            load_js('clipboard.js');
            $clipboard_link = "
                            <div class='card card panelCard border-card-left-default px-3 py-2 mt-4'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                <h3>$langPageLink</h3>
                                    <button class='btn submitAdminBtn' id='copy-btn' data-bs-toggle='tooltip' data-bs-placement='bottom' data-clipboard-target='#page-link'>
                                        <span class='fa fa-clipboard'></span>&nbsp;$langCopy
                                    </button>
                                </div>
                                <div class='card-body'>
                                    <input aria-label='$langCopy' class='form-control' id='page-link' value='{$urlServer}main/eportfolio/index.php?id=$id&token=$token'>
                                </div>                              
                            </div>";
            $head_content .= "<script type='text/javascript'>
                               
                                $(function() {
                                  var clipboard = new Clipboard('#copy-btn');
                    
                                  clipboard.on('success', function(e) {
                                    e.clearSelection();
                                    $(e.trigger).attr('title', '$langCopiedSucc').tooltip('fixTitle').tooltip('show');
                                  });
                    
                                  clipboard.on('error', function(e) {
                                    $(e.trigger).attr('title', '$langCopiedErr').tooltip('fixTitle').tooltip('show');
                                  });
                    
                                });
                              </script>";
        }

        $tool_content .= action_bar(array(
                                        array('title' => $userdata->eportfolio_enable ? $langViewHide : $langViewShow,
                                            'url' => $userdata->eportfolio_enable ? "{$urlAppend}main/eportfolio/index.php?id=$id&amp;token=$token&amp;toggle_val=off" : "{$urlAppend}main/eportfolio/index.php?id=$id&amp;token=$token&amp;toggle_val=on",
                                            'icon' => $userdata->eportfolio_enable ? 'fa-eye-slash' : 'fa-eye',
                                            'level' => 'primary'),
                                        array('title' => $langBio,
                                            'url' => "{$urlAppend}main/eportfolio/index.php?action=get_bio&amp;id=$id&amp;token=$token",
                                            'icon' => 'fa-solid fa-book-open',
                                            'level' => 'primary',
                                            'show' => file_exists("$webDir/courses/eportfolio/userbios/$id/bio.pdf")),
                                        array('title' => $langUploadBio,
                                            'url' => "{$urlAppend}main/eportfolio/bio_upload.php",
                                            'icon' => 'fa-upload'),
                                        array('title' => $langEditChange,
                                            'url' => "{$urlAppend}main/eportfolio/edit_eportfolio.php",
                                            'icon' => 'fa-edit' ),
                                        array('title' => $langResourcesCollection,
                                            'url' => "{$urlAppend}main/eportfolio/resources.php?id=$id&amp;token=$token",
                                            'icon' => 'fa-solid fa-award',
                                            'level' => 'primary'
                                            )
                                ));
    } else {
        if ($userdata->eportfolio_enable == 0) {
            $tool_content = "<div class='col-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langUserePortfolioDisabled</span></div></div>";
            if ($session->status == 0) {
                draw($tool_content, 0);
            } else {
                draw($tool_content, 1);
            }
            exit;
        }

        $action_bar = action_bar(array(
                                        array('title' => $langBio,
                                            'url' => "{$urlAppend}main/eportfolio/index.php?action=get_bio&amp;id=$id&amp;token=$token",
                                            'icon' => 'fa-solid fa-book-open',
                                            'level' => 'primary-label',
                                            'show' => file_exists("$webDir/courses/eportfolio/userbios/$id/bio.pdf")),
                                        array('title' => $langResourcesCollection,
                                              'url' => "{$urlAppend}main/eportfolio/resources.php?id=$id&amp;token=$token",
                                              'icon' => 'fa-solid fa-award',
                                              'level' => 'primary-label'),
                                    ));

        $tool_content .= $action_bar;
    }

    if (isset($_GET['action']) && $_GET['action'] == 'get_bio') {
        if (file_exists("$webDir/courses/eportfolio/userbios/$id/bio.pdf")) {
            send_file_to_client(str_replace('\\', '/', $webDir)."/courses/eportfolio/userbios/$id/bio.pdf", 'bio.pdf', null, true);
        }
    }

    $head_content .= "<script type='text/javascript'>
    $(document).ready(function() {
        /* Check if we are in safari and fix Bootstrap Affix*/
        if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
        var stickywidget = $('#floatMenu');
        var explicitlySetAffixPosition = function() {
            stickywidget.css('left',stickywidget.offset().left+'px');
        };
        /* Before the element becomes affixed, add left CSS that is equal to the distance of the element from the left of the screen */
        stickywidget.on('affix.bs.affix',function(){
            stickywidget.removeAttr('style');
            explicitlySetAffixPosition();
        });
        stickywidget.on('affixed-bottom.bs.affix',function(){
            stickywidget.css('left', 'auto');
        });
        /* On resize of window, un-affix affixed widget to measure where it should be located, set the left CSS accordingly, re-affix it */
        $(window).resize(function(){
            if(stickywidget.hasClass('affix')) {
                stickywidget.removeClass('affix');
                explicitlySetAffixPosition();
                stickywidget.addClass('affix');
            }
        });
    }
    </script>";

    $head_content .= "
        <script>
        $(function() {
            $('body').scrollspy({ target: '#affixedSideNav' });
        });
        </script>
    ";

    $head_content .= "
        <script>
        $(function() {
            $('#floatMenu').affix({
              offset: {
                top: 230,
                bottom: function () {
                  return (this.bottom = $('.footer').outerHeight(true))
                }
              }
            })
        });
        </script>";

    $ret_str = render_eportfolio_fields_content($id);

    if ($ret_str['panels'] == ""){
        $tool_content .= "
                        
                            <div class='col-sm-12'>
                                <div class='card panelCard h-100'>
                                    <div class='card-body text-center'>".$langNoInfoAvailable."</div>
                                </div>";
    } else {
        $tool_content .= "
                            <div class='col-sm-12'>
                            <div class='row row-cols-1 g-4'>".$ret_str['panels']."</div>";
    }


    if ($userdata->eportfolio_enable == 1 AND $ret_str['panels'] != "") {
        $social_share = "<div class='float-end mt-4'>".print_sharing_links($urlServer."main/eportfolio/index.php?id=$id&token=$token", $langUserePortfolio)."</div>";
    } else {
        $social_share = '';
    }

    $tool_content .= $clipboard_link;
    $tool_content .= "$social_share</div>";
    $tool_content .= $ret_str['right_menu'];
    $tool_content .= "";
}
if ($uid == $id) {
    draw($tool_content, 1, null, $head_content);
} else {
    draw($tool_content, 0, null, $head_content);
}
