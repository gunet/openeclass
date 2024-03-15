<?php

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/webexapp.php';

$app = ExtAppManager::getApp('webex');
$toolName = $langConfig . ' ' . $app->getDisplayName();
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => 'extapp.php',
        'icon' => 'fa-reply',
        'level' => 'primary')));

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
        
        // remove 'all courses' selection when selected other courses
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
        
        $('#custom_webex_url').change(function() {
            if($(this).prop('checked')) {
                $('#default_webex_url').prop('disabled', true);
            } else {
                $('#default_webex_url').prop('disabled', false);
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
        Session::Messages($langFileUpdatedSuccess, 'alert-info');
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
        if (isset($_POST['custom_webex_url'])) {
            $hostname = 'webex';
        } else {
            $hostname = $_POST['url'];
        }

        $tc_sql = Database::get()->querySingle("SELECT id FROM tc_servers WHERE `type` = 'webex'");
        if ($tc_sql) {
            $tc_id = $tc_sql->id;
            Database::get()->query("UPDATE tc_servers SET hostname = ?s, api_url = ?s, enabled = ?s, all_courses = ?s WHERE id = ?d",
                $hostname, $hostname, $enabled, $all_courses, $tc_id);
        } else {
            $tc_sql = Database::get()->query("INSERT INTO tc_servers (`type`, hostname, api_url, enabled, all_courses) 
                                            VALUES('webex', ?s, ?s, ?s, ?s)",
                $hostname, $hostname, $enabled, $all_courses);
            $tc_id = $tc_sql->lastInsertID;
        }

        Database::get()->query("DELETE FROM course_external_server WHERE external_server = ?d", $tc_id);
        if (($all_courses == 0) and count($_POST['tc_courses']) > 0) {
            foreach ($_POST['tc_courses'] as $tc_courses) {
                Database::get()->query("INSERT INTO course_external_server (course_id, external_server) 
                                              VALUES (?d, ?d)", $tc_courses, $tc_id);
            }
        }
        if ($result) {
            Session::Messages($result, 'alert-danger');
        } else {
            Session::Messages($langFileUpdatedSuccess, 'alert-success');
        }
    }
    redirect_to_home_page($app->getConfigUrl());
}

$boolean_field = "";
$tool_content .= "
    <div class='row extapp'><div class='col-lg-6 col-12'>
      <div class='form-wrapper form-edit border-0 px-0'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>";

foreach ($app->getParams() as $param) {
    if ($param->getType() == ExtParam::TYPE_BOOLEAN) {
        $checked = $param->value() == 1 ? "checked" : "";
        $boolean_field .= "<div class='form-group mt-4'><div class='col-sm-offset-3 col-sm-9'><div class='checkbox'>";
        $boolean_field .= "<label class='label-container'><input type='checkbox' name='" . $param->name() . "' value='1' $checked><span class='checkmark'></span>" . $param->display() . "</label>";
        $boolean_field .= "</div></div></div>";
    } else if ($param->name() == WebexApp::WEBEXURL) {
        $extra = '';
        $q = Database::get()->querySingle("SELECT hostname FROM tc_servers WHERE type = 'webex'");
        if ($q) {
            $webex_host = $q->hostname;
            if ($webex_host == 'webex') {
                $extra = 'disabled';
            } else {
                $extra = '';
            }
        }
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='" . $param->name() . "' class='col-sm-12 control-label-notes'>" . $param->display() . "&nbsp;&nbsp;";
        $tool_content .= "<span class='fa fa-info-circle' data-bs-toggle='tooltip' data-bs-placement='right' title='$langWebexUrl'></span></label>";
        $tool_content .= "<div class='col-sm-12'><input class='form-control' id='default_webex_url' type='text' name='" . $param->name() . "' value='" . q($param->value()) . "' placeholder='" . WebexApp::WEBEXDEFAULURL . "' $extra></div>";
        $tool_content .= "</div>";
    } else if ($param->name() == WebexApp::WEBEXCUSTOMURL) {
        $checked = $param->value() == 1 ? "checked" : "";
        $tool_content .= "<div class='form-group mt-4'><div class='col-sm-offset-2 col-sm-10'><div class='checkbox'>";
        $tool_content .= "<label class='label-container'><input type='checkbox' id='custom_webex_url' name='" . $param->name() . "' value='1' $checked>" . $param->display() . "<span class='checkmark'></span></label>";
        $tool_content .= "</div></div></div>";
    } else if ($param->name() == WebexApp::ENABLEDCOURSES) {
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
        $tool_content .= "<label for='" . $param->name() . "' class='col-12 control-label-notes'>$langUseOfService&nbsp;&nbsp;";
        $tool_content .= "<span class='fa fa-info-circle' data-bs-toggle='tooltip' data-bs-placement='right' title='$langUseOfServiceInfo'></span></label>";
        $tool_content .= "<div class='col-12'><select id='select-courses' class='form-control' name='tc_courses[]' multiple>";
        $tool_content .= "<option value='0' $selected><h2>$langToAllCourses</h2></option>";
        foreach ($courses_list as $c) {
            $selected = in_array($c->id, $selections) ? "selected" : "";
            $tool_content .= "<option value='$c->id' $selected>" . q($c->title) . " (" . q($c->code) . ")</option>";
        }
        $tool_content .= "</select><a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a></div></div>";
        $tool_content .= "<input type='hidden' id='enabled-courses' name='" . $param->name() . "'>";
    } else {
        $tool_content .= "<div class='form-group mt-4'>";
        $tool_content .= "<label for='" . $param->name() . "' class='col-12 control-label-notes'>" . $param->display() . "</label>";
        $tool_content .= "<div class='col-12'><input class='form-control' type='text' name='" . $param->name() . "' value='" . q($param->value()) . "'></div>";
        $tool_content .= "</div>";
    }
}

$tool_content .= $boolean_field;
$tool_content .= "
            <div class='form-group mt-4'>
              <div class='col-12 d-flex justify-content-end gap-2 flex-wrap'>
                <button class='btn submitAdminBtn' type='submit' name='submit'>$langSubmit</button>
                <button class='btn deleteAdminBtn' type='submit' name='submit' value='clear'>$langClearSettings</button>
                <a href='extapp.php' class='btn cancelAdminBtn'>$langCancel</a>
              </div>
            </div>" .
    generate_csrf_token_form_field() . "
        </form>
      </div>
    </div>
    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
        <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
    </div>
  </div>";

draw($tool_content, 3, null, $head_content);
