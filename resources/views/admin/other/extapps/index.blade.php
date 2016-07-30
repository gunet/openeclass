@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class="row extapp">
         <div class='col-xs-12'>
            <table class="table-default dataTable no-footer extapp-table">
                <thead class='list-header'>
                    <td>{{ trans('langExtAppName') }}</td>
                    <td>{{ trans('langExtAppDescription') }}</td>
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
                                        <button type="button" class="btn btn-default" data-app="{{ getIndirectReference($app->getName()) }}"  data-toggle='modal' data-target='#noSettings'> 
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
                    <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
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
@endsection