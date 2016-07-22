@extends('layouts.default')

@section('content')

    {!! $action_bar !!}

    <div class="row">
        <div class="col-xs-12">
            <div class='table-responsive'>
                <table id='ann_table_my_ann' class='table-default'>
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
