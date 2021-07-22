<?php
/*
 * ========================================================================
 * Open eClass 3.11 - E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2021  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
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
 *
 * For a full list of contributors, see "credits.txt".
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

    // do nothing if AJAX action is not handled.
    default:
        throw new Exception("Unhandled AJAX");
        break;
}
