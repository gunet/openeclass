@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebarAdmin')
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>
                        
                    
                        <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                        @include('layouts.partials.sidebarAdmin')
                        </div>
                    </div>

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='table-responsive'>
                        <table class='announcements_table'>
                            <tr>
                                <td>
                                    <a href='../usage/displaylog.php?from_other=TRUE'>{{ trans('langSystemActions') }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=failurelogin'>{{ trans('langLoginFailures') }}</a>
                                    <small> ({{ trans('langLast15Days') }})</small>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=musers'>{{ trans('langMultipleUsers') }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=memail'>{{ trans('langMultipleAddr') }} e-mail</a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=mlogins'>{{ trans('langMultiplePairs') }} LOGIN - PASS</a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=vmusers'>{{ trans('langMailVerification') }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?stats=unregusers'>{{ trans('langUnregUsers') }}</a>
                                    <small> ({{ trans('langLastMonth') }})</small>
                                </td>
                            </tr>
                        </table>            
                    </div>


                    @if (isset($_GET['stats']))
                        @if (in_array($_GET['stats'], ['failurelogin', 'unregusers']))
                                {!! $extra_info !!}
                        @elseif ($_GET['stats'] == 'musers')
                            <div class='table-responsive'>
                                <table class='announcements_table'>
                                    <tr class='notes_thead'>
                                        <th>
                                            <b class='text-white'>{{ trans('langMultipleUsers') }}</b>
                                        </th>
                                        <th class='right'>
                                            <strong class='text-white'>{{ trans('langResult') }}</strong>
                                        </th>
                                    </tr>
                                    @if (count($loginDouble) > 0)
                                        {!! tablize($loginDouble) !!}
                                        <tr>
                                            <td class='right' colspan='2'>
                                                <b>
                                                    <span style='color: #FF0000'>{{ trans('langExist') }} <?php print_r(count($loginDouble));?></span>
                                                </b>
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class='right' colspan='2'>
                                                <div class='text-center not_visible'> - {{ trans('langNotExist') }} - </div>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        @elseif ($_GET['stats'] == 'memail')
                            <div class='table-responsive'>
                                <table class='announcements_table'>
                                    <tr class='notes_thead'>
                                        <th><b class='text-white'>{{ trans('langMultipleAddr') }} e-mail</b></th>
                                        <th class='right'>
                                            <strong class='text-white'>{{ trans('langResult') }}</strong>
                                        </th>
                                    </tr>
                                    @if (count($loginDouble) > 0)
                                        {!! tablize($loginDouble) !!}
                                        <tr>
                                            <td class=right colspan='2'>
                                                <b>
                                                    <span style='color: #FF0000'>{{ trans('langExist') }} <?php print_r(count($loginDouble));?></span>
                                                </b>
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class=right colspan='2'>
                                                <div class='text-center not_visible'> - {{ trans('langNotExist') }} - </div>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        @elseif ($_GET['stats'] == 'mlogins')
                            <div class='table-responsive'>
                                <table class='announcements_table'>
                                    <tr class='notes_thead'>
                                        <th>
                                            <b class='text-white'>{{ trans('langMultiplePairs') }} LOGIN - PASS</b>
                                        </th>
                                        <th class='right'>
                                            <b class='text-white'>{{ trans('langResult') }}</b>
                                        </th>
                                    </tr>
                                    @if (count($loginDouble) > 0)
                                        {!! tablize($loginDouble) !!}
                                        <tr>
                                            <td class='right' colspan='2'>
                                                <b>
                                                    <span style='color: #FF0000'>{{ trans('langExist') }} <?php print_r(count($loginDouble));?></span>
                                                </b>
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class='right' colspan='2'>
                                                <div class='text-center not_visible'> - {{ trans('langNotExist') }} - </div>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        @elseif  ($_GET['stats'] == 'vmusers')
                            
                                <div class='col-sm-12 mt-5'>
                                    <div class='shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
                                        <div class='control-label-notes pb-3'>
                                            {{ trans('langUsers') }}
                                        </div>
                                        <ul class='list-group'>
                                            <li class='list-group-item'>
                                                <label>
                                                    <a href='listusers.php?search=yes&verified_mail=1'>{{ trans('langMailVerificationYes') }}</a>
                                                </label>          
                                                <span class='badge'>{{ $verifiedEmailUserCnt }}</span>
                                            </li>
                                            <li class='list-group-item'>
                                                <label>
                                                    <a href='listusers.php?search=yes&verified_mail=2'>{{ trans('langMailVerificationNo') }}</a>
                                                </label>                            
                                                <span class='badge'>{{ $unverifiedEmailUserCnt }}</span>
                                            </li>
                                            <li class='list-group-item'>
                                                <label>
                                                    <a href='listusers.php?search=yes&verified_mail=0'>{{ trans('langMailVerificationPending') }}</a>
                                                </label>
                                                <span class='badge'>{{ $verificationRequiredEmailUserCnt }}</span>
                                            </li>
                                            <li class='list-group-item'>
                                                <label>
                                                    <a href='listusers.php?search=yes'>{{ trans('langTotal') }}</a>
                                                </label>
                                                <span class='badge text-secondary'>{{ $totalUserCnt }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                               
                        @endif
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>


    
@endsection