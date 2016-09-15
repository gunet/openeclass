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
                                            <a href='{{ $urlServer }}modules/blog/index.php?user_id={{ $id }}'>{{ trans('langUserBlog') }}</a>
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
                                @foreach ($user->getDepartmentIds($id) as $i=>$dep) {
                                {{ $i+1 }}
                                {{ $tree->getFullPath($dep) }} {{ ($i < count($user->getDepartmentIds($id))) ? '<br/>' : '' }}
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

@endsection