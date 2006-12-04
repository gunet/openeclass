<?php
/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/*===========================================================================
	survey.inc.php
	@last update: 17-4-2006 by Costas Tsibanis
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================        
        @Description: Survey tool tranlation
==============================================================================
*/

$langQuestionnaire = "Questionnaire";
$langSurveysActive = "Active Surveys";
$langSurveysInactive = "Inactive Surveys";
$langSurveyName = "Íame";
$langSurveyNumAnswers = "Number of Answers";
$langSurveyCreation = "Creation Date";
$langSurveyStart = "Start Date";
$langSurveyEnd = "End Date";
$langSurveyOperations = "Operations";
$langSurveyEdit = "Edit";
$langSurveyRemove = "Delete";
$langSurveyCreate = "Create";
$langSurveyQuestion = "Question";
$langSurveyAnswer = "Answer";
$langSurveyAddAnswer = "Add Answers";
$langSurveyType = "Type";
$langSurveyMC = "Multiple Choice";
$langSurveyFillText = "Fill in the text";
$langSurveyContinue = "Continue";
$langSurveyMoreAnswers = "More answers";
$langSurveyYes = "Yes";
$langSurveyNo = "No";
$langSurveyMoreAnswers ="More answers";
$langSurveyMoreQuestions = "More questions";
$langSurveyCreate = "Create Survey";
$langSurveyCreated = "The Survey was created succefully. Click <a href=\"questionnaire.php\">here</a> to visit survey pages.";
$langSurveyCreator = "Creator";
$langSurveyCourse = "Course";
$langSurveyCreationError = "Survey creation error. Please try again.";
$langSurveyDeactivate = "Deactivate";
$langSurveyActivate = "Activate";
$langSurveyParticipate = "Participate";
$langSurveyDeleted = "The Survey was deleted successfully. Click <a href=\"questionnaire.php\">here</a> to visit survey pages.";
$langSurveyDeactivated = "The Survey was deactivated successfully. Click <a href=\"questionnaire.php\">here</a> to visit survey pages.";
$langSurveyActivated = "The Survey was activated successfully. Click <a href=\"questionnaire.php\">here</a> to visit survey pages.";
$langSurveySubmitted = "The Survey was activated successfully. Click <a href=\"questionnaire.php\">here</a> to visit survey pages.";
$langSurveyUser = "User";
$langSurveyTotalAnswers = "Total number of answers";
$langSurveyNone = "There are no surveys for the current course.";
$langSurveyInactive = "The survey has expired or has not started yet.";
$langSurveyCharts = "Collective Results";
$langSurveyIndividuals = "Results per user";

$langTestEcho = "The worked";
$langPollsActive = "Active Surveys";
$langPollsInactive = "Inactive Surveys";
$langPollName = "Íame";
$langPollNumAnswers = "Number of Answers";
$langPollCreation = "Creation Date";
$langPollStart = "Start Date";
$langPollEnd = "End Date";
$langPollOperations = "Operations";
$langPollEdit = "Edit";
$langPollRemove = "Delete";
$langPollCreate = "Create";
$langPollQuestion = "Question";
$langPollAnswer = "Answer";
$langPollAddAnswer = "Add Answers";
$langPollType = "Type";
$langPollMC = "Multiple Choice";
$langPollFillText = "Fill in the text";
$langPollContinue = "Continue";
$langPollMoreAnswers = "More answers";
$langPollYes = "Yes";
$langPollNo = "No";
$langPollMoreAnswers ="More answers";
$langPollMoreQuestions = "More questions";
$langPollCreate = "Create Survey";
$langPollCreated = "The Survey was created successfully. Click <a href=\"questionnaire.php\">here</a> to visit poll pages.";
$langPollCreator = "Creator";
$langPollCourse = "Course";
$langPollCreationError = "Survey creation error. Please try again.";
$langPollDeactivate = "Deactivate";
$langPollActivate = "Activate";
$langPollParticipate = "Participate";
$langPollDeleted = "The Survey was deleted successfully. Click <a href=\"questionnaire.php\">here</a> to visit poll pages.";
$langPollDeactivated = "The Survey was deactivated successfully. Click <a href=\"questionnaire.php\">here</a> to visit poll pages.";
$langPollActivated = "The Survey was activated successfully. Click <a href=\"questionnaire.php\">here</a> to visit poll pages.";
$langPollSubmitted = "The Survey was activated successfully. Click <a href=\"questionnaire.php\">here</a> to visit poll pages.";
$langPollUser = "User";
$langPollTotalAnswers = "Total number of answers";
$langPollNone = "There are no polls for the current course.";
$langPollInactive = "The poll has expired or is not active yet.";
$langPollCharts = "Collective Results";
$langPollIndividuals = "Results per user";

$langQPref = "What type of questionnaire do you prefer?";
$langQPrefSurvey = "Survey";
$langQPrefPoll = "Poll";

$langNamesPoll = "Polls";
$langNamesSurvey = "Surveys";

$langPollHasParticipated = "Already participated";
$langSurveyHasParticipated = "Already participated";
$langSurveyDeleteMsg = "Are you sure you would like to delete this survey?";
$langPollDeleteMsg = "Are you sure you would like to delete this poll?";
$langSurveyDeleteYes = "Yes";
$langSurveyDeleteNo = "No";

$langSurveyInfo1 ="Please ass the name of the Poll and how logn you wish it to be available to the users (by default 365 days)";
$langSurveyInfo2 ="Please confirm that the name of the Poll is correct and that the time period is th edesired. Then you may choose from a the list of COLLES/ATTL questions or add your own question in the blank fields.";

$langQQuestionNotGiven ="You have not entered the text for the last question.";
$langQFillInAllQs ="Please answer all questions.";
?>
