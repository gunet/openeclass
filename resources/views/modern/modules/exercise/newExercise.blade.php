<?php //print_a($array_exercises_editor); ?>
<?php //print_a($array_all_exercises); 
print_r($exerciseAssignToSpecific);?>


@extends('layouts.default')

@section('content')


<script src ="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>  

<script type='text/javascript'>
        $(function() {
            $('#exerciseStartDate, #exerciseEndDate').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true
            }).on('changeDate', function(ev){
                if($(this).attr('id') === 'exerciseEndDate') {
                    $('#answersDispEndDate, #scoreDispEndDate').removeClass('hidden');
                }
            }).on('blur', function(ev){
                if($(this).attr('id') === 'exerciseEndDate') {
                    var end_date = $(this).val();
                    if (end_date === '') {
                        if ($('input[name=\"dispresults\"]:checked').val() == 4) {
                            $('input[name=\"dispresults\"][value=\"1\"]').prop('checked', true);
                        }
                        $('#answersDispEndDate, #scoreDispEndDate').addClass('hidden');
                    }
                }
            });
            $('#enableEndDate, #enableStartDate').change(function() {
                var dateType = $(this).prop('id').replace('enable', '');
                if($(this).prop('checked')) {
                    $('input#exercise'+dateType).prop('disabled', false);
                    if (dateType === 'EndDate' && $('input#exerciseEndDate').val() !== '') {
                        $('#answersDispEndDate, #scoreDispEndDate').removeClass('hidden');
                    }
                } else {
                    $('input#exercise'+dateType).prop('disabled', true);
                    if ($('input[name=\"dispresults\"]:checked').val() == 4) {
                        $('input[name=\"dispresults\"][value=\"1\"]').prop('checked', true);
                    }
                    $('#answersDispEndDate, #scoreDispEndDate').addClass('hidden');
                }
            });                                    
            $('#exerciseAttemptsAllowed').blur(function(){
                var attempts = $(this).val();
                if (attempts ==0) {
                    $('#answersDispLastAttempt, #scoreDispLastAttempt').addClass('hidden');
                    if ($('input[name=\"dispresults\"]:checked').val() == 3) {
                        $('input[name=\"dispresults\"][value=\"1\"]').prop('checked', true);
                    }
                } else {
                    $('#answersDispLastAttempt, #scoreDispLastAttempt').removeClass('hidden');
                }
            });
            $('#exerciseIPLock').select2({
                minimumResultsForSearch: Infinity,
                tags: true,
                tokenSeparators: [',', ' ']
            });
            $('#assign_button_all').click(hideAssignees);
            $('#assign_button_user, #assign_button_group').click(ajaxAssignees);
            $('#continueAttempt').change(function () {
                if ($(this).prop('checked')) {
                    $('#continueTimeField').show('fast');
                } else {
                    $('#continueTimeField').hide('fast');
                }
            }).change();
        });
        function ajaxAssignees()
        {
            $('#assignees_tbl').removeClass('hide');
            var type = $(this).val();
            $.post('',
            {
              assign_type: type
            },
            function(data,status){
                var index;
                var parsed_data = JSON.parse(data);
                var select_content = '';
                if(type==1){
                    for (index = 0; index < parsed_data.length; ++index) {
                        select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + parsed_data[index]['surname'] + ' ' + parsed_data[index]['givenname'] + '<\/option>';
                    }
                } else {
                    for (index = 0; index < parsed_data.length; ++index) {
                        select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + parsed_data[index]['name'] + '<\/option>';
                    }
                }
                $('#assignee_box').find('option').remove();
                $('#assign_box').find('option').remove().end().append(select_content);
            });
        }
        function hideAssignees()
        {
            $('#assignees_tbl').addClass('hide');
            $('#assignee_box').find('option').remove();
        }
    </script>


<div class="row back-navbar-eclass"></div>
<div class="row back-navbar-eclass2"></div>


    <div class="pb-5">

        <div class="container-fluid main-container">

            <div class="row">

                <div class="col-xl-2 col-lg-4 col-md-0 col-sm-6 col-xs-6 justify-content-center col_sidebar_active" >
                    @include('layouts.partials.sidebar',[$title_course,$title_course])
                </div>

                <div class="d-flex flex-column align-self-start col-xl-10 col-lg-8 col-md-12 col-sm-6 col-xs-6 justify-content-center col_maincontent_active">
                    
                    <div class="row p-5">

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
                                        <li class="breadcrumb-item active" aria-current="page">Νέα άσκηση</li>
                                    </ol>
                                </nav>


                                <div class="col-xxl-12 col-lx-12 col-lg-12 col-md-10 col-sm-6">
                                    <legend class="float-none w-auto py-2 px-4 notes-legend"><span class="pos_TitleCourse"><i class="fas fa-list" aria-hidden="true"></i> Ασκήσεις του μαθήματος <<strong>{{$title_course}} <small>({{$course_code_title}})</small></strong>></span>
                                        <div class="manage-course-tools"style="float:right">
                                            @if($is_editor == 1)
                                                @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code_title])
                                            @endif
                                        </div>
                                    </legend>
                                </div>
                                <div class="row p-2"></div>
                                <small>Καθηγητής: {{$course_Teacher}}</small>
                                <div class="row p-2"></div>

                                @if($is_editor == 1)

                                        <?php $checking; ?>
                                        <form class='form-horizontal' role='form' method='post' action='{{$urlAppend}}modules/exercise/admin.php?course={{$course_code}}&NewExercise=Yes'>
                                            

                                                <div class='form-group'>
                                                    <label for='exerciseTitle' class='col-sm-4 control-label-notes'>{{ trans('langName') }}:</label>
                                                    <div class='col-sm-12'>
                                                        <input name='exerciseTitle' type='text' class='form-control' id='exerciseTitle' value='{{$exerciseTitle}}' placeholder='Ονομα άσκησης'>
                                                    </div>
                                                </div>

                                                <div class="row p-2"></div>


                                                <div class='form-group'>
                                                    <label for='exerciseDescription' class='col-sm-4 control-label-notes'>{{ trans('langDescription') }}:</label>
                                                    <div class='col-sm-12'>
                                                        {!! $rich_text_editor !!}
                                                    </div>
                                                </div>

                                                <div class="row p-2"></div>

                                                <div class='form-group'>
                                                    <label for='exerciseDescription' class='col-sm-4 control-label-notes'>{{ trans('langViewShow') }}:</label>
                                                    <div class='col-sm-12'>
                                                        <div class='radio'>
                                                            <label>
                                                                @if($exerciseType<=1)
                                                                    <input type="radio" name="exerciseType" value="1" checked>
                                                                @else
                                                                    <input type="radio" name="exerciseType" value="1">
                                                                @endif
                                                                Σε μία μόνο σελίδα
                                                            </label>
                                                        </div>
                                                        <div class='radio'>
                                                            <label>
                                                                @if($exerciseType>=2)
                                                                    <input type='radio' name='exerciseType' value='2' checked>
                                                                @else
                                                                    <input type='radio' name='exerciseType' value='2'>
                                                                @endif
                                                                Μία ερώτηση ανά σελίδα (στη σείρα)
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row p-2"></div>

                                                <div class='form-group'>
                                                    <label class='col-sm-4 control-label-notes'>{{ trans('langExerciseScaleGrade') }}:</label>
                                                    <div class='col-sm-12'>
                                                        <select name='exerciseRange' class='form-control'>
                                                            <option {{$exerciseRange == '0'?'selected':''}}>-- χωρίς κλίμακα --</option>
                                                            <option {{$exerciseRange == '10'?'selected':''}}>0-10</option>
                                                            <option {{$exerciseRange == '20'?'selected':''}}>0-20</option>
                                                            <option {{$exerciseRange == '5'?'selected':''}}>0-5</option>
                                                            <option {{$exerciseRange == '100'?'selected':''}}>0-100</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row p-2"></div>


                                                <div class="input-append date form-group" id="startdatepicker" data-bs-date="{{$exerciseStartDate}}" data-bs-date-format="dd-mm-yyyy">
                                                    <label for='exerciseStartDate' class='col-sm-4 control-label-notes'>{{ trans('langStartDate') }}:</label>
                                                    <div class='col-sm-12'>
                                                        <div class='input-group'>
                                                            <span class='input-group-addon'>
                                                                <input style='cursor:pointer;' type='checkbox' id='enableStartDate' name='enableStartDate' value='1' {{$enableStartDate ? 'checked' : ''}}>
                                                            </span>
                                                            <input class='form-control' name='exerciseStartDate' id='exerciseStartDate' type='text' value='{{$exerciseStartDate}}' {{$enableStartDate ? '' : 'disabled'}}>
                                                        </div>
                                                
                                                    </div>
                                                </div>

                                                <div class="row p-2"></div>


                                                <div class="input-append date form-group" id="enddatepicker" data-bs-date="{{$exerciseEndDate}}" data-bs-date-format="dd-mm-yyyy">
                                                    <label for='exerciseEndDate' class='col-sm-4 control-label-notes'>{{ trans('langFinish') }}:</label>
                                                    <div class='col-sm-12'>
                                                        <div class='input-group'>
                                                            <span class='input-group-addon'>
                                                            <input style='cursor:pointer;' type='checkbox' id='enableEndDate' name='enableEndDate' value='1' {{$enableEndDate ? 'checked' : ''}}>
                                                            </span>
                                                            <input class='form-control' name='exerciseEndDate' id='exerciseEndDate' type='text' value='{{$exerciseEndDate}}' {{$enableEndDate ? '' : 'disabled'}}>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="row p-2"></div>


                                                <div class='form-group'>
                                                    <label for='exerciseTempSave' class='col-sm-4 control-label-notes'>{{ trans('langTemporarySave') }}:</label>
                                                    <div class='col-sm-12'>
                                                        <div class='radio'>
                                                            <label>
                                                                <input type='radio' name='exerciseTempSave' value='0' {{$exerciseTempSave==0 ? 'checked' : ''}}>
                                                                {{ trans('langDeactivate') }}
                                                            </label>
                                                        </div>
                                                        <div class='radio'>
                                                            <label>
                                                                <input type='radio' name='exerciseTempSave' value='1' {{$exerciseTempSave==1 ? 'checked' : ''}}>
                                                                {{ trans('langActivate') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="row p-2"></div>


                                                <div class="form-group">
                                                    <label for='exerciseTimeConstraint' class='col-sm-4 control-label-notes'>{{ trans('langExerciseConstrain') }}:</label>
                                                    <div class='col-sm-12'>
                                                        <input type='text' class='form-control' name='exerciseTimeConstraint' id='exerciseTimeConstraint' value='{{$exerciseTimeConstraint}}' placeholder='{{$langExerciseConstrain}}'>
                                                        <span class='help-block'>{{ trans('langExerciseConstrainUnit') }} ({{ trans('langExerciseConstrainExplanation') }})</span>
                                                    </div>
                                                </div>

                                                <div class="row p-2"></div>


                                                <div class='form-group'>
                                                    <label for='exerciseAttemptsAllowed' class='col-sm-4 control-label-notes'>{{ trans('langExerciseAttemptsAllowed') }}:</label>
                                                    <div class='col-sm-12'>
                                                        <input type='text' class='form-control' name='exerciseAttemptsAllowed' id='exerciseAttemptsAllowed' value='{{$exerciseAttemptsAllowed}}' placeholder='{{$langExerciseConstrain}}'>
                                                        <span class='help-block'>{{ trans('langExerciseAttemptsAllowedUnit') }} ({{ trans('langExerciseAttemptsAllowedExplanation') }})</span>
                                                    </div>
                                                </div>


                                                <div class="row p-2"></div>



                                                <div class='form-group'>
                                                    <label for='dispresults' class='col-sm-4 control-label-notes'>{{ trans('langAnswers') }}:</label>
                                                    <div class='col-sm-12'>
                                                        <div class='radio'>
                                                            <label>
                                                                <input type='radio' name='dispresults' value='1' {{$displayResults == 1 ? 'checked' : ''}}>
                                                                {{ trans('langAnswersDisp') }}
                                                            </label>
                                                        </div>
                                                        <div class='radio'>
                                                            <label>
                                                                <input type='radio' name='dispresults' value='0' {{$displayResults == 0 ? 'checked' : ''}}>
                                                                {{ trans('langAnswersNotDisp') }}
                                                            </label>
                                                        </div>
                                                        <div id='answersDispLastAttempt'  class="radio{{$exerciseAttemptsAllowed ? '' : ' hidden'}}">
                                                            <label>
                                                                <input type='radio' name='dispresults' value='3' {{$displayResults == 3 ? 'checked' : ''}}>
                                                                {{ trans('langAnswersDispLastAttempt') }}
                                                            </label>
                                                        </div>
                                                        <div id='answersDispEndDate' class="radio{{!empty($exerciseEndDate) ? '' : ' hidden'}}">
                                                            <label>
                                                                <input type='radio' name='dispresults' value='4' {{$displayResults == 4 ? 'checked' : ''}}>
                                                                {{ trans('langAnswersDispEndDate') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="row p-2"></div>


                                                <div class='form-group'>
                                                    <label for='dispresults' class='col-sm-4 control-label-notes'>{{ trans('langScore') }}:</label>
                                                    <div class='col-sm-12'>
                                                        <div class='radio'>
                                                            <label>
                                                                <input type='radio' name='dispscore' value='1' {{$displayScore == 1 ? 'checked' : ''}}>
                                                                {{ trans('langScoreDisp') }}
                                                            </label>
                                                        </div>
                                                        <div class='radio'>
                                                            <label>
                                                                <input type='radio' name='dispscore' value='0' {{$displayScore == 0 ? 'checked' : ''}}>
                                                                {{ trans('langScoreNotDisp') }}
                                                            </label>
                                                        </div>
                                                        <div id='scoreDispLastAttempt' class="radio{{$exerciseAttemptsAllowed ? '' : ' hidden'}}">
                                                            <label>
                                                                <input type='radio' name='dispscore' value='3' {{$displayScore == 3 ? 'checked' : ''}}>
                                                                {{ trans('langScoreDispLastAttempt') }}
                                                            </label>
                                                        </div>
                                                        <div id='scoreDispEndDate' class="radio{{!empty($exerciseEndDate) ? '' : ' hidden'}}">
                                                            <label>
                                                                <input type='radio' name='dispscore' value='4' {{$displayScore == 4 ? 'checked' : ''}}>
                                                                {{ trans('langScoreDispEndDate') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="row p-2"></div>



                                                <div class='form-group'>
                                                    <label class='col-sm-2 control-label-notes'>{{$WorkAssignTo}}:</label>
                                                    <div class='col-sm-10'>
                                                        <div class='radio'>
                                                        <label>
                                                            <input type='radio' id='assign_button_all' name='assign_to_specific' value='0' {{$exerciseAssignToSpecific == 0 ? " checked" : ""}}>
                                                            <span>{{$WorkToAllUsers}}</span>
                                                        </label>
                                                        </div>
                                                        <div class='radio'>
                                                        <label>
                                                            <input type='radio' id='assign_button_user' name='assign_to_specific' value='1' {{$exerciseAssignToSpecific == 1 ? " checked" : ""}}>
                                                            <span>{{$WorkToUser}}</span>
                                                        </label>
                                                        </div>
                                                        <div class='radio'>
                                                        <label>
                                                            <input type='radio' id='assign_button_group' name='assign_to_specific' value='2' {{$exerciseAssignToSpecific == 2 ? " checked" : ""}}>
                                                            <span>{{$WorkToGroup}}</span>
                                                        </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row p-2"></div>

                                                
                                                <div class='form-group'>
                                                    <div class='col-sm-12 col-sm-offset-2'>
                                                        <div class='table-responsive'>
                                                            <table id='assignees_tbl' class="table-default{{(in_array($exerciseAssignToSpecific, [1, 2]) ? '' : ' hide')}}">
                                                                <tr class='title1'>
                                                                    <td id='assignees'>{{trans('langStudents')}}</td>
                                                                    <td class='text-center'>{{trans('langMove')}}</td>
                                                                    <td>{{$WorkAssignTo}}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <select class='form-control' id='assign_box' size='10' multiple>
                                                                            <?php ((isset($unassigned_options)) ? $unassigned_options : '') ?>
                                                                        </select>
                                                                    </td>
                                                                    <td class='text-center'>
                                                                        <input type='button' onClick="move('assign_box','assignee_box')" value="    &gt;&gt;   "/><br>
                                                                        <input type='button' onClick="move('assignee_box','assign_box')" value="    &lt;&lt;   "/>
                                                                    </td>
                                                                    <td width='40%'>
                                                                        <select class='form-control' id='assignee_box' name='ingroup[]' size='10' multiple>
                                                                            <?php ((isset($assignee_options)) ? $assignee_options : '') ?>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                


                                                <div class="row p-2"></div>


                                                <div class='form-group'>
                                                    <label class='col-sm-4 control-label-notes'>{{ trans('langContinueAttempt') }}:</label>
                                                    <div class='col-sm-12'>
                                                        <div class='checkbox'>
                                                            <label>
                                                                <input id='continueAttempt' name='continueAttempt' type='checkbox' {{$continueTimeLimit ? 'checked' : ''}}>
                                                                {{ trans('langContinueAttemptExplanation') }}
                                                            </label>
                                                        </div>
                                                        <div id='continueTimeField' class='form-inline' style="margin-top: 15px; {{$continueTimeLimit ? '' : 'display: none'}}">
                                                            Χρονικό περιθώριο: <input type="text" class="form-control" name="continueTimeLimit" value="{{$continueTimeLimit}}">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row p-2"></div>

                                                <div class='course-info-title clearfix'>
                                                    <a role='button' data-bs-toggle='collapse' href='#CheckAccess' aria-expanded='false' aria-controls='CheckAccess'>
                                                        <h5 class='panel-heading'>
                                                            <span class='fas fa-chevron-down fa-fw'></span> {{ trans('langCheckAccess') }}
                                                        </h5>
                                                    </a>
                                                </div>

                                                <div id='CheckAccess' class='collapse'>
                                                    <div class='form-group'>
                                                        <label for='exercisePasswordLock' class='col-sm-4 control-label-notes'>{{ trans('langPassCode') }}:</label>
                                                        <div class='col-sm-12'>
                                                            <input name='exercisePasswordLock' type='text' class='form-control' id='exercisePasswordLock' value='{{$exercisePasswordLock}}' placeholder=''>
                                                        </div>
                                                    </div>

                                                    <div class="row p-2"></div>

                                                    <div class='form-group'>
                                                        <label for='exerciseIPLock' class='col-sm-4 control-label-notes'>{{ trans('langIPUnlock') }}:</label>
                                                        <div class='col-sm-12'>
                                                            <select name='exerciseIPLock[]' class='form-control' id='exerciseIPLock' multiple>
                                                                {!! $exerciseIPLockOptions !!}
                                                            </select>
                                                        </div>
                                                    </div>
                                                    {!! $tagInputs !!}
                                                </div>

                                                <div class="row p-2"></div>


                                                <div class='form-group'>
                                                    <div class='col-sm-offset-2 col-sm-12'>
                                                        {!! $formButtons !!}
                                                    </div>
                                                </div>
                                           
                                        </form>
                                    
                                   
                                @endif

                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection