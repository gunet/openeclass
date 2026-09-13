<?php

require_once '../include/init.php';

if (!isset($_GET['domain'])) {
    http_response_code(400);
}

$q = Database::get()->queryArray('SELECT url FROM tenant WHERE url LIKE ?s', 'https://' . $_GET['domain'] . '/');
foreach ($q as $item) {
    $host = parse_url($item->url, PHP_URL_HOST);
    if ($host == $_GET['domain']) {
        http_response_code(200);
        exit;
    }
}

http_response_code(404);
