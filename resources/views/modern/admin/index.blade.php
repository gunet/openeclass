@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

        <div class="container-fluid main-container">

            <div class="row">

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                        <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

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

                                <!---------------------------------------->
                                <!---------------------------------------->
                                <!---------------------------------------->
                                <!-- Προσθήκη panels του διαχειριστικού -->

                                  @include('layouts.partials.sidebarAdmin')

                                <!---------------------------------------->
                                <!---------------------------------------->
                                <!---------------------------------------->
                                

                                <!-- @if ($release_info and version_compare($release_info->release, ECLASS_VERSION) > 0)
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
                                @endif -->


                                

                                @if ($is_admin)
                                    <div class='col-lg-6 col-12 mt-3'>
                                        <div class='row'>
                                            <div class='col-sm-12'>
                                                <div class='panel panel-admin'>
                                                    <div class='panel-heading text-center'>
                                                        <span class='text-white'>{{ trans('langQuickLinks') }}</span>
                                                    </div>
                                                    <div class='panel-body'>
                                                        <div class='row pt-2 pb-2'>
                                                            <a href='search_user.php' class='w-50 btn btn-transparent text-primary btn-xs'>{{ trans('langSearchUser') }}</a>
                                                            <a href='searchcours.php' class='w-50 btn btn-transparent text-primary btn-xs'>{{ trans('langSearchCourse') }}</a>
                                                        </div>
                                                        <div class='row pt-2 pb-2'>
                                                            <a href='hierarchy.php' class='w-50 btn btn-transparent text-primary btn-xs'>{{ trans('langHierarchy') }}</a>
                                                            <a href='eclassconf.php' class='w-50 btn btn-transparent text-primary btn-xs'>{{ trans('langConfig') }}</a>
                                                        </div>
                                                        <div class='row pt-2 pb-2'>
                                                            <a href='theme_options.php' class='w-50 btn btn-transparent text-primary btn-xs'>{{ trans('langThemeSettings') }}</a>
                                                            <a href='extapp.php' class='w-50 btn btn-transparent text-primary btn-xs'>{{ trans('langExternalTools') }}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='col-sm-12 mt-3'>
                                                <div class='panel panel-admin'>
                                                    <div class='panel-heading text-center'>
                                                        <div class='panel-title'>{{ trans('langPlatformIdentity') }}</div>
                                                    </div>
                                                    <div class='panel-body'>
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-sm-6'>
                                                                {!! icon('fa-check') !!} <strong class='control-label-notes'>{{ trans('langWebServerVersion') }}</strong>
                                                            </div>
                                                            <div class='col-sm-6'>
                                                                <em>{{ $_SERVER['SERVER_SOFTWARE'] }}</em>
                                                            </div>
                                                        </div>
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-sm-6'>
                                                                {!! $validPHP ? icon('fa-check') : icon('fa-ban') !!}
                                                                <strong class='control-label-notes'>{{ trans('langPHPVersion') }}</strong>
                                                            </div>
                                                            <div class='col-sm-6'>
                                                                <em>{{ PHP_VERSION }}</em>
                                                            </div>
                                                        </div>
                                                        @if (!$validPHP)
                                                            <div class='row p-2 margin-bottom-thin'>
                                                                <div class='col-sm-12'>
                                                                    <div class='alert alert-danger'>{{ trans('langWarnAboutPHP') }}</div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-sm-6'>
                                                                {!! icon('fa-check') !!}
                                                                <strong class='control-label-notes'>{{ trans('langMySqlVersion') }}</strong>
                                                            </div>
                                                            <div class='col-sm-6'>
                                                                <em>{{ $serverVersion }}</em>
                                                            </div>
                                                        </div>
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-sm-6'>
                                                                <strong class='control-label-notes'>{{ trans('langVersion') }}:</strong>
                                                            </div>
                                                            <div class='col-sm-6'>
                                                                <em>{{ $siteName }} {{ ECLASS_VERSION }}</em>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='col-sm-12 mt-3'>
                                                <div class='panel panel-admin'>
                                                    <div class='panel-heading'>
                                                        <div class='panel-title text-center'>{{ trans('langIndexInfo') }}</div>
                                                    </div>
                                                    <div class='panel-body'>
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-sm-6'>
                                                            <strong class='control-label-notes'>{{ trans('langIndexNumDocs') }}:</strong>
                                                            </div>
                                                            <div class='col-sm-6'>
                                                                {{ $numDocs }}
                                                            </div>
                                                        </div>
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-sm-6'>
                                                            <strong class='control-label-notes'>{{ trans('langIndexIsOptimized') }}</strong>
                                                            </div>
                                                            <div class='col-sm-6'>
                                                                {{ $isOpt }}
                                                            </div>
                                                        </div>
                                                        @if ($idxHasDeletions)
                                                            <div class='row p-2 margin-bottom-thin'>
                                                                <div class='col-sm-9 col-sm-offset-3'>
                                                                    <a href='../search/optpopup.php' onclick="return optpopup('../search/optpopup.php', 600, 500)">{{ trans('langOptimize') }}</a>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-sm-9 col-sm-offset-3'>
                                                                <a id='reindex_link' href='../search/idxpopup.php?reindex'>{{ trans('langReindex') }}</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {!! $idxModal !!}
                                                <img src='cron.php' width='2' height='1' alt=''>
                                            </div>
                                        </div>
                                    </div>
                                    
                                @endif

                                <div class='col-sm-12 mt-3'>
                                    <a class="btn btn-primary w-100" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                                        {{trans('langMore')}}
                                    </a>
                                </div>

                                <div class="collapse" id="collapseExample">
                                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>

                                        <div class='panel panel-default'>
                                            <div class='panel-body ps-3 pt-2 pb-2 control-label-notes text-center'>
                                            {{ trans('langOnlineUsers') }} : <b>{{ getOnlineUsers() }}</b>
                                            </div>
                                        </div>

                                        <div class='panel panel-default mt-3'>
                                            <div class='panel-heading'>
                                                <div class='panel-title fs-5 ps-3 pt-2 pb-2'>{{ trans('langInfoAdmin') }}</div>
                                            </div>
                                            <div class='panel-body panel-body-admin'>
                                                <div class='row p-2 margin-bottom-thin'>
                                                    <div class='col-sm-5'>
                                                        <strong class='control-label-notes'>{{ trans('langOpenRequests') }}:</strong>
                                                    </div>
                                                    <div class='col-sm-7'>
                                                        @if ($count_prof_requests)
                                                            {{ trans('langThereAre') }} {{ $count_prof_requests }} {{ trans('langOpenRequests') }}
                                                        @else
                                                            {{ trans('langNoOpenRequests') }}
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class='row p-2 margin-bottom-thin'>
                                                    <div class='col-sm-5'>
                                                        <strong class='control-label-notes'>{{ trans('langLastLesson') }}</strong>
                                                    </div>
                                                    <div class='col-sm-7'>
                                                        @if ($lastCreatedCourse)
                                                            <b>{{ $lastCreatedCourse->title }}</b>
                                                            ({{ $lastCreatedCourse->code }}, {{ $lastCreatedCourse->prof_names }})
                                                        @else
                                                            {{ trans('langNoCourses') }}
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class='row p-2 margin-bottom-thin'>
                                                    <div class='col-sm-5'>
                                                        <strong class='control-label-notes'>{{ trans('langLastProf') }}</strong>
                                                    </div>
                                                    <div class='col-sm-7'>
                                                        <b>{{ $lastProfReg->givenname . " " . $lastProfReg->surname }}</b>
                                                        ({{ $lastProfReg->username }}, {{ date("j/n/Y H:i", strtotime($lastProfReg->registered_at)) }})
                                                    </div>
                                                </div>
                                                <div class='row p-2 margin-bottom-thin'>
                                                    <div class='col-sm-5'>
                                                        <strong class='control-label-notes'>{{ trans('langLastStud') }}</strong>
                                                    </div>
                                                    <div class='col-sm-7'>
                                                        @if ($lastStudReg)
                                                            <b>{{ $lastStudReg->givenname . " " . $lastStudReg->surname }}</b>
                                                            ({{ $lastStudReg->username . ", " . date("j/n/Y H:i", strtotime($lastStudReg->registered_at)) }})
                                                        @else
                                                            {{ trans('langLastStudNone') }}
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class='row p-2 margin-bottom-thin'>
                                                    <div class='col-sm-5'>
                                                        <strong class='control-label-notes'>{{ trans('langAfterLastLoginInfo') }}</strong>
                                                    </div>
                                                    <div class='col-sm-7'>
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

                                        @if (count($cronParams) > 0)
                                        <div class='panel panel-default mt-3'>
                                            <div class='panel-heading'>
                                                <div class='panel-title fs-5 ps-3 pt-2 pb-2'>{{ trans('langCronInfo') }}</div>
                                            </div>
                                            <div class='panel-body panel-body-admin'>
                                                <div class='row p-2 margin-bottom-thin'>
                                                    <div class='col-sm-5'>
                                                    <strong class='control-label-notes'>{{ trans('langCronName') }}</strong>
                                                    </div>
                                                    <div class='col-sm-7'>
                                                        {{ trans('langCronLastRun') }}
                                                        <div class='row p-2'>
                                                            @foreach ($cronParams as $cronParam)
                                                                <div class='col-6'>{{ $cronParam->name }}</div>
                                                                <div class='col-6'>{{ $cronParam->last_run }}</div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                    </div>
            </div>
        </div>
    </div>
</div>
@endsection