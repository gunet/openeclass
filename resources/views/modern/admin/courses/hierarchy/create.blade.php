@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebarAdmin')
                </div>
            </div>

            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>
                        
                    
                        <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                        @include('layouts.partials.sidebarAdmin')
                        </div>
                    </div>

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='form-wrapper shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
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
