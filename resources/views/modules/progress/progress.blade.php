@extends('layouts.default')

@section('content')    
    <style>

            .badge-container
            {
                padding-bottom: 40px;
            }

            h4
            {
                font-family: Roboto;
            }
            a{
                color: inherit;
            }
            a:hover
            {
                text-decoration: none;
                color: inherit;
            }

            .certificate_panel
            {
                width: 348px; /* original was max-width: 300px; */
                height: 198px; /* original was height: 150px; */
                margin: 0 auto;
                padding: 20px;
                border-radius: 1px;
                background-color: #fafafa;
                transition: box-shadow 0.1s;
                box-shadow: 0px 0px 5px 2px #BBBBBB;
                border: 4px solid #FFFFFF;
                position: relative;
                font-family: Calibri;
                float: left;
                margin: 20px;
            }
            .certificate_panel:hover
            {
                box-shadow: 0px 0px 10px 6px #d9d9d9;
            }

            .certificate_panel_title
            {
                font-size: 20px;
                height:60%;
                font-weight: 600;
            }

            .certificate_panel_date
            {
                font-size: 17px;
            }

            .certificate_panel_viewdetails
            {
                font-size: 15px;
                position: absolute;
                bottom: 15px;
            }

            .certificate_panel_state
            {
                position: absolute;
                top:-20px;
                right:-20px;
                font-size:20px;
                padding: 10px;
                width: 40px; /* original was width: 20px; */
                height: 40px; /* original was height: 20px; */
                box-shadow: 0px 3px 4px 1px #999999;
                border-radius: 30px;
                background: #FFFFFF;
            }

            .certificate_panel_state .state_success
            {
                font-size: 2em;
                position: absolute;
                left: 3px;
                bottom: 0px;
                color: #11D888;
            }

            .certificate_panel_state .state_waiting
            {
                position: absolute;
                font-size: 1.1em;
                bottom: 7px;
                margin-left: 1px;
                color: #F73B51;
            }

            .certificate_panel_badge
            {
                position: absolute;
                bottom:0;
                right:0px
            }

            .certificate_panel_badge img
            {
                width: 120px;
                top: 30px;
                left: 10px;
                position: relative;
            }

            .certificate_panel_badge .badge_waiting
            {
                opacity: 0.1;
            }

            .certificate_panel_percentage
            {
                position: absolute;
                bottom:15px;
                right:15px;
                font-size:20px;
                padding: 10px 10px;
                width: 72px; /* original was width: 40px; */
                height: 72px; /* original was height: 40px;*/
                border: 6px solid #AAAAAA;
                border-radius: 40px;
                background: #FFFFFF;
                color: #AAAAAA;
                line-height: 38px;
                font-weight: 600;
                text-align: center;
            }
    </style>

    <div class="badge-container">
        <h3>{{  trans('langBadges') }}</h3>
        <hr>
        @if (count($game_badge) > 0)
            <div class="clearfix">
                @foreach ($game_badge as $key => $badge)
                    <?php
                        $formatted_date = claro_format_locale_date('%A, %d %B %Y', strtotime($badge->assigned));
                        $dateAssigned = ($badge->completed == 1) ? $formatted_date : '';
                    ?>
                    <a href="index.php?course={{$course_code}}&amp;badge_id={{$badge->badge}}&amp;u={{$badge->user}}">
                        <div class="certificate_panel">
                            <h4 class="certificate_panel_title">{{ $badge->title }}</h4>
                            <div class="certificate_panel_date">{{ $dateAssigned }}</div>
                            <div class="certificate_panel_viewdetails">
                                @if ($badge->completed == 1)
                                    <img src="{{ $badge_template_path }}{{ $badge->filename }}">
                                @endif
                            </div>

                            @if ($badge->completed == 1)
                                <div class="certificate_panel_state">
                                    <i class="fa fa-check-circle fa-inverse state_success"></i>
                                    {{-- <i class="fa fa-hourglass-2 state_waiting"></i> --}}
                                </div>
                                <div class="certificate_panel_badge">
                                    <img src="{{ $template_base }}/img/game/badge.png">
                                </div>
                            @else
                                <div class="certificate_panel_percentage">{{ round($badge->completed_criteria / $badge->total_criteria * 100, 0) }}%</div>
                            @endif
                        </div>
                    </a>
                    {{-- badge id: {{ $badge->badge }} <br/>
                    badge title: {{ $badge->title }} <br/>
                    badge description: {{ $badge->description }} <br/>
                    completed : {{ $badge->completed }} <br/>
                    completed criteria: {{ $badge->completed_criteria }} <br/>
                    total criteria: {{ $badge->total_criteria }} <br/>
                    percentage: {{ round($badge->completed_criteria / $badge->total_criteria * 100, 2) }}% <br/>
                    created: {{ $badge->created }} <br/>
                    updated: {{ $badge->updated }} <br/>
                    assigned: {{ $badge->assigned }} <br/>
                    <br/> <br/> --}}
                @endforeach
            </div>
        @endif
    </div>

    <div class="badge-container">
        <h3>{{  trans('langCertificates') }}</h3>
        <hr>
        @if (count($game_certificate) > 0)
            <div class="clearfix">
                @foreach ($game_certificate as $key => $certificate)
                    <?php
                    $formatted_date = claro_format_locale_date('%A, %d %B %Y', strtotime($certificate->assigned));
                    $dateAssigned = ($certificate->completed == 1) ? $formatted_date : '';
                    ?>
                    <a href="index.php?course={{$course_code}}&amp;certificate_id={{$certificate->certificate}}&amp;u={{$certificate->user}}">
                        <div class="certificate_panel">
                            <h4 class="certificate_panel_title">{{ $certificate->title }}</h4>
                            <div class="certificate_panel_date">{{ $dateAssigned }}</div>
                            <div class="certificate_panel_viewdetails">
                                @if ($certificate->completed == 1)
                                    &nbsp;&nbsp;<a href="index.php?course={{$course_code}}&amp;certificate_id={{$certificate->certificate}}&amp;u={{$certificate->user}}&amp;p=1">{{  trans('langPrintVers') }}</a>
                                @endif
                            </div>

                            @if ($certificate->completed == 1)
                                <div class="certificate_panel_state">
                                    <i class="fa fa-check-circle fa-inverse state_success"></i>
                                    {{-- <i class="fa fa-hourglass-2 state_waiting"></i> --}}
                                </div>
                                <div class="certificate_panel_badge">
                                    <img src="{{ $template_base }}/img/game/badge.png">
                                    {{-- <img class="badge_waiting" src="{{ $template_base }}/img/game/badge.png"> --}}
                                </div>
                            @else
                                <div class="certificate_panel_percentage">{{ round($certificate->completed_criteria / $certificate->total_criteria * 100, 0) }}%</div>
                            @endif
                        </div>
                    </a>
                    {{-- certificate id: {{ $certificate->certificate }} <br/>
                    certificate title: {{ $certificate->title }} <br/>
                    certificate description: {{ $certificate->description }} <br/>
                    completed : {{ $certificate->completed }} <br/>
                    completed criteria: {{ $certificate->completed_criteria }} <br/>
                    total criteria: {{ $certificate->total_criteria }} <br/>
                    percentage: {{ round($certificate->completed_criteria / $certificate->total_criteria * 100, 2) }}% <br/>
                    created: {{ $certificate->created }} <br/>
                    updated: {{ $certificate->updated }} <br/>
                    assigned: {{ $certificate->assigned }} <br/>
                    <br/> <br/> --}}
                @endforeach
            </div>

        @endif
    </div>

    @endsection