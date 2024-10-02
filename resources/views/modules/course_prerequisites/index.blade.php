@extends('layouts.default')

@push('head_scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $(document).on("click", ".delete_btn", function(e) {
                var link = $(this).attr("href");
                e.preventDefault();

                bootbox.confirm({
                    closeButton: false,
                    title: "<div class=\"icon-modal-default\"><i class=\"fa-regular fa-trash-can fa-xl Accent-200-cl\"></i></div><div class=\"modal-title-default text-center mb-0\">{{ js_escape(trans('langConfirmDelete')) }}</div>",
                    message: "<p class=\"text-center\">{{ js_escape(trans('langDelWarnCoursePrerequisite')) }}</p>",
                    buttons: {
                        cancel: {
                            label: "{{ js_escape(trans('langCancel')) }}",
                            className: "cancelAdminBtn position-center"
                        },
                        confirm: {
                            label: "{{ js_escape(trans('langDelete')) }}",
                            className: "deleteAdminBtn position-center",
                        }
                    },
                    callback: function (result) {
                        if (result) {
                            document.location.href = link;
                        }
                    }
                });

            });
        });
    </script>
@endpush

@section('content')
    <div class="col-12 main-section">
        <div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                @include('layouts.partials.left_menu')

                <div class="col_maincontent_active">

                    <div class="row">

                      @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view')

                        <div id='operations_container'>
                            {!! $action_bar !!}
                        </div>

                        @include('layouts.partials.show_alert')

                        @if (count($result) > 0)
                            <div class='row'>
                                <div class='col-sm-12'>
                                    <div class='table-responsive'>
                                        <table class='table-default'>
                                            <thead>
                                                <tr class='list-header'>
                                                    <th>{{ trans('langTitle') }}</th>
                                                    <th aria-label='{{ trans('langSettingSelect') }}'><span class='fa gears'></span></th>
                                                </tr>
                                            </thead>
                                            @foreach ($result as $row)
                                                <tr>
                                                    <td>{{ $row->title }} {{ ($row->public_code) }}</td>
                                                    <td class='option-btn-cell text-end'>
                                                        {!! action_button(
                                                                array(array(
                                                                    'title' => trans('langRemovePrerequisite'),
                                                                    'level' => 'primary',
                                                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;del=" . intval($row->id),
                                                                    'icon' => 'fa-xmark Accent-200-cl',
                                                                    'btn_class' => 'delete_btn deleteAdminBtn'
                                                                )))
                                                        !!}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class='col-sm-12'>
                                <div class='alert alert-warning'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                    <span>{{ trans('langNoCoursePrerequisites') }}</span>
                                </div>
                            </div>
                        @endif

                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
