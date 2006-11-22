<?php
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
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
	serachuser.php
	@last update: 15-10-2006 by Karatzidis Stratos
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================        
  @Description: Activate the inactive accounts

  									
==============================================================================
*/

// LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
$langFiles = array('gunet','admin','registration');
$require_admin = TRUE;
include '../../include/baseTheme.php';
include 'admin.inc.php';
$nameTools = "Προσθήκη ενεργού χρόνου στους απενεργοποιημένους λογαριασμούς";		// Define $nameTools
$tool_content = "";		// Initialise $tool_content

// Main body
$activate = isset($_GET['activate'])?$_GET['activate']:'';		//variable of declaring the activation update

// update process for all the inactive records/users
if((!empty($activate)) && ($activate==1))
{
	// do the update
	$newtime = time() + 15552000;
	$qry = "UPDATE user SET expires_at=".$newtime." WHERE expires_at<=".time();		
	$sql = mysql_query($qry);
	if($sql)
	{
		$countinactive = mysql_affected_rows();
		if($countinactive>0)
		{
			$tool_content .= "Πραγματοποιήθηκαν ".$countinactive." ενημερώσεις<br><br>";
		}
	}
	else
	{
		$tool_content .= "Δεν πραγματοποιήθηκε καμία αλλαγή";
	}
	
}
	
	
// 3: display administrator menu
draw($tool_content,3);
?>
