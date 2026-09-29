<?php

require_once '../../include/baseTheme.php';
require_once 'process_functions.php';

header('Content-Type: application/json');

if (!isset($_POST['certificate_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing certificate_id']);
    exit;
}

$certificateId = intval($_POST['certificate_id']);
$result = certificate_thumbnails($certificateId);
echo json_encode(['success' => true, 'result' => $result]);

