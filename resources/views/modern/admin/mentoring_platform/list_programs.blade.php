
@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row m-auto">

                    @include('layouts.partials.legend_view')
                    
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
                    
                    
                    {!! $action_bar !!}
                    
                    @if(count($all_programs) > 0)
                        <div class='col-12'>
                            <div class='table-responsive mt-0'>
                                <table class='table-default'>
                                    <thead class='list-header'>
                                        <tr>
                                            <th>{{ trans('langProgram') }}</th>
                                            <th class='text-center'>{{ trans('langDelete') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($all_programs as $p)
                                            <tr>
                                                <td><a href='{{ $urlAppend }}mentoring_programs/{{ $p->code }}/index.php?goToMentoring=true'>{{ $p->title }}</a></td>
                                                <td class='text-center'>
                                                    <button class="btn btn-outline-danger btn-sm small-text ms-2 rounded-2"
                                                        data-bs-toggle="modal" data-bs-target="#DeleteProgramModal{{ $p->id }}" >
                                                        <span class='fa fa-trash'></span>
                                                    </button>

                                                    <div class="modal fade" id="DeleteProgramModal{{ $p->id }}" tabindex="-1" aria-labelledby="DeleteProgramModalLabel{{ $p->id }}" aria-hidden="true">
                                                        <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}">
                                                            <div class="modal-dialog modal-md modal-danger">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="DeleteProgramModalLabel{{ $p->id }}">
                                                                            {{ $p->title }}&nbsp -> &nbsp{{ trans('langDelete') }}
                                                                        </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body text-start">
                                                                        {!! trans('langDeleteProgramFromPlatform') !!}
                                                                        <input type='hidden' name='del_program_id' value='{{ $p->id }}'>
                                                                        <input type='hidden' name='del_program_code' value='{{ $p->code }}'>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                        <button type='submit' class="btn btn-danger small-text rounded-2" name="delete_program">
                                                                            {{ trans('langDelete') }}
                                                                        </button>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    @else
                        <div class='col-12'>
                            <div class='alert alert-info'>{{ trans('langNoInfoAvailable') }}</div>
                        </div>
                    @endif
                    
                  
                

        </div>
      
    </div>
</div>



@endsection