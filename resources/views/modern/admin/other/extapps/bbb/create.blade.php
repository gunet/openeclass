@push('head_scripts')
    <script type='text/javascript'>
        $(document).ready(function() {
            $('#select-courses').select2();
            $('#selectAll').click(function(e) {
                e.preventDefault();
                var stringVal = [];
                $('#select-courses').find('option').each(function(){
                    stringVal.push($(this).val());
                });
                $('#select-courses').val(stringVal).trigger('change');
            });
            $('#removeAll').click(function(e) {
                e.preventDefault();
                var stringVal = [];
                $('#select-courses').val(stringVal).trigger('change');
            });
            $('#allCourses').click(function(e) {
                var sc = $('#select-courses');
                e.preventDefault();
                if (!sc.find('option[value=0]').length) {
                    sc.prepend('<option value=\"0\">" . js_escape($langToAllCourses) . "</option>');
                }
                $('#select-courses').val(['0']).trigger('change');
            });
        });
    </script>
@endpush

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
                    <form class='form-horizontal' role='form' name='serverForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                    <fieldset>
                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                        <div class='form-group mt-4'>
                            <label for='hostname_form' class='col-sm-12 control-label-notes'>{{ trans('langName') }} <span class='Accent-200-cl'>(*)</span></label>
                            <div class='col-sm-12'>
                                <input class='form-control' placeholder="{{ trans('langName') }}" type='text' id='hostname_form' name='hostname_form' value='{{ isset($server) ? $server->hostname : ""}}'>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='api_url_form' class='col-sm-12 control-label-notes'>API URL <span class='Accent-200-cl'>(*)</span></label>
                            <div class='col-sm-12'>
                                <input class='form-control' placeholder="api url..." type='text' id='api_url_form' name='api_url_form' value='{{ isset($server) ? $server->api_url : "" }}'>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='key_form' class='col-sm-12 control-label-notes'>{{ trans('langPresharedKey') }}</label>
                            <div class='col-sm-12'>
                                <input id='key_form' class='form-control' placeholder="{{ trans('langPresharedKey') }}..." type='text' name='key_form' value='{{ isset($server) ? $server->server_key : "" }}'>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='max_rooms_for' class='col-sm-12 control-label-notes'>{{ trans('langMaxRooms') }}</label>
                            <div class='col-sm-12'>
                                <input class='form-control' type='text' placeholder="{{ trans('langMaxRooms') }}..." id='max_rooms_for' name='max_rooms_form' value='{{ isset($server) ? $server->max_rooms : "" }}'>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='max_users_form' class='col-sm-12 control-label-notes'>{{ trans('langMaxUsers') }}</label>
                            <div class='col-sm-12'>
                                <input class='form-control' type='text' placeholder="{{ trans('langMaxUsers') }}..." id='max_users_form' name='max_users_form' value='{{ isset($server) ? $server->max_users : "" }}'>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <div class='col-sm-12 control-label-notes'>{{ trans('langBBBEnableRecordings') }}</div>
                            <div class="col-sm-12 d-inline-flex">
                                <div class='radio'>
                                    <label>
                                        <input  type='radio' id='recordings_on' name='enable_recordings' value='true'{{ $enabled_recordings ? ' checked' : '' }}>
                                        {{ trans('langYes') }}
                                    </label>
                                </div>
                                <div class='radio ms-4'>
                                    <label>
                                        <input  type='radio' id='recordings_off' name='enable_recordings' value='false'{{ $enabled_recordings ? '' : ' checked' }}>
                                        {{ trans('langNo') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <div class='col-sm-12 control-label-notes'>{{ trans('langActivate') }}</div>
                            <div class="col-sm-12 d-inline-flex">
                                <div class='radio'>
                                    <label>
                                        <input  type='radio' id='enabled_true' name='enabled' value='true'{{ $enabled ? ' checked' : '' }}>
                                        {{ trans('langYes') }}
                                    </label>
                                </div>
                                <div class='radio ms-4'>
                                    <label>
                                        <input  type='radio' id='enabled_false' name='enabled' value='false'{{ $enabled ? '' : ' checked' }}>
                                        {{ trans('langNo') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='weight' class='col-sm-12 control-label-notes'>{{ trans('langBBBServerOrder') }}</label>
                            <div class='col-sm-12'>
                                <input id='weight' class='form-control' type='text' placeholder="{{ trans('langBBBServerOrder') }}..." name='weight' value='{{ isset($server) ? $server->weight : "" }}'>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='select-courses' class='col-sm-12 control-label-notes'>{{ trans('langUseOfTc') }}</label>
                            <div class="col-sm-12">
                                <select class='form-select' name='tc_courses[]' multiple class='form-control' id='select-courses'>
                                    {!! $listcourses !!}
                                </select>
                                <a href='#' id='selectAll'>{{ trans('langJQCheckAll') }}</a> | <a href='#' id='removeAll'>{{ trans('langJQUncheckAll') }}</a>
                            </div>
                        </div>
                        @if (isset($server))
                            <input class='form-control' type = 'hidden' name = 'id_form' value='{{ getIndirectReference($bbb_server) }}'>
                        @endif
                        <div class='form-group mt-5'>
                            <div class='col-12 d-flex justify-content-end align-items-center'>
                                <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langAddModify') }}'>
                            </div>
                        </div>
                    </fieldset>
                    </form>
                </div>
            </div>
            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var chkValidator  = new Validator("serverForm");
    chkValidator.addValidation("key_form","req","{{ trans('langBBBServerAlertKey') }}");
    chkValidator.addValidation("api_url_form","req","{{ trans('langBBBServerAlertAPIUrl') }}");
    chkValidator.addValidation("max_rooms_form","req","{{ trans('langBBBServerAlertMaxRooms') }}");
    chkValidator.addValidation("max_rooms_form","numeric","{{ trans('langBBBServerAlertMaxRooms') }}");
    chkValidator.addValidation("max_users_form","req","{{ trans('langBBBServerAlertMaxUsers') }}");
    chkValidator.addValidation("max_users_form","numeric","{{ trans('langBBBServerAlertMaxUsers') }}");
    chkValidator.addValidation("weight","req","{{ trans('langBBBServerAlertOrder') }}");
    chkValidator.addValidation("weight","numeric","{{ trans('langBBBServerAlertOrder') }}");
</script>

@endsection
