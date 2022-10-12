
<div class='d-none d-md-none d-lg-block mt-4'>
    <div class='col-12 shadow p-3 pb-3 bg-body rounded'>
        
            @if($course_code)
                @if($is_editor)
                    <div class='row'>
                        <div class='col-10'>
                            @if($toolName)
                                <div class='col-12 mb-2'>
                                    <div class='d-inline-flex align-items-top'>
                                        <span class="fas fa-tools orangeText pe-2 mt-1 fs-6" aria-hidden="true"></span>
                                        <span class='control-label-notes fs-6'>{{$toolName}}</span>
                                    </div>
                                </div>
                                <div class='col-12 mb-2'>
                                    <div class='d-inline-flex align-items-top'>
                                        <span class='fas fa-university orangeText pe-2 mt-1'></span>
                                        <span class='control-label-notes fs-6'>{{$currentCourseName}}</span>
                                    </div>
                                </div>
                                <div class='col-12'>
                                    <div class='d-inline-flex align-items-top'>
                                        <span class='fas fa-user orangeText pe-1 mt-1'></span>
                                        <span class='text-secondary fs-6 ms-1 me-2'>{{course_id_to_prof($course_id)}}</span>
                                        <span class="fas fa-key orangeText pe-1 mt-1"></span>
                                        <span class='text-secondary fs-6'>{{course_id_to_public_code($course_id)}}</span>                                     
                                    </div>
                                </div>
                            @else
                                <div class='col-12 mb-2'>
                                    <div class='d-inline-flex align-items-top'>
                                        <span class='fas fa-university orangeText pe-2 mt-1'></span>
                                        <span class='control-label-notes fs-6'>{{$currentCourseName}}</span>
                                    </div>
                                </div>
                                <div class='col-12'>
                                    <div class='d-inline-flex align-items-top'>      
                                        <span class='fas fa-user orangeText pe-2 mt-1'></span>
                                        <span class='text-secondary fs-6 me-2'>{{course_id_to_prof($course_id)}}</span> 
                                        <span class="fas fa-key orangeText pe-2 mt-1"></span>
                                        <span class='text-secondary fs-6'>{{course_id_to_public_code($course_id)}}</span>                              
                                    </div>
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
                                    <div class='d-inline-flex align-items-top'>
                                        <span class="fas fa-tools orangeText pe-2 mt-1" aria-hidden="true"></span>
                                        <span class='control-label-notes fs-6'>{{$toolName}}</span>
                                    </div>
                                </div>
                                <div class='col-12 mb-2'>
                                    <div class='d-inline-flex align-items-top'>
                                        <span class='fas fa-university orangeText pe-2 mt-1'></span>
                                        <span class='control-label-notes fs-6'>{{$currentCourseName}}</span>
                                    </div>
                                </div>
                                <div class='col-12'>
                                    <div class='d-inline-flex align-items-top'>
                                        <span class='fas fa-user orangeText pe-1 mt-1'></span>
                                        <span class='text-secondary fs-6 ms-1 me-2'>{{course_id_to_prof($course_id)}}</span>
                                        <span class="fas fa-key orangeText pe-1 mt-1"></span>
                                        <span class='text-secondary fs-6'>{{course_id_to_public_code($course_id)}}</span>                                     
                                    </div>
                                </div>
                            @else
                                <div class='col-12 mb-2'>
                                    <div class='d-inline-flex align-items-top'>
                                        <span class='fas fa-university orangeText pe-2 mt-1'></span>
                                        <span class='control-label-notes fs-6'>{{$currentCourseName}}</span>
                                    </div>
                                </div>
                                <div class='col-12'>
                                    <div class='d-inline-flex align-items-top'>      
                                        <span class='fas fa-user orangeText pe-2 mt-1'></span>
                                        <span class='text-secondary fs-6 me-2'>{{course_id_to_prof($course_id)}}</span> 
                                        <span class="fas fa-key orangeText pe-2 mt-1"></span>
                                        <span class='text-secondary fs-6'>{{course_id_to_public_code($course_id)}}</span>                              
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <div class='d-flex justify-content-center ps-1 pt-1 pb-2'>
                    <span class="control-label-notes">
                        <i class="fas fa-tools orangeText" aria-hidden="true"></i> 
                        {{$toolName}} 
                    </span>
                </div>
            @endif
        
    </div></br>
</div>

<div class='d-block d-md-block d-lg-none mt-3'>
    <div class='col-12 shadow p-3 bg-body rounded'>
        
            @if($course_code)
                @if($is_editor)
                    <div class='row'>
                        <div class='col-10'>
                           
                                <table class='table'>
                                    <thead>
                                        @if($toolName)
                                            <tr class='border-0'>
                                                <th class='border-0'>
                                                    <div class='d-inline-flex aling-items-top'>
                                                        <span class="fas fa-tools orangeText pe-2 mt-1 fs-6" aria-hidden="true"></span>
                                                        <span class='control-label-notes fs-6'>
                                                            {{$toolName}}
                                                        </span>
                                                    </div>
                                                </th>
                                            </tr>
                                        @endif
                                       
                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <div class='d-inline-flex aling-items-top'>
                                                    <span class="fas fa-university orangeText pe-2 mt-1 fs-6" aria-hidden="true"></span>
                                                    <span class='control-label-notes fs-6'>
                                                        {{$currentCourseName}}
                                                    </span>
                                                </div>
                                            </th>
                                        </tr>
                                        
                                            
                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <div class='d-inline-flex aling-items-top'>
                                                    <span class="fas fa-user orangeText pe-2 mt-1 fs-6" aria-hidden="true"></span>
                                                    <span class='control-label-notes fs-6'>
                                                        {{course_id_to_prof($course_id)}}
                                                    </span>
                                                </div>
                                            </th>
                                        </tr>

                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <div class='d-inline-flex aling-items-top'>
                                                    <span class="fa fa-key orangeText pe-1 mt-1 fs-6" aria-hidden="true"></span>
                                                    <span class='control-label-notes fs-6'>
                                                        {{course_id_to_public_code($course_id)}}
                                                    </span>
                                                </div>
                                            </th>
                                        </tr>
                                        
                                        
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
                                        @if($toolName)
                                            <tr class='border-0'>
                                                <th class='border-0'>
                                                    <div class='d-inline-flex aling-items-top mb-2'>
                                                        <span class="fas fa-tools orangeText pe-2 mt-1" aria-hidden="true"></span>
                                                        <span class='control-label-notes fs-6'>
                                                            {{$toolName}}
                                                        </span>
                                                    </div>
                                                </th>
                                            </tr>
                                        @endif
                                       
                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <div class='d-inline-flex aling-items-top mb-2'>
                                                    <span class="fas fa-university orangeText pe-2 mt-1" aria-hidden="true"></span>
                                                    <span class='control-label-notes fs-6'>
                                                        {{$currentCourseName}}
                                                    </span>
                                                </div>
                                            </th>
                                        </tr>
                                        
                                            
                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <div class='d-inline-flex aling-items-top mb-2'>
                                                    <span class="fas fa-user orangeText pe-2 mt-1" aria-hidden="true"></span>
                                                    <span class='control-label-notes fs-6'>
                                                        {{course_id_to_prof($course_id)}}
                                                    </span>
                                                </div>
                                            </th>
                                        </tr>

                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <div class='d-inline-flex aling-items-top'>
                                                    <span class="fa fa-key orangeText pe-2 mt-1" aria-hidden="true"></span>
                                                    <span class='control-label-notes fs-6'>
                                                        {{course_id_to_public_code($course_id)}}
                                                    </span>
                                                </div>
                                            </th>
                                        </tr>
                                        
                                        
                                        <tbody>
                                        </tbody>
                                    </thead>
                                </table>
                            
                        </div>
                    </div>
                @endif
            @else
                <div class='d-flex justify-content-center ps-1 pt-1 pb-2'>
                    <span class="control-label-notes">
                        <i class="fas fa-tools orangeText text-center" aria-hidden="true"></i> 
                        {{$toolName}} 
                    </span>
                </div>
            @endif
        
    </div></br>
</div>
