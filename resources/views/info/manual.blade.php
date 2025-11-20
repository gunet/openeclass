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
                            <h1 class="mt-4 mb-4">
                                {{ trans('langVideoTutorials') }}
                            </h1>
                            <ul class='list-group list-group-flush'>
                                <li class="list-group-item element">
                                    <a class='TextBold' href="https://www.youtube.com/playlist?list=PLIy44c1N0HnXskRAp-KXBmZq04yPzKz5H" target='_blank' class='mainpage'>{{ trans('langVideoManS') }}</a>
                                </li>
                                <li class="list-group-item element">
                                    <a class='TextBold' href="https://www.youtube.com/playlist?list=PLIy44c1N0HnVJt-GtxPaqenss4jeoEt-j" target='_blank' class='mainpage'>{{ trans('langVideoManT1') }}</a>
                                </li>
                                <li class="list-group-item element">
                                    <a class='TextBold' href="https://www.youtube.com/playlist?list=PLIy44c1N0HnUvby36zDp2kNkkQxsyuMkJ" target='_blank' class='mainpage'>{{ trans('langVideoManT2') }}</a>
                                </li>
                            </ul>
                        </div>
                        <div class='col card-manual-img d-none d-lg-block'></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
