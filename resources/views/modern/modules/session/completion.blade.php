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

                    <div class='d-lg-flex gap-4'>
                        <div class='flex-grow-1'>
                            @if(count($all_sessions) > 0)
                                <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{!! trans('langCompletedConsultingInfo') !!}</span></div>
                                <div class='form-wrapper form-edit'>
                                    <form role='form' class='form-horizontal' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}" method='post'>
                                        <fieldset>
                                            <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                            <div class='form-group'>
                                                <div class='table-responsive mt-0'>
                                                    <table class='table-default'>
                                                        <thead>
                                                            <tr>
                                                                <th>{{ trans('langSession') }}</th>
                                                                <th aria-label="{{ trans('langSettingSelect') }}"></th>
                                                            </tr>
                                                        </thead>
                                                        @foreach($all_sessions as $s)
                                                            <tr>
                                                                <td>
                                                                    <a class='link-color' href='{{ $urlAppend }}modules/session/session_space.php?course={{ $course_code }}&session={{ $s->id }}'>
                                                                        {{ $s->title }}
                                                                    </a>
                                                                </td>
                                                                <td>
                                                                    <div class='d-flex justify-content-end'>
                                                                        <div class='checkbox'>
                                                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                                                <input type='checkbox' name='sessions_completed[]' value='{{ $s->id }}' {!! in_array($s->id,$session_ids) ? 'checked' : '' !!}>
                                                                                <span class='checkmark'></span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                </div>
                                            </div>
                                            {!! generate_csrf_token_form_field() !!}    
                                            <div class='form-group mt-5'>
                                                <div class='col-12 d-flex justify-content-end aling-items-center'>
                                                    <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </form>
                                </div>
                            @else
                                <div class='alert alert-warning'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                    <span>{{ trans('langNoInfoAvailable')}}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>






@endsection
