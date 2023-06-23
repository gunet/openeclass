@extends('layouts.default')

@section('content')
  
<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">
    <div class="row rowMargin">

        @if($course_code and $currentCourseName and !isset($_GET['fromFlipped']))
            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-3"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>
        @endif

        @if($course_code and $currentCourseName and !isset($_GET['fromFlipped']))
            <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active p-lg-5">
        @else
            <div class="col-12 col_maincontent_active_Homepage">
        @endif
                <div class="row">
                
                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @if($course_code and $currentCourseName)
                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>
                    @endif
                    

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
                    
                    <div class='col-12'>{!! $tool_content !!}</div>
                </div>    
        </div>
    </div>
</div>

@endsection


