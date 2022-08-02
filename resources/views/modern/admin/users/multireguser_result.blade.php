@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                        {!! Session::get('message') !!}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    @if (!empty($unparsed_lines))
                        <p>
                            <b>{{ trans('langErrors') }}</b>
                        </p>
                        <pre>{{ $unparsed_lines }}</pre>
                    @endif

                    <div class='table-responsive'>
                        <table class='announcements_table'>
                            <tr class='notes_thead'>
                                <th class='text-white'>{{ trans('langSurname') }}</th>
                                <th class='text-white'>{{ trans('langName') }}</th>
                                <th class='text-white'>e-mail</th>
                                <th class='text-white'>{{ trans('langPhone') }}</th>
                                <th class='text-white'>{{ trans('langAm') }}</th>
                                <th class='text-white'>username</th>
                                <th class='text-white'>password</th>
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