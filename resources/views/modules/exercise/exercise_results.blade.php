@extends('layouts.default')

@section('content')
    <div class='panel panel-primary'>
        <div class='panel-heading'>
          <h3 class='panel-title'>{!! q_math($exercise->exercise) !!}</h3>
        </div>
        <div class='panel-body'>
        @if ($exercise->selectParsedDescription())
            {!! $exercise->selectParsedDescription() !!}
            <hr>
        @endif
            <div class='row'>
                <div class='col-xs-6 col-md-3 text-right'>
                    <strong>{{ trans('langSurname') }}:</strong>
                </div>
                <div class='col-xs-6 col-md-3'>
                    {{ $user->surname }}
                </div>
                <div class='col-xs-6 col-md-3 text-right'>
                    <strong>{{ trans('langName') }}:</strong>
                </div>
                <div class='col-xs-6 col-md-3'>
                    {{ $user->givenname }}
                </div>
                @if ($user->am)
                    <div class='col-xs-6 col-md-3 text-right'>
                        <strong>{{ trans('langAm') }}:</strong>
                    </div>
                    <div class='col-xs-6 col-md-3'>
                        {{ $user->am }}
                    </div>
                @endif
                @if ($user->phone)
                <div class='col-xs-6 col-md-3 text-right'>
                    <strong>{{ trans('langPhone') }}:</strong>
                </div>
                <div class='col-xs-6 col-md-3'>
                    {{ $user->phone }}
                </div>
                @endif
                @if ($user->email)
                    <div class='col-xs-6 col-md-3 text-right'>
                        <strong>Email:</strong>
                    </div>
                    <div class='col-xs-6 col-md-3'>
                        {{ $user->email }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class='row margin-bottom-fat'>
        <div class='col-md-5 col-md-offset-7'>
        @if ($is_editor && $exercise_user_record->attempt_status == ATTEMPT_PENDING)
            <div class='btn-group btn-group-sm' style='float:right;'>
                <a class='btn btn-primary' id='all'>{{ trans('langAllExercises') }}</a>
                <a class='btn btn-default' id='ungraded'>{{ trans('langAttemptPending') }}</a>
            </div>
        @endif
        </div>
    </div>
    @if (!empty($questions))
        @foreach ($questions as $key => $question)
            @if (!$question->is_deleted)
                @if ($question->type == FREE_TEXT)
                    @include('modules.exercise.partials.freeText')
                @else
                    @include('modules.exercise.partials.other')
                @endif
            @else
                @include('modules.exercise.partials.deleted')
            @endif
        @endforeach
    @endif
    @if ($showScore)
        <br>
        <table class='table-default'>
            <tr>
                <td class='text-right'>
                    <b>
                        {{ trans('langYourTotalScore') }}: 
                        <span id='total_score'>{{ $exercise_user_record->total_score }}</span> / {{ $exercise_user_record->total_weighting }}
                    </b>
                </td>
            </tr>
        </table>
    @endif
    <br>
    <div align='center'>
        @if ($is_editor && $exercise_user_record->attempt_status == ATTEMPT_PENDING)
            <a class='btn btn-primary' href='index.php' id='submitButton'>{{ trans('langSubmit') }}</a>
        @endif
        <a class='btn btn-default' href='index.php?course={{ $course_code }}'>{{ trans('langReturn') }}</a>
    </div>
@endsection
