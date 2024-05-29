@extends('layouts.default')

@push('head_scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#start_session').datetimepicker({
                    format: 'dd-mm-yyyy hh:ii',
                    pickerPosition: 'bottom-right',
                    language: '{{ $language }}',
                    minuteStep: 5,
                    autoclose: true
                });

            $('#end_session').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '{{ $language }}',
                minuteStep: 5,
                autoclose: true
            });

            if ($('#one_session').is(':checked')){
                $('#select_one_session').removeClass('d-none');
                $('#select_one_session').addClass('d-block');
                $('#select_group_session').removeClass('d-block');
                $('#select_group_session').addClass('d-none');
            }

            if ($('#group_session').is(':checked')){
                $('#select_users_group_session').select2();
                $('#select_one_session').removeClass('d-block');
                $('#select_one_session').addClass('d-none');
                $('#select_group_session').removeClass('d-none');
                $('#select_group_session').addClass('d-block');
            }

            $('#one_session').on('change',function(){
                $('#select_one_session').removeClass('d-none');
                $('#select_one_session').addClass('d-block');
                $('#select_group_session').removeClass('d-block');
                $('#select_group_session').addClass('d-none');
            });
            
            $('#group_session').on('change',function(){
                $('#select_users_group_session').select2();
                $('#select_one_session').removeClass('d-block');
                $('#select_one_session').addClass('d-none');
                $('#select_group_session').removeClass('d-none');
                $('#select_group_session').addClass('d-block');
            });
        });
    </script>
@endpush


@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

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
                            
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit rounded'>
                                <form role='form' class='form-horizontal' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&session={{ $session_id }}" method='post'>
                                    <fieldset>

                                        <div class="form-group">
                                            <label for='creators' class='control-label-notes'>{{ trans('langCreator') }}&nbsp;<span class='Accent-200-cl'>(*)</span></label>
                                            <select class='form-select' name='creators' id='creators'>
                                                @if($is_tutor_course)
                                                    <option value=''>
                                                        {{ trans('langSelectConsultant') }}
                                                    </option>
                                                    @foreach($creators as $c)
                                                        <option value='{{ $c->user_id }}' {!! $c->user_id == $creator ? 'selected' : '' !!}>
                                                            {{ $c->givenname }}&nbsp;{{ $c->surname }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    @foreach($creators as $c)
                                                        <option value='{{ $c->id }}'>
                                                            {{ $c->givenname }}&nbsp;{{ $c->surname }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @if(Session::getError('creators'))
                                                <span class='help-block Accent-200-cl'>{!! Session::getError('creators') !!}</span>
                                            @endif
                                        </div>

                                        <div class="form-group mt-4">
                                            <label for='title' class='col-12 control-label-notes'>{{ trans('langTitle')}}&nbsp;<span class='Accent-200-cl'>(*)</span></label>
                                            <div class='col-12'>
                                                <input id='title' type='text' name='title' class='form-control' value='{{ $title }}'>
                                                @if(Session::getError('title'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('title') !!}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='comments' class='col-12 control-label-notes'>{{ trans('langComments')}}</label>
                                            {!! $comments !!}
                                        </div>

                                        <div class='form-group mt-4'>
                                            <div class="radio">
                                                <label>
                                                    <input type='radio' name='session_type' value='one' id='one_session' {!! $session_type=='one' ? 'checked' : '' !!}>
                                                    {{ trans('langIndividualSession') }}
                                                </label>
                                            </div>
                                            <div class="radio mt-2">
                                                <label>
                                                    <input type='radio' name='session_type' value='group' id='group_session' {!! $session_type=='group' ? 'checked' : '' !!}>
                                                    {{ trans('langGroupSession') }}
                                                </label>
                                            </div>

                                            <p class='control-label-notes mb-0 mt-3'>{{ trans('langSelectUser') }}&nbsp;<span class='Accent-200-cl'>(*)</span></p>
                                            <div id='select_one_session' class='d-block mt-1'>
                                                <select name='one_participant' class='form-select'>
                                                    <option value=''>{{ trans('langSelectUser') }}</option>
                                                    @foreach($simple_users as $u)
                                                        <option value='{{ $u->user_id }}' {!! in_array($u->user_id,$participants_arr) ? 'selected' : '' !!}>
                                                            {{ $u->givenname }}&nbsp;{{ $u->surname }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if(Session::getError('one_participant'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('one_participant') !!}</span>
                                                @endif
                                            </div>
                                            <div id='select_group_session' class='d-none mt-1'>
                                                <select id='select_users_group_session' name='many_participants[]' class='form-select' multiple>
                                                    @foreach($simple_users as $u)
                                                        <option value='{{ $u->user_id }}' {!! in_array($u->user_id,$participants_arr) ? 'selected' : '' !!}>
                                                            {{ $u->givenname }}&nbsp;{{ $u->surname }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if(Session::getError('many_participants'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('many_participants') !!}</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class='input-append date form-group mt-4'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langStart') }}&nbsp;<span class='Accent-200-cl'>(*)</span></label>
                                            <div class='col-sm-12'>
                                                <div class='input-group'>
                                                    <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>
                                                    <input class='form-control mt-0 border-start-0' id='start_session' name='start_session' type='text' value='{{ $start }}'>
                                                </div>
                                            </div>
                                            @if(Session::getError('start_session'))
                                                <span class='help-block Accent-200-cl'>{!! Session::getError('start_session') !!}</span>
                                            @endif
                                        </div>

                                        <div class='input-append date form-group mt-4'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langEnd') }}&nbsp;<span class='Accent-200-cl'>(*)</span></label>
                                            <div class='col-sm-12'>
                                                <div class='input-group'>
                                                    <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>
                                                    <input class='form-control mt-0 border-start-0' id='end_session' name='end_session' type='text' value='{{ $finish }}'>
                                                </div>
                                            </div>
                                            @if(Session::getError('end_session'))
                                                <span class='help-block Accent-200-cl'>{!! Session::getError('end_session') !!}</span>
                                            @endif
                                        </div>

                                        <div class='form-group mt-4'>
                                            <p class='control-label-notes mb-0 mt-3'>{{ trans('langTypeRemote') }}</p>
                                            <select class='form-select' name='type_remote'>
                                                <option value='0' {!! $type_remote==0 ? 'selected' : '' !!}>{{ trans('langNotRemote') }}</option>
                                                <option value='1' {!! $type_remote==1 ? 'selected' : '' !!}>{{ trans('langRemote') }}</option>
                                            </select>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <div class='checkbox'>
                                                <label class='label-container'>
                                                    <input type='checkbox' name='session_visible' {!! $visible==1 ? 'checked' : '' !!}>
                                                    <span class='checkmark'></span>
                                                    {{ trans('langVisible') }}
                                                </label>
                                            </div>
                                        </div>

                                        {!! generate_csrf_token_form_field() !!}    

                                        <div class='form-group mt-5'>
                                            <div class='col-12 d-flex justify-content-end aling-items-center'>
                                                <input class='btn submitAdminBtn' type='submit' name='modify' value='{{ trans('langModify') }}'>
                                            </div>
                                        </div>

                                    </fieldset>
                                </form>
                            </div>
                        </div>
                        <div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
