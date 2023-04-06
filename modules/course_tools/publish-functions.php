<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * ========================================================================
 */

/**
 * Generate and return a random secret.
 *
 * @param int $length The length of the secret to be created.
 * @return string
 */
function random_secret(int $length = 32): string {
    require_once 'include/lib/srand.php';
    $randombytes = secure_random_bytes($length);
    $pool  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $pool .= 'abcdefghijklmnopqrstuvwxyz';
    $pool .= '0123456789';
    $poollen = strlen($pool);
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $rand = ord($randombytes[$i]);
        $string .= substr($pool, ($rand%($poollen)), 1);
    }
    return $string;
}

/**
 * Publish as LTI tool (display form).
 */
function new_publish_ltiapp() {
    global $tool_content, $langAdd, $course_code, $langTitle, $langDescription,
           $langLTIProviderKey, $langLTIProviderSecret, $langNewLTIAppStatus, $langNewLTIAppActive, $langNewLTIAppInActive,
           $langLTIAPPlertTitle, $langLTIAPPlertKey, $langLTIAPPlertSecret;

    $textarea = rich_text_editor('desc', 4, 20, '');
    $key = random_secret(8);
    $secret = random_secret();
    $tool_content .= "
        <div class='form-wrapper'>
        <form class='form-horizontal' role='form' name='sessionForm' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post' >
        <fieldset>
        <div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langTitle:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='title' id='title' placeholder='$langTitle' size='50' />
            </div>
        </div>
        <div class='form-group'>
            <label for='desc' class='col-sm-2 control-label'>$langDescription:</label>
            <div class='col-sm-10'>
                $textarea
            </div>
        </div>
        <div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langLTIProviderKey:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='lti_key' id='lti_key' placeholder='$langLTIProviderKey' value='$key' size='32' />
            </div>
        </div>        
        <div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langLTIProviderSecret:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='lti_secret' id='lti_secret' placeholder='$langLTIProviderSecret' value='$secret' size='32' />
            </div>
        </div>
        <div class='form-group'>
            <label for='active_button' class='col-sm-2 control-label'>$langNewLTIAppStatus:</label>
            <div class='col-sm-10'>
                <div class='radio'>
                  <label>
                    <input type='radio' id='active_button' name='status' value='1' checked>
                    $langNewLTIAppActive
                  </label>
                </div>
                <div class='radio'>
                  <label>
                    <input type='radio' id='inactive_button' name='status' value='0'>
                   $langNewLTIAppInActive
                  </label>
                </div>
            </div>
        </div>
        <div class='form-group'>
            <div class='col-sm-10 col-sm-offset-2'>
                <input class='btn btn-primary' type='submit' name='new_publish_ltiapp' value='$langAdd'>
            </div>
        </div>
        </fieldset>
         ". generate_csrf_token_form_field() ."
        </form></div>";

    $tool_content .='<script type="text/javascript">
        //<![CDATA[
            let chkValidator  = new Validator("sessionForm");
            chkValidator.addValidation("title", "req", "' . $langLTIAPPlertTitle . '");
            chkValidator.addValidation("lti_key", "req", "' . $langLTIAPPlertKey . '");
            chkValidator.addValidation("lti_secret", "req", "' . $langLTIAPPlertSecret . '");
        //]]></script>';
}

/**
 * Publish as LTI tool (edit form).
 *
 * @param id
 */
function edit_publish_ltiapp($id) {
    global $tool_content, $langModify, $course_code, $langTitle, $langDescription,
           $langLTIProviderKey, $langLTIProviderSecret, $langNewLTIAppStatus, $langNewLTIAppActive, $langNewLTIAppInActive,
           $langLTIAPPlertTitle, $langLTIAPPlertKey, $langLTIAPPlertSecret;

    $row = Database::get()->querySingle("SELECT * FROM course_lti_publish WHERE id = ?d ", $id);
    $status = ($row->enabled == 1 ? 1 : 0);
    $textarea = rich_text_editor('desc', 4, 20, $row->description);
    $key = $row->lti_provider_key;
    $secret = $row->lti_provider_secret;

    $tool_content .= "
        <div class='form-wrapper'>
        <form class='form-horizontal' role='form' name='sessionForm' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post' >
        <fieldset>
        <div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langTitle:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='title' id='title' placeholder='$langTitle' size='50' value='".q($row->title)."'/>
            </div>
        </div>
        <div class='form-group'>
            <label for='desc' class='col-sm-2 control-label'>$langDescription:</label>
            <div class='col-sm-10'>
                $textarea
            </div>
        </div>
        <div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langLTIProviderKey:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='lti_key' id='lti_key' placeholder='$langLTIProviderKey' value='$key' size='32' />
            </div>
        </div>        
        <div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langLTIProviderSecret:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='lti_secret' id='lti_secret' placeholder='$langLTIProviderSecret' value='$secret' size='32' />
            </div>
        </div>
        <div class='form-group'>
            <label for='active_button' class='col-sm-2 control-label'>$langNewLTIAppStatus:</label>
            <div class='col-sm-10'>
                <div class='radio'>
                  <label>
                    <input type='radio' id='active_button' name='status' value='1' ".(($status==1) ? "checked" : "").">
                    $langNewLTIAppActive
                  </label>
                </div>
                <div class='radio'>
                  <label>
                    <input type='radio' id='inactive_button' name='status' value='0' ".(($status==0) ? "checked" : "").">
                   $langNewLTIAppInActive
                  </label>
                </div>
            </div>
        </div>
        <div class='form-group'>
            <div class='col-sm-10 col-sm-offset-2'>
                <input class='btn btn-primary' type='submit' name='update_publish_ltiapp' value='$langModify'>
                <input type='hidden' name='id' value='" . getIndirectReference($id) . "'>
            </div>
        </div>
        </fieldset>
         ". generate_csrf_token_form_field() ."
        </form></div>";

    $tool_content .='<script type="text/javascript">
        //<![CDATA[
            let chkValidator  = new Validator("sessionForm");
            chkValidator.addValidation("title", "req", "' . $langLTIAPPlertTitle . '");
            chkValidator.addValidation("lti_key", "req", "' . $langLTIAPPlertKey . '");
            chkValidator.addValidation("lti_secret", "req", "' . $langLTIAPPlertSecret . '");
        //]]></script>';
}

/**
 * Publish as LTI tool (show information).
 *
 * @param id
 */
function show_publish_ltiapp($id) {
    global $tool_content, $urlServer, $langLTIProviderUrl, $langLTIProviderKey, $langLTIProviderSecret, $langLTIProviderCartridgeUrl,
           $langLTIProviderHelp1, $langLTIProviderHelp2;

    $row = Database::get()->querySingle("SELECT * FROM course_lti_publish WHERE id = ?d", $id);
    $launchurl = $urlServer . "modules/lti/tool.php?id=".$id;
    $key = $row->lti_provider_key;
    $secret = $row->lti_provider_secret;
    $cartridgeurl = $urlServer . "modules/lti/cartridge.php?id=" . $id . "&token=" . LtiEnrolHelper::generate_cartridge_token($id);

    $tool_content .= "<div class='form-wrapper'><form class='form-horizontal'><fieldset>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langLTIProviderUrl</label>
                <div class='col-sm-10'>
                    <input type='text' class='form-control' value='$launchurl' readonly>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langLTIProviderKey</label>
                <div class='col-sm-10'>
                    <input type='text' class='form-control' value='$key' readonly>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langLTIProviderSecret</label>
                <div class='col-sm-10'>
                    <input type='text' class='form-control' value='$secret' readonly>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langLTIProviderCartridgeUrl</label>
                <div class='col-sm-10'>
                    <input type='text' class='form-control' value='$cartridgeurl' readonly>
                </div>
            </div>
        </fieldset></form></div><p>$langLTIProviderHelp1</p><p>$langLTIProviderHelp2</p>";
}

/**
 * Add published as LTI tool.
 */
function add_publish_ltiapp($title, $desc, $key, $secret, $status) {
    global $course_id;
    Database::get()->query("INSERT INTO course_lti_publish (course_id, title, description, lti_provider_key, lti_provider_secret, enabled) VALUES (?d, ?s, ?s, ?s, ?s, ?d)",
        $course_id, $title, $desc, $key, $secret, $status);
}

/**
 * Update published as LTI tool.
 */
function update_publish_ltiapp($id, $title, $desc, $key, $secret, $status) {
    Database::get()->querySingle("UPDATE course_lti_publish SET title = ?s, description = ?s, lti_provider_key = ?s, lti_provider_secret = ?s, enabled = ?d WHERE id = ?d",
        $title, $desc, $key, $secret, $status, $id);
}

/**
 * Display available published LTI configurations.
 */
function lti_provider_details() {
    global $course_id, $tool_content, $is_editor, $course_code,
           $langConfirmDelete, $langNewLTIAppSessionDesc, $langTitle, $langActivate,
           $langDeactivate, $langEditChange, $langDelete, $langNoPUBLTIApps, $langViewShow;

    load_js('trunk8');

    $activeClause = ($is_editor) ? '' : "AND enabled = 1";
    $result = Database::get()->queryArray("SELECT * FROM course_lti_publish
        WHERE course_id = ?s $activeClause ORDER BY title ASC", $course_id);
    if ($result) {
        $headingsSent = false;
        $headings = "<div class='row'>
                       <div class='col-md-12'>
                         <div class='table-responsive'>
                           <table class='table-default'>
                             <tr class='list-header'>
                               <th style='width:30%'>$langTitle</th>
                               <th class='text-center'>$langNewLTIAppSessionDesc</th>";
        if ($is_editor) {
            $headings .= "<th class='text-center'>" . icon('fa-gears') . "</th>";
        }
        $headings .= "</tr>";

        foreach ($result as $row) {
            $id = $row->id;
            $title = $row->title;

            $desc = $row->description ?? '';

            if ($is_editor) {
                if (!$headingsSent) {
                    $tool_content .= $headings;
                    $headingsSent = true;
                }
                $showUrl = "editpublish.php?course=$course_code&amp;id=" . getIndirectReference($id) . "&amp;choice=show";
                $tool_content .= '<tr' . ($row->enabled? '': " class='not_visible'") . ">
                    <td class='text-left'><a href='$showUrl'>$title</</td>
                    <td>$desc</td>
                    <td class='option-btn-cell'>".
                    action_button(array(
                        array('title' => $langEditChange,
                            'url' => "editpublish.php?course=$course_code&amp;id=" . getIndirectReference($id) . "&amp;choice=edit",
                            'icon' => 'fa-edit'),
                        array('title' => $langViewShow,
                            'url' => $showUrl,
                            'icon' => 'fa-archive'),
                        array('title' => $row->enabled? $langDeactivate : $langActivate,
                            'url' => "editpublish.php?id=" . getIndirectReference($row->id) . "&amp;choice=do_".
                                ($row->enabled? 'disable' : 'enable'),
                            'icon' => $row->enabled? 'fa-eye': 'fa-eye-slash'),
                        array('title' => $langDelete,
                            'url' => "editpublish.php?id=" . getIndirectReference($row->id) . "&amp;choice=do_delete",
                            'icon' => 'fa-times',
                            'class' => 'delete',
                            'confirm' => $langConfirmDelete)
                    )) .
                    "</td></tr>";
            } else {
                if (!$headingsSent) {
                    $tool_content .= $headings;
                    $headingsSent = true;
                }
                $tool_content .= "<tr>
                    <td class='text-center'>$title</td>
                    <td>$desc</td>
                    </tr>";
            }
        }
        if ($headingsSent) {
            $tool_content .= "</table></div></div></div>";
        }

        if (!$is_editor and !$headingsSent) {
            $tool_content .= "<div class='alert alert-warning'>$langNoPUBLTIApps</div>";
        }
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langNoPUBLTIApps</div>";
    }
}

function disable_publish_ltiapp($id) {
    global $langLTIAppUpdateSuccessful, $course_code;

    Database::get()->querySingle("UPDATE course_lti_publish set enabled = 0 WHERE id = ?d", $id);
    Session::Messages($langLTIAppUpdateSuccessful, 'alert-success');
    redirect_to_home_page("modules/course_tools/index.php?course=$course_code");
}

function enable_publish_ltiapp($id) {
    global $langLTIAppUpdateSuccessful, $course_code;

    Database::get()->querySingle("UPDATE course_lti_publish SET enabled = 1 WHERE id = ?d", $id);
    Session::Messages($langLTIAppUpdateSuccessful, 'alert-success');
    redirect_to_home_page("modules/course_tools/index.php?course=$course_code");
}

/**
 * Delete Published LTI tool
 *
 * @param $id
 */
function delete_publish_ltiapp($id) {
    Database::get()->querySingle("DELETE FROM course_lti_publish WHERE id = ?d", $id);
}
