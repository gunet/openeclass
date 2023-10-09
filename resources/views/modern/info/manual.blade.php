@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    <div class='col-12'>
                        <h1>{{ trans('langManuals') }}</h1>
                    </div>

                    <div id='accordion1' class='col-12 mt-4 mb-4'>
                        <ul class='list-group list-group-flush list-group-default'>
                            <li class='list-group-item'>
                                <a class="btn list-group-btn d-flex justify-content-start align-items-start px-0" role="button" data-bs-toggle="collapse" href="#Manual1" aria-expanded="true">
                                    <i class="fa-solid fa-chevron-down"></i>
                                    &nbsp;&nbsp;{{ $general_tutorials['title'] }}
                                </a>
                            </li>
                            
                            <div id="Manual1" class="panel-collapse accordion-collapse border-0 rounded-0 collapse show" role="tabpanel" data-bs-parent="#accordion1" style="">
                                @foreach ($general_tutorials['links'] as $gt)
                                    <li class="list-group-item element">
                                        <a class='categoryName text-decoration-underline' href='{{ $gt['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $gt['desc'] !!}</a>
                                    </li>
                                @endforeach
                            </div>
                        </ul>
                    </div>

                    <div id='accordion2' class='col-12 mb-4'>
                        <ul class='list-group list-group-flush list-group-default'>
                            
                            <li class='list-group-item'>
                                <a class="btn list-group-btn d-flex justify-content-start align-items-start px-0" role="button" data-bs-toggle="collapse" href="#Manual2" aria-expanded="true">
                                    <i class="fa-solid fa-chevron-down"></i>
                                    &nbsp;&nbsp;{{ $teacher_tutorials['title'] }}
                                </a>
                            </li>
                            <div id="Manual2" class="panel-collapse accordion-collapse border-0 rounded-0 collapse show" role="tabpanel" data-bs-parent="#accordion2" style="">
                                @foreach ($teacher_tutorials['links'] as $tt)
                                    <li class="list-group-item element">
                                        <a class='categoryName text-decoration-underline' href='{{ $tt['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $tt['desc'] !!}</a>
                                    </li>
                                @endforeach
                            </div>

                        </ul>
                    </div>


                    <div id='accordion3' class='col-12 mb-4'>
                        <ul class='list-group list-group-flush list-group-default'>
                            
                            <li class='list-group-item'>
                                <a class="btn list-group-btn d-flex justify-content-start align-items-start px-0" role="button" data-bs-toggle="collapse" href="#Manual3" aria-expanded="true">
                                    <i class="fa-solid fa-chevron-down"></i>
                                    &nbsp;&nbsp;{{ $student_tutorials['title'] }}
                                </a>
                            </li>
                            <div id="Manual3" class="panel-collapse accordion-collapse border-0 rounded-0 collapse show" role="tabpanel" data-bs-parent="#accordion3" style="">
                                @foreach ($student_tutorials['links'] as $st)
                                    <li class="list-group-item element">
                                        <a class='categoryName text-decoration-underline' href='{{ $st['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $st['desc'] !!}</a>
                                    </li>
                                @endforeach
                            </div>

                        </ul>
                    </div>
                
        </div>
   
</div>
</div>

@endsection
