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

                    {!! $users_login_data !!}

                    @if($users_login_data) <div class='mt-3'></div> @endif

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                            <form class='form-horizontal' role='form' method='get' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                                <input type="hidden" name="u" value="{{ $u }}">
                                <div class='form-group' data-date='{{ $user_date_start }}' data-date-format='dd-mm-yyyy'>
                                    <label for='user_date_start' class='col-sm-12 control-label-notes'>{{ trans('langStartDate') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' name='user_date_start' id='user_date_start' type='text' value = '{{ $user_date_start }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-4' data-date= '{{ $user_date_end }}' data-date-format='dd-mm-yyyy'>
                                    <label for='user_date_end' class='col-sm-12 control-label-notes'>{{ trans('langEndDate') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' name='user_date_end' id='user_date_end' type='text' value= '{{ $user_date_end }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='log_type_id' class='col-sm-12 control-label-notes'>{{ trans('langLogTypes') }}</label>
                                    <div class='col-sm-12'>{!! selection($log_types, 'logtype', $logtype, "class='form-control' id='log_type_id'") !!}</div>
                                </div>
                                <div class="form-group mt-4">
                                    <label for="course_u_id" class="col-sm-12 control-label-notes">{{ trans('langCourse') }}</label>
                                    <div class="col-sm-12">{!! selection($cours_opts, 'u_course_id', $u_course_id, "class='form-control' id='course_u_id'") !!}</div>
                                </div>
                                <div class="form-group mt-4">
                                    <label for="u_id_module_id" class="col-sm-12 control-label-notes">{{ trans('langLogModules') }}</label>
                                    <div class="col-sm-12">{!! selection($module_names, 'u_module_id', '', "class='form-select' id='u_id_module_id'") !!}</div>
                                </div>
                                <div class="form-group mt-5">
                                    <div class="col-12 d-flex justify-content-end align-items-center gap-2">
                                        <input class="btn submitAdminBtn" type="submit" name="submit" value="{{ trans('langSubmit') }}">
                                        <a class="btn cancelAdminBtn" href="listusers.php" data-bs-placement="bottom" data-bs-toggle="tooltip" title="" data-bs-original-title="{{ trans('langBack') }}" aria-label="{{ trans('langBack') }}">
                                            <span class="fa fa-reply space-after-icon"></span>{{ trans('langBack') }}
                                        </a>
                                    </div>
                                </div>
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
