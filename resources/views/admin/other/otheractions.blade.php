@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='table-responsive'>
        <table class='table-default'>
            <tr>
                <td>
                    <a href='../usage/displaylog.php?from_other=TRUE'>{{ trans('langSystemActions') }}</a>
                </td>
            </tr>
            <tr>
                <td>
                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=failurelogin'>{{ trans('langLoginFailures') }}</a>
                    <small> ({{ trans('langLast15Days') }})</small>
                </td>
            </tr>
            <tr>
                <td>
                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=musers'>{{ trans('langMultipleUsers') }}</a>
                </td>
            </tr>
            <tr>
                <td>
                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=memail'>{{ trans('langMultipleAddr') }} e-mail</a>
                </td>
            </tr>
            <tr>
                <td>
                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=mlogins'>{{ trans('langMultiplePairs') }} LOGIN - PASS</a>
                </td>
            </tr>
            <tr>
                <td>
                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=vmusers'>{{ trans('langMailVerification') }}</a>
                </td>
            </tr>
            <tr>
                <td>
                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=unregusers'>{{ trans('langUnregUsers') }}</a>
                    <small> ({{ trans('langLastMonth') }})</small>
                </td>
            </tr>
        </table>            
    </div>
    @if (isset($_GET['stats']))
        @if (in_array($_GET['stats'], ['failurelogin', 'unregusers']))
            {!! $extra_info !!}
        @elseif ($_GET['stats'] == 'musers')
            <div class='table-responsive'>
                <table class='table-default'>
                    <tr class='list-header'>
                        <th>
                            <b>{{ trans('langMultipleUsers') }}</b>
                        </th>
                        <th class='right'>
                            <strong>{{ trans('langResult') }}</strong>
                        </th>
                    </tr>
                    @if (count($loginDouble) > 0)
                        {!! tablize($loginDouble) !!}
                        <tr>
                            <td class='right' colspan='2'>
                                <b>
                                    <span style='color: #FF0000'>{{ trans('langExist') }}</span>
                                </b>
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td class='right' colspan='2'>
                                <div class='text-center not_visible'> - {{ trans('langNotExist') }} - </div>
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        @elseif ($_GET['stats'] == 'memail')
            <div class='table-responsive'>
                <table class='table-default'>
                    <tr class='list-header'>
                        <th><b>{{ trans('langMultipleAddr') }} e-mail</b></th>
                        <th class='right'>
                            <strong>{{ trans('langResult') }}</strong>
                        </th>
                    </tr>
                    @if (count($loginDouble) > 0)
                        {!! tablize($loginDouble) !!}
                        <tr>
                            <td class=right colspan='2'>
                                <b>
                                    <span style='color: #FF0000'>{{ trans('langExist') }}</span>
                                </b>
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td class=right colspan='2'>
                                <div class='text-center not_visible'> - {{ trans('langNotExist') }} - </div>
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        @elseif ($_GET['stats'] == 'mlogins')
            <div class='table-responsive'>
                <table class='table-default'>
                    <tr class='list-header'>
                        <th>
                            <b>{{ trans('langMultiplePairs') }} LOGIN - PASS</b>
                        </th>
                        <th class='right'>
                            <b>{{ trans('langResult') }}</b>
                        </th>
                    </tr>
                    @if (count($loginDouble) > 0)
                        {!! tablize($loginDouble) !!}
                        <tr>
                            <td class='right' colspan='2'>
                                <b>
                                    <span style='color: #FF0000'>{{ trans('langExist') }}</span>
                                </b>
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td class='right' colspan='2'>
                                <div class='text-center not_visible'> - {{ trans('langNotExist') }} - </div>
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        @elseif  ($_GET['stats'] == 'vmusers')
            <div class='row'>
                <div class='col-sm-12'>
                    <div class='content-title h3'>
                        {{ trans('langUsers') }}
                    </div>
                    <ul class='list-group'>
                        <li class='list-group-item'>
                            <label>
                                <a href='listusers.php?search=yes&verified_mail=1'>{{ trans('langMailVerificationYes') }}</a>
                            </label>          
                            <span class='badge'>{{ $verifiedEmailUserCnt }}</span>
                        </li>
                        <li class='list-group-item'>
                            <label>
                                <a href='listusers.php?search=yes&verified_mail=2'>{{ trans('langMailVerificationNo') }}</a>
                            </label>                            
                            <span class='badge'>{{ $unverifiedEmailUserCnt }}</span>
                        </li>
                        <li class='list-group-item'>
                            <label>
                                <a href='listusers.php?search=yes&verified_mail=0'>{{ trans('langMailVerificationPending') }}</a>
                            </label>
                            <span class='badge'>{{ $verificationRequiredEmailUserCnt }}</span>
                        </li>
                        <li class='list-group-item'>
                            <label>
                                <a href='listusers.php?search=yes'>{{ trans('langTotal') }}</a>
                            </label>
                            <span class='badge'>{{ $totalUserCnt }}</span>
                        </li>
                    </ul>
                </div>
            </div>        
        @endif
    @endif
@endsection