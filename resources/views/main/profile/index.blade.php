
@extends('layouts.default')

@push('head_scripts')
    @if($is_user_teacher)
        <script type='text/javascript'>
            $(document).ready(function () {
                var calendar = $('#smallCalendar{{ $id }}').fullCalendar({
                    events : "{{ $urlAppend }}main/profile/display_profile.php?view=true&show_tutor={{ $id }}",
                    defaultView: 'listMonth',
                    eventColor : '#687DA3',
                    eventTextColor : 'white',
                    selectable : false,
                    locale: '{{ $language }}',
                    height   : 400,
                    editable : false,
                    header:{
                        left: 'prev,next ',
                        center: 'title',
                        right: ''
                    },
                    allDaySlot : false,
                    displayEventTime: true,
                    eventRender: function( event, element, view ) {
                        var title = element.find( '.fc-list-item-title' );
                        title.html( title.text() );
                    },
                    theme: 'standard'
                });
            });
        </script>
    @endif

    <!-- Badge Publication Module -->
    @if ($uid == $id && !isset($_GET['id']) && !isset($_GET['token']))
        <link rel="stylesheet" href="{{ $urlAppend }}modules/backpack/css/badge-publication.css">
        <script src="{{ $urlAppend }}modules/backpack/js/badge-publication.js"></script>
        <script>
            const BADGE_SELECT_PROVIDER = "{{ trans('langSelectProvider') }}";
            const BADGE_NO_PROVIDERS_CONNECTED = "{{ trans('langNoProvidersConnected') }}";
            const BADGE_PUBLISH_SUCCESS = "{{ trans('langBadgePublishedSuccessfully') }}";
            const BADGE_PUBLISH_ERROR = "{{ trans('langBadgePublishError') }}";
            const BADGE_SELECT_PROVIDER_ALERT = "{{ trans('langSelectProviderAlert') }}";
            const BADGE_PUBLISHING = "{{ trans('langPublishing') }}...";
            const BADGE_PUBLISH = "{{ trans('langPublish') }}";
            const BADGE_PUBLISHED_TO_BACKPACK = "{{ trans('langPublishedToBackpack') }}";
        </script>
    @endif

    @if ($uid == $id)
        <script>
            $(document).on('click', 'a.list-group-item[href*="resources.php"]', function(e) {
                e.preventDefault();
                const href = $(this).attr('href');
                const url = new URL(href, window.location.origin);
                const rid = url.searchParams.get('rid');
                const modalId = `modal_blog_${rid}`;
                const modalElement = document.getElementById(modalId);
                if (modalElement) {
                    const Modal = new bootstrap.Modal(modalElement);
                    Modal.show();
                    const formSelector = `#vis_form_blog_${rid}`;
                    $(formSelector).attr('action', href);
                } else {
                    console.warn('Modal with ID', modalId, 'not found');
                }
            });
        </script>
    @endif
@endpush

@section('content')

<main id="main" class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">
            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
            @include('layouts.partials.legend_view')
            @if(isset($action_bar) and $action_bar)
                {!! $action_bar !!}
            @else
                <div class='mt-4'></div>
            @endif
            @include('layouts.partials.show_alert')

            {{-- Single profile card --}}
            <div class='col-12 mt-3'>
                <div class="card panelCard rounded-3 epf-panel-card">
                    <div class="px-5 py-4">

                        {{-- Header: avatar + name + buttons --}}
                        <div class="d-flex align-items-center gap-4 flex-wrap">
                            {{-- Avatar (smaller) --}}
                            <div class="flex-shrink-0" style="width:90px;height:90px;border-radius:50%;overflow:hidden;">
                                {!! $profile_img !!}
                            </div>
                            {{-- Name + username --}}
                            <div class="flex-grow-1">
                                <div class="fw-bold" style="font-size:1.15rem;">
                                    {{ $userdata->surname }} {{ $userdata->givenname }}
                                </div>
                                <div class="text-muted" style="font-size:0.9rem;">{{ $userdata->username }}</div>
                            </div>
                            {{-- Action buttons (larger) --}}
                            <div class="d-flex gap-2 flex-wrap flex-shrink-0">
                                @if(get_config('eportfolio_enable'))
                                    @if($uid == $id)
                                        <a class="btn btn-primary btn-lg d-inline-flex align-items-center gap-2"
                                           href="{{ $urlAppend }}main/eportfolio/index.php">
                                            <i class="fa-solid fa-table-columns"></i>
                                            {{ trans('langMyePortfolio') }}
                                        </a>
                                    @elseif($userdata->eportfolio_enable)
                                        <a class="btn btn-primary btn-lg d-inline-flex align-items-center gap-2"
                                           href="{{ $urlAppend }}main/eportfolio/index.php?token={{ $userdata->eportfolio_token }}">
                                            <i class="fa-solid fa-table-columns"></i>
                                            {{ trans('langMyePortfolio') }}
                                        </a>
                                    @endif
                                @endif
                                @if(get_config('personal_blog'))
                                    <a class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center gap-2"
                                       href="{{ $urlAppend }}modules/blog/index.php?user_id={{ $id }}&token={{ token_generate('personal_blog' . $id) }}">
                                        <i class="fa-regular fa-user"></i>
                                        {{ trans('langUserBlog') }}
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Section header --}}
                        <div class="d-flex align-items-center gap-4 mb-3 mt-5">
                            <div class="epf-cat-icon flex-shrink-0" style="background:#6366f1;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <h2 class="text-heading-h3 mb-0">{{ trans('langPersInfo') }}</h2>
                        </div>

                    {{-- Fields grid — each col-md-6 item fills 2 columns naturally --}}
                    <div class="row g-0">

                        {{-- Δικαιώματα (always shown) --}}
                        <div class="col-md-6 py-3 pe-md-4 border-bottom">
                            <div class="d-flex align-items-center gap-4">
                                <div class="epf-cat-icon flex-shrink-0" style="background:#3b82f6;">
                                    <i class="fa-solid fa-lock"></i>
                                </div>
                                <span class="flex-shrink-0 fw-semibold" style="min-width:130px;font-size:0.9rem;">{{ trans('langUserPermissions') }}</span>
                                <span class="text-muted">{{ $privilege_message }}</span>
                            </div>
                        </div>

                        {{-- Αριθμός μητρώου --}}
                        @if(!empty($userdata->am) and allow_access($userdata->am_public))
                            <div class="col-md-6 py-3 ps-md-4 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="epf-cat-icon flex-shrink-0" style="background:#10b981;">
                                        <i class="fa-solid fa-id-card"></i>
                                    </div>
                                    <span class="flex-shrink-0 fw-semibold" style="min-width:130px;font-size:0.9rem;">{{ trans('langAm') }}</span>
                                    <span class="text-muted">{{ $userdata->am }}</span>
                                </div>
                            </div>
                        @else
                            <div class="col-md-6 py-3 border-bottom"></div>
                        @endif

                        {{-- Email --}}
                        @if(!empty($userdata->email) and allow_access($userdata->email_public))
                            <div class="col-md-6 py-3 pe-md-4 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="epf-cat-icon flex-shrink-0" style="background:#f97316;">
                                        <i class="fa-solid fa-envelope"></i>
                                    </div>
                                    <span class="flex-shrink-0 fw-semibold" style="min-width:130px;font-size:0.9rem;">{{ trans('langEmail') }}</span>
                                    <span class="text-muted">{!! mailto($userdata->email) !!}</span>
                                </div>
                            </div>
                        @else
                            <div class="col-md-6 py-3 border-bottom"></div>
                        @endif

                        {{-- Κατηγορία / Department --}}
                        <div class="col-md-6 py-3 ps-md-4 border-bottom">
                            <div class="d-flex align-items-center gap-4">
                                <div class="epf-cat-icon flex-shrink-0" style="background:#06b6d4;">
                                    <i class="fa-solid fa-building-columns"></i>
                                </div>
                                <span class="flex-shrink-0 fw-semibold" style="min-width:130px;font-size:0.9rem;">{{ trans('langFaculty') }}</span>
                                <span class="text-muted">
                                    @foreach($user->getDepartmentIds($id) as $i => $dep)
                                        {!! $tree->getFullPath($dep) !!}
                                        @if($i + 1 < count($user->getDepartmentIds($id)))<br/>@endif
                                    @endforeach
                                </span>
                            </div>
                        </div>

                        {{-- Τηλέφωνο --}}
                        @if(!empty($userdata->phone) and allow_access($userdata->phone_public))
                            <div class="col-md-6 py-3 pe-md-4 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="epf-cat-icon flex-shrink-0" style="background:#22c55e;">
                                        <i class="fa-solid fa-phone"></i>
                                    </div>
                                    <span class="flex-shrink-0 fw-semibold" style="min-width:130px;font-size:0.9rem;">{{ trans('langPhone') }}</span>
                                    <span class="text-muted">{{ $userdata->phone }}</span>
                                </div>
                            </div>
                        @else
                            <div class="col-md-6 py-3 border-bottom"></div>
                        @endif

                        {{-- Μέλος από (always shown) --}}
                        <div class="col-md-6 py-3 ps-md-4 border-bottom">
                            <div class="d-flex align-items-center gap-4">
                                <div class="epf-cat-icon flex-shrink-0" style="background:#ec4899;">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </div>
                                <span class="flex-shrink-0 fw-semibold" style="min-width:130px;font-size:0.9rem;">{{ trans('langProfileMemberSince') }}</span>
                                <span class="text-muted">{{ format_locale_date(strtotime($userdata->registered_at)) }}</span>
                            </div>
                        </div>

                        {{-- Σχετικά με εμένα (About Me) --}}
                        @if(!empty($userdata->description) && ($userdata->pic_public || $_SESSION['status'] == USER_TEACHER || $uid == $id))
                            <div class="col-12 py-3">
                                <div class="d-flex align-items-start gap-4">
                                    <div class="epf-cat-icon flex-shrink-0" style="background:#8b5cf6;">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </div>
                                    <span class="flex-shrink-0 fw-semibold" style="min-width:130px;font-size:0.9rem;">{{ trans('langAboutMe') }}</span>
                                    <span class="text-muted">{!! $userdata->description !!}</span>
                                </div>
                            </div>
                        @endif

                        {{-- External auth providers --}}
                        @if($id == $uid && !empty($extAuthList))
                            @foreach($extAuthList as $item)
                                <div class="col-md-6 py-2 pe-md-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="epf-cat-icon flex-shrink-0" style="background:#6366f1;">
                                            <i class="fa-solid fa-link"></i>
                                        </div>
                                        <span class="flex-shrink-0 fw-semibold" style="min-width:130px;font-size:0.9rem;">{{ trans('langProviderConnectWith') }}</span>
                                        <span class="text-muted">
                                            <img src="{{ $themeimg }}/{{ $item->auth_name }}.png" alt="">
                                            {{ $authFullName[$item->auth_id] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                    </div>

                    {{-- Teacher availability calendar --}}
                    @if($is_user_teacher && get_config('individual_group_bookings'))
                        <div class="mt-4 pt-3 border-top">
                            <div class="control-label-notes mb-3">{{ trans('langAvailableDateForUser') }}</div>
                            <div id="smallCalendar{{ $id }}" class="calendarViewDatesTutorGroup"></div>
                            @if(isset($_GET['id']) && isset($_GET['token']) && $is_simple_user)
                                <a class="btn submitAdminBtnDefault w-100 m-auto mt-3"
                                   href="{{ $urlAppend }}main/profile/add_available_dates.php?uBook={{ $id }}&bookWith=1&do_booking=1&token={{ $_GET['token'] }}">
                                    {{ trans('langDoBooking') }}
                                </a>
                            @endif
                        </div>
                    @endif

                    </div>{{-- /px-5 py-4 --}}
                </div>{{-- /card --}}
            </div>{{-- /col-12 --}}

            @if ($userdata->status != USER_GUEST)
                {!! render_profile_fields_content(array('user_id' => $id)) !!}
            @endif

            @if(!isset($_GET['id']) and !isset($_GET['token']))
                @php
                    $hasCerts    = count($cert_completed) > 0;
                    $hasBadges   = count($badge_completed) > 0;
                    $hasExternal = isset($openBadgesEnabled) && $openBadgesEnabled && isset($badge_external) && count($badge_external) > 0;
                    $firstTab    = $hasCerts ? 'certs' : ($hasBadges ? 'badges' : 'external');
                @endphp
                @if($hasCerts or $hasBadges or $hasExternal)
                    <div class="col-12 mt-4">
                        <div class="card panelCard epf-panel-card rounded-3">
                            <div class="px-5 pt-3 pb-4">

                                {{-- Tab navigation --}}
                                <ul class="nav gap-4 border-0 mb-0" id="awardsTab" role="tablist">
                                    @if($hasCerts)
                                        <li class="nav-item" role="presentation">
                                            <button class="border-0 bg-transparent p-0 text-heading-h3 d-flex align-items-center gap-3"
                                                    style="{{ $firstTab !== 'certs' ? 'opacity:0.4;' : '' }}"
                                                    id="certs-tab" data-bs-toggle="tab"
                                                    data-bs-target="#certs-tab-pane"
                                                    type="button" role="tab">
                                                <div class="epf-cat-icon flex-shrink-0" style="background:#f59e0b;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="18" height="18">
                                                        <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                                    </svg>
                                                </div>
                                                {{ trans('langMyCertificates') }}
                                            </button>
                                        </li>
                                    @endif
                                    @if($hasBadges)
                                        <li class="nav-item" role="presentation">
                                            <button class="border-0 bg-transparent p-0 text-heading-h3 d-flex align-items-center gap-3"
                                                    style="{{ $firstTab !== 'badges' ? 'opacity:0.4;' : '' }}"
                                                    id="badges-tab" data-bs-toggle="tab"
                                                    data-bs-target="#badges-tab-pane"
                                                    type="button" role="tab">
                                                <div class="epf-cat-icon flex-shrink-0" style="background:#8b5cf6;">
                                                    <i class="fa-solid fa-award"></i>
                                                </div>
                                                {{ trans('langBadges') }}
                                            </button>
                                        </li>
                                    @endif
                                    @if($hasExternal)
                                        <li class="nav-item" role="presentation">
                                            <button class="border-0 bg-transparent p-0 text-heading-h3 d-flex align-items-center gap-3"
                                                    style="{{ $firstTab !== 'external' ? 'opacity:0.4;' : '' }}"
                                                    id="external-tab" data-bs-toggle="tab"
                                                    data-bs-target="#external-tab-pane"
                                                    type="button" role="tab">
                                                <div class="epf-cat-icon flex-shrink-0" style="background:#3b82f6;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="18" height="18">
                                                        <path d="M12 2.25a.75.75 0 01.75.75v11.69l3.22-3.22a.75.75 0 111.06 1.06l-4.5 4.5a.75.75 0 01-1.06 0l-4.5-4.5a.75.75 0 111.06-1.06l3.22 3.22V3a.75.75 0 01.75-.75zm-9 13.5a.75.75 0 01.75.75v2.25a1.5 1.5 0 001.5 1.5h13.5a1.5 1.5 0 001.5-1.5V16.5a.75.75 0 011.5 0v2.25a3 3 0 01-3 3H5.25a3 3 0 01-3-3V16.5a.75.75 0 01.75-.75z"/>
                                                    </svg>
                                                </div>
                                                {{ trans('langExternalBadges') }}
                                            </button>
                                        </li>
                                    @endif
                                </ul>

                                {{-- Tab content --}}
                                <div class="tab-content pt-4" id="awardsTabContent">

                                    {{-- Certificates --}}
                                    @if($hasCerts)
                                        <div class="tab-pane fade {{ $firstTab === 'certs' ? 'show active' : '' }}"
                                             id="certs-tab-pane" role="tabpanel">
                                            <div class="row row-cols-1 row-cols-md-3 g-4">
                                                @foreach($cert_completed as $certificate)
                                                    <div class="col">
                                                        <a href="../out.php?i={{ $certificate->identifier }}"
                                                           class="card h-100 border shadow-sm p-3 d-flex flex-row align-items-center gap-3 text-decoration-none">
                                                            <img style="height:65px;width:65px;object-fit:contain;flex-shrink:0;"
                                                                 src="{{ $urlServer }}resources/img/game/badge.png"
                                                                 alt="certificate">
                                                            <div class="text-start">
                                                                <div class="fw-semibold text-dark mb-1">{!! $certificate->cert_title !!}</div>
                                                                <div class="text-success small mb-1">
                                                                    {!! format_locale_date(strtotime($certificate->assigned), null, false) !!}
                                                                </div>
                                                                <div class="text-muted small">{!! $certificate->cert_issuer !!}</div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Badges --}}
                                    @if($hasBadges)
                                        <div class="tab-pane fade {{ $firstTab === 'badges' ? 'show active' : '' }}"
                                             id="badges-tab-pane" role="tabpanel">
                                            <div class="row row-cols-1 row-cols-md-3 g-4">
                                                @foreach($badge_completed as $badge)
                                                    <div class="col">
                                                        <div class="card h-100 border shadow-sm badge-card-wrapper p-3">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <img style="height:65px;width:65px;object-fit:contain;flex-shrink:0;"
                                                                     src="{{ $urlServer . BADGE_TEMPLATE_PATH . get_badge_filename($badge->badge) }}"
                                                                     alt="badge">
                                                                <div class="text-start flex-grow-1">
                                                                    <a href="../../modules/progress/index.php?course={{ course_id_to_code($badge->course_id) }}&amp;badge_id={{ $badge->badge }}&amp;u={{ $badge->user }}"
                                                                       class="text-decoration-none">
                                                                        <div class="fw-semibold text-dark mb-1">{{ ellipsize($badge->title, 40) }}</div>
                                                                        <div class="text-success small mb-1">
                                                                            {!! format_locale_date(strtotime($badge->assigned), null, false) !!}
                                                                        </div>
                                                                        <div class="text-muted small">{!! $badge->issuer !!}</div>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            @if($uid == $id && isset($openBadgesEnabled) && $openBadgesEnabled)
                                                                <div class="badge-card-footer d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2 pt-2 border-top">
                                                                    @if(!empty($badge->external_assertion_id))
                                                                        <div class="badge-published-status d-flex align-items-center gap-2 mb-0">
                                                                            <i class="fa fa-check-circle text-success"></i>
                                                                            <span class="text-success small">{{ trans('langPublishedToBackpack') }}</span>
                                                                        </div>
                                                                    @elseif(isset($badge->allow_export) && $badge->allow_export == 0)
                                                                        <div class="badge-card-actions d-flex align-items-center gap-2 flex-wrap">
                                                                            <button class="badge-publish-btn disabled" disabled
                                                                                    data-bs-toggle="tooltip" data-bs-placement="left"
                                                                                    title="{{ trans('langBadgeExportDisabled') }}"
                                                                                    style="opacity:0.5;cursor:not-allowed;">
                                                                                <i class="fa fa-cloud-upload"></i>
                                                                            </button>
                                                                            <span class="text-muted small">{{ trans('langBadgeExportDisabledShort') }}</span>
                                                                        </div>
                                                                    @else
                                                                        <span class="text-muted small">{{ trans('langPublishToBackpack') }}</span>
                                                                        <div class="badge-card-actions d-flex align-items-center gap-2 flex-wrap">
                                                                            <button class="badge-publish-btn"
                                                                                    data-user-badge-id="{{ $badge->user_badge_id }}"
                                                                                    data-bs-toggle="tooltip" data-bs-placement="left"
                                                                                    title="{{ trans('langPublishBadgeTooltip') }}"
                                                                                    aria-label="{{ trans('langPublishBadgeAriaLabel') }}">
                                                                                <i class="fa fa-cloud-upload"></i>
                                                                            </button>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- External badges --}}
                                    @if($hasExternal)
                                        <div class="tab-pane fade {{ $firstTab === 'external' ? 'show active' : '' }}"
                                             id="external-tab-pane" role="tabpanel">
                                            <div class="row row-cols-1 row-cols-md-3 g-4">
                                                @foreach($badge_external as $badge)
                                                    <div class="col">
                                                        @if(get_config('eportfolio_enable') && ($id == $uid))
                                                            <div class="text-end mb-1">
                                                                {!! action_button(array(
                                                                    array(
                                                                        'title' => trans('langAddResePortfolio'),
                                                                        'url' => "$urlServer"."main/eportfolio/resources.php?action=add&amp;type=external_badges&amp;rid=".$badge->user_badge_id,
                                                                        'icon' => 'fa-star'
                                                                    ),
                                                                )) !!}
                                                            </div>
                                                            <div class="modal fade" id="modal_blog_{{$badge->user_badge_id}}" tabindex="-1" aria-labelledby="blogModalLabel_{{$badge->user_badge_id}}" aria-hidden="true">
                                                                <div class="modal-dialog">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="blogModalLabel_{{$badge->user_badge_id}}">{{trans('langAddResePortfolio')}} - {{ellipsize($badge->title, 40)}}</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <form id="vis_form_blog_{{$badge->user_badge_id}}" name="vis_form_blog_{{$badge->user_badge_id}}" action="" method="post">
                                                                                <div class="mb-3">
                                                                                    <label for="vis_form_blog_{{$badge->user_badge_id}}_select" class="form-label">{{trans('langePortfolioFieldsVisibilitySettings')}}</label>
                                                                                    <select class="form-select" name="visibility" id="vis_form_blog_{{$badge->user_badge_id}}_select">
                                                                                        <option value="{{EPF_VISIBLE_PUBLIC}}">{{trans('langPublicePortfolioField')}}</option>
                                                                                        <option value="{{EPF_VISIBLE_USERS}}">{{trans('langOpenToRegisteredUsers')}}</option>
                                                                                        <option value="{{EPF_VISIBLE_PRIVATE}}">{{trans('langProfileInfoPrivate')}}</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="mb-3">
                                                                                    <label for="vis_form_blog_{{$badge->user_badge_id}}_textarea" class="form-label">{{trans('langePortfolioPromptAddReflComments')}}</label>
                                                                                    <textarea class="form-control" name="reflection_comments" id="vis_form_blog_{{$badge->user_badge_id}}_textarea"></textarea>
                                                                                </div>
                                                                                <button type="submit" class="btn btn-primary">{{trans('langSubmit')}}</button>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        <div class="card h-100 border shadow-sm badge-card-wrapper external-badge p-3">
                                                            <div class="d-flex align-items-center gap-3">
                                                                @if(!empty($badge->image_url))
                                                                    <img style="height:65px;width:65px;object-fit:contain;flex-shrink:0;"
                                                                         src="{{ $badge->image_url }}"
                                                                         alt="external badge"
                                                                         onerror="this.src='{{ $urlServer }}resources/img/game/badge.png'">
                                                                @else
                                                                    <img style="height:65px;width:65px;object-fit:contain;flex-shrink:0;"
                                                                         src="{{ $urlServer }}resources/img/game/badge.png"
                                                                         alt="external badge">
                                                                @endif
                                                                <div class="text-start">
                                                                    <div class="fw-semibold text-dark mb-1">{{ ellipsize($badge->title, 40) }}</div>
                                                                    @if(!empty($badge->created))
                                                                        <div class="text-success small mb-1">
                                                                            {!! format_locale_date(strtotime($badge->created), null, false) !!}
                                                                        </div>
                                                                    @endif
                                                                    <div class="text-muted small">{{ $badge->issuer ?? trans('langUnknownIssuer') }}</div>
                                                                    @if(!empty($badge->description))
                                                                        <div class="text-muted small mt-1">{{ ellipsize($badge->description, 100) }}</div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                </div>{{-- /tab-content --}}
                            </div>{{-- /px-4 --}}
                        </div>{{-- /card --}}
                    </div>{{-- /col-12 --}}
                @endif
            @endif
        </div>
    </div>
</main>

@if ($uid == $id && !isset($_GET['id']) && !isset($_GET['token']))
    @include('modules.backpack.templates.publish_badge_modal')
@endif

@endsection
