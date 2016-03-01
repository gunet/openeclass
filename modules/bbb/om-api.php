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

    $res = Database::get()->querySingle("SELECT running_at FROM bbb_session WHERE meeting_id = ?s",$meeting_id);
    if ($res) {
        $running_server = str_replace('888','',$res->running_at);
    }

    $res = Database::get()->querySingle("SELECT * FROM om_servers WHERE id = ?d", $running_server);

    $url = $res->hostname.':'.$res->port;

    $soapUsers = new SoapClient('http://'.$url.'/'.$res->webapp.'/services/UserService?wsdl');
    $roomService = new SoapClient('http://'.$url.'/'.$res->webapp.'/openmeetings/services/RoomService?wsdl');

    $rs = array();
    $rs = $soapUsers->getSession();

    $session_id = $rs->return->session_id;
    
    $params = array(
            'SID' => $session_id,
            'username' => utf8_encode($res->username),
            'userpass' => utf8_encode($res->password)
    );

    $l = array();
    $l = $soapUsers->loginUser($params);

    $params = array(
            'SID' => $session_id,
            'username' => utf8_encode($username),
            'firstname' => utf8_encode($name),
            'lastname' => utf8_encode($surname),
            'profilePictureUrl' => '',
            'email' => $email,
            'externalUserId' => $uid,
            'externalUserType' => 'openeclass',
            'room_id' => 19,
            'becomeModeratorAsInt' => $moderator,
            'showAudioVideoTestAsInt' => 1
    );

    $rs = array();
    $rs = $soapUsers->setUserObjectAndGenerateRoomHash($params);
    
    return "http://$url/'.$res->webapp.'/?secureHash=".$rs->return;
}

function om_session_running($meeting_id)
{
    $res = Database::get()->querySingle("SELECT running_at FROM bbb_session WHERE meeting_id = ?s",$meeting_id);

    if (!isset($res->running_at)) {
        return false;
    }
    $running_server = str_replace('888','',$res->running_at);

    if (Database::get()->querySingle("SELECT count(*) as count FROM om_servers
            WHERE id=?d AND enabled='true'", $running_server)->count == 0) {
        //it means that the server is disabled so session must be recreated
        return false;
    }

    $res = Database::get()->querySingle("SELECT *
                                    FROM om_servers
                                    WHERE id=?d", $running_server);
    
    $url = $res->hostname.':'.$res->port;

    $soapUsers = new SoapClient('http://'.$url.'/'.$res->webapp.'/services/UserService?wsdl');
    $roomService = new SoapClient('http://'.$url.'/'.$res->webapp.'/openmeetings/services/RoomService?wsdl');

    $rs = array();
    $rs = $soapUsers->getSession();

    $session_id = $rs->return->session_id;
    
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