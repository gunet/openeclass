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


/*===========================================================================
 validateXML.php
@last update: 03-12-2013 by Sakis Agorastos
@author list: Sakis Agorastos <th_agorastos@hotmail.com>

==============================================================================
@Description: This script returns a flat string with the first XML error when 
libxml_display_errors() is called. Useful for XSD validations.
==============================================================================
*/

function libxml_display_error($error)
{
    switch ($error->level) {
    	case LIBXML_ERR_WARNING:
    	    $return .= "<b>Warning $error->code</b>: ";
    	    break;
    	case LIBXML_ERR_ERROR:
    	    $return .= "<b>Error $error->code</b>: ";
    	    break;
    	case LIBXML_ERR_FATAL:
    	    $return .= "<b>Fatal Error $error->code</b>: ";
    	    break;
    }
    $return .= trim($error->message);
    /* if ($error->file) {
        $return .=    " in <b>manifest.xml</b>";
    } */
    $return .= " on line <b>$error->line</b>\n";

    return $return;
}

function libxml_display_errors() {
    $errors = libxml_get_errors();
    $messages = "";
    foreach ($errors as $error) {
        $messages .= libxml_display_error($error);
        break; //return only the first error message. if you remove this, then all errors are returned.
    }
    
    return $messages;
    
    libxml_clear_errors();
}


?>