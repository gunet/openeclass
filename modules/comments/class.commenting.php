<?php
/* ========================================================================
 * Open eClass 3.0
* E-learning and Course Management System
* ========================================================================
* Copyright 2003-2014  Greek Universities Network - GUnet
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
 * This class represents a commenting system
*/
Class Commenting {
    
    private $rtype = '';
    private $rid = 0;
    
    /**
     * Constructor
     * @param course_id the id of the course in case of a course blog
     * @param user_id the id of the user in case of a user blog
     */
    public function __construct($rtype, $rid) {
    	$this->rtype = $rtype;
    	$this->rid = $rid;
    }
    
    
    /**
     * Get number of comments for a resource
     * @return int
     */
    public function getCommentsNum() {
        $sql = "SELECT COUNT(`id`) as c FROM `comments` WHERE `rtype` = ? AND `rid` = ?";
        $res = Database::get()->querySingle($sql, $this->rtype, $this->rid);
        return $res->c;
    }
}