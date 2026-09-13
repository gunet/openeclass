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

/*
 * Session-expired sentinel for the mobile app WebView.
 *
 * When a mobile app WebView request hits an expired session, the auth layer redirects here
 * (mlogin.php for the login-exchange flow, init.php for an already-open page). The app detects
 * this URL, silently refreshes its token and re-enters, so the user normally never sees this page.
 *
 * The content below is only a fallback for older app versions that do not perform that
 * interception; it is a plain, public, dependency-free page (no auth) that always returns 200.
 */

header('Content-Type: text/html; charset=utf-8');

?><!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Η συνεδρία έληξε</title>
    <style>
        body { margin: 0; font-family: -apple-system, Roboto, Helvetica, Arial, sans-serif;
               background: #f4f6f8; color: #333; display: flex; min-height: 100vh;
               align-items: center; justify-content: center; }
        .box { max-width: 420px; padding: 32px 24px; text-align: center; }
        h1 { font-size: 20px; margin: 0 0 12px; }
        p { font-size: 15px; line-height: 1.5; margin: 0; color: #666; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Η συνεδρία έληξε</h1>
        <p>Επιστρέψτε στην εφαρμογή για να ανανεωθεί η σύνδεσή σας.</p>
    </div>
</body>
</html>
