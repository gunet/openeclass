<?
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Agorastos Sakis <th_agorastos@hotmail.com>

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

//  elegxos gia to pou vrisketai o xrhsths sto systhma kai redirect sto antistoixo script anazhthshs
//  oi diathesimes katastaseis einai oi ekseis:
//
//  1. sthn kentrikh selida tou systhmatos (den exei ginei log-in)
//
//  2. sthn kentrikh selida twn mathimatwn (amesws meta to log-in)
//
//  3. sthn kentrikh selida mathimatos (exei ginei log-in kai o xrhsths eigaxthhke se mathima)
//


//elegxos ean o xrhsths vrisketai sthn kentrikh selida tou systhmatos xwris na exei kanei login
if (@empty($uid))
{	
	include 'search_loggedout.php';
	
}else 
{
	//elegxos ean o xrhsths einai entos kapoiou mathimatos
	if (@$require_current_course == FALSE) 
	{
		include 'search_loggedin.php';
		
	}else 
	{
		//elegxos ean o xrhsths exei kanei login alla *den* einai entos mathimatos
		include 'search_incourse.php';	
		
	}
		
}



?>