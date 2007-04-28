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
     * returns the current document web path
     */
    function document_web_path()
    {
        return "http://" . $_SERVER['HTTP_HOST'] . dirname( $_SERVER['SCRIPT_NAME'] );
    }

    /**
     * returns the current document system path
     */
    function document_sys_path()
    {
        return realpath( str_replace( '\\', '/', $_SERVER['DOCUMENT_ROOT'] ) . dirname( $_SERVER['SCRIPT_NAME'] ) );
    }
    
    // remove from claroline version
    
    function add_check_if_javascript_enabled_js()
    {
        return '<script type="text/javascript">document.cookie="javascriptEnabled=true";</script>';
    }
    
    function is_javascript_enabled()
    {
        return isset( $_COOKIE['javascriptEnabled'] )
            && ( $_COOKIE['javascriptEnabled'] == true )
            ;
    }
?>
