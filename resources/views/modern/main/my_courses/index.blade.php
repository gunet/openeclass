@extends('layouts.default')

@section('content')

        <div class="pb-3 pt-3">

            <div class="container-fluid main-container">


                <div class="row ">

                        @if(Session::has('message'))
                            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                                <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                    {{ Session::get('message') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </p>
                            </div>
                        @endif
                   
                        @if($myCourses)
                           @include('layouts.partials.all_my_courses_view',['myCourses' => $myCourses])
                        @else
                            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                                <div class='alert alert-warning'>{{ trans('langNoCourses') }}</div>
                            </div>
                        @endif
                </div>

            </div>
        </div>

@endsection


