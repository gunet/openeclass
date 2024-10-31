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
 * \brief Response to Verb ListRecords
 *
 * Lists records according to conditions. If there are too many, a resumptionToken is generated.
 * - If a request comes with a resumptionToken and is still valid, read it and send back records.
 * - Otherwise, set up a query with conditions such as: 'metadataPrefix', 'from', 'until', 'set'.
 * Only 'metadataPrefix' is compulsory.  All conditions are accessible through global array variable <B>$args</B>  by keywords.
 */

debug_message("\nI am debuging". __FILE__) ;

// Resume previous session?
if (isset($args['resumptionToken'])) {
	if (!file_exists(TOKEN_PREFIX.$args['resumptionToken'])) {
		$errors[] = oai_error('badResumptionToken', '', $args['resumptionToken']);
	} else {
		$readings = readResumToken(TOKEN_PREFIX.$args['resumptionToken']);
		if ($readings == false) {
			$errors[] = oai_error('badResumptionToken', '', $args['resumptionToken']);
		} else {
			debug_var_dump('readings',$readings);
			list($deliveredrecords, $extquery, $metadataPrefix) = $readings;
		}
	}
} else { // no, we start a new session
	$deliveredrecords = 0;
	$extquery = '';

	$metadataPrefix = $args['metadataPrefix'];

	if (isset($args['from'])) {
		$from = checkDateFormat($args['from']);
		$extquery .= fromQuery($from);
	}

	if (isset($args['until'])) {
		$until = checkDateFormat($args['until']);
		$extquery .= untilQuery($until);
	}

    if (isset($args['set'])) {
	    if (is_array($SETS)) {
		    $extquery .= setQuery($args['set']);
	    } else {
			$errors[] = oai_error('noSetHierarchy');
		}
	}
}

if (!empty($errors)) {
	oai_exit();
}

// Load the handler
if (is_array($METADATAFORMATS[$metadataPrefix])
	&& isset($METADATAFORMATS[$metadataPrefix]['myhandler'])) {
	$inc_record  = $METADATAFORMATS[$metadataPrefix]['myhandler'];
	include($inc_record);
} else {
	$errors[] = oai_error('cannotDisseminateFormat', 'metadataPrefix', $metadataPrefix);
}

if (!empty($errors)) {
	oai_exit();
}

if (empty($errors)) {
 	$r = $res = Database::get()->queryArray("SELECT * FROM " . $SQL['table'] . " WHERE " . $SQL['metadataPrefix'] . " LIKE ?s" . $extquery, $metadataPrefix);
 	if ($r===false) {
		if (SHOW_QUERY_ERROR) {
			echo __FILE__.','.__LINE__."<br />";
			exit();
		} else {
			$errors[] = oai_error('noRecordsMatch');
		}
	} else {
		if ($r===false) {
			exit("FetchMode is not supported");
		}
		$num_rows = count($res);
		if ($num_rows==0) {
			if (SHOW_QUERY_ERROR) {
				echo "Cannot find records\n";
			}
			$errors[] = oai_error('noRecordsMatch');
		}
	}
}

if (!empty($errors)) {
	oai_exit();
}

// Will we need a new ResumptionToken?
if($args['verb']=='ListRecords') {
	$maxItems = MAXRECORDS;
} elseif($args['verb']=='ListIdentifiers') {
	$maxItems = MAXIDS;
} else {
	exit("Check ".__FILE__." ".__LINE__.", there is something wrong.");
}
$maxrec = min($num_rows - $deliveredrecords, $maxItems);

if ($num_rows - $deliveredrecords > $maxItems) {
	$cursor = (int)$deliveredrecords + $maxItems;
	$restoken = createResumToken($cursor, $extquery, $metadataPrefix);
	$expirationdatetime = gmstrftime('%Y-%m-%dT%TZ', time()+TOKEN_VALID);
}
// Last delivery, return empty ResumptionToken
elseif (isset($args['resumptionToken'])) {
	$restoken = $args['resumptionToken']; // just used as an indicator
	unset($expirationdatetime);
}

// Record counter
$countrec  = 0;

if (isset($args['resumptionToken'])) {
	debug_message("Try to resume because a resumptionToken supplied.") ;
	$countrec += $deliveredrecords;
	$maxrec += $deliveredrecords;
}

// Publish a batch to $maxrec number of records
$outputObj = new ANDS_Response_XML($args);
while ($countrec++ < $maxrec) {
	$record = $res[$countrec-1];
	if ($record===false) {
		if (SHOW_QUERY_ERROR) {
			echo __FILE__.",". __LINE__."<br />";
			exit();
		}
	}

	$identifier = $oaiprefix.$record->{$SQL['identifier']};
	$datestamp = formatDatestamp($record->{$SQL['datestamp']});
	$setspec = $record->{$SQL['set']};

	// debug_var_dump('record', $record);
	if (isset($record->{$SQL['deleted']}) && (intval($record->{$SQL['deleted']}) === 1 ) &&
		($deletedRecord == 'transient' || $deletedRecord == 'persistent')) {
		$status_deleted = TRUE;
	} else {
		$status_deleted = FALSE;
	}

  //debug_var_dump('status_deleted', $status_deleted);
	if($args['verb']=='ListRecords') {
		$cur_record = $outputObj->create_record();
		$cur_header = $outputObj->create_header($identifier, $datestamp,$setspec,$cur_record);
	// return the metadata record itself
		if (!$status_deleted) {
			debug_var_dump('inc_record',$inc_record);
			create_metadata($outputObj, $cur_record, $identifier, $setspec);
		}
	} else { // for ListIdentifiers, only identifiers will be returned.
		$cur_header = $outputObj->create_header($identifier, $datestamp,$setspec);
	}
	if ($status_deleted) {
		$cur_header->setAttribute("status","deleted");
	}
}

// ResumptionToken
if (isset($restoken)) {
	if(isset($expirationdatetime)) {
		$outputObj->create_resumpToken($restoken,$expirationdatetime,$num_rows,$cursor);
	} else {
		$outputObj->create_resumpToken('',null,$num_rows,$deliveredrecords);
	}
}

// end ListRecords
if (SHOW_QUERY_ERROR) {echo "Debug listrecord.php reached to the end.\n\n";}
?>
