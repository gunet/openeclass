<?php

/* ========================================================================
 * Open eClass 3.7
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2019  Greek Universities Network - GUnet
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

/* ===========================================================================
  @file importLearningPath.php
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
                 Sakis Agorastos <th_agorastos@hotmail.com>
                 Piraux Sebastien <pir@cerdecam.be>
                 Lederer Guillaume <led@cerdecam.be>

  @brief: This script handles importing of SCORM packages.
  It mainly parses imsmanifest.xml and extracts the SCOs
  from the zip file.
  ==============================================================================
 */

$require_current_course = TRUE;
$require_editor = TRUE;
$require_help = TRUE;
$helpTopic = "learningpath";
$helpSubTopic = 'learning_objects';
require_once("../../include/baseTheme.php");
require_once "include/lib/learnPathLib.inc.php";
require_once "include/lib/fileManageLib.inc.php";
require_once "include/lib/fileUploadLib.inc.php";
require_once "include/lib/fileDisplayLib.inc.php";
require_once 'include/log.class.php';

$pwd = getcwd();

$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langLearningPaths);
$pageName = $langimportLearningPath;

// error handling
$errorFound = false;

/* --------------------------------------------------------
  Functions
  -------------------------------------------------------- */

/*
 * Function used by the SAX xml parser when the parser meets a opening tag
 * example :
 *          <manifest identifier="samplescorm" version"1.1">
 *      will give
 *          $name == "manifest"
 *          attributes["identifier"] == "samplescorm"
 *          attributes["version"]    == "1.1"
 *
 * @param $parser xml parser created with "xml_parser_create()"
 * @param $name name of the element
 * @param $attributes array with the attributes of the element
 */

function startElement($parser, $name, $attributes) {
    global $elementsPile, $itemsPile, $manifestData, $flagTag;

    array_push($elementsPile, $name);

    switch ($name) {
        case "MANIFEST" :
            if (isset($attributes['XML:BASE'])) {
                $manifestData['xml:base']['manifest'] = $attributes['XML:BASE'];
            }
            break;
        case "RESOURCES" :
            if (isset($attributes['XML:BASE'])) {
                $manifestData['xml:base']['resources'] = $attributes['XML:BASE'];
            }
            $flagTag['type'] == "resources";
            break;
        case "RESOURCE" :
            if (isset($attributes['ADLCP:SCORMTYPE']) && $attributes['ADLCP:SCORMTYPE'] == 'sco') {
                if (isset($attributes['HREF'])) {
                    $manifestData['scos'][$attributes['IDENTIFIER']]['href'] = $attributes['HREF'];
                }
                if (isset($attributes['XML:BASE'])) {
                    $manifestData['scos'][$attributes['IDENTIFIER']]['xml:base'] = $attributes['XML:BASE'];
                }
                $flagTag['type'] = "sco";
                $flagTag['value'] = $attributes['IDENTIFIER'];
            }
            elseif (isset($attributes['ADLCP:SCORMTYPE']) && $attributes['ADLCP:SCORMTYPE'] == 'asset') {
                if (isset($attributes['HREF'])) {
                    $manifestData['assets'][$attributes['IDENTIFIER']]['href'] = $attributes['HREF'];
                }
                if (isset($attributes['XML:BASE'])) {
                    $manifestData['assets'][$attributes['IDENTIFIER']]['xml:base'] = $attributes['XML:BASE'];
                }
                $flagTag['type'] = "asset";
                if (isset($attributes['IDENTIFIER'])) {
                    $flagTag['value'] = $attributes['IDENTIFIER'];
                }
            }
            else { // check in $manifestData['items'] if this ressource identifier is used
                foreach ($manifestData['items'] as $itemToCheck) {
                    if (isset($itemToCheck['identifierref']) && $itemToCheck['identifierref'] == $attributes['IDENTIFIER']) {
                        if (isset($attributes['HREF'])) {
                            $manifestData['scos'][$attributes['IDENTIFIER']]['href'] = $attributes['HREF'];
                        }

                        if (isset($attributes['XML:BASE'])) {
                            $manifestData['scos'][$attributes['IDENTIFIER']]['xml:base'] = $attributes['XML:BASE'];
                        }

                        // eidiko flag gia na anixneusoume osa scorm paketa einai typou assets
                        // dhladh osa den perilambanoun javascript gia thn parakolou8hsh ths proodou,
                        // opote allou ston kwdika 8a prepei na ta xeiristoume diaforetika
                        $manifestData['scos'][$attributes['IDENTIFIER']]['contentTypeFlag'] = CTSCORMASSET_;
                    }
                }
            }
            break;

        case "ITEM" :
            if (isset($attributes['IDENTIFIER'])) {
                $manifestData['items'][$attributes['IDENTIFIER']]['itemIdentifier'] = $attributes['IDENTIFIER'];

                if (isset($attributes['IDENTIFIERREF'])) {
                    $manifestData['items'][$attributes['IDENTIFIER']]['identifierref'] = $attributes['IDENTIFIERREF'];
                }
                if (isset($attributes['PARAMETERS'])) {
                    $manifestData['items'][$attributes['IDENTIFIER']]['parameters'] = $attributes['PARAMETERS'];
                }
                if (isset($attributes['ISVISIBLE'])) {
                    $manifestData['items'][$attributes['IDENTIFIER']]['isvisible'] = $attributes['ISVISIBLE'];
                }

                if (count($itemsPile) > 0) {
                    $manifestData['items'][$attributes['IDENTIFIER']]['parent'] = $itemsPile[count($itemsPile) - 1];
                }

                array_push($itemsPile, $attributes['IDENTIFIER']);

                if ($flagTag['type'] == "item") {
                    $flagTag['deep'] ++;
                } else {
                    $flagTag['type'] = "item";
                    $flagTag['deep'] = 0;
                }
                $manifestData['items'][$attributes['IDENTIFIER']]['deep'] = $flagTag['deep'];
                $flagTag['value'] = $attributes['IDENTIFIER'];
            }
            break;

        case "ORGANIZATIONS" :
            if (isset($attributes['DEFAULT'])) {
                $manifestData['defaultOrganization'] = $attributes['DEFAULT'];
            } else {
                $manifestData['defaultOrganization'] = '';
            }
            break;
        case "ORGANIZATION" :
            $flagTag['type'] = "organization";
            $flagTag['value'] = $attributes['IDENTIFIER'];
            break;
        case "ADLCP:LOCATION" :
            // when finding this tag we read the specified XML file so the data structure doesn't even
            // 'see' that this is another file
            // for that we remove this element from the pile so it doesn't appear when we compare the
            // pile with the position of an element
            $poped = array_pop($elementsPile);
            break;
    }
}

/**
 * Function used by the SAX xml parser when the parser meets a closing tag
 *
 * @param $parser xml parser created with "xml_parser_create()"
 * @param $name name of the element
 */
function endElement($parser, $name) {
    global $elementsPile, $itemsPile, $flagTag;

    switch ($name) {
        case "ITEM" :
            $trash = array_pop($itemsPile);
            if ($flagTag['type'] == "item" && $flagTag['deep'] > 0) {
                $flagTag['deep'] --;
            } else {
                $flagTag['type'] = "endItem";
            }
            break;
        case "RESOURCES" :
            $flagTag['type'] = "endResources";
            break;
        case "RESOURCE" :
            $flagTag['type'] = "endResource";
            break;
    }

    $poped = array_pop($elementsPile);
}

/**
 * Function used by the SAX xml parser when the parser meets something that's not a tag
 *
 * @param $parser xml parser created with "xml_parser_create()"
 * @param $data "what is not a tag"
 */
function elementData($parser, $data) {
    global $elementsPile;
    global $itemsPile;
    global $manifestData;
    global $flagTag;
    global $iterator;
    global $errorFound;
    global $langErrorReadingXMLFile;
    global $errorMsgs;
    global $pathToManifest;
    global $langErrorOpeningXMLFile;


// -----------------------------------
// when eclass is full utf 8 restore this
// -----------------------------------------

    if (!isset($data)) {
        $data = "";
    } else {
        $data = trim($data);
    }

    switch ($elementsPile[count($elementsPile) - 1]) {

        case "RESOURCE" :
            break;
        case "TITLE" :
            // $data == '' (empty string) means that title tag contains elements (<langstring> for an exemple), so it's not the title we need
            if ($data != '') {
                if ($flagTag['type'] == "item") { // item title check
                    if (!isset($manifestData['items'][$flagTag['value']]['title'])) {
                        $manifestData['items'][$flagTag['value']]['title'] = "";
                    }
                    $manifestData['items'][$flagTag['value']]['title'] .= $data;
                }


                // get title of package if it was not find in the manifest metadata in the default organization
                if ($elementsPile[sizeof($elementsPile) - 2] == "ORGANIZATION" && $flagTag['type'] == "organization" && $flagTag['value'] == $manifestData['defaultOrganization']) {
                    // if we do not find this title
                    //  - the metadata title has been set as package title
                    //  - if there was nor title for metadata nor for default organization set 'unamed path'
                    // If we are here it means we have found the title in organization, this is the best to chose
                    $manifestData['packageTitle'] = $data;
                }
            }
            break;

        case "ITEM" :
            break;

        case "ADLCP:DATAFROMLMS" :
            $manifestData['items'][$flagTag['value']]['datafromlms'] = $data;
            break;

        // found a link to another XML file, parse it ...
        case "ADLCP:LOCATION" :
            $file = "imsmanifest.xml";
            if (!($fp = @fopen($file, "r"))) {
                $errorFound = true;
                $errorMsgs[] = $langErrorOpeningManifest;
            } else {
                if (!$errorFound) {
                    $xml_parser = xml_parser_create();
                    xml_set_element_handler($xml_parser, "startElement", "endElement");
                    xml_set_character_data_handler($xml_parser, "elementData");

                    $file = rawurldecode($data); //url of secondary manifest files is relative to the position of the base imsmanifest.xml
                    $zipFile = new ZipArchive();
                    if ($zipFile->open($pathToManifest . $file) == TRUE) {
                        if ($zipFile->extractTo($pathToManifest)) {
                            $zipFile->close();
                        }
                    } else {
                        array_push($errorMsgs, $langErrorOpeningXMLFile . $pathToManifest . $file);
                    }

                    if (!$errorFound) {
                        if (!isset($cache)) {
                            $cache = "";
                        }
                        while ($readdata = str_replace("\n", "", fread($fp, 4096))) {
                            // fix for fread breaking thing
                            // msg from "ml at csite dot com" 02-Jul-2003 02:29 on http://www.php.net/xml
                            // preg expression has been modified to match tag with inner attributes
                            $readdata = $cache . $readdata;
                            if (!feof($fp)) {
                                if (preg_match_all("/<[^\>]*.>/", $readdata, $regs)) {
                                    $lastTagname = $regs[0][count($regs[0]) - 1];
                                    $split = false;
                                    for ($i = strlen($readdata) - strlen($lastTagname); $i >= strlen($lastTagname); $i--) {
                                        if ($lastTagname == substr($readdata, $i, strlen($lastTagname))) {
                                            $cache = substr($readdata, $i, strlen($readdata));
                                            $readdata = substr($readdata, 0, $i);
                                            $split = true;
                                            break;
                                        }
                                    }
                                }
                                if (!$split) {
                                    $cache = $readdata;
                                }
                            }
                            // end of fix
                            if (!xml_parse($xml_parser, $readdata, feof($fp))) {
                                // if reading of the xml file in not successfull :
                                // set errorFound, set error msg, break while statement
                                $errorFound = true;
                                array_push($errorMsgs, $langErrorReadingXMLFile . $pathToManifest . $file);
                                break;
                            }
                        } // while $readdata
                    }    //if fopen
                    @fclose($fp); // close file
                }
            }
            break;

        case "LANGSTRING" :
            if (isset($flagTag['type'])) {
                switch ($flagTag['type']) {
                    case "item" :
                        // DESCRIPTION
                        // if the langstring tag is a children of a description tag
                        if ($elementsPile[sizeof($elementsPile) - 2] == "DESCRIPTION" && $elementsPile[sizeof($elementsPile) - 3] == "GENERAL") {
                            if (!isset($manifestData['items'][$flagTag['value']]['description'])) {
                                $manifestData['items'][$flagTag['value']]['description'] = "";
                            }
                            $manifestData['items'][$flagTag['value']]['description'] .= $data;
                        }
                        // title found in metadata of an item (only if we haven't already one title for this sco)
                        if ($manifestData['items'][$flagTag['value']]['title'] == '' || !isset($manifestData['items'][$flagTag['value']]['title'])) {
                            if ($elementsPile[sizeof($elementsPile) - 2] == "TITLE" && $elementsPile[sizeof($elementsPile) - 3] == "GENERAL") {
                                $manifestData['items'][$flagTag['value']]['title'] .= $data;
                            }
                        }
                        break;
                    case "sco" :
                        // DESCRIPTION
                        // if the langstring tag is a children of a description tag
                        if ($elementsPile[sizeof($elementsPile) - 2] == "DESCRIPTION" && $elementsPile[sizeof($elementsPile) - 3] == "GENERAL") {
                            if (isset($manifestData['scos'][$flagTag['value']]['description'])) {
                                $manifestData['scos'][$flagTag['value']]['description'] .= $data;
                            } else {
                                $manifestData['scos'][$flagTag['value']]['description'] = $data;
                            }
                        }
                        // title found in metadata of an item (only if we haven't already one title for this sco)
                        if (!isset($manifestData['scos'][$flagTag['value']]['title']) || $manifestData['scos'][$flagTag['value']]['title'] == '') {
                            if ($elementsPile[sizeof($elementsPile) - 2] == "TITLE" && $elementsPile[sizeof($elementsPile) - 3] == "GENERAL") {
                                $manifestData['scos'][$flagTag['value']]['title'] = $data;
                            }
                        }
                        break;
                    case "asset" :
                        // DESCRIPTION
                        // if the langstring tag is a children of a description tag
                        if ($elementsPile[sizeof($elementsPile) - 2] == "DESCRIPTION" && $elementsPile[sizeof($elementsPile) - 3] == "GENERAL") {
                            if (isset($manifestData['assets'][$flagTag['value']]['description'])) {
                                $manifestData['assets'][$flagTag['value']]['description'] .= $data;
                            } else {
                                $manifestData['assets'][$flagTag['value']]['description'] = $data;
                            }
                        }
                        // title found in metadata of an item (only if we haven't already one title for this sco)
                        if (!isset($manifestData['assets'][$flagTag['value']]['title']) || $manifestData['assets'][$flagTag['value']]['title'] == '') {
                            if ($elementsPile[sizeof($elementsPile) - 2] == "TITLE" && $elementsPile[sizeof($elementsPile) - 3] == "GENERAL") {
                                if (isset($manifestData['assets'][$flagTag['value']]['title'])) {
                                    $manifestData['assets'][$flagTag['value']]['title'] .= $data;
                                } else {
                                    $manifestData['assets'][$flagTag['value']]['title'] = $data;
                                }
                            }
                        }
                        break;
                    default :
                        // DESCRIPTION
                        $posPackageDesc = array("MANIFEST", "METADATA", "LOM", "GENERAL", "DESCRIPTION");
                        if (compareArrays($posPackageDesc, $elementsPile)) {
                            if (!isset($manifestData['packageDesc'])) {
                                $manifestData['packageDesc'] = "";
                            }
                            $manifestData['packageDesc'] .= $data;
                        }

                        if (!isset($manifestData['packageTitle']) || $manifestData['packageTitle'] == '') {
                            $posPackageTitle = array("MANIFEST", "METADATA", "LOM", "GENERAL", "TITLE");
                            if (compareArrays($posPackageTitle, $elementsPile)) {
                                $manifestData['packageTitle'] = $data;
                            }
                        }
                        break;
                } // end switch ( $flagTag['type'] )
            }
            break;

        default :
            break;
    } // end switch ($elementsPile[count($elementsPile)-1] )
}

/**
 * This function checks in element pile if the sequence of markup is the same as in array2Compare
 * Checks if the sequence is the same in the begining of pile.
 * If the sequences are the same then it means that the elementdata is the one we were looking for.
 *
 * @param $array1 list xml markups upper than the requesting markup
 * @return true if arrays are the same, false otherwise
 */
function compareArrays($array1, $array2) {
    // sizeof(array2) so we do not compare the last tag, this is the one we are in, so we not that already.
    for ($i = 0; $i < sizeof($array2) - 1; $i++) {
        if ($array1[$i] != $array2[$i]) {
            return false;
        }
    }
    return true;
}

/**
 * This function return true if $Str could be UTF-8, false otherwise
 *
 * function found @ http://www.php.net/manual/en/function.utf8-encode.php
 */
function seems_utf8($str) {
    for ($i = 0; $i < strlen($str); $i++) {
        if (ord($str[$i]) < 0x80) {
            continue; // 0bbbbbbb
        } else if ((ord($str[$i]) & 0xE0) == 0xC0) {
            $n = 1; // 110bbbbb
        } else if ((ord($str[$i]) & 0xF0) == 0xE0) {
            $n = 2; // 1110bbbb
        } else if ((ord($str[$i]) & 0xF8) == 0xF0) {
            $n = 3; // 11110bbb
        } else if ((ord($str[$i]) & 0xFC) == 0xF8) {
            $n = 4; // 111110bb
        } else if ((ord($str[$i]) & 0xFE) == 0xFC) {
            $n = 5; // 1111110b
        } else {
            return false; // Does not match any model
        }
        for ($j = 0; $j < $n; $j++) { // n bytes matching 10bbbbbb follow ?
            if (( ++$i == strlen($str)) || ((ord($str[$i]) & 0xC0) != 0x80)) {
                return false;
            }
        }
    }
    return true;
}

/**
 *
 */
function utf8_decode_if_is_utf8($str) {
    return seems_utf8($str) ? utf8_decode($str) : $str;
}

function replaceIdHiddenInput() {
    global $webDir, $course_code;
    $replaceId = (isset($_GET['replace_id']) && intval($_GET['replace_id']) > 0) ? intval($_GET['replace_id']) : 0;
    if (is_dir($webDir . "/courses/" . $course_code . "/scormPackages/path_" . $replaceId)) {
        return "<input type='hidden' name='replace_id' value='" . $replaceId . "'>";
    } else {
        return '';
    }
}

$okMsgs = array();
$errorMsgs = array();
$maxFilledSpace = 1.0e10; // Max filled space: 10 GB
$baseWorkDir = 'courses/' . $course_code . '/scormPackages/';

if (!is_dir($baseWorkDir)) {
    make_dir($baseWorkDir);
}

// handle upload
// if the post is done a second time, the claroformid mecanism
// will set $_POST to NULL, so we need to check it
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !is_null($_POST)) {

    // arrays used to store inserted ids
    $insertedModule_id = array();
    $insertedAsset_id = array();

    $lpName = $langUnamedPath;
    $replace_id = null;

    // handle learnpath id replacing mode
    if (isset($_POST['replace_id']) && intval($_POST['replace_id']) > 0) {
        // validate replace_id
        $valid = intval(Database::get()->querySingle("SELECT 1 as exist FROM lp_learnPath where learnPath_id = ?d and course_id = ?d", $_POST['replace_id'], $course_id)->exist);
        if ($valid == 1) {
            // init vars
            $replace_id = intval($_POST['replace_id']);
            $tempPathId = $replace_id;
            $rankMax = intval(Database::get()->querySingle("SELECT `rank` AS max FROM lp_learnPath WHERE learnPath_id = ?d", $replace_id)->max);

            // delete old lp
            $lp_name = deleteLearningPath($replace_id);
            Log::record($course_id, MODULE_ID_LP, LOG_DELETE, array('name' => $lp_name));

            // create new lp
            Database::get()->query("INSERT INTO `lp_learnPath`
            (`learnPath_id`, `course_id`, `name`,`visible`,`rank`,`comment`)
            VALUES (?d, ?d, ?s, 0, ?d,'')", $tempPathId, $course_id, $lpName, $rankMax);
        }
    }

    if ($replace_id == null) {
        // we need a new path_id for this learning path so we prepare a line in DB
        // this line will be removed if an error occurs
        $rankMax = 1 + intval(Database::get()->querySingle("SELECT MAX(`rank`) AS max
            FROM `lp_learnPath` WHERE `course_id` = ?d", $course_id)->max);

        $tempPathId = Database::get()->query("INSERT INTO `lp_learnPath`
            (`course_id`, `name`,`visible`,`rank`,`comment`)
            VALUES (?d, ?s, 0, ?d,'')", $course_id, $lpName, $rankMax)->lastInsertID;
    }
    $baseWorkDir .= "path_" . $tempPathId;

    if (!is_dir($baseWorkDir)) {
        make_dir($baseWorkDir);
    }

    /*
     * Check if the file is valid (not to big and exists)
     */
    if (!isset($_FILES['uploadedPackage']) || !is_uploaded_file($_FILES['uploadedPackage']['tmp_name'])) {
        $errorFound = true;
        array_push($errorMsgs, $langFileScormError);
    }

    /*
     * Check the file size doesn't exceed
     * the maximum file size authorized in the directory
     */ elseif (!enough_size($_FILES['uploadedPackage']['size'], $baseWorkDir, $maxFilledSpace)) {
        $errorFound = true;
        array_push($errorMsgs, $langNoSpace);
    }

    /*
     * Unzipping stage
     */
    elseif (preg_match("/.zip$/i", $_FILES['uploadedPackage']['name'])) {

        array_push($okMsgs, $langOkFileReceived . basename($_FILES['uploadedPackage']['name']));

        $zipFile = new ZipArchive();
        $is_allowedToUnzip = true; // default initialisation

        $pathToManifest = ""; // empty by default because we can expect that the manifest.xml is in the root of zip file
        $pathToManifestFound = false;

        $zipname = $_FILES['uploadedPackage']['name'];
        $files_in_zip = array();
        if (move_uploaded_file($_FILES['uploadedPackage']['tmp_name'], "$webDir/$baseWorkDir/$zipname")) {
            if ($zipFile->open("$webDir/$baseWorkDir/$zipname") == TRUE) {
                for ($i = 0; $i < $zipFile->numFiles; $i++) {
                    $stat = $zipFile->statIndex($i, ZipArchive::FL_ENC_RAW);
                    $files_in_zip[$i] = $stat['name'];
                    validateUploadedFile($files_in_zip[$i]);
                }

                if ($zipFile->extractTo("$webDir/$baseWorkDir")) {
                    $zipFile->close();
                } else {
                    $errorFound = true;
                    array_push($errorMsgs, $langZipError);
                }
            } else {
                $errorFound = true;
                array_push($errorMsgs, $langErrorFileMustBeZip);
            }
        }
    }

    chdir($baseWorkDir);

    // find xmlmanifest (must be in root else ==> cancel operation, delete files)
    // parse xml manifest to find :
    // package name - learning path name
    // SCO list
    // start asset path

    if (!$errorFound) {
        $elementsPile = array(); // array used to remember where we are in the arborescence of the XML file
        $itemsPile = array(); // array used to remember parents items
        // declaration of global arrays used for extracting needed info from manifest for the new modules/SCO
        $manifestData = array();   // for global data  of the learning path
        $manifestData['items'] = array(); // item tags content (attributes + some child elements data (title for an example)
        $manifestData['scos'] = array();  // for path of start asset id of each new module to create

        $iterator = 0; // will be used to increment position of paths in manifestData['scosPaths"]
        // and to have the names at the same pos if found
        //$xml_parser = xml_parser_create();
        $xml_parser = xml_parser_create('utf-8');
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler($xml_parser, "elementData");

        // this file has to exist in a SCORM conformant package
        // this file must be in the root the sent zip
        $file = "imsmanifest.xml";

        if (!($fp = @fopen($file, "r"))) {
            $errorFound = true;
            array_push($errorMsgs, $langErrorOpeningManifest);
        } else {
            if (!isset($manifestPath)) {
                $manifestPath = "";
            }

            array_push($okMsgs, $langOkManifestFound . $manifestPath . "imsmanifest.xml");

            while ($data = str_replace("\n", "", fread($fp, 4096))) {
                // fix for fread breaking thing
                // msg from "ml at csite dot com" 02-Jul-2003 02:29 on http://www.php.net/xml
                // preg expression has been modified to match tag with inner attributes

                if (!isset($cache)) {
                    $cache = "";
                }

                $data = $cache . $data;
                if (!feof($fp)) {
                    // search fo opening, closing, empty tags (with or without attributes)
                    if (preg_match_all("/<[^\>]*.>/", $data, $regs)) {
                        $lastTagname = $regs[0][count($regs[0]) - 1];
                        $split = false;
                        for ($i = strlen($data) - strlen($lastTagname); $i >= strlen($lastTagname); $i--) {
                            if ($lastTagname == substr($data, $i, strlen($lastTagname))) {
                                $cache = substr($data, $i, strlen($data));
                                $data = substr($data, 0, $i);
                                $split = true;
                                break;
                            }
                        }
                    }

                    if (!$split) {
                        $cache = $data;
                    }
                }
                // end of fix

                if (!xml_parse($xml_parser, $data, feof($fp))) {
                    // if reading of the xml file in not successfull :
                    // set errorFound, set error msg, break while statement

                    $errorFound = true;
                    array_push($errorMsgs, $langErrorReadingManifest);
                    // Since manifest.xml cannot be parsed, test versus IMS CP 1.4.4 XSD (compatible with all SCORM packages as well)
                    require_once 'include/validateXML.php';
                    libxml_use_internal_errors(true);

                    $xml = new DOMDocument();
                    $xml->load($manifestPath."imsmanifest.xml");

                    if (!$xml->schemaValidate($urlServer . 'modules/learnPath/export/imscp_v1p2.xsd')) {
                        $messages = libxml_display_errors();

                        array_push($errorMsgs, $langErrorValidatingManifest . $messages);
                    }
                    break;
                }
            }
            // close file
            fclose($fp);
        }

        // liberate parser ressources
        xml_parser_free($xml_parser);
    } //if (!$errorFound)
    // check if all starts assets files exist in the zip file
    if (!$errorFound) {

        array_push($okMsgs, $langOkManifestRead);
        if (sizeof($manifestData['items']) > 0) {
            // if there is items in manifest we look for sco type resources referenced in idientifierref
            foreach ($manifestData['items'] as $item) {
                if (!isset($item['identifierref']) || $item['identifierref'] == '') {
                    break;
                } // skip if no ressource reference in item (item is probably a chapter head)

                // find the file in the zip file
                $scoPathFound = false;

                for ($i = 0; $i < sizeof($files_in_zip); $i++) {
                    if (isset($manifestData['scos'][$item['identifierref']]['xml:base'])) {
                        $extraPath = $manifestData['scos'][$item['identifierref']]['xml:base'];
                    } else if (isset($manifestData['assets'][$item['identifierref']]['xml:base'])) {
                        $extraPath = $manifestData['assets'][$item['identifierref']]['xml:base'];
                    } else {
                        $extraPath = "";
                    }

                    if (isset($files_in_zip[$i]) &&
                            ( ( isset($manifestData['scos'][$item['identifierref']]['href']) && $files_in_zip[$i] == $pathToManifest . $extraPath . $manifestData['scos'][$item['identifierref']]['href']) || (isset($manifestData['assets'][$item['identifierref']]['href']) && $files_in_zip[$i] == $pathToManifest . $extraPath . $manifestData['assets'][$item['identifierref']]['href'])
                            )
                    ) {
                        $scoPathFound = true;
                        break;
                    }
                }

                if (!$scoPathFound) {
                    $errorFound = true;
                    array_push($errorMsgs, $langErrorAssetNotFound . $manifestData['scos'][$item['identifierref']]['href']);
                    break;
                }
            }
        } //if (sizeof ...)
        elseif (sizeof($manifestData['scos']) > 0) {
            // if there ie no items in the manifest file
            // check for scos in resources

            foreach ($manifestData['scos'] as $sco) {
                // find the file in the zip file
                // create a fake item so that the rest of the procedure (add infos of in db) can remains the same
                $manifestData['items'][$sco['href']]['identifierref'] = $sco['href'];
                $manifestData['items'][$sco['href']]['parameters'] = '';
                $manifestData['items'][$sco['href']]['isvisible'] = "true";
                $manifestData['items'][$sco['href']]['title'] = $sco['title'];
                $manifestData['items'][$sco['href']]['description'] = $sco['description'];
                $manifestData['items'][$attributes['IDENTIFIER']]['parent'] = 0;

                $scoPathFound = false;

                for ($i = 0; $i < sizeof($files_in_zip); $i++) {
                    if ($files_in_zip[$i] == $sco['href']) {
                        $scoPathFound = true;
                        break;
                    }
                }
                if (!$scoPathFound) {
                    $errorFound = true;
                    array_push($errorMsgs, $langErrorAssetNotFound . $sco['href']);
                    break;
                }
            }
        } // if sizeof (...ΰ
        else {
            $errorFound = true;
            array_push($errorMsgs, $langErrorNoModuleInPackage);
        }
    }// if errorFound
    // unzip all files
    // &&
    // insert corresponding entries in database

    if (!$errorFound) {
        // insert informations in DB :
        //        - 1 learning path ( already added because we needed its id to create the package directory )
        //        - n modules
        //        - n asset as start asset of modules

        if (sizeof($manifestData['items']) == 0) {
            $errorFound = true;
            array_push($errorMsgs, $langErrorNoModuleInPackage);
        } else {
            $i = 0;
            $insertedLPMid = array(); // array of learnPath_module_id && order of related group
            $inRootRank = 1; // default rank for root module (parent == 0)

            foreach ($manifestData['items'] as $item) {
                if (isset($item['parent']) && isset($insertedLPMid[$item['parent']])) {
                    $parent = $insertedLPMid[$item['parent']]['LPMid'];
                    $rank = $insertedLPMid[$item['parent']]['rank'] ++;
                } else {
                    $parent = 0;
                    $rank = $inRootRank++;
                }

                //-------------------------------------------------------------------------------
                // add chapter head
                //-------------------------------------------------------------------------------

                if ((!isset($item['identifierref']) || $item['identifierref'] == '') && isset($item['title']) && $item['title'] != '') {
                    // add title as a module
                    $chapterTitle = $item['title'];

                    // array of all inserted module ids
                    $insertedModule_id[$i] = Database::get()->query("INSERT INTO `lp_module`
                            (`course_id`, `name`, `comment`, `contentType`, `launch_data`)
                            VALUES (?d, ?s, '', ?s,'')", $course_id, $chapterTitle, CTLABEL_)->lastInsertID;
                    if (!$insertedModule_id[$i]) {
                        $errorFound = true;
                        array_push($errorMsgs, $langErrorSql);
                        break;
                    }
                    // visibility
                    if (isset($item['isvisible']) && $item['isvisible'] != '') {
                        $visibility = ($item['isvisible'] == "true") ? 1 : 0;
                    } else {
                        $visibility = 1; // IMS consider that the default value of 'isvisible' is true
                    }

                    // add title module in the learning path
                    // finally : insert in learning path
                    // get the inserted id of the learnPath_module rel to allow 'parent' link in next inserts
                    $insertedLPMid[$item['itemIdentifier']]['LPMid'] = Database::get()->query("INSERT INTO `lp_rel_learnPath_module`
                            (`learnPath_id`, `module_id`,`rank`, `visible`, `parent`, `specificComment`)
                            VALUES (?d, ?d, ?d, ?d, ?d, '')", $tempPathId, $insertedModule_id[$i], $rank, $visibility, $parent)->lastInsertID;
                    $insertedLPMid[$item['itemIdentifier']]['rank'] = 1;

                    if (!$insertedLPMid[$item['itemIdentifier']]['LPMid']) {
                        $errorFound = true;
                        array_push($errorMsgs, $langErrorSql);
                        break;
                    }
                    if (!$errorFound) {
                        array_push($okMsgs, $langOkChapterHeadAdded . "<i>" . $chapterTitle . "</i>");
                    }
                    $i++;
                    continue;
                }

                // use found title of module or use default title
                if (!isset($item['title']) || $item['title'] == '') {
                    $moduleName = $langUnamedModule;
                } else {
                    $moduleName = $item['title'];
                }

                // set description as comment or default comment
                // look fo description in item description or in sco (resource) description
                // don't remember why I checked for parameters string ... so comment it
                if ((!isset($item['description']) || $item['description'] == '' ) &&
                        (!isset($manifestData['scos'][$item['identifierref']]['description']) /* || $manifestData['scos'][$item['identifierref']]['parameters'] == '' */ )
                ) {
                    $description = $langDefaultModuleComment;
                } else {
                    if (isset($item['description']) && $item['description'] != '') {
                        $description = $item['description'];
                    } else {
                        $description = $manifestData['scos'][$item['identifierref']]['description'];
                    }
                }

                // insert modules and their start asset
                // create new module

                if (!isset($item['datafromlms'])) {
                    $item['datafromlms'] = "";
                }

                // elegxoume an to contentType prepei na einai scorm h asset
                if (isset($manifestData['scos'][$item['identifierref']]['href']) and parse_url($manifestData['scos'][$item['identifierref']]['href'],  PHP_URL_HOST) !== null) {
                    $contentType = CTLINK_;
                } elseif (isset($manifestData['scos'][$item['identifierref']]['contentTypeFlag']) && $manifestData['scos'][$item['identifierref']]['contentTypeFlag'] == CTSCORMASSET_) {
                    $contentType = CTSCORMASSET_;
                } else {
                    $contentType = CTSCORM_;
                }

                // array of all inserted module ids
                $insertedModule_id[$i] = Database::get()->query("INSERT INTO `lp_module`
                        (`course_id`, `name`, `comment`, `contentType`, `launch_data`)
                        VALUES (?d, ?s, ?s, ?s, ?s)", $course_id, $moduleName, $description, $contentType, $item['datafromlms'])->lastInsertID;

                if (!$insertedModule_id[$i]) {
                    $errorFound = true;
                    array_push($errorMsgs, $langErrorSql);
                    break;
                }
                // build asset path
                // a $manifestData['scos'][$item['identifierref']] __SHOULD__ not exist if a $manifestData['assets'][$item['identifierref']] exists
                // so according to IMS we can say that one is empty if the other is filled, so we concat them without more verification than if the var exists.

                // suppress notices
                if (!isset($manifestData['xml:base']['manifest'])) {
                    $manifestData['xml:base']['manifest'] = "";
                }
                if (!isset($manifestData['xml:base']['ressources'])) {
                    $manifestData['xml:base']['ressources'] = "";
                }
                if (!isset($manifestData['scos'][$item['identifierref']]['href'])) {
                    $manifestData['scos'][$item['identifierref']]['href'] = "";
                }
                if (!isset($manifestData['assets'][$item['identifierref']]['href'])) {
                    $manifestData['assets'][$item['identifierref']]['href'] = "";
                }
                if (!isset($manifestData['scos'][$item['identifierref']]['parameters'])) {
                    $manifestData['scos'][$item['identifierref']]['parameters'] = "";
                }
                if (!isset($manifestData['assets'][$item['identifierref']]['parameters'])) {
                    $manifestData['assets'][$item['identifierref']]['parameters'] = "";
                }
                if (!isset($manifestData['items'][$item['itemIdentifier']]['parameters'])) {
                    $manifestData['items'][$item['itemIdentifier']]['parameters'] = "";
                }

                if (isset($manifestData['scos'][$item['identifierref']]['xml:base'])) {
                    $extraPath = $manifestData['scos'][$item['identifierref']]['xml:base'];
                } else if (isset($manifestData['assets'][$item['identifierref']]['xml:base'])) {
                    $extraPath = $manifestData['assets'][$item['identifierref']]['xml:base'];
                } else {
                    $extraPath = "";
                }

                if ($contentType == CTLINK_) {
                    $assetPath = $manifestData['scos'][$item['identifierref']]['href'];
                } else {
                    $assetPath = "/"
                        . $manifestData['xml:base']['manifest']
                        . $manifestData['xml:base']['ressources']
                        . $extraPath
                        . $manifestData['scos'][$item['identifierref']]['href']
                        . $manifestData['assets'][$item['identifierref']]['href']
                        . $manifestData['scos'][$item['identifierref']]['parameters']
                        . $manifestData['assets'][$item['identifierref']]['parameters']
                        . $manifestData['items'][$item['itemIdentifier']]['parameters'];
                }

                // create new asset
                // array of all inserted asset ids
                $insertedAsset_id[$i] = Database::get()->query("INSERT INTO `lp_asset`
                        (`path` , `module_id` , `comment`)
                        VALUES (?s, ?d, '')", $assetPath, $insertedModule_id[$i])->lastInsertID;

                if (!$insertedAsset_id[$i]) {
                    $errorFound = true;
                    array_push($errorMsgs, $langErrorSql);
                    break;
                }
                // update of module with correct start asset id
                Database::get()->query("UPDATE `lp_module`
                        SET `startAsset_id` = ?d
                        WHERE `module_id` = ?d
                        AND `course_id` = ?d", $insertedAsset_id[$i], $insertedModule_id[$i], $course_id);

                // visibility
                if (isset($item['isvisible']) && $item['isvisible'] != '') {
                    $visibility = ($item['isvisible'] == "true") ? 1 : 0;
                } else {
                    $visibility = 1; // IMS consider that the default value of 'isvisible' is true
                }

                // finally : insert in learning path
                // get the inserted id of the learnPath_module rel to allow 'parent' link in next inserts
                $insertedLPMid[$item['itemIdentifier']]['LPMid'] = Database::get()->query("INSERT INTO `lp_rel_learnPath_module`
                        (`learnPath_id`, `module_id`, `specificComment`, `rank`, `visible`, `lock`, `parent`)
                        VALUES (?d, ?d, ?s, ?d, ?d, 'OPEN', ?d)", $tempPathId, $insertedModule_id[$i], $langDefaultModuleAddedComment, $rank, $visibility, $parent)->lastInsertID;
                $insertedLPMid[$item['itemIdentifier']]['rank'] = 1;

                if (!$insertedLPMid[$item['itemIdentifier']]['LPMid']) {
                    $errorFound = true;
                    array_push($errorMsgs, $langErrorSql);
                    break;
                }

                if (!$errorFound) {
                    array_push($okMsgs, $langOkModuleAdded . "<i>" . $moduleName . "</i>");
                }
                $i++;
            }//foreach
        } // if sizeof($manifestData['items'] == 0 )
    } // if errorFound
    // last step
    // - delete all added files/directories/records in db
    // or
    // - update the learning path record

    if ($errorFound) {
        // delete all database entries of this "module"
        // delete modules and assets (build query)
        // delete assets
        $sqlDelAssets = "DELETE FROM `lp_asset` WHERE 1 = 0";
        foreach ($insertedAsset_id as $insertedAsset) {
            $sqlDelAssets .= " OR `asset_id` = " . intval($insertedAsset);
        }
        Database::get()->query($sqlDelAssets);

        // delete modules
        $sqlDelModules = "DELETE FROM `lp_module` WHERE 1 = 0";
        foreach ($insertedModule_id as $insertedModule) {
            $sqlDelModules .= " OR ( `module_id` = " . intval($insertedModule) . " AND `course_id` = " . intval($course_id) . " )";
        }
        Database::get()->query($sqlDelModules);

        // delete learningPath_module
        Database::get()->query("DELETE FROM `lp_rel_learnPath_module` WHERE `learnPath_id` = ?d", $tempPathId);

        // delete learning path
        Database::get()->query("DELETE FROM `lp_learnPath`
                     WHERE `learnPath_id` = ?d
                     AND `course_id` = ?d", $tempPathId, $course_id);

        // delete the directory (and files) of this learning path and all its content
        claro_delete_file($baseWorkDir);
    } else {
        // finalize insertion : update the empty learning path insert that was made to find its id
        if ($replace_id != null) {
            $rankMax = 1 + intval(Database::get()->querySingle("SELECT MAX(`rank`) AS max
                FROM `lp_learnPath`
                WHERE `course_id` = ?d", $course_id)->max);
        }

        if (isset($manifestData['packageTitle'])) {
            $lpName = $manifestData['packageTitle'];
        } else {
            array_push($okMsgs, $langOkDefaultTitleUsed);
        }

        if (isset($manifestData['packageDesc'])) {
            $lpComment = $manifestData['packageDesc'];
        } else {
            array_push($okMsgs, $langOkDefaultCommentUsed);
            $lpComment = '';
        }

        Database::get()->query("UPDATE `lp_learnPath`
                SET `rank` = ?d,
                    `name` = ?s,
                    `comment` = ?s,
                    `visible` = 1
                WHERE `learnPath_id` = ?d
                AND `course_id` = ?d", $rankMax, $lpName, $lpComment, $tempPathId, $course_id);
    }

    /* --------------------------------------
      status messages
      -------------------------------------- */
    foreach ($okMsgs as $msg) {
        $tool_content .= "<div class='alert alert-success'>" . icon('fa-check', $langSuccessOk) . ' ' . $msg . '</div>';
    }

    foreach ($errorMsgs as $msg) {
        $tool_content .= "<div class='alert alert-danger'>" . icon('fa-times', $langError) . ' ' . $msg . '</div>';
    }

    // installation completed or not message
    if (!$errorFound) {
        $tool_content .= "<div class='alert alert-info'>" . $langInstalled . "</div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>" . $langNotInstalled . "</div>";
    }

    $tool_content .=  action_bar(array(
            array('title' => $langBack,
                'url' => "index.php?course=$course_code",
                'icon' => 'fa-reply',
                'level' => 'primary-label')),false) ;
} else { // if method == 'post'
    // don't display the form if user already sent it
    /* --------------------------------------
      UPLOAD FORM
      -------------------------------------- */

    // Action_bar section
    $tool_content .= "
        <div class='row'>
            <div class='col-sm-12'>";
            $tool_content .= action_bar(array(
                        array('title' => $langBack,
                            'url' => "index.php?course=$course_code",
                            'icon' => 'fa-reply',
                            'level' => 'primary-label'
                        )
                    ),false);
            $tool_content .= "</div>
        </div>
    ";
    // Upload Form section
    enableCheckFileSize();
    $tool_content .="
        <div class='row'>
            <div class='col-sm-12'>
                <div class='form-wrapper'>
                    <h4 class='form-heading'>$langImport</h4>
                    <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' enctype='multipart/form-data'>
                        <div class='form-group'>
                            <label for='uploadedPackage' class='col-sm-2 control-label'>$langPathUploadFile</label>
                            <div class='col-sm-10'>
                                <input type='hidden' name='claroFormId' value='" . uniqid('') . "' >" .
                                fileSizeHidenInput() . replaceIdHiddenInput() . "
                                <input id='uploadedPackage' type='file' name='uploadedPackage'>
                                <span class='smaller'>$langLearningPathUploadFile</span>
                                <span class='help-block' style='margin-bottom: 0px;'><small>$langMaxFileSize " . ini_get('upload_max_filesize') . "</small></span>
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-sm-offset-2 col-sm-10'>".form_buttons(array(
                                    array(
                                        'text' => $langImport,
                                        'value'=> $langImport
                                    ),
                                    array(
                                        'href' => "index.php?course=$course_code",
                                    )
                                ))."</div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    ";

    $tool_content .= "
            <div class='row'>
                <div class='col-xs-12'>
                    <div class='alert alert-info'>
                        <p>$langNote:</p>
                        <p>$langScormIntroTextForDummies</p>
                    </div>
                </div>
            </div>
            ";
} // else if method == 'post'

chdir($pwd);
draw($tool_content, 2, null, $head_content);
