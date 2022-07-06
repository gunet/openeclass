@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebarAdmin')
                </div>
            </div>

            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-5">

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

                    @if($breadcrumbs && count($breadcrumbs)>2)
                    <div class='row p-2'></div>
                    <div class="float-start">
                        <p class='control-label-notes'>{!! $breadcrumbs[1]['bread_text'] !!}</p>
                        <small class='text-secondary'>{!! $breadcrumbs[count($breadcrumbs)-1]['bread_text'] !!}</small>
                    </div>
                    <div class='row p-2'></div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='form-wrapper shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
                        <form class='form-horizontal' role='form' method='post' name='makeadmin' action='{{ $_SERVER['SCRIPT_NAME']  }}'>
                        <fieldset>
                                <div class='form-group'>
                                    <label for='username' class='col-sm-6 control-label-notes'>{{ trans('langUsername') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' name='username' size='30' maxlength='30' placeholder='{{ trans('langUsername') }}'>
                                    </div>
                                </div>
                                <div class='row p-2'></div>
                                <div class='form-group'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langAddRole') }}</label>
                                        <div class='col-sm-12'>
                                            <div class='radio'>
                                                <input type='radio' name='adminrights' value='admin' checked>{{ trans('langAdministrator') }}
                                                <span class='help-block'>
                                                    <br><small class='text-warning'>{{ trans('langHelpAdministrator') }}</small>
                                                </span>
                                            </div>
                                            <div class='row p-2'></div>
                                            <div class='radio'>
                                                <input type='radio' name='adminrights' value='poweruser'>{{ trans('langPowerUser') }}
                                                <span class='help-block'>
                                                    <br><small class='text-warning'>{{ trans('langHelpPowerUser') }}&nbsp;</small>
                                                </span>
                                            </div>
                                            <div class='row p-2'></div>
                                            <div class='radio'>
                                                <input type='radio' name='adminrights' value='manageuser'>{{ trans('langManageUser') }}
                                                <span class='help-block'>
                                                    <br><small class='text-warning'>{{ trans('langHelpManageUser') }}</small>
                                                </span>
                                            </div>
                                            <div class='row p-2'></div>
                                            <div class='radio'>
                                                <input type='radio' name='adminrights' value='managedepartment'>{{ trans('langManageDepartment') }}
                                                <span class='help-block'>
                                                    <br><small class='text-warning'>{{ trans('langHelpManageDepartment') }}</small>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class='row p-2'></div>
                                {!! showSecondFactorChallenge() !!}
                                <div class='form-group'>
                                    <div class='col-sm-10 col-sm-offset-2'>
                                        <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langAdd') }}'>
                                    </div>
                                </div>       
                            </fieldset>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div></div>  

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <table class='announcements_table'>
                            <tr class='notes_thead'>
                                <th class='text-white center'>ID</th>
                                <th class='text-white'>{{ trans('langSurnameName') }}</th>
                                <th class='text-white'>{{ trans('langUsername') }}</th>
                                <th class='text-white text-center'>{{ trans('langRole') }}</th>
                                <th class='text-white text-center'>{!! icon('fa-gears') !!}</th>
                            </tr>
                            @foreach ($admins as $admin)
                                <tr>
                                    <td>{{ $admin->id }}</td>
                                    <td>{{ $admin->givenname }} {{ $admin->surname }}</td>
                                    <td>{{ $admin->username }}</td>
                                    <td>
                                    @if ($admin->privilege == 0)
                                        {{ trans('langAdministrator') }}
                                    @elseif ($admin->privilege == 1)
                                        {{ trans('langPowerUser') }}
                                    @elseif ($admin->privilege == 2)
                                        {{ trans('langManageUser') }}
                                    @elseif ($admin->privilege == 3)
                                        {{ trans('langManageDepartment') }}
                                    @endif
                                    </td>
                                    <td class='text-center'>
                                    @if ($admin->id != 1)
                                        {!! action_button([
                                                [
                                                    'title' => trans('langDelete'),
                                                    'url' => "$_SERVER[SCRIPT_NAME]?delete=1&amp;aid=" . getIndirectReference($admin->id),
                                                    'class' => 'delete',
                                                    'icon' => 'fa-times'
                                                ]
                                            ]) !!}
                                    @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection