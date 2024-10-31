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

use GuzzleHttp\Client;
use modules\tc\Zoom\Api\Repository;
use modules\tc\Zoom\User\ZoomUserRepository;

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/admin/extconfig/externals.php';

$app = ExtAppManager::getApp('zoom');
$toolName = $langConfig . ' ' . $app->getDisplayName();
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

$client = new Client();
$zoomApiRepo = new Repository($client);
$zoomUserRepo = new ZoomUserRepository($client, $zoomApiRepo);

load_js('select2');

$head_content .= "<script type='text/javascript'>
    function doSelectedCourses() {
        let selectedVals = $('#select-courses').val();
        let i = 0;
        let csvSelection = '';
        
        // comma separate selection and update field
        while (i < selectedVals.length) {
            if (csvSelection.length > 0) {
                csvSelection = csvSelection.concat(',', selectedVals[i]);
            } else {
                csvSelection = csvSelection.concat(selectedVals[i]);
            }
            i++;
        }
        $('#enabled-courses').val(csvSelection);
        
        // remove allcourses selection when selected other courses
        if (selectedVals.length > 1) {
            let index = selectedVals.indexOf('0');
            if (index > -1) {
                selectedVals.splice(index, 1);
                $('#select-courses').val(selectedVals).trigger('change');
            }
        }
        // restore all courses selection when deselected other courses
        if (selectedVals.length <= 0) {
            selectedVals.push(0);
            $('#select-courses').val(selectedVals).trigger('change');
        }
    }
    
    $(document).ready(function () {        
        
        $('#custom_zoom_url').change(function() {
            if($(this).prop('checked')) {
                $('#default_zoom_url').prop('disabled', true);
            } else {
                $('#default_zoom_url').prop('disabled', false);
            }
        });
        
        $('#select-courses').select2();
        $('#selectAll').click(function(e) {
            e.preventDefault();
            let stringVal = [];
            $('#select-courses').find('option').each(function(){
                if ($(this).val() != 0) {
                    stringVal.push($(this).val());
                }
            });
            $('#select-courses').val(stringVal).trigger('change');
        });
        $('#removeAll').click(function(e) {
            e.preventDefault();
            let stringVal = [];
            stringVal.push(0);
            $('#select-courses').val(stringVal).trigger('change');
        });
        $('#select-courses').change(function(e) {
            doSelectedCourses();
        });
        doSelectedCourses();
    });
</script>";

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if ($_POST['submit'] == 'clear') {
        foreach ($app->getParams() as $param) {
            $param->setValue('');
            $param->persistValue();
        }
        Session::flash('message', $langFileUpdatedSuccess);
        Session::flash('alert-class', 'alert-info');
    } else {
        $result = $app->storeParams();
        if (isset($_POST['enabled'])) {
            $enabled = 'true';
        } else {
            $enabled = 'false';
        }
        if ($_POST['enabledcourses'] == 0) {
            $all_courses = 1;
        } else {
            $all_courses = 0;
        }
        if (isset($_POST['clientId']) && isset($_POST['clientSecret']) && isset($_POST['accountId'])) {
            Database::get()->query("INSERT INTO tc_servers (`type`, hostname, api_url, enabled, all_courses, webapp, enable_recordings) 
                                    VALUES('zoom', ?s, ?s, ?s, ?s, ?s, true)
                                ON DUPLICATE KEY UPDATE enabled = ?s, all_courses = ?s, webapp = ?s, enable_recordings = true",
                                $app::ZOOMDEFAULTURL, $app::ZOOMDEFAULTURL, $enabled, $all_courses, $app::API, $enabled, $all_courses, $app::API);
            $zoomUserRepo->syncZoomUsersTable();
        } else {
            Database::get()->query("INSERT INTO tc_servers (`type`, hostname, api_url, enabled, all_courses, webapp, enable_recordings) 
                                            VALUES('zoom', ?s, ?s, ?s, ?s, ?s, true)
                                        ON DUPLICATE KEY UPDATE enabled = ?s, all_courses = ?s, webapp = ?s, enable_recordings = true",
                $app::ZOOMDEFAULTURL, $app::ZOOMDEFAULTURL, $enabled, $all_courses, $app::CUSTOM, $enabled, $all_courses, $app::CUSTOM);
        }

        Database::get()->query("INSERT INTO tc_servers (`type`, hostname, api_url, enabled, all_courses) 
                                            VALUES('zoom', ?s, ?s, ?s, ?s)
                                        ON DUPLICATE KEY UPDATE enabled = ?s, all_courses = ?s",
            $app::ZOOMURL, $app::ZOOMURL, $enabled, $all_courses, $enabled, $all_courses);

        $tc_id = Database::get()->querySingle("SELECT id FROM tc_servers WHERE `type` = 'zoom'")->id;
        Database::get()->query("DELETE FROM course_external_server WHERE external_server = ?d", $tc_id);
        if (($all_courses == 0) and count($_POST['tc_courses']) > 0) {
            foreach ($_POST['tc_courses'] as $tc_courses) {
                Database::get()->query("INSERT INTO course_external_server (course_id, external_server) 
                                              VALUES (?d, ?d)", $tc_courses, $tc_id);
            }
        }
        if ($result) {
            Session::flash('message', $result);
            Session::flash('alert-class', 'alert-danger');
        } else {
            Session::flash('message', $langFileUpdatedSuccess);
            Session::flash('alert-class', 'alert-success');
        }
    }
    redirect_to_home_page($app->getConfigUrl());

} elseif (isset($_GET['zoom_type_api'])) { // zoop api config
    $tool_content .= action_bar(array(
        array('title' => $langShowZoomApiUsers,
            'url' => 'zoomconf.php?show_api_users=1',
            'icon' => 'fa-user',
            'level' => 'primary-label',
            'button-class' => 'btn-success')
        ));

    $boolean_field = "";
    $tool_content .= "
        <div class='row extapp'><div class='col-lg-6 col-12'>
          <div class='form-wrapper form-edit border-0 px-0'>
            <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>";

    foreach ($app->getParams() as $param) {
        if ($param->getType() == ExtParam::TYPE_BOOLEAN) {
            $checked = $param->value() == 1 ? "checked" : "";
            $boolean_field .= "<div class='form-group mt-4'><div class='col-sm-offset-2 col-sm-10'><div class='checkbox'>";
            $boolean_field .= "<label class='label-container' aria-label='$langSelect'><input type='checkbox' name='" . $param->name() . "' value='1' $checked>" . $param->display() . "<span class='checkmark'></span></label>";
            $boolean_field .= "</div></div></div>";
        } else if ($param->name() == ZoomApp::ACCOUNT_ID or $param->name() == ZoomApp::CLIENT_ID or $param->name() == ZoomApp::CLIENT_SECRET) {
            $tool_content .= "<div class='form-group mt-4'>";
            $tool_content .= "<label for='" . $param->name() . "' class='col-sm-12 control-label-notes'>" . $param->display() . "</label>";
            $type = 'text';
            if ($param->name() === ZoomApp::CLIENT_SECRET) {
                $type = 'password';
            }
            $tool_content .= "<div class='col-sm-12'><input id='" . $param->name() . "' class='form-control' type='" . $type . "' name='" . $param->name() . "' value='" . q($param->value()) . "'></div>";
            $tool_content .= "</div>";
        } else if ($param->name() == ZoomApp::ENABLEDCOURSES) {
            $courses_list = Database::get()->queryArray("SELECT id, code, title FROM course
                                                                WHERE visible != " . COURSE_INACTIVE . "
                                                             ORDER BY title");
            $csvSelection = $param->value();
            $selections = array();
            if (!empty($csvSelection) && strlen($csvSelection) > 0) {
                $selections = explode(",", $csvSelection);
                $selected = in_array("0", $selections) ? "selected" : "";
            } else {
                $selected = "selected";
            }
            $tool_content .= "<div class='form-group mt-4' id='courses-list'>";
            $tool_content .= "<label for='select-courses' class='col-sm-12 control-label-notes'>$langUseOfService&nbsp;&nbsp;";
            $tool_content .= "<span class='fa fa-info-circle' data-bs-toggle='tooltip' data-bs-placement='right' title='$langUseOfServiceInfo'></span></label>";
            $tool_content .= "<div class='col-sm-12'><select id='select-courses' class='form-select' name='tc_courses[]' multiple>";
            $tool_content .= "<option value='0' $selected><h2>$langToAllCourses</h2></option>";
            foreach ($courses_list as $c) {
                $selected = in_array($c->id, $selections) ? "selected" : "";
                $tool_content .= "<option value='$c->id' $selected>" . q($c->title) . " (" . q($c->code) . ")</option>";
            }
            $tool_content .= "</select><a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a></div></div>";
            $tool_content .= "<input type='hidden' id='enabled-courses' name='" . $param->name() . "'>";
        }
    }

    $tool_content .= $boolean_field;
    $tool_content .= "
                <div class='form-group mt-4'>
                  <div class='d-flex justify-content-end gap-2 flex-wrap'>
                    <button class='btn submitAdminBtn' type='submit' name='submit'>$langSubmit</button>
                    <button class='btn deleteAdminBtn' type='submit' name='submit' value='clear'>$langClearSettings</button>
                    <a href='zoomconf.php' class='btn cancelAdminBtn'>$langCancel</a>
                  </div>
                </div>" .
            generate_csrf_token_form_field() . "
            </form>
          </div>
        </div>
        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
            <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
        </div>
      </div>";

    draw($tool_content, 3, null, $head_content);

} elseif (isset($_GET['zoom_type_custom'])) { // config zoom without api

    $boolean_field = "";

    $tool_content .= "
    <div class='row extapp'><div class='col-lg-6 col-12'>
      <div class='form-wrapper form-edit border-0 px-0'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>";

        foreach ($app->getParams() as $param) {
            if ($param->getType() == ExtParam::TYPE_BOOLEAN) {
                $checked = $param->value() == 1 ? "checked" : "";
                $boolean_field .= "<div class='form-group mt-4'><div class='col-sm-offset-2 col-sm-10'><div class='checkbox'>";
                $boolean_field .= "<label class='label-container' aria-label='$langSelect'><input type='checkbox' name='" . $param->name() . "' value='1' $checked>" . $param->display() . "<span class='checkmark'></span></label>";
                $boolean_field .= "</div></div></div>";
            } elseif ($param->name() == ZoomApp::ENABLEDCOURSES) {
                $courses_list = Database::get()->queryArray("SELECT id, code, title FROM course
                                                                    WHERE visible != " . COURSE_INACTIVE . "
                                                                 ORDER BY title");
                $csvSelection = $param->value();
                $selections = array();
                if (!empty($csvSelection) && strlen($csvSelection) > 0) {
                    $selections = explode(",", $csvSelection);
                    $selected = in_array("0", $selections) ? "selected" : "";
                } else {
                    $selected = "selected";
                }
                $tool_content .= "<div class='form-group mt-4' id='courses-list'>";
                $tool_content .= "<label for='select-courses' class='col-sm-12 control-label-notes'>$langUseOfService&nbsp;&nbsp;";
                $tool_content .= "<span class='fa fa-info-circle' data-bs-toggle='tooltip' data-bs-placement='right' title='$langUseOfServiceInfo'></span></label>";
                $tool_content .= "<div class='col-sm-12'><select id='select-courses' class='form-select' name='tc_courses[]' multiple>";
                $tool_content .= "<option value='0' $selected><h2>$langToAllCourses</h2></option>";
                foreach ($courses_list as $c) {
                    $selected = in_array($c->id, $selections) ? "selected" : "";
                    $tool_content .= "<option value='$c->id' $selected>" . q($c->title) . " (" . q($c->code) . ")</option>";
                }
                $tool_content .= "</select><a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a></div></div>";
                $tool_content .= "<input type='hidden' id='enabled-courses' name='" . $param->name() . "'>";
            }
        }

    $tool_content .= $boolean_field;
    $tool_content .= "
                <div class='form-group mt-4'>
                  <div class='d-flex justify-content-end gap-2 flex-wrap'>
                    <button class='btn submitAdminBtn' type='submit' name='submit'>$langSubmit</button>
                    <button class='btn deleteAdminBtn' type='submit' name='submit' value='clear'>$langClearSettings</button>
                    <a href='zoomconf.php' class='btn cancelAdminBtn'>$langCancel</a>
                  </div>
                </div>" .
            generate_csrf_token_form_field() . "
            </form>
          </div>
        </div>
        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
            <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
        </div>
      </div>";

    draw($tool_content, 3, null, $head_content);

} elseif (isset($_GET['show_api_users'])) {
    if (!$zoomUserRepo->zoomApiEnabled()) {
        Session::flash('message', $langNoApiCredentials);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page($_SERVER['HTTP_REFERER'], true);
    }
    $apiUsers = $zoomUserRepo->listAllZoomUsers();

    load_js('tools.js');
    load_js('datatables');
    $head_content .= "
        <script>
            $(document).ready( function () {
                    $('#zoomTable').DataTable();
                } 
            );
        </script>";

    $tool_content .= "<table class='table-default' id='zoomTable'>";
    $tool_content .= "<thead>
                        <tr>
                          <th style='width:20%'>$langSurname</th>
                          <th style='width:20%'>$langName</th>
                          <th style='width: 20%;'>$langEmail</th>
                          <th style='width: 5%;'>$langLicense</th>
                          <th style='width: 5%;'>$langActions</th>
                        </tr>
                    </thead>
                    <tbody>";

    foreach ($apiUsers->users as $user_data) {
        if ($user_data->type == 2) {
            $changeType = 1;
            $typeString = $langPaid;
        } else {
            $changeType = 2;
            $typeString = $langFree;
        }

        $tool_content .= "<tr>
                    <td>". $user_data->last_name . "</td>
                    <td>$user_data->first_name</td>
                    <td>$user_data->email</td>
                    <td>$typeString</td>
                    <td><a href='".$_SERVER['SCRIPT_NAME']."?change_zoom_user_type=1&id=".$user_data->id."&email=".$user_data->email."&type=".$changeType."' class='btn btn-primary'>$langModify</a></td>
                    </tr>";
    }
    $tool_content .= "</tbody></table>";
    draw($tool_content, 3, null, $head_content);

} elseif (isset($_GET['change_zoom_user_type'])) {
    $res = $zoomUserRepo->changeUserType($_GET['id'], $_GET['email'], $_GET['type']);
    if (!empty($res->fail_details)) {
        if (!empty($res->fail_details[0]->reason) && $res->fail_details[0]->reason == 'Not enough seats') {
            Session::flash('message', $langNoEmptySeats);
            Session::flash('alert-claass', 'alert-warning');
            redirect_to_home_page($_SERVER['HTTP_REFERER'], true);
        }
    }
    Session::flash('message', $langQuotaSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page($_SERVER['HTTP_REFERER'], true);
} else {
    $tool_content .= "  <div class='row'>
                            <div class='col-lg-6 col-12'>
                                <div class='card panelCard card-default px-lg-4 py-lg-3 h-100'>
                                    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                        <h3>$langZoomConnect</h3>
                                    </div>
                                    <div class='card-body'>
                                        <div class='d-flex justify-content-center align-items-center gap-3 flex-wrap'>
                                            <div class='col-12 d-flex justify-content-center align-items-center'><a href='".$urlServer."modules/admin/zoomconf.php?zoom_type_api=1' class='btn submitAdminBtn d-inline-flex'>$langZoomConnectViaApi</a></div>
                                            <div class='col-12 d-flex justify-content-center align-items-center'><a href='".$urlServer."modules/admin/zoomconf.php?zoom_type_custom=1' class='btn submitAdminBtnDefault d-inline-flex'>$langZoomConnectNoApi</a></div>                                                
                                        </div>                                                                                    
                                    </div>
                                </div>
                            </div>
                            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                                <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
                            </div>
                        </div>";

    draw($tool_content, 3, null);
}
