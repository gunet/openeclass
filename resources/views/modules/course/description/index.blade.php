@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if ($course_descs)
        @foreach ($course_descs as $key => $course_desc)          
            <div class='panel panel-action-btn-default'>
                <div class='panel-heading'>
                    @if ($is_editor) 
                        <div class='pull-right'>
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
                        {{ $course_desc->title}}
                        @if ($course_desc->visible && $is_editor)
                            &nbsp;&nbsp;
                            <span data-original-title='{{ trans('langSeenToCourseHome') }}' data-toggle='tooltip' data-placement='bottom' class='label label-primary'>
                                <i class='fa fa-eye'></i>
                            </span>
                        @endif
                    </h3>      
                </div>
                <div class='panel-body'>
                    {!! handleType($course_desc->type) !!} 
                    <br>
                    <br>
                    {!! standard_text_escape($course_desc->comments) !!} 
                </div>            
            </div>
        @endforeach
    @else
        <div class='alert alert-warning'>{{ trans('langThisCourseDescriptionIsEmpty') }}</div>
    @endif
@endsection