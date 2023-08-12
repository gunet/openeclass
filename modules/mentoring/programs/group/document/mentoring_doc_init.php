<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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
 * @file mentoring_doc_init.php
 * @brief initialize various subsystems for subsystem document
 */

function mentoring_doc_init() {
    global $urlAppend,$urlServer, $mentoring_program_id, $mentoring_program_code, $webDir, $can_upload_mentoring, $group_name, 
        $is_admin, $navigation, $subsystem, $subsystem_id, $secret_directory,
        $program_group_id, $groupset, $base_url, $group_name, $upload_target_url, $group_sql, $is_member,
        $group_hidden_input, $basedir, $uid, $session, $pageName, $is_editor_mentoring_program, $is_editor_mentoring_group, $is_editor_wall;

        //after login timeout then recconect and get the last values from session table
        if(!isset($mentoring_platform) and !$mentoring_platform){
            after_reconnect_go_to_mentoring_homepage();
        }
        if(isset($_GET['group_id']) and intval(getDirectReference($_GET['group_id'])) == 0 and !isset($_SESSION['mentoring_group_id'])
           and defined('MENTORING_GROUP_DOCUMENTS')){
            after_reconnect_go_to_mentoring_homepage();
        }
        if(isset($_GET['comment']) and (defined('MENTORING_COMMON_DOCUMENTS') or defined('MENTORING_MYDOCS'))){
            if(empty(getDirectReference($_GET['comment']))){
                after_reconnect_go_to_mentoring_homepage();
            }
        }
        if(isset($_GET['move']) and (defined('MENTORING_COMMON_DOCUMENTS') or defined('MENTORING_MYDOCS'))){
            if(empty(getDirectReference($_GET['move']))){
                after_reconnect_go_to_mentoring_homepage();
            }
        }
        if(isset($_GET['rename']) and (defined('MENTORING_COMMON_DOCUMENTS') or defined('MENTORING_MYDOCS'))){
            if(empty(getDirectReference($_GET['rename']))){
                after_reconnect_go_to_mentoring_homepage();
            }
        }
        if(isset($_GET['replace']) and (defined('MENTORING_COMMON_DOCUMENTS') or defined('MENTORING_MYDOCS'))){
            if(empty(getDirectReference($_GET['replace']))){
                after_reconnect_go_to_mentoring_homepage();
            }
        }

        if(defined('MENTORING_MYDOCS') or isset($_GET['editPathMydoc'])){
            
            $subsystem = MYDOCS;
            $subsystem_id = $uid;
            $groupset = '';
            $upload_target_url = 'mydoc.php?mydocs=true';
            $group_id = '';
            $group_sql = "mentoring_program_id = -1 AND subsystem = $subsystem AND subsystem_id = $uid";
            $group_hidden_input = '';
            $basedir = $webDir . '/mentoring_programs/mydocs/' . $uid;
            if (!is_dir($basedir)) {
                make_dir($basedir);
            }
            $pageName = trans('langMyDocs');
            define('SAVED_MENTORING_CODE', $mentoring_program_code);
            define('SAVED_MENTORING_ID', $mentoring_program_id);
            $base_url = $_SERVER['SCRIPT_NAME'] . '?program=' . SAVED_MENTORING_CODE . '&amp;';
            $mentoring_program_id = -1;
            $mentoring_program_code = '';
            $can_upload_mentoring = $session->user_id == $uid;
        }else if(defined('MENTORING_COMMON_DOCUMENTS') or isset($_GET['editPathCommon'])) {
            $subsystem = MENTORING_COMMON;
            $subsystem_id = 'NULL';
            $groupset = '';
            $base_url = $_SERVER['SCRIPT_NAME'] . '?';
            $upload_target_url = 'mydoc.php?common_docs=true';
            $group_id = '';
            $group_sql = "mentoring_program_id = -1 AND subsystem = $subsystem";
            $group_hidden_input = '';
            $basedir = $webDir . '/mentoring_programs/commondocs';
            if (!is_dir($basedir)) {
                make_dir($basedir);
            }
            $pageName = trans('langCommonDocs').' '.trans('langMentoringPlatforms');
            $navigation[] = array('url' => $urlAppend . 'modules/admin/index.php', 'name' => trans('langAdmin'));
            define('SAVED_MENTORING_CODE', $mentoring_program_code);
            define('SAVED_MENTORING_ID', $mentoring_program_id);
            $base_url = $_SERVER['SCRIPT_NAME'] . '?common_program=' . SAVED_MENTORING_CODE . '&amp;';
            $mentoring_program_id = -1;
            $mentoring_program_code = '';
            $can_upload_mentoring = $session->user_id == $uid;
        }else{

            // $is_editor_mentoring_group
            $is_editor_mentoring_group = false;
            if(isset($_SESSION['mentoring_group_id'])){
                $program_group_id = $_SESSION['mentoring_group_id'];
                // an uid einai ypeuthonos omadas
                $check_ = Database::get()->queryArray("SELECT *FROM mentoring_group_members
                                                        WHERE group_id = ?d
                                                        AND user_id = ?d
                                                        AND is_tutor = ?d
                                                        AND status_request = ?d",$program_group_id,$uid,1,1);
            
                if(count($check_) > 0 or $is_editor_mentoring_program or $is_admin){
                    $is_editor_mentoring_group = true;
                }
            }

            $can_upload_mentoring = $is_editor_mentoring_program || $is_editor_mentoring_group || $is_admin;

            require_once 'modules/mentoring/functions.php';

            $subsystem = MENTORING_GROUP;
    
            mentoring_initialize_group_id();
            
            mentoring_initialize_group_info($program_group_id);
            
            // $isCommonProgramGroup = Database::get()->querySingle("SELECT common FROM mentoring_group WHERE id = ?d",$program_group_id)->common;
            // if($isCommonProgramGroup == 0){
            //     if (!$uid or !($is_member or $can_upload_mentoring)) {
            //         forbidden();
            //     }
            // }
    
            $subsystem_id = $program_group_id;
            $groupset = "group_id=".getInDirectReference($program_group_id);
            $base_url = $_SERVER['SCRIPT_NAME'] . '?' . $groupset . '&amp;';
            $upload_target_url = 'mydoc.php?group_id='.getInDirectReference($program_group_id);
            $group_sql = "mentoring_program_id = $mentoring_program_id AND subsystem = $subsystem AND subsystem_id = $subsystem_id";
            $group_hidden_input = "<input type='hidden' name='group_id' value='$program_group_id' />";
            $basedir = $webDir . '/mentoring_programs/' . $mentoring_program_code . '/group/' . $secret_directory;    
            $can_upload_mentoring = $can_upload_mentoring || $is_member || $is_admin;
            $pageName = trans('langGroupDocumentsLink');
            $navigation[] = array('url' => $urlAppend . 'modules/mentoring/programs/group/index.php', 'name' => trans('langGroups'));
            $navigation[] = array('url' => $urlAppend . 'modules/mentoring/programs/group/group_space.php?space_group_id=' . getInDirectReference($program_group_id), 'name' => q($group_name));
            
        }

        // is editor wall can add a post on wall. So is editor a wall is a member of group
        $is_editor_wall = $is_editor_mentoring_program || $is_editor_mentoring_group || $is_admin;

}



function mentoring_initialize_group_id($param = 'group_id') {
    global $program_group_id, $urlServer, $mentoring_program_code, $mentoring_program_id;

    if (!isset($program_group_id)) {
        if (isset($_REQUEST[$param])) {
            $program_group_id = getDirectReference($_REQUEST[$param]);
        } else {
            if(isset($mentoring_program_id) && $mentoring_program_id){// wall for common group
                $program_group_id = Database::get()->querySingle("SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d AND common = ?d",$mentoring_program_id,1)->id;
            }else{
                header("Location: {$urlServer}modules/mentoring/mentoring_platform_home.php");
                exit;
            }
        }
    }
}
