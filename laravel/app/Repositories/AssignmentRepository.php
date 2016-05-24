<?php
namespace App\Repositories;
/* ========================================================================
 * Open eClass 
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
 * ======================================================================== 
 */
use App\Models\Assignment;
class AssignmentRepository {
   private $assignment;

    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }  
    public function getAllAssignments($course_id, $with = []) {
        return $this->assignment->with($with)->where('course_id', '=', $course_id)->get();
    } 
    public function getAllAssignmentsPaginated($course_id, $limit, $with = []) {
        return $this->assignment->with($with)->where('course_id', '=', $course_id)->paginate($limit);
    }
    public function findAssignment($course_id, $id, $with = []) {
        return $this->assignment->with($with)->where('course_id', '=', $course_id)->findOrFail($id);
    }
    public function createOrUpdateAssignment($data, $id = NULL) {
        if($id){
            return $this->assignment->find($id)->update($data);
        } else {
            return $this->assignment->create($data);
        }      
    }    
    //This allows normal eloquent methods applied to repository
    public function __call($method, $args)
    {
        return call_user_func_array([$this->user, $method], $args);
    }      
}
