@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            <div class='col-12 mb-4'>
                <h1>{{ $toolName }}</h1>
            </div>

            <div class='col-12'>

                <div class='row row-cols-lg-2 row-cols-1 g-4 '>
                    <div class='col'>
                        <div class='card panelCard px-lg-4 py-lg-3 w-100'>
                            <div class='card-body'>
                                <div class='col-12 d-flex justify-content-center mb-2'>
                                    <div class='circle-img-contant'>
                                        <i class="fa-solid fa-address-card fa-lg"></i>
                                    </div>
                                </div>
                                <div class='col-12 d-flex justify-content-center mb-0'>
                                    <strong>{!! trans('langInstitutePostAddress') !!}</strong>
                                </div>
                                <div class='col-12 d-flex justify-content-center'>
                                    @if(!empty($postaddress))
                                        {!! $postaddress !!}
                                    @else
                                        - {{trans('langProfileNotAvailable')}} -
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class='col'>
                        <div class='card panelCard px-lg-4 py-lg-3 w-100'>
                            <div class='card-body'>
                                <div class='col-12 d-flex justify-content-center mb-2'>
                                    <div class='circle-img-contant'>
                                        <i class="fa-solid fa-address-card fa-lg"></i>
                                    </div>
                                </div>
                                <div class='col-12 d-flex justify-content-center mb-0'>
                                    <strong>{!! trans('langPhone') !!}</strong>
                                </div>
                                <div class='col-12 d-flex justify-content-center'>
                                    @if(!empty($phone))
                                        {{ $phone }}
                                    @else
                                        - {!! trans('langProfileNotAvailable') !!} -
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class='col'>
                        <div class='card panelCard px-lg-4 py-lg-3 w-100'>
                            <div class='card-body'>
                                <div class='col-12 d-flex justify-content-center mb-2'>
                                    <div class='circle-img-contant'>
                                        <i class="fa-solid fa-address-card fa-lg"></i>
                                    </div>
                                </div>
                                <div class='col-12 d-flex justify-content-center mb-0'>
                                    <strong>{!! trans('langEmail') !!}</strong>
                                </div>
                                <div class='col-12 d-flex justify-content-center'>
                                    @if(!empty($emailhelpdesk))
                                        {!! $emailhelpdesk !!}
                                    @else
                                        - {{trans('langProfileNotAvailable')}} -
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
