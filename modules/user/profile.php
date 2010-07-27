<?php
/*
 * Open eClass 2.4 - E-learning and Course Management System
 * ========================================================================
 * Copyright(c) 2010  Greek Universities Network - GUnet
 *
 * User Profile
 *
 */

$require_login = true;
$require_help = TRUE;
$helpTopic = 'User';

include '../../include/baseTheme.php';

$nameTools = $langUserProfile;

if (!isset($_GET['id'])) {
        redirect_to_home_page();
}
$id = intval($_GET['id']);

$q = db_query("SELECT user_id, nom, prenom, email FROM user WHERE user_id = $id");

if (!$q or mysql_num_rows($q) == 0) {
        redirect_to_home_page();
}

$tool_content = display_user(mysql_fetch_array($q), true);

draw($tool_content, 1);
