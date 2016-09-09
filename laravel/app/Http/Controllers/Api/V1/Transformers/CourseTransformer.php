<?php 
// Declaring transformer namespace
namespace App\Http\Controllers\Api\V1\Transformers;
//aliasing namespaces
use League\Fractal;
use App\Models\Course;
class CourseTransformer extends Fractal\TransformerAbstract {
        protected $availableIncludes = [
            'courseDepartments'
        ];     
        public function transform( Course $course ) {
            //Mapping database fields to API names
            return [
                'courseID' => $course->id,
                'courseCode' => $course->code,
                'courseLang' => $course->lang,
                'courseTitle' => $course->title,
                'courseProfessors' => $course->prof_names,
                'courseDescription' => $course->description,
                'courseLicenseType' => $course->course_license > 0 && $course->course_license < 7 ? "cc" : $course->course_license,
                'courseCCLicenseType' => $course->course_license > 0 && $course->course_license < 7 ? $course->course_license : null,
                
            ];
        }
        
        public function includeCourseDepartments( Course $course )
        {
            return $this->collection( $course->departments, new DepartmentTransformer() );
        }    
        
//        public function transformInput($input) {
//            //Mapping database fields to API names
//            $input_transformations = [
//                'courseTitle' => 'title',
//                'courseProfessors' => 'prof_names',
//                'courseDescription' => 'description',
//                'courseLang' => 'lang',
//                'courseDepartments' => 'departments',
//                'courseLicenseType' => 'l_radio',
//                'courseCCLicenseType' => 'cc_use',
//            ];
//            foreach ($input as $key => $value) {
//                if (array_key_exists($key, $input_transformations)) {
//                    $input[$input_transformations[$key]] = $input[$key];
//                    unset($input[$key]);                     
//                }  
//            }
//            return $input;
//        }         
}
