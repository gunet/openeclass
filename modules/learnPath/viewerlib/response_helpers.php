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

// Minimal header helpers for scorm viewer endpoints.

function resp_no_cache_headers(): void {
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}

function resp_send_json($payload, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    resp_no_cache_headers();
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
}

function resp_return_json($payload): false|string {
    return json_encode($payload, JSON_UNESCAPED_UNICODE);
}

function resp_no_content(int $status = 204): void {
    http_response_code($status);
    resp_no_cache_headers();
}

function resp_send_fragment(string $html, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    resp_no_cache_headers();
    echo $html;
}
