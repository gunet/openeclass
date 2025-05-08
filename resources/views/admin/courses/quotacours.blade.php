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

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>

                            <form role='form' class='form-horizontal' action="{{ $_SERVER['SCRIPT_NAME'] }}?c={{ $course->code }}" method='post'>
                                <fieldset>
                                    <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                    <div class='alert alert-info mt-0'><i class='fa-solid fa-circle-info fa-lg'></i><span>
                                        {{ trans('langTheCourse') }} <b>{{ $course->title }}</b> {{ trans('langMaxQuota') }}</span>
                                    </div>
                                    <div class='form-group'>
                                        <div class='col-sm-12 control-label-notes'>{{ trans('langLegend') }} {{ trans('langDoc') }}</div>
                                            <div class='col-sm-12'><input class='form-control' type='text' name='dq' value='{{ $dq }}' size='4' maxlength='4'> MB</div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <div class='col-sm-12 control-label-notes'>{{ trans('langLegend') }} {{ trans('langVideo') }}</div>
                                            <div class='col-sm-12'><input class='form-control' type='text' name='vq' value='{{ $vq }}' size='4' maxlength='4'> MB</div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <div class='col-sm-12 control-label-notes'>{{ trans('langLegend') }} {{ trans('langGroups') }}</div>
                                        <div class='col-sm-12'>
                                            <input class='form-control' type='text' name='gq' value='{{ $gq }}' size='4' maxlength='4'> MB
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <div class='col-sm-12 control-label-notes'>{{ trans('langLegend') }} {{ trans('langDropBox') }}</div>
                                        <div class='col-sm-12'>
                                            <input class='form-control' type='text' name='drq' value='{{ $drq }}' size='4' maxlength='4'> MB
                                        </div>
                                    </div>
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-end align-items-center'>
                                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                        </div>
                                    </div>
                                </fieldset>
                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    </div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                    </div>

        </div>
    </div>
</div>
@endsection
