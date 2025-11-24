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

                        {{-- Submission details --}}
                        @if ($submissions_exist)
                            @include('modules.work.submission_details')
                            <div class="col-12 d-flex justify-content-center my-4">
                                <div class="bg-transparent">
                                    <i class="fa-solid fa-circle-chevron-down fs-1 Neutral-900-cl"></i>
                                </div>
                            </div>
                            @if ($grading_type == ASSIGNMENT_PEER_REVIEW_GRADE && $ass)
                                @if ($start_date_review < $cdate)
                                    @if ($reviews_per_assignment < $count_of_assign && $result)
                                        <div class="col-12">
                                            <div class="card panelCard px-lg-4 py-lg-3" @if($row->due_date_review && ($cdate > $row->due_date_review or $cdate < $row->start_date_review)) style="opacity: 0.65;" @endif>
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <h3 class="mb-0" style="line-height: 14px;">
                                                        <em>{{ trans('langGradeReviews') }}</em>
                                                    </h3>
                                                </div>
                                                <div class="card-body">
                                                    @if ($cdate < $row->start_date_review)
                                                        <p class="text-warning TextBold small-text" style="line-height:14px;">{{ trans('langGradeReviewHasNotStarted') }}</p>
                                                    @elseif ($cdate >= $row->start_date_review && $cdate <= $row->due_date_review)
                                                        <div class='d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                                                            <p class="text-success TextBold small-text" style="line-height:14px;">{{ trans('langGradeReviewInProgress') }}</p>
                                                            <div>
                                                                <div class='spinner-grow text-success spinner-grow-sm' role='status'>
                                                                    <span class='visually-hidden'></span>
                                                                </div>
                                                                <div class='spinner-grow text-danger spinner-grow-sm' role='status'>
                                                                    <span class='visually-hidden'></span>
                                                                </div>
                                                                <div class='spinner-grow text-warning spinner-grow-sm' role='status'>
                                                                    <span class='visually-hidden'></span>
                                                                </div>
                                                                <div class='spinner-grow text-info spinner-grow-sm' role='status'>
                                                                    <span class='visually-hidden'></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @elseif ($cdate > $row->due_date_review)
                                                        <p class="text-danger TextBold small-text" style="line-height:14px;">{{ trans('langGradeReviewHasExpired') }}</p>
                                                    @endif
                                                    @include('modules.work.assignment_review')
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="col-12">
                                        <div class="card panelCard px-lg-4 py-lg-3" @if($row->due_date_review && ($cdate > $row->due_date_review or $cdate < $row->start_date_review)) style="opacity: 0.65;" @endif>
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h3 class="mb-0" style="line-height: 14px;">
                                                    <em>{{ trans('langGradeReviews') }}</em>
                                                </h3>
                                            </div>
                                            <div class="card-body">
                                                <p class="text-warning TextBold small-text" style="line-height:14px;">{{ trans('langGradeReviewHasNotStarted') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endif

                        {{-- Assignment details --}}
                        @include('modules.work.assignment_details')

                        @if ($submit_ok)
                            @if ($submissions_exist)
                                <div class='col-12'>
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
                            @if (!$is_group_assignment || $count_user_group_info || $on_behalf_of)
                                <div class="col-12">
                                    <div class="card panelCard px-lg-4 py-lg-3" @if($row->deadline && $cdate > $row->deadline) style="opacity: 0.65;" @endif>
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h3 class="mb-0" style="line-height: 14px;">
                                                <em>{{ trans('langAssignmentsSubmission')}}</em>
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <div class='d-lg-flex gap-4'>
                                                <div class='flex-grow-1'>
                                                    <div class='form-wrapper form-edit rounded'>
                                                        <form id="submit_assignment_form" class='form-horizontal' enctype='multipart/form-data' action='{{ $form_link }}' method='post'>
                                                            <input type='hidden' name='id' value='{{ $id }}'>
                                                            {!! $group_select_hidden_input !!}

                                                            @if (isset($_GET['unit']))
                                                                <input type='hidden' name='unit' value='{{ $_GET['unit'] }}'>
                                                                <input type='hidden' name='res_type' value='assignment'>
                                                            @endif

                                                            <fieldset>
                                                                <legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>
                                                                {!! $group_select_form !!}

                                                                @if ($assignment_type == ASSIGNMENT_TYPE_TURNITIN)
                                                                    {!! show_turnitin_integration($id) !!}
                                                                @else
                                                                    @if ($submission_type == 1)  {{-- online text submission--}}
                                                                        <div class='form-group mt-0'>
                                                                            <label for='submission_text' class='col-sm-6 control-label-notes'>{{ trans('langWorkOnlineText') }}:</label>
                                                                            <div class='col-sm-12'>
                                                                                {!! $rich_text_editor !!}
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        @php
                                                                            if ($submission_type == 0) {
                                                                                $max_submissions = 1;
                                                                            }
                                                                        @endphp
                                                                        <div class='form-group mt-0'>
                                                                            @push('head_styles')
                                                                                <link href="{{ $urlAppend }}js/bundle/uppy.min.css" rel="stylesheet">
                                                                            @endpush
                                                                            <div>
                                                                                <label for='userfile' class='col-sm-12 control-label-notes'>{{ trans('langWorkFileLimit') }}: {{ $max_submissions }}</label>
                                                                                <div id="uppy"></div>
                                                                            </div>
                                                                            <script>
                                                                                let isUppyLoaded = false;

                                                                                async function loadUppy() {
                                                                                    console.log('loadUppy');
                                                                                    try {
                                                                                        console.log('Uppy loaded');
                                                                                        const { Uppy, Dashboard, XHRUpload, English, French, German, Italian, Spanish, Greek } = await import("{{ $urlAppend }}js/bundle/uppy.js");

                                                                                        const locale_map = {
                                                                                            'de': German,
                                                                                            'el': Greek,
                                                                                            'en': English,
                                                                                            'es': Spanish,
                                                                                            'fr': French,
                                                                                            'it': Italian,
                                                                                        }

                                                                                        const uppy = new Uppy({
                                                                                            autoProceed: false,
                                                                                            restrictions: {
                                                                                                maxFileSize: {{ parseSize(ini_get('upload_max_filesize')) }},
                                                                                                maxNumberOfFiles: {{ $max_submissions }},
                                                                                            }
                                                                                        })

                                                                                        uppy.use(Dashboard, {
                                                                                            target: '#uppy',
                                                                                            inline: true,
                                                                                            showProgressDetails: true,
                                                                                            proudlyDisplayPoweredByUppy: false,
                                                                                            height: 500,
                                                                                            thumbnailWidth: 100,
                                                                                            locale: locale_map['{{ $language }}'] || English,
                                                                                            hideUploadButton: true
                                                                                        });

                                                                                        uppy.use(XHRUpload, {
                                                                                            endpoint: '{!! $form_link !!}',
                                                                                            fieldName: 'userfile[]',
                                                                                            bundle: true,
                                                                                            formData: true,
                                                                                            getResponseData: (responseText, response) => {
                                                                                                return { url: '' };
                                                                                            }
                                                                                        });

                                                                                        const form = document.querySelector('#submit_assignment_form');

                                                                                        if (form) {
                                                                                            form.addEventListener('submit', (event) => {
                                                                                                event.preventDefault();
                                                                                                const files = uppy.getFiles();
                                                                                                if (files.length === 0) {
                                                                                                    form.submit();
                                                                                                    return;
                                                                                                }
                                                                                                const formData = new FormData(form);
                                                                                                const metaData = {};

                                                                                                formData.forEach((value, key) => {
                                                                                                    metaData[key] = value;
                                                                                                });

                                                                                                uppy.setMeta(metaData);

                                                                                                const submitBtn = form.querySelector('[type="submit"]');
                                                                                                if(submitBtn) {
                                                                                                    submitBtn.disabled = true;
                                                                                                    submitBtn.innerText = '{{ trans("langPleaseWait") }}...';
                                                                                                }

                                                                                                uppy.upload();
                                                                                            });
                                                                                        }

                                                                                        uppy.on('complete', (result) => {
                                                                                            const submitBtn = form.querySelector('[type="submit"]');

                                                                                            if (result.successful.length > 0) {
                                                                                                console.log('Upload complete! We submitted inputs + files.');

                                                                                                window.location.href = '{!! $assignment_link !!}';

                                                                                            } else {
                                                                                                console.error('Upload failed:', result.failed);
                                                                                                if(submitBtn) {
                                                                                                    submitBtn.disabled = false;
                                                                                                    submitBtn.innerText = 'Submit';
                                                                                                }
                                                                                                alert('An error occurred during submission.');
                                                                                            }
                                                                                        });

                                                                                        isUppyLoaded = true;

                                                                                    } catch (error) {
                                                                                        console.log('Uppy not loaded', error);
                                                                                        isUppyLoaded = false;
                                                                                    }
                                                                                }

                                                                                loadUppy();

                                                                            </script>
                                                                        </div>
                                                                    @endif

                                                                    {{-- Comments --}}

                                                                    @if ($on_behalf_of)
                                                                        <div class='form-group mt-4'>
                                                                            <div class='col-sm-6 control-label-notes'>
                                                                                {{ trans('langGradebookGrade') }}:
                                                                            </div>
                                                                            <div class='col-sm-1'>
                                                                                {!! $grade_field !!}
                                                                                <input type='hidden' name='on_behalf_of' value='1'>
                                                                            </div>
                                                                        </div>
                                                                        @if ($grading_type == ASSIGNMENT_STANDARD_GRADE)
                                                                            <span class="help-block">({{ trans('langMaxGrade') }}: {{ $max_grade }})</span>
                                                                        @endif
                                                                        <div class='form-group mt-4'>
                                                                            <label for='stud_comments' class='col-sm-6 control-label-notes'>{{ trans('langComments') }}:</label>
                                                                            <div class='col-sm-12'>
                                                                                <textarea class='form-control' name='stud_comments' id='stud_comments' rows='5'></textarea>
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
                                                                    @else
                                                                        <div class='form-group mt-3'>
                                                                            <label for='stud_comments' class='col-sm-6 control-label-notes'>{{ trans('langComments') }}:</label>
                                                                            <div class='col-sm-12'>
                                                                                <textarea class='form-control' name='stud_comments' id='stud_comments' rows='5'></textarea>
                                                                            </div>
                                                                        </div>
                                                                        <input type='hidden' name='work_submit' value='true'>
                                                                    @endif

                                                                    <div class='form-group mt-4'>
                                                                        <div class='col-12 d-flex justify-content-end align-items-center'>
                                                                            {!!
                                                                                form_buttons([
                                                                                    [ 'class' => 'cancelAdminBtn',
                                                                                      'href' => $back_link ],
                                                                                    [ 'class' => 'submitAdminBtn',
                                                                                      'text'          => trans('langSubmit'),
                                                                                      'name'          => 'work_submit',
                                                                                      'value'         => trans('langSubmit') ],
                                                                                ])
                                                                            !!}
                                                                        </div>
                                                                    </div>
                                                                    <small>
                                                                        {{ trans('langMaxFileSize') }} {!! ini_get('upload_max_filesize') !!}
                                                                    </small>
                                                                @endif
                                                            </fieldset>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
