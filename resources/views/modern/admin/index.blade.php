@push('head_scripts')
    <script type='text/javascript'>
        var optwindow = null;
        var reidxwindow = null;

        function optpopup(url, w, h) {
            var left = (screen.width/2)-(w/2);
            var top = (screen.height/2)-(h/2);

            if (optwindow == null || optwindow.closed) {
                optwindow = window.open(url, 'optpopup', 'resizable=yes, scrollbars=yes, status=yes, width='+w+', height='+h+', top='+top+', left='+left);
                if (window.focus && optwindow !== null) {
                    optwindow.focus();
                }
            } else {
                optwindow.focus();
            }

            return false;
        }

        function reidxpopup(url, w, h) {
            var left = (screen.width/2)-(w/2);
            var top = (screen.height/2)-(h/2);

            if (reidxwindow == null || reidxwindow.closed) {
                reidxwindow = window.open(url, 'reidxpopup', 'resizable=yes, scrollbars=yes, status=yes, width='+w+', height='+h+', top='+top+', left='+left);
                if (window.focus && reidxwindow !== null) {
                    reidxwindow.focus();
                }
            } else {
                reidxwindow.focus();
            }

            return false;
        }

        $(document).ready(function() {

            $('#confirmReindexDialog').modal({
                show: false,
                keyboard: false,
                backdrop: 'static'
            });

            $("#confirmReindexCancel").click(function() {
                $("#confirmReindexDialog").modal("hide");
            });

            $("#confirmReindexOk").click(function() {
                $("#confirmReindexDialog").modal("hide");
                reidxpopup('../search/idxpopup.php?reindex', 600, 500);
            });

            $('#reindex_link').click(function(event) {
                event.preventDefault();
                $("#confirmReindexDialog").modal("show");
            });

        });

    </script>
@endpush

@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            <div class='col-12 my-4'>
                <h1>{{ $pageName }}</h1>
            </div>

            @if(isset($action_bar))
                {!! $action_bar !!}
            @endif

            @include('layouts.partials.show_alert') 

            <!---------------------------------------------------------------------------------------->
            <!---------------------------------- Include admin panels -------------------------------->
                @include('layouts.partials.sidebarAdmin')
            <!----------------------------------------------------------------------------------------->
            <!----------------------------------------------------------------------------------------->

            <div class='col-lg-6 col-12 mt-4'>
                <div class='row row-cols-1 row-cols-lg-2 g-4'>
                    <div class='col'>
                        <div class='card panelCard h-100'>
                            <div class='card-body d-flex justify-content-center align-items-center'>
                                <div>
                                    <h1 class='d-flex justify-content-center align-items-center'>
                                        <i class='fa-solid fa-user pe-2'></i>{{ getOnlineUsers() }}
                                    </h1>
                                    <div class='form-label text-center'>{{ trans('langOnlineUsers') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='col'>
                        <div class='card panelCard h-100'>
                            <div class='card-body d-flex justify-content-center align-items-center'>
                                <div>
                                    <h1 class='d-flex justify-content-center align-items-center'>
                                        <i class="fa-solid fa-user-tie pe-2"></i>
                                        {{ $count_prof_requests }}
                                    </h1>
                                    <div class='form-label text-center'>{{ trans('langOpenRequests') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            @if ($is_admin)
                <div class='col-12 mt-4'>
                    <div class='row row-cols-1 row-cols-lg-2 g-3 g-lg-4'>

                        <div class='col'>
                            <div class='card panelCard border-card-left-default px-lg-4 py-lg-3 h-100'>
                                <div class='card-body'>
                                    <div class='row p-2 margin-bottom-thin'>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-label'>{{ trans('langWebServerVersion') }}</div>
                                        </div>
                                        <div class='col-lg-6 col-12'>
                                            <div>{{ $_SERVER['SERVER_SOFTWARE'] }}</div>
                                        </div>
                                    </div>
                                    <div class='row p-2 margin-bottom-thin'>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-label'>{{ trans('langPHPVersion') }}</div>
                                        </div>
                                        <div class='col-lg-6 col-12'>
                                            <div>{{ PHP_VERSION }}</div>
                                        </div>
                                    </div>
                                    <div class='row p-2 margin-bottom-thin'>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-label'>{{ trans('langMySqlVersion') }}</div>
                                        </div>
                                        <div class='col-6'>
                                            <div>{{ $serverVersion }}</div>
                                        </div>
                                    </div>
                                    <div class='row p-2 margin-bottom-thin'>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-label'>{{ trans('langVersion') }}</div>
                                        </div>
                                        <div class='col-lg-6 col-12'>
                                            <div>{{ $siteName }} {{ ECLASS_VERSION }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class='col'>
                            <div class='card panelCard border-card-left-default px-lg-4 py-lg-3 h-100'>
                                <div class='card-body'>
                                    <div class='row p-2 margin-bottom-thin'>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-label'>{{ trans('langIndexNumDocs') }}</div>
                                        </div>
                                        <div class='col-lg-6 col-12'>
                                            <div>{{ $numDocs }}</div>
                                        </div>
                                    </div>
                                    <div class='row p-2 margin-bottom-thin'>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-label'>{{ trans('langIndexIsOptimized') }}</div>
                                        </div>
                                        <div class='col-lg-6 col-12'>
                                            <div>{{ $isOpt }}</div>
                                        </div>
                                    </div>
                                    <div class='row p-2 margin-bottom-thin'>
                                        <div class='col-lg-5 col-12'>
                                            <a class='btn submitAdminBtn' href='../search/optpopup.php' onclick="return optpopup('../search/optpopup.php', 600, 500)">{{ trans('langOptimize') }}</a>
                                        </div>
                                        <div class='col-lg-7 col-12'>
                                            <a class='btn submitAdminBtn mt-lg-0 mt-3' id='reindex_link' href='../search/idxpopup.php?reindex'>{{ trans('langReindex') }}</a>
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

            <div class='col-12 mt-4'>

                @php
                    $colSize = '';
                    if (count($cronParams) > 0) {
                        $colSize = '2';
                    } else {
                        $colSize = '1';
                    }
                @endphp
                <div class="row row-cols-1 row-cols-lg-{{ $colSize }} g-3 g-lg-4">

                    <div class='col'>
                        <div class='card panelCard border-card-left-default px-lg-4 py-lg-3 h-100'>
                            <div class='card-body'>

                                <div class='row p-2 margin-bottom-thin'>
                                    <div class='col-lg-6 col-12'>
                                        <div class='form-label'>{{ trans('langLastLesson') }}</div>
                                    </div>
                                    <div class='col-lg-6 col-12'>
                                        <div>
                                            @if ($lastCreatedCourse)
                                                <b>{{ $lastCreatedCourse->title }}</b>
                                                ({{ $lastCreatedCourse->code }}, {{ $lastCreatedCourse->prof_names }})
                                            @else
                                                {{ trans('langNoCourses') }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class='row p-2 margin-bottom-thin'>
                                    <div class='col-lg-6 col-12'>
                                        <div class='form-label'>{{ trans('langLastProf') }}</div>
                                    </div>
                                    <div class='col-lg-6 col-12'>
                                        <div>
                                            <b>{{ $lastProfReg->givenname . " " . $lastProfReg->surname }}</b>
                                            ({{ $lastProfReg->username }}, {{ date("j/n/Y H:i", strtotime($lastProfReg->registered_at)) }})
                                        </div>
                                    </div>
                                </div>
                                <div class='row p-2 margin-bottom-thin'>
                                    <div class='col-lg-6 col-12'>
                                        <div class='form-label'>{{ trans('langLastStud') }}</div>
                                    </div>
                                    <div class='col-lg-6 col-12'>
                                        <div>
                                            @if ($lastStudReg)
                                                <b>{{ $lastStudReg->givenname . " " . $lastStudReg->surname }}</b>
                                                ({{ $lastStudReg->username . ", " . date("j/n/Y H:i", strtotime($lastStudReg->registered_at)) }})
                                            @else
                                                {{ trans('langLastStudNone') }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class='row p-2 margin-bottom-thin'>
                                    <div class='col-lg-6 col-12'>
                                        <div class='form-label'>{{ trans('langAfterLastLoginInfo') }}</div>
                                    </div>
                                    <div class='col-lg-6 col-12'>
                                        <div>
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
                        </div>
                    </div>

                    <div class='col'>
                        <div class='card panelCard border-card-left-default px-lg-4 py-lg-3 h-100'>
                            <div class='card-body'>

                                <div class='row p-2 margin-bottom-thin'>
                                    <div class='col-lg-6 col-12'>
                                        <div class='form-label'>{{ trans('langH5p') }}</div>
                                    </div>
                                    @if ($ts)
                                        <div class='col-lg-6 col-12'>
                                            <div>
                                                <a href='h5pconf.php'>{{ trans('langlastUpdated') }}: {{ $ts }}</a>
                                            </div>
                                        </div>
                                    @else
                                        <div class='col-lg-6 col-12'>
                                            <div>
                                                <a style='color:red; font-weight: bold;' href='h5pconf.php'>{{ trans('langUpdateRequired') }} !</a>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if (count($cronParams) > 0)
                                    <div class='row p-2 margin-bottom-thin'>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-label'>{{ trans('langCronName') }}</div>
                                        </div>
                                        <div class='col-lg-6 col-12'>
                                            <div>
                                                {{ trans('langCronLastRun') }}
                                                <div class='row p-2'>
                                                    @foreach ($cronParams as $cronParam)
                                                        <div class='col-12'>{{ $cronParam->name }}</div>
                                                        <div class='col-12'>{{ $cronParam->last_run }}</div>
                                                    @endforeach
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
</div>
@endsection
