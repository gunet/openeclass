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

// exercice.php

$langNoResult="Ακόμα δεν υπάρχει αποτέλεσμα";
$langQuestion="Ερώτηση";
$langQuestions="Ερωτήσεις";
$langAnswer="Απάντηση";
$langAnswers="Απαντήσεις";
$langComment="Σχόλιο";

$langMaj="Ενημέρωση";
$langExercices="Ασκήσεις";
$langEvalSet="Ρυθμίσεις βαθμολογίας";
$langExercice="Άσκηση";
$langActive="ενεργό";
$langInactive="μη ενεργό";
$langActivate="Ενεργοποίηση";
$langDeactivate="Απενεργοποίηση";
$langNoEx="Αυτή τη στιγμή δεν υπάρχει άσκηση";
$langNewEx="Καινούρια άσκηση";
$langPreviousPage = "Προηγούμενη";
$langNextPage = "Επόμενη";
$langNext = "Επόμενη";
// exercise_admin.inc.php

$langExerciseType="Τύπος Ασκήσεων";
$langExerciseName="'Ονομα Άσκησης";
$langExerciseDescription="Περιγραφή Άσκησης";
$langSimpleExercise="Σε μία μόνο σελίδα";
$langSequentialExercise="Σε μία ερώτηση ανά σελίδα (στη σειρά)";
$langRandomQuestions="Τυχαίες Ερωτήσεις";
$langGiveExerciseName="Δώστε το όνομα της άσκησης";
$langGiveExerciseInts="Τα πεδία Χρονικός Περιορισμός & Επιτρεπόμενες Επαναλήψεις πρέπει να είναι ακέραιοι (0, 1, 2, ..,ν)";
$langQuestCreate="Δημιουργία ερωτήσεων";
$langExRecord="Η άσκηση σας αποθηκεύτηκε";
$langBackModif="Επιστροφή στην διόρθωση της άσκησης";
$langDoEx="Κάντε την άσκηση";
$langDefScor="Καθορίστε τις ρυθμίσεις βαθμών";
$langCreateModif="Δημιουργία / Αλλαγή των ερωτήσεων";
$langSub="Υπότιτλος";
$langNewQu="Καινούρια ερώτηση";
$langDescription="Περιγραφή";
$langTrue="Σωστό";
$langMoreAnswers="+απαντ.";
$langLessAnswers="-απάντ.";
$langMoreElements="Προσθήκη";
$langLessElements="Αφαίρεση";
$langRecEx="Αποθήκευση άσκησης";
$langRecQu="Αποθήκευση ερώτησης";
$langRecAns="Αποθήκευση απαντήσεων";
$langIntroduction="Εισαγωγή";
$langTitleAssistant="Βοηθός δημιουργίας ασκήσεων";
$langQuesList="Κατάλογος ερωτήσεων";
$langSaveEx="Αποθήκευση απάντησης";
$langClose="Κλείσιμο";
$langFinish="Τέλος";
$langCancel="Ακύρωση";
$langQImage="Ερώτηση-Εικόνα";
$langAddQ="Προσθήκη ερώτησης";
$langAmong = "μεταξύ";
$langTake = "διάλεξε";

// admin.php
$langExerciseManagement="Διαχείριση Ασκήσεων";
$langQuestionManagement="Διαχείριση Ερωτήσεων / Απαντήσεων";
$langQuestionNotFound="Δεν βρέθηκε η ερώτηση";

// question_admin.inc.php
$langNoAnswer="Δεν υπάρχει απάντηση αυτή την στιγμή";
$langGoBackToQuestionPool="Επιστροφή στις δισθέσιμες ερωτήσεις";
$langGoBackToQuestionList="Επιστροφή στη λίστα ερωτήσεων";
$langQuestionAnswers="Απαντήσεις στην ερώτηση";
$langUsedInSeveralExercises="Προσοχή ! Αυτή η ερώτηση και οι απαντήσεις τις χρησιμοποιούνται σε αρκετές ασκήσεις. Θέλετε να τις αλλάξετε;";
$langModifyInAllExercises="σε όλες τις ασκήσεις";
$langModifyInThisExercise="μόνο στην τρέχουσα άσκηση";

// statement_admin.inc.php

$langAnswerType="Τύπος Απάντησης";
$langUniqueSelect="Πολλαπλής Επιλογής (Μοναδική Απάντηση)";
$langMultipleSelect="Πολλαπλής Επιλογής (Πολλαπλές Απαντήσεις)";
$langFillBlanks="Συμπλήρωμα Κενών";
$langMatching="Ταίριασμα";
$langAddPicture="Προσθήκη εικόνας";
$langReplacePicture="Αντικατάσταση της εικόνας";
$langDeletePicture="Διαγραφή της εικόνας";
$langQuestionDescription="Προαιρετικό σχόλιο";
$langGiveQuestion="Δώστε την ερώτηση";


// answer_admin.inc.php

$langWeightingForEachBlank="Δώστε ένα βάρος σε κάθε κενό";
$langUseTagForBlank="χρησιμοποιήστε αγκύλες [...] για να ορίσετε ένα ή περισσότερα κενά";
$langQuestionWeighting="Βάρος";
$langTrue="Σωστό";
$langTypeTextBelow="Πληκτρολογήστε το κείμενό σας παρακάτω";
$langDefaultTextInBlanks="Πρωτεύουσα της Ελλάδας είναι η [Αθήνα].";
$langDefaultMatchingOptA="rich";
$langDefaultMatchingOptB="good looking";
$langDefaultMakeCorrespond1="Your dady is";
$langDefaultMakeCorrespond2="Your mother is";
$langDefineOptions="Καθορίστε τις επιλογές";
$langMakeCorrespond="Κάντε την αντιστοιχία";
$langFillLists="Συμπληρώστε τις δύο λίστες που ακολουθούν";
$langGiveText="Πληκτρολογήστε το κείμενο";
$langDefineBlanks="Ορίστε τουλάχιστον ένα κενό με αγκύλες [...]";
$langGiveAnswers="Δώστε τις απαντήσεις στις ερωτήσεις";
$langChooseGoodAnswer="Διαλέξτε μια καλή απάντηση";
$langChooseGoodAnswers="Διαλέξτε μια ή περισσότερες καλές απαντήσεις";

// question_list_admin.inc.php
$langNewQu="Καινούρια Ερώτηση";
$langQuestionList="Κατάλογος ερωτήσεων της άσκησης";
$langMoveUp="Μετακίνηση πάνω";
$langMoveDown="Μετακίνηση κάτω";
$langGetExistingQuestion="Ερώτηση από άλλη άσκηση";

// question_pool.php
$langQuestionPool="Διαθέσιμες Ερωτήσεις";
$langOrphanQuestions="Ερωτήσεις χωρίς απάντηση";
$langNoQuestion="Δεν υπάρχει ερώτηση αυτή την στιγμή";
$langAllExercises="Ολες οι ασκήσεις";
$langFilter="Φιλτράρισμα";
$langGoBackToEx="Επιστροφή στην άσκηση";
$langReuse="Επαναχρησιμοποίηση";

// exercise_result.php
$langElementList="Το στοιχείο";
$langResult="Αποτέλεσμα";
$langScore="Βαθμολογία";
$langCorrespondsTo="Αντιστοιχεί σε";
$langExpectedChoice="Αναμενόμενη Απάντηση";
$langYourTotalScore="Η συνολική σου βαθμολογία είναι";
$langYourTotalScore2="Βαθμολογία";

// exercice_submit.php
$langDoAnEx="Κάντε μια άσκηση";
$langGenerator="Γεννήτρια ασκήσεων";
$langResult="Βαθμολογία";
$langChoice="Η επιλογή σας";
$langCorrect="Σωστό";

$langExerciseNotFound="Η απάντηση δεν βρέθηκε";
$langAlreadyAnswered="Απαντήσατε ήδη στην ερώτηση";

// scoring.php & scoring_student.php
$langPossAnsw="Αριθμός πιθανών απαντήσεων για μια ερώτηση";
$langStudAnsw="αριθμός λαθών από φοιτητή";
$langDetermine="Ορίστε τους βαθμούς-βάρη των απαντήσεων συμπληρώνοντας τον παρακάτω πίνακα. Στη συνέχεια πατήστε \"Εντάξει\"";
$langNonNumber="Ενας βαθμός μικρότερος του 0";
$langAnd="και";
$langReplaced="έχει μπεί. Εχει αντικατασταθεί από το 0";
$langSuperior="Εχετε βάλει ένα βαθμό μεγαλύτερο του 20";
$langRep20="Εχει αντικατασταθεί από το  20";
$langDefault="Εξ' ορισμού βαθμοί *";
$langDefComment="* Εάν πατήσετε στο \"Εξ όρισμού Βαθμοί\", οι προηγούμενες τιμές θα διαγραφούν οριστικά.";
$langScoreGet="Οι αριθμοί με μαύρο χρώμα ειναι η βαθμολογία";

$langShowScor="Εμφάνιση βαθμολογίας στους φοιτητές : ";

$langConfirmYourChoice="Είστε σίγουρος;";

$langExerciseStart="Έναρξη";
$langExerciseEnd="Λήξη";
$langExerciseConstrain="Χρονικός περιορισμός";
$langExerciseEg="π.χ.";
$langExerciseConstrainUnit="λεπτά";
$langExerciseConstrainExplanation="0 για καθόλου περιορισμό";
$langExerciseAttemptsAllowedExplanation="0 για απεριόριστο αριθμό επαναλήψεων";
$langExerciseAttemptsAllowed="Επιτρεπόμενες επαναλήψεις";
$langExerciseAttemptsAllowedUnit="φορές";
$langExerciseExpired="Έχετε ξεπεράσει το επιτρεπτό χρονικό όριο ή έχετε ήδη φτάσει τον μέγιστο επιτρεπτό αριθμό επαναλήψεων της άσκησης.";
$langExerciseLis="Λίστα ασκήσεων";
$langResults="Αποτελέσματα";
$langResultsFailed="Αποτυχία";
$langYourTotalScore2="Συνολική βαθμολογία";
$langExerciseScores1="Ιστοσελίδα";
$langExerciseScores2="Ποσοστιαία";
$langExerciseScores3="CSV";
$langExerciseName="Όνομα";
$langExerciseSurname="Επώνυμο";
?>
