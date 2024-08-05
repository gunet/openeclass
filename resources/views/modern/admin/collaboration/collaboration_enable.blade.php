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
                    
                    <form role='form' class='form-horizontal' action="{{ $_SERVER['SCRIPT_NAME'] }}" method='post'>
                        <fieldset>
                            <div class='form-group'>
                                <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label class='label-container' aria-label="{{ trans('langSettingSelect') }}">
                                            <input id='enable_collaboration' type='checkbox' name='enable_collaboration' {!! get_config('show_collaboration') ? 'checked' : '' !!}>
                                            <span class='checkmark'></span>
                                            {{ trans('langEnableCollaboration') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label class='label-container' aria-label="{{ trans('langSettingSelect') }}">
                                            <input id='always_enabled_collaboration' type='checkbox' name='always_enabled_collaboration' {!! get_config('show_always_collaboration') ? 'checked' : '' !!}>
                                            <span class='checkmark'></span>
                                            {{ trans('langAlwaysEnabledCollaboration') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            {!! generate_csrf_token_form_field() !!}    
                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-end aling-items-center'>
                                    <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langModify') }}'>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>  
            </div>

            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
            </div>
                
        </div>
    </div>
</div>             

@endsection