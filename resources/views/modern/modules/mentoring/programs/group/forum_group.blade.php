
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
                                <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                            </ol>
                        </nav>
                    @endif

                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoGroupForumText')!!}</p>
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
                   
                    <div class='col-6'>
                        {!! $action_bar !!}
                    </div>
                    <div class='col-6 d-flex justify-content-end align-items-start'>

                        <button class="btn submitAdminBtnDefault"
                            data-bs-toggle="modal" data-bs-target="#CreateTopicModal" >
                            <span class='fa fa-plus'></span><span class='hidden-xs-mentoring TextBold'>&nbsp{{ trans('langCreateTopicMentoringGroup') }}</span>
                        </button>

                        <div class="modal fade" id="CreateTopicModal" tabindex="-1" aria-labelledby="CreateTopicModalLabel" aria-hidden="true">
                            <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?forum_group_id={!! getInDirectReference($group_id) !!}" enctype="multipart/form-data">
                                <div class="modal-dialog modal-md modal-success">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="CreateTopicModalLabel">{{ trans('langCreateTopicMentoringGroup') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">

                                            <div class='col-12'><p class='TextBold text-end'>(<span class='text-danger'>*</span>) {{trans('langCPFFieldRequired')}}</p></div>

                                            <div class='form-group'>
                                                <div for='subject' class='col-12 control-label-notes mb-1'>{{ trans('langTitle') }}<span class='text-danger'>*</span></div>
                                                <div class='col-12'>
                                                    <input name='subject' id='subject' type='text' class='form-control rounded-2 bgEclass' placeholder="{{ trans('langTitle') }}">
                                                </div>
                                            </div>

                                            <div class='form-group mt-4'>
                                                <label for='message' class='col-sm-12 control-label-notes mb-1'>{{ trans('langDescrMentoringProgram') }}<span class='text-danger'>*</span></label>
                                                <div class='col-sm-12'>
                                                    {!! rich_text_editor('message', 4, 20, '') !!}
                                                </div>
                                            </div>

                                            <div class='form-group mt-4'>
                                                <label for='file_upload_topic' class='col-sm-12 control-label-notes mb-1'>{{ trans('langPathUploadFile') }}</label>
                                                <div class='col-sm-12'><input id='file_upload_topic' type='file' name='topic_file'></div>
                                            </div>

                                            <input type='hidden' name='empty_message_topic'>

                                        </div>
                                        <div class="modal-footer">
                                            <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                            <button type='submit' class="btn successAdminBtn" name="create_topic">
                                                {{ trans('langCreate') }}
                                            </button>

                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                                        
                    @if(count($all_topics) > 0)
                       <div class='col-12 mt-3'>
                           
                               <table class='table-default rounded-2' id="table_forums">
                                    <thead>
                                        <tr class='list-header'>
                                            <th>{{ trans('langSubject')}}</th>
                                            <th>{{ trans('langCreator')}}</th>
                                            <th class='text-center'>{{ trans('langAnswers') }}</th>
                                            <th>{{ trans('langLastMsg') }}</th>
                                            <th class='text-center'><span class='fa fa-cogs'></span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($all_topics as $topic)
                                        <tr>
                                            <td>
                                                    <a class='d-flex justify-content-start align-items-start' 
                                                    href='{{ $urlAppend }}modules/mentoring/programs/group/view_topic.php?group_id={!! getInDirectReference($group_id) !!}&topic_id={!! getInDirectReference($topic->id) !!}&forum_id={!! getInDirectReference($topic->forum_id) !!}'>
                                                        <span class='fa fa-comment mt-1'></span>&nbsp{{ $topic->title }}
                                                    </a>
                                                </td>
                                            <td>
                                            
                                                @php 
                                                        $user = Database::get()->queryArray("SELECT givenname,surname FROM user WHERE id = ?d",$topic->topic_poster_id); 
                                                        $profile_img = profile_image($topic->topic_poster_id, IMAGESIZE_SMALL, 'img-responsive img-circle img-profile'); 
                                                    
                                                @endphp
                                                
                                                <div class='d-md-flex justify-content-md-start align-items-md-center'>
                                                        {!! $profile_img !!}
                                                        @foreach($user as $u)
                                                            {{ $u->givenname }}&nbsp{{ $u->surname }}
                                                        @endforeach
                                                </div>
                                                
                                            </td>
                                            <td class='text-center'> 
                                                {{ $topic->num_replies }}
                                            </td>
                                            <td>
                                                @php 
                                                    $user = Database::get()->queryArray("SELECT id,givenname,surname FROM user 
                                                                                            WHERE id IN (SELECT poster_id FROM mentoring_forum_post WHERE topic_id = ?d AND id = ?d)",$topic->id,$topic->last_post_id);
                                                @endphp
                                                <div class='d-md-flex justify-content-md-start align-items-md-center'>
                                                    
                                                        @foreach($user as $u) 
                                                            @php $profile_img = profile_image($u->id, IMAGESIZE_SMALL, 'img-responsive img-circle img-profile'); @endphp
                                                            {!! $profile_img !!}
                                                            {{ $u->givenname }}&nbsp{{ $u->surname }}
                                                        @endforeach
                                                </div>
                                            </td>
                                            @if($topic->topic_poster_id == $uid or $is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin)
                                                    <td class='d-flex justify-content-center aling-items-center gap-2'>
                                                            
                                                        <button class="btn"
                                                            data-bs-toggle="modal" data-bs-target="#EditTopicModal{{ $topic->id }}">
                                                            <span class='fa-solid fa-edit Primary-500-cl fa-lg'></span>
                                                        </button>

                                                        <button class="btn"
                                                            data-bs-toggle="modal" data-bs-target="#DeleteTopicModal{{ $topic->id }}">
                                                            <span class='fa-solid fa-xmark Accent-200-cl fa-lg'></span>
                                                        </button>
                                                            
                                                    </td>
                                            @else
                                                 <td class='text-center'> -- </td>
                                            @endif
                                        </tr>

                                        @if($topic->topic_poster_id == $uid or $is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin)
                                                <div class="modal fade" id="EditTopicModal{{ $topic->id }}" tabindex="-1" aria-labelledby="EditTopicModalLabel{{ $topic->id }}" aria-hidden="true">
                                                    <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?forum_group_id={!! getInDirectReference($group_id) !!}">
                                                        <div class="modal-dialog modal-md modal-primary">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="EditTopicModalLabel{{ $topic->id }}">{{ trans('langEditTopicMentoring') }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">

                                                                    <div class='col-12'><p class='TextBold text-end'>(<span class='text-danger'>*</span>) {{trans('langCPFFieldRequired')}}</p></div>

                                                                    <div class='form-group'>
                                                                        <div for='subject' class='col-12 control-label-notes mb-1'>{{ trans('langTitle') }}<span class='text-danger'>*</span></div>
                                                                        <div class='col-12'>
                                                                            <input name='subject' id='subject' type='text' class='form-control rounded-2 bgEclass' value="{{ $topic->title }}">
                                                                        </div>
                                                                    </div>

                                                                    <input type='hidden' name='empty_subject_topic'>
                                                                    <input type='hidden' name='topic_id' value="{{ $topic->id }}">

                                                                </div>
                                                                <div class="modal-footer">
                                                                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                    <button type='submit' class="btn submitAdminBtnDefault" name="edit_topic">
                                                                        {{ trans('langSubmit') }}
                                                                    </button>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>


                                                <div class="modal fade" id="DeleteTopicModal{{ $topic->id }}" tabindex="-1" aria-labelledby="DeleteTopicModalLabel{{ $topic->id }}" aria-hidden="true">
                                                    <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?forum_group_id={!! getInDirectReference($group_id) !!}">
                                                        <div class="modal-dialog modal-md modal-danger">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="DeleteTopicModalLabel{{ $topic->id }}">{{ trans('langDeleteTopicMentoring') }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">

                                                                    <input type='hidden' name='topic_id' value='{{ $topic->id }}'>
                                                                    <input type='hidden' name='topic_poster_id' value='{{ $topic->topic_poster_id }}'>
                                                                    {!! trans('langDeleteSubjectMentoringMsg') !!}
                                                                    

                                                                </div>
                                                                <div class="modal-footer">
                                                                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                    <button type='submit' class="btn deleteAdminBtn" name="delete_topic">
                                                                        {{ trans('langDelete') }}
                                                                    </button>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>


                                                
                                            @endif

                                        @endforeach
                                    </tbody>
                               </table>
                           
                       </div>
                    @else
                       
                            <div class='col-12 mt-4'>
                                <div class='alert alert-warning'>
                                <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                <span>{{ trans('langNoForumMentoring') }}</span></div>
                            </div>
                      
                    @endif

                        
                        
                  
                  
                

        </div>
      
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {

        $('#table_forums').DataTable();

        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });
    } );
</script>

@endsection






