@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col_maincontent_active">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

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
                            
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    
                    <!-- Regarding deliverable information -->
                    <div class='col-12'>
                        @foreach($resource_info as $r)
                            <div class="card panelCard border-card-left-default px-lg-4 py-lg-3">
                                <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                    <h3 class='mb-0'>{{ $r->title }}</h3>
                                    @if($is_consultant)
                                        <a class='link-color' href="{{ $urlAppend }}modules/session/edit_resource.php?course={{ $course_code }}&session={{ $sessionID }}&resource_id={{ $resource_id }}">
                                            {{ trans('langModify')}}
                                        </a>
                                    @endif
                                </div>
                                <div class='card-body'>
                                    <ul class='list-group list-group-flush'>
                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='title-default'>{{ trans('langFileName') }}</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    {!! $download_hidden_link !!}
                                                    {!! $link !!}
                                                </div>
                                            </div>
                                        </li>
                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='title-default'>{{ trans('langComments') }}</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    @if(!empty($r->comments))
                                                        {!! $r->comments !!}
                                                    @else
                                                        {{ trans('langNoCommentsAvailable') }}
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='title-default'>{{ trans('langResourceDateCreated') }}</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    {{ format_locale_date(strtotime($r->date), 'short') }}
                                                </div>
                                            </div>
                                        </li>
                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='title-default'>{{ trans('langReferencedObject') }}</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    <div class='d-flex justify-content-start align-items-center gap-4 flex-wrap'>
                                                        @foreach($users_participants as $p)
                                                            <span>{!! participant_name($p) !!}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='title-default'>{{ trans('lampAnalyticsStatus') }}</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    @if($is_criterion_completion)
                                                        {{ trans('langResourceBelongsToSessionPrereq') }}
                                                    @else
                                                        {!! trans('langResourceΝοBelongsToSessionPrereq') !!}
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                        @if($is_consultant && $is_criterion_completion)
                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='title-default'>{{ trans('langDocSender') }}</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    {{ $total_deliverables }}
                                                </div>
                                            </div>
                                        </li>
                                        @endif
                                        @if($is_criterion_completion)
                                            @if($is_consultant && !$upload_doc_for_user)
                                            <li class='list-group-item element'>
                                                <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                    <div class='col-md-3 col-12'>
                                                        <div class='title-default'>{{ trans('langDownloadFile') }}</div>
                                                    </div>
                                                    <div class='col-md-9 col-12 title-default-line-height'>
                                                        <a class='link-color' href="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&session={{ $sessionID }}&resource_id={{ $resource_id }}&file_id={{ $file_id }}&upload_for_user=true">
                                                            {{ trans('langUploadOnBehalfOf') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </li>
                                            @endif
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    </div>



                    <!-- show all deliverable for each user -->
                    @if($is_criterion_completion)
                    <div class="col-12 mt-4 @if($is_consultant && isset($_GET['upload_for_user'])) d-none @endif">
                        <div class="card panelCard border-card-left-default px-lg-4 py-lg-3">
                            <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <h3 class='mb-0'>
                                    @if($is_consultant or $is_course_reviewer)
                                        {{ trans('langDocSender') }}
                                    @else
                                        {{ trans('langMyUploadedFiles') }}
                                    @endif
                                </h3>
                            </div>
                            <div class='card-body'>
                                @if(!$is_consultant && !$is_course_reviewer)
                                    <div class='alert alert-info mt-0'>
                                        <i class='fa-solid fa-circle-info fa-lg'></i>
                                        <span>{{ trans('langInfoForUploadedDeliverable')}}</span>
                                    </div>
                                @endif
                                @if(count($docs) > 0)
                                    <div class='table-responsive mt-0'>
                                        <table class='table-default table-deliverable-comments'>
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('langFileName') }}</th>
                                                    <th>{{ trans('langFrom') }}</th>
                                                    <th>{{ trans('langReferencedObject') }}</th>
                                                    <th>{{ trans('langDate') }}</th>
                                                    @if($is_consultant || $is_course_reviewer)<th class='text-center'>{{ trans('langAlreadyBrowsed')}}</th>@endif
                                                    <th class='text-end'></th>
                                                </tr>
                                                <tr></tr>
                                            </thead>
                                            <tbody>
                                                @foreach($docs as $doc)
                                                    <tr class='tr-deliverable'>
                                                        <td>{!! $doc->link !!}</td>
                                                        <td>{{ $doc->creator }}</td>
                                                        <td>{{ $doc->refers_to }}</td>
                                                        <td>{{ format_locale_date(strtotime($doc->date), 'short') }}</td>
                                                        @if($is_consultant || $is_course_reviewer)
                                                            <td class='text-center'>
                                                                @if($doc->completed)
                                                                    <i class='fa-solid fa-check fa-lg text-success'></i>
                                                                @else
                                                                    <i class='fa-solid fa-xmark fa-lg text-danger'></i>
                                                                @endif
                                                            </td>
                                                        @endif
                                                        <td class='text-end'>
                                                            @if($is_editor || !$is_course_reviewer)
                                                                {!! 
                                                                    action_button(array(
                                                                        array(
                                                                            'title' => trans('langSubmitCompletion'),
                                                                            'url' => "#",
                                                                            'icon' => 'fa-solid fa-award',
                                                                            'icon-class' => "add-award",
                                                                            'icon-extra' => "data-bs-toggle='modal' data-bs-target='#doUserAward' data-id='{$doc->id}' data-userBadgeCriterionId='{$doc->user_badge_criterion_id}' data-userSender='{$doc->user_sender}'",
                                                                            'show' => ($is_consultant && !$doc->completed)
                                                                        ),
                                                                        array(
                                                                            'title' => trans('langNoSubmitCompletion'),
                                                                            'url' => "#",
                                                                            'icon' => 'fa-solid fa-ban',
                                                                            'icon-class' => "remove-award",
                                                                            'icon-extra' => "data-bs-toggle='modal' data-bs-target='#noUserAward' data-id='{$doc->id}' data-userBadgeCriterionId='{$doc->user_badge_criterion_id}' data-userSender='{$doc->user_sender}'",
                                                                            'show' => ($is_consultant && $doc->completed)
                                                                        ),
                                                                        array(
                                                                            'title' => trans('langAddComment'),
                                                                            'url' => "#",
                                                                            'icon' => 'fa-solid fa-comments',
                                                                            'icon-class' => "add-comments",
                                                                            'icon-extra' => "data-bs-toggle='modal' data-bs-target='#doComments' data-id='{$doc->id}' data-fileTitle='{$doc->fileTitle}' data-fileCreator='{$doc->creator}' data-forUserId='{$doc->user_sender}' data-commentDoc='{$doc->deliverable_comment}'",
                                                                            'show' => $is_consultant
                                                                        ),
                                                                        array(
                                                                            'title' => trans('langDownload'),
                                                                            'url' => $doc->download_url,
                                                                            'icon' => 'fa-download',
                                                                            'icon-class' => 'download-doc'
                                                                        ),
                                                                        array(
                                                                            'title' => trans('langDelete'),
                                                                            'url' => '#',
                                                                            'icon' => 'fa-xmark',
                                                                            'icon-extra' => "data-bs-toggle='modal' data-bs-target='#docDelete' data-id='{$doc->id}'",
                                                                            'icon-class' => 'doc-delete',
                                                                            'show' => $doc->can_delete_file)
                                                                    ))
                                                                !!}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="7">
                                                            <div class='d-flex justify-content-start align-items-start gap-3 flex-wrap'>
                                                                <p class='control-label-notes mt-1'>{{ trans('langCommentsByConsultant') }}:</p>
                                                                @if(!empty($doc->deliverable_comment))
                                                                    {!! $doc->deliverable_comment !!}
                                                                @else
                                                                    {{ trans('langNoCommentsAvailable') }}
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class='alert alert-warning'>
                                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                        <span>{{ trans('langNotExistDeliverables') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif


                    <!-- upload deliverable by simple user or upload a deliverable by consultant for a user -->
                    @if($is_criterion_completion && !$is_course_reviewer)
                        @if(!$is_consultant or $upload_doc_for_user)
                            <div class='d-lg-flex gap-4 mt-4'>
                                <div class='flex-grow-1'>
                                    <div class='alert alert-info'>
                                        <i class='fa-solid fa-circle-info fa-lg'></i>
                                        <span>{{ trans('langInfoUploadExistedDeliverable') }}</span>
                                    </div>
                                    <div class='form-wrapper form-edit rounded'>
                                        <form role='form' class='form-horizontal' action='{{ $urlAppend }}modules/session/resource.php?course={{ $course_code }}&session={{ $sessionID }}' method='post' enctype='multipart/form-data'>
                                            <fieldset>

                                                <input type='hidden' name='id' value='{{ $sessionID }}' />

                                                <div class='form-group'>
                                                    <label for='file-upload' class='col-12 control-label-notes'>{{ trans('langDownloadFile') }}&nbsp;<span class='Accent-200-cl'>(*)</span></label>
                                                    <input id='file-upload' type='file' name='file-upload'/>
                                                </div>

                                                <div class='form-group mt-4'>
                                                    <label for='title' class='col-12 control-label-notes'>{{ trans('langTitle') }}</label>
                                                    <div class='col-12'>
                                                        <input id='title' type='text' name='title' class='form-control'>
                                                    </div>
                                                </div>

                                                <div class='form-group mt-4'>
                                                    <label for='comments' class='col-12 control-label-notes'>{{ trans('langComments') }}</label>
                                                    {!! rich_text_editor('comments', 5, 40, '') !!}
                                                </div>

                                                @if(!$is_consultant)
                                                    <input type='hidden' name='refers_to_resource' value='{{ $file_id }}'>
                                                    <input type='hidden' name='fromUser' value='{{ $uid }}' />
                                                @endif

                                                @if($is_consultant)
                                                    <input type='hidden' name='onBehalfOfUserID' value='1'>
                                                    <input type='hidden' name='refers_to_resource' value='{{ $file_id }}'>
                                                    <label for='onBehalfOfUser' class='col-12 control-label-notes mt-4'>{{ trans('langOnBehalfOfUser') }}</label>
                                                    <select class='form-select' name='fromUser' id='onBehalfOfUser'>
                                                        @foreach($users_participants as $u)
                                                            <option value='{{ $u }}'>{!! participant_name($u) !!}</option>
                                                        @endforeach
                                                    </select>
                                                @endif


                                                <div class='form-group mt-5'>
                                                    <div class='col-12 d-flex justify-content-end aling-items-center'>
                                                        @if($upload_doc_for_user)
                                                            <a class='btn cancelAdminBtn me-2' 
                                                                href="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&session={{ $sessionID }}&resource_id={{ $resource_id }}&file_id={{ $file_id }}">
                                                                {{ trans('langCancel') }}
                                                            </a>
                                                        @endif
                                                        <input class='btn submitAdminBtn' type='submit' name='submit_upload' value="{{ trans('langSubmit') }}">
                                                    </div>
                                                </div>

                                                <input type='hidden' name='for_deliverable' value='{{ $resource_id }}'>
                                                <input type='hidden' name='for_file' value='{{ $file_id }}'>

                                                {!! generate_csrf_token_form_field() !!}   

                                            </fieldset>
                                        </form>
                                    </div>
                                </div>
                                <div class='d-none d-lg-block'>
                                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                                </div>
                            </div>
                        @endif
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<div class='modal fade' id='doUserAward' tabindex='-1' aria-labelledby='doUserAwardLabel' aria-hidden='true'>
    <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&id={{ $sessionID }}&resource_id={{ $resource_id }}&file_id={{ $file_id }}">
        <div class='modal-dialog modal-md'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <div class='modal-title'>
                        <div class='icon-modal-default'><i class='fa-solid fa-award fa-xl Neutral-500-cl'></i></div>
                        <h3 class="modal-title-default text-center mb-0 mt-2" id="doUserAwardLabel">{!! trans('langSubmitCompletion') !!}</h3>
                    </div>
                </div>
                <div class='modal-body text-center'>
                    <input type='hidden' name='document_id' id='document_id_yesAward'>
                    <input type='hidden' name='userBadgeCriterionId' id='userBadgeCriterionId_yesAward'>
                    <input type='hidden' name='userSender' id='userSender_yesAward'>
                    <input type='hidden' name='token' value="{{ $_SESSION['csrf_token'] }}">
                    {!! trans('langContinueToUserAwarded') !!}
                </div>
                <div class='modal-footer d-flex justify-content-center align-items-center'>
                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                    <button type='submit' class="btn submitAdminBtn">
                        {{ trans('langInstallEnd') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class='modal fade' id='noUserAward' tabindex='-1' aria-labelledby='noUserAwardLabel' aria-hidden='true'>
    <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&id={{ $sessionID }}&resource_id={{ $resource_id }}&file_id={{ $file_id }}">
        <div class='modal-dialog modal-md'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <div class='modal-title'>
                        <div class='icon-modal-default'><i class='fa-solid fa-award fa-xl Neutral-500-cl'></i></div>
                        <h3 class="modal-title-default text-center mb-0 mt-2" id="noUserAwardLabel">{!! trans('langNoSubmitCompletion') !!}</h3>
                    </div>
                </div>
                <div class='modal-body text-center'>
                    <input type='hidden' name='document_id' id='document_id_noAward'>
                    <input type='hidden' name='userBadgeCriterionId' id='userBadgeCriterionId_noAward'>
                    <input type='hidden' name='userSender' id='userSender_noAward'>
                    <input type='hidden' name='token' value="{{ $_SESSION['csrf_token'] }}">
                    {!! trans('langContinueToNoSubmiCompletion') !!}
                </div>
                <div class='modal-footer d-flex justify-content-center align-items-center'>
                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                    <button type='submit' class="btn submitAdminBtn">
                        {{ trans('langSubmit') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class='modal fade' id='doComments' tabindex='-1' aria-labelledby='doCommentsLabel' aria-hidden='true'>
    <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&id={{ $sessionID }}&resource_id={{ $resource_id }}&file_id={{ $file_id }}">
        <div class='modal-dialog modal-md'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <div class='modal-title'>
                        <div class='icon-modal-default'><i class='fa-solid fa-comments fa-xl Neutral-500-cl'></i></div>
                        <h3 class="modal-title-default text-center mb-0 mt-2" id="doCommentsLabel">{!! trans('langAddComment') !!}</h3>
                    </div>
                </div>
                <div class='modal-body text-start'>
                    <p class='control-label-notes'>{{ trans('langFileName') }}:&nbsp;<span id='fileTitle'></span></p>
                    <p class='control-label-notes'>{{ trans('langUser') }}:&nbsp;<span id='fileCreator'></span></p>
                    <label for='comment_deliverable' class='control-label-notes mt-4'>{{ trans('langComment') }}</label>
                    <textarea id='comment_deliverable' name='add_comment' placeholder="{{ trans('langTypeOutComment') }}"></textarea>
                    <input type='hidden' name='for_resource_id' value='{{ $file_id }}'>
                    <input type='hidden' name='for_user_id' id='forUserId'>
                    <input type='hidden' name='token' value="{{ $_SESSION['csrf_token'] }}">
                    <div class='alert alert-info'>
                        <i class='fa-solid fa-circle-info fa-lg'></i>
                        <span>{{ trans('langSendEmailWithComments') }}</span>
                    </div>
                </div>
                <div class='modal-footer d-flex justify-content-center align-items-center'>
                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                    <button type='submit' class="btn submitAdminBtn">
                        {{ trans('langSubmit') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class='modal fade' id='docDelete' tabindex='-1' aria-labelledby='docDeleteLabel' aria-hidden='true'>
    <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&id={{ $sessionID }}&resource_id={{ $resource_id }}&file_id={{ $file_id }}">
        <div class='modal-dialog modal-md'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <div class='modal-title'>
                        <div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>
                        <h3 class="modal-title-default text-center mb-0 mt-2" id="docDeleteLabel">{!! trans('langDelete') !!}</h3>
                    </div>
                </div>
                <div class='modal-body text-center'>
                    <input type='hidden' name='delete_resource' id='deleteResource'>
                    {{ trans('langContinueToDelSession') }}
                </div>
                <div class='modal-footer d-flex justify-content-center align-items-center'>
                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                    <button type='submit' class="btn deleteAdminBtn">
                        {{ trans('langDelete') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    $(function() {
        $(document).on('click', '.add-award', function(e){
            e.preventDefault();
            var doc_id = $(this).attr('data-id');
            var user_badge_criterion_id = $(this).attr('data-userBadgeCriterionId');
            var user_sender = $(this).attr('data-userSender');
            document.getElementById("document_id_yesAward").value = doc_id;
            document.getElementById("userBadgeCriterionId_yesAward").value = user_badge_criterion_id;
            document.getElementById("userSender_yesAward").value = user_sender;
        });
        $(document).on('click', '.remove-award', function(e){
            e.preventDefault();
            var doc_id = $(this).attr('data-id');
            var user_badge_criterion_id = $(this).attr('data-userBadgeCriterionId');
            var user_sender = $(this).attr('data-userSender');
            document.getElementById("document_id_noAward").value = doc_id;
            document.getElementById("userBadgeCriterionId_noAward").value = user_badge_criterion_id;
            document.getElementById("userSender_noAward").value = user_sender;
        });
        $(document).on('click', '.add-comments', function(e){
            e.preventDefault();
            var doc_id = $(this).attr('data-id');
            var file_title = $(this).attr('data-fileTitle');
            var file_creator = $(this).attr('data-fileCreator');
            var user_sender = $(this).attr('data-forUserId');
            var comment_doc = $(this).attr('data-commentDoc');
            document.getElementById("fileTitle").innerHTML = file_title;
            document.getElementById("fileCreator").innerHTML = file_creator;
            document.getElementById("forUserId").value = user_sender;
            document.getElementById("comment_deliverable").value = comment_doc;
        });
        $(document).on('click', '.doc-delete', function(e){
            e.preventDefault();
            var doc_id = $(this).attr('data-id');
            document.getElementById("deleteResource").value = doc_id;
        });
    });
</script>


<script>
    $('.fileModal').click(function (e){
        e.preventDefault();
        var fileURL = $(this).attr('href');
        var downloadURL = $(this).prev('input').val();
        var fileTitle = $(this).attr('title');

        // BUTTONS declare
        var bts = {
            download: {
                label: '<i class="fa fa-download"></i> {{ trans('langDownload') }}',
                className: 'submitAdminBtn gap-1',
                callback: function (d) {
                    window.location = downloadURL;
                }
            },
            print: {
                label: '<i class="fa fa-print"></i> {{ trans('langPrint') }}',
                className: 'submitAdminBtn gap-1',
                callback: function (d) {
                    var iframe = document.getElementById('fileFrame');
                    iframe.contentWindow.print();
                }
            }
        };
        if (screenfull.enabled) {
            bts.fullscreen = {
                label: '<i class="fa fa-arrows-alt"></i> {{ trans('langFullScreen') }}',
                className: 'submitAdminBtn gap-1',
                callback: function() {
                    screenfull.request(document.getElementById('fileFrame'));
                    return false;
                }
            };
        }
        bts.newtab = {
            label: '<i class="fa fa-plus"></i> {{ trans('langNewTab') }}',
            className: 'submitAdminBtn gap-1',
            callback: function() {
                window.open(fileURL);
                return false;
            }
        };
        bts.cancel = {
            label: '{{ trans('langCancel') }}',
            className: 'cancelAdminBtn'
        };

        bootbox.dialog({
            size: 'large',
            title: fileTitle,
            message: '<div class="row">'+
                        '<div class="col-12">'+
                            '<div class="iframe-container" style="height:500px;"><iframe id="fileFrame" src="'+fileURL+'" style="width:100%; height:500px;"></iframe></div>'+
                        '</div>'+
                    '</div>',
            buttons: bts
        });
    });

</script>
@endsection
