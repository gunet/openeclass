@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])


                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    
                    @if (get_admin_rights($user) > 0)
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                            <div class='alert alert-warning'>
                                {{ trans('langCantDeleteAdmin', ["<em>$u_realname ($u_account)</em>"]) }}
                                {{ trans('langIfDeleteAdmin') }}
                            </div>
                        </div>
                    @else
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                            <div class='alert alert-warning'>{{ trans('langConfirmDeleteQuestion1') }} <em>{{ $u_realname }} ({{ $u_account }})</em><br>
                                {{ trans('langConfirmDeleteQuestion3') }}
                            </div>
                        </div>

                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                            <form method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}?u={{ $user }}'>
                                {!! showSecondFactorChallenge() !!}
                                <input class='btn btn-danger' type='submit' name='doit' value='{{ trans('langDelete') }}'>
                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection