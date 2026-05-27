
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
                        <div class="mb-3 mt-4">
                            <h2 class="text-heading-h3 mb-0">{{ trans('langPersInfo') }}</h2>
                        </div>

                    {{-- Fields grid — each col-md-6 item fills 2 columns naturally --}}
                    <div class="row g-0">

                        {{-- Δικαιώματα (always shown) --}}
                        <div class="col-md-6 py-2 pe-md-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="epf-cat-icon flex-shrink-0" style="background:#3b82f6;">
                                    <i class="fa-solid fa-lock"></i>
                                </div>
                                <span class="flex-shrink-0 fw-semibold" style="min-width:130px;font-size:0.9rem;">{{ trans('langUserPermissions') }}</span>
                                <span class="text-muted">{{ $privilege_message }}</span>
                            </div>
                        </div>

                        {{-- Αριθμός μητρώου --}}
                        @if(!empty($userdata->am) and allow_access($userdata->am_public))
                            <div class="col-md-6 py-2 ps-md-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="epf-cat-icon flex-shrink-0" style="background:#10b981;">
                                        <i class="fa-solid fa-id-card"></i>
                                    </div>
                                    <span class="flex-shrink-0 fw-semibold" style="min-width:130px;font-size:0.9rem;">{{ trans('langAm') }}</span>
                                    <span class="text-muted">{{ $userdata->am }}</span>
                                </div>
                            </div>
                        @else
                            <div class="col-md-6 py-2"></div>
                        @endif

                        {{-- Email --}}
                        @if(!empty($userdata->email) and allow_access($userdata->email_public))
                            <div class="col-md-6 py-2 pe-md-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="epf-cat-icon flex-shrink-0" style="background:#f97316;">
                                        <i class="fa-solid fa-envelope"></i>
                                    </div>
                                    <span class="flex-shrink-0 fw-semibold" style="min-width:130px;font-size:0.9rem;">{{ trans('langEmail') }}</span>
                                    <span class="text-muted">{!! mailto($userdata->email) !!}</span>
                                </div>
                            </div>
                        @else
                            <div class="col-md-6 py-2"></div>
                        @endif

                        {{-- Κατηγορία / Department --}}
                        <div class="col-md-6 py-2 ps-md-4">
                            <div class="d-flex align-items-center gap-3">
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
                            <div class="col-md-6 py-2 pe-md-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="epf-cat-icon flex-shrink-0" style="background:#22c55e;">
                                        <i class="fa-solid fa-phone"></i>
                                    </div>
                                    <span class="flex-shrink-0 fw-semibold" style="min-width:130px;font-size:0.9rem;">{{ trans('langPhone') }}</span>
                                    <span class="text-muted">{{ $userdata->phone }}</span>
                                </div>
                            </div>
                        @else
                            <div class="col-md-6 py-2"></div>
                        @endif

                        {{-- Μέλος από (always shown) --}}
                        <div class="col-md-6 py-2 ps-md-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="epf-cat-icon flex-shrink-0" style="background:#ec4899;">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </div>
                                <span class="flex-shrink-0 fw-semibold" style="min-width:130px;font-size:0.9rem;">{{ trans('langProfileMemberSince') }}</span>
                                <span class="text-muted">{{ format_locale_date(strtotime($userdata->registered_at)) }}</span>
                            </div>
                        </div>

                        {{-- Σχετικά με εμένα (About Me) --}}
                        @if(!empty($userdata->description) && ($userdata->pic_public || $_SESSION['status'] == USER_TEACHER || $uid == $id))
                            <div class="col-12 py-2">
                                <div class="d-flex align-items-start gap-3">
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
                @if(count($cert_completed) > 0 or count($badge_completed) > 0)
                    @if (count($cert_completed) > 0)
                        <div class='col-12 mt-4'>
                            <div class="card panelCard border-card-left-default px-3 py-2">
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                    <h2 class='text-heading-h3'>{{ trans('langMyCertificates') }}</h2>
                                </div>
                                <div class="card-body">
                                    @if (count($cert_completed) == 1)
                                        <div class='row row-cols-1 row-cols-md-1 g-4'>
                                    @else
                                        <div class='row row-cols-1 row-cols-md-2 g-4'>
                                    @endif
                                        @foreach ($cert_completed as $key => $certificate)
                                            <div class='col'>
                                                <div class="card h-100 border-0">
                                                    <img style='height:150px; width:150px;' src="{{ $urlServer }}resources/img/game/badge.png" class="card-img-top ms-auto me-auto mt-3" alt="certificate">
                                                    <div class="card-body text-center">
                                                        <a href='../out.php?i={{ $certificate->identifier }}'>
                                                            <h4 class='certificate_panel_title text-center'>
                                                                {!! $certificate->cert_title !!}
                                                            </h4>
                                                            <div class='text-center text-success'>
                                                                {!! format_locale_date(strtotime($certificate->assigned), null, false) !!}
                                                            </div>
                                                            <div class='certificate_panel_issuer text-center'>
                                                                {!! $certificate->cert_issuer !!}
                                                            </div>
                                                            <div class='certificate_panel_state text-center mt-2'>
                                                                <i class='fa fa-check-circle fa-inverse state_success fs-5'></i>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (count($badge_completed) > 0)
                        <div class='col-12 mt-4'>
                            <div class="card panelCard border-card-left-default px-3 py-2">
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                    <h2 class='text-heading-h3'>{{ trans('langBadges') }}</h2>
                                </div>
                                <div class="card-body">
                                    @if(count($badge_completed) == 1)
                                    <div class='row row-cols-1 row-cols-md-1 g-4'>
                                    @else
                                    <div class='row row-cols-1 row-cols-md-2 g-4'>
                                    @endif
                                        @foreach ($badge_completed as $key => $badge)
                                            <div class='col'>
                                                <div class="card h-100 border-0 badge-card-wrapper">
                                                    <img style='height:150px; width:150px;' src="{{ $urlServer . BADGE_TEMPLATE_PATH . get_badge_filename($badge->badge) }}" class="card-img-top ms-auto me-auto mt-3" alt="badge">
                                                    <div class="card-body">
                                                        <a href='../../modules/progress/index.php?course={{ course_id_to_code($badge->course_id) }}&amp;badge_id={{ $badge->badge }}&amp;u={{ $badge->user }}'>
                                                            <div class='text-heading-h5 text-center'>{{ ellipsize($badge->title, 40) }}</div>
                                                            <div class='badge_date text-center text-success'>
                                                                {!! format_locale_date(strtotime($badge->assigned), null, false) !!}
                                                            </div>
                                                            <div class='bagde_panel_issuer text-center'>{!! $badge->issuer !!}</div>
                                                        </a>
                                                    </div>
                                                    @if ($uid == $id && isset($openBadgesEnabled) && $openBadgesEnabled)
                                                    <div class='badge-card-footer d-flex flex-wrap align-items-center justify-content-between gap-2'>
                                                        @if (!empty($badge->external_assertion_id))
                                                            <div class='badge-published-status d-flex align-items-center gap-2 mb-0'>
                                                                <i class='fa fa-check-circle text-success'></i>
                                                                <span class='text-success'>{{ trans('langPublishedToBackpack') }}</span>
                                                            </div>
                                                        @elseif (isset($badge->allow_export) && $badge->allow_export == 0)
                                                            <div class='badge-card-footer-text text-muted mb-0'>{{ trans('langPublishToBackpack') }}</div>
                                                            <div class='badge-card-actions d-flex align-items-center gap-2 flex-wrap'>
                                                                <button class='badge-publish-btn disabled' disabled
                                                                        data-bs-toggle='tooltip' data-bs-placement='left'
                                                                        title='{{ trans('langBadgeExportDisabled') }}'
                                                                        style='opacity:0.5;cursor:not-allowed;'>
                                                                    <i class='fa fa-cloud-upload'></i>
                                                                </button>
                                                                <span class='text-muted small'>{{ trans('langBadgeExportDisabledShort') }}</span>
                                                            </div>
                                                        @else
                                                            <div class='badge-card-footer-text mb-0'>{{ trans('langPublishToBackpack') }}</div>
                                                            <div class='badge-card-actions d-flex align-items-center gap-2 flex-wrap'>
                                                                <button class='badge-publish-btn disabled'
                                                                        data-user-badge-id='{{ $badge->user_badge_id }}'
                                                                        data-bs-toggle='tooltip' data-bs-placement='left'
                                                                        title='{{ trans('langPublishBadgeTooltip') }}'
                                                                        aria-label='{{ trans('langPublishBadgeAriaLabel') }}'>
                                                                    <i class='fa fa-cloud-upload'></i>
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
                            </div>
                        </div>
                    @endif

                    @if (isset($openBadgesEnabled) && $openBadgesEnabled && isset($badge_external) && count($badge_external) > 0)
                        <div class='col-12 mt-4'>
                            <div class="card panelCard border-card-left-default px-3 py-2">
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                    <h3>
                                        {{ trans('langExternalBadges') }}
                                        <small class='text-muted ms-2'>({{ trans('langSyncedFromBackpack') }})</small>
                                    </h3>
                                </div>
                                <div class="card-body">
                                    @if(count($badge_external) == 1)
                                    <div class='row row-cols-1 row-cols-md-1 g-4'>
                                    @else
                                    <div class='row row-cols-1 row-cols-md-2 g-4'>
                                    @endif
                                        @foreach ($badge_external as $key => $badge)
                                            <div class='col'>
                                                @if (get_config('eportfolio_enable') && ($id == $uid))
                                                <div class="text-end">
                                                    {!!
                                                        action_button(array(
                                                            array(
                                                                'title' => trans('langAddResePortfolio'),
                                                                'url' => "$urlServer"."main/eportfolio/resources.php?action=add&amp;type=external_badges&amp;rid=".$badge->user_badge_id,
                                                                'icon' => 'fa-star'
                                                            ),
                                                        ))
                                                    !!}
                                                </div>
                                                <div class="modal fade" id="modal_blog_{{$badge->user_badge_id}}" tabindex="-1" aria-labelledby="blogModalLabel_{{$badge->user_badge_id}}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="blogModalLabel_{{$badge->user_badge_id}}">{{trans('langAddResePortfolio')}} - {{ellipsize($badge->title, 40)}}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="'.$langClose.'"></button>
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
                                                <div class="card h-100 border-0 badge-card-wrapper external-badge">
                                                    @if(!empty($badge->image_url))
                                                        <img style='height:150px; width:150px; object-fit:contain;'
                                                             src="{{ $badge->image_url }}"
                                                             class="card-img-top ms-auto me-auto mt-3"
                                                             alt="external badge"
                                                             onerror="this.src='{{ $urlServer }}resources/img/game/badge.png'">
                                                    @else
                                                        <img style='height:150px; width:150px;'
                                                             src="{{ $urlServer }}resources/img/game/badge.png"
                                                             class="card-img-top ms-auto me-auto mt-3"
                                                             alt="external badge">
                                                    @endif
                                                    <div class="card-body">
                                                        <div class='text-heading-h5 text-center'>{{ ellipsize($badge->title, 40) }}</div>
                                                        @if(!empty($badge->created))
                                                            <div class='badge_date text-center text-success'>
                                                                {!! format_locale_date(strtotime($badge->created), null, false) !!}
                                                            </div>
                                                        @endif
                                                        <div class='bagde_panel_issuer text-center'>
                                                            {{ $badge->issuer ?? trans('langUnknownIssuer') }}
                                                        </div>
                                                        @if(!empty($badge->description))
                                                            <div class='badge_description text-center text-muted mt-2' style='font-size:0.9em;'>
                                                                {{ ellipsize($badge->description, 100) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            @endif
        </div>
    </div>
</main>

@if ($uid == $id && !isset($_GET['id']) && !isset($_GET['token']))
    @include('modules.backpack.templates.publish_badge_modal')
@endif

@endsection
