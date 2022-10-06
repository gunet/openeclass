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
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 all-alerts'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='alert alert-info'>
                            <label>{{ trans('langMethods') }}</label>
                            <ul>
                            @foreach ($auth_ids as $auth_id => $auth_name)
                                <?php $auth_count = count_auth_users($auth_id); ?>
                                @if ($auth_count > 0 or in_array($auth_id, $auth_active_ids))
                                    <li>
                                        {{ get_auth_info($auth_id) }}
                                        ({{ trans('langNbUsers') }}:
                                        @if ($auth_count == 0)
                                            0
                                        @else
                                            <a href='listusers.php?fname=&amp;lname=&amp;am=&amp;user_type=0&amp;auth_type={{ $auth_id }}&amp;reg_flag=1&amp;user_registered_at=&verified_mail=3&amp;email=&amp;uname=&amp;department=0'>{{ $auth_count }}</a>
                                        @endif
                                        @if ($auth_id != 1 and $auth_count > 0)
                                            - <a href='auth_change.php?auth={{ $auth_id }}'>{{ trans('langAuthChangeUser') }}</a>
                                        @endif
                                        )
                                        @if (!in_array($auth_id, $auth_active_ids))
                                            <br>
                                            <span class='label label-warning'>{{ trans('langAuthWarnInactive') }}</span>
                                        @endif
                                    </li>
                                @endif
                            @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class='table-responsive'>
                        <table class='table-default'>
                            <thead class='list-header'>
                                <th class='text-white'>{{ trans('langAllAuthTypes') }}</th>
                                <th class='text-white text-center'>{!! icon('fa-gears', trans('langActions')) !!}</th>
                            </thead>
                            <tbody>
                            @foreach ($authMethods as $authMethod)
                                <tr>
                                    <td{!! $authMethod->auth_default? '' : ' class=InvisibleAuth' !!}>
                                        {{ strtoupper($authMethod->auth_name) }}
                                        @if ($authMethod->auth_default > 1)
                                            &nbsp;&nbsp;
                                            <small>
                                                <span class='label label-default'>{{ trans('langPrimaryAuthType') }}</span>
                                            </small>
                                        @endif
                                    </td>
                                    <td class='option-btn-cell text-center'>
                                        {!! action_button(
                                        [
                                            [
                                                'title' => $authMethod->auth_default ? trans('langDeactivate') : trans('langActivate'),
                                                'url' => "$_SERVER[PHP_SELF]?auth=" . $authMethod->auth_id . "&amp;q=" . !$authMethod->auth_default,
                                                'icon' => $authMethod->auth_default ? 'fa-toggle-off' : 'fa-toggle-on',
                                                'show' => $authMethod->auth_id == 1 || $authMethod->auth_settings
                                            ],
                                            [
                                                'title' => trans('langAuthSettings'),
                                                'url' => "auth_process.php?auth=" . $authMethod->auth_id,
                                                'icon' => 'fa-gear'
                                            ],
                                            [
                                                'title' => trans('langPrimaryAuthType'),
                                                'url' => "$_SERVER[PHP_SELF]?auth=" . $authMethod->auth_id . "&amp;p=1",
                                                'icon' => 'fa-flag',
                                                'show' => $authMethod->auth_default and !$authMethod->auth_default > 1
                                            ],
                                            [
                                                'title' => trans('langSecondaryAuthType'),
                                                'url' => "$_SERVER[PHP_SELF]?auth=" . $authMethod->auth_id . "&amp;p=0",
                                                'icon' => 'fa-circle-o',
                                                'show' => $authMethod->auth_default > 1
                                            ],
                                            [
                                                'title' => trans('langConnTest'),
                                                'url' => "auth_test.php?auth=$authMethod->auth_id",
                                                'icon' => 'fa-plug',
                                                'show' => $authMethod->auth_id != 1 && $authMethod->auth_settings
                                            ],
                                            [   'title' => "Ενεργοποίηση μετάβασης",
                                                'url' => "$_SERVER[SCRIPT_NAME]?transition=true",
                                                'icon' => 'fa-bell',
                                                'show' => $auth_name == 'cas' && !get_config('sso_transition')
                                            ],
                                            [
                                                'title' => "Απενεργοποίηση μετάβασης",
                                                'url' => "$_SERVER[SCRIPT_NAME]?transition=false",
                                                'icon' => 'fa-bell-slash',
                                                'show' => $auth_name == 'cas' && !is_null(get_config('sso_transition')) && get_config('sso_transition')
                                            ],
                                            [
                                                'title' => "Αιτήματα εξαιρέσεων μετάβασης",
                                                'url' => "../auth/transition/admin_auth_transition.php",
                                                'icon' => 'fa-exclamation',
                                                'show' => $auth_name == 'cas' && !is_null(get_config('sso_transition')) && get_config('sso_transition')
                                            ]
                                        ])
                                    !!}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection