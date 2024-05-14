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

                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                    @endif


                    <div class='col-12 mb-4'>
                        <div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4'>
                            @if(get_config('show_collaboration') and !get_config('show_always_collaboration'))
                                <div class='col'>
                                    <select id='select-type-course' class='form-select'>
                                        <option value="0" selected>{{ trans('langCourse') }}</option>
                                        <option value="1">{{ trans('langTypeCollaboration') }}</option>
                                    </select>
                                </div>
                            @endif
                            <div class='col'>
                                <input id='searchCourse' type="text" class="form-control"
                                            placeholder="&#xf002&nbsp;&nbsp;{{ trans('langSearch') }}..." aria-label="{{ trans('langSearch') }}">
                            </div>
                        </div>
                    </div>

                    <div id='MyCourses'></div>

                    <input id='type-course' type='hidden' value='{{ $collaboration_value }}'>

                </div>
            </div>
        </div>

        <script type='text/javascript'>
            jQuery(document).ready(function() {
                var typeVal = document.getElementById('type-course').value;
                $.ajax({
                    type: 'POST',
                    url: '{{ $urlAppend }}main/my_courses.php?term=',
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
                        url: '{{ $urlAppend }}main/my_courses.php?term='+searchval+'&typeCourse='+typeVal,
                        success: function(json){
                            if(json){
                                $('#MyCourses').html(json);
                            }
                        }
                    });
                });
                $('#select-type-course').change(function() {
                    typeVal = $(this).val();
                    $.ajax({
                        type: 'POST',
                        url: '{{ $urlAppend }}main/my_courses.php?term=&typeCourse='+typeVal,
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
