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
 * Eclass User Coordinating Object.
 * 
 * This class does not represent a user entity, but a core logic coordinating object
 * responsible for handling user and hierarchy-to-user related tasks.
 */
class user {

    private $utable;
    private $departmenttable;
    
    /**
     * Constructor - do not use any arguments for default eclass behaviour (standard db tables).
     *
     * @param string  $utable   - Name of users table
     * @param string  $deptable - Name of user <-> department lookup table
     */    
    public function user($utable = 'user', $deptable = 'user_department')
    {
        $this->utable = $utable;
        $this->departmenttable = $deptable;
    }
    
    /**
     * Refresh the hierarchy nodes (departments) that a user belongs to. All previous belonging
     * nodes get deleted and then refreshed with the ones given as array argument.
     * 
     * @param int   $id          - Id for a given user
     * @param array $departments - Array containing the node ids that the given user should belong to
     */
    public function refresh($id, $departments)
    {
        if ($departments != null)
        {
            db_query("DELETE FROM $this->departmenttable WHERE user = '$id'");
            foreach (array_unique($departments) as $key => $department)
            {
                db_query("INSERT INTO $this->departmenttable (user, department) VALUES ($id, $department)");
            }
        }
    }
    
    /**
     * Delete user and all its hierarchy nodes dependencies.
     * 
     * @param int $id - The id of the user to delete
     */
    public function delete($id)
    {
        db_query("DELETE FROM $this->departmenttable WHERE user = '$id'");
        db_query("DELETE FROM $this->utable WHERE user_id = '$id'");
    }
    
    /**
     * Get an array with a given user's hierarchy nodes that he belongs to.
     * 
     * @param  int   $id  - The id of a given user
     * @return array $ret - Array containing the given user's nodes
     */
    public function getDepartmentIds($id)
    {
        $ret = array();
        $result = db_query("SELECT ud.department AS id
                              FROM $this->utable u, $this->departmenttable ud
                             WHERE u.user_id = ". intval($id) ." 
                               AND u.user_id = ud.user");
        
        while($row = mysql_fetch_assoc($result))
            $ret[] = $row['id'];
        
        return $ret;
    }

}
?>
