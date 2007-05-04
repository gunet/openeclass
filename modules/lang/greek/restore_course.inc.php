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

// restore_course.php

$langAdmin = "Εργαλεία Διαχείρισης";
$langRequest1 = "Κάντε κλικ στο Browse για να αναζητήσετε το αντίγραφο ασφαλείας του μαθήματος που θέλετε να επαναφέρετε. Μετά κάντε κλίκ στο 'Αποστολή'. ";
$langSend = "Αποστολή";
$langRestore = "Επαναφορά";

$langRequest2 = "Αν το αντίγραφο ασφαλείας, από το οποίο θα ανακτήσετε το μάθημα, είναι μεγάλο σε μέγεθος και δεν μπορείτε να το ανεβάσετε, τότε μπορείτε να πληκτρολογήσετε τη ακριβή διαδρομή (path) που βρίσκεται το αρχείο στον server.";
$langRestoreStep1 = "1° Ανάκτηση μαθήματος από αρχείο ή υποκατάλογο.";
$langDescribe = "Το αντίγραφο ασφαλείας κάποιου μαθήματος βρίσκεται σε ένα συμπιεσμένο αρχείο ή υποκατάλογο, και αποτελείται από 4 μέρη";
$langDescribe1 = "Ένα περιγραφικό αρχείο";
$langDescribe2 = "Ο υποκατάλογος των εγγράφων";
$langDescribe3 = "Το αρχείο SQL για την επαναδημιουργία της βάσης του μαθήματος";
$langDescribe4 = "Το αρχείο SQL που περιέχει τα δεδομένα της κεντρικής βάσης.";
$langFileNotFound = "Το αρχείο δεν βρέθηκε.";

$langFileSent = "Στάλθηκε ένα αρχείο";
$langFileSentName = "Όνομα:";
$langFileSentSize = "Μέγεθος:";
$langFileSentType = "Τύπος:";
$langFileSentTName = "Προσωρινό όνομα:";
$langFileUnzipping = "Αποσυμπίεση του αρχείου";
$langEndFileUnzip = "Τέλος αποσυμπίεσης";
$langLesFound = "Μαθήματα που βρέθηκαν μέσα στο αρχείο:";
$langLesFiles = "Αρχεία του μαθήματος:";

$langInvalidCode = "Μη αποδεκτός κωδικός μαθήματος";
$langCopyFiles = "Τα αρχεία του μαθήματος αντιγράφτηκαν στο";
$langCourseExists = "Υπάρχει ήδη ένα μάθημα με αυτόν τον κωδικό !";
$langUserExists = "Στη πλατφόρμα υπάρχει ήδη ένας χρήστης με username";
$langUserExists2 = "Ονομάζεται";
$langWarning = "<em><font color='red'>ΠΡΟΣΟΧΗ!</font></em> Αν επιλέξετε να μην προστεθούν οι χρήστες του μαθήματος και το αντίγραφο ασφαλείας του μαθήματος, περιέχει υποσυστήματα με πληροφορίες που σχετίζονται με τους χρήστες (π.χ. 'Εργασίες Φοιτητών', 'Χώρος Ανταλλαγής Αρχείων' ή 'Ομάδες Χρηστών') τότε οι πληροφορίες αυτές <b>ΔΕΝ</b> θα ανακτηθούν.";

$langUserWith = "Σφάλμα! Ο χρήστης με userid";
$langAlready = "ήδη προστέθηκε";
$langWithUsername = "Ο χρήστης με username";
$langUserisAdmin = "είναι διαχειριστής";
$langUsernameSame = "το username του παραμένει ίδιο.";
$langUserAlready = "Στη πλατφόρμα υπάρχει ήδη ένας χρήστης με username";
$langUName = "Ονομάζεται";

$langInfo1 = "Το αντίγραφο ασφαλείας που στείλατε, περιείχε τις παρακάτω πληροφορίες για το μάθημα.";
$langInfo2 = "Μπορείτε να αλλάξετε τον κωδικό του μαθήματος και ότι άλλο θέλετε (π.χ. περιγραφή, καθηγητής κ.λπ.)";
$langCourseCode = "Κωδικός";
$langCourseLang = "Γλώσσα";
$langCourseTitle = "Τίτλος";
$langCourseDesc = "Περιγραφή";
$langCourseFac = "Σχολή / τμήμα ";
$langCourseOldFac = "Παλιά σχολή / τμήμα";
$langCourseVis = "Τύπος πρόσβασης";
$langCourseProf = "Καθηγητής";
$langCourseType = "Προπτυχιακό / μεταπτυχιακό";
$langUserName = "Όνομα χρήστη";
$langPrevId = "Προηγούμενο user_id";
$langNewId = "Καινούριο user_id";

$langUsersWillAdd = "Οι χρήστες του μαθήματος θα προστεθούν";
$langUserPrefix = "Στα ονόματα χρηστών του μαθήματος θα προστεθεί ένα πρόθεμα";
$langOk = "Εντάξει";
$langGreek = "Ελληνικά";
$langEnglish = "Αγγλικά";
$langErrorLang = "Πρόβλημα! Δεν υπάρχουν γλώσσες για το μάθημα!";


?>
