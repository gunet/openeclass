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


    /**
     * create wiki tables in devel/upgrade mode
     * @param DatabaseConnection con database connection
     * @param boolean drop_tables drop existing tables
     */
    function init_wiki_tables( &$con, $drop_tables = false )
    {
        // get claro db names using claro_get_course_tbl_name()
        $tblList = claro_sql_get_course_tbl();
        $tblWikiProperties = $tblList[ 'wiki_properties' ];
        $tblWikiPages = $tblList[ 'wiki_pages' ];
        $tblWikiPagesContent = $tblList[ 'wiki_pages_content' ];
        $tblWikiAcls = $tblList[ 'wiki_acls' ];
        
        $con->connect();

        // drop tables

        if ( $drop_tables === true )
        {
            $sql = "DROP TABLE IF EXISTS `$tblWikiPages`";
            $con->executeQuery( $sql );

            $sql = "DROP TABLE IF EXISTS `$tblWikiPagesContent`";
            $con->executeQuery( $sql );

            $sql = "DROP TABLE IF EXISTS `$tblWikiProperties`";
            $con->executeQuery( $sql );
            
            $sql = "DROP TABLE IF EXISTS `$tblWikiAcls`";
            $con->executeQuery( $sql );
        }

        // init page table

        $sql = "CREATE TABLE IF NOT EXISTS `$tblWikiPages` (
            `id` int(11) unsigned NOT NULL auto_increment,
            `wiki_id` int(11) unsigned NOT NULL default '0',
            `owner_id` int(11) unsigned NOT NULL default '0',
            `title` varchar(255) NOT NULL default '',
            `ctime` datetime NOT NULL default '0000-00-00 00:00:00',
            `last_version` int(11) unsigned NOT NULL default '0',
            `last_mtime` datetime NOT NULL default '0000-00-00 00:00:00',
            PRIMARY KEY  (`id`)
            )"
            ;

        $con->executeQuery( $sql );

        // init version table

        $sql = "CREATE TABLE IF NOT EXISTS `$tblWikiPagesContent` (
            `id` int(11) unsigned NOT NULL auto_increment,
            `pid` int(11) unsigned NOT NULL default '0',
            `editor_id` int(11) NOT NULL default '0',
            `mtime` datetime NOT NULL default '0000-00-00 00:00:00',
            `content` text NOT NULL,
            PRIMARY KEY  (`id`)
            )"
            ;

        $con->executeQuery( $sql );

        $sql = "CREATE TABLE IF NOT EXISTS `$tblWikiProperties`(
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL DEFAULT '',
            `description` TEXT NULL,
            `group_id` INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY(`id`)
            )"
            ;

        $con->executeQuery( $sql );
        
        $sql = "CREATE TABLE IF NOT EXISTS `$tblWikiAcls` (
                    `wiki_id` INT(11) UNSIGNED NOT NULL,
                    `flag` VARCHAR(255) NOT NULL,
                    `value` ENUM('false','true') NOT NULL DEFAULT 'false'
                )"
                ;
        $con->executeQuery( $sql );
    }
    
    /**
     * create wiki MainPage
     * @param DatabaseConnection con database connection
     * @param int wikiId ID of the Wiki the page belongs to
     * @param int creatorId ID of the user who creates the page
     * @return boolean true if the creation succeeds, false if it fails
     */
    function init_wiki_main_page( &$con, $wikiId, $creatorId, $wikiTitle )
    {
        global $langWikiMainPageContent;
        
        $tblList = claro_sql_get_course_tbl();

        $mainPageContent = sprintf( $langWikiMainPageContent, $wikiTitle = '' );
        
        $config = array();
        // use claro functions
        $config["tbl_wiki_pages"] = $tblList[ "wiki_pages" ];
        $config["tbl_wiki_pages_content"] = $tblList[ "wiki_pages_content" ];
        
        $wikiPage = new WikiPage( $con, $config, $wikiId );
        
        $wikiPage->create( $creatorId, '__MainPage__'
            , $mainPageContent, date( "Y-m-d H:i:s" ), true );
            
        return (! ( $wikiPage->hasError() ));
    }
    
#    /**
#     * Create a sample wiki in a given course or group
#     * Not used at this time
#     * @param DatabaseConnection con database connection
#     * @param int creatorId ID of the user who creates the page
#     * @param int groupId ID of the group, if course wiki set it to Zero
#     * @return boolean true if the creation succeeds, false if it fails
#     */
#    function create_sample_wiki( &$con, $creatorId, $groupId = 0 )
#    {
#        global $langWikiSampleTitle, $langWikiSampleDescription;
#        
#        $config = array();
#        // use claro functions
#        $tblList = claro_sql_get_course_tbl();
#        $config["tbl_wiki_pages"] = $tblList[ "wiki_pages" ];
#        $config["tbl_wiki_pages_content"] = $tblList[ "wiki_pages_content" ];
#        $config["tbl_wiki_properties"] = $tblList[ "wiki_properties" ];
#        $config["tbl_wiki_acls"] = $tblList[ "wiki_acls" ];
#        
#        $wiki = new Wiki( $con, $config );
#        
#        $wiki->setTitle( $langWikiSampleTitle );
#        $wiki->setDescription( $langWikiSampleDescription );
#        $wiki->setGroupId( $groupId );
#        
#        if ( $groupId != 0 )
#        {
#            $acl = array(
#                'course_read' => true,
#                'course_edit' => true,
#                'course_create' => true,
#                'group_read' => false,
#                'group_edit' => false,
#                'group_create' => false,
#                'other_read' => true,
#                'other_edit' => false,
#                'other_create' => false
#            );
#            
#        }
#        else
#        {
#            $acl = array(
#                'course_read' => true,
#                'course_edit' => false,
#                'course_create' => false,
#                'group_read' => true,
#                'group_edit' => true,
#                'group_create' => true,
#                'other_read' => false,
#                'other_edit' => false,
#                'other_create' => false
#            );
#        }
#        
#        $wiki->setACL( $acl );
#        $wikiId = $wiki->save();
#        
#        return init_wiki_main_page( $con, $wikiId, $creatorId );
#    }
?>
