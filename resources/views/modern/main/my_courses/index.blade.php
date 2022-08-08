@extends('layouts.default')

@section('content')

        <div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

            <div class="container-fluid main-container">


                <div class="row rowMedium">

                    @include('layouts.partials.all_my_courses_view',['myCourses' => $myCourses])

                </div>

            </div>
        </div>

@endsection


