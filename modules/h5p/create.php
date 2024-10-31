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
$require_help = TRUE;
$helpTopic = 'h5p';
$helpSubTopic = 'h5p_objects';

require_once '../../include/baseTheme.php';
require_once 'classes/H5PFactory.php';

$backUrl = $urlAppend . 'modules/h5p/index.php?course=' . $course_code;

$toolName = $langCreate;
$navigation[] = ['url' => $backUrl, 'name' => $langH5p];

// h5p variables
$factory = new H5PFactory();
$jsCacheBuster = "?ver=" . CACHE_SUFFIX;

if (isset($_POST['h5paction']) && $_POST['h5paction'] === 'create') {
    if (isset($_POST['cancel'])) {
        redirect($backUrl);
    }
    if (!isset($_POST['h5pcorecommonpath'])) {
        redirect($backUrl);
    }
    // init core
    $coreCommonPath = $_POST['h5pcorecommonpath'];
    $core = new H5PCore($factory->getFramework(), $webDir . '/' . $coreCommonPath, $urlServer . $coreCommonPath, 'en', FALSE);

    // save h5p data
    $id = saveContent((object)$_POST);

    Session::flash('message',$langH5pSaveSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect($backUrl);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // init core and validator
    $coreCommonPath = 'courses/' . $course_code . '/h5p/content/' . $id . '/workspace';
    $core = new H5PCore($factory->getFramework(), $webDir . '/' . $coreCommonPath, $urlServer . $coreCommonPath, 'en', FALSE);
    $contentValidator = new H5PContentValidator($factory->getFramework(), $core);

    $oldcontent = $core->loadContent($id);

    if ($oldcontent === null) {
        redirect($backUrl);
    }
    $library = H5PCore::libraryToString($oldcontent['library']);

    $params = $core->filterParameters($oldcontent);
    $maincontentdata = ['params' => json_decode($params)];

    if (isset($oldcontent['metadata'])) {
        $maincontentdata['metadata'] = $oldcontent['metadata'];
    }

    // prepare editor content
    if (file_exists($webDir . '/' . $coreCommonPath . '/content')) {
        recurse_copy($webDir . '/' . $coreCommonPath . '/content', $webDir . '/' . $coreCommonPath . '/editor');
    }
    if (file_exists($webDir . '/' . $coreCommonPath . '/editor/content.json')) {
        unlink($webDir . '/' . $coreCommonPath . '/editor/content.json');
    }
} else {
    $id = "";
    if (!isset($_GET['library'])) {
        redirect($backUrl);
    }

    // init core and validator
    $coreCommonPath = 'courses/' . $course_code . '/h5p';
    $core = new H5PCore($factory->getFramework(), $webDir . '/' . $coreCommonPath, $urlServer . $coreCommonPath, 'en', FALSE);
    $contentValidator = new H5PContentValidator($factory->getFramework(), $core);

    $library = $_GET['library'];
    $maincontentdata = ['params' => (object)[]]; // {&quot;params&quot;:{}}
}

// h5p editor form
$tool_content .= "
        <div class='col-12'>
            <form id='coolh5peditor' autocomplete='off' action='{$urlAppend}modules/h5p/create.php?course=$course_code' method='post' accept-charset='utf-8' class='mform'>
                <div style='display: none;'>
                    <input name='library' type='hidden' value='$library' />
                    <input name='h5plibrary' type='hidden' value='$library' />
                    <input name='h5pparams' type='hidden' value='" . q(json_encode($maincontentdata, true)) . "' />
                    <input name='h5paction' type='hidden' value='' />
                    <input name='id' type='hidden' value='$id' />
                    <input name='h5pcorecommonpath' type='hidden' value='$coreCommonPath' />
                </div>

                <div class='h5p-editor-wrapper' id='h5p-editor-region'>
                    <div class='h5p-editor'>
                        <span class='loading-icon icon-no-margin'><i class='icon fa fa-circle-o-notch fa-spin fa-fw' title='$langLoading' aria-label='$langLoading'></i></span>
                    </div>
                </div>

                <div class='h5p-editor-upload'></div>

                " . addActionButtons() . "

            </form>
        </div>\n";

$head_content .= "
    <script type='text/javascript'>
        var H5PIntegration = " . json_encode(getH5pIntegrationObject(), JSON_PRETTY_PRINT) . ";

        $(document).ready(function() {
            const editorwrapper = $('#h5p-editor-region');
            const editor = $('.h5p-editor');
            const mform = editor.closest('form');
            const editorupload = $('h5p-editor-upload');
            const h5plibrary = $('input[name=\"h5plibrary\"]');
            const h5pparams = $('input[name=\"h5pparams\"]');
            const inputname = $('input[name=\"name\"]');
            const h5paction = $('input[name=\"h5paction\"]');

            // Cancel validation and submission of form if clicking cancel button.
            const cancelSubmitCallback = function(button) {
                return button.is('[name=\"cancel\"]');
            };

            h5paction.val('create');

            H5PEditor.init(
                mform,
                h5paction,
                editorupload,
                editorwrapper,
                editor,
                h5plibrary,
                h5pparams,
                '',
                inputname,
                cancelSubmitCallback
            );
            document.querySelector('#h5p-editor-region iframe').setAttribute('name', 'h5p-editor');
        });
    </script>\n";

draw($tool_content, 2, null, $head_content);

function addActionButtons(): string {
    global $langSave, $langCancel, $backUrl;

    return "
        <div id='fgroup_id_buttonar' class='form-group row fitem femptylabel' data-groupname='buttonar'>
            <div class='col-md-3 col-form-label d-flex pb-0 pr-md-0'>
                <div class='form-label-addon d-flex align-items-center align-self-start'></div>
            </div>

            <div class='col-md-9 form-inline felement mt-5' data-fieldtype='group'>
                <fieldset class='w-100 m-0 p-0 border-0'>
                    <legend class='sr-only'></legend>
                    <div class='d-flex flex-wrap align-items-center'>
                        <div class='form-group fitem'>
                            <span data-fieldtype='submit'>
                                <input type='submit'
                                       class='btn submitAdminBtn'
                                       name='submitbutton'
                                       id='id_submitbutton'
                                       value='$langSave' >
                            </span>
                            <div class='form-control-feedback invalid-feedback' id='id_error_submitbutton'></div>
                        </div>

                        <div class='form-group fitem btn-cancel' >
                            <span data-fieldtype='submit'>
                                <input type='submit'
                                       class='btn cancelAdminBtn ms-2'
                                       name='cancel'
                                       id='id_cancel'
                                       value='$langCancel'
                                       onclick='window.location.href=\"$backUrl\"; return true;' >
                            </span>
                            <div class='form-control-feedback invalid-feedback' id='id_error_cancel'></div>
                        </div>
                    </div>
                </fieldset>
                <div class='form-control-feedback invalid-feedback' id='fgroup_id_error_buttonar'></div>
            </div>
        </div>
    ";
}

function getH5pIntegrationObject(): array {
    global $head_content, $urlServer, $urlAppend, $webDir, $jsCacheBuster, $language, $contentValidator, $coreCommonPath;

    $settings = getCoreAssets();

    // Use js and styles from core
    $assets = [
        'css' => $settings['core']['styles'],
        'js' => $settings['core']['scripts']
    ];

    $jsH5pEditor = "js/h5p-editor/";

    // Add editor styles
    foreach (H5peditor::$styles as $style) {
        $assets['css'][] = $urlServer . $jsH5pEditor . $style . $jsCacheBuster;
    }

    // Add editor JavaScript
    foreach (H5peditor::$scripts as $script) {
        // We do not want the creator of the iframe inside the iframe
        if ($script !== 'scripts/h5peditor-editor.js') {
            $assets['js'][] = $urlServer . $jsH5pEditor . $script . $jsCacheBuster;
        }
    }

    // Add JavaScript with library framework integration (editor part)
    $head_content .= "<script type='text/javascript' src='" . $urlAppend . $jsH5pEditor . 'scripts/h5peditor-editor.js' . $jsCacheBuster ."'></script>\n";
    $head_content .= "<script type='text/javascript' src='" . $urlAppend . $jsH5pEditor . 'scripts/h5peditor-init.js' . $jsCacheBuster ."'></script>\n";

    // Load editor translations
    $languagescript = $webDir . "/" . $jsH5pEditor . "language/" . $language . ".js";
    $lfile = $language;
    if (!file_exists($languagescript)) {
        $lfile = 'en';
    }
    $head_content .= "<script type='text/javascript' src='" . $urlAppend . $jsH5pEditor . 'language/' . $lfile . '.js' . $jsCacheBuster ."'></script>\n";

    // Editor settings
    $editorajaxtoken = H5PCore::createToken(EditorAjax::EDITOR_AJAX_TOKEN);
    $_SESSION[$editorajaxtoken . '.h5pcorecommonpath'] = $coreCommonPath;
    $settings['editor'] = [
        'filesPath' => $urlServer . $coreCommonPath . '/editor',
        'fileIcon' => [
            'path' => $urlServer . $jsH5pEditor . 'images/binary-file.png',
            'width' => 50,
            'height' => 50,
        ],
        'ajaxPath' =>  $urlServer . "modules/h5p/ajax.php?token={$editorajaxtoken}&action=",
        'libraryUrl' => $urlServer . $jsH5pEditor,
        'copyrightSemantics' => $contentValidator->getCopyrightSemantics(),
        'metadataSemantics' => $contentValidator->getMetadataSemantics(),
        'assets' => $assets,
        'apiVersion' => H5PCore::$coreApi,
        'language' => $language,
    ];

    return $settings;
}

function getCoreAssets(): array {
    global $head_content, $urlServer, $urlAppend, $core, $jsCacheBuster;

    // get core settings
    $settings = getCoreSettings();
    $settings['core'] = [
        'styles' => [],
        'scripts' => []
    ];
    $settings['loadedJs'] = [];
    $settings['loadedCss'] = [];

    $jsH5pCore = "js/h5p-core/";

    // Add core stylesheets
    foreach ($core::$styles as $style) {
        $settings['core']['styles'][] = $urlServer . $jsH5pCore . $style . $jsCacheBuster;
        $head_content .= "<link rel='stylesheet' href='" . $urlAppend . $jsH5pCore . $style . $jsCacheBuster . "'>\n";
    }

    // Add core javascript
    foreach ($core::$scripts as $script) {
        $settings['core']['scripts'][] = $urlServer . $jsH5pCore . $script . $jsCacheBuster;
        $head_content .= "<script type='text/javascript' src='" . $urlAppend . $jsH5pCore . $script . $jsCacheBuster ."'></script>\n";
    }

    return $settings;
}

function getCoreSettings(): array {
    global $urlServer, $uid, $core, $jsCacheBuster;

    // Generate AJAX paths.
    $ajaxpaths = [];
    $ajaxpaths['xAPIResult'] = '';
    $ajaxpaths['contentUserData'] = '';

    // user info
    $usersettings = [];
    if ($uid) {
        $userdata = Database::get()->querySingle("SELECT username, email FROM user WHERE id = ?d", $uid);
        $usersettings = ['name' => $userdata->username, 'mail' => $userdata->email];
    }

    return array(
        'baseUrl' => $urlServer,
        'url' => $urlServer . "courses/h5p", // TODO: check
        'urlLibraries' => $urlServer . "courses/h5p/libraries",
        'postUserStatistics' => false,
        'ajax' => $ajaxpaths,
        'saveFreq' => false,
        'siteUrl' => $urlServer,
        'l10n' => array('H5P' => $core->getLocalization()),
        'user' => $usersettings,
        'hubIsEnabled' => true,
        'reportingIsEnabled' => false,
        'crossorigin' => null,
        'libraryConfig' => $core->h5pF->getLibraryConfig(),
        'pluginCacheBuster' => $jsCacheBuster,
        'libraryUrl' => $urlServer . "js/h5p-core/js",
    );
}

/**
 * Create or Update H5P content from the submitted form data.
 *
 * @param stdClass $data Form data to create or update H5P content.
 *
 * @return int The id of the created or updated content.
 * @throws Exception
 */
function saveContent(stdClass $data): int {
    global $factory, $core, $webDir, $course_code, $course_id, $coreCommonPath;

    $framework = $factory->getFramework();
    $editor = $factory->getH5PEditor();

    // The H5P libraries expect data->id as the H5P content id
    // The method H5PCore::saveContent throws an error if id is set but empty
    if (empty($data->id)) {
        unset($data->id);
    }

    if (empty($data->h5pparams)) {
        throw new Exception("Missing H5P params");
    }

    if (!isset($data->h5plibrary)) {
        throw new Exception("Missing H5P library");
    }

    // Prepare library data to be saved and current parameters
    $data->params = $data->h5pparams;
    $data->library = H5PCore::libraryFromString($data->h5plibrary);
    $data->library['libraryId'] = $framework->getLibraryId($data->library['machineName'], $data->library['majorVersion'], $data->library['minorVersion']);
    $params = json_decode($data->params);

    $modified = false;
    if (empty($params->metadata)) {
        $params->metadata = new stdClass();
        $modified = true;
    }
    if (empty($params->metadata->title)) {
        // Use a default string if not available.
        $params->metadata->title = 'Untitled';
        $modified = true;
    }
    if (!isset($data->title)) {
        $data->title = $params->metadata->title;
    }
    if ($modified) {
        $data->params = json_encode($params);
    }

    // Save content
    $data->id = $core->saveContent((array)$data);

    // Move any uploaded images or files. Determine content dependencies.
    $editor->processParameters($data->id, $data->library, $params->params);
    // Revert params because editor processParameters modifies them in a not predictable way.
    $params = json_decode($data->params);
    $workspacePath = $webDir . "/courses/" . $course_code . "/h5p/content/" . $data->id . "/workspace";
    $contentJsonPath = $workspacePath . "/content";
    if (!file_exists($contentJsonPath)) {
        mkdir($contentJsonPath, 0775, true);
    }

    $tmpEditorPath = $webDir . '/' . $coreCommonPath . '/editor';
    if (file_exists($tmpEditorPath)) {
        recurse_copy($tmpEditorPath, $contentJsonPath);
        H5PCore::deleteFileTree($tmpEditorPath);
    }

    // Generates filtered params
    $contentarray = $core->loadContent($data->id);
    $contentarray['title'] = $data->title;
    $fparams = $core->filterParameters($contentarray);

    // create proper content.json file on disk with params
    file_put_contents($contentJsonPath . "/content.json", $fparams);

    // Calculate dependencies by validating and filtering against main library semantics
    $vdeps = array();
    $validator = new H5PContentValidator($framework, $core);
    $vparams = (object) array(
        'library' => H5PCore::libraryToString($data->library),
        'params' => $params->params
    );
    if (!empty($vparams->params)) {
        $validator->validateLibrary($vparams, (object) array('options' => array($vparams->library)));
        $vdeps = $validator->getDependencies();
    }

    // create proper h5p.json file on disk
    $h5p = new stdClass();
    $h5p->title = $data->title;
    $h5p->mainLibrary = $data->library['machineName'];
    $h5p->preloadedDependencies = array();
    $vdepsAdded = array();
    foreach ($vdeps as $dependency) {
        if ($dependency['type'] !== "editor" && !in_array($dependency['library']['machineName'], $vdepsAdded)) {
            $h5p->preloadedDependencies[] = (object)array(
                'machineName' => $dependency['library']['machineName'],
                'majorVersion' => $dependency['library']['majorVersion'],
                'minorVersion' => $dependency['library']['minorVersion']
            );
            $vdepsAdded[] = $dependency['library']['machineName'];
        }
    }
    file_put_contents($workspacePath . "/h5p.json", json_encode($h5p));

    // handle package title
    Database::get()->query("UPDATE h5p_content SET title = ?s WHERE id = ?d AND course_id = ?d", $data->title, $data->id, $course_id);

    return $data->id;
}

function handleUpload($contentTmpPath, $contentJsonPath, $file) {
    if (file_exists($contentTmpPath . $file->path)) {
        $targetDir = dirname($contentJsonPath . "/" . $file->path);
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0775, true);
        }
        copy($contentTmpPath . $file->path, $contentJsonPath . "/" . $file->path);
    }
}
