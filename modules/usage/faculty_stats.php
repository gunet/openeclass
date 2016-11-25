<?php
$require_admin = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'modules/admin/hierarchy_validations.php';

load_js('tools.js');
load_js('bootstrap-datetimepicker');

$head_content .= "<script type='text/javascript'>
        $(function() {
            $('#user_date_start, #user_date_end').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true
            });
        });
    </script>";

$tree = new Hierarchy();
$user = new User();

$toolName = $langStatOfFaculty;
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);
$navigation[] = array("url" => "index.php?t=a", "name" => $langUsage);

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => "index.php?t=a",
        'icon' => 'fa-reply',
        'level' => 'primary-label')));

if (isset($_GET['user_date_start'])) {
    $uds = DateTime::createFromFormat('d-m-Y H:i', $_GET['user_date_start']);
    $u_date_start = $uds->format('Y-m-d H:i');
    $user_date_start = $uds->format('d-m-Y H:i');
} else {
    $date_start = new DateTime();
    $date_start->sub(new DateInterval('P2Y'));
    $u_date_start = $date_start->format('Y-m-d H:i');
    $user_date_start = $date_start->format('d-m-Y H:i');
}
if (isset($_GET['user_date_end'])) {
    $ude = DateTime::createFromFormat('d-m-Y H:i', $_GET['user_date_end']);
    $u_date_end = $ude->format('Y-m-d H:i');
    $user_date_end = $ude->format('d-m-Y H:i');
} else {
    $date_end = new DateTime();
    $date_start->sub(new DateInterval('P1M'));
    $u_date_end = $date_end->format('Y-m-d H:i');
    $user_date_end = $date_end->format('d-m-Y H:i');
}

if (isset($_GET['stats_submit'])) {
    if (isset($_GET['formsearchfaculte'])) {
        $searchfaculte = getDirectReference($_GET['formsearchfaculte']);
        if ($searchfaculte) {
            $subs = $tree->buildSubtrees(array($searchfaculte));
            $ids = 0;
            foreach ($subs as $key => $id) {
                $terms[] = $id;
                $ids++;
            }
            $query = ' AND hierarchy.id IN (' . implode(', ', array_fill(0, $ids, '?d')) . ')';
        } else {
            $query = $terms = '';
        }
    }

    // only one course
    if (isset($_GET['c'])) {
        $tool_content .= "<div class='row'><div class='col-xs-12'>";
        $name = Database::get()->querySingle("SELECT name FROM hierarchy, course, course_department WHERE hierarchy.id = course_department.department
                                         AND course_department.course = course.id AND course.id = ?d", $_GET['c'])->name;
        $code = course_id_to_code(intval($_GET['c']));
        $course = Database::get()->querySingle("SELECT title, prof_names, code, visible FROM course WHERE id = ?d", $_GET['c']);
        $users = Database::get()->querySingle("SELECT COUNT(user_id) AS users FROM course_user WHERE course_id = ?d", $_GET['c'])->users;

        $tool_content .= "<div class='panel panel-default'>
                             <div class='panel-body'>
                                <div class='inner-heading'><strong>" . $tree->unserializeLangField($name) . "</strong></div>
                                <div class='row'>
                                   <div class='col-sm-6'>
                                   <dl>
                                      <dt>$langTitle :</dt>
                                      <dd>$course->title <small>($course->code)</small></dd></dl><dl>
                                      <dt>$langTeacher :</dt>
                                      <dd>$course->prof_names</dd>
                                   </dl>
                                   </div>
                                   <div class='col-sm-6'>
                                   <dl>
                                      <dt>$langCourseVis :</dt>
                                      <dd>" . course_status_message($_GET['c']) . "</dd></dl><dl>
                                      <dt>$langUsers :</dt>
                                      <dd>$users</dd>
                                   </dl>
                                   </div>
                                </div>
                             </div>
                          </div>";

        // user registrations per month
        $tool_content .= "<div class='table-responsive'><table class='table-default'>";
        $tool_content .= "<tr class='list-header'><th class='col-xs-8'>$langMonth</th><th class='col-xs-4'>$langMonthlyCourseRegistrations</th></tr>";
        $q2 = Database::get()->queryArray("SELECT COUNT(*) AS registrations, MONTH(reg_date) AS month, YEAR(reg_date) AS year FROM course_user
                            WHERE course_id = ?d AND (reg_date BETWEEN '$u_date_start' AND '$u_date_end')
                                AND status = " . USER_STUDENT . " GROUP BY month, year ORDER BY year, month ASC", $_GET['c']);
        foreach ($q2 as $data) {
            $tool_content .= "<tr><td>$data->month-$data->year</td><td>$data->registrations</td></tr>";
        }
        $tool_content .= "</table></div>";

        // visits per month
        $tool_content .= "<div class='table-responsive'><table class='table-default'>";
        $tool_content .= "<tr class='list-header'><th class='col-xs-6'>$langMonth</th><th class='col-xs-2'>$langVisits</th><th class='col-xs-2'>$langUsers</th></tr>";
        $q1 = Database::get()->queryArray("SELECT MONTH(day) AS month, YEAR(day) AS year, COUNT(*) AS visits, COUNT(DISTINCT user_id) AS users FROM actions_daily
                        WHERE (day BETWEEN '$u_date_start' AND '$u_date_end') AND course_id = ?d GROUP BY month,year ORDER BY year, month ASC", $_GET['c']);
        $total_visits = $total_users = 0;
        foreach ($q1 as $data) {
            $tool_content .= "<tr><td>$data->month-$data->year</td><td>$data->visits</td><td>$data->users</td></tr>";
            $total_visits += $data->visits;
            $total_users += $data->users;
        }
        $tool_content .= "<tr><td><h5>$langTotal</h5></td><td><h5>$total_visits</h5></td><td><h5>$total_users</h5></td></tr>";
        $tool_content .= "</table></div>";

        // visits per module per month
        $tool_content .= "<div class='table-responsive'><table class='table-default'>";
        $tool_content .= "<tr class='list-header'><th class='col-xs-6'>$langModule</th><th class='col-xs-2'>$langVisits</th><th class='col-xs-2'>$langUsers</th></tr>";
        $q3 = Database::get()->queryArray("SELECT COUNT(*) AS cnt, module_id, COUNT(DISTINCT user_id) AS users FROM actions_daily
                        WHERE (day BETWEEN '$u_date_start' AND '$u_date_end') AND course_id = ?d
                        GROUP BY module_id", $_GET['c']);
        foreach ($q3 as $data) {
            if ($data->module_id > 0) {
                if ($data->module_id == MODULE_ID_UNITS) { // course_units
                    $mod_id = $static_modules[$data->module_id];
                } else {
                    $mod_id = $modules[$data->module_id];
                }
                $tool_content .= "<tr>";
                $tool_content .= "<td>$mod_id[title]</td><td>$data->cnt</td><td>$data->users</td>";
                $tool_content .= "</tr>";
            }
        }
        $tool_content .= "</table></div></div>";
    } else {
        // courses list
        $tool_content .= "<div class='table-responsive'>";
        $tool_content .= "<h4 class='text-center'>" . $tree->getNodeName($searchfaculte) . "</h4>";
        if (!empty($query)) {
            $s = Database::get()->querySingle("SELECT COUNT(*) AS total FROM course, course_department, hierarchy
                                            WHERE course.id = course_department.course
                                            AND hierarchy.id = course_department.department
                                            $query", $terms)->total;
        } else { // get all courses
            $s = Database::get()->querySingle("SELECT COUNT(*) AS total FROM course, course_department, hierarchy
                                            WHERE course.id = course_department.course
                                            AND hierarchy.id = course_department.department")->total;
        }
        $all = Database::get()->querySingle("SELECT COUNT(*) AS num_of_courses FROM course")->num_of_courses;
        $tool_content .= "<div class='panel'><div class='panel-body'><p class='text-center'>$s $langCourses ($langFrom2 $all $langSumFrom $siteName)</p></div></div>";

        // division info
        /*$tool_content .= "<table class='table table-striped table-bordered table-condensed'>";
        $tool_content .= "<tr class='success'><th class='col-xs-9'>Τομείς</th><th class='col-xs-3'>Μαθήματα</th></tr>";
        $qf = db_query("SELECT id, name FROM division WHERE faculte_id = 19 ORDER BY id");
        while ($f = mysql_fetch_array($qf)) {
                $division = db_query_get_single_value("SELECT COUNT(*) FROM cours WHERE division_id = '$f[id]'");
                $tool_content .= "<tr><td>$f[name]</td><td>$division</td></tr>";
        }
        $tool_content .= "</table>"; */
        $tool_content .= "<div class='table-responsive'><table class='table-default'>";
        $tool_content .= "<tr class='list-header'><th class='col-xs-3'>$langCourse - $langCode</th>
                                              <th class='col-xs-4'>$langTeacher</th>
                                              <th class='col-xs-3'>$langCreationDate</th>
                                              <th class='col-xs-1'>$langActions</th>";
        if (!empty($query)) {
            $sql = Database::get()->queryArray("SELECT course.id, course.code, course.visible, title, prof_names, DATE_FORMAT(created, '%d-%m-%Y %h:%m') AS creation_time
                                            FROM course, course_department, hierarchy
                                                WHERE course.id = course_department.course
                                                AND hierarchy.id = course_department.department $query
                                                ORDER by creation_time DESC", $terms);
        } else { // get all courses
            $sql = Database::get()->queryArray("SELECT course.id, course.code, course.visible, title, prof_names, DATE_FORMAT(created, '%d-%m-%Y %h:%m') AS creation_time
                                FROM course, course_department, hierarchy
                                    WHERE course.id = course_department.course
                                    AND hierarchy.id = course_department.department
                                    ORDER by creation_time DESC");
        }
        foreach ($sql as $data) {
            $tool_content .= "<tr>
            <td><a href='$_SERVER[SCRIPT_NAME]?c=$data->id&amp;user_date_start=$user_date_start&amp;user_date_end=$user_date_end&amp;stats_submit=true'>$data->title</a><br/><small>($data->code)</small></td>
            <td>$data->prof_names</td>
            <td>$data->creation_time</td><td class='text-center'>". action_button(array(
                    array('title' => $langCsv,
                        'url' => "faculty_stats_csv.php?c=$data->id&amp;user_date_start=$u_date_start&amp;user_date_end=$u_date_end",
                        'icon' => 'fa-file-excel-o'),
                    array('title' => "$langCsv ($langcsvenc2)",
                        'url' => "faculty_stats_csv.php?c=$data->id&amp;user_date_start=$u_date_start&amp;user_date_end=$u_date_end&amp;enc=UTF-8",
                        'icon' => 'fa-file-excel-o')
                    ),

                    array(
                        'secondary_icon' => 'fa-download'))  ."
                            </td></tr>";
        }
        $tool_content .= "</table></div>";
    }
    $tool_content .= "</div>";
} else { // display form

    load_js('jstree3');
    $tool_content .= "<div class='form-wrapper'>
                        <form role='form' class='form-horizontal' action='$_SERVER[SCRIPT_NAME]' method='get'>
                    <fieldset>";
    $tool_content .= "<div class='form-group'><label class='col-sm-2 control-label'>$langFaculty:</label>";
    $tool_content .= "<div class='col-sm-10'>";
    if (isDepartmentAdmin()) {
        list($js, $html) = $tree->buildNodePickerIndirect(array('params' => 'name="formsearchfaculte"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false, 'allowables' => $user->getDepartmentIds($uid)));
    } else {
        list($js, $html) = $tree->buildNodePickerIndirect(array('params' => 'name="formsearchfaculte"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false));
    }

    $head_content .= $js;
    $tool_content .= $html;
    $tool_content .= "</div></div>";

    $tool_content .= "<div class='input-append date form-group' data-date = '" . q($user_date_start) . "' data-date-format='dd-mm-yyyy'>
    <label class='col-sm-2 control-label' for='user_date_start'>$langStartDate:</label>
        <div class='col-xs-10 col-sm-9'>
            <input class='form-control' name='user_date_start' id='user_date_start' type='text' value = '" . q($user_date_start) . "'>
        </div>
        <div class='col-xs-2 col-sm-1'>
            <span class='add-on'><i class='fa fa-times'></i></span>
            <span class='add-on'><i class='fa fa-calendar'></i></span>
        </div>
        </div>";
    $tool_content .= "<div class='input-append date form-group' data-date= '" . q($user_date_end) . "' data-date-format='dd-mm-yyyy'>
        <label class='col-sm-2 control-label' for='user_date_end'>$langEndDate:</label>
            <div class='col-xs-10 col-sm-9'>
                <input class='form-control' id='user_date_end' name='user_date_end' type='text' value= '" . q($user_date_end) . "'>
            </div>
        <div class='col-xs-2 col-sm-1'>
            <span class='add-on'><i class='fa fa-times'></i></span>
            <span class='add-on'><i class='fa fa-calendar'></i></span>
        </div>
        </div>";
    $tool_content .= "<div class='form-group'>
                        <div class='col-sm-10 col-sm-offset-2'>
                            <input class='btn btn-primary' type='submit' name='stats_submit' value='$langSubmit'>
                            <a href='index.php' class='btn btn-default'>$langCancel</a>
                        </div>
          </div>";
    $tool_content .= "</fieldset></form></div>";
}

draw($tool_content, 3, null, $head_content);
