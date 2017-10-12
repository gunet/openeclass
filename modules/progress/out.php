<?php

/* ========================================================================
 * Open eClass 
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
 * ======================================================================== 
 */

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'functions.php';
require_once 'process_functions.php';

if (isset($_GET['i'])) {
    $i = $_GET['i'];
    $sql = Database::get()->querySingle("SELECT cert_id, cert_title, cert_issuer, user_fullname, UNIX_TIMESTAMP(assigned) AS cert_date, expires "
                                        . "FROM certified_users WHERE identifier = ?s", $i);
    if ($sql) {
        $username = $sql->user_fullname;
        $certificate_id = $sql->cert_id;
        $certificate_title = $sql->cert_title;
        $certificate_issuer = $sql->cert_issuer;
        $certificate_date = claro_format_locale_date($dateFormatLong, $sql->cert_date);        
        $certificate_expiration_date = $sql->expires;        
        if (!is_null($certificate_expiration_date) and $certificate_expiration_date < date('Y-m-d H:i:s')) {
            echo "<div align='center'><h3>Το πιστοποιητικό που είχατε αποκτήσει έχει λήξει!</h3></div>";
        } else {
            cert_output_to_pdf($certificate_id, $username, $certificate_title, $certificate_issuer, $certificate_date, $i);
        }
    }
}