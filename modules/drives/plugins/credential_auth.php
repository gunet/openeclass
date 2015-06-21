<?php

/* ========================================================================
 * Open eClass 
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
 * ======================================================================== 
 */

session_start();
require_once '../clouddrive.php';
$drive = CloudDriveManager::getSessionDrive();

$url = trim(addslashes(array_key_exists('url', $_POST) ? $_POST['url'] : $drive->getDefaultURL()));
$username = trim(addslashes(array_key_exists('username', $_POST) ? $_POST['username'] : ""));
$password = array_key_exists('password', $_POST) ? $_POST['password'] : "";
$username_value = ($username == "")? "value='' placeholder='Username'" : "value='$username'";

if ($drive->checkCredentials($url, $username, $password)) {
    header('Location: ' . '../popup.php?' . $drive->getDriveDefaultParameter() . "&" . $drive->getCallbackName() . '=' . $drive->encodeCredentials($url, $username, $password));
    die();
}

echo '<head>';
echo '<title>User Login</title>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '<link rel="stylesheet" href="../../../template/default/CSS/bootstrap-custom.css?v=3.1">';
echo '</head>';

echo '<body style="background-color: #efefef; padding-top:30px;">';
echo '<div class="container">';
echo '<div class="col-xs-12">';

echo '<h4 class="text-center"> - Login Form - </h4><br>';

if ($username || $password) {
    echo '<div class="alert alert-warning">Unable to login with given credentials</div>';
}

echo '<form action="credential_auth.php?' . $drive->getDriveDefaultParameter() . '" method="POST">';

echo '<div class="form-group">';
echo '<input type="url" class="form-control text-center" id="url" name="url" value="' . $url . '">';
echo '</div>';

echo '<div class="form-group">';
echo '<input type="text" class="form-control text-center" id="username" name="username" '.$username_value.'>';
echo '</div>';

echo '<div class="form-group">';
echo '<input type="password" class="form-control text-center" id="password" name="password" placeholder="Password">';
echo '</div>';

echo '<div class="form-group">';
echo '<input type="submit" class="btn btn-primary btn-block" value="Submit">';
echo '</div>';

echo '</form>';
echo '</div>';
echo '</div>';
echo '</body>';
