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
            @foreach ($session->active_ui_languages as $key => $langcode)
                <div class='form-group'>
                    <label class='col-sm-3 control-label'>{{ trans('langNodeDescription') }}:</label>
                    <div class='col-sm-9'>
                        @if (isset($desc_is_ser) && $desc_is_ser && isset($names[$langcode]))
                            {!! rich_text_editor('description-' . $langcode, 8, 20, $descriptions[$langcode]) !!}
                        @elseif (isset($desc_is_ser) && !$desc_is_ser && $key == 0)
                            {!! rich_text_editor('description-' . $langcode, 8, 20, $mynode->description) !!}
                        @else
                            {!! rich_text_editor('description-' . $langcode, 8, 20, $GLOBALS['langFaculte2'] . " (" . $GLOBALS['langNameOfLang'][langcode_to_name($langcode)] . ")") !!}
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

        <div class='form-group'>
            <label class='col-sm-3 control-label'>{{ trans('langAvailableTypes') }}</label>
            <div class='col-sm-9'>
                <div class='radio'>
                    <label>
                        <input id='nodeopen' type='radio' name='visible' value='2' {{ $visibleChecked[2] }}>
                        <span class='fa fa-unlock fa-fw' style='font-size:23px;'></span>&nbsp;{{ trans('langNodePublic') }}
                        <span class='help-block'><small>{{ trans('langNodePublic2') }}</small></span>
                    </label>
                </div>
                <div class='radio'>
                    <label>
                        <input id='nodeforsubscribed' type='radio' name='visible' value='1' {{ $visibleChecked[1] }}>
                        <span class='fa fa-lock fa-fw'  style='font-size:23px;'>
                            <span class='fa fa-pencil text-danger fa-custom-lock' style='font-size:16px; position:absolute; top:13px; left:35px;'></span>
                        </span>&nbsp;{{ trans('langNodeSubscribed') }}
                        <span class='help-block'><small>{{ trans('langNodeSubscribed2') }}</small></span>
                    </label>
                </div>
                <div class='radio'>
                    <label>
                        <input id='nodehidden' type='radio' name='visible' value='0' {{ $visibleChecked[0] }}>
                        <span class='fa fa-lock fa-fw' style='font-size:23px;'></span>&nbsp;{{ trans('langNodeHidden') }}
                        <span class='help-block'><small>{{ trans('langNodeHidden2') }}</small></span>
                    </label>
                </div>
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