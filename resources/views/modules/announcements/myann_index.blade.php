
@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
            <div class="row m-auto">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view')

                        @include('layouts.partials.show_alert') 

                        <div class='col-12'>
                            <div class='table-responsive mt-0'>
                                <table id='ann_table_my_ann' class='table-default annn_table_my_ann'>
                                    <thead>
                                    <tr class='list-header'>
                                        <th>{{ trans('langAnnouncement') }}</th>
                                        <th style='width:15%;'>{{ trans('langDate') }}</th>
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
