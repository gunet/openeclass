@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">

                <div class="row p-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => [0 => ['bread_href' => 'about.php', 'bread_text' => trans('langUsageTerms') ]]])

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <div class='row'>
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
