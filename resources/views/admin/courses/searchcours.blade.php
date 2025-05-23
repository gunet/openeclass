@push('head_scripts')
    <script type='text/javascript'>
        $(function() {
            $('#id_date').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '{{ js_escape($language) }}',
                autoclose: true
            });
        });
    </script>
@endpush

@extends('layouts.default')

@section('content')

<div class="col-12 main-section searchcours">
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

                    <form role='form' class='form-horizontal' action='listcours.php?search=yes' method='get'>
                        <fieldset>
                            <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                            <div class='form-group'>
                                <label for='formsearchtitle' class='col-sm-12 control-label-notes'>{{ trans('langTitle') }}</label>
                                <div class='col-sm-12'>
                                    <input type='text' placeholder='{{ trans('langTitle') }}' class='form-control' id='formsearchtitle' name='formsearchtitle' value=''>
                                </div>
                            </div>
                            <div class='form-group mt-4'>
                                <label for='formsearchcode' class='col-sm-12 control-label-notes'>{{ trans('langCourseCode') }}</label>
                                <div class='col-sm-12'>
                                    <input id='formsearchcode' type='text' placeholder='{{ trans('langCourseCode') }}' class='form-control' name='formsearchcode' value=''>
                                </div>
                            </div>
                            <div class='form-group mt-4'>
                                <label for='formsearchtype' class='col-sm-12 control-label-notes'>{{ trans('langCourseVis') }}</label>
                                <div class='col-sm-12'>
                                    <select class='form-select' name='formsearchtype' id='formsearchtype'>
                                        <option value='-1'>{{ trans('langAllTypes') }}</option>
                                        <option value='2'>{{ trans('langTypeOpen') }}</option>
                                        <option value='1'>{{ trans('langTypeRegistration') }}</option>
                                        <option value='0'>{{ trans('langTypeClosed') }}</option>
                                        <option value='4'>{{ trans('langCourseActiveShort') }}</option>
                                        <option value='3'>{{ trans('langCourseInactiveShort') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label for='formprof' class='col-sm-12 control-label-notes'>{{ trans('langTeachers') }}:</label>
                                <div class='col-sm-12'>
                                    <input type='text' placeholder="{{ trans('langTeachers') }}" class='form-control' id='formprof' name='formsearchprof' value=''>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <div class='col-sm-12 control-label-notes'>{{ trans('langCreationDate') }}</div>
                                <div class='row'>
                                    <div class='col-6'>
                                        {!! selection($reg_flag_data, 'reg_flag', '', 'class="form-select" aria-label="Date creation"') !!}
                                    </div>
                                    <div class='col-6'>
                                        <input aria-label="{{ trans('langCreationDate') }}" class='form-control' id='id_date' name='date' type='text' value='' data-date-format='dd-mm-yyyy' placeholder='{{ trans('langCreationDate') }}'>
                                    </div>
                                </div>
                            </div>
                            <div class='form-group mt-4'>
                                <label for='dialog-set-value' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}</label>
                                <div class='col-sm-12'>
                                    {!! $html !!}
                                </div>
                            </div>
                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                   <input class='btn submitAdminBtn' type='submit' name='search_submit' value='{{ trans('langSearch') }}'>
                                   <a href='index.php' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
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
@endsection
