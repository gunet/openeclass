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

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2024  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

$require_login = true;
$require_valid_uid = true;

require_once '../../include/baseTheme.php';
require_once 'main/eportfolio/eportfolio_functions.php';

check_uid();
check_guest();

$toolName = $langMyePortfolio;
$pageName = $langEditChange;
$token = token_generate('eportfolio' . $uid);
$navigation[] = array("url" => "{$urlAppend}main/profile/display_profile.php", "name" => $langMyProfile);
$navigation[] = array('url' => "index.php?id=$uid&token=$token", 'name' => $langMyePortfolio);

if (!get_config('eportfolio_enable')) {
    $tool_content = "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langePortfolioDisabled</span></div>";
    draw($tool_content, 1);
    exit;
}

$userdata = Database::get()->querySingle("SELECT eportfolio_enable FROM user WHERE id = ?d", $uid);

if ($userdata->eportfolio_enable == 0) {
    $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langePortfolioDisableWarning</span></div>";
}

load_js('tools.js');

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    //check for validation errors in e-portfolio fields
    $v = new Valitron\Validator($_POST);
    epf_validate($v);
    if (!$v->validate()) {
        Session::flashPost();
        Session::flashPost()->Messages($langFormErrors, 'alert-danger')->Errors($v->errors());
        redirect_to_home_page("main/eportfolio/edit_eportfolio.php");
    } else {
        process_eportfolio_fields_data();
        Session::flash('message', $langePortfolioChangeSucc);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("main/eportfolio/index.php?id=$uid&token=$token");
    }
}

$head_content .= "
        <script>
        $(function() {
            $('body').scrollspy({ target: '#affixedSideNav' });
        });
        </script>
    ";

$sec = $urlServer . 'main/eportfolio/edit_eportfolio.php';

$tool_content .=
    "<div class='row mt-4'>
        <div class='col-sm-9'>
            <form class='form-horizontal' action='$sec' method='post'>
            <div data-bs-spy='scroll' data-bs-target='#navbar-examplePortfolioEdit' data-bs-offset='0' tabindex='0'>  ";

//add custom profile fields
$ret_str = render_eportfolio_fields_form();
$tool_content .= $ret_str['panels'];

$tool_content .= "
    <div class='form-group mt-5 d-flex justify-content-center align-items-center gap-2'>
        <input class='btn submitAdminBtn' type='submit' name='submit' value='$langSubmit'>     
        <a href='{$urlAppend}main/eportfolio/index.php?id=$uid&amp;token=$token' class='btn cancelAdminBtn'>$langCancel</a>
                  </div>
      ". generate_csrf_token_form_field() ."  
      </div></form>
      </div>
      ".$ret_str['right_menu']."
      </div>";

$head_content .= "
    <script>
        $(document).ready(function() {
            $('.visibility_select').on('change', function() {
                var selectName = $(this).attr('name');    // Get the select's name
                var selectValue = $(this).val();          // Get the select's value
                $('#' + selectName + '_hidden').val(selectValue);     // Set the hidden input's value
                if (selectValue == 2) {
                    $('#' + selectName + '_button').html('<i class=\"fa fa-users\"></i>');
                } else if (selectValue == 3) {
                    $('#' + selectName + '_button').html('<i class=\"fa fa-lock\"></i>');
                } else {
                    $('#' + selectName + '_button').html('<i class=\"fa fa-globe\"></i>');
                }
            });
        });
    </script>
";


draw($tool_content, 1, null, $head_content);
