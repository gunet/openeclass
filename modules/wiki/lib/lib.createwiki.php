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
	lib.createwiki.php
	@last update: 15-05-2007 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               
	based on Claroline version 1.7.9 licensed under GPL
	      copyright (c) 2001, 2007 Universite catholique de Louvain (UCL)
	      
	      original file: lib.createwiki Revision: 1.4.2.2
	      
	Claroline authors: Frederic Minne <zefredz@gmail.com>
==============================================================================        
    @Description: 

    @Comments:
 
    @todo: 
==============================================================================
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
