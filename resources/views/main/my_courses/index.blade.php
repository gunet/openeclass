@extends('layouts.default')

@section('content')

{!! $action_bar !!}

    @if ($myCourses)
        <div class='table-responsive'>
            <table class='table-default'>
                <thead class='list-header'>
                    <th>
                        {{ trans('langCourse') }}
                    </th>
                    <th class='text-center'>
                        {!! icon('fa-gears') !!}
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
                                    <a href='{{ $urlServer }}courses/{{ q($course->code) }}/'>{{ q($course->title) }}</a>
                                </strong> ({{ q($course->public_code) }})
                                <div><small>{{ q($course->professor) }} </small></div>
                            </td>
                            <td class='text-center'>
                                @if (isset($course->favorite))
                                    {!! icon('fa-star', '', "course_favorite.php?course=$course->code&fav=0&from_ext_view=1") !!}
                                @else
                                    {!! icon('fa-bookmark-o', trans('langFavorite'), "course_favorite.php?course=$course->code&fav=1&from_ext_view=1") !!}
                                @endif
                                @if ($course->status == USER_STUDENT)
                                    {!! icon('fa-minus-circle', trans('langUnregCourse'), "${urlServer}main/unregcours.php?cid=$course->course_id&amp;uid=$uid") !!}
                                @else ($course->status == USER_TEACHER)
                                   {!! icon('fa-wrench', trans('langAdm'), "${urlServer}modules/course_info/?from_home=true&amp;course=" . $course->code) !!}
                                @endif
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
