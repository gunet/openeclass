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

require_once 'include/course_settings.php';

/**
 * Needed javascript for abuse report to work
 * @return string
 */
function abuse_report_add_js() {
    global $urlServer, $langError;
    static $loaded;
    
    if ($loaded) {
        return;
    }
     
    $loaded = true;
    
    return '<script>
              $(function() {
                $(".modal-footer").on("click", "button.btn-primary", function(event){
                  var id = $(this).attr("id");
                  var sub_id = id.substr(13);
                  var splitted_id = sub_id.split("_");
                  if(splitted_id[0] == "forum") {
                    var rtype = splitted_id[0]+"_"+splitted_id[1];
                    var rid = splitted_id[2];
                  } else {
                    var rtype = splitted_id[0];
                    var rid = splitted_id[1];
                  }
                  $.ajax({
                    type: "POST",
                    url: "'.$urlServer.'modules/abuse_report/process_report.php",
                    data: $("form#abuse_form_"+rtype+"_"+rid).serialize(),
                    dataType: "json",
                    success: function(data){
                      $("#abuse_modal_body_"+rtype+"_"+rid).html(data[1]);
                      if (data[0] != "fail") {
                        $("#abuse_submit_"+rtype+"_"+rid).hide();
                      }
                  },
                  error: function(){
                      alert("'.$langError.'");
                  }
                  });
                });
              });
          </script>';
}

/**
 * Inject code for report flag icon
 * @param string $rtype
 * @param int $rid
 * @param int $course_id
 * @return string html flag icon
 */
function abuse_report_icon_flag ($rtype, $rid, $course_id) {
    global $head_content, $langAbuseReport, $langClose, $langSend, 
    $langAbuseReportCat, $langMessage, $langSpam, $langRudeness, $langOther;
    
    $out = '<a href="javascript:void(0);" data-toggle="modal" data-target="#abuse_modal_'.$rtype.'_'.$rid.'"><span class="fa fa-flag-o pull-right" data-original-title="'.$langAbuseReport.'" title="" data-toggle="tooltip"></span></a>';
    $out .= '<div class="modal fade" id="abuse_modal_'.$rtype.'_'.$rid.'" tabindex="-1" role="dialog" aria-labelledby="abuse_modal_label_'.$rtype.'_'.$rid.'" aria-hidden="true">
                 <div class="modal-dialog">
                     <div class="modal-content">
                         <div class="modal-header">
                             <button type="button" class="close" onClick="$(\'#abuse_modal_'.$rtype.'_'.$rid.'\').modal(\'hide\');" aria-label="'.$langClose.'"><span aria-hidden="true">&times;</span></button>
                             <h4 class="modal-title" id="abuse_modal_label_'.$rtype.'_'.$rid.'">'.$langAbuseReport.'</h4>
                         </div>
                         <div class="modal-body" id="abuse_modal_body_'.$rtype.'_'.$rid.'">
	                         <form id="abuse_form_'.$rtype.'_'.$rid.'">
		                         <fieldset>
                                      <div class="form-group">
                                          <label for="abuse_form_select'.$rtype.'_'.$rid.'">'.$langAbuseReportCat.'</label>
                                          <select class="form-control" name="abuse_report_reason" id="abuse_form_select'.$rtype.'_'.$rid.'">
                                              <option value="rudeness">'.$langRudeness.'</option>
                                              <option value="spam">'.$langSpam.'</option>
                                              <option value="other">'.$langOther.'</option>    
                                          </select>
                                      </div>
                                      <div class="form-group">
                                          <label for="abuse_form_txt'.$rtype.'_'.$rid.'">'.$langMessage.'</label>
                                          <textarea class="form-control" name="abuse_report_msg" id="abuse_form_txt'.$rtype.'_'.$rid.'"></textarea>
                                      </div>
                                      <input type="hidden" name="rtype" value="'.$rtype.'">
                                      <input type="hidden" name="rid" value="'.$rid.'">
                                      <input type="hidden" name="cid" value="'.$course_id.'">                                                                                         
		                         </fieldset>
		                     </form>
                         </div>
                         <div class="modal-footer">
                             <button type="button" class="btn btn-default" onClick="$(\'#abuse_modal_'.$rtype.'_'.$rid.'\').modal(\'hide\');">'.$langClose.'</button>
                             <button type="button" class="btn btn-primary" id="abuse_submit_'.$rtype.'_'.$rid.'">'.$langSend.'</button>
                         </div>
                     </div>
                 </div>
             </div>';
    
    return $out;
}

/**
 * Inject code for report flag option in action button
 * @param string $rtype
 * @param int $rid
 * @param int $course_id
 * @return array: [0] -> array option in action_button for report flag [1] -> html for modal
 */
function abuse_report_action_button_flag ($rtype, $rid, $course_id) {
    global $head_content, $langAbuseReport, $urlServer, $langClose, $langSend, $langError,
    $langAbuseReportCat, $langMessage, $langSpam, $langRudeness, $langOther;
    
    $ret = array();
    
    $ret[] = array('title' => $langAbuseReport,
            'url' => "javascript:void(0);",
            'icon' => 'fa-flag-o',
            'link-attrs' => "data-toggle='modal' data-target='#abuse_modal_".$rtype."_".$rid."'");
    
    $ret[] = '<div class="modal fade" id="abuse_modal_'.$rtype.'_'.$rid.'" tabindex="-1" role="dialog" aria-labelledby="abuse_modal_label_'.$rtype.'_'.$rid.'" aria-hidden="true">
                 <div class="modal-dialog">
                     <div class="modal-content">
                         <div class="modal-header">
                             <button type="button" class="close" data-dismiss="modal" aria-label="'.$langClose.'"><span aria-hidden="true">&times;</span></button>
                             <h4 class="modal-title" id="abuse_modal_label_'.$rtype.'_'.$rid.'">'.$langAbuseReport.'</h4>
                         </div>
                         <div class="modal-body" id="abuse_modal_body_'.$rtype.'_'.$rid.'">
	                         <form id="abuse_form_'.$rtype.'_'.$rid.'">
		                         <fieldset>
                                      <div class="form-group">
                                          <label for="abuse_form_select'.$rtype.'_'.$rid.'">'.$langAbuseReportCat.'</label>
                                          <select class="form-control" name="abuse_report_reason" id="abuse_form_select'.$rtype.'_'.$rid.'">
                                              <option value="rudeness">'.$langRudeness.'</option>
                                              <option value="spam">'.$langSpam.'</option>
                                              <option value="other">'.$langOther.'</option>
                                          </select>
                                      </div>
                                      <div class="form-group">
                                          <label for="abuse_form_txt'.$rtype.'_'.$rid.'">'.$langMessage.'</label>
                                          <textarea class="form-control" name="abuse_report_msg" id="abuse_form_txt'.$rtype.'_'.$rid.'"></textarea>
                                      </div>
                                      <input type="hidden" name="rtype" value="'.$rtype.'">
                                      <input type="hidden" name="rid" value="'.$rid.'">
                                      <input type="hidden" name="cid" value="'.$course_id.'">
		                         </fieldset>
		                     </form>
                         </div>
                         <div class="modal-footer">
                             <button type="button" class="btn btn-default" data-dismiss="modal">'.$langClose.'</button>
                             <button type="button" class="btn btn-primary" id="abuse_submit_'.$rtype.'_'.$rid.'">'.$langSend.'</button>
                         </div>
                     </div>
                 </div>
             </div>';
    
    return $ret;
}

/**
 * Check if flag should be shown or not
 * @param string $rtype
 * @param int $rid
 * @param int $course_id
 * @param int $uid
 * @param boolean $is_editor
 * @return boolean
 */
function abuse_report_show_flag ($rtype, $rid, $course_id, $is_editor) {
    
    global $uid;
    if ($uid == 0) { //do not show for not logged in users
        return false;
    }

    if (setting_get(SETTING_COURSE_ABUSE_REPORT_ENABLE, $course_id) != 1) { // abuse report disabled for course
        return false;
    } elseif ($is_editor) { //do not show for editor
        return false;
    } else {
        //check if there is already an open report for this resource from this user
        $result = Database::get()->querySingle("SELECT COUNT(`id`) AS c FROM `abuse_report` WHERE `rtype` = ?s 
                AND `rid` = ?d AND `user_id` = ?d AND `status` = ?d", $rtype, $rid, $_SESSION['uid'], 1);
        if ($result->c != 0) {
            return false;
        }
        
        //check for each resource type if resource exists and user is author
        if ($rtype == 'comment') {
            $result = Database::get()->querySingle("SELECT `user_id` FROM `comments` WHERE `id` = ?d", $rid);
            if ($result) {
                if ($result->user_id == $_SESSION['uid']) {
                    return false;
                }
            } else {
                return false;
            }
        } elseif ($rtype == 'forum_post') {
            $result = Database::get()->querySingle("SELECT `poster_id` FROM `forum_post` WHERE `id` = ?d", $rid);
            if ($result) {
                if ($result->poster_id == $_SESSION['uid']) {
                    return false;
                }
            } else {
                return false;
            }
        } elseif ($rtype == 'link') {
            $result = Database::get()->querySingle("SELECT `user_id` FROM `link` WHERE `id` = ?d", $rid);
            if ($result) {
                if ($result->user_id == $_SESSION['uid']) {
                    return false;
                }
            } else {
                return false;
            }
        } elseif ($rtype == 'wallpost') {
            $result = Database::get()->querySingle("SELECT `user_id` FROM `wall_post` WHERE `id` = ?d", $rid);
            if ($result) {
                if ($result->user_id == $_SESSION['uid']) {
                    return false;
                }
            } else {
                return false;
            } 
        } else { //unknown rtype
            return false;
        }
    }
    
    return true;
}
