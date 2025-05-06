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

                    <div class='col-lg-6 col-12'>
                        <form class='form-wrapper form-edit border-0 px-0' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                            <fieldset>
                                <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                <table class='table table-bordered'>
                                    <tr>
                                    <th width='200'><b>{{ trans('langSFAConf') }}</b></th>
                                    <td>
                                        <select class='form-select' name='formconnector'>{!! implode('', $connectorOptions) !!}</select>
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
                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                    </div>

        </div>
</div>
</div>
@endsection
