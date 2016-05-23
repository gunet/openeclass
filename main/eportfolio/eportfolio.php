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
include 'main/eportfolio/eportfolio_functions.php';
require_once 'modules/sharing/sharing.php';

if (!get_config('eportfolio_enable')) {
    $tool_content = "<div class='alert alert-danger'>$langePortfolioDisabled</div>";
    draw($tool_content, 1);
    exit;
}

$userdata = array();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $toolName = $langUserePortfolio;
} else {
    $id = $uid;
    $toolName = $langMyePortfolio;
}

$userdata = Database::get()->querySingle("SELECT surname, givenname, username, has_icon, eportfolio_enable
                                          FROM user WHERE id = ?d", $id);

if ($userdata) {
    if ($uid == $id) {
        
        if (isset($_POST['toggle_val'])) {
            if ($_POST['toggle_val'] == 'on') {
                Database::get()->query("UPDATE user SET eportfolio_enable = ?d WHERE id = ?d", 1, $id);
                $userdata->eportfolio_enable = 1;
            } elseif ($_POST['toggle_val'] == 'off') {
                Database::get()->query("UPDATE user SET eportfolio_enable = ?d WHERE id = ?d", 0, $id);
                $userdata->eportfolio_enable = 0;
            }
        }
        
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
        } elseif ($userdata->eportfolio_enable == 1) {
            $off_class = "btn btn-default locked_inactive";
            $on_class = "btn btn-info unlocked_active";
        }
        
        $tool_content .= '<div class="btn-group" id="toggle_event_editing">
                              <form method="post" action="" id="enable-eportfolio-form">
                                  <input type="hidden" name="toggle_val">
                              </form>
	                          <button type="button" class="'.$off_class.'">OFF</button>
	                          <button type="button" class="'.$on_class.'">ON</button>
                          </div>';
        
        $tool_content .= 
            action_bar(array(
                array('title' => $langEditePortfolio,
                    'url' => "edit_eportfolio.php",
                    'icon' => 'fa-edit',
                    'level' => 'primary-label'),
                array('title' => $langUploadBio,
                    'url' => "bio_upload.php",
                    'icon' => 'fa-upload',
                    'level' => 'primary-label')
                ));    
    } else {
        if ($userdata->eportfolio_enable == 0) {
            $tool_content = "<div class='alert alert-danger'>$langUserePortfolioDisabled</div>";
            draw($tool_content, 1);
            exit;
        }
        
        if (file_exists("$webDir/courses/userbios/$id"."_bio.pdf")) {
            $tool_content .=
                action_bar(array(
                    array('title' => $langBio,
                    'url' => "{$urlAppend}courses/userbios/$id"."_bio.pdf",
                    'icon' => 'fa-download',
                    'level' => 'primary-label')
                ));
        }
    }
    
    $tool_content .= "
        <div class='row'>
            <div class='col-sm-12'>
                <div class='panel panel-default'>
                <div class='panel-body'>
                    <div id='pers_info' class='row'>
                        <div class='col-xs-12 col-sm-2'>
                            <div id='profile-avatar'>" . profile_image($id, IMAGESIZE_LARGE, 'img-responsive img-circle') . "</div>
                        </div>
                        <div class='col-xs-12 col-sm-10 profile-pers-info'>
                            <div class='row profile-pers-info-name'>
                                <div class='col-xs-12'>
                                    <div>" . q("$userdata->givenname $userdata->surname") . "</div>
                                    <div class='not_visible'>(".q($userdata->username).")</div>
                                </div>
                            </div>
                        </div>";
    
    $tool_content .= render_eportfolio_fields_content($id);
    if ($userdata->eportfolio_enable == 1) {
        $social_share = "<div class='pull-right'>".print_sharing_links($urlServer."main/eportfolio.php?id=".$id, $langUserePortfolio)."</div>";
    } else {
        $social_share = '';
    }
    $tool_content .= "</div>
                $social_share
            </div>
        </div>
    </div>
</div>";
}
if ($uid == $id) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 2);
}
