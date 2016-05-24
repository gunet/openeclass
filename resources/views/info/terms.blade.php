@extends('layouts.default')

@section('content')

    {!! $action_bar !!}
    <div class='row'>
        <div class='col-xs-12'>
            <div class='panel'>
                <div class='panel-body'>
                    {!! $terms !!} 
                </div>
            </div>
        </div>
    </div>

@endsection
