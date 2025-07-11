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
                    <div class='form-wrapper form-edit border-0 px-0'>

                        <form class='form-horizontal' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>

                            @foreach($appParams as $param)
                                @if($param->name() == SolrApp::SOLRURL)
                                    <div class='form-group mb-4'>
                                        <label for='{{ $param->name() }}' class='col-12 control-label-notes'>
                                            {{ $param->display() }}&nbsp;&nbsp;
                                            <span class='fa fa-info-circle' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langSolrUrl') }}'></span>
                                        </label>
                                        <div class='col-12'>
                                            <input id='{{ $param->name() }}' class='form-control' type='text' name='{{$param->name() }}' value='{{ $param->value() }}' placeholder='{{ SolrApp::SOLRDEFAULTURL }}'>
                                        </div>
                                    </div>
                                @elseif ($param->getType() != ExtParam::TYPE_BOOLEAN)
                                    <div class='form-group mb-4'>
                                        <label for='{{ $param->name() }}' class='col-12 control-label-notes'>
                                            {{ $param->display()}}
                                        </label>
                                        <div class='col-12'>
                                            <input id='{{ $param->name() }}' class='form-control' type='text' name='{{ $param->name() }}' value='{{ $param->value() }}'>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            @foreach($appParams as $param)
                                @if($param->getType() == ExtParam::TYPE_BOOLEAN)
                                    {!! $checked = $param->value() == 1 ? "checked" : ""; !!}
                                    <div class='form-group mb-4'>
                                        <div class='col-sm-offset-2 col-sm-10'>
                                            <div class='checkbox'>
                                                <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                    <input type='checkbox' name='{{ $param->name() }}' value='1' {{ $checked }}><span class='checkmark'></span>{{ $param->display() }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-end align-items-center'>
                                    {!! form_buttons(array(
                                        array(
                                            'class' => 'submitAdminBtn',
                                            'text' => trans('langSubmit'),
                                            'name' => 'submit',
                                            'value'=> trans('langSubmit')
                                        )
                                    )) !!}

                                    {!! form_buttons(array(
                                        array(
                                            'class' => 'deleteAdminBtn',
                                            'text' => trans('langClearSettings'),
                                            'name' => 'submit',
                                            'value'=> 'clear'
                                        )
                                    )) !!}

                                    {!! form_buttons(array(
                                        array(
                                            'class' => 'cancelAdminBtn ms-1',
                                            'href' => "extapp.php"
                                        )
                                    )) !!}
                                </div>
                            </div>

                            {!! generate_csrf_token_form_field() !!}

                        </form>
                    </div>
                </div>

                <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                </div>

            </div>
        </div>
    </div>
@endsection