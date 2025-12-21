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

$organizationLogoFullPath = '';
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

global $webDir;

$tenant_id = $_GET['id'];

if (!$tenant_id) {
    redirect_to_home_page("main/portfolio.php");
}

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

$tenant = getTenantById($tenant_id);
$tenantOptions = $tenant->options ? unserialize($tenant->options) : [];

$tenantLogo = getTenantOption($tenantOptions, 'imageUpload');
$tenantLogoSmall = getTenantOption($tenantOptions, 'imageUploadSmall');
$tenantFavicon = getTenantOption($tenantOptions, 'faviconUpload');
$contact_phone = q(getTenantOption($tenantOptions, 'contact_phone'));
$contact_address = q(getTenantOption($tenantOptions, 'contact_address'));
$contact_email = q(getTenantOption($tenantOptions, 'contact_email'));

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

    foreach (['contact_phone', 'contact_address', 'contact_email', 'imageUpload', 'imageUploadSmall', 'faviconUpload'] as $var) {
        if (isset($_POST[$var])) {
            $value = trim($_POST[$var]);
            if ($var == 'contact_email' and $value and !valid_email($value)) {
                Session::Messages(sprintf($msgInvalidEmail, q($value)), 'alert-danger');
            }

            $tenantInfo[$var] = $value;
        }
    }

    if (!empty($tenantInfo)) {
        $serialized = serialize($tenantInfo);
        Database::get()->query("UPDATE tenant SET options = ?s WHERE id = ?d", $serialized, $tenant_id);
    }

    $_SESSION['current_user_tenant'] = getTenantById($tenant_id);

    Session::flash('message', $langTenantUpdated);
    Session::flash('alert-class', 'alert-success');

    redirect_to_home_page("modules/admin/tenant_options.php?id=$tenant_id");
}

// Move uploaded files to final destination and update DB
function upload_images($tenantInfo)
{
    global $webDir, $tenantOptions, $tenant;

    $basePath = "/courses/tenant/$tenant->id/logos";
    make_dir($webDir . $basePath, 0755, true);

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

            if ($tenantOptions[$image]) {
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

$pageName = "$langTenantProfile - $tenant->name";

if ($tenantLogo) {
    $logo_field = "
        <img src='$tenantLogo' style='max-height:100px;max-width:150px;' alt='Logo upload' /> <a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?id=$tenant_id&delete_image=imageUpload'>$langDelete</a>
        <input type='hidden' name='imageUpload' value='$tenantLogo'>";
} else {
    $logo_field = "<input type='file' name='imageUpload' id='imageUpload' class='form-control-file'>";
}

if ($tenantLogoSmall) {
    $small_logo_field = "
        <img src='$tenantLogoSmall' style='max-height:100px;max-width:150px;' alt='Small logo upload' /> <a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?id=$tenant_id&delete_image=imageUploadSmall'>$langDelete</a>
        <input type='hidden' name='imageUploadSmall' value='$tenantLogoSmall'>";
} else {
    $small_logo_field = "<input type='file' name='imageUploadSmall' id='imageUploadSmall' class='form-control-file'>";
}

if ($tenantFavicon) {
    $faviconUpload = "
            <img src='$tenantFavicon' style='max-height:100px;max-width:150px;' alt='Favicon upload' /> <a class='btn deleteAdminBtn' href='$_SERVER[SCRIPT_NAME]?id=$tenant_id&delete_image=faviconUpload'>$langDelete</a>
            <input type='hidden' name='faviconUpload' value='$tenantFavicon'>";
} else {
    $faviconUpload = "<label for='faviconUpload' aria-label='$langFavicon'></label><input type='file' name='faviconUpload' id='faviconUpload'>";
}

load_js('datatables');

$tool_content .= action_bar([
    [
        'title' => $langBack,
        'url' => "{$urlAppend}modules/admin/index.php",
        'icon' => 'fa-reply',
        'level' => 'primary'
    ]
], false) . "

<form id='tenant_options_form' class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?id=$tenant_id' enctype='multipart/form-data' method='post'>
  <div class='container-fluid'>
    <div class='form-group'>
        <div class='col-sm-12 control-label-notes mb-2'>$langLogo <small>$langLogoNormal</small>:</div>
        <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
            $logo_field
        </div>
    </div>
    <div class='form-group mt-4'>
        <div class='col-sm-12 control-label-notes mb-2'>$langLogo <small>$langLogoSmall</small>:</div>
        <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
            $small_logo_field
        </div>
    </div>
    <div class='form-group mt-4'>
        <div class='col-sm-12 control-label-notes mb-2'>$langFavicon </div>
        <div class='col-sm-12 d-inline-flex justify-content-start align-items-center'>
            $faviconUpload
        </div>
    </div>

    <div class='form-group mt-4'>
      <label for='contact_phone' class='col-md-3 col-form-label'>$langPhone</label>
      <div class='col-md-9'>
        <input type='tel' class='form-control' name='contact_phone' id='contact_phone' value='$contact_phone'>
      </div>
    </div>

    <div class='form-group mt-4'>
      <label for='contact_email' class='col-md-3 col-form-label'>$langEmail</label>
      <div class='col-md-9'>
        <input type='email' class='form-control' name='contact_email' id='contact_email' value='$contact_email'>
      </div>
    </div>

    <div class='form-group mt-4'>
      <label for='contact_address' class='col-md-3 col-form-label'>$langPostMail</label>
      <div class='col-md-9'>
        <textarea id='contact_address' name='contact_address' class='form-control' rows='3'>$contact_address</textarea>
      </div>
    </div>";

$tool_content .= "
    <div class='form-group mt-4'>
      <div class='col-md-9 d-flex justify-content-end'>
        <input class='btn btn-primary' name='optionsSave' type='submit' value='$langSave'>
      </div>
    </div>
  </div>" . generate_csrf_token_form_field() . "
</form>";

$head_content .= "<script type='text/javascript'>
    $(document).ready(function() {

        var oTable = $('#admins-table').DataTable ({
            'bStateSave': true,
            'bProcessing': false,
            'bServerSide': false,
            'sScrollX': true,
            'responsive': true,
            'searchDelay': 1000,
            'lengthMenu': [10, 15, 20 , -1],
            'fnDrawCallback': function( oSettings ) {
                $('.table_td_body').each(function() {
                    $(this).trunk8({
                        lines: '3',
                        fill: '&hellip;<div class=\"clearfix\"></div><a style=\"float:right;\" href=\"$_SERVER[SCRIPT_NAME]?an_id='+ $(this).data('id')+'\">$langMore</div>'
                    })
                });
                $('#admins-table_wrapper .dt-search input').attr({
                    'class' : 'form-control input-sm ms-0 mb-3',
                    'placeholder' : '$langSearch...'
                });
                $('#admins-table_wrapper .dt-search label').attr('aria-label', '$langSearch');
                },
                'sPaginationType': 'full_numbers',
            'bSort': false,
            'oLanguage': {
                    'lengthLabels': {
                        '-1': '$langAllOfThem'
                    },                       
                    'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                    'sZeroRecords':  '" . $langNoResult . "',
                    'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                    'sInfoEmpty':    '',
                    'sInfoFiltered': '',
                    'sInfoPostFix':  '',
                    'sSearch':       '',
                    'oPaginate': {
                        'sFirst':    '&laquo;',
                        'sPrevious': '&lsaquo;',
                        'sNext':     '&rsaquo;',
                        'sLast':     '&raquo;'
                    }
                }
            });
    });
    </script>";

$tenant_admins = getTenantAdmins($tenant_id);
$tenant_admins_rows = "";

foreach ($tenant_admins as $admin) {
    $tenant_admins_rows .= "<tr>
            <td>$admin->id</td>
            <td>$admin->givenname</td>
            <td>$admin->surname</td>
            <td>$admin->email</td>
            <td>$admin->username</td>
        </tr>";
}

$tool_content .= "
    <div class='container-fluid'>
        <div class='col-md-9 mt-5 mb-5'>
            <h2> Διαχειριστές </h2>

            <div class='table-responsive'>
                <table id='admins-table' class='table-default '>
                    <thead>
                    <tr class='list-header'>
                        <th>ID</th>
                        <th>$langName</th>
                        <th>$langSurname</th>
                        <th>$langEmail</th>
                        <th>$langUsername</th>
                    </tr>
                    </thead>
                    <tbody>
                        $tenant_admins_rows
                    </tbody>
                </table>
            </div>
        </div>
    </div>";


draw($tool_content, 3, null, $head_content);
