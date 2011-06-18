<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */ 

function metaCreateForm($metadata, $oldFilename, $real_filename) {
	// globals
	global $code_cours, $group_hidden_input;
	
	// lang globals
	global $langAddMetadata, $langWorkFile, $langTitle, $langTitleHelp,
		$langDescription, $langDescriptionHelp, $langAuthor, $langAuthorHelp,
		$langLanguage, $langGreek, $langEnglish, $langFrench, $langGerman,
		$langItalian, $langSpanish, $langLanguageHelp, $langLearningResourceType,
		$langLearningResourceTypeHelp, $langKeywords, $langKeywordsHelp, $langTopic,
		$langTopicHelp, $langSubTopic, $langSubTopicHelp, $langLevel, $langLevelHelp,
		$langTypicalAgeRange, $langTypicalAgeRangeHelp, $langComment, $langCommentHelp,
		$langCopyright, $langCopyrightHelp, $langIntentedEndUserRole, $langIntentedEndUserRoleHelp,
		$langOkComment, $langNotRequired;
	
	// variable definitions
	$metaTitle = "";
	$metaLanguage = "";
	$metaDescription = "";
	$metaAuthors = "";
	$metaKeywords = "";
	$metaRights = "";
	$metaLearningResourceTypes = "";
	$metaIntendedEndUserRoles = "";
	$metaLevels = "";
	$metaTypicalAgeRanges = "";
	$metaNotes = "";
	$metaTopic = "";
	$metaSubTopic = "";
	
	if (file_exists($real_filename.".xml")) {
		$sxe = simplexml_load_file($real_filename.".xml");
		
		if ($sxe) {
			$metaTitle = $sxe->general->title->string;
			$metaLanguage = $sxe->general->language;
			$metaDescription = $sxe->general->description->string;
			$metaAuthors = $sxe->lifeCycle->contribute->entity;
			$metaKeywords = $sxe->general->keyword;
			$metaRights = $sxe->rights->description->string;
			$metaLearningResourceTypes = $sxe->educational->learningResourceType;
			$metaIntendedEndUserRoles = $sxe->educational->intendedEndUserRole;
			$metaLevels = $sxe->educational->context;
			$metaTypicalAgeRanges = $sxe->educational->typicalAgeRange;
			$metaNotes = $sxe->educational->description->string;
			$metaTopic = $sxe->classification->taxonPath->source->string;
			$metaSubTopic = $sxe->classification->taxonPath->taxon->entry->string;
		}
	}
	
	$checkMap['meta_learningresourcetype'] = metaBuildCheckMap($metaLearningResourceTypes, "meta_learningresourcetype");
	$checkMap['meta_intendedenduserrole']  = metaBuildCheckMap($metaIntendedEndUserRoles, "meta_intendedenduserrole");
	$checkMap['meta_level']                = metaBuildCheckMap($metaLevels, "meta_level"); 
	
	$output = "";
	
	$output .= "
	<form method='post' action='document.php?course=$code_cours'>
	<fieldset>
	  <input type='hidden' name='metadataPath' value='". q($metadata) ."' />
	  <input type='hidden' name='meta_filename' value='$oldFilename' />
	  $group_hidden_input
	  <legend>$langAddMetadata</legend>
	  <table class='tbl' width='100%'>
	  <tr>
	    <th>$langWorkFile:</th>
	    <td>$oldFilename</td>
	  </tr>";
	  
	  $output .= metaTextAreaRow($langTitle, "meta_title", $metaTitle, $langTitleHelp)
	          .  metaTextAreaRow($langDescription, "meta_description", $metaDescription, $langDescriptionHelp, 4);
	  
	  $output .= "<tr>
	    <th rowspan='2'>$langAuthor:</th>
	    <td><textarea cols='68' name='meta_author'>";
	  if (!empty($metaAuthors)) {
		  $i = 0;
		  foreach ($metaAuthors as $metaAuthor) {
		  	$i++;
		  	$output .= $metaAuthor;
		  	if ($i < count($metaAuthors))
		  		$output .= ", ";
		  }
	  }
	  $output .= "</textarea></td>
	  </tr><tr><td>$langAuthorHelp</td></tr>
	  <tr>
	    <th rowspan='2'>$langLanguage:</th>
	    <td>". selection(array('el' => $langGreek,
				'en' => $langEnglish,
				'fr' => $langFrench,
				'de' => $langGerman,
				'it' => $langItalian,
				'es' => $langSpanish), 'meta_language', $metaLanguage) ."</td>
	  </tr><tr><td>$langLanguageHelp</td></tr>
	  <tr>
	    <th rowspan='2'>$langLearningResourceType:</th>
	    <td>";
	  
	  $resourceTypes = array("exercise", "simulation", "questionnaire", "diagram", "figure", 
	    "graph", "index", "slide", "table", "narrative text", "exam", "experiment", 
	    "problem statement", "self assessment", "lecture");
	  
	  foreach ($resourceTypes as $type)
	    $output .= metaCheckBoxInput($checkMap, "meta_learningresourcetype", $type) ."<br/>\n";
	    
	  $output .= "</td>
	  </tr><tr><td>$langLearningResourceTypeHelp</td></tr>
	  <tr>
	    <th rowspan='2'>$langKeywords:</th>
	    <td><textarea cols='68' name='meta_keywords'>";
	  if (!empty($metaKeywords)) {
		  $i = 0;
		  foreach ($metaKeywords as $metaKeyword) {
		  	$i++;
		  	$output .= $metaKeyword->string;
		  	if ($i < count($metaKeywords))
		  		$output .= ", ";
		  }
	  }
	  $output .= "</textarea></td>
	  </tr><tr><td>$langKeywordsHelp</td></tr>";
	  
	  $output .= metaInputTextRow($langTopic, "meta_topic", $metaTopic, $langTopicHelp)
	          .  metaInputTextRow($langSubTopic, "meta_subtopic", $metaSubTopic, $langSubTopicHelp);
	  
	  $output .= "<tr>
	    <th rowspan='2'>$langLevel:</th>
	    <td>";
	  
	  $levels = array("school", "higher education", "training", "other");
	  
	  foreach ($levels as $level)
	  	$output .= metaCheckBoxInput($checkMap, "meta_level", $level) ."<br/>\n";
	  
	  $output .= "</td>
	  </tr><tr><td>$langLevelHelp</td></tr>
	  <tr>
	    <th rowspan='2'>$langTypicalAgeRange:</th>
	    <td><input type='text' size='60' name='meta_typicalagerange' value='";
	  if (!empty($metaTypicalAgeRanges)) {
		  $i = 0;
		  foreach ($metaTypicalAgeRanges as $metaTypicalAgeRange) {
		  	$i++;
		  	$output .= htmlspecialchars($metaTypicalAgeRange->string, ENT_QUOTES, 'utf-8');
		  	if ($i < count($metaTypicalAgeRanges))
		  		$output .= ", ";
		  }
	  }
	  $output .= "' /></td>
	  </tr><tr><td>$langTypicalAgeRangeHelp</td></tr>";
	  
	  $output .= metaTextAreaRow($langComment, "meta_notes", $metaNotes, $langCommentHelp, 4)
	          .  metaTextAreaRow($langCopyright, "meta_rights", $metaRights, $langCopyrightHelp);
	  
	  $output .= "<tr>
	    <th rowspan='2'>$langIntentedEndUserRole:</th>
	    <td>";
	  
	  $userRoles = array("teacher", "author", "learner", "manager");
	  
	  foreach ($userRoles as $role)
	  	$output .= metaCheckBoxInput($checkMap, "meta_intendedenduserrole", $role) ."<br/>\n";
	  
	  $output .= "</td>
	  </tr><tr><td>$langIntentedEndUserRoleHelp</td></tr>
	  <tr>
	    <th>&nbsp;</th>
	    <td class='right'><input type='submit' value='$langOkComment' /></td>
	  </tr>
	  <tr>
	    <th>&nbsp;</th>
	    <td class='right'>$langNotRequired</td>
	  </tr>
	  </table>
	</fieldset>
	</form>";
	  
	return $output;
}


/*
 * Build Array Map for the Metadata Form to decide which checkboxes should be 
 * checked when editing a XML file
 */
function metaBuildCheckMap($values, $group){
	$retAr = array();
	
	if (!empty($values))
		foreach ($values as $value)
			$retAr["$value->value"] = true;
			
	return $retAr;
}


/*
 * Create input checkboxes for the Metadata Form
 */
function metaCheckBoxInput($checkMap, $group, $element) {
	$langElement = "lang".ucfirst(str_replace(" ", "", $element));
	global $$langElement;
	
	$check = (isset($checkMap["$group"]["$element"])) ? " checked='1' " : '';
	
	return "<input type='checkbox' name='".$group."[]' value='$element' $check />".$$langElement;
}


/*
 * 
 */
function makeFormRow($title, $cell, $help) {
	return "<tr>
	    <th rowspan='2'>$title:</th>
	    <td>$cell</td>
	  </tr><tr><td>$help</td></tr>";
}


/*
 * Create input textarea table row for the Metadata Form
 */
function metaTextAreaRow($title, $name, $value, $help, $rows = 2) {
	return makeFormRow($title, "<textarea cols='68' rows='$rows' name='$name'>$value</textarea>", $help);
}


/*
 * Create input text table row for the Metadata Form
 */
function metaInputTextRow($title, $name, $value, $help) {
	return makeFormRow($title, "<input type='text' size='60' name='$name' value='".htmlspecialchars($value, ENT_QUOTES, 'utf-8')."' />", $help);
}


function metaCreateDomDocument($xmlFilename) {
	$dom = new DomDocument('1.0', 'utf-8');
	$lom = $dom->appendChild($dom->createElementNS('http://ltsc.ieee.org/xsd/LOM', 'lom'));
	$lom->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
	$lom->setAttribute('xsi:schemaLocation', 'http://ltsc.ieee.org/xsd/LOM ity.xsd');
	// end of lom
	
	$general = $lom->appendChild($dom->createElement('general'));
	
	$title = $general->appendChild($dom->createElement('title'));
	$langstring = $title->appendChild($dom->createElement('string', htmlspecialchars($_POST['meta_title'], ENT_QUOTES, 'utf-8')));
	$langstring->setAttribute('language', $_POST['meta_language']);
	
	$general->appendChild($dom->createElement('language', $_POST['meta_language']));
	
	$description = $general->appendChild($dom->createElement('description'));
	$langstring = $description->appendChild($dom->createElement('string', htmlspecialchars($_POST['meta_description'], ENT_QUOTES, 'utf-8')));
	$langstring->setAttribute('language', $_POST['meta_language']);
	
	metaLangStringLoop($dom, $general, $_POST['meta_language'], 'keyword', $_POST['meta_keywords']);
	// end of general
	
	$lifecycle = $lom->appendChild($dom->createElement('lifeCycle'));
	$contribute = $lifecycle->appendChild($dom->createElement('contribute'));
	
	metaSimpleLoop($dom, $contribute, 'entity', $_POST['meta_author']);
	// end of lifeCycle
	
	$rights = $lom->appendChild($dom->createElement('rights'));
	
	$copyrightAndOtherRestrictionse = $rights->appendChild($dom->createElement('copyrightAndOtherRestrictions'));
	$source = $copyrightAndOtherRestrictionse->appendChild($dom->createElement('source', 'LOMv1.0'));
	$value = $copyrightAndOtherRestrictionse->appendChild($dom->createElement('value', 'yes'));
	
	$description = $rights->appendChild($dom->createElement('description'));
	$langstring = $description->appendChild($dom->createElement('string', htmlspecialchars($_POST['meta_rights'], ENT_QUOTES, 'utf-8')));
	$langstring->setAttribute('language', $_POST['meta_language']);
	// end of rights
	
	$educational = $lom->appendChild($dom->createElement('educational'));

	if (isset($_POST['meta_learningresourcetype']))
		metaSourceValueArrayLoop($dom, $educational, 'learningResourceType', $_POST['meta_learningresourcetype']);
	if (isset($_POST['meta_intendedenduserrole']))
		metaSourceValueArrayLoop($dom, $educational, 'intendedEndUserRole', $_POST['meta_intendedenduserrole']);
	if (isset($_POST['meta_level']))
		metaSourceValueArrayLoop($dom, $educational, 'context', $_POST['meta_level']);
	metaLangStringLoop($dom, $educational, $_POST['meta_language'], 'typicalAgeRange', $_POST['meta_typicalagerange']);
	
	$description = $educational->appendChild($dom->createElement('description'));
	$langstring = $description->appendChild($dom->createElement('string', htmlspecialchars($_POST['meta_notes'], ENT_QUOTES, 'utf-8')));
	$langstring->setAttribute('language', $_POST['meta_language']);
	// end of educational
	
	$classification = $lom->appendChild($dom->createElement('classification'));
	
	$purpose = $classification->appendChild($dom->createElement('purpose'));
	$source = $purpose->appendChild($dom->createElement('source', 'LOMv1.0'));
	$value = $purpose->appendChild($dom->createElement('value', 'discipline'));
	
	$taxonPath = $classification->appendChild($dom->createElement('taxonPath'));
	$source = $taxonPath->appendChild($dom->createElement('source'));
	$langstring = $source->appendChild($dom->createElement('string', htmlspecialchars($_POST['meta_topic'], ENT_QUOTES, 'utf-8')));
	$langstring->setAttribute('language', $_POST['meta_language']);
	
	$taxon = $taxonPath->appendChild($dom->createElement('taxon'));
	$entry = $taxon->appendChild($dom->createElement('entry'));
	$langstring = $entry->appendChild($dom->createElement('string', htmlspecialchars($_POST['meta_subtopic'], ENT_QUOTES, 'utf-8')));
	$langstring->setAttribute('language', $_POST['meta_language']);
	// end of classification
	
	$dom->formatOutput = true;
	$dom->save($xmlFilename);
}

function metaLangStringLoop($dom, $parent, $lang, $element, $inputValue) {
	if (strlen(trim($inputValue))) {
		$values = explode(',', htmlspecialchars($inputValue, ENT_QUOTES, 'utf-8'));
		foreach ($values as $v) {
			if (strlen(trim($v))) {
				$child = $parent->appendChild($dom->createElement($element));
				$langstring = $child->appendChild($dom->createElement('string', trim($v)));
				$langstring->setAttribute('language', $lang);
			}
		}
	}
}

function metaSimpleLoop($dom, $parent, $element, $inputValue) {
	if (strlen(trim($inputValue))) {	
		$values = explode(',', htmlspecialchars($inputValue, ENT_QUOTES, 'utf-8'));
		foreach ($values as $v)
			if (strlen(trim($v)))
				$child = $parent->appendChild($dom->createElement($element, trim($v)));
	}
}

function metaSourceValueLoop($dom, $parent, $element, $inputValue) {
	$child = $parent->appendChild($dom->createElement($element));
	$source = $child->appendChild($dom->createElement('source', 'LOMv1.0'));
	
	if (strlen(trim($inputValue))) {
		$values = explode(',', htmlspecialchars($inputValue, ENT_QUOTES, 'utf-8'));
		$i = 0;
		foreach ($values as $v) {
			$i++;
			if (strlen(trim($v))) {
				$value = $child->appendChild($dom->createElement('value', trim($v)));
				if ($i < count($values) && strlen(trim($values[$i]))) {
					$child = $parent->appendChild($dom->createElement($element));
					$source = $child->appendChild($dom->createElement('source', 'LOMv1.0'));
				}
			}
		}
	}
}

function metaSourceValueArrayLoop($dom, $parent, $element, $inputValue) {
	$child = $parent->appendChild($dom->createElement($element));
	$source = $child->appendChild($dom->createElement('source', 'LOMv1.0'));
	
	$i = 0;
	foreach ($inputValue as $v) {
		$i++;
		$value = $child->appendChild($dom->createElement('value', $v));
		if ($i < count($inputValue) ) {
			$child = $parent->appendChild($dom->createElement($element));
			$source = $child->appendChild($dom->createElement('source', 'LOMv1.0'));
		}
	}
}

function hasMetaData($filename, $basedir, $group_sql) {
	$xml = $filename.".xml";
	$real_filename = $basedir . str_replace('/..', '', q($xml));
	$result = db_query("SELECT * FROM document WHERE $group_sql AND path = " . autoquote($xml));
		
	if (file_exists($real_filename) && mysql_num_rows($result) > 0) {
		
		$row = mysql_fetch_array($result);
		if ($row['format'] == ".meta")
			return true;
	} else 
		return false;
	
	return false;
}

?>