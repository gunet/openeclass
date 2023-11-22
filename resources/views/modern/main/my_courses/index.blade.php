@extends('layouts.default')

@section('content')

        <div class="col-12 main-section">
            <div class='{{ $container }} main-container'>
                <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

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


                    <div class='col-lg-4 col-md-6 col-12 mb-4'>
                        <input id='searchCourse' type="text" class="form-control"
                                    placeholder="&#xf002&nbsp;&nbsp;{{ trans('langSearch') }}..." aria-label="{{ trans('langSearch') }}">
                    </div>

                    <div id='MyCourses'></div>

                </div>
            </div>
        </div>

        <script type='text/javascript'>
            jQuery(document).ready(function() {
                $.ajax({
                    type: 'POST',
                    url: '{{$urlAppend}}main/my_courses.php?term=',
                    success: function(json){
                        if(json){
                            $('#MyCourses').html(json);
                        }
                    }
                });
                $('#searchCourse').keyup(function() {
                    var searchval = $('#searchCourse').val();
                    $.ajax({
                        type: 'POST',
                        url: '{{$urlAppend}}main/my_courses.php?term='+searchval,
                        success: function(json){
                            if(json){
                                $('#MyCourses').html(json);
                            }
                        }
                    });
                });
            });
        </script>

@endsection
