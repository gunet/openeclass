@extends('layouts.default')

@section('content')
    <div class='table-responsive'>
        <table class='table-default'>
            <tr>
                <th>{!! q_math($exercise->exercise)  !!}</th>
            </tr>
            @if ($exercise->selectDescription())
                <tr>
                    <td>{!! standard_text_escape($exercise->selectDescription()) !!}</td>
                </tr>
            @endif
        </table>
    </div>
    <br>
    <select class='form-control' style='margin:0 0 12px 0;' id='status_filtering'>
        <option value='results.php?course={{ $course_code }}&exerciseId={{ getIndirectReference($exercise->id) }}'>--- {{ trans('langCurrentStatus') }} ---</option>
        <option value='results.php?course={{ $course_code }}&exerciseId={{ getIndirectReference($exercise->id) }}&status={{ ATTEMPT_ACTIVE }}'{{ $status === 0 ? ' selected' : '' }}>{{  trans('langAttemptActive') }}</option>
        <option value='results.php?course={{ $course_code }}&exerciseId={{ getIndirectReference($exercise->id) }}&status={{ ATTEMPT_COMPLETED }}'{{ $status === 1 ? ' selected' : '' }}>{{ trans('langAttemptCompleted') }}</option>
        <option value='results.php?course={{ $course_code }}&exerciseId={{ getIndirectReference($exercise->id) }}&status={{ ATTEMPT_PENDING }}'{{ $status === 2 ? ' selected' : '' }}>{{ trans('langAttemptPending') }}</option>
        <option value='results.php?course={{ $course_code }}&exerciseId={{ getIndirectReference($exercise->id) }}&status={{ ATTEMPT_PAUSED }}'{{ $status === 3 ? ' selected' : '' }}>{{ trans('langAttemptPaused') }}</option>
        <option value='results.php?course={{ $course_code }}&exerciseId={{ getIndirectReference($exercise->id) }}&status={{ ATTEMPT_CANCELED }}'{{ $status === 4 ? ' selected' : '' }}>{{ trans('langAttemptCanceled') }}</option>
    </select>
    @foreach ($students as $student)
        @if (count($user_attempts[$student->id]) > 0)
            <div class='table-responsive'>
                <table class='table-default'>
                    <tr>
                        <td colspan='{{ $is_editor ? 5 : 4 }}'>
                        @if (!$student->id)
                            {{ trans('langNoGroupStudents') }}
                        @else
                            <b>{{ trans('langUser') }}:</b> 
                            {{ $student->surname }} {{ $student->givenname }}
                            <div class='smaller'>
                                ({{ trans('langAm') }}: {{ $student->am ?: '-' }})
                            </div>
                        @endif
                        </td>
                    </tr>
                    <tr>
                        <th class='text-center'>{{ trans('langStart') }}</td>
                        <th class='text-center'>{{ trans('langExerciseDuration') }}</td>
                        <th class='text-center'>{{ trans('langTotalScore') }}</td>
                        <th class='text-center'>{{ trans('langCurrentStatus') }}</th>
                        @if($is_editor)
                            <th class='text-center'>{!! icon('fa-gears') !!}</th>
                        @endif
                    </tr>
                    @foreach ($user_attempts[$student->id] as $attempt)
                        @if ($attempt->attempt_status == ATTEMPT_ACTIVE)                   
                            <tr class='{{ $cur_date_time > $attempt->max_attempt_end_date ? 'warning' : 'success' }}' data-toggle='tooltip' title='{{ $cur_date_time > $attempt->max_attempt_end_date ? trans('langAttemptActiveButDeadMsg') : trans('langAttemptActiveMsg') }}'>
                        @else
                            <tr>
                        @endif
                            <td class='text-center'> {{ $attempt->record_start_date->format('d/m/Y H:i:s') }}</td>
                            @if ($attempt->time_duration == '00:00:00' || empty($attempt->time_duration) || $attempt->attempt_status == ATTEMPT_ACTIVE)
                                <td class='text-center'>{{ trans('langNotRecorded') }}</td>
                            @else
                                <td class='text-center'>{!! format_time_duration($attempt->time_duration) !!}</td>
                            @endif
                            <td class='text-center'>
                                @if ($attempt->attempt_status == ATTEMPT_COMPLETED)
                                    <!--I NEED TO ADD SOME LOGIC HERE-->
                                    @if ($showScore)
                                        @if ($attempt->answers_cnt)
                                        <a href='exercise_result.php?course={{ $course_code }}&amp;eurId={{ $attempt->id }}'>{{ $attempt->total_score }} / {{ $attempt->total_weighting }}</a>
                                        @else
                                            {{ $attempt->total_score }} / {{ $attempt->total_weighting }}
                                        @endif
                                    @else
                                        @if ($displayScore == 2)
                                            {{ trans('langScoreNotDisp') }}
                                        @elseif ($displayScore == 3)
                                            {{ trans('langScoreDispLastAttempt') }}
                                        @elseif ($displayScore == 4)
                                            {{ trans('langScoreDispEndDate') }}
                                        @endif
                                    @endif
                                @elseif ($attempt->attempt_status == ATTEMPT_PAUSED || $attempt->attempt_status == ATTEMPT_ACTIVE)
                                    -/-
                                @else
                                    {{ $attempt->total_score }} / {{ $attempt->total_weighting }}
                                @endif
                            </td>
                            <td class='text-center'>
                            @if ($attempt->attempt_status == ATTEMPT_COMPLETED)
                                {{ trans('langAttemptCompleted') }}
                            @elseif ($attempt->attempt_status == ATTEMPT_PAUSED)
                                {{ trans('langAttemptPaused') }}
                            @elseif ($attempt->attempt_status == ATTEMPT_ACTIVE)
                                {{ trans('langAttemptActive') }}
                            @elseif($attempt->attempt_status == ATTEMPT_PENDING)
                                <a href='exercise_result.php?course={{ $course_code }}&amp;eurId={{ $attempt->id }}'>
                                    {{ trans('langAttemptPending') }} 
                                </a>
                            @else
                                {{ trans('langAttemptCanceled') }}
                            @endif
                            </td>
                            @if ($is_editor)
                                    <td class='option-btn-cell'>
                                    @if(!($attempt->attempt_status == ATTEMPT_ACTIVE && $cur_date_time <= $attempt->max_attempt_end_date))
                                        {!! action_button(array(
                                            array(
                                                'title' => trans('langDelete'),
                                                'url' => "results.php?course=$course_code&exerciseId=$exercise->id&purgeAttempID=$attempt->id",
                                                'icon' => "fa-times",
                                                'confirm' => trans('langQuestionCatDelConfirrm'),
                                                'class' => 'delete'
                                            )
                                        )) !!}
                                    @endif
                                    </td>
                            @endif
                        </tr>
                    @endforeach                    
                </table>
            </div>
        @endif
    @endforeach
@endsection
