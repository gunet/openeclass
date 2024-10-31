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

require_once 'modules/lti/classes/LtiEnrolHelper.php';
require_once 'modules/lti/classes/LtiEnrolDataConnector.php';
require_once 'modules/lti/ltiprovider/src/Profile/Item.php';
require_once 'modules/lti/ltiprovider/src/Profile/Message.php';
require_once 'modules/lti/ltiprovider/src/Profile/ResourceHandler.php';
require_once 'modules/lti/ltiprovider/src/Profile/ServiceDefinition.php';
require_once 'modules/lti/ltiprovider/src/ToolProvider/ToolProvider.php';

use IMSGlobal\LTI\Profile\Item;
use IMSGlobal\LTI\Profile\Message;
use IMSGlobal\LTI\Profile\ResourceHandler;
use IMSGlobal\LTI\Profile\ServiceDefinition;
use IMSGlobal\LTI\ToolProvider\ToolProvider;

/**
 * Extends the IMS Tool provider library for the LTI enrolment.
 */
class LtiToolProvider extends ToolProvider {

    /**
     * @var stdClass $tool The object representing the enrol instance providing this LTI tool
     */
    protected $tool;

    /**
     * Remove $this->baseUrl (wwwroot) from a given url string and return it.
     *
     * @param string $url The url from which to remove the base url
     * @return string|null A string of the relative path to the url, or null if it couldn't be determined.
     */
    protected function strip_base_url(string $url): ?string {
        if (substr($url, 0, strlen($this->baseUrl)) == $this->baseUrl) {
            return substr($url, strlen($this->baseUrl));
        }
        return null;
    }

    /**
     * Create a new instance of tool_provider to handle all the LTI tool provider interactions.
     *
     * @param int $toolid The id of the tool to be provided.
     */
    public function __construct($toolid) {
        global $siteName, $Institution, $urlServer;

        $token = LtiEnrolHelper::generate_proxy_token($toolid);

        $tool = LtiEnrolHelper::get_lti_tool($toolid);
        $this->tool = $tool;

        $dataconnector = new LtiEnrolDataConnector();
        parent::__construct($dataconnector);

        $this->baseUrl = $urlServer;
        $toolpath = LtiEnrolHelper::get_launch_url($toolid);
        $toolpath = $this->strip_base_url($toolpath);

        $this->vendor = new Item($siteName, $Institution, null, $urlServer);

        $name = $tool->title;
        $description = $tool->description;
        $icon = LtiEnrolHelper::get_icon($tool);
        $icon = $this->strip_base_url($icon);

        $this->product = new Item(
            $token,
            $name,
            $description,
            LtiEnrolHelper::get_launch_url($toolid),
            '1.0'
        );

        $requiredmessages = [
            new Message(
                'basic-lti-launch-request',
                $toolpath,
                [
                   'Context.id',
                   'CourseSection.title',
                   'CourseSection.label',
                   'CourseSection.sourcedId',
                   'CourseSection.longDescription',
                   'CourseSection.timeFrame.begin',
                   'ResourceLink.id',
                   'ResourceLink.title',
                   'ResourceLink.description',
                   'User.id',
                   'User.username',
                   'Person.name.full',
                   'Person.name.given',
                   'Person.name.family',
                   'Person.email.primary',
                   'Person.sourcedId',
                   'Person.name.middle',
                   'Person.address.street1',
                   'Person.address.locality',
                   'Person.address.country',
                   'Person.address.timezone',
                   'Person.phone.primary',
                   'Person.phone.mobile',
                   'Person.webaddress',
                   'Membership.role',
                   'Result.sourcedId',
                   'Result.autocreate'
                ]
            )
        ];
        $optionalmessages = [
        ];

        $this->resourceHandlers[] = new ResourceHandler(
             new Item(
                 $token,
                 $tool->title,
                 $description
             ),
             $icon,
             $requiredmessages,
             $optionalmessages
        );

        $this->requiredServices[] = new ServiceDefinition(['application/vnd.ims.lti.v2.toolproxy+json'], ['POST']);
        $this->requiredServices[] = new ServiceDefinition(['application/vnd.ims.lis.v2.membershipcontainer+json'], ['GET']);
    }

    /**
     * Override onError for custom error handling.
     * @return void
     */
    protected function onError() {
        $message = $this->message;
        if ($this->debugMode && !empty($this->reason)) {
            $message = $this->reason;
        }

        // Display the error message from the provider's side if the consumer has not specified a URL to pass the error to.
        if (empty($this->returnUrl)) {
            error_log("enrol_lti failed request error with reason: " . $message);
        }
    }

    /**
     * Override onLaunch with tool logic.
     * @return void
     */
    protected function onLaunch() {
        global $urlServer;

        // Check for valid consumer.
        if (empty($this->consumer) || $this->dataConnector->loadToolConsumer($this->consumer) === false) {
            $this->ok = false;
            $this->message = "enrol_lti: invalidtoolconsumer";
            return;
        }

        $url = LtiEnrolHelper::get_launch_url($this->tool->id);
        // If a tool proxy has been stored for the current consumer trying to access a tool,
        // check that the tool is being launched from the correct url.
        $correctlaunchurl = false;
        if (!empty($this->consumer->toolProxy)) {
            $proxy = json_decode($this->consumer->toolProxy);
            $handlers = $proxy->tool_profile->resource_handler;
            foreach ($handlers as $handler) {
                foreach ($handler->message as $message) {
                    $fullpath = $message->path;
                    if ($message->message_type == "basic-lti-launch-request" && $fullpath == $url) {
                        $correctlaunchurl = true;
                        break 2;
                    }
                }
            }
        } else if ($this->tool->lti_provider_secret == $this->consumer->secret) {
            // Test if the LTI1 secret for this tool is being used. Then we know the correct tool is being launched.
            $correctlaunchurl = true;
        }
        if (!$correctlaunchurl) {
            $this->ok = false;
            $this->message = "enrol_lti: invalidrequest";
            return;
        }

        // Before we do anything check that the context is valid.
        $tool = $this->tool;
        $serviceurl = $this->resourceLink->getSetting('lis_outcome_service_url');
        if ($service_parsed_url = parse_url($serviceurl)) {
            $service_scheme = isset($service_parsed_url['scheme']) ? $service_parsed_url['scheme'] . '://' : '';
            $service_host   = $service_parsed_url['host'] ?? '';
            $service_port   = isset($service_parsed_url['port']) ? ':' . $service_parsed_url['port'] : '';
            $servicedomain = $service_scheme . $service_host . $service_port . '/';
        } else {
            $servicedomain = $serviceurl;
        }

        // Set the user data.
        $user = new stdClass();
        $user->email = $this->user->email;
        $user->username = LtiEnrolHelper::create_username($urlServer, $servicedomain, $this->user->ltiUserId);
        $user->password = 'lti_publish';
        if (!empty($this->user->firstname)) {
            $user->givenname = $this->user->firstname;
        } else {
            $user->givenname = $this->user->getRecordId();
        }
        if (!empty($this->user->lastname)) {
            $user->surname = $this->user->lastname;
        } else {
            $user->surname = $this->user->getRecordId();
        }

        // Check if the user exists.
        $dbuser = Database::get()->querySingle("SELECT * FROM user WHERE username = ?s", $user->username);
        if (!$dbuser) {
            // If the email was stripped/not set then fill it with a default one.
            if (empty($user->email)) {
                $user->email = $user->username . "@example.com";
            }

            $registered_at = DBHelper::timeAfter();

            $uq = Database::get()->query("INSERT INTO user (surname, givenname, username, password, email, registered_at, expires_at)
                                                              VALUES (?s, ?s, ?s, ?s, ?s, " . $registered_at . ", 
                                                              DATE_ADD(NOW(), INTERVAL " . get_config('account_duration') . " SECOND))",
                $user->surname,
                $user->givenname,
                $user->username,
                $user->password,
                $user->email
            );
            $userid = $uq->lastInsertID;

            // Get the updated user record.
            $user = Database::get()->querySingle("SELECT * FROM user WHERE id = ?d", $userid);
        } else {
            if (LtiEnrolHelper::user_match($user, $dbuser)) {
                $user = $dbuser;
            } else {
                // If email is empty don't update the user with an empty email
                $emptyemail = false;
                if (empty($user->email)) {
                    $emptyemail = true;
                }

                if ($emptyemail) {
                    Database::get()->query("UPDATE user SET surname = ?s, givenname = ?s, password = ?s WHERE id = ?d",
                        $user->surname,
                        $user->givenname,
                        $user->password,
                        $dbuser->id
                    );
                } else {
                    Database::get()->query("UPDATE user SET surname = ?s, givenname = ?s, password = ?s, email = ?s WHERE id = ?d",
                        $user->surname,
                        $user->givenname,
                        $user->password,
                        $user->email,
                        $dbuser->id
                    );
                }

                // Get the updated user record.
                $user = Database::get()->querySingle("SELECT * FROM user WHERE id = ?d", $dbuser->id);
            }
        }

        $courseid = $tool->course_id;
        $urltogo = $urlServer . "courses/" . course_id_to_code($courseid);
        $sourceid = $this->user->ltiResultSourcedId;
        $consumerkey = $this->consumer->getKey();
        $membershipsurl = $this->resourceLink->getSetting('ext_ims_lis_memberships_url');
        $membershipsid = $this->resourceLink->getSetting('ext_ims_lis_memberships_id');

        // Enrol the user in the course with no role, log user activity and check if we have recorded this user before
        $result = LtiEnrolHelper::enrol_user($tool, $user->id, $sourceid, $serviceurl, $consumerkey, $membershipsurl, $membershipsid);

        // Display an error, if there is one.
        if ($result !== LtiEnrolHelper::ENROLMENT_SUCCESSFUL) {
            print_error($result, 'enrol_lti');
            exit();
        }

        // set necessary cookie settings
        // see also: http://www.imsglobal.org/samesite-cookie-issues-lti-tool-providers
        session_destroy();
        session_set_cookie_params([
            'secure' => true,
            'samesite' => 'None'
        ]);
        session_start();

        // Finalise the user log in.
        login($user, $user->username, $user->password, 'lti_publish');

        // Everything's good. Set appropriate OK flag and message values.
        $this->ok = true;
        $this->message = "success";

        // All done, redirect the user to where they want to go.
        redirect($urltogo);
    }

    /**
     * Override onRegister with registration code.
     */
    protected function onRegister() {

        if (empty($this->consumer)) {
            $this->ok = false;
            $this->message = "enrol_lti: invalidtoolconsumer";
            return;
        }

        if (empty($this->returnUrl)) {
            $this->ok = false;
            $this->message = "enrol_lti: returnurlnotset";
            return;
        }

        if ($this->doToolProxyService()) {
            // Indicate successful processing in message.
            $this->message = "enrol_lti: successfulregistration";

            // Prepare response.
            $returnurl = $this->returnUrl;
            $returnurl .= (parse_url($returnurl, PHP_URL_QUERY) ? "&" : "?") . "lti_msg=" . rawurlencode("enrol_lti: successfulregistration");
            $returnurl .= (parse_url($returnurl, PHP_URL_QUERY) ? "&" : "?") . "status=success";
            $guid = $this->consumer->getKey();
            $returnurl .= (parse_url($returnurl, PHP_URL_QUERY) ? "&" : "?") . "tool_proxy_guid=" . rawurlencode($guid);

            redirect($returnurl);
        } else {
            // Tell the consumer that the registration failed.
            $this->ok = false;
            $this->message = "enrol_lti: couldnotestablishproxy";
        }
    }

}
