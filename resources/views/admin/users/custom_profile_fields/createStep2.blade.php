@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' name='fieldForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
        <fieldset>
            @if (isset($_GET['edit_field']))
                <input type='hidden' name='field_id' value='{{ getIndirectReference($fieldid) }}'>
            @else
                <input type='hidden' name='catid' value='{{ getIndirectReference($catid) }}'>
            @endif
            <input type='hidden' name='datatype' value='{{ $datatype }}'>
            <div class='form-group'>
                <label for='name' class='col-sm-2 control-label'>{{ trans('langName') }}</label>
                <div class='col-sm-10'>
                    <input id='name' type='text' name='field_name' class="form-control" value="{{ isset($name) ? $name : '' }}">
                </div>
            </div>
            <div class='form-group'>
                <label for='shortname' class='col-sm-2 control-label'>
                    {{ trans('langCPFShortName') }} <small>({{ trans('langCPFUniqueShortname') }})</small>
                </label>
                <div class='col-sm-10'>
                    <input id='shortname' type='text' name='field_shortname' class="form-control" value="{{ isset($shortname) ? $shortname : '' }}">
                </div>
            </div>
            <div class='form-group'>
                <label for='fielddescr' class='col-sm-2 control-label'>{{ trans('langDescription') }}</label>
                <div class='col-sm-10'>
                    {!! $fielddescr_rich_text !!}
                </div>
            </div>
            @if (isset($_GET['edit_field']))
                <div class='form-group'>
                    <label for='datatype' class='col-sm-2 control-label'>{{ trans('langCPFFieldDatatype') }}</label>
                    <div class='col-sm-10'>
                        {!! selection($field_types, 'datatype_disabled', $datatype, 'class="form-control" disabled') !!}
                    </div>
                </div>           
            @endif
            <div class='form-group'>
                <label for='required' class='col-sm-2 control-label'>{{ trans('langCPFFieldRequired') }}</label>
                <div class='col-sm-10'>
                    {!! selection($yes_no, 'required', isset($required) ? $required : '', 'class="form-control"') !!}
                </div>
            </div>
            @if ($datatype == CPF_MENU)
                <div class='form-group'>
                    <label for='options' class='col-sm-2 control-label'>
                        {{ trans('langCPFMenuOptions') }} <small>({{ trans('langCPFMenuOptionsExplan') }})</small>
                    </label>
                    <div class='col-sm-10'>
                        <textarea name='options' rows='8' cols='20' class="form-control">{{ isset($textarea_val) ? $textarea_val : '' }}</textarea>
                    </div>
                </div>
            @endif
            <div class='form-group'>
                <label for='registration' class='col-sm-2 control-label'>{{ trans('langCPFFieldRegistration') }}</label>
                <div class='col-sm-10'>
                    {!! selection($yes_no, 'registration', isset($registration) ? $registration : '', 'class="form-control"') !!}
                </div>
            </div>
            <div class='form-group'>
                <label for='user_type' class='col-sm-2 control-label'>{{ trans('langCPFFieldUserType') }}</label>
                <div class='col-sm-10'>
                    {!! selection($user_type, 'user_type', isset($utype) ? $utype : 10, 'class="form-control"') !!}
                </div>
            </div>
            <div class='form-group'>
                <label for='visibility' class='col-sm-2 control-label'>{{ trans('langCPFFieldVisibility') }}</label>
                <div class='col-sm-10'>
                    {!! selection($visibility, 'visibility', isset($vis) ? $vis : 10, 'class="form-control"') !!}
                </div>
            </div>
            <div class='col-sm-offset-2 col-sm-10'>
                {!! showSecondFactorChallenge() !!}
                <input class='btn btn-primary' type='submit' name='submit_field' value='{{ trans('langAdd') }}'>
            </div>
        </fieldset>
        {!! generate_csrf_token_form_field() !!}
        </form>
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