
@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row m-auto">

                    <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                            <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                            <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">{{ trans('langMyPrograms') }}</a></li>
                            <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php">{!! show_mentoring_program_title($mentoring_program_code) !!}</a></li>
                            <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                        </ol>
                    </nav>

                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoUserProgramsText')!!}</p>
                        </div>
                    </div>
                    
                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php 
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp
                            
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    <div class='col-12'>{!! $action_bar !!}</div>
                   
                    <div class='col-12'>
                        <!-- accepted requests show -->
                                                       
                            <div class='panel panel-admin rounded-2 border-1 BorderSolid bg-white py-md-4 px-md-4 py-3 px-3 shadow-none'>
                                <div class='panel-heading bg-body p-0'>
                                    <h3>{{ trans('langRequestsHasAccepted') }}&nbsp--&nbsp{{ trans('langMembersProgram') }}</h3>
                                </div>
                                <div class="panel-body p-md-1 p-0 rounded-2">
                                    @if(count($users_program) > 0)
                                        <table class='table-default rounded-2' id="table_accepted_requests">
                                            <thead>
                                                <tr class='list-header'>
                                                    <th>{{ trans('langGuidedProgramParticipate') }}</th>
                                                    <th>{{ trans('langRemoveFromProgram') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($users_program as $ac)
                                                <tr>
                                                    <td>
                                                        @php 
                                                            $name = Database::get()->queryArray("SELECT givenname,surname,email,registered_at,description FROM user WHERE id = ?d", $ac->guided_id);
                                                        @endphp
                                                        <img class="mt-0" src="{{ user_icon($ac->guided_id, IMAGESIZE_SMALL) }}">
                                                        <span class='TextSemiBold'>
                                                            @foreach($name as $n)
                                                            <a class='TextSemiBold' href='{{ $urlAppend }}modules/mentoring/profile/user_profile.php?user_id={!! getInDirectReference($ac->guided_id) !!}'>{{ $n->givenname }}&nbsp{{ $n->surname }}</a>
                                                            @endforeach
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn deleteAdminBtn"
                                                            data-bs-toggle="modal" data-bs-target="#DeleteUserModal{{ $ac->guided_id }}" >
                                                            <span class='fa-solid fa-trash-can'></span>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <div class="modal fade" id="DeleteUserModal{{ $ac->guided_id }}" tabindex="-1" aria-labelledby="DeleteUserModalLabel{{ $ac->guided_id }}" aria-hidden="true">
                                                    <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}">
                                                        <div class="modal-dialog modal-md modal-danger">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="DeleteUserModalLabel{{ $ac->guided_id }}">{{ trans('langDelete') }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    {!! trans('langContinueActionRequest') !!}
                                                                    <input type='hidden' name='guided_id' value="{{ $ac->guided_id }}">
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                    <button type='submit' class="btn deleteAdminBtn" name="action_del_guided" value="delete">
                                                                        {{ trans('langDelete') }}
                                                                    </button>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class='col-12'>
                                            <p class='blackBlueText TextRegular mt-md-0 mt-3'>{{ trans('langNoUserList')}}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                       
                    </div>
                
        </div>
    </div>
</div>

<script>

    $('#table_accepted_requests').DataTable();
    $('#table_no_accepted_requests').DataTable();

    $('.showProgramsBtn').on('click',function(){
        localStorage.setItem("MenuMentoring","program");
    });
</script>

@endsection