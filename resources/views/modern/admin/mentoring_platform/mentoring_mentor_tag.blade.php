
{{--



@extends('layouts.default')

@push('head_styles')
    <link rel='stylesheet' type='text/css' href="{{ $urlServer }}/template/modern/css/mentoringCssAdmin.css" />
@endpush

@section('content')

@if(get_config('mentoring_always_active') and get_config('mentoring_platform'))
<div class="pb-0 pt-0" >

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 d-flex justify-content-center align-items-start col_maincontent_active_mentoring ps-0 pe-0">

                <div class="row p-lg-5 p-md-5 ps-0 pe-0 pt-3 pb-3">

@else
<div class="pb-lg-3 pt-lg-3 pb-0 pt-0" >

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                    
                    <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">
@endif
                    @include('layouts.partials.legend_view')
                    
                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show rounded-0" role="alert">
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
                   
                    {!! $action_bar !!}

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                          <div class='col-12 h-100 left-form'></div>
                    </div>

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded p-3 bg-light'>
                            <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}">
                                <div class='form-group'>
                                    <label class='col-12 control-label-notes mb-2'>{{ trans('langOurMentors') }}</label>
                                    <label class='help-block'><span class='text-info fs-6'>(*)</span>&nbsp{{ trans('langShowAllMentors') }}</label>
                                    <div class='col-12'>
                                        @if(count($all_mentors) > 0)
                                            <select class='form-select rounded-2' name='mentor_id[]' id='select-mentors' multiple>
                                                {{-- <option value='0' selected>{{ trans('langWelcomeSelect')}}</option> --}}
                                                @foreach($all_mentors as $m)
                                                   <option value='{{ getIndirectReference($m->id) }}'>{{ $m->givenname }}&nbsp{{ $m->surname }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label class='col-12 control-label-notes mb-1'>{{ trans('langEmploymentSector') }}</label>
                                    <label class='help-block'><span class='text-info fs-6'>(*)</span>&nbsp{{ trans('langSearchTagByKeyboard') }}</label>
                                    <div class='col-12'>
                                        <select id='tag-mentor' class='form-select' name='tags[]' multiple></select>
                                    </div>
                                </div>

                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-center align-items-center'>
                                        <input type='submit' class='btn submitAdminBtn' name='submitTag' value='{{ trans('langSubmit')}}'>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    @if(count($list_tags) > 0)
                       <div class='col-12 mt-3'>
                            <div class='panel panel-admin rounded-2 BorderSolid bg-white mt-lg-3 mt-3 py-md-4 px-md-4 py-3 px-3 shadow-none'>
                                <div class='panel-heading bg-body p-0'>
                                    <div class='col-12 Help-panel-heading'>
                                        <span class='panel-title text-uppercase Help-text-panel-heading'>{{ trans('langTagList') }}&nbsp -- &nbsp{{ trans('langEmploymentSector')}}</span>
                                    </div>
                                </div>
                                <div class='panel-body p-0 rounded-0'>
                                    <ul class="nav nav-pills">
                                        @foreach($list_tags as $t)
                                            <li class="nav-item p-2">
                                                <div class="btn-group">
                                                    <a class="tagButton btn small-text text-white TextBold" aria-current="page"
                                                        data-bs-toggle="collapse" href="#multiCollapseTag{{ $t->id }}" role="button" 
                                                        aria-expanded="false" aria-controls="multiCollapseTag{{ $t->id }}">
                                                        {{ $t->name }}
                                                    </a>
                                                    <button class="btn bgEclass text-danger small-text" data-bs-toggle="modal" data-bs-target="#DeleteTag{{ $t->id }}" >
                                                        <span class='fa fa-trash'></span>
                                                    </button>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>

                                    <div class='col-12' id='showTagDetails'>
                                        @foreach($list_tags as $t)
                                            <div class='collapse multi-collapse mt-4' id="multiCollapseTag{{ $t->id }}" data-bs-parent="#showTagDetails">
                                                <div class='panel panel-admin rounded-2 BorderSolid bg-white mt-lg-3 mt-3 py-md-4 px-md-4 py-3 px-3 shadow-none'>
                                                    <div class='panel-heading bg-body p-0'>
                                                        <div class='col-12 Help-panel-heading'>
                                                            <span class='panel-title text-uppercase Help-text-panel-heading'>{{ $t->name}}</span>
                                                        </div>
                                                    </div>
                                                    <div class='panel-body p-0 rounded-0'>
                                                        @php 
                                                           $mentors = Database::get()->queryArray("SELECT *FROM user 
                                                                                                    WHERE id IN (SELECT user_id FROM mentoring_mentor_tag WHERE tag_id = ?d)",$t->id);
                                                        @endphp
                                                        @if(count($mentors) > 0)
                                                            <ul class="nav nav-pills">
                                                                @foreach($mentors as $m)
                                                                    @php $profile_img = profile_image($m->id, IMAGESIZE_SMALL, 'img-responsive img-circle img-profile'); @endphp
                                                                    <li class="nav-item m-2 badge bgEclass TextBold blackBlueText d-flex justify-content-center align-items-center rounded-2 pe-2">
                                                                        {!! $profile_img !!}<span class='ms-1 me-1'>{{ $m->givenname }}</span>{{ $m->surname }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            <div class='alert alert-warning rounded-2'>{{ trans('langNoExistsMentorInThisTag') }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal fade" id="DeleteTag{{ $t->id }}" tabindex="-1" aria-labelledby="LabelDeleteTag{{ $t->id }}" aria-hidden="true">
                                                <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}">
                                                    <div class="modal-dialog modal-md modal-danger">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="LabelDeleteTag{{ $t->id }}"><span class='help-block fs-6'>{{ $t->name }}</span></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                {!! trans('langDeleteTagMsg') !!}
                                                                <input type='hidden' name='tag_id' value="{{ $t->id }}">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                <button type='submit' class="btn btn-outline-danger small-text rounded-2" name="deleteTag">
                                                                    {{ trans('langDelete') }}
                                                                </button>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                </div>
                                
                            </div>
                       </div>
                    @endif
                  
                </div>

            </div>

        </div>
      
    </div>
</div>



@endsection


--}}