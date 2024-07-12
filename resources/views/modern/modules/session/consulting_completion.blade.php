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

                    <div class='col-12'>
                        @if(count($users_actions) > 0)
                            <div class='card panelCard border-card-left-default px-lg-4 py-lg-3'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                    <h3 class='mb-0'>{{ trans('langSessionsTable')}}</h3>
                                </div>
                                <div class='card-body'>
                                    <div class='alert alert-info'>
                                        <i class='fa-solid fa-circle-info fa-lg'></i>
                                        <span>{!! trans('langShowOnlySessionWithCompletionEnable') !!}</span>
                                    </div>
                                    <div class='table-responsive'>
                                        <table class='table-default'>
                                            <thead></thead>
                                            <tbody>
                                                @foreach($users_actions as $key => $val)
                                                    <tr>
                                                        <td>
                                                            <div class='d-flex justify-content-start align-items-center gap-4'>
                                                                <div style='width:150px;'>{!! display_user($key) !!}</div>
                                                                <div class='d-flex justify-content-start align-items-center gap-4 flex-wrap'>
                                                                    @foreach($val as $v)
                                                                        <div>
                                                                            <strong>{!! $v->title !!}</strong></br>
                                                                            {!! format_locale_date(strtotime($v->start), 'short', false) !!}</br>
                                                                            {!! $v->completion !!}
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class='alert alert-warning'>
                                <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                <span>{{ trans('langNoInfoAvailable') }}</span>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    
    </div>
</div>

@endsection
