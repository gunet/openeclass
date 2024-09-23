@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>

            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                <div class='row row-cols-lg-2 row-cols-1 g-4 mt-4 mb-3'>
                    <div class='col-md-7 col-lg-8'>
                        <div class='card panelCard card-default px-lg-4 py-lg-3'>
                            <div class='card-body'>
                                <form class='form-wrapper' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>

                                    <div class="text-heading-h4 mt-2">
                                        {{ trans('langCheckReq') }}
                                    </div>

                                    <ul class='list-group list-group-flush'>
                                        <li class='list-group-item element'><i class="fa-solid fa-check Success-200-cl"></i> <strong>Webserver</strong>
                                            <em> {{ ($_SERVER['SERVER_SOFTWARE']) }} </em>
                                        </li>
                                    </ul>

                                    <ul class='list-group list-group-flush'>
                                        <div class="text-heading-h4 mt-2">
                                            {{ trans('langPHPVersion') }}
                                        </div>
                                        {!! checkPHPVersion('8.0'); !!}
                                    </ul>

                                    <div class="text-heading-h4 mt-2">
                                        {{ trans('langRequiredPHP') }}
                                    </div>
                                    <ul class='list-group list-group-flush'>
                                        {!! warnIfExtNotLoaded('pdo_mysql') !!}
                                        {!! warnIfExtNotLoaded('gd') !!}
                                        {!! warnIfExtNotLoaded('mbstring') !!}
                                        {!! warnIfExtNotLoaded('xml'); !!}
                                        {!! warnIfExtNotLoaded('zlib') !!}
                                        {!! warnIfExtNotLoaded('pcre') !!}
                                        {!! warnIfExtNotLoaded('curl') !!}
                                        {!! warnIfExtNotLoaded('zip') !!}
                                        {!! warnIfExtNotLoaded('intl') !!}
                                    </ul>

                                    <div class="text-heading-h4 mt-2">
                                        {{ trans('langOptionalPHP') }}
                                    </div>
                                    <ul class='list-group list-group-flush'>
                                        {!! warnIfExtNotLoaded('soap'); !!}
                                        {!! warnIfExtNotLoaded('ldap'); !!}
                                    </ul>

                                    @if (ini_get('register_globals'))
                                        <div class='caution'>
                                            {{ trans('langWarningInstall1') }}
                                        </div>
                                    @endif

                                    @if (ini_get('short_open_tag'))
                                        <div class='caution'>
                                            {{ trans('langWarningInstall2') }}
                                        </div>
                                    @endif

                                    <div class='col-12 d-flex justify-content-center mt-5'>
                                        <input class='btn btn-primary' name='submit_2' value='{{ trans('langContinue') }} &raquo;' type='submit'>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>

                    @include('upgrade.upgrade_menu', [ 'upgrade_menu' => upgrade_menu() ] )

                </div>

            </div>
        </div>
    </div>

@endsection

