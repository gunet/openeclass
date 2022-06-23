@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if ($release_info and version_compare($release_info->release, ECLASS_VERSION) > 0)
        <div class='row'>
            <div class='col-md-12'>
                <div class='panel panel-success'>
                    <div class='panel-heading'>
                        {{ trans('langNewEclassVersion') }}
                    </div>
                    <div class='panel-body'>
                        {!! sprintf( trans('langNewEclassVersionInfo'), "<strong>" . q($release_info->release) . "</strong>",
                                    "<a href='https://www.openeclass.org/' target='_blank'>www.openeclass.org</a>") !!}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class='row'>
        <div class='col-md-12'>
            <div class='panel'>
                <div class='panel-body'>
                {{ trans('langOnlineUsers') }} : <b>{{ getOnlineUsers() }}</b>
                </div>
            </div>
        </div>
    </div>

    @if ($is_admin)
        <div class='row'>
            <div class='col-md-12'>
                <div class='panel panel-default'>
                    <div class='panel-heading'>
                        <h3 class='panel-title'>{{ trans('langQuickLinks') }}</h3>
                    </div>
                    <div class='panel-body'>
                        <a href='search_user.php' class='btn btn-default btn-xs'>{{ trans('langSearchUser') }}</a>
                        <a href='searchcours.php' class='btn btn-default btn-xs'>{{ trans('langSearchCourse') }}</a>
                        <a href='hierarchy.php' class='btn btn-default btn-xs'>{{ trans('langHierarchy') }}</a>
                        <a href='eclassconf.php' class='btn btn-default btn-xs'>{{ trans('langConfig') }}</a>
                        <a href='theme_options.php' class='btn btn-default btn-xs'>{{ trans('langThemeSettings') }}</a>
                        <a href='extapp.php' class='btn btn-default btn-xs'>{{ trans('langExternalTools') }}</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class='row'>
        <div class='col-md-12'>
            <div class='panel panel-default'>
                <div class='panel-heading'>
                    <div class='panel-title h3'>{{ trans('langPlatformIdentity') }}</div>
                </div>
                <div class='panel-body'>
                    <div class='row margin-bottom-thin'>
                        <div class='col-sm-3'>
                            {!! icon('fa-check') !!} <strong>{{ trans('langWebServerVersion') }}</strong>
                        </div>
                        <div class='col-sm-9'>
                            <em>{{ $_SERVER['SERVER_SOFTWARE'] }}</em>
                        </div>
                    </div>
                    <div class='row margin-bottom-thin'>
                        <div class='col-sm-3'>
                            {!! $validPHP ? icon('fa-check') : icon('fa-ban') !!}
                            <strong>{{ trans('langPHPVersion') }}</strong>
                        </div>
                        <div class='col-sm-9'>
                            <em>{{ PHP_VERSION }}</em>
                        </div>
                    </div>
                    @if (!$validPHP)
                        <div class='row margin-bottom-thin'>
                            <div class='col-sm-12'>
                                <div class='alert alert-danger'>{{ trans('langWarnAboutPHP') }}</div>
                            </div>
                        </div>
                    @endif
                    <div class='row margin-bottom-thin'>
                        <div class='col-sm-3'>
                            {!! icon('fa-check') !!}
                            <strong>{{ trans('langMySqlVersion') }}</strong>
                        </div>
                        <div class='col-sm-9'>
                            <em>{{ $serverVersion }}</em>
                        </div>
                    </div>
                    <div class='row margin-bottom-thin'>
                        <div class='col-sm-3'>
                            <strong>{{ trans('langVersion') }}:</strong>
                        </div>
                        <div class='col-sm-9'>
                            <em>{{ $siteName }} {{ ECLASS_VERSION }}</em>
                        </div>
                    </div>
                </div>
            </div>
            <div class='panel panel-default'>
                <div class='panel-heading'>
                    <div class='panel-title h3'>{{ trans('langInfoAdmin') }}</div>
                </div>
                <div class='panel-body'>
                    <div class='row margin-bottom-thin'>
                        <div class='col-sm-4'>
                            <strong>{{ trans('langOpenRequests') }}:</strong>
                        </div>
                        <div class='col-sm-8'>
                            @if ($count_prof_requests)
                                {{ trans('langThereAre') }} {{ $count_prof_requests }} {{ trans('langOpenRequests') }}
                            @else
                                {{ trans('langNoOpenRequests') }}
                            @endif
                        </div>
                    </div>
                    <div class='row margin-bottom-thin'>
                        <div class='col-sm-4'>
                            <strong>{{ trans('langLastLesson') }}</strong>
                        </div>
                        <div class='col-sm-8'>
                            @if ($lastCreatedCourse)
                                <b>{{ $lastCreatedCourse->title }}</b>
                                ({{ $lastCreatedCourse->code }}, {{ $lastCreatedCourse->prof_names }})
                            @else
                                {{ trans('langNoCourses') }}
                            @endif
                        </div>
                    </div>
                    <div class='row margin-bottom-thin'>
                        <div class='col-sm-4'>
                            <strong>{{ trans('langLastProf') }}</strong>
                        </div>
                        <div class='col-sm-8'>
                            <b>{{ $lastProfReg->givenname . " " . $lastProfReg->surname }}</b>
                            ({{ $lastProfReg->username }}, {{ date("j/n/Y H:i", strtotime($lastProfReg->registered_at)) }})
                        </div>
                    </div>
                    <div class='row margin-bottom-thin'>
                        <div class='col-sm-4'>
                            <strong>{{ trans('langLastStud') }}</strong>
                        </div>
                        <div class='col-sm-8'>
                            @if ($lastStudReg)
                                <b>{{ $lastStudReg->givenname . " " . $lastStudReg->surname }}</b>
                                ({{ $lastStudReg->username . ", " . date("j/n/Y H:i", strtotime($lastStudReg->registered_at)) }})
                            @else
                                {{ trans('langLastStudNone') }}
                            @endif
                        </div>
                    </div>
                    <div class='row margin-bottom-thin'>
                        <div class='col-sm-4'>
                            <strong>{{ trans('langAfterLastLoginInfo') }}</strong>
                        </div>
                        <div class='col-sm-8'>
                        {{ trans('langAfterLastLogin') }}
                            <ul class='custom_list'>
                              <li>
                                  <b>{{ $lastregisteredprofs }}</b>
                                  {{ trans('langTeachers') }}
                              </li>
                              <li>
                                  <b>{{ $lastregisteredstuds }}</b>
                                  {{ trans('langStudents') }}
                              </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class='panel panel-default'>
                <div class='panel-heading'>
                    <div class='panel-title h3'>{{ trans('langIndexInfo') }}</div>
                </div>
                <div class='panel-body'>
                    <div class='row margin-bottom-thin'>
                        <div class='col-sm-3'>
                        <strong>{{ trans('langIndexNumDocs') }}:</strong>
                        </div>
                        <div class='col-sm-9'>
                            {{ $numDocs }}
                        </div>
                    </div>
                    <div class='row margin-bottom-thin'>
                        <div class='col-sm-3'>
                        <strong>{{ trans('langIndexIsOptimized') }}</strong>
                        </div>
                        <div class='col-sm-9'>
                            {{ $isOpt }}
                        </div>
                    </div>
                    @if ($idxHasDeletions)
                        <div class='row margin-bottom-thin'>
                            <div class='col-sm-9 col-sm-offset-3'>
                                <a href='../search/optpopup.php' onclick="return optpopup('../search/optpopup.php', 600, 500)">{{ trans('langOptimize') }}</a>
                            </div>
                        </div>
                    @endif
                    <div class='row margin-bottom-thin'>
                        <div class='col-sm-9 col-sm-offset-3'>
                            <a id='reindex_link' href='../search/idxpopup.php?reindex'>{{ trans('langReindex') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            {!! $idxModal !!}
            <img src='cron.php' width='2' height='1' alt=''>
            @if (count($cronParams) > 0)
                <div class='panel panel-default'>
                    <div class='panel-heading'>
                        <div class='panel-title h3'>{{ trans('langCronInfo') }}</div>
                    </div>
                    <div class='panel-body'>
                        <div class='row margin-bottom-thin'>
                            <div class='col-sm-3'>
                            <strong>{{ trans('langCronName') }}</strong>
                            </div>
                            <div class='col-sm-9'>
                                {{ trans('langCronLastRun') }}
                                <div class='row'>
                                    @foreach ($cronParams as $cronParam)
                                        <div class='col-xs-6'>{{ $cronParam->name }}</div>
                                        <div class='col-xs-6'>{{ nice_format($cronParam->last_run, true) }}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
