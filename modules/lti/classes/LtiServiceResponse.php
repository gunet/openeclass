<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2024  Greek Universities Network - GUnet
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
 *The Lti Service Response class.
 */
class LtiServiceResponse {

    // HTTP response code.
    private int $code;
    // HTTP response reason.
    private string $reason;
    // HTTP request method.
    private string $requestmethod;
    // HTTP request accept header.
    private string $accept;
    // HTTP response content type.
    private string $contenttype;
    // HTTP request body.
    private string $data;
    // string HTTP response body.
    private string $body;
    // HTTP response codes.
    private array $responsecodes;
    // HTTP additional headers.
    private array $additionalheaders;

    /**
     * Class constructor.
     */
    public function __construct() {

        $this->code = 200;
        $this->reason = '';
        $this->requestmethod = $_SERVER['REQUEST_METHOD'];
        $this->accept = '';
        $this->contenttype = '';
        $this->data = '';
        $this->body = '';
        $this->responsecodes = array(
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            300 => 'Multiple Choices',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            415 => 'Unsupported Media Type',
            500 => 'Internal Server Error',
            501 => 'Not Implemented'
        );
        $this->additionalheaders = array();

    }

    /**
     * Get the response code.
     *
     * @return int
     */
    public function getCode(): int {
        return $this->code;
    }

    /**
     * Set the response code.
     *
     * @param int $code Response code
     */
    public function setCode(int $code): void {
        $this->code = $code;
        $this->reason = '';
    }

    /**
     * Get the response reason.
     *
     * @return string
     */
    public function getReason(): string {
        $code = $this->code;
        if (($code < 200) || ($code >= 600)) {
            $code = 500;  // Status code must be between 200 and 599.
        }
        if (empty($this->reason) && array_key_exists($code, $this->responsecodes)) {
            $this->reason = $this->responsecodes[$code];
        }
        // Use generic reason for this category (based on first digit) if a specific reason is not defined.
        if (empty($this->reason)) {
            $this->reason = $this->responsecodes[intval($code / 100) * 100];
        }
        return $this->reason;
    }

    /**
     * Set the response reason.
     *
     * @param string $reason Reason
     */
    public function setReason(string $reason): void {
        $this->reason = $reason;
    }

    /**
     * Get the request method.
     *
     * @return string
     */
    public function getRequestMethod(): string {
        return $this->requestmethod;
    }

    /**
     * Get the request accept header.
     *
     * @return string
     */
    public function getAccept(): string {
        return $this->accept;
    }

    /**
     * Set the request accept header.
     *
     * @param string $accept Accept header value
     */
    public function setAccept(string $accept): void {
        $this->accept = $accept;
    }

    /**
     * Get the response content type.
     *
     * @return string
     */
    public function getContentType(): string {
        return $this->contenttype;
    }

    /**
     * Set the response content type.
     *
     * @param string $contenttype Content type
     */
    public function setContentType(string $contenttype): void {
        $this->contenttype = $contenttype;
    }

    /**
     * Get the request body.
     *
     * @return string
     */
    public function getRequestData(): string {
        return $this->data;
    }

    /**
     * Set the response body.
     *
     * @param string $data Body data
     */
    public function setRequestData(string $data): void {
        $this->data = $data;
    }

    /**
     * Get the response body.
     *
     * @return string
     */
    public function getBody(): string {
        return $this->body;
    }

    /**
     * Set the response body.
     *
     * @param string $body Body data
     */
    public function setBody(string $body): void {
        $this->body = $body;
    }

    /**
     * Add an additional header.
     *
     * @param string $header The new header
     */
    public function addAdditionalHeader(string $header): void {
        array_push($this->additionalheaders, $header);
    }

    /**
     * Send the response.
     */
    public function send(): void {
        header("HTTP/1.0 {$this->code} {$this->getReason()}");
        foreach ($this->additionalheaders as $header) {
            header($header);
        }
        if ((($this->code >= 200) && ($this->code < 300)) || !empty($this->body)) {
            if (!empty($this->contenttype)) {
                header("Content-Type: {$this->contenttype}; charset=utf-8");
            }
            if (!empty($this->body)) {
                echo $this->body;
            }
        } else if ($this->code >= 400) {
            header("Content-Type: application/json; charset=utf-8");
            $body = new \stdClass();
            $body->status = $this->code;
            $body->reason = $this->getReason();
            $body->request = new \stdClass();
            $body->request->method = $_SERVER['REQUEST_METHOD'];
            $body->request->url = $_SERVER['REQUEST_URI'];
            if (isset($_SERVER['HTTP_ACCEPT'])) {
                $body->request->accept = $_SERVER['HTTP_ACCEPT'];
            }
            if (isset($_SERVER['CONTENT_TYPE'])) {
                $body->request->contentType = explode(';', $_SERVER['CONTENT_TYPE'], 2)[0];
            }
            echo json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
    }

}