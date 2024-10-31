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

namespace modules\tc\Zoom\User;

use Database;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use modules\tc\Zoom\Api\Repository;
use Session;

require_once 'include/lib/pwgen.inc.php';

class ZoomUserRepository
{
    const ZOOM_API_BASE_URL = 'https://api.zoom.us';
    const TYPE_BASIC = 1;
    const TYPE_LICENSED = 2;
    const ACTION_CREATE = 'create';

    /**
     * @var Client
     */
    private $client;
    /**
     * @var Repository
     */
    private $zoomRepository;

    public function __construct(Client $client, Repository $zoomRepository)
    {
        $this->client = $client;
        $this->zoomRepository = $zoomRepository;
    }

    public function getUserFromDatabase(int $id)
    {
        return Database::get()->querySingle("SELECT * FROM `zoom_user` WHERE `user_id` = " . $id);
    }


    public function syncZoomUsersTable() : void
    {
        global $langZoomUserCreateError;

        $zoomUsers = $this->listAllZoomUsers();

        if (
            !empty($zoomUsers)
            && !empty($zoomUsers->users)
        ) {
            foreach ($zoomUsers->users as $zoomUser) {
                $eclassUser = Database::get()
                    ->querySingle("SELECT id FROM user WHERE email = '".$zoomUser->email."'");

                if (
                    $eclassUser
                    && !empty($eclassUser->id)
                ) {
                    $tc_user = $this->saveInDatabase($eclassUser->id, $zoomUser);

                    if (!$tc_user) {
                        Session::flash('message', $langZoomUserCreateError);
                        Session::flash('alert-class', 'alert-danger');
                        redirect_to_home_page($_SERVER['HTTP_REFERER'], true);
                    }
                }
            }
        }
    }

    public function createZoomUser($eclassUser)
    {
        global $langZoomUserCreateError;

        $accessToken = $this->zoomRepository->getAccessToken();
        $password = choose_password_strength();
        $password_encrypted = password_hash($password, PASSWORD_DEFAULT);

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $body = '{
          "action": "'.self::ACTION_CREATE.'",
          "user_info": {
            "email": "'.$eclassUser->email.'",
            "first_name": "'.$eclassUser->givenname.'",
            "last_name": "'.$eclassUser->surname.'",
            "display_name": "'.$eclassUser->username.'",
            "password": "'.$password_encrypted.'",
            "type": '.self::TYPE_BASIC.',
            "feature": {
              "zoom_phone": false
            }
          }
        }';

        try {
            $res = $this->client->post(
                self::ZOOM_API_BASE_URL . '/v2/users',
                [
                    'headers' => $headers,
                    'body' => $body
                ]
            );
        } catch (ClientException $e) {
            Session::flash('message', $langZoomUserCreateError);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page($_SERVER['HTTP_REFERER'], true);
        }

        $responseDataJson = $res->getBody()->getContents();
        $responseData = json_decode($responseDataJson);

        $tc_user = $this->saveInDatabase($eclassUser->id, $responseData);
        if (!$tc_user) {
            Session::flash('message', $langZoomUserCreateError);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page($_SERVER['HTTP_REFERER'], true);
        }
        return $responseData;
    }

    public function listAllZoomUsers()
    {
        global $langZoomListUsersError;

        $accessToken = $this->zoomRepository->getAccessToken();

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken
        ];

        try {
            $res = $this->client->get(
                self::ZOOM_API_BASE_URL . '/v2/users',
                [
                    'headers' => $headers
                ]
            );
        } catch (ClientException $e) {
            Session::flash('message', $langZoomListUsersError);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page($_SERVER['HTTP_REFERER'], true);
        }

        $responseDataJson = $res->getBody()->getContents();
        return json_decode($responseDataJson);
    }

    public function userExists(string $email) : bool
    {
        global $langZoomUserExistsError;

        $accessToken = $this->zoomRepository->getAccessToken();

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken
        ];

        try {
            $res = $this->client->get(
                self::ZOOM_API_BASE_URL . '/v2/users/email?email=' . $email,
                [
                    'headers' => $headers
                ]
            );
        } catch (ClientException $e) {
            Session::flash('message', $langZoomUserExistsError . $email);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page($_SERVER['HTTP_REFERER'], true);
        }

        $responseDataJson = $res->getBody()->getContents();
        $response = json_decode($responseDataJson);

        if (empty($response->existed_email)) {
            return false;
        }
        return true;
    }

    public function changeUserType(string $id, string $email, int $type)
    {
        global $langZoomUserTypeError;

        $accessToken = $this->zoomRepository->getAccessToken();

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ];

        $body = '{
          "feature_type": "user_type",
          "feature_value": "'.$type.'",
          "users": [
            {
              "id": "'.$id.'",
              "email": "'.$email.'"
            }
          ]
        }';

        try {
            $res = $this->client->post(
                self::ZOOM_API_BASE_URL . '/v2/users/features',
                [
                    'headers' => $headers,
                    'body' => $body
                ]
            );
        } catch (ClientException $e) {
            Session::flash('message', $langZoomUserTypeError);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page($_SERVER['HTTP_REFERER'], true);
        }

        $responseDataJson = $res->getBody()->getContents();
        return json_decode($responseDataJson);
    }

    public function zoomApiEnabled() : bool
    {
        return $this->zoomRepository->isEnabled();
    }

    private function saveInDatabase($userId, $data)
    {
        Database::get()->query("INSERT IGNORE INTO `zoom_user` 
                                            (`user_id`, `id`, `first_name`, `last_name`, `email`, `type`)
                                            VALUES (
                                                    ".$userId.", 
                                                    '".$data->id."', 
                                                    '".$data->first_name."', 
                                                    '".$data->last_name."', 
                                                    '".strtolower($data->email)."', 
                                                    ".$data->type.")");

        return Database::get()->querySingle("SELECT * FROM `zoom_user` where `user_id` = " . $userId);
    }
}
