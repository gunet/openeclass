<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

include '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';

$countCallback = null;
$isInOpenCoursesMode = (defined('LISTING_MODE') && LISTING_MODE === 'COURSE_METADATA');

if ($isInOpenCoursesMode) {
    require_once 'modules/course_metadata/CourseXML.php';
    $countCallback = CourseXMLElement::getCountCallback();
    // exit if feature disabled
    if (!get_config('opencourses_enable')) {
        header("Location: {$urlServer}");
        exit();
    }
}

$tree = new Hierarchy();
$nameTools = $langListCourses;
$navigation[] = array('url' => 'listfaculte.php', 'name' => $langSelectFac);

if (isset($_GET['fc']))
    $fc = intval($_GET['fc']);

// parse the faculte id in a session
// This is needed in case the user decides to switch language.
if (isset($fc))
    $_SESSION['fc_memo'] = $fc;
else
    $fc = $_SESSION['fc_memo'];


$fac = Database::get()->querySingle("SELECT name FROM hierarchy WHERE id = ?d", $fc)->name;
if (!($fac = $fac[0]))
    die("ERROR: no faculty with id $fc");


// use the following array for the legend icons
$icons = array(
    2 => "<img src='$themeimg/lock_open.png'         alt='" . $m['legopen'] . "' title='" . $m['legopen'] . "' width='16' height='16' />",
    1 => "<img src='$themeimg/lock_registration.png' alt='" . $m['legrestricted'] . "' title='" . $m['legrestricted'] . "' width='16' height='16' />",
    0 => "<img src='$themeimg/lock_closed.png'       alt='" . $m['legclosed'] . "' title='" . $m['legclosed'] . "' width='16' height='16' />"
);

if (count($tree->buildRootsArray()) > 1) {
    $tool_content .= $tree->buildRootsSelectForm($fc);
}

$tool_content .= "<table width=100% class='tbl_border'>
                    <tr>
                    <th><a name='top'></a>$langFaculty:&nbsp;<b>" . $tree->getFullPath($fc, false, $_SERVER['SCRIPT_NAME'] . '?fc=') . "</b></th>
                    </tr>
                  </table><br/>\n\n";

$tool_content .= $tree->buildDepartmentChildrenNavigationHtml($fc, 'opencourses', $countCallback);

$queryCourseIds = '';
$queryExtraSelect = '';
$queryExtraJoin = '';
$queryExtraJoinWhere = '';
$runQuery = true;

if ($isInOpenCoursesMode) {
    // find subnode's certified opencourses
    $opencourses = array();
    Database::get()->queryFunc("SELECT course.id, course.code
                                  FROM course, course_department, course_review
                                 WHERE course.id = course_department.course
                                   AND course.id = course_review.course_id
                                   AND course_department.department = ?d
                                   AND course_review.is_certified = 1", function($course) use (&$opencourses) {
        $opencourses[$course->id] = $course->code;
    }, $fc);

    // construct comma seperated string with open courses ids
    $commaIds = "";
    $i = 0;
    foreach ($opencourses as $courseId => $courseCode) {
        if ($i != 0) {
            $commaIds .= ",";
        }
        $commaIds .= $courseId;
        $i++;
    }

    if (count($opencourses) > 0) {
        $queryCourseIds = " AND course.id IN ($commaIds) ";
        $queryExtraJoin = ", course_review ";
        $queryExtraJoinWhere = " AND course.id = course_review.course_id ";
        $queryExtraSelect = " , course_review.level level ";
    } else {
        $runQuery = false; // left the rest of the code fail safely
    }
}

$courses = array();

if ($runQuery) {
    Database::get()->queryFunc("SELECT course.code k,
                               course.public_code c,
                               course.title i,
                               course.visible visible,
                               course.prof_names t,
                               course.id id
                               $queryExtraSelect
                          FROM course, course_department $queryExtraJoin
                         WHERE course.id = course_department.course
                           $queryExtraJoinWhere
                           AND course_department.department = ?d
                           AND course.visible != ?d
                           $queryCourseIds
                      ORDER BY course.title, course.prof_names", function ($course) use (&$courses) {
        $courses[] = $course;
    }, $fc, COURSE_INACTIVE );
}

if (count($courses) > 0) {

    $tool_content .= "
        <table width='100%' class='tbl_border'>
        <tr>
            <th class='left' colspan='2'>" . q($m['lessoncode']) . "</th>";

    if ($isInOpenCoursesMode) {
        $tool_content .= "
                <th class='left' width='220'>" . q($m['professor']) . "</th>
                <th width='30'>$langOpenCoursesLevel</th>";
    } else {
        $tool_content .= "
                <th class='left' width='200'>" . q($m['professor']) . "</th>
                <th width='30'>$langType</th>";
    }

    $tool_content .= "</tr>";

    $k = 0;    
    foreach ($courses as $mycours) {
        if ($mycours->visible == 2) {
            $codelink = "<a href='../../courses/" . urlencode($mycours->k) . "/'>" . q($mycours->i) . "</a>&nbsp;<small>(" . q($mycours->c) . ")</small>";
        } else {
            $codelink = q($mycours->i) . "&nbsp;<small>(" . q($mycours->c) . ")</small>";
        }

        if ($k % 2 == 0) {
            $tool_content .= "<tr class='even'>";
        } else {
            $tool_content .= "<tr class='odd'>";
        }

        $tool_content .= "<td width='16'><img src='$themeimg/arrow.png' title='bullet'></td>";
        $tool_content .= "<td>" . $codelink . "</td>";
        $tool_content .= "<td>" . q($mycours->t) . "</td>";
        $tool_content .= "<td align='center'>";

        if ($isInOpenCoursesMode) {
            // metadata are displayed in click-to-open modal dialogs
            $metadata = CourseXMLElement::init($mycours->id, $mycours->k);
            $tool_content .= "\n" . CourseXMLElement::getLevel($mycours->level) .
                    "<div id='modaldialog-" . $mycours->id . "' class='modaldialog' title='$langCourseMetadata'>" .
                    $metadata->asDiv() . "</div>
                <a href='javascript:modalOpen(\"#modaldialog-" . $mycours->id . "\");'>" .
                    "<img src='${themeimg}/lom.png'/></a>";
        } else {
            // show the necessary access icon
            foreach ($icons as $visible => $image) {
                if ($visible == $mycours->visible) {
                    $tool_content .= $image;
                }
            }
        }
        $tool_content .= "</td>";
        $tool_content .= "</tr>";
        $k++;
    }
    $tool_content .= "</table>";
} else {
    $subTrees = $tree->buildSubtrees(array($fc));
    if (count($subTrees) <= 1) { // is leaf
        $tool_content .= "<p class='alert1'>" . $m['nolessons'] . "</p>";
    }
}

if ($isInOpenCoursesMode) {
    load_js('jquery');
    load_js('jquery-ui');
    $head_content .= <<<EOF
<script type='text/javascript'>
/* <![CDATA[ */

    var modalOpen = function(id) {
        $(id).dialog( "open" );
    };

    $(document).ready(function(){
        $( ".cmetaaccordion" ).accordion({
            collapsible: true,
            active: false
        });

        $( ".tabs" ).tabs();

        $( ".modaldialog" ).dialog({
            autoOpen: false,
            modal: true,
            height: 600,
            width: 600,
            open: function() {
                $( ".ui-widget-overlay" ).on('click', function() {
                    $( ".modaldialog" ).dialog('close');
                });
            }
        });
    });

/* ]]> */
</script>
<style type="text/css">
.ui-widget {
    font-family: "Trebuchet MS",Tahoma,Arial,Helvetica,sans-serif;
    font-size: 13px;
}

.ui-widget-content {
    color: rgb(119, 119, 119);
}
</style>
EOF;
}


draw($tool_content, (isset($uid) and $uid) ? 1 : 0, null, $head_content);
