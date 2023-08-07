<?php

$require_login = TRUE;


require_once '../../../../../include/baseTheme.php';
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'include/sendMail.inc.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

load_js('bootstrap-datetimepicker');
load_js('select2');

$head_content .= "<script type='text/javascript'>
    $(document).ready(function () {
        $('#select-users').select2();
    });
    </script>
    <script type='text/javascript' src='{$urlAppend}js/tools.js'></script>\n
";

if(isset($_GET['group_id']) and intval(getDirectReference($_GET['group_id'])) != 0){
    put_session_group_id_in_db_and_get_this_after_logout($uid,$mentoring_program_code,getDirectReference($_GET['group_id']));
    $_SESSION['mentoring_group_id'] = getDirectReference($_GET['group_id']);
}

//space of group id
if(isset($_GET['group_id'])){

    if(intval(getDirectReference($_GET['group_id'])) != 0){
        $data['group_id'] = $group_id = getDirectReference($_GET['group_id']);

        $check_group = Database::get()->queryArray("SELECT *FROM mentoring_group WHERE id = ?d",$group_id);
        if(count($check_group) == 0){
            redirect_to_home_page("modules/mentoring/programs/group/select_group.php");
        }
    }else{
        after_reconnect_go_to_mentoring_homepage();
    }

    $data['is_mentee'] = $is_mentee = check_if_uid_is_mentee_for_current_group($uid,$group_id);
    $data['is_editor_current_group'] = $is_editor_current_group = get_editor_for_current_group($uid,$group_id);
    $data['is_tutor_of_mentoring_program'] = $is_tutor_of_mentoring_program = show_mentoring_program_editor($mentoring_program_id,$uid);

    //only mentee,tutor,editor of group can view group
    if($is_mentee or $is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin){

        $toolName = $langAnnouncements.' ('.get_name_for_current_group($group_id).')';

        $checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                        WHERE id = ?d 
                                                        AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->common;

        if($checkIsCommon == 1){
            $data['isCommonGroup'] = 1;
        }else{
            $data['isCommonGroup'] = 0;
        }


        $data['selected_email'] = 'selected';
        $data['announce_id'] = '';
        $data['checked_public'] = 'checked';
        $data['start_checkbox'] = '';
        $data['end_checkbox'] = '';
        $data['showFrom'] = '';
        $data['end_disabled'] = '';
        $data['showUntil'] = '';
        $data['titleToModify'] = '';
        $contentToModify = '';

        // submit announcement
        if (isset($_POST['submitAnnouncement'])) {

            $v = new Valitron\Validator($_POST);
            $v->rule('required', array('antitle'));
            $v->labels(array('antitle' => "$langTheField $langAnnTitle"));
            if (isset($_POST['startdate_active'])) {
                $v->rule('required', array('startdate'));
                $v->labels(array('startdate' => "$langTheField $langStartDate"));
            }
            if (isset($_POST['enddate_active'])) {
                $v->rule('required', array('enddate'));
                $v->labels(array('enddate' => "$langTheField $langEndDate"));
            }
            if ($v->validate()) {
                $datetime = format_locale_date(time(), 'short');
                if (isset($_POST['show_public'])) {
                    $is_visible = 1;
                } else {
                    $is_visible = 0;
                }

                $antitle = $_POST['antitle'];
                $newContent = purify($_POST['newContent']);
                $send_mail = isset($_POST['recipients']) && (count($_POST['recipients'])>0);
                if (isset($_POST['startdate_active']) && isset($_POST['startdate']) && !empty($_POST['startdate'])) {
                    $start_display = $_POST['startdate'];
                } else {
                    $start_display = null;
                }
                if (isset($_POST['enddate_active']) && isset($_POST['enddate']) && !empty($_POST['enddate'])) {
                    $now = date('Y-m-d H:i', strtotime('now'));
                    if($_POST['enddate'] < $now){
                        Session::flash('message',"$invalidDate");
                        Session::flash('alert-class', 'alert-danger');
                        redirect_to_home_page("modules/mentoring/programs/group/announcements/index.php?group_id=".getInDirectReference($group_id));
                    }else{
                        $stop_display = $_POST['enddate'];
                    }
                    
                } else {
                    $stop_display = null;
                }

                if (!empty($_POST['id'])) {// for edit announcement
                    $id = $_POST['id'];
                    Database::get()->query("UPDATE mentoring_announcement
                            SET content = ?s,
                                title = ?s,
                                `date` = " . DBHelper::timeAfter() . ",
                                start_display = ?t,
                                stop_display = ?t,
                                visible = ?d
                            WHERE id = ?d",
                        $newContent, $antitle, $start_display, $stop_display, $is_visible, $id);

                    Database::get()->query("DELETE FROM mentoring_announcement_user WHERE announcement_id IN (?d)",$id);

                    if(isset($_POST['recipients']) && (count($_POST['recipients'])>0)){
                        foreach($_POST['recipients'] as $user_reciever){
                            Database::get()->query("INSERT INTO mentoring_announcement_user
                                                            SET announcement_id = ?d,
                                                                toUser = ?d",$id,$user_reciever);
                        }
                    }
                    
                    $message = $langAnnModify;
                } else { // add new announcement
                    if(isset($_POST['recipients']) && (count($_POST['recipients'])>0)){
                        
                        $id = Database::get()->query("INSERT INTO mentoring_announcement
                                                            SET content = ?s,
                                                                title = ?s, 
                                                                `date` = " . DBHelper::timeAfter() . ",
                                                                mentoring_program_id = ?d,
                                                                group_id = ?d,
                                                                `order` = 0,
                                                                start_display = ?t,
                                                                stop_display = ?t,
                                                                visible = ?d", $newContent, $antitle, $mentoring_program_id, $group_id, $start_display, $stop_display, $is_visible);
                        
                        foreach($_POST['recipients'] as $user_reciever){
                            Database::get()->query("INSERT INTO mentoring_announcement_user
                                                            SET announcement_id = ?d,
                                                                toUser = ?d",$id->lastInsertID,$user_reciever);
                        }
                        $message = $langAnnAdd;
                    }
                    
                }

                // send email
                if ($send_mail and $is_visible == 1) {
                    $title = $antitle;
                    $recipients_emaillist = "";
                    if ($_POST['recipients'][0] == -1) { // all users
                        $cu = Database::get()->queryArray("SELECT cu.user_id FROM mentoring_group_members cu
                                                                    JOIN user u ON cu.user_id=u.id
                                                                WHERE cu.group_id = ?d
                                                                AND u.email <> ''
                                                                AND u.email IS NOT NULL", $group_id);
                        if (count($cu) > 0) {
                            foreach ($cu as $re) {
                                $recipients_emaillist .= (empty($recipients_emaillist)) ? "'$re->user_id'" : ",'$re->user_id'";
                            }
                        }
                    } else { // selected users
                        foreach ($_POST['recipients'] as $re) {
                            $recipients_emaillist .= (empty($recipients_emaillist)) ? "'$re'" : ",'$re'";
                        }
                    }
                    if (!empty($recipients_emaillist)) {
                        $groupName = get_name_for_current_group($group_id);
                        $emailHeaderContent = "
                            <!-- Header Section -->
                            <div id='mail-header'>
                                <br>
                                <div>
                                    <div id='header-title'>$langAnnHasPublishedGroup <a href='{$urlServer}modules/mentoring/programs/group/group_space.php?group_id=".getInDirectReference($group_id)."'>" . $groupName . "</a>.</div>
                                    <ul id='forum-category'>
                                        <li><span><b>$langSender:</b></span> <span class='left-space'>" . q($_SESSION['givenname']) . " " . q($_SESSION['surname']) . "</span></li>
                                        <li><span><b>$langDate:</b></span> <span class='left-space'>$datetime</span></li>
                                    </ul>
                                </div>
                            </div>";

                        $emailBodyContent = "
                            <!-- Body Section -->
                            <div id='mail-body'>
                                <br>
                                <div><b>$langSubject:</b> <span class='left-space'>" . q($_POST['antitle']) . "</span></div>
                                <br>
                                <div><b>$langMailBody</b></div>
                                <div id='mail-body-inner'>
                                    $newContent
                                </div>
                                
                            </div>";

                        $emailFooterContent = "";

                        $emailContent = $emailHeaderContent . $emailBodyContent . $emailFooterContent;

                        $emailSubject = "$groupName - $langAnnouncement";
                        // select students email list
                        $countEmail = 0;
                        $invalid = 0;
                        $recipients = array();
                        $emailBody = html2text($emailContent);
                        $general_to = 'Members of group ' . $groupName;
                        Database::get()->queryFunc("SELECT mentoring_group_members.user_id as id, user.email as email
                                                            FROM mentoring_group_members, user
                                                            WHERE group_id = ?d AND user.id IN ($recipients_emaillist) AND
                                                            mentoring_group_members.user_id = user.id", function ($person)
                        use (&$countEmail, &$recipients, &$invalid, $group_id, $general_to, $emailSubject, $emailBody, $emailContent, $charset) {
                            $countEmail++;
                            $emailTo = $person->email;
                            $user_id = $person->id;
                            // check email syntax validity
                            if (!valid_email($emailTo)) {
                                $invalid++;
                            } 
                            elseif (mentoring_get_user_email_notification($user_id)) {
                                // checks if user is notified by email
                                array_push($recipients, $emailTo);
                            }
                            // send mail message per 50 recipients
                            if (count($recipients) >= 50) {
                                send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], $general_to, $recipients, $emailSubject, $emailBody, $emailContent);
                                $recipients = array();
                            }
                        }, $group_id);
                        if (count($recipients) > 0) {
                            send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], $general_to, $recipients, $emailSubject, $emailBody, $emailContent);
                        }
                    
                        Session::flash('message',"$langAnnAddWithEmail $countEmail $langRegUserGroup");
                        Session::flash('alert-class', 'alert-success');
                        if ($invalid > 0) { // info about invalid emails (if exist)
                            Session::flash('message',"$langInvalidMail $invalid");
                            Session::flash('alert-class', 'alert-warning');
                        }
                    }
                } else {
                    if($is_visible == 0){
                        Session::flash('message',$langEmailNotSendedByInvisibleAnn);
                        Session::flash('alert-class', 'alert-warning');
                    }else{
                        Session::flash('message',$message);
                        Session::flash('alert-class', 'alert-warning');
                    }
                    
                }
                redirect_to_home_page("modules/mentoring/programs/group/announcements/index.php?group_id=".getInDirectReference($group_id));
            } 
        }

        // Create or edit announcement
        elseif (isset($_GET['modify'])) {
            $announce = Database::get()->querySingle("SELECT * FROM mentoring_announcement WHERE id=?d", getDirectReference($_GET['modify']));
            if ($announce) {
                $data['announce_id'] = $announce->id;
                $contentToModify = Session::has('newContent') ? Session::get('newContent') : $announce->content;
                $data['titleToModify'] = Session::has('antitle') ? Session::get('antitle') : q($announce->title);
                if ($announce->start_display) {
                    $data['showFrom'] = $announce->start_display;
                }
                if ($announce->stop_display) {
                    $data['showUntil'] = $announce->stop_display;
                }
                
                $data['allRecievers'] = Database::get()->queryArray("SELECT toUser FROM mentoring_announcement_user WHERE announcement_id = ?d",$announce->id);

            }

            $langAdd = $pageName = $langModifAnn;
            $data['checked_public'] = $announce->visible ? 'checked' : '';
            $data['selected_email'] = '';
            if (!is_null($announce->start_display)) {
                // $showFrom is set earlier
                $data['start_checkbox'] = 'checked';
                $data['start_text_disabled'] = '';
                $data['end_disabled'] = "";
                if (!is_null($announce->stop_display)) {
                    // $data['showUntil'] is set earlier
                    $data['end_checkbox'] = 'checked';
                    $data['end_text_disabled'] = '';
                } else {
                    $data['showUntil'] = '';
                    $data['end_checkbox'] = '';
                    $end_text_disabled = 'disabled';
                }
            } else {
                $data['start_checkbox'] = '';
                $start_text_disabled = 'disabled';
                $data['end_checkbox'] = '';
                $data['end_disabled'] = 'disabled';
                $end_text_disabled = 'disabled';
                $data['showFrom'] = '';
                $data['showUntil'] = '';
            }

        } elseif(isset($_POST['delete_announce'])){
            Database::get()->query("DELETE FROM mentoring_announcement WHERE id = ?d",$_POST['announcement_del_id']);
            Session::flash('message',"$AnnDelSuccess");
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/mentoring/programs/group/announcements/index.php?group_id=".getInDirectReference($group_id));
        } 
        
        $navigation[] = array('url' => "index.php?group_id=".getInDirectReference($group_id)."", 'name' => $langAnnouncements);

        $antitle_error = Session::getError('antitle', "<span class='help-block'>:message</span>");
        $data['startdate_error'] = Session::getError('startdate', "<span class='help-block'>:message</span>");
        $data['enddate_error'] = Session::getError('enddate', "<span class='help-block'>:message</span>");

        $data['antitle_error'] = ($antitle_error ? " has-error" : "");
        $data['contentToModify'] = rich_text_editor('newContent', 4, 20, $contentToModify);

        $data['group_users'] = Database::get()->queryArray("SELECT cu.user_id, CONCAT(u.surname, ' ', u.givenname) name, u.email
            FROM mentoring_group_members cu
            JOIN user u ON cu.user_id=u.id
            WHERE cu.group_id = ?d
            AND u.email<>''
            AND u.email IS NOT NULL ORDER BY u.surname, u.givenname", $group_id);

        $data['startdate_error'] = $data['startdate_error'] ? " has-error" : "";
        $data['enddate_error'] = $data['enddate_error'] ? " has-error" : "";
        $data['submitUrl'] = $urlAppend . 'modules/mentoring/programs/group/announcements/submit.php?group_id='.getInDirectReference($group_id);

        if(!$is_mentee){
            $data['allAnnouncements'] = Database::get()->queryArray("SELECT *FROM mentoring_announcement 
                                                                 WHERE mentoring_program_id = ?d 
                                                                 AND group_id = ?d ORDER BY `date` DESC",$mentoring_program_id,$group_id);
        }else{
            $now = date('Y-m-d H:i', strtotime('now'));
            
            $data['allAnnouncements'] = Database::get()->queryArray("SELECT *FROM mentoring_announcement 
                                                                 WHERE mentoring_program_id = ?d 
                                                                 AND group_id = ?d 
                                                                 AND visible = ?d
                                                                 AND id IN (SELECT announcement_id FROM mentoring_announcement_user WHERE toUser = ?d OR toUser = ?d)
                                                                 AND (start_display <= NOW() OR start_display IS NULL) 
                                                                 AND (stop_display >= NOW() OR stop_display IS NULL)
                                                                 ORDER BY `date` DESC",$mentoring_program_id,$group_id,1,-1,$uid);
        }
        

       

       if(!isset($_GET['modify'])){//index
            if(isset($_GET['show_an_id'])){//show
                if(intval(getDirectReference($_GET['show_an_id'])) != 0){
                    $data['announcementShow'] = Database::get()->queryArray("SELECT *FROM mentoring_announcement WHERE id = ?d",getDirectReference($_GET['show_an_id']));
                    $data['action_bar'] = action_bar([
                        [ 'title' => trans('langBackPage'),
                            'url' => $urlServer.'modules/mentoring/programs/group/announcements/index.php?group_id='.getInDirectReference($group_id),
                            'icon' => 'fa-chevron-left',
                            'level' => 'primary-label',
                            'button-class' => 'backButtonMentoring' ]
                        ], false);
                    view('modules.mentoring.programs.group.announcements.show', $data);
                }else{
                    redirect_to_home_page("modules/mentoring/programs/group/group_space.php?space_group_id=".getIndirectReference($group_id));
                }
            }else{
                $data['action_bar'] = action_bar([
                [ 'title' => trans('langBackPage'),
                    'url' => $urlServer.'modules/mentoring/programs/group/group_space.php?space_group_id='.getInDirectReference($group_id),
                    'icon' => 'fa-chevron-left',
                    'level' => 'primary-label',
                    'button-class' => 'backButtonMentoring' ]
                ], false);
                view('modules.mentoring.programs.group.announcements.index', $data);
            }
            
       }else{//edit
            if(isset($_GET['modify']) and intval(getDirectReference($_GET['modify'])) != 0){
                $data['action_bar'] = action_bar([
                [ 'title' => trans('langBackPage'),
                    'url' => $urlServer.'modules/mentoring/programs/group/announcements/index.php?group_id='.getInDirectReference($group_id),
                    'icon' => 'fa-chevron-left',
                    'level' => 'primary-label',
                    'button-class' => 'backButtonMentoring' ]
                ], false);
                view('modules.mentoring.programs.group.announcements.edit', $data);
            }else{
                redirect_to_home_page("modules/mentoring/programs/group/group_space.php?space_group_id=".getIndirectReference($group_id));
            }
        }
      
        
    }else{
        redirect_to_home_page("modules/mentoring/programs/show_programs.php");
    }
    
}






/**
 * @brief checks if user is notified via email from a given course
 * @param type $user_id
 * @param type $course_id
 * @return boolean
 */
function mentoring_get_user_email_notification($user_id) {
    // check if user is active
    if (Database::get()->querySingle('SELECT expires_at < NOW() AS expired FROM user WHERE id = ?d', $user_id)->expired) {
        return false;
    }

    // check if user's email address is verified
    if (get_config('email_verification_required') && get_config('dont_mail_unverified_mails')) {
        $verified_mail = get_mail_ver_status($user_id);
        if ($verified_mail == EMAIL_VERIFICATION_REQUIRED or $verified_mail == EMAIL_UNVERIFIED) {
            return false;
        }
    }

    return true;
}