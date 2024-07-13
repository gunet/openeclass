@extends('layouts.default')

@push('head_scripts')
    <script src="APIWrapper.js"></script>
    <script src="scores.js"></script>
@endpush

@section('content')

<div class="col-12 main-section">
<div class='container module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active col_maincontent_active_module_content">

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

                    <div id="claroBody">
                        <form id="quiz">
                            <table class="table-default">
                                <tr>
                                    <td>


                                    {{-- questions --}}


                                    <?php
                                        // Keep track of raw scores (ponderation) for each question
                                        $questionPonderationList = array();
                                        // Keep track of correct texts for fill-in type questions
                                        $fillAnswerList = array();
                                        // Counter used to generate the elements' id. Incremented after every <input> or <select>
                                        $idCounter = 0;
                                        // Display each question
                                        $questionCount = 0;
                                    ?>


                                    @foreach ($questions as $question)
                                        <?php
                                            $questionCount++;
                                            $questionPonderationList[$question->selectId()] = $question->selectWeighting();
                                        ?>
                                        @if ($question->selectType() == FREE_TEXT)
                                            @continue
                                        @endif

                                        <table class="table-default mb-4">
                                            <tr>
                                                <th valign="top" colspan="2">{{ trans('langQuestion') }}&nbsp;{{ $questionCount }}</th>
                                            </tr>
                                            <tfoot>
                                                <tr>
                                                    <td valign="top" colspan="2">{{ $question->selectTitle() }}</td>
                                                </tr>
                                                <tr>
                                                    <td valign="top" colspan="2"><i>{!! parse_user_text($question->selectDescription()) !!}</i></td>
                                                </tr>


                                                {{-- answers --}}


                                                <?php
                                                    $answer = new Answer($question->selectId());
                                                    $answerCount = $answer->selectNbrAnswers();
                                                    // Used for matching:
                                                    $letterCounter = 'A';
                                                    $choiceCounter = 1;
                                                    $Select = array();
                                                ?>


                                                @for ($answerId = 1; $answerId <= $answerCount; $answerId++)
                                                        @if ($question->selectType() == UNIQUE_ANSWER || $question->selectType() == TRUE_FALSE)

                                                            <tr>
                                                                <td width="5%" align="center">
                                                                    <input type="radio" name="unique_{{ $questionCount }}_x"
                                                                        id="scorm_{{ $idCounter }}"
                                                                        value="{{ $answer->selectWeighting($answerId) }}">
                                                                </td>
                                                                <td width="95%"><label for="scorm_{{ $idCounter }}">{!!  strip_tags($answer->selectAnswer($answerId)) !!}</label></td>
                                                            </tr>

                                                            <?php
                                                                $idCounter++;
                                                            ?>
                                                        @elseif ($question->selectType() == MULTIPLE_ANSWER)

                                                            <tr>
                                                                <td width="5%" align="center">
                                                                    <div class='checkbox'>
                                                                        <label class='label-container'>
                                                                            <input type="checkbox" name="multiple_{{ $questionCount }}_{{ $answerId }}"
                                                                                id="scorm_{{ $idCounter }}"
                                                                                value="{{ $answer->selectWeighting($answerId) }}">
                                                                                <span class='checkmark'></span>
                                                                        </label>
                                                                    </div>
                                                                </td>
                                                                <td width="95%"><label for="scorm_{{ $idCounter }}">{!!  strip_tags($answer->selectAnswer($answerId)) !!}</label></td>
                                                            </tr>

                                                            <?php
                                                                $idCounter++;
                                                            ?>

                                                        @elseif ($question->selectType() == FILL_IN_BLANKS || $question->selectType() == FILL_IN_BLANKS_TOLERANT)

                                                            <tr>
                                                                <td colspan="2">

                                                                <?php
                                                                    // We must split the text, to be able to treat each input independently
                                                                    // separate the text and the scorings
                                                                    $explodedAnswer = explode('::', strip_tags($answer->selectAnswer($answerId)));
                                                                    $phrase = (isset($explodedAnswer[0])) ? $explodedAnswer[0] : '';
                                                                    $weighting = (isset($explodedAnswer[1])) ? $explodedAnswer[1] : '';
                                                                    $fillType = (!empty($explodedAnswer[2])) ? $explodedAnswer[2] : 1;
                                                                    // default value if value is invalid
                                                                    if ($fillType != TEXTFIELD_FILL && $fillType != LISTBOX_FILL) {
                                                                        $fillType = TEXTFIELD_FILL;
                                                                    }
                                                                    $wrongAnswers = (!empty($explodedAnswer[3])) ? explode('[', $explodedAnswer[3]) : array();
                                                                    // get the scorings as a list
                                                                    $fillScoreList = explode(',', $weighting);
                                                                    $fillScoreCounter = 0;
                                                                    //listbox
                                                                    if ($fillType == LISTBOX_FILL) {
                                                                        // get the list of propositions (good and wrong) to display in lists
                                                                        // add wrongAnswers in the list
                                                                        $answerList = $wrongAnswers;
                                                                        // add good answers in the list
                                                                        // we save the answer because it will be modified
                                                                        $temp = $phrase;
                                                                        while (1) {
                                                                            // quits the loop if there are no more blanks
                                                                            if (($pos = strpos($temp, '[')) === false) {
                                                                                break;
                                                                            }
                                                                            // removes characters till '['
                                                                            $temp = substr($temp, $pos + 1);
                                                                            // quits the loop if there are no more blanks
                                                                            if (($pos = strpos($temp, ']')) === false) {
                                                                                break;
                                                                            }
                                                                            // stores the found blank into the array
                                                                            $answerList[] = substr($temp, 0, $pos);
                                                                            // removes the character ']'
                                                                            $temp = substr($temp, $pos + 1);
                                                                        }
                                                                        // alphabetical sort of the array
                                                                        natcasesort($answerList);
                                                                    }
                                                                    // Split after each blank
                                                                    $responsePart = explode(']', $phrase);
                                                                    $acount = 0;
                                                                ?>

                                                                    @foreach ($responsePart as $part)

                                                                        <?php
                                                                            // Split between text and (possible) blank
                                                                            if (strpos($part, '[') !== false) {
                                                                                list($rawtext, $blankText) = explode('[', $part);
                                                                            } else {
                                                                                $rawtext = $part;
                                                                                $blankText = "";
                                                                            }
                                                                        ?>

                                                                        {{ $rawtext }}

                                                                        {{-- If there's a blank to fill-in after the text (this is usually not the case at the end) --}}
                                                                        @if (!empty($blankText))

                                                                            <?php
                                                                                $name = 'fill_' . $questionCount . '_' . $acount;
                                                                                // Keep track of the correspondance between element's name and correct value + scoring
                                                                                $fillAnswerList[$name] = array($blankText, $fillScoreList[$fillScoreCounter]);
                                                                            ?>

                                                                            @if ($fillType == LISTBOX_FILL)

                                                                                <select class='form-select' name="fill_{{ $questionCount }}_{{ $acount }}" id="scorm_{{ $idCounter }}">
                                                                                    <option value="">&nbsp;</option>

                                                                                    @foreach ($answerList as $answer)
                                                                                        <option value="{!! htmlspecialchars($answer) !!}">{{ $answer }}</option>
                                                                                    @endforeach

                                                                                </select>
                                                                            @else
                                                                                <input class='form-control' type="text" name="fill_{{ $questionCount }}_{{ $acount }}" size="10" id="scorm_{{ $idCounter }}"></br>
                                                                            @endif

                                                                            <?php
                                                                                $fillScoreCounter++;
                                                                                $idCounter++;
                                                                            ?>
                                                                        @endif

                                                                        <?php
                                                                            $acount++;
                                                                        ?>
                                                                    @endforeach

                                                                </td>
                                                            </tr>

                                                        @elseif ($question->selectType() == MATCHING)

                                                            @if (!$answer->isCorrect($answerId))
                                                                <?php
                                                                    // Add the option as a possible answer.
                                                                    $Select[$answerId] = strip_tags($answer->selectAnswer($answerId));
                                                                ?>
                                                            @else
                                                                <tr>
                                                                    <td colspan="2">
                                                                        <table border="0" cellpadding="0" cellspacing="0" width="99%">
                                                                            <tr>
                                                                                <td width="40%" valign="top"><b>{{ $choiceCounter }}.</b>{!!  strip_tags($answer->selectAnswer($answerId)) !!}</td>
                                                                                <td width="20%" valign="top">&nbsp;
                                                                                    <select class='form-select' name="matching_{{ $questionCount }}_{{ $answerId }}" id="scorm_{{ $idCounter }}">
                                                                                        <option value="0">--</option>

                                                                                        <?php
                                                                                            $idCounter++;
                                                                                            // fills the list-box
                                                                                            $letter = 'A';
                                                                                        ?>

                                                                                        @foreach ($Select as $key => $val)
                                                                                            <?php $scoreModifier = ( $key == $answer->isCorrect($answerId) ) ? $answer->selectWeighting($answerId) : 0; ?>
                                                                                            <option value="{{ $scoreModifier }}">{{ $letter++ }}</option>
                                                                                        @endforeach

                                                                                    </select>
                                                                                </td>
                                                                                <td width="40%" valign="top">

                                                                                    @if (isset($Select[$choiceCounter]))
                                                                                        <b>{{ $letterCounter }}.</b> {{ $Select[$choiceCounter] }}
                                                                                    @endif

                                                                                    &nbsp;
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                <?php
                                                                    // Done with this one
                                                                    $letterCounter++;
                                                                    $choiceCounter++;
                                                                ?>

                                                                {{-- If the left side has been completely displayed : --}}
                                                                @if ($answerId == $answerCount)
                                                                    {{-- Add all possibly remaining answers to the right --}}
                                                                    @while (isset($Select[$choiceCounter]))
                                                                        <tr>
                                                                            <td colspan="2">
                                                                                <table border="0" cellpadding="0" cellspacing="0" width="99%">
                                                                                    <tr>
                                                                                        <td width="40%">&nbsp;</td>
                                                                                        <td width="20%">&nbsp;</td>
                                                                                        <td width="40%"><b>{{ $letterCounter }}.</b> {{ $Select[$choiceCounter] }}</td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                        </tr>

                                                                        <?php
                                                                            $letterCounter++;
                                                                            $choiceCounter++;
                                                                        ?>
                                                                    @endwhile
                                                                @endif

                                                            @endif

                                                        @endif
                                                @endfor

                                            </tfoot>
                                        </table>

                                    @endforeach  {{-- end of questions --}}


                                    </td>
                                </tr>
                                <tr>
                                    <td align="center"><br><input class="btn submitAdminBtn" type="button" value="{{ trans('langOk') }}" onClick="calcScore()"></td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
   
</div>
</div>

@endsection

@push('head_scripts')
    <script type='text/javascript'>
        var raw_to_pass = 50;
        var weighting = {!! array_sum($questionPonderationList) !!};
        var rawScore;
        var scoreCommited = false;
        var showScore = true;
        var fillAnswerList = new Array();

        @foreach ($fillAnswerList as $key => $val)
            fillAnswerList['{{ $key }}'] = new Array('{{ $val[0] }}', '{{ $val[1] }}');
        @endforeach

        function calcScore()
        {
            if( !scoreCommited )
            {
                rawScore = CalculateRawScore(document, {{ $idCounter }}, fillAnswerList);
                var score = Math.max(Math.round(rawScore * 100 / weighting), 0);
                var oldScore = doGetValue("cmi.score.raw");

                doSetValue("cmi.score.max", weighting);
                doSetValue("cmi.score.min", 0);

                computeTime();

                if (score > oldScore) // Update only if score is better than the previous time.
                {
                    doSetValue("cmi.score.raw", rawScore);
                }

                var oldStatus = doGetValue( "cmi.success_status" )
                if (score >= raw_to_pass)
                {
                    doSetValue("cmi.success_status", "passed");
                }
                else if (oldStatus != "passed" ) // If passed once, never mark it as failed.
                {
                    doSetValue("cmi.success_status", "failed");
                }

                doCommit();
                doTerminate();
                scoreCommited = true;
                if(showScore) alert('{!! clean_str_for_javascript($langScore) !!} :\n' + rawScore + '/' + weighting + '\n' + '{!! clean_str_for_javascript($langExerciseDone) !!}');
            }
        }
    </script>
@endpush