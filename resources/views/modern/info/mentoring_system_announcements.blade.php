@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row m-auto">


                    @include('modules.mentoring.common.common_current_title')

                    {!! $action_bar !!}

                    <table id='mentoring_ann_table_admin_logout' class='mentoring_announcements_table table-default'>
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
@endsection
