@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col_maincontent_active">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    @include('layouts.partials.legend_view')
                    
                   
                    {!! $action_bar !!}

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
                            
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

               

                    <div class='d-lg-flex gap-4 mt-4'>
                    <div class='flex-grow-1'>
                        <div class='form-wrapper form-edit rounded'>
                            <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code}}&action=true'>
                                <div class='form-group'>
                                    <label for='link' class='col-sm-6 control-label-notes'>{{ trans('langLink') }}:</label>
                                    <div class='col-sm-12'>
                                        <input id='link' class='form-control' type='text' name='link' size='50' value='http://'>
                                    </div>
                                </div>

                          


                                <div class='form-group mt-4'>
                                    <label for='name_link' class='col-sm-6 control-label-notes'>{{ trans('langLinkName') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' name='name_link' size='50'>
                                    </div>
                                </div>

                               


                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-end align-items-center'>
                                    <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langAdd') }}'>
                                    </div>
                                </div>
                                {!! $csrf !!}
                            </form>
                        </div>
                    </div><div class='form-content-modules d-none d-lg-block'>
    <img class='form-image-modules' src='{{$urlAppend}}template/modern/img/form-image.png' alt='form-image'>
</div>
</div>
                </div>
            </div>

        </div>
    
</div>
</div>

@endsection