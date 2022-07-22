@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

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
                        
                        <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                            <div class='row p-2'></div>
                            <div class='form-group'>
                            <label for = 'username' class='col-sm-6 control-label-notes'>{{ trans('langUsername') }}:</label>
                                <div class='col-sm-12'>
                                    <input id='username' class='form-control' type='text' name='username' placeholder='{{ trans('langUsername') }}'>
                                </div>
                            </div>
                            <div class='row p-2'></div>
                            <div class='form-group'>
                                <div class='col-sm-9 col-sm-offset-3'>
                                    {!! showSecondFactorChallenge() !!}
                                    <input class='btn btn-primary' type='submit' value='{{ trans('langSubmit') }}'>
                                </div>
                            </div>
                            {!! generate_csrf_token_form_field() !!}            
                        </form>
                    </div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection