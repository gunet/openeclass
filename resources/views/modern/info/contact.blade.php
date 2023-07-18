@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row rowMargin">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! $action_bar !!}
                   
                    <div class='col-12'>
                        <div class="card panelCard px-lg-4 py-lg-3">
                            <div class='card-body'>
                                <div class='row'>
                                    <div class='col-lg-4 col-12 d-flex justify-content-center align-items-center mb-lg-0 mb-5'>
                                        <img class='contactImage' src="{{ $urlAppend }}template/modern/img/indexlogo.png">
                                    </div>
                                    <div class='col-lg-8 col-12'>
                                        
                                        <div class='row'>
                                            <div class='col-md-6 col-12'>
                                                <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class='fa fa-address-card text-white'></i></div></div>
                                                <div class='col-12 d-flex justify-content-center mb-0'><strong>{!! trans('langPostMail') !!}</strong></div>
                                                <div class='col-12 d-flex justify-content-center'>{!! $Institution !!}</div>
                                            </div>
                                            <div class='col-md-6 col-12 mt-md-0 mt-5'>
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

                                        <div class='row mt-5'>
                                            <div class='col-md-4 col-12'>
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
                                            <div class='col-md-4 col-12 mt-md-0 mt-5'>
                                                <div class='col-12 d-flex justify-content-center mb-2'><div class='circle-img-contant'><i class='fa fa-fax text-white'></i></div></div>
                                                <div class='col-12 d-flex justify-content-center mb-0'><strong>{!! trans('langFax') !!}</strong></div>
                                                <div class='col-12 d-flex justify-content-center'>
                                                    @if(!empty($fax))
                                                        {{ $fax }}
                                                    @else
                                                            - {!! trans('langProfileNotAvailable') !!} -
                                                    @endif
                                                </div>
                                            </div>
                                            <div class='col-md-4 col-12 mt-md-0 mt-5'>
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
