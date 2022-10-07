<?php //print_a($array_exercises_editor); ?>
<?php //print_a($array_all_exercises); ?>


@extends('layouts.default')

@section('content')

<div class="row back-navbar-eclass"></div>
<div class="row back-navbar-eclass2"></div>


    <div class="pb-5">

        <div class="container-fluid main-container">

            <div class="row">

                <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-6 col-xs-6 justify-content-center col_sidebar_active" >
                    @include('layouts.partials.sidebar',[$title_course,$title_course])
                </div>

                <div class="d-flex flex-column align-self-start col-xl-10 col-lg-9 col-md-12 col-sm-6 col-xs-6 justify-content-center col_maincontent_active">
                    
                    <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                                <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                                    <button type="button" id="menu-btn" class="btn btn-primary menu_btn_button">
                                        <i class="fas fa-align-left"></i>
                                        <span></span>
                                    </button>
                                </nav>

                                <nav class="navbar_breadcrumb" aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ $urlAppend }}main/portfolio.php">Χαρτοφυλάκιο</a></li>
                                        <li class="breadcrumb-item"><a href="{{ $urlAppend }}main/my_courses.php">Τα μαθήματά μου</a></li>
                                        <li class="breadcrumb-item"><a href="{{ $urlAppend }}main/info_mycourse.php?course={{$course_code}}&studentView=false">{{$title_course}}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Διαχείριση άσκησης</li>
                                    </ol>
                                </nav>


                                @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])
                                
                                <div class="row p-2"></div>
                                <small>Καθηγητής: {{$course_Teacher}}</small>
                                <div class="row p-2"></div>

                                {!! $action_bar !!}

                                <div class="row p-2"></div>

                                @if($is_editor == 1)
                                    <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                        <div class='row margin-bottom-fat form-wrapper' style='margin-top: 10px; margin-bottom: 30px; margin-left:0px; margin-right:0px; border:1px solid #cab4b4; border-radius:5px;'>
                                            <div class='col-sm-12'>
                                                <h4 style='font-weight: bold;'>{{$exerciseTitle}}</h4>
                                            </div>
                                            @if(!empty($exerciseDescription))
                                                <div class='col-sm-12'>
                                                    <em>{!! $mathfilter !!}</em>
                                                </div>
                                            @endif
                                            <div class='course-info-title clearfix'>
                                                <a role='button' data-bs-toggle='collapse' href='#MoreSettings' aria-expanded='false' aria-controls='MoreSettings'>
                                                    <h5 class='panel-heading'>
                                                        <span class='fa fa-chevron-down fa-fw'></span> {{$langGroupProperties}}
                                                    </h5>
                                                </a>
                                            </div>
                                            <div id='MoreSettings' class='collapse'>
                                                <span class='col-sm-12'>
                                                    @isset($exerciseStartDate)
                                                        <span style='color: green; padding-right: 30px;'>{{ trans('langStart') }}: 
                                                            <em>{{$exerciseStartDate}}</em>
                                                        </span>
                                                    @endisset
                                                    @isset($exerciseEndDate)
                                                        @if(!empty($exerciseEndDate))
                                                                <span style="color: red;">{{ trans('langFinish') }}: 
                                                                    <em>{{$exerciseEndDate}}</em>
                                                                </span>
                                                        @endif
                                                    @endisset
                                                </span>

                                                <div class="row p-2"></div>

                                                <span class='col-sm-12'>{{ trans('langViewShow') }}: 
                                                    <em><strong>{{$exerciseType}}</strong></em>
                                                </span>

                                                <div class="row p-2"></div>


                                                <span class='col-sm-12' style='margin-top: 15px;'>
                                                    @if($exerciseTempSave == 1)
                                                        <span style='padding-right: 30px'>{{ trans('langTemporarySave')}}: 
                                                            <em><strong>{{ trans('langYes') }}</strong></em>
                                                        </span>
                                                    @endif
                                                    @if($exerciseTimeConstraint > 0)
                                                        <span style='padding-right: 30px'>{{ trans('langDuration') }}: 
                                                            <em><strong>{{$exerciseTimeConstraint}}</strong> {{$langExerciseConstrainUnit}}</em>
                                                        </span>
                                                    @endif
                                                    @if($exerciseAttemptsAllowed > 0)
                                                        {{ trans('langExerciseAttemptsAllowed') }}: <em><strong>{{$exerciseAttemptsAllowed}}</strong> {{$langExerciseAttemptsAllowedUnit}}</em>
                                                    @endif
                                                </span>

                                                <div class="row p-2"></div>


                                                <span class='col-sm-12' style='margin-top: 15px;'>{{ trans('langAnswers') }}: <em><strong>{{$disp_results_message}}</strong></em></span>

                                                <div class="row p-2"></div>


                                                <span class='col-sm-12'>{{ trans('langScore') }}: <em><strong>{{$disp_score_message}}</strong></em></span>

                                                <div class="row p-2"></div>
                                                @if($exerciseAssignToSpecific > 0)
                                                    <span class='col-sm-12' style='margin-top: 15px;'>{{$WorkAssignTo}}: <strong>{{$assign_to_users_message}}</strong></span>
                                                @endif
                                                @if($tags_list)
                                                    <span class='col-sm-3'>
                                                        <strong>{{ trans('langTags') }}:</strong>
                                                    </span>
                                                    <span class='col-sm-9'>
                                                            {!! $tags_list !!}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>


                                        <div class="row p-2"></div>


                                    </div>

                                    <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                        <div class="btn-group" role="group" aria-label="Basic example">
                                            <a type="button" class="btn btn-success" href="{{$urlAppend}}modules/exercise/admin.php?course={{$course_code}}&exerciseId={{$exId}}&newQuestion=yes"><i class="fas fa-plus"></i> Νέα ερώτηση</a>
                                            <a type="button" class="btn btn-secondary">Δυναμική επιλογή ερωτήσεων</a>
                                            <a type="button" class="btn btn-secondary"><i class="fas fa-university"></i> Εισαγωγή από τράπεζα ερωτήσεων</a>
                                            <a type="button" class="btn btn-secondary"><i class="fas fa-university"></i> Εισαγωγή με κρητίρια από τράπεζα ερωτήσεων</a>
                                        </div>
                                    </div>
                                    
                                @endif

                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection