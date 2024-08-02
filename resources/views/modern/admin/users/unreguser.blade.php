@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    

                    @include('layouts.partials.legend_view')

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif
                    
                    @include('layouts.partials.show_alert') 

                    @if ($u_account && $c)
                        <div class='col-lg-6 col-12'>
                            
                                <div class='alert alert-warning'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                    <span>
                                        {{ trans('langConfirmDeleteQuestion1') }} 
                                        <em>{{ $u_realname }} ({{ $u_account }})</em>
                                        {{ trans('langConfirmDeleteQuestion2') }} 
                                        <em>{{ course_id_to_title($c) }}</em>
                                    </span>
                                </div>
                                <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                    <a class='btn deleteAdminBtn' href='{{ $_SERVER['SCRIPT_NAME'] }}?u={{ $u }}&amp;c={{ $c }}&amp;doit=yes'>{{ trans('langDelete') }}</a>
                                </div>
                        
                        </div>
                        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                        </div>
                    @else
                        <div class='col-12'>
                            <div class='alert alert-danger'>
                                <i class='fa-solid fa-circle-xmark fa-lg'></i>
                                <span>{{ trans('langErrorUnreguser') }}</span>
                            </div>
                        </div>
                    @endif
               
        </div>
</div>
</div>              
@endsection