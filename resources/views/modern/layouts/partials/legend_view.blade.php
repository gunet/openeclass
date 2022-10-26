
<div class='d-none d-md-block mt-4'>
    <div class='col-12 shadow p-3 pb-3 bg-body rounded'>
        
            @if($course_code)
                @if($is_editor)
                    <div class='row'>
                        <div class='col-10'>
                            @if($toolName)
                                <div class='col-12 mb-2'>
                                    <span class='control-label-notes fs-5 me-1'>{{$currentCourseName}}</span>
                                    <span class='text-secondary'>({{course_id_to_public_code($course_id)}})</span><br>
                                    <span class='text-secondary'>{{course_id_to_prof($course_id)}}</span>
                                </div>
                                <div class='col-12'>
                                    <span class='text-secondary fst-italic'>{{$toolName}}</span>
                                </div>
                            @else
                                <div class='col-12'>
                                    <span class='control-label-notes fs-5 me-1'>{{$currentCourseName}}</span>
                                    <span class='text-secondary'>({{course_id_to_public_code($course_id)}})</span><br>
                                    <span class='text-secondary'>{{course_id_to_prof($course_id)}}</span> 
                                </div>
                            @endif
                        </div>
                        <div class='col-2 d-flex justify-content-end align-items-center'>
                            @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])
                        </div>
                    </div>
                @else
                    <div class='row'>
                        <div class='col-12'>
                            @if($toolName)
                                <div class='col-12 mb-2'>
                                    <span class='control-label-notes fs-5 me-1'>{{$currentCourseName}}</span> 
                                    <span class='text-secondary'>{{course_id_to_public_code($course_id)}}</span><br>
                                    <span class='text-secondary'>{{course_id_to_prof($course_id)}}</span>
                                </div>
                                <div class='col-12'>
                                    <span class='text-secondary fst-italic'>{{$toolName}}</span>
                                </div>
                            @else
                                <div class='col-12'>
                                    <span class='control-label-notes fs-5 me-1'>{{$currentCourseName}}</span>
                                    <span class='text-secondary'>{{course_id_to_public_code($course_id)}}</span><br> 
                                    <span class='text-secondary'>{{course_id_to_prof($course_id)}}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <div class='d-flex justify-content-center ps-1 pt-1 pb-2'>
                    <div class="d-inline-flex align-items-top">
                        <i class="fas fa-tools orangeText text-center me-2 mt-1" aria-hidden="true"></i> 
                        <span class="control-label-notes">{{$toolName}}</spa>
                    </div>
                </div>
            @endif
        
    </div></br>
</div>

<div class='d-block d-md-none mt-3'>
    <div class='col-12 shadow p-3 bg-body rounded'>
        
            @if($course_code)
                @if($is_editor)
                    <div class='row'>
                        <div class='col-10'>
                           
                                <table class='table'>
                                    <thead>
                                        
                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <span class='control-label-notes fs-5'>
                                                    {{$currentCourseName}}
                                                </span>
                                                <span class='text-secondary'>
                                                    ({{course_id_to_public_code($course_id)}})
                                                </span><br>
                                                <span class='text-secondary'>
                                                    {{course_id_to_prof($course_id)}}
                                                </span>
                                            </th>
                                        </tr>

                                        @if($toolName)
                                            <tr class='border-0'>
                                                <th class='border-0'>
                                                    <span class='text-secondary fst-italic'>
                                                        {{$toolName}}
                                                    </span>
                                                </th>
                                            </tr>
                                        @endif

                                        <tbody>
                                        </tbody>
                                    </thead>
                                </table>
                            
                        </div>
                        <div class='col-2 d-flex justify-content-end align-items-end'>
                            @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])
                        </div>
                    </div>
                @else
                    <div class='row'>
                        <div class='col-12'>
                            
                                <table class='table'>
                                    <thead>
                                       
                                       
                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <span class='control-label-notes fs-5'>
                                                    {{$currentCourseName}}
                                                </span>
                                                <span class='text-secondary'>
                                                    {{course_id_to_public_code($course_id)}}
                                                </span><br>
                                                <span class='text-secondary'>
                                                    {{course_id_to_prof($course_id)}}
                                                </span>
                                            </th>
                                        </tr>

                                        @if($toolName)
                                            <tr class='border-0'>
                                                <th class='border-0'>
                                                    <span class='text-secondary fst-italic'>
                                                        {{$toolName}}
                                                    </span>
                                                </th>
                                            </tr>
                                        @endif
                                        
                                        
                                        <tbody>
                                        </tbody>
                                    </thead>
                                </table>
                            
                        </div>
                    </div>
                @endif
            @else
                <div class='d-flex justify-content-center ps-1 pt-1 pb-2'>
                    <div class="d-inline-flex align-items-top">
                        <i class="fas fa-tools orangeText text-center me-2 mt-1" aria-hidden="true"></i> 
                        <span class="control-label-notes">{{$toolName}}</span>
                    </div>
                </div>
            @endif
        
    </div></br>
</div>
