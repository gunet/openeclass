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

                    <nav class="navbar_breadcrumb" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <?php $size_breadcrumb = count($breadcrumbs); $count=0; ?>
                            <?php for($i=0; $i<$size_breadcrumb; $i++){ ?>
                                <li class="breadcrumb-item"><a href="{!! $breadcrumbs[$i]['bread_href'] !!}">{!! $breadcrumbs[$i]['bread_text'] !!}</a></li>
                            <?php } ?> 
                        </ol>
                    </nav>

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


                    @if ($user_requests)
                        <div class='table-responsive'>
                            <table id='requests_table' class='announcements_table'>
                                {!! table_header() !!}        
                                <tbody>
                                @foreach ($user_requests as $user_request)
                                    <tr>
                                        <td>{{ $user_request->givenname }} {{ $user_request->surname }}</td>
                                        <td>{{ $user_request->username }}</td>
                                        <td>{!! $tree->getFullPath($user_request->faculty_id) !!}</td>
                                        <td class='text-center'>
                                            <small>{{ nice_format(date('Y-m-d', strtotime($user_request->date_open))) }}</small>
                                        </td>
                                        <td class='option_btn_cell'>
                                        @if ($user_request->password == 'pop3')
                                            {!! action_button(array(
                                                array('title' => trans('langElaboration').' ('. trans('langViaPop').')',
                                                    'icon' => 'fa-edit',
                                                    'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=2")
                                            )) !!}
                                        @elseif ($user_request->password == 'imap')
                                            {!! action_button(array(
                                                array('title' => trans('langElaboration').' ('. trans('langViaImap').')',
                                                    'icon' => 'fa-edit',
                                                    'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=3")
                                            )) !!}
                                        @elseif ($user_request->password == 'ldap')
                                            {!! action_button(array(
                                                array('title' => trans('langElaboration').' ('. trans('langViaLdap').')',
                                                    'icon' => 'fa-edit',
                                                    'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=4")
                                            )) !!}
                                        @elseif ($user_request->password == 'db')
                                            {!! action_button(array(
                                                array('title' => trans('langElaboration').' ('. trans('langViaDB').')',
                                                    'icon' => 'fa-edit',
                                                    'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=5")
                                            )) !!}
                                        @elseif ($user_request->password == 'shibboleth')
                                            {!! action_button(array(
                                                array('title' => trans('langElaboration').' ('. trans('langViaShibboleth').')',
                                                    'icon' => 'fa-edit',
                                                    'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=6")
                                            )) !!}
                                        @elseif ($user_request->password == 'cas')
                                            {!! action_button(array(
                                                array('title' => trans('langElaboration').' ('. trans('langViaCAS').')',
                                                    'icon' => 'fa-edit',
                                                    'url' => "../auth/ldapnewprofadmin.php?id=$user_request->id&amp;auth=7")
                                            )) !!}
                                        @else
                                            {!! action_button(array(
                                                array('title' => trans('langElaboration'),
                                                    'icon' => 'fa-edit',
                                                    'url' => "newuseradmin.php?id=$user_request->id")
                                            )) !!}                       
                                        @endif
                                        </td>
                                    </tr>            
                                @endforeach               
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class='alert alert-warning'>{{ trans('langUserNoRequests') }}</div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection