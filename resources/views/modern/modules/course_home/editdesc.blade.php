
@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">


                <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0"> 
                    <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                    </div>
                </div>
                

                <div class="col_maincontent_active">
                    
                        <div class="row">
                            
                            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                            <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                                <div class="offcanvas-header">
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body">
                                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                                </div>
                            </div>

                            @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])
                            
                            

                            @if(Session::has('message'))
                            <div class='col-12 all-alerts'>
                                <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                    @php 
                                        $alert_type = '';
                                        if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                            $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                        }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                            $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                        }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                            $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                        }else{
                                            $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                        }
                                    @endphp
                                    
                                    @if(is_array(Session::get('message')))
                                        @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                        {!! $alert_type !!}<span>
                                        @foreach($messageArray as $message)
                                            {!! $message !!}
                                        @endforeach</span>
                                    @else
                                        {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                                    @endif
                                    
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                            @endif

                            <div class='col-12'>
                                <div class='form-wrapper form-edit p-3 rounded'>
                                    <form class='form-horizontal' role='form' method='post' action='editdesc.php?course={{$course_code}}' enctype='multipart/form-data'>
                                        <fieldset>
                                            <div class='form-group'>
                                                <label for='description' class='col-sm-6 control-label-notes'>{{$langCourseLayout}}:</label>
                                                <div class='col-sm-12'>
                                                    {!! $selection !!}
                                                </div>
                                            </div>
                                            <div class="row p-2"></div>

                                            @if($layout == 1)
                                            <div id='image_field' class='form-group'>
                                            @else
                                            <div id='image_field' class='form-group hidden'>
                                            @endif
                                            
                                                <label for='course_image' class='col-sm-6 control-label-notes'>{{$langCourseImage}}:</label>
                                                <div class='col-sm-12'>
                                                    @if(!$course_image == NULL)
                                                        <img style="max-height:100px;max-width:150px;" src='{{$urlAppend}}courses/{{$course_code}}/image/{{$course_image}}'> &nbsp;&nbsp;
                                                        <a class='btn btn-xs btn-danger' href='{{$urlAppend}}modules/course_home/editdesc.php?deleteImageCourse={{$course_id}}&delete_image=true&{!! $generate_csrf_token_link_parameter !!}'>Διαγραφή</a>
                                                        <input type='hidden' name='course_image' value='{{$course_image}}'>
                                                    @else
                                                        {!! $enableCheckFileSize !!}
                                                        {!! $fileSizeHidenInput !!}<input type='file' name='course_image' id='course_image'>
                                                    @endif
                                                </div>
                                            </div>     
                                            <div class="row p-2"></div>             
                                            <div class='form-group'>
                                                <label for='description' class='col-sm-6 control-label-notes'>{{$langDescription}}:</label>
                                                <div class='col-sm-12'>
                                                     {!! $rich_text_editor !!}
                                                </div>
                                            </div>
                                            <div class="row p-2"></div>
                                            <div class='form-group'>
                                                <div class='col-sm-12 col-sm-offset-2'>
                                                    <input class='btn submitAdminBtn' type='submit' name='submit' value='{{$langSubmit}}'>
                                                    <a href='{{$urlServer}}courses/{{$course_code}}/index.php' class='btn btn-secondary'>Ακύρωση</a>
                                                </div>
                                            </div>
                                        </fieldset>
                                        {!! $generate_csrf_token_form_field !!}
                                    </form>
                                </div>  
                            </div>
                        </div>
                    
                </div>
                
            
        </div>


</div>
</div>



<script>
    $(function(){
        $('select[name=layout]').change(function ()
        {
            if($(this).val() == 1) {
                $('#image_field').removeClass('hidden');
            } else {
                $('#image_field').addClass('hidden');
            }
        });          
    });
</script>

@endsection
