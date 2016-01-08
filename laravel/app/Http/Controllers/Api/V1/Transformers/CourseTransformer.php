<?php 
// Declaring transformer namespace
namespace App\Http\Controllers\Api\V1\Transformers;
//aliasing namespaces
use League\Fractal;
use App\Models\Course;
class CourseTransformer extends Fractal\TransformerAbstract {
        public function transform(Course $course) {
            //Mapping database fields to API names
            return [
                'courseID' => $course->id,
                'courseCode' => $course->code,
                'courseLang' => $course->lang,
                'courseTitle' => $course->title,
                'courseProfessors' => $course->prof_names,
                'courseDescription' => $course->description
            ];
        }           
}

