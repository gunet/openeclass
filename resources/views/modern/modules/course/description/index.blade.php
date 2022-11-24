@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

                <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active"> 
                    <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                    </div>
                </div>

                <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active">
                
                    <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                            <div class="offcanvas-header">
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            </div>
                        </div>
                                
                        @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                        


                        {!! isset($action_bar) ?  $action_bar : '' !!}

                        @if(Session::has('message'))
                        <div class='col-12 all-alerts'>
                            <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                @if(is_array(Session::get('message')))
                                    @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                    @foreach($messageArray as $message)
                                        {!! $message !!}
                                    @endforeach
                                @else
                                    {!! Session::get('message') !!}
                                @endif
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                        @endif
                        

                        @if ($course_descs)
                            @foreach ($course_descs as $key => $course_desc)    
                                <div class='col-12 mb-3'>      
                                    <div class='panel panel-action-btn-default rounded-0'>
                                        <div class='panel-heading rounded-0'>
                                            @if ($is_editor) 
                                                <div class='float-end'>
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
                                                                'icon' => 'fa-times',
                                                                'class' => 'delete',
                                                                'confirm' => trans('langConfirmDelete'))                            
                                                        )
                                                ) !!}

                                                </div>
                                            @endif
                                            <h3 class='panel-title'>
                                                <span class='control-label-notes'>{{ $course_desc->title}}</span>
                                                @if ($course_desc->visible && $is_editor)
                                                    &nbsp;&nbsp;
                                                    <span data-bs-original-title='{{ trans('langSeenToCourseHome') }}' data-bs-toggle='tooltip' data-bs-placement='bottom' class='label label-primary'>
                                                        <i class='fa fa-eye'></i>
                                                    </span>
                                                @endif
                                            </h3>      
                                        </div>
                                        <div class='panel-body rounded-0'>
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
                            <div class='col-12'><div class='alert alert-warning'>{{ trans('langThisCourseDescriptionIsEmpty') }}</div></div>
                        @endif

                    </div>
                </div>


        </div>
    </div>
</div>
@endsection