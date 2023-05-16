@extends('layouts.default')

@section('content')


<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-3">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active">

                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
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
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif


                    <div class='col-sm-12'>
                        <div class='panel panel-default'>
                            <div class='panel-heading'>
                                <div class='panel-title text-start'>
                                    <span>
                                    {{ $request->title }}
                                    @if ($request->type_id)
                                        <small> -> {{ $type->name }}</small>
                                    @endif
                                    </span>
                                </div>
                            </div>

                            <div class='panel-body'>

                                <div class='row'>
                                    <div class='d-inline-flex align-items-center'>
                                        <span class='control-label-notes pe-2'>{{ trans('langNewBBBSessionStatus') }}:</span>
                                        {{ $state }}
                                    </div>
                                </div>
                                <div class='row mt-3'>
                                    <div class='d-inline-flex align-items-center'>
                                        <b class='control-label-notes pe-2'>{{ trans('langFrom') }}:</b>
                                        {!! display_user($request->creator_id) !!}
                                    </div>
                                </div>

                                @if ($watchers)
                                    <div class='row mt-3'>
                                        <div class='d-inline-flex align-items-center'>
                                            <b class='control-label-notes pe-2'>{{ trans('langWatchers') }}:</b>
                                            @foreach ($watchers as $user)
                                                {!! display_user($user) !!}
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if ($assigned)
                                    <div class='row mt-3'>
                                        <div class='d-inline-flex align-items-center'>
                                            <b class='control-label-notes pe-2'>{{ trans("m['WorkAssignTo']") }}:</b>
                                            @foreach ($assigned as $user)
                                                {!! display_user($user) !!}
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <hr>

                                @if ($field_data)
                                    @foreach ($field_data as $field)
                                        <div class='row'>
                                            <div class='col-12 col-sm-2 text-end'>
                                                <b>{{ getSerializedMessage($field->name) }}:</b>
                                            </div>
                                            <div class='col-12 col-sm-10'>
                                                @if (is_null($field->data) or $field->data === '')
                                                    <span class='not_visible'> - </span>
                                                @else
                                                    @if ($field->datatype == REQUEST_FIELD_DATE)
                                                        {{ format_locale_date(strtotime($field->data)) }}
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
                                        <div class='p-2'>{!! $request->description !!}</div>
                                    </div>
                                </div>

                            </div>

                            <div class='panel-footer'>
                                <div class='announcement-date text-center info-date'>
                                    {{ format_locale_date(strtotime($request->open_date)) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($can_modify or $can_assign_to_self)
                        <div class='col-sm-12 mt-3'>
                            <form role='form' method='post' action='{{ $targetUrl }}'>
                                <p>
                                {!! generate_csrf_token_form_field() !!}
                                @if ($can_assign_to_self)
                                    <button class='btn submitAdminBtn' type='submit' name='assignToSelf'>{{ trans('langTakeRequest') }}</button>
                                @endif
                                @if ($can_modify)
                                <div class='d-flex'>
                                    <button class='btn submitAdminBtn me-1' type='button' data-bs-toggle='modal' data-bs-target='#assigneesModal'>{{ trans("m['WorkAssignTo']") }}...</button>
                                    <button class='btn submitAdminBtn me-1' type='button' data-bs-toggle='modal' data-bs-target='#watchersModal'>{{ trans("langWatchers") }}...</button>
                                    <a class='btn submitAdminBtn' href='{{ $editUrl }}'>{{ trans("langElaboration") }}...</a>
                                </div>
                                @endif
                                </p>
                            </form>
                        </div>
                    @endif

                    @if ($can_comment)
                        <div class='col-sm-12'>
                            <div class='form-wrapper form-edit rounded'>
                                <form class='form-horizontal' role='form' method='post' action='{{ $targetUrl }}' enctype='multipart/form-data'>
                                    <fieldset>
                                        @if ($can_modify)
                                            <div class='form-group'>
                                                <label for='newState' class='col-sm-6 control-label-notes'>{{ trans('langChangeState') }}</label>
                                                <div class='col-sm-12'>
                                                    <select class='form-select' name='newState' id='newState'>
                                                        @foreach ($states as $stateId => $stateName)
                                                            <option value='{{ $stateId }}'@if ($stateId == $request->state) selected @endif>{{ $stateName }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endif



                                        <div class='form-group  @if($can_modify) mt-4 @endif'>
                                            <label for='requestComment' class='col-sm-6 control-label-notes'>{{ trans('langComment') }}</label>
                                            <div class='col-sm-12'>
                                                {!! $commentEditor !!}
                                            </div>
                                        </div>



                                        <div class='form-group mt-4'>
                                            <label for='requestFile' class='col-sm-6 control-label-notes'>{{ trans('langAttachedFile') }}</label>
                                            <div class='col-sm-12'>
                                                <input type='hidden' name='MAX_FILE_SIZE' value='{{ fileUploadMaxSize() }}'>
                                                <input type='file' name='requestFile'>
                                            </div>
                                        </div>



                                        <div class='form-group mt-5'>
                                            <div class='col-12 d-flex justify-content-center align-items-center'>
                                                
                                                   
                                                        <button class='btn submitAdminBtn' type='submit'>{{ trans('langSubmit') }}</button>
                                                   
                                                  
                                                        <a class='btn cancelAdminBtn ms-1' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a> 
                                                   
                                                
                                                
                                            </div>
                                        </div>
                                        {!! generate_csrf_token_form_field() !!}
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if ($comments)
                        @foreach ($comments as $comment)
                            <div class='col-sm-12 mt-3'>
                                <div class='panel panel-default'>
                                    <div class='panel-body'>
                                        
                                        <div class='d-inline-flex align-items-center'>
                                            <b class='control-label-notes pe-2'>{{ trans('langFrom') }}:</b>
                                            {!! display_user($comment->user_id) !!}
                                        </div><br>
                                        
                                        
                                        <div class='d-inline-flex align-items-center'>
                                            <b class='control-label-notes pe-2'>{{ trans('langDate') }}:</b>
                                            {{ format_locale_date(strtotime($comment->ts)) }}
                                        </div><br>
                                        
                                        @if ($comment->old_state != $comment->new_state)
                                            
                                            <div class='d-inline-flex align-items-center'>
                                                <b class='control-label-notes pe-2'>{{ trans('langChangeState') }}:</b>
                                                {{ $states[$comment->new_state] }}
                                                <span>({{ trans('langFrom') }}:</span> {{ $states[$comment->old_state] }})
                                            </div><br>
                                            
                                        @endif
                                        @if ($comment->real_filename)
                                            
                                            <div class='d-inline-flex align-items-center'>
                                                <b class='control-label-notes pe-2'>{{ trans('langAttachedFile') }}:</b>
                                                <a href='{{ commentFileLink($comment) }}'>{{ $comment->filename }}</a>
                                            </div><br>
                                            
                                        @endif
                                        @if ($comment->comment)
                                            
                                            <div class='d-inline-flex align-items-center'>
                                                <b class='control-label-notes pe-2'>{{ trans('langComment') }}:</b>
                                                {!! standard_text_escape($comment->comment) !!}
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
