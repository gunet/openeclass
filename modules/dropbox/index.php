<?php
/* ========================================================================
 * Open eClass 2.10
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
 
require_once("functions.php");
$nameTools = $langDropBox;
$basedir = $webDir . 'courses/' . $currentCourseID . '/dropbox';
$diskUsed = dir_total_space($basedir);
$displayall = false;
$display_outcoming = false;
$is_tutor = FALSE;

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_DROPBOX');
/**************************************/

require_once('dropbox_class.inc.php');

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {    
    $dropbox_person = new Dropbox_Person($uid);
    
    if (isset($_GET['s']) && $_GET['s']) {
        $message_type = 'sent';
        if (isset($_POST['action']) && $_POST['action']=='delete') {
            $row_id = intval($_POST['value']);
            $dropbox_person->deleteSentWork($row_id);
            exit();
        }
    } elseif (isset($_GET['other']) && $_GET['other'] && $is_editor) {
        $message_type = 'allsent';
        if (isset($_POST['action']) && $_POST['action']=='delete') {
            $row_id = intval($_POST['value']);
            $dropbox_person->deleteWork($row_id);
            exit();
        }        
    } else {
        $message_type = 'received';
        if (isset($_POST['action']) && $_POST['action']=='delete') {
            $row_id = intval($_POST['value']);
            $dropbox_person->deleteReceivedWork($row_id);
            exit();
        }         
    }
    
    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);    
    
    //Total records
    $data['iTotalRecords'] = count($dropbox_person->{$message_type.'Work'});
 
    $keyword = quote('%' . $_GET['sSearch'] . '%');
    $dropbox_person->filterMessages($message_type, $keyword);
    
    //Total records after applying search filter
    $data['iTotalDisplayRecords'] = count($dropbox_person->{$message_type.'Work'});
    
    //Records after applying filtering and pagination
    $dropbox_person->filterMessages($message_type, $keyword, $limit, $offset);
    
    $data['aaData'] = array();
   
    foreach ($dropbox_person->{$message_type.'Work'} as $w) {

        if (($w->filename != '') and ($w->filesize != 0)) {
            $ahref = "dropbox_download.php?course=$code_cours&amp;id=" . urlencode($w->id) ;
            $file_name = "&nbsp;&nbsp;<a href='$ahref' target='_blank'><img src='$themeimg/attachment.png' />
                          </a><span class='smaller'>&nbsp;&nbsp;(".format_file_size($w->filesize).")</span><br />";
        } else {
            $file_name = '';
        }
        
        $dropbox_unid = md5(uniqid(crypto_rand_secure(), true));
                        
        //Creating sender field ONLY for allsent datatable       
        $td[1] = "<small>".display_user($w->uploaderId, false, false)."</small>";
        //because of extra field added move all fields to the right by 1
        if ($message_type=='allsent') {
            $index_offset = 1;
            $message_link_parameter = '&amp;other=1';
        } else {
            $index_offset = 0; //if not allsent datatable, there will be no offset
            $td[4] = ''; //if not allsent datatable, forth column will be empty
            if ($message_type == 'received') {
                   $message_link_parameter = '';
            } else {
                    $message_link_parameter = '&amp;s=1';
            }
        }
        //Generating icon, message title,message link and message ellipsized content
        $td[0] = "<img src='$themeimg/message.png' title='".q($w->title)."' />
                  <a href='$_SERVER[SCRIPT_NAME]?course={$code_cours}{$message_link_parameter}&amp;id=$w->id'>".$w->title."</a>".$file_name."";
        
        // Get Recipient for datatables other than received (inbox)
        if ($message_type != 'received'){
            //Get Recipients Names
            $recipients_names = '';
            foreach($w->recipients as $r) {
                $recipients_names .= display_user($r['id'], false, false) . " <br />";
            }            
            $td[1+$index_offset] = "<small>".ellipsize_html($recipients_names, 50)."</small>";            
        }
        //Setting and positioning of date field
        $td[2+$index_offset] = '<small>'.$w->uploadDate.'</small>';
        //Setting and positioning of delete button
        $td[3+$index_offset] = "<div align='center'><a class='delete_btn' href='#'>
                                    <img src='$themeimg/delete.png' title='".q($langDelete)."' /></a></div>";
        $data['aaData'][] = array(
                    'DT_RowId' => $w->id,
                    '0' => $td[0], 
                    '1' => $td[1],
                    '2' => $td[2],
                    '3' => $td[3],
                    '4' => $td[4]
                    );
    }
    echo json_encode($data);
    exit();            
}           

load_js('jquery');
load_js('datatables');
load_js('datatables_filtering_delay');
load_js('jquery-ui');
load_js('jquery.multiselect.min.js');
if (isset($_GET['s']) && $_GET['s']) {
    $messages_type='sent';
} elseif (isset($_GET['other']) && $_GET['other']) {
    $messages_type='allsent';
} else {
    $messages_type= 'received';
}

$head_content .= "<script type='text/javascript'>$(document).ready(function () {
               var oTable = $('#messages_tbl{$messages_type}{$cours_id}').DataTable ({
                'bState': true,
                'bProcessing': true,
                'bServerSide': true,
                'sDom': '<\"top\"pfl<\"clear\">>rt<\"bottom\"ip<\"clear\">>',
                'sAjaxSource': '$_SERVER[REQUEST_URI]',                   
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
                if (confirmation()) {
                
                    var row_id = $(this).closest('tr').attr('id');   
                    $.post('$_SERVER[REQUEST_URI]', { action: 'delete', value: row_id}, function() {
                        var num_page_records = oTable.fnGetData().length;
                        var per_page = oTable.fnPagingInfo().iLength;
                        var page_number = oTable.fnPagingInfo().iPage;
                        if(num_page_records==1){
                            if(page_number!=0) {
                                page_number--;
                            }
                        }
                        $('#tool_title').after('<p class=\"success\">$dropbox_lang[fileDeleted]</p>');
                        $('.success').delay(3000).fadeOut(1500);    
                        oTable.fnPageChange(page_number);
                    }, 'json');
                 }
            });            
        $('#select-recipients').multiselect({
                selectedText: '$langJQSelectNum',
                noneSelectedText: '$langJQNoneSelected',
                checkAllText: '$langJQCheckAll',
                uncheckAllText: '$langJQUncheckAll'
        });
});</script>
<link href='../../js/jquery-ui.css' rel='stylesheet' type='text/css'>
<link href='../../js/jquery.multiselect.css' rel='stylesheet' type='text/css'>";


$dropbox_unid = md5(uniqid(crypto_rand_secure(), true)); //this var is used to give a unique value to every
                                                         //page request. This is to prevent resubmiting data

if (isset($_GET['showQuota']) and $_GET['showQuota'] == TRUE) {
	$nameTools = $langQuotaBar;
	$navigation[]= array ("url"=>"$_SERVER[SCRIPT_NAME]?course=$code_cours", "name"=> $langDropBox);
	$tool_content .= showquota($diskQuotaDropbox, $diskUsed);
	draw($tool_content, 2);
	exit;
}

$tool_content .= "<div id='operations_container'>
  <ul id='opslist'>
    <li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;upload=1'>$dropbox_lang[uploadFile]</a></li>";
    $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours'>$dropbox_lang[receivedTitle]</a></li>";
    $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;s=1'>$dropbox_lang[sentTitle]</a></li>";
if ($is_editor) {      
        $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;other=1'>$langOtherDropBoxFiles</a></li>";       
}
$tool_content .= " <li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;showQuota=TRUE'>$langQuotaBar</a></li>
  </ul>
</div>";
if ($is_editor) {
    if (isset($_GET['other']) and $_GET['other']) {
        $navigation[]= array ("url"=>"$_SERVER[SCRIPT_NAME]?course=$code_cours", "name"=> $langDropBox);
        $nameTools = $langOtherDropBoxFiles;
        $displayall = true;    
        if (isset($_GET['id'])) {            
            $message_id = intval($_GET['id']);
            $dropbox_person = new Dropbox_Person($uid, false);
            foreach ($dropbox_person->sentWork as $w) {
                $recipients_names = '';
                $i = 0;
                foreach($w->recipients as $r) {
                    if ($i > 0 ) { // add comma if needed
                       $recipients_names .= ", ";
                    }
                    $recipients_names .= display_user($r['id'], false, false);
                    $i++;
                }                
                $tool_content .= "<span style='font-weight:bold;'>".q($w->title)."</span>&nbsp;";
                $tool_content .= "<a href=\"dropbox_submit.php?course=$code_cours&amp;AdminDeleteSent=".urlencode($w->id)."&amp;dropbox_unid=".urlencode($dropbox_unid)."\" onClick='return confirmation();'>
                             <img src='$themeimg/delete.png' title='".q($langDelete)."' /></a>";
                $tool_content .= "<br /><small>$langFrom: ".$w->uploaderName."</small>";
                $tool_content .= "<br /><small>$langToHim: ".$recipients_names."</small>";
                $tool_content .= "<div style='margin-top: 10px; margin-bottom: 10px;'>".standard_text_escape($w->description)."</div>";
                if (($w->filename != '') and ($w->filesize != 0)) {
                   $ahref = "dropbox_download.php?course=$code_cours&amp;id=" . urlencode($w->id);
                    $tool_content .= "<small><a href='$ahref' target='_blank'><img src='$themeimg/attachment.png' />
                          ($w->real_filename)</small></a><span class='smaller'>&nbsp;&nbsp;&nbsp;(".format_file_size($w->filesize).")</span>";
                }
            }
        draw($tool_content, 2, null, $head_content);
	exit;             
        }
    }
}

if (isset($_GET['s']) and $_GET['s']) {
    $navigation[]= array ("url"=>"$_SERVER[SCRIPT_NAME]?course=$code_cours", "name"=> $langDropBox);
    $nameTools = $dropbox_lang['sentTitle'];
    $display_outcoming = true;
    $require_help = false;        
    $dropbox_person = new Dropbox_Person($uid);
    if (isset($_GET['id'])) {
        $s_message_id = intval($_GET['id']);    
        $dropbox_person = new Dropbox_Person($uid, true, false);        
        foreach ($dropbox_person->sentWork as $w){
            $recipients_names = '';
            $i = 0;
            foreach($w->recipients as $r) {
                if ($i > 0 ) { // add comma if needed
                   $recipients_names .= ", ";
                }
                $recipients_names .= display_user($r['id'], false, false);
                $i++;
            }
            $tool_content .= "<span style='font-weight:bold; font-size: 110%;'>".q($w->title)."</span>&nbsp;<small>($w->uploadDate)</small>";
            $tool_content .= "<a href=\"dropbox_submit.php?course=$code_cours&amp;deleteSent=".urlencode($w->id)."&amp;dropbox_unid=".urlencode($dropbox_unid)."\" onClick='return confirmation();'>
                             <img src='$themeimg/delete.png' title='".q($langDelete)."' /></a>";
            $tool_content .= "<br /><small>$langToHim: ".$recipients_names."</small>";
            $tool_content .= "<div style='margin-top: 10px; margin-bottom: 10px;'>".standard_text_escape($w->description)."</div>";
            if (($w->filename != '') and ($w->filesize != 0)) {
                $ahref = "dropbox_download.php?course=$code_cours&amp;id=" . urlencode($w->id);
                $tool_content .= "<small><a href='$ahref' target='_blank'><img src='$themeimg/attachment.png' />
                          ($w->real_filename)</small></a><span class='smaller'>&nbsp;&nbsp;&nbsp;(".format_file_size($w->filesize).")</span>";
            }
        }
        draw($tool_content, 2, null, $head_content);
	exit;        
    }
} else {
    if (!isset($_GET['other'])) {
        $navigation[]= array ("url"=>"$_SERVER[SCRIPT_NAME]?course=$code_cours", "name"=> $langDropBox);
        $nameTools = $dropbox_lang['receivedTitle'];
    }
    $dropbox_person = new Dropbox_Person($uid);
    if (isset($_GET['id'])) {
        $messagebody = true;
        $r_message_id = intval($_GET['id']);        
        $dropbox_person = new Dropbox_Person($uid, false, true);
        foreach ($dropbox_person->receivedWork as $w){            
            $tool_content .= "<span style='font-weight:bold;'>".q($w->title)."</span>&nbsp;<small>($langFrom2: ".display_user($w->uploaderId, false, false).")</small>";
            $tool_content .= "<a href=\"dropbox_submit.php?course=$code_cours&amp;deleteReceived=".urlencode($w->id)."&amp;dropbox_unid=".urlencode($dropbox_unid)."\" onClick='return confirmation();'>
                             <img src='$themeimg/delete.png' title='".q($langDelete)."' /></a>";
            $tool_content .= "<div style='margin-top:10px; margin-bottom: 10px;'>".standard_text_escape($w->description)."</div>";
            if (($w->filename != '') and ($w->filesize != 0)) {
                $ahref = "dropbox_download.php?course=$code_cours&amp;id=" . urlencode($w->id);
                $tool_content .= "<small><a href='$ahref' target='_blank'><img src='$themeimg/attachment.png' />
                          ($w->real_filename)</small></a><span class='smaller'>&nbsp;&nbsp;&nbsp;(".format_file_size($w->filesize).")</span>";
            }
        }
        draw($tool_content, 2, null, $head_content);
	exit;           
    }   
}

/*
 * ========================================
 * FORM UPLOAD FILE
 * ========================================
 */

if(isset($_REQUEST['upload']) && $_REQUEST['upload'] == 1) {
        if (isset($_GET['group_id'])) {            
            $group_id = intval($_GET['group_id']);            
            $tutor_id = db_query_get_single_value("SELECT is_tutor FROM group_members WHERE group_id = $group_id AND user_id = $uid", $mysqlMainDb);            
            $is_tutor = ($tutor_id == 1)?TRUE:FALSE;
        }
                
	$tool_content .= "<form method='post' action='dropbox_submit.php?course=$code_cours' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
	$tool_content .= "
            <fieldset>
            <legend>".$dropbox_lang['uploadFile']."</legend>
            <table width='100%' class='tbl'>
            <tr>
              <th>".$langSender.":</th>
              <td>".q(uid_to_name($uid))."</td>
            </tr>";
            @$tool_content .= "<tr>
              <th width='120'>".$langTitle.":</th>
              <td><input type='input' name='title' size='50' value='$title' />	      
              </td>
            </tr>";
            @$tool_content .= "<tr>
              <th>".$langMessage.":</th>
              <td>".rich_text_editor('description', 4, 20, $description)."
              </td>
            </tr>
            <tr>
              <th width='120'>".$langFileName.":</th>
              <td><input type='file' name='file' size='35' />
                  <input type='hidden' name='dropbox_unid' value='$dropbox_unid' />
              </td>
            </tr>
            <tr>
              <th>".$langSend.":</th>
              <td>
            <select name='recipients[]' multiple='true' class='auth_input' id='select-recipients'>";
	
        if (isset($group_id) and ($is_editor or $is_tutor)) { // if we come from groups and user is tutor show only his group              
                $row = db_query_get_single_row("SELECT id, name FROM `group` WHERE course_id = $cours_id AND id = $group_id", $mysqlMainDb);
                $tool_content .= "<option value = '_$row[id]' selected>".q($row['name'])."</option>";
        } else {
            if ($is_editor or $dropbox_cnf["allowStudentToStudent"]) { // if user is a teacher then show all users of current course
		$sql = "SELECT DISTINCT u.user_id , CONCAT(u.nom,' ', u.prenom) AS name
			FROM user u, cours_user cu
			WHERE cu.cours_id = $cours_id
				AND cu.user_id = u.user_id 
				AND cu.statut != 10
				AND u.user_id != $uid
				ORDER BY UPPER(u.nom), UPPER(u.prenom)";
                // also select all course groups if exist
                $sql_g = "SELECT id, name FROM `group` WHERE course_id = $cours_id";                
                $result_g = db_query($sql_g, $mysqlMainDb);
                while ($res_g = mysql_fetch_array($result_g))
                {
                    $tool_content .= "<option value = '_$res_g[id]'>".q($res_g['name'])."</option>";
                }	                 
            } else {
                    // if user is tutor show its group
                    $s = db_query("SELECT group_id, is_tutor FROM group_members WHERE user_id = $uid", $mysqlMainDb);
                    while ($r = mysql_fetch_array($s)) {
                        if ($r['is_tutor'] == 1) {
                            $row = db_query_get_single_row("SELECT id, name FROM `group` WHERE course_id = $cours_id and id = $r[group_id]", $mysqlMainDb);
                            $tool_content .= "<option value = '_$row[id]'>".q($row['name'])."</option>";
                        }                        
                    }
                    // if user is student then show all teachers of current course
                    $sql = "SELECT DISTINCT u.user_id , CONCAT(u.nom,' ', u.prenom) AS name
                            FROM user u, cours_user cu
                            WHERE cu.cours_id = $cours_id
                                    AND cu.user_id = u.user_id
                                    AND (cu.statut <> 5 OR cu.tutor = 1)
                                    AND u.user_id != $uid
                                    ORDER BY UPPER(u.nom), UPPER(u.prenom)";
            }

            $result = db_query($sql, $mysqlMainDb);
            while ($res = mysql_fetch_array($result))
            {
                    $tool_content .= "<option value = ".$res['user_id'].">".q($res['name'])."</option>";
            }
        }	
	$tool_content .= "</select></td></tr>
	<tr>
	  <th>&nbsp;</th>
	  <td class='left'><input type='submit' name='submitWork' value='".q($langSend)."' />&nbsp;
	  $dropbox_lang[mailtousers]<input type='checkbox' name='mailing' value='1' checked /></td>
	</tr>
        </table>
        </fieldset>
	<input type='hidden' name='authors' value='".q(uid_to_name($uid))."' />
        </form>
	<p class='right smaller'>$langMaxFileSize ".ini_get('upload_max_filesize')."</p>";
}

/*
 * --------------------------------------
 * RECEIVED FILES LIST:  TABLE HEADER
 * --------------------------------------
 */
if (!$displayall) {
    if (!$display_outcoming) {    
            if (!isset($_GET['mailing'])) {
                    $numberDisplayed = count($dropbox_person -> receivedWork);
                    $tool_content .= "<p class='sub_title1'>$langDelList";
                    // check if there are received documents. If yes then display the icon deleteall
                    $dr_unid = urlencode($dropbox_unid);
                    if ($numberDisplayed > 0) {
                            $dr_lang_all = addslashes($dropbox_lang["all"]);
                            $tool_content .= "&nbsp;<a href='dropbox_submit.php?course=$code_cours&amp;deleteReceived=all&amp;dropbox_unid=$dr_unid' onClick=\"return confirmation();\">
                            <img src='$themeimg/delete.png' title='".q($langDelete)."' /></a>";
                    }
                    $tool_content .= "</p>";

             /*
             * --------------------------------------
             * RECEIVED FILES LIST
             * --------------------------------------
             */

                $tool_content .= "
                    <script type='text/javascript' src='../auth/sorttable.js'></script>
                    <table width='100%' class='sortable' id='messages_tbl{$messages_type}{$cours_id}'>
                    <thead>
                        <tr>
                            <th width='350'>$dropbox_lang[file]</th>
                            <th width='130'>$langSender</th>
                            <th width='100'>$langDate</th>
                            <th width='20'>$langDelete</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    </table>";
            }
        /*
         * --------------------------------------
         * SENT FILES LIST:  TABLE HEADER
         * --------------------------------------
         */
    } else {          
        $numSent = count($dropbox_person -> sentWork);
        $tool_content .= "<p class='sub_title1'>$langDelList";        
        // if the user has sent files then display the icon deleteall
        if ($numSent > 0) {
            $tool_content .= "&nbsp;<a href='dropbox_submit.php?course=$code_cours&amp;deleteSent=all&amp;dropbox_unid=".urlencode($dropbox_unid)."'
                    onClick=\"return confirmation();\"><img src='$themeimg/delete.png' title='".q($langDelete)."' /></a>";
        }
        $tool_content .= "</p>";

            /*
             * --------------------------------------
             * SENT FILES LIST
             * --------------------------------------
             */

        $tool_content .= "
            <script type='text/javascript' src='../auth/sorttable.js'></script>
            <table width=100% class='sortable' id='messages_tbl{$messages_type}{$cours_id}'>
            <thead>
                <tr>
                    <th>$dropbox_lang[file]</th>
                    <th width='130'>$dropbox_lang[col_recipient]</th>
                    <th width='100'>$langDate</th>
                    <th width='10'>$langDelete</th>
                 </tr>
             </thead>
             <tbody>
             </tbody>
             </table>";
      
        }  
} else { // display all user files sent and received (only to course admin)
                       
    $tool_content .= "<br /><p class='sub_title1'>";
    $tool_content .= $langOtherDropBoxFiles;                
    $tool_content .= "</p>";

    $tool_content .= "
        <script type='text/javascript' src='../auth/sorttable.js'></script>
        <table width=100% class='sortable' id='messages_tbl{$messages_type}{$cours_id}'>
        <thead>    
            <tr>
                <th>$dropbox_lang[file]</th>
                <th width='65'>$langSender</th>
                <th width='65'>$dropbox_lang[col_recipient]</th>
                <th width='100'>$langDate</th>
                <th width='20'>$langDelete</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        </table>";

}
draw($tool_content, 2, null, $head_content);
