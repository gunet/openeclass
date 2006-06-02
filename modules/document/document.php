<?php

/*
      +----------------------------------------------------------------------+
      | e-class version 1.0                                                  |
      | based on CLAROLINE version 1.3.0 $Revision$                |
      +----------------------------------------------------------------------+
      |   $Id$		 |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      | Copyright (c) 2003 GUNet                                             |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
      |                                                                      |
      | e-class changes by: Costas Tsibanis <costas@noc.uoa.gr>              |
      |                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
      |                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
      +----------------------------------------------------------------------+

  DESCRIPTION:
  ****
  This PHP script allow user to manage files and directories on a remote http server.
  The user can : - navigate trough files and directories.
                 - upload a file
                 - rename, delete, copy a file or a directory

  The script is organised in four sections.

  * 1st section execute the command called by the user
                Note: somme commands of this section is organised in two step.
                The script lines always begin by the second step,
                so it allows to return more easily to the first step.

  * 2nd section define the directory to display

  * 3rd section read files and directories from the directory defined in part 3

  * 4th section display all of that on a HTML page
*/

$require_current_course = TRUE;
$langFiles = 'document';

include '../../include/init.php';
include 'forcedownload.php';

$nameTools = $langDoc;
$dbTable = "document";

// check for quotas
$d = mysql_fetch_array(mysql_query("SELECT doc_quota FROM cours
    WHERE code='$currentCourseID'"));
$diskQuotaDocument = $d['doc_quota'];

// -------------------------
// download action 
// --------------------------

if (@$action=="download")
 {
    include('forcedownload.php');
		$real_file = $webDir."/courses/".$currentCourseID."/document/".$id;
		if (strpos($real_file, '/../') === FALSE) {
    	send_file_to_client($real_file, basename($id));
	exit;
		} else {
			header("Refresh: ${urlServer}modules/document/document.php");
 		}
 }

include "../../include/lib/fileDisplayLib.inc.php";

if($is_adminOfCourse) // for teacher only
{
    include "../../include/lib/fileManageLib.inc.php";
    include "../../include/lib/fileUploadLib.inc.php";

    if (@$uncompress == 1)
        include("../../include/pclzip/pclzip.lib.php");
}

mysql_select_db($currentCourseID);

/**************************************
FILEMANAGER BASIC VARIABLES DEFINITION
**************************************/

$baseServDir = $webDir;             
$baseServUrl = $urlAppend."/";      
$courseDir = "courses/$currentCourseID/document";
$baseWorkDir = $baseServDir.$courseDir;

$local_head = '
<script>
function confirmation (name)
{
    if (confirm("'.$langAreYouSureToDelete.'"+ name + " ?"))
        {return true;}
    else
        {return false;}
}
</script>
';

/*** clean information submited by the user from antislash ***/

stripSubmitValue($_POST);
stripSubmitValue($_GET);

begin_page();

echo '</td></tr></table>';

/*****************************************************************************/

if($is_adminOfCourse) 
{       // TEACHER ONLY



    /*>>>>>>>>>>>> MAIN SECTION  <<<<<<<<<<<<*/


    /********************************************************************************************************************************
                 												UPLOAD FILE
    
    //ousiastika dhmiourgei ena safe_fileName xrhsimopoiwntas ta DATETIME wste na mhn dhmiourgeitai
    //provlhma sto filesystem apo to onoma tou arxeiou. Parola afta to palio filename pernaei apo
    //'filtrarisma' wste na apofefxthoun 'epikyndynoi' xarakthres.
    
    //gia pardeigma me $fileName = "test.jpg" sto filesystem grafetai arxeio $safe_fileName = "20060301121510.jpg"
    
    ********************************************************************************************************************************/
    
    
    
    

    $dialogBox = '';
    if (is_uploaded_file(@$userFile))
    {
    
        /* Check the file size doesn't exceed
         * the maximum file size authorized in the directory
         */
        $diskUsed = dir_total_space($baseWorkDir);
        if ($diskUsed + @$_FILES['userFile']['size'] > $diskQuotaDocument) {
            $dialogBox .= $langNoSpace;
        } elseif (preg_match('/\.(ade|adp|bas|bat|chm|cmd|com|cpl|crt|exe|hlp|hta|' .
                'inf|ins|isp|jse|lnk|mdb|mde|msc|msi|msp|mst|pcd|pif|reg|scr|sct|shs|' .
                'shb|url|vbe|vbs|wsc|wsf|wsh)$/', $_FILES['userFile']['name'])) {
            $dialogBox .= "$langUnwantedFiletype: {$_FILES['userFile']['name']}";
        }

        /*** Unzipping stage ***/

        elseif (@$uncompress == 1 && preg_match("/.zip$/", $_FILES['userFile']['name']) )
        {
            $zipFile = new pclZip($userFile);

            /*** Check the zip content (real size and file extension) ***/

            $zipContentArray = $zipFile->listContent();
	
	    $realFileSize = 0;
            foreach($zipContentArray as $thisContent)
            {
                if ( preg_match("/.php$/", $thisContent['filename']) )
                {
                    $dialogBox .= $langZipNoPhp;
                    $found_php = true;
                    break;
                }
                
                $realFileSize += $thisContent['size'];

            }
            if (isset($realFileSize) and ($realFileSize + $diskUsed > $diskQuotaDocument))
            {   
                $dialogBox .= $langNoSpace;
            }
            elseif(!isset($found_php))
            {   /*** Uncompressing phase ***/
                
                    /*** PHP method - slower... ***/
                    chdir($baseWorkDir.$uploadPath);
                    $unzippingSate = $zipFile->extract();
            }

            if (!isset($found_php)) {
                // Added by Thomas
                $dialogBox .= $langDownloadAndZipEnd;
            }
            
             
            
        }
        else
        {
        	
        	$fileName = trim ($_FILES['userFile']['name']);
        	
        	
        	//elegxos ean to "path" tou arxeiou pros upload vrisketai hdh se eggrafh ston pinaka documents
            //(aftos einai ousiastika o elegxos if_exists dedomenou tou oti to onoma tou arxeiou sto filesystem
            //einai monadiko)
            
            $result = mysql_query ("SELECT filename FROM document WHERE filename LIKE '%$fileName%'");
        	$row = mysql_fetch_array($result);
        	
        	if (!empty($row['filename'])) 
        	{
        		//to arxeio yparxei hdh se eggrafh ston pinaka document ths vashs
        		$dialogBox .= "<b>$langFileExists !</b>";
        	}else //to arxeio den vrethike sth vash ara mporoume na proxwrhsoume me to upload
			{
 
		
		            /**** Check for no desired characters ***/
		            $fileName = replace_dangerous_char($fileName);
		
		            /*** Try to add an extension to files witout extension ***/
		            $fileName = add_ext_on_mime($fileName);
		            
		            /*** Handle PHP files ***/
		            $fileName = php2phps($fileName);
		            
		            
		            
		            
		            //ypologismos onomatos arxeiou me date + time.
		            //to onoma afto tha xrhsimopoiei sto filesystem & tha apothikevetai ston pinaka documents
		            $safe_fileName = date("YmdGis").".".get_file_extention($fileName);
		            
		            
		            
		                   
		            
		            //prosthiki eggrafhs kai metadedomenwn gia to eggrafo sth vash
		            if ($uploadPath == ".") $uploadPath2 = "/".$safe_fileName; else $uploadPath2 = $uploadPath."/".$safe_fileName;
		            
		            
		            //san file format vres to extension tou arxeiou
		            $file_format = get_file_extention($fileName);
		            
		            //san date you arxeiou xrhsimopoihse thn shmerinh hm/nia
		            $file_date = date("Y\-m\-d G\:i\:s");
		            
		            
		           
		            
		            
		            
		            $query = "INSERT INTO ".$dbTable." SET 
		            	path			=		'$uploadPath2',
		            	filename		=		'$fileName',
		            	visibility		=		'v',
		            	comment			=		'$file_comment',
		            	category		=		'$file_category',
		            	title			=		'$file_title',
		            	creator			=		'$file_creator',
		            	date			=		'$file_date',
		            	date_modified	=		'$file_date',
		            	subject			=		'$file_subject',
		            	description		=		'$file_description',            	
		            	author			=		'$file_author',
		            	format			=		'$file_format',
		            	language		=		'$file_language',
		            	copyrighted		=		'$file_copyrighted'";
		            
		            
		            
		            
		            //debuging commands
		            /*echo $query;
		            echo "<br><br>";
		            echo "Copy: $userFile to $baseWorkDir$uploadPath/$safe_fileName<br><br>";
		            exit;*/
		            
		            
		            
		            
		            
		            
		            mysql_query($query);
		            
		            
		            
		            
		            
		            /*** Copy the file to the desired destination ***/
		            copy ($userFile, $baseWorkDir.$uploadPath."/".$safe_fileName);
		            
		            
		            
		            
		            
		
		            @$dialogBox .= $langDownloadEnd;
			} // end else tou if(!empty($row['filename']))
        		
        		
        } // end else

    } // end if is_uploaded_file




    /**************************************
                MOVE FILE OR DIRECTORY
    **************************************/

    /*
     * The code begin with STEP 2
     * so it allows to return to STEP 1 if STEP 2 unsucceeds
     */

    /*-------------------------------------
        MOVE FILE OR DIRECTORY : STEP 2
    --------------------------------------*/

    if (isset($moveTo))
    {
        if (move($baseWorkDir.$source,$baseWorkDir.$moveTo)) {
            update_db_info("update", $source, $moveTo."/".basename($source));
            $dialogBox = $langDirMv;
        }
        else
        {
            $dialogBox = $langImpossible;

            /*** return to step 1 ***/
            $move = $source;
            unset ($moveTo);
        }
        
    }


    /*-------------------------------------
        MOVE FILE OR DIRECTORY : STEP 1
    --------------------------------------*/

    if (isset($move))
    {
    	$moveFileName = $move;
    	
    	//h $move periexei to onoma tou arxeiou. anazhthsh onomatos arxeiou sth vash
    	$result = mysql_query ("SELECT * FROM $dbTable WHERE path=\"".$move."\"");
    	$res = mysql_fetch_array($result);
    	if(!empty($res))
    	{
    		$moveFileName = $res['filename'];
    	}
    	
        @$dialogBox .= form_dir_list("source", $moveFileName, "moveTo", $baseWorkDir);
    }


    /**************************************
            DELETE FILE OR DIRECTORY
    **************************************/


    if (isset($delete)) {
        if (my_delete($baseWorkDir.$delete)) {
            update_db_info("delete", $delete);
            $dialogBox = "<b>$langDocDeleted</b>";
        }
    }


    /*****************************************
                     RENAME
    ******************************************/

    /*
     * The code begin with STEP 2
     * so it allows to return to STEP 1
     * if STEP 2 unsucceds
     */


    /*-------------------------------------
              RENAME : STEP 2
    --------------------------------------*/

    if (isset($renameTo))
    {//afth einai h palia methodos metonomasias arxeiwn (kateftheian sto filesystem)
        if ( my_rename($baseWorkDir.$sourceFile, $renameTo) )
        {        	
            update_db_info("update", $sourceFile,
                dirname($sourceFile).'/'.$renameTo,
                is_dir("$baseWorkDir/$renameTo"));
                

            $dialogBox = "<b>$langElRen</b>";
        }
        else
        {
            $dialogBox = "<b>$langFileExists !</b>";

            /*** return to step 1 ***/
            $rename = $sourceFile;
            unset($sourceFile);
        }
    }
    
    
    //nea methodos metonomasias arxeiwn kanontas update sthn eggrafh pou yparxei sth vash
    //elegxos gia thn yparksh eggrafh sth vash ginetai sto STEP 1
    if (isset($renameTo2))
    {

		$query =  "UPDATE ".$dbTable." SET filename=\"".$renameTo2."\" WHERE path=\"".$sourceFile."\"";
		echo "<br><br>".$query."<br><br>";
		mysql_query($query);
		
		$dialogBox = "<b>$langElRen</b>";
    	
    }


    /*-------------------------------------
                RENAME : STEP 1
    --------------------------------------*/

    if (isset($rename))
    {
    	//elegxos gia to ean yparxei hdh eggrafh sth vash
    	$result = mysql_query ("SELECT * FROM $dbTable WHERE path=\"".$rename."\"");
    	$res = mysql_fetch_array($result);
    	
    	//ean den yparxei eggrafh sth vash tote to onoma tou arxeiou parto apo to filesystem,
    	//kai akolouthise thn palia methodo metonomasias arxeiwn
    	if(empty($res["filename"])) 
    	{
	    	
	    	$fileName = basename($rename);    
	        
	        @$dialogBox .= "<!-- rename -->\n";
	        $dialogBox .= "<form>\n";
	        $dialogBox .= "<input type=\"hidden\" name=\"sourceFile\" value=\"$rename\">\n";
	        $dialogBox .= "$langRename ".htmlspecialchars($fileName)." $langIn :\n";
	        $dialogBox .= "<input type=\"text\" name=\"renameTo\" value=\"$fileName\">\n";
	        $dialogBox .= "<input type=\"submit\" value=\"$langRename\">\n";
	        $dialogBox .= "</form>\n";
    	}else 
    	{//yparxei eggrafh sth vash gia to arxeio opote xrhsimopoihse thn nea methodo metonomasias (ginetai sto STEP 2)
    		
    		$fileName = $res["filename"];
    		
    		@$dialogBox .= "<!-- rename -->\n";
	        $dialogBox .= "<form>\n";
	        $dialogBox .= "<input type=\"hidden\" name=\"sourceFile\" value=\"$rename\">\n";
	        $dialogBox .= "$langRename ".htmlspecialchars($fileName)." $langIn :\n";
	        $dialogBox .= "<input type=\"text\" name=\"renameTo2\" value=\"$fileName\">\n";
	        $dialogBox .= "<input type=\"submit\" value=\"$langRename\">\n";
	        $dialogBox .= "</form>\n";
    	}
    	
    	
    	
    	
    }




    /*****************************************
               CREATE DIRECTORY
    *****************************************/

    /*
     * The code begin with STEP 2
     * so it allows to return to STEP 1
     * if STEP 2 unsucceds
     */

    /*-------------------------------------
        STEP 2
    --------------------------------------*/
    if (isset($newDirPath) && isset($newDirName))
    {
        $newDirName = replace_dangerous_char($newDirName);

        if ( check_name_exist($baseWorkDir.$newDirPath."/".$newDirName) )
        {
            $dialogBox .= "<b>$langFileExists!</b>";
            $createDir = $newDirPath; unset($newDirPath);// return to step 1
        }
        else
        {
            mkdir($baseWorkDir.$newDirPath."/".$newDirName, 0700);
        }

        $dialogBox = "<b>$langDirCr.</b>";
    }


    /*-------------------------------------
            STEP 1
    --------------------------------------*/

    if (isset($createDir))
    {
        @$dialogBox .= "<!-- create dir -->\n";
        $dialogBox .= "<form>\n";
        $dialogBox .= "<input type=\"hidden\" name=\"newDirPath\" value=\"$createDir\">\n";
        $dialogBox .= "$langNameDir:\n";
        $dialogBox .= "<input type=\"text\" name=\"newDirName\">\n";
        $dialogBox .= "<input type=\"submit\" value=\"$langCreateDir\">\n";
        $dialogBox .= "</form>\n";
    }


    /*****************************************
            ADD/UPDATE/REMOVE COMMENT
    *****************************************/

    /*
     * The code begin with STEP 2
     * so it allows to return to STEP 1
     * if STEP 2 unsucceds
     */

    /*------------------------------------------------------------------------------------
                 COMMENT : STEP 2 (process all metadata and update the DB)
     ------------------------------------------------------------------------------------*/
    //h $commentPath periexei to path tou arxeiou gia to opoio tha epikyrothoun ta metadata
    
    if (isset($edit_metadata))
    {
        //elegxos ean yparxei eggrafh sth vash gia to arxeio
    	$result = mysql_query ("SELECT * FROM $dbTable WHERE path=\"".$commentPath."\"");
    	$res = mysql_fetch_array($result);
    	if(!empty($res))
    	{
    		
    		//elegxos ean o xrhsths epelekse diaforetikh glwssa h' tipota (option -> "")
    		if (empty($file_language)) $file_language = $file_oldLanguage;
    		
    		$query =  "UPDATE ".$dbTable." SET 
    						comment=\"".$file_comment."\",
							category=\"".$file_category."\",
    						title=\"".$file_title."\",							    				
							date_modified=\"".date("Y\-m\-d G\:i\:s")."\",
    						subject=\"".$file_subject."\",
    						description=\"".$file_description."\",
    						author=\"".$file_author."\",
    						language=\"".$file_language."\",
    						copyrighted=\"".$file_copyrighted."\"    						
    				  WHERE path=\"".$commentPath."\"";
    		
    	    	
    	} else 
    	//den yparxei eggrafh sth vash gia to sygkekrimeno arxeio opote dhmiourghse thn eggrafh
    	{
    		
    		if (empty($file_language)) $file_language = $file_oldLanguage;
    		if (empty($file_filename)) $file_filename = htmlspecialchars($fileName);
    		$file_format = get_file_extention($file_filename);
    		
    		$query =  "INSERT INTO ".$dbTable." SET
    			path=\"".$commentPath."\",
    			filename=\"".$file_filename."\",
    			visibility=\"v\",
				comment=\"".$file_comment."\",
				category=\"".$file_category."\",
				title=\"".$file_title."\",
				creator=\"".$prenom." ".$nom."\",
				date=\"".date("Y\-m\-d G\:i\:s")."\",
				date_modified=\"".date("Y\-m\-d G\:i\:s")."\",
				subject=\"".$file_subject."\",
				description=\"".$file_description."\",
				author=\"".$file_author."\",
				format=\"$file_format\",
				language=\"".$file_language."\",
				copyrighted=\"".$file_copyrighted."\"";
    		
    	}
    	    		
    		mysql_query($query);
    	
    	
    	/* afth einai h palia kai axrhsth pleon methodos gia thn epeksergasia sxoliwn
        
        $newComment = trim($newComment); // remove spaces

        // Check if there is yet a record for this file in the DB //
        $result = mysql_query ("SELECT * FROM $dbTable WHERE path=\"".$commentPath."\"");
        while($row = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            $attribute['path']= $row['path'];
            $attribute['visibility']= $row['visibility'];
            $attribute['comment']= $row['comment'];
        }


        // Determine the correct query to the DB //
        if (isset($attribute) && $attribute['visibility']=="i")
        {
                $query = "UPDATE ".$dbTable." SET comment=\"".$newComment."\" WHERE path=\"".$commentPath."\"";
        }
        elseif ($newComment == "" && isset($attribute) && $attribute['visibility'] != "i")
        {
            $query = "DELETE FROM ".$dbTable." WHERE path=\"".$commentPath."\"";
        }
        elseif (isset($attribute) && $attribute['comment'] != "" && $newComment != "")
        {
            $query= "UPDATE ".$dbTable." SET comment=\"".$newComment."\" WHERE path=\"".$commentPath."\"";
        }
        else
        {
            $query = "INSERT INTO ".$dbTable." SET path=\"".$commentPath."\", comment=\"".$newComment."\", visibility='v'";
        }

        mysql_query($query);
        unset($attribute);

        $dialogBox = "<b>$langComMod.</b>"; 
        */
    }

    /*------------------------------------------------------------
                 COMMENT : STEP 1 (show edit comment form)
     -----------------------------------------------------------*/
    //emfanish ths formas gia tropopoihsh comment
    //edw tha valoume kai ta epipleon pedia gia ta metadedomena

    if (isset($comment))
    {
        $oldComment='';
        /*** Retrieve the old comment and metadata ***/
                
        $query = "SELECT * FROM $dbTable WHERE path LIKE \"%".$comment."%\"";
        $result = mysql_query ($query);
       	
        $row = mysql_fetch_array($result);
        
        	$oldFilename = $row['filename'];
        	$oldComment = $row['comment'];
        	$oldCategory = $row['category'];
        	$oldTitle = $row['title'];
        	$oldCreator = $row['creator'];
        	$oldDate = $row['date'];
        	$oldSubject = $row['subject'];
        	$oldDescription = $row['description'];
        	$oldAuthor = $row['author'];
			$oldLanguage = $row['language'];
			$oldCopyrighted = $row['copyrighted'];
        	
        
        
      	//filsystem compability: ean gia to arxeio den yparxoun dedomena sto pedio filename
      	//(ara to arxeio den exei safe_filename (=alfarithmitiko onoma)) xrhsimopoihse to
      	//$fileName gia thn provolh tou onomatos arxeiou
        
      	$fileName = basename($comment);
      	if (empty($oldFilename)) $oldFilename = $fileName;

        
        @$dialogBox .="<!-- comment -->\n";
        $dialogBox .="	<form method=\"post\" action=\"$_SERVER[PHP_SELF]?edit_metadata\">
        					<input type=\"hidden\" name=\"commentPath\" value=\"$comment\">
        					$langAddComment<br>
        						<strong>".htmlspecialchars($oldFilename)."</strong>
        					<hr>
        					<input type=\"hidden\" size=\"80\" name=\"file_filename\" value=\"$oldFilename\"><br>
        					$langComment:<input type=\"text\" size=\"80\" name=\"file_comment\" value=\"$oldComment\"><br>
							$langTitle&nbsp;:<input type=\"text\" size=\"80\" name=\"file_title\" value=\"$oldTitle\"><br>
							$langCategory:							";
        //ektypwsh tou combobox gia thn epilogh kathgorias tou eggrafou
		$dialogBox .= "
	
							<select name=\"file_category\">
									<option"; if($oldCategory=="0") $dialogBox .= " selected=\"selected\""; $dialogBox .= " value=\"0\">$langCategoryOther<br>";
							$dialogBox .= "		<option"; if($oldCategory=="1") $dialogBox .= " selected=\"selected\""; $dialogBox .= " value=\"1\">$langCategoryExcercise<br>
									<option"; if($oldCategory=="1") $dialogBox .= " selected=\"selected\""; $dialogBox .= " value=\"2\">$langCategoryLecture<br>
									<option"; if($oldCategory=="2") $dialogBox .= " selected=\"selected\""; $dialogBox .= " value=\"3\">$langCategoryEssay<br>
									<option"; if($oldCategory=="3") $dialogBox .= " selected=\"selected\""; $dialogBox .= " value=\"4\">$langCategoryDescription<br>
									<option"; if($oldCategory=="4") $dialogBox .= " selected=\"selected\""; $dialogBox .= " value=\"5\">$langCategoryExample<br>
									<option"; if($oldCategory=="5") $dialogBox .= " selected=\"selected\""; $dialogBox .= " value=\"6\">$langCategoryTheory<br>
							</select><br>";
							
							
					
		$dialogBox .= "					
							
    						<input type=\"hidden\" size=\"80\" name=\"file_creator\" value=\"$oldCreator\">
    						<input type=\"hidden\" size=\"80\" name=\"file_date\" value=\"$oldDate\">
    						$langSubject&nbsp;:<input type=\"text\" size=\"80\" name=\"file_subject\" value=\"$oldSubject\"><br>
    						$langDescription&nbsp;:<input type=\"text\" size=\"80\" name=\"file_description\" value=\"$oldDescription\"><br>
    						$langAuthor&nbsp;:<input type=\"text\" size=\"80\" name=\"file_author\" value=\"$oldAuthor\"><br>";
    	
		$dialogBox .= "		$langCopyrighted&nbsp;: <input name=\"file_copyrighted\" type=\"radio\" value=\"0\" "; if ($oldCopyrighted=="0") $dialogBox .= " checked=\"checked\" "; $dialogBox .= " /> $langCopyrightedUnknown
    					   							<input name=\"file_copyrighted\" type=\"radio\" value=\"2\" "; if ($oldCopyrighted=="2") $dialogBox .= " checked=\"checked\" "; $dialogBox .= " /> $langCopyrightedFree
  						   							<input name=\"file_copyrighted\" type=\"radio\" value=\"1\" "; if ($oldCopyrighted=="0") $dialogBox .= " checked=\"checked\" "; $dialogBox .= "/> $langCopyrightedNotFree
  						   							
  						   							
  		<input type=\"hidden\" size=\"80\" name=\"file_oldLanguage\" value=\"$oldLanguage\">";
    						
    						
    						
    	//ektypwsh tou combox gia epilogh glwssas			
    	
		$dialogBox .= "		<br>$langLanguage&nbsp;: $oldLanguage
		
		
		
		
		
		
		
															
															<select name=\"file_language\">
																<option selected=\"selected\" value=\"\">
																</option><option value=\"en\">English
																</option><option value=\"da\">Danish
																</option><option value=\"fi\">Finnish
																</option><option value=\"is\">Icelandic
																</option><option value=\"no\">Norwegian
																</option><option value=\"no-nyn\">No: Nynorsk
																</option><option value=\"no-bok\">No: Bokmaal
																</option><option value=\"sv\">Swedish
																</option><option value=\"i-sami-no\">Northern Sámi
																</option><option value=\"ab\">Abkhazian
																</option><option value=\"aa\">Afar
																
																</option><option value=\"af\">Afrikaans
																</option><option value=\"sq\">Albanian
																</option><option value=\"am\">Amharic
																</option><option value=\"ar\">Arabic
																</option><option value=\"hy\">Armenian
																</option><option value=\"as\">Assamese
																</option><option value=\"ay\">Aymara
																</option><option value=\"az\">Azerbaijani
																</option><option value=\"ba\">Bashkir
																</option><option value=\"eu\">Basque
																</option><option value=\"bn\">Bengali; Bangla
																</option><option value=\"dz\">Bhutani
																</option><option value=\"bh\">Bihari
																</option><option value=\"bi\">Bislama
																</option><option value=\"br\">Breton
																</option><option value=\"bg\">Bulgarian
																</option><option value=\"my\">Burmese
																
																</option><option value=\"be\">Byelorussian
																</option><option value=\"km\">Cambodian
																</option><option value=\"ca\">Catalan
																</option><option value=\"zh\">Chinese
																</option><option value=\"kw\">Cornish
																</option><option value=\"co\">Corsican
																</option><option value=\"hr\">Croatian
																</option><option value=\"cs\">Czech
																</option><option value=\"nl\">Dutch
																</option><option value=\"eo\">Esperanto
																</option><option value=\"et\">Estonian
																</option><option value=\"fo\">Faroese
																</option><option value=\"fj\">Fiji
																</option><option value=\"fr\">French
																</option><option value=\"fy\">Frisian
																</option><option value=\"gl\">Galician
																</option><option value=\"ka\">Georgian
																
																</option><option value=\"de\">German
																</option><option value=\"el\">Greek
																</option><option value=\"kl\">Greenlandic
																</option><option value=\"gn\">Guarani
																</option><option value=\"gu\">Gujarati
																</option><option value=\"ha\">Hausa
																</option><option value=\"he\">Hebrew
																</option><option value=\"hi\">Hindi
																</option><option value=\"hu\">Hungarian
																</option><option value=\"id\">Indonesian
																</option><option value=\"ia\">Interlingua
																</option><option value=\"ie\">Interlingue
																</option><option value=\"iu\">Inuktitut
																</option><option value=\"ik\">Inupiak
																</option><option value=\"ga\">Irish (Irish Gaelic)
																</option><option value=\"it\">Italian
																</option><option value=\"ja\">Japanese
																
																</option><option value=\"jw\">Javanese
																</option><option value=\"kn\">Kannada
																</option><option value=\"ks\">Kashmiri
																</option><option value=\"kk\">Kazakh
																</option><option value=\"rw\">Kinyarwanda
																</option><option value=\"ky\">Kirghiz
																</option><option value=\"rn\">Kirundi
																</option><option value=\"ko\">Korean
																</option><option value=\"ku\">Kurdish
																</option><option value=\"lo\">Laothian (Laotian)
																</option><option value=\"la\">Latin
																</option><option value=\"lv\">Latvian; Lettish
																</option><option value=\"ln\">Lingala
																</option><option value=\"lt\">Lithuanian
																</option><option value=\"lb\">Luxemburgish
																</option><option value=\"mk\">Macedonian
																</option><option value=\"mg\">Malagasy
																
																</option><option value=\"ms\">Malay
																</option><option value=\"ml\">Malayalam
																</option><option value=\"mt\">Maltese
																</option><option value=\"gv\">Manx Gaelic
																</option><option value=\"mi\">Maori
																</option><option value=\"mr\">Marathi
																</option><option value=\"mo\">Moldavian
																</option><option value=\"mn\">Mongolian
																</option><option value=\"na\">Nauru
																</option><option value=\"ne\">Nepali
																</option><option value=\"oc\">Occitan
																</option><option value=\"or\">Oriya
																</option><option value=\"om\">Oromo (Afan) 
																</option><option value=\"ps\">Pashto; Pushto
																</option><option value=\"fa\">Persian
																</option><option value=\"pl\">Polish
																</option><option value=\"pt\">Portuguese
																
																</option><option value=\"pa\">Punjabi
																</option><option value=\"qu\">Quechua
																</option><option value=\"rm\">Rhaeto-Romance
																</option><option value=\"ro\">Romanian
																</option><option value=\"ru\">Russian
																</option><option value=\"sm\">Samoan
																</option><option value=\"sg\">Sangho
																</option><option value=\"sa\">Sanskrit
																</option><option value=\"gd\">Scots Gaelic (Scottish Gaelic)
																</option><option value=\"sr\">Serbian
																</option><option value=\"sh\">Serbo-Croatian
																</option><option value=\"st\">Sesotho
																</option><option value=\"tn\">Setswana
																</option><option value=\"sn\">Shona
																</option><option value=\"sd\">Sindhi
																</option><option value=\"si\">Singhalese
																</option><option value=\"ss\">Siswati
																
																</option><option value=\"sk\">Slovak
																</option><option value=\"sl\">Slovenian
																</option><option value=\"so\">Somali
																</option><option value=\"es\">Spanish
																</option><option value=\"su\">Sudanese
																</option><option value=\"sv\">Swedish
																</option><option value=\"sw\">Swahili
																</option><option value=\"tl\">Tagalog
																</option><option value=\"tg\">Tajik
																</option><option value=\"ta\">Tamil
																</option><option value=\"tt\">Tatar
																</option><option value=\"te\">Telugu
																</option><option value=\"th\">Thai
																</option><option value=\"bo\">Tibetan
																</option><option value=\"ti\">Tigrinya
																</option><option value=\"to\">Tonga
																</option><option value=\"ts\">Tsonga
																
																</option><option value=\"tr\">Turkish
																</option><option value=\"tk\">Turkmen
																</option><option value=\"tw\">Twi
																</option><option value=\"ug\">Uigur
																</option><option value=\"uk\">Ukrainian
																</option><option value=\"ur\">Urdu
																</option><option value=\"uz\">Uzbek
																</option><option value=\"vi\">Vietnamese
																</option><option value=\"vo\">Volapük
																</option><option value=\"cy\">Welsh
																</option><option value=\"wo\">Wolof
																</option><option value=\"xh\">Xhosa
																</option><option value=\"yi\">Yiddish
																</option><option value=\"yo\">Yoruba
																</option><option value=\"za\">Zhuang
																</option><option value=\"zu\">Zulu
																</option>
															
															</select>
		
															<br><br>
		
		
		
		
		
		
        					<input type=\"submit\" value=\"$langOkComment\">
        				</form>";
    }




    /**************************************
             VISIBILITY COMMANDS
    **************************************/

    if (isset($mkVisibl) || isset($mkInvisibl))
    {
        $visibilityPath = @$mkVisibl.@$mkInvisibl; // At least one of these variables are empty. So it's okay to proceed this way

        /*** Check if there is yet a record for this file in the DB ***/
        $result = mysql_query ("SELECT * FROM $dbTable WHERE path LIKE \"".$visibilityPath."\"");
        while($row = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            $attribute['path']= $row['path'];
            $attribute['visibility']= $row['visibility'];
            $attribute['comment']= $row['comment'];
        }

        if (isset($mkVisibl))
        {
            $newVisibilityStatus = "v";
        }
        elseif ($mkInvisibl)
        {
            $newVisibilityStatus = "i";
        }

        if (@$attribute['comment'])
        {
            $query = "UPDATE ".$dbTable." SET visibility='$newVisibilityStatus' WHERE path=\"".$visibilityPath."\"";
        }
        elseif (isset($attribute) && $attribute['visibility']=="i" && $newVisibilityStatus == "v")
        {
            $query="DELETE FROM ".$dbTable." WHERE path=\"".$visibilityPath."\"";
        }
        else
        {
            $query="INSERT INTO ".$dbTable." SET path=\"".$visibilityPath."\", visibility=\"".$newVisibilityStatus."\"";
        }

        mysql_query($query);
        unset($attribute);

        $dialogBox = "<b>$langViMod</b>";

    }
} // TEACHER ONLY




/*>>>>>>>>>>>> COMMON FOR THEACHERS AND STUDENTS  <<<<<<<<<<<<*/
 



/**************************************
       DEFINE CURRENT DIRECTORY
**************************************/

if (isset($openDir)  || isset($moveTo) || isset($createDir) || isset($newDirPath) || isset($uploadPath) ) // $newDirPath is from createDir command (step 2) and $uploadPath from upload command
{
    $curDirPath = @$openDir . @$createDir . @$moveTo . @$newDirPath . @$uploadPath;
    /*
     * NOTE: Actually, only one of these variables is set.
     * By concatenating them, we eschew a long list of "if" statements
     */
}
elseif ( isset($delete) || isset($move) || isset($rename) || isset($sourceFile) || isset($comment) || isset($commentPath) || isset($mkVisibl) || isset($mkInvisibl)) //$sourceFile is from rename command (step 2)
{
    $curDirPath = dirname(@$delete . @$move . @$rename . @$sourceFile . @$comment . @$commentPath . @$mkVisibl . @$mkInvisibl);
    /*
     * NOTE: Actually, only one of these variables is set.
     * By concatenating them, we eschew a long list of "if" statements
     */
}
else
{
    $curDirPath="";
}

if ($curDirPath == "/" || $curDirPath == "\\" || strpos($curDirPath, ".."))
{
    $curDirPath =""; // manage the root directory problem

    /*
     * The strpos($curDirPath, "..") prevent malicious users to go to the root directory
     */
}

$curDirName = basename($curDirPath);
$parentDir = dirname($curDirPath);

if ($parentDir == "/" || $parentDir == "\\")
{
    $parentDir =""; // manage the root directory problem
}




/**************************************
    READ CURRENT DIRECTORY CONTENT
**************************************/

/*----------------------------------------
SEARCHING FILES & DIRECTORIES INFOS
    ON THE DB
--------------------------------------*/

/*** Search infos in the DB about the current directory the user is in ***/
$result = db_query("SELECT * FROM $dbTable
    WHERE path LIKE '$curDirPath/%'
        AND path NOT LIKE '$curDirPath/%/%'");

while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
    $attribute['path'][] = $row['path'];
    $attribute['visibility'][] = $row['visibility'];
    $attribute['comment'][] = $row['comment'];
}


/*----------------------------------------
LOAD FILES AND DIRECTORIES INTO ARRAYS
--------------------------------------*/

if (@chdir(realpath($baseWorkDir.$curDirPath))) {

$handle = opendir(".");

while ($file = readdir($handle))
{
    if ($file == "." || $file == "..")
    {
        continue;                       // Skip current and parent directories
    }

    if(is_dir($file))
    {
        $dirNameList[] = $file;

        /*** Make the correspondance between info given by the file system and info given by the DB ***/
        $keyDir = sizeof($dirNameList)-1;

        if (isset($attribute))
        {
            $keyAttribute = array_search($curDirPath."/".$file, $attribute['path']);

            if ($keyAttribute !== false)
            {
                $dirCommentList[$keyDir] = $attribute['comment'][$keyAttribute];
                $dirVisibilityList[$keyDir] = $attribute['visibility'][$keyAttribute];
            }
        }

    }

    if(is_file($file))
    {
        $fileNameList[] = $file;
        $fileSizeList[] = filesize($file);
        $fileDateList[] = filemtime($file);

        /*** Make the correspondance between info given by the file system and info given by the DB ***/
        $keyFile = sizeof($fileNameList)-1;

        if (isset($attribute))
        {
            $keyAttribute = array_search($curDirPath."/".$file, $attribute['path']);

            if ($keyAttribute !== false)
            {
                $fileCommentList[$keyFile] = $attribute['comment'][$keyAttribute];
                $fileVisibilityList[$keyFile] = $attribute['visibility'][$keyAttribute];
            }
        }
    }
} // end while ($file = readdir($handle))



/*----------------------------------------
    CHECK BASE INTEGRITY
--------------------------------------*/


if (isset($attribute))
{
    /* check if the number of DB records is greater than the numbers of files attributes previously given */
    if (sizeof($attribute['path']) > (sizeof(@$dirVisibilityList) + sizeof(@$fileVisibilityList)))
    {
        /* search DB records wich have not correspondance on the directory */
        foreach( $attribute['path'] as $chekinFile)
        {
            if (@$dirNameList && in_array(basename($chekinFile), $dirNameList))
                continue;
            elseif (@$fileNameList && in_array(basename($chekinFile), $fileNameList))
                continue;
            else
                $recToDel[]= $chekinFile; // add chekinFile to the list of records to delete
        }

        /* Build the query to delete deprecated DB records */
        $nbrRecToDel = sizeof (@$recToDel);
        for ($i=0; $i < $nbrRecToDel ;$i++)
        {
            $queryClause .= "path LIKE \"".$recToDel[$i]."%\"";
            if ($i < $nbrRecToDel-1)
                {$queryClause .=" OR ";}
        }

        mysql_query("DELETE FROM $dbTable WHERE ".@$queryClause);
        mysql_query("DELETE FROM $dbTable WHERE comment LIKE '' AND visibility LIKE 'v'");
        /* The second query clean the DB 'in case of' empty records (no comment an visibility=v)
           These kind of records should'nt be there, but we never know... */
    }
}
	closedir($handle);
	unset($attribute);

/*** Sort alphabetically ***/

	if (isset($dirNameList)) {
	    asort($dirNameList);
	}

	if (isset($fileNameList)) {
	    asort($fileNameList);
	}


} else {
	echo $langInvalidDir;
}


/*>>>>>>>>>>>> END: COMMON TO TEACHERS AND STUDENTS <<<<<<<<<<<<*/

/*>>>>>>>>>>>> TEACHER VIEW <<<<<<<<<<<<*/


if($is_adminOfCourse) {   

    /**************************************
                DISPLAY
    **************************************/


    $dspCurDirName = htmlspecialchars($curDirName);
    $cmdCurDirPath = rawurlencode($curDirPath);
    $cmdParentDir  = rawurlencode($parentDir);

    ?>
    <div class="fileman" align="center">
    <table width="100%" border="1" cellspacing="2" cellpadding="4">
    <tr>
        <td>&nbsp;</td>
        <td align="right">
        <a href="../help/help.php?topic=Doc&language=<?= $language?>" 
        onClick="window.open('../help/help.php?topic=Doc&language=<?= $language?>','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); 
        return false;">
        <font size=2 face="arial, helvetica"><?= $langHelp ?></font>
        </a>
        </td>
    </tr>

    <tr>

    <?php


    /*----------------------------------------
        DIALOG BOX SECTION
    --------------------------------------*/
    if (!empty($dialogBox))
    {
        echo "<td bgcolor=\"#9999FF\">";
        echo "<!-- dialog box -->";
        echo $dialogBox;
        echo "</td>";
    }
    else
    {
        echo "<td>\n<!-- dialog box -->\n&nbsp;\n</td>\n";
    }


    /*------------------------------------------------------------------------------------------------------------------------------
        UPLOAD SECTION (ektypwnei th forma me ta stoixeia gia upload eggrafou + ola ta pedia gia ta metadata symfwna me Dublin Core)
    -------------------------------------------------------------------------------------------------------------------------------*/
    echo "<!-- upload  -->
    <td align=\"right\">
    	<a href=\"upload.php?fileCreator=$prenom $nom&uploadPath=$curDirPath\">$langDownloadFile</a>
  	</td>\n";

    ?>

    </tr>
    </table>
    
    <table width="100%" border="1" cellspacing="2">
    <?php


    /*----------------------------------------
        CURRENT DIRECTORY LINE
    --------------------------------------*/

    echo "<tr>\n";
    echo "<td colspan=8>\n";

    /*** go to parent directory ***/
    if ($curDirName) // if the $curDirName is empty, we're in the root point and we can't go to a parent dir
    {
        echo "<!-- parent dir -->\n";
        echo "<a href=\"$_SERVER[PHP_SELF]?openDir=".$cmdParentDir."\">\n";
        echo "<IMG src=\"img/parent.gif\" border=0 align=\"absbottom\" hspace=5>\n";
        echo "<small>$langUp</small>\n";
        echo "</a>\n";
    }


    /*** create directory ***/
    echo "<!-- create dir -->\n";
    echo "<a href=\"$_SERVER[PHP_SELF]?createDir=".$cmdCurDirPath."\">";
    echo "<IMG src=\"img/dossier.gif\" border=0 align=\"absbottom\" hspace=5>";
    echo "<small> $langCreateDir</small>";
    echo "</a>";

    echo "</tr>\n";
    echo "</td>\n";


    if ($curDirName) // if the $curDirName is empty, we're in the root point and there is'nt a dir name to display
    {
        /*** current directory ***/
        echo "<!-- current dir name -->\n";
        echo "<tr>\n";
        echo "<td colspan=\"8\" align=\"left\" bgcolor=\"#000066\">\n";
        echo "<img src=\"img/opendir.gif\" align=\"absbottom\" vspace=2 hspace=5>\n";
        echo "<font color=\"#CCCCCC\">".$dspCurDirName."</font>\n";
        echo "</td>\n";
        echo "</tr>\n";
    }

    ?>


    <!-- command list -->

    <tr bgcolor="<?php echo "$color2" ?>"  align="center" valign="top">
    <?
    echo "<td>$langName</td>
    <td>$langSize</td>
    <td>$langDate</td>
    <td>$langDelete</td>
    <td>$langMove</td>
    <td>$langRename</td>
    <td>$langEditMeta</td>
    <td>$langVisible</td>
    </tr>";


    
    /*----------------------------------------
                DISPLAY DIRECTORIES
    ------------------------------------------*/

    if (isset($dirNameList))
    {
        while (list($dirKey, $dirName) = each($dirNameList))
        {
            $dspDirName = htmlspecialchars($dirName);
            $cmdDirName = rawurlencode($curDirPath."/".$dirName);

            if (@$dirVisibilityList[$dirKey] == "i")
            {
                $style=" class=\"invisible\"";
            }
            else
            {
                $style="";
            }

            echo "<tr align=\"center\">\n";
            echo "<td align=\"left\">\n";
            echo "<a href=\"$_SERVER[PHP_SELF]?openDir=".$cmdDirName."\"".$style.">\n";
            echo "<img src=\"img/dossier.gif\" border=0 hspace=5>\n";
            echo $dspDirName."\n";
            echo "</a>\n";

            /*** skip display date and time ***/
            echo "<td>&nbsp;</td>\n";
            echo "<td>&nbsp;</td>\n";

            /*** delete command ***/
            echo "<td><a href=\"$_SERVER[PHP_SELF]?delete=".$cmdDirName."\" onClick=\"return confirmation('".addslashes($dspDirName)."');\">
		<img src=\"./img/supprimer.gif\" border=0></a></td>\n";
            /*** copy command ***/
            echo "<td><a href=\"$_SERVER[PHP_SELF]?move=".$cmdDirName."\">
		<img src=\"img/deplacer.gif\" border=0></a></td>\n";
            /*** rename command ***/
            echo "<td><a href=\"$_SERVER[PHP_SELF]?rename=".$cmdDirName."\">
		<img src=\"img/renommer.gif\" border=0></a></td>\n";
            /*** comment command ***/
            echo "<td><a href=\"$_SERVER[PHP_SELF]?comment=".$cmdDirName."\">
		<img src=\"img/comment.gif\" border=0></a></td>\n";

            /*** visibility command ***/
            if (@$dirVisibilityList[$dirKey] == "i")
            {
                echo "<td><a href=\"$_SERVER[PHP_SELF]?mkVisibl=".$cmdDirName."\">
			<img src=\"img/invisible.gif\" border =0></a>\n</td>\n";
            }
            else
            {
                echo "<td><a href=\"$_SERVER[PHP_SELF]?mkInvisibl=".$cmdDirName."\">
			<img src=\"img/visible.gif\" border =0></a></td>\n";
            }

            echo "</tr>\n";

            /*** comments ***/
            if ( @$dirCommentList[$dirKey] != "" )
            {
                $dirCommentList[$dirKey] = htmlspecialchars($dirCommentList[$dirKey]);
                $dirCommentList[$dirKey] = nl2br($dirCommentList[$dirKey]);

                echo "<tr align=\"left\">\n";
                echo "<td colspan=\"8\">\n";
                echo "<div class=\"comment\">";
                echo $dirCommentList[$dirKey];
                echo "</div>\n";
                echo "</td>\n";
                echo "</tr>\n";
            }
        }
    }


    /*----------------------------------------
        DISPLAY FILES
    --------------------------------------*/

    if (isset($fileNameList))
    {
        while (list($fileKey, $fileName) = each ($fileNameList))
        {
            $image       = choose_image($fileName);
            $size        = format_file_size($fileSizeList[$fileKey]);
            $date        = format_date($fileDateList[$fileKey]);
            $urlFileName = format_url($baseServUrl.$courseDir.$curDirPath."/".$fileName);
            $cmdFileName = rawurlencode($curDirPath."/".$fileName);
            $dspFileName = htmlspecialchars($fileName);

            if (@$fileVisibilityList[$fileKey] == "i")
            {
                $style=" class=\"invisible\"";
            }
            else
            {
                $style="";
            }



            echo "<tr align=\"center\"".$style.">\n";
            echo "<td align=\"left\">\n";
            echo "<a href=\"".$urlFileName."\" target=_blank".$style.">\n";
            echo "<img src=\"./img/".$image."\" border=0 hspace=5>\n";
            
            
            
            
            
            
            
            
            //h $dspFileName periexei to onoma tou arxeiou sto filesystem
            //h $fakeFileName periexei to onoma tou arxeiou parmeno apo ton pinaka document sth vash
            
            
            // ************* P R O S O X H ***********
            //Aftos o tropos stelnei pollapla erwthmata ston mySQL server & endexetai na ton fortwnei!
            $query = "SELECT filename FROM document WHERE path LIKE '%".$curDirPath."/".$fileName."%'";
            $result = mysql_query ($query);
        	$row = mysql_fetch_array($result);
            
            //ektypwsh tou onomatos tou arxeiou ean yparxei eggrafh sth vash, alliws typwse to onoma tou filesystem (gia logous compability)
            if(empty($row["filename"])) echo $dspFileName."</a>"; else echo $row["filename"]."</a>";
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            echo "<a href='$_SERVER[PHP_SELF]?action=download&id=".$cmdFileName."'>
            <img src=\"./img/save.gif\" border=\"0\" align=\"absmiddle\" title=\"$langSave\"></a>"; 
            
            
            
            
            //ektypwsh twn sxoliwn dipla sto onoma tou arxeiou
            /*** comments ***/
            if ( @$fileCommentList[$fileKey] != "" )
            {
                $fileCommentList[$fileKey] = htmlspecialchars($fileCommentList[$fileKey]);
                $fileCommentList[$fileKey] = nl2br($fileCommentList[$fileKey]);
                
                echo "&nbsp;<span class=\"comment\">";
                echo "(".$fileCommentList[$fileKey].")";
                echo "</span>\n";
            }
            
            
            
            

            echo "</td>";

            /*** size ***/
            echo "<td><small>".$size."</small></td>\n";
            /*** date ***/
            echo "<td><small>".$date."</small></td>\n";

            /*** delete command ***/
            echo "<td><a href=\"$_SERVER[PHP_SELF]?delete=".$cmdFileName."\" onClick=\"return confirmation('".addslashes($dspFileName)."');\">
		<img src=\"img/supprimer.gif\" border=0></a></td>\n";
            /*** copy command ***/
            echo "<td><a href=\"$_SERVER[PHP_SELF]?move=".$cmdFileName."\">
		<img src=\"img/deplacer.gif\" border=0></a></td>\n";
            /*** rename command ***/
            echo "<td><a href=\"$_SERVER[PHP_SELF]?rename=".$cmdFileName."\">
		<img src=\"img/renommer.gif\" border=0></a></td>\n";
            /*** comment command ***/
            echo "<td><a href=\"$_SERVER[PHP_SELF]?comment=".$cmdFileName."\">
		<img src=\"img/comment.gif\" border=0></a></td>\n";

            /*** visibility command ***/
            if (@$fileVisibilityList[$fileKey] == "i")
            {
                echo "<td><a href=\"$_SERVER[PHP_SELF]?mkVisibl=".$cmdFileName."\">
			<img src=\"img/invisible.gif\" border=0></a>\n</td>\n";
            }
            else
            {
                echo "<td><a href=\"$_SERVER[PHP_SELF]?mkInvisibl=".$cmdFileName."\">
			<img src=\"img/visible.gif\" border=0></a></td>\n";
            }

            echo "</tr>\n";

        }
    }
    echo "</table>\n";
    echo "</div>\n";

}

/*>>>>>>>>>>>> END: TEACHER VIEW <<<<<<<<<<<<*/

/*>>>>>>>>>>>> STUDENT VIEW <<<<<<<<<<<<*/

else
{ 


/**************************************
               DISPLAY
**************************************/

//$parentDir = format_url($parentDir);
$dspCurDirName = htmlspecialchars($curDirName);
$cmdCurDirPath = rawurlencode($curDirPath);
$cmdParentDir  = rawurlencode($parentDir);

?>
<div class="fileman" align="center">
<table width="600" border="1" cellspacing="2">

<?

// -- current dir name -->

/*----------------------------------------
           CURRENT DIRECTORY LINE
 --------------------------------------*/

/*** go to parent directory ***/

if ($curDirName) // if the $curDirName is empty, we're in the root point and we can't go to a parent dir
{
    echo "<tr>\n";
    echo "<td colspan=\"3\" align=\"left\">\n";
    echo "<!-- parent dir -->\n";
    echo "<a href=\"$_SERVER[PHP_SELF]?openDir=".$cmdParentDir."\">\n";
    echo "<IMG src=\"img/parent.gif\" border=0 align=\"absbottom\" hspace=5>\n";
    echo "<small>$langUp</small>\n";
    echo "</a>\n";
    echo "</td>\n";
    echo "</tr>\n";
}

/*** current directory ***/

if ($curDirName) // if the $curDirName is empty, we're in the root point and there is'nt a dir name to display
{
    echo "<!-- current dir name -->\n";
    echo "<tr>\n";
    echo "<td colspan=\"3\" align=\"left\" bgcolor=\"#000066\">\n";
    echo "<img src=\"img/opendir.gif\" align=\"absbottom\" vspace=2 hspace=5>\n";
    echo "<font color=\"#CCCCCC\">".$dspCurDirName."</font>\n";
    echo "</td>\n";
    echo "</tr>\n";
}
?>


<!-- command list -->
</tr>
<tr bgcolor="<?= $color2 ?>"  align="center" valign="top">

<?
echo "<td>$langName</td>
<td>$langSize</td>
<td>$langDate</td>
</tr>";

// !-- dir list -->

/*----------------------------------------
            DISPLAY DIRECTORIES
 --------------------------------------*/

if (isset($dirNameList))
{
    while (list($dirKey, $dirName) = each($dirNameList))
    {
        if (@$dirVisibilityList[$dirKey] == "i")
            continue;
        else
        {
            $dspDirName = htmlspecialchars($dirName);
            $cmdDirName = rawurlencode($curDirPath."/".$dirName);

            if (@$dirVisibilityList[$dirKey] == "i") {
                $style = ' class="invisible"';
            } else {
                $style = '';
            }

            echo "<tr align=\"center\">\n";
            echo "<td align=\"left\">\n";
            echo "<a href=\"$_SERVER[PHP_SELF]?openDir=".$cmdDirName."\"".$style.">\n";
            echo "<img src=\"img/dossier.gif\" border=0 hspace=5>\n";
            echo $dspDirName."\n";
            echo "</a>\n";

            /*** skip display date and time ***/
            echo "<td>&nbsp;</td>\n";
            echo "<td>&nbsp;</td>\n";

            echo "</tr>\n";

            /*** comments ***/
            if (@$dirCommentList[$dirKey] != "" )
            {
                $dirCommentList[$dirKey] = htmlspecialchars($dirCommentList[$dirKey]);
                $dirCommentList[$dirKey] = nl2br($dirCommentList[$dirKey]);

                echo "<tr align=\"left\">\n";
                echo "<td colspan=\"3\">\n";
                echo "<div class=\"comment\">";
                echo $dirCommentList[$dirKey];
                echo "</div>\n";
                echo "</td>\n";
                echo "</tr>\n";
            }

        }

    }
}


/*----------------------------------------
             DISPLAY FILES
 --------------------------------------*/

if (isset($fileNameList))
{
    while (list($fileKey, $fileName) = each ($fileNameList))
    {
        $image       = choose_image($fileName);
        $size        = format_file_size($fileSizeList[$fileKey]);
        $date        = format_date($fileDateList[$fileKey]);
        $urlFileName = format_url($baseServUrl.$courseDir.$curDirPath."/".$fileName);
        $cmdFileName = rawurlencode($curDirPath."/".$fileName);
        $dspFileName = htmlspecialchars($fileName);

        if (@($fileVisibilityList[$fileKey] == "i"))
            continue;
        else
        {
            $style='';
            echo "<tr align=\"center\"".$style.">\n";
            echo "<td align=\"left\">\n";
            echo "<a href=\"".$urlFileName."\"".$style.">\n";
            echo "<img src=\"./img/".$image."\" border=0 hspace=5>\n";
            echo $dspFileName."\n";
            echo "</a>\n";

            
            /*** comments ***/
            if (@$fileCommentList[$fileKey] != "" )
            {
                $fileCommentList[$fileKey] = htmlspecialchars($fileCommentList[$fileKey]);
                $fileCommentList[$fileKey] = nl2br($fileCommentList[$fileKey]);

                //echo "<tr align=\"left\">\n";
                //echo "<td colspan=\"8\">\n";
                //echo "<div class=\"comment\">";
                echo $fileCommentList[$fileKey];
                //echo "</div>\n";
                //echo "</td>\n";
                //echo "</tr>\n";
            }
            
            
            echo "<a href='$_SERVER[PHP_SELF]?action=download&id=".$cmdFileName."'>
                                <img src=\"./img/save.gif\" border=\"0\" align=\"absmiddle\" title=\"$langSave\"></a>";

            /*** size ***/
            echo "<td><small>".$size."</small></td>\n";
            /*** date ***/
            echo "<td><small>".$date."</small></td>\n";

            echo "</tr>\n";

            
        }
    }
}
echo "</table>";
echo "</div>";
}

/*>>>>>>>>>>>> END: STUDENT VIEW <<<<<<<<<<<<*/

?>

</body>
</html>
