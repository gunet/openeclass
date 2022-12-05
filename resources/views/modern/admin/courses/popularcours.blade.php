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
                    
                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if($courses)
                        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                            <div class='col-12 h-100 left-form'></div>
                        </div>

                        <div class='col-lg-6 col-12'>
                            <div class='form-wrapper form-edit rounded'>
                                
                                <form role='form' class='form-horizontal' action='{{$urlAppend}}modules/admin/popularcourse.php' method='post'>
                                    <fieldset>      
                                        <div class='row'>
                                            @foreach($courses as $course)
                                                <div class='col-md-6 col-12'>
                                                    <div class='form-group mb-4'>
                                                        <div class='checkbox'>
                                                            <label>
                                                                <input type='checkbox' name='set_popular_course[]' value="{{ $course->id }}" {{ $course->popular_course == 1 ? 'checked' : '' }}>
                                                                {{$course->title}}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <div class='form-group d-flex justify-content-center align-items-center mt-4'>
                                                <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmitChanges') }}'>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class='col-12'>
                            <div class='alert alert-warning'>{{trans('langNoCourses')}}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection