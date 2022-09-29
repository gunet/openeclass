
<div class='d-none d-md-none d-lg-block mt-4'>
    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 shadow p-3 pb-3 bg-body rounded bg-primary'>
        
            @if($course_code)
                @if($is_editor)
                    <div class='row'>
                        <div class='col-12'>
                            @if($toolName)
                                <div class='row'>
                                    <span class='control-label-notes'>
                                        <span class="fas fa-tools orangeText pe-2" aria-hidden="true"></span>{{$toolName}}
                                    </span>
                                </div>
                                <div class='row'>
                                    <span class='control-label-notes'>
                                        <a href='{{ $urlAppend }}index.html'>{!! $section_title !!}</a>
                                    </span>
                                </div>
                                <div class='row'>
                                    <span class='control-label-notes'>
                                        <span class='fas fa-user orangeText pe-1'></span><span class='text-secondary fs-6 ms-1'>{{course_id_to_prof($course_id)}}</span>
                                        <span class="fas fa-code orangeText pe-1"></span><span class='text-secondary fs-6'>{{course_id_to_public_code($course_id)}}</span>                                     
                                    </span>
                                </div>
                            @else
                                <div class='row'>
                                    <span class='control-label-notes'>
                                        <a href='{{ $urlAppend }}index.html'>{!! $section_title !!}</a>
                                    </span>
                                </div>
                                <div class='row'>      
                                    <span class='control-label-notes'>
                                        <span class='fas fa-user orangeText pe-2'></span><span class='text-secondary fs-6'>{{course_id_to_prof($course_id)}}</span> 
                                        <span class="fas fa-code orangeText pe-2"></span><span class='text-secondary fs-6'>{{course_id_to_public_code($course_id)}}</span>                              
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class='row'>
                        <div class='col-12'>
                            @if($toolName)
                                <div class='row'>
                                    <span class='control-label-notes'>
                                        <span class="fas fa-tools orangeText pe-2" aria-hidden="true"></span>{{$toolName}}
                                    </span>
                                </div>
                                <div class='row'>
                                    <span class='control-label-notes'>
                                        <a href='{{ $urlAppend }}index.html'>{!! $section_title !!}</a>
                                    </span>
                                </div>
                                <div class='row'>
                                    <span class='control-label-notes'>
                                        <span class='fas fa-user orangeText pe-1'></span><span class='text-secondary fs-6 ms-1'>{{course_id_to_prof($course_id)}}</span>
                                        <span class="fas fa-code orangeText pe-1"></span><span class='text-secondary fs-6'>{{course_id_to_public_code($course_id)}}</span>                                     
                                    </span>
                                </div>
                            @else
                                <div class='row'>
                                    <span class='control-label-notes'>
                                        <a href='{{ $urlAppend }}index.html'>{!! $section_title !!}</a>
                                    </span>
                                </div>
                                <div class='row'>      
                                    <span class='control-label-notes'>
                                        <span class='fas fa-user orangeText pe-2'></span><span class='text-secondary fs-6'>{{course_id_to_prof($course_id)}}</span> 
                                        <span class="fas fa-code orangeText pe-2"></span><span class='text-secondary fs-6'>{{course_id_to_public_code($course_id)}}</span>                              
                                    </span>
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
    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 shadow p-3 bg-body rounded'>
        
            @if($course_code)
                @if($is_editor)
                    <div class='row'>
                        <div class='col-12 border border-top-0 border-start-0 border-end-0 border-bottom-secondary'>
                           
                                <table class='table'>
                                    <thead>
                                        @if($toolName)
                                            <tr class='border-0'>
                                                <th class='border-0'>
                                                    <div class='row'>
                                                        <div class='col-2'>
                                                            <span class='control-label-notes'>
                                                                <span class="fas fa-tools orangeText pe-2" aria-hidden="true"></span>
                                                            </span>
                                                        </div>
                                                        <div class='col-10'>
                                                            <span class='control-label-notes fs-6'>
                                                                {{$toolName}}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </th>
                                            </tr>
                                        @endif
                                       
                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <div class='row'>
                                                    <div class='col-2'>
                                                        <span class='control-label-notes'>
                                                            <span class="fas fa-university orangeText pe-2" aria-hidden="true"></span>
                                                        </span>
                                                    </div>
                                                    <div class='col-10'>
                                                        <span class='control-label-notes fs-6'>
                                                            <a href='{{ $urlAppend }}index.html'>{!! $section_title !!}</a>
                                                        </span>
                                                    </div>
                                                </div>
                                            </th>
                                        </tr>
                                        
                                            
                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <div class='row'>
                                                    <div class='col-2'>
                                                        <span class='control-label-notes'>
                                                            <span class="fas fa-user orangeText pe-2" aria-hidden="true"></span>
                                                        </span>
                                                    </div>
                                                    <div class='col-10'>
                                                        <span class='control-label-notes fs-6'>
                                                            {{course_id_to_prof($course_id)}}
                                                        </span>
                                                    </div>
                                                </div>
                                            </th>
                                        </tr>

                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <div class='row'>
                                                    <div class='col-2'>
                                                        <span class='control-label-notes'>
                                                            <span class="fas fa-code orangeText pe-2" aria-hidden="true"></span>
                                                        </span>
                                                    </div>
                                                    <div class='col-10'>
                                                        <span class='control-label-notes fs-6'>
                                                        {{course_id_to_public_code($course_id)}}
                                                        </span>
                                                    </div>
                                                </div>
                                            </th>
                                        </tr>
                                        
                                        
                                        <tbody>
                                        </tbody>
                                    </thead>
                                </table>
                            
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
                                                    <div class='row'>
                                                        <div class='col-2'>
                                                            <span class='control-label-notes'>
                                                                <span class="fas fa-tools orangeText pe-2" aria-hidden="true"></span>
                                                            </span>
                                                        </div>
                                                        <div class='col-10'>
                                                            <span class='control-label-notes fs-6'>
                                                                {{$toolName}}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </th>
                                            </tr>
                                        @endif
                                       
                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <div class='row'>
                                                    <div class='col-2'>
                                                        <span class='control-label-notes'>
                                                            <span class="fas fa-university orangeText pe-2" aria-hidden="true"></span>
                                                        </span>
                                                    </div>
                                                    <div class='col-10'>
                                                        <span class='control-label-notes fs-6'>
                                                            <a href='{{ $urlAppend }}index.html'>{!! $section_title !!}</a>
                                                        </span>
                                                    </div>
                                                </div>
                                            </th>
                                        </tr>
                                        
                                            
                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <div class='row'>
                                                    <div class='col-2'>
                                                        <span class='control-label-notes'>
                                                            <span class="fas fa-user orangeText pe-2" aria-hidden="true"></span>
                                                        </span>
                                                    </div>
                                                    <div class='col-10'>
                                                        <span class='control-label-notes fs-6'>
                                                            {{course_id_to_prof($course_id)}}
                                                        </span>
                                                    </div>
                                                </div>
                                            </th>
                                        </tr>

                                        <tr class='border-0'>
                                            <th class='border-0'>
                                                <div class='row'>
                                                    <div class='col-2'>
                                                        <span class='control-label-notes'>
                                                            <span class="fas fa-code orangeText pe-2" aria-hidden="true"></span>
                                                        </span>
                                                    </div>
                                                    <div class='col-10'>
                                                        <span class='control-label-notes fs-6'>
                                                        {{course_id_to_public_code($course_id)}}
                                                        </span>
                                                    </div>
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
