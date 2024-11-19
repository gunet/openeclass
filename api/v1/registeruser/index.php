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

function api_method($access) {

    if ( isset($_GET['username']) && isset($_GET['password']) && isset($_GET['email'])) {

        $username = $_GET['username'];
        $password = $_GET['password'];
        $password_encrypted = password_hash($password, PASSWORD_DEFAULT);
        $email = $_GET['email'];
        $registered_at = DBHelper::timeAfter();
        $expires_at = DBHelper::timeAfter(get_config('account_duration'));

        if ( !valid_email($email)) {
            header('Content-Type: application/json');
            echo json_encode('Not valid email', JSON_UNESCAPED_UNICODE);
            exit();
        }



        $result = array();

        $result['username'] = $username;
        $result['password'] = $password_encrypted;
        $result['email'] = $email;
        $result['registered_at'] = $registered_at;
        $result['expires_at'] = $expires_at;


        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit();
    }

}


chdir('..');
//require_once 'index.php';
require_once 'apiCall.php';
