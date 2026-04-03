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

                        <div id='dialog' style='display:none;'>
                            {{ trans('langUsedInSeveralExercises') }}
                        </div>

                        <div class='form-wrapper mb-4'>
                            <form class='form-inline' role='form' name='qfilter' method='get' action='{{ $_SERVER['REQUEST_URI'] }}'>
                                <input type='hidden' name='course' value='{{ $course_code }}'>

                                @if (isset($fromExercise))_
                                    <input type='hidden' name='fromExercise' value='{{ $fromExercise }}'>
                                @endif

                                <div class='form-group'>
                                    <select onChange = 'document.qfilter.submit();' name='exerciseId' class='form-select'>
                                        {!! $exercise_options !!}
                                    </select>
                                </div>
                                <div class='form-group mt-3'>
                                    <select onChange = 'document.qfilter.submit();' name='categoryId' class='form-select'>
                                        {!! $q_cat_options !!}
                                    </select>
                                </div>
                                <div class='form-group mt-3'>
                                    <select onChange = 'document.qfilter.submit();' name='difficultyId' class='form-select'>
                                        <option value='-1' @if (isset($difficultyId) && $difficultyId == -1) selected='selected' @endif>-- {{ trans('langQuestionAllDiffs') }} --</option>
                                        <option value='0' @if (isset($difficultyId) && $difficultyId == 0) selected='selected' @endif>{{ trans('langQuestionNotDefined') }}</option>
                                        <option value='1' @if (isset($difficultyId) && $difficultyId == 1) selected='selected' @endif>{{ trans('langQuestionVeryEasy') }}</option>
                                        <option value='2' @if (isset($difficultyId) && $difficultyId == 2) selected='selected' @endif>{{ trans('langQuestionEasy') }}</option>
                                        <option value='3' @if (isset($difficultyId) && $difficultyId == 3) selected='selected' @endif>{{ trans('langQuestionModerate') }}</option>
                                        <option value='4' @if (isset($difficultyId) && $difficultyId == 4) selected='selected' @endif>{{ trans('langQuestionDifficult') }}/option>
                                        <option value='5' @if (isset($difficultyId) && $difficultyId == 5) selected='selected' @endif>{{ trans('langQuestionVeryDifficult') }}</option>
                                    </select>
                                </div>

                                <div class='form-group mt-3'>
                                    {!!  $selection_question_types !!}
                                </div>

                            </form>
                        </div>

                        <div class='table-responsive'>
                            <table class='table-default' id='questions_{{ $course_code }}'>
                                <thead>
                                    <tr class='list-header'>
                                        <th>{{ trans('langQuesList') }}</th>
                                        <th aria-label='{{ trans('langSettingSelect') }}'></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {!! $tr_content !!}
                                </tbody>
                            </table>
                        </div>

                        {{-- Modal --}}
                        <div class='modal fade' id='modalWarning' tabindex='-1' role='dialog' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <div class='modal-title'>{{ trans('langNote') }}</div>
                                        <button type='button' class='close' data-bs-dismiss='modal'></button>

                                    </div>
                                    <div class='modal-body'>
                                        {!! trans('langUsedInSeveralExercises') !!}
                                    </div>
                                    <div class='modal-footer'>
                                        <a href='#' id='modifyAll' class='btn submitAdminBtn'>{{ trans('langModifyInAllExercises') }}</a>
                                        <a href='#' id='modifyOne' class='btn submitAdminBtn ms-1'>{{ trans('langModifyInQuestionPool') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type='text/javascript'>
        $(function() {
            $('#questions_{{ $course_code }}').DataTable ({
                'stateSave': true,
                'fnDrawCallback': function (settings) { typeof MathJax !== 'undefined' && MathJax.typeset(); },
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'order' : [[1, 'desc']],
                'lengthMenu': [10, 20, 30, -1],
                'oLanguage': {
                    'lengthLabels': {
                        '-1': '{{ trans('langAllOfThem') }}'
                    },
                    'sLengthMenu': '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
                    'sZeroRecords': '{{ trans('langNoResult') }}',
                    'sEmptyTable': '{{ trans('langNoResult') }}',
                    'sInfo': '{{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langTotalResults') }}',
                    'sInfoEmpty':    '',
                    'sInfoFiltered': '',
                    'sInfoPostFix':  '',
                    'sSearch':       '',
                    'sUrl':          '',
                    'oPaginate': {
                        'sFirst':    '&laquo;',
                        'sPrevious': '&lsaquo;',
                        'sNext':     '&rsaquo;',
                        'sLast':     '&raquo;'
                    }
                }
            });

            $('.dt-search input').attr({
                class : 'form-control input-sm mb-3 me-3',
                placeholder : '{{ trans('langSearch') }}...'
            });
            $('.dt-search label').attr('aria-label', '{{ trans('langSearch') }}');

            $(document).on('click', '.warnLink', function(e){
                var modifyAllLink = $(this).attr('href');
                var modifyOneLink = modifyAllLink.concat('&clone=true');
                $('a#modifyAll').attr('href', modifyAllLink);
                $('a#modifyOne').attr('href', modifyOneLink);
            });

            $(document).on('click', '.previewQuestion', function(e) {
                e.preventDefault();
                var qid = $(this).data('qid'),
                    nbr = $(this).data('nbr'),
                    editUrl = $(this).data('editurl'),
                    url = '{{ $urlAppend }}modules/exercise/question_preview.php?course={{ $course_code }}&question=' + qid;
                $.ajax({
                    url: url,
                    success: function(data) {
                        var dialog = bootbox.dialog({
                            message: data,
                            title: '{{ trans('langQuestionPreview') }}',
                            onEscape: true,
                            backdrop: true,
                            buttons: {
                                edit: {
                                    label: '{{ trans('langEditChange') }}',
                                    className: 'submitAdminBtn',
                                    callback: function () {
                                        if (nbr > 1) {
                                            $('#modalWarning').modal('show');
                                        } else {
                                            window.location.href = editUrl;
                                        }
                                    }
                                },
                                success: {
                                    label: '{{ trans('langClose') }}',
                                    className: 'cancelAdminBtn',
                                },
                            }
                        });
                        dialog.init(function() {
                            typeof MathJax !== 'undefined' && MathJax.typeset();
                        });
                    }
                });
            });

            $('.warnDup').on('click', function(e) {
                e.preventDefault();
                bootbox.dialog({
                    title: '{{ trans('langCreateDuplicateIn') }}',
                    message: '<form action="{{ $_SERVER['SCRIPT_NAME'] }}" method="post" id="clone_pool_form">'+
                                '<select class="form-select" id="course_id" name="clone_pool_to_course_id">'+
                                    {!! $courses_options !!} +
                                '</select>'+
                              '</form>',
                    buttons: {
                        cancel: {
                            label: '{{ trans('langCancel') }}',
                            className: 'cancelAdminBtn'
                        },
                        success: {
                            label: '{{ trans('langCreateDuplicate') }}',
                            className: 'submitAdminBtn',
                            callback: function (d) {
                                $('#clone_pool_form').attr('action', '{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&clone_pool=1');
                                $('#clone_pool_form').submit();
                            }
                        }
                    }
                });
            });

        });
    </script>

@endsection