@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert') 

                    @if (!$deps_valid)
                        <div class='col-12'>
                            <div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>
                                {{ trans('langCreateCourseNotAllowedNode') }}</span>
                            </div>
                            <p class='float-end'>
                                <a class='btn btn-secondary' href='create_course.php'>{{ trans('langBack') }}</a>
                            </p>
                        </div>
                    @else
                    <div class='col-12'>
                        <div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>
                            <b>{{ trans('langJustCreated') }} :</b> {{ $title }}<br>
                            <span class='smaller'>{{ trans('langEnterMetadata') }}</span></span>
                        </div>
                    </div>
                    @endif
                
        </div>
  
</div>
</div>

@endsection
