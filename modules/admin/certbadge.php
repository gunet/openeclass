<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

$require_admin = true;
require_once '../../include/baseTheme.php';

$toolName = $langCertBadgeAdmin;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);


$tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => 'index.php',
              'icon' => 'fa-reply',
              'level' => 'primary'),
        array('title' => "$langAddNewCertTemplate",
              'url' => "$_SERVER[SCRIPT_NAME]?action=add_cert",
              'icon' => 'fa-plus-circle',
              'level' => 'primary-label',
              'button-class' => 'btn-success'),
        array('title' => "$langAddNewBadgeTemplate",
              'url' => "$_SERVER[SCRIPT_NAME]?action=add_badge",
              'icon' => 'fa-plus-circle',
              'level' => 'primary-label',
              'button-class' => 'btn-success')
        ));

if (isset($_GET['del_badge'])) { // delete badge icon
    $sql_badge_icon = Database::get()->querySingle("SELECT id, filename FROM badge_icon WHERE id = ?d", $_GET['del_badge']);
    $badge_icon_id = $sql_badge_icon->id;
    $cnt = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM badge WHERE icon = ?d", $badge_icon_id)->cnt;

    if ($cnt > 0) {  // don't delete if it's used by a badge (foreign key constrain)
        Session::flash('message',$langIconBelongsToBadge);
        Session::flash('alert-class', 'alert-warning');
    } else {
        $badge_icon = $sql_badge_icon->filename;
        if (unlink($webDir . BADGE_TEMPLATE_PATH . $badge_icon)) {
            Database::get()->query("DELETE FROM badge_icon WHERE id = ?d", $_GET['del_badge']);
            //Session::Messages($langDelWithSuccess, 'alert-success');
            Session::flash('message',$langDelWithSuccess);
            Session::flash('alert-class', 'alert-success');
        }
    }
}

if (isset($_GET['del_cert'])) { // delete certificate template
    $sql_cert_template = Database::get()->querySingle("SELECT id, filename FROM certificate_template WHERE id = ?d", $_GET['del_cert']);
    $cert_template_id = $sql_cert_template->id;
    $cnt = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM certificate WHERE template = ?d", $cert_template_id)->cnt;
    if ($cnt > 0) { // don't delete it if it's used by a certificate (foreign key constrain)
        //Session::Messages($langTemplateBelongsToCert, 'alert-warning');
        Session::flash('message',$langTemplateBelongsToCert);
        Session::flash('alert-class', 'alert-warning');
    } else {
        $cert_template = $sql_cert_template->filename;
        if (unlink($webDir . CERT_TEMPLATE_PATH . $cert_template)) {
            Database::get()->query("DELETE FROM certificate_template WHERE id = ?d", $_GET['del_cert']);
            //Session::Messages($langDelWithSuccess, 'alert-success');
            Session::flash('message',$langDelWithSuccess);
            Session::flash('alert-class', 'alert-success');
        }
    }
}

if (isset($_POST['submit_cert_template'])) { // insert certificate template
    if (isset($_POST['cert_id'])) {
        if ($_FILES['filename']['size'] > 0) { // replace file if needed
            $filename = $_FILES['filename']['name'];
            if (move_uploaded_file($_FILES['filename']['tmp_name'], "$webDir" . CERT_TEMPLATE_PATH . "$filename")) {
                $archive = new ZipArchive;
                if ($archive->open("$webDir" . CERT_TEMPLATE_PATH . "$filename") == TRUE) {
                    if ($archive->extractTo("$webDir" . CERT_TEMPLATE_PATH)) {
                        $archive->close();
                        $old_file = Database::get()->querySingle("SELECT filename FROM certificate_template WHERE id = ?d", $_POST['cert_id'])->filename;
                        unlink($webDir . CERT_TEMPLATE_PATH . $old_file); // delete old template
                        Database::get()->querySingle("UPDATE certificate_template SET
                                                        name = ?s,
                                                        description = ?s,
                                                        filename = ?s
                                                       WHERE id = ?d",
                                                    $_POST['name'], $_POST['description'], $_POST['certhtmlfile'], $_POST['cert_id']);
                        //Session::Messages($langDownloadEnd, 'alert-success');
                        Session::flash('message',$langDownloadEnd);
                        Session::flash('alert-class', 'alert-success');
                    } else {
                        die("Error : Zip file couldn't be extracted!");
                    }
                }
            }
        } else {
            Database::get()->querySingle("UPDATE certificate_template SET
                                            name = ?s,
                                            description = ?s,
                                            orientation = ?s
                                        WHERE id = ?d",
                                    $_POST['name'], $_POST['description'], $_POST['orientation'], $_POST['cert_id']);
        }
    } else {
        $filename = $_FILES['filename']['name'];
        if (move_uploaded_file($_FILES['filename']['tmp_name'], "$webDir" . CERT_TEMPLATE_PATH . "$filename")) {
            $archive = new ZipArchive;
            if ($archive->open("$webDir" . CERT_TEMPLATE_PATH . "$filename") == TRUE) {
                if ($archive->extractTo("$webDir" . CERT_TEMPLATE_PATH)) {
                    $archive->close();
                    Database::get()->querySingle("INSERT INTO certificate_template SET
                                        name = ?s,
                                        description = ?s,
                                        filename = ?s,
                                        orientation = ?s", $_POST['name'], $_POST['description'], $_POST['certhtmlfile'], $_POST['orientation']);
                    //Session::Messages($langDownloadEnd, 'alert-success');
                    Session::flash('message',$langDownloadEnd);
                    Session::flash('alert-class', 'alert-success');
                } else {
                    die("Error : Zip file couldn't be extracted!");
                }
            }
        }
    }

} elseif (isset($_POST['submit_badge_icon'])) { // insert / update badge icon
    if (!isset($_POST['token']) or !validate_csrf_token($_POST['token'])) {
        forbidden();
    }
    $new_icon = $old_icon = $filename = null;
    $badge_id = isset($_POST['badge_id'])? $_POST['badge_id']: null;
    if ($_FILES['icon']['size'] > 0) {
        $filename = $_FILES['icon']['name'];
        $extension = strtolower(get_file_extension($filename));
        if (!in_array($extension, ['png', 'jpg', 'jpeg'])) {
            Session::flash('message', $langUploadedFileNotAllowed . ' ' . q($filename));
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/admin/certbadge.php');
        }
        $new_icon = ($badge_id? "$badge_id-": '') . bin2hex(random_bytes(8)) . '.' . $extension;
    }
    $name = canonicalize_whitespace($_POST['name'] ?? '');
    if (!$name) {
        Session::Messages($langEmptyNodeName, 'alert-warning');
        redirect_to_home_page('modules/admin/certbadge.php');
    }
    if ($badge_id) {
        $old_icon = Database::get()->querySingle('SELECT filename FROM badge_icon WHERE id = ?d', $_POST['badge_id'])->filename;
    }
    if (move_uploaded_file($_FILES['icon']['tmp_name'], $webDir . BADGE_TEMPLATE_PATH . $new_icon)) {
        if ($old_icon) {
            unlink($webDir . BADGE_TEMPLATE_PATH . $old_icon); // delete old icon if needed
        }
    }
    if ($badge_id) {
        Database::get()->querySingle("UPDATE badge_icon SET
            name = ?s,
            description = ?s,
            filename = ?s
            WHERE id = ?d", $_POST['name'], $_POST['description'], $new_icon ?? $old_icon, $badge_id);
    } else {
        Database::get()->querySingle("INSERT INTO badge_icon SET
            name = ?s,
            description = ?s,
            filename = ?s", $_POST['name'], $_POST['description'], $new_icon);
    }
    Session::Messages($langDownloadEnd, 'alert-success');
    redirect_to_home_page('modules/admin/certbadge.php');
}

// display forms
if (isset($_GET['action'])) {
    if (($_GET['action'] == 'add_cert') or ($_GET['action'] == 'edit_cert')) { // add certificate template
        $cert_name = $cert_description = $cert_hidden_id = $cert_htmlfile = '';
        $cert_orientation_l = 'checked';
        $cert_orientation_p = '';
        if (isset($_GET['cid'])) {
            $cert_id = $_GET['cid'];
            $cert_data = Database::get()->querySingle("SELECT * FROM certificate_template WHERE id = ?d", $cert_id);
            $cert_name = $cert_data->name;
            $cert_description = $cert_data->description;
            $cert_htmlfile = $cert_data->filename;
            $cert_orientation = $cert_data->orientation;
            if ($cert_orientation == "P") {
                $cert_orientation_l = '';
                $cert_orientation_p = 'checked';
            }
            $cert_hidden_id = "<input type='hidden' name='cert_id' value='$cert_id'>";
        }
        $tool_content .= "
        <div class='row'>

            <div class='col-lg-6 col-12'>
                <div class='form-wrapper form-edit border-0 px-0'>
                    <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post' enctype='multipart/form-data'>

                            <div class='form-group'>
                                <label class='col-sm-12 control-label-notes'>$langZipFile</label>
                                <input type='file' class='' name='filename' value=''>

                            </div>

                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>$langHtmlFile</label>
                                <div class='col-sm-12'>
                                    <input type='text' placeholder='$langHtmlFile...' class='form-control' name='certhtmlfile' value='$cert_htmlfile'>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>$langTemplateName</label>
                                <div class='col-sm-12'>
                                    <input type='text' placeholder='$langTemplateName...' class='form-control' name='name' value='$cert_name'>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes mb-2'>$langOrientation</label>
                                <div class='radio mb-2'>
                                    <label><input type='radio' name='orientation' $cert_orientation_l value='L'>$langLandscape</label>
                                </div>
                                <div class='radio'>
                                    <label><input type='radio' name='orientation' $cert_orientation_p value='P'>$langPortrait</label>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                            <label for='description' class='col-sm-12 control-label-notes'>$langDescription</label>
                                <div class='col-sm-12'>
                                    " . rich_text_editor('description', 2, 60, $cert_description) . "
                                </div>
                            </div>
                            $cert_hidden_id

                            <div class='form-group mt-5 d-flex justify-content-end align-items-center'>
                                <button class='btn submitAdminBtn' type ='submit' name='submit_cert_template'>$langUpload</button>
                                <a class='btn cancelAdminBtn ms-1' href='index.php'>$langCancel</a>
                            </div>
                    </form>
                </div>
            </div>
            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
            </div>
        </div>";
    } elseif (($_GET['action'] == 'add_badge') or  ($_GET['action'] == 'edit_badge')) { // add badge icons
        $badge_name = $badge_description = $badge_hidden_id = '';
        if (isset($_GET['bid'])) {
            $badge_id = $_GET['bid'];
            $badge_data = Database::get()->querySingle("SELECT * FROM badge_icon WHERE id = ?d", $badge_id);
            $badge_name = $badge_data->name;
            $badge_description = $badge_data->description;
            $badge_hidden_id = "<input type='hidden' name='badge_id' value='$badge_id'>";
        }

        $tool_content .= "
            <div class='row'>
                <div class='col-lg-6 col-12'>
                    <div class='form-wrapper form-edit border-0 px-0'>
                        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post' enctype='multipart/form-data'>" .
                        generate_csrf_token_form_field();
                        if (isset($_GET['bid'])) {
                            $icon_link = $urlServer . BADGE_TEMPLATE_PATH . "$badge_data->filename";
                            $tool_content .= "<div class='form-group'>
                                <label class='col-sm-12 control-label-notes'>$langReplace:</label>
                                <div class='col-sm-12'>
                                    <img src='$icon_link' width='60' height='60'>
                                    <input type='file' name='icon' value=''>
                                </div>
                            </div>";
                        } else {
                            $tool_content .= "<div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>$langIcon:</label>
                                <div class='col-sm-12'>
                                    <input type='file' name='icon' value=''>
                                </div>
                            </div>";
                        }
                        $tool_content .= "<div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>$langName</label>
                                <div class='col-sm-12'>
                                    <input type='text' class='form-control' placeholder='$langName...' name='name' value='$badge_name'>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                            <label for='description' class='col-sm-12 control-label-notes'>$langDescription: </label>
                                <div class='col-sm-12'>
                                    " . rich_text_editor('description', 2, 60, $badge_description) . "
                                </div>
                            </div>
                            $badge_hidden_id

                            <div class='form-group mt-5 d-flex justify-content-end align-items-center'>
                                <button class='btn submitAdminBtn' type ='submit' name='submit_badge_icon'>$langUpload</button>
                                 <a class='btn cancelAdminBtn ms-1' href='index.php'>$langCancel</a>
                            </div>

                    </form>
                </div>
            </div>
            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
            </div>
        </div>";
    }
} else { // display available certificates / badges
    $sql1 = Database::get()->queryArray("SELECT * FROM certificate_template");
    $tool_content .= "<div class='table-responsive'>
                        <table class='table-default'>
                        <thead>
                            <tr>
                                <th style='width:30%;'>$langTitle</th>
                                <th style='width:60%;'>$langDescription</th>
                                <th style='width:10%;'></th>
                            </tr>
                        </thead>";

                foreach ($sql1 as $cert_data) {
                    //$template_link = $urlServer . CERT_TEMPLATE_PATH ."$cert_data->filename";
                    $tool_content .= "<tr><td style='width:30%;'>$cert_data->name</td>
                                      <td style='width:60%;'>" . ellipsize_html($cert_data->description, 100) . "</td>";
                    $tool_content .= "<td style='width:10%;' class='text-end option-btn-cell'>".
                            action_button(array(
                                array('title' => $langEdit,
                                    'icon' => 'fa-edit',
                                    'url' => "$_SERVER[SCRIPT_NAME]?action=edit_cert&amp;cid=$cert_data->id"
                                    ),
                                array('title' => $langDelete,
                                    'icon' => 'fa-xmark',
                                    'url' => "$_SERVER[SCRIPT_NAME]?del_cert=$cert_data->id",
                                    'confirm' => $langConfirmDelete,
                                    'class' => 'delete')
                                )).
                            "</td></tr>";
                }
    $tool_content .= "</table>";
    $tool_content .= "</div>";

    $sql2 = Database::get()->queryArray("SELECT * FROM badge_icon");

    $tool_content .= "<div class='table-responsive mt-5'>
                        <table class='table-default'>
                        <thead>
                        <tr>
                            <th style='width:30%;'>$langTitle</th>
                            <th style='width:60%;'>$langDescription</th>
                            <th style='width:10%;'>$langIcon</th>
                            <th style='width:10%;'></th>
                        </tr>
                        </thead>";
                foreach ($sql2 as $badge_data) {
                    $icon_link = $urlServer . BADGE_TEMPLATE_PATH ."$badge_data->filename";
                    $tool_content .= "<tr><td style='width:30%;'>$badge_data->name</td>
                                      <td style='width:50%;'>" . ellipsize_html($badge_data->description, 100) . "</td>
                                      <td style='width:10%;' ><img src='$icon_link' width='50' height='50'></td>";
                    $tool_content .= "<td style='width:10%;' class='text-end option-btn-cell'>".
                            action_button(array(
                                array('title' => $langEdit,
                                    'icon' => 'fa-edit',
                                    'url' => "$_SERVER[SCRIPT_NAME]?action=edit_badge&amp;bid=$badge_data->id"
                                    ),
                                array('title' => $langDelete,
                                    'icon' => 'fa-xmark',
                                    'url' => "$_SERVER[SCRIPT_NAME]?del_badge=$badge_data->id",
                                    'confirm' => $langConfirmDelete,
                                    'class' => 'delete'))).
                            "</td></tr>";
                }
    $tool_content .= "</table>";
    $tool_content .= "</div>";
}
draw($tool_content, null);
