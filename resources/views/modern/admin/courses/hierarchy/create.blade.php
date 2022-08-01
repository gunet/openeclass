@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif
                    
                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='form-wrapper shadow-sm p-3 mt-5 rounded'>
                            
                            <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}{{ isset($mynode) ? '?action=edit' : '?action=add' }}' onsubmit='return validateNodePickerForm();'>
                            <fieldset>
                                <div class='form-group mt-3'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langNodeCode1') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' name='code' value='{{ isset($mynode) ? $mynode->code : "" }}'>
                                        &nbsp;<i>{{ trans('langCodeFaculte2') }}</i>
                                    </div>
                                </div>
                                @foreach ($session->active_ui_languages as $key => $langcode)
                                    <div class='form-group mt-3'>
                                        <label class='col-sm-6 control-label-notes'>{{ trans('langNodeName') }}:</label>
                                        <div class='col-sm-12'>
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
                                    <div class='form-group mt-3'>
                                        <label class='col-sm-6 control-label-notes'>{{ trans('langNodeDescription') }}:</label>
                                        <div class='col-sm-12'>
                                            {!! rich_text_editor('description-' . $langcode, 8, 20, $descriptions[$langcode]) !!}
                                        </div>
                                    </div>
                                @endforeach
                                <div class='form-group mt-3'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langNodeParent') }}:</label>
                                    <div class='col-sm-12'>
                                        {!! $html !!}
                                        <span class='help-block'>
                                            <small>{{ trans('langNodeParent2') }}</small>
                                        </span>
                                    </div>
                                </div>
                                <div class='form-group mt-3'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langNodeAllowCourse') }}:</label>
                                    <div class='col-sm-12'>
                                        <input type='checkbox' name='allow_course' value='1'{!! isset($mynode) && $mynode->allow_course == 1 ? " checked" : '' !!}>
                                        <span class='help-block'>
                                            <small>{{ trans('langNodeAllowCourse2') }}</small>
                                        </span>
                                    </div>
                                </div>
                                <div class='form-group mt-3'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langNodeAllowUser') }}</label>
                                    <div class='col-sm-12'>
                                        <input type='checkbox' name='allow_user' value='1'{!! isset($mynode) && $mynode->allow_user == 1 ? " checked" : '' !!}>
                                        <span class='help-block'>
                                            <small>{{ trans('langNodeAllowUser2') }}</small>
                                        </span>
                                    </div>
                                </div>
                            <div class='form-group mt-3'>
                                <label class='col-sm-6 control-label-notes'>{{ trans('langNodeOrderPriority') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='order_priority' value='{{ isset($mynode) ? $mynode->order_priority : '' }}'>
                                    <span class='help-block'>
                                        <small>{{ trans('langNodeOrderPriority2') }}</small>
                                    </span>
                                </div>
                            </div>

                            <div class='form-group mt-3'>
                                <label class='col-sm-6 control-label-notes'>{{ trans('langAvailableTypes') }}</label>
                                <div class='col-sm-12'>
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
                                <input type='hidden' name='id' value='{{ $id }}'>
                                <input type='hidden' name='oldparentid' value='{{ $formOPid }}'>
                                <input type='hidden' name='lft' value='{{ $mynode->lft }}'>
                                <input type='hidden' name='rgt' value='{{ $mynode->rgt }}'>
                            @endif
                            {!! showSecondFactorChallenge() !!}
                            <div class='form-group mt-3'>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
