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
use Session;

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
        global $langZoomCreateMeetingError;

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
            Session::Messages($langZoomCreateMeetingError);
            redirect_to_home_page($_SERVER['HTTP_REFERER'], true);
        }

        $responseDataJson = $res->getBody()->getContents();
        return json_decode($responseDataJson);
    }

    public function getAccessToken() : string
    {
        global $langZoomAccessTokenError;

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
            Session::Messages($langZoomAccessTokenError);
            redirect_to_home_page($_SERVER['HTTP_REFERER'], true);
        }
        return $dbToken->value;
    }

    public function isEnabled() : bool
    {
        return (
            !empty($this->getClientId())
            && !empty($this->getClientSecret())
            && !empty($this->getAccountId())
            && $this->getZoomWebAppType() == 'api'
        );
    }

    private function generateAccessToken() : string
    {
        global $langZoomAccessTokenError;

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
            Session::Messages($langZoomAccessTokenError);
            redirect_to_home_page($_SERVER['HTTP_REFERER'], true);
        }

        $responseDataJson = $res->getBody()->getContents();
        $responseData = json_decode($responseDataJson);
        return $responseData->access_token;
    }

    private function saveAccessTokenInDatabase(string $accessToken) : void
    {
        global $langZoomAccessTokenError;

        $query = "REPLACE INTO `config` (`key`, `value`) 
                VALUES ('zoomApiAccessToken', '".$accessToken."'), 
                ('zoomApiAccessTokenCreated', '".strtotime('now')."')";

        try {
            Database::get()->querySingle($query);
        } catch (Exception $e) {
            Session::Messages($langZoomAccessTokenError);
            redirect_to_home_page($_SERVER['HTTP_REFERER'], true);
        }
    }

    private function getAccountId()
    {
        return get_config('ext_zoom_accountId');
    }

    private function getClientId()
    {
        return get_config('ext_zoom_clientId');
    }

    private function getClientSecret()
    {
        return get_config('ext_zoom_clientSecret');
    }

    private function getZoomWebAppType()
    {
        $q = Database::get()->querySingle("SELECT webapp FROM tc_servers WHERE type = 'zoom'");

        if (
            !$q
            || empty($q->webapp)
        ) {
            return null;
        }
        return $q->webapp;
    }

    private function getAccessTokenCreation()
    {
        $q = Database::get()->querySingle("SELECT `value` AS `key_creation` 
                                                    FROM `config` 
                                                    WHERE `key` = 'zoomApiAccessTokenCreated'");
        return $q->key_creation;
    }
}