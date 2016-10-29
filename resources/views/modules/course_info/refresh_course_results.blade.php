@extends('layouts.default')

@section('content')

{!! $action_bar !!}

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