<?php

include '../include/lib/fileUploadLib.inc.php';
include '../modules/document/forcedownload.php';

//anadromikos algorithmos scannarismatos arxeiwn kai fakelwmn - einai h kyria function gia to scannarisma olwn twn arxeiwn kai twn fakelwn 
function RecurseDir($directory, $baseFolder)
{
	//echo "<b>".getcwd()."</b>";
	
	$thisdir = array("name", "struct");
	$thisdir['name'] = $directory;
	if ($dir = @opendir($directory))
	{
	  $i = 0;
		while ($file = readdir($dir))
		{
			if (($file != ".")&&($file != ".."))
			{
				$tempDir = $directory."\\".$file;
				if (is_dir($tempDir)) {
				  $thisdir['struct'][] = RecurseDir($tempDir, $baseFolder);
				} else {			
				  $thisdir['struct'][] = $file;
				  
				  $tmp = $directory;
				  $tmp = substr($tmp, strlen($baseFolder));
				  $tmp = str_replace("\\", "/", $tmp);
				  
				  if (empty($tmp)) 
				  {
				  	upgrade_document("/", $file);
				  }
				  else
				  {
				  	upgrade_document("/".$tmp."/", $file);
				  }
				} 
		  	$i++;
			} 
			
		} 
		
		if ($i == 0)
		{
		  // empty directory
		  $thisdir['struct'] = -2;
		}
		
	} else
	{
	  // directory could not be accessed
		$thisdir['struct'] = -1;
	}
	
	//echo "<hr>";
	//print_r ($thisdir);
	return $thisdir;
}//telos anadromikou algorithmou scannarismatos arxeiwn kai fakelwn



//leitourgeia pou pairnei san orismata $uploadPath = sxetikos fakelos pou vrisketai to arxeio (p.x. "/" h' "/folder/") kai $file = pragmatiko_onoma_arxeiou
//h leitourgia metonomazei to onoma tou arxeiou se kapoio me safe_filename xrhsimopoiwntas enan genhtora monadikou arithmou kai prosthetei ston pinaka document ths vashs tou kathe mathimatos (h vash exei proepilegei apo prohgoumena vhmata) ta anagkaia metadedomena
function upgrade_document ($uploadPath, $file)
{
	
	$fileName = trim ($file);

        	
        	
	//elegxos ean to "path" tou arxeiou pros upload vrisketai hdh se eggrafh ston pinaka documents
    //(aftos einai ousiastika o elegxos if_exists dedomenou tou oti to onoma tou arxeiou sto filesystem
    //einai monadiko)
    
    $result = mysql_query ("SELECT filename FROM document WHERE path LIKE '%".$uploadPath.$fileName."%'");
    //echo "<br>SELECT filename FROM document WHERE path LIKE '%".$uploadPath.$fileName."%'<br>";
    
	$row = mysql_fetch_array($result);
	
	if (empty($row['filename'])) //to arxeio den vrethike sth vash ara mporoume na proxwrhsoume me to upload
	{


            /**** Check for no desired characters ***/
            $fileName = replace_dangerous_char($fileName);

            /*** Try to add an extension to files witout extension ***/
            $fileName = add_ext_on_mime($fileName);
            
            /*** Handle PHP files ***/
            $fileName = php2phps($fileName);
            
            
            
            
            //ypologismos onomatos arxeiou me date + time + 3 tyxaia alfarithmitika psifia.
            //to onoma afto tha xrhsimopoiei sto filesystem & tha apothikevetai ston pinaka documents
            $safe_fileName = date("YmdGis")."_".randomkeys('3').".".get_file_extention($fileName);
            
            
            
                   
            
            //prosthiki eggrafhs kai metadedomenwn gia to eggrafo sth vash
            if ($uploadPath == ".")
            {
            	$uploadPath2 = "/".$safe_fileName;
            	$existinguploadPath =  "/".str_replace("/", "\\", $fileName);        
            }
            else
            {
            	$uploadPath2 = $uploadPath.$safe_fileName;
            	$existinguploadPath = $uploadPath.$fileName;
            }
            
            
            //san file format vres to extension tou arxeiou
            $file_format = get_file_extention($fileName);
            
            //san date you arxeiou xrhsimopoihse thn shmerinh hm/nia
            $file_date = date("Y\-m\-d G\:i\:s");
            
            
           
            //arxikopoihsh timwn twn metavlhtwn gia to upgrade twn eggrafwn
            $file_comment = "";
            $file_category = "";
            $file_title = "";
            $file_creator = "";
            $file_subject = "";
            $file_description = "";
            $file_author = "";
            $file_format = "";
            $file_language = "";
            $file_copyrighted = "";
            
            
            $query = "INSERT INTO document SET 
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
            
            
            
            
            /*** Allagh onomatos arxeiou me safe_filename ***/
            rename (getcwd().$existinguploadPath, getcwd().$uploadPath2);
            //echo "<br>rename ".getcwd().$existinguploadPath." ---TO--- ".getcwd().$uploadPath2."<br>";
            
            
            //echo "<br>query: $query<br><hr>";
            mysql_query($query);
            
            
            
            
                  
	} //telos if(empty($row['filename']))
        	
	
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
?>