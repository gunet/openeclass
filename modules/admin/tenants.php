<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2025, Greek Universities Network - GUnet
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

$require_admin = true;
$require_help = true;
$helpTopic = 'tenants';

require_once '../../include/baseTheme.php';

load_js('datatables');

$toolName = $langAdmin;
$pageName = $langTenants;

$navigation[] = ['url' => 'index.php', 'name' => $langAdmin];

/**
 * Check disk usage cron job status
 */
function getDiskUsageCronStatus(): array
{
    $cronTs = Database::get()->querySingle(
        "SELECT value FROM config WHERE `key` = 'disk_usage_cron_ts'"
    );

    $isEnabled = !empty($cronTs?->value);
    $isRunning = false;

    if ($isEnabled) {
        $lastRun = DateTime::createFromFormat('Y-m-d H:i', $cronTs->value);
        $threshold = (new DateTime())->sub(new DateInterval('PT1H'));
        
        // Check if cron ran within the last hour
        $isRunning = $lastRun && $lastRun >= $threshold;
    }

    return [
        'enabled' => $isEnabled,
        'running' => $isRunning
    ];
}

/**
 * Get cron status message configuration
 */
function getCronMessageConfig(bool $isRunning, bool $isEnabled): array
{
    global $langTenantsCronRunning, $langTenantsCronStopped, $langTenantsCronEnable;

    if ($isRunning) {
        return [
            'icon' => 'fa-check-circle',
            'message' => $langTenantsCronRunning,
            'class' => 'alert-success'
        ];
    }

    if ($isEnabled) {
        return [
            'icon' => 'fa-exclamation-triangle',
            'message' => $langTenantsCronStopped,
            'class' => 'alert-danger'
        ];
    }

    return [
        'icon' => 'fa-info-circle',
        'message' => $langTenantsCronEnable,
        'class' => 'alert-info'
    ];
}

/**
 * Process cron message with dynamic content
 */
function processCronMessage(string $message, array $replacements = []): string
{
    if (!empty($replacements)) {
        $message = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $message
        );
    }

    return preg_replace(
        '/\{(.*?)\}/',
        "<p class='text-center' style='padding-top: 5px'>" .
        "<button class='btn btn-default' data-bs-toggle='modal' data-bs-target='#tenantsCronInfoModal'>\\1</button>" .
        "</p>",
        $message
    );
}

/**
 * Fetch tenants with aggregated statistics
 */
function getTenantStatistics(): array
{
    return Database::get()->queryArray(
        "SELECT t.*, 
               h.lft, 
               h.rgt,
               COUNT(DISTINCT ud.user) AS total_users,
               COUNT(DISTINCT cd.course) AS total_courses,
               COALESCE(SUM(cru.disk_size), 0) AS disk_usage
        FROM tenant t
        LEFT JOIN hierarchy h ON h.id = t.department_id
        LEFT JOIN hierarchy h2 ON h2.lft BETWEEN h.lft AND h.rgt
        LEFT JOIN user_department ud ON ud.department = h2.id
        LEFT JOIN course_department cd ON cd.department = h2.id
        LEFT JOIN course_resource_usage cru ON cru.course_id = cd.course
        GROUP BY t.id"
    );
}

$data = [
    'disk_usage_cron_running' => false,
    'cron_message' => '',
    'cron_icon' => '',
    'cron_class' => ''
];

$cronStatus = getDiskUsageCronStatus();
$messageConfig = getCronMessageConfig($cronStatus['running'], $cronStatus['enabled']);

$data['disk_usage_cron_running'] = $cronStatus['running'];
$data['cron_icon'] = $messageConfig['icon'];
$data['cron_class'] = $messageConfig['class'];

if (!$cronStatus['running']) {
    global $webDir, $urlServer, $langTenantsCronEnableInstructions;
    
    $replacements = [
        '{webRoot}' => $webDir,
        '{cronURL}' => $urlServer . 'cron_disk_usage.php'
    ];
    
    $data['cron_message'] = processCronMessage(
        $messageConfig['message'],
        $replacements
    );
    $data['cron_instructions'] = str_replace(
        array_keys($replacements),
        array_values($replacements),
        $langTenantsCronEnableInstructions
    );
} else {
    $data['cron_message'] = $messageConfig['message'];
}

$data['tenants'] = getTenantStatistics();

view('admin.other.tenants.index', $data);
