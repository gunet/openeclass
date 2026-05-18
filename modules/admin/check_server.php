<?php

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';

if ($uid) {
    $tenant = getCurrentTenant();

    if ($tenant->url) {
        if ($tenant->url_active) { // Tenant URL already active according to database
            die('ENABLED');
        }
        $host = trim(parse_url($tenant->url, PHP_URL_HOST), '.');
        if (isset($_SESSION['tenant_url'][$host])) {
            // Customer host DNS check passed, try getting status via web server
            $out = @file_get_contents($tenant->url . 'modules/admin/check_server.php');
            if ($out) {
                Database::get()->query('UPDATE tenant
                    SET url_active = 1 WHERE id = ?d', $tenant->id);
                $subject = $langTenantActivateURL;
                $body = varmsg(
                    $langTenantURLActivateText,
                    ['name' => $tenant->name, 'url' => $tenant->url]
                );
                send_mail('', '', '', get_config('email_helpdesk'), $subject, $body);
                die($out);
            }
        }
        // Try checking DNS - use dig to avoid caching
        $server = preg_quote(parse_url($urlServer, PHP_URL_HOST) . '.');
        $server_ip = preg_quote(gethostbyname($server));
        $out = shell_exec('dig +trace +nocmd +noall +answer ' . escapeshellarg($host) . '.');

        if (
            preg_match("/\sIN\s+A\s+$server_ip/m", $out) or
            preg_match("/\sIN\s+CNAME\s+$server/m", $out)
        ) {
            error_log("enabling tenant domain $host");
            // Keep DNS check status in session for next request
            $_SESSION['tenant_url'][$host] = 'ok';
            die('OK');
        } else {
            error_log("check $tenant->url - dig failed");
        }
    }
} elseif (isset($_SESSION['current_user_tenant'])) {
    // We have passed hostname check via web server and domain check in init.php
    die('ENABLED');
}

die('ERROR');
