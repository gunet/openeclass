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


    require_once dirname(__FILE__) . "/class.clarodbconnection.php";
	require_once dirname(__FILE__) . "/class.wikiaccesscontrol.php";
	require_once dirname(__FILE__) . "/class.wikistore.php";
	require_once dirname(__FILE__) . "/class.wikipage.php";
	require_once dirname(__FILE__) . "/class.wiki.php";
	require_once dirname(__FILE__) . "/lib.wikisql.php";
	
	
	function create_wiki( $gid = false, $wikiName = 'New wiki' )
    {
		global $_uid;
		
		$creatorId = $_uid;
		
		$tblList = claro_sql_get_course_tbl();

		$config = array();
		$config["tbl_wiki_properties"] = $tblList[ "wiki_properties" ];
		$config["tbl_wiki_pages"] = $tblList[ "wiki_pages" ];
		$config["tbl_wiki_pages_content"] = $tblList[ "wiki_pages_content" ];
		$config["tbl_wiki_acls"] = $tblList[ "wiki_acls" ];

		$con = new ClarolineDatabaseConnection();
		
		$acl = array();
		
		if ( $gid )
        {
            $acl = WikiAccessControl::defaultGroupWikiACL();
        }
        else
        {
            $acl = WikiAccessControl::defaultCourseWikiACL();
        }
        
        $wiki = new Wiki( $con, $config );
		$wiki->setTitle( $wikiName );
        $wiki->setDescription( 'This is a sample wiki' );
        $wiki->setACL( $acl );
        $wiki->setGroupId( $gid );
        $wikiId = $wiki->save();
        $wikiTitle = $wiki->getTitle();
                
        $mainPageContent = sprintf( "This is the main page of the Wiki %s. Click on edit to modify the content.", $wikiTitle );
                
        $wikiPage = new WikiPage( $con, $config, $wikiId );
        $wikiPage->create( $creatorId
			, '__MainPage__'
			, $mainPageContent
			, date( "Y-m-d H:i:s" )
			, true );
			
		echo $con->getError();
    }
    
    function delete_wiki( $groupId )
    {
		$tblList = claro_sql_get_course_tbl();
		
		$config = array();
		$config["tbl_wiki_properties"] = $tblList[ "wiki_properties" ];
		$config["tbl_wiki_pages"] = $tblList[ "wiki_pages" ];
		$config["tbl_wiki_pages_content"] = $tblList[ "wiki_pages_content" ];
		$config["tbl_wiki_acls"] = $tblList[ "wiki_acls" ];
		
		$con = new ClarolineDatabaseConnection();
		
		$store = new WikiStore( $con, $config );
		
		if ( strtoupper($groupId) == 'ALL' )
		{
			$wikiList = $store->getGroupWikiList();
		}
		else
		{
			$wikiList = $store->getWikiListByGroup( $groupId );
		}
		
		// var_dump( $wikiList );
		
		if ( is_array( $wikiList ) && count( $wikiList ) > 0 )
		{
		  foreach ( $wikiList as $wiki )
		  {
			     $store->deleteWiki( $wiki['id'] );
		  }
        }
    }
    
    function delete_group_wikis( $groupIdList = 'ALL' )
    {
		// echo "passed here";
		if ( strtoupper($groupIdList) == 'ALL' )
		{
			delete_wiki( 'ALL' );
		}
		elseif( is_array( $groupIdList ) && count( $groupIdList ) > 0 )
		{
			foreach ( $groupIdList as $groupId )
			{
				// echo "passed here";
				delete_wiki( $groupId );
			}
		}
		else
		{
			delete_wiki( $groupIdList );
		}
    }
?>