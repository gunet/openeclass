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

                    <div class="extapp">
                        <div class='col-12'>
                            <table class="table-default extapp-table">
                                <thead class='list-header'>
                                    <td class='text-white TextSemiBold'>{{ trans('langExtAppName') }}</td>
                                    <td class='text-white TextSemiBold'>{{ trans('langDescription') }}</td>
                                </thead>
                                @foreach (ExtAppManager::getApps() as $app)
                                    <tr>
                                    <!--WARNING!!!! LEAVE THE SIZE OF THE IMAGE TO BE DOUBLE THE SIZE OF THE ACTUAL PNG FILE, TO SUPPORT HDPI DISPLAYS!!!!-->
                                        <td style="width:90px; padding:0px;">
                                            <div class="text-center" style="padding:10px;">
                                                <a href="{{ $urlAppend . $app->getConfigUrl() }}">
                                                @if ($app->getAppIcon() !== null)
                                                    <img width="89" src="{{ $app->getAppIcon() }}">
                                                @endif
                                                {{ $app->getDisplayName() }}
                                                </a>
                                            </div>
                                        </td>

                                        <td class="text-muted clearfix">
                                            <div class="extapp-dscr-wrapper">
                                                {!! $app->getShortDescription() !!}
                                            </div>
                                            <div class="extapp-controls">
                                                <div class="btn-group btn-group-sm">
                                                    @if ($app->isConfigured())
                                                        @if (showSecondFactorChallenge() != "")
                                                            <button onclick="var totp=prompt('Type 2FA:','');this.setAttribute('data-app', this.getAttribute('data-app')+','+escape(totp));"  type="button" class="btn{!! $app->isEnabled() ? ' submitAdminBtn' : ' deleteAdminBtn' !!} extapp-status" data-app="{{ getIndirectReference($app->getName()) }}">
                                                        @elseif ($app->getName() == 'bigbluebutton')
                                                            <button type="button" class="btn{!! $app->isEnabled() ? ' submitAdminBtn' : ' deleteAdminBtn' !!} bbb-status" data-app="{{ getIndirectReference($app->getName()) }}">
                                                        @elseif ($app->getName() == 'openmeetings')
                                                            <button type="button" class="btn{!! $app->isEnabled() ? ' submitAdminBtn' : ' deleteAdminBtn' !!} om-status" data-app="{{ getIndirectReference($app->getName()) }}">
                                                        @elseif ($app->getName() == 'webconf')
                                                            <button type="button" class="btn{!! $app->isEnabled() ? ' submitAdminBtn' : ' deleteAdminBtn' !!} webconf-status" data-app="{{ getIndirectReference($app->getName()) }}">
                                                        @else
                                                            <button type="button" class="btn{!! $app->isEnabled() ? ' submitAdminBtn' : ' deleteAdminBtn' !!} extapp-status" data-app="{{ getIndirectReference($app->getName()) }}">
                                                        @endif
                                                            {!! $app->isEnabled() ? '<i class="fa fa-toggle-on"></i>' : '<i class="fa fa-toggle-off"></i>' !!}
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn cancelAdminBtn" data-app="{{ getIndirectReference($app->getName()) }}"  data-bs-toggle='modal' data-bs-target='#noSettings'>
                                                            <i class="fa fa-warning"></i>
                                                        </button>
                                                    @endif
                                                    <a href="{{ $urlAppend . $app->getConfigUrl() }}" class="btn submitAdminBtn">
                                                        <i class="fa fa-sliders fw"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                    <div class='modal fade' id='noSettings' tabindex='-1' role='dialog' aria-labelledby='myModalLabel'>
                        <div class='modal-dialog' role='document'>
                            <div class='modal-content'>
                                <div class='modal-header'>
                                    <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'>
                                        <span aria-hidden='true'>&times;</span>
                                    </button>
                                    <h4 class='modal-title' id='myModalLabel'>{{ trans('langNotConfigured') }}</h4>
                                </div>
                                <div class='modal-body'>
                                {{ trans('langEnableAfterConfig') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
     // External Apps activate/deactivate button
     $('.extapp-status').on('click', function () {
        var url = window.location.href;
        var button = $(this).children('i');
        var state = button.hasClass('fa-toggle-on') ? "fa-toggle-on" : "fa-toggle-off";
        var appName = button.parent('button').attr('data-app');

        button.removeClass(state).addClass('fa-spinner fa-spin');

        $.post( url,
                {state: state,
                 appName: appName},
                function (data) {
                    var newState = (data === "0")? "fa-toggle-off":"fa-toggle-on";
                    button.removeClass('fa-spinner fa-spin').addClass(newState);
                    btnColorState = button.parent('button').hasClass('submitAdminBtn')?'submitAdminBtn':'deleteAdminBtn';
                    newBtnColorState = button.parent('button').hasClass('submitAdminBtn')?'deleteAdminBtn':'submitAdminBtn';
                    button.parent('button').removeClass(btnColorState).addClass(newBtnColorState);
                });
    });

    // deactivate om + webconf button when bbb button is enabled
    $('.bbb-status').on('click', function () {
        var url = window.location.href;
        var button = $(this).children('i');
        var om_button = $('.om-status').children('i');
        var webconf_button = $('.webconf-status').children('i');
        var state = button.hasClass('fa-toggle-on') ? "fa-toggle-on" : "fa-toggle-off";
        var om_state = om_button.hasClass('fa-toggle-on') ? "fa-toggle-on" : "fa-toggle-off";
        var webconf_state = webconf_button.hasClass('fa-toggle-on') ? "fa-toggle-on" : "fa-toggle-off";
        var appName = button.parent('button').attr('data-app');

        button.removeClass(state).addClass('fa-spinner fa-spin');

        $.post( url,
                {state: state,
                 appName: appName},
                function (data) {
                    if (data === "0") {
                        newState = "fa-toggle-off";
                    } else {
                        newState = "fa-toggle-on";
                        if (om_state === 'fa-toggle-on') {
                           om_newState = "fa-toggle-off";
                           om_button.removeClass('fa-spinner fa-spin').addClass(om_newState);
                           om_btnColorState = om_button.parent('button').hasClass('submitAdminBtn')?'submitAdminBtn':'deleteAdminBtn';
                           om_newBtnColorState = om_button.parent('button').hasClass('submitAdminBtn')?'deleteAdminBtn':'submitAdminBtn';
                           om_button.parent('button').removeClass(om_btnColorState).addClass(om_newBtnColorState);
                        }
                        if (webconf_state === 'fa-toggle-on') {
                           webconf_newState = "fa-toggle-off";
                           webconf_button.removeClass('fa-spinner fa-spin').addClass(webconf_newState);
                           webconf_btnColorState = webconf_button.parent('button').hasClass('submitAdminBtn')?'submitAdminBtn':'deleteAdminBtn';
                           webconf_newBtnColorState = webconf_button.parent('button').hasClass('submitAdminBtn')?'deleteAdminBtn':'submitAdminBtn';
                           webconf_button.parent('button').removeClass(webconf_btnColorState).addClass(webconf_newBtnColorState);
                        }
                    }
                    button.removeClass('fa-spinner fa-spin').addClass(newState);
                    btnColorState = button.parent('button').hasClass('submitAdminBtn')?'submitAdminBtn':'deleteAdminBtn';
                    newBtnColorState = button.parent('button').hasClass('submitAdminBtn')?'deleteAdminBtn':'submitAdminBtn';
                    button.parent('button').removeClass(btnColorState).addClass(newBtnColorState);
                });
    });

    // deactivate bbb + webconf button when om button is enabled
    $('.om-status').on('click', function () {
        var url = window.location.href;
        var button = $(this).children('i');
        var bbb_button = $('.bbb-status').children('i');
        var webconf_button = $('.webconf-status').children('i');
        var state = button.hasClass('fa-toggle-on') ? "fa-toggle-on" : "fa-toggle-off";
        var bbb_state = bbb_button.hasClass('fa-toggle-on') ? "fa-toggle-on" : "fa-toggle-off";
        var webconf_state = webconf_button.hasClass('fa-toggle-on') ? "fa-toggle-on" : "fa-toggle-off";
        var appName = button.parent('button').attr('data-app');

        button.removeClass(state).addClass('fa-spinner fa-spin');

        $.post( url,
                {state: state,
                 appName: appName},
                function (data) {
                    if (data === "0") {
                        newState = "fa-toggle-off";
                    } else {
                        newState = "fa-toggle-on";
                        if (bbb_state === 'fa-toggle-on') {
                           bbb_newState = "fa-toggle-off";
                           bbb_button.removeClass('fa-spinner fa-spin').addClass(bbb_newState);
                           bbb_btnColorState = bbb_button.parent('button').hasClass('submitAdminBtn')?'submitAdminBtn':'deleteAdminBtn';
                           bbb_newBtnColorState = bbb_button.parent('button').hasClass('submitAdminBtn')?'deleteAdminBtn':'submitAdminBtn';
                           bbb_button.parent('button').removeClass(bbb_btnColorState).addClass(bbb_newBtnColorState);
                        }
                        if (webconf_state === 'fa-toggle-on') {
                           webconf_newState = "fa-toggle-off";
                           webconf_button.removeClass('fa-spinner fa-spin').addClass(webconf_newState);
                           webconf_btnColorState = webconf_button.parent('button').hasClass('submitAdminBtn')?'submitAdminBtn':'deleteAdminBtn';
                           webconf_newBtnColorState = webconf_button.parent('button').hasClass('submitAdminBtn')?'deleteAdminBtn':'submitAdminBtn';
                           webconf_button.parent('button').removeClass(webconf_btnColorState).addClass(webconf_newBtnColorState);
                        }
                    }
                    button.removeClass('fa-spinner fa-spin').addClass(newState);
                    btnColorState = button.parent('button').hasClass('submitAdminBtn')?'submitAdminBtn':'deleteAdminBtn';
                    newBtnColorState = button.parent('button').hasClass('submitAdminBtn')?'deleteAdminBtn':'submitAdminBtn';
                    button.parent('button').removeClass(btnColorState).addClass(newBtnColorState);
                });
    });

    // deactivate bbb + om button when webconf button is enabled
    $('.webconf-status').on('click', function () {
        var url = window.location.href;
        var button = $(this).children('i');
        var bbb_button = $('.bbb-status').children('i');
        var om_button = $('.om-status').children('i');
        var state = button.hasClass('fa-toggle-on') ? "fa-toggle-on" : "fa-toggle-off";
        var bbb_state = bbb_button.hasClass('fa-toggle-on') ? "fa-toggle-on" : "fa-toggle-off";
        var om_state = om_button.hasClass('fa-toggle-on') ? "fa-toggle-on" : "fa-toggle-off";
        var appName = button.parent('button').attr('data-app');

        button.removeClass(state).addClass('fa-spinner fa-spin');

        $.post( url,
                {state: state,
                 appName: appName},
                function (data) {
                    if (data === "0") {
                        newState = "fa-toggle-off";
                    } else {
                        newState = "fa-toggle-on";
                        if (bbb_state === 'fa-toggle-on') {
                           bbb_newState = "fa-toggle-off";
                           bbb_button.removeClass('fa-spinner fa-spin').addClass(bbb_newState);
                           bbb_btnColorState = bbb_button.parent('button').hasClass('submitAdminBtn')?'submitAdminBtn':'deleteAdminBtn';
                           bbb_newBtnColorState = bbb_button.parent('button').hasClass('submitAdminBtn')?'deleteAdminBtn':'submitAdminBtn';
                           bbb_button.parent('button').removeClass(bbb_btnColorState).addClass(bbb_newBtnColorState);
                        }
                        if (om_state === 'fa-toggle-on') {
                           om_newState = "fa-toggle-off";
                           om_button.removeClass('fa-spinner fa-spin').addClass(om_newState);
                           om_btnColorState = om_button.parent('button').hasClass('submitAdminBtn')?'submitAdminBtn':'deleteAdminBtn';
                           om_newBtnColorState = om_button.parent('button').hasClass('submitAdminBtn')?'deleteAdminBtn':'submitAdminBtn';
                           om_button.parent('button').removeClass(om_btnColorState).addClass(om_newBtnColorState);
                        }
                    }
                    button.removeClass('fa-spinner fa-spin').addClass(newState);
                    btnColorState = button.parent('button').hasClass('submitAdminBtn')?'submitAdminBtn':'deleteAdminBtn';
                    newBtnColorState = button.parent('button').hasClass('submitAdminBtn')?'deleteAdminBtn':'submitAdminBtn';
                    button.parent('button').removeClass(btnColorState).addClass(newBtnColorState);
                });
    });
</script>
@endsection
