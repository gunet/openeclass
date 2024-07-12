@extends('layouts.default')

@push('head_scripts')
    <!-- About deletion -->
    <script>
        $(function() {
            $(document).on('click', '.user-register', function(e){
                e.preventDefault();
                var userID = $(this).attr('data-id');
                document.getElementById("addUserId").value = userID;
            });
            $(document).on('click', '.user-unregister', function(e){
                e.preventDefault();
                var userID = $(this).attr('data-id');
                document.getElementById("deleteUserId").value = userID;
            });
        });
    </script>
@endpush

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

                    @include('layouts.partials.show_alert') 

                    <div class='col-12 mt-4'>
                        <div class="card panelCard border-card-left-default px-lg-4 py-lg-3">
                            <div class='card-header border-0'>
                                <div class='d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                    <h3>{{ trans('langDetails') }}</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(count($all_users) > 0)
                                    <div class='table-responsive'>
                                        <table class='table-default'>
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('langUsers') }}</th>
                                                    <th>{{ trans('langRegisteredUsers') }}</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($all_users as $u)
                                                    <tr>
                                                        <td>{!! display_user($u->participants) !!}</td>
                                                        <td>
                                                            @if($u->is_accepted)
                                                                <span class='badge Success-200-bg'><i class='fa-solid fa-check fa-lg'></i></span>
                                                                &nbsp;<span>{{ trans('langUserHasConsent')}}</span>
                                                            @else
                                                                <div class='d-flex justify-content-start align-items-center gap-3 flex-wrap'>
                                                                    <div class="spinner-border link-color" role="status" style="width:20px; height:20px;">
                                                                        <span class="visually-hidden"></span>
                                                                    </div>
                                                                    <span>{{ trans('langUserConsentUnknown')}}</span>
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class='text-end'>
                                                            @if($is_editor || !$is_course_reviewer)
                                                                {!! 
                                                                    action_button(array(
                                                                        array(
                                                                            'title' => trans('langSubmitRegistration'),
                                                                            'url' => "#",
                                                                            'icon' => 'fa-solid fa-check',
                                                                            'icon-class' => "user-register",
                                                                            'icon-extra' => "data-bs-toggle='modal' data-bs-target='#doRegistration' data-id='{$u->participants}'",
                                                                            'show' => !$u->is_accepted
                                                                        ),
                                                                        array(
                                                                            'title' => trans('langNoSubmitRegistration'),
                                                                            'url' => "#",
                                                                            'icon' => 'fa-xmark',
                                                                            'icon-class' => "user-unregister",
                                                                            'icon-extra' => "data-bs-toggle='modal' data-bs-target='#undoRegistration' data-id='{$u->participants}'",
                                                                            'show' => $u->is_accepted
                                                                        )

                                                                    ))
                                                                !!}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
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
</div>




<div class='modal fade' id='doRegistration' tabindex='-1' aria-labelledby='doRegistrationLabel' aria-hidden='true'>
    <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&session={{ $sessionID }}">
        <div class='modal-dialog modal-md'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <div class='modal-title'>
                        <div class='icon-modal-default'><i class='fa-solid fa-check fa-xl Neutral-500-cl'></i></div>
                        <h3 class="modal-title-default text-center mb-0 mt-2" id="doRegistrationLabel">{!! trans('langSubmitRegistration') !!}</h3>
                    </div>
                </div>
                <div class='modal-body text-center'>
                    <input type='hidden' name='addUserId' id='addUserId'>
                    <input type='hidden' name='token' value="{{ $_SESSION['csrf_token'] }}">
                    {!! trans('langContinueToBooking') !!}
                </div>
                <div class='modal-footer d-flex justify-content-center align-items-center'>
                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                    <button type='submit' class="btn submitAdminBtn" name="submit_user">
                        {{ trans('langInstallEnd') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class='modal fade' id='undoRegistration' tabindex='-1' aria-labelledby='undoRegistrationLabel' aria-hidden='true'>
    <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&session={{ $sessionID }}">
        <div class='modal-dialog modal-md'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <div class='modal-title'>
                        <div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>
                        <h3 class="modal-title-default text-center mb-0 mt-2" id="undoRegistrationLabel">{!! trans('langNoSubmitRegistration') !!}</h3>
                    </div>
                </div>
                <div class='modal-body text-center'>
                    <input type='hidden' name='deleteUserId' id='deleteUserId'>
                    <input type='hidden' name='token' value="{{ $_SESSION['csrf_token'] }}">
                    {!! trans('langCancelSessionRegistration') !!}
                </div>
                <div class='modal-footer d-flex justify-content-center align-items-center'>
                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                    <button type='submit' class="btn deleteAdminBtn" name="submit_user">
                        {{ trans('langNoSubmitRegistration') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection
