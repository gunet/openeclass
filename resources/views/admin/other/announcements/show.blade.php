@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


            @include('layouts.partials.legend_view')

            @if(isset($action_bar))
                {!! $action_bar !!}
            @else
                <div class='mt-4'></div>
            @endif

            @if($announcementsID)

                <div class='col-12'>
                    <div class="card panelCard px-lg-4 py-lg-3">
                        <div class="card-header border-0 d-flex justify-content-between align-items-center">
                            <h3>{{$announcementsID->title}}</h3>
                        </div>
                        <div class="card-body">

                                {!! $announcementsID->body !!}

                        </div>
                        <div class='card-footer border-0 d-flex justify-content-start align-items-center'>
                            <div class='announcement-date small-text'>{!! format_locale_date(strtotime($announcementsID->date)) !!}</div>
                        </div>
                    </div>
                </div>

            @endif

        </div>
    </div>
</div>

@endsection
