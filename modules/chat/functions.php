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
 * @brief checks if user has chat access permissions
 * @global type $is_editor
 * @param type $uid
 * @param type $conference_id
 * @param type $conference_status
 * @return boolean
 */
function is_valid_chat_user($uid, $conference_id, $conference_status) {
    
    global $is_editor;
    
    if ($is_editor) {
        return TRUE;
    } else {
        if ($conference_status == 'inactive') {
            return FALSE;
        } else {
            $c_users = Database::get()->querySingle("SELECT user_id FROM conference WHERE conf_id = ?d", $conference_id)->user_id;
            if ($c_users == 0) { // all users
                return TRUE;
            } else { // check if we're in list of chat users
                $chat_users = explode(',', $c_users);
                return in_array($uid, $chat_users);
            }
        }
    }
}