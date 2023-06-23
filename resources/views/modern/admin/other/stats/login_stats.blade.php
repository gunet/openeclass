@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">

                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <!--C3 plot-->
                    <div class='row plotscontainer ms-0'>
                        <div id='userlogins_container' class='col-lg-12 p-0'>
                            {!! plot_placeholder("old_stats", trans('langLoginUser')) !!}
                        </div>
                    </div>


                    <div class='col-12'>
                        <div class='table-responsive'>
                            <table class='table-default'>
                                <tr>
                                    <th class='list-header' colspan='2'>
                                        <strong class='text-white'>{{ trans('langLoginUser') }} {{ trans('langUsersOf') }}</strong>
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
