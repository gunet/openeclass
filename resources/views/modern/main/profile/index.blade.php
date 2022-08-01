
@extends('layouts.default')

@push('head_scripts')
@endpush

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="inner-heading clearfix">
                                    {!! $action_bar !!}
                                    @if(Session::has('message'))
                                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                            {{ Session::get('message') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </p>
                                    </div>
                                    @endif
                                </div>
                                <div class="row p-2"></div>
                                <div class="row">
                                    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                        <div class="row">
                                            <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                                <div id='profile-avatar'>{!! $profile_img !!}</div>
                                            </div>
                                            <div class="col-xxl-8 col-xl-8 col-lg-8 col-md-12 col-sm-12 col-12">
                                                <div class="profile-name">{{ $userdata->givenname }} {{ $userdata->surname }}</div>
                                                <div class='not_visible'><strong>{{ $userdata->username }}</strong></div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                        {!! $action_bar_blog_portfolio !!}
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="profile-content-panel">
                                            <div class="profile-content-panel-title">
                                                {{ trans('langProfilePersInfo') }}
                                            </div>
                                            <div class="profile-content-panel-text">
                                                @if (!empty($userdata->email) and allow_access($userdata->email_public))
                                                    <span style='font-weight: bold; color: #888;'>{{ trans('langEmail') }}:</span>
                                                    {!! mailto($userdata->email) !!}
                                                @endif
                                            </div>

                                            @if (!empty($userdata->phone) and allow_access($userdata->phone_public))
                                                <div style='line-height:26px;'>
                                                <span style='font-weight: bold; color: #888;'>
                                                    {{ trans('langPhone') }}:
                                                </span>
                                                    {{ q($userdata->phone) }}
                                                </div>
                                            @endif
                                            <div style="line-height:26px;">
                                                    <span style="font-weight: bold; color: #888;">
                                                        {{ trans('langStatus') }}:
                                                    </span>{{ $userdata->status==1 ? trans('langTeacher'): trans('langStudent') }}
                                            </div>

                                            @if (!empty($userdata->am) and allow_access($userdata->am_public))
                                                <div style="line-height:26px;">
                                                    <span style="font-weight: bold; color: #888;">
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
                                            <div style="line-height:26px;">
                                                <span style="font-weight: bold; color: #888;">
                                                    {{ trans('langFaculty') }}:
                                                </span>
                                                @foreach ($user->getDepartmentIds($id) as $i=>$dep)
                                                    {!! $tree->getFullPath($dep) !!}
                                                    @if($i+1 < count($user->getDepartmentIds($id)))
                                                        <br/>
                                                    @endif
                                                @endforeach
                                            </div>
                                            <div style="line-height:26px;">
                                                <span style="font-weight: bold; color: #888;">
                                                    {{ trans('langProfileMemberSince') }}:
                                                </span>{{ $userdata->registered_at }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="profile-content-panel">
                                        <div class="profile-content-panel-title">
                                            {{ trans('langProfileAboutMe') }}
                                        </div>
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
                         {!! render_profile_fields_content(array('user_id' => $id)) !!} 
                        </div>
                    </div>


                    {{-- @if (count($cert_completed) > 0) --}}
                    <div class='col-12 mt-3'>
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class='col-sm-10'><h4>{{ trans('langMyCertificates') }}</h4></div>
                                <div class='row'>
                                    <div class='badge-container'>
                                        <div class='clearfix'>
                                            @foreach ($cert_completed as $key => $certificate)
                                                <div class='col-xs-12 col-sm-4 col-xl-2'>
                                                    <a style='display:inline-block; width: 100%;' href='../out.php?i={{ $certificate->identifier }}'>
                                                        <div class='certificate_panel' style='width:210px; height:120px;'>
                                                            <h4 class='certificate_panel_title' style='font-size:15px; margin-top:2px;'>
                                                                {{ $certificate->cert_title }}
                                                            </h4>
                                                            <div style='font-size:10px;'>
                                                                {{ claro_format_locale_date('%A, %d %B %Y', strtotime($certificate->assigned)) }}
                                                            </div>
                                                            <div class='certificate_panel_issuer' style='font-size:11px;'>
                                                                {{ $certificate->cert_issuer }}
                                                            </div>

                                                            <div class='certificate_panel_state'>
                                                                <i class='fa fa-check-circle fa-inverse state_success'></i>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- @endif --}}


                    @if (count($badge_completed) > 0)
                    <div class='col-12 mt-3'>
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class='col-sm-10' style='padding-bottom:30px;'><h4>{{ trans('langBadges') }}</h4></div>
                                    <div class='row'>
                                        <div class='badge-container'>
                                        <div class='clearfix'>
                                            @foreach ($badge_completed as $key => $badge)
                                                <div class='col-xs-6 col-sm-4'>
                                                <a href='../../modules/progress/index.php?course={{ course_id_to_code($badge->course_id) }}&amp;badge_id={{ $badge->badge }}&amp;u={{ $badge->user }}' style='display: block; width: 100%'>
                                                    <img class='center-block' src='{{ $urlServer . BADGE_TEMPLATE_PATH . get_badge_filename($badge->badge) }}' width='100' height='100'>
                                                    <h5 class='text-center' style='padding-top: 10px;'>
                                                        {{ ellipsize($badge->title, 40) }}
                                                    </h5>
                                                </a></div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if ($uid == $id)
                       
                            <div class="col-12 mt-3">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-sm-8">
                                                <div class="profile-content-panel-title">
                                                    {{ trans('langUnregUser') }}
                                                </div>
                                                <div class="profile-content-panel-text">
                                                    {{ trans('langExplain') }}
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
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
