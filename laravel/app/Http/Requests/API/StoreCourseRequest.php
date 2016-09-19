<?php

namespace App\Http\Requests\API;

use App\Http\Requests\Request;
use App\Http\Controllers\Api\V1\Transformers\CourseTransformer;

class StoreCourseRequest extends Request
{
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'courseTitle' => 'required',
            'courseProfessors' => 'required',
            'courseLang' => 'required',
            'courseDepartments' => 'required',
            'courseLicense' => 'required',
            'courseViewType' => 'required',
        ];
    }
}
