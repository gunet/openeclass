@extends('layouts.default')

@section('content')
<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 shadow p-3 pb-3 bg-body rounded bg-primary'>
                        <div class='d-flex justify-content-center ps-1 pt-1 pb-2'>
                            <span class="control-label-notes">
                                <i class="fas fa-tools orangeText" aria-hidden="true"></i> 
                                Copyright
                            </span>
                        </div>
                    </div>

                    <p class='mt-5'>{!! trans('langCopyrightNotice') !!}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
