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
    require_once dirname(__FILE__) . "/class.wikipage.php";

    // Error codes
    !defined( "WIKI_NO_TITLE_ERROR" ) && define( "WIKI_NO_TITLE_ERROR", "Missing title" );
    !defined( "WIKI_NO_TITLE_ERRNO" ) && define( "WIKI_NO_TITLE_ERRNO", 1 );
    !defined( "WIKI_ALREADY_EXISTS_ERROR" ) && define( "WIKI_ALREADY_EXISTS_ERROR", "Wiki already exists" );
    !defined( "WIKI_ALREADY_EXISTS_ERRNO" ) && define( "WIKI_ALREADY_EXISTS_ERRNO", 2 );
    !defined( "WIKI_CANNOT_BE_UPDATED_ERROR" ) && define( "WIKI_CANNOT_BE_UPDATED_ERROR", "Wiki cannot be updated" );
    !defined( "WIKI_CANNOT_BE_UPDATED_ERRNO" ) && define( "WIKI_CANNOT_BE_UPDATED_ERRNO", 3 );
    !defined( "WIKI_NOT_FOUND_ERROR" ) && define( "WIKI_NOT_FOUND_ERROR", "Wiki not found" );
    !defined( "WIKI_NOT_FOUND_ERRNO" ) && define( "WIKI_NOT_FOUND_ERRNO", 4 );

    /**
     * This class represents a Wiki
     */
    class Wiki
    {
        var $wikiId;
        var $title;
        var $desc;
        var $accessControlList;
        var $groupId;
        
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
        function Wiki( &$con, $config = null )
        {
            if ( is_array( $config ) )
            {
                $this->config = array_merge( $this->config, $config );
            }
            $this->con =& $con;
            
            $this->wikiId = 0;
        }
        
        // accessors

        /**
         * Set Wiki title
         * @param string wikiTitle
         */
        function setTitle( $wikiTitle )
        {
            $this->title = $wikiTitle;
        }

        /**
         * Get the Wiki title
         * @return string title of the wiki
         */
        function getTitle()
        {
            return $this->title;
        }

        /**
         * Set the description of the Wiki
         * @param string wikiDesc description of the wiki
         */
        function setDescription( $wikiDesc = '' )
        {
            $this->desc = $wikiDesc;
        }

        /**
         * Get the description of the Wiki
         * @param string description of the wiki
         */
        function getDescription()
        {
            return $this->desc;
        }

        /**
         * Set the access control list of the Wiki
         * @param array accessControlList wiki access control list
         */
        function setACL( $accessControlList )
        {
            $this->accessControlList = $accessControlList;
        }

        /**
         * Get the access control list of the Wiki
         * @return array wiki access control list
         */
        function getACL()
        {
            return $this->accessControlList;
        }

        /**
         * Set the group ID of the Wiki
         * @param int groupId group ID
         */
        function setGroupId( $groupId )
        {
            $this->groupId = $groupId;
        }

        /**
         * Get the group ID of the Wiki
         * @return int group ID
         */
        function getGroupId()
        {
            return $this->groupId;
        }

        /**
         * Set the ID of the Wiki
         * @param int wikiId ID of the Wiki
         */
        function setWikiId( $wikiId )
        {
            $this->wikiId = $wikiId;
        }
        
        /**
         * Set the ID of the Wiki
         * @return int ID of the Wiki
         */
        function getWikiId()
        {
            return $this->wikiId;
        }
        
        // load and save

        /**
         * Load a Wiki
         * @param int wikiId ID of the Wiki
         */
        function load( $wikiId )
        {
            if( $this->wikiIdExists($wikiId) )
            {
                $this->loadProperties( $wikiId );
                $this->loadACL( $wikiId );
            }
            else
            {
                $this->setError( WIKI_NOT_FOUND_ERROR, WIKI_NOT_FOUND_ERRNO );
            }
        }
        
        /**
         * Load the properties of the Wiki
         * @param int wikiId ID of the Wiki
         */
        function loadProperties( $wikiId )
        {
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }

            $sql = "SELECT `id`, `title`, `description`, `group_id` "
                . "FROM `".$this->config['tbl_wiki_properties']."` "
                . "WHERE `id` = ".$wikiId
                ;
                
            $result = $this->con->getRowFromQuery( $sql );

            $this->setWikiId( $result['id'] );
            $this->setTitle( stripslashes( $result['title'] ) );
            $this->setDescription( stripslashes( $result['description'] ) );
            $this->setGroupId($result['group_id']);
        }
        
        /**
         * Load the access control list of the Wiki
         * @param int wikiId ID of the Wiki
         */
        function loadACL( $wikiId )
        {
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }

            $sql = "SELECT `flag`, `value` "
                . "FROM `".$this->config['tbl_wiki_acls']."` "
                . "WHERE `wiki_id` = " . $wikiId
                ;

            $result = $this->con->getAllRowsFromQuery( $sql );
            
            $acl = array();
            
            if( is_array( $result ) )
            {
                foreach ( $result as $row )
                {
                    $value = ( $row['value'] == 'true' ) ? true : false;
                    $acl[$row['flag']] = $value;
                }
            }

            $this->setACL( $acl );
        }

        /**
         * Save the Wiki
         */
        function save()
        {
            $this->saveProperties();
            
            $this->saveACL();
            
            if ( $this->hasError() )
            {
                return 0;
            }
            else
            {
                return $this->wikiId;
            }
        }
        
        /**
         * Save the access control list of the Wiki
         */
        function saveACL()
        {
            // reconnect if needed
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }
            

            $sql = "SELECT `wiki_id` FROM `"
                . $this->config['tbl_wiki_acls']."` "
                . "WHERE `wiki_id` = " . $this->getWikiId()
                ;

            // wiki already exists
            if ( $this->con->queryReturnsResult( $sql ) )
            {
                $acl = $this->getACL();
                    
                foreach ( $acl as $flag => $value )
                {
                    $value = ( $value == false ) ? 'false' : 'true';

                    $sql = "UPDATE `" . $this->config['tbl_wiki_acls'] . "` "
                        . "SET `value`='" . $value . "'"
                        . "WHERE `wiki_id`=" . $this->getWikiId() . " "
                        . "AND `flag`='" . $flag . "'"
                        ;

                    $this->con->executeQuery( $sql );
                }
            }
            // new wiki
            else
            {
                $acl = $this->getACL();

                foreach ( $acl as $flag => $value )
                {
                    $value = ( $value == false ) ? 'false' : 'true';

                    $sql = "INSERT INTO "
                        . "`".$this->config['tbl_wiki_acls']."`"
                        . "("
                        . "`wiki_id`, `flag`, `value`"
                        . ") "
                        . "VALUES("
                        . $this->getWikiId() . ","
                        . "'" . $flag . "',"
                        . "'" . $value . "'"
                        . ")"
                        ;

                    $this->con->executeQuery( $sql );
                }
            }
        }
        
        /**
         * Save the properties of the Wiki
         */
        function saveProperties()
        {
            // reconnect if needed
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }
            
            // new wiki
            if ( $this->getWikiId() === 0 )
            {
                // INSERT PROPERTIES
                $sql = "INSERT INTO `"
                    . $this->config['tbl_wiki_properties']
                    . "`("
                    . "`title`,`description`,`group_id`"
                    . ") "
                    . "VALUES("
                    . "'". addslashes( $this->getTitle() ) ."', "
                    . "'" . addslashes( $this->getDescription() ) . "', "
                    . "'" . $this->getGroupId() . "'"
                    . ")"
                    ;
                    
                // GET WIKIID
                $this->con->executeQuery( $sql );

                if ( ! $this->con->hasError() )
                {
                    $wikiId = $this->con->getLastInsertId();
                    $this->setWikiId( $wikiId );
                }
            }
            // Wiki already exists
            else
            {
                // UPDATE PROPERTIES
                $sql = "UPDATE `" . $this->config['tbl_wiki_properties'] . "` "
                    . "SET "
                    . "`title`='".addslashes($this->getTitle())."', "
                    . "`description`='".addslashes($this->getDescription())."', "
                    . "`group_id`='".$this->getGroupId()."' "
                    . "WHERE `id`=" . $this->getWikiId()
                    ;
                    
                $this->con->executeQuery( $sql );
            }
        }
        
        // utility methods

        /**
         * Check if a page exists in the wiki
         * @param string title page title
         * @return boolean
         */
        function pageExists( $title )
        {
            // reconnect if needed
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }

            $sql = "SELECT `id` "
                . "FROM `".$this->config['tbl_wiki_pages']."` "
                . "WHERE BINARY `title` = '".addslashes($title)."' "
                . "AND `wiki_id` = " . $this->wikiId
                ;

            return $this->con->queryReturnsResult( $sql );
        }
        
        /**
         * Check if a wiki exists using its title
         * @param string title wiki title
         * @return boolean
         */
        function wikiExists( $title )
        {
            // reconnect if needed
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }

            $sql = "SELECT `id` "
                . "FROM `".$this->config['tbl_wiki_properties']."` "
                . "WHERE `title` = '".addslashes($title)."'"
                ;

            return $this->con->queryReturnsResult( $sql );
        }
        
        /**
         * Check if a wiki exists usind its ID
         * @param int id wiki ID
         * @return boolean
         */
        function wikiIdExists( $id )
        {
            // reconnect if needed
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }

            $sql = "SELECT `id` "
                . "FROM `".$this->config['tbl_wiki_properties']."` "
                . "WHERE `id` = '".$id."'"
                ;

            return $this->con->queryReturnsResult( $sql );
        }
        
        /**
         * Get all the pages of this wiki (at this time the method returns
         * only the titles of the pages...)
         * @return array containing thes pages
         */
        function allPages()
        {
            // reconnect if needed
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }
            
            $sql = "SELECT `title` "
                . "FROM `".$this->config['tbl_wiki_pages']."` "
                . "WHERE `wiki_id` = " . $this->getWikiId() . " "
                . "ORDER BY `title` ASC"
                ;
                
            return $this->con->getAllRowsFromQuery( $sql );
        }
        
        /**
         * Get recently modified wiki pages
         * @param int offset start at given offset
         * @param int count number of record to return starting at offset
         * @return array recently modified pages (title, last_mtime, editor_id)
         */
        function recentChanges( $offset = 0, $count = 50 )
        {
            // reconnect if needed
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }
            
            $limit = ($count == 0 ) ? "" : "LIMIT " . $offset . ", " . $count;
            
            $sql = "SELECT `page`.`title`, `page`.`last_mtime`, `content`.`editor_id` "
                . "FROM `".$this->config['tbl_wiki_pages']."` `page`, "
                . "`".$this->config['tbl_wiki_pages_content']."` `content` "
                . "WHERE `page`.`wiki_id` = " . $this->getWikiId() . " "
                . "AND `page`.`last_version` = `content`.`id` "
                . "ORDER BY `page`.`last_mtime` DESC "
                . $limit
                ;
                
            return $this->con->getAllRowsFromQuery( $sql );
        }
        
        // error handling

        function setError( $errmsg = '', $errno = 0 )
        {
            $this->error = ($errmsg != '') ? $errmsg : 'Unknown error';
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
