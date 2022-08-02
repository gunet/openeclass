@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif
                    
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='alert alert-danger'>
                            {{ trans('langCourseDelConfirm2') }} 
                            <em>{{ course_id_to_title($course_id) }}</em>
                            <br><br>
                            <i>{{ trans('langNoticeDel') }}</i>
                            <br>
                        </div>   
                    </div> 

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <ul class='list-group'>
                            <li class='list-group-item'>
                                <!-- <a href='{{ $_SERVER['SCRIPT_NAME'] }}?c={{ getIndirectReference($course_id) }}&amp;delete=yes&amp;{{ generate_csrf_token_link_parameter() }}' {!! $asktotp !!}> -->
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
    </div>
</div>
@endsection