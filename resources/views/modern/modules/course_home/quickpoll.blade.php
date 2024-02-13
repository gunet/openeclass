@push('head_styles')
    <style>
        .c3-tooltip {
            border: 1px solid #c3c3c3;
            padding: 5px 10px;
            display: block;
            background-color: #f1f1f1;
        }
        .c3-tooltip td.name {
            display: none;
        }
        .c3-tooltip td.value {
            text-align: center;
        }
    </style>
@endpush
@if ($theQuestion)
    <div class='card panelCard card-transparent border-0 mt-5 sticky-column-course-home'>
        <div class='card-header card-header-default px-0 py-0 border-0 d-flex justify-content-between align-items-center'>
            <h3 class='mb-0'>{{ trans('langQuickSurvey') }}</h3>
        </div>
        <div class='card-body card-body-default px-0'>
            <div class='d-flex justify-content-between align-items-start' role='alert' style='margin: 0'>
                <p>{!! standard_text_escape($theQuestion->question_text) !!}</p>
                @if ($show_results)
                    <script type = 'text/javascript'>
                        pollChartData = [];
                        $(document).ready(function(){
                            $('.showResults, .pollQuestionDiv').show();
                            $('.showPoll, .pollResultsDiv').hide();
                            $('.showResults').on('click', function() {
                                $(this).hide();
                                $('.pollQuestionDiv').hide();
                                $('.showPoll, .pollResultsDiv').show();
                            });
                            $('.showPoll').on('click', function() {
                                $(this).hide();
                                $('.pollResultsDiv').hide();
                                $('.showResults, .pollQuestionDiv').show();
                            });
                            draw_plots();
                        });

                        function draw_plots(){
                            var options = null;
                            for(var i=0;i<pollChartData.length;i++){

                                pollChartData[0]['answer'].unshift('x')
                                pollChartData[0]['count'].unshift('Votes')
                                let chartHeight = pollChartData[0]['count'].length * 32

                                options = {
                                    size: {
                                        height: chartHeight,
                                    },
                                    padding: {
                                        right: 25,
                                    },

                                    data: {
                                        x: 'x',
                                        columns: [
                                            pollChartData[0]['answer'],
                                            pollChartData[0]['count'],
                                        ],
                                        type: 'bar',
                                        labels: true,
                                        color: function(inColor, data) {
                                            var colors = ['#3498db', '#2ecc71', '#e74c3c', '#9b59b6', '#1abc9c', '#d35400', '#34495e','#f39c12', '#c0392b', '#27ae60'];
                                            if(data.index !== undefined) {
                                                return colors[data.index];
                                            }

                                            return inColor;
                                        },
                                    },
                                    legend:{show:false},
                                    bar: {
                                        width: 15,
                                        space: 2
                                    },
                                    axis: {
                                        rotated: true,
                                        x: {
                                            type: 'category'
                                        },
                                        y: {
                                            show: false,
                                            tick: {
                                                format: function (d) {
                                                    return (parseInt(d) == d) ? d : null;
                                                }
                                            }
                                        }
                                    },
                                    bindto: '#poll_chart'+i
                                };
                                c3.generate(options);
                            }
                        }
                        pollChartData.push({!! json_encode($this_chart_data) !!})
                    </script>

                    <div class='btn submitAdminBtn' tabindex='0'>
                        <i class='fa-solid fa-chart-bar showResults'></i> 
                        <i class='fa-regular fa-rectangle-list showPoll'></i>
                    </div>
            </div>

            <div class='panel-body pollResultsDiv bg-transparent'>
                <div id='poll_chart0'></div>
            </div>
            @endif



            @if ($uid && $has_participated > 0 && !$is_editor && !$multiple_submissions)
                <div class='panel-body p-0 mt-3 bg-transparent'>{{ trans('langPollAlreadyParticipated') }}</div>
            @else
                <div class='panel-body pollQuestionDiv p-0 mt-3 bg-transparent'>
                    <form id='homePollForm' class='form-horizontal' role='form' action='' id='poll' method='post'>
                        {!! $quick_poll_answers_content !!}
                        <input name='qtype' type='hidden' value='{{ $qtype }}'>
                        <input name='pid' type='hidden' value='{{ $pid }}'>
                        <input name='pqid' type='hidden' value='{{ $pqid }}'>
                        <input name='multiple_submissions' type='hidden' value='{{ $multiple_submissions }}'>
                        <input class='btn submitAdminBtn mt-3' name='submitPoll' type='submit' value='{{ trans('langSubmit') }}'>
                    </form>
                </div>
            @endif

        </div>
    </div>
    

@endif
