            <table class='table-default{{ !is_null($question->user_score) ? ' graded' : ' ungraded' }}'>
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
                @if ($showResults && $question->user_choice[0])
                    <tr class='active'>
                        <td><b>{{ trans('langAnswer') }}</b></td>
                    </tr>
                @endif
                <tr class='even'>
                    <td>{!! purify($question->user_choice[0]) !!}</td>
                </tr>
                <tr class='active'>
                    <th colspan='{{ $question->colspanByType() }}'>                
                    @if (!empty(purify($question->user_choice[0])))
                        @if (is_null($question->user_score))
                            <span class='text-danger'>{{ trans('langAnswerUngraded') }}</span>
                        @endif
                    @endif                           
                    @if ($showScore)
                        @if ($question->user_choice[0])
                            @if ($is_editor && !isset($question->user_score))
                                <span style='float:right;'>
                                    {{ trans('langQuestionScore') }} : 
                                    <input style='display:inline-block;width:auto;' type='text' class='questionGradeBox' maxlength='3' size='3' name='questionScore[{{ $question->id }}]'>
                                    <input type='hidden' name='questionMaxGrade' value='{{ $question->weighting }}'>
                                    <b>/{{ $question->weighting }}</b>
                                </span>
                            @else
                                <span style='float:right;'>
                                    {{ trans('langQuestionScore') }}: <b>{{ round($question->user_score,2) }}/{{  $question->weighting }}</b>
                                </span>
                            @endif
                        @else
                            <span style='float:right;'>
                                {{ trans('langQuestionScore') }}: <b>{{ round($question->user_score,2) }}/{{  $question->weighting }}</b>
                            </span>
                        @endif
                    @endif
                    </th>
                </tr>                
            </table>                