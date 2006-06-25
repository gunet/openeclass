<?php 
//session_start();

/*
*
*	File : redirector.php
*	
*	Redirector
*
*	This is the main parser of eClass:Personalised
*	Responsible for redirecting to the corresponding tool.
*	Performs logging for future use by the ERA algorithm
*
*	@author Evelthon Prodromou <eprodromou@upnet.gr>
*
*	@access public
*
*	@version 1.0.1
*	
*
*/

if (session_is_registered("uid") && isset($perso)) {
	switch ($perso){
		case 1: { //assignments
			//$c is the lesson code.
			$_SESSION["dbname"] = $c;
			$url = $urlServer."modules/work/work.php?id=".$i;
			header("location:".$url);
			break;
		}

		case 2: {//announcements
			//$c is the lesson code.
			$_SESSION["dbname"] = $c;
			header("location:".$urlServer."modules/announcements/announcements.php");
			break;
		}

		case 3: {//documents
			$menu = lessonToolsMenu();
			//			echo "switch two";
			break;
		}

		case 4: {//agenda
			//$c is the lesson code.
			$_SESSION["dbname"] = $c;
			header("location:".$urlServer."modules/agenda/agenda.php");
			break;
		}
		
		case 5: {//forum
			$_SESSION["dbname"] = $c;
			$url = $urlServer."modules/phpbb/viewtopic.php?topic=".$t."&forum=".$f."&sub=".$s;
			header("location:".$url);
			break;
		}
		
		case 6: {//documents
			$_SESSION["dbname"] = $c;
			$url = $urlServer."modules/document/document.php";
			header("location:".$url);
			break;
		}
	}


}
	
//	persoLogger($mysqlMainDb, $uid, $dbname, $mysqlServer, $mysqlUser, $mysqlPassword);
//	header("location:".$final_url);

elseif (!session_is_registered("uid")){
	die("UNAUTHORISED ACCESS. THIS IS AN INTERNAL SCRIPT AND CANNOT BE ACCESSED DIRECTLY. Please go back to <a href=\"$urlServer\">the login page</a>");
}

//The following functions logs data to be used by the ERA algorithm
function persoLogger($mysqlMainDb, $uid, $dbname, $mysqlServer, $mysqlUser, $mysqlPassword)
{
	$perso_logger =	"INSERT INTO $mysqlMainDb.perso_activity_log
					SELECT '',$uid,$mysqlMainDb.cours.cours_id, now()
					FROM $mysqlMainDb.cours
					WHERE $mysqlMainDb.cours.code = '$dbname'";
	
	$log_link = mysql_connect($mysqlServer, $mysqlUser, $mysqlPassword);			
	mysql_query($perso_logger);
	mysql_close($log_link);
	
}
?>