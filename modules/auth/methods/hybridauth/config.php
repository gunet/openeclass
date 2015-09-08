<?php

/* ========================================================================
 * Open eClass 3.0
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
* ======================================================================== */

/* ===========================================================================
 config.php
 authors list: Sakis Agorastos <th_agorastos@hotmail.com>
==============================================================================
@Description: HybridAuth Configuration settings

This script is a modified version of the HybridAuth Config file. This version
fetches the provider settings from the open eclass db instead of reading them
from a simple text file.

==============================================================================
*/
/*!
* HybridAuth 2.1.2
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
*/

// ----------------------------------------------------------------------------------------
//	HybridAuth Config file: http://hybridauth.sourceforge.net/userguide/Configuration.html
// ----------------------------------------------------------------------------------------
if (!function_exists('get_hybridauth_config')) {
	function get_hybridauth_config() {
        $providers = array (
            // openid providers
            'OpenID' => array ('enabled' => false));

        $q = Database::get()->queryArray("SELECT * FROM auth
            WHERE auth_name IN ('yahoo', 'google', 'facebook', 'twitter', 'live', 'linkedin')");
		if ($q) {
			foreach ($q as $row) {
                $name = $row->auth_name == 'linkedin' ? 'LinkedIn' : ucfirst($row->auth_name);
                if ($row->auth_default and !empty($row->auth_settings)) {
                    $providers[$name]['keys'] = unserialize($row->auth_settings);
                    $providers[$name]['enabled'] = true;
                    if ($name == 'Facebook') {
                        $providers[$name]['scope'] = 'public_profile, email';
                    }
                } else {
                    $providers[$name]['keys'] = array();
                    $providers[$name]['enabled'] = true;
                }
			}
        }
	
        // return configuration data as an array
        return array(
            'base_url' => $GLOBALS['urlServer'] . 'modules/auth/methods/hybridauth/', 
            'providers' => $providers,
            // if you want to enable logging, set 'debug_mode' to true then provide a writable file by the web server on 'debug_file'
            'debug_mode' => false,
            'debug_file' => '/log.txt');
    }
}
