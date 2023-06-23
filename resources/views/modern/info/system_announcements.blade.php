@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                    
                <div class="row">


                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! $action_bar !!}

                    <div class='col-12'>
                        <div class='table-responsive'>
                            <table id='ann_table_admin_logout' class='announcements_table'>
                                <thead>
                                <tr class='notes_thead'>
                                    <th>{{ trans('langAnnouncement') }}</th>
                                    <th>{{ trans('langDate') }}</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
</div>

@endsection
