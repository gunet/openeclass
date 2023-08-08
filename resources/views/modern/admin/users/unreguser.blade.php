@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row rowMargin">

                    @if(!get_config('mentoring_always_active') and !get_config('mentoring_platform'))
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    @endif

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

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

                    @if ($u_account && $c)
                    <div class='col-12'>
                        
                            <div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>
                                {{ trans('langConfirmDeleteQuestion1') }} 
                                <em>{{ $u_realname }} ({{ $u_account }})</em>
                                {{ trans('langConfirmDeleteQuestion2') }} 
                                <em>{{ course_id_to_title($c) }}</em></span>
                            </div>
                            <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                <a class='btn submitAdminBtn' href='{{ $_SERVER['SCRIPT_NAME'] }}?u={{ $u }}&amp;c={{ $c }}&amp;doit=yes'>{{ trans('langDelete') }}</a>
                            </div>
                       
                    </div>
                    @else
                    <div class='col-12'>
                        <div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>{{ trans('langErrorUnreguser') }}</span></div>
                    </div>
                    @endif
               
        </div>
</div>
</div>              
@endsection