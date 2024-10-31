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

$mail_ver_excluded = true;
$force_password_excluded = true;
require_once '../include/baseTheme.php';

if (get_config('dont_display_manual_menu')) {
    redirect_to_home_page();
}

$toolName = $langManuals;

$addon = '';

if (!in_array($language, array('el', 'en'))) {
    $language = 'en';
    $addon = " ($langOnlyInEnglish)";
}

$url = 'https://docs.openeclass.org/' . $language . '/' . preg_replace('/^(\d\.\d+).*$/', '\1', ECLASS_VERSION);

$data['general_tutorials'] = [
    'title' => $langGeneralTutorials . $addon,
    'links' =>
        [
            'detail_descr' =>
                [
                    'desc' => $langFinalDesc,
                    'url' => "$url:detail_description"
                ],
            'short_descr' =>
                [
                    'desc' => $langShortDesc,
                    'url' => "$url:short_description"
                ],
            'mant' =>
                [
                    'desc' => $langManT,
                    'url' => "$url:teacher"
                ],
            'mans' =>
                [
                    'desc' => $langManS,
                    'url' => "$url:student"
                ]
        ]
];

$data['teacher_tutorials'] = [
    'title' => $langTeacherTutorials . $addon,
    'links' =>
        [
            'create_account' =>
                [
                    'desc' => $langCreateAccount,
                    'url' => "$url:wizards"
                ],
            'create_course' =>
                [
                    'desc' => $langCourseCreate,
                    'url' => "$url:wizards"
                ],
            'portfolio_management' =>
                [
                    'desc' => $langUserPortfolio,
                    'url' => "$url:wizards"
                ],
            'course_management' =>
                [
                    'desc' => $langAdministratorCourse,
                    'url' => "$url:wizards"
                ],
            'forum_management' =>
                [
                    'desc' => $langAdministratorForum,
                    'url' => "$url:wizards"
                ],
            'group_management' =>
                [
                    'desc' => $langAdministratorGroup,
                    'url' => "$url:wizards"
                ]
        ]
];


$data['student_tutorials'] = [
    'title' => $langStudentTutorials . $addon,
    'links' =>
        [
            'register_course' =>
                [
                    'desc' => $langRegCourses,
                    'url' => "$url:register_course"
                ],
            'personal_portfolio' =>
                [
                    'desc' => $langUserPortfolio,
                    'url' => "$url:personal_portfolio"
                ],
            'ecourse' =>
                [
                    'desc' => $langIntroToCourse,
                    'url' => "$url:ecourse"
                ],
            'forum' =>
                [
                    'desc' => $langForumParticipation,
                    'url' => "$url:forum"
                ]
        ]
];

view('info.manual', $data);
