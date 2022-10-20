@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif
                    
                    {!! isset($action_bar) ?  $action_bar : '' !!}


                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit p-3 rounded'>
                            
                            <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}{{ isset($mynode) ? '?action=edit' : '?action=add' }}' onsubmit='return validateNodePickerForm();'>
                            <fieldset>
                                <div class='form-group'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langNodeCode1') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' placeholder="{{ trans('langNodeCode1') }}..." type='text' name='code' value='{{ isset($mynode) ? $mynode->code : "" }}'>
                                        &nbsp;<i>{{ trans('langCodeFaculte2') }}</i>
                                    </div>
                                </div>
                                @foreach ($session->active_ui_languages as $key => $langcode)
                                    <div class='form-group mt-3'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langNodeName') }}</label>
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
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langNodeDescription') }}</label>
                                        <div class='col-sm-12'>
                                            {!! rich_text_editor('description-' . $langcode, 8, 20, $descriptions[$langcode]) !!}
                                        </div>
                                    </div>
                                @endforeach
                                <div class='form-group mt-3'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langNodeParent') }}</label>
                                    <div class='col-sm-12'>
                                        {!! $html !!}
                                        <span class='help-block'>
                                            <small>{{ trans('langNodeParent2') }}</small>
                                        </span>
                                    </div>
                                </div>
                                <div class='form-group mt-3'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langNodeAllowCourse') }}</label>
                                    <div class='col-sm-12'>
                                        <input type='checkbox' name='allow_course' value='1'{!! isset($mynode) && $mynode->allow_course == 1 ? " checked" : '' !!}>
                                        <span class='help-block'>
                                            <small>{{ trans('langNodeAllowCourse2') }}</small>
                                        </span>
                                    </div>
                                </div>
                                <div class='form-group mt-3'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langNodeAllowUser') }}</label>
                                    <div class='col-sm-12'>
                                        <input type='checkbox' name='allow_user' value='1'{!! isset($mynode) && $mynode->allow_user == 1 ? " checked" : '' !!}>
                                        <span class='help-block'>
                                            <small>{{ trans('langNodeAllowUser2') }}</small>
                                        </span>
                                    </div>
                                </div>
                            <div class='form-group mt-3'>
                                <label class='col-sm-12 control-label-notes'>{{ trans('langNodeOrderPriority') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='order_priority' value='{{ isset($mynode) ? $mynode->order_priority : '' }}'>
                                    <span class='help-block'>
                                        <small>{{ trans('langNodeOrderPriority2') }}</small>
                                    </span>
                                </div>
                            </div>

                            <div class='form-group mt-3'>
                                <label class='col-sm-12 control-label-notes'>{{ trans('langAvailableTypes') }}</label>
                                <div class='col-sm-12'>
                                    <div class='radio d-inline-flex align-items-top'>
                                        <label>
                                            <input id='nodeopen' type='radio' name='visible' value='2' {{ $visibleChecked[2] }}>
                                            <span class='fa fa-unlock fa-fw mt-1'></span>&nbsp;{{ trans('langNodePublic') }}
                                        </label>

                                    </div> 
                                    <div class='help-block'><small>{{ trans('langNodePublic2') }}</small></div>

                                    <div class='radio d-inline-flex align-items-top mt-3'>
                                        <label>
                                            <input id='nodeforsubscribed' type='radio' name='visible' value='1' {{ $visibleChecked[1] }}>
                                            <span class='fa fa-lock fa-fw mt-1'></span>
                                            <span class='fa fa-pencil text-danger fa-custom-lock pen-hierarchy'></span>
                                            <span class='ms-3'>{{ trans('langNodeSubscribed') }}</span>
                                        </label>
                                    </div>
                                    <div class='help-block'><small>{{ trans('langNodeSubscribed2') }}</small></div>

                                    <div class='radio d-inline-flex align-items-top mt-3'>
                                        <label>
                                            <input id='nodehidden' type='radio' name='visible' value='0' {{ $visibleChecked[0] }}>
                                            <span class='fa fa-lock fa-fw mt-1'></span>{{ trans('langNodeHidden') }}
                                        </label>
                                    </div>
                                    <div class='help-block'><small>{{ trans('langNodeHidden2') }}</small></div>
                                </div>
                            </div>

                            @if (isset($mynode))
                                <input type='hidden' name='id' value='{{ $id }}'>
                                <input type='hidden' name='oldparentid' value='{{ $formOPid }}'>
                                <input type='hidden' name='lft' value='{{ $mynode->lft }}'>
                                <input type='hidden' name='rgt' value='{{ $mynode->rgt }}'>
                            @endif
                            {!! showSecondFactorChallenge() !!}
                            <div class='form-group mt-5'>
                                <div class='col-12'>
                                    <div class='row'>
                                        <div class='col-6'>
                                            {!! form_buttons([
                                                [
                                                    'class' => 'btn-primary btn-sm submitAdminBtn w-100',
                                                    'text' => trans('langSave'),
                                                    'name' => isset($mynode) ? 'edit' : 'add',
                                                    'value'=> isset($mynode) ? trans('langAcceptChanges') : trans('langAdd')
                                                ]
                                            ]) !!}
                                        </div>
                                        <div class='col-6'>
                                        {!! form_buttons([
                                            [
                                                'class' => 'btn-secondary btn-sm cancelAdminBtn w-100',
                                                'href' => $_SERVER['SCRIPT_NAME']
                                            ]
                                        ]) !!}
                                        </div>
                                    </div>
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
