@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
            <div class="row m-auto">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view')

                        {!! $action_bar !!}

                        @include('layouts.partials.show_alert') 

                        <div class='col-12'>
                            <div class='card panelCard px-lg-4 py-lg-3'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                    <h3>
                                       {!! $announcement->title !!}
                                    </h3>
                                </div>
                                <div class='card-body'>
                                    {!! $announcement->content !!}
                                </div>
                                <div class='card-footer d-flex justify-content-between align-items-center small-text border-0'>

                                    @if($announcement->code)
                                        <a class='TextBold' href='{{$urlAppend}}modules/announcements/index.php?course={{$announcement->code}}&an_id={{$announcement->id}}'>{!! $announcement->course_title !!}</a>
                                    @endif

                                    <div>{!! $announcement->an_date !!}</div>

                                </div>
                            </div>
                        </div>
            </div>

        </div>
    </div>


@endsection
