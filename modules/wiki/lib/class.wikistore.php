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



    require_once dirname(__FILE__) . "/class.dbconnection.php";
    require_once dirname(__FILE__) . "/class.wiki.php";
    
    // Error codes
    !defined("WIKI_NO_TITLE_ERROR") && define( "WIKI_NO_TITLE_ERROR", "Missing title" );
    !defined("WIKI_NO_TITLE_ERRNO") && define( "WIKI_NO_TITLE_ERRNO", 1 );
    !defined("WIKI_ALREADY_EXISTS_ERROR") && define( "WIKI_ALREADY_EXISTS_ERROR", "Wiki already exists" );
    !defined("WIKI_ALREADY_EXISTS_ERRNO") && define( "WIKI_ALREADY_EXISTS_ERRNO", 2 );
    !defined( "WIKI_CANNOT_BE_UPDATED_ERROR") && define( "WIKI_CANNOT_BE_UPDATED_ERROR", "Wiki cannot be updated" );
    !defined( "WIKI_CANNOT_BE_UPDATED_ERRNO") && define( "WIKI_CANNOT_BE_UPDATED_ERRNO", 3 );
    !defined( "WIKI_NOT_FOUND_ERROR") && define( "WIKI_NOT_FOUND_ERROR", "Wiki not found" );
    !defined( "WIKI_NOT_FOUND_ERRNO") && define( "WIKI_NOT_FOUND_ERRNO", 4 );
    
    /**
     * Class representing the WikiStore
     * (ie the place where the wiki are stored)
     */
    class WikiStore
    {
        // private fields
        var $con;
        
        // default configuration
        var $config = array(
                'tbl_wiki_pages' => 'wiki_pages',
                'tbl_wiki_pages_content' => 'wiki_pages_content',
                'tbl_wiki_properties' => 'wiki_properties',
                'tbl_wiki_acls' => 'wiki_acls'
            );
            
        // error handling
        var $error = '';
        var $errno = 0;
        
        /**
         * Constructor
         * @param DatabaseConnection con connection to the database
         * @param array config associative array containing tables name
         */
        function WikiStore( &$con, $config = null )
        {
            if ( is_array( $config ) )
            {
                $this->config = array_merge( $this->config, $config );
            }
            $this->con = $con;
        }
        
        // load and save
        /**
         * Load a Wiki
         * @param int wikiId ID of the Wiki
         * @return Wiki the loaded Wiki
         */
        function loadWiki( $wikiId )
        {
            $wiki = new Wiki( $this->con, $this->config );
            
            $wiki->load( $wikiId );
            
            if ( $wiki->hasError() )
            {
                $this->setError( $wiki->error, $wiki->errno );
            }
            
            return $wiki;
        }
        
        /**
         * Check if a page exists in a given wiki
         * @param int wikiId ID of the Wiki
         * @param string title page title
         * @return boolean
         */
        function pageExists( $wikiId, $title )
        {
            // reconnect if needed
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }

            $sql = "SELECT `id` "
                . "FROM `".$this->config['tbl_wiki_pages']."` "
                . "WHERE BINARY `title` = '".addslashes( $title )."' "
                . "AND `wiki_id` = " . $wikiId
                ;

            return $this->con->queryReturnsResult( $sql );
        }
        
        /**
         * Check if a wiki exists usind its ID
         * @param int id wiki ID
         * @return boolean
         */
        function wikiIdExists( $wikiId )
        {
            // reconnect if needed
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }

            $sql = "SELECT `id` "
                . "FROM `".$this->config['tbl_wiki_properties']."` "
                . "WHERE `id` = '".$wikiId."'"
                ;

            return $this->con->queryReturnsResult( $sql );
        }
        
        // Wiki methods

        /**
         * Get the list of the wiki's for a given group
         * @param int groupId ID of the group, Zero for a course
         * @return array list of the wiki's for the given group
         */
        function getWikiListByGroup( $groupId )
        {
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }
            
            $sql = "SELECT `id`, `title`, `description` "
                . "FROM `".$this->config['tbl_wiki_properties']."` "
                . "WHERE `group_id` = ".$groupId . " "
                . "ORDER BY `id` ASC"
                ;
                
            return $this->con->getAllRowsFromQuery( $sql );
        }
        
        /**
         * Get the list of the wiki's in a course
         * @return array list of the wiki's in the course
         * @see WikiStore::getWikiListByGroup( $groupId )
         */
        function getCourseWikiList( )
        {
            return $this->getWikiListByGroup( 0 );
        }
        
        /**
         * Get the list of the wiki's in all groups (exept course wiki's)
         * @return array list of all the group wiki's
         */
        function getGroupWikiList()
        {
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }
            
            $sql = "SELECT `id`, `title`, `description` "
                . "FROM `".$this->config['tbl_wiki_properties']."` "
                . "WHERE `group_id` != 0 "
                . "ORDER BY `group_id` ASC"
                ;
                
            return $this->con->getAllRowsFromQuery( $sql );
        }
        
        function getNumberOfPagesInWiki( $wikiId )
        {
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }

            if ( $this->wikiIdExists( $wikiId ) )
            {
                $sql = "SELECT count( `id` ) as `pages` "
                    . "FROM `".$this->config['tbl_wiki_pages']."` "
                    . "WHERE `wiki_id` = " . $wikiId
                    ;
                    
                $result = $this->con->getRowFromQuery( $sql );
                
                return $result['pages'];
            }
            else
            {
                $this->setError( WIKI_NOT_FOUND_ERROR, WIKI_NOT_FOUND_ERRNO );
                return false;
            }
        }
        
        /**
         * Delete a Wiki from the store
         * @param int wikiId ID of the wiki
         * @return boolean true on success, false on failure
         */
        function deleteWiki( $wikiId )
        {
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }
            
            if ( $this->wikiIdExists( $wikiId ) )
            {
                // delete properties
                $sql = "DELETE FROM `".$this->config['tbl_wiki_properties']."` "
                    . "WHERE `id` = " . $wikiId
                    ;
                    
                $numrows = $this->con->executeQuery( $sql );
                
                if ( $numrows < 1 || $this->hasError() )
                {
                    return false;
                }
                
                // delete wiki acl
                $sql = "DELETE FROM `".$this->config['tbl_wiki_acls']."` "
                    . "WHERE `wiki_id` = " . $wikiId
                    ;
                    
                $numrows = $this->con->executeQuery( $sql );

                if ( $numrows < 1 || $this->hasError() )
                {
                    return false;
                }
                
                $sql = "SELECT `id` "
                    . "FROM `" . $this->config['tbl_wiki_pages'] . "` "
                    . "WHERE `wiki_id` = " . $wikiId
                    ;
                    
                $pageIds = $this->con->getAllRowsFromQuery( $sql );
                
                if ( $this->hasError() )
                {
                    return false;
                }
                
                foreach ( $pageIds as $pageId )
                {
                    $sql = "DELETE "
                        . "FROM `".$this->config['tbl_wiki_pages_content']."` "
                        . "WHERE `pid` = " . $pageId['id']
                        ;
                        
                    $this->con->executeQuery( $sql );
                    
                    if ( $this->hasError() )
                    {
                        return false;
                    }
                }
                
#                // delete wiki pages
#                $sql = "DELETE `content`.* "
#                    . "FROM `"
#                    . $this->config['tbl_wiki_pages_content']."` `content`, `"
#                    . $this->config['tbl_wiki_pages'] . "` `pages`"
#                    . "WHERE `pages`.`wiki_id` = " . $wikiId . " "
#                    . "AND `content`.`pid` = `pages`.`id`"
#                    ;
#
#                $numrows = $this->con->executeQuery( $sql );
#                
#                if ( $this->hasError() )
#                {
#                    return false;
#                }
                
                $sql = "DELETE FROM `".$this->config['tbl_wiki_pages']."` "
                    . "WHERE `wiki_id` = " . $wikiId
                    ;
                    
                $numrows = $this->con->executeQuery( $sql );

                if ( $this->hasError() )
                {
                    return false;
                }
                
                return true;
            }
            else
            {
                $this->setError( WIKI_NOT_FOUND_ERROR, WIKI_NOT_FOUND_ERRNO );
                return false;
            }
        }
        
        // error handling

        function setError( $errmsg = '', $errno = 0 )
        {
            $this->error = ($errmsg != '') ? $errmsg : "Unknown error";
            $this->errno = $errno;
        }

        function getError()
        {
            if ( $this->con->hasError() )
            {
                return $this->con->getError();
            }
            else if ($this->error != '')
            {
                $errno = $this->errno;
                $error = $this->error;
                $this->error = '';
                $this->errno = 0;
                return $errno.' - '.$error;
            }
            else
            {
                return false;
            }
        }

        function hasError()
        {
            return ( $this->error != '' ) || $this->con->hasError();
        }
    }
?>
