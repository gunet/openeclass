@php
    $check_module = Database::get()->queryArray("SELECT *FROM course_module 
                        WHERE module_id = ?d AND course_id = ?d", $module_id, $course_id);
    foreach($check_module as $m){
        $visible_module = $m->visible;
    }
    $go_back_url = $_SERVER['REQUEST_URI'];
@endphp

<div class='d-none d-md-block mt-4'>
    <div class='col-12 ps-3 pb-3 ps-0 pe-0 legendViewContent'>
        
            @if($course_code)
                @if($is_editor)
                    <div class='row'>
                        <div class='col-10 ps-0 pe-0'>
                            @if($toolName)
                                <div class='col-12 mb-2'>
                                    <span class='courseInfoText TextExtraBold blackBlueText me-1'>{{$currentCourseName}}</span>
                                    <span class='courseInfoText TextExtraBold normalBlueText'>({{course_id_to_public_code($course_id)}})</span><br>
                                    <span class='small-text TextRegular blackBlueText'>{{course_id_to_prof($course_id)}}</span>
                                </div>
                                <div class='col-12 d-inline-flex'>
                                    <!-- toolName -->
                                    <span class='text-secondary fst-italic me-2'>{{$toolName}}</span>
                                    <!-- active - inactive module_id -->
                                    @if($module_id != MODULE_ID_COURSEINFO and $module_id != MODULE_ID_USERS
                                        and $module_id != MODULE_ID_USAGE and $module_id != MODULE_ID_TOOLADMIN
                                        and $module_id != MODULE_ID_ABUSE_REPORT and $module_id != MODULE_ID_COURSE_WIDGETS
                                        and $module_id != MODULE_ID_UNITS and !empty($module_id))
                                    <form id="form_id" action="{{$urlAppend}}main/module_toggle.php?course={{$course_code}}&module_id={{$module_id}}" method="post">
                                        <input type="hidden" name="hide" value="{{$visible_module}}">
                                        <input type="hidden" name="Active_Deactive_Btn">
                                        <input type="hidden" name="prev_url" value="{{$go_back_url}}">
                                        @if($visible_module == 0)
                                            <a href="javascript:$('#form_id').submit();"
                                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ trans('langActivate') }}">
                                                <span class="fa tiny-icon fa-minus-square text-danger"></span>
                                            </a>
                                        @else
                                            <a href="javascript:$('#form_id').submit();"
                                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ trans('langDeactivate') }}">
                                                <span class="fa tiny-icon fa-check-square text-success"></span>
                                            </a>
                                        @endif
                                    </form>
                                    @endif
                                    <!-- rss for announcements - blog -->
                                    @if($module_id == MODULE_ID_ANNOUNCE or $module_id == MODULE_ID_BLOG)
                                       @php $getToken = generate_csrf_token_link_parameter(); @endphp
                                       @if($module_id == MODULE_ID_ANNOUNCE)
                                            <a class="ms-2" href="{{$urlAppend}}modules/announcements/rss.php?c={{$course_code}}&uid={{$uid}}&{{$getToken}}">
                                                <span class="fa fa-rss-square tiny-icon tiny-icon-rss text-warning" data-bs-toggle="tooltip" 
                                                data-bs-placement="bottom" data-bs-original-title="{{trans('langRSSFeed')}}"></span>
                                            </a>
                                       @else
                                            <a class="ms-2" href="{{$urlAppend}}modules/blog/rss.php?c={{$course_code}}&uid={{$uid}}&{{$getToken}}">
                                                <span class="fa fa-rss-square tiny-icon tiny-icon-rss text-warning" data-bs-toggle="tooltip" 
                                                data-bs-placement="bottom" data-original-title="{{trans('langRSSFeed')}}"></span>
                                            </a>
                                       @endif
                                    @endif
                                  
                                </div>
                            @else
                                <div class='col-12'>
                                    <span class='courseInfoText TextExtraBold blackBlueText me-1'>{{$currentCourseName}}</span>
                                    <span class='courseInfoText TextExtraBold normalBlueText'>({{course_id_to_public_code($course_id)}})</span><br>
                                    <span class='small-text TextRegular blackBlueText'>{{course_id_to_prof($course_id)}}</span> 
                                </div>
                            @endif
                        </div>
                        <div class='col-2 d-flex justify-content-end align-items-top'>
                            @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])
                        </div>
                    </div>
                @else
                    <div class='row'>
                        <div class='col-12'>
                            @if($toolName)
                                <div class='col-12 mb-2'>
                                    <span class='courseInfoText TextExtraBold blackBlueText me-1'>{{$currentCourseName}}</span> 
                                    <span class='courseInfoText TextExtraBold normalBlueText'>{{course_id_to_public_code($course_id)}}</span><br>
                                    <span class='small-text TextRegular blackBlueText'>{{course_id_to_prof($course_id)}}</span>
                                </div>
                                <div class='col-12 d-inline-flex'>
                                    <span class='text-secondary fst-italic'>{{$toolName}}</span>
                                    <!-- rss for announcements - blog -->
                                    @if($toolName == trans('langAnnouncements') or $toolName == trans('langBlog'))
                                       @php $getToken = generate_csrf_token_link_parameter(); @endphp
                                       @if($toolName == trans('langAnnouncements'))
                                            <a class="ms-2" href="{{$urlAppend}}modules/announcements/rss.php?c={{$course_code}}&uid={{$uid}}&{{$getToken}}">
                                                <span class="fa fa-rss-square tiny-icon tiny-icon-rss text-warning" data-bs-toggle="tooltip" 
                                                data-bs-placement="bottom" data-bs-original-title="{{trans('langRSSFeed')}}"></span>
                                            </a>
                                       @else
                                            <a class="ms-2" href="{{$urlAppend}}modules/blog/rss.php?c={{$course_code}}&uid={{$uid}}&{{$getToken}}">
                                                <span class="fa fa-rss-square tiny-icon tiny-icon-rss text-warning" data-bs-toggle="tooltip" 
                                                data-bs-placement="bottom" data-original-title="{{trans('langRSSFeed')}}"></span>
                                            </a>
                                       @endif
                                    @endif
                                </div>
                            @else
                                <div class='col-12'>
                                    <span class='courseInfoText TextExtraBold blackBlueText me-1'>{{$currentCourseName}}</span>
                                    <span class='courseInfoText TextExtraBold normalBlueText'>{{course_id_to_public_code($course_id)}}</span><br> 
                                    <span class='small-text TextRegular blackBlueText'>{{course_id_to_prof($course_id)}}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <div class='d-flex justify-content-center align-items-center'>
                    <span class="courseInfoText TextExtraBold blackBlueText">{{$toolName}}</spa>
                </div>
            @endif
        
    </div></br>
</div>

<div class='d-block d-md-none mt-3'>
    <div class='col-12 legendViewContent ps-0 pe-0 pt-0 pb-3'>
        
            @if($course_code)
                @if($is_editor)
                    <div class='row'>
                        <div class='col-10'>
                           
                                <table class='table mb-0 ps-0 pe-0'>
                                    <thead>
                                        
                                        <tr class='border-0 ps-0'>
                                            <th class='border-0 ps-0'>
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
                                            <tr class='border-0 ps-0'>
                                                <th class='border-0 d-inline-flex ps-0'>
                                                    <span class='text-secondary fst-italic me-2'>
                                                        {{$toolName}}
                                                    </span>
                                                     <!-- active - inactive module_id -->
                                                     @if($module_id != MODULE_ID_COURSEINFO and $module_id != MODULE_ID_USERS
                                                        and $module_id != MODULE_ID_USAGE and $module_id != MODULE_ID_TOOLADMIN
                                                        and $module_id != MODULE_ID_ABUSE_REPORT and $module_id != MODULE_ID_COURSE_WIDGETS
                                                        and $module_id != MODULE_ID_UNITS and !empty($module_id))
                                                    <form id="form_id" action="{{$urlAppend}}main/module_toggle.php?course={{$course_code}}&module_id={{$module_id}}" method="post">
                                                        <input type="hidden" name="hide" value="{{$visible_module}}">
                                                        <input type="hidden" name="Active_Deactive_Btn">
                                                        <input type="hidden" name="prev_url" value="{{$go_back_url}}">
                                                        @if($visible_module == 0)
                                                            <a href="javascript:$('#form_id').submit();"
                                                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ trans('langActivate') }}">
                                                                <span class="fa tiny-icon fa-minus-square text-danger"></span>
                                                            </a>
                                                        @else
                                                            <a href="javascript:$('#form_id').submit();"
                                                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ trans('langDeactivate') }}">
                                                                <span class="fa tiny-icon fa-check-square text-success"></span>
                                                            </a>
                                                        @endif
                                                    </form>
                                                    @endif
                                                    <!-- rss for announcements - blog -->
                                                    @if($module_id == MODULE_ID_ANNOUNCE or $module_id == MODULE_ID_BLOG)
                                                        @php $getToken = generate_csrf_token_link_parameter(); @endphp
                                                        @if($module_id == MODULE_ID_ANNOUNCE)
                                                                <a class="ms-2" href="{{$urlAppend}}modules/announcements/rss.php?c={{$course_code}}&uid={{$uid}}&{{$getToken}}">
                                                                    <span class="fa fa-rss-square tiny-icon tiny-icon-rss text-warning" data-bs-toggle="tooltip" 
                                                                    data-bs-placement="bottom" data-bs-original-title="{{trans('langRSSFeed')}}"></span>
                                                                </a>
                                                        @else
                                                                <a class="ms-2" href="{{$urlAppend}}modules/blog/rss.php?c={{$course_code}}&uid={{$uid}}&{{$getToken}}">
                                                                    <span class="fa fa-rss-square tiny-icon tiny-icon-rss text-warning" data-bs-toggle="tooltip" 
                                                                    data-bs-placement="bottom" data-original-title="{{trans('langRSSFeed')}}"></span>
                                                                </a>
                                                        @endif
                                                    @endif
                                                </th>
                                            </tr>
                                        @endif

                                        <tbody>
                                        </tbody>
                                    </thead>
                                </table>
                            
                        </div>
                        <div class='col-2 d-flex justify-content-end align-items-top pt-3'>
                            @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])
                        </div>
                    </div>
                @else
                    <div class='row'>
                        <div class='col-12'>
                            
                                <table class='table mb-0 ps-0 pe-0'>
                                    <thead>
                                       
                                       
                                        <tr class='border-0 ps-0'>
                                            <th class='border-0 ps-0'>
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
                                                <th class='border-0 d-inline-flex'>
                                                    <span class='text-secondary fst-italic'>
                                                        {{$toolName}}
                                                    </span>
                                                    <!-- rss for announcements - blog -->
                                                    @if($toolName == trans('langAnnouncements') or $toolName == trans('langBlog'))
                                                        @php $getToken = generate_csrf_token_link_parameter(); @endphp
                                                        @if($toolName == trans('langAnnouncements'))
                                                                <a class="ms-2" href="{{$urlAppend}}modules/announcements/rss.php?c={{$course_code}}&uid={{$uid}}&{{$getToken}}">
                                                                    <span class="fa fa-rss-square tiny-icon tiny-icon-rss text-warning" data-bs-toggle="tooltip" 
                                                                    data-bs-placement="bottom" data-bs-original-title="{{trans('langRSSFeed')}}"></span>
                                                                </a>
                                                        @else
                                                                <a class="ms-2" href="{{$urlAppend}}modules/blog/rss.php?c={{$course_code}}&uid={{$uid}}&{{$getToken}}">
                                                                    <span class="fa fa-rss-square tiny-icon tiny-icon-rss text-warning" data-bs-toggle="tooltip" 
                                                                    data-bs-placement="bottom" data-original-title="{{trans('langRSSFeed')}}"></span>
                                                                </a>
                                                        @endif
                                                    @endif
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
