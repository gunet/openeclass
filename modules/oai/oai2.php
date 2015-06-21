<?php
/**
 * \file oai2.php
 * \brief
 * OAI Data Provider command processor
 *
 * OAI Data Provider is not designed for human to retrieve data.
 *
 * This is an implementation of OAI Data Provider version 2.0.
 * @see http://www.openarchives.org/OAI/2.0/openarchivesprotocol.htm
 * 
 * It needs other files:
 * - oaidp-config.php : Configuration of provider
 * - oaidp-util.php : Utility functions
 * - xml_creater.php : XML generating functions
 * - Actions:
 * 	- identify.php : About the provider
 * 	- listmetadataformats.php : List supported metadata formats
 * 	- listrecords.php : List identifiers and records
 * 	- listsets.php : List sets
 * 	- getrecord.php : Get a record
 *		- Your own implementation for providing metadata records.
 *
 * It also initiates:
 *	- ANDS_XML XML document handler $outputObj.  
 *
 * \todo <b>Remember:</b> to define your own classess for generating metadata records.
 * In common cases, you have to implement your own code to act fully and correctly.
 * For generic usage, you can try the ANDS_Response_XML defined in xml_creater.php.
 */

if(function_exists("date_default_timezone_set")) {
    date_default_timezone_set("Europe/Athens");
}
 
// Report all errors except E_NOTICE
// This is the default value set in php.ini
// If anything else, try them.
// error_reporting (E_ALL ^ E_NOTICE);

/**
 * An array for collecting erros which can be reported later. It will be checked before a new action is taken.
 */
$errors = array();

/**
 * Supported attributes associate to verbs.
 */
$attribs = array ('from', 'identifier', 'metadataPrefix', 'set', 'resumptionToken', 'until');

if (in_array($_SERVER['REQUEST_METHOD'],array('GET','POST'))) {
		$args = $_REQUEST;
} else {
	$errors[] = oai_error('badRequestMethod', $_SERVER['REQUEST_METHOD']);
}

require_once('oaidp-util.php');
// Always using htmlentities() function to encodes the HTML entities submitted by others.
// No one can be trusted.
foreach ($args as $key => $val) {
	$checking = htmlspecialchars(stripslashes($val));
	if (!is_valid_attrb($checking)) {
		$errors[] = oai_error('badArgument', $checking);
	} else {$args[$key] = $checking; }
}
if (!empty($errors)) {	oai_exit(); }

foreach($attribs as $val) {
	unset($$val);
}

require_once('oaidp-config.php');

// For generic usage or just trying:
// require_once('xml_creater.php');
// In common cases, you have to implement your own code to act fully and correctly.
require_once('eclass_oaidc.php');

// Default, there is no compression supported
$compress = FALSE;
if (isset($compression) && is_array($compression)) {
	if (in_array('gzip', $compression) && ini_get('output_buffering')) {
		$compress = TRUE;
	}
}

if (SHOW_QUERY_ERROR) {
	echo "Args:\n"; print_r($args);
}

if (isset($args['verb'])) {
	switch ($args['verb']) {

		case 'Identify':
			// we never use compression in Identify
			$compress = FALSE;
			if(count($args)>1) {
				foreach($args as $key => $val) {
					if(strcmp($key,"verb")!=0) {
						$errors[] = oai_error('badArgument', $key, $val);
					}	
				}
			}
			if (empty($errors)) include 'identify.php';
			break;

		case 'ListMetadataFormats':
			$checkList = array("ops"=>array("identifier"));
			checkArgs($args, $checkList);
			if (empty($errors)) include 'listmetadataformats.php';
			break;

		case 'ListSets':
			if(isset($args['resumptionToken']) && count($args) > 2) {
					$errors[] = oai_error('exclusiveArgument');
			}
			$checkList = array("ops"=>array("resumptionToken"));
			checkArgs($args, $checkList);
			if (empty($errors)) include 'listsets.php';
			break;

		case 'GetRecord':
			$checkList = array("required"=>array("metadataPrefix","identifier"));
			checkArgs($args, $checkList);
			if (empty($errors)) include 'getrecord.php';
			break;

		case 'ListIdentifiers':
		case 'ListRecords':
			if(isset($args['resumptionToken'])) {
				if (count($args) > 2) {
					$errors[] = oai_error('exclusiveArgument');
				}
				$checkList = array("ops"=>array("resumptionToken"));
			} else {
				$checkList = array("required"=>array("metadataPrefix"),"ops"=>array("from","until","set"));
			}
			checkArgs($args, $checkList);
			if (empty($errors)) include 'listrecords.php';
			break;

		default:
			// we never use compression with errors
			$compress = FALSE;
			$errors[] = oai_error('badVerb', $args['verb']);
	} /*switch */
} else {
	$errors[] = oai_error('noVerb');
}

if (!empty($errors)) {	oai_exit(); }

if ($compress) {
	ob_start('ob_gzhandler');
}

header(CONTENT_TYPE);

if(isset($outputObj)) {
	$outputObj->display();
} else {
	exit("There is a bug in codes");
}

	if ($compress) {
		ob_end_flush();
	}

?>
