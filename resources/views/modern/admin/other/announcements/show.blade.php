@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-xl-5 px-lg-0 py-lg-3 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if($announcementsID)
                       
                        <div class='col-12'>
                            <div class="card panelCard px-lg-4 py-lg-3">
                                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">                   
                                    <div class='text-uppercase normalColorBlueText TextBold fs-6'>{{$announcementsID->title}}</div>
                                </div>
                                <div class="card-body">
                                    
                                        {!! $announcementsID->body !!}
                                    
                                </div>
                                <div class='card-footer bg-white border-0 d-flex justify-content-start align-items-center'>
                                    <div class='announcement-date info-date small-text'>{!! format_locale_date(strtotime($announcementsID->date)) !!}</div>
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