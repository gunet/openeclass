@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                

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

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <div class='alert alert-info'>{!! $infoText !!}</div>
                    </div>

                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <div class='form-wrapper shadow-sm p-3 mt-5 rounded'>
                            
                            <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                                <fieldset>
                                    {!! $monthsField !!}
                                    <div class='form-group mt-3'>
                                        <label class='col-sm-6 control-label-notes'>{{ trans('langMultiDelUserData') }}:</label>
                                        <div class='col-sm-12'>
                                            <textarea class='auth_input form-control' name='user_names' rows='30'>{{ $usernames }}</textarea>
                                        </div>
                                    </div>
                                    {!! showSecondFactorChallenge() !!}
                                    <div class='form-group mt-3'>
                                        <div class='col-sm-10 col-sm-offset-2'>
                                            <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langSubmit') }}'{!! $confirm !!}>
                                            <a href='index.php' class='btn btn-secondary'>{{ trans('langCancel') }}</a>
                                        </div>
                                    </div>
                                </fieldset>
                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection