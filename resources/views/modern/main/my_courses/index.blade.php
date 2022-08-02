@extends('layouts.default')

@section('content')

        <div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

            <div class="container-fluid main-container">


                <div class="row rowMedium">

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif
                   
                       
                        @include('layouts.partials.all_my_courses_view',['myCourses' => $myCourses])

                </div>

            </div>
        </div>

@endsection


