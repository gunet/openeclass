
@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row rowMargin">

                    @if($isCommonGroup == 1)
                        <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">{{ trans('langMyPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php">{!! show_mentoring_program_title($mentoring_program_code) !!}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/select_group.php">{{ trans('langMentoringSpace')}}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/group_space.php?space_group_id={!! getInDirectReference($group_id) !!}">{!! show_mentoring_program_group_name($group_id) !!}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/forum_group.php?forum_group_id={!! getInDirectReference($group_id) !!}">{{ trans('langForum') }}&nbsp({!! show_mentoring_program_group_name($group_id) !!})</a></li>
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
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/forum_group.php?forum_group_id={!! getInDirectReference($group_id) !!}">{{ trans('langForum')}}(&nbsp{!! show_mentoring_program_group_name($group_id) !!})</a></li>
                                <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                            </ol>
                        </nav>
                    @endif

                    @include('modules.mentoring.common.common_current_title')
                    
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
                   
                    <div class='col-3'>
                        {!! $action_bar !!}
                    </div>
                    <div class='col-9 d-flex justify-content-end align-items-start'>
                        <button class="btn btn-outline-success btn-sm small-text rounded-2 TextSemiBold text-uppercase"
                            data-bs-toggle="modal" data-bs-target="#AnswerTopicModal" >
                            <span class='fa fa-plus'></span><span class='hidden-xs-mentoring'>&nbsp{{ trans('langAnswerTopicMentoringGroup') }}</span>
                        </button>

                        @if($is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin)
                            <button class="btn btn-outline-danger btn-sm small-text ms-2 rounded-2 TextSemiBold text-uppercase"
                                data-bs-toggle="modal" data-bs-target="#DeleteAllMessagesTopicModal" >
                                <span class='fa fa-times'></span><span class='hidden-xs-mentoring'>&nbsp{{ trans('langDeleteAllMessagesOfTopic') }}</span>
                            </button>
                        @endif

                        <div class="modal fade" id="AnswerTopicModal" tabindex="-1" aria-labelledby="AnswerTopicModalLabel" aria-hidden="true">
                            <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?group_id={!! getInDirectReference($group_id) !!}&topic_id={!! getInDirectReference($topic_id) !!}&forum_id={!! getInDirectReference($forum_id) !!}" enctype="multipart/form-data">
                                <div class="modal-dialog modal-md modal-success">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="AnswerTopicModalLabel">{{ trans('langAnswerTopicMentoringGroup') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">

                                            <input type='hidden' name='parent_post' value='0'>

                                            <div class='col-12'><p class='TextBold text-end'>(<span class='text-danger'>*</span>) {{trans('langCPFFieldRequired')}}</p></div>

                                            <div class='form-group'>
                                                <label for='message' class='col-sm-6 control-label-notes'>{{ trans('langBodyMessage') }}<span class='text-danger'>*</span></label>
                                                <div class='col-sm-12'>
                                                    {!! rich_text_editor('message', 15, 70, '') !!}
                                                </div>
                                            </div>

                                            <div class='form-group mt-4'>
                                                <label for='topic_file' class='col-sm-6 control-label-notes'>{{ trans('langAttachedFile') }}</label>
                                                <div class='col-sm-12'>
                                                    <input type='file' name='topic_file' id='topic_file' size='35'>
                                                    {!! fileSizeHidenInput() !!}
                                                </div>
                                            </div>

                                        </div>
                                        <div class="modal-footer">
                                            <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                            <button type='submit' class="btn btn-success small-text rounded-2" name="create_answer" value="answer">
                                                {{ trans('langAnswer') }}
                                            </button>

                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        @if($is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin)
                            <div class="modal fade" id="DeleteAllMessagesTopicModal" tabindex="-1" aria-labelledby="DeleteAllMessagesTopicModalLabel" aria-hidden="true">
                                <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?group_id={!! getInDirectReference($group_id) !!}&topic_id={!! getInDirectReference($topic_id) !!}&forum_id={!! getInDirectReference($forum_id) !!}">
                                    <div class="modal-dialog modal-md modal-danger">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="DeleteAllMessagesTopicModalLabel">{{ trans('langDeleteAllMessagesOfTopic') }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                 {{ trans('langDeleteAllMessagesOfTopicQuestion') }}
                                            </div>
                                            <div class="modal-footer">
                                                <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                <button type='submit' class="btn btn-danger small-text rounded-2" name="delete_all_messages_of_topic">
                                                    {{ trans('langDelete') }}
                                                </button>

                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                    
                    @if(count($answers_topic) > 0)
                        <div class='col-12' id='forum_col_load'>
                            <input type='hidden' id='group_id_load' value='{!! getInDirectReference($group_id) !!}'>
                            <input type='hidden' id='topic_id_load' value='{!! getInDirectReference($topic_id) !!}'>
                            <input type='hidden' id='forum_id_load' value='{!! getInDirectReference($forum_id) !!}'>
                            <div class='panel panel-default rounded-2'>
                                <div class='panel-body rounded-2 bg-white'>
                                    @php $counter = 0; @endphp
                                    @foreach($answers_topic as $answer)
                                        @php 
                                            $profile_img = profile_image($answer->poster_id, IMAGESIZE_SMALL, 'img-responsive img-circle img-profile');
                                            $user_name = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$answer->poster_id)->givenname;        
                                            $user_surname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$answer->poster_id)->surname;        
                                        @endphp
                                        <div class='col-12'>
                                            <div class='row'>
                                                <div class='col-12 d-md-flex justify-content-md-start align-items-md-center'>
                                                    {!! $profile_img !!}&nbsp
                                                    <span class='TextBold blackBlueText'>{!! $user_name !!}&nbsp{!! $user_surname !!}</span>
                                                    &nbsp{{ trans('langSendMessageTopicMentoring')}}
                                                    &nbsp<span class='TextBold blackBlueText small-text'>{!! format_locale_date(strtotime($answer->post_time)) !!}</span>
                                                    @if($counter == (count($answers_topic) - 1))
                                                        &nbsp<span class='badge rounded-pill bg-info'>{{ trans('langLastMsg') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-12 mt-3'>
                                            {!! $answer->post_text !!}
                                        </div>
                                        <div class='col-12 mt-3'>
                                            <div class='row ms-0'>
                                                <div class='col-md-4 col-12 d-flex justify-conten-start mt-1 ps-0'>
                                                    @if(!empty($answer->topic_filepath))
                                                        <a href="{{ $_SERVER['SCRIPT_NAME'] }}?group_id={!! getInDirectReference($group_id) !!}&topic_id={!! getInDirectReference($topic_id) !!}&forum_id={!! getInDirectReference($forum_id) !!}&get={{ getInDirectReference($answer->id) }}">
                                                            {{ $answer->topic_filename }}&nbsp<span class='fa fa-download'></span>
                                                        </a>
                                                    @endif
                                                </div>
                                                <div class='col-md-8 col-12 d-flex justify-content-end align-items-end pe-0'>
                                                    @if($answer->poster_id == $uid or $is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin)

                                                        @if($answer->poster_id == $uid)
                                                        <button class="btn blackBlueText"
                                                            data-bs-toggle="modal" data-bs-target="#EditTopicModal{{ $answer->id }}">
                                                            <span class='fa fa-edit fs-5'></span>
                                                        </button>
                                                        @endif

                                                        <button class="btn text-danger"
                                                            data-bs-toggle="modal" data-bs-target="#DeleteTopicModal{{ $answer->id }}">
                                                            <span class='fa fa-trash fs-5'></span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        @if($answer->poster_id == $uid or $is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin)
                                            <div class="modal fade" id="EditTopicModal{{ $answer->id }}" tabindex="-1" aria-labelledby="EditTopicModalLabel{{ $answer->id }}" aria-hidden="true">
                                                <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?group_id={!! getInDirectReference($group_id) !!}&topic_id={!! getInDirectReference($topic_id) !!}&forum_id={!! getInDirectReference($forum_id) !!}" enctype="multipart/form-data">
                                                    <div class="modal-dialog modal-md modal-primary">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="EditTopicModalLabel{{ $answer->id }}">{{ trans('langEditTopicMentoring') }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">

                                                                <input type='hidden' name='editt_post' value='0'>
                                                                <input type='hidden' name='post_id' value='{{ $answer->id }}'>
                                                                <input type='hidden' name='poster_id' value='{{ $answer->poster_id }}'>

                                                                <div class='col-12'><p class='TextBold text-end'>(<span class='text-danger'>*</span>) {{trans('langCPFFieldRequired')}}</p></div>

                                                                <div class='form-group'>
                                                                    <label for='message' class='col-sm-6 control-label-notes'>{{ trans('langBodyMessage') }}<span class='text-danger'>*</span></label>
                                                                    <div class='col-sm-12'>
                                                                        {!! rich_text_editor('message', 15, 70, $answer->post_text) !!}
                                                                    </div>
                                                                </div>

                                                                <div class='form-group mt-4'>
                                                                    <label for='topic_file' class='col-sm-6 control-label-notes'>{{ trans('langAttachedFile') }}</label>
                                                                    <div class='col-sm-12'>
                                                                       
                                                                        <p class='small-text'>{{ trans('langOldValue') }}:&nbsp
                                                                            @if(!empty($answer->topic_filename))
                                                                                <span class='ms-1 text-primary TextBold'>{{ $answer->topic_filename }}</span>&nbsp
                                                                                <a href="{{ $urlAppend }}modules/mentoring/programs/group/view_topic.php?group_id={!! getInDirectReference($group_id) !!}&topic_id={!! getInDirectReference($topic_id) !!}&forum_id={!! getInDirectReference($forum_id) !!}&del_fileid={!! getInDirectReference($answer->id) !!}">
                                                                                    <span class='fa fa-times text-danger'></span>
                                                                                </a>
                                                                            @else
                                                                                <input type='file' name='topic_file' id='topic_file' size='35'>
                                                                                {!! fileSizeHidenInput() !!}
                                                                            @endif
                                                                        </p>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="modal-footer">
                                                                <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                <button type='submit' class="btn btn-primary small-text rounded-2" name="edit_topic">
                                                                    {{ trans('langSubmit') }}
                                                                </button>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>


                                            <div class="modal fade" id="DeleteTopicModal{{ $answer->id }}" tabindex="-1" aria-labelledby="DeleteTopicModalLabel{{ $answer->id }}" aria-hidden="true">
                                                <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?group_id={!! getInDirectReference($group_id) !!}&topic_id={!! getInDirectReference($topic_id) !!}&forum_id={!! getInDirectReference($forum_id) !!}">
                                                    <div class="modal-dialog modal-md modal-danger">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="DeleteTopicModalLabel{{ $answer->id }}">{{ trans('langDeleteTopicMentoring') }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">

                                                               
                                                                <input type='hidden' name='post_id' value='{{ $answer->id }}'>
                                                                <input type='hidden' name='poster_id' value='{{ $answer->poster_id }}'>
                                                                
                                                                {{ trans('langDeleteTopicMentoringMsg') }}
                                                                

                                                            </div>
                                                            <div class="modal-footer">
                                                                <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                <button type='submit' class="btn btn-danger small-text rounded-2" name="delete_topic">
                                                                    {{ trans('langDelete') }}
                                                                </button>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        @endif

                                        @if($counter < count($answers_topic) - 1)<hr>@endif
                                        @php $counter++; @endphp
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class='col-12'>
                            <div class='col-12 bg-white p-3 rounded-2 solidPanel'><div class='alert alert-warning rounded-2'>{{ trans('langNoAnswer') }}</div></div>
                        </div>
                    @endif
                    
                  
                  
                

        </div>
      
    </div>
</div>

<script>
    $(document).ready(function () {

        var group_id = $('#group_id_load').val();
        var topic_id = $('#topic_id_load').val();
        var forum_id = $('#forum_id_load').val();
        var str = "view_topic.php?group_id="+group_id+"&topic_id="+topic_id+"&forum_id="+forum_id;

        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });

    });

</script>

@endsection






