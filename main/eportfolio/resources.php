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
require_once 'include/lib/forcedownload.php';
require_once 'modules/group/group_functions.php';

if (!get_config('eportfolio_enable')) {
    $tool_content = "<div class='alert alert-danger'>$langePortfolioDisabled</div>";
    draw($tool_content, 1);
    exit;
}

$userdata = array();

if (isset($_GET['id']) && intval($_GET['id']) != 0) {
    $id = intval($_GET['id']);
    $toolName = $langUserePortfolio;
} else {
    if ($uid == 0) {
        redirect_to_home_page();
        exit;
    } else {
        $id = $uid;
        $toolName = $langMyePortfolio;
    }
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
                      'url' => "{$urlAppend}main/eportfolio/index.php?action=get_bio&amp;id=$id",
                      'icon' => 'fa-download',
                      'level' => 'primary-label',
                      'show' => file_exists("$webDir/courses/eportfolio/userbios/$id/bio.pdf")),
                array('title' => $langResume,
                      'url' => "{$urlAppend}main/eportfolio/index.php?id=$id",
                      'level' => 'primary-label'),
                array('title' => $langResourcesCollection,
                      'url' => "{$urlAppend}main/eportfolio/resources.php?id=$id",
                      'level' => 'primary-label',
                      'button-class' => 'btn-info'),
                array('title' => $langEditResume,
                      'url' => "{$urlAppend}main/eportfolio/edit_eportfolio.php",
                      'icon' => 'fa-edit'),
                array('title' => $langUploadBio,
                      'url' => "{$urlAppend}main/eportfolio/bio_upload.php",
                      'icon' => 'fa-upload')
            ));
        
        if (isset($_GET['action']) && $_GET['action'] == 'add') {
            if (isset($_GET['type']) && isset($_GET['rid'])) {
                $rtype = $_GET['type'];
                $rid = intval($_GET['rid']);
                
                if ($rtype == 'blog') {
                    $post = Database::get()->querySingle("SELECT * FROM blog_post WHERE id = ?d AND user_id = ?d", $rid, $uid);
                    if ($post) {
                        if ($post->user_id == $uid){
                            if ($post->course_id == 0) { //personal blog post
                                $course_title = '';
                            } else {
                                $course_title = Database::get()->querySingle("SELECT title FROM course WHERE id = ?d", $post->course_id)->title;
                            }
                        }
                        $data = array('title' => $post->title, 'content' => $post->content, 'timestamp' => $post->time);
                        
                        Database::get()->query("INSERT INTO eportfolio_resource (user_id,resource_id,resource_type,course_id,course_title,data)
                                VALUES (?d,?d,?s,?d,?s,?s)", $uid,$rid,'blog',$post->course_id,$course_title,serialize($data));
                        Session::Messages($langePortfolioResourceAdded, 'alert-success');
                        redirect_to_home_page("main/eportfolio/resources.php");
                    }
                } elseif ($rtype == 'work_submission') {
                    $submission = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE id = ?d AND uid = ?d", $rid, $uid);
                    if($submission) {
                        $work = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $submission->assignment_id);
                        if ( ($submission->group_id == 0 && $submission->uid == $uid) ||
                             ($submission->group_id != 0 && array_key_exists($submission->group_id, user_group_info($uid, $work->course_id))) ) {
                            
                            $course_info = Database::get()->querySingle("SELECT title,code FROM course WHERE id = ?d", $work->course_id);
                            $course_title = $course_info->title;
                            $course_code =  $course_info->code;
                            
                            $data = array('title' => $work->title, 'descr' => $work->description, 'subm_date' => $submission->submission_date, 
                                          'max_grade' => $work->max_grade, 'subm_text' => $submission->submission_text, 'grade' => $submission->grade, 
                                          'group_id' => $submission->group_id);
                            
                            //create dir for user
                            if (!file_exists($webDir."/courses/eportfolio/work_submissions/".$uid)) {
                                @mkdir($webDir."/courses/eportfolio/work_submissions/".$uid, 0777);
                            }
                            
                            //assignment file
                            if (!empty($work->file_path)) {
                                $ass_file_path_explode = explode("/", $work->file_path);
                                $ass_file_extension = pathinfo($webDir.'courses/'.$course_code.'/work/'.$ass_file_path_explode[0].'/'.rawurlencode($ass_file_path_explode[1]), PATHINFO_EXTENSION);
                                $ass_source = $urlServer.'courses/'.$course_code.'/work/admin_files/'.$ass_file_path_explode[0].'/'.rawurlencode($ass_file_path_explode[1]);
                                $ass_dest = 'courses/eportfolio/work_submissions/'.$uid.'/'.uniqid().'.'.$ass_file_extension;
                                copy($ass_source,$ass_dest);
                                $data['assignment_file'] = $ass_dest;
                            } else {
                                $data['assignment_file'] = $work->file_path;
                            }
                            
                            //submission file
                            if (!empty($submission->file_path)) {
                                $subm_file_path_explode = explode("/", $submission->file_path);
                                $subm_file_extension = pathinfo($webDir.'courses/'.$course_code.'/work/'.$subm_file_path_explode[0].'/'.rawurlencode($subm_file_path_explode[1]), PATHINFO_EXTENSION);
                                $subm_source = $urlServer.'courses/'.$course_code.'/work/'.$subm_file_path_explode[0].'/'.rawurlencode($subm_file_path_explode[1]);
                                $subm_dest = 'courses/eportfolio/work_submissions/'.$uid.'/'.uniqid().'.'.$subm_file_extension;
                                copy($subm_source,$subm_dest);
                                $data['submission_file'] = $subm_dest;
                            } else {
                                $data['submission_file'] = $submission->file_path;
                            }
                            
                            Database::get()->query("INSERT INTO eportfolio_resource (user_id,resource_id,resource_type,course_id,course_title,data)
                                VALUES (?d,?d,?s,?d,?s,?s)", $uid,$rid,'work_submission',$work->course_id,$course_title,serialize($data));
                            Session::Messages($langePortfolioResourceAdded, 'alert-success');
                            redirect_to_home_page("main/eportfolio/resources.php");
                            
                        }
                    }
                } elseif ($rtype == 'mydocs') {
                    $document = Database::get()->querySingle("SELECT * FROM document WHERE id = ?d AND subsystem = ?d AND subsystem_id = ?d AND format <> ?s", $rid, MYDOCS, $uid, '.dir');
                    
                    if ($document) {
                        $data = array('title' => $document->title, 'filename' => $document->filename, 'comment' => $document->comment, 
                                      'subject' => $document->subject, 'description' => $document->description);
                        
                        //create dir for user
                        if (!file_exists($webDir."/courses/eportfolio/mydocs/".$uid)) {
                            @mkdir($webDir."/courses/eportfolio/mydocs/".$uid, 0777);
                        }
                        
                        $file_source = $urlServer.'courses/mydocs/'.$uid.$document->path;
                        $path_extension = pathinfo($file_source, PATHINFO_EXTENSION);
                        $file_dest = 'courses/eportfolio/mydocs/'.$uid.'/'.uniqid().'.'.$path_extension;
                        copy($file_source,$file_dest);
                        $data['file_path'] = $file_dest;
                        
                        Database::get()->query("INSERT INTO eportfolio_resource (user_id,resource_id,resource_type,course_id,course_title,data)
                                VALUES (?d,?d,?s,?d,?s,?s)", $uid, $rid, 'mydocs', 0 ,'', serialize($data));
                    }
                    
                    Session::Messages($langePortfolioResourceAdded, 'alert-success');
                    redirect_to_home_page("main/eportfolio/resources.php");
                }
            }
        } elseif (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['er_id'])) {
            $er_id = intval($_GET['er_id']);
            $info = Database::get()->querySingle("SELECT resource_type, data FROM eportfolio_resource WHERE user_id = ?d AND id = ?d", $uid, $er_id);
            if ($info) {
                if ($info->resource_type == 'work_submission') {
                    $data_array = unserialize($info->data);
                    @unlink($webDir.'/'.$data_array["assignment_file"]);
                    @unlink($webDir.'/'.$data_array["submission_file"]);
                }
                Database::get()->query("DELETE FROM eportfolio_resource WHERE id = ?d", $er_id);
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
                      'url' => "{$urlAppend}main/eportfolio/index.php?action=get_bio&amp;id=$id",
                      'icon' => 'fa-download',
                      'level' => 'primary-label',
                      'show' => file_exists("$webDir/courses/eportfolio/userbios/$id/bio.pdf")),
                array('title' => $langResume,
                      'url' => "{$urlAppend}main/eportfolio/index.php?id=$id",
                      'level' => 'primary-label'),
                array('title' => $langResourcesCollection,
                      'url' => "{$urlAppend}main/eportfolio/resources.php?id=$id",
                      'level' => 'primary-label',
                      'button-class' => 'btn-info'),
            ));
    }
    
    if (isset($_GET['action']) && $_GET['action'] == 'get') {
        if (isset($_GET['type']) && isset($_GET['er_id'])) {
            $info = Database::get()->querySingle("SELECT data FROM eportfolio_resource WHERE user_id = ?d
                                AND resource_type = ?d AND id = ?d", $id, 'work_submission', intval($_GET['er_id']));
            if ($info) {
                $data_array = unserialize($info->data);
                if ($_GET['type'] == 'assignment') {
                    $file_info = $data_array['assignment_file'];
                } else if ($_GET['type'] == 'submission') {
                    $file_info = $data_array['submission_file'];
                }
                $file = str_replace('\\', '/', $webDir)."/".$file_info;
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                send_file_to_client($file, 'file.'.$extension, null, true);
            }
        }
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
                                                    'url' => "$_SERVER[SCRIPT_NAME]?action=remove&amp;type=blog&amp;er_id=".$post->id,
                                                    'icon' => 'fa-times',
                                                    'class' => 'delete',
                                                    'confirm' => $langePortfolioSureToRemoveResource,
                                                    'show' => ($post->user_id == $uid)
                                                )))."
                                     </div>
                                        <h3 class='panel-title'>".q($data['title'])."</h3>
                                </div>
                                <div class='panel-body'>
                                    <div class='label label-success'>" . nice_format($data['timestamp'], true). "</div><small>".$langBlogPostUser.display_user($post->user_id, false, false)."</small><br><br>".standard_text_escape($data['content'])."
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
            if (is_null($data['grade'])) {
                $data['grade'] = '-';
            }
            if ($data['group_id'] == 0) {
                $assignment_type = $m['user_work'];
            } else {
                $assignment_type = $m['group_work'];
            }
            $submission_header_content = "<div><h3 class='panel-title'>".$langTitle.": ".q($data['title'])."</h3></div>";
            $submission->course_title = $langCourse.': '.$submission->course_title;
            $submission_content = "<div><button type='button' class='btn btn-primary btn-xs' data-toggle='collapse' data-target='#header_more_$submission->id'>$langMore</button></div>
                                   <div id='header_more_$submission->id' class='collapse'>";
            if (!empty($data['descr'])) {
                $submission_content .= "<div><b>".$langDescription."</b>:</div><div>".$data['descr']."</div>";
            }
            $submission_content .= "<div><a href='resources.php?action=get&amp;id=$id&amp;type=assignment&er_id=$submission->id'>$langWorkFile</a></div>";
            $submission_content .= "</div>";
            $submission_content .= "<div><b>$langSubmit</b>: " . nice_format($data['subm_date'], true). "</div>
                                   <div><b>".$m['grade']."</b>: ".$data['grade']." / ".$data['max_grade']."</div>
                                   <div><b>".$m['group_or_user']."</b>: ".$assignment_type."</div>";
            if (!is_null($data['subm_text'])) {
                $submission_content .= "<div><b>$langWorkOnlineText</b>: <br>".$data['subm_text']."</div>";
            } else {
               $submission_content .= "<div><a href='resources.php?action=get&amp;id=$id&amp;type=submission&er_id=$submission->id'>$langWorkFile</a></div>"; 
            }
            $submission_footer = "<div class='panel-footer'>
                                      <div class='row'>
                                          <div class='col-sm-6'>$submission->course_title</div>
                                      </div>
                                  </div>";
            $tool_content .= "<div class='panel panel-action-btn-default'>
                                <div class='panel-heading'>
                                    <div class='pull-right'>
                                        ". action_button(array(
                                                    array(
                                                            'title' => $langePortfolioRemoveResource,
                                                            'url' => "$_SERVER[SCRIPT_NAME]?action=remove&amp;type=work_submission&amp;er_id=".$submission->id,
                                                            'icon' => 'fa-times',
                                                            'class' => 'delete',
                                                            'confirm' => $langePortfolioSureToRemoveResource,
                                                            'show' => ($submission->user_id == $uid)
                                                    )))."
                                     </div>
                                        $submission_header_content
                                </div>
                                <div class='panel-body'>
                                $submission_content    
                                </div>
                                $submission_footer
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
    draw($tool_content, 0);
}

function cmp($obj1, $obj2)
{   
    $data1 = unserialize($obj1->data);
    if (array_key_exists('subm_date', $data1)) {
        $key = 'subm_date';
    } elseif (array_key_exists('timestamp', $data1)) {
        $key = 'timestamp';
    }
    $data1 = strtotime($data1[$key]);
    $data2 = unserialize($obj2->data);
    $data2 = strtotime($data2[$key]);
    
    if ($data1 < $data2)
        return true;
    else 
        return false;
}