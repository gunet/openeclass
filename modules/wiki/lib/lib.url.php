<?php
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

/**===========================================================================
	lib.url.php
	@last update: 15-05-2007 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               
	based on Claroline version 1.7.9 licensed under GPL
	      copyright (c) 2001, 2007 Universite catholique de Louvain (UCL)
	      
	      original file: lib.url Revision: 1.7.2.2
	      
	Claroline authors: Frederic Minne <zefredz@gmail.com>
==============================================================================        
    @Description: 

    @Comments:
 
    @todo: 
==============================================================================
*/

     /**
      * add a GET request variable to the given URL
      * @param string url url
      * @param string name name of the variable
      * @param string value value of the variable
      * @return string url
      */
    function add_request_variable_to_url( &$url, $name, $value )
    {
        if ( strstr( $url, "?" ) != false )
        {
            $url .= "&amp;$name=$value";
        }
        else
        {
            $url .= "?$name=$value";
        }
        
        return $url;
    }
    
    /**
      * add a GET request variable list to the given URL
      * @param string url url
      * @param array variableList list of the request variables to add
      * @return string url
      */
    function add_request_variable_list_to_url( &$url, $variableList )
    {
        foreach ( $variableList as $name => $value )
        {
            $url = add_request_variable_to_url( $url, $name, $value );
        }
        
        return $url;
    }
?>
