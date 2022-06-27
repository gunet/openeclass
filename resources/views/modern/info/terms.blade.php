@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">

                <div class="row p-5">

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ $urlAppend }}">{{trans('langPortfolio')}}</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{trans('langUsageTerms')}}</li>
                            </ol>
                        </nav>
                        <hr>
                    </div>


                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <div class='row'>
                            <div class='text-start text-secondary'>{{trans('langEclass')}} - {{trans('langUsageTerms')}}</div>
                            {!! $action_bar !!}
                        </div>
                    </div>


                    <div class='col-xs-12'>
                        <div class='panel shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
                            <div class='panel-body pane-body-terms'>
                                {!! $terms !!}
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>

@endsection
