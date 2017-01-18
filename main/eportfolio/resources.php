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
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'modules/group/group_functions.php';
require_once 'modules/sharing/sharing.php';

if (!get_config('eportfolio_enable')) {
    $tool_content = "<div class='alert alert-danger'>$langePortfolioDisabled</div>";
    if ($session->status == 0) {
        draw($tool_content, 0);
    } else {
        draw($tool_content, 1);
    }
    exit;
}

if (isset($_GET['id']) && intval($_GET['id']) > 0) {
    $id = intval($_GET['id']);
    $toolName = $langUserePortfolio;
} else {
    if ($session->status == 0) {
        redirect_to_home_page();
        exit;
    } else {
        $id = $uid;
        $toolName = $langMyePortfolio;
    }
}

if (!token_validate('eportfolio' . $id, $_GET['token'])) {
    redirect_to_home_page();
}

$token = token_generate('eportfolio' . $id);

$userdata = Database::get()->querySingle("SELECT surname, givenname, eportfolio_enable
                                          FROM user WHERE id = ?d", $id);

if ($userdata) {
    if ($uid == $id) {
        
        if (isset($_GET['toggle_val'])) {
            if ($_GET['toggle_val'] == 'on') {
                Database::get()->query("UPDATE user SET eportfolio_enable = ?d WHERE id = ?d", 1, $id);
            } elseif ($_GET['toggle_val'] == 'off') {
                Database::get()->query("UPDATE user SET eportfolio_enable = ?d WHERE id = ?d", 0, $id);
            }
            redirect_to_home_page("main/eportfolio/resources.php?id=$id&token=$token");
        }
        
        if ($userdata->eportfolio_enable == 0) {
            $tool_content .= "<div class='alert alert-warning'>$langePortfolioDisableWarning</div>";
        }
        
        $tool_content .= "<div class='alert alert-info fade in'>
                            <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
                            $langePortfolioCollectionUserInfo
                          </div>";
        
        if ($userdata->eportfolio_enable == 1) {
            $tool_content .= "<script type='text/javascript'>
                                $('#copy-btn').tooltip({
                                });
                    
                                $(function() {
                                  var clipboard = new Clipboard('#copy-btn');
                    
                                  clipboard.on('success', function(e) {
                                    e.clearSelection();
                                    $(e.trigger).attr('title', '$langCopiedSucc').tooltip('fixTitle').tooltip('show');
                                  });
                    
                                  clipboard.on('error', function(e) {
                                    $(e.trigger).attr('title', '$langCopiedErr').tooltip('fixTitle').tooltip('show');
                                  });
                    
                                });
                              </script>";
        }
        
        $tool_content .= action_bar(array(
                array('title' => $langBio,
                      'url' => "{$urlAppend}main/eportfolio/index.php?action=get_bio&amp;id=$id&amp;token=$token",
                      'icon' => 'fa-download',
                      'level' => 'primary-label',
                      'show' => file_exists("$webDir/courses/eportfolio/userbios/$id/bio.pdf")),
                array('title' => $langResume,
                      'url' => "{$urlAppend}main/eportfolio/index.php?id=$id&amp;token=$token",
                      'level' => 'primary-label'),
                array('title' => $langResourcesCollection,
                      'url' => "{$urlAppend}main/eportfolio/resources.php?id=$id&amp;token=$token",
                      'level' => 'primary-label',
                      'button-class' => 'btn-primary'),
                array('title' => $userdata->eportfolio_enable ? $langViewHide : $langViewShow,
                      'url' => $userdata->eportfolio_enable ? "{$urlAppend}main/eportfolio/resources.php?id=$id&amp;token=$token&amp;toggle_val=off" : "{$urlAppend}main/eportfolio/resources.php?id=$id&amp;token=$token&amp;toggle_val=on",
                      'icon' => $userdata->eportfolio_enable ? 'fa-eye-slash' : 'fa-eye'),
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
                        if ($post->course_id > 0) {
                            $course_status = Database::get()->querySingle("SELECT visible FROM course WHERE id = ?d", $post->course_id)->visible;
                            $module_status = Database::get()->querySingle("SELECT visible FROM course_module WHERE course_id = ?d AND module_id = ?d", $post->course_id, MODULE_ID_BLOG)->visible;
                            if ($course_status != COURSE_INACTIVE AND $module_status) {
                                $course_post_proceed = TRUE;
                            } else {
                                $course_post_proceed = FALSE; 
                            }
                        }
                        
                        if ($course_post_proceed || ($post->course_id == 0 && get_config('personal_blog'))) {
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
                            redirect_to_home_page("main/eportfolio/resources.php?id=$uid&token=$token");
                        }
                    }
                    
                    Session::Messages($langGeneralError, 'alert-danger');
                    redirect_to_home_page("main/eportfolio/resources.php?id=$uid&token=$token");
                    
                } elseif ($rtype == 'work_submission') {
                    $submission = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE id = ?d AND uid = ?d", $rid, $uid);
                    if($submission) {
                        $work = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $submission->assignment_id);
                        $course_status = Database::get()->querySingle("SELECT visible FROM course WHERE id = ?d", $work->course_id)->visible;
                        $module_status = Database::get()->querySingle("SELECT visible FROM course_module WHERE course_id = ?d AND module_id = ?d", $work->course_id, MODULE_ID_ASSIGN)->visible;
                        if ($module_status AND $course_status != COURSE_INACTIVE) {
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
                                redirect_to_home_page("main/eportfolio/resources.php?id=$uid&token=$token");
                                
                            }
                        }
                    }
                    
                    Session::Messages($langGeneralError, 'alert-danger');
                    redirect_to_home_page("main/eportfolio/resources.php?id=$uid&token=$token");
                    
                } elseif ($rtype == 'mydocs') {
                    if (($session->status == USER_TEACHER && get_config('mydocs_teacher_enable')) || ($session->status == USER_STUDENT && get_config('mydocs_student_enable'))) {
                        $document = Database::get()->querySingle("SELECT * FROM document WHERE id = ?d AND subsystem = ?d AND subsystem_id = ?d AND format <> ?s", $rid, MYDOCS, $uid, '.dir');
                        
                        if ($document) {
                            $data = array('title' => $document->title, 'filename' => $document->filename, 'comment' => $document->comment, 
                                          'subject' => $document->subject, 'description' => $document->description, 'date' => $document->date, 
                                          'date_modified' => $document->date_modified, 'format' => $document->format);
                            
                            //create dir for user
                            if (!file_exists($webDir."/courses/eportfolio/mydocs/".$uid)) {
                                @mkdir($webDir."/courses/eportfolio/mydocs/".$uid, 0777);
                            }
                            
                            if ($document->extra_path) {
                                $data['extra_path'] = $document->extra_path;
                            } else {
                                $file_source = $urlServer.'courses/mydocs/'.$uid.$document->path;
                                $path_extension = pathinfo($file_source, PATHINFO_EXTENSION);
                                $file_dest = 'courses/eportfolio/mydocs/'.$uid.'/'.uniqid().'.'.$path_extension;
                                copy($file_source,$file_dest);
                                $data['file_path'] = $file_dest;
                            }
                            
                            Database::get()->query("INSERT INTO eportfolio_resource (user_id,resource_id,resource_type,course_id,course_title,data)
                                    VALUES (?d,?d,?s,?d,?s,?s)", $uid, $rid, 'mydocs', 0 ,'', serialize($data));
                            
                            Session::Messages($langePortfolioResourceAdded, 'alert-success');
                            redirect_to_home_page("main/eportfolio/resources.php?id=$uid&token=$token");
                        }
                    }
                    
                    Session::Messages($langGeneralError, 'alert-danger');
                    redirect_to_home_page("main/eportfolio/resources.php?id=$uid&token=$token");
                    
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
                } elseif ($info->resource_type == 'mydocs') {
                    $data_array = unserialize($info->data);
                    @unlink($webDir.'/'.$data_array["file_path"]);
                }
                Database::get()->query("DELETE FROM eportfolio_resource WHERE id = ?d", $er_id);
                Session::Messages($langePortfolioResourceRemoved, 'alert-success');
                redirect_to_home_page("main/eportfolio/resources.php?id=$uid&token=$token");
            } else {
                Session::Messages($langGeneralError, 'alert-danger');
                redirect_to_home_page("main/eportfolio/resources.php?id=$uid&token=$token");
            }
        }
    } else {
        if ($userdata->eportfolio_enable == 0) {
            $tool_content = "<div class='alert alert-danger'>$langUserePortfolioDisabled</div>";
            if ($session->status == 0) {
                draw($tool_content, 0);
            } else {
                draw($tool_content, 1);
            }
            exit;
        }
        
        $tool_content .= action_bar(array(
                array('title' => $langBio,
                      'url' => "{$urlAppend}main/eportfolio/index.php?action=get_bio&amp;id=$id&amp;token=$token",
                      'icon' => 'fa-download',
                      'level' => 'primary-label',
                      'show' => file_exists("$webDir/courses/eportfolio/userbios/$id/bio.pdf")),
                array('title' => $langResume,
                      'url' => "{$urlAppend}main/eportfolio/index.php?id=$id&amp;token=$token",
                      'level' => 'primary-label'),
                array('title' => $langResourcesCollection,
                      'url' => "{$urlAppend}main/eportfolio/resources.php?id=$id&amp;token=$token",
                      'level' => 'primary-label',
                      'button-class' => 'btn-primary'),
            ));
    }
    
    if (isset($_GET['action']) && $_GET['action'] == 'get') {
        if (isset($_GET['type']) && isset($_GET['er_id'])) {
            if ($_GET['type'] == 'assignment' || $_GET['type'] == 'submission') {
                $info = Database::get()->querySingle("SELECT data FROM eportfolio_resource WHERE user_id = ?d
                                    AND resource_type = ?d AND id = ?d", $id, 'work_submission', intval($_GET['er_id']));
                if ($info) {
                    $data_array = unserialize($info->data);
                    if ($_GET['type'] == 'assignment') {
                        $file_info = $data_array['assignment_file'];
                    } elseif ($_GET['type'] == 'submission') {
                        $file_info = $data_array['submission_file'];
                    }
                    if (!empty($file_info)) {
                        $file = str_replace('\\', '/', $webDir)."/".$file_info;
                        $extension = pathinfo($file, PATHINFO_EXTENSION);
                        if (file_exists($file)) {
                            send_file_to_client($file, 'file.'.$extension, null, true);
                        }
                    }
                }
            } elseif ($_GET['type'] == 'mydocs') {
                $info = Database::get()->querySingle("SELECT data FROM eportfolio_resource WHERE user_id = ?d
                                    AND resource_type = ?d AND id = ?d", $id, 'mydocs', intval($_GET['er_id']));
                
                if ($info) {
                    $data_array = unserialize($info->data);
                    $file = str_replace('\\', '/', $webDir)."/".$data_array['file_path'];
                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                    send_file_to_client($file, 'file.'.$extension, null, true);
                }
            }
        }
    }
    
    if (isset($_GET['action']) && $_GET['action'] == 'showBlogPost' && isset($_GET['er_id'])) {
        $post = Database::get()->querySingle("SELECT * FROM eportfolio_resource WHERE user_id = ?d AND resource_type = ?s AND id = ?d", $id, 'blog', intval($_GET['er_id']));
        if ($post) {
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
                                                                        'url' => "$_SERVER[SCRIPT_NAME]?token=$token&amp;action=remove&amp;type=blog&amp;er_id=".$post->id,
                                                                        'icon' => 'fa-times',
                                                                        'class' => 'delete',
                                                                        'confirm' => $langePortfolioSureToRemoveResource,
                                                                        'show' => ($post->user_id == $uid)
                                                                )))."
                                         </div>
                                            <h3 class='panel-title'>".q($data['title'])."</h3>
                                    </div>
                                    <div class='panel-body'>
                                        <div class='label label-success'>" . nice_format($data['timestamp'], true). "</div><br><br>".standard_text_escape($data['content'])."
                                    </div>
                                    <div class='panel-footer'>
                                        <div class='row'>
                                            <div class='col-sm-6'>$post->course_title</div>
                                        </div>
                                    </div>
                                </div>";
            }
            
            if ($session->status == 0) {
                draw($tool_content, 0);
            } else {
                draw($tool_content, 1);
            }
            exit;
            
        }
    
    $blog_posts = Database::get()->queryArray("SELECT * FROM eportfolio_resource WHERE user_id = ?d AND resource_type = ?s ORDER BY time_added DESC", $id, 'blog');
    $submissions = Database::get()->queryArray("SELECT * FROM eportfolio_resource WHERE user_id = ?d AND resource_type = ?s ORDER BY time_added DESC", $id, 'work_submission');
    $docs = Database::get()->queryArray("SELECT * FROM eportfolio_resource WHERE user_id = ?d AND resource_type = ?s ORDER BY time_added DESC", $id, 'mydocs');
    
    //hide tabs when there are no resources
    if (!$blog_posts && !$submissions && !$docs) {
        $tool_content .= "<div class='alert alert-warning'>$langePortfolioNoResInCollection</div>";
    } else {
        
        $active_class = ' class="active"';
        
        if ($blog_posts) {
            $blog_li = '<li'.$active_class.'><a data-toggle="tab" href="#blog">'.$langBlogPosts.'</a></li>';
            if ($active_class != '') {
                $blog_div_class = 'tab-pane fade in active';
            } else {
                $blog_div_class = 'tab-pane fade';
            }
            $active_class = '';
        } else {
            $blog_li = '';
        }
        
        if ($submissions) {
            $work_li = '<li'.$active_class.'><a data-toggle="tab" href="#works">'.$langWorks.'</a></li>';
            if ($active_class != '') {
                $work_div_class = 'tab-pane fade in active';
            } else {
                $work_div_class = 'tab-pane fade';
            }
            $active_class = '';
        } else {
            $work_li = '';
        }
        
        if ($docs) {
            $mydocs_li = '<li'.$active_class.'><a data-toggle="tab" href="#mydocs">'.$langDoc.'</a></li>';
            if ($active_class != '') {
                $mydocs_div_class = 'tab-pane fade in active';
            } else {
                $mydocs_div_class = 'tab-pane fade';
            }
            $active_class = '';
        } else {
            $mydocs_li = '';    
        }
        
        $tool_content .= '<ul class="nav nav-tabs">
                            '.$blog_li.'
                            '.$work_li.'
                            '.$mydocs_li.'
                          </ul>';
        $tool_content .= '<div class="tab-content">';

        //show blog_posts    
        if ($blog_posts) {
            $tool_content .= '<div id="blog" class="'.$blog_div_class.'" style="padding-top:20px">';
            //usort($blog_posts, "cmp");
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
                                                        'url' => "$_SERVER[SCRIPT_NAME]?token=$token&amp;action=remove&amp;type=blog&amp;er_id=".$post->id,
                                                        'icon' => 'fa-times',
                                                        'class' => 'delete',
                                                        'confirm' => $langePortfolioSureToRemoveResource,
                                                        'show' => ($post->user_id == $uid)
                                                    )))."
                                         </div>
                                            <h3 class='panel-title'>".q($data['title'])."</h3>
                                    </div>
                                    <div class='panel-body'>
                                        <div class='label label-success'>" . nice_format($data['timestamp'], true). "</div><br><br>".ellipsize_html(standard_text_escape($data['content']), 500, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?id=$id&amp;action=showBlogPost&amp;er_id=".$post->id."'> <span class='smaller'>[$langMore]</span></a></strong>")."
                                                    </div>
                                                    <div class='panel-footer'>
                                                    <div class='row'>
                                                    <div class='col-sm-6'>$post->course_title</div>
                                                    </div>
                                                    </div>
                                                    </div>";
            }
            $tool_content .= "</div>
                            </div>
                          </div>";
        }
        
        //show assignment submissions
        if ($submissions) {
            $tool_content .= '<div id="works" class="'.$work_div_class.'" style="padding-top:20px">';
            //usort($submissions, "cmp");
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
                $submission_content = "<div class='well'>"; 
                $submission_content .= "<div><button type='button' class='btn btn-primary btn-xs' data-toggle='collapse' data-target='#header_more_$submission->id'>$langMore</button></div>
                                       <div id='header_more_$submission->id' class='collapse'>";
                if (!empty($data['descr'])) {
                    $submission_content .= "<div><b>".$langDescription."</b>:</div><div>".$data['descr']."</div>";
                }
                $submission_content .= "<div><a href='resources.php?action=get&amp;id=$id&amp;token=$token&amp;type=assignment&amp;er_id=$submission->id'>$langWorkFile</a></div>";
                $submission_content .= "</div>";
                $submission_content .= "</div>";
                $submission_content .= "<div><b>$langSubmit</b>: " . nice_format($data['subm_date'], true). "</div>
                                       <div><b>".$m['grade']."</b>: ".$data['grade']." / ".$data['max_grade']."</div>
                                       <div><b>".$m['group_or_user']."</b>: ".$assignment_type."</div>";
                if (!is_null($data['subm_text'])) {
                    $submission_content .= "<div><b>$langWorkOnlineText</b>: <br>".$data['subm_text']."</div>";
                } else {
                   $submission_content .= "<div><a href='resources.php?action=get&amp;id=$id&amp;token=$token&amp;type=submission&amp;er_id=$submission->id'>$langWorkFile</a></div>"; 
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
                                                                'url' => "$_SERVER[SCRIPT_NAME]?token=$token&amp;action=remove&amp;type=work_submission&amp;er_id=".$submission->id,
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
            $tool_content .= "</div>
                            </div>
                          </div>";
        }
        
        //show mydocs collection
        if ($docs) {
            $tool_content .= '<div id="mydocs" class="'.$mydocs_div_class.'" style="padding-top:20px">';
            //usort($docs, "cmp");
            $tool_content .= "<div class='table-responsive'>
                                <table class='table-default'>
                                  <tbody>
                                    <tr class='list-header'>
                                      <th class='text-left' width='60'>$langType</th>
                                      <th class='text-left'>$langName</th>
                                      <th class='text-left'>$langDate</th>
                                      <th class='text-left'>$langSize</th>";
            if ($id == $uid) {
                $tool_content .= "<th class='text-center'>".icon('fa-gears', $langCommands)."</th>";
            }
                                      
            $tool_content .= "</tr>";
            foreach ($docs as $doc) {
                $data = unserialize($doc->data);
                if (empty($data['title'])) {
                    $filename = q($data['filename']);
                } else {
                    $filename = q($data['title']);
                }
                if (isset($data['extra_path'])) {
                    $row_class = 'not_visible';
                    $file_link = '<a href="'.$data['extra_path'].'">'.$filename.'</a> '.icon('fa-external-link', $langExternalFile);
                    $filesize = '0 B';
                } else {
                    $row_class = 'visible';
                    $file_link = "<a href='resources.php?action=get&amp;id=$id&amp;token=$token&amp;type=mydocs&amp;er_id=$doc->id'>$filename</a>";
                    $filesize = format_file_size(filesize($data['file_path']));
                }
                $tool_content .= "<tr class='$row_class'>
                                    <td class='text-center'><span class='fa ".choose_image('.' . $data['format'])."'></span></td>
                                    <td>$file_link</td>
                                    <td>".nice_format($data['date_modified'], true, true)."</td>
                                    <td>$filesize</td>
                                    <td class='option-btn-cell'>
                                       ". action_button(array(
                                                    array(
                                                            'title' => $langePortfolioRemoveResource,
                                                            'url' => "$_SERVER[SCRIPT_NAME]?token=$token&amp;action=remove&amp;type=my_docs&amp;er_id=".$doc->id,
                                                            'icon' => 'fa-times',
                                                            'class' => 'delete',
                                                            'confirm' => $langePortfolioSureToRemoveResource,
                                                            'show' => ($doc->user_id == $uid)
                                                    )))." 
                                    </td>
                                  </tr>";
            }      
            $tool_content .= "    </tbody>
                                </table>
                              </div>
                            </div>";
        }
        
        if ($userdata->eportfolio_enable == 1) {
            $social_share = "<div class='pull-right'>".print_sharing_links($urlServer."main/resources.php?id=$id&token=$token", $langUserePortfolio)."</div>";
        } else {
            $social_share = '';
        }
        
        $tool_content .= $social_share.'</div>';
    }
}

if ($uid == $id) {
    draw($tool_content, 1, null, $head_content);
} else {
    draw($tool_content, 0, null, $head_content);
}

function cmp($obj1, $obj2)
{   
    $data1 = unserialize($obj1->data);
    if (array_key_exists('subm_date', $data1)) {
        $key = 'subm_date';
    } elseif (array_key_exists('timestamp', $data1)) {
        $key = 'timestamp';
    } elseif (array_key_exists('date_modified', $data1)) {
        $key = 'date_modified';
    }
    $data1 = strtotime($data1[$key]);
    $data2 = unserialize($obj2->data);
    $data2 = strtotime($data2[$key]);
    
    if ($data1 < $data2)
        return true;
    else 
        return false;
}