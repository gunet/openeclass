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
 * @brief create join link
 * @param type $meeting_id
 * @param type $username
 * @param type $uid
 * @param type $email
 * @param type $surname
 * @param type $name
 * @param type $moderator
 * @return type
 */
function om_join_user($meeting_id, $username, $uid, $email, $surname, $name, $moderator) {

    global $webDir, $urlServer;
    
    $res = Database::get()->querySingle("SELECT running_at FROM tc_session WHERE meeting_id = ?s",$meeting_id);
    if ($res) {
        $running_server = $res->running_at;
    }

    $res = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id = ?d", $running_server);

    $url = $res->hostname.':'.$res->port;

    $soapUsers = new SoapClient($url.'/'.$res->webapp.'/services/UserService?wsdl');
    $roomService = new SoapClient($url.'/'.$res->webapp.'/services/RoomService?wsdl');

    $rs = array();
    $rs = $soapUsers->getSession();

    $session_id = $rs->return->session_id;
    
    $params = array(
            'SID' => $session_id,
            'username' => $res->username,
            'userpass' => $res->password
        );

    $l = array();
    $l = $soapUsers->loginUser($params);

    // check for user profile image if exists
    $profileimageurl = "courses/userimg/${uid}_256";
    if (file_exists("${webDir}/$profileimageurl.jpg")) {
        $userimage = "${urlServer}/$profileimageurl.jpg";    
    } elseif (file_exists("${webDir}/$profileimageurl.png")) { 
        $userimage = "${urlServer}/$profileimageurl.png";
    } else {       
        $userimage = '';
    }

    $params = array(
            'SID' => $session_id,
            'username' => $username,
            'firstname' => $name,
            'lastname' => $surname,
            'profilePictureUrl' => $userimage,
            'email' => $email,
            'externalUserId' => $uid,
            'externalUserType' => 'openeclass',
            'room_id' => 19,
            'becomeModeratorAsInt' => $moderator,
            'showAudioVideoTestAsInt' => 1
    );

    $rs = array();
    $rs = $soapUsers->setUserObjectAndGenerateRoomHash($params);
    
    return $url.'/'.$res->webapp.'/?secureHash='.$rs->return;
}

/**
 * @brief check if session is running
 * @param type $meeting_id
 * @return boolean
 */
function om_session_running($meeting_id)
{
    $res = Database::get()->querySingle("SELECT running_at FROM tc_session WHERE meeting_id = ?s",$meeting_id);

    if (!isset($res->running_at)) {
        return false;
    }
    $running_server = $res->running_at;

    if (Database::get()->querySingle("SELECT COUNT(*) AS count FROM tc_servers
            WHERE id=?d AND enabled='true'", $running_server)->count == 0) {
        //it means that the server is disabled so session must be recreated
        return false;
    }

    $res = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id=?d", $running_server);
    
    $url = $res->hostname.':'.$res->port;

    $soapUsers = new SoapClient($url.'/'.$res->webapp.'/services/UserService?wsdl');
    $roomService = new SoapClient($url.'/'.$res->webapp.'/services/RoomService?wsdl');

    $rs = array();
    $rs = $soapUsers->getSession();

    $session_id = $rs->return->session_id;
    
    $params = array(
	'SID' => $session_id,
	'username' => $res->username,
	'userpass' => $res->password
    );

    $l = array();
    $l = $soapUsers->loginUser($params);
    
    $params = array(
	'SID' => $session_id,
	'start' => 0,
	'max' => 10000,
	'orderby' => 'name',
	'asc' => true
    );
    
    $rs = $roomService->getRooms($params);
    
    foreach ($rs->return->result as $rr)
    {
        if($rr->name == $meeting_id)
            return true;
    }
    
    return false;
}

/**
 * @brief create Open Meeting Room
 * @global type $course_id
 * @global type $course_code
 * @global $langBBBCreationRoomError
 * @param type $title
 * @param type $meeting_id
 * @param type $record
 */
function create_om_meeting($title, $meeting_id, $record)
{
    global $course_id, $langBBBCreationRoomError, $langBBBConnectionErrorOverload, $course_code;
        
    $run_to = Database::get()->querySingle("SELECT running_at FROM tc_session WHERE meeting_id = ?s", $meeting_id)->running_at;
        
    if (isset($run_to)) {        
        if (!is_om_server_available($run_to)) { // if existing on server is busy try to find next one
            $r = Database::get()->queryArray("SELECT id FROM tc_servers 
                            WHERE `type`= 'om' AND enabled='true' AND id <> ?d ORDER BY weight ASC", $run_to);
            if (($r) and count($r) > 0) {
                foreach ($r as $server) {
                    if (is_om_server_available($server->id)) {
                        $run_to = $server->id;
                        Database::get()->query("UPDATE tc_session SET running_at = ?d WHERE meeting_id = ?s", $run_to, $meeting_id);
                        break;
                    } else {
                        $run_to = -1; // no om server available
                    }
                }
            } else {
                $run_to = -1; // no om server exists
            }
        }
    }
        
    if ($run_to == -1) {
        Session::Messages($langBBBConnectionErrorOverload, 'alert-danger');
        redirect_to_home_page("modules/tc/index.php?course=$course_code");
    } else {
        // we find the om server that will serve the session
        $res = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id=?d AND `type` = 'om'", $run_to);
        
        $url = $res->hostname.':'.$res->port;
        $soapUsers = new SoapClient($url.'/'.$res->webapp.'/services/UserService?wsdl');
        $roomService = new SoapClient($url.'/'.$res->webapp.'/services/RoomService?wsdl');

        $rs = array();
        $rs = $soapUsers->getSession();

        $session_id = $rs->return->session_id;

        $params = array(
            'SID' => $session_id,
            'username' => $res->username,
            'userpass' => $res->password
        );

        $l = array();
        $l = $soapUsers->loginUser($params);

        $params = array(
            'SID' => $session_id,
            'name' => $meeting_id,
            'roomtypes_id' => 1,
            'comment' => $title,
            'numberOfPartizipants' => $users_to_join+20,
            'ispublic' => true,
            'appointment' => false,
            'isDemoRoom' => false,
            'isDemoRoom' => false,
            'demoTime' => '',
            'isModeratedRoom' => true
        );

        $l = $roomService->addRoomWithModeration($params);       
    }
}


/**
 * @brief get Open Meeting Server active rooms
 * @param type $om_server
 * @return int
 */
function get_om_active_rooms($om_server)
{
    $active_rooms = 0;
    $res = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id=?d", $om_server);
    
    $url = $res->hostname.':'.$res->port;

    $soapUsers = new SoapClient($url.'/'.$res->webapp.'/services/UserService?wsdl');
    $roomService = new SoapClient($url.'/'.$res->webapp.'/services/RoomService?wsdl');

    $rs = array();
    $rs = $soapUsers->getSession();

    $session_id = $rs->return->session_id;
    
    $params = array(
	'SID' => $session_id,
	'username' => $res->username,
	'userpass' => $res->password
    );

    $l = array();
    $l = $soapUsers->loginUser($params);
    
    $params = array(
	'SID' => $session_id,
	'start' => 0,
	'max' => 10000,
	'orderby' => 'name',
	'asc' => true
    );
    
    $rs = $roomService->getRooms($params);
    
    foreach ($rs->return->result as $rr)
    {
        $active_rooms += 1;
    }
    
    return $active_rooms;
}

/**
 * @brief get Open Meeting Server connected users
 * @param type $om_server
 * @return type
 */
function get_om_connected_users($om_server)
{
    $connected_users = 0;
    $res = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id=?d", $om_server);
        
    $url = $res->hostname.':'.$res->port;

    $soapUsers = new SoapClient($url.'/'.$res->webapp.'/services/UserService?wsdl');
    $roomService = new SoapClient($url.'/'.$res->webapp.'/services/RoomService?wsdl');

    $rs = array();
    $rs = $soapUsers->getSession();

    $session_id = $rs->return->session_id;
    
    $params = array(
	'SID' => $session_id,
	'username' => $res->username,
	'userpass' => $res->password
    );

    $l = array();
    $l = $soapUsers->loginUser($params);
    
    $params = array(
	'SID' => $session_id,
	'start' => 0,
	'max' => 10000,
	'orderby' => 'name',
	'asc' => true
    );
    
    $rs = $roomService->getRooms($params);
    
    foreach ($rs->return->result as $rr)
    {
        $params = array(
            'SID' => $session_id,
            'roomId' => $rr->id
        );

        $cu = $roomService->getRoomCounters($params);        
        $connected_users++;
    }
    
    return $connected_users;
}


/**
 * @brief check if om server is available
 * @global type $course_id
 * @param type $server_id
 * @return boolean
 */
function is_om_server_available($server_id) {
    
    global $course_id;
    
    //Get all course participants
    $users_to_join = Database::get()->querySingle("SELECT COUNT(*) AS count FROM course_user, user
                                WHERE course_user.course_id = ?d AND course_user.user_id = user.id", $course_id)->count;
    
    $row = Database::get()->querySingle("SELECT id, max_rooms, max_users 
                                    FROM tc_servers WHERE id = ?d AND enabled = 'true'", $server_id);
    if ($row) {
        $max_rooms = $row->max_rooms;
        $max_users = $row->max_users;
        // get connected users
        $connected_users = get_om_connected_users($server_id);
        // get active rooms
        $active_rooms = get_om_active_rooms($server_id);
        //cases
        // max_users = 0 && max_rooms = 0 - UNLIMITED
        // active_rooms < max_rooms && active_users < max_users
        // active_rooms < max_rooms && max_users = 0 (UNLIMITED)
        // active_users < max_users && max_rooms = 0 (UNLIMITED)
        if (($max_rooms == 0 && $max_users == 0) 
            or (($max_users > ($users_to_join + $connected_users)) and $active_rooms < $max_rooms) 
            or ($active_rooms < $max_rooms and $max_users == 0) 
            or (($max_users > ($users_to_join + $connected_users)) && $max_rooms == 0)) // YOU FOUND THE SERVER
        {
            return true;
        } else {     
            return false;
        }
    } else {        
        return false;
    }
}


