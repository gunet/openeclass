<?php         

$require_login = TRUE;


require_once '../../../include/baseTheme.php';
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'modules/mentoring/functions.php';

//after_reconnect_go_to_mentoring_homepage();

load_js('select2');

$head_content .= "<script type='text/javascript'>
    $(document).ready(function () {
        $('#select-tutors').select2();
    });
    </script>
    <script type='text/javascript' src='{$urlAppend}js/tools.js'></script>\n
";

$toolName = $langCreateMentoringProgramms;

$data['is_editor_mentoring'] = is_editor_mentoring($uid);

$data['users_mentors'] = Database::get()->queryArray("SELECT *FROM user WHERE is_mentor = ?d",1);
$tutor_mentoring = Database::get()->querySingle("SELECT id,givenname,surname FROM user WHERE id = ?d",$uid);
$data['tutor_name'] = $tutor_mentoring->givenname . " " . $tutor_mentoring->surname;

$data['lang_select_options'] = lang_select_options('localize', "class='form-control'");

$data['all_specializations'] = Database::get()->queryArray("SELECT *FROM mentoring_specializations");

$title = '';
$code = '';
$description = '';
$description_group = '';
$check_mentor_edit = false;
$start_date = date('Y-m-d H:i:s', strtotime('now'));
$end_date = date('Y-m-d H:i:s', strtotime('now +12 months'));

// create mentoring program
if(isset($_POST['create_mentoring_program'])){

    if(isset($_POST['startdate']) and isset($_POST['enddate']) and $_POST['startdate'] > $_POST['enddate']){
        Session::flash('message',$langInvalidDates);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('modules/mentoring/programs/create_program.php');
    }

    if(count($_POST['check_mentor']) == 1){
        foreach($_POST['check_mentor'] as $c){
            if($c == ''){
                $_POST['check_mentor'] = array();
            }
        }
    }
    if(!empty($_POST['title']) and !empty($_POST['code']) and count($_POST['check_mentor']) > 0 and isset($_POST['mentoring_tutor']) and count($_POST['mentoring_tutor']) > 0 and !empty($_POST['group_name'])){

        if (preg_match('/[^A-Za-z0-9]/', $_POST['code'])){
            // code dont contain only english letters & digits
            Session::flash('message',$langOnlyNumbersLetters);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page('modules/mentoring/programs/create_program.php');
        }

        // check if code exists
        $check_code = check_exist_mentoring_code_db($_POST['code']);

        // if doesnt exist
        if(!$check_code){
            $mentors_ids = array();
            $tutors_ids = array();
            $title = $_POST['title'];
            $code = $_POST['code'];
            $tutors_ids = isset($_POST['mentoring_tutor']) ? $_POST['mentoring_tutor'] : array();
            $language_mentoring_program = $_POST['localize'];
            $description = purify($_POST['description']);
            if(isset($_POST['check_mentor']) and count($_POST['check_mentor']) > 0){
                foreach($_POST['check_mentor'] as $m_ids){
                    $mentors_ids = explode(',', $m_ids);
                }
                if(count($mentors_ids) > 0){
                    $mentors_ids = array_unique($mentors_ids);
                }
            }else{
                $_POST['check_mentor'] = array();
            }
            $start_date = $_POST['startdate'];
            $end_date = $_POST['enddate'];
            $keywords = '';
            $program_image = $_FILES['image_mentoring_program']['name'];
            $tmp_name = '';
            $size = '';
            if(!empty($program_image)){
              $tmp_name = $_FILES['image_mentoring_program']['tmp_name'];
              $size = $_FILES["image_mentoring_program"]["size"];
            }
            $allow_unreg_mentee_from_program = isset($_POST['yes_allow_unreg']) ? $_POST['yes_allow_unreg'] : 0;
            
               
            $mentoring_program_id = create_mentoring_program($code, $language_mentoring_program, $title, $tutors_ids, $start_date, $end_date,
                                                                $keywords, $program_image, $description, $mentors_ids, $webDir, $tmp_name, $size, $allow_unreg_mentee_from_program);
    
            if($mentoring_program_id  > 0){

                //create common group

                $group_max = $_POST['group_max'];
                $group_quantity = $_POST['group_quantity'] = 1;
                $group_description = isset($_POST['description_group']) ? $_POST['description_group'] : '';
                $private_forum = isset($_POST['private_forum']) ? $_POST['private_forum'] : 0;
                if (isset($_POST['group_name'])) {
                    $group_name = $_POST['group_name'];
                }

                if (isset($_POST['all'])) { // default values if we create multiple groups
                    $self_reg = 1;
                    $allow_unreg = 0;
                } else {
                    if (isset($_POST['self_reg']) and $_POST['self_reg'] == 'on') {
                        $self_reg = 1;
                    } else {
                        $self_reg = 0;
                    }

                    if (isset($_POST['self_request']) and $_POST['self_request'] == 'on') {
                        $self_request = 1;
                    } else {
                        $self_request = 0;
                    }

                    if (isset($_POST['allow_unreg']) and $_POST['allow_unreg'] == 'on') {
                        $allow_unreg = 1;
                    } else {
                        $allow_unreg = 0;
                    }
                }
                if (isset($_POST['forum']) and $_POST['forum'] == 'on') {
                    $has_forum = 1;
                } else {
                    $has_forum = 0;
                }

                if (isset($_POST['documents']) and $_POST['documents'] == 'on'){
                    $documents = 1;
                } else {
                    $documents = 0;
                }

                if (isset($_POST['announcements']) and $_POST['announcements'] == 'on'){
                    $announcements = 1;
                }else{
                    $announcements = 0;
                }
        
                if (isset($_POST['wall']) and $_POST['wall'] == 'on'){
                    $wall = 1;
                }else{
                    $wall = 0;
                }

                $group_num = Database::get()->querySingle("SELECT COUNT(*) AS count FROM `mentoring_group` WHERE mentoring_program_id = ?d", $mentoring_program_id)->count;

                // Create a hidden category for group forums
                $req = Database::get()->querySingle("SELECT id FROM mentoring_forum_category
                                        WHERE cat_order = -1
                                        AND mentoring_program_id = ?d", $mentoring_program_id);
                if ($req) {
                    $cat_id = $req->id;
                } else {
                    $req2 = Database::get()->query("INSERT INTO mentoring_forum_category (cat_title, cat_order, mentoring_program_id)
                                                VALUES (?s, -1, ?d)", $langCatagoryGroup, $mentoring_program_id);
                    $cat_id = $req2->lastInsertID;
                }
                for ($i = 1; $i <= $group_quantity; $i++) {
                    if (isset($_POST['all'])) {
                        $g_name = "$langGroup $group_num";
                        $res = Database::get()->query("SELECT id FROM `mentoring_group` WHERE name = '$langGroup ". $group_num . "'");
                        if ($res) {
                            $group_num++;
                        }
                        $forumname = "$langForumGroup $group_num";
                    } else {
                        $g_name = $_POST['group_name'];
                        $forumname = "$langForumGroup $g_name";
                    }
                    // Create a unique path to group documents to try (!)
                    // avoiding groups entering other groups area
                    $secretDirectory = uniqid('');
                    make_dir("mentoring_programs/$code/group/$secretDirectory");
                    touch("mentoring_programs/$code/group/index.php");
                    touch("mentoring_programs/$code/group/$secretDirectory/index.php");

                    // group forum creation
                    $q = Database::get()->query("INSERT INTO mentoring_forum SET name = ?s,
                                                        `desc` = ' ', num_topics = 0,
                                                        num_posts = 0, last_post_id = 1,
                                                        cat_id = ?d, mentoring_program_id = ?d", $forumname, $cat_id, $mentoring_program_id);
                    $forum_id = $q->lastInsertID;

                    $_POST['selectcategory'] = null;
                    $id = Database::get()->query("INSERT INTO `mentoring_group` SET
                                                mentoring_program_id = ?d,
                                                name = ?s,
                                                description = ?s,
                                                forum_id = ?d,
                                                max_members = ?d,
                                                secret_directory = ?s,
                                                category_id = ?d,
                                                common = ?d",
                                        $mentoring_program_id, $g_name, $group_description, $forum_id, $group_max, $secretDirectory, $_POST['selectcategory'],1)->lastInsertID;
                    Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_GROUP, MENTORING_LOG_INSERT, array('uid' => '','group_title' => $g_name, 'type' => 'insert_group'));


                    $common_tutors_array = array();
                    if (isset($_POST['common_tutor'])) {
                        foreach($_POST['common_tutor'] as $common_tutor){
                            $common_tutors_array = explode (',', $common_tutor);
                        }
                        foreach($common_tutors_array as $ctutor){
                            Database::get()->query("INSERT INTO mentoring_group_members SET group_id = ?d, user_id = ?d, is_tutor = 1, status_request = 1", $id, $ctutor);
                            Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_GROUP, MENTORING_LOG_INSERT, array('uid' => display_user($ctutor,false,false),'group_title' => $group_title, 'type' => 'insert_tutor_group'));
                        }
                        
                    } 


                    $query_vars = [
                        $mentoring_program_id,
                        $id,
                        $self_reg,
                        $self_request,
                        $allow_unreg,
                        $has_forum,
                        $private_forum,
                        $documents,
                        $announcements,
                        $wall
                    ];

                    $group_info = Database::get()->query("INSERT INTO `mentoring_group_properties` SET mentoring_program_id = ?d,
                                                                        group_id = ?d, self_registration = ?d, self_request = ?d,
                                                                        allow_unregister = ?d,
                                                                        forum = ?d, private_forum = ?d,
                                                                        documents = ?d, announcements = ?d, wall = ?d,
                                                                        agenda = 0", $query_vars);


                    // add mentors of program into common group automatically
                    $mentors_Of_program = Database::get()->queryArray("SELECT user_id FROM mentoring_programs_user
                                                                        WHERE mentoring_program_id = ?d
                                                                        AND mentor = ?d",$mentoring_program_id,1);
                    if(count($mentors_Of_program) > 0){
                        $theCommonGroupId = Database::get()->querySingle("SELECT id FROM mentoring_group
                                                                            WHERE mentoring_program_id = ?d
                                                                            AND common = ?d",$mentoring_program_id,1)->id;
                        if($theCommonGroupId > 0){
                            $max_members_of_group = Database::get()->querySingle("SELECT max_members FROM mentoring_group WHERE id = ?d AND mentoring_program_id = ?d",$theCommonGroupId,$mentoring_program_id)->max_members;
                            foreach($mentors_Of_program as $um){

                                $is_mentor_the_tutor_of_program = Database::get()->querySingle("SELECT tutor FROM mentoring_programs_user
                                                                                                WHERE mentoring_program_id = ?d 
                                                                                                AND user_id = ?d",$mentoring_program_id, $um->user_id)->tutor;

                                
                                $all_members_in_group = Database::get()->querySingle("SELECT COUNT(user_id) as ui FROM mentoring_group_members
                                                                                        WHERE group_id = ?d
                                                                                        AND is_tutor = ?d AND status_request = ?d",$theCommonGroupId,0,1)->ui;

                                if($max_members_of_group == 0 or ($max_members_of_group > 0 and $all_members_in_group < $max_members_of_group)){
                                    Database::get()->query("INSERT INTO mentoring_group_members SET
                                                            group_id = ?d,
                                                            user_id = ?d,
                                                            is_tutor = ?d,
                                                            status_request = ?d",$theCommonGroupId, $um->user_id, $is_mentor_the_tutor_of_program, 1);
                                }
                            }
                        }
                    }

                }


                Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_PROGRAM, MENTORING_LOG_CREATE_PROGRAM, array('title' => $title,'code' => $code));
                Session::flash('message',$langReqMentoringDone);
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page('mentoring_programs/'.$code.'/index.php');

            }else{
                Session::flash('message',$langNoReqMentoringDone);
                Session::flash('alert-class', 'alert-danger');
                redirect_to_home_page('modules/mentoring/programs/create_program.php');
            }

            
        }else{
            Session::flash('message',$langCodeExistMentoring);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/mentoring/programs/create_program.php');
        }
    }else{
        Session::flash('message',$langFieldsMissing);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page('modules/mentoring/programs/create_program.php');
    }
    
}
$data['title'] = $title;
$data['code'] = $code;
$data['rich_text_editor'] = rich_text_editor('description', 4, 20, $description);
$data['check_mentor_edit'] = $check_mentor_edit;
$data['startdate'] = $start_date;
$data['enddate'] = $end_date;

//about common group
$data['rich_text_editor2'] = rich_text_editor('description_group', 4, 20, $description_group);

$data['group_max_value'] = Session::has('group_max') ? Session::get('group_max') : 0;
$data['group_quantity_value'] = Session::has('group_quantity') ? Session::get('group_quantity') : 1;

load_js('tools.js');
load_js('bootstrap-datetimepicker');


view('modules.mentoring.programs.create_program', $data);

