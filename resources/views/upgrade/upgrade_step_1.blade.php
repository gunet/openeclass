@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>

            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                <div class='col-12 d-flex justify-content-center mt-5'>

                    <div class='card panelCard px-lg-4 py-lg-3'>
                        <div class='card-body'>
                            <form class='form-wrapper' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>

                                <h4 class="mt-2">
                                    {{ trans('langCheckReq') }}
                                </h4>

                                <ul class='list-group list-group-flush'>
                                    <li class='list-group-item element'><i class="fa-solid fa-check" style="color: green"></i> <strong>Webserver</strong>
                                        <em> {{ ($_SERVER['SERVER_SOFTWARE']) }} </em>
                                    </li>
                                </ul>

                                <ul class='list-group list-group-flush'>
                                    <h4 class="mt-2">
                                        {{ trans('langPHPVersion') }}
                                    </h4>
                                    {!! checkPHPVersion('8.0'); !!}
                                </ul>

                                <h4 class="mt-2">
                                    {{ trans('langRequiredPHP') }}
                                </h4>
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

                                <h4 class="mt-2">
                                    {{ trans('langOptionalPHP') }}
                                </h4>
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

                    @include('upgrade.upgrade_menu', [ 'upgrade_menu' => upgrade_menu() ] )

                </div>

            </div>
        </div>
    </div>

@endsection

