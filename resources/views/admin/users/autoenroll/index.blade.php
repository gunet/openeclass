@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">


                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    @if(isset($action_bar) and !empty($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    @include('layouts.partials.show_alert')

                    @if ($rules)
                    <div class='col-12'>
                        <div class='row row-cols-1 row-cols-md-2 g-4'>
                            @foreach ($rules as $key => $rule)
                            <div class='col'>
                                <div class='card panelCard card-default px-lg-4 py-lg-3 h-100'>
                                    <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>


                                                <h3>{{ trans('langAutoEnrollRule') }} {{ $key + 1 }}</h3>


                                                <div>
                                                    {!! action_button([
                                                        [
                                                            'title' => trans('langEditChange'),
                                                            'icon' => 'fa-edit',
                                                            'url' => "autoenroll.php?edit=" . getIndirectReference($rule['id'])
                                                        ],
                                                        [
                                                            'title' => trans('langDelete'),
                                                            'icon' => 'fa-xmark',
                                                            'url' => "autoenroll.php?delete=" . getIndirectReference($rule['id']),
                                                            'confirm' => trans('langSureToDelRule'),
                                                            'btn-class' => 'delete_btn btn-default'
                                                        ],
                                                    ]) !!}
                                                </div>


                                    </div>
                                    <div class='card-body'>
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
                                                        <a href='{{ $urlAppend }}modules/auth/opencourses.php?fc={{ $auto_enroll_dep->id }}'>
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
                        </div>
                    </div>
                    @else
                        <div class='col-12'>
                            <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span> {{ trans('langNoRules') }}</span></div>
                        </div>
                    @endif

        </div>
</div>

</div>
@endsection
