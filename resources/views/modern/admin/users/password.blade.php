@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    
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


                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>
                    
                    <div class='col-lg-6 col-12'>
                      <div class='form-wrapper form-edit p-3 rounded'>
                        
                          <form class='form-horizontal' role='form' method='post' action='{{ $urlServer }}modules/admin/password.php'>
                            <fieldset>      
                              <input type='hidden' name='userid' value='{{ $_GET['userid'] }}'>
                              <div class='form-group'>
                              <label class='col-sm-12 control-label-notes'>{{ trans('langNewPass1') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' placeholder="{{ trans('langNewPass1') }}..." type='password' size='40' name='password_form' value='' id='password' autocomplete='off'>
                                    &nbsp;
                                    <span id='result'></span>
                                </div>
                              </div>
                              <div class='form-group mt-3'>
                                <label class='col-sm-12 control-label-notes'>{{ trans('langNewPass2') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' placeholder="{{ trans('langNewPass2') }}..." type='password' size='40' name='password_form1' value='' autocomplete='off'>
                                </div>
                              </div>
                              <div class='col-12 mt-5'>
                                {!! showSecondFactorChallenge() !!}
                                <div class='row'>
                                    <div class='col-6'>
                                        <input class='btn btn-sm btn-primary submitAdminBtn w-100' type='submit' name='changePass' value='{{ trans('langModify') }}'>
                                    </div>
                                    <div class='col-6'>
                                         <a class='btn btn-sm btn-secondary cancelAdminBtn w-100' href='{{ $urlServer }}modules/admin/edituser.php?u={{ urlencode(getDirectReference($_REQUEST['userid'])) }}'>{{ trans('langCancel') }}</a>
                                    </div>
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
</div>            
@endsection