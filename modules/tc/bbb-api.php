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

/* get the config values */
//require_once "config.php";

class BigBlueButton {

    private $_securitySalt;
    private $_bbbServerBaseUrl;

    /* ___________ General Methods for the BigBlueButton Class __________ */

    /**
     * BigBlueButton constructor.
     * @brief /*Establish just our basic elements in the constructor:
        BASE CONFIGS - set these for your BBB server in config.php and they will simply flow in here via the constants:
     * @param $salt
     * @param $bbb_url
     */
    function __construct($salt, $bbb_url) {

        $this->_securitySalt = $salt;
        $this->_bbbServerBaseUrl = $bbb_url;
    }

    /**
     * @brief A private utility method used by other public methods to process XML responses.
     * @param $url
     * @return bool|SimpleXMLElement/*
     */
    private function _processXmlResponse($url){

        if (extension_loaded('curl')) {
            $ch = curl_init() or die(curl_error());
            $timeout = 10;
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $data = curl_exec($ch);
            curl_close($ch);

            if ($data) {
                try {
                    $element = @new SimpleXMLElement($data);
                } catch (Exception $e) {
                    return false;
                }
                return (new SimpleXMLElement($data));
            } else {
                return false;
            }
        }
        return (simplexml_load_file($url));
    }

    /**
     * @brief /* Process required params and throw errors if we don't get values
     * @param $param
     * @return mixed
     * @throws Exception
     */
    private function _requiredParam($param) {

        if ((isset($param)) && ($param != '')) {
            return $param;
        }
        elseif (!isset($param)) {
            throw new Exception('Missing parameter.');
        }
        else {
            throw new Exception(''.$param.' is required.');
        }
    }

    private function _optionalParam($param) {
        /* Pass most optional params through as set value, or set to '' */
        /* Don't know if we'll use this one, but let's build it in case. */
        if ((isset($param)) && ($param != '')) {
            return $param;
        } else {
            $param = '';
            return $param;
        }
    }

    /* __________________ BBB ADMINISTRATION METHODS _________________ */
    /* The methods in the following section support the following categories of the BBB API:
    -- create
    -- join
    -- end
    */

    public function getCreateMeetingURL($creationParams) {
        /*
        USAGE:
        (see $creationParams array in createMeetingArray method.)
         */
        $this->_meetingId = $this->_requiredParam($creationParams['meetingId']);
        $this->_meetingName = $this->_requiredParam($creationParams['meetingName']);
        // Set up the basic creation URL:
        $creationUrl = $this->_bbbServerBaseUrl."api/create?";
        // Add params:
        $params =
            'name='.urlencode($this->_meetingName).
            '&meetingID='.urlencode($this->_meetingId).
            '&attendeePW='.urlencode($creationParams['attendeePw']).
            '&moderatorPW='.urlencode($creationParams['moderatorPw']).
            '&dialNumber='.urlencode($creationParams['dialNumber']).
            '&voiceBridge='.urlencode($creationParams['voiceBridge']).
            '&webVoice='.urlencode($creationParams['webVoice']).
            '&logoutURL='.urlencode($creationParams['logoutUrl']).
            '&maxParticipants='.urlencode($creationParams['maxParticipants']).
            '&record='.urlencode($creationParams['record']).
            '&duration='.urlencode($creationParams['duration']).
            '&muteOnStart='.urlencode($creationParams['muteOnStart']).
            '&lockSettingsDisableMic='.urlencode($creationParams['lockSettingsDisableMic']).
            '&lockSettingsDisableCam='.urlencode($creationParams['lockSettingsDisableCam']).
            '&webcamsOnlyForModerator='.urlencode($creationParams['webcamsOnlyForModerator']).
            '&lockSettingsDisablePrivateChat='.urlencode($creationParams['lockSettingsDisablePrivateChat']).
            '&lockSettingsDisablePublicChat='.urlencode($creationParams['lockSettingsDisablePublicChat']).
            '&lockSettingsDisableNote='.urlencode($creationParams['lockSettingsDisableNote']).
            '&lockSettingsHideUserList='.urlencode($creationParams['lockSettingsHideUserList']);
            //'&meta_category='.urlencode($creationParams['meta_category']);
            $welcomeMessage = $creationParams['welcomeMsg'];
        if (trim($welcomeMessage)) {
            $params .= '&welcome=' . urlencode($welcomeMessage);
        }
        // Return the complete URL:
        return $creationUrl.$params.'&checksum='.sha1("create".$params.$this->_securitySalt);
    }

    public function createMeetingWithXmlResponseArray($creationParams) {
        /*
        USAGE:
        $creationParams = array(
        'name' => 'Meeting Name', -- A name for the meeting (or username)
        'meetingId' => '1234', -- A unique id for the meeting
        'attendeePw' => 'ap', -- Set to 'ap' and use 'ap' to join = no user pass required.
        'moderatorPw' => 'mp', -- Set to 'mp' and use 'mp' to join = no user pass required.
        'welcomeMsg' => '', -- ''= use default. Change to customize.
        'dialNumber' => '', -- The main number to call into. Optional.
        'voiceBridge' => '', -- PIN to join voice. Optional.
        'webVoice' => '', -- Alphanumeric to join voice. Optional.
        'logoutUrl' => '', -- Default in bigbluebutton.properties. Optional.
        'maxParticipants' => '-1', -- Optional. -1 = unlimitted. Not supported in BBB. [number]
        'record' => 'false', -- New. 'true' will tell BBB to record the meeting.
        'duration' => '0', -- Default = 0 which means no set duration in minutes. [number]
        'muteOnStart' => $muteOnStart, // Default = false. true will mute all users when the meeting starts
        'lockSettingsDisableMic' => $lockSettingsDisableMic, // Default = false. true will disable viewer's mic
        'lockSettingsDisableCam' => $lockSettingsDisableCam, // Default = false. true will prevent viewers from sharing their camera in the meeting
        'webcamsOnlyForModerator' => $webcamsOnlyForModerator, // Default = false. With true, webcams shared by viewers will only appear to moderators
        'lockSettingsDisablePrivateChat' => $lockSettingsDisablePrivateChat, // Default = false. true will disable private chats for viewers
        'lockSettingsDisablePublicChat' => $lockSettingsDisablePublicChat, // Default = false. true will disable public chat for viewers
        'lockSettingsDisableNote' => $lockSettingsDisableNote, // Default = false. true will disable shared notes for viewers
        'lockSettingsHideUserList' => $lockSettingsHideUserList, // Default = false. true will hide viewer's list for viewers
        'meta_category' => '', -- Use to pass additional info to BBB server. See API docs to enable.
        );
        */
        $xml = $this->_processXmlResponse($this->getCreateMeetingURL($creationParams));

        if ($xml) {
            if ($xml->meetingID) {
                return array(
                    'returncode' => $xml->returncode,
                    'message' => $xml->message,
                    'messageKey' => $xml->messageKey,
                    'meetingId' => $xml->meetingID,
                    'attendeePw' => $xml->attendeePW,
                    'moderatorPw' => $xml->moderatorPW,
                    'hasBeenForciblyEnded' => $xml->hasBeenForciblyEnded,
                    'createTime' => $xml->createTime
                );
            } else {
                return array(
                    'returncode' => $xml->returncode,
                    'message' => $xml->message,
                    'messageKey' => $xml->messageKey
                );
            }
        } else {
            return null;
        }
    }

    public function getJoinMeetingURL($joinParams) {
        /*
        NOTE: At this point, we don't use a corresponding joinMeetingWithXmlResponse here because the API
        doesn't respond on success, but you can still code that method if you need it. Or, you can take the URL
        that's returned from this method and simply send your users off to that URL in your code.
        USAGE:
        $joinParams = array(
        'meetingId' => '1234', -- REQUIRED - A unique id for the meeting
        'fullName' => 'Jane Doe', -- REQUIRED - The name that will display for the user in the meeting
        'password' => 'ap', -- REQUIRED - The attendee or moderator password, depending on what's passed here
        'createTime' => '', -- OPTIONAL - string. Leave blank ('') unless you set this correctly.
        'userID' => '', -- OPTIONAL - string
        'webVoiceConf' => '' -- OPTIONAL - string
        );
        */
        $this->_meetingId = $this->_requiredParam($joinParams['meetingId']);
        $this->_fullName = $this->_requiredParam($joinParams['fullName']);
        $this->_password = $this->_requiredParam($joinParams['password']);
        // Establish the basic join URL:
        $joinUrl = $this->_bbbServerBaseUrl."api/join?";
        // Add parameters to the URL:
        $params =
            'meetingID='.urlencode($this->_meetingId).
            '&fullName='.urlencode($this->_fullName).
            '&password='.urlencode($this->_password).
            '&userID='.urlencode($joinParams['userId']).
            '&webVoiceConf='.urlencode($joinParams['webVoiceConf']);
        // Only use createTime if we really want to use it. If it's '', then don't pass it:
        if (((isset($joinParams['createTime'])) && ($joinParams['createTime'] != ''))) {
            $params .= '&createTime='.urlencode($joinParams['createTime']);
        }
        // Return the URL:
        return $joinUrl.$params.'&checksum='.sha1("join".$params.$this->_securitySalt);
    }

    public function getEndMeetingURL($endParams) {
        /* USAGE:
            $endParams = array (
                'meetingId' => '1234', -- REQUIRED - The unique id for the meeting
                'password' => 'mp' -- REQUIRED - The moderator password for the meeting
            );
         */
        $this->_meetingId = $this->_requiredParam($endParams['meetingId']);
        $this->_password = $this->_requiredParam($endParams['password']);
        $endUrl = $this->_bbbServerBaseUrl."api/end?";
        $params =
            'meetingID='.urlencode($this->_meetingId).
            '&password='.urlencode($this->_password);
        return $endUrl.$params.'&checksum='.sha1("end".$params.$this->_securitySalt);
    }

    public function endMeetingWithXmlResponseArray($endParams) {
        /* USAGE:
        $endParams = array (
        'meetingId' => '1234', -- REQUIRED - The unique id for the meeting
        'password' => 'mp' -- REQUIRED - The moderator password for the meeting
        );
        */
        $xml = $this->_processXmlResponse($this->getEndMeetingURL($endParams));
        if ($xml) {
            return array(
                'returncode' => $xml->returncode,
                'message' => $xml->message,
                'messageKey' => $xml->messageKey
            );
        } else {
            return null;
        }

    }

    /* __________________ BBB MONITORING METHODS _________________ */
    /* The methods in the following section support the following categories of the BBB API:
    -- isMeetingRunning
    -- getMeetings
    -- getMeetingInfo
    */

    public function getIsMeetingRunningUrl($meetingId) {
        /* USAGE:
        $meetingId = '1234' -- REQUIRED - The unique id for the meeting
        */
        $this->_meetingId = $this->_requiredParam($meetingId);
        $runningUrl = $this->_bbbServerBaseUrl."api/isMeetingRunning?";
        $params = 'meetingID='.urlencode($this->_meetingId);
        return $runningUrl.$params.'&checksum='.sha1("isMeetingRunning".$params.$this->_securitySalt);
    }

    public function isMeetingRunningWithXmlResponseArray($meetingId) {
        /* USAGE:
        $meetingId = '1234' -- REQUIRED - The unique id for the meeting
        */
        $xml = $this->_processXmlResponse($this->getIsMeetingRunningUrl($meetingId));
        if ($xml) {
            return array(
                'returncode' => $xml->returncode,
                'running' => $xml->running // -- Returns true/false.
            );
        } else {
            return null;
        }

    }

    public function getGetMeetingsUrl() {
        /* Simply formulate the getMeetings URL
        We do this in a separate function so we have the option to just get this
        URL and print it if we want for some reason.
        */
        $getMeetingsUrl = $this->_bbbServerBaseUrl."api/getMeetings?checksum=".sha1("getMeetings".$this->_securitySalt);
        return $getMeetingsUrl;
    }

    public function getMeetingsWithXmlResponseArray() {
    /* USAGE:
    We don't need to pass any parameters with this one, so we just send the query URL off to BBB
    and then handle the results that we get in the XML response.
     */
        $xml = $this->_processXmlResponse($this->getGetMeetingsUrl());
        if ($xml) {
            // If we don't get a success code, stop processing and return just the returncode:
            if ($xml->returncode != 'SUCCESS') {
                $result = array(
                    'returncode' => $xml->returncode
                );
                return $result;
            }
            elseif ($xml->messageKey == 'noMeetings') {
                /* No meetings on server, so return just this info: */
                $result = array(
                    'returncode' => $xml->returncode,
                    'messageKey' => $xml->messageKey,
                    'message' => $xml->message
                );
                return $result;
            }
            else {
                // In this case, we have success and meetings. First return general response:
                $result = array(
                    'returncode' => $xml->returncode,
                    'messageKey' => $xml->messageKey,
                    'message' => $xml->message
                );
                // Then interate through meeting results and return them as part of the array:
                foreach ($xml->meetings->meeting as $m) {
                    $result[] = array(
                        'meetingId' => $m->meetingID,
                        'meetingName' => $m->meetingName,
                        'createTime' => $m->createTime,
                        'attendeePw' => $m->attendeePW,
                        'moderatorPw' => $m->moderatorPW,
                        'hasBeenForciblyEnded' => $m->hasBeenForciblyEnded,
                        'running' => $m->running,
                        'participantCount' => $m->participantCount,
                        'listenerCount' => $m->listenerCount,
                        'voiceParticipantCount' => $m->voiceParticipantCount,
                        'videoCount' => $m->videoCount,
                        'moderatorCount' => $m->moderatorCount
                    );
                }
                return $result;
            }
        } else {
            return null;
        }

    }

        /**
         * @param $bbb_url
         * @param $salt
         * @param $infoParams = array(
                                'meetingId' => '1234', -- REQUIRED - The unique id for the meeting
                                'password' => 'mp' -- REQUIRED - The moderator password for the meeting
         * @return string
         * @throws Exception
         */
    public function getMeetingInfoUrl($bbb_url, $salt, $infoParams) {

        $this->_meetingId = $this->_requiredParam($infoParams['meetingId']);
        $this->_password = $this->_requiredParam($infoParams['password']);
        $infoUrl = $bbb_url."api/getMeetingInfo?";
        $securitySalt = $salt;

        $params =
            'meetingID='.urlencode($this->_meetingId).
            '&password='.urlencode($this->_password);

        return ($infoUrl.$params.'&checksum='.sha1("getMeetingInfo".$params.$securitySalt));
    }


        /**
         * @param $bbb
         * @param $bbb_url
         * @param $salt
         * @param $infoParams = array(
                                    'meetingId' => '1234', -- REQUIRED - The unique id for the meeting
                                    'password' => 'mp' -- REQUIRED - The moderator password for the meeting
         * @return array|null
         */
    public function getMeetingInfoWithXmlResponseArray($bbb, $bbb_url, $salt, $infoParams) {


        $xml = $bbb->_processXmlResponse($bbb->getMeetingInfoUrl($bbb_url,$salt,$infoParams));

        if ($xml) {
            // If we don't get a success code or messageKey, find out why:
            if (($xml->returncode != 'SUCCESS') || ($xml->messageKey == null)) {
                $result = array(
                    'returncode' => $xml->returncode,
                    'messageKey' => $xml->messageKey,
                    'message' => $xml->message
                );
                return $result;
            }
            else {
                // In this case, we have success and meeting info:
                $result = array(
                    'returncode' => $xml->returncode,
                    'meetingName' => $xml->meetingName,
                    'meetingId' => $xml->meetingID,
                    'createTime' => $xml->createTime,
                    'createDate' => $xml->createDate,
                    'voiceBridge' => $xml->voiceBridge,
                    'attendeePw' => $xml->attendeePW,
                    'moderatorPw' => $xml->moderatorPW,
                    'running' => $xml->running,
                    'recording' => $xml->recording,
                    'hasBeenForciblyEnded' => $xml->hasBeenForciblyEnded,
                    'startTime' => $xml->startTime,
                    'endTime' => $xml->endTime,
                    'participantCount' => $xml->participantCount,
                    'listenerCount' => $xml->listenerCount,
                    'voiceParticipantCount' => $xml->voiceParticipantCount,
                    'videoCount' => $xml->videoCount,
                    'maxUsers' => $xml->maxUsers,
                    'moderatorCount' => $xml->moderatorCount,
                    'attendees' => array(),
                );
                // Then interate through attendee results and return them as part of the array:
                foreach ($xml->attendees->attendee as $a) {
                    $result['attendees'][] = array(
                        'userId' => $a->userID,
                        'fullName' => $a->fullName,
                        'role' => $a->role
                    );
                }
                return $result;
            }
        }
        else {
            return null;
        }

    }

    /* __________________ BBB RECORDING METHODS _________________ */
    /* The methods in the following section support the following categories of the BBB API:
    -- getRecordings
    -- publishRecordings
    -- deleteRecordings
    */

        /**
         * @param $recordingParams  array(
                            'meetingId' => '1234', -- OPTIONAL - comma separate if multiple ids
         * @return string
         */
    public function getRecordingsUrl($recordingParams) {
        $recordingsUrl = $this->_bbbServerBaseUrl."api/getRecordings?";
        $params =
            'meetingID='.urlencode($recordingParams['meetingId']);
        return ($recordingsUrl.$params.'&checksum='.sha1("getRecordings".$params.$this->_securitySalt));

    }

        /**
         * @brief
         * @note: 'duration' DOES work when creating a meeting, so if you set duration
                when creating a meeting, it will kick users out after the duration. Should
                probably be required in user code when 'recording' is set to true.
         * @param $recordingParams array(
                            'meetingId' => '1234', -- OPTIONAL - comma separate if multiple ids
                                );
         * @return array|null
         */
    public function getRecordingsWithXmlResponseArray($recordingParams) {

        $xml = $this->_processXmlResponse($this->getRecordingsUrl($recordingParams));
        if ($xml) {
            // If we don't get a success code or messageKey, find out why:
            if (($xml->returncode != 'SUCCESS') || ($xml->messageKey == null)) {
                $result = array(
                    'returncode' => $xml->returncode,
                    'messageKey' => $xml->messageKey,
                    'message' => $xml->message
                );
                return $result;
            }
            else {
                // In this case, we have success and recording info:
                $result = array(
                    'returncode' => $xml->returncode,
                    'messageKey' => $xml->messageKey,
                    'message' => $xml->message
                );

                foreach ($xml->recordings->recording as $r) {
                    $result[] = array(
                        'recordId' => $r->recordID,
                        'meetingId' => $r->meetingID,
                        'name' => $r->name,
                        'published' => $r->published,
                        'startTime' => $r->startTime,
                        'endTime' => $r->endTime,
                        'playbackFormatType' => $r->playback->format->type,
                        'playbackFormatUrl' => $r->playback->format->url,
                        'playbackFormatLength' => $r->playback->format->length,
                        'metadataTitle' => $r->metadata->title,
                        'metadataSubject' => $r->metadata->subject,
                        'metadataDescription' => $r->metadata->description,
                        'metadataCreator' => $r->metadata->creator,
                        'metadataContributor' => $r->metadata->contributor,
                        'metadataLanguage' => $r->metadata->language,
                        // Add more here as needed for your app depending on your
                        // use of metadata when creating recordings.
                    );
                }
                return $result;
            }
        }
        else {
            return null;
        }
    }

    public function getPublishRecordingsUrl($recordingParams) {
        /* USAGE:
        $recordingParams = array(
        'recordId' => '1234', -- REQUIRED - comma separate if multiple ids
        'publish' => 'true', -- REQUIRED - boolean: true/false
        );
         */
        $recordingsUrl = $this->_bbbServerBaseUrl."api/publishRecordings?";
        $params =
            'recordID='.urlencode($recordingParams['recordId']).
            '&publish='.urlencode($recordingParams['publish']);
        return ($recordingsUrl.$params.'&checksum='.sha1("publishRecordings".$params.$this->_securitySalt));

    }

    public function publishRecordingsWithXmlResponseArray($recordingParams) {
        /* USAGE:
        $recordingParams = array(
        'recordId' => '1234', -- REQUIRED - comma separate if multiple ids
        'publish' => 'true', -- REQUIRED - boolean: true/false
        );
         */
        $xml = $this->_processXmlResponse($this->getPublishRecordingsUrl($recordingParams));
        if ($xml) {
            return array(
                'returncode' => $xml->returncode,
                'published' => $xml->published // -- Returns true/false.
            );
        }
        else {
            return null;
        }


    }

    public function getDeleteRecordingsUrl($recordingParams) {
        /* USAGE:
        $recordingParams = array(
        'recordId' => '1234', -- REQUIRED - comma separate if multiple ids
        );
         */
        $recordingsUrl = $this->_bbbServerBaseUrl."api/deleteRecordings?";
        $params =
            'recordID='.urlencode($recordingParams['recordId']);
        return ($recordingsUrl.$params.'&checksum='.sha1("deleteRecordings".$params.$this->_securitySalt));
    }

    public function deleteRecordingsWithXmlResponseArray($recordingParams) {
        /* USAGE:
        $recordingParams = array(
        'recordId' => '1234', -- REQUIRED - comma separate if multiple ids
        );
         */

        $xml = $this->_processXmlResponse($this->getDeleteRecordingsUrl($recordingParams));
        if ($xml) {
            return array(
                'returncode' => $xml->returncode,
                'deleted' => $xml->deleted // -- Returns true/false.
            );
        } else {
            return null;
        }

    }


    public function getMeetingInfo($url) {
        return $this->_processXmlResponse($url);
    }


} // END OF BIGBLUEBUTTON CLASS
