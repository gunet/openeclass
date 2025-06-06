<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$require_login = false;
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'modules/group/group_functions.php';
require_once 'modules/sharing/sharing.php';
require_once 'modules/progress/process_functions.php';

if (!get_config('eportfolio_enable')) {
    $tool_content = "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langePortfolioDisabled</span></div>";
    if ($session->status == 0) {
        draw($tool_content, 0);
    } else {
        draw($tool_content, 1);
    }
    exit;
}

if (isset($_GET['id']) && intval($_GET['id']) > 0) {
    $id = intval($_GET['id']);
    //$navigation_msg = $langUserePortfolio
    $navigation_msg = $langMyePortfolio;
} else {
    if ($session->status == 0) {
        redirect_to_home_page();
        exit;
    } else {
        $id = $uid;
        $navigation_msg = $langMyePortfolio;
    }
}

$toolName = $langMyePortfolio;
$pageName = $langResourcesCollection;

if (!token_validate('eportfolio' . $id, $_GET['token'])) {
    redirect_to_home_page();
}

$token = token_generate('eportfolio' . $id);
$navigation[] = array("url" => "{$urlAppend}main/profile/display_profile.php", "name" => $langMyProfile);
$navigation[] = array("url" => "{$urlAppend}main/eportfolio/index.php?id=$id&token=$token", "name" => $navigation_msg);

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
            $tool_content .= "<div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langePortfolioDisableWarning</span></div></div>";
        }

        if ($userdata->eportfolio_enable == 1) {
            $tool_content .= "<script type='text/javascript'>
                                $(document).ready(function(){
                                    $('#copy-btn').tooltip({container: 'body'});
                                })
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

        $action_bar = action_bar(array(
            array('title' => $userdata->eportfolio_enable ? $langViewHide : $langViewShow,
                'url' => $userdata->eportfolio_enable ? "{$urlAppend}main/eportfolio/index.php?id=$id&amp;token=$token&amp;toggle_val=off" : "{$urlAppend}main/eportfolio/index.php?id=$id&amp;token=$token&amp;toggle_val=on",
                'icon' => $userdata->eportfolio_enable ? 'fa-eye-slash' : 'fa-eye',
                'level' => 'primary'),
            array('title' => $langBio,
                'url' => "{$urlAppend}main/eportfolio/index.php?action=get_bio&amp;id=$id&amp;token=$token",
                'icon' => 'fa-solid fa-book-open',
                'level' => 'primary',
                'show' => file_exists("$webDir/courses/eportfolio/userbios/$id/bio.pdf")),
            array('title' => $langUploadBio,
                'url' => "{$urlAppend}main/eportfolio/bio_upload.php",
                'icon' => 'fa-upload'),
            array('title' => $langEditChange,
                'url' => "{$urlAppend}main/eportfolio/edit_eportfolio.php",
                'icon' => 'fa-edit' ),
            array('title' => $langResourcesCollection,
                'url' => "{$urlAppend}main/eportfolio/resources.php?id=$id&amp;token=$token",
                'icon' => 'fa-solid fa-award',
                'level' => 'primary'
            )
        ));
        $tool_content .= $action_bar;

        $tool_content .= "<div class='col-12'><div class='alert alert-info '><i class='fa-solid fa-circle-info fa-lg'></i><span>
                            $langePortfolioCollectionUserInfo</span>
                          </div></div>";

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
                            Session::flash('message', $langePortfolioResourceAdded);
                            Session::flash('alert-class', 'alert-success');
                            $tool_content .= "
                                              <div class='col-12'>
                                                  <div class='alert alert-success alert-dismissible fade show' role='alert'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langePortfolioResourceAdded</span></div>
                                                  <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='$langClose'></button>
                                              </div>";
                        }
                    } else {
                        Session::flash('message', $langGeneralError);
                        Session::flash('alert-class', 'alert-danger');
                    }
                    redirect_to_home_page("main/eportfolio/resources.php?id=$uid&token=$token");
                } elseif ($rtype == 'work_submission') {
                    $submission = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE assignment_id = ?d AND uid = ?d", $rid, $uid);
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
                                Session::flash('message', $langePortfolioResourceAdded);
                                Session::flash('alert-class', 'alert-success');
                            }
                        }
                    } else {
                        Session::flash('message', $langGeneralError);
                        Session::flash('alert-class', 'alert-danger');
                    }
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

                            Session::flash('message', $langePortfolioResourceAdded);
                            Session::flash('alert-class', 'alert-success');
                        }
                    } else {
                        Session::flash('message', $langGeneralError);
                        Session::flash('alert-class', 'alert-danger');
                    }
                    redirect_to_home_page("main/eportfolio/resources.php?id=$uid&token=$token");
                } elseif ($rtype == 'my_badges') {
                    $badgeExists = Database::get()->querySingle("SELECT id FROM eportfolio_resource WHERE user_id = ?d AND resource_id = ?d AND resource_type = ?s", $uid, $rid, $rtype);
                    if ($badgeExists) {
                        Session::flash('message', $langResourceExists);
                        Session::flash('alert-class', 'alert-warning');
                    } else {
                        $userBadge = Database::get()->querySingle("SELECT id,completed_criteria,total_criteria FROM user_badge WHERE user = ?d AND badge = ?d", $uid, $rid);
                        if ($userBadge && $userBadge->completed_criteria == $userBadge->total_criteria) {
                            $badgeInfo = Database::get()->querySingle("SELECT * FROM badge WHERE id = ?d", $rid);
                            $data = array('title' => $badgeInfo->title, 'issuer' => $badgeInfo->issuer, 'description' => $badgeInfo->description, 
                                          'icon' => $badgeInfo->icon, 'course_id' => course_id_to_title($badgeInfo->course_id),
                                          'date_created' => $badgeInfo->created, 'date_expired' => $badgeInfo->expires, 'badgeId' => $rid);
    
                            Database::get()->query("INSERT INTO eportfolio_resource (user_id,resource_id,resource_type,course_id,course_title,data)
                                                    VALUES (?d,?d,?s,?d,?s,?s)", $uid, $rid, 'my_badges', $badgeInfo->course_id ,course_id_to_title($badgeInfo->course_id), serialize($data));
    
                            Session::flash('message', $langePortfolioResourceAdded);
                            Session::flash('alert-class','alert-success');
                        } elseif ($userBadge && $userBadge->completed_criteria < $userBadge->total_criteria) {
                            Session::flash('message', $langNoCompleted);
                            Session::flash('alert-class', 'alert-warning');
                        }
                    }
                    redirect_to_home_page("main/mycertificates.php");
                } elseif ($rtype == 'my_certificates') {
                    $certificateExists = Database::get()->querySingle("SELECT id FROM eportfolio_resource WHERE user_id = ?d AND resource_id = ?d AND resource_type = ?s", $uid, $rid, $rtype);
                    if ($certificateExists) {
                        Session::flash('message', $langResourceExists);
                        Session::flash('alert-class', 'alert-warning');
                    } else {
                        $userCertificate = Database::get()->querySingle("SELECT id,completed_criteria,total_criteria FROM user_certificate WHERE user = ?d AND `certificate` = ?d", $uid, $rid);
                        if ($userCertificate && $userCertificate->completed_criteria == $userCertificate->total_criteria) {
                            $certificateInfo = Database::get()->querySingle("SELECT * FROM `certificate` WHERE id = ?d", $rid);
                            $data = array('title' => $certificateInfo->title, 'issuer' => $certificateInfo->issuer, 'description' => $certificateInfo->description, 
                                          'template' => $certificateInfo->template, 'message' => $certificateInfo->message, 'course_id' => course_id_to_title($certificateInfo->course_id),
                                          'date_created' => $certificateInfo->created, 'date_expired' => $certificateInfo->expires, 'certificateId' => $rid);

                            Database::get()->query("INSERT INTO eportfolio_resource (user_id,resource_id,resource_type,course_id,course_title,data)
                                    VALUES (?d,?d,?s,?d,?s,?s)", $uid, $rid, 'my_certificates', $certificateInfo->course_id ,course_id_to_title($certificateInfo->course_id), serialize($data));
                            Session::flash('message', $langePortfolioResourceAdded);
                            Session::flash('alert-class','alert-success');
                        } elseif ($userCertificate && $userCertificate->completed_criteria < $userCertificate->total_criteria) {
                            Session::flash('message', $langNoCompleted);
                            Session::flash('alert-class', 'alert-warning');
                        }
                    }
                    redirect_to_home_page("main/mycertificates.php");
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
                Session::flash('message', $langePortfolioResourceRemoved);
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page("main/eportfolio/resources.php?id=$uid&token=$token");
            } else {
                Session::flash('message', $langGeneralError);
                Session::flash('alert-class', 'alert-danger');
                redirect_to_home_page("main/eportfolio/resources.php?id=$uid&token=$token");
            }
        }
    } else {
        if ($userdata->eportfolio_enable == 0) {
            $tool_content = "<div class='col-sm-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langUserePortfolioDisabled</span></div></div>";
            if ($session->status == 0) {
                draw($tool_content, 0);
            } else {
                draw($tool_content, 1);
            }
            exit;
        }

        $action_bar = action_bar(array(
                array('title' => $langBio,
                      'url' => "{$urlAppend}main/eportfolio/index.php?action=get_bio&amp;id=$id&amp;token=$token",
                      'icon' => 'fa-download',
                      'level' => 'primary-label',
                      'show' => file_exists("$webDir/courses/eportfolio/userbios/$id/bio.pdf"))
            ));
        $tool_content .= $action_bar;
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
                $post->course_title = $langCourse.': '.q($post->course_title);
            } else {
                $post->course_title = $langUserBlog;
            }
            $tool_content .= "<div class='card panelCard card-default px-lg-4 py-lg-3 mb-3'>
                                    <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>                                                                                 
                                            <h3>".q($data['title'])."</h3>
                                            <div>
                                                ". action_button(array(
                                                                    array(
                                                                            'title' => $langePortfolioRemoveResource,
                                                                            'url' => "$_SERVER[SCRIPT_NAME]?token=$token&amp;action=remove&amp;type=blog&amp;er_id=".$post->id,
                                                                            'icon' => 'fa-xmark',
                                                                            'class' => 'delete',
                                                                            'confirm' => $langePortfolioSureToRemoveResource,
                                                                            'show' => ($post->user_id == $uid)
                                                                    )))."
                                            </div>                                           
                                    </div>
                                    <div class='card-body'>
                                        <p>" . format_locale_date(strtotime($data['timestamp'])) . "</p><br><br>".standard_text_escape($data['content'])."
                                    </div>
                                    <div class='card-footer border-0 d-flex justify-content-start align-items-center'>                                        
                                            <div class='small-text'>$post->course_title</div>                                        
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
    $myBadges = Database::get()->queryArray("SELECT * FROM eportfolio_resource WHERE user_id = ?d AND resource_type = ?s ORDER BY time_added DESC", $id, 'my_badges');
    $myCertificates = Database::get()->queryArray("SELECT * FROM eportfolio_resource WHERE user_id = ?d AND resource_type = ?s ORDER BY time_added DESC", $id, 'my_certificates');

    //hide tabs when there are no resources
    if (!$blog_posts && !$submissions && !$docs && !$myBadges && !$myCertificates) {
        $tool_content .= "<div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langePortfolioNoResInCollection</span></div></div>";
    } else {

        $active_class = ' class="nav-item"';

        if ($blog_posts) {
            $blog_li = '<li class="nav-item" role="presentation"><button id="blogtab" class="nav-link active" data-bs-toggle="tab" data-bs-target="#blog">'.$langBlogPosts.'</button></li>';
            if ($active_class != '') {
                $blog_div_class = 'tab-pane fade show active';
            } else {
                $blog_div_class = 'tab-pane fade';
            }
            $active_class = '';
        } else {
            $blog_li = '';
        }

        if ($submissions) {
            $work_li = '<li class="nav-item" role="presentation"><button id="worktab" class="nav-link" data-bs-toggle="tab" data-bs-target="#works">'.$langWorks.'</button></li>';
            if ($active_class != '') {
                $work_div_class = 'tab-pane fade show active';
            } else {
                $work_div_class = 'tab-pane fade';
            }
            $active_class = '';
        } else {
            $work_li = '';
        }

        if ($docs) {
            $mydocs_li = '<li class="nav-item" role="presentation"><button id="docstab" class="nav-link" data-bs-toggle="tab" data-bs-target="#mydocs">'.$langDoc.'</button></li>';
            if ($active_class != '') {
                $mydocs_div_class = 'tab-pane fade show active';
            } else {
                $mydocs_div_class = 'tab-pane fade';
            }
            $active_class = '';
        } else {
            $mydocs_li = '';
        }

        if ($myBadges) {
            $myBadges_li = '<li class="nav-item" role="presentation"><button id="badgestab" class="nav-link" data-bs-toggle="tab" data-bs-target="#mybadges">'.$langBadges.'</button></li>';
            if ($active_class != '') {
                $myBadges_div_class = 'tab-pane fade show active';
            } else {
                $myBadges_div_class = 'tab-pane fade';
            }
            $active_class = '';
        } else {
            $myBadges_li = '';
        }

        if ($myCertificates) {
            $myCertificates_li = '<li class="nav-item" role="presentation"><button id="certificatestab" class="nav-link" data-bs-toggle="tab" data-bs-target="#mycertificates">'.$langCertificates.'</button></li>';
            if ($active_class != '') {
                $myCertificates_div_class = 'tab-pane fade show active';
            } else {
                $myCertificates_div_class = 'tab-pane fade';
            }
            $active_class = '';
        } else {
            $myCertificates_li = '';
        }

        $tool_content .= '<div class="col-12"><ul class="nav nav-tabs" role="tablist">
                            '.$blog_li.'
                            '.$work_li.'
                            '.$mydocs_li.'
                            '.$myBadges_li.'
                            '.$myCertificates_li.'
                          </ul></div>';
        $tool_content .= '<div class="col-12"><div class="tab-content pb-4">';

        //show blog_posts
        if ($blog_posts) {
            $tool_content .= '<div id="blog" role="tabpanel" class="'.$blog_div_class.'" aria-labelledby="blogtab" >';
            $tool_content .= "<div class='row row-cols-1 row-cols-md-2 g-4'>";

            foreach ($blog_posts as $post) {
                $tool_content .= "<div class='col'>";
                $data = unserialize($post->data);
                if (!empty($post->course_title)) {
                    $post->course_title = $langCourse.': '.q($post->course_title);
                } else {
                    $post->course_title = $langUserBlog;
                }
                $tool_content .= "<div class='card panelCard card-default px-lg-4 py-lg-3 mt-3 h-100'>
                                    <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>                                           
                                        <h3>".q($data['title'])."</h3>                                    
                                        <div>
                                            ". action_button(array(
                                                array(
                                                    'title' => $langePortfolioRemoveResource,
                                                    'url' => "$_SERVER[SCRIPT_NAME]?token=$token&amp;action=remove&amp;type=blog&amp;er_id=".$post->id,
                                                    'icon' => 'fa-xmark',
                                                    'class' => 'delete',
                                                    'confirm' => $langePortfolioSureToRemoveResource,
                                                    'show' => ($post->user_id == $uid)
                                                )))."
                                        </div>                                          
                                    </div>
                                    <div class='card-body'>
                                        <p class='TextBold'>$langSubmit:<span class='ms-1 small-text TextRegular'>" . format_locale_date(strtotime($data['timestamp'])) . "</span></p>
                                        ".ellipsize_html(standard_text_escape($data['content']), 500, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?id=$id&amp;action=showBlogPost&amp;er_id=".$post->id."'> <span class='smaller'>[$langMore]</span></a></strong>")."
                                    </div>
                                    <div class='card-footer border-0 d-flex justify-content-start align-items-center'>                                       
                                        <div class='small-text'>$post->course_title</div>                                       
                                    </div>
                                </div>";
            $tool_content .= "</div>";
            }
            $tool_content .= "
                            </div>
                          </div>";
        }

        //show assignment submissions
        if ($submissions) {
            $tool_content .= '<div id="works" role="tabpanel" class="'.$work_div_class.'" aria-labelledby="worktab" style="padding-top:20px">';
            $tool_content .= "<div class='row row-cols-1 row-cols-md-2 g-4'>";

            foreach ($submissions as $submission) {
                $tool_content .= "<div class='col'>";
                $data = unserialize($submission->data);
                if (is_null($data['grade'])) {
                    $data['grade'] = '-';
                }
                if ($data['group_id'] == 0) {
                    $assignment_type = $langUserAssignment;
                } else {
                    $assignment_type = $langGroupAssignment;
                }
                $submission_header_content = "<h3>".q($data['title'])."</h3>";
                $submission->course_title = $langCourse.': '. q($submission->course_title);

                $submission_content = " <div class='well panel border-bottom-default mb-3'>
                                            <div class='panel-group group-section' id='accordion_$submission->id' role='tablist' aria-multiselectable='true'>
                                                <ul class='list-group list-group-flush'>
                                                    <li class='list-group-item px-0'>";
                                $submission_content .= "<a type='button' class='accordion-btn d-flex justify-content-start align-items-start' data-bs-toggle='collapse' href='#header_more_$submission->id' aria-expanded='false' aria-controls='#header_more_$submission->id'>
                                                            <span class='fa-solid fa-chevron-down'></span>
                                                            $langMore
                                                        </a>
                                                        <div id='header_more_$submission->id' class='panel-collapse accordion-collapse collapse border-0 rounded-0 mt-3' role='tabpanel' data-bs-parent='#accordion_$submission->id'>";
                                if (!empty($data['descr'])) {
                                    $submission_content .= "<div class='mt-3'>
                                                                <p class='title-default'>".$langDescription."</p>
                                                            </div>
                                                            <div>".$data['descr']."</div>";
                                }
                                    $submission_content .= "<div class='mt-3'>
                                                                <a class='link-color TextBold' href='resources.php?action=get&amp;id=$id&amp;token=$token&amp;type=assignment&amp;er_id=$submission->id'>$langWorkFile</a>
                                                            </div>";
                                $submission_content .= "</div>
                                                    </li>
                                                </ul>";
                    $submission_content .= "</div>
                                        </div>";



                $submission_content .= "<div class='mb-3'><p class='title-default'>$langSubmit</p> " . format_locale_date(strtotime($data['subm_date'])) . "</div>
                                       <div class='mb-3'><p class='title-default'>$langGradebookGrade</p> ".$data['grade']." / ".$data['max_grade']."</div>
                                       <div class='mb-3'><p class='title-default'>".$langAssignmentType."</p> ".$assignment_type."</div>";

                if (!is_null($data['subm_text'])) {
                    $submission_content .= "<div class='mb-3'><p class='title-default'>$langWorkOnlineText</p>".$data['subm_text']."</div>";
                } else {
                   $submission_content .= "<div class='mb-3'><a class='link-color TextBold' href='resources.php?action=get&amp;id=$id&amp;token=$token&amp;type=submission&amp;er_id=$submission->id'>$langWorkFile</a></div>";
                }
                $submission_footer = "<div class='card-footer border-0 d-flex justify-content-start align-items-center'>                                         
                                              <div class='small-text'>$submission->course_title</div>                                          
                                      </div>";
                $tool_content .= "<div class='card panelCard card-default px-lg-4 py-lg-3 h-100'>
                                    <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>                                        
                                            $submission_header_content                                           
                                            <div>
                                            ". action_button(array(
                                                array(
                                                        'title' => $langePortfolioRemoveResource,
                                                        'url' => "$_SERVER[SCRIPT_NAME]?token=$token&amp;action=remove&amp;type=work_submission&amp;er_id=".$submission->id,
                                                        'icon' => 'fa-xmark',
                                                        'class' => 'delete',
                                                        'confirm' => $langePortfolioSureToRemoveResource,
                                                        'show' => ($submission->user_id == $uid)
                                                )))."
                                            </div>
                                    </div>
                                    <div class='card-body'>
                                        $submission_content
                                    </div>
                                    $submission_footer
                                </div>";
                $tool_content .= "</div>";
            }
            $tool_content .= "</div>
                            </div>";
        }

        //show mydocs collection
        if ($docs) {
            $tool_content .= '<div id="mydocs" role="tabpanel" class="'.$mydocs_div_class.'" aria-labelledby="blogtab" style="padding-top:20px">';
            $tool_content .= "<div class='table-responsive'>
                                <table class='table-default'>
                                  <tbody>
                                    <tr class='list-header'>
                                      <th>$langType</th>
                                      <th>$langName</th>
                                      <th>$langDate</th>
                                      <th>$langSize</th>";
            if ($id == $uid) {
                $tool_content .= "<th class='text-end' aria-label='$langSettingSelect'>".icon('fa-gears', $langActions)."</th>";
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
                                    <td><span class='fa ".choose_image('.' . $data['format'])."'></span></td>
                                    <td>$file_link</td>
                                    <td>".format_locale_date(strtotime($data['date_modified']), 'short', false)."</td>
                                    <td>$filesize</td>
                                    <td class='text-end'>
                                       ". action_button(array(
                                                    array(
                                                            'title' => $langePortfolioRemoveResource,
                                                            'url' => "$_SERVER[SCRIPT_NAME]?token=$token&amp;action=remove&amp;type=my_docs&amp;er_id=".$doc->id,
                                                            'icon' => 'fa-xmark',
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

        //show mybadges collection
        if ($myBadges) {
            $tool_content .= '<div id="mybadges" role="tabpanel" class="'.$myBadges_div_class.'" aria-labelledby="mybadgestab" >';
            $tool_content .= "<div class='row row-cols-1 row-cols-md-2 g-4'>";

            foreach ($myBadges as $mybadge) {
                $tool_content .= "<div class='col'>";
                $data = unserialize($mybadge->data);
                if (!empty($mybadge->course_title)) {
                    $mybadge->course_title = $langCourse.': '.q($mybadge->course_title);
                } else {
                    $mybadge->course_title = $langBadges;
                }
                $tool_content .= "<div class='card panelCard card-default px-lg-4 py-lg-3 mt-3 h-100'>
                                    <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>                                           
                                        <h3>".q($data['title'])."</h3>                                    
                                        <div>
                                            ". action_button(array(
                                                array(
                                                    'title' => $langePortfolioRemoveResource,
                                                    'url' => "$_SERVER[SCRIPT_NAME]?token=$token&amp;action=remove&amp;type=my_badges&amp;er_id=".$mybadge->id,
                                                    'icon' => 'fa-xmark',
                                                    'class' => 'delete',
                                                    'confirm' => $langePortfolioSureToRemoveResource,
                                                    'show' => ($mybadge->user_id == $uid)
                                                )))."
                                        </div>                                          
                                    </div>
                                    <div class='card-body'>
                                        <img style='height:150px; width:150px;' src='{$urlServer}" . BADGE_TEMPLATE_PATH . get_badge_filename($data['badgeId']) ."' class='card-img-top m-auto d-block mt-3' alt='badge'>
                                        <div class='card-body text-center'>
                                            <a class='link-color' href='{$urlServer}modules/progress/index.php?course=" . course_id_to_code($mybadge->course_id) . "&amp;badge_id= " .  $data['badgeId'] . "&amp;u=" . $mybadge->user_id . "'>
                                                " . ellipsize($data['title'], 40) . "
                                                " . format_locale_date(strtotime($data['date_created'] ?? ''), null, false) . "
                                                " . $data['issuer'] . "
                                            </a>
                                        </div>
                                    </div>
                                </div>";
            $tool_content .= "</div>";
            }
            $tool_content .= "
                            </div>
                          </div>";
        }

        //show my certificates collection
        if ($myCertificates) {
            $tool_content .= '<div id="mycertificates" role="tabpanel" class="'.$myCertificates_div_class.'" aria-labelledby="mycertificatestab" >';
            $tool_content .= "<div class='row row-cols-1 row-cols-md-2 g-4'>";

            foreach ($myCertificates as $mycertificate) {
                $tool_content .= "<div class='col'>";
                $data = unserialize($mycertificate->data);
                if (!empty($mycertificate->course_title)) {
                    $mycertificate->course_title = $langCourse.': '.q($mycertificate->course_title);
                } else {
                    $mycertificate->course_title = $langCertificates;
                }
                $identifier = Database::get()->querySingle("SELECT identifier FROM certified_users WHERE cert_id = ?d AND template_id = ?d AND user_id = ?d", $mycertificate->resource_id, $data['template'], $mycertificate->user_id)->identifier;
                $tool_content .= "<div class='card panelCard card-default px-lg-4 py-lg-3 mt-3 h-100'>
                                    <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>                                           
                                        <h3>".q($data['title'])."</h3>                                    
                                        <div>
                                            ". action_button(array(
                                                array(
                                                    'title' => $langePortfolioRemoveResource,
                                                    'url' => "$_SERVER[SCRIPT_NAME]?token=$token&amp;action=remove&amp;type=my_certificates&amp;er_id=".$mycertificate->id,
                                                    'icon' => 'fa-xmark',
                                                    'class' => 'delete',
                                                    'confirm' => $langePortfolioSureToRemoveResource,
                                                    'show' => ($mycertificate->user_id == $uid)
                                                )))."
                                        </div>                                          
                                    </div>
                                    <div class='card-body'>
                                        <img style='height:150px; width:150px;' src='{$urlServer}resources/img/game/badge.png' target='_blank' class='card-img-top m-auto d-block mt-3' alt='certificate'>
                                        <div class='card-body text-center'>
                                            <a class='link-color' href='{$urlServer}main/out.php?i={$identifier}'>
                                                " . ellipsize($data['title'], 40) . "
                                                " . format_locale_date(strtotime($data['date_created'] ?? ''), null, false) . "
                                                " . $data['issuer'] . "
                                            </a>
                                        </div>
                                    </div>
                                </div>";
            $tool_content .= "</div>";
            }
            $tool_content .= "
                            </div>
                          </div>";
        }

        if ($userdata->eportfolio_enable == 1) {
            $social_share = "<div class='col-12 mt-5'><div class='shadow-sm p-3 rounded float-end rounded-pill'>".print_sharing_links($urlServer."main/resources.php?id=$id&token=$token", $langUserePortfolio)."</div></div>";
        } else {
            $social_share = '';
        }

        $tool_content .= $social_share.'</div></div>';
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
