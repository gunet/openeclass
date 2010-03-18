<?php
/*========================================================================
*   Open eClass 2.3
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
	@last update: 10-01-2010 by Thanos Kyritsis
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
define ("KEY_DOCUMENTFILES", "documentFiles");
define ("KEY_DOCUMENTFILES_SIZE", "documentFiles_size");
define ("KEY_TEXTS", "texts");
define ("KEY_TEXTS_SIZE", "texts_size");

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
define ("IMPORT_DOCUMENTFILES", "bcms_documentFiles");
define ("IMPORT_DOCUMENTFILES_SIZE", "bcms_documentFiles_size");

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
		$docsPR = $co->getCmsProperty(KEY_DOCUMENTFILES);
		
		$unitArray = array();
		$unitsSize = 0;
		if (!java_is_null($unitsPR) && $unitsPR->size() > 0) {
			for ($i = 0; $i < java_values($unitsPR->size()); $i++) {
				$ccprop = $unitsPR->get($i);
				$unitTitle = java_values($ccprop->getChildProperty(KEY_TITLE)->getSimpleTypeValue());
				$unitDesc = java_values($ccprop->getChildProperty(KEY_DESCRIPTION)->getSimpleTypeValue());
				
				if (isset($unitTitle) && isset($unitDesc)) {
					$unitscosPR = $ccprop->getChildProperty(KEY_SCORMFILES);
					$unitscoArray = array();
					$unitscoindex = 0;
					if (!java_is_null($unitscosPR)) {
						$unitscos = $unitscosPR->getSimpleTypeValues();
						foreach ($unitscos as $key => $unitsco) {
							$bp = $unitsco->getCmsProperty(KEY_CONTENT);
							$bc = $bp->getSimpleTypeValue();
							$unitscoArray[$unitscoindex] = array(
								KEY_ID => java_values($unitsco->getId()),
								KEY_SOURCEFILENAME => java_values($bc->getSourceFilename()),
								KEY_MIMETYPE => java_values($bc->getMimeType()),
								KEY_CALCULATEDSIZE => java_values($bc->getCalculatedSize()),
								);
							$unitscoindex++;
						}
					}
					
					$unitdocsPR = $ccprop->getChildProperty(KEY_DOCUMENTFILES);
					$unitdocArray = array();
					$unitdocindex = 0;
					if (!java_is_null($unitdocsPR)) {
						$unitdocs = $unitdocsPR->getSimpleTypeValues();
						foreach ($unitdocs as $key => $unitdoc) {
							$bp = $unitdoc->getCmsProperty(KEY_CONTENT);
							$bc = $bp->getSimpleTypeValue();
							$unitdocArray[$unitdocindex] = array(
								KEY_ID => java_values($unitdoc->getId()),
								KEY_SOURCEFILENAME => java_values($bc->getSourceFilename()),
								KEY_MIMETYPE => java_values($bc->getMimeType()),
								KEY_CALCULATEDSIZE => java_values($bc->getCalculatedSize()),
								);
							$unitdocindex++;
						}
					}
					
					$textsPR = $ccprop->getChildProperty(KEY_TEXTS);
					$texts = $textsPR->getSimpleTypeValues();
					$unittextArray = array();
					$unittextindex = 0;
					if (!java_is_null($texts) && java_values($texts->size()) > 0) {
						for ($j = 0; $j < java_values($texts->size()); $j++) {
							$unittextArray[$j] = java_values($texts->get($j));
							$unittextindex++;
						}
					}
					
					$ar = array(KEY_TITLE => $unitTitle, KEY_DESCRIPTION => $unitDesc, 
						KEY_SCORMFILES => $unitscoArray, KEY_SCORMFILES_SIZE => $unitscoindex,
						KEY_DOCUMENTFILES => $unitdocArray, KEY_DOCUMENTFILES_SIZE => $unitdocindex,
						KEY_TEXTS => $unittextArray, KEY_TEXTS_SIZE => $unittextindex);
					$unitArray[$i] = $ar;
					$unitsSize++;
				}
			}
		}
		
		$scoArray = array();
		$scoindex = 0;
		if (!java_is_null($scosPR)) {
			$scos = $scosPR->getSimpleTypeValues();
			foreach ($scos as $key => $sco) {
				$bp = $sco->getCmsProperty(KEY_CONTENT);
				$bc = $bp->getSimpleTypeValue();
				$scoArray[$scoindex] = array(
					KEY_ID => java_values($sco->getId()),
					KEY_SOURCEFILENAME => java_values($bc->getSourceFilename()),
					KEY_MIMETYPE => java_values($bc->getMimeType()),
					KEY_CALCULATEDSIZE => java_values($bc->getCalculatedSize())
					);
				$scoindex++;
			}
		}
		
		$docArray = array();
		$docindex = 0;
		if (!java_is_null($docsPR)) {
			$docs = $docsPR->getSimpleTypeValues();
			foreach ($docs as $key => $doc) {
				$bp = $doc->getCmsProperty(KEY_CONTENT);
				$bc = $bp->getSimpleTypeValue();
				$docArray[$docindex] = array(
					KEY_ID => java_values($doc->getId()),
					KEY_SOURCEFILENAME => java_values($bc->getSourceFilename()),
					KEY_MIMETYPE => java_values($bc->getMimeType()),
					KEY_CALCULATEDSIZE => java_values($bc->getCalculatedSize()),
					);
				$docindex++;
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
			KEY_SCORMFILES_SIZE => $scoindex,
			KEY_DOCUMENTFILES => $docArray,
			KEY_DOCUMENTFILES_SIZE => $docindex
		);
		
		return $retArray;
	}
	else {
		return null;
	}
}


function getLessonSco($bcmsrepo, $objectId, $scoId) {
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
			
		$scosPR = $co->getCmsProperty(KEY_SCORMFILES);
		
		$scoindex = 0;
		if (!java_is_null($scosPR)) {
			$scos = $scosPR->getSimpleTypeValues();
			foreach ($scos as $key => $sco) {
				if (!java_is_null($sco) && $scoId == $scoindex) {
					$bp = $sco->getCmsProperty(KEY_CONTENT);
					$bc = $bp->getSimpleTypeValue();
					
					return java_values($bc->getContent());
				}
				
				$scoindex++;
			}
		}
	}
	else {
		return null;
	}
}


function getLessonDoc($bcmsrepo, $objectId, $docId) {
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
			
		$docsPR = $co->getCmsProperty(KEY_DOCUMENTFILES);
		
		$docindex = 0;
		if (!java_is_null($docsPR)) {
			$docs = $docsPR->getSimpleTypeValues();
			foreach ($docs as $key => $doc) {
				if (!java_is_null($doc) && $docId == $docindex) {
					$bp = $doc->getCmsProperty(KEY_CONTENT);
					$bc = $bp->getSimpleTypeValue();
					
					return java_values($bc->getContent());
				}
				
				$docindex++;
			}
		}
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
		
		$insertedScosArray = array();
		$insertedDocsArray = array();
		
		if ($_SESSION[IMPORT_SCORMFILES_SIZE] > 0) {
			foreach ($_SESSION[IMPORT_SCORMFILES] as $key => $sco) {
				$fp = fopen("../../courses/".$repertoire."/temp/".$sco[KEY_SOURCEFILENAME], "w");
				fwrite($fp, getLessonSco($_SESSION[BETACMSREPO], $_SESSION[IMPORT_ID], $key));
				fclose($fp);
				
				// Do the learningPath Import
				require_once("../learnPath/importLearningPathLib.php");
				list($messages, $insertedScosArray[$sco[KEY_ID]]) = doImport($repertoire, $mysqlMainDb, $webDir, $sco[KEY_CALCULATEDSIZE], $sco[KEY_SOURCEFILENAME]);
				$importMessages .= $messages;
				unlink("../../courses/".$repertoire."/temp/".$sco[KEY_SOURCEFILENAME]);
			}
			
			// Remove them from session for proper memory/space management
			unset($_SESSION[IMPORT_SCORMFILES]);
			unset($_SESSION[IMPORT_SCORMFILES_SIZE]);
		}
		
		if ($_SESSION[IMPORT_DOCUMENTFILES_SIZE] > 0) {
			foreach ($_SESSION[IMPORT_DOCUMENTFILES] as $key => $doc) {
				$fp = fopen("../../courses/".$repertoire."/temp/".$doc[KEY_SOURCEFILENAME], "w");
				fwrite($fp, getLessonDoc($_SESSION[BETACMSREPO], $_SESSION[IMPORT_ID], $key));
				fclose($fp);
				
				// register document into the database and write it in the correct place with the correct filename
				require_once("../../include/lib/forcedownload.php");
				require_once("../../include/lib/fileDisplayLib.inc.php");
				require_once("../../include/lib/fileManageLib.inc.php");
				require_once("../../include/lib/fileUploadLib.inc.php");
				$fileName = trim ($doc[KEY_SOURCEFILENAME]);
				$fileName = replace_dangerous_char($fileName);
				$fileName = add_ext_on_mime($fileName);
				$fileName = php2phps($fileName);
				$safe_fileName = safe_filename(get_file_extension($fileName));
				$file_format = get_file_extension($fileName);
				$file_date = date("Y\-m\-d G\:i\:s");
				copy("../../courses/".$repertoire."/temp/".$doc[KEY_SOURCEFILENAME], "../../courses/".$repertoire."/document/".$safe_fileName);
				$query = "INSERT INTO document SET
					path	=	'".mysql_real_escape_string("/".$safe_fileName)."',
					filename =	'".mysql_real_escape_string($fileName)."',
					visibility =	'v',
					date	= '".mysql_real_escape_string($file_date)."',
					date_modified	=	'".mysql_real_escape_string($file_date)."',
					format	=	'".mysql_real_escape_string($file_format)."'";
				db_query($query, $repertoire);
				$insertedDocsArray[$doc[KEY_ID]] = mysql_insert_id();
				mysql_select_db($mysqlMainDb);
				unlink("../../courses/".$repertoire."/temp/".$doc[KEY_SOURCEFILENAME]);
			}
			
			// Remove them from session for proper memory/space management
			unset($_SESSION[IMPORT_DOCUMENTFILES]);
			unset($_SESSION[IMPORT_DOCUMENTFILES_SIZE]);
		}

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
				$unitId = mysql_insert_id();
				list($unitResOrder) = mysql_fetch_array(db_query("SELECT MAX(`order`) FROM unit_resources WHERE unit_id=$unitId"));
				
				// add unit texts
				foreach ($unit[KEY_TEXTS] as $key => $text) {
					$unitResOrder++;
					db_query("INSERT INTO unit_resources SET unit_id=$unitId, type='text', title='', 
						comments=" . autoquote($text) . ", visibility='v', `order`=$unitResOrder, `date`=NOW(), res_id=0");
				}
				
				// add unit scorms
				foreach ($unit[KEY_SCORMFILES] as $key => $unitsco) {
					if (isset($insertedScosArray[$unitsco[KEY_ID]])) {
						$lp_id = $insertedScosArray[$unitsco[KEY_ID]];
						$unitResOrder++;
						$lp = mysql_fetch_array(db_query("SELECT * FROM lp_learnPath
							WHERE learnPath_id =" . intval($lp_id), $repertoire), MYSQL_ASSOC);
						if ($lp['visibility'] == 'HIDE') {
							 $visibility = 'i';
						} else {
							$visibility = 'v';
						}
						db_query("INSERT INTO unit_resources SET unit_id=$unitId, type='lp', title=" .
							quote($lp['name']) . ", comments=" . quote($lp['comment']) .
							", visibility='$visibility', `order`=$unitResOrder, `date`=NOW(), res_id=$lp[learnPath_id]",
							$mysqlMainDb);
					}
				}
				
				// add unit documents
				foreach ($unit[KEY_DOCUMENTFILES] as $key => $unitdoc) {
					if (isset($insertedDocsArray[$unitdoc[KEY_ID]])) {
						$file_id = $insertedDocsArray[$unitdoc[KEY_ID]];
						$unitResOrder++;
						$file = mysql_fetch_array(db_query("SELECT * FROM document
							WHERE id =" . intval($file_id), $repertoire), MYSQL_ASSOC);
						$title = (empty($file['title']))? $file['filename']: $file['title'];
						db_query("INSERT INTO unit_resources SET unit_id=$unitId, type='doc', title=" .
							 autoquote($title) . ", comments=" . autoquote($file['comment']) .
							 ", visibility='$file[visibility]', `order`=$unitResOrder, `date`=NOW(), res_id=$file[id]",
							 $mysqlMainDb);
					}
				}
			}
			
			// Remove them from session for proper memory/space management
			unset($_SESSION[IMPORT_UNITS]);
			unset($_SESSION[IMPORT_UNITS_SIZE]);
		}

		// done - cleanup
		$_SESSION[IMPORT_FLAG_INITIATED] = false;
		unset($_SESSION[IMPORT_FLAG]);
		unset($_SESSION[IMPORT_FLAG_INITIATED]);
		unset($_SESSION[IMPORT_ID]);
	}
	
	return $importMessages;
}
?>