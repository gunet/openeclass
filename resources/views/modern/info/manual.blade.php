@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    <div class='col-12 mb-3'>
                        <h2>{{ $toolName }}</h2>
                    </div>

                    <div class='col-12'>
                        <div class='row row-cols-1 row-cols-md-3 g-3 g-md-4'>
                            <div class='col'>
                                <ul class='list-group list-group-flush'>
                                    <li class='list-group-item list-group-item-action border-0 pb-3'>
                                        {{ $general_tutorials['title'] }}
                                    </li>

                                    @foreach ($general_tutorials['links'] as $gt)
                                        <li class="list-group-item element">
                                            <a class='TextBold' href='{{ $gt['url'] }}' target='_blank' class='mainpage' aria-label='(opens new window)'>{!! $gt['desc'] !!}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class='col'>
                                <ul class='list-group list-group-flush'>
                                    <li class='list-group-item list-group-item-action border-0 pb-3'>
                                        {{ $teacher_tutorials['title'] }}
                                    </li>
                                    @foreach ($teacher_tutorials['links'] as $tt)
                                        <li class="list-group-item element">
                                            <a class='TextBold' href='{{ $tt['url'] }}' target='_blank' class='mainpage' aria-label='(opens new window)'>{!! $tt['desc'] !!}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>


                            <div class='col'>
                                <ul class='list-group list-group-flush'>

                                    <li class='list-group-item list-group-item-action border-0 pb-3'>
                                        {{ $student_tutorials['title'] }}
                                    </li>
                                    @foreach ($student_tutorials['links'] as $st)
                                        <li class="list-group-item element">
                                            <a class='TextBold' href='{{ $st['url'] }}' target='_blank' class='mainpage' aria-label='(opens new window)'>{!! $st['desc'] !!}</a>
                                        </li>
                                    @endforeach

                                </ul>
                            </div>
                        </div>
                    </div>

        </div>

    </div>
</div>

@endsection
