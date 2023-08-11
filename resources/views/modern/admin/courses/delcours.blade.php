@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }}'>

        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

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

                    <div class='col-12'>
                        <div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>
                            {{ trans('langCourseDelConfirm2') }}
                            <em>{{ course_id_to_title($course_id) }}</em>
                            <br><br>
                            <i>{{ trans('langNoticeDel') }}</i>
                            <br></span>
                        </div>
                    </div>

                    <div class='col-12'>
                        <ul class='list-group'>
                            <li class='list-group-item'>
                                <a href='{{ $_SERVER['SCRIPT_NAME'] }}?c={{ $course_id }}&amp;delete=yes&amp;{{ generate_csrf_token_link_parameter() }}' {!! $asktotp !!}>
                                <b>{{ trans('langYes') }}</b>
                                </a>
                            </li>
                            <li class='list-group-item'>
                                <a href='listcours.php'>
                                    <b>{{ trans('langNo') }}</b>
                                </a>
                            </li>
                        </ul>
                    </div>

                
        </div>
    </div>
</div>
@endsection
