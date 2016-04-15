@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
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
                        <a href='listusers.php?fname=&amp;lname=&amp;am=&amp;user_type=0&amp;auth_type={{ $auth_id }}&amp;reg_flag=1&amp;user_registered_at=&verified_mail=3&amp;email=&amp;uname=&amp;department={{ getIndirectReference(0) }}'>{{ $auth_count }}</a>
                    @endif
                    @if ($auth_id != 1 and $auth_count > 0)
                        - <a href='auth_change.php?auth={{ getIndirectReference($auth_id) }}'>{{ trans('langAuthChangeUser') }}</a>
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
    <div class='table-responsive'>
        <table class='table-default'>
            <th>{{ trans('langAllAuthTypes') }}</th>
            <th class='text-center'>{!! icon('fa-gears', trans('langActions')) !!}</th>
            @foreach ($authMethods as $authMethod)
                <tr>
                    <td{!! $authMethod->auth_default? '' : ' class=not_visible' !!}>
                        {{ strtoupper($authMethod->auth_name) }}
                        @if ($authMethod->auth_default > 1)
                            &nbsp;&nbsp;
                            <small>
                                <span class='label label-default'>{{ trans('langPrimaryAuthType') }}</span>
                            </small>
                        @endif
                    </td>
                    <td class='option-btn-cell'>
                        {!! action_button(
                        [
                            [
                                'title' => $authMethod->auth_default ? trans('langDeactivate') : trans('langActivate'),
                                'url' => "$_SERVER[PHP_SELF]?auth=" . getIndirectReference($authMethod->auth_id) . "&amp;q=" . !$authMethod->auth_default,
                                'icon' => $authMethod->auth_default ? 'fa-toggle-off' : 'fa-toggle-on',
                                'show' => $authMethod->auth_id == 1 || $authMethod->auth_settings
                            ],
                            [
                                'title' => trans('langAuthSettings'),
                                'url' => "auth_process.php?auth=" . getIndirectReference($authMethod->auth_id),
                                'icon' => 'fa-gear'
                            ],
                            [
                                'title' => trans('langPrimaryAuthType'),
                                'url' => "$_SERVER[PHP_SELF]?auth=" . getIndirectReference($authMethod->auth_id) . "&amp;p=1",
                                'icon' => 'fa-flag',
                                'show' => $authMethod->auth_default and !$authMethod->auth_default > 1
                            ],
                            [
                                'title' => trans('langSecondaryAuthType'),
                                'url' => "$_SERVER[PHP_SELF]?auth=" . getIndirectReference($authMethod->auth_id) . "&amp;p=0",
                                'icon' => 'fa-circle-o',
                                'show' => $authMethod->auth_default > 1
                            ],
                            [
                                'title' => trans('langConnTest'),
                                'url' => "auth_test.php?auth=$authMethod->auth_id",
                                'icon' => 'fa-plug',
                                'show' => $authMethod->auth_id != 1 && $authMethod->auth_settings
                            ]
                        ]
                        ) !!}
                    </td>
                <tr>
            @endforeach            
        </table>
    </div>
@endsection