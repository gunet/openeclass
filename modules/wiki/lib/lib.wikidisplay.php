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

require_once dirname(__FILE__) . "/class.wiki2xhtmlarea.php";
require_once dirname(__FILE__) . "/class.wikiaccesscontrol.php";
require_once dirname(__FILE__) . "/lib.url.php";

/**
 * Generate wiki editor html code
 * @param int wikiId ID of the Wiki
 * @param string title page title
 * @param string content page content
 * @param string script callback script url
 * @param boolean showWikiToolbar use Wiki toolbar if true
 * @param boolean forcePreview force preview before saving
 *      (ie disable save button)
 * @return string HTML code of the wiki editor
 */
function claro_disp_wiki_editor($wikiId, $title, $versionId
, $content, $changelog = '', $script = null, $showWikiToolBar = true
, $forcePreview = true) {
    global $langPreview, $langCancel, $langSave, $langWikiMainPage, $langNote, $course_code,
           $langWikiSyntaxHelp;

    // create script
    $script = ( is_null($script) ) ? $_SERVER['SCRIPT_NAME'] . "?course=$course_code" : $script;
    $script = add_request_variable_to_url($script, "title", rawurlencode($title));

    // set display title
    $localtitle = ( $title === '__MainPage__' ) ? $langWikiMainPage : $title;

    $out = "
            <h4>$localtitle</h4>
            <br>
            <div class='col-sm-12'><div class='form-wrapper form-edit rounded'>
            <form class='form-horizontal' role='form' method='POST' action='$script' name='editform' id='editform'>";

    if ($showWikiToolBar === true) {
        $wikiarea = new Wiki2xhtmlArea($content, 'wiki_content', 80, 15, null);
        // opens the syntax help modal (see claro_disp_wiki_syntax_help());
        // wiki_preview.js puts the mobile Edit / Preview tabs on this same line
        $out .= "<div class='mt-3 d-flex flex-wrap align-items-stretch justify-content-between gap-2 wiki-editor-actions'><button type='button' class='btn btn-sm btn-outline-primary' "
            . "data-bs-toggle='modal' data-bs-target='#wiki-syntax-help'>"
            . "$langWikiSyntaxHelp <i class='fa-solid fa-circle-question'></i></button></div>";
        $out .= "<div class='form-group mt-3'><div class='col-12'>". $wikiarea->toHTML() . "</div></div>";
    } else { // Does it ever gets in here?
        $out .= "<label for='wiki_content' class='col-sm-6 control-label-notes'>Texte :</label><br>
                <textarea class='form-control' name='wiki_content' id='wiki_content' vcols='80' rows='15' wrap='virtual'>
                    ". q($content) ."
                </textarea>";
    }

    //notes
    $out .= "<div class='form-group mt-3'>
                <label for='changelog' class='col-sm-6 control-label-notes'>$langNote:</label>
                <div class='col-sm-12'>
                    <input class='form-control' type='text' id='changelog' value='".q($changelog)."' name='changelog' size='70' maxlength='200' wrap='virtual'>
                </div>
            </div>";
    //end notes

    $out .= '<div class="d-flex gap-2 mt-4">' . "\n";

    $out .= '<input type="hidden" name="wikiId" value="'
            . $wikiId
            . '" />' . "\n"
    ;

    $out .= '<input type="hidden" name="versionId" value="'
            . $versionId
            . '" />' . "\n"
    ;

    $out .= generate_csrf_token_form_field() . "\n";

    $out .= '<input class="btn submitAdminBtn" type="submit" name="action[preview]" value="'
            . $langPreview . '" />' . "\n"
    ;

    if (!$forcePreview) {
        $out .= '<input class="btn submitAdminBtn" type="submit" name="action[save]" value="'
                . $langSave . '" />' . "\n"
        ;
    }

    $location = add_request_variable_to_url($script, "wikiId", $wikiId);
    $location = add_request_variable_to_url($location, "action", "show");

    $out .= "   <a class='btn cancelAdminBtn' href='$location'>$langCancel</a>
            </div>
        </form>
    </div></div>";

    return $out;
}

/**
 * Generate html code of the wiki page preview
 * @param Wiki2xhtmlRenderer wikiRenderer rendering engine
 * @param string title page title
 * @param string content page content
 * @return string html code of the preview pannel
 */
function claro_disp_wiki_preview(&$wikiRenderer, $title, $content = '') {
    global $langWikiContentEmpty, $langWikiPreviewTitle
    , $langWikiPreviewWarning, $langWikiMainPage;

    if ($title === '__MainPage__') {
        $title = $langWikiMainPage;
    }

    $out = "<div id='preview' class='wikiTitle'>
                <h4 class='wikiTitle'>$langWikiPreviewTitle$title</h4>
            </div>
            <div class='col-sm-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langWikiPreviewWarning</span></div></div><br>
            <div class='wiki2xhtml'>";

    if ($content != '') {
        $out .= $wikiRenderer->render($content);
    } else {
        $out .= $langWikiContentEmpty;
    }

    $out .= "</div>\n";

    // $out .= "</div>\n";

    return $out;
}

/**
 * Generate html code ofthe preview panel button bar
 * @param int wikiId ID of the Wiki
 * @param string title page title
 * @param string content page content
 * @param string script callback script url
 * @return string html code of the preview pannel button bar
 */
function claro_disp_wiki_preview_buttons($wikiId, $title, $content, $changelog = '', $script = null) {
    global $langSave, $langEdit, $langCancel, $course_code;

    $script = ( is_null($script) ) ? $_SERVER['SCRIPT_NAME'] . "?course=$course_code" : $script;

    $out = "<div>
            <form method='POST' action='$script' name='previewform' id='previewform'>
             <input type='hidden' name='wiki_content' value='". q($content) . "'>
             <input type='hidden' name='changelog' value='". q($changelog) . "'>
             <input type='hidden' name='title' value='". q($title) ."'>
             <input type='hidden' name='wikiId' value='$wikiId'>
             <div class='form-group mt-4'>
                 <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                     <input class='btn submitAdminBtn' type='submit' name='action[save]' value='$langSave'>
                     <input class='btn submitAdminBtn' type='submit' name='action[edit]' value='$langEdit'>";

    $location = add_request_variable_to_url($script, "wikiId", $wikiId);
    $location = add_request_variable_to_url($location, "title", $title);
    $location = add_request_variable_to_url($location, "action", "show");

    $out .= "<a class='btn cancelAdminBtn' href='$location'>$langCancel</a>";

    $out .= "</div></div>";
    $out .= "</form>";
    $out .= "</div>";

    return $out;
}

/**
 * Generate html code of the wiki syntax cheat sheet modal
 *
 * Opened by the "Syntax help" button above the editor (claro_disp_wiki_editor()).
 * The entries mirror the buttons built in Wiki2xhtmlArea::getToolbar() and
 * reuse the $wiki_toolbar labels, so the help and the toolbar cannot disagree.
 * Each example can be clicked to insert it at the cursor position
 * (see modules/wiki/lib/javascript/wiki_preview.js).
 * @return string html code of the syntax help modal
 */
function claro_disp_wiki_syntax_help() {
    global $wiki_toolbar, $langWikiSyntaxHelp, $langWikiSyntaxExample, $langWikiSyntaxInsert;

    // [key in $wiki_toolbar, example snippet]
    $entries = array(
        array('H1', '!!!! Heading'),
        array('H2', '!!! Heading'),
        array('H3', '!! Heading'),
        array('H4', '! Heading'),
        array('Strongemphasis', "'''bold'''"),
        array('Emphasis', "''italic''"),
        array('Inserted', '__underlined__'),
        array('Deleted', '--struck through--'),
        array('Inlinequote', '{{quote}}'),
        array('Code', '@@code@@'),
        array('Unorderedlist', "* item"),
        array('Orderedlist', '# item'),
        array('Blockquote', '> quote'),
        array('Preformatedtext', ' preformatted'),
        array('Linebreak', '%%%'),
        array('Link', '[label|https://example.com]'),
        array('Externalimage', '((https://example.com/image.png))'),
    );

    $out = "<div class='modal fade' id='wiki-syntax-help' tabindex='-1' aria-labelledby='wiki-syntax-help-title' aria-hidden='true'>"
        . "<div class='modal-dialog modal-dialog-centered modal-dialog-scrollable'><div class='modal-content'>"
        . "<div class='modal-header'><h5 class='modal-title' id='wiki-syntax-help-title'>"
        . "<i class='fa-solid fa-circle-question'></i> $langWikiSyntaxHelp</h5>"
        . "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button></div>"
        . "<div class='modal-body'>"
        . "<div class='table-responsive'><table class='table table-sm small align-middle mb-0'>"
        . "<thead><tr><th></th><th>$langWikiSyntaxExample</th><th></th></tr></thead><tbody>";

    foreach ($entries as $entry) {
        list($key, $example) = $entry;
        $label = isset($wiki_toolbar[$key]) ? $wiki_toolbar[$key] : $key;
        $out .= "<tr><td class='pe-3'>" . q($label) . "</td>"
            . "<td class='pe-3'><code>" . q($example) . "</code></td>"
            . "<td class='text-end'><button type='button' class='btn btn-sm btn-outline-primary py-0 px-2' "
            . "data-wiki-insert='" . q($example) . "'>$langWikiSyntaxInsert</button></td></tr>";
    }

    $out .= "</tbody></table></div></div></div></div></div>";

    return $out;
}

/**
 * Generate html code of Wiki properties edit form
 * @param int wikiId ID of the wiki
 * @param string title wiki tile
 * @param string desc wiki description
 * @param int groupId id of the group the wiki belongs to
 *      (0 for a course wiki)
 * @param array acl wiki access control list
 * @param string script callback script url
 * @return string html code of the wiki properties form
 */
function claro_disp_wiki_properties_form($wikiId = 0, $title = '', $desc = '', $groupId = 0, $acl = null, $script = null) {

    global $langWikiTitle, $langWikiDescription,
            $langCancel, $langSave, $langBack, $course_code, $urlAppend, $langImgFormsDes, $langForm;

    $title = ( $title != '' ) ? $title : '';

    $desc = ( $desc != '' ) ? $desc : '';

    if (is_null($acl) && $groupId == 0) {
        $acl = WikiAccessControl::defaultCourseWikiACL();
    } elseif (is_null($acl) && $groupId != 0) {
        $acl = WikiAccessControl::defaultGroupWikiACL();
    }

    // process ACL
    $group_read_checked = ( $acl['group_read'] == true ) ? ' checked="checked"' : '';
    $group_edit_checked = ( $acl['group_edit'] == true ) ? ' checked="checked"' : '';
    $group_create_checked = ( $acl['group_create'] == true ) ? ' checked="checked"' : '';
    $course_read_checked = ( $acl['course_read'] == true ) ? ' checked="checked"' : '';
    $course_edit_checked = ( $acl['course_edit'] == true ) ? ' checked="checked"' : '';
    $course_create_checked = ( $acl['course_create'] == true ) ? ' checked="checked"' : '';
    $other_read_checked = ( $acl['other_read'] == true ) ? ' checked="checked"' : '';
    $other_edit_checked = ( $acl['other_edit'] == true ) ? ' checked="checked"' : '';
    $other_create_checked = ( $acl['other_create'] == true ) ? ' checked="checked"' : '';

    $script = ( is_null($script) ) ? $_SERVER['SCRIPT_NAME'] . "?course=$course_code" : $script;

    $form = "<div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
                <form class='form-horizontal' role='form' method='POST' id='wikiProperties' action='$script'>
                    <fieldset>
                        <legend class='mb-0' aria-label='$langForm'></legend>
                        <input type='hidden' name='wikiId' value='$wikiId'>
                        <!-- groupId = 0 if course wiki, != 0 if group_wiki  -->
                        <input type='hidden' name='gid' value='$groupId'>
                        <div class='form-group".(Session::getError('title') ? " has-error" : "")."'>
                            <label for='wikiTitle' class='col-sm-6 control-label-notes'>$langWikiTitle <span class='asterisk Accent-200-cl'>(*)</span></label>
                            <div class='col-sm-12'>
                                <input name='title' type='text' class='form-control' id='wikiTitle' value='".q($title) ."' placeholder='$langWikiTitle'>
                                <span class='help-block Accent-200-cl'>".Session::getError('title')."</span>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='wikiDesc' class='col-sm-6 control-label-notes'>".$langWikiDescription."</label>
                            <div class='col-sm-12'>
                                <textarea class='form-control' id='wikiDesc' name='desc'>" . q($desc) . "</textarea>";

// atkyritsis
// hardwiring
    if ($groupId == 0) {
        $form .= "
                <input type='hidden' name='acl[course_read]' value='on'>
                <input type='hidden' name='acl[course_edit]' value='on'>
                <input type='hidden' name='acl[course_create]' value='on'>
                <input type='hidden' name='acl[other_read]' value='on'>
                <input type='hidden' name='acl[other_edit]' value='off'>
                <input type='hidden' name='acl[other_create]' value='off'>";
    } else {//default values for group wikis
        $form .= "
                <input type='hidden' name='acl[group_read]' value='on'>
                <input type='hidden' name='acl[group_edit]' value='on'>
                <input type='hidden' name='acl[group_create]' value='on'>
                <input type='hidden' name='acl[course_read]' value='on'>
                <input type='hidden' name='acl[course_edit]' value='off'>
                <input type='hidden' name='acl[course_create]' value='off'>
                <input type='hidden' name='acl[other_read]' value='off'>
                <input type='hidden' name='acl[other_edit]' value='off'>
                <input type='hidden' name='acl[other_create]' value='off'>";
    }

// hardwiring over

    $form .= "                  </div>
                            </div>
                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                    <input class='btn submitAdminBtn' type='submit' name='action[exEdit]' value='$langSave'>
                                    <a class='btn cancelAdminBtn' href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langCancel</a>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div></div><div class='d-none d-lg-block'>
                <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
            </div>
            </div>";

    return $form;
}
