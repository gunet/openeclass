@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

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

                    @if (!empty($unparsed_lines))
                        <p>
                            <b>{{ trans('langErrors') }}</b>
                        </p>
                        <pre>{{ $unparsed_lines }}</pre>
                    @endif
                    <div class='col-sm-12'>
                        <div class='table-responsive'>
                            <table class='table-default'>
                                <tr class='list-header'>
                                    <th>{{ trans('langSurname') }}</th>
                                    <th>{{ trans('langName') }}</th>
                                    <th>e-mail</th>
                                    <th>{{ trans('langPhone') }}</th>
                                    <th>{{ trans('langAm') }}</th>
                                    <th>username</th>
                                    <th>password</th>
                                </tr>
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

</div>              
@endsection