<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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


/* ===========================================================================
  lib.wikidisplay.php
  @last update: 15-05-2007 by Thanos Kyritsis
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

  based on Claroline version 1.7.9 licensed under GPL
  copyright (c) 2001, 2007 Universite catholique de Louvain (UCL)

  original file: lib.wikidisplay Revision: 1.21.2.2

  Claroline authors: Frederic Minne <zefredz@gmail.com>
  ==============================================================================
  @Description:

  @Comments:

  @todo:
  ==============================================================================
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
    global $langPreview, $langCancel, $langSave, $langWikiMainPage, $langNote, $course_code;

    // create script
    $script = ( is_null($script) ) ? $_SERVER['SCRIPT_NAME'] . "?course=$course_code" : $script;
    $script = add_request_variable_to_url($script, "title", rawurlencode($title));

    // set display title
    $localtitle = ( $title === '__MainPage__' ) ? $langWikiMainPage : $title;

    $out = "
            <h1>$localtitle</h1>
            <br>
            <div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='POST' action='$script' name='editform' id='editform'>";
  
    if ($showWikiToolBar === true) {
        $wikiarea = new Wiki2xhtmlArea($content, 'wiki_content', 80, 15, null);
        $out .= "<div class='form-group'><div class='col-xs-12'>". $wikiarea->toHTML() . "</div></div>";
    } else { // Does it ever gets in here?
        $out .= "<label>Texte :</label><br>
                <textarea class='form-control' name='wiki_content' id='wiki_content'vcols='80' rows='15' wrap='virtual'>
                    ". q($content) ."
                </textarea>";
    }
	
    //notes
    $out .= "<div class='form-group'>
                <label for='changelog' class='col-sm-2 control-label'>$langNote:</label>
                <div class='col-sm-10'>    
                    <input class='form-control' type='text'  id='changelog' value='".q($changelog)."' name='changelog' size='70' maxlength='200' wrap='virtual'>
                </div>        
            </div>";
    //end notes

    $out .= '<div style="padding:10px;">' . "\n";

    $out .= '<input type="hidden" name="wikiId" value="'
            . $wikiId
            . '" />' . "\n"
    ;

    $out .= '<input type="hidden" name="versionId" value="'
            . $versionId
            . '" />' . "\n"
    ;

    $out .= '<input class="btn btn-primary" type="submit" name="action[preview]" value="'
            . $langPreview . '" />' . "\n"
    ;

    if (!$forcePreview) {
        $out .= '<input class="btn btn-primary" type="submit" name="action[save]" value="'
                . $langSave . '" />' . "\n"
        ;
    }

    $location = add_request_variable_to_url($script, "wikiId", $wikiId);
    $location = add_request_variable_to_url($location, "action", "show");

    $out .= "   <a class='btn btn-default' href='$location'>$langCancel</a>
            </div>
        </form>
    </div>";

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
                <h1 class='wikiTitle'>$langWikiPreviewTitle$title</h1>
            </div>
            <div class='alert alert-danger'>$langWikiPreviewWarning</div><br>
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

    $out = "<br>
            <div><form method='POST' action='$script' name='previewform' id='previewform'>
             <input type='hidden' name='wiki_content' value='". q($content) . "'>
             <input type='hidden' name='changelog' value='". q($changelog) . "'>
             <input type='hidden' name='title' value='". q($title) ."'>
             <input type='hidden' name='wikiId' value='$wikiId'>
             <input class='btn btn-primary' type='submit' name='action[save]' value='$langSave'>
             <input class='btn btn-primary' type='submit' name='action[edit]' value='$langEdit'>
            ";

    $location = add_request_variable_to_url($script, "wikiId", $wikiId);
    $location = add_request_variable_to_url($location, "title", $title);
    $location = add_request_variable_to_url($location, "action", "show");

    $out .= "<a class='btn btn-default' href='$location'>$langCancel</a>";

    $out .= "</form></div>";

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
    global $langWikiDescriptionForm, $langWikiDescriptionFormText, $langWikiTitle
    , $langWikiDescription, $langWikiAccessControl, $langWikiAccessControlText
    , $langWikiCourseMembers, $langWikiGroupMembers, $langWikiOtherUsers
    , $langWikiOtherUsersText, $langWikiReadPrivilege, $langWikiEditPrivilege
    , $langWikiCreatePrivilege, $langCancel, $langSave, $langBack
    , $course_code;

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

    $form = action_bar(array(
        array('title' => $langBack,
              'url' => "$_SERVER[SCRIPT_NAME]'?course=$course_code",
              'icon' => 'fa-reply',
              'level' => 'primary-label',)
    ));
                
    $form .= "<div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='POST' id='wikiProperties' action='$script'>
                    <fieldset>
                        <input type='hidden' name='wikiId' value='$wikiId'>
                        <!-- groupId = 0 if course wiki, != 0 if group_wiki  -->
                        <input type='hidden' name='gid' value='$groupId'>                             
                        <div class='form-group".(Session::getError('title') ? " has-error" : "")."'>
                            <label for='title' class='col-sm-2 control-label'>$langWikiTitle:</label>
                            <div class='col-sm-10'>
                                <input name='title' type='text' class='form-control' id='wikiTitle' value='".q($title) ."' placeholder='$langWikiTitle'>
                                <span class='help-block'>".Session::getError('title')."</span>    
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='wikiDesc' class='col-sm-2 control-label'>".$langWikiDescription.":</label>
                            <div class='col-sm-10'>
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
                            <div class='form-group'>
                                <div class='col-sm-10 col-sm-offset-2'>
                                    <input class='btn btn-primary' type='submit' name='action[exEdit]' value='$langSave'>
                                    <a class='btn btn-default' href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langCancel</a>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>";

    return $form;
}
