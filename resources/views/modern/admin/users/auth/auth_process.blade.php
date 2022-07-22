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
                        
                        <form class='form-horizontal' name='authmenu' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                        <fieldset>	
                            <input type='hidden' name='auth' value='{{ getIndirectReference($auth) }}'>
                            @if ($auth == 1)
                                @include('admin.users.auth.methods.eclass')
                            @elseif ($auth == 2)
                                @include('admin.users.auth.methods.pop3')
                                @include('admin.users.auth.methods.eclass')
                            @elseif ($auth == 3)
                                @include('admin.users.auth.methods.imap')
                                @include('admin.users.auth.methods.eclass') 
                            @elseif ($auth == 4)
                                @include('admin.users.auth.methods.ldap')
                                @include('admin.users.auth.methods.eclass')   
                            @elseif ($auth == 5)
                                @include('admin.users.auth.methods.db')
                                @include('admin.users.auth.methods.eclass')
                            @elseif ($auth == 6)
                                @include('admin.users.auth.methods.shib')
                                @include('admin.users.auth.methods.eclass')
                            @elseif ($auth == 7)
                                @include('admin.users.auth.methods.cas')
                                @include('admin.users.auth.methods.eclass')
                            @else
                                @include('admin.users.auth.methods.hybrid')
                            @endif
                            {!! showSecondFactorChallenge() !!}
                            <div class='row p-2'></div>
                            <div class='form-group'>
                                <div class='col-sm-10 col-sm-offset-2'>
                                    <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langModify') }}'>
                                    <a class='btn btn-secondary' href='auth.php'>{{ trans('langCancel') }}</a>
                                </div>
                            </div>
                        </fieldset>
                        {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div> </div>
                </div>
            </div>
        </div>
    </div>
</div>           
@endsection