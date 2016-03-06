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


function new_lti_app() {

    global $course_id, $uid;
    global $tool_content, $langAdd, $course_code;
    global $langNewBBBSessionDesc, $langLTIProviderUrl, $langLTIProviderSecret, $langLTIProviderKey;
    global $langNewLTIAppActive, $langNewLTIAppInActive;
    global $langNewLTIAppStatus, $langBBBSessionAvailable;
    global $langTitle;
    global $langLTIAPPlertTitle, $langLTIAPPlertURL;

    $textarea = rich_text_editor('desc', 4, 20, '');
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
            <label for='desc' class='col-sm-2 control-label'>$langNewBBBSessionDesc:</label>
            <div class='col-sm-10'>
                $textarea
            </div>
        </div>        
        <div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langLTIProviderUrl:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='lti_url' id='lti_url' placeholder='$langLTIProviderUrl' size='50' />
            </div>
        </div>
        <div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langLTIProviderKey:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='lti_key' id='lti_key' placeholder='$langLTIProviderKey' size='50' />
            </div>
        </div>
        <div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langLTIProviderSecret:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='lti_secret' id='lti_secret' placeholder='$langLTIProviderSecret' size='50' />
            </div>
        </div>";
        $tool_content .= "<div class='form-group'>
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
                <input class='btn btn-primary' type='submit' name='new_lti_app' value='$langAdd'>
            </div>
        </div>
        </fieldset>
         ". generate_csrf_token_form_field() ."
        </form></div>";
        $tool_content .='<script language="javaScript" type="text/javascript">
        //<![CDATA[
            var chkValidator  = new Validator("sessionForm");
            chkValidator.addValidation("title","req","'.$langLTIAPPlertTitle.'");
            chkValidator.addValidation("lti_url","req","'.$langLTIAPPlertURL.'");
        //]]></script>';
}

function add_update_lti_app($title, $desc, $url, $key, $secret, $status, $update = 'false', $session_id = '')
{
    global $langDescr, $course_code, $course_id, $urlServer;

    if ($update == 'true') {
        Database::get()->querySingle("UPDATE lti_apps SET title=?s, description=?s, lti_provider_url=?s,
                                        lti_provider_key=?s, lti_provider_secret=?s, enabled=?s WHERE id=?d",
                                        $title, $desc, $url, $key, $secret, $status, $session_id);

    } else {
        $q = Database::get()->query("INSERT INTO lti_apps (course_id, title, description,
                                                            lti_provider_url, lti_provider_key, lti_provider_secret,
                                                            enabled)
                                                        VALUES (?d,?s,?s,?s,?s,?s,?s)",
                                            $course_id, $title, $desc, $url, $key, $secret, $status);
    }

}

function edit_lti_app($session_id) {
    global $tool_content, $langModify, $course_code, $course_id, $uid;
    global $langNewLTIAppSessionDesc, $langLTIProviderUrl, $langLTIProviderKey, $langLTIProviderSecret;
    global $langNewLTIAppStatus, $langNewLTIAppActive, $langNewLTIAppInActive,$langLTIAppAvailable;
    global $langTitle;
    global $langLTIAPPlertTitle, $langLTIAPPlertURL;

    $row = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d ", $session_id);

    $status = ($row->enabled == 1 ? 1 : 0);

    $textarea = rich_text_editor('desc', 4, 20, $row->description);
    $tool_content .= "
                <div class='form-wrapper'>
                    <form class='form-horizontal' role='form' name='sessionForm' action='$_SERVER[SCRIPT_NAME]?id=" . getIndirectReference($session_id) . "' method='post'>
                    <fieldset>
                    <div class='form-group'>
                        <label for='title' class='col-sm-2 control-label'>$langTitle:</label>
                        <div class='col-sm-10'>
                            <input class='form-control' type='text' name='title' id='title' value='".q($row->title)."'>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='desc' class='col-sm-2 control-label'>$langNewLTIAppSessionDesc:</label>
                        <div class='col-sm-10'>
                            $textarea
                        </div>
                    </div>";
        $tool_content .="<div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langLTIProviderUrl:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='lti_url' id='lti_url' value='$row->lti_provider_url' size='50' />
            </div>
        </div>
        <div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langLTIProviderKey:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='lti_key' id='lti_key' value='$row->lti_provider_key' size='50' />
            </div>
        </div>
        <div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langLTIProviderSecret:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='lti_secret' id='lti_secret' value='$row->lti_provider_secret' size='50' />
            </div>
        </div>";

    $tool_content .= "<div class='form-group'>
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
                            <input class='btn btn-primary' type='submit' name='update_lti_app' value='$langModify'>
                        </div>
                    </div>
                    </fieldset>
                     ". generate_csrf_token_form_field() ."
                    </form></div>";
                $tool_content .='<script language="javaScript" type="text/javascript">
                    //<![CDATA[
                    var chkValidator  = new Validator("sessionForm");
                    chkValidator.addValidation("title","req","'.$langLTIAPPlertTitle.'");
                    chkValidator.addValidation("lti_url","req","'.$langLTIAPPlertURL.'");
                    //]]></script>';
}

function lti_app_details() {
    global $course_id, $tool_content, $is_editor, $course_code, $head_content,
        $langConfirmDelete, $langNewLTIAppSessionDesc, $langNote,
        $langTitle,$langActivate, $langDeactivate,
        $langEditChange, $langDelete, $langNoLTIApps, $m;

    load_js('trunk8');

    $activeClause = $is_editor? '': "AND enabled = '1'";
    $result = Database::get()->queryArray("SELECT * FROM lti_apps
        WHERE course_id = ?s $activeClause ORDER BY id DESC", $course_id);
    if ($result) {
        $headingsSent = false;
        $headings = "<div class='row'>
                       <div class='col-md-12'>
                         <div class='table-responsive'>
                           <table class='table-default'>
                             <tr class='list-header'>
                               <th style='width:30%'>$langTitle</th>
                               <th class='text-center'>$langNewLTIAppSessionDesc</th>
                               <th class='text-center'>".icon('fa-gears')."</th>
                             </tr>";

        foreach ($result as $row) {
            $id = $row->id;
            $title = $row->title;

            $desc = isset($row->description)? $row->description: '';

            $canJoin = $row->enabled == '1';
            if ($canJoin) {
                if($is_editor)
                {
                    $joinLink = "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=do_join&amp;meeting_id=" . urlencode($meeting_id) . "&amp;title=".urlencode($title)."&amp;att_pw=".urlencode($att_pw)."&amp;mod_pw=".urlencode($mod_pw)."&amp;record=$record' target='_blank'>" . q($title) . "</a>";
                }else
                {
                    $joinLink = "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=do_join&amp;meeting_id=" . urlencode($meeting_id) . "&amp;title=".urlencode($title)."&amp;att_pw=".urlencode($att_pw)."&amp;record=$record' target='_blank'>" . q($title) . "</a>";
                }
            } else {
                $joinLink = q($title);
            }

            if ($is_editor) {
                if (!$headingsSent) {
                    $tool_content .= $headings;
                    $headingsSent = true;
                }
                $tool_content .= '<tr' . ($row->enabled? '': " class='not_visible'") . ">
                    <td class='text-left'>$joinLink</td>
                    <td>$desc</td>
                    <td class='option-btn-cell'>".
                        action_button(array(
                            array(  'title' => $langEditChange,
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=" . getIndirectReference($id) . "&amp;choice=edit",
                                    'icon' => 'fa-edit'),
                            array(  'title' => $row->enabled? $langDeactivate : $langActivate,
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=" . getIndirectReference($row->id) . "&amp;choice=do_".
                                             ($row->enabled? 'disable' : 'enable'),
                                    'icon' => $row->enabled? 'fa-eye': 'fa-eye-slash'),
                            array(  'title' => $langDelete,
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=" . getIndirectReference($row->id) . "&amp;choice=do_delete",
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
                    <td class='text-center'>$joinLink</td>
                    <td>$desc</td>
                    <td class='text-center'>";

                    if ($canJoin) {
                        $tool_content .= icon('fa-sign-in', $langBBBSessionJoin,"$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=do_join&amp;title=".urlencode($title)."&amp;meeting_id=" . urlencode($meeting_id) . "&amp;att_pw=".urlencode($att_pw)."&amp;record=$record' target='_blank");
                    } else {
                        $tool_content .= "-</td>";
                    }
                    $tool_content .= "</tr>";
            }
        }
        if ($headingsSent) {
            $tool_content .= "</table></div></div></div>";
        }

        if (!$is_editor and !$headingsSent) {
            $tool_content .= "<div class='alert alert-warning'>$langNoLTIApps</div>";
        }
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langNoLTIApps</div>";
    }
}

function disable_lti_app($id)
{
    global $langLTIAppDeleteSuccessful, $course_code;

    Database::get()->querySingle("UPDATE lti_apps set enabled='0' WHERE id=?d",$id);
    Session::Messages($langLTIAppDeleteSuccessful, 'alert-success');
    redirect_to_home_page("modules/lti_consumer/index.php?course=$course_code");
}

function enable_lti_app($id)
{
    global $langLTIAppUpdateSuccessful, $course_code;

    Database::get()->querySingle("UPDATE lti_apps SET enabled='1' WHERE id=?d",$id);
    Session::Messages($langBBBUpdateSuccessful, 'alert-success');
    redirect_to_home_page("modules/lti_consumer/index.php?course=$course_code");
}


function delete_lti_app($id)
{
    global $langBBBDeleteSuccessful, $course_code;

    Database::get()->querySingle("DELETE FROM lti_apps WHERE id=?d",$id);
    Session::Messages($langBBBDeleteSuccessful, 'alert-success');
    redirect_to_home_page("modules/lti_consumer/index.php?course=$course_code");
}

