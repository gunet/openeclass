@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')
                    
                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert') 

                    {{-- Add or modify comments for a user --}}
                    @if(isset($_GET['add']) or isset($_GET['modify']))
                        <div class='d-lg-flex gap-4 mt-4'>
                            <div class='flex-grow-1'>
                                <div class='form-wrapper form-edit'>
                                    <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&amp;session={{ $sessionID }}&amp;pid={{ $pid }}">
                                        <fieldset>
                                            @if(!isset($_GET['modify']))
                                            <div class='alert alert-info'>
                                                <i class='fa-solid fa-circle-info fa-lg'></i>
                                                <span>{!! trans('langInfoCommentsConsultant') !!}</span>
                                            </div>
                                            @endif
                                            <div class='form-group'>
                                                <label class='control-label-notes' for='title'>{{ trans('langTitle') }}<span class='asterisk Accent-200-cl'>(*)</span></label>
                                                <input type='text' class='form-control' name='title' id='title' value='{{ $title }}'>
                                                @if(Session::getError('title'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('title') !!}</span>
                                                @endif
                                            </div>
                                            <div class='form-group mt-4'>
                                                <label class='control-label-notes' for='comments'>{{ trans('langComments') }}<span class='asterisk Accent-200-cl'>(*)</span></label>
                                                {!! $rich_text_editor_comments !!}
                                                @if(Session::getError('comments'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('comments') !!}</span>
                                                @endif
                                            </div>
                                            
                                            <div class='form-group mt-4'>
                                                <label class='control-label-notes' for='participants_u'>{{ trans('langReferencedObject') }}<span class='asterisk Accent-200-cl'>(*)</span></label>
                                                @if(!isset($_GET['modify']))
                                                    @if(count($participants) > 0)
                                                    <select class='form-select' id='participants_u' name='participants_u'>
                                                        <option value='0' selected>{{ trans('langSelect' )}}</option>
                                                        @foreach($participants as $p)
                                                            <option value='{{ $p->participants }}'>{{ $p->givenname }}&nbsp;{{ $p->surname }}</option>
                                                        @endforeach
                                                    </select>
                                                    @endif
                                                @else
                                                    <select class='form-select' id='participants_u' name='participants_u'>
                                                        <option value='{{ $user_u }}' selected>{!! $user_n !!}</option>
                                                    </select>
                                                    <input type='hidden' name='modify_comment' value='{{ $comment_id }}'>
                                                @endif
                                                @if(Session::getError('participants_u'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('participants_u') !!}</span>
                                                @endif
                                            </div>

                                            <div class='form-group mt-4'>
                                                <div class='col-sm-12'>
                                                    <div class='checkbox'>
                                                        <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                            <input type='checkbox' name='notify_comments' {!! ($notify_com == 1) ? 'checked' : '' !!}>
                                                            <span class='checkmark'></span>
                                                            {{ trans('langNotifyCommentsConsultant') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <button type='submit' class='btn submitAdminBtn mt-4' name='add_comments'>{{ trans('langSubmit') }}</button>
                                            {!! generate_csrf_token_form_field() !!}    
                                        </fieldset>
                                    </form>
                                </div>
                            </div>
                            <div class='d-none d-lg-block'>
                                <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                            </div>
                        </div>
                    @endif

                    {{-- Display all comments --}}
                    @if(!isset($_GET['add']) && !isset($_GET['view_comment']))
                        <div class='col-12'>
                            @if(count($all_comments)>0)
                                <div class='table-responsive'>
                                    <table class='table-default'>
                                        <thead>
                                            <tr>
                                                <th>{{ trans('langTitle') }}</th>
                                                <th>{{ trans('langTool') }}</th>
                                                <th>{{ trans('langReferencedObject') }}</th>
                                                @if($is_consultant)
                                                <th></th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($all_comments as $c)
                                                <tr>
                                                    <td>
                                                        <a class='link-color' href='{{ $urlAppend }}modules/session/poll_comments.php?course={{ $course_code }}&amp;session={{ $sessionID }}&amp;pid={{ $pid }}&amp;view_comment={{ $c->id }}'>{{ $c->title }}</a>
                                                    </td>
                                                    <td>
                                                        {{ $c->name }}
                                                    </td>
                                                    <td>{{ $c->givenname }}&nbsp;{{ $c->surname }}</td>
                                                    @if($is_consultant)
                                                        <td class='text-end'>
                                                            {!! 
                                                                action_button(array(
                                                                    array(
                                                                        'title' => trans('langEdit'),
                                                                        'url' => $urlAppend . "modules/session/poll_comments.php?course=" . $course_code . "&amp;session=" . $sessionID . "&amp;pid=" . $pid . "&amp;add=1&amp;modify=1&amp;comment=" . $c->id,
                                                                        'icon' => 'fa-solid fa-comments',
                                                                        'icon-class' => "add-comments",
                                                                        'show' => $is_consultant
                                                                    ),
                                                                    array(
                                                                        'title' => trans('langDelete'),
                                                                        'url' => '#',
                                                                        'icon' => 'fa-xmark',
                                                                        'icon-extra' => "data-bs-toggle='modal' data-bs-target='#commentDelete' data-id='{$c->id}'",
                                                                        'icon-class' => 'comment-delete')
                                                                ))
                                                            !!}
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class='alert alert-warning'>
                                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                        <span>{{ trans('langNoInfoAvailable') }}</span>
                                </div>
                            @endif
                        </div>
                    @endif


                    {{-- Display user's comment --}}
                    @if(isset($_GET['view_comment']))
                        {!! $html_comment !!}                                             
                    @endif

                </div>
            </div>

        </div>
    
    </div>
</div>

<div class='modal fade' id='commentDelete' tabindex='-1' aria-labelledby='commentDeleteLabel' aria-hidden='true'>
    <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&session={{ $sessionID }}&pid={{ $pid }}">
        <div class='modal-dialog modal-md'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <div class='modal-title'>
                        <div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>
                        <h2 class="modal-title-default text-center mb-0 mt-2" id="docDeleteLabel">{!! trans('langDelete') !!}</h2>
                    </div>
                </div>
                <div class='modal-body text-center'>
                    <input type='hidden' name='delete_comment' id='deleteComment'>
                    {{ trans('langContinueToDelComment') }}
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
        $(document).on('click', '.comment-delete', function(e){
            e.preventDefault();
            var comment_id = $(this).attr('data-id');
            document.getElementById("deleteComment").value = comment_id;
        });
    });
</script>

@endsection
