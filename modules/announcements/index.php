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

/**
 * @file announcements.php
 * @brief Course announcements
*/

$require_current_course = true;
$require_help = true;
$helpTopic = 'Announce';
$guest_allowed = true;

include '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/log.php';
require_once 'modules/search/announcementindexer.class.php';
// The following is added for statistics purposes
require_once 'include/action.php';

$action = new action();
$action->record(MODULE_ID_ANNOUNCE);

define('RSS', 'modules/announcements/rss.php?c=' . $course_code);
$public_code = course_id_to_public_code($course_id);
$nameTools = $langAnnouncements;

//Identifying ajax request
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['action']) && $is_editor) {
                
        if ($_POST['action']=='delete') {        /* delete */
            //$delete = intval($_GET['delete']);
            $row_id = intval($_POST['value']);            
            $announce = Database::get()->querySingle("SELECT title, content FROM announcement WHERE id = ?d ", $row_id);
            $txt_content = ellipsize_html(canonicalize_whitespace(strip_tags($announce->content)), 50, '+');
            Database::get()->query("DELETE FROM announcement WHERE id = ?d", $row_id);
            $aidx->remove($row_id);
            Log::record($course_id, MODULE_ID_ANNOUNCE, LOG_DELETE, array('id' => $delete,
                                                                          'title' => $announce->title,
                                                                          'content' => $txt_content));
            //$message = "<p class='success'>$langAnnDel</p>";
    
            
            //$result = db_query("DELETE FROM annonces WHERE id='$row_id'", $mysqlMainDb);      
           exit();
        } elseif ($_POST['action']=='visibility') {    /* modify visibility */        
           $row_id = intval($_POST['value']);
           $visibility = intval($_POST['visibility']) ? 1 : 0;                     
           Database::get()->query("UPDATE announcement SET visible = ?d WHERE id = ?d", $visibility, $row_id);
           $aidx->store($row_id);                     
           exit();
        }                              
    }  
    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);
    $keyword = quote('%' . $_GET['sSearch'] . '%');
    
    $student_sql = $is_editor? '': "AND visible = '1'";
    $all_announc = db_query("SELECT COUNT(*) AS total FROM announcement WHERE course_id = $course_id $student_sql");
    $all_announc = mysql_fetch_assoc($all_announc);
    $filtered_announc = db_query("SELECT COUNT(*) AS total FROM announcement WHERE course_id = $course_id AND title LIKE $keyword $student_sql");
    $filtered_announc = mysql_fetch_assoc($filtered_announc);
    ($limit>0) ? $extra_sql = "LIMIT $offset,$limit" : $extra_sql = "";

    $result = db_query("SELECT * FROM announcement WHERE course_id = $course_id AND title LIKE $keyword $student_sql ORDER BY `order` DESC $extra_sql");

    $data['iTotalRecords'] = $all_announc['total'];
    $data['iTotalDisplayRecords'] = $filtered_announc['total'];
    $data['aaData'] = array();
    if ($is_editor) {
        $iterator = 1;
        while ($myrow = mysql_fetch_array($result)) {
            //checking visibility status
            if ($myrow['visible'] == '1') {
                $visibility = 1;
                $vis_icon = 'invisible';
            } else {
                $visibility = 0;
                $vis_icon = 'visible';               
            }
            //checking ordering status and initializing appropriate arrows
            $up_arrow = $down_arrow = '';
            if ($iterator != 1 or $offset > 0)  {
                $up_arrow = icon('up', $langMove, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;up={$myrow['id']}");
            }
            if ($offset + $iterator < $all_announc['total']) {
                $down_arrow = icon('down', $langMove, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;down={$myrow['id']}");
            }
            //setting datables column data
            $preview = create_preview($myrow['content'], $myrow['preview'], $myrow['id'], $course_id, $course_code);
            $data['aaData'][] = array(
                'DT_RowId' => $myrow['id'],
                'DT_RowClass' => $vis_icon,
                '0' => date('d-m-Y', strtotime($myrow['temps'])), 
                '1' => '<a href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code.'&an_id='.$myrow['id'].'">'.$myrow['title'].'</a>'.$preview, 
                '2' => icon('edit', $langModify, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;modify=$myrow[id]")  .
                       "&nbsp;" . icon('delete', $langDelete, "", "class=\"delete_btn\"") .
                       "&nbsp;" . icon($vis_icon, $langVisible, "", "class=\"vis_btn\" data-vis=\"$visibility\"") . 
                       "&nbsp;" . $down_arrow . $up_arrow
                );
            $iterator++;
        }
    } else {
        while ($myrow = mysql_fetch_array($result)) {
            $preview = create_preview($myrow['content'], $myrow['preview'], $myrow['id'], $course_id, $course_code);
            $data['aaData'][] = array(
                '0' => date('d-m-Y', strtotime($myrow['date'])), 
                '1' => '<a href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code.'&an_id='.$myrow['id'].'">'.$myrow['title'].'</a>'.$preview
                );
        }        
    }
    echo json_encode($data);
    exit();
}

load_js('tools.js');
load_js('jquery');
if (!isset($_GET['addAnnounce']) && !isset($_GET['modify']) && !isset($_GET['an_id'])) {
    load_js('datatables');
    load_js('datatables_filtering_delay');
    $head_content .= "<script type='text/javascript'>  
        $(document).ready(function() {
           var oTable = $('#ann_table{$course_id}').DataTable ({
                'bStateSave': true,
                'bProcessing': true,
                'bServerSide': true,
                'sDom': '<\"top\"pfl<\"clear\">>rt<\"bottom\"ip<\"clear\">>',
                'sAjaxSource': '$_SERVER[SCRIPT_NAME]',                   
                'aLengthMenu': [
                   [10, 15, 20 , -1],
                   [10, 15, 20, '$langAllOfThem'] // change per page values here
               ],                    
                'sPaginationType': 'full_numbers',              
                'bSort': false,               
                'oLanguage': {                       
                       'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                       'sZeroRecords':  '".$langNoResult."',
                       'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                       'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                       'sInfoFiltered': '',
                       'sInfoPostFix':  '',
                       'sSearch':       '".$langSearch."',
                       'sUrl':          '',
                       'oPaginate': {
                           'sFirst':    '&laquo;',
                           'sPrevious': '&lsaquo;',
                           'sNext':     '&rsaquo;',
                           'sLast':     '&raquo;'
                       }
                   }
            }).fnSetFilteringDelay(1000);
            $(document).on( 'click','.delete_btn', function (e) {
                e.preventDefault();
                if (confirmation('$langSureToDelAnnounce')) {
                    var row_id = $(this).closest('tr').attr('id');
                    $.post('', { action: 'delete', value: row_id}, function() {
                        var num_page_records = oTable.fnGetData().length;
                        var per_page = oTable.fnPagingInfo().iLength;
                        var page_number = oTable.fnPagingInfo().iPage;
                        if(num_page_records==1){
                            if(page_number!=0) {
                                page_number--;
                            }
                        }
                        $('#tool_title').after('<p class=\"success\">$langAnnDel</p>');
                        $('.success').delay(3000).fadeOut(1500);    
                        oTable.fnPageChange(page_number);
                    }, 'json');
                 }
            });
            $(document).on( 'click','.vis_btn', function (g) {
                g.preventDefault();
                var vis = $(this).data('vis');
                var row_id = $(this).closest('tr').attr('id');
                $.post('', { action: 'visibility', value: row_id, visibility: vis}, function() {
                    var page_number = oTable.fnPagingInfo().iPage;
                    var per_page = oTable.fnPagingInfo().iLength;
                    oTable.fnPageChange(page_number);
                }, 'json');                             
            });
            $('.success').delay(3000).fadeOut(1500);
        });
        </script>";
}
ModalBoxHelper::loadModalBox();

if (isset($_GET['an_id'])) {
    (!$is_editor)? $student_sql = "AND visible = '1'" : $student_sql = "";
    $result = db_query("SELECT * FROM announcement WHERE course_id = $course_id AND id = ". intval($_GET['an_id']) ." ".$student_sql);
    $row = mysql_fetch_array($result);
}

if ($is_editor) {    
    $head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
            $langEmptyAnTitle . '";</script>';
    $aidx = new AnnouncementIndexer();

    $announcementNumber = Database::get()->querySingle("SELECT COUNT(*) AS count FROM announcement WHERE course_id = ?d", $course_id)->count;

    $displayForm = true;
    /* up and down commands */
    if (isset($_GET['down'])) {
        $thisAnnouncementId = intval($_GET['down']);
        $sortDirection = 'DESC';
    }
    if (isset($_GET['up'])) {
        $thisAnnouncementId = intval($_GET['up']);
        $sortDirection = 'ASC';
    }

    $thisAnnouncementOrderFound = false;
    if (isset($thisAnnouncementId) && $thisAnnouncementId && isset($sortDirection) && $sortDirection) {
        //Debug::setLevel(Debug::INFO);
        $ids = Database::get()->queryArray("SELECT id, `order` FROM announcement
                                           WHERE course_id = ?d
                                           ORDER BY `order` $sortDirection", $course_id);
        foreach ($ids as $announcement) {   
            if ($thisAnnouncementOrderFound) {
                $nextAnnouncementId = $announcement->id;
                $nextAnnouncementOrder = $announcement->order;
                Database::get()->query("UPDATE announcement SET `order` = ?d WHERE id = ?d", $nextAnnouncementOrder, $thisAnnouncementId);
                Database::get()->query("UPDATE announcement SET `order` = ?d WHERE id = ?d", $thisAnnouncementOrder, $nextAnnouncementId);
                break;
            }
            // find the order
            if ($announcement->id == $thisAnnouncementId) {
                $thisAnnouncementOrder = $announcement->order;
                $thisAnnouncementOrderFound = true;
            }
       }       
    }
     
    /* modify */
    if (isset($_GET['modify'])) {
        $modify = intval($_GET['modify']);
        $announce = Database::get()->querySingle("SELECT * FROM announcement WHERE id=?d", $modify);
        if ($announce) {
            $AnnouncementToModify = $announce->id;
            $contentToModify = $announce->content;
            $titleToModify = q($announce->title);
        }
    }

    /* submit */
    if (isset($_POST['submitAnnouncement'])) {
        // modify announcement
        $antitle = $_POST['antitle'];       
        $newContent = purify($_POST['newContent']);
        $send_mail = !!(isset($_POST['emailOption']) and $_POST['emailOption']);
        if (!empty($_POST['id'])) {
            $id = intval($_POST['id']);
            Database::get()->query("UPDATE announcement SET content = ?s, title = ?s, `date` = NOW() WHERE id = ?d", $newContent, $antitle, $id);
            $log_type = LOG_MODIFY;
            $message = "<p class='success'>$langAnnModify</p>";
        } else { // add new announcement
            $orderMax = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM announcement
                                                   WHERE course_id = ?d", $course_id)->maxorder;
            $order = $orderMax + 1;
            // insert
            $id = Database::get()->query("INSERT INTO announcement
                                         SET content = ?s,
                                             title = ?s, `date` = NOW(),
                                             course_id = ?d, `order` = ?d,
                                             visible = 1", $newContent, $antitle, $course_id, $order)->lastInsertID;
            $log_type = LOG_INSERT;
        }
        $aidx->store($id);
        $txt_content = ellipsize_html(canonicalize_whitespace(strip_tags($_POST['newContent'])), 50, '+');
        Log::record($course_id, MODULE_ID_ANNOUNCE, $log_type, array('id' => $id,
                                                                     'email' => $send_mail,
                                                                     'title' => $_POST['antitle'],
                                                                     'content' => $txt_content));

        // send email
        if ($send_mail) {
            $emailContent = "$professorMessage: $_SESSION[givenname] $_SESSION[surname]<br>\n<br>\n" .
                    autounquote($_POST['antitle']) .
                    "<br>\n<br>\n" .
                    autounquote($_POST['newContent']);
            $emailSubject = "$professorMessage ($public_code - $title)";
            // select students email list
            $countEmail = 0;
            $invalid = 0;
            $recipients = array();
            $emailBody = html2text($emailContent);
            $linkhere = "&nbsp;<a href='${urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a>.";
            $unsubscribe = "<br /><br />$langNote: " . sprintf($langLinkUnsubscribe, $title);
            $emailContent .= $unsubscribe . $linkhere;
            $general_to = 'Members of course ' . $course_code;
            Database::get()->queryFunc("SELECT course_user.user_id as id, user.email as email
                                                   FROM course_user, user
                                                   WHERE course_id = ?d AND
                                                         course_user.user_id = user.id", function ($person)
                    use (&$countEmail, &$recipients, &$invalid, $course_id, $general_to, $emailSubject, $emailBody, $emailContent, $charset) {
                $countEmail++;
                $emailTo = $person->email;
                $user_id = $person->id;
                // check email syntax validity
                if (!email_seems_valid($emailTo)) {
                    $invalid++;
                } elseif (get_user_email_notification($user_id, $course_id)) {
                    // checks if user is notified by email
                    array_push($recipients, $emailTo);
                }
                // send mail message per 50 recipients
                if (count($recipients) >= 50) {
                    send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], $general_to, $recipients, $emailSubject, $emailBody, $emailContent, $charset);
                    $recipients = array();
                }
            }, $course_id);
            if (count($recipients) > 0) {
                send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], $general_to, $recipients, $emailSubject, $emailBody, $emailContent, $charset);
            }
            $messageInvalid = " $langOn $countEmail $langRegUser, $invalid $langInvalidMail";
            $message = "<p class='success'>$langAnnAdd $langEmailSent<br />$messageInvalid</p>";
        } else {
            $message = "<p class='success'>$langAnnAdd</p>";
        }
    } // end of if $submit
    // teacher display
    if (isset($message) && $message) {
        $tool_content .= $message . "<br/>";
        $displayForm = false; //do not show form
    }

    /* display form */
    if ($displayForm and (isset($_GET['addAnnounce']) or isset($_GET['modify']))) {
        $tool_content .= "
        <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'antitle');\">
        <fieldset>
        <legend>$langAnnouncement</legend>
        <table class='tbl' width='100%'>";
        if (isset($_GET['modify'])) {
            $langAdd = $nameTools = $langModifAnn;
        } else {
            $nameTools = $langAddAnn;
        }
        $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langAnnouncements);
        if (!isset($AnnouncementToModify))
            $AnnouncementToModify = "";
        if (!isset($contentToModify))
            $contentToModify = "";
        if (!isset($titleToModify))
            $titleToModify = "";

        $tool_content .= "
        <tr><th>$langAnnTitle:</th></tr>
        <tr>
          <td><input type='text' name='antitle' value='$titleToModify' size='50' /></td>
        </tr>
        <tr><th>$langAnnBody:</th></tr>
        <tr>
          <td>" . rich_text_editor('newContent', 4, 20, $contentToModify) . "</td>
        </tr>
        <tr>
          <td class='smaller right'>
          <img src='$themeimg/email.png' title='email' /> $langEmailOption: <input type='checkbox' value='1' name='emailOption' /></td>
        </tr>
        <tr>
          <td class='right'><input type='submit' name='submitAnnouncement' value='$langAdd' /></td>
        </tr>
        </table>
        <input type='hidden' name='id' value='$AnnouncementToModify' />
        </fieldset>
        </form>";
    } else {
        /* display actions toolbar */
        $tool_content .= "
        <div id='operations_container'>
          <ul id='opslist'>
            <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addAnnounce=1'>" . $langAddAnn . "</a></li>
          </ul>
        </div>";
    }
} // end: teacher only

/* display announcements */
$limit_sql = ($is_editor ? '' : ' AND visible = 1') .
        (isset($_GET['an_id']) ? ' AND id = ' . intval($_GET['an_id']) : '');
$result = Database::get()->queryArray("SELECT * FROM announcement WHERE course_id = ?d " . $limit_sql . " ORDER BY `order` DESC", $course_id);

$iterator = 1;
$bottomAnnouncement = $announcementNumber = count($result);
if (isset($_GET['an_id'])) {
        $nameTools = $row['title'];
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langAnnouncements);
        $tool_content .= $row['content'];
    }
        

if (!isset($_GET['addAnnounce']) && !isset($_GET['modify']) && !isset($_GET['an_id'])) {
    $tool_content .= "<table id='ann_table{$course_id}' class='display'>";
    $tool_content .= "<thead>";	
    $tool_content .= "<tr><th width='100'>$langDate</th><th>$langAnnouncement</th>";                
    if ($is_editor) {
        $tool_content .= "<th width='100' class='center'>$langActions</th>";
    }
    $tool_content .= "</tr></thead><tbody></tbody></table>";
}

    
/*

if ($announcementNumber < 1) {
    $no_content = true;
    if (isset($_GET['addAnnounce'])) {
        $no_content = false;
    }
    if (isset($_GET['modify'])) {
        $no_content = false;
    }
    if ($no_content) {
        $tool_content .= "<p class='alert1'>$langNoAnnounce</p>\n";
    }
}
*/
add_units_navigation(TRUE);

draw($tool_content, 2, null, $head_content);
