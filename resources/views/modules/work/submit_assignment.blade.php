@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                @include('layouts.partials.left_menu')

                <div class="col_maincontent_active">
                    <div class="row">
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view')

                        <div id='operations_container'>
                            {!! $action_bar !!}
                        </div>

                        @include('layouts.partials.show_alert')

                        {{-- Assignment details --}}
                        @include('modules.work.assignment_details')

                        {{-- Submission details --}}
                        @if ($submissions_exist)
                            @include('modules.work.submission_details')
                        @endif

                        @if ($submit_ok)
                            @if ($submissions_exist)
                                <div class='col-12 mt-3'>
                                    <div class='alert alert-info'>
                                        <i class='fa-solid fa-circle-info fa-lg'></i>
                                        <span>
                                            @if ($submissions_exist > 1)
                                                {{ trans('langNotice3Multiple') }}
                                            @else
                                                {{ trans('langNotice3') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endif

                            <div class='d-lg-flex gap-4 mt-4'>
                                <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
                                    <form class='form-horizontal' enctype='multipart/form-data' action='{{ $form_link }}' method='post'>
                                        <input type='hidden' name='id' value='{{ $id }}'>
                                        {!! $group_select_hidden_input !!}

                                        @if (isset($_GET['unit']))
                                            <input type='hidden' name='unit' value='{{ $_GET['unit'] }}'>
                                            <input type='hidden' name='res_type' value='assignment'>
                                        @endif

                                        <fieldset>
                                            <legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>
                                            {!! $group_select_form !!}

                                            @if (!$is_group_assignment || $count_user_group_info || $on_behalf_of)
                                                @if ($submission_type == 1)  {{-- online text submission--}}
                                                    <div class='form-group mt-0'>
                                                        <label for='submission_text' class='col-sm-6 control-label-notes'>{{ trans('langWorkOnlineText') }}:</label>
                                                        <div class='col-sm-12'>
                                                            {!! rich_text_editor('submission_text', 10, 20, '')!!}
                                                        </div>
                                                    </div>
                                                @elseif ($submission_type == 2) {{-- Multiple file submission --}}
                                                    <script>
                                                        $(function () { initialize_multifile_submission({{ $max_submissions }}) });
                                                    </script>
                                                    <div class='form-group mt-0'>
                                                        <label for='userfile' class='col-sm-6 control-label-notes'>{{ trans('langWorkFileLimit') }}: {{ $max_submissions }} </label>
                                                        <div class='col-sm-10'>
                                                            <div>
                                                                <button class='btn submitAdminBtn btn-sm moreFiles' aria-label='Add'>
                                                                    <span class='fa fa-plus'></span>
                                                                </button>
                                                            </div>
                                                            <input type='file' name='userfile[]' id='userfile'>
                                                        </div>
                                                    </div>
                                                @else {{-- Single file submission --}}
                                                    <div class='form-group mt-0'>
                                                        <label for='userfile' class='col-sm-6 control-label-notes'>{{ trans('langWorkFile') }}:</label>
                                                        <div class='col-sm-10'>
                                                            <input type='file' name='userfile' id='userfile'>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif

                                            @if ($on_behalf_of)
                                                <div class='form-group mt-4'>
                                                    <div class='col-sm-6 control-label-notes'>{{ trans('langGradebookGrade') }}:</div>
                                                    <div class='col-sm-2'>
                                                        {!! $grade_field !!}
                                                        <input type='hidden' name='on_behalf_of' value='1'>
                                                    </div>
                                                </div>
                                                <div class='form-group mt-4'>
                                                    <div class='col-sm-10 col-sm-offset-2'>
                                                        <div class='checkbox'>
                                                            <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                                <input type='checkbox' name='send_email' id='email_button' value='1'>
                                                                <span class='checkmark'></span>
                                                                {{ trans('langEmailToUsers') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class='form-group mt-4'>
                                                <label for='stud_comments' class='col-sm-6 control-label-notes'>{{ trans('langComments') }}:</label>
                                                <div class='col-sm-12'>
                                                    <textarea class='form-control' name='stud_comments' id='stud_comments' rows='5'></textarea>
                                                </div>
                                            </div>

                                            <div class='form-group mt-4'>
                                                <div class='col-12 d-flex justify-content-end align-items-center'>
                                                    {!!
                                                        form_buttons(array(
                                                            array(
                                                            'class'         => 'submitAdminBtn',
                                                            'text'          => trans('langSubmit'),
                                                            'name'          => 'work_submit',
                                                            'value'         => trans('langSubmit')
                                                            ),
                                                            array(
                                                                'class' => 'cancelAdminBtn',
                                                                'href' => $back_link
                                                                )
                                                        ))
                                                    !!}
                                                </div>
                                            </div>

                                        </fieldset>
                                    </form>
                                    <small>
                                        {{ trans('langMaxFileSize') }} {!! ini_get('upload_max_filesize') !!}
                                    </small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


