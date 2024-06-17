<?php
/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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

$mail_ver_excluded = true;
require_once '../include/baseTheme.php';

if (get_config('dont_display_manual_menu')) {
    redirect_to_home_page();
}

$toolName = $langManuals;

$addon = '';
$url = 'https://docs.openeclass.org/' . preg_replace('/^(\d\.\d+).*$/', '\1', ECLASS_VERSION);

if (!in_array($language, array('el', 'en'))) {
    $language = 'en';
    $addon = " ($langOnlyInEnglish)";
}

$data['general_tutorials'] = [
    'title' => $langGeneralTutorials . $addon,
    'links' =>
        [
            'detail_descr' =>
                [
                    'desc' => $langFinalDesc,
                    'url' => "$url/$language:detail_description"
                ],
            'short_descr' =>
                [
                    'desc' => $langShortDesc,
                    'url' => "$url/$language:short_description"
                ],
            'mant' =>
                [
                    'desc' => $langManT,
                    'url' => "$url/$language:teacher"
                ],
            'mans' =>
                [
                    'desc' => $langManS,
                    'url' => "$url/$language:student"
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
                    'url' => "$url/$language:wizards"
                ],
            'create_course' =>
                [
                    'desc' => $langCourseCreate,
                    'url' => "$url/$language:wizards"
                ],
            'portfolio_management' =>
                [
                    'desc' => $langUserPortfolio,
                    'url' => "$url/$language:wizards"
                ],
            'course_management' =>
                [
                    'desc' => $langAdministratorCourse,
                    'url' => "$url/$language:wizards"
                ],
            'forum_management' =>
                [
                    'desc' => $langAdministratorForum,
                    'url' => "$url/$language:wizards"
                ],
            'group_management' =>
                [
                    'desc' => $langAdministratorGroup,
                    'url' => "$url/$language:wizards"
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
                    'url' => "$url/$language:register_course"
                ],
            'personal_portfolio' =>
                [
                    'desc' => $langUserPortfolio,
                    'url' => "$url/$language:personal_portfolio"
                ],
            'ecourse' =>
                [
                    'desc' => $langIntroToCourse,
                    'url' => "$url/$language:ecourse"
                ],
            'forum' =>
                [
                    'desc' => $langForumParticipation,
                    'url' => "$url/$language:forum"
                ]
        ]
];

view('info.manual', $data);
