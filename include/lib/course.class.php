<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

/**
 * Eclass Course Coordinating Object.
 *
 * This class does not represent a course entity, but a core logic coordinating object
 * responsible for handling course and hierarchy-to-course related tasks.
 */
class Course {

    private $ctable;
    private $departmenttable;

    /**
     * Constructor - do not use any arguments for default eclass behaviour (standard db tables).
     *
     * @param string $ctable    - Name of courses table
     * @param string $deptable  - Name of course <-> department lookup table
     */
    public function __construct($ctable = 'course', $deptable = 'course_department') {
        $this->ctable = $ctable;
        $this->departmenttable = $deptable;
    }

    /**
     * Refresh the hierarchy nodes (departments) that a course belongs to. All previous belonging
     * nodes get deleted and then refreshed with the ones given as array arguments.
     *
     * @param int   $id          - Id for a given course
     * @param array $departments - Array containing the node ids that the given course should belong to
     */
    public function refresh($id, $departments) {
        if ($departments != null) {
            Database::get()->query("DELETE FROM $this->departmenttable WHERE course = ?d", $id);
            foreach (array_unique($departments) as $key => $department) {
                Database::get()->query("INSERT INTO $this->departmenttable (course, department) VALUES (?d,?d)", $id, $department);
            }
        }
        // refresh index
        global $webDir; // required for indexer
        require_once 'modules/search/classes/ConstantsUtil.php';
        require_once 'modules/search/lucene/indexer.class.php';
        Indexer::queueAsync(ConstantsUtil::REQUEST_STORE, ConstantsUtil::RESOURCE_COURSE, $id);
        // refresh course metadata
        require_once 'modules/course_metadata/CourseXML.php';
        CourseXMLElement::refreshCourse($id, course_id_to_code($id));
    }

    /**
     * Delete course and all its hierarchy nodes dependencies.
     *
     * @param int $id - The id of the course to delete
     */
    public function delete($id) {
        Database::get()->query("DELETE FROM $this->departmenttable WHERE course = ?d", $id);
        Database::get()->query("DELETE FROM $this->ctable WHERE id = ?d", $id);
    }

    /**
     * Get an array with a given course's hierarchy nodes that it belongs to.
     *
     * @param  int   $id  - Id for a given course
     * @return array $ret - Array containing the given course's nodes
     */
    public function getDepartmentIds($id) {
        $ret = array();
        Database::get()->queryFunc("SELECT cd.department AS id
                              FROM $this->ctable c, $this->departmenttable cd
                             WHERE c.id = ?d
                               AND c.id = cd.course", function($row) use (&$ret) {
            $ret[] = $row->id;
        }, $id);
        return $ret;
    }

}
