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

class CourseXMLElement extends SimpleXMLElement {
    
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
        if (isset($attributes[$name]))
            return $attributes[$name];
        else
            return false;
    }
    
    /**
     * Recursively set a leaf element's attribute.
     * 
     * @param string $name
     * @param string $value
     */
    public function setLeafAttribute($name, $value) {
        $children = $this->children();
        if (count($children) == 0)
            $this->addAttribute($name, $value);
        
        foreach ($children as $ele)
            $ele->setLeafAttribute($name, $value);
    }
    
    /**
     * Returns an HTML Form for editing the XML.
     * 
     * @global string $course_code
     * @global string $langSubmit
     * @param  array  $data        - array containing data to preload the form with
     * @return string
     */
    public function asForm($data = null) {
        global $course_code, $langSubmit;
        $out = "";
        $out .= "<form method='post' action='" . $_SERVER['SCRIPT_NAME'] . "?course=$course_code'>
                 <fieldset>
                 <legend>langCourseInfo</legend>
                 <table class='tbl' width='100%'>";
        if ($data != null)
            $this->populate ($data);
        $out .= $this->populateForm();
        $out .= "</table>
                 </fieldset>
                 <p class='right'><input type='submit' name='submit' value='$langSubmit' /></p>
                 </form>";
        return $out;
    }
    
    /**
     * Recursively populate the HTML Form.
     * 
     * @param  string $parentKey
     * @return string
     */
    private function populateForm($parentKey = '') {
        $fullKey = $this->mendFullKey($parentKey);
        
        $children = $this->children();
        if (count($children) == 0)
            return $this->appendLeafFormField($fullKey);
        
        $out = "";
        foreach ($children as $ele)
            $out .= $ele->populateForm($fullKey);
        
        return $out;
    }
    
    /**
     * Populate a single simple HTML Form Field (leaf).
     * 
     * @global string $currentCourseLanguage
     * @param  string $fullKey
     * @return string
     */
    private function appendLeafFormField($fullKey) {
        global $currentCourseLanguage;
        
        // init vars
        $keyLbl = (isset($GLOBALS['langCMeta'][$fullKey])) ? $GLOBALS['langCMeta'][$fullKey] : $fullKey;
        $help = (isset($GLOBALS['langCMeta']['help_' . $fullKey])) ? $GLOBALS['langCMeta']['help_' . $fullKey] : '';
        $fullKeyNoLang = $fullKey;
        $sameAsCourseLang = false;
        $lang = '';
        if ($this->getAttribute('lang')) {
            $fullKey .= '_' . $this->getAttribute('lang');
            $lang = ' (' . $this->getAttribute('lang') .')';
            if ($this->getAttribute('lang') == $currentCourseLanguage)
                $sameAsCourseLang = true;
        }
        
        // hidden/auto-generated fields
        if (in_array($fullKeyNoLang, self::$hiddenFields) && (!$this->getAttribute('lang') || $sameAsCourseLang))
            return;
        
        // readonly fields
        $readonly = '';
        if (in_array($fullKeyNoLang, self::$readOnlyFields) && (!$this->getAttribute('lang') || $sameAsCourseLang))
            $readonly = 'disabled="true" readonly';
        
        // TODO: types
        // if (lookup(key) == numeric/shorterInputField)
        // if (lookup(key) == largeString/textarea)
        // if (lookup(key) == boolean/dropdown)
        // if (lookup(key) == enumeration/dropdown)
        
        return "<tr>
                <th rowspan='2'>" . $keyLbl . $lang . ":</th>
                <td><input type='text' size='60' name='". $fullKey ."' value='". (string) $this ."' $readonly/></td>
                </tr><tr><td>$help</td></tr>";
    }
    
    /**
     * Populate the XML with data.
     * 
     * @param  array $data
     * @param  string $parentKey
     */
    public function populate($data, $parentKey = '') {
        $fullKey = $this->mendFullKey($parentKey);
        
        $children = $this->children();
        if (count($children) == 0)
            return $this->populateLeaf($data, $fullKey);
        
        foreach ($children as $ele)
            $ele->populate($data, $fullKey);
    }
    
    /**
     * Populate a single simple xml node (leaf).
     * 
     * @param array  $data
     * @param string $fullKey
     */
    private function populateLeaf($data, $fullKey) {
        if ($this->getAttribute('lang'))
            $fullKey .= '_' . $this->getAttribute('lang');
        
        if (isset($data[$fullKey])) {
            if (!is_array($data[$fullKey]))
                $this->{0} = $data[$fullKey];
            else { // multiple entities use associative indexed arrays
                $index = intval($this->getAttribute('index'));
                if ($index && isset($data[$fullKey][$index])) {
                    $this->{0} = $data[$fullKey][$index];
                    unset($this['index']); // remove attribute
                }
            }
        }
    }
    
    /**
     * Convert the XML as a flat array (key => value).
     * 
     * @param  string $parentKey
     * @return array
     */
    public function asFlatArray($parentKey = '') {
        $fullKey = $this->mendFullKey($parentKey);
        
        $children = $this->children();
        if (count($children) == 0) {
            if ($this->getAttribute('lang'))
                $fullKey .= '_' . $this->getAttribute('lang');
            
            return array($fullKey => (string) $this);
        }
        
        $out = array();
        foreach ($children as $ele)
            $out = array_merge($out, $ele->asFlatArray($fullKey));
        
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
        
        // adapt for units in data
        $unitsNo = (isset($data['course_numberOfUnits'])) ? intval($data['course_numberOfUnits']) : 0;
        if ( $unitsNo > 0 ) {
            $skeletonU = $webDir . '/modules/course_metadata/skeletonUnit.xml';
            $dom = dom_import_simplexml($this);
            
            // remove current unit elements
            unset($this->unit);
            
            for ($i = 1; $i <= $unitsNo; $i++) {
                $unitXML = simplexml_load_file ($skeletonU, 'CourseXMLElement');
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
        if (!empty($parentKey))
            $fullKey = $parentKey . "_" . $fullKey;
        return $fullKey;
    }
    
    /**
     * Iteratively count all XML elements.
     * 
     * @return int
     */
    public function countAll() {
        $children = $this->children();
        if (count($children) == 0)
            return 1;
        
        $sum = 0;
        foreach ($children as $ele)
            $sum += $ele->countAll();
        
        return $sum;
    }
    
    public static function init($courseId, $courseCode) {
        $skeleton = self::getSkeletonPath();
        $xmlFile  = self::getCourseXMLPath($courseCode);
        $data     = self::getAutogenData($courseId); // preload xml with auto-generated data
        
        $skeletonXML = simplexml_load_file($skeleton, 'CourseXMLElement');
        $skeletonXML->adapt($data);
        $skeletonXML->populate($data);

        if (file_exists($xmlFile)) {
            $xml = simplexml_load_file($xmlFile, 'CourseXMLElement');
            if (!$xml) // fallback if xml is broken
                return $skeletonXML;
        } else // fallback if starting fresh
            return $skeletonXML;

        $xml->adapt($data);
        $xml->populate($data);

        // load xml from skeleton if it has more fields (useful for incremental updates)
        if ($skeletonXML->countAll() > $xml->countAll()) {
            $skeletonXML->populate($xml->asFlatArray());
            return $skeletonXML;
        }

        return $xml;
    }
    
    public static function refreshCourse($courseId, $courseCode) {
        if (get_config('course_metadata')) {
            $xml = self::init($courseId, $courseCode);
            self::save($courseCode, $xml);
        }
    }
    
    public static function save($courseCode, $xml) {
        $doc = new DOMDocument('1.0');
        $doc->loadXML( $xml->asXML() );
        $doc->formatOutput = true;
        $doc->save(self::getCourseXMLPath($courseCode));
    }
    
    public static function getAutogenData($courseId) {
        global $urlServer;
        $data = array();
    
        $res1 = db_query("SELECT * FROM course WHERE id = " . intval($courseId));
        $course = mysql_fetch_assoc($res1);
        if (!$course)
            return array();

        $clang = $course['lang'];
        $data['course_language'] = $clang;
        $data['course_url'] = $urlServer . 'courses/'. $course['code'];
        $data['course_instructor_fullName_' . $clang] = $course['prof_names'];
        $data['course_title_' . $clang] = $course['title'];
        $data['course_keywords_' . $clang] = $course['keywords'];

        // turn visible units to associative array
        $res2 = db_query("SELECT id, title, comments
                           FROM course_units
                          WHERE visible > 0
                            AND course_id = " . intval($courseId));
        $unitsCount = 0;
        while($row = mysql_fetch_assoc($res2)) {
            $unitsCount++; // also serves as array index
            $data['course_unit_title_' . $clang][$unitsCount] = $row['title'];
            $data['course_unit_description_' . $clang][$unitsCount] = $row['comments'];
        }    
        $data['course_numberOfUnits'] = $unitsCount;

        // TODO: course description
        // TODO: course objectives

        return $data;
    }
    
    public static function getSkeletonPath() {
        global $webDir;
        return $webDir . '/modules/course_metadata/skeleton.xml';
    }
    
    public static  function getCourseXMLPath($courseCode) {
        global $webDir;
        return $webDir . '/courses/' . $courseCode . '/courseMetadata.xml';
    }
    
    /**
     * Fields that should be hidden from the HTML Form.
     * @var array
     */
    public static $hiddenFields = array(
        'course_unit_keywords', 
        'course_unit_material_notes', 'course_unit_material_slides', 
        'course_unit_material_exercises', 'course_unit_material_multimedia_title', 
        'course_unit_material_multimedia_speaker', 'course_unit_material_multimedia_subject', 
        'course_unit_material_multimedia_description', 'course_unit_material_multimedia_keywords', 
        'course_unit_material_multimedia_url', 'course_unit_material_other', 
        'course_unit_material_digital_url', 'course_unit_material_digital_library'
    );
    
    /**
     * Fields that should readonly in the HTML Form.
     * @var array
     */
    public static $readOnlyFields = array(
        'course_language', 'course_instructor_fullName', 'course_title',
        'course_url', 'course_keywords', 'course_numberOfUnits', 
        'course_unit_title', 'course_unit_description'
    );
}