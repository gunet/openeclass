@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row rowMargin">


                    @if(isset($_SESSION['uid']))
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    @endif

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! $action_bar !!}

                    <div class='col-12'>
                        <div class='table-responsive'>
                            <table id='ann_table_admin_logout' class='table-default'>
                                <thead>
                                <tr class='list-header'>
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

@endsection
