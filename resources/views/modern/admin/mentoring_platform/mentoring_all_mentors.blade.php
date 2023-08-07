{{--


@extends('layouts.default')

@push('head_styles')
    <link rel='stylesheet' type='text/css' href="{{ $urlServer }}/template/modern/css/mentoringCssAdmin.css" />
@endpush

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.partials.legend_view')

                    {!! isset($action_bar) ?  $action_bar : '' !!}

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
                    
                    @if(count($all_mentors) > 0)
                        <div class='col-12'>
                            <table class='table-default rounded-2' id="mentorsTable">
                                <thead class='list-header'>
                                    <tr>
                                        <th class='text-start'>{{ trans('langName') }}</th>
                                        <th class='text-start'>{{ trans('langDeleteTagsFromMentor') }}</th>
                                        <th class='text-center'>{{ trans('langAvailabilityMentor') }}</th>
                                    </tr>
                                </head>
                                <tbody>
                                    @foreach($all_mentors as $m)
                                        <tr>
                                            <td class='text-start'>
                                                <div class='d-flex justify-content-start align-items-center'>
                                                    @php $profile_img = profile_image($m->id, IMAGESIZE_SMALL, 'img-responsive rounded-2 img-profile me-2'); @endphp
                                                    {!! $profile_img !!}
                                                    {{ $m->givenname }}&nbsp{{ $m->surname }}
                                                </div>
                                            </td>
                                            <td class='text-start'>
                                                @php
                                                $tags = Database::get()->queryArray("SELECT id,name FROM mentoring_tag
                                                                                        WHERE id IN (SELECT tag_id FROM mentoring_mentor_tag 
                                                                                                        WHERE user_id = ?d)",$m->id);
                                                @endphp

                                                @if(count($tags) > 0)
                                                    <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}" class='mb-3'>
                                                        <div class='panel panel-admin rounded-2 BorderSolid bg-white mt-lg-3 mt-3 py-md-4 px-md-4 py-3 px-3 shadow-none'>
                                                            <div class='panel-heading bg-body p-0'>
                                                                <div class='col-12 Help-panel-heading'>
                                                                    <span class='panel-title text-uppercase Help-text-panel-heading'>{{ trans('langTags') }}</span>
                                                                </div>
                                                            </div>
                                                            <div class='panel-body p-0 rounded-0'>
                                                                <ul class='nav nav-pills'> 
                                                                    <input type='hidden' name='mentor_id' value='{{ $m->id }}'>
                                                                    @foreach($tags as $t)
                                                                       
                                                                        <li class='nav-item p-2'>                           
                                                                            <div class='d-flex justify-content-start align-items-center bagde bgEclass p-2 rounded-2'>
                                                                            
                                                                                <input type='checkbox' name='tags_ids[]' value='{{ $t->id }}' class='me-2'>
                                                                                <span class='blackBlueText small-text TextBold'>{{ $t->name }}</span>
                                                                            </div>

                                                                        </li>
                                                                    @endforeach
                                                                </ul> 
                                                            </div>
                                                            <div class='panel-footer mt-5'>
                                                                 <input type='submit' name='deleteTagFromMentor' class='btn btn-outline-danger btn-sm small-text rounded-2' value="{{ trans('langDelete')}}">
                                                            </div>
                                                        </div>
                                                       
                                                    </form>
                                                @endif
                                            </td>
                                            <td class='text-center'>
                                                @php 
                                                    $isAvailable = Database::get()->querySingle("SELECT COUNT(id) as c FROM mentoring_mentor_availability
                                                                                                    WHERE user_id = ?d
                                                                                                    AND start <= NOW() AND end >= NOW()",$m->id)->c;
                                                @endphp
                                                @if($isAvailable > 0)
                                                    <span class='badge bg-success rounded-circle'><i class='fa fa-check fs-5'></i></span>
                                                @else
                                                    <span class='badge bg-danger rounded-circle'><i class='fa fa-times fs-5'></i></span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready( function () {
   
        $('#mentorsTable').DataTable();

    } );
</script>
@endsection


--}}