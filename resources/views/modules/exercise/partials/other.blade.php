            <table class='table-default graded'>
                <tr class='active'>
                    <td colspan='{{ $question->colspanByType() }}'>
                        <b><u>{{ trans('langQuestion') }}</u>: {{ $key+1 }}</b>
                    </td>
                </tr>
                <tr>
                    <td colspan='{{ $question->colspanByType() }}'>
                    @if ($question)
                        <b>{!! q_math($question->question) !!}</b>
                        <br>
                        {!! standard_text_escape($question->selectParsedDescription()) !!}
                        <br><br>
                    @else
                        <div class='alert alert-warning'>{{ trans('langQuestionAlreadyDeleted') }}</div>
                    @endif
                    </td>
                </tr>
                @if (file_exists($picturePath . '/quiz-' . $question->id))
                    <tr>
                        <td class='text-center' colspan='{{ $question->colspanByType() }}'>
                            <img src='../../{{ $picturePath }}/quiz-{{ $question->id }}'>
                        </td>
                    </tr>
                @endif
                @if ($showResults && !empty($question->user_choice))
                    @if (in_array($question->type, [UNIQUE_ANSWER, MULTIPLE_ANSWER, TRUE_FALSE]))
                        <tr class='even'>
                            <td width='50' valign='top'><b>{{ trans('langChoice') }}</b></td>
                            <td width='50' class='center' valign='top'><b>{{ trans('langExpectedChoice') }}</b></td>
                            <td valign='top'><b>{{ trans('langAnswer') }}</b></td>
                            <td valign='top'><b>{{ trans('langComment') }}</b></td>
                        </tr>
                    @elseif (in_array($question->type, [FILL_IN_BLANKS, FILL_IN_BLANKS_TOLERANT, FREE_TEXT]))
                        <tr class='active'>
                            <td><b>{{ trans('langAnswer') }}</b></td>
                        </tr>
                    @else
                        <tr class='even'>
                            <td><b>{{ trans('langElementList') }}</b></td>
                            <td><b>{{ trans('langCorrespondsTo') }}</b></td>
                        </tr>
                    @endif
                @endif
                         
                @foreach ($question->answers->selectAnswers() as $key => $answer)
                    @if ($showResults)
                        @if ($question->type != MATCHING || $question->answers->isCorrect($key))
                            @if (in_array($question->type, [UNIQUE_ANSWER, MULTIPLE_ANSWER, TRUE_FALSE]))
                                <tr>
                                    <td>
                                        <div align='center'>
                                            {!! in_array($key, $question->user_choice) ? icon('fa-check-square-o') : icon('fa-square-o') !!}
                                        </div>
                                    </td>
                                    <td>
                                        <div align='center'>
                                            {!! $question->answers->isCorrect($key) ? icon('fa-check-square-o') : icon('fa-square-o') !!}
                                        </div>
                                    </td>
                                    <td>{!! standard_text_escape($answer) !!}</td>
                                    <td>
                                    @if (in_array($key, $question->user_choice))
                                        {!! standard_text_escape(nl2br(make_clickable($question->answers->selectComment($key)))) !!}
                                    @else
                                        &nbsp;
                                    @endif
                                    </td>
                                </tr>
                            @elseif (in_array($question->type, [FILL_IN_BLANKS, FILL_IN_BLANKS_TOLERANT]))
                                <tr>
                                    <td>{!! $question->getBlanksAnswer($answer, $exercise_user_record->id) !!}</td>
                                </tr>
                            @else
                                <tr class='even'>
                                  <td>{!! standard_text_escape($answer) !!}</td>
                                  <td>
                                        {{ $answer }} /
                                        @if ($question->user_choice[$key])
                                            @if ($question->answers->isCorrect($key) == $question->user_choice[$key])
                                              <span class="text-success"><b>{{ $question->answers->selectAnswer($question->user_choice[$key]) }}</b></span>
                                            @else
                                              <span class="text-danger"><b><del>{{ $question->answers->selectAnswer($question->user_choice[$key]) }}</del></b></span>
                                            @endif
                                        @else
                                            &nbsp;&nbsp;&nbsp;
                                        @endif
                                  </td>
                                </tr>                            
                            @endif
                        @endif
                    @endif                 
                @endforeach       
                <tr class='active'>
                    <th colspan='{{ $question->colspanByType() }}'>                                              
                    @if ($showScore)
                        @if (!empty($question->user_choice))
                            <span style='float:right;'>
                                {{ trans('langQuestionScore') }}: <b>{{ $question->user_score }}/{{ $question->weighting }}</b>
                            </span>
                        @else
                            <span style='float:right;'>
                                {{ trans('langQuestionScore') }}: <b>{{ $question->weighting }}</b>
                            </span>
                        @endif
                    @endif
                    </th>
                </tr>                
            </table>                