@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}

    <div class='form-wrapper'>
        <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}{{ isset($mynode) ? '?action=edit' : '?action=add' }}' onsubmit='return validateNodePickerForm();'>
        <fieldset>
            <div class='form-group'>
                <label class='col-sm-3 control-label'>{{ trans('langNodeCode1') }}:</label>
                <div class='col-sm-9'>
                    <input class='form-control' type='text' name='code' value='{{ isset($mynode) ? $mynode->code : "" }}'>
                    &nbsp;<i>{{ trans('langCodeFaculte2') }}</i>
                </div>
            </div>
            @foreach ($session->active_ui_languages as $key => $langcode)
                <div class='form-group'>
                    <label class='col-sm-3 control-label'>{{ trans('langNodeName') }}:</label>
                    <div class='col-sm-9'>
                            @if (isset($is_serialized) && $is_serialized && isset($names[$langcode]))
                                <input class='form-control' type='text' name='name-{{ $langcode }}' value='{{ $names[$langcode] }}' placeholder='{{ trans('langFaculte2') }} ({{ trans("langNameOfLang['".langcode_to_name($langcode)."']")}})'>                        
                            @elseif (isset($is_serialized) && !$is_serialized && $key == 0)
                                <input class='form-control' type='text' name='name-{{ $langcode }}' value='{{ $mynode->name }}' placeholder='{{ trans('langFaculte2') }} ({{ trans("langNameOfLang['".langcode_to_name($langcode)."']")}})'>                        
                            @else
                                <input class='form-control' type='text' name='name-{{ $langcode }}' value='' placeholder='{{ trans('langFaculte2') }} ({{ trans("langNameOfLang['".langcode_to_name($langcode)."']")}})'>
                            @endif
                    </div>
                </div>
            @endforeach            
            <div class='form-group'>
                <label class='col-sm-3 control-label'>{{ trans('langNodeParent') }}:</label>
                <div class='col-sm-9'>
                    {!! $html !!}
                    <span class='help-block'>
                        <small>{{ trans('langNodeParent2') }}</small>
                    </span>
                </div>
            </div>        
            <div class='form-group'>
                <label class='col-sm-3 control-label'>{{ trans('langNodeAllowCourse') }}:</label>
                <div class='col-sm-9'>
                    <input type='checkbox' name='allow_course' value='1'{!! isset($mynode) && $mynode->allow_course == 1 ? " checked" : '' !!}>
                    <span class='help-block'>
                        <small>{{ trans('langNodeAllowCourse2') }}</small>
                    </span>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-3 control-label'>{{ trans('langNodeAllowUser') }}</label>
                <div class='col-sm-9'>
                    <input type='checkbox' name='allow_user' value='1'{!! isset($mynode) && $mynode->allow_user == 1 ? " checked" : '' !!}>
                    <span class='help-block'>
                        <small>{{ trans('langNodeAllowUser2') }}</small>
                    </span>
                </div>
            </div>
        <div class='form-group'>
            <label class='col-sm-3 control-label'>{{ trans('langNodeOrderPriority') }}</label>      
            <div class='col-sm-9'>
                <input class='form-control' type='text' name='order_priority' value='{{ isset($mynode) ? $mynode->order_priority : '' }}'>
                <span class='help-block'>
                    <small>{{ trans('langNodeOrderPriority2') }}</small>
                </span>
            </div>
        </div>
        @if (isset($mynode))
            <input type='hidden' name='id' value='{{ getIndirectReference($id) }}'>
            <input type='hidden' name='oldparentid' value='{{ getIndirectReference($formOPid) }}'>
            <input type='hidden' name='lft' value='{{ $mynode->lft }}'>
            <input type='hidden' name='rgt' value='{{ $mynode->rgt }}'>
        @endif
        {!! showSecondFactorChallenge() !!}
        <div class='form-group'>
            <div class='col-sm-9 col-sm-offset-3'>
                {!! form_buttons([
                    [
                        'text' => trans('langSave'),
                        'name' => isset($mynode) ? 'edit' : 'add',
                        'value'=> isset($mynode) ? trans('langAcceptChanges') : trans('langAdd')
                    ],
                    [
                        'href' => $_SERVER['SCRIPT_NAME']
                    ]
                ]) !!}
            </div>
        </div>        
        </fieldset>
        {!! generate_csrf_token_form_field() !!}
        </form>
    </div>   
@endsection