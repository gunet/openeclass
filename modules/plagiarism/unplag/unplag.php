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

require_once 'vendor/autoload.php';

class UnPlag extends Plagiarism {

    const DEBUGLEVEL = Debug::ERROR;

    public function isFileSubmitted($fileID) {
        return $this->getFileID($fileID) > 0;
    }

    public function submitFile($fileID, $fileLocation, $filename = null) {
        return $this->submitFileToServer($fileID, $fileLocation, $filename) && $this->requestReportFromServer($fileID);
    }

    public function getResults($fileID) {
        $result = $this->getFileResults($fileID);
        if ($result && $result->progress == 1)
            $this->getPDF($fileID, $result);
        return $result;
    }

    protected function getType() {
        return 1;
    }

    /**
     *
     * @param type $fileID
     * @param PlagiarismResult $result
     */
    private function getPDF($fileID, $result) {
        $submitID = $this->getSubmissionID($fileID);
        try {
            $response = $this->getClient()->request('POST', 'check/generate_pdf', [
                'json' => [
                    'id' => $submitID
                ],
                'headers' => [
                    'Accept' => 'application/json' // Force unplag to give response in JSON
                ]
            ]);
            $pdfResult = json_decode($response->getBody(), true);
            if ($pdfResult['pdf_report']['download_url']) {
                $result->ready = TRUE;
                $result->pdfURL = $pdfResult['pdf_report']['download_url'];
            }
        } catch (Exception $ex) {
            Debug::message($ex->getMessage(), UnPlag::DEBUGLEVEL);
        }
    }

    /**
     *
     * @param type $fileID
     * @return PlagiarismResult
     */
    private function getFileResults($fileID) {
        $submitID = $this->getSubmissionID($fileID);
        if ($submitID) {
            try {
                $response = $this->getClient()->request('GET', 'check/get', [
                    'query' => [
                        'id' => $submitID
                    ],
                    'headers' => [
                        'Accept' => 'application/json' // Force unplag to give response in JSON
                    ]
                ]);
                $result = json_decode($response->getBody(), true);
                return new PlagiarismResult($result['check']['progress'], $result['check']['report']['view_url']);
            } catch (Exception $ex) {
                Debug::message($ex->getMessage(), UnPlag::DEBUGLEVEL);
            }
        }
        return null;
    }

    private function requestReportFromServer($fileID) {
        $subID = $this->getSubmissionID($fileID);
        if ($subID > 0)
            return TRUE;
        try {
            $response = $this->getClient()->request('POST', 'check/create', [
                'json' => [// Encode body as application/json
                    'type' => 'web',
                    'file_id' => $this->getFileID($fileID),
                    'options' => [
                        'sensitivity' => '0.1',
                        'exclude_self_plagiarism' => '1'
                    ]
                ],
                'headers' => [
                    'Accept' => 'application/json' // Force unplag to give response in JSON
                ]
            ]);
            $result = json_decode($response->getBody(), true);
            if ($result['check']['id']) {
                $this->createSubmission($fileID, $result['check']['id']);
                return TRUE;
            }
        } catch (Exception $ex) {
            Debug::message($ex->getMessage(), UnPlag::DEBUGLEVEL);
        }
        return FALSE;
    }

    private function submitFileToServer($fileID, $fileLocation, $filename = null) {

        if ($this->isFileSubmitted($fileID))
            return TRUE;

        try {
            $response = $this->getClient()->post('file/upload', [//send HTTP POST request
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen("$fileLocation", 'r') //resource will be automatically encoded by guzzle
                    ],
                    [
                        'name' => 'format',
                        'contents' => strtolower(pathinfo(basename($fileLocation), PATHINFO_EXTENSION)) //specify format of file
                    ],
                    [
                        'name' => 'name',
                        'contents' => $filename //optional parameter name
                    ]
                ],
                'headers' => [
                    'Accept' => 'application/json' // Force unplag to give response in JSON
                ]
            ]);
            $result = json_decode($response->getBody(), true);
            if ($result['file']['id']) {
                $this->createRemoteFileID($fileID, $result['file']['id']);
                return TRUE;
            }
        } catch (Exception $ex) {
            Debug::message($ex->getMessage(), UnPlag::DEBUGLEVEL);
        }
        return FALSE;
    }

    private function getClient() {
        $app = ExtAppManager::getApp("unplag");
        if (!$app->isEnabled()) {
            return null;
        }

        $key = $app->getParam(UnplagApp::APIKEY)->value();
        $secret = $app->getParam(UnplagApp::APISECRET)->value();
        if (!$key || !$secret)
            return null;

        // Create handler stack to use oauth with guzzle.
        $stack = \GuzzleHttp\HandlerStack::create();

        // Create oauth middleware.
        $middleware = new \GuzzleHttp\Subscriber\Oauth\Oauth1([
            'consumer_key' => $key,
            'consumer_secret' => $secret,
            'token_secret' => '',
            'token' => ''
        ]);

        // Add oauth middle to handler stack.
        $stack->push($middleware);

        // Create guzzle client using Unplag API base url and oauth handler stack.
        $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://unplag.com/api/v2/', // the final '/' IS important!
            'handler' => $stack,
            'auth' => 'oauth'
        ]);
        return $client;
    }

}
