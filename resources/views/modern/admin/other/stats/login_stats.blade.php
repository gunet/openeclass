@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">

                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <!--C3 plot-->
                    <div class='row plotscontainer'>
                        <div id='userlogins_container' class='col-lg-12'>
                            {!! plot_placeholder("old_stats", trans('langLoginUser')) !!}
                        </div>
                    </div>


                    <div class='table-responsive'>
                        <table class='table-default'>
                            <tr>
                                <th class='list-header' colspan='2'>
                                    <strong>{{ trans('langLoginUser') }} {{ trans('langUsersOf') }}</strong>
                                </th>
                            </tr>
                            @foreach ($recent_logins as $data)
                                <tr>
                                    <td>{{ $data[0] }}</td>
                                    <td class='text-right col-sm-1'>{{ $data[1] }}</td>
                                </tr>
                            @endforeach
                            @foreach ($user_logins_data as $data)
                                @php
                                    $formatted_data = date_format(date_create($data[0]), "n / Y")
                                @endphp
                                <tr>
                                    <td>{{ $formatted_data }}</td>
                                    <td class='text-right'>{{ $data[1] }}</td>
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
