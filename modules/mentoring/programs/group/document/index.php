<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2020  Greek Universities Network - GUnet
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


$require_login = true;
require_once '../../../../../include/baseTheme.php';
require_once 'mentoring_doc_init.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';

require_once 'modules/search/indexer.class.php';
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'modules/drives/clouddrive.php';
require_once 'modules/mentoring/functions.php';

if(defined('MENTORING_GROUP_DOCUMENTS')){
    mentoring_program_access();
}

$require_help = true;
$helpTopic = 'documents';

mentoring_doc_init();

if(defined('MENTORING_MYDOCS')){
    $can_upload_mentoring = $session->user_id == $uid;
}else{
    $can_upload_mentoring = $is_editor_mentoring_program || $is_editor_mentoring_group || $is_admin;
}

// Used to check for quotas
$diskUsed = dir_total_space($basedir);

$user_upload = $uid && $subsystem == MENTORING_GROUP && get_config('enable_docs_public_write') && mentoring_setting_get($program_group_id);
$uploading_as_user = !$can_upload_mentoring && $user_upload;

if (defined('MENTORING_COMMON_DOCUMENTS')) {
    $menuTypeID = 3;
    $data['menuTypeID'] = $menuTypeID;
    $toolName = $pageName;
    $diskQuotaDocument = $diskUsed + parseSize(ini_get('upload_max_filesize'));
}elseif (defined('MENTORING_MYDOCS')) {
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
}else{
    $menuTypeID = 2;
    $type = ($subsystem == MENTORING_GROUP) ? 'group_quota' : 'doc_quota';
    $diskQuotaDocument = Database::get()->querySingle("SELECT $type AS quotatype FROM mentoring_programs WHERE id = ?d", $mentoring_program_id)->quotatype;
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

if (isset($_GET['showQuota'])) {
    $backUrl = documentBackLink('');
    $navigation[] = array('url' => $backUrl, 'name' => $pageName);
    showquota($diskQuotaDocument, $diskUsed, $backUrl, $menuTypeID);
    exit;
}

$dialogBox = $metaDataBox = '';
if (!defined('MENTORING_MYDOCS') and !defined('MENTORING_COMMON_DOCUMENTS')){
    $title_group = Database::get()->querySingle("SELECT name FROM mentoring_group WHERE id = ?d",$program_group_id)->name;
    $toolName = $langDoc . ' ' .'('.$title_group .')'; 
}


// ---------------------------
// manage docs for user-mentee
// ---------------------------
if(isset($_POST['settingsDocYes'])){
    Database::get()->query("UPDATE mentoring_group SET allow_manage_doc = ?d WHERE id = ?d",$_POST['settingsDocYes'],$program_group_id);
}
if(isset($_POST['settingsDocNo'])){
    Database::get()->query("UPDATE mentoring_group SET allow_manage_doc = ?d WHERE id = ?d",$_POST['settingsDocNo'],$program_group_id);
}
if(isset($_POST['settingsDocYes']) or isset($_POST['settingsDocNo'])){
    Session::flash('message',$langFaqEditSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_current_dir();
}
if(isset($_GET['settingsUsersdoc']) and !defined('MENTORING_MYDOCS')){
    $dialogBox = 'user_docs_settings';
    //$navigation[] = array('url' => $backUrl, 'name' => $pageName);
    if (!$uploading_as_user) {
        $setting_value = Database::get()->querySingle("SELECT allow_manage_doc FROM mentoring_group WHERE id = ?d",$program_group_id)->allow_manage_doc;
        $dialogData = array(
            'setting_value' => $setting_value);
    }
}

// ---------------------------
// download directory or file
// ---------------------------
if (isset($_GET['download'])) {
    $downloadDir = getDirectReference($_GET['download']);

    if ($downloadDir == '/') {
        $format = '.dir';
        $mentoring_public_code = Database::get()->querySingle("SELECT public_code FROM 
                                                               mentoring_programs WHERE code = ?s",$mentoring_program_code)->public_code; 
        $real_filename = remove_filename_unsafe_chars($langDoc . ' ' . $mentoring_public_code);
    } else {
        $q = Database::get()->querySingle("SELECT filename, format, visible, extra_path, public FROM mentoring_document
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
        if (!(mentoring_resource_access($visible, $public) or (isset($status) and $status == USER_TEACHER))) {
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
        $dload_filename = $webDir . '/mentoring_programs/temp/' . safe_filename('zip');
        zip_documents_directory($dload_filename, $downloadDir, $can_upload_mentoring);
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


if ($can_upload_mentoring or $user_upload) {
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
        $result = Database::get()->querySingle("SELECT id FROM mentoring_document
                        WHERE $group_sql AND path = ?s LIMIT 1", $uploadPath);
        if (!$result or !$result->id) {
            $error = $langImpossible;
        }
    }

    /* ******************************************************************** *
      UPLOAD FILE
     * ******************************************************************** */

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
        // check file type
        validateUploadedFile($fileName, $menuTypeID);
        // check for disk quotas
        if ($diskUsed + @$_FILES['userFile']['size'] > $diskQuotaDocument) {
            Session::flash('message',$langNoSpace);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_current_dir();
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
                        validateUploadedFile(my_basename($files_in_zip[$i]), $menuTypeID);
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
                Session::flash('message',$langErrorFileMustBeZip);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_current_dir();
            }
            //$session->setDocumentTimestamp($mentoring_program_id);
            Session::flash('message',$langDownloadAndZipEnd);
            Session::flash('alert-class', 'alert-success');
            redirect_to_current_dir();
        } else {
            $fileName = canonicalize_whitespace($fileName);
            $uploaded = true;
        }
    } elseif (isset($_POST['fileURL']) and ($fileURL = trim($_POST['fileURL']))) {
        $extra_path = canonicalize_url($fileURL);
        if (preg_match('/^javascript/', $extra_path)) {
            Session::flash('message',$langUnwantedFiletype . ': ' . q($extra_path));
            Session::flash('alert-class', 'alert-danger');
            redirect_to_current_dir();
        } else {
            $uploaded = true;
        }
        $components = explode('/', trim($extra_path, '/'));
        $fileName = end($components);
    } elseif (isset($_POST['file_content'])) {

        if ($diskUsed + strlen($_POST['file_content']) > $diskQuotaDocument) {
            Session::flash('message',$langNoSpace);
            Session::flash('alert-class', 'alert-danger');
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
        $result = Database::get()->querySingle("SELECT path, visible, lock_user_id FROM mentoring_document WHERE
                                           $group_sql AND
                                           path REGEXP ?s AND
                                           $checkFileSQL LIMIT 1",
                                        "^$uploadPath/[^/]+$", $checkFileName);
        if ($result) {
            if (isset($_POST['replace']) and (!$uploading_as_user or $result->lock_user_id == $uid)) {
                // Delete old file record when replacing file
                $file_path = $result->path;
                $vis = $result->visible;
                Database::get()->query("DELETE FROM mentoring_document WHERE
                                                 $group_sql AND
                                                 path = ?s", $file_path);
            } else {
                $error = $langFileExists;
            }
        }
    }
    if ($error) {
        Session::flash('message',$error);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_current_dir();
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
                Session::flash('message',$langCloudFileError);
                Session::flash('alert-class', 'alert-danger');
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
            $id = Database::get()->query("INSERT INTO mentoring_document SET
                                        mentoring_program_id = ?d,
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
                            , $mentoring_program_id, $subsystem, $subsystem_id, $file_path, $extra_path, $fileName, $vis
                            , $_POST['file_comment'], $_POST['file_category'], $_POST['file_title'], $_POST['file_creator']
                            , $file_date, $file_date, $_POST['file_subject'], $_POST['file_description'], $_POST['file_author']
                            , $file_format, $_POST['file_language'], $_POST['file_copyrighted'], $uid)->lastInsertID;
            //Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $id);
            // Logging
            Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_DOCS, MENTORING_LOG_INSERT, array('id' => $id,
                'filepath' => $file_path,
                'filename' => $fileName,
                'comment' => $_POST['file_comment'],
                'title' => $_POST['file_title']));
                Session::flash('message',$langDownloadEnd);
                Session::flash('alert-class', 'alert-success');
            //$session->setDocumentTimestamp($mentoring_program_id);
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
                    $fileInfo = Database::get()->querySingle("SELECT * FROM mentoring_document
                        WHERE $group_sql AND path = ?s", $_POST['editPath']);
                    if ($fileInfo->editable) {
                        $file_path = $fileInfo->path;
                        $q = Database::get()->query("UPDATE mentoring_document
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
                    $q = Database::get()->query("INSERT INTO mentoring_document SET
                                mentoring_program_id = ?d,
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
                                $mentoring_program_id, $subsystem, $subsystem_id, $file_path,
                                $fileName, $_POST['file_title'], $file_creator,
                                $file_date, $file_date, $file_creator, $file_format,
                                $language, $uid);
                }
                if ($q) {
                    if (!isset($id)) {
                        $id = $q->lastInsertID;
                        $log_action = MENTORING_LOG_INSERT;
                    } else {
                        $log_action = MENTORING_LOG_MODIFY;
                    }

                    Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_DOCS, $log_action,
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
                    
                    Session::flash('message',$langDownloadEnd);
                    Session::flash('alert-class', 'alert-success');

                }
               
                redirect_to_current_dir($curDirPath);
            } else {
                
                $append_to_url = isset($_POST['editPath']) ? "&editPath=$_POST[editPath]" : "&uploadPath=$curDirPath";
                $redirect_url = "modules/document/new.php?program=$mentoring_program_code$append_to_url";
                redirect_to_home_page($redirect_url);
                
            }
            
            
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
            $r = Database::get()->querySingle("SELECT filename, extra_path FROM mentoring_document WHERE $group_sql AND path = ?s", $source);
            $filename = $r->filename;
            $extra_path = $r->extra_path;
            if (empty($extra_path)) {
                if (move($basedir . $source, $basedir . $moveTo)) {
                    if (mentoring_hasMetaData($source, $basedir, $group_sql)) {
                        move($basedir . $sourceXml, $basedir . $moveTo);
                    }
                    update_db_info('mentoring_document', 'update', $source, $filename, $moveTo . '/' . my_basename($source));
                }
            } else {
                update_db_info('mentoring_document', 'update', $source, $filename, $moveTo . '/' . my_basename($source));
            }
            Session::flash('message',$langDirMv);
            Session::flash('alert-class', 'alert-success');
            $curDirPath = $moveTo;
            redirect_to_current_dir();
        } else {
            Session::flash('message',$langImpossible);
            Session::flash('alert-class', 'alert-danger');
            // return to step 1
            $_GET['move'] = $source;
        }
    }

    //Move file or directory: Step 1
    if (isset($_GET['move'])) {
        $file = $_GET['file'];
        $dialogBox = 'move';
        $movePath = getDirectReference($_GET['move']);
        $curDirPath = my_dirname($movePath);
        $navigation[] = array('url' => documentBackLink($curDirPath), 'name' => $pageName);

        // $move contains file path - search for filename in db
        $q = Database::get()->querySingle("SELECT filename, format FROM mentoring_document
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
        $filePath =  getDirectReference($_GET['filePath']);
        $curDirPath = my_dirname(getDirectReference($_GET['filePath']));
        // Check if file actually exists
        $r = Database::get()->querySingle("SELECT id, path, extra_path, format, filename, lock_user_id FROM mentoring_document
                                        WHERE $group_sql AND path = ?s", $filePath);
        $delete_ok = true;
        if ($r and (!$uploading_as_user or $r->lock_user_id == $uid)) {
                
                if (empty($r->extra_path)) {
                    if ($delete_ok = my_delete($basedir . $filePath) && $delete_ok) {
                        if (mentoring_hasMetaData($filePath, $basedir, $group_sql)) {
                            $delete_ok = my_delete($basedir . $filePath . ".xml") && $delete_ok;
                        }
                        update_db_info('mentoring_document', 'delete', $filePath, $r->filename);
                    }
                } else {
                    update_db_info('mentoring_document', 'delete', $filePath, $r->filename);
                }

                if ($delete_ok) {
                    Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_DOCS, MENTORING_LOG_DELETE, array('path' => $filePath,
                        'filename' => $r->filename,
                        'comment' => '',
                        'title' => ''));
                    
                    Session::flash('message',$langDocDeleted);
                    Session::flash('alert-class', 'alert-success');
                } else {
                    Session::flash('message',$langGeneralError);
                    Session::flash('alert-class', 'alert-danger');
                }
                redirect_to_current_dir();
            
        }
    }

    /*****************************************
      RENAME
     ******************************************/
    // Step 2: Rename file by updating record in database
    if (isset($_POST['renameTo'])) {

        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

        $renameTo = canonicalize_whitespace($_POST['renameTo']);
        $sourceFile = getDirectReference($_POST['sourceFile']);
        $r = Database::get()->querySingle("SELECT id, filename, format FROM mentoring_document
            WHERE $group_sql AND path = ?s", $sourceFile);

        if ($r->format != '.dir') {
            validateRenamedFile($renameTo, $menuTypeID);
        }

        Database::get()->query("UPDATE mentoring_document SET filename = ?s, date_modified = NOW()
                          WHERE $group_sql AND path=?s", $renameTo, $sourceFile);
        // Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $r->id);
        Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_DOCS, MENTORING_LOG_MODIFY, array(
            'path' => $sourceFile,
            'filename' => $r->filename,
            'newfilename' => $renameTo));
        if (mentoring_hasMetaData($sourceFile, $basedir, $group_sql)) {
            if (Database::get()->query("UPDATE mentoring_document SET filename = ?s WHERE $group_sql AND path = ?s",
                    $renameTo . '.xml',
                    $sourceFile . '.xml')->affectedRows > 0) {
                mentoring_metaRenameDomDocument($basedir . $sourceFile . '.xml', $renameTo);
            }
        }
        $curDirPath = my_dirname($sourceFile);
        Session::flash('message',$langElRen);
        Session::flash('alert-class', 'alert-success');
        redirect_to_current_dir();
    }

    // Step 1: Show rename dialog box
    if (isset($_GET['rename'])) {
        $dialogBox = 'rename';
        $renamePath = getDirectReference($_GET['rename']);
        $curDirPath = my_dirname($renamePath);
        $backUrl = documentBackLink($curDirPath);
        $navigation[] = array('url' => $backUrl, 'name' => $pageName);
        $r = Database::get()->querySingle("SELECT id, filename, format, lock_user_id FROM mentoring_document
            WHERE $group_sql AND path = ?s", $renamePath);
        if ($r and (!$uploading_as_user or $r->lock_user_id == $uid)) {
            $dialogData = array(
                'renamePath' => $_GET['rename'],
                'filename' => $r->filename,
                'filenameLabel' => $r->format == '.dir'? $m['dirname'] : $m['filename']);
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
                
                Session::flash('message',$langDirCr);
                Session::flash('alert-class', 'alert-success');
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
        $res = Database::get()->querySingle("SELECT * FROM mentoring_document
                                             WHERE $group_sql AND
                                                   path=?s", $commentPath);
        if ($res and (!$uploading_as_user or $res->lock_user_id == $uid)) {
            if ($res->format == '.dir') {
                Database::get()->query("UPDATE mentoring_document SET comment = ?s
                     WHERE $group_sql AND path = ?s", $_POST['file_comment'], $commentPath);
            } else {
                $file_language = $session->validate_language_code($_POST['file_language'], $language);
                Database::get()->query("UPDATE mentoring_document SET
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
            //Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_DOCUMENT, $res->id);
            Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_DOCS, MENTORING_LOG_MODIFY, array('path' => $commentPath,
                'filename' => $res->filename,
                'comment' => $_POST['file_comment'],
                'title' => $_POST['file_title']));
            }
            $curDirPath = my_dirname($commentPath);
            Session::flash('message',$langComMod);
            Session::flash('alert-class', 'alert-success');
            redirect_to_current_dir();
        }
    }

    if (isset($_POST['replacePath']) and
            isset($_FILES['newFile']) and
            is_uploaded_file($_FILES['newFile']['tmp_name'])) {
        validateUploadedFile($_FILES['newFile']['name'], $menuTypeID);
        $replacePath = getDirectReference($_POST['replacePath']);
        // Check if file actually exists
        $result = Database::get()->querySingle("SELECT id, path, format, lock_user_id FROM mentoring_document WHERE
                                        $group_sql AND
                                        format <> '.dir' AND
                                        path=?s", $replacePath);
        if ($result and (!$uploading_as_user or $result->lock_user_id == $uid)) {
            $docId = $result->id;
            $oldpath = $result->path;
            $oldformat = $result->format;
            $curDirPath = $_POST['curDirPathAfterReplace'];
            // check for disk quota
            if ($diskUsed - filesize($basedir . $oldpath) + $_FILES['newFile']['size'] > $diskQuotaDocument) {
                Session::flash('message',$langNoSpace);
                Session::flash('alert-class', 'alert-danger');
                redirect_to_current_dir();
            } else {
                $newformat = get_file_extension($_FILES['newFile']['name']);
                $newpath = preg_replace("/\\.$oldformat$/", '', $oldpath) .
                        (empty($newformat) ? '' : '.' . $newformat);
                my_delete($basedir . $oldpath);
                $affectedRows = Database::get()->query("UPDATE mentoring_document SET path = ?s, format = ?s, filename = ?s, date_modified = NOW()
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
                    if (mentoring_hasMetaData($oldpath, $basedir, $group_sql)) {
                        rename($basedir . $oldpath . ".xml", $basedir . $newpath . ".xml");
                        Database::get()->query("UPDATE mentoring_document SET path = ?s, filename=?s WHERE $group_sql AND path = ?s"
                                , ($newpath . ".xml"), ($_FILES['newFile']['name'] . ".xml"), ($oldpath . ".xml"));
                    }
                    
                    Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_DOCS, MENTORING_LOG_MODIFY, array('oldpath' => $oldpath,
                        'newpath' => $newpath,
                        'filename' => $_FILES['newFile']['name']));
                        Session::flash('message',$langReplaceOK);
                        Session::flash('alert-class', 'alert-success');
                    redirect_to_current_dir();
                }
            }
        }
    }

    // Display form to replace/overwrite an existing file
    if (isset($_GET['replace'])) {
        $result = Database::get()->querySingle("SELECT filename, path, lock_user_id FROM mentoring_document
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
                'replaceMessage' => sprintf($langReplaceFile, '<span class="lightBlueText">' . q($result->filename) . '</span>'));
        }
    }

    // Add comment form
    if (isset($_GET['comment'])) {
        $comment = getDirectReference($_GET['comment']);
        // Retrieve the old comment and metadata
        $row = Database::get()->querySingle("SELECT * FROM mentoring_document WHERE $group_sql AND path = ?s", $comment);
        if ($row and (!$uploading_as_user or $row->lock_user_id == $uid)) {
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
            Session::flash('message',$langFileNotFound);
            Session::flash('alert-class', 'alert-danger');
            view('layouts.default', $data);
            exit;
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
            $r = Database::get()->querySingle("SELECT id FROM mentoring_document WHERE $group_sql AND path = ?s", $visibilityPath);
           
            Database::get()->query("UPDATE mentoring_document SET visible = ?d
                                                WHERE $group_sql AND
                                                    path = ?s", $newVisibilityStatus, $visibilityPath);
            
            Session::flash('message',$langViMod);
            Session::flash('alert-class', 'alert-success');
            $curDirPath = my_dirname($visibilityPath);
            redirect_to_current_dir();
            
        }

        // Public accessibility commands
        if (isset($_GET['public']) || isset($_GET['limited'])) {
            $new_public_status = intval(isset($_GET['public']));
            $path = isset($_GET['public']) ? $_GET['public'] : $_GET['limited'];
            Database::get()->query("UPDATE mentoring_document SET public = ?d
                                              WHERE $group_sql AND
                                                    path = ?s", $new_public_status, $path);
            $r = Database::get()->querySingle("SELECT id FROM mentoring_document WHERE $group_sql AND path = ?s", $path);
            Session::flash('message',$langViMod);
            Session::flash('alert-class', 'alert-success');
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
try {
    if (strpos($curDirName, '/../') !== false or ! is_dir(realpath($basedir . $curDirPath))) {
        Session::flash('message',$langInvalidDir);
    Session::flash('alert-class', 'alert-danger');
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

$document_timestamp = false;

// Check directory access if in subfolder
if ($curDirPath) {
    $dirInfo = Database::get()->querySingle("SELECT visible, public
        FROM mentoring_document WHERE $group_sql AND path = ?s", $curDirPath);
    if (!$dirInfo) {
        Session::flash('message',$langInvalidDir);
        Session::flash('alert-class', 'alert-danger');
        view('layouts.default', $data);
        exit;
    } elseif (!$can_upload_mentoring and !$is_member) {
        if (!$uid) {
            // If not logged in, try to log in first
            $next = str_replace($urlAppend, '/', $_SERVER['REQUEST_URI']);
            header("Location:" . $urlServer . "main/login_form.php?next=" . urlencode($next));
        } else {
            // Logged in but access forbidden
            Session::flash('message',$langInvalidDir);
            Session::flash('alert-class', 'alert-danger');
            view('layouts.default', $data);
        }
        exit;
    }
}

// Retrieve file info for current directory from database and disk
$result = Database::get()->queryArray("SELECT id, path, filename,
        format, title, extra_path, mentoring_program_id, date_modified,
        public, visible, editable, copyrighted, comment, lock_user_id,
        IF((title = '' OR title IS NULL), filename, title) AS sort_key
    FROM mentoring_document
    WHERE $group_sql AND
          path LIKE ?s AND
          path NOT LIKE ?s $filter $order",
    "$curDirPath/%", "$curDirPath/%/%");
$dirs = $files = [];

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
        // print_r('new_path:'.$path);
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
        $updated = intval(Database::get()->querySingle("SELECT COUNT(*) AS c FROM mentoring_document
            WHERE $group_sql AND
                  path LIKE ?s AND
                  date_modified > ?t" .
                  ($can_upload_mentoring? '': ' AND visible=1'),
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
        'lock_user_id' => $row->lock_user_id,
        'controls' => $can_upload_mentoring || ($user_upload && $row->lock_user_id == $uid),
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
    if (!$is_in_tinymce) {
        $cmdDirName = getIndirectReference($row->path);
        if ($can_upload_mentoring or ($uploading_as_user and $row->lock_user_id == $uid)) {
            $xmlCmdDirName = ($row->format == ".meta" && get_file_extension($row->path) == 'xml') ? substr($row->path, 0, -4) : $row->path;
            $info['action_button'] = action_button(array(
                array('title' => $langEditChange,
                      'url' => "{$base_url}comment=" . $cmdDirName,
                      'icon' => 'fa-edit',
                      'show' => $row->format != '.meta'),
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
                array('title' => $row->visible ? $langViewHide : $langViewShow,
                      'url' => "{$base_url}" . ($row->visible? 'mkInvisibl=' : 'mkVisibl=') . $cmdDirName,
                      'icon' => $row->visible ? 'fa-eye-slash' : 'fa-eye',
                      'show' => !$uploading_as_user),
                array('title' => $row->public ? $langResourceAccessLock : $langResourceAccessUnlock,
                'url' => "{$base_url}" . ($row->public ? 'limited=' : 'public=') . $row->path,
                      'icon' => $row->public ? 'fa-lock' : 'fa-unlock',
                      'show' => !$uploading_as_user),
                array('title' => $langDownload,
                      'url' => $download_url,
                      'icon' => 'fa-download'),
                array('title' => $langDelete,
                      'url' => "{$base_url}filePath=$cmdDirName&amp;delete=1",
                      'class' => 'delete',
                      'icon' => 'fa-times',
                      'confirm' => $langConfirmDelete . ' ' . q($row->filename))));
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
        $info['url'] = mentoring_file_url($row->path, $row->filename,$program_group_id);
        $dObj = MediaResourceFactory::initFromDocument($row);
        $dObj->setAccessURL($info['url']);
        if ($is_in_tinymce && !$compatiblePlugin) {
            // use Access/DL URL for non-modable tinymce plugins
            $dObj->setPlayURL($dObj->getAccessURL());
        } else {
            $dObj->setPlayURL(mentoring_file_playurl($row->path, $row->filename,$program_group_id));
        }
        if($row->visible == 1){
            $info['link'] = MultimediaHelper::chooseMediaAhref($dObj);
        }else{
            $info['link'] = '<a style="color:grey; opacity:0.5;">'.$row->filename.'</href>';
        }
        //print_a($info);
        if ($row->editable) {
            if($groupset == ''){//mydocs
                if(defined('MENTORING_COMMON_DOCUMENTS')){//common docs
                    $info['edit_url'] = "new.php?editPath=" . $row->path . "&editPathCommon=true"; 
                }else{//mydocs
                   $info['edit_url'] = "new.php?editPath=" . $row->path . "&editPathMydoc=true"; 
                }
                
            }else{//group docs
                $info['edit_url'] = "new.php?editPath=" . $row->path . "&$groupset";
            }
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
// end of common to teachers and students
// ----------------------------------------------
// Display
// ----------------------------------------------

$data = compact('menuTypeID', 'can_upload_mentoring', 'is_in_tinymce', 'base_url',
    'group_hidden_input', 'curDirName', 'curDirPath', 'dialogBox','metaDataBox');
$data['fileInfo'] = array_merge($dirs, $files);

if (isset($dialogData)) {
    $data = array_merge($data, $dialogData);
}

if ($curDirName) {
    $data['dirComment'] = Database::get()->querySingle("SELECT comment FROM mentoring_document WHERE $group_sql AND path = ?s", $curDirPath)->comment;
    $data['parentLink'] = $base_url . 'openDir=' . rawurlencode($parentDir);
}

if (($can_upload_mentoring or $user_upload)) {
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
        array('title' => $langBackPage,
              'url' => "{$urlAppend}modules/mentoring/programs/group/group_space.php?space_group_id=".getInDirectReference($program_group_id),
              'icon' => 'fa-reply',
              'level' => 'primary-label',
              'show' => $subsystem == MENTORING_GROUP),
        array('title' => $langDownloadFile,
              'url' => "{$base_url}upload=true&uploadPath=$curDirPath",
              'icon' => 'fa-upload',
              'level' => 'primary-label',
              'button-class' => 'btn-success'),
        array('title' => $langCreateDoc,
              'url' => "{$base_url}new=true&uploadPath=$curDirPath",
              'icon' => 'fa-file',
              'level' => 'primary'),
        array('title' => $langCreateDir,
              'url' => "{$base_url}createDir=$curDirPath",
              'icon' => 'fa-folder',
              'level' => 'primary'),
        array('title' => $langExternalFile,
              'url' => "{$base_url}upload=true&uploadPath=$curDirPath&amp;ext=true",
              'icon' => 'fa-link'),
        array('title' => $langQuotaBar,
              'url' => "{$base_url}showQuota=true",
              'icon' => 'fa-pie-chart'),
        array('title' => $langMananeDocMentees,
              'url' => "{$base_url}settingsUsersdoc=true",
              'icon' => 'fa-file',
              'show' => ($is_editor_mentoring_group or $is_editor_mentoring_program and !defined('MENTORING_MYDOCS')))
        ), false);
} else {
    $data['dialogBox'] = '';
    $data['actionBar'] = action_bar(array(
       array('title' => $langBackPage,
              'button-class' => 'backButtonMentoring',
              'url' => "{$urlAppend}modules/mentoring/programs/group/group_space.php?space_group_id=".getInDirectReference($program_group_id),
              'icon' => 'fa-chevron-left',
              'level' => 'primary-label')
        ), false);
}

if (count($data['fileInfo'])) {
    $download_path = empty($curDirPath) ? '/' : $curDirPath;
    $data['downloadPath'] = (!$is_in_tinymce and $uid) ? ("{$base_url}download=" . getIndirectReference($download_path)) : '';
} else {
    $data['downloadPath'] = '';
}
$data['backUrl'] = isset($backUrl)? $backUrl: documentBackLink($curDirPath);
if (defined('SAVED_MENTORING_CODE')) {
    $mentoring_program_code = SAVED_MENTORING_CODE;
    $mentoring_program_id = SAVED_MENTORING_ID;
}
add_units_navigation(true);

$data['can_upload_mentoring'] = $can_upload_mentoring;
$data['uploading_as_user'] = $uploading_as_user;

if(!defined('MENTORING_MYDOCS') and !defined('MENTORING_COMMON_DOCUMENTS')){
   $data['group_id'] = $program_group_id; 
   $data['is_group_doc'] = 1;

   $checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                WHERE id = ?d 
                                                AND mentoring_program_id = ?d",$program_group_id,$mentoring_program_id)->common;

    if($checkIsCommon == 1){
        $data['isCommonGroup'] = 1;
    }else{
        $data['isCommonGroup'] = 0;
    }


}else{
    $data['is_group_doc'] = 0;
}


view('modules.mentoring.programs.group.document.index', $data);


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
    return '<a class="Neutral-900-cl TextBold text-decoration-none" href="' . $base_url . 'openDir=' . $path .
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
            $row = Database::get()->querySingle("SELECT filename FROM mentoring_document
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
    global $base_url, $curDirPath;
    
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
             FROM mentoring_document WHERE $group_sql AND
                  path LIKE ?s AND path NOT LIKE ?s AND filename REGEXP ?s",
        $prefix, $suffix, $uploadPath . '/%', $uploadPath . '/%/%',
        preg_quote($prefix) . '[0-9]+' . preg_quote($suffix))->newPageId;
    return $prefix . $newId . $suffix;
}


function mentoring_hasMetaData($filename, $basedir, $group_sql) {
    $xml = $filename . ".xml";
    $real_filename = $basedir . str_replace('/..', '', q($xml));
    $result = Database::get()->querySingle("SELECT * FROM mentoring_document WHERE $group_sql AND path = ?s", $xml);
    if (file_exists($real_filename) && $result && $result->format == ".meta") {
        return true;
    } else {
        return false;
    }
}


function metaRenameDomDocument($xmlFilename, $newEntry) {

    if (!file_exists($xmlFilename))
        return;

    $sxe = simplexml_load_file($xmlFilename);
    if ($sxe === false)
        return;

    $sxe->general->identifier->entry = $newEntry;

    $dom_sxe = dom_import_simplexml($sxe);
    if (!$dom_sxe)
        return;

    $dom = new DOMDocument('1.0');
    $dom_sxe = $dom->importNode($dom_sxe, true);
    $dom_sxe = $dom->appendChild($dom_sxe);
    $dom->formatOutput = true;
    $dom->save($xmlFilename);
}

