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

include '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';
require_once 'main/eportfolio/eportfolio_functions.php';
require_once 'modules/sharing/sharing.php';

if (!get_config('eportfolio_enable')) {
    $tool_content = "<div class='alert alert-danger'>$langePortfolioDisabled</div>";
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
            $tool_content .= "<div class='col-12'><div class='alert alert-warning'>$langePortfolioDisableWarning</div></div>";
        } elseif ($userdata->eportfolio_enable == 1) {
            load_js('clipboard.js');
            $clipboard_link = "
                            <div class='card mt-3'>
                                <div class='card-header d-flex justify-content-between aling-items-center'>
                                    <span class='normalBlueText TextSemiBold fs-6 mt-1'>$langPageLink</span>
                                    <button class='btn submitAdminBtn' id='copy-btn' data-bs-toggle='tooltip' data-bs-placement='bottom' data-clipboard-target='#page-link'>
                                        <span class='fa fa-clipboard'></span>&nbsp;&nbsp;&nbsp;$langCopy
                                    </button>
                                </div>
                                <div class='card-body'>
                                    <input class='form-control' id='page-link' value='{$urlServer}main/eportfolio/index.php?id=$id&token=$token'>
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
                                        array('title' => $langBio,
                                            'url' => "{$urlAppend}main/eportfolio/index.php?action=get_bio&amp;id=$id&amp;token=$token",
                                            'icon' => 'fa-download',
                                            'level' => 'primary-label',
                                            'show' => file_exists("$webDir/courses/eportfolio/userbios/$id/bio.pdf")),
                                        array('title' => $langResume,
                                            'url' => "{$urlAppend}main/eportfolio/index.php?id=$id&amp;token=$token",
                                            'level' => 'primary-label',
                                            'button-class' => 'btn-primary'),
                                        array('title' => $userdata->eportfolio_enable ? $langViewHide : $langViewShow,
                                              'url' => $userdata->eportfolio_enable ? "{$urlAppend}main/eportfolio/index.php?id=$id&amp;token=$token&amp;toggle_val=off" : "{$urlAppend}main/eportfolio/index.php?id=$id&amp;token=$token&amp;toggle_val=on",
                                              'icon' => $userdata->eportfolio_enable ? 'fa-eye-slash' : 'fa-eye'),
                                        array('title' => $langResourcesCollection,
                                            'url' => "{$urlAppend}main/eportfolio/resources.php?id=$id&amp;token=$token",
                                            'level' => 'primary-label'),
                                        array('title' => $langEditResume,
                                            'url' => "{$urlAppend}main/eportfolio/edit_eportfolio.php",
                                            'icon' => 'fa-edit'),
                                        array('title' => $langUploadBio,
                                            'url' => "{$urlAppend}main/eportfolio/bio_upload.php",
                                            'icon' => 'fa-upload')
                                    ));    
    } else {
        if ($userdata->eportfolio_enable == 0) {
            $tool_content = "<div class='col-12'><div class='alert alert-danger'>$langUserePortfolioDisabled</div></div>";
            if ($session->status == 0) {
                draw($tool_content, 0);
            } else {
                draw($tool_content, 1);
            }
            exit;
        }
        
        $tool_content .= action_bar(array(
                                        array('title' => $langBio,
                                            'url' => "{$urlAppend}main/eportfolio/index.php?action=get_bio&amp;id=$id&amp;token=$token",
                                            'icon' => 'fa-download',
                                            'level' => 'primary-label',
                                            'show' => file_exists("$webDir/courses/eportfolio/userbios/$id/bio.pdf")),
                                        array('title' => $langResume,
                                            'url' => "{$urlAppend}main/eportfolio/index.php?id=$id&amp;token=$token",
                                            'level' => 'primary-label',
                                            'button-class' => 'btn-primary'),
                                        array('title' => $langResourcesCollection,
                                              'url' => "{$urlAppend}main/eportfolio/resources.php?id=$id&amp;token=$token",
                                              'level' => 'primary-label'),
                                    ));
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
    });
    </script>";
    
    $ret_str = render_eportfolio_fields_content($id);

    if ($ret_str['panels'] == ""){
        $tool_content .= "
                        
                            <div class='col-sm-12'>
                                <div class='panel panel-default'>
                                    <div class='text-center text-muted panel-body'>".$langNoInfoAvailable."</div>
                                </div>";
    } else {
        $tool_content .= "
                            <div class='col-sm-12'>
                            <div class='row row-cols-1 row-cols-md-2 g-4'>".$ret_str['panels']."</div>";
    }

    
    if ($userdata->eportfolio_enable == 1 AND $ret_str['panels'] != "") {
        $social_share = "<div class='float-end mt-3 shadow-sm p-3 rounded bg-light'>".print_sharing_links($urlServer."main/eportfolio/index.php?id=$id&token=$token", $langUserePortfolio)."</div>";
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
