<?php

namespace App\Http\Requests\API;

use App\Http\Requests\Request;

class APIRequest extends Request
{
    public $transformer;
    
    public function all(){
        return $this->transformer->transformInput(parent::all());
    }    
}
