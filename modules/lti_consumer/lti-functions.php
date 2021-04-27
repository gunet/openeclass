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

define('LTI_LAUNCHCONTAINER_EMBED', 1);
define('LTI_LAUNCHCONTAINER_NEWWINDOW', 2);
define('LTI_LAUNCHCONTAINER_EXISTINGWINDOW', 3);
define('LTI_DESCRIPTION_MAX_LENGTH', 999);

const TURNITIN_LTI_TYPE = 'turnitin';
const LIMESURVEY_LTI_TYPE = 'limesurvey';

/**
 * @brief new lti app (display form)
 * @param bool $is_template
 * @param $course_code
 * @param string $lti_url_default
 */
function new_lti_app($is_template = false, $course_code, $lti_url_default = '') {
    global $tool_content, $langAdd, $langNewBBBSessionDesc, $langLTIProviderUrl, $langLTIProviderSecret,
           $langLTIProviderKey, $langNewLTIAppActive, $langNewLTIAppInActive, $langNewLTIAppStatus, $langTitle,
           $langLTIAPPlertTitle, $langLTIAPPlertURL, $langLTILaunchContainer, $langUseOfApp,
           $langUseOfAppInfo, $langJQCheckAll, $langJQUncheckAll, $langToAllCourses, $course_id;

    $urlext = ($is_template == false) ? '?course=' . $course_code : '';
    $urldefault = (strlen($lti_url_default) > 0) ? " value='$lti_url_default' " : '';

    $textarea = rich_text_editor('desc', 4, 20, '');
    $tool_content .= "
        <div class='form-wrapper'>
        <form class='form-horizontal' role='form' name='sessionForm' action='$_SERVER[SCRIPT_NAME]$urlext' method='post' >
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
                <input class='form-control' type='text' name='lti_url' id='lti_url' placeholder='$langLTIProviderUrl' size='50' $urldefault />
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
                <label for='lti_launchcontainer' class='col-sm-2 control-label'>$langLTILaunchContainer:</label>
                <div class='col-sm-10'>" . selection(lti_get_containers_selection(), 'lti_launchcontainer',  LTI_LAUNCHCONTAINER_EMBED) . "</div>
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
            </div>";
            if (!isset($course_code)) {
                $tool_content .= "<div class='form-group' id='courses-list'>
                    <label class='col-sm-3 control-label'>$langUseOfApp:&nbsp;&nbsp;
                    <span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langUseOfAppInfo'></span></label>                    
                    <div class='col-sm-9'>                                
                        <select class='form-control' name='lti_courses[]' multiple class='form-control' id='select-courses'>";
                    $courses_list = Database::get()->queryArray("SELECT id, code, title FROM course
                                                            WHERE id NOT IN (SELECT course_id FROM course_lti_app)
                                                            AND visible != " . COURSE_INACTIVE . "
                                                            ORDER BY title");
                    $tool_content .= "<option value='0' selected><h2>$langToAllCourses</h2></option>";
                    foreach($courses_list as $c) {
                        $tool_content .= "<option value='$c->id'>" . q($c->title) . " (" . q($c->code) . ")</option>";
                    }
                    $tool_content .= "</select>
                        <a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
                    </div>
                </div>";
            } else {
                $tool_content .= "<input type='hidden' name='lti_courses[]' value='$course_id'>";
            }

        $tool_content .= "<div class='form-group'>
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

/**
 * @param $title
 * @param $desc
 * @param $url
 * @param $key
 * @param $secret
 * @param $launchcontainer
 * @param $status
 * @param $lti_courses
 * @param null $course_id
 * @param bool $is_template
 * @param bool $update
 * @param string $session_id
 * @param string $type
 * @brief add / update lti app settings
 */
function add_update_lti_app($title, $desc, $url, $key, $secret, $launchcontainer, $status, $lti_courses, $course_id = null,
                            $is_template = false, $update = false, $session_id = null, $type)  {
    if (in_array(0, $lti_courses)) {
        $all_courses = 1; // lti app is assigned to all courses
    } else {
        $all_courses = 0; // lti app is assigned to specific courses
    }
    if ($update == true) {
        Database::get()->querySingle("UPDATE lti_apps SET title = ?s, description = ?s, lti_provider_url = ?s, lti_provider_key = ?s,
                                        lti_provider_secret = ?s, launchcontainer = ?d, enabled = ?s, all_courses = ?d, type = ?s WHERE id = ?d",
                                        $title, $desc, $url, $key, $secret, $launchcontainer, $status, $all_courses, $type, $session_id);
        Database::get()->query("DELETE FROM course_lti_app WHERE lti_app = ?d", $session_id);
        if ($all_courses == 0) {
            foreach ($lti_courses as $data) {
                Database::get()->query("INSERT INTO course_lti_app SET course_id = ?d, lti_app = ?d", $data, $session_id);
            }
        }
    } else {
        $firstparam = ($is_template == true) ? 'is_template' : 'course_id';
        $firstarg = ($is_template == true) ? 1 : intval($course_id);

        $q = Database::get()->query("INSERT INTO lti_apps (" . $firstparam . ", title, description,
                                                            lti_provider_url, lti_provider_key, lti_provider_secret,
                                                            launchcontainer, enabled, all_courses, type)
                                                        VALUES (?d,?s,?s,?s,?s,?s,?d,?s,?d,?s)",
                                            $firstarg, $title, $desc, $url, $key, $secret, $launchcontainer, $status, $all_courses, $type);
        $lti_app_id = $q->lastInsertID;
        if ($all_courses == 0) {
            foreach ($lti_courses as $data) {
                Database::get()->query("INSERT INTO course_lti_app SET course_id = ?d, lti_app = ?d", $data, $lti_app_id);
            }
        }
    }
}

/**
 * @brief edit lti app (display form)
 * @param $session_id
 */
function edit_lti_app($session_id) {
    global $tool_content, $langModify, $langNewLTIAppSessionDesc, $langLTIProviderUrl, $langLTIProviderKey, $langLTIProviderSecret,
           $langNewLTIAppStatus, $langNewLTIAppActive, $langNewLTIAppInActive, $langTitle, $langLTIAPPlertTitle, $langLTIAPPlertURL,
           $langLTILaunchContainer, $langUseOfApp, $course_id,
           $langUseOfAppInfo, $langJQCheckAll, $langJQUncheckAll, $langToAllCourses;;

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
            <label for='lti_url' class='col-sm-2 control-label'>$langLTIProviderUrl:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='lti_url' id='lti_url' value='$row->lti_provider_url' size='50' />
            </div>
        </div>
        <div class='form-group'>
            <label for='lti_key' class='col-sm-2 control-label'>$langLTIProviderKey:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='lti_key' id='lti_key' value='$row->lti_provider_key' size='50' />
            </div>
        </div>
        <div class='form-group'>
            <label for='lti_secret' class='col-sm-2 control-label'>$langLTIProviderSecret:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='lti_secret' id='lti_secret' value='$row->lti_provider_secret' size='50' />
            </div>
        </div>";

    $tool_content .= "<div class='form-group'>
                        <label for='lti_launchcontainer' class='col-sm-2 control-label'>$langLTILaunchContainer:</label>
                        <div class='col-sm-10'>" . selection(lti_get_containers_selection(), 'lti_launchcontainer',  intval($row->launchcontainer)) . "</div>
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
                    </div>";

                    if (!isset($course_id)) {
                        $tool_content .= "<div class='form-group' id='courses-list'>
                            <label class='col-sm-3 control-label'>$langUseOfApp:&nbsp;&nbsp;
                            <span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langUseOfAppInfo'></span></label>                    
                            <div class='col-sm-9'>                                
                                <select class='form-control' name='lti_courses[]' multiple class='form-control' id='select-courses'>";
                                $courses_list = Database::get()->queryArray("SELECT id, code, title FROM course
                                                                    WHERE id NOT IN (SELECT course_id FROM course_lti_app)
                                                                    AND visible != " . COURSE_INACTIVE . "
                                                                    ORDER BY title");
                                if ($row->all_courses == 1) {
                                    $tool_content .= "<option value='0' selected><h2>$langToAllCourses</h2></option>";
                                } else {
                                    $lti_courses_list = Database::get()->queryArray("SELECT id, code, title FROM course WHERE id 
                                                                                IN (SELECT course_id FROM course_lti_app WHERE lti_app = ?d) ORDER BY title", $session_id);
                                    if (count($lti_courses_list) > 0) {
                                        foreach($lti_courses_list as $c) {
                                            $tool_content .= "<option value='$c->id' selected>" . q($c->title) . " (" . q($c->code) . ")</option>";
                                        }
                                        $tool_content .= "<option value='0'><h2>$langToAllCourses</h2></option>";
                                    }
                                }
                                foreach($courses_list as $c) {
                                    $tool_content .= "<option value='$c->id'>" . q($c->title) . " (" . q($c->code) . ")</option>";
                                }
                            $tool_content .= "</select>
                                <a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
                            </div>
                        </div>";
                    } else {
                        $tool_content .= "<input type='hidden' name='lti_courses[]' value='$course_id'>";
                    }

                    $tool_content .= "<div class='form-group'>
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

/**
 * @brief display available lti apps (if any)
 */
function lti_app_details() {
    global $course_id, $tool_content, $is_editor, $course_code, $head_content,
        $langConfirmDelete, $langNewLTIAppSessionDesc, $langNote,
        $langTitle,$langActivate, $langDeactivate, $langLTIAppActions,
        $langEditChange, $langDelete, $langNoLTIApps, $m;

    load_js('trunk8');

    $activeClause = ($is_editor) ? '' : "AND enabled = 1";
    $result = Database::get()->queryArray("SELECT * FROM lti_apps
        WHERE course_id = ?s $activeClause AND is_template = 0 ORDER BY title ASC", $course_id);
    if ($result) {
        $headingsSent = false;
        $headings = "<div class='row'>
                       <div class='col-md-12'>
                         <div class='table-responsive'>
                           <table class='table-default'>
                             <tr class='list-header'>
                               <th style='width:30%'>$langTitle</th>
                               <th class='text-center'>$langNewLTIAppSessionDesc</th>
                               <th class='text-center'>$langLTIAppActions</th>";
        if ($is_editor) {
            $headings .= "<th class='text-center'>" . icon('fa-gears') . "</th>";
        }
        $headings .= "</tr>";

        foreach ($result as $row) {
            $id = $row->id;
            $title = $row->title;

            $desc = isset($row->description)? $row->description: '';

            $canJoin = ($row->enabled == 1 || $is_editor);
            if ($canJoin) {
                if ($row->launchcontainer == LTI_LAUNCHCONTAINER_EMBED) {
                    $joinLink = create_launch_button($row->id);
                } else {
                    $joinLink = create_join_button(
                        $row->lti_provider_url,
                        $row->lti_provider_key,
                        $row->lti_provider_secret,
                        $row->id,
                        "lti_tool",
                        $row->title,
                        $row->description,
                        $row->launchcontainer
                    );
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
                    <td class='text-left'>$title</td>
                    <td>$desc</td>
                    <td class='text-center'>$joinLink</td>
                    <td class='option-btn-cell'>".
                        action_button(array(
                            array(  'title' => $langEditChange,
                                    'url' => "../lti_consumer/index.php?course=$course_code&amp;id=" . getIndirectReference($id) . "&amp;choice=edit",
                                    'icon' => 'fa-edit'),
                            array(  'title' => $row->enabled? $langDeactivate : $langActivate,
                                    'url' => "../lti_consumer/index.php?id=" . getIndirectReference($row->id) . "&amp;choice=do_".
                                             ($row->enabled? 'disable' : 'enable'),
                                    'icon' => $row->enabled? 'fa-eye': 'fa-eye-slash'),
                            array(  'title' => $langDelete,
                                    'url' => "../lti_consumer/index.php?id=" . getIndirectReference($row->id) . "&amp;choice=do_delete",
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
                    <td class='text-center'>$joinLink</td>
                    </tr>";
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
    global $langLTIAppUpdateSuccessful, $course_code;

    Database::get()->querySingle("UPDATE lti_apps set enabled = 0 WHERE id = ?d",$id);
    Session::Messages($langLTIAppUpdateSuccessful, 'alert-success');
    redirect_to_home_page("modules/course_tools/index.php?course=$course_code");
}

function enable_lti_app($id)
{
    global $langLTIAppUpdateSuccessful, $course_code;

    Database::get()->querySingle("UPDATE lti_apps SET enabled = 1 WHERE id = ?d",$id);
    Session::Messages($langLTIAppUpdateSuccessful, 'alert-success');
    redirect_to_home_page("modules/course_tools/index.php?course=$course_code");
}


/**
 * @param $id
 * @brief delete lti app
 */
function delete_lti_app($id)
{
    Database::get()->query("DELETE FROM course_lti_app WHERE lti_app=?d", $id);
    Database::get()->querySingle("DELETE FROM lti_apps WHERE id=?d",$id);

}

function lti_build_signature($launch_url, $secret, $launch_data) {
    $launch_data_keys = array_keys($launch_data);
    sort($launch_data_keys);

    $launch_params = array();
    foreach ($launch_data_keys as $key) {
        array_push($launch_params, $key . "=" . rawurlencode($launch_data[$key]));
    }

    $base_string = "POST&" . urlencode($launch_url) . "&" . rawurlencode(implode("&", $launch_params));
    $secret = urlencode($secret) . "&";
    return base64_encode(hash_hmac("sha1", $base_string, $secret, true));
}

function lti_prepare_launch_data($course_id, $course_code, $language, $uid, $oauth_consumer_key, $resource_link_id, $resource_link_type, $resource_link_title, $resource_link_description, $launchcontainer, $extraobj = null) {
    global $urlServer;

    $now = new DateTime();
    $udata = user_get_data($uid);
    $lis_person_name_given = $udata->givenname;
    $lis_person_name_family = $udata->surname;
    $lis_person_name_full = $udata->givenname . " " . $udata->surname;
    $lis_person_contact_email_primary = $udata->email;
    if (strlen($udata->surname) <= 0 && strlen($udata->givenname) > 0) {
        $lis_person_name_full = $udata->givenname;
        $lis_person_name_family = "";
        $lis_person_name_given = "";
    }
    $launch_presentation_document_target = ($launchcontainer == LTI_LAUNCHCONTAINER_EMBED) ? 'iframe' : 'window';

    // clean title and description
    $clean_title = str_replace(array("\r", "\n", "\""), " ", $resource_link_title);
    $clean_description = str_replace(array("\r", "\n", "&", "\""), " ",strip_tags( $resource_link_description));
    if (strlen($clean_description) > LTI_DESCRIPTION_MAX_LENGTH) {
        $clean_description = mb_substr($clean_description, 0, LTI_DESCRIPTION_MAX_LENGTH);
    }
    $clean_course_title = str_replace(array("\r", "\n", "\""), " ", course_id_to_title($course_id));

    $launch_data = array(
        "user_id" => $uid,
        "roles" => lti_get_ims_role(),
        "resource_link_id" => $_SERVER['SERVER_NAME'] . ":" . $resource_link_type . ":" . $resource_link_id,
        "resource_link_title" => $clean_title,
        "resource_link_description" => $clean_description,
        "lis_person_name_full" => $lis_person_name_full,
        "lis_person_name_family" => $lis_person_name_family,
        "lis_person_name_given" => $lis_person_name_given,
        "lis_person_contact_email_primary" => $lis_person_contact_email_primary,
        "context_id" => $_SERVER['SERVER_NAME'] . ":" . $course_id,
        "context_title" => $clean_course_title,
        "context_label" => $course_code,
        "context_type" => "CourseSection",
        "lis_course_section_sourcedid" => $_SERVER['SERVER_NAME'] . ":" . $course_id,
        "launch_presentation_locale" => $language,
        "launch_presentation_document_target" => $launch_presentation_document_target,
        "launch_presentation_return_url" => $urlServer . "courses/" . $course_code,
        "tool_consumer_info_product_family_code" => "openeclass",
        "tool_consumer_info_version" => ECLASS_VERSION,
        "tool_consumer_instance_name" => $GLOBALS['siteName'],
        "tool_consumer_instance_guid" => $_SERVER['SERVER_NAME'],
        "tool_consumer_instance_description" => $GLOBALS['Institution'],
        "lti_version" => "LTI-1p0",
        "lti_message_type" => "basic-lti-launch-request",
        "oauth_callback" => "about:blank",
        "oauth_consumer_key" => $oauth_consumer_key,
        "oauth_version" => "1.0",
        "oauth_nonce" => uniqid('', true),
        "oauth_timestamp" => $now->getTimestamp(),
        "oauth_signature_method" => "HMAC-SHA1"
    );

    if ($extraobj != null) {
        $launch_data['custom_startdate'] = gmstrftime("%Y-%m-%dT%TZ", strtotime($extraobj->submission_date));
        if ($extraobj->deadline != NULL) {
            $launch_data['custom_duedate'] = gmstrftime("%Y-%m-%dT%TZ", strtotime($extraobj->deadline));
        }
        if ($extraobj->tii_feedbackreleasedate != NULL) {
            $launch_data['custom_feedbackreleasedate'] = gmstrftime("%Y-%m-%dT%TZ", strtotime($extraobj->tii_feedbackreleasedate));
        }
        $launch_data['custom_maxpoints'] = intval($extraobj->max_grade);
        $launch_data['custom_late_accept_flag'] = intval($extraobj->late_submission);
        $launch_data['custom_internetcheck'] = intval($extraobj->tii_internetcheck);
        $launch_data['custom_journalcheck'] = intval($extraobj->tii_journalcheck);
        $launch_data['custom_report_gen_speed'] = intval($extraobj->tii_report_gen_speed);
        $launch_data['custom_s_view_reports'] = intval($extraobj->tii_s_view_reports);
        $launch_data['custom_studentpapercheck'] = intval($extraobj->tii_studentpapercheck);
        $launch_data['custom_use_biblio_exclusion'] = intval($extraobj->tii_use_biblio_exclusion);
        $launch_data['custom_use_quoted_exclusion'] = intval($extraobj->tii_use_quoted_exclusion);
        $launch_data['custom_exclude_type'] = $extraobj->tii_exclude_type;
        $launch_data['custom_exclude_value'] = intval($extraobj->tii_exclude_value);
        //$launch_data['custom_institutioncheck'] = intval($extraobj->tii_institutioncheck);
        //$launch_data['custom_submit_papers_to'] = intval($extraobj->tii_submit_papers_to);

        if ($resource_link_type == "assignment") {
            $assignment_secret = Database::get()->querySingle("SELECT secret_directory FROM assignment WHERE id = ?d", $resource_link_id)->secret_directory;
            $token = token_generate($assignment_secret, true);
            $launch_data['lis_result_sourcedid'] = $token . "-" . $resource_link_id . "-" . $uid;
            $launch_data['ext_outcomes_tool_placement_url'] = $urlServer . "modules/work/tii_placement.php";
            $launch_data['lis_outcome_service_url'] = $urlServer . "modules/work/tii_outcome.php";
        }
    }

    return $launch_data;
}

function create_launch_button($resource_link_id) {
    global $urlServer, $course_code, $langLogIn;

    $button = '<form id="ltiLaunchForm" name="ltiLaunchForm" method="POST" action="' . $urlServer . "modules/lti_consumer/launch.php?course=" . $course_code . "&amp;id=" . getIndirectReference($resource_link_id) . '">';
    $button .= '<button class="btn btn-primary" type="submit">' . $langLogIn . '</button>';
    $button .= '</form>';

    return $button;
}

function create_join_button($launch_url, $oauth_consumer_key, $secret, $resource_link_id, $resource_link_type, $resource_link_title, $resource_link_description, $launchcontainer, $extrabutton = '', $extraobj = null) {
    global $course_id, $course_code, $language, $uid, $langLogIn;

    $launch_data = lti_prepare_launch_data(
        $course_id,
        $course_code,
        $language,
        $uid,
        $oauth_consumer_key,
        $resource_link_id,
        $resource_link_type,
        $resource_link_title,
        $resource_link_description,
        $launchcontainer,
        $extraobj
    );
    $signature_url = $launch_url;

    // re-organize signature url and data if launch url contains query get parameters
    $launch_url_parts = parse_url($launch_url);
    if (array_key_exists('query', $launch_url_parts)) {
        $launch_query_args = [];
        parse_str($launch_url_parts['query'], $launch_query_args);
        $launch_data = array_merge($launch_data, $launch_query_args);
        $signature_url = $launch_url_parts['scheme'].'://'.$launch_url_parts['host'];
        if (array_key_exists('path', $launch_url_parts)) {
            $signature_url .= $launch_url_parts['path'];
        }
    }

    $signature = lti_build_signature($signature_url, $secret, $launch_data);

    $formtarget = ($launchcontainer == LTI_LAUNCHCONTAINER_NEWWINDOW) ? 'target="_blank"' : '';
    $button ='<form id="ltiLaunchForm" name="ltiLaunchForm" method="POST" ' . $formtarget . ' action="' . $launch_url . '">';
    foreach ($launch_data as $k => $v) {
        $button .='<input type="hidden" name="' . $k .'" value="' . $v . '">';
    }
    $button .='<input type="hidden" name="oauth_signature" value="' . $signature . '">';
    $button .= $extrabutton . '<button class="btn btn-primary" type="submit">' . $langLogIn . '</button>';
    $button .='</form>';

    return $button;  
}

function tii_post_request($url, $post_data, $download_file = false, $local_filename = '') {
    $response = null;
    $http_code = null;
    $headers = array();
    if (!extension_loaded('curl')) {
        return array($response, $http_code, $headers);
    }

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$headers) {
        $len = strlen($header);
        $header = explode(':', $header, 2);
        if (count($header) < 2) { // ignore invalid headers
            return $len;
        }

        $name = strtolower(trim($header[0]));
        if (!array_key_exists($name, $headers)) {
            $headers[$name] = [trim($header[1])];
        } else {
            $headers[$name][] = trim($header[1]);
        }

        return $len;
    });
    if ($download_file) {
        $fp = fopen($local_filename, 'w');
        curl_setopt($ch, CURLOPT_FILE, $fp);
    }

    $response = curl_exec($ch);
    if(!curl_errno($ch)) {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }
    curl_close($ch);
    if ($download_file) {
        fclose($fp);
    }

    return array($response, $http_code, $headers);
}

function lti_prepare_oauth_only_data($url, $oauth_consumer_key, $secret) {
    $now = new DateTime();
    $launch_data = array(
        "oauth_consumer_key" => $oauth_consumer_key,
        "oauth_version" => "1.0",
        "oauth_nonce" => uniqid('', true),
        "oauth_timestamp" => $now->getTimestamp(),
        "oauth_signature_method" => "HMAC-SHA1"
    );
    $signature = lti_build_signature($url, $secret, $launch_data);
    $launch_data['oauth_signature'] = $signature;
    return $launch_data;
}

/**
 * Gets the IMS role string for the specified user and course
 *
 * @return string A role string suitable for passing with an LTI launch
 */
function lti_get_ims_role() {
	global $is_editor, $is_admin, $is_course_admin;

	$roles = array();

	if ($is_editor) {
		array_push($roles, 'Instructor');
	} else {
		array_push($roles, 'Learner');
	}

	if ($is_admin || $is_course_admin) {
		// admins do not need the Learner role, set ims admin role instead
		$roles = array_diff($roles, array('Learner'));
		array_push($roles, 'urn:lti:sysrole:ims/lis/Administrator', 'urn:lti:instrole:ims/lis/Administrator');
	}

	return join(',', $roles);
}

function lti_get_containers_selection() {
    global $langLTILaunchContainerEmbed, $langLTILaunchContainerNewWindow, $langLTILaunchContainerExistingWindow;

    return array(LTI_LAUNCHCONTAINER_EMBED => $langLTILaunchContainerEmbed,
        LTI_LAUNCHCONTAINER_NEWWINDOW => $langLTILaunchContainerNewWindow,
        LTI_LAUNCHCONTAINER_EXISTINGWINDOW => $langLTILaunchContainerExistingWindow);
}

function lti_verify_extract_sourcedid($sourcedid, $ts_valid_time) {
    // extract sourcedid info
    $sourcediddata = explode("-", $sourcedid);
    if (count($sourcediddata) != 4) {
        error_log("invalid lis_result_sourcedid, exiting ($sourcedid)...");
        die();
    }
    $token = $sourcediddata[0] . "-" . $sourcediddata[1];
    $assignment_id = intval($sourcediddata[2]);
    $uid = intval($sourcediddata[3]);

    // locate/validate assignment, lti, user and token
    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $assignment_id);
    if (!$assignment) {
        error_log("no assignment found, exiting ($sourcedid)...");
        die();
    }
    if (!token_validate($assignment->secret_directory, $token, $ts_valid_time )) {
        error_log("invalid token, exiting ($sourcedid)...");
        die();
    }
    $lti = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d ", $assignment->lti_template);
    if (!$lti) {
        error_log("no lti found, exiting...");
        die();
    }
    $user = Database::get()->querySingle("SELECT * FROM user WHERE id  = ?d", $uid);
    if (!$user) {
        error_log("no user found, exiting...");
        die();
    }

    return array($assignment_id, $uid, $assignment, $lti, $user);
}

function getLTILinksForTools() {
    global $course_id, $course_code, $urlServer, $is_editor;

    $activeClause = ($is_editor) ? '' : "AND enabled = 1";
    $rows = Database::get()->queryArray("SELECT * FROM lti_apps
        WHERE course_id = ?d $activeClause AND is_template = 0 ORDER BY title ASC", $course_id);

    if ($rows) {
        $result = array();
        foreach ($rows as $row) {
            $ret = new stdClass();
            $ret->title = $row->title;

            switch ($row->launchcontainer) {
                case LTI_LAUNCHCONTAINER_EMBED:
                    $ret->url = $urlServer . "modules/lti_consumer/launch.php?course=" . $course_code . "&id=" . getIndirectReference($row->id);
                    $ret->menulink = 'fa-link';
                    break;
                case LTI_LAUNCHCONTAINER_NEWWINDOW:
                    $ret->url = $urlServer . "modules/lti_consumer/load.php?course=" . $course_code . "&id=" . getIndirectReference($row->id);
                    $ret->menulink = 'fa-external-link';
                    break;
                case LTI_LAUNCHCONTAINER_EXISTINGWINDOW:
                    $ret->url = $urlServer . "modules/lti_consumer/load.php?course=" . $course_code . "&id=" . getIndirectReference($row->id);
                    $ret->menulink = 'fa-link';
                    break;
                default:
                    $ret->url = $urlServer . "modules/lti_consumer/launch.php?course=" . $course_code . "&id=" . getIndirectReference($row->id);
                    $ret->menulink = 'fa-link';
                    break;
            }

            $result[] = $ret;
        }
        return $result;
    } else {
        return false;
    }
}

function is_active_external_lti_app($externalapp, $lti_type, $course_id) {
    if ($externalapp->isEnabled()) {
        $q = Database::get()->querySingle("SELECT id, course_id, all_courses FROM lti_apps WHERE `type` = ?s", $lti_type);
        if (!is_null($q->course_id)) {
            return true;
        } else {
            if ($q->all_courses == 1) { // external app is enabled for all courses
                return true;
            } else { // otherwise check if external app is enabled for specific course
                $s = Database::get()->querySingle("SELECT * FROM course_lti_app WHERE course_id = ?d AND lti_app = $q->id", $course_id);
                if ($s) {
                    return true;
                }
            }
        }
    } else {
        return false;
    }
}
