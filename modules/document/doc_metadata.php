<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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
    global $course_code, $group_hidden_input;

    // lang globals
    global $langAddMetadata, $langWorkFile, $langTitle, $langTitleHelp,
    $langInfoAbout, $langDescriptionHelp, $langAuthor, $langAuthorHelp,
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

    if (file_exists($real_filename . ".xml")) {
        $sxe = simplexml_load_file($real_filename . ".xml");

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
    $checkMap['meta_intendedenduserrole'] = metaBuildCheckMap($metaIntendedEndUserRoles, "meta_intendedenduserrole");
    $checkMap['meta_level'] = metaBuildCheckMap($metaLevels, "meta_level");

    $output = "
	<form method='post' action='index.php?course=$course_code'>
	<fieldset>
	  <input type='hidden' name='metadataPath' value='" . q($metadata) . "' />
	  <input type='hidden' name='meta_filename' value='$oldFilename' />
	  <input type='hidden' name='meta_mimetype' value='" . get_mime_type($oldFilename) . "' />
	  $group_hidden_input
	  <legend>$langAddMetadata</legend>
	  <table class='table-default'>
	  <tr>
	    <th>$langWorkFile:</th>
	    <td>$oldFilename</td>
	  </tr>";

    $output .= metaTextAreaRow($langTitle, "meta_title", $metaTitle, $langTitleHelp)
            . metaTextAreaRow($langInfoAbout, "meta_description", $metaDescription, $langDescriptionHelp, 4)
            . metaCommaTextAreaRow($langAuthor, "meta_author", $metaAuthors, $langAuthorHelp);

    $cellLang = selection(array('el' => $langGreek,
        'en' => $langEnglish,
        'fr' => $langFrench,
        'de' => $langGerman,
        'it' => $langItalian,
        'es' => $langSpanish), 'meta_language', $metaLanguage);
    $output .= metaFormRow($langLanguage, $cellLang, $langLanguageHelp);

    $resourceTypes = array("narrative text", "simulation", "photo", "experiment", "image", "microexperiment", "figure",
        "map", "diagram", "interactivemap", "graph", "exploration", "table", "interactivegame", "sound", "conceptualmap",
        "music", "index", "narration", "problem statement", "video", "self assessment", "animation", "questionnaire",
        "3danimation", "quiz", "slide", "exam", "presentation", "exercise", "lecture", "learningscenario", "textbook",);
    $output .= metaCheckBoxRow($langLearningResourceType, "meta_learningresourcetype", $resourceTypes, $checkMap, $langLearningResourceTypeHelp, true)
            . metaCommaTextAreaRow($langKeywords, "meta_keywords", $metaKeywords, $langKeywordsHelp, 2, "string")
            . metaInputTextRow($langTopic, "meta_topic", $metaTopic, $langTopicHelp)
            . metaInputTextRow($langSubTopic, "meta_subtopic", $metaSubTopic, $langSubTopicHelp);

    $levels = array("nursery", "primary", "secondary", "highschool", "technical", "training", "higher education", "other");
    $output .= metaCheckBoxRow($langLevel, "meta_level", $levels, $checkMap, $langLevelHelp)
            . metaCommaInputTextRow($langTypicalAgeRange, "meta_typicalagerange", $metaTypicalAgeRanges, $langTypicalAgeRangeHelp, "string")
            . metaTextAreaRow($langComment, "meta_notes", $metaNotes, $langCommentHelp, 4)
            . metaTextAreaRow($langCopyright, "meta_rights", $metaRights, $langCopyrightHelp);

    $userRoles = array("teacher", "learner", "author", "manager", "other");
    $output .= metaCheckBoxRow($langIntentedEndUserRole, "meta_intendedenduserrole", $userRoles, $checkMap, $langIntentedEndUserRoleHelp);

    $output .= "<tr>
	    <th>&nbsp;</th>
	    <td class='right'><input class='btn btn-primary' type='submit' value='$langOkComment' /></td>
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

function metaBuildCheckMap($values, $group) {
    $retAr = array();

    if (!empty($values))
        foreach ($values as $value)
            $retAr["$value->value"] = true;

    return $retAr;
}

/*
 * Create table row for the Metadata Form
 */

function metaFormRow($title, $cell, $help) {
    return "<tr>
	    <th rowspan='2'>$title:</th>
	    <td>$cell</td>
	  </tr><tr><td>$help</td></tr>";
}

/*
 * Create input checkboxes row for the Metadata Form
 */

function metaCheckBoxRow($title, $name, $values, $checkMap, $help, $twocols = false) {
    $cell = "<table class='table-default'>";
    $i = 0;

    foreach ($values as $value) {
        $i++;
        $langElement = "langMeta" . ucfirst(str_replace(" ", "", $value));
        global $$langElement;

        $check = (isset($checkMap["$name"]["$value"])) ? " checked='1' " : '';
        $start = ($twocols && $i % 2 == 0) ? "<td>" : "<tr><td>";
        $end = ($twocols && $i % 2 != 0 && $i < count($values)) ? "</td>\n" : "</td></tr>\n";

        $cell .= "$start<input type='checkbox' name='" . $name . "[]' value='$value' $check />" . $$langElement . $end;
    }

    $cell .= "</table>";

    return metaFormRow($title, $cell, $help);
}

/*
 * Create input textarea table row for the Metadata Form
 */

function metaTextAreaRow($title, $name, $value, $help, $rows = 2) {
    return metaFormRow($title, "<textarea cols='68' rows='$rows' name='$name'>$value</textarea>", $help);
}

/*
 * Create comma separated textarea table row for the Metadata Form
 */

function metaCommaTextAreaRow($title, $name, $values, $help, $rows = 2, $element = null) {
    $cell = "<textarea cols='68' rows='$rows' name='$name'>";

    if (!empty($values)) {
        $i = 0;
        foreach ($values as $value) {
            $i++;
            $v = (isset($element)) ? $value->{$element} : $value;
            $cell .= $v;
            if ($i < count($values))
                $cell .= ", ";
        }
    }

    $cell .= "</textarea>";

    return metaFormRow($title, $cell, $help);
}

/*
 * Create input text table row for the Metadata Form
 */

function metaInputTextRow($title, $name, $value, $help) {
    return metaFormRow($title, "<input type='text' size='60' name='$name' value='" . htmlspecialchars($value, ENT_QUOTES, 'utf-8') . "' />", $help);
}

/*
 * Create comma separated input text table row for the Metadata Form
 */

function metaCommaInputTextRow($title, $name, $values, $help, $element = null) {
    $cell = "<input type='text' size='60' name='$name' value='";

    if (!empty($values)) {
        $i = 0;
        foreach ($values as $value) {
            $i++;
            $v = (isset($element)) ? $value->{$element} : $value;
            $cell .= htmlspecialchars($v, ENT_QUOTES, 'utf-8');
            if ($i < count($values))
                $cell .= ", ";
        }
    }

    $cell .= "' />";

    return metaFormRow($title, $cell, $help);
}

function metaCreateDomDocument($xmlFilename) {
    $dom = new DomDocument('1.0', 'utf-8');
    $lom = $dom->appendChild($dom->createElementNS('http://ltsc.ieee.org/xsd/LOM', 'lom'));
    $lom->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $lom->setAttribute('xsi:schemaLocation', 'http://ltsc.ieee.org/xsd/LOM ity.xsd');
    // end of lom

    $general = $lom->appendChild($dom->createElement('general'));

    $identifier = $general->appendChild($dom->createElement('identifier'));
    $catalog = $identifier->appendChild($dom->createElement('catalog', 'URL'));
    $entry = $identifier->appendChild($dom->createElement('entry', $_POST['meta_filename']));

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

    $metaMetadata = $lom->appendChild($dom->createElement('metaMetadata'));

    $contribute = $metaMetadata->appendChild($dom->createElement('contribute'));
    $entity = $contribute->appendChild($dom->createElement('entity', $_SESSION['givenname'] . " " . $_SESSION['surname']));
    $date = $contribute->appendChild($dom->createElement('date'));
    $dateTime = $date->appendChild($dom->createElement('dateTime', date('Y-m-d')));
    // end of metametadata

    $technical = $lom->appendChild($dom->createElement('technical'));
    $format = $technical->appendChild($dom->createElement('format', $_POST['meta_mimetype']));
    // end of technical

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

    $rights = $lom->appendChild($dom->createElement('rights'));

    $copyrightAndOtherRestrictionse = $rights->appendChild($dom->createElement('copyrightAndOtherRestrictions'));
    $source = $copyrightAndOtherRestrictionse->appendChild($dom->createElement('source', 'LOMv1.0'));
    $value = $copyrightAndOtherRestrictionse->appendChild($dom->createElement('value', 'yes'));

    $description = $rights->appendChild($dom->createElement('description'));
    $langstring = $description->appendChild($dom->createElement('string', htmlspecialchars($_POST['meta_rights'], ENT_QUOTES, 'utf-8')));
    $langstring->setAttribute('language', $_POST['meta_language']);
    // end of rights

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
        if ($i < count($inputValue)) {
            $child = $parent->appendChild($dom->createElement($element));
            $source = $child->appendChild($dom->createElement('source', 'LOMv1.0'));
        }
    }
}

function hasMetaData($filename, $basedir, $group_sql) {
    $xml = $filename . ".xml";
    $real_filename = $basedir . str_replace('/..', '', q($xml));
    $result = Database::get()->querySingle("SELECT * FROM document WHERE $group_sql AND path = ?s", $xml);

    if (file_exists($real_filename) && $result && $result->format == ".meta") {
        return true;
    } else {
        return false;
    }
}

/*
 * Update general->identifier->entry when renaming a file with metadata
 */

function metaRenameDomDocument($xmlFilename, $newEntry) {
    if (!file_exists($xmlFilename))
        return;

    $sxe = simplexml_load_file($xmlFilename);
    if ($sxe === false)
        return;

    $sxe->general->identifier->entry = $newEntry;

    $dom_sxe = dom_import_simplexml($sxe);
    if (!$dom_sxe)
        return;

    $dom = new DOMDocument('1.0');
    $dom_sxe = $dom->importNode($dom_sxe, true);
    $dom_sxe = $dom->appendChild($dom_sxe);
    $dom->formatOutput = true;
    $dom->save($xmlFilename);
}
