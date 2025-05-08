@extends('layouts.default')
    @section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class="row m-auto">
                @if(isset($_SESSION['uid']))
                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                @endif

                @include('layouts.partials.show_alert')

                <div class='col-12 @if (isset($_SESSION['uid'])) mt-4 @endif'>
                    <h1>{{ trans('langCourses') }}</h1>
                </div>
                <div class='col-12 mt-4'>
                    <div class='row row-cols-1 row-cols-lg-2 g-lg-5 g-4'>
                        <div class='col-lg-6 col-12'>
                            <ul class='list-group list-group-flush'>
                                {!! $tree !!}
                            </ul>
                        </div>
                        <div class='col-lg-6 col-12 d-none d-lg-block text-end'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt='{{ trans('langImgFormsDes') }}'>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
