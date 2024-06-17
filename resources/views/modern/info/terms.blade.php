@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
        <div class='{{ $container }} main-container'>
                <div class="row m-auto">
                        <div class='col-12'>
                                <h2>{{ $toolName }}</h2>
                        </div>

                        <div class='col-12 mt-4'>
                                {!! $terms !!}<
                        </div>
                </div>
        </div>
</div>

@endsection
