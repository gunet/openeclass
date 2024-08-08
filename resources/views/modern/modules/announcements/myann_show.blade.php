@extends('layouts.default')

@section('content')

    {!! $action_bar !!}

    <div class="row">
        <div class="col-xs-12">
            <div class="table-responsive">
                <table id='ann_table{{ $course_id }}' class='table-default'>
                    <thead>
                        <tr class='list-header'>
                            <th>{{ trans('langAnnouncement') }}</th>
                            <th>{{ trans('langDate') }}</th>
                            @if ($is_editor)
                                <th>{{ trans('langNewBBBSessionStatus') }}</th>
                                <th aria-label="{{ trans('langSettingSelect') }}"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
