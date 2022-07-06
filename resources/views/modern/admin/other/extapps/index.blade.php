@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebarAdmin')
                </div>
            </div>

            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-5">

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

                    @if($breadcrumbs && count($breadcrumbs)>2)
                    <div class='row p-2'></div>
                    <div class="float-start">
                        <p class='control-label-notes'>{!! $breadcrumbs[1]['bread_text'] !!}</p>
                        <small class='text-secondary'>{!! $breadcrumbs[count($breadcrumbs)-1]['bread_text'] !!}</small>
                    </div>
                    <div class='row p-2'></div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class="extapp">
                        <div class='col-12'>
                            <table class="announcements_table dataTable no-footer extapp-table">
                                <thead class='notes_thead'>
                                    <td class='text-white'>{{ trans('langExtAppName') }}</td>
                                    <td class='text-white'>{{ trans('langDescription') }}</td>
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
                                                            <button onclick="var totp=prompt('Type 2FA:','');this.setAttribute('data-app', this.getAttribute('data-app')+','+escape(totp));"  type="button" class="btn{!! $app->isEnabled() ? ' btn-success' : ' btn-danger' !!} extapp-status" data-app="{{ getIndirectReference($app->getName()) }}"> 
                                                        @elseif ($app->getName() == 'bigbluebutton')
                                                            <button type="button" class="btn{!! $app->isEnabled() ? ' btn-success' : ' btn-danger' !!} bbb-status" data-app="{{ getIndirectReference($app->getName()) }}">     
                                                        @elseif ($app->getName() == 'openmeetings')
                                                            <button type="button" class="btn{!! $app->isEnabled() ? ' btn-success' : ' btn-danger' !!} om-status" data-app="{{ getIndirectReference($app->getName()) }}"> 
                                                        @elseif ($app->getName() == 'webconf')
                                                            <button type="button" class="btn{!! $app->isEnabled() ? ' btn-success' : ' btn-danger' !!} webconf-status" data-app="{{ getIndirectReference($app->getName()) }}"> 
                                                        @else
                                                            <button type="button" class="btn{!! $app->isEnabled() ? ' btn-success' : ' btn-danger' !!} extapp-status" data-app="{{ getIndirectReference($app->getName()) }}"> 
                                                        @endif
                                                            {!! $app->isEnabled() ? '<i class="fa fa-toggle-on"></i>' : '<i class="fa fa-toggle-off"></i>' !!} 
                                                        </button>  
                                                    @else
                                                        <button type="button" class="btn btn-secondary" data-app="{{ getIndirectReference($app->getName()) }}"  data-bs-toggle='modal' data-bs-target='#noSettings'> 
                                                            <i class="fa fa-warning"></i> 
                                                        </button>
                                                    @endif
                                                    <a href="{{ $urlAppend . $app->getConfigUrl() }}" class="btn btn-primary"> 
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
                    btnColorState = button.parent('button').hasClass('btn-success')?'btn-success':'btn-danger';
                    newBtnColorState = button.parent('button').hasClass('btn-success')?'btn-danger':'btn-success';
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
                           om_btnColorState = om_button.parent('button').hasClass('btn-success')?'btn-success':'btn-danger';
                           om_newBtnColorState = om_button.parent('button').hasClass('btn-success')?'btn-danger':'btn-success';
                           om_button.parent('button').removeClass(om_btnColorState).addClass(om_newBtnColorState);
                        }
                        if (webconf_state === 'fa-toggle-on') {
                           webconf_newState = "fa-toggle-off";
                           webconf_button.removeClass('fa-spinner fa-spin').addClass(webconf_newState);
                           webconf_btnColorState = webconf_button.parent('button').hasClass('btn-success')?'btn-success':'btn-danger';
                           webconf_newBtnColorState = webconf_button.parent('button').hasClass('btn-success')?'btn-danger':'btn-success';
                           webconf_button.parent('button').removeClass(webconf_btnColorState).addClass(webconf_newBtnColorState);
                        }
                    }
                    button.removeClass('fa-spinner fa-spin').addClass(newState);                    
                    btnColorState = button.parent('button').hasClass('btn-success')?'btn-success':'btn-danger';                    
                    newBtnColorState = button.parent('button').hasClass('btn-success')?'btn-danger':'btn-success';                    
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
                           bbb_btnColorState = bbb_button.parent('button').hasClass('btn-success')?'btn-success':'btn-danger';
                           bbb_newBtnColorState = bbb_button.parent('button').hasClass('btn-success')?'btn-danger':'btn-success';
                           bbb_button.parent('button').removeClass(bbb_btnColorState).addClass(bbb_newBtnColorState);
                        }
                        if (webconf_state === 'fa-toggle-on') {
                           webconf_newState = "fa-toggle-off";
                           webconf_button.removeClass('fa-spinner fa-spin').addClass(webconf_newState);
                           webconf_btnColorState = webconf_button.parent('button').hasClass('btn-success')?'btn-success':'btn-danger';
                           webconf_newBtnColorState = webconf_button.parent('button').hasClass('btn-success')?'btn-danger':'btn-success';
                           webconf_button.parent('button').removeClass(webconf_btnColorState).addClass(webconf_newBtnColorState);
                        }
                    }                    
                    button.removeClass('fa-spinner fa-spin').addClass(newState);                    
                    btnColorState = button.parent('button').hasClass('btn-success')?'btn-success':'btn-danger';                    
                    newBtnColorState = button.parent('button').hasClass('btn-success')?'btn-danger':'btn-success';                    
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
                           bbb_btnColorState = bbb_button.parent('button').hasClass('btn-success')?'btn-success':'btn-danger';
                           bbb_newBtnColorState = bbb_button.parent('button').hasClass('btn-success')?'btn-danger':'btn-success';
                           bbb_button.parent('button').removeClass(bbb_btnColorState).addClass(bbb_newBtnColorState);
                        }
                        if (om_state === 'fa-toggle-on') {
                           om_newState = "fa-toggle-off";
                           om_button.removeClass('fa-spinner fa-spin').addClass(om_newState);
                           om_btnColorState = om_button.parent('button').hasClass('btn-success')?'btn-success':'btn-danger';
                           om_newBtnColorState = om_button.parent('button').hasClass('btn-success')?'btn-danger':'btn-success';
                           om_button.parent('button').removeClass(om_btnColorState).addClass(om_newBtnColorState);
                        }
                    }
                    button.removeClass('fa-spinner fa-spin').addClass(newState);
                    btnColorState = button.parent('button').hasClass('btn-success')?'btn-success':'btn-danger';
                    newBtnColorState = button.parent('button').hasClass('btn-success')?'btn-danger':'btn-success';
                    button.parent('button').removeClass(btnColorState).addClass(newBtnColorState);
                });
    });
</script>
@endsection