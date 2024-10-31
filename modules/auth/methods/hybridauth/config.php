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
* HybridAuth 2.6.0
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
            WHERE auth_name IN ('google', 'facebook', 'twitter', 'linkedin', 'Yahoo', 'live')");
		if ($q) {
			foreach ($q as $row) {
                $name = $row->auth_name == 'linkedin' ? 'LinkedIn' : ucfirst($row->auth_name);
		if($name == 'Live'){$name = 'WindowsLive';}
                if ($row->auth_default and !empty($row->auth_settings)) {
                    $providers[$name]['keys'] = unserialize($row->auth_settings);
                    $providers[$name]['enabled'] = true;
                    if ($name == 'Facebook') {
                        $providers[$name]['scope'] = 'public_profile, email';
                    } elseif($name == 'LinkedIn') {
		    	$providers[$name]['scope'] = 'r_liteprofile r_emailaddress';
		    }
                } else {
                    $providers[$name]['keys'] = array();
                    $providers[$name]['enabled'] = true;
                }
			}
        }
	global $auth_ids;
        $activeAuthMethods = get_auth_active_methods();
        foreach ($providers as $provider => $settings) {
            $aid = array_search(strtolower($provider), $auth_ids);
            if($provider === 'WindowsLive') {
                $aid = array_search(strtolower('live'), $auth_ids);
            }
            if (array_search($aid, $activeAuthMethods) === false) {
               $providers[$provider]['enabled'] = false;
            }
        }

        // return configuration data as an array
        return array(
            'callback' => $GLOBALS['urlServer'],
            'providers' => $providers,
            // if you want to enable logging, set 'debug_mode' to true then provide a writable file by the web server on 'debug_file'
            'debug_mode' => false,
            'debug_file' => '/tmp/log.log'
	);
    }
}
