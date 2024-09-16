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

                        <form class='form-horizontal' role='form' name='fieldForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                        <fieldset>
                            <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                            @if (isset($_GET['edit_field']))
                                <input type='hidden' name='field_id' value='{{ $fieldid }}'>
                            @else
                                <input type='hidden' name='catid' value='{{ $catid }}'>
                            @endif
                            <input type='hidden' name='datatype' value='{{ $datatype }}'>
                            <div class='form-group'>
                                <label for='name' class='col-sm-12 control-label-notes'>{{ trans('langName') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                <div class='col-sm-12'>
                                    <input id='name' type='text' name='field_name' class="form-control" value="{{ isset($name) ? $name : '' }}">
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label for='shortname' class='col-sm-12 control-label-notes'>
                                    {{ trans('langCPFShortName') }} <small>({{ trans('langCPFUniqueShortname') }}) <span class='asterisk Accent-200-cl'>(*)</span></small>
                                </label>
                                <div class='col-sm-12'>
                                    <input id='shortname' type='text' name='field_shortname' class="form-control" value="{{ isset($shortname) ? $shortname : '' }}">
                                </div>
                            </div>


                            <div class='form-group mt-4'>
                                <label for='fielddescr' class='col-sm-12 control-label-notes'>{{ trans('langDescription') }}</label>
                                <div class='col-sm-12'>
                                    {!! $fielddescr_rich_text !!}
                                </div>
                            </div>
                            @if (isset($_GET['edit_field']))

                                <div class='form-group mt-4'>
                                    <label for='datatype' class='col-sm-12 control-label-notes'>{{ trans('langCPFFieldDatatype') }}</label>
                                    <div class='col-sm-12'>
                                        {!! selection($field_types, 'datatype_disabled', $datatype, 'class="form-control" id="datatype" disabled') !!}
                                    </div>
                                </div>
                            @endif

                            <div class='form-group mt-4'>
                                <label for='required' class='col-sm-12 control-label-notes'>{{ trans('langCPFFieldRequired') }}</label>
                                <div class='col-sm-12'>
                                    {!! selection($yes_no, 'required', isset($required) ? $required : '', 'class="form-control" id="required"') !!}
                                </div>
                            </div>
                            @if ($datatype == CPF_MENU)

                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes'>
                                        {{ trans('langCPFMenuOptions') }} <small>({{ trans('langCPFMenuOptionsExplan') }})</small>
                                    </div>
                                    <div class='col-sm-12'>
                                        <textarea aria-label="{{ trans('langCPFMenuOptionsExplan') }}" name='options' rows='8' cols='20' class="form-control">{{ isset($textarea_val) ? $textarea_val : '' }}</textarea>
                                    </div>
                                </div>
                            @endif

                            <div class='form-group mt-4'>
                                <label for='registration' class='col-sm-12 control-label-notes'>{{ trans('langCPFFieldRegistration') }}</label>
                                <div class='col-sm-12'>
                                    {!! selection($yes_no, 'registration', isset($registration) ? $registration : '', 'class="form-control" id="registration"') !!}
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label for='visibility' class='col-sm-12 control-label-notes'>{{ trans('langCPFFieldVisibility') }}</label>
                                <div class='col-sm-12'>
                                    {!! selection($visibility, 'visibility', isset($vis) ? $vis : 10, 'class="form-control" id="visibility"') !!}
                                </div>
                            </div>

                            <div class='col-12 mt-5 d-flex justify-content-end align-items-center'>
                                {!! showSecondFactorChallenge() !!}
                                <input class='btn submitAdminBtn' type='submit' name='submit_field' value='{{ trans('langAdd') }}'>
                            </div>
                        </fieldset>
                        {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div></div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                    </div>

        </div>
</div>
</div>

<script language="javaScript" type="text/javascript">
    //<![CDATA[
        var chkValidator  = new Validator("fieldForm");
        chkValidator.addValidation("field_name", "req", "{{ trans('langCPFFieldNameAlert') }}");
        chkValidator.addValidation("field_shortname", "req", "{{ trans('langCPFFieldShortNameAlert') }}");
        @if ($datatype == CPF_MENU)
            chkValidator.addValidation("options", "req", "{{ trans('langCPFMenuOptionsAlert') }}");
        @endif
    //]]>
</script>
@endsection
