<?php

/* ========================================================================
 * Open eClass 3.6
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
 * ======================================================================== */

$is_in_tinymce = (isset($_REQUEST['embedtype']) && $_REQUEST['embedtype'] == 'tinymce') ? true : false;

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

// Used to check for quotas
$diskUsed = dir_total_space($basedir);

$user_upload = $uid && $subsystem == MAIN && get_config('enable_docs_public_write') && setting_get(SETTING_DOCUMENTS_PUBLIC_WRITE);
$uploading_as_user = !$can_upload && $user_upload;

if (defined('COMMON_DOCUMENTS')) {
    $menuTypeID = 3;
    $toolName = $langCommonDocs;
    $diskQuotaDocument = $diskUsed + ini_get('upload_max_filesize') * 1024 * 1024;
} elseif (defined('MY_DOCUMENTS')) {
    if ($session->status == USER_TEACHER and !get_config('mydocs_teacher_enable')) {
        redirect_to_home_page();
    }
    if ($session->status == USER_STUDENT and !get_config('mydocs_student_enable')) {
        redirect_to_home_page();
    }
    $menuTypeID = 1;
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
    load_js('jquery-' . JQUERY_VERSION . '.min');
    load_js('tinymce.popup.urlgrabber.min.js');
}

load_js('tools.js');
ModalBoxHelper::loadModalBox(true);
copyright_info_init();

if (defined('EBOOK_DOCUMENTS')) {
    $navigation[] = array('url' => 'edit.php?course=' . $course_code . '&amp;id=' . $ebook_id, 'name' => $langEBookEdit);
}

if (isset($_GET['showQuota'])) {
    $navigation[] = array('url' => documentBackLink(''), 'name' => $pageName);
    $tool_content .= showquota($diskQuotaDocument, $diskUsed);
    draw($tool_content, $menuTypeID);
    exit;
}

// ---------------------------
//mindmap save button
// ---------------------------
if (isset($_GET['mindmap'])) {
    $mindmap = $_GET['mindmap'];
    $title = $_GET['mindtitle'];

    //$uploadPath = " ";
    $filename = $title . ".jm";
    $safe_fileName = safe_filename(get_file_extension($filename));
    $file_path = '/' . $safe_fileName;
    $file_date = date('Y\-m\-d G\:i\:s');
    $file_format = get_file_extension($filename);

    $myfile = fopen($basedir . $file_path, 'w') or die('Unable to open file!');
    $txt = $mindmap;

    fwrite($myfile, $txt);
    fclose($myfile);

    move_uploaded_file($myfile , $basedir . $file_path);

    $file_creator = "$_SESSION[givenname] $_SESSION[surname]";
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
    Session::Messages($langMindMapSaved,"alert-success");
    redirect_to_home_page("modules/mindmap/index.php");
}


// ---------------------------
// mindmap screenshot save
// ---------------------------
if (isset($_POST['imgBase64'])) {
    $shootname = $_POST['imgname'];
    $img = $_POST['imgBase64'];
    $img = str_replace('data:image/png;base64,', '', $img);
    $img = str_replace(' ', '+', $img);
    $fileData = base64_decode($img);
    $filename = $shootname . '.png';
    $safe_fileName = safe_filename(get_file_extension($filename));
    $file_path = '/' . $safe_fileName;
    $file_date = date("Y\-m\-d G\:i\:s");
    $file_format = get_file_extension($filename);

    // mindmap save in database
    file_put_contents($basedir . $file_path, $fileData);

    $file_creator = "$_SESSION[givenname] $_SESSION[surname]";
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
            $file_date, $file_date, $file_creator, $file_format,
            $language, $uid);
    exit;
}


// ---------------------------
// download directory or file
// ---------------------------
if (isset($_GET['download'])) {
    $downloadDir = $_GET['download'];

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

    $action_message = $dialogBox = '';
    $extra_path = '';
    if (isset($_POST['fileCloudInfo']) or isset($_FILES['userFile'])) {
        if (isset($_POST['fileCloudInfo'])) { // upload cloud file
            $cloudfile = CloudFile::fromJSON($_POST['fileCloudInfo']);
            $uploaded = true;
            $fileName = $cloudfile->name();
        } else if (isset($_FILES['userFile']) and is_uploaded_file($_FILES['userFile']['tmp_name'])) { // upload local file
            $fileName = $_FILES['userFile']['name'];
            $userFile = $_FILES['userFile']['tmp_name'];
        }
        validateUploadedFile($fileName, $menuTypeID); // check file type
        // check for disk quotas
        if ($diskUsed + @$_FILES['userFile']['size'] > $diskQuotaDocument) {
            Session::Messages($langNoSpace, 'alert-danger');
            redirect_to_current_dir();
        } elseif (isset($_POST['uncompress']) and $_POST['uncompress'] == 1 and preg_match('/\.zip$/i', $fileName)) {
            /* ** Unzipping stage ** */
            $zipFile = new PclZip($userFile);
            validateUploadedZipFile($zipFile->listContent(), $menuTypeID);
            $realFileSize = 0;
            $zipFile->extract(PCLZIP_CB_PRE_EXTRACT, 'process_extracted_file');
            if ($diskUsed + $realFileSize > $diskQuotaDocument) {
                Session::Messages($langNoSpace, 'alert-danger');
            } else {
                $session->setDocumentTimestamp($course_id);
                Session::Messages($langDownloadAndZipEnd, 'alert-success');
            }
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
            if (isset($_POST['replace']) and (!$uploading_as_user or $result->lock_user_id == $uid)) {
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
        Session::Messages($error, 'alert-danger');
        redirect_to_current_dir();
    } elseif ($uploaded) {
        // No errors, so proceed with upload
        // File date is current date
        $file_date = date("Y\-m\-d G\:i\:s");
        // Try to add an extension to files witout extension,
        // change extension of PHP files
        $fileName = php2phps(add_ext_on_mime($fileName));
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
        if($connector->isEnabled() == true ){
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
                                        category = ?d,
                                        title = ?s,
                                        creator = ?s,
                                        date = ?t,
                                        date_modified = ?t,
                                        subject = ?s,
                                        description = ?s,
                                        author = ?s,
                                        format = ?s,
                                        language = ?s,
                                        copyrighted = ?d,
                                        lock_user_id = ?d"
                            , $course_id, $subsystem, $subsystem_id, $file_path, $extra_path, $fileName, $vis
                            , $_POST['file_comment'], $_POST['file_category'], $_POST['file_title'], $_POST['file_creator']
                            , $file_date, $file_date, $_POST['file_subject'], $_POST['file_description'], $_POST['file_author']
                            , $file_format, $_POST['file_language'], $_POST['file_copyrighted'], $uid)->lastInsertID;
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $id);
            // Logging
            Log::record($course_id, MODULE_ID_DOCS, LOG_INSERT, array('id' => $id,
                'filepath' => $file_path,
                'filename' => $fileName,
                'comment' => $_POST['file_comment'],
                'title' => $_POST['file_title']));
            Session::Messages($langDownloadEnd, 'alert-success');
            $session->setDocumentTimestamp($course_id);
            redirect_to_current_dir();
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
                            $subsectionOrder = Database::get()->querySingle("SELECT COALESCE(MAX(public_id), 0) + 1
                                FROM ebook_subsection WHERE section_id = ?d", $_POST['section_id']);
                            Database::get()->query("INSERT INTO ebook_subsection
                                SET section_id = ?s, file_id = ?d, title = ?s, public_id = ?s",
                                $_POST['section_id'], $id, $ebookSectionTitle, $subsectionOrder);
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
        $moveTo = $_POST['moveTo'];
        $source = $_POST['source'];
        $sourceXml = $source . '.xml';
        // check if source and destination are the same
        if ($basedir . $source != $basedir . $moveTo or $basedir . $source != $basedir . $moveTo) {
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
            Session::Messages($langDirMv, 'alert-success');
            redirect_to_current_dir();
        } else {
            $action_message = "<div class='alert alert-danger'>$langImpossible</div><br>";
            // return to step 1
            $move = $source;
            unset($moveTo);
        }
    }

    // Move file or directory: Step 1
    if (isset($_GET['move'])) {
        $move = $_GET['move'];
        $curDirPath = my_dirname($move);
        $navigation[] = array('url' => documentBackLink($curDirPath), 'name' => $pageName);

        // $move contains file path - search for filename in db
        $q = Database::get()->querySingle("SELECT filename, format FROM document
                                                WHERE $group_sql AND path=?s", $move);
        $moveFileNameAlias = $q->filename;
        $exclude = ($q->format == '.dir')? $move: '';
        $dialogBox .= directory_selection($move, 'moveTo', $curDirPath, $exclude);
    }

    // Delete file or directory
    if (isset($_GET['delete']) and isset($_GET['filePath'])) {
        $filePath = $_GET['filePath'];
        $curDirPath = my_dirname($_GET['filePath']);
        // Check if file actually exists
        $r = Database::get()->querySingle("SELECT id, path, extra_path, format, filename, lock_user_id FROM document
                                        WHERE $group_sql AND path = ?s", $filePath);
        $delete_ok = true;
        if ($r and (!$uploading_as_user or $r->lock_user_id == $uid)) {
            if (resource_belongs_to_progress_data(MODULE_ID_DOCS, $r->id)) {
                Session::Messages($langResourceBelongsToCert, "alert-warning");
            } else {
                // remove from index if relevant (except non-main sysbsystems and metadata)
                Database::get()->queryFunc("SELECT id FROM document WHERE course_id >= 1 AND subsystem = 0
                                                AND format <> '.meta' AND path LIKE ?s",
                    function ($r2) {
                        Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_DOCUMENT, $r2->id);
                        if (resource_belongs_to_progress_data(MODULE_ID_DOCS, $r2->id)) {                            
                            Session::Messages($langResourceBelongsToCert, "alert-warning");
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
                redirect_to_current_dir();
            }
        }
    }

    /*****************************************
      RENAME
     ******************************************/
    // Step 2: Rename file by updating record in database
    if (isset($_POST['renameTo'])) {

        $r = Database::get()->querySingle("SELECT id, filename, format, lock_user_id FROM document WHERE $group_sql AND path = ?s", $_POST['sourceFile']);

        if ($r) {
            // Check if target filename already exists
            $curDirPath = my_dirname($_POST['sourceFile']);
            $fileExists = Database::get()->querySingle("SELECT id FROM document
                    WHERE $group_sql AND path REGEXP ?s AND filename = ?s LIMIT 1",
                    "^$curDirPath/[^/]+$", $_POST['renameTo']);
            if ($fileExists) {
                Session::Messages($langFileExists, 'alert-danger');
                redirect_to_current_dir();
            }
            if ($r->format != '.dir') {
                validateRenamedFile($_POST['renameTo'], $menuTypeID);
            }
            Database::get()->query("UPDATE document SET filename = ?s, date_modified = NOW()
                              WHERE $group_sql AND path=?s",
                        $_POST['renameTo'], $_POST['sourceFile']);
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $r->id);
            Log::record($course_id, MODULE_ID_DOCS, LOG_MODIFY, array('path' => $_POST['sourceFile'],
                'filename' => $r->filename,
                'newfilename' => $_POST['renameTo']));
            if (hasMetaData($_POST['sourceFile'], $basedir, $group_sql)) {
                if (Database::get()->query("UPDATE document SET filename=?s WHERE $group_sql AND path = ?s",
                    $_POST['renameTo'] . '.xml', $_POST['sourceFile'] . '.xml')->affectedRows > 0) {
                    metaRenameDomDocument($basedir . $_POST['sourceFile'] . '.xml', $_POST['renameTo']);
                }
            }
            Session::Messages($langElRen, 'alert-success');
            redirect_to_current_dir();
        }
    }

    // Step 1: Show rename dialog box
    if (isset($_GET['rename'])) {

        $r = Database::get()->querySingle("SELECT id, filename, format, lock_user_id
            FROM document WHERE $group_sql AND path = ?s", $_GET['rename']);

        if ($r and (!$uploading_as_user or $r->lock_user_id == $uid)) {
            $fileName = Database::get()->querySingle("SELECT filename FROM document
                                                 WHERE $group_sql AND
                                                       path = ?s", $_GET['rename'])->filename;
            $curDirPath = my_dirname($_GET['rename']);
            $backUrl = documentBackLink($curDirPath);
            $navigation[] = array('url' => $backUrl, 'name' => $pageName);
            $dialogBox .= "
                <div class='row'>
                    <div class='col-xs-12'>
                        <div class='form-wrapper'>
                            <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
                                <fieldset>
                                        <input type='hidden' name='sourceFile' value='" . q($_GET['rename']) . "' />
                                        $group_hidden_input
                                        <div class='form-group'>
                                            <label for='renameTo' class='col-xs-2 control-label' >" . ($r->format != '.dir'? $m['filename'] : $m['dirname'] ). ":</label>
                                            <div class='col-xs-10'>
                                                <input class='form-control' type='text' name='renameTo' value='" . q($fileName) . "' />
                                            </div>
                                        </div>
                                        <div class='form-group'>
                                            <div class='col-xs-offset-2 col-xs-10'>" .
                                                form_buttons(array(
                                                    array('text' => $langRename, 'value'=> $langRename),
                                                    array('href' => $backUrl))) . "
                                            </div>
                                        </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>";
        }
    }

    // create directory
    // step 2: create the new directory
    if (isset($_POST['newDirPath'])) {
        $newDirName = canonicalize_whitespace($_POST['newDirName']);
        if (!empty($newDirName)) {
            $curDirPath = $_POST['newDirPath'];
            $newDirPath = make_path($_POST['newDirPath'], array($newDirName));
            // $path_already_exists: global variable set by make_path()
            if ($path_already_exists) {
                Session::Messages($langFileExists, 'alert-danger');
            } else {
                $session->setDocumentTimestamp($course_id);
                $r = Database::get()->querySingle("SELECT id FROM document WHERE $group_sql AND path = ?s", $newDirPath);
                Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $r->id);
                Session::Messages($langDirCr, 'alert-success');
            }
            redirect_to_current_dir();
        }
    }

    // step 1: display a field to enter the new dir name
    if (isset($_GET['createDir'])) {
        $curDirPath = $createDir = q($_GET['createDir']);
        $backLink = documentBackLink($curDirPath);
        $navigation[] = array('url' => $backLink, 'name' => $pageName);
        $dialogBox .= "
        <div class='row'>
            <div class='col-md-12'>
                <div class='form-wrapper'>
                    <form action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post' class='form-horizontal' role='form'>
                        $group_hidden_input
                        <input type='hidden' name='newDirPath' value='$createDir' />
                        <div class='form-group'>
                            <label for='newDirName' class='col-sm-2 control-label'>$langNameDir :</label>
                            <div class='col-xs-10'>
                                <input type='text' class='form-control' id='newDirName' name='newDirName'>
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-xs-offset-2 col-xs-10'>".form_buttons(array(
                                    array(
                                        'text' => $langCreate
                                    ),
                                    array(
                                        'href' => $backLink,
                                    )
                                ))."</div>
                        </div>
                    </form>

                </div>
            </div>
        </div>";
    }

    // add/update/remove comment
    if (isset($_POST['commentPath'])) {
        $commentPath = $_POST['commentPath'];
        // check if file exists
        $res = Database::get()->querySingle("SELECT * FROM document
                                             WHERE $group_sql AND
                                                   path=?s", $commentPath);
        if ($res and (!$uploading_as_user or $res->lock_user_id == $uid)) {
            $file_language = $session->validate_language_code($_POST['file_language'], $language);
            Database::get()->query("UPDATE document SET
                                                comment = ?s,
                                                category = ?d,
                                                title = ?s,
                                                date_modified = NOW(),
                                                subject = ?s,
                                                description = ?s,
                                                author = ?s,
                                                language = ?s,
                                                copyrighted = ?d
                                        WHERE $group_sql AND
                                              path = ?s"
                    , $_POST['file_comment'], $_POST['file_category'], $_POST['file_title'], $_POST['file_subject']
                    , $_POST['file_description'], $_POST['file_author'], $file_language, $_POST['file_copyrighted'], $commentPath);
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $res->id);
            Log::record($course_id, MODULE_ID_DOCS, LOG_MODIFY, array('path' => $commentPath,
                'filename' => $res->filename,
                'comment' => $_POST['file_comment'],
                'title' => $_POST['file_title']));
            $curDirPath = my_dirname($commentPath);
            Session::Messages($langComMod, 'alert-success');
            redirect_to_current_dir();
        }
    }

    // add/update/remove metadata
    // h $metadataPath periexei to path tou arxeiou gia to opoio tha epikyrwthoun ta metadata
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
                                language = ?s
                                WHERE $group_sql AND path = ?s",
                "$_SESSION[givenname] $_SESSION[surname]", $file_format, $_POST['meta_language'], $metadataPath);
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
                $file_format, $_POST['meta_language'], $uid);
        }

        Session::Messages($langMetadataMod, 'alert-success');
        redirect_to_current_dir();
    }

    if (isset($_POST['replacePath']) and
            isset($_FILES['newFile']) and
            is_uploaded_file($_FILES['newFile']['tmp_name'])) {
        validateUploadedFile($_FILES['newFile']['name'], $menuTypeID);
        $replacePath = $_POST['replacePath'];
        // Check if file actually exists
        $result = Database::get()->querySingle("SELECT id, path, format, lock_user_id FROM document WHERE
                                        $group_sql AND
                                        format <> '.dir' AND
                                        path=?s", $replacePath);
        if ($result and (!uploading_as_user or $result->lock_user_id == $uid)) {
            $docId = $result->id;
            $oldpath = $result->path;
            $oldformat = $result->format;
            $curDirPath = my_dirname($_POST['replacePath']);
            // check for disk quota
            if ($diskUsed - filesize($basedir . $oldpath) + $_FILES['newFile']['size'] > $diskQuotaDocument) {
                Session::Messages($langNoSpace, 'alert-danger');
                redirect_to_current_dir();
            } elseif (unwanted_file($_FILES['newFile']['name'])) {
                Session::Messages($langUnwantedFiletype . ": " . q($_FILES['newFile']['name']), 'alert-danger');
                redirect_to_current_dir();
            } else {
                $newformat = get_file_extension($_FILES['newFile']['name']);
                $newpath = preg_replace("/\\.$oldformat$/", '', $oldpath) .
                        (empty($newformat) ? '' : '.' . $newformat);
                $newpath = php2phps($newpath);
                my_delete($basedir . $oldpath);
                $affectedRows = Database::get()->query("UPDATE document SET path = ?s, format = ?s, filename = ?s, date_modified = NOW()
                          WHERE $group_sql AND path = ?s"
                                , $newpath, $newformat, ($_FILES['newFile']['name']), $oldpath)->affectedRows;
                if (!copy($_FILES['newFile']['tmp_name'], $basedir . $newpath) or $affectedRows == 0) {
                    Session::Messages($langGeneralError, 'alert-danger');
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
                                                path = ?s", $_GET['replace']);
        if ($result and (!$uploading_as_user or $result->lock_user_id == $uid)) {
            $curDirPath = my_dirname($result->path);
            $navigation[] = array('url' => documentBackLink($curDirPath), 'name' => $pageName);
            $filename = q($result->filename);
            $replacemessage = sprintf($langReplaceFile, '<b>' . $filename . '</b>');
            enableCheckFileSize();
            $dialogBox = "<div class='form-wrapper'>
                        <form class='form-horizontal' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' enctype='multipart/form-data'>" .
                        fileSizeHidenInput() . "
                        <fieldset>
                        <input type='hidden' name='replacePath' value='" . q($_GET['replace']) . "' />
                        $group_hidden_input
                        <div class='form-group'>
                            <label class='col-sm-5 control-label'>$replacemessage</label>
                            <div class='col-sm-7'><input type='file' name='newFile' size='35' /></div>
                        </div>
                        <div class='form-group'>
                            <div class='col-sm-offset-4 col-sm-8'>".form_buttons(array(
                                    array(
                                        'text' => $langReplace,
                                        'value'=> $langReplace
                                    ),
                                    array(
                                        'href' => "index.php?course=$course_code",
                                    )
                                ))."</div>
                        </div>
                        </fieldset>
                        </form></div>";
        }
    }

    // Add comment form
    if (isset($_GET['comment'])) {

        $comment = $_GET['comment'];
        // Retrieve the old comment and metadata
        $row = Database::get()->querySingle("SELECT * FROM document WHERE $group_sql AND path = ?s", $comment);
        if ($row and (!$uploading_as_user or $row->lock_user_id == $uid)) {
            $curDirPath = my_dirname($comment);
            $backUrl = documentBackLink($curDirPath);
            $navigation[] = array('url' => $backUrl, 'name' => $pageName);
            $oldFilename = q($row->filename);
            $oldComment = q($row->comment);
            $oldCategory = $row->category;
            $oldTitle = q($row->title);
            $oldCreator = q($row->creator);
            $oldDate = q($row->date);
            $oldSubject = q($row->subject);
            $oldDescription = q($row->description);
            $oldAuthor = q($row->author);
            $oldLanguage = q($row->language);
            $oldCopyrighted = $row->copyrighted;
            $oldFormat = $row->format;

            $is_file = $row->format != '.dir'? 1 : 0 ;

            $dialogBox .= "<div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
                <fieldset>
                  <input type='hidden' name='commentPath' value='" . q($comment) . "' />
                  <input type='hidden' size='80' name='file_filename' value='$oldFilename' />
                  $group_hidden_input
                  <div class='form-group'>
                    <label class='col-sm-2 control-label'>".($is_file? $langWorkFile : $langDirectory).":</label>
                    <div class='col-sm-10'>
                        <p class='form-control-static'>$oldFilename</p>
                    </div>
                  </div>";
            if ($is_file) { // if we are editing files file info
                  $dialogBox .= "<div class='form-group'>
                    <label class='col-sm-2 control-label'>$langTitle:</label>
                    <div class='col-sm-10'><input class='form-control' type='text' name='file_title' value='$oldTitle'></div>
                  </div>";
            }
                $dialogBox .= "<div class='form-group'>
                  <label class='col-sm-2 control-label'>$langComment:</label>
                  <div class='col-sm-10'><input class='form-control' type='text' name='file_comment' value='$oldComment'></div>
                </div>";
            if ($is_file) { // if we are editing files file info
                $dialogBox .= "<div class='form-group'>
                    <label class='col-sm-2 control-label'>$langCategory:</label>
                    <div class='col-sm-10'>" .
                        selection(array('0' => $langCategoryOther,
                            '1' => $langCategoryExcercise,
                            '2' => $langCategoryLecture,
                            '3' => $langCategoryEssay,
                            '4' => $langCategoryDescription,
                            '5' => $langCategoryExample,
                            '6' => $langCategoryTheory), 'file_category', $oldCategory, "class='form-control'") . "</div>
                  </div>
                  <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langSubject:</label>
                    <div class='col-sm-10'><input class='form-control' type='text' name='file_subject' value='$oldSubject'></div>
                  </div>
                  <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langDescription:</label>
                    <div class='col-sm-10'><input class='form-control' type='text' name='file_description' value='$oldDescription'></div>
                  </div>
                  <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langAuthor:</label>
                    <div class='col-sm-10'><input class='form-control' type='text' name='file_author' value='$oldAuthor'></div>
                  </div>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langCopyrighted:</label>
                    <div class='col-sm-10'>"
                         .selection($copyright_titles, 'file_copyrighted', $oldCopyrighted, "class='form-control'") .
                    "</div>
                </div>
                <div class='form-group'>
                        <label class='col-sm-2 control-label'>$langLanguage:</label>
                        <div class='col-sm-10'>" .
                            selection(array('en' => $langEnglish,
                                'fr' => $langFrench,
                                'de' => $langGerman,
                                'el' => $langGreek,
                                'it' => $langItalian,
                                'es' => $langSpanish), 'file_language', $oldLanguage, "class='form-control'") .
                        "</div>
                </div>";
            } else {
                $dialogBox .= "<input type='hidden' size='80' name='file_title' value='$oldTitle'>
                               <input type='hidden' size='80' name='file_category' value='$oldCategory'>
                               <input type='hidden' size='80' name='file_subject' value='$oldSubject'>
                               <input type='hidden' size='80' name='file_description' value='$oldDescription'>
                               <input type='hidden' size='80' name='file_author' value='$oldAuthor'>
                               <input type='hidden' size='80' name='file_copyrighted' value='$oldCopyrighted'>
                               <input type='hidden' size='80' name='file_language' value='$oldLanguage'>";
            }
            $dialogBox .= "<div class='form-group'>
                    <div class='col-sm-offset-2 col-sm-10'>".form_buttons(array(
                            array(
                                'text' => $langSave,
                                'value'=> $langOkComment
                            ),
                            array(
                                'href' => $backUrl,
                            )
                        ))."</div>
                </div>";
            if ($is_file) { // if we are editing files file info
                $dialogBox .= "<div class='form-group'>
                    <div class='col-sm-offset-2 col-sm-10'>
                        <span class='help-block'>$langNotRequired</span>
                    </div>
                </div>";
            }
                $dialogBox .= "<input type='hidden' size='80' name='file_creator' value='$oldCreator'>
                <input type='hidden' size='80' name='file_date' value='$oldDate'>
                </fieldset>
                </form></div>";
        } else {
            $action_message = "<div class='alert alert-danger'>$langFileNotFound</div>";
        }
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
            $dialogBox .= metaCreateForm($metadata, $oldFilename, $real_filename);
        } else {
            $action_message = "<div class='alert alert-danger'>$langFileNotFound</div>";
        }
    }

    // Don't allow these commands for users in courses with user upload
    if (!$uploading_as_user) {
        // Visibility commands
        if (isset($_GET['mkVisibl']) || isset($_GET['mkInvisibl'])) {
            if (isset($_GET['mkVisibl'])) {
                $newVisibilityStatus = 1;
                $visibilityPath = $_GET['mkVisibl'];
            } else {
                $newVisibilityStatus = 0;
                $visibilityPath = $_GET['mkInvisibl'];
            }
            Database::get()->query("UPDATE document SET visible = ?d
                                              WHERE $group_sql AND
                                                    path = ?s", $newVisibilityStatus, $visibilityPath);
            $r = Database::get()->querySingle("SELECT id FROM document WHERE $group_sql AND path = ?s", $visibilityPath);
            if (($newVisibilityStatus == 0) and resource_belongs_to_progress_data(MODULE_ID_DOCS, $r->id)) {
                Session::Messages($langResourceBelongsToCert, "alert-warning");
            } else {
                Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $r->id);
                Session::Messages($langViMod, 'alert-success');
                $curDirPath = my_dirname($visibilityPath);
                redirect_to_current_dir();
            }
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
    $curDirPath = isset($_GET['openDir'])? $_GET['openDir']: '';
    if ($curDirPath == '/' or $curDirPath == '\\') {
        $curDirPath = '';
    }
}
$curDirName = my_basename($curDirPath);
$parentDir = my_dirname($curDirPath);
if (strpos($curDirName, '/../') !== false or ! is_dir(realpath($basedir . $curDirPath))) {
    Session::Messages($langInvalidDir, 'alert-danger');
    draw($tool_content, $menuTypeID);
    exit;
}

$order = 'ORDER BY sort_key COLLATE utf8_unicode_ci';
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
        Session::Messages($langInvalidDir);
        draw($tool_content, $menuTypeID);
        exit;
    } elseif (!$can_upload and !resource_access($dirInfo->visible, $dirInfo->public)) {
        if (!$uid) {
            // If not logged in, try to log in first
            $next = str_replace($urlAppend, '/', $_SERVER['REQUEST_URI']);
            header("Location:" . $urlServer . "main/login_form.php?next=" . urlencode($next));
        } else {
            // Logged in but access forbidden
            Session::Messages($langInvalidDir);
            draw($tool_content, $menuTypeID);
        }
        exit;
    }
}

/* * * Retrieve file info for current directory from database and disk ** */
$result = Database::get()->queryArray("SELECT id, path, filename,
        format, title, extra_path, course_id, date_modified,
        public, visible, editable, copyrighted, comment, lock_user_id,
        IF((title = '' OR title IS NULL), filename, title) AS sort_key
    FROM document
    WHERE $group_sql AND
          path LIKE ?s AND
          path NOT LIKE ?s $filter $order",
    "$curDirPath/%", "$curDirPath/%/%");
$fileinfo = array();
foreach ($result as $row) {
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
        $updated = false;
    } elseif ($row->date_modified > $document_timestamp) {
        $updated = true;
    } elseif ($is_dir) {
        $updated = Database::get()->querySingle("SELECT COUNT(*) AS c FROM document
            WHERE $group_sql AND
                  path LIKE ?s AND
                  date_modified > ?t" .
                  ($can_upload? '': ' AND visible=1'),
            $row->path . '/%', $document_timestamp)->c;
        $updated = intval($updated);
    } else {
        $updated = false;
    }
    $fileinfo[] = array(
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
        'controls' => $can_upload || ($user_upload && $row->lock_user_id == $uid),
        'updated' => $updated);
}
// end of common to teachers and students
// ----------------------------------------------
// Display
// ----------------------------------------------

$cmdCurDirPath = rawurlencode($curDirPath);
$cmdParentDir = rawurlencode($parentDir);

if ($can_upload or $user_upload) {
    // Action result message
    if (!empty($action_message)) {
        $tool_content .= $action_message;
    }
    // available actions
    if (!$is_in_tinymce) {
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
        $diskQuotaDocument = $diskQuotaDocument * 1024 / 1024;
        $tool_content .= action_bar(array(
            array('title' => $langDownloadFile,
                  'url' => "upload.php?course=$course_code&amp;{$groupset}uploadPath=$curDirPath",
                  'icon' => 'fa-upload',
                  'level' => 'primary-label',
                  'button-class' => 'btn-success'),
            array('title' => $langCreateDoc,
                  'url' => "new.php?course=$course_code&amp;{$groupset}uploadPath=$curDirPath",
                  'icon' => 'fa-file',
                  'level' => 'primary'),
            array('title' => $langCreateDir,
                  'url' => "{$base_url}createDir=$cmdCurDirPath",
                  'icon' => 'fa-folder',
                  'level' => 'primary'),
            array('title' => $langExternalFile,
                  'url' => "upload.php?course=$course_code&amp;{$groupset}uploadPath=$curDirPath&amp;ext=true",
                  'icon' => 'fa-link'),
            array('title' => $langCommonDocs,
                  'url' => "../units/insert.php?course=$course_code&amp;dir=$curDirPath&amp;type=doc&amp;id=-1",
                  'icon' => 'fa-share-alt',
                  'show' => !defined('MY_DOCUMENTS') && !defined('COMMON_DOCUMENTS') && get_config('enable_common_docs')),
            array('title' => $langQuotaBar,
                  'url' => "{$base_url}showQuota=true",
                  'icon' => 'fa-pie-chart'),
            array('title' => $langBack,
                  'url' => "group_space.php?course=$course_code&group_id=$group_id",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label',
                  'show' => $subsystem == GROUP)
            ),false);

    }
    // Dialog Box
    if (!empty($dialogBox)) {
        $tool_content .= $dialogBox;
        if(isset($comment) && $is_file){
            draw($tool_content, $menuTypeID);
            exit;
        }
    }
}

// check if there are documents
$doc_count = Database::get()->querySingle("SELECT COUNT(*) as count FROM document WHERE $group_sql $filter" .
                ($can_upload ? '' : " AND visible=1"))->count;
if ($doc_count == 0) {
    $tool_content .= "<div class='alert alert-warning'>$langNoDocuments</div>";
} else {
    // Current Directory Line
    $tool_content .= "
    <div class='row'>
        <div class='col-md-12'>
            <div class='panel'>
                <div class='panel-body'>";
    if ($can_upload) {
        $cols = 4;
    } else {
        $cols = 3;
    }

    // If inside a subdirectory ($curDirName is not empty)
    if ($curDirName) {
        // Display parent directory link
        $parentlink = $base_url . 'openDir=' . $cmdParentDir;
        $tool_content.=" <div class='pull-right'>
                            <a href='$parentlink' type='button' class='btn btn-success'><i class='fa fa-level-up'></i> $langUp</a>
                        </div>";
        // Get current directory comment
        $dirComment = Database::get()->querySingle("SELECT comment FROM document WHERE $group_sql AND path = ?s", $curDirPath)->comment;
    } else {
        // In root directory - don't display parent directory link or comments
        $dirComment = '';
    }
    $download_path = empty($curDirPath) ? '/' : $curDirPath;
    $download_dir = (!$is_in_tinymce and $uid) ? icon('fa-download', $langDownloadDir, "{$base_url}download=$download_path") : '';
    $tool_content .= "<div>".make_clickable_path($curDirPath) .
            "&nbsp;&nbsp;$download_dir</div>";
    if ($dirComment) {
        $tool_content .= '<div>' . q($dirComment) . '</div>';
    }

    $tool_content .= "</div>
            </div>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-12'>
                <div class='table-responsive'>
                <table class='table-default'>
                    <tr class='list-header'>";
    $tool_content .= "<th class='text-left' width='60'>" . headlink($langType, 'type') . '</th>' .
                     "<th class='text-left'>" . headlink($langName, 'name') . '</th>' .
                     "<th class='text-left'>$langSize</th>" .
                     "<th class='text-left'>" . headlink($langDate, 'date') . '</th>';
    if (!$is_in_tinymce) {
        $tool_content .= "<th class='text-center'>".icon('fa-gears', $langCommands)."</th>";
    }
    $tool_content .= "</tr>";

    if (!count($fileinfo)) {
        $tool_content .= "<tr><td colspan=10><p class='not_visible text-center'> - " . q($langNoDocuments) . " - </td></tr>";
    } else {

    // -------------------------------------
    // Display directories first, then files
    // -------------------------------------
    foreach (array(true, false) as $is_dir) {
        foreach ($fileinfo as $entry) {
            $link_title_extra = '';
            if (($entry['is_dir'] != $is_dir) or ( !$can_upload and ( !resource_access($entry['visible'], $entry['public'])))) {
                continue;
            }
            $cmdDirName = $entry['path'];
            if (!$entry['visible']) {
                $style = ' class="not_visible"';
            } else {
                $style = ' class="visible"';
            }
            if ($is_dir) {
                $img_href = icon('fa-folder');
                $file_url = $base_url . "openDir=$cmdDirName";
                $link_title = q($entry['filename']);
                $dload_msg = $langDownloadDir;
                $link_href = "<a href='$file_url'>$link_title</a>";
            } else {
                $img_href = icon(choose_image('.' . $entry['format']));
                $file_url = file_url($cmdDirName, $entry['filename']);
                if ($entry['extra_path']) {
                    $cdpath = common_doc_path($entry['extra_path']);
                    if ($cdpath) {
                        if ($can_upload) {
                            if ($common_doc_visible) {
                                $link_title_extra .= '&nbsp;' .
                                    icon('common', $langCommonDocLink);
                            } else {
                                $link_title_extra .= '&nbsp;' .
                                    icon('common-invisible', $langCommonDocLinkInvisible);
                                $style = ' class="invisible"';
                            }
                        }
                    } else {
                        // External file URL
                        $file_url = $entry['extra_path'];
                        if ($can_upload) {
                            $link_title_extra .= '&nbsp;' . icon('fa-external-link', $langExternalFile);
                        }
                    }
                }


                if ($entry['editable'] and $entry['controls']) {
                    $edit_url = "new.php?course=$course_code&amp;editPath=$entry[path]" .
                        ($groupset? "&amp;$groupset": '');
                    $link_title_extra .= '&nbsp;' .
                        icon('fa-edit', $langEdit, $edit_url);
                }
                if (($copyid = $entry['copyrighted']) && $entry['copyrighted'] != 2) {
                    $copyicon = ($copyid == 1) ? 'fa-copyright' : 'fa-cc';
                    $link_title_extra .= "&nbsp;" .
                        icon($copyicon, $copyright_titles[$copyid], $copyright_links[$copyid], ' target="_blank" style="color:#555555;"');
                }
                $dload_msg = $langSave;

                $dObj = $entry['object'];
                $dObj->setAccessURL($file_url);
                $dObj->setPlayURL(file_playurl($cmdDirName, $entry['filename']));
                if ($is_in_tinymce && !$compatiblePlugin) // use Access/DL URL for non-modable tinymce plugins
                    $dObj->setPlayURL($dObj->getAccessURL());

                $link_href = MultimediaHelper::chooseMediaAhref($dObj);
            }
            if ($entry['updated']) {
                $link_title_extra .= "<span class='label label-success pull-right'>";
                if (is_integer($entry['updated'])) {
                    $link_title_extra .= sprintf(
                        ($entry['updated'] > 1? $langNewAddedPlural: $langNewAddedSingular),
                        $entry['updated']);
                } else {
                    $link_title_extra .= $langNew;
                }
                $link_title_extra .= "</span>";
            }
            if (!$entry['extra_path'] or common_doc_path($entry['extra_path'])) {
                // Normal or common document
                $download_url = $base_url . "download=$cmdDirName";
            } else {
                // External document
                $download_url = $entry['extra_path'];
            }
            if ($can_upload and !$entry['public']) {
                $link_title_extra .= '&nbsp;' . icon('fa-lock', $langNonPublicFile);
            }

            // open jm in mindmap module
            if ($entry['format'] == "jm"){

                $jmname = $entry['title'];
                $jmpath = base64_encode( json_encode($basedir . $entry['path']) );

                $edit_url = "../mindmap/index.php?course=$course_code&amp;jmpath=$jmpath";


                $tool_content .= "<tr $style><td class='text-center'>.jm</td>
                              <td><a href='$edit_url'>$jmname $link_title_extra</a>";

            }else{

                $tool_content .= "<tr $style><td class='text-center'>$img_href</td>
                              <td><input type='hidden' value='$download_url'>$link_href $link_title_extra";

            }
            // comments
            if (!empty($entry['comment'])) {
                $tool_content .= "<br><span class='comment text-muted'><small>" .
                        nl2br(htmlspecialchars($entry['comment'])) .
                        "</small></span>";
            }
            $tool_content .= "</td>";
            $date = nice_format($entry['date'], true, true);
            $date_with_time = nice_format($entry['date'], true);
            if ($is_dir) {
                $tool_content .= "<td>&nbsp;</td><td class='center'>$date</td>";
            } else if ($entry['format'] == ".meta") {
                $size = format_file_size($entry['size']);
                $tool_content .= "<td>$size</td><td>$date</td>";
            } else {
                $size = format_file_size($entry['size']);
                $tool_content .= "<td>$size</td><td title='$date_with_time'>$date</td>";
            }
            if (!$is_in_tinymce) {
                if ($entry['controls']) {
                    $tool_content .= "<td class='option-btn-cell'>";

                    $xmlCmdDirName = ($entry['format'] == ".meta" && get_file_extension($cmdDirName) == "xml") ? substr($cmdDirName, 0, -4) : $cmdDirName;
                    $tool_content .= action_button(array(
                                    array('title' => $langEditChange,
                                          'url' => "{$base_url}comment=$cmdDirName",
                                          'icon' => 'fa-edit',
                                          'show' => $entry['format'] != '.meta'),
                                    array('title' => $langGroupSubmit,
                                          'url' => "{$urlAppend}modules/work/group_work.php?course=$course_code&amp;group_id=$group_id&amp;submit=$cmdDirName",
                                          'icon' => 'fa-book',
                                          'show' => $subsystem == GROUP and isset($is_member) and $is_member),
                                    array('title' => $langMove,
                                          'url' => "{$base_url}move=$cmdDirName",
                                          'icon' => 'fa-arrows',
                                          'show' => $entry['format'] != '.meta'),
                                    array('title' => $langRename,
                                          'url' => "{$base_url}rename=$cmdDirName",
                                          'icon' => 'fa-pencil',
                                          'show' => $entry['format'] != '.meta'),
                                    array('title' => $langReplace,
                                          'url' => "{$base_url}replace=$cmdDirName",
                                          'icon' => 'fa-exchange',
                                          'show' => !$is_dir && $entry['format'] != '.meta'),
                                    array('title' => $langMetadata,
                                          'url' =>  "{$base_url}metadata=$xmlCmdDirName",
                                          'icon' => 'fa-tags',
                                          'show' => get_config("insert_xml_metadata")),
                                    array('title' => $entry['visible'] ? $langViewHide : $langViewShow,
                                          'url' => "{$base_url}" . ($entry['visible']? "mkInvisibl=$cmdDirName" : "mkVisibl=$cmdDirName"),
                                          'icon' => $entry['visible'] ? 'fa-eye-slash' : 'fa-eye',
                                          'show' => !$uploading_as_user),
                                    array('title' => $entry['public'] ? $langResourceAccessLock : $langResourceAccessUnlock,
                                          'url' => $entry['public'] ? "{$base_url}limited=$cmdDirName" : "{$base_url}public=$cmdDirName",
                                          'icon' => $entry['public'] ? 'fa-lock' : 'fa-unlock',
                                          'show' => !$uploading_as_user),
                                    array('title' => $langDownload,
                                          'url' => $download_url,
                                          'icon' => 'fa-download'),
                                    array('title' => $langAddResePortfolio,
                                          'url' => "$urlServer"."main/eportfolio/resources.php?token=".token_generate('eportfolio' . $uid)."&amp;action=add&amp;type=mydocs&amp;rid=".$row->id,
                                          'icon' => 'fa-star',
                                          'show' => !$is_dir && $subsystem == MYDOCS && $subsystem_id == $uid && get_config('eportfolio_enable')),
                                    array('title' => $langDelete,
                                          'url' => "{$base_url}filePath=$cmdDirName&amp;delete=1",
                                          'icon' => 'fa-times',
                                          'class' => 'delete',
                                          'confirm' => "$langConfirmDelete $entry[filename]")));
                    $tool_content .= "</td>";
                } else { // student view
                    $tool_content .= "<td class='text-center'>" .
                        (($uid or $entry['format'] != '.dir')? icon('fa-download', $dload_msg, $download_url): '&nbsp;') . "</td>";
                }
            }
            $tool_content .= "</tr>";

        }
    }
    $head_content .= "<script>
    $(function(){
        $('.fileModal').click(function (e)
        {
            e.preventDefault();
            var fileURL = $(this).attr('href');
            var downloadURL = $(this).prev('input').val();
            var fileTitle = $(this).attr('title');
            bootbox.dialog({
                size: 'large',
                title: fileTitle,
                message: '<div class=\"row\">'+
                            '<div class=\"col-sm-12\">'+
                                '<div class=\"iframe-container\"><iframe id=\"fileFrame\" src=\"'+fileURL+'\"></iframe></div>'+
                            '</div>'+
                        '</div>',
                buttons: {
                    download: {
                        label: '<i class=\"fa fa-download\"></i> $langDownload',
                        className: 'btn-success',
                        callback: function (d) {
                            window.location = downloadURL;
                        }
                    },
                    print: {
                        label: '<i class=\"fa fa-print\"></i> $langPrint',
                        className: 'btn-primary',
                        callback: function (d) {
                            var iframe = document.getElementById('fileFrame');
                            iframe.contentWindow.print();
                        }
                    },
                    cancel: {
                        label: '$langCancel',
                        className: 'btn-default'
                    }
                }
            });
        });
    });
    </script>";

    }
    $tool_content .= "</table>
            </div>
        </div>
    </div>";
}

if (defined('SAVED_COURSE_CODE')) {
    $course_code = SAVED_COURSE_CODE;
    $course_id = SAVED_COURSE_ID;
}
add_units_navigation(TRUE);
draw($tool_content, $menuTypeID, null, $head_content);

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
 * @global type $sort
 * @global type $reverse
 * @global type $curDirPath
 * @global type $base_url
 * @global type $themeimg
 * @global type $langUp
 * @global type $langDown
 * @param type $label
 * @param type $this_sort
 * @return type
 */
function headlink($label, $this_sort) {
    global $sort, $reverse, $curDirPath, $base_url, $themeimg, $langUp, $langDown;

    if (empty($curDirPath)) {
        $path = '/';
    } else {
        $path = $curDirPath;
    }
    if ($sort == $this_sort) {
        $this_reverse = !$reverse;
        $indicator = " <img src='$themeimg/arrow_" .
                ($reverse ? 'up' : 'down') . ".png' alt='" .
                ($reverse ? $langUp : $langDown) . "'>";
    } else {
        $this_reverse = $reverse;
        $indicator = '';
    }
    return '<a href="' . $base_url . 'openDir=' . $path .
            '&amp;sort=' . $this_sort . ($this_reverse ? '&amp;rev=1' : '') .
            '">' . $label . $indicator . '</a>';
}


/**
 * Used in documents path navigation bar
 * @global type $langRoot
 * @global type $base_url
 * @global type $group_sql
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
            $out .= " &raquo; <a href='{$base_url}openDir=$cur'>".q($dirname)."</a>";
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

