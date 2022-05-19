@extends('layouts.default')

@push('head_scripts')
@endpush

@section('content')

    <style>
        .profile-name
        {
            font-size: larger;
            font-weight: bold;
            line-height: 3;
        }

        .profile-content-panel
        {
            font-size: 14px;
            margin: 25px 0;
            padding: 25px;
            background-color: #f5f5f5;
        }

        .profile-content-panel-title
        {
            font-size: larger;
            color: #888;
            margin-bottom: 40px;
        }
    </style>

    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="inner-heading clearfix">
                        {!! $action_bar !!}
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-xs-4">
                                    <div id='profile-avatar'>{!! $profile_img !!}</div>
                                </div>
                                <div class="col-xs-8">
                                    <div class="profile-name">{{ $userdata->givenname }} {{ $userdata->surname }}</div>
                                    <div class='not_visible'><strong>{{ $userdata->username }}</strong></div>
                                </div>

                            </div>
                        </div>
                        <div class="col-sm-6">
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
                                    @foreach ($extAuthList as $item)
                                        <div>
                                            <span style='font-weight: bold; color: #888;'>{{ trans('langProviderConnectWith') }} &nbsp;:&nbsp;</span>
                                            <span class='tag-value'><img src='{{ $themeimg }}/{{ $item->auth_name }}.png' alt=''> {{ q($authFullName[$item->auth_id]) }}</span>
                                        </div>
                                    @endforeach
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
    </div>

@if (count($cert_completed) > 0)
        <div class="panel panel-default">
            <div class="panel-body">
                <div class='col-sm-10' style='padding-top:20px;'><h4>{{ trans('langMyCertificates') }}</h4></div>
                <div class='row'>
                    <div class='badge-container'>
                        <div class='clearfix'>
                            @foreach ($cert_completed as $key => $certificate)
                                <?php
                                    $template_details = get_certificate_template($certificate->template_id);
                                    $template_name = key($template_details);
                                    $template_filename = $template_details[$template_name];
                                    $thumbnail_filename = preg_replace('/.html/', '_thumbnail.png', $template_filename);
                                    $icon_link = $urlServer . CERT_TEMPLATE_PATH . $thumbnail_filename;
                                ?>
                                <div class='col-sm-5' style='padding-top: 20px;'>";
                                    <a style='display:inline-block; width: 100%;' href='../out.php?i={{ $certificate->identifier }}'>
                                        <img style='box-shadow: 0 0 15px 1px #bbb' class='img-responsive center-block' src='{{ $icon_link }}'>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if (count($badge_completed) > 0)
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
    @endif


@if ($uid == $id)
    <div class="row">
        <div class="col-xs-12">
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
    </div>
@endif

@endsection
