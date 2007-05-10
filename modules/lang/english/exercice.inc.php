<?
/*
      +----------------------------------------------------------------------+
      | GUnet eClass 2.0                                                     |
      | Asychronous Teleteaching Platform                                    |
      +----------------------------------------------------------------------+
      | Copyright (c) 2003-2007  GUnet                                       |
      +----------------------------------------------------------------------+
      |                                                                      |
      | GUnet eClass 2.0 is an open platform distributed in the hope that    |
      | it will be useful (without any warranty), under the terms of the     |
      | GNU License (General Public License) as published by the Free        |
      | Software Foundation. The full license can be read in "license.txt".  |
      |                                                                      |
      | Main Developers Group: Costas Tsibanis <k.tsibanis@noc.uoa.gr>       |
      |                        Yannis Exidaridis <jexi@noc.uoa.gr>           |
      |                        Alexandros Diamantidis <adia@noc.uoa.gr>      |
      |                        Tilemachos Raptis <traptis@noc.uoa.gr>        |
      |                                                                      |
      | For a full list of contributors, see "credits.txt".                  |
      |                                                                      |
      +----------------------------------------------------------------------+
      | Contact address: Asynchronous Teleteaching Group (eclass@gunet.gr),  |
      |                  Network Operations Center, University of Athens,    |
      |                  Panepistimiopolis Ilissia, 15784, Athens, Greece    |
      +----------------------------------------------------------------------+
*/


$langMaj="Update";
$langExercices="Exercices";
$langEvalSet="Scoring settings";
$langExercice="Exercise";
$langActive="Active";
$langInactive="Inactive";
$langActivate="Activate";
$langDeactivate="Deactivate";
$langNoEx="There is no exercise for the moment";
$langNewEx="New exercise";

// exercice_admin.php

$langExerciseType="Exercise Type";
$langExerciseName="Exercise Name";
$langExerciseDescription="Exercise Description";
$langQuestCreate="Creation of questions";
$langExRecord="Your exercise has been saved";
$langBackModif="Back to editing of this exercise";
$langDoEx="Do the exercise";
$langDefScor="Define scoring settings";
$langCreateModif="Creation/modification of questions";
$langSub="Sub-title";
$langNewQu="New question";
$langQuestion="Question";
$langQuestions="Questions";
$langDescription="Description";
$langAnswers="Answers";
$langTrue="True";
$langAnswer="Answer";
$langComment="Comment";
$langMorA="+answ.";
$langLesA="-answ.";
$langRecEx="Save exercise";
$langRecQu="Save question";
$langRecAns="Save Answers";
$langIntroduction="Introduction";
$langTitleAssistant="Exercices creation assistant";
$langQuesList="Questions list";
$langSaveEx="Save exercise";
$langClose="Close";
$langFinish="Finish";
$langCancel="Cancel";
$langQImage="Picture-question";
$langAddQ="Add Question";
$langAmong = "among";
$langTake = "Take";

// admin.php

$langExerciseManagement="Exercise Management";
$langQuestionManagement="Questions / Answers Management";
$langQuestionNotFound="Question not Found";

// question_admin.inc.php

$langNoAnswer="There is no answer";
$langGoBackToQuestionPool="Back to Question Pool";
$langGoBackToQuestionList="Back to question list";
$langQuestionAnswers="Question answers";
$langUsedInSeveralExercises="Be careful! This question and its answers are being used in several exercises. Do you want to change them?";
$langModifyInAllExercises="in all exercises";
$langModifyInThisExercise="in this exercise";

// statement_admin.inc.php

$langAnswerType="Answer type";
$langUniqueSelect="Multiple Choice (One Anwser)";
$langMultipleSelect="Multiple Choice (Multiple Anwsers)";
$langFillBlanks="Fill in the Blanks";
$langMatching="Matching";
$langAddPicture="Add Picture";
$langReplacePicture="Replace Picture";
$langDeletePicture="Delete Picture";
$langQuestionDescription="Optional description";
$langGiveQuestion="Enter your question";

// answer_admin.inc.php

$langWeightingForEachBlank="Enter weight for each blank";
$langUseTagForBlank="Use tags [...] to define one or more blanks";
$langQuestionWeighting="Weight";
$langTrue="True";
$langTypeTextBelow="Type your text below";
$langDefaultTextInBlanks="[Athens] is capital of Greece.";
$langDefaultMatchingOptA="rich";
$langDefaultMatchingOptB="good looking";
$langDefaultMakeCorrespond1="Your dady is";
$langDefaultMakeCorrespond2="Your mother is";
$langDefineOptions="Define options";
$langMakeCorrespond="Make correspond";
$langFillLists="Fill lists";
$langGiveText="Enter text";
$langDefineBlanks="Define one blank by tags [...]";
$langGiveAnswers="Enter answers";
$langChooseGoodAnswer="Choose a good answer";
$langChooseGoodAnswers="Choose good answers";

// question_list_admin.inc.php
$langNewQu="New Question";
$langQuestionList="Question List";
$langMoveUp="Move Up";
$langMoveDown="Move Down";
$langGetExistingQuestion="Get Existing Question";

// question_pool.php
$langQuestionPool="Question Pool";
$langOrphanQuestions="Orphan Questions";
$langNoQuestion="There is no question";
$langAllExercises="All exercises";
$langFilter="Filter";
$langGoBackToEx="Back to exercise";
$langReuse="Reuse";

// exercise_result.php
$langElementList="Element List";
$langResult="Result";
$langScore="Score";
$langCorrespondsTo="Corresponds to";
$langExpectedChoice="Expected Choice";
$langYourTotalScore="Your total score is";
$langYourTotalScore2="Score";



// exercice_submit.php

$langDoAnEx="Do an exercise";
$langGenerator="Exercises List";
$langResult="Score";
$langChoice="Your choice";
$langCorrect="True";



// scoring.php & scoring_student.php

$langPossAnsw="Number of possible answers for a question";
$langStudAnsw="Number of errors made by student";
$langDetermine="Determine yourself the scoring weight through editing the table below. Click then on \"Ok\"";
$langNonNumber="A non numeric value in";
$langAnd="and";
$langReplaced="has been introduced. It has been replaced by 0";
$langSuperior="A value bigger than 20 in";
$langRep20="has been introduced. It has been replaced by 20";
$langDefault="Default values *";
$langDefComment="* If you click on \"Default values\", your ancient values will be permanently deleted.";
$langScoreGet="Numbers in black = Score";


$langShowScor="Show scoring to students : ";
$langConfirmYourChoice = "Are you sure?";

$langExerciseStart="Start";
$langExerciseEnd="End";
$langExerciseConstrain="Time constrain";
$langExerciseEg="eg.";
$langExerciseConstrainUnit="minutes";
$langExerciseConstrainExplanation="0 for no constrain";
$langExerciseAttemptsAllowedExplanation="0 for unlimited number of attempts";
$langExerciseAttemptsAllowed="Attempts allowed";
$langExerciseAttemptsAllowedUnit="times";
$langExerciseExpired="The time limit for the exercise expired or you have reache dthe maximum number of allowed attempts.";
$langExerciseLis="List of exercises";
$langResults="Results";
$langResultsFailed="Failure";
$langYourTotalScore2="Total score";
$langExerciseScores1="Webpage";
$langExerciseScores2="Percentages";
$langExerciseScores3="CSV";
$langExerciseName="First Name";
$langExerciseSurname="Surname";

?>
