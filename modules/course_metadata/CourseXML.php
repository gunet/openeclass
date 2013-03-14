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
    
    public function getAttribute($name) {
        $attributes = $this->attributes();
        if (isset($attributes[$name]))
            return $attributes[$name];
        else
            return false;
    }
    
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
    
    public function populate($data, $parentKey = '') {
        $fullKey = $this->mendFullKey($parentKey);
        
        $children = $this->children();
        if (count($children) == 0)
            return $this->populateLeaf($data, $fullKey);
        
        foreach ($children as $ele)
            $ele->populate($data, $fullKey);
    }
    
    private function populateLeaf($data, $fullKey) {
        if ($this->getAttribute('lang'))
            $fullKey .= '_' . $this->getAttribute('lang');
        
        if (isset($data[$fullKey]))
            $this->{0} = $data[$fullKey];
    }
    
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
    
    private function mendFullKey($parentKey) {
        $fullKey = $this->getName();
        if (!empty($parentKey))
            $fullKey = $parentKey . "_" . $fullKey;
        return $fullKey;
    }
    
    public function countAll() {
        $children = $this->children();
        if (count($children) == 0)
            return 1;
        
        $sum = 0;
        foreach ($children as $ele)
            $sum += $ele->countAll();
        
        return $sum;
    }
    
    public static $hiddenFields = array(
        
    );
    
    public static $readOnlyFields = array(
        'course_language', 'course_instructor_fullName', 'course_title',
        'course_url', 'course_keywords'
    );
}