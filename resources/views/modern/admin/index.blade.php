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

            @include('layouts.partials.legend_view')

            @if(isset($action_bar))
                {!! $action_bar !!}
            @else
                <div class='mt-4'></div>
            @endif

            @if(Session::has('message'))
                <div class='col-12 all-alerts'>
                    <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                        @php
                            $alert_type = '';
                            if (Session::get('alert-class', 'alert-info') == 'alert-success'){
                                $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                            } elseif (Session::get('alert-class', 'alert-info') == 'alert-info'){
                                $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                            } elseif (Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                            } else {
                                $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                            }
                        @endphp

                        @if(is_array(Session::get('message')))
                            @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                            {!! $alert_type !!}<span>
                            @foreach($messageArray as $message)
                                {!! $message !!}
                            @endforeach</span>
                        @else
                            {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                        @endif

                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

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
                    <div class='row row-cols-1 row-cols-lg-2 g-4'>

                        <div class='col'>
                            <div class='card panelCard border-card-left-default px-lg-4 py-lg-3 bg-default h-100'>
                                <div class='card-body'>
                                    <div class='row p-2 margin-bottom-thin'>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-label'>{{ trans('langWebServerVersion') }}</div>
                                        </div>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-value'>{{ $_SERVER['SERVER_SOFTWARE'] }}</div>
                                        </div>
                                    </div>
                                    <div class='row p-2 margin-bottom-thin'>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-label'>{{ trans('langPHPVersion') }}</div>
                                        </div>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-value'>{{ PHP_VERSION }}</div>
                                        </div>
                                    </div>
                                    <div class='row p-2 margin-bottom-thin'>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-label'>{{ trans('langMySqlVersion') }}</div>
                                        </div>
                                        <div class='col-6'>
                                            <div class='form-value'>{{ $serverVersion }}</div>
                                        </div>
                                    </div>
                                    <div class='row p-2 margin-bottom-thin'>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-label'>{{ trans('langVersion') }}</div>
                                        </div>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-value'>{{ $siteName }} {{ ECLASS_VERSION }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class='col'>
                            <div class='card panelCard border-card-left-default px-lg-4 py-lg-3 bg-default h-100'>
                                <div class='card-body'>
                                    <div class='row p-2 margin-bottom-thin'>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-label'>{{ trans('langIndexNumDocs') }}</div>
                                        </div>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-value'>{{ $numDocs }}</div>
                                        </div>
                                    </div>
                                    <div class='row p-2 margin-bottom-thin'>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-label'>{{ trans('langIndexIsOptimized') }}</div>
                                        </div>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-value'>{{ $isOpt }}</div>
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
                <div class="row row-cols-1 row-cols-lg-{{ $colSize }} g-4">

                    <div class='col'>
                        <div class='card panelCard border-card-left-default px-lg-4 py-lg-3 h-100'>
                            <div class='card-body'>

                                <div class='row p-2 margin-bottom-thin'>
                                    <div class='col-lg-6 col-12'>
                                        <div class='form-label'>{{ trans('langLastLesson') }}</div>
                                    </div>
                                    <div class='col-lg-6 col-12'>
                                        <div class='form-value'>
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
                                        <div class='form-value'>
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
                                        <div class='form-value'>
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
                                        <div class='form-value'>
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

                    @if (count($cronParams) > 0)
                        <div class='col'>
                            <div class='card panelCard border-card-left-default px-lg-4 py-lg-3 h-100'>
                                <div class='card-body'>
                                    <div class='row p-2 margin-bottom-thin'>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-label'>{{ trans('langCronName') }}</div>
                                        </div>
                                        <div class='col-lg-6 col-12'>
                                            <div class='form-value'>
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
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
