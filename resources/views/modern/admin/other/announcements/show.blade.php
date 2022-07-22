@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if($announcementsID)
                       
                        <div class='col-sm-12'>
                            <div class="panel panel-default">
                                <div class="panel-heading">                   
                                    <span class='control-label-notes'>{{$announcementsID->title}}</span>
                                </div>
                                <div class="panel-body">
                                    <span class="text-secondary">
                                        {!! $announcementsID->body !!}
                                    </span>
                                </div>
                                <div class='panel-footer'>
                                    <div class='text-primary text-end'>{{trans('langDate')}}: <span class='text-secondary'>{{ $announcementsID->date }}</span></div>
                                </div>
                            </div>
                        </div>
                       
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection