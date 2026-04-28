@extends('layouts.default')

@push('head_scripts')
    <script type='text/javascript'>
        var lang = {
            assignmentPasswordModalTitle: '{{ trans('langAssignmentPasswordModalTitle') }}',
            theFieldIsRequired: '{{ trans('langTheFieldIsRequired') }}',
            cancel: '{{ trans('langCancel') }}',
            submit: '{{ trans('langSubmit') }}',
        };
    </script>
@endpush

@if(get_config('eportfolio_enable'))
    @push('head_scripts')
        <script>
            $(document).on('click', 'a.list-group-item[href*="resources.php?token="]', function(e) {
                e.preventDefault();

                const href = $(this).attr('href');
                const url = new URL(href, window.location.origin);
                const rid = url.searchParams.get('rid');

                const modalId = `modal_work_${rid}`;
                const modalElement = document.getElementById(modalId);

                if (modalElement) {
                    const Modal = new bootstrap.Modal(modalElement);
                    Modal.show();

                    const formSelector = `#vis_form_work_${rid}`;
                    $(formSelector).attr('action', href);
                } else {
                    console.warn('Modal with ID', modalId, 'not found');
                }
            });
        </script>
    @endpush
@endif

@section('content')

<div class='{{ $container }} module-container py-lg-0'>
    <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">
            <aside class='aside-sidebar'>@include('layouts.partials.left_menu')</aside>
            <main id="main" class="col-12 main-maincontent col_maincontent_active">
                    <div class="row">
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view')

                        <div id='operations_container'>
                            {!! $action_bar !!}
                        </div>

                        @include('layouts.partials.show_alert')

                        @if (count($result) > 0)
                            <div class='col-sm-12'>
                                
                                    <table id='assignment_table_{{ $course_code }}' class='table-default'>
                                        <thead>
                                            <tr class='list-header'>
                                                <th style='width:45%;'>{{ trans('langTitle') }}</th>
                                                <th style='width:25%;'>{{ trans('langGroupWorkDeadline_of_Submission') }}</th>
                                                <th class='text-center'>{{ trans('langSubmitted') }}</th>
                                                <th class='text-center'>{{ trans('langGradebookGrade') }}</th>
                                                @if (get_config('eportfolio_enable'))
                                                    <th style='width:10%;' class='text-center' aria-label='{{ trans('langSettingSelect') }}'><i class="fa-solid fa-gear"></th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($result as $key => $row)
                                            @if (strtotime(date("d-m-Y H:i:s")) < strtotime($row->submission_date)) {{-- assignment not starting yet --}}
                                                <tr class='not_visible'>
                                            @else
                                                <tr>
                                            @endif
                                                <td>
                                                    @if (strtotime(date("d-m-Y H:i:s")) < strtotime($row->submission_date)) {{-- assignment not starting yet --}}
                                                        {{ $row->title }}
                                                    @else
                                                        <a href='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&id={{ $row->id }}' @if ($row->password_lock) class='password_protected' @endif>{{ $row->title }}</a>

                                                        @if ($row->assignment_type == ASSIGNMENT_TYPE_TURNITIN)
                                                            &nbsp;&nbsp;<span class='badge' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true' data-bs-original-title='{{ trans('langAssignmentTypeTurnitinInfo') }}'>
                                                                <small>
                                                                    {{ trans('langAssignmentTypeTurnitin') }}
                                                                </small>
                                                            </span>
                                                        @endif

                                                        @if (!isset($_REQUEST['unit']))
                                                            @if ($row->password_lock or $row->ip_lock)
                                                                &nbsp;&nbsp;<span class='fa fa-exclamation-triangle space-after-icon' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true'
                                                                                  data-bs-original-title='<ul>@if ($row->password_lock) <li>{{ trans('langPassCode') }}</li> @endif @if ($row->ip_lock) <li>{{ trans('langIPUnlock') }} @endif</li></ul>'>
                                                                </span>
                                                            @endif
                                                        @endif

                                                        <div>
                                                            <small class='text-muted'>
                                                                @if ($row->group_submissions)
                                                                    {{ trans('langGroupAssignment') }}
                                                                @else
                                                                    {{ trans('langUserAssignment') }}
                                                                @endif
                                                            </small>
                                                        </div>
                                                    @endif

                                                    @if ($row->grading_type == ASSIGNMENT_PEER_REVIEW_GRADE && $row->start_date_review && $row->due_date_review)
                                                        <p class="TextBold mt-2 mb-0 text-decoration-underline small-text">{{ trans('langGradeReviews')}}</p>
                                                        <p class="TextBold mb-0 small-text">{{ trans('langStartDate') }}:&nbsp;<span>{{ format_locale_date(strtotime($row->start_date_review), 'short') }}</span></p>
                                                        <p class="TextBold mb-0 small-text">{{ trans('langEndDate') }}:&nbsp;<span>{{ format_locale_date(strtotime($row->due_date_review), 'short') }}</span></p>
                                                        @if ( strtotime(date("d-m-Y H:i:s")) < strtotime($row->start_date_review) )
                                                            <p class="text-warning TextBold small-text mt-2" style="line-height:14px;">{{ trans('langGradeReviewHasNotStarted') }}</p>
                                                        @elseif ( strtotime(date("d-m-Y H:i:s")) > strtotime($row->start_date_review) && strtotime(date("d-m-Y H:i:s")) < strtotime($row->due_date_review))
                                                            <p class="text-success TextBold small-text mt-2" style="line-height:14px;">{{ trans('langGradeReviewInProgress') }}</p>
                                                            <div class="mt-2">
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
                                                        @elseif (strtotime(date("d-m-Y H:i:s")) > strtotime($row->due_date_review))
                                                            <p class="text-danger TextBold small-text mt-2" style="line-height:14px;">{{ trans('langGradeReviewHasExpired') }}</p>
                                                        @endif
                                                    @endif

                                                </td>
                                                <td data-sort='{{ $loop->iteration }}'>
                                                    @if (isset($row->deadline))
                                                        {!! format_locale_date(strtotime($row->deadline)) !!}
                                                    @else
                                                        {{ trans('langNoDeadline') }}
                                                    @endif
                                                    @if (strtotime(date("d-m-Y H:i:s")) < strtotime($row->submission_date)) {{-- assignment not starting yet --}}
                                                        <div class='Warning-200-cl'>
                                                            <small>
                                                                {{ trans('langWillStartAt') }}: {!! format_locale_date(strtotime($row->submission_date)) !!}
                                                            </small>
                                                        </div>
                                                    @elseif ($row->time > 0)
                                                        <div>
                                                            <small class='label label-warning'>{{ trans('langDaysLeft') }} {!! format_time_duration($row->time) !!}</small>
                                                        </div>
                                                    @elseif($row->deadline)
                                                        <div>
                                                            <small class='label label-danger'>
                                                                {{ trans('langHasExpiredS') }}
                                                            </small>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class='text-center'>
                                                    @if ($submission = find_submissions(is_group_assignment($row->id), $uid, $row->id, $gids))
                                                        <i class='fa-solid fa-check' data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ trans('langYes') }}" aria-label="{{ trans('langYes') }}" tabindex="0"></i><br>
                                                        @foreach ($submission as $sub)
                                                            @if (isset($sub->group_id)) {{-- if is a group assignment --}}
                                                                <div>
                                                                    <small>
                                                                        {{ trans('langGroupSubmit') }} {{ trans('langOfGroup') }} <em> {{ gid_to_name($sub->group_id) }}</em>
                                                                    </small>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <i class='fa-regular fa-hourglass-half' data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ trans('langNo') }}" aria-label="{{ trans('langNo') }}" tabindex="0"></i><br>
                                                    @endif
                                                </td>
                                                <td class='text-center'>
                                                    @if ($submission = find_submissions(is_group_assignment($row->id), $uid, $row->id, $gids))
                                                        @foreach ($submission as $sub)
                                                            <div>
                                                                @if (submission_grade($sub->id))
                                                                    {{ submission_grade($sub->id) }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </td>

                                                @if (get_config('eportfolio_enable'))
                                                    <td class='text-center' style='width:10%;'>
                                                        {!! action_button(array(
                                                            array(
                                                                'title' => trans('langAddResePortfolio'),
                                                                'url' => $urlAppend . "main/eportfolio/resources.php?token=" .token_generate('eportfolio' . $uid) ."&amp;action=add&amp;type=work_submission&amp;rid=$row->id",
                                                                'icon' => 'fa-star'
                                                                )
                                                            ));
                                                        !!}
                                                        <div class="modal fade" id="modal_work_{{$row->id}}" tabindex="-1" aria-labelledby="workModalLabel_{{$row->id}}" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                            <div class="modal-content">
                                                        
                                                                <div class="modal-header">
                                                                <h5 class="modal-title" id="workModalLabel_{{$row->id}}">{{ trans('langAddResePortfolio') }} - {{$row->title}}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ trans('langClose') }}"></button>
                                                                </div>
                                                        
                                                                <div class="modal-body">
                                                                <form id="vis_form_work_{{$row->id}}" name="vis_form_work_{{$row->id}}" action="" method="post">
                                                                    <div class="mb-3">
                                                                        <label for="vis_form_work_{{$row->id}}_select" class="form-label">{{ trans('langePortfolioFieldsVisibilitySettings') }}</label>
                                                                        <select class="form-select" name="visibility" id="vis_form_work_{{$row->id}}_select">
                                                                        <option value="{{EPF_VISIBLE_PUBLIC}}">{{ trans('langPublicePortfolioField') }}</option>
                                                                        <option value="{{EPF_VISIBLE_USERS}}">{{ trans('langOpenToRegisteredUsers') }}</option>
                                                                        <option value="{{EPF_VISIBLE_PRIVATE}}">{{ trans('langProfileInfoPrivate') }}</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="vis_form_work_{{$row->id}}_textarea" class="form-label">{{ trans('langePortfolioPromptAddReflComments') }}</label>
                                                                        <textarea class="form-control" name="reflection_comments" id="vis_form_work_{{$row->id}}_textarea"></textarea>
                                                                    </div>
                                                                    <button type="submit" class="btn btn-primary">{{ trans('langSubmit') }}</button>
                                                                </form>
                                                                </div>
                                                        
                                                            </div>
                                                            </div>
                                                        </div>
                                                     </td>
                                                @endif
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                
                            </div>
                        @else
                            <div class='col-sm-12'>
                                <div class='alert alert-warning'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                    <span>{{ trans('langNoAssign') }}</span>
                                </div>
                            </div>
                        @endif

                    </div>
                </main>
            </div>
        </div>


    <script type='text/javascript'>
        $(document).ready(function() {
            $('#assignment_table_{{ $course_code }}').DataTable ({
                'stateSave': true,
                'columns': [ {{ $columns }} ],
                'fnDrawCallback': function (settings) { typeof MathJax !== 'undefined' && MathJax.typeset(); },
                'lengthMenu': [10, 20, 30 , -1],
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'responsive': true,
                'order' : [ [1, 'asc'] ],
                'oLanguage': {
                    'lengthLabels': {
                        '-1': '{{ trans('langAllOfThem') }}'
                    },
                    'sLengthMenu': '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
                    'sEmptyTable': '{{ trans('langNoResult') }}',
                    'sZeroRecords': '{{ trans('langNoResult') }}',
                    'sInfo': '{{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langTotalResults') }}',
                    'sInfoEmpty': '',
                    'sInfoFiltered': '',
                    'sInfoPostFix': '',
                    'sSearch': '',
                    'sUrl': '',
                    'oPaginate': {
                        'sFirst': '&laquo;',
                        'sPrevious': '&lsaquo;',
                        'sNext': '&rsaquo;',
                        'sLast': '&raquo;'
                    }
                },
                'tabIndex': -1,
                'initComplete': function() {
                    $('#assignment_table_{{ $course_code }} thead .dt-column-order').each(function() {
                        $(this).removeAttr('aria-label');
                        $(this).attr('aria-hidden', 'true');
                    });
                }
            });
            $('#assignment_table_{{ $course_code }}').on('order.dt', function() {
                $('#assignment_table_{{ $course_code }} thead .dt-column-order').each(function() {
                    $(this).removeAttr('aria-label');
                    $(this).attr('aria-hidden', 'true');
                });
            });
            $('.dt-search input').attr({
                'class': 'form-control input-sm ms-0 mb-3',
                'placeholder': '{{ trans('langSearch') }}...'
            });
            $('.dt-search label').attr('aria-label', '{{ trans('langSearch') }}');

            $(document).on('click', '.password_protected', unit_password_bootbox);
        });
    </script>

@endsection

