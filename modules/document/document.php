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

      
/*===========================================================================
	document.php
	@last update: 25-10-2006 by Sakis Agorastos
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================        

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

include '../../include/baseTheme.php';
include 'forcedownload.php';
//include 'gaugebar.php';

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_DOCS');
/**************************************/


$tool_content = "";
$nameTools = $langDoc;
$dbTable = "document";

$require_help = TRUE;
$helpTopic = 'Doc';

// check for quotas
mysql_select_db($mysqlMainDb);
$d = mysql_fetch_array(mysql_query("SELECT doc_quota FROM cours WHERE code='$currentCourseID'"));

$diskQuotaDocument = $d['doc_quota'];

mysql_select_db($currentCourseID);

// -------------------------
// download action2 
// --------------------------

if (@$action2=="download")
 {
		$real_file = $webDir."/courses/".$currentCourseID."/document/".$id;
		if (strpos($real_file, '/../') === FALSE) {
		
		//fortwma tou pragmatikou onomatos tou arxeiou pou vrisketai apothikevmeno sth vash
		$result = mysql_query ("SELECT filename FROM document WHERE path LIKE '%$id%'");            
        	$row = mysql_fetch_array($result);
        	
        	if (!empty($row['filename'])) 
        	{
        		$id = $row['filename'];
        	}
			
    	send_file_to_client($real_file, my_basename($id));
	exit;
		} else {
			header("Refresh: ${urlServer}modules/document/document.php");
 		}
 }


include "../../include/lib/fileDisplayLib.inc.php";
include "../../include/lib/fileManageLib.inc.php";
include "../../include/lib/fileUploadLib.inc.php";

if($is_adminOfCourse)  {
	if (@$uncompress == 1)
        include("../../include/pclzip/pclzip.lib.php");
}



/**************************************
FILEMANAGER BASIC VARIABLES DEFINITION
**************************************/

$baseServDir = $webDir;             
$baseServUrl = $urlAppend."/";      
$courseDir = "courses/$currentCourseID/document";
$baseWorkDir = $baseServDir.$courseDir;

$diskUsed = dir_total_space($baseWorkDir);

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

//begin_page();

//$tool_content .=  '</td></tr></table>';

/*****************************************************************************/

if($is_adminOfCourse) 
{       // TEACHER ONLY



    /*>>>>>>>>>>>> MAIN SECTION  <<<<<<<<<<<<*/


    /********************************************************************************************************************************
                 												UPLOAD FILE
    
    //ousiastika dhmiourgei ena safe_fileName xrhsimopoiwntas ta DATETIME wste na mhn dhmiourgeitai
    //provlhma sto filesystem apo to onoma tou arxeiou. Parola afta to palio filename pernaei apo
    //'filtrarisma' wste na apofefxthoun 'epikyndynoi' xarakthres.
    
    //gia pardeigma me $fileName = "test.jpg" sto filesystem grafetai arxeio $safe_fileName = "20060301121510sdjklhsd.jpg"
    
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
        }
        if (preg_match('/\.(ade|adp|bas|bat|chm|cmd|com|cpl|crt|exe|hlp|hta|' .
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
            
            $result = mysql_query ("SELECT filename FROM document WHERE filename LIKE '%$uploadPath/$fileName%'");
            //$tool_content .= "SELECT filename FROM document WHERE filename LIKE '%$fileName%'";
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
		            $safe_fileName = date("YmdGis").randomkeys("8").".".get_file_extention($fileName);
		            
		            
		            
		                   
		            
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
		            /*$tool_content .=  $query;
		            $tool_content .=  "<br><br>";
		            $tool_content .=  "Copy: $userFile to $baseWorkDir$uploadPath/$safe_fileName<br><br>";
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
    	if($baseWorkDir."/".$source != $baseWorkDir.$moveTo || $baseWorkDir.$source != $baseWorkDir.$moveTo) //elegxos ean source kai destintation einai to idio
    	{
	        if (move($baseWorkDir."/".$source,$baseWorkDir.$moveTo)) {
	            update_db_info("update", $source, $moveTo."/".my_basename($source));
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
    		$moveFileNameAlias = $res['filename'];
    	}
    	
        @$dialogBox .= "<strong>$moveFileNameAlias</strong>".form_dir_list_exclude("source", $moveFileName, "moveTo", $baseWorkDir, $move);
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
    if (isset($renameTo2)) {
	$query =  "UPDATE ".$dbTable." SET filename=\"".$renameTo2."\" WHERE path=\"".$sourceFile."\"";
	//$tool_content .=  "<br><br>".$query."<br><br>";
	mysql_query($query);
			

	if (is_dir("$baseWorkDir/$sourceFile")) {
	echo "F=", $baseWorkDir.$sourceFile, "R=", $renameTo2;
		my_rename($baseWorkDir.$sourceFile, $renameTo2);
		update_db_info("update", $sourceFile,
		   dirname($sourceFile).'/'.$renameTo2);
	}


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
	    	
	    	$fileName = my_basename($rename);    
	        
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
        $newDirName = replace_dangerous_char(trim($newDirName));

        if ( check_name_exist($baseWorkDir.$newDirPath."/".$newDirName) )
        {
            $dialogBox .= "<b>$langFileExists!</b>";
            $createDir = $newDirPath; unset($newDirPath);// return to step 1
        }
        else
        {
            mkdir($baseWorkDir.$newDirPath."/".$newDirName, 0775);
            
            $query =  "INSERT INTO ".$dbTable." SET
    			path=\"".$newDirPath."/".$newDirName."\",
    			filename=\"".$newDirName."\",
    			visibility=\"v\",
				comment=\"\",
				category=\"\",
				title=\"\",
				creator=\"".$prenom." ".$nom."\",
				date=\"".date("Y\-m\-d G\:i\:s")."\",
				date_modified=\"".date("Y\-m\-d G\:i\:s")."\",
				subject=\"\",
				description=\"\",
				author=\"\",
				format=\"\",
				language=\"\",
				copyrighted=\"\"";
            
            mysql_query($query);

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
                
        $query = "SELECT * FROM $dbTable WHERE path LIKE '%".$comment."%'";
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
        
      	$fileName = my_basename($comment);
      	if (empty($oldFilename)) $oldFilename = $fileName;

        
        @$dialogBox .="<!-- comment -->\n";
        $dialogBox .="	<form method=\"post\" action=\"$_SERVER[PHP_SELF]?edit_metadata\">
        					<input type=\"hidden\" name=\"commentPath\" value=\"$comment\">
        					$langAddComment<br>
        						<strong>".htmlspecialchars($oldFilename)."</strong>
        					<hr>
        					$langNotRequired<br>
        					<input type=\"hidden\" size=\"80\" name=\"file_filename\" value=\"$oldFilename\"><br>
        					$langComment:<br><input type=\"text\" size=\"80\" name=\"file_comment\" value=\"$oldComment\"><br>
							$langTitle&nbsp;:<br><input type=\"text\" size=\"80\" name=\"file_title\" value=\"$oldTitle\"><br>
							$langCategory:<br>							";
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
    						$langSubject&nbsp;:<Br><input type=\"text\" size=\"80\" name=\"file_subject\" value=\"$oldSubject\"><br>
    						$langDescription&nbsp;:<br><input type=\"text\" size=\"80\" name=\"file_description\" value=\"$oldDescription\"><br>
    						$langAuthor&nbsp;:<br><input type=\"text\" size=\"80\" name=\"file_author\" value=\"$oldAuthor\"><br>";
    	
		$dialogBox .= "		$langCopyrighted&nbsp;:<br><input name=\"file_copyrighted\" type=\"radio\" value=\"0\" "; if ($oldCopyrighted=="0" || empty($oldCopyrighted)) $dialogBox .= " checked=\"checked\" "; $dialogBox .= " /> $langCopyrightedUnknown
    					   							<input name=\"file_copyrighted\" type=\"radio\" value=\"2\" "; if ($oldCopyrighted=="2") $dialogBox .= " checked=\"checked\" "; $dialogBox .= " /> $langCopyrightedFree
  						   							<input name=\"file_copyrighted\" type=\"radio\" value=\"1\" "; if ($oldCopyrighted=="1") $dialogBox .= " checked=\"checked\" "; $dialogBox .= "/> $langCopyrightedNotFree
  						   							
  						   							
  		<br><input type=\"hidden\" size=\"80\" name=\"file_oldLanguage\" value=\"$oldLanguage\">";
    						
    						
    						
    	//ektypwsh tou combox gia epilogh glwssas			
    	
		$dialogBox .= "		<br>$langLanguage&nbsp;: $oldLanguage<br>
		
		
		
		
		
		
		
															
															<select name=\"file_language\">
																<option selected=\"selected\" value=\"\">
																</option><option value=\"en\">English
																</option><option value=\"fr\">French
																</option><option value=\"de\">German
																</option><option value=\"el\">Greek
																</option><option value=\"it\">Italian
																</option><option value=\"es\">Spanish																								</option>
															
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

        // analoga me poia metavlhth exei timh ($mkVisibl h' $mkInvisibl) vale antistoixh
        //timh sthn $newVisibilityStatus gia na graftei sth vash
        if (isset($mkVisibl)) $newVisibilityStatus = "v"; else $newVisibilityStatus = "i";
        
        
        /*** enallagh ths timhs sto pedio visibility tou pinaka document ***/
        mysql_query ("UPDATE $dbTable SET visibility='".$newVisibilityStatus."' WHERE path LIKE '%".$visibilityPath."%'");
        
        /* o parakatw kwdikas exei ginei comment out giati den xreiazetai.
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
        */

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

$curDirName = my_basename($curDirPath);
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
            if (@$dirNameList && in_array(my_basename($chekinFile), $dirNameList))
                continue;
            elseif (@$fileNameList && in_array(my_basename($chekinFile), $fileNameList))
                continue;
            else
                $recToDel[]= $chekinFile; // add chekinFile to the list of records to delete
        }

        /* Build the query to delete deprecated DB records */
        $queryClause = "";
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
	$tool_content .=  $langInvalidDir;
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

    $tool_content .= "
    <div class=\"fileman\" align=\"center\">
    <table width=\"80%\" border=\"0\" cellspacing=\"2\" cellpadding=\"4\">
    <tr>";
    
    /*------------------------------------------------------------------------------------------------------------------------------
        UPLOAD SECTION (ektypwnei th forma me ta stoixeia gia upload eggrafou + ola ta pedia gia ta metadata symfwna me Dublin Core)
    -------------------------------------------------------------------------------------------------------------------------------*/
    $tool_content .=  "<!-- upload  -->
    <td align=\"left\">
    	<a href=\"upload.php?uploadPath=$curDirPath\">$langDownloadFile</a>
   	&nbsp;|&nbsp;";
    
    /*----------------------------------------
        Create new folder
    --------------------------------------*/
    $tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?createDir=".$cmdCurDirPath."\">$langCreateDir</small></a>
    </td>";
  	
  	/*----------------------------------------
        HELP (metaferthike parapanw sto neo template)
    --------------------------------------*/
  	/*$tool_content .=  "
        <td align=\"right\">
        <a href=\"../help/help.php?topic=Doc&language=$language\" 
        onClick=\"window.open('../help/help.php?topic=Doc&language=$language','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); 
        return false;\">
        <font size=2 face=\"arial, helvetica\">$langHelp</font>
        </a>
        </td>
    </tr>

    <tr>";*/
  	$tool_content .= "</tr>"; //mphke sto meros ths palias 'voitheias'



    /*----------------------------------------
        DIALOG BOX SECTION
    --------------------------------------*/
    if (!empty($dialogBox))
    {
        $tool_content .=  "<td class=\"success\" colspan=\"2\">";
        $tool_content .=  "<!-- dialog box -->";
        $tool_content .=  $dialogBox;
        $tool_content .=  "</td>";
    }
    else
    {
        $tool_content .=  "<td colspan=\"2\">\n<!-- dialog box -->\n&nbsp;\n</td>\n";
    }


	$tool_content .="
    </tr>
    </table>
    
    <br>
    
    <table width=\"80%\" border=\"1\" cellspacing=\"2\">";



    /*----------------------------------------
        CURRENT DIRECTORY LINE
    --------------------------------------*/

    $tool_content .=  "<tr>\n";
    $tool_content .=  "<td colspan=8>";

    /*** go to parent directory ***/
    if ($curDirName) // if the $curDirName is empty, we're in the root point and we can't go to a parent dir
    {
        $tool_content .=  "<!-- parent dir -->\n";
        $tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?openDir=".$cmdParentDir."\">\n";
        $tool_content .=  "<IMG src=\"img/parent.gif\" border=0 align=\"absbottom\" hspace=5>\n";
        $tool_content .=  "<small>$langUp</small>\n";
        $tool_content .=  "</a>\n";
    }


    /*** create directory ***/
    /*$tool_content .=  "<!-- create dir -->\n";
    $tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?createDir=".$cmdCurDirPath."\">";
    $tool_content .=  "<IMG src=\"img/dossier.gif\" border=0 align=\"absbottom\" hspace=5>";
    $tool_content .=  "<small> $langCreateDir</small>";
    $tool_content .=  "</a>";*/

    $tool_content .=  "</tr>\n";
    $tool_content .=  "</td>\n";


    //ektypwsh tou onomatos tou CWD (exei ginei comment out kai emfanizetai parakatw sto <thead>)
    if ($curDirName) // if the $curDirName is empty, we're in the root point and there is'nt a dir name to display
    {
        /*** current directory ***/
        /*$tool_content .=  "<!-- current dir name -->\n";
        $tool_content .=  "<tr>\n";
        $tool_content .=  "<td colspan=\"8\" align=\"left\" bgcolor=\"#000066\">\n";
        $tool_content .=  "<img src=\"img/opendir.gif\" align=\"absbottom\" vspace=2 hspace=5>\n";
        $tool_content .=  "<font color=\"#CCCCCC\">".$dspCurDirName."</font>\n";
        $tool_content .=  "</td>\n";
        $tool_content .=  "</tr>\n";*/
    }
    



    $tool_content .= "<!-- command list -->
    <thead>";
    
    if (!empty($dspCurDirName)) $tool_content .= "<tr><th colspan=\"4\"><h3>".
    	make_clickable_path($curDirPath) . "</h3></th></tr>";
    
    $tool_content .= "
    	<tr bgcolor=\"$color2\" align=\"center\" valign=\"top\">
			<th>$langName</th>
		    <th>$langSize</th>
		    <th>$langDate</th>
		    <th>$langCommands</th>
    	</tr>
    </thead>";


    
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
                $style2 = " class=\"invisible_doc\"";
            }
            else
            {
                $style="";
                $style2="";
            }

            $tool_content .=  "<tr align=\"center\" $style2>\n";
            $tool_content .=  "<td align=\"left\">\n";
            $tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?openDir=".$cmdDirName."\"".$style.">\n";
            $tool_content .=  "<img src=\"img/dossier.gif\" border=0 hspace=5>\n";
            $tool_content .=  $dspDirName."\n";
            $tool_content .=  "</a>";
            
            /*** comments ***/
            if ( @$dirCommentList[$dirKey] != "" )
            {
                $dirCommentList[$dirKey] = htmlspecialchars($dirCommentList[$dirKey]);
                $dirCommentList[$dirKey] = nl2br($dirCommentList[$dirKey]);

                $tool_content .=  "<span class=\"comment\">";
                $tool_content .=  "(".$dirCommentList[$dirKey].")";
                $tool_content .=  "</span>\n";
            }

            /*** skip display date and time ***/
            $tool_content .=  "<td>&nbsp;</td>\n";
            $tool_content .=  "<td>&nbsp;</td>\n";

            /*** delete command ***/
            $tool_content .=  "<td><table><tr><td><a href=\"$_SERVER[PHP_SELF]?delete=".$cmdDirName."\" onClick=\"return confirmation('".addslashes($dspDirName)."');\">
		<img src=\"./img/supprimer.gif\" border=0 title=\"$langDelete\"></a></td>";
            /*** copy command ***/
            $tool_content .=  "<td><a href=\"$_SERVER[PHP_SELF]?move=".$cmdDirName."\">
		<img src=\"img/deplacer.gif\" border=0 title=\"$langMove\"></a></td>";
            /*** rename command ***/
            $tool_content .=  "<td><a href=\"$_SERVER[PHP_SELF]?rename=".$cmdDirName."\">
		<img src=\"img/renommer.gif\" border=0 title=\"$langRename\"></a></td>";
            /*** comment command ***/
            $tool_content .= "<td><a href=\"$_SERVER[PHP_SELF]?comment=".$cmdDirName."\">
		<img src=\"img/comment.gif\" border=0 title=\"$langComment\"></a></td>";

            /*** visibility command ***/
            if (@$dirVisibilityList[$dirKey] == "i")
            {
                $tool_content .=  "<td><a href=\"$_SERVER[PHP_SELF]?mkVisibl=".$cmdDirName."\">
			<img src=\"img/invisible.gif\" border=0 title=\"$langVisible\"></a></td>";
            }
            else
            {
                $tool_content .=  "<td><a href=\"$_SERVER[PHP_SELF]?mkInvisibl=".$cmdDirName."\">
			<img src=\"img/visible.gif\" border=0 title=\"$langVisible\"></a></td>";
            }

            $tool_content .=  "</tr></table></td></tr>";

            
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
                $style2=" class=\"invisible_doc\"";
            }
            else
            {
                $style="";
                $style2="";
            }



            $tool_content .=  "<tr align=\"center\"".$style2.">\n";
            $tool_content .=  "<td align=\"left\">\n";
          //aferaish tou: kathe arxeio na einai hyperlink me apeftheias URL -  $tool_content .=  "<a href=\"".$urlFileName."\" target=_blank".$style.">\n";
            $tool_content .=  "<img src=\"./img/".$image."\" border=0 hspace=5>\n";
            
            
            
            
            
            
            
            
            //h $dspFileName periexei to onoma tou arxeiou sto filesystem
            //h $fakeFileName periexei to onoma tou arxeiou parmeno apo ton pinaka document sth vash
            
            
            // ************* P R O S O X H ***********
            //Aftos o tropos stelnei pollapla erwthmata ston mySQL server & endexetai na ton fortwnei!
            $query = "SELECT filename, copyrighted FROM document WHERE path LIKE '%".$curDirPath."/".$fileName."%'";
            $result = mysql_query ($query);
        	$row = mysql_fetch_array($result);
            
            //ektypwsh tou onomatos tou arxeiou ean yparxei eggrafh sth vash, alliws typwse to onoma tou filesystem (gia logous compability)
            //aferaish tou: kathe arxeio na einai hyperlink me apeftheias URL  -  if(empty($row["filename"])) $tool_content .=  $dspFileName."</a>"; else $tool_content .=  $row["filename"]."</a>";
            if(empty($row["filename"]))
            {
            	$tool_content .=  "<a href='$_SERVER[PHP_SELF]?action2=download&id=".$cmdFileName."' title=\"$langSave\">".$dspFileName."</a>"; 
            }else
            {
            	$tool_content .=  "<a href='$_SERVER[PHP_SELF]?action2=download&id=".$cmdFileName."' title=\"$langSave\">".$row["filename"];
            	if ($row["copyrighted"] == "1") $tool_content .= "<img src=\"./img/copyrighted.jpg\" border=\"0\">";
            	$tool_content .= "</a>";
            }
            
            
            
            //$tool_content .=  "<a href='$_SERVER[PHP_SELF]?action2=download&id=".$cmdFileName."' title=\"$langSave\"><img src=\"./img/save.gif\" border=\"0\" align=\"absmiddle\" title=\"$langSave\"></a>"; 
            
            
            
            //ektypwsh twn sxoliwn dipla sto onoma tou arxeiou
            /*** comments ***/
            if ( @$fileCommentList[$fileKey] != "" )
            {
                $fileCommentList[$fileKey] = htmlspecialchars($fileCommentList[$fileKey]);
                $fileCommentList[$fileKey] = nl2br($fileCommentList[$fileKey]);
                
                $tool_content .=  "&nbsp;<span class=\"comment\">";
                $tool_content .=  "(".$fileCommentList[$fileKey].")";
                $tool_content .=  "</span>\n";
            }
            
            
            
            

            $tool_content .=  "</td>";

            /*** size ***/
            $tool_content .=  "<td><small>".$size."</small></td>\n";
            /*** date ***/
            $tool_content .=  "<td><small>".$date."</small></td>\n";

            /*** delete command ***/
            $tool_content .=  "<td><table><tr><td><a href=\"$_SERVER[PHP_SELF]?delete=".$cmdFileName."\" onClick=\"return confirmation('".addslashes($row["filename"])."');\">
		<img src=\"img/supprimer.gif\" border=0  title=\"$langDelete\"></a></td>";
            /*** copy command ***/
            $tool_content .=  "<td><a href=\"$_SERVER[PHP_SELF]?move=".$cmdFileName."\">
		<img src=\"img/deplacer.gif\" border=0  title=\"$langCopy\"></a></td>";
            /*** rename command ***/
            $tool_content .=  "<td><a href=\"$_SERVER[PHP_SELF]?rename=".$cmdFileName."\">
		<img src=\"img/renommer.gif\" border=0  title=\"$langRename\"></a></td>";
            /*** comment command ***/
            $tool_content .=  "<td><a href=\"$_SERVER[PHP_SELF]?comment=".$cmdFileName."\">
		<img src=\"img/comment.gif\" border=0  title=\"$langComment\"></a></td>";

            /*** visibility command ***/
            if (@$fileVisibilityList[$fileKey] == "i")
            {
                $tool_content .=  "<td><a href=\"$_SERVER[PHP_SELF]?mkVisibl=".$cmdFileName."\">
			<img src=\"img/invisible.gif\" border=0  title=\"$langVisible\"></a></td>";
            }
            else
            {
                $tool_content .=  "<td><a href=\"$_SERVER[PHP_SELF]?mkInvisibl=".$cmdFileName."\">
			<img src=\"img/visible.gif\" border=0  title=\"$langVisible\"></a></td>";
            }

            $tool_content .=  "</tr></table></td></tr>\n";

        }
    }
    $tool_content .=  "</table>";
    
    //emfanish link gia to quota bar
    $diskQuotaDocument = $diskQuotaDocument * 1024 / 1024;
    //$tool_content .= "<a href=\"showquota.php?diskQuotaDocument=$diskQuotaDocument&diskUsed=$diskUsed\" target=\"blank\">$langQuotaBar</a>";
    $tool_content .= "<a href=\"showquota.php?diskQuotaDocument=$diskQuotaDocument&diskUsed=$diskUsed\">$langQuotaBar</a>";
    
    $tool_content .=  "</div>";

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

$tool_content .= "
<div class=\"fileman\" align=\"center\">
<table width=\"99%\" border=\"1\" cellspacing=\"2\">";


// -- current dir name -->

/*----------------------------------------
           CURRENT DIRECTORY LINE
 --------------------------------------*/


/*** current directory ***/


//exei ginei comment out. efmanizetai parakatw (mesa sto <thead>)
/*if ($curDirName) // if the $curDirName is empty, we're in the root point and there isn't a dir name to display
{
    $tool_content .=  "<!-- current dir name -->\n";
    $tool_content .=  "<tr>\n";
    $tool_content .=  "<td colspan=\"3\" align=\"left\" bgcolor=\"#000066\">\n";
    $tool_content .=  "<img src=\"img/opendir.gif\" align=\"absbottom\" vspace=2 hspace=5>\n";
    $tool_content .=  "<font color=\"#CCCCCC\">".$dspCurDirName."</font>\n";
    $tool_content .=  "</td>\n";
    $tool_content .=  "</tr>\n";
}*/

/*** go to parent directory ***/

if ($curDirName) // if the $curDirName is empty, we're in the root point and we can't go to a parent dir
{
    $tool_content .=  "<tr>\n";
    $tool_content .=  "<td colspan=\"3\" align=\"left\">\n";
    $tool_content .=  "<!-- parent dir -->\n";
    $tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?openDir=".$cmdParentDir."\">\n";
    $tool_content .=  "<IMG src=\"img/parent.gif\" border=0 align=\"absbottom\" hspace=5>\n";
    $tool_content .=  "<small>$langUp</small>\n";
    $tool_content .=  "</a>\n";
    $tool_content .=  "</td>\n";
    $tool_content .=  "</tr>\n";
}

$tool_content .= "
<!-- command list -->
</tr>
<thead>
	<tr>
		<th colspan=\"3\"><h3>".make_clickable_path($curDirPath)."</h3></th>
	</tr>";


$tool_content .= "
	<tr bgcolor=\"$color2\" align=\"center\" valign=\"top\">
		<th>$langName</th>
		<th>$langSize</th>
		<th>$langDate</th>
	</tr>
</thead>";

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

            $tool_content .=  "<tr align=\"center\">\n";
            $tool_content .=  "<td align=\"left\">\n";
            $tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?openDir=".$cmdDirName."\"".$style.">\n";
            $tool_content .=  "<img src=\"img/dossier.gif\" border=0 hspace=5>\n";
            $tool_content .=  $dspDirName."\n";
            $tool_content .=  "</a>\n";

            

            /*** comments ***/
            if (@$dirCommentList[$dirKey] != "" )
            {
                $dirCommentList[$dirKey] = htmlspecialchars($dirCommentList[$dirKey]);
                $dirCommentList[$dirKey] = nl2br($dirCommentList[$dirKey]);

                $tool_content .=  "<span class=\"comment\">";
                $tool_content .=  "(".$dirCommentList[$dirKey].")";
                $tool_content .=  "</span>\n";
            }
            
            /*** skip display date and time ***/
            $tool_content .=  "<td>&nbsp;</td>\n";
            $tool_content .=  "<td>&nbsp;</td>\n";

            $tool_content .=  "</tr>\n";

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
            $tool_content .=  "<tr align=\"center\"".$style.">\n";
            $tool_content .=  "<td align=\"left\">\n";
            $tool_content .=  "<a href=\"".$urlFileName."\"".$style.">\n";
            $tool_content .=  "<img src=\"./img/".$image."\" border=0 hspace=5>\n";
            $tool_content .=  "<a href='$_SERVER[PHP_SELF]?action2=download&id=".$cmdFileName."' title=\"$langSave\">".$dspFileName."</a>";
            $tool_content .=  "</a>\n";

            
            /*** comments ***/
            if (@$fileCommentList[$fileKey] != "" )
            {
                $fileCommentList[$fileKey] = htmlspecialchars($fileCommentList[$fileKey]);
                $fileCommentList[$fileKey] = nl2br($fileCommentList[$fileKey]);

                //$tool_content .=  "<tr align=\"left\">\n";
                //$tool_content .=  "<td colspan=\"8\">\n";
                $tool_content .=  "<span class=\"comment\">";
                $tool_content .=  "(".$fileCommentList[$fileKey].")";
                $tool_content .=  "</span>\n";
                //$tool_content .=  "</td>\n";
                //$tool_content .=  "</tr>\n";
            }
            
            
            //$tool_content .=  "<a href='$_SERVER[PHP_SELF]?action2=download&id=".$cmdFileName."' title=\"$langSave\"></a>";

            /*** size ***/
            $tool_content .=  "<td><small>".$size."</small></td>\n";
            /*** date ***/
            $tool_content .=  "<td><small>".$date."</small></td>\n";

            $tool_content .=  "</tr>\n";

            
        }
    }
}
$tool_content .=  "</table>";
$tool_content .=  "</div>";
}

/*>>>>>>>>>>>> END: STUDENT VIEW <<<<<<<<<<<<*/

$tmp_cwd = getcwd();
chdir($baseServDir."/modules/document/");
draw($tool_content, 2, "document", $local_head);
chdir($tmp_cwd);




//epipleon functions

function make_clickable_path($path)
{
	$cur = '';
	$out = '';
	$base = $_SERVER['PHP_SELF'];
	foreach (explode('/', $path) as $component) {
		if (empty($component)) {
			$out = "<a href='$base?openDir=/'>root</a>";
		} else {
			$cur .= rawurlencode("/$component");
			$out .= " &raquo; <a href='$base?openDir=$cur'>$component</a>";
		}
	}
	return $out;
}


//function pou epistrefei tyxaious xarakthres. to orisma $length kathorizei to megethos tou apistrefomenou xarakthra
function randomkeys($length)
{
   $key = "";
   $pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
   for($i=0;$i<$length;$i++)
   {
     $key .= $pattern{rand(0,35)};
   }
   return $key;
   
}

 
// A helper function, when passed a number representing KB,
// and optionally the number of decimal places required,
// it returns a formated number string, with unit identifier.
function format_bytesize ($kbytes, $dec_places = 2)
{
    global $text;
    if ($kbytes > 1048576) {
        $result  = sprintf('%.' . $dec_places . 'f', $kbytes / 1048576);
        $result .= '&nbsp;Gb';
    } elseif ($kbytes > 1024) {
        $result  = sprintf('%.' . $dec_places . 'f', $kbytes / 1024);
        $result .= '&nbsp;Mb';
    } else {
        $result  = sprintf('%.' . $dec_places . 'f', $kbytes);
        $result .= '&nbsp;Kb';
    }
    return $result;
}
?>
