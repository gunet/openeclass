@extends('layouts.default')

@section('content')

    <div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

            <div class="row rowMargin">

                <div class="col-12 col_maincontent_active_Homepage">
                    <div class="row">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                        {!! $action_bar !!}

                        @if(Session::has('message'))
                        <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php 
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp
                            
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                        @endif

                        
                    
                        <div class='col-12'>
                            <div class='card panelCard px-lg-4 py-lg-3'>
                                <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                    <h3>
                                       {!! $announcement->title !!}
                                    </h3>
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
    

@endsection
