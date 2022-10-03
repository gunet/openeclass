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
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 all-alerts'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>
                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper shadow-sm p-3 rounded'>
                        
                        <form class='form-horizontal' role='form' name='catForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                        <fieldset>
                            @if ($catid)
                            <input type='hidden' name='cat_id' value='{{ getIndirectReference($catid) }}'>
                            @endif
                            <div class='form-group mt-3'>
                                <label for='catname' class='col-sm-12 control-label-notes'>{{ trans('langName') }}</label>
                                <div class='col-sm-12'>
                                    <input id='catname' placeholder="{{ trans('langName') }}..." class="form-control" type='text' name='cat_name' value="{{ $cat_name ?: '' }}">
                                </div>
                            </div>
                     
                            <div class='col-12 mt-5'>
                                {!! showSecondFactorChallenge() !!}
                                <input class='btn btn-sm btn-primary submitAdminBtn w-100' type='submit' name='submit_cat' value='{{ trans('langAdd') }}'>
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
    var chkValidator  = new Validator("catForm");
    chkValidator.addValidation("catname","req","{{ trans('langCPFCategoryNameAlert') }}");
//]]></script>
@endsection