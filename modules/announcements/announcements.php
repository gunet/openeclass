<?php
/* ========================================================================
 * Open eClass 2.9
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
 * @brief announcements module
 * @abstract This component offers several operations regarding a course's announcements.
 * The course administrator can:
 * 1. Re-arrange the order of the announcements
 * 2. Delete announcements (one by one or all at once)
 * 3. Modify existing announcements
 * 4. Add new announcements
 */

$require_current_course = true;
$require_help = true;
$helpTopic = 'Announce';
$guest_allowed = true;

include '../../include/baseTheme.php';
include '../../include/lib/textLib.inc.php';
include '../../include/sendMail.inc.php';
require_once '../../include/lib/modalboxhelper.class.php';
require_once '../../include/lib/multimediahelper.class.php';
require_once 'preview.php';

// The following is added for statistics purposes
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_ANNOUNCE');

define('RSS', 'modules/announcements/rss.php?c='.$currentCourseID);
//Identifying ajax request
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $limit = $_GET['iDisplayLength'];
    $offset = $_GET['iDisplayStart'];
    $keyword = $_GET['sSearch'];
    
    (!$is_editor)? $student_sql = "AND visibility = 'v'" : $student_sql = "";
    $all_announc = db_query("SELECT COUNT(*) AS total FROM annonces WHERE cours_id = $cours_id $student_sql", $mysqlMainDb);
    $all_announc = mysql_fetch_assoc($all_announc);
    $filtered_announc = db_query("SELECT COUNT(*) AS total FROM annonces WHERE cours_id = $cours_id AND title LIKE '%$keyword%' $student_sql", $mysqlMainDb);
    $filtered_announc = mysql_fetch_assoc($filtered_announc);
    ($limit>0) ? $extra_sql = "LIMIT $offset,$limit" : $extra_sql = "";

    $result = db_query("SELECT * FROM annonces WHERE cours_id = $cours_id AND title LIKE '%$keyword%' $student_sql ORDER BY ordre DESC $extra_sql", $mysqlMainDb);

    $data['iTotalRecords'] = $all_announc['total'];
    $data['iTotalDisplayRecords'] = $filtered_announc['total'];
    $data['aaData'] = array();
    if ($is_editor) {
        $iterator = 1;
        while ($myrow = mysql_fetch_array($result)) {
            //checking visibility status
            if ($myrow['visibility'] == 'i') {
                $visibility = 1;
                $vis_icon = 'invisible';
            } else {
                $visibility = 0;
                $vis_icon = 'visible';               
            }
            //checking ordering status and initializing appropriate arrows
            $up_arrow = $down_arrow = '';
            if ($iterator != 1)  {
                $up_arrow = "<a href='$_SERVER[SCRIPT_NAME]?course=".$code_cours ."&amp;up=" . $myrow["id"] . "'>
                                <img class='displayed' src='$themeimg/up.png' title='" . q($langMove) ."' alt = '". q($langUp) . "' />
                                </a>";
            }
            if ($iterator < $all_announc['total']) {
                $down_arrow = "<a href='$_SERVER[SCRIPT_NAME]?course=".$code_cours ."&amp;down=" . $myrow["id"] . "'>
                                <img class='displayed' src='$themeimg/down.png' title='" . q($langMove) ."' alt = '". q($langDown) . "' />
                                </a>";
            }
            //setting datables column data
            $preview = create_preview($myrow['contenu'], $myrow['preview'], $myrow['id'], $cours_id, $code_cours);
            $data['aaData'][] = array(
                'DT_RowClass' => $vis_icon,
                '0' => date('d-m-Y', strtotime($myrow['temps'])), 
                '1' => '<a href="'.$_SERVER['SCRIPT_NAME'].'?course='.$code_cours.'&an_id='.$myrow['id'].'">'.$myrow['title'].'</a>'.$preview, 
                '2' => icon('edit', $langModify, "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;modify=$myrow[id]")  .
                       "&nbsp;" . icon('delete', $langDelete, "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;delete=$myrow[id]", "onClick=\"return confirmation('$langSureToDelAnnounce');\"") .
                       "&nbsp;" . icon($vis_icon, $langVisible, "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;mkvis=$myrow[id]&amp;vis=$visibility") . 
                       "&nbsp;" . $down_arrow . $up_arrow
                );
            $iterator++;
        }
    } else {
        while ($myrow = mysql_fetch_array($result)) {
            $preview = create_preview($myrow['contenu'], $myrow['preview'], $myrow['id'], $cours_id, $code_cours);
            $data['aaData'][] = array(
                '0' => date('d-m-Y', strtotime($myrow['temps'])), 
                '1' => '<a href="'.$_SERVER['SCRIPT_NAME'].'?course='.$code_cours.'&an_id='.$myrow['id'].'">'.$myrow['title'].'</a>'.$preview
                );
        }        
    }
    echo json_encode($data);
    exit();
}
   
load_js('tools.js');
load_js('jquery');
//check if Datables code is needed
if (!isset($_GET['addAnnounce']) && !isset($_GET['modify']) && !isset($_GET['an_id'])) {
load_js('datatables');
load_js('datatables_filtering_delay');
$head_content .= "<script type='text/javascript'>  
        $(document).ready(function() {
           $('#ann_table').DataTable ({
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
                'fnDrawCallback': function() {
                    if (Math.ceil((this.fnSettings().fnRecordsDisplay()) / this.fnSettings()._iDisplayLength) > 1)  {
                        $('.dataTables_paginate').css('display', 'block'); 
                        $('.dataTables_filter').css('display', 'block');                       
                    } else {
                        $('.dataTables_paginate').css('display', 'none');
                        $('.dataTables_filter').css('display', 'none');
                    }
                    if (this.fnSettings().fnRecordsDisplay() > 10)  {
                        $('.dataTables_length').css('display', 'block');
                    } else {
                        $('.dataTables_length').css('display', 'none');
                    }
                },               
                'bSort': true,\n"
                .(($is_editor) ? "'aoColumnDefs': [{ 'bSortable': false, 'aTargets': [ 0 ] }, { 'bSortable': false, 'aTargets': [ 1 ] }, { 'bSortable': false, 'aTargets': [ 2 ] }]," : "").                
                "'oLanguage': {                       
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
        });
        </script>";
}
ModalBoxHelper::loadModalBox();

$fake_code = course_id_to_fake_code($cours_id);
$nameTools = $langAnnouncements;

if (isset($_GET['an_id'])) {
    (!$is_editor)? $student_sql = "AND visibility = 'v'" : $student_sql = "";
    $result = db_query("SELECT * FROM annonces WHERE cours_id = $cours_id AND id = ". intval($_GET['an_id']) ." ".$student_sql, $mysqlMainDb);
    $row = mysql_fetch_array($result);
}
if ($is_editor) {	
	$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
			 $langEmptyAnTitle . '";</script>';

	$result = db_query("SELECT count(*) FROM annonces WHERE cours_id = $cours_id", $mysqlMainDb);

	list($announcementNumber) = mysql_fetch_row($result);
	mysql_free_result($result);

	$displayForm = true;
	/* up and down commands */
	if (isset($_GET['down'])) {
		$thisAnnouncementId = $_GET['down'];
		$sortDirection = "DESC";
	}
	if (isset($_GET['up'])) {
		$thisAnnouncementId = $_GET['up'];
		$sortDirection = "ASC";
	}

	if (isset($thisAnnouncementId) && $thisAnnouncementId && isset($sortDirection) && $sortDirection) {
	    $result = db_query("SELECT id, ordre FROM annonces WHERE cours_id = $cours_id
		ORDER BY ordre $sortDirection", $mysqlMainDb);
		while (list ($announcementId, $announcementOrder) = mysql_fetch_row($result)) {
			if (isset($thisAnnouncementOrderFound) && $thisAnnouncementOrderFound == true) {
			    $nextAnnouncementId = $announcementId;
			    $nextAnnouncementOrder = $announcementOrder;
			    db_query("UPDATE annonces SET ordre = '$nextAnnouncementOrder' WHERE id = '$thisAnnouncementId'", $mysqlMainDb);
			    db_query("UPDATE annonces SET ordre = '$thisAnnouncementOrder' WHERE id = '$nextAnnouncementId'", $mysqlMainDb);
			    break;
			}
			// find the order
			if ($announcementId == $thisAnnouncementId) {
			    $thisAnnouncementOrder = $announcementOrder;
			    $thisAnnouncementOrderFound = true;
			}
		}
	}

    /* modify visibility */
    if (isset($_GET['mkvis'])) {
	$mkvis = intval($_GET['mkvis']);
	if ($_GET['vis'] == 1) {
	    $result = db_query("UPDATE annonces SET visibility = 'v' WHERE id = '$mkvis'", $mysqlMainDb);
	}
	if ($_GET['vis'] == 0) {
	    $result = db_query("UPDATE annonces SET visibility = 'i' WHERE id = '$mkvis'", $mysqlMainDb);
	}
    }
    /* delete */
    if (isset($_GET['delete'])) {
	$delete = intval($_GET['delete']);
        $result = db_query("DELETE FROM annonces WHERE id='$delete'", $mysqlMainDb);
        $message = "<p class='success'>$langAnnDel</p>";
    }

    /* modify */
    if (isset($_GET['modify'])) {
        $modify = intval($_GET['modify']);
        $result = db_query("SELECT * FROM annonces WHERE id='$modify'", $mysqlMainDb);
        $myrow = mysql_fetch_array($result);
        if ($myrow) {
            $AnnouncementToModify = $myrow['id'];
	    $contentToModify = $myrow['contenu'];
            $titleToModify = q($myrow['title']);
        }
    }

    /* submit */
    if (isset($_POST['submitAnnouncement'])) {
        // modify announcement
        $antitle = autoquote($_POST['antitle']);
        $newContent = purify(autounquote($_POST['newContent']));
        if (!empty($_POST['id'])) {
            $id = intval($_POST['id']);
            db_query("UPDATE annonces SET contenu = " . quote($newContent) . ",
                             title = $antitle, temps = NOW()
			WHERE id = $id AND cours_id = $cours_id");
            $message = "<p class='success'>$langAnnModify</p>";
        } else { // add new announcement
            $result = db_query("SELECT MAX(ordre) FROM annonces
				WHERE cours_id = $cours_id", $mysqlMainDb);
            list($orderMax) = mysql_fetch_row($result);
            $order = $orderMax + 1;
            // insert
            db_query("INSERT INTO annonces SET contenu = " . quote($newContent) . ",
                                  title = $antitle, temps = NOW(),
                                  cours_id = $cours_id, ordre = $order,
                                  visibility = 'v'");
            $id = mysql_insert_id();
        }
        create_preview($_POST['newContent'], false, $id, $cours_id, $code_cours);

        // send email
        if (isset($_POST['emailOption']) and $_POST['emailOption']) {
            $emailContent = "$professorMessage: $_SESSION[prenom] $_SESSION[nom]<br>\n<br>\n".
			     autounquote($_POST['antitle']) .
                            "<br>\n<br>\n" .
                            $newContent;
            $emailSubject = "$intitule ($fake_code)";
            // select students email list
            $sqlUserOfCourse = "SELECT cours_user.user_id, user.email FROM cours_user, user
                                WHERE cours_id = $cours_id
                                AND cours_user.user_id = user.user_id";
            $result = db_query($sqlUserOfCourse, $mysqlMainDb);

            $countEmail = mysql_num_rows($result); // number of mail recipients

            $invalid = 0;
	    $recipients = array();
            $emailBody = html2text($emailContent);
            $linkhere = "&nbsp;<a href='${urlServer}modules/profile/emailunsubscribe.php?cid=$cours_id'>$langHere</a>.";
            $unsubscribe = "<br /><br />$langNote:".sprintf($langLinkUnsubscribe, q($intitule));
            $emailContent .= $unsubscribe.$linkhere;
            $general_to = 'Members of course ' . $currentCourseID;
            while ($myrow = mysql_fetch_array($result)) {
                    $emailTo = $myrow["email"];
                    $user_id = $myrow["user_id"];
                    // check email syntax validity
                    if (!email_seems_valid($emailTo)) {
                            $invalid++;
                    } elseif (get_user_email_notification($user_id, $cours_id)) {
                            // checks if user is notified by email
                            array_push($recipients, $emailTo);
                    }
                    // send mail message per 50 recipients
                    if (count($recipients) >= 50) {
                            send_mail_multipart("$_SESSION[prenom] $_SESSION[nom]", $_SESSION['email'],
                                                $general_to,
                                            $recipients, $emailSubject,
                                            $emailBody, $emailContent, $charset);
                            $recipients = array();
                    }
            }
            if (count($recipients) > 0)  {
                send_mail_multipart("$_SESSION[prenom] $_SESSION[nom]", $_SESSION['email'], $general_to,
                            $recipients, $emailSubject,
                            $emailBody, $emailContent, $charset);
            }
            $messageInvalid = " $langOn $countEmail $langRegUser, $invalid $langInvalidMail";
            $message = "<p class='success'>$langAnnAdd $langEmailSent<br />$messageInvalid</p>";
        } // if $emailOption==1
        else {
            $message = "<p class='success'>$langAnnAdd</p>";
        }
    } // end of if $submit


    // teacher display
    if (isset($message) && $message) {
        $tool_content .= $message . "<br/>";
        $displayForm = false; //do not show form
    }

    /* display form */
    if ($displayForm && (isset($_GET['addAnnounce']) || isset($_GET['modify']))) {
        $tool_content .= "
        <form method='post' action='$_SERVER[SCRIPT_NAME]?course=".$code_cours."' onsubmit=\"return checkrequired(this, 'antitle');\">
        <fieldset>
        <legend>$langAnnouncement</legend>
	<table class='tbl' width='100%'>";
        if (isset($_GET['modify'])) {
            $langAdd = $nameTools = $langModifAnn;
        } else {
	    $nameTools = $langAddAnn;
        }
	$navigation[] = array("url" => "announcements.php?course=$code_cours", "name" => $langAnnouncements);
        if (!isset($AnnouncementToModify)) $AnnouncementToModify = "";
        if (!isset($contentToModify)) $contentToModify = "";
        if (!isset($titleToModify)) $titleToModify = "";

        $tool_content .= "
        <tr>
          <th>$langAnnTitle:</th>
        </tr>
        <tr>
          <td><input type='text' name='antitle' value='$titleToModify' size='50' /></td>
	</tr>
	<tr>
          <th>$langAnnBody:</th>
        </tr>
        <tr>
          <td>".rich_text_editor('newContent', 4, 20, $contentToModify)."</td>
        </tr>
	<tr>
          <td class='smaller right'>
	  <img src='$themeimg/email.png' title='email' /> $langEmailOption: <input type='checkbox' value='1' name='emailOption' /></td>
        </tr>
	<tr>
          <td class='right'><input type='submit' name='submitAnnouncement' value='".q($langAdd)."' /></td>
	</tr>
	</table>
	<input type='hidden' name='id' value='$AnnouncementToModify' />
        </fieldset>
	</form>";
    } else {
	/* display actions toolbar */
	$tool_content .= "
	<div id='operations_container'>
	  <ul id='opslist'>";
        if (isset($_GET['an_id'])) {
            $tool_content .= "<li><a href='" . $_SERVER['SCRIPT_NAME'] . "?course=" .$code_cours . "&amp;modify=$row[id]'>" . $langModify . "</a></li>
                              <li><a href='" . $_SERVER['SCRIPT_NAME'] . "?course=" .$code_cours . "&amp;delete=$row[id]' onClick=\"return confirmation('$langSureToDelAnnounce');\">" . $langDelete . "</a></li>";
        } else {
            $tool_content .= "<li><a href='" . $_SERVER['SCRIPT_NAME'] . "?course=" .$code_cours . "&amp;addAnnounce=1'>" . $langAddAnn . "</a></li>";
        }
        $tool_content .= "  </ul>
	</div>";
    }
} // end: teacher only

    /* display announcements */
    if (isset($_GET['an_id'])) {
        $nameTools = $row['title'];
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$code_cours", "name" => $langAnnouncements);
        $tool_content .= $row['contenu'];
    }
    if (!isset($_GET['addAnnounce']) && !isset($_GET['modify']) && !isset($_GET['an_id'])) {
        $tool_content .= "<table id='ann_table' class='display'>";
        $tool_content .= "<thead>";	
        $tool_content .= "<tr><th width='100'>$langDate</th><th>$langAnnouncement</th>";                
        if ($is_editor) {
            $tool_content .= "<th width='100' class='center'>$langActions</th>";
        }
        $tool_content .= "</tr></thead><tbody></tbody></table>";
    }
add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
