@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' name='catForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
        <fieldset>
            @if ($catid)
            <input type='hidden' name='cat_id' value='{{ getIndirectReference($catid) }}'>
            @endif
            <div class='form-group'>
                <label for='catname' class='col-sm-2 control-label'>{{ trans('langName') }}</label>
                <div class='col-sm-10'>
                    <input id='catname' class="form-control" type='text' name='cat_name' value="{{ $cat_name ?: '' }}">
                </div>
            </div>
            <div class='col-sm-offset-2 col-sm-10'>
                {!! showSecondFactorChallenge() !!}
                <input class='btn btn-primary' type='submit' name='submit_cat' value='{{ trans('langAdd') }}'>
            </div>
        </fieldset>
        {!! generate_csrf_token_form_field() !!}
        </form>
    </div>
    <script language="javaScript" type="text/javascript">
    //<![CDATA[
        var chkValidator  = new Validator("catForm");
        chkValidator.addValidation("catname","req","{{ trans('langCPFCategoryNameAlert') }}");
    //]]></script>
@endsection