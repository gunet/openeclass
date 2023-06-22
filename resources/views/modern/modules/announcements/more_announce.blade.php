@extends('layouts.default')

@section('content')

    <div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

        <div class="container-fluid main-container">

            <div class="row rowMedium">

                <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                    <div class="row p-xl-5 px-lg-0 py-lg-3 p-md-5 ps-1 pe-1 pt-5 pb-5">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                        @if(Session::has('message'))
                        <div class='col-12 all-alerts'>
                            <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                @if(is_array(Session::get('message')))
                                    @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                    @foreach($messageArray as $message)
                                        {!! $message !!}
                                    @endforeach
                                @else
                                    {!! Session::get('message') !!}
                                @endif
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                        @endif

                        {!! $action_bar !!}
                    
                        <div class='col-12'>
                            <div class='card panelCard px-lg-4 py-lg-3'>
                                <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                    <div class='text-uppercase normalColorBlueText TextBold fs-6'>
                                       {!! $announcement->title !!}
                                    </div>
                                </div>
                                <div class='card-body'>
                                    {!! $announcement->content !!}
                                </div>
                                <div class='card-footer d-flex justify-content-between align-items-center small-text bg-white border-0'>
                                       
                                    @if($announcement->code)
                                        <a class='TextBold' href='{{$urlAppend}}modules/announcements/index.php?course={{$announcement->code}}&an_id={{$announcement->id}}'>{!! $announcement->course_title !!}</a>
                                    @endif
                                
                                    <div>{!! $announcement->an_date !!}</div>
                                        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    

@endsection
