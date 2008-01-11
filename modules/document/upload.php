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

/*===========================================================================
	upload.php
	@last update: 18-07-2006 by Sakis Agorastos
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================        
        @Description: Upload form that aids the user to select 
					  a file to upload and add some metadata with it.

    The script shows a form with a "Browse file" tag and some simpl
    inputs for metadata. The actual uploading takes place at document.php
==============================================================================*/

$require_current_course = TRUE;  // flag ασφάλειας
$langFiles = 'document';  // αρχείο μηνυμάτων
 
$require_help = FALSE;
//$helpTopic = 'User';
$require_login = true;
$require_prof = true;

include "../../include/baseTheme.php";
$tool_content = "";

if(!isset($_REQUEST['uploadPath'])) {
	$_REQUEST['uploadPath'] = "";
}
 
$nameTools = $langDownloadFile;
$navigation[]= array ("url"=>"document.php", "name"=> $langDoc);
$tool_content .= "
<form action=\"document.php\" method=\"post\" enctype=\"multipart/form-data\">
    <input type=\"hidden\" name=\"uploadPath\" value=\"".htmlspecialchars($_REQUEST['uploadPath'])."\">
     <table width=\"99%\">
     <tbody>
     <tr>
       <th class='left' width='220'>&nbsp;</th>
       <td><b>$dropbox_lang[uploadFile]</b></td>
       <td>&nbsp;</td>
     </tr>
     <tr>
       <th class='left'>$langPathUploadFile:</th>
       <td><input type=\"file\" name=\"userFile\" size=\"35\" class='FormData_InputText'></td>
       <td>&nbsp;</td>
     </tr>
     <tr>
       <th class='left'>$langTitle:</th>
       <td><input type=\"text\" name=\"file_title\" value=\"\" size=\"40\" class='FormData_InputText'></td>
       <td>&nbsp;</td>
     </tr>
     <tr>
        <th class='left'>$langComment:</th>
        <td><input type=\"text\" name=\"file_comment\" value=\"\" size=\"40\" class='FormData_InputText'></td>
       <td>&nbsp;</td>
     </tr>
     <tr>
       <th class='left'>$langCategory:</th>
       <td>
           <select name=\"file_category\" class='auth_input'>
             <option selected=\"selected\" value=\"0\">$langCategoryOther<br>
             <option value=\"1\">$langCategoryExcercise<br>
             <option value=\"2\">$langCategoryLecture<br>
             <option value=\"3\">$langCategoryEssay<br>
             <option value=\"4\">$langCategoryDescription<br>
             <option value=\"5\">$langCategoryExample<br>
             <option value=\"6\">$langCategoryTheory<br>
           </select>
       </td>
       <td>&nbsp;</td>
           <input type=\"hidden\" name=\"file_creator\" value=\"$prenom $nom\" size=\"40\">
     </tr>
     <tr>
       <th class='left'>$langSubject:</th>
       <td><input type=\"text\" name=\"file_subject\" value=\"\" size=\"40\" class='FormData_InputText'></td>
       <td>&nbsp;</td>
     </tr>
     <tr>
       <th class='left'>$langDescription:</th>
       <td><input type=\"text\" name=\"file_description\" value=\"\" size=\"40\" class='FormData_InputText'></td>
       <td>&nbsp;</td>
     </tr>
     <tr>
       <th class='left'>$langAuthor:</th>
       <td><input type=\"text\" name=\"file_author\" value=\"\" size=\"40\" class='FormData_InputText'></td>
       <td>&nbsp;</td>
     </tr>
     <tr>
       <th class='left'><input type=\"hidden\" name=\"file_date\" value=\"\" size=\"40\">
           <input type=\"hidden\" name=\"file_format\" value=\"\" size=\"40\">
           $langLanguage:
       </th>
       <td>
          <select name=\"file_language\" class='auth_input'>
            <option selected=\"selected\" value=\"en\">English
            </option><option value=\"fr\">French
            </option><option value=\"de\">German
            </option><option value=\"el\">Greek
            </option><option value=\"it\">Italian
            </option><option value=\"es\">Spanish	
            </option>
          </select>
       </td>
       <td>&nbsp;</td>
     </tr>
     <tr>
       <th class='left'>$langCopyrighted:</th>
       <td>
         <input name=\"file_copyrighted\" type=\"radio\" value=\"2\" /> $langCopyrightedFree<br>
         <input name=\"file_copyrighted\" type=\"radio\" value=\"1\" /> $langCopyrightedNotFree
       </td>
       <td>&nbsp;</td>
     </tr>
     <tr>
       <th class='left'>$langUncompress</th>
       <td><input type=\"checkbox\" name=\"uncompress\" value=\"1\"> </td>
       <td><small><div align='right'>$langNoticeGreek</div></small></td>
     </tr>
     <tr>
       <th class='left'>&nbsp;</th>
       <td><input type=\"submit\" value=\"$langUpload\"></td>
       <td><p align='right'><small>$langNotRequired</small></p></td>
     </tr>
     </tbody>
     </table>
     <br/>";
    $tool_content .=  "</form>";
 

 
draw($tool_content, '2');
 
?>
