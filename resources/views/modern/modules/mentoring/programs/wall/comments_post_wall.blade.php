
@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row m-auto">

                    @if($isCommonGroup == 1)
                        <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">{{ trans('langMyPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php">{!! show_mentoring_program_title($mentoring_program_code) !!}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/select_group.php">{{ trans('langMentoringSpace')}}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/group_space.php?space_group_id={!! getInDirectReference($group_id) !!}">{!! show_mentoring_program_group_name($group_id) !!}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/wall/my_doc_wall.php?group_id={!! getInDirectReference($group_id) !!}&wall">{{ trans('langWall') }}&nbsp({!! show_mentoring_program_group_name($group_id) !!})</a></li>
                                <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                            </ol>
                        </nav>
                    @else
                        <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">{{ trans('langMyPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php">{!! show_mentoring_program_title($mentoring_program_code) !!}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/select_group.php">{{ trans('langMentoringSpace')}}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/index.php">{{ trans('langGroupMentorsMentees') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/group_space.php?space_group_id={!! getInDirectReference($group_id) !!}">{!! show_mentoring_program_group_name($group_id) !!}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/wall/my_doc_wall.php?group_id={!! getInDirectReference($group_id) !!}&wall">{{ trans('langWall') }}&nbsp({!! show_mentoring_program_group_name($group_id) !!})</a></li>
                                <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                            </ol>
                        </nav>
                    @endif

                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4 ps-3 pe-3'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto ps-3 pe-3'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoGroupWallCommentsText')!!}</p>
                        </div>
                    </div>
                    
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
                   
                    {!! $action_bar !!}

                    @if($AddCommentContinue)
                        <div class='col-12'>
                            <div class="panel panel-default rounded-2">
                                <div class="panel-heading bg-white rounded-2 border-0">
                                    <span class="panel-title">
                                        <a class="media-left p-0" href="{{ $urlServer }}modules/mentoring/profile/user_profile.php?user_id={!! getInDirectReference($postUser) !!}&token={{ $token }}">
                                            {!! profile_image($postUser, IMAGESIZE_SMALL, 'img-circle') !!}
                                        </a>
                                    </span>
                                </div>

                                <div class="panel-body rounded-2 bg-white">
                                    
                                    @if(count($post_details) > 0)
                                        <div class="margin-top-thin" style="padding:20px">
                                            @foreach($post_details as $p)
                                                @if(empty($p->extvideo))
                                                    @php 
                                                        $shared = trans('langWallSharedPost');
                                                        $extvideo_block = '';
                                                    @endphp
                                                @else
                                                    @php 
                                                        $shared = trans('langWallSharedVideo');
                                                        $extvideo_embed = MentoringExtVideoUrlParser::get_embed_url($p->extvideo);
                                                    @endphp
                                                    @if($extvideo_embed[0] == 'youtube')
                                                        <div class="video_status">
                                                            <iframe  scrolling="no" width="445" height="250" src="{!! $extvideo_embed[1] !!}" frameborder="0" allowfullscreen></iframe>
                                                        </div>
                                                    @endif

                                                    @if($extvideo_embed[0] == 'vimeo')
                                                        <div class="video_status">
                                                            <iframe  scrolling="no" width="445" height="250" src="{!! $extvideo_embed[1] !!}" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                                        </div>
                                                    @endif
                                                @endif

                                            <small>{{ trans('langWallUser') }}&nbsp{!! mentoring_display_user($postUser,$token) !!}&nbsp{{ $shared }}</small>
                                            <div class="userContent control-label-notes">{!! nl2br(standard_text_escape($p->content)) !!}</div>
                                            @endforeach
                                        </div>
                                        {!! show_resources($post_id) !!} 
                                        <div class='col-12 mt-4'>
                                            <p class='fs-6 blackBlueText TextBold mb-2'>{{ trans('langComments')}}({{ $countPosts }})</p>
                                            @if(count($allCommentForCurrentPost) > 0)
                                                @foreach($allCommentForCurrentPost as $c)
                                                    <div class='col-12 d-flex justify-content-start align-items-center mb-2'>
                                                        {!! profile_image($c->user_id, IMAGESIZE_SMALL, 'img-circle') !!}&nbsp
                                                        <small>{{ trans('langWallUser') }}&nbsp{!! mentoring_display_user($c->user_id,'') !!}&nbsp{{ trans('langAddAcomment') }}&nbsp<span class='TextSemiBold'>({!! format_locale_date(strtotime($c->time))!!})</span></small>
                                                    </div>
                                                    <div class='col-12 ps-5 pe-5 mb-3'><span class='TextBold BlackBlueText small-text'>{{ $c->content }}</span></div>
                                                    <div class='col-12 d-flex justify-content-end align-items-start'>
                                                        @php $postBelongToUser = Database::get()->querySingle("SELECT user_id FROM mentoring_wall_post WHERE id = ?d",$c->rid)->user_id; @endphp
                                                        @if($c->user_id == $uid or $postBelongToUser ==  $uid or $is_editor_current_group or $is_editor_mentoring_program or $is_admin)

                                                            @if($c->user_id == $uid)
                                                            <button class='btn ms-e rounded-2'
                                                                data-bs-toggle='modal' data-bs-target='#UpdateComment{{ $c->id }}'><span class='fa fa-edit blackBlueText fs-5'></span>
                                                            </button>
                                                            @endif


                                                            <button class='btn rounded-2'
                                                                data-bs-toggle='modal' data-bs-target='#DeleteComment{{ $c->id }}'><span class='fa fa-trash fs-5 text-danger'></span>
                                                            </button>
                                                            

                                                            <div class="modal fade" id="UpdateComment{{ $c->id }}" tabindex="-1" aria-labelledby="UpdateCommentLabel{{ $c->id }}" aria-hidden="true">
                                                                <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?group_id={!! getInDirectReference($group_id) !!}">
                                                                    <div class="modal-dialog modal-md modal-primary">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="UpdateCommentLabel{{ $c->id }}">{!! trans('langChangeComment') !!}</h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <label class='control-label-notes'>{{ trans('langComment')}}</label>
                                                                                <input type='text' style='height:25px;' class='w-100 p-3 mb-2 rounded-2' name='contentComment' value='{{ $c->content }}' required>
                                                                                <input type='hidden' name='comment_id' value='{{ $c->id }}'>
                                                                                <input type='hidden' name='fromUser' value='{{ $c->user_id }}'>


                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                                <button type='submit' class="btn btn-primary small-text rounded-2" name="updateComment">
                                                                                    {{ trans('langSubmit') }}
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>

                                                            <div class="modal fade" id="DeleteComment{{ $c->id }}" tabindex="-1" aria-labelledby="DeleteCommentLabel{{ $c->id }}" aria-hidden="true">
                                                                <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?group_id={!! getInDirectReference($group_id) !!}">
                                                                    <div class="modal-dialog modal-md modal-danger">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="DeleteCommentLabel{{ $c->id }}">{!! trans('langdeleteComment') !!}</h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                {{ trans('langContinueChangeComment') }}
                                                                                <input type='hidden' name='comment_id' value='{{ $c->id }}'>

                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                                <button type='submit' class="btn btn-danger small-text rounded-2" name="deleteComment">
                                                                                    {{ trans('langSubmit') }}
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>

                                                        @endif
                                                    </div>
                                                    <div class='mb-4 mt-2' style='height:1px; background-color:#e8e8e8;'></div>
                                                @endforeach
                                            @endif
                                            <div class="form-wrapper form-edit rounded-2 bg-transparent">
                                                <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?group_id={!! getInDirectReference($group_id) !!}">
                                                    <textarea class='p-3 mb-2 rounded-2 SolidPanel' placeholder="{{ trans('langAddComment') }}" name='contentComment' required></textarea>
                                                    <input type='hidden' name='post_id' value='{{ $post_id }}'>
                                                    <input type='hidden' name='fromUser' value='{{ $uid }}'>
                                                    <input type='submit' name='submitComment' class='btn submitAdminBtn' value="{{ trans('langSubmit') }}">
                                                </form>
                                            </div>
                                            
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                        </div>
                    @endif
                    
                    
                

        </div>
      
    </div>
</div>

<script>

    $('.showProgramsBtn').on('click',function(){
        localStorage.setItem("MenuMentoring","program");
    });
                  
</script>
@endsection