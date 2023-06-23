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
                        
                    <form class='form-horizontal' name='authmenu' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                        <input type='hidden' name='auth' value='{{ $auth }}'>
                        <fieldset>  
                            <div class='alert alert-info'>{{ trans('langTestAccount') }} ({{ $auth_ids[$auth] }})</div>
                            
                            <div class='form-group mt-4'>
                                <label for='test_username' class='col-sm-12 control-label-notes'>{{ trans('langUsername') }}:</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='test_username' id='test_username' value='{{ canonicalize_whitespace($test_username) }}' autocomplete='off'>
                                </div>
                            </div>

                          

                            <div class='form-group mt-4'>
                                <label for='test_password' class='col-sm-12 control-label-notes'>{{ trans('langPass') }}:</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='password' name='test_password' id='test_password' value='{{ $test_password }}' autocomplete='off'>
                                </div>
                            </div>

                            

                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-center align-items-center'>
                                 
                                      
                                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langConnTest') }}'>
                                      
                                      
                                            <a class='btn cancelAdminBtn ms-1' href='auth.php'>{{ trans('langCancel') }}</a>
                                       
                                
                                    
                                    
                                </div>
                            </div>
                        </fieldset>
                        {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div></div>
                </div>
            </div>
        </div>  
</div>     
@endsection