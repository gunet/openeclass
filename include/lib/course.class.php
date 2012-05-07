<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

/**
 * Eclass Course Coordinating Object.
 * 
 * This class does not represent a course entity, but a core logic coordinating object
 * responsible for handling course and hierarchy-to-course related tasks.
 */
class course {

    private $ctable;
    private $typetable;
    private $istypetable;
    private $departmenttable;
    
    /**
     * Constructor - do not use any arguments for default eclass behaviour (standard db tables).
     *
     * @param string $ctable    - Name of courses table
     * @param string $typetable - Name of courses types table
     * @param string $istype    - Name of course <-> course_type lookup table
     * @param string $deptable  - Name of course <-> department lookup table
     */    
    public function course($ctable = 'course', $typetable = 'course_type', $istype = 'course_is_type', $deptable = 'course_department')
    {
        $this->ctable = $ctable;
        $this->typetable = $typetable;
        $this->istypetable = $istype;
        $this->departmenttable = $deptable;
    }
    
    /**
     * Refresh the types and the hierarchy nodes (departments) that a course belongs to. All previous belonging
     * nodes get deleted and then refreshed with the ones given as array arguments.
     * 
     * @param int   $id          - Id for a given course
     * @param array $types       - Array containing the type ids that the given course should belong to
     * @param array $departments - Array containing the node ids that the given course should belong to
     */
    public function refresh($id, $types, $departments)
    {
        if ($types != null)
        {
            db_query("DELETE FROM $this->istypetable WHERE course = '$id'");
            foreach (array_unique($types) as $key => $type)
            {
                if ($type > 0)
                    db_query("INSERT INTO $this->istypetable (course, course_type) VALUES ($id, $type)");
            }
        }
        
        if ($departments != null)
        {
            db_query("DELETE FROM $this->departmenttable WHERE course = '$id'");
            foreach (array_unique($departments) as $key => $department)
            {
                db_query("INSERT INTO $this->departmenttable (course, department) VALUES ($id, $department)");
            }
        }
    }
    
    /**
     * Delete course and all its hierarchy nodes and types dependencies.
     * 
     * @param int $id - The id of the course to delete
     */
    public function delete($id)
    {
        db_query("DELETE FROM $this->istypetable WHERE course = '$id'");
        db_query("DELETE FROM $this->departmenttable WHERE course = '$id'");
        db_query("DELETE FROM $this->ctable WHERE id = '$id'");
    }
    
    /**
     * Build an array with all course types.
     * 
     * @return array $types - An array containing all course types
     */
    public function buildTypes()
    {
        $result = db_query("SELECT id, name FROM $this->typetable ORDER BY id ASC");
        
        $types = array();
        $types[] = "";
        
        while($row = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            if (substr($row['name'], 0, strlen("lang")) === "lang")
            {
                global $$row['name'];
                $types[$row['id']] = $$row['name'];
            }
            else
            {
                $types[$row['id']] = $row['name'];
            }
        }  
        
        return $types;
    }
    
    /**
     * Build ArrayMap with the types a given course belongs to.
     * 
     * @param  int   $id          - Id for a given course
     * @return array $coursetypes - ArrayMap containing the given course's types, in the form of <type id, boolean true>
     */
    private function buildTypesMap($id)
    {
        $result = db_query("SELECT course_type FROM $this->istypetable WHERE course = '$id'");
        
        $coursetypes = array();
        
        while($row = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            $coursetypes[$row['course_type']] = true;
        }
        
        return $coursetypes;
    }
    
    /**
     * Build an HTML section containing checkboxes for a given course's belonging types, in order to be used in html forms.
     * 
     * @param  int    $id   - Id for a given course
     * @param  string $name - The name of the input checkbox form field
     * @return string $html - The returned html output
     */
    public function buildTypesSelection($id = null, $name = "coursetypes")
    {
        $html = "";
        
        $types = $this->buildTypes();
        $checkMap = ($id != null) ? $this->buildTypesMap($id) : null ;
        
        foreach($types as $key => $value)
        {
            $check = (isset($checkMap[$key])) ? " checked='1' " : '';
            $html .= "<input type='checkbox' name='".$name."[]' value='$key' $check />$value";
        }
        
        return $html;
    }
    
    /**
     * Get an array with a given course's hierarchy nodes that it belongs to.
     * 
     * @param  int   $id  - Id for a given course
     * @return array $ret - Array containing the given course's nodes
     */
    public function getDepartmentIds($id)
    {
        $ret = array();
        $result = db_query("SELECT cd.department AS id
                              FROM $this->ctable c, $this->departmenttable cd
                             WHERE c.id = ". intval($id) ." 
                               AND c.id = cd.course");
        
        while($row = mysql_fetch_assoc($result))
            $ret[] = $row['id'];
        
        return $ret;
    }
}