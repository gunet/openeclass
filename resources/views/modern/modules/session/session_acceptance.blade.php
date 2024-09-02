@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')
                    
                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert') 

                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class="card panelCard border-card-left-default px-lg-4 py-lg-3">
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                    <h3 class='mb-0'>{{ trans('langDetailsSession') }}</h3>
                                </div>
                                <div class='card-body'>
                                    @if(is_session_visible($course_id,$sessionID))
                                        @foreach($session_info as $s)
                                            <ul class='list-group list-group-flush'>
                                                <li class='list-group-item element'>
                                                    <div class='row row-cols-1 g-1'>
                                                        <div class='col-12'>
                                                            <div class='title-default'>{{ trans('langTitle') }}</div>
                                                        </div>
                                                        <div class='col-12 title-default-line-height'>
                                                            {{ $s->title }}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class='list-group-item element'>
                                                    <div class='row row-cols-1 g-1'>
                                                        <div class='col-12'>
                                                            <div class='title-default'>{{ trans('langConsultant') }}</div>
                                                        </div>
                                                        <div class='col-12 title-default-line-height'>
                                                            {!! participant_name($s->creator) !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class='list-group-item element'>
                                                    <div class='row row-cols-1 g-1'>
                                                        <div class='col-12'>
                                                            <div class='title-default'>{{ trans('langStartDate') }}</div>
                                                        </div>
                                                        <div class='col-12 title-default-line-height'>
                                                        {!! format_locale_date(strtotime($s->start), 'short') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class='list-group-item element'>
                                                    <div class='row row-cols-1 g-1'>
                                                        <div class='col-12'>
                                                            <div class='title-default'>{{ trans('langEndDate') }}</div>
                                                        </div>
                                                        <div class='col-12 title-default-line-height'>
                                                            <div class='d-flex justify-content-start align-items-center gap-4 flex-wrap'>
                                                            {!! format_locale_date(strtotime($s->finish), 'short'); !!}  
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class='list-group-item element'>
                                                    <div class='row row-cols-1 g-1'>
                                                        <div class='col-12'>
                                                            <div class='title-default'>{{ trans('langTypeRemote') }}</div>
                                                        </div>
                                                        <div class='col-12 title-default-line-height'>
                                                            @if($s->type_remote)
                                                                {{ trans('langRemote') }}
                                                            @else
                                                                {{ trans('langNotRemote') }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class='list-group-item element'>
                                                    <div class='row row-cols-1 g-1'>
                                                        <div class='col-12'>
                                                            <div class='title-default'>{{ trans('langSSession') }}</div>
                                                        </div>
                                                        <div class='col-12 title-default-line-height'>
                                                            @if($s->type='one')
                                                                {{ trans('langIndividualS') }}
                                                            @else
                                                                {{ trans('langGroupS') }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        @endforeach

                                        @if(!$is_participant)
                                            <div class='form-wrapper form-edit rounded'>
                                                <form role='form' class='form-horizontal' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&session={{ $sessionID }}" method='post'>
                                                    <fieldset>
                                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                                        <p>{!! trans('langQuestionAcceptanceSession') !!}</p>
                                                        <input type='hidden' name='userId' value='{{ $uid }}' >
                                                        {!! generate_csrf_token_form_field() !!}    
                                                        <div class='form-group mt-5'>
                                                            <div class='col-12 d-flex justify-content-end aling-items-center gap-3'>
                                                                <button class='btn cancelAdminBtn' type='submit' name='submit' value='no_acceptance'>{{ trans('langNo') }}</button>
                                                                <button class='btn submitAdminBtn' type='submit' name='submit' value='acceptance'>{{ trans('langAccept') }}</button>
                                                            </div>
                                                        </div>
                                                    </fieldset>
                                                </form>
                                            </div>
                                        @endif
                                    @else
                                        <div class='alert alert-warning'>
                                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                            <span>{{ trans('langNoInfoAvailable') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
