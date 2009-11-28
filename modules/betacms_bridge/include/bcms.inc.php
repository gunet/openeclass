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
	@last update: 28-11-2009 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
==============================================================================
    @Description: 

    @Comments:
==============================================================================
*/

function getLessonsList($bcmsrepo) {
	$bridge_host = $bcmsrepo["bridge_host"];
	$bridge_port = $bcmsrepo["bridge_port"];
	$bridge_context = $bcmsrepo["bridge_context"];
	$bcms_host = $bcmsrepo["bcms_host"];
	$bcms_port = $bcmsrepo["bcms_port"];
	$bcms_repo = $bcmsrepo["bcms_repo"];
	$bcms_user = $bcmsrepo["bcms_user"];
	$bcms_pass = $bcmsrepo["bcms_pass"];
	
	$lessonsArray = array();
	
	$cli = connectToRepo($bcmsrepo);
	
	if ($cli != null) {
		require_once("http://".$bridge_host.":".$bridge_port."/".$bridge_context."/java/Java.inc");
		
		$criteria = java("org.betaconceptframework.betacms.repository.model.factory.CmsCriteriaFactory")->newContentObjectCriteria();
		$criteria->addContentObjectTypeEqualsCriterion("eClassLessonObject");
		$criteria->setCacheable(java("org.betaconceptframework.betacms.repository.api.model.query.CacheRegion")->FIVE_MINUTES);
		
		//Execute query
		$cmsOutcome = $cli->getContentService()->searchContentObjects($criteria);
		
		$i = 0;
		
		foreach ($cmsOutcome->getResults() as $key => $cmsRankedOutcome) {
			$co = $cmsRankedOutcome->getCmsRepositoryEntity();
			$contentObjectType = $co->getContentObjectType();
			
			$titlePR = $co->getCmsProperty("profile.title");
			$lessonDescPR = $co->getCmsProperty("lessonDescription");
			$keywordsPR = $co->getCmsProperty("keywords");
			$copyrightPR = $co->getCmsProperty("copyright");
			$authorsPR = $co->getCmsProperty("authors");
			$projectPR = $co->getCmsProperty("project");
			$commentsPR = $co->getCmsProperty("comments");
			$unitsPR = $co->getCmsPropertyList("units");
			$scosPR = $co->getCmsProperty("scormFiles");
			
			$lessonsArray[$i] = array(
				"id" => java_values($co->getId()),
				"title" => java_values($titlePR->getSimpleTypeValue()),
				"description" => java_values($lessonDescPR->getSimpleTypeValue()),
				"keywords" => java_values($keywordsPR->getSimpleTypeValue()),
				"copyright" => java_values($copyrightPR->getSimpleTypeValue()),
				"authors" => java_values($authorsPR->getSimpleTypeValue()),
				"project" => java_values($projectPR->getSimpleTypeValue()),
				"comments" => java_values($commentsPR->getSimpleTypeValue())
			);
			
//			if (!java_is_null($unitsPR) && $unitsPR->size() > 0) {
//				echo "=== Units(".$unitsPR->size().") ===" ."\n";
//				for ($i = 0; $i < java_values($unitsPR->size()); $i++) {
//					$ccprop = $unitsPR->get($i);
//					echo "title[".$i."]: " . $ccprop->getChildProperty("title")->getSimpleTypeValue() ."\n";
//					echo "description[".$i."]: " . $ccprop->getChildProperty("description")->getSimpleTypeValue() ."\n";
//				}
//			}
//			
//			if (!java_is_null(scosPR)) {
//				echo "=== SCOs ====" ."\n";
//				$bcs = $scosPR->getSimpleTypeValues();
//				echo "number of files: " . $bcs->size() ."\n";
//				foreach ($bcs as $key => $bc) {
//					echo $bc->getSourceFilename() ." ". $bc->getMimeType() ." ".  $bc->getCalculatedSize() ."\n";
//				}
//			}
					
			//echo $co->toXml();
			$i++;
		}
	}
	else {
		return null;
	}
	
	return $lessonsArray;
}


function connectToRepo($bcmsrepo) {
	$bridge_host = $bcmsrepo["bridge_host"];
	$bridge_port = $bcmsrepo["bridge_port"];
	$bridge_context = $bcmsrepo["bridge_context"];
	$bcms_host = $bcmsrepo["bcms_host"];
	$bcms_port = $bcmsrepo["bcms_port"];
	$bcms_repo = $bcmsrepo["bcms_repo"];
	$bcms_user = $bcmsrepo["bcms_user"];
	$bcms_pass = $bcmsrepo["bcms_pass"];
	
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