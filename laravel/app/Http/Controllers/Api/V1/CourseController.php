<?php 
// Declaring controller namespace
namespace App\Http\Controllers\Api\V1;

//Aliasing psr-4 autoloaded classes
//use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Api\V1\Transformers\CourseTransformer;
use Illuminate\Http\Request;
use App\Http\Requests\API\StoreCourseRequest;
use Sorskod\Larasponse\Larasponse;
use App\Repositories\CourseRepository;
use App\Http\Controllers\Controller;
use App\Helpers\ApiHelper;
use App\Models\Hierarchy;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;
use App\Models\Config;

class CourseController extends Controller {

        private $response;
        private $courseRepo;
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(Larasponse $response, CourseRepository $courseRepo)
	{
                $this->response = $response;
                $this->courseRepo = $courseRepo;
	}

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
            $limit = $request->input('limit') ?: 5;
            $courses = $this->courseRepo->getAllCourses($limit, ['departments']);
            
            return $this->response->paginatedCollection($courses, new CourseTransformer());
	}
        
	public function store(StoreCourseRequest $request)
	{

            $data['title'] = $request->input('courseTitle');
            $data['description'] = Purifier::clean($request->input('courseDescription'));
            $data['prof_names'] = $request->input('courseProfessors');
            $data['course_license'] = $request->input('courseLicense');
            $data['view_type'] = $request->input('courseViewType');
            $data['visible'] = $request->input('courseVisibility');
            
            $data['doc_quota'] = Config::find('doc_quota')->value * 1024 * 1024;
            $data['group_quota'] = Config::find('group_quota')->value * 1024 * 1024;
            $data['video_quota'] = Config::find('video_quota')->value * 1024 * 1024;
            $data['dropbox_quota'] = Config::find('dropbox_quota')->value * 1024 * 1024;
            
            $hierarchy_id = $request->input('courseDepartments')[0];
            $hierarchy = Hierarchy::find($hierarchy_id);
            // The code below covers cases where different courses share a common hierarchy code
            $all_hierarchies_same_code = Hierarchy::where('code', $hierarchy->code)->get();
            $hierarchy_generator = $all_hierarchies_same_code->max('generator');
            if ($hierarchy) {
                do {
                    $hierarchy_generator += 1;
                    $code = $hierarchy->code . $hierarchy_generator;                    
                } while(in_array($code, Storage::disk('courses')->directories()));
            }
            
            $code = str_replace(' ', '', strtoupper($code));
            
            $data['code'] = $data['public_code'] = $code;
            
            $course = $this->courseRepo->storeCourse($data);  
            
            if ($course->id){
                $course_folders = [
                    $code, 
                    $code.'/image', 
                    $code.'/document', 
                    $code.'/dropbox',
                    $code.'/page',
                    $code.'/work',
                    $code.'/group',
                    $code.'/temp',
                    $code.'/scormPackages'
                ];
                
                foreach ($course_folders as $course_folder) {
                    Storage::disk('courses')->makeDirectory($course_folder); 
                }
                
                Storage::disk('videos')->makeDirectory($code);
                
                return $this->response->item($course, new CourseTransformer()); 
                
            } else {

            }
	}        
	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($course)
	{
            $this->response->parseIncludes('courseDepartments');
            return $this->response->item($course, new CourseTransformer());
        }
        
	public function destroy($course_code)
	{
            dd('Course succeffuly deleted!!');
        }             
}
