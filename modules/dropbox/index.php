<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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

$require_login = TRUE;
if(isset($_GET['course'])) {//course messages
    $require_current_course = TRUE;
} else {//personal messages
    $require_current_course = FALSE;
}
$guest_allowed = FALSE;
$require_help = TRUE;
$helpTopic = 'Dropbox';

include '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';

$personal_msgs_allowed = get_config('dropbox_allow_personal_messages');

if (!isset($course_id)) {
    $course_id = 0;
}

if ($course_id != 0) {
    $dropbox_dir = $webDir . "/courses/" . $course_code . "/dropbox";
    if (!is_dir($dropbox_dir)) {
        mkdir($dropbox_dir);
    }
    
    // get dropbox quotas from database
    $d = Database::get()->querySingle("SELECT dropbox_quota FROM course WHERE code = ?s", $course_code);
    $diskQuotaDropbox = $d->dropbox_quota;
    $diskUsed = dir_total_space($dropbox_dir);
}

// javascript functions
$head_content = '<script type="text/javascript">
                    function checkForm (frm) {
                        if (frm.elements["recipients[]"].selectedIndex < 0) {
                                alert("' . $langNoUserSelected . '");
                                return false;
                        } else {
                                return true;
                        }
                    }
                </script>';

$nameTools = $langDropBox;

if ($course_id != 0) {
    $tool_content .="
    <div id='operations_container'>
      <ul id='opslist'>
        <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;upload=1&amp;type=cm'>$langNewCourseMessage</a></li>
        <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;showQuota=TRUE'>$langQuotaBar</a></li>
      </ul>
    </div>";
} else {
    $tool_content .="
    <div id='operations_container'>
      <ul id='opslist'>";
    if ($personal_msgs_allowed) {
        $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?upload=1'>$langNewPersoMessage</a></li>";
    }
    $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?upload=1&amp;type=cm'>$langNewCourseMessage</a></li>
      </ul>
    </div>";
}

if (isset($_GET['course']) and isset($_GET['showQuota']) and $_GET['showQuota'] == TRUE) {
    $nameTools = $langQuotaBar;
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langDropBox);
    $space_released = 0;
    if ($is_editor && ($diskUsed/$diskQuotaDropbox >= 0.9)) { 
        $space_to_free = ($diskQuotaDropbox/1024/1024/10);
        
        if (isset($_GET['free']) && $_GET['free'] == TRUE) { //free some space
            $sql = "SELECT da.filename, da.id, da.filesize FROM dropbox_attachment as da, dropbox_msg as dm
                    WHERE da.msg_id = dm.id
                    AND dm.course_id = ?d
                    ORDER BY dm.timestamp ASC";   
            $result = Database::get()->queryArray($sql, $course_id); 
            foreach ($result as $file) {
                unlink($dropbox_dir . "/" . $file->filename);
                $space_released += $file->filesize;
                Database::get()->query("DELETE FROM dropbox_attachment WHERE id = ?d", $file->id);
                if ($space_released >= $diskQuotaDropbox/10) {
                    break;
                }
            }
            $tool_content .= "<p class='success'>".sprintf($langDropboxFreeSpaceSuccess, $space_released/1024/1024)."</p>";
        } else { //provide option to free some space
            $tool_content .= "<div id='operations_container'>
                                <ul id='opslist'>
                                  <li><a onclick=\"return confirm('".sprintf($langDropboxFreeSpaceConfirm, $space_to_free)."');\" href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;showQuota=TRUE&amp;free=TRUE'>".sprintf($langDropboxFreeSpace, $space_to_free)."</a></li>
                                </ul>
                              </div>";
        }
    }
    
    $tool_content .= showquota($diskQuotaDropbox, $diskUsed-$space_released);
    
    draw($tool_content, 2);
    exit;
}

if (isset($_REQUEST['upload']) && $_REQUEST['upload'] == 1) {//new message form
    if ($course_id == 0) {
        if (isset($_GET['type']) && $_GET['type'] == 'cm') {
            $type = 'cm';
        } else {
            $type = 'pm';
        }
    }
    
    if ($course_id == 0 && $type == 'pm') {
        if (!$personal_msgs_allowed) {
            $tool_content .= "<p class='alert1'>$langGeneralError</p>";
            draw($tool_content, 1, null, $head_content);
            exit;
        }
        $tool_content .= "<form id='newmsg' method='post' action='dropbox_submit.php' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
    } elseif ($course_id == 0 && $type == 'cm') {
        $tool_content .= "<form method='post' action='dropbox_submit.php' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
    } else {
        $type = 'cm'; //only course messages are allowed in the context of a course
        $tool_content .= "<form method='post' action='dropbox_submit.php?course=$course_code' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
    }
    $tool_content .= "
	<fieldset>
	<table width='100%' class='tbl'>
        <tr>
	  <th>$langSender:</th>
	  <td>" . q(uid_to_name($uid)) . "</td>
	</tr>";
    if ($type == 'cm' && $course_id == 0) {//course message from central interface
        //find user's courses
        $sql = "SELECT course.code code, course.title title
                FROM course, course_user
                WHERE course.id = course_user.course_id
                AND course_user.user_id = ?d
                ORDER BY title";
        $res = Database::get()->queryArray($sql, $uid);
        
        $head_content .= "<script type='text/javascript'>
                            $(document).on('change','#courseselect',function(){
                              $.ajax({
                                type: 'POST',
                                dataType: 'json',
                                url: 'load_recipients.php',
                                data: {'course' : $('#courseselect').val() }
                              }).done(function(data) {
                                $('#select-recipients').empty();
                                if(!($.isEmptyObject(data))) {
                                  $('#select-recipients').empty();
                                  $.each(data, function(key,value){
                                    if (key.charAt(0) == '_') {
                                      $('#select-recipients').prepend('<option value=\'' + key + '\'>' + value + '</option>');
                                    } else {
                                      $('#select-recipients').append('<option value=\'' + key + '\'>' + value + '</option>');
                                    }
                                  });
                                }
                                $('#select-recipients').select2('destroy');
                                $('#select-recipients').select2();
                              });
                            });
                          </script>";
        
        $tool_content .= "<tr>
            <th width='120'>".$langCourse.":</th>
              <td>
                <select id='courseselect' name='course'>
                  <option value='-1'>&nbsp;</option>";
        foreach ($res as $course) {    
            $tool_content .="<option value='".$course->code."'>".q($course->title)."</option>";
        }
        $tool_content .="    </select>
                           </td>
                         </tr>";
    }
    $tool_content .= "<tr>
        <th width='120'>" . $langTitle . ":</th>
        <td><input type='text' name='message_title' size='50'/>	      
        </td>
        </tr>";
    $tool_content .= "<tr>
              <th>" . $langMessage . ":</th>
              <td>".rich_text_editor('body', 4, 20, '')."
              <small>&nbsp;&nbsp;$langMaxMessageSize</small></td>           
            </tr>";
    if ($course_id != 0 || ($type == 'cm' && $course_id == 0)) {
        $tool_content .= "<tr>
	      <th width='120'>$langFileName:</th>
	      <td><input type='file' name='file' size='35' />	     
	      </td>
	    </tr>";
    }
    
    if ($course_id != 0 || ($type == 'cm' && $course_id == 0)){
    	$tool_content .= "<tr>
    	  <th>$langSendTo:</th>
    	  <td>
    	<select name='recipients[]' multiple='multiple' class='form-control' id='select-recipients'>";
    
        if ($course_id != 0) {//course messages
            
            $student_to_student_allow = get_config('dropbox_allow_student_to_student');
            
            if ($is_editor || $student_to_student_allow == 1) {
                //select all users from this course except yourself
                $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name
                        FROM user u, course_user cu
        			    WHERE cu.course_id = ?d
                        AND cu.user_id = u.id
                        AND cu.status != ?d
                        AND u.id != ?d
                        ORDER BY UPPER(u.surname), UPPER(u.givenname)";
                
                $res = Database::get()->queryArray($sql, $course_id, USER_GUEST, $uid);
                
                if ($is_editor) {
                    $sql_g = "SELECT id, name FROM `group` WHERE course_id = ?d";
                    $result_g = Database::get()->queryArray($sql_g, $course_id);
                } else {//allow students to send messages only to groups they are members of
                    $sql_g = "SELECT `g`.id, `g`.name FROM `group` as `g`, `group_members` as `gm` 
                              WHERE `g`.id = `gm`.group_id AND `g`.course_id = ?d AND `gm`.user_id = ?d";
                    $result_g = Database::get()->queryArray($sql_g, $course_id, $uid);            
                }
                
                foreach ($result_g as $res_g)
                {
                    $tool_content .= "<option value = '_$res_g->id'>".q($res_g->name)."</option>";
                }
            } else {
                //if user is student and student-student messages not allowed for course messages show teachers
                $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name
                        FROM user u, course_user cu
        			    WHERE cu.course_id = ?d
                        AND cu.user_id = u.id
                        AND (cu.status = ?d OR cu.editor = ?d)
                        AND u.id != ?d
                        ORDER BY UPPER(u.surname), UPPER(u.givenname)";
                
                $res = Database::get()->queryArray($sql, $course_id, USER_TEACHER, 1, $uid);
                
                //check if user is group tutor
                 $sql_g = "SELECT `g`.id, `g`.name FROM `group` as `g`, `group_members` as `gm`
                WHERE `g`.id = `gm`.group_id AND `g`.course_id = ?d AND `gm`.user_id = ?d AND `gm`.is_tutor = ?d";
                
                $result_g = Database::get()->queryArray($sql_g, $course_id, $uid, 1);
                foreach ($result_g as $res_g)
                {
                    $tool_content .= "<option value = '_$res_g->id'>".q($res_g->name)."</option>";
                }
                
                //find user's group and their tutors
                $tutors = array();
                $sql_g = "SELECT `group`.id FROM `group`, group_members
                          WHERE `group`.course_id = ?d 
                          AND `group`.id = group_members.group_id 
                          AND `group_members`.user_id = ?d";
                $result_g = Database::get()->queryArray($sql_g, $course_id, $uid);
                foreach ($result_g as $res_g) {
                    $sql_gt = "SELECT u.id, CONCAT(u.surname,' ', u.givenname) AS name
                               FROM user u, group_members g
                               WHERE g.group_id = ?d 
                               AND g.is_tutor = ?d 
                               AND g.user_id = u.id 
                               AND u.id != ?d";
                    $res_gt = Database::get()->queryArray($sql_gt, $res_g->id, 1, $uid);
                    foreach ($res_gt as $t) {
                        $tutors[$t->id] = $t->name; 
                    }
                }
            }
            
            foreach ($res as $r) {
                if (isset($tutors) && !empty($tutors)) {
                    if (isset($tutors[$r->user_id])) {
                        unset($tutors[$r->user_id]);
                    }
                }
                $tool_content .= "<option value=" . $r->user_id . ">" . q($r->name) . "</option>";
            }
            if (isset($tutors)) {
                foreach ($tutors as $key => $value) {
                    $tool_content .= "<option value=" . $key . ">" . q($value) . "</option>";
                }
            }
        } 
    
        $tool_content .= "</select><a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a></td></tr>";
    } elseif ($type == 'pm' && $course_id == 0) {//personal messages
        $head_content .= " <script type='text/javascript'>
                             var selected = [];
                             $(function() {
                               function split( val ) {
                                 return val.split( /,\s*/ );
                                }
                                function extractLast( term ) {
                                  return split( term ).pop();
                                }
                                $(\"#recipients\" )
                                // don't navigate away from the field on tab when selecting an item
                                .bind( \"keydown\", function( event ) {
                                  if ( event.keyCode === $.ui.keyCode.TAB && $( this ).data( \"ui-autocomplete\" ).menu.active ) {
                                    event.preventDefault();
                                  }
                                })
                                .autocomplete({
                                  source: function( request, response ) {
                                    $.getJSON( \"load_recipients.php?autocomplete=1\", {
                                      term: extractLast( request.term )
                                    }, response );
                                  },
                                  search: function() {
                                    // custom minLength
                                    var term = extractLast( this.value );
                                    if ( term.length < 3 ) {
                                      return false;
                                    }
                                  },
                                  focus: function() {
                                    // prevent value inserted on focus
                                    return false;
                                  },
                                  select: function( event, ui ) {
                                    var terms = split( this.value );
                                    // remove the current input
                                    terms.pop();
                                    // add the selected item
                                    terms.push( ui.item.label );
                                    // add placeholder to get the comma-and-space at the end
                                    terms.push( \"\" );
                                    this.value = terms.join( \", \" );
                                    //do not add a recipient already selected
                                    if ($.inArray(ui.item.value, selected) == -1) {
                                      $('#newmsg').append('<input type=\'hidden\' name=\'recipients[]\' value=\''+ui.item.value+'\'/>');
                                      selected.push(ui.item.value);
                                    }
                                    return false;
                                  }
                                });
                              });
                            </script>";
        
        $tool_content .= "<tr>
    	                    <th>$langSendTo:</th>
    	                    <td><input name='autocomplete' id='recipients' /><br/><em>$langSearchSurname</em></td>
                          </tr>";        
    }
    
	$tool_content .= "<tr>
	  <th>&nbsp;</th>
	  <td class='left'><input type='submit' name='submit' value='" . q($langSend) . "' />&nbsp;
	  $langMailToUsers<input type='checkbox' name='mailing' value='1' checked /></td>
	</tr>
        </table>
        </fieldset>	
        </form>
	<p class='right smaller'>$langMaxFileSize " . ini_get('upload_max_filesize') . "</p>";
    
	if ($course_id != 0 || ($type == 'cm' && $course_id == 0)){
        load_js('select2');
        $head_content .= "<script type='text/javascript'>
            $(document).ready(function () {
                $('#select-recipients').select2();       
                $('#selectAll').click(function(e) {
                    e.preventDefault();
                    var stringVal = [];
                    $('#select-recipients').find('option').each(function(){
                        stringVal.push($(this).val());
                    });
                    $('#select-recipients').val(stringVal).trigger('change');
                });
                $('#removeAll').click(function(e) {
                    e.preventDefault();
                    var stringVal = [];
                    $('#select-recipients').val(stringVal).trigger('change');
                });         
            });

            </script>
        ";
	}
} else {//mailbox
    load_js('jquery-ui');
    load_js('datatables');
    load_js('datatables_filtering_delay');
    load_js('datatables_reload');
    $head_content .= "<script type='text/javascript'>
		              $(function() {
		                $( \"#tabs\" ).tabs({
		                  collapsible: false,
                          //cache tab and avoid reload
                          beforeLoad: function( event, ui ) {
                            if ( ui.tab.data( \"loaded\" ) ) {
                              event.preventDefault();
                              return;
                            }
                            ui.jqXHR.success(function() {
                              ui.tab.data( \"loaded\", true );
                            });
                          },
                          //open links inside tabs
                          load: function(event, ui) {
                            //following line prevents double requests by unbinding click event on previously loaded tab content 
                            $('.ui-tabs-panel.ui-widget-content-new').off('click', 'a'); 
                            $('.ui-tabs-panel.ui-widget-content-new').on('click', 'a', function(event) {
                              if (event.target.className != 'outtabs' && event.target.className.indexOf('paginate_button') == -1) {
                                event.preventDefault();
                                $(this).closest('.ui-tabs-panel.ui-widget-content-new').load(this.href);
                              }
                            });
                          }
                         })
                        //remove some classes to avoid overriding of openeclass styling
                        $('#tabs').removeClass('ui-widget');
                        $('#tabs').removeClass('ui-widget-content');
                        $('#ui-tabs-1').removeClass('ui-widget-content');
                        $('#ui-tabs-2').removeClass('ui-widget-content');
                        //add classes needed for opening links inside tabs (see above)
                        $('#ui-tabs-1').addClass('ui-widget-content-new');
                        $('#ui-tabs-2').addClass('ui-widget-content-new');
                      })
                      </script>";
    if ($course_id == 0) {
        $tool_content .= "<div id=\"tabs\">
                           <ul>
                             <li><a href=\"inbox.php\">Inbox</a></li>
                             <li><a href=\"outbox.php\">Outbox</a></li>
                           </ul>
                         </div>";
    } else {
        $tool_content .= "<div id=\"tabs\">
                           <ul>
                             <li><a href=\"inbox.php?course=$course_code\">Inbox</a></li>
                             <li><a href=\"outbox.php?course=$course_code\">Outbox</a></li>
                           </ul>
                         </div>";
    }
}

if ($course_id == 0) {
    draw($tool_content, 1, null, $head_content);
} else {
    draw($tool_content, 2, null, $head_content);
}
