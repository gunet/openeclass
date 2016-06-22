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

/**
 * @brief create jnlp file
 * @global type $webDir
 * @global type $course_code
 * @param type $meeting_id
 */
function create_webconf_jnlp_file($meeting_id)
{
    global $webDir, $course_code, $urlServer;
    
    if (!file_exists("$webDir/courses/$course_code/rooms/")) {
        make_dir("$webDir/courses/$course_code/rooms/");
    }
       
    $jnlp_file = $webDir.'/courses/'.$course_code.'/rooms/'.$meeting_id.'.jnlp';
    $jar_file = $urlServer.'modules/tc/webconf/screenshare.jar';
    
    $file = fopen($jnlp_file,"w");    
    fwrite($file,
                "<?xml version='1.0' encoding='utf-8'?>
                <jnlp spec='1.0+' codebase='".$jar_file."'>
                    <information>
                        <title>Delos ScreenShare</title>
                        <vendor>Dele Olajide</vendor>
                        <homepage>http://code.google.com/p/red5screnshare/</homepage>
                        <description>Delos ScreenShare</description>
                        <description kind='short'>An Open Source Screen Share Java application for Adobe Flash</description>
                        <offline-allowed/>
                    </information>
                <security>
                    <all-permissions/>
                </security>
                <resources>
                    <j2se version='1.4+'/>
                    <jar href='screenshare.jar'/>
                </resources>
                <application-desc main-class='org.redfire.screen.ScreenShare'>
                    <argument>".$jar_file."</argument> 
                    <argument>screenshare</argument> 
                    <argument>1935</argument> 
                    <argument>$meeting_id</argument> 
                    <argument>flashsv1</argument>
                </application-desc> 
            </jnlp>");
    fclose($file);   
}