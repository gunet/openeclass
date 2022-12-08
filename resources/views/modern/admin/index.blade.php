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

                                <!---------------------------------------------------------------------------------------->
                                <!---------------------------------- Include admin panels -------------------------------->

                                  @include('layouts.partials.sidebarAdmin')

                                <!----------------------------------------------------------------------------------------->
                                <!----------------------------------------------------------------------------------------->



                                @if ($is_admin)
                                    <div class='col-lg-6 col-12 mt-3'>
                                        <div class='row'>
                                            {{--<div class='col-sm-12'>
                                                <div class='panel panel-admin shadow'>
                                                    <div class='panel-heading text-center'>
                                                        <span class='colorPalette'>{{ trans('langQuickLinks') }}</span>
                                                    </div>
                                                    <div class='panel-body'>
                                                        <ul class="list-group list-group-flush">
                                                            <li class="list-group-item border-0 admin-list-group">
                                                                <a href="search_user.php" class='list-group-item'>
                                                                    <div class='d-inline-flex'>
                                                                        <span class='fa fa-caret-right fa-fw mt-1 orangeText'></span>
                                                                        <span class='toolAdminText'>{{ trans('langSearchUser') }}</span>
                                                                    </div>
                                                                </a>
                                                            </li>
                                                            <li class="list-group-item border-0 admin-list-group">
                                                                <a href="searchcours.php" class='list-group-item'>
                                                                    <div class='d-inline-flex'>
                                                                        <span class='fa fa-caret-right fa-fw mt-1 orangeText'></span>
                                                                        <span class='toolAdminText'>{{ trans('langSearchCourse') }}</span>
                                                                    </div>
                                                                </a>
                                                            </li>
                                                            <li class="list-group-item border-0 admin-list-group">
                                                                <a href="hierarchy.php" class='list-group-item'>
                                                                    <div class='d-inline-flex'>
                                                                        <span class='fa fa-caret-right fa-fw mt-1 orangeText'></span>
                                                                        <span class='toolAdminText'>{{ trans('langHierarchy') }}</span>
                                                                    </div>
                                                                </a>
                                                            </li>
                                                            <li class="list-group-item border-0 admin-list-group">
                                                                <a href="eclassconf.php" class='list-group-item'>
                                                                    <div class='d-inline-flex'>
                                                                        <span class='fa fa-caret-right fa-fw mt-1 orangeText'></span>
                                                                        <span class='toolAdminText'>{{ trans('langConfig') }}</span>
                                                                    </div>
                                                                </a>
                                                            </li>
                                                            <li class="list-group-item border-0 admin-list-group">
                                                                <a href="theme_options.php" class='list-group-item'>
                                                                    <div class='d-inline-flex'>
                                                                        <span class='fa fa-caret-right fa-fw mt-1 orangeText'></span>
                                                                        <span class='toolAdminText'>{{ trans('langThemeSettings') }}</span>
                                                                    </div>
                                                                </a>
                                                            </li>
                                                            <li class="list-group-item border-0 admin-list-group">
                                                                <a href="extapp.php" class='list-group-item'>
                                                                    <div class='d-inline-flex'>
                                                                        <span class='fa fa-caret-right fa-fw mt-1 orangeText'></span>
                                                                        <span class='toolAdminText'>{{ trans('langExternalTools') }}</span>
                                                                    </div>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>--}}
                                            <div class='col-sm-12'>
                                                <div class='panel panel-admin p-md-3 bg-white'>
                                                    <div class='panel-heading bg-body'>
                                                        <div class='col-12 Help-panel-heading'>
                                                            <span class='panel-title text-uppercase Help-text-panel-heading'>{{ trans('langPlatformIdentity') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class='panel-body'>
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-sm-6'>
                                                                <strong class='control-label-notes'>{{ trans('langWebServerVersion') }}</strong>
                                                            </div>
                                                            <div class='col-sm-6'>
                                                                <em>{{ $_SERVER['SERVER_SOFTWARE'] }}</em>
                                                            </div>
                                                        </div>
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-sm-6'>
                                                                <strong class='control-label-notes'>{{ trans('langPHPVersion') }}</strong>
                                                            </div>
                                                            <div class='col-sm-6'>
                                                                <em>{{ PHP_VERSION }}</em>
                                                            </div>
                                                        </div>
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-sm-6'>
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
                                                <div class='panel panel-admin p-md-3 bg-white'>
                                                    <div class='panel-heading bg-body'>
                                                        <div class='col-12 Help-panel-heading'>
                                                            <span class='panel-title text-uppercase Help-text-panel-heading'>{{ trans('langIndexInfo') }}</span>
                                                        </div>
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

                                <div class='col-sm-12 d-flex justify-content-center mt-5'>
                                    <a class="d-flex justify-content-center align-items-center BtnMoreInfoAdmin" data-bs-toggle="collapse" href="#collapseMoreInfoAdmin" role="button" aria-expanded="false" aria-controls="collapseExample">
                                        <span class='text-uppercase'>{{trans('langMore')}} <span class='fa fa-arrow-down'></span>
                                    </a>
                                </div>

                                <div class="collapse" id="collapseMoreInfoAdmin">
                                    <div class='col-12 mt-5'>

                                        <div class='panel panel-admin'>
                                            <div class='panel-heading ps-3 pt-2 pb-2 text-center Borders'>
                                            {{ trans('langOnlineUsers') }} : <b>{{ getOnlineUsers() }}</b>
                                            </div>
                                        </div>

                                        <div class='panel panel-info mt-3'>
                                            <div class='panel-heading'>
                                                <div class='panel-title text-center'>{{ trans('langInfoAdmin') }}</div>
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
                                        <div class='panel panel-info mt-3'>
                                            <div class='panel-heading'>
                                                <div class='panel-title text-center'>{{ trans('langCronInfo') }}</div>
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
