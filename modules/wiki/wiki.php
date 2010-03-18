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
	wiki.php
	@last update: 15-05-2007 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

	based on Claroline version 1.7.9 licensed under GPL
	      copyright (c) 2001, 2007 Universite catholique de Louvain (UCL)

	      original file: wiki Revision: 1.53.2.1

	Claroline authors: Frederic Minne <zefredz@gmail.com>
==============================================================================
    @Description:

    @Comments:

    @todo:
==============================================================================
*/

    $tlabelReq = 'CLWIKI__';

    require_once("../../include/lib/learnPathLib.inc.php");

    $require_current_course = TRUE;
	$require_help           = TRUE;
	$helpTopic              = "Wiki";

	require_once("../../include/baseTheme.php");

	$head_content = "";
	$tool_content = "";

$head_content .= '
<script>
function confirmation (name)
{
    if (confirm("'.$langConfirmDelete.'"))
        {return true;}
    else
        {return false;}
}
</script>
';

	$style= "";

	$imgRepositoryWeb = "../../template/classic/img";
	// temporary
	$_gid = null;

	$nameTools = $langWiki;

	mysql_select_db($currentCourseID);
    // display mode

    // check and set user access level for the tool

    // set admin mode and groupId

    $is_allowedToAdmin = $is_adminOfCourse;

    if ( $_gid && $is_groupAllowed )
    {
        // group context
        $groupId = (int) $_gid;

        $navigation[]  = array ('url' => '../group/group.php', 'name' => $langGroups);
        $navigation[]  = array ('url' => '../group/group_space.php', 'name' => $_group['name']);
    }
    elseif ($_gid && ! $is_groupAllowed)
    {
        die($langNotAllowed);
    }
    else
    {
        // course context
        $groupId = 0;
    }

    // require wiki files

    require_once "lib/class.clarodbconnection.php";
    require_once "lib/class.wiki.php";
    require_once "lib/class.wikistore.php";
    require_once "lib/class.wikipage.php";
    require_once "lib/lib.requestfilter.php";
    require_once "lib/lib.wikisql.php";
    require_once "lib/lib.javascript.php";
    require_once "lib/lib.wikidisplay.php";

    // filter request variables

    // filter allowed actions using user status
    if ( $is_allowedToAdmin )
    {
	$valid_actions = array( "list", "rqEdit", "exEdit", "exDelete" );
    }
    else
    {
        $valid_actions = array( "list" );
    }

    $_CLEAN = filter_by_key( 'action', $valid_actions, "R", false );

    $action = ( isset( $_CLEAN['action'] ) ) ? $_CLEAN['action'] : 'list';

    $wikiId = ( isset( $_REQUEST['wikiId'] ) ) ? (int) $_REQUEST['wikiId'] : 0;

    $creatorId = $uid;

    // get request variable for wiki edition
    if ( $action == "exEdit" )
    {
        $wikiTitle = ( isset( $_POST['title'] ) ) ? strip_tags( $_POST['title'] ) : '';
        $wikiDesc = ( isset( $_POST['desc'] ) ) ? strip_tags( $_POST['desc'] ) : '';

        if ( $wikiDesc == $langWikiDefaultDescription )
        {
            $wikiDesc = '';
        }

        $acl = ( isset( $_POST['acl'] ) ) ? $_POST['acl'] : null;

        // initialise access control list

        $wikiACL = WikiAccessControl::emptyWikiACL();

        if ( is_array( $acl ) )
        {
            foreach ( $acl as $key => $value )
            {
                if ( $value == 'on' )
                {
                    $wikiACL[$key] = true;
                }
            }
        }

        // force Wiki ACL coherence

        if ( $wikiACL['course_read'] == false && $wikiACL['course_edit'] == true )
        {
            $wikiACL['course_edit'] = false;
        }
        if ( $wikiACL['group_read'] == false && $wikiACL['group_edit'] == true )
        {
            $wikiACL['group_edit'] = false;
        }
        if ( $wikiACL['other_read'] == false && $wikiACL['other_edit'] == true )
        {
            $wikiACL['other_edit'] = false;
        }

        if ( $wikiACL['course_edit'] == false  && $wikiACL['course_create'] == true )
        {
            $wikiACL['course_create'] = false;
        }
        if ( $wikiACL['group_edit'] == false  && $wikiACL['group_create'] == true )
        {
            $wikiACL['group_create'] = false;
        }
        if ( $wikiACL['other_edit'] == false  && $wikiACL['other_create'] == true )
        {
            $wikiACL['other_create'] = false;
        }
    }

    // Database nitialisation

    $config = array();
    $config["tbl_wiki_properties"] = "wiki_properties";
    $config["tbl_wiki_pages"] = "wiki_pages";
    $config["tbl_wiki_pages_content"] = "wiki_pages_content";
    $config["tbl_wiki_acls"] = "wiki_acls";

    $con = new ClarolineDatabaseConnection();

    // DEVEL_MODE database initialisation
    // DO NOT FORGET TO REMOVE FOR PROD !!!
    if( defined("DEVEL_MODE") && ( DEVEL_MODE == true ) )
    {
        init_wiki_tables( $con, false );
    }

    // Objects instantiation

    $wikiStore = new WikiStore( $con, $config );
    $wikiList = array();

    // --------- Start of command processing ----------------

    switch ( $action )
    {
        // execute delete
        case "exDelete":
        {
            if ( $wikiStore->wikiIdExists( $wikiId ) )
            {
                $wiki = $wikiStore->deleteWiki( $wikiId );
            }
            else
            {
                $message = $langWikiInvalidWikiId;
                $action = "error";
                $style = "caution";
            }

            if ( $groupId === 0 )
            {
                $wikiList = $wikiStore->getCourseWikiList();
            }
            else
            {
                $wikiList = $wikiStore->getWikiListByGroup( $groupId );
            }

            $message = $langWikiDeletionSucceed;
            $style = "success";


            $action = 'list';

            break;
        }
        // request edit
        case "rqEdit":
        {
            if ( $wikiId == 0 )
            {
                $wikiTitle = '';
                $wikiDesc = '';
                $wikiACL = null;
            }
            elseif ( $wikiStore->wikiIdExists( $wikiId ) )
            {
                $wiki = $wikiStore->loadWiki( $wikiId );
                $wikiTitle = $wiki->getTitle();
                $wikiDesc = $wiki->getDescription();
                $wikiACL = $wiki->getACL();
                $groupId = $wiki->getGroupId();

            }
            else
            {
                $message = $langWikiInvalidWikiId;
                $action = "error";
                $style = "caution";
            }
            break;
        }
        // execute edit
        case "exEdit":
        {
            if ( $wikiId == 0 )
            {
                $wiki = new Wiki( $con, $config );
                $wiki->setTitle( $wikiTitle );
                $wiki->setDescription( $wikiDesc );
                $wiki->setACL( $wikiACL );
                $wiki->setGroupId( $groupId );
                $wikiId = $wiki->save();

                $mainPageContent = sprintf( $langWikiMainPageContent, $wikiTitle );

                $wikiPage = new WikiPage( $con, $config, $wikiId );
                $wikiPage->create( $creatorId, '__MainPage__'
                    , $mainPageContent, date( "Y-m-d H:i:s" ), true );

                $message = $langWikiCreationSucceed;
                $style = "success";
            }
            elseif ( $wikiStore->wikiIdExists( $wikiId ) )
            {
                $wiki = $wikiStore->loadWiki( $wikiId );
                $wiki->setTitle( $wikiTitle );
                $wiki->setDescription( $wikiDesc );
                $wiki->setACL( $wikiACL );
                $wiki->setGroupId( $groupId );
                $wikiId = $wiki->save();

                $message = $langWikiEditionSucceed;
                $style = "success";
            }
            else
            {
                $message = $langWikiInvalidWikiId;
                $action = "error";
                $style = "caution";
            }

            $action = 'list';

            // no break
        }
        // list wiki
        case "list":
        {
            if ( $groupId == 0 )
            {
                $wikiList = $wikiStore->getCourseWikiList();
            }
            else
            {
                $wikiList = $wikiStore->getWikiListByGroup( $groupId );
            }
            break;
        }
    }

    // ------------ End of command processing ---------------

    // javascript

    if ( $action == 'rqEdit' )
    {
        $jspath = document_web_path() . '/lib/javascript';
        $htmlHeadXtra[] = '<script type="text/javascript" src="'.$jspath.'/wiki_acl.js"></script>';
        $claroBodyOnload[] = 'initBoxes();';
    }

    // Breadcrumps

    switch( $action )
    {
        case "rqEdit":
        {
            $navigation[] = array ('url' => 'wiki.php', 'name' => $langWiki );
            $navigation[] = array ('url' => NULL
                , 'name' => $wikiTitle);
            $nameTools = $langWikiProperties;
            $noPHP_SELF = true;
            break;
        }
        case "list":
        default:
        {
            $nameTools = $langWiki;
        }
    }


    // --------- Start of display ----------------

    // toolTitle

    $toolTitle = array();

    if ( $_gid )
    {
		$toolTitle['supraTitle'] = $_group['name'];
    }

    switch( $action )
    {
        // edit form
        case "rqEdit":
        {
            if ( $wikiId == 0 )
            {
                $toolTitle['mainTitle'] = $langWikiTitleNew;
            }
            else
            {
                $toolTitle['mainTitle'] = $langWikiTitleEdit;
                $toolTitle['subTitle'] = $wikiTitle;
            }

            break;
        }
        // list wiki
        case "list":
        {
            $toolTitle['mainTitle'] = sprintf( $langWikiTitlePattern, $langWikiList );

            break;
        }
    }

    switch( $action )
    {
        // an error occurs
        case "error":
        {
            break;
        }
        // edit form
        case "rqEdit":
        {
            $tool_content .= claro_disp_wiki_properties_form( $wikiId, $wikiTitle
                , $wikiDesc, $groupId, $wikiACL );

            break;
        }
        // list wiki
        case "list":
        {
            //find the wiki with recent modification from the notification system

                if (isset($_uid))
                {
                    $date = $claro_notifier->get_notification_date($_uid);
                    $modified_wikis = $claro_notifier->get_notified_ressources($_cid, $date, $_uid, $_gid,12);
                }
                else
                {
                    $modified_wikis = array();
                }

            // if admin, display add new wiki link

            if ( $is_allowedToAdmin )
            {
                $tool_content .= '
              <div id="operations_container">
                <ul id="opslist">
                  <li><a href="'
                    . $_SERVER['PHP_SELF']
                    . '?action=rqEdit'
                    . '">'
                    . $langWikiCreateNewWiki
                    . '</a></li>
                </ul>
              </div>
               ';
            }

    // O titlos tis othonis wiki
    //$tool_content .= disp_tool_title($toolTitle) . "\n";
    if ( ! empty( $message ) )
    {
        //$tool_content .= disp_message_box( $message, $style ) ."<br />" . "\n";

        $tool_content .= "
        <table width=\"99%\">
        <tbody>
        <tr>
          <td class=\"success\">
            <p><b>$message</b></p>
          </td>
        </tr>
        </tbody>
        </table>
        <br />
        ";
    }

            // display list in a table

            $tool_content .= '
        <table width="99%" class="WikiSum">' . "\n";

            // if admin, display title, edit and delete
            if ( $is_allowedToAdmin )
            {
                $tool_content .= '        <thead>' . "\n"
                    . '        <tr class="Wiki_header">' . "\n"
                    . '          <td width="1%">&nbsp;</td>' . "\n"
                    . '          <td><div align="left">'.$langTitle.'</div></td>' . "\n"
                    . '          <td>'.$langDescription.'</td>' . "\n"
                    . '          <td width="15%"><div align="center">'.$langPages.'</div></td>' . "\n"
                    . '          <td width="15%" colspan="3" ><div align="center">'.$langActions.'</div></td>'
                    . '        </tr>' . "\n"
                    . '        </thead>' . "\n"
                    ;
            }
            // else display title only
            else
            {
                $tool_content .= '        <thead>' . "\n"
                    . '        <tr class="Wiki_header">' . "\n"
                    . '          <td width="1%">&nbsp;</td>' . "\n"
                    . '          <td><div align="left">'.$langTitle.'</div></td>' . "\n"
                    . '          <td>'.$langDescription.'</td>' . "\n"
                    . '          <td width="20%"><div align="center">'.$langWikiNumberOfPages.'</div></td>' . "\n"
                    . '          <td width="20%"><div align="center">'.$langWikiRecentChanges.'</div></td>' . "\n"
                    . '        </tr>' . "\n"
                    . '        </thead>' . "\n"
                    ;
            }

            $tool_content .= '        <tbody>' . "\n";

            // wiki list not empty
            if ( is_array( $wikiList ) && count( $wikiList ) > 0 )
            {

                foreach ( $wikiList as $entry )
                {
                    $tool_content .= '        <tr>' . "\n";

                    // display title for all users

                    //modify style if the wiki is recently added or modified since last login

                    if ((isset($_uid) && $claro_notifier->is_a_notified_ressource($_cid, $date, $_uid, $_gid, $_tid, $entry['id'])))
                    {
                        $classItem=" hot";
                    }
                    else // otherwise just display its title normally
                    {
                        $classItem="";
                    }

                    $tool_content .= '          <td>';
                    $tool_content .= '<img src="' . $imgRepositoryWeb . '/arrow_grey.gif" alt="'.$langWiki.'" title="'.$langWiki.'" border="0" />';
                    $tool_content .= '</td>' . "\n";

                    $tool_content .= '          <td>';
                    // display direct link to main page
                    $tool_content .= '<a class="item'.$classItem.'" href="page.php?wikiId='
                        . $entry['id'].'&amp;action=show'
                        . '">'
                        . $entry['title'] . '</a>'
                        ;
                        ;
                    $tool_content .= '</td>' . "\n";

                    $tool_content .= '          <td>';
                   if ( ! empty( $entry['description'] ) )
                    {
                        $tool_content .= ''
                            . $entry['description'].''
                            ;
                    }
                    $tool_content .= '</td>' . "\n";

                    $tool_content .= '          <td><div align="center">';
                    $tool_content .= '<a href="page.php?wikiId=' . $entry['id'] . '&amp;action=all">';
                    $tool_content .= $wikiStore->getNumberOfPagesInWiki( $entry['id'] );
                    $tool_content .= '</a>';
                    $tool_content .= '</div></td>' . "\n";

                    $tool_content .= '          <td style="text-align: center;">';
                    // display direct link to main page
                    $tool_content .= '<a href="page.php?wikiId='
                        . $entry['id'].'&amp;action=recent'
                        . '">'
                        . '<img src="' . $imgRepositoryWeb . '/history.gif" border="0" alt="'.$langWikiRecentChanges.'" title="'.$langWikiRecentChanges.'" />'
                        . '</a>'
                        ;
                        ;
                    $tool_content .= '</td>' . "\n";

                    // if admin, display edit and delete links

                    if ( $is_allowedToAdmin )
                    {
                        // edit link

                        $tool_content .= '          <td style="text-align: center;">';
                        $tool_content .= '<a href="'.$_SERVER['PHP_SELF'].'?wikiId='
                            . $entry['id'].'&amp;action=rqEdit'
                            . '">'
                            . '<img src="'.$imgRepositoryWeb.'/edit.gif" border="0" alt="'.$langWikiEditProperties.'" title="'.$langWikiEditProperties.'" />'
                            . '</a>'
                            ;

                        $tool_content .= '</td>' . "\n";

                        // delete link

                        $tool_content .= '<td style="text-align: center;">';
                        $tool_content .= '<a href="'.$_SERVER['PHP_SELF'].'?wikiId='
                            . $entry['id'].'&amp;action=exDelete'
                            . '">'
                            . '<img src="'.$imgRepositoryWeb.'/delete.gif" border="0" alt="'.$langDelete.'" title="'.$langDelete.'" onClick="return confirmation();"/>'
                            . '</a>'
                            ;
                        $tool_content .= '</td>' . "\n";
                    }

                    $tool_content .= '        </tr>' . "\n";
                }
            }
            // wiki list empty
            else
            {
                $tool_content .= '        <tr><td colspan="5" style="text-align: center;">'.$langWikiNoWiki.'</td></tr>' . "\n";
            }

            $tool_content .= '        </tbody>' . "\n";
            $tool_content .= '        </table>' . "\n" . "\n";

            break;
        }
        default:
        {
            trigger_error( "Invalid action supplied to " . $_SERVER['PHP_SELF']
                , E_USER_ERROR
                );
        }
    }

    // ------------ End of display ---------------
add_units_navigation(TRUE);
draw($tool_content, 2, "wiki", $head_content);

