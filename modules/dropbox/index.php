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
    $tool_content .= showquota($diskQuotaDropbox, $diskUsed);
    draw($tool_content, 2);
    exit;
}

load_js('jquery');
load_js('jquery-ui');

if (isset($_REQUEST['upload']) && $_REQUEST['upload'] == 1) {//new message form
    if ($course_id == 0) {
        if (!$personal_msgs_allowed) {
            $tool_content .= "<p class='alert1'>$langGeneralError</p>";
            draw($tool_content, 1, null, $head_content);
            exit;
        }
        
        if (isset($_GET['type']) && $_GET['type'] == 'cm') {
            $type = 'cm';
        } else {
            $type = 'pm';
        }
        $tool_content .= "<form id='newmsg' method='post' action='dropbox_submit.php' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
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
                                $('#select-recipients').multiselect('refresh');
                              });
                            });
                          </script>";
        
        $tool_content .= "<tr>
            <th width='120'>".$langCourse.":</th>
              <td>
                <select id='courseselect' name='course'>
                  <option value='-1'>&nbsp;</option>";
        foreach ($res as $course) {    
            $tool_content .="<option value='".$course->code."'>$course->title</option>";
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
    	<select name='recipients[]' multiple='multiple' class='auth_input' id='select-recipients'>";
    
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
                //if user is student an student-student messages not allowed for course messages show teachers
                $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name
                        FROM user u, course_user cu
        			    WHERE cu.course_id = ?d
                        AND cu.user_id = u.id
                        AND cu.status = ?d
                        AND u.id != ?d
                        ORDER BY UPPER(u.surname), UPPER(u.givenname)";
                
                $res = Database::get()->queryArray($sql, $course_id, USER_TEACHER, $uid);
            }
            
            foreach ($res as $r) {
                $tool_content .= "<option value=" . $r->user_id . ">" . q($r->name) . "</option>";
            }
        } 
    
        $tool_content .= "</select></td></tr>";
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
                                    if ( term.length < 2 ) {
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
    	                    <td><input name='autocomplete' id='recipients' /></td>
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
    	load_js('jquery.multiselect.min.js');
        $head_content .= "<script type='text/javascript'>$(document).ready(function () {
                $('#select-recipients').multiselect({
                    selectedText: '$langJQSelectNum',
                    noneSelectedText: '$langJQNoneSelected',
                    checkAllText: '$langJQCheckAll',
                    uncheckAllText: '$langJQUncheckAll'
                });
        });</script>
        <link href='../../js/jquery.multiselect.css' rel='stylesheet' type='text/css'>";
	}
} else {//mailbox
    load_js('datatables');
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
                            $(\".ui-tabs-panel.ui-widget-content-new\").delegate('a', 'click', function(event) {
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
