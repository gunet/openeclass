<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Requests;
use Sorskod\Larasponse\Larasponse;
use App\Http\Controllers\Controller;

class RouteController extends Controller
{
    public function __construct(Larasponse $response)
    {
            $this->response = $response;
    }    
    public function index(Request $request)
    {
        $routes = \Route::getRoutes();
        $routes_array = [];
        foreach($routes as $route)
        {
            if ($route->getName()) {
                $routes_array[] = $route->getName();
            }
        }
        
        return $this->response
                ->collection(collect($routes_array));
    }
}
