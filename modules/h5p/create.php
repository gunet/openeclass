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

$backUrl = $urlAppend . 'modules/h5p/?course=' . $course_code;

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => $backUrl,
        'icon' => 'fa-reply',
        'level' => 'primary-label')
), false);

$toolName = $langCreate;
$navigation[] = ['url' => $backUrl, 'name' => "H5P"];

// h5p variables
$factory = new H5PFactory();
$core = $factory->getCore();
$contentValidator = $factory->getContentValidator();
$jsCacheBuster = "?ver=" . time();
$maincontentdata = ['params' => (object)[]]; // {&quot;params&quot;:{}}

// h5p editor form
$tool_content .= "
    <div class='row'>
        <div class='col-xs-12'>
            <form id='coolh5peditor' autocomplete='off' action='${urlAppend}modules/h5p/create.php?course=$course_code' method='post' accept-charset='utf-8' class='mform'>
                <div style='display: none;'>
                    <input name='library' type='hidden' value='" . $_GET['library'] . "' />
                    <input name='h5plibrary' type='hidden' value='" . $_GET['library'] . "' />
                    <input name='h5pparams' type='hidden' value='" . q(json_encode($maincontentdata, true)) . "' />
                    <input name='h5paction' type='hidden' value='' />
                </div>

                " . addActionButtons() . "

                <div class='h5p-editor-wrapper' id='h5p-editor-region'>
                    <div class='h5p-editor'>
                        <span class='loading-icon icon-no-margin'><i class='icon fa fa-circle-o-notch fa-spin fa-fw' title='$langLoading' aria-label='$langLoading'></i></span>
                    </div>
                </div>
    
                <div class='h5p-editor-upload'></div>
                
                " . addActionButtons() . "
                
            </form>
        </div>
    </div>\n";

$head_content .= "
    <script type='text/javascript'>
        var H5PIntegration = " . json_encode(getH5IntegrationObject(), JSON_PRETTY_PRINT) . ";
    
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
    global $langSave, $langCancel;

    return "
        <div id='fgroup_id_buttonar' class='form-group row fitem femptylabel' data-groupname='buttonar'>
            <div class='col-md-3 col-form-label d-flex pb-0 pr-md-0'>
                <div class='form-label-addon d-flex align-items-center align-self-start'></div>
            </div>
            
            <div class='col-md-9 form-inline align-items-start felement' data-fieldtype='group'>
                <fieldset class='w-100 m-0 p-0 border-0'>
                    <legend class='sr-only'></legend>
                    <div class='d-flex flex-wrap align-items-center'>
                        <div class='form-group fitem'>
                            <span data-fieldtype='submit'>
                                <input type='submit'
                                       class='btn btn-primary'
                                       name='submitbutton'
                                       id='id_submitbutton'
                                       value='$langSave' >
                            </span>
                            <div class='form-control-feedback invalid-feedback' id='id_error_submitbutton'></div>
                        </div>
    
                        <div class='form-group fitem btn-cancel' >
                            <span data-fieldtype='submit'>
                                <input type='submit'
                                       class='btn btn-secondary'
                                       name='cancel'
                                       id='id_cancel'
                                       value='$langCancel'
                                       data-skip-validation='1' data-cancel='1' onclick='skipClientValidation = true; return true;' >
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

function getH5IntegrationObject(): array {
    global $head_content, $urlServer, $urlAppend, $webDir, $jsCacheBuster, $language, $contentValidator;

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
    $settings['editor'] = [
        'filesPath' => $urlServer . "courses/h5p", // TODO: check
        'fileIcon' => [
            'path' => $urlServer . $jsH5pEditor . 'images/binary-file.png',
            'width' => 50,
            'height' => 50,
        ],
        'ajaxPath' =>  $urlServer . "modules/h5p/ajax.php?action=",
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