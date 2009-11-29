<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2009  Greek Universities Network - GUnet
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
	@last update: 29-11-2009 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
==============================================================================
    @Description: 

    @Comments:
==============================================================================
*/

define ("BETACMSREPO", "betacmsrepo");

define ("BRIDGE_HOST", "bridge_host");
define ("BRIDGE_PORT", "bridge_port");
define ("BRIDGE_CONTEXT", "bridge_context");
define ("BCMS_HOST", "bcms_host");
define ("BCMS_PORT", "bcms_port");
define ("BCMS_REPO", "bcms_repo");
define ("BCMS_USER", "bcms_user");
define ("BCMS_PASS", "bcms_pass");

define ("KEY_ID", "id");
define ("KEY_TITLE", "title");
define ("KEY_DESCRIPTION", "description");
define ("KEY_KEYWORDS", "keywords");
define ("KEY_COPYRIGHT", "copyright");
define ("KEY_AUTHORS", "authors");
define ("KEY_PROJECT", "project");
define ("KEY_COMMENTS", "comments");
define ("KEY_UNITS", "units");
define ("KEY_SCORMFILES", "scormFiles");
define ("KEY_SOURCEFILENAME", "sourcefilename");
define ("KEY_MIMETYPE", "mimetype");
define ("KEY_CALCULATEDSIZE", "calculatedsize");

define ("PRKEY_TITLE", "profile.title");
define ("PRKEY_DESCRIPTION", "lessonDescription");

define ("IMPORT_FLAG", "bcms_flag");
define ("IMPORT_ID", "bcms_id");
define ("IMPORT_INTITULE", "bcms_intitule");
define ("IMPORT_DESCRIPTION", "bcms_description");
define ("IMPORT_COURSE_KEYWORDS", "bcms_course_keywords");
define ("IMPORT_COURSE_ADDON", "bcms_course_addon");


function getLessonsList($bcmsrepo) {
	$bridge_host = $bcmsrepo[BRIDGE_HOST];
	$bridge_port = $bcmsrepo[BRIDGE_PORT];
	$bridge_context = $bcmsrepo[BRIDGE_CONTEXT];
	$bcms_host = $bcmsrepo[BCMS_HOST];
	$bcms_port = $bcmsrepo[BCMS_PORT];
	$bcms_repo = $bcmsrepo[BCMS_REPO];
	$bcms_user = $bcmsrepo[BCMS_USER];
	$bcms_pass = $bcmsrepo[BCMS_PASS];
	
	$lessonsArray = array();
	
	$cli = connectToRepo($bcmsrepo);
	
	if (isset($cli)) {
		require_once("http://".$bridge_host.":".$bridge_port."/".$bridge_context."/java/Java.inc");
		
		$criteria = java("org.betaconceptframework.betacms.repository.model.factory.CmsCriteriaFactory")->newContentObjectCriteria();
		$criteria->addContentObjectTypeEqualsCriterion("eClassLessonObject");
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
	$bridge_port = $bcmsrepo[BRIDGE_PORT];
	$bridge_context = $bcmsrepo[BRIDGE_CONTEXT];
	$bcms_host = $bcmsrepo[BCMS_HOST];
	$bcms_port = $bcmsrepo[BCMS_PORT];
	$bcms_repo = $bcmsrepo[BCMS_REPO];
	$bcms_user = $bcmsrepo[BCMS_USER];
	$bcms_pass = $bcmsrepo[BCMS_PASS];
	
	$cli = connectToRepo($bcmsrepo);
	
	if (isset($cli)) {
		require_once("http://".$bridge_host.":".$bridge_port."/".$bridge_context."/java/Java.inc");
		
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
		if (!java_is_null($unitsPR) && $unitsPR->size() > 0) {
			for ($i = 0; $i < java_values($unitsPR->size()); $i++) {
				$ccprop = $unitsPR->get($i);
				$unitTitle = java_values($ccprop->getChildProperty(KEY_TITLE)->getSimpleTypeValue());
				$unitDesc = java_values($ccprop->getChildProperty(KEY_DESCRIPTION)->getSimpleTypeValue());
				if (isset($unitTitle) && isset($unitDesc)) {
					$ar = array(KEY_TITLE => $unitTitle, KEY_DESCRIPTION => $unitDesc);
					$unitArray[$i] = $ar;
				}
			}
		}
		
		$scoArray = array();
		$i = 0;
		if (!java_is_null(scosPR)) {
			$bcs = $scosPR->getSimpleTypeValues();
			foreach ($bcs as $key => $bc) {
				$scoArray[$i] = array(
					KEY_SOURCEFILENAME => java_values($bc->getSourceFilename()),
					KEY_MIMETYPE => java_values($bc->getMimeType()),
					KEY_CALCULATEDSIZE => java_values($bc->getCalculatedSize())
					);
				$i++;
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
			KEY_SCORMFILES => $scoArray
		);
		
		return $retArray;
	}
	else {
		return null;
	}
}


function connectToRepo($bcmsrepo) {
	$bridge_host = $bcmsrepo[BRIDGE_HOST];
	$bridge_port = $bcmsrepo[BRIDGE_PORT];
	$bridge_context = $bcmsrepo[BRIDGE_CONTEXT];
	$bcms_host = $bcmsrepo[BCMS_HOST];
	$bcms_port = $bcmsrepo[BCMS_PORT];
	$bcms_repo = $bcmsrepo[BCMS_REPO];
	$bcms_user = $bcmsrepo[BCMS_USER];
	$bcms_pass = $bcmsrepo[BCMS_PASS];
	
	require_once("http://".$bridge_host.":".$bridge_port."/".$bridge_context."/java/Java.inc");

	$cli = new Java("org.betaconceptframework.betacms.repository.client.BetaCmsRepositoryClient", $bcms_host.":".$bcms_port);
	
	// $availableRepositories = java_values($cli->getRepositoryService()->getAvailableCmsRepositories());
	// foreach ($availableRepositories as $key => $cmsRepository) {
	//	echo "repo".$key.": " .$cmsRepository->getId() ."\n";
	// }
	        
	$betacmsRepository = $cli->getRepositoryService()->getCmsRepository($bcms_repo);
	// echo "We retrieved information about BetaCMS repository with id: " .$betacmsRepository->getId() ."\n";
	
	if (java_values($cli->getRepositoryService()->isRepositoryAvailable($bcms_repo))) {
		// echo "demorepo is available\n";
		$pass = new Java("java.lang.String", $bcms_pass);
		$passCh = $pass->toCharArray();
		$credentials = new Java("org.betaconceptframework.betacms.repository.api.security.BetaCmsCredentials", $bcms_user, $passCh);
		$cli->login($bcms_repo, $credentials);
	}
	else {
		return null;
	}
	
	return $cli;
}
?>