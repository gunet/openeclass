@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div id="background-cheat-leftnav" class="col-xl-3 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebarAdmin')
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>
                        
                    
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
                        <form class='shadow-lg p-3 mb-5 bg-body rounded bg-primary' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection