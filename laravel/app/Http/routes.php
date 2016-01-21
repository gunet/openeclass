<?php
/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'api/v1'], function()
{
    //This line is needed so that Fractal Lib returns collections and items in similar 
    //format (i.e. in a data array)
    App::bind('League\Fractal\Serializer\SerializerAbstract', 'League\Fractal\Serializer\DataArraySerializer');

    //Using resource Controllers (http://laravel.com/docs/5.0/controllers#restful-resource-controllers)
    Route::resource('courses', 'Api\V1\CourseController', ['only' => ['index', 'show']]);
    Route::resource('courses.assignments', 'Api\V1\AssignmentController');
});

/**
 * Binding the course_code parameter to the Course Model.
 * A course model object is injected to the route's controller
 * More info on (http://laravel.com/docs/5.0/routing#route-model-binding)
 */
Route::bind('courses', function($value)
{
    $course = App\Models\Course::where('code', '=', $value)->firstOrFail();
    return $course; // so that the course object is available to all controllers related to course
});
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    Route::get('test', function () {     
        return view('welcome');
    });    
});
