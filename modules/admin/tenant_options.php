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

$urlMessage = '';
$urlStatus = '';
$tenantUrl = q($tenant->url);
$customUrlEnabled = false;
$checkModal = '';
$tenantName = $tenant->name;
$tenantUrlActive = $tenant->url_active;
$tenantLogo = getTenantOption($tenantOptions, 'imageUpload');
$tenantLogoSmall = getTenantOption($tenantOptions, 'imageUploadSmall');
$tenantFavicon = getTenantOption($tenantOptions, 'faviconUpload');
$contact_phone = q(getTenantOption($tenantOptions, 'contact_phone'));
$contact_address = q(getTenantOption($tenantOptions, 'contact_address'));
$contact_email = q(getTenantOption($tenantOptions, 'contact_email'));
$platform_title = q(getTenantOption($tenantOptions, 'platform_title'));
$platform_intro = q(getTenantOption($tenantOptions, 'platform_intro'));

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

if ($tenantUrl) {
    $host = parse_url($tenantUrl, PHP_URL_HOST);
    if ($tenantUrlActive) {
        $urlStatus = 'has-success';
        $urlMessage = "<p class='text-success small'>$langTenantURLActivated</p>";
        $customUrlEnabled = true;
    } else {
        $server = q(parse_url($urlServer, PHP_URL_HOST));
        $zone = q(preg_replace('/^[^.]+\./', '', $host));
        $hostName = q(preg_replace('/\..*$/', '', $host));
        $urlStatus = 'has-warning';
        $urlMessage = "
            <p class='text-warning small'>
                $langTenantURLActivationInfo1
            </p>
            <p class='text-warning small'>" .
            sprintf($langTenantURLActivationInfo2, q($host), $zone) . "
            </p>
            <code class='d-block p-2 my-2 bg-light text-muted'>
                $hostName CNAME $server.
            </code>
            <p class='small'>
                <button type='button' class='btn btn-warning' data-bs-toggle='modal' data-bs-target='#checkModal'>$langTenantURLCheckActivate</button>
            </p>";
        $customerUrl = $tenantUrl . 'modules/auth/redirect.php?token=' . session_id();
        $checkModal = "
        <div class='modal fade' id='checkModal' tabindex='-1' aria-labelledby='checkModalLabel' aria-hidden='true'>
          <div class='modal-dialog'>
            <div class='modal-content'>
              <div class='modal-header'>
                <h5 class='modal-title' id='checkModalLabel'>$langTenantActivateURL</h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
              </div>
              <div class='modal-body text-center'>
                <p id='dns-check-result'>
                    <span class='spinner-border spinner-border-sm' role='status' aria-hidden='true'></span>
                    $langTenantURLChecking
                </p>
              </div>
              <div class='modal-footer'>
                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal' id='modal-close-btn'>$langCancel</button>
              </div>
            </div>
          </div>
        </div>
        <script>
            var checkServer = function () {
                $.get('{$urlAppend}modules/admin/check_server.php', function (data) {
                    var msg;
                    if (data == 'OK') {
                        msg = '$langTenantURLCheckSuccess<br><span class=\"spinner-border spinner-border-sm\" role=\"status\" aria-hidden=\"true\"></span> $langTenantURLActivating';
                        setTimeout(checkServer, 5000); // Poll every 5 seconds
                    } else if (data == 'ENABLED') {
                        msg = '$langTenantURLActivated';
                        $('#modal-close-btn')
                            .removeClass('btn-secondary')
                            .addClass('btn-primary')
                            .html('$langTenantGotoURL')
                            .click(function () {
                                window.location.href = '{$customerUrl}';
                            });
                    } else {
                        $('#dns-check-result').removeClass('text-center');
                        msg = '" .
            varmsg(
                $GLOBALS['langTenantURLCheckFail'],
                ['host' => $host, 'server' => $server]
            ) . "';
                    }
                    $('#dns-check-result').html('<p>' + msg + '</p>');
                });
            };
            $(function () {
                var checkModal = document.getElementById('checkModal');
                checkModal.addEventListener('shown.bs.modal', function () {
                    checkServer();
                });
            });
        </script>";
    }
}

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
    </div>

    <div class='form-group mt-4'>
      <label for='platform_title' class='col-md-3 col-form-label'>$langSiteTitle</label>
      <div class='col-md-9'>
        <input type='text' class='form-control' name='platform_title' id='platform_title' value='$platform_title'>
      </div>
    </div>

    <div class='form-group mt-4'>
      <label for='platform_intro' class='col-md-3 col-form-label'>$langSiteDescr</label>
      <div class='col-md-9'>
        " . rich_text_editor('platform_intro', 4, 50, $platform_intro, options: array('id' => 'platform_intro')) . "
      </div>
    </div>
    
    <div class='form-group mt-4'>
      <label for='urlField' class='col-md-3 col-form-label'>$langTenantURL</label>
      <div class='col-md-9'>
        <small class='d-block text-muted'>$langTenantURLText</small>
        <input type='text' class='form-control' name='url' id='urlField' placeholder='https://eclass.example.com/' value='$tenantUrl'>
        $urlMessage
      </div>
    </div>";

$tool_content .= "
    <div class='form-group mt-5'>
        <div class='col-sm-12'>
            <h4 class='mb-3'>$langTenantConfig</h4>

            <div class='checkbox'>
                <label class='label-container'>
                    <input type='checkbox'
                           name='allow_teacher_clone_course'
                           value='1'
                           $cbox_allow_teacher_clone_course>
                    <span class='checkmark'></span>
                    $lang_allow_teacher_clone_course
                </label>
            </div>
        </div>
    </div>";

$tool_content .= "
    <div class='form-group mt-4'>
      <div class='col-md-9 d-flex justify-content-end'>
        <input class='btn btn-primary' name='optionsSave' type='submit' value='$langSave'>
      </div>
    </div>
  </div>" . generate_csrf_token_form_field() . "
</form>" . $checkModal;

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
