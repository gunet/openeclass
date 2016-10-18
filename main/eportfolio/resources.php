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

$require_login = false;
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'modules/group/group_functions.php';

if (!get_config('eportfolio_enable')) {
    $tool_content = "<div class='alert alert-danger'>$langePortfolioDisabled</div>";
    draw($tool_content, 1);
    exit;
}

$userdata = array();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $toolName = $langUserePortfolio;
} else {
    $id = $uid;
    $toolName = $langMyePortfolio;
}

$userdata = Database::get()->querySingle("SELECT surname, givenname, eportfolio_enable
                                          FROM user WHERE id = ?d", $id);

if ($userdata) {
    if ($uid == $id) {
        
        if ($userdata->eportfolio_enable == 0) {
            $tool_content .= "<div class='alert alert-warning'>$langePortfolioDisableWarning</div>";
        }
        
        $tool_content .= action_bar(array(
                array('title' => $langBio,
                      'url' => "{$urlAppend}courses/eportfolio/userbios/$id"."_bio.pdf",
                      'icon' => 'fa-download',
                      'level' => 'primary-label',
                      'show' => file_exists("$webDir/courses/eportfolio/userbios/$id"."_bio.pdf")),
                array('title' => $langResume,
                      'url' => "index.php?id=$id",
                      'level' => 'primary-label'),
                array('title' => $langResourcesCollection,
                      'url' => "resources.php?id=$id",
                      'level' => 'primary-label',
                      'button-class' => 'btn-info'),
                array('title' => $langEditResume,
                      'url' => "edit_eportfolio.php",
                      'icon' => 'fa-edit'),
                array('title' => $langUploadBio,
                      'url' => "bio_upload.php",
                      'icon' => 'fa-upload')
            ));
        
        if (isset($_GET['action']) && $_GET['action'] == 'add') {
            if (isset($_GET['type']) && isset($_GET['rid'])) {
                $rtype = $_GET['type'];
                $rid = $_GET['rid'];
                
                if ($rtype == 'blog') {
                    $post = Database::get()->querySingle("SELECT * FROM blog_post WHERE id = ?d", $rid);
                    if ($post) {
                        if ($post->user_id == $uid){
                            if ($post->course_id == 0) { //personal blog post
                                $course_title = '';
                            } else {
                                $course_title = Database::get()->querySingle("SELECT title FROM course WHERE id = ?d", $post->course_id)->title;
                            }
                        }
                        $data = array($post->title,$post->content,$post->time);
                        
                        Database::get()->query("INSERT INTO eportfolio_resource (user_id,resource_id,resource_type,course_id,course_title,data)
                                VALUES (?d,?d,?s,?d,?s,?s)", $uid,$rid,'blog',$post->course_id,$course_title,serialize($data));
                        Session::Messages($langePortfolioResourceAdded, 'alert-success');
                        redirect_to_home_page("main/eportfolio/resources.php");
                    }
                } elseif ($rtype == 'work_submission') {
                    $submission = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE id = ?d", $rid);
                    if($submission) {
                        $work = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $submission->assignment_id);
                        if ( ($submission->group_id == 0 && $submission->uid == $uid) ||
                             ($submission->group_id != 0 && array_key_exists($submission->group_id, user_group_info($uid, $work->course_id))) ) {
                            
                            $course_info = Database::get()->querySingle("SELECT title,code FROM course WHERE id = ?d", $work->course_id);
                            $course_title = $course_info->title;
                            $course_code =  $course_info->code;
                            
                            $data = array($work->title, $work->description, $submission->submission_date, $work->max_grade, $submission->submission_text, $submission->grade, $submission->group_id);
                            
                            //create dir for user
                            if (!file_exists($webDir."/courses/eportfolio/work_submissions/".$uid)) {
                                @mkdir($webDir."/courses/eportfolio/work_submissions/".$uid, 0777);
                            }
                            
                            //assignment file
                            if (!empty($work->file_path)) {
                                $ass_file_path_explode = explode("/", $work->file_path);
                                $ass_file_extension = pathinfo($webDir.'courses/'.$course_code.'/work/'.$ass_file_path_explode[0].'/'.rawurlencode($ass_file_path_explode[1]), PATHINFO_EXTENSION);
                                $ass_source = $urlServer.'courses/'.$course_code.'/work/'.$ass_file_path_explode[0].'/'.rawurlencode($ass_file_path_explode[1]);
                                $ass_dest = 'courses/eportfolio/work_submissions/'.$uid.'/'.uniqid().'.'.$ass_file_extension;
                                copy($ass_source,$ass_dest);
                                $data[] = $ass_dest;
                            } else {
                                $data[] = $work->file_path;
                            }
                            
                            //submission file
                            if (!empty($submission->file_path)) {
                                $subm_file_path_explode = explode("/", $submission->file_path);
                                $subm_file_extension = pathinfo($webDir.'courses/'.$course_code.'/work/'.$subm_file_path_explode[0].'/'.rawurlencode($subm_file_path_explode[1]), PATHINFO_EXTENSION);
                                $subm_source = $urlServer.'courses/'.$course_code.'/work/'.$subm_file_path_explode[0].'/'.rawurlencode($subm_file_path_explode[1]);
                                $subm_dest = 'courses/eportfolio/work_submissions/'.$uid.'/'.uniqid().'.'.$subm_file_extension;
                                copy($subm_source,$subm_dest);
                            } else {
                                $data[] = $submission->file_path;
                            }
                            
                            Database::get()->query("INSERT INTO eportfolio_resource (user_id,resource_id,resource_type,course_id,course_title,data)
                                VALUES (?d,?d,?s,?d,?s,?s)", $uid,$rid,'work_submission',$work->course_id,$course_title,serialize($data));
                            Session::Messages($langePortfolioResourceAdded, 'alert-success');
                            redirect_to_home_page("main/eportfolio/resources.php");
                            
                        }
                    }
                }
            }
        } elseif (isset($_GET['action']) && $_GET['action'] == 'remove') {
            if (isset($_GET['type']) && isset($_GET['rid'])) {
                //TODO delete files if existing when deleting work submissions
                $rtype = $_GET['type'];
                $rid = $_GET['rid'];
                Database::get()->query("DELETE FROM eportfolio_resource WHERE user_id = ?d AND resource_id = ?d AND resource_type = ?d", $uid, $rid, $rtype);
                Session::Messages($langePortfolioResourceRemoved, 'alert-success');
                redirect_to_home_page("main/eportfolio/resources.php");
            }
        }
        
    } else {
        if ($userdata->eportfolio_enable == 0) {
            $tool_content = "<div class='alert alert-danger'>$langUserePortfolioDisabled</div>";
            draw($tool_content, 1);
            exit;
        }
        
        $tool_content .= action_bar(array(
                array('title' => $langBio,
                      'url' => "{$urlAppend}courses/eportfolio/userbios/$id"."_bio.pdf",
                      'icon' => 'fa-download',
                      'level' => 'primary-label',
                      'show' => file_exists("$webDir/courses/eportfolio/userbios/$id"."_bio.pdf")),
                array('title' => $langResume,
                      'url' => "index.php?id=$id",
                      'level' => 'primary-label'),
                array('title' => $langResourcesCollection,
                      'url' => "resources.php?id=$id",
                      'level' => 'primary-label',
                      'button-class' => 'btn-info'),
            ));
    }
    
    $tool_content .= '<ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#blog">'.$langBlogPosts.'</a></li>
                        <li><a data-toggle="tab" href="#works">'.$langWorks.'</a></li>
                      </ul>';
    $tool_content .= '<div class="tab-content">
                        <div id="blog" class="tab-pane fade in active" style="padding-top:20px">';
    
    //show blog posts collection
    $blog_posts = Database::get()->queryArray("SELECT * FROM eportfolio_resource WHERE user_id = ?d AND resource_type = ?s", $id, 'blog');
    if ($blog_posts) {
        usort($blog_posts, "cmp");
        $tool_content .= "<div class='row'>";
        $tool_content .= "<div class='col-sm-12'>";
        foreach ($blog_posts as $post) {
            $data = unserialize($post->data);
            if (!empty($post->course_title)) {
                $post->course_title = $langCourse.': '.$post->course_title;
            } else {
                $post->course_title = $langUserBlog;
            }
            $tool_content .= "<div class='panel panel-action-btn-default'>
                                <div class='panel-heading'>
                                    <div class='pull-right'>
                                        ". action_button(array(
                                                array(
                                                    'title' => $langePortfolioRemoveResource,
                                                    'url' => "$_SERVER[SCRIPT_NAME]?action=remove&amp;type=blog&amp;rid=".$post->resource_id,
                                                    'icon' => 'fa-times',
                                                    'class' => 'delete',
                                                    'confirm' => $langePortfolioSureToRemoveResource,
                                                    'show' => ($post->user_id == $uid)
                                                )))."
                                     </div>
                                        <h3 class='panel-title'>".q($data[0])."</h3>
                                </div>
                                <div class='panel-body'>
                                    <div class='label label-success'>" . nice_format($data[2], true). "</div><small>".$langBlogPostUser.display_user($post->user_id, false, false)."</small><br><br>".standard_text_escape($data[1])."
                                                </div>
                                                <div class='panel-footer'>
                                                <div class='row'>
                                                <div class='col-sm-6'>$post->course_title</div>
                                                </div>
                                                </div>
                                                </div>";
        }
        $tool_content .= "</div></div>";
    } else {
        $tool_content .= '<div class="alert alert-warning">'.$langePortfolioResourceNoBlog.'</div>';
    }
    
    $tool_content .= '</div>';
    
    
    $tool_content .= '<div id="works" class="tab-pane fade" style="padding-top:20px">';
    
    //show assignment submissions collection
    $submissions = Database::get()->queryArray("SELECT * FROM eportfolio_resource WHERE user_id = ?d AND resource_type = ?s", $id, 'work_submission');
    if ($submissions) {
        usort($submissions, "cmp");
        $tool_content .= "<div class='row'>";
        $tool_content .= "<div class='col-sm-12'>";
        foreach ($submissions as $submission) {
            $data = unserialize($submission->data);
            $submission->course_title = $langCourse.': '.$submission->course_title;
            $tool_content .= "<div class='panel panel-action-btn-default'>
                                <div class='panel-heading'>
                                    <div class='pull-right'>
                                        ". action_button(array(
                                                    array(
                                                            'title' => $langePortfolioRemoveResource,
                                                            'url' => "$_SERVER[SCRIPT_NAME]?action=remove&amp;type=work_submission&amp;rid=".$submission->resource_id,
                                                            'icon' => 'fa-times',
                                                            'class' => 'delete',
                                                            'confirm' => $langePortfolioSureToRemoveResource,
                                                            'show' => ($submission->user_id == $uid)
                                                    )))."
                                     </div>
                                        <h3 class='panel-title'>".$langTitle.": ".q($data[0])."</h3>
                                </div>
                                <div class='panel-body'>
                                    <div class='label label-success'>$langSubmit: " . nice_format($data[2], true). "</div><small>".$langBlogPostUser.display_user($post->user_id, false, false)."</small><br><br>
                                        </div>
                                        <div class='panel-footer'>
                                        <div class='row'>
                                        <div class='col-sm-6'>$submission->course_title</div>
                                        </div>
                                        </div>
                                        </div>";
        }
        $tool_content .= "</div></div>";
    } else {
        $tool_content .= '<div class="alert alert-warning">'.$langePortfolioResourceNoWork.'</div>';
    }
    
    
    $tool_content .= '</div>';
    
    $tool_content .= '</div>';
}

if ($uid == $id) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 2);
}

function cmp($obj1, $obj2)
{   
    $data1 = unserialize($obj1->data);
    $data1 = strtotime($data1[2]);
    $data2 = unserialize($obj2->data);
    $data2 = strtotime($data2[2]);
    
    if ($data1 < $data2)
        return true;
    else 
        return false;
}