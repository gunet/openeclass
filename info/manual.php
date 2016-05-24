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

$mail_ver_excluded = true;
require_once '../include/baseTheme.php';
$pageName = $langManuals;

$addon = '';
$url = 'http://docs.openeclass.org/3.3';

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
                    'url' => "$url/$language:detail_descr"
                ],
            'short_descr' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langShortDesc,
                    'url' => "$url/$language:short_descr"
                ],
            'mant' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langManT,
                    'url' => "$url/$language:mant"
                ],
            'mans' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langManS,
                    'url' => "$url/$language:mans"
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
                    'url' => "$url/$language:create_account"
                ],
            'create_course' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langCourseCreate,
                    'url' => "$url/$language:create_course"
                ],
            'portfolio_management' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langUserPortfolio,
                    'url' => "$url/$language:portfolio_management"
                ],
            'course_management' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langAdministratorCourse,
                    'url' => "$url/$language:course_management"
                ],
            'forum_management' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langAdministratorForum,
                    'url' => "$url/$language:forum_management"
                ],
            'group_management' =>
                [
                    'desc' => icon('fa-globe') . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $langAdministratorGroup,
                    'url' => "$url/$language:group_management"
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