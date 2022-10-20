@extends('layouts.default')

@section('content')

    <div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

        <div class="container-fluid main-container">

            <div class="row rowMedium">

                <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                    <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

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
                            <div class='panel panel-default rounded-0'>
                                <div class='panel-heading rounded-0'>
                                    <div class='panel-title'>
                                       {!! $announcement->title !!}
                                    </div>
                                </div>
                                <div class='panel-body rounded-0'>
                                    {!! $announcement->content !!}
                                </div>
                                <div class='panel-footer rounded-0'>
                                    <div class='row'>
                                        <div class='col-6'>
                                            @if($announcement->code)
                                            <div class='text-start fw-bold'>
                                                <a href='{{$urlAppend}}modules/announcements/index.php?course={{$announcement->code}}&an_id={{$announcement->id}}'>{!! $announcement->course_title !!}</a></div>
                                            @endif
                                        </div>
                                        <div class='col-6'>
                                            <div class='text-end info-date'>{!! $announcement->an_date !!}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    

@endsection
