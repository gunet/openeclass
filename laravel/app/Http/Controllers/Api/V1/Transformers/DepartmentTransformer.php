<?php 
// Declaring transformer namespace
namespace App\Http\Controllers\Api\V1\Transformers;
//aliasing namespaces
use League\Fractal;
use App\Models\Hierarchy;
class DepartmentTransformer extends Fractal\TransformerAbstract {    
        public function transform( Hierarchy $department ) {
            //Mapping database fields to API names
            return [
                'departmentID' => $department->id,
                'departmentCode' => $department->code,
                'departmentName' => $department->name,
                'departmentNumber' => $department->number,
                'departmentGenerator' => $department->generator,
                'departmentLft' => $department->lft,
                'departmentRgt' => $department->rgt,
                'departmentAllowCourse' => (boolean) $department->allow_course,
                'departmentAllowUser' => (boolean) $department->allow_user,
                'departmentOrderPriority' => $department->order_priority,   
            ];
        }          
}

