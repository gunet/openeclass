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

                        {{-- exercise info --}}
                        <div class='col-12 mb-4'>
                            <div class='card panelCard border-card-left-default px-3 py-2 h-100'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                    <h3>{{ $exerciseTitle }}</h3>
                                </div>
                                <div class='card-body'>
                                    <div class='row row-cols-1 g-3'>
                                        @if ($exerciseDescription !== '')
                                            <div class='col-sm-12'>
                                                {!! standard_text_escape($exerciseDescription) !!}
                                            </div>
                                            <hr>
                                        @endif

                                        @if ($periodLabel)
                                            <div class='col-12'>
                                                {!! $periodLabel !!} <strong>{{ $periodInfo }}</strong>
                                            </div>
                                        @endif

                                        <div class='col-sm-12'>
                                            @switch ($exerciseType)
                                                @case (MULTIPLE_PAGE_TYPE)
                                                    {{ trans('langSequentialExercise') }}
                                                @break
                                                @case (ONE_WAY_TYPE)
                                                    {{ trans('langOneWayExercise') }}
                                                @break
                                                @default
                                                    {{ trans('langSimpleExercise') }}
                                                @break
                                            @endswitch
                                        </div>
                                        @if ($exerciseTempSave == 1)
                                            <div class='col-12 '><strong>{{ trans('langTemporarySave') }}:</strong> {{ trans('langYes') }}</div>
                                        @endif
                                        @if ($exerciseTimeConstraint > 0)
                                            <div class='col-12 '>
                                                {{ trans('langExerciseConstrain') }}: <strong> {{ $exerciseTimeConstraint  }}
                                                @if ($exerciseTimeConstraint == 1)
                                                    {{ trans('langminute') }}
                                                @endif
                                                {{ trans('langExerciseConstrainUnit') }}</strong>
                                            </div>
                                        @endif
                                        @if ($exerciseAttemptsAllowed > 0)
                                            <div class='col-12 '>
                                                {{ trans('langExerciseAttemptsAllowed') }}:
                                                <strong>
                                                    {{ $exerciseAttemptsAllowed }} {{ trans('langExerciseAttemptsAllowedUnit') }}
                                                </strong>
                                            </div>
                                        @endif

                                        <div class='col-sm-12 '>
                                            @switch ($displayResults)
                                                @case(0)
                                                    {{ trans('langAnswersNotDisp') }}
                                                @break
                                                @case(1)
                                                    {{ trans('langAnswersDisp') }}
                                                @break
                                                case(3)
                                                    {{ trans('langAnswersDispLastAttempt') }}
                                                @break
                                                @case(4)
                                                    {{ trans('langAnswersDispEndDate') }}
                                                @break
                                           @endswitch
                                        </div>
                                        <div class='col-sm-12 '>
                                            @switch ($displayScore)
                                                @case(0)
                                                    {{ trans('langScoreNotDisp') }}
                                                @break;
                                                @case(1)
                                                    {{ trans('langScoreDisp') }}
                                                @break;
                                                @case(3)
                                                    {{ trans('langScoreDispLastAttempt') }}
                                                @break;
                                                @case(4)
                                                    {{ trans('langScoreDispEndDate') }}
                                                @break;
                                            @endswitch
                                        </div>

                                        @if ($exerciseAssignToSpecific > 0)
                                            <div class='col-sm-12 '>
                                                {{ trans('langWorkAssignTo') }}:
                                                <strong>
                                                    @switch ($exerciseAssignToSpecific)
                                                        @case(1)
                                                            {{ trans('langWorkToUser') }}
                                                        @break
                                                        @case(2)
                                                            {{ trans('langWorkToGroup') }}
                                                        @break
                                                    @endswitch
                                                </strong>
                                            </div>
                                        @endif

                                        @if ($tags_list)
                                            <div class='col-sm-12 '>
                                                {{ trans('langTags') }}: {!! $tags_list !!}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id='operations_container'>
                            {!! $actionBarButtons !!}
                        </div>

                        @if ($nbrQuestions)
                            <div class='col-12'>
                                <div id='RandomizationForm' class='form-wrapper form-edit rounded'>
                                    <form class='form-horizontal' role='form' method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&exerciseId={{ $exerciseId }}">
                                        <div class='form-group'>
                                            <div class='col-sm-12'>
                                                <div class='checkbox' id='divcheckboxShuffleQuestions'>
                                                    <label class='form-control-static label-container' aria-label='{{ trans('langSelect') }}'>
                                                        <input id='checkboxShuffleQuestions' type='checkbox' name='enableShuffleQuestions' value='1' @if($shuffleQuestions == 1) checked @endif>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langShuffleQuestions') }}
                                                    </label>
                                                </div>
                                            </div>
                                            <div class='col-sm-12'>
                                                <div class='checkbox' id='divcheckboxRandomQuestions'>
                                                    <label class='form-control-static label-container' aria-label='{{ trans('langSelect') }}'>
                                                        <input id='checkboxRandomQuestions'type='checkbox' name='enableRandomQuestions' value='1' @if($randomQuestions > 0) checked @endif">
                                                        <span class='checkmark'></span>
                                                        {{ trans('langChooseRandomQuestions') }}
                                                    </label>
                                                </div>
                                                <label class='col-12 control-label-notes mt-2' for='inputRandomQuestions'>
                                                    {{ trans('langsQuestions') }}
                                                </label>
                                                <div class='col-md-6 col-12'>
                                                    <input id='inputRandomQuestions' class='form-control' type='text' name='numberOfRandomQuestions' value='@if ($randomQuestions > 0) {{ $randomQuestions }} @endif'>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <div class='col-sm-12'>
                                                <input class='btn submitAdminBtn' type='submit' value='{{ trans('langSubmit') }}' name='shuffleQuestions'>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- questions list --}}
                            <div class='table-responsive'>
                                <table class='table-default'>
                                    <thead class='list-header'>
                                    <tr>
                                        <th style='width:6%;' class='count-col'>#</th>
                                        <th>{{ trans('langQuestionList') }}
                                        @if ($randomQuestions > 0)
                                            <small>
                                                <span class='help-block'>
                                                    {{ trans('langViewShow') }} {{ $randomQuestions  }} {{ trans('langFromRandomQuestions') }}
                                                </span>
                                            </small>
                                        @endif
                                        </th>
                                        <th aria-label='{{ trans('langSettingSelect') }}'></th>
                                    </tr>
                                    </thead>
                                    <tbody id='q_sort'>
                                        @foreach ($questionList as $id)
                                            {!! questionLegend($id, $exerciseId) !!}
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class='modal fade' id='modalWarning' tabindex='-1' role='dialog' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <div class='modal-title'>
                                                {{ trans('langNote') }}
                                            </div>
                                            <button type='button' class='close' data-bs-dismiss='modal'><span aria-hidden='true'>&times;</span><span class='sr-only'>{{ trans('langClose') }}</span></button>
                                        </div>
                                        <div class='modal-body'>
                                            {!! trans('langUsedInSeveralExercises2') !!}
                                        </div>
                                        <div class='modal-footer'>
                                            <a href='#' id='modifyAll' class='btn submitAdminBtn'>{{ trans('langModifyInAllExercises') }}</a>
                                            <a href='#' id='modifyOne' class='btn submitAdminBtn'>{{ trans('langModifyInThisExercise') }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @else
                            <div class='col-12'>
                                <div class='alert alert-warning'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoQuestion') }}</span>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type='text/javascript'>

        function RandomizationForm() {
            var formRandomQuestions = '{{ $formRandomQuestions }}';
            if (formRandomQuestions == 'disable') {
                $('#RandomizationForm *').prop('disabled', true);
            }
        }

        $(function() {
            if (typeof(q_sort) !== 'undefined') {
                Sortable.create(q_sort,{
                    handle: '.fa-arrows',
                    animation: 150,
                    onEnd: function (evt) {

                        var itemEl = $(evt.item);

                        var idReorder = itemEl.attr('data-id');
                        var prevIdReorder = itemEl.prev().attr('data-id');

                        $.ajax({
                            type: 'post',
                            dataType: 'text',
                            data: {
                                toReorder: idReorder,
                                prevReorder: prevIdReorder,
                            }
                        });
                    }
                });
            }
            RandomizationForm();
            $('#checkboxShuffleQuestions').click(function() {
                if ($(this).is(':checked')) {
                    $('#checkboxRandomQuestions').prop('disabled', true);
                    $('#inputRandomQuestions').prop('disabled', true);
                    $('#divcheckboxRandomQuestions').addClass('not_visible');
                } else {
                    $('#inputRandomQuestions').prop('disabled', false);
                    $('#checkboxRandomQuestions').prop('disabled', false);
                    $('#divcheckboxRandomQuestions').removeClass('not_visible');
                }
            });
            $('#checkboxRandomQuestions').click(function() {
                if ($(this).is(':checked')) {
                    $('#checkboxShuffleQuestions').prop('disabled', true);
                    $('#divcheckboxShuffleQuestions').addClass('not_visible');
                } else {
                    $('#checkboxShuffleQuestions').prop('disabled', false);
                    $('#divcheckboxShuffleQuestions').removeClass('not_visible');
                }
            });
            $('.questionSelection').click( function(e){
                e.preventDefault();
                bootbox.dialog({
                        title: '{{ trans('langWithCriteria') }}',
                        message: '<div class=\"row\">' +
                                    '<div class=\"col-md-12\">' +
                                        '<form class=\"form-horizontal\"> ' +
                                            '<h4>{{ trans('langSelectionRule') }}</h4>' +
                                            '<div class=\"form-group\">' +
                                                '<div class=\"row\">'+
                                                    '<div class=\"col-sm-4\">' +
                                                        '<select name=\"category\" class=\"form-select\" id=\"cat\">{!! $cat_options !!}</select>' +
                                                    '</div>' +
                                                    '<div class=\"col-sm-4\">' +
                                                        '<select name=\"difficulty\" class=\"form-select\" id=\"diff\">' +
                                                            '<option value=\"-1\">-- {{ trans('langQuestionAllDiffs') }} --</option>'+
                                                            '<option value=\"0\">-- {{ trans('langQuestionNotDefined') }} --</option>'+
                                                            '<option value=\"1\">{{ trans('langQuestionVeryEasy') }} </option>'+
                                                            '<option value=\"2\">{{ trans('langQuestionEasy') }} </option>'+
                                                            '<option value=\"3\">{{ trans('langQuestionModerate') }} </option>'+
                                                            '<option value=\"4\">{{ trans('langQuestionDifficult') }}</option>'+
                                                            '<option value=\"5\">{{ trans('langQuestionVeryDifficult') }}</option>'+
                                                        '</select>' +
                                                    '</div>' +
                                                    '<div class=\"col-sm-4\">' +
                                                        '<input class=\"form-control\" type=\"text\" id=\"q_num\" name=\"q_num\" value=\"\">{{ trans('langQuestions') }}' +
                                                    '</div>' +
                                                '</div>' +
                                            '</div>' +
                                        '</form>' +
                                    '</div>' +
                                '</div>',
                        buttons: {
                            success: {
                                label: '{{ trans('langSelection') }}',
                                className: 'submitAdminBtn',
                                callback: function () {
                                    var catValue = $('select#cat').val();
                                    var diffValue = $('select#diff').val();
                                    var qnumValue = $('input#q_num').val();
                                    $.ajax({
                                        type: 'POST',
                                        url: '',
                                        datatype: 'json',
                                        data: {
                                            action: 'add_questions',
                                            category: catValue,
                                            difficulty: diffValue,
                                            qnum: qnumValue
                                        },
                                        success: function(data){
                                            window.location.href = '{!! $_SERVER['REQUEST_URI'] !!}';
                                        },
                                        error: function(xhr, textStatus, error){
                                            console.log(xhr.statusText);
                                            console.log(textStatus);
                                            console.log(error);
                                        }
                                    });
                                }
                            }
                        }
                    }
                ).find('div.modal-dialog').addClass('modal-lg');
            });
            $('.randomWithCriteria').click(function(e) {
                e.preventDefault();
                bootbox.dialog({
                    title: '<span class=\"fa fa-random\" style=\"margin-right: 10px;\"></span>{{ trans('langRandomQuestionsWithCriteria') }}',
                    message: '<div class=\"row\">' +
                                '<form class=\"form-horizontal\">' +
                                    '<div class=\"col-12\">' +
                                        '<div class=\"row\" style=\"margin-bottom: 10px;\">' +
                                            '<div class=\"col-4 control-label-notes\">{{ trans('langQuestionDiffGrade') }}</div>' +
                                            '<div class=\"col-4 control-label-notes\">{{ trans('langQuestionCats') }}</div>' +
                                            '<div class=\"col-4 control-label-notes\">{{ trans('langNumQuestions') }}</div>' +
                                        '</div>'+
                                        '<div class=\"row form-group\">' +
                                            '<div class=\"col-4\">' +
                                                '<select id=\"difficultyId\" class=\"form-select\">' +
                                                    '<option value=\"0\">  ----  </option>' +
                                                    '<option value=\"1\">{{ trans('langQuestionVeryEasy') }}</option>' +
                                                    '<option value=\"2\">{{ trans('langQuestionEasy') }}</option>' +
                                                    '<option value=\"3\">{{ trans('langQuestionModerate') }}</option>' +
                                                    '<option value=\"4\">{{ trans('langQuestionDifficult') }}</option>' +
                                                    '<option value=\"5\">{{ trans('langQuestionVeryDifficult') }}</option>' +
                                                '</select>' +
                                            '</div>' +
                                            '<div class=\"col-4\">' +
                                                '<select id=\"categoryId\" class=\"form-select\">{!! $cat_options_2 !!}</select>' +
                                            '</div>' +
                                            '<div class=\"col-4\">' +
                                                '<input class=\"form-control\" type=\"text\" id=\"questionRandomDrawn\" value=\"\">' +
                                            '</div>' +
                                        '</div>' +
                                    '</div>' +
                                '</form>' +
                            '</div>',
                    buttons: {
                        success: {
                            label: '{{ trans('langSubmit') }}',
                            className: 'submitAdminBtn',
                            callback: function () {
                                var difficultyIdValue = $('select#difficultyId').val();
                                var categoryIdValue = $('select#categoryId').val();
                                var questionRandomDrawnValue = $('input#questionRandomDrawn').val();
                                $.ajax({
                                    type: 'POST',
                                    url: '',
                                    datatype: 'json',
                                    data: {
                                        action: 'random_criteria',
                                        difficultyId: difficultyIdValue,
                                        categoryId: categoryIdValue,
                                        questionRandomDrawn: questionRandomDrawnValue
                                    },
                                    success: function(data) {
                                        window.location.href = '{!! $_SERVER['REQUEST_URI'] !!}';
                                    },
                                    error: function(xhr, textStatus, error){
                                        console.log(xhr.statusText);
                                        console.log(textStatus);
                                        console.log(error);
                                    }
                                });
                            }
                        }
                    }
                }).find('div.modal-dialog').addClass('modal-lg');
            });
            $('.menu-popover').on('shown.bs.popover', function () {
                $('.warnLink').on('click', function(e){
                    var modifyAllLink = $(this).attr('href');
                    var modifyOneLink = modifyAllLink.concat('&clone=true');
                    $('a#modifyAll').attr('href', modifyAllLink);
                    $('a#modifyOne').attr('href', modifyOneLink);
                });
            });

            $(document).on('click', '.previewQuestion', function(e) {
                e.preventDefault();
                var qid = $(this).data('qid'),
                    nbr = $(this).data('nbr'),
                    editUrl = $(this).data('editurl'),
                    deleteUrl = $(this).data('deleteurl'),
                    url = '{{ $urlAppend }}' + 'modules/exercise/question_preview.php?course={{ $course_code }}&question=' + qid;
                $.ajax({
                    url: url,
                    success: function(data) {
                        var dialog = bootbox.dialog({
                            message: data,
                            title: '{{ trans('langQuestionPreview') }}' + qid,
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
        });
    </script>
@endsection
