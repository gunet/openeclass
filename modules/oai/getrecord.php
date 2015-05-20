<?php
/**
 * \file
 * \brief Response to Verb GetRecord
 *
 * Retrieve a record based its identifier.
 *
 * Local variables <B>$metadataPrefix</B> and <B>$identifier</B> need to be provided through global array variable <B>$args</B> 
 * by their indexes 'metadataPrefix' and 'identifier'.
 * The reset of information will be extracted from database based those two parameters.
 */

debug_message("\nI am debuging". __FILE__) ;

$metadataPrefix = $args['metadataPrefix'];
// myhandler is a php file which will be included to generate metadata node.
// $inc_record  = $METADATAFORMATS[$metadataPrefix]['myhandler'];

if (is_array($METADATAFORMATS[$metadataPrefix]) 
	&& isset($METADATAFORMATS[$metadataPrefix]['myhandler'])) {
	$inc_record  = $METADATAFORMATS[$metadataPrefix]['myhandler'];
} else {
	$errors[] = oai_error('cannotDisseminateFormat', 'metadataPrefix', $metadataPrefix);
}

$identifier = $args['identifier'];
$res = Database::get()->queryArray("SELECT * FROM " . $SQL['table'] . " WHERE " . $SQL['metadataPrefix'] . " LIKE ?s AND " . $SQL['identifier'] . " = ?s", $metadataPrefix, $identifier);

if ($res===false) {
	if (SHOW_QUERY_ERROR) {
		echo __FILE__.','.__LINE__."<br />";
		die();
	} else {
		$errors[] = oai_error('idDoesNotExist', '', $identifier); 
	}
} elseif (!count($res)) {
	$errors[] = oai_error('idDoesNotExist', '', $identifier); 
}

 
if (!empty($errors)) {
	oai_exit();
}

$record = $res[0];
if ($record===false) {
	if (SHOW_QUERY_ERROR) {
		echo __FILE__.','.__LINE__."<br />";
	}
	$errors[] = oai_error('idDoesNotExist', '', $identifier);	
}

$datestamp = formatDatestamp($record->{$SQL['datestamp']}); 

if (isset($record->{$SQL['deleted']}) && (intval($record->{$SQL['deleted']}) === 1) &&
        ($deletedRecord == 'transient' || $deletedRecord == 'persistent')) {
	$status_deleted = TRUE;
} else {
	$status_deleted = FALSE;
}

$outputObj = new ANDS_Response_XML($args);
$cur_record = $outputObj->create_record();
$cur_header = $outputObj->create_header($record->{$SQL['identifier']}, $datestamp, $record->{$SQL['set']}, $cur_record);
// return the metadata record itself
if (!$status_deleted) {
	include($inc_record); // where the metadata node is generated.
	create_metadata($outputObj, $cur_record, $record->{$SQL['identifier']}, $record->{$SQL['set']});
}	else {
	$cur_header->setAttribute("status","deleted");
}  
?>
