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
                        
                          <form class='form-horizontal' role='form' method='post' action='{{ $urlServer }}modules/admin/password.php'>
                            <fieldset>      
                              <input type='hidden' name='userid' value='{{ $_GET['userid'] }}'>
                              <div class='form-group mt-3'>
                              <label class='col-sm-6 control-label-notes'>{{ trans('langNewPass1') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='password' size='40' name='password_form' value='' id='password' autocomplete='off'>
                                    &nbsp;
                                    <span id='result'></span>
                                </div>
                              </div>
                              <div class='form-group mt-3'>
                                <label class='col-sm-6 control-label-notes'>{{ trans('langNewPass2') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='password' size='40' name='password_form1' value='' autocomplete='off'>
                                </div>
                              </div>
                              <div class='col-sm-offset-3 col-sm-9 mt-3'>
                                {!! showSecondFactorChallenge() !!}
                                <input class='btn btn-primary' type='submit' name='changePass' value='{{ trans('langModify') }}'>
                                <a class='btn btn-secondary' href='{{ $urlServer }}modules/admin/edituser.php?u={{ urlencode(getDirectReference($_REQUEST['userid'])) }}'>{{ trans('langCancel') }}</a>
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