            <table class='table-default graded'>
                <tr class='active'>
                    <td>
                        <b><u>{{ trans('langQuestion') }}</u>: {{ $key+1 }}</b>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class='alert alert-warning'>{{ trans('langQuestionAlreadyDeleted') }}</div>
                    </td>
                </tr>      
                <tr class='active'>
                    <th>                                              
                    @if ($showScore)
                            <span style='float:right;'>
                                {{ trans('langQuestionScore') }}: <b>{{ $question->user_score }}</b>
                            </span>
                    @endif
                    </th>
                </tr>                
            </table>                