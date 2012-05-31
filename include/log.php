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


class Log {
        
        public static function record($module_id, $details, $action_type) {              
                
                global $course_id;
                
                echo "Now Loggin....";
                echo "<br />";                        
                echo "INSERT INTO log SET 
                        user_id = $_SESSION[uid],
                        course_id = $course_id,
                        module_id = $module_id,
                        details = '$details',
                        action_type = '$action_type',
                        ts = NOW(),
                        ip = '$_SERVER[SERVER_ADDR]'";                
        }
        
        public static function display($module_id, $date_from, $date_now) {
                
                global $course_id;
                
                echo "Now displaying...";
                echo "<br />";
                echo "SELECT details, action_type 
                        FROM log
                        WHERE ts BETWEEN $date_from AND $date_now";
                
        }        
}

?>
