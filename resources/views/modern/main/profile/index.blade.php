
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

            @if(Session::has('message'))
            <div class='col-12 all-alerts'>
                <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                    @php
                        $alert_type = '';
                        if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                            $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                        }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                            $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                        }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                            $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                        }else{
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
                                    <p class='text-center'>
                                        @if(($userdata->status == USER_TEACHER))
                                            {{ trans('langMetaTeacher') }}
                                        @elseif(($userdata->status == USER_STUDENT))
                                            {{ trans('langCStudent') }}
                                        @else
                                            {{ trans('langAdministrator')}}
                                        @endif
                                    </p>

                                    @if(get_config('eportfolio_enable'))
                                    <p class='text-center mt-2'>
                                        <a class='btn submitAdminBtn d-inline-flex' href='{{ $urlAppend }}main/eportfolio/index.php?id={{ $uid }}&token={{ token_generate("eportfolio" . $id) }}'>
                                            {{ trans('langMyePortfolio') }}
                                        </a>
                                    </p>
                                    @endif

                                    @if(get_config('personal_blog'))
                                    <p class='text-center mt-2'>
                                        <a class='btn submitAdminBtn d-inline-flex' href='{{ $urlAppend }}modules/blog/index.php?user_id={{ $uid }}&token={{ token_generate("personal_blog" . $id) }}'>
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
                            <div class='card-header border-0 bg-default d-flex justify-content-between align-items-center'>
                                <h3>{{ trans('langPersInfo') }}</h3>
                            </div>
                            <div class="card-body">

                                @if (!empty($userdata->email) and allow_access($userdata->email_public))
                                    <div class='profile-pers-info-data mb-3'>
                                        <p class='form-label'>{{ trans('langEmail') }}</p>
                                        <p class='form-value'>{!! mailto($userdata->email) !!}</p>
                                    </div>
                                @endif


                                @if (!empty($userdata->phone) and allow_access($userdata->phone_public))
                                    <div class='profile-pers-info-data mb-3'>
                                        <p class='form-label'>{{ trans('langPhone') }}</p>
                                        <p class='form-value'>{{ q($userdata->phone) }}</p>
                                    </div>
                                @endif

                                <div class='profile-pers-info-data mb-3'>
                                    <p class='form-label'>{{ trans('langStatus') }}</p>
                                    <p class='form-value'>{{ $userdata->status==1 ? trans('langTeacher'): trans('langStudent') }}</p>
                                </div>


                                @if (!empty($userdata->am) and allow_access($userdata->am_public))
                                    <div class='profile-pers-info-data mb-3'>
                                        <p class='form-label'>{{ trans('langAm') }}:</p>
                                        <p class='form-value'>{{ q($userdata->am) }}</p>
                                    </div>
                                @endif


                                @if($id == $uid && !empty($extAuthList))
                                    <div class='profile-pers-info-data mb-3'>
                                        @foreach ($extAuthList as $item)
                                            <p class='form-label'>{{ trans('langProviderConnectWith') }}</p>
                                            <p class='form-value'>
                                                <img src='{{ $themeimg }}/{{ $item->auth_name }}.png' alt=''>
                                                {{ $authFullName[$item->auth_id] }}
                                            </p>
                                        @endforeach
                                    </div>
                                @endif


                                <div class='profile-pers-info-data mb-3'>
                                    <p class='form-label'>{{ trans('langFaculty') }}</p>
                                    <p class='form-value'>
                                        @foreach ($user->getDepartmentIds($id) as $i=>$dep)
                                            {!! $tree->getFullPath($dep) !!}
                                            @if($i+1 < count($user->getDepartmentIds($id)))
                                                <br/>
                                            @endif
                                        @endforeach
                                    </p>
                                </div>


                                <div class='profile-pers-info-data mb-3'>
                                    <p class='form-label'>{{ trans('langProfileMemberSince') }}</p>
                                    <p class='form-value'>{{ format_locale_date(strtotime($userdata->registered_at)) }}</p>
                                </div>

                                <div class='panel-group group-section' id='accordion' role='tablist' aria-multiselectable='true'>
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
                            <div class='card-header border-0 bg-default d-flex justify-content-between align-items-center'>
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
                                                        <h5 class='certificate_panel_title text-center'>
                                                            {!! $certificate->cert_title !!}
                                                        </h5>
                                                        <div class='text-center text-success'>
                                                            {!! format_locale_date(strtotime($certificate->assigned), null, false) !!}
                                                        </div>
                                                        <div class='certificate_panel_issuer text-center text-secondary'>
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
                            <div class='card-header border-0 bg-default d-flex justify-content-between align-items-center'>
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
                                                        <div class='bagde_panel_issuer text-center text-secondary'>
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
                        <div class='card-header border-0 bg-default d-flex justify-content-between align-items-center'>
                            <h3>{{trans('langUnregUser')}}</h3>
                        </div>
                        <div class="card-body">
                            <p class='card-text'>{{ trans('langExplain') }}</p>
                        </div>
                        @if($action_bar_unreg == 1)
                        <div class='card-footer bg-default border-0 d-flex justify-content-start'>
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
