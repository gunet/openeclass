@extends('layouts.default')

@push('head_scripts')
<link type="text/css" rel="stylesheet" media="all" href="{{$urlServer}}/js/h5p-standalone/styles/h5p.css" />
<script type="text/javascript" src="{{$urlServer}}/js/h5p-standalone/main.bundle.js"></script>
@endpush

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-3"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active">
                          
                      <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                            <div class="offcanvas-header">
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            </div>
                        </div>


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

                        
                        <div class="col-12 mt-4">
                            <div id="h5p-container"></div>
                        </div>
                        
                </div>
            </div>
        </div>
    </div>
</div>
	
<script type='text/javascript'>
        $(document).ready(function() {
            const el = document.getElementById('h5p-container');
            const options = {
              h5pJsonPath:  '{{$workspaceUrl}}',
              librariesPath: '{{$workspaceLibs}}',
              frameJs: '{{$urlServer}}/js/h5p-standalone/frame.bundle.js',
              frameCss: '{{$urlServer}}/js/h5p-standalone/styles/h5p.css',
              frame: true,
              copyright: true,
              icon: true,
              fullScreen: true
            };
            new H5PStandalone.H5P(el, options);
        });
    </script>
@endsection
