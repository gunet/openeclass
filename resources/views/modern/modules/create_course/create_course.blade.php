@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! $action_bar !!}

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 all-alerts'>
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

                    @if (!$deps_valid)
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                            <div class='alert alert-danger'>
                                {{ trans('langCreateCourseNotAllowedNode') }}
                            </div>
                            <p class='float-end'>
                                <a class='btn btn-secondary' href='create_course.php'>{{ trans('langBack') }}</a>
                            </p>    
                        </div>
                    @else
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='alert alert-success'>
                            <b>{{ trans('langJustCreated') }} :</b> {{ $title }}<br>
                            <span class='smaller'>{{ trans('langEnterMetadata') }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection