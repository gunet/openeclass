@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    
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
                        
                        <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                            
                            <div class='form-group'>
                            <label for = 'username' class='col-sm-12 control-label-notes'>{{ trans('langUsername') }}</label>
                                <div class='col-sm-12'>
                                    <input id='username' class='form-control' type='text' name='username' placeholder='{{ trans('langUsername') }}...'>
                                </div>
                            </div>
                         
                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-center align-items-center'>
                                    {!! showSecondFactorChallenge() !!}
                                    <input class='btn submitAdminBtn' type='submit' value='{{ trans('langSubmit') }}'>
                                </div>
                            </div>
                            {!! generate_csrf_token_form_field() !!}            
                        </form>
                    </div></div>
                </div>
            </div>
        </div>
    
</div>
@endsection