
@extends('layouts.default')

@push('head_scripts')
@endpush

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">

                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    <div class="col-12">
                        <div class="panel panel-admin px-lg-4 py-lg-3 bg-white">
                            <div class='panel-heading bg-body'>
                                <div class='col-12 Help-panel-heading'>
                                    <span class='text-uppercase fw-bold Help-text-panel-heading'>{{trans('langAnalyticsEditElements')}}</span>
                                </div>
                            </div>
                            <div class="panel-body">
                            
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

                                <div class="row px-2">
                                    <div class="col-xl-4 col-lg-4 col-md-5 col-sm-12 col-12">
                                        <div class="row">
                                            <div class="col-12 d-flex justify-content-center">
                                                <div id='profile-avatar'>{!! $profile_img !!}</div>
                                            </div>
                                        </div>
                                        <div class="row justify-content-md-start justify-content-center text-center mt-2" >
                                            <h5 class='text-center blackBlueText TextSemiBold mt-3'> {{ $userdata->surname }} {{ $userdata->givenname }} </h5>
                                            <p class='text-center'>
                                                @if(($userdata->status == USER_TEACHER))
                                                    {{ trans('langMetaTeacher') }}
                                                @elseif(($userdata->status == USER_STUDENT))
                                                    {{ trans('langCStudent') }}
                                                @else
                                                    {{ trans('langAdministrator')}}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="row justify-content-md-start justify-content-center text-center mt-3">
                                            <div class="py-1 d-flex justify-content-center align-items-center" >
                                                {!! $action_bar !!}
                                            </div>
                                            <div class="py-1">
                                                {{ trans('langExplain') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-8 col-lg-8 col-md-7 col-sm-12 col-12 ps-md-3 pe-md-0 ps-0 pe-0">

                                        <div class='panel panel-admin border-0 shadow-none mt-md-2 mt-3 bg-white rounded-0 shadow-none'>
                                            <div class='panel-heading rounded-0 bg-white ps-md-3 pe-md-1 px-0'>
                                                <div class='panel-heading bg-white ps-md-3 pe-md-0'>
                                                    <div class='col-12 Help-panel-heading'>
                                                        <span class='text-uppercase Help-text-panel-heading'>{{ trans('langPersInfo') }}</span>
                                                    </div>
                                                </div>

                                                <div class='panel-body rounded-0'>
                                                    <div class="row mt-0">
                                                        <div class="col-xl-4 col-lg-6 col-12 mb-2">
                                                            <p class='text-secondary TextMedium small-text'>E-mail:</p>
                                                            <p class='blackBlueText small-text'>{{ $userdata->email }}</p>
                                                        </div>

                                                        <div class="col-xl-4 col-lg-6 col-12 mb-2">
                                                            <p class='text-secondary TextMedium small-text'>{{ trans('langStatus') }}:</p>
                                                            <p class='blackBlueText small-text'>
                                                                @if(($userdata->status == USER_TEACHER))
                                                                    {{ trans('langMetaTeacher') }}
                                                                @elseif(($userdata->status == USER_STUDENT))
                                                                    {{ trans('langCStudent') }}
                                                                @else
                                                                    {{ trans('langAdministrator')}}
                                                                @endif

                                                            </p>
                                                        </div>

                                                        <div class="col-xl-4 col-lg-6 col-12 mb-lg-3 mt-lg-3 mt-xl-0 mb-2">
                                                            <p class='text-secondary TextMedium small-text'>{{trans('langPhone')}}: </p>
                                                            <p class='blackBlueText small-text'>{{ $userdata->phone }}</p>
                                                        </div>


                                                        <div class="col-xl-4 col-lg-6 col-12 mt-xl-0 mt-lg-3 mb-lg-3 mb-2">
                                                            <p class='text-secondary TextMedium small-text'>{{ trans('langAm') }}:</p>
                                                            <p class='blackBlueText small-text'>{{ $userdata->am }}</p>
                                                        </div>

                                                        <div class="col-xl-4 col-lg-6 col-12 mb-lg-3 mb-2">
                                                            <p class='text-secondary TextMedium small-text'>{{ trans('langProfileMemberSince') }}:</p>
                                                            <p class='blackBlueText small-text'>{{ $userdata->registered_at }}</p>
                                                        </div>

                                                        <div class="col-xl-4 col-lg-6 col-12">
                                                            <p class='text-secondary TextMedium small-text'>{{ trans('langFaculty') }}:</p>
                                                            <p class='blackBlueText small-text'>
                                                                @php
                                                                    $user = new User();
                                                                    $departments = $user->getDepartmentIds($uid);
                                                                @endphp
                                                                @foreach ($departments as $dep)
                                                                    {!! $tree->getFullPath($dep) !!}
                                                                @endforeach
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='panel panel-admin border-0 bg-white rounded-0 px-0'>
                                            <div class='panel-heading rounded-0 bg-white ps-md-3 pe-md-1 px-0'>
                                                <div class='panel-heading bg-body ps-md-3 pe-md-0'>
                                                    <div class='col-12 Help-panel-heading'>
                                                        <span class='text-uppercase Help-text-panel-heading'>{{ trans('langAboutMe') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='panel-body rounded-0 ps-md-3 pt-0 px-0'>
                                                <div class='col-12 ps-3'>
                                                    @if (!empty($userdata->description))
                                                        {!! standard_text_escape($userdata->description) !!}
                                                    @else
                                                        <p class='text-center mb-0'>{{ trans('langNoInfoAvailable') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        
                                    </div>
                                </div>

                                <div class="row px-2 mb-3">
                                    {!! render_profile_fields_content(array('user_id' => $id)) !!}
                                </div>

                                <div class="row px-2">
                                    <div class="col-12">
                                        <div class="panel panel-admin border-0 bg-white rounded-0 px-0">
                                            <div class='panel-heading rounded-0 bg-white ps-md-0 pe-md-1 px-0'>
                                                <div class='panel-heading bg-body ps-0 pe-0'>
                                                    <div class='col-12 Help-panel-heading'>
                                                        <span class='text-uppercase Help-text-panel-heading'>{{ trans('langProfilePersInfo') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='panel-body rounded-0 ps-md-0 pt-0 px-0'>
                                                
                                                <div class="profile-content-panel-text">
                                                    @if (!empty($userdata->email) and allow_access($userdata->email_public))
                                                        <span class='text-secondary fw-bold'>{{ trans('langEmail') }}:</span>
                                                        {!! mailto($userdata->email) !!}
                                                    @endif
                                                </div>
                                            

                                                @if (!empty($userdata->phone) and allow_access($userdata->phone_public))
                                                    <div class='lh-25px'>
                                                        <span class='text-secondary fw-bold'>
                                                            {{ trans('langPhone') }}:
                                                        </span>
                                                        {{ q($userdata->phone) }}
                                                    </div>
                                                @endif
                                                <div class='lh-25px'>
                                                        <span class='text-secondary fw-bold'>
                                                            {{ trans('langStatus') }}:
                                                        </span>{{ $userdata->status==1 ? trans('langTeacher'): trans('langStudent') }}
                                                </div>

                                                @if (!empty($userdata->am) and allow_access($userdata->am_public))
                                                    <div class='lh-25px'>
                                                        <span class='text-secondary fw-bold'>
                                                            {{ trans('langAm') }}:
                                                        </span>
                                                            {{ q($userdata->am) }}
                                                    </div>
                                                @endif

                                                @if($id == $uid && !empty($extAuthList))
                                                    <div>
                                                        @foreach ($extAuthList as $item)
                                                            <span class='tag'>{{ trans('langProviderConnectWith') }} : </span>
                                                            <span class='tag-value'><img src='{{ $themeimg }}/{{ $item->auth_name }}.png' alt=''> {{ $authFullName[$item->auth_id] }}</span><br>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                <div class='lh-25px'>
                                                    <span class='text-secondary fw-bold'>
                                                        {{ trans('langFaculty') }}:
                                                    </span>
                                                    @foreach ($user->getDepartmentIds($id) as $i=>$dep)
                                                        {!! $tree->getFullPath($dep) !!}
                                                        @if($i+1 < count($user->getDepartmentIds($id)))
                                                            <br/>
                                                        @endif
                                                    @endforeach
                                                </div>
                                                <div class='lh-25px'>
                                                    <span class='text-secondary fw-bold'>
                                                        {{ trans('langProfileMemberSince') }}:
                                                    </span>{{ $userdata->registered_at }}
                                                </div>
                                               
                                            </div>
                                        </div>
                                    </div>
                                    {{--<div class="col-12 mt-3">
                                        <div class="panel panel-admin border-0 bg-white rounded-0 px-0">
                                            <div class='panel-heading rounded-0 bg-white ps-md-0 pe-md-1 px-0'>
                                                <div class='panel-heading bg-body ps-0 pe-0'>
                                                    <div class='col-12 Help-panel-heading'>
                                                        <span class='text-uppercase Help-text-panel-heading'> {{ trans('langProfileAboutMe') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='panel-body rounded-0 ps-md-0 pt-0 px-0'>
                                                <div class="profile-content-panel-text">
                                                    <p>
                                                    @if (!empty($userdata->description))
                                                        {!! standard_text_escape($userdata->description) !!}
                                                    @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>--}}
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(count($cert_completed) > 0 or count($badge_completed) > 0)

                        @if (count($cert_completed) > 0)
                            <div class='col-12 mt-3'>
                                <div class="panel panel-admin px-lg-4 py-lg-3 bg-white">
                                    <div class='panel-heading bg-body'>
                                        <div class='panel-heading bg-body ps-0 pe-0'>
                                            <div class='col-12 Help-panel-heading'>
                                                <span class='text-uppercase fw-bold Help-text-panel-heading'>{{ trans('langMyCertificates') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class='row row-cols-1 row-cols-md-2 g-4'>
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
                            <div class='col-12 mt-3'>
                                <div class="panel panel-admin px-lg-4 py-lg-3 bg-white">
                                    <div class='panel-heading bg-body'>
                                        <div class='panel-heading bg-body ps-0 pe-0'>
                                            <div class='col-12 Help-panel-heading'>
                                                <span class='text-uppercase fw-bold Help-text-panel-heading'>{{ trans('langBadges') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class='row row-cols-1 row-cols-md-2 g-4'>
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

                            <div class="col-12 mt-3">
                                <div class="panel panel-admin px-lg-4 py-lg-3 bg-white">
                                    <div class='panel-heading bg-body'>
                                        <div class='col-12 Help-panel-heading'>
                                            <span class='text-uppercase fw-bold Help-text-panel-heading'>{{trans('langUnregUser')}}</span>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="profile-content-panel-text">
                                            {{ trans('langExplain') }}
                                        </div>
                                    </div>
                                    <div class='panel-footer'>
                                         {!! $action_bar_unreg !!}
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
