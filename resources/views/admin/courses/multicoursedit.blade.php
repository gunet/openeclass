@push('head_scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#id_date').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '{{ js_escape($language) }}',
                autoclose: true
            });

            var searchSubmitted = false;
            var currentStep = 1;

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
                        d.formsearchtype = $('#formsearchtype').val();
                        d.formsearchprof = $('#formprof').val();
                        d.reg_flag = $('select[name="reg_flag"]').val();
                        d.date = $('#id_date').val();
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

            function goToStep(step) {
                currentStep = step;
                $('#step1_container, #step2_container, #step3_container').hide();
                $('#step' + step + '_container').show();

                for (var i = 1; i <= 3; i++) {
                    var $stepEl = $('#wizard_step' + i);
                    var $badgeEl = $stepEl.find('.step-badge');
                    var $labelEl = $stepEl.find('.step-label');

                    if (i === step) {
                        $stepEl.removeClass('opacity-50').addClass('active');
                        $badgeEl.removeClass('bg-secondary text-white bg-success').addClass('bg-primary text-white');
                        $labelEl.removeClass('text-secondary text-success').addClass('text-primary');
                    } else if (i < step) {
                        $stepEl.removeClass('opacity-50').addClass('completed');
                        $badgeEl.removeClass('bg-secondary bg-primary').addClass('bg-success text-white');
                        $labelEl.removeClass('text-secondary text-primary').addClass('text-success');
                    } else {
                        $stepEl.addClass('opacity-50').removeClass('active completed');
                        $badgeEl.removeClass('bg-primary bg-success').addClass('bg-secondary text-white');
                        $labelEl.removeClass('text-primary text-success').addClass('text-secondary');
                    }
                }
            }

            function performSearch(e) {
                if (e) {
                    e.preventDefault();
                }
                searchSubmitted = true;
                courseTable.ajax.reload(function() {
                    goToStep(2);
                });
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

            $('#step2_prev_btn').on('click', function() {
                goToStep(1);
            });

            $('#step2_next_btn').on('click', function() {
                var checkedCount = $('#course_results_table input.select_course_checkbox:checked').length;
                if (checkedCount === 0) {
                    alert('{{ js_escape(trans('langNoCourseSelected') ?? 'Δεν έχετε επιλέξει κανένα μάθημα.') }}');
                    return false;
                }
                $('#selected_courses_count').text(checkedCount);
                goToStep(3);
                toggleBulkActionUI();
            });

            $('#step3_prev_btn').on('click', function() {
                goToStep(2);
            });

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

            <!-- Wizard Progress Header -->
            <div class="col-12 mb-4">
                <div class="card border-0 bg-light p-3">
                    <div class="d-flex justify-content-between align-items-center position-relative">
                        <div class="wizard-step active text-center flex-fill position-relative" id="wizard_step1">
                            <div class="step-badge rounded-circle bg-primary text-white mx-auto d-flex align-items-center justify-content-center mb-1" style="width: 36px; height: 36px; font-weight: bold;">1</div>
                            <span class="step-label fw-bold text-primary">{{ trans('langSearch') }}</span>
                        </div>
                        <div class="wizard-step-line flex-fill bg-secondary mx-2" style="height: 2px;"></div>
                        <div class="wizard-step text-center flex-fill position-relative opacity-50" id="wizard_step2">
                            <div class="step-badge rounded-circle bg-secondary text-white mx-auto d-flex align-items-center justify-content-center mb-1" style="width: 36px; height: 36px; font-weight: bold;">2</div>
                            <span class="step-label fw-bold text-secondary">{{ trans('langSelectCourses') ?? 'Επιλογή Μαθημάτων' }}</span>
                        </div>
                        <div class="wizard-step-line flex-fill bg-secondary mx-2" style="height: 2px;"></div>
                        <div class="wizard-step text-center flex-fill position-relative opacity-50" id="wizard_step3">
                            <div class="step-badge rounded-circle bg-secondary text-white mx-auto d-flex align-items-center justify-content-center mb-1" style="width: 36px; height: 36px; font-weight: bold;">3</div>
                            <span class="step-label fw-bold text-secondary">{{ trans('langActions') ?? 'Ενέργειες' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 1: Search Form -->
            <div id="step1_container" class="col-12">
                <div class="row">
                    <div class="col-lg-6 col-12">
                        <div class="form-wrapper form-edit border-0 px-0">
                            <form id="searchForm" role="form" class="form-horizontal" action="#" method="get">
                                <fieldset>
                                    <legend class="mb-0" aria-label="{{ trans('langForm') }}"></legend>

                                    <div class="form-group">
                                        <label for="formsearchtitle" class="col-sm-12 control-label-notes">{{ trans('langTitle') }}</label>
                                        <div class="col-sm-12">
                                            <input type="text" placeholder="{{ trans('langTitle') }}" class="form-control" id="formsearchtitle" name="formsearchtitle" value="">
                                        </div>
                                    </div>

                                    <div class="form-group mt-4">
                                        <label for="formsearchtype" class="col-sm-12 control-label-notes">{{ trans('langCourseVis') }}</label>
                                        <div class="col-sm-12">
                                            <select class="form-select" name="formsearchtype" id="formsearchtype">
                                                <option value="-1">{{ trans('langAllTypes') }}</option>
                                                <option value="2">{{ trans('langTypeOpen') }}</option>
                                                <option value="1">{{ trans('langTypeRegistration') }}</option>
                                                <option value="0">{{ trans('langTypeClosed') }}</option>
                                                <option value="4">{{ trans('langCourseActiveShort') }}</option>
                                                <option value="3">{{ trans('langCourseInactiveShort') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group mt-4">
                                        <label for="formprof" class="col-sm-12 control-label-notes">{{ trans('langTeachers') }}:</label>
                                        <div class="col-sm-12">
                                            <input type="text" placeholder="{{ trans('langTeachers') }}" class="form-control" id="formprof" name="formsearchprof" value="">
                                        </div>
                                    </div>

                                    <div class="form-group mt-4">
                                        <div class="col-sm-12 control-label-notes">{{ trans('langCreationDate') }}</div>
                                        <div class="row">
                                            <div class="col-6">
                                                {!! selection($reg_flag_data, 'reg_flag', '', 'class="form-select" aria-label="Date creation"') !!}
                                            </div>
                                            <div class="col-6">
                                                <input aria-label="{{ trans('langCreationDate') }}" class="form-control" id="id_date" name="date" type="text" value="" data-date-format="dd-mm-yyyy" placeholder="{{ trans('langCreationDate') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mt-4">
                                        <label for="dialog-set-value" class="col-sm-12 control-label-notes">{{ trans('langFaculty') }}</label>
                                        <div class="col-sm-12">
                                            {!! $html !!}
                                        </div>
                                    </div>

                                    <div class="form-group mt-5">
                                        <div class="col-12 d-flex justify-content-end align-items-center gap-2">
                                            <button class="btn submitAdminBtn" type="submit" name="search_submit" id="search_submit">
                                                {{ trans('langNext') ?? 'Επόμενο' }}<i class="fa-solid fa-arrow-right ms-1"></i>
                                            </button>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6 col-12 d-none d-md-none d-lg-block text-end">
                        <img class="form-image-modules" src="{!! get_form_image() !!}" alt="{{ trans('langImgFormsDes') }}">
                    </div>
                </div>
            </div>

            <!-- Bulk Actions & Table Form wrapping Step 2 and Step 3 -->
            <form id="bulkActionsForm" action="{{ $_SERVER['SCRIPT_NAME'] }}" method="post" class="w-100">
                {!! generate_csrf_token_form_field() !!}

                <!-- STEP 2: Course Selection Table -->
                <div id="step2_container" class="col-12 mt-3" style="display: none;">
                    <table id="course_results_table" class="table-default display" style="width: 100%;">
                        <thead>
                            <tr class="list-header">
                                <th class="text-center" width="5%"><input type="checkbox" id="select_all_courses"></th>
                                <th>{{ trans('langCourseCode') }}</th>
                                <th>{{ trans('langFaculty') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                    <!-- Step 2 Navigation Buttons -->
                    <div class="col-12 mt-4 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn cancelAdminBtn" id="step2_prev_btn">
                            <i class="fa-solid fa-arrow-left me-1"></i>{{ trans('langPrevious') ?? 'Προηγούμενο' }}
                        </button>
                        <button type="button" class="btn submitAdminBtn" id="step2_next_btn">
                            {{ trans('langNext') ?? 'Επόμενο' }}<i class="fa-solid fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Actions Selection -->
                <div id="step3_container" class="col-12 mt-3" style="display: none;">
                    <!-- Selected Courses Summary Badge -->
                    <div class="alert alert-info mb-4 d-flex align-items-center justify-content-between">
                        <div>
                            <i class="fa-solid fa-circle-info fa-lg me-2"></i>
                            <span>{{ trans('langSelectedCourses') ?? 'Έχουν επιλεγεί' }}: <strong id="selected_courses_count">0</strong> {{ trans('langCourses') ?? 'μαθήματα' }}</span>
                        </div>
                    </div>

                    <!-- Action Selector -->
                    <div class="card p-3 bg-light border mb-4">
                        <div class="d-flex align-items-center gap-2" style="max-width: 400px;">
                            <label for="bulk_action_select" class="form-label mb-0 fw-bold text-nowrap">{{ trans('langAction') }}:</label>
                            <select id="bulk_action_select" name="bulk_action" class="form-select">
                                <option value="refresh">{{ trans('langRefreshCourse') }}</option>
                                <option value="delete">{{ trans('langCourseDel') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Deletion Warning Alert -->
                    <div id="delete_warning_box" class="col-12 mb-4" style="display: none;">
                        <div class="alert alert-warning mb-0">
                            <i class="fa-solid fa-circle-xmark fa-lg me-2"></i>{{ trans('langByDel') }}
                        </div>
                    </div>

                    <!-- Refresh Options Checkboxes Section -->
                    <div id="refresh_options_box" class="col-12 mb-4" style="display: none;">
                        <div class="form-wrapper form-edit rounded border p-4 bg-light">
                            <div class="alert alert-info mb-4">
                                <i class="fa-solid fa-circle-info fa-lg me-2"></i>
                                <span>{{ trans('langRefreshInfo') }} {{ trans('langRefreshInfo_A') }}</span>
                            </div>

                            <!-- Users - Unregister -->
                            <div class="mb-4">
                                <div class="fw-bold mb-2">
                                    {{ trans('langUsers') }} - {{ trans('langUnCourse') }}
                                    <span class="help-block d-block fw-normal text-muted small">{{ trans('langUserDelCourseInfo') }}</span>
                                </div>
                                <div class="form-group mt-2">
                                    <div class="checkbox">
                                        <label class="label-container">
                                            <input type="checkbox" name="delusersinactive">
                                            <span class="checkmark"></span>
                                            {{ trans('langInactiveUsers') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group mt-3">
                                    <div class="checkbox">
                                        <label class="label-container">
                                            <input type="checkbox" name="delusersdate">
                                            <span class="checkmark"></span>
                                            {{ trans('langWithRegistrationDate') }}
                                        </label>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6 col-12">
                                            {!! selection(array('before' => trans('langBefore'), 'after' => trans('langAfter')), 'reg_flag', 'before', 'class="form-select"') !!}
                                        </div>
                                        <div class="col-md-6 col-12 mt-2 mt-md-0">
                                            <input aria-label="{{ trans('langDate') }}" class="form-control" type="text" name="reg_date" id="reg_date" value="{!! date("d-m-Y", time()) !!}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Announcements & Agenda -->
                            <div class="row">
                                <div class="col-md-6 col-12 mb-3">
                                    <div class="fw-bold mb-2">{{ trans('langAnnouncements') }}</div>
                                    <div class="checkbox">
                                        <label class="label-container">
                                            <input type="checkbox" name="delannounces">
                                            <span class="checkmark"></span>
                                            {{ trans('langAnnouncesDel') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12 mb-3">
                                    <div class="fw-bold mb-2">{{ trans('langAgenda') }}</div>
                                    <div class="checkbox">
                                        <label class="label-container">
                                            <input type="checkbox" name="delagenda">
                                            <span class="checkmark"></span>
                                            {{ trans('langAgendaDel') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Assignments & Exercises -->
                            <div class="row">
                                <div class="col-md-6 col-12 mb-3">
                                    <div class="fw-bold mb-2">{{ trans('langWorks') }}</div>
                                    <div class="checkbox mb-2">
                                        <label class="label-container">
                                            <input type="checkbox" name="hideworks">
                                            <span class="checkmark"></span>
                                            {{ trans('langHideWork') }}
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label class="label-container">
                                            <input type="checkbox" name="delworkssubs">
                                            <span class="checkmark"></span>
                                            {{ trans('langDelAllWorkSubs') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12 mb-3">
                                    <div class="fw-bold mb-2">{{ trans('langExercises') }}</div>
                                    <div class="checkbox">
                                        <label class="label-container">
                                            <input type="checkbox" name="purgeexercises">
                                            <span class="checkmark"></span>
                                            {{ trans('langPurgeExercisesResults') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Statistics & Blog -->
                            <div class="row">
                                <div class="col-md-6 col-12 mb-3">
                                    <div class="fw-bold mb-2">{{ trans('langUsage') }}</div>
                                    <div class="checkbox">
                                        <label class="label-container">
                                            <input type="checkbox" name="clearstats">
                                            <span class="checkmark"></span>
                                            {{ trans('langClearStats') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12 mb-3">
                                    <div class="fw-bold mb-2">{{ trans('langBlog') }}</div>
                                    <div class="checkbox">
                                        <label class="label-container">
                                            <input type="checkbox" name="delblogposts">
                                            <span class="checkmark"></span>
                                            {{ trans('langDelBlogPosts') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Wall -->
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="fw-bold mb-2">{{ trans('langWall') }}</div>
                                    <div class="checkbox">
                                        <label class="label-container">
                                            <input type="checkbox" name="delwallposts">
                                            <span class="checkmark"></span>
                                            {{ trans('langDelWallPosts') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Step 3 Navigation Buttons -->
                    <div class="col-12 mt-4 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn cancelAdminBtn" id="step3_prev_btn">
                            <i class="fa-solid fa-arrow-left me-1"></i>{{ trans('langPrevious') ?? 'Προηγούμενο' }}
                        </button>
                        <button type="submit" class="btn submitAdminBtn" name="bulk_submit" id="bulk_submit">
                            {{ trans('langSubmit') }}
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</main>
@endsection
