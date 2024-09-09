@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert') 

                    <div class='col-12'>
                        <div class='alert alert-info'>
                            <i class='fa-solid fa-circle-info fa-lg'></i><span>
                            <ol>
                                <li>{{ trans('langBUCourseDataOfMainBase') }} {{ $course_code }}</li>
                                <li>{{ trans('langBackupOfDataBase') }} {{ $course_code }}</li>
                            </ol></span>
                        </div>
                    </div>

                    <div class='col-12'>
                        <div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>
                            {{ trans('langBackupSuccesfull') }}</span>
                        </div>
                    </div>

                    

                </div>

            </div>
        </div>
    
</div>
</div>
  
@endsection