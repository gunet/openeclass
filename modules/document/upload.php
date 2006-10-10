<?php

/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Α full copyright notice can be read in "/info/copyright.txt".
        
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
 
//θέλουμε help
$require_help = FALSE;
//$helpTopic = 'User';
 
// αλλάζουμε το include 'init.php ' και αντικαθιστάται από το baseTheme.php
//include "../../include/init.php";
 
// το API: αρχείο με μεθόδους για τη δημιουργία τμημάτων της διεπαφής (baseTheme.php)
include "../../include/baseTheme.php";
$tool_content = "";
 
$nameTools = $langDownloadFile;

$tool_content .= "<i>$langNotRequired</i><br><br><br>
<form action=\"document.php\" method=\"post\" enctype=\"multipart/form-data\">
    <input type=\"hidden\" name=\"uploadPath\" value=\"$uploadPath\">
    $langDownloadFile&nbsp;:<br><input type=\"file\" name=\"userFile\" size=\"80\"><br><hr>
    
    <table width=\"99%\" border=\"1\">
    	<tr>
    		<td align=\"right\">$langTitle&nbsp;:</td>
			<td><input type=\"text\" name=\"file_title\" value=\"\" size=\"40\"></td>
		</tr>
		<tr>
			<td align=\"right\">$langComment&nbsp;:</td>
			<td><input type=\"text\" name=\"file_comment\" value=\"\" size=\"40\"></td>
		</tr>
		<tr>
			<td align=\"right\">$langCategory&nbsp;:</td>
			<td>
			    <select name=\"file_category\">
					<option selected=\"selected\" value=\"0\">$langCategoryOther<br>
					<option value=\"1\">$langCategoryExcercise<br>
					<option value=\"2\">$langCategoryLecture<br>
					<option value=\"3\">$langCategoryEssay<br>
					<option value=\"4\">$langCategoryDescription<br>
					<option value=\"5\">$langCategoryExample<br>
					<option value=\"6\">$langCategoryTheory<br>
			    </select>
			</td>
						
			
			        
			    
			    <input type=\"hidden\" name=\"file_creator\" value=\"$prenom $nom\" size=\"40\">
		</tr>
		<tr>
			<td align=\"right\">$langSubject&nbsp;:</td>
			<td><input type=\"text\" name=\"file_subject\" value=\"\" size=\"40\"></td>
		</tr>
		<tr>
			<td align=\"right\">$langDescription&nbsp;:</td>
			<td><input type=\"text\" name=\"file_description\" value=\"\" size=\"40\"></td>
		</tr>
		<tr>
			<td align=\"right\">$langAuthor&nbsp;:</td>
			<td><input type=\"text\" name=\"file_author\" value=\"\" size=\"40\"></td>
		</tr>
		<tr>
			<td align=\"right\"><input type=\"hidden\" name=\"file_date\" value=\"\" size=\"40\">
			    <input type=\"hidden\" name=\"file_format\" value=\"\" size=\"40\">
			    $langLanguage&nbsp;:
			</td>
			<td>			    
			
			    
			    
					<select name=\"file_language\">
						<option selected=\"selected\" value=\"en\">English
							</option><option value=\"fr\">French
							</option><option value=\"de\">German
							</option><option value=\"el\">Greek
							</option><option value=\"it\">Italian
							</option><option value=\"es\">Spanish																		
						</option>
					
					</select>";
			
			    
			
			    $tool_content .=  "</td>
		</tr>
		<tr>
			<td align=\"right\">$langCopyrighted&nbsp;:</td>
			<td>
				<input name=\"file_copyrighted\" type=\"radio\" value=\"0\" checked=\"checked\" /> $langCopyrightedUnknown<br>
			    <input name=\"file_copyrighted\" type=\"radio\" value=\"2\" /> $langCopyrightedFree<br>
			  	<input name=\"file_copyrighted\" type=\"radio\" value=\"1\" /> $langCopyrightedNotFree
			</td>
		</tr>
		<tr>
			 <td>&nbsp;</td>
			 <td>
			 	<input type=\"checkbox\" name=\"uncompress\" value=\"1\">$langUncompress<br>
			 	<small>$langNoticeGreek</small>
			 </td>
		</tr>
		</table>
			    <p align=\"right\"><input type=\"submit\" value=\"$langDownload\"></p>";
    $tool_content .=  "</form>";
 

 
draw($tool_content, '2');
 
?>