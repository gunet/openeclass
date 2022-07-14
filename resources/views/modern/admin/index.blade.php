@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

        <div class="container-fluid main-container">

            <div class="row">

                <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
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

                                {!! isset($action_bar) ?  $action_bar : '' !!}

                                @if ($release_info and version_compare($release_info->release, ECLASS_VERSION) > 0)
                                    <div class='row'>
                                        <div class='col-md-12'>
                                            <div class='panel panel-success'>
                                                <div class='panel-heading notes_thead'>
                                                    {{ trans('langNewEclassVersion') }}
                                                </div>
                                                <div class='panel-body panel-body-exercise'>
                                                    {!! sprintf( trans('langNewEclassVersionInfo'), "<strong>" . q($release_info->release) . "</strong>",
                                                                "<a href='https://www.openeclass.org/' target='_blank'>www.openeclass.org</a>") !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                
                                <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                                    <div class='panel panel-admin'>
                                        <div class='panel-body panel-body-exercise ps-3 pt-2 pb-2 text-white'>
                                        {{ trans('langOnlineUsers') }} : <b>{{ getOnlineUsers() }}</b>
                                        </div>
                                    </div>
                                </div>
                                

                                @if ($is_admin)
                                    <div class='row p-2'></div>
                                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                                        <div class='panel panel-default'>
                                            <div class='panel-heading notes_thead'>
                                                <h3 class='panel-title text-white'>{{ trans('langQuickLinks') }}</h3>
                                            </div>
                                            <div class='panel-body panel-body-admin ps-3 pe-3 '>
                                                <div class='row pt-2 pb-2'>
                                                    <a href='search_user.php' class='w-50 btn btn-transparent text-secondary btn-xs'>{{ trans('langSearchUser') }}</a>
                                                    <a href='searchcours.php' class='w-50 btn btn-transparent text-secondary btn-xs'>{{ trans('langSearchCourse') }}</a>
                                                </div>
                                                <div class='row pt-2 pb-2'>
                                                    <a href='hierarchy.php' class='w-50 btn btn-transparent text-secondary btn-xs'>{{ trans('langHierarchy') }}</a>
                                                    <a href='eclassconf.php' class='w-50 btn btn-transparent text-secondary btn-xs'>{{ trans('langConfig') }}</a>
                                                </div>
                                                <div class='row pt-2 pb-2'>
                                                    <a href='theme_options.php' class='w-50 btn btn-transparent text-secondary btn-xs'>{{ trans('langThemeSettings') }}</a>
                                                    <a href='extapp.php' class='w-50 btn btn-transparent text-secondary btn-xs'>{{ trans('langExternalTools') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                @endif

                                <div class='row p-2'></div>

                                
                                <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                                    <div class='panel panel-default'>
                                        <div class='panel-heading notes_thead'>
                                            <div class='panel-title fs-5 text-white ps-3 pt-2 pb-2'>{{ trans('langPlatformIdentity') }}</div>
                                        </div>
                                        <div class='panel-body panel-body-admin ps-3 pt-2 pb-2'>
                                            <div class='row p-2 margin-bottom-thin'>
                                                <div class='col-sm-5'>
                                                    {!! icon('fa-check') !!} <strong class='control-label-notes'>{{ trans('langWebServerVersion') }}</strong>
                                                </div>
                                                <div class='col-sm-7'>
                                                    <em>{{ $_SERVER['SERVER_SOFTWARE'] }}</em>
                                                </div>
                                            </div>
                                            <div class='row p-2 margin-bottom-thin'>
                                                <div class='col-sm-5'>
                                                    {!! $validPHP ? icon('fa-check') : icon('fa-ban') !!}
                                                    <strong class='control-label-notes'>{{ trans('langPHPVersion') }}</strong>
                                                </div>
                                                <div class='col-sm-7'>
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
                                                <div class='col-sm-5'>
                                                    {!! icon('fa-check') !!}
                                                    <strong class='control-label-notes'>{{ trans('langMySqlVersion') }}</strong>
                                                </div>
                                                <div class='col-sm-7'>
                                                    <em>{{ $serverVersion }}</em>
                                                </div>
                                            </div>
                                            <div class='row p-2 margin-bottom-thin'>
                                                <div class='col-sm-5'>
                                                    <strong class='control-label-notes'>{{ trans('langVersion') }}:</strong>
                                                </div>
                                                <div class='col-sm-7'>
                                                    <em>{{ $siteName }} {{ ECLASS_VERSION }}</em>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class='row p-2'></div>

                                    <div class='panel panel-default'>
                                        <div class='panel-heading notes_thead'>
                                            <div class='panel-title fs-5 text-white ps-3 pt-2 pb-2'>{{ trans('langInfoAdmin') }}</div>
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

                                    <div class='row p-2'></div>

                                    <div class='panel panel-default'>
                                        <div class='panel-heading notes_thead'>
                                            <div class='panel-title fs-5 text-white ps-3 pt-2 pb-2'>{{ trans('langIndexInfo') }}</div>
                                        </div>
                                        <div class='panel-body panel-body-admin'>
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

                                    <div class='row p-2'></div>

                                    @if (count($cronParams) > 0)
                                    <div class='panel panel-default'>
                                        <div class='panel-heading notes_thead'>
                                            <div class='panel-title fs-5 text-white ps-3 pt-2 pb-2'>{{ trans('langCronInfo') }}</div>
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
@endsection