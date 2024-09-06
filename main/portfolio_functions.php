<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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
 * @file portfolio_functions.php
 * @brief user course and course activity functions
 */
require_once 'include/lib/mediaresource.factory.php';
require_once 'main/personal_calendar/calendar_events.class.php';
require_once 'modules/message/class.mailbox.php';
require_once 'modules/message/class.msg.php';
/**
 * @brief display user courses
 * @param integer $uid
 * @return string
 */
function getUserCourseInfo($uid): string
{
    global $langCourse, $langActions,
           $session, $lesson_ids, $collaboration_ids, $courses, $urlServer, $lesson_content,
           $langUnregCourse, $langAdm, $langFavorite,
           $langNotEnrolledToLessons, $langWelcomeProfPerso, $langWelcomeStudPerso,
           $langWelcomeSelect, $langPreview, $langOfCourse,
           $langThisCourseDescriptionIsEmpty, $langSyllabus, $langNotificationsExist,
           $langMyCollaborations, $langPreviewCollaboration, $langUnregCollaboration, $langNotEnrolledToCollaborations,
           $langWelcomeStudCollab, $langWelcomeProfCollab, $langThisCollabDescriptionIsEmpty,
           $mine_courses, $mine_collaborations, $langNotificationsExist, $langCourseImage;

    if(!get_config('show_always_collaboration')){
        $myCourses = $mine_courses = getUserCourses($uid);
    }

    if(get_config('show_collaboration')){
        $myCollaborations = $mine_collaborations = getUserCollaborations($uid);
    }

    if(!get_config('show_always_collaboration')){
        if ($myCourses) {
            $lesson_content .= "<table id='portfolio_lessons' class='table portfolio-courses-table'>";
            $lesson_content .= "<thead class='sr-only'><tr><th>$langCourse</th><th>$langActions</th></tr></thead>";
            foreach ($myCourses as $data) {

                $courses[$data->code] = $data->status;
                $_SESSION['courses'] = $courses;
                $lesson_ids[] = $data->course_id;
                $visclass = '';

                //Get syllabus for course
                $syllabus = Database::get()->queryArray("SELECT cd.id, cd.title, cd.comments, cd.type, cdt.icon FROM course_description cd
                                        LEFT JOIN course_description_type cdt ON (cd.type = cdt.id)
                                        WHERE cd.course_id = ?d AND cd.visible = 1 ORDER BY cd.order", $data->course_id);

                if ($data->visible == COURSE_INACTIVE) {
                    $visclass = "not_visible";
                }
                if (isset($data->favorite)) {
                    $favorite_icon = 'fa-star Primary-500-cl';
                    $fav_status = 0;
                    $fav_message = '';
                } else {
                    $favorite_icon = 'fa-regular fa-star';
                    $fav_status = 1;
                    $fav_message = $langFavorite;
                }
                $license = '';
                if($data->course_license > 0){
                    $license = copyright_info($data->course_id);
                }
                $lesson_content .= "
                    <tr class='$visclass row-course'>
                        <td class='border-top-0 border-start-0 border-end-0'>
                            <div class='d-flex gap-3 flex-wrap'>
                                <a class='TextBold' href='{$urlServer}courses/$data->code/'>" . q(ellipsize($data->title, 64)) . "
                                    &nbsp;(" . q($data->public_code) . ")
                                </a>
                                <a id='btnNotification_{$data->course_id}' class='invisible btn btn-notification-course text-decoration-none' data-bs-toggle='collapse' href='#notification{$data->course_id}'
                                                role='button' aria-expanded='false' aria-controls='notification{$data->course_id}' aria-label='$langNotificationsExist'>
                                    <i class='fa-solid fa-bell link-color' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-original-title='$langNotificationsExist'></i>
                                </a>
                            </div>
                            <div>
                                <small class='vsmall-text Neutral-900-cl TextRegular'>" . q($data->professor) . "</small>
                            </div>
                            <div class='collapse' id='notification{$data->course_id}'>
                                <div class='d-flex align-items-start lesson-notifications' data-id='{$data->course_id}'></div>
                            </div>
                        </td>";

                $lesson_content .= "
                        <td class='border-top-0 border-start-0 border-end-0 text-end align-middle'>
                            <div class='col-12 portfolio-tools'>
                                <div class='d-inline-flex'>";

                $lesson_content .= "<a class='ClickCoursePortfolio me-3' href='javascript:void(0);' id='CourseTable_{$data->code}' type='button' class='btn btn-secondary' data-bs-toggle='tooltip' data-bs-placement='top' title='$langPreview&nbsp;$langOfCourse' aria-label='$langPreview&nbsp;$langOfCourse'>
                                    <i class='fa-solid fa-display'></i>
                                </a>

                                <div id='PortfolioModal{$data->code}' class='modal'>

                                    <div class='modal-content modal-content-opencourses px-lg-5 py-lg-5'>
                                        <div class='col-12 d-flex justify-content-between align-items-start modal-display'>
                                            <div>
                                                <div class='d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                                                    <h2 class='mb-0'>". q($data->title) . "</h2>
                                                    " . course_access_icon($data->visible) . "
                                                    $license
                                                </div>
                                                <div class='mt-2'>" . q($data->public_code) . "&nbsp; - &nbsp;" . q($data->professor) . "</div>
                                            </div>
                                            <div>
                                                <button aria-label='Close' type='button' class='close'></button>
                                            </div>

                                        </div>
                                        <div class='course-content mt-4'>
                                            <div class='col-12 d-flex justify-content-center align-items-start'>";
                if($data->course_image == NULL) {
                    $lesson_content .= "<img class='openCourseImg' src='{$urlServer}resources/img/ph1.jpg' alt='$langCourseImage' />";
                } else {
                    $lesson_content .= "<img class='openCourseImg' src='{$urlServer}courses/{$data->code}/image/{$data->course_image}' alt='$langCourseImage' />";
                }
                $lesson_content .= "</div>
                        <div class='col-12 openCourseDes mt-3 Neutral-900-cl pb-3'> ";
                if(empty($data->description)) {
                    $lesson_content .= "<p class='text-center'>$langThisCourseDescriptionIsEmpty</p>";
                } else {
                    $lesson_content .= "{$data->description}";
                }
                $lesson_content .= "</div>";
                    if(count($syllabus) > 0) {
                        $lesson_content .= "<div class='col-12 mt-4'>
                                                <div class='row m-auto'>
                                                    <div class='panel px-0'>
                                                        <div class='panel-group group-section mt-2 px-0' id='accordionCourseSyllabus{$data->course_id}'>
                                                            <ul class='list-group list-group-flush'>
                                                                <li class='list-group-item px-0 mb-4 bg-transparent'>

                                                                    <div class='d-flex justify-content-between border-bottom-default'>
                                                                        <a class='accordion-btn d-flex justify-content-start align-items-start gap-2 py-2' role='button' data-bs-toggle='collapse' href='#courseSyllabus{$data->course_id}' aria-expanded='true' aria-controls='courseSyllabus{$data->course_id}'>
                                                                            <i class='fa-solid fa-chevron-down settings-icon'></i>
                                                                            $langSyllabus
                                                                        </a>
                                                                    </div>
                                                                    <div class='panel-collapse accordion-collapse collapse border-0 rounded-0 mt-3 show' id='courseSyllabus{$data->course_id}' data-bs-parent='#accordionCourseSyllabus{$data->course_id}'>";
                                                                    foreach ($syllabus as $row) {
                                                                        $lesson_content .= "<div class='col-12 mb-4'>
                                                                            <p class='form-label text-start'>" .q($row->title) ."</p>
                                                                            " . standard_text_escape($row->comments) . "
                                                                        </div>";
                                                                    }
                                            $lesson_content .= "    </div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>";
                        }

                    $lesson_content .= "</div>";

                $lesson_content .= "</div>
                                </div>";

                $lesson_content .= icon($favorite_icon, $fav_message, "course_favorite.php?course=" . $data->code . "&amp;fav=$fav_status");
                if ($data->status == USER_STUDENT) {
                    if (get_config('disable_student_unregister_cours') == 0) {
                        $lesson_content .= icon('fa-minus-circle ms-3', $langUnregCourse, "{$urlServer}main/unregcours.php?cid=$data->course_id&amp;uid=$uid");
                    }
                } elseif ($data->status == USER_TEACHER) {
                    $lesson_content .= icon('fa-wrench ms-3', $langAdm, "{$urlServer}modules/course_info/index.php?from_home=true&amp;course=" . $data->code, '', true, true);
                }
                $lesson_content .= "</div>
                            </div>
                        </td>
                    </tr>
                ";

            }
            $lesson_content .= "</tbody></table>";

        } else { // if we are not registered to courses
            $lesson_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNotEnrolledToLessons!</span></div></div>";
            if ($session->status == USER_TEACHER) {
                $lesson_content .= "<div class='col-sm-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langWelcomeSelect $langWelcomeProfPerso</span></div></div>";
            } else {
                $lesson_content .= "<div class='col-sm-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langWelcomeSelect $langWelcomeStudPerso</span></div></div>";
            }
        }
    }





    // Create ui for collabations which a user is participated in.
    if(get_config('show_collaboration')){
        if(!get_config('show_always_collaboration')){
        $lesson_content .= "<div class='col-12 mt-5 mb-4'>
                <h2>$langMyCollaborations&nbsp;&nbsp;(" . count($myCollaborations) . ")</h2>
            </div>";}
        if($myCollaborations){
                $lesson_content .= "<table id='portfolio_collaborations' class='table portfolio-collaborations-table'>";
                $lesson_content .= "<thead class='sr-only'><tr><th>$langCourse</th><th>$langActions</th></tr></thead>";
                foreach ($myCollaborations as $data) {

                    $collaborations[$data->code] = $data->status;
                    $_SESSION['collaborations'] = $collaborations;
                    $collaboration_ids[] = $data->course_id;
                    $visclass = '';

                    //Get syllabus for course
                    $syllabus = Database::get()->queryArray("SELECT cd.id, cd.title, cd.comments, cd.type, cdt.icon FROM course_description cd
                                            LEFT JOIN course_description_type cdt ON (cd.type = cdt.id)
                                            WHERE cd.course_id = ?d AND cd.visible = 1 ORDER BY cd.order", $data->course_id);

                    if ($data->visible == COURSE_INACTIVE) {
                        $visclass = "not_visible";
                    }
                    if (isset($data->favorite)) {
                        $favorite_icon = 'fa-star Primary-500-cl';
                        $fav_status = 0;
                        $fav_message = '';
                    } else {
                        $favorite_icon = 'fa-regular fa-star';
                        $fav_status = 1;
                        $fav_message = $langFavorite;
                    }
                    $license = '';
                    if($data->course_license > 0){
                        $license = copyright_info($data->course_id);
                    }
                    $lesson_content .= "
                        <tr class='$visclass row-course'>
                            <td class='border-top-0 border-start-0 border-end-0'>
                                <div class='d-flex gap-3 flex-wrap'>
                                    <a class='TextBold' href='{$urlServer}courses/$data->code/'>" . q(ellipsize($data->title, 64)) . "
                                        &nbsp;(" . q($data->public_code) . ")
                                    </a>
                                    <a id='btnNotification_{$data->course_id}' class='invisible btn btn-notification-collaboration text-decoration-none' data-bs-toggle='collapse' href='#notification{$data->course_id}'
                                                    role='button' aria-expanded='false' aria-controls='notification{$data->course_id}' aria-label='$langNotificationsExist'>
                                        <i class='fa-solid fa-bell link-color' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-original-title='$langNotificationsExist'></i>
                                    </a>
                                </div>
                                <div>
                                    <small class='vsmall-text Neutral-900-cl TextRegular'>" . q($data->professor) . "</small>
                                </div>
                                <div class='collapse' id='notification{$data->course_id}'>
                                    <div class='d-flex align-items-start collaboration-notifications' data-id='{$data->course_id}'></div>
                                </div>
                            </td>";

                    $lesson_content .= "
                            <td class='border-top-0 border-start-0 border-end-0 text-end align-middle'>
                                <div class='col-12 portfolio-tools'>
                                    <div class='d-inline-flex'>";

                    $lesson_content .= "<a class='ClickCoursePortfolio me-3' href='javascript:void(0);' id='CourseTable_{$data->code}' type='button' class='btn btn-secondary' data-bs-toggle='tooltip' data-bs-placement='top' title='$langPreview&nbsp;$langPreviewCollaboration' aria-label='$langPreview&nbsp;$langOfCourse'>
                                        <i class='fa-solid fa-display'></i>
                                    </a>

                                    <div id='PortfolioModal{$data->code}' class='modal'>

                                        <div class='modal-content modal-content-opencourses px-lg-5 py-lg-5'>
                                            <div class='col-12 d-flex justify-content-between align-items-start modal-display'>
                                                <div>
                                                    <div class='d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                                                        <h2 class='mb-0'>". q($data->title) . "</h2>
                                                        " . course_access_icon($data->visible) . "
                                                        $license
                                                    </div>
                                                    <div class='mt-2'>" . q($data->public_code) . "&nbsp; - &nbsp;" . q($data->professor) . "</div>
                                                </div>
                                                <div>
                                                    <button aria-label='Close' type='button' class='close'></button>
                                                </div>

                                            </div>
                                            <div class='course-content mt-4'>
                                                <div class='col-12 d-flex justify-content-center align-items-start'>";
                    if($data->course_image == NULL) {
                        $lesson_content .= "<img class='openCourseImg' src='{$urlServer}resources/img/ph1.jpg' alt='$langCourseImage' />";
                    } else {
                        $lesson_content .= "<img class='openCourseImg' src='{$urlServer}courses/{$data->code}/image/{$data->course_image}' alt='$langCourseImage' />";
                    }
                    $lesson_content .= "</div>
                            <div class='col-12 openCourseDes mt-3 Neutral-900-cl pb-3'> ";
                    if(empty($data->description)) {
                        $lesson_content .= "<p class='text-center'>$langThisCollabDescriptionIsEmpty</p>";

                    } else {
                        $lesson_content .= "{$data->description}";
                    }
                    $lesson_content .= "</div>";
                        if(count($syllabus) > 0) {
                            $lesson_content .= "<div class='col-12 mt-4'>
                                                    <div class='row m-auto'>
                                                        <div class='panel px-0'>
                                                            <div class='panel-group group-section mt-2 px-0' id='accordionCollaborationSyllabus{$data->course_id}'>
                                                                <ul class='list-group list-group-flush'>
                                                                    <li class='list-group-item px-0 mb-4 bg-transparent'>

                                                                        <div class='d-flex justify-content-between border-bottom-default'>
                                                                            <a class='accordion-btn d-flex justify-content-start align-items-start gap-2 py-2' role='button' data-bs-toggle='collapse' href='#collaborationSyllabus{$data->course_id}' aria-expanded='true' aria-controls='collaborationSyllabus{$data->course_id}'>
                                                                                <i class='fa-solid fa-chevron-down settings-icon'></i>
                                                                                $langSyllabus
                                                                            </a>
                                                                        </div>
                                                                        <div class='panel-collapse accordion-collapse collapse border-0 rounded-0 mt-3 show' id='collaborationSyllabus{$data->course_id}' data-bs-parent='#accordionCollaborationSyllabus{$data->course_id}'>";
                                                                        foreach ($syllabus as $row) {
                                                                            $lesson_content .= "<div class='col-12 mb-4'>
                                                                                <p class='form-label text-start'>" .q($row->title) ."</p>
                                                                                " . standard_text_escape($row->comments) . "
                                                                            </div>";
                                                                        }
                                                $lesson_content .= "    </div>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>";
                            }

                        $lesson_content .= "</div>";

                    $lesson_content .= "</div>
                                    </div>";

                    $lesson_content .= icon($favorite_icon, $fav_message, "course_favorite.php?course=" . $data->code . "&amp;fav=$fav_status");
                    if ($data->status == USER_STUDENT) {
                        if (get_config('disable_student_unregister_cours') == 0) {
                            $lesson_content .= icon('fa-minus-circle ms-3', $langUnregCollaboration, "{$urlServer}main/unregcours.php?cid=$data->course_id&amp;uid=$uid");
                        }
                    } elseif ($data->status == USER_TEACHER) {
                        $lesson_content .= icon('fa-wrench ms-3', $langAdm, "{$urlServer}modules/course_info/index.php?from_home=true&amp;course=" . $data->code, '', true, true);
                    }
                    $lesson_content .= "</div>
                                </div>
                            </td>
                        </tr>
                    ";

                }
                $lesson_content .= "</tbody></table>";

        }else{
            $lesson_content .= "<div class='col-sm-12 mt-4'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNotEnrolledToCollaborations!</span></div></div>";
            if ($session->status == USER_TEACHER) {
                $lesson_content .= "<div class='col-sm-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langWelcomeSelect $langWelcomeProfCollab</span></div></div>";
            } else {
                $lesson_content .= "<div class='col-sm-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langWelcomeSelect $langWelcomeStudCollab</span></div></div>";
            }
        }
    }

    return $lesson_content;
}


/**
 * @brief get last month course announcements
 * @param $lesson_id
 * @param string $type
 * @param false $to_ajax
 * @param string $filter
 * @return array|string
 */
function getUserAnnouncements($lesson_id, $type='', $to_ajax=false, $filter='') {

    global $urlAppend, $langAdminAn;

    if ($type == 'more') {
        $sql_append = '';
    } else {
        $sql_append = 'LIMIT 5';
    }

    if (!empty($filter)) {
        $admin_filter_sql = 'AND admin_announcement.title LIKE ?s';
        $course_filter_sql = 'AND announcement.title LIKE ?s';
        $filter_param = '%' . $filter . '%';
    } else {
        $admin_filter_sql = $course_filter_sql = '';
        $filter_param = array();
    }

    if (!count($lesson_id)) {
        $q = Database::get()->queryArray("
                                SELECT admin_announcement.title,
                                             admin_announcement.`date` AS an_date,
                                             admin_announcement.id
                                FROM admin_announcement
                                WHERE admin_announcement.visible = 1
                                        AND (admin_announcement.begin <= " . DBHelper::timeAfter() . " OR admin_announcement.begin IS NULL)
                                        AND (admin_announcement.end >= " . DBHelper::timeAfter() . " OR admin_announcement.end IS NULL)
                                        AND admin_announcement.`date` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) $admin_filter_sql
                                 ORDER BY an_date DESC
                         $sql_append", $filter_param);
    } else {

        $course_id_sql = implode(', ', array_fill(0, count($lesson_id), '?d'));

        $q = Database::get()->queryArray("(SELECT announcement.title,
                                             announcement.`date` AS an_date,
                                             announcement.id,
                                             announcement.content,
                                             course.code,
                                             course.title course_title
                            FROM course, course_module, announcement
                            WHERE course.id IN ($course_id_sql)
                                    AND course.id = course_module.course_id
                                    AND course.id = announcement.course_id
                                    AND announcement.visible = 1
                                    AND (announcement.start_display <= " . DBHelper::timeAfter() . " OR announcement.start_display IS NULL)
                                    AND (announcement.stop_display >= " . DBHelper::timeAfter() . " OR announcement.stop_display IS NULL)
                                    AND announcement.`date` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                                    AND course_module.module_id = " . MODULE_ID_ANNOUNCE . "
                                    AND course_module.visible = 1 $course_filter_sql)
                            UNION
                                (SELECT admin_announcement.title,
                                             admin_announcement.`date` AS admin_an_date,
                                             admin_announcement.id, admin_announcement.body AS content, '', ''
                                FROM admin_announcement
                                WHERE admin_announcement.visible = 1
                                      AND (admin_announcement.begin <= " . DBHelper::timeAfter() . " OR admin_announcement.begin IS NULL)
                                      AND (admin_announcement.end >= " . DBHelper::timeAfter() . " OR admin_announcement.end IS NULL)
                                      AND admin_announcement.`date` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) $admin_filter_sql
                                ) ORDER BY an_date DESC
                         $sql_append", $lesson_id, $filter_param, $filter_param);
    }

    if ($to_ajax) {
        $arr_an = array();
        foreach ($q as $arr_q) {
            $arr_an[] = $arr_q;
        }
        return $arr_an;
    } else {
        //Προσθήκη μετρητή ώστε να εμφανίζονται μέχρι 2 ανακοινώσεις σαν pagination
        // Όλες οι τελευταίες ανακοινώσεις εμφανίζονται όταν πατήσει ο χρήστης το κουμπί.
        $counterAn = 0;
        $ann_content = '';
        if($q){
           $ann_content .= "<ul class='list-group list-group-flush mb-2'>";
        }
        foreach ($q as $ann) {
            if ($counterAn <= 1){
                if (isset($ann->code) & $ann->code != '') {
                    $course_title = q(ellipsize($ann->course_title, 80));
                    $ann_url = $urlAppend . 'modules/announcements/index.php?course=' . $ann->code . '&amp;an_id=' . $ann->id;
                    $ann_date = format_locale_date(strtotime($ann->an_date));
                    $ann_content .= "
                        <li class='list-group-item element'>
                            <a class='TextBold' href='$ann_url'>" . q(ellipsize($ann->title, 60)) . "</a>
                            <p class='TextBold mb-0'>$course_title</p>
                            <div class='TextRegular Neutral-900-cl'>$ann_date</div>
                        </li>";
                } else {
                    $ann_url = $urlAppend . 'main/system_announcements.php?an_id=' . $ann->id;
                    $ann_date = format_locale_date(strtotime($ann->an_date));
                    $ann_content .= "
                    <li class='list-group-item element'>
                        <a class='TextBold' href='$ann_url'>" . q(ellipsize($ann->title, 60)) . "</a>
                        <p class='TextBold mb-0'>$langAdminAn&nbsp; <i class='fa-solid fa-user'></i></p>
                        <div class='TextRegular Neutral-900-cl'>$ann_date</div>
                    </li>";
                }
            }
            $counterAn++;
        }
        if ($q) {
            $ann_content .= "</ul>";
        }
        return $ann_content;
    }
}

/**
 * @brief get user personal messages
 * @return string
 */
function getUserMessages() {

    global $uid, $urlServer, $langFrom;

    $message_content = '';

    $mbox = new Mailbox($uid, 0);
    $msgs = $mbox->getInboxMsgs('', 5);
    $counterMs = 0;
    if($msgs){
         $message_content .= "<ul class='list-group list-group-flush mb-2'>";
    }

    foreach ($msgs as $message) {
        if($counterMs <= 1){
            if ($message->course_id > 0) {
                $course_title = q(ellipsize(course_id_to_title($message->course_id), 30));
            } else {
                $course_title = '';
            }
            $message_date = format_locale_date($message->timestamp);
            $message_content .= "<li class='list-group-item element'>
                                        <div class='text-title TextBold'>
                                            <span>$langFrom:</span>
                                            <span>".display_user($message->author_id, false, false)."</span>
                                        </div>

                                        <a class='TextBold mt-2' href='{$urlServer}modules/message/index.php?mid=$message->id'>" .q($message->subject)."</a>

                                        <p class='TextBold mb-0'>$course_title</p>
                                        <div class='TextRegular Neutral-900-cl'>$message_date</div>
                                </li>";
        }
        $counterMs++;
    }
    if($msgs){
        $message_content .= "</ul>";
    }
    return $message_content;
}


/**
 * @brief check if user has accepted or rejected the current privacy policy
 * @param $uid
 * @return boolean
 */
function user_has_accepted_policy($uid) {
    $q = Database::get()->querySingle('SELECT ts FROM user_consent
        WHERE user_id = ?d', $uid);
    if ($q and $q->ts >= get_config('privacy_policy_timestamp')) {
        return true;
    } else {
        return false;
    }
}


/*
 * @brief update user consent
 * @param $uid
 * @param bool $accept
 */
function user_accept_policy($uid, $accept = true) {
    $accept = $accept? 1: 0;
    Database::get()->query('INSERT INTO user_consent
        (has_accepted, user_id, ts) VALUES (?d, ?d, NOW())
        ON DUPLICATE KEY UPDATE has_accepted = ?d, ts = NOW()', $accept, $uid, $accept);
}


/**
 * @brief get user courses
 * @param $uid
 * @return array|DBResult|null
 */
function getUserCourses($uid)
{
    $myCourses = Database::get()->queryArray("SELECT course.id course_id,
                             course.code code,
                             course.public_code,
                             course.title title,
                             course.prof_names professor,
                             course.course_license course_license,
                             course.lang,
                             course.visible visible,
                             course.description description,
                             course.course_image course_image,
                             course.popular_course popular_course,
                             course_user.status status,
                             course_user.favorite favorite
                        FROM course JOIN course_user
                            ON course.id = course_user.course_id
                            AND course_user.user_id = ?d
                            AND (course.visible != " . COURSE_INACTIVE . " OR course_user.status = " . USER_TEACHER . ")
                            AND course.is_collaborative = ?d
                        ORDER BY favorite DESC, status ASC, visible ASC, title ASC", $uid, 0);

    return $myCourses;
}

/**
 * @brief get user collaborations
 * @param $uid
 * @return array|DBResult|null
 */
function getUserCollaborations($uid)
{
    $myCollaborations = Database::get()->queryArray("SELECT course.id course_id,
                             course.code code,
                             course.public_code,
                             course.title title,
                             course.prof_names professor,
                             course.course_license course_license,
                             course.lang,
                             course.visible visible,
                             course.description description,
                             course.course_image course_image,
                             course.popular_course popular_course,
                             course.is_collaborative is_collaborative,
                             course_user.status status,
                             course_user.favorite favorite
                        FROM course JOIN course_user
                            ON course.id = course_user.course_id
                            AND course_user.user_id = ?d
                            AND (course.visible != " . COURSE_INACTIVE . " OR course_user.status = " . USER_TEACHER . ")
                            AND course.is_collaborative = ?d
                        ORDER BY favorite DESC, status ASC, visible ASC, title ASC", $uid,1);

    return $myCollaborations;
}

/**
 * @brief count student courses (except inactive)
 * @param $uid
 * @return void
 */
function CountStudentCourses($uid) {
    $total = Database::get()->querySingle("SELECT COUNT(*) AS total
                FROM course JOIN course_user
                    ON course.id = course_user.course_id
                    AND course_user.user_id = ?d
                    AND course.is_collaborative = ?d
                    AND course.visible != " . COURSE_INACTIVE, $uid,0)->total;
    return $total;
}

/**
 * @brief count student courses (except inactive)
 * @param $uid
 * @return void
 */
function CountStudentCollaborations($uid) {
    $total = Database::get()->querySingle("SELECT COUNT(*) AS total
                FROM course JOIN course_user
                    ON course.id = course_user.course_id
                    AND course_user.user_id = ?d
                    AND course.is_collaborative = ?d
                    AND course.visible != " . COURSE_INACTIVE, $uid, 1)->total;
    return $total;
}

/**
 * @brief count teacher courses
 * @param $uid
 * @return mixed
 */
function CountTeacherCourses($uid) {
    $total = Database::get()->querySingle("SELECT COUNT(*) AS total
                FROM course JOIN course_user
                    ON course.id = course_user.course_id
            AND course_user.user_id = ?d
            AND course.is_collaborative = ?d", $uid,0)->total;

    return $total;
}

/**
 * @brief count teacher courses
 * @param $uid
 * @return mixed
 */
function CountTeacherCollaborations($uid) {
    $total = Database::get()->querySingle("SELECT COUNT(*) AS total
                FROM course JOIN course_user
                    ON course.id = course_user.course_id
            AND course_user.user_id = ?d
            AND course.is_collaborative = ?d", $uid, 1)->total;

    return $total;
}
