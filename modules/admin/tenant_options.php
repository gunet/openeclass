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

$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/sendMail.inc.php';
require_once 'modules/admin/tenant_functions.php';

if (!get_config('enable_tenant')) {
    redirect_to_home_page("modules/admin/index.php");
}

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
if ($is_admin) {
    $navigation[] = array('url' => 'tenants.php', 'name' => $langTenants);
}

if (isset($_GET['id'])) {
    $tenant_id = $_GET['id'];
} else {
    redirect_to_home_page("main/portfolio.php");
}

load_js('datatables');

if (!$is_admin) {
    $tenantUserIds = array_map(fn($u) => intval($u->id), getTenantUsers([], $tenant_id));
    $user_belongs_to_tenant = in_array($uid, $tenantUserIds);

    // Restrict access to tenant profile when user is not an admin and does not belong to the tenant.
    if (!$user_belongs_to_tenant) {
        Session::flash('message', $langTenantProfileEditNotAllowed);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("main/portfolio.php");
    }
}

$host = $server = $organizationLogoFullPath = $urlMessage = $customerUrl = '';
$tenant = getTenantById($tenant_id);
$tenantOptions = $tenant->options ? unserialize($tenant->options) : [];
$tenantUrl = q($tenant->url);
$tenantName = $tenant->name;
$tenantUrlActive = $tenant->url_active;
$tenantLogo = getTenantOption($tenantOptions, 'imageUpload');
$tenantLogoSmall = getTenantOption($tenantOptions, 'imageUploadSmall');
$tenantFavicon = getTenantOption($tenantOptions, 'faviconUpload');
$contact_phone = q(getTenantOption($tenantOptions, 'contact_phone'));
$contact_address = q(getTenantOption($tenantOptions, 'contact_address'));
$contact_email = q(getTenantOption($tenantOptions, 'contact_email'));
$platform_title = q(getTenantOption($tenantOptions, 'platform_title'));
$platform_intro = getTenantOption($tenantOptions, 'platform_intro');

$allow_teacher_clone_course = getTenantOption($tenantOptions, 'allow_teacher_clone_course');
$cbox_allow_teacher_clone_course = $allow_teacher_clone_course ? 'checked' : '';

if (isset($_GET['delete_image'])) {
    global $webDir, $tenant, $tenantOptions;

    $logo_type = $_GET['delete_image'];
    $image_path = $webDir . $tenantOptions[$logo_type];

    unlink($image_path);
    unset($tenantOptions[$logo_type]);
    $serialized_data = serialize($tenantOptions);
    Database::get()->query("UPDATE tenant SET options = ?s WHERE id = ?d", $serialized_data, $tenant_id);
    $_SESSION['current_user_tenant'] = getTenantById($tenant_id);

    redirect_to_home_page("modules/admin/tenant_options.php?id=$tenant_id");
}

if (isset($_POST['optionsSave'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }

    $tenantInfo = [];
    $tenantInfo = upload_images($tenantInfo);
    $tenantInfo['allow_teacher_clone_course'] = isset($_POST['allow_teacher_clone_course']) ? 1 : 0;

    foreach (
        [
            'contact_phone',
            'contact_address',
            'contact_email',
            'imageUpload',
            'imageUploadSmall',
            'faviconUpload',
            'platform_title',
            'platform_intro',
            'allow_teacher_clone_course',
        ] as $var
    ) {
        if (isset($_POST[$var])) {
            $value = trim($_POST[$var]);
            if ($var == 'contact_email' and $value and !valid_email($value)) {
                Session::Messages(sprintf($msgInvalidEmail, q($value)), 'alert-danger');
            }
            $tenantInfo[$var] = $value;
        }
    }

    // platform URL
    $validUrl = true;
    if (isset($_POST['url']) and $_POST['url']) {
        $newUrl = strtolower(trim($_POST['url']));
        if ($tenantUrl != $newUrl) {
            if (!preg_match('|^https://[.a-zA-Z0-9-]+/?$|', $newUrl)) {
                if (preg_match('|^[.a-zA-Z0-9-]+$|', $newUrl) and !preg_match('/\.\.|--/', $newUrl)) {
                    $newUrl = "https://$newUrl/";
                } else {
                    Session::Messages(sprintf($langTenantInvalidURL, q($_POST['url'])), 'alert-danger');
                    $validUrl = false;
                }
            }
            if (substr($newUrl, -1) != '/') {
                $newUrl .= '/';
            }
            if ($validUrl) {
                Database::get()->query("UPDATE tenant SET url = ?s, url_active = 0 WHERE id = ?d", $newUrl, $tenant_id);
                $subject = $langTenantURLChange;
                $body = varmsg(
                    $langTenantURLChangeText,
                    ['name' => $tenantName, 'oldurl' => $tenantUrl, 'newurl' => $newUrl]
                );
                send_mail('', '', '', get_config('email_helpdesk'), $subject, $body);
                if ($tenantUrl) {
                    Session::Messages($langTenantURLRegisterInfo, 'alert-info');
                } else {
                    Session::Messages($langTenantURLRegisterActivate, 'alert-success');
                }
            }
        }
    } elseif ($tenantUrl) {
        Database::get()->query("UPDATE tenant SET url = '', url_active = 0 WHERE id = ?d", $tenant_id);
        $subject = $langTenantURLDelete;
        $body = varmsg(
            $langTenantURLDeleteText,
            ['name' => $tenantName, 'url' => $tenantUrl]
        );
        send_mail('', '', '', get_config('email_helpdesk'), $subject, $body);
        Session::Messages(sprintf($langTenantURLDeleted, $tenantUrl), 'alert-info');
    }

    if (!empty($tenantInfo)) {
        $serialized = serialize($tenantInfo);
        Database::get()->query("UPDATE tenant SET options = ?s WHERE id = ?d", $serialized, $tenant_id);
    }

    // Update the session’s current-tenant cache only for regular users.
    // Admins can manage any tenant, so we avoid binding their session to one tenant record.
    if (!$is_admin) {
        $_SESSION['current_user_tenant'] = getTenantById($tenant_id);
    }
    Session::flash('message', $langTenantUpdated);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/tenant_options.php?id=$tenant_id");
}


$toolName = "$langWhiteLabelΤenant - $tenant->name";

if ($tenantUrl) {
    $host = parse_url($tenantUrl, PHP_URL_HOST);
    if ($tenantUrlActive) {
        $urlMessage = "<p class='text-success small'>$langTenantURLActivated</p>";
    } else {
        $customerUrl = $tenantUrl . 'modules/auth/redirect.php?token=' . session_id();
        $server = q(parse_url($urlServer, PHP_URL_HOST));
        $zone = q(preg_replace('/^[^.]+\./', '', $host));

        $urlMessage = "
            <p class='text-warning small'>
                $langTenantURLActivationInfo1
            </p>
            <p class='text-warning small'>" .
            sprintf($langTenantURLActivationInfo2, q($host), $zone) . "
            </p>
            <code class='d-block p-2 my-2 bg-light text-muted'>
                "  . q(preg_replace('/\..*$/', '', $host)) . " CNAME $server.
            </code>
            <p class='small'>
                <button type='button' class='btn btn-warning' data-bs-toggle='modal' data-bs-target='#checkModal'>$langTenantURLCheckActivate</button>
            </p>";
    }
}

if ($is_admin) {
    $back_url = "{$urlAppend}modules/admin/tenants.php";
} else {
    $back_url = "{$urlAppend}modules/admin/index.php";
}

$action_bar = action_bar([
    [
        'title' => $langBack,
        'url' => $back_url,
        'icon' => 'fa-reply',
        'level' => 'primary'
    ]
], false);


$data['rich_text_editor'] = rich_text_editor('platform_intro', 4, 50, $platform_intro, options: array('id' => 'platform_intro'));
$data['cbox_allow_teacher_clone_course'] = $cbox_allow_teacher_clone_course;
$data['tenantLogo'] = $tenantLogo;
$data['tenantLogoSmall'] = $tenantLogoSmall;
$data['tenantFavicon'] = $tenantFavicon;
$data['tenantUrl'] = $tenantUrl;
$data['tenantUrlActive'] = $tenantUrlActive;
$data['customerUrl'] = $customerUrl;
$data['urlMessage'] = $urlMessage;
$data['platform_title'] = $platform_title;
$data['action_bar'] = $action_bar;
$data['tenant_admins'] = $tenant_admins = getTenantAdmins($tenant_id);
$data['tenant_id'] = $tenant_id;
$data['contact_email'] = $contact_email;
$data['contact_phone'] = $contact_phone;
$data['contact_address'] = $contact_address;
$data['platform_intro'] = $platform_intro;
$data['host'] = $host;
$data['server'] = $server;

view('admin.other.tenants.options', $data);

// Move uploaded files to the final destination and update DB
function upload_images($tenantInfo)
{
    global $webDir, $tenantOptions, $tenant;

    $basePath = "/courses/tenant/$tenant->id/logos";
    make_dir($webDir . $basePath);

    $images = [
        'imageUpload',
        'imageUploadSmall',
        'faviconUpload'
    ];

    foreach ($images as $image) {
        if (isset($_FILES[$image]) && is_uploaded_file($_FILES[$image]['tmp_name'])) {
            $file_name = $_FILES[$image]['name'];
            $ext = strtolower(get_file_extension($file_name));
            validateUploadedFile($file_name, 2);

            if (isset($tenantOptions[$image])) {
                $fullPath = $webDir . $tenantOptions[$image];
                unlink($fullPath);
                $fullPath = dirname($fullPath);
                if (!glob($fullPath . '/*')) {
                    rmdir($fullPath);
                }
            }
            $imagePath = "$basePath/$image.$ext";
            move_uploaded_file($_FILES[$image]['tmp_name'], $webDir . $imagePath);
            $tenantInfo[$image] = $imagePath;
        }
    }
    return $tenantInfo;
}