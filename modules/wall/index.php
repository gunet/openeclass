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

$require_current_course = TRUE;

require_once '../../include/baseTheme.php';
require_once 'modules/wall/wall_functions.php';

$head_content .= '<link rel="stylesheet" type="text/css" href="css/wall.css">';

if (isset($_POST['submit'])) {
    if ($is_editor || allow_to_post($course_id, $uid)) {
        if ($_POST['type'] == 'text') {
            if (!empty($_POST['message'])) {
                Database::get()->query("INSERT INTO wall_post (course_id, user_id, content, timestamp) VALUES (?d,?d,?s,UNIX_TIMESTAMP())",
                    $course_id, $uid, links_autodetection($_POST['message']));
                Session::Messages($langWallPostSaved, 'alert-success');
            } else {
                Session::Messages($langWallMessageEmpty);
            }
        } elseif ($_POST['type'] == 'video') {
            
        }
        redirect_to_home_page("modules/wall/index.php?course=$course_code");
    }
}

if ($is_editor || allow_to_post($course_id, $uid)) {
    $head_content .= "<script>
                          $(function() {
                              $('#hidden_input').hide();
                              $('#type_input').change(function(){
                                  if($('#type_input').val() == 'video') {
                                      $('#hidden_input').show(); 
                                  } else {
                                      $('#hidden_input').hide(); 
                                  } 
                              });
                          });
            
                          $(function() {
                              $('#wall_form').submit(function() {
                                  if($('#type_input').val() != 'video') {
                                      $('#video_link').remove();
                                  }
                              });
                          })
                      </script>";
    
    $tool_content .= '<div class="row">
        <div class="col-sm-12">
            <div class="form-wrapper">
                <form id="wall_form" method="post" action="" enctype="multipart/form-data">
                    <fieldset> 
                        <div class="form-group">
                            <label for="message_input">'.$langMessage.'</label>
                            <textarea class="form-control" rows="6" name="message" id="message_input"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="type_input">'.$langType.'</label>
                            <select class="form-control" name="type" id="type_input">
                                <option value="text">'.$langWallText.'</option>
                                <option value="video">'.$langWallVideo.'</option>
                            </select>
                        </div>
                        <div class="form-group" id="hidden_input">
                            <label for="video_link">'.$langWallVideoLink.'</label>
                            <input class="form-control" type="url" name="video" id="video_link">
                        </div>                
                    </fieldset>
                    <div class="form-group">'.
                        form_buttons(array(
                            array(
                                'text'  =>  $langSubmit,
                                'name'  =>  'submit',
                                'value' =>  $langSubmit
                            )
                        ))
                  .'</div>        
                </form>
            </div>
        </div>
    </div>';
}


draw($tool_content, 2, null, $head_content);