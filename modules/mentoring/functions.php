<?php


/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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


define('MENTORING_POSTS_PER_PAGE', 10);
define('MENTORING_TOPICS_PER_PAGE', 10);
define('MENTORING_HOT_THRESHOLD', 20);
define('MENTORING_PAGINATION_CONTEXT', 3);

define('MENTORING_POST_MAX_INDENTATION_LEVEL', 4);
define('MENTORING_POSTS_PAGINATION_VIEW_ASC', 0);
define('MENTORING_POSTS_PAGINATION_VIEW_DESC', 1);
define('MENTORING_POSTS_THREADED_VIEW', 2);

// require_once 'modules/mentoring/lib/forcedownload.php';

/**
 * @brief create mentoring_program
 * @param  type  $code
 * @param  type  $lang
 * @param  type  $title
 * @param  type  $tutor
 * @param  type  $start_date
 * @param  type  $end_date
 * @param  type  keywords
 * @param  type  $program_image
 * @param  type  $old_image_program
 * @param  type  $tmp_name
 * @param  type  $size
 * @param string $description
 * @param  array $mentors_ids
 * @return int
 */
function create_mentoring_program($code, $lang, $title, $tutors_ids, $start_date, $end_date, $keywords, $program_image, $description, $mentors_ids, $webDir, $tmp_name, $size, $allow_unreg_mentee_from_program) {

    $uid = $_SESSION['uid'];
    $doc_quota = get_config('doc_quota');
    $group_quota = get_config('group_quota');
    $video_quota = get_config('video_quota');
    $dropbox_quota = get_config('dropbox_quota');

    if(!empty($start_date)){
        $start_date = date('Y-m-d H:i:s',strtotime($start_date));
    }else{
        $start_date = null;
    }

    if(!empty($end_date)){
        $end_date = date('Y-m-d H:i:s',strtotime($end_date));
    }else{
        $end_date = null;
    }

    $result = Database::get()->query("INSERT INTO mentoring_programs SET
                                        code = ?s,
                                        lang = ?s,
                                        title = ?s,
                                        public_code = ?s,
                                        visible = ?d,
                                        doc_quota = ?f,
                                        video_quota = ?f,
                                        group_quota = ?f,
                                        dropbox_quota = ?f,
                                        start_date = ?s,
                                        finish_date = ?s,
                                        keywords = ?s,
                                        created = " . DBHelper::timeAfter() . ",
                                        program_image = ?s,
                                        description = ?s,
                                        allow_unreg_mentee = ?d",
                                    $code, $lang, $title, $code, 1,
                                    $doc_quota * 1024 * 1024,
                                    $video_quota * 1024 * 1024, $group_quota * 1024 * 1024,
                                    $dropbox_quota * 1024 * 1024, $start_date, 
                                    $end_date, $keywords,$program_image,$description,$allow_unreg_mentee_from_program);
                                    
    
    if ($result) { 

        
        //SET TUTOR
        foreach($tutors_ids as $t){
            if(!in_array($t,$mentors_ids)){
                $user_uid_status = Database::get()->querySingle("SELECT status FROM user WHERE id = ?d",$t)->status;
                $result2 = Database::get()->query("INSERT INTO mentoring_programs_user SET 
                    mentoring_program_id = $result->lastInsertID,
                    user_id = $t,
                    status = $user_uid_status,
                    tutor = 1,
                    reg_date = " . DBHelper::timeAfter() . " ,
                    mentor = 0");
            }
        }

        //SET MENTORS and TUTOR OF PROGRAM
        $tutor_value = '';
        $mentor_value = '';
        $user_status = '';
        $values = array();
        $rowvalues = array();
        foreach($mentors_ids as $mentor){
            //if tutor is a mentor
            $user_status = Database::get()->querySingle("SELECT status FROM user WHERE id = ?d",$mentor)->status;
            //Finally, a simple user can be a mentor
            if($user_status == USER_STUDENT){
                $user_status = USER_TEACHER;
            }
            if(in_array($mentor,$tutors_ids)){
                $tutor_value = 1;
                $mentor_value = 1;
            }else{
                $tutor_value = 0;
                $mentor_value = 1;
            }
            $rowvalues = array($result->lastInsertID,$mentor,$user_status,$tutor_value,$mentor_value);
            $values[] = "('".implode("','",$rowvalues)."')";
        }
        $FinalListValues = implode(', ', $values);
        $result2 = Database::get()->query("INSERT IGNORE INTO mentoring_programs_user (mentoring_program_id,user_id,status,tutor,mentor) VALUES $FinalListValues");
        $result2 = Database::get()->query("UPDATE mentoring_programs_user SET reg_date = " . DBHelper::timeAfter() ." WHERE mentoring_program_id = ?d", $result->lastInsertID);
      
        if($result2){
            
            if (!create_mentoring_program_dirs($code)) {
                return false;
            }

            mentoring_program_index($code);

            if(!empty($program_image)){
                $target_dir = "$webDir/mentoring_programs/$code/image/";
                $target_file = $target_dir . my_basename($program_image);
                $uploadOk = 1;
                $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
                upload_mentoring_program_image($target_file, $target_dir,$imageFileType, $uploadOk, $tmp_name,$size, $webDir, $program_image, $old_image_program);
            }

            return $result->lastInsertID;
        }
       

    } else {
        return 0;
    }

}


/**
 * @brief update mentoring_program
 * @param  type  $code
 * @param  type  $public_code
 * @param  type  $lang
 * @param  type  $title
 * @param  type  $tutor
 * @param  type  $start_date
 * @param  type  $end_date
 * @param  type  keywords
 * @param  type  $program_image
 * @param  type  $tmp_name
 * @param  type  $size
 * @param  type  $old_image_program
 * @param string $description
 * @param  array $mentors_ids
 * @param  array $old_tutors
 * @return int
 */
function update_mentoring_program($code, $public_code, $lang, $title, $tutor, $start_date, $end_date, $keywords, $program_image, $description, $mentors_ids, $webDir, $existing_program_id, $uuserid, $old_image_program, $tmp_name, $size,$old_tutors,$allow_unreg_mentee_from_program) {

    global $uid,$is_admin;
    //$uid = $_SESSION['uid'];

    if(!empty($start_date)){
        $start_date = date('Y-m-d H:i:s',strtotime($start_date));
    }else{
        $start_date = null;
    }

    if(!empty($end_date)){
        $end_date = date('Y-m-d H:i:s',strtotime($end_date));
    }else{
        $end_date = null;
    }

    $result_edit = Database::get()->query("UPDATE mentoring_programs SET
                                        public_code = ?s,
                                        lang = ?s,
                                        title = ?s,
                                        visible = ?d,
                                        start_date = ?s,
                                        finish_date = ?s,
                                        keywords = ?s,
                                        created = " . DBHelper::timeAfter() . ",
                                        program_image = ?s,
                                        description = ?s,
                                        allow_unreg_mentee = ?d WHERE id = ?d AND code = ?s",
                            $public_code, $lang, $title, 1,
                            $start_date, 
                            $end_date, $keywords, $program_image, $description, $allow_unreg_mentee_from_program, $existing_program_id, $code);

    /////////////////////// about edit tutors /////////////////////////////////////////////// 
    $old_tutors = array();
    $old_tutors = Database::get()->queryArray("SELECT mentoring_programs_user.user_id 
                                                FROM mentoring_programs_user 
                                                LEFT JOIN mentoring_programs 
                                                ON mentoring_programs_user.mentoring_program_id = mentoring_programs.id 
                                                WHERE mentoring_programs_user.mentoring_program_id = ?d 
                                                AND mentoring_programs_user.tutor = ?d",$existing_program_id, 1);

    $old_t = array();
    foreach($old_tutors as $old){
        $old_t[] = $old->user_id;
    } 

    foreach($old_t as $old_tutor){
        if(!in_array($old_tutor,$tutor)){
           //if new tutor doesnt exist in old tutors then check if old tutor is mentor.
           // If is mentor dont delete him just update tutor flag to equals with 0.
           // else delete him
           $checkIfOldTutorIsMentorBeforeDeleteHim = Database::get()->querySingle("SELECT COUNT(user_id) AS ui FROM mentoring_programs_user 
                                WHERE mentoring_program_id = ?d AND user_id = ?d AND tutor = ?d AND mentor = ?d", $existing_program_id, $old_tutor, 1,1)->ui;
            // is tutor_mentor
            if($checkIfOldTutorIsMentorBeforeDeleteHim > 0){
               Database::get()->query("UPDATE mentoring_programs_user SET tutor = ?d
                                        WHERE mentoring_program_id = ?d AND user_id = ?d AND mentor = ?d",0,$existing_program_id,$old_tutor,1);

                //update him as simple user mentor in common group
                Database::get()->query("UPDATE mentoring_group_members SET is_tutor = ?d
                                        WHERE group_id IN (SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d AND common = ?d)
                                        AND user_id = ?d",0, $existing_program_id, 1, $old_tutor);

            }else{
                Database::get()->query("DELETE FROM mentoring_programs_user WHERE mentoring_program_id = ?d AND user_id = ?d AND tutor = ?d",$existing_program_id,$old_tutor,1);
                //delete him from common group
                Database::get()->query("DELETE FROM mentoring_group_members 
                                        WHERE group_id IN (SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d AND common = ?d)
                                        AND user_id = ?d",$existing_program_id, 1, $old_tutor);
            }
        }
    }
    
    foreach($tutor as $new_tutor){
        if(!in_array($new_tutor,$old_t)){
            Database::get()->query("INSERT INTO mentoring_programs_user SET 
                                    mentoring_program_id = $existing_program_id, 
                                    user_id = $new_tutor, 
                                    tutor = 1, 
                                    mentor = 0,
                                    reg_date = ". DBHelper::timeAfter() .",
                                    status = 1 ,
                                    is_guided = 0");

            //Add him as tutor in common group
            $theCommonGroupOfProgram = Database::get()->querySingle("SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d AND common = ?d",$existing_program_id,1)->id;
            Database::get()->query("INSERT INTO mentoring_group_members SET
                                    group_id = ?d,
                                    user_id = ?d,
                                    is_tutor = ?d,
                                    status_request = ?d",$theCommonGroupOfProgram, $new_tutor, 1, 1);
        }
    }
    /////////////////////// about edit mentors ///////////////////////////////////////////////                       
    $old_mentors = array();
    $old_mentors = Database::get()->queryArray("SELECT user_id FROM mentoring_programs_user WHERE mentoring_program_id = ?d AND status = ?d AND mentor = ?d",$existing_program_id,1,1);

    $old_m = array();
    foreach($old_mentors as $m){
        $old_m[] = $m->user_id;
    }
    $values = implode(',', $old_m);
    //get tutor ids of program
    $tutors_ids = array();
    $tutor_id = Database::get()->queryArray("SELECT user_id FROM mentoring_programs_user WHERE mentoring_program_id = ?d AND tutor = ?d",$existing_program_id,1);
    foreach($tutor_id as $t){
        $tutors_ids[] = $t->user_id;
    }
    $tutorss = implode(',', $tutors_ids);
    $query = 'DELETE FROM mentoring_programs_user WHERE (user_id) IN ('.$values.') AND (user_id) NOT IN ('.$tutorss.') AND mentoring_program_id = '.$existing_program_id.'';
    $del = Database::get()->query($query);

    if($del){

        //SET MENTORS OF PROGRAM
        $user_status = '';
        $valuesUpdated = array();
        $rowvalues = array();
        foreach($mentors_ids as $mentor){
            //if tutor is a mentor
            $user_status = Database::get()->querySingle("SELECT status FROM user WHERE id = ?d",$mentor)->status;
            //Finally, a simple user can be a mentor
            if($user_status == USER_STUDENT){
                $user_status = USER_TEACHER;
            }
            if(!in_array($mentor,$tutors_ids)){
                $rowvalues = array($existing_program_id,$mentor,$user_status,1);
                $valuesUpdated[] = "('".implode("','",$rowvalues)."')";
            }
        }
        $FinalListUpdatedValues = implode(', ', $valuesUpdated);
        $result2 = Database::get()->query("INSERT IGNORE INTO mentoring_programs_user (mentoring_program_id,user_id,status,mentor) VALUES $FinalListUpdatedValues");
        $result2 = Database::get()->query("UPDATE mentoring_programs_user SET reg_date = " . DBHelper::timeAfter() ." WHERE mentoring_program_id = ?d", $existing_program_id);

        //check uid as tutor or mentor or tutor_mentor
        if(show_mentoring_program_editor($existing_program_id, $uuserid)){
            if(!$is_admin){
                if(in_array($uid,$mentors_ids)){
                    $result_mentors = Database::get()->query("UPDATE mentoring_programs_user SET
                                                                reg_date = " . DBHelper::timeAfter() . ",
                                                                mentor = 1
                                                                WHERE mentoring_program_id = ?d
                                                                AND user_id = ?d",$existing_program_id,$uuserid);
                }
    
                if(!in_array($uid,$mentors_ids)){
                    $result_mentors = Database::get()->query("UPDATE mentoring_programs_user SET
                                                                reg_date = " . DBHelper::timeAfter() . ",
                                                                mentor = 0
                                                                WHERE mentoring_program_id = ?d
                                                                AND user_id = ?d",$existing_program_id, $uuserid);
                }
            }else{
                foreach($tutor_id as $t){
                    $user_mentor_id = $t->user_id;
                    if(in_array($user_mentor_id,$mentors_ids)){
                        $result_mentors = Database::get()->query("UPDATE mentoring_programs_user SET
                                                                    reg_date = " . DBHelper::timeAfter() . ",
                                                                    mentor = 1
                                                                    WHERE mentoring_program_id = ?d
                                                                    AND user_id = ?d",$existing_program_id,$user_mentor_id);
                    }
        
                    if(!in_array($user_mentor_id,$mentors_ids)){
                        $result_mentors = Database::get()->query("UPDATE mentoring_programs_user SET
                                                                    reg_date = " . DBHelper::timeAfter() . ",
                                                                    mentor = 0
                                                                    WHERE mentoring_program_id = ?d
                                                                    AND user_id = ?d",$existing_program_id, $user_mentor_id);
                    }
                }
                
            }
            
        }

        if($result_mentors){

            if(!empty($program_image)){
                $target_dir = "$webDir/mentoring_programs/$code/image/";
                $target_file = $target_dir . my_basename($program_image);
                $uploadOk = 1;
                $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
                upload_mentoring_program_image($target_file, $target_dir,$imageFileType, $uploadOk,$tmp_name,$size, $webDir, $program_image, $old_image_program);
            }
            
            return 1;

        }else{
            return 0;
        }
    }
    
    
}

/**
 * @brief check code if exist in database or is unique
 * @param type $code
 * @return bool
 */
function check_exist_mentoring_code_db($code) {
   $check_code = Database::get()->queryArray("SELECT code FROM mentoring_programs");
   $exist = false;
   foreach($check_code as $c){
      if(strcasecmp($code,$c->code) == 0){
        $exist = true;
      }
   }

   $check_code_course = Database::get()->queryArray("SELECT code FROM course");
   foreach($check_code_course as $d){
        if(strcasecmp($code,$d->code) == 0){
            $exist = true;
        }
   }

   return $exist;
}

/**
 * @brief create main mentoring_program index.php
 * @global type $webDir
 * @param type $code
 * @return bool
 */
function mentoring_program_index($code) {
    global $webDir;

    $fd = fopen($webDir . "/mentoring_programs/$code/index.php", "w");
    chmod($webDir . "/mentoring_programs/$code/index.php", 0644);
    if (!$fd) {
        return false;
    }
    fwrite($fd, "<?php\nsession_start();\n" .
            "\$_SESSION['program_code']='$code';\n" .
            "include '../../modules/mentoring/programs/program_home.php';\n");
    fclose($fd);
    return true;
}

/**
 * @brief create mentoring_program directories
 * @param type $code
 * @return bool
 */
function create_mentoring_program_dirs($code) {
    global $langDirectoryCreateError;

    $base = "mentoring_programs/$code";
    $dirs = [$base, "$base/image", "$base/document", "$base/dropbox",
        "$base/page", "$base/work", "$base/group", "$base/temp",
        "$base/scormPackages", "video/$code"];
    foreach ($dirs as $dir) {
        if (!make_dir($dir)) {
            Session::flash('message',sprintf($langDirectoryCreateError, $dir));
            Session::flash('alert-class', 'alert-warning');
            return false;
        }
        if ($dir != $base) {
            touch("$dir/index.html");
        }
    }
    return true;
}

/**
 * @brief get mentoring_program details
 * @param type $mp_id
 * @param type $uid
 * @return bool
 */
function register_mentoring_program($mp_id, $uid){
    $user_status = Database::get()->querySingle("SELECT status FROM user WHERE id = ?d",$uid)->status;
    $reg = Database::get()->query("INSERT INTO mentoring_programs_user SET
                                    mentoring_program_id = ?d,
                                    user_id = ?d,
                                    status = ?d,
                                    reg_date = " . DBHelper::timeAfter() . ",
                                    mentor = ?d, 
                                    tutor = ?d,
                                    is_guided = ?d",$mp_id, $uid, $user_status ,0,0,1);
    if($reg){
        return true;
    }else{
        return false;
    }
}

/**
 * @brief get mentoring_program details
 * @param type $mp_id
 * @param type $uid
 * @return bool
 */
function check_is_guided_mentoring_program($mp_id, $uid){
    $check = Database::get()->queryArray("SELECT is_guided FROM mentoring_programs_user WHERE user_id = ?d AND mentoring_program_id = ?d",$uid,$mp_id);
    if($check){
        foreach($check as $ch){
            if($ch->is_guided == 1){
                return true;
            }else{
                return false;
            }
        }
    }
}

/**
 * @brief get mentoring_program guidied users
 * @param type $mp_id
 * @return array
 */
function get_all_guides_from_mentoring_program($mp_id){
    $get_users = Database::get()->queryArray("SELECT id,givenname,surname,email,registered_at,has_icon FROM user 
                                              WHERE id IN (SELECT user_id FROM mentoring_programs_user
                                                           WHERE mentoring_program_id = ?d
                                                           AND is_guided = ?d)",$mp_id,1);
    
    if($get_users){
        return $get_users;
    }else{
        return array();
    }
}

/**
 * @brief get mentoring_program details
 * @param type $mp_id
 * @param type $user
 * @return bool
 */
function delete_guides_from_mentoring_program($mp_id,$user){
    $del_booking_user = Database::get()->query("DELETE FROM mentoring_booking WHERE mentoring_program_id = ?d AND 
                                                id IN (SELECT mentoring_booking_id FROM mentoring_booking_user WHERE mentee_id = ?d)",$mp_id,$user);

    $del_request_user_accepted = Database::get()->query("DELETE FROM mentoring_programs_requests
                                                        WHERE mentoring_program_id = ?d
                                                        AND guided_id = ?d
                                                        AND status_request = ?d",$mp_id,$user,1);

    $check_user_member_group = Database::get()->queryArray("SELECT user_id FROM mentoring_group_members
                                                            WHERE group_id IN (SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d)
                                                            AND user_id = ?d AND is_tutor = ?d AND status_request = ?d",$mp_id,$user,0,1);
    if(count($check_user_member_group) > 0){
        Database::get()->query("DELETE FROM mentoring_group_members WHERE group_id IN (SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d)
                                AND user_id = ?d AND is_tutor = ?d AND status_request = ?d",$mp_id,$user,0,1);
    }

    $del_user = Database::get()->query("DELETE FROM mentoring_programs_user
                                        WHERE mentoring_program_id = ?d
                                        AND user_id = ?d
                                        AND is_guided = ?d",$mp_id,$user,1);
    
    if($del_user && $del_request_user_accepted){
        return true;
    }else{
        return false;
    }
}

/**
 * @brief get mentoring_program details
 * @param type $mp_id
 * @return int
 */
function check_if_mentoring_program_has_expired($mp_id){
    $now = date('Y-m-d H:i:s', strtotime('now'));
    $finish_date = Database::get()->querySingle("SELECT finish_date FROM mentoring_programs WHERE id = ?d",$mp_id)->finish_date;
    if($finish_date < $now){
        return 0;
    }else{
        return 1;
    }
}


/**
 * @brief get all available mentoring_programs
 * @return array
 */
function show_all_available_mentoring_programs(){
    $now = date('Y-m-d H:i', strtotime('now'));

    $all_programs = Database::get()->queryArray("SELECT *FROM mentoring_programs WHERE finish_date >= ?s", $now);
    if($all_programs){
        return $all_programs;
    }else{
        return array();
    }
}

/**
 * @brief get all unavailable mentoring_programs

 * @return array
 */
function show_all_no_available_mentoring_programs(){
    $now = date('Y-m-d H:i', strtotime('now'));

    $all_programs = Database::get()->queryArray("SELECT *FROM mentoring_programs WHERE finish_date < ?s", $now);
    if($all_programs){
        return $all_programs;
    }else{
        return array();
    }
}

/**
 * @brief get all available mentoring_programs

 * @return array
 */
function show_all_mentoring_programs(){
    $all_programs = Database::get()->queryArray("SELECT *FROM mentoring_programs 
                                                    WHERE (start_date <= NOW() OR start_date IS NULL) 
                                                    AND (finish_date >= NOW() OR finish_date IS NULL)");
    if($all_programs){
        return $all_programs;
    }else{
        return array();
    }
}

/**
 * @brief get all unavailable mentoring_programs only for admin

 * @return array
 */
function show_all_unvailable_mentoring_programs(){
    $all_programs = Database::get()->queryArray("SELECT *FROM mentoring_programs 
                                                    WHERE finish_date < NOW()");
    if($all_programs){
        return $all_programs;
    }else{
        return array();
    }
}

/**
 * @brief get all available mentoring_programs for uid as guided
 * @param type $uid
 * @return array
 */
function show_all_mentoring_programs_for_uid_as_guided($uid){
    $all_programs = Database::get()->queryArray("SELECT id,code,title,tutor,start_date,finish_date,program_image,description,allow_unreg_mentee FROM mentoring_programs
                                                 WHERE (start_date <= NOW() OR start_date IS NULL) 
                                                AND (finish_date >= NOW() OR finish_date IS NULL)
                                                 AND id IN
                                                 (SELECT mentoring_program_id FROM mentoring_programs_user
                                                   WHERE user_id = ?d 
                                                   AND is_guided = 1
                                                  )
                                                ",$uid);
    if($all_programs){
        return $all_programs;
    }else{
        return array();
    }
}


/**
 * @brief get all available mentoring_programs where uid is tutor or mentor
 * @param type $uid
 * @return array
 */
function get_all_available_mentoring_programs_as_mentor_or_tutor_or_tutor_mentor_by_uid($uid){
    $all_programs = Database::get()->queryArray("SELECT id,code,title,tutor,start_date,finish_date,program_image,description FROM mentoring_programs
                                                 WHERE (start_date <= NOW() OR start_date IS NULL) 
                                                AND (finish_date >= NOW() OR finish_date IS NULL)
                                                 AND id IN
                                                 (SELECT mentoring_program_id FROM mentoring_programs_user
                                                   WHERE user_id = ?d 
                                                   AND ((tutor = 1 AND mentor = 0) OR (tutor = 0 AND mentor = 1) OR (tutor = 1 AND mentor = 1))
                                                  )
                                                ",$uid);

    if($all_programs){
        return $all_programs;
    }else{
        return array();
    }
}

/**
 * @brief get all available mentoring_programs where uid is mentor
 * @param type $uid
 * @return array
 */
function get_all_available_mentoring_programs_as_mentor_by_uid($uid){

    $all_programs = Database::get()->queryArray("SELECT id,code,title,tutor,start_date,finish_date,program_image FROM mentoring_programs
                                                 WHERE (start_date <= NOW() OR start_date IS NULL) 
                                                 AND (finish_date >= NOW() OR finish_date IS NULL)
                                                 AND id IN
                                                 (SELECT mentoring_program_id FROM mentoring_programs_user
                                                   WHERE user_id = ?d 
                                                   AND ((tutor = 0 AND mentor = 1) OR (tutor = 1 AND mentor = 1))
                                                  )
                                                ",$uid);

    if($all_programs){
        return $all_programs;
    }else{
        return array();
    }
}


/**
 * @brief get all unavailable mentoring_programs where uid is tutor or mentor
 * @param type $uid
 * @return array
 */
function get_all_unavailable_mentoring_programs_as_mentor_or_tutor_or_tutor_mentor_by_uid($uid){

    $now = date('Y-m-d H:i', strtotime('now'));
    $all_programs = Database::get()->queryArray("SELECT id,code,title,tutor,start_date,finish_date,program_image FROM mentoring_programs
                                                 WHERE finish_date < ?s 
                                                 AND id IN
                                                 (SELECT mentoring_program_id FROM mentoring_programs_user
                                                   WHERE user_id = ?d 
                                                   AND ((tutor = 1 AND mentor = 0) OR (tutor = 0 AND mentor = 1) OR (tutor = 1 AND mentor = 1))
                                                  )
                                                ",$now, $uid);

    if($all_programs){
        return $all_programs;
    }else{
        return array();
    }
}


/**
 * @brief get all available mentoring_programs

 * @return array
 */
function show_all_available_mentors(){

    $all_mentors = Database::get()->queryArray("SELECT id,givenname,surname,email,description,has_icon FROM user 
                                                WHERE is_mentor = ?d",1);

    if($all_mentors){
        return $all_mentors;
    }else{
        return array();
    }
}

/**
 * @brief get all available mentoring_programs
* @param type $user_id_mentor
 * @return array
 */
function show_details_of_mentor($user_id_mentor){

    $details = Database::get()->queryArray("SELECT id,givenname,surname,email,description,has_icon FROM user 
                                                WHERE is_mentor = ?d AND id = ?d",1,$user_id_mentor);

    if($details){
        return $details;
    }else{
        return array();
    }
}



/**
 * @brief get mentoring_program details
 * @param type $code
 * @param type $mp_id
 * @return array
 */
function show_mentoring_program_details($code, $mp_id){
    $details = Database::get()->queryArray("SELECT *FROM mentoring_programs WHERE code = ?s AND id = ?d",$code, $mp_id);
    if($details){
        return $details;
    }else{
        return array();
    }
}

/**
 * @brief get mentoring_program id
 * @param type $code
 * @return int
 */
function show_mentoring_program_id($code){
    $id = Database::get()->queryArray("SELECT id FROM mentoring_programs WHERE code = ?s",$code);

    if($id){
        foreach($id as $i){
            $mentoring_program_id = $i->id;
        }
        return $mentoring_program_id;
    }else{
        return -1;
    }
}

/**
 * @brief get mentoring_program code
 * @param type $mp_id
 * @return string
 */
function show_mentoring_program_code($mp_id){
    $code = Database::get()->querySingle("SELECT code FROM mentoring_programs WHERE id = ?s",$mp_id)->code;
    if($code){
        return $code;
    }else{
        return;
    }
}

/**
 * @brief show mentoring_program title
 * @param type $code
 * @return string
 */
function show_mentoring_program_title($code){
    $title = Database::get()->querySingle("SELECT title FROM mentoring_programs WHERE code = ?s",$code)->title;
    if($title){
        return $title;
    }else{
        return;
    }
}

/**
 * @brief show mentoring_program title
 * @param type $mp_id
 * @return string
 */
function show_mentoring_program_title_by_id($mp_id){
    $title = Database::get()->querySingle("SELECT title FROM mentoring_programs WHERE id = ?s",$mp_id)->title;
    if($title){
        return $title;
    }else{
        return;
    }
}

/**
 * @brief show mentoring_program tutor
 * @param type $mp_id
 * @return array
 */
function show_mentoring_program_tutor($mp_id){
    $tutor = Database::get()->queryArray("SELECT id,givenname,surname FROM user 
                                            WHERE id IN (SELECT user_id FROM mentoring_programs_user
                                                         WHERE mentoring_program_id = ?d
                                                         AND status = ?d
                                                         AND tutor = ?d)",$mp_id,1,1);
    if(count($tutor) > 0){
        return $tutor;
    }else{
        return;
    }
}

/**
 * @brief GET IMAGE of mentoring_program if exist
 * @param type $code
 * @return string
 */
function show_mentoring_program_image($code) {
    $image = Database::get()->querySingle("SELECT program_image FROM mentoring_programs WHERE code = ?s",$code)->program_image;
    if($image){
      return $image;
    }else{
      return ;
    }
 }

/**
 * @brief show mentoring_program mentors
 * @param type $mp_id
 * @return array
 */
function show_mentoring_program_mentors($mp_id){
    $mentors = array();
    $mentors = Database::get()->queryArray("SELECT user.id,user.givenname,user.surname,user.email,user.description,user.has_icon FROM mentoring_programs_user 
                                            LEFT JOIN user ON mentoring_programs_user.user_id = user.id 
                                            WHERE mentoring_programs_user.mentoring_program_id = ?d 
                                            AND mentoring_programs_user.mentor = ?d
                                            AND user.is_mentor = ?d",$mp_id,1,1);
    if($mentors){
        return $mentors;
    }else{
        return array();
    }
}

/**
 * @brief show title of group
 * @param type $gr_id
 * @return string
 */
function show_mentoring_program_group_name($gr_id){
    $title = Database::get()->querySingle("SELECT name FROM mentoring_group WHERE id = ?d",$gr_id)->name;
    if($title){
        return $title;
    }else{
        return;
    }
}

/**
 * @brief show id of common group
 * @param type $mp_id
 * @return string
 */
function show_mentoring_program_group_id($mp_id){
    $id_common_group = Database::get()->querySingle("SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d AND common = ?d",$mp_id,1)->id;
    if($id_common_group){
        return $id_common_group;
    }else{
        return;
    }
}

/**
 * @brief check if uid is tutor or mentor or guided of mentoring program
 * @param type $mp_id
 * @param type $uid
 * @return array
 */
function check_if_uid_is_mentor_or_tutor_or_guided_of_mentoring_program($mp_id, $uid){
    $check = Database::get()->queryArray("SELECT tutor,mentor,is_guided FROM mentoring_programs_user WHERE mentoring_program_id = ?d AND user_id = ?d",$mp_id,$uid);
    $check_uid = array();//first column contains the number of(tutor,mentor,guided) and second column contains uid
    if($check){//exist record
        foreach($check as $c){
            if(($c->tutor == 1) and ($c->mentor == 0)){//tutor
                $check_uid[0]['tutor_or_mentor'] = 1;
                $check_uid[0]['uid'] = $uid;
            }elseif(($c->tutor == 0) and ($c->mentor == 1)){//mentor
                $check_uid[0]['tutor_or_mentor'] = 0;
                $check_uid[0]['uid'] = $uid;
            }elseif(($c->tutor == 0) and ($c->mentor == 0) and ($c->is_guided == 1)){//guided
                $check_uid[0]['tutor_or_mentor'] = 2;
                $check_uid[0]['uid'] = $uid;
            }elseif(($c->tutor == 1) and ($c->mentor == 1)){//tutor and mentor
                $check_uid[0]['tutor_or_mentor'] = 4;
                $check_uid[0]['uid'] = $uid;
            }
        }
    }
    else{//doesnt exist record
        $check_uid[0]['tutor_or_mentor'] = 3;
        $check_uid[0]['uid'] = $uid;
    }
    return $check_uid;
}


/**
 * @brief check if uid is tutor or mentor or guided of mentoring program
 * @param type $mp_id
 * @param type $mentor_id
 * @return bool
 */
function exist_mentor_in_programs($mentor_id){
    $now = date('Y-m-d H:i', strtotime('now'));
    $check = Database::get()->queryArray("SELECT * FROM mentoring_programs
                                                 WHERE finish_date >= ?s AND id IN
                                                 (SELECT mentoring_program_id FROM mentoring_programs_user
                                                   WHERE user_id = ?d 
                                                   AND ((mentor = 1 AND tutor = 0) OR (mentor = 1 AND tutor = 1))
                                                  )
                                                ",$now,$mentor_id);

    if($check){
        return true;
    }else{
        return false;
    }
}


/**
 * @brief show mentoring_program editor
 * @param type $mp_id
 * @param type $userid
 * @return bool
 */
function show_mentoring_program_editor($mp_id, $userid){
    global $is_admin;
    $editor = Database::get()->queryArray("SELECT tutor FROM mentoring_programs_user WHERE mentoring_program_id = ?d
                                                                                      AND user_id = ?d",$mp_id, $userid);
    
    if($editor){
        foreach($editor as $t){
            if($t->tutor == 1){
                return true;
            }else{
                return false;
            }
        }
    }else{
        if($is_admin){
            return true;
        }
    }
    
    
}

/**
 * @brief show mentoring_program editor
 * @param type $uid
 * @return int
 */
function check_if_uid_is_mentor($uid){
    //$uid = $_SESSION['uid'];
    $mentor = Database::get()->querySingle("SELECT is_mentor FROM user WHERE id = ?d",$uid)->is_mentor;
   
    if($mentor == 1){
        return 1;
    }else{
        return 0;
    }
    
    
}


/**
 * @brief upload mentoring_program image
 * @param type $target_file
 * @param type $target_dir
 * @param type $check
 * @param type $tmp_name
 * @param type $size
 * @param type $program_image
 * @param type $webDir
 * @return bool
 */
function upload_mentoring_program_image($target_file, $target_dir, $imageFileType, $uploadOk,$tmp_name,$size, $webDir, $program_image, $old_image_program){
    
        // Check if file already exists
        if (file_exists($target_file)) {
            $uploadOk = 0;
        }
        // Check file size
        if ($size > 5000000) {
            $uploadOk = 0;
        }
        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" &&
        $imageFileType != "JPG" && $imageFileType != "PNG" && $imageFileType != "JPEG"
        && $imageFileType != "GIF" ) {
            $uploadOk = 0;
        }
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            //echo "<center>Sorry, your file was not uploaded.</center>";
        // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($tmp_name, $target_file)) {
                unlink($target_dir.$old_image_program);//remove old image program
                echo "<center><i><h4>The file ". my_basename($program_image). " has been uploaded.</h4></i></center>";
            } else {
                echo "<center>Sorry, there was an error uploading your file.</font></center>";
            }
        }
}

/**
 * @brief available mentoring_program until end date
 * @param type $code
 * @return string
 */
function until_date_mentoring_program($code){
    $until = Database::get()->querySingle("SELECT finish_date FROM mentoring_programs WHERE code = ?s",$code)->finish_date;
    if($until){
         return date('d-m-Y H:i:s',strtotime($until));
    }else{
         return ;
    }
 }
 

/**
 * @brief set empty mentoring_program image
 * @param type $code
 * @return bool
 */
function delete_mentoring_program_image($code){
   $del = Database::get()->query("UPDATE mentoring_programs SET program_image = '' WHERE code = ?s",$code);
   if($del){
        return true;
   }else{
        return false;
   }
}

/**
 * @brief delete mentoring program
 * @param type $code
 * @param type $mp_id
 * @return bool
 */
function delete_mentoring_program($code, $mp_id, $webDir){
    
    //del groups and tools group
    $del_announcements = Database::get()->query("DELETE FROM mentoring_announcement WHERE mentoring_program_id = ?d",$mp_id);

    $del_bookings = Database::get()->query("DELETE FROM mentoring_booking WHERE mentoring_program_id = ?d",$mp_id);

    $del_document = Database::get()->query("DELETE FROM mentoring_document WHERE mentoring_program_id = ?d",$mp_id);

    $del_post = Database::get()->query("DELETE FROM mentoring_forum_post WHERE topic_id IN 
                                        (SELECT id FROM mentoring_forum_topic WHERE forum_id IN 
                                        (SELECT id FROM mentoring_forum WHERE mentoring_program_id = ?d))",$mp_id);

    $del_forum_topic = Database::get()->query("DELETE FROM mentoring_forum_topic WHERE forum_id IN 
                                                                                    (SELECT id FROM mentoring_forum WHERE mentoring_program_id = ?d)",$mp_id);

    $del_forum_category = Database::get()->query("DELETE FROM mentoring_forum_category WHERE mentoring_program_id = ?d",$mp_id);

    $del_forum_stats = Database::get()->query("DELETE FROM mentoring_forum_user_stats WHERE mentoring_program_id = ?d",$mp_id);

    $del_forum = Database::get()->query("DELETE FROM mentoring_forum WHERE mentoring_program_id = ?d",$mp_id);

    $del_wall_post = Database::get()->query("DELETE FROM mentoring_wall_post_resources WHERE post_id IN 
                                                (SELECT id FROM mentoring_wall_post WHERE mentoring_program_id = ?d)",$mp_id);

    $del_wall = Database::get()->query("DELETE FROM mentoring_wall_post WHERE mentoring_program_id = ?d",$mp_id);

    $del_rentezvous = Database::get()->query("DELETE FROM mentoring_rentezvous WHERE mentoring_program_id = ?d",$mp_id);

    $del_group_members = Database::get()->query("DELETE FROM mentoring_group_members WHERE group_id IN 
                                                                                    (SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d)",$mp_id);
    $del_group_properties = Database::get()->query("DELETE FROM mentoring_group_properties WHERE group_id IN 
                                                                                    (SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d)",$mp_id);
    $del_groups = Database::get()->query("DELETE FROM mentoring_group WHERE mentoring_program_id = ?d",$mp_id);

    $del_availability_of_mentor_in_group = Database::get()->query("DELETE FROM mentoring_mentor_availability_group WHERE mentoring_program_id = ?d",$mp_id);


    $del2 = Database::get()->query("DELETE FROM mentoring_programs_user WHERE mentoring_program_id = ?d", $mp_id);
    $del = Database::get()->query("DELETE FROM mentoring_programs WHERE id = ?d",$mp_id);

    $subDir = "/mentoring_programs/";
    $subDirVideo = "/video/";
    $dir = $webDir.$subDir.$code;
    $dir2 =  $webDir.$subDirVideo.$code;
    $delfolders = deleteAllfoldersFiles($dir);
    $delfolderVideo = deleteAllfoldersFiles($dir2);

    if($del and $del2 and $delfolders and $delfolderVideo){
         return true;
    }else{
         return false;
    }
 }

 /**
 * @brief delete all folders and files of mentoring program
 * @param type $dir
 * @return bool
 */
 function deleteAllfoldersFiles($dir) {
    foreach(glob($dir . '/*') as $file) {
        if(is_dir($file))
            deleteAllfoldersFiles($file);
        else
            unlink($file);
    }
    rmdir($dir);
    if(!is_dir($dir)){
       return true;
    }else{
        return false;
    }
}


/**
 * @brief show mentoring_program requests
 * @param type $mp_id
 * @param type $uid
 * @return array
 */
function show_mentoring_program_requests($mp_id){
    
    $requests = Database::get()->queryArray("SELECT * FROM mentoring_programs_requests 
                                             WHERE status_request = ?d AND mentoring_program_id = ?d",0,$mp_id);
    if(count($requests) > 0){
        return $requests;
    }
    else{
        return array();
    }
    
}

/**
 * @brief show mentoring_program requests
 * @param type $mp_id
 * @param type $uid
 * @return bool
 */
function check_if_send_request($mp_id,$uid){
    
    $check = Database::get()->queryArray("SELECT * FROM mentoring_programs_requests 
                                          WHERE mentoring_program_id = ?d
                                          AND guided_id = ?d",$mp_id,$uid);
    if(count($check) > 0){
        return true;
    }
    else{
        return false;
    }
    
}

/**
 * @brief show mentoring_program requests
 * @param type $mp_id
 * @return array
 */
function get_accepted_requests($mp_id){
    
    $check = Database::get()->queryArray("SELECT * FROM mentoring_programs_requests 
                                          WHERE mentoring_program_id = ?d
                                          AND status_request = ?d",$mp_id,1);
    if(count($check) > 0){
        return $check;
    }
    else{
        return array();
    }
    
}

/**
 * @brief show mentoring_program requests
 * @param type $mp_id
 * @return array
 */
function get_denied_requests($mp_id){
    
    $check = Database::get()->queryArray("SELECT * FROM mentoring_programs_requests 
                                          WHERE mentoring_program_id = ?d
                                          AND status_request = ?d",$mp_id,2);
    if(count($check) > 0){
        return $check;
    }
    else{
        return array();
    }
    
}

/**
 * @brief show mentoring_program requests
 * @param type $mp_id
 * @param type $uid
 * @return int
 */
function check_accepted_or_denied_request_uid_from_program($mp_id,$uid){
    
    $check = Database::get()->queryArray("SELECT status_request FROM mentoring_programs_requests 
                                          WHERE mentoring_program_id = ?d
                                          AND guided_id = ?d
                                          AND (status_request = 1 OR status_request = 2)",$mp_id,$uid);
    if(count($check) > 0){
       return $check;
    }
    else{
        return array();
    }
    
}


/**
 * @brief check is_editor_mentoring
 * @param type $uid
 * @return bool
 */
function is_editor_mentoring($uid){
    $is_editor_mentoring = false;
    $user = Database::get()->querySingle("SELECT *FROM user WHERE id = ?d",$uid);
    if ($user) {
        if ($user->status == USER_TEACHER) {
            $is_editor_mentoring = true;
        }
    }

    return $is_editor_mentoring;
    
}


/**
 * @brief get groups details
 * @param type $mp_id
 * @return array
 */
function get_groups_details_for_mentoring_program($mp_id){
    
    $get_groups = Database::get()->queryArray("SELECT DISTINCT mentoring_group.id,mentoring_group.mentoring_program_id,mentoring_group.name,
                                                      mentoring_group.description,mentoring_group.forum_id,mentoring_group.common,
                                                      mentoring_group.max_members,mentoring_group.secret_directory,mentoring_group.visible,
                                                      mentoring_group_members.group_id
                                                      FROM mentoring_group
                                                      LEFT JOIN mentoring_group_members ON mentoring_group.id = mentoring_group_members.group_id
                                                      WHERE mentoring_group.mentoring_program_id = ?d
                                                      AND common = ?d",$mp_id,0);
    
    if(count($get_groups) > 0){
        return $get_groups;
    }else{
        return array();
    }
    
}

/**
 * @brief get groups details
 * @param type $mp_id
 * @return array
 */
function get_common_group_details_for_mentoring_program($mp_id){
    
    $get_group = Database::get()->queryArray("SELECT DISTINCT mentoring_group.id,mentoring_group.mentoring_program_id,mentoring_group.name,
                                                      mentoring_group.description,mentoring_group.forum_id,mentoring_group.common,
                                                      mentoring_group.max_members,mentoring_group.secret_directory,mentoring_group.visible,
                                                      mentoring_group_members.group_id
                                                      FROM mentoring_group
                                                      LEFT JOIN mentoring_group_members ON mentoring_group.id = mentoring_group_members.group_id
                                                      WHERE mentoring_group.mentoring_program_id = ?d 
                                                      AND common = ?d",$mp_id,1);
    
    if(count($get_group) > 0){
        return $get_group;
    }else{
        return array();
    }
    
}





/**
 * @brief get groups details
 * @param type $g_id
 * @return array
 */
function get_group_mentor_for_mentoring_program($g_id){
   $mentor = Database::get()->queryArray("SELECT user.id,user.givenname,user.surname FROM user
                                           LEFT JOIN mentoring_group_members ON user.id = mentoring_group_members.user_id
                                           WHERE mentoring_group_members.is_tutor = 1
                                           AND mentoring_group_members.group_id = ?d",$g_id);
    if($mentor){
        return $mentor;
    }else{
        return array();
    }
    
}

/**
 * @brief get groups properties
 * @param type $g_id
 * @param type $mp_id
 * @return array
 */
function get_group_properties_for_mentoring_program($mp_id,$g_id){
    $properties = Database::get()->queryArray("SELECT mentoring_group_properties.mentoring_program_id,mentoring_group_properties.group_id,
                                                        mentoring_group_properties.self_registration,
                                                        mentoring_group_properties.allow_unregister,
                                                        mentoring_group_properties.self_request
                                                FROM mentoring_group_properties 
                                                LEFT JOIN mentoring_group ON mentoring_group_properties.group_id = mentoring_group.id
                                                WHERE mentoring_group_properties.mentoring_program_id = ?d
                                                AND mentoring_group_properties.group_id = ?d",$mp_id, $g_id);
     if($properties){
         return $properties;
     }else{
         return array();
     }
     
 }

 function isMemberOfCommonGroup($userId,$mp_id,$g_id){
    //check if user has registered in group
    $check = Database::get()->querySingle("SELECT *FROM mentoring_group_members
                                            WHERE group_id = ?d
                                            AND user_id = ?d AND status_request = ?d AND is_tutor = ?d",$g_id,$userId,1,0);

    $has_send_request = Database::get()->querySingle("SELECT *FROM mentoring_group_members
                                                        WHERE group_id = ?d
                                                        AND user_id = ?d AND status_request = ?d AND is_tutor = ?d",$g_id,$userId,0,0);

    // check if group exist
    $group_exist = Database::get()->querySingle("SELECT *FROM mentoring_group
                                                    WHERE id = ?d AND mentoring_program_id = ?d",$g_id,$mp_id);
    if($check && $group_exist){
        redirect_to_home_page("modules/mentoring/programs/group/group_space.php?space_group_id=".getInDirectReference($g_id));
    }elseif(!$check && $group_exist && $has_send_request){
        $is_common_group = Database::get()->querySingle("SELECT common FROM mentoring_group WHERE id = ?d",$g_id)->common;
        if($is_common_group == 1){
            redirect_to_home_page("modules/mentoring/programs/group/index.php?commonGroupView=1");
        }else{
            redirect_to_home_page("modules/mentoring/programs/group/index.php");
        }
    }elseif(!$check && $group_exist && !$has_send_request){
        $is_common_group = Database::get()->querySingle("SELECT common FROM mentoring_group WHERE id = ?d",$g_id)->common;
        if($is_common_group == 1){
            redirect_to_home_page("modules/mentoring/programs/group/index.php?commonGroupView=1");
        }else{
            redirect_to_home_page("modules/mentoring/programs/group/index.php");
        }
    }else{
        redirect_to_home_page("modules/mentoring/programs/group/index.php");
    }
 }

 /**
 * @brief get editor for current group
 * @param type $g_id
 * @param type $uid
 * @return bool
 */
function get_editor_for_current_group($uid,$g_id){
     
    $check_ = Database::get()->queryArray("SELECT *FROM mentoring_group_members
                                            WHERE group_id = ?d
                                            AND user_id = ?d
                                            AND is_tutor = ?d
                                            AND status_request = ?d",$g_id,$uid,1,1);

    if(count($check_) > 0){
        return true;
        
    }else{
        return false;
    }
     
 }

  /**
 * @brief get editor for current group
 * @param type $g_id
 * @return array
 */
function get_details_of_editor_for_current_group($g_id){
     
    $details = Database::get()->queryArray("SELECT *FROM user
                                            WHERE id IN (SELECT user_id FROM mentoring_group_members
                                                         WHERE group_id = ?d AND is_tutor = ?d AND status_request = ?d)",$g_id,1,1);

    if(count($details) > 0){
        return $details;
        
    }else{
        return array();
    }
     
 }

   /**
 * @brief get editor for current group
 * @param type $g_id
 * @param type $uid
 * @return array
 */
function get_details_of_editor_for_current_group_with_uid($g_id,$uid){
     
    $details = Database::get()->queryArray("SELECT *FROM user
                                            WHERE id IN (SELECT user_id FROM mentoring_group_members
                                                         WHERE group_id = ?d AND user_id = ?d AND is_tutor = ?d AND status_request = ?d)",$g_id,$uid,1,1);

    if(count($details) > 0){
        return $details;
        
    }else{
        return array();
    }
     
 }

    /**
 * @brief get editor for current group
 * @param type $g_id
 * @param type $mp_id
 * @param type $uid
 * @return array
 */
function get_rentezvous_of_mentee_for_current_group_with_uid($mp_id,$g_id,$uid){
    $now = date('Y-m-d H:i:s', strtotime('now'));
    $now_day = date('Y-m-d', strtotime('now'));
    $end = date('Y-m-d H:i:s',strtotime('now + 30days'));
    $details = Database::get()->queryArray("SELECT *FROM mentoring_rentezvous
                                            WHERE id IN (SELECT mentoring_rentezvous_id FROM mentoring_rentezvous_user
                                                         WHERE mentee_id = ?d)
                                            AND mentoring_program_id = ?d
                                            AND group_id = ?d
                                            AND (start >= ?t OR start >= ?t)
                                            AND end <= ?t
                                            ORDER BY start ASC",$uid,$mp_id,$g_id,$now,$now_day,$end);

    if(count($details) > 0){
        return $details;
        
    }else{
        return array();
    }
     
 }

  /**
 * @brief check if uid is mentee for current group
 * @param type $g_id
 * @param type $uid
 * @return bool
 */
function check_if_uid_is_mentee_for_current_group($uid,$g_id){
     
    $check_ = Database::get()->queryArray("SELECT *FROM mentoring_group_members
                                            WHERE group_id = ?d
                                            AND user_id = ?d
                                            AND is_tutor = ?d
                                            AND status_request = ?d",$g_id,$uid,0,1);

    if(count($check_) > 0){
        return true;
    }else{
        return false;
    }
     
 }



   /**
 * @brief check if allow register to group by max_members
 * @param type $g_id
 * @return bool
 */
function check_max_members_of_group_for_register($g_id){
     
    $max_members = Database::get()->queryArray("SELECT max_members FROM mentoring_group
                                            WHERE id = ?d",$g_id);

    $members_of_group = Database::get()->querySingle("SELECT COUNT(group_id) AS gi FROM mentoring_group_members 
                                                      WHERE group_id = ?d AND is_tutor = ?d AND status_request = ?d", $g_id, 0, 1)->gi;
    
    
    foreach($max_members as $m){
        if($m->max_members == $members_of_group and $m->max_members != 0){// cant register mentee to group
            return true;
        }else{
            return false;
        }
    }
   
     
 }



    /**
 * @brief count of mentees for current group
 * @param type $g_id
 * @return array
 */
function get_all_mentees_of_current_group($g_id){

    $mentees = Database::get()->queryArray("SELECT id,surname,givenname,email FROM user
                                            WHERE id IN (SELECT user_id FROM mentoring_group_members
                                                         WHERE group_id = ?d AND is_tutor = ?d AND status_request = ?d)",$g_id,0,1);
    
    
    if($mentees){
        return $mentees;
    }else{
        return array();
    }
   
     
 }



    /**
 * @brief count of mentees for current group
 * @param type $g_id
 * @return int
 */
function count_of_mentees_of_current_group($g_id){

    $count = Database::get()->querySingle("SELECT COUNT(group_id) AS gi FROM mentoring_group_members 
                                                      WHERE group_id = ?d AND is_tutor = ?d AND status_request = ?d", $g_id, 0, 1)->gi;
    
    
    if($count){
        return $count;
    }else{
        return 0;
    }
   
     
 }


  /**
 * @brief get editor for current group
 * @param type $g_id
 * @return string
 */
function get_name_for_current_group($g_id){
     
    $name = Database::get()->querySingle("SELECT name FROM mentoring_group
                                            WHERE id = ?d",$g_id);

    if($name){
        return $name->name;
    }else{
        return null;
    }
     
 }


   /**
 * @brief get settings registration of mentee for current group
 * @param type $mp_id
 * @param type $code
 * @return int
 */
function get_settings_registration_of_mentees_for_mentoring_program($mp_id,$code){
     
    $reg_group = Database::get()->queryArray("SELECT other_groups_reg FROM mentoring_programs
                                               WHERE id = ?d AND code = ?s",$mp_id, $code); 


    if(count($reg_group) > 0){
        foreach($reg_group as $g){
           $reg_group_result = $g->other_groups_reg;
        }
        return $reg_group_result;  
    }else{
        return -1;
    }

    

 }


    /**
 * @brief check uid if exist at least one group as mentee for current mentoring program
 * @param type $mp_id
 * @param type $uid
 * @return array
 */
function check_if_uid_exist_at_least_one_group_as_mentee_for_mentoring_program($mp_id,$uid){
     
    $check = Database::get()->queryArray("SELECT mentoring_group_members.user_id FROM mentoring_group_members  
                                            LEFT JOIN mentoring_group ON mentoring_group_members.group_id = mentoring_group.id
                                            WHERE mentoring_group.mentoring_program_id = ?d
                                            AND mentoring_group_members.is_tutor = ?d
                                            AND mentoring_group_members.status_request = ?d
                                            AND mentoring_group_members.user_id = ?d",$mp_id,0,1,$uid); 

    if(count($check) > 0){
        return $check;
    }else{
        return array();  
    }
    

 }


     /**
 * @brief check if exist a mentee participate to many groups of a current mentoring program not only one group
 * @param type $mp_id
 * @return int
 */
function check_if_mentee_participate_to_many_groups_of_mentoring_program($mp_id){
     
    $check = Database::get()->queryArray("SELECT mentoring_group_members.user_id FROM mentoring_group_members 
                                          LEFT JOIN mentoring_group ON mentoring_group_members.group_id = mentoring_group.id
                                          WHERE mentoring_group.mentoring_program_id = ?d 
                                          AND mentoring_group_members.group_id NOT IN (SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d AND common = ?d)
                                          AND mentoring_group_members.is_tutor = ?d 
                                          AND mentoring_group_members.status_request = ?d",$mp_id,$mp_id,1,0,1); 

    $indexes = array();
    if(count($check) > 0){
        foreach($check as $c){
            $indexes[] = $c->user_id;
        }
        if(count($indexes) != count(array_unique($indexes))){
            //Not Unique, there are duplicate elements
            return 0;	
        }else{
            //No duplicate elements
            return 1;
        }
    }else{
        return 1;
    }

 }



      /**
 * @brief check if mentors of program participate to a group as tutor_group
 * @param type $mp_id
 * @return array
 */
function check_if_mentors_of_programs_participate_to_group_as_tutor_group($mp_id){
     
    $check = Database::get()->queryArray("SELECT u.id, u.surname, u.givenname, u.am FROM (user u, mentoring_programs_user mpu)
                                                WHERE mpu.mentoring_program_id = ?d AND
                                                    mpu.user_id = u.id AND
                                                    mpu.mentor = 1 AND
                                                    mpu.status = " . USER_TEACHER . " AND
                                                    u.id IN (SELECT user_id FROM mentoring_group_members, `mentoring_group`
                                                                                    WHERE `mentoring_group`.id = mentoring_group_members.group_id AND
                                                                                    `mentoring_group`.mentoring_program_id = ?d
                                                                                    AND mentoring_group_members.is_tutor = ?d)
                                                GROUP BY u.id, u.surname, u.givenname, u.am
                                                ORDER BY u.surname, u.givenname",$mp_id,$mp_id,1); 

    $tutors_groups = array();
    if(count($check) > 0){
        foreach($check as $c){
            $tutors_groups[] = $c->id;
        }
       return $tutors_groups;
    }else{
        return array();
    }

 }






 

/**
 * @brief get old values code,id for mentoring after connection timeout and login again in previous page
 * @param type $uid
 * @param type $code
 */

function put_session_values_in_db_and_get_this_after_logout($uid,$code){

    $check = Database::get()->queryArray("SELECT *FROM mentoring_programs_old_session 
                                          WHERE user_id = ?d",$uid);
                                        
    if(count($check) == 0){
        $q = Database::get()->query("INSERT INTO mentoring_programs_old_session SET
                                     user_id = ?d,
                                     reg_date = " . DBHelper::timeAfter() . ",
                                     mentoring_program_code = ?s",$uid,$code);
    }else{
       $q = Database::get()->query("UPDATE mentoring_programs_old_session SET
                                        reg_date = " . DBHelper::timeAfter() . ",
                                        mentoring_program_code = ?s
                                    WHERE user_id = ?d",$code,$uid);
    }
    
 }


 /**
 * @brief get old values code,id for mentoring after connection timeout and login again in previous page
 * @param type $uid
 * @param type $code
 * @param type $group_id
 */

function put_session_group_id_in_db_and_get_this_after_logout($uid,$code,$group_id){

       $q = Database::get()->query("UPDATE mentoring_programs_old_session SET
                                        reg_date = " . DBHelper::timeAfter() . ",
                                        group_id = ?d
                                    WHERE mentoring_program_code = ?s AND user_id = ?d",$group_id,$code,$uid);
    
 }


  /**
 * @brief get old values code,id for mentoring after connection timeout and login again in previous page
 * @param type $uid
 * @param type $code
 * @param type $forum_id
 */

function put_session_forum_id_in_db_and_get_this_after_logout($uid,$code,$forum_id){

    $q = Database::get()->query("UPDATE mentoring_programs_old_session SET
                                     reg_date = " . DBHelper::timeAfter() . ",
                                     forum_id = ?d
                                 WHERE mentoring_program_code = ?s AND user_id = ?d",$forum_id,$code,$uid);
 
}

  /**
 * @brief get old values code,id for mentoring after connection timeout and login again in previous page
 * @param type $uid
 * @param type $code
 * @param type $topic_id
 */

 function put_session_topic_id_in_db_and_get_this_after_logout($uid,$code,$topic_id){

    $q = Database::get()->query("UPDATE mentoring_programs_old_session SET
                                     reg_date = " . DBHelper::timeAfter() . ",
                                     topic_id = ?d
                                 WHERE mentoring_program_code = ?s AND user_id = ?d",$topic_id,$code,$uid);
 
}



 /**
 * @brief get_total_topics
 * @param type $forum_id
 */
function mentoring_get_total_topics($forum_id) {
    return Database::get()->querySingle("SELECT COUNT(*) AS total FROM mentoring_forum_topic WHERE forum_id = ?d", $forum_id)->total;
}


/*
 * Return the total number of posts in forum or topic
 */

 function mentoring_get_total_posts($id) {
    return Database::get()->querySingle("SELECT COUNT(*) AS total FROM mentoring_forum_post WHERE topic_id = ?d", $id)->total;
}




/**
 * @brief download forum post attached file
 * @param $file_id
 */
function mentoring_send_forum_post_file($file_id) {
    global $webDir, $mentoring_program_code;

    $info = Database::get()->querySingle("SELECT topic_filepath, topic_filename FROM mentoring_forum_post WHERE id = ?d", $file_id);
    
    $actual_filename = $webDir . "/mentoring_programs/" . $mentoring_program_code . "/forum/" . $info->topic_filepath;
    // download it
    send_file_to_client($actual_filename, $info->topic_filename, null, true);
    exit;
}





/**
 * @brief group info initialization
 * @global type $mentoring_program_id
 * @global type $status
 * @global type $self_reg
 * @global type $allow_unreg
 * @global type $multi_reg
 * @global type $has_forum
 * @global type $private_forum
 * @global type $documents
 * @global type $wiki
 * @global type $group_name
 * @global type $group_description
 * @global type $forum_id
 * @global type $max_members
 * @global type $secret_directory
 * @global type $tutors
 * @global type $member_count
 * @global type $is_tutor
 * @global type $is_editor
 * @global type $is_member
 * @global type $uid
 * @global type $urlServer
 * @global type $user_group_description
 * @global type $mentoring_program_code
 * @global type $is_editor_mentoring_group
 * @global type $is_editor_mentoring_program
 * @param type $group_id
 */
function mentoring_initialize_group_info($group_id) {

    global $mentoring_program_id, $status, $self_reg, $allow_unreg, $has_forum, $private_forum, $documents, $wiki,
    $group_name, $group_description, $forum_id, $max_members, $secret_directory, $tutors, $group_category,
    $member_count, $is_tutor, $is_member, $uid, $urlServer, $user_group_description, $mentoring_program_code, $is_editor_mentoring_program;

    $grp_property_item = Database::get()->querySingle("SELECT self_registration, allow_unregister, forum, private_forum, documents, self_request
                     FROM mentoring_group_properties WHERE mentoring_program_id = ?d AND group_id = ?d", $mentoring_program_id, $group_id);

    $self_reg = $grp_property_item->self_registration;
    $allow_unreg = $grp_property_item->allow_unregister;
    $has_forum = $grp_property_item->forum;
    $private_forum = $grp_property_item->private_forum;
    $documents = $grp_property_item->documents;
    $self_request = $grp_property_item->self_request;

    // Guest users aren't allowed to register / unregister
    if ($status == USER_GUEST) {
        $self_reg = $allow_unreg = 0;
    }

    $res = Database::get()->querySingle("SELECT name, description, forum_id, max_members, secret_directory, category_id
                             FROM `mentoring_group` WHERE mentoring_program_id = ?d AND id = ?d", $mentoring_program_id, $group_id);
    if (!$res) {
        header("Location: {$urlServer}modules/mentoring/programs/group/select_group.php");
        exit;
    }
    $group_name = Session::has('name') ? Session::get('name') : $res->name;
    $group_description = Session::has('description') ? Session::get('description') : $res->description;
    $forum_id = $res->forum_id;
    $max_members = Session::has('maxStudent') ? Session::get('maxStudent') : $res->max_members;
    $secret_directory = $res->secret_directory;
    $group_category = $res->category_id;

    $member_count = Database::get()->querySingle("SELECT COUNT(*) AS count FROM mentoring_group_members
                                                                    WHERE group_id = ?d
                                                                    AND is_tutor = 0
                                                                    AND status_request = 1", $group_id)->count;

    $tutors = mentoring_group_tutors($group_id);

    $is_tutor = $is_member = FALSE;
    $user_group_description = NULL;

    if (isset($uid)) { // check if we are group_member
        $res = Database::get()->querySingle("SELECT user_id FROM mentoring_group_members
                                     WHERE group_id = ?d AND user_id = ?d AND is_tutor = 0 AND status_request = 1", $group_id, $uid);
        if ($res) {
            $is_member = TRUE;
        }
        // check if we are group tutor
        $res = Database::get()->querySingle("SELECT is_tutor FROM mentoring_group_members
                                     WHERE group_id = ?d AND user_id = ?d AND is_tutor = 1 AND status_request = 1", $group_id, $uid);
        if ($res) {
            $is_tutor = $res->is_tutor;
        }
    }

    // check description
    if ($is_tutor || $is_editor_mentoring_program) {
        $res = Database::get()->queryArray("SELECT description,user_id FROM mentoring_group_members
                                     WHERE group_id = ?d", $group_id);
        foreach ($res as $d) {
            if (!empty($d->description) or $d->description != '') {
                $user_group_description .= display_user($d->user_id, false, false)."<br>$d->description<br><br>";
            }
        }
    } else {
        if (isset($uid)) {
            $res = Database::get()->querySingle("SELECT description FROM mentoring_group_members
                                         WHERE group_id = ?d AND user_id = ?d AND is_tutor != 1 AND status_request = 1", $group_id, $uid);
            if ($res) {
                $user_group_description .= $res->description;
            }
        }
    }
}


/**
 * @brief find group tutors
 * @param type $group_id
 * @return type
 */
function mentoring_group_tutors($group_id) {

    $tutors = array();
    $res = Database::get()->queryArray("SELECT user.id AS user_id, surname, givenname, has_icon FROM mentoring_group_members, user
             WHERE group_id = ?d AND
                   is_tutor = 1 AND
                   status_request = 1 AND
                   mentoring_group_members.user_id = user.id
             ORDER BY surname, givenname", $group_id);
    foreach ($res as $tutor) {
        $tutors[] = $tutor;
    }
    return $tutors;
}


/**
 * @brief group settings for allow upload mentees
 * @global type $program_group_id
 * @return boolean
 */
function mentoring_setting_get($program_group_id){
    

    $set = Database::get()->querySingle("SELECT allow_manage_doc FROM mentoring_group WHERE id = ?d",$program_group_id)->allow_manage_doc;
    if($set == 1){
        return true;
    }else{
        return false;
    }

}

function mentoring_file_playurl($path, $filename, $program_group_id) {
    global $urlServer,$mentoring_program_code;

            if (defined('MENTORING_COMMON_DOCUMENTS')){
                $mentoring_program_code = 'common';
            }

            $gid = defined('MENTORING_GROUP_DOCUMENTS') ? ",$program_group_id" : '';
            return htmlspecialchars($urlServer .
            "modules/mentoring/programs/group/document/play.php?$mentoring_program_code$gid" .
              public_file_path($path, $filename), ENT_QUOTES);   
}


function mentoring_file_url($path, $filename, $program_group_id) {
    global $urlServer,$mentoring_program_code,$uid,$mentoring_platform;

    if (defined('MENTORING_COMMON_DOCUMENTS')) {
        $programCode = 'common';
        $grid = '';
        return htmlspecialchars($urlServer .
            "modules/mentoring/programs/group/document/file.php?$programCode$grid" .
            public_file_path($path, $filename), ENT_QUOTES);
    }elseif (defined('MENTORING_MYDOCS')) {
        $tmp = 'user';
        $gid = ",$uid";
        return htmlspecialchars($urlServer .
            "modules/mentoring/programs/group/document/file.php?$tmp$gid" .
            public_file_path($path, $filename), ENT_QUOTES);
    }else{
        $gid = ",$program_group_id";
        return htmlspecialchars($urlServer .
            "modules/mentoring/programs/group/document/file.php?$mentoring_program_code$gid" .
            public_file_path($path, $filename), ENT_QUOTES);
    }
}

/**
 * @brief check recourse accessibility
 * @global type $mentoring_program_code
 * @param type $public
 * @return boolean
 */
function mentoring_resource_access($visible, $public) {
    global $mentoring_program_code;
    if ($visible) {
        if ($public) {
            return TRUE;
        } else {
            if (isset($_SESSION['uid']) and (isset($mentoring_program_code) and $mentoring_program_code)) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    } else {
        return FALSE;
    }
}


function mentoring_program_access() {
    global $is_admin, $uid, $mentoring_program_id, $mentoring_platform;

    if(isset($mentoring_platform) and $mentoring_platform and isset($mentoring_program_id) and $mentoring_program_id){
        $check = 0;
        $isMemberUserOfProgram = Database::get()->queryArray("SELECT user_id FROM mentoring_programs_user WHERE mentoring_program_id = ?d",$mentoring_program_id);
        if(count($isMemberUserOfProgram) > 0){
            foreach($isMemberUserOfProgram as $u){
                if($u->user_id == $uid or $is_admin){
                    $check = 1;
                }
            }
        }
        if($check != 1){
            redirect_to_home_page("modules/mentoring/programs/show_programs.php");
            //redirect_to_home_page("modules/mentoring/mentoring_platform_home.php");
        }
    }else{
        redirect_to_home_page("modules/mentoring/mentoring_platform_home.php");
    }
}


function after_reconnect_go_to_mentoring_homepage(){
    global $mentoring_platform;

    if(!isset($mentoring_platform) or !$mentoring_platform or get_config('mentoring_always_active')){
        redirect_to_home_page("modules/mentoring/mentoring_platform_home.php");
    }
}

function getNextMeetingForMentee($menteeId,$g_id,$mp_id){ 
    $end = date('Y-m-d',strtotime('now + 30days'));
    $available_rentezvous_of_mentee_id = Database::get()->queryArray("SELECT *FROM mentoring_rentezvous
                                                            WHERE id IN (SELECT mentoring_rentezvous_id FROM mentoring_rentezvous_user WHERE mentee_id = ?d)
                                                            AND mentoring_program_id = ?d
                                                            AND group_id = ?d
                                                            AND start >= NOW()
                                                            AND end <= ?t ORDER BY start ASC",$menteeId,$mp_id,$g_id,$end);

    if(count($available_rentezvous_of_mentee_id) > 0){
        $firstRentezvous = array();
        $counter = 0;
        foreach($available_rentezvous_of_mentee_id as $r){
            if($counter == 0){
                $firstRentezvous[] = [
                    'id' => $r->id,
                    'mentoring_program_id' => $r->mentoring_program_id,
                    'mentor_id' => $r->mentor_id,
                    'title' => $r->title,
                    'start' => $r->start,
                    'end' => $r->end,
                    'type_tc' => $r->type_tc,
                    'api_url' => $r->api_url,
                    'passcode' => $r->passcode
                ];
            }
            $counter++;
        }
        return $firstRentezvous;
    }else{
        return array();
    }
}


function getNextMeetingForMentor($editorId,$g_id,$mp_id){ 
    $end = date('Y-m-d',strtotime('now + 30days'));
    $available_rentezvous_of_mentor_id = Database::get()->queryArray("SELECT *FROM mentoring_rentezvous
                                                            WHERE mentoring_program_id = ?d
                                                            AND mentor_id = ?d
                                                            AND group_id = ?d
                                                            AND start >= NOW()
                                                            AND end <= ?t ORDER BY start ASC",$mp_id,$editorId,$g_id,$end);

    if(count($available_rentezvous_of_mentor_id) > 0){
        $firstRentezvous = array();
        $counter = 0;
        foreach($available_rentezvous_of_mentor_id as $r){
            if($counter == 0){
                $firstRentezvous[] = [
                    'id' => $r->id,
                    'mentoring_program_id' => $r->mentoring_program_id,
                    'mentor_id' => $r->mentor_id,
                    'title' => $r->title,
                    'start' => $r->start,
                    'end' => $r->end,
                    'type_tc' => $r->type_tc,
                    'api_url' => $r->api_url,
                    'passcode' => $r->passcode
                ];
            }
            $counter++;
        }
        return $firstRentezvous;
    }else{
        return array();
    }
}


/**
 * @brief register in group
 * @global type $g_id
 * @global type $mp_id
 * @global type $code
 */
function register_in_group_by_settings($g_id,$mp_id,$code){
    global $uid, $urlAppend, $is_admin;
    global $langGroupIsFull,$langGroupMentoringMembers,$langRegister,$langUnRegister,$langSendGroupRequest;
    global $langRequest, $langContinueActionRequest, $langCancel, $langSend, $langHasSendRequestGroupMentoring;
    global $langStandByState, $langCancelSendRequest, $langReqDeniedFromTutor, $langIsMenteeAtleastOneGroupError,$langUnRegisterDontAllowedFromGroup;

    //is group the common group?
    $is_common_group = Database::get()->querySingle("SELECT common FROM mentoring_group WHERE id = ?d AND mentoring_program_id = ?d",$g_id,$mp_id)->common;

    $common_group_id = Database::get()->querySingle("SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d AND common = ?d",$mp_id,1)->id;
    
    $one_or_many_group_register = Database::get()->querySingle("SELECT other_groups_reg FROM mentoring_programs WHERE id = ?d AND code = ?s",$mp_id,$code)->other_groups_reg;

    $group_properties = Database::get()->queryArray("SELECT self_registration,allow_unregister,self_request FROM mentoring_group_properties
                                                     WHERE mentoring_program_id = ?d AND group_id = ?d",$mp_id,$g_id);

    $max_members_of_group = Database::get()->querySingle("SELECT max_members FROM mentoring_group WHERE id = ?d AND mentoring_program_id = ?d",$g_id,$mp_id)->max_members;

    $all_members_in_group = Database::get()->querySingle("SELECT COUNT(user_id) as ui FROM mentoring_group_members
                                               WHERE group_id = ?d
                                               AND is_tutor = ?d AND status_request = ?d",$g_id,0,1)->ui;

    $uid_is_member_of_group = Database::get()->querySingle("SELECT COUNT(user_id) as ui FROM mentoring_group_members
                                                            WHERE group_id = ?d AND group_id NOT IN (?d) AND user_id = ?d
                                                            AND is_tutor = ?d AND status_request = ?d",$g_id,$common_group_id,$uid,0,1)->ui;

    $uid_is_member_of_common_group = Database::get()->querySingle("SELECT COUNT(user_id) as ui FROM mentoring_group_members
                                                            WHERE group_id = ?d AND group_id IN (?d) AND user_id = ?d
                                                            AND is_tutor = ?d AND status_request = ?d",$g_id,$common_group_id,$uid,0,1)->ui;

    $check_if_uid_is_member_in_other_groups = Database::get()->querySingle("SELECT COUNT(user_id) as ui FROM mentoring_group_members
                                                                            WHERE group_id NOT IN (SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d AND common = ?d)
                                                                            AND group_id NOT IN (?d)
                                                                            AND group_id IN (SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d AND common = ?d)
                                                                            AND user_id = ?d
                                                                            AND is_tutor = ?d
                                                                            AND status_request = ?d",$mp_id,1,$g_id,$mp_id,0,$uid,0,1)->ui;

    $check_if_uid_has_sent_request = Database::get()->querySingle("SELECT COUNT(user_id) as ui FROM mentoring_group_members
                                                                   WHERE group_id = ?d AND user_id = ?d AND is_tutor = ?d AND status_request = ?d",$g_id,$uid,0,0)->ui;

    $check_if_request_has_denied_from_tutor = Database::get()->querySingle("SELECT COUNT(user_id) as ui FROM mentoring_group_members
                                                                   WHERE group_id = ?d AND user_id = ?d AND is_tutor = ?d AND status_request = ?d",$g_id,$uid,0,2)->ui;

    $selfRg = "selfReg=1&group_id=". getIndirectReference($g_id) . "&uid=" .getIndirectReference($uid);
    $selfUnRg = "selfUnReg=1&group_id=" .getIndirectReference($g_id). "&uid=" .getIndirectReference($uid);

    $groupmodal = getIndirectReference($g_id);

    $html_group = '';
    
    // if group isnt common group
    if(!$is_common_group){
        // check members count to continue
        if($max_members_of_group == 0 or ($max_members_of_group > 0 and $all_members_in_group < $max_members_of_group)){
            //group properties
            foreach($group_properties as $p){
                // Alone registration and can unregister
                if($p->self_registration == 1 and $p->allow_unregister == 1 and $p->self_request == 0){//reg-unreg alone
                    if($uid_is_member_of_group == 0){// uid isnt member of group so can register alone
                        if(($check_if_uid_is_member_in_other_groups == 0 and $one_or_many_group_register == 1) or $one_or_many_group_register == 0){// uid not in member in other group so can register in current group
                            $html_group .= "<a href='{$urlAppend}modules/mentoring/programs/group/group_space.php?$selfRg'>
                                                <span class='fa fa-sign-in fs-3' title='' data-bs-original-title='$langRegister' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                            </a>";
                        }else{
                            $html_group .= "<p class='small-text text-secondary TextSemiBold'>$langIsMenteeAtleastOneGroupError</p>";
                        }
                    }else{
                        $html_group .= "<a href='{$urlAppend}modules/mentoring/programs/group/group_space.php?$selfUnRg' style='color:#d9534f;'>
                                            <span class='fa fa-sign-out fs-3' title='' data-bs-original-title='$langUnRegister' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                        </a>";
                    }

                }elseif($p->self_registration == 1 and $p->allow_unregister == 0 and $p->self_request == 0){//reg alone , not unreg
                    if($uid_is_member_of_group == 0){// uid isnt member of group so can register alone
                        if(($check_if_uid_is_member_in_other_groups == 0 and $one_or_many_group_register == 1) or $one_or_many_group_register == 0){// uid not in member in other group so can register in current group
                            $html_group .= "<a href='{$urlAppend}modules/mentoring/programs/group/group_space.php?$selfRg'>
                                                <span class='fa fa-sign-in fs-3' title='' data-bs-original-title='$langRegister' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                            </a>";
                        }else{
                            $html_group .= "<p class='small-text text-secondary TextSemiBold'>$langIsMenteeAtleastOneGroupError</p>";
                        }
                    }
                }elseif($p->self_registration == 0 and $p->allow_unregister == 1 and $p->self_request == 1){// send request and can unreg
                    if($uid_is_member_of_group == 0){// uid isnt member of group so can register alone
                        if($check_if_uid_has_sent_request == 0 and $check_if_request_has_denied_from_tutor == 0){// uid hasnt sent request so can send request
                            if(($check_if_uid_is_member_in_other_groups == 0 and $one_or_many_group_register == 1) or $one_or_many_group_register == 0){// uid not in member in other group so can register in current group
                                $html_group .= "<button class='btn btn-outline-primary small-text' data-bs-toggle='modal' data-bs-target='#SendRequestModal$g_id'>
                                                            $langSendGroupRequest
                                                    </button>";
                            }else{
                                $html_group .= "<p class='small-text text-secondary TextSemiBold'>$langIsMenteeAtleastOneGroupError</p>";
                            }
                        }else{// uid has sent request so can cancel request if request not denied by tutor
                            if($check_if_request_has_denied_from_tutor == 0){ // uid can cancel request
                                $html_group .= "<div class='d-flex flex-wrap justify-content-start align-items-center'><span class='small-text me-2'>$langHasSendRequestGroupMentoring</span>
                                                <span class='fa fa-spinner'></span>
                                                <span class='pe-2 TextBold blackBlueText'>$langStandByState</span>
                                                <button class='btn btn-outline-danger btn-sm small-text mt-0 pt-0 pb-0 ps-1 pe-1 rounded-2'
                                                        data-bs-toggle='modal' data-bs-target='#CancelRequestModal$g_id'>
                                                        <span class='fa fa-times'></span>
                                                </button></div>";
                            }else{// uid cant cancel request because his request has denied from tutor
                                $html_group .= "<p class='text-warning small-text mt-1 TextSemiBold'>$langReqDeniedFromTutor</p>";
                            }
                        }
                    }else{// uid is member of group so can unregister alone
                        $html_group .= "<a href='{$urlAppend}modules/mentoring/programs/group/group_space.php?$selfUnRg' style='color:#d9534f;'>
                                            <span class='fa fa-sign-out fs-3' title='' data-bs-original-title='$langUnRegister' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                        </a>";
                    }
                }elseif($p->self_registration == 0 and $p->allow_unregister == 0 and $p->self_request == 1){// send request and cant unreg
                    if($uid_is_member_of_group == 0){// uid isnt member of group so can register alone
                        if($check_if_uid_has_sent_request == 0 and $check_if_request_has_denied_from_tutor == 0){// uid hasnt sent request so can send request
                            if(($check_if_uid_is_member_in_other_groups == 0 and $one_or_many_group_register == 1) or $one_or_many_group_register == 0){// uid not in member in other group so can register in current group
                                $html_group .= "<button class='btn btn-outline-primary small-text' data-bs-toggle='modal' data-bs-target='#SendRequestModal$g_id'>
                                                            $langSendGroupRequest
                                                    </button>";
                            }else{
                                $html_group .= "<p class='small-text text-secondary TextSemiBold'>$langIsMenteeAtleastOneGroupError</p>";
                            }
                        }else{// uid has sent request so can cancel request if request not denied by tutor
                            if($check_if_request_has_denied_from_tutor == 0){ // uid can cancel request
                                $html_group .= "<div class='d-flex flex-wrap justify-content-start align-items-center'><span class='small-text me-2'>$langHasSendRequestGroupMentoring</span>
                                                <span class='fa fa-spinner'></span>
                                                <span class='pe-2 TextBold blackBlueText'>$langStandByState</span>
                                                <button class='btn btn-outline-danger btn-sm small-text mt-0 pt-0 pb-0 ps-1 pe-1 rounded-2'
                                                        data-bs-toggle='modal' data-bs-target='#CancelRequestModal$g_id'>
                                                        <span class='fa fa-times'></span>
                                                </button></div>";
                            }else{// uid cant cancel request because his request has denied from tutor
                                $html_group .= "<p class='text-warning small-text mt-1 TextSemiBold'>$langReqDeniedFromTutor</p>";
                            }
                        }
                    }
                }
            }
        }else{
            
            foreach($group_properties as $p){
                if($p->allow_unregister == 1){//epitrepetai h apeggrafh
                    if($uid_is_member_of_group > 0){//einai melos
                        $html_group .= "<a href='{$urlAppend}modules/mentoring/programs/group/group_space.php?$selfUnRg' style='color:#d9534f;'>
                                            <span class='fa fa-sign-out fs-3' title='' data-bs-original-title='$langUnRegister' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                        </a>";
                    }else{
                        if($check_if_uid_is_member_in_other_groups == 0){
                            if($p->self_registration == 1 and $p->self_request == 0){
                                $html_group .= "<a class='opacity-help pe-none' href='{$urlAppend}modules/mentoring/programs/group/group_space.php?$selfRg'>
                                                    <span class='fa fa-sign-in fs-3' title='' data-bs-original-title='$langRegister' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                                </a>";
                            }
                            if($p->self_registration == 0 and $p->self_request == 1){
                                $html_group .= "<button class='btn btn-outline-primary small-text opacity-help pe-none' data-bs-toggle='modal' data-bs-target='#SendRequestModal$g_id'>
                                                                $langSendGroupRequest
                                                </button>";
                            }
                        }else{
                            if($one_or_many_group_register == 1){
                                $html_group .= "<p class='small-text text-secondary TextSemiBold'>$langIsMenteeAtleastOneGroupError</p>";
                            }else{
                                if($p->self_registration == 1 and $p->self_request == 0){
                                    $html_group .= "<a class='opacity-help pe-none' href='{$urlAppend}modules/mentoring/programs/group/group_space.php?$selfRg'>
                                                        <span class='fa fa-sign-in fs-3' title='' data-bs-original-title='$langRegister' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                                    </a>";
                                }
                                if($p->self_registration == 0 and $p->self_request == 1){
                                    $html_group .= "<button class='btn btn-outline-primary small-text opacity-help pe-none' data-bs-toggle='modal' data-bs-target='#SendRequestModal$g_id'>
                                                                    $langSendGroupRequest
                                                    </button>";
                                }
                            }
                            
                        }
                    }
                }else{
                    $html_group .= "<p class='text-warning small-text mt-1 TextSemiBold'>$langUnRegisterDontAllowedFromGroup</p>";
                }
            }
        }
    }else{ // group is the common group

        $clickerBtn = "";
        if($max_members_of_group > 0 and $all_members_in_group >= $max_members_of_group){
            $clickerBtn = "pe-none opacity-help";
        }

        $TutorsOfCommonGroup = Database::get()->queryArray("SELECT user_id FROM mentoring_group_members
                                                            WHERE group_id = ?d AND is_tutor = ?d",$g_id,1);
        if(count($TutorsOfCommonGroup) > 0){
            $arrTut = array();
            foreach($TutorsOfCommonGroup as $t){
                $arrTut[] = $t->user_id;
            }

            if(in_array($uid,$arrTut)){
                $clickerBtn = "pe-none opacity-help";
            }
        }

        // check members count to continue
        if($max_members_of_group == 0 or ($max_members_of_group > 0 and $all_members_in_group <= $max_members_of_group)){
            foreach($group_properties as $p){
                if($p->self_registration == 1 and $p->allow_unregister == 1 and $p->self_request == 0){//reg-unreg alone
                    if($uid_is_member_of_common_group == 0){// uid isnt member of common group so can register alone
                        $html_group .= "<a class='$clickerBtn' href='{$urlAppend}modules/mentoring/programs/group/group_space.php?$selfRg'>
                                            <span class='fa fa-sign-in fs-3' title='' data-bs-original-title='$langRegister' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                        </a>";
                    }else{
                        $html_group .= "<a href='{$urlAppend}modules/mentoring/programs/group/group_space.php?$selfUnRg' style='color:#d9534f;'>
                                            <span class='fa fa-sign-out fs-3' title='' data-bs-original-title='$langUnRegister' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                        </a>";
                    }
                }elseif($p->self_registration == 1 and $p->allow_unregister == 0 and $p->self_request == 0){//reg alone , not unreg
                    if($uid_is_member_of_common_group == 0){// uid isnt member of common group so can register alone
                        $html_group .= "<a class='$clickerBtn' href='{$urlAppend}modules/mentoring/programs/group/group_space.php?$selfRg'>
                                            <span class='fa fa-sign-in fs-3' title='' data-bs-original-title='$langRegister' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                        </a>";
                    }
                }elseif($p->self_registration == 0 and $p->allow_unregister == 1 and $p->self_request == 1){// send request and can unreg
                    if($uid_is_member_of_common_group == 0){// uid isnt member of common group so can register alone
                        if($check_if_uid_has_sent_request == 0 and $check_if_request_has_denied_from_tutor == 0){// uid hasnt sent request so can send request
                            $html_group .= "<button class='btn btn-outline-primary small-text $clickerBtn' data-bs-toggle='modal' data-bs-target='#SendRequestModal$g_id'>
                                                        $langSendGroupRequest
                                                </button>";
                        }else{// uid has sent request so can cancel request if request not denied by tutor
                            if($check_if_request_has_denied_from_tutor == 0){ // uid can cancel request
                                $html_group .= "<div class='d-flex flex-wrap justify-content-start align-items-center'><span class='small-text me-2'>$langHasSendRequestGroupMentoring</span>
                                                <span class='fa fa-spinner'></span>
                                                <span class='pe-2 TextBold blackBlueText'>$langStandByState</span>
                                                <button class='btn btn-outline-danger btn-sm small-text mt-0 pt-0 pb-0 ps-1 pe-1 rounded-2'
                                                        data-bs-toggle='modal' data-bs-target='#CancelRequestModal$g_id'>
                                                        <span class='fa fa-times'></span>
                                                </button></div>";
                            }else{// uid cant cancel request because his request has denied from tutor
                                $html_group .= "<p class='text-warning small-text mt-1 TextSemiBold'>$langReqDeniedFromTutor</p>";
                            }
                        }
                    }else{// uid is member of group so can unregister alone
                        $html_group .= "<a href='{$urlAppend}modules/mentoring/programs/group/group_space.php?$selfUnRg' style='color:#d9534f;'>
                                            <span class='fa fa-sign-out fs-3' title='' data-bs-original-title='$langUnRegister' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                        </a>";
                    }
                }elseif($p->self_registration == 0 and $p->allow_unregister == 0 and $p->self_request == 1){// send request and cant unreg
                    if($uid_is_member_of_common_group == 0){// uid isnt member of common group so can register alone
                        if($check_if_uid_has_sent_request == 0 and $check_if_request_has_denied_from_tutor == 0){// uid hasnt sent request so can send request
                            $html_group .= "<button class='btn btn-outline-primary small-text $clickerBtn' data-bs-toggle='modal' data-bs-target='#SendRequestModal$g_id'>
                                                            $langSendGroupRequest
                                                    </button>";
                        }else{// uid has sent request so can cancel request if request not denied by tutor
                            if($check_if_request_has_denied_from_tutor == 0){ // uid can cancel request
                                $html_group .= "<div class='d-flex flex-wrap justify-content-start align-items-center'><span class='small-text me-2'>$langHasSendRequestGroupMentoring</span>
                                                <span class='fa fa-spinner'></span>
                                                <span class='pe-2 TextBold blackBlueText'>$langStandByState</span>
                                                <button class='btn btn-outline-danger btn-sm small-text mt-0 pt-0 pb-0 ps-1 pe-1 rounded-2'
                                                        data-bs-toggle='modal' data-bs-target='#CancelRequestModal$g_id'>
                                                        <span class='fa fa-times'></span>
                                                </button></div>";
                            }else{// uid cant cancel request because his request has denied from tutor
                                $html_group .= "<p class='text-warning small-text mt-1 TextSemiBold'>$langReqDeniedFromTutor</p>";
                            }
                        }
                    }
                }
            }
        }else{
            
            foreach($group_properties as $p){
                if($p->allow_unregister == 1){//epitrepetai h apeggrafh
                    if($uid_is_member_of_common_group > 0){//einai melos
                        $html_group .= "<a href='{$urlAppend}modules/mentoring/programs/group/group_space.php?$selfUnRg' style='color:#d9534f;'>
                                            <span class='fa fa-sign-out fs-3' title='' data-bs-original-title='$langUnRegister' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                        </a>";
                    }else{
                        if($check_if_uid_is_member_in_other_groups == 0){
                            if($p->self_registration == 1 and $p->self_request == 0){
                                $html_group .= "<a class='opacity-help pe-none' href='{$urlAppend}modules/mentoring/programs/group/group_space.php?$selfRg'>
                                                    <span class='fa fa-sign-in fs-3' title='' data-bs-original-title='$langRegister' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                                </a>";
                            }
                            if($p->self_registration == 0 and $p->self_request == 1){
                                $html_group .= "<button class='btn btn-outline-primary small-text opacity-help pe-none' data-bs-toggle='modal' data-bs-target='#SendRequestModal$g_id'>
                                                                $langSendGroupRequest
                                                </button>";
                            }
                        }else{
                            if($one_or_many_group_register == 1){
                                $html_group .= "<p class='small-text text-secondary TextSemiBold'>$langIsMenteeAtleastOneGroupError</p>";
                            }else{
                                if($p->self_registration == 1 and $p->self_request == 0){
                                    $html_group .= "<a class='opacity-help pe-none' href='{$urlAppend}modules/mentoring/programs/group/group_space.php?$selfRg'>
                                                        <span class='fa fa-sign-in fs-3' title='' data-bs-original-title='$langRegister' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                                    </a>";
                                }
                                if($p->self_registration == 0 and $p->self_request == 1){
                                    $html_group .= "<button class='btn btn-outline-primary small-text opacity-help pe-none' data-bs-toggle='modal' data-bs-target='#SendRequestModal$g_id'>
                                                                    $langSendGroupRequest
                                                    </button>";
                                }
                            }
                        }
                    }
                }else{
                    $html_group .= "<p class='text-warning small-text mt-1 TextSemiBold'>$langUnRegisterDontAllowedFromGroup</p>";
                }
            }
        }
    }


    //////////////////////////////// About modals /////////////////////////////////
    // uid send request
    $html_group .= "<div class='modal fade' id='SendRequestModal$g_id' tabindex='-1' aria-labelledby='SendRequestModalLabel$g_id' aria-hidden='true'>
                        <form method='post' action='{$urlAppend}modules/mentoring/programs/group/group_space.php?group_id=$groupmodal&fromRegModals'>
                            <div class='modal-dialog modal-md modal-primary'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='SendRequestModalLabel$g_id'>$langRequest</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <div class='modal-body'>
                                        $langContinueActionRequest
                                        <input type='hidden' name='mentoring_program_id' value='$mp_id'>
                                        <input type='hidden' name='group_id' value='$g_id'>
                                        <input type='hidden' name='mentee_id' value='$uid'>
                                        
                                    </div>
                                    <div class='modal-footer'>
                                        <a class='btn btn-outline-secondary small-text rounded-2' href='' data-bs-dismiss='modal'>$langCancel</a>
                                        <button type='submit' class='btn btn-primary small-text rounded-2' name='action_send_request' value='send_request'>
                                            $langSend
                                        </button>
                                        
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>";

    // uid cancel request
    $html_group .= "<div class='modal fade' id='CancelRequestModal$g_id' tabindex='-1' aria-labelledby='CancelRequestModalLabel$g_id' aria-hidden='true'>
                        <form method='post' action='{$urlAppend}modules/mentoring/programs/group/group_space.php?group_id=$groupmodal&fromRegModals'>
                            <div class='modal-dialog modal-md modal-danger'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='CancelRequestModalLabel$g_id'>$langRequest</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <div class='modal-body'>
                                        $langContinueActionRequest
                                        <input type='hidden' name='mentoring_program_id' value='$mp_id'>
                                        <input type='hidden' name='group_id' value='$g_id'>
                                        <input type='hidden' name='mentee_id' value='$uid'>
                                        
                                    </div>
                                    <div class='modal-footer'>
                                        <a class='btn btn-outline-secondary small-text rounded-2' href='' data-bs-dismiss='modal'>$langCancel</a>
                                        <button type='submit' class='btn btn-danger small-text rounded-2' name='action_cancel_request' value='cancel_request'>
                                            $langCancelSendRequest
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>";

    return $html_group;

}

