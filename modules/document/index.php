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


$is_in_tinymce = isset($_REQUEST['embedtype']) && $_REQUEST['embedtype'] == 'tinymce';

if (!isset($require_current_course)) {
    $require_current_course = !(defined('COMMON_DOCUMENTS') or defined('MY_DOCUMENTS'));
}

if (!isset($require_login)) {
    $require_login = defined('MY_DOCUMENTS') or defined('COMMON_DOCUMENTS');
}

$guest_allowed = true;
require_once '../../include/baseTheme.php';
/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_DOCS);

require_once 'doc_init.php';
require_once 'doc_metadata.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'modules/search/indexer.class.php';
require_once 'include/log.class.php';
require_once 'modules/drives/clouddrive.php';
require_once 'include/course_settings.php';

$require_help = true;
$helpTopic = 'documents';

doc_init();


if ($is_editor) {

    if (isset($_GET['prevent_pdf'])) {
        $filePath = getDirectReference($_GET['pdf']);
        Database::get()->query("UPDATE document SET prevent_download = ?d WHERE path = ?s AND course_id = ?d", $_GET['prevent_pdf'], $filePath, $course_id);
        if ($_GET['prevent_pdf'] > 0) {
            Session::Messages($langPreventEnablePDF, 'alert-success');
        } else {
            Session::Messages($langPreventDisablePDF, 'alert-success');
        }
        $curDirPath = my_dirname($filePath);
        redirect_to_current_dir();
    }

    if (isset($_GET['unzip'])) {
        $myFile = $basedir.$_GET['unzip'];

        if (isset($_GET['openDir'])) {
            $openDir = $_GET['openDir'];
        } else {
            $openDir = '';
        }

        /* ** Unzipping stage ** */
        $files_in_zip = array();
        $zipFile = new ZipArchive();
        $realFileSize = 0;

        if ($zipFile->open($myFile) === TRUE) {
            // check for file type in zip contents
            for ($i = 0; $i < $zipFile->numFiles; $i++) {
                $stat = $zipFile->statIndex($i, ZipArchive::FL_ENC_RAW);
                $files_in_zip[$i] = $stat['name'];
                if (!empty(my_basename($files_in_zip[$i]))) {
                    validateUploadedFile(my_basename($files_in_zip[$i]), 3);
                }
            }
            // extract files
            for ($i = 0; $i < $zipFile->numFiles; $i++) {
                $stat = $zipFile->statIndex($i, ZipArchive::FL_ENC_RAW);
                $realFileSize += $stat["size"]; // check for free space
                $extracted_file_name = process_extracted_file($stat);
                if (!is_null($extracted_file_name)) {
                    $zipFile->renameIndex($i, $extracted_file_name);
                    $zipFile->extractTo($basedir, $extracted_file_name);
                }
            }
            $zipFile->close();
        } else {
            Session::Messages($langErrorFileMustBeZip, 'alert-warning');
            redirect_to_current_dir();
        }
        $session->setDocumentTimestamp($course_id);
        Session::Messages($langDownloadAndZipEnd, 'alert-success');
        redirect_to_current_dir();
    }

    if (isset($_POST['bulk_submit'])) {

        if ($_POST['selectedcbids']) {

            $cbids = explode(',', $_POST['selectedcbids']);


            if ($_POST['bulk_action'] == 'delete') {

                $filepaths = explode(',', $_POST['filepaths']);
                foreach (array_combine($cbids, $filepaths) as $row_id => $filePath) {

                    $curDirPath = my_dirname($filePath);
                    // Check if file actually exists
                    $r = Database::get()->querySingle("SELECT id, path, extra_path, format, filename, lock_user_id FROM document
                                        WHERE $group_sql AND path = ?s", $filePath);
                    $delete_ok = true;
                    if ($r) {
                        if (resource_belongs_to_progress_data(MODULE_ID_DOCS, $r->id)) {
                            Session::Messages($langResourceBelongsToCert, 'alert-warning');
                        } else {
                            // remove from index if relevant (except non-main sysbsystems and metadata)
                            Database::get()->queryFunc("SELECT id FROM document WHERE course_id >= 1 AND subsystem = 0
                                                AND format <> '.meta' AND path LIKE ?s",
                                function ($r2) {
                                    Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_DOCUMENT, $r2->id);
                                    if (resource_belongs_to_progress_data(MODULE_ID_DOCS, $r2->id)) {
                                        Session::Messages(trans('langResourceBelongsToCert'), 'alert-warning');
                                    }
                                },
                                $filePath . '%');

                            if (empty($r->extra_path)) {
                                if ($delete_ok = my_delete($basedir . $filePath) && $delete_ok) {
                                    if (hasMetaData($filePath, $basedir, $group_sql)) {
                                        $delete_ok = my_delete($basedir . $filePath . ".xml") && $delete_ok;
                                    }
                                    update_db_info('document', 'delete', $filePath, $r->filename);
                                }
                            } else {
                                update_db_info('document', 'delete', $filePath, $r->filename);
                            }
                            if(isset($_GET['ebook_id'])){
                                Database::get()->query("DELETE FROM ebook_subsection WHERE file_id = ?d", $r->id);
                            }
                            if ($delete_ok) {
                                Session::Messages($langDocDeleted, 'alert-success');
                            } else {
                                Session::Messages($langGeneralError, 'alert-danger');
                            }
                        }
                    }

                }

            }
            if ($_POST['bulk_action'] == 'visible') {
                foreach ($cbids as $row_id) {
                    Database::get()->query("UPDATE document SET visible = ?d WHERE id = ?d", 1, $row_id);
                    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $row_id);
                }
            }
            if ($_POST['bulk_action'] == 'invisible') {
                foreach ($cbids as $row_id) {
                    Database::get()->query("UPDATE document SET visible = ?d WHERE id = ?d", 0, $row_id);
                    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $row_id);
                }
            }

            if ($_POST['bulk_action'] == 'move') {
                if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
                $moveTo = $_POST['source_path'];
                $moveTo = getDirectReference($moveTo);
                $filepaths = explode(',', $_POST['filepaths']);
                $existingFilesArr = [];
                foreach ($filepaths as $source) {

                    $sourceXml = $source . '.xml';
                    // check if source and destination are the same
                    if ($source != $moveTo) {
                        $r = Database::get()->querySingle("SELECT filename, extra_path FROM document WHERE $group_sql AND path = ?s", $source);
                        $filename = $r->filename;
                        $extra_path = $r->extra_path;
                        // Check if target filename already exists
                        $curDirPath = $moveTo;
                        $fileExists = Database::get()->querySingle("SELECT id FROM document
                        WHERE $group_sql AND path REGEXP ?s AND filename = ?s LIMIT 1",
                            "^$curDirPath/[^/]+$", $filename);
                        if ($fileExists) {
                            $curDirPath = my_dirname($source);
                            $existingFilesArr[] = q($filename);
                            $existingFiles = implode(', ', $existingFilesArr);
                            Session::Messages($langFileExists . ' ' . $existingFiles, 'alert-danger');
                        } else {
                            if (empty($extra_path)) {
                                if (move($basedir . $source, $basedir . $moveTo)) {
                                    if (hasMetaData($source, $basedir, $group_sql)) {
                                        move($basedir . $sourceXml, $basedir . $moveTo);
                                    }
                                    update_db_info('document', 'update', $source, $filename, $moveTo . '/' . my_basename($source));
                                }
                            } else {
                                update_db_info('document', 'update', $source, $filename, $moveTo . '/' . my_basename($source));
                            }
                            Session::Messages($langDirMv, 'alert-success');
                            $curDirPath = $moveTo;
                        }

                    } else {
                        Session::Messages($langImpossible, 'alert-danger');
                        // return to step 1
                        $_GET['move'] = $source;
                    }
                }
            }
        }
        redirect_to_current_dir();
    }
}


if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && $is_editor) {
    /* save video recorded data */
    if (isset($_FILES['video-blob'])) {
        $title = $_POST['userFile'];
        $file_path = '/' . safe_filename('webm');
        if (!move_uploaded_file($_FILES['video-blob']['tmp_name'], $basedir . $file_path)) {
            Session::flash('message', $langGeneralError);
            Session::flash('alert-class', 'alert-danger');
        } else {
            $filename = $title;
            $file_creator = "$_SESSION[givenname] $_SESSION[surname]";
            $file_date = date('Y-m-d G:i:s');
            $file_format = 'webm';
            Database::get()->query("INSERT INTO document SET
            course_id = ?d,
            subsystem = ?d,
            subsystem_id = ?d,
            path = ?s,
            extra_path = '',
            filename = ?s,
            visible = 1,
            comment = '',
            category = 0,
            title = ?s,
            creator = ?s,
            date = ?s,
            date_modified = ?s,
            subject = '',
            description = '',
            author = ?s,
            format = ?s,
            copyrighted = 0,
            editable = 0,
            lock_user_id = ?d",
                $course_id, $subsystem, $subsystem_id, $file_path,
                $filename, $title, $file_creator,
                $file_date, $file_date, $file_creator, $file_format,
                $language, $uid);
            Session::Messages($langDownloadEnd, 'alert-success');
            exit();
        }
    }
    /* save audio recorded data */
    if (isset($_FILES['audio-blob'])) {
        $title = $_POST['userFile'];
        $file_path = '/' . safe_filename('mka');
        if (!move_uploaded_file($_FILES['audio-blob']['tmp_name'], $basedir . $file_path)) {
            Session::flash('message', $langGeneralError);
            Session::flash('alert-class', 'alert-danger');
        } else {
            $filename = $title;
            $file_creator = "$_SESSION[givenname] $_SESSION[surname]";
            $file_date = date('Y-m-d G:i:s');
            $file_format = 'mka';
            Database::get()->query("INSERT INTO document SET
            course_id = ?d,
            subsystem = ?d,
            subsystem_id = ?d,
            path = ?s,
            extra_path = '',
            filename = ?s,
            visible = 1,
            comment = '',
            category = 0,
            title = ?s,
            creator = ?s,
            date = ?s,
            date_modified = ?s,
            subject = '',
            description = '',
            author = ?s,
            format = ?s,
            language = ?s,
            copyrighted = 0,
            editable = 0,
            lock_user_id = ?d",
                $course_id, $subsystem, $subsystem_id, $file_path,
                $filename, $title, $file_creator,
                $file_date, $file_date, $file_creator, $file_format,
                $language, $uid);
            Session::Messages($langDownloadEnd, 'alert-success');
            exit();
        }
    }
}

// Used to check for quotas
$diskUsed = dir_total_space($basedir);

$user_upload = $uid && $subsystem == MAIN && get_config('enable_docs_public_write') && setting_get(SETTING_DOCUMENTS_PUBLIC_WRITE);
$uploading_as_user = !$can_upload && $user_upload;

if (defined('COMMON_DOCUMENTS')) {
    $menuTypeID = 3;
    $data['menuTypeID'] = $menuTypeID;
    $toolName = $langCommonDocs;
    $diskQuotaDocument = $diskUsed + parseSize(ini_get('upload_max_filesize'));
} elseif (defined('MY_DOCUMENTS')) {
    if ($session->status == USER_TEACHER and !get_config('mydocs_teacher_enable')) {
        redirect_to_home_page();
    }
    if ($session->status == USER_STUDENT and !get_config('mydocs_student_enable')) {
        redirect_to_home_page();
    }
    $menuTypeID = 1;
    $data['menuTypeID'] = $menuTypeID;
    $toolName = $langMyDocs;
    if ($session->status == USER_TEACHER) {
        $diskQuotaDocument = get_config('mydocs_teacher_quota') * 1024 * 1024;
    } else {
        $diskQuotaDocument = get_config('mydocs_student_quota') * 1024 * 1024;
    }
} else {
    $menuTypeID = 2;
    $toolName = $langDoc;
    $type = ($subsystem == GROUP) ? 'group_quota' : 'doc_quota';
    $diskQuotaDocument = Database::get()->querySingle("SELECT $type AS quotatype FROM course WHERE id = ?d", $course_id)->quotatype;
}

if ($is_in_tinymce) {
    $menuTypeID = 5;
    $_SESSION['embedonce'] = true; // necessary for baseTheme
    $docsfilter = (isset($_REQUEST['docsfilter'])) ? 'docsfilter=' . $_REQUEST['docsfilter'] . '&amp;' : '';
    $base_url .= 'embedtype=tinymce&amp;' . $docsfilter;
    load_js('tinymce.popup.urlgrabber.min.js');
}

load_js('tools.js');
load_js('screenfull/screenfull.min.js');
ModalBoxHelper::loadModalBox(true);
copyright_info_init();

if (defined('EBOOK_DOCUMENTS')) {
    $navigation[] = array('url' => 'edit.php?course=' . $course_code . '&id=' . $ebook_id, 'name' => $langEBookEdit);
}

if (isset($_GET['showQuota'])) {
    $backUrl = documentBackLink('');
    $navigation[] = array('url' => $backUrl, 'name' => $pageName);
    showquota($diskQuotaDocument, $diskUsed);
    exit;
}

$dialogBox = $metaDataBox = '';

$dialogData = [
    'movePath' => '',
    'filename' => '',
    'file' => '',
    'directories' => '' ];

// ---------------------------
// Mindmap save button
// ---------------------------
if (isset($_GET['mindmap'])) {
    $mindmap = $_GET['mindmap'];
    $title = $_GET['mindtitle'];

    $file_path = '/' . safe_filename('jm');
    if (!file_put_contents($basedir . $file_path, $_GET['mindmap'])) {
        Session::flash('message', $langGeneralError);
        Session::flash('alert-class', 'alert-danger');
    } else {
        $filename = $title . '.jm';
        $file_creator = "$_SESSION[givenname] $_SESSION[surname]";
        $file_date = date('Y-m-d G:i:s');
        $file_format = 'jm';
        Database::get()->query("INSERT INTO document SET
            course_id = ?d,
            subsystem = ?d,
            subsystem_id = ?d,
            path = ?s,
            extra_path = '',
            filename = ?s,
            visible = 1,
            comment = '',
            category = 0,
            title = ?s,
            creator = ?s,
            date = ?s,
            date_modified = ?s,
            subject = '',
            description = '',
            author = ?s,
            format = ?s,
            copyrighted = 0,
            editable = 0,
            lock_user_id = ?d",
            $course_id, $subsystem, $subsystem_id, $file_path,
            $filename, $title, $file_creator,
            $file_date, $file_date, $file_creator, $file_format,
            $uid);
            Session::Messages($langMindMapSaved, 'alert-success');
    }
    redirect_to_home_page('modules/document/index.php?course=' . $course_code);
}


// ---------------------------
// Mindmap screenshot save
// ---------------------------
if (isset($_POST['imgBase64'])) {
    $shootname = $_POST['imgname'];
    $img = $_POST['imgBase64'];
    $img = str_replace('data:image/png;base64,', '', $img);
    $img = str_replace(' ', '+', $img);
    $fileData = base64_decode($img);
    $file_path = '/' . safe_filename('png');
    $file_date = date('Y-m-d G:i:s');

    // mindmap save in database
    if (file_put_contents($basedir . $file_path, $fileData)) {
        $file_creator = "$_SESSION[givenname] $_SESSION[surname]";
        $filename = $shootname . '.png';
        $file_format = 'png';
        Database::get()->query("INSERT INTO document SET
            course_id = ?d,
            subsystem = ?d,
            subsystem_id = ?d,
            path = ?s,
            extra_path = '',
            filename = ?s,
            visible = 1,
            comment = '',
            category = 0,
            title = ?s,
            creator = ?s,
            date = ?s,
            date_modified = ?s,
            subject = '',
            description = '',
            author = ?s,
            format = ?s,
            language = ?s,
            copyrighted = 0,
            editable = 0,
            lock_user_id = ?d",
            $course_id, $subsystem, $subsystem_id, $file_path,
            $filename, $shootname, $file_creator,
            $file_date, $file_date, $file_creator, $file_format, $language,
            $uid);
    }
    exit;
}


// ---------------------------
// download directory or file
// ---------------------------
if (isset($_GET['download'])) {
    $downloadDir = getDirectReference($_GET['download']);

    if ($downloadDir == '/') {
        $format = '.dir';
        $real_filename = remove_filename_unsafe_chars($langDoc . ' ' . $public_code);
    } else {
        $q = Database::get()->querySingle("SELECT filename, format, visible, extra_path, public FROM document
                        WHERE $group_sql AND
                        path = ?s", $downloadDir);
        if (!$q) {
            not_found($downloadDir);
        }
        $real_filename = $q->filename;
        $format = $q->format;
        $visible = $q->visible;
        $extra_path = $q->extra_path;
        $public = $q->public;
        if (!(resource_access($visible, $public) or (isset($status) and $status == USER_TEACHER))) {
            not_found($downloadDir);
        }
    }
    // Allow unlimited time for creating the archive
    set_time_limit(0);

    if ($format == '.dir') {
        if (!$uid) {
            forbidden($downloadDir);
        }
        $real_filename = $real_filename . '.zip';
        $dload_filename = $webDir . '/courses/temp/' . safe_filename('zip');
        zip_documents_directory($dload_filename, $downloadDir, $can_upload);
        $delete = true;
    } elseif ($extra_path) {
        if ($real_path = common_doc_path($extra_path, true)) {
            // Common document
            if (!$common_doc_visible) {
                forbidden($downloadDir);
            }
            $dload_filename = $real_path;
            $delete = false;
        } else {
            // External document - redirect to URL
            redirect($extra_path);
        }
    } else {
        $dload_filename = $basedir . $downloadDir;
        $delete = false;
    }

    send_file_to_client($dload_filename, $real_filename, null, true, $delete);
    exit;
}


if ($can_upload or $user_upload) {
    $fileName = '';
    $error = false;
    $uploaded = false;
    if (isset($_POST['uploadPath'])) {
        $curDirPath = $uploadPath = $_POST['uploadPath'];
    } elseif (isset($_POST['editPath'])) {
        $curDirPath = $uploadPath = my_dirname($_POST['editPath']);
    }
    // Check if upload path exists
    if (!empty($uploadPath)) {
        $result = Database::get()->querySingle("SELECT id FROM document
                        WHERE $group_sql AND path = ?s LIMIT 1", $uploadPath);
        if (!$result or !$result->id) {
            $error = $langImpossible;
        }
    }

    /* ******************************************************************** *
      UPLOAD FILE
     * ******************************************************************** */

    // How to handle errors - session is for Uppy uploads, collects errors in $_SESSION['upload_errors']
    $XHRUpload = $_POST['XHRUpload'] ?? false;
    $error_response = $XHRUpload? 'session': 'html';
    $extra_path = '';
    if (isset($_POST['fileCloudInfo']) or isset($_FILES['userFile'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        if (isset($_POST['fileCloudInfo'])) { // upload cloud file
            $cloudfile = CloudFile::fromJSON($_POST['fileCloudInfo']);
            $uploaded = true;
            $fileName = $cloudfile->name();
        } else if (isset($_FILES['userFile']) and is_uploaded_file($_FILES['userFile']['tmp_name'])) { // upload local file
            $fileName = $_FILES['userFile']['name'];
            $userFile = $_FILES['userFile']['tmp_name'];
        }
        // check file type
        validateUploadedFile($fileName, $menuTypeID, $error_response);
        // check for disk quotas
        if ($diskUsed + @$_FILES['userFile']['size'] > $diskQuotaDocument) {
            if ($XHRUpload) {
                $_SESSION['upload_errors'][] = $langNoSpace;
                http_response_code(400);
                die;
            } else {
                Session::Messages($langNoSpace, 'alert-danger');
                redirect_to_current_dir();
            }
        } elseif (isset($_POST['uncompress']) and $_POST['uncompress'] == 1 and preg_match('/\.zip$/i', $fileName)) {
            /* ** Unzipping stage ** */
            $files_in_zip = array();
            $zipFile = new ZipArchive();
            $realFileSize = 0;
            if ($zipFile->open($userFile) == TRUE) {
                // check for file type in zip contents
                for ($i = 0; $i < $zipFile->numFiles; $i++) {
                    $stat = $zipFile->statIndex($i, ZipArchive::FL_ENC_RAW);
                    $files_in_zip[$i] = $stat['name'];
                    if (!empty(my_basename($files_in_zip[$i]))) {
                        validateUploadedFile(my_basename($files_in_zip[$i]), $menuTypeID, $error_response);
                    }
                }
                // extract files
                for ($i = 0; $i < $zipFile->numFiles; $i++) {
                    $stat = $zipFile->statIndex($i, ZipArchive::FL_ENC_RAW);
                    $realFileSize += $stat["size"]; // check for free space
                    if ($diskUsed + $realFileSize > $diskQuotaDocument) {
                        Session::flash('message',$langNoSpace);
                        Session::flash('alert-class', 'alert-danger');
                        redirect_to_current_dir();
                    }
                    $extracted_file_name = process_extracted_file($stat);
                    if (!is_null($extracted_file_name)) {
                        $zipFile->renameIndex($i, $extracted_file_name);
                        $zipFile->extractTo($basedir, $extracted_file_name);
                    }
                }
                $zipFile->close();
            } else {
                Session::Messages($langErrorFileMustBeZip, 'alert-warning');
                redirect_to_current_dir();
            }
            $session->setDocumentTimestamp($course_id);
            if ($XHRUpload) {
                http_response_code(200);
                die;
            }
            Session::Messages($langDownloadAndZipEnd, 'alert-success');
            redirect_to_current_dir();
        } else {
            $fileName = canonicalize_whitespace($fileName);
            $uploaded = true;
        }
    } elseif (isset($_POST['fileURL']) and ($fileURL = trim($_POST['fileURL']))) {
        $extra_path = canonicalize_url($fileURL);
        if (preg_match('/^javascript/', $extra_path)) {
            Session::Messages($langUnwantedFiletype . ': ' . q($extra_path), 'alert-danger');
            redirect_to_current_dir();
        } else {
            $uploaded = true;
        }
        $components = explode('/', trim($extra_path, '/'));
        $fileName = end($components);
    } elseif (isset($_POST['file_content'])) {

        if ($diskUsed + strlen($_POST['file_content']) > $diskQuotaDocument) {
            Session::Messages($langNoSpace, 'alert-danger');
            redirect_to_current_dir();
        } else {
            $fileName = newPageFileName($uploadPath, 'page_', '.html');
            $uploaded = true;
        }
    }
    if ($uploaded and !isset($_POST['editPath'])) {
        // Check if file already exists
        if (isset($fileURL)) {
            $checkFileSQL = 'extra_path = ?s';
            $checkFileName = $extra_path;
        } else {
            $checkFileSQL = 'filename = ?s';
            $checkFileName = $fileName;
        }
        $result = Database::get()->querySingle("SELECT path, visible, lock_user_id FROM document WHERE
                                           $group_sql AND
                                           path REGEXP ?s AND
                                           $checkFileSQL LIMIT 1",
                                        "^$uploadPath/[^/]+$", $checkFileName);
        if ($result) {
            if (isset($_POST['replace']) and $_POST['replace'] == 1 and (!$uploading_as_user or $result->lock_user_id == $uid)) {
                // Delete old file record when replacing file
                $file_path = $result->path;
                $vis = $result->visible;
                Database::get()->query("DELETE FROM document WHERE
                                                 $group_sql AND
                                                 path = ?s", $file_path);
            } else {
                $error = $langFileExists;
            }
        }
    }
    if ($error) {
        if ($XHRUpload) {
            $_SESSION['upload_errors'][] = $error;
            http_response_code(400);
            die;
        } else {
            Session::Messages($error, 'alert-danger');
            redirect_to_current_dir();
        }
    } elseif ($uploaded) {
        // No errors, so proceed with upload
        // File date is current date
        $file_date = date("Y\-m\-d G\:i\:s");
        // Try to add an extension to files without extension,
        $fileName = add_ext_on_mime($fileName);
        // File name used in file system and path field
        $safe_fileName = safe_filename(get_file_extension($fileName));
        if ($uploadPath == '.') {
            $file_path = '/' . $safe_fileName;
        } else {
            $file_path = $uploadPath . '/' . $safe_fileName;
        }
        $fileUploadOK = false;
        if (isset($cloudfile)) {
            try {
                $fileUploadOK = ($cloudfile->storeToLocalFile($basedir . $file_path) == CloudDriveResponse::OK);
            } catch (Exception $e) {
                Session::Messages($langCloudFileError, 'alert-danger');
            }
        } elseif (isset($userFile)) {
            $fileUploadOK = @copy($userFile, $basedir . $file_path);
        }
        require_once 'modules/admin/extconfig/externals.php';
        $connector = AntivirusApp::getAntivirus();
        if($connector->isEnabled()) {
            $output=$connector->check($basedir . $file_path);
            if($output->status==$output::STATUS_INFECTED){
                AntivirusApp::block($output->output);
            }
        }

        if ($extra_path or $fileUploadOK) {
            $vis = 1;
            $file_format = get_file_extension($fileName);
            $id = Database::get()->query("INSERT INTO document SET
                                        course_id = ?d,
                                        subsystem = ?d,
                                        subsystem_id = ?d,
                                        path = ?s,
                                        extra_path = ?s,
                                        filename = ?s,
                                        visible = ?d,
                                        comment = ?s,
                                        title = ?s,
                                        date = ?t,
                                        date_modified = ?t,
                                        format = ?s,
                                        language = ?s,
                                        copyrighted = ?d,
                                        lock_user_id = ?d"
                            , $course_id, $subsystem, $subsystem_id, $file_path, $extra_path, $fileName, $vis
                            , $_POST['file_comment'] ?? '', $_POST['file_title'] ?? ''
                            , $file_date, $file_date
                            , $file_format, $language, $_POST['file_copyrighted'], $uid)->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $id);
            // Logging
            Log::record($course_id, MODULE_ID_DOCS, LOG_INSERT, [
                'id' => $id,
                'filepath' => $file_path,
                'filename' => $fileName,
                'comment' => $_POST['file_comment'] ?? '',
                'title' => $_POST['file_title'] ?? ''
            ]);
            $session->setDocumentTimestamp($course_id);
            if ($XHRUpload) {
                http_response_code(200);
                die;
            } else {
                Session::Messages($langDownloadEnd, 'alert-success');
                redirect_to_current_dir();
            }
        } elseif (isset($_POST['file_content'])) {
            $v = new Valitron\Validator($_POST);
            $v->rule('required', array('file_title'));
            $v->labels(array(
                'file_title' => "$langTheField $langTitle"
            ));
            if ($v->validate()) {
                $q = false;
                if (isset($_POST['editPath'])) {
                    $fileInfo = Database::get()->querySingle("SELECT * FROM document
                        WHERE $group_sql AND path = ?s", $_POST['editPath']);
                    if ($fileInfo->editable) {
                        $file_path = $fileInfo->path;
                        $q = Database::get()->query("UPDATE document
                                SET date_modified = NOW(), title = ?s
                                WHERE $group_sql AND path = ?s",
                                $_POST['file_title'], $_POST['editPath']);
                        $id = $fileInfo->id;
                        $fileName = $fileInfo->filename;
                    }
                } else {
                    $safe_fileName = safe_filename(get_file_extension($fileName));
                    $file_path = $uploadPath . '/' . $safe_fileName;
                    $file_date = date("Y\-m\-d G\:i\:s");
                    $file_format = get_file_extension($fileName);
                    $file_creator = "$_SESSION[givenname] $_SESSION[surname]";
                    $q = Database::get()->query("INSERT INTO document SET
                                course_id = ?d,
                                subsystem = ?d,
                                subsystem_id = ?d,
                                path = ?s,
                                extra_path = '',
                                filename = ?s,
                                visible = 1,
                                comment = '',
                                category = 0,
                                title = ?s,
                                creator = ?s,
                                date = ?s,
                                date_modified = ?s,
                                subject = '',
                                description = '',
                                author = ?s,
                                format = ?s,
                                language = ?s,
                                copyrighted = 0,
                                editable = 1,
                                lock_user_id = ?d",
                                $course_id, $subsystem, $subsystem_id, $file_path,
                                $fileName, $_POST['file_title'], $file_creator,
                                $file_date, $file_date, $file_creator, $file_format,
                                $language, $uid);
                }
                if ($q) {
                    if (!isset($id)) {
                        $id = $q->lastInsertID;
                        $log_action = LOG_INSERT;
                    } else {
                        $log_action = LOG_MODIFY;
                    }
                    $ebookSectionTitle = $_POST['file_title'] ? $_POST['file_title'] : $fileName;
                    if (isset($_GET['ebook_id']) && isset($_POST['section_id'])){
                        if(isset($_POST['editPath'])){
                            Database::get()->query("UPDATE ebook_subsection
                                SET section_id = ?s, title = ?s WHERE file_id = ?d",
                                $_POST['section_id'], $ebookSectionTitle, $id);
                        } else {
                            $subsectionOrder = Database::get()->querySingle("SELECT COALESCE(MAX(public_id), 0) + 1 AS subsection_order
                                FROM ebook_subsection WHERE section_id = ?d", $_POST['section_id']);
                            Database::get()->query("INSERT INTO ebook_subsection
                                SET section_id = ?s, file_id = ?d, title = ?s, public_id = ?s",
                                $_POST['section_id'], $id, $ebookSectionTitle, $subsectionOrder->subsection_order);
                        }
                    }
                    Log::record($course_id, MODULE_ID_DOCS, $log_action,
                            array('id' => $id,
                                  'filepath' => $file_path,
                                  'filename' => $fileName,
                                  'title' => $_POST['file_title']));
                    $title = $_POST['file_title']? $_POST['file_title']: $fileName;
                    file_put_contents($basedir . $file_path,
                        "<!DOCTYPE html>\n" .
                        "<head>\n" .
                        "  <meta charset='utf-8'>\n" .
                        '  <title>' . q($title) . "</title>\n</head>\n<body>\n" .
                        purify($_POST['file_content']) .
                        "\n</body>\n</html>\n");
                    $session->setDocumentTimestamp($course_id);
                    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $id);
                    Session::Messages($langDownloadEnd, 'alert-success');
                    if (isset($_GET['from']) and $_GET['from'] == 'ebookEdit') {
                        $redirect_url = "modules/ebook/edit.php?course=$course_code&id=$ebook_id";
                    } else {
                        redirect_to_current_dir();
                    }
                }
            } else {
                Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
                if (isset($_GET['ebook_id']) && isset($_POST['section_id'])){
                    $append_to_url = isset($_GET['from']) && $_GET['from'] == 'ebookEdit' ? "&from=ebookEdit" : "";
                    $redirect_url = "modules/ebook/new.php?course=$course_code&ebook_id=$ebook_id$append_to_url";
                } else {
                    $append_to_url = isset($_POST['editPath']) ? "&editPath=$_POST[editPath]" : "&uploadPath=$curDirPath";
                    $redirect_url = "modules/document/new.php?course=$course_code$append_to_url";
                }
            }
            redirect_to_home_page($redirect_url);
        }
    }

    /**************************************
      MOVE FILE OR DIRECTORY
     **************************************/
    // Move file or directory: Step 2
    if (isset($_POST['moveTo'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        $moveTo = getDirectReference($_POST['moveTo']);
        $source = $_POST['movePath'];
        $sourceXml = $source . '.xml';
        // check if source and destination are the same
        if ($source != $moveTo) {
            $r = Database::get()->querySingle("SELECT filename, extra_path FROM document WHERE $group_sql AND path = ?s", $source);
            $filename = $r->filename;
            $extra_path = $r->extra_path;
            // Check if target filename already exists
            $curDirPath = $moveTo;
            $fileExists = Database::get()->querySingle("SELECT id FROM document
                    WHERE $group_sql AND path REGEXP ?s AND filename = ?s LIMIT 1",
                    "^$curDirPath/[^/]+$", $filename);
            if ($fileExists) {
                $curDirPath = my_dirname($source);
                Session::Messages($langFileExists, 'alert-danger');
                redirect_to_current_dir();
            }
            if (empty($extra_path)) {
                if (move($basedir . $source, $basedir . $moveTo)) {
                    if (hasMetaData($source, $basedir, $group_sql)) {
                        move($basedir . $sourceXml, $basedir . $moveTo);
                    }
                    update_db_info('document', 'update', $source, $filename, $moveTo . '/' . my_basename($source));
                }
            } else {
                update_db_info('document', 'update', $source, $filename, $moveTo . '/' . my_basename($source));
            }
            Session::flash($langDirMv, 'alert-success');
            $curDirPath = $moveTo;
            redirect_to_current_dir();
        } else {
            Session::Messages($langImpossible, 'alert-danger');
            // return to step 1
            $_GET['move'] = $source;
        }
    }

    // Move file or directory: Step 1
    if (isset($_GET['move'])) {
        $file = $_GET['file'];
        $dialogBox = 'move';
        $movePath = getDirectReference($_GET['move']);
        $curDirPath = my_dirname($movePath);
        $navigation[] = array('url' => documentBackLink($curDirPath), 'name' => $pageName);

        // $move contains file path - search for filename in db
        $q = Database::get()->querySingle("SELECT filename, format FROM document
                                                WHERE $group_sql AND path=?s", $movePath);
        $exclude = ($q->format == '.dir')? $movePath: '';
        $dialogData = array(
            'movePath' => $movePath,
            'filename' => $q->filename,
            'file' => $file,
            'directories' => directory_selection($movePath, 'moveTo', $curDirPath, $exclude));
    }

    // Delete file or directory
    if (isset($_GET['delete']) and isset($_GET['filePath'])) {
        if (!isset($_REQUEST['token']) || !validate_csrf_token($_REQUEST['token'])) csrf_token_error();
        $filePath =  getDirectReference($_GET['filePath']);
        $curDirPath = my_dirname(getDirectReference($_GET['filePath']));
        // Check if file actually exists
        $r = Database::get()->querySingle("SELECT id, path, extra_path, format, filename, lock_user_id FROM document
                                        WHERE $group_sql AND path = ?s", $filePath);
        $delete_ok = true;
        if ($r and (!$uploading_as_user or $r->lock_user_id == $uid)) {
            if (resource_belongs_to_progress_data(MODULE_ID_DOCS, $r->id)) {
                Session::Messages($langResourceBelongsToCert, 'alert-warning');
            } else {
                // remove from index if relevant (except non-main sysbsystems and metadata)
                Database::get()->queryFunc("SELECT id FROM document WHERE course_id >= 1 AND subsystem = 0
                                                AND format <> '.meta' AND path LIKE ?s",
                    function ($r2) use($langResourceBelongsToCert) {
                        Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_DOCUMENT, $r2->id);
                        if (resource_belongs_to_progress_data(MODULE_ID_DOCS, $r2->id)) {
                            Session::flash('message',$langResourceBelongsToCert);
                            Session::flash('alert-class', 'alert-warning');
                            redirect_to_current_dir();
                        }
                    },
                    $filePath . '%');

                if (empty($r->extra_path)) {
                    if ($delete_ok = my_delete($basedir . $filePath) && $delete_ok) {
                        if (hasMetaData($filePath, $basedir, $group_sql)) {
                            $delete_ok = my_delete($basedir . $filePath . ".xml") && $delete_ok;
                        }
                        update_db_info('document', 'delete', $filePath, $r->filename);
                    }
                } else {
                    update_db_info('document', 'delete', $filePath, $r->filename);
                }
                if(isset($_GET['ebook_id'])){
                    Database::get()->query("DELETE FROM ebook_subsection WHERE file_id = ?d", $r->id);
                }
                if ($delete_ok) {
                    Session::Messages($langDocDeleted, 'alert-success');
                } else {
                    Session::Messages($langGeneralError, 'alert-danger');
                }
            }
        }
        redirect_to_current_dir();
    }

    /*****************************************
      RENAME
     ******************************************/
    // Step 2: Rename file by updating record in database
    if (isset($_POST['renameTo'])) {

        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

        $renameTo = canonicalize_whitespace($_POST['renameTo']);
        $sourceFile = getDirectReference($_POST['sourceFile']);
        $r = Database::get()->querySingle("SELECT id, filename, format FROM document
            WHERE $group_sql AND path = ?s", $sourceFile);

        if ($r->format != '.dir') {
            validateRenamedFile($renameTo, $menuTypeID);
        }

        Database::get()->query("UPDATE document SET filename = ?s, date_modified = NOW()
                          WHERE $group_sql AND path=?s", $renameTo, $sourceFile);
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $r->id);
        Log::record($course_id, MODULE_ID_DOCS, LOG_MODIFY, array(
            'path' => $sourceFile,
            'filename' => $r->filename,
            'newfilename' => $renameTo));
        if (hasMetaData($sourceFile, $basedir, $group_sql)) {
            if (Database::get()->query("UPDATE document SET filename = ?s WHERE $group_sql AND path = ?s",
                    $renameTo . '.xml',
                    $sourceFile . '.xml')->affectedRows > 0) {
                metaRenameDomDocument($basedir . $sourceFile . '.xml', $renameTo);
            }
        }
        $curDirPath = my_dirname($sourceFile);
        Session::Messages($langElRen, 'alert-success');
        redirect_to_current_dir();
    }

    // Step 1: Show rename dialog box
    if (isset($_GET['rename'])) {
        $dialogBox = 'rename';
        $renamePath = getDirectReference($_GET['rename']);
        $curDirPath = my_dirname($renamePath);
        $backUrl = documentBackLink($curDirPath);
        $navigation[] = array('url' => $backUrl, 'name' => $pageName);
        $r = Database::get()->querySingle("SELECT id, filename, format, lock_user_id FROM document
            WHERE $group_sql AND path = ?s", $renamePath);
        if ($r and (!$uploading_as_user or $r->lock_user_id == $uid)) {
            $dialogData = array(
                'renamePath' => $_GET['rename'],
                'filename' => $r->filename,
                'filenameLabel' => $r->format == '.dir'? $langDirectory : $langFileName);
        }
    }

    // create directory
    // step 2: create the new directory
    if (isset($_POST['newDirPath'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        $newDirName = canonicalize_whitespace($_POST['newDirName']);
        if (!empty($newDirName)) {
            $newDirPath = make_path($_POST['newDirPath'], array($newDirName));
            // $path_already_exists: global variable set by make_path()
            if ($path_already_exists) {
                Session::flash('message',$langFileExists);
                Session::flash('alert-class', 'alert-danger');
            } else {
                $session->setDocumentTimestamp($course_id);
                $r = Database::get()->querySingle("SELECT id FROM document WHERE $group_sql AND path = ?s", $newDirPath);
                Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $r->id);
                Session::Messages($langDirCr, 'alert-success');
            }
            $curDirPath = $_POST['newDirPath'];
            redirect_to_current_dir();
        }
    }

    // step 1: display a field to enter the new dir name
    if (isset($_GET['createDir'])) {
        $dialogBox = 'createDir';
        $curDirPath = $_GET['createDir'];
        $backLink = documentBackLink($curDirPath);
        $navigation[] = array('url' => $backLink, 'name' => $pageName);
    }

    // add/update/remove comment
    if (isset($_POST['commentPath'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        $commentPath = $_POST['commentPath'];
        // check if file exists
        $res = Database::get()->querySingle("SELECT * FROM document
                                             WHERE $group_sql AND
                                                   path=?s", $commentPath);
        if ($res and (!$uploading_as_user or $res->lock_user_id == $uid)) {
            if ($res->format == '.dir') {
                Database::get()->query("UPDATE document SET comment = ?s
                     WHERE $group_sql AND path = ?s", $_POST['file_comment'], $commentPath);
            } else {
                Database::get()->query("UPDATE document SET
                                                comment = ?s,
                                                title = ?s,
                                                date_modified = " . DBHelper::timeAfter() . ",
                                                copyrighted = ?d
                                        WHERE $group_sql AND
                                              path = ?s"
                    , $_POST['file_comment'], $_POST['file_title']
                    , $_POST['file_copyrighted'], $commentPath);
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $res->id);
            Log::record($course_id, MODULE_ID_DOCS, LOG_MODIFY, array('path' => $commentPath,
                'filename' => $res->filename,
                'comment' => $_POST['file_comment'],
                'title' => $_POST['file_title']));
            }
            $curDirPath = my_dirname($commentPath);
            Session::Messages($langComMod, 'alert-success');
            redirect_to_current_dir();
        }
    }

    // add/update/remove metadata
    // $metadataPath contains the path to the file the metadata applies to
    if (isset($_POST['metadataPath'])) {
        $curDirPath = my_dirname($_POST['metadataPath']);
        $navigation[] = array('url' => documentBackLink($curDirPath), 'name' => $pageName);
        $metadataPath = $_POST['metadataPath'] . ".xml";
        $oldFilename = $_POST['meta_filename'] . ".xml";
        $xml_filename = $basedir . str_replace('/..', '', $metadataPath);
        $xml_date = date("Y\-m\-d G\:i\:s");
        $file_format = ".meta";

        metaCreateDomDocument($xml_filename);

        $result = Database::get()->querySingle("SELECT * FROM document WHERE $group_sql AND path = ?s", $metadataPath);
        if ($result) {
            Database::get()->query("UPDATE document SET
                                creator = ?s,
                                date_modified = NOW(),
                                format = ?s,
                                WHERE $group_sql AND path = ?s",
                "$_SESSION[givenname] $_SESSION[surname]", $file_format, $metadataPath);
        } else {
            Database::get()->query("INSERT INTO document SET
                                course_id = ?d ,
                                subsystem = ?d ,
                                subsystem_id = ?d ,
                                path = ?s,
                                filename = ?s ,
                                visible = 0,
                                creator = ?s,
                                date = ?t ,
                                date_modified = ?t ,
                                format = ?s,
                                language = ?s,
                                lock_user_id = ?d",
                $course_id, $subsystem, $subsystem_id, $metadataPath, $oldFilename,
                "$_SESSION[givenname] $_SESSION[surname]", $xml_date, $xml_date,
                $file_format, $language, $uid);
        }

        Session::Messages($langMetadataMod, 'alert-success');
        redirect_to_current_dir();
    }

    if (isset($_POST['replacePath']) and
            isset($_FILES['newFile']) and
            is_uploaded_file($_FILES['newFile']['tmp_name'])) {
        validateUploadedFile($_FILES['newFile']['name'], $menuTypeID);
        $replacePath = getDirectReference($_POST['replacePath']);
        // Check if file actually exists
        $result = Database::get()->querySingle("SELECT id, path, format, lock_user_id FROM document WHERE
                                        $group_sql AND
                                        format <> '.dir' AND
                                        path=?s", $replacePath);
        if ($result and (!$uploading_as_user or $result->lock_user_id == $uid)) {
            $docId = $result->id;
            $oldpath = $result->path;
            $oldformat = $result->format;
            // check for disk quota
            if ($diskUsed - filesize($basedir . $oldpath) + $_FILES['newFile']['size'] > $diskQuotaDocument) {
                Session::Messages($langNoSpace, 'alert-danger');
                redirect_to_current_dir();
            } else {
                $newformat = get_file_extension($_FILES['newFile']['name']);
                $newpath = preg_replace("/\\.$oldformat$/", '', $oldpath) .
                        (empty($newformat) ? '' : '.' . $newformat);
                my_delete($basedir . $oldpath);
                $affectedRows = Database::get()->query("UPDATE document SET path = ?s, format = ?s, filename = ?s, date_modified = NOW()
                          WHERE $group_sql AND path = ?s"
                                , $newpath, $newformat, ($_FILES['newFile']['name']), $oldpath)->affectedRows;
                if (!copy($_FILES['newFile']['tmp_name'], $basedir . $newpath) or $affectedRows == 0) {
                    Session::flash('message',$langGeneralError);
                    Session::flash('alert-class', 'alert-danger');
                    redirect_to_current_dir();
                } else {
                    require_once 'modules/admin/extconfig/externals.php';
                    $connector = AntivirusApp::getAntivirus();
                    if($connector->isEnabled() == true ){
                        $output=$connector->check($basedir . $newpath);
                        if($output->status==$output::STATUS_INFECTED){
                            AntivirusApp::block($output->output);
                        }
                    }
                    if (hasMetaData($oldpath, $basedir, $group_sql)) {
                        rename($basedir . $oldpath . ".xml", $basedir . $newpath . ".xml");
                        Database::get()->query("UPDATE document SET path = ?s, filename=?s WHERE $group_sql AND path = ?s"
                                , ($newpath . ".xml"), ($_FILES['newFile']['name'] . ".xml"), ($oldpath . ".xml"));
                    }
                    $session->setDocumentTimestamp($course_id);
                    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $docId);
                    Log::record($course_id, MODULE_ID_DOCS, LOG_MODIFY, array('oldpath' => $oldpath,
                        'newpath' => $newpath,
                        'filename' => $_FILES['newFile']['name']));
                    $curDirPath = my_dirname($replacePath);
                    Session::Messages($langReplaceOK, 'alert-success');
                    redirect_to_current_dir();
                }
            }
        }
    }

    // Display form to replace/overwrite an existing file
    if (isset($_GET['replace'])) {
        $result = Database::get()->querySingle("SELECT filename, path, lock_user_id FROM document
                                        WHERE $group_sql AND
                                                format <> '.dir' AND
                                                path = ?s",  getDirectReference($_GET['replace']));
        if ($result and (!$uploading_as_user or $result->lock_user_id == $uid)) {
            $dialogBox = 'replace';
            $curDirPath = my_dirname($result->path);
            $backUrl = documentBackLink($curDirPath);
            $navigation[] = array('url' => $backUrl, 'name' => $pageName);
            enableCheckFileSize();
            $dialogData = array(
                'filename' => $result->filename,
                'curDirPath' => $curDirPath,
                'replacePath' => $_GET['replace'],
                'replaceMessage' => sprintf($langReplaceFile, '<span class="Primary-500-cl">' . q($result->filename) . '</span>'));
        }
    }

    // Display comment form
    if (isset($_GET['comment'])) {
        $comment = getDirectReference($_GET['comment']);
        // Retrieve the old comment and metadata
        $row = Database::get()->querySingle("SELECT * FROM document WHERE $group_sql AND path = ?s", $comment);
        if ($row and (!$uploading_as_user or $row->lock_user_id == $uid)) {
            $dialogBox = 'comment';
            $curDirPath = my_dirname($comment);
            $backUrl = htmlspecialchars_decode(documentBackLink($curDirPath));
            $navigation[] = array('url' => $backUrl, 'name' => $pageName);
            foreach ($license as $license_selection) {
                $license_title[] = $license_selection['title'];
            }

            $dialogData = array(
                'backUrl' => $backUrl,
                'base_url' => $base_url,
                'file' => $row,
                'is_dir' => $row->format == '.dir',
                'selected_license_title' => $row->copyrighted,
                'license_title' => $license_title
            );
            view('modules.document.comment', $dialogData);
        } else {
            Session::Messages($langFileNotFound, 'alert-danger');
            $curDirPath = my_dirname($comment);
            redirect_to_current_dir();
        }
        exit;
    }

    // Display form to modify metadata
    if (isset($_GET['metadata'])) {

        $metadata = $_GET['metadata'];
        $row = Database::get()->querySingle("SELECT filename FROM document WHERE $group_sql AND path = ?s", $metadata);
        if ($row && (!$uploading_as_user or $row->lock_user_id == $uid)) {
            $curDirPath = my_dirname($metadata);
            $backUrl = documentBackLink($curDirPath);
            $navigation[] = array('url' => $backUrl, 'name' => $pageName);
            $oldFilename = q($row->filename);
            $real_filename = $basedir . str_replace('/..', '', q($metadata));
            $metaDataBox .= metaCreateForm($metadata, $oldFilename, $real_filename);
        } else {
            Session::Messages($langFileNotFound, 'alert-danger');
            $curDirPath = my_dirname($metadata);
            redirect_to_current_dir();
        }
    }

    // Don't allow these commands for users in courses with user upload
    if (!$uploading_as_user) {
        // Visibility commands
        if (isset($_GET['mkVisibl']) || isset($_GET['mkInvisibl'])) {
            if (isset($_GET['mkVisibl'])) {
                $newVisibilityStatus = 1;
                $visibilityPath = getDirectReference($_GET['mkVisibl']);
            } else {
                $newVisibilityStatus = 0;
                $visibilityPath = getDirectReference($_GET['mkInvisibl']);
            }
            $r = Database::get()->querySingle("SELECT id FROM document WHERE $group_sql AND path = ?s", $visibilityPath);
            if (($newVisibilityStatus == 0) and resource_belongs_to_progress_data(MODULE_ID_DOCS, $r->id)) {
                Session::Messages($langResourceBelongsToCert, 'alert-warning');
            } else {
                Database::get()->query("UPDATE document SET visible = ?d
                                                  WHERE $group_sql AND
                                                        path = ?s", $newVisibilityStatus, $visibilityPath);
                Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $r->id);
                Session::Messages($langViMod, 'alert-success');
            }
            $curDirPath = my_dirname($visibilityPath);
            redirect_to_current_dir();
        }

        // Public accessibility commands
        if (isset($_GET['public']) || isset($_GET['limited'])) {
            $new_public_status = intval(isset($_GET['public']));
            $path = isset($_GET['public']) ? $_GET['public'] : $_GET['limited'];
            Database::get()->query("UPDATE document SET public = ?d
                                              WHERE $group_sql AND
                                                    path = ?s", $new_public_status, $path);
            $r = Database::get()->querySingle("SELECT id FROM document WHERE $group_sql AND path = ?s", $path);
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $r->id);
            Session::Messages($langViMod, 'alert-success');
            $curDirPath = my_dirname($path);
            redirect_to_current_dir();
        }
    }
}

// Common for teachers and students

// Set current directory
if (!isset($curDirPath)) {
    $curDirPath = $_GET['openDir'] ?? '';
    if ($curDirPath == '/' or $curDirPath == '\\') {
        $curDirPath = '';
    }
}
$curDirName = my_basename($curDirPath);
$parentDir = my_dirname($curDirPath);
try {
    if (strpos($curDirPath, '/../') !== false or ! is_dir(realpath($basedir . $curDirPath))) {
        Session::Messages($langInvalidDir, 'alert-danger');
        view('layouts.default', $data);
        exit;
    }
} catch (Throwable $t) {
    not_found($curDirPath);
}

$order = 'ORDER BY sort_key COLLATE utf8mb4_unicode_ci';
$sort = 'name';
$reverse = false;
if (isset($_GET['sort'])) {
    if ($_GET['sort'] == 'type') {
        $order = 'ORDER BY format';
        $sort = 'type';
    } elseif ($_GET['sort'] == 'date') {
        $order = 'ORDER BY date_modified';
        $sort = 'date';
    }
}
if (isset($_GET['rev'])) {
    $order .= ' DESC';
    $reverse = true;
}

list($filter, $compatiblePlugin) = (isset($_REQUEST['docsfilter'])) ? select_proper_filters($_REQUEST['docsfilter']) : array('', true);

if (!$is_in_tinymce) {
    $document_timestamp = $session->getDocumentTimestamp($course_id);
} else {
    $document_timestamp = false;
}


// Check directory access if in subfolder
if ($curDirPath) {
    $dirInfo = Database::get()->querySingle("SELECT visible, public
        FROM document WHERE $group_sql AND path = ?s", $curDirPath);
    if (!$dirInfo) {
        Session::Messages($langInvalidDir, 'alert-danger');
        $curDirPath = '';
        redirect_to_current_dir();
    } elseif (!$can_upload and !resource_access($dirInfo->visible, $dirInfo->public)) {
        if (!$uid) {
            // If not logged in, try to log in first
            $next = str_replace($urlAppend, '/', $_SERVER['REQUEST_URI']);
            header("Location:" . $urlServer . "main/login_form.php?next=" . urlencode($next));
        } else {
            // Logged in but access forbidden
            Session::Messages($langInvalidDir, 'alert-danger');
            $curDirPath = '';
            redirect_to_current_dir();
        }
        exit;
    }
}

// Retrieve file info for current directory from database and disk
$result = Database::get()->queryArray("SELECT id, path, filename,
        format, title, extra_path, course_id, date_modified,
        public, visible, editable, copyrighted, comment, lock_user_id, prevent_download,
        IF((title = '' OR title IS NULL), filename, title) AS sort_key
    FROM document
    WHERE $group_sql AND
          path LIKE ?s AND
          path NOT LIKE ?s $filter $order",
    "$curDirPath/%", "$curDirPath/%/%");
$dirs = $files = [];
foreach ($result as $row) {
    $id = $row->id;
    $is_dir = $row->format == '.dir';
    if ($real_path = common_doc_path($row->extra_path, true)) {
        // common docs
        if (!$common_doc_visible and !$is_admin) {
            // hide links to invisible common docs to non-admins
            continue;
        }
        $path = $real_path;
    } else {
        $path = $basedir . $row->path;
    }
    if (!$real_path and $row->extra_path) {
        // external file
        $size = 0;
    } else {
        $size = file_exists($path)? filesize($path): 0;
    }
    if (!$document_timestamp) {
        $updated_message = '';
    } elseif ($row->date_modified > $document_timestamp) {
        $updated_message = $langNew;
    } elseif ($is_dir) {
        $updated = intval(Database::get()->querySingle("SELECT COUNT(*) AS c FROM document
            WHERE $group_sql AND
                  path LIKE ?s AND
                  date_modified > ?t" .
                  ($can_upload? '': ' AND visible=1'),
            $row->path . '/%', $document_timestamp)->c);
        if ($updated > 0) {
            $updated_message = sprintf($updated > 1? $langNewAddedPlural: $langNewAddedSingular, $updated);
        } else {
            $updated_message = '';
        }
    } else {
        $updated_message = '';
    }

    $info = array(
        'id' => $id,
        'is_dir' => $is_dir,
        'size' => $size,
        'title' => $row->title,
        'filename' => $row->filename,
        'format' => $row->format,
        'path' => $row->path,
        'extra_path' => $row->extra_path,
        'visible' => ($row->visible == 1),
        'public' => $row->public,
        'comment' => $row->comment,
        'copyrighted' => $row->copyrighted,
        'date' => $row->date_modified,
        'object' => MediaResourceFactory::initFromDocument($row),
        'editable' => $row->editable,
        'updated_message' => $updated_message,
        'controls' => $can_upload || ($user_upload && $row->lock_user_id == $uid),
        'prevent_download' => $row->prevent_download,
        'document_id' => $row->id);

    if ($row->extra_path) {
        $info['common_doc_path'] = common_doc_path($row->extra_path); // sets global $common_doc_visible
        $info['common_doc_visible'] = $common_doc_visible;
    }

    if (!$row->extra_path or $info['common_doc_path']) {
        // Normal or common document
        $download_url = $base_url . "download=" . getIndirectReference($row->path);
    } else {
        // External document
        $download_url = $row->extra_path;
    }

    $downloadMessage = $row->format == '.dir' ? $langDownloadDir : $langSave;
    $info['action_button'] = '';
    if (!$is_in_tinymce) {
        $cmdDirName = getIndirectReference($row->path);
        if ($can_upload) {
            $xmlCmdDirName = ($row->format == ".meta" && get_file_extension($row->path) == 'xml') ? substr($row->path, 0, -4) : $row->path;
            $info['action_button'] = action_button(array(
                array('title' => $langFileUnzipping,
                    'url' => "{$base_url}unzip=" . $row->path,
                    'icon' => 'fa-file-zipper',
                    'show' => $row->format == 'zip'),
                array('title' => $langEditChange,
                      'url' => "{$base_url}comment=" . $cmdDirName,
                      'icon' => 'fa-edit',
                      'show' => $row->format != '.meta'),
                array('title' => $langGroupSubmit,
                      'url' => "{$urlAppend}modules/work/group_work.php?course=$course_code&amp;group_id=$group_id&amp;submit=$cmdDirName",
                      'icon' => 'fa-book',
                      'show' => $subsystem == GROUP and isset($is_member) and $is_member),
                array('title' => $langMove,
                'url' => "{$base_url}move=$cmdDirName&file=$row->path",
                      'icon' => 'fa-arrows',
                      'show' => $row->format != '.meta'),
                array('title' => $langRename,
                      'url' => "{$base_url}rename=$cmdDirName",
                      'icon' => 'fa-pencil',
                      'show' => $row->format != '.meta'),
                array('title' => $langReplace,
                      'url' => "{$base_url}replace=$cmdDirName",
                      'icon' => 'fa-exchange',
                      'show' => !$is_dir && $row->format != '.meta'),
                array('title' => $langMetadata,
                      'url' =>  "{$base_url}metadata=$xmlCmdDirName",
                      'icon' => 'fa-tags',
                      'show' => get_config("insert_xml_metadata")),
                array('title' => $row->visible ? $langViewHide : $langViewShow,
                      'url' => $base_url . ($row->visible? 'mkInvisibl=' : 'mkVisibl=') . $cmdDirName,
                      'icon' => $row->visible ? 'fa-eye-slash' : 'fa-eye',
                      'show' => !$uploading_as_user),
                array('title' => $row->public ? $langResourceAccessLock : $langResourceAccessUnlock,
                      'url' => $base_url . ($row->public ? 'limited=' : 'public=') . $row->path,
                      'icon' => $row->public ? 'fa-lock' : 'fa-unlock',
                      'show' => !$uploading_as_user),
                array('title' => $row->prevent_download ? $langDisablePreventDownloadPdf : $langEnablePreventDownloadPdf,
                      'url' => $base_url . ($row->prevent_download ? 'prevent_pdf=0' : 'prevent_pdf=1') . "&amp;pdf=$cmdDirName",
                      'icon' => !$row->prevent_download ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark',
                      'show' => (get_config('enable_prevent_download_url') && $row->format == 'pdf')),
                array('title' => $langDownload,
                      'url' => $download_url,
                      'icon' => 'fa-download'),
                array('title' => $langAddResePortfolio,
                      'url' => "{$urlAppend}main/eportfolio/resources.php?token=".token_generate('eportfolio' . $uid)."&amp;action=add&amp;type=mydocs&amp;rid=".$row->id,
                      'icon' => 'fa-star',
                      'show' => !$is_dir && $subsystem == MYDOCS && $subsystem_id == $uid && get_config('eportfolio_enable')),
                array('title' => $langDelete,
                      'url' => "{$base_url}filePath=$cmdDirName&amp;delete=1&amp;" . generate_csrf_token_link_parameter() ,
                      'class' => 'delete',
                      'icon' => 'fa-xmark',
                      'confirm' => $langConfirmDelete . ' ' . q($row->filename))));
        } elseif ($uid or $row->format != '.dir') {
            if (get_config('enable_prevent_download_url') && $row->format == 'pdf' && $row->prevent_download == 1){
                $info['action_button'] = '';
            } else {
                $info['action_button'] = icon('fa-download', $downloadMessage, $download_url);
            }
        }
    }

    $info['copyrighted'] = false;
    if ($is_dir) {
        $info['icon'] = 'fa-folder';
        $info['url'] = $base_url . 'openDir=' . $row->path;
        $dirs[] = (object) $info;
    } else {
        $info['icon'] = choose_image('.' . $row->format);
        $info['url'] = file_url($row->path, $row->filename);
        $dObj = MediaResourceFactory::initFromDocument($row);
        $dObj->setAccessURL($info['url']);
        if ($is_in_tinymce && !$compatiblePlugin) {
            // use Access/DL URL for non-modable tinymce plugins
            $dObj->setPlayURL($dObj->getAccessURL());
        } else {
            $dObj->setPlayURL(file_playurl($row->path, $row->filename));
        }
        $info['link'] = MultimediaHelper::chooseMediaAhref($dObj);

        if ($row->editable) {
            $info['edit_url'] = "new.php?course=$course_code&editPath=" . $row->path .
                ($groupset? "&$groupset": '');
        }
        $copyid = $row->copyrighted;
        if ($copyid and $copyid != 2) {
            $info['copyrighted'] = true;
        }
        $files[] = (object) $info;
    }
}
// end of common to teachers and students
// ----------------------------------------------
// Display
// ----------------------------------------------

$data = compact('menuTypeID', 'can_upload', 'is_in_tinymce', 'base_url',
    'group_hidden_input', 'curDirName', 'curDirPath', 'dialogBox','metaDataBox');
$data['fileInfo'] = array_merge($dirs, $files);

if (isset($dialogData)) {
    $data = array_merge($data, $dialogData);
}

if ($curDirName) {
    $data['dirComment'] = Database::get()->querySingle("SELECT comment FROM document WHERE $group_sql AND path = ?s", $curDirPath)->comment;
    $data['parentLink'] = $base_url . 'openDir=' . rawurlencode($parentDir);
}


$data['diskQuotaDocument'] = $diskQuotaDocument = $diskQuotaDocument * 1024 / 1024;
$data['diskUsed'] = $diskUsed;

if (($can_upload or $user_upload) and !$is_in_tinymce) {
    // available actions
    if (isset($_GET['rename'])) {
        $pageName = $langRename;
    }
    if (isset($_GET['move'])) {
        $pageName = $langMove;
    }
    if (isset($_GET['createDir'])) {
        $pageName = $langCreateDir;
    }
    if (isset($_GET['comment'])) {
        $pageName = $langAddComment;
    }
    if (isset($_GET['replace'])) {
        $pageName = $langReplace;
    }

    $data['action_bar'] = action_bar(array(
        array('title' => $langBack,
              'url' => "group_space.php?course=$course_code&group_id=$group_id",
              'icon' => 'fa-reply',
              'level' => 'primary',
              'show' => $subsystem == GROUP),
        array('title' => $langDownloadFile,
              'url' => "upload.php?course=$course_code&amp;{$groupset}uploadPath=$curDirPath",
              'icon' => 'fa-upload',
              'level' => 'primary-label',
              'text-class' => 'uploadBTN',
              'button-class' => 'btn-success'),
        array('title' => $langCreateDir,
            'url' => "{$base_url}createDir=$curDirPath",
            'icon' => 'fa-folder',
            'level' => 'primary'),
        array('title' => $langBulkProcessing,
            'class' => 'bulk-processing',
            'icon' => 'fa-hat-wizard'),
        array('title' => $langUploadRecAudio,
            'url' => "rec_audio.php?course=$course_code",
            'icon' => 'fa-microphone',
            'show' => (get_config('allow_rec_audio') and $course_code)),
        array('title' => $langUploadRecVideo,
            'url' => "rec_video.php?course=$course_code",
            'icon' => 'fa-camera',
            'show' => (get_config('allow_rec_video') and $course_code) ),
        array('title' => $langCreateDoc,
              'url' => "new.php?course=$course_code&amp;{$groupset}uploadPath=$curDirPath",
              'icon' => 'fa-file'),
        array('title' => $langExternalFile,
              'url' => "upload.php?course=$course_code&amp;{$groupset}uploadPath=$curDirPath&amp;ext=true",
              'icon' => 'fa-link'),
        array('title' => $langMindmap,
              'url' => "../mindmap/index.php?course=$course_code",
              'icon' => 'fa-solid fa-sitemap'),
        array('title' => $langCommonDocs,
              'url' => "../units/insert.php?course=$course_code&amp;dir=$curDirPath&amp;type=doc&amp;id=-1",
              'icon' => 'fa-share-alt',
              'show' => !defined('MY_DOCUMENTS') && !defined('COMMON_DOCUMENTS') && get_config('enable_common_docs')),
        array('title' => $langQuotaBar,
              'url' => "{$base_url}showQuota=true",
              'icon' => 'fa-pie-chart')

        ), false);
} else {
    $data['action_bar'] = $data['dialogBox'] = '';
}

if (count($data['fileInfo'])) {
    $download_path = empty($curDirPath) ? '/' : $curDirPath;
    $data['downloadPath'] = (!$is_in_tinymce and $uid) ? ("{$base_url}download=" . getIndirectReference($download_path)) : '';
} else {
    $data['downloadPath'] = '';
}
$data['backUrl'] = htmlspecialchars_decode($backUrl ?? documentBackLink($curDirPath));

if (defined('SAVED_COURSE_CODE')) {
    $course_code = SAVED_COURSE_CODE;
    $course_id = SAVED_COURSE_ID;
}
add_units_navigation(true);

// Collect errors collected during multi-file upload
if (isset($_SESSION['upload_errors'])) {
    foreach ($_SESSION['upload_errors'] as $upload_error) {
        Session::Messages($upload_error, 'alert-danger');
        unset($_SESSION['upload_errors']);
    }
}

$data['course_id'] = $course_id;
$data['course_code'] = $course_code;
$data['is_editor'] = $is_editor;
$data['can_upload'] = $can_upload;
view('modules.document.index', $data);

function select_proper_filters($requestDocsFilter) {
    $filter = '';
    $compatiblePlugin = true;

    switch ($requestDocsFilter) {
        case 'image':
            $ors = '';
            foreach (MultimediaHelper::getSupportedImages() as $imgfmt)
                $ors .= " OR format LIKE '$imgfmt'";
            $filter = "AND (format LIKE '.dir' $ors)";
            break;
        case 'eclmedia':
            $ors = '';
            foreach (MultimediaHelper::getSupportedMedia() as $mediafmt)
                $ors .= " OR format LIKE '$mediafmt'";
            $filter = "AND (format LIKE '.dir' $ors)";
            break;
        case 'media':
            $compatiblePlugin = false;
            $ors = '';
            foreach (MultimediaHelper::getSupportedMedia() as $mediafmt)
                $ors .= " OR format LIKE '$mediafmt'";
            $filter = "AND (format LIKE '.dir' $ors)";
            break;
        case 'zip':
            $filter = "AND (format LIKE '.dir' OR FORMAT LIKE 'zip')";
            break;
        case 'file':
            $filter = '';
            break;
        default:
            break;
    }

    return array($filter, $compatiblePlugin);
}


/**
 * @brief Link for sortable table headings
 * @param type $this_sort
 * @return type
 */
function headlink($label, $this_sort) {
    global $sort, $reverse, $curDirPath, $base_url;

    if (empty($curDirPath)) {
        $path = '/';
    } else {
        $path = $curDirPath;
    }
    if ($sort == $this_sort) {
        $this_reverse = !$reverse;
        $icon = ($reverse) ? 'fa-solid fa-caret-down': 'fa-solid fa-caret-up';
    } else {
        $this_reverse = $reverse;
        $icon = '';
    }
    return "<a class='TextBold text-decoration-none text-nowrap' href='{$base_url}openDir=$path&amp;sort=$this_sort" . ($this_reverse ? '&amp;rev=1' : '') . "'>
                <i class='$icon'></i>  $label</a>";
}


/**
 * Used in documents path navigation bar
 * @param type $path
 * @return type
 */
function make_clickable_path($path) {
    global $langRoot, $base_url, $group_sql;

    $cur = $out = '';
    foreach (explode('/', $path) as $component) {
        if (empty($component)) {
            $out = "<a href='{$base_url}openDir=/'>$langRoot</a>";
        } else {
            $cur .= rawurlencode("/$component");
            $row = Database::get()->querySingle("SELECT filename FROM document
                                        WHERE path LIKE '%/$component' AND $group_sql");
            $dirname = $row->filename;
            $out .= " <span class='fa-solid fa-chevron-right px-2 small-text'></span> <a href='{$base_url}openDir=$cur'>".q($dirname)."</a>";
        }
    }
    return $out;
}


/**
 * Redirect user to current documents page, keeping subsystem and current directory
 * @global type $base_url
 * @global type $curDirPath
 */
function redirect_to_current_dir() {
    global $base_url, $curDirPath, $course_code, $ebook_id;

    if (defined('EBOOK_DOCUMENTS') and isset($_POST['back']) and $_POST['back'] == 'edit') {
        redirect_to_home_page('modules/ebook/edit.php?course=' .
            $course_code . '&id=' . $ebook_id);
    }
    $redirect_base_url = str_replace('&amp;', '&', $base_url);
    if (isset($curDirPath) and $curDirPath) {
        $redirect_base_url .= 'openDir=' . $curDirPath;
    } else {
        $redirect_base_url = preg_replace('/[&?]$/', '', $redirect_base_url);
    }
    redirect_to_home_page($redirect_base_url, true);
}

/**
 * Generate a new filename in path $editPath in current document subsystem
 * @param string $editPath Current path
 * @param string $prefix New file prefix
 * @param string $suffix New file suffix
 * @global string $group_sql Current subsystem SQL options
 */
function newPageFileName($uploadPath, $prefix, $suffix) {
    global $group_sql;

    $newId = Database::get()->querySingle(
        "SELECT COALESCE(MAX(CONVERT(REPLACE(REPLACE(filename, ?s, ''), ?s, ''), SIGNED INTEGER)), 0) + 1 AS newPageId
             FROM document WHERE $group_sql AND
                  path LIKE ?s AND path NOT LIKE ?s AND filename REGEXP ?s",
        $prefix, $suffix, $uploadPath . '/%', $uploadPath . '/%/%',
        preg_quote($prefix) . '[0-9]+' . preg_quote($suffix))->newPageId;
    return $prefix . $newId . $suffix;
}
