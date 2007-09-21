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

/*********************************************
* about.inc.php
*********************************************/

$langIntro = "Η πλατφόρμα <b>$siteName</b> είναι ένα ολοκληρωμένο Σύστημα Διαχείρισης Ηλεκτρονικών Μαθημάτων και υποστηρίζει την Υπηρεσία Ασύγχρονης Τηλεκπαίδευσης στο <a href=\"$InstitutionUrl\" target=\"_blank\" class=mainpage>$Institution</a>.";
$langVersion="Έκδοση του eClass";
$langAboutText="Η έκδοση της πλατφόρμας είναι";
$langEclassVersion="2.0";
$langEClass = "eClass";
$langHostName="Ο υπολογιστής στον οποίο βρίσκεται η πλατφόρμα είναι ο ";
$langWebVersion="Xρησιμοποιεί ";
$langMySqlVersion="και MySql  ";
$langNoMysql="Η MySql δεν λειτουργεί !";
$langUptime = "Λειτουργεί από τις";
$langTotalHits = "Συνολικές προσβάσεις";
$langLast30daysLogins = "Συνολικές προσβάσεις στην πλατφόρμα τις τελευταίες 30 μέρες";
$langTotalCourses = "Αριθμός μαθημάτων";
$langInfo = "Ταυτότητα Πλατφόρμας";
$langAboutCourses = "Η πλατφόρμα υποστηρίζει συνολικά";
$langAboutUsers = "H πλατφόρμα διαθέτει";

#For the logged-out user:
$langAboutCourses1 = "Αυτή τη στιγμή, η πλατφόρμα διαθέτει συνολικά";
$langAboutUsers1 = "Οι εγγεγραμένοι χρήστες είναι ";
$langLast30daysLogins1 = "και οι συνολικές προσβάσεις στην πλατφόρμα τις τελευταίες 30 μέρες είναι ";

$langProf = "Καθηγητές";
$langStud = "Φοιτητές";
$langGuest = "Επισκέπτες Φοιτητές";
$langAnd = "και";
$langCourses = "μαθήματα";
$langClosed = "κλειστά";
$langOpen = "ανοιχτά";
$langSemiopen = "απαιτούν εγγραφή";
$langUsers = "χρήστες";
$langSupportUser = "Υπεύθυνος Υποστήριξης:";

/********************************************
* addadmin.inc.php
*********************************************/

$langNomPageAddHtPass = "Προσθήκη διαχειριστή";
$langPassword = "Συνθηματικό";
$langAdd = "Προσθήκη";
$langTheUser = "Ο χρήστης";
$langNotFound = "δεν βρέθηκε";
$langWith = "με";
$langDone = "έγινε διαχειριστής.";
$langError = "Σφάλμα: ο χρήστης δεν προστέθηκε στους διαχειριστές. Πιθανόν να είναι ήδη διαχειριστής.";
$langInsertUserInfo = "Εισαγωγή στοιχείων χρήστη";

/****************************************************
* admin.inc.php
****************************************************/
// index
$langAdmin = "Εργαλεία Διαχείρισης Πλατφόρμας";
$langAdminBy = "Διαχείριση από ";
$langTools = "Διαχείριση πλατφόρμας eClass";
$langState = "Διαχείριση Εξυπηρετητή";
$langDevAdmin ="Διαχείριση Βάσης Δεδομένων";
$langNomPageAdmin 		= "Διαχείριση";
$langSysInfo  			= "Πληροφορίες Συστήματος";
$langCheckDatabase  	= "Ελεγχος κύριας βάσης δεδομένων";
$langStatOf 			= "Στατιστικά του ";
$langSpeeSubscribe 		= "Εγγραφή σαν Διαχειριστής Μαθήματος";
$langLogIdentLogout 	= "Καταγραφή των εισόδων και εξόδων από το σύστημα";
$langPlatformStats 		= "Στατιστικά Πλατφόρμας";
$langPlatformGenStats   = "Γενικά στατιστικά";
$langVisitsStats        = "Στατιστικά επισκέψεων";
$langMonthlyReport      = "Μηνιαίες αναφορές";
$langReport             = "Αναφορά για το μήνα ";
$langNoReport           = "Δεν υπάρχουν διαθέσιμα στοιχεία για το μήνα ";
$langEmailNotSend = "Σφάλμα κατά την αποστολή e-mail στη διεύθυνση";
$langFound = "Βρέθηκαν";

$langListCours = "Λίστα Μαθημάτων / Ενέργειες";
$langListUsers = "Λίστα Χρηστών / Ενέργειες";
$langSearchUser = "Αναζήτηση Χρήστη";
$langInfoMail = "Ενημερωτικό email";
$langProfReg = "Εγγραφή Καθηγητή";
$langProfOpen = "Αιτήσεις Καθηγητών";
$langUserOpen = "Αιτήσεις Φοιτητών";
$langListFaculte = "Λίστα Σχολών / Ενέργειες";
$langPHPInfo = "Πληροφορίες για την PHP";
$langManuals = "Διαθέσιμα Εγχειρίδια";
$langAdminManual = "Εγχειρίδιο Διαχειριστή";
$langConfigFile = "Αρχείο ρυθμίσεων";
$langDBaseAdmin = "Διαχείριση Β.Δ. (phpMyAdmin)";
$langActions = "Ενέργειες";
$langAdminProf = "Διαχείριση Καθηγητών";
$langAdminUsers = "Διαχείριση Χρηστών";
$langAdminCours = "Διαχείριση Μαθημάτων";

$langGenAdmin="Άλλα Εργαλεία";
$langBackAdmin = "Επιστροφή στη σελίδα διαχείρισης";

$langPlatformIdentity = "Ταυτότητα Πλατφόρμας";
$langStoixeia = "Στοιχεία Πλατφόρμας";
$langThereAre = "Υπάρχουν";
$langOpenRequests = "Ανοικτές αιτήσεις καθηγητών";
$langNoOpenRequests = "Δεν βρέθηκαν ανοικτές αιτήσεις καθηγητών";
$langInfoAdmin  = "Ενημερωτικά Στοιχεία για τον Διαχειριστή";
$langLastLesson = "Τελευταίο μάθημα που δημιουργήθηκε:";
$langLastProf = "Τελευταία εγγραφή εκπαιδευτή:";
$langLastStud = "Τελευταία εγγραφή εκπαιδευομένου:";
$langAfterLastLogin = "Μετά την τελευταία σας είσοδο έχουν εγγραφεί στην πλατφόρμα:";
$langOtherActions = "Άλλες Ενέργειες";

// Stat
$langStat4eClass = "Στατιστικά πλατφόρμας";
$langNbProf = "Αριθμός καθηγητών";
$langNbStudents = "Αριθμός φοιτητών";
$langNbLogin = "Αριθμός εισόδων";
$langNbCourses = "Αριθμός μαθημάτων";
$langNbVisitors = "Αριθμός επισκεπτών";
$langToday   ="Σήμερα";
$langLast7Days ="Τελευταίες 7 μέρες";
$langLast30Days ="Τελευταίες 30 μέρες";
$langNbAnnoucement = "Αριθμός ανακοινώσεων";
$langNbUsers = "Αριθμός χρηστών";
$langCoursVisible = "Ορατότητα";
$langCoursType = "Τύπος";
$langOthers = "Διάφορα σύνολα";
$langCoursesPerDept = "Αριθμός μαθημάτων ανά τμήμα";
$langCoursesPerLang = "Αριθμός μαθημάτων ανά γλώσσα";
$langCoursesPerVis= "Αριθμός μαθημάτων ανά κατάσταση ορατότητας";
$langCoursesPerType= "Αριθμός μαθημάτων ανά τύπο μαθημάτων";
$langUsersPerCourse= "Αριθμός χρηστών ανά μάθημα";
$langErrors = "Σφάλματα:";
$langMultEnrol = "Πολλαπλές εγγραφές χρηστών";
$langMultEmail= "Πολλαπλές εμφανίσεις διευθύνσεων e-mail";
$langMultLoginPass = "Πολλαπλά ζεύγη LOGIN - PASS";
$langError = "Προσοχή!";
$langOk = "Εντάξει";
$langNumUsers = "Αριθμός συμμετεχόντων στην πλατφόρμα";
$langNumGuest = "Αριθμός επισκεπτών";
$langAddAdminInApache ="Προσθήκη Διαχειριστή";
$langRestoreCourse = "Ανάκτηση Μαθήματος";
$langStatCour = "Ποσοτικά στοιχεία μαθημάτων";
$langNumCourses = "Αριθμός μαθημάτων";
$langNumEachCourse = "Αριθμός μαθημάτων ανά τμήμα";
$langNumEachLang = "Αριθμός μαθημάτων ανά γλώσσα";
$langNunEachAccess = "Αριθμός μαθημάτων ανά τύπο πρόσβασης";
$langNumEachCat = "Αριθμός μαθημάτων ανά τύπο μαθημάτων";
$langAnnouncements = "Ανακοινώσεις";
$langNumEachRec = "Αριθμός εγγραφών ανά μάθημα";
$langFrom = "Από";
$langNotExist = "Δεν υπάρχουν!";
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

// listusers
$langBegin="αρχή";
$langEnd = "τέλος";
$langPreced50 = "Προηγούμενοι 50";
$langFollow50 = "Επόμενοι 50";
$langAll="όλοι";

// listcours
$langOpenCourse = "Ανοιχτό";
$langClosedCourse = "Κλειστό";
$langRegCourse = "Απαιτείται Εγγραφή";

// quotacours
$langQuotaAdmin = "Διαχείριση Αποθηκευτικού Χώρου Μαθήματος";
$langQuotaSuccess = "Η αλλαγή έγινε με επιτυχία";
$langQuotaFail = "Η αλλαγή δεν έγινε!";
$langTheCourse = "Το μάθημα";
$langMaxQuota = "έχει μέγιστο επιτρεπτό αποθηκευτικό χώρο";
$langLegend = "Για το υποσύστημα";
$langDocument = "Έγγραφα";
$langDropbox = "Χώρος Ανταλλαγής Αρχείων";
$langVideo = "Βίντεο";
$langGroup = "Ομάδες Χρηστών";

// Added by vagpits
// General
$langReturn = "Επιστροφή";
$langReturnToSearch = "Επιστροφή στα αποτελέσματα αναζήτησης";
$langReturnSearch = "Επιστροφή στην αναζήτηση";
$langChange = "Αλλαγή";
$langNoChangeHappened = "Δεν πραγματοποιήθηκε καμία αλλαγή!";

// addfaculte
$langFaculteCatalog = "Κατάλογος Σχολών";
$langFaculteDepartment = "Σχολή / Τμήμα";
$langFaculteDepartments = "Σχολές / Τμήματα";
$langManyExist = "Υπάρχουν";
$langReturnToAddFaculte = "Επιστροφή στην προσθήκη τμήματος";
$langFaculteAdd = "Προσθήκη Τμήματος";
$langAcceptChanges = "Επικύρωση Αλλαγών";

// addusertocours
$langQuickAddDelUserToCours = "Γρήγορη εγγραφή - διαγραφή εκπαιδευομένων - εκπαιδευτών";
$langQuickAddDelUserToCoursSuccess = "Η διαχείριση χρηστών ολοκληρώθηκε με επιτυχία!";
$langFormUserManage = "Φόρμα Διαχείρισης Χρηστών";
$langListNotRegisteredUsers = "Λίστα Μη Εγγεγραμμένων Χρηστών";
$langStudents = "Εκπαιδευόμενοι";
$langProfessors = "Εκπαιδευτές";
$langListRegisteredStudents = "Λίστα Εγγεγραμμένων Εκπαιδευομένων";
$langListRegisteredProfessors = "Λίστα Εγγεγραμμένων Εκπαιδευτών";

// delcours
$langCourseDel = "Διαγραφή μαθήματος";
$langCourseDelSuccess = "Το μάθημα διαγράφηκε με επιτυχία!";
$langCourseDelConfirm = "Επιβεβαίωση Διαγραφής Μαθήματος";
$langCourseDelConfirm2 = "Θέλετε σίγουρα να διαγράψετε το μάθημα με κωδικό";
$langWarning = "Προσοχή!";
$langNoticeDel = " Η διαγραφή του μαθήματος θα διαγράψει επίσης τους εγγεγραμμένους φοιτητές από το μάθημα, την αντιστοιχία του μαθήματος στο Τμήμα, καθώς και όλο το υλικό του μαθήματος.";

// editcours
$langCourseEdit = "Επεξεργασία Μαθήματος";
$langCourseInfo = "Στοιχεία Μαθήματος";
$langDepartment = "Τμήμα";
$langCourseCode = "Κωδικός";
$langDidaskon = "Διδάσκων";
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

// listreq.php
$langOpenProfessorRequests = "Ανοικτές Αιτήσεις Καθηγητών";
$langProfessorRequestClosed = "Η αίτηση του καθηγητή έκλεισε !";
$langReqHaveClosed = "Αιτήσεις που έχουν κλείσει";
$langReqHaveBlocked = "Αιτήσεις που έχουν απορριφθεί";
$langReqHaveFinished = "Αιτήσεις που έχουν ολοκληρωθεί";
$langemailsubjectBlocked = "Απόρριψη αίτησης εγγραφής στην Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης";
$langemailbodyBlocked = "Η αίτησή σας για εγγραφή στην πλατφόρμα eClass απορρίφθηκε.";
$langCloseConf = "Επιβεβαίωση κλεισίματος αίτησης";
// mailtoprof.php
$langSendMessageTo = "Αποστολή μηνύματος";
$langToAllUsers = "σε όλους τους χρήστες";
$langProfOnly = "μόνο στους εκπαιδευτές";

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
$langAuthActivate = "Ενεργοποίηση";
$langAuthDeactivate = "Απενεργοποίηση";
$langChooseAuthMethod = "Επιλέξτε τον τρόπο πιστοποίησης χρηστών και καθορίστε τις ρυθμίσεις του";
$langConnYes = "ΕΠΙΤΥΧΗΣ ΣΥΝΔΕΣΗ";
$langConnNo = "H ΣΥΝΔΕΣΗ ΔΕΝ ΛΕΙΤΟΥΡΓΕΙ";
$langAuthNoValidUser = "Μη έγκυρος χρήστης.Αδύνατη η εγγραφή";
$langConnTest = "Γίνεται δοκιμή του τρόπου πιστοποίησης...";
$langAuthMethod = "Τρόπος πιστοποίησης χρηστών";
$langdbhost = "Διακομιστής Database";
$langdbname = "Όνομα Database";
$langdbuser = "Χρήστης Database";
$langdbpass = "Συνθηματικό χρήστη Database";
$langdbtable = "Όνομα πίνακα Database";
$langdbfielduser = "Όνομα πεδίου Χρήστη στον πίνακα";
$langdbfieldpass = "Όνομα πεδίου Συνθηματικού Χρήστη στον πίνακα";
$langInstructions = "Οδηγίες διασύνδεσης και χρήσης";
$langTestAccount = "Για να ενεργοποιηθεί ο τρόπος πιστοποίησης είναι απαραίτητο να κάνετε μια δοκιμαστική χρήση με ένα λογαριασμό της μεθόδου που επιλέξατε";
$langpop3host = "Διακομιστής POP3";
$langpop3port = "Πόρτα υπηρεσίας POP3";
$langimaphost = "Διακομιστής IMAP";
$langimapport = "Πόρτα υπηρεσίας IMAP";
$langldap_host_url = "Διακομιστής LDAP";
$langldap_bind_dn = "Ορίσματα για LDAP binding";
$langldap_bind_user = "Username για LDAP binding";
$langldap_bind_pw = "Password για LDAP binding";
$langUserAuthentication = "Πιστοποίηση Χρηστών";
$langSearchCourses = "Αναζήτηση Μαθημάτων";
$langSettings = "Ρυθμίσεις";
$langActSuccess = "Μόλις ενεργοποιήσατε την ";
$langDeactSuccess = "Μόλις απενεργοποιήσατε την ";
$langThe = "Η ";
$langActFailure = "δεν μπορεί να ενεργοποιηθεί, διότι δεν έχετε καθορίσει τις ρυθμίσεις του τρόπου πιστοποίησης";

// other
$langTeachers = "Εκπαιδευτές";
$langVisitors = "Επισκέπτες";
$langTeacher = "Εκπαιδευτής";
$langVisitor = "Επισκέπτης";
$langOther = "Άλλο";
$langTotal = "Σύνολο";
$langProperty = "Ιδιότητα";
$langUser2 = "χρήστη";
$langStat = "Στατιστικά";
$langNoUserList = "Δεν υπάρχουν αποτελέσματα πρός εμφάνιση";
$langContactAdmin = "Αποστολή ενημερωτικού email στον Διαχειριστή";
$langActivateAccount = "Παρακαλώ να ενεργοποιήσετε το λογαριασμό μου";
$langLessonCode = "Κωδικός μαθήματος";

// unregister
$langConfirmDelete = "Επιβεβαίωση διαγραφής";
$langConfirmDeleteQuestion1 = "Θέλετε σίγουρα να διαγράψετε τον χρήστη";
$langConfirmDeleteQuestion2 = "από το μάθημα με κωδικό";
$langQueryMark = ";";
$langCannotDeleteAdmin = "Προσπαθήσατε να διαγράψετε τον χρήστη με user id = 1(Admin)!";
$langUserWithId = "Ο χρήστης με id";
$langWasDeleted = "διαγράφηκε";
$langWasAdmin = "ήταν διαχειριστής";
$langWasCourseDeleted = "διαγράφηκε από το Μάθημα";
$langErrorDelete = "Σφάλμα κατά τη διαγραφή του χρήστη";
$langAfter = "Μετά από";
$langBefore = "Πρίν από";
$langUserType = "Τύπος χρήστη";
$langStudent2 = "Εκπαιδευόμενος";

// search
$langSearchUsers = "Αναζήτηση Χρηστών";
$langInactiveUsers = "Μη ενεργοί χρήστες";
$langAddSixMonths = "Προσθήκη χρόνου:6 μήνες";

// list requests
$langListRequests = "Ανοικτές Αιτήσεις Καθηγητών";

// eclassconf
$langRestoredValues = "Επαναφορά προηγούμενων τιμών";
$langEclassConf = "Αρχείο ρυθμίσεων του eClass";
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
$langAdminAnnDel = "Η ανακοίνωση διαχειριστή διαγράφτηκε";
$langAdminAnnMes = "τοποθετήθηκε την";

$langAdminAnnTitle = "Τίτλος";
$langAdminAnnBody = "Ανακοίνωση";
$langAdminAnnComm = "Σχόλια";

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
$langLasting="Διάρκεια";
$langDateNow = "Σημερινή ημερομηνία:";
$langCalendar = "Ημερολόγιο";
$langNow = "Σημερινή Ημερομηνία";
$langAddEvent="Προσθήκη ενός γεγονότος";
$langDetail="Λεπτομέρειες";
$langChooseDate = "Επιλέξτε Ημερομηνία";
$langOldToNew = "Αντιστροφή σειράς παρουσίασης";
$langNewToOld = "Αντιστροφή σειράς παρουσίασης";

$langStoredOK="Το γεγονός αποθηκεύτηκε";
$langDeleteOK="Το γεγονός διαγράφηκε";

$langNoEvents = "Δεν υπάρχουν γεγονότα";
$langSureToDel = "Είστε σίγουρος ότι θέλετε να διαγράψετε το γεγονός με τίτλο";
$langDelete = "Διαγραφή";

/***********************************************************
* announcements.inc.php
************************************************************/

$langAn ="Ανακοινώσεις";
$langOn="Σε";
$langRegUser="εγγεγραμμένους χρήστες του μαθήματος";
$langUnvalid="έχουν άκυρη διεύθυνση email ή δεν έχουν καθόλου";
$langModifAnn="Αλλαγή της ανακοίνωσης";
$langAnnouncement = "Ανακοίνωση";
$langMove = "Μετακίνηση";

$langAnnEmpty="Τα περιεχόμενα του καταλόγου ανακοινώσεων διαγράφτηκαν";
$langAnnModify="η ανακοίνωση άλλαξε";
$langAnnAdd="Η ανακοίνωση προστέθηκε";
$langAnnDel="η ανακοίνωση διαγράφηκε";
$langPubl="αναρτήθηκε την";
$langAddAnn="Προσθήκη Ανακοίνωσης";
$langContent="Περιεχόμενο";
$langAnnTitle = "Τίτλος Ανακοίνωσης";
$langAnnBody = "Σώμα Ανακοίνωσης";
$langEmptyAnn="Διαγραφή καταλόγου ανακοινώσεων";

$professorMessage="Μήνυμα καθηγητή";
$langEmailSent=" και στάλθηκε στους εγγεγραμμένους μαθητές";

$langEmailOption="Αποστολή (με email) της ανακοίνωσης στους εγγεγραμμένους μαθητές";
$langUp = "Επάνω";
$langDown = "Κάτω";
$langNoAnnounce = "Δεν υπάρχουν ανακοινώσεις";
$langSureToDelAnnounce = "Είστε σίγουρος ότι θέλετε να διαγράψετε την ανακοίνωση";
$langSureToDelAnnounceAll = "Είστε σίγουρος ότι θέλετε να διαγράψετε όλες τις ανακοινώσεις";

// my announcements
$langtheCourse = "Μάθημα";
$langAnn = "Ανακοινώθηκε την";
$langTitulaire = "Διδάσκων";


/*******************************************
* archive_course.inc.php
*******************************************/

$langArchiveCourse = "Αντίγραφο Ασφαλείας";
$langCreatedIn = "δημιουργήθηκε την";
$langCreateMissingDirectories ="Δημιουργία των καταλόγων που λείπουν";
$langCopyDirectoryCourse = "Αντιγραφή των αρχείων του μαθήματος";
$langDisk_free_space = "Ελεύθερος χώρος";
$langBuildTheCompressedFile ="2ο - Δημιουργία του αρχείου αντίγραφου ασφαλείας";
$langFileCopied = "αρχεία αντιγράφτηκαν";
$langArchiveLocation="Τοποθεσία";
$langSizeOf ="Μέγεθος του";
$langArchiveName ="Όνομα";
$langBackupSuccesfull = "Δημιουργήθηκε με επιτυχία το αντίγραφο ασφαλείας!";
$langBUCourseDataOfMainBase = "Αντίγραφο ασφαλείας των δεδομένων του μαθήματος";
$langBUUsersInMainBase = "Αντίγραφο ασφαλείας των χρηστών του μαθήματος";
$langBUAnnounceInMainBase="Αντίγραφο ασφαλείας των ανακοινώσεων του μαθήματος";
$langBackupOfDataBase="Αντίγραφο ασφαλείας της βάσης δεδομένων του μαθήματος";
$langDownload = "Κατεβάστε το ";
$langBackupEnd = "Ολοκληρώθηκε το αντίγραφο ασφαλείας σε μορφή";

/*********************************************
* auth_methods.inc.php
**********************************************/

$langViaeClass = "μέσω eClass";
$langViaPop = "με πιστοποίηση μέσω POP3";
$langViaImap = "με πιστοποίηση μέσω IMAP";
$langViaLdap = "με πιστοποίηση μέσω LDAP";
$langViaDB = "με πιστοποίηση μέσω άλλης Βάσης Δεδομένων";

$langHasActivate = "O τρόπος πιστοποίησης που επιλέξατε, έχει ενεργοποιηθεί";
$langAlreadyActiv = "O τρόπος πιστοποίησης που επιλέξατε, είναι ήδη ενεργοποιημένος";
$langErrActiv ="Σφάλμα! Ο τρόπος πιστοποίησης δεν μπορεί να ενεργοποιηθεί";
$langAuthSettings = "Ρυθμίσεις πιστοποίησης";
$langWrongAuth = "Πληκτρολογήσατε λάθος όνομα χρήστη / συνθηματικό";

/************************************************************
 * conference.inc.php
 *
 * @author Dimitris Tsachalis <ditsa@ccf.auth.gr>
 * @version $Id$
 *
 * @abstract
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
 $langVideo_content="<p align='justify'>Εδώ θα παρουσιαστεί το βίντεο αφού την ενεργοποιήσει ο καθηγητής.</p>";

 $langTeleconference_content1 = "<p align='justify'>Εδώ θα παρουσιαστεί η τηλεδιάσκεψη αφού την ενεργοποιήσει ο καθηγητής.</p>";
 $langTeleconference_content_noIE="<p align='justify'>Η τηλεδιάσκεψη ενεργοποιείται μόνο αν έχετε IE ως πλοηγό.</p>";


 $langWashVideo="Παύση μετάδοσης";
 $langPresantation_content="<p align='center'>Εδώ θα παρουσιαστεί μία ιστοσελίδα που θα επιλέξει ο καθηγητής.</p>";
 $langWashPresanation="Παύση μετάδοσης";
 $langSaveChat="Αποθήκευση κουβέντας";
 $langSaveMessage="Η κουβέντα αποθηκεύτηκε στα Έγγραφα.";
 $langSaveErrorMessage="Η κουβέντα δεν μπόρεσε να αποθηκευτή";

/*****************************************************************
* copyright.inc.php
******************************************************************/

$langCopyright = "Πληροφορίες Πνευματικών Δικαιωμάτων";

$langCopyrightNotice = '
eClass © 2003 - 2007 <a href="http://www.gunet.gr/" target=_blank>Ακαδημαϊκό Διαδίκτυο GUnet</a>.<br>&nbsp;<br>
Η <a href="http://portal.eclass.gunet.gr" target=_blank>πλατφόρμα eClass</a>
είναι ένα ολοκληρωμένο Σύστημα Διαχείρισης Ηλεκτρονικών Μαθημάτων και αποτελεί
την πρόταση του Ακαδημαϊκού Διαδικτύου GUnet για την υποστήριξη της Υπηρεσίας
Ασύγχρονης Τηλεκπαίδευσης. Aναπτύχθηκε και υποστηρίζεται ενεργά από την Ομάδα
Ασύγχρονης Τηλεκπαίδευσης του GUnet και <a
href="http://download.eclass.gunet.gr" target="_blank">διανέμεται ελεύθερα</a>
ως Λογισμικό Ανοικτού Κώδικα σύμφωνα με τη γενική δημόσια άδεια GNU General
Public License (GNU GPL).<br><br>
Το περιεχόμενο των Ηλεκτρονικών Μαθημάτων που φιλοξενεί η πλατφόρμα eClass, καθώς και τα πνευματικά δικαιώματα του υλικού αυτού, ανήκουν στους συγγραφείς τους και το GUnet δεν διεκδικεί δικαιώματα σε αυτό. Για οποιαδήποτε χρήση ή αναδημοσίευση του περιεχομένου παρακαλούμε επικοινωνήστε με τους υπεύθυνους των αντίστοιχων Mαθημάτων.
';

/*******************************************************
* course_description.inc.php
*******************************************************/

$langCourseProgram = "Περιγραφή Μαθήματος";
$langThisCourseDescriptionIsEmpty = "Το μάθημα δεν διαθέτει περιγραφή";
$langEditCourseProgram = "Δημιουργία και διόρθωση";
$langQuestionPlan = "Ερώτηση στον διδάσκοντα";
$langInfo2Say = "Πληροφορία για τους φοιτητές";
$langContenuPlan = "";
$langAddCat = "Κατηγορία";
$langBackAndForget ="Ακύρωση και επιστροφή";
$langBlockDeleted = "Η παρακάτω περιγραφή διαγράφηκε!";

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
$langIdentity = "Ταυτότητα Μαθήματος";


/*********************************************
* course_info.inc.php
*********************************************/
$langCourseIden = "Ταυτότητα Μαθήματος";
$langBackupCourse="Αντίγραφο ασφαλείας του μαθήματος";
$langModifInfo="Διαχείριση Μαθήματος";
$langModifDone="Η πληροφορία έχει αλλάξει";
$langHome="Επιστροφή στην αρχική σελίδα";
$langCode="Κωδικός Μαθήματος";
$langDelCourse="Διαγραφή του μαθήματος";
$langDelUsers="Διαγραφή χρηστών από το μάθημα";
$langTitle="Τίτλος Μαθήματος";
$langFaculty="Τμήμα";
$langDescription="Περιγραφή";
$langConfidentiality="Πρόσβαση στο μάθημα";
$langPrivOpen="Ανοικτό με Εγγραφή (Ελεγχόμενη Πρόσβαση με ανοικτή εγγραφή)";
$langPrivate="Κλειστό (Πρόσβαση στο μάθημα έχουν μόνο οι χρήστες που βρίσκονται στη <a href=../user/user.php>Λίστα
Χρηστών</a>)";
$langForbidden="Μη επιτρεπτή ενέργεια";
$langConfTip="Επιλέξτε τον τύπο πρόσβασης του μαθήματος από τους χρήστες.";
$langOptPassword = "Προαιρετικό συνθηματικό: ";
// delete_course.php
$langModifGroups="Ομάδες Εργασίας";
$langCourse="Tο μάθημα";
$langHasDel="έχει διαγραφεί";
$langByDel="Διαγράφοντας το μάθημα θα διαγραφούν μόνιμα όλα τα περιεχόμενα του και όλοι οι φοιτητές που είναι γραμμένοι σε αυτό (δεν θα διαγραφούν από τα άλλα μαθήματα).<p>Θέλετε πράγματι να διαγράψετε το";
$langTipLang="Επιλέξτε την γλώσσα στην οποία θα εμφανίζονται τα μηνύματα του μαθήματος.";

// deluser_course.php
$langConfirmDel = "Επιβεβαίωση διαγραφής μαθήματος";
$langUserDel="Πρόκειται να διαγράψετε όλους τους μαθητές από το μάθημα (δεν θα διαγραφτούν από τα άλλα μαθήματα).<p>Θέλετε πράγματι να προχωρήσετε στη διαγραφή τους από το μάθημα";

// refresh course.php
$langRefreshCourse = "Ανανέωση μαθήματος";
$langRefreshInfo="Προκειμένου να προετοιμάσετε το μάθημα για μια νέα ομάδα φοιτητών μπορείτε να διαγράψετε το παλιό περιεχόμενο. Επιλέξτε ποιες ενέργειες θέλετε να πραγματοποιηθούν.";
$langUserDelCourse="Διαγραφή χρηστών από το μάθημα";
$langUserDelNotice = "Σημ.: Οι χρήστες δεν θα διαγραφούν από τα άλλα μαθήματα";
$langAnnouncesDel = "Διαγραφή ανακοινώσεων του μαθήματος";
$langAgendaDel = "Διαγραφή εγγραφών από την ατζέντα του μαθήματος";
$langHideDocuments = "Απόκρυψη των εγγράφων του μαθήματος";
$langHideWork = "Απόκρυψη των εργασιών του μαθήματος";
$langSubmitActions = "Εκτέλεση ενεργειών";
$langOptions = "Επιλογές";
$langRefreshSuccess = "Η ανανέωση του μαθήματος ήταν επιτυχής. Εκτελέσθηκαν οι ακόλουθες ενέργειες:";
$langUsersDeleted="Οι χρήστες διαγράφηκαν από το μάθημα";
$langAnnDeleted="Οι ανακοινώσεις διαγράφηκαν από το μάθημα";
$langAgendaDeleted="Οι εγγραφές της ατζέντας διαγράφηκαν από το μάθημα";
$langWorksDeleted="Οι εργασίες απενεργοποιήθηκαν";
$langDocsDeleted="Τα έγγραφα απενεργοποιήθηκαν";


/****************************************************
* create_course.inc.php
*****************************************************/

$langDescrInfo="Σύντομη περιγραφή του μαθήματος";
$langCreateSite="Δημιουργία ενός μαθήματος";
$langFieldsRequ="Όλα τα πεδία είναι υποχρεωτικά!";
$langFieldsOptional = "Προαιρετικά πεδία";
$langFieldsOptionalNote = "Σημ. μπορείτε να αλλάξετε οποιεσδήποτε από τις παρακάτω πληροφορίες αργότερα";
$langEx="π.χ. <i>Ιστορία της Τέχνης</i>";
$langFac="Σχολή / Τμήμα";
$langDivision = "Τομέας";
$langTargetFac="Η σχολή ή το τμήμα που υπάγεται το μάθημα";
$langMax="με λατινικά γράμματα μέχρι 12 χαρακτήρες, π.χ. <i>FYS1234</i>";
$langDoubt="Αν δεν ξέρετε το κωδικό του μαθήματος συμβουλευτείτε";
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
$langErrorDir = "Ο υποκατάλογος του μαθήματος δεν δημιουργήθηκε και το μάθημα δεν θα λειτουργήσει! <br><br>Ελέγξτε τα δικαιώματα πρόσβασης του καταλόγου <em>courses</em>.";

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

$langLinks="Σύνδεσμοι";
$langDoc="Έγγραφα";
$langVideoLinks="Βιντεοσκοπημένα Μαθήματα";
$langWorks="Εργασίες";
$langForums="Περιοχή Συζητήσεων";
$langExercices="Ασκήσεις";
$langAddPageHome="Ανέβασμα Ιστοσελίδας";
$langLinkSite="Προσθήκη συνδέσμου στην αρχική σελίδα";
$langModifyInfo= "Διαχείριση Μαθήματος";
$langDropBox = "Ανταλλαγή Αρχείων";
$langLearnPath = "Γραμμή Μάθησης";
$langWiki = "Σύστημα Wiki";
$langToolManagement = "Ενεργοποίηση Εργαλείων";
$langUsage = "Στατιστικά Χρήσης";
$langCourseDesc = "Περιγραφή Μαθήματος";

$langVideoText="Παράδειγμα ενός αρχείου RealVideo. Μπορείτε να ανεβάσετε οποιοδήποτε τύπο αρχείου βίντεο (.mov, .rm, .mpeg...), εφόσον οι φοιτητές έχουν το αντίστοιχο plug-in για να το δούν";
$langGoogle="Γρήγορη και Πανίσχυρη μηχανής αναζήτησης";
$langIntroductionText="Εισαγωγικό κείμενο του μαθήματος. Αντικαταστήτε το με το δικό σας, κάνοντας κλίκ στην <b>Αλλαγή</b>.";
$langIntroductionTwo="Αυτή η σελίδα επιτρέπει οποιοδήποτε φοιτητή να ανεβάσει ένα αρχείο στο μάθημα. Μπορείτε να στείλετε αρχεία HTML, μόνο αν δεν έχουν εικόνες.";
$langCourseDescription="Γράψτε μια περιγραφή η οποία θα εμφανίζεται στο κατάλογο μαθημάτων .";
$langProfessor="Καθηγητής";
$langAnnouncementEx="Παράδειγμα ανακοίνωσης. Μόνο ο καθηγητής και τυχόν άλλοι διαχειριστές του μαθήματος μπορεί να εισαγάγουν ανακοινώσεις.";
$langJustCreated="Μόλις δημιουργήσατε με επιτυχία το μάθημα με τίτλο ";
 // Groups
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
$langcourse_references="Συμπληρωματικά στοιχεία";
$langcourse_keywords="Λέξεις κλειδιά";
$langNextStep="Επόμενο βήμα";
$langPreviousStep="Προηγούμενο βήμα";
$langFinalize="Δημιουργία μαθήματος!";
$langCourseCategory="Η κατηγορία στην οποία ανήκει το μάθημα";
$langProfessorsInfo="Ονοματεπώνυμα καθηγητών του μαθήματος χωρισμένα με κόμματα (π.χ.<i>Νίκος Τζικόπουλος, Κώστας Αδαμόπουλος</i>)";

$langPublic="Ανοικτό (Ελεύθερη Πρόσβαση από τη αρχική σελίδα χωρίς συνθηματικό)";
$langPrivate="Κλειστό (Πρόσβαση στο μάθημα έχουν μόνο οι χρήστες που βρίσκονται στη Λίστα Χρηστών)";
$langAlertTitle = "Παρακαλώ συμπληρώστε τον τίτλο του μαθήματος!";
$langAlertProf = "Παρακαλώ συμπληρώστε τον διδάσκοντα του μαθήματος!";

/******************************************************
* document.inc.php
******************************************************/

$langDownloadFile= "Ανέβασμα αρχείου στον εξυπηρέτη";
$langDownload="Ανέβασμα";
$langCreateDir="Δημιουργία καταλόγου";
$langName="Όνομα";
$langNameDir="Όνομα του καινούριου καταλόγου";
$langSize="Μέγεθος";
$langDate="Ημερομηνία";
$langMoveFrom = "Μετακίνηση του αρχείου";
$langRename="Μετονομασία";
$langOkComment="Επικύρωση αλλαγών"; //"Προσθήκη / Αλλαγή";
$langVisible="Ορατό / Αόρατο";
$langCopy="Αντιγραφή";
$langNoSpace="Η αποστολή του αρχείου απέτυχε. Έχετε υπερβεί το μέγιστο επιτρεπτό
	χώρο. Για περισσότερες πληροφορίες, επικοινωνήστε με το διαχειριστή του συστήματος.";
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
$langAddComment="Προσθήκη / αλλαγή πληροφοριών στο έγγραφο: ";
$langViMod="Η ορατότητα του εγγράφου άλλαξε";
$langElRen="Το αντικείμενο μετονομάστηκε";
$langMoveOK="Μεταφορά επιτυχής!";
$langMoveNotOK="Μεταφορά ανεπιτυχής";

// Special for group documents
$langGroupManagement="Διαχείριση Ομάδας Χρηστών";
$langGroupSpace="Περιοχή ομάδας χρηστών";
$langGroupSpaceLink="Ομάδα χρηστών";
$langGroupForumLink="Περιοχή συζητήσεων ομάδας χρηστών";
$langZipNoPhp="Το αρχείο zip δεν πρέπει να περιέχει αρχεία .php";
$langUncompress="αποσυμπίεση του αρχείου (.zip) στον εξυπηρέτη <small>(*)</small>";
$langDownloadAndZipEnd="Το αρχείο .zip ανέβηκε και αποσυμπιέστηκε";

$langPublish = "Δημοσίευση";
$langParentDir = "αρχικό κατάλογο";
$langNoticeGreek = "(*) Προσοχή! Το όνομα του αρχείου δεν πρέπει να περιέχει ελληνικούς χαρακτήρες";
$langInvalidDir = "Ακυρο ή μη υπαρκτό όνομα καταλόγου";

//prosthikes gia v2 - metadata
$langCategory="Κατηγορία";
//$langCreator=""; //den xrhsimopoieitai giati o creator einai o diaxeirisths pou kanei upload
$langCreatorEmail="Ηλ. Διεύθυνση Συγγραφέα";
$langFormat="Τυπος-Κατηγορία";
$langSubject="Θέμα";
$langAuthor="Συγγραφέας";
$langCopyrighted="Πνευματικά Δικαιώματα";
$langCopyrightedFree="Ελεύθερο";
$langCopyrightedNotFree="Προστατευμένο";
$langCopyrightedUnknown="Αγνωστο";
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
$dropbox_lang["dropbox"] = 'Χώρος Ανταλλαγής Αρχείων';
$dropbox_lang["help"] = 'Βοήθεια';
$dropbox_lang['aliensNotAllowed'] = "Μόνο οι εγγεγραμμένοι χρήστες στην πλατφόρμα μπορούν να χρησιμοποιούν το dropbox. Δεν είστε εγγεγραμμένος χρήστης στην πλατφόρμα.";
$dropbox_lang['queryError'] = "Error in database query. Παρακαλώ επικοινωνήστε με τον διαχειριστή της πλατφόρμας.";
$dropbox_lang['generalError'] = "Παρουσιάστηκε σφάλμα. Παρακαλούμε επικοινωνήστε με τον διαχειριστή της πλατφόρμας.";
$dropbox_lang['badFormData'] = "Η αποστολή του αρχείου απέτυχε: Τα δεδομένα ήταν με λάθος μορφή. Παρακαλούμε επικοινωνήστε με τον διαχειριστή της πλατφόρμας.";
$dropbox_lang['noUserSelected'] = "Παρακαλούμε επιλέξτε το χρήστη στον οποίο θέλετε να σταλεί το αρχείο.";
$dropbox_lang['noFileSpecified'] = "Δεν έχετε επιλέξει κάποιο αρχείο για να ανεβάσετε.";
$dropbox_lang['tooBig'] = "Δεν έχετε επιλέξει κάποιο αρχείο να ανεβάσετε ή το αρχείο υπερβαίνει το επιτρεπτό όριο σε μέγεθος.";
$dropbox_lang['uploadError'] = "Παρουσιάστηκε σφάλμα κατά το ανέβασμα του αρχείου. Παρακαλούμε επικοινωνήστε με τον διαχειριστή της πλατφόρμας.";
$dropbox_lang['errorCreatingDir'] = "Παρουσιάστηκε σφάλμα κατά τη δημιουργία καταλόγου. Παρακαλούμε επικοινωνήστε με τον διαχειριστή της πλατφόρμας.";
$dropbox_lang['installError'] = "Can't install the necessary tables for the dropbox module. Παρακαλώ επικοινωνήστε με τον διαχειριστή συστήματος.";
$dropbox_lang['quotaError'] = "Έχετε ξεπεράσει το μέγιστο συνολικό επιτρεπτό μέγεθος αρχείων! Το ανέβασμα του αρχείου δεν πραγματοποιήθηκε.";
$dropbox_lang['uploadFile'] = "Ανέβασμα αρχείου";
$dropbox_lang['authors'] = "Αποστολέας";
$dropbox_lang['description'] = "Περιγραφή αρχείου";
$dropbox_lang['sendTo'] = "Αποστολή στον/στην";

$dropbox_lang['receivedTitle'] = "ΕΙΣΕΡΧΟΜΕΝΑ ΑΡΧΕΙΑ";
$dropbox_lang['sentTitle'] = "ΑΠΕΣΤΑΛΜΕΝΑ ΑΡΧΕΙΑ";
$dropbox_lang['confirmDelete1'] = "Σημείωση: Το αρχείο ";
$dropbox_lang['confirmDelete2'] = " θα διαγραφτεί μόνο από τον κατάλογό σας";
$dropbox_lang['all'] = "Σημείωση: Τα αρχεία θα διαγραφτούν μόνο από τον κατάλογό σας";
$dropbox_lang['workDelete'] = "Διαγραφή από τον κατάλογο";
$dropbox_lang['sentBy'] = "Στάλθηκε από τον/την";
$dropbox_lang['sentTo'] = "Στάλθηκε στον/στην";
$dropbox_lang['sentOn'] = "την";
$dropbox_lang['anonymous'] = "ανώνυμος";
$dropbox_lang['ok'] = "Αποστολή";
$dropbox_lang['lastUpdated'] = "Τελευταία ενημέρωση την";
$dropbox_lang['lastResent'] = "Last resent on";
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

$dropbox_lang["mailingAsUsername"] = "Mailing ";
$dropbox_lang["mailingInSelect"] = "---Mailing---";
$dropbox_lang["mailingSelectNoOther"] = "Η αποστολή μηνύματος δεν μπορεί να συνδιαστεί με αποστολή σε άλλους παραλήπτες";
$dropbox_lang["mailingNonMailingError"] = "Mailing cannot be overwritten by non-mailing and vice-versa";
$dropbox_lang["mailingExamine"] = "Examine mailing zip-file";
$dropbox_lang["mailingNotYetSent"] = "Mailing content files have not yet been sent out...";
$dropbox_lang["mailingSend"] = "Send content files";
$dropbox_lang["mailingConfirmSend"] = "Send content files to individual destinations ?";
$dropbox_lang["mailingBackToDropbox"] = "(back to Dropbox main window)";
$dropbox_lang["mailingWrongZipfile"] = "Mailing must be zipfile with STUDENTID or LOGINNAME";
$dropbox_lang["mailingZipEmptyOrCorrupt"] = "Mailing zipfile is empty or not a valid zipfile";
$dropbox_lang["mailingZipPhp"] = "Mailing zipfile must not contain php files - it will not be sent";
$dropbox_lang["mailingZipDups"] = "Mailing zipfile must not contain duplicate files - it will not be sent";
$dropbox_lang["mailingFileFunny"] = "no name, or extension not 1-4 letters or digits";
$dropbox_lang["mailingFileNoPrefix"] = "name does not start with ";
$dropbox_lang["mailingFileNoPostfix"] = "name does not end with ";
$dropbox_lang["mailingFileNoRecip"] = "name does not contain any recipient-id";
$dropbox_lang["mailingFileRecipNotFound"] = "no such student with ";
$dropbox_lang["mailingFileRecipDup"] = "multiple users have ";
$dropbox_lang["mailingFileIsFor"] = "is for ";
$dropbox_lang["mailingFileSentTo"] = "sent to ";
$dropbox_lang["mailingFileNotRegistered"] = " (not registered for this course)";
$dropbox_lang["mailingNothingFor"] = "Nothing for";

$dropbox_lang['justUploadInSelect'] = "--- Ανέβασμα αρχείου ---";
$dropbox_lang['justUploadInList'] = "Ανέβασμα αρχείου από τον/την";
$dropbox_lang['mailingJustUploadNoOther'] = "Το ανέβασμα αρχείου δεν μπορεί να συνδιαστεί με αποστολή σε άλλους παραλήπτες";

/**********************************************************
* exercice.inc.php
**********************************************************/

$langQuestion="Ερώτηση";
$langQuestions="Ερωτήσεις";
$langAnswer="Απάντηση";
$langAnswers="Απαντήσεις";
$langComment="Σχόλιο";
$langMaj="Ενημέρωση";
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
$langQuestionList="Κατάλογος ερωτήσεων της άσκησης";
$langMoveUp="Μετακίνηση προς τα πάνω";
$langMoveDown="Μετακίνηση προς τα κάτω";
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
$langScore="Βαθμολογία";
$langCorrespondsTo="Αντιστοιχεί σε";
$langExpectedChoice="Αναμενόμενη Απάντηση";
$langYourTotalScore="Η συνολική σου βαθμολογία είναι";

// exercice_submit.php
$langDoAnEx="Κάντε μια άσκηση";
$langGenerator="Γεννήτρια ασκήσεων";
$langCorrect="Σωστό";

$langExerciseNotFound="Η απάντηση δεν βρέθηκε";
$langAlreadyAnswered="Απαντήσατε ήδη στην ερώτηση";

// scoring.php & scoring_student.php
$langPossAnsw="Αριθμός πιθανών απαντήσεων για μια ερώτηση";
$langStudAnsw="αριθμός λαθών από φοιτητή";
$langDetermine="Ορίστε τους βαθμούς-βάρη των απαντήσεων συμπληρώνοντας τον παρακάτω πίνακα. Στη συνέχεια πατήστε \"Εντάξει\"";
$langNonNumber="Ενας βαθμός μικρότερος του 0";
$langReplaced="έχει μπεί. Εχει αντικατασταθεί από το 0";
$langSuperior="Εχετε βάλει ένα βαθμό μεγαλύτερο του 20";
$langRep20="Εχει αντικατασταθεί από το  20";
$langDefault="Εξ' ορισμού βαθμοί *";
$langDefComment="* Εάν πατήσετε στο \"Εξ όρισμού Βαθμοί\", οι προηγούμενες τιμές θα διαγραφούν οριστικά.";
$langScoreGet="Οι αριθμοί με μαύρο χρώμα ειναι η βαθμολογία";

$langShowScor="Εμφάνιση βαθμολογίας στους φοιτητές : ";

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
$langExerciseSurname="Επώνυμο";

/***********************************************
* external_module.inc.php
***********************************************/

$langLinkSite="Σύνδεση σε ένα site";
$langSubTitle="<br><strong>Συμβουλή: </strong>Αν θέλετε να προσθέσετε ένα σύνδεσμο σε μια σελίδα,
	πηγαίνετε σε αυτή τη σελίδα, κάντε αποκοπή και επικόλληση τη διεύθυνσή της στη μπάρα των URL
	στο πάνω μέρος του browser και εισάγετέ το στο πεδίο \"Σύνδεσμος\" παρακάτω.<br><br>";
$langAdded="Ο σύνδεσμος προστέθηκε";
$langLink="Σύνδεσμος";
$langInvalidLink = "Ο σύνδεσμος είναι κενός και δεν προστέθηκε!";
$langNotAllowed = "Μη επιτρεπτή ενέργεια";


/***********************************************
* faculte.inc.php
***********************************************/

$langCodeF="Κωδικός";
$langListFaculte="Κατάλογος Σχολών / Τμημάτων - Ενέργειες";
$langCodeFaculte1="Κωδικός Σχολής / Τμήματος";
$langCodeFaculte2="(με λατινικούς χαρακτήρες μόνο, π.χ. MATH)";
$langAddFaculte="Προσθήκη Σχολών / Τμημάτων";
$langFaculte1="Σχολή / Τμήμα";
$langFaculte2="(π.χ. Μαθηματικό)";
$langAddYes="Προσθήκη";
$langAddSuccess="Η εισαγωγή πραγματοποιήθηκε με επιτυχία !";
$langNoSuccess="Πρόβλημα κατά την εισαγωγή των στοιχείων !";

$langProErase="Υπάρχουν διδασκόμενα μαθήματα στο τμήμα αυτό !";
$langNoErase="Η διαγραφή του τμήματος δεν είναι δυνατή.";
$langErase="Το τμήμα διαγράφτηκε!";

$langFCodeExists= "Ο κωδικός που βάλατε υπάρχει ήδη! Δοκιμάστε ξανά επιλέγοντας διαφορετικό";
$langFaculteExists="Η σχολή / τμήμα που βάλατε υπάρχει ήδη! Δοκιμάστε ξανά επιλέγοντας διαφορετικό";
$langEmptyFaculte="Αφήσατε κάποιο από τα πεδία κενά! Δοκιμάστε ξανά";

$langGreekCode="Ο κωδικός που βάλατε περιέχει μη λατινικούς χαρακτήρες!. Δοκιμάστε ξανά επιλέγοντας διαφορετικό";

/******************************************************
* forum_admin.inc.php
*******************************************************/

$langOrganisation="Διαχείριση των περιοχών συζητήσεων";
$langForCat="Περιοχές συζητήσεων της κατηγορίας";
$langBackCat="επιστροφή στις κατηγορίες";
$langForName="Όνομα περιοχής συζητήσεων";
$langFunctions="Λειτουργίες";
$langAddForCat="Προσθήκη περιοχής συζητήσεων στην κατηγορία";
$langChangeCat="Αλλαγή της κατηγορίας";
$langModCatName="Αλλαγή ονόματος κατηγορίας";
$langCat="Κατηγορία";
$langNameCatMod="Το όνομα της κατηγορίας έχει αλλάξει";
$langBack="Επιστροφή";
$langCatAdded="Προστέθηκε κατηγορία";
$langForCategories="Κατηγορίες περιοχών συζητήσεων";
$langAddForums="Για να προσθέσετε περιοχές συζητήσεων, κάντε κλίκ στο «Περιοχές συζητήσεων» στην κατηγορία της επιλογής σας. Μια κενή κατηγορία (χωρίς περιοχές) δεν θα φαίνεται στους φοιτητές";
$langCategories="Κατηγορίες";
$langNbFor="Αριθμός περιοχών συζητήσεων";
$langAddCategory="Προσθήκη κατηγορίας";
$langForumDataChanged = "Τα στοιχεία του φόρουμ έχουν αλλάξει";
$langForumCategoryAdded = "Προστέθηκε νέο φόρουμ στην κατηγορία που επιλέξατε";
$langForumDelete = "Η περιοχή συζητήσεων έχει διαγραφεί";


/***************************************************************
* grades.inc.php
****************************************************************/

$m['grades'] = "Βαθμολογία";

/*************************************************************
* group.inc.php
*************************************************************/

$langGroupManagement="Ομάδες χρηστών";
$langNewGroupCreate="Δημιουργία καινούριας ομάδας χρηστών";
$langGroupCreation="Δημιουργία καινούριας ομάδας χρηστών";
$langNewGroups="Αριθμός ομάδων χρηστών";
$langMax="Μέγ.";
$langPlaces="συμμετέχοντες στην ομάδα χρηστών (προαιρετικό)";
$langGroupPlacesThis="συμμετέχοντες (προαιρετικό)";
$langDeleteGroups="Διαγραφή όλων των ομάδων χρηστών";
$langGroupsAdded="ομάδες χρηστών έχουν προστεθεί";
$langGroupAdded = "ομάδα χρηστών έχει προστεθεί";
$langGroupsDeleted="Ολες οι ομάδες χρηστών έχουν διαγραφεί";
$langGroupDel="Η ομάδα χρηστών διαγράφτηκε";

$langGroupsEmptied="Όλες οι ομάδες χρηστών είναι άδειες";
$langEmtpyGroups="Εκκαθάριση όλων των ομάδων χρηστών";
$langGroupsFilled="Όλες οι ομάδες χρηστών έχουν συμπληρωθεί";
$langFillGroups="Συμπλήρωση των ομάδων χρηστών";
$langGroupsProperties="Ρυθμίσεις ομάδες χρηστών";
$langStudentRegAllowed="Οι φοιτητές επιτρέπεται να γραφτούν στις ομάδες";
$langStudentRegNotAllowed="Οι φοιτητές δεν επιτρέπεται να γραφτούν στις ομάδες";
$langPrivateAccess="Οι περιοχές συζητήσεων των ομάδων χρηστών είναι κλειστά";
$langNoPrivateAccess="Οι περιοχές συζητήσεων των ομάδων χρηστών είναι κλειστά";
$langTools="Εργαλεία";
$langGroup="Ομάδα Χρηστών";
$langExistingGroups="Ομάδες Χρηστών";
$langEdit="Διόρθωση";
$langDeleteGroupWarn = "Είστε σίγουρος ότι θέλετε να διαγράψετε αυτή την ομάδα χρηστών";
$langDeleteGroupAllWarn = "Είστε σίγουρος ότι θέλετε να διαγράψετε όλες τις ομάδες χρηστών";
$langEmptyGroupAllWarn = "Είστε σίγουρος ότι θέλετε να διαγράψετε όλες τις ομάδες χρηστών";

// Group Properties
$langGroupProperties="Ρυθμίσεις ομάδων χρηστών";
$langGroupAllowStudentRegistration="Οι φοιτητές επιτρέπονται να εγγραφούν στις ομάδες χρηστών";
$langGroupPrivatise="Κλειστές περιοχές συζητήσεων ομάδων χρηστών";
$langGroupTools="Εργαλεία";
$langGroupForum="Περιοχή συζητήσεων";
$langGroupDocument="Έγγραφα";
$langValidate="Αλλαγή";
$langGroupPropertiesModified="Αλλάχτηκαν οι ρυθμίσεις της ομάδας χρηστών";

// Group space
$langGroupThisSpace="Περιοχή για την ομάδα χρηστών";
$langGroupName="Όνομα ομάδας χρηστών";
$langGroupDescription="Περιγραφή";
$langEditGroup="Διόρθωση της ομάδας χρηστών";
$langUncompulsory="(προαιρετικό)";
$langNoGroupStudents="Μη εγγεγραμμένοι φοιτητές";
$langGroupMembers="Μέλη ομάδας χρηστών";
$langGroupValidate="Επικύρωση";
$langGroupCancel="Ακύρωση";
$langGroupSettingsModified="Οι ρυθμίσεις της ομάδας χρηστών έχουν αλλάξει";
$langNameSurname="Όνομα Επίθετο";
$langAM="Αριμός Μητρώου";
$langEmail="email";

$langGroupStudentsInGroup="φοιτητές εγγεγραμμένοι σε ομάδες χρηστών";
$langGroupStudentsRegistered="φοιτητές εγγεγραμμένοι στο μάθημα";
$langGroupNoGroup="μη εγγεγραμμένοι φοιτητές";
$langGroupUsersList="Βλέπε <a href=../user/user.php>Χρήστες</a>";
$langGroupTooMuchMembers="Ο αριθμός που προτάθηκε υπερβαίνει το μέγιστο επιτρεπόμενο (μπορείτε να το αλλάξετε παρακάτω).
	Η σύνθεση της ομάδας δεν άλλαξε";
$langGroupTutor="Διδάσκοντας";
$langGroupNoTutor="κανένας";
$langGroupNone="δεν υπάρχει";
$langGroupNoneMasc="κανένας";
$langGroupUManagement="Διαχείριση Χρηστών";
$langAddTutors="Διαχείριση καταλόγου χρηστών";
$langForumGroup="Περιοχή συζητήσεων της ομάδας";
$langMyGroup="η ομάδα μου";
$langOneMyGroups="ο επιβλέπων";
$langGroupSelfRegistration="Εγγραφή";
$langGroupSelfRegInf="εγγραφή";
$langRegIntoGroup="Προσθέστε με στην ομάδα";
$langGroupNowMember="Είσαι τώρα μέλος της ομάδας";
$langPublic="ανοικτό";
$langForumType="Τύπος περιοχής συζητήσεων";
$langPropModify="Αλλαγή ρυθμίσεων";
$langGroupAccess="Πρόσβαση";
$langGroupFilledGroups="Οι ομάδες χρηστών έχουν συμπληρωθεί από φοιτητές που βρίσκονται στον κατάλογο «Χρήστες».";

// group - email
$langEmailGroup = "Αποστολή e-mail στην ομάδα";
$langTypeMessage = "Πληκτρολογήστε το μήνυμά σας παρακάτω";
$langSend = "Αποστολή";
$langEmailSuccess = "Το e-mail σας στάλθηκε με επιτυχία !";
$langMailError = "Σφάλμα κατά την αποστολή e-mail !";
$langGroupMail = "Mail στην Ομάδα Χρηστών";
$langMailSubject = "Θέμα :";
$langMailBody = "Μήνυμα :";
$langProfLesson = "Διδάσκων του μαθήματος";

/*****************************************************
* guest.inc.php
*****************************************************/

$langAskGuest="Πληκτρολογήστε το συνθηματικό του λογαριασμού επισκέπτη";

$langAddGuest="Προσθήκη χρήστη επισκέπτη";
$langGuestName="Επισκέπτης";
$langGuestSurname="Μαθήματος";
$langGuestUserName="guest";

$langGuestAdd="Προσθήκη";
$langChangeGuestPasswd="Αλλαγή";
$langGuestExist="Υπάρχει ήδη ο λογαριασμός Επισκέπτη! Μπορείτε όμως αν θέλετε να αλλάξετε το συνθηματικό του.";

$langGuestSuccess="Ο λογαριασμός επισκέπτη (guest account) δημιουργήθηκε με επιτυχία !";
$langGuestFail="Πρόβλημα κατά την δημιουργία λογαριασμού επισκέπτη";
$langGuestChange="Η αλλαγή συνθηματικού επισκέπτη έγινε με επιτυχία!";


/********************************************************
* gunet.inc.php
********************************************************/

$infoprof="Σύντομα θα σας σταλεί e-mail από την Ομάδα Διαχείρισης της Πλατφόρμας Ασύγχρονης Τηλεκπαίδευσης e-class, με τα στοιχεία του λογαριασμού σας.";

$profinfo="Η ηλεκτρονική πλατφρόρμα GUNET e-Class διαθέτει 2 εναλλακτικούς τρόπους εγγραφής διδασκόντων";
$userinfo="Η ηλεκτρονική πλατφόρμα GUNET e-Class διαθέτει 2 εναλλακτικούς τρόπους εγγραφής χρηστών:";
$regprofldap="Εγγραφή διδασκόντων που έχουν λογαριασμό στην Υπηρεσία Καταλόγου (LDAP Directory Service) του ιδρύματος που ανήκουν";
$regldap="Εγγραφή χρηστών που έχουν λογαριασμό στην Υπηρεσία Καταλόγου (LDAP Directory Service) του ιδρύματος που ανήκουν";
$regprofnoldap="Εγγραφή διδασκόντων που δεν έχουν λογαριασμό στην Υπηρεσία Καταλόγου του ιδρύματος που ανήκουν";
$regnoldap="Εγγραφή χρηστών που δεν έχουν λογαριασμό στην Υπηρεσία Καταλόγου του ιδρύματος που ανήκουν";

$mailbody1="\nΠανελλήνιο Ακαδημαϊκό Δίκτυο GUNet\n\n";
$mailbody2="Ο Χρήστης\n\n";
$mailbody3="επιθυμεί να έχει πρόσβαση ";
$mailbody4="στην υπηρεσία Ασύγχρονης Τηλεκπαίδευσης ";
$mailbody5="του GUNet ";
$mailbody6="σαν καθηγητής.";
$mailbody7="Σχολή / Τμήμα:";
$mailbody8="ως φοιτητής.";

$logo= "eClass Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης GUNet";

$gunet="Ομάδα Ασύγχρονης Τηλεκπαίδευσης GUNet";
$sendinfomail="Αποστολή ενημερωτικού e-mail στους καθηγητές του eClass";
$infoabouteclass="Ενημερωτικό δελτίο πλατφόρμας eClass";

// contact.php
$introcontact = "Μπορείτε να επικοινωνείτε με την Ομάδα Υποστήριξης της πλατφόρμας <b>".$siteName."</b> με τους παρακάτω
 τρόπους:";
$langPostMail="<b>Ταχυδρομική Διεύθυνση:</b>";
$langPhone = "<b>Τηλ:</b>";
$langFax = "<b>Fax:</b>";


/********************************************************************
* help.inc.php
********************************************************************/

// help_forums.php
$langClose="Κλείσιμο παραθύρου";

$langHDefault='Βοήθεια μη διαθέσιμη';
$langDefaultContent='<p>Δεν υπάρχει κείμενο βοήθειας για την περιοχή
της πλατφόρμας στην οποία βρίσκεστε.</p>';


$langHFor="Βοήθεια περιοχής συζητήσεων";
$langForContent="<p>Οι περιοχές συζητήσεων είναι ένα εργαλείο για ασύγχρονη
γραπτή επικοινωνία. Ενώ το ηλεκτρονικό ταχυδρομείο επιτρέπει το διάλογο ανάμεσα
σε δύο μόνο άτομα, οι περιοχές συζητήσεων επιτρέπουν δημόσιες συζητήσεις. Από
τεχνική άποψη, για τη χρήση μιας περιοχής συζητήσεων απαιτείται μόνο ένα
πρόγραμμα browser.</p>

<p>Για την οργάνωση των περιοχών συζητήσεων, κάντε κλίκ στο «Διαχείριση». Οι
συζητήσεις είναι οργανωμένες σε σύνολα και υποσύνολα ως εξής:</p>

<p><b>Κατηγορία > Περιοχή > Θέμα > Απαντήσεις</b></p>

<p>Για να έχετε τακτοποιημένες τις συζητήσεις των φοιτητών σας, είναι
απαραίτητο να οργανώσετε κατηγορίες και περιοχές από πριν, και να αφήσετε τη
δημιουργία των θεμάτων και των απαντήσεων σε αυτούς. Οι περιοχές συζητήσεων του
e-Class έχουν μια περιοχή συζήτησης και ένα θέμα ως δείγματα.</p>

<p>Το πρώτο πράγμα που πρέπει να κάνετε είναι να διαγράψετε το δοκιμαστικό θέμα
και να μετονομάσετε την περιοχή συζήτησης. Στη συνέχεια, μπορείτε να
δημιουργήσετε και άλλες περιοχές, κατά ομάδες ή κατά θέματα, που να ταιριάζουν
στις εκπαιδευτικές σας ανάγκες.</p> <p>Μην ανακατεύετε τις κατηγορίες και τις
περιοχές συζητήσεων, και μην ξεχνάτε ότι μια κενή κατηγορία (χωρίς περιοχές)
δεν εμφανίζεται στις σελίδες που βλέπουν οι φοιτητές.</p>

<p>Η περιγραφή κάποιας περιοχής μπορεί να περιλαμβάνει τον κατάλογο των μελών
της, το σκοπό της, κάποιο έργο ή θέμα, κλπ.</p>";

// help_home.php

$langHHome="Βοήθεια";
$langHomeContent="<p>Για περισσότερη ευκολία, τα εργαλεία του eClass δε
δημιουργούνται κενά. Σε κάθε εργαλείο υπάρχει ένα μικρό παράδειγμα για να σας
βοηθήσει να κατανοήσετε ευκολότερα τον τρόπο λειτουργίας του.  Μπορείτε να
επιλέξετε να σβήσετε αυτό το παράδειγμα ή να το αλλάξετε.</p>

<p>Για παράδειγμα, στην αρχική σελίδα του μαθήματός σας, υπάρχει ένα μικρό
κείμενο που λέει «Εισαγωγικό κείμενο του μαθήματος. Αντικαταστήτε το με το δικό
σας, κάνοντας κλίκ στην Αλλαγή». Πιέστε το «Αλλαγή», διορθώστε το κείμενο και
πιέστε «Επικύρωση». Κάθε εργαλείο έχει την ίδια απλή λογική: προσθέτετε,
διαγράφετε, αλλάζετε - οι σελίδες του μαθήματος είναι δυναμικές.</p>

<p>Όταν πρωτοδημιουργείτε τις σελίδες του μαθήματός σας, τα περισσότερα
εργαλεία είναι ενεργοποιημένα. Κι εδώ, είναι δική σας επιλογή να
απενεργοποιήσετε αυτά που δε χρειάζεστε. Απλώς κάντε κλίκ στο «Απενεργοποίηση».
Τότε, μεταφέρετε στο γκρίζο μέρος της σελίδας. Δεν είναι πια ορατό από τους
φοιτητές σας, αλλά μπορείτε να το ξαναενεργοποιήσετε όποτε θέλετε.</p>

<p>Μπορείτε να προσθέσετε τις δικές σας σελίδες στην αρχική σελίδα του
μαθήματος. Αυτές οι σελίδες πρέπει να είναι σε μορφή HTML, και που μπορεί να
δημιουργηθεί με κάποιον επεξεργαστή κειμένου ή πρόγραμμα δημιουργίας
ιστοσελίδων. Χρησιμοποιείστε την επιλογή «Ανέβασμα σελίδας και δημιουργία
συνδέσμου στην Αρχική Σελίδα» για να στείλετε τη σελίδα σας στον εξυπηρετητή. Η
επικεφαλίδα του δικτυακού τόπου θα ενσωματωθεί αυτόματα στις σελίδες σας, οπότε
χρειάζεται μόνο να σκεφτείτε για το περιεχόμενο των σελίδων. Αν θέλετε να
προσθέσετε συνδέσμους από την αρχική σελίδα του μαθήματος προς υπάρχουσες
ιστοσελίδες που υπάρχουν ήδη κάπου αλλού στο δίκτυο (ή ακόμα κάπου αλλού στον
δικό σας δικτυακό τόπο), χρησιμοποιήστε την «Προσθήκη συνδέσμου στην αρχική
σελίδα». Οι σελίδες που προσθέτετε εσείς στην αρχική σελίδα μπορούν να
απενεργοποιηθούν και να διαγραφούν, ενώ τα ενσωματωμένα εργαλεία μπορούν να
απενεργοποιηθούν μόνο, αλλά όχι να διαγραφούν.</p>

<p>Όταν η σελίδα του μαθήματός σας είναι έτοιμη, μπορείτε να καθορίσετε τα
δικαιώματα πρόσβασης για τους χρήστες του συστήματος, από την επιλογή «Αλλαγή
πληροφορίας για το μάθημα». Στην αρχή, είναι κρυμμένη (γιατί δουλεύετε ακόμη σε
αυτήν).</p>";


$langHInit = "Αρχική Σελίδα";
$langInitContent = "<p class='helptopic'>Βρίσκεστε στην Αρχική Σελίδα της
πλατφόρμας eClass. Για να χρησιμοποιήσετε την πλατφόρμα πληκτρολογήστε το όνομα
χρήστη και το συνθηματικό σας. Αν δεν θυμάστε τα στοιχεία σας, κάντε κλικ στο
σύνδεσμο 'Ξεχάσατε το συνθηματικό σας' και στην φόρμα που θα σας εμφανιστεί,
συμπληρώστε την ηλεκτρονική σας διεύθυνση που δηλώσατε κατά την εγγραφή σας
στην πλατφόρμα για να σταλούν τα στοιχεία σας.</p>

<p class='helptopic'>Αν είστε φοιτητής και δεν έχετε λογαριασμό, κάντε κλικ στο
σύνδεσμο 'Εγγραφή Φοιτητή' για να αποκτήσετε. Αν θέλετε να αποκτήσετε
λογαριασμό με δικαιώματα 'καθηγητή', κάντε κλικ στο σύνδεσμο 'Εγγραφή καθηγητή'
για να συμπληρώσετε την 'Αίτηση Εγγραφής Καθηγητή'</p>

<p class='helptopic'>Για περισσότερες πληροφορίες σχετικά με τη λειτουργία της
πλατφόρμας διαβάστε τα σχετικά εγχειρίδια χρήσης.</p>";


$langHPortfolio="Χαρτοφυλάκιο χρήστη";
$langPortfolioContent="<p>Σε αυτή τη σελίδα εμφανίζονται τα μαθήματα στα
οποία είστε εγγεγραμμένος (ως φοιτητής) ή έχετε δημιουργήσει (ως καθηγητής).
Μπορείτε να παρακολούθησετε όποιο επιθυμείτε επιλέγοντας τον αντίστοιχο
τίτλο.</p>

<p>Μέσω των συνδέσμων του μενού, μπορείτε να εγγραφείτε σε άλλα μαθήματα ή
να απεγγραφείτε από αυτά που παρακολουθείτε, να αλλάξετε τα στοιχεία του
προφίλ σας όπως όνομα, συνθηματικό και προτιμώμενη γλώσσα, καθώς και
να δείτε συγκεντρωτικά τις ανακοινώσεις και το ημερολόγιο των μαθημάτων
σας.</p>
";

$langHcourse_home_stud='Κεντρική σελίδα μαθήματος';
$langcourse_home_studContent='<p>Βρίσκεστε στην κεντρική σελίδα του
ηλεκτρονικού μαθήματος. Από τις επιλογές του μενού στο αριστερό μέρος της
οθόνης, μπορείτε να μεταβείτε στα υποσυστήματα που έχουν ενεργοποιηθεί από
τους διδάσκοντες του μαθήματος.</p>';

$langHcourse_home_prof='Κεντρική σελίδα μαθήματος';
$langcourse_home_profContent='<p class="helptopic">Βρίσκεστε στην κεντρική σελίδα του
ηλεκτρονικού μαθήματός σας. Από τις επιλογές του μενού στο αριστερό μέρος της
οθόνης, μπορείτε να μεταβείτε στα υποσυστήματα του μαθήματος, στα οποία
μπορείτε να προσθέσετε το εκπαιδευτικό υλικό του μαθήματος. Τα
ενεργά εργαλεία είναι διαθέσιμα και στους φοιτητές/επισκέπτες του μαθήματος,
ενώ τα ανενεργά εμφανίζονται μόνο στο διδάσκοντα.</p>
<p class="helptopic">Από τα εργαλεία διαχείρισης μπορείτε να ενεργοποιήσετε ή να
απενεργοποιήσετε τα υποσυστήματα που επιθυμείτε, να διαχειριστείτε
τους εγγεγραμμένους χρήστες, να αλλάξετε διάφορες επιλογές του μαθήματος
(όπως τίτλο, έλεγχο πρόσβασης κλπ.), και να λάβετε στατιστικά χρήσης
του μαθήματος.</p>';


$langHDoc="Βοήθεια";
$langDocContent="<p>Το εργαλείο αρχείων είναι όμοιο σε λειτουργία με τον
Διαχειριστή Αρχείων του προσωπικού σας υπολογιστή.</p>

<p>Μπορείτε να ανεβάσετε αρχεία οποιουδήποτε τύπου (HTML, Word, Powerpoint,
Excel, Acrobat, Flash, Quicktime, κ.λπ.). Ο μόνος σας περιορισμός είναι ότι οι
φοιτητές πρέπει να έχουν εγκατεστημένη στον υπολογιστή τους την αντίστοιχη
εφαρμογή για να τα διαβάσουν.  Μερικοί τύποι αρχείων μπορεί να περιέχουν ιούς,
έτσι είναι δικιά σας ευθύνη να μην ανεβάζετε μολυσμένα αρχεία. Συνίσταται να
ελέγχεται τα έγγραφα σας με ένα αντιβιοτικό πρόγραμμα πριν τα ανεβάσετε.</p>

<p>Τα έγγραφα παρουσιάζονται με αλφαβητική σειρά.
<br><b>Συμβουλή:</b> Αν θέλετε να τα παρουσιάσετε με διαφορετική σειρά,
αριθμήστε τα: 01, 02, 03...</p>

<p>Μπορείτε :</p>

<h4>Να ανεβάσετε ένα αρχείο</h4>
<ul>
  <li>Επιλέξτε το αρχείο στον υπολογιστή σας χρησιμοποιώντας το πλήκτρο Browse <input
 type=submit value=Browse name=submit2>
    στο δεξί μέρος της οθόνης σας.</li>
  <li>Ξεκινήστε το ανέβασμα με το πλήκτρο Ανέβασμα <input type=submit
 value=Ανέβασμα name=submit2>
    .</li>
</ul>
<h4>Μετονομάστε ένα έγγραφο (ένα κατάλογο)</h4>
<ul>
  <li>κάντε κλίκ στο <img src=../../template/classic/img/renommer.gif width=20 height=20
 align=baseline> πλήκτρο στη
  στήλη Μετονομασία</li>
  <li>Πληκτρολογήστε το καινούριο όνομα στο πεδίο (πάνω αριστερά)</li>
  <li>Επιβεβαιώστε το κάνοντας κλίκ στο <input type=submit value=Ok name=submit24>.
</ul>
    <h4>Διαγραφή ενός αρχείου (ή ενος καταλόγου)</h4>
    <ul>

  <li>Κάντε κλίκ <img src=../../template/classic/img/supprimer.gif width=20 height=20>
    στη στήλη 'Διαγραφή'.</li>
    </ul>
    <h4>Μετατροπή ενός αρχείου (ή καταλόγου) σε αόρατο για τους φοιτητές</h4>
    <ul>

  <li>Κάντε κλίκ <img src=../../template/classic/img/visible.gif width=20 height=20>
 στη στήλη 'Ορατό/Αόρατο'.</li>
      <li>Το αρχείου (ή κατάλογο) υπάρχει αλλά δεν είναι ορατό από τους φοιτητές πλέον.</li>
  <li>Γα να το κάνετε αόρατο ξανά, κάντε κλίκ στη <img
 src=../../template/classic/img/invisible.gif width=24 height=20>
    στήλη 'Ορατό/Αόρατο'</li>
    </ul>
    <h4>Προσθήκη ή τροποποίηση σχολίου σε ένα αρχείο (ή ενός καταλόγου)</h4>
    <ul>
  <li>Κάντε κλίκ <img src=../../template/classic/img/comment.gif width=20
 height=20>
    στη στήλη 'Σχόλιο'</li>
      <li>Πληκτρολογήστε καινούριο σχόλιο στο αντίστοιχο πεδίο (πάνω δεξιά).</li>
      <li>Επιβεβαιώστε το κάντε κλίκ στο <input type=submit value=OK name=submit2>.</li>
    </ul>
    <p>Για να διαγράψετε ένα σχόλιο, κάντε κλίκ στο <img
 src=../../template/classic/img/comment.gif width=20 height=20>,
     για να διαγράψετε το παλιό σχόλιο στο πεδίο κα κάντε κλίκ στο<input type=submit
 value=OK name=submit22>.
    <hr>
    <p>Μπορείτε να οργανώσετε το περιεχόμενό σας μέσο αρχειοθέτησης. Για το σκοπό αυτό:</p>
    <h4><b>Δημιουργήστε ένα κατάλογο</b></h4>
    <ul>
      <li>Κάντε κλίκ στο <img src=../../template/classic/img/dossier.gif width=20
 height=20> 'Δημιουργία ενός καταλόγου' (πάνω αριστερά)</li>
      <li>Πληκτρολογήστε το όνομα του καινούριου σας καταλόγου στο αντίστοιχο πεδίο (πάνω αριστερά)</li>
      <li>Επιβεβαιώστε το κάνοντας κλίκ στο<input type=submit value=OK
 name=submit23>.</li>
    </ul>
    <h4>Μετακίνηση ενός αρχείου (ή καταλόγου)</h4>
    <ul>
      <li>Κάντε κλικ στο πλήκτρο <img src=../../template/classic/img/deplacer.gif
 width=34 height=16>
        στη στήλη 'Μετακίνηση'</li>
      <li>Επιλέξτε τον κατάλογο μέσα στον οποίο θέλετε να μετακινήσετε το έγγραφο (ή το κατάλογο) στο
     αντίστοιχο κυλιόμενο μενού (πάνω αριστερά) (σημείωση: η λέξη 'αρχικό κατάλογο' σημαίνει ότι
     δεν μπορείτε να πάτε πάνω από αυτό το επίπεδο στο δέντρο αρχείων του εξυπηρέτη).</li>
      <li>Επιβεβαιώστε το κάνοντας κλίκ στο <input type=submit value=OK
 name=submit232>.</li>
    </ul>
    <center>
      <p>";



// Help_user.php

$langHUser="Βοήθεια";
$langUserContent="<b>Ρόλοι</b><p>Οι ρόλοι των χρηστών στην πλατφόρμα δεν
σχετίζονται καθόλου με τον υπολογιστή στον οποίο τρέχει η πλατφόρμα. Αυτό
σημαίνει ότι δεν δίνουν κανένα δικαιώμα πάνω στο λειτουργικό σύστημα.  Συνήθως
δείχνουν στους ανθρώπους, ποιος είναι ποιος.</p>
<hr>
<b>Δικαιώματα Διαχειριστή</b>
<p>Τα δικαιώματα διαχειριστή, από την άλλη, ανταποκρίνονται στην τεχνική
 εξουσιοδότηση να αλλάξετε τα περιεχόμενα και τον οργάνωση του μαθήματος.</p>
<p>Για να επιτρέψετε σε ένα βοηθό, για παράδειγμα, να συν-διαχειριστεί το
μάθημα, αρκεί να τον εγγράψετε στο μάθημα ή να βεβαιωθείτε ότι είναι ήδη
γραμμένος, ύστερα κάντε κλίκ στην 'Αλλαγή'  κάτω από τα 'Δικαιώματα
Διαχειριστή', ύστερα κάντε κλίκ στο 'Ολα', ύστερα στο 'Εντάξει'.</P><hr>
<b>Συνδιδάσκοντας</b>
<p>Για να αναφέρετε στην επικεφαλίδα του μαθήματος το όνομα ενός συνδιδάσκοντα,
 χρησιμοποιήστε το εργαλείο 'Αλλαγή πληροφορίας για το Μάθημα' .
 Αυτή η αλλαγή δεν κάνει τον συνδιδάσκοντά σας ένα χρήστη ενός μαθήματος.
 Το πεδίο 'Καθηγητές' είναι εντελώς ανεξάρτητο από τη λίστα των χρηστών.</p><hr>
<b>Προσθήκη ενός χρήστη</b>
<p>Για να προσθέσετε ένα χρήστη στο μάθημά σας, συμπληρώστε τα πεδία και
επιβεβαιώστε το. Ο χρήστης θα λάβει ένα e-mail που θα τον/την ενημερώνει ότι
τον/την έχετε εγγράψει και απλά πείτε του/της ή θυμήστε του/της το όνομα χρήστη
και το συνθηματικό.</p>";

// Help guest user

$langHGuest = "Προσθήκη χρήστη επισκέπτη";
$langGuestContent = "Επιλέγοντας 'Προσθήκη χρήστη Επισκέπτη' σας δίνετε η δυνατότητα να δημιουργήσετε ένα χρήστη επισκέπτη,
τα στοιχεία του οποίου θα γνωστοποιήσετε στους χρήστες οι οποίοι είναι εγγεγραμμένοι στο μάθημα (ή στην πλατφόρμα). Ένας
χρήστης επισκέπτης, έχει τη δυνατότητα να βλέπει την αρχική σελίδα του μαθήματος στο οποίο είναι γραμμένος και όλα τα
εργαλεία που είναι ενεργοποιημένα, αλλά δεν μπορεί να εκτελέσει λειτουργίες όπως να ανεβάσει εργασίες.";

// Help survey
$langHSurvey="Βοήθεια";
$langSurveyContent="<p>Το εργαλείο των ερωτηματολογίων μαθησιακού προφίλ (Ε.Μ.Π.) επιτρέπει στον
καθηγητή την δημιουργία και διαχείριση Ε.Μ.Π. Για να σας
εμφανίζεται σαν επιλογή πρέπει να είναι πρώτα ενεργοποιημένο για το
τρέχον μάθημα.</p>
<p>καθηγητής θα μπορεί να ενεργοποιήσει την λειτουργία
σε όσα μαθήματα επιθυμεί. Στην πρώτη σελίδα των Ε.Μ.Π. ενός
μαθήματος ενημερώνεται ο καθηγητής για τα ενεργά και ανενεργά Ε.Μ.Π.
που αντιστοιχούν στο τρέχον μάθημα . Επίσης θα μπορεί να προβεί σε
λειτουργίες όπως προσθήκη, διαγραφή, ενεργοποίηση/απενεργοποίηση.
Κατά την δημιουργία μιας δημοσόπισης ο καθηγητής θα καθορίζει ερώτηση
και όσες απαντήσεις επιθυμεί. Υπάρχουν έτοιμες ερωτήσεις-οδηγεί βασισμένες στα πρότυπα COLLES/ATTL.
Επίσης θα καθορίζει το χρονικό διάστημα
κατά το οποίο η δημοσκόπηση θα είναι ενεργή (ημερομηνία έναρξης-λήξης).
Μέσα από την σελίδα του μαθήματος ο μαθητής θα μπορεί να επισκεφτεί την
σελίδα των Ε.Μ.Π., εφόσον υπάρχουν για το τρέχον μάθημα, και να
τις δεί σε μία λίστα. Επιλέγοντας την Ε.Μ.Π. που τον ενδιαφέρει
από την προηγούμενη λίστα θα μπορεί να επιλέξει μία από τις πιθανές
απαντήσεις. </p>
<p>Αποτελέσματα μπορεί να δεί και ο καθηγητής από την σελίδα
διαχείρισης τoυ Ε.Μ.Π.</p>";

// Help poll.php
$langHPoll="Βοήθεια";
$langPollContent="<p>Το εργαλείο των δημοσκοπίσεων επιτρέπει στον
καθηγητή την δημιουργία και διαχείριση δημοσκοπήσεων. Για να σας
εμφανίζεται σαν επιλογή πρέπει να είναι πρώτα ενεργοποιημένο για το
τρέχον μάθημα.</p>
<p>καθηγητής θα μπορεί να ενεργοποιήσει την λειτουργία δημοσκοπήσεων
σε όσα μαθήματα επιθυμεί. Στην πρώτη σελίδα των δημοσκοπήσεων ενός
μαθήματος ενημερώνεται ο καθηγητής για τα ενεργά και ανενεργά poll
που αντιστοιχούν στο τρέχον μάθημα . Επίσης θα μπορεί να προβεί σε
λειτουργίες όπως προσθήκη, διαγραφή, ενεργοποίηση/απενεργοποίηση.
Κατά την δημιουργία μιας δημοσόπισης ο καθηγητής θα καθορίζει ερώτηση
και όσες απαντήσεις επιθυμεί. Επίσης θα καθορίζει το χρονικό διάστημα
κατά το οποίο η δημοσκόπηση θα είναι ενεργή (ημερομηνία έναρξης-λήξης).
Μέσα από την σελίδα του μαθήματος ο μαθητής θα μπορεί να επισκεφτεί την
σελίδα των δημοσκοπήσεων, εφόσον υπάρχουν για το τρέχον μάθημα, και να
τις δεί σε μία λίστα. Επιλέγοντας την δημοσκόπηση που τον ενδιαφέρει
από την προηγούμενη λίστα θα μπορεί να επιλέξει μία από τις πιθανές
απαντήσεις. </p>
<p>Αποτελέσματα μπορεί να δεί και ο καθηγητής από την σελίδα
διαχείρισης της δημοσκόπισης.</p>";

// Help questionnaire.php
$langHQuestionnaire="Βοήθεια";
$langQuestionnaireContent="<p>Το εργαλείο των Ερωτηματολογίων επιτρέπει στον
καθηγητή την δημιουργία και διαχείριση Ερευνών Μαθησιακού Προφίλ ή Δημοσκοπήσεων. Για να σας
εμφανίζεται σαν επιλογή πρέπει να είναι πρώτα ενεργοποιημένο για το
τρέχον μάθημα.</p>
<p>Ο καθηγητής θα μπορεί να ενεργοποιήσει την λειτουργία ερωτηματολογίων
σε όσα μαθήματα επιθυμεί. </p>
<p>Αποτελέσματα μπορεί να δεί και ο καθηγητής από την σελίδα
διαχείρισης του ερωτηματολογίου.</p>
<p>Το εργαλείο των δημοσκοπίσεων επιτρέπει στον
καθηγητή την δημιουργία και διαχείριση δημοσκοπήσεων. Για να σας
εμφανίζεται σαν επιλογή πρέπει να είναι πρώτα ενεργοποιημένο για το
τρέχον μάθημα.</p>
<p>καθηγητής θα μπορεί να ενεργοποιήσει την λειτουργία δημοσκοπήσεων
σε όσα μαθήματα επιθυμεί. Στην πρώτη σελίδα των δημοσκοπήσεων ενός
μαθήματος ενημερώνεται ο καθηγητής για τα ενεργά και ανενεργά poll
που αντιστοιχούν στο τρέχον μάθημα . Επίσης θα μπορεί να προβεί σε
λειτουργίες όπως προσθήκη, διαγραφή, ενεργοποίηση/απενεργοποίηση.
Κατά την δημιουργία μιας δημοσόπισης ο καθηγητής θα καθορίζει ερώτηση
και όσες απαντήσεις επιθυμεί. Επίσης θα καθορίζει το χρονικό διάστημα
κατά το οποίο η δημοσκόπηση θα είναι ενεργή (ημερομηνία έναρξης-λήξης).
Μέσα από την σελίδα του μαθήματος ο μαθητής θα μπορεί να επισκεφτεί την
σελίδα των δημοσκοπήσεων, εφόσον υπάρχουν για το τρέχον μάθημα, και να
τις δεί σε μία λίστα. Επιλέγοντας την δημοσκόπηση που τον ενδιαφέρει
από την προηγούμενη λίστα θα μπορεί να επιλέξει μία από τις πιθανές
απαντήσεις. </p>
<p>Αποτελέσματα μπορεί να δεί και ο καθηγητής από την σελίδα
διαχείρισης της δημοσκόπισης.</p>
<p>Το εργαλείο των ερωτηματολογίων μαθησιακού προφίλ (Ε.Μ.Π.) επιτρέπει στον
καθηγητή την δημιουργία και διαχείριση Ε.Μ.Π. Για να σας
εμφανίζεται σαν επιλογή πρέπει να είναι πρώτα ενεργοποιημένο για το
τρέχον μάθημα.</p>
<p>καθηγητής θα μπορεί να ενεργοποιήσει την λειτουργία
σε όσα μαθήματα επιθυμεί. Στην πρώτη σελίδα των Ε.Μ.Π. ενός
μαθήματος ενημερώνεται ο καθηγητής για τα ενεργά και ανενεργά Ε.Μ.Π.
που αντιστοιχούν στο τρέχον μάθημα . Επίσης θα μπορεί να προβεί σε
λειτουργίες όπως προσθήκη, διαγραφή, ενεργοποίηση/απενεργοποίηση.
Κατά την δημιουργία μιας δημοσόπισης ο καθηγητής θα καθορίζει ερώτηση
και όσες απαντήσεις επιθυμεί. Υπάρχουν έτοιμες ερωτήσεις-οδηγεί βασισμένες στα πρότυπα COLLES/ATTL.
Επίσης θα καθορίζει το χρονικό διάστημα
κατά το οποίο η δημοσκόπηση θα είναι ενεργή (ημερομηνία έναρξης-λήξης).
Μέσα από την σελίδα του μαθήματος ο μαθητής θα μπορεί να επισκεφτεί την
σελίδα των Ε.Μ.Π., εφόσον υπάρχουν για το τρέχον μάθημα, και να
τις δεί σε μία λίστα. Επιλέγοντας την Ε.Μ.Π. που τον ενδιαφέρει
από την προηγούμενη λίστα θα μπορεί να επιλέξει μία από τις πιθανές
απαντήσεις. </p>
<p>Αποτελέσματα μπορεί να δεί και ο καθηγητής από την σελίδα
διαχείρισης τoυ Ε.Μ.Π.</p>";

// Help exercice.php

$langHExercise="Βοήθεια";
$langExerciseContent="<p>Το εργαλείο των ασκήσεων σας επιτρέπει να δημιουργήσετε ασκήσεις
που θα περιέχουν όσες ερωτήσεις θέλετε.<br>
<br>Υπάρχουν διάφοροι τύποι απαντήσεων για τις ερωτήσεις σας:<br><br>
<ul>
  <li>Πολλαπλής επιλογής (Μοναδική απάντηση)</li>
  <li>Πολλαπλής επιλογής (πολλαπλές απαντήσεις)</li>
  <li>Ταίριασμα</li>
  <li>Συμπλήρωμα κενών</li>
</ul>
Μια άσκηση περιλαμβάνει ένα μόνο κοινό τύπο ερωτήσεων</p>
<hr>
<b>Δημιουργία Aσκησης</b>
<p>Για να δημιουργήσετε μια άσκηση:
<ol>
  <li>Αρχικά πρέπει το υποσύστημα των ασκήσεων να είναι ενεργοποιημένο για το τρέχον μάθημα</li>
  <li>Κάντε κλίκ στο σύνδεσμο &quot;Καινούρια Aσκηση&quot;.</li>
  <li> Πληκτρολογήσετε το όνομα της άσκησης, καθώς και μία (προαιρετική) περιγραφή της.</li>
  <li> Μπορείτε να διαλέξετε μεταξύ 2 τύπων ασκήσεων :
    <ul>
      <li>Ερωτήσεις σε μία μόνο σελίδα</li>
      <li>Μία ερώτηση ανά σελίδα</li>
    </ul>
  </li>
  <li>Επιλέξτε ημερομηνία και ώρα έναρξης της άσκησης (π.χ. 1977-06-29 12:00:00). Πριν από αυτή οι διδασκόμενοι δεν θα μπορούν να συμμετέχουν στην άσκηση.</li>
  <li>Επιλέξτε ημερομηνία και ώρα λήξης της άσκησης (π.χ. 1977-06-29 12:00:00). Μετά από αυτή οι διδασκόμενοι δεν θα μπορούν να συμμετέχουν στην άσκηση.</li>
  <li>Καθορίστε τον χρονικό περιορισμό που θα έχουν οι συμμετέχοντες, δηλαδή πόσα λεπτά θα έχουν στην διάθεση τους για να ολοκληρώσουν όλες τις ερωτήσεις (0 για καθόλου περιορισμό). </li>
  <li>Εισάγεται πόσες επαναλήψεις επιτρέπονται, δηλαδή πόσες φορές θα μπορεί κάποιος να συμμετέχει στην άσκηση. Παρόμοια με πρίν 0 για απεριόριστο αριθμό επαναλήψεων.</li>
</ol>
<br>
<br>
Κατόπιν αποθηκεύστε την άσκηση. Θα μεταφερθείτε στην διαχείριση ερωτήσεων για την άσκηση.</p>
<hr>
<b>Προσθήκη Ερώτησης</b>
<p>Μπορείτε να προσθέσετε μια ερώτηση στην άσκηση που δημιουργήσατε προηγουμένως.
Η περιγραφή είναι προαιρετική, όπως και η εικόνα.</p>
<hr>
<b>Πολλαπλής Επιλογής</b>
<p>
Για να δημιουργήσετε μια ερώτηση πολλαπλής επιλογής:<br><br>
<ul>
  <li>Δημιουργήστε απαντήσεις για την ερώτηση σας. Μπορείτε να προσθέσετε ή να διαγράψετε μια απάντηση κάνοντας
κλίκ στο κατάλληλο κουμπί</li>
  <li>Τσεκάρετε το αριστερό κουμπί για την σωστή απάντηση</li>
  <li>Προσθέστε (αν θέλετε) ένα σχόλιο. Το σχόλιο δεν θα το δει ο μαθητής παρά μόνο όταν απαντήσει στην ερώτηση</li>
  <li>Δώστε ένα βάρος (βαθμό) σε κάθε απάντηση. Το βάρος (βαθμός) μπορεί να είναι ένας οποιοσδήποτε θετικός
ή αρνητικός αριθμός ή μηδέν.</li>
  <li>Αποθηκεύστε τις απαντήσεις σας</li>
</ul></p>
<hr>
<b>Σημπλήρωμα κενών</b>
<p>Μπορείτε να δημιουργήσετε ένα κείμενο με κενά. Αυτό έχει σαν σκοπό, να βρούν οι μαθητές τις λέξεις που
λείπουν.<br><br>
Για να διαγράψετε μια λέξη από το κείμενο, έτσι ώστε να δημιουργηθεί κενό, βάλτε την λέξη μεταξύ αγκυλών [όπως
αυτή].<br><br>
Από την στιγμή που το κείμενο έχει πληκτρολογηθεί και έχουν οριστεί τα κενά, μπορείτε να προσθέσετε ένα σχόλιο το
οποίο θα το δεί ο μαθητής όταν απαντήσει στην ερώτηση.<br><br>
Αποθηκεύστε το κείμενο σας, και θα προχωρήσετε στο επόμενο βήμα, που θα σας επιτρέψει να δώσετε ένα βάρος σε κάθε
κενό. Για παράδειγμα αν θέλετε να ορίσετε στην ερώτηση,  σαν άριστα το 10 και έχετε 5 κενά, μπορείτε να δώσετε σαν
βαθμολογία το 2 σε κάθε κενό.</p>
<hr>
<b>Ταίριασμα</b>
<p>Μπορείτε να δημιουργήσετε μια ερώτηση όπου ο μαθητής θα πρέπει να συνδυάσει στοιχεία από δύο σύνολα.<br><br>
Μπορείτε επίσης να ζητήσετε από τους μαθητές, να ταξινομήσουν στοιχεία με κάποια σειρά.<br><br>
Πρώτα ορίζετε τις επιλογές, μεταξύ των οποίων οι μαθητές θα μπορούν να διαλέξουν την σωστή απάντηση. Ύστερα ορίζετε
τις ερωτήσεις που θα πρέπει να συνδεθούν με τις επιλογές.
Τέλος, αντιστοιχήστε τες, μέσω του μενού, από το πρώτο σύνολο με αυτό του δεύτερου συνόλου.<br><br>
Σημείωση: Μερικά στοιχεία από το πρώτο σύνολο, μπορούν να δείχνουν στο ίδιο στοιχείο του δεύτερου συνόλου.<br><br>
Δώστε ένα βάρος σε κάθε σωστό ταίριασμα, και σώστε την απάντησή σας.</p>
<hr>
<b>Αλλαγή Aσκησης</b>
<p>Για να αλλάξετε μια άσκηση, εκτελείτε παρόμοιες ενέργειες όπως και στη δημιουργία.
Απλά κάντε κλίκ στην εικόνα <img src=\"../../template/classic/img/edit.gif\" border=\"0\" align=\"absmiddle\"> δίπλα στην άσκηση για να
την αλλάξετε.</p>
<hr>
<b>Διαγραφή Aσκησης</b>
<p>Για να διαγράψετε μια άσκηση, κάντε κλίκ στην εικόνα
<img src=\"../../template/classic/img/delete.gif\" border=\"0\" align=\"absmiddle\"> δίπλα από την άσκηση για να τη διαγράψετε.</p>
<hr>
<b>Ενεργοποίηση Aσκησης</b>
<p>Για να χρησιμοποιηθεί η άσκησή σας από τους μαθητές, πρέπει να την ενεργοποιήσετε κάνοντας κλίκ στην εικόνα
<img src=\"../../template/classic/img/invisible.gif\" border=\"0\" align=\"absmiddle\"> δίπλα από την άσκηση για να την ενεργοποιήσετε.</p>
<hr>
<b>Δοκιμή της Aσκησης</b>
<p>Μπορείτε να δοκιμάσετε την άσκησή σας κάνοντας κλίκ στο όνομά της.</p>
<hr>
<b>Τυχαίες ασκήσεις</b>
<p>Την ώρα της δημιουργίας / αλλαγής μιας άσκησης μπορείτε να ορίσετε αν θέλετε οι ερωτήσεις να εμφανίζονται σε τυχαία
σειρά.<br><br>
Αυτό σημαίνει ότι, ενεργοποιώντας την επιλογή αυτή, οι ερωτήσεις θα εμφανίζονται σε διαφορετική σειρά κάθε φορά
που θα εκτελείται η άσκηση από τους μαθητές.<br><br>
Αν έχετε ένα μεγάλο αριθμό ερωτήσεων, μπορείτε να επιλέξετε να εμφανίζονται τυχαία, ένας ορισμένος αριθμός
ερωτήσεων.</p>
<hr>
<b>Διαθέσιμες Ερωτήσεις</b>
<p>Οταν διαγράφετε μια άσκηση, οι ερωτήσεις τις δεν διαγράφονται από την βάση δεδομένων και μπορούν να
επαναχρησιμοποιηθούν σε μια καινούρια άσκηση.<br><br>
Με αυτό τον τρόπο μπορεί να χρησιμοποιήσετε τις ίδιες ερωτήσεις σε αρκετές ασκήσεις.<br><br>
Εξ' ορισμού, παρουσιάζονται όλες οι ερωτήσεις του μαθήματος σας. Μπορείτε να δείτε τις ερωτήσεις σχετικές με μια άσκηση,
επιλέγοντας την άσκηση από το μενού &quot;Φιλτράρισμα&quot;.<br><br>
Οι &quot;ερωτήσεις χωρίς απάντηση&quot; είναι αυτές που δεν ανήκουν σε καμμία άσκηση.</p>
<b>Αποτελέσματα</b>
<p>Μπορείτε να δείτε τα αποτελέσματα επιλέγοντας την μορφή που επιθυμείτε.</p>";


// help work

$langHWork = "Βοήθεια";
$langWorkContent = "
<p>Το εργαλείο των εργασιών είναι ένα ολοκληρωμένο σύστημα δημιουργίας / παράδοσης / βαθμολογίας εργασιών.</p>
<p>Σαν καθηγητής μπορείτε να δημιουργήσετε μια εργασία κάνοντας κλίκ στη <b>\"Δημιουργία Εργασίας\"</b>.
Συμπληρώστε τον τίλο της εργασίας, καθορίστε την ημερομηνία υποβολής και προεραιτικά προσθέστε και ένα
σχόλιο.</p>
<p>Όταν ολοκληρωθεί η δημιουργία της εργασίας, μην παραλείψετε να την ενεργοποιήσετε κάνοντας κλίκ
στο εικονίδιο <img src=\"../../template/classic/img/invisible.gif\" border=\"0\" align=\"absmiddle\">. Η εργασία θα είναι ορατή και
προσβάσιμη από τους φοιτητές μόνο όταν την ενεργοποιήσετε. Μπορείτε ανά πάσα στιγμή να διορθώσετε την
εργασία κάνοντας κλικ στο εικονίδιο <img src=\"../../template/classic/img/edit.gif\" border=\"0\" align=\"middle\"> ή να την
διαγράψετε κάνοντας κλικ στο εικονίδιο <img src=\"../../images/delete.gif\" border=\"0\" align=\"middle\">.
Κάνοντας κλικ στον τίτλο της εργασίας βλέπετε τις τυχόν εργασίες που έχουν υποβληθεί από τους φοιτητές.
Στα στοιχεία του φοιτητή βλέπετε τον αριθμό μητρώου, την ημερομηνία υποβολής και το αντίστοιχο αρχείο.
Κάνοντας κλικ στο \"Κατέβασμα όλων των εργασιών σε μορφή .zip \" θα \"κατεβάσετε\" όλα τα αρχεία που έχουν
υποβάλλει οι φοιτητές (σε συμπιεσμένη μορφή .zip) και που αντιστοιχούν στην εργασία που έχετε ορίσει.
Για να βαθμολογήσετε την εργασία συμπληρώστε τον αντίστοιχο βαθμό δίπλα στο όνομα του φοιτητή και κάντε
κλικ στο κουμπί <b>\"Υποβολή Βαθμολογίας\"</b>. Ο φοιτητής θα δει τον βαθμό του μόλις κάνει κλικ στο τίτλο της
εργασίας.</p>
<p>Ο φοιτητής μπορεί να δεί όλες τις εργασίες που έχουν υποβληθεί από τον καθηγητή.
Ο κατάλογος των εργασιών περιλαβάνει (εκτός από τον τίτλο της εργασίας), την προθεσμία υποβολής, τυχόν
βαθμολογία από τον καθηγητή και μια ένδειξη για το αν έχει στείλει εργασία ή όχι.
Μπορεί να υποβάλλει τη εργασία κάνοντας κλικ στον αντίστοιχο τίτλο της.
Να σημειωθεί, ότι ο φοιτητής δεν μπορεί να υποβάλλει εργασία μετά τη λήξη της προθεσμίας υποβολής.
Επίσης αν έχει \"ανεβάσει\" μια εργασία και θέλει να \"ανεβάσει\" ακόμα μία, η παλιά του εργασία θα σβηστεί και θα
αντικατασταθεί από την καινούρια.
</p>
";


// Help Group
$langHGroup="Βοήθεια ομάδων";
$langGroupContent="<p><b>Εισαγωγή</b></p>
<p>Το εργαλείο αυτό επιτρέπει τη δημιουργία και τη διαχείριση ομάδων
εργασίας. Κατά τη δημιουργία («Δημιουργία καινούριας ομάδας χρηστών»),
οι ομάδες είναι κενές. Υπάρχουν διάφοροι τρόποι για να συμπληρωθούν:
<ul><li>αυτόματα («Συμπλήρωση των ομάδων χρηστών»),</li>
<li>απο τον υπεύθυνο του μαθήματος («Διόρθωση της ομάδας χρηστών»),</li>
<li>με αυτοεγγραφή των φοιτητών («Αλλαγή ρυθμίσεων»: Οι φοιτητές επιτρέπονται να εγγραφούν...).</li>
</ul>
<p>Αυτοί οι τρείς τρόποι μπορούν να συνδυαστούν. Μπορείτε για παράδειγμα να ζητήσετε από τους
φοιτητές να εγγραφούν μόνοι τους. Αργότερα, διαπιστώνετε ότι κάποιοι δεν εγγράφηκαν
και επιλέγετε την αυτόματη συμπλήρωση ομάδων. Μπορείτε επίσης να αλλάξετε τα μέλη των
ομάδων πριν ή μετά την αυτόματη συμπλήρωση ή την εγγραφή από τους ίδιους.</p>
<p>Η εγγραφή σε ομάδες, αυτόματα ή όχι, λειτουργεί μόνο εάν υπάρχουν ήδη
φοιτητές εγγεγραμμένοι στο μάθημα (η εγγραφή σε ομάδες δε σχετίζεται με την
εγγραφή στο ίδιο το μάθημα). Οι εγγεγραμμένοι φοιτητές στο μάθημα εμφανίζονται στο
εργαλείο «Χρήστες».</p>
<hr noshade size=1>
<p><b>Δημιουργία ομάδων</b></p>
<p>Για να δημιουργήσετε νέες ομάδες, επιλέξτε το «Δημιουργία καινούριας ομάδας χρηστών»
και ορίστε πόσες ομάδες θα δημιουργηθούν. Ο μέγιστος αριθμός μελών είναι προαιρετικός
αλλά προτείνουμε να επιλέξετε κάποιον. Αν δεν τον αλλάξετε, στις ομάδες
θα μπορούν να εγγραφούν απεριόριστα μέλη.</p>
<hr noshade size=1>
<p><b>Ρυθμίσεις ομάδων</b></p>
<p>Μπορείτε να ορίσετε τις ρυθμίσεις που ισχύουν για όλες τις ομάδες.
<p><b>Ρυθμίσεις ομάδων χρηστών</b>:
<p>Μπορείτε να δημιουργήσετε κενές ομάδες και να επιτρέψετε την εγγραφή φοιτητών
σε αυτές. Αν έχετε ορίσει μέγιστο αριθμό μελών, οι πλήρεις ομάδες δε δέχονται
νέα μέλη. Αυτή η μέθοδος εξυπηρετεί όταν δε γνωρίζετε τον ακριβή κατάλογο των
φοιτητών όταν δημιουργείτε ομάδες.</p>
<p><b>Εργαλεία</b>:</p>
<p>Κάθε ομάδα έχει είτε μια περιοχή συζητήσεων (δημόσια ή ιδιωτική) είτε
μια περιοχή εγγράφων (ένα σύστημα διαχείρισης αρχείων που μοιράζονται τα μέλη)
είτε (συχνότερα) και τα δύο.</p>
<hr noshade size=1>
<p><b>Διόρθωση ομάδων</b></p>
<p>Αφού δημιουργθούν οι ομάδες, στο κάτω μέρος της σελίδας εμφανίζεται ο κατάλογος
των ομάδων με μια σειρά από πληροφορίες και λειτουργίες. Επιλέξτε:
<ul><li><b>Διόρθωση</b> για να αλλάξετε το όνομα της ομάδας, την περιγραφή,
τον διδάσκοντα και τον κατάλογο των μελών.</li>
<li><b>Διαγραφή</b> για να διαγράψετε μια ομάδα.</li></ul>
<hr noshade size=1>";

// Help Agenda
$langHAgenda = "Ατζέντα";
$langAgendaContent = "<p>Αν επιθυμείτε να προσθέσετε ένα γεγονός στην ατζέντα,
μπορείτε να επιλέξετε την ημερομηνία του γεγονότος, να δώσετε ένα τίτλο, να αναφέρετε τις λεπτομέρειες και στη
συνέχεια να πατήσετε το πλήκτρο 'εντάξει'. Με αυτό τον τρόπο, το γεγονός προστίθετε στην ατζέντα.</p><p>
Στη συνέχεια, σας δίνεται η δυνατότητα αν επιθυμείτε, να αλλάξετε κάποια από τις παραμέτρους του γεγονότος,
επιλέγοντας 'τροποποίηση' ή να διαγράψετε κάποιο γεγονός από την ατζέντα επιλέγοντας 'διαγραφή'.</p>";

// Help link

$langHLink = "Σύνδεσμοι";
$langLinkContent = "<p>Σας δίνεται η δυνατότητα να προσθέσετε συνδέσμους προς κάποιες υπάρχουσες ιστοσελίδες στο
δίκτυο (ή κάπου αλλού στον δικό σας δικτυακό τόπο).</p><p>Για να προσθέσετε ένα σύνδεσμο επιλέξτε 'Προσθήκη
συνδέσμου'. Πληκτρολογήστε το URL, τον τίτλο και κάποια περιγραφή του συνδέσμου. Επιλέξτε την κατηγορία στην οποία θέλετε να προστεθεί ο
σύνδεσμος και στη συνέχεια πατήστε το πλήκτρο 'Προσθήκη'.</p> <p>Για να προσθέσετε μια νέα κατηγορία συνδέσμων,
με σκοπό να ομαδοποιήσετε κάποιους συνδέσμους, επιλέξτε 'Προσθήκη κατηγορίας'.  Πληκτρολογήστε το όνομα και την
περιγραφή της κατηγορίας. Στη συνέχεια πατήστε το πλήκτρο 'Προσθήκη'. Επίσης μπορείτε να αλλάξετε το όνομα ή την
περιγραφή του συνδέσμου επιλέγοντας 'αλλαγή' ή να διαγράψετε κάποιο σύνδεσμο επιλέγοντας 'διαγραφή'.
Επιλέγοντας 'διαγραφή' κατηγορίας διαγράφετε την κατηγορία και όλους τους συνδέσμους που περιέχει.</p>
<p> Τέλος υπάρχει η επιλογή 'Εμφάνιση' αν θέλετε να εμφανιστούν οι σύνδεσμοι που περιέχει μια κατηγορία
και η επιλογή 'Απόκρυψη' αν θέλετε να μην εμφανίζονται.</p>";

// Help announcements

$langHAnnounce = "Ανακοινώσεις";
$langAnnounceContent = "<p>Σας δίνεται η δυνατότητα να  προσθέσετε ανακοινώσεις στην σελίδα του μαθήματος
πληκτρολογώντας  την περιγραφή της  και στη συνέχεια πατώντας το πλήκτρο 'εντάξει'.</p><p>Επίσης μπορείτε να
αλλάξετε το περιεχόμενο της ανακοίνωσης επιλέγοντας 'Aλλαγή' ή να διαγράψετε μια ανακοίνωση  επιλέγοντας
'Διαγραφή'. Αν επιθυμείτε η ανακοίνωση να σταλεί και με mail στους φοιτητές που είναι εγγεγραμμένοι στο
μάθημα, τότε επιλέγετε 'Αποστολή (με email) της ανακοίνωσης στους εγγεγραμμένους μαθητές'.</p>";

// Help profile

$langHProfile = "Αλλαγή προσωπικών στοιχείων";
$langProfileContent = "<p>Σας δίνετε η δυνατότητα να αλλάξετε κάποια από τα προσωπικά σας στοιχεία που
χρησιμοποιείτε στην πλατφόρμα.</p>
<li>Σε περίπτωση που έχει γίνει κάποιο λάθος στην καταχώρηση σας μπορείτε να αλλάξετε το όνομα, το επώνυμο και
την ηλεκτρονική σας διεύθυνση.</li>
<li>Επίσης αν επιθυμείτε μπορείτε να αλλάξετε το όνομα χρήστη και το συνθηματικό σας.</li>
<li>Τέλος για να καταχωρηθούν στη βάση δεδομένων οι αλλαγές που κάνατε πατήστε το πλήκτρο 'Αλλαγή'.</li>";


// Help Video

$langHVideo = "Βίντεο";
$langVideoContent = "<b><u>Περιγραφή</u></b>
<p>
Μέρος του υλικού των μαθημάτων που προσφέρονται από την εφαρμογή e-class μπορεί να είναι οπτικοακουστικό υλικό. Οι τρόποι με τους οποίους μπορεί να διανεμηθεί το υλικό αυτό είναι δύο. Ο πρώτος είναι το απλό κατέβασμα των αρχείων τοπικά στο απομακρυσμένο υπολογιστή και η τοπική αναπαραγωγή του μετά την ολοκλήρωση του κατεβάσματος. Ο δεύτερος είναι η διάθεση του οπτικοακουστικού υλικού από έναν εξυπηρετητή ροών με πλεονέκτημα την άμεση αναπαραγωγή του υλικού χωρίς καθυστέρηση στον απομακρυσμένο υπολογιστή. Το υποσύστημα video δίνει την δυνατότητα σύνδεσης της εφαρμογής e-class με εξυπηρετητή ροών.</p>
<b><u>Λειτουργίες Εκπαιδευτή</u></b>
<p>
Μπορείτε να ανεβάσετε αρχεία video (τύπου mpeg, avi κ.λπ.) στην πλατφόρμα. Επιλέξτε \"Προσθήκη βίντεο\" και στη συνέχεια πληκτρολογήστε το όνομα του αρχείου ή κάντε κλικ στον πλήκτρο \"Browse\" για να το αναζητήσετε. Επίσης, πληκτρολογήστε στα αντίστοιχα πεδία το τίτλο του βίντεο και, αν επιθυμείτε, μια σύντομη περιγραφή. Μετά κάντε κλικ στο πλήκτρο \"Προσθήκη\" για να γίνει το \"ανέβασμα\" στη πλατφόρμα. Έχετε τη δυνατότητα να προσθέσετε συνδέσμους βιντεοσκοπημένων μαθημάτων στην σελίδα του μαθήματός σας. Επιλέξτε \"Προσθήκη συνδέσμου βίντεο\" πληκτρολογήστε την διεύθυνση του vod server, στον οποίο βρίσκεται το αρχείο βιντεοσκοπημένου μαθήματος που επιθυμείτε να προσθέσετε, στο πεδίο \"URL\". Στη συνέχεια πληκτρολογήστε τον τίτλο και την περιγραφή και τέλος πατήστε το πλήκτρο \"Προσθήκη\". Μπορείτε να αλλάξετε κάποια από τις παραμέτρους επιλέγοντας \"Αλλαγή\" ή να διαγράψετε κάποιο σύνδεσμο ή αρχείο επιλέγοντας \"Διαγραφή\". Επίσης επιλέγοντας \"Διαγραφή όλων\" διαγράφετε όλους τους συνδέσμους προς βιντεοσκοπημένα μαθήματα και όλα τα αρχεία που υπάρχουν στην σελίδα του μαθήματος. Αν το σύστημα είναι συνδεμένο με εξυπηρετητή ροών η σύνδεση είναι διάφανη στον εκπαιδευτή και δεν υπάρχει ανάγκη να κάνει κάποια επιπλέον κίνηση ή ενέργεια για να διατεθούν τα αρχεία οπτικοακουστικού υλικού από τον εξυπηρετητή ροών. Πρέπει να έχετε υπ' όψιν σας ότι το υλικό θα είναι διαθέσιμο από τον εξυπηρετητή ροών και εκτός της εφαρμογής e-class εάν κάποιος γνωρίζει τον απευθείας σύνδεσμο του αρχείου στον εξυπηρετητή ροών.
</p>
<b><u>Λειτουργίες Εκπαιδευόμενου</u></b>
<p>Επιλέξτε το βίντεο που θέλετε να δείτε. Αν το σύστημα είναι συνδεμένο με εξυπηρετητή ροών ο εκπαιδευόμενος πρέπει να γνωρίζει με
ποιο πρόγραμμα πελάτη μπορεί να αναπαράγει τα αρχεία οπτικοακουστικού υλικού</p>
";



// Help Conference

$langHConference = "Τηλεσυνεργασία";
$langConferenceContent = "
<b><u>Περιγραφή</u></b>
<p>Δίνει την δυνατότητα επικοινωνίας σε πραγματικό χρόνο μεταξύ των εκπαιδευτών και των εκπαιδευόμενων.<br></p>
<p>Το συγκεκριμένο εργαλείο έχει 4 λειτουργικότητες:</p>
<ul>
	<li>Τηλεδιάσκεψη(Πρέπει να έχει ενεργοποιηθεί από τον διαχειριστή της πλατφόρμας eclass)</li>
	<li>Βίντεο</li>
	<li>Παρουσίαση</li>
	<li>Ανταλλαγή μηνυμάτων</li>
</ul>
<b><u>Τηλεδιάσκεψη</u></b>
<p><b><u>Λειτουργίες Εκπαιδευτή</u></b></p>
<p>Για τη λειτουργία τηλεδιάσκεψης, ο εκπαιδευτής δεν έχει παρά να επιλέξει αυτή τη ρύθμιση κατά την είσοδό του στο υποσύστημα και αυτόματα ενεργοποιείται η δυνατότητα σε όλους τους εκπαιδευόμενους που έχουν εισέλθει στο υποσύστημα. Το κάθε μάθημα της πλατφόρμας διαθέτει το δικό του \"εικονικό δωμάτιο τηλεδιάσκεψης\", στο οποίο βρίσκονται συνδεδεμένοι όλοι οι συμμετέχοντες. Ο εκπαιδευτής δεν έχει τρόπο να ελέγχει το ποιός έχει το λόγο και πρέπει να εξηγήσει στους συμμετέχοντες τους κανόνες της τηλεδιάσκεψης που επιθυμεί να τηρήσει. Καλό θα ήταν ο εκπαιδευτής να προτρέπει τους εκπαιδευόμενους για διατήρηση κλειστού μικροφώνου (muting) σε όλους τους συνδεδεμένους σταθμούς μέχρι την ανάγκη επαφής με τον εκπαιδευτή, ώστε τυχόν θόρυβος από τους εκπαιδευόμενους να μην μεταδίδεται άκαιρα στους συμμετέχοντες. Επίσης, συνιστάται η διενέργεια δοκιμαστικής τηλεδιάσκεψης πριν την αναγκαία ημέρα και ώρα, ώστε να υπάρχει προηγούμενη εξοικείωση των συμμετεχόντων με το περιβάλλον.
Οι απαιτήσεις σε εξοπλισμό για τη λειτουργικότητα αυτή είναι η ύπαρξη ηχείων και μικροφώνου (προαιρετική η ύπαρξη κάμερας), ενώ οι α
παιτήσεις σε λογισμικό είναι η χρήση αποκλειστικά του Microsoft Internet Explorer και ταυτόχρονα η ύπαρξη του NetMeeting στην τελευτ
αία του έκδοση 3.0.1 στον προσωπικό υπολογιστή, πράγμα που συμβαίνει αυτόματα σε υπολογιστές με λειτουργικό σύστημα WinXP, ενώ υπάρχ
ει η δυνατότητα εγκατάστασης σε Win2000,Win98,WinNT κ.λ.π. Εφόσον το NetMeeting δεν έχει χρησιμοποιηθεί σε αυτόν τον υπολογιστή, οι
προεπιλεγμένες ρυθμίσεις δεν θα λειτουργήσουν σωστά. Αν όμως έχει μεσολαβήσει η ρύθμιση των επιλογών \"Advanced Calling\" σε προηγούμενη φάση, θα χρειαστεί η ενεργοποίηση της χρήσης του Gatekeeper που θα δωθεί από τον διαχειριστή της πλατφόρμας eclass. Καλή πρακτική για την αποφυγή προβλημάτων κατά την διεξαγωγή των τηλεδιασκέψεων είναι η χρήση του \"Audio Tuning Wizard\" σε φάση προετοιμασίας για την τηλεδιάσκεψη. Η επιλογή αυτή είναι διαθέσιμη στο μενού \"Tools\" του NM και επιτρέπει στον χρήστη να επιβεβαιώσει με απλό τρόπο την καλή λειτουργία των ηχείων (για να λαμβάνει τον ήχο των υπολοίπων) και του μικροφώνου του (για να μπορεί να παίρνει και ο ίδιος τον λόγο).
</p>
<p><b><u>Λειτουργίες Εκπαιδευόμενου</u></b></p>
<p>Ο εκπαιδευόμενος θα αποκτήσει τη δυνατότητα μέσα από τις ιστοσελίδες του υποσυστήματος εφόσον το έχει ενεργοποιήσει ο εκπαιδευτής με την επιλογή του κατά την είσοδό του στο υποσύστημα «Κουβέντα». Η κλήση σύνδεσης στην τηλεδιάσκεψη γίνεται αυτόματα με την είσοδο του εκπαιδευόμενου στο υποσύστημα «Κουβέντα», εφόσον έχει ρυθμιστεί σωστά την πρώτη φορά το NetMeeting για χρήση του gatekeeper, όπως περιγράφεται παραπάνω. Το κάθε μάθημα της πλατφόρμας διαθέτει το δικό του \"εικονικό δωμάτιο τηλεδιάσκεψης\", στο οποίο βρίσκονται συνδεδεμένοι όλοι οι συμμετέχοντες. Καλή πρακτική είναι η διατήρηση κλειστού μικροφώνου (muting) μέχρι την ανάγκη επαφής με τον εκπαιδευτή, ώστε θόρυβοι ή ομιλίες να μην μεταδίδονται άκαιρα στους υπόλοιπους συμμετέχοντες.</p>

<b><u>Βίντεο</u></b>
<p><b><u>Λειτουργίες Εκπαιδευτή</u></b></p>
<p>
Ο καθηγητής επιλέγει «video» εμφανίζεται ένα πεδίο που του λέει να τοποθετήσει το σύνδεσμο του βίντεο. Όταν τοποθετήσει το link και πατήσει «Play»φορτώνει από πάνω το video που επέλεξε στον ανάλογο πρόγραμμα παρουσίασης αναπαραγωγής οπτικοακουστικού υλικού  σύμφωνα με την κατάληξη που έχει. Ο εκπαιδευτής πρέπει να έχει ενημερώσει τους εκπαιδευόμενους για τον τύπο του προγράμματος αναπαραγωγής οπτικοακουστικού υλικού που πρέπει να διαθέτουν. </p>
<p><b><u>Λειτουργίες Εκπαιδευόμενου</u></b></p>
<p>Ο εκπαιδευόμενος από την πλευρά του δεν χρειάζεται να κάνει τίποτα. Αυτόματα θα φορτώσει το ανάλογο πρόγραμμα αναπαραγωγής οπτικοακουστικού υλικού αναλόγως το βιντεο που επέλεξε ο εκπαιδευτής</p>

<b><u>Παρουσίαση</u></b>
<p><b><u>Λειτουργίες Εκπαιδευτή</u></b></p>
<p>Ο καθηγητής τοποθετεί την ιστοσελίδα που θέλει να εμφανίσει στους εκπαιδευόμενους στο πεδίο «Σύνδεσμος παρουσίασης» και στη συνέχει πατάει το πλήκτρο «ΟΚ». Αμέσως θα εμφανιστεί στον παράθυρο παρουσίασης η ιστοσελίδα που επέλεξε όπως επίσης και στα παράθυρα των εκπαιδευόμενων. Από αυτή τη στιγμή και μετά δεν υπάρχει συγχρονισμός μεταξύ εκπαιδευτή-εκπαιδευόμενου στο παράθυρο παρουσίαση. Π.χ. Στην συνέχει αν αποφασίσει να πατήσει ένα σύνδεσμο μέσα στο παράθυρο της παρουσίασης πρέπει να ενημερώσει του εκπαιδευόμενους για την κίνησή του.</p>
<p><b><u>Λειτουργίες Εκπαιδευόμενου</u></b></p>
<p>Στον εκπαιδευόμενο παρουσιάζεται ο σύνδεσμος που επέλεξε ο καθηγητής  και στη συνέχεια πρέπει να ακολουθεί τις οδηγίες που τους λέει ο καθηγητής ώστε να ακολουθεί τους συνδέσμους που πατάει.</p>
<b><u>Ανταλλαγή μηνυμάτων</u></b>
<p><b><u>Λειτουργίες Εκπαιδευτή</u></b></p>
<p>Ο εκπαιδευτή έχει την δυνατότητα να ανταλλάσει μηνύματα με όσους χρήστες είναι εγγεγραμμένοι στο μάθημα πληκτρολογώντας το μήνυμα σας και συνέχεια κάνοντας κλικ στο πλήκτρο '>>'. Τέλος μπορείτε να διαγράψετε όλα τα μηνύματα που υπάρχουν στην ζωντανή συζήτηση επιλέγοντας 'Καθάρισμα'.</p>
<p><b><u>Λειτουργίες Εκπαιδευόμενου</u></b></p>
<p>Ο εκπαιδευόμενος έχει την δυνατότητα να ανταλλάσει μηνύματα με όσους χρήστες είναι εγγεγραμμένοι στο μάθημα πληκτρολογώντας το μήνυμα σας και συνέχεια κάνοντας κλικ στο πλήκτρο '>>'.</p>
";


// Help course description

$langHCoursedescription = "Πληροφορίες Μαθήματος";
$langCoursedescriptionContent = "<p>Έχετε τη δυνατότητα να προσθέσετε πληροφορίες για το μάθημα επιλέγοντας
'Δημιουργία και Διόρθωση'. Μπορείτε να προσθέσετε μια κατηγορία, επιλέγοντας την από τον κατάλογο με τις
υπάρχουσες κατηγορίες και κάνοντας κλικ στο πλήκτρο 'Προσθήκη'.</p><p>Στη συνέχεια μπορείτε να πληκτρολογήσετε  τις
πληροφορίες που επιθυμείτε σχετικά με την κατηγορία που επιλέξατε και για να ολοκληρωθεί η προσθήκη των
πληροφοριών πατήστε το πλήκτρο 'Προσθήκη'.</p><p>Αν τελικά δεν επιθυμείτε να προσθέσετε την κατηγορία που επιλέξατε
πατήστε 'Επιστροφή και Ακύρωση'. Οποιαδήποτε χρονική  στιγμή  σας δίνετε η δυνατότητα να αλλάξετε τις πληροφορίες
μιας κατηγορίας επιλέγοντας 'Αλλαγή' ή να διαγράψετε τις πληροφορίες μιας κατηγορίας επιλέγοντας
'Διαγραφή'.</p>";

// Help external  module

$langHModule = "Προσθήκη συνδέσμου στην αρχική σελίδα";
$langModuleContent = "<p>Αν θέλετε να προσθέσετε συνδέσμους από την αρχική σελίδα του μαθήματος προς ιστοσελίδες
που υπάρχουν ήδη κάπου αλλού στο δίκτυο (ή ακόμα κάπου αλλού στον δικό σας δικτυακό τόπο) πληκτρολογήστε το
σύνδεσμο
και τον τίτλο του συνδέσμου και στη συνέχεια πατήστε το πλήκτρο 'Προσθήκη'. Οι σελίδες που προσθέτετε εσείς στην
αρχική σελίδα μπορούν να απενεργοποιηθούν και να διαγραφούν, ενώ τα ενσωματωμένα εργαλεία μπορούν να
απενεργοποιηθούν μόνο, αλλά όχι να διαγραφούν.</p>";

//Help import page
$langHImport = "Ανέβασμα ιστοσελίδας / αρχείου";
$langImportContent = "<p>Αν θέλετε μπορείτε να ανεβάσετε αρχείο σχετικό με το μάθημα σας. Το αρχείο αυτό θα αποθκευτεί στον εξυπηρετητή του e-Class. Θα δημιουργηθεί σύνδεσμος προς αυτό στο αριστερό μενού του μαθήματος
που θα ανοίγει σε νέο παράθυρο του φυλλομετρητή του χρήστη.</p>
<p>Για να το κάνετε αυτό πατήστε το πλήκτρο 'Browse', επιλέξτε το αρχείο που θέλετε να ανεβάσετε, δώστε ένα τίτλο στο πεδίο 'Τίτλος σελίδας' και πιέστε το πλήκτρο 'Προσθήκη'.</p>
<p>Ο σύνδεσμος του αρχείου που ανεβάζετε μπορεί να απενεργοποιηθεί και να διαγραφεί από το εργαλείο
'Διαχείριση εργαλείων'.</p>";

//Help Course tools
$langHcourseTools = "Διαχείριση εργαλείων";
$langcourseToolsContent = "<p>Αυτό το εργαλείο προσφέρει τη δυνατότητα στον καθηγητή να ενεργοποιήσει και να απενεργοποιήσει τα εργαλεία του μαθήματος. Στις δύο στήλες παρουσιάζεται η κατάσταση του κάθε εργαλείου , δηλαδή αν είναι ενεργό ή όχι.</p>

<p>Για να αλλάξετε την κατάσταση κάποιου εργαλείου κάντε κλικ στο αντίστοιχο εργαλείο και στη συνέχεια πιέστε το πλήκτρο '>>' για να αλλαχθεί η κατάσταση του εργαλείου. Μπορείτε να μετακινείσετε πολλά εργαλεία από την μια στήλη στην άλλη με CTRL+κλίκ. Τέλος πιέστε το πλήκτρο 'Υποβολή αλλαγών' στο τέλος του table, για να αποθηκευτούν οι αλλαγές σας.</p>
";


// Help Scorm / Learning Path


$langHPath="Βοήθεια - Γραμμή Μάθησης";

$langPathContent="
Το εργαλείο Γραμμή Μάθησης έχει τέσσερις λειτουργίες:
<ul>
<li>Δημιουργία γραμμής μάθησης</li>
<li>Εισαγωγή γραμμής μάθησης από πρότυπο SCORM ή IMS</li>
<li>Εξαγωγή γραμμής μάθησης σε πρότυπο συμβατό με Scorm 2004 ή 1.2</li>
<li>Παρακολούθηση των εκπαιδευόμενων στις γραμμές μάθησης</li>
</ul>

<p><b>Τί είναι η γραμμή μάθησης ;</b></p>

<p>Η γραμμή μάθησης είναι μια ακολουθία από βήματα μάθησης που περιλαμβάνονται σε ενότητες.
Μπορεί να είναι είτε βασισμένη σε περιεχόμενo (μοιάζοντας με πίνακα περιεχομένων)
είτε βασισμένη σε ενέργειες, μοιάζοντας με ατζέντα ή πρόγραμμα του τί χρειάζεται να κάνει
ο εκπαιδευόμενος για να κατανοήσει ή να εκπαιδευτεί σε μια συκγκεκριμένη πηγή γνώσης.
</p>

<p>Επιπροσθέτως του να είναι δομημένη, μια γραμμή μάθησης μπορεί επίσης να έχει μια
συγκεκριμένη αλληλοδιαδοχή. Αυτό σημαίνει πως κάποια βήματα είναι προαπαιτούμενα για τα
αμέσως επόμενα αυτών βήματα (\"δεν μπορείτε να πάτε στο βήμα 2 πρίν το βήμα 1\").
Η αλληλοδιαδοχή μπορεί να είναι μόνο υποδηλωτική (τα βήματα εμφανίζονται το ένα μετά
το άλλο).</p>

<p><b>Πώς δημιουργείτε τη δικιά σας γραμμή μάθησης ;</b></p>

<p>Το πρώτο βήμα είναι να προσπελάσετε τον τομέα Λίστα Γραμμών Μάθησης. Στην κεντρική
οθόνη της λίστας γραμμών μάθησης, υπάρχει ένας ειδικός σύνδεσμος. Εκεί μπορείτε να
δημιουργήσετε όσες γραμμές μάθησης επιθυμείτε κάνοντας κλικ στο
<i>Δημιουργία νέας γραμμής μάθησης</i>. Δημιουργούνται με αυτόν τον τρόπο κενές
γραμμές μάθησης, μέχρι να τους προσθέσετε ενότητες και βήματα.</p>

<p><b>Ποιά είναι τα βήματα για τις γραμμές αυτές ; (Ποιά είναι τα αντικείμενα που μπορούν να προστεθούν ;)</b></p>

<p>Κάποια από τα εργαλεία, τις ενέργειες και το περιεχόμενο του Eclass που θεωρείτε
χρήσιμα και κατάλληλα για τη γραμμή σας μπορούν να προστεθούν:</p>

<ul>
<li>Ξεχωριστά έγγραφα (κείμενα, εικόνες, Έγγραφα τύπου Office, ...)</li>
<li>Ετικέτες</li>
<li>Σύνδεσμοι</li>
<li>Ασκήσεις του Eclass</li>
<li>Περιγραφή μαθήματος</li>
</ul>

<p><b>Άλλα χαρακτηριστικά της Γραμμής Μάθησης</b></p>

<p>Μπορεί να ζητηθεί από τους εκπαιδευόμενους να ακολουθήσουν (αναγνώσουν)
τη γραμμή σας με μια συγκεκριμένη σειρά. Αυτό σημαίνει για παράδειγμα ότι οι
εκπαιδευόμενοι δεν μπορούν να προσπελάσουν την Άσκηση 2 αν δεν έχουν διαβάσει
το Έγγραφο 1. Όλα τα αντικείμενα έχουν κατάσταση: ολοκληρωμένη ή μη ολοκληρωμένη,
επομένως η πρόοδος των εκπαιδευόμενων είναι πάντα διαθέσιμη μέσω του ειδικού
εργαλείου <i>Παρακολούθηση γραμμών μάθησης</i>.</p>

<p>Αν θέλετε να τροποποιήσετε τον αρχικό τίτλο ενός βήματος, ο νέος τίτλος
μπορεί να φαίνεται στη γραμμή, χωρίς να επηρεαστεί ο αρχικός. Επομενώς, αν θέλετε
να εμφανίσετε το test8.doc ως 'Τελική Εξέταση' στη γραμμή, δε χρειάζεται να
μετονομάσετε το αρχείο, αλλά αρκεί να χρησιμοποιήσετε έναν άλλο τίτλο στη γραμμή.
Είναι επίσης προτεινόμενο να δίνετε νέους τίτλους στους συνδέσμους αν οι
τελευταίοι έχουν μεγάλο μήκος ονόματος.</p>
<br>


<p><b>Τί είναι η γραμμή μάθησης κατά το πρότυπο Scorm ή IMS και πώς μπορείτε να το εισάγετε ;</b></p>

<p>Το εργαλείο γραμμή μάθησης σας επιτρέπει να ανεβάσετε και να εισάγετε
εκπαιδευτικό περιεχόμενο συμβατό με τα πρότυπα SCORM και IMS.</p>

<p>Το SCORM (<i>Sharable Content Object Reference Model</i>) είναι ένα διεθνές
πρότυπο, με το οποίο έχουν ταχθεί πολλοί κυρίαρχοι οργανισμοί που ασχολούνται
με την ασύγχρονη τηλεκπαίδευση (e-Learning), όπως οι: NETg, Macromedia,
Microsoft, Skillsoft, κλπ και το οποίο δρα σε τρείς τομείς:</p>

<ul>
<li><b>Οικονομία</b>: Το πρότυπο Scorm επιτρέπει σε ολόκληρα μαθήματα ή
μικρότερες ενότητες περιεχομένου να επαναχρησιμοποιηθούν σε διαφορετικές
πλατφόρμες τηλεκπαίδευσης (Learning Management Systems - LMS) μέσω του
διαχωρισμού του περιεχομένου και της δομής του,</li>
<li><b>Παιδαγωγική</b>: Το πρότυπο Scorm ενσωματώνει τις έννοιες των
προαπαιτούμενων ή της <i>αλληλοδιαδοχής</i> (<i>π.χ. </i>\"Δεν
μπορείτε να προσπελάσετε το κεφάλαιο 2 αν δεν ολοκληρώσετε επιτυχώς την Άσκηση 1\"),</li>
<li><b>Τεχνολογία</b>: Το πρότυπο Scorm συνθέτει έναν πίνακα περιεχομένων
ως ένα επιπλέον επίπεδο αφαιρετικότητας, ασχέτως περιεχομένου και πλατφόρμας
τηλεκπαίδευσης. Βοηθάει τις έννοιες του περιεχομένου και της πλατφόρμας
τηλεκπαίδευσης στο να επικοινωνούν μεταξύ τους. Η επικοινωνία αυτή αποτελείται
κυρίως από <i>δείκτες</i> (\"Πού ακριβώς βρίσκεται ο Γιάννης στο μάθημα ;\"),
<i>βαθμολογία</i> (\"Με τί βαθμό πέρασε ο Γιάννης την άσκηση ;\") και <i>χρόνο</i>
(\"Πόσο χρόνο ξόδεψε ο Γιάννης στο κεφάλαιο 1 ;\").</li>
</ul>

<p><b>Πώς δημιουργείτε μια γραμμή μάθησης συμβατή με το πρότυπο SCORM ;</b></p>

<p>Η πιο φυσική μέθοδος είναι να χρησιμοποιήσετε το εργαλείο δημιουργίας γραμμής
μάθησης του Eclass και στη συνέχεια να το εξάγετε κάνοντας κλικ στο κατάλληλο
εικονίδιο. Όμως, μπορεί να θέλετε να δημιουργήσετε εκπαιδευτικό περιεχόμενο
συμβατό με το πρότυπο Scorm τοπικά στον υπολογιστή σας και στη συνέχεια να το
εισάγετε στο εργαλείο γραμμή μάθησης του Eclass. Σε αυτήν την περίπτωση, σας
προτείνουμε να χρησιμοποιήσετε κάποιο εξεζητημένο εργαλείο όπως το
Lectora&reg; ή το Reload&reg;</p>

<p><b>Χρήσιμοι σύνδεσμοι</b></p>

<ul>
<li>Adlnet: ή υπεύθυνη αρχή για την κανονικοποίηση του προτύπου Scorm, <a
href=\"http://www.adlnet.org/\">http://www.adlnet.org</a></li>
<li>Reload: εργαλείο Ελεύθερου Λογισμικού/Λογισμικού Ανοικτού Κώδικα για σύνταξη και
ανάγνωση περιεχομένου Scorm, <a
href=\"http://www.reload.ac.uk/\">http://www.reload.ac.uk</a></li>
<li>Lectora: εργαλείο για σύνταξη και δημοσίευση περιεχομένου Scorm, <a
href=\"http://www.trivantis.com/\">http://www.trivantis.com</a></li>
</ul>

<p><b>Σημείωση:</b></p>

<p>Ο τομέας Λίστα γραμμών μάθησης εμφανίζει όλες τις γραμμές μάθησης
<i>που δημιουργήθηκαν μέσω του Eclass</i> και όλες τις εισηγμένες
γραμμές μάθησης <i>συμβατές με το πρότυπο Scorm</i>.</p>
";

//Help Dropbox

$langHDropbox="Χώρος Ανταλλαγής Αρχείων";

$langDropboxContent="<p>Ο Χώρος Ανταλλαγής Αρχείων είναι ένα εργαλείο ανταλλαγής αρχείων μεταξύ διδάσκων
και φοιτητών. Μπορείτε να ανταλλάξετε οποιοδήποτε τύπο αρχείων (π.χ. αρχεία Word, Excel, PDF κ.λπ.)</p>
<p>Υπάρχουν δύο κατάλογοι στο Χώρο Ανταλλαγής Αρχείων. Στον κατάλογο <b>Εισερχόμενα Αρχεία</b>
εμφανίζονται τα αρχεία που έχετε παραλάβει από άλλους χρήστες της πλατφόρμας, με κάποιες επιπλέον
πληροφορίες που αφορούν το αρχείο, όπως το όνομα του χρήστη, το μέγεθος του αρχείου και η ημερομηνία που
το παραλάβατε. Στον κατάλογο <b>Απεσταλμένα Αρχεία</b> εμφανίζονται τα αρχεία που έχετε στείλει
σε άλλους χρήστες της πλατφόρμας με τις αντίστοιχες πληροφορίες.</p>
<p>Αν ο κατάλογος με τα αρχεία που έχετε παραλάβει ή τα αρχεία που έχετε αποστείλει, γίνει αρκετά μεγάλος
μπορείτε να τον ελαττώσετε διαγράφοντας όλα ή μερικά από τα αρχεία του.
Σημειώστε, ότι το αρχείο δεν διαγράφεται από τη βάση δεδομένων της πλατφόρμας πάρα μόνο από τον κατάλογο.</p>
<p>Για να στείλετε ένα αρχείο σε κάποιον χρήστη, αρχικά επιλέξτε το αρχείο στον υπολογιστή σας
χρησιμοποιώντας το πλήκτρο Browse. Προαιρετικά μπορείτε να πληκτρολογήστε μια σύντομη περιγραφή.
Επιλέξτε από τον κατάλογο των χρηστών τον παραλήπτη του αρχείου και κάντε κλικ στο πλήκτρο 'Αποστολή'.
Αν θέλετε το αρχείο να σταλεί σε περισσότερους χρήστες, επιλέξτε τους επιθυμητούς παραλήπτες
κάνοντας κλικ με το ποντίκι σας στο όνομά του και κρατώντας πατημένο το πλήκτρο <b>CTRL (Control)</b>
</p>";

$langHUsage = "Στατιστικά Χρήσης";
$langUsageContent =
"
<p>Το υποσύστημα αυτό παρέχει τη δυνατότητα στον καθηγητή να δει στατιστικά που αφορούν το μάθημα του. Τα στατιστικά αυτά παρουσιάζονται υπό τη μορφή γραφικών παραστάσεων ή λίστας.</p>
<p><strong>Κατηγορίες στατιστικών</strong></p>
<ul>
<li>Στατιστικά χρήσης</li>
<li>Προτίμηση υποσυστημάτων</li>
<li>Επισκέψεις χρηστών στο μάθημα</li>
<li>Εμφάνιση παλιών στατιστικών</li>
</ul>
<p>Τα στατιστικά χρήσης μπορούν να ομαδοποιηθούν κατά αριθμό επισκέψεων ή κατά τη χρονική διάρκεια των επισκέψεων. Επιπλέον μπορεί να επιλεγεί για ποια υποσυστήματα χρειάζονται στατιστικά και η χρονική διάρκεια τους.</p>
<p>Τα στατιστικά προτίμησης υποσυστημάτων  μπορούν να ομαδοποιηθούν κατά αριθμό επισκέψεων ή κατά τη χρονική διάρκεια των επισκέψεων. Επιπλέον μπορεί να επιλεγεί για ποιους χρήστες χρειάζονται στατιστικά.</p>
<p>Τα στατιστικά επισκέψεων χρηστών στο μάθημα  μπορούν να ομαδοποιηθούν βάσει των χρηστών για ποιους χρήστες χρειάζονται στατιστικά.</p>
<p>Η εμφάνιση παλιών στατιστικών μπορεί να ομαδοποιηθεί κατά αριθμό επισκέψεων ή κατά τη χρονική διάρκεια των επισκέψεων. Επιπλέον μπορεί να επιλεγεί για ποια υποσυστήματα χρειάζονται στατιστικά και η χρονική διάρκεια τους.</p>
";

//Help Create Course Wizard
$langHCreateCourse = "Οδηγός δημιουργίας μαθημάτων";
$langCreateCourseContent = "<p>Ο οδηγός δημιουργίας μαθημάτων αποτελεί ένα πολύ σημαντικό εργαλείο της πλατφόρμας, αφού μέσω αυτού ο χρήστης-καθηγητής μπορεί να δημιουργεί νέα μαθήματα.</p><p>Ο οδηγός αποτελείται από 3 βήματα. Η συμπλήρωση απαιτούμενων πληροφοριών στα πεδία με αστερίσκο είναι απαραίτητη. Κάτω από κάθε πεδίο βρίσκεται μια ενδεικτική τιμή για να βοηθήσει τον χρήστη στη συμπλήρωσή τους.</p><p>Σε περίπτωση συμπλήρωσης ενός πεδίου με λανθασμένη τιμή, το σύστημα ενημερώνει τον χρήστη για το λάθος του και τον προτρέπει να το διορθώσει προκειμένου να μπορεί να μεταφερθεί στο επόμενο βήμα.</p>";

// Wiki Help

$langHWiki = "Βοήθεια - Wiki";
$langWikiContent = "<h3>Βοήθεια διαχείρισης Wiki</h3>
<dl class=\"Βοήθεια wiki\">
<dt>Πώς να δημιουργήσετε ένα νέο Wiki ?</dt>
<dd>Κάντε κλίκ στο σύνδεσμο 'Δημιουργήστε ένα νέο Wiki'. Μετά εισαγάγετε τις ιδιότητες του Wiki:
<ul>
<li><b>Τίτλος του Wiki</b> : επιλέξτε έναν τίτλο για το Wiki</li>
<li><b>Περιγραφή του Wiki</b> : επιλέξτε μια περιγραφή για το Wiki</li>
<li><b>Διαχείριση ελέγχου πρόσβασης</b> : θέστε τον έλεγχο πρόσβασης για τον Wiki επιλέγοντας/αποεπιλέγοντας το κουτί (δείτε πιο κάτω)</li>
</ul>
</dd>
<dt> Πώς να εισαγάγετε το Wiki ?</dt>
<dd> Κάντε κλικ στον τίτλο του Wiki στον κατάλογο.</dd>
<dt> Πώς να αλλάξετε τις ιδιότητες του Wiki ?</dt>
<dd>Κάντε κλικ στο εικονίδιο 'Properties' στην λίστα του Wiki και πήγαινε στη φόρμα ιδιοτήτων του Wiki.</dd>
<dt> Πώς να χρησιμοποιήσει τις διοικητικές επιλογές ελέγχου πρόσβασης;</dt>
<dd> Μπορείτε να θέσετε τα δικαιώματα πρόσβασης για τους χρήστες με τον επιλογή/αποεπιλογή του κουτιού στο \"διοικητικό\" τμήμα ελέγχου πρόσβασης των ιδιοτήτων Wiki.
 Μπορείτε να χορηγήσετε/μη χορηγήσετε πρόσβαση σε τρεις τύπους χρηστών:<ul>
<li><b> Μέλη μαθημάτων </b> : οι χρήστες εγγράφονται στη σειρά μαθημάτων (εκτός από τους διευθυντές μαθημάτων)</li>
<li><b> Μέλη ομάδας </b> (μόνο διαθέσιμο μεσα σε  μια ομάδα) : χρήστες που είναι μέλη της ομάδας (αναμείνετε τους δασκάλους ομάδας s)</li>
<li><b>Αλλοι χρήστες </b> : ανώνυμοι χρήστες ή χρήστες που δεν είναι μέλη σειράς μαθημάτων </li></ul>
Για κάθε τύπο χρηστών, μπορείτε να χορηγήσετε τον τύπο τρίων προνομίων για το Wiki(*) :<ul>
<li><b> Διαβάστε τις σελίδες </b> : ο χρήστης του δεδομένου τύπου μπορεί να διαβάσει τις σελίδες του Wiki</li>
<li><b>Αλλαγή σελίδων</b> : ο χρήστης του δεδομένου τύπου μπορεί να τροποποιήσει το περιεχόμενο των σελίδων του Wiki</li>
<li><b> Δημιουργήστε τις σελίδες </b> : ο χρήστης του δεδομένου τύπου μπορεί να δημιουργήσει νέες σελίδες του Wiki</li>
</ul><small><em>(*) Σημειώστε ότι εάν ένας χρήστης δεν μπορεί να διαβάσει τις σελίδες του  Wiki, δεν μπορεί να τις αλλάξει ή να τις τροποποιήσει. Σημειώστε ότι εάν ένας χρήστης δεν μπορεί να αλλαξει τις σελίδες του Wiki, δεν μπορεί να δημιουργήσει νέες σελίδες.</em></small></dd>
<dt> Πώς να διαγράψει το Wiki ?</dt>
<dd>Κάντε κλικ στο εικονίδιο 'Delete' στη στήλη για να σβήσετε το Wiki και όλες του τις σελίδες.</dd>
<dt> Πώς να πάρετε τον κατάλογο των σελίδων σε ένα Wiki ;</dt>
<dd>Κάντε κλικ στον αριθμό των σελίδως σε αυτό το Wiki στην λίστα των Wiki.</dd>
<dt> Πώς να πάρετε τον κατάλογο των  τελευταίων τροποποιημένων σελίδων σε ένα Wiki;</dt>
<dd>Κάντε κλικ στο εικονίδιο 'Recent changes' στη στήλη του καταλόγου του Wiki.</dd>
</dl>";

$langHWikiSyntax = "Βοήθεια - Σύνταξη Wiki";
$langWikiSyntaxContent = "<h1>Σύνταξη Wiki </h1>
<h2>1. Βασική σύνταξη </h2>
<dl class=\"Βοήθεια wiki\">
<dt> Δημιουργία των σελίδων και των συνδέσεων wiki μεταξύ τους </dt>
<dd><strong>Λέξεις Wiki </strong> : Οι λέξεις Wiki είναι λέξεις που γράφονται όπως <em>ΛέξηWiki</em>. Τα Wiki2xhtml τους αναγνωρίζουν ατόματα ως συνδέσεις σελίδων Wiki. Για να δημιουργήσετε μια σελίδα wiki ή για να δημιουργήσετε μια σύνδεση με μια σελίδα wiki, τροποποιήστε μια ήδη υπάρχουσα και προσθέστε το τίτλο στην σύνταξη του wiki, για παράδειγμα <em>ΝεαΣελιδα</em> (χωρίς τόνους ή με αγγλικούς χαρακτήρες), και μετά φύλαξε τη σελίδα. Wiki2xhtml θα αντικαταστήσει αυτόματα τη λέξη <em>ΝεαΣελιδα</em> με μια σύνδεση με τη σελίδα Wiki <em>ΝεαΣελιδα</em>&nbsp;</dd>
<dd><strong> συνδέσεις  Wiki </strong> : Οι συνδέσεις Wiki είναι όπως τους συνδέσμους υπερ-κειμένου (βλ. κατωτέρω) αναμένουν ότι δεν περιέχουν οποιοδήποτε σχέδιο πρωτοκόλλου (όπως <em>http://</em> ή <em>ftp://</em>) και ότι αυτόματα αναγνωρίζουν συνδέσμους σε σελίδες  Wiki. Για να δημιουργήσετε μια νέα σελίδα ή να δημιουργήσετε μια σύνδεση με μια υπάρχουσα που χρησιμοποιεί τις συνδέσεις Wiki, αλλαξτε μια σελίδα και προσθέστε <code>[page title]</code> η <code>[name of link|title of page]</code> στα περιεχόμενα του. Μπορείτε επίσης να χρησιμοποιήσετε αυτήν την σύνταξη για να αλλάξετε το κείμενο μιας σύνδεσης WikiWord: <code>[όνομα συνδέσμου|WikiWord]</code>.</dd>
<dt> Σύνδεσμοι υπερ-κειμένου </dt>
<dd><code>[url]</code>, <code>[name|url]</code>, <code>[name|url|language]</code> or <code>[name|url|language|title]</code>.&nbsp;</dd>
<dt> Συνυπολογισμός εικόνας </dt>
<dd><code>((url|alternate text))</code>, <code>((url| εναλλάσσομενο κείμενο |position))</code> ou <code>((url|alternate text|position|long description))</code>. <br /> Το επιχείρημα θέσης μπορεί να πάρει τις ακόλουθες τιμές : L (αριστερά), R (δεξιά) or C (κεντρικά).&nbsp;</dd>
<dd> Μπορείτε να χρησιμοποιήσετε τη σύνταξη ως συνδέσμους υπερ-κειμένου. Παραδείγματος χάριν <code>[τίτλος|image.gif]</code>. Αυτή η σύνταξη είναι αποδοκιμασμένη, σκεφτήτε να χρησιμποιήσετε την προηγούμενη&nbsp;</dd>
<dt> Σύνδεση με μια εικόνα </dt>
<dd> όπως τους συνδέσμους υπερ-κειμένου αλλά τεθειμένο 0 στο τέταρτο επιχείρημα για να αποφευχθεί η αναγνώριση εικόνας και να φταθεί ένας σύνδεσμος υπερ-κειμένου σε μια εικόνα. Παραδείγματος χάριν <code>[image|image.gif||0]</code> θα επιδείξει μια σύνδεση με την image.gif iαντι για επίδειξη της ίδιας της φωτογραφίας</dd>
<dt> Σχεδιάγραμμα </dt>
<dd><strong> Κυρτός </strong> : περιβάλτε το κείμενό σας με δύο ενιαία αποσπάσματα <code>'' κείμενο ''</code>&nbsp;</dd>
<dd><strong>Εντονα</strong> : περιβάλτε το κείμενό σας με τρία ενιαία αποσπάσματα υπογραμμίζει <code>''' κείμενο '''</code>&nbsp;</dd>
<dd><strong>Υπογράμμιση</strong> : περιβάλτε το κείμενό σας με δύο υπογραμμίζει <code>__ κείμενο __</code>&nbsp;</dd>
<dd><strong> Γραμμή</strong> : περιβάλτε το κείμενό σας με δύο αρνητικά σύμβολα <code>-- κείμενο --</code>&nbsp;</dd>
<dd><strong> Τίτλος </strong> : <code>!!!</code>, <code>!!</code>, <code>!</code> αντίστοιχα για τους τίτλους, τους υποτίτλους και τους υπο-υπο-τίτλους &nbsp;</dd>
<dt> Κατάλογος </dt>
<dd> γραμμή αρχίζοντας από <code>*</code> (άδιάτακτος κατάλογος) ή <code>#</code> (διαταγμένος κατάλογος). Μπορείτε να αναμίξετε τους καταλόγους (<code>*#*</code>) για να δημιουργήθούν πολυ - κατάλογοι επιπέδων.&nbsp;</dd>
<dt> Παράγραφος </dt>
<dd> Χωριστές παράγραφοι με μια ή περισσότερες νέες γραμμές &nbsp;</dd>
</dl>
<h2>2. Προχωρημένη σύνταξη </h2>
<dl class=\"Βοήθεια wiki\">
<dt> Υποσημείωση </dt>
<dd><code>\$\$ κείμενο υποσημειώσεων \$\$</code>&nbsp;</dd>
<dt>προκαθοριμένο κείμενο </dt>
<dd> αρχίστε κάθε γραμμή του κείμενο με ένα κενό διάστημα &nbsp;</dd>
<dt> Αναφέρετε φραγμού </dt>
<dd><code>&gt;</code> ή <code>;:</code> πριν από κάθε γραμμή &nbsp;</dd>
<dt> Οριζόντια γραμμή </dt>
<dd><code>----</code>&nbsp;</dd>
<dt> Αναγκασμένο σπάσιμο γραμμών </dt>
<dd><code>%%%</code>&nbsp;</dd>
<dt>ακρώνυμο</dt>
<dd><code>??ακρώνυμο??</code> or <code>??ακρώνυμο|ορισμός??</code>&nbsp;</dd>
<dt>Ευθυγραμμισμένη αναφορά </dt>
<dd><code>{{αναφορα}}</code>, <code>{{αναφορά|γλώσσα}}</code> or <code>{{αναφορά|γλώσσα|url}}</code>&nbsp;</dd>
<dt>Κώδικας</dt>
<dd><code>@@Ο κωδικας σου εδώ@@</code>&nbsp;</dd>
<dt>Ονομα στηρίγματος</dt>
<dd><code>~στήριγμα~</code>&nbsp;</dd>
</dl>";


/************************************************************
* import.inc.php
************************************************************/

$langAddPage="Προσθήκη μιας σελίδας";
$langPageAdded="Η σελίδα προστέθηκε";
$langPageTitleModified="Ο τίτλος της σελίδας άλλαξε";
$langSendPage="Όνομα αρχείου της σελίδας";
$langCouldNotSendPage="Το αρχείο δεν είναι σε μορφή HTML και δεν ήταν δυνατόν να σταλεί. Αν θέλετε να στείλετε αρχεία που
δεν είναι σε μορφή HTML (π.χ. PDF, Word, Power Point, Video, κ.λπ.)
χρησιμοποιήστε τα <a href=../document/document.php>Έγγραφα</a>";
$langAddPageToSite="Προσθήκη μιας σελίδας σε ένα site";
$langCouldNot="Το αρχείο δεν ήταν δυνατόν να σταλεί";
$langOkSent="<p><b>Η σελίδα σας στάλθηκε</b><br/><br/>Δημιουργήθηκε σύνδεσμος προς αυτήν στο αριστερό μενού</p>";
$langTooBig="Δεν διαλέξατε κάποιο αρχείο για να στείλετε,ή είναι πολύ μεγάλο";
$langExplanation="Η σελίδα πρέπει να είναι σε μορφή HTML (π.χ. \"my_page.htm\"). Θα δημιουργηθεί σύνδεσμος στην αρχική
σελίδα προς αυτήν. Αν θέλετε να στείλετε αρχεία που δεν είναι σε μορφή HTML (π.χ. PDF, Word, Power Point, Video, κ.λπ.)
χρησιμοποιήστε τα <a href=../document/document.php>Έγγραφα</a>";
$langPgTitle="Τίτλος σελίδας";
$langLinks = "Σελίδα HTML";

/***************************************************************
* index.inc.php
***************************************************************/

$langInvalidId = '<font color="red" size="1" face="arial, helvetica">
        Λάθος στοιχεία.<br>Αν δεν είστε γραμμένος, συμπληρώστε τη
        <a href=modules/auth/newuser_info.php>φόρμα εγγραφής</a>.
        </font><br>&nbsp;<br>';
$langAccountInactive1 = "Μη ενεργός λογαριασμός.";
$langAccountInactive2 = "Παρακαλώ επικοινωνήστε με τον διαχειριστή για την ενεργοποίηση του λογαριασμού σας";
$langMyCoursesProf="Τα μαθήματα που υποστηρίζω (Καθηγητής)";
$langMyCoursesUser="Τα μαθήματα που παρακολουθώ (Εγγεγραμμένος)";
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
$langUserName="Όνομα χρήστη (username)";
$langPass="Συνθηματικό (password)";
$langHelp="Βοήθεια";
$langSelection="Επιλογή";
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
$langContact = 'Επικοινωνία';
$langInfoPlat = 'Ταυτότητα Πλατφόρμας';
$lang_forgot_pass = "Ξεχάσατε το συνθηματικό σας;";
$langNewAnnounce = "Νέα !";
$langUnregUser = "Διαγραφή λογαριασμού";
$langListFaculte = "Κατάλογος Μαθημάτων";
$langAsynchronous = "Ομάδα Ασύγχρονης Τηλεκπαίδευσης";
$langUserLogin = "Σύνδεση χρήστη";
$langWelcomeToEclass = "Καλωσορίσατε στο eClass!";
$langPlatformAnnounce = "Ανακοινώσεις";
$langUnregCourse = "Απεγγραφή από μάθημα";
$langUnCourse = "Απεγγραφή";
$langCourseCode = "Μάθημα (Κωδικός)";
$langInfoAbout = "Η πλατφόρμα <strong>GUnet eClass</strong> αποτελεί ένα ολοκληρωμένο Σύστημα Διαχείρισης Ηλεκτρονικών Μαθημάτων. Έχει σχεδιαστεί με προσανατολισμό την ενίσχυση της συμβατικής διδασκαλίας αξιοποιώντας την ήδη σε υψηλό βαθμό αφομοιωμένη στο χώρο της εκπαίδευσης πληροφορική τεχνολογία. Ακολουθεί τη φιλοσοφία του λογισμικού ανοικτού κώδικα και υποστηρίζει την υπηρεσία Ασύγχρονης Τηλεκπαίδευσης χωρίς περιορισμούς και δεσμεύσεις. Η πρόσβαση στην υπηρεσία γίνεται με τη χρήση ενός απλού φυλλομετρητή (web browser) χωρίς την απαίτηση εξειδικευμένων τεχνικών γνώσεων.<br><br>
Στόχος είναι η ενίσχυση της εκπαιδευτικής διαδικασίας, προσφέροντας στους συμμετέχοντες ένα δυναμικό περιβάλλον αλληλεπίδρασης και συνεχούς επικοινωνίας εκπαιδευτή εκπαιδευόμενου. Ειδικότερα, επιτρέπει στον εκπαιδευτή την ηλεκτρονική οργάνωση, αποθήκευση και παρουσίαση του εκπαιδευτικού υλικού και παρέχει στον εκπαιδευόμενο ένα εναλλακτικό κανάλι εξατομικευμένης μάθησης ανεξάρτητο από χωροχρονικές δεσμεύσεις.";

/*$langWelcomeStud = "<br>Καλωσήλθατε στο περιβάλλον της πλατφόρμας <b>$siteName</b>.<br><br>
                    Επιλέξτε \"Εγγραφή σε μάθημα\" για να παρακολουθήσετε τα διαθέσιμα ηλεκτρονικά μαθήματα.";
$langWelcomeProf = "<br>Καλωσήλθατε στο περιβάλλον της πλατφόρμας <b>$siteName</b>.<br><br>
                    Επιλέξτε \"Δημιουργία Μαθήματος\" για να δημιουργήσετε τα ηλεκτρονικά σας μαθήματα.";
*/
$langWelcomeStud = "Επιλέξτε \"Εγγραφή σε μάθημα\" για να παρακολουθήσετε τα διαθέσιμα ηλεκτρονικά μαθήματα.";
$langWelcomeProf = "Επιλέξτε \"Δημιουργία Μαθήματος\" για να δημιουργήσετε τα ηλεκτρονικά σας μαθήματα.";


/***********************************************************
* install.inc.php
***********************************************************/

$langEG 			= "π. χ.";
$langDBHost			= "Όνομα υπολογιστή της Βάσης Δεδομένων";
$langDBLogin		= "Όνομα Χρήστη για τη Βάση Δεδομένων";
$langDBPassword 	= "Συνθηματικό για τη Βάση Δεδομένων";
$langMainDB			= "Κύρια Βάση Δεδομένων του e-Class";
$langAllFieldsRequired	= "όλα τα πεδία είναι υποχρεωτικά";
$langPrintVers			= "Εκτυπώσιμη μορφή";
$langLocalPath			= "Path των αρχείων του e-Class στον εξυπηρετητή";
$langAdminEmail			= "Email Διαχειριστή";
$langAdminName			= "Όνομα Διαχειριστή";
$langAdminSurname		= "Επώνυμο Διαχειριστή";
$langAdminLogin			= "Όνομα Χρήστη του Διαχειριστή";
$langAdminPass			= "Συνθηματικό του Διαχειριστή";
$langHelpDeskPhone		= "Τηλέφωνο Helpdesk";
$langHelpDeskFax		= "Αριθμός Fax Helpdesk";
$langHelpDeskEmail		= "Email Helpdesk";
$langCampusName			= "Όνομα Πλατφόρμας";
$langInstituteShortName         = "Όνομα Ιδρύματος - Οργανισμού";
$langInstituteName		= "Website Ιδρύματος - Οργανισμού";
$langInstitutePostAddress       = "Ταχ. Διεύθυνση Ιδρύματος - Οργανισμού";

$langWarnHelpDesk		= "Προσοχή: στο \"Email helpdesk\" στέλνονται οι αιτήσεις καθηγητών για λογαριασμό στην πλατφόρμα";


$langDBSettingIntro		= "Το πρόγραμμα εγκατάστασης θα δημιουργήσει την κύρια βάση δεδομένων του e-Class.
				Έχετε υπ'όψιν σας ότι κατά τη λειτουργία της πλατφόρμας θα χρειαστεί να
				δημιουργηθούν νέες βάσεις δεδομένων (μία για κάθε μάθημα) ";


$langStep1 			= "Βήμα 1 από 6";
$langStep2 			= "Βήμα 2 από 6";
$langStep3 			= "Βήμα 3 από 6";
$langStep4 			= "Βήμα 4 από 6";
$langStep5 			= "Βήμα 5 από 6";
$langStep6 			= "Βήμα 6 από 6";
$langCfgSetting		= "Ρυθμίσεις Συστήματος";
$langDBSetting 		= "Ρυθμίσεις της MySQL";
$langMainLang 		= "Κύρια Γλώσσα Εγκατάστασης";
$langLicence		= "Άδεια Χρήσης";
$langLastCheck		= "Τελευταίος έλεγχος πριν την εγκατάσταση";
$langRequirements	= "Απαιτήσεις Συστήματος";
$langInstallEnd   	= "Ολοκλήρωση Εγκατάστασης";


/********************************************************
* learnpath.inc.php
*********************************************************/

$langAddComment = "Προσθήκη / αλλαγή σχολίου στο";
$langAddModule = "Προσθήκη";
$langAddModulesButton = "Προσθήκη επιλεγμένων";
$langAddOneModuleButton = "Προσθήκη ενότητας";
$langAlertBlockingMakedInvisible = "Αυτή η ενότητα είναι φραγμένη. Κάνοντας τη αόρατη, θα επιτραπεί στους εκπαιδευόμενους η είσοδος στην επόμενη ενότητα χωρίς να χρειάζεται να ολοκληρώσουν την παρούσα. Επιβεβαιώστε την επιλογή σας";
$langAlertBlockingPathMadeInvisible = "Αυτή η γραμμή ειναι φραγμένη. Κάνοντας την μη ορατή θα επιτραπεί στους εκπαιδευόμενους η είσοδος στην επόμενη γραμμή χωρίς να χρειάζεται να ολοκληρώσουν την παρούσα. Επιβεβαιώστε την επιλογή σας";
$langAlreadyBrowsed = "Ολοκληρώθηκε";
$langAltClarodoc = "Clarodoc";
$langAltDocument = "Έγγραφο";
$langAltExercise = "Άσκηση";
$langAltMakeNotBlocking = "Αποδέσμευση";
$langAltMakeVisible = "Κάντε το ορατό";
$langAltMove = "Μετακίνηση";
$langAltMoveUp = "Κίνηση προς τα πάνω";
$langAltScorm = "Scorm";
$langAreYouSureDeleteModule = " Είστε βέβαιοι για την συνολική διαγραφή της ενότητας; Θα διαγραφεί εντελώς από τον κεντρικό υπολογιστή και από κάθε γραμμή. Δεν θα είστε σε θέση να τη χρησιμοποιήσετε. Επιβεβαιώστε τη διαγραφή:  ";
$langAreYouSureToDeleteScorm = "H γραμμή μάθησης αποτελεί μέρος ενός πακέτου SCORM. Αν διαγράψετε αυτή τη γραμμή, όλες οι ενότητες που συμβαδίζουν με το SCORM και όλα τα σχετικά αρχεία θα διαγραφούν απο την πλατφόρμα. Σίγουρα θέλετε να διαγράψετε τη γραμμή μάθησης ";
$langAreYouSureToRemove = "Σίγουρα θέλετε να απομακρύνετε/αφαιρέσετε την παρακάτω ενότητα απο τη γραμμή μάθησης: ";
$langAreYouSureToRemoveLabel = "Διαγράφοντας μία ετικέτα θα διαγραφούν και όλες οι ενότητες ή οι ετικέτες που περιέχει.";
$langAreYouSureToRemoveSCORM = "Ενότητες σύμφωνες με το SCORM θα αφαιρεθούν οριστικά απο το server, όταν διαγράψετε τη γραμμή μάθησης.";
$langAreYouSureToRemoveStd = "Η ενότητα θα παραμείνει διαθέσιμη στην ομάδα των ενοτήτων.";
$langBackModule = "Επιστροφή στη λίστα";
$langBackToLPAdmin = "Επιστροφή στη διαχείριση της γραμμής μάθησης";
$langBlock = "Φραγή";
$langBrowserCannotSeeFrames = "Ο browser σας δεν αναγνωρίζει frames.";
$langChangeRaw = "Αλλαγή του ελάχιστου αρχικό σημείο για να περάσει αυτή η ενότητα (ποσοστό): ";
$langChat = "Κουβεντούλα";
$langConfirmYourChoice = "Παρακαλώ επιβεβαιώστε την επιλογή σας";
$langCourseDescription = "Περιγραφή Μαθήματος";
$langCourseDescriptionAsModule = "Χρήση Περιγραφής Μαθήματος";
$langCourseHome = "Αρχική σελίδα μαθήματος";
$langCreateLabel = "Δημιουργία ετικέτας";
$langCreateNewLearningPath = "Δημιουργία νέας γραμμής μάθησης";
$langDOCUMENTTypeDesc = "Έγγραφο";
$langDefaultLearningPathComment = "Αυτό είναι το εισαγωγικό κείμενο αυτής της γραμμής μάθησης. Για να το αντικαταστήσετε με δικό σας κείμενο, καντε κλικ παρακάτω στη <b>μετατροπή</b>.";
$langDefaultModuleAddedComment = "Αυτό είναι πρόσθετο εισαγωγικό κείμενο σχετικά με την παρουσία αυτής της ενότητας ειδικά σε αυτή τη γραμμή μάθησης. Για να το αντικαταστήσετε με δικό σας κείμενο, κάντε κλικ παρακάτω στο <b>μετατροπή</b>.";
$langDefaultModuleComment = "Αυτό είναι το εισαγωγικό κείμενο αυτής της ενότητας, θα εμφανίζεται σε κάθε γραμμή μάθησης που θα περιέχει αυτή την ενότητα. Για να το αντικαταστήσετε με δικό σας κείμενο, κάντε κλικ παρακάτω στο <b>μετατροπή</b>.";
$langDescriptionCours = "Περιγραφή μαθήματος";
$langDocInsertedAsModule = "έχει προστεθεί σαν ενότητα";
$langDocumentAlreadyUsed = "Αυτό το έγγραφο έχει ήδη χρησιμοποιηθεί σαν ενότητα σε αυτή τη γραμμή μάθησης";
$langDocumentAsModule = "Χρήση Εγγράφου";
$langDocumentInModule = "Έγγραφο σε ενότητα";
$langEXERCISETypeDesc = "Άσκηση Eclass";
$langEndOfSteps = "Κάντε κλίκ στη λήξη αφού ολοκληρώσετε αυτό το τελευταίο βήμα.";
$langErrorAssetNotFound = "Το στοιχείο δεν ευρέθη : ";
$langErrorCopyAttachedFile = "Μη δυνατή η αντιγραφή αρχείου: ";
$langErrorCopyScormFiles = "Σφάλμα κατά την αντιγραφή των αναγκαίων αρχείων SCORM ";
$langErrorCopyingScorm = "Σφάλμα αντιγραφής υπάρχων περιεχομένου SCORM";
$langErrorCreatingDirectory = "Μη δυνατή η δημιουργία κατάλογου: ";
$langErrorCreatingFile = "Μη δυνατή η δημιουργία αρχείου: ";
$langErrorCreatingFrame = "Μη δυνατή η δημιουργια τα πλαίσια του αρχείου ";
$langErrorCreatingManifest = "Μη δυνατή η  δημιουργία της προκήρυξης SCORM (imsmanifest.xml)";
$langErrorCreatingScormArchive = "Μη δυνατή η δημιουργια του καταλόγου αρχείων SCORM ";
$langErrorEmptyName = "Το όνομα πρέπει να συμπληρωθεί";
$langErrorFileMustBeZip = "Το αρχείο πρέπει να είναι σε μορφή αρχείου zip (.zip)";
$langErrorInvalidParms = "Σφάλμα: μη έγγυρη παράμετρος (χρησιμοποιήστε μόνο αριθμούς)";
$langErrorLoadingExercise = "Μη δυνατή η φόρτωση της άσκησης ";
$langErrorLoadingQuestion = "Μη δυνατή η φόρτωση της ερώτησης της άσκησης ";
$langErrorNameAlreadyExists = "Σφάλμα: Το όνομα υπάρχει ήδη στη γραμμή μάθησης ή στο σύνολο των ενοτήτων ";
$langErrorNoModuleInPackage = "Δεν υπάρχει ενότητα στο πακέτο";
$langErrorNoZlibExtension = "Η επέκταση Zlib php απαιτείται για τη χρήση αυτού του εργαλείου.  Παρακαλώ επικοινωνήστε με τον διαχειριστή της πλατφόρμας σας.";
$langErrorOpeningManifest = "Δεν μπορεί να βρεθεί το αρχείο <i>manifest</i> στο πακέτο.<br /> Αρχείο που δε βρέθηκε: imsmanifest.xml";
$langErrorOpeningXMLFile = "Δεν μπορει να βρεθεί δευτερεύον αρχείο έναρξης στο πακέτο.<br /> Αρχείο που δε βρέθηκε: ";
$langErrorReadingManifest = "Σφαλμα ανάγνωσης αρχείου <i>manifest</i>";
$langErrorReadingXMLFile = "Σφάλμα ανάγνωσης δευτερεύοντος αρχείου ρύθμισης έναρξης: ";
$langErrorReadingZipFile = "Σφάλμα ανάγνωσης αρχειου zip.";
$langErrorSql = "Σφάλμα στη δήλωση SQL";
$langErrorValuesInDouble = "Σφάλμα: μία ή δυο τιμές είναι διπλές";
$langErrortExtractingManifest = "Δεν μπορεί να εμφανιστεί απόσπασμα απο το αρχείο zip.";
$langExAlreadyUsed = "Αυτή η άσκηση ήδη χρησιμοποιείται σαν ενότητα σε αυτή τη γραμμή μάθησης";
$langExInsertedAsModule = "έχει προστεθεί σαν ενότητα μαθήματος αυτής της γραμμής μάθησης";
$langExercise = "Ασκήσεις";
$langExerciseAsModule = "Χρήση Άσκησης";
$langExerciseCancelled = "Ακύρωση άσκησης, επιλέξτε την επόμενη ενότητα για να συνεχίσετε, κάνοντας κλίκ στο επόμενο βήμα.";
$langExerciseDone = "Ολοκλήρωση άσκησης, επιλέξτε την επόμενη ενότητα για να συνεχίσετε, κάνοντας κλίκ στο επόμενο βήμα.";
$langExerciseInModule = "Ασκηση στην ενότητα";
$langExercises = "Ασκήσεις";
$langExport = "Εξαγωγή";
$langExport2004 = "Εξαγωγή σε πρότυπο SCORM 2004";
$langExport12 = "Εξαγωγή σε πρότυπο SCORM 1.2";
$langFailed = "Ολοκληρώθηκε ανεπιτυχώς";
$langFileError = "Το αρχείο που θα ενημερωθεί δεν είναι έγκυρο.";
$langFileName = "Όνομα αρχείου";
$langFirstName = "Όνομα";
$langFullScreen = "Μεγάλη/γεμάτη οθόνη ";
$langGlobalProgress = "Πρόοδος της γραμμής μάθησης: ";
$langGroups = "Ομάδες Εκπαιδευόμενων";
$langImport = "Εισαγωγή";
$langInFrames = "Σε πλαίσια";
$langInfoProgNameTitle = "Πληροφορία";
$langInsertMyDescToolName = "Εισαγωγή περιγραφής μαθήματος";
$langInsertMyDocToolName = "Εισαγωγή εγγράφου";
$langInsertMyExerciseToolName = "Εισαγωγή άσκησης";
$langInsertMyLinkToolName = "Εισαγωγή Συνδέσμου";
$langInsertMyModuleToolName = "Εισαγωγή ενότητας";
$langInsertMyModulesTitle = "Εισαγωγή ενότητας μαθήματος";
$langInsertNewModuleName = "Εισαγωγή νέου ονόματος";
$langInstalled = "Η γραμμή μάθησης έχει εισαχθεί με επιτυχία.";
$langIntroLearningPath = "Χρησιμοποήστε αυτό το εργαλείο για να παρέχετε στους μαθητές σας μια γραμμή μεταξύ εγγράφων, ασκήσεων,σελίδες HTML, συνδέσεις,...<br /><br />Εάν επιθυμείτε να παρουσιάσετε στους μαθητές τη γραμμή μάθησης σας, κάντε κλικ παρακάτω.<br />";
$langLINKTypeDesc = "Σύνδεσμος";
$langLastName = "Επίθετο";
$langLastSessionTimeSpent = "Τελευταία χρονική συνεδρίαση";
$langLearningPath = "Γραμμή μάθησης";
$langLearningPathAdmin = "Διαχείριση γραμμής μάθησης";
$langLearningPathEmpty = "Η γραμμή μάθησης είναι κενή ";
$langLearningPathList = "Λίστα γραμμών μάθησης";
$langLearningPathName = "Νέο όνομα γραμμής μάθησης : ";
$langLearningPathNotFound = "Η γραμμή μάθησης δεν βρέθηκε ";
$langLessonStatus = "Κατάσταση ενότητας";
$langLinkAlreadyUsed = "Αυτός ο σύνδεσμος ήδη χρησιμοποιείται σαν ενότητα σε αυτήν τη γραμμή μάθησης";
$langLinkAsModule = "Χρήση Συνδέσμου";
$langLinkInsertedAsModule = "έχει προστεθεί σαν ενότητα μαθήματος αυτής της γραμμής μάθησης";
$langLogin = "Είσοδος";
$langMakeInvisible = "Μετατροπή σε αόρατο";
$langMaxFileSize = "Μέγιστο μέγεθος αρχείου: ";
$langMinuteShort = "ελαχ.";
$langModule = "Ενότητα";
$langModuleMoved = "Μετακίνηση ενότητας";
$langModuleOfMyCourse = "Χρήση ενότητας αυτού του μαθήματος";
$langModuleStillInPool = "Ενότητες αυτής της γραμμής θα είναι ακόμα διαθέσιμες στο σύνολο των ενοτήτων";
$langModules = "Ενότητες";
$langModulesPoolToolName = "Σύνολο ενοτήτων";
$langMyCourses = "Τα μαθήματά μου";
$langNameOfLang = "Διάταξη";
$langNeverBrowsed = "Δεν έχει ολοκληρωθεί";
$langNewLabel = "Δημιουργία νέας ετικέτας";
$langNext = "Επόμενο";
$langNextPage = "Επόμενη Σελίδα";
$langNoEmail = "Δεν έχει οριστεί email";
$langNoLearningPath = "Καμία γραμμή μάθησης";
$langNoModule = "Καμία ενότητα";
$langNoMoreModuleToAdd = "Όλες οι ενότητες αυτού του μαθήματος ήδη χρησιμοποιήθηκαν σε αυτή τη γραμμή μάθησης.";
$langNoSpace = "Το ανέβασμα του αρχείου απέτυχε. Δεν υπάρχει αρκετός χώρος στον κατάλογο σας";
$langNoStartAsset = "Δεν υπάρχει κανένα απόκτημα/στοιχείο έναρξης που να ορίζεται για αυτή την ενότητα.";
$langNotAttempted = "Δεν έχει επιχειρηθεί";
$langNotInstalled = "Προέκυψε σφάλμα.  Η εισαγωγή της γραμμής μάθησης απέτυχε.";
$langOkChapterHeadAdded = "Ο τίτλος προστέθηκε: ";
$langOkDefaultCommentUsed = "προειδοποίηση: Η εγκατάσταση δε μπορεί να βρεί την περιγραφή της γραμμής μάθησης και έχει χρησιμοποιήσει ένα προκαθορισμένο σχόλιο.  Θα πρέπει να το αλλάξετε";
$langOkDefaultTitleUsed = "προειδοποίηση : Η εγκατάσταση δε μπορεί να βρεί το όνομα της γραμμής μάθησης και έχει ορίσει καποιο προκαθορισμένο όνομα .  Θα πρέπει να το αλλάξετε.";
$langOkFileReceived = "Το αρχείο ελήφθη: ";
$langOkManifestFound = "Η ανακοίνωση βρέθηκε σε αρχείο zip: ";
$langOkManifestRead = "H ανακοίνωση διαβάστηκε.";
$langOkModuleAdded = "Προσθήκη ενότητας: ";
$langOrder = "Εντολή ";
$langOtherCourses = "Λίστα Μαθημάτων";
$langPassed = "Ολοκληρώθηκε επιτυχώς";
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
$langRoot = "Αρχικό κατάλογο";
$langSCORMTypeDesc = "SCORM προσαρμοσμένο περιεχόμενο";
$langScormIntroTextForDummies = "Τα εισαγόμενα πακέτα πρεπει να αποτελούνται απο ένα zip αρχείο και να είναι συμβατά με το SCORM 2004 ή με το SCORM 1.2.";
$langSecondShort = "δευτ.";
$langStartModule = "Έναρξη ενότητας";
$langStatsOfLearnPath = "Παρακολούθηση γραμμής μάθησης";
$langStudent = "Εκπαιδευόμενος";
$langSwitchEditorToTextConfirm = "Η εντολή θα αφαιρέσει τη τρέχουσα διάταξη κειμένου. Θέλετε να συνεχίσετε?";
$langTextEditorDisable = "Απενεργοποίηση επεξεργαστή κειμένου";
$langTextEditorEnable = "Ενεργοποίηση επεξεργαστή κειμένου";
$langTimeInLearnPath = "Χρόνος στη γραμμή μάθησης";
$langTo = "στο";
$langTotalTimeSpent = "Σύνολο χρόνου";
$langTrackAllPath = "Παρακολούθηση γραμμών μάθησης";
$langTrackAllPathExplanation = "Πρόοδος εκπαιδευόμενων σε όλες τις διαδρομές μάθησης";
$langTrackUser = "Πρόοδος Εκπαιδευόμενου";
$langTracking = "Παρακολούθηση";
$langTypeOfModule = "Τύπος ενότητας";
$langUnamedModule = "Ενότητα χωρίς όνομα";
$langUnamedPath = "Γραμμή χωρίς όνομα";
$langUseOfPool = "Αυτή η σελίδα επιτρέπει να δείς όλες τις διαθέσιμες ενότητες σε αυτό το μάθημα. <br /> Όποια άσκηση ή έγγραφο έχει προστεθεί στη γραμμή μάθησης θα εμφανίζεται σε αυτή τη λίστα.";
$langUsedInLearningPaths = "Αριθμός διαδρομών μάθησης που χρησιμοποιούν αυτή την ενότητα : ";
$langUser = "Εκπαιδευόμενος";
$langView = "Εμφάνιση";
$langViewMode = "Παρουσίαση τρόπου";
$langVisibility = "Ορατό / Αόρατο";
$langWork = "Εργασίες Εκπαιδευόμενων";
$langWrongOperation = "Λανθασμένη λειτουργία";
$langYourBestScore = "Η καλύτερη σου βαθμολογία";
$langZipNoPhp = "Το αρχείο zip δεν πρέπει να περιέχει αρχεία .php";
$lang_enroll = "Eγγραφή";
$langimportLearningPath = "Εισαγωγή γραμμής μάθησης";


/*************************************************
* lessontools.inc.php
**************************************************/

$langActiveTools="Ενεργά εργαλεία";
$langAdministrationTools="Εργαλεία διαχείρισης";
$langAdministratorTools="Εργαλεία διαχειριστή";
$langTools="Εργαλεία μαθήματος";

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

// Category language variables
$langCategoryName="Όνομα κατηγορίας";
$langDescriptionGR="Περιγραφή";

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
// Other
$showall = "Εμφάνιση";
$shownone = "Απόκρυψη";
$langProfNoLinksExist = "Δεν υπάρχουν σύνδεσμοι. Μπορείτε να χρησιμοποιήσετε τις λειτουργίες του εργαλείου για να προσθέσετε σύνδεσμους.";
$langNoLinksExist = "Δεν έχουν προστεθεί σύνδεσμοι από τον εκπαιδευτή του μαθήματος.";


/*****************************************************************
* lostpass.inc.php
*****************************************************************/

$lang_remind_pass = 'Επανατοποθέτηση συνθηματικού';
$lang_pass_intro = '<p>Αν έχετε ξεχάσει τα στοιχεία του λογαριασμού σας, συμπληρώστε το <em>όνομα χρήστη</em>
και την διεύθυνση ηλεκτρονικού ταχυδρομείου με την οποία είστε εγγεγραμμένος 
(<em>προσοχή: αυτή που έχετε δηλώσει στην πλατφόρμα</em>).</p> <p>Στη συνέχεια θα παραλάβετε ένα μήνυμα σε αυτή τη
διεύθυνση με οδηγίες για να αλλάξετε το συνθηματικό σας.</p>';
$lang_pass_submit = 'Αποστολή';
$lang_pass_invalid_mail1 = 'H διεύθυνση ηλεκτρονικού ταχυδρομείου που δώσατε,';
$lang_pass_invalid_mail2 = 'δεν είναι έγκυρη. Αν κάνατε λάθος, δοκιμάστε ξανά.
	Αλλιώς, και αν είστε σίγουροι ότι έχετε ήδη λογαριασμό στο σύστημα,
	παρακαλούμε να επικοινωνήστε με τους διαχειριστές του συστήματος στη διεύθυνση';
$lang_pass_invalid_mail3 = 'δίνοντας και στοιχεία που μπορούν να βοηθήσουν στο να βρούμε
		το λογαριασμό σας, όπως ονοματεπώνυμο, σχολή/τμήμα, κ.λπ.';
$langPassResetIntro ="
Έχει ζητηθεί να γίνει επανατοποθέτηση του συνθηματικού πρόσβασης σας στην
πλατφόρμα τηλεκπαίδευσης $siteName. Αν δεν ζητήσατε εσείς αυτή την ενέργεια,
απλώς αγνοήστε τις οδηγίες αυτού του μηνύματος και αναφέρετέ το γεγονός αυτό
στο διαχειριστή του συστήματος, στην διεύθυνση: ";


$langHowToResetTitle = "

===============================================================================
			Οδηγίες επανατοποθέτησης συνθηματικού
===============================================================================
";

$langPassResetGoHere = "
Για να επανατοποθετήσετε το συνθηματικό σας πηγαίνετε στην πιο κάτω διεύθυνση.
Αν δεν μπορείτε να μεταβείτε κάνοντας κλικ πάνω στη διεύθυνση αυτή, αντιγράψτε
την στη μπάρα διευθύνσεων του φυλλομετρητή σας.  Η διεύθυνση αυτή έχει ισχύ
μίας (1) ώρας. Πέραν αυτού του χρονικού ορίου θα πρέπει να κάνετε από την αρχή
τη διαδικασία επανατοποθέτησης συνθηματικού.


";

$langPassEmail1 = "Το συνθηματικό σας έχει επανατοποθετηθεί επιτυχώς. Το νέο σας συνθηματικό είναι αυτό που ακολουθεί

";
			
$langPassEmail2 = "

Για λόγους ασφάλειας, παρακαλούμε αλλάξετε αυτό το συνθηματικό άμεσα, σε κάτι
που μόνο εσείς το γνωρίζετε μόλις συνδεθείτε στην πλατφόρμα.
";


$langAccountResetSuccess1="Η επανατοποθέτηση του συνθηματικού σας έχει ολοκληρωθεί";
$langAccountResetSuccess2="Στη διεύθυνση ηλεκτρονικού ταχυδρομείου σας";
$langAccountResetSuccess3="έχει σταλεί ένα μήνυμα με το νέο σας συνθηματικό πρόσβασης.";

$langAccountEmailError1 = 'Παρουσιάστηκε σφάλμα κατά την αποστολή των στοιχείων σας';
$langAccountEmailError2 = "Δεν κατέστη δυνατή η αποστολή των οδηγιών επανατοποθέτησης του συνθηματικού σας στη διεύθυνση";
$langAccountEmailError3 = 'Αν χρειαστεί, μπορείτε να επικοινωνήσετε με τους διαχειριστές του συστήματος στη διεύθυνση';
$lang_pass_email_ok = 'Τα στοιχεία του λογαριασμού σας βρέθηκαν και στάλθηκαν
	μέσω ηλεκτρονικού ταχυδρομείου στη διεύθυνση';

$langAccountNotFound1 = 'Δε βρέθηκε λογαριασμός στο σύστημα με τη διεύθυνση ηλεκτρονικού ταχυδρομείου που δώσατε'; 
$langAccountNotFound2 = ' Αν παρόλα αυτά είστε σίγουρος ότι έχετε ήδη λογαριασμό, παρακαλούμε επικοινωνήστε με τους διαχειριστές του συστήματος στη διεύθυνση ';

$langAccountNotFound3 = 'δίνοντας και στοιχεία που μπορούν να βοηθήσουν στο να βρούμε το λογαριασμό σας, όπως ονοματεπώνυμο, σχολή/τμήμα, κλπ.';

$lang_email = 'e-mail';
$lang_send = 'Αποστολή';
$lang_username="Όνομα χρήστη";
$langPassCannotChange1="Το συνθηματικό αυτού του λογαριασμού δεν μπορεί να αλλαχθεί";
$langPassCannotChange2="Ο λογαριασμός αυτός ανήκει σε εξωτερική μέθοδο πιστοποίησης. Παρακαλούμε, επικοινωνήστε με το διαχειριστή στην διεύθυνση";
$langPassCannotChange3="για περισσότερες πληροφορίες";


/******************************************************
* manual.inc.php
*******************************************************/
$langIntroMan = "Στην ενότητα αυτή υπάρχουν διαθέσιμα χρήσιμα εγχειρίδια που αφορούν την περιγραφή, τη λειτουργία και τις δυνατότητες της πλατφόρμας eClass";
$langFinalDesc = "Αναλυτική Περιγραφή eClass";
$langShortDesc = "Σύντομη Περιγραφή eClass";
$langManS = "Εγχειρίδιο Χρήστη Φοιτητή";
$langManT = "Εγχειρίδιο Καθηγητή";
$langOr = "ή";
$langNote = "Σημείωση";
$langAcrobat = "Για να διαβάσετε τα αρχεία PDF μπορείτε να χρησιμοποιήσετε το πρόγραμμα Acrobat Reader";
$langWhere ="που θα βρείτε";
$langHere = "εδώ";


/*********************************************************
* opencours.inc.php
*********************************************************/

$opencours="Κατάλογος Μαθημάτων";
$listfac="Επιλογή Τμήματος";
$listtomeis = "Τομείς";
$langDepartmentsList = "Ακολουθεί ο κατάλογος τμημάτων του ιδρύματος.
	Επιλέξτε οποιοδήποτε από αυτά για να δείτε τα διαθέσιμα σε αυτό μαθήματα.";
$langWrongPassCourse = "Λάθος συνθηματικό για το μάθημα";
$langAvCourses = "διαθέσιμα μαθήματα";
$langAvCourse = "διαθέσιμο μάθημα";

$m['begin'] = 'αρχή';
$m['department'] = 'Σχολή / Τμήμα';
$m['lessoncode'] = 'Όνομα Μαθήματος (κωδικός)';
$m['tomeis'] = 'Τομείς';
$m['tomeas'] = 'Τομέας';
$m['open'] = 'Ανοικτά μαθήματα (Ελεύθερη Πρόσβαση)';
$m['restricted'] = 'Ανοικτά μαθήματα με εγγραφή (Απαιτείται λογαριασμός χρήστη)';
$m['closed'] = 'Κλειστά μαθήματα';
$m['title'] = 'Τίτλος';
$m['description'] = 'Περιγραφή';
$m['professor'] = 'Καθηγητής';
$m['type']  = 'Κατηγορία μαθήματος';
$m['pre']  = 'Προπτυχιακό';
$m['post']  = 'Μεταπτυχιακό';
$m['other']  = 'Άλλο';

$m['pres']  = 'Προπτυχιακά';
$m['posts']  = 'Μεταπτυχιακά';
$m['others']  = 'Άλλα';
$m['legend'] = 'Υπόμνημα';
$m['legopen'] = 'Ανοικτό Μάθημα';
$m['legrestricted'] = 'Απαιτείται εγγραφή';
$m['legclosed'] = 'Κλειστό μάθημα';
$m['nolessons'] = 'Δεν υπάρχουν διαθέσιμα μαθήματα!';

$m['type']="Τύπος";
$m['name']="Μάθημα";
$m['code']="Κωδικός μαθήματος";
$m['prof']="Καθηγητής(ες)";

$m['mailprof'] = "Για να εγγραφείτε στο μάθημα θα πρέπει να στείλετε mail στον διδάσκοντα του μαθήματος
κάνοντας κλικ";
$m['here'] = " εδώ.";
$m['unsub'] = "Το μάθημα είναι κλειστό και δεν μπορείτε να απεγγραφείτε";

/***************************************************************
* pedasugggest.inc.php
****************************************************************/

/*
unset($titreBloc);
unset($titreBlocNotEditable);
unset($questionPlan);
unset($info2Say);
*/
$titreBloc[] = "Περιγραφή";
$titreBlocNotEditable[] = FALSE;
$questionPlan[] = "Σε τι αναφέρεται το μάθημα? Χρειάζονται τυχόν προαπαιτούμενα μαθήματα?";
$info2Say[] = "Πληροφορίες σχετικά με το μάθημα θα δωθούν απο τον διδάσκοντα";
$titreBloc[] = "Στόχοι";
$titreBlocNotEditable[] = TRUE;
$info2Say[] = "Οι στόχοι του μαθήματος θα ανακοινωθούν άμεσα";
$questionPlan[] = "Ποιοί είναι οι στόχοι του μαθήματος?";
$titreBloc[] = "Περιεχόμενο Μαθήματος";
$titreBlocNotEditable[] = TRUE;
$questionPlan[] = "Ποια θα είναι η διδακτέα ύλη μαθήματος ?";
$info2Say[] = "Η διδακτέα ύλη θα ανακοινωθεί σύντομα";
$titreBloc[] = "Εκπαιδευτικές Δραστηριότητες";
$titreBlocNotEditable[] = TRUE;
$questionPlan[] = "Ποιες τυχόν εκπαιδευτικές δραστηριότητηες θα υπάρξουν για την εμπέδωση της διδακτέας ύλης του μαθήματος?";
$info2Say[] = "Θα υπάρξουν αρκετές. Θα ανακοινωθούν πολύ σύντομα.";
$titreBloc[] =" Βοηθήματα";
$titreBlocNotEditable[] = TRUE;
$questionPlan[] = "Υπάρχουν επιπλέον εκπαιδευτικά βοηθήματα για το μάθημα? Ειναι γενικά διαθέσιμα?";
$info2Say[] = "Υπάρχουν αρκετά εκπαιδευτικά βοηθήματα για το μάθημα. θα υπάρξει πλήρης κατάλογος και πληροφορίες πρόσβασης.";
$titreBloc[] = "Ανθρώπινο Δυναμικό";
$titreBlocNotEditable[] = TRUE;
$questionPlan[] = "Υπάρχει διαθέσιμο ανθρώπινο δυναμικό για βοήθεια στη διδασκαλία του μαθήματος?";
$info2Say[] = "Υπάρχει διαθέσιμο ανθρώπινο δυναμικό καθώς και υλικοτεχνική υποδομή.";
$titreBloc[] = "Τρόποι αξιολόγησης / εξέτασης";
$titreBlocNotEditable[] = TRUE;
$questionPlan[] = "Με ποιο τρόπο θα γίνει η εξέταση του μαθήματος?";
$info2Say[] = "Η εξέταση θα γίνει με προφορικές και γραπτές εξετάσεις.";
$titreBloc[] ="Συμπληρωματικά στοιχεία";
$titreBlocNotEditable[] = TRUE;


/********************************************************************
* perso.inc.php
*********************************************************************/

$langMyPersoLessons = "ΤΑ ΜΑΘΗΜΑΤΑ ΜΟΥ";
$langMyPersoDeadlines = "ΟΙ ΔΙΟΡΙΕΣ ΜΟΥ";
$langMyPersoAnnouncements = "ΟΙ ΤΕΛΕΥΤΑΙΕΣ ΜΟΥ ΑΝΑΚΟΙΝΩΣΕΙΣ";
$langMyPersoDocs = "ΤΑ ΤΕΛΕΥΤΑΙΑ ΜΟΥ ΕΓΓΡΑΦΑ";
$langMyPersoAgenda = "Η ΑΤΖΕΝΤΑ ΜΟΥ";
$langMyPersoForum = "ΟΙ ΤΕΛΕΥΤΑΙΕΣ ΑΠΟΣΤΟΛΕΣ ΣΤΙΣ ΠΕΡΙΟΧΕΣ ΣΥΖΗΤΗΣΕΩΝ ΜΟΥ";

$langLesson = "Μάθημα";
$langAssignment = "Εργασία";
$langDeadline = "Λήξη";

$langNoEventsExist="Δεν υπάρχουν γεγονότα";
$langNoAssignmentsExist="Δεν υπάρχουν εργασίες προς παράδοση";
$langNoAnnouncementsExist="Δεν υπάρχουν ανακοινώσεις";
$langNoDocsExist="Δεν υπάρχουν έγγραφα";
$langNoPosts="Δεν υπάρχουν αποστολές στις περιοχές συζητήσεων";

$langNotEnrolledToLessons="Δεν είστε εγγεγραμμένος/η σε μαθήματα";
$langCreateLesson="Μπορείτε να δημιουργήσετε μάθημα ακολουθώντας τον σύνδεσμο \"$langCourseCreate\"";
$langEnroll="Μπορείτε να εγγραφείτε σε μαθήματα ακολουθώντας τον σύνδεσμο \"$langOtherCourses\"";

$langMore="...[Περισσότερα]";
$langSender="Αποστολέας";
$langUnknown="Άγνωστο";
$langDuration="Διάρκεια";

 
/***********************************************************
* phpbb.inc.php
************************************************************/

$langHelp="βοήθεια";

$langTitle="Τίτλος";

$langAdm="διαχείριση";
$langQuote="quote";
$langEditDel="αλλαγή/διαγραφή";
$langSeen="Το έχουν δει";
$langLastMsg="Τελευταίο μην.";

$langLoginBeforePost1 = "Για να στείλετε μηνύματα, ";
$langLoginBeforePost2 = "πρέπει προηγουμένως να ";
$langLoginBeforePost3 = "κάνετε login στην Τάξη";

// page_header.php

$langNewTopic="Νέο θέμα";
$langGroupForumLink="Περιοχή συζητήσεων ομάδας";
$langGroupDocumentsLink="Έγγραφα ομάδας ";
$l_forum 	= "Περιοχή συζητήσεων";
$l_forums	= "Περιοχές συζητήσεων";
$l_topic	= "Θέμα";
$l_topics 	= "Θέματα";
$l_replies	= "Απαντήσεις";
$l_poster	= "Αποστολέας";
$l_author	= "Συγγραφέας";
$l_views	= "Όψεις";
$l_post 	= "Αποστολή";
$l_posts 	= "Αποστολές";
$l_message	= "Μήνυμα";
$l_messages	= "Μηνύματα";
$l_subject	= "Θέμα";
$l_body		= "Σώμα μηνύματος";
$l_from		= "Από";   // Message from
$l_moderator 	= "Συντονιστής";
$l_username 	= "Όνομα χρήστη";
$l_password 	= "Συνθηματικό";
$l_email 	= "Email";
$l_emailaddress	= "Διεύθυνση Email";
$l_preferences	= "Προτιμήσεις";

$l_anonymous	= "Ανώνυμος";  // Post
$l_guest	= "Φιλοξενούμενος"; // Whosonline
$l_noposts	= "Όχι $l_posts";
$l_joined	= "Προσχώρηση";
$l_gotopage	= "Πήγαινε σε σελίδα";
$l_nextpage 	= "Επόμενη σελίδα";
$l_prevpage     = "Προηγούμενη σελίδα";
$l_go		= "Πήγαινε";
$l_selectforum	= "Επιλογή $l_forum";

$l_date		= "Ημερομηνία";
$l_number	= "Αριθμός";
$l_name		= "Όνομα";
$l_options 	= "Επιλογές";
$l_submit	= "Υποβολή";
$l_confirm 	= "Επιβεβαίωση";
$l_enter 	= "Είσοδος";
$l_by		= "από"; // Posted by
$l_ondate	= "στις"; // This message is edited by: $username on $date
$l_new          = "Νέο";

$l_html		= "HTML";
$l_bbcode	= "BBcode";
$l_smilies	= "Smilies";
$l_on		= "On";
$l_off		= "Off";
$l_yes		= "Ναι";
$l_no		= "Όχι";

$l_click 	= "Πιέστε";
$l_here 	= "εδώ";
$l_toreturn	= " για επιστροφή";
$l_returnindex	= "$l_toreturn στο ευρετήριο περιοχών συζητήσεων.";
$l_returntopic	= "$l_toreturn στον κατάλογο θεμάτων της περιοχής συζητήσεων.";

$l_error	= "Σφάλμα";
$l_tryagain	= "Παρακαλούμε επιστρέψτε στην προηγούμενη σελίδα και ξαναδοκιμάστε.";
$l_mismatch 	= "Τα συνθηματικά δεν είναι ίδια.";
$l_userremoved 	= "Ο χρήστης αυτός έχει διαγραφεί από τον κατάλογο χρηστών";
$l_wrongpass	= "Δώσατε λάθος συνθηματικό.";
$l_userpass	= "Παρακαλούμε δώστε το όνομα χρήστη και το συνθηματικό σας.";
$l_banned 	= "Σας έχει απαγορευτεί η πρόσβαση σε αυτή την περιοχή. Αν έχετε κάποια ερώτηση επικοινωνήστε με το διαχειριστή του συστήματος.";
$l_enterpassword= "Πρέπει να δώσετε το συνθηματικό σας";

$l_nopost	= "Δεν έχετε δικαίωμα αποστολής μηνυμάτων σε αυτή την περιοχή.";
$l_noread	= "Δεν έχετε δικαίωμα ανάγνωσης αυτής της περιοχής.";

$l_lastpost 	= "Τελευταία $l_post";
$l_sincelast	= "από την προηγούμενη επίσκεψή σας.";
$l_newposts 	= "Υπάρχουν νέα $l_posts $l_sincelast";
$l_nonewposts 	= "Δεν υπάρχουν νέα $l_posts $l_sincelast";

// Index page
$l_indextitle	= "Ευρετήριο περιοχών συζητήσεων";

// Members and profile
$l_profile	= "Προφίλ";
$l_register	= "Καταχώρηση";
$l_onlyreq 	= "Απαιτείται μόνο αν αλλάζει";
$l_location 	= "Από";
$l_viewpostuser	= "Εμφάνιση μηνυμάτων μόνο αυτού του χρήστη";
$l_perday       = "$l_messages ανά ημέρα";
$l_oftotal      = "του συνόλου";
$l_url 		= "URL";
$l_icq 		= "ICQ";
$l_icqnumber	= "Αριθμός ICQ";
$l_icqadd	= "Προσθήκη";
$l_icqpager	= "Pager";
$l_aim 		= "AIM";
$l_yim 		= "YIM";
$l_yahoo 	= "Yahoo Messenger";
$l_msn 		= "MSN";
$l_messenger 	= "MSN Messenger";
$l_website 	= "Διεύθυνση ιστοσελίδας";
$l_occupation 	= "Επάγγελμα";
$l_interests 	= "Ενδιαφέροντα";
$l_signature 	= "Υπογραφή";
$l_sigexplain 	= "Ένα κείμενο που επισυνάπτεται στο τέλος των μηνυμάτων σας.<BR>Μέγιστο μήκος 255 χαρακτήρες!";
$l_usertaken	= "Το $l_username που επιλέξατε χρησιμοποιείται ήδη.";
$l_userdisallowed= "Το $l_username που επιλέξατε δεν επιτρέπεται από το διαχειριστή. $l_tryagain";
$l_infoupdated	= "Οι πληροφορίες σας ενημερώθηκαν";
$l_publicmail	= "Εμφάνιση της διεύθυνσης email σας στους άλλους χρήστες";
$l_itemsreq	= "Τα στοιχεία που σημειώνονται με * είναι υποχρεωτικά";

// Viewforum
$l_viewforum	= "Εμφάνιση περιοχής συζητήσεων";
$l_notopics	= "Δεν υπάρχουν θέματα σε αυτή την περιοχή. Μπορείτε να ξεκινήσετε ένα νέο.";
$l_hotthres	= "To όριο των μηνυμάτων ξεπεράστηκε";
$l_islocked	= "Το θέμα είναι κλειδωμένο (δεν μπορούν να αποσταλούν νέα μηνύματα σε αυτό)";
$l_moderatedby	= "Συντονιστής: ";

// Private forums
$l_privateforum	= "Αυτή η περιοχή συζητήσεων είναι <b>προσωπική</b>.";
$l_private 	= "$l_privateforum<br>Σημείωση: πρέπει να έχετε ενεργοποιημένα τα cookies για να χρησιμοποιήσετε προσωπικές περιοχές.";
$l_noprivatepost = "$l_privateforum Δεν έχετε πρόσβαση αποστολής μηνυμάτων σε αυτή την περιοχή.";

// Viewtopic
$l_topictitle	= "Εμφάνιση θέματος";
$l_unregistered	= "Μη καταχωρημένος χρήστης";
$l_posted	= "Στάλθηκε";
$l_profileof	= "Εμφάνιση προφίλ του";
$l_viewsite	= "Μετάβαση στην ιστοσελίδα του";
$l_icqstatus	= "$l_icq status";  // ICQ status
$l_editdelete	= "Διόρθωση/διαγραφή αυτού του μηνύματος";
$l_replyquote	= "Απάντηση με παράθεση";
$l_viewip	= "Εμφάνιση IP αποστολέα (μόνο για διαχειριστές/συντονιστές)";
$l_locktopic	= "Κλείδωμα αυτού του θέματος";
$l_unlocktopic	= "Ξεκλείδωμα αυτού του θέματος";
$l_movetopic	= "Μεταφορά αυτού του θέματος";
$l_deletetopic	= "Διαγραφή αυτού του θέματος";

// Functions
$l_loggedinas	= "Συνδεδεμένος ως";
$l_notloggedin	= "Μη συνδεδεμένος";
$l_logout	= "Αποσύνδεση";
$l_login	= "Σύνδεση";

// Page_header
$l_separator	= "» »";  // Included here because some languages have
		          // problems with high ASCII (Big-5 and the like).
$l_editprofile	= "Μεταβολή προφίλ";
$l_editprefs	= "Μεταβολή προτιμήσεων";
$l_search	= "Αναζήτηση";
$l_memberslist	= "Λίστα μελών";
$l_faq		= "FAQ";
$l_privmsgs	= "Προσωπικά μηνύματα";
$l_sendpmsg	= "Αποστολή προσωπικού μηνύματος";
$l_statsblock   = '$statsblock = "Οι χρήστες μας έχουν στείλει συνολικά -$total_posts- μηνύματα.<br>
Έχουμε -$total_users- καταχωρημένους χρήστες.<br>
Ο νεότερος καταχωρημένος χρήστης: -<a href=\"$profile_url\">$newest_user</a>-.<br>
-$users_online- ". ($users_online==1?"χρήστης":"χρήστες") ." <a href=\"$online_url\">διαβάζουν αυτή τη στιγμή</a> τις περιοχές συζητήσεων.<br>";';
$l_privnotify   = '$privnotify = "<br>Έχετε $new_message <a href=\"$privmsg_url\">".($new_message>1?"νέα προσωπικά μηνύματα":"νέο προσωπικό μήνυμα")."</a>.";';

// Page_tail
$l_adminpanel	= "Πίνακας διαχείρισης";
$l_poweredby	= "Υποστηρίζεται από το";
$l_version	= "Έκδοση";

// Register
$l_notfilledin	= "Σφάλμα - δε συμπληρώσατε όλα τα απαιτούμενα πεδία";
$l_invalidname	= "Το όνομα χρήστη που επιλέξατε, χρησιμοποιείται ήδη.";
$l_disallowname	= "Το όνομα χρήστη δεν επιτρέπεται από τον διαχειριστή.";

$l_welcomesubj	= "Καλωσορίσατε στις περιοχές συζητήσεων";

$l_beenadded	= "Προστεθήκατε στη βάση δεδομένων.";
$l_thankregister= "Σας ευχαριστούμε για την εγγραφή σας!";
$l_useruniq	= "Πρέπει να είναι μοναδικό. Δε γίνεται δύο χρήστες να έχουν το ίδιο όνομα.";
$l_storecookie	= "Αποθήκευση του ονόματός σας σε ένα «cookie» για ένα χρόνο.";

// Prefs
$l_prefupdated	= "Οι προτιμήσεις ενημερώθηκαν. <a href=\"index.php\">Πιέστε εδώ για να επιστρέψετε</a> στην κεντρική σελίδα";
$l_editprefs	= "Αλλαγή των προτιμήσεών σας";
$l_themecookie	= "ΣΗΜΕΙΩΣΗ: για να αλλάξετε την εμφάνιση των σελίδων πρέπει να έχετε τα cookies ενεργά.";
$l_alwayssig	= "Προσθήκη υπογραφής σε όλα τα μηνύματα";
$l_alwaysdisable= "Απενεργοποίηση παντού "; // Only used for next three strings
$l_alwayssmile	= "Απενεργοποίηση των $l_smilies παντού";
$l_alwayshtml	= "Απενεργοποίηση της $l_html παντού";
$l_alwaysbbcode	= "Απενεργοποίηση του $l_bbcode παντού";
$l_boardtheme	= "Εμφάνιση περιοχής συζητήσεων";
$l_boardlang    = "Γλώσσα περιοχής συζητήσεων";
$l_nothemes	= "Δεν υπάρχουν ρυθμίσεις εμφάνισης στη βάση";
$l_saveprefs	= "Αποθήκευση προτιμήσεων";

// Search
$l_searchterms	= "Λέξεις κλειδιά";
$l_searchany	= "Αναζήτηση για ΟΠΟΙΟΝΔΗΠΟΤΕ από τους όρους (Προκαθορισμένο)";
$l_searchall	= "Αναζήτηση για ΟΛΟΥΣ τους όρους";
$l_searchallfrm	= "Αναζήτηση σε όλες τις περιοχές συζητήσεων";
$l_sortby	= "Ταξινόμηση κατα";
$l_searchin	= "Αναζήτηση σε";
$l_titletext	= "Τίτλο και Κείμενο";
$l_nomatches	= "Δεν βρέθηκαν εγγραφές που να ταιριάζουν. Παρακαλώ διευρύνετε την αναζήτηση.";

// Whosonline
$l_whosonline	= "Ποιος είναι συνδεδεμένος;";
$l_nousers	= "Κανείς χρήστης δε διαβάζει αυτή τη στιγμή τις περιοχές συζητήσεων";


// Editpost
$l_notedit	= "Δεν μπορείτει να αλλάξετε μήνυμα που δεν είναι δικό σας.";
$l_permdeny	= "Δεν δώσατε το σωστό $l_password ή δεν έχετε το δικαίωμα να αλλάξετε αυτό το μήνυμα. $l_tryagain";
$l_editedby	= "Το $l_message διορθώθηκε από:";
$l_stored	= "Το $l_message αποθηκεύτηκε στη βάση.";
$l_viewmsg	= " για να εμφανίσετε το $l_message.";
$l_deleted	= "Το μήνυμα διαγράφτηκε.";
$l_nouser	= "Το $l_username δεν υπάρχει.";
$l_passwdlost	= "Ξέχασα το συνθηματικό μου!";
$l_delete	= "Διαγραφή αυτού του μηνύματος";

$l_disable	= "Απενεργοποίηση";
$l_onthispost	= "σε αυτό το μήνυμα";

$l_htmlis	= "$l_html ";
$l_bbcodeis	= "$l_bbcode ";

$l_notify	= "Ειδοποίηση μέσω email αν σταλούν απαντήσεις";

// Newtopic
$l_emptymsg	= "Για να στείλετε ένα μήνυμα πρέπει να γράψετε κάποιο κείμενο. Δεν μπορείτε να στείλετε κενό μήνυμα.";
$l_aboutpost	= "Σχετικά με την αποστολή μηνυμάτων";
$l_regusers	= "Όλοι οι <b>εγγεγραμμένοι</b> χρήστες";
$l_anonusers	= "Οι <b>ανώνυμοι</b> χρήστες";
$l_modusers	= "Μόνο οι <b>συντονιστές</b> και οι <b>διαχειριστές</b>";
$l_anonhint	= "<br>(για να στείλετε μήνυμα ανώνυμα απλώς μη δώσετε όνομα χρήστη και συνθηματικό)";
$l_inthisforum	= "μπορούν να στείλουν απαντήσεις και να ανοίξουν νέα θέματα εδώ";
$l_attachsig	= "Εμφάνιση υπογραφής <font size=-2>(Μπορεί να προστεθεί ή να αλλαχτεί στο προφίλ σας)</font>";
$l_cancelpost	= "Ακύρωση αποστολής";

// Reply
$l_nopostlock	= "Δεν μπορείτε να στείλετε απαντήσεις σε αυτό το θέμα, έχει κλειδωθεί.";
$l_topicreview  = "Ανασκόπηση θέματος";
$l_notifysubj	= "Στάλθηκε μια απάντηση στο θέμα σας.";

$l_quotemsg	= '[quote]\nΣτις $m[post_time], ο/η $m[username] έγραψε:\n$text\n[/quote]';

// Sendpmsg
$l_norecipient	= "Πρέπει να εισάγετε το όνομα χρήστη προς το οποίο θέλετε να στείλετε το μήνυμα.";
$l_sendothermsg	= "Αποστολή άλλου προσωπικού μηνύματος";
$l_cansend	= "μπορούν να στείλουν προσωπικά μηνύματα";  // All registered users can send PM's
$l_yourname	= "Το όνομα χρήστη σας";
$l_recptname	= "Όνομα χρήστη παραλήπτη";

// Replypmsg
$l_pmposted	= "Στάλθηκε απάντηση, πιέστε <a href=\"viewpmsg.php\">εδώ</a> για να δείτε τα προσωπικά σας μηνύματα";

// Viewpmsg
$l_nopmsgs	= "Δεν έχετε προσωπικά μηνύματα.";
$l_reply	= "Απάντηση";

// Delpmsg
$l_deletesucces	= "Διαγραφή επιτυχής.";

// Smilies
$l_smilesym	= "Τι να γράψετε";
$l_smileemotion	= "Συναίσθημα";
$l_smilepict	= "Εικόνα";

// Sendpasswd
$l_wrongactiv	= "Το κλειδί ενεργοποίησης που δώσατε δεν είναι σωστό. Παρακαλώ ελέγξτε το μήνυμα email που λάβατε και βεβαιωθείτε ότι έχετε αντιγράψει το κλειδί ενεργοποίησης ακριβώς.";
$l_passchange	= "Το συνθηματικό σας αλλάχτηκε επιτυχώς. Μπορείτε τώρα να πάτε στο <a href=\"bb_profile.php?mode=edit\">προφίλ σας</a> και να διαλάξετε ένα συνθηματικό της προτίμησής σας.";
$l_wrongmail	= "Η διεύθυνση email που δώσατε δεν ταιριάζει με αυτήν που είναι αποθηκευμένη στη βάση μας.";

$l_pwdmessage	= '$checkinfo[username],
Λαμβάνετε αυτό το μήνυμα γιατί εσείς (ή κάποιος που προσποιήθηκε εσάς)
ζήτησε αλλαγή συνθηματικού στις περιοχές συζητήσεων του $sitename. Αν
πιστεύετε ότι λάβατε αυτό το μήνυμα κατά λάθος, απλώς διαγράψτε το και
το συνθηματικό σας θα μείνει ίδιο.

Το νέο συνθηματικό που δημιουργήθηκε αυτόματα είναι: $newpw

Για να τεθεί σε ενέργεια αυτή η αλλαγή πρέπει να επισκεφτείτε τη σελίδα:

   http://$SERVER_NAME$PHP_SELF?actkey=$key

Μόλις επισκεφτείτε αυτή τη σελίδα το συνθηματικό σας θα αλλαχτεί στη βάση
δεδομένων μας, και θα μπορείτε να το αλλάξετε όπως επιθυμείτε από την σελίδα
του προφίλ σας.

Σας ευχαριστούμε που χρησιμοποιείτε τις περιοχές συζητήσεων του $sitename.

$email_sig';

$l_passsent	= "Ορίστηκε για σας ένα νέο τυχαίο συνθηματικό. Παρακαλούμε ελέγξτε τα μηνύματα email σας για το πώς θα ολοκληρώσετε τη διαδικασία αλλαγής συθηματικού.";
$l_emailpass	= "Αποστολή συνθηματικού μέσω email";
$l_passexplain	= "Παρακαλούμε συμπληρώστε αυτή τη φόρμα, και ένα νέο συνθηματικό θα σας σταλεί μέσω ηλεκτρονικού ταχυδρομείου.";
$l_sendpass	= "Αποστολή συνθηματικού";


/*****************************************************************
* questionnaire.inc.php
******************************************************************/

$langQuestionnaire = "Ερωτηματολόγιo";
$langSurveysActive = "Ενεργές Έρευνες Μαθησιακού Προφίλ";
$langSurveysInactive = "Ανενεργές Έρευνες Μαθησιακού Προφίλ";
$langSurveyName = "Όνομα";
$langSurveyNumAnswers = "Απαντήσεις";
$langSurveyCreation = "Δημιουργία";
$langSurveyDateCreated = "Δημιουργήθηκε την";
$langSurveyStart = "Ξεκίνησε την";
$langSurveyEnd = "και τελείωσε την";
$langSurveyOperations = "Λειτουργίες";
$langSurveyEdit = "Επεξεργασία";
$langSurveyRemove = "Διαγραφή";
$langSurveyQuestion = "Ερώτηση";
$langSurveyAnswer = "Απάντηση";
$langSurveyAddAnswer = "Προσθήκη Απαντήσεων";
$langSurveyType = "Τύπος";
$langSurveyMC = "Πολλαπλής Επιλογής";
$langSurveyFillText = "Συμπληρώστε το κενό";
$langSurveyContinue = "Συνέχεια";
$langSurveyMoreAnswers ="+ απαντήσεις";
$langSurveyMoreQuestions = "+ ερωτήσεις";
$langSurveyCreate = "Δημιουργία Έρευνας Μαθησιακού Προφίλ";
$langSurveyCreated ="Η Έρευνα Μαθησιακού Προφίλ δημιουργήθηκε με επιτυχία.<br><br><a href=\"questionnaire.php\">Επιστροφή</a>";
$langSurveyCreator = "Δημιουργός";
$langSurveyCourse = "Μάθημα";
$langSurveyCreationError = "Σφάλμα κατά την δημιουργία της Δημοσκόπησης. Παρακαλώ προσπαθήστε ξανά.";
$langSurveyDeactivate = "Απενεργοποίηση";
$langSurveyActivate = "Ενεργοποίηση";
$langSurveyParticipate = "Συμμετοχή";
$langSurveyDeleted ="Η Έρευνα Μαθησιακού Προφίλ διαγράφηκε με επιτυχία.<br><br><a href=\"questionnaire.php\">Επιστροφή</a>.";
$langSurveyDeactivated ="Η Έρευνα Μαθησιακού Προφίλ απενεργοποιήθηκε με επιτυχία.";
$langSurveyActivated ="Η Έρευνα Μαθησιακού Προφίλ ενεργοποιήθηκε με επιτυχία.";
$langSurveySubmitted ="Ευχαριστούμε για την συμμετοχή σας!<br><br><a href=\"questionnaire.php\">Επιστροφή</a>.";
$langSurveyUser = "Χρήστης";
$langSurveyTotalAnswers = "Συνολικός αριθμός απαντήσεων";
$langSurveyNone = "Δεν έχουν δημιουργηθεί έρευνες μαθησιακού προφίλ για το μάθημα";
$langSurveyInactive = "Η Έρευνα Μαθησιακού Προφίλ έχει λήξει ή δεν έχει ενεργοποιηθεί ακόμα.";
$langSurveyCharts = "Αποτελέσματα έρευνας";

$langQPref = "Τι τύπο ερωτηματολογίου επιθυμείτε;";
$langQPrefSurvey = "Έρευνα μαθησιακού προφίλ";
$langQPrefPoll = "Δημοσκόπηση";

$langNamesPoll = "Δημοσκοπήσεις";
$langNamesSurvey = "Έρευνες Μαθησιακού Προφίλ";
$langHasParticipated = "Έχετε ήδη συμμετάσχει";

$langSurveyInfo ="Επιλέξτε ένα έτοιμο ερώτημα (σύμφωνα με το πρότυπο COLLES/ATTL) ή εισάγετε δικιά σας ερώτηση στα κενά πεδία.";

$langQQuestionNotGiven ="Δεν έχετε εισάγει την τελευταία ερώτηση.";
$langQFillInAllQs ="Παρακαλώ απαντήστε σε όλες τις ερωτήσεις.";

// polls
$langPollsActive = "Ενεργές Δημοσκοπήσεις";
$langPollsInactive = "Ανενεργές Δημοσκοπήσεις";
$langPollName = "Όνομα";
$langPollCreation = "Δημιουργία";
$langPollStart = "Έναρξη";
$langPollStarted = "Ξεκίνησε την";
$langPollEnd = "Λήξη";
$langPollEnded = "και τελείωσε την";
$langPollOperations = "Λειτουργίες";
$langPollEdit = "Επεξεργασία";
$langPollRemove = "Διαγραφή";
$langPollQuestion = "Ερώτηση";
$langPollAnswer = "Απάντηση";
$langPollNumAnswers = "Απαντήσεις";
$langPollAddAnswer = "Προσθήκη απαντήσεων";
$langPollType = "Τύπος";
$langPollMC = "Πολλαπλής Επιλογής";
$langPollFillText = "Συμπληρώστε το κενό";
$langPollContinue = "Συνέχεια";
$langPollMoreAnswers ="+ απαντήσεις";
$langPollMoreQuestions = "+ ερωτήσεις";
$langPollCreate = "Δημιουργία Δημοσκόπησης";
$langPollCreated ="Η Δημοσκόπηση δημιουργήθηκε με επιτυχία.<br><br> <a href=\"questionnaire.php\">Επιστροφή</a>.";
$langPollCreator = "Δημιουργός";
$langPollCreateDate = "Η δημοσκόπηση δημιουργήθηκε την";
$langPollCourse = "Μάθημα";
$langPollCreationError = "Σφάλμα κατά την δημιουργία της Δημοσκόπησης. Παρακαλώ προσπαθήστε ξανά.";
$langPollDeactivate = "Απενεργοποίηση";
$langPollActivate = "Ενεργοποίηση";
$langPollParticipate = "Συμμετοχή";
$langPollDeleted ="Η Δημοσκόπηση διαγράφηκε με επιτυχία. <br><br><a href=\"questionnaire.php\">Επιστροφή</a>.";
$langPollDeactivated ="Η Δημοσκόπηση απενεργοποιήθηκε με επιτυχία!";
$langPollActivated ="Η Δημοσκόπηση ενεργοποιήθηκε με επιτυχία!";
$langPollSubmitted ="Ευχαριστούμε για την συμμετοχή σας!<br><br><a href=\"questionnaire.php\">Επιστροφή</a>";
$langPollTotalAnswers = "Συνολικός αριθμός απαντήσεων";
$langPollNone = "Δεν υπάρχουν αυτή την στιγμή διαθέσιμες δημοσκοπήσεις.";
$langPollInactive = "Η Δημοσκόπηση έχει λήξει ή δεν έχει ενεργοποιηθεί ακόμα.";
$langPollCharts = "Αποτελέσματα δημοσκόπησης";
$langIndividuals = "Αποτελέσματα ανά χρήστη";
$langDelConf = "Επιβεβαίωση διαγραφής";
$langCollectiveCharts = "Συγκεντρωτικά αποτελέσματα";


/************************************************************
* registration.inc.php
*************************************************************/

$langSee = "Προεπισκόπηση";
$langNoSee = "Απαιτείται εγγραφή";
$langSubscribe = "Υποβολή αλλαγών";
$langCourseName = "Τίτλος Μαθήματος";
$langCoursesLabel = 'Τμήματα';
$langFaculte = "Τμήμα";
$langNoCourses = "Δεν υπάρχουν διαθέσιμα μαθήματα για εγγραφή";

$langEmptyFields = "Αφήσατε μερικά πεδία κενά!";
$langOtherCourses = "Εγγραφή σε μάθημα";
$langRegistration="Εγγραφή";
$langSurname="Επώνυμο";
$langUsername="Όνομα χρήστη (username)";
$langConfirmation="Επιβεβαίωση συνθηματικού";
$langUserNotice = "(μέχρι 20 χαρακτήρες)";
$langEmailNotice = "Το e-mail δεν είναι απαραίτητο, αλλά χωρίς αυτό δε θα μπορείτε να λαμβάνετε
ανακοινώσεις, ούτε θα μπορείτε να χρησιμοποιήσετε τη λειτουργία υπενθύμισης συνθηματικού.";
$langAm = "Αριθμός μητρώου";
$langDepartment="Σχολή / Τμήμα";
$langUserDetails = "Στοιχεία νέου χρήστη";
$langSubmitNew = "Υποβολή Αίτησης";

// newuser_second.php
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
$langPersonalSettingsMore="	Κάντε κλίκ <a href='../../index.php'>εδώ</a> για να εισέλθετε στο προσωπικό σας χαρτοφυλάκιο.<br>
							Εκεί μπορείτε: 
							<ul>
								<li>να περιηγηθείτε στο περιβάλλον της πλατφόρμας και τις προσωπικές σας επιλογές,</li>
								<li>να επιλέξετε στον \"Κατάλογο Μαθημάτων\" τα μαθήματα που επιθυμείτε να παρακολουθήσετε.</li>
							<ul>";
$langYourRegTo="Ο κατάλογος μαθημάτων σας περιέχει";
$langIsReg="έχει ενημερωθεί";
$langCanEnter="Είσοδος στην ψηφιακή αίθουσα.";
$langChoice="Επιλογή";
$langLessonName="Όνομα μαθήματος";
$langProfessors="Καθηγητής(ες)";

// profile.php
$langPassTwo="Έχετε πληκτρολογήσει δύο διαφορετικά νέα συνθηματικά";
$langAgain="Ξαναπροσπαθήστε!";
$langFields="Αφήσατε μερικά πεδία κενά";
$langUserTaken="Το όνομα χρήστη που επιλέξατε δεν είναι διαθέσιμο";
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
$langUnreg = "Διαγραφή";
$langAddHereSomeCourses = "<p>Για να εγγραφείτε / απεγγραφείτε σε / από ένα μάθημα,
πρώτα επιλέξτε το τμήμα στο οποίο βρίσκεστε και στη συνέχεια επιλέξτε / αποεπιλέξτε το μάθημα.<br>
<p>Για να καταχωρηθούν οι προτιμήσεις σας πατήστε 'Υποβολή αλλαγών'</p><br>";
$langTitular = "Διδάσκων";
$langDeleteUser = "Είστε σίγουρος ότι θέλετε να διαγράψεται τον χρήστη";
$langDeleteUser2 = "από αυτό το μάθημα";

// adduser.php - added by adia 2003-02-21
$langAskUser = "Πληκτρολογήστε το επώνυμο ή το όνομα ή το όνομα χρήστη για να αναζητήσετε τον χρήστη που θέλετε να προστεθεί.
        <br><br>Ο χρήστης θα πρέπει να έχει ήδη λογαριασμό στην πλατφόρμα για να γραφτεί στο μάθημά σας.";
$langAskManyUsers = "Πληκτρολογήστε το όνομα αρχείου χρηστών ή κάντε κλικ στο πλήκτρο \"Browse\" για να το
    αναζητήσετε.<br><br>Οι χρήστες θα πρέπει να έχουν ήδη λογαριασμό στην πλατφόρμα για να γραφτούν στον μάθημά
σας.";
$langAskManyUsers2 = "<strong>Σημείωση</strong>: Το αρχείο χρηστών πρέπει να είναι απλό αρχείο κειμένου με τα ονόματα
        των χρηστών ένα ανά γραμμή. Παράδειγμα:
    <br><br>
    eleni<br>
    nikos<br>
    spiros<br>
    ";
$langAskSearch = "Πληκτρολογήστε το επώνυμο ή το όνομα ή το όνομα χρήστη που θέλετε να αναζητήσετε.";
$langAddUser = "Προσθήκη ενός χρήστη";
$langAddManyUsers  = "Προσθήκη πολλών χρηστών";
$langOneUser = "ενός χρήστη";
$langManyUsers = "πολλών χρηστών";
$langGUser = "χρήστη επισκέπτη";
$langNoUsersFound = "Δε βρέθηκε κανένας χρήστης με τα στοιχεία που δώσατε ή ο χρήστης υπάρχει ήδη στο μάθημά σας.";
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
$langphone= "Τηλέφωνο";
$langUserNoneMasc="-";
$langTutor="Διδάσκων";
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
$langIsReg="Οι αλλαγές σας κατοχυρώθηκαν.";
$langPassTooEasy ="Το συνθηματικό σας είναι πολύ απλό. Χρησιμοποιήστε ένα συνθηματικό σαν και αυτό";

$langChoiceLesson ="Επιλογή Μαθημάτων";
$langCoursesRegistered="Η εγγραφή σας στα μαθήματα που επιλέξατε έγινε με επιτυχία!";
$langNoCoursesRegistered="<p>Δεν επιλέξατε μάθημα για εγγραφή.</p><p> Μπορείτε να εγγραφείτε σε μάθημα, την
επόμενη φορά που θα μπείτε στην πλατφόρμα.</p>";

$langIfYouWantToAddManyUsers="Αν θέλετε να προσθέσετε ένα κατάλογο με χρήστες στο μάθημά σας, παρακαλώ συμβουλευτείτε τον διαχειριστή συστήματος.";

$langCourse="μάθημα";

$langLastVisits="Οι τελευταίες μου επισκέψεις";
$langLastUserVisits= "Οι τελευταίες επισκέψεις του χρήστη ";

$langGuestUser="Προσθήκη χρήστη επισκέπτη";
$langDumpUser="Κατάλογος χρηστών:";
$langExcel="α) σε αρχείο Excel";
$langCsv="β) σε αρχείο csv";

$langFieldsMissing="Αφήσατε κάποιο(α) από τα υποχρεωτικά πεδία κενό(ά) !";
$langFillAgain="Παρακαλούμε ξανασυμπληρώστε την";
$langFillAgainLink="αίτηση";

$reqregprof="Αίτηση Εγγραφής Καθηγητή";
$profpers="Στοιχεία Καθηγητή";
$profsname="Επίθετο Καθηγητή";
$profname="Όνομα Καθηγητή";
$profphone="Τηλέφωνο Καθηγητή";
$profuname="Επιθυμητό Όνομα Χρήστη (Username)";
$profcomment="Σχόλια";
$profreason="(Αναφέρατε τους λόγους χρήσης της πλατφόρμας)";
$profemail="E-mail Χρήστη";

$reg="Εγγραφή";
$reguser="Εγγραφή Χρήστη";
$reguserldap="Εγγραφή Χρήστη μέσω LDAP";
$regprof="Εγγραφή Καθηγητή";
$regprofldap="Εγγραφή Καθηγητή μέσω LDAP";
$langByLdap="Μέσω LDAP";
$langNewProf="Εισαγωγή στοιχείων νέου λογαριασμού καθηγητή";
$profsuccess="Η δημιουργία νέου λογαριασμού καθηγητή πραγματοποιήθηκε με επιτυχία!";

$dearprof="Αγαπητέ διδάσκοντα!";
$success="Η αποστολή των στοιχείων σας έγινε με επιτυχία!";
$click="Κάντε κλίκ";
$here="εδώ";
$backpage="για να επιστρέψετε στην αρχική σελίδα.";

$emailprompt="Δώστε την διεύθυνση e-mail σας:";
$ldapprompt="Δώστε το συνθηματικό LDAP σας:";
$univprompt="Επιλέξτε Πανεπιστημιακό Ίδρυμα";
$ldapnamesur="Ονοματεπώνυμο:";
$langInstitution='Ίδρυμα:';

$ldapuserexists="Στο σύστημα υπάρχει ήδη κάποιος χρήστης με τα στοιχεία που δώσατε.";
$ldapempty="Αφήσατε κάποιο από τα πεδία κενό!";
$ldapfound="βρέθηκε στον εξυπηρέτη LDAP και τα στοιχεία που έδωσε είναι σωστά";
$ldapchoice="Παρακαλούμε επιλέξτε το ίδρυμα στο οποίο ανήκετε!";
$ldapnorecords="Δεν βρέθηκαν εγγραφές. Πιθανόν να δώσατε λάθος στοιχεία.";
$ldapwrongpasswd="Το συνθηματικό που δώσατε είναι λανθασμένο. Παρακαλούμε δοκιμάστε ξανά";
$ldapproblem="Υπάρχει πρόβλημα με τα στοιχεία του";
$ldapcontact="Παρακαλούμε επικοινωνήστε με τον διαχειριστή του εξυπηρέτη LDAP.";
$ldaperror="Δεν είναι δυνατή η σύνδεση στον εξυπηρέτη του LDAP.";
$ldapmailpass="Το συνθηματικό σας είναι το ίδιο με αυτό της υπηρεσίας e-mail.";

$ldapback="Επιστροφή στην";
$ldaplastpage="προηγούμενη σελίδα";

$back="Επιστροφή στην αρχική σελίδα";

$star="(Τα πεδία με (*) είναι υποχρεωτικά)";

$mailsubject="Αίτηση Καθηγητή - Υπηρεσία Ασύγχρονης Τηλεκπαίδευσης";
$mailsubject2="Αίτηση Φοιτητή - Υπηρεσία Ασύγχρονης Τηλεκπαίδευσης";

$contactphone="Τηλέφωνο επικοινωνίας";
$contactpoint="Επικοινωνία";

$searchuser="Αναζήτηση Καθηγητών / Χρηστών";

$typeyourmessage="Πληκτρολογήστε το μήνυμά σας παρακάτω";
$emailsuccess="Το e-mail στάλθηκε!";

$langBackReq = "Επιστροφή στις Ανοικτές Αιτήσεις Καθηγητών";
$langTheTeacher = 'Ο διδάσκων';
$langTheUser = 'Ο χρήστης';

$langDestination = 'Παραλήπτης:';
$langAsProf = 'ως καθηγητής';
$langTel = 'Τηλ.';
$langPassSameLDAP = 'Το συνθηματικό σας είναι αυτό της υπηρεσίας καταλόγου (LDAP).';

$langLDAPUser = 'Χρήστης LDAP';
$langLogIn = 'Σύνδεση';
$langLogOut = 'Αποσύνδεση';
$langAction = 'Ενέργεια';
$langRequiredFields = 'Τα πεδία με (*) είναι υποχρεωτικά';
$langCourseVisits = "Επισκέψεις ανά μάθημα";


// USER REGISTRATION
$langAuthUserName = "Δώστε το όνομα χρήστη:";
$langAuthPassword = "Δώστε το συνθηματικό σας:";
$langAuthenticateVia = "πιστοποίηση μέσω";
$langAuthenticateVia2 = "Διαθέσιμοι τρόποι πιστοποίησης στο ίδρυμα";
$langCannotUseAuthMethods = "Η εγγραφή στην πλατφόρμα, πρός το παρόν δεν επιτρέπεται. Παρακαλούμε, ενημερώστε το διαχειριστή του συστήματος";
$langAuthReg = "Εγγραφή χρήστη";
$langUserData = "Στοιχεία χρήστη";
$langUserAccount = 'Λογαριασμός Εκπαιδευόμενου';
$langProfAccount = 'Λογαριασμός Εκπαιδευτή';
$langUserAccountInfo1 = '(Αίτηση)&nbsp;';
$langUserAccountInfo2 = '(Δημιουργία)&nbsp;';
$langUserAccountInfo3 = 'Εναλλακτικά, μπορείτε να επιλέξετε';
$langNewAccount = 'Νέος Λογαριασμός';
$langNewAccountΑctivation = 'Ενεργοποίση Λογαριασμού';
$langNewUserAccountΑctivation = 'Ενεργοποίση Λογαριασμού Εκπαιδευόμενου';
$langNewProfAccountΑctivation = 'Ενεργοποίση Λογαριασμού Εκπαιδευτή';
$langNewAccountΑctivation1 = 'την ενεργοποίση λογαριασμού σας';
$langUserExistingAccount = 'Στοιχεία Εισόδου';

// list requests
$langDateRequest = "Ημ/νία αίτησης";
$langDateReject = "Ημ/νία απόρριψης";
$langDateClosed = "Ημ/νια κλεισίματος";
$langDateCompleted = "Ημ/νία ολοκλήρωσης";
$langDeleteRequest = "Διαγραφή";
$langRejectRequest = "Απόρριψη";
$langAcceptRequest = "Εγγραφή";
$langListRequest = "Λίστα Αιτήσεων";
$langTeacherRequestHasDeleted = "Η αίτηση του καθηγητή διαγράφηκε!";
$langRejectRequestSubject = "Απόρριψη αίτησης εγγραφής στην Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης";
$langGoingRejectRequest = "Πρόκειται να απορρίψετε την αίτηση καθηγητή με στοιχεία:";
$langRequestSendMessage = "Αποστολή μηνύματος στο χρήστη στην διεύθυνση:";
$langRequestDisplayMessage = "στο μήνυμα θα αναφέρεται και το παραπάνω σχόλιο";
$langNoSuchRequest = "Δεν υπάρχει κάποια σχετική αίτηση με αυτό το ID. Δεν είναι δυνατή η επεξεργασία της αίτησης.";
$langTeacherRequestHasRejected = "Η αίτηση του καθηγητή απορρίφθηκε";
$langRequestMessageHasSent = " και στάλθηκε ενημερωτικό μήνυμα στη διεύθυνση ";
$langRequestHasRejected = "Η αίτησή σας για εγγραφή στην πλατφόρμα e-Class απορρίφθηκε.";
$langRegistrationDate = "Ημ/νια εγγραφής";
$langExpirationDate = "Ημ/νια λήξης";
$langUserID = "Κωδικός χρήστη(ID)";
$langUpdate = "ΕΝΗΜΕΡΩΣΗ";
$langStudentParticipation = "Μαθήματα στα οποία συμμετέχει ο χρήστης";
$langNoStudentParticipation = "Ο χρήστης δεν συμμετέχει σε κανένα μάθημα";
$langCannotDeleteAdmin = "Ο χρήστης αυτός (με user id = 1) είναι ο βασικός διαχειριστής της πλατφόρμας και δε διαγράφεται.";
$langExpireBeforeRegister = "Σφάλμα: H ημ/νια λήξης είναι πρίν την ημ/νια εγγραφής";
$langSuccessfulUpdate = "Μόλις ενημερώθηκε η Βάση Δεδομένων της πλατφόρμας E-Class με τα νέα στοιχεία για τον χρήστη με ID";
$langNoUpdate = "Δεν είναι εφικτή η ενημέρωση των στοιχείων για το χρήστη με id";
$langUpdateNoChange = "Δεν αλλάξατε κάποιο/κάποια από τα στοιχεία του χρήστη.";
$langError = "Σφάλμα";
$langRegistrationError = "Λάθος Ενέργεια. Επιστρέψτε στην αρχική σελίδα της πλατφόρμας.";

$langUserNoRequests = "Δεν Υπάρχουν Ανοικτές Αιτήσεις Φοιτητών !";
$langCharactersNotAllowed = "Δεν επιτρέπονται στο password και στο username, οι χαρακτήρες: ',\" ή \\";
$star2 = "Στα πεδία με (**) ";

$langEditUser = "Επεξεργασία στοιχείων χρήστη";
$langUnregForbidden = "Δεν επιτρέπεται να διαγράψετε τον χρήστη:";
$langUnregFirst = "Θα πρέπει να διαγράψετε πρώτα τον χρήστη από τα παρακάτω μαθήματα:";
$langUnregTeacher = "Είναι εκπαιδευτής στα παρακάτω μαθήματα:";
$langPlease = "Παρακαλούμε";

$langOtherDepartments = "Εγγραφή σε μαθήματα άλλων τμημάτων/σχολών";
$langNoLessonsAvailable = "Δεν υπάρχουν Διαθέσιμα Μαθήματα.";

// formuser.php
$langUserRequest = "Αίτηση Δημιουργίας Λογαριασμού Εκπαιδευόμενου";
$langUserFillData = "Συμπλήρωση στοιχείων";
$langUserOpenRequests = "Ανοικτές αιτήσεις φοιτητών";
$langWarnReject = "Πρόκειται να απορρίψετε την αίτηση φοιτητή";
$langWithDetails = "με στοιχεία";

$langNewUser = "Στοιχεία Λογαριασμού Χρήστη-Φοιτητή";
$langInfoProfReq = "Αν επιθυμείτε να έχετε πρόβαση στην πλατφόρμα με δικαιώματα χρήστη - καθηγητή, παρακαλώ συμπληρώστε την παρακάτω αίτηση. Η αίτηση θα σταλεί στον υπεύθυνο διαχειριστή ο οποίος θα δημιουργήσει το λογαριασμό και θα σας στείλει τα στοιχεία μέσω ηλεκτρονικού ταχυδρομείου.";
$langInfoStudReg = "Αν επιθυμείτε να έχετε πρόσβαση στην πλατφόρμα με δικαιώματα χρήστη - φοιτητή, παρακαλώ συμπληρώστε τα στοιχεία σας στην παρακάτω φόρμα. Ο λογαριασμός σας θα δημιουργηθεί αυτόματα.";
$langReason = "Αναφέρατε τους λόγους χρήσης της πλατφόρμας";
$langInfoStudReq = "Αν επιθυμείτε να έχετε πρόβαση στην πλατφόρμα με δικαιώματα χρήστη - φοιτητή, παρακαλώ συμπληρώστε την παρακάτω αίτηση. Η αίτηση θα σταλεί στον υπεύθυνο διαχειριστή ο οποίος θα δημιουργήσει το λογαριασμό και θα σας στείλει τα στοιχεία μέσω ηλεκτρονικού ταχυδρομείου.";
$langInfoProf = "Σύντομα θα σας σταλεί mail από την Ομάδα Διαχείρισης της Πλατφόρμας Ασύγχρονης Τηλεκπαίδευσης, με τα στοιχεία του λογαριασμού σας.";
$langDearUser = "Αγαπητέ χρήστη";
$langMailErrorMessage = "Παρουσιάστηκε σφάλμα κατά την αποστολή του μηνύματος - η αίτησή σας καταχωρήθηκε, αλλά δεν στάλθηκε. Παρακαλούμε επικοινωνήστε με το διαχειριστή του συστήματος στη διεύθυνση";

$langUserSuccess = "Νέος λογαριασμός Φοιτητή";
$usersuccess="Η δημιουργία νέου λογαριασμού φοιτητή πραγματοποιήθηκε με επιτυχία!";
$langAsUser = "(Λογαριασμός Φοιτητή)";
$langChooseReg = "Επιλογή τρόπου εγγραφής";
$langTryAgain = "Δοκιμάστε ξανά!";


/************************************************************
* restore_course.inc.php
*************************************************************/

// restore_course.php
$langAdmin = "Εργαλεία Διαχείρισης";
$langRequest1 = "Κάντε κλικ στο Browse για να αναζητήσετε το αντίγραφο ασφαλείας του μαθήματος που θέλετε να επαναφέρετε. Μετά κάντε κλίκ στο 'Αποστολή'. ";
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
$langErrorLang = "Πρόβλημα! Δεν υπάρχουν γλώσσες για το μάθημα!";

/*****************************************************************
* search.inc.php
*****************************************************************/
$langDoSearch = "Εκτέλεση Αναζήτησης";
$langSearch_terms = "Όροι Αναζήτησης: ";
$langSearchIn = "Αναζήτηση σε: ";
$langSearchWith = "Αναζήτηση με κριτήρια:";
$langSearchingFor = "";
$langNoResult = "Δεν βρέθηκαν αποτελέσματα";
$langIntroductionNote = "Εισαγωγικό Σημείωμα";
$langForum = "Περιοχή συζητήσεων";
$langOR = "Τουλάχιστον έναν από τους όρους";
$langNOT = "Κανέναν από τους ακόλουθους όρους";
$langKeywords = "Λέξεις κλειδιά";
$langInstructor = "Καθηγητής/ές";
$langTitle_Descr = "αφορά τον τίτλο ή τμήμα από τον τίτλο του μαθήματος";
$langKeywords_Descr = "κάποια λέξη ή οι λέξεις κλειδιά που προσδιορίζουν τη θεματική περιοχή του μαθήματος";
$langInstructor_Descr = "το όνομα ή τα ονόματα των καθηγητών του μαθήματος";
$langCourseCode_Descr = "ο κωδικός του μαθήματος";
$langAccessType = "Τύπος Πρόσβασης";
$langTypeClosed = "Κλειστό";
$langTypeOpen = "Ανοικτό";
$langTypeRegistration = "Ανοικτό με εγγραφή";
$langAllTypes = "(όλοι οι τύποι πρόσβασης)";
$langAllFacultes = "Σε όλες τις σχολές/τμήματα";

/*****************************************************
* speedsubsribe.inc.php
******************************************************/
$langSpeedSubscribe = "Εγγραφή σαν διαχειριστής μαθήματος";
$langSubscribe = "Εγγραφή";
$langPropositions="Κατάλογος με μελλοντικές προτάσεις ";
$langSuccess = "Η εγγραφή σας σαν διαχειριστής έγινε με επιτυχία";
$lang_subscribe_processing ="Διαδικασία Εγγραφής";
$langAuthRequest = "Απαιτείται εξακρίβωση στοιχείων";
$langAndGoBack ="Επιστροφή";
$langAlreadySubscribe ="Είστε ήδη εγγεγραμμένος";
$langAs = "ως";

/*******************************************************
* stat.inc.php
********************************************************/

 $msgAdminPanel = "Πίνακας Διαχείρισης";
 $msgStats = "Στατιστικά";
 $msgStatsBy = "Στατιστικά σύμφωνα με";
 $msgHours = "ώρες";
 $msgDay = "μέρα";
 $msgWeek = "εβδομάδα";
 $msgMonth = "μήνα";
 $msgYear = "χρόνος";
 $msgFrom = "από ";
 $msgTo = "έως ";
 $msgPreviousDay = "προηγούμενη μέρα";
 $msgNextDay = "επόμενη μέρα";
 $msgPreviousWeek = "προηγούμενη εβδομάδα";
 $msgNextWeek = "επόμενη εβδομάδα";
 $msgCalendar = "ημερολόγιο";
 $msgShowRowLogs = "show row logs";
 $msgRowLogs = "row logs";
 $msgRecords = "εγγραφές";
 $msgDaySort = "Ταξινόμηση σύμφωνα με την ημέρα";
 $msgMonthSort = "Ταξινόμηση σύμφωνα με το μήνα";
 $msgCountrySort = "Ταξινόμηση σύμφωνα με τη χώρα";
 $msgOsSort = "Ταξινόμηση σύμφωνα με το λειτουργικό σύστημα";
 $msgBrowserSort = "Ταξινόμηση σύμφωνα με το Browser";
 $msgProviderSort = "Ταξινόμηση σύμφωνα με το παροχέα υπηρεσιών";
 $msgTotal = "Συνολικά";
 $msgBaseConnectImpossible = "Δεν είναι δυνατή η επιλογή βάσης δεδομένων";
 $msgSqlConnectImpossible = "Δεν είναι δυνατή η σύνδεση με τον εξυπηρέτη SQL";
 $msgSqlQuerryError = "Δεν είναι δυνατό το ερώτημα SQL";
 $msgBaseCreateError = "Παρουσιάστηκε σφάλμα κατά την διάρκεια της δημιουργίας ezboo";
 $msgMonthsArray = array("Ιανουαρίου",
			"Φεβρουαρίου",
			"Μαρτίου",
			"Απριλίου",
			"Μαΐου",
			"Ιουνίου",
			"Ιουλίου",
			"Αυγούστου",
			"Σεπτεμβρίου",
			"Οκτωβρίου",
			"Νοεμβρίου",
			"Δεκεμβρίου");
 $msgDaysArray = array("Κυριακή","Δευτέρα","Τρίτη","Τετάρτη","Πέμπτη","Παρασκευή","Σάββατο");
 $msgDaysShortArray=array("Κ","Δ","Τ","Τ","Π","Π","Σ");
 $msgToday = "Σήμερα";
 $msgOther = "Αλλο";
 $msgUnknown = "Αγνωστο";
 $msgServerInfo = "Πληροφορίες για τον εξυπηρέτη της php";
 $msgStatBy = "Στατιστικά με";
 $msgVersion = "Webstats 1.30";
 $msgCreateCook = "<b>Διαχειριστής:</b> Ένα cookie έχει δημιουργηθεί στον υπολογιστή σας,<BR>
     Δεν θα εμφανίζεστε πλέον στα logs σας.<br><br><br><br>";
 $msgCreateCookError = "<b>Διαχειριστής:</b>το cookie δεν ήταν δυνατόν να αποθηκευθεί στον υπολογιστή σας<br>
     Ελέγξτε τις ρυθμίσεις του browser και ανανεώστε ξανά τη σελίδα.<br><br><br><br>";
 $msgInstalComments = "<p>Η αυτόματη διαδικασία εγκατάστασης θα προσπαθήσει να:</p>
       <ul>
         <li>δημιουργήσει ένα πίνακα που ονομάζεται <b>liste_domaines</b> στην βάση δεδομένων<br>
           </b>Ο πίνακας αυτόματα θα συμπληρωθεί με ονόματα χωρών με βάση τους κωδικούς από το InterNIC</li>
         <li>δημιουργία ενός πίνακα που ονομάζεται <b>logezboo</b><br>
           Αυτός ο πίνακας θα αποθηκεύει τα logs</li>
       </ul>
       <font color=\"#FF3333\">Πρέπει να έχετε τροποποιήσει κατάλληλα το αρχείο<ul><li><b>config_sql.php3</b> με το
<b>όνομα χρήστη</b>, <b>συνθηματικό</b> και τη<b>βάση δεδομένων </b> για τη σύνδεση με τον SQL εξυπηρέτη.</li><br><li>Το αρχείο
<b>config.inc.php3</b>
πρέπει να έχει τροποποιηθεί για την επιλογή κατάλληλης γλώσσας.</font></li></ul><br>Μπορείτε να χρησιμοποιήσετε για αυτόν το σκοπό
οποιοδήποτε επεξεργαστή κειμένου (π.χ. Notepad).";
 $msgInstallAbort = "Εγκατάλειψη του SETUP";
 $msgInstall1 = "Αν δεν υπάρχει μήνυμα λάθους παραπάνω, η εγκατάσταση είναι επιτυχημένη.";
 $msgInstall2 = "Εχουν δημιουργηθεί 2 πίνακες στη βάση δεδομένων";
 $msgInstall3 = "Μπορείτε τώρα να ανοίξετε το κύριο interface";
 $msgInstall4 = "Για να συμπληρώσετε το πίνακά σας όταν οι σελίδες φορτωθούν, πρέπει να τοποθετήσετε μία ετικέτα στις σελίδες που θέλετε να παρακολουθείτε.";

 $msgUpgradeComments ="Η νέα έκδοση του ezBOO WebStats χρησιμοποιεί τον ίδιο πίνακα <b>logezboo</b> όπως οι προηγούμενες εκδόσεις.<br>
  						Αν οι χώρες δεν είναι στα Αγγλικά, πρέπει να διαγράψετε τον πίνακα <b>liste_domaine</b>
  						και να ξεκινήσετε την εγκατάσταση.<br>
  						Αυτό δεν θα έχει αποτέλεσμα στον πίνακα <b>logezboo</b> .<br>
  						Το μήνυμα λάθους ειναι φυσιολογικό. :-)";
$langStats="Στατιστικά";

/*******************************************************************
* toolmanagement.inc.php
********************************************************************/

$langTool = "Εργαλείο";
$langUploadPage = "Ανέβασμα ιστοσελίδας";
$langAddExtLink = "Προσθήκη εξωτερικού σύνδεσμου στο αριστερό μενού";
$deleteSuccess = "Ο σύνδεσμος διαγράφηκε";
$langDeleteLink = "Είστε βέβαιος/η ότι θέλετε να διαγράψετε αυτό τον σύνδεσμο";
$langToolTitle = "Τίτλος";
$langOperations="Ενέργειες σε εξωτερικούς σύνδεσμους";
$langInactiveTools = "Ανενεργά εργαλεία";
$langSubmitChanges = "Υποβολή αλλαγών";


/********************************************************************
* trad4all.inc.php
*********************************************************************/

$langYes = "Ναι";
$langNo = "Όχι";
$iso639_2_code = "el";
$iso639_1_code = "ell";

$langNameOfLang['english']="Αγγλικά";
$langNameOfLang['french']="Γαλλλικά";
$langNameOfLang['greek']="Ελληνικά";

$charset = 'iso-8859-7';
$text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)

$langDay_of_weekNames = array();
$langDay_of_weekNames['init'] = array('Κ', 'Δ', 'Τ', 'Τ', 'Π', 'Π', 'Σ');
$langDay_of_weekNames['short'] = array('Κυρ', 'Δευ', 'Τρι', 'Τετ', 'Πεμ', 'Παρ', 'Σαβ');
$langDay_of_weekNames['long'] = array('Κυριακή', 'Δευτέρα', 'Τρίτη', 'Τετάρτη', 'Πέμπτη', 'Παρασκευή', 'Σάββατο');

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


$dateFormatShort =  "%b %d, %y";
$dateFormatLong  = '%A, %e %B %Y';
$dateTimeFormatLong  = '%d %B %Y / Ώρα: %R';
$timeNoSecFormat = '%R';

$langUser = "Χρήστης:";
$langNoAdminAccess = '
		<p><b>Η σελίδα
		που προσπαθείτε να μπείτε απαιτεί όνομα
		χρήστη και συνθηματικό.</b> <br/>Το σύστημα σας ανακατεύθυνε αυτόματα στην αρχική σελίδα
		για να συνδεθείτε πρωτού προχωρήσετε σε άλλες ενέργειες. Αυτό μπορεί να συνέβηκε
		λόγω εσφαλμένου URL ή λόγω λήξης της συνόδου σας (time-out).</p>
';

$langLoginRequired = '
		<p><b>Δεν είστε εγγεγραμμένος στο μάθημα που προσπαθείτε να μπείτε. </b>
		Το σύστημα σας ανακατεύθυνε αυτόματα στην αρχική σελίδα
		για να εγγραφείτε στο μάθημα, αν η εγγραφή είναι ελεύθερη. </p>
';

$langSessionIsLost = "
		<p><b>Η σύνοδος σας έχει λήξει. </b><br/>Το σύστημα σας ανακατεύθυνε αυτόματα στην αρχική σελίδα
		για να συνδεθείτε πρωτού προχωρήσετε σε άλλες ενέργειες.</p>
			";

$langCheckProf = "
		<p><b>Η ενέργεια που προσπαθήσατε να εκτελέσετε απαιτεί δικαιώματα καθηγητή. </b><br/>
		Το σύστημα σας ανακατεύθυνε αυτόματα στην αρχική σελίδα
		για να συνδεθείτε, εάν είστε ο καθηγητής του εν λόγω μαθήματος.</p>
";

$langLessonDoesNotExist = "
	<p><b>Το μάθημα που προσπαθήσατε να προσπελάσετε δεν υπάρχει.</b><br/>
	Αυτό μπορεί να συμβαίνει λόγω του ότι εκτελέσατε μια μη επιτρεπτή ενέργεια ή λόγω προβλήματος
	στην πλατφόρμα.</p>
";

$langCheckAdmin = "
		<p><b>Η ενέργεια που προσπαθήσατε να εκτελέσετε απαιτεί δικαιώματα διαχειριστή. </b><br/>
		Το σύστημα σας ανακατεύθυνε αυτόματα στην αρχική σελίδα
		για να συνδεθείτε, εάν είστε ο διαχειριστής της πλατφόρμας.</p>
";

$langCheckGuest = "
		<p><b>Η ενέργεια που προσπαθήσατε να εκτελέσετε δεν είναι δυνατή με δικαιώματα επισκέπτη χρήστη. </b><br/>
		Για λόγους ασφάλειας το σύστημα σας ανακατεύθυνε αυτόματα στην αρχική σελίδα
		για να συνδεθείτε ξανά.</p>
";

$langCheckPublicTools = "
		<p><b>Προσπαθήσατε να αποκτήσετε πρόσβαση σε απενεργοποιημένο εργαλείο μαθήματος. </b><br/>
		Για λόγους ασφάλειας το σύστημα σας ανακατεύθυνε αυτόματα στην αρχική σελίδα
		για να συνδεθείτε ξανά.</p>
";

$langUserBriefcase = "Χαρτοφυλάκιο χρήστη";
$langPersonalisedBriefcase = "Προσωπικό χαρτοφυλάκιο";
$langEclass = "Πλατφόρμα ασύγχρονης τηλεκπαίδευσης eClass";
$langCopyrightFooter="Πληροφορίες πνευματικών δικαιωμάτων";
$langAdvancedSearch="Σύνθετη αναζήτηση";


/***************************************************************
* unreguser.inc.php
****************************************************************/
$langBackHome = "Επιστροφή στην αρχική σελίδα";
$langAdminNo = "Ο λογαριασμός του διαχειριστή της πλατφόρμας δεν μπορεί να διαγραφεί!";
$langConfirm = "Θέλετε σίγουρα να διαγραφείτε από την πλατφόρμα?";
$langExplain = "Για να διαγραφείτε από την πλατφόρμα, πρέπει πρώτα να απεγγραφείτε από τα μαθήματα που είστε εγγεγραμμένος.";
$langConfirm = "Επιβεβαίωση διαγραφής λογαριασμού";
$langDelSuccess = "Ο λογαριασμός σας στην πλατφόρμα έχει διαγραφεί.";
$langThanks = "Ευχαριστούμε για τη χρήση της πλατφόρμας!";
$langError = "Σφάλμα κατά τη διαγραφή του χρήστη!";
$langNotice = "Σημείωση";
$langModifProfile="Αλλαγή του προφίλ μου";

//unregcours.php
$langUnregCours = "Απεγγραφή από μάθημα";
$langCoursDelSuccess = "Η απεγγραφή σας από το μάθημα έγινε με επιτυχία";
$langCoursError = "Σφάλμα κατά την απεγγραφή του χρήστη";
$langConfirmUnregCours = "Θέλετε σίγουρα να απεγγραφείτε από το μάθημα με κωδικό";

/*******************************************************************
* usage.inc.php
********************************************************************/

 $langGDRequired = "Απαιτείται η βιβιλιοθήκη GD!";
 $langPersonalStats="Τα στατιστικά μου";
 $langUserLogins="Επισκέψεις χρηστών στο μάθημα";
 $langStartDate = "Ημερομηνία Έναρξης";
 $langEndDate = "Ημερομηνία Λήξης";
 $langUser = "Χρήστης";
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
$langFavourite = "Προτίμηση Υποσυστημάτων";
 $langFavouriteExpl = "Παρουσιάζεται η προτίμηση ενός χρήστη ή όλων των χρηστών στα υποσυστήματα μέσα σε ένα χρονικό διάστημα.";
 $langOldStats = "Εμφάνιση παλιών στατιστικών";
 $langOldStatsExpl = "Παρουσιάζονται συγκεντρωτικά μηνιαία στατιστικά στοιχεία παλιότερα των οκτώ μηνών.";
 $langOldStatsLoginsExpl = "Παρουσιάζονται συγκεντρωτικά μηνιαία στατιστικά σχετικά με τις εισόδους στην πλατφόρμα παλιότερα των οκτώ μηνών.";
 $langInterval = "Διάστημα";
 $langDaily = "Ημερήσιο";
 $langWeekly = "Εβδομαδιαίο";
 $langMonthly = "Μηνιαίο";
 $langYearly = "Ετήσιο";
 $langSummary = "Συγκεντρωτικά";
 $langDurationVisits = "Χρονική Διάρκεια Επισκέψεων";
 $langDurationExpl = "Η χρονική διάρκεια των επισκέψεων σε κάθε υποσύστημα υπολογίζεται κατά προσέγγιση.";
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
 $langMonths['01'] = "Ιανουάριο";
 $langMonths['02'] = "Φεβρουάριο";
 $langMonths['03'] = "Μάρτιο";
 $langMonths['04'] = "Απρίλιο";
 $langMonths['05'] = "Μάιο";
 $langMonths['06'] = "Ιούνιο";
 $langMonths['07'] = "Ιούλιο";
 $langMonths['08'] = "Αύγουστο";
 $langMonths['09'] = "Σεπτέμβριο";
 $langMonths['10'] = "Οκτώβριο";
 $langMonths['11'] = "Νοέμβριο";
 $langMonths['12'] = "Δεκέμβριο";
 $langPre = "Προπτυχιακό";
 $langPost = "Μεταπτυχιακό";
 $langHidden = "Κλειστό";
 $langVis_enrol = "Ανοικτό με εγγραφή";
 $langVisible = "Ανοικτό";

 $langPres = "Προπτυχιακά";
 $langPosts = "Μεταπτυχιακά";
 $langHiddens = "Κλειστά";
 $langVis_enrols = "Ανοικτά με εγγραφή";
 $langVisibles = "Ανοικτά";

 $langAddress = "Διεύθυνση";
 $langLoginDate = "Ημερ/νία εισόδου";
 $langNoLogins = "Δεν έχουν γίνει είσοδοι το συγκεκριμένο χρονικό διάστημα.";
 $langStatAccueil = "Για το χρονικό διάστημα που ζητήθηκε, διατίθεται και η παρακάτω πληροφορία, για το σύνολο των χρηστών του μαθηματος:";
 $langHost = "Host";

 #for platform Statistics
 $langUsersCourse = "Χρήστες ανά μάθημα";
 $langVisitsCourseStats = "Επισκέψεις σε σελίδες μαθημάτων";
 $langUserStats = "Στατιστικά Χρήστη";
 $langTotalVisitsCourses = "Συνολικές επισκέψεις σε σελίδες μαθημάτων";


/****************************************************************
* video.inc.php
*****************************************************************/

// video
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

// videolinks
$langVideoAdd = "Ο σύνδεσμος προστέθηκε";
$langVideoDel = "Ο σύνδεσμος διαγράφτηκε";
$langVideoMod = "Τα στοιχεία του συνδέσμου τροποποιήθηκαν";
$langVideoDeleted = "Όλοι οι σύνδεσμοι διαγράφτηκαν";
$langURL="Εξωτερικός σύνδεσμος προς τον εξυπηρετητή ήχου ή βίντεο";
$langcreator="Δημιουργός";
$langpublisher="Εκδότης";
$langdate="Ημερομηνία";
$langAreYouSureToDelete = "Επιβεβαίωση διαγραφής";

/*************************************************************
* wiki.inc.php
**************************************************************/

$langAddImage = "Συμπεριέλαβε εικόνα";
$langAdministrator = "Διαχειριστής";
$langChangePwdexp = "Βάλτε δυο φορές νεο κωδικό (password) για να γίνει αλλαγή, αφήστε κενό για να κρατήσετε τον ίδιο";
$langChooseYourPassword = " Επέλεξε τώρα ένα όνομα χρήστη και έναν κωδικό πρόσβασης για το λογαριασμό χρήστη. ";
$langCloseWindow = "Κλείστε το παράθυρο";
$langCodeUsed = "Αυτός ο επίσημος κωδικός χρησιμοποιείται ήδη από άλλο χρήστη.";
$langContinue = " Συνέχεια ";
$langCourseManager = "Διαχειριστής μαθήματος";
$langDelImage = "Διαγραφή εικόνας";
$langFirstname = "Όνομα";
$langGroups = "Ομάδες Χρηστών";
$langIs = "είναι";
$langLastname = "Επώνυμο";
$langLegendRequiredFields = "<span class=\"required\">*</span> δείχνει απαραίτητο πεδίο ";
$langManager = "Διαχειριστής";
$langMemorizeYourPassord = "Αποστήθισε τα, θα τα χρειαστείς την επόμενη φορά που θα μπεις σε αυτή τη σελίδα.";
$langModifyProfile = "Αλλαγή του προφίλ μου";
$langNameOfLang = "Διάταξη";
$langOfficialCode = "Κωδικός διαχείρισης";
$langOneResp = "Ενας από τους διαχειριστές του μαθήματος";
$langOtherCourses = "Λίστα Μαθημάτων";
$langPersonalCourseList = "Προσωπική λίστα μαθήματος";
$langPreview = "Παρουσίαση/προβολή";
$langSaveChanges = "Αποθήκευση αλλαγών";
$langSettings = "δημιουργήθηκε με επιτυχία!
 Τα προσωπικά στοιχεία του λογαριασμού σας είναι τα εξής:

Όνομα χρήστη:";
$langTheSystemIsCaseSensitive = "(γίνεται διάκριση μεταξύ κεφαλαίων και πεζών γραμμάτων.)";
$langUpdateImage = "Αλλαγή εικόνας";
$langUserIsPlaformAdmin = "είναι διαχειριστής της πλατφόρμας ";
$langUserid = " Ταυτότητα χρήστη";
$langWarning = " Προειδοποίηση.";
$langWikiAccessControl = " Διαχείριση ελέγχου πρόσβασης ";
$langWikiAccessControlText = " Μπορείτε να θέσετε τα δικαιώματα πρόσβασης για τους χρήστες χρησιμοποιώντας το ακόλουθο πλέγμα: ";
$langWikiAllPages = " Όλες οι σελίδες ";
$langWikiBackToPage = " Πίσω στη σελίδα ";
$langWikiConflictHowTo = "<p><strong>Αλλάξτε τη σύγκρουση</strong> : Η σελίδα που πρσπαθελις φαίνετε ότι έχει αλλάξεια απο το καιρό που την άλλαξες.<br /><br />
Τι θές να κάνεις τώρα;<ul>
<li>Μπορείς να αντιγράψεις/επικολλήσεις τις αλλαγές σου σε ένα κειμενογράφο (όπως το notepad) και κάνε κλίκ στο  'edit last version' για να προσπαθήσεις να προσθέσεις τις αλλαγές σου στην καινούργια έκδοση της σελίδας.</li>
<li>Μπορείς επίσης να πατήσεις στο άκυρο για να ακυρώσεις τις αλλαγές σου.</li>
</ul></p>";
$langWikiContentEmpty = " Αυτή η σελίδα είναι κενή, κάνε κλικ στο 'Edit this page' για να προσθεσεις περιεχομενο";
$langWikiCourseMembers = " Μέλη μαθήματος ";
$langWikiCreateNewWiki = " Δημιουργήστε ένα νέο Wiki";
$langWikiCreatePrivilege = " Δημιουργήστε σελίδες ";
$langWikiCreationSucceed = "Η δημιουργία του Wiki ήταν επιτυχημένη";
$langWikiDefaultDescription = " Εισάγετε την περιγραφή του νέου σας wiki έδω";
$langWikiDefaultTitle = "Καινούργιο Wiki";
$langWikiDeleteWiki = "Διαγραφή Wiki";
$langWikiDeleteWikiWarning = " ΠΡΟΕΙΔΟΠΟΙΗΣΗ : πρόκειται να διαγράψετε αυτό το wiki και όλες τις σελίδες του. Είστε βέβαιοι ότι θέλετε να συνεχίσετε;";
$langWikiDeletionSucceed = "Η διαγραφή του Wiki ήταν επιτυχημένη";
$langWikiDescription = "Περιγραφή του Wiki";
$langWikiDescriptionForm = "Περιγραφή Wiki";
$langWikiDescriptionFormText = " Μπορείτε να επιλέξετε έναν τίτλο για το wiki : ";
$langWikiDiffAddedLine = " Προστιθέμενη γραμμή ";
$langWikiDiffDeletedLine = " Διαγραμμένη γραμμή ";
$langWikiDiffMovedLine = " Μετακινημένη γραμμή ";
$langWikiDiffUnchangedLine = " Αμετάβλητη γραμμή ";
$langWikiDifferenceKeys = " Κλειδιά :";
$langWikiDifferencePattern = " διαφορές μεταξύ της έκδοσης %1\$s τροποποιημένης από %2\$s και της έκδοσης %3\$s τροποποιημένης απο %4\$s";
$langWikiDifferenceTitle = " Διαφορές :";
$langWikiEditConflict = "Αλλαγή σύγκρουσης";
$langWikiEditLastVersion = "Αλλαγή τελευταίας έκδοσης";
$langWikiEditPage = " Αλλαγή αυτής της σελίδας";
$langWikiEditPrivilege = " Αλλαγή σελίδων";
$langWikiEditProperties = " Αλλαγή ιδιοτήτων";
$langWikiEditionSucceed = " Η εκδοση Wiki είναι επιτυχημένη";
$langWikiGroupMembers = "Μέλη ομάδας";
$langWikiHelpAdminContent = "<h3>Βοήθεια διαχείρισης Wiki</h3>
<dl class=\"Βοήθεια wiki\">
<dt> Πώς να δημιουργήσετε έναν νέο Wiki ?</dt>
<dd> Κάντε κλίκ στη σύνδεση 'Create a new Wiki'. Μετά εισαγετε τις ιδιότητες του Wiki :
<ul>
<li><b> Τίτλος του Wiki</b> : επιλέξτε έναν τίτλο για το Wiki</li>
<li><b> Περιγραφή του  Wiki</b> : επιλέξτε μια περιγραφή για το Wiki</li>
<li><b> Διαχείριση ελέγχου πρόσβασης </b> : θέστε τον έλεγχο πρόσβασης για τον Wiki επιλέγοντας/αποεπιλέγοντας το κουτί (δείτε πιο κάτω)</li>
</ul>
</dd>
<dt> Πώς να εισαγάγετε το Wiki ?</dt>
<dd> Κάντε κλικ στον τίτλο του Wiki στον κατάλογο.</dd>
<dt> Πώς να αλλάξετε τις ιδιότητες του Wiki ?</dt>
<dd>Κάντε κλικ στο εικονίδιο 'Properties' στην λίστα του Wiki και πήγαινε στη φόρμα ιδιοτήτων του Wiki.</dd>
<dt> Πώς να χρησιμοποιήσει τις διοικητικές επιλογές ελέγχου πρόσβασης;</dt>
<dd> Μπορείτε να θέσετε τα δικαιώματα πρόσβασης για τους χρήστες με τον επιλογή/αποεπιλογή του κουτιού στο \"διοικητικό\" τμήμα ελέγχου πρόσβασης των ιδιοτήτων Wiki.
 Μπορείτε να χορηγήσετε/μη χορηγήσετε πρόσβαση σε τρεις τύπους χρηστών:<ul>
<li><b> Μέλη μαθημάτων </b> : οι χρήστες εγγράφονται στη σειρά μαθημάτων (εκτός από τους διευθυντές μαθημάτων)</li>
<li><b> Μέλη ομάδας </b> (μόνο διαθέσιμο μεσα σε  μια ομάδα) : χρήστες που είναι μέλη της ομάδας (αναμείνετε τους δασκάλους ομάδας s)</li>
<li><b>Αλλοι χρήστες </b> : ανώνυμοι χρήστες ή χρήστες που δεν είναι μέλη σειράς μαθημάτων </li></ul>
Για κάθε τύπο χρηστών, μπορείτε να χορηγήσετε τον τύπο τρίων προνομίων για το Wiki(*) :<ul>
<li><b> Διαβάστε τις σελίδες </b> : ο χρήστης του δεδομένου τύπου μπορεί να διαβάσει τις σελίδες του Wiki</li>
<li><b>Αλλαγή σελίδων</b> : ο χρήστης του δεδομένου τύπου μπορεί να τροποποιήσει το περιεχόμενο των σελίδων του Wiki</li>
<li><b> Δημιουργήστε τις σελίδες </b> : ο χρήστης του δεδομένου τύπου μπορεί να δημιουργήσει νέες σελίδες του Wiki</li>
</ul><small><em>(*) Σημειώστε ότι εάν ένας χρήστης δεν μπορεί να διαβάσει τις σελίδες του  Wiki, δεν μπορεί να τις αλλάξει ή να τις τροποποιήσει. Σημειώστε ότι εάν ένας χρήστης δεν μπορεί να αλλαξει τις σελίδες του Wiki, δεν μπορεί να δημιουργήσει νέες σελίδες.</em></small></dd>
<dt> Πώς να διαγράψει το Wiki ?</dt>
<dd>Κάντε κλικ στο εικονίδιο 'Delete' στη στήλη για να σβήσετε το Wiki και όλες του τις σελίδες.</dd>
<dt> Πώς να πάρετε τον κατάλογο των σελίδων σε ένα Wiki ;</dt>
<dd>Κάντε κλικ στον αριθμό των σελίδως σε αυτό το Wiki στην λίστα των Wiki.</dd>
<dt> Πώς να πάρετε τον κατάλογο των  τελευταίων τροποποιημένων σελίδων σε ένα Wiki;</dt>
<dd>Κάντε κλικ στο εικονίδιο 'Recent changes' στη στήλη του καταλόγου του Wiki.</dd>
</dl>";
$langWikiHelpSyntax = "Σύνταξη του Wiki ";
$langWikiHelpSyntaxContent = "<h1>Σύνταξη Wiki </h1>
<h2>1. Βασική σύνταξη </h2>
<dl class=\"Βοήθεια wiki\">
<dt> Δημιουργία των σελίδων και των συνδέσεων wiki μεταξύ τους </dt>
<dd><strong>Λέξεις Wiki </strong> : Οι λέξεις Wiki είναι λέξεις που γράφονται όπως <em>Λέξη Wiki</em>. Τα Wiki2xhtml τους αναγνωρίζουν ατόματα ως συνδέσεις σελίδων Wiki. Για να δημιουργήσετε μια σελίδα wiki ή για να δημιουργήσετε μια σύνδεση με μια σελίδα wiki, τροποποιήστε μια ήδη υπάρχουσα και προσθέστε το τίτλο στην σύνταξη του wiki, για παράδειγμα <em>Η σελίδα μου</em>, και μετά φύλαξε τη σελίδα. Wiki2xhtml θα αντικαταστήσει αυτόματα την λέξη<em>Η σελίδα μου</em> με μια σύνδεση με τη σελίδα Wiki <em>Η σελίδα μου</em>&nbsp;;</dd>
<dd><strong> συνδέσεις  Wiki </strong> : Οι συνδέσεις Wiki είναι όπως τους συνδέσμους υπερ-κειμένου (βλ. κατωτέρω) αναμένουν ότι δεν περιέχουν οποιοδήποτε σχέδιο πρωτοκόλλου (όπως <em>http://</em> ή <em>ftp://</em>) και ότι αυτόματα αναγνωρίζουν συνδέσμους σε σελίδες  Wiki. Για να δημιουργήσετε μια νέα σελίδα ή να δημιουργήσετε μια σύνδεση με μια υπάρχουσα που χρησιμοποιεί τις συνδέσεις Wiki, αλλαξτε μια σελίδα και προσθέστε <code>[page title]</code> η <code>[name of link|title of page]</code> στα περιεχόμενα του. Μπορείτε επίσης να χρησιμοποιήσετε αυτήν την σύνταξη για να αλλάξετε το κείμενο μιας σύνδεσης WikiWord: <code>[όνομα συνδέσμου|WikiWord]</code>.</dd>
<dt> Σύνδεσμοι υπερ-κειμένου </dt>
<dd><code>[url]</code>, <code>[name|url]</code>, <code>[name|url|language]</code> or <code>[name|url|language|title]</code>.&nbsp;;</dd>
<dt> Συνυπολογισμός εικόνας </dt>
<dd><code>((url|alternate text))</code>, <code>((url| εναλλάσσομενο κείμενο |position))</code> ou <code>((url|alternate text|position|long description))</code>. <br /> Το επιχείρημα θέσης μπορεί να πάρει τις ακόλουθες τιμές : L (αριστερά), R (δεξιά) or C (κεντρικά).&nbsp;;</dd>
<dd> Μπορείτε να χρησιμοποιήσετε τη σύνταξη ως συνδέσμους υπερ-κειμένου. Παραδείγματος χάριν <code>[τίτλος|image.gif]</code>. Αυτή η σύνταξη είναι αποδοκιμασμένη, σκεφτήτε να χρησιμποιήσετε την προηγούμενη&nbsp;;</dd>
<dt> Σύνδεση με μια εικόνα </dt>
<dd> όπως τους συνδέσμους υπερ-κειμένου αλλά τεθειμένο 0 στο τέταρτο επιχείρημα για να αποφευχθεί η αναγνώριση εικόνας και να φταθεί ένας σύνδεσμος υπερ-κειμένου σε μια εικόνα. Παραδείγματος χάριν <code>[image|image.gif||0]</code> θα επιδείξει μια σύνδεση με την image.gif iαντι για επίδειξη της ίδιας της φωτογραφίας</dd>
<dt> Σχεδιάγραμμα </dt>
<dd><strong> Κυρτός </strong> : περιβάλτε το κείμενό σας με δύο ενιαία αποσπάσματα <code>'' κείμενο ''</code>&nbsp;;</dd>
<dd><strong>Εντονα</strong> : περιβάλτε το κείμενό σας με τρία ενιαία αποσπάσματα υπογραμμίζει <code>''' κείμενο '''</code>&nbsp;;</dd>
<dd><strong>Υπογράμμιση</strong> : περιβάλτε το κείμενό σας με δύο υπογραμμίζει <code>__ κείμενο __</code>&nbsp;;</dd>
<dd><strong> Γραμμή</strong> : περιβάλτε το κείμενό σας με δύο αρνητικά σύμβολα <code>-- κείμενο --</code>&nbsp;;</dd>
<dd><strong> Τίτλος </strong> : <code>!!!</code>, <code>!!</code>, <code>!</code> αντίστοιχα για τους τίτλους, τους υποτίτλους και τους υπο-υπο-τίτλους &nbsp;;</dd>
<dt> Κατάλογος </dt>
<dd> γραμμή αρχίζοντας από <code>*</code> (άδιάτακτος κατάλογος) ή <code>#</code> (διαταγμένος κατάλογος). Μπορείτε να αναμίξετε τους καταλόγους (<code>*#*</code>) για να δημιουργήθούν πολυ - κατάλογοι επιπέδων.&nbsp;;</dd>
<dt> Παράγραφος </dt>
<dd> Χωριστές παράγραφοι με μια ή περισσότερες νέες γραμμές &nbsp;;</dd>
</dl>
<h2>2. Προχωρημένη σύνταξη </h2>
<dl class=\"Βοήθεια wiki\">
<dt> Υποσημείωση </dt>
<dd><code>\$\$ κείμενο υποσημειώσεων \$\$</code>&nbsp;;</dd>
<dt>προκαθοριμένο κείμενο </dt>
<dd> αρχίστε κάθε γραμμή του κείμενο με ένα κενό διάστημα &nbsp;;</dd>
<dt> Αναφέρετε φραγμού </dt>
<dd><code>&gt;</code> ή <code>;:</code> πριν από κάθε γραμμή &nbsp;;</dd>
<dt> Οριζόντια γραμμή </dt>
<dd><code>----</code>&nbsp;;</dd>
<dt> Αναγκασμένο σπάσιμο γραμμών </dt>
<dd><code>%%%</code>&nbsp;;</dd>
<dt>ακρώνυμο</dt>
<dd><code>??ακρώνυμο??</code> or <code>??ακρώνυμο|ορισμός??</code>&nbsp;;</dd>
<dt>Ευθυγραμμισμένη αναφορά </dt>
<dd><code>{{αναφορα}}</code>, <code>{{αναφορά|γλώσσα}}</code> or <code>{{αναφορά|γλώσσα|url}}</code>&nbsp;;</dd>
<dt>Κώδικας</dt>
<dd><code>@@Ο κωδικας σου εδώ@@</code>&nbsp;;</dd>
<dt>Ονομα στηρίγματος</dt>
<dd><code>~στήριγμα~</code>&nbsp;;</dd>
</dl>";
$langWikiIdenticalContent = " Ίδιο περιεχόμενο <br />καμιά αλλαγή δεν αποθηκεύτηκε";
$langWikiInvalidWikiId = "Μη έγκυρο Wiki Id";
$langWikiList = "Λίστα του Wiki";
$langWikiMainPage = "Κύρια σελίδα";
$langWikiMainPageContent = "Αυτη είναι η κύρια σελίδα του Wiki %s. Επέλεξε '''Edit''' για να τροποποιήσεις το περιεχόμενο.";
$langWikiNoWiki = "Κανένα Wiki";
$langWikiNotAllowedToCreate = " Δεν επιτρέπεται να δημιουργήσεις σελίδα";
$langWikiNotAllowedToEdit = " Δεν επιτρέπεται να αλλάξεις αυτή τη σελίδα";
$langWikiNotAllowedToRead = "Δεν επιτρέπεται να διαβάσεις αυτή τη σελίδα";
$langWikiNumberOfPages = "Αριθμός σελίδων";
$langWikiOtherUsers = "Άλλοι χρήστες (*)";
$langWikiOtherUsersText = "(*) ανώνυμοι χρήστες και χρήστες που δεν είναι μέλη αυτού του μαθήματος...";
$langWikiPageHistory = "Ιστορικό σελίδας";
$langWikiPageSaved = "Η σελίδα αποθηκεύτηκε";
$langWikiPreviewTitle = "Προεπισκόπηση : ";
$langWikiPreviewWarning = " ΠΡΟΕΙΔΟΠΟΙΗΣΗ: αυτή η σελίδα αποτελεί προεπισκόπηση.  Οι τροποποιήσεις σας στο wiki δεν έχουν αποθηκευτεί ακόμα ! Για να τις αποθηκεύσετε μη ξεχάσετε να κάνετε κλικ στο κουμπί 'save' στο τέλος της σελίδας.";
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



/*************************************************************
* work.inc.php
**************************************************************/

$langBackAssignment = "Επιστροφή στη σελίδα της εργασίας";

$m['activate'] = "Ενεργοποίηση";
$m['deactivate'] = "Απενεργοποίηση";
$m['deadline'] = "Προθεσμία υποβολής";
$m['username'] = "Όνομα φοιτητή";
$m['filename'] = "Όνομα αρχείου";
$m['sub_date'] = "Ημερομηνία αποστολής";
$m['comments'] = "Σχόλια";
$m['gradecomments'] = "Σχόλια βαθμολογητή";
$m['addgradecomments'] = "Προσθήκη σχολίων βαθμολογητή";
$m['delete'] = "Διαγραφή";
$m['edit'] = "Αλλαγή";
$m['start_date'] = "Ημερομηνία έναρξης";
$m['grade'] = "Βαθμός";
$m['am'] = "Αριθμός μητρώου";
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
$m['deleted_work_by_user'] = "Διαγράφτηκε η προηγούμενη υποβληθείσα
	εργασία που είχατε στείλει στο αρχείο";
$m['deleted_work_by_group'] = "Διαγράφτηκε η προηγούμενη εργασία που
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
$m['noguest'] = 'Για να αποστείλετε εργασία πρέπει να συνδεθείτε
	ως κανονικός χρήστης.';
$m['one_submission'] = 'Έχει υποβληθεί μία εργασία';
$m['more_submissions'] = 'Έχουν υποβληθεί %d εργασίες';
$m['plainview'] = 'Συνοπτική λίστα εργασιών - βαθμολογίας';

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

$langEditSuccess = "Η διόρθωση των στοιχείων της εργασίας έγινε με επιτυχία!";
$langEditError = "Παρουσιάστηκε πρόβλημα κατά την διόρθωση των στοιχείων !";
$langNewAssign = "Δημιουργία Εργασίας";
$langDeleted = "Η εργασία διαγράφηκε";
$langDelAssign = "Διαγραφή Εργασίας";
$langDelWarn1 = "Πρόκειται να διαγράψετε την εργασία με τίτλο";
$langDelSure = "Είστε σίγουρος;";
$langWorkFile = "Αρχείο";
$langZipDownload = "Κατέβασμα όλων των εργασιών σε αρχείο .zip";

$langDelWarn2 = "Έχει αποσταλεί μία εργασία φοιτητή. Το αρχείο αυτό θα διαγραφεί!";
$langDelTitle = "Προσοχή!";
$langDelMany1 = "Έχουν αποσταλεί";
$langDelMany2 = "εργασίες φοιτητών. Τα αρχεία αυτά θα διαγραφούν!";
$langSubmissions = "Εργασίες φοιτητών που έχουν υποβληθεί";
$langSubmitted = "Η εργασία αυτή έχει ήδη υποβληθεί.";
$langNotice2 = "Ημερομηνία αποστολής";
$langNotice3 = "Αν στείλετε κάποιο άλλο αρχείο, το αρχείο που υπάρχει
	αυτή τη στιγμή θα σβηστεί και θα αντικατασταθεί με το νέο.";
$langSubmittedAndGraded = "Η εργασία αυτή έχει ήδη υποβληθεί και βαθμολογηθεί.";
$langSubmissionDescr = "Ο φοιτητής %s, στις %s, έστειλε το αρχείο με όνομα \"%s\".";
$langEndDeadline = "(η προθεσμία έχει λήξει)";
$langWEndDeadline = "(η προθεσμία λήγει αύριο)";
$langNEndDeadLine = "(η προθεσμία λήγει σήμερα)";
$langDays = "ημέρες)";
$langDaysLeft = "(απομένουν";
$langGrades = "H βαθμολογία σας κατοχυρώθηκε με επιτυχία";
$langUpload = "Το ανέβασμα της εργασίας σας ολοκληρώθηκε με επιτυχία !";
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

?>
