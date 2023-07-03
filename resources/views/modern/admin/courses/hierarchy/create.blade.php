@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

    <div class="row rowMargin">

        

            <div class="col-12 col_maincontent_active_Homepage">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

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
                    
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded'>
                            
                            <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}{{ isset($mynode) ? '?action=edit' : '?action=add' }}' onsubmit='return validateNodePickerForm();'>
                            <fieldset>
                                <div class='form-group'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langNodeCode1') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' placeholder="{{ trans('langNodeCode1') }}" type='text' name='code' value='{{ isset($mynode) ? $mynode->code : "" }}'>
                                        &nbsp;<i>{{ trans('langCodeFaculte2') }}</i>
                                    </div>
                                </div>
                                @foreach ($session->active_ui_languages as $key => $langcode)
                                    <div class='form-group mt-4'>
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
                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langNodeDescription') }}</label>
                                        <div class='col-sm-12'>
                                            {!! rich_text_editor('description-' . $langcode, 8, 20, $descriptions[$langcode]) !!}
                                        </div>
                                    </div>
                                @endforeach
                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langNodeParent') }}</label>
                                    <div class='col-sm-12'>
                                        {!! $html !!}
                                        <span class='help-block'>
                                            <small>{{ trans('langNodeParent2') }}</small>
                                        </span>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langNodeAllowCourse') }}</label>
                                    <div class='col-sm-12 checkbox'>
                                        <label>
                                            <input type='checkbox' name='allow_course' value='1'{!! isset($mynode) && $mynode->allow_course == 1 ? " checked" : '' !!}>
                                            {{ trans('langNodeAllowCourse2') }}
                                        </label>
                                        
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langNodeAllowUser') }}</label>
                                    <div class='col-sm-12 checkbox'>
                                        <label>
                                            <input type='checkbox' name='allow_user' value='1'{!! isset($mynode) && $mynode->allow_user == 1 ? " checked" : '' !!}>
                                            {{ trans('langNodeAllowUser2') }}
                                        </label>
                                       
                                    </div>
                                </div>
                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>{{ trans('langNodeOrderPriority') }}</label>
                                <div class='col-sm-12'>
                                    
                                        <input class='form-control' type='text' name='order_priority' value='{{ isset($mynode) ? $mynode->order_priority : '' }}'>
                                        <div class='help-block'>{{ trans('langNodeOrderPriority2') }}</div>
                                    
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langAvailableTypes') }}</label>
                                <div class='col-sm-12'>
                                    <div class='radio mb-3'>
                                        <label>
                                            <input class='input-StatusCourse' id='nodeopen' type='radio' name='visible' value='2' {{ $visibleChecked[2] }}>
                                            <label for="nodeopen"><span class='fa fa-unlock fa-fw'></label>
                                            {{ trans('langNodePublic') }}
                                        </label>
                                        <div class='help-block Neutral-900-cl ps-4 ms-3'><span class='fa fa-minus'></span>&nbsp{{ trans('langNodePublic2') }}</div>
                                    </div> 
                                   

                                    <div class='radio mb-3'>
                                        <label>
                                            <input class='input-StatusCourse' id='nodeforsubscribed' type='radio' name='visible' value='1' {{ $visibleChecked[1] }}>
                                            <label for="nodeforsubscribed">
                                                <span class='fa fa-lock fa-fw'></span>
                                                <span class='fa fa-pencil text-danger fa-custom-lock pen-hierarchy'></span>
                                            </label>
                                            {{ trans('langNodeSubscribed') }}
                                        </label>
                                        <div class='help-block Neutral-900-cl ps-4 ms-3'><span class='fa fa-minus'></span>&nbsp{{ trans('langNodeSubscribed2') }}</div>
                                    </div>
                                    

                                    <div class='radio'>
                                        <label>
                                            <input class='input-StatusCourse' id='nodehidden' type='radio' name='visible' value='0' {{ $visibleChecked[0] }}>
                                            <label><span class='fa fa-lock fa-fw'></span></label>
                                            {{ trans('langNodeHidden') }}
                                        </label>
                                         <div class='help-block Neutral-900-cl ps-4 ms-3'><span class='fa fa-minus'></span>&nbsp{{ trans('langNodeHidden2') }}</div>
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
                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-center align-items-center'>
                                    
                                        
                                        {!! form_buttons([
                                            [
                                                'class' => 'submitAdminBtn',
                                                'text' => trans('langSave'),
                                                'name' => isset($mynode) ? 'edit' : 'add',
                                                'value'=> isset($mynode) ? trans('langAcceptChanges') : trans('langAdd')
                                            ]
                                        ]) !!}
                                        
                                       
                                        {!! form_buttons([
                                            [
                                                'class' => 'cancelAdminBtn ms-1',
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
@endsection
