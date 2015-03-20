<?php

/* ========================================================================
 * Open eClass 2.10
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

require_once('CourseXMLConfig.php');

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
        $helptitle = (isset($GLOBALS['langCMeta']['help_' . $fullKey])) ? "title='" . $GLOBALS['langCMeta']['help_' . $fullKey] . "'" : '';
        $fullKeyNoLang = $fullKey;
        $sameAsCourseLang = false;
        $lang = '';
        if ($this->getAttribute('lang')) {
            $fullKey .= '_' . $this->getAttribute('lang');
            $lang = ' (' . $GLOBALS['langCMeta'][(string) $this->getAttribute('lang')] . ')';
            if ($this->getAttribute('lang') == langname_to_code($currentCourseLanguage)) {
                $sameAsCourseLang = true;
            } else {
                $helptitle = ''; // in case of multi-lang field, display help text only once (the same as the course lang)
            }
        }

        // proper divs initializations
        $fieldStart = "";
        if (in_array($fullKey, CourseXMLConfig::$breakAccordionStartFields)) {
            $fieldStart .= "<div class='cmetaaccordion'><h3>" . $GLOBALS['langMore'] . "</h3><div>";
        }
        $cmetalabel = (in_array($fullKey, CourseXMLConfig::$mandatoryFields) || strpos($fullKey, 'course_unit_') === 0 || strpos($fullKey, 'course_numberOfUnits') === 0 || in_array($fullKey, CourseXMLConfig::$overrideClass)) ? 'cmetalabel' : 'cmetalabelinaccordion';
        $fieldStart .= "<div $helptitle class='cmetarow'><span class='$cmetalabel'>";
        if (in_array($fullKeyNoLang, CourseXMLConfig::$linkedFields) && (!$this->getAttribute('lang') || $sameAsCourseLang)) {
            $fieldStart .= "<a href='" . CourseXMLConfig::getLinkedValue($fullKey) . "' target='_blank'>" . q($keyLbl . $lang) . "</a>";
        } else {
            $fieldStart .= q($keyLbl . $lang);
        }
        $fieldStart .= ":</span><span class='cmetafield'>";

        $fieldEnd = "</span>";
        if (in_array($fullKey, CourseXMLConfig::$mandatoryFields)) {
            $fieldEnd .= "<span class='cmetamandatory'>*</span>";
        }
        $fieldEnd .= "</div>";

        // break divs
        if (in_array($fullKey, CourseXMLConfig::$breakAccordionEndFields)) {
            $fieldEnd .= "</div></div>";
        }
        
        // inject
        if (in_array($fullKey, CourseXMLConfig::$injectFields)) {
            $fieldEnd .= CourseXMLConfig::getInjectValue($fullKey);
        }
        
        // break tabs
        if (array_key_exists($fullKey, CourseXMLConfig::$breakFields)) {
            $fieldEnd .= "</div><div id='tabs-" . CourseXMLConfig::$breakFields[$fullKey] . "'>";
        }
        
        // hidden/auto-generated fields. NOTE: if we need to uncomment the following, introduce hiddenMultiLangFields
        if (in_array($fullKeyNoLang, CourseXMLConfig::$hiddenFields) /*&& (!$this->getAttribute('lang') || $sameAsCourseLang)*/) {
            return;
        }

        // boolean fields
        if (in_array($fullKeyNoLang, CourseXMLConfig::$booleanFields)) {
            $value = (string) $this;
            if (empty($value)) {
                $value = 'false';
            }
            return $fieldStart . selection(array('false' => $GLOBALS['langCMeta']['false'],
                        'true' => $GLOBALS['langCMeta']['true']), $fullKey, $value) . $fieldEnd;
        }

        // enumeration fields
        if (in_array($fullKeyNoLang, CourseXMLConfig::$enumerationFields)) {
            return $fieldStart . selection(CourseXMLConfig::getEnumerationValues($fullKey), $fullKey, (string) $this, "id='" . $fullKeyNoLang . "'") . $fieldEnd;
        }

        // multiple enumeration fields
        if (in_array($fullKeyNoLang, CourseXMLConfig::$multiEnumerationFields)) {
            return $fieldStart . multiselection(CourseXMLConfig::getEnumerationValues($fullKey), $fullKey . '[]', explode(',', (string) $this), 'id="multiselect" multiple="true"') . $fieldEnd;
        }

        // readonly fields
        $readonly = '';
        if (in_array($fullKey, CourseXMLConfig::$readOnlyMultiLangFields)) {
            $readonly = 'disabled readonly';
        }
        if (in_array($fullKeyNoLang, CourseXMLConfig::$readOnlyFields) && (!$this->getAttribute('lang') || $sameAsCourseLang)) {
            $readonly = 'disabled readonly';
        }

        // integer fields
        if (in_array($fullKeyNoLang, CourseXMLConfig::$integerFields)) {
            $value = (string) $this;
            if (empty($value)) {
                $value = 0;
            }
            return $fieldStart . "<input type='text' size='2' name='" . q($fullKey) . "' value='" . intval($value) . "' $readonly>" . $fieldEnd;
        }

        // textarea fields
        if (in_array($fullKeyNoLang, CourseXMLConfig::$textareaFields)) {
            return $fieldStart . "<textarea cols='53' rows='2' name='" . q($fullKey) . "' $readonly>" . q((string) $this) . "</textarea>" . $fieldEnd;
        }

        // binary (file-upload) fields
        if (in_array($fullKeyNoLang, CourseXMLConfig::$binaryFields)) {
            $html = '';
            $is_multiple = in_array($fullKey, CourseXMLConfig::$multipleFields);
            $is_arrayField = in_array($fullKeyNoLang, CourseXMLConfig::$arrayFields);
            $multiplicity = ($is_multiple || $is_arrayField) ? '[]' : '';

            if (!$is_multiple) {
                $html .= $fieldStart;
                $value = (string) $this;
                $idorclass = ($is_arrayField) ? 'class' : 'id';
                if (!empty($value)) { // image already exists
                    $mime = (string) $this->getAttribute('mime');
                    $html .= "<img " . $idorclass . "='" . $fullKey . "_image' src='data:" . q($mime) . ";base64," . q($value) . "'/>
                              <img " . $idorclass . "='" . $fullKey . "_delete' src='" . $GLOBALS['themeimg'] . "/delete.png'/>
                              <input " . $idorclass . "='" . $fullKey . "_hidden' type='hidden' name='" . q($fullKey) . $multiplicity . "' value='" . q($value) . "'>
                              <input " . $idorclass . "='" . $fullKey . "_hidden_mime' type='hidden' name='" . q($fullKey) . "_mime" . $multiplicity . "' value='" . q($mime) . "'>
                              </span></div>
                              <div class='cmetarow'><span class='$cmetalabel'></span><span class='cmetafield'>";
                } else {
                    // add as empty array, in order to keep correspondence
                    $html .= "<input " . $idorclass . "='" . $fullKey . "_hidden' type='hidden' name='" . q($fullKey) . $multiplicity . "'>
                              <input " . $idorclass . "='" . $fullKey . "_hidden_mime' type='hidden' name='" . q($fullKey) . "_mime" . $multiplicity . "'>";
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
        if (in_array($fullKeyNoLang, CourseXMLConfig::$arrayFields) || in_array($fullKeyNoLang, CourseXMLConfig::$unitFields)) {
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
        if (in_array($fullKey, CourseXMLConfig::$breakAccordionStartFields)) {
            $fieldStart .= "<div class='cmetaaccordion'><h3>" . $GLOBALS['langMore'] . "</h3><div>";
        }
        $cmetalabel = (in_array($fullKey, CourseXMLConfig::$mandatoryFields) || strpos($fullKey, 'course_unit_') === 0 || strpos($fullKey, 'course_numberOfUnits') === 0) ? 'cmetalabel' : 'cmetalabelinaccordion';
        $fieldStart .= "<div class='cmetarow'><span class='$cmetalabel'>" . q($keyLbl . $lang) . ":</span><span class='cmetafield'>";

        $fieldEnd = "</span></div>";
        if (in_array($fullKey, CourseXMLConfig::$breakAccordionEndFields)) {
            $fieldEnd .= "</div></div>";
        }
        if (array_key_exists($fullKey, CourseXMLConfig::$breakFields)) {
            $fieldEnd .= "</div><div id='tabs-" . CourseXMLConfig::$breakFields[$fullKey] . "'>";
        }

        // hidden/auto-generated fields
        if (in_array($fullKeyNoLang, CourseXMLConfig::$hiddenFields) && (!$this->getAttribute('lang') || $sameAsCourseLang)) {
            return;
        }

        // fields hidden from anonymous users
        if ((!isset($GLOBALS['code_cours']) || $_SESSION['status'][$GLOBALS['code_cours']] == 0) && in_array($fullKeyNoLang, CourseXMLConfig::$hiddenFromAnonymousFields)) {
            return;
        }

        // print nothing for empty and non-breaking-necessary fields
        if (!in_array($fullKey, CourseXMLConfig::$breakAccordionStartFields) &&
                !in_array($fullKey, CourseXMLConfig::$breakAccordionEndFields) &&
                !array_key_exists($fullKey, CourseXMLConfig::$breakFields) &&
                strlen((string) $this) <= 0) {
            return;
        }

        // boolean fields
        if (in_array($fullKeyNoLang, CourseXMLConfig::$booleanFields)) {
            $value = (string) $this;
            if (empty($value)) {
                $value = 'false';
            }
            $valueOut = $GLOBALS['langCMeta'][$value];
            return $fieldStart . $valueOut . $fieldEnd;
        }

        // enumeration and multiple enumeration fields
        if (in_array($fullKeyNoLang, CourseXMLConfig::$enumerationFields)) {
            $valArr = CourseXMLConfig::getEnumerationValues($fullKey);
            $value = "";
            if (!isset($valArr[(string) $this]) && isset($GLOBALS['langCMeta'][(string) $this])) {
                $value = $GLOBALS['langCMeta'][(string) $this];
            } else {
                $value = $valArr[(string) $this];
            }
            return $fieldStart . $value . $fieldEnd;
        }

        // multiple enumeration fiels
        if (in_array($fullKeyNoLang, CourseXMLConfig::$multiEnumerationFields)) {
            $valueOut = '';
            $valArr = CourseXMLConfig::getEnumerationValues($fullKey);
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
        if (in_array($fullKeyNoLang, CourseXMLConfig::$binaryFields)) {
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
                if (in_array($fullKeyNoLang, CourseXMLConfig::$integerFields)) {
                    $this->{0} = intval($data[$fullKey]);
                } else {
                    $this->{0} = $data[$fullKey];
                }
                // mime attribute for mime fields 
                if (in_array($fullKeyNoLang, CourseXMLConfig::$binaryFields)) {
                    $this['mime'] = isset($data[$fullKey . '_mime']) ? $data[$fullKey . '_mime'] : '';
                }
            } else {
                // multiple entities (multiEnum, multiFields and units) use associative indexed arrays
                if (in_array($fullKeyNoLang, CourseXMLConfig::$multiEnumerationFields)) {
                    // multiEnums are just comma separated
                    $this->{0} = implode(',', $data[$fullKey]);
                } else if (in_array($fullKeyNoLang, CourseXMLConfig::$multipleFields)) {
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
                            if (in_array($fullKeyNoLang, CourseXMLConfig::$integerFields)) {
                                $parent->{$name}[$i] = intval($data[$fullKey][$i]);
                            } else {
                                $parent->{$name}[$i] = $data[$fullKey][$i];
                            }
                            // mime attribute for mime fields
                            if (in_array($fullKeyNoLang, CourseXMLConfig::$binaryFields)) {
                                $parent->{$name}[$i]['mime'] = isset($data[$fullKey . '_mime'][$i]) ? $data[$fullKey . '_mime'][$i] : '';
                            }
                            // store index for locating the proper child at the next iteration
                            $data[$fullKey . '_walked'] = $i;
                        }
                    }
                } else if (in_array($fullKeyNoLang, CourseXMLConfig::$arrayFields)) {
                    if ($parent !== null) {
                        $name = $this->getName();
                        // calc index to locate the proper child
                        $j = 0;
                        if (isset($data[$fullKey . '_walked'])) {
                            $j = intval($data[$fullKey . '_walked']) + 1;
                        }
                        // this part is walked n independent times, where n = count($data[$fullKey])
                        // for each walking, we have to remember which was the previous index
                        // and assign the next array value to the (next) proper parent element
                        if ($j < count($data[$fullKey])) {
                            if (in_array($fullKeyNoLang, CourseXMLConfig::$integerFields)) {
                                $this->{0} = intval($data[$fullKey][$j]);
                            } else {
                                $this->{0} = $data[$fullKey][$j];
                            }
                            // mime attribute for mime fields 
                            if (in_array($fullKeyNoLang, CourseXMLConfig::$binaryFields)) {
                                $this['mime'] = isset($data[$fullKey . '_mime']) ? $data[$fullKey . '_mime'][$j] : '';
                            }
                            // store index for locating the proper child at the next iteration
                            $data[$fullKey . '_walked'] = $j;
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
        foreach (CourseXMLConfig::$multipleFields as $field) {
            $dataCount = 0;
            if (isset($data[$field])) {
                $dataCount = count($data[$field]);
            }

            $xmlCount = 0;
            $asarr = $this->asFlatArray();
            if (isset($asarr[$field])) {
                $xmlCount = count($asarr[$field]);
            }

            $parentXPath = CourseXMLConfig::getMultipleFieldParentXPath($field);
            $fieldName = CourseXMLConfig::getMultipleFieldName($field);

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
        
        // adapt for instructors
        $xmlInstCnt = count($this->instructor);
        $datInstCnt = 0;
        // count filled data
        if (isset($data['course_instructor_lastName_el']) && is_array($data['course_instructor_lastName_el'])) {
            foreach($data['course_instructor_lastName_el'] as $sampleLast) {
                if (!empty($sampleLast)) {
                    $datInstCnt++;
                }
            }
        }
        if ($datInstCnt > $xmlInstCnt) {
            $diff = $datInstCnt - $xmlInstCnt;
            $dom = dom_import_simplexml($this)->ownerDocument;
            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('n', self::DEFAULT_NS);
            $domCoTeach = $xpath->query('/n:course/n:coTeaching')->item(0);
            $domI = $xpath->query('/n:course/n:instructor')->item(0);
            
            for ($i = 1; $i <= $diff; $i++) {
                $domCoTeach->parentNode->insertBefore($domI->cloneNode(true), $domCoTeach);
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

        foreach (CourseXMLConfig::$mandatoryFields as $mfield) {
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
     * @param  int     $courseId
     * @param  string  $courseCode
     * @param  boolean $forceUpdate
     * @return CourseXMLElement
     */
    public static function init($courseId, $courseCode, $forceUpdate = false) {
        global $mysqlMainDb;
        $skeleton = self::getSkeletonPath();
        $xmlFile = CourseXMLConfig::getCourseXMLPath($courseCode);
        $data = self::getAutogenData($courseId); // preload xml with auto-generated data
        // course-based adaptation
        list($dnum) = mysql_fetch_row(db_query("select count(id) from `$mysqlMainDb`.document where course_id = " . intval($courseId)));
        list($vnum) = mysql_fetch_row(db_query("select count(id) from `$courseCode`.video"));
        list($vlnum) = mysql_fetch_row(db_query("select count(id) from `$courseCode`.videolinks"));
        if ($dnum + $vnum + $vlnum < 1) {
            CourseXMLConfig::$hiddenFields[] = 'course_confirmVideolectures';
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
        if (($skeletonXML->countAll() > $xml->countAll()) || $forceUpdate) {
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
        $xmlFile = CourseXMLConfig::getCourseXMLPath($courseCode);

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
     * @param int     $courseId
     * @param string  $courseCode
     * @param boolean $forceUpdate
     */
    public static function refreshCourse($courseId, $courseCode, $forceUpdate = false) {
        if (get_config('course_metadata')) {
            $xml = self::init($courseId, $courseCode, $forceUpdate);
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
        
        // pre-save operations
        foreach ($xml->instructor as $instructor) {
            $instrFirst = array();
            $instrLast = array();
            foreach ($instructor->firstName as $fname) {
                $fnameLang = (string) $fname->getAttribute('lang');
                $instrFirst[$fnameLang] = (string) $fname;
            }
            foreach ($instructor->lastName as $lname) {
                $lnameLang = (string) $lname->getAttribute('lang');
                $instrLast[$lnameLang] = (string) $lname;
            }
            foreach ($instructor->fullName as $name) {
                $nameLang = (string) $name->getAttribute('lang');
                $name->{0} = $instrFirst[$nameLang] . " " . $instrLast[$nameLang];
            }
        }
        
        $doc = new DOMDocument('1.0');
        $doc->loadXML($xml->asXML(), LIBXML_NONET | LIBXML_DTDLOAD | LIBXML_DTDATTR);
        $doc->formatOutput = true;
        $doc->save(CourseXMLConfig::getCourseXMLPath($courseCode));
        
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
            $firstCreateDate = quote(date('Y-m-d H:i:s', $ts));
        } else {
            $firstCreateDate = quote(gmdate('Y-m-d H:i:s'));
        }
        $nowdatestamp = quote(gmdate('Y-m-d H:i:s'));
        
        // insert or update oai_record
        $exists = db_query_get_single_value("SELECT 1 FROM `$mysqlMainDb`.oai_record WHERE course_id = " . $courseId);
        if ($exists) {
            $deleted = ($is_certified) ? 0 : 1;
            db_query("UPDATE `$mysqlMainDb`.oai_record SET
                `oai_identifier` = " . quote("oai:" . $_SERVER['SERVER_NAME'] . ":" . $courseId) . ",
                `datestamp` = " . $nowdatestamp . ",
                `deleted` = " . $deleted . ",
                `dc_title` = " . quote(self::serialize($xml->title)) . ",
                `dc_description` = " . quote(self::serialize($xml->description)) . ",
                `dc_syllabus` = " . quote(self::serialize($xml->contents)) . ",
                `dc_subject` = " . quote(self::makeMultiLang($xml->thematic)) . ",
                `dc_subsubject` = " . quote(self::makeMultiLang($xml->subthematic)) . ",
                `dc_objectives` = " . quote(self::serialize($xml->objectives)) . ",
                `dc_level` = " . quote(self::makeMultiLang($xml->level)) . ",
                `dc_prerequisites` = " . quote(self::serialize($xml->prerequisites)) . ",
                `dc_instructor` = " . quote(self::serializeMulti($xml->instructor, "fullName")) . ",
                `dc_department` = " . quote(self::serialize($xml->department)) . ",
                `dc_institution` = " . quote(self::makeMultiLang($xml->institution)) . ",
                `dc_coursephoto` = " . quote($xml->coursePhoto) . ",
                `dc_coursephotomime` = " . quote($xml->coursePhoto['mime']) . ",
                `dc_instructorphoto` = " . quote(self::serializeMulti($xml->instructor, "photo")) . ",
                `dc_instructorphotomime` = " . quote(self::serializeAttr($xml->instructor, "photo", "mime")) . ",
                `dc_url` = " . quote($xml->url) . ",
                `dc_language` = " . quote(self::serialize($xml->language)) . ",
                `dc_date` = " . $firstCreateDate . ",
                `dc_format` = " . quote($level) . ",
                `dc_rights` = " . quote(self::serialize($xml->license)) . ",
                `dc_videolectures` = " . quote($xml->videolectures) . ",
                `dc_code` = " . quote($xml->code) . ",
                `dc_keywords` = " . quote(self::serialize($xml->keywords)) . ",
                `dc_contentdevelopment` = " . quote(self::serialize($xml->contentDevelopment)) . ",
                `dc_formattypes` = " . quote($xml->format) . ",
                `dc_recommendedcomponents` = " . quote(self::serialize($xml->recommendedComponents)) . ",
                `dc_assignments` = " . quote(self::serialize($xml->assignments)) . ",
                `dc_requirements` = " . quote(self::serialize($xml->requirements)) . ",
                `dc_remarks` = " . quote(self::serialize($xml->remarks)) . ",
                `dc_acknowledgments` = " . quote(self::serialize($xml->acknowledgments)) . ",
                `dc_coteaching` = " . quote($xml->coTeaching) . ",
                `dc_coteachingcolleagueopenscourse` = " . quote($xml->coTeachingColleagueOpensCourse) . ",
                `dc_coteachingautonomousdepartment` = " . quote($xml->coTeachingAutonomousDepartment) . ",
                `dc_coteachingdepartmentcredithours` = " . quote($xml->coTeachingDepartmentCreditHours) . ",
                `dc_yearofstudy` = " . quote($xml->yearOfStudy) . ",
                `dc_semester` = " . quote($xml->semester) . ",
                `dc_coursetype` = " . quote($xml->type) . ",
                `dc_credithours` = " . quote($xml->credithours) . ",
                `dc_credits` = " . quote($xml->credits) . ",
                `dc_institutiondescription` = " . quote(self::serialize($xml->institutionDescription)) . ",
                `dc_curriculumtitle` = " . quote(self::serialize($xml->curriculumTitle)) . ",
                `dc_curriculumdescription` = " . quote(self::serialize($xml->curriculumDescription)) . ",
                `dc_outcomes` = " . quote(self::serialize($xml->outcomes)) . ",
                `dc_curriculumkeywords` = " . quote(self::serialize($xml->curriculumKeywords)) . ",
                `dc_sector` = " . quote(self::serialize($xml->sector)) . ",
                `dc_targetgroup` = " . quote(self::serialize($xml->targetGroup)) . ",
                `dc_curriculumtargetgroup` = " . quote(self::serialize($xml->curriculumTargetGroup)) . ",
                `dc_featuredbooks` = " . quote(self::serialize($xml->featuredBooks)) . ",
                `dc_structure` = " . quote(self::serialize($xml->structure)) . ",
                `dc_teachingmethod` = " . quote(self::serialize($xml->teachingMethod)) . ",
                `dc_assessmentmethod` = " . quote(self::serialize($xml->assessmentMethod)) . ",
                `dc_eudoxuscode` = " . quote($xml->eudoxusCode) . ",
                `dc_eudoxusurl` = " . quote($xml->eudoxusURL) . ",
                `dc_kalliposurl` = " . quote($xml->kalliposURL) . ",
                `dc_numberofunits` = " . quote($xml->numberOfUnits) . ",
                `dc_unittitle` = " . quote(self::serializeMulti($xml->unit, "title")) . ",
                `dc_unitdescription` = " . quote(self::serializeMulti($xml->unit, "description")) . ",
                `dc_unitkeywords` = " . quote(self::serializeMulti($xml->unit, "keywords")) . "
                WHERE course_id = " . intval($courseId));
        } else {
            if ($is_certified) {
                db_query("INSERT INTO `$mysqlMainDb`.oai_record SET
                    `course_id` = " . intval($courseId) . ",
                    `oai_identifier` = " . quote("oai:" . $_SERVER['SERVER_NAME'] . ":" . $courseId) . ",
                    `datestamp` = " . $nowdatestamp . ",
                    `deleted` = 0,
                    `dc_title` = " . quote(self::serialize($xml->title)) . ",
                    `dc_description` = " . quote(self::serialize($xml->description)) . ",
                    `dc_syllabus` = " . quote(self::serialize($xml->contents)) . ",
                    `dc_subject` = " . quote(self::makeMultiLang($xml->thematic)) . ",
                    `dc_subsubject` = " . quote(self::makeMultiLang($xml->subthematic)) . ",
                    `dc_objectives` = " . quote(self::serialize($xml->objectives)) . ",
                    `dc_level` = " . quote(self::makeMultiLang($xml->level)) . ",
                    `dc_prerequisites` = " . quote(self::serialize($xml->prerequisites)) . ",
                    `dc_instructor` = " . quote(self::serializeMulti($xml->instructor, "fullName")) . ",
                    `dc_department` = " . quote(self::serialize($xml->department)) . ",
                    `dc_institution` = " . quote(self::makeMultiLang($xml->institution)) . ",
                    `dc_coursephoto` = " . quote($xml->coursePhoto) . ",
                    `dc_coursephotomime` = " . quote($xml->coursePhoto['mime']) . ",
                    `dc_instructorphoto` = " . quote(self::serializeMulti($xml->instructor, "photo")) . ",
                    `dc_instructorphotomime` = " . quote(self::serializeAttr($xml->instructor, "photo", "mime")) . ",
                    `dc_url` = " . quote($xml->url) . ",
                    `dc_language` = " . quote(self::serialize($xml->language)) . ",
                    `dc_date` = " . $firstCreateDate . ",
                    `dc_format` = " . quote($level) . ",
                    `dc_rights` = " . quote(self::serialize($xml->license)) . ",
                    `dc_videolectures` = " . quote($xml->videolectures)) . ",
                    `dc_code` = " . quote($xml->code) . ",
                    `dc_keywords` = " . quote(self::serialize($xml->keywords)) . ",
                    `dc_contentdevelopment` = " . quote(self::serialize($xml->contentDevelopment)) . ",
                    `dc_formattypes` = " . quote($xml->format) . ",
                    `dc_recommendedcomponents` = " . quote(self::serialize($xml->recommendedComponents)) . ",
                    `dc_assignments` = " . quote(self::serialize($xml->assignments)) . ",
                    `dc_requirements` = " . quote(self::serialize($xml->requirements)) . ",
                    `dc_remarks` = " . quote(self::serialize($xml->remarks)) . ",
                    `dc_acknowledgments` = " . quote(self::serialize($xml->acknowledgments)) . ",
                    `dc_coteaching` = " . quote($xml->coTeaching) . ",
                    `dc_coteachingcolleagueopenscourse` = " . quote($xml->coTeachingColleagueOpensCourse) . ",
                    `dc_coteachingautonomousdepartment` = " . quote($xml->coTeachingAutonomousDepartment) . ",
                    `dc_coteachingdepartmentcredithours` = " . quote($xml->coTeachingDepartmentCreditHours) . ",
                    `dc_yearofstudy` = " . quote($xml->yearOfStudy) . ",
                    `dc_semester` = " . quote($xml->semester) . ",
                    `dc_coursetype` = " . quote($xml->type) . ",
                    `dc_credithours` = " . quote($xml->credithours) . ",
                    `dc_credits` = " . quote($xml->credits) . ",
                    `dc_institutiondescription` = " . quote(self::serialize($xml->institutionDescription)) . ",
                    `dc_curriculumtitle` = " . quote(self::serialize($xml->curriculumTitle)) . ",
                    `dc_curriculumdescription` = " . quote(self::serialize($xml->curriculumDescription)) . ",
                    `dc_outcomes` = " . quote(self::serialize($xml->outcomes)) . ",
                    `dc_curriculumkeywords` = " . quote(self::serialize($xml->curriculumKeywords)) . ",
                    `dc_sector` = " . quote(self::serialize($xml->sector)) . ",
                    `dc_targetgroup` = " . quote(self::serialize($xml->targetGroup)) . ",
                    `dc_curriculumtargetgroup` = " . quote(self::serialize($xml->curriculumTargetGroup)) . ",
                    `dc_featuredbooks` = " . quote(self::serialize($xml->featuredBooks)) . ",
                    `dc_structure` = " . quote(self::serialize($xml->structure)) . ",
                    `dc_teachingmethod` = " . quote(self::serialize($xml->teachingMethod)) . ",
                    `dc_assessmentmethod` = " . quote(self::serialize($xml->assessmentMethod)) . ",
                    `dc_eudoxuscode` = " . quote($xml->eudoxusCode) . ",
                    `dc_eudoxusurl` = " . quote($xml->eudoxusURL) . ",
                    `dc_kalliposurl` = " . quote($xml->kalliposURL) . ",
                    `dc_numberofunits` = " . quote($xml->numberOfUnits) . ",
                    `dc_unittitle` = " . quote(self::serializeMulti($xml->unit, "title")) . ",
                    `dc_unitdescription` = " . quote(self::serializeMulti($xml->unit, "description")) . ",
                    `dc_unitkeywords` = " . quote(self::serializeMulti($xml->unit, "keywords"));
            }
        }
    }
    
    /**
     * Prepare a XML element as an array for serialization, handle multi-lang and multiplicity as needed.
     * 
     * @param  CourseXMLElement $ele
     * @return array
     */
    private static function prepareArrayForSerialization($ele) {
        $arr = array();
        foreach ($ele as $innerele) {
            $lang = $innerele->getAttribute('lang');
            if ($lang !== false) {
                $arr[(string) $lang] = (string) $innerele;
            } else {
                $arr[] = (string) $innerele;
            }
        }
        return $arr;
    }
    
    /**
     * Serialize a XML element.
     * 
     * @param CourseXMLElement $ele
     * return string
     */
    private static function serialize($ele) {
        if (count($ele) == 1) {
            return (string) $ele;
        } else if (count($ele) > 1) {
            return base64_encode(serialize(self::prepareArrayForSerialization($ele)));
        } else {
            return null;
        }
    }
    
    /**
     * Serialize a multiple XML element
     * 
     * @param  CourseXMLElement $parent
     * @param  string           $childName
     * @return string
     */
    private static function serializeMulti($parent, $childName) {
        if (count($parent) == 1) {
            return self::serialize($parent->{$childName});
        } else if (count($parent) > 1) {
            $arr = array();
            foreach ($parent as $child) {
                if (!empty($child->{$childName})) {
                    $arr[] = self::prepareArrayForSerialization($child->{$childName});
                }
            }
            return base64_encode(serialize($arr));
        } else {
            return null;
        }
    }
    
    /**
     * Serialize a multiple XML element attribute
     * 
     * @param  CourseXMLElement $parent
     * @param  string           $childName
     * @return string
     */
    private static function serializeAttr($parent, $childName, $attrName) {
        if (count($parent) == 1) {
            return self::serialize((string) $parent->{$childName}[$attrName]);
        } else if (count($parent) > 1) {
            $arr = array();
            foreach ($parent as $child) {
                if (!empty($child->{$childName})) {
                    $arr[] = (string) $child->{$childName}[$attrName];
                }
            }
            return base64_encode(serialize($arr));
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
    private static function makeMultiLang($ele) {
        global $currentCourseLanguage, $webDir, $siteName, $Institution, $InstitutionUrl;
        if (empty($currentCourseLanguage)) {
            $currentCourseLanguage = 'greek';
        }
        $clang = langname_to_code($currentCourseLanguage);
        $arr = array();
        $key = (string) $ele;
        if (!isset($GLOBALS['langCMeta'][$key])) {
            if ($ele->getName() === 'institution') {
                $key = 'otherinst';
            }
            if ($ele->getName() === 'thematic') {
                $key = 'othersubj';
            }
            if ($ele->getName() === 'subthematic') {
                $key = 'othersubsubj';
            }
        }
        $arr[$clang] = $GLOBALS['langCMeta'][$key];
        $revert = false;
        if ($clang != 'en') {
            include("${webDir}modules/lang/english/common.inc.php");
            include("${webDir}modules/lang/english/messages.inc.php");
            $arr['en'] = $langCMeta[$key];
            $revert = true;
        }
        if ($clang != 'el') {
            include("${webDir}modules/lang/greek/common.inc.php");
            include("${webDir}modules/lang/greek/messages.inc.php");
            $arr['el'] = $langCMeta[$key];
            $revert = true;
        }
        if ($revert) { // revert messages back to current language
            include("${webDir}modules/lang/" . $currentCourseLanguage . "/common.inc.php");
            include("${webDir}modules/lang/" . $currentCourseLanguage . "/messages.inc.php");
        }
        return base64_encode(serialize($arr));
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
            $data['course_language_el'] = $langNameOfLang[$course['languageCourse']]; // do not use GLOBALS here as it will not work
            // revert messages back to current language
            include("${webDir}modules/lang/" . $course['languageCourse'] . "/common.inc.php");
            include("${webDir}modules/lang/" . $course['languageCourse'] . "/messages.inc.php");
        }
        
        $data['course_url'] = $urlServer . 'courses/' . $course['code'];
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
            'course_literature_' . $clang => 'bibliography',
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
