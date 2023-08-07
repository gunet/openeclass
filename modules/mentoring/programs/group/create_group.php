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

$toolName = $langCreateMentoringGroup;

$data['mentees'] = get_all_guides_from_mentoring_program($mentoring_program_id);
$data['rich_text_editor'] = rich_text_editor('description', 4, 20, '');

$data['group_max_value'] = Session::has('group_max') ? Session::get('group_max') : 0;
$data['group_quantity_value'] = Session::has('group_quantity') ? Session::get('group_quantity') : 1;

if (isset($_POST['creation'])) { // groups creation
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('group_quantity'));
    $v->rule('numeric', array('group_quantity'));
    $v->rule('min', array('group_quantity'), 1);
    $v->rule('required', array('group_max'));
    $v->rule('numeric', array('group_max'));
    $v->rule('min', array('group_max'), 0);
    $v->labels(array(
        'group_quantity' => "$langTheField $langNewGroups",
        'group_max' => "$langTheField $langNewGroupMembers"
    ));

    
    if($v->validate() and ((!isset($_POST['ingroup'])) or (count($_POST['ingroup']) <= $_POST['group_max']) or (isset($_POST['ingroup']) and $_POST['group_max'] == 0))) {
        $group_max = $_POST['group_max'];
        $group_quantity = $_POST['group_quantity'] = 1;
        $group_description = isset($_POST['description']) ? $_POST['description'] : '';
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
        } else {
            $announcements = 0;
        }

        if (isset($_POST['wall']) and $_POST['wall'] == 'on'){
            $wall = 1;
        } else {
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
            make_dir("mentoring_programs/$mentoring_program_code/group/$secretDirectory");
            touch("mentoring_programs/$mentoring_program_code/group/index.php");
            touch("mentoring_programs/$mentoring_program_code/group/$secretDirectory/index.php");

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
                                         category_id = ?d",
                                $mentoring_program_id, $g_name, $group_description, $forum_id, $group_max, $secretDirectory, $_POST['selectcategory'])->lastInsertID;
            Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_GROUP, MENTORING_LOG_INSERT, array('uid' => '','group_title' => $g_name, 'type' => 'insert_group'));

            if (isset($_POST['tutor'])) {
                $user_tutor_id = 0;
                foreach ($_POST['tutor'] as $user_tutor_id) {
                    Database::get()->query("INSERT INTO mentoring_group_members SET group_id = ?d, user_id = ?d, is_tutor = 1, status_request = 1", $id, $user_tutor_id);
                    Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_GROUP, MENTORING_LOG_INSERT, array('uid' => display_user($user_tutor_id,false,false),'group_title' => $group_title, 'type' => 'insert_tutor_group'));
                }
            }
            if (isset($_POST['ingroup'])) {
                $new_group_members = count($_POST['ingroup']);
                for ($i = 0; $i < $new_group_members; $i++) {
                   Database::get()->query("INSERT INTO mentoring_group_members (user_id, group_id, status_request)
                                          VALUES (?d, ?d, ?d)", $_POST['ingroup'][$i], $id, 1);
                   Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_GROUP, MENTORING_LOG_INSERT, array('uid' => display_user($_POST['ingroup'][$i],false,false),'group_title' => $group_title, 'type' => 'insert_mentee_group'));
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
                                                                documents = ?d,
                                                                announcements = ?d,
                                                                wall = ?d,
                                                                agenda = 0", $query_vars);


        }
        
        Session::flash('message',$langGroupsAdded2);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/mentoring/programs/group/index.php");

    } else {
        Session::flash('message',$langNoGroupMentoring);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page($_SERVER['SCRIPT_NAME']);
    }
}



$data['action_bar'] = action_bar([
    [ 'title' => trans('langBackPage'),
        'url' => $urlServer.'modules/mentoring/programs/group/index.php',
        'icon' => 'fa-chevron-left',
        'level' => 'primary-label',
        'button-class' => 'backButtonMentoring' ]
    ], false);


view('modules.mentoring.programs.group.create_group', $data);


