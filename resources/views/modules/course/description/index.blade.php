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

                        {!! isset($action_bar) ?  $action_bar : '' !!}

                        @include('layouts.partials.show_alert') 

                        @if ($course_descs)
                            @foreach ($course_descs as $key => $course_desc)    
                                <div class='col-12 mb-4'>      
                                    <div class='card panelCard px-lg-4 py-lg-3'>
                                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>                                 
                                            <h3>
                                                {{ $course_desc->title}}
                                                @if ($course_desc->visible && $is_editor)
                                                    <a aria-label="{{ trans('langSeenToCourseHome') }}" data-bs-original-title='{{ trans('langSeenToCourseHome') }}' data-bs-toggle='tooltip' data-bs-placement='bottom'>
                                                        <i class='fa fa-eye'></i>
                                                    </a>
                                                @endif
                                            </h3>
                                            @if ($is_editor)
                                            
                                                <div>
                                                    {!! action_button(
                                                        array(
                                                            array(
                                                                'title' => trans('langEditChange'),
                                                                'url' => "edit.php?course=$course_code&amp;id=" . getIndirectReference($course_desc->id),
                                                                'icon' => 'fa-edit'
                                                            ),
                                                            array('title' => $course_desc->visible ? trans('langRemoveFromCourseHome') : trans('langAddToCourseHome'),
                                                                'url' => "index.php?course=$course_code&amp;vis=" . getIndirectReference($course_desc->id),
                                                                'icon' => $course_desc->visible ? 'fa-eye-slash' : 'fa-eye'
                                                            ),
                                                            array('title' => trans('langUp'),
                                                                'level' => 'primary',
                                                                'icon' => 'fa-arrow-up',
                                                                'url' => "index.php?course=$course_code&amp;up=" . getIndirectReference($course_desc->id),
                                                                'disabled' => $key <= 0),
                                                            array('title' => trans('langDown'),
                                                                'level' => 'primary',
                                                                'icon' => 'fa-arrow-down',
                                                                'url' => "index.php?course=$course_code&amp;down=" . getIndirectReference($course_desc->id),
                                                                'disabled' => $key + 1 >= count($course_descs)),
                                                            array('title' => trans('langDelete'),
                                                                'url' => "index.php?course=$course_code&amp;del=" . getIndirectReference($course_desc->id),
                                                                'icon' => 'fa-xmark',
                                                                'class' => 'delete',
                                                                'confirm' => trans('langConfirmDelete'))                            
                                                        )
                                                    ) !!}
                                                </div>
                                            
                                            @endif
                                            
                                        </div>
                                        <div class='card-body'>
                                            {!! handleType($course_desc->type) !!} 
                                            @if(!empty($course_desc->type))
                                                <br>
                                                <br>
                                            @endif
                                            <div class='col-12'>
                                                {!! standard_text_escape($course_desc->comments) !!} 
                                            </div>
                                        </div>            
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langThisCourseDescriptionIsEmpty') }}</span></div></div>
                        @endif

                    </div>
                </div>


        </div>
    
</div>
</div>
@endsection