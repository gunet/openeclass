<?php

/* ========================================================================
 * Open eClass 
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
 * ======================================================================== 
 */

$require_current_course = TRUE;
$require_login = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
$coursePath = $webDir . '/courses/';

$toolName = $langChat;

load_js('tools.js');
load_js('validation.js');

$available_themes = active_subdirs("$webDir/template", 'theme.html');

if (isset($_GET['add_conference'])) {
    $pageName = $langAdd;
    $textarea = rich_text_editor('description', 4, 20, '');
    
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "index.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
    
    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form class='form-horizontal' role='form' name='confForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
    $tool_content .= "<fieldset>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='title' class='col-sm-2 control-label'>$langTitle:</label>";
    $tool_content .= "<div class='col-sm-10'>";
    $tool_content .= "<input class='form-control' type='text' name='title' id='title' placeholder='$langTitle' size='50' />";
    $tool_content .= "</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='description' class='col-sm-2 control-label'>$langDescr:</label>";
    $tool_content .= "<div class='col-sm-10'>";
    $tool_content .= "$textarea";
    $tool_content .= "</div>";
    $tool_content .= "</div>";   
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label class='col-sm-2 control-label'>$langActivate</label>
        <div class='col-sm-10 radio'><label><input type='radio' id='enabled_true' name='status' checked value='active'>$langYes</label></div>
        <div class='col-sm-offset-2 col-sm-10 radio'><label><input type='radio' id='enabled_false' name='status' value='inactive'>$langNo</label></div>            
    </div>";
    $tool_content .= "<input type = 'hidden' name = 'course_id' value='$course_id'>";

    $tool_content .= "<div class='col-sm-offset-2 col-sm-10'><input class='btn btn-primary' type='submit' name='submit' value='$langAddModify'></div>";
    $tool_content .= "</fieldset></form></div>";
    $tool_content .='<script language="javaScript" type="text/javascript">
        //<![CDATA[
            var chkValidator  = new Validator("confForm");
            chkValidator.addValidation("title","req","'.$langChatTitleError.'");
        //]]></script>';

} else if (isset($_GET['delete_conference'])) {
    $id = $_GET['delete_conference'];
    Database::get()->querySingle("DELETE FROM conference WHERE conf_id=?d", $id);
    $fileChatName = $coursePath . $course_code . '/'.$id.'_chat.txt';
    $tmpArchiveFile = $coursePath . $course_code . '/'.$id.'_tmpChatArchive.txt';

    if(file_exists($fileChatName))
       unlink($fileChatName);
    if(file_exists($tmpArchiveFile))
        unlink($tmpArchiveFile);
        
    Session::Messages($langChatDeleted,"alert-success");
    redirect_to_home_page("modules/conference/index.php");
    
}
else if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    if (isset($_POST['conference_id'])) {
    $conf_id = $_POST['conference_id'];
        Database::get()->querySingle("UPDATE conference SET conf_title= ?s,conf_description = ?s,
                status = ?s
                WHERE conf_id =?d", $title, $description, $status, $conf_id);
    } else {
        $course_id = $_POST['course_id'];
        Database::get()->querySingle("INSERT INTO conference (course_id,conf_title,conf_description,status) VALUES
        (?d,?s,?s,?s)", $course_id,$title,$description,$status);
    }    
    // Display result message
    Session::Messages($langAttendanceEdit,"alert-success");
    redirect_to_home_page("modules/conference/index.php");
    
} // end of if($submit)
else {    
    if (isset($_GET['edit_conference'])) {
        $pageName = $langEdit;
        $conf_id = $_GET['edit_conference'];
        $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "index.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
        
        $conf = Database::get()->querySingle("SELECT * FROM conference WHERE conf_id = ?d", $conf_id);
        $textarea = rich_text_editor('description', 4, 20, $conf->conf_description);

        $tool_content .= "<div class='form-wrapper'>";
        $tool_content .= "<form class='form-horizontal' role='form' name='confForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
        $tool_content .= "<fieldset>";        
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='title' class='col-sm-2 control-label'>$langTitle:</label>";
        $tool_content .= "<div class='col-sm-10'>";
        $tool_content .= "<input class='form-control' type='text' name='title' id='title' value='$conf->conf_title' size='50' />";
        $tool_content .= "</div>";        
        $tool_content .= "</div>";        
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='desc' class='col-sm-2 control-label'>$langDescr:</label>";
        $tool_content .= "<div class='col-sm-10'>";
        $tool_content .= "$textarea";
        $tool_content .= "</div>";
        $tool_content .= "</div>";         
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label class='col-sm-2 control-label'>$langActivate</label>";
        if ($conf->status == "active") {
            $checkedtrue2 = " checked='false' ";
        } else $checkedtrue2 = '';
        
        $tool_content .= "<div class='col-sm-10 radio'>
                <label><input type='radio' id='enabled_true' name='status' $checkedtrue2 value='active'>$langYes</label></div>";
        
        if ($conf->status == "inactive") {
            $checkedfalse2 = " checked='false' ";
        } else $checkedfalse2 = '';
        
        $tool_content .= "<div class='col-sm-offset-2 col-sm-10 radio'>
                            <label><input type='radio' id='enabled_false' name='status' $checkedfalse2 value='inactive'>$langNo</label>
                        </div>
        </div>";
     
        $tool_content .= "<input type = 'hidden' name = 'conference_id' value='$conf_id'>";
        $tool_content .= "<div class='col-sm-offset-2 col-sm-10'><input class='btn btn-primary' type='submit' name='submit' value='$langSubmit'></div>";
        $tool_content .= "</fieldset></form></div>";
        $tool_content .='<script language="javaScript" type="text/javascript">
                //<![CDATA[
                    var chkValidator  = new Validator("confForm");
                    chkValidator.addValidation("title","req","'.$langChatTitleError.'");
                //]]></script>';
                    
    } else {
        //display available conferences
        if ($is_editor)
        {
            $tool_content .= action_bar(array(
                array('title' => $langAdd,
                    'url' => "index.php?add_conference",
                    'icon' => 'fa-plus-circle',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success')));
            
            $q = Database::get()->queryArray("SELECT * FROM conference WHERE course_id=?d ORDER BY conf_id DESC",$course_id);
        } else {
            $q = Database::get()->queryArray("SELECT * FROM conference WHERE course_id=?d AND status = 'active' ORDER BY conf_id DESC",$course_id);
        }
        if (count($q)>0) {
            $tool_content .= "<div class='table-responsive'>";
            $tool_content .= "<table class='table-default'>
                <thead>
                    <tr class='list-header'>
                        <th>$langChat</th>
                        <th class = 'text-center' width='150'>$langNewBBBSessionStatus</th>
                        <th class = 'text-center' width='200'>$langStartDate</th>";
                    
            if($is_editor){
                $tool_content .= "<th class = 'text-center'>".icon('fa-gears')."</th>"; 
            }
            $tool_content .="</tr></thead>";
            foreach ($q as $conf) {
                $enabled_conference = ($conf->status == 'active')? "<span class='text-success'><span class='fa fa-eye'></span> $langAdminAnVis</span>" : "<span class='text-danger'><span class='fa fa-eye-slash'></span> $langAdminAnNotVis</span>";
                ($conf->status == 'active')? $tool_content .= "<tr>" : $tool_content .= "<tr class='not_visible'>";
                $tool_content .= "<td>";
                ($conf->status == 'active')? $tool_content .= "<a href='./conference.php?conference_id=$conf->conf_id'>$conf->conf_title</a>" : $tool_content .= $conf->conf_title;
                $tool_content .= "<div style='font-size:smaller; padding-top: 10px;'>$conf->conf_description</div>";
                $tool_content .= "</td>";
                $tool_content .= "<td class='text-center'>$enabled_conference</td>";
                $tool_content .= "<td class='text-center'>".claro_format_locale_date($dateTimeFormatShort, strtotime($conf->start))."</td>";
                if($is_editor)
                {
                    $tool_content .= "<td class='option-btn-cell'>".action_button(array(
                                                        array('title' => $langEdit,
                                                              'url' => "$_SERVER[SCRIPT_NAME]?edit_conference=$conf->conf_id",
                                                              'icon' => 'fa-edit'),
                                                        array('title' => $langDelete,
                                                              'url' => "$_SERVER[SCRIPT_NAME]?delete_conference=$conf->conf_id",
                                                              'icon' => 'fa-times',
                                                              'class' => 'delete',
                                                              'confirm' => $langConfirmDelete)
                                                        ))."</td>";
                }
                    $tool_content .= "</tr>";
            }
            $tool_content .= "</table></div>";
        } else {
             $tool_content .= "<div class='alert alert-warning'>$langNoChatAvailable</div>";
        }
    }
}

draw($tool_content, 2, null, $head_content);
