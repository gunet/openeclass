<?php 
// Declaring transformer namespace
namespace App\Http\Controllers\Api\V1\Transformers;
//aliasing namespaces
use League\Fractal;
use App\Models\Assignment;
class AssignmentTransformer extends Fractal\TransformerAbstract {
        protected $availableIncludes = [
            'submissions'
        ];        
        public function transform(Assignment $assignment) {
            //Mapping database fields to API names
            return [
                'assignID' => $assignment->id,
                'assignTitle' => $assignment->title,
                'assignDescription' => $assignment->description,
                'assignDeadline' => $assignment->deadline,
                'assignLateSubmission' => (boolean) $assignment->late_submission,
                'assignSubmissionsCount' => $assignment->submissions->count(),
                'assignUngradedSubmissions' => $assignment->submissions->filter(function($item){return $item->grade == NULL;})->count()
            ];
        }
        public function includeSubmissions( Assignment $assignment )
        {
            return $this->collection( $assignment->submissions, new SubmissionsTransformer() );
        }             
}

