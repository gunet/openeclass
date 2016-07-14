@extends('layouts.default')

@section('content')
    {!!
    action_bar(array(
        array('title' => $GLOBALS['langBack'],
              'url' => $backPath,
              'icon' => 'fa-reply',
              'level' => 'primary-label')
        )
    )
    !!}
    
    @if ($jsonObj !== null && property_exists($jsonObj, "resources"))
        {!! displayDelosForm($jsonObj, getCurrentVideoLinks()) !!}
    @else
        <div class='alert alert-warning' role='alert'>{{ trans('langNoVideo') }}</div>
    @endif
    
@endsection

