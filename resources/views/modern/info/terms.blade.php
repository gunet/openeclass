@extends('layouts.default')

@section('content')

    <div class="pb-3 pt-3">

        <div class="container-fluid main-container">

            <div class="row">

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    <div class="row p-5">
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                            <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                                <a type="button" id="getTopicButton" class="d-none d-sm-block d-md-none d-lg-block ms-2 btn btn-primary btn btn-primary" href="{{$urlAppend}}modules/help/help.php?language={{$language}}&topic={{$helpTopic}}&subtopic={{$helpSubTopic}}" style='margin-top:-10px'>
                                    <i class="fas fa-question"></i>
                                </a>
                            </nav>
                            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                        </div>

                        <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3">
                            <legend class="float-none w-auto py-2 px-4 notes-legend"><span style="margin-left:-20px;"><i class="fa fa-gavel"></i> {{trans('langUsageTerms')}}</span></legend>
                            <div class="row p-2"></div>
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
