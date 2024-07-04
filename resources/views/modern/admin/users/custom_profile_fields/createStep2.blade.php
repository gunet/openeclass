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


                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp

                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif





                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>

                        <form class='form-horizontal' role='form' name='fieldForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                        <fieldset>
                            @if (isset($_GET['edit_field']))
                                <input type='hidden' name='field_id' value='{{ $fieldid }}'>
                            @else
                                <input type='hidden' name='catid' value='{{ $catid }}'>
                            @endif
                            <input type='hidden' name='datatype' value='{{ $datatype }}'>
                            <div class='form-group'>
                                <label for='name' class='col-sm-12 control-label-notes'>{{ trans('langName') }}</label>
                                <div class='col-sm-12'>
                                    <input id='name' type='text' name='field_name' class="form-control" value="{{ isset($name) ? $name : '' }}">
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label for='shortname' class='col-sm-12 control-label-notes'>
                                    {{ trans('langCPFShortName') }} <small>({{ trans('langCPFUniqueShortname') }})</small>
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
                                        {!! selection($field_types, 'datatype_disabled', $datatype, 'class="form-control" disabled') !!}
                                    </div>
                                </div>
                            @endif

                            <div class='form-group mt-4'>
                                <label for='required' class='col-sm-12 control-label-notes'>{{ trans('langCPFFieldRequired') }}</label>
                                <div class='col-sm-12'>
                                    {!! selection($yes_no, 'required', isset($required) ? $required : '', 'class="form-control"') !!}
                                </div>
                            </div>
                            @if ($datatype == CPF_MENU)

                                <div class='form-group mt-4'>
                                    <label for='options' class='col-sm-12 control-label-notes'>
                                        {{ trans('langCPFMenuOptions') }} <small>({{ trans('langCPFMenuOptionsExplan') }})</small>
                                    </label>
                                    <div class='col-sm-12'>
                                        <textarea name='options' rows='8' cols='20' class="form-control">{{ isset($textarea_val) ? $textarea_val : '' }}</textarea>
                                    </div>
                                </div>
                            @endif

                            <div class='form-group mt-4'>
                                <label for='registration' class='col-sm-12 control-label-notes'>{{ trans('langCPFFieldRegistration') }}</label>
                                <div class='col-sm-12'>
                                    {!! selection($yes_no, 'registration', isset($registration) ? $registration : '', 'class="form-control"') !!}
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label for='visibility' class='col-sm-12 control-label-notes'>{{ trans('langCPFFieldVisibility') }}</label>
                                <div class='col-sm-12'>
                                    {!! selection($visibility, 'visibility', isset($vis) ? $vis : 10, 'class="form-control"') !!}
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
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
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
