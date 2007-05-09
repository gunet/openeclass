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

$langInvalidId = '<font color="red" size="1" face="arial, helvetica">
        Λάθος στοιχεία.<br>Αν δεν είστε γραμμένος, συμπληρώστε τη
        <a href=modules/auth/newuser_info.php>φόρμα εγγραφής</a>.
        </font><br>&nbsp;<br>';
$langAccountInactive1 = "Μη ενεργός λογαριασμός.";
$langAccountInactive2 = "Παρακαλώ επικοινωνήστε με τον διαχειριστή για την ενεργοποίηση του λογαριασμού σας";
$langMyCourses="Τα μαθήματά μου";
$langMyCoursesProf="Τα μαθήματα που υποστηρίζω (Καθηγητής)";
$langMyCoursesUser="Τα μαθήματα που παρακολουθώ (Εγγεγραμμένος)";

$langNoCourses="Δεν υπάρχουν μαθήματα";

$langCourseCreate="Δημιουργία μαθήματος";
$langModifyProfile="Αλλαγή του προφίλ μου";
$langMyAgenda = "Το Ημερολόγιό μου";
$langMyStats = "Προσωπικά Στατιστικά Χρήσης";   #ophelia 1-8-2006
$langMyAnnouncements = "Οι Ανακοινώσεις μου";
$langWelcome="τα μαθήματα είναι διαθέσιμα παρακάτω. Άλλα μαθήματα απαιτούν
όνομα χρήστη και συνθηματικό, τα οποία μπορείτε να τα αποκτήσετε κάνοντας κλίκ στην 'εγγραφή'. Οι καθηγητές
μπορούν να δημιουργήσουν μαθήματα κάνοντας κλικ στην εγγραφή επίσης, αλλά επιλέγοντας ύστερα
'Δημιουργία μαθημάτων (καθηγητές)'.";
$langAdminTool = "Εργαλεία Διαχείρισης";
$langUserName="Όνομα χρήστη (username)";
$langPass="Συνθηματικό (password)";
$langEnter="Είσοδος";
$langHelp="Βοήθεια";
$langManager="Διαχειριστής";
$langManagement="Διαχείριση";
$langReg="Εγγραφή";
$langMenu ="Μενού";
$langLogout="Έξοδος";
$langOtherCourses="Εγγραφή σε μάθημα";
$langSupportForum="Περιοχή Υποστήριξης";
$langNewUser = 'Εγγραφή Χρήστη';
$langProfReq = 'Εγγραφή Καθηγητή';
$langUser = 'Χρήστης:';
$langManuals = 'Εγχειρίδια';
$langContact = 'Επικοινωνία';
$langInfoPlat = 'Ταυτότητα Πλατφόρμας';
$lang_forgot_pass = "Ξεχάσατε το συνθηματικό σας;";
$langNewAnnounce = "Νέα !";
$langUnregUser = "Διαγραφή λογαριασμού";
$langListFaculte = "Κατάλογος Μαθημάτων";
$langAsynchronous = "Ομάδα Ασύγχρονης Τηλεκπαίδευσης";
$langCopyright = "Πληροφορίες Πνευματικών Δικαιωμάτων";
$langUserLogin = "Σύνδεση χρήστη";
$langWelcomeToEclass = "Καλωσορίσατε στο eClass!";
$langSearch = "Αναζήτηση";
$langPlatformAnnounce = "Ανακοινώσεις";
$langUnregCourse = "Απεγγραφή από μάθημα";
$langUnCourse = "Απεγγραφή";
$langCourseCode = "Μάθημα (Κωδικός)";
$langInfo = "Η πλατφόρμα <strong>GUnet eClass</strong> αποτελεί ένα ολοκληρωμένο σύστημα ηλεκτρονικής οργάνωσης, αποθήκευσης και παρουσίασης ηλεκτρονικού εκπαιδευτικού υλικού. Είναι σχεδιασμένη με προσανατολισμό την ενίσχυση και υποστήριξη της εκπαιδευτικής διαδικασίας, προσφέροντας στους συμμετέχοντες ένα δυναμικό περιβάλλον αλληλεπίδρασης, ανεξάρτητο από τους περιοριστικούς παράγοντες του χώρου και του χρόνου της κλασσικής διδασκαλίας.";

/*$langWelcomeStud = "<br>Καλωσήλθατε στο περιβάλλον της πλατφόρμας <b>$siteName</b>.<br><br>
                    Επιλέξτε \"Εγγραφή σε μάθημα\" για να παρακολουθήσετε τα διαθέσιμα ηλεκτρονικά μαθήματα.";
$langWelcomeProf = "<br>Καλωσήλθατε στο περιβάλλον της πλατφόρμας <b>$siteName</b>.<br><br>
                    Επιλέξτε \"Δημιουργία Μαθήματος\" για να δημιουργήσετε τα ηλεκτρονικά σας μαθήματα.";
*/

$langWelcomeStud = "<br>Καλωσήλθατε στο περιβάλλον της πλατφόρμας <b>GUNet eClass</b>.<br><br>
                    Επιλέξτε \"Εγγραφή σε μάθημα\" για να παρακολουθήσετε τα διαθέσιμα ηλεκτρονικά μαθήματα.";
$langWelcomeProf = "<br>Καλωσήλθατε στο περιβάλλον της πλατφόρμας <b>GUNet eClass</b>.<br><br>
                    Επιλέξτε \"Δημιουργία Μαθήματος\" για να δημιουργήσετε τα ηλεκτρονικά σας μαθήματα.";

?>
