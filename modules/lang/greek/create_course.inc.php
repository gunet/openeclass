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

// create_course.php
$langLn="Γλώσσα";
$langLogin = "Χρήστης";
$langDescription="Περιγραφή";
$langDescrInfo="Σύντομη περιγραφή του μαθήματος";
$langCreateSite="Δημιουργία ενός μαθήματος";
$langFieldsRequ="Όλα τα πεδία είναι υποχρεωτικά!";
$langFieldsOptional = "Προαιρετικά πεδία";
$langFieldsOptionalNote = "Σημ. μπορείτε να αλλάξετε οποιεσδήποτε από τις παρακάτω πληροφορίες αργότερα";
$langTitle="Τίτλος μαθήματος";
$langEx="π.χ. <i>Ιστορία της Τέχνης</i>";
$langFac="Σχολή / Τμήμα";
$langDivision = "Τομέας";
$langTargetFac="Η σχολή ή το τμήμα που υπάγεται το μάθημα";
$langCode="Κωδικός Μαθήματος";
$langMax="με λατινικά γράμματα μέχρι 12 χαρακτήρες, π.χ. <i>FYS1234</i>";
$langDoubt="Αν δεν ξέρετε το κωδικό του μαθήματος συμβουλευτείτε";
$langProfessors="Εκπαιδευτής(ές)";
$langExplanation="Οταν πατήσετε «Δημιουργία», θα δημιουργηθεί η ιστοσελίδα του μαθήματος με Περιοχή συζητήσεων,
Ατζέντα, κ.λπ. την οποία μπορείτε να τροποποιήσετε αργότερα σύμφωνα με τις απαιτήσεις σας.";
$langExFac = "* Αν επιθυμείτε να δημιουργήσετε μάθημα, σε άλλο τμήμα από αυτό που ανήκετε, τότε επικοινωνήστε με
την Ομάδα Ασύγχρονης Τηλεκπαίδευσης";
$langEmpty="Αφήσατε μερικά πεδία κενά.<br>Πατήστε το πλήκτρο «Επιστροφή» του browser και ξαναδοκιμάστε.";
$langCodeTaken="Αυτός ο κωδικός μαθήματος χρησιμοποιείται ήδη. Παρακαλούμε επιλέξτε κάποιον άλλο.";
$langCreate="Δημιουργία";
$langCourseKeywords = "Λέξεις Κλειδιά:";
$langCourseKeywordsNote = "π.χ. <i>πρώτοι αριθμοί</i>";
$langCourseAddon = "Συμπληρωματικά Στοιχεία:";

$langAccessType="Επιλέξτε έναν από τους 3 τύπους πρόσβασης";
$langSubsystems="Επιλέξτε ποιά από τα υποσυστήματα θέλετε να ενεργοποιήσετε και ποιά όχι";
$langLanguageTip="Επιλέξτε σε ποια γλώσσα θα εμφανίζονται οι σελίδες του μαθήματος";

$langAccess = "Τύπος Πρόσβασης:";
$langAvailableTypes = "Διαθέσιμοι τύποι πρόσβασης";
$langModules = "Υποσυστήματα:";

// tables MySQL
$langForumLanguage="english";
$langTestForum="Δοκιμαστική περιοχή συζητήσεων";
$langDelAdmin="Διαγράψτε την μέσω του εργαλείου διαχείρισης της περιοχής";
$langMessage="Όταν διαγράψετε τη δοκιμαστική περιοχή συζητήσεων, θα διαγραφτεί και το παρόν μήνυμα.";
$langExMessage="Παράδειγμα Μηνύματος";
$langAnonymous="Ανώνυμος";
$langExerciceEx="Υπόδειγμα άσκησης";
$langAntique="Ιστορία της αρχαίας φιλοσοφίας";
$langSocraticIrony="Η Σωκρατική ειρωνεία είναι...";
$langManyAnswers="(περισσότερες από μία απαντήσεις μπορεί να είναι σωστές)";
$langRidiculise="Γελοιοποίηση του συνομιλητή σας προκειμένου να παραδεχτεί ότι κάνει λάθος.";
$langNoPsychology="Όχι, η Σωκρατική ειρωνεία δεν είναι θέμα ψυχολογίας, αλλά σχετίζεται με την επιχειρηματολογία.";
$langAdmitError="Παραδοχή των δικών σας σφαλμάτων ώστε να ενθαρρύνετε το συνομιλητή σας να κάνει το ίδιο.";
$langNoSeduction="Όχι, η Σωκρατική ειρωνεία δεν είναι μέθοδος αποπλάνησης, ούτε βασίζεται στο παράδειγμα.";
$langForce="Εξώθηση του συνομιλητή σας, με μια σειρά ερωτήσεων και υποερωτήσεων, να παραδεχτεί ότι δεν ξέρει ό,τι ισχυρίζεται πως ξέρει.";
$langIndeed="Πράγματι, η Σωκρατική ειρωνεία είναι μια μέθοδος ερωτημάτων.";
$langContradiction="Χρήση της αρχής της αποφυγής αντιφάσεων προκειμένου να οδηγήσετε τον συνομιλητή σας σε αδιέξοδο.";
$langNotFalse="Η απάντηση δεν είναι εσφαλμένη. Είναι αλήθεια ότι η αποκάλυψη της άγνοιας του συνομιλητή σας επιδεικνύει τα αντιφατικά συμπεράσματα που προκύπτουν από τις αρχικές παραδοχές του.";

//  MySQL Table "accueil"
$langAgenda="Ατζέντα";
$langLinks="Σύνδεσμοι";
$langDoc="Έγγραφα";
$langVideo="Βίντεο";
$langVideoLinks="Βιντεοσκοπημένα μαθήματα";
$langWorks="Εργασίες φοιτητών";
$langCourseProgram="Πρόγραμμα Μαθήματος";
$langAnnouncements="Ανακοινώσεις";
$langUsers="Χρήστες";
$langForums="Περιοχή συζητήσεων";
$langExercices="Ασκήσεις";
$langStatistics="Στατιστικά";
$langAddPageHome="Ανέβασμα Ιστοσελίδας";
$langLinkSite="Προσθήκη συνδέσμου στην αρχική σελίδα";
$langModifyInfo="Διαχείριση Μαθήματος";
$langConference ="Τηλεσυνεργασία";
$langDropBox = "Χώρος Ανταλλαγής Αρχείων";
$langLearnPath = "Γραμμή Μάθησης";
$langWiki = "Wiki";
$langToolManagement = "Διαχείριση εργαλείων";
$langUsage = "Στατιστικά Χρήσης";
$langPoll="Δημοσκόπηση";
$langSurvey="Ερωτηματολόγιο μαθησιακού προφίλ";
$langQuestionnaire = "Ερωτηματολόγιο";
$langCourseDesc = "Περιγραφή Μαθήματος";

// Other SQL tables
$langVideoText="Παράδειγμα ενός αρχείου RealVideo. Μπορείτε να ανεβάσετε οποιοδήποτε τύπο αρχείου βίντεο (.mov, .rm, .mpeg...), εφόσον οι φοιτητές έχουν το αντίστοιχο plug-in για να το δούν";
$langGoogle="Γρήγορη και Πανίσχυρη μηχανής αναζήτησης";
$langIntroductionText="Εισαγωγικό κείμενο του μαθήματος. Αντικαταστήτε το με το δικό σας, κάνοντας κλίκ στην <b>Αλλαγή</b>.";
$langIntroductionTwo="Αυτή η σελίδα επιτρέπει οποιοδήποτε φοιτητή να ανεβάσει ένα αρχείο στο μάθημα. Μπορείτε να στείλετε αρχεία HTML, μόνο αν δεν έχουν εικόνες.";
$langCourseDescription="Γράψτε μια περιγραφή η οποία θα εμφανίζεται στο κατάλογο μαθημάτων .";
$langProfessor="Καθηγητής";
$langAnnouncementEx="Παράδειγμα ανακοίνωσης. Μόνο ο καθηγητής και τυχόν άλλοι διαχειριστές του μαθήματος μπορεί να εισαγάγουν ανακοινώσεις.";
$langJustCreated="Μόλις δημιουργήσατε με επιτυχία το μάθημα με τίτλο ";
$langEnter="Είσοδος";
 // Groups
$langGroups="Ομάδες Χρηστών";
$langCreateCourseGroups="Ομάδες Χρηστών";
$langCatagoryMain="Αρχή";
$langCatagoryGroup="Συζήτησεις Ομάδων χρηστών";

//neos odhgos dhmiourgias mathimaton
$langEnterMetadata="(Σημ.) μπορείτε να αλλάξετε διάφορες ρυθμίσεις του μαθήματος μέσα από τη λειτουργία 'Διαχείριση Μαθήματος'";
$langCreateCourse="Οδηγός δημιουργίας μαθήματος";
$langCreateCourseStep="Βήμα";
$langCreateCourseStep2="από";
$langCreateCourseStep1Title="Βασικά στοιχεία και πληροφορίες μαθήματος";
$langCreateCourseStep2Title="Συμπληρωματικές πληροφορίες μαθήματος";
$langCreateCourseStep3Title="Υποσυστήματα και τύπος πρόσβασης";
$langcourse_objectives="Στόχοι του μαθήματος";
$langcourse_prerequisites="Προαπαιτούμενες γνώσεις";
$langcourse_references="Συπληρωματικά Στοιχεία";
$langcourse_keywords="Λέξεις κλειδιά";
$langNextStep="Επόμενο βήμα";
$langPreviousStep="Προηγούμενο βήμα";
$langFinalize="Δημιουργία μαθήματος!";
$langCourseCategory="Η κατηγορία στην οποία ανήκει το μάθημα";
$langProfessorsInfo="Ονοματεπώνυμα καθηγητών του μαθήματος χωρισμένα με κόμματα (π.χ.<i>Νίκος Τζικόπουλος, Κώστας Αδαμόπουλος</i>)";

$langPublic="Ανοικτό (Ελεύθερη Πρόσβαση από τη αρχική σελίδα χωρίς συνθηματικό)";
$langPrivOpen="Ανοικτό με Εγγραφή (Ελεγχόμενη Πρόσβαση με ανοικτή εγγραφή)";
$langPrivate="Κλειστό (Πρόσβαση στο μάθημα έχουν μόνο οι χρήστες που βρίσκονται στη Λίστα Χρηστών)";
$langAlertTitle = "Παρακαλώ συμπληρώστε τον τίτλο του μαθήματος!";
$langAlertProf = "Παρακαλώ συμπληρώστε τον διδάσκοντα του μαθήματος!";
?>
