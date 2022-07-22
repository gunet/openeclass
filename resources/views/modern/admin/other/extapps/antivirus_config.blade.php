@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='extapp'>
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                            <form class='form-wrapper shadow-sm p-3 rounded' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                                <fieldset>
                                    <table class='table table-bordered' width='100%'>
                                        <tr>
                                        <th width='200' class='left'>
                                            <b>{{ trans('langAntivirusConnector') }}</b>
                                        </th>
                                        <td>
                                            <select name='formconnector'>{!! implode('', $connectorOptions) !!}</select>
                                        </td>
                                        </tr>
                                        @foreach($connectorClasses as $curConnectorClass)
                                            @foreach((new $curConnectorClass())->getConfigFields() as $curField => $curLabel)
                                                <tr class='connector-config connector-{{ $curConnectorClass }}' style='display: none;'>
                                                    <th width='200' class='left'>
                                                        <b>{{ $curLabel }}</b>
                                                    </th>
                                                    <td>
                                                        <input class='FormData_InputText' type='text' name='form{{ $curField }}' size='40' value='{{ get_config($curField) }}'>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </table>
                                </fieldset>
                                {!! generate_csrf_token_form_field() !!}
                                <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langModify') }}'>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection