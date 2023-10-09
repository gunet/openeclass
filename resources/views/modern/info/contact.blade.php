@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            <div class='col-12'>
                <h1>{{ trans('langContact') }}</h1>
            </div>

            <div class='col-12 mt-4'>
                <div class="card panelCard px-lg-4 py-lg-3">
                    <div class='card-body'>
                        <div class='row'>
                            <div class='col-lg-4 col-12 d-flex justify-content-center align-items-center mb-lg-0 mb-5'>
                                <img class='contactImage' src="{{ $urlAppend }}template/modern/img/indexlogo.png">
                            </div>
                            <div class='col-lg-8 col-12'>

                                <div class='row'>
                                    <div class='col-md-6 col-12 mt-md-0 mt-3'>
                                        <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class='fa fa-address-card text-white'></i></div></div>
                                        <div class='col-12 d-flex justify-content-center mb-0'><strong>{!! trans('langInstitutePostAddress') !!}:</strong></div>
                                        <div class='col-12 d-flex justify-content-center'>
                                            @if(!empty($postaddress))
                                                {!! $postaddress !!}
                                            @else
                                                - {{trans('langProfileNotAvailable')}} -
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class='row'>
                                    <div class='col-md-6 col-12 mt-md-3 mt-3'>
                                        <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class='fa fa-phone text-white'></i></div></div>
                                        <div class='col-12 d-flex justify-content-center mb-0'><strong>{!! trans('langPhone') !!}:</strong></div>
                                        <div class='col-12 d-flex justify-content-center'>
                                            @if(!empty($phone))
                                                {{ $phone }}
                                            @else
                                                - {!! trans('langProfileNotAvailable') !!} -
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class='col-md-6 col-12 mt-md-3 mt-3'>
                                        <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class='fa fa-envelope text-white'></i></div></div>
                                        <div class='col-12 d-flex justify-content-center mb-0'><strong>{!! trans('langEmail') !!}:</strong></div>
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
    </div>
</div>

@endsection
