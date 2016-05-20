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

function get_total_webconf_servers()
{
    $total = 0;

    if (get_config('ext_webconf_enabled')) {
        $total = Database::get()->querySingle("SELECT COUNT(*) AS count FROM wc_servers WHERE enabled='true'")->count;
    }

    return $total;
}

function create_webconf_jnlp_file($meeting_id)
{
    global $webDir;
    $jnlp_file = $webDir.'/modules/bbb/webconf/rooms/'.$meeting_id.'.jnlp';
    
    //TO BE BETTER IMPLEMENTED
    $screenshare_server = Database::get()->querySingle("SELECT * FROM wc_servers WHERE enabled='true' ORDER BY id DESC LIMIT 1")->screenshare;
    //
            
    $file = fopen($jnlp_file,"w");
    echo fwrite($file,
                "<?xml version='1.0' encoding='utf-8'?>
                <jnlp spec='1.0+' codebase='http://delos.uoa.gr/opendelos/resources/screencast/' >
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
                    <argument>".$screenshare_server."</argument> 
                    <argument>screenshare</argument> 
                    <argument>1935</argument> 
                    <argument>$meeting_id</argument> 
                    <argument>flashsv1</argument>
                </application-desc> 
            </jnlp>");
    fclose($file);
    
}