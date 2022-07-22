@extends('layouts.default')

@section('content')


<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if ($rules)
                        @foreach ($rules as $key => $rule)
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                            <div class='panel panel-info'>
                                <div class='panel-heading ps-3 pt-2'>
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
                                            'btn-class' => 'delete_btn btn-default'
                                        ],
                                    ]) !!}
                                    </div>
                                </div>
                                <div class='panel-body panel-body-admin ps-3 pb-3 pt-3 pe-3'>
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
                        </div>                 
                        @endforeach
                    @else
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                            <div class='alert alert-warning text-center'> {{ trans('langNoRules') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>    
@endsection