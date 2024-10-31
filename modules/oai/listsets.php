<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

/**
 * \file
 * \brief Response to Verb ListSets
 *
 * Lists what sets are available to records in the system.
 */

// Here the size of sets is small, no resumptionToken is taken care.
if (is_array($SETS)) {
	$outputObj = new ANDS_Response_XML($args);
	foreach($SETS as $set) {
		$setNode = $outputObj->add2_verbNode("set");
		foreach($set as $key => $val) {
			if($key=='setDescription') {
				$desNode = $outputObj->addChild($setNode,$key);
				$des = $outputObj->doc->createDocumentFragment();
				$des->appendXML($val);
				$desNode->appendChild($des);
			} else {
				$outputObj->addChild($setNode,$key,$val);
			}
		}
	}
}	else {
	$errors[] = oai_error('noSetHierarchy');
	oai_exit();
}

?>
