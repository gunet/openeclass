<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
/*********************************************
* about.inc.php
*********************************************/

$langIntro = "Η πλατφόρμα <b>$siteName</b> είναι ένα ολοκληρωμένο Σύστημα Διαχείρισης Ηλεκτρονικών Μαθημάτων και υποστηρίζει την Υπηρεσία Ασύγχρονης Τηλεκπαίδευσης στο <a href=\"$InstitutionUrl\" target=\"_blank\" class=mainpage>$Institution</a>.";
$langVersion="Έκδοση του $siteName";
$langAboutText="Η έκδοση της πλατφόρμας είναι";
$langHostName="Ο υπολογιστής στον οποίο βρίσκεται η πλατφόρμα είναι ο ";
$langWebVersion="Xρησιμοποιεί ";
$langMySqlVersion="και MySql ";
$langNoMysql="Η MySql δεν λειτουργεί !";
$langUptime = "Λειτουργεί από τις";
$langTotalHits = "Συνολικές προσβάσεις";
$langLast30daysLogins = "Συνολικές προσβάσεις στην πλατφόρμα τις τελευταίες 30 μέρες";
$langTotalCourses = "Αριθμός μαθημάτων";
$langInfo = "Ταυτότητα Πλατφόρμας";
$langAboutCourses = "Η πλατφόρμα υποστηρίζει συνολικά";
$langAboutUsers = "H πλατφόρμα διαθέτει";
$langOperation ="Λειτουργία";


#For the logged-out user:
$langAboutCourses1 = "Αυτή τη στιγμή, η πλατφόρμα διαθέτει συνολικά";
$langAboutUsers1 = "Οι εγγεγραμένοι χρήστες είναι ";
$langLast30daysLogins1 = "και οι συνολικές προσβάσεις στην πλατφόρμα τις τελευταίες 30 μέρες είναι ";
$langAnd = "και";
$langCourses = "μαθήματα";
$langCoursesHeader = "Μαθήματα";
$langClosed = "κλειστά";
$langOpen = "ανοικτά";
$langSemiopen = "απαιτούν εγγραφή";
$langUsers = "Χρήστες";
$langUsersS = "χρήστες";
$langUser = "Χρήστης";
$langUserHeader = "Χρήστης:";
$langUserS = "χρήστης";
$langSupportUser = "Υπεύθυνος Υποστήριξης:";
$langAdminUsers = "Διαχείριση Χρηστών";


/********************************************
* addadmin.inc.php
*********************************************/
$langDeleteAdmin="H διαγραφή του διαχειριστή με id:";
$langNotFeasible ="δεν είναι εφικτή";
$langNomPageAddHtPass = "Προσθήκη διαχειριστή";
$langPassword = "Συνθηματικό";
$langAdd = "Προσθήκη";
$langNotFound = "δεν βρέθηκε";
$langWith = "με";
$langDone = "έγινε διαχειριστής.";
$langErrorAddaAdmin = "Σφάλμα: ο χρήστης δεν προστέθηκε στους διαχειριστές. Πιθανόν να είναι ήδη διαχειριστής.";
$langInsertUserInfo = "Εισαγωγή στοιχείων χρήστη";
$langPage="Σελίδα:";
$langBeforePage="Προηγούμενη";
$langAfterPage="Eπόμενη";
/****************************************************
* admin.inc.php
****************************************************/
// index
$langComments = "Σχόλια";
$langAdmin = "Εργαλεία Διαχείρισης Πλατφόρμας";
$langState = "Διαχείριση Εξυπηρετητή";
$langDevAdmin ="Διαχείριση Βάσης Δεδομένων";
$langSysInfo  	= "Πληροφορίες Συστήματος";
$langCheckDatabase  	= "Ελεγχος κύριας βάσης δεδομένων";
$langStatOf 	= "Στατιστικά του ";
$langSpeeSubscribe 	= "Εγγραφή σαν Διαχειριστής Μαθήματος";
$langLogIdentLogout 	= "Καταγραφή των εισόδων και εξόδων από το σύστημα";
$langPlatformStats 	= "Στατιστικά Πλατφόρμας";
$langPlatformGenStats   = "Γενικά στατιστικά";
$langVisitsStats        = "Στατιστικά επισκέψεων";
$langMonthlyReport      = "Μηνιαίες αναφορές";
$langReport             = "Αναφορά για το μήνα";
$langNoReport           = "Δεν υπάρχουν διαθέσιμα στοιχεία για το μήνα";
$langEmailNotSend = "Σφάλμα κατά την αποστολή e-mail στη διεύθυνση";
$langFound = "Βρέθηκαν";
$langFoundIt = "βρέθηκε";
$langListCours = "Λίστα Μαθημάτων / Ενέργειες";
$langListUsersActions = "Λίστα Χρηστών / Ενέργειες";
$langSearchUser = "Αναζήτηση Χρήστη";
$langInfoMail = "Ενημερωτικό email";
$langProfReg = "Εγγραφή $langOfTeacher";
$langProfOpen = "Αιτήσεις $langOfTeachers";
$langUserOpen = "Αιτήσεις $langOfStudents";
$langPHPInfo = "Πληροφορίες για την PHP";
$langManuals = "Διαθέσιμα Εγχειρίδια";
$langFormatPDF = "Μορφή PDF";
$langFormatHTML = "Μορφή HTML";
$langAdminManual = "Εγχειρίδιο Διαχειριστή";
$langConfigFile = "Αρχείο ρυθμίσεων";
$langDBaseAdmin = "Διαχείριση Β.Δ. (phpMyAdmin)";
$langActions = "Ενέργειες";
$langAdminProf = "Διαχείριση $langOfTeachers";
$langAdminCours = "Διαχείριση Μαθημάτων";
$langGenAdmin="Άλλα Εργαλεία";
$langBackAdmin = "Επιστροφή στη σελίδα διαχείρισης";
$langPlatformIdentity = "Ταυτότητα Πλατφόρμας";
$langStoixeia = "Στοιχεία Πλατφόρμας";
$langThereAre = "Υπάρχουν";
$langThereIs = "Υπάρχει";
$langOpenRequests = "Ανοικτές αιτήσεις ".$langsOfTeachers;
$langNoOpenRequests = "Δεν βρέθηκαν ανοικτές αιτήσεις ".$langsOfTeachers;
$langInfoAdmin  = "Ενημερωτικά Στοιχεία για τον Διαχειριστή";
$langLastLesson = "Τελευταίο μάθημα που δημιουργήθηκε:";
$langLastProf = "Τελευταία εγγραφή ".$langsOfTeacher.":";
$langLastStud = "Τελευταία εγγραφή ".$langsOfStudent.":";
$langAfterLastLogin = "Μετά την τελευταία σας είσοδο έχουν εγγραφεί στην πλατφόρμα:";
$langAfterLastLoginInfo = "Στοιχεία μετά την τελευταία σας είσοδο:";
$langOtherActions = "Άλλες Ενέργειες";
$langTutorialDesc = "Επίσης, στον παρακάτω σύνδεσμο, θα βρείτε χρήσιμους οδηγούς λειτουργίας και διαχείρισης της πλατφόρμας Open eClass με πρακτικά παραδείγματα που διευκολύνουν τόσο τους εκπαιδευτές και τους εκπαιδευόμενους όσο και τους υπεύθυνους διαχειριστές. Οι οδηγοί αυτοί είναι διαθέσιμοι σε 3 διαφορετικές μορφές: α) εικονογραφημένοι οδηγοί, β) βιντεοπαρουσιάσεις και γ) SCORM Packages.";
$langTutorial = "Χρήσιμοι Οδηγοί";
// Stat
$langStat4eClass = "Στατιστικά πλατφόρμας";
$langNbProf = "Αριθμός ".$langsOfTeachers;
$langNbStudents = "Αριθμός ".$langsOfStudents;
$langNbLogin = "Αριθμός εισόδων";
$langNbCourses = "Αριθμός μαθημάτων";
$langNbVisitors = "Αριθμός επισκεπτών";
$langToday   ="Σήμερα";
$langLast7Days ="Τελευταίες 7 μέρες";
$langLast30Days ="Τελευταίες 30 μέρες";
$langNbAnnoucement = "Αριθμός ανακοινώσεων";
$langNbUsers = "Αριθμός χρηστών";
$langCoursVisible = "Ορατότητα";
$langOthers = "Διάφορα σύνολα";
$langCoursesPerVis= "Αριθμός μαθημάτων ανά κατάσταση ορατότητας";
$langUsersPerCourse= "Αριθμός χρηστών ανά μάθημα";
$langErrors = "Σφάλματα:";
$langMultEnrol = "Πολλαπλές εγγραφές χρηστών";
$langMultEmail= "Πολλαπλές εμφανίσεις διευθύνσεων e-mail";
$langMultLoginPass = "Πολλαπλά ζεύγη LOGIN - PASS";
$langOk = "Εντάξει";
$langCont = "Συνέχεια";
$langNumUsers = "Αριθμός συμμετεχόντων στην πλατφόρμα";
$langNumGuest = "Αριθμός επισκεπτών";
$langAddAdminInApache ="Προσθήκη Διαχειριστή";
$langRestoreCourse = "Ανάκτηση Μαθήματος";
$langStatCour = "Ποσοτικά στοιχεία μαθημάτων";
$langNumCourses = "Αριθμός μαθημάτων";
$langNumEachCourse = "Αριθμός μαθημάτων ανά $langsFaculty";
$langNumEachLang = "Αριθμός μαθημάτων ανά γλώσσα";
$langNunEachAccess = "Αριθμός μαθημάτων ανά τύπο πρόσβασης";
$langNumEachCat = "Αριθμός μαθημάτων ανά τύπο μαθημάτων";
$langAnnouncements = "Ανακοινώσεις";
$langNumEachRec = "Αριθμός εγγραφών ανά μάθημα";
$langFrom = "Από";
$langFrom2 = "από";
$langNotExist = "Δεν υπάρχουν";
$langExist = "Υπάρχουν!";
$langResult =" Αποτέλεσμα";
$langMultiplePairs = "Πολλαπλά ζεύγη";
$langMultipleAddr = "Πολλαπλές εμφανίσεις διευθύνσεων";
$langMultipleUsers = "Πολλαπλές εγγραφές χρηστών";
$langAlert = "Σημεία Προσοχής";
$langServerStatus ="Κατάσταση του εξυπηρέτη Mysql : ";
$langDataBase = "Βάση δεδομένων ";
$langLanguage ="Γλώσσα";
$langUpgradeBase = "Αναβάθμιση βάσης Δεδομένων";
$langCleanUp = "Διαγραφή παλιών αρχείων";
$langUpdatingStatistics = "Ενημέρωση μηνιαίων στατιστικών";
$langPleaseWait = "Παρακαλώ περιμένετε";

// listusers
$langBegin="αρχή";
$langEnd = "τέλος";
$langPreced50 = "Προηγούμενοι";
$langFollow50 = "Επόμενοι";
$langAll="όλοι";
$langNoSuchUsers = "Δεν υπάρχουν χρήστες σύμφωνα με τα κριτήρια που ορίσατε";
$langAsInactive = "ως μη ενεργοί";

// listcours
$langOpenCourse = "Ανοιχτό";
$langClosedCourse = "Κλειστό";
$langRegCourse = "Απαιτείται Εγγραφή";

// quotacours
$langQuotaAdmin = "Διαχείριση Αποθηκευτικού Χώρου Μαθήματος";
$langQuotaSuccess = "Η αλλαγή έγινε με επιτυχία";
$langQuotaFail = "Η αλλαγή δεν έγινε!";
$langMaxQuota = "έχει μέγιστο επιτρεπτό αποθηκευτικό χώρο";
$langLegend = "Για το υποσύστημα";
$langVideo = "Βίντεο";

// Added by vagpits
// General
$langReturnToSearch = "Επιστροφή στα αποτελέσματα αναζήτησης";
$langReturnSearch = "Επιστροφή στην αναζήτηση";
$langNoChangeHappened = "Δεν πραγματοποιήθηκε καμία αλλαγή!";

// addfaculte
$langFaculteCatalog = "Κατάλογος Σχολών";
$langManyExist = "Υπάρχουν";
$langReturnToAddFaculte = "Επιστροφή στην προσθήκη";
$langReturnToEditFaculte = "Επιστροφή στην επεξεργασία";
$langFaculteAdd = "Προσθήκη $langOfFaculty";
$langFaculteDel = "Διαγραφή $langOfFaculty";
$langFaculteEdit = "Επεξεργασία στοιχείων $langOfFaculty";
$langFaculteIns = "Εισαγωγή Στοιχείων $langOfFaculty";
$langAcceptChanges = "Επικύρωση Αλλαγών";
$langEditFacSucces = "Η επεξεργασία του μαθήματος ολοκληρώθηκε με επιτυχία!";

// addusertocours
$langQuickAddDelUserToCoursSuccess = "Η διαχείριση χρηστών ολοκληρώθηκε με επιτυχία!";
$langFormUserManage = "Φόρμα Διαχείρισης Χρηστών";
$langListNotRegisteredUsers = "Λίστα Μη Εγγεγραμμένων Χρηστών";
$langListRegisteredStudents = "Λίστα Εγγεγραμμένων ".$langOfStudents;
$langListRegisteredProfessors = "Λίστα Εγγεγραμμένων ".$langOfTeachers;
$langErrChoose = "Παρουσιάστηκε σφάλμα στην επιλογή μαθήματος!";
// delcours
$langCourseDel = "Διαγραφή μαθήματος";
$langCourseDelSuccess = "Το μάθημα διαγράφηκε με επιτυχία!";
$langCourseDelConfirm = "Επιβεβαίωση Διαγραφής Μαθήματος";
$langCourseDelConfirm2 = "Θέλετε σίγουρα να διαγράψετε το μάθημα με κωδικό";
$langNoticeDel = "ΣΗΜΕΙΩΣΗ: Η διαγραφή του μαθήματος θα διαγράψει επίσης τους εγγεγραμμένους ".$langsOfStudentss." από το μάθημα, την αντιστοιχία του μαθήματος στη $langsFaculty, καθώς και όλο το υλικό του μαθήματος.";

// editcours
$langCourseEdit = "Επεξεργασία Μαθήματος";
$langCourseInfo = "Στοιχεία Μαθήματος";
$langQuota = "Όρια αποθηκευτικού χώρου";
$langCourseStatus = "Κατάσταση Μαθήματος";
$langCurrentStatus = "Τρέχουσα κατάσταση";
$langListUsers = "Λίστα Χρηστών";
$langCourseDelFull = "Διαγραφή Μαθήματος";
$langTakeBackup = "Λήψη Αντιγράφου Ασφαλείας";
$langStatsCourse = "Στατιστικά Μαθήματος";

// infocours.php
$langCourseEditSuccess = "Τα στοιχεία του μαθήματος άλλαξαν με επιτυχία!";
$langCourseInfoEdit = "Αλλαγή Στοιχείων Μαθήματος";
$langBackCourse = "Επιστροφή στην αρχική σελίδα του μαθήματος";

// listreq.php
$langOpenProfessorRequests = "Ανοικτές Αιτήσεις ".$langOfTeachers;
$langProfessorRequestClosed = "Η αίτηση του ".$langsOfTeacher." έκλεισε!";
$langReqHaveClosed = "Αιτήσεις που έχουν κλείσει";
$langReqHaveBlocked = "Αιτήσεις που έχουν απορριφθεί";
$langReqHaveFinished = "Αιτήσεις που έχουν ολοκληρωθεί";
$langemailsubjectBlocked = "Απόρριψη αίτησης εγγραφής στην Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης";
$langemailbodyBlocked = "Η αίτησή σας για εγγραφή στην πλατφόρμα ".$siteName." απορρίφθηκε.";
$langCloseConf = "Επιβεβαίωση κλεισίματος αίτησης";
$langReintroductionApplication="Η επαναφορά της αίτησης ολοκληρώθηκε με επιτυχία!";
$langBackRequests = "Επιστροφή στις αιτήσεις";

// mailtoprof.php
$langSendMessageTo = "Αποστολή μηνύματος";
$langToAllUsers = "σε όλους τους χρήστες";
$langProfOnly = "μόνο στους ".$langsTeachers." ";

// searchcours.php
$langSearchCourse = "Αναζήτηση Μαθημάτων";
$langNewSearch = "Νέα Αναζήτηση";
$langSearchCriteria = "Κριτήρια Αναζήτησης";
$langSearch = "Αναζήτηση";

// statuscours.php
$langCourseStatusChangedSuccess = "Ο τύπος πρόσβασης του μαθήματος άλλαξε με επιτυχία!";
$langCourseStatusChange = "Αλλαγή τύπου πρόσβασης μαθήματος";

// authentication
$langMethods = "Ενεργοί τρόποι πιστοποίησης:";
$langActivate = "Ενεργοποίηση";
$langDeactivate = "Απενεργοποίηση";
$langChooseAuthMethod = "Επιλέξτε τον τρόπο πιστοποίησης χρηστών και καθορίστε τις ρυθμίσεις του";
$langConnYes = "ΕΠΙΤΥΧΗΣ ΣΥΝΔΕΣΗ";
$langConnNo = "H ΣΥΝΔΕΣΗ ΔΕΝ ΛΕΙΤΟΥΡΓΕΙ!";
$langAuthNoValidUser = "Τα στοιχεία του χρήστη δεν είναι σωστά. Η εγγραφή δεν πραγματοποιήθηκε.";
$langConnTest = "Γίνεται δοκιμή του τρόπου πιστοποίησης...";
$langAuthMethod = "Τρόπος πιστοποίησης χρηστών";
$langdbhost = "Εξυπηρέτης Βάσης Δεδομένων";
$langdbname = "Όνομα Βάσης Δεδομένων";
$langdbuser = "Χρήστης Βάσης Δεδομένων";
$langdbpass = "Συνθηματικό χρήστη Βάσης Δεδομένων";
$langdbtable = "Όνομα πίνακα Βάσης Δεδομένων";
$langdbfielduser = "Όνομα πεδίου Χρήστη στον πίνακα";
$langdbfieldpass = "Όνομα πεδίου Συνθηματικού Χρήστη στον πίνακα";
$langInstructionsAuth = "Οδηγίες διασύνδεσης και χρήσης";
$langTestAccount = "Για να ενεργοποιηθεί ο τρόπος πιστοποίησης είναι απαραίτητο να κάνετε μια δοκιμαστική χρήση με ένα λογαριασμό της μεθόδου που επιλέξατε";
$langpop3host = "Εξυπηρέτης POP3";
$langpop3port = "Πόρτα λειτουργίας POP3";
$langimaphost = "Εξυπηρέτης IMAP";
$langimapport = "Πόρτα λειτουργίας IMAP";
$langldap_host_url = "Εξυπηρέτης LDAP";
$langldap_bind_dn = "Ορίσματα για LDAP binding";
$langldap_bind_user = "Όνομα Χρήστη για LDAP binding";
$langldap_bind_pw = "Συνθηματικό για LDAP binding";
$langUserAuthentication = "Πιστοποίηση Χρηστών";
$langChangeUser = 'Σύνδεση με λογαριασμό άλλου χρήστη';
$langChangeUserNotFound = 'Δε βρέθηκε λογαριασμός με όνομα χρήστη "%s"';
$langMultiRegUser = 'Μαζική δημιουργία λογαριασμών χρηστών';
$langMultiRegUserInfo = "<p>Εισαγάγετε στην παρακάτω περιοχή έναν κατάλογο με
τα στοιχεία των χρηστών, μία γραμμή ανα χρήστη που επιθυμείτε να δημιουργηθεί.
</p>
<p>Η σειρά των στοιχείων ορίζεται στο πεδίο που βρίσκεται πριν την
περιοχή, και οι δυνατές ετικέτες είναι οι εξής:</p>
<ul>
<li><tt>first</tt>: Όνομα</li>
<li><tt>last</tt>: Επώνυμο</li>
<li><tt>email</tt>: Διεύθυνση e-mail</li>
<li><tt>id</tt>: Αριθμός μητρώου</li>
<li><tt>phone</tt>: Τηλέφωνο</li>
<li><tt>username</tt>: Όνομα χρήστη</li>
</ul>
<p>Αν επιθυμείτε οι χρήστες να εγγραφούν αυτόματα σε κάποια μαθήματα, προσθέστε
τους κωδικούς τους στο τέλος της γραμμής μετά τα πεδία που έχετε ορίσει. Το
e-mail είναι προαιρετικό - αν θέλετε να το παραλείψετε, βάλτε στη θέση του μια
παύλα (-). Οι γραμμές που αρχίζουν από # αγνοούνται. Αν δεν ορίσετε το όνομα
χρήστη των χρηστών, θα πάρουν αυτόματα ονόματα χρήστη αποτελούμενα από το
πρόθεμα που μπορείτε να ορίσετε παρακάτω και έναν αύξοντα αριθμό.</p>";
$langMultiRegCourseInvalid = 'Χρήστης %s: μη έγκυρος κωδικός μαθήματος «%s»';
$langMultiRegFields = 'Ορισμός πεδίων';
$langMultiRegFieldError = 'Σφάλμα! Μη επιτρεπτή ετικέτα πεδίου:';
$langMultiRegType = 'Δημιουργία λογαριασμών';
$langMultiRegSendMail = 'Αποστολή των στοιχείων των χρηστών μέσω e-mail';
$langMultiRegPrefix = 'Πρόθεμα username χρηστών';
$langSearchCourses = "Αναζήτηση μαθημάτων";
$langActSuccess = "Μόλις ενεργοποιήσατε την ";
$langDeactSuccess = "Μόλις απενεργοποιήσατε την ";
$langThe = "Η ";
$langActFailure = "διότι δεν έχετε καθορίσει τις ρυθμίσεις του";
$langLdapNotWork = "Προειδοποίση: Η php δεν έχει υποστήριξη για ldap. Βεβαιωθείτε ότι η ldap υποστήριξη είναι εγκατεστημένη και ενεργοποιημένη.";
$langShibEmail = "Shibboleth Email";
$langShibUsername = "Shibboleth User Name";
$langShibCn = "Shibboleth Canonical Name";
$langShibboleth = "Πιστοποίηση μέσω Shibboleth";

// other
$langVisitors = "Επισκέπτες";
$langVisitor = "Επισκέπτης";
$langOther = "Άλλο";
$langTotal = "Σύνολο";
$langProperty = "Ιδιότητα";
$langStat = "Στατιστικά";
$langNoUserList = "Δεν υπάρχουν αποτελέσματα πρός εμφάνιση";
$langContactAdmin = "Αποστολή ενημερωτικού email στον Διαχειριστή";
$langActivateAccount = "Παρακαλώ να ενεργοποιήσετε το λογαριασμό μου";
$langLessonCode = "Συνθηματικό μαθήματος";

// unregister
$langConfirmDelete = "Επιβεβαίωση διαγραφής ";
$langConfirmDeleteQuestion1 = "Θέλετε σίγουρα να διαγράψετε τον χρήστη";
$langConfirmDeleteQuestion2 = "από το μάθημα με κωδικό";
$langTryDeleteAdmin = "Προσπαθήσατε να διαγράψετε τον χρήστη με user id = 1(Admin)!";
$langUserWithId = "Ο χρήστης με id";
$langWasDeleted = "διαγράφηκε";
$langWasAdmin = "ήταν διαχειριστής";
$langWasCourseDeleted = "διαγράφηκε από το Μάθημα";
$langErrorDelete = "Σφάλμα κατά τη διαγραφή του χρήστη";
$langAfter = "Μετά από";
$langBefore = "Πρίν από";
$langUserType = "Τύπος χρήστη";

// search
$langSearchUsers = "Αναζήτηση Χρηστών";
$langInactiveUsers = "Μη ενεργοί χρήστες";
$langAddSixMonths = "Προσθήκη χρόνου: 6 μήνες";

// eclassconf
$langRestoredValues = "Επαναφορά προηγούμενων τιμών";
$langEclassConf = "Αρχείο ρυθμίσεων του $siteName";
$langFileUpdatedSuccess = "Το αρχείο ρυθμίσεων τροποποιήθηκε με επιτυχία!";
$langFileEdit = "Επεξεργασία Αρχείου";
$langFileError = "Το αρχείο config.php δεν μπόρεσε να διαβαστεί! Ελέγξτε τα δικαιώματα πρόσβασης.";
$langReplaceBackupFile = "Αντικατάσταση του config_backup.php.";
$langencryptedPasswd = "Υποχρεωτική κρυπτογράφηση των συνθηματικών";

// admin announcements
$langAdminAn = "Ανακοινώσεις Διαχειριστή";
$langAdminAddAnn = "Προσθήκη ανακοίνωσης διαχειριστή";
$langAdminModifAnn = "Τροποποίηση ανακοίνωσης διαχειριστή";
$langAdminAnnModify = "Η ανακοίνωση διαχειριστή τροποποιήθηκε";
$langAdminAnVis = "Ορατή";
$langAdminAnnAdd = "Η ανακοίνωση διαχειριστή προστέθηκε";
$langAdminAnnDel = "Η ανακοίνωση διαχειριστή διαγράφηκε";
$langAdminAnnMes = "τοποθετήθηκε την";
$langAdminAnnTitleEn = "Τίτλος (Αγγλικά)";
$langAdminAnnBodyEn = "Ανακοίνωση (Αγγλικά)";
$langAdminAnnCommEn = "Σχόλια (Αγγλικά)";

// cleanup.php
$langCleanupOldFiles = 'Εκκαθάριση παλαιών αρχείων';
$langCleaningUp = 'Εκκαθάριση αρχείων παλαιότερων από %s %s στον υποκατάλογο %s';
$langDaySing = 'ημέρα';
$langDayPlur = 'ημέρες';
$langCleanupInfo = 'Η λειτουργία αυτή θα διαγράψει τα παλιά αρχεία από τους υποκαταλόγους "temp", "archive", "garbage", και"tmpUnzipping". Είστε βέβαιοι?';
$langCleanup = 'Εκκαθάριση';

/**********************************************************
* agenda.inc.php
**********************************************************/
$langModify="Αλλαγή";
$langAddModify="Προσθήκη / Αλλαγή";
$langAddIntro="Προσθήκη Εισαγωγικού Κειμένου";
$langBackList="Επιστροφή στον κατάλογο";
$langEvents="Γεγονότα";
$langAgenda="Ατζέντα";
$langDay="Μέρα";
$langMonth="Μήνας";
$langYear="Έτος";
$langHour="Ώρα";
$langHours = "Ώρες";
$langMinute ="Λεπτά";
$langsecond = 'δευτερόλεπτο';
$langseconds = 'δευτερόλεπτα';
$langminute = 'λεπτό';
$langminutes = 'λεπτά';
$langhour = 'ώρα';
$langhours = 'ώρες';
$langLasting="Διάρκεια";
$langDateNow = "Σημερινή ημερομηνία:";
$langCalendar = "Ημερολόγιο";
$langAddEvent="Προσθήκη ενός γεγονότος";
$langDetail="Λεπτομέρειες";
$langChooseDate = "Επιλέξτε Ημερομηνία";
$langOldToNew = "Αντιστροφή σειράς παρουσίασης";
$langStoredOK="Το γεγονός αποθηκεύτηκε";
$langDeleteOK="Το γεγονός διαγράφηκε";
$langNoEvents = "Δεν υπάρχουν γεγονότα";
$langSureToDel = "Είστε σίγουρος ότι θέλετε να διαγράψετε το γεγονός με τίτλο";
$langDelete = "Διαγραφή";
$langInHour = "(σε ώρες)";
$langEmptyAgendaTitle = "Παρακαλώ πληκτρολογήστε τον τίτλο του γεγονότος";
$langAgendaNoTitle = "Γεγονός χωρίς τίτλο";

// week days
$langDay_of_weekNames = array();
$langDay_of_weekNames['init'] = array('Κ', 'Δ', 'Τ', 'Τ', 'Π', 'Π', 'Σ');
$langDay_of_weekNames['short'] = array('Κυρ', 'Δευ', 'Τρι', 'Τετ', 'Πεμ', 'Παρ', 'Σαβ');
$langDay_of_weekNames['long'] = array('Κυριακή', 'Δευτέρα', 'Τρίτη', 'Τετάρτη', 'Πέμπτη', 'Παρασκευή', 'Σάββατο');

// month names
$langMonthNames = array();
$langMonthNames['init']  = array('Ι', 'Φ', 'Μ', 'Α', 'Μ', 'Ι', 'Ι', 'Α', 'Σ', 'Ο', 'Ν', 'Δ');
$langMonthNames['short'] = array('Ιαν', 'Φεβ', 'Μαρ', 'Απρ', 'Μάι', 'Ιουν', 'Ιουλ', 'Αυγ', 'Σεπ', 'Οκτ', 'Νοε', 'Δεκ');
$langMonthNames['long'] = array('Ιανουάριος', 'Φεβρουάριος', 'Μάρτιος', 'Απρίλιος', 'Μάιος', 'Ιούνιος', 'Ιούλιος', 'Αύγουστος', 'Σεπτέμβριος', 'Οκτώβριος', 'Νοέμβριος', 'Δεκέμβριος');
$langMonthNames['fine'] = array('Ιανουαρίου',
        'Φεβρουαρίου',
        'Μαρτίου',
        'Απριλίου',
        'Μαΐου',
        'Ιουνίου',
        'Ιουλίου',
        'Αυγούστου',
        'Σεπτεμβρίου',
        'Οκτωβρίου',
        'Νοεμβρίου',
        'Δεκεμβρίου');

/***********************************************************
* announcements.inc.php
************************************************************/
$langOn="Σε";
$langRegUser="εγγεγραμμένους χρήστες του μαθήματος";
$langUnvalid="έχουν άκυρη διεύθυνση email ή δεν έχουν καθόλου";
$langModifAnn="Αλλαγή της ανακοίνωσης";
$langAnnouncement = "Ανακοίνωση";
$langMove = "Μετακίνηση";
$langAnnEmpty="Όλες οι ανακοινώσεις διαγράφηκαν";
$langAnnModify="η ανακοίνωση άλλαξε";
$langAnnAdd="Η ανακοίνωση προστέθηκε";
$langAnnDel="η ανακοίνωση διαγράφηκε";
$langPubl="αναρτήθηκε την";
$langAddAnn="Προσθήκη Ανακοίνωσης";
$langContent="Περιεχόμενο";
$langAnnTitle = "Τίτλος Ανακοίνωσης";
$langAnnBody = "Σώμα Ανακοίνωσης";
$langEmptyAnn="Διαγραφή ανακοινώσεων";
$professorMessage="Μήνυμα $langsOfTeacher";
$langEmailSent=" και στάλθηκε στους εγγεγραμμένους χρήστες";
$langEmailOption="Αποστολή (με email) της ανακοίνωσης στους εγγεγραμμένους χρήστες";
$langUp = "Επάνω";
$langDown = "Κάτω";
$langNoAnnounce = "Δεν υπάρχουν ανακοινώσεις";
$langSureToDelAnnounce = "Είστε σίγουρος ότι θέλετε να διαγράψετε την ανακοίνωση";
$langSureToDelAnnounceAll = "Είστε σίγουρος ότι θέλετε να διαγράψετε όλες τις ανακοινώσεις";
$langAnn = "Ανακοινώθηκε την";
$langEmptyAnTitle = "Παρακαλώ πληκτρολογήστε τον τίτλο της ανακοίνωσης";
$langAnnouncementNoTille = "Ανακοίνωση χωρίς τίτλο";
$langCourseAnnouncements = "Ανακοινώσεις μαθήματος";

/*******************************************
* archive_course.inc.php
*******************************************/
$langArchiveCourse = "Αντίγραφο Ασφαλείας";
$langCreatedIn = "δημιουργήθηκε την";
$langCreateDirMainBase ="Δημιουργία του καταλόγου για την ανάκτηση της κεντρικής βάσης";
$langCreateDirCourseBase ="Δημιουργία του καταλόγου για την ανάκτηση των βάσεων των μαθημάτων";
$langCopyDirectoryCourse = "Αντιγραφή των αρχείων του μαθήματος";
$langDisk_free_space = "Ελεύθερος χώρος";
$langBuildTheCompressedFile ="Δημιουργία του αρχείου αντίγραφου ασφαλείας";
$langFileCopied = "αρχεία αντιγράφηκαν";
$langArchiveLocation="Τοποθεσία";
$langSizeOf ="Μέγεθος του";
$langBackupSuccesfull = "Δημιουργήθηκε με επιτυχία το αντίγραφο ασφαλείας!";
$langBUCourseDataOfMainBase = "Αντίγραφο ασφαλείας των δεδομένων του μαθήματος";
$langBackupOfDataBase="Αντίγραφο ασφαλείας της βάσης δεδομένων του μαθήματος";
$langDownloadIt = "Κατεβάστε το";
$langBackupEnd = "Ολοκληρώθηκε το αντίγραφο ασφαλείας σε μορφή";

/*********************************************
* auth_methods.inc.php
**********************************************/
$langViaeClass = "μέσω πλατφόρμας";
$langViaPop = "με πιστοποίηση μέσω POP3";
$langViaImap = "με πιστοποίηση μέσω IMAP";
$langViaLdap = "με πιστοποίηση μέσω LDAP";
$langViaDB = "με πιστοποίηση μέσω άλλης Βάσης Δεδομένων";
$langViaShibboleth = "με πιστοποίηση μέσω Shibboleth";
$langHasActivate = "O τρόπος πιστοποίησης που επιλέξατε, έχει ενεργοποιηθεί";
$langAlreadyActiv = "O τρόπος πιστοποίησης που επιλέξατε, είναι ήδη ενεργοποιημένος";
$langErrActiv ="Σφάλμα! Ο τρόπος πιστοποίησης δεν μπορεί να ενεργοποιηθεί";
$langAuthSettings = "Ρυθμίσεις πιστοποίησης";
$langWrongAuth = "Πληκτρολογήσατε λάθος όνομα χρήστη / συνθηματικό";
$langExplainShib = "Πληκτρολογήστε τα ονόματα των μεταβλητών που επιστρέφει ο εξυπηρέτης Shibboleth. Οι μεταβλητές θα γραφτούν στο αρχείο <em>%ssecure/index.php</em>. Σημειώστε, ότι αν το Shibboleth Canonical Name περιλαμβάνει το ονοματεπώνυμο του χρήστη, τότε θα πρέπει να ορίσετε τον χαρακτήρα που χωρίζει το όνομα από το επώνυμο.";
$langCharSeparator = "Χαρακτήρας χωρισμού";

/************************************************************
 * conference.inc.php
 ******************************************************************/

$langConference = "Τηλεσυνεργασία";
$langWash = "Καθάρισμα";
$langWashFrom = "Η κουβέντα καθάρισε από";
$langSave = "Αποθήκευση";
$langClearedBy = "καθαρισμός από";
$langChatError = "Δεν είναι δυνατόν να ξεκινήσει η Ζωντανή Τηλεσυνεργασία";
$langsetvideo="Σύνδεσμος παρουσίασης βίντεο";
$langButtonVideo="Μετάδοση";
$langButtonPresantation="Μετάδοση";
$langconference="Ενεργοποίηση τηλεδιάσκεψης";
$langpresantation="Σύνδεσμος παρουσίασης ιστοσελίδας";
$langVideo_content="<p align='justify'>Εδώ θα παρουσιαστεί το βίντεο αφού το ενεργοποιήσει ο $langsTeacher.</p>";
$langTeleconference_content1 = "<p align='justify'>Εδώ θα παρουσιαστεί η τηλεδιάσκεψη αφού την ενεργοποιήσει ο $langsTeacher.</p>";
$langTeleconference_content_noIE="<p align='justify'>Η τηλεδιάσκεψη ενεργοποιείται μόνο αν έχετε IE ως πλοηγό.</p>";
$langWashVideo="Παύση μετάδοσης";
$langPresantation_content="<p align='center'>Εδώ θα παρουσιαστεί μία ιστοσελίδα που θα επιλέξει ο $langsTeacher.</p>";
$langWashPresanation="Παύση μετάδοσης";
$langSaveChat="Αποθήκευση κουβέντας";
$langSaveMessage="Η κουβέντα αποθηκεύθηκε στα Έγγραφα";
$langSaveErrorMessage="Η κουβέντα δεν μπόρεσε να αποθηκευθεί";
$langNoAliens = "Μόνο οι εγγεγραμμένοι χρήστες στην πλατφόρμα μπορούν να χρησιμοποιούν το υποσύστημα 'Κουβέντα' !";
$langNoGuest = "Οι χρήστες - επισκέπτες δεν μπορούν να χρησιμοποιήσουν το υποσύστημα 'Κουβέντα' !";

/*****************************************************************
* copyright.inc.php
******************************************************************/
$langCopyright = "Πληροφορίες Πνευματικών Δικαιωμάτων";
$langCopyrightNotice = '
Copyright © 2003 - 2010 <a href="http://www.openeclass.org" target=_blank>Open eClass</a>.<br>&nbsp;<br>
Η πλατφόρμα '.$siteName.' βασίζεται στην ανοικτή πλατφόρμα <a href="http://www.openeclass.org" target=_blank>Open eClass</a>
η οποία είναι ένα ολοκληρωμένο Σύστημα Διαχείρισης Ηλεκτρονικών Μαθημάτων και αποτελεί
την πρόταση του Ακαδημαϊκού Διαδικτύου GUnet για την υποστήριξη της Υπηρεσίας
Ασύγχρονης Τηλεκπαίδευσης. Aναπτύχθηκε και υποστηρίζεται ενεργά από την Ομάδα
Ασύγχρονης Τηλεκπαίδευσης του GUnet και <a
href="http://download.eclass.gunet.gr" target="_blank">διανέμεται ελεύθερα</a>
ως Λογισμικό Ανοικτού Κώδικα σύμφωνα με τη γενική δημόσια άδεια GNU General
Public License (GNU GPL).<br><br>
Το περιεχόμενο των Ηλεκτρονικών Μαθημάτων που φιλοξενεί η πλατφόρμα '.$siteName.', καθώς και τα πνευματικά δικαιώματα του υλικού αυτού, ανήκουν στους συγγραφείς τους και το GUnet δεν διεκδικεί δικαιώματα σε αυτό. Για οποιαδήποτε χρήση ή αναδημοσίευση του περιεχομένου παρακαλούμε επικοινωνήστε με τους υπεύθυνους των αντίστοιχων Mαθημάτων.
';

/*******************************************************
* course_description.inc.php
*******************************************************/
$langCourseProgram = "Περιγραφή Μαθήματος";
$langThisCourseDescriptionIsEmpty = "Το μάθημα δεν διαθέτει περιγραφή";
$langEditCourseProgram = "Δημιουργία και διόρθωση";
$langQuestionPlan = "Ερώτηση στον διδάσκοντα";
$langInfo2Say = "Πληροφορία για τους ".$langsOfStudentss;
$langAddCat = "Κατηγορία";
$langBackAndForget ="Ακύρωση και επιστροφή";
$langBlockDeleted = "Η περιγραφή διαγράφηκε!";

/********************************************************
* course_home.inc.php
*********************************************************/
$langAdminOnly="Μόνο για Διαχειριστές";
$langInLnk="Απενεργοποιημένοι σύνδεσμοι";
$langDelLk="Θέλετε πραγματικά να διαγράψετε αυτόν τον σύνδεσμο ?";
$langRemove="διαγραφή";
$langEnter ="Είσοδος";
$langUpdate = "Αλλαγή Τίτλου";
$langIcon = "Εικονίδιο";
$langNameOfTheLink ="Όνομα Συνδέσμου";
$langRegistered = "εγγεγραμμένοι";
$langOneRegistered = "εγγεγραμμένος";
$langIdentity = "Ταυτότητα Μαθήματος";
$langCourseS = "μάθημα";

/*********************************************
* course_info.inc.php
*********************************************/
$langCourseIden = "Ταυτότητα Μαθήματος";
$langBackupCourse="Αντίγραφο ασφαλείας του μαθήματος";
$langModifInfo="Διαχείριση Μαθήματος";
$langModifDone="Η πληροφορία έχει αλλάξει";
$langHome="Επιστροφή στην αρχική σελίδα";
$langCode="Κωδικός";
$langDelCourse="Διαγραφή του μαθήματος";
$langDelUsers="Διαγραφή χρηστών από το μάθημα";
$langCourseTitle="Τίτλος Μαθήματος";
$langDescription="Περιγραφή";
$langConfidentiality="Πρόσβαση στο μάθημα";
$langPrivOpen="Ελεύθερη Πρόσβαση (με εγγραφή) σε όσους διαθέτουν λογαριασμό στην πλατφόρμα";
$langForbidden="Μη επιτρεπτή ενέργεια";
$langConfTip="Επιλέξτε τον τύπο πρόσβασης του μαθήματος από τους χρήστες.";
$langOptPassword = "Προαιρετικό συνθηματικό: ";
$langNoCourseTitle = "Δεν πληκτρολογήσατε τον τίτλο του μαθήματος";

// delete_course.php
$langModifGroups="Ομάδες Εργασίας";
$langTheCourse="Tο μάθημα";
$langHasDel="έχει διαγραφεί";
$langByDel="Διαγράφοντας το μάθημα θα διαγραφούν μόνιμα όλα τα περιεχόμενα του και όλοι οι ".$langsStudents." που είναι γραμμένοι σε αυτό (δεν θα διαγραφούν από τα άλλα μαθήματα).";
$langByDel_A="Θέλετε πράγματι να διαγράψετε το μάθημα:";
$langTipLang="Επιλέξτε την γλώσσα στην οποία θα εμφανίζονται τα μηνύματα του μαθήματος.";
$langTipLang2="Επιλέξτε την γλώσσα στην οποία θα εμφανίζονται τα μηνύματα της πλατφόρμας.";

// deluser_course.php
$langConfirmDel = "Επιβεβαίωση διαγραφής μαθήματος";
$langUserDel="Πρόκειται να διαγράψετε όλους τους ".$langsOfStudentss." από το μάθημα (δεν θα διαγραφούν από τα άλλα μαθήματα).<p>Θέλετε πράγματι να προχωρήσετε στη διαγραφή τους από το μάθημα";

// refresh course.php
$langRefreshCourse = "Ανανέωση μαθήματος";
$langRefreshInfo="Προκειμένου να προετοιμάσετε το μάθημα για μια νέα ομάδα ".$langsOfStudents." μπορείτε να διαγράψετε το παλιό περιεχόμενο.";
$langRefreshInfo_A="Επιλέξτε ποιες ενέργειες θέλετε να πραγματοποιηθούν";
$langUserDelCourse="Διαγραφή χρηστών από το μάθημα";
$langUserDelNotice = "Σημ.: Οι χρήστες δεν θα διαγραφούν από άλλα μαθήματα";
$langAnnouncesDel = "Διαγραφή ανακοινώσεων του μαθήματος";
$langAgendaDel = "Διαγραφή εγγραφών από την ατζέντα του μαθήματος";
$langHideDocuments = "Απόκρυψη των εγγράφων του μαθήματος";
$langHideWork = "Απόκρυψη των εργασιών του μαθήματος";
$langSubmitActions = "Εκτέλεση ενεργειών";
$langOptions = "Επιλογές";
$langRefreshSuccess = "Η ανανέωση του μαθήματος ήταν επιτυχής. Εκτελέστηκαν οι ακόλουθες ενέργειες:";
$langUsersDeleted="Οι χρήστες διαγράφηκαν από το μάθημα";
$langAnnDeleted="Οι ανακοινώσεις διαγράφηκαν από το μάθημα";
$langAgendaDeleted="Οι εγγραφές της ατζέντας διαγράφηκαν από το μάθημα";
$langWorksDeleted="Οι εργασίες απενεργοποιήθηκαν";
$langDocsDeleted="Τα έγγραφα απενεργοποιήθηκαν";

/*****************************************************
* contact.inc.php
******************************************************/
$langContactProf = "Επικοινωνία με τους $langsTeachers";
$langEmailEmpty = "Η διεύθυνση ηλεκτρονικού ταχυδρομείου σας είναι κενή.
Για να μπορείτε να επικοινωνήσετε με τον $langsOfTeacher, θα πρέπει να έχετε ορίσει
τη διεύθυνσή σας, ώστε να μπορείτε να λάβετε απάντηση. Μπορείτε να ορίσετε τη
διεύθυνσή σας από την επιλογή <a href='%s'>«Αλλαγή του προφίλ μου»</a> στη
σελίδα του χαρτοφυλακίου σας.";

$langEmptyMessage = "Αφήσατε το κείμενο του μηνύματος κενό. Το μήνυμα δε στάλθηκε";
$langSendMessage = "Αποστολή μηνύματος";
$langContactMessage = "Επικοινωνήστε με τους υπεύθυνους $langsTeachers του μαθήματος.
Εισάγετε το κείμενο του μηνύματός σας:";

$langSendingMessage = "Το μήνυμά σας αποστέλλεται προς:";
$langErrorSendingMessage = "Σφάλμα αποστολής! Το μήνυμα δε στάλθηκε.";
$langContactIntro = "Ο χρήστης της πλατφόρμας $siteName με όνομα %s
και διεύθυνση ηλεκτρονικού ταχυδρομείου <%s> σας έστειλε
το παρακάτω μήνυμα. Αν απαντήσετε στο μήνυμα αυτό, η απάντησή
σας θα σταλεί στον παραπάνω χρήστη.

%s
";

$langNonUserContact = "Για να επικοινωνήσετε με τους υπεύθυνους $langsTeachers
του μαθήματος, θα πρέπει να έχετε λογαριασμό στο σύστημα και
να έχετε συνδεθεί. Παρακαλούμε επισκεφθείτε την <a href='%s'>αρχική σελίδα</a>.";
$langIntroMessage = "Σύνταξη μηνύματος";
$langHeaderMessage = "Μήνυμα από $langsstudent_acc";


/****************************************************
* create_course.inc.php
*****************************************************/
$langDescrInfo="Σύντομη περιγραφή του μαθήματος";
$langFieldsRequ="Όλα τα πεδία είναι υποχρεωτικά!";
$langFieldsOptional = "Προαιρετικά πεδία";
$langFieldsOptionalNote = "Σημ. μπορείτε να αλλάξετε οποιεσδήποτε από τις πληροφορίες αργότερα";
$langEx="π.χ. <i>Ιστορία της Τέχνης</i>";
$langDivision = "Τομέας";
$langTargetFac="Η $langsFaculty που υπάγεται το μάθημα";
$langDoubt="Αν δεν ξέρετε το κωδικό του μαθήματος συμβουλευτείτε";
$langExFac = "* Αν επιθυμείτε να δημιουργήσετε μάθημα, σε άλλη $langsFaculty από αυτό που ανήκετε, τότε επικοινωνήστε με
την Ομάδα Ασύγχρονης Τηλεκπαίδευσης";
$langEmptyFields="Αφήσατε μερικά πεδία κενά!";
$langCreate="Δημιουργία";
$langCourseKeywords = "Λέξεις Κλειδιά:";
$langCourseAddon = "Συμπληρωματικά Στοιχεία:";
$langErrorDir = "Ο υποκατάλογος του μαθήματος δεν δημιουργήθηκε και το μάθημα δεν θα λειτουργήσει!<br><br>Ελέγξτε τα δικαιώματα πρόσβασης του καταλόγου <em>courses</em>.";
$langSubsystems="Επιλέξτε τα υποσυστήματα που θέλετε να ενεργοποιήσετε για το νέο σας μάθημα:";
$langLanguageTip="Επιλέξτε σε ποια γλώσσα θα εμφανίζονται οι σελίδες του μαθήματος";
$langAccess = "Τύπος Πρόσβασης:";
$langAvailableTypes = "Διαθέσιμοι τύποι πρόσβασης";
$langModules = "Υποσυστήματα:";
$langTestForum="Γενικές συζητήσεις";
$langDelAdmin="Περιοχή συζητήσεων για κάθε θέμα που αφορά το μάθημα";
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
$langDoc="Έγγραφα";
$langVideoLinks="Βιντεοσκοπημένα Μαθήματα";
$langWorks="Εργασίες";
$langForums="Περιοχές Συζητήσεων";
$langExercices="Ασκήσεις";
$langAddPageHome="Ανέβασμα Ιστοσελίδας";
$langLinkSite="Προσθήκη συνδέσμου στην αρχική σελίδα";
$langModifyInfo= "Διαχείριση Μαθήματος";
$langDropBox = "Ανταλλαγή Αρχείων";
$langLearnPath = "Γραμμή Μάθησης";
$langWiki = "Σύστημα Wiki";
$langToolManagement = "Ενεργοποίηση Εργαλείων";
$langUsage = "Στατιστικά Χρήσης";
$langStats = "Στατιστικά";
$langVideoText="Παράδειγμα ενός αρχείου RealVideo. Μπορείτε να ανεβάσετε οποιοδήποτε τύπο αρχείου βίντεο (.mov, .rm, .mpeg...), εφόσον οι ".$langsStudents." έχουν το αντίστοιχο plug-in για να το δούν";
$langGoogle="Γρήγορη και Πανίσχυρη μηχανής αναζήτησης";
$langIntroductionText="Εισαγωγικό κείμενο του μαθήματος. Αντικαταστήτε το με το δικό σας, κάνοντας κλίκ στην <b>Αλλαγή</b>.";
$langJustCreated="Μόλις δημιουργήσατε με επιτυχία το μάθημα με τίτλο ";

 // Groups
$langCreateCourseGroups="Ομάδες Χρηστών";
$langCatagoryMain="Αρχή";
$langCatagoryGroup="Συζήτησεις Ομάδων χρηστών";
$langNoGroup="Δεν έχουν οριστεί ομάδες χρηστών";

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
$langNextStep="Επόμενο βήμα";
$langPreviousStep="Προηγούμενο βήμα";
$langFinalize="Δημιουργία μαθήματος!";
$langCourseCategory="Η κατηγορία στην οποία ανήκει το μάθημα";
$langProfessorsInfo="Ονοματεπώνυμα $langsOfTeachers του μαθήματος χωρισμένα με κόμματα (π.χ.<i>Νίκος Τζικόπουλος, Κώστας Αδαμόπουλος</i>)";
$langPublic="Ελεύθερη Πρόσβαση (χωρίς εγγραφή) από τη αρχική σελίδα χωρίς συνθηματικό";
$langPrivate="Πρόσβαση στο μάθημα (για εγγραφή) έχουν μόνο όσοι βρίσκονται στη Λίστα Χρηστών του μαθήματος";
$langPrivate_1="Πρόσβαση στο μάθημα";
$langPrivate_2="μόνο όσοι βρίσκονται στη Λίστα Χρηστών του μαθήματος (με εγγραφή)";
$langPrivate_3="ελεύθερη πρόσβαση (χωρίς εγγραφή)";
$langAlertTitle = "Παρακαλώ συμπληρώστε τον τίτλο του μαθήματος!";
$langAlertProf = "Παρακαλώ συμπληρώστε τον διδάσκοντα του μαθήματος!";

/******************************************************
* document.inc.php
******************************************************/
$langUpload = "Ανέβασμα";
$langDownloadFile= "Ανέβασμα αρχείου στον εξυπηρέτη";
$langPathUploadFile= "Εντοπισμός θέσης του αρχείου στον Η/Υ σας (τοπικά)";
$langCreateDir="Δημιουργία καταλόγου";
$langName="Όνομα";
$langNameDir="Όνομα νέου καταλόγου";
$langSize="Μέγεθος";
$langDate="Ημερομηνία";
$langMoveFrom = "Μετακίνηση του αρχείου";
$langRename="Μετονομασία";
$langOkComment="Επικύρωση αλλαγών";
$langVisible="Ορατό / Αόρατο";
$langCopy="Αντιγραφή";
$langNoSpace="Η αποστολή του αρχείου απέτυχε. Έχετε υπερβεί το μέγιστο επιτρεπτό χώρο. Για περισσότερες πληροφορίες, επικοινωνήστε με το διαχειριστή του συστήματος.";
$langUnwantedFiletype='Μη αποδεκτός τύπος αρχείου';
$langDownloadEnd="Το ανέβασμα ολοκληρώθηκε";
$langFileExists="Δεν είναι δυνατή η λειτουργία.<br>Υπάρχει ήδη ένα αρχείο με το ίδιο όνομα.";
$langDocCopied="Tο έγγραφο αντιγράφηκε";
$langDocDeleted="Το έγγραφο διαγράφηκε";
$langElRen="Η μετονομασία έγινε";
$langDirCr="Ο κατάλογος δημιουργήθηκε";
$langDirMv="Η μετακίνηση ολοκληρώθηκε";
$langComMod="Τα σχόλια τροποποιήθηκαν";
$langIn="στο";
$langNewDir="Όνομα του καινούριου καταλόγου";
$langImpossible="Δεν είναι δυνατή η λειτουργία";
$langViMod="Η ορατότητα του εγγράφου άλλαξε";
$langMoveOK="Η μεταφορά έγινε με επιτυχία!";
$langMoveNotOK="η μεταφορά δεν πραγματοποιήθηκε!";
$langRoot = "Αρχικός κατάλογος";
$langNoDocuments = "Δεν υπάρχουν έγγραφα";
$langNoRead = "Δεν έχετε δικαίωμα ανάγνωσης σε αυτή την περιοχή";

// Special for group documents
$langGroupSpace="Περιοχή ομάδας χρηστών";
$langGroupSpaceLink="Ομάδα χρηστών";
$langGroupForumLink="Περιοχή συζητήσεων ομάδας χρηστών";
$langZipNoPhp="Το αρχείο zip δεν πρέπει να περιέχει αρχεία .php";
$langUncompress="αποσυμπίεση του αρχείου (.zip) στον εξυπηρέτη";
$langDownloadAndZipEnd="Το αρχείο .zip ανέβηκε και αποσυμπιέστηκε";
$langPublish = "Δημοσίευση";
$langParentDir = "αρχικό κατάλογο";
$langInvalidDir = "Ακυρο ή μη υπαρκτό όνομα καταλόγου";
$langInvalidGroupDir = "Σφάλμα! Ο κατάλογος των ομάδων χρηστών δεν υπάρχει!";

//prosthikes gia v2 - metadata
$langCategory="Κατηγορία";
$langCreatorEmail="Ηλ. Διεύθυνση Συγγραφέα";
$langFormat="Τύπος-Κατηγορία";
$langSubject="Θέμα";
$langAuthor="Συγγραφέας";
$langCopyrighted="Πνευματικά Δικαιώματα";
$langCopyrightedFree="Ελεύθερο";
$langCopyrightedNotFree="Προστατευμένο";
$langCopyrightedUnknown="Άγνωστο";
$langChangeMetadata="Αλλαγή πληροφοριών εγγράφου";
$langEditMeta="Επεξεργασία<br>Πληροφοριών";
$langCategoryExcercise="Άσκηση";
$langCategoryEssay="Εργασία";
$langCategoryDescription="Περιγραφή μαθήματος";
$langCategoryExample="Παράδειγμα";
$langCategoryTheory="Θεωρία";
$langCategoryLecture="Διάλεξη";
$langCategoryNotes="Σημειώσεις";
$langCategoryOther="Άλλο";
$langNotRequired = "Η συμπλήρωση των πεδίων είναι προαιρετική";
$langCommands = "Ενέργειες";
$langQuotaBar = "Επισκόπηση αποθηκευτικού χώρου";
$langQuotaUsed = "Χρησιμοποιούμενος Χώρος";
$langQuotaTotal = "Συνολικός Διαθέσιμος Χώρος";
$langQuotaPercentage = "Ποσοστό χρήσης";
$langEnglish = "Αγγλικά";
$langFrench = "Γαλλικά";
$langGerman = "Γερμανικά";
$langGreek = "Ελληνικά";
$langItalian = "Ιταλικά";
$langSpanish = "Ισπανικά";
$langDirectory = "Κατάλογος";

/*************************************************
* dropbox.inc.php
*************************************************/
$dropbox_lang['dropbox'] = 'Χώρος Ανταλλαγής Αρχείων';
$dropbox_lang['help'] = 'Βοήθεια';
$dropbox_lang['aliensNotAllowed'] = "Μόνο οι εγγεγραμμένοι χρήστες στην πλατφόρμα μπορούν να χρησιμοποιούν το dropbox. Δεν είστε εγγεγραμμένος χρήστης στην πλατφόρμα.";
$dropbox_lang['queryError'] = "Πρόβλημα στην βάση δεδομένων. Παρακαλώ επικοινωνήστε με τον διαχειριστή της πλατφόρμας.";
$dropbox_lang['generalError'] = "Παρουσιάστηκε σφάλμα. Παρακαλούμε επικοινωνήστε με τον διαχειριστή της πλατφόρμας.";
$dropbox_lang['badFormData'] = "Η αποστολή του αρχείου απέτυχε: Τα δεδομένα ήταν με λάθος μορφή. Παρακαλούμε επικοινωνήστε με τον διαχειριστή της πλατφόρμας.";
$dropbox_lang['noUserSelected'] = "Παρακαλούμε επιλέξτε το χρήστη στον οποίο θέλετε να σταλεί το αρχείο.";
$dropbox_lang['noFileSpecified'] = "Δεν έχετε επιλέξει κάποιο αρχείο για να ανεβάσετε.";
$dropbox_lang['tooBig'] = "Δεν έχετε επιλέξει κάποιο αρχείο να ανεβάσετε ή το αρχείο υπερβαίνει το επιτρεπτό όριο σε μέγεθος.";
$dropbox_lang['uploadError'] = "Παρουσιάστηκε σφάλμα κατά το ανέβασμα του αρχείου. Παρακαλούμε επικοινωνήστε με τον διαχειριστή της πλατφόρμας.";
$dropbox_lang['errorCreatingDir'] = "Παρουσιάστηκε σφάλμα κατά τη δημιουργία καταλόγου. Παρακαλούμε επικοινωνήστε με τον διαχειριστή της πλατφόρμας.";
$dropbox_lang['quotaError'] = "Έχετε ξεπεράσει το μέγιστο συνολικό επιτρεπτό μέγεθος αρχείων! Το ανέβασμα του αρχείου δεν πραγματοποιήθηκε.";
$dropbox_lang['uploadFile'] = "Ανέβασμα αρχείου";
$dropbox_lang['authors'] = "Αποστολέας";
$dropbox_lang['description'] = "Περιγραφή αρχείου";
$dropbox_lang['sendTo'] = "Αποστολή στον/στην";
$dropbox_lang['receivedTitle'] = "Εισερχόμενα Αρχεία";
$dropbox_lang['sentTitle'] = "Απεσταλμένα Αρχεία";
$dropbox_lang['confirmDelete1'] = "Σημείωση: Το αρχείο ";
$dropbox_lang['confirmDelete2'] = " θα διαγραφεί μόνο από τον κατάλογό σας";
$dropbox_lang['all'] = "Σημείωση: Τα αρχεία θα διαγραφούν μόνο από τον κατάλογό σας";
$dropbox_lang['workDelete'] = "Διαγραφή από τον κατάλογο";
$dropbox_lang['sentBy'] = "Στάλθηκε από τον/την";
$dropbox_lang['sentTo'] = "Στάλθηκε στον/στην";
$dropbox_lang['sentOn'] = "την";
$dropbox_lang['anonymous'] = "ανώνυμος";
$dropbox_lang['ok'] = "Αποστολή";
$dropbox_lang['lastUpdated'] = "Τελευταία ενημέρωση την";
$dropbox_lang['lastResent'] = "Τελευταία επαναποστολή στις";
$dropbox_lang['tableEmpty'] = "Ο κατάλογος είναι κενός.";
$dropbox_lang['overwriteFile'] = "Θέλετε να αντικαταστήσετε το προηγούμενο αρχείο που στείλατε;";
$dropbox_lang['orderBy'] = "Ταξινόμηση με βάση";
$dropbox_lang['lastDate'] = "την τελευταία ημερομηνία αποστολής";
$dropbox_lang['firstDate'] = "την πρώτη ημερομηνία αποστολής";
$dropbox_lang['title'] = "τον τίτλο";
$dropbox_lang['size'] = "το μέγεθος του αρχείου";
$dropbox_lang['author'] = "τον διδάσκοντα";
$dropbox_lang['sender'] = "τον αποστολέα";
$dropbox_lang['file'] = "Αρχείο";
$dropbox_lang['fileSize'] = "Μέγεθος";
$dropbox_lang['date'] = "Ημερομηνία";
$dropbox_lang['col_recipient'] = "Παραλήπτης";
$dropbox_lang['recipient'] = "τον παραλήπτη";
$dropbox_lang['docAdd'] = "Το αρχείο στάλθηκε με επιτυχία";
$dropbox_lang['fileDeleted'] = "Το επιλεγμένο αρχείο έχει διαγραφεί από το Χώρο Ανταλλαγής Αρχείων.";
$dropbox_lang['backList'] = "Επιστροφή στο Χώρο Ανταλλαγής Αρχείων";
$dropbox_lang['mailingAsUsername'] = "Mailing ";
$dropbox_lang['mailingInSelect'] = "---Mailing---";
$dropbox_lang['mailingSelectNoOther'] = "Η αποστολή μηνύματος δεν μπορεί να συνδιαστεί με αποστολή σε άλλους παραλήπτες";
$dropbox_lang['mailingNonMailingError'] = "Mailing cannot be overwritten by non-mailing and vice-versa";
$dropbox_lang['mailingNotYetSent'] = "Mailing content files have not yet been sent out...";
$dropbox_lang['mailingSend'] = "Send content files";
$dropbox_lang['mailingConfirmSend'] = "Send content files to individual destinations ?";
$dropbox_lang['mailingBackToDropbox'] = "(back to Dropbox main window)";
$dropbox_lang['mailingWrongZipfile'] = "Mailing must be zipfile with STUDENTID or LOGINNAME";
$dropbox_lang['mailingZipEmptyOrCorrupt'] = "Mailing zipfile is empty or not a valid zipfile";
$dropbox_lang['mailingZipPhp'] = "Mailing zipfile must not contain php files - it will not be sent";
$dropbox_lang['mailingZipDups'] = "Mailing zipfile must not contain duplicate files - it will not be sent";
$dropbox_lang['mailingFileFunny'] = "no name, or extension not 1-4 letters or digits";
$dropbox_lang['mailingFileNoPrefix'] = "name does not start with ";
$dropbox_lang['mailingFileNoPostfix'] = "name does not end with ";
$dropbox_lang['mailingFileNoRecip'] = "name does not contain any recipient-id";
$dropbox_lang['mailingFileRecipNotFound'] = "no such student with ";
$dropbox_lang['mailingFileRecipDup'] = "multiple users have ";
$dropbox_lang['mailingFileIsFor'] = "is for ";
$dropbox_lang['mailingFileSentTo'] = "sent to ";
$dropbox_lang['mailingFileNotRegistered'] = " (not registered for this course)";
$dropbox_lang['mailingNothingFor'] = "Nothing for";
$dropbox_lang['justUploadInSelect'] = "--- Ανέβασμα αρχείου ---";
$dropbox_lang['justUploadInList'] = "Ανέβασμα αρχείου από τον/την";
$dropbox_lang['mailingJustUploadNoOther'] = "Το ανέβασμα αρχείου δεν μπορεί να συνδυαστεί με αποστολή σε άλλους παραλήπτες";

/**********************************************************
* exercice.inc.php
**********************************************************/
$langExercicesView="Προβολή Άσκησης";
$langExercicesResult="Αποτελέσματα Άσκησης";
$langQuestion="Ερώτηση";
$langQuestions="Ερωτήσεις";
$langAnswer="Απάντηση";
$langAnswers="Απαντήσεις";
$langComment="Σχόλιο";
$langMaj="Ενημέρωση";
$langEvalSet="Ρυθμίσεις βαθμολογίας";
$langExercice="Ασκηση";
$langActive="ενεργό";
$langInactive="μη ενεργό";
$langNoEx="Δεν υπάρχει διαθέσιμη άσκηση";
$langNewEx="Νέα άσκηση";
$langExerciseType="Τύπος Ασκήσεων";
$langExerciseName="Όνομα Άσκησης";
$langExerciseDescription="Περιγραφή Άσκησης";
$langSimpleExercise="Σε μία μόνο σελίδα";
$langSequentialExercise="Σε μία ερώτηση ανά σελίδα (στη σειρά)";
$langRandomQuestions="Τυχαίες Ερωτήσεις";
$langGiveExerciseName="Δώστε το όνομα της άσκησης";
$langGiveExerciseInts="Τα πεδία Χρονικός Περιορισμός & Επιτρεπόμενες Επαναλήψεις πρέπει να είναι ακέραιοι αριθμοί";
$langQuestCreate="Δημιουργία ερωτήσεων";
$langExRecord="Η άσκηση σας αποθηκεύτηκε";
$langBackModif="Επιστροφή στην διόρθωση της άσκησης";
$langDoEx="Κάντε την άσκηση";
$langDefScor="Καθορίστε τις ρυθμίσεις βαθμών";
$langCreateModif="Δημιουργία / Αλλαγή των ερωτήσεων";
$langSub="Υπότιτλος";
$langNewQu="Νέα ερώτηση";
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
$langInfoQuestion="Στοιχεία ερώτησης";
$langInfoExercise="Στοιχεία άσκησης";
$langAmong = "μεταξύ";
$langTake = "διάλεξε";
$langAnswersNotDisp = "Απόκρυψη απαντήσεων μετά το τέλος της άσκησης";
$langAnswersDisp = "Εμφάνιση απαντήσεων μετά το τέλος της άσκησης";
$langScoreNotDisp = "Απόκρυψη βαθμολογίας μετά το τέλος της άσκησης";
$langScoreDisp = "Εμφάνιση βαθμολογίας μετά το τέλος της άσκησης";
// admin.php
$langExerciseManagement="Διαχείριση Άσκησης";
$langExerciseModify="Τροποποίηση Άσκησης";
$langQuestionManagement="Διαχείριση Ερώτησης";
$langQuestionNotFound="Δεν βρέθηκε η ερώτηση";
$langAlertAdmin="Παρακαλώ δηλώστε τουλάχιστον έναν διαχειριστή για το μάθημα!";
$langBackExerciseManagement = "Επιστροφή στη διαχείριση άσκησης";

// question_admin.inc.php
$langNoAnswer="Δεν υπάρχει απάντηση αυτή την στιγμή!";
$langGoBackToQuestionPool="Επιστροφή στις διαθέσιμες ερωτήσεις";
$langGoBackToQuestionList="Επιστροφή στη λίστα ερωτήσεων";
$langQuestionAnswers="Απαντήσεις στην ερώτηση";
$langUsedInSeveralExercises="Προσοχή! H ερώτηση και οι απαντήσεις τις χρησιμοποιούνται σε αρκετές ασκήσεις. Θέλετε να τις αλλάξετε;";
$langModifyInAllExercises="σε όλες τις ασκήσεις";
$langModifyInThisExercise="μόνο στην τρέχουσα άσκηση";
$langQuestionView="Προβολή";

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
$langTypeTextBelow="Πληκτρολογήστε το κείμενό σας παρακάτω";
$langDefaultTextInBlanks="Πρωτεύουσα της Ελλάδας είναι η [Αθήνα].";
$langDefaultMatchingOptA="καλός";
$langDefaultMatchingOptB="όμορφη";
$langDefaultMakeCorrespond1="Ο πατέρας σου είναι";
$langDefaultMakeCorrespond2="Η μητέρα σου είναι";
$langDefineOptions="Καθορίστε τις επιλογές";
$langMakeCorrespond="Κάντε την αντιστοιχία";
$langFillLists="Συμπληρώστε τις δύο λίστες που ακολουθούν";
$langGiveText="Πληκτρολογήστε το κείμενο";
$langDefineBlanks="Ορίστε τουλάχιστον ένα κενό με αγκύλες [...]";
$langGiveAnswers="Δώστε τις απαντήσεις στις ερωτήσεις";
$langChooseGoodAnswer="Διαλέξτε την σωστή απάντηση";
$langChooseGoodAnswers="Διαλέξτε μία ή περισσότερες σωστές απαντήσεις";
$langColumnA="Στήλη Α";
$langColumnB="Στήλη B";
$langMoreLessChoices="Προσθήκη/Αφαίρεση επιλογών";

// question_list_admin.inc.php
$langQuestionList="Κατάλογος ερωτήσεων της άσκησης";
$langGetExistingQuestion="Ερώτηση από άλλη άσκηση";

// question_pool.php
$langQuestionPool="Διαθέσιμες Ερωτήσεις";
$langOrphanQuestions="Ερωτήσεις χωρίς απάντηση";
$langNoQuestion="Δεν έχουν ορισθεί ερωτήσεις για τη συγκεκριμένη άσκηση";
$langAllExercises="Όλες οι ερωτήσεις";
$langFilter="Φιλτράρισμα";
$langGoBackToEx="Επιστροφή στην άσκηση";
$langReuse="Επαναχρησιμοποίηση";
$langQuestionReused = "Η ερώτηση προστέθηκε στην άσκηση";

// exercise_result.php
$langElementList="Το στοιχείο";
$langScore="Βαθμολογία";
$langQuestionScore="Βαθμολογία ερώτησης";
$langCorrespondsTo="Αντιστοιχεί σε";
$langExpectedChoice="Αναμενόμενη Απάντηση";
$langYourTotalScore="Συνολική βαθμολογία άσκησης";

// exercice_submit.php
$langDoAnEx="Κάντε μια άσκηση";
$langCorrect="Σωστό";
$langExerciseNotFound="Η απάντηση δεν βρέθηκε";
$langAlreadyAnswered="Απαντήσατε ήδη στην ερώτηση";

// exercise result.php
$langExerciseStart="Έναρξη";
$langExerciseEnd="Λήξη";
$langExerciseDuration="Διάρκεια Εκτέλεσης";
$langExerciseConstrain="Χρονικός περιορισμός";
$langExerciseEg="π.χ.";
$langExerciseConstrainUnit="λεπτά";
$langExerciseConstrainExplanation="0 για καθόλου περιορισμό";
$langExerciseAttemptsAllowedExplanation="0 για απεριόριστο αριθμό επαναλήψεων";
$langExerciseAttemptsAllowed="Επιτρεπόμενες επαναλήψεις";
$langExerciseAttemptsAllowedUnit="φορές";
$langExerciseExpired="Έχετε φτάσει τον μέγιστο επιτρεπτό αριθμό επαναλήψεων της άσκησης.";
$langExerciseExpiredTime="Έχετε ξεπεράσει το επιτρεπτό χρονικό όριο εκτέλεσης της άσκησης.";
$langExerciseLis="Λίστα ασκήσεων";
$langResults="Αποτελέσματα";
$langResultsFailed="Αποτυχία";
$langYourTotalScore2="Συνολική βαθμολογία";
$langExerciseScores1="HTML";
$langExerciseScores2="Ποσοστιαία";
$langExerciseScores3="CSV";
$langNotRecorded = "μη καταγεγραμμένη";

/***********************************************
* external_module.inc.php
***********************************************/
$langSubTitle="<br><strong>Σημείωση:</strong> Αν θέλετε να προσθέσετε ένα σύνδεσμο σε μια σελίδα,
	πηγαίνετε σε αυτή τη σελίδα, κάντε αποκοπή και επικόλληση τη διεύθυνσή της στη μπάρα των URL
	στο πάνω μέρος του browser και εισάγετέ το στο πεδίο \"Σύνδεσμος\" παρακάτω.<br><br>";
$langLink="Σύνδεσμος";
$langInvalidLink = "Ο σύνδεσμος (ή η περιγραφή του) είναι κενός και δεν προστέθηκε!";
$langNotAllowed = "Μη επιτρεπτή ενέργεια";

/***********************************************
* faculte.inc.php
***********************************************/
$langListFaculteActions="Κατάλογος $langOfFaculties - Ενέργειες";
$langCodeFaculte1="Κωδικός $langOfFaculty";
$langCodeFaculte2="(με λατινικούς χαρακτήρες μόνο, π.χ. MATH)";
$langAddFaculte="Προσθήκη $langOfFaculties";
$langFaculte2="(π.χ. Μαθηματικό)";
$langAddSuccess="Η εισαγωγή πραγματοποιήθηκε με επιτυχία!";
$langNoSuccess="Πρόβλημα κατά την εισαγωγή των στοιχείων!";
$langProErase="Υπάρχουν διδασκόμενα μαθήματα!";
$langNoErase="Η διαγραφή του $langOfFaculty δεν είναι δυνατή.";
$langErase="Η $langFaculty διαγράφηκε!";
$langFCodeExists= "Ο κωδικός που βάλατε υπάρχει ήδη! Δοκιμάστε ξανά επιλέγοντας διαφορετικό";
$langFaculteExists="Η $langFaculty που βάλατε υπάρχει ήδη! Δοκιμάστε ξανά επιλέγοντας διαφορετικό";
$langEmptyFaculte="Αφήσατε κάποιο από τα πεδία κενά! Δοκιμάστε ξανά";
$langGreekCode="Ο κωδικός που βάλατε περιέχει μη λατινικούς χαρακτήρες!. Δοκιμάστε ξανά επιλέγοντας διαφορετικό";

/******************************************************
* forum_admin.inc.php
*******************************************************/
$langOrganisation="Διαχείριση περιοχών";
$langForCat="Περιοχές συζητήσεων της κατηγορίας";
$langBackCat="επιστροφή στις κατηγορίες";
$langForName="Όνομα περιοχής συζητήσεων";
$langFunctions="Λειτουργίες";
$langEditForum="Τροποποίηση";
$langAddForCat="Προσθήκη περιοχής συζητήσεων";
$langChangeCat="Αλλαγή της κατηγορίας";
$langChangeForum="Τροποποίηση της περιοχής συζήτησης";
$langModCatName="Αλλαγή ονόματος κατηγορίας";
$langCat="Κατηγορία";
$langNameCatMod="Το όνομα της κατηγορίας έχει αλλάξει";
$langBack="Επιστροφή";
$langCatAdded="Προστέθηκε κατηγορία";
$langEmptyCat = "Πληκτρολογήστε όνομα κατηγορίας";
$langForCategories="Κατηγορίες περιοχών συζητήσεων";
$langAddForums="Για να προσθέσετε περιοχές συζητήσεων, κάντε κλίκ στο «Περιοχές συζητήσεων» στην κατηγορία της επιλογής σας. Μια κενή κατηγορία (χωρίς περιοχές) δεν θα φαίνεται στους ".$langsOfStudentss." ";
$langCategories="Κατηγορίες";
$langNbFor="Πλήθος συζητήσεων";
$langAddCategory="Προσθήκη κατηγορίας";
$langForumDataChanged = "Τα στοιχεία της περιοχής συζητήσεων έχουν αλλάξει";
$langForumCategoryAdded = "Προστέθηκε νέα περιοχή συζητήσεων στην κατηγορία που επιλέξατε";
$langForumDelete = "Η περιοχή συζητήσεων έχει διαγραφεί";
$langCatForumDelete = "Η κατηγορία της περιοχής συζητήσεων έχει διαγραφεί";
$langID = "Α/Α";
$langForumOpen = "Ανοικτή";
$langForumClosed = "Κλειστή";


/***************************************************************
* grades.inc.php
****************************************************************/
$m['grades'] = "Βαθμολογία";

/*************************************************************
* group.inc.php
*************************************************************/
$langGroupManagement="Διαχείριση ομάδων χρηστών";
$langNewGroupCreateData="Στοιχεία Ομάδας (αριθμητικά)";
$langNewGroupCreate="Δημιουργία καινούριας ομάδας χρηστών";
$langNewGroups="Αριθμός ομάδων χρηστών";
$langNewGroupMembers="Αριθμός συμμετεχόντων";
$langMax="Μέγ.";
$langPlaces="συμμετέχοντες στην ομάδα χρηστών (προαιρετικό)";
$langGroupPlacesThis="συμμετέχοντες (προαιρετικό)";
$langDeleteGroups="Διαγραφή όλων των ομάδων χρηστών";
$langGroupsAdded="ομάδες χρηστών έχουν προστεθεί";
$langGroupAdded = "ομάδα χρηστών έχει προστεθεί";
$langGroupsDeleted="Ολες οι ομάδες χρηστών έχουν διαγραφεί";
$langGroupDel="Η ομάδα χρηστών διαγράφηκε";
$langGroupsEmptied="Όλες οι ομάδες χρηστών είναι άδειες";
$langEmtpyGroups="Εκκαθάριση όλων των ομάδων χρηστών";
$langGroupsFilled="Όλες οι ομάδες χρηστών έχουν συμπληρωθεί";
$langFillGroups="Συμπλήρωση των ομάδων χρηστών";
$langGroupsProperties="Ρυθμίσεις ομάδες χρηστών";
$langStudentRegAllowed="Οι χρήστες επιτρέπεται να γραφτούν στις ομάδες";
$langStudentRegNotAllowed="Οι χρήστες δεν επιτρέπεται να γραφτούν στις ομάδες";
$langTools="Εργαλεία";
$langExistingGroups="Υπάρχουσες Ομάδες Χρηστών";
$langEdit="Διόρθωση";
$langDeleteGroupWarn = "Επιβεβαίωση διαγραφής της ομάδας χρηστών";
$langDeleteGroupAllWarn = "Επιβεβαίωση διαγραφής όλων των ομάδων χρηστών";
$langEmptyGroupName = "Αφήσατε κενό το όνομα της ομάδας χρηστών";

// Group Properties
$langGroupProperties="Ρυθμίσεις ομάδων χρηστών";
$langGroupAllowStudentRegistration="Οι ".$langsStudents." επιτρέπονται να εγγραφούν στις ομάδες χρηστών";
$langGroupStudentRegistrationType="Δυνατότητα Εγγραφής";
$langGroupPrivatise="Κλειστές περιοχές συζητήσεων ομάδων χρηστών";
$langGroupForum="Περιοχή συζητήσεων";
$langGroupPropertiesModified="Αλλάχτηκαν οι ρυθμίσεις της ομάδας χρηστών";

// Group space
$langGroupThisSpace="Περιοχή για την ομάδα χρηστών";
$langGroupName="Όνομα ομάδας χρηστών";
$langEditGroup="Διόρθωση της ομάδας χρηστών";
$langUncompulsory="(προαιρετικό)";
$langNoGroupStudents="Μη εγγεγραμμένοι ".$langsStudents." ";
$langGroupMembers="Μέλη ομάδας χρηστών";
$langGroupValidate="Επικύρωση";
$langGroupCancel="Ακύρωση";
$langGroupSettingsModified="Οι ρυθμίσεις της ομάδας χρηστών έχουν αλλάξει";
$langNameSurname="Όνομα Επίθετο";
$langEmail="email";
$langGroupStudentsInGroup=" ".$langsStudents." εγγεγραμμένοι σε ομάδες χρηστών";
$langGroupStudentsRegistered=" ".$langsStudents." εγγεγραμμένοι στο μάθημα";
$langGroupNoGroup="μη εγγεγραμμένοι ".$langsStudents." ";
$langGroupUsersList="Βλέπε <a href=../user/user.php>Χρήστες</a>";
$langGroupTooMuchMembers="Ο αριθμός που προτάθηκε υπερβαίνει το μέγιστο επιτρεπόμενο (μπορείτε να το αλλάξετε παρακάτω).
	Η σύνθεση της ομάδας δεν άλλαξε";
$langGroupTutor="Υπεύθυνος ομάδας";
$langGroupNoTutor="κανένας";
$langGroupNone="δεν υπάρχει";
$langGroupNoneMasc="κανένας";
$langAddTutors="Διαχείριση καταλόγου χρηστών";
$langForumGroup="Περιοχή συζητήσεων της ομάδας";
$langMyGroup="η ομάδα μου";
$langOneMyGroups="επιβλέπων";
$langRegIntoGroup="Προσθέστε με στην ομάδα";
$langGroupNowMember="Είσαι τώρα μέλος της ομάδας";
$langPublicAccess="ανοικτό";
$langForumType="Τύπος περιοχής συζητήσεων";
$langPropModify="Αλλαγή ρυθμίσεων";
$langGroupAccess="Πρόσβαση";
$langGroupFilledGroups="Οι ομάδες χρηστών έχουν συμπληρωθεί από ".$langsOfStudentss." που βρίσκονται στον κατάλογο «Χρήστες».";
$langGroupInfo = "Στοιχεία Ομάδας";

// group - email
$langEmailGroup = "Αποστολή e-mail στην ομάδα";
$langTypeMessage = "Πληκτρολογήστε το μήνυμά σας παρακάτω";
$langSend = "Αποστολή";
$langEmailSuccess = "Το e-mail σας στάλθηκε με επιτυχία !";
$langMailError = "Σφάλμα κατά την αποστολή e-mail !";
$langGroupMail = "Mail στην Ομάδα Χρηστών";
$langMailSubject = "Θέμα: ";
$langMailBody = "Μήνυμα: ";
$langProfLesson = "Διδάσκων του μαθήματος";

/*****************************************************
* guest.inc.php
*****************************************************/
$langAskGuest="Πληκτρολογήστε το συνθηματικό του λογαριασμού επισκέπτη";
$langAddGuest="Προσθήκη χρήστη επισκέπτη";
$langGuestName="Επισκέπτης";
$langGuestSurname="Μαθήματος";
$langGuestUserName="guest";
$langGuestExist="Υπάρχει ήδη ο λογαριασμός Επισκέπτη! Μπορείτε όμως αν θέλετε να αλλάξετε το συνθηματικό του.";
$langGuestSuccess="Ο λογαριασμός επισκέπτη (guest account) δημιουργήθηκε με επιτυχία !";
$langGuestFail="Πρόβλημα κατά την δημιουργία λογαριασμού επισκέπτη";
$langGuestChange="Η αλλαγή συνθηματικού επισκέπτη έγινε με επιτυχία!";

/********************************************************
* gunet.inc.php
********************************************************/
$infoprof="Σύντομα θα σας σταλεί e-mail από την Ομάδα Διαχείρισης της Πλατφόρμας Ασύγχρονης Τηλεκπαίδευσης $siteName, με τα στοιχεία του λογαριασμού σας.";
$profinfo="Η ηλεκτρονική πλατφόρμα $siteName διαθέτει 2 εναλλακτικούς τρόπους εγγραφής διδασκόντων";
$userinfo="Η ηλεκτρονική πλατφόρμα $siteName διαθέτει 2 εναλλακτικούς τρόπους εγγραφής χρηστών:";
$regprofldap="Εγγραφή διδασκόντων που έχουν λογαριασμό στην Υπηρεσία Καταλόγου (LDAP Directory Service) του ιδρύματος που ανήκουν";
$regldap="Εγγραφή χρηστών που έχουν λογαριασμό στην Υπηρεσία Καταλόγου (LDAP Directory Service) του ιδρύματος που ανήκουν";
$regprofnoldap="Εγγραφή διδασκόντων που δεν έχουν λογαριασμό στην Υπηρεσία Καταλόγου του ιδρύματος που ανήκουν";
$regnoldap="Εγγραφή χρηστών που δεν έχουν λογαριασμό στην Υπηρεσία Καταλόγου του ιδρύματος που ανήκουν";
$mailbody1="\n$Institution\n\n";
$mailbody2="Ο Χρήστης\n\n";
$mailbody3="επιθυμεί να έχει πρόσβαση ";
$mailbody4="στην υπηρεσία Ασύγχρονης Τηλεκπαίδευσης ";
$mailbody5="του $siteName ";
$mailbody6="σαν $langTeacher.";
$mailbody7="$langFaculty:";
$mailbody8="ως $langStudent.";
$logo= "Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης $siteName";
$gunet="Ομάδα Ασύγχρονης Τηλεκπαίδευσης $siteName";
$sendinfomail="Αποστολή ενημερωτικού e-mail στους $langsTeachers του $siteName";
$infoabouteclass="Ενημερωτικό δελτίο πλατφόρμας $siteName";

// contact.php
$introcontact = "Μπορείτε να επικοινωνείτε με την Ομάδα Υποστήριξης της πλατφόρμας <b>".$siteName."</b> με τους παρακάτω τρόπους:";
$langPostMail="<b>Ταχυδρομική Διεύθυνση:</b>";
$langPhone = "Τηλέφωνο";
$langFax = "<b>Fax:</b>";
$langForm="Συμπλήρωση Φόρμας";
$langReturn="Eπιστροφή";

/************************************************************
* import.inc.php
************************************************************/
$langAddPage="Προσθήκη μιας σελίδας";
$langPageAdded="Η σελίδα προστέθηκε";
$langPageTitleModified="Ο τίτλος της σελίδας άλλαξε";
$langSendPage="Όνομα αρχείου της σελίδας";
$langCouldNotSendPage="Το αρχείο δεν είναι σε μορφή HTML και δεν ήταν δυνατόν να σταλεί. Αν θέλετε να στείλετε αρχεία που
δεν είναι σε μορφή HTML (π.χ. PDF, Word, Power Point, Video, κ.λπ.)
χρησιμοποιήστε τα <a href='../document/document.php'>Έγγραφα</a>.";
$langAddPageToSite="Προσθήκη μιας σελίδας σε ένα site";
$langCouldNot="Το αρχείο δεν ήταν δυνατόν να σταλεί";
$langOkSent="<p><b>Η σελίδα σας στάλθηκε</b><br/><br/>Δημιουργήθηκε σύνδεσμος προς αυτήν στο αριστερό μενού</p>";
$langTooBig="Δεν επιλέξατε κάποιο αρχείο για να στείλετε ή το αρχείο είναι πολύ μεγάλο ή δεν πληκτρολογήσατε τίτλο σελίδας";
$langExplanation_0="Αν έχετε  δημιουργήσει κάποια σελίδα για το μάθημα σας σε μορφή HTML (π.χ. \"my_page.htm\"), τότε μπορείτε να χρησιμοποιήσετε την παρακάτω φόρμα για να κατασκευάσετε έναν σύνδεσμο στο μενού του μαθήματος (αριστερά). Η σελίδα σας με αυτό τον τρόπο  δημοσιεύεται (ανεβαίνει) στην πλατφόρμα και εμφανίζεται μαζί με τα υπόλοιπα εργαλεία του μαθήματος. <br/>Για μεγαλύτερη ευελιξία, ο σύνδεσμος αυτός μπορεί να γίνεται ενεργός/ανενεργός όπως τα υπόλοιπα εργαλεία.";
$langExplanation_1="Στοιχεία σελίδας";
$langExplanation_2="Το όνομα που θα εμφανίζεται στο αριστερό μενού.";
$langExplanation_3="Αν θέλετε να δημιουργήσετε συνδέσμους για αρχεία που <u>δεν</u> είναι σε μορφή HTML (π.χ. PDF, Word, Power Point, Video, κ.λπ.) τότε χρησιμοποιήστε το υποσύστημα <a href='../document/document.php'>Έγγραφα</a>.";
$langExplanation_4="Στοιχεία εξωτερικού συνδέσμου";
$langNoticeExpl = "Σημ: Το μέγιστο επιτρεπτό μέγεθος του αρχείου της σελίδας είναι 20MB.";
$langPgTitle="Τίτλος σελίδας";

/***************************************************************
* index.inc.php
***************************************************************/
$langHomePage = "Αρχική Σελίδα";
$langInvalidId = "Λάθος στοιχεία.<br>Αν δεν είστε γραμμένος, συμπληρώστε τη
        <a href='modules/auth/registration.php'>φόρμα εγγραφής</a>.";
$langInvalidGuestAccount = "Το μάθημα για το οποίο έχει δημιουργηθεί ο λογαριασμός 'χρήστη επισκέπτη' δεν υπάρχει πλέον.";
$langAccountInactive1 = "Μη ενεργός λογαριασμός.";
$langAccountInactive2 = "Παρακαλώ επικοινωνήστε με τον διαχειριστή για την ενεργοποίηση του λογαριασμού σας";
$langMyCoursesProf="Τα μαθήματα που υποστηρίζω (".$langTeacher.")";
$langMyCoursesUser="Τα μαθήματα που παρακολουθώ (".$langStudent.")";
$langNoCourses="Δεν υπάρχουν μαθήματα";
$langCourseCreate="Δημιουργία Μαθήματος";
$langMyAgenda = "Το Ημερολόγιό μου";
$langMyStats = "Στατιστικά Χρήσης";   #ophelia 1-8-2006
$langMyAnnouncements = "Οι Ανακοινώσεις μου";
$langWelcome="τα μαθήματα είναι διαθέσιμα παρακάτω. Άλλα μαθήματα απαιτούν
όνομα χρήστη και συνθηματικό, τα οποία μπορείτε να τα αποκτήσετε κάνοντας κλίκ στην 'εγγραφή'. Οι καθηγητές
μπορούν να δημιουργήσουν μαθήματα κάνοντας κλικ στην εγγραφή επίσης, αλλά επιλέγοντας ύστερα
'Δημιουργία μαθημάτων (καθηγητές)'.";
$langAdminTool = "Διαχείριση Πλατφόρμας";
$langPass="Συνθηματικό (password)";
$langHelp="Βοήθεια";
$langSelection="Επιλογή";
$langMenu ="Μενού";
$langLogout="Έξοδος";
$langSupportForum="Περιοχή Υποστήριξης";
$langInvalidAuth = "Λάθος τρόπος πιστοποίησης";
$langContact = 'Επικοινωνία';
$langInfoPlat = 'Ταυτότητα Πλατφόρμας';
$lang_forgot_pass = "Ξεχάσατε το συνθηματικό σας;";
$langNewAnnounce = "Νέα!";
$langUnregUser = "Διαγραφή λογαριασμού";
$langListFaculte = "Κατάλογος Τμημάτων";
$langListCourses = "Kατάλογος Μαθημάτων";
$langAsynchronous = "Ομάδα Ασύγχρονης Τηλεκπαίδευσης";
$langUserLogin = "Σύνδεση χρήστη";
$langWelcomeToEclass = "Καλωσορίσατε στο ".$siteName."!";
$langWelcomeToPortfolio = "Καλωσορίσατε στο προσωπικό σας χαρτοφυλάκιο";
$langUnregCourse = "Απεγγραφή από μάθημα";
$langUnCourse = "Απεγγραφή";
$langCourseCode = "Μάθημα (Κωδικός)";

/*$langWelcomeStud = "<br>Καλωσήλθατε στο περιβάλλον της πλατφόρμας <b>$siteName</b>.<br><br>
                    Επιλέξτε \"Εγγραφή σε μάθημα\" για να παρακολουθήσετε τα διαθέσιμα ηλεκτρονικά μαθήματα.";
$langWelcomeProf = "<br>Καλωσήλθατε στο περιβάλλον της πλατφόρμας <b>$siteName</b>.<br><br>
                    Επιλέξτε \"Δημιουργία Μαθήματος\" για να δημιουργήσετε τα ηλεκτρονικά σας μαθήματα.";
*/
$langWelcomeStud = "Επιλέξτε \"Εγγραφή σε μάθημα\" για να παρακολουθήσετε τα διαθέσιμα ηλεκτρονικά μαθήματα.";
$langWelcomeProf = "Επιλέξτε \"Δημιουργία Μαθήματος\" για να δημιουργήσετε τα ηλεκτρονικά σας μαθήματα.";
$langWelcomeSelect = "Επιλέξτε";
$langWelcomeStudPerso = "<b>\"Εγγραφή σε μάθημα\"</b> για να παρακολουθήσετε τα διαθέσιμα ηλεκτρονικά μαθήματα.";
$langWelcomeProfPerso = "<b>\"Δημιουργία Μαθήματος\"</b> για να δημιουργήσετε τα ηλεκτρονικά σας μαθήματα.";
$langBasicOptions = "Βασικές Επιλογές";
$langUserOptions = "Επιλογές Χρήστη";
$langAdminOptions = "Επιλογές Διαχείρισης";
$langCourseOptions = "Επιλογές Μαθήματος";

/***********************************************************
* install.inc.php
***********************************************************/
$langTitleInstall = "Οδηγός εγκατάστασης Open eClass";
$langWelcomeWizard = "Καλωσορίσατε στον οδηγό εγκατάστασης του Open eClass!";
$langInstallProgress = "Πορεία εγκατάστασης";
$langThisWizard = "Ο οδηγός αυτός:";
$langWizardHelp1 = "Θα σας βοηθήσει να ορίσετε τις ρυθμίσεις για τη βάση δεδομένων";
$langWizardHelp2 = "Θα σας βοηθήσει να ορίσετε τις ρυθμίσεις της πλατφόρμας";
$langWizardHelp3 = "Θα δημιουργήσει το αρχείο <tt>config.php</tt>";
$langRequiredPHP = "Απαιτούμενα PHP modules";
$langOptionalPHP = "Προαιρετικά PHP modules";
$langOtherReq = "Άλλες απαιτήσεις συστήματος";
$langInstallBullet1 = "Μια βάση δεδομένων MySQL, στην οποία έχετε λογαριασμό με δικαιώματα να δημιουργείτε και να διαγράφετε βάσεις δεδομένων.";
$langInstallBullet2 = "Δικαιώματα εγγραφής στον κατάλογο <tt>include/</tt>.";
$langInstallBullet3 = "Δικαιώματα εγγραφής στον κατάλογο όπου το Open eClass έχει αποσυμπιεστεί.";
$langCheckReq = "Έλεγχος προαπαιτούμενων προγραμμάτων για τη λειτουργία του Open eClass";
$langInfoLicence = "Tο Open eClass είναι ελεύθερη εφαρμογή και διανέμεται σύμφωνα με την άδεια GNU General Public Licence (GPL). <br>Παρακαλούμε διαβάστε την άδεια και κάνετε κλίκ στο 'Αποδοχή'";
$langAccept = "Αποδοχή";
$langEG	= "π.χ.";
$langDBLogin = "Όνομα Χρήστη για τη Βάση Δεδομένων";
$langDBPassword	= "Συνθηματικό για τη Βάση Δεδομένων";
$langMainDB = "Κύρια Βάση Δεδομένων του Open eClass";
$langAllFieldsRequired	= "όλα τα πεδία είναι υποχρεωτικά";
$langPrintVers = "Εκτυπώσιμη μορφή";
$langLocalPath	= "Path του Open eClass στον εξυπηρετητή";
$langAdminEmail	= "Email Διαχειριστή";
$langAdminName = "Όνομα Διαχειριστή";
$langAdminSurname = "Επώνυμο Διαχειριστή";
$langAdminLogin	= "Όνομα Χρήστη του Διαχειριστή";
$langAdminPass	= "Συνθηματικό του Διαχειριστή";
$langHelpDeskPhone = "Τηλέφωνο Helpdesk";
$langHelpDeskFax = "Αριθμός Fax Helpdesk";
$langHelpDeskEmail = "Email Helpdesk";
$langCampusName	= "Όνομα Πλατφόρμας";
$langInstituteShortName  = "Όνομα Ιδρύματος - Οργανισμού";
$langInstituteName = "Website Ιδρύματος - Οργανισμού";
$langInstitutePostAddress = "Ταχ. Διεύθυνση Ιδρύματος - Οργανισμού";
$langWarnHelpDesk = "Προσοχή: στο \"Email Helpdesk\" στέλνονται οι αιτήσεις καθηγητών για λογαριασμό στην πλατφόρμα";
$langDBSettingIntro = "Το πρόγραμμα εγκατάστασης θα δημιουργήσει την κύρια βάση δεδομένων του Open eClass. Έχετε υπ'όψιν σας ότι κατά τη λειτουργία της πλατφόρμας θα χρειαστεί να δημιουργηθούν νέες βάσεις δεδομένων (μία για κάθε μάθημα) ";
$langStep1 = "Βήμα 1 από 6";
$langStep2 = "Βήμα 2 από 6";
$langStep3 = "Βήμα 3 από 6";
$langStep4 = "Βήμα 4 από 6";
$langStep5  = "Βήμα 5 από 6";
$langStep6 = "Βήμα 6 από 6";
$langCfgSetting	= "Ρυθμίσεις Συστήματος";
$langDBSetting = "Ρυθμίσεις της MySQL";
$langMainLang	= "Κύρια Γλώσσα Εγκατάστασης";
$langLicence = "Άδεια Χρήσης";
$langLastCheck = "Τελευταίος έλεγχος πριν την εγκατάσταση";
$langRequirements = "Απαιτήσεις Συστήματος";
$langInstallEnd	= "Ολοκλήρωση Εγκατάστασης";
$langModuleNotInstalled = "Δεν είναι εγκατεστημένο";
$langReadHelp = "Διαβάστε περισσότερα";
$langWarnConfig = "Προσοχή !! Το αρχείο <b>config.php</b> υπάρχει ήδη στο σύστημά σας!! Το πρόγραμμα εγκατάστασης δεν πραγματοποιεί αναβάθμιση. Αν θέλετε να ξανατρέξετε την εγκατάσταση της πλατφόρμας, παρακαλούμε διαγράψτε το αρχείο config.php!";
$langWarnConfig1 = "Το αρχείο <b>config.php</b> υπάρχει ήδη στο σύστημά σας";
$langWarnConfig2 = "Αν θέλετε να ξανατρέξετε την εγκατάσταση της πλατφόρμας, παρακαλούμε διαγράψτε το αρχείο <b>config.php</b>";
$langWarnConfig3 = "Το πρόγραμμα εγκατάστασης δεν πραγματοποιεί αναβάθμιση";
$langErrorConfig = "<br><b>Παρουσιάστηκε σφάλμα!</b><br><br>Δεν είναι δυνατή η δημιουργία του αρχείου config.php.<br><br>Παρακαλούμε ελέγξτε τα δικαιώματα πρόσβασης στους υποκαταλόγους του Open eClass και δοκιμάστε ξανά την εγκατάσταση.";
$langErrorMysql = "Η MySQL  δεν λειτουργεί ή το όνομα χρήστη/συνθηματικό δεν είναι σωστό.<br/>Παρακαλούμε ελέγξετε τα στοιχεία σας:";
$langBackStep3 = "Επιστροφή στο βήμα 3";
$langBackStep3_2 = "Eπιστρέψτε στο βήμα 3 για να τα διορθώσετε.";
$langNotNeedChange = "Δεν χρειάζεται να το αλλάξετε";
$langNeedChangeDB = "αν υπάρχει ήδη κάποια Β.Δ. με το όνομα eclass αλλάξτε το";
$langWillWrite = "Τα παρακάτω θα γραφτούν στο αρχείο <b>config.php</b>";
$langProtect = "Συμβουλή: Για να προστατέψετε το Open eClass, αλλάξτε τα δικαιώματα πρόσβασης των αρχείων
           <tt>/config/config.php</tt> και <tt>/install/index.php</tt> και
           επιτρέψτε μόνο ανάγνωση (CHMOD 444).";
$langInstallSuccess = "Η εγκατάσταση ολοκληρώθηκε με επιτυχία! Κάντε κλίκ παρακάτω για να μπείτε στο Open eClass";
$langEnterFirstTime = "Είσοδος στο Open eClass";
$langMCU = "MCU (μονάδα ελέγχου για τηλεδιάσκεψη)";
$langVod = "Εξυπηρέτης Vod ";
$langSiteUrl = "URL του Open eClass";
$langInstall = "Eγκατάσταση του Open eClass";
$langAddOnStreaming = "Επιπρόσθετη λειτουργικότητα";
$langAddOnExpl = "Εάν επιθυμείτε να υποστηρίζετε streaming για τα αρχεία video που θα αποτελούν μέρος του υλικού των αποθηκευμένων μαθημάτων θα πρέπει να υπάρχει εγκατεστημένος streaming server.";

$langWarningInstall1 = "<b>Προσοχή!</b> Φαίνεται πως η επιλογή register_globals στο αρχείο php.ini δεν είναι ενεργοποιημένη. Χωρίς αυτήν το Open eClass δεν μπορεί να λειτουργήσει. Παρακαλούμε διορθώστε το αρχείο php.ini ώστε να περιέχει τη γραμμή:</p> <p><b>register_globals = On</b></p>";
$langWarningInstall2 = "<b>Προσοχή!</b> Φαίνεται πως η επιλογή short_open_tag στο αρχείο php.ini δεν είναι ενεργοποιημένη. Χωρίς αυτήν το Open eClass δεν μπορεί να λειτουργήσει. Παρακαλούμε διορθώστε το αρχείο php.ini ώστε να περιέχει τη γραμμή:</p><p><b>short_open_tag = On</b></p>";
$langWarningInstall3 = "<b>Προσοχή!</b> Φαίνεται πως ο οδηγός εγκατάστασης δεν έχει δικαιώματα δημιουργίας του κατάλογου <b>/config</b>.<br/>Χωρίς δικαιώματα δημιουργίας, ο οδηγός εγκατάστασης δεν μπορεί να συνεχίσει. Παρακαλούμε διορθώστε τα δικαιώματα.<br/>";
$langWarningInstall4 = "<b>Προσοχή!</b> Φαίνεται πως ο οδηγός εγκατάστασης δεν έχει δικαιώματα δημιουργίας του κατάλογου <b>/courses</b>.<br/>Χωρίς δικαιώματα δημιουργίας, ο οδηγός εγκατάστασης δεν μπορεί να συνεχίσει. Παρακαλούμε διορθώστε τα δικαιώματα.<br/>";
$langWarningInstall5 = "<b>Προσοχή!</b> Φαίνεται πως ο οδηγός εγκατάστασης δεν έχει δικαιώματα δημιουργίας του κατάλογου <b>/video</b>.<br/>Χωρίς δικαιώματα δημιουργίας, ο οδηγός εγκατάστασης δεν μπορεί να συνεχίσει. Παρακαλούμε διορθώστε τα δικαιώματα.<br/>";

$langWarnInstallNotice1 = "Πιθανόν επίσης να χρειάζονται και κάποιες άλλες αλλαγές. Διαβάστε τις οδηγίες εγκατάστασης στο αρχείο";
$langWarnInstallNotice2 = " και επανεκκινείστε τον οδηγό εγκατάστασης.";

$langReviewSettings = "Τα στοιχεία που δηλώσατε είναι τα παρακάτω: (Εκτυπώστε τα αν θέλετε να θυμάστε το συνθηματικό του διαχειριστή και τις άλλες ρυθμίσεις)";
$langToReq = "Η εγγραφή χρηστών θα γίνεται με αίτηση προς τον διαχειριστή της πλατφόρμας";
$langLDAPBaseDn = "Base DN του LDAP Εξυπηρέτη";
$langChooseLang = "Επιλογή Γλώσσας";
$langExpPhpMyAdmin = "Το Open eClass θα εγκαταστήσει το δικό του διαχειριστικό εργαλείο μέσω web των βάσεων δεδομένων MySQL (<a href=\"http://www.phpmyadmin.net\" target=_blank>phpMyAdmin</a>) αλλά μπορείτε να χρησιμοποιήσετε και το δικό σας.";
$langBeforeInstall1 = "Πριν προχωρήσετε στην εγκατάσταση τυπώστε και διαβάστε προσεκτικά τις ";
$langBeforeInstall2 = "Επίσης, γενικές οδηγίες για την πλατφόρμα μπορείτε να διαβάσετε ";
$langInstallInstr = "Οδηγίες Εγκατάστασης";
$langWithPHP = "με υποστήριξη PHP";

/********************************************************
* learnpath.inc.php
*********************************************************/
$langAddComment = "Προσθήκη / αλλαγή σχολίου";
$langLearningModule = "Ενότητα";
$langLearningObjects = "Εκπαιδευτικά Αντικείμενα";
$langLearningObject = "Εκπαιδευτικό Αντικείμενο";
$langLearningObjectsInUse = "Εκπαιδευτικά Αντικείμενα σε χρήση";
$langLearningObjectsInUse_sort = "Αντικείμενα σε χρήση";
$langLearningPathStructure = "Δομή Γραμμής Μάθησης";
$langLearningPathConfigure = "Διαμόρφωση γραμμής μάθησης";
$langContents = "Περιεχόμενα";
$langLearningPathUploadFile= "Εντοπισμός θέσης του αρχείου γραμμής μάθησης στον Η/Υ σας (τοπικά)";
$langAddModulesButton = "Προσθήκη επιλεγμένων";
$langAddOneModuleButton = "Προσθήκη ενότητας";
$langAlertBlockingMakedInvisible = "Αυτή η ενότητα είναι φραγμένη. Κάνοντας τη αόρατη, θα επιτραπεί στους ".$langsOfStudentss." η είσοδος στην επόμενη ενότητα χωρίς να χρειάζεται να ολοκληρώσουν την παρούσα. Επιβεβαιώστε την επιλογή σας";
$langAlertBlockingPathMadeInvisible = "Αυτή η γραμμή είναι φραγμένη. Κάνοντας την μη ορατή θα επιτραπεί στους ".$langsOfStudentss." η είσοδος στην επόμενη γραμμή χωρίς να χρειάζεται να ολοκληρώσουν την παρούσα. Επιβεβαιώστε την επιλογή σας";
$langAlreadyBrowsed = "Ολοκληρώθηκε";
$langAltMakeNotBlocking = "Αποδέσμευση";
$langAltScorm = "Scorm";
$langAreYouSureDeleteModule = "Είστε βέβαιοι για την συνολική διαγραφή της ενότητας;";
$langAreYouSureToDeleteScorm = "H γραμμή μάθησης αποτελεί μέρος ενός πακέτου SCORM. Αν διαγράψετε αυτή τη γραμμή, όλες οι ενότητες που συμβαδίζουν με το SCORM και όλα τα σχετικά αρχεία θα διαγραφούν απο την πλατφόρμα. Σίγουρα θέλετε να διαγράψετε τη γραμμή μάθησης ";
$langAreYouSureToRemove = "Σίγουρα θέλετε να αφαιρέσετε την παρακάτω ενότητα από τη γραμμή μάθησης: ";
$langAreYouSureToRemoveLabel = "Διαγράφοντας μία ετικέτα θα διαγραφούν και όλες οι ενότητες ή οι ετικέτες που περιέχει.";
$langAreYouSureToRemoveSCORM = "Ενότητες σύμφωνες με το SCORM θα αφαιρεθούν οριστικά, όταν διαγράψετε τη γραμμή μάθησης.";
$langAreYouSureToRemoveStd = "Η ενότητα θα παραμείνει διαθέσιμη στην ομάδα των ενοτήτων.";
$langBackModule = "Επιστροφή στη γραμμή μάθησης";
$langBackToLPAdmin = "Επιστροφή στη διαχείριση της γραμμής μάθησης";
$langBlock = "Φραγή";
$langBrowserCannotSeeFrames = "Ο browser σας δεν αναγνωρίζει frames.";
$langChangeRaw = "Αλλαγή του ελάχιστου αρχικού σημείου για να περάσει αυτή η ενότητα (ποσοστό): ";
$langChat = "Κουβεντούλα";
$langConfirmYourChoice = "Παρακαλώ επιβεβαιώστε την επιλογή σας";
$langCourseDescription = "Περιγραφή Μαθήματος";
$langCourseDescriptionAsModule = "Χρήση Περιγραφής Μαθήματος";
$langCourseDescriptionAsModuleLabel = "Περιγραφής Μαθήματος";
$langCourseHome = "Αρχική σελίδα μαθήματος";
$langCreateLabel = "Δημιουργία ετικέτας";
$langCreateNewLearningPath = "Δημιουργία νέας γραμμής μάθησης";
$langDOCUMENTTypeDesc = "Έγγραφο";
$langDefaultLearningPathComment = "Εισαγωγικό κείμενο της γραμμής μάθησης.";
$langDefaultModuleAddedComment = "Πρόσθετο εισαγωγικό κείμενο σχετικά με την παρουσία της ενότητας στη γραμμή μάθησης.";
$langDefaultModuleComment = "Εισαγωγικό κείμενο της ενότητας. Θα εμφανίζεται σε κάθε γραμμή μάθησης που θα περιέχει αυτή την ενότητα";
$langInstructions = "Οδηγίες";
$langModuleComment_inCurrentLP = "<u>Μόνο</u> για τη συγκεκριμένη γρ. μάθησης";
$langModuleComment_inCourse = "Εμφανίζεται με την <u>επαναχρησιμοποίηση</u> του αντικείμενου σε άλλη γραμμή μάθησης";
$langDescriptionCours = "Περιγραφή μαθήματος";
$langDocInsertedAsModule = "έχει προστεθεί σαν ενότητα";
$langDocumentAlreadyUsed = "Αυτό το έγγραφο έχει ήδη χρησιμοποιηθεί σαν ενότητα σε αυτή τη γραμμή μάθησης";
$langDocumentAsModule = "Χρήση Εγγράφου";
$langDocumentAsModuleLabel = "Εγγράφου";
$langDocumentInModule = "Έγγραφο σε ενότητα";
$langEXERCISETypeDesc = "Άσκηση πλατφόρμας";
$langEndOfSteps = "Κάντε κλίκ στη λήξη αφού ολοκληρώσετε αυτό το τελευταίο βήμα.";
$langErrorAssetNotFound = "Το στοιχείο δεν βρέθηκε: ";
$langErrorCopyAttachedFile = "Δεν είναι δυνατή η αντιγραφή αρχείου: ";
$langErrorCopyScormFiles = "Σφάλμα κατά την αντιγραφή των αναγκαίων αρχείων SCORM ";
$langErrorCopyingScorm = "Σφάλμα αντιγραφής περιεχομένου SCORM";
$langErrorCreatingDirectory = "Δεν είναι δυνατή η δημιουργία κατάλογου: ";
$langErrorCreatingFile = "Δεν είναι δυνατή η δημιουργία αρχείου: ";
$langErrorCreatingFrame = "Δεν είναι δυνατή η δημιουργία στα πλαίσια του αρχείου ";
$langErrorCreatingManifest = "Δεν είναι δυνατή η δημιουργία της προκήρυξης SCORM (imsmanifest.xml)";
$langErrorCreatingScormArchive = "Δεν είναι δυνατή η δημιουργία του καταλόγου αρχείων SCORM ";
$langErrorEmptyName = "Το όνομα πρέπει να συμπληρωθεί";
$langErrorFileMustBeZip = "Το αρχείο πρέπει να είναι σε μορφή αρχείου .zip";
$langErrorInvalidParms = "Σφάλμα: μη έγκυρη παράμετρος (χρησιμοποιήστε μόνο αριθμούς)";
$langErrorLoadingExercise = "Δεν είναι δυνατή η φόρτωση της άσκησης ";
$langErrorLoadingQuestion = "Δεν είναι δυνατή η φόρτωση της ερώτησης της άσκησης ";
$langErrorNameAlreadyExists = "Σφάλμα: Το όνομα υπάρχει ήδη στη γραμμή μάθησης ή στο σύνολο των ενοτήτων ";
$langErrorNoModuleInPackage = "Δεν υπάρχει ενότητα στο πακέτο";
$langErrorNoZlibExtension = "Η επέκταση Zlib της php απαιτείται για τη χρήση αυτού του εργαλείου. Παρακαλώ επικοινωνήστε με τον διαχειριστή της πλατφόρμας σας.";
$langErrorOpeningManifest = "Δεν μπορεί να βρεθεί το αρχείο <i>manifest</i> στο πακέτο.<br /> Αρχείο που δε βρέθηκε: imsmanifest.xml";
$langErrorOpeningXMLFile = "Δεν μπορεί να βρεθεί το δευτερεύον αρχείο έναρξης στο πακέτο.<br /> Αρχείο που δε βρέθηκε: ";
$langErrorReadingManifest = "Σφάλμα ανάγνωσης αρχείου <i>manifest</i>";
$langErrorReadingXMLFile = "Σφάλμα ανάγνωσης δευτερεύοντος αρχείου ρύθμισης έναρξης: ";
$langErrorReadingZipFile = "Σφάλμα ανάγνωσης αρχείου zip.";
$langErrorSql = "Σφάλμα στη δήλωση SQL";
$langErrorValuesInDouble = "Σφάλμα: μία ή δύο τιμές είναι διπλές";
$langErrortExtractingManifest = "Δεν μπορεί να εμφανιστεί απόσπασμα από το αρχείο zip.";
$langExAlreadyUsed = "Αυτή η άσκηση ήδη χρησιμοποιείται σαν ενότητα σε αυτή τη γραμμή μάθησης";
$langExInsertedAsModule = "έχει προστεθεί σαν ενότητα μαθήματος της γραμμής μάθησης";
$langExercise = "Ασκήσεις";
$langExerciseAsModule = "Χρήση Άσκησης";
$langExerciseAsModuleLabel = "Άσκησης";
$langExerciseCancelled = "Ακύρωση άσκησης, επιλέξτε την επόμενη ενότητα για να συνεχίσετε, κάνοντας κλίκ στο επόμενο βήμα.";
$langExerciseDone = "Ολοκλήρωση άσκησης, επιλέξτε την επόμενη ενότητα για να συνεχίσετε, κάνοντας κλίκ στο επόμενο βήμα.";
$langExerciseInModule = "Ασκηση στην ενότητα";
$langExercises = "Ασκήσεις";
$langExport = "Εξαγωγή";
$langExport2004 = "Εξαγωγή σε πρότυπο SCORM 2004";
$langExport12 = "Εξαγωγή σε πρότυπο SCORM 1.2";
$langFailed = "Ολοκληρώθηκε ανεπιτυχώς";
$langFileScormError = "Το αρχείο που θα ενημερωθεί δεν είναι έγκυρο.";
$langFileName = "Όνομα αρχείου";
$langFullScreen = "Πλήρης οθόνη ";
$langGlobalProgress = "Πρόοδος της γραμμής μάθησης: ";
$langImport = "Εισαγωγή";
$langInFrames = "Σε πλαίσια";
$langInfoProgNameTitle = "Πληροφορία";
$langInsertMyDescToolName = "Εισαγωγή περιγραφής μαθήματος";
$langInsertMyDocToolName = "Εισαγωγή εγγράφου";
$langInsertMyExerciseToolName = "Εισαγωγή άσκησης";
$langInsertMyLinkToolName = "Εισαγωγή συνδέσμου";
$langInsertMyModuleToolName = "Εισαγωγή ενότητας";
$langInsertMyModulesTitle = "Επαναχρησιμοποίηση ενότητας του μαθήματος";
$langInsertNewModuleName = "Εισαγωγή νέου ονόματος";
$langInstalled = "Η γραμμή μάθησης έχει εισαχθεί με επιτυχία.";
$langIntroLearningPath = "Χρησιμοποήστε αυτό το εργαλείο για να παρέχετε στους ".$langsOfStudentss." μια γραμμή μάθησης μεταξύ εγγράφων, ασκήσεων, σελίδες HTML, συνδέσεις κ.λπ.<br /><br />Εάν επιθυμείτε να παρουσιάσετε στους ".$langsOfStudentss." τη γραμμή μάθησης σας, κάντε κλικ παρακάτω.<br />";
$langLINKTypeDesc = "Σύνδεσμος";
$langLastName = "Επίθετο";
$langLastSessionTimeSpent = "Τελευταία χρονική συνεδρία";
$langLearningPath = "Γραμμή μάθησης";
$langLearningPaths = "Γραμμές μάθησης";
$langLearningPath1 = "γραμμής μάθησης";
$langLearningPathEmpty = "Η γραμμή μάθησης είναι κενή";
$langLearningPathImportFromDocuments = "Εισαγωγή γραμμής μάθησης από τα έγγραφα";
$langLearningPathList = "Διαθέσιμες γραμμές μάθησης";
$langLearningPathName = "Όνομα νέας γραμμής μάθησης";
$langLearningPathData = "Στοιχεία γραμμής μάθησης";
$langLearningObjectData = "Στοιχεία Εκπαιδευτικού Αντικείμενου";
$langLearningPathNotFound = "Η γραμμή μάθησης δεν βρέθηκε ";
$langLessonStatus = "Κατάσταση ενότητας";
$langLinkAlreadyUsed = "Αυτός ο σύνδεσμος ήδη χρησιμοποιείται σαν ενότητα σε αυτήν τη γραμμή μάθησης";
$langLinkAsModule = "Χρήση Συνδέσμου";
$langLinkAsModuleLabel = "Συνδέσμου";
$langLinkInsertedAsModule = "Έχει προστεθεί σαν ενότητα μαθήματος αυτής της γραμμής μάθησης";
$langLogin = "Είσοδος";
$langMaxFileSize = "Μέγιστο μέγεθος αρχείου: ";
$langMinuteShort = "ελαχ.";
$langModuleMoved = "Μετακίνηση ενότητας";
$langModuleOfMyCourse = "Χρήση ενότητας του μαθήματος";
$langModuleOfMyCourseLabel = "Eτικέτας του μαθήματος";
$langModuleOfMyCourseLabel_onom = "Eτικέτα του μαθήματος";
$langModuleStillInPool = "Ενότητες αυτής της γραμμής θα είναι ακόμα διαθέσιμες στο σύνολο των ενοτήτων";
$langModulesPoolToolName = "Σύνολο ενοτήτων";
$langMyCourses = "Τα μαθήματά μου";
$langNeverBrowsed = "Δεν έχει ολοκληρωθεί";
$langNewLabel = "Δημιουργία νέας ετικέτας";
$langLabel = "Eτικέτα";
$langNext = "Επόμενο";
$langNextPage = "Επόμενη Σελίδα";
$langNoEmail = "Δεν έχει οριστεί email";
$langNoLearningPath = "Δεν υπάρχουν γραμμές μάθησης";
$langNoModule = "Δεν έχουν χρησιμοποιηθεί εκπαιδευτικά αντικείμενα";
$langNoMoreModuleToAdd = "Όλες οι ενότητες αυτού του μαθήματος ήδη χρησιμοποιήθηκαν σε αυτή τη γραμμή μάθησης.";
$langNoStartAsset = "Δεν υπάρχει κανένα απόκτημα/στοιχείο έναρξης που να ορίζεται για αυτή την ενότητα.";
$langNotAttempted = "Δεν έχει επιχειρηθεί";
$langNotInstalled = "Προέκυψε σφάλμα. Η εισαγωγή της γραμμής μάθησης απέτυχε.";
$langOkChapterHeadAdded = "Ο τίτλος προστέθηκε: ";
$langOkDefaultCommentUsed = "προειδοποίηση: Η εγκατάσταση δε μπορεί να βρεί την περιγραφή της γραμμής μάθησης και έχει χρησιμοποιήσει ένα προκαθορισμένο σχόλιο.  Θα πρέπει να το αλλάξετε";
$langOkDefaultTitleUsed = "προειδοποίηση: Η εγκατάσταση δε μπορεί να βρεί το όνομα της γραμμής μάθησης και έχει ορίσει καποιο προκαθορισμένο όνομα. Θα πρέπει να το αλλάξετε.";
$langOkFileReceived = "Το αρχείο ελήφθη: ";
$langOkManifestFound = "Η ανακοίνωση βρέθηκε σε αρχείο zip: ";
$langOkManifestRead = "H ανακοίνωση διαβάστηκε.";
$langOkModuleAdded = "Προσθήκη ενότητας: ";
$langOrder = "Εντολή ";
$langOtherCourses = "Λίστα Μαθημάτων";
$langPassed = "Ολοκληρώθηκε με επιτυχία";
$langPathContentTitle = "Περιεχόμενο γραμμής μάθησης";
$langPathsInCourseProg = "Πρόοδος μαθήματος ";
$langPeriodDayShort = "μ.";
$langPeriodHourShort = "ω.";
$langPersoValue = "Αξιολόγηση";
$langPlatformAdministration = "Διαχείριση Πλατφόρμας";
$langPrevious = "Προηγούμενο";
$langPreviousPage = "Προηγούμενη Σελίδα";
$langProgInModuleTitle = "Η πρόοδος σου σε αυτή την ενότητα";
$langProgress = "Πρόοδος";
$langQuitViewer = "Επιστροφή στη λίστα";
$langRawHasBeenChanged = "Ο ελάχιστος βαθμός για προαγωγή έχει αλλαχθεί";
$langSCORMTypeDesc = "SCORM προσαρμοσμένο περιεχόμενο";
$langScormIntroTextForDummies = "Τα εισαγόμενα πακέτα πρέπει να αποτελούνται από ένα αρχείο zip και να είναι συμβατά με:
   <ul>
     <li> το SCORM 2004 ή</li>
     <li> το SCORM 1.2.</li>
   </ul>";
$langSecondShort = "δευτ.";
$langStartModule = "Έναρξη ενότητας";
$langStatsOfLearnPath = "Παρακολούθηση γραμμής μάθησης";
$langTrackAllPath = "Παρακολούθηση γραμμών μάθησης";
$langSwitchEditorToTextConfirm = "Η εντολή θα αφαιρέσει τη τρέχουσα διάταξη κειμένου. Θέλετε να συνεχίσετε;";
$langTextEditorDisable = "Απενεργοποίηση επεξεργαστή κειμένου";
$langTextEditorEnable = "Ενεργοποίηση επεξεργαστή κειμένου";
$langTimeInLearnPath = "Χρόνος στη γραμμή μάθησης";
$langTo = "στο";
$langTotalTimeSpent = "Σύνολο χρόνου";
$langTrackAllPathExplanation = "Πρόοδος ".$langsOfStudents;
$langTrackUser = "Πρόοδος ".$langOfStudent;
$langTracking = "Παρακολούθηση";
$langTypeOfModule = "Τύπος ενότητας";
$langUnamedModule = "Ενότητα χωρίς όνομα";
$langUnamedPath = "Γραμμή χωρίς όνομα";
$langUseOfPool = "Μπορείτε να δείτε όλες τις διαθέσιμες ενότητες στο μάθημα. <br /> Όποια άσκηση ή έγγραφο έχει προστεθεί στη γραμμή μάθησης εμφανίζεται παρακάτω.";
$langUsedInLearningPaths = "Αριθμός διαδρομών μάθησης που χρησιμοποιούν αυτή την ενότητα: ";
$langView = "Εμφάνιση";
$langViewMode = "Παρουσίαση τρόπου";
$langVisibility = "Ορατό / Αόρατο";
$langWork = "Εργασίες ".$langOfStudents;
$langWrongOperation = "Λανθασμένη λειτουργία";
$langYourBestScore = "Η καλύτερη σου βαθμολογία";
$lang_enroll = "Eγγραφή";
$langimportLearningPath = "Εισαγωγή γραμμής μάθησης";
$langScormErrorExport = "Σφάλμα κατά την εξαγωγή του πακέτου SCORM";

/*************************************************
* lessontools.inc.php
**************************************************/
$langActiveTools="Ενεργά εργαλεία";
$langAdministrationTools="Εργαλεία διαχείρισης";
$langAdministratorTools="Εργαλεία διαχειριστή";
$langCourseTools="Εργαλεία μαθήματος";

/**************************************************
* link.inc.php
***************************************************/
$langLinks="Σύνδεσμοι";
$langListDeleted="Ο κατάλογος έχει διαγραφεί";
$langLinkMod="Ο σύνδεσμος τροποποιήθηκε";
$langLinkModify = "Αλλαγή σύνδεσμου";
$langLinkDeleted="Ο σύνδεσμος διαγράφηκε";
$langLinkName="Όνομα συνδέσμου";
$langLinkAdd="Προσθήκη συνδέσμου";
$langLinkAdded="Ο σύνδεσμος προστέθηκε";
$langLinkDelconfirm = "Θέλετε να διαγράψετε τον σύνδεσμο;";
$langCategoryName="Όνομα κατηγορίας";
$langCategoryAdd = "Προσθήκη κατηγορίας";
$langCategoryAdded = "Η κατηγορία προστέθηκε";
$langCategoryMod = "Αλλαγή κατηγορίας";
$langCategoryModded = "Η κατηγορία άλλαξε";
$langCategoryDel = "Διαγραφή κατηγορίας";
$langCategoryDeleted = "Η κατηγορία διαγράφηκε μαζί με όλους τους συνδέσμους της";
$langCatDel = "Οταν διαγράψετε μια κατηγορία, θα διαγραφούν όλοι οι σύνδεσμοι της κατηγορίας.\\n".
"Είστε βέβαιος ότι θέλετε να διαγράψετε την κατηγορία; ";
$langAllCategoryDel = "Διαγραφή όλων των καταλόγων και όλων των συνδέσμων";
$langAllCategoryDeleted = "Όλες οι κατηγορίες και όλοι οι σύνδεσμοι έχουν διαγραφεί";
$langGiveURL = "Δώστε το URL του συνδέσμου";
$langGiveCategoryName = "Όνομα κατηγορίας";
$langNoCategory = "Γενικοί σύνδεσμοι";
$langCategorisedLinks = "Κατηγοριοποιημένοι σύνδεσμοι";
$showall = "Εμφάνιση";
$shownone = "Απόκρυψη";
$langProfNoLinksExist = "Δεν υπάρχουν σύνδεσμοι!<p><p align=\"center\"><small>Μπορείτε να χρησιμοποιήσετε τις λειτουργίες του εργαλείου για να προσθέσετε σύνδεσμους.</small>";
$langNoLinksExist = "Δεν έχουν προστεθεί σύνδεσμοι.";
$langEmptyLinkURL = "Δεν πληκτρολογήσατε το url του συνδέσμου";

/*****************************************************************
* lostpass.inc.php
*****************************************************************/
$lang_remind_pass = 'Ορισμός νέου συνθηματικού';
$lang_pass_intro = '<p>Αν έχετε ξεχάσει τα στοιχεία του λογαριασμού σας, συμπληρώστε το <em>όνομα χρήστη</em>
και την διεύθυνση ηλεκτρονικού ταχυδρομείου με την οποία είστε εγγεγραμμένος
(<em>προσοχή: αυτή που έχετε δηλώσει στην πλατφόρμα</em>).</p> <p>Στη συνέχεια θα παραλάβετε ένα μήνυμα σε αυτή τη
διεύθυνση με οδηγίες για να αλλάξετε το συνθηματικό σας.</p>';
$lang_pass_submit = 'Αποστολή';
$lang_pass_invalid_mail1 = 'H διεύθυνση ηλεκτρονικού ταχυδρομείου που δώσατε,';
$lang_pass_invalid_mail2 = 'δεν είναι έγκυρη. Αν κάνατε λάθος, δοκιμάστε ξανά. Διαφορετικά και εφόσον είστε βέβαιοι ότι έχετε λογαριασμό στην πλατφόρμα, παρακαλούμε να επικοινωνήσετε με τους διαχειριστές της πλατφόρμας';
$lang_pass_invalid_mail3 = 'δίνοντας τα στοιχεία σας όπως το ονοματεπώνυμο σας ή/και το όνομα χρήστη';
$langPassResetIntro ="
Έχει ζητηθεί να οριστεί νέο συνθηματικό πρόσβασης σας στην
πλατφόρμα τηλεκπαίδευσης $siteName. Αν δεν ζητήσατε εσείς αυτή την ενέργεια,
απλώς αγνοήστε τις οδηγίες αυτού του μηνύματος και αναφέρετε το γεγονός αυτό
στο διαχειριστή του συστήματος, στην διεύθυνση: ";
$langHowToResetTitle = "

===============================================================================
			Οδηγίες ορισμού νέου συνθηματικού
===============================================================================
";

$langPassResetGoHere = "
Για να ορίσετε νέο συνθηματικό πηγαίνετε στην παρακάτω διεύθυνση.
Αν δεν μπορείτε να μεταβείτε κάνοντας κλικ πάνω στη διεύθυνση αυτή, αντιγράψτε
την στη μπάρα διευθύνσεων του φυλλομετρητή σας. Η διεύθυνση αυτή έχει ισχύ
μίας (1) ώρας. Πέραν αυτού του χρονικού ορίου θα πρέπει να κάνετε από την αρχή
τη διαδικασία επανατοποθέτησης συνθηματικού.
";

$langPassEmail1 = "Το συνθηματικό σας έχει οριστεί ξανά επιτυχώς. Το νέο σας συνθηματικό είναι αυτό που ακολουθεί:";
$langPassEmail2 = "Για λόγους ασφάλειας, παρακαλούμε αλλάξτε το συνθηματικό το συντομότερο δυνατόν, σε κάτι
που μόνο εσείς το γνωρίζετε, μόλις συνδεθείτε στην πλατφόρμα.";
$langAccountResetSuccess1="Ο ορισμός νέου συνθηματικού σας έχει ολοκληρωθεί";
$langAccountResetInvalidLink="Ο σύνδεσμος που ακολουθήσατε δεν ισχύει πλέον. Παρακαλούμε επαναλάβετε από την αρχή την διαδικασία.";
$langAccountEmailError1 = "Παρουσιάστηκε σφάλμα κατά την αποστολή των στοιχείων σας";
$langAccountEmailError2 = "Δεν ήταν δυνατή η αποστολή των οδηγιών επανατοποθέτησης του συνθηματικού σας στη διεύθυνση";
$langAccountEmailError3 = 'Αν χρειαστεί, μπορείτε να επικοινωνήσετε με τους διαχειριστές του συστήματος στη διεύθυνση';
$lang_pass_email_ok = 'Τα στοιχεία του λογαριασμού σας βρέθηκαν και στάλθηκαν
	μέσω ηλεκτρονικού ταχυδρομείου στη διεύθυνση';
$langAccountNotFound1 = "Δε βρέθηκε λογαριασμός στο σύστημα με τα στοιχεία που δώσατε.";
$langAccountNotFound2 = "Αν παρόλα αυτά είστε σίγουρος ότι έχετε ήδη λογαριασμό, παρακαλούμε επικοινωνήστε με τους διαχειριστές του συστήματος στη διεύθυνση ";
$langAccountNotFound3 = "δίνοντας και στοιχεία που μπορούν να βοηθήσουν στο να βρούμε το λογαριασμό σας, όπως ονοματεπώνυμο, $langsFaculty, κ.λπ.";
$lang_email = "e-mail";
$lang_send = "Αποστολή";
$lang_username="Όνομα χρήστη";
$langPassCannotChange1="Το συνθηματικό αυτού του λογαριασμού δεν μπορεί να αλλαχθεί.";
$langPassCannotChange2="Ο λογαριασμός αυτός χρησιμοποιεί εξωτερική μέθοδο πιστοποίησης. Παρακαλούμε, επικοινωνήστε με το διαχειριστή στην διεύθυνση";
$langPassCannotChange3="για περισσότερες πληροφορίες.";

/******************************************************
* manual.inc.php
*******************************************************/
$langIntroMan = "Στην ενότητα αυτή υπάρχουν διαθέσιμα χρήσιμα εγχειρίδια που αφορούν την περιγραφή, τη λειτουργία και τις δυνατότητες της πλατφόρμας $siteName";
$langFinalDesc = "Αναλυτική Περιγραφή $siteName";
$langShortDesc = "Σύντομη Περιγραφή $siteName";
$langManS = "Εγχειρίδιο $langOfStudent";
$langManT = "Εγχειρίδιο $langOfTeacher";
$langOr = "ή";
$langNote = "Σημείωση";
$langAcrobat = "Για να διαβάσετε τα αρχεία PDF μπορείτε να χρησιμοποιήσετε το πρόγραμμα Acrobat Reader";
$langWhere ="που θα βρείτε";
$langHere = "εδώ";
$langTutorials = "Οδηγοί";
$langTut = "Οδηγός";
$langScormVideo = "Βιντεοπαρουσίαση";
$langIntroToCourse = "Γνωριμία με το Ηλεκτρονικό Μάθημα";
$langAdministratorCourse = "Διαχείριση Ηλεκτρονικού Μαθήματος";
$langCreateAccount = "Δημιουργία Λογαριασμού";
$langAllTutorials = "Γενικοί οδηγοί";

/*********************************************************
* opencours.inc.php
*********************************************************/
$langSelectFac="Επιλογή $langOfFaculty";
$langListFac="Κατάλογος Μαθημάτων / Επιλογή $langOfFaculty";
$listtomeis = "Τομείς";
$langDepartmentsList = "Ακολουθεί ο κατάλογος τμημάτων του ιδρύματος.
	Επιλέξτε οποιοδήποτε από αυτά για να δείτε τα διαθέσιμα σε αυτό μαθήματα.";
$langWrongPassCourse = "Λάθος συνθηματικό για το μάθημα";
$langAvCourses = "διαθέσιμα μαθήματα";
$langAvCours = "διαθέσιμο μάθημα";
$m['begin'] = 'αρχή';
$m['lessoncode'] = 'Όνομα Μαθήματος (κωδικός)';
$m['tomeis'] = 'Τομείς';
$m['tomeas'] = 'Τομέας';
$m['open'] = 'Ανοικτά μαθήματα (Ελεύθερη Πρόσβαση)';
$m['restricted'] = 'Ανοικτά μαθήματα με εγγραφή (Απαιτείται λογαριασμός χρήστη)';
$m['closed'] = 'Κλειστά μαθήματα';
$m['title'] = 'Τίτλος';
$m['description'] = 'Περιγραφή';
$m['professor'] = $langTeacher;
$m['legend'] = 'Υπόμνημα';
$m['legopen'] = 'Ανοικτό Μάθημα';
$m['legrestricted'] = 'Απαιτείται εγγραφή';
$m['legclosed'] = 'Κλειστό μάθημα';
$m['nolessons'] = 'Δεν υπάρχουν διαθέσιμα μαθήματα!';
$m['name']="Μάθημα";
$m['code']="Συνθηματικό μαθήματος";
$m['prof']=$langTeacher;
$m['mailprof'] = "Για να εγγραφείτε στο μάθημα θα πρέπει να στείλετε mail στον διδάσκοντα του μαθήματος
κάνοντας κλικ";
$m['here'] = " εδώ.";
$m['unsub'] = "Το μάθημα είναι κλειστό και δεν μπορείτε να απεγγραφείτε";

/***************************************************************
* pedasugggest.inc.php
****************************************************************/

$titreBloc = array("Περιεχόμενο Μαθήματος", "Εκπαιδευτικές Δραστηριότητες",
"Βοηθήματα", "Ανθρώπινο Δυναμικό", "Τρόποι αξιολόγησης / εξέτασης",
"Συμπληρωματικά Στοιχεία");
$titreBlocNotEditable = array(TRUE, TRUE, TRUE, TRUE, TRUE, FALSE);


/********************************************************************
* perso.inc.php
*********************************************************************/
$langPerso = "Αλλαγή εμφάνισης χαρτοφυλακίου";
$langMyPersoLessons = "ΤΑ ΜΑΘΗΜΑΤΑ ΜΟΥ";
$langMyPersoDeadlines = "ΟΙ ΔΙΟΡΙΕΣ ΜΟΥ";
$langMyPersoAnnouncements = "ΟΙ ΤΕΛΕΥΤΑΙΕΣ ΜΟΥ ΑΝΑΚΟΙΝΩΣΕΙΣ";
$langMyPersoDocs = "ΤΑ ΤΕΛΕΥΤΑΙΑ ΜΟΥ ΕΓΓΡΑΦΑ";
$langMyPersoAgenda = "Η ΑΤΖΕΝΤΑ ΜΟΥ";
$langMyPersoForum = "ΟΙ ΣΥΖΗΤΗΣΕΙΣ ΜΟΥ - ΤΕΛΕΥΤΑΙΕΣ ΑΠΟΣΤΟΛΕΣ";
$langAssignment = "Εργασία";
$langDeadline = "Λήξη";
$langNoEventsExist="Δεν υπάρχουν γεγονότα";
$langNoAssignmentsExist="Δεν υπάρχουν εργασίες προς παράδοση";
$langNoAnnouncementsExist="Δεν υπάρχουν ανακοινώσεις";
$langNoDocsExist="Δεν υπάρχουν έγγραφα";
$langNoPosts="Δεν υπάρχουν αποστολές στις περιοχές συζητήσεων";
$langNotEnrolledToLessons="Δεν είστε εγγεγραμμένος/η σε μαθήματα";
$langMore="Περισσότερα";
$langSender="Αποστολέας";
$langUnknown="μη ορισμένη";
$langDuration="Διάρκεια";
$langClassic = "Συνοπτικό";
$langModern = "Αναλυτικό";

/***********************************************************
* phpbb.inc.php
************************************************************/
$langAdm = "Διαχείριση";
$langEditDel = "αλλαγή/διαγραφή";
$langSeen = "Το έχουν δει";
$langLastMsg = "Τελευταίο μήνυμα";
$langLoginBeforePost1 = "Για να στείλετε μηνύματα, ";
$langLoginBeforePost2 = "πρέπει προηγουμένως να ";
$langLoginBeforePost3 = "κάνετε login στην πλατφόρμα";
$langPages = "Σελίδες";
$langNoForums = "Δεν υπάρχουν διαθέσιμες περιοχές συζητήσεων";
$langNoForumsCat = "Δεν υπάρχουν περιοχές συζήτησης σε αυτή την κατηγορία.";
$langNewTopic = "Νέο θέμα";
$langTopicData="Στοιχεία θέματος";
$langTopicAnswer = "Απάντηση στο θέμα συζήτησης";
$langGroupDocumentsLink = "Έγγραφα ομάδας ";
$langNotify = "Ειδοποίηση μέσω email αν σταλούν απαντήσεις";
$langNotifyActions = "Ειδοποιήσεις";
$langGoToPage = "Μετάβαση στη σελίδα";
$langClick = "Κάντε κλικ";
$langSubjectNotify = "Ειδοποίηση για καινούριο μήνυμα";
$langNewForumNotify = "Ειδοποίηση για καινούριο θέμα";
$langCatNotify = "Ειδοποίηση για καινούρια περιοχή συζητήσεων";
$langInForum = "στο θέμα";
$langInForums = "στην περιοχή συζητήσεων";
$langInCat = "στη κατηγορία";
$langBodyForumNotify = "Ένα καινούριο θέμα έχει προστεθεί";
$langBodyTopicNotify = "Μια νέα απάντηση έχει προστεθεί";
$langBodyCatNotify = "Μια νέα περιοχή συζητήσεων έχει προστεθεί";
$langOfForum = "της περιοχής συζητήσεων";
$langSubjects = "Θέματα";
$langPosts = "Αποστολές";
$langMessage = "Μήνυμα";
$langMessages = "Μηνύματα";
$langAllOfThem = "όλα";
$langBodyMessage = "Σώμα μηνύματος";
$langPostTitle = "Θέμα δημοσίευσης";
$langAnonymousExplain = 'Οι χρήστες που εμφανίζονται με διαφορετικό χρώμα δεν είναι αυτή τη στιγμή εγγεγραμμένοι στο μάθημα, ενώ όσοι έχουν την ένδειξη «Ανώνυμος» έχουν διαγραφεί από το σύστημα.';
$langNoPosts = "Δεν υπάρχουν αποστολές";
$langNoPost = "Δεν έχετε δικαίωμα αποστολής μηνυμάτων σε αυτή την περιοχή.";
$langReturnIndex = "Επιστροφή στο ευρετήριο περιοχών συζητήσεων";
$langReturnTopic = "Επιστροφή στην περιοχή συζητήσεων";
$langLastPost = "Τελευταία αποστολή";
$langNoTopics = "Δεν υπάρχουν θέματα σε αυτή την περιοχή. Μπορείτε να ξεκινήσετε ένα νέο.";
$langPrivateForum = "Αυτή η περιοχή συζητήσεων είναι <b>προσωπική</b>.";
$langPrivateNotice = "$langPrivateForum<br>Σημείωση: πρέπει να έχετε ενεργοποιημένα τα cookies για να χρησιμοποιήσετε προσωπικές περιοχές.";
$langSent = "Στάλθηκε";
$langViewMessage = "Εμφάνιση μηνύματος";
$langNotEdit = "Δεν μπορείτε να αλλάξετε μήνυμα που δεν είναι δικό σας.";
$langStored = "Το μήνυμα αποθηκεύτηκε";
$langViewMsg = " για να εμφανίσετε το μήνυμα";
$langViewMsg1 = "εμφάνιση μηνύματος";
$langDeletedMessage = "Το μήνυμα διαγράφηκε";
$langDeleteMessage = "Διαγραφή του μηνύματος";
$langEmptyMsg = "Για να στείλετε ένα μήνυμα πρέπει να γράψετε κάποιο κείμενο. Δεν μπορείτε να στείλετε κενό μήνυμα.";
$langCancelPost	= "Ακύρωση αποστολής";
$langTopicReview  = "Ανασκόπηση θέματος";
$langReply = "Απάντηση";
$langReplyEdit = "Αλλαγή απάντησης";
$langQuoteMsg = '[quote]\nΣτις $m[post_time], ο/η $m[username] έγραψε:\n$text\n[/quote]';
$langErrorConnectForumDatabase = "Παρουσιάστηκε πρόβλημα. Αδύνατη η σύνδεση με τη βάση δεδομένων του Forum.";
$langErrorTopicSelect="Η περιοχή συζητήσεων που επιλέξατε δεν υπάρχει. Παρακαλώ προσπαθήστε ξανά.";

/*****************************************************************
* questionnaire.inc.php
******************************************************************/
$langCreateSurvey = 'Δημιουργία Έρευνας Μαθησιακού Προφίλ';
$langCreatePoll = 'Δημιουργία Ερωτηματολογίου';
$langEditPoll = 'Τροποποίηση Ερωτηματολογίου';
$langQuestionnaire = "Ερωτηματολόγια";
$langSurvey = "Ερωτηματολόγιο";
$langSurveys = "Ερωτηματολόγια";
$langParticipate = "Συμμετοχή";
$langSurveysActive = "Ενεργές Έρευνες Μαθησιακού Προφίλ";
$langSurveysInactive = "Ανενεργές Έρευνες Μαθησιακού Προφίλ";
$langSurveyNumAnswers = "Απαντήσεις";
$langSurveyDateCreated = "Δημιουργήθηκε την";
$langSurveyStart = "Ξεκίνησε την";
$langSurveyEnd = "και τελείωσε την";
$langSurveyOperations = "Λειτουργίες";
$langSurveyAddAnswer = "Προσθήκη Απαντήσεων";
$langSurveyType = "Τύπος";
$langSurveyMC = "Πολλαπλής Επιλογής";
$langSurveyFillText = "Συμπληρώστε το κενό";
$langSurveyContinue = "Συνέχεια";
$langSurveyMoreAnswers ="+ απαντήσεις";
$langSurveyMoreQuestions = "+ ερωτήσεις";
$langSurveyCreated ="Η Έρευνα Μαθησιακού Προφίλ δημιουργήθηκε με επιτυχία.<br><br><a href=\"questionnaire.php\">Επιστροφή</a>";
$langSurveyCreator = "Δημιουργός";
$langSurveyCreationError = "Σφάλμα κατά την δημιουργία του Ερωτηματολογίου. Παρακαλώ προσπαθήστε ξανά.";
$langSurveyDeleted ="Η Έρευνα Μαθησιακού Προφίλ διαγράφηκε με επιτυχία.<br><br><a href=\"questionnaire.php\">Επιστροφή</a>.";
$langSurveyDeactivated ="Η Έρευνα Μαθησιακού Προφίλ απενεργοποιήθηκε με επιτυχία.";
$langSurveyActivated ="Η Έρευνα Μαθησιακού Προφίλ ενεργοποιήθηκε με επιτυχία.";
$langSurveySubmitted ="Ευχαριστούμε για την συμμετοχή σας!<br><br><a href=\"questionnaire.php\">Επιστροφή</a>.";
$langSurveyTotalAnswers = "Συνολικός αριθμός απαντήσεων";
$langSurveyNone = "Δεν έχουν δημιουργηθεί έρευνες μαθησιακού προφίλ για το μάθημα";
$langSurveyInactive = "Η Έρευνα Μαθησιακού Προφίλ έχει λήξει ή δεν έχει ενεργοποιηθεί ακόμα.";
$langSurveyCharts = "Αποτελέσματα έρευνας";
$langQPref = "Τι τύπο έρευνα μαθησιακού προφίλ επιθυμείτε;";
$langQPrefSurvey = "Έρευνα μαθησιακού προφίλ";
$langNamesSurvey = "Έρευνες Μαθησιακού Προφίλ";
$langHasParticipated = "Έχετε ήδη συμμετάσχει";
$langSurveyInfo ="Επιλέξτε ένα έτοιμο ερώτημα (σύμφωνα με το πρότυπο COLLES/ATTL)";
$langQQuestionNotGiven ="Δεν έχετε εισάγει την τελευταία ερώτηση.";
$langQFillInAllQs ="Παρακαλώ απαντήστε σε όλες τις ερωτήσεις.";

$langQuestion1= array('Σε αυτή την ενότητα, η προσπάθεια μου επικεντρώθηκε σε θέματα που με ενδιέφεραν.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);
$langQuestion2= array('Σε αυτή την ενότητα, αυτά που μαθαίνω έχουν να κάνουν με το επάγγελμά μου.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);
$langQuestion3= array('Σε αυτή την ενότητα, ασκώ κριτική σκέψη.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);

$langQuestion4= array('Σε αυτή την ενότητα, συνεργάζομαι με τους συμφοιτητές μου.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);

$langQuestion5= array('Σε αυτή την ενότητα, η διδασκαλία κρίνεται ικανοποιητική.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);

$langQuestion6= array('Σε αυτή την ενότητα, υπάρχει σωστή επικοινωνία με τον διδάσκοντα.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);
$langQuestion7= array('Προσπαθώ να βρίσκω λάθη στο σκεπτικό του συνομιλητή μου.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);
$langQuestion8= array('Όταν συζητώ μπαίνω στην θέση του συνομιλητή μου.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);

$langQuestion9= array('Μένω αντικειμενικός κατά την ανάλυση καταστάσεων.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);

$langQuestion10= array('Μου αρέσει να παίρνω τον ρόλο του συνήγορου του διαβόλου.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);

// polls
$langPollsActive = "Ενεργά Ερωτηματολόγια";
$langPollsInactive = "Ανενεργά Ερωτηματολόγια";
$langPollStart = "Έναρξη";
$langPollStarted = "Ξεκίνησε την";
$langPollEnd = "Λήξη";
$langPollEnded = "και τελείωσε την";
$langPollOperations = "Λειτουργίες";
$langPollNumAnswers = "Απαντήσεις";
$langPollAddAnswer = "Προσθήκη απαντήσεων";
$langPollType = "Τύπος";
$langPollMC = "Πολλαπλής Επιλογής";
$langPollFillText = "Συμπληρώστε το κενό";
$langPollContinue = "Συνέχεια";
$langPollMoreAnswers ="+";
$langPollAddMultiple = "Νέα ερώτηση πολλαπλής επιλογής";
$langPollAddFill = "Νέα ερώτηση συμπλήρωσης κενού";
$langPollCreated ="<p class='success_small'>Το Ερωτηματολόγιο δημιουργήθηκε με επιτυχία.<br /><a href=\"questionnaire.php\">Επιστροφή</a></p>";
$langPollEdited ="<p class='success_small'>Το Ερωτηματολόγιο τροποποιήθηκε με επιτυχία.<br /><a href=\"questionnaire.php\">Επιστροφή</a></p>";
$langPollCreator = "Δημιουργός";
$langPollCreation = "Ημ/νία Δημιουργίας";
$langPollCreateDate = "Το Ερωτηματολόγιο δημιουργήθηκε την";
$langPollCreationError = "<p class='caution_small'>Σφάλμα κατά την δημιουργία του Ερωτηματολογίου. Παρακαλώ προσπαθήστε ξανά.</p>";
$langPollDeleted ="<p class='success_small'>Το Ερωτηματολόγιο διαγράφηκε με επιτυχία. <br><a href=\"questionnaire.php\">Επιστροφή</a></p>";
$langPollDeactivated ="<p class='success_small'>Το Ερωτηματολόγιο απενεργοποιήθηκε με επιτυχία!</p>";
$langPollActivated ="<p class='success_small'>Το Ερωτηματολόγιο ενεργοποιήθηκε με επιτυχία!</p>";
$langPollSubmitted ="<p class='success_small'>Ευχαριστούμε για την συμμετοχή σας!<br /><a href=\"questionnaire.php\">Επιστροφή</a></p>";
$langPollTotalAnswers = "Συνολικός αριθμός απαντήσεων";
$langPollNone = "Δεν υπάρχουν αυτή την στιγμή διαθέσιμα Ερωτηματολόγια.";
$langPollInactive = "<p class='caution_small'>Το Ερωτηματολόγιο έχει λήξει ή δεν έχει ενεργοποιηθεί ακόμα.</p>";
$langPollHasEnded = "Έχει λήξει";
$langPollCharts = "Αποτελέσματα Ερωτηματολογίου";
$langPollUnknown = "Δεν ξέρω / Δεν απαντώ";
$langIndividuals = "Αποτελέσματα ανά χρήστη";
$langCollectiveCharts = "Συγκεντρωτικά αποτελέσματα";
$langHasNotParticipated = "Δεν έχετε συμμετάσχει";
$langThereAreParticipants = "<p class='caution_small'>Στο Ερωτηματολόγιο έχουν ήδη συμμετάσχει χρήστες. <br />Η διόρθωση των στοιχείων δεν είναι δυνατή!</p>";
$langPollEmpty = "<p class='caution_small'>Παρακαλώ προσθέστε ερωτήσεις στο Ερωτηματολόγιο!</p>";
$langPollEmptyAnswers = "<p class='caution_small'>Σφάλμα! Δεν υπάρχουν απαντήσεις στην ερώτηση</p>";

/************************************************************
* registration.inc.php
*************************************************************/
$langSee = "Προεπισκόπηση";
$langNoSee = "Απαιτείται εγγραφή";
$langCourseName = "Τίτλος Μαθήματος";
$langNoCoursesAvailable = "Δεν υπάρχουν διαθέσιμα μαθήματα για εγγραφή";
$langRegistration="Εγγραφή";
$langSurname="Επώνυμο";
$langUsername="Όνομα χρήστη (username)";
$langConfirmation="Επιβεβαίωση συνθηματικού";
$langUserNotice = "(μέχρι 20 χαρακτήρες)";
$langEmailNotice = "Το e-mail δεν είναι απαραίτητο, αλλά χωρίς αυτό δε θα μπορείτε να λαμβάνετε
ανακοινώσεις, ούτε θα μπορείτε να χρησιμοποιήσετε τη λειτουργία υπενθύμισης συνθηματικού.";
$langAm = "Αριθμός μητρώου";
$langUserDetails = "Εγγραφή $langOfStudent";
$langSubmitNew = "Υποβολή Αίτησης";
$langPassTwice="Πληκτρολογήσατε δύο διαφορετικά συνθηματικά. Χρησιμοποιήστε το πλήκτρο «επιστροφή» του browser σας και ξαναδοκιμάστε.";
$langUserFree="Το όνομα χρήστη που επιλέξατε χρησιμοποιείται!";
$langYourReg="Η εγγραφή σας στο";
$langDear="Αγαπητέ";
$langYouAreReg="\nΟ λογαριασμός σας στην πλατφόρμα";
$langSettings="δημιουργήθηκε με επιτυχία!\nΤα προσωπικά στοιχεία του λογαριασμού σας είναι τα εξής:\n\nΌνομα χρήστη:";
$langAddressOf="\n\nΗ διεύθυνση του";
$langProblem="\nΣτη περίπτωση που αντιμετωπίζετε προβλήματα, επικοινωνήστε με την Ομάδα Ασύγχρονης Τηλεκπαίδευσης";
$langFormula="\n\nΦιλικά,\n";
$langManager="\nΥπεύθυνος";
$langPersonalSettings="Οι προσωπικές σας ρυθμίσεις έχουν καταχωρηθεί και σας στάλθηκε ένα e-mail για να θυμάστε το όνομα χρήστη και το συνθηματικό σας.</p>";
$langPersonalSettingsMore= "Κάντε κλίκ <a href='../../index.php'>εδώ</a> για να εισέλθετε στο προσωπικό σας χαρτοφυλάκιο.<br>Εκεί μπορείτε:<ul><li>να περιηγηθείτε στο περιβάλλον της πλατφόρμας και τις προσωπικές σας επιλογές,</li><li>να επιλέξετε στον \"Κατάλογο Μαθημάτων\" τα μαθήματα που επιθυμείτε να παρακολουθήσετε.</li><ul>";
$langYourRegTo="Ο κατάλογος μαθημάτων σας περιέχει";
$langIsReg="έχει ενημερωθεί";
$langCanEnter="Είσοδος στην ψηφιακή αίθουσα.";
$langChoice="Επιλογή";
$langLessonName="Όνομα μαθήματος";

// profile.php
$langPassTwo="Έχετε πληκτρολογήσει δύο διαφορετικά νέα συνθηματικά";
$langAgain="Ξαναπροσπαθήστε!";
$langFields="Αφήσατε μερικά πεδία κενά";
$langEmailWrong="Η διεύθυνση ηλεκτρονικού ταχυδρομείου δεν είναι συμπληρωμένη ή περιέχει άκυρους χαρακτήρες";
$langPassChanged="Το συνθηματικό πρόσβασης στην πλατφόρμα έχει αλλάξει";
$langPassOldWrong="Το παρόν συνθηματικό πρόσβασης που δώσατε είναι λάθος";
$langNewPass1="Νέο συνθηματικό";
$langNewPass2="Νέο συνθηματικό (ξανά)";
$langInvalidCharsPass="Έχετε χρησιμοποιήσει μη επιτρεπτούς χαρακτήρες στο συνθηματικό σας";
$langInvalidCharsUsername="Έχετε χρησιμοποιήσει μη επιτρεπτούς χαρακτήρες στο όνομα χρήστη σας";
$langProfileReg="Οι αλλαγές στο προφίλ σας αποθηκεύτηκαν";
$langOldPass="Παρόν συνθηματικό";
$langChangePass="Αλλαγή συνθηματικού πρόσβασης";

// user.php
$langNewUser = "Εγγραφή Χρήστη";
$langModRight="Αλλαγή των δικαιωμάτων διαχειριστή του";
$langNone="κανένας";
$langNoAdmin="δεν έχει<b>δικαιώματα διαχειριστή σε αυτό το site</b>";
$langAllAdmin="έχει τώρα<b>όλα τα δικαιώματα διαχειριστή σε αυτό το site</b>";
$langModRole="Αλλαγή του ρόλου του";
$langRole="Ρόλος";
$langIsNow="είναι τώρα";
$langInC="σε αυτό το μάθημα";
$langFilled="Εχετε αφήσει μερικά πεδία κενά.";
$langUserNo="Το όνομα χρήστη που διαλέξατε";
$langTaken="χρησιμοποιείται ήδη. Διαλέξτε άλλο.";
$langRegYou="σας έχει εγγράψει στο μάθημα";
$langTheU="Ο χρήστης";
$langAddedU="έχει προστεθεί. Στάλθηκε ένα email σε αυτόν";
$langAndP="και το συνθηματικό τους";
$langDereg="έχει διαγραφεί από αυτό το μάθημα";
$langAddAU="Προσθέστε ένα χρήστη";
$langAdmR="Δικαιώματα Διαχειριστή";
$langAddHereSomeCourses = "<p>Για να εγγραφείτε / απεγγραφείτε σε / από ένα μάθημα,
πρώτα επιλέξτε τη $langsFaculty που βρίσκεστε και στη συνέχεια επιλέξτε / αποεπιλέξτε το μάθημα.<br>
<p>Για να καταχωρηθούν οι προτιμήσεις σας πατήστε 'Υποβολή αλλαγών'</p><br>";
$langDeleteUser = "Είστε σίγουρος ότι θέλετε να διαγράψετε τον χρήστη";
$langDeleteUser2 = "από αυτό το μάθημα";
$langSurnameName ="Ονοματεπώνυμο";
$langAskUser = "Αναζητήστε τον χρήστη που θέλετε να προστεθεί. Ο χρήστης θα πρέπει να έχει ήδη λογαριασμό στην πλατφόρμα για να εγγραφεί στο μάθημά σας.";
$langAskManyUsers = "Πληκτρολογήστε το όνομα αρχείου χρηστών ή κάντε κλικ στο πλήκτρο \"Αναζήτηση\" για να το εντοπίσετε.";
$langAskManyUsers1 = "<strong>Σημείωση</strong>:<br />1) Οι χρήστες θα πρέπει να έχουν ήδη λογαριασμό στην πλατφόρμα για να γραφτούν στον μάθημά σας.";
$langAskManyUsers2 = "2) Το αρχείο χρηστών πρέπει να είναι απλό αρχείο κειμένου με τα ονόματα των χρηστών ένα ανά γραμμή. <br /><br />
<u>Παράδειγμα</u>:
    <br>
    eleni<br>
    nikos<br>
    spiros<br>
    ";
$langAddUser = "Προσθήκη ενός χρήστη";
$langAddManyUsers  = "Προσθήκη πολλών χρηστών";
$langOneUser = "ενός χρήστη";
$langManyUsers = "πολλών χρηστών";
$langGUser = "χρήστη επισκέπτη";
$langNoUsersFound = "Δε βρέθηκε κανένας χρήστης με τα στοιχεία που δώσατε ή ο χρήστης υπάρχει ήδη στο μάθημά σας.";
$langNoUsersFound2 = "Δε βρέθηκε κανένας χρήστης με τα στοιχεία που δώσατε";
$langRegister = "Εγγραφή χρήστη στο μάθημα";
$langAdded = " προστέθηκε στο μάθημά σας.";
$langAddError = "Σφάλμα! Ο χρήστης δεν προστέθηκε στο μάθημα. Παρακαλούμε προσπαθήστε ξανά ή επικοινωνήστε με το διαχειριστή του συστήματος.";
$langAddBack = "Επιστροφή στη σελίδα εγγραφής χρηστών";
$langAskUserFile = "Όνομα αρχείου";
$langFileNotAllowed = "Λάθος τύπος αρχείου! Το αρχείο χρηστών πρέπει να είναι απλό αρχείο κειμένου με τα ονόματα
των χρηστών ανά γραμμή";
$langUserNoExist = "Ο χρήστης δεν είναι γραμμένος στην πλατφόρμα";
$langUserAlready = "Ο χρήστης είναι ήδη γραμμένος στο μάθημά σας";
$langUserFile = "Όνομα αρχείου χρηστών";

// search_user.php
$langUserNoneMasc="-";
$langTutor="Εκπαιδευτής";
$langTutorDefinition="Διδάσκων (δικαίωμα να επιβλέπει τις ομάδες χρηστών)";
$langAdminDefinition="Διαχειριστής (δικαίωμα να αλλάζει το περιεχόμενο των μαθημάτων)";
$langDeleteUserDefinition="Διαγραφή (διαγραφή από τον κατάλογο χρηστών του <b>παρόντος</b> μαθήματος)";
$langNoTutor = "δεν είναι διδάσκων σε αυτό το μάθημα";
$langYesTutor = "είναι διδάσκων σε αυτό το μάθημα";
$langUserRights="Δικαιώματα χρηστών";
$langNow="τώρα";
$langOneByOne="Προσθήκη χρήστη";
$langUserMany="Εισαγωγή καταλόγου χρηστών μέσω αρχείων κειμένου";
$langUserAddExplanation="κάθε γραμμή του αρχείου που θα στείλετε θα περιέχει 5 πεδία:
         <b>Όνομα&nbsp;&nbsp;&nbsp;Επίθετο&nbsp;&nbsp;&nbsp;
        Όνομα Χρήστη&nbsp;&nbsp;&nbsp;Συνθηματικό&nbsp;
        &nbsp;&nbsp;email</b> και θα ειναι χωρισμένο με tab.
        Οι χρήστες θα λάβουν ειδοποίηση μέσω email με το όνομα χρήστη / συνθηματικό.";
$langDownloadUserList="Ανέβασμα καταλόγου";
$langUserNumber="αριθμός";
$langGiveAdmin="Προσθήκη δικαίωματος";
$langRemoveRight="Αφαίρεση δικαίωματος";
$langGiveTutor="Προσθήκη δικαίωματος";
$langUserOneByOneExplanation="Αυτός (αυτή) θα λάβει ειδοποίηση μέσω email με όνομα χρήστη και συνθηματικό";
$langBackUser="Επιστροφή στη λίστα χρηστών";
$langUserAlreadyRegistered="Ενας χρήστης με ίδιο όνομα / επίθετο είναι ήδη γραμμένος σε αυτό το μάθημα.
                Δεν μπορείτε να τον (την) ξαναγράψετε.";
$langAddedToCourse="είναι ήδη γραμμένος στην πλατφόρμα αλλά όχι σε αυτό το μάθημα. Τώρα έγινε.";
$langGroupUserManagement="Διαχείριση ομάδας χρηστών";
$langRegDone="Οι αλλαγές σας κατοχυρώθηκαν.";
$langPassTooEasy ="Το συνθηματικό σας είναι πολύ απλό. Χρησιμοποιήστε ένα συνθηματικό σαν και αυτό";
$langChoiceLesson ="Επιλογή Μαθημάτων";
$langRegCourses = "Εγγραφή σε μάθημα";
$langChoiceDepartment ="Επιλογή $langOfFaculty";
$langCoursesRegistered="Η εγγραφή σας στα μαθήματα που επιλέξατε έγινε με επιτυχία!";
$langNoCoursesRegistered="<p>Δεν επιλέξατε μάθημα για εγγραφή.</p><p> Μπορείτε να εγγραφείτε σε μάθημα, την
επόμενη φορά που θα μπείτε στην πλατφόρμα.</p>";
$langIfYouWantToAddManyUsers="Αν θέλετε να προσθέσετε ένα κατάλογο με χρήστες στο μάθημά σας, παρακαλώ συμβουλευτείτε τον διαχειριστή συστήματος.";
$langCourse="Μάθημα";
$langLastVisits="Οι τελευταίες μου επισκέψεις";
$langLastUserVisits= "Οι τελευταίες επισκέψεις του χρήστη ";
$langDumpUser="Κατάλογος χρηστών ";
$langCsv=" σε αρχείο csv";
$langcsvenc1="κωδικοποίηση windows (windows-1253)";
$langcsvenc2="κωδικοποίηση unicode (UTF-8)";
$langFieldsMissing="Αφήσατε κάποιο(α) από τα υποχρεωτικά πεδία κενό(ά) !";
$langFillAgain="Παρακαλούμε ξανασυμπληρώστε την";
$langFillAgainLink="αίτηση";
$langReqRegProf="Αίτηση Εγγραφής $langOfTeacher";
$langProfUname="Επιθυμητό Όνομα Χρήστη (Username)";
$profreason="Αναφέρατε τους λόγους χρήσης της πλατφόρμας";
$langProfEmail="e-mail Χρήστη";
$reguserldap="Εγγραφή Χρήστη μέσω LDAP";
$langByLdap="Μέσω LDAP";
$langNewProf="Εισαγωγή στοιχείων νέου λογαριασμού $langsOfTeacher";
$profsuccess="Η δημιουργία νέου λογαριασμού $langsOfTeacher πραγματοποιήθηκε με επιτυχία!";
$langDearProf="Αγαπητέ διδάσκοντα!";
$success="Η αποστολή των στοιχείων σας έγινε με επιτυχία!";
$click="Κάντε κλίκ";
$langBackPage="για να επιστρέψετε στην αρχική σελίδα.";
$emailprompt="Δώστε την διεύθυνση e-mail σας:";
$ldapprompt="Δώστε το συνθηματικό LDAP σας:";
$univprompt="Επιλέξτε Πανεπιστημιακό Ίδρυμα";
$ldapnamesur="Ονοματεπώνυμο:";
$langInstitution='Ίδρυμα:';
$ldapuserexists="Στο σύστημα υπάρχει ήδη κάποιος χρήστης με τα στοιχεία που δώσατε.";
$ldapempty="Αφήσατε κάποιο από τα πεδία κενό!";
$ldapfound="πιστοποιήθηκε και τα στοιχεία που έδωσε είναι σωστά";
$ldapchoice="Παρακαλούμε επιλέξτε το ίδρυμα στο οποίο ανήκετε!";
$ldapnorecords="Δεν βρέθηκαν εγγραφές. Πιθανόν να δώσατε λάθος στοιχεία.";
$ldapwrongpasswd="Το συνθηματικό που δώσατε είναι λανθασμένο. Παρακαλούμε δοκιμάστε ξανά";
$ldapproblem="Υπάρχει πρόβλημα με τα στοιχεία του";
$ldapcontact="Παρακαλούμε επικοινωνήστε με τον διαχειριστή του εξυπηρέτη LDAP.";
$ldaperror="Δεν είναι δυνατή η σύνδεση στον εξυπηρέτη του LDAP.";
$ldapmailpass="Το συνθηματικό σας είναι το ίδιο με αυτό της υπηρεσίας e-mail.";
$ldapback="Επιστροφή στην";
$ldaplastpage="προηγούμενη σελίδα";
$mailsubject="Αίτηση ".$langOfTeacher." - Υπηρεσία Ασύγχρονης Τηλεκπαίδευσης";
$mailsubject2="Αίτηση ".$langOfStudent." - Υπηρεσία Ασύγχρονης Τηλεκπαίδευσης";
$contactphone="Τηλέφωνο επικοινωνίας";
$contactpoint="Επικοινωνία";
$searchuser="Αναζήτηση Καθηγητών / Χρηστών";
$typeyourmessage="Πληκτρολογήστε το μήνυμά σας παρακάτω";
$emailsuccess="Το e-mail στάλθηκε!";
$langTheTeacher = 'Ο διδάσκων';
$langTheUser = 'Ο χρήστης';
$langDestination = 'Παραλήπτης:';
$langAsProf = "ως $langsOfTeacher";
$langTel = 'Τηλ.';
$langPassSameAuth = 'Το συνθηματικό σας είναι αυτό της υπηρεσίας πιστοποίησης του λογαριασμού σας.';
$langLdapRequest = 'Υπάρχει ήδη μια αίτηση για τον χρήστη';
$langLDAPUser = 'Χρήστης LDAP';
$langLogIn = 'Σύνδεση';
$langAction = 'Ενέργεια';
$langRequiredFields = 'Τα πεδία με (*) είναι υποχρεωτικά';
$langCourseVisits = "Επισκέψεις ανά μάθημα";
$langDurationVisitsPerCourse = "Χρονική διάρκεια συμμετοχής ανά μάθημα";

// user registration
$langAuthUserName = "Δώστε το όνομα χρήστη:";
$langAuthPassword = "Δώστε το συνθηματικό σας:";
$langAuthenticateVia = "πιστοποίηση μέσω";
$langAuthenticateVia2 = "Διαθέσιμοι τρόποι πιστοποίησης στο ίδρυμα";
$langCannotUseAuthMethods = "Η εγγραφή στην πλατφόρμα, προς το παρόν δεν επιτρέπεται. Παρακαλούμε, ενημερώστε το διαχειριστή του συστήματος";
$langConfirmUser = "Έλεγχος Στοιχείων Χρήστη";
$langUserData = "Στοιχεία χρήστη";
$langUsersData = "Στοιχεία χρηστών";
$langUserAccount = "Λογαριασμός $langOfStudent";
$langProfAccount = "Λογαριασμός $langOfTeacher";
$langUserAccountInfo1 = '(Αίτηση)&nbsp;';
$langUserAccountInfo2 = '(Δημιουργία)&nbsp;';
$langUserAccountInfo3 = 'Εναλλακτικά, μπορείτε να επιλέξετε';
$langNewAccount = 'Νέος Λογαριασμός';
$langNewAccountActivation = 'Ενεργοποίηση Λογαριασμού';
$langNewUserAccountActivation = "Ενεργοποίηση Λογαριασμού $langOfStudent";
$langNewProfAccountActivation = "Ενεργοποίηση Λογαριασμού $langOfTeacher";
$langNewAccountActivation1 = "την ενεργοποίηση λογαριασμού σας";
$langUserExistingAccount = "Στοιχεία Εισόδου";

// list requests
$langDateRequest = "Ημ/νία αίτησης";
$langDateReject = "Ημ/νία απόρριψης";
$langDateClosed = "Ημ/νία κλεισίματος";
$langDateCompleted = "Ημ/νία ολοκλήρωσης";

$langDateRequest_small = "Aίτησης";
$langDateReject_small = "Aπόρριψης";
$langDateClosed_small = "Kλεισίματος";
$langDateCompleted_small = "Oλοκλήρωσης";

$langRejectRequest = "Απόρριψη";
$langListRequest = "Λίστα Αιτήσεων";
$langTeacherRequestHasDeleted = "Η αίτηση του $langsOfTeacher διαγράφηκε!";
$langRejectRequestSubject = "Απόρριψη αίτησης εγγραφής στην Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης";
$langGoingRejectRequest = "Πρόκειται να απορρίψετε την αίτηση $langsOfTeacher με στοιχεία:";
$langRequestSendMessage = "Αποστολή μηνύματος στο χρήστη στην διεύθυνση:";
$langRequestDisplayMessage = "στο μήνυμα θα αναφέρεται και το παραπάνω σχόλιο";
$langNoSuchRequest = "Δεν υπάρχει κάποια σχετική αίτηση με αυτό το ID. Δεν είναι δυνατή η επεξεργασία της αίτησης.";
$langTeacherRequestHasRejected = "Η αίτηση του $langsOfTeacher απορρίφθηκε";
$langRequestMessageHasSent = " και στάλθηκε ενημερωτικό μήνυμα στη διεύθυνση ";
$langRequestHasRejected = "Η αίτησή σας για εγγραφή στην πλατφόρμα $siteName απορρίφθηκε.";
$langRegistrationDate = "Ημερομηνία εγγραφής";
$langExpirationDate = "Ημερομηνία λήξης";
$langCourseRegistrationDate = "Ημ/νία εγγραφής στο μάθημα";
$langUnknownDate = "(άγνωστη)";
$langUserID = "Αριθμός χρήστη";
$langStudentParticipation = "Μαθήματα στα οποία συμμετέχει ο χρήστης";
$langNoStudentParticipation = "Ο χρήστης δεν συμμετέχει σε κανένα μάθημα";
$langCannotDeleteAdmin = "Ο χρήστης αυτός (με user id = 1) είναι ο βασικός διαχειριστής της πλατφόρμας και δε διαγράφεται.";
$langExpireBeforeRegister = "Σφάλμα: H ημ/νια λήξης είναι πρίν την ημ/νια εγγραφής";
$langSuccessfulUpdate = "Τα στοιχεία του χρήστη ενημερώθηκαν";
$langNoUpdate = "Δεν είναι εφικτή η ενημέρωση των στοιχείων για το χρήστη με id";
$langUpdateNoChange = "Δεν πραγματοποιήθηκε καμμία αλλαγή στα στοιχεία του χρήστη.";
$langError = "Σφάλμα";
$langRegistrationError = "Λάθος Ενέργεια. Επιστρέψτε στην αρχική σελίδα της πλατφόρμας.";
$langUserNoRequests = "Δεν Υπάρχουν Ανοικτές Αιτήσεις Φοιτητών !";
$langCharactersNotAllowed = "Δεν επιτρέπονται στο password και στο username, οι χαρακτήρες: ',\" ή \\";
$langStar2 = "Στα πεδία με (**) ";
$langEditUser = "Επεξεργασία στοιχείων χρήστη";
$langUnregForbidden = "Δεν επιτρέπεται να διαγράψετε τον χρήστη:";
$langUnregFirst = "Θα πρέπει να διαγράψετε πρώτα τον χρήστη από τα παρακάτω μαθήματα:";
$langUnregTeacher = "Είναι ".$langsTeacher." στα παρακάτω μαθήματα:";
$langPlease = "Παρακαλούμε";
$langOtherDepartments = "Εγγραφή σε μαθήματα άλλων τμημάτων/σχολών";
$langNoLessonsAvailable = "Δεν υπάρχουν Διαθέσιμα Μαθήματα.";
$langUserPermitions = "Δικαιώματα";

// formuser.php
$langUserRequest = "Αίτηση Δημιουργίας Λογαριασμού $langOfStudent";
$langUserFillData = "Συμπλήρωση στοιχείων";
$langUserOpenRequests = "Ανοικτές αιτήσεις $langOfStudents";
$langWarnReject = "Πρόκειται να απορρίψετε την αίτηση $langsOfStudent";
$langWithDetails = "με στοιχεία";
$langNewUserDetails = "Στοιχεία Λογαριασμού Χρήστη-$langOfStudent";
$langInfoProfReq = "Αν επιθυμείτε να έχετε πρόβαση στην πλατφόρμα με δικαιώματα χρήστη - $langsOfTeacher, παρακαλώ συμπληρώστε την παρακάτω αίτηση. Η αίτηση θα σταλεί στον υπεύθυνο διαχειριστή ο οποίος θα δημιουργήσει το λογαριασμό και θα σας στείλει τα στοιχεία μέσω ηλεκτρονικού ταχυδρομείου.";
$langInfoStudReg = "Αν επιθυμείτε να έχετε πρόσβαση στην πλατφόρμα με δικαιώματα χρήστη - $langsOfStudent, παρακαλώ συμπληρώστε τα στοιχεία σας στην παρακάτω φόρμα. Ο λογαριασμός σας θα δημιουργηθεί αυτόματα.";
$langReason = "Αναφέρατε τους λόγους χρήσης της πλατφόρμας";
$langInfoStudReq = "Αν επιθυμείτε να έχετε πρόβαση στην πλατφόρμα με δικαιώματα χρήστη - ".$langsOfStudent .", παρακαλώ συμπληρώστε την παρακάτω αίτηση. Η αίτηση θα σταλεί στον υπεύθυνο διαχειριστή ο οποίος θα δημιουργήσει το λογαριασμό και θα σας στείλει τα στοιχεία μέσω ηλεκτρονικού ταχυδρομείου.";
$langInfoProf = "Σύντομα θα σας σταλεί mail από την Ομάδα Διαχείρισης της Πλατφόρμας Ασύγχρονης Τηλεκπαίδευσης, με τα στοιχεία του λογαριασμού σας.";
$langDearUser = "Αγαπητέ χρήστη";
$langMailErrorMessage = "Παρουσιάστηκε σφάλμα κατά την αποστολή του μηνύματος.<br/>Η αίτησή σας καταχωρήθηκε στην πλατφόρμα, αλλά δεν στάλθηκε ενημερωτικό email στο διαχειριστή του συστήματος. <br/>Παρακαλούμε επικοινωνήστε με το διαχειριστή στη διεύθυνση:";
$langUserSuccess = "Νέος λογαριασμός $langOfStudent";
$usersuccess="Η δημιουργία νέου λογαριασμού ".$langsOfStudent." πραγματοποιήθηκε με επιτυχία!";
$langAsUser = "(Λογαριασμός $langOfStudent)";
$langChooseReg = "Επιλογή τρόπου εγγραφής";
$langTryAgain = "Δοκιμάστε ξανά!";
$langViaReq = "Εγγραφή χρηστών μέσω αίτησης";

/************************************************************
* restore_course.inc.php
*************************************************************/
$langFirstMethod = "1ος τρόπος";
$langSecondMethod = "2ος τρόπος";
$langRequest1 = "Κάντε κλικ στο Browse για να αναζητήσετε το αντίγραφο ασφαλείας του μαθήματος που θέλετε να επαναφέρετε. Μετά κάντε κλίκ στο 'Αποστολή'. ";
$langRestore = "Επαναφορά";
$langRequest2 = "Αν το αντίγραφο ασφαλείας, από το οποίο θα ανακτήσετε το μάθημα, είναι μεγάλο σε μέγεθος και δεν μπορείτε να το ανεβάσετε, τότε μπορείτε να πληκτρολογήσετε τη ακριβή διαδρομή (path) που βρίσκεται το αρχείο στον server.";
$langRestoreStep1 = "1° Ανάκτηση μαθήματος από αρχείο ή υποκατάλογο.";
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
$langUName = "Ονομάζεται";
$langInfo1 = "Το αντίγραφο ασφαλείας που στείλατε, περιείχε τις παρακάτω πληροφορίες για το μάθημα.";
$langInfo2 = "Μπορείτε να αλλάξετε τον κωδικό του μαθήματος και ότι άλλο θέλετε (π.χ. περιγραφή, καθηγητής κ.λπ.)";
$langCourseOldFac = "Παλιά $langFaculty";
$langCourseVis = "Τύπος πρόσβασης";
$langCourseType = "$langpre / $langpost";
$langPrevId = "Προηγούμενο user_id";
$langNewId = "Καινούριο user_id";
$langUsersWillAdd = "Οι χρήστες του μαθήματος θα προστεθούν";
$langUserPrefix = "Στα ονόματα χρηστών του μαθήματος θα προστεθεί ένα πρόθεμα";
$langErrorLang = "Πρόβλημα! Δεν υπάρχουν γλώσσες για το μάθημα!";

/*****************************************************************
* search.inc.php
*****************************************************************/
$langDoSearch = "Εκτέλεση Αναζήτησης";
$langSearch_terms = "Όροι Αναζήτησης: ";
$langSearchIn = "Αναζήτηση σε: ";
$langSearchWith = "Αναζήτηση με κριτήρια:";
$langNoResult = "Δεν βρέθηκαν αποτελέσματα";
$langIntroductionNote = "Εισαγωγικό Σημείωμα";
$langForum = "Περιοχή συζητήσεων";
$langOR = "Τουλάχιστον έναν από τους όρους";
$langNOT = "Κανέναν από τους ακόλουθους όρους";
$langKeywords = "Λέξεις κλειδιά";
$langTitle_Descr = "αφορά τον τίτλο ή τμήμα από τον τίτλο του μαθήματος";
$langKeywords_Descr = "κάποια λέξη ή οι λέξεις κλειδιά που προσδιορίζουν τη θεματική περιοχή του μαθήματος";
$langInstructor_Descr = "το όνομα ή τα ονόματα των καθηγητών του μαθήματος";
$langCourseCode_Descr = "ο κωδικός του μαθήματος";
$langAccessType = "Τύπος Πρόσβασης";
$langTypeClosed = "Κλειστό";
$langTypeOpen = "Ανοικτό";
$langTypeRegistration = "Ανοικτό με εγγραφή";
$langTypesRegistration = "Ανοικτά με εγγραφή";
$langAllTypes = "(όλοι οι τύποι πρόσβασης)";
$langAllFacultes = "Σε όλες τις $langsFaculties";

/*****************************************************
* speedsubsribe.inc.php
******************************************************/
$langSpeedSubscribe = "Εγγραφή σαν διαχειριστής μαθήματος";
$langPropositions="Κατάλογος με μελλοντικές προτάσεις ";
$langSuccess = "Η εγγραφή σας σαν διαχειριστής έγινε με επιτυχία";
$lang_subscribe_processing ="Διαδικασία Εγγραφής";
$langAuthRequest = "Απαιτείται εξακρίβωση στοιχείων";
$langAlreadySubscribe ="Είστε ήδη εγγεγραμμένος";
$langAs = "ως";

/************************************************************
* upgrade.inc.php
************************************************************/
$langUpgrade = "Αναβάθμιση των βάσεων δεδομένων";
$langExplUpgrade = "Το πρόγραμμα αναβάθμισης θα τροποποιήσει το αρχείο ρυθμίσεων <em>config.php</em>.
   Επομένως πριν προχωρήσετε στην αναβάθμιση βεβαιωθείτε ότι ο web server
   μπορεί να έχει πρόσβαση στο <em>config.php</em>. Για λόγους ασφαλείας, οι
   τωρινές ρυθμίσεις του <em>config.php</em> θα κρατηθούν στο αρχείο
   <em>config_backup.php</em>.";
$langExpl2Upgrade = "Επίσης για λόγους ασφαλείας βεβαιωθείτε ότι έχετε κρατήσει αντίγραφα ασφαλείας των βάσεων δεδομένων.";
$langWarnUpgrade = "ΠΡΟΣΟΧΗ!";
$langExecTimeUpgrade = "ΠΡΟΣΟΧΗ! Για να ολοκληρωθεί η διαδικασία της αναβάθμισης βεβαιωθείτε ότι η μεταβλητή <em>max_execution_time</em> που ορίζεται στο <em>php.ini</em> είναι μεγαλύτερη από 300 (= 5 λεπτά). Αλλάξτε την τιμή της και ξαναξεκινήστε την διαδικασία αναβάθμισης";
$langUpgradeCont = "Για να προχωρήσετε στην αναβάθμιση της βάσης δεδομένων, δώστε το όνομα
   χρήστη και το συνθηματικό του διαχειριστή της πλατφόρμας:";
$langUpgDetails = "Στοιχεία Εισόδου";
$langUpgMan = "οδηγίες αναβάθμισης";
$langUpgLastStep = "πριν προχωρήσετε στο παρακάτω βήμα.";
$langUpgToSee = "Για να δείτε τις αλλαγές-βελτιώσεις της καινούριας έκδοσης του eClass κάντε κλικ";
$langUpgRead = "Αν δεν το έχετε κάνει ήδη, παρακαλούμε διαβάσετε και ακολουθήστε τις";
$langSuccessOk = "Επιτυχία";
$langSuccessBad = "Σφάλμα ή δεν χρειάζεται τροποποίηση";
$langUpgAdminError = "Τα στοιχεία που δώσατε δεν αντιστοιχούν στο διαχειριστή του συστήματος! Παρακαλούμε επιστρέψτε στην προηγούμενη σελίδα και ξαναδοκιμάστε.";
$langUpgNoVideoDir = "Ο υποκατάλογος 'video' δεν υπάρχει και δεν μπόρεσε να δημιουργηθεί. Ελέγξτε τα δικαιώματα πρόσβασης.";
$langUpgNoVideoDir2 = "Υπάρχει ένα αρχείο με όνομα 'video' που εμποδίζει! Θα πρέπει να το διαγράψετε.";
$langUpgNoVideoDir3 = "Δεν υπάρχει δικαίωμα εγγραφής στον υποκατάλογο 'video'!";
$langConfigError4 = "Δεν ήταν δυνατή η πρόσβαση στον κατάλογο του αρχείο ρυθμίσεων config.php! Ελέγξτε τα δικαιώματα πρόσβασης.";
$langConfigError1 = "Δεν ήταν δυνατή η λειτουργία αντιγράφου ασφαλείας του config.php! Ελέγξτε τα δικαιώματα πρόσβασης.";
$langConfigError2 = "Το αρχείο ρυθμίσεων config.php δεν μπόρεσε να διαβαστεί! Ελέγξτε τα δικαιώματα πρόσβασης.";
$langConfigError3 = "Δεν πραγματοποιήθηκε η εγγραφή των αλλαγών στο αρχείο ρυθμίσεων config.php! Ελέγξτε τα δικαιώματα πρόσβασης.";
$langUpgradeSuccess = "Η αναβάθμιση των βάσεων δεδομένων του eClass πραγματοποιήθηκε!";
$langUpgReady = "Είστε πλέον έτοιμοι να χρησιμοποιήσετε την καινούρια έκδοση του Open eClass!";
$langUpgSucNotice = "Αν παρουσιάστηκε κάποιο σφάλμα, πιθανόν κάποιο μάθημα να μην δουλεύει εντελώς σωστά.<br>
                Σε αυτή την περίπτωση επικοινωνήστε μαζί μας στο <a href='mailto:eclass@gunet.gr'>eclass@gunet.gr</a><br>
                περιγράφοντας το πρόβλημα που παρουσιάστηκε<br> και στέλνοντας (αν είναι δυνατόν) όλα τα μηνύματα που εμφανίστηκαν στην οθόνη σας";
$langUpgCourse = "Αναβάθμιση μαθήματος";
$langUpgFileNotRead = "To αρχείο δεν μπόρεσε να διαβαστεί. Ελέγξτε τα δικαιώματα πρόσβασης.";
$langUpgFileNotModify = "Το αρχείο δεν μπόρεσε να τροποποιηθεί. Ελέγξτε τα δικαιώματα πρόσβασης.";
$langUpgNotChDir = "Δεν πραγματοποιήθηκε η αλλαγή στον κατάλογο αναβάθμισης! Ελέγξτε τα δικαιώματα πρόσβασης.";
$langUpgIndex = "Τροποποίηση αρχείου index.php του μαθήματος";
$langCheckPerm = "Ελέγξτε τα δικαιώματα πρόσβασης";
$langUpgNotIndex = "Δεν πραγματοποιήθηκε η αλλαγή στον κατάλογο του μαθήματος";
$langConfigFound = "Στο αρχείο ρυθμίσεων <tt>config.php</tt> βρέθηκαν τα παρακάτω στοιχεία επικοινωνίας.";
$langConfigMod = "Μπορείτε να τα αλλάξετε / συμπληρώσετε.";
$langUpgContact = "Στοιχεία Επικοινωνίας";
$langUpgAddress = "Διεύθυνση Ιδρύματος:";
$langUpgTel = "Τηλ. Επικοινωνίας:";
$langUpgReg = "Εγγραφή Χρηστών";
$langTable = "Πίνακας";
$langToTable = "στον πίνακα";
$langAddField = "Προσθήκη πεδίου";
$langAfterField = "μετά το πεδίο";
$langToA = "σε";
$langRenameField = "Μετονομασία πεδίου";
$langOfTable = "του πίνακα";
$langDeleteField = "Διαγραφή πεδίου";
$langDeleteTable = "Διαγραφή πίνακα";
$langMergeTables = "Ενοποίηση των πινάκων";
$langIndexExists = "Υπάρχει ήδη κάποιο index στον πίνακα";
$langIndexAdded = "Προστέθηκε index";
$langNotTablesList = "Πρόβλημα με την Β.Δ. Δεν ήταν δυνατή η εύρεση των πινάκων";
$langNotMovedDir = "Προσοχή: Δεν ήταν δυνατή η μεταφορά του υποκαταλόγου";
$langToDir = "στο φάκελο";
$langCorrectTableEntries = "Διόρθωση εγγραφών του πίνακα";
$langMoveIntroText = "Μεταφορά του εισαγωγικού κειμένου στον πίνακα";
$langEncodeDocuments = "Κωδικοποίηση των περιεχομένων του υποσυστήματος 'Έγγραφα'";
$langEncodeGroupDocuments = "Κωδικοποίηση των περιεχομένων του υποσυστήματος Ομάδες Χρηστών - 'Έγγραφα'";
$langEncodeDropBoxDocuments = "Κωδικοποίηση των περιεχομένων του υποσυστήματος Ανταλλαγή Αρχείων";
$langEncDropboxError = "Πρόβλημα κατά την μετονομασία στο υποσύστημα Ανταλλαγή Αρχείων";
$langWarnVideoFile = "Προσοχή: το αρχείο video";
$langChangeDBCharset = "Αλλαγή κωδικοποίησης βάσης δεδομένων";
$langToUTF = "σε UTF-8";
$langEncryptPass = "Κωδικοποίηση των συνθηματικών των χρηστών";
$langNotEncrypted = "ΠΡΟΣΟΧΗ! Η διαδικασία αναβάθμισης δεν μπόρεσε να κρυπτογραφήσει τα password και η πλατφόρμα δεν μπορεί να λειτουργήσει. Αφαιρέστε τη γραμμή «\$encryptedPasswd = true;» από το αρχείο ρυθμίσεων config.inc.php";
$langUpgradeStart = 'Έναρξη αναβάθμισης Open eClass';
$langUpgradeConfig = 'Αναβάθμιση αρχείου ρυθμίσεων (config.php)';

/*******************************************************************
* toolmanagement.inc.php
********************************************************************/
$langTool = "Εργαλείο";
$langUploadPage = "Ανέβασμα ιστοσελίδας";
$langAddExtLink = "Προσθήκη εξωτερικού σύνδεσμου στο αριστερό μενού";
$langDeleteLink = "Είστε βέβαιος/η ότι θέλετε να διαγράψετε τον σύνδεσμο";
$langOperations="Ενέργειες σε εξωτερικούς σύνδεσμους";
$langInactiveTools = "Ανενεργά εργαλεία";
$langSubmitChanges = "Υποβολή αλλαγών";

/********************************************************************
* trad4all.inc.php
*********************************************************************/
$iso639_2_code = "el";
$langNameOfLang['greek']="Ελληνικά";
$langNameOfLang['english']="Αγγλικά";
$langNameOfLang['spanish']="Ισπανικά";
$langNameOfLang['czech'] = "Τσέχικα";
$langNameOfLang['german'] = "Γερμανικά";
$langNameOfLang['italian'] = "Ιταλικά";
$langNameOfLang['french']="Γαλλικά";
$charset = 'UTF-8';
$dateFormatShort =  "%b %d, %y";
$dateFormatLong  = '%A, %d %B %Y';
$dateTimeFormatLong  = '%d %B %Y / Ώρα: %R';
$timeNoSecFormat = '%R';
$langNoAdminAccess = '
		<p><b>Η σελίδα
		που προσπαθείτε να μπείτε απαιτεί όνομα
		χρήστη και συνθηματικό.</b> <br/>Η πλατφόρμα σας ανακατεύθυνε αυτόματα στην αρχική σελίδα
		για να συνδεθείτε πρωτού προχωρήσετε σε άλλες ενέργειες. Πιθανόν να έληξε η σύνοδός σας.</p>
';

$langLoginRequired = '
		<p><b>Δεν είστε εγγεγραμμένος στο μάθημα και επομένως δεν μπορείτε να χρησιμοποιήσετε το αντίστοιχο υποσύστημα.</b>
		Η πλατφόρμα σας ανακατεύθυνε αυτόματα στην αρχική σελίδα
		για να εγγραφείτε στο μάθημα, αν η εγγραφή είναι ελεύθερη. </p>
';
$langSessionIsLost = "
		<p><b>Η σύνοδος σας έχει λήξει. </b><br/>Η πλατφόρμα σας ανακατεύθυνε αυτόματα στην αρχική σελίδα
		για να συνδεθείτε προτού προχωρήσετε σε άλλες ενέργειες.</p>
			";
$langCheckProf = "
		<p><b>Η ενέργεια που προσπαθήσατε να εκτελέσετε απαιτεί δικαιώματα καθηγητή. </b><br/>
		Η πλατφόρμα σας ανακατεύθυνε αυτόματα στην αρχική σελίδα
		για να συνδεθείτε ξανά.</p>
";
$langLessonDoesNotExist = "
	<p><b>Το μάθημα που προσπαθήσατε να προσπελάσετε δεν υπάρχει.</b><br/>
	Αυτό μπορεί να συμβαίνει λόγω του ότι εκτελέσατε μια μη επιτρεπτή ενέργεια ή λόγω προβλήματος
	στην πλατφόρμα.</p>
";
$langCheckAdmin = "
		<p><b>Η ενέργεια που προσπαθήσατε να εκτελέσετε απαιτεί δικαιώματα διαχειριστή. </b><br/>
		Η πλατφόρμα σας ανακατεύθυνε αυτόματα στην αρχική σελίδα
		για να συνδεθείτε, εάν είστε ο διαχειριστής της πλατφόρμας.</p>
";
$langCheckGuest = "
		<p><b>Η ενέργεια που προσπαθήσατε να εκτελέσετε δεν είναι δυνατή με δικαιώματα επισκέπτη χρήστη. </b><br/>
		Για λόγους ασφάλειας η πλατφόρμα σας ανακατεύθυνε αυτόματα στην αρχική σελίδα
		για να συνδεθείτε ξανά.</p>
";
$langCheckPublicTools = "
		<p><b>Προσπαθήσατε να αποκτήσετε πρόσβαση σε απενεργοποιημένο εργαλείο μαθήματος. </b><br/>
		Για λόγους ασφάλειας η πλατφόρμα σας ανακατεύθυνε αυτόματα στην αρχική σελίδα
		για να συνδεθείτε ξανά.</p>
";
$langWarnShibUser = "<p><b>Προειδοποίηση:</b> Επειδή η πιστοποίησή σας έγινε μέσω Shibboleth δεν έχετε αποσυνδεθεί από την πλατφόρμα!<br>Για να αποσυνδεθείτε θα πρέπει να κλείσετε τον browser σας.</p>";

$langUserBriefcase = "Χαρτοφυλάκιο χρήστη";
$langPersonalisedBriefcase = "Προσωπικό χαρτοφυλάκιο";
$langCopyrightFooter="Copyright &copy; 2003-2010 OpeneClass";
$langAdvancedSearch="Σύνθετη αναζήτηση";
$langTitle = "Τίτλος";
$langType = "Τύπος";
/***************************************************************
* unreguser.inc.php
****************************************************************/
$langBackHome = "Επιστροφή στην αρχική σελίδα";
$langAdminNo = "Ο λογαριασμός του διαχειριστή της πλατφόρμας δεν μπορεί να διαγραφεί!";
$langExplain = "Για να διαγραφείτε από την πλατφόρμα, πρέπει πρώτα να απεγγραφείτε από τα μαθήματα που είστε εγγεγραμμένος.";
$langConfirm = "Επιβεβαίωση διαγραφής λογαριασμού";
$langDelSuccess = "Ο λογαριασμός σας στην πλατφόρμα έχει διαγραφεί.";
$langThanks = "Ευχαριστούμε για τη χρήση της πλατφόρμας!";
$langNotice = "Σημείωση";
$langModifProfile="Αλλαγή του προφίλ μου";

//unregcours.php
$langUnregCours = "Απεγγραφή από μάθημα";
$langCoursDelSuccess = "Η απεγγραφή σας από το μάθημα έγινε με επιτυχία";
$langCoursError = "Σφάλμα κατά την απεγγραφή του χρήστη";
$langConfirmUnregCours = "Θέλετε σίγουρα να απεγγραφείτε από το μάθημα";

/*******************************************************************
* usage.inc.php
********************************************************************/
 $langCreateStatsGraph ="Επιλογή παραμέτρων";
 $langGDRequired = "Απαιτείται η βιβλιοθήκη GD!";
 $langPersonalStats="Τα στατιστικά μου";
 $langUserLogins="Επισκέψεις χρηστών στο μάθημα";
 $langUserDuration="Συμμετοχή χρηστών ανά μάθημα";
 $langStartDate = "Ημερομηνία Έναρξης";
 $langEndDate = "Ημερομηνία Λήξης";
 $langAllUsers = "Όλοι οι Χρήστες";
 $langAllCourses = "Όλα τα μαθήματα";
 $langSubmit = "Υποβολή";
 $langModule = "Υποσύστημα";
 $langAllModules = "Όλα τα Υποσυστήματα";
 $langValueType = "Είδος Στατιστικών";
 $langQuantity = "Ποσοτικά";
 $langProportion = "Ποσοστιαία";
 $langStatsType = "Είδος Στατιστικών";
 $langTotalVisits = "Συνολικές Eπισκέψεις";
 $langVisits = "Αριθμός Επισκέψεων";
 $langFirstLetterUser = "Πρώτο Γράμμα Επωνύμου";
 $langFirstLetterCourse = "Πρώτο Γράμμα Τίτλου";
 $langUsageVisits = "Στατιστικά Επισκεψιμότητας";
 $langFavourite = "Προτίμηση Υποσυστημάτων";
 $langFavouriteExpl = "Παρουσιάζεται η προτίμηση ενός χρήστη ή όλων των χρηστών στα υποσυστήματα μέσα σε ένα χρονικό διάστημα.";
 $langOldStats = "Εμφάνιση παλιών στατιστικών";
 $langOldStatsExpl = "Συγκεντρωτικά μηνιαία στατιστικά στοιχεία <u>παλιότερα των οκτώ μηνών</u>.";
 $langOldStatsLoginsExpl = "Συγκεντρωτικά μηνιαία στατιστικά σχετικά με τις εισόδους στην πλατφόρμα παλιότερα των οκτώ μηνών.";
 $langInterval = "Διάστημα";
 $langDaily = "Ημερήσιο";
 $langWeekly = "Εβδομαδιαίο";
 $langMonthly = "Μηνιαίο";
 $langYearly = "Ετήσιο";
 $langSummary = "Συνολικά";
 $langDurationVisits = "Χρονική Διάρκεια Επισκέψεων";
 $langDurationExpl = "Η χρονική διάρκεια των επισκέψεων σε κάθε υποσύστημα είναι σε λεπτά της ώρας και υπολογίζεται κατά προσέγγιση.";
 $langMonths[1] = "Ιαν";
 $langMonths[2] = "Φεβ";
 $langMonths[3] = "Μαρ";
 $langMonths[4] = "Απρ";
 $langMonths[5] = "Μαϊ";
 $langMonths[6] = "Ιουν";
 $langMonths[7] = "Ιουλ";
 $langMonths[8] = "Αυγ";
 $langMonths[9] = "Σεπ";
 $langMonths[10] = "Οκτ";
 $langMonths[11] = "Νοε";
 $langMonths[12] = "Δεκ";
 #for monthly report
 $langMonths['01'] = "Ιανουάριος";
 $langMonths['02'] = "Φεβρουάριος";
 $langMonths['03'] = "Μάρτιος";
 $langMonths['04'] = "Απρίλιος";
 $langMonths['05'] = "Μάιος";
 $langMonths['06'] = "Ιούνιος";
 $langMonths['07'] = "Ιούλιος";
 $langMonths['08'] = "Αύγουστος";
 $langMonths['09'] = "Σεπτέμβριος";
 $langMonths['10'] = "Οκτώβριος";
 $langMonths['11'] = "Νοέμβριος";
 $langMonths['12'] = "Δεκέμβριος";
 $langHidden = "Κλειστό";
 $langAddress = "Διεύθυνση";
 $langLoginDate = "Ημερ/νία εισόδου";
 $langNoLogins = "Δεν έχουν γίνει είσοδοι το συγκεκριμένο χρονικό διάστημα.";
 $langNoStatistics = "Δεν έχουν γίνει επισκέψεις το συγκεκριμένο χρονικό διάστημα.";
 $langStatAccueil = "Για το χρονικό διάστημα που ζητήθηκε, διατίθεται και η παρακάτω πληροφορία, για το σύνολο των χρηστών του μαθηματος:";
 $langHost = "Υπολογιστής";
 $langGroupUsage = 'Στατιστικά ομάδων χρηστών';

 #for platform Statistics
 $langUsersCourse = "Χρήστες ανά μάθημα";
 $langVisitsCourseStats = "Επισκέψεις σε σελίδες μαθημάτων";
 $langUserStats = "Στατιστικά Χρήστη";
 $langTotalVisitsCourses = "Συνολικές επισκέψεις σε σελίδες μαθημάτων";
 $langDumpUserDuration = "Στατιστικά χρήσης";
 $langDumpUserDurationToFile = "Σε αρχείο csv";
 $langCodeUTF = "κωδικοποίηση UTF-8";
 $langCodeWin= "κωδικοποίηση Windows-1253";

/****************************************************************
* video.inc.php
*****************************************************************/
$langFileNot="Το αρχείο δεν στάλθηκε";
$langTitleMod="Ο τίτλος του εγγράφου τροποποιήθηκε";
$langFAdd="Το αρχείο προστέθηκε";
$langDelF="Το αρχείο διαγράφηκε";
$langAddV="Προσθήκη βίντεο";
$langAddVideoLink="Προσθήκη συνδέσμου βίντεο";
$langsendV="Αποστολή αρχείου ήχου ή βίντεο";
$langVideoTitle="Τίτλος βίντεο";
$langDescr="Περιγραφή";
$langDelList="Διαγραφή όλων";
$langVideoMod = "Τα στοιχεία του συνδέσμου τροποποιήθηκαν";
$langVideoDeleted = "Όλοι οι σύνδεσμοι διαγράφηκαν";
$langURL="Εξωτερικός σύνδεσμος προς τον εξυπηρετητή ήχου ή βίντεο";
$langcreator="Δημιουργός";
$langpublisher="Εκδότης";
$langdate="Ημερομηνία";
$langNoVideo = "Δεν υπάρχουν διαθέσιμα βίντεο για το μάθημα";
$langEmptyVideoTitle = "Παρακαλώ πληκτρολογήστε ένα τίτλο για το βίντεο σας";

/*************************************************************
* wiki.inc.php
**************************************************************/
$langWikis = "Διαθέσιμα wiki";
$langAddImage = "Πρόσθεσε εικόνα";
$langAdministrator = "Διαχειριστής";
$langChangePwdexp = "Βάλτε δύο φορές νέο κωδικό (password) για να γίνει αλλαγή, αφήστε κενό για να κρατήσετε τον ίδιο";
$langChooseYourPassword = " Επιλέξτε ένα όνομα χρήστη και έναν κωδικό πρόσβασης για το λογαριασμό χρήστη. ";
$langCloseWindow = "Κλείστε το παράθυρο";
$langCodeUsed = "Αυτός ο επίσημος κωδικός χρησιμοποιείται ήδη από άλλο χρήστη.";
$langContinue = " Συνέχεια ";
$langCourseManager = "Διαχειριστής μαθήματος";
$langDelImage = "Διαγραφή εικόνας";
$langGroups = "Ομάδες Χρηστών";
$langGroup = "Ομάδα Χρηστών";
$langIs = "είναι";
$langLastname = "Επώνυμο";
$langLegendRequiredFields = "<span class=\"required\">*</span> δείχνει απαραίτητο πεδίο ";
$langMemorizeYourPassord = "Αποστήθισε τα, θα τα χρειαστείς την επόμενη φορά που θα μπεις σε αυτή τη σελίδα.";
$langModifyProfile = "Αλλαγή του προφίλ μου";
$langOfficialCode = "Κωδικός διαχείρισης";
$langOneResp = "Ενας από τους διαχειριστές του μαθήματος";
$langPersonalCourseList = "Προσωπική λίστα μαθήματος";
$langPreview = "Παρουσίαση/Προβολή";
$langSaveChanges = "Αποθήκευση αλλαγών";
$langTheSystemIsCaseSensitive = "(γίνεται διάκριση μεταξύ κεφαλαίων και πεζών γραμμάτων.)";
$langUpdateImage = "Αλλαγή εικόνας";
$langUserIsPlaformAdmin = "είναι διαχειριστής της πλατφόρμας ";
$langUserid = "Ταυτότητα χρήστη";
$langWikiAccessControl = "Διαχείριση ελέγχου πρόσβασης ";
$langWikiAccessControlText = "Μπορείτε να θέσετε τα δικαιώματα πρόσβασης για τους χρήστες χρησιμοποιώντας το ακόλουθο πλέγμα: ";
$langWikiAllPages = "Όλες οι σελίδες";
$langWikiBackToPage = "Πίσω στη σελίδα";
$langWikiConflictHowTo = "<p><strong>Αλλάξτε τη σύγκρουση</strong> : Η σελίδα που πρσπαθείτε φαίνετε ότι έχει τροποποιηθεί από την τελευταία φορά που την τροποποίησες.<br /><br />
Τι θέλετε να γίνει;<ul>
<li>Μπορείτε να αντιγράψετε/επικολλήσετε τις αλλαγές σας σε ένα κειμενογράφο (όπως το notepad) και κάντε κλίκ στο  'edit last version' για να προσπαθήσεις να προσθέσεις τις αλλαγές σου στην καινούργια έκδοση της σελίδας.</li>
<li>Μπορείς επίσης να πατήσεις στο άκυρο για να ακυρώσεις τις αλλαγές σου.</li>
</ul></p>";
$langWikiContentEmpty = "Αυτή η σελίδα είναι κενή, κάνε κλικ στο 'Edit this page' για να προσθεσεις περιεχομενο";
$langWikiCourseMembers = "Μέλη μαθήματος ";
$langWikiCreateNewWiki = "Δημιουργήστε ένα νέο Wiki";
$langWikiCreatePrivilege = "Δημιουργήστε σελίδες ";
$langWikiCreationSucceed = "Η δημιουργία του Wiki ήταν επιτυχημένη";
$langWikiDefaultDescription = "Εισάγετε την περιγραφή του νέου σας wiki έδω";
$langWikiDefaultTitle = "Καινούργιο Wiki";
$langWikiDeleteWiki = "Διαγραφή Wiki";
$langWikiDeleteWikiWarning = "ΠΡΟΕΙΔΟΠΟΙΗΣΗ: πρόκειται να διαγράψετε αυτό το wiki και όλες τις σελίδες του. Είστε βέβαιοι ότι θέλετε να συνεχίσετε;";
$langWikiDeletionSucceed = "Η διαγραφή του Wiki ήταν επιτυχημένη";
$langWikiDescription = "Περιγραφή του Wiki";
$langWikiDescriptionForm = "Περιγραφή Wiki";
$langWikiDescriptionFormText = "Μπορείτε να επιλέξετε έναν τίτλο για το wiki : ";
$langWikiDiffAddedLine = "Προστιθέμενη γραμμή ";
$langWikiDiffDeletedLine = "Διαγραμμένη γραμμή ";
$langWikiDiffMovedLine = "Μετακινημένη γραμμή ";
$langWikiDiffUnchangedLine = "Αμετάβλητη γραμμή ";
$langWikiDifferenceKeys = "Κλειδιά :";
$langWikiDifferencePattern = "διαφορές μεταξύ της έκδοσης %1\$s τροποποιημένης από %2\$s και της έκδοσης %3\$s τροποποιημένης απο %4\$s";
$langWikiDifferenceTitle = "Διαφορές :";
$langWikiEditConflict = "Αλλαγή σύγκρουσης";
$langWikiEditLastVersion = "Αλλαγή τελευταίας έκδοσης";
$langWikiEditPage = "Αλλαγή της σελίδας";
$langWikiEditPrivilege = "Αλλαγή σελίδων";
$langWikiEditProperties = "Αλλαγή ιδιοτήτων";
$langWikiEditionSucceed = "Οι αλλαγές πραγματοποιηθήκανε";
$langWikiGroupMembers = "Μέλη ομάδας";
$langWikiIdenticalContent = " Ίδιο περιεχόμενο <br />καμιά αλλαγή δεν αποθηκεύτηκε";
$langWikiInvalidWikiId = "Μη έγκυρο Wiki Id";
$langWikiList = "Λίστα του Wiki";
$langWikiMainPage = "Κύρια σελίδα";
$langWikiMainPageContent = "Αυτη είναι η κύρια σελίδα του Wiki %s. Επιλέξτε '''Αλλαγή της σελίδας''' για να τροποποιήσετε το περιεχόμενο.";
$langWikiNoWiki = "Δεν υπάρχει Wiki";
$langWikiNotAllowedToCreate = " Δεν επιτρέπεται να δημιουργήσεις σελίδα";
$langWikiNotAllowedToEdit = " Δεν επιτρέπεται να αλλάξεις τη σελίδα";
$langWikiNotAllowedToRead = "Δεν επιτρέπεται να διαβάσεις τη σελίδα";
$langWikiNumberOfPages = "Αριθμός σελίδων";
$langWikiOtherUsers = "Άλλοι χρήστες (*)";
$langWikiOtherUsersText = "(*) ανώνυμοι χρήστες και χρήστες που δεν είναι μέλη αυτού του μαθήματος...";
$langWikiPageHistory = "Ιστορικό σελίδας";
$langWikiPageSaved = "Η σελίδα αποθηκεύτηκε";
$langWikiPreviewTitle = "Προεπισκόπηση : ";
$langWikiPreviewWarning = " ΠΡΟΕΙΔΟΠΟΙΗΣΗ: Αυτή η σελίδα αποτελεί προεπισκόπηση.  Οι τροποποιήσεις σας στο wiki δεν έχουν αποθηκευτεί ακόμα ! Για να τις αποθηκεύσετε μη ξεχάσετε να κάνετε κλικ στο κουμπί 'save' στο τέλος της σελίδας.";
$langWikiProperties = "Ιδιότητες";
$langWikiReadPrivilege = "Διάβασε σελίδες";
$langWikiRecentChanges = "Πρόσφατες αλλαγές";
$langWikiRecentChangesPattern = "%1\$s τροποποιήθηκε στις %2\$s από %3\$s";
$langWikiShowDifferences = "Δείξε τις διαφορές";
$langWikiTitle = "Τίτλος του wiki";
$langWikiTitleEdit = "Wiki : Αλλάξετε τις ιδιότητες";
$langWikiTitleNew = "Wiki : Δημιούργησε καινούργιο Wiki";
$langWikiTitlePattern = "Wiki : %s";
$langWikiVersionInfoPattern = "(έκδοση από %1\$s τροποποιημένη απο%2\$s)";
$langWikiVersionPattern = "%1\$s απο %2\$s";
$lang_footer_p_CourseManager = "Υπεύθυνος για %s";
$lang_p_platformManager = "Διαχειριστής για το %s";
$langWikiUrl = "Όνομα συνδέσμου";
$langWikiUrlLang = "Γλώσσα συνδέσμου";
$wiki_toolbar['Strongemphasis'] = "Έντονα";
$wiki_toolbar['Emphasis'] = "Πλαγιαστά";
$wiki_toolbar['Inserted'] = "Εισαγωγή";
$wiki_toolbar['Deleted'] = "Διαγραμμένα";
$wiki_toolbar['Inlinequote'] = "Σχόλιο";
$wiki_toolbar['Code'] = "Κώδικας";
$wiki_toolbar['Linebreak'] = "Αλλαγή γραμμής";
$wiki_toolbar['Blockquote'] = "Παράγραφος";
$wiki_toolbar['Preformatedtext'] = "Μορφοποιημένο κείμενο";
$wiki_toolbar['Unorderedlist'] = "Λίστα";
$wiki_toolbar['Orderedlist'] = "Διατεταγμένη λίστα";
$wiki_toolbar['Externalimage'] = "Εξωτερική εικόνα";
$wiki_toolbar['Link'] = "Σύνδεσμος";

/*************************************************************
* work.inc.php
**************************************************************/
$langBackAssignment = "Επιστροφή στη σελίδα της εργασίας";
$m['activate'] = "Ενεργοποίηση";
$m['deactivate'] = "Απενεργοποίηση";
$m['deadline'] = "Προθεσμία υποβολής";
$m['username'] = "Όνομα ".$langsOfStudent." ";
$m['filename'] = "Όνομα αρχείου";
$m['sub_date'] = "Ημ/νία αποστολής";
$m['comments'] = "Σχόλια";
$m['gradecomments'] = "Σχόλια βαθμολογητή";
$m['addgradecomments'] = "Προσθήκη σχολίων βαθμολογητή";
$m['delete'] = "Διαγραφή";
$m['edit'] = "Αλλαγή";
$m['start_date'] = "Ημερομηνία έναρξης";
$m['grade'] = "Βαθμός";
$m['am'] = "Αρ. Mητρώου";
$m['yes'] = "Ναι";
$m['no'] = "Όχι";
$m['in'] = "σε";
$m['today'] = "σήμερα";
$m['tomorrow'] = "αύριο";
$m['expired'] = "έχει&nbsp;λήξει";
$m['submitted'] = "Έχει&nbsp;αποσταλεί";
$m['select'] = "Επιλογή";
$m['groupsubmit'] = "Υποβλήθηκε εκ μέρους της";
$m['ofgroup'] = "ομάδας";
$m['deleted_work_by_user'] = "Διαγράφηκε η προηγούμενη υποβληθείσα
	εργασία που είχατε στείλει με το αρχείο";
$m['deleted_work_by_group'] = "Διαγράφηκε η προηγούμενη εργασία που
	είχε υποβληθεί από κάποιο μέλος της ομάδας σας και βρισκόταν στο αρχείο";
$m['by_groupmate'] = 'Από άλλο μέλος της ομάδας σας';
$m['the_file'] = 'Το αρχείο';
$m['was_submitted'] = 'υποβλήθηκε στην εργασία.';
$m['group_sub'] = 'Επιλέξτε αν θέλετε να υποβάλετε το αρχείο αυτό
	εκ μέρους της ομάδας σας';
$m['group'] = 'ομάδα';
$m['already_group_sub'] = 'Έχει ήδη υποβληθεί η εργασία αυτή από κάποιο
	μέλος της ομάδας σας';
$m['group_or_user'] = 'Τύπος εργασίας';
$m['group_work'] = 'Ομαδική';
$m['user_work'] = 'Ατομική';
$m['submitted_by_other_member'] = 'Το αρχείο αυτό υποβλήθηκε από άλλο μέλος της';
$m['your_group'] = 'ομάδας σας';
$m['this_is_group_assignment'] = 'Η εργασία αυτή είναι ομαδική. Για να
	στείλετε κάποιο αρχείο, πηγαίνετε στο';
$m['group_documents'] = 'χώρο αρχείων της ομάδας σας';
$m['select_publish'] = 'και επιλέξτε «Δημοσίευση» για το αρχείο που θέλετε.';
$m['noguest'] = 'Για να αποστείλετε εργασία πρέπει να συνδεθείτε ως κανονικός χρήστης.';
$m['one_submission'] = 'Έχει υποβληθεί μία εργασία';
$m['more_submissions'] = 'Έχουν υποβληθεί %d εργασίες';
$m['plainview'] = 'Συνοπτική λίστα εργασιών - βαθμολογίας';
$m['WorkInfo']= 'Στοιχεία εργασίας';
$m['WorkView']= 'Εργασίες μαθήματος';
$m['WorkDelete']= 'Διαγραφή εργασίας';
$m['WorkEdit']= 'Τροποποίηση εργασίας';
$m['SubmissionWorkInfo']= 'Στοιχεία υποβολής εργασίας';
$m['SubmissionStatusWorkInfo']= 'Κατάσταση υποβολής εργασίας';
$langGroupWorkIntro = '
	Παρακάτω εμφανίζονται οι διαθέσιμες εργασίες που έχουν ανατεθεί
	στα πλαίσια αυτού του μαθήματος. Παρακαλούμε επιλέξτε την εργασία όπου θέλετε
	να αποστείλετε το αρχείο ως εργασία της ομάδας σας, και συμπληρώστε
	τυχόν σχόλια που θέλετε να διαβάσει ο διδάσκων του μαθήματος. Σημειώστε ότι
	αν στείλετε ένα αρχείο για εργασία που έχει ήδη υποβληθεί κάποιο αρχείο ως
	ομαδική εργασία, είτε από εσάς είτε από κάποιο άλλο μέλος της ομάδας, το
	αρχείο αυτό θα χαθεί και θα αντικατασταθεί από το νέο. Τέλος,
	δεν μπορείτε να στείλετε αρχείο σε εργασία που έχει ήδη βαθμολογηθεί
	από τον διδάσκοντα.';

$langGroupWorkSubmitted = "Έχει&nbsp;αποσταλεί";
$langGroupWorkSubmitted1 = "ΔΕΝ έχει&nbsp;αποσταλεί";
$langGroupWorkDeadline_of_Submission = "Προθεσμία υποβολής";
$langEmptyAsTitle = "Δεν συμπληρώσατε τον τίτλο της εργασίας";
$langEditSuccess = "Η διόρθωση των στοιχείων της εργασίας έγινε με επιτυχία!";
$langEditError = "Παρουσιάστηκε πρόβλημα κατά την διόρθωση των στοιχείων !";
$langNewAssign = "Δημιουργία Εργασίας";
$langDeleted = "Η εργασία διαγράφηκε";
$langDelAssign = "Διαγραφή Εργασίας";
$langDelWarn1 = "Πρόκειται να διαγράψετε την εργασία με τίτλο";
$langDelSure = "Είστε σίγουρος;";
$langWorkFile = "Αρχείο";
$langZipDownload = "Κατέβασμα όλων των εργασιών σε αρχείο .zip";
$langDelWarn2 = "Έχει αποσταλεί μία εργασία ".$langsOfStudent.". Το αρχείο αυτό θα διαγραφεί!";
$langDelTitle = "Προσοχή!";
$langDelMany1 = "Έχουν αποσταλεί";
$langDelMany2 = "εργασίες ".$langsOfStudents.". Τα αρχεία αυτά θα διαγραφούν!";
$langSubmissions = "Εργασίες που έχουν υποβληθεί";
$langSubmitted = "Η εργασία αυτή έχει ήδη υποβληθεί.";
$langNotice2 = "Ημερομηνία αποστολής";
$langNotice3 = "Αν στείλετε κάποιο άλλο αρχείο, το αρχείο που υπάρχει
	αυτή τη στιγμή θα διαγραφεί και θα αντικατασταθεί με το νέο.";
$langSubmittedAndGraded = "Η εργασία αυτή έχει ήδη υποβληθεί και βαθμολογηθεί.";
$langSubmissionDescr = "Ο ".$langsStudent." %s, στις %s, έστειλε το αρχείο με όνομα \"%s\".";
$langEndDeadline = "(η προθεσμία έχει λήξει)";
$langWEndDeadline = "(η προθεσμία λήγει αύριο)";
$langNEndDeadLine = "(η προθεσμία λήγει σήμερα)";
$langDays = "ημέρες)";
$langDaysLeft = "(απομένουν";
$langGrades = "H βαθμολογία σας κατοχυρώθηκε με επιτυχία";
$langUploadSuccess = "Το ανέβασμα της εργασίας σας ολοκληρώθηκε με επιτυχία !";
$langUploadError = "Πρόβλημα κατά το ανέβασμα της εργασίας!";
$langWorkGrade = "Η εργασία έχει βαθμολογηθεί με βαθμό";
$langGradeComments = "Τα σχόλια του βαθμολογητή ήταν:";
$langGradeOk = "Καταχώρηση αλλαγών";
$langGroupSubmit = "Αποστολή ομαδικής εργασίας";
$langGradeWork = "Σχόλια βαθμολογίας";
$langUserOnly="Για να υποβάλλετε μια εργασία πρέπει να κάνετε login στη πλατφόρμα.";
$langNoSubmissions = "Δεν έχουν υποβληθεί εργασίες";
$langNoAssign = "Δεν υπάρχουν εργασίες";
$langWorkWrongInput = 'Ο βαθμός πρέπει να είναι νούμερο. Παρακαλώ επιστρέψτε και ξανασυμπληρώστε το πεδίο.';
$langWarnForSubmissions = "Αν έχουν υποβληθεί εργασίες, αυτες θα διαγραφούν";
$langAssignmentActivated = "Η εργασία ενεργοποιήθηκε";
$langAssignmentDeactivated = "Η εργασία απενεργοποιήθηκε";
$langSaved = "Τα στοιχεία της εργασίας αποθηκεύτηκαν";
$langExerciseNotPermit="Η υποβολή της εργασίας δεν επιτρέπεται!";
$langGraphResults="Κατανομή βαθμολογιών εργασίας";

/*************************************************************
* listerqusers.php
**************************************************************/
$langRequestStudent="Η αίτηση του $langsOfStudent έκλεισε!";
$langRequestReject="Η αίτηση απορρίφθηκε";
$langInformativeEmail="Στάλθηκε ενημερωτικό μύνημα στη διεύθυνση";

/*************************************************************
editpost.php
**************************************************************/
$langErrorDataOne="Λάθος στην ανάκτηση των στοιχείων από τη βάση δεδομένων";
$langErrorDataTwo="Λάθος στην ανάκτηση των στοιχείων από τη βάση δεδομένων";
$langUnableUpadatePost="Αδύνατο να ανανεωθεί το μήνυμά σας στη βάση δεδομένων ";
$langUnableUpadateTopic="Αδύνατο να ανανέωθεί το θέμα σας στη βάση δεδομένων";
$langUnableDeletePost="Αδύνατο να διαγραφεί το μήνυμα σας στη βάση δεδομένων";
$langPostRemoved="Αδύνατο να ανανεωθεί το προηγούμενο μηνυμα σας - το τελευταίο μήνυμα έχει μεταφερθεί ";
$langUnableDeleteTopic="Αδύνατο να διαγραφεί το θέμα απο τη βάση δεδομένων ";
$langTopicInformation="Δεν ήταν δυνατή η ερώτηση στην βάση δεδομένων.";
$langErrorTopicSelect="Το θέμα της περιοχής συζητήσεων που επιλέξατε δεν υπάρχει. Παρακαλώ επιστρέψτε και προσπαθήστε πάλι.";
$langUserTopicInformation="<p>Δεν ήταν δυνατή η ερώτηση στην βάση δεδομένων.";

/*************************************************************
newtopic.php
**************************************************************/
$langErrorDataForum="Τα δεδομένα του forum δεν είναι διαθέσιμα.";
$langErrorPost="Η περιοχή συζητήσεων που προσπαθείτε να συμμετάσχετε δεν υπάρχει. Παρακαλώ προσπαθήστε ξανά.";
$langErrorEnterTopic="Αδύνατη η εισαγωγή θέματος στη Βάση Δεδομένων.";
$langErrorEnterPost="Αδύνατη η εισαγωγή μυνήματος στη Βάση Δεδομένων.";
$langErrorEnterTextPost="Αδύνατη η εισαγωγή κειμένου!";
$langErrorEnterTopicTable="Αδύνατη η ανανέωση του θέματος!";
$langErrorUpdatePostCount="Αδύνατη η ανανέωση του μετρητή μυνημάτων .";

/*************************************************************
vietopic.php
**************************************************************/
$langErrorConnectPostDatabase="Παρουσιάστηκε πρόβλημα. Αδύνατη η σύνδεση με τη βάση δεδομένων των μυνημάτων.";

/*************************************************************
vietopic.php
**************************************************************/
$langAddTime="Προσθήκη ενεργού χρόνου στους απενεργοποιημένους λογαριασμούς.";
$langRealised="Πραγματοποιήθηκαν";
$langUpdates="ενημερώσεις";
$langNoChanges="Πρόβλημα! Δεν πραγματοποιήθηκε καμία αλλαγή!";


/**********************************************************************
units.php
***********************************************************************/
$langAddUnit = "Προσθήκη θεματικής ενότητας";
$langEditUnit = "Επεξεργασία θεματικής ενότητας";
$langUnitTitle = "Τίτλος θεματικής ενότητας";
$langUnitDescr = "Σύντομη περιγραφή";
$langUnitUnknown = "Άγνωστη θεματική ενότητα";
$langEmptyUnitTitle = "Παρακαλώ πληκτρολογήστε τον τίτλο της θεματικής ενότητας";
$langCourseUnits = "Θεματικές ενότητες μαθήματος";
$langCourseUnitDeleted = "Η θεματική ενότητα διαγράφηκε";
$langCourseUnitAdded = "Η θεματική ενότητα προστέθηκε";
$langCourseUnitModified = "Τα στοιχεία της ενότητας τροποποιήθηκαν";
$langResourceCourseUnitDeleted = "Ο πόρος της θεματικής ενότητας διαγράφηκε";
$langResourceUnitModified = "Τα στοιχεία του πόρου της ενότητας τροποποιήθηκαν";
$langInsertText = "κειμένου";
$langInsertDoc = "εγγράφου";
$langInsertExercise = "άσκησης";
$langInsertVideo = "βίντεο";
$langInsertForum = "περιοχής συζητήσεων";
$langInsertWork = 'εργασίας';
$langInsertWiki = 'wiki';
$langUnknownResType = "Πρόβλημα! Άγνωστος πόρος θεματικής ενότητας";
$langNoExercises = "Δεν υπάρχουν ασκήσεις";

$langVia = 'μέσω';
$langStudentViewEnable = "Μετάβαση σε περιβάλλον<br /><b>$langsOfStudent</b>";
$langStudentViewDisable = "Επιστροφή σε περιβάλλον<br /><b>$langsOfTeacher</b>";

/**********************************************************************
 BetaCMS Bridge
***********************************************************************/
$langBrowseBCMSRepo = "Γέφυρα BetaCMS";
$langNeedAllowUrlInclude = "Πρέπει να ενεργοποιήσετε το PHP option allow_url_include για να λειτουργήσει η γέφυρα BetaCMS";
$langNeedAllowUrlFopen = "Πρέπει να ενεργοποιήσετε το PHP option allow_url_fopen για να λειτουργήσει η γέφυρα BetaCMS";
$langFailConnectBetaCMSBridge = "Αποτυχία: αδυναμία σύνδεσης με το απομακρυσμένο αποθετήριο BetaCMS";
$langBetaCMSLogout = "Έξοδος από το BetaCMS";
$langBetaCMSCreateNewLesson = "Δημιουργία νέου μαθήματος στο BetaCMS";
$langBetaCMSId = "Id Μαθήματος";
$langBetaCMSTitle = "Τίτλος Μαθήματος";
$langBetaCMSDescription = "Περιγραφή Μαθήματος";
$langBetaCMSKeywords = "Λέξεις-Κλειδιά Μαθήματος";
$langBetaCMSCopyright = "Πνευματικά Δικαιώματα Μαθήματος";
$langBetaCMSAuthors = "Συγγραφική Ομάδα Μαθήματος";
$langBetaCMSProject = "Έργο Μαθήματος";
$langBetaCMSComments = "Σχόλια Μαθήματος";
$langBetaCMSActions = "Ενέργειες";
$langBetaCMSLoginProperties = "Ιδιότητα Αποθετηρίου BetaCMS και της Γέφυρας PHP";
$langBetaCMSBridgeHost = "Bridge Host";
$langBetaCMSContext = "Bridge Context";
$langBetaCMSHost = "BetaCMS Host";
$langBetaCMSRepository = "BetaCMS Repository";
$langBetaCMSUsername = "BetaCMS Username";
$langBetaCMSPassword = "BetaCMS Password";
$langBetaCMSLessonCreatedOK = "Το μάθημα δημιουργήθηκε επιτυχώς!";
$langBetaCMSLessonCreateFail = "Αποτυχία δημιουργίας μαθήματος";
$langBetaCMSRedirectAfterImport = "Παρακαλώ χρησιμοποιήστε το εργαλείο δημιουργίας μαθήματος για να εισάγετε το επιλεγμένο μάθημα. Αν ο περιηγητής ιστού δε σας ανακατευθύνει αυτόματα, πατήστε";
$langBetaCMSRedirectHere = "εδώ";
$langBetaCMSEclassLessonObjectView = "Προεπισκόπηση Ψηφιακού Αντικειμένου Μαθήματος";
$langBetaCMSTotalNumber = "συνολικός αριθμός";
$langBetaCMSUnits = "Ενότητες";
$langBetaCMSUnitTitle = "Τίτλος Ενότητας";
$langBetaCMSUnitDescription = "Περιγραφή Ενότητας";
$langBetaCMSScormFiles = "Αρχεία Scorm";
$langBetaCMSSourceFilename = "Όνομα Αρχείου";
$langBetaCMSMimeType = "Mime Type";
$langBetaCMSCalculatedSize = "Μέγεθος Αρχείου";
$langBetaCMSDocumentFiles = "Έγγραφα";
$langBetaCMSUnitScormFiles = "Αρχεία Scorm Eνότητας";
$langBetaCMSUnitDocumentFiles = "Έγγραφα Ενότητας";
$langBetaCMSUnitTexts = "Κείμενα Ενότητας";
$langBetaCMSText = "Κείμενο";

$langNoCookies = 'Προσοχή! Έχετε απενεργοποιημένα τα cookies στο πρόγραμμα πλοήγησης που χρησιμοποιείτε. Η σύνδεση δεν είναι δυνατή.';
