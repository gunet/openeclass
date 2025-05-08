@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')
            <div class="col_maincontent_active">

                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert')

                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit rounded'>
                                <form role='form' class='form-horizontal' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&session={{ $sessionID }}" method='post'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        <input type='hidden' name='resourceId' value='{{ $resource_id }}'>

                                        <div class='d-flex justify-content-end'>
                                            <div>
                                                <sup>
                                                    <small>(<span class='text-danger'>*</span>)</small>
                                                </sup>
                                                <small class='text-muted'>
                                                    {{ trans('langCPFFieldRequired') }}
                                                </small>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for='title' class='col-12 control-label-notes'>{{ trans('langTitle')}} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                            <div class='col-12'>
                                                <input id='title' type='text' name='title' class='form-control' value='{{ $title }}'>
                                                @if(Session::getError('title'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('title') !!}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='comments' class='col-12 control-label-notes'>{{ trans('langDescription')}}</label>
                                            {!! $comments !!}
                                        </div>

                                        {!! generate_csrf_token_form_field() !!}

                                        <div class='form-group mt-5'>
                                            <div class='col-12 d-flex justify-content-end aling-items-center'>
                                                <input class='btn submitAdminBtn' type='submit' name='modify_resource' value='{{ trans('langSubmit') }}'>
                                            </div>
                                        </div>

                                    </fieldset>
                                </form>
                            </div>
                        </div>
                        <div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>



@endsection
