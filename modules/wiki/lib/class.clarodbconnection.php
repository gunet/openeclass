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

    class ClarolineDatabaseConnection extends DatabaseConnection
    {
        function ClarolineDatabaseConnection()
        {
            // use only in claroline tools
        }

        function setError( $errmsg = '', $errno = 0 )
        {
            if ( $errmsg != '' )
            {
                $this->error = $errmsg;
                $this->errno = $errno;
            }
            else
            {
                $this->error = ( @mysql_error() !== false ) ? @mysql_error() : 'Unknown error';
                $this->errno = ( @mysql_errno() !== false ) ? @mysql_errno() : 0;
            }

            $this->connected = false;
        }

        function connect()
        {

        }

        function close()
        {

        }

        function executeQuery( $sql )
        {
            db_query( $sql );

            if( @mysql_errno() != 0 )
            {
                $this->setError();

                return 0;
            }

            return @mysql_affected_rows( );
        }

        function getAllObjectsFromQuery( $sql )
        {
            $result = db_query( $sql );

            if ( @mysql_num_rows( $result ) > 0 )
            {
                $ret= array();

                while( ( $item = @mysql_fetch_object( $result ) ) != false )
                {
                    $ret[] = $item;
                }
            }
            else
            {
                $this->setError();

                @mysql_free_result( $result );

                return null;
            }

            @mysql_free_result( $result );

            return $ret;
        }

        function getObjectFromQuery( $sql )
        {
            $result = db_query( $sql );

            if ( ( $item = @mysql_fetch_object( $result ) ) != false )
            {
                @mysql_free_result( $result );

                return $item;
            }
            else
            {
                $this->setError();

                @mysql_free_result( $result );
                return null;
            }
        }

        function getAllRowsFromQuery( $sql )
        {
            $result = db_query( $sql );

            if ( @mysql_num_rows( $result ) > 0 )
            {
                $ret= array();

                while ( ( $item = @mysql_fetch_array( $result ) ) != false )
                {
                    $ret[] = $item;
                }
            }
            else
            {
                $this->setError();

                @mysql_free_result( $result );

                return null;
            }

            @mysql_free_result( $result );

            return $ret;
        }

        function getRowFromQuery( $sql )
        {
            $result = db_query( $sql );

            if ( ( $item = @mysql_fetch_array( $result ) ) != false )
            {
                @mysql_free_result( $result );

                return $item;
            }
            else
            {
                $this->setError();

                @mysql_free_result( $result );

                return null;
            }
        }

        function queryReturnsResult( $sql )
        {
            $result = db_query( $sql );

            if ( @mysql_errno() == 0 )
            {

                if ( @mysql_num_rows( $result ) > 0 )
                {
                    @mysql_free_result( $result );

                    return true;
                }
                else
                {
                    @mysql_free_result( $result );

                    return false;
                }
            }
            else
            {
                $this->setError();

                return false;
            }
        }

        function getLastInsertID()
        {
            if ( $this->hasError() )
            {
                return 0;
            }
            else
            {
                return mysql_insert_id();
            }
        }
    }
?>
