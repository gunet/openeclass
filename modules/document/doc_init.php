<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

// Subsystems
define('MAIN', 0);
define('GROUP', 1);
define('EBOOK', 2);

$can_upload = $is_adminOfCourse;
if (defined('GROUP_DOCUMENTS')) {
        include '../group/group_functions.php';
        $action->record('MODULE_ID_GROUPS');
	$subsystem = GROUP;
        initialize_group_id('gid');
        initialize_group_info($group_id);
        $subsystem_id = $group_id;
        $navigation[] = array ('url' => 'group.php', 'name' => $langGroups);
        $navigation[] = array ('url' => 'group_space.php?group_id=' . $group_id, 'name' => q($group_name));
        $groupset = "gid=$group_id&amp;";
        $base_url = $_SERVER['PHP_SELF'] . '?' . $groupset;
        $group_sql = "course_id = $cours_id AND subsystem = $subsystem AND subsystem_id = $subsystem_id";
        $group_hidden_input = "<input type='hidden' name='gid' value='$group_id' />";
        $basedir = $webDir . 'courses/' . $currentCourseID . '/group/' . $secret_directory;
	$can_upload = $can_upload || $is_member;
        $nameTools = $langGroupDocumentsLink;
} elseif (defined('EBOOK_DOCUMENTS')) {
	$subsystem = EBOOK;
        $subsystem_id = $ebook_id;
        $group_sql = "course_id = $cours_id AND subsystem = $subsystem AND subsystem_id = $subsystem_id";
        $basedir = $webDir . 'courses/' . $currentCourseID . '/ebook/' . $ebook_id;
} else {
        $action->record('MODULE_ID_DOCS');
	$subsystem = MAIN;
        $base_url = $_SERVER['PHP_SELF'] . '?';
        $subsystem_id = 'NULL';
        $groupset = '';
        $group_sql = "course_id = $cours_id AND subsystem = $subsystem";
        $group_hidden_input = '';
        $basedir = $webDir . 'courses/' . $currentCourseID . '/document';
        $nameTools = $langDoc;
}
mysql_select_db($mysqlMainDb);