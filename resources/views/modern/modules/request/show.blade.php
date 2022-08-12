@extends('layouts.default')

@section('content')


<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">


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
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])
                    


                    {!! $action_bar !!}

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

               
                            
                    <div class='panel panel-default mt-3'>
                        <div class='panel-heading notes_thead'>
                            <div class='row'>
                                <div class='col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-6 col-6'>
                                    <h4 class='text-white text-start ps-3 pt-1'>
                                        <span>
                                        {{ $request->title }}
                                        @if ($request->type_id)
                                            <small> -> {{ $type->name }}</small>
                                        @endif
                                        </span>
                                    </h4>
                                </div>
                                <div class='col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-6 col-6'>
                                    <div class='announcement-date text-end text-white pt-2 pe-3'>{{
                                        claro_format_locale_date(trans('dateFormatLong') . ' ' . trans('timeNoSecFormat'),
                                                                strtotime($request->open_date)) }}
                                    </div>
                                </div>
                            </div>
                           
                        </div>
                        
                        <div class='panel-body panel-body-request'>
   
                            <div class='row'>
                                <div class='col-12 col-sm-2 text-right'>
                                    <b class='control-label-notes ps-3'>{{ trans('langNewBBBSessionStatus') }}:</b>
                                </div>
                                <div class='col-12 col-sm-4'>
                                    {{ $state }}
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-12 col-sm-2 text-right'>
                                    <b class='control-label-notes ps-3'>{{ trans('langFrom') }}:</b>
                                </div>
                                <div class='col-12 col-sm-4'>
                                    {!! display_user($request->creator_id) !!}
                                </div>
                                @if ($watchers)
                                    <div class='col-12 col-sm-2 text-right'>
                                        <b class='control-label-notes ps-3'>{{ trans('langWatchers') }}:</b>
                                    </div>
                                    <div class='col-12 col-sm-4'>
                                        @foreach ($watchers as $user)
                                            {!! display_user($user) !!}
                                        @endforeach
                                    </div>
                                @endif
                                @if ($assigned)
                                    <div class='col-12 col-sm-2 text-right'>
                                        <b class='control-label-notes ps-3'>{{ trans("m['WorkAssignTo']") }}:</b>
                                    </div>
                                    <div class='col-12 col-sm-4'>
                                        @foreach ($assigned as $user)
                                            {!! display_user($user) !!}
                                        @endforeach
                                    </div>
                                @endif
                                </div>
                            <hr>
                            @if ($field_data)
                                @foreach ($field_data as $field)
                                    <div class='row'>
                                        <div class='col-12 col-sm-2 text-right'>
                                            <b>{{ getSerializedMessage($field->name) }}:</b>
                                        </div>
                                        <div class='col-12 col-sm-10'>
                                            @if (is_null($field->data) or $field->data === '')
                                                <span class='not_visible'> - </span>
                                            @else
                                                @if ($field->datatype == REQUEST_FIELD_DATE)
                                                    {{ claro_format_locale_date('%A, %d-%m-%Y', strtotime($field->data)) }}
                                                @else
                                                    {{ $field->data }}
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                                <hr>
                            @endif
                            <div class='row'>
                                <div class='col-12'>
                                    <div class='ps-3'>{!! $request->description !!}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class='row p-2'></div>
                            

                    @if ($can_modify or $can_assign_to_self)
                        <div class='col-md-12'>
                            <form role='form' method='post' action='{{ $targetUrl }}'>
                                <p>
                                {!! generate_csrf_token_form_field() !!}
                                @if ($can_assign_to_self)
                                    <button class='btn btn-secondary' type='submit' name='assignToSelf'>{{ trans('langTakeRequest') }}</button>
                                @endif
                                @if ($can_modify)
                                    <button class='btn btn-secondary' type='button' data-bs-toggle='modal' data-bs-target='#assigneesModal'>{{ trans("m['WorkAssignTo']") }}...</button>
                                    <button class='btn btn-secondary' type='button' data-bs-toggle='modal' data-bs-target='#watchersModal'>{{ trans("langWatchers") }}...</button>
                                    <a class='btn btn-secondary' href='{{ $editUrl }}'>{{ trans("langElaboration") }}...</a>
                                @endif
                                </p>
                            </form>
                        </div>
                    @endif

                    @if ($can_comment)
                        <div class='col-md-12'>
                            <form class='form-horizontal' role='form' method='post' action='{{ $targetUrl }}' enctype='multipart/form-data'>
                                <fieldset>
                                    @if ($can_modify)
                                        <div class='form-group mt-3'>
                                            <label for='newState' class='col-sm-6 control-label-notes'>{{ trans('langChangeState') }}:</label>
                                            <div class='col-sm-12'>
                                                <select class='form-select' name='newState' id='newState'>
                                                    @foreach ($states as $stateId => $stateName)
                                                        <option value='{{ $stateId }}'@if ($stateId == $request->state) selected @endif>{{ $stateName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                    

                                    <div class='form-group mt-3'>
                                        <label for='requestComment' class='col-sm-6 control-label-notes'>{{ trans('langComment') }}:</label>
                                        <div class='col-sm-12'>
                                            {!! $commentEditor !!}
                                        </div>
                                    </div>

                                    

                                    <div class='form-group mt-3'>
                                        <label for='requestFile' class='col-sm-6 control-label-notes'>{{ trans('langAttachedFile') }}:</label>
                                        <div class='col-sm-12'>
                                            <input type='hidden' name='MAX_FILE_SIZE' value='{{ fileUploadMaxSize() }}'>
                                            <input type='file' name='requestFile'>
                                        </div>
                                    </div>

                                    

                                    <div class='form-group mt-3'>
                                        <div class='col-xs-offset-2 col-xs-10'>
                                            <button class='btn btn-primary' type='submit'>{{ trans('langSubmit') }}</button>
                                            <a class='btn btn-secondary' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                                        </div>
                                    </div>
                                    {!! generate_csrf_token_form_field() !!}
                                </fieldset>
                            </form>
                        </div>
                    @endif

                    @if ($comments)
                        @foreach ($comments as $comment)
                            <div class='col-md-12'>
                                <div class='panel panel-default'>
                                    <div class='panel-body panel-body-request'>
                                        <div class='row p-2'>
                                            <div class='col-12 col-sm-3'>
                                                <b class='control-label-notes ps-3'>{{ trans('langFrom') }}:</b>
                                            </div>
                                            <div class='col-12 col-sm-9'>
                                                {!! display_user($comment->user_id) !!}
                                            </div>
                                        </div>
                                        <div class='row p-2'>
                                            <div class='col-12 col-sm-3'>
                                                <b class='control-label-notes ps-3'>{{ trans('langDate') }}:</b>
                                            </div>
                                            <div class='col-12 col-sm-9'>
                                                {{ claro_format_locale_date(trans('dateFormatLong') . ' ' . trans('timeNoSecFormat'),
                                                                            strtotime($comment->ts)) }}
                                            </div>
                                        </div>
                                        @if ($comment->old_state != $comment->new_state)
                                            <div class='row p-2'>
                                                <div class='col-12 col-sm-3'>
                                                    <b class='control-label-notes ps-3'>{{ trans('langChangeState') }}:</b>
                                                </div>
                                                <div class='col-12 col-sm-9'>
                                                    <b>{{ $states[$comment->new_state] }}</b> ({{ trans('langFrom') }}: {{ $states[$comment->old_state] }})
                                                </div>
                                            </div>
                                        @endif
                                        @if ($comment->real_filename)
                                            <div class='row p-2'>
                                                <div class='col-12 col-sm-3'>
                                                    <b class='control-label-notes ps-3'>{{ trans('langAttachedFile') }}:</b>
                                                </div>
                                                <div class='col-12 col-sm-9'>
                                                    <a href='{{ commentFileLink($comment) }}'>{{ $comment->filename }}</a>
                                                </div>
                                            </div>
                                        @endif
                                        @if ($comment->comment)
                                            <div class='row p-2'>
                                                <div class='col-12 col-sm-3'>
                                                    <b class='control-label-notes ps-3'>{{ trans('langComment') }}:</b>
                                                </div>
                                                <div class='col-12 col-sm-9'>
                                                    {!! standard_text_escape($comment->comment) !!}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                        

                    @if ($can_modify)
                        @include('modules.request.modals')
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
