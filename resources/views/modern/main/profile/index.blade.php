
@extends('layouts.default')

@push('head_scripts')
@endpush

@section('content')

<div class="col-12 main-section">
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

            <div class='col-12'>
                <div class="row row-cols-1 row-cols-md-2 g-4">

                    <div class="col">
                        <div class="card panelCard border-card-left-default px-3 py-2 h-100">
                            <div class="card-body">
                                <div class="col-12">
                                    <div id='profile-avatar'>
                                        {!! $profile_img !!}
                                    </div>
                                    <h3 class='text-center mt-3'> {{ $userdata->surname }} {{ $userdata->givenname }} </h3>
                                    <div class='text-center title-default-line-height m-3'>
                                        {!! mailto($userdata->username) !!}
                                    </div>
                                    @if(get_config('eportfolio_enable'))
                                        <p class='text-center mt-2'>
                                            <a class='btn submitAdminBtn d-inline-flex' href='{{ $urlAppend }}main/eportfolio/index.php?id={{ $id }}&token={{ token_generate("eportfolio" . $id) }}'>
                                                {{ trans('langMyePortfolio') }}
                                            </a>
                                        </p>
                                    @endif

                                    @if(get_config('personal_blog'))
                                        <p class='text-center mt-2'>
                                            <a class='btn submitAdminBtn d-inline-flex' href='{{ $urlAppend }}modules/blog/index.php?user_id={{ $id }}&token={{ token_generate("personal_blog" . $id) }}'>
                                                {{ trans('langUserBlog') }}
                                            </a>
                                        </p>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="card panelCard border-card-left-default px-3 py-2 h-100">
                            <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                <h3>{{ trans('langPersInfo') }}</h3>
                            </div>
                            <div class="card-body">
                                <ul class='list-group list-group-flush'>
                                    @if ($is_admin)
                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-lg-2 g-1'>
                                                <div class='col-lg-4 col-12'>
                                                    <div class='title-default'>{{ trans('langUserPermissions') }}</div>
                                                </div>
                                                <div class='col-lg-8 col-12 title-default-line-height'>
                                                    {{ trans('langAdministrator') }}
                                                </div>
                                            </div>
                                        </li>
                                    @elseif ($userdata->status == USER_TEACHER)
                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-lg-2 g-1'>
                                                <div class='col-lg-4 col-12'>
                                                    <div class='title-default'>{{ trans('langUserPermissions') }}</div>
                                                </div>
                                                <div class='col-lg-8 col-12 title-default-line-height'>
                                                    {{ trans('langWithCourseCreationRights') }}
                                                </div>
                                            </div>
                                        </li>
                                   @endif

                                    @if (!empty($userdata->email) and allow_access($userdata->email_public))
                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-lg-2 g-1'>
                                                <div class='col-lg-4 col-12'>
                                                    <div class='title-default'>{{ trans('langEmail') }}</div>
                                                </div>
                                                <div class='col-lg-8 col-12 title-default-line-height'>
                                                    {!! mailto($userdata->email) !!}
                                                </div>
                                            </div>
                                        </li>
                                    @endif

                                    @if (!empty($userdata->phone) and allow_access($userdata->phone_public))
                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-lg-2 g-1'>
                                                <div class='col-lg-4 col-12'>
                                                    <div class='title-default'>{{ trans('langPhone') }}</div>
                                                </div>
                                                <div class='col-lg-8 col-12 title-default-line-height'>
                                                    {{ q($userdata->phone) }}
                                                </div>
                                            </div>
                                        </li>
                                    @endif

                                    @if (!empty($userdata->am) and allow_access($userdata->am_public))
                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-lg-2 g-1'>
                                                <div class='col-lg-4 col-12'>
                                                    <div class='title-default'>{{ trans('langAm') }}</div>
                                                </div>
                                                <div class='col-lg-8 col-12 title-default-line-height'>
                                                    {{ q($userdata->am) }}
                                                </div>
                                            </div>
                                        </li>
                                    @endif


                                    @if($id == $uid && !empty($extAuthList))

                                            @foreach ($extAuthList as $item)
                                                <li class='list-group-item element'>
                                                    <div class='row row-cols-1 row-cols-lg-2 g-1'>
                                                        <div class='col-lg-4 col-12'>
                                                            <div class='title-default'>{{ trans('langProviderConnectWith') }}</div>
                                                        </div>
                                                        <div class='col-lg-8 col-12 title-default-line-height'>
                                                            <img src='{{ $themeimg }}/{{ $item->auth_name }}.png' alt=''>
                                                            {{ $authFullName[$item->auth_id] }}
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach

                                    @endif


                                    <li class='list-group-item element'>
                                        <div class='row row-cols-1 row-cols-lg-2 g-1'>
                                            <div class='col-lg-4 col-12'>
                                                <div class='title-default'>{{ trans('langFaculty') }}</div>
                                            </div>
                                            <div class='col-lg-8 col-12 title-default-line-height'>
                                                @foreach ($user->getDepartmentIds($id) as $i=>$dep)
                                                    {!! $tree->getFullPath($dep) !!}
                                                    @if($i+1 < count($user->getDepartmentIds($id)))
                                                        <br/>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </li>

                                    <li class='list-group-item element'>
                                        <div class='row row-cols-1 row-cols-lg-2 g-1'>
                                            <div class='col-lg-4 col-12'>
                                                <div class='title-default'>{{ trans('langProfileMemberSince') }}</div>
                                            </div>
                                            <div class='col-lg-8 col-12 title-default-line-height'>
                                                {{ format_locale_date(strtotime($userdata->registered_at)) }}
                                            </div>
                                        </div>
                                    </li>

                                </ul>

                                <div class='panel-group group-section mt-4' id='accordion' role='tablist' aria-multiselectable='true'>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item px-0 mb-4 bg-transparent">
                                            <a class="accordion-btn d-flex justify-content-start align-items-start" role="button" data-bs-toggle="collapse" href="#AboutMe" aria-expanded="false">
                                                <span class="fa-solid fa-chevron-down"></span>
                                                {{ trans('langAboutMe') }}
                                            </a>
                                            <div id="AboutMe" class="panel-collapse accordion-collapse collapse border-0 rounded-0" role="tabpanel" data-bs-parent="#accordion">
                                                <div class="panel-body bg-transparent Neutral-900-cl px-4">
                                                    @if(!empty($userdata->description))
                                                        {!! $userdata->description !!}
                                                    @else
                                                        {{ trans('langNoInfoAvailable') }}
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($userdata->status != USER_GUEST)
                {!! render_profile_fields_content(array('user_id' => $id)) !!}
            @endif

            @if(count($cert_completed) > 0 or count($badge_completed) > 0)

                @if (count($cert_completed) > 0)
                    <div class='col-12 mt-4'>
                        <div class="card panelCard border-card-left-default px-3 py-2">
                            <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                <h3>{{ trans('langMyCertificates') }}</h3>
                            </div>
                            <div class="card-body">
                                @if(count($cert_completed) == 1)
                                <div class='row row-cols-1 row-cols-md-1 g-4'>
                                @else
                                <div class='row row-cols-1 row-cols-md-2 g-4'>
                                @endif
                                    @foreach ($cert_completed as $key => $certificate)
                                        <div class='col'>
                                            <div class="card h-100 border-0">
                                                <img style='height:150px; width:150px;' src="{{ $urlServer }}template/modern/img/game/badge.png" class="card-img-top ms-auto me-auto mt-3" alt="certificate">
                                                <div class="card-body">
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
                                <h3>{{ trans('langBadges') }}</h3>
                            </div>
                            <div class="card-body">
                                @if(count($badge_completed) == 1)
                                <div class='row row-cols-1 row-cols-md-1 g-4'>
                                @else
                                <div class='row row-cols-1 row-cols-md-2 g-4'>
                                @endif
                                    @foreach ($badge_completed as $key => $badge)
                                        <div class='col'>
                                            <div class="card h-100 border-0">
                                                <img style='height:150px; width:150px;' src="{{ $urlServer . BADGE_TEMPLATE_PATH . get_badge_filename($badge->badge) }}" class="card-img-top ms-auto me-auto mt-3" alt="badge">
                                                <div class="card-body">
                                                    <a href='../../modules/progress/index.php?course={{ course_id_to_code($badge->course_id) }}&amp;badge_id={{ $badge->badge }}&amp;u={{ $badge->user }}'>
                                                        <h5 class='text-center'>
                                                            {{ ellipsize($badge->title, 40) }}
                                                        </h5>
                                                        <div class='badge_date text-center text-success'>
                                                            {!! format_locale_date(strtotime($badge->assigned), null, false) !!}
                                                        </div>
                                                        <div class='bagde_panel_issuer text-center'>
                                                            {!! $badge->issuer !!}
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

            @endif


            @if ($uid == $id)
                <div class="col-12 mt-4">
                    <div class="card panelCard border-card-left-default px-3 py-2">
                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                            <h3>{{trans('langUnregUser')}}</h3>
                        </div>
                        <div class="card-body">
                            <p class='card-text'>{{ trans('langExplain') }}</p>
                        </div>
                        @if($action_bar_unreg == 1)
                        <div class='card-footer border-0 d-flex justify-content-start'>
                            <a class='btn deleteAdminBtn' href='{{ $urlAppend }}main/unreguser.php'>{{ trans('langUnregUser')}}</a>
                        </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
