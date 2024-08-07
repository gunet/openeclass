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
                    
                    @if (isset($auth_methods_active) == 0)
                        <div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langAuthChangeno') }}</span></div></div>
                    @else
                    
                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                            <form class='form-horizontal' role='form' name='authchange' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>   
                            <fieldset>
                                <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                <div class='form-group'>
                                    <label id='auth_change_id' class='col-sm-12 control-label-notes'>{{ trans('langAuthChangeto') }}</label>
                                    <div class='col-sm-12'>
                                        {!! selection($auth_methods_active, 'auth_change', '', "class='form-control' id='auth_change_id'") !!}
                                    </div>
                                </div>
                                <input type='hidden' name='auth' value='{{ getIndirectReference(intval($auth)) }}'>  
                                <div class='col-12 mt-5 d-flex justify-content-end align-items-center'>
                                    <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langModify') }}'>
                                </div>
                            </fieldset>
                            {!! generate_csrf_token_form_field() !!}    
                            </form>
                        </div>
                    </div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                    </div>
                    @endif    
                
        </div>
</div>

</div>     
@endsection