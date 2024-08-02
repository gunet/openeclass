@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    @include('layouts.partials.legend_view')

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    @include('layouts.partials.show_alert') 

                    <div class='col-lg-6 col-12'>
                        <form class='form-wrapper form-edit border-0 px-0' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
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
                            <div class='form-group mt-4'>
                                <div class='col-12 d-flex justify-content-end gap-2 flex-wrap'>
                                    <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langModify') }}'>
                                    <a href='extapp.php' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                        <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                    </div>
        </div>
</div>
</div>
@endsection
