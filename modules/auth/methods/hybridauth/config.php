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

		$yahoo_key = $yahoo_secret = $google_id = $google_secret = $facebook_id = $facebook_secret = $twitter_key = $twitter_secret = $live_id = $live_secret = $linkedin_key = $linkedin_secret = '';
		$yahoo_enabled = $google_enabled = $google_enabled = $facebook_enabled = $twitter_enabled = $live_enabled = $linkedin_enabled = false;
		$tmp = array();
		
		$q = Database::get()->queryArray("SELECT auth_id, auth_name, auth_title, auth_settings, auth_instructions, auth_default, auth_enabled FROM auth WHERE 1");
		if ($q) {
			foreach ($q as $row) {
				if(!$row->auth_settings) continue;
				// get only those with valid, not empty settings
				switch($row->auth_name){
					case "yahoo":
						$tmp = explode('|', $row->auth_settings);
						$yahoo_key = $tmp[0];
						$yahoo_secret = $tmp[1];
						$yahoo_enabled = boolval($row->auth_enabled);
						break;
					case "google":
						list($google_id, $google_secret) = explode('|', $row->auth_settings);
						$google_enabled = boolval($row->auth_enabled);
						break;
					case "facebook":
						list($facebook_id, $facebook_secret) = explode('|', $row->auth_settings);
						$facebook_enabled = boolval($row->auth_enabled);
						break;
					case "twitter":
						list($twitter_key, $twitter_secret) = explode('|', $row->auth_settings);
						$twitter_enabled = boolval($row->auth_enabled);
						break;
					case "live":
						list($live_id, $live_secret) = explode('|', $row->auth_settings);
						$live_enabled = boolval($row->auth_enabled);
						break;
					case "linkedin":
						list($linkedin_key, $linkedin_secret) = explode('|', $row->auth_settings);
						$linkedin_enabled = boolval($row->auth_enabled);
						break;
				}
			}
		}
	
	//return configuration data as an array
	return 
		array(
			"base_url" => $GLOBALS['urlServer'] . "/modules/auth/methods/hybridauth/", 
	
			"providers" => array ( 
				// openid providers
				"OpenID" => array (
					"enabled" => false
				),
	
				"Yahoo" => array ( 
					"enabled" => $yahoo_enabled,
					"keys"    => array ( "key" => $yahoo_key, "secret" => $yahoo_secret )
				),
	
				"Google" => array ( 
					"enabled" => $google_enabled,
					"keys"    => array ( "id" => $google_id, "secret" => $google_secret )
				),
	
				"Facebook" => array ( 
					"enabled" => $facebook_enabled,
					"keys"    => array ( "id" => "$facebook_id", "secret" => "$facebook_secret" )
				),
	
				"Twitter" => array ( 
					"enabled" => $twitter_enabled,
					"keys"    => array ( "key" => $twitter_key, "secret" => $twitter_secret ) 
				),
	
				"Live" => array ( 
					"enabled" => $live_enabled,
					"keys"    => array ( "id" => $live_id, "secret" => $live_secret ) //does not work locally!
				),
	
				"LinkedIn" => array ( 
					"enabled" => $linkedin_enabled,
					"keys"    => array ( "key" => $linkedin_key, "secret" => $linkedin_secret ) 
				),
	
			),
	
			// if you want to enable logging, set 'debug_mode' to true  then provide a writable file by the web server on "debug_file"
			"debug_mode" => false,
	
			"debug_file" => "/log.txt"
		);
	
	}
}