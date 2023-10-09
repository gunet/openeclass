@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">
            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
            @include('layouts.partials.legend_view')
            {!! $action_bar !!}
            <div class='col-xl-9 col-lg-8 col-md-12 col-sm-12 col-12 forms-panels-admin'>
                <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>

                        <div class='panel-body'>

                            <div class='form-group mt-4'>
                                <label for='formpostaddress' class='col-sm-12 control-label-notes'>{{ trans('langPostMail') }}</label>
                                <div class='col-sm-12'>
                                    <textarea class='form-control form-control-admin' name='formpostaddress' id='formpostaddress'>{{ get_config('postaddress') }}</textarea>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label for='formtelephone' class='col-sm-12 control-label-notes'>{{ trans('langPhone') }}:</label>
                                <div class='col-sm-12'>
                                    <input class='form-control form-control-admin' type='text' name='formtelephone' id='formtelephone' value='{{ get_config('phone') }}'>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label for='formemailhelpdesk' class='col-sm-12 control-label-notes'>{{ trans('langHelpDeskEmail') }}:</label>
                                <div class='col-sm-12'>
                                    <input class='form-control form-control-admin' type='text' name='formemailhelpdesk' id='formemailhelpdesk' value='{{ get_config('email_helpdesk') }}'>
                                </div>
                            </div>

                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-center'>
                                    <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langModify') }}'>
                                    <a class='btn cancelAdminBtn ms-1' href='index.php'>{{ trans('langCancel') }}</a>
                                </div>
                            </div>

                        </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection
