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

                        @include('layouts.partials.show_alert')

                        <div id='operations_container'>
                            {!! $action_bar !!}
                        </div>

                        {{-- Pending Grading of Submissions  (if any) --}}
                        @if ($is_editor && count($pending_exercises) > 0)
                            <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i>
                                <span>{{ trans('langPendingExercise') }}:
                                    <ul style='margin-top: 10px;'>
                                        @foreach ($pending_exercises as $row)
                                            <li>{{ $row->title }}) (<a class='Primary-400-cl' href='results.php?course={{ $course_code }}&exerciseId={{ getIndirectReference($row->eid) }}&status=2'>{{ trans('langViewShow') }}</a>)</li>
                                        @endforeach
                                    </ul>
                                </span>
                            </div>
                        @endif

                        {{-- Exercises list --}}
                        @if (count($result) > 0)
                            <div class='table-responsive'>
                                <table id='ex' class='table-default'>
                                    <thead>
                                    <tr class='list-header'>
                                        @if ($is_editor)
                                            <th>{{ trans('langExerciseName') }}</th>
                                            <th>{{ trans('langInfoExercise') }}</th>
                                            <th>{{ trans('langResults') }}</th>
                                            <th aria-label='{{ trans('langSettingSelect') }}'></th>
                                        @else
                                            <th>{{ trans('langExerciseName') }}</th>
                                            <th>{{ trans('langInfoExercise') }}</th>
                                            @if ($previousResultsAllowed)
                                                <th>{{ trans('langResults') }}</th>
                                            @endif
                                        @endif
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($result as $row)
                                        @if (!hasExerciseAnswers($row->id) and !$is_editor)
                                            @continue
                                        @endif

                                        <tr @if (!$row->active) class='not_visible' @endif>

                                            @if ($is_course_reviewer)
                                                <td>
                                                    <div class='line-height-default'><a href='admin.php?course={{ $course_code }}&amp;exerciseId={{ $row->id }}&amp;preview=1'>{{ $row->title }}</a>
                                                        @if (!$row->public)
                                                            &nbsp;{{ icon('fa-lock', trans('langNonPublicFile')) }}
                                                        @endif
                                                        @if (dateToObject($row->end_date) and dateToObject($row->end_date) < $currentDate)
                                                            {{-- exercise has expired --}}
                                                            &nbsp;&nbsp;<span class='text-danger'>({{ trans('langHasExpiredS') }})</span>
                                                        @endif
                                                        @if ($row->is_exam == 1)
                                                            &nbsp;
                                                            &nbsp;{!! icon('fa-solid fa-chalkboard-user', trans('langExam')) !!}
                                                        @endif
                                                    </div>
                                                    @if (!empty($row->description))
                                                        <br>{!! standard_text_escape($row->description) !!}
                                                    @endif
                                                    @if ($row->assign_to_specific == 1)
                                                        <a class='assigned_to' data-eid='{{ $row->id }}'><small class='help-block link-color'>{{ trans('langWorkAssignTo') }}: {{ trans('langWorkToUser') }}</small></a>
                                                    @elseif ($row->assign_to_specific == 2)
                                                        <a class='assigned_to' data-eid='{{ $row->id }}'><small class='help-block link-color'>{{ trans('langWorkAssignTo') }}: {{ trans('langWorkToGroup') }}</small></a>
                                                    @endif
                                                </td>

                                                <td @if (isset($row->start_date)) data-sort='{{ date('Y-m-d H:i', strtotime($row->start_date)) }}' @endif>
                                                    <small>
                                                        {{-- start date --}}
                                                        @if (isset($row->start_date))
                                                            <div class='Success-200-cl'>
                                                                {{ trans('langStart') }}
                                                                : {{ format_locale_date(strtotime($row->start_date), 'short') }}
                                                            </div>
                                                        @endif
                                                        {{-- end date --}}
                                                        @if (isset($row->end_date))
                                                            <div class='Accent-200-cl'>
                                                                {{ trans('langFinish') }}
                                                                : {{ format_locale_date(strtotime($row->end_date), 'short') }}
                                                            </div>
                                                        @endif
                                                        {{-- duration --}}
                                                        @if ($row->time_constraint > 0)
                                                            <div>
                                                                {{ trans('langDuration') }}
                                                                : {{ $row->time_constraint }} {{ trans('langExerciseConstrainUnit') }}
                                                            </div>
                                                        @endif
                                                        {{-- how many attempts we have? --}}
                                                        @if ($row->attempts_allowed > 0)
                                                            <div>
                                                                {{ trans('langAttempts') }}: {{ $row->attempts_allowed }}
                                                            </div>
                                                        @endif
                                                        {{-- is temp save enabled? --}}
                                                        @if ($row->temp_save == 1)
                                                            <div>
                                                                {{ trans('langTemporarySave') }}:<span class='Success-200-cl'>{{ trans('langYes') }}</span>
                                                            </div>
                                                        @endif
                                                    </small>
                                                </td>

                                                <td>
                                                    @if (count_exercise_submissions($row->id) > 0)
                                                        <div>
                                                            <a href='results.php?course={{ $course_code }}&exerciseId={{ getIndirectReference($row->id) }}'>{{ trans('langViewShow') }}</a>
                                                        </div>
                                                        <div>
                                                         <span class='badge Success-200-bg mt-2'>
                                                             @if (count_exercise_submissions($row->id) == 1)
                                                                 1 {{ trans('langExercisesSubmission') }}
                                                             @else
                                                                 {{ count_exercise_submissions($row->id) }} {{ trans('langExercisesSubmissions') }}
                                                             @endif
                                                         </span>
                                                        </div>
                                                        @else
                                                            &mdash;
                                                    @endif
                                                </td>
                                                @if ($is_editor)
                                                    <td class='text-end'>
                                                        {!!
                                                        action_button(array(
                                                            array('title' => trans('langCourseInfo'),
                                                                  'url' => "admin.php?course=$course_code&amp;exerciseId=$row->id&amp;modifyExercise=yes",
                                                                  'icon' => 'fa-cogs'),
                                                            array('title' => trans('langQuestionsManagement'),
                                                                  'url' => "admin.php?course=$course_code&amp;exerciseId=$row->id",
                                                                  'icon' => 'fa-edit'),
                                                            array('title' => trans('langCorrectByQuestion'),
                                                                    'icon-class' => 'by_question',
                                                                    'icon-extra' => "data-exerciseid=[$row->id]",
                                                                    'url' => "#",
                                                                    'icon' => 'fa-pencil',
                                                                    'show' => false), // to be fixed !!
                                                                    //'show' => $TotalExercises),
                                                            array('title' => trans('langDistributeExercise'),
                                                                    'icon-class' => 'distribution',
                                                                    'icon-extra' => "data-exerciseid= [$row->id]",
                                                                    'url' => "#",
                                                                    'icon' => 'fa-exchange',
                                                                    'show' => false), // to be fixed !!
                                                                    //'show' => $TotalExercises),
                                                            array('title' => $row->active?  trans('langViewHide') : trans('langViewShow'),
                                                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;".($row->active ? "choice=disable" : "choice=enable")."&amp;exerciseId=" . $row->id,
                                                                    'icon' => $row->active ? 'fa-eye-slash' : 'fa-eye' ),
                                                            array('title' => $row->public ? trans('langResourceAccessLock') : trans('langResourceAccessUnlock'),
                                                                    'url' => "$_SERVER[SCRIPT_NAME]?course={{ $course_code }}&amp;".($row->public ? "choice=limited" : "choice=public")."&amp;exerciseId=$row->id",
                                                                    'icon' => $row->public ? 'fa-lock' : 'fa-unlock',
                                                                    'show' => course_status($course_id) == COURSE_OPEN),
                                                            array('title' => trans('langUsage'),
                                                                    'url' => "exercise_stats.php?course=$course_code&amp;exerciseId=$row->id",
                                                                    'icon' => 'fa-line-chart'),
                                                            array('title' => trans('langWorkUserGroupNoSubmission'),
                                                                    'url' => "users_no_submission.php?course=$course_code&amp;exerciseId=$row->id",
                                                                    'icon' => 'fa-minus-square'),
                                                            array('title' => trans('langCreateDuplicate'),
                                                                    'icon-class' => 'warnLink',
                                                                    'icon-extra' => "data-exerciseid='$row->id'",
                                                                    'url' => "#",
                                                                    'icon' => 'fa-copy'),
                                                            array('title' => trans('langPurgeExerciseResults'),
                                                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=purge&amp;exerciseId=$row->id&" . generate_csrf_token_link_parameter(),
                                                                    'icon' => 'fa-eraser',
                                                                    'confirm' => trans('langConfirmPurgeExerciseResults')),
                                                            array('title' => trans('langDelete'),
                                                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=delete&amp;exerciseId=$row->id",
                                                                    'icon' => 'fa-xmark',
                                                                    'class' => 'delete',
                                                                    'confirm' => trans('langConfirmPurgeExercise'))
                                                            ))
                                                        !!}
                                                    </td>
                                                @endif
                                            @else {{-- student view --}}
                                                @if (!resource_access($row->active, $row->public))
                                                    @continue
                                                @endif
                                                @if ($currentDate >= dateToObject($row->start_date) && (!dateToObject($row->end_date) !== null || dateToObject($row->end_date) !== null && $currentDate <= dateToObject($row->end_date)))
                                                    @if (!is_null(hasExerciseIncompleteAttempts($row->id, $uid, $row->continue_time_limit)))
                                                        <td>
                                                            <a class='ex_settings active_exercise @if ($row->password_lock && !$is_editor) password_protected @endif'
                                                               href='exercise_submit.php?course={{ $course_code }}&exerciseId={{ $row->id }}&eurId={{ hasExerciseIncompleteAttempts($row->id, $uid, $row->continue_time_limit) }}'>{{ $row->title }}</a>&nbsp;&nbsp;(<span
                                                                    style='color:darkgrey'>{{ trans('langAttemptActive') }}</span>)
                                                    @elseif (!is_null(isExercisePaused($row->id, $uid)))
                                                        <td>
                                                            <a class='ex_settings paused_exercise @if ($row->password_lock && !$is_editor) password_protected @endif'
                                                               href='exercise_submit.php?course={{ $course_code }}&exerciseId={{ $row->id }}&amp;eurId={{ isExercisePaused($row->id, $uid) }}'> {{ $row->title }}</a>&nbsp;&nbsp;(<span style='color:darkgrey'>{{ trans('langAttemptPausedS') }}</span>)
                                                    @else
                                                        <td>
                                                            <div class='line-height-default'>
                                                                <a class='ex_settings @if ($row->password_lock && !$is_editor) password_protected @endif'
                                                                   href='exercise_submit.php?course={{ $course_code }}&exerciseId={{ $row->id }}'> {{ $row->title }}</a>
                                                                @if (!$row->public)
                                                                    &nbsp;{{ icon('fa-lock', trans('langNonPublicFile')) }}
                                                                @endif
                                                                @if (!hasExerciseAnswers($row->id) or isset($row->password_lock) or isset($row->ip_lock))
                                                                    &nbsp;&nbsp;<span class='fas fa-exclamation-triangle space-after-icon' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-html='true'
                                                                                      data-bs-title='
                                                                                      <ul>
                                                                                        @if ($row->password_lock)
                                                                                            <li>{{ trans('langPassCode') }}</li>
                                                                                        @endif
                                                                                        @if ($row->ip_lock)
                                                                                            <li>{{ trans('langIPUnlock') }}</li>
                                                                                        @endif
                                                                                        @if (!hasExerciseAnswers($row->id))
                                                                                            <li>{{ trans('langNoQuestion') }}</li>
                                                                                        @endif
                                                                                        </ul>
                                                                                      '>
                                                                    </span>
                                                                @endif
                                                                @if ($row->is_exam == 1)&nbsp;
                                                                    &nbsp;{!! icon('fa-solid fa-chalkboard-user', trans('langExam')) !!}
                                                                @endif
                                                    @endif
                                                        @elseif ($currentDate <= dateToObject(($row->start_date)))
                                                            {{-- exercise has not yet started --}}
                                                            <td class='not_visible'>{{ $row->title }}
                                                                @if (!$row->public)
                                                                    &nbsp;{{ icon('fa-lock', trans('langNonPublicFile')) }}
                                                                @endif
                                                                &nbsp;&nbsp;
                                                        @else
                                                            {{-- exercise has expired --}}
                                                            <td> {{ $row->title }})
                                                                @if (!$row->public)
                                                                    &nbsp;{{ icon('fa-lock', trans('langNonPublicFile')) }}
                                                                @endif
                                                                &nbsp;&nbsp;(<span
                                                                        class='asterisk Accent-200-cl'>{{ trans('langHasExpiredS') }}</span>)
                                                                @endif
                                                                @if (has_user_participate_in_exercise($row->id))
                                                                    &nbsp; <span class='fa-solid fa-check'
                                                                                 data-bs-toggle='tooltip'
                                                                                 data-bs-placement='bottom'
                                                                                 data-bs-title='{{ trans('langHasParticipated') }}'></span>
                                                        @endif
                                                        </div>
                                                        {!! $row->description !!}
                                                        </td>

                                                        <td @if (isset($row->start_date)) data-sort='{{ date("Y-m-d H:i", strtotime($row->start_date)) }}' @endif>
                                                            <small>
                                                                @if (isset($row->start_date))
                                                                    <div class='Success-200-cl'>
                                                                        {{ trans('langStart') }}
                                                                        : {{ format_locale_date(strtotime($row->start_date), 'short') }}
                                                                    </div>
                                                                @endif
                                                                @if (isset($row->end_date))
                                                                    <div class='Accent-200-cl'>
                                                                        {{ trans('langFinish') }}
                                                                        :{{ format_locale_date(strtotime($row->end_date), 'short') }}
                                                                    </div>
                                                                @endif
                                                                @if ($row->time_constraint > 0)
                                                                    <div>
                                                                        {{ trans('langDuration') }}
                                                                        : {{ format_locale_date(strtotime($row->end_date), 'short') }} {{ trans('langExerciseConstrainUnit') }}
                                                                    </div>
                                                                @endif
                                                                {{-- hom many attempts we have? --}}
                                                                @if ($row->attempts_allowed > 0)
                                                                    <div>
                                                                        {{ trans('langAttempts') }}
                                                                        : {{ exerciseUserAttempts($row->id, $uid) }}
                                                                        /{{ $row->attempts_allowed }}
                                                                    </div>
                                                                @endif
                                                                {{-- is temp save enabled? --}}
                                                                @if ($row->temp_save == 1)
                                                                    <div>
                                                                        {{ trans('langTemporarySave') }}: <span
                                                                                class='Success-200-cl'>{{ trans('langYes') }}</span>
                                                                    </div>
                                                                @endif
                                                            </small>
                                                        </td>
                                                        @if ($previousResultsAllowed)
                                                            @if ($row->score)
                                                                {{--  user last exercise score--}}
                                                                @if (exerciseUserLastScore($row->id, $uid) > 0)
                                                                    <td>
                                                                        <a href='results.php?course={{ $course_code }}&exerciseId={{ getIndirectReference($row->id) }}'>{{ trans('langViewShow') }}</a>
                                                                    </td>
                                                                @else
                                                                    <td> &dash;</td>
                                                                @endif
                                                            @else
                                                                <td>
                                                                    {{ trans('langNotAvailable') }}
                                                                </td>
                                                            @endif
                                                        @endif
                                                @endif
                                            </tr>
                                      @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class='col-sm-12'>
                                <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                    <span>{{ trans('langNoExercises') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type='text/javascript'>

        var lang = {
            assignmentPasswordModalTitle: '{{ js_escape(trans('langAssignmentPasswordModalTitle')) }}',
            exercisePasswordModalTitle: '{{ js_escape(trans('langExercisePasswordModalTitle')) }}',
            theFieldIsRequired: '{{ js_escape(trans('langTheFieldIsRequired')) }}',
            temporarySaveNotice: '{{ js_escape(trans('langTemporarySaveNotice2')) }}',
            continueAttemptNotice: '{{ js_escape(trans('langContinueAttemptNotice')) }}',
            continueAttempt: '{{ js_escape(trans('langContinueAttempt')) }}',
            cancel: '{{ js_escape(trans('langCancel')) }}',
            submit: '{{ js_escape(trans('langSubmit')) }}',
        };

        $(document).ready(function() {
            $('#ex').DataTable ({
                columns: [ {{ $columns }} ],
                fnDrawCallback: function (settings) { typeof MathJax !== 'undefined' && MathJax.typeset(); },
                lengthMenu: [10, 20, 30, -1],
                sPaginationType: 'full_numbers',
                bAutoWidth: true,
                searchDelay: 1000,
                order : [[1, 'desc']],
                oLanguage: {
                    lengthLabels: {
                        '-1': '{{ trans('langAllOfThem') }}'
                    },
                    sLengthMenu: '{{ js_escape(trans('langDisplay') . ' _MENU_ ' . trans('langResults2')) }}',
                    sZeroRecords: '{{ js_escape(trans('langNoResult')) }}',
                    sEmptyTable:   '{{ js_escape(trans('langNoExercises')) }}',
                    sInfo: '{{ js_escape(trans('langDisplayed') . ' _START_ ' .
                                     trans('langTill') . ' _END_ ' . trans('langFrom2') .
                                     ' _TOTAL_ ' . trans('langTotalResults')) }}',
                    sInfoEmpty: '',
                    sInfoFiltered: '',
                    sInfoPostFix:  '',
                    sSearch:       '',
                    oPaginate: {
                        sFirst: '&laquo;',
                        sPrevious: '&lsaquo;',
                        sNext: '&rsaquo;',
                        sLast: '&raquo;'
                    }
                }
            });
            $('.dt-search input').attr({
                'class' : 'form-control input-sm ms-0 mb-3',
                'placeholder' : '{{ js_escape(trans('langSearch')) }}...'
            });
            $('.dt-search label').attr('aria-label', '{{ js_escape(trans('langSearch')) }}');

            $(document).on('click', '.assigned_to', function(e) {
                e.preventDefault();
                var eid = $(this).data('eid');
                url = '{{ $urlAppend }}' + 'modules/exercise/index.php?ex_info_assigned_to=true&eid=' + eid;
                $.ajax({
                    url: url,
                    success: function(data) {
                        var dialog = bootbox.dialog({
                            message: data,
                            title : '{{ js_escape(trans('langWorkAssignTo')) }}',
                            onEscape: true,
                            backdrop: true,
                            buttons: {
                                success: {
                                    label: '{{ js_escape(trans('langClose')) }}',
                                    className: 'btn-success',
                                }
                            }
                        });
                        dialog.init(function() {
                            typeof MathJax !== 'undefined' && MathJax.typeset();
                        });
                    }
                });
            });

            $(document).on('click', '.warnLink', function() {
                var exerciseid = $(this).data('exerciseid');
                bootbox.dialog({
                    closeButton: false,
                    title: "<div class='icon-modal-default'><i class='fa-solid fa-cloud-arrow-up fa-xl Neutral-500-cl'></i></div><h2 class='modal-title-default text-center mb-0'>{{ js_escape(trans('langCreateDuplicateIn')) }}</h2>",
                    message: "<form action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post' id='clone_form'>"+
                                "<select class='form-select' id='course_id' name='clone_to_course_id'>"+
                                "<option value='{{ $course_id  }}'>--- {{ js_escape(trans('langCurrentCourse')) }} ---</option>"+
                                {!! $courses_options !!}  +
                                "</select>"+
                             "</form>",
                    buttons: {
                        cancel: {
                            label: '{{ js_escape(trans('langCancel')) }}',
                            className: 'cancelAdminBtn position-center'
                        },
                        success: {
                            label: '{{ js_escape(trans('langCreateDuplicate')) }}',
                            className: 'submitAdminBtn position-center',
                            callback: function (d) {
                                $('#clone_form').attr('action', 'index.php?course={{ $course_code }}&choice=clone&exerciseId=' + exerciseid);
                                $('#clone_form').submit();
                            }
                        }
                    }
                });
            });

            $(document).on('click', '.ex_settings, .password_protected', unit_password_bootbox);

            localStorage.removeItem('openEx');
            localStorage.removeItem('isTinyMCEFocused');
        });
    </script>
@endsection
