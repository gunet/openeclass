<?php

namespace modules\tc\Zoom\User;

use Database;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use modules\tc\Zoom\Api\Repository;

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

    public function createZoomUser($eclassUser)
    {
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
            "type": '.self::TYPE_LICENSED.',
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
            echo '<pre>';
            print_r($e->getMessage());
            exit;
        }

        $responseDataJson = $res->getBody()->getContents();
        $responseData = json_decode($responseDataJson);

        $tc_user = $this->saveInDatabase($eclassUser->id, $responseData);
        if (!$tc_user) {
            die('here');
        }
        return $responseData;
    }

    public function listAllZoomUsers()
    {
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
            echo '<pre>';
            print_r($e->getMessage());
            exit;
        }

        $responseDataJson = $res->getBody()->getContents();
        return json_decode($responseDataJson);
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