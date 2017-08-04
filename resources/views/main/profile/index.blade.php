@extends('layouts.default')

@push('head_scripts')
@endpush

@section('content')

    {!! $action_bar !!}

    <div class="row">
        <div class="col-xs-12">
            <div class='panel panel-default'>
                <div class='panel-body'>
                    <div id='pers_info' class='row'>
                        <div class='col-xs-12 col-sm-2'>
                            <div id='profile-avatar'>{!! $profile_img !!}</div>
                        </div>
                        <div class='col-xs-12 col-sm-10 profile-pers-info'>
                            <div class='row profile-pers-info-name'>
                                <div class='col-xs-12'>
                                    <div>{{ $userdata->givenname }} {{ $userdata->surname }}</div>
                                    <div class='not_visible'>({{ $userdata->username }})</div>
                                </div>
                            </div>
                            @if (get_config('personal_blog'))
                                <div class='row'>
                                    <div class='col-xs-12'>
                                        <div>
                                            <a href='{{ $urlServer }}modules/blog/index.php?user_id={{ $id }}&token={{ token_generate("personal_blog" . $id) }}'>{{ trans('langUserBlog') }}</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if (get_config('eportfolio_enable'))
                                <div class='row'>
                                    <div class='col-xs-12'>
                                        <div>
                                            <a href='{{ $urlServer }}/main/eportfolio/index.php?id={{ $id }}&token={{ token_generate("eportfolio" . $id) }}'>{{ trans('langUserePortfolio') }}</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                                <div class='row'>
                                    <div class='col-xs-6'>
                                        <h4>{{ trans('langProfilePersInfo') }}</h4>
                                        <div class='profile-pers-info'>
                                            <span class='tag'>{{ trans('langEmail') }} :</span>
                                        @if (!empty($userdata->email) and allow_access($userdata->email_public))
                                            <span class='tag-value'>{!! mailto($userdata->email) !!}</span>
                                         @else
                                            <span class='tag-value not_visible'> - {{ trans('langProfileNotAvailable') }} - </span>
                                        @endif
                                        </div>
                                        <div class='profile-pers-info'>
                                            <span class='tag'>{{ trans('langPhone') }} :</span>
                                        @if (!empty($userdata->phone) and allow_access($userdata->phone_public))
                                            <span class='tag-value'>{{ $userdata->phone }}</span>
                                        @else
                                            <span class='tag-value not_visible'> - {{ trans('langProfileNotAvailable') }} - </span>
                                        @endif
                                        </div>
                                        <div class='profile-pers-info'>
                                            <span class='tag'>{{ trans('langStatus') }} :</span>
                                        @if (!empty($userdata->status))
                                            <span class='tag-value'>{{ $userdata->status==1 ? trans('langTeacher'): trans('langStudent') }}</span>
                                        @else
                                            <span class='tag-value not_visible'> - {{ trans('langProfileNotAvailable') }} - </span>
                                        @endif
                                        </div>
                                        <div class='profile-pers-info-data'>
                                            <span class='tag'>{{ trans('langAm') }} :</span>
                                        @if (!empty($userdata->am) and allow_access($userdata->am_public))
                                            <span class='tag-value'>{{ $userdata->am }}</span>
                                        @else
                                            <span class='tag-value not_visible'> - {{ trans('langProfileNotAvailable') }} - </span>
                                        @endif
                                        </div>
                                    @if( $id == $uid && !empty($extAuthList) )
                                        <div>
                                            @foreach( $extAuthList as $item )
                                                <span class='tag'>{{ trans('langProviderConnectWith') }} : </span>
                                                <span class='tag-value'><img src='{{ $themeimg }}/{{ $item->auth_name }}.png' alt=''> {{ $authFullName[$item->auth_id] }}</span><br>
                                            @endforeach
                                        </div>
                                    @endif
                                    </div>
                                </div>
                            </div>
                    </div>
                @if (!empty($userdata->description))
                    <div id='profile-about-me' class='row'>
                        <div class='col-xs-12 col-md-10 col-md-offset-2 profile-pers-info'>
                            <h4>{{ trans('langProfileAboutMe') }}</h4>
                            <div>
                                {{ standard_text_escape($userdata->description) }}
                            </div>
                        </div>
                    </div>
                @endif
                    <div id='profile-departments' class='row'>
                        <div class='col-xs-12 col-md-10 col-md-offset-2 profile-pers-info'>
                            <div>
                                <span class='tag'>{{ trans('langFaculty') }} : </span>
                                @foreach ($user->getDepartmentIds($id) as $i=>$dep)
                                    {!! $tree->getFullPath($dep) !!}
                                    @if($i+1 < count($user->getDepartmentIds($id)))
                                        <br/>
                                    @endif
                                @endforeach
                            </div>
                            <div>
                                <span class='tag'>{{ trans('langProfileMemberSince') }} : </span><span class='tag-value'>{{ $userdata->registered_at }}</span>
                            </div>
                        </div>
                    </div>
                    {!! render_profile_fields_content(array('user_id' => $id)) !!}
                </div>
            </div>
        </div>
    </div>
    
    
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
                        <button class="btn btn-primary btn-sm pull-right">Button</button>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                        <div class="row">
                            <div class="col-xs-4">
                                <div id='profile-avatar'>{!! $profile_img !!}</div>
                            </div>
                            <div class="col-xs8">
                                <div class="profile-name">{{ $userdata->givenname }} {{ $userdata->surname }}</div>
                                <div class='not_visible'><strong>{{ $userdata->username }}</strong></div>
                            </div>
                        </div>
                        </div>
                        <div class="col-sm-6">

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="profile-content-panel">
                                <div class="profile-content-panel-title">
                                    Personal Info
                                </div>
                                <div class="profile-content-panel-text">
                                    <div style="line-height:26px;"><span style="font-weight: bold; color: #888;">asdasdf</span>: adsfasdfasdf</div>
                                    <div style="line-height:26px;"><span style="font-weight: bold; color: #888;">asdasdf</span>: adsfasdfasdf</div>
                                    <div style="line-height:26px;"><span style="font-weight: bold; color: #888;">asdasdf</span>: adsfasdfasdf</div>
                                    <div style="line-height:26px;"><span style="font-weight: bold; color: #888;">asdasdf</span>: adsfasdfasdf</div>
                                    <div style="line-height:26px;"><span style="font-weight: bold; color: #888;">asdasdf</span>: adsfasdfasdf</div>
                                    <div style="line-height:26px;"><span style="font-weight: bold; color: #888;">asdasdf</span>: adsfasdfasdf</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="profile-content-panel">
                                <div class="profile-content-panel-title">
                                    Description
                                </div>
                                <div class="profile-content-panel-text">
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean vel venenatis odio. Sed condimentum cursus quam in mattis. Aliquam ut aliquam erat. Aliquam luctus lacinia auctor. Curabitur blandit augue id quam blandit, convallis aliquet magna vulputate. Sed nec urna lacus. Morbi sodales orci eget lacus imperdiet, non egestas justo lobortis. Praesent eget molestie eros. Proin euismod, augue id auctor iaculis, elit justo mattis nisl, tempor tincidunt diam dolor a lorem. Ve</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="profile-content-panel-title">
                                Delete Account
                            </div>
                            <div class="profile-content-panel-text">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean vel venenatis odio. Sed condimentum
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="center-block" style="display: inline-block; background-color: #aa9933;  margin-top: 50px;  border-radius: 3px; "><p style="padding: 15px 40px;" class="text-center">DELETE ACCOUNT</p></div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

@endsection