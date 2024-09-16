@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    <div class='mt-4'></div>

                    @include('layouts.partials.show_alert') 

                    <div class='col-12'>
                        <div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>
                            {{ trans('langCleanupInfo') }}</span>
                        </div>
                    </div>

                    <div class='col-sm-12 col-sm-offset-5'>
                        <form method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                            {!! showSecondFactorChallenge() !!}
                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langCleanup') }}'>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div>

        </div>
</div>
</div>
@endsection
