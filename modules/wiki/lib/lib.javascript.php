<?php

/**=============================================================================
       	GUnet eClass 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2007  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
                     Yannis Exidaridis <jexi@noc.uoa.gr> 
                     Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/**===========================================================================
	lib.javascript.php
	@last update: 15-05-2007 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               
	based on Claroline version 1.7.9 licensed under GPL
	      copyright (c) 2001, 2007 Universite catholique de Louvain (UCL)
	      
	      original file: lib.javascript Revision: 1.6.2.2
	      
	Claroline authors: Frederic Minne <zefredz@gmail.com>
==============================================================================        
    @Description: 

    @Comments:
 
    @todo: 
==============================================================================
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
