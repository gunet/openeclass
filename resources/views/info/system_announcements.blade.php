@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">


                    @if(isset($_SESSION['uid']))
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    @endif

                    <div class='col-12 my-4'>
                        <h1>{{ $pageName }}</h1>
                    </div>

                    {!! $action_bar !!}

                    <div class='col-12'>
                        <div class='table-responsive'>
                            <table id='ann_table_admin_logout' class='table-default annn_table_my_ann'>
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
