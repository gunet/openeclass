<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

/*===========================================================================
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
    function claro_disp_wiki_editor( $wikiId, $title, $versionId
        , $content, $script = null, $showWikiToolBar = true
        , $forcePreview = true )
    {
        global $langPreview, $langCancel, $langSave, $langWikiMainPage;

        // create script
        $script = ( is_null( $script ) ) ? $_SERVER['PHP_SELF'] : $script;
        $script = add_request_variable_to_url( $script, "title", rawurlencode($title) );

        // set display title
        $localtitle = ( $title === '__MainPage__' ) ? $langWikiMainPage : $title;

        // display title
        $out = '<div class="wikiTitle">' . "\n";
        $out .= '<h1>'.$localtitle.'</h1>' . "\n";
        $out .= '</div>' . "\n";

                // display editor
        $out .= '<form method="POST" action="'.$script.'"'
            . ' name="editform" id="editform">' . "\n"
            ;

        if ( $showWikiToolBar === true )
        {
            $wikiarea = new Wiki2xhtmlArea( $content, 'wiki_content', 80, 15, null );
            $out .= $wikiarea->toHTML();
        }
        else
        {
            $out .= '<label>Texte :</label><br />' . "\n";
            $out .= '<textarea name="wiki_content" id="wiki_content"'
                 . ' cols="80" rows="15" wrap="virtual">'
                 ;
            $out .= $content;
            $out .= '</textarea>' . "\n";
        }

        $out .= '<div style="padding:10px;">' . "\n";

        $out .= '<input type="hidden" name="wikiId" value="'
            . $wikiId
            . '" />' . "\n"
            ;

        $out .= '<input type="hidden" name="versionId" value="'
            . $versionId
            . '" />' . "\n"
            ;

        $out .= '<input type="submit" name="action[preview]" value="'
            .$langPreview.'" />' . "\n"
            ;

        if( ! $forcePreview )
        {
            $out .= '<input type="submit" name="action[save]" value="'
                .$langSave.'" />' . "\n"
                ;
        }

        $location = add_request_variable_to_url( $script, "wikiId", $wikiId );
        $location = add_request_variable_to_url( $location, "action", "show" );

        $out .= disp_button ($location, $langCancel);

        $out .= '</div>' . "\n";

        $out .= "</form>\n";

        return $out;
    }

    /**
     * Generate html code of the wiki page preview
     * @param Wiki2xhtmlRenderer wikiRenderer rendering engine
     * @param string title page title
     * @param string content page content
     * @return string html code of the preview pannel
     */
    function claro_disp_wiki_preview( &$wikiRenderer, $title, $content = '' )
    {
        global $langWikiContentEmpty,$langWikiPreviewTitle
            ,$langWikiPreviewWarning,$langWikiMainPage;

        $out = "<div id=\"preview\" class=\"wikiTitle\">\n";

        if( $title === '__MainPage__' )
        {
            $title = $langWikiMainPage;
        }

        $title = "<h1 class=\"wikiTitle\">$langWikiPreviewTitle$title</h1>\n";

        $out .= $title;

        $out .= '</div>' . "\n";

        $out .= disp_message_box( '<small>'.$langWikiPreviewWarning.'</small>', "caution" )
            ."<br />". "\n";

        $out .= '<div class="wiki2xhtml">' . "\n";

        if ( $content != '' )
        {
            $out .= $wikiRenderer->render( $content );
        }
        else
        {
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
    function claro_disp_wiki_preview_buttons( $wikiId, $title, $content, $script = null )
    {
        global $langSave,$langEdit,$langCancel;

        $script = ( is_null( $script ) ) ? $_SERVER['PHP_SELF'] : $script;

        $out = '<div style="clear:both;"><form method="POST" action="' . $script
            . '" name="previewform" id="previewform">' . "\n"
            ;
        $out .= '<input type="hidden" name="wiki_content" value="'
            . htmlspecialchars($content) . '" />' . "\n"
            ;

        $out .= '<input type="hidden" name="title" value="'
            . htmlspecialchars($title)
            . '" />' . "\n"
            ;

        $out .= '<input type="hidden" name="wikiId" value="'
            . $wikiId
            . '" />' . "\n"
            ;

        $out .= '<input type="submit" name="action[save]" value="'
            . $langSave.'" />' . "\n"
            ;
        $out .= '<input type="submit" name="action[edit]" value="'
            . $langEdit . '"/>' . "\n"
            ;

        $location = add_request_variable_to_url( $script, "wikiId", $wikiId );
        $location = add_request_variable_to_url( $location, "title", $title );
        $location = add_request_variable_to_url( $location, "action", "show" );

        $out .= disp_button ($location, $langCancel);

        $out .= "</form></div>\n";

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
    function claro_disp_wiki_properties_form( $wikiId = 0
        , $title ='', $desc = '', $groupId = 0, $acl = null
        , $script = null )
    {
        global $langWikiDescriptionForm, $langWikiDescriptionFormText,  $langWikiTitle
            , $langWikiDescription, $langWikiAccessControl, $langWikiAccessControlText
            , $langWikiCourseMembers, $langWikiGroupMembers, $langWikiOtherUsers
            , $langWikiOtherUsersText, $langWikiReadPrivilege, $langWikiEditPrivilege
            , $langWikiCreatePrivilege, $langCancel, $langSave, $langWikiDefaultTitle
            , $langWikiDefaultDescription
            ;

        $title = ( $title != '' ) ? $title : $langWikiDefaultTitle;

        $desc = ( $desc != '' ) ? $desc : $langWikiDefaultDescription;

        if ( is_null ( $acl ) && $groupId == 0 )
        {
            $acl = WikiAccessControl::defaultCourseWikiACL();
        }
        elseif ( is_null ( $acl ) && $groupId != 0 )
        {
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

        $script = ( is_null( $script ) ) ? $_SERVER['PHP_SELF'] : $script;

        $form = '<form method="POST" id="wikiProperties" action="'.$script.'">' . "\n"
              . '      <table width="99%" class="FormData">' . "\n"
              . '      <tbody>' . "\n"
              . '      <tr>' . "\n"
              . '        <th class="left" width="220">&nbsp;</th>' . "\n"
              . '        <td><b>'.$langWikiDescriptionForm.'</b></td>' . "\n"
              . '      </tr>' . "\n"
              . '      <tr>' . "\n"
              . '        <th class="left">'.$langWikiTitle.' :</th>' . "\n"
              . '        <td>' . "\n"
              . '        <input type="hidden" name="wikiId" value="'.$wikiId.'" />' . "\n"
              . '        <!-- groupId = 0 if course wiki, != 0 if group_wiki  -->' . "\n"
              . '        <input type="hidden" name="groupId" value="'.$groupId.'" />' . "\n"
              . '        <input class="FormData_InputText" type="text" name="title" id="wikiTitle" size="53" maxlength="254" value="'.htmlspecialchars($title).'" />' . "\n"
              . '        </td>' . "\n"
              . '      </tr>' . "\n"
              . '      <tr>' . "\n"
              . '        <th class="left">'.$langWikiDescription.' :</th>' . "\n"
              . '        <td>' . "\n"
              . '        <textarea class="FormData_InputText" id="wikiDesc" name="desc" cols="50" rows="5">'.$desc.'</textarea>' . "\n";


              /*
              . '<fieldset style="padding: 10px; margin: 10px;">' . "\n"
              . '<legend>'.$langWikiDescriptionForm.'</legend>' . "\n"
              . '<!-- wikiId = 0 if creation, != 0 if edition  -->' . "\n"
              . '<p style="font-style: italic;">' . $langWikiDescriptionFormText . '</p>' . "\n"
              . '<input type="hidden" name="wikiId" value="'.$wikiId.'" />' . "\n"
              . '<!-- groupId = 0 if course wiki, != 0 if group_wiki  -->' . "\n"
              . '<input type="hidden" name="groupId" value="'.$groupId.'" />' . "\n"
              . '<div style="padding: 5px">' . "\n"

              . '<label for="wikiTitle">' . $langWikiTitle . ' :</label><br />' . "\n"
              . '<input type="text" name="title" id="wikiTitle" size="80" maxlength="254" value="'.htmlspecialchars($title).'" />' . "\n"
              . '</div>' . "\n"
              . '<div style="padding: 5px">' . "\n"
              . '<label for="wikiDesc">'.$langWikiDescription.' :</label><br />' . "\n"
              . '<textarea id="wikiDesc" name="desc" cols="80" rows="10">'.$desc.'</textarea>' . "\n"
              . '</div>' . "\n"
              . '</fieldset>' . "\n"
              */
;// atkyritsis, for the moment we skip wiki ACL
// commenting below and hardwiring the default ACL properties
/*            . '<fieldset id="acl" style="padding: 10px;margin: 10px;">' . "\n"
            . '<legend>' . $langWikiAccessControl . '</legend>' . "\n"
            . '<p style="font-style: italic;">'.$langWikiAccessControlText.'</p>' . "\n"
            . '<table style="text-align: center; padding: 5px;" id="wikiACL">' . "\n"
            . '<tr class="matrixAbs">' . "\n"
            . '<td><!-- empty --></td>' . "\n"
            . '<td>'.$langWikiReadPrivilege.'</td>' . "\n"
            . '<td>'.$langWikiEditPrivilege.'</td>' . "\n"
            . '<td>'.$langWikiCreatePrivilege.'</td>' . "\n"
            . '</tr>' . "\n"
            . '<tr>' . "\n"
            . '<td class="matrixOrd">'.$langWikiCourseMembers.'</td>' . "\n"
            . '<td><input type="checkbox" onclick="updateBoxes(\'course\',\'read\');" id="course_read" name="acl[course_read]"'.$course_read_checked.' /></td>' . "\n"
            . '<td><input type="checkbox" onclick="updateBoxes(\'course\',\'edit\');" id="course_edit" name="acl[course_edit]"'.$course_edit_checked.' /></td>' . "\n"
            . '<td><input type="checkbox" onclick="updateBoxes(\'course\',\'create\');" id="course_create" name="acl[course_create]"'.$course_create_checked.' /></td>' . "\n"
            . '</tr>' . "\n"
            ;

        if ( $groupId != 0 )
        {
            $form .= '<!-- group acl row hidden if groupId == 0, set all to false -->' . "\n"
                . '<tr>' . "\n"
                . '<td class="matrixOrd">'.$langWikiGroupMembers.'</td>' . "\n"
                . '<td><input type="checkbox" onclick="updateBoxes(\'group\',\'read\');" id="group_read" name="acl[group_read]"'.$group_read_checked.' /></td>' . "\n"
                . '<td><input type="checkbox" onclick="updateBoxes(\'group\',\'edit\');" id="group_edit" name="acl[group_edit]"'.$group_edit_checked.' /></td>' . "\n"
                . '<td><input type="checkbox" onclick="updateBoxes(\'group\',\'create\');" id="group_create" name="acl[group_create]"'.$group_create_checked.' /></td>' . "\n"
                . '</tr>' . "\n"
                ;
        }

        $form .= '<tr>' . "\n"
            . '<td class="matrixOrd">'.$langWikiOtherUsers.'</td>' . "\n"
            . '<td><input type="checkbox" onclick="updateBoxes(\'other\',\'read\');" id="other_read" name="acl[other_read]"'.$other_read_checked.' /></td>' . "\n"
            . '<td><input type="checkbox" onclick="updateBoxes(\'other\',\'edit\');" id="other_edit" name="acl[other_edit]"'.$other_edit_checked.' /></td>' . "\n"
            . '<td><input type="checkbox" onclick="updateBoxes(\'other\',\'create\');" id="other_create" name="acl[other_create]"'.$other_create_checked.' /></td>' . "\n"
            . '</tr>' . "\n"
            . '</table>' . "\n"
            . '<p style="font-style: italic;">'.$langWikiOtherUsersText.'</p>' . "\n"
            . '</fieldset>' . "\n"
            ;*/
// atkyritsis
// hardwiring
		$form .= '        <input type="hidden" name="acl[course_read]" value="on" />' ."\n";
		$form .= '        <input type="hidden" name="acl[course_edit]" value="on" />' ."\n";
		$form .= '        <input type="hidden" name="acl[course_create]" value="on" />' ."\n";
		$form .= '        <input type="hidden" name="acl[other_read]" value="on" />' ."\n";
		$form .= '        <input type="hidden" name="acl[other_edit]" value="off" />' ."\n";
		$form .= '        <input type="hidden" name="acl[other_create]" value="off" />' ."\n";

// hardwiring over

        //$form .= '<div style="padding: 10px">' . "\n" ;

        if ( $groupId != 0 )
        {
            $form .= '        <input type="hidden" name="gidReq" value="' . $groupId  . '" />' . "\n";
        }

		$form .= '        </td>' . "\n"
               . '      </tr>' . "\n"
               . '      <tr>' . "\n"
               . '        <th class="left">&nbsp;</th>' . "\n"
               . '        <td>' . "\n";


        $form .= '        <input type="submit" name="action[exEdit]" value="' . $langSave . '" />' . "\n"
            . disp_button ($_SERVER['PHP_SELF'] . '?action=list', $langCancel) . "\n"
            ;

        $form .= '        </td>' . "\n"
               . '      </tr>' . "\n"
               . '      </tbody>' . "\n"
               . '      </table>' . "\n"
            . '      </form>' . "\n"
            ;

        return $form;
    }
?>
