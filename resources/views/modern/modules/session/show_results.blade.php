@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <nav id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </nav>

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

                    <div class='d-lg-flex gap-4'>
                        <div class='flex-grow-1'>
                            @if(count($completedSessionByUsers) > 0)
                                <div class='col-12'>
                                    <div class='row row-cols-1 row-cols-lg-2 g-4'>
                                        @foreach($completedSessionByUsers as $c => $key)
                                            @if($is_simple_user)
                                                @php $hasIncompletedPrereq = false; @endphp
                                                @foreach($key as $q)
                                                    @if($q['hasIncompletePrereq'])
                                                        @php $hasIncompletedPrereq = true; @endphp
                                                    @endif
                                                @endforeach
                                            @endif
                                            <div class='col'>
                                                <div class='card panelCard border-card-left-default px-lg-4 py-lg-3 h-100'>
                                                    <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                                        <a class="link-color normal-text TextBold
                                                                    @if($is_simple_user && session_not_started($course_id,$c)) pe-none opacity-help @endif
                                                                    @if($is_simple_user && $hasIncompletedPrereq) pe-none opacity-help @endif" 
                                                            href="{{ $urlAppend }}modules/session/session_space.php?course={{ $course_code }}&session={{ $c }}">
                                                            {!! title_session($course_id,$c) !!}
                                                        </a>
                                                        <span class='TextBold'>{!! date_session($course_id,$c) !!}</span>
                                                    </div>
                                                    <div class='card-body'>
                                                        <ul class='list-group list-group-flush'>
                                                            @foreach($key as $k)
                                                                <li class='list-group-item element'>
                                                                    <div class='d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                                                        <span class='TextBold'>{!! $k['user'] !!}</span>
                                                                        {!! $k['icon'] !!}
                                                                    </div>
                                                                    <div class='small-text'>{!! $k['info'] !!}</div>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <div class='card-footer border-0'>
                                                        <ul class='list-group list-group-flush'>
                                                            @if(session_has_expired($course_id,$c))
                                                                <li class='list-group-item element'><span class='badge Accent-200-bg'>{{ trans('langSessionHasExpired') }}</span></li>
                                                            @endif
                                                            @if(session_is_running($course_id,$c))
                                                                <li class='list-group-item element'>{{ trans('langInProgress') }}</li>
                                                            @endif
                                                            @if($is_simple_user && $hasIncompletedPrereq) 
                                                                <li class='list-group-item element'>{{ trans('langExistsInCompletedPrerequisite') }}</li>
                                                            @endif
                                                            @if(session_not_started($course_id,$c)) 
                                                                <li class='list-group-item element'>{{ trans('langSessionNotStarted') }}</li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class='alert alert-warning'>
                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                    <span>{{ trans('langNoInfoAvailable')}}</span>
                                </div> 
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
