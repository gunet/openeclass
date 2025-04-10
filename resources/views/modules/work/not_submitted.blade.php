@include('layouts.partials.show_alert')

@extends('layouts.default')

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

                        @include('modules.work.assignment_details')

                        @if ($group_submissions)
                            @if ($num_results > 0)
                                <div class='col-sm-12'>
                                    <div class='alert alert-warning mt-4'>
                                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                        @if ($num_results == 1)
                                            {{ trans('langOneNonSubmission') }}
                                        @else
                                            {!! sprintf(trans('langMoreNonSubmissions'), $num_results) !!}
                                        @endif
                                    </div>
                                    <div class='table-responsive'>
                                        <table class='table-default sortable'>
                                            <thead>
                                                <tr class='list-header'>
                                                    <th>
                                                        {{ trans('langGroups') }}
                                                    </th>
                                                </tr>
                                            </thead>
                                            @foreach ($groups as $row => $value)
                                                <tr>
                                                    <td>
                                                        <a href='../group/group_space.php?course={{ $course_code }}&group_id={{ $row }}'>{{ $value }}</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            @else
                                <p class='sub_title1 mt-3'>{{ trans('langWorkGroupNoSubmission') }}:</p>
                                <div class='col-sm-12'>
                                    <div class='alert alert-warning'>
                                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoneWorkGroupNoSubmission') }}</span>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class='col-sm-12'>
                                @if ($num_results > 0)
                                    <div class='alert alert-warning mt-4'>
                                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                        <span>
                                            @if ($num_results == 1)
                                                {{ trans('langOneNonSubmission') }}
                                            @else
                                                {!! sprintf(trans('langMoreNonSubmissions'), $num_results) !!}
                                            @endif
                                        </span>
                                    </div>
                                <div class='table-responsive mt-1'>
                                    <table class='table-default'>
                                        <thead>
                                            <tr class='list-header'>
                                                <th>{{ trans('langSurnameName') }}</th>
                                                <th>{{ trans('langAmShort') }}</th>
                                            </tr>
                                        </thead>
                                        @foreach ($users as $row => $value)
                                            <tr>
                                                <td> {!! display_user($row) !!}</td>
                                                <td>
                                                    @if (!is_null(uid_to_am($row)))
                                                        <div class='text-heading-h6'>
                                                            {{ uid_to_am($row) }}
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                            @else
                                <p class='sub_title1 mt-3'>{{ trans('langWorkUserNoSubmission') }}:</p>
                                <div class='col-sm-12'>
                                    <div class='alert alert-warning'>
                                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoneWorkUserNoSubmission') }}</span>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
