@push('head_scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            var searchSubmitted = false;

            var courseTable = $("#course_results_table").DataTable({
                'aoColumnDefs': [
                    { 'bSortable': false, 'sClass': 'text-center', 'aTargets': [0] }
                ],
                "bProcessing": true,
                "bServerSide": true,
                "ajax": {
                    "url": "{!! $_SERVER['SCRIPT_NAME'] !!}",
                    "type": "POST",
                    "headers": {
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    "data": function (d) {
                        d.search_submitted = searchSubmitted ? 1 : 0;
                        d.formsearchtitle = $('#formsearchtitle').val();
                        d.formsearchcode = $('#formsearchcode').val();
                        var facVal = $('input[name="formsearchfaculte"]').val();
                        if (typeof facVal === 'undefined' || facVal === null || facVal === '') {
                            facVal = $('#dialog-set-key').val();
                        }
                        d.formsearchfaculte = facVal || 0;
                    }
                },
                "aLengthMenu": [10, 15, 20, -1],
                "sPaginationType": "full_numbers",
                "bAutoWidth": false,
                "bStateSave": false,
                "searchDelay": 1000,
                "aoColumns": [
                    { "bSortable": false, "sWidth": "5%" },
                    { "bSortable": true, "sWidth": "60%" },
                    { "bSortable": false, "sWidth": "35%" }
                ],
                "fnDrawCallback": function(oSettings) {
                    var api = this.api();
                    if (searchSubmitted) {
                        $('#results_container').show();
                        if (api.rows().count() > 0) {
                            $('#bulk_actions_wrapper').show();
                            toggleBulkActionUI();
                        } else {
                            $('#bulk_actions_wrapper').hide();
                            $('#refresh_options_box').hide();
                        }
                    } else {
                        $('#results_container').hide();
                        $('#bulk_actions_wrapper').hide();
                        $('#refresh_options_box').hide();
                    }
                },
                "oLanguage": {
                    'lengthLabels': {
                        '-1': '{{ trans('langAllOfThem') }}'
                    },
                    "sLengthMenu":   "{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}",
                    "sZeroRecords":  "{{ trans('langNoResult') }}",
                    'sEmptyTable':   "{{ trans('langNoResult') }}",
                    "sInfo":         " {{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langToralResults') }}",
                    "sInfoEmpty":    '',
                    "sInfoFiltered": '',
                    "sInfoPostFix":  '',
                    "sSearch":       '{{ trans('langSearch') }}',
                    "oPaginate": {
                        "sFirst":    '&laquo;',
                        "sPrevious": '&lsaquo;',
                        "sNext":     '&rsaquo;',
                        "sLast":     '&raquo;'
                    }
                }
            });

            $('.dt-search input').attr({
                'style': 'width: 200px',
                'placeholder': '{{ trans('langTitle') }}, {{ trans('langTeacher') }}'
            });

            function performSearch(e) {
                if (e) {
                    e.preventDefault();
                }
                searchSubmitted = true;
                $('#results_container').show();
                courseTable.ajax.reload();
            }

            function toggleBulkActionUI() {
                var action = $('#bulk_action_select').val();
                if (action === 'delete') {
                    $('#delete_warning_box').slideDown(200);
                    $('#refresh_options_box').slideUp(200);
                } else if (action === 'refresh') {
                    $('#delete_warning_box').slideUp(200);
                    $('#refresh_options_box').slideDown(200);
                } else {
                    $('#delete_warning_box').slideUp(200);
                    $('#refresh_options_box').slideUp(200);
                }
            }

            $('#searchForm').on('submit', performSearch);
            $('#search_submit').on('click', performSearch);
            $('#bulk_action_select').on('change', toggleBulkActionUI);

            $(document).on('click', '#select_all_courses', function() {
                var isChecked = this.checked;
                $('#course_results_table input.select_course_checkbox').prop('checked', isChecked);
            });

            $('#bulkActionsForm').on('submit', function(e) {
                var action = $('#bulk_action_select').val();
                var checkedCount = $('#course_results_table input.select_course_checkbox:checked').length;

                if (checkedCount === 0) {
                    e.preventDefault();
                    alert('{{ js_escape(trans('langNoCourseSelected') ?? 'Δεν έχετε επιλέξει κανένα μάθημα.') }}');
                    return false;
                }

                if (action === 'delete') {
                    if (!confirm('{{ js_escape(trans('langCourseDelConfirm2') ?? 'Θέλετε σίγουρα να διαγράψετε τα επιλεγμένα μαθήματα;') }}')) {
                        e.preventDefault();
                        return false;
                    }
                }
            });
        });
    </script>
@endpush

@extends('layouts.default')

@section('content')

<main id="main" class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            @if(isset($action_bar))
                {!! $action_bar !!}
            @else
                <div class='mt-4'></div>
            @endif

            @include('layouts.partials.show_alert')

            <!-- Search Form -->
            <div class='col-12 mb-4'>
                <div class='form-wrapper form-edit border-0 px-0'>
                    <form id='searchForm' role='form' class='form-horizontal' action='#' method='get'>
                        <fieldset>
                            <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                            <div class='row align-items-end'>
                                <div class='col-md-4 col-12 form-group'>
                                    <label for='formsearchtitle' class='control-label-notes'>{{ trans('langTitle') }}</label>
                                    <input type='text' placeholder='{{ trans('langTitle') }}' class='form-control' id='formsearchtitle' name='formsearchtitle' value=''>
                                </div>
                                <div class='col-md-4 col-12 form-group mt-3 mt-md-0'>
                                    <label for='formsearchcode' class='control-label-notes'>{{ trans('langCourseCode') }}</label>
                                    <input id='formsearchcode' type='text' placeholder='{{ trans('langCourseCode') }}' class='form-control' name='formsearchcode' value=''>
                                </div>
                                <div class='col-md-4 col-12 form-group mt-3 mt-md-0'>
                                    <label for='dialog-set-value' class='control-label-notes'>{{ trans('langFaculty') }}</label>
                                    {!! $html !!}
                                </div>
                            </div>
                            <div class='form-group mt-4'>
                                <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                    <button class='btn submitAdminBtn' type='submit' name='search_submit' id='search_submit'>
                                        <i class="fa-solid fa-magnifying-glass me-1"></i>{{ trans('langSearch') }}
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>

            <!-- Results Container (Hidden by default until search is performed) -->
            <div id='results_container' class="col-12 mt-3" style="display: none;">
                <form id='bulkActionsForm' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                    {!! generate_csrf_token_form_field() !!}

                    <!-- Bulk Actions Header Bar (Above table, below search form) -->
                    <div id='bulk_actions_wrapper' class='mb-3' style='display: none;'>
                        <div class='d-flex justify-content-between align-items-center flex-wrap gap-3'>
                            <div class='d-flex align-items-center gap-2' style='max-width: 350px;'>
                                <select id='bulk_action_select' name='bulk_action' class='form-select'>
                                    <option value='refresh'>{{ trans('langRefreshCourse') }}</option>
                                    <option value='delete'>{{ trans('langCourseDel') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Deletion Warning Alert -->
                        <div id='delete_warning_box' class='col-12 mt-3' style='display: none;'>
                            <div class='alert alert-warning mb-0'>
                                <i class='fa-solid fa-circle-xmark fa-lg me-2'></i>{{ trans('langByDel') }}
                            </div>
                        </div>
                    </div>

                    <!-- Results Table -->
                    <table id='course_results_table' class='table-default display' style='width: 100%;'>
                        <thead>
                            <tr class='list-header'>
                                <th class='text-center' width='5%'><input type='checkbox' id='select_all_courses'></th>
                                <th>{{ trans('langCourseCode') }}</th>
                                <th>{{ trans('langFaculty') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                    <!-- Refresh Options Section (Shown below table when "refresh" action is selected) -->
                    <div id='refresh_options_box' class='col-12 mt-4' style='display: none;'>
                        <div class='form-wrapper form-edit rounded border'>
                            <div class='alert alert-info mb-4'>
                                <i class='fa-solid fa-circle-info fa-lg me-2'></i>
                                <span>{{ trans('langRefreshInfo') }} {{ trans('langRefreshInfo_A') }}</span>
                            </div>

                            <!-- Users - Unregister -->
                            <div class='mb-4'>
                                <div class='fw-bold mb-2'>
                                    {{ trans('langUsers') }} - {{ trans('langUnCourse') }}
                                    <span class='help-block d-block fw-normal text-muted small'>{{ trans('langUserDelCourseInfo') }}</span>
                                </div>
                                <div class='form-group mt-2'>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='delusersinactive'>
                                            <span class='checkmark'></span>
                                            {{ trans('langInactiveUsers') }}
                                        </label>
                                    </div>
                                </div>
                                <div class='form-group mt-3'>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='delusersdate'>
                                            <span class='checkmark'></span>
                                            {{ trans('langWithRegistrationDate') }}
                                        </label>
                                    </div>
                                    <div class='row mt-2'>
                                        <div class='col-md-6 col-12'>
                                            {!! selection(array('before' => trans('langBefore'), 'after' => trans('langAfter')), 'reg_flag', 'before', 'class="form-select"') !!}
                                        </div>
                                        <div class='col-md-6 col-12 mt-2 mt-md-0'>
                                            <input aria-label="{{ trans('langDate') }}" class='form-control' type='text' name='reg_date' id='reg_date' value='{!! date("d-m-Y", time()) !!}'>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Announcements & Agenda -->
                            <div class='row'>
                                <div class='col-md-6 col-12 mb-3'>
                                    <div class='fw-bold mb-2'>{{ trans('langAnnouncements') }}</div>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='delannounces'>
                                            <span class='checkmark'></span>
                                            {{ trans('langAnnouncesDel') }}
                                        </label>
                                    </div>
                                </div>
                                <div class='col-md-6 col-12 mb-3'>
                                    <div class='fw-bold mb-2'>{{ trans('langAgenda') }}</div>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='delagenda'>
                                            <span class='checkmark'></span>
                                            {{ trans('langAgendaDel') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Assignments & Exercises -->
                            <div class='row'>
                                <div class='col-md-6 col-12 mb-3'>
                                    <div class='fw-bold mb-2'>{{ trans('langWorks') }}</div>
                                    <div class='checkbox mb-2'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='hideworks'>
                                            <span class='checkmark'></span>
                                            {{ trans('langHideWork') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='delworkssubs'>
                                            <span class='checkmark'></span>
                                            {{ trans('langDelAllWorkSubs') }}
                                        </label>
                                    </div>
                                </div>
                                <div class='col-md-6 col-12 mb-3'>
                                    <div class='fw-bold mb-2'>{{ trans('langExercises') }}</div>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='purgeexercises'>
                                            <span class='checkmark'></span>
                                            {{ trans('langPurgeExercisesResults') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Statistics & Blog -->
                            <div class='row'>
                                <div class='col-md-6 col-12 mb-3'>
                                    <div class='fw-bold mb-2'>{{ trans('langUsage') }}</div>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='clearstats'>
                                            <span class='checkmark'></span>
                                            {{ trans('langClearStats') }}
                                        </label>
                                    </div>
                                </div>
                                <div class='col-md-6 col-12 mb-3'>
                                    <div class='fw-bold mb-2'>{{ trans('langBlog') }}</div>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='delblogposts'>
                                            <span class='checkmark'></span>
                                            {{ trans('langDelBlogPosts') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Wall -->
                            <div class='row'>
                                <div class='col-12 mb-3'>
                                    <div class='fw-bold mb-2'>{{ trans('langWall') }}</div>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='delwallposts'>
                                            <span class='checkmark'></span>
                                            {{ trans('langDelWallPosts') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Submit Button (Below table & options on the right) -->
                    <div id='bulk_submit_wrapper' class='col-12 mt-3 d-flex justify-content-end'>
                        <button type='submit' class='btn submitAdminBtn' name='bulk_submit' id='bulk_submit'>
                            {{ trans('langSubmit') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</main>
@endsection
