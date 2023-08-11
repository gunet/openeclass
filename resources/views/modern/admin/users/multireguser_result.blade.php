@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row m-auto">

                    @if(!get_config('mentoring_always_active') and !get_config('mentoring_platform'))
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    @endif

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

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

                    @if (!empty($unparsed_lines))
                        <p>
                            <b>{{ trans('langErrors') }}</b>
                        </p>
                        <pre>{{ $unparsed_lines }}</pre>
                    @endif
                    <div class='col-sm-12'>
                        <div class='table-responsive'>
                            <table class='table-default'>
                                <thead>
                                <tr class='list-header'>
                                    <th>{{ trans('langSurname') }}</th>
                                    <th>{{ trans('langName') }}</th>
                                    <th>e-mail</th>
                                    <th>{{ trans('langPhone') }}</th>
                                    <th>{{ trans('langAm') }}</th>
                                    <th>username</th>
                                    <th>password</th>
                                </tr></thead>
                                @foreach ($new_users_info as $n)
                                    <tr>
                                        <td>{{ $n[1] }}</td>
                                        <td>{{ $n[2] }}</td>
                                        <td>{{ $n[3] }}</td>
                                        <td>{{ $n[4] }}</td>
                                        <td>{{ $n[5] }}</td>
                                        <td>{{ $n[6] }}</td>
                                        <td>{{ $n[7] }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                
        </div>
</div>
</div>              
@endsection