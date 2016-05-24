@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if ($rules)
        @foreach ($rules as $key => $rule)
            <div class='panel panel-info'>
                <div class='panel-heading'>
                    {{ trans('langAutoEnrollRule') }} {{ $key + 1 }}
                    <div class='pull-right'>
                    {!! action_button([
                        [
                            'title' => trans('langEditChange'),
                            'icon' => 'fa-edit',
                            'url' => "autoenroll.php?edit=" . getIndirectReference($rule['id'])
                        ],
                        [
                            'title' => trans('langDelete'),
                            'icon' => 'fa-times',
                            'url' => "autoenroll.php?delete=" . getIndirectReference($rule['id']),
                            'confirm' => trans('langSureToDelRule'),
                            'btn_class' => 'delete_btn btn-default'
                        ],
                    ]) !!}
                    </div>
                </div>
                <div class='panel-body'>
                    <div>
                        {{ trans('langApplyTo') }}: <b>{{ $rule['status'] == USER_STUDENT ? trans('langStudents'): trans('langTeachers') }}</b>
                        @if ($rule['deps'])
                            {{ trans('langApplyDepartments') }}:
                            <ul>
                            @foreach ($rule['deps'] as $dep)
                                <li>{{ getSerializedMessage($dep->name) }}</li>
                            @endforeach
                            </ul>
                        @else
                            {{ trans('langApplyAnyDepartment') }}:
                            <br>                 
                        @endif
                        @if ($rule['courses'])
                            {{ trans('langAutoEnrollCourse') }}:
                            <ul>
                            @foreach ($rule['courses'] as $course)
                                <li>
                                    <a href='{{ $urlAppend }}courses/{{ $course->code }}/'>
                                        {{ $course->title }}
                                    </a> 
                                    ({{ $course->public_code }})
                                </li>
                            @endforeach
                            </ul>
                        @endif
                        @if ($rule['auto_enroll_deps'])
                            {{ trans('langAutoEnrollDepartment') }}:
                            <ul>
                            @foreach ($rule['auto_enroll_deps'] as $auto_enroll_dep)
                                <li>
                                    <a href='{{ $urlAppend }}modules/auth/courses.php?fc={{ $auto_enroll_dep->id }}'>
                                        {{ getSerializedMessage($auto_enroll_dep->name) }}
                                    </a>
                                </li>
                            @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>                    
        @endforeach
    @else
        <div class='alert alert-warning text-center'> {{ trans('langNoRules') }}</div>
    @endif    
@endsection