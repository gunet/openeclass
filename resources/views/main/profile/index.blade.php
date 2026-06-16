
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

            {{-- Top profile card: avatar + name + role + dept/date + buttons --}}
            <div class='col-12 mt-3'>
                <div class="card panelCard rounded-3 epf-panel-card px-4 profile-top-card" style="padding-top:1.25rem;padding-bottom:1.25rem;">
                    <div class="d-flex align-items-center gap-4 flex-wrap">

                        {{-- Avatar --}}
                        {!! $profile_img !!}

                        {{-- Name + role badge + dept/date row --}}
                        <div class="flex-grow-1">
                            <div class="fw-bold" style="font-size:1.15rem;">
                                {{ $userdata->surname }} {{ $userdata->givenname }}
                            </div>
                            <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                <span class="text-muted" style="font-size:0.9rem;">{{ '@' . $userdata->username }}</span>
                                @php
                                    if ($userdata->status == USER_TEACHER) { $dp_role = trans('langTeacher'); }
                                    elseif ($userdata->status == USER_STUDENT) { $dp_role = trans('langStudent'); }
                                    else { $dp_role = ''; }
                                @endphp
                                @if(!empty($dp_role))
                                    <span class="badge rounded-pill" style="background:#eff6ff;color:#2563eb;font-weight:500;font-size:0.8rem;">{{ $dp_role }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Action buttons --}}
                        <div class="d-flex gap-2 flex-wrap flex-shrink-0 align-items-center">
                            @if(get_config('eportfolio_enable'))
                                @if($uid == $id)
                                    <a class="btn btn-primary d-inline-flex align-items-center gap-2"
                                       href="{{ $urlAppend }}main/eportfolio/index.php">
                                        <i class="fa-solid fa-table-columns"></i>{{ trans('langMyePortfolio') }}
                                    </a>
                                @elseif($userdata->eportfolio_enable)
                                    <a class="btn btn-primary d-inline-flex align-items-center gap-2"
                                       href="{{ $urlAppend }}main/eportfolio/index.php?token={{ $userdata->eportfolio_token }}">
                                        <i class="fa-solid fa-table-columns"></i>{{ trans('langMyePortfolio') }}
                                    </a>
                                @endif
                            @endif
                            @if(get_config('personal_blog'))
                                <a class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"
                                   href="{{ $urlAppend }}modules/blog/index.php?user_id={{ $id }}&token={{ token_generate('personal_blog' . $id) }}">
                                    <i class="fa-regular fa-user"></i>{{ trans('langUserBlog') }}
                                </a>
                            @endif
                            @if($uid == $id)
                                <a class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"
                                   href="{{ $urlAppend }}main/profile/profile.php?edProfile=true">
                                    <i class="fa-solid fa-pen-to-square"></i>{{ trans('langModProfile') }}
                                </a>
                            @elseif(get_config('dropbox_allow_personal_messages'))
                                <a class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"
                                   href="{{ $urlAppend }}modules/message/index.php?upload=1&amp;id={{ $id }}">
                                    <i class="fa-solid fa-envelope"></i>{{ trans('langProfileSendMail') }}
                                </a>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            {{-- Personal Info + Academic Info (left) | About Me + Awards (right) --}}
            @php
                $deptIds     = $user->getDepartmentIds($id);
                $hasCerts    = count($cert_completed) > 0;
                $hasBadges   = count($badge_completed) > 0;
                $hasExternal = isset($openBadgesEnabled) && $openBadgesEnabled && isset($badge_external) && count($badge_external) > 0;
            @endphp
            <div class="col-12 mt-4">
                <div class="row g-3">

                    {{-- Left column: Personal Info stacked above Academic Info --}}
                    <div class="col-md-4 d-flex flex-column gap-3">

                        {{-- Personal Info card --}}
                        <div class="card panelCard rounded-3 epf-panel-card px-4 py-3">
                            <h2 class="text-heading-h3 mb-3">{{ trans('langPersInfo') }}</h2>
                            <div class="d-flex flex-column gap-3">
                                @if(!empty($userdata->email) and allow_access($userdata->email_public))
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="epf-cat-icon flex-shrink-0" style="background:#f97316;">
                                            <i class="fa-solid fa-envelope"></i>
                                        </div>
                                        <span class="text-muted" style="font-size:0.9rem;">{!! mailto($userdata->email) !!}</span>
                                    </div>
                                @endif
                                @if(!empty($userdata->phone) and allow_access($userdata->phone_public))
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="epf-cat-icon flex-shrink-0" style="background:#22c55e;">
                                            <i class="fa-solid fa-phone"></i>
                                        </div>
                                        <span class="text-muted" style="font-size:0.9rem;">{{ $userdata->phone }}</span>
                                    </div>
                                @endif
                                <div class="d-flex align-items-center gap-3">
                                    <div class="epf-cat-icon flex-shrink-0" style="background:#8b5cf6;">
                                        <i class="fa-solid fa-lock"></i>
                                    </div>
                                    <span class="text-muted" style="font-size:0.9rem;">{{ $privilege_message }}</span>
                                </div>
                                @if(!empty($userdata->am) and allow_access($userdata->am_public))
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="epf-cat-icon flex-shrink-0" style="background:#06b6d4;">
                                            <i class="fa-solid fa-id-card"></i>
                                        </div>
                                        <span class="text-muted" style="font-size:0.9rem;">{{ $userdata->am }}</span>
                                    </div>
                                @endif
                                @if($id == $uid && !empty($extAuthList))
                                    @foreach($extAuthList as $item)
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="epf-cat-icon flex-shrink-0" style="background:#6366f1;">
                                                <i class="fa-solid fa-link"></i>
                                            </div>
                                            <span class="text-muted" style="font-size:0.9rem;">
                                                <img src="{{ $themeimg }}/{{ $item->auth_name }}.png" alt="">
                                                {{ $authFullName[$item->auth_id] }}
                                            </span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        {{-- Academic Info card --}}
                        @if(!empty($deptIds))
                            <div class="card panelCard rounded-3 epf-panel-card px-4 py-3">
                                <h2 class="text-heading-h3 mb-3">{{ trans('langFaculty') }}</h2>
                                <div class="d-flex flex-column gap-3">
                                    @foreach($deptIds as $dep)
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="epf-cat-icon flex-shrink-0" style="background:#3b82f6;">
                                                <i class="fa-solid fa-building-columns"></i>
                                            </div>
                                            <span class="text-muted" style="font-size:0.9rem;">{!! $tree->getFullPath($dep) !!}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>

                    {{-- Right column: About Me + Awards stacked --}}
                    <div class="col-md-8 d-flex flex-column gap-3">

                        {{-- About Me card --}}
                        <div class="card panelCard rounded-3 epf-panel-card px-4 py-3">
                            <h2 class="text-heading-h3 mb-3">{{ trans('langAboutMe') }}</h2>
                            @if(!empty($userdata->description) && ($userdata->pic_public || $_SESSION['status'] == USER_TEACHER || $uid == $id))
                                <p class="text-muted mb-0" style="font-size:0.9rem;">{!! standard_text_escape($userdata->description) !!}</p>
                            @else
                                <p class="text-muted mb-0" style="font-size:0.9rem;">-</p>
                            @endif
                        </div>

                        {{-- Certificates card --}}
                        @if($hasCerts)
                            <div class="card panelCard epf-panel-card rounded-3 px-4 py-3">
                                <h2 class="text-heading-h3 mb-3">{{ trans('langMyCertificates') }}</h2>
                                <div class="row row-cols-1 row-cols-md-2 g-3">
                                    @foreach($cert_completed as $certificate)
                                        <div class="col">
                                            <a href="../out.php?i={{ $certificate->identifier }}"
                                               class="card h-100 border p-3 d-flex flex-row align-items-center gap-3 text-decoration-none epf-award-item">
                                                <img style="height:65px;width:65px;object-fit:contain;flex-shrink:0;"
                                                     src="{{ $urlServer }}resources/img/game/badge.png"
                                                     alt="{{ trans('langCertificate') }}">
                                                <div class="text-start">
                                                    <div class="fw-semibold text-dark mb-1">{!! $certificate->cert_title !!}</div>
                                                    <div class="text-muted small">{!! $certificate->cert_issuer !!}</div>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Badges card --}}
                        @if($hasBadges)
                            <div class="card panelCard epf-panel-card rounded-3 px-4 py-3">
                                <h2 class="text-heading-h3 mb-3">{{ trans('langBadges') }}</h2>
                                <div class="row row-cols-1 row-cols-md-2 g-3">
                                    @foreach($badge_completed as $badge)
                                        <div class="col">
                                            <div class="card h-100 border p-3 epf-award-item badge-card-wrapper">
                                                <a href="{{ $urlAppend }}modules/progress/index.php?course={{ course_id_to_code($badge->course_id) }}&amp;badge_id={{ $badge->badge }}&amp;u={{ $badge->user }}"
                                                   class="d-flex align-items-center gap-3 text-decoration-none flex-grow-1">
                                                    <img style="height:65px;width:65px;object-fit:contain;flex-shrink:0;"
                                                         src="{{ $urlServer . BADGE_TEMPLATE_PATH . get_badge_filename($badge->badge) }}"
                                                         alt="{{ trans('langBadge') }}">
                                                    <div class="text-start">
                                                        <div class="fw-semibold text-dark mb-1">{{ ellipsize($badge->title, 40) }}</div>
                                                        <div class="text-muted small">{!! $badge->issuer !!}</div>
                                                    </div>
                                                </a>
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

                        {{-- External badges card --}}
                        @if($hasExternal)
                            <div class="card panelCard epf-panel-card rounded-3 px-4 py-3">
                                <h2 class="text-heading-h3 mb-3">{{ trans('langExternalBadges') }}</h2>
                                <div class="row row-cols-1 row-cols-md-2 g-3">
                                    @foreach($badge_external as $badge)
                                        <div class="col">
                                            @if(get_config('eportfolio_enable') && ($id == $uid))
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
                                            <a href="{{ !empty($badge->external_assertion_id) ? $badge->external_assertion_id : '#' }}"
                                               target="_blank" rel="noopener"
                                               class="card h-100 border p-3 d-flex flex-row align-items-center gap-3 text-decoration-none epf-award-item">
                                                @if(!empty($badge->image_url))
                                                    <img style="height:65px;width:65px;object-fit:contain;flex-shrink:0;"
                                                         src="{{ $badge->image_url }}"
                                                         alt="{{ trans('langExternalBadge') }}"
                                                         onerror="this.src='{{ $urlServer }}resources/img/game/badge.png'">
                                                @else
                                                    <img style="height:65px;width:65px;object-fit:contain;flex-shrink:0;"
                                                         src="{{ $urlServer }}resources/img/game/badge.png"
                                                         alt="{{ trans('langExternalBadge') }}">
                                                @endif
                                                <div class="text-start">
                                                    <div class="fw-semibold text-dark mb-1">{{ ellipsize($badge->title, 40) }}</div>
                                                    <div class="text-muted small">{{ $badge->issuer ?? trans('langUnknownIssuer') }}</div>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>{{-- /col-md-8 --}}

                </div>
            </div>

            {{-- Teacher availability calendar --}}
            @if($is_user_teacher && get_config('individual_group_bookings'))
                <div class="col-12 mt-4">
                    <div class="card panelCard rounded-3 epf-panel-card px-4 py-3">
                        <div class="control-label-notes mb-3">{{ trans('langAvailableDateForUser') }}</div>
                        <div id="smallCalendar{{ $id }}" class="calendarViewDatesTutorGroup"></div>
                        @if(isset($_GET['id']) && isset($_GET['token']) && $is_simple_user)
                            <a class="btn submitAdminBtnDefault w-100 m-auto mt-3"
                               href="{{ $urlAppend }}main/profile/add_available_dates.php?uBook={{ $id }}&bookWith=1&do_booking=1&token={{ $_GET['token'] }}">
                                {{ trans('langDoBooking') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            @if ($userdata->status != USER_GUEST)
                {!! render_profile_fields_content(array('user_id' => $id)) !!}
            @endif

        </div>
    </div>
</main>

@if ($uid == $id && !isset($_GET['id']) && !isset($_GET['token']))
    @include('modules.backpack.templates.publish_badge_modal')
@endif

@endsection
