@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])


                    {!! $action_bar !!}

                    
                    <div class="col-12">
                        <div class="card panelCard px-lg-4 py-lg-3">
                            <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                <h3>{!! $title !!}</h3>
                            </div>
                        
                            <div class="card-body">
                                <div class="single_announcement">
                                    <div class='announcement-main'>
                                        {!! $body !!}
                                    </div>
                                </div>
                            </div>
                            <div class='card-footer bg-white border-0 d-flex justify-content-start align-items-center'>
                                <div class="announcement-date info-date small-text">
                                     {!! $date !!} 
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                </div>
            </div>
        </div>

</div>

@endsection


