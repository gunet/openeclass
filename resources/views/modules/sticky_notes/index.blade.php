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

                    <div class='col-sm-12'>

                        @if ($topics)

                            <div class='table-responsive'>
                                <table id='request_table_{{ $course_id }}' class='table table-default table-request'>
                                    <thead>
                                        <tr class='list-header'>
                                            <th>{{ trans('langStickyNotesTopic') }}</th>
                                            <th>{{ trans('langDescription') }}</th>
                                            <th>{{ trans('langStickyNotesTotal') }}</th>
                                            <th>{{ trans('langCreationDate') }}</th>
                                            @if ($is_course_admin)
                                            <th class='text-end' aria-label="{{ trans('langSettingSelect') }}"><span class='fa fa-cogs'></span></th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($topics as $topic)
                                            @if (!(!$is_course_admin && !$topic->is_active))
                                                <tr @class(['not_visible' => !$topic->is_active])>
                                                    <td>
                                                        <a href="index.php?course={{$course_code}}&topic={{$topic->id}}">{{ $topic->title }}</a>
                                                    </td>
                                                    <td>{{ $topic->description }}</td>
                                                    <td>{{ $topic->posts }}</td>
                                                    <td>{{ $topic->created_at }}</td>
                                                    @if ($is_course_admin)
                                                    <td class='option_btn_cell text-center'>
                                                        {!! action_button([
                                                        [ 'title' => trans('langEditChange'),
                                                        'icon' => 'fa-edit',
                                                        'url' => "new_topic.php?course=$course_code&id=$topic->id" ],
                                                        ['title' => $topic->is_active ? trans('langViewHide') : trans('langViewShow'),
                                                        'icon' => $topic->is_active ? 'fa-eye-slash' : 'fa-eye',
                                                        'url' => "new_topic.php?course=$course_code&id=$topic->id&active=" . ($topic->is_active ? 0 : 1)],
                                                        [ 'title' => trans('langDelete'),
                                                        'icon' => 'fa-trash',
                                                        'url' => "delete_topic.php?course=$course_code&id=$topic->id" ],
                                                        ]) !!}
                                                    </td>
                                                    @endif
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        @else

                            <div class='alert alert-warning'>
                                <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                <span>{{ trans('langStickyNotesNoTopics') }}</span>
                            </div>

                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection