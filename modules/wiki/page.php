<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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


/*===========================================================================
	page.php
	@last update: 15-05-2007 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

	based on Claroline version 1.7.9 licensed under GPL
	      copyright (c) 2001, 2007 Universite catholique de Louvain (UCL)

	      original file: page Revision: 1.61.2.4

	Claroline authors: Frederic Minne <zefredz@gmail.com>
==============================================================================
    @Description:

    @Comments:

    @todo:
==============================================================================
*/
$tlabelReq = 'CLWIKI__';
$require_current_course = TRUE;
$require_help           = TRUE;
$helpTopic              = 'Wiki';
require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/textLib.inc.php';
$style= '';
$_gid = null;

if (isset($_SESSION['statut']) && $_SESSION['statut'] != 0 ) {
	$is_courseMember = true;
}
else {
	$is_courseMember = false;
}
$nameTools = $langWiki;

// check and set user access level for the tool
if ( ! isset( $_REQUEST['wikiId'] ) )
{
    header("Location: index.php?course=$course_code");
    exit();
}

// set admin mode and groupId

$is_allowedToAdmin = $is_editor;

if ( $_gid && $is_groupAllowed )
{
    // group context
    $grouId = $_gid;
    $navigation[]  = array ('url' => '../group/index.php?course='.$course_code, 'name' => $langGroups);
    $navigation[]= array ('url' => '../group/group_space.php?course='.$course_code, 'name' => $_group['name']);
}
elseif ($_gid && ! $is_groupAllowed )
{
    die($langNotAllowed);
}
else
{
    // course context
    $groupId = 0;
}

// Wiki specific classes and libraries
require_once 'modules/wiki/lib/class.clarodbconnection.php';
require_once 'modules/wiki/lib/class.wiki2xhtmlrenderer.php';
require_once 'modules/wiki/lib/class.wikipage.php';
require_once 'modules/wiki/lib/class.wikistore.php';
require_once 'modules/wiki/lib/class.wiki.php';
require_once 'modules/wiki/lib/lib.requestfilter.php';
require_once 'modules/wiki/lib/lib.wikisql.php';
require_once 'modules/wiki/lib/lib.wikidisplay.php';
require_once 'modules/wiki/lib/lib.javascript.php';

// security fix : disable access to other groups wiki
if ( isset( $_REQUEST['wikiId'] ) )
{
    $wikiId = (int) $_REQUEST['wikiId'];

    // Database nitialisation

    $tblList = array();
    $tblList["wiki_properties"] = "wiki_properties";
    $tblList["wiki_pages"] = "wiki_pages";
    $tblList["wiki_pages_content"] = "wiki_pages_content";
    $tblList["wiki_acls"] = "wiki_acls";

    $con = new ClarolineDatabaseConnection();

    $sql = "SELECT `group_id` "
	. "FROM `" . $tblList[ "wiki_properties" ] . "` "
	. "WHERE `id` = " . $wikiId
	." AND `course_id` = $course_id"
	;

    $result = $con->getRowFromQuery( $sql );

    $wikiGroupId = (int) $result['group_id'];

    if ( isset( $_gid ) && $_gid != $wikiGroupId )
    {
	die($langNotAllowed);
    }
    elseif( !isset( $_gid ) && $result['group_id'] != 0 )
    {
	die($langNotAllowed);
    }
}

// set request variables
$wikiId = ( isset( $_REQUEST['wikiId'] ) ) ? (int) $_REQUEST['wikiId'] : 0;

// Database nitialisation
$config = array();
$config["tbl_wiki_properties"] = $tblList[ "wiki_properties" ];
$config["tbl_wiki_pages"] = $tblList[ "wiki_pages" ];
$config["tbl_wiki_pages_content"] = $tblList[ "wiki_pages_content" ];
$config["tbl_wiki_acls"] = $tblList[ "wiki_acls" ];

$con = new ClarolineDatabaseConnection();

// auto create wiki in devel mode
if( defined("DEVEL_MODE") && ( DEVEL_MODE == true ) )
{
    init_wiki_tables( $con, false );
}

// Objects instantiation
$wikiStore = new WikiStore( $con, $config );

if ( ! $wikiStore->wikiIdExists( $wikiId ) )
{
    die ( $langWikiInvalidWikiId );
    draw($tool_content, 2, null, $head_content);
}

$wiki = $wikiStore->loadWiki( $wikiId );
$wikiPage = new WikiPage( $con, $config, $wikiId );
$wikiRenderer = new Wiki2xhtmlRenderer( $wiki );

$accessControlList = $wiki->getACL();

// --------------- Start of access rights management --------------

// Wiki access levels

$is_allowedToEdit = false;
$is_allowedToRead = false;
$is_allowedToCreate = false;

// set user access rights using user status and wiki access control list

if ( $_gid && $is_groupAllowed )
{
    // group_context
    if ( is_array( $accessControlList ) )
    {
	$is_allowedToRead = $is_allowedToAdmin
	    || ( $is_groupMember && WikiAccessControl::isAllowedToReadPage( $accessControlList, 'group' ) )
	    || ( $is_courseMember && WikiAccessControl::isAllowedToReadPage( $accessControlList, 'course' ) )
	    || WikiAccessControl::isAllowedToReadPage( $accessControlList, 'other' );
	$is_allowedToEdit = $is_allowedToAdmin
	    || ( $is_groupMember && WikiAccessControl::isAllowedToEditPage( $accessControlList, 'group' ) )
	    || ( $is_courseMember && WikiAccessControl::isAllowedToEditPage( $accessControlList, 'course' ) )
	    || WikiAccessControl::isAllowedToEditPage( $accessControlList, 'other' );
	$is_allowedToCreate = $is_allowedToAdmin
	    || ( $is_groupMember && WikiAccessControl::isAllowedToCreatePage( $accessControlList, 'group' ) )
	    || ( $is_courseMember && WikiAccessControl::isAllowedToCreatePage( $accessControlList, 'course' ) )
	    || WikiAccessControl::isAllowedToCreatePage( $accessControlList, 'other' );
    }
}
else
{
    // course context
    if ( is_array( $accessControlList ) )
    {
	// course member
	if ( $is_courseMember )
	{
	    $is_allowedToRead = $is_allowedToAdmin
		|| WikiAccessControl::isAllowedToReadPage( $accessControlList, 'course' );
	    $is_allowedToEdit = $is_allowedToAdmin
		|| WikiAccessControl::isAllowedToEditPage( $accessControlList, 'course' );
	    $is_allowedToCreate = $is_allowedToAdmin
		|| WikiAccessControl::isAllowedToCreatePage( $accessControlList, 'course' );
	}
	// not a course member
	else
	{
	    $is_allowedToRead = $is_allowedToAdmin
		|| WikiAccessControl::isAllowedToReadPage( $accessControlList, 'other' );
	    $is_allowedToEdit = $is_allowedToAdmin
		|| WikiAccessControl::isAllowedToEditPage( $accessControlList, 'other' );
	    $is_allowedToCreate = $is_allowedToAdmin
		|| WikiAccessControl::isAllowedToCreatePage( $accessControlList, 'other' );
	}
    }
}

// --------------- End of  access rights management ----------------

// filter action

if ( $is_allowedToEdit || $is_allowedToCreate )
{
    $valid_actions = array( "edit", "preview", "save"
	, "show", "recent", "diff", "all", "history"
	);
}
else
{
    $valid_actions = array( "show", "recent", "diff", "all"
	, "history"
	);
}

$_CLEAN = filter_by_key( 'action', $valid_actions, "R", true );

$action = ( isset( $_CLEAN['action'] ) ) ? $_CLEAN['action'] : 'show';

// get request variables

$creatorId = $uid;

$versionId = ( isset( $_REQUEST['versionId'] ) ) ? (int) $_REQUEST['versionId'] : 0;

$wiki_title = ( isset( $_REQUEST['title'] ) ) ? strip_tags( $_REQUEST['title'] ) : '';

if ( $action == "diff" )
{
    $old = ( isset( $_REQUEST['old'] ) ) ? (int) $_REQUEST['old'] : 0;
    $new = ( isset( $_REQUEST['new'] ) ) ? (int) $_REQUEST['new'] : 0;
}

// get content

if ( $action == "edit" )
{
    if ( isset( $_REQUEST['wiki_content'] ) )
    {
	    if (!get_magic_quotes_gpc()) {
	    $content = ( $_REQUEST['wiki_content'] == '' ) ? "__CONTENT__EMPTY__" : $_REQUEST['wiki_content'];
	}
	else {
	    $content = ( $_REQUEST['wiki_content'] == '' ) ? "__CONTENT__EMPTY__" : stripslashes($_REQUEST['wiki_content']);
	}
    }
    else
    {
	$content = '';
    }
}
else
{
    if (!get_magic_quotes_gpc()) {
	    $content = ( isset( $_REQUEST['wiki_content'] ) ) ? $_REQUEST['wiki_content'] : '';
    }
    else {
	    $content = ( isset( $_REQUEST['wiki_content'] ) ) ? stripslashes($_REQUEST['wiki_content']) : '';
    }
}

// use __MainPage__ if empty title

if ( $wiki_title === '' )
{
    // create wiki main page in a localisation compatible way
    $wiki_title = '__MainPage__';

    if( $wikiStore->pageExists( $wikiId, $wiki_title ) )
    {
	// do nothing
    }
    // auto create wiki in devel mode
    elseif ( ( ! $wikiStore->pageExists( $wikiId, $wiki_title ) )
	&& ( defined("DEVEL_MODE") && ( DEVEL_MODE == true ) ) )
    {
	init_wiki_main_page( $con, $wikiId, $creatorId );
    }
    else
    {
	// something weird's happened
	die("$langWrongWikiPageTitle");
	draw($tool_content, 2, null, $head_content);
    }
}

// --------- Start of wiki command processing ----------

// init message
$message = '';

switch( $action )
{
    // show differences
    case 'diff':
    {
	include 'modules/wiki/lib/lib.diff.php';

	if ( $wikiStore->pageExists( $wikiId, $wiki_title ) )
	{
	    // older version
	    $wikiPage->loadPageVersion( $old );
	    $old = $wikiPage->getContent();
	    $oldTime = $wikiPage->getCurrentVersionMtime();
	    $oldEditor = $wikiPage->getEditorId();

	    // newer version
	    $wikiPage->loadPageVersion( $new );
	    $new = $wikiPage->getContent();
	    $newTime = $wikiPage->getCurrentVersionMtime();
	    $newEditor = $wikiPage->getEditorId();

	    // get differences
	    $diff = '<table style="border: 0;">'.diff( $old, $new, true, 'format_table_line' ).'</table>';
	}

	break;
    }
    // recent changes
    case 'recent':
    {
	$recentChanges = $wiki->recentChanges();
	break;
    }
    // all pages
    case 'all':
    {
	$allPages = $wiki->allPages();
	break;
    }
    // edit page content
    case 'edit':
    {
	if( $wikiStore->pageExists( $wikiId, $wiki_title ) )
	{
	    if ( $versionId == 0 )
	    {
		$wikiPage->loadPage( $wiki_title );
	    }
	    else
	    {
		$wikiPage->loadPageVersion( $versionId );
	    }
	    if ( $content == '' )
	    {
		$content = $wikiPage->getContent();
	    }
	    if  ( $content == "__CONTENT__EMPTY__" )
	    {
		$content = '';
	    }

	    $wiki_title = $wikiPage->getTitle();
	    $_SESSION['wikiLastVersion'] = $wikiPage->getLastVersionId();
	}
	else
	{
	    if ( $content == '' )
	    {
		$message = $langWikiNoContent;
		$style = 'caution';
	    }
	}
	break;
    }
    // view page
    case 'show':
    {
	unset( $_SESSION['wikiLastVersion'] );

	if ( $wikiStore->pageExists( $wikiId, $wiki_title ) )
	{
	    if ( $versionId == 0 )
	    {
		$wikiPage->loadPage( $wiki_title );
	    }
	    else
	    {
		$wikiPage->loadPageVersion( $versionId );
	    }

	    $content = $wikiPage->getContent();

	    $wiki_title = $wikiPage->getTitle();
	}
	else
	{
	    $message = $langWikiPageNotFound;
	    $style = 'caution';
	}
	break;
    }
    // save page
    case 'save':
    {
	if ( isset( $content ) )
	{
	    $time = date( 'Y-m-d H:i:s' );

	    if ( $wikiPage->pageExists( $wiki_title ) )
	    {
		$wikiPage->loadPage( $wiki_title );
		if ( $content == $wikiPage->getContent() )
		{
		    unset( $_SESSION['wikiLastVersion'] );

		    $message = $langWikiIdenticalContent;
		    $style = 'caution';
		    $action = 'show';
		}
		else
		{
		    if ( isset( $_SESSION['wikiLastVersion'] )
			&& $wikiPage->getLastVersionId() != $_SESSION['wikiLastVersion'] )
		    {
			$action = 'conflict';
		    }
		    else
		    {
			$wikiPage->edit( $creatorId, $content, $time, true );
			unset( $_SESSION['wikiLastVersion'] );
			if ( $wikiPage->hasError() )
			{
			    $message = "Database error : " . $wikiPage->getError();
			    $style = "caution";
			}
			else
			{
			    $message = $langWikiPageSaved;
			    $style = "success";
			}
			$action = 'show';
		    }
		}
	    }
	    else
	    {
		$wikiPage->create( $creatorId, $wiki_title, $content, $time, true );
		if ( $wikiPage->hasError() )
		{
		    $message = 'Database error : ' . $wikiPage->getError();
		    $style = 'caution';
		}
		else
		{
		    $message = $langWikiPageSaved;
		    $style = 'success';
		}
		$action = 'show';
	    }
	}
	break;
    }
    // page history
    case 'history':
    {
	$wikiPage->loadPage( $wiki_title );
	$wiki_title = $wikiPage->getTitle();
	$history = $wikiPage->history( 0, 0, 'DESC' );
	break;
    }
}

// change to use empty page content

if ( ! isset( $content ) )
{
    $content = '';
}

// --------- End of wiki command processing -----------

// --------- Start of wiki display --------------------

// set xtra head

$jspath = document_web_path() . '/lib/javascript';

// set image repository
$head_content .= "<script type=\"text/javascript\">"
    . "\nvar sImgPath = '$themeimg'"
    . "\n</script>\n"
    ;
//navigation bar
    if (!add_units_navigation()) {
	    $navigation[]= array ( 'url' => 'index.php?course='.$course_code, 'name' => $langWiki);
	    $navigation[]= array ( 'url' => 'page.php?course='.$course_code.'&amp;wikiId='.$wikiId.'&amp;action=show', 'name' => $wiki->getTitle());
    }    

switch( $action )
{
    case "edit":
    {            
	$dispTitle = ( $wiki_title == "__MainPage__" ) ? $langWikiMainPage : $wiki_title;
	$navigation[]= array ( 'url' => 'page.php?course='.$course_code.'&amp;action=show&amp;wikiId='
	    . $wikiId . '&amp;title=' . $wiki_title
	    , 'name' => $dispTitle ); 

	$nameTools = $langEdit;	
	break;
    }
    case "all":
    {
	$nameTools = $langWikiAllPages;	
	break;
    }
    case "recent":
    {
	$nameTools = $langWikiRecentChanges;
	break;
    }
    case "history":
    {
	$dispTitle = ( $wiki_title == "__MainPage__" ) ? $langWikiMainPage : $wiki_title;
	$navigation[]= array ( 'url' => 'page.php?course='.$course_code.'&amp;action=show&amp;wikiId='
	    . $wikiId . '&amp;title=' . $wiki_title
	    , 'name' => $dispTitle );
	$nameTools = $langWikiPageHistory;	
	break;
    }
    default:
    {        
	$nameTools = ( $wiki_title == "__MainPage__" ) ? $langWikiMainPage : $wiki_title ;	
    }
}

// tool title

$toolTitle = array();
$toolTitle['mainTitle'] = sprintf( $langWikiTitlePattern, $wiki->getTitle() );

if ( $_gid )
{
	    $toolTitle['supraTitle'] = $_group['name'];
}

switch( $action )
{
    case "all":
    {
	$toolTitle['subTitle'] = $langWikiAllPages;
	break;
    }
    case "recent":
    {
	$toolTitle['subTitle'] = $langWikiRecentChanges;
	break;
    }
    case "history":
    {
	$toolTitle['subTitle'] = $langWikiPageHistory;
	break;
    }
    default:
    {
	$subTitle = ( $wiki_title == "__MainPage__" ) ? $langWikiMainPage: $wiki_title ;
	break;
    }
}

if (!empty($message)) {
    $tool_content .= "<table width=\"99%\">
	<tbody><tr>
	<td class='success'>
	    <p><b>$message</b></p>
	  </td>
	</tr>
	</tbody>
	</table><br />";
}

// Check for javascript
$javascriptEnabled = is_javascript_enabled();
// user is not allowed to read this page

if (!$is_allowedToRead)
{
    $tool_content .= $langWikiNotAllowedToRead;
    draw($tool_content, 2, null, $head_content);
    die;
}

// Wiki navigation bar
$tool_content .= '
  <div id="operations_container">
    <ul id="opslist">' . "\n";
$tool_content .= '          <li>'
    . '<img src="'.$themeimg.'/wiki.png" align="middle" />&nbsp;<a class="claroCmd" href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code
    . '&amp;wikiId=' . $wiki->getWikiId()
    . '&amp;action=show'
    . '&amp;title=__MainPage__'
    . '">'
    . $langWikiMainPage.'</a></li>' . "\n"
    ;
$tool_content .= '          <li>'
    . '<img src="'.$themeimg.'/history.png" align="middle" />&nbsp;<a class="claroCmd" href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code
    . '&amp;wikiId=' . $wiki->getWikiId()
    . '&amp;action=recent'
    . '">'
    . $langWikiRecentChanges.'</a></li>' . "\n"
    ;

$tool_content .= '          <li>'
    . '<img src="'.$themeimg.'/book.png" align="middle" />&nbsp;<a class="claroCmd" href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code
    . '&amp;wikiId=' . $wiki->getWikiId()
    . '&amp;action=all">'
    . $langWikiAllPages.'</a></li>' . "\n"
    ;

$tool_content .= '<li>'
    . '<img src="'.$themeimg.'/list.png" align="middle" />&nbsp;<a class="claroCmd" href="'.'index.php?course='.$course_code
    . '">'
    . $langWikiList .'</a></li>' . "\n"
    ;

$tool_content .= '</ul></div>';

if ( $action != 'recent' && $action != 'all' ) {

        $tool_content .= '<p align="right">';
        if ( $action == "edit" || $action == "diff" || $action == "history" ) {
            $tool_content .= ''
                . '<img src="'.$themeimg.'/back.png" align="middle" />&nbsp;'
                . '<a class="claroCmd" href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code
                . '&amp;wikiId=' . $wiki->getWikiId()
                . '&amp;action=show'
                . '&amp;title=' . rawurlencode($wiki_title)
                . '">'.$langWikiBackToPage.'</a>'
                ;
        }

        if ( $is_allowedToEdit || $is_allowedToCreate ) {
            // Show context
            if ( $action == "show" || $action == "history" || $action == "diff" )
            {
                $tool_content .= '&nbsp;&nbsp;&nbsp;'
                    . '<img src="'.$themeimg.'/edit.png" align="middle" />&nbsp;'
                    . '<a class="claroCmd" href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code
                    . '&amp;wikiId=' . $wiki->getWikiId()
                    . '&amp;action=edit'
                    . '&amp;title=' . rawurlencode( $wiki_title )
                    . '&amp;versionId=' . $versionId
                    . '">'
                    . $langWikiEditPage.'</a>'
                    ;
            }
        }

        if ( $action == "show" || $action == "edit" || $action == "history" || $action == "diff" ) {
            // active
            $tool_content .= '&nbsp;&nbsp;&nbsp;'
                    . '<img src="'.$themeimg.'/version.png" align="middle" />&nbsp;'
                    . '<a class="claroCmd" href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code
                    . '&amp;wikiId=' . $wiki->getWikiId()
                    . '&amp;action=history'
                    . '&amp;title=' . rawurlencode( $wiki_title )
                    . '">'
                    . $langWikiPageHistory.'</a>'
                    ;
        }

        if ( $action == "edit" || $action == "diff" ) {
            /*$tool_content .= '&nbsp;&nbsp;&nbsp;'
                . '<img src="'.$themeimg.'/help.png" border="0" alt="history" />&nbsp;'
                . '<a class="claroCmd" href="#" onClick="MyWindow=window.open(\''
                . '../help/help.php?topic=WikiSyntax&amp;language=' . $language
                . '\',\'MyWindow\',\'toolbar=no,location=no,directories=no,status=yes,menubar=no'
                . ',scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10\'); return false;">'
                . $langWikiHelpSyntax . '</a>'
                ; */
        }
        $tool_content .= "</p>";
}

switch( $action )
{
    case "conflict":
    {
	if( $wiki_title === '__MainPage__' )
	{
	    //$displaytitle = $langWikiMainPage;
	    $displaytitle = '';
	}
	else
	{
	    //$displaytitle = $wiki_title;
	    $displaytitle = '';
	}

	$tool_content .= '<div class="wikiTitle">' . "\n";
	$tool_content .= '<h2>'.$displaytitle
	    . ' : ' . $langWikiEditConflict
	    . '</h2>'
	    . "\n"
	    ;
	$tool_content .= '</div>' . "\n";
	$message = $langWikiConflictHowTo;
	$tool_content .= "<div class='caution'>$message</div></br />";
	$tool_content .= '<form id="editConflict" action="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code.'" method="POST">';
	$tool_content .= '<textarea name="conflictContent" id="wiki_content"'
	     . ' cols="80" rows="15" wrap="virtual">'
	     ;
	$tool_content .= q($content);
	$tool_content .= '</textarea><br /><br />' . "\n";
	$tool_content .= '<div>' . "\n";
	$tool_content .= '<input type="hidden" name="wikiId" value="'.$wikiId.'" />' . "\n";
	$tool_content .= '<input type="hidden" name="title" value="'.$wiki_title.'" />' . "\n";
	$tool_content .= '<input type="submit" name="action[edit]" value="'.$langWikiEditLastVersion.'" />' . "\n";
	$url = $_SERVER['SCRIPT_NAME']
	    . '?course='.$course_code
	    . '&amp;wikiId=' . $wikiId
	    . '&amp;title=' . $wiki_title
	    . '&amp;action=show'
	    ;
	$tool_content .= disp_button($url, $langCancel) . "\n";
	$tool_content .= '</div>' . "\n";
	$tool_content .= '</form>';
	break;
    }
    case "diff":
    {
	if( $wiki_title === '__MainPage__' )
	{
	    //$displaytitle = $langWikiMainPage;
	    $displaytitle = '';
	}
	else
	{
	    //$displaytitle = $wiki_title;
	    $displaytitle = '';
	}

	$oldTime = claro_format_locale_date($dateTimeFormatLong
		    , strtotime($oldTime) )
		    ;

	$userInfo = user_get_data( $oldEditor );	
	$oldEditorStr = q($userInfo['givenname']) . "&nbsp;" . q($userInfo['surname']);

	$newTime = claro_format_locale_date( $dateTimeFormatLong
		    , strtotime($newTime) )
		    ;

	$userInfo = user_get_data( $newEditor );
	$newEditorStr = q($userInfo['givenname']) . "&nbsp;" . q($userInfo['surname']);

	$versionInfo = '('
	    . sprintf( $langWikiDifferencePattern, $oldTime, $oldEditorStr, $newTime, $newEditorStr )
	    . ')'
	    ;

	$versionInfo = '<span style="font-size: 10px; font-weight: normal; color: red;">'
		    . $versionInfo . '</span>'
		    ;

	$tool_content .= '<div class="wikiTitle">' . "\n";
	$tool_content .= '<h2>'.$displaytitle
	    . $versionInfo
	    . '</h2>'
	    . "\n"
	    ;
	$tool_content .= '</div>' . "\n";

	$tool_content .= '<strong>'.$langWikiDifferenceKeys.'</strong>';

	$tool_content .= '<div class="diff">' . "\n";
	$tool_content .= '= <span class="diffEqual" >'.$langWikiDiffUnchangedLine.'</span><br />';
	$tool_content .= '+ <span class="diffAdded" >'.$langWikiDiffAddedLine.'</span><br />';
	$tool_content .= '- <span class="diffDeleted" >'.$langWikiDiffDeletedLine.'</span><br />';
	$tool_content .= 'M <span class="diffMoved" >'.$langWikiDiffMovedLine.'</span><br />';
	$tool_content .= '</div>' . "\n";
	$tool_content .= '<strong>'.$langWikiDifferenceTitle.'</strong>';
	$tool_content .= '<div class="diff">' . "\n";
	$tool_content .= $diff;
	$tool_content .= '</div>' . "\n";

	break;
    }
    case "recent":
    {
	if ( is_array( $recentChanges ) )
	{
	    $tool_content .= '<ul>' . "\n";

	    foreach ( $recentChanges as $recentChange )
	    {
		$pgtitle = ( $recentChange['title'] == "__MainPage__" )
		    ? $langWikiMainPage
		    : $recentChange['title']
		    ;

		$entry = '<strong><a href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code.'&amp;wikiId='
		    . $wikiId . '&amp;title=' . rawurlencode( $recentChange['title'] )
		    . '&amp;action=show"'
		    . '>'.$pgtitle.'</a></strong>'
		    ;

		$time = claro_format_locale_date( $dateTimeFormatLong
		    , strtotime($recentChange['last_mtime']) )
		    ;

		$userInfo = user_get_data( $recentChange['editor_id'] );		

		$userStr = q($userInfo['firstname']) . "&nbsp;" . q($userInfo['lastname']);
		$userUrl = $userStr;
		$tool_content .= '<li>'
		    . sprintf( $langWikiRecentChangesPattern, $entry, $time, $userUrl )
		    . '</li>'
		    . "\n"
		    ;
	    }

	    $tool_content .= '</ul>' . "\n";
	}
	break;
    }
    case "all":
    {
	// handle main page

	$tool_content .= '<ul><li><a href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code
	    . '&amp;wikiId=' . $wikiId
	    . '&amp;title=' . rawurlencode("__MainPage__")
	    . '&amp;action=show">'
	    . $langWikiMainPage
	    . '</a></li></ul>' . "\n"
	    ;

	// other pages

	if ( is_array( $allPages ) )
	{
	    $tool_content .= '<ul>' . "\n";

	    foreach ( $allPages as $page )
	    {
		if ( $page['title'] == "__MainPage__" )
		{
		    // skip main page
		    continue;
		}

		$pgtitle = rawurlencode( $page['title'] );

		$link = '<a href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code.'&amp;wikiId='
		    . $wikiId . '&amp;title=' . $pgtitle . '&amp;action=show"'
		    . '>' . $page['title'] . '</a>'
		    ;

		$tool_content .= '<li>' . $link. '</li>' . "\n";
	    }
	    $tool_content .= '</ul>' . "\n";
	}
	break;
    }
    // edit page
    case "edit":
    {
	if ( ! $wiki->pageExists( $wiki_title ) && ! $is_allowedToCreate )
	{
	    $tool_content .= $langWikiNotAllowedToCreate;
	}
	elseif ( $wiki->pageExists( $wiki_title ) && ! $is_allowedToEdit )
	{
	    $tool_content .= $langWikiNotAllowedToEdit;
	}
	else
	{
	    $script = $_SERVER['SCRIPT_NAME']."?course=$course_code";

	    $tool_content .= claro_disp_wiki_editor( $wikiId, $wiki_title, $versionId, $content, $script
		, true, false )
		;
	}

	break;
    }
    // page preview
    case "preview":
    {
	if ( ! isset( $content ) )
	{
	    $content = '';
	}

	$tool_content .= claro_disp_wiki_preview( $wikiRenderer, $wiki_title, $content );
	$tool_content .= claro_disp_wiki_preview_buttons( $wikiId, $wiki_title, $content );
	break;
    }
    // view page
    case "show":
    {
	if( $wikiPage->hasError() )
	{
	    $tool_content .= $wikiPage->getError();
	}
	else
	{

	    // get localized value for wiki main page title
	    if( $wiki_title === '__MainPage__' )
	    {
		//$displaytitle = $langWikiMainPage;
		$displaytitle = '';
	    }
	    else
	    {
		//$displaytitle = $wiki_title;
		$displaytitle = '';
	    }


	    if ( $versionId != 0 )
	    {
		$editorInfo = user_get_data( $wikiPage->getEditorId() );		

		$editorStr = q($editorInfo['firstname']) . "&nbsp;" . q($editorInfo['lastname']);

		$editorUrl = '&nbsp;-&nbsp;' . $editorStr;

		$mtime = claro_format_locale_date( $dateTimeFormatLong
		    , strtotime($wikiPage->getCurrentVersionMtime()) )
		    ;

		$versionInfo = sprintf( $langWikiVersionInfoPattern, $mtime, $editorUrl );

		$versionInfo = '&nbsp;<span style="font-size: 10px; font-weight: normal; color: red;">'
		    . $versionInfo . '</span>'
		    ;
	    }
	    else
	    {
		$versionInfo = '';
	    }

	    $tool_content .= '<div class="wikiTitle">' . "\n";
	    $tool_content .= '<h2>'.$displaytitle
		. $versionInfo
		. '</h2>'
		. "\n"
		;
	    $tool_content .= '</div>' . "\n";

	    $tool_content .= '<div class="wiki2xhtml">' . "\n";
	    $tool_content .= $wikiRenderer->render( $content );
	    $tool_content .= '</div>' . "\n";

	    $tool_content .= '<div style="clear:both;"><!-- spacer --></div>' . "\n";
	}

	break;
    }
    case "history":
    {
	if( $wiki_title === '__MainPage__' )
	{
	    //$displaytitle = $langWikiMainPage;
	    $displaytitle = '';
	}
	else
	{
	    //$displaytitle = $wiki_title;
	    $displaytitle = '';
	}

	$tool_content .= '<div class="wikiTitle">' . "\n";
	$tool_content .= '<h2>'.$displaytitle.'</h2>' . "\n";
	$tool_content .= '</div>' . "\n";

	$tool_content .= '<form id="differences" method="GET" action="'
	    . $_SERVER['SCRIPT_NAME']
	    . '">'
	    . "\n"
	    ;

	$tool_content .= '<div>' . "\n"
	    . '<input type="hidden" name="course" value="'.$course_code.'" />' . "\n"
	    . '<input type="hidden" name="wikiId" value="'.$wikiId.'" />' . "\n"
	    . '<input type="hidden" name="title" value="'.$wiki_title.'" />' . "\n"
	    . '<input type="submit" name="action[diff]" value="'.$langWikiShowDifferences.'" />' . "\n"
	    . '</div>' . "\n"
	    ;

	$tool_content .= '<table style="border: 0px;">' . "\n";

	if ( is_array( $history ) )
	{
	    $firstPass = true;

	    foreach ( $history as $version )
	    {
		$tool_content .= '<tr>' . "\n";

		if ( $firstPass == true )
		{
		    $checked = ' checked="checked"';
		    $firstPass = false;
		}
		else
		{
		    $checked = '';
		}

		$tool_content .= '<td>'
		    . '<input type="radio" name="old" value="'.$version['id'].'"'.$checked.' />' . "\n"
		    . '</td>'
		    . "\n"
		    ;

		$tool_content .= '<td>'
		    . '<input type="radio" name="new" value="'.$version['id'].'"'.$checked.' />' . "\n"
		    . '</td>'
		    . "\n"
		    ;

		$userInfo = user_get_data( $version['editor_id'] );
		mysql_select_db($mysqlMainDb);

		$userStr = q($userInfo['firstname']) . "&nbsp;" . q($userInfo['lastname']);

		$userUrl = $userStr;

		$versionUrl = '<a href="' . $_SERVER['SCRIPT_NAME'] . '?course='.$course_code.'&amp;wikiId='
		    . $wikiId . '&amp;title=' . rawurlencode( $wiki_title )
		    . '&amp;action=show&amp;versionId=' . $version['id']
		    . '">'
		    . claro_format_locale_date( $dateTimeFormatLong
			, strtotime($version['mtime']) )
		    . '</a>'
		    ;

		$tool_content .= '<td>'
		    . sprintf( $langWikiVersionPattern, $versionUrl, $userUrl )
		    . '</td>'
		    . "\n"
		    ;

		$tool_content .= '</tr>' . "\n";
	    }
	}

	$tool_content .= '</table>' . "\n";
	$tool_content .= '</form>';
	break;
    }
    default:
    {
	trigger_error( "Invalid action supplied to " . $_SERVER['SCRIPT_NAME'], E_USER_ERROR);
    }
}

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);

