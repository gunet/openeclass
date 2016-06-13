@extends('layouts.default')

@section('content')
    <div class='table-responsive'>
        <table class='table-default'>
            <tr>
                <th>{!! q_math($exercise->exercise)  !!}</th>
            </tr>
            @if ($exercise->selectParsedDescription())
                <tr>
                    <td>{!! $exercise->selectParsedDescription() !!}</td>
                </tr>
            @endif
        </table>
    </div>
    <br>
    <select class='form-control' style='margin:0 0 12px 0;' id='status_filtering'>
        <option value='results.php?course=$course_code&exerciseId=$exerciseId'>--- {{ trans('langCurrentStatus') }} ---</option>
        <option value='results.php?course=$course_code&exerciseId=$exerciseId&status=".ATTEMPT_ACTIVE."'".($status === 0 ? ' selected' : '').">{{  trans('langAttemptActive') }}</option>
        <option value='results.php?course=$course_code&exerciseId=$exerciseId&status=".ATTEMPT_COMPLETED."'".($status === 1 ? ' selected' : '').">{{ trans('langAttemptCompleted') }}</option>
        <option value='results.php?course=$course_code&exerciseId=$exerciseId&status=".ATTEMPT_PENDING."'".($status === 2 ? ' selected' : '').">{{ trans('langAttemptPending') }}</option>
        <option value='results.php?course=$course_code&exerciseId=$exerciseId&status=".ATTEMPT_PAUSED."'".($status === 3 ? ' selected' : '').">{{ trans('langAttemptPaused') }}</option>
        <option value='results.php?course=$course_code&exerciseId=$exerciseId&status=".ATTEMPT_CANCELED."'".($status === 4 ? ' selected' : '').">{{ trans('langAttemptCanceled') }}</option>
    </select>    
@endsection
