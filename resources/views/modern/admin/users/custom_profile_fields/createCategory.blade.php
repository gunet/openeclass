@extends('layouts.default')

@section('content')
<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    @include('layouts.partials.show_alert') 
                    
                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                        
                        <form class='form-horizontal' role='form' name='catForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                        <fieldset>
                            @if ($catid)
                            <input type='hidden' name='cat_id' value='{{ getIndirectReference($catid) }}'>
                            @endif
                            <div class='form-group'>
                                <label for='catname' class='col-sm-12 control-label-notes'>{{ trans('langName') }}</label>
                                <div class='col-sm-12'>
                                    <input id='catname' placeholder="{{ trans('langName') }}" class="form-control" type='text' name='cat_name' value="{{ $cat_name ?: '' }}">
                                </div>
                            </div>
                     
                            <div class='col-12 mt-5 d-flex justify-content-end align-items-center'>
                                {!! showSecondFactorChallenge() !!}
                                <input class='btn submitAdminBtn' type='submit' name='submit_cat' value='{{ trans('langAdd') }}'>
                            </div>
                        </fieldset>
                        {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div></div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
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