@extends('layouts.default')

@section('content')


<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebarAdmin')
                </div>
            </div>

            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>
                        
                    
                        <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                        @include('layouts.partials.sidebarAdmin')
                        </div>
                    </div>

                    @if($breadcrumbs && count($breadcrumbs)>2)
                    <div class='row p-2'></div>
                    <div class="float-start">
                        <p class='control-label-notes'>{!! $breadcrumbs[1]['bread_text'] !!}</p>
                        <small class='text-secondary'>{!! $breadcrumbs[count($breadcrumbs)-1]['bread_text'] !!}</small>
                    </div>
                    <div class='row p-2'></div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if ($rules)
                        @foreach ($rules as $key => $rule)
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                            <div class='panel panel-info'>
                                <div class='panel-heading notes_thead text-white ps-3 pt-2'>
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