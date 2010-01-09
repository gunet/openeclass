<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
/*===========================================================================
	bcms.inc.php
	@last update: 09-01-2010 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
==============================================================================
    @Description: 

    @Comments:
==============================================================================
*/

define ("BETACMSREPO", "betacmsrepo");

define ("BRIDGE_HOST", "bridge_host");
define ("BRIDGE_CONTEXT", "bridge_context");
define ("BCMS_HOST", "bcms_host");
define ("BCMS_REPO", "bcms_repo");
define ("BCMS_USER", "bcms_user");
define ("BCMS_PASS", "bcms_pass");

define ("ECLASS_LESSON_OBJECT", "eClassLessonObject");

define ("KEY_ID", "id");
define ("KEY_TITLE", "title");
define ("KEY_DESCRIPTION", "description");
define ("KEY_KEYWORDS", "keywords");
define ("KEY_COPYRIGHT", "copyright");
define ("KEY_AUTHORS", "authors");
define ("KEY_PROJECT", "project");
define ("KEY_COMMENTS", "comments");
define ("KEY_UNITS", "units");
define ("KEY_UNITS_SIZE", "units_size");
define ("KEY_SCORMFILES", "scormFiles");
define ("KEY_SCORMFILES_SIZE", "scormFiles_size");
define ("KEY_SOURCEFILENAME", "sourcefilename");
define ("KEY_MIMETYPE", "mimetype");
define ("KEY_CALCULATEDSIZE", "calculatedsize");
define ("KEY_FILECONTENT", "filecontent");
define ("KEY_CONTENT", "content");

define ("PRKEY_TITLE", "profile.title");
define ("PRKEY_DESCRIPTION", "lessonDescription");

define ("IMPORT_FLAG", "bcms_flag");
define ("IMPORT_FLAG_INITIATED", "bcms_flag_initiated");
define ("IMPORT_ID", "bcms_id");
define ("IMPORT_INTITULE", "bcms_intitule");
define ("IMPORT_DESCRIPTION", "bcms_description");
define ("IMPORT_COURSE_KEYWORDS", "bcms_course_keywords");
define ("IMPORT_COURSE_ADDON", "bcms_course_addon");
define ("IMPORT_UNITS", "bcms_units");
define ("IMPORT_UNITS_SIZE", "bcms_units_size");
define ("IMPORT_SCORMFILES", "bcms_scormFiles");
define ("IMPORT_SCORMFILES_SIZE", "bcms_scormFiles_size");

define ("JAVA_PREFER_VALUES", true);


function getLessonsList($bcmsrepo) {
	$bridge_host = $bcmsrepo[BRIDGE_HOST];
	$bridge_context = $bcmsrepo[BRIDGE_CONTEXT];
	$bcms_host = $bcmsrepo[BCMS_HOST];
	$bcms_repo = $bcmsrepo[BCMS_REPO];
	$bcms_user = $bcmsrepo[BCMS_USER];
	$bcms_pass = $bcmsrepo[BCMS_PASS];
	
	$lessonsArray = array();
	
	$cli = connectToRepo($bcmsrepo);
	
	if (isset($cli)) {
		require_once("http://".$bridge_host."/".$bridge_context."/java/Java.inc");
		
		$criteria = java("org.betaconceptframework.betacms.repository.model.factory.CmsCriteriaFactory")->newContentObjectCriteria();
		$criteria->addContentObjectTypeEqualsCriterion(ECLASS_LESSON_OBJECT);
		$criteria->setCacheable(java("org.betaconceptframework.betacms.repository.api.model.query.CacheRegion")->FIVE_MINUTES);
		
		//Execute query
		$cmsOutcome = $cli->getContentService()->searchContentObjects($criteria);
		
		$i = 0;
		
		foreach ($cmsOutcome->getResults() as $key => $cmsRankedOutcome) {
			$co = $cmsRankedOutcome->getCmsRepositoryEntity();
			// $contentObjectType = $co->getContentObjectType();
			
			$titlePR = $co->getCmsProperty(PRKEY_TITLE);
			$lessonDescPR = $co->getCmsProperty(PRKEY_DESCRIPTION);
			$keywordsPR = $co->getCmsProperty(KEY_KEYWORDS);
			$copyrightPR = $co->getCmsProperty(KEY_COPYRIGHT);
			$authorsPR = $co->getCmsProperty(KEY_AUTHORS);
			$projectPR = $co->getCmsProperty(KEY_PROJECT);
			$commentsPR = $co->getCmsProperty(KEY_COMMENTS);
			
			$lessonsArray[$i] = array(
				KEY_ID => java_values($co->getId()),
				KEY_TITLE => java_values($titlePR->getSimpleTypeValue()),
				KEY_DESCRIPTION => java_values($lessonDescPR->getSimpleTypeValue()),
				KEY_KEYWORDS => java_values($keywordsPR->getSimpleTypeValue()),
				KEY_COPYRIGHT => java_values($copyrightPR->getSimpleTypeValue()),
				KEY_AUTHORS => java_values($authorsPR->getSimpleTypeValue()),
				KEY_PROJECT => java_values($projectPR->getSimpleTypeValue()),
				KEY_COMMENTS => java_values($commentsPR->getSimpleTypeValue())
			);
					
			$i++;
		}
	}
	else {
		return null;
	}
	
	return $lessonsArray;
}


function getLesson($bcmsrepo, $objectId) {
	$bridge_host = $bcmsrepo[BRIDGE_HOST];
	$bridge_context = $bcmsrepo[BRIDGE_CONTEXT];
	$bcms_host = $bcmsrepo[BCMS_HOST];
	$bcms_repo = $bcmsrepo[BCMS_REPO];
	$bcms_user = $bcmsrepo[BCMS_USER];
	$bcms_pass = $bcmsrepo[BCMS_PASS];
	
	$cli = connectToRepo($bcmsrepo);
	
	if (isset($cli)) {
		require_once("http://".$bridge_host."/".$bridge_context."/java/Java.inc");
		
		$co = $cli->getContentService()->getContentObjectById($objectId, 
			java("org.betaconceptframework.betacms.repository.api.model.query.CacheRegion")->FIVE_MINUTES);
			
		$titlePR = $co->getCmsProperty(PRKEY_TITLE);
		$lessonDescPR = $co->getCmsProperty(PRKEY_DESCRIPTION);
		$keywordsPR = $co->getCmsProperty(KEY_KEYWORDS);
		$copyrightPR = $co->getCmsProperty(KEY_COPYRIGHT);
		$authorsPR = $co->getCmsProperty(KEY_AUTHORS);
		$projectPR = $co->getCmsProperty(KEY_PROJECT);
		$commentsPR = $co->getCmsProperty(KEY_COMMENTS);
		$unitsPR = $co->getCmsPropertyList(KEY_UNITS);
		$scosPR = $co->getCmsProperty(KEY_SCORMFILES);
		
		$unitArray = array();
		$unitsSize = 0;
		if (!java_is_null($unitsPR) && $unitsPR->size() > 0) {
			for ($i = 0; $i < java_values($unitsPR->size()); $i++) {
				$ccprop = $unitsPR->get($i);
				$unitTitle = java_values($ccprop->getChildProperty(KEY_TITLE)->getSimpleTypeValue());
				$unitDesc = java_values($ccprop->getChildProperty(KEY_DESCRIPTION)->getSimpleTypeValue());
				if (isset($unitTitle) && isset($unitDesc)) {
					$ar = array(KEY_TITLE => $unitTitle, KEY_DESCRIPTION => $unitDesc);
					$unitArray[$i] = $ar;
					$unitsSize++;
				}
			}
		}
		
		$scoArray = array();
		$scoindex = 0;
		if (!java_is_null(scosPR)) {
			$scos = $scosPR->getSimpleTypeValues();
			foreach ($scos as $key => $sco) {
				$bp = $sco->getCmsProperty(KEY_CONTENT);
				$bc = $bp->getSimpleTypeValue();
				$scoArray[$scoindex] = array(
					KEY_SOURCEFILENAME => java_values($bc->getSourceFilename()),
					KEY_MIMETYPE => java_values($bc->getMimeType()),
					KEY_CALCULATEDSIZE => java_values($bc->getCalculatedSize()),
					KEY_FILECONTENT => java_values($bc->getContent())
					);
				$scoindex++;
			}
		}
		
		$retArray = array(
			KEY_ID => java_values($co->getId()),
			KEY_TITLE => java_values($titlePR->getSimpleTypeValue()),
			KEY_DESCRIPTION => java_values($lessonDescPR->getSimpleTypeValue()),
			KEY_KEYWORDS => java_values($keywordsPR->getSimpleTypeValue()),
			KEY_COPYRIGHT => java_values($copyrightPR->getSimpleTypeValue()),
			KEY_AUTHORS => java_values($authorsPR->getSimpleTypeValue()),
			KEY_PROJECT => java_values($projectPR->getSimpleTypeValue()),
			KEY_COMMENTS => java_values($commentsPR->getSimpleTypeValue()),
			KEY_UNITS => $unitArray,
			KEY_UNITS_SIZE => $unitsSize,
			KEY_SCORMFILES => $scoArray,
			KEY_SCORMFILES_SIZE => $scoindex
		);
		
		return $retArray;
	}
	else {
		return null;
	}
}


function putLesson($bcmsrepo, $lessonArray) {
	$bridge_host = $bcmsrepo[BRIDGE_HOST];
	$bridge_context = $bcmsrepo[BRIDGE_CONTEXT];
	$bcms_host = $bcmsrepo[BCMS_HOST];
	$bcms_repo = $bcmsrepo[BCMS_REPO];
	$bcms_user = $bcmsrepo[BCMS_USER];
	$bcms_pass = $bcmsrepo[BCMS_PASS];
	
	$cli = connectToRepo($bcmsrepo);
	
	if (isset($cli)) {
		require_once("http://".$bridge_host."/".$bridge_context."/java/Java.inc");
		
		$co = $cli->getCmsRepositoryEntityFactory()->newContentObjectForType(ECLASS_LESSON_OBJECT, "el");
		
		$owner = $cli->getRepositoryUserService()->getSystemRepositoryUser();
		$co->setOwner($owner);
		
		$canBeReadByProperty = $co->getCmsProperty("accessibility.canBeReadBy");
		$canBeReadByProperty->addSimpleTypeValue("ALL");
		$canBeUpdatedByProperty = $co->getCmsProperty("accessibility.canBeUpdatedBy");
		$canBeUpdatedByProperty->addSimpleTypeValue("NONE");
		$canBeDeletedByProperty = $co->getCmsProperty("accessibility.canBeDeletedBy");
		$canBeDeletedByProperty->addSimpleTypeValue("NONE");
		$canBeTaggedByProperty = $co->getCmsProperty("accessibility.canBeTaggedBy");
		$canBeTaggedByProperty->addSimpleTypeValue("ALL");
		
		
		$titlePR = $co->getCmsProperty(PRKEY_TITLE);
		$lessonDescPR = $co->getCmsProperty(PRKEY_DESCRIPTION);
		$keywordsPR = $co->getCmsProperty(KEY_KEYWORDS);
		$copyrightPR = $co->getCmsProperty(KEY_COPYRIGHT);
		$authorsPR = $co->getCmsProperty(KEY_AUTHORS);
		$projectPR = $co->getCmsProperty(KEY_PROJECT);
		$commentsPR = $co->getCmsProperty(KEY_COMMENTS);
		$unitsPR = $co->getCmsPropertyList(KEY_UNITS);
		$scosPR = $co->getCmsProperty(KEY_SCORMFILES);
		
		$titlePR->setSimpleTypeValue($lessonArray[KEY_TITLE]);
		$lessonDescPR->setSimpleTypeValue($lessonArray[KEY_DESCRIPTION]);
		$keywordsPR->setSimpleTypeValue($lessonArray[KEY_KEYWORDS]);
		$copyrightPR->setSimpleTypeValue($lessonArray[KEY_COPYRIGHT]);
		$authorsPR->setSimpleTypeValue($lessonArray[KEY_AUTHORS]);
		$projectPR->setSimpleTypeValue($lessonArray[KEY_PROJECT]);
		$commentsPR->setSimpleTypeValue($lessonArray[KEY_COMMENTS]);
		
		
		$co = $cli->getContentService()->saveContentObject($co, false);
		
		return true;
	}
	else {
		return false;
	}
}


function connectToRepo($bcmsrepo) {
	$bridge_host = $bcmsrepo[BRIDGE_HOST];
	$bridge_context = $bcmsrepo[BRIDGE_CONTEXT];
	$bcms_host = $bcmsrepo[BCMS_HOST];
	$bcms_repo = $bcmsrepo[BCMS_REPO];
	$bcms_user = $bcmsrepo[BCMS_USER];
	$bcms_pass = $bcmsrepo[BCMS_PASS];
	
	require_once("http://".$bridge_host."/".$bridge_context."/java/Java.inc");
	
	try {
		$cli = new Java("org.betaconceptframework.betacms.repository.client.BetaCmsRepositoryClient", $bcms_host);
		
		if (java_values($cli->getRepositoryService()->isRepositoryAvailable($bcms_repo))) {
			$pass = new Java("java.lang.String", $bcms_pass);
			$passCh = $pass->toCharArray();
			$credentials = new Java("org.betaconceptframework.betacms.repository.api.security.BetaCmsCredentials", $bcms_user, $passCh);
			$cli->login($bcms_repo, $credentials);
		}
		else {
			return null;
		}
	} catch (JavaException $e) {
		return null;
	}
	
	return $cli;
}


function checkConnectivityToRepo($bcmsrepo) {
	$bridge_host = $bcmsrepo[BRIDGE_HOST];
	$bridge_context = $bcmsrepo[BRIDGE_CONTEXT];
	
	$url = "http://".$bridge_host."/".$bridge_context."/java/Java.inc";
	$fp = @fopen($url, "r");
	
	if (isset($fp) && $fp != false) {
		$contents = "";
		while (!feof($fp)) {
  			$contents .= fread($fp, 8192);
		}
		fclose($fp);
		if (substr_count($contents, "HTTP Status 404") > 0)
			return false;

			
		@include_once($url);
		if (!class_exists("Java"))
			return false;
			
		if (connectToRepo($bcmsrepo) == null)
			return false;
			
		return true;
	}
	else
		return false;
}


// ---- Functions to call from create_course module ----

function doImportFromBetaCMSBeforeCourseCreation() {
	if (isset($_SESSION[IMPORT_FLAG]) && $_SESSION[IMPORT_FLAG] == true) {
		$_POST['intitule'] = $_SESSION[IMPORT_INTITULE];
		$_POST['description'] = $_SESSION[IMPORT_DESCRIPTION];
		$_POST['course_keywords'] = $_SESSION[IMPORT_COURSE_KEYWORDS];
		$_POST['course_addon'] = $_SESSION[IMPORT_COURSE_ADDON];
		$_SESSION[IMPORT_FLAG] = false;
		$_SESSION[IMPORT_FLAG_INITIATED] = true;
		
		// Remove them from session for proper memory/space management
		unset($_SESSION[IMPORT_INTITULE]);
		unset($_SESSION[IMPORT_DESCRIPTION]);
		unset($_SESSION[IMPORT_COURSE_KEYWORDS]);
		unset($_SESSION[IMPORT_COURSE_ADDON]);
	}
}

function doImportFromBetaCMSAfterCourseCreation($repertoire, $mysqlMainDb, $webDir) {
	$importMessages = "";
	
	if (isset($_SESSION[IMPORT_FLAG_INITIATED]) && $_SESSION[IMPORT_FLAG_INITIATED] == true) {

		if ($_SESSION[IMPORT_UNITS_SIZE] > 0) {
			// find course id
			$result = db_query("SELECT cours_id FROM cours WHERE cours.code='" . $repertoire ."'");
			$theCourse = mysql_fetch_array($result);
			$cid = $theCourse["cours_id"];
			
			foreach ($_SESSION[IMPORT_UNITS] as $key => $unit) {
				// find order
				$result = db_query("SELECT MAX(`order`) FROM course_units WHERE course_id = " . $cid);
				list($maxorder) = mysql_fetch_row($result);
				$order = $maxorder + 1;

				// add unit
				db_query("INSERT INTO course_units SET title = '" . $unit[KEY_TITLE] ."', 
						comments = '" . $unit[KEY_DESCRIPTION] ."', `order` = '" . $order ."', course_id = '" . $cid ."'");
			}
			
			// Remove them from session for proper memory/space management
			unset($_SESSION[IMPORT_UNITS]);
			unset($_SESSION[IMPORT_UNITS_SIZE]);
		}
		
		if ($_SESSION[IMPORT_SCORMFILES_SIZE] > 0) {
			foreach ($_SESSION[IMPORT_SCORMFILES] as $key => $sco) {
				$fp = fopen("../../courses/".$repertoire."/temp/".$sco[KEY_SOURCEFILENAME], "w");
				fwrite($fp, $sco[KEY_FILECONTENT]);
				fclose($fp);
				
				// Do the learningPath Import
				require_once("../learnPath/importLearningPathLib.php");
				$importMessages .= doImport($repertoire, $mysqlMainDb, $webDir, $sco[KEY_CALCULATEDSIZE], $sco[KEY_SOURCEFILENAME]);
				unlink("../../courses/".$repertoire."/temp/".$sco[KEY_SOURCEFILENAME]);
			}
			
			// Remove them from session for proper memory/space management
			unset($_SESSION[IMPORT_SCORMFILES]);
			unset($_SESSION[IMPORT_SCORMFILES_SIZE]);
		}

		// done - cleanup
		$_SESSION[IMPORT_FLAG_INITIATED] = false;
		unset($_SESSION[IMPORT_FLAG]);
		unset($_SESSION[IMPORT_FLAG_INITIATED]);
	}
	
	return $importMessages;
}
?>