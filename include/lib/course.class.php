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

class course {

    private $ctable;
    private $typetable;
    private $istypetable;
    private $departmenttable;
    
    /**
     * Constructor
     *
     * @param string $ctable - Name of courses table
     * @param string $typetable - Name of courses types table
     * @param string $istype - Name of course <-> course_type lookup table
     * @param string $deptable - Name of course <-> department lookup table
     */    
    public function course($ctable = 'cours', $typetable = 'course_type', $istype = 'course_is_type', $deptable = 'course_department')
    {
        $this->ctable = $ctable;
        $this->typetable = $typetable;
        $this->istypetable = $istype;
        $this->departmenttable = $deptable;
    }
    
    /**
     * Refresh types and departments of a course
     * 
     * @param int $id
     * @param array $types
     * @param array $departments
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
     * Delete course
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
     * Build array with all course types
     * 
     * @return array 
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
     * Build CheckMap with the types a specific course belongs to
     * 
     * @param int $id
     * 
     * @return array
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
     * Build CheckMap with the nodes a specific course belongs to
     * 
     * @param int $id
     * 
     * @return array
     */
    private function buildDepartmentsMap($id)
    {
        $result = db_query("SELECT department FROM $this->departmenttable WHERE course = '$id'");
        
        $nodes = array();
        
        while($row = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            $nodes[$row['department']] = true;
        }
        
        return $nodes;
    }
    
    public function buildDepartmentSelect($nodes, $id = null, $name = "departments")
    {
        $html = ($this->allowmultidep) ? "<br/>" : "<select name='$name'>";
        
        $checkMap = ($id != null) ? $this->buildDepartmentsMap($id) : null ;
        
        foreach($nodes as $key => $value)
        {
            if ($this->allowmultidep)
            {
                $check = (isset($checkMap[$key])) ? " checked='1' " : '';
                $html .= "<input type='checkbox' name='".$name."[]' value='$key' $check />$value <br />";
            }
            else
            {
                $select = (isset($checkMap[$key])) ? " selected " : '';
                $html .= "<option value='$key' $select>$value</option>";
            }
        }
        
        $html .= ($this->allowmultidep) ? "" : "</select>";
        
        return $html;
    }
    
    public function getDepartmentIds($id)
    {
        $ret = array();
        $result = db_query("SELECT cd.department AS id
                              FROM $this->ctable c, $this->departmenttable cd
                             WHERE c.cours_id = ". intval($id) ." 
                               AND c.cours_id = cd.course");
        
        while($row = mysql_fetch_assoc($result))
            $ret[] = $row['id'];
        
        return $ret;
    }
}
?>
