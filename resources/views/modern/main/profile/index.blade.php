
@extends('layouts.default')

@push('head_scripts')
@endpush

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">

                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

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
                                <div class="card panelCard px-lg-4 py-lg-3 h-100">
                                    <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                        <div class='text-uppercase normalColorBlueText TextBold fs-6'>{{ trans('langAnalyticsEditElements') }}</div>
                                    </div>
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
                                            <p class='text-center'>
                                                {{ trans('langExplain') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col">
                                <div class="card panelCard px-lg-4 py-lg-3 h-100">
                                    <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                        <div class='text-uppercase normalColorBlueText TextBold fs-6'>{{ trans('langPersInfo') }}</div>
                                    </div>
                                    <div class="card-body">

                                        @if (!empty($userdata->email) and allow_access($userdata->email_public))
                                            <p class='card-title fw-bold mb-0 fs-6'>{{ trans('langEmail') }}</p>
                                            <p class='card-text'>{!! mailto($userdata->email) !!}</p>
                                        @endif


                                        @if (!empty($userdata->phone) and allow_access($userdata->phone_public))
                                            <p class='card-title fw-bold mb-0 fs-6'>{{ trans('langPhone') }}</p>
                                            <p class='card-text'>{{ q($userdata->phone) }}</p>
                                        @endif


                                        <p class='card-title fw-bold mb-0 fs-6'>{{ trans('langStatus') }}</p>
                                        <p class='card-text'>{{ $userdata->status==1 ? trans('langTeacher'): trans('langStudent') }}</p>


                                        @if (!empty($userdata->am) and allow_access($userdata->am_public))
                                            <p class='card-title fw-bold mb-0 fs-6'>{{ trans('langAm') }}</p>
                                            <p class='card-text'>{{ q($userdata->am) }}</p>
                                        @endif


                                        @if($id == $uid && !empty($extAuthList))
                                            @foreach ($extAuthList as $item)
                                                <p class='card-title fw-bold mb-0 fs-6'>{{ trans('langProviderConnectWith') }}</p>
                                                <p class='card-text'>
                                                    <img src='{{ $themeimg }}/{{ $item->auth_name }}.png' alt=''>
                                                    {{ $authFullName[$item->auth_id] }}
                                                </p>
                                            @endforeach
                                        @endif



                                        <p class='card-title fw-bold mb-0 fs-6'>{{ trans('langFaculty') }}</p>
                                        <p class='card-text'>
                                            @foreach ($user->getDepartmentIds($id) as $i=>$dep)
                                                {!! $tree->getFullPath($dep) !!}
                                                @if($i+1 < count($user->getDepartmentIds($id)))
                                                    <br/>
                                                @endif
                                            @endforeach
                                        </p>


                                        <p class='card-title fw-bold mb-0 fs-6'>{{ trans('langProfileMemberSince') }}</p>
                                        <p class='card-text'>{{ format_locale_date(strtotime($userdata->registered_at)) }}</p>

                                        <p class='card-title fw-bold mb-0 fs-6'>{{ trans('langAboutMe') }}</p>
                                        <p class='card-text m-0 p-0'>{!! $userdata->description !!}</p>

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
                                <div class="card panelCard px-lg-4 py-lg-3">
                                    <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                        <div class='text-uppercase normalColorBlueText TextBold fs-6'>{{ trans('langMyCertificates') }}</div>
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
                                <div class="card panelCard px-lg-4 py-lg-3">
                                    <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                        <div class='text-uppercase normalColorBlueText TextBold fs-6'>{{ trans('langBadges') }}</div>
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
                            <div class="card panelCard px-lg-4 py-lg-3">
                                <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                    <div class='text-uppercase normalColorBlueText TextBold fs-6'>{{trans('langUnregUser')}}</div>
                                </div>
                                <div class="card-body">
                                    <p class='card-text'>{{ trans('langExplain') }}</p>
                                </div>
                                @if($action_bar_unreg == 1)
                                <div class='card-footer bg-white border-0 d-flex justify-content-start'>
                                    <a class='btn deleteAdminBtn' href='{{ $urlAppend }}main/unreguser.php'>{{ trans('langUnregUser')}}</a>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
</div>

@endsection
