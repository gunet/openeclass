<?php 
// Declaring transformer namespace
namespace App\Http\Controllers\Api\V1\Transformers;
// Aliasing namespaces
use App\Models\AssignmentSubmit;
use League\Fractal;
class SubmissionsTransformer extends Fractal\TransformerAbstract {     
        public function transform(AssignmentSubmit $assignment_submit) {
            //Mapping database fields to API names
            return [
                'AssignSubmitID' => $assignment_submit->id,
                'AssignSubmitGrade' => $assignment_submit->grade
            ];
        } 
}

