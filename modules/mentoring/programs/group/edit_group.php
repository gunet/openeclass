<?php

$require_login = TRUE;


require_once '../../../../include/baseTheme.php';
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

load_js('select2');

$head_content .= "<script type='text/javascript'>
    $(document).ready(function () {
        $('#select-tutor').select2();
    });
    </script>
    <script type='text/javascript' src='{$urlAppend}js/tools.js'></script>\n
";

if(isset($_GET['group_id']) and intval(getDirectReference($_GET['group_id'])) != 0){
    put_session_group_id_in_db_and_get_this_after_logout($uid,$mentoring_program_code,getDirectReference($_GET['group_id']));
}

$message = '';
// Once modifications have been done, the user validates and arrives here
if (isset($_POST['modify'])) {
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('name'));
    $v->rule('required', array('maxStudent'));
    $v->rule('numeric', array('maxStudent'));
    $v->rule('min', array('maxStudent'), 0);
    $v->labels(array(
        'name' => "$langTheField $langNewGroups",
        'maxStudent' => "$langTheField $langMax $langGroupPlacesThis"
    ));
    if($v->validate() and ((!isset($_POST['ingroup'])) or (count($_POST['ingroup']) <= $_POST['maxStudent']) or (isset($_POST['ingroup']) and $_POST['maxStudent'] == 0))) {
        $self_reg = $self_request = $allow_unreg = $has_forum = $documents = $announcements = $wall = 0;

        if (isset($_POST['self_reg']) and $_POST['self_reg'] == 'on') {
            $self_reg = 1;
        }

        if (isset($_POST['self_request']) and $_POST['self_request'] == 'on') {
            $self_request = 1;
        }

        if (isset($_POST['allow_unreg']) and $_POST['allow_unreg'] == 'on') {
            $allow_unreg = 1;
        }

        if (isset($_POST['forum']) and $_POST['forum'] == 'on') {
            $has_forum = 1;
        }

        if (isset($_POST['documents']) and $_POST['documents'] == 'on'){
            $documents = 1;
        }

        if (isset($_POST['announcements']) and $_POST['announcements'] == 'on'){
            $announcements = 1;
        }

        if (isset($_POST['wall']) and $_POST['wall'] == 'on'){
            $wall = 1;
        }

      
        $private_forum = $_POST['private_forum'];
        $group_id = $_POST['group_id'];

        Database::get()->query("UPDATE mentoring_group_properties SET
                                self_registration = ?d,
                                allow_unregister = ?d,
                                forum = ?d,
                                private_forum = ?d,
                                documents = ?d,
                                announcements = ?d,
                                wall = ?d,
                                self_request = ?d WHERE mentoring_program_id = ?d AND group_id = ?d",
            $self_reg, $allow_unreg, $has_forum, $private_forum, $documents, $announcements, $wall, $self_request, $mentoring_program_id, $group_id);

        $group_title = Database::get()->querySingle("SELECT name FROM mentoring_group WHERE mentoring_program_id = ?d AND id = ?d",$mentoring_program_id,$group_id)->name;
        Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_GROUP, MENTORING_LOG_MODIFY, array('uid' => '','group_title' => $group_title,
                                                                                                             'has_forum' => $has_forum, 'has_doc' => $documents, 'type' => 'group_properties'));

        // Update main group settings
        register_posted_variables(array('name' => true, 'description' => true), 'all');
        register_posted_variables(array('maxStudent' => true), 'all');

        Database::get()->query("UPDATE `mentoring_group`
                                        SET name = ?s,
                                            description = ?s,
                                            max_members = ?d
                                        WHERE id = ?d AND mentoring_program_id = ?d", $name, $description, $maxStudent, $group_id, $mentoring_program_id);

        $forumname = "$langForumGroup $name";
        Database::get()->query("UPDATE mentoring_forum SET name = ?s WHERE id =
                            (SELECT forum_id FROM `mentoring_group` WHERE id = ?d)
                                AND mentoring_program_id = ?d", $forumname, $group_id, $mentoring_program_id);

        
        if (isset($_POST['tutor'])) {

            $old_tutors = array();
            $tutors = Database::get()->queryArray("SELECT user_id FROM mentoring_group_members WHERE group_id = ?d AND is_tutor = ?d AND status_request = ?d",$group_id,1,1);
            if(count($tutors) > 0){
                foreach($tutors as $t){
                    $old_tutors[] = $t->user_id;
                }
            }


            Database::get()->query("DELETE FROM mentoring_group_members
                                        WHERE group_id = ?d AND is_tutor = 1 AND status_request = 1", $group_id);
            foreach ($_POST['tutor'] as $tutor_id) {
                $tutor_id = intval($tutor_id);
                Database::get()->query("REPLACE INTO mentoring_group_members SET group_id = ?d, user_id = ?d, is_tutor = 1, status_request = 1", $group_id, $tutor_id);
                Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_GROUP, MENTORING_LOG_MODIFY, array('uid' => display_user($tutor_id,false,false),'group_title' => $group_title, 'type' => 'insert_tutor_group'));
            }

            $new_tutors = array();
            $tutorss = Database::get()->queryArray("SELECT user_id FROM mentoring_group_members WHERE group_id = ?d AND is_tutor = ?d AND status_request = ?d",$group_id,1,1);
            if(count($tutorss) > 0){
                foreach($tutorss as $t){
                    $new_tutors[] = $t->user_id;
                }
            }

            $delTutors = array_diff($old_tutors,$new_tutors);
            if(count($delTutors) > 0){
                foreach($delTutors as $d){
                    $check = Database::get()->querySingle("SELECT COUNT(*) as total FROM  mentoring_group_members 
                                                            WHERE group_id = ?d AND user_id = ?d AND is_tutor = ?d AND status_request = ?d",$group_id,$d,1,1)->total;
                    if($check == 0){
                        Database::get()->query("DELETE FROM mentoring_mentor_availability_group WHERE user_id = ?d AND group_id = ?d AND mentoring_program_id = ?d",$d,$group_id,$mentoring_program_id);
                        Database::get()->query("DELETE FROM mentoring_booking WHERE mentoring_program_id = ?d AND group_id = ?d AND mentor_id = ?d",$mentoring_program_id,$group_id,$d);
                        Database::get()->query("DELETE FROM mentoring_rentezvous WHERE mentoring_program_id = ?d AND group_id = ?d AND mentor_id = ?d",$mentoring_program_id,$group_id,$d);
                    }
                }
            }


        } 
        

        // Delete all members of this group
        $cur_member_ids = [];
        Database::get()->queryFunc("SELECT user_id FROM mentoring_group_members "
                . "WHERE group_id = ?d AND is_tutor = 0 AND status_request = 1",
                function ($group_member) use (&$cur_member_ids) {
                    array_push($cur_member_ids, $group_member->user_id);
                },$group_id);
        if (isset($_POST['ingroup'])) {
            $ids_to_be_inserted = array_diff($_POST['ingroup'], $cur_member_ids);
            $ids_to_be_deleted = implode(', ', array_diff($cur_member_ids, $_POST['ingroup']));
            if ($ids_to_be_deleted) {
                Database::get()->query("DELETE FROM mentoring_group_members
                                            WHERE group_id = ?d AND status_request = 1 AND is_tutor = 0 AND user_id IN ($ids_to_be_deleted)", $group_id);
            }
            foreach ($ids_to_be_inserted as $user_id) {
                $id_to_be_inserted_has_sent_request = Database::get()->queryArray("SELECT *FROM mentoring_group_members
                                                                                    WHERE group_id = ?d
                                                                                    AND user_id = ?d
                                                                                    AND is_tutor = ?d
                                                                                    AND status_request = ?d",$group_id,$user_id,0,0);
                if(count($id_to_be_inserted_has_sent_request) > 0){//user has sent request so update him
                    Database::get()->query("UPDATE mentoring_group_members SET status_request = ?d
                                            WHERE group_id = ?d AND user_id = ?d AND is_tutor = ?d AND status_request = ?d
                                            ", 1, $group_id, $user_id, 0, 0);
                }else{//user hasnt sent request so insert him
                    Database::get()->query("INSERT INTO mentoring_group_members (user_id, group_id, is_tutor, status_request)
                                            VALUES (?d, ?d, ?d ,?d)", $user_id, $group_id, 0, 1);
                }
                Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_GROUP, MENTORING_LOG_MODIFY, array('uid' => display_user($user_id,false,false),'group_title' => $group_title, 'type' => 'insert_mentee_group'));
            }
        } else {
            Database::get()->query("DELETE FROM mentoring_group_members
                                        WHERE group_id = ?d AND is_tutor = 0 AND status_request = 1",$group_id);
        }
        
        
        Session::flash('message',$langGroupSettingsModified); 
        Session::flash('alert-class', 'alert-success');

        redirect_to_home_page("modules/mentoring/programs/group/group_space.php?space_group_id=".getIndirectReference($group_id));

    } else {
        if(count($_POST['ingroup']) > $_POST['maxStudent']){
             Session::flash('message',$langGroupTooManyMembers); 
             Session::flash('alert-class', 'alert-danger');
             $group_id = $_POST['group_id'];
             redirect_to_home_page("modules/mentoring/programs/group/edit_group.php?edit_group=1&group_id=".getIndirectReference($group_id));
        }
       
    }
}








//edit group by tutor or editor of group
if(isset($_GET['edit_group']) and $_GET['edit_group'] == 1){
    
    if(isset($_GET['group_id']) and intval(getDirectReference($_GET['group_id'])) == 0){
        after_reconnect_go_to_mentoring_homepage();
    }else{
        $data['group_id'] = $group_id = getDirectReference($_GET['group_id']);
        $check_group = Database::get()->queryArray("SELECT *FROM mentoring_group WHERE id = ?d",$group_id);
        if(count($check_group) == 0){
            redirect_to_home_page("modules/mentoring/programs/group/select_group.php");
        }
    }

    $toolName = $langEditChange.' -- '.get_name_for_current_group($group_id);
   
    $checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                WHERE id = ?d 
                                                AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->common;

    if($checkIsCommon == 1){
        $data['isCommonGroup'] = 1;
    }else{
        $data['isCommonGroup'] = 0;
    }


    $res = Database::get()->querySingle("SELECT name, description, forum_id, max_members, secret_directory, category_id
                             FROM `mentoring_group` WHERE mentoring_program_id = ?d AND id = ?d", $mentoring_program_id, $group_id);
    
    $data['group_name'] = $group_name = $res->name;
    $data['group_description'] = $group_description = $res->description;
    $data['forum_id'] = $forum_id = $res->forum_id;
    $data['max_members'] = $max_members = $res->max_members;
    $data['secret_directory'] = $secret_directory = $res->secret_directory;

    $group = Database::get()->querySingle("SELECT * FROM mentoring_group_properties WHERE group_id = ?d AND mentoring_program_id = ?d", $group_id, $mentoring_program_id);

    $data['self_reg'] = ($group->self_registration ? 'checked' : '');
    $data['allow_unreg'] = ($group->allow_unregister ? 'checked' : '');
    $data['private_forum_yes'] =($group->private_forum ? 'checked="1"' : '');
    $data['private_forum_no'] = ($group->private_forum ? '' : ' checked="1"');
    $data['has_forum'] = ($group->forum ? 'checked' : '');
    $data['documents'] = ($group->documents ? 'checked' : '');
    $data['announcements'] = ($group->announcements ? 'checked' : '');
    $data['wall'] = ($group->wall ? 'checked' : '');
    $data['self_req'] = ($group->self_request == 1 ? 'checked' : '');

   
}

$data['action_bar'] = action_bar([
    [ 'title' => trans('langBackPage'),
        'url' => $urlServer.'modules/mentoring/programs/group/group_space.php?space_group_id='.getIndirectReference($group_id),
        'icon' => 'fa-chevron-left',
        'level' => 'primary-label',
        'button-class' => 'backButtonMentoring' ]
    ], false);



view('modules.mentoring.programs.group.edit_group', $data);