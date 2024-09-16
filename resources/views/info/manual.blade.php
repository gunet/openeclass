@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            <div class='col-12 mb-3'>
                <h1>{{ $toolName }}</h1>
            </div>

            <div class='col-12'>
                <div class='row row-cols-1 row-cols-lg-2 g-4'>
                    <div class='col'>
                        <ul class='list-group list-group-flush'>
                            @foreach ($general_tutorials['links'] as $gt)
                                <li class="list-group-item element">
                                    <a class='TextBold' href='{{ $gt['url'] }}' target='_blank' class='mainpage' aria-label="{!! $gt['desc'] !!}">{!! $gt['desc'] !!}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class='col card-manual-img d-none d-lg-block'></div>
                </div>
            </div>

        </div>

    </div>
</div>

@endsection
