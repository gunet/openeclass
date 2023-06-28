@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif
                    
                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    

                    
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>
                    <div class='col-lg-6 col-12'>
                        <form class='form-wrapper form-edit rounded' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
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
                            <input class='btn submitAdminBtn m-auto d-block mt-5' type='submit' name='submit' value='{{ trans('langModify') }}'>
                        </form>
                    </div>
                   
                </div>
            </div>
        </div>

</div>
@endsection