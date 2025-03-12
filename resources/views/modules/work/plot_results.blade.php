
@extends('layouts.default')

@push('head_scripts')
    <script type="text/javascript">

        $(document).ready(function() {
            {
                var options = null;
                options = {
                    data: {
                        json: {!! $json_encoded_chart_data !!},
                        x: 'grade',
                        types:{
                            percentage: 'line'
                        },
                        axes: {percentage: 'y'},
                        names:{percentage:'%'},
                        colors:{percentage:'#e9d460'}
                    },
                    legend: {
                        show:false
                    },
                    bar: {
                        width: {
                            ratio:0.8
                        }
                    },
                    axis:{
                        x: {
                            type: 'category'
                        },
                        y: {
                            max: 100,
                            min: 0,
                            padding: {
                                top:0,
                                bottom:0
                            }
                        }
                    },
                    bindto: '#grades_chart'
                };
                c3.generate(options);
            }
        });

    </script>
@endpush

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                @include('layouts.partials.left_menu')

                <div class="col_maincontent_active">
                    <div class="row">
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view')

                        <div id='operations_container'>
                            {!! $action_bar !!}
                        </div>

                        @include('modules.work.assignment_details')

                        @if ($gradesExists)
                            <div class='row plotscontainer'>
                                <div class='col-lg-12 mt-4'>
                                    {!! plot_placeholder("grades_chart", trans('langGraphResults')) !!}
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

