@extends('layouts.default_old')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
        <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">

            <div class="row p-5">

                <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                    <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                        <i class="fas fa-align-left"></i>
                        <span></span>
                    </button>

            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">

                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

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

                @if($breadcrumbs && count($breadcrumbs)>2)
                    <div class='row p-2'></div>
                    <div class="float-start">
                        <p class='control-label-notes'>{!! $breadcrumbs[1]['bread_text'] !!}</p>
                        <small class='text-secondary'>{!! $breadcrumbs[count($breadcrumbs)-1]['bread_text'] !!}</small>
                    </div>
                    <div class='row p-2'></div>
                @endif

                {!! isset($action_bar) ?  $action_bar : '' !!}

                @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                @endif

                <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                  <div class='form-wrapper shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
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
@endsection
