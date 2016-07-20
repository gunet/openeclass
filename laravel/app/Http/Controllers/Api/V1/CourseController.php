<?php 
// Declaring controller namespace
namespace App\Http\Controllers\Api\V1;

//Aliasing psr-4 autoloaded classes
//use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Api\V1\Transformers\CourseTransformer;
use Illuminate\Http\Request;
use Sorskod\Larasponse\Larasponse;
use App\Repositories\CourseRepository;
use App\Http\Controllers\Controller;

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
            $courses = $this->courseRepo->getAllCourses($limit);
            return $this->response->paginatedCollection($courses, new CourseTransformer());
	}
        
	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($course)
	{
            return $this->response->item($course, new CourseTransformer());
        }        
}
