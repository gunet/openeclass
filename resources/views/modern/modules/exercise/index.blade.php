<?php //print_a($array_exercises_editor); ?>
<?php //print_a($array_all_exercises); ?>
<?php //print_a($array_exercises_student); ?>

@extends('layouts.default')

@section('content')

<div class="row back-navbar-eclass"></div>
<div class="row back-navbar-eclass2"></div>

    <script type="text/javascript" src="{{ $urlAppend }}js/my_courses_color_header.js"></script>
    <div class="pb-5">

        <div class="container-fluid main-container">

            <div class="row">

                <div id="background-cheat-leftnav" class="col-xl-2 col-lg-4 col-md-0 col-sm-6 col-xs-6 justify-content-center col_sidebar_active" >
                    @include('layouts.partials.sidebar',[$title_course,$title_course])
                </div>

                <div class="d-flex flex-column align-self-start col-xl-10 col-lg-8 col-md-12 col-sm-6 col-xs-6 justify-content-center col_maincontent_active">

                    <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

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
                                        <li class="breadcrumb-item active" aria-current="page">Ασκήσεις</li>
                                    </ol>
                                </nav>


                                @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                                <div class="row p-2"></div>
                                <small>Καθηγητής: {{$course_Teacher}}</small>
                                <div class="row p-2"></div>

                                @if($is_editor == 1)
                                    @if($pending_exercises && count($pending_exercises)>0)
                                        <ul style='margin-top: 10px;'>
                                            @foreach($pending_exercises as $row)
                                               <li>{{$row->title}}<a href='results.php?course={{$course_code}}&exerciseId={{$row->eid}}&status=2'>Εμφάνιση</a></li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    <div class="col-xxl-8 col-xl-8 col-lg-6 col-md-10 col-sm-12 col-12">
                                        <div class="btn-group" role="group" aria-label="Basic example">
                                            <a type="button" href="{!! $new_exercise_button !!}" class="btn btn-success"><i class="fas fa-plus"></i> Νέα άσκηση</a>
                                            <a type="button" href="{!! $question_categories_button !!}" class="btn btn-secondary"><i class="fas fa-question"></i> Κατηγορίες ερωτήσεων</a>
                                            <a type="button" href="{!! $question_bank_button !!}" class="btn btn-secondary"><i class="fas fa-university"></i> Τράπεζα ερωτήσεων</a>
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>
                                @endif

                                @if($nbrExercises)
                                    <div class='table-responsive glossary-categories' style="overflow: inherit">
                                        <table id='ex' class='table' style="overflow: inherit">
                                            <thead class="notes_thead text-light">
                                                @if($is_editor == 1)
                                                    <tr>
                                                        <th scope="col"><span class="announcement_th_comment">#</span></th>
                                                        <th scope="col"><span class="announcement_th_comment">Όνομα άσκησης</span></th>
                                                        <th scope="col"><span class="announcement_th_comment">Κατηγορίες ερωτήσεων</span></th>
                                                        <th scope="col"><span class="announcement_th_comment">Ρυθμίσεις άσκησεις</span></th>
                                                        <th scope="col"><span class="announcement_th_comment">Αποτελέσματα άσκησης</span></th>
                                                        <th scope="col"><span class="announcement_th_comment">Επιλογές</span></th>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <th scope="col"><span class="announcement_th_comment">#</span></th>
                                                        <th scope="col"><span class="announcement_th_comment">Όνομα άσκησης</span></th>
                                                        <th scope="col"><span class="announcement_th_comment">Ρυθμίσεις άσκησεις</span></th>
                                                        <th scope="col"><span class="announcement_th_comment">Αποτελέσματα άσκησης</span></th>
                                                    </tr>
                                                @endif
                                            </thead>
                                            <tbody>
                                                <?php $i=0;?>
                                                @foreach($result as $row)
                                                    <?php $i++; ?>
                                                    @if($is_editor == 1)
                                                        <tr>
                                                            <td>{{$i}}</td>
                                                            <td>
                                                                <a href='admin.php?course={{$course_code}}&amp;exerciseId={{$row->id}}&amp;preview=1'>{{$row->title}}</a><br>
                                                                <span>{!! $row->description !!}</span>
                                                            </td>
                                                            <td>
                                                                @if($array_exercises_editor[$row->id][1] != 1)
                                                                    <button type="button" class="btn btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="{!! $array_exercises_editor[$row->id][0] !!}">
                                                                        {!! $array_exercises_editor[$row->id][2] !!}
                                                                    </button>
                                                                @else
                                                                    <button type="button" class="btn btn-success" data-bs-toggle="tooltip" data-bs-placement="top" title="{!! $array_exercises_editor[$row->id][0] !!}">
                                                                        {!! $array_exercises_editor[$row->id][2] !!}
                                                                    </button>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <p class="text-left text-success">Έναρξη: {!! $array_exercises_editor[$row->id][3] !!}</p>
                                                                <p class="text-left text-danger">Ληξη: {!! $array_exercises_editor[$row->id][4] !!}</p>
                                                            </td>
                                                            <td>
                                                                @if($array_exercises_editor[$row->id][6]>0)
                                                                    <a href='results.php?course={{$course_code}}&amp;exerciseId={!! $array_exercises_editor[$row->id][5] !!}'>Εμφάνιση</a>
                                                                    &nbsp;&nbsp;<span><button class="btn btn-success" style="pointer-events:none;">{!! $array_exercises_editor[$row->id][7] !!}</button></span>
                                                                @else
                                                                    <p class="text-secondary">Δεν υπάρχουν αποτελέσματα</p>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="dropdown">
                                                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownExercise{{$row->id}}" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        <i class="fas fa-cog"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-exercise" aria-labelledby="dropdownExercise{{$row->id}}">
                                                                        <li class="exersice-li"><a class="exercise-item" href="admin.php?course={{$course_code}}&amp;exerciseId={{$row->id}}"><i class="fas fa-edit" style="color:white;"></i> Επεξεργασία</a></li>
                                                                        <?php $eye_or_not = $row->active ? "choice=disable" : "choice=enable" ?>
                                                                        <li class="exersice-li"><a class="exercise-item" href="{{$_SERVER[SCRIPT_NAME]}}?course={{$course_code}}&amp;{{$eye_or_not}}&amp;exerciseId={{$row->id}}"><i class="fas fa-eye"></i> Απόκρυψη</a></li>
                                                                        <li class="exersice-li"><a class="exercise-item" href="{{$urlAppend}}modules/exercise/exercise_stats.php?course={{$course_code}}&amp;exerciseId={{$row->id}}"><i class="fas fa-chart-bar"></i> Στατιστικά</a></li>
                                                                        <li class="exersice-li"><a class="exercise-item" href="#"><i class="fas fa-copy"></i> Δημιουργία αντιγράφου</a></li>
                                                                        <li class="exersice-li"><a class="exercise-item" href="{{$_SERVER[SCRIPT_NAME]}}?course={{$course_code}}&amp;choice=purge&amp;exerciseId={{$row->id}}"><i class="fas fa-eraser"></i> Εκκαθάριση αποτελεσμάτων</a></li>
                                                                        <li class="exersice-li"><a class="exercise-item" href="#"><i class="fas fa-trash" style="color:white;"></i> Διαγραφή</a></li>
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @else
                                                        <tr>
                                                            <td>{{$i}}</td>
                                                            <?php $currentDate = $array_exercises_student[$row->id][0]; ?>
                                                            <?php $temp_StartDate = $array_exercises_student[$row->id][1]; ?>
                                                            <?php $temp_EndDate = $array_exercises_student[$row->id][2]; ?>
                                                            <?php $paused_exercises = $array_exercises_student[$row->id][3]; ?>
                                                            <?php $incomplete_attempt = $array_exercises_student[$row->id][4];?>
                                                            @if(($currentDate->date >= $temp_StartDate->date) && (!isset($temp_EndDate) || isset($temp_EndDate) && ($currentDate->date) <= ($temp_EndDate->date)))
                                                                @if($incomplete_attempt)
                                                                    <td><a class='ex_settings active_exercise $link_class' href='exercise_submit.php?course={{$course_code}}&amp;exerciseId={{$row->id}}&amp;eurId={{$incomplete_attempt->eurid}}'>{{$row->title}}</a>
                                                                        &nbsp;&nbsp;<span style="color:#a9a9a9">{{ trans('langAttemptActive') }}</span>
                                                                @elseif($paused_exercises)
                                                                    <td><a class='ex_settings paused_exercise $link_class' href='exercise_submit.php?course={{$course_code}}&amp;exerciseId={{$row->id}}&amp;eurId={{$paused_exercises->eurid}}'>{{$row->title}}</a>
                                                                        &nbsp;&nbsp;<span style="color:#a9a9a9">{{ trans('langAttemptPausedS') }}</span>
                                                                @else
                                                                    <td><a class='ex_settings {{$array_exercises_student[$row->id][5]}}' href='exercise_submit.php?course={{$course_code}}&amp;exerciseId={{$row->id}}'>{{$row->title}}</a>{{$array_exercises_student[$row->id][6]}}
                                                                @endif
                                                            @elseif($currentDate->date <= $temp_StartDate->date)
                                                                <td class='not_visible'>{{$row->title}}{{$array_exercises_student[$row->id][7]}}&nbsp;&nbsp;
                                                            @else
                                                                <td>{{$row->title}}{{$array_exercises_student[$row->id][8]}}&nbsp;&nbsp;<span style="color:red">{{ trans('langHasExpiredS') }}</span>
                                                            @endif
                                                            {!! $row->description !!}</td>

                                                            <td>
                                                                <small>
                                                                    @if(isset($row->start_date))
                                                                        <div style='color:green;'>{{ trans('langStart') }}: {!! $array_exercises_student[$row->id][9] !!}</div>
                                                                    @endif
                                                                    @if(isset($row->end_date))
                                                                        <div style='color:red;'>{{ trans('langFinish') }}: {!! $array_exercises_student[$row->id][10] !!}</div>
                                                                    @endif
                                                                    @if($row->time_constraint > 0)
                                                                        <div>{{ trans('langDuration') }}: {{$row->time_constraint}} {{$langExerciseConstrainUnit}}</div>
                                                                    @endif
                                                                    @if($row->attempts_allowed > 0)
                                                                        <div>{{ trans('langAttempts') }}: {{$$array_exercises_student[$row->id][11]}}/{{$row->attempts_allowed}}</div>
                                                                    @endif
                                                                    @if($row->temp_save == 1)
                                                                        <div>{{ trans('langTemporarySave') }}: <span style='color:green;'>{{ trans('langYes') }}</span></div>
                                                                    @endif
                                                                </small>
                                                            </td>


                                                            <td>
                                                                @if($array_exercises_student[$row->id][12])
                                                                    @if($row->score)
                                                                        @if($array_exercises_student[$row->id][14] > 0)
                                                                            <a href='results.php?course={{$course_code}}&amp;exerciseId={{$array_exercises_student[$row->id][13]}}'>{{ trans('langViewShow') }}</a>
                                                                        @else
                                                                            &dash;
                                                                        @endif
                                                                    @else
                                                                        {{ trans('langNotAvailable') }}
                                                                    @endif
                                                                @else
                                                                    {{ trans('langNotAvailable') }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif


                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
