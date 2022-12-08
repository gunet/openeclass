
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
                        <div class="panel panel-admin bg-white">
                            <div class='panel-heading bg-body'>
                                <div class='col-12 Help-panel-heading'>
                                    <span class='text-uppercase fw-bold Help-text-panel-heading-Portfolio'>{{trans('langAnalyticsEditElements')}}</span>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="inner-heading clearfix">
                                    {!! $action_bar !!}
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
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="row">
                                            <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12 d-flex justify-content-center">
                                                <div id='profile-avatar'>{!! $profile_img !!}</div>
                                            </div>
                                            <div class="col-xxl-8 col-xl-8 col-lg-8 col-md-12 col-sm-12 col-12 text-md-start text-center">
                                                <div class="profile-name TextBold">{{ $userdata->givenname }} {{ $userdata->surname }}</div>
                                                <div class='not_visible'><strong>{{ $userdata->username }}</strong></div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        {!! $action_bar_blog_portfolio !!}
                                    </div>
                                    <div class='col-sm-12'>
                                        <div class='row'>
                                           {!! render_profile_fields_content(array('user_id' => $id)) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="panel panel-default">
                                            <div class="panel-heading text-dark text-center">
                                                {{ trans('langProfilePersInfo') }}
                                            </div>
                                            <div class='panel-body'>
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
                                                <div  class='lh-25px'>
                                                        <span class='text-secondary fw-bold'>
                                                            {{ trans('langStatus') }}:
                                                        </span>{{ $userdata->status==1 ? trans('langTeacher'): trans('langStudent') }}
                                                </div>

                                                @if (!empty($userdata->am) and allow_access($userdata->am_public))
                                                    <div  class='lh-25px'>
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
                                                <div  class='lh-25px'>
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
                                                <div  class='lh-25px'>
                                                    <span class='text-secondary fw-bold'>
                                                        {{ trans('langProfileMemberSince') }}:
                                                    </span>{{ $userdata->registered_at }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12 mt-md-0 mt-3">
                                        <div class="panel panel-default">
                                            <div class="panel-heading text-center text-dark">
                                                {{ trans('langProfileAboutMe') }}
                                            </div>
                                            <div class='panel-body'>
                                                <div class="profile-content-panel-text">
                                                    <p>
                                                    @if (!empty($userdata->description))
                                                        {!! standard_text_escape($userdata->description) !!}
                                                    @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(count($cert_completed) > 0 or count($badge_completed) > 0)

                        @if (count($cert_completed) > 0)
                        <div class='col-sm-6 mt-3'>
                            <div class="panel panel-success">
                                <div class='panel-heading'>
                                    <div class='panel-title text-center text-white p-0'>
                                         {{ trans('langMyCertificates') }}
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class='row'>
                                        <div class='badge-container'>
                                            <div class='clearfix'>
                                            @php $counterCertificate = 0; @endphp
                                                @foreach ($cert_completed as $key => $certificate)
                                                    <div class='col-sm-12 d-flex justify-content-center'>
                                                        <a href='../out.php?i={{ $certificate->identifier }}'>
                                                            <div class='certificate_panel'>
                                                                <h4 class='certificate_panel_title text-center'>
                                                                    {!! $certificate->cert_title !!}
                                                                </h4>
                                                                <div class='text-center text-success'>
                                                                    {!! format_locale_date(strtotime($certificate->assigned), null, false) !!}
                                                                </div>
                                                                <div class='certificate_panel_issuer text-center text-secondary'>
                                                                    {!! $certificate->cert_issuer !!}
                                                                </div>

                                                                <div class='certificate_panel_state text-center'>
                                                                    <i class='fa fa-check-circle fa-inverse state_success'></i>
                                                                </div>

                                                                <div class='certificate_panel_badge mt-2'>
                                                                    <img class='m-auto d-block' src='{{$urlServer}}template/modern/img/game/badge.png' width='100' height='100'>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    @if($counterCertificate < (count($cert_completed)-1) and count($cert_completed)>=2)
                                                    <hr>
                                                    @endif
                                                    @php $counterCertificate++; @endphp

                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if (count($badge_completed) > 0)
                        <div class='col-sm-6 mt-3'>
                            <div class="panel panel-success">
                                <div class='panel-heading'>
                                    <div class='panel-title text-center text-white p-0'>
                                        {{ trans('langBadges') }}
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class='row'>
                                        <div class='badge-container'>
                                            <div class='clearfix'>
                                            @php $counterBagde = 0; @endphp
                                                @foreach ($badge_completed as $key => $badge)
                                                <div class='col-sm-12 d-flex justify-content-center'>
                                                    <a href='../../modules/progress/index.php?course={{ course_id_to_code($badge->course_id) }}&amp;badge_id={{ $badge->badge }}&amp;u={{ $badge->user }}'>
                                                        <h4 class='text-center'>
                                                            {{ ellipsize($badge->title, 40) }}
                                                        </h4>
                                                        <div class='badge_date text-center text-success'>
                                                            {!! format_locale_date(strtotime($badge->assigned), null, false) !!}
                                                        </div>
                                                        <div class='bagde_panel_issuer text-center text-secondary'>
                                                            {!! $badge->issuer !!}
                                                        </div>
                                                        <img class='m-auto d-block' src='{{ $urlServer . BADGE_TEMPLATE_PATH . get_badge_filename($badge->badge) }}' width='100' height='100'>
                                                    </a>
                                                </div>
                                                @if($counterBagde < (count($badge_completed)-1) and count($badge_completed)>=2)
                                                <hr>
                                                @endif
                                                @php $counterBagde++; @endphp
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                    @endif

                    @if ($uid == $id)

                            <div class="col-12 mt-3">
                                <div class="panel panel-danger">
                                    <div class='panel-heading'>
                                        <div class='panel-title text-center p-0'>
                                            {{trans('langUnregUser')}}
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-9 col-12">
                                                <div class="profile-content-panel-text">
                                                    {{ trans('langExplain') }}
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-12 mt-md-0 mt-3">
                                                {!! $action_bar_unreg !!}
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
