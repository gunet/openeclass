@extends('layouts.default')

@section('content')

    <main id="main" class="col-12 main-section">
        <div class='{{ $container }} main-container'>

            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                <div class='alert alert-danger'>
                    <i class='fa-solid fa-circle-xmark fa-lg'></i>
                    <span>$message</span>
                </div>

            </div>
        </div>
    </main>
@endsection
