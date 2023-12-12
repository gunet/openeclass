<?php

namespace modules\tc\Zoom;

use Database;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class Repository
{
    private function generateAccessToken() : string
    {
        $clientId = 'IdFyyqIWQp2HWDaYvXwYw';
        $clientSecret = 'H65Za7wHA3bKBLRUJR8etvP2h3iylTnh';
        $accountId = 'mTa_VOkuS9WweHm52L1pQg';
        $zoomBaseUrl = 'https://api.zoom.us';

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode($clientId.':'.$clientSecret),
        ];

        $client = new Client();
        $request = new Request('POST', $zoomBaseUrl . '/oauth/token?grant_type=account_credentials&account_id=' . $accountId, $headers);
        $res = $client->sendAsync($request)->wait();
        $responseDataJson = $res->getBody()->getContents();
        $responseData = json_decode($responseDataJson);

        return $responseData->access_token;
    }

    private function saveAccessTokenInDatabase(string $accessToken) : void
    {
        $accessToken = $this->generateAccessToken();
        $query = "INSERT INTO api_token (token, name, comments, ip, enabled) 
                VALUES (".$accessToken.", 'ZoomApiAccessToken', 'Zoom API Access Token', 'localhost', 1)";

        try {
            Database::get()->query($query);
        } catch (Exception $e) {
            die($e);
        }
    }

    private function checkAccessTokenValidity()
    {
        //
    }

    public function getUsers()
    {
        $accessToken = $this->generateAccessToken();
    }
}