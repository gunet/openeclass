<?php

/* ========================================================================
 * Open eClass 2.8
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

class CourseXMLElement extends SimpleXMLElement {

    const DEFAULT_NS = 'http://www.openeclass.org';
    const NO_LEVEL = 0;
    const A_MINUS_LEVEL = 1;
    const A_LEVEL = 2;
    const A_PLUS_LEVEL = 3;

    private static $tmpData = array();

    /**
     * Get element's attribute if exists.
     * Returns string with attribute value or
     * boolean false if it doesn't exists.
     * 
     * @param  string $name
     * @return mixed 
     */
    public function getAttribute($name) {
        $attributes = $this->attributes();
        if (isset($attributes[$name])) {
            return $attributes[$name];
        } else {
            return false;
        }
    }

    /**
     * Recursively set a leaf element's attribute.
     * 
     * @param string $name
     * @param string $value
     */
    public function setLeafAttribute($name, $value) {
        $children = $this->children();
        if (count($children) == 0) {
            $this->addAttribute($name, $value);
        }

        foreach ($children as $ele) {
            $ele->setLeafAttribute($name, $value);
        }
    }

    /**
     * Returns an HTML Form for editing the XML.
     * 
     * @global string $code_cours
     * @global string $langSubmit
     * @global string $langRequiredFields
     * @param  array  $data - array containing data to preload the form with
     * @return string
     */
    public function asForm($data = null) {
        global $code_cours, $langSubmit, $langRequiredFields;
        $out = "<div class='right smaller'>$langRequiredFields</div>";
        $out .= "<form method='post' enctype='multipart/form-data' action='" . $_SERVER['SCRIPT_NAME'] . "?course=$code_cours'>
                 <div id='tabs' style='padding-bottom: 10px;'>
                    <ul>
                       <li><a href='#tabs-1'>" . $GLOBALS['langCMeta']['courseGroup'] . "</a></li>
                       <li><a href='#tabs-2'>" . $GLOBALS['langCMeta']['instructorGroup'] . "</a></li>
                       <li><a href='#tabs-3'>" . $GLOBALS['langCMeta']['curriculumGroup'] . "</a></li>
                       <li><a href='#tabs-4'>" . $GLOBALS['langCMeta']['unitsGroup'] . "</a></li>
                    </ul>
                 <div id='tabs-1'>";
        if ($data != null) {
            $this->populate($data);
        }
        $out .= $this->populateForm();
        $out .= "</div>
                 <p class='right'><input type='submit' name='submit' value='$langSubmit'></p>
                 </div>
                 </form>
                 <div class='right smaller'>$langRequiredFields</div>";
        return $out;
    }

    /**
     * Returns an HTML Div for viewing the XML.
     * 
     * @param  array  $data        - array containing data to preload the form with
     * @return string
     */
    public function asDiv($data = null) {
        $out = "<div class='tabs' style='padding-bottom: 10px;'>
                    <ul>
                       <li><a href='#tabs-1'>" . $GLOBALS['langCMeta']['courseGroup'] . "</a></li>
                       <li><a href='#tabs-2'>" . $GLOBALS['langCMeta']['instructorGroup'] . "</a></li>
                       <li><a href='#tabs-3'>" . $GLOBALS['langCMeta']['curriculumGroup'] . "</a></li>
                       <li><a href='#tabs-4'>" . $GLOBALS['langCMeta']['unitsGroup'] . "</a></li>
                    </ul>
                 <div id='tabs-1'>";
        if ($data != null) {
            $this->populate($data);
        }
        $out .= $this->populateDiv();
        $out .= "</div>
                 </div>";
        return $out;
    }

    /**
     * Recursively populate the HTML Form.
     * 
     * @param  string           $parentKey
     * @param  CourseXMLElement $parent
     * @return string
     */
    private function populateForm($parentKey = '', $parent = null) {
        $fullKey = $this->mendFullKey($parentKey);

        $children = $this->children();
        if (count($children) == 0) {
            return $this->appendLeafFormField($fullKey, $parent);
        }

        $out = "";
        foreach ($children as $ele) {
            $out .= $ele->populateForm($fullKey, $this);
        }

        return $out;
    }

    /**
     * Recursively populate the HTML Div.
     * 
     * @param  string $parentKey
     * @return string
     */
    private function populateDiv($parentKey = '') {
        $fullKey = $this->mendFullKey($parentKey);

        $children = $this->children();
        if (count($children) == 0) {
            return $this->appendLeafDivElement($fullKey);
        }

        $out = "";
        foreach ($children as $ele) {
            $out .= $ele->populateDiv($fullKey);
        }

        return $out;
    }

    /**
     * Populate a single simple HTML Form Field (leaf).
     * 
     * @global string           $currentCourseLanguage
     * @param  string           $fullKey
     * @param  CourseXMLElement $parent
     * @return string
     */
    private function appendLeafFormField($fullKey, $parent) {
        global $currentCourseLanguage;

        // init vars
        $keyLbl = (isset($GLOBALS['langCMeta'][$fullKey])) ? $GLOBALS['langCMeta'][$fullKey] : $fullKey;
        $help = (isset($GLOBALS['langCMeta']['help_' . $fullKey])) ? $GLOBALS['langCMeta']['help_' . $fullKey] : '';
        $fullKeyNoLang = $fullKey;
        $sameAsCourseLang = false;
        $lang = '';
        if ($this->getAttribute('lang')) {
            $fullKey .= '_' . $this->getAttribute('lang');
            $lang = ' (' . $GLOBALS['langCMeta'][(string) $this->getAttribute('lang')] . ')';
            if ($this->getAttribute('lang') == langname_to_code($currentCourseLanguage)) {
                $sameAsCourseLang = true;
            } else {
                $help = ''; // in case of multi-lang field, display help text only once (the same as the course lang)
            }
        }

        // proper divs initializations
        $fieldStart = "";
        if (in_array($fullKey, self::$breakAccordionStartFields)) {
            $fieldStart .= "<div class='cmetaaccordion'><h3>" . $GLOBALS['langMore'] . "</h3><div>";
        }
        $cmetalabel = (in_array($fullKey, self::$mandatoryFields) || strpos($fullKey, 'course_unit_') === 0 || strpos($fullKey, 'course_numberOfUnits') === 0) ? 'cmetalabel' : 'cmetalabelinaccordion';
        $fieldStart .= "<div title='$help' class='cmetarow'><span class='$cmetalabel'>";
        if (in_array($fullKeyNoLang, self::$linkedFields) && (!$this->getAttribute('lang') || $sameAsCourseLang)) {
            $fieldStart .= "<a href='" . self::getLinkedValue($fullKey) . "' target='_blank'>" . q($keyLbl . $lang) . "</a>";
        } else {
            $fieldStart .= q($keyLbl . $lang);
        }
        $fieldStart .= ":</span><span class='cmetafield'>";

        $fieldEnd = "</span>";
        if (in_array($fullKey, self::$mandatoryFields)) {
            $fieldEnd .= "<span class='cmetamandatory'>*</span>";
        }
        $fieldEnd .= "</div>";

        // break divs
        if (in_array($fullKey, self::$breakAccordionEndFields)) {
            $fieldEnd .= "</div></div>";
        }
        if (array_key_exists($fullKey, self::$breakFields)) {
            $fieldEnd .= "</div><div id='tabs-" . self::$breakFields[$fullKey] . "'>";
        }

        // hidden/auto-generated fields
        if (in_array($fullKeyNoLang, self::$hiddenFields) && (!$this->getAttribute('lang') || $sameAsCourseLang)) {
            return;
        }

        // boolean fields
        if (in_array($fullKeyNoLang, self::$booleanFields)) {
            $value = (string) $this;
            if (empty($value)) {
                $value = 'false';
            }
            return $fieldStart . selection(array('false' => $GLOBALS['langCMeta']['false'],
                        'true' => $GLOBALS['langCMeta']['true']), $fullKey, $value) . $fieldEnd;
        }

        // enumeration fields
        if (in_array($fullKeyNoLang, self::$enumerationFields)) {
            return $fieldStart . selection(self::getEnumerationValues($fullKey), $fullKey, (string) $this) . $fieldEnd;
        }

        // multiple enumeration fields
        if (in_array($fullKeyNoLang, self::$multiEnumerationFields)) {
            return $fieldStart . multiselection(self::getEnumerationValues($fullKey), $fullKey . '[]', explode(',', (string) $this), 'id="multiselect" multiple="true"') . $fieldEnd;
        }

        // readonly fields
        $readonly = '';
        if (in_array($fullKeyNoLang, self::$readOnlyFields) && (!$this->getAttribute('lang') || $sameAsCourseLang)) {
            $readonly = 'disabled readonly';
        }

        // integer fields
        if (in_array($fullKeyNoLang, self::$integerFields)) {
            $value = (string) $this;
            if (empty($value)) {
                $value = 0;
            }
            return $fieldStart . "<input type='text' size='2' name='" . q($fullKey) . "' value='" . intval($value) . "' $readonly>" . $fieldEnd;
        }

        // textarea fields
        if (in_array($fullKeyNoLang, self::$textareaFields)) {
            return $fieldStart . "<textarea cols='53' rows='2' name='" . q($fullKey) . "' $readonly>" . q((string) $this) . "</textarea>" . $fieldEnd;
        }

        // binary (file-upload) fields
        if (in_array($fullKeyNoLang, self::$binaryFields)) {
            $html = '';
            $is_multiple = (in_array($fullKey, self::$multipleFields)) ? true : false;
            $multiplicity = ($is_multiple) ? '[]' : '';

            if (!$is_multiple) {
                $html .= $fieldStart;
                $value = (string) $this;
                if (!empty($value)) { // image already exists
                    $mime = (string) $this->getAttribute('mime');
                    $html .= "<img id='" . $fullKey . "_image' src='data:" . q($mime) . ";base64," . q($value) . "'/>
                              <img id='" . $fullKey . "_delete' src='" . $GLOBALS['themeimg'] . "/delete.png'/>
                              <input id='" . $fullKey . "_hidden' type='hidden' name='" . q($fullKey) . $multiplicity . "' value='" . q($value) . "'>
                              <input id='" . $fullKey . "_hidden_mime' type='hidden' name='" . q($fullKey) . "_mime" . $multiplicity . "' value='" . q($mime) . "'>
                              </span></div>
                              <div class='cmetarow'><span class='$cmetalabel'></span><span class='cmetafield'>";
                }
                $html .= "<input type='file' size='30' name='" . q($fullKey) . $multiplicity . "'>";
                $html .= $fieldEnd;
            } else {
                // do nothing if field already walked/processed
                $walked = isset(self::$tmpData[$fullKey . '_walked']);
                if (!$walked) {
                    $html .= "<div id='" . $fullKey . "_container'>";
                    $html .= $fieldStart;
                    $name = $this->getName();
                    $cnt = 0;

                    if ($parent !== null && $name !== null) {
                        foreach ($parent->{$name} as $currentField) {
                            $value = (string) $currentField;
                            if (!empty($value)) { // image already exists
                                $mime = (string) $currentField->getAttribute('mime');
                                if ($cnt > 0) {
                                    $html .= "</span></div><div class='cmetarow'><span class='$cmetalabel'></span><span class='cmetafield'>";
                                }
                                $html .= "<img id='" . $fullKey . $cnt . "_image' src='data:" . q($mime) . ";base64," . q($value) . "'/>
                                          <a id='" . $fullKey . $cnt . "_delete' href='javascript:photoDelete(\"#" . $fullKey . $cnt . "\");'>
                                          <img src='" . $GLOBALS['themeimg'] . "/delete.png'/></a>
                                          <input id='" . $fullKey . $cnt . "_hidden' type='hidden' name='" . q($fullKey) . $multiplicity . "' value='" . q($value) . "'>
                                          <input id='" . $fullKey . $cnt . "_hidden_mime' type='hidden' name='" . q($fullKey) . "_mime" . $multiplicity . "' value='" . q($mime) . "'>";
                                $cnt++;
                            }
                        }
                    }

                    if ($cnt == 0) {
                        $html .= "<input type='file' size='30' name='" . q($fullKey) . $multiplicity . "'>";
                    }
                    $html .= $fieldEnd;
                    $html .= "</div>"; // close container
                    // + button
                    $html .= "<div class='cmetarow'><span class='$cmetalabel'></span><span class='cmetafield'>";
                    $html .= "<a id='" . $fullKey . "_add' href='#add'><img src='" . $GLOBALS['themeimg'] . "/add.png' alt='alt'/></a>";
                    $html .= "</span></div>";
                    self::$tmpData[$fullKey . '_walked'] = true;
                }
            }

            return $html;
        }

        // array fields
        if (in_array($fullKeyNoLang, self::$arrayFields)) {
            return $fieldStart . "<input type='text' size='55' name='" . q($fullKey) . "[]' value='" . q((string) $this) . "' $readonly>" . $fieldEnd;
        }

        // all others get a typical input type box
        return $fieldStart . "<input type='text' size='55' name='" . q($fullKey) . "' value='" . q((string) $this) . "' $readonly>" . $fieldEnd;
    }

    /**
     * Populate a single simple HTML Div Element (leaf).
     * 
     * @global string $currentCourseLanguage
     * @param  string $fullKey
     * @return string
     */
    private function appendLeafDivElement($fullKey) {
        global $currentCourseLanguage;

        // init vars
        $keyLbl = (isset($GLOBALS['langCMeta'][$fullKey])) ? $GLOBALS['langCMeta'][$fullKey] : $fullKey;
        $fullKeyNoLang = $fullKey;
        $sameAsCourseLang = false;
        $lang = '';
        if ($this->getAttribute('lang')) {
            $fullKey .= '_' . $this->getAttribute('lang');
            $lang = ' (' . $GLOBALS['langCMeta'][(string) $this->getAttribute('lang')] . ')';
            if ($this->getAttribute('lang') == langname_to_code($currentCourseLanguage)) {
                $sameAsCourseLang = true;
            }
        }

        // proper divs initializations
        $fieldStart = "";
        if (in_array($fullKey, self::$breakAccordionStartFields)) {
            $fieldStart .= "<div class='cmetaaccordion'><h3>" . $GLOBALS['langMore'] . "</h3><div>";
        }
        $cmetalabel = (in_array($fullKey, self::$mandatoryFields) || strpos($fullKey, 'course_unit_') === 0 || strpos($fullKey, 'course_numberOfUnits') === 0) ? 'cmetalabel' : 'cmetalabelinaccordion';
        $fieldStart .= "<div class='cmetarow'><span class='$cmetalabel'>" . q($keyLbl . $lang) . ":</span><span class='cmetafield'>";

        $fieldEnd = "</span></div>";
        if (in_array($fullKey, self::$breakAccordionEndFields)) {
            $fieldEnd .= "</div></div>";
        }
        if (array_key_exists($fullKey, self::$breakFields)) {
            $fieldEnd .= "</div><div id='tabs-" . self::$breakFields[$fullKey] . "'>";
        }

        // hidden/auto-generated fields
        if (in_array($fullKeyNoLang, self::$hiddenFields) && (!$this->getAttribute('lang') || $sameAsCourseLang)) {
            return;
        }

        // fields hidden from anonymous users
        if ((!isset($GLOBALS['code_cours']) || $_SESSION['status'][$GLOBALS['code_cours']] == 0) && in_array($fullKeyNoLang, self::$hiddenFromAnonymousFields)) {
            return;
        }

        // print nothing for empty and non-breaking-necessary fields
        if (!in_array($fullKey, self::$breakAccordionStartFields) &&
                !in_array($fullKey, self::$breakAccordionEndFields) &&
                !array_key_exists($fullKey, self::$breakFields) &&
                strlen((string) $this) <= 0) {
            return;
        }

        // boolean fields
        if (in_array($fullKeyNoLang, self::$booleanFields)) {
            $value = (string) $this;
            if (empty($value)) {
                $value = 'false';
            }
            $valueOut = $GLOBALS['langCMeta'][$value];
            return $fieldStart . $valueOut . $fieldEnd;
        }

        // enumeration and multiple enumeration fields
        if (in_array($fullKeyNoLang, self::$enumerationFields)) {
            $valArr = self::getEnumerationValues($fullKey);
            return $fieldStart . $valArr[(string) $this] . $fieldEnd;
        }

        // multiple enumeration fiels
        if (in_array($fullKeyNoLang, self::$multiEnumerationFields)) {
            $valueOut = '';
            $valArr = self::getEnumerationValues($fullKey);
            $i = 1;
            foreach (explode(',', (string) $this) as $value) {
                if ($i > 1) {
                    $valueOut .= ', ';
                }
                $valueOut .= $valArr[$value];
                $i++;
            }
            return $fieldStart . $valueOut . $fieldEnd;
        }

        // binary (file-upload) fields
        if (in_array($fullKeyNoLang, self::$binaryFields)) {
            $html = $fieldStart;
            $value = (string) $this;
            if (!empty($value)) { // image already exists
                $mime = (string) $this->getAttribute('mime');
                $html .= "<img src='data:" . q($mime) . ";base64," . q($value) . "'/>";
            }
            $html .= $fieldEnd;
            return $html;
        }

        if ($fullKey == 'course_language') {
            return $fieldStart . $GLOBALS['native_language_names_init'][((string) $this)] . $fieldEnd;
        }

        // all others get a typical printout
        return $fieldStart . q((string) $this) . $fieldEnd;
    }

    /**
     * Populate the XML with data.
     * 
     * @param array            $data
     * @param string           $parentKey
     * @param CourseXMLElement $parent
     */
    public function populate(&$data, $parentKey = '', $parent = null) {
        $fullKey = $this->mendFullKey($parentKey);

        $children = $this->children();
        if (count($children) == 0) {
            return $this->populateLeaf($data, $fullKey, $parent);
        }

        foreach ($children as $ele) {
            $ele->populate($data, $fullKey, $this);
        }
    }

    /**
     * Populate a single simple xml node (leaf).
     * 
     * @param array            $data
     * @param string           $fullKey
     * @param CourseXMLElement $parent
     */
    private function populateLeaf(&$data, $fullKey, $parent) {
        $fullKeyNoLang = $fullKey;
        if ($this->getAttribute('lang')) {
            $fullKey .= '_' . $this->getAttribute('lang');
        }

        if (isset($data[$fullKey])) {
            if (!is_array($data[$fullKey])) {
                if (in_array($fullKeyNoLang, self::$integerFields)) {
                    $this->{0} = intval($data[$fullKey]);
                } else {
                    $this->{0} = $data[$fullKey];
                }
                // mime attribute for mime fields 
                if (in_array($fullKeyNoLang, self::$binaryFields)) {
                    $this['mime'] = isset($data[$fullKey . '_mime']) ? $data[$fullKey . '_mime'] : '';
                }
            } else {
                // multiple entities (multiEnum, multiFields and units) use associative indexed arrays
                if (in_array($fullKeyNoLang, self::$multiEnumerationFields)) {
                    // multiEnums are just comma separated
                    $this->{0} = implode(',', $data[$fullKey]);
                } else if (in_array($fullKeyNoLang, self::$multipleFields)) {
                    // multiplicity fields
                    if ($parent !== null) {
                        $name = $this->getName();
                        // calc index to locate the proper child
                        $i = 0;
                        if (isset($data[$fullKey . '_walked'])) {
                            $i = intval($data[$fullKey . '_walked']) + 1;
                        }
                        // this part is walked n independent times, where n = count($data[$fullKey])
                        // for each walking, we have to remember which was the previous index
                        // and assign the next array value to the (next) proper parent element
                        if ($i < count($data[$fullKey])) {
                            if (in_array($fullKeyNoLang, self::$integerFields)) {
                                $parent->{$name}[$i] = intval($data[$fullKey][$i]);
                            } else {
                                $parent->{$name}[$i] = $data[$fullKey][$i];
                            }
                            // mime attribute for mime fields
                            if (in_array($fullKeyNoLang, self::$binaryFields)) {
                                $parent->{$name}[$i]['mime'] = isset($data[$fullKey . '_mime'][$i]) ? $data[$fullKey . '_mime'][$i] : '';
                            }
                            // store index for locating the proper child at the next iteration
                            $data[$fullKey . '_walked'] = $i;
                        }
                    }
                } else { // units
                    $index = intval($this->getAttribute('index')) - 1;
                    if ($index >= 0 && isset($data[$fullKey][$index])) {
                        $this->{0} = $data[$fullKey][$index];
                        unset($this['index']); // remove attribute
                    }
                }
            }
        }
    }

    /**
     * Convert the XML as a flat array (key => value) and do special post-processing.
     * 
     * @param  string $parentKey
     * @return array
     */
    public function asFlatArray() {
        $data = $this->asFlatArrayRec();

        // special post processing for unit properties
        $extra = array();
        $unitsCount = 0;
        foreach ($this->unit as $unit) {
            foreach ($unit->keywords as $keyword) {
                $extra['course_unit_keywords_' . $keyword->getAttribute('lang')][$unitsCount] = (string) $keyword;
            }
            $unitsCount++;
        }

        $ret = array_merge_recursive($data, $extra);
        return $ret;
    }

    /**
     * Convert the XML recursively as a flat array (key => value).
     * 
     * @param  string $parentKey
     * @return array
     */
    private function asFlatArrayRec($parentKey = '') {
        $fullKey = $this->mendFullKey($parentKey);

        $children = $this->children();
        if (count($children) == 0) {
            if ($this->getAttribute('lang')) {
                $fullKey .= '_' . $this->getAttribute('lang');
            }

            $ret = array($fullKey => (string) $this);

            if ($this->getAttribute('mime')) {
                $ret = array_merge_recursive($ret, array($fullKey . '_mime' => (string) $this->getAttribute('mime')));
            }

            return $ret;
        }

        $out = array();
        foreach ($children as $ele) {
            $out = array_merge_recursive($out, $ele->asFlatArrayRec($fullKey));
        }

        return $out;
    }

    /**
     * Adapt the current XML according to the given data array.
     * It ensures the proper number of multiple
     * elements exist in the XML (multiple instructors, units, etc).
     * 
     * @param array $data
     */
    public function adapt($data) {
        global $webDir;

        // adapt to the multiplicity of these fields
        foreach (self::$multipleFields as $field) {
            $dataCount = 0;
            if (isset($data[$field])) {
                $dataCount = count($data[$field]);
            }

            $xmlCount = 0;
            $asarr = $this->asFlatArray();
            if (isset($asarr[$field])) {
                $xmlCount = count($asarr[$field]);
            }

            $parentXPath = self::getMultipleFieldParentXPath($field);
            $fieldName = self::getMultipleFieldName($field);

            if ($dataCount > $xmlCount && $parentXPath !== null && $fieldName !== null) {
                // locate parent node
                $this->registerXPathNamespace('n', self::DEFAULT_NS);
                $parents = $this->xpath($parentXPath);

                // add children to match both counts
                for ($i = 0; $i < $dataCount - $xmlCount; $i++) {
                    $parents[0]->addChild($fieldName, '');
                }
            }
        }

        // adapt for units in data
        $unitsNo = (isset($data['course_numberOfUnits'])) ? intval($data['course_numberOfUnits']) : 0;
        if ($unitsNo > 0) {
            $skeletonU = $webDir . '/modules/course_metadata/skeletonUnit.xml';
            $dom = dom_import_simplexml($this);

            // remove current unit elements
            unset($this->unit);

            for ($i = 1; $i <= $unitsNo; $i++) {
                $unitXML = simplexml_load_file($skeletonU, 'CourseXMLElement');
                $unitXML->setLeafAttribute('index', $i);
                $domU = dom_import_simplexml($unitXML);
                $domUIn = $dom->ownerDocument->importNode($domU, true);
                $dom->appendChild($domUIn);
            }
        }
    }

    /**
     * Array key for iterating over XML, POST or array data.
     * 
     * @param type $parentKey
     * @return string
     */
    private function mendFullKey($parentKey) {
        $fullKey = $this->getName();
        if (!empty($parentKey)) {
            $fullKey = $parentKey . "_" . $fullKey;
        }
        return $fullKey;
    }

    /**
     * Iteratively count all XML elements.
     * 
     * @return int
     */
    public function countAll() {
        $children = $this->children();
        if (count($children) == 0) {
            return 1;
        }

        $sum = 0;
        foreach ($children as $ele) {
            $sum += $ele->countAll();
        }

        return $sum;
    }

    /**
     * Whether the XML contains all mandatory fields or not.
     * 
     * @return boolean
     */
    public function hasMandatoryMetadata() {
        $data = $this->asFlatArray();

        foreach (self::$mandatoryFields as $mfield) {
            if (!isset($data[$mfield]) || empty($data[$mfield])) {
                return false;
            }
        }

        // check mandatory unit fields
        if (!isset($data['course_numberOfUnits']) || !intval($data['course_numberOfUnits']) > 0) {
            return false;
        }
        // check each unit title and description
        for ($i = 0; $i < intval($data['course_numberOfUnits']); $i++) {
            if (!isset($data['course_unit_title_el'][$i]) || empty($data['course_unit_title_el'][$i])) {
                return false;
            }
            if (!isset($data['course_unit_description_el'][$i]) || empty($data['course_unit_description_el'][$i])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Initialize an XML structure for a specific course.
     * 
     * @param  int    $courseId
     * @param  string $courseCode
     * @return CourseXMLElement
     */
    public static function init($courseId, $courseCode) {
        global $mysqlMainDb;
        $skeleton = self::getSkeletonPath();
        $xmlFile = self::getCourseXMLPath($courseCode);
        $data = self::getAutogenData($courseId); // preload xml with auto-generated data
        // course-based adaptation
        list($dnum) = mysql_fetch_row(db_query("select count(id) from `$mysqlMainDb`.document where course_id = " . intval($courseId)));
        list($vnum) = mysql_fetch_row(db_query("select count(id) from `$courseCode`.video"));
        list($vlnum) = mysql_fetch_row(db_query("select count(id) from `$courseCode`.videolinks"));
        if ($dnum + $vnum + $vlnum < 1) {
            self::$hiddenFields[] = 'course_confirmVideolectures';
            $data['course_confirmVideolectures'] = 'false';
        }
        $data['course_videolectures'] = $vnum + $vlnum;

        $skeletonXML = simplexml_load_file($skeleton, 'CourseXMLElement');
        $skeletonXML->adapt($data);
        $skeletonXML->populate($data);

        if (file_exists($xmlFile)) {
            $xml = simplexml_load_file($xmlFile, 'CourseXMLElement');
            if (!$xml) { // fallback if xml is broken
                return $skeletonXML;
            } else { // xml is valid, merge/replace autogen data and current xml data
                $new_data = array_replace_recursive($xml->asFlatArray(), $data);
                $data = $new_data;
            }
        } else { // fallback if starting fresh
            return $skeletonXML;
        }

        $xml->adapt($data);
        $xml->populate($data);

        // load xml from skeleton if it has more fields (useful for incremental updates)
        if ($skeletonXML->countAll() > $xml->countAll()) {
            $skd = $xml->asFlatArray();
            $skeletonXML->populate($skd);
            return $skeletonXML;
        }

        return $xml;
    }

    /**
     * Initialize an XML structure for a specific course only if the metadatafile is present
     * and without using database queries. This is a lighter version than init, but it 
     * should be used for read-only operations. Write operations rely on init's adaption
     * and population with DB data as well.
     * 
     * @param  string $courseCode
     * @return CourseXMLElement or false on error
     */
    public static function initFromFile($courseCode) {
        $xmlFile = self::getCourseXMLPath($courseCode);

        if (file_exists($xmlFile)) {
            $xml = simplexml_load_file($xmlFile, 'CourseXMLElement');
            if (!$xml) {
                return false;
            } else {
                return $xml;
            }
        } else {
            return false;
        }
    }

    /**
     * Refresh/update the auto-generated values for a specific course.
     * 
     * @param int    $courseId
     * @param string $courseCode
     */
    public static function refreshCourse($courseId, $courseCode) {
        if (get_config('course_metadata')) {
            $xml = self::init($courseId, $courseCode);
            self::save($courseId, $courseCode, $xml);
        }
    }

    /**
     * Save the XML structure for a specific course.
     * 
     * @param int              $courseId
     * @param string           $courseCode
     * @param CourseXMLElement $xml
     */
    public static function save($courseId, $courseCode, $xml) {
        global $mysqlMainDb;
        
        $doc = new DOMDocument('1.0');
        $doc->loadXML($xml->asXML(), LIBXML_NONET | LIBXML_DTDLOAD | LIBXML_DTDATTR);
        $doc->formatOutput = true;
        $doc->save(self::getCourseXMLPath($courseCode));
        
        $is_certified = 1;
        $level = '';
        if ($xml->confirmAPlusLevel == 'true') {
            $level = $GLOBALS['langOpenCoursesAPlusLevel'];
        } else if ($xml->confirmALevel == 'true') {
            $level = $GLOBALS['langOpenCoursesALevel'];
        } else if ($xml->confirmAMinusLevel == 'true') {
            $level = $GLOBALS['langOpenCoursesAMinusLevel'];
        } else {
            $is_certified = 0;
        }
        $firstCreateDate = null;
        $ts = strtotime($xml->firstCreateDate);
        if ($ts > 0) {
            $firstCreateDate = date('Y-m-d H:i:s', $ts);
        }
        
        // insert or update oai_record
        $exists = db_query_get_single_value("SELECT 1 FROM `$mysqlMainDb`.oai_record WHERE course_id = " . $courseId);
        if ($exists) {
            $deleted = ($is_certified) ? 0 : 1;
            db_query("UPDATE `$mysqlMainDb`.oai_record SET
                `oai_identifier` = " . quote($courseId) /* TODO: improve/replace */ . ",
                `datestamp` = NOW(),
                `deleted` = " . $deleted . ",
                `dc_title` = " . quote(self::serialize($xml->title)) . ",
                `dc_description` = " . quote(self::serialize($xml->description)) . ",
                `dc_syllabus` = " . quote(self::serialize($xml->contents)) . ",
                `dc_subject` = " . quote(self::serialize($xml->thematic)) . ",
                `dc_objectives` = " . quote(self::serialize($xml->objectives)) . ",
                `dc_level` = " . quote(self::makeMultiLang($xml->level)) . ",
                `dc_prerequisites` = " . quote(self::serialize($xml->prerequisites)) . ",
                `dc_instructor` = " . quote(self::serialize($xml->instructor->fullName)) /* TODO: multiplicity */ . ",
                `dc_department` = " . quote(self::serialize($xml->department)) . ",
                `dc_institution` = " . quote(self::serialize($xml->institution)) . ",
                `dc_coursephoto` = " . quote($xml->coursePhoto) . ",
                `dc_instructorphoto` = " . quote($xml->instructor->photo) /* TODO: multiplicity */ . ",
                `dc_url` = " . quote($xml->url) . ",
                `dc_language` = " . quote(self::serialize($xml->language)) . ",
                `dc_date` = " . quote($firstCreateDate) . ",
                `dc_format` = " . quote($level) . ",
                `dc_rights` = " . quote(self::serialize($xml->license)) . ",
                `dc_videolectures` = " . quote($xml->videolectures) . "
                WHERE course_id = " . intval($courseId));
        } else {
            if ($is_certified) {
                db_query("INSERT INTO `$mysqlMainDb`.oai_record SET
                    `course_id` = " . intval($courseId) /* TODO: improve/replace */ . ",
                    `oai_identifier` = " . quote($courseId) . ",
                    `datestamp` = NOW(),
                    `deleted` = 0,
                    `dc_title` = " . quote(self::serialize($xml->title)) . ",
                    `dc_description` = " . quote(self::serialize($xml->description)) . ",
                    `dc_syllabus` = " . quote(self::serialize($xml->contents)) . ",
                    `dc_subject` = " . quote(self::serialize($xml->thematic)) . ",
                    `dc_objectives` = " . quote(self::serialize($xml->objectives)) . ",
                    `dc_level` = " . quote(self::makeMultiLang($xml->level)) . ",
                    `dc_prerequisites` = " . quote(self::serialize($xml->prerequisites)) . ",
                    `dc_instructor` = " . quote(self::serialize($xml->instructor->fullName)) /* TODO: multiplicity */ . ",
                    `dc_department` = " . quote(self::serialize($xml->department)) . ",
                    `dc_institution` = " . quote(self::serialize($xml->institution)) . ",
                    `dc_coursephoto` = " . quote($xml->coursePhoto) . ",
                    `dc_instructorphoto` = " . quote($xml->instructor->photo) /* TODO: multiplicity */ . ",
                    `dc_url` = " . quote($xml->url) . ",
                    `dc_language` = " . quote(self::serialize($xml->language)) . ",
                    `dc_date` = " . quote($firstCreateDate) . ",
                    `dc_format` = " . quote($level) . ",
                    `dc_rights` = " . quote(self::serialize($xml->license)) . ",
                    `dc_videolectures` = " . quote($xml->videolectures));
            }
        }
    }
    
    /**
     * Serialize a XML element.
     * 
     * @param CourseXMLElement $ele
     * return string
     */
    public static function serialize($ele) {
        if (count($ele) == 1) {
            return (string) $ele;
        } else if (count($ele) > 1) {
            $arr = array();
            foreach ($ele as $innerele) {
                $lang = $innerele->getAttribute('lang');
                if ($lang !== false) {
                    $arr[(string) $lang] = (string) $innerele;
                } else {
                    $arr[] = (string) $innerele;
                }
            }
            return serialize($arr);
        } else {
            return null;
        }
    }
    
    /**
     * Turn a non-multiLang field into multiLang.
     * 
     * @global string $currentCourseLanguage
     * @global string $webDir
     * @global string $siteName
     * @global string $Institution
     * @global string $InstitutionUrl
     * @param  CourseXMLElement $ele
     * @return array
     */
    public static function makeMultiLang($ele) {
        global $currentCourseLanguage, $webDir, $siteName, $Institution, $InstitutionUrl;
        $clang = langname_to_code($currentCourseLanguage);
        $arr = array();
        $arr[$clang] = $GLOBALS['langCMeta'][(string) $ele];
        $revert = false;
        if ($clang != 'en') {
            include("${webDir}modules/lang/english/common.inc.php");
            include("${webDir}modules/lang/english/messages.inc.php");
            $arr['en'] = $langCMeta[(string) $ele];
            $revert = true;
        }
        if ($clang != 'el') {
            include("${webDir}modules/lang/greek/common.inc.php");
            include("${webDir}modules/lang/greek/messages.inc.php");
            $arr['en'] = $langCMeta[(string) $ele];
            $revert = true;
        }
        if ($revert) { // revert messages back to current language
            include("${webDir}modules/lang/" . $currentCourseLanguage . "/common.inc.php");
            include("${webDir}modules/lang/" . $currentCourseLanguage . "/messages.inc.php");
        }
        return serialize($arr);
    }

    /**
     * Auto-Generate Data for a specific course.
     * 
     * @global string $urlServer
     * @param  int    $courseId
     * @return array
     */
    public static function getAutogenData($courseId) {
        global $urlServer, $mysqlMainDb, $license, $webDir, $siteName, $Institution, $InstitutionUrl;
        $data = array();

        $res1 = db_query("SELECT * FROM `$mysqlMainDb`.cours WHERE cours_id = " . intval($courseId));
        $course = mysql_fetch_assoc($res1);
        if (!$course) {
            return array();
        }

        // course language
        $clang = langname_to_code($course['languageCourse']);
        $data['course_language_' . $clang] = $GLOBALS['langNameOfLang'][$course['languageCourse']];
        if ($clang != 'en') {
            $data['course_language_en'] = ucfirst($course['languageCourse']);
        }
        if ($clang != 'el') {
            include("${webDir}modules/lang/greek/common.inc.php");
            include("${webDir}modules/lang/greek/messages.inc.php");
            $data['course_language_el'] = $GLOBALS['langNameOfLang'][$course['languageCourse']];
            // revert messages back to current language
            include("${webDir}modules/lang/" . $course['languageCourse'] . "/common.inc.php");
            include("${webDir}modules/lang/" . $course['languageCourse'] . "/messages.inc.php");
        }
        
        $data['course_url'] = $urlServer . 'courses/' . $course['code'];
        $data['course_instructor_fullName_' . $clang] = $course['titulaires']; // TODO: multiplicity
        $data['course_title_' . $clang] = $course['intitule'];
        $data['course_keywords_' . $clang] = $course['course_keywords'];
        
        // course license
        if ($course['course_license']) {
            $data['course_license_' . $clang] = $license[$course['course_license']]['title'];
            $revert = false;
            if ($clang != 'en') {
                include("${webDir}modules/lang/english/common.inc.php");
                include("${webDir}modules/lang/english/messages.inc.php");
                include("${webDir}include/license_info.php");
                $data['course_license_en'] = $license[$course['course_license']]['title'];
                $revert = true;
            }
            if ($clang != 'el') {
                include("${webDir}modules/lang/greek/common.inc.php");
                include("${webDir}modules/lang/greek/messages.inc.php");
                include("${webDir}include/license_info.php");
                $data['course_license_el'] = $license[$course['course_license']]['title'];
                $revert = true;
            }
            if ($revert) { // revert messages back to current language
                include("${webDir}modules/lang/" . $course['languageCourse'] . "/common.inc.php");
                include("${webDir}modules/lang/" . $course['languageCourse'] . "/messages.inc.php");
                include("${webDir}include/license_info.php");
            }
        } else {
            $data['course_license_' . $clang] = '';
            if ($clang != 'en') {
                $data['course_license_en'] = '';
            }
            if ($clang != 'el') {
                $data['course_license_el'] = '';
            }
        }
        
        // first creation date
        $ts = strtotime($course['first_create']);
        if ($ts > 0) {
            $data['course_firstCreateDate'] = date("Y-m-d\TH:i:sP", $ts);
        }
        
        // course review data
        $res2 = db_query("SELECT * FROM `$mysqlMainDb`.course_review WHERE course_id = " . intval($courseId));
        $review = mysql_fetch_assoc($res2);
        if ($review !== false) {
            $ts = strtotime($review['last_review']);
            if ($ts > 0) {
                $data['course_lastLevelConfirmation'] = date("Y-m-d\TH:i:sP", $ts);
            }
            $level = intval($review['level']);
            if ($level >= self::A_MINUS_LEVEL) {
                $data['course_confirmAMinusLevel'] = 'true';
            }
            if ($level >= self::A_LEVEL) {
                $data['course_confirmALevel'] = 'true';
            }
            if ($level >= self::A_PLUS_LEVEL) {
                $data['course_confirmAPlusLevel'] = 'true';
            }
        }

        // course description types
        $desctypes = array(
            'course_contents_' . $clang => 'syllabus',
            'course_objectives_' . $clang => 'objectives',
            'course_literature_' . $clang => 'literature',
            'course_teachingMethod_' . $clang => 'teaching_method',
            'course_assessmentMethod_' . $clang => 'assessment_method',
            'course_prerequisites_' . $clang => 'prerequisites');
        foreach ($desctypes as $xmlkey => $desctype) {
            $resDesc = db_query("SELECT cd.comments
                    FROM `$mysqlMainDb`.course_description cd
                    LEFT JOIN `$mysqlMainDb`.course_description_type t on (t.id = cd.type)
                    WHERE cd.course_id = " . intval($courseId) . " AND t.`" . $desctype . "` = 1
                    ORDER BY cd.order");
            $commDesc = '';
            $i = 0;
            while ($row = mysql_fetch_array($resDesc)) {
                if ($i > 0) {
                    $commDesc .= ' ';
                }
                $commDesc .= strip_tags($row['comments']);
                $i++;
            }
            if (strlen($commDesc) > 0) {
                $data[$xmlkey] = $commDesc;
            }
        }

        // turn visible units to associative array
        $res3 = db_query("SELECT id, title, comments
                           FROM `$mysqlMainDb`.course_units
                          WHERE visibility = 'v'
                            AND course_id = " . intval($courseId));
        $unitsCount = 0;
        while ($row = mysql_fetch_assoc($res3)) {
            $data['course_unit_title_' . $clang][$unitsCount] = $row['title'];
            $data['course_unit_description_' . $clang][$unitsCount] = strip_tags($row['comments']);
            $unitsCount++; // also serves as array index, starting from 0
        }
        $data['course_numberOfUnits'] = $unitsCount;

        return $data;
    }

    /**
     * Returns the path of the skeleton XML file.
     * 
     * @global string $webDir
     * @return string
     */
    public static function getSkeletonPath() {
        global $webDir;
        return $webDir . '/modules/course_metadata/skeleton.xml';
    }

    /**
     * Returns the path of a specific course's XML file.
     * 
     * @global string $webDir
     * @param  string $courseCode
     * @return string
     */
    public static function getCourseXMLPath($courseCode) {
        global $webDir;
        return $webDir . '/courses/' . $courseCode . '/courseMetadata.xml';
    }

    /**
     * Returns whether a course is OpenCourses Certified or not.
     * 
     * @param  string  $courseCode
     * @return boolean
     */
    public static function isCertified($courseCode) {
        if (!get_config('course_metadata')) {
            return false;
        }

        $xml = self::initFromFile($courseCode);
        if ($xml !== false) {
            $xmlData = $xml->asFlatArray();
            if ((isset($xmlData['course_confirmAMinusLevel']) && $xmlData['course_confirmAMinusLevel'] == 'true') ||
                    (isset($xmlData['course_confirmALevel']) && $xmlData['course_confirmALevel'] == 'true') ||
                    (isset($xmlData['course_confirmAPlusLevel']) && $xmlData['course_confirmAPlusLevel'] == 'true')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the Certification Level language string by matching a key-value 
     * (i.e. int field from DB table course_review).
     * 
     * @param  int    $key
     * @return string
     */
    public static function getLevel($key) {
        if (!get_config('course_metadata')) {
            return null;
        }

        $valArr = array(
            self::A_MINUS_LEVEL => $GLOBALS['langOpenCoursesAMinusLevel'],
            self::A_LEVEL => $GLOBALS['langOpenCoursesALevel'],
            self::A_PLUS_LEVEL => $GLOBALS['langOpenCoursesAPlusLevel']
        );

        if (isset($valArr[$key])) {
            return $valArr[$key];
        } else {
            return null;
        }
    }

    /**
     * Enumeration values for HTML Form fields.
     * @param  string $key
     * @return array
     */
    public static function getEnumerationValues($key) {
        $valArr = array(
            'course_level' => array('undergraduate' => $GLOBALS['langCMeta']['undergraduate'],
                'graduate' => $GLOBALS['langCMeta']['graduate'],
                'doctoral' => $GLOBALS['langCMeta']['doctoral']),
            'course_curriculumLevel' => array('undergraduate' => $GLOBALS['langCMeta']['undergraduate'],
                'graduate' => $GLOBALS['langCMeta']['graduate'],
                'doctoral' => $GLOBALS['langCMeta']['doctoral']),
            'course_yearOfStudy' => array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6'),
            'course_semester' => array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6',
                '7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12'),
            'course_type' => array('compulsory' => $GLOBALS['langCMeta']['compulsory'],
                'optional' => $GLOBALS['langCMeta']['optional']),
            'course_format' => array('slides' => $GLOBALS['langCMeta']['slides'],
                'notes' => $GLOBALS['langCMeta']['notes'],
                'video lectures' => $GLOBALS['langCMeta']['video lectures'],
                'podcasts' => $GLOBALS['langCMeta']['podcasts'],
                'audio material' => $GLOBALS['langCMeta']['audio material'],
                'multimedia material' => $GLOBALS['langCMeta']['multimedia material'],
                'interactive exercises' => $GLOBALS['langCMeta']['interactive exercises'])
        );

        if (isset($valArr[$key])) {
            return $valArr[$key];
        } else {
            return array();
        }
    }

    /**
     * Fields that should be hidden from the HTML Form.
     * @var array
     */
    public static $hiddenFields = array(
        'course_unit_material_notes', 'course_unit_material_slides',
        'course_unit_material_exercises', 'course_unit_material_multimedia_title',
        'course_unit_material_multimedia_speaker', 'course_unit_material_multimedia_subject',
        'course_unit_material_multimedia_description', 'course_unit_material_multimedia_keywords',
        'course_unit_material_multimedia_url', 'course_unit_material_other',
        'course_unit_material_digital_url', 'course_unit_material_digital_library',
        'course_confirmAMinusLevel', 'course_confirmALevel', 'course_confirmAPlusLevel',
        'course_lastLevelConfirmation', 'course_firstCreateDate', 'course_videolectures'
    );

    /**
     * Fields that should be hidden from anonymous users.
     * @var array
     */
    public static $hiddenFromAnonymousFields = array(
        'course_credits', 'course_structure', 'course_assessmentMethod', 'course_assignments'
    );

    /**
     * Fields that should be readonly in the HTML Form.
     * @var array
     */
    public static $readOnlyFields = array(
        'course_language', 'course_instructor_fullName', 'course_title',
        'course_url', 'course_keywords', 'course_numberOfUnits',
        'course_unit_title', 'course_unit_description', 'course_license',
        'course_contents', 'course_objectives', 'course_literature',
        'course_teachingMethod', 'course_assessmentMethod',
        'course_prerequisites'
    );

    /**
     * Boolean/dropdown HTML Form fields.
     * @var array
     */
    public static $booleanFields = array(
        'course_coTeaching', 'course_coTeachingColleagueOpensCourse',
        'course_coTeachingAutonomousDepartment', 'course_confirmCurriculum',
        'course_confirmVideolectures'
    );

    /**
     * Integer HTML Form fields.
     * @var array
     */
    public static $integerFields = array(
        'course_credithours', 'course_coTeachingDepartmentCreditHours',
        'course_credits', 'course_numberOfUnits'
    );

    /**
     * Enumeration HTML Form fields.
     * @var array
     */
    public static $enumerationFields = array(
        'course_level', 'course_curriculumLevel', 'course_yearOfStudy',
        'course_semester', 'course_type'
    );

    /**
     * Multiple enumartion HTML Form fields.
     * @var array
     */
    public static $multiEnumerationFields = array(
        'course_format'
    );

    /**
     * Fields with multiplicity.
     * @var array
     */
    public static $multipleFields = array(
        'course_instructor_photo'
    );

    /**
     * XPaths to locate the parents of multiplicity fields.
     * 
     * @param  string      $field
     * @return string|null
     */
    public static function getMultipleFieldParentXPath($field) {
        $valArr = array(
            'course_instructor_photo' => '/n:course/n:instructor'
        );

        if (isset($valArr[$field])) {
            return $valArr[$field];
        } else {
            return null;
        }
    }

    /**
     * Provide the field name for multiplicity fields. 
     * 
     * @param  string      $field
     * @return string|null
     */
    public static function getMultipleFieldName($field) {
        $valArr = array(
            'course_instructor_photo' => 'photo'
        );

        if (isset($valArr[$field])) {
            return $valArr[$field];
        } else {
            return null;
        }
    }

    /**
     * Textarea HTML Form fields.
     * @var array
     */
    public static $textareaFields = array(
        'course_instructor_moreInformation', 'course_instructor_cv',
        'course_targetGroup', 'course_description',
        'course_contents', 'course_objectives',
        'course_contentDevelopment', 'course_featuredBooks', 'course_structure',
        'course_teachingMethod', 'course_assessmentMethod',
        'course_prerequisites', 'course_literature',
        'course_recommendedComponents', 'course_assignments',
        'course_requirements', 'course_remarks', 'course_acknowledgments',
        'course_thematic', 'course_institutionDescription',
        'course_curriculumDescription', 'course_outcomes',
        'course_curriculumTargetGroup'
    );

    /**
     * Binary HTML Form fields.
     * @var array
     */
    public static $binaryFields = array(
        'course_instructor_photo', 'course_coursePhoto'
    );

    /**
     * UI Tabs Break points.
     * @var array
     */
    public static $breakFields = array(
        'course_acknowledgments_en' => '2',
        'course_confirmCurriculum' => '3',
        'course_kalliposURL' => '4'
    );

    /**
     * UI Accordion Start Break points.
     * @var array
     */
    public static $breakAccordionStartFields = array(
        'course_prerequisites_en',
        'course_instructor_moreInformation_el',
        'course_sector_el'
    );

    /**
     * UI Accordion End Break points.
     * @var array
     */
    public static $breakAccordionEndFields = array(
        'course_acknowledgments_en',
        'course_confirmCurriculum',
        'course_kalliposURL'
    );

    /**
     * Mandatory HTML Form fields.
     * @var array
     */
    public static $mandatoryFields = array(
        'course_instructor_firstName_el', 'course_instructor_firstName_en',
        'course_instructor_lastName_el', 'course_instructor_lastName_en',
        'course_instructor_fullName_el', 'course_instructor_fullName_en',
        'course_title_el', 'course_title_en',
        'course_level', 'course_code_el',
        'course_description_el', 'course_description_en',
        'course_contents_el',
        'course_objectives_el',
        'course_keywords_el', 'course_keywords_en',
        'course_prerequisites_el',
        'course_literature_el',
        'course_thematic_el', 'course_thematic_en',
        'course_institution_el', 'course_institution_en',
        'course_institutionDescription_el', 'course_institutionDescription_en',
        'course_department_el', 'course_department_en',
        'course_curriculumTitle_el', 'course_curriculumTitle_en',
        'course_curriculumDescription_el', 'course_curriculumDescription_en',
        'course_outcomes_el', 'course_outcomes_en',
        'course_curriculumKeywords_el', 'course_curriculumKeywords_en',
        'course_curriculumLevel',
        'course_yearOfStudy', 'course_semester', 'course_credithours',
        'course_type', 'course_credits'
    );

    /**
     * Linked HTML Form labels.
     * @var array 
     */
    public static $linkedFields = array(
        'course_title', 'course_instructor_fullName',
        'course_language', 'course_keywords',
        'course_unit_title', 'course_unit_description',
        'course_numberOfUnits', 'course_license',
        'course_contents', 'course_objectives', 'course_literature',
        'course_teachingMethod', 'course_assessmentMethod',
        'course_prerequisites'
    );

    /**
     * Array HTML Form fields.
     * @var array
     */
    public static $arrayFields = array(
        'course_unit_keywords'
    );

    /**
     * Link value for HTML Form labels.
     * 
     * @param  string $key
     * @return string
     */
    public static function getLinkedValue($key) {
        global $urlServer, $code_cours, $currentCourseLanguage;

        $infocours = $urlServer . 'modules/course_info/infocours.php?course=' . $code_cours;
        $coursedesc = $urlServer . 'modules/course_description/index.php?course=' . $code_cours;
        $coursehome = $urlServer . 'courses/' . $code_cours . '/index.php';
        $clang = langname_to_code($currentCourseLanguage);

        $valArr = array(
            'course_title_' . $clang => $infocours,
            'course_instructor_fullName_' . $clang => $infocours,
            'course_language' => $infocours,
            'course_keywords_' . $clang => $infocours,
            'course_unit_title_' . $clang => $coursehome,
            'course_unit_description_' . $clang => $coursehome,
            'course_numberOfUnits' => $coursehome,
            'course_license' => $infocours,
            'course_contents_' . $clang => $coursedesc,
            'course_objectives_' . $clang => $coursedesc,
            'course_literature_' . $clang => $coursedesc,
            'course_teachingMethod_' . $clang => $coursedesc,
            'course_assessmentMethod_' . $clang => $coursedesc,
            'course_prerequisites_' . $clang => $coursedesc
        );

        if (isset($valArr[$key])) {
            return $valArr[$key];
        } else {
            return null;
        }
    }

    /**
     * Debug the contents of an array.
     * 
     * @param  array $xmlArr
     * @return string        - HTML preformatted output
     */
    public static function debugArray($xmlArr) {
        $out = "<pre>";
        ob_start();
        $out .= print_r($xmlArr, true);
        $out .= ob_get_contents();
        ob_end_clean();
        $out .= "</pre>";
        return $out;
    }

}
