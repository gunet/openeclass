@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

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

                    @if ($u_account && $c)
                    <div class='col-12'>
                        
                            <div class='alert alert-warning'>
                                {{ trans('langConfirmDeleteQuestion1') }} 
                                <em>{{ $u_realname }} ({{ $u_account }})</em>
                                {{ trans('langConfirmDeleteQuestion2') }} 
                                <em>{{ course_id_to_title($c) }}</em>
                            </div>
                            <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                <a class='btn submitAdminBtn' href='{{ $_SERVER['SCRIPT_NAME'] }}?u={{ $u }}&amp;c={{ $c }}&amp;doit=yes'>{{ trans('langDelete') }}</a>
                            </div>
                       
                    </div>
                    @else
                    <div class='col-12'>
                        <div class='alert alert-danger'>{{ trans('langErrorUnreguser') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
   
</div>              
@endsection