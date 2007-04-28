<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:

    /**
     * CLAROLINE
     *
     * @version 1.7 $Revision$
     *
     * @copyright 2001-2005 Universite catholique de Louvain (UCL)
     *
     * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
     * This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
     * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
     * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
     *
     * @author Frederic Minne <zefredz@gmail.com>
     *
     * @package Wiki
     */
     
    $tlabelReq = 'CLWIKI__';

    require_once "../inc/claro_init_global.inc.php";
    
    /*if ( ! $is_toolAllowed )
    {
        if ( is_null( $_cid ) )
        {
            claro_disp_auth_form( true );
        }
        else
        {
            claro_die($langNotAllowed);
        }
    }*/
    
    if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);
    
    // config file
    require_once $includePath . "/conf/CLWIKI.conf.php";
    
    // check and set user access level for the tool
    
    if ( ! isset( $_REQUEST['wikiId'] ) )
    {
        header( "Location: wiki.php" );
        exit();
    }
    
    // set admin mode and groupId
    
    $is_allowedToAdmin = claro_is_allowed_to_edit();
    

    if ( $_gid && $is_groupAllowed )
    {
        // group context
        $grouId = $_gid;
        
        $interbredcrump[]  = array ('url' => '../group/group.php', 'name' => $langGroups);
        $interbredcrump[]= array ('url' => '../group/group_space.php', 'name' => $_group['name']);
    }
    elseif ( $_gid && ! $is_groupAllowed )
    {
        claro_die($langNotAllowed);
    }
    elseif ( $is_courseAllowed )
    {
        // course context
        $groupId = 0;
    }
    else
    {
        claro_disp_auth_form();
    }
    
    // Wiki specific classes and libraries
    
    require_once "lib/class.clarodbconnection.php";
    require_once "lib/class.wiki2xhtmlrenderer.php";
    require_once "lib/class.wikipage.php";
    require_once "lib/class.wikistore.php";
    require_once "lib/class.wiki.php";
    require_once "lib/lib.requestfilter.php";
    require_once "lib/lib.wikisql.php";
    require_once "lib/lib.wikidisplay.php";
    require_once "lib/lib.javascript.php";

    // security fix : disable access to other groups wiki
    if ( isset( $_REQUEST['wikiId'] ) )
    {
        $wikiId = (int) $_REQUEST['wikiId'];

        // Database nitialisation

        $tblList = claro_sql_get_course_tbl();

        $con = new ClarolineDatabaseConnection();

        $sql = "SELECT `group_id` "
            . "FROM `" . $tblList[ "wiki_properties" ] . "` "
            . "WHERE `id` = " . $wikiId
            ;

        $result = $con->getRowFromQuery( $sql );
        
        $wikiGroupId = (int) $result['group_id'];

        if ( isset( $_gid ) && $_gid != $wikiGroupId )
        {
            claro_die($langNotAllowed);
        }
        elseif( !isset( $_gid ) && $result['group_id'] != 0 )
        {
            claro_die($langNotAllowed);
        }
    }
    
    // Claroline libraries
    
    require_once $includePath . '/lib/user.lib.php';
    
    // set request variables
    
    $wikiId = ( isset( $_REQUEST['wikiId'] ) ) ? (int) $_REQUEST['wikiId'] : 0;
    
    // Database nitialisation
    
    $tblList = claro_sql_get_course_tbl();
    
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
    
    $creatorId = $_uid;
    
    $versionId = ( isset( $_REQUEST['versionId'] ) ) ? (int) $_REQUEST['versionId'] : 0;

    $title = ( isset( $_REQUEST['title'] ) ) ? strip_tags( $_REQUEST['title'] ) : '';
    
    if ( $action == "diff" )
    {
        $old = ( isset( $_REQUEST['old'] ) ) ? (int) $_REQUEST['old'] : 0;
        $new = ( isset( $_REQUEST['new'] ) ) ? (int) $_REQUEST['new'] : 0;
    }
    
    // get content
    
    if ( $action == "edit" )
    {
        if ( isset( $_REQUEST['content'] ) )
        {
            $content = ( $_REQUEST['content'] == '' ) ? "__CONTENT__EMPTY__" : $_REQUEST['content'];
        }
        else
        {
            $content = '';
        }
    }
    else
    {
        $content = ( isset( $_REQUEST['content'] ) ) ? $_REQUEST['content'] : '';
    }
    
    // use __MainPage__ if empty title

    if ( $title === '' )
    {
        // create wiki main page in a localisation compatible way
        $title = '__MainPage__';
        
        if( $wikiStore->pageExists( $wikiId, $title ) )
        {
            // do nothing
        }
        // auto create wiki in devl mode
        elseif ( ( ! $wikiStore->pageExists( $wikiId, $title ) )
            && ( defined("DEVEL_MODE") && ( DEVEL_MODE == true ) ) )
        {
            init_wiki_main_page( $con, $wikiId, $creatorId );
        }
        else
        {
            // something weird's happened
            die ( "Wrong page title" );
        }
    }
    
    // --------- Start of wiki command processing ----------
    
    // init message
    $message = '';
    
    switch( $action )
    {
        // show differences
        case "diff":
        {
            require_once "lib/lib.diff.php";
            
            if ( $wikiStore->pageExists( $wikiId, $title ) )
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
        case "recent":
        {
            require_once $includePath . '/lib/user.lib.php';
            $recentChanges = $wiki->recentChanges();
            break;
        }
        // all pages
        case "all":
        {
            $allPages = $wiki->allPages();
            break;
        }
        // edit page content
        case "edit":
        {
            if( $wikiStore->pageExists( $wikiId, $title ) )
            {
                if ( $versionId == 0 )
                {
                    $wikiPage->loadPage( $title );
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

                $title = $wikiPage->getTitle();
                
                $_SESSION['wikiLastVersion'] = $wikiPage->getLastVersionId();
            }
            else
            {
                if ( $content == '' )
                {
                    $message = "This page is empty, use the editor to add content.";
                }
            }
            break;
        }
        // view page
        case "show":
        {
            unset( $_SESSION['wikiLastVersion'] );
            
            if ( $wikiStore->pageExists( $wikiId, $title ) )
            {
                if ( $versionId == 0 )
                {
                    $wikiPage->loadPage( $title );
                }
                else
                {
                    $wikiPage->loadPageVersion( $versionId );
                }

                $content = $wikiPage->getContent();

                $title = $wikiPage->getTitle();
            }
            else
            {
                $message = "Page " . $title . " not found";
            }
            break;
        }
        // save page
        case "save":
        {
            if ( isset( $content ) )
            {
                $time = date( "Y-m-d H:i:s" );

                if ( $wikiPage->pageExists( $title ) )
                {
                    $wikiPage->loadPage( $title );
                    
                    if ( $content == $wikiPage->getContent() )
                    {
                        unset( $_SESSION['wikiLastVersion'] );

                        $message = $langWikiIdenticalContent;
                        
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
                            }
                            else
                            {
                                $message = $langWikiPageSaved;
                            }
                            
                            $action = 'show';
                        }
                    }
                    
                    //notify modification of the page
                
                    $eventNotifier->notifyCourseEvent('wiki_page_modified'
                                         , $_cid
                                         , $_tid
                                         , $wikiId
                                         , $_gid
                                         , '0');
                }
                else
                {
                    $wikiPage->create( $creatorId, $title, $content, $time, true );
                    
                    if ( $wikiPage->hasError() )
                    {
                        $message = "Database error : " . $wikiPage->getError();
                    }
                    else
                    {
                        $message = $langWikiPageSaved;
                    }
                    
                    $action = 'show';
                    
                    //notify creation of the page
                
                    $eventNotifier->notifyCourseEvent('wiki_page_added'
                                         , $_cid
                                         , $_tid
                                         , $wikiId
                                         , $_gid
                                         , '0');
                }               
            }
            
            break;
        }
        // page history
        case "history":
        {
            $wikiPage->loadPage( $title );
            $title = $wikiPage->getTitle();
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
    $htmlHeadXtra[] = "<script type=\"text/javascript\">"
        . "\nvar sImgPath = '".$imgRepositoryWeb . "'"
        . "\n</script>\n"
        ;
    
    // set style
    $htmlHeadXtra[] =
        "<style type=\"text/css\">
        .wikiTitle h1{
            color: Black;
            background: none;
            font-size: 200%;
            font-weight: bold;
            /*font-weight: normal;*/
            border-bottom: 2px solid #aaaaaa;
        }
        .wiki2xhtml{
            margin-left: 5px;
        }
        .wiki2xhtml h2,h3,h4{
            color: Black;
            background: none;
        }
        .wiki2xhtml h2{
            border-bottom: 1px solid #aaaaaa;
            font-size:175%;
            font-weight:bold;
        }
        .wiki2xhtml h3{
            border-bottom: 1px groove #aaaaaa;
            font-size:150%;
            font-weight:bold;
        }
        .wiki2xhtml h4{
            font-size:125%;
            font-weight:bold;
        }
        
        .wiki2xhtml a.wikiEdit{
            color: red;
        }
        .diff{
            font-family: monospace;
            padding: 5px;
            margin: 5px;
        }
        .diffEqual{
            background-color: white;
        }
        .diffMoved{
            background-color: #00CCFF;
        }
        .diffAdded{
            background-color: lime;
        }
        .diffDeleted{
            background-color: #FF00AA;
        }
        </style>"
        ;
        
    // Breadcrumps
    
    $interbredcrump[]= array ( 'url' => 'wiki.php', 'name' => $langWiki);
    $interbredcrump[]= array ( 'url' => NULL
        , 'name' => $wiki->getTitle() );
        
    switch( $action )
    {
        case "edit":
        {
            $dispTitle = ( $title == "__MainPage__" ) ? $langWikiMainPage : $title;
            $interbredcrump[]= array ( 'url' => 'page.php?action=show&amp;wikiId='
                . $wikiId . '&amp;title=' . $title
                , 'name' => $dispTitle );
            $nameTools = $langEdit;
            $noPHP_SELF = true;
            break;
        }
        case "all":
        {
            $nameTools = $langWikiAllPages;
            $noPHP_SELF = true;
            break;
        }
        case "recent":
        {
            $nameTools = $langWikiRecentChanges;
            $noPHP_SELF = true;
            break;
        }
        case "history":
        {
            $dispTitle = ( $title == "__MainPage__" ) ? $langWikiMainPage : $title;
            $interbredcrump[]= array ( 'url' => 'page.php?action=show&amp;wikiId='
                . $wikiId . '&amp;title=' . $title
                , 'name' => $dispTitle );
            $nameTools = $langWikiPageHistory;
            $noPHP_SELF = true;
            break;
        }
        default:
        {
            $nameTools = ( $title == "__MainPage__" ) ? $langWikiMainPage : $title ;
            $noPHP_SELF = true;
        }
    }
    
    // Claroline Header and Banner

    require_once $includePath . "/claro_init_header.inc.php";
    
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
            $subTitle = ( $title == "__MainPage__" )
                ? $langWikiMainPage
                : $title
                ;
                
            // $toolTitle['subTitle'] = $subTitle;
                
            break;
        }
    }
    
    echo claro_disp_tool_title( $toolTitle, false );
    
    if ( !empty($message) )
    {
        echo claro_disp_message_box($message) . "\n";
    }
    
    // Check javascript
    
    $javascriptEnabled = claro_is_javascript_enabled();
    
    // user is not allowed to read this page
    
    if ( ! $is_allowedToRead )
    {
        echo $langWikiNotAllowedToRead;
        
        require_once "../inc/claro_init_footer.inc.php";
        
        die ( '' );
    }
    
    // Wiki navigation bar
    
    echo '<p>';
    
    echo '<a class="claroCmd" href="'
        . $_SERVER['PHP_SELF']
        . '?wikiId=' . $wiki->getWikiId()
        . '&amp;action=show'
        . '&amp;title=__MainPage__'
        . '">'
        . '<img src="'.$imgRepositoryWeb.'wiki.gif" border="0" alt="edit" />&nbsp;'
        . $langWikiMainPage.'</a>'
        ;
    
    echo '&nbsp;|&nbsp;<a class="claroCmd" href="'
        . $_SERVER['PHP_SELF']
        . '?wikiId=' . $wiki->getWikiId()
        . '&amp;action=recent'
        . '">'
        . '<img src="'.$imgRepositoryWeb.'history.gif" border="0" alt="recent changes" />&nbsp;'
        . $langWikiRecentChanges.'</a>'
        ;

    echo '&nbsp;|&nbsp;<a class="claroCmd" href="'
        . $_SERVER['PHP_SELF']
        . '?wikiId=' . $wiki->getWikiId()
        . '&amp;action=all'
        . '">'
        . '<img src="'.$imgRepositoryWeb.'book.gif" border="0" alt="all pages" />&nbsp;'
        . $langWikiAllPages.'</a>'
        ;
        
    echo '&nbsp;|&nbsp;<a class="claroCmd" href="'
        . 'wiki.php'
        . '">'
        . '<img src="'.$imgRepositoryWeb.'info.gif" border="0" alt="all pages" />&nbsp;'
        . $langWikiList .'</a>'
        ;
    
    echo '</p>';
    
    if ( $action != 'recent' && $action != 'all' )
    {
    
    echo '<p>';
    
    if ( $action == "edit" || $action == "diff" || $action == "history" )
    {
        echo '<a class="claroCmd" href="'
            . $_SERVER['PHP_SELF']
            . '?wikiId=' . $wiki->getWikiId()
            . '&amp;action=show'
            . '&amp;title=' . rawurlencode($title)
            . '">'
            . '<img src="'.$imgRepositoryWeb.'back.gif" border="0" alt="back" />&nbsp;'
            . $langWikiBackToPage.'</a>'
            ;
    }
    else
    {
        echo '<span class="claroCmdDisabled">'
            . '<img src="'.$imgRepositoryWeb.'back.gif" border="0" alt="back" />&nbsp;'
            . $langWikiBackToPage.'</span>'
            ;
    }
        
    if ( $is_allowedToEdit || $is_allowedToCreate )
    {
        // Show context
        if ( $action == "show" || $action == "history" || $action == "diff" )
        {
            echo '&nbsp;|&nbsp;<a class="claroCmd" href="'
                . $_SERVER['PHP_SELF']
                . '?wikiId=' . $wiki->getWikiId()
                . '&amp;action=edit'
                . '&amp;title=' . rawurlencode( $title )
                . '&amp;versionId=' . $versionId
                . '">'
                . '<img src="'.$imgRepositoryWeb.'edit.gif" border="0" alt="edit" />&nbsp;'
                . $langWikiEditPage.'</a>'
                ;
        }
        // Other contexts
        else
        {
            echo '&nbsp;|&nbsp;<span class="claroCmdDisabled">'
                . '<img src="'.$imgRepositoryWeb.'edit.gif" border="0" alt="edit" />&nbsp;'
                . $langWikiEditPage . '</span>'
                ;
        }
    }
    else
    {
        echo '&nbsp;|&nbsp;<span class="claroCmdDisabled">'
            . '<img src="'.$imgRepositoryWeb.'edit.gif" border="0" alt="edit" />&nbsp;'
            . $langWikiEditPage . '</span>'
            ;
    }
    
    if ( $action == "show" || $action == "edit" || $action == "history" || $action == "diff" )
    {
        // active
        echo '&nbsp;|&nbsp;<a class="claroCmd" href="'
                . $_SERVER['PHP_SELF']
                . '?wikiId=' . $wiki->getWikiId()
                . '&amp;action=history'
                . '&amp;title=' . rawurlencode( $title )
                . '">'
                . '<img src="'.$imgRepositoryWeb.'version.gif" border="0" alt="history" />&nbsp;'
                . $langWikiPageHistory.'</a>'
                ;
    }
    else
    {
        // inactive
        echo '&nbsp;|&nbsp;<span class="claroCmdDisabled">'
            . '<img src="'.$imgRepositoryWeb.'version.gif" border="0" alt="history" />&nbsp;'
            . $langWikiPageHistory . '</span>'
            ;
    }
        
    if ( $action == "edit" || $action == "diff" )
    {
        echo '&nbsp;|&nbsp;<a class="claroCmd" href="#" onClick="MyWindow=window.open(\''
            . 'help_wiki.php?help=syntax'
            . '\',\'MyWindow\',\'toolbar=no,location=no,directories=no,status=yes,menubar=no'
            . ',scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10\'); return false;">'
            . '<img src="'.$imgRepositoryWeb.'help_little.gif" border="0" alt="history" />&nbsp;'
            . $langWikiHelpSyntax . '</a>'
            ;
    }

    echo '</p>' . "\n";
    
    }
    
    switch( $action )
    {
        case "conflict":
        {
            if( $title === '__MainPage__' )
            {
                $displaytitle = $langWikiMainPage;
            }
            else
            {
                $displaytitle = $title;
            }
            
            echo '<div class="wikiTitle">' . "\n";
            echo '<h1>'.$displaytitle
                . ' : ' . $langWikiEditConflict
                . '</h1>'
                . "\n"
                ;
            echo '</div>' . "\n";
            
            $message = $langWikiConflictHowTo;
                
            echo claro_disp_message_box ( $message ) . '<br />' . "\n";
            
            echo '<form id="editConflict" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
            echo '<textarea name="conflictContent" id="content"'
                 . ' cols="80" rows="15" wrap="virtual">'
                 ;
            echo $content;
            echo '</textarea><br /><br />' . "\n";
            echo '<div>' . "\n";
            echo '<input type="hidden" name="wikiId" value="'.$wikiId.'" />' . "\n";
            echo '<input type="hidden" name="title" value="'.$title.'" />' . "\n";
            echo '<input type="submit" name="action[edit]" value="'.$langWikiEditLastVersion.'" />' . "\n";
            $url = $_SERVER['PHP_SELF']
                . '?wikiId=' . $wikiId
                . '&amp;title=' . $title
                . '&amp;action=show'
                ;
            echo claro_disp_button( $url, $langCancel ) . "\n";
            echo '</div>' . "\n";
            echo '</form>';
            break;
        }
        case "diff":
        {
            if( $title === '__MainPage__' )
            {
                $displaytitle = $langWikiMainPage;
            }
            else
            {
                $displaytitle = $title;
            }
            
            $oldTime = claro_disp_localised_date( $dateTimeFormatLong
                        , strtotime($oldTime) )
                        ;
                        
            $userInfo = user_get_data( $oldEditor );
            $oldEditorStr = $userInfo['firstname'] . "&nbsp;" . $userInfo['lastname'];

            $newTime = claro_disp_localised_date( $dateTimeFormatLong
                        , strtotime($newTime) )
                        ;
                        
            $userInfo = user_get_data( $newEditor );
            $newEditorStr = $userInfo['firstname'] . "&nbsp;" . $userInfo['lastname'];

            $versionInfo = '('
                . sprintf( $langWikiDifferencePattern, $oldTime, $oldEditorStr, $newTime, $newEditorStr )
                . ')'
                ;
                
            $versionInfo = '&nbsp;<span style="font-size: 40%; font-weight: normal; color: red;">'
                        . $versionInfo . '</span>'
                        ;

            echo '<div class="wikiTitle">' . "\n";
            echo '<h1>'.$displaytitle
                . $versionInfo
                . '</h1>'
                . "\n"
                ;
            echo '</div>' . "\n";
            
            echo '<strong>'.$langWikiDifferenceKeys.'</strong>';

            echo '<div class="diff">' . "\n";
            echo '= <span class="diffEqual" >'.$langWikiDiffUnchangedLine.'</span><br />';
            echo '+ <span class="diffAdded" >'.$langWikiDiffAddedLine.'</span><br />';
            echo '- <span class="diffDeleted" >'.$langWikiDiffDeletedLine.'</span><br />';
            echo 'M <span class="diffMoved" >'.$langWikiDiffMovedLine.'</span><br />';
            echo '</div>' . "\n";
            
            echo '<strong>'.$langWikiDifferenceTitle.'</strong>';

            echo '<div class="diff">' . "\n";
            echo $diff;
            echo '</div>' . "\n";
            
            break;
        }
        case "recent":
        {
            if ( is_array( $recentChanges ) )
            {
                echo '<ul>' . "\n";
                
                foreach ( $recentChanges as $recentChange )
                {
                    $pgtitle = ( $recentChange['title'] == "__MainPage__" )
                        ? $langWikiMainPage
                        : $recentChange['title']
                        ;
                        
                    $entry = '<strong><a href="'.$_SERVER['PHP_SELF'].'?wikiId='
                        . $wikiId . '&amp;title=' . rawurlencode( $recentChange['title'] )
                        . '&amp;action=show"'
                        . '>'.$pgtitle.'</a></strong>'
                        ;
                        
                    $time = claro_disp_localised_date( $dateTimeFormatLong
                        , strtotime($recentChange['last_mtime']) )
                        ;
                        
                    $userInfo = user_get_data( $recentChange['editor_id'] );
                    
                    $userStr = $userInfo['firstname'] . "&nbsp;" . $userInfo['lastname'];
                    
                    if ( $is_courseMember )
                    {
                        $userUrl = '<a href="'. $clarolineRepositoryWeb
                            . 'user/userInfo.php?uInfo='
                            . $recentChange['editor_id'].'">'
                            .$userStr.'</a>'
                            ;
                    }
                    else
                    {
                        $userUrl = $userStr;
                    }
                        
                    echo '<li>'
                        . sprintf( $langWikiRecentChangesPattern, $entry, $time, $userUrl )
                        . '</li>'
                        . "\n"
                        ;
                }

                echo '</ul>' . "\n";
            }
            break;
        }
        case "all":
        {
            // handle main page
            
            echo '<ul><li><a href="'.$_SERVER['PHP_SELF']
                . '?wikiId=' . $wikiId
                . '&amp;title=' . rawurlencode("__MainPage__")
                . '&amp;action=show">'
                . $langWikiMainPage
                . '</a></li></ul>' . "\n"
                ;
            
            // other pages
            
            if ( is_array( $allPages ) )
            {
                echo '<ul>' . "\n";
                
                foreach ( $allPages as $page )
                {
                    if ( $page['title'] == "__MainPage__" )
                    {
                        // skip main page
                        continue;
                    }

                    $pgtitle = rawurlencode( $page['title'] );

                    $link = '<a href="'.$_SERVER['PHP_SELF'].'?wikiId='
                        . $wikiId . '&amp;title=' . $pgtitle . '&amp;action=show"'
                        . '>' . $page['title'] . '</a>'
                        ;
                        
                    echo '<li>' . $link. '</li>' . "\n";
                }
                echo '</ul>' . "\n";
            }
            break;
        }
        // edit page
        case "edit":
        {
            if ( ! $wiki->pageExists( $title ) && ! $is_allowedToCreate )
            {
                echo $langWikiNotAllowedToCreate;
            }
            elseif ( $wiki->pageExists( $title ) && ! $is_allowedToEdit )
            {
                echo $langWikiNotAllowedToEdit;
            }
            else
            {
                $script = $_SERVER['PHP_SELF'];

                echo claro_disp_wiki_editor( $wikiId, $title, $versionId, $content, $script
                    , $showWikiEditorToolbar, $forcePreviewBeforeSaving )
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

            echo claro_disp_wiki_preview( $wikiRenderer, $title, $content );
            
            echo claro_disp_wiki_preview_buttons( $wikiId, $title, $content );

            break;
        }
        // view page
        case "show":
        {
            if( $wikiPage->hasError() )
            {
                echo $wikiPage->getError();
            }
            else
            {
                // get localized value for wiki main page title
                if( $title === '__MainPage__' )
                {
                    $displaytitle = $langWikiMainPage;
                }
                else
                {
                    $displaytitle = $title;
                }
                
                if ( $versionId != 0 )
                {
                    $editorInfo = user_get_data( $wikiPage->getEditorId() );

                    $editorStr = $editorInfo['firstname'] . "&nbsp;" . $editorInfo['lastname'];

                    if ( $is_courseMember )
                    {
                        $editorUrl = '&nbsp;-&nbsp;<a href="'. $clarolineRepositoryWeb
                            . 'user/userInfo.php?uInfo='
                            . $wikiPage->getEditorId() .'">'
                            . $editorStr.'</a>'
                            ;
                    }
                    else
                    {
                        $editorUrl = '&nbsp;-&nbsp;' . $editorStr;
                    }
                    
                    $mtime = claro_disp_localised_date( $dateTimeFormatLong
                        , strtotime($wikiPage->getCurrentVersionMtime()) )
                        ;
                        
                    $versionInfo = sprintf( $langWikiVersionInfoPattern, $mtime, $editorUrl );
                        
                    $versionInfo = '&nbsp;<span style="font-size: 40%; font-weight: normal; color: red;">'
                        . $versionInfo . '</span>'
                        ;
                }
                else
                {
                    $versionInfo = '';
                }
                
                echo '<div class="wikiTitle">' . "\n";
                echo '<h1>'.$displaytitle
                    . $versionInfo
                    . '</h1>'
                    . "\n"
                    ;
                echo '</div>' . "\n";
                
                echo '<div class="wiki2xhtml">' . "\n";
                echo $wikiRenderer->render( $content );
                echo '</div>' . "\n";
                
                echo '<div style="clear:both;"><!-- spacer --></div>' . "\n";
            }

            break;
        }
        case "history":
        {
            if( $title === '__MainPage__' )
            {
                $displaytitle = $langWikiMainPage;
            }
            else
            {
                $displaytitle = $title;
            }

            echo '<div class="wikiTitle">' . "\n";
            echo '<h1>'.$displaytitle.'</h1>' . "\n";
            echo '</div>' . "\n";
            
            echo '<form id="differences" method="GET" action="'
                . $_SERVER['PHP_SELF']
                . '">'
                . "\n"
                ;
                
            echo '<div>' . "\n"
                . '<input type="hidden" name="wikiId" value="'.$wikiId.'" />' . "\n"
                . '<input type="hidden" name="title" value="'.$title.'" />' . "\n"
                . '<input type="submit" name="action[diff]" value="'.$langWikiShowDifferences.'" />' . "\n"
                . '</div>' . "\n"
                ;
            
            echo '<table style="border: 0px;">' . "\n";
            
            if ( is_array( $history ) )
            {
                $firstPass = true;
                
                foreach ( $history as $version )
                {
                    echo '<tr>' . "\n";
                    
                    if ( $firstPass == true )
                    {
                        $checked = ' checked="checked"';
                        $firstPass = false;
                    }
                    else
                    {
                        $checked = '';
                    }
                    
                    echo '<td>'
                        . '<input type="radio" name="old" value="'.$version['id'].'"'.$checked.' />' . "\n"
                        . '</td>'
                        . "\n"
                        ;
                        
                    echo '<td>'
                        . '<input type="radio" name="new" value="'.$version['id'].'"'.$checked.' />' . "\n"
                        . '</td>'
                        . "\n"
                        ;

                    $userInfo = user_get_data( $version['editor_id'] );

                    $userStr = $userInfo['firstname'] . "&nbsp;" . $userInfo['lastname'];
                    
                    if ( $is_courseMember )
                    {
                        $userUrl = '<a href="'. $clarolineRepositoryWeb
                            . 'user/userInfo.php?uInfo='
                            . $version['editor_id'].'">'
                            .$userStr.'</a>'
                            ;
                    }
                    else
                    {
                        $userUrl = $userStr;
                    }
                    
                    $versionUrl = '<a href="' . $_SERVER['PHP_SELF'] . '?wikiId='
                        . $wikiId . '&amp;title=' . rawurlencode( $title )
                        . '&amp;action=show&amp;versionId=' . $version['id']
                        . '">'
                        . claro_disp_localised_date( $dateTimeFormatLong
                            , strtotime($version['mtime']) )
                        . '</a>'
                        ;
                    
                    echo '<td>'
                        . sprintf( $langWikiVersionPattern, $versionUrl, $userUrl )
                        . '</td>'
                        . "\n"
                        ;
                        
                    echo '</tr>' . "\n";
                }
            }
            
            echo '</table>' . "\n";
            
            echo '</form>';
            
            break;
        }
        default:
        {
            trigger_error( "Invalid action supplied to " . $_SERVER['PHP_SELF']
                , E_USER_ERROR
                );
        }
    }
    
    // ------------ End of wiki script ---------------

    // Claroline footer
    
    require_once $includePath . "/claro_init_footer.inc.php";
?>
