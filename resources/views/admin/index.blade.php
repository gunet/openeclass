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

<main id="main" class="col-12 main-section">
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

            @include('layouts.partials.sidebarAdmin')

            <div class='col-lg-6 col-12 mt-4'>
                <div class='row row-cols-1 row-cols-lg-2 g-4'>
                    @if ($onlineusers > 0)
                        <div class='col'>
                            <div class='card panelCard h-100'>
                                <div class='card-body d-flex justify-content-center align-items-center'>
                                    <div>
                                        <h1 class='d-flex justify-content-center align-items-center'>
                                            <i class='fa-solid fa-user pe-2'></i>{{ $onlineusers }}
                                        </h1>
                                        <div class='form-label text-center'>{{ trans('langOnlineUsers') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
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

                        @if (get_config('ext_solr_enabled') && !empty($coreStats))
                            <div class='col'>
                                <div class='card panelCard border-card-left-default px-lg-4 py-lg-3 h-100'>
                                    <div class='card-body'>
                                        @if (get_config('enable_indexing'))
                                            <div class='row p-2 margin-bottom-thin'>
                                                <div class='col-lg-6 col-12'>
                                                    <div class='form-label'>{{ trans('langIndexNumDocs') }}</div>
                                                </div>
                                                <div class='col-lg-6 col-12'>
                                                    <div>{{ $coreStats['numDocs'] }}</div>
                                                </div>
                                            </div>
                                            <div class='row p-2 margin-bottom-thin'>
                                                <div class='col-lg-6 col-12'>
                                                    <div class='form-label'>{{ trans('langLastUpdate') }}</div>
                                                </div>
                                                <div class='col-lg-6 col-12'>
                                                    <div>{!! format_locale_date((new DateTime($coreStats['lastModified']))->getTimestamp()) !!}</div>
                                                </div>
                                            </div>
                                            <div class='row p-2 margin-bottom-thin'>
                                                <div class='col-lg-6 col-12'>
                                                    <div class='form-label'>{{ trans('langSize') }}</div>
                                                </div>
                                                <div class='col-lg-6 col-12'>
                                                    <div>{{ $coreStats['size'] }}</div>
                                                </div>
                                            </div>
                                            <div class='row p-2 margin-bottom-thin'>
                                                <div class='d-flex justify-content-start align-items-center gap-3 flex-wrap'>
                                                    <a class='btn submitAdminBtn text-nowrap mt-lg-0 mt-3' id='reindex_link' href='../search/idxpopup.php?reindex'>{{ trans('langReindex') }}</a>
                                                </div>
                                            </div>
                                        @else
                                            <div class='row p-2'>
                                                <div class='col-lg-6 col-12'>
                                                    <div class='form-label'>{{ trans('langMetaIndex') }}</div>
                                                </div>
                                                <div class='col-lg-6 col-12'>
                                                    {{ trans('langIndexDisabled') }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                {!! $idxModal !!}
                                <img src='cron.php' width='2' height='1' alt=''>
                            </div>
                        @elseif (get_config('enable_indexing'))
                            <div class='col'>
                                <div class='card panelCard border-card-left-default px-lg-4 py-lg-3 h-100'>
                                    <div class='card-body'>
                                        @if (get_config('enable_indexing'))
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
                                                <div class='d-flex justify-content-start align-items-center gap-3 flex-wrap'>
                                                    <a class='btn submitAdminBtn text-nowrap' href='../search/optpopup.php' onclick="return optpopup('../search/optpopup.php', 600, 500)">{{ trans('langOptimize') }}</a>
                                                    <a class='btn submitAdminBtn text-nowrap mt-lg-0 mt-3' id='reindex_link' href='../search/idxpopup.php?reindex'>{{ trans('langReindex') }}</a>
                                                </div>
                                            </div>
                                        @else
                                            <div class='row p-2'>
                                                <div class='col-lg-6 col-12'>
                                                    <div class='form-label'>{{ trans('langMetaIndex') }}</div>
                                                </div>
                                                <div class='col-lg-6 col-12'>
                                                    {{ trans('langIndexDisabled') }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                {!! $idxModal !!}
                                <img src='cron.php' width='2' height='1' alt=''>
                            </div>
                        @endif
                    </div>
                </div>

            @endif

            <div class='col-12 mt-4'>

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
                                                <a class='TextBold Accent-200-cl' href='h5pconf.php'>{{ trans('langUpdateRequired') }} !</a>
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

            {{-- OpenBadges Statistics Section --}}
            @php
                $openBadgesApp = ExtAppManager::getApp('openbadges');
                $openBadgesEnabled = $openBadgesApp && $openBadgesApp->isEnabled();
            @endphp
            @if ($is_admin && isset($badge_stats) && $openBadgesEnabled)
                <div class='col-12 mt-4'>
                    <div class='card panelCard border-card-left-default px-lg-4 py-lg-3'>
                        <div class='card-header border-0 px-0'>
                            <h3 class='mb-0'>
                                <i class='fa-solid fa-certificate pe-2 Accent-200-cl'></i>
                                {{ trans('langOpenBadgesStatistics') }}
                            </h3>
                        </div>
                        <div class='card-body px-0'>
                            
                            {{-- Primary Statistics Grid --}}
                            <div class='row row-cols-2 row-cols-sm-4 g-3 g-lg-4 mb-4'>
                                
                                {{-- Users with Connected Backpack --}}
                                <div class='col'>
                                    <div class='card statistics-card drop-shadow card-default h-100'>
                                        <div class='card-body d-flex justify-content-center align-items-center'>
                                            <div class='text-center w-100'>
                                                <div class='d-flex justify-content-center align-items-center mb-2'>
                                                    <i class='fa-solid fa-users fa-2x text-primary'></i>
                                                    <h2 class='mb-0 ms-3'>{{ $badge_stats['users_with_backpack'] }}</h2>
                                                </div>
                                                <p class='form-label text-center mb-0'>{{ trans('langUsersWithBackpack') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Active Backpack Users --}}
                                <div class='col'>
                                    <div class='card statistics-card drop-shadow card-default h-100'>
                                        <div class='card-body d-flex justify-content-center align-items-center'>
                                            <div class='text-center w-100'>
                                                <div class='d-flex justify-content-center align-items-center mb-2'>
                                                    <i class='fa-solid fa-user-check fa-2x text-success'></i>
                                                    <h2 class='mb-0 ms-3'>{{ $badge_stats['active_backpack_users'] }}</h2>
                                                </div>
                                                <p class='form-label text-center mb-0'>{{ trans('langActiveBackpackUsers') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Exported Badges --}}
                                <div class='col'>
                                    <div class='card statistics-card drop-shadow card-default h-100'>
                                        <div class='card-body d-flex justify-content-center align-items-center'>
                                            <div class='text-center w-100'>
                                                <div class='d-flex justify-content-center align-items-center mb-2'>
                                                    <i class='fa-solid fa-upload fa-2x text-info'></i>
                                                    <h2 class='mb-0 ms-3'>{{ $badge_stats['exported_badges'] }}</h2>
                                                </div>
                                                <p class='form-label text-center mb-0'>{{ trans('langExportedBadges') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Imported Badges --}}
                                <div class='col'>
                                    <div class='card statistics-card drop-shadow card-default h-100'>
                                        <div class='card-body d-flex justify-content-center align-items-center'>
                                            <div class='text-center w-100'>
                                                <div class='d-flex justify-content-center align-items-center mb-2'>
                                                    <i class='fa-solid fa-download fa-2x text-warning'></i>
                                                    <h2 class='mb-0 ms-3'>{{ $badge_stats['imported_badges'] }}</h2>
                                                </div>
                                                <p class='form-label text-center mb-0'>{{ trans('langImportedBadges') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- Secondary Statistics Grid --}}
                            <div class='row row-cols-2 row-cols-sm-4 g-3 g-lg-4 mb-4'>
                                
                                {{-- Total Local Badges --}}
                                <div class='col'>
                                    <div class='card statistics-card drop-shadow card-default h-100'>
                                        <div class='card-body d-flex justify-content-center align-items-center'>
                                            <div class='text-center w-100'>
                                                <div class='d-flex justify-content-center align-items-center mb-2'>
                                                    <i class='fa-solid fa-award fa-2x Accent-200-cl'></i>
                                                    <h2 class='mb-0 ms-3'>{{ $badge_stats['total_local_badges'] }}</h2>
                                                </div>
                                                <p class='form-label text-center mb-0'>{{ trans('langTotalLocalBadges') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Total Badge Awards --}}
                                <div class='col'>
                                    <div class='card statistics-card drop-shadow card-default h-100'>
                                        <div class='card-body d-flex justify-content-center align-items-center'>
                                            <div class='text-center w-100'>
                                                <div class='d-flex justify-content-center align-items-center mb-2'>
                                                    <i class='fa-solid fa-star fa-2x text-success'></i>
                                                    <h2 class='mb-0 ms-3'>{{ $badge_stats['total_badge_awards'] }}</h2>
                                                </div>
                                                <p class='form-label text-center mb-0'>{{ trans('langTotalBadgeAwards') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Users with Badges --}}
                                <div class='col'>
                                    <div class='card statistics-card drop-shadow card-default h-100'>
                                        <div class='card-body d-flex justify-content-center align-items-center'>
                                            <div class='text-center w-100'>
                                                <div class='d-flex justify-content-center align-items-center mb-2'>
                                                    <i class='fa-solid fa-user-graduate fa-2x text-primary'></i>
                                                    <h2 class='mb-0 ms-3'>{{ $badge_stats['users_with_badges'] }}</h2>
                                                </div>
                                                <p class='form-label text-center mb-0'>{{ trans('langUsersWithBadges') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Active Providers --}}
                                <div class='col'>
                                    <div class='card statistics-card drop-shadow card-default h-100'>
                                        <div class='card-body d-flex justify-content-center align-items-center'>
                                            <div class='text-center w-100'>
                                                <div class='d-flex justify-content-center align-items-center mb-2'>
                                                    <i class='fa-solid fa-server fa-2x text-info'></i>
                                                    <h2 class='mb-0 ms-3'>{{ $badge_stats['active_providers'] }}</h2>
                                                </div>
                                                <p class='form-label text-center mb-0'>{{ trans('langActiveBackpackProviders') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- Additional Information --}}
                            <div class='row g-3 g-lg-4'>
                                
                                {{-- Most Exported Badge --}}
                                <div class='col-lg-3 col-md-6 col-12'>
                                    <div class='card border-card-left-success px-3 py-3 h-100'>
                                        <div class='card-body d-flex flex-column p-0 pt-2'>
                                            <div class='d-flex align-items-center mb-2 px-3'>
                                                <i class='fa-solid fa-trophy fa-lg text-warning me-2'></i>
                                                <h6 class='mb-0'>{{ trans('langMostExportedBadge') }}</h6>
                                            </div>
                                            <div class='flex-grow-1 d-flex align-items-center justify-content-center px-3'>
                                                @if($badge_stats['most_exported_badge_title'])
                                                    <div class='d-flex align-items-center gap-2 w-100'>
                                                        @if($badge_stats['most_exported_badge_icon'])
                                                            <img src='{{ $urlServer }}courses/user_progress_data/badge_templates/{{ $badge_stats['most_exported_badge_icon'] }}' 
                                                                 alt='{{ $badge_stats['most_exported_badge_title'] }}'
                                                                 style='width: 50px; height: 50px; object-fit: contain;'
                                                                 onerror="this.src='{{ $urlServer }}resources/img/game/badge.png'">
                                                        @else
                                                            <img src='{{ $urlServer }}resources/img/game/badge.png' 
                                                                 alt='badge'
                                                                 style='width: 50px; height: 50px; object-fit: contain;'>
                                                        @endif
                                                        <div class='flex-grow-1'>
                                                            <p class='mb-0 small fw-bold'>{{ $badge_stats['most_exported_badge_title'] }}</p>
                                                            <p class='mb-0 text-muted small'>{{ $badge_stats['most_exported_badge_count'] }} {{ trans('langExports') }}</p>
                                                        </div>
                                                    </div>
                                                @else
                                                    <p class='mb-0 text-muted small'>-</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Course with Most Exports --}}
                                <div class='col-lg-3 col-md-6 col-12'>
                                    <div class='card border-card-left-success px-3 py-3 h-100'>
                                        <div class='card-body d-flex flex-column p-0 pt-2'>
                                            <div class='d-flex align-items-center mb-2 px-3'>
                                                <i class='fa-solid fa-book-open fa-lg Accent-200-cl me-2'></i>
                                                <h6 class='mb-0'>{{ trans('langCourseMostExports') }}</h6>
                                            </div>
                                            <div class='flex-grow-1 d-flex align-items-center justify-content-center px-3'>
                                                @if($badge_stats['course_most_exports_title'])
                                                    <div class='text-center w-100'>
                                                        <a href='{{ $urlServer }}courses/{{ $badge_stats['course_most_exports_code'] }}/' 
                                                           class='text-decoration-none'>
                                                            <p class='mb-0 small fw-bold text-primary'>
                                                                {{ $badge_stats['course_most_exports_title'] }}
                                                            </p>
                                                            <p class='mb-0 text-muted small'>({{ $badge_stats['course_most_exports_code'] }})</p>
                                                        </a>
                                                        <p class='mb-0 text-muted small mt-1'>
                                                            {{ $badge_stats['course_most_exports_count'] }} {{ trans('langExports') }}
                                                        </p>
                                                    </div>
                                                @else
                                                    <p class='mb-0 text-muted small'>-</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Recent Sync Activity --}}
                                <div class='col-lg-3 col-md-6 col-12'>
                                    <div class='card border-card-left-info px-3 py-3 h-100'>
                                        <div class='card-body d-flex flex-column p-0 pt-2'>
                                            <div class='d-flex align-items-center mb-2 px-3'>
                                                <i class='fa-solid fa-sync-alt fa-lg text-info me-2'></i>
                                                <h6 class='mb-0'>{{ trans('langRecentSyncActivity') }}</h6>
                                            </div>
                                            <div class='flex-grow-1 d-flex align-items-center justify-content-center px-3'>
                                                <p class='mb-0 text-muted'>
                                                    {{ $badge_stats['recent_syncs'] }} {{ trans('langUsersLast30Days') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Last Activity --}}
                                <div class='col-lg-3 col-md-12 col-12'>
                                    <div class='card border-card-left-warning px-3 py-3 h-100'>
                                        <div class='card-body d-flex flex-column p-0 pt-2'>
                                            <div class='d-flex align-items-center mb-2 px-3'>
                                                <i class='fa-solid fa-clock fa-lg text-secondary me-2'></i>
                                                <h6 class='mb-0'>{{ trans('langLastActivity') }}</h6>
                                            </div>
                                            <div class='flex-grow-1 d-flex align-items-center justify-content-center px-3'>
                                                <div class='small w-100'>
                                                    <div class='mb-1'>
                                                        <strong>{{ trans('langLastImport') }}:</strong> 
                                                        <span class='text-muted'>{{ $badge_stats['last_import'] }}</span>
                                                    </div>
                                                    <div>
                                                        <strong>{{ trans('langLastExport') }}:</strong> 
                                                        <span class='text-muted'>{{ $badge_stats['last_export'] }}</span>
                                                    </div>
                                                </div>
                                            </div>
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
</main>
@endsection
