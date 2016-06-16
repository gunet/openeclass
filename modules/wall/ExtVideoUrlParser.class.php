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

class ExtVideoUrlParser
{
    public static function getYoutubePatterns() {
        return array('/youtube\.com\/v\/([^&^\?]+)/i',
                '/youtube\.com\/watch\?v=([^&]+)/i',
                '/youtube\.com\/embed\/([^&^\?]+)/i',
                '/youtu\.be\/([^&^\?]+)/i');
    }
    
    public static function getVimeoPatterns() {
        return array('/https:\/\/vimeo\.com\/([^&^\?]+)/i',
                '/player\.vimeo\.com\/video\/([^&^\?]+)/i');
    }
	
    public static function validateUrl($url) {
        foreach (self::getYoutubePatterns() as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }

        foreach (self::getVimeoPatterns() as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }
        
        return false;
    }
    
	public static function get_embed_url($url) {
	    $matches = null;
	    
	    foreach (self::getYoutubePatterns() as $pattern) {
	        if (preg_match($pattern, $url, $matches)) {
	            $sanitized = strip_tags($matches[1]);
	            return array('youtube','https://www.youtube.com/embed/'.$sanitized);
	        }
	    }
	    
	    foreach (self::getVimeoPatterns() as $pattern) {
	        if (preg_match($pattern, $url, $matches)) {
	            $sanitized = strip_tags($matches[1]);
	            return array('vimeo','https://player.vimeo.com/video/' . $sanitized . '?color=00ADEF&fullscreen=1');
	        }
	    }
	    
	    return null;
	}
}
 ?>