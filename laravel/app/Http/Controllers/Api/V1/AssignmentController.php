<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Api\V1\Transformers\AssignmentTransformer;
use App\Http\Controllers\Controller;
use Sorskod\Larasponse\Larasponse;
use App\Repositories\AssignmentRepository;

class AssignmentController extends Controller
{
        private $response;
        private $assignmentRepo;
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(Larasponse $response, AssignmentRepository $assignmentRepo)
	{
                $this->response = $response;
                $this->assignmentRepo = $assignmentRepo;
	}
	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index($course, Request $request)
	{
            $limit = $request->input('limit') ?: 5;
            //Query Using ORM, Model required
            $assignments = $this->assignmentRepo->getAllAssignmentsPaginated($course->id, $limit, ['submissions']);
            return $this->response->paginatedCollection($assignments, new AssignmentTransformer());
	}
        
	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($course, $id)
	{
            $assignment = $this->assignmentRepo->findAssignment($course->id, $id, ['submissions']);

            //include submissions relation to response
            $this->response->parseIncludes('submissions');
            
            return $this->response->item($assignment, new AssignmentTransformer());           
        }            
}
