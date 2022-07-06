@extends('layouts.default_old')

@section('content')

{!! $action_bar !!}

@if(Session::has('message'))
<div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
    <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
        {{ Session::get('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </p>
</div>
@endif

<div class='alert alert-success'>
    @if (!isset($_GET['from_user']))
        {{ trans('langRefreshSuccess') }}
    @endif
    <ul class='listBullet'>
        @for ($i = 0; $i < $count_events; $i++) 
            <li>{{ $output[$i] }}</li>
        @endfor    
    </ul>
</div>

@endsection