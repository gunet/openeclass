<?php

namespace modules\tc\Zoom\Api;

require_once 'include/init.php';

use Database;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use modules\tc\Zoom\User\ZoomUser;

class Repository
{
    const ZOOM_API_BASE_URL =   'https://api.zoom.us';
    const DATETIME_FORMAT   =   'Y-m-d\TH:i:s.000\Z';
    const RECORDING_NONE    =   'none';
    const RECORDING_CLOUD   =   'cloud';
    const RECORDING_LOCAL   =   'local';
    const ACCESS_TOKEN_DURATION = 3500;

    /**
     * @var Client
     */
    private $client;
    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->dateTime = new DateTime();
    }

    public function createMeeting(ZoomUser $zoomUser, string $agenda, string $topic, string $date, string $auto_recording)
    {
        $accessToken = $this->getAccessToken();
        $record = self::RECORDING_NONE;
        $this->dateTime->createFromFormat('Y-m-d H:i:s', $date);

        if ($auto_recording === 'true') {
            $record = self::RECORDING_CLOUD;
        }

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $body = '{
              "agenda": "'.$agenda.'",
              "default_password": true,
              "pre_schedule": false,
              "settings": {
                "auto_recording": "'.$record.'"
              },
              "start_time": "'.$this->dateTime->format(self::DATETIME_FORMAT).'",
              "timezone": "Europe/Athens",
              "topic": "'.$topic.'"
        }';

        try {
            $res = $this->client->post(
                'https://api.zoom.us/v2/users/'.$zoomUser->id.'/meetings',
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
        return json_decode($responseDataJson);
    }

    public function getAccessToken() : string
    {
        $accessTokenCreated = $this->getAccessTokenCreation();

        if (
            empty($accessTokenCreated)
            || $accessTokenCreated === 'null'
            || (strtotime('now') - $accessTokenCreated >= self::ACCESS_TOKEN_DURATION)
        ) {
            $generateAccessToken = $this->generateAccessToken();
            $this->saveAccessTokenInDatabase($generateAccessToken);
        }
        $dbToken = Database::get()->querySingle("SELECT `value` FROM `config` WHERE `key` = 'zoomApiAccessToken'");
        if (empty($dbToken->value)) {
            die('Something went wrong while generating access token');
        }
        return $dbToken->value;
    }

    private function generateAccessToken() : string
    {
        $clientId = $this->getClientId();
        $clientSecret = $this->getClientSecret();
        $accountId = $this->getAccountId();

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode($clientId.':'.$clientSecret),
        ];

        try {
            $res = $this->client->request(
                'POST',
                self::ZOOM_API_BASE_URL . '/oauth/token?grant_type=account_credentials&account_id=' . $accountId,
                [
                    'headers' => $headers
                ]
            );
        } catch (Exception|GuzzleException $e) {
            die($e);
        }

        $responseDataJson = $res->getBody()->getContents();
        $responseData = json_decode($responseDataJson);
        return $responseData->access_token;
    }

    private function saveAccessTokenInDatabase(string $accessToken) : void
    {
        $query = "REPLACE INTO `config` (`key`, `value`) 
                VALUES ('zoomApiAccessToken', '".$accessToken."'), 
                ('zoomApiAccessTokenCreated', '".strtotime('now')."')";

        try {
            Database::get()->querySingle($query);
        } catch (Exception $e) {
            die($e);
        }
    }

    private function getAccountId() : string
    {
        return get_config('ext_zoom_accountId');
    }

    private function getClientId() : string
    {
        return get_config('ext_zoom_clientId');
    }

    private function getClientSecret() : string
    {
        return get_config('ext_zoom_clientSecret');
    }

    private function getAccessTokenCreation()
    {
        $q = Database::get()->querySingle("SELECT `value` AS `key_creation` 
                                                    FROM `config` 
                                                    WHERE `key` = 'zoomApiAccessTokenCreated'");
        return $q->key_creation;
    }

    private function formatRequestDatetime(string $date)
    {
        return date(self::DATETIME_FORMAT, strtotime($date));
    }
}