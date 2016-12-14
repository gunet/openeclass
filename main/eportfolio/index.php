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

if ($userdata) {
    
    if ($uid == $id) {

        /*
        if (isset($_POST['toggle_val'])) {
            if ($_POST['toggle_val'] == 'on') {
                Database::get()->query("UPDATE user SET eportfolio_enable = ?d WHERE id = ?d", 1, $id);
                $userdata->eportfolio_enable = 1;
            } elseif ($_POST['toggle_val'] == 'off') {
                Database::get()->query("UPDATE user SET eportfolio_enable = ?d WHERE id = ?d", 0, $id);
                $userdata->eportfolio_enable = 0;
            }
            redirect_to_home_page("main/eportfolio/index.php?id=$id&token=$token");
        }
        */
        
        $head_content .= "<script type='text/javascript'>//<![CDATA[
                              $(function(){
                                  $('#toggle_event_editing button').click(function(){
	                                  if($(this).hasClass('locked_active') || $(this).hasClass('unlocked_inactive')){
		                                  /* code to do when unlocking */
                                          $('#enable-eportfolio-form input').val('on');
                                          $('#enable-eportfolio-form').submit();
	                                  }else{
		                                  /* code to do when locking */
                                          $('#enable-eportfolio-form input').val('off');
                                          $('#enable-eportfolio-form').submit();
	                                  }
	
	                                  /* reverse locking status */
	                                  $('#toggle_event_editing button').eq(0).toggleClass('locked_inactive locked_active btn-default btn-info');
	                                  $('#toggle_event_editing button').eq(1).toggleClass('unlocked_inactive unlocked_active btn-info btn-default');
                                  });
                              });//]]> 
                          </script>";
        
        if ($userdata->eportfolio_enable == 0) {
            $off_class = "btn btn-info locked_active";
            $on_class = "btn btn-default unlocked_inactive";
            $tool_content .= "<div class='alert alert-warning'>$langePortfolioDisableWarning</div>";
        } elseif ($userdata->eportfolio_enable == 1) {
            $off_class = "btn btn-default locked_inactive";
            $on_class = "btn btn-info unlocked_active";
        }

        /*
        $tool_content .= '<div style="margin-top:10px" class="btn-group" id="toggle_event_editing">
                              <form method="post" action="" id="enable-eportfolio-form">
                                  <input type="hidden" name="toggle_val">
                              </form>
                              <b>'.$langEnabledePortfolioButtonsLabel.'</b><br/>
	                          <button type="button" class="'.$off_class.'">OFF</button>
	                          <button type="button" class="'.$on_class.'">ON</button>
                          </div>';
        */

        
        $tool_content .= action_bar(array(
                                        array('title' => $langBio,
                                            'url' => "{$urlAppend}main/eportfolio/index.php?action=get_bio&amp;id=$id&amp;token=$token",
                                            'icon' => 'fa-download',
                                            'level' => 'primary-label',
                                            'show' => file_exists("$webDir/courses/eportfolio/userbios/$id/bio.pdf")),
                                        array('title' => $langResume,
                                            'url' => "{$urlAppend}main/eportfolio/index.php?id=$id&amp;token=$token",
                                            'level' => 'primary-label',
                                            'button-class' => 'btn-info'),
                                        array('title' => $userdata->eportfolio_enable ? $langViewHide : $langViewShow,
                                              'url' => "#",
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
            $tool_content = "<div class='alert alert-danger'>$langUserePortfolioDisabled</div>";
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
                                            'button-class' => 'btn-info'),
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
    
    $tool_content .= "
        <div class='row'>
            <div class='col-sm-12'>
                   ";

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
    
    $tool_content .= "<div class='row'>
                        <div class='col-sm-9'>";
    $tool_content .= $ret_str['panels'];
    $tool_content .= "</div>";
    $tool_content .= $ret_str['right_menu'];
    $tool_content .= "</div>";
    if ($userdata->eportfolio_enable == 1) {
        $social_share = "<div class='pull-right'>".print_sharing_links($urlServer."main/index.php?id=$id&token=$token", $langUserePortfolio)."</div>";
    } else {
        $social_share = '';
    }
    $tool_content .= "$social_share</div>
            </div>";
}
if ($uid == $id) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 0);
}
