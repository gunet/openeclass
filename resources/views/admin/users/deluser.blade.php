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
                    
                    @if (get_admin_rights($user) > 0)
                        <div class='col-12'>
                            <div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>
                                {{ trans('langCantDeleteAdmin', ["$u_realname ($u_account)"]) }}
                                {{ trans('langIfDeleteAdmin') }}
                            </span>
                            </div>
                        </div>
                    @else
                        <div class='col-12'>
                            <div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langConfirmDeleteQuestion1') }} <em>{{ $u_realname }} ({{ $u_account }})</em><br>
                                {{ trans('langConfirmDeleteQuestion3') }}</span>
                            </div>
                        </div>

                        <div class='col-12'>
                            <form method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}?u={{ $user }}'>
                                {!! showSecondFactorChallenge() !!}
                                <input class='btn deleteAdminBtn' type='submit' name='doit' value='{{ trans('langDelete') }}'>
                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    @endif

               
        </div>
</div>
</div>
@endsection