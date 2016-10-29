@extends('layouts.default')

@section('content')

{!! $action_bar !!}
    
    @if ($myCourses)
        <div class='table-responsive'>
            <table class='table-default'>
                <thead class='list-header'>
                    <th>
                        {{ trans('langTitle') }}
                    </th>
                    <th>
                        {{ trans('langTeacher') }}
                    </th>
                    <th class='text-center'>
                        {!! $actions !!}
                    </th>
                </thead>
                <tbody>
                @foreach ($myCourses as $course)
                    @if ($course->visible == COURSE_INACTIVE)
                        <tr class = 'not_visible'>
                    @else 
                        <tr>
                    @endif
                        <td>
                            <strong>
                                <a href='{{ $urlServer }}courses/{{ q($course->code) }}'>{{ q($course->title) }}</a>
                            </strong> ({{ q($course->public_code) }})
                        </td>
                        <td>
                            {{ q($course->professor) }}
                        </td>
                        <td class='text-center'>
                            {!! $action_button !!}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class='alert alert-warning'>{{ trans('langNoCourses') }}</div>
    @endif        
@endsection