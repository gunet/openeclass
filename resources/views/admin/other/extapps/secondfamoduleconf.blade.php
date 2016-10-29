@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <form action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
    <fieldset>
        <table class='table table-bordered'>
        <tr>
           <th width='200'><b>{{ trans('langSFAConf') }}</b></th>
           <td>
               <select name='formconnector'>{!! implode('', $connectorOptions) !!}</select>
           </td>
        </tr>
        @foreach($connectorClasses as $curConnectorClass)
            @foreach((new $curConnectorClass())->getConfigFields() as $curField => $curLabel)
                <tr class='connector-config connector-{{ $curConnectorClass }}' style='display: none;'>
                    <th width='200' class='left'><b>{{ $curLabel }}</b></th>
                    <td><input class='FormData_InputText' type='text' name='form$curField' size='40' value='{{ get_config($curField) }}'></td>
                </tr>
            @endforeach
        @endforeach
        </table>
        </fieldset>
        <p>{{ trans('langSFAusage') }}</p>
        <ul>
            <li><a href='https://www.authy.com/'>Authy for iOS, Android, Chrome, OS X</a></li>
            <li><a href='https://fedorahosted.org/freeotp/'>FreeOTP for iOS, Android and Peeble</a></li>
            <li><a href='https://www.toopher.com/'>FreeOTP for iOS, Android and Peeble</a></li>
            <li><a href='http://itunes.apple.com/us/app/google-authenticator/id388497605?mt=8%22'>Google Authenticator for iOS</a></li>
            <li><a href='https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2%22'>Google Authenticator for Android</a></li>
            <li><a href='https://m.google.com/authenticator%22'>Google Authenticator for Blackberry</a></li>
            <li><a href='http://apps.microsoft.com/windows/en-us/app/google-authenticator/7ea6de74-dddb-47df-92cb-40afac4d38bb%22'>Google Authenticator (port) on Windows app store</a></li>
        </ul>
        <br>
        <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langModify') }}'>
        {!! generate_csrf_token_form_field() !!}
    </form>
@endsection