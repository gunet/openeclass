@extends('layouts.default')

@push('head_styles')
    <style>
        a {
            background-color:transparent !important;
            text-decoration:none !important;
        }
        .col_maincontent_active_Homepage{
            transition: 0.4s;
            min-height:auto;
            background-color: white;
            border-radius:15px;
        }
        pre {margin: 0px; font-family: monospace;}
        table {border-collapse: collapse;}
        .center {text-align: center;}
        .center table { margin-left: auto; margin-right: auto; text-align: left;}
        .center th { text-align: center !important; }
        td, th { border: 1px solid #000000; font-size: 75%; vertical-align: baseline;}
        h1 {font-size: 150%;}
        h2 {font-size: 125%;}
        .p {text-align: left;}
        .e {background-color: #ccccff; font-weight: bold; color: #000000;}
        .h {background-color: #9999cc; font-weight: bold; color: #000000;}
        .v {background-color: #cccccc; color: #000000;}
        .vr {background-color: #cccccc; text-align: right; color: #000000;}
        img {float: right; border: 0px;}
        hr {width: 600px; background-color: #cccccc; border: 0px; height: 1px; color: #000000;}
    </style>
@endpush

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            <div class='col-12'>
                <span>
                    {!! phpinfo() !!}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
