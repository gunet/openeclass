<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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
require_once 'include/action.php';
require_once 'modules/document/doc_init.php';
require_once 'modules/document/doc_metadata.php';
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

$action = new action();
$action->record(MODULE_ID_DOCS);

$require_help = true;
$helpTopic = 'documents';

doc_init();

// Used to check for quotas
$diskUsed = dir_total_space($basedir);

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
    load_js('tinymce.popup.urlgrabber.min.js');
}

load_js('tools.js');
ModalBoxHelper::loadModalBox(true);
copyright_info_init();

if (defined('EBOOK_DOCUMENTS')) {
    $navigation[] = array('url' => 'edit.php?course=' . $course_code . '&amp;id=' . $ebook_id, 'name' => $langEBookEdit);
}

if (isset($_GET['showQuota'])) {
    $backUrl = documentBackLink('');
    $navigation[] = array('url' => $backUrl, 'name' => $pageName);
    showquota($diskQuotaDocument, $diskUsed, $backUrl, $menuTypeID);
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


if ($can_upload) {
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

    $dialogBox = '';
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
        $result = Database::get()->querySingle("SELECT path, visible FROM document WHERE
                                           $group_sql AND
                                           path REGEXP ?s AND
                                           $checkFileSQL LIMIT 1",
                                        "^$uploadPath/[^/]+$", $checkFileName);
        if ($result) {
            if (isset($_POST['replace'])) {
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
                                        copyrighted = ?d"
                            , $course_id, $subsystem, $subsystem_id, $file_path, $extra_path, $fileName, $vis
                            , $_POST['file_comment'], $_POST['file_category'], $_POST['file_title'], $_POST['file_creator']
                            , $file_date, $file_date, $_POST['file_subject'], $_POST['file_description'], $_POST['file_author']
                            , $file_format, $_POST['file_language'], $_POST['file_copyrighted'])->lastInsertID;
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
            if($v->validate()) {
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
                                editable = 1",
                                $course_id, $subsystem, $subsystem_id, $file_path,
                                $fileName, $_POST['file_title'], $file_creator,
                                $file_date, $file_date, $file_creator, $file_format,
                                $language);
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
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        $moveTo = getDirectReference($_POST['moveTo']);
        $source = getDirectReference($_POST['movePath']);
        $sourceXml = $source . '.xml';
        // check if source and destination are the same
        if ($source != $moveTo) {
            $r = Database::get()->querySingle("SELECT filename, extra_path FROM document WHERE $group_sql AND path = ?s", $source);
            $filename = $r->filename;
            $extra_path = $r->extra_path;
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
            redirect_to_current_dir();
        } else {
            Session::Messages($langImpossible, 'alert-danger');
            // return to step 1
            $_GET['move'] = $source;
        }
    }

    // Move file or directory: Step 1
    if (isset($_GET['move'])) {
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
            'directories' => directory_selection($movePath, 'moveTo', $curDirPath, $exclude));
    }

    // Delete file or directory
    if (isset($_GET['delete']) and isset($_GET['filePath'])) {
        $filePath =  getDirectReference($_GET['filePath']);
        $curDirPath = my_dirname(getDirectReference($_GET['filePath']));
        // Check if file actually exists
        $r = Database::get()->querySingle("SELECT id, path, extra_path, format, filename FROM document
                                        WHERE $group_sql AND path = ?s", $filePath);
        $delete_ok = true;        
        if ($r) {
            if (resource_belongs_to_progress_data(MODULE_ID_DOCS, $r->id)) {
                Session::Messages($langResourceBelongsToCert, "alert-warning");
            } else {                
                // remove from index if relevant (except non-main sysbsystems and metadata)
                Database::get()->queryFunc("SELECT id FROM document WHERE course_id >= 1 AND subsystem = 0
                                                AND format <> '.meta' AND path LIKE ?s",
                    function ($r2) use($langResourceBelongsToCert) {
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

        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

        $sourceFile = getDirectReference($_POST['sourceFile']);
        $r = Database::get()->querySingle("SELECT id, filename, format FROM document
            WHERE $group_sql AND path = ?s", $sourceFile);

        if ($r->format != '.dir') {
            validateRenamedFile($_POST['renameTo'], $menuTypeID);
        }

        Database::get()->query("UPDATE document SET filename = ?s, date_modified = NOW()
                          WHERE $group_sql AND path=?s", $_POST['renameTo'], $sourceFile);
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $r->id);
        Log::record($course_id, MODULE_ID_DOCS, LOG_MODIFY, array(
            'path' => $sourceFile,
            'filename' => $r->filename,
            'newfilename' => $_POST['renameTo']));
        if (hasMetaData($sourceFile, $basedir, $group_sql)) {
            if (Database::get()->query("UPDATE document SET filename = ?s WHERE $group_sql AND path = ?s",
                    $_POST['renameTo'] . '.xml',
                    $sourceFile . '.xml')->affectedRows > 0) {
                metaRenameDomDocument($basedir . $sourceFile . '.xml', $_POST['renameTo']);
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
        $r = Database::get()->querySingle("SELECT id, filename, format FROM document
            WHERE $group_sql AND path = ?s", $renamePath);
        $dialogData = array(
            'renamePath' => $_GET['rename'],
            'filename' => $r->filename,
            'filenameLabel' => $r->format == '.dir'? $m['dirname'] : $m['filename']);
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
                Session::Messages($langFileExists, 'alert-danger');
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
        $commentPath = getDirectReference($_POST['commentPath']);
        // check if file exists
        $res = Database::get()->querySingle("SELECT * FROM document
                                             WHERE $group_sql AND
                                                   path=?s", $commentPath);
        if ($res) {
            if ($res->format == '.dir') {
                Database::get()->query("UPDATE document SET comment = ?s
                     WHERE $group_sql AND path = ?s", $_POST['file_comment'], $commentPath);
            } else {
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
                                              path = ?s",
                    $_POST['file_comment'], $_POST['file_category'],
                    $_POST['file_title'], $_POST['file_subject'],
                    $_POST['file_description'], $_POST['file_author'],
                    $file_language, $_POST['file_copyrighted'], $commentPath);
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
                                WHERE $group_sql AND path = ?s"
                    , ($_SESSION['givenname'] . " " . $_SESSION['surname']), $file_format, $_POST['meta_language'], $metadataPath);
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
                                language = ?s"
                    , $course_id, $subsystem, $subsystem_id, $metadataPath, $oldFilename
                    , ($_SESSION['givenname'] . " " . $_SESSION['surname']), $xml_date, $xml_date, $file_format, $_POST['meta_language']);
        }

        Session::Messages($langMetadataMod, 'alert-success');
        redirect_to_current_dir();
    }

    if (isset($_POST['replacePath']) and
            isset($_FILES['newFile']) and
            is_uploaded_file($_FILES['newFile']['tmp_name'])) {

        validateUploadedFile($_FILES['newFile']['name'], $menuTypeID);
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        $replacePath = getDirectReference($_POST['replacePath']);
        // Check if file actually exists
        $result = Database::get()->querySingle("SELECT id, path, format FROM document WHERE
                                        $group_sql AND
                                        format <> '.dir' AND
                                        path = ?s", $replacePath);
        if ($result) {
            $docId = $result->id;
            $oldpath = $result->path;
            $oldformat = $result->format;
            $curDirPath = my_dirname($replacePath);
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
                        WHERE $group_sql AND path = ?s",
                    $newpath, $newformat, ($_FILES['newFile']['name']), $oldpath)->affectedRows;
                if (!copy($_FILES['newFile']['tmp_name'], $basedir . $newpath) or $affectedRows == 0) {
                    Session::Messages($langGeneralError, 'alert-danger');
                    redirect_to_current_dir();
                } else {
                    require_once 'modules/admin/extconfig/externals.php';
                    $connector = AntivirusApp::getAntivirus();
                    if ($connector->isEnabled() == true ){
                        $output = $connector->check($basedir . $newpath);
                        if ($output->status==$output::STATUS_INFECTED){
                            AntivirusApp::block($output->output);
                        }
                    }
                    if (hasMetaData($oldpath, $basedir, $group_sql)) {
                        rename($basedir . $oldpath . ".xml", $basedir . $newpath . ".xml");
                        Database::get()->query("UPDATE document SET path = ?s, filename=?s
                                WHERE $group_sql AND path = ?s",
                            $newpath . '.xml', $_FILES['newFile']['name'] . '.xml', $oldpath . '.xml');
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
        $result = Database::get()->querySingle("SELECT filename, path FROM document
                                        WHERE $group_sql AND
                                                format <> '.dir' AND
                                                path = ?s",  getDirectReference($_GET['replace']));
        if ($result) {
            $dialogBox = 'replace';
            $curDirPath = my_dirname($result->path);
            $backUrl = documentBackLink($curDirPath);
            $navigation[] = array('url' => $backUrl, 'name' => $pageName);
            enableCheckFileSize();
            $dialogData = array(
                'filename' => $result->filename,
                'replacePath' => $_GET['replace'],
                'replaceMessage' => sprintf($langReplaceFile, '<b>' . q($result->filename) . '</b>'));
        }
    }

    // Add comment form
    if (isset($_GET['comment'])) {
        $comment = getDirectReference($_GET['comment']);
        // Retrieve the old comment and metadata
        $row = Database::get()->querySingle("SELECT * FROM document WHERE $group_sql AND path = ?s", $comment);
        if ($row) {
            $dialogBox = 'comment';
            $curDirPath = my_dirname($comment);
            $backUrl = documentBackLink($curDirPath);
            $navigation[] = array('url' => $backUrl, 'name' => $pageName);

            copyright_info_init();

            $dialogData = array(
                'file' => $row,
                'is_dir' => $row->format == '.dir',
                'languages' => $fileLanguageNames,
                'categories' => $fileCategoryNames,
                'copyrightTitles' => $copyright_titles);
        } else {
            Session::Messages($langFileNotFound, 'alert-danger');
            view('layouts.default', $data);
            exit;
        }
    }

    // Display form to modify metadata
    if (isset($_GET['metadata'])) {

        $metadata = $_GET['metadata'];
        $row = Database::get()->querySingle("SELECT filename FROM document WHERE $group_sql AND path = ?s", $metadata);
        if ($row) {
            $curDirPath = my_dirname($metadata);
            $backUrl = documentBackLink($curDirPath);
            $navigation[] = array('url' => $backUrl, 'name' => $pageName);
            $oldFilename = q($row->filename);
            $real_filename = $basedir . str_replace('/..', '', q($metadata));
            $dialogBox .= metaCreateForm($metadata, $oldFilename, $real_filename);
        } else {
            Session::Messages($langFileNotFound, 'alert-danger');
        }
    }

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
            Session::Messages($langResourceBelongsToCert, "alert-warning");
        } else {
            Database::get()->query("UPDATE document SET visible = ?d
                                              WHERE $group_sql AND
                                                    path = ?s", $newVisibilityStatus, $visibilityPath);            
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $r->id);
            Session::Messages($langViMod, 'alert-success');
            $curDirPath = my_dirname($visibilityPath);
            redirect_to_current_dir();
        }
    }

    // Public accessibility commands
    if (isset($_GET['public']) || isset($_GET['limited'])) {
        $new_public_status = intval(isset($_GET['public']));
        $path = isset($_GET['public']) ? getDirectReference($_GET['public']) : getDirectReference($_GET['limited']);
        Database::get()->query("UPDATE document SET public = ?d
                                          WHERE $group_sql AND
                                                path = ?s", $new_public_status, $path);
        $r = Database::get()->querySingle("SELECT id FROM document WHERE $group_sql AND path = ?s", $path);
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $r->id);
        Session::Messages($langViMod, 'alert-success');
        $curDirPath = my_dirname($path);
        redirect_to_current_dir();
    }
} // teacher only

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
    view('layouts.default', $data);
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
        view('layouts.default', $data);
        exit;
    } elseif (!$can_upload and !resource_access($dirInfo->visible, $dirInfo->public)) {
        if (!$uid) {
            // If not logged in, try to log in first
            $next = str_replace($urlAppend, '/', $_SERVER['REQUEST_URI']);
            header("Location:" . $urlServer . "main/login_form.php?next=" . urlencode($next));
        } else {
            // Logged in but access forbidden
            Session::Messages($langInvalidDir);
            view('layouts.default', $data);
        }
        exit;
    }
}

// Retrieve file info for current directory from database and disk
$result = Database::get()->queryArray("SELECT id, path, filename, format,
                                        title, extra_path, course_id,
                                        date_modified, public, visible,
                                        editable, copyrighted, comment,
                                        IF((title = '' OR title IS NULL), filename, title) AS sort_key
                                FROM document
                                WHERE $group_sql AND
                                      path LIKE '$curDirPath/%' AND
                                      path NOT LIKE '$curDirPath/%/%' $filter $order");
$files = $dirs = array();
$cmdCurDirPath = rawurlencode($curDirPath);
$cmdParentDir = rawurlencode($parentDir);
foreach ($result as $row) {
    if (!$can_upload and !resource_access($row->visible, $row->public)) {
        continue;
    }
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
        'is_dir' => $is_dir,
        'size' => format_file_size($size),
        'title' => $row->sort_key,
        'filename' => $row->filename,
        'format' => $row->format,
        'path' => $row->path,
        'extra_path' => $row->extra_path,
        'visible' => ($row->visible == 1),
        'public' => $row->public,
        'comment' => $row->comment,
        'date' => nice_format($row->date_modified, true, true),
        'date_time' => nice_format($row->date_modified, true),
        'editable' => $row->editable,
        'updated_message' => $updated_message);

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
    if (!$is_in_tinymce) {
        $cmdDirName = getIndirectReference($row->path);
        if ($can_upload) {
            $xmlCmdDirName = ($row->format == ".meta" && get_file_extension($row->path) == 'xml') ? substr($row->path, 0, -4) : $row->path;
            $info['action_button'] = action_button(array(
                array('title' => $langEditChange,
                      'url' => "{$base_url}comment=" . $cmdDirName,
                      'icon' => 'fa-edit',
                      'show' => $row->format != '.meta'),
                array('title' => $langGroupSubmit,
                      'url' => "{$urlAppend}modules/work/group_work.php?course=$course_code&amp;group_id=$group_id&amp;submit=$cmdDirName",
                      'icon' => 'fa-book',
                      'show' => $subsystem == GROUP and isset($is_member) and $is_member),
                array('title' => $langMove,
                      'url' => "{$base_url}move=$cmdDirName",
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
                      'url' => "{$base_url}" . ($row->visible? 'mkInvisibl=' : 'mkVisibl=') . $cmdDirName,
                      'icon' => $row->visible ? 'fa-eye-slash' : 'fa-eye'),
                array('title' => $row->public ? $langResourceAccessLock : $langResourceAccessUnlock,
                      'url' => $base_url . ($row->public ? 'limited=' : 'public=') . $cmdDirName,
                      'icon' => $row->public ? 'fa-lock' : 'fa-unlock'),
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
                      'confirm' => "$langConfirmDelete {$row->filename}")));
        } elseif ($uid or $row->format != '.dir') {
            $info['action_button'] = icon('fa-download', $downloadMessage, $download_url);
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
            $info['copyright_icon'] = ($copyid == 1) ? 'fa-copyright' : 'fa-cc';
            $info['copyright_title'] = $copyright_titles[$copyid];
            $info['copyright_link'] = $copyright_links[$copyid];
        }
        $files[] = (object) $info;
    }
}

// ----------------------------------------------
// Display
// ----------------------------------------------

$data = compact('menuTypeID', 'can_upload', 'is_in_tinymce', 'base_url',
    'group_hidden_input', 'curDirName', 'curDirPath', 'dialogBox');
$data['fileInfo'] = array_merge($dirs, $files);

if (isset($dialogData)) {
    $data = array_merge($data, $dialogData);
}

if ($curDirName) {
    $data['dirComment'] = Database::get()->querySingle("SELECT comment FROM document WHERE $group_sql AND path = ?s", $curDirPath)->comment;
    $data['parentLink'] = $base_url . 'openDir=' . $cmdParentDir;
}

if ($can_upload and !$is_in_tinymce) {
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
    $diskQuotaDocument = $diskQuotaDocument * 1024 / 1024;
    $data['actionBar'] = action_bar(array(
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
        ), false);
} else {
    $data['actionBar'] = $data['dialogBox'] = '';
}

if (count($data['fileInfo'])) {
    $download_path = empty($curDirPath) ? '/' : $curDirPath;
    $data['downloadPath'] = (!$is_in_tinymce and $uid) ? ("{$base_url}download=" . getIndirectReference($download_path)) : '';
} else {
    $data['downloadPath'] = '';
}
$data['backUrl'] = isset($backUrl)? $backUrl: documentBackLink($curDirPath);

if (defined('SAVED_COURSE_CODE')) {
    $course_code = SAVED_COURSE_CODE;
    $course_id = SAVED_COURSE_ID;
}
add_units_navigation(true);
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
        $indicator = ' <span class="fa fa-sort-' .
                ($reverse ? 'asc' : 'desc') . '"><span>';
    } else {
        $this_reverse = $reverse;
        $indicator = '';
    }
    return '<a class="text-nowrap" href="' . $base_url . 'openDir=' . $path .
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

