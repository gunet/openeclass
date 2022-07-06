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
                        
                        <form class='form-horizontal' role='form' name='fieldForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                        <fieldset>
                            @if (isset($_GET['edit_field']))
                                <input type='hidden' name='field_id' value='{{ getIndirectReference($fieldid) }}'>
                            @else
                                <input type='hidden' name='catid' value='{{ getIndirectReference($catid) }}'>
                            @endif
                            <input type='hidden' name='datatype' value='{{ $datatype }}'>
                            <div class='form-group'>
                                <label for='name' class='col-sm-6 control-label-notes'>{{ trans('langName') }}</label>
                                <div class='col-sm-12'>
                                    <input id='name' type='text' name='field_name' class="form-control" value="{{ isset($name) ? $name : '' }}">
                                </div>
                            </div>
                            <div class='row p-2'></div>
                            <div class='form-group'>
                                <label for='shortname' class='col-sm-6 control-label-notes'>
                                    {{ trans('langCPFShortName') }} <small>({{ trans('langCPFUniqueShortname') }})</small>
                                </label>
                                <div class='col-sm-12'>
                                    <input id='shortname' type='text' name='field_shortname' class="form-control" value="{{ isset($shortname) ? $shortname : '' }}">
                                </div>
                            </div>
                            <div class='row p-2'></div>

                            <div class='form-group'>
                                <label for='fielddescr' class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}</label>
                                <div class='col-sm-12'>
                                    {!! $fielddescr_rich_text !!}
                                </div>
                            </div>
                            @if (isset($_GET['edit_field']))
                            <div class='row p-2'></div>
                                <div class='form-group'>
                                    <label for='datatype' class='col-sm-6 control-label-notes'>{{ trans('langCPFFieldDatatype') }}</label>
                                    <div class='col-sm-12'>
                                        {!! selection($field_types, 'datatype_disabled', $datatype, 'class="form-control" disabled') !!}
                                    </div>
                                </div>           
                            @endif
                            <div class='row p-2'></div>
                            <div class='form-group'>
                                <label for='required' class='col-sm-6 control-label-notes'>{{ trans('langCPFFieldRequired') }}</label>
                                <div class='col-sm-12'>
                                    {!! selection($yes_no, 'required', isset($required) ? $required : '', 'class="form-control"') !!}
                                </div>
                            </div>
                            @if ($datatype == CPF_MENU)
                            <div class='row p-2'></div>
                                <div class='form-group'>
                                    <label for='options' class='col-sm-6 control-label-notes'>
                                        {{ trans('langCPFMenuOptions') }} <small>({{ trans('langCPFMenuOptionsExplan') }})</small>
                                    </label>
                                    <div class='col-sm-12'>
                                        <textarea name='options' rows='8' cols='20' class="form-control">{{ isset($textarea_val) ? $textarea_val : '' }}</textarea>
                                    </div>
                                </div>
                            @endif
                            <div class='row p-2'></div>
                            <div class='form-group'>
                                <label for='registration' class='col-sm-6 control-label-notes'>{{ trans('langCPFFieldRegistration') }}</label>
                                <div class='col-sm-12'>
                                    {!! selection($yes_no, 'registration', isset($registration) ? $registration : '', 'class="form-control"') !!}
                                </div>
                            </div>
                            <div class='row p-2'></div>
                            <div class='form-group'>
                                <label for='user_type' class='col-sm-6 control-label-notes'>{{ trans('langCPFFieldUserType') }}</label>
                                <div class='col-sm-12'>
                                    {!! selection($user_type, 'user_type', isset($utype) ? $utype : 10, 'class="form-control"') !!}
                                </div>
                            </div>
                            <div class='row p-2'></div>
                            <div class='form-group'>
                                <label for='visibility' class='col-sm-6 control-label-notes'>{{ trans('langCPFFieldVisibility') }}</label>
                                <div class='col-sm-12'>
                                    {!! selection($visibility, 'visibility', isset($vis) ? $vis : 10, 'class="form-control"') !!}
                                </div>
                            </div>
                            <div class='row p-2'></div>
                            <div class='col-sm-offset-2 col-sm-10'>
                                {!! showSecondFactorChallenge() !!}
                                <input class='btn btn-primary' type='submit' name='submit_field' value='{{ trans('langAdd') }}'>
                            </div>
                        </fieldset>
                        {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div></div>
                </div>
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