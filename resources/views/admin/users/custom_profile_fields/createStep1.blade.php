@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' name='fieldForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
            <fieldset>
            <input type='hidden' name='catid' value='{{ getIndirectReference($catid) }}'>
            <div class='form-group'>
                <label for='datatype' class='col-sm-2 control-label'>{{ trans('langCPFFieldDatatype') }}</label>
                <div class='col-sm-10'>
                    {!! selection($field_types, 'datatype', 1, 'class="form-control"') !!}
                </div>
            </div>
            <div class='col-sm-offset-2 col-sm-10'>
                <input class='btn btn-primary' type='submit' name='add_field_proceed_step2' value='{{ trans('langNext') }}'>
            </div>
            </fieldset>
            {!! generate_csrf_token_form_field() !!}
        </form>
    </div>
@endsection