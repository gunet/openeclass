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
                        <form class='form-wrapper form-edit p-3 rounded' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
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
                                            <tr class='connector-config connector-{{ $curConnectorClass }} d-none'>
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
                            <input class='btn btn-sm btn-primary submitAdminBtn w-100' type='submit' name='submit' value='{{ trans('langModify') }}'>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection