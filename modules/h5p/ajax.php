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

$require_login = true;
$require_current_course = true;
require_once '../../include/baseTheme.php';
require_once 'classes/H5PFactory.php';

header('Content-Type: application/json; charset=UTF-8');

// require action param
if (!isset($_GET['action'])) {
    throw new Exception("Unhandled AJAX");
}

// init editor
$factory = new H5PFactory();
$editor = $factory->getH5PEditor();

switch ($_GET['action']) {
    // Load list of libraries or details for library.
    case H5PEditorEndpoints::LIBRARIES:
        // Get parameters.
        $name = (isset($_GET['machineName'])) ? $_GET['machineName'] : '';
        $major = (isset($_GET['majorVersion'])) ? intval($_GET['majorVersion']) : 0;
        $minor = (isset($_GET['minorVersion'])) ? intval($_GET['minorVersion']) : 0;
        $language = (isset($_GET['language'])) ? $_GET['language'] : null;

        if (!empty($name)) {
            $editor->ajax->action(H5PEditorEndpoints::SINGLE_LIBRARY, $name, $major, $minor, $language, '', '', $language);
        } else {
            $editor->ajax->action(H5PEditorEndpoints::LIBRARIES);
        }

        break;

    // Load content type cache list to display available libraries in hub.
    case H5PEditorEndpoints::CONTENT_TYPE_CACHE:
        $editor->ajax->action(H5PEditorEndpoints::CONTENT_TYPE_CACHE);
        break;

    // Get the $language libraries translations.
    case H5PEditorEndpoints::TRANSLATIONS:
        $language = (isset($_GET['language'])) ? $_GET['language'] : null;
        $editor->ajax->action(H5PEditorEndpoints::TRANSLATIONS, $language);
        break;

    // Handle file upload through the editor.
    // This endpoint needs a token that only users with H5P editor access could get.
    case H5PEditorEndpoints::FILES:
        $token = $_GET['token'];
        $contentid = $_POST['contentId'];

        // init editor
        $coreCommonPath = $_SESSION[$token . '.h5pcorecommonpath'];
        $core = new H5PCore($factory->getFramework(), $webDir . '/' . $coreCommonPath, $urlServer . $coreCommonPath, 'en', FALSE);
        $editor = new H5peditor($core, new EditorStorage(), $factory->getH5PEditorAjax());

        $maxsize = getMaxUploadFileSize();
        // Check size of each uploaded file.
        foreach ($_FILES as $uploadedfile) {
            if ($uploadedfile['size'] > $maxsize) {
                $filename = cleanFilename($uploadedfile['name']);
                H5PCore::ajaxError('maxbytesfile error for file: ' . $filename . " and size: " . $uploadedfile['size']);
                return;
            }
        }

        $editor->ajax->action(H5PEditorEndpoints::FILES, $token, $contentid);
        break;

    // do nothing if AJAX action is not handled.
    default:
        throw new Exception("Unhandled AJAX action: " . $_GET['action']);
        break;
}

function getMaxUploadFileSize(): int {
    if (!$filesize = ini_get('upload_max_filesize')) {
        $filesize = '5M';
    }
    $minimumsize = getRealSize($filesize);

    if ($postsize = ini_get('post_max_size')) {
        $postsize = getRealSize($postsize);
        if ($postsize < $minimumsize) {
            $minimumsize = $postsize;
        }
    }

    return $minimumsize;
}

/**
 * Converts numbers like 10M into bytes.
 *
 * @param string $size The size to be converted
 * @return int
 */
function getRealSize(string $size): int {
    if (!$size) {
        return 0;
    }

    static $binaryprefixes = array(
        'K' => 1024 ** 1,
        'k' => 1024 ** 1,
        'M' => 1024 ** 2,
        'm' => 1024 ** 2,
        'G' => 1024 ** 3,
        'g' => 1024 ** 3,
        'T' => 1024 ** 4,
        't' => 1024 ** 4,
        'P' => 1024 ** 5,
        'p' => 1024 ** 5,
    );

    if (preg_match('/^([0-9]+)([KMGTP])/i', $size, $matches)) {
        return $matches[1] * $binaryprefixes[$matches[2]];
    }

    return (int) $size;
}

function cleanFilename(string $val): string {
    $val = preg_replace('~[[:cntrl:]]|[&<>"`\|\':\\\\/]~u', '', $val);
    if ($val === '.' || $val === '..') {
        $val = '';
    }
    return $val;
}
