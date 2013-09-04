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
     * @global string $code_cours
     * @global string $langSubmit
     * @param  array  $data        - array containing data to preload the form with
     * @return string
     */
    public function asForm($data = null) {
        global $code_cours, $langSubmit;
        $out = "";
        $out .= "<form method='post' enctype='multipart/form-data' action='" . $_SERVER['SCRIPT_NAME'] . "?course=$code_cours'>
                 <div id='tabs'>
                    <ul>
                       <li><a href='#tabs-1'>" . $GLOBALS['langCMeta']['courseGroup'] . "</a></li>
                       <li><a href='#tabs-2'>" . $GLOBALS['langCMeta']['instructorGroup'] . "</a></li>
                       <li><a href='#tabs-3'>" . $GLOBALS['langCMeta']['curriculumGroup'] . "</a></li>
                       <li><a href='#tabs-4'>" . $GLOBALS['langCMeta']['unitsGroup'] . "</a></li>
                    </ul>
                 <div id='tabs-1'>
                 <table class='tbl' width='100%'>";
        if ($data != null)
            $this->populate($data);
        $out .= $this->populateForm();
        $out .= "</table>
                 </div>
                 <p class='right'><input type='submit' name='submit' value='$langSubmit'></p>
                 </div>
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
            $lang = ' (' . $GLOBALS['langCMeta'][(string)$this->getAttribute('lang')] .')';
            if ($this->getAttribute('lang') == langname_to_code($currentCourseLanguage))
                $sameAsCourseLang = true;
            else
                $help = ''; // in case of multi-lang field, display help text only once (the same as the course lang)
        }
        $fieldStart = "<tr><th style='background-color: transparent'>". q($keyLbl . $lang) .":</th><td rowspan='2'>";
        $fieldEnd = "</td></tr><tr><td style='font-size: 10px;'>". $help ."</td></tr><tr><td></td></tr>";
        if (array_key_exists($fullKey, self::$breakFields))
            $fieldEnd .= "</table></div><div id='tabs-". self::$breakFields[$fullKey] ."'><table class='tbl' width='100%'>";
        
        // hidden/auto-generated fields
        if (in_array($fullKeyNoLang, self::$hiddenFields) && (!$this->getAttribute('lang') || $sameAsCourseLang))
            return;
        
        // boolean fields
        if (in_array($fullKeyNoLang, self::$booleanFields)) {
            $value = (string) $this;
            if (empty($value))
                $value = 'false';
            return $fieldStart . selection(array('false' => $GLOBALS['langCMeta']['false'], 
                                                 'true'  => $GLOBALS['langCMeta']['true']), $fullKey, $value) . $fieldEnd;
        }
        
        // enumeration fields
        if (in_array($fullKeyNoLang, self::$enumerationFields))
            return $fieldStart . selection(self::getEnumerationValues($fullKey), $fullKey, (string) $this) . $fieldEnd;

        // multiple enumeration fields
        if (in_array($fullKeyNoLang, self::$multiEnumerationFields))
            return $fieldStart . multiselection(self::getEnumerationValues($fullKey), $fullKey . '[]', explode(',', (string) $this), 'id="multiselect" multiple="true"') . $fieldEnd;
        
        // readonly fields
        $readonly = '';
        if (in_array($fullKeyNoLang, self::$readOnlyFields) && (!$this->getAttribute('lang') || $sameAsCourseLang))
            $readonly = 'disabled readonly';
        
        // integer fields
        if (in_array($fullKeyNoLang, self::$integerFields)) {
            $value = (string) $this;
            if (empty($value))
                $value = 0;
            return $fieldStart ."<input type='text' size='2' name='". q($fullKey) ."' value='". intval($value) ."' $readonly>". $fieldEnd;
        }
        
        // textarea fields
        if (in_array($fullKeyNoLang, self::$textareaFields))
            return $fieldStart ."<textarea cols='53' rows='2' name='". q($fullKey) ."'>". q((string) $this) ."</textarea>". $fieldEnd;
        
        // binary (file-upload) fields
        if (in_array($fullKeyNoLang, self::$binaryFields)) {
            $html = $fieldStart;
            $value = (string) $this;
            if (!empty($value)) { // image already exists
                $mime = (string) $this->getAttribute('mime');
                $html .= "<img src='data:". q($mime) .";base64,". q($value) ."'/>
                          <input type='hidden' name='". q($fullKey) ."' value='". q($value) ."'>
                          <input type='hidden' name='". q($fullKey) ."_mime' value='". q($mime) ."'>
                          </td></tr><tr><td>";
            }
            $html .= "<input type='file' size='30' name='". q($fullKey) ."'>". $fieldEnd;
            return $html;
        }
        
        // all others get a typical input type box
        return $fieldStart ."<input type='text' size='55' name='". q($fullKey) ."' value='". q((string) $this) ."' $readonly>". $fieldEnd;
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
        $fullKeyNoLang = $fullKey;
        if ($this->getAttribute('lang'))
            $fullKey .= '_' . $this->getAttribute('lang');
        
        if (isset($data[$fullKey])) {
            if (!is_array($data[$fullKey])) {
                if (in_array($fullKeyNoLang, self::$integerFields))
                    $this->{0} = intval($data[$fullKey]);
                else
                    $this->{0} = $data[$fullKey];
                
                if (in_array($fullKeyNoLang, self::$binaryFields)) // mime attribute for mime fields
                    $this['mime'] = isset($data[$fullKey .'_mime']) ? $data[$fullKey .'_mime'] : '';
            }
            else { // multiple entities (multiEnum and units) use associative indexed arrays
                if (in_array($fullKeyNoLang, self::$multiEnumerationFields))
                    $this->{0} = implode(',', $data[$fullKey]); // comma separated
                else { // units
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
            
            $ret = array($fullKey => (string) $this);
            
            if ($this->getAttribute('mime'))
                $ret = array_merge($ret, array($fullKey .'_mime' => (string) $this->getAttribute('mime')));
            
            return $ret;
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
    
    /**
     * Whether the XML contains all mandatory fields or not.
     * 
     * @return boolean
     */
    public function hasMandatoryMetadata() {
        $data = $this->asFlatArray();
        
        foreach (self::$mandatoryFields as $mfield)
            if (!isset($data[$mfield]) || empty($data[$mfield]))
                return false;
            
        // check mandatory unit fields
        if (!isset($data['course_numberOfUnits']) || !intval($data['course_numberOfUnits']) > 0)
            return false;
        // check each unit title and description
        for ($i = 0; $i < intval($data['course_numberOfUnits']); $i++) {
            if (!isset($data['course_unit_title_el'][$i]) || empty($data['course_unit_title_el'][$i]))
                return false;
            if (!isset($data['course_unit_description_el'][$i]) || empty($data['course_unit_description_el'][$i]))
                return false;
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
        $xmlFile  = self::getCourseXMLPath($courseCode);
        $data     = self::getAutogenData($courseId); // preload xml with auto-generated data
        
        // course-based adaptation
        list($dnum)  = mysql_fetch_row(db_query("select count(id) from document where course_id = " . intval($courseId), $mysqlMainDb));
        list($vnum)  = mysql_fetch_row(db_query("select count(id) from video", $courseCode));
        list($vlnum) = mysql_fetch_row(db_query("select count(id) from videolinks", $courseCode));
        if ($dnum + $vnum + $vlnum < 1) {
            self::$hiddenFields[] = 'course_confirmVideolectures';
            $data['course_confirmVideolectures'] = 'false';
        }
        
        $skeletonXML = simplexml_load_file($skeleton, 'CourseXMLElement');
        $skeletonXML->adapt($data);
        $skeletonXML->populate($data);

        if (file_exists($xmlFile)) {
            $xml = simplexml_load_file($xmlFile, 'CourseXMLElement');
            if (!$xml) // fallback if xml is broken
                return $skeletonXML;
            else { // xml is valid, merge autogen data and current xml data
                $new_data = array_merge($xml->asFlatArray(), $data);
                $data = $new_data;
            }
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
    
    /**
     * Refresh/update the auto-generated values for a specific course.
     * 
     * @param int    $courseId
     * @param string $courseCode
     */
    public static function refreshCourse($courseId, $courseCode) {
        if (get_config('course_metadata')) {
            $xml = self::init($courseId, $courseCode);
            self::save($courseCode, $xml);
        }
    }
    
    /**
     * Save the XML structure for a specific course.
     * 
     * @param string           $courseCode
     * @param CourseXMLElement $xml
     */
    public static function save($courseCode, $xml) {
        $doc = new DOMDocument('1.0');
        $doc->loadXML( $xml->asXML() );
        $doc->formatOutput = true;
        $doc->save(self::getCourseXMLPath($courseCode));
    }
    
    /**
     * Auto-Generate Data for a specific course.
     * 
     * @global string $urlServer
     * @param  int    $courseId
     * @return array
     */
    public static function getAutogenData($courseId) {
        global $urlServer, $mysqlMainDb;
        $data = array();
    
        $res1 = db_query("SELECT * FROM cours WHERE cours_id = " . intval($courseId), $mysqlMainDb);
        $course = mysql_fetch_assoc($res1);
        if (!$course)
            return array();

        $clang = langname_to_code($course['languageCourse']);
        $data['course_language'] = $clang;
        $data['course_url'] = $urlServer . 'courses/'. $course['code'];
        $data['course_instructor_fullName_' . $clang] = $course['titulaires'];
        $data['course_title_' . $clang] = $course['intitule'];
        $data['course_keywords_' . $clang] = $course['course_keywords'];

        // turn visible units to associative array
        $res2 = db_query("SELECT id, title, comments
                           FROM course_units
                          WHERE visibility = 'v'
                            AND course_id = " . intval($courseId), $mysqlMainDb);
        $unitsCount = 0;
        while($row = mysql_fetch_assoc($res2)) {
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
     * Enumeration values for HTML Form fields.
     * @param  string $key
     * @return array
     */
    public static function getEnumerationValues($key) { 
        $valArr = array(
            'course_level' => array('undergraduate' => $GLOBALS['langCMeta']['undergraduate'],
                                    'graduate'      => $GLOBALS['langCMeta']['graduate'],
                                    'doctoral'      => $GLOBALS['langCMeta']['doctoral']),
            'course_curriculumLevel' => array('undergraduate' => $GLOBALS['langCMeta']['undergraduate'],
                                              'graduate'      => $GLOBALS['langCMeta']['graduate'],
                                              'doctoral'      => $GLOBALS['langCMeta']['doctoral']),
            'course_yearOfStudy' => array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6'),
            'course_semester' => array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6',
                                       '7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12'),
            'course_type' => array('compulsory' => $GLOBALS['langCMeta']['compulsory'], 
                                   'optional'   => $GLOBALS['langCMeta']['optional']),
            'course_format' => array('slides'                => $GLOBALS['langCMeta']['slides'],
                                     'notes'                 => $GLOBALS['langCMeta']['notes'],
                                     'video lectures'        => $GLOBALS['langCMeta']['video lectures'],
                                     'podcasts'              => $GLOBALS['langCMeta']['podcasts'],
                                     'audio material'        => $GLOBALS['langCMeta']['audio material'],
                                     'multimedia material'   => $GLOBALS['langCMeta']['multimedia material'],
                                     'interactive exercises' => $GLOBALS['langCMeta']['interactive exercises'])
        );
        
        if (isset($valArr[$key]))
            return $valArr[$key];
        else
            return array();
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
        'course_confirmAMinusLevel', 'course_confirmALevel', 'course_confirmAPlusLevel'
    );
    
    /**
     * Fields that should be readonly in the HTML Form.
     * @var array
     */
    public static $readOnlyFields = array(
        'course_language', 'course_instructor_fullName', 'course_title',
        'course_url', 'course_keywords', 'course_numberOfUnits', 
        'course_unit_title', 'course_unit_description'
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
     * Mandatory HTML Form fields.
     * @var array
     */
    public static $mandatoryFields = array(
        'course_instructor_firstName_el', 'course_instructor_firstName_en',
        'course_instructor_lastName_el', 'course_instructor_lastName_en',
        'course_instructor_fullName_el', 'course_instructor_fullName_en',
        'course_title_el', 'course_title_en',
        'course_url',
        'course_code_el',
        'course_targetGroup_el',
        'course_description_el', 'course_description_en',
        'course_contents_el',
        'course_objectives_el',
        'course_keywords_el', 'course_keywords_en',
        'course_featuredBooks_el',
        'course_prerequisites_el',
        'course_literature_el',
        'course_thematic_el', 'course_thematic_en',
        'course_institution_el', 'course_institution_en',
        'course_institutionDescription_el', 'course_institutionDescription_en',
        'course_department_el', 'course_department_en',
        'course_sector_el', 'course_sector_en',
        'course_curriculumTitle_el', 'course_curriculumTitle_en',
        'course_curriculumDescription_el', 'course_curriculumDescription_en',
        'course_outcomes_el', 'course_outcomes_en',
        'course_curriculumKeywords_el', 'course_curriculumKeywords_en',
        'course_curriculumTargetGroup_el', 'course_curriculumTargetGroup_en'
    );
    
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
