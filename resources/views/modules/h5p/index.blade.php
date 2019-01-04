@extends('layouts.default')

@section('content')

{!! $action_bar !!}

    <div class="row">
        <div class="col-xs-12">
		@if ($is_editor)
			I'm an editor!
		@else
			I'm a student!
		@endif
		Hello H5p!
        </div>
    </div>
@endsection
