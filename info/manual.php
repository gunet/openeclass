<?php
/* ========================================================================
 * Open eClass 4.0
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

$mail_ver_excluded = true;
require_once '../include/baseTheme.php';
$pageName = $langManuals;

$addon = '';
$url = 'https://docs.openeclass.org/' . preg_replace('/^(\d\.\d+).*$/', '\1', ECLASS_VERSION);

if (!in_array($language, array('el', 'en'))) {
    $language = 'en';
    $addon = " ($langOnlyInEnglish)";
}

$data['action_bar'] = action_bar([[
    'title' => $langBack,
    'url' => $urlServer,
    'icon' => 'fa-reply',
    'level' => 'primary-label',
    'button-class' => 'btn-default'
]], false);

$data['general_tutorials'] = [
    'title' => $langGeneralTutorials . $addon,
    'links' =>
        [
            'detail_descr' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langFinalDesc,
                    'url' => "$url/$language:detail_description"
                ],
            'short_descr' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langShortDesc,
                    'url' => "$url/$language:short_description"
                ],
            'mant' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langManT,
                    'url' => "$url/$language:teacher"
                ],
            'mans' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langManS,
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
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langCreateAccount,
                    'url' => "$url/$language:wizards"
                ],
            'create_course' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langCourseCreate,
                    'url' => "$url/$language:wizards"
                ],
            'portfolio_management' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langUserPortfolio,
                    'url' => "$url/$language:wizards"
                ],
            'course_management' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langAdministratorCourse,
                    'url' => "$url/$language:wizards"
                ],
            'forum_management' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langAdministratorForum,
                    'url' => "$url/$language:wizards"
                ],
            'group_management' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langAdministratorGroup,
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
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langRegCourses,
                    'url' => "$url/$language:register_course"
                ],
            'personal_portfolio' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langUserPortfolio,
                    'url' => "$url/$language:personal_portfolio"
                ],
            'ecourse' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langIntroToCourse,
                    'url' => "$url/$language:ecourse"
                ],
            'forum' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langForumParticipation,
                    'url' => "$url/$language:forum"
                ]
        ]
];


$data['menuTypeID'] = isset($uid) && $uid ? 1 : 0;

view('info.manual', $data);
