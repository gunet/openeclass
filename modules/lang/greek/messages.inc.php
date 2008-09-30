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

$langIntro = "Η πλατφόρμα <b>$siteName</b> είναι ένα ολοκληρωμένο Σύστημα Διαχείρισης Ηλεκτρονικών Μαθημάτων και υποστηρίζει την �?πηρεσία Ασύγχρονης �?ηλεκπαίδευσης στο <a href=\"$InstitutionUrl\" target=\"_blank\" class=mainpage>$Institution</a>.";
$langVersion="Έκδοση του $siteName";
$langAboutText="Η έκδοση της πλατφόρμας είναι";
$langEclassVersion="2.1";
$langHostName="Ο υπολογιστ�?ς στον οποίο βρίσκεται η πλατφόρμα είναι ο ";
$langWebVersion="Xρησιμοποιεί ";
$langMySqlVersion="και MySql ";
$langNoMysql="Η MySql δεν λειτουργεί !";
$langUptime = "Λειτουργεί από τις";
$langTotalHits = "Συνολικές προσβάσεις";
$langLast30daysLogins = "Συνολικές προσβάσεις στην πλατφόρμα τις τελευταίες 30 μέρες";
$langTotalCourses = "Αριθμός μαθημάτων";
$langInfo = "�?αυτότητα Πλατφόρμας";
$langAboutCourses = "Η πλατφόρμα υποστηρίζει συνολικά";
$langAboutUsers = "H πλατφόρμα διαθέτει";


#For the logged-out user:
$langAboutCourses1 = "Αυτ�? τη στιγμ�?, η πλατφόρμα διαθέτει συνολικά";
$langAboutUsers1 = "Οι εγγεγραμένοι χρ�?στες είναι ";
$langLast30daysLogins1 = "και οι συνολικές προσβάσεις στην πλατφόρμα τις τελευταίες 30 μέρες είναι ";
$langAnd = "και";
$langCourses = "μαθ�?ματα";
$langClosed = "κλειστά";
$langOpen = "ανοικτά";
$langSemiopen = "απαιτούν εγγραφ�?";
$langUsers = "Χρ�?στες";
$langUsersS = "χρ�?στες";
$langUser = "Χρ�?στης";
$langUserS = "χρ�?στης";
$langSupportUser = "�?πεύθυνος �?ποστ�?ριξης:";
$langAdminUsers = "Διαχείριση Χρηστών";


/********************************************
* addadmin.inc.php
*********************************************/
$langDeleteAdmin="H διαγραφ�? του διαχειριστ�? με id:";
$langNotFeasible ="δεν είναι εφικτ�?";
$langNomPageAddHtPass = "Προσθ�?κη διαχειριστ�?";
$langPassword = "Συνθηματικό";
$langAdd = "Προσθ�?κη";
$langNotFound = "δεν βρέθηκε";
$langWith = "με";
$langDone = "έγινε διαχειριστ�?ς.";
$langErrorAddaAdmin = "Σφάλμα: ο χρ�?στης δεν προστέθηκε στους διαχειριστές. Πιθανόν να είναι �?δη διαχειριστ�?ς.";
$langInsertUserInfo = "Εισαγωγ�? στοιχείων χρ�?στη";
$langPage="Σελίδα:";
$langBeforePage="Προηγούμενη";
$langAfterPage="Eπόμενη";
/****************************************************
* admin.inc.php
****************************************************/
// index
$langComments = "Σχόλια";
$langAdmin = "Εργαλεία Διαχείρισης Πλατφόρμας";
$langState = "Διαχείριση Εξυπηρετητ�?";
$langDevAdmin ="Διαχείριση Βάσης Δεδομένων";
$langNomPageAdmin 	= "Διαχείριση";
$langSysInfo  	= "Πληροφορίες Συστ�?ματος";
$langCheckDatabase  	= "Ελεγχος κύριας βάσης δεδομένων";
$langStatOf 	= "Στατιστικά του ";
$langSpeeSubscribe 	= "Εγγραφ�? σαν Διαχειριστ�?ς Μαθ�?ματος";
$langLogIdentLogout 	= "Καταγραφ�? των εισόδων και εξόδων από το σύστημα";
$langPlatformStats 	= "Στατιστικά Πλατφόρμας";
$langPlatformGenStats   = "Γενικά στατιστικά";
$langVisitsStats        = "Στατιστικά επισκέψεων";
$langMonthlyReport      = "Μηνιαίες αναφορές";
$langReport             = "Αναφορά για το μ�?να";
$langNoReport           = "Δεν υπάρχουν διαθέσιμα στοιχεία για το μ�?να";
$langEmailNotSend = "Σφάλμα κατά την αποστολ�? e-mail στη διεύθυνση";
$langFound = "Βρέθηκαν";
$langFoundIt = "βρέθηκε";
$langListCours = "Λίστα Μαθημάτων / Ενέργειες";
$langListUsersActions = "Λίστα Χρηστών / Ενέργειες";
$langSearchUser = "Αναζ�?τηση Χρ�?στη";
$langInfoMail = "Ενημερωτικό email";
$langProfReg = "Εγγραφ�? $langOfTeacher";
$langProfOpen = "Αιτ�?σεις $langOfTeachers";
$langUserOpen = "Αιτ�?σεις $langOfStudents";
$langPHPInfo = "Πληροφορίες για την PHP";
$langManuals = "Διαθέσιμα Εγχειρίδια";
$langFormatPDF = "Μορφ�? PDF";
$langFormatHTML = "Μορφ�? HTML";
$langAdminManual = "Εγχειρίδιο Διαχειριστ�?";
$langConfigFile = "Αρχείο ρυθμίσεων";
$langDBaseAdmin = "Διαχείριση Β.Δ. (phpMyAdmin)";
$langActions = "Ενέργειες";
$langAdminProf = "Διαχείριση $langOfTeachers";
$langAdminCours = "Διαχείριση Μαθημάτων";
$langGenAdmin="Άλλα Εργαλεία";
$langBackAdmin = "Επιστροφ�? στη σελίδα διαχείρισης";
$langPlatformIdentity = "�?αυτότητα Πλατφόρμας";
$langStoixeia = "Στοιχεία Πλατφόρμας";
$langThereAre = "�?πάρχουν";
$langThereIs = "�?πάρχει";
$langOpenRequests = "Ανοικτές αιτ�?σεις ".$langsOfTeachers;
$langNoOpenRequests = "Δεν βρέθηκαν ανοικτές αιτ�?σεις ".$langsOfTeachers;
$langInfoAdmin  = "Ενημερωτικά Στοιχεία για τον Διαχειριστ�?";
$langLastLesson = "�?ελευταίο μάθημα που δημιουργ�?θηκε:";
$langLastProf = "�?ελευταία εγγραφ�? ".$langsOfTeacher.":";
$langLastStud = "�?ελευταία εγγραφ�? ".$langsOfStudent.":";
$langAfterLastLogin = "Μετά την τελευταία σας είσοδο έχουν εγγραφεί στην πλατφόρμα:";
$langOtherActions = "Άλλες Ενέργειες";

// Stat
$langStat4eClass = "Στατιστικά πλατφόρμας";
$langNbProf = "Αριθμός ".$langsOfTeachers;
$langNbStudents = "Αριθμός ".$langsOfStudents;
$langNbLogin = "Αριθμός εισόδων";
$langNbCourses = "Αριθμός μαθημάτων";
$langNbVisitors = "Αριθμός επισκεπτών";
$langToday   ="Σ�?μερα";
$langLast7Days ="�?ελευταίες 7 μέρες";
$langLast30Days ="�?ελευταίες 30 μέρες";
$langNbAnnoucement = "Αριθμός ανακοινώσεων";
$langNbUsers = "Αριθμός χρηστών";
$langCoursVisible = "Ορατότητα";
$langOthers = "Διάφορα σύνολα";
$langCoursesPerDept = "Αριθμός μαθημάτων ανά τμ�?μα";
$langCoursesPerLang = "Αριθμός μαθημάτων ανά γλώσσα";
$langCoursesPerVis= "Αριθμός μαθημάτων ανά κατάσταση ορατότητας";
$langCoursesPerType= "Αριθμός μαθημάτων ανά τύπο μαθημάτων";
$langUsersPerCourse= "Αριθμός χρηστών ανά μάθημα";
$langErrors = "Σφάλματα:";
$langMultEnrol = "Πολλαπλές εγγραφές χρηστών";
$langMultEmail= "Πολλαπλές εμφανίσεις διευθύνσεων e-mail";
$langMultLoginPass = "Πολλαπλά ζεύγη LOGIN - PASS";
$langOk = "Εντάξει";
$langCont = "Συνέχεια";
$langNumUsers = "Αριθμός συμμετεχόντων στην πλατφόρμα";
$langNumGuest = "Αριθμός επισκεπτών";
$langAddAdminInApache ="Προσθ�?κη Διαχειριστ�?";
$langRestoreCourse = "Ανάκτηση Μαθ�?ματος";
$langStatCour = "Ποσοτικά στοιχεία μαθημάτων";
$langNumCourses = "Αριθμός μαθημάτων";
$langNumEachCourse = "Αριθμός μαθημάτων ανά τμ�?μα";
$langNumEachLang = "Αριθμός μαθημάτων ανά γλώσσα";
$langNunEachAccess = "Αριθμός μαθημάτων ανά τύπο πρόσβασης";
$langNumEachCat = "Αριθμός μαθημάτων ανά τύπο μαθημάτων";
$langAnnouncements = "Ανακοινώσεις";
$langNumEachRec = "Αριθμός εγγραφών ανά μάθημα";
$langFrom = "Από";
$langNotExist = "Δεν υπάρχουν";
$langExist = "�?πάρχουν!";
$langResult =" Αποτέλεσμα";
$langMultiplePairs = "Πολλαπλά ζεύγη";
$langMultipleAddr = "Πολλαπλές εμφανίσεις διευθύνσεων";
$langMultipleUsers = "Πολλαπλές εγγραφές χρηστών";
$langAlert = "Σημεία Προσοχ�?ς";
$langServerStatus ="Κατάσταση του εξυπηρέτη Mysql : ";
$langDataBase = "Βάση δεδομένων ";
$langLanguage ="Γλώσσα";
$langUpgradeBase = "Αναβάθμιση βάσης Δεδομένων";
$langCleanUp = "Διαγραφ�? παλιών αρχείων";

// listusers
$langBegin="αρχ�?";
$langEnd = "τέλος";
$langPreced50 = "Προηγούμενοι";
$langFollow50 = "Επόμενοι";
$langAll="όλοι";
$langNoSuchUsers = "Δεν υπάρχουν χρ�?στες σύμφωνα με τα κριτ�?ρια που ορίσατε";
$langAsInactive = "ως μη ενεργοί";

// listcours
$langOpenCourse = "Ανοιχτό";
$langClosedCourse = "Κλειστό";
$langRegCourse = "Απαιτείται Εγγραφ�?";

// quotacours
$langQuotaAdmin = "Διαχείριση Αποθηκευτικού Χώρου Μαθ�?ματος";
$langQuotaSuccess = "Η αλλαγ�? έγινε με επιτυχία";
$langQuotaFail = "Η αλλαγ�? δεν έγινε!";
$langMaxQuota = "έχει μέγιστο επιτρεπτό αποθηκευτικό χώρο";
$langLegend = "Για το υποσύστημα";
$langDropbox = "Χώρος Ανταλλαγ�?ς Αρχείων";
$langVideo = "Βίντεο";

// Added by vagpits
// General
$langReturnToSearch = "Επιστροφ�? στα αποτελέσματα αναζ�?τησης";
$langReturnSearch = "Επιστροφ�? στην αναζ�?τηση";
$langNoChangeHappened = "Δεν πραγματοποι�?θηκε καμία αλλαγ�?!";

// addfaculte
$langFaculteCatalog = "Κατάλογος Σχολών";
$langFaculteDepartment = "Σχολ�? / �?μ�?μα";
$langFaculteDepartments = "Σχολές / �?μ�?ματα";
$langManyExist = "�?πάρχουν";
$langReturnToAddFaculte = "Επιστροφ�? στην προσθ�?κη τμ�?ματος";
$langReturnToEditFaculte = "Επιστροφ�? στην Επεξεργασία �?μ�?ματος";
$langFaculteAdd = "Προσθ�?κη �?μ�?ματος";
$langFaculteDel = "Διαγραφ�? �?μ�?ματος";
$langFaculteEdit = "Επεξεργασία στοιχείων �?μ�?ματος";
$langFaculteIns = "Εισαγωγ�? Στοιχείων �?μ�?ματος";
$langAcceptChanges = "Επικύρωση Αλλαγών";
$langEditFacSucces = "Η επεξεργασία του μαθ�?ματος ολοκληρώθηκε με επιτυχία!";

// addusertocours
$langQuickAddDelUserToCoursSuccess = "Η διαχείριση χρηστών ολοκληρώθηκε με επιτυχία!";
$langFormUserManage = "Φόρμα Διαχείρισης Χρηστών";
$langListNotRegisteredUsers = "Λίστα Μη Εγγεγραμμένων Χρηστών";
$langListRegisteredStudents = "Λίστα Εγγεγραμμένων ".$langOfStudents;
$langListRegisteredProfessors = "Λίστα Εγγεγραμμένων ".$langOfTeachers;
$langErrChoose = "Παρουσιάστηκε σφάλμα στην επιλογ�? μαθ�?ματος!";
// delcours
$langCourseDel = "Διαγραφ�? μαθ�?ματος";
$langCourseDelSuccess = "�?ο μάθημα διαγράφηκε με επιτυχία!";
$langCourseDelConfirm = "Επιβεβαίωση Διαγραφ�?ς Μαθ�?ματος";
$langCourseDelConfirm2 = "Θέλετε σίγουρα να διαγράψετε το μάθημα με κωδικό";
$langNoticeDel = "ΣΗΜΕΙΩΣΗ: Η διαγραφ�? του μαθ�?ματος θα διαγράψει επίσης τους εγγεγραμμένους ".$langsOfStudentss." από το μάθημα, την αντιστοιχία του μαθ�?ματος στο �?μ�?μα, καθώς και όλο το υλικό του μαθ�?ματος.";

// editcours
$langCourseEdit = "Επεξεργασία Μαθ�?ματος";
$langCourseInfo = "Στοιχεία Μαθ�?ματος";
$langQuota = "Όρια αποθηκευτικού χώρου";
$langCourseStatus = "Κατάσταση Μαθ�?ματος";
$langCurrentStatus = "�?ρέχουσα κατάσταση";
$langListUsers = "Λίστα Χρηστών";
$langCourseDelFull = "Διαγραφ�? Μαθ�?ματος";
$langTakeBackup = "Λ�?ψη Αντιγράφου Ασφαλείας";
$langStatsCourse = "Στατιστικά Μαθ�?ματος";

// infocours.php
$langCourseEditSuccess = "�?α στοιχεία του μαθ�?ματος άλλαξαν με επιτυχία!";
$langCourseInfoEdit = "Αλλαγ�? Στοιχείων Μαθ�?ματος";

// listreq.php
$langOpenProfessorRequests = "Ανοικτές Αιτ�?σεις ".$langOfTeachers;
$langProfessorRequestClosed = "Η αίτηση του ".$langsOfTeacher." έκλεισε!";
$langReqHaveClosed = "Αιτ�?σεις που έχουν κλείσει";
$langReqHaveBlocked = "Αιτ�?σεις που έχουν απορριφθεί";
$langReqHaveFinished = "Αιτ�?σεις που έχουν ολοκληρωθεί";
$langemailsubjectBlocked = "Απόρριψη αίτησης εγγραφ�?ς στην Πλατφόρμα Ασύγχρονης �?ηλεκπαίδευσης";
$langemailbodyBlocked = "Η αίτησ�? σας για εγγραφ�? στην πλατφόρμα ".$siteName." απορρίφθηκε.";
$langCloseConf = "Επιβεβαίωση κλεισίματος αίτησης";
$langReintroductionApplication="Η επαναφορά της αίτησης ολοκληρώθηκε με επιτυχία!";

// mailtoprof.php
$langSendMessageTo = "Αποστολ�? μηνύματος";
$langToAllUsers = "σε όλους τους χρ�?στες";
$langProfOnly = "μόνο στους ".$langsTeachers." ";

// searchcours.php
$langSearchCourse = "Αναζ�?τηση Μαθημάτων";
$langNewSearch = "Νέα Αναζ�?τηση";
$langSearchCriteria = "Κριτ�?ρια Αναζ�?τησης";
$langSearch = "Αναζ�?τηση";

// statuscours.php
$langCourseStatusChangedSuccess = "Ο τύπος πρόσβασης του μαθ�?ματος άλλαξε με επιτυχία!";
$langCourseStatusChange = "Αλλαγ�? τύπου πρόσβασης μαθ�?ματος";

// authentication
$langMethods = "Ενεργοί τρόποι πιστοποίησης:";
$langActivate = "Ενεργοποίηση";
$langDeactivate = "Απενεργοποίηση";
$langChooseAuthMethod = "Επιλέξτε τον τρόπο πιστοποίησης χρηστών και καθορίστε τις ρυθμίσεις του";
$langConnYes = "ΕΠΙ�?�?ΧΗΣ Σ�?ΝΔΕΣΗ";
$langConnNo = "H Σ�?ΝΔΕΣΗ ΔΕΝ ΛΕΙ�?Ο�?ΡΓΕΙ!";
$langAuthNoValidUser = "�?α στοιχεία του χρ�?στη δεν είναι σωστά. Η εγγραφ�? δεν πραγματοποι�?θηκε.";
$langConnTest = "Γίνεται δοκιμ�? του τρόπου πιστοποίησης...";
$langAuthMethod = "�?ρόπος πιστοποίησης χρηστών";
$langdbhost = "Εξυπηρέτης Βάσης Δεδομένων";
$langdbname = "Όνομα Βάσης Δεδομένων";
$langdbuser = "Χρ�?στης Βάσης Δεδομένων";
$langdbpass = "Συνθηματικό χρ�?στη Βάσης Δεδομένων";
$langdbtable = "Όνομα πίνακα Βάσης Δεδομένων";
$langdbfielduser = "Όνομα πεδίου Χρ�?στη στον πίνακα";
$langdbfieldpass = "Όνομα πεδίου Συνθηματικού Χρ�?στη στον πίνακα";
$langInstructionsAuth = "Οδηγίες διασύνδεσης και χρ�?σης";
$langTestAccount = "Για να ενεργοποιηθεί ο τρόπος πιστοποίησης είναι απαραίτητο να κάνετε μια δοκιμαστικ�? χρ�?ση με ένα λογαριασμό της μεθόδου που επιλέξατε";
$langpop3host = "Εξυπηρέτης POP3";
$langpop3port = "Πόρτα λειτουργίας POP3";
$langimaphost = "Εξυπηρέτης IMAP";
$langimapport = "Πόρτα λειτουργίας IMAP";
$langldap_host_url = "Εξυπηρέτης LDAP";
$langldap_bind_dn = "Ορίσματα για LDAP binding";
$langldap_bind_user = "Όνομα Χρ�?στη για LDAP binding";
$langldap_bind_pw = "Συνθηματικό για LDAP binding";
$langUserAuthentication = "Πιστοποίηση Χρηστών";
$langSearchCourses = "Αναζ�?τηση μαθημάτων";
$langActSuccess = "Μόλις ενεργοποι�?σατε την ";
$langDeactSuccess = "Μόλις απενεργοποι�?σατε την ";
$langThe = "Η ";
$langActFailure = "δεν μπορεί να ενεργοποιηθεί, διότι δεν έχετε καθορίσει τις ρυθμίσεις του τρόπου πιστοποίησης";
$langLdapNotWork = "Προειδοποίση: Η php δεν έχει υποστ�?ριξη για ldap. Βεβαιωθείτε ότι η ldap υποστ�?ριξη είναι εγκατεστημένη και ενεργοποιημένη.";

// other
$langVisitors = "Επισκέπτες";
$langVisitor = "Επισκέπτης";
$langOther = "Άλλο";
$langTotal = "Σύνολο";
$langProperty = "Ιδιότητα";
$langStat = "Στατιστικά";
$langNoUserList = "Δεν υπάρχουν αποτελέσματα πρός εμφάνιση";
$langContactAdmin = "Αποστολ�? ενημερωτικού email στον Διαχειριστ�?";
$langActivateAccount = "Παρακαλώ να ενεργοποι�?σετε το λογαριασμό μου";
$langLessonCode = "Κωδικός μαθ�?ματος";

// unregister
$langConfirmDelete = "Επιβεβαίωση διαγραφ�?ς ";
$langConfirmDeleteQuestion1 = "Θέλετε σίγουρα να διαγράψετε τον χρ�?στη";
$langConfirmDeleteQuestion2 = "από το μάθημα με κωδικό";
$langTryDeleteAdmin = "Προσπαθ�?σατε να διαγράψετε τον χρ�?στη με user id = 1(Admin)!";
$langUserWithId = "Ο χρ�?στης με id";
$langWasDeleted = "διαγράφηκε";
$langWasAdmin = "�?ταν διαχειριστ�?ς";
$langWasCourseDeleted = "διαγράφηκε από το Μάθημα";
$langErrorDelete = "Σφάλμα κατά τη διαγραφ�? του χρ�?στη";
$langAfter = "Μετά από";
$langBefore = "Πρίν από";
$langUserType = "�?ύπος χρ�?στη";

// search
$langSearchUsers = "Αναζ�?τηση Χρηστών";
$langInactiveUsers = "Μη ενεργοί χρ�?στες";
$langAddSixMonths = "Προσθ�?κη χρόνου: 6 μ�?νες";

// eclassconf
$langRestoredValues = "Επαναφορά προηγούμενων τιμών";
$langEclassConf = "Αρχείο ρυθμίσεων του $siteName";
$langFileUpdatedSuccess = "�?ο αρχείο ρυθμίσεων τροποποι�?θηκε με επιτυχία!";
$langFileEdit = "Επεξεργασία Αρχείου";
$langFileError = "�?ο αρχείο config.php δεν μπόρεσε να διαβαστεί! Ελέγξτε τα δικαιώματα πρόσβασης.";
$langReplaceBackupFile = "Αντικατάσταση του config_backup.php.";
$langencryptedPasswd = "�?ποχρεωτικ�? κρυπτογράφηση των συνθηματικών";

// admin announcements
$langAdminAn = "Ανακοινώσεις Διαχειριστ�?";
$langAdminAddAnn = "Προσθ�?κη ανακοίνωσης διαχειριστ�?";
$langAdminModifAnn = "�?ροποποίηση ανακοίνωσης διαχειριστ�?";
$langAdminAnnModify = "Η ανακοίνωση διαχειριστ�? τροποποι�?θηκε";
$langAdminAnVis = "Ορατ�?";
$langAdminAnnAdd = "Η ανακοίνωση διαχειριστ�? προστέθηκε";
$langAdminAnnDel = "Η ανακοίνωση διαχειριστ�? διαγράφηκε";
$langAdminAnnMes = "τοποθετ�?θηκε την";
$langAdminAnnTitleEn = "�?ίτλος (Αγγλικά)";
$langAdminAnnBodyEn = "Ανακοίνωση (Αγγλικά)";
$langAdminAnnCommEn = "Σχόλια (Αγγλικά)";

// cleanup.php
$langCleanupOldFiles = 'Εκκαθάριση παλαιών αρχείων';
$langCleaningUp = 'Εκκαθάριση αρχείων παλαιότερων από %s %s στον υποκατάλογο %s';
$langDaySing = 'ημέρα';
$langDayPlur = 'ημέρες';
$langCleanupInfo = 'Η λειτουργία αυτ�? θα διαγράψει τα παλιά αρχεία από τους υποκαταλόγους "temp", "archive", "garbage", και"tmpUnzipping". Είστε βέβαιοι?';
$langCleanup = 'Εκκαθάριση';

/**********************************************************
* agenda.inc.php
**********************************************************/
$langModify="Αλλαγ�?";
$langAddModify="Προσθ�?κη / Αλλαγ�?";
$langAddIntro="Προσθ�?κη Εισαγωγικού Κειμένου";
$langBackList="Επιστροφ�? στον κατάλογο";
$langEvents="Γεγονότα";
$langAgenda="Ατζέντα";
$langDay="Μέρα";
$langMonth="Μ�?νας";
$langYear="Έτος";
$langHour="Ώρα";
$langHours = "Ώρες";
$langMinute ="Λεπτά";
$langLasting="Διάρκεια";
$langDateNow = "Σημεριν�? ημερομηνία:";
$langCalendar = "Ημερολόγιο";
$langAddEvent="Προσθ�?κη ενός γεγονότος";
$langDetail="Λεπτομέρειες";
$langChooseDate = "Επιλέξτε Ημερομηνία";
$langOldToNew = "Αντιστροφ�? σειράς παρουσίασης";
$langStoredOK="�?ο γεγονός αποθηκεύτηκε";
$langDeleteOK="�?ο γεγονός διαγράφηκε";
$langNoEvents = "Δεν υπάρχουν γεγονότα";
$langSureToDel = "Είστε σίγουρος ότι θέλετε να διαγράψετε το γεγονός με τίτλο";
$langDelete = "Διαγραφ�?";
$langInHour = "(σε ώρες)";
$langEmptyAgendaTitle = "Παρακαλώ πληκτρολογ�?στε τον τίτλο του γεγονότος";

// week days
$langDay_of_weekNames = array();
$langDay_of_weekNames['init'] = array('Κ', 'Δ', '�?', '�?', 'Π', 'Π', 'Σ');
$langDay_of_weekNames['short'] = array('Κυρ', 'Δευ', '�?ρι', '�?ετ', 'Πεμ', 'Παρ', 'Σαβ');
$langDay_of_weekNames['long'] = array('Κυριακ�?', 'Δευτέρα', '�?ρίτη', '�?ετάρτη', 'Πέμπτη', 'Παρασκευ�?', 'Σάββατο');

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
$langRegUser="εγγεγραμμένους χρ�?στες του μαθ�?ματος";
$langUnvalid="έχουν άκυρη διεύθυνση email �? δεν έχουν καθόλου";
$langModifAnn="Αλλαγ�? της ανακοίνωσης";
$langAnnouncement = "Ανακοίνωση";
$langMove = "Μετακίνηση";
$langAnnEmpty="Όλες οι ανακοινώσεις διαγράφηκαν";
$langAnnModify="η ανακοίνωση άλλαξε";
$langAnnAdd="Η ανακοίνωση προστέθηκε";
$langAnnDel="η ανακοίνωση διαγράφηκε";
$langPubl="αναρτ�?θηκε την";
$langAddAnn="Προσθ�?κη Ανακοίνωσης";
$langContent="Περιεχόμενο";
$langAnnTitle = "�?ίτλος Ανακοίνωσης";
$langAnnBody = "Σώμα Ανακοίνωσης";
$langEmptyAnn="Διαγραφ�? ανακοινώσεων";
$professorMessage="Μ�?νυμα $langsOfTeacher";
$langEmailSent=" και στάλθηκε στους εγγεγραμμένους χρ�?στες";
$langEmailOption="Αποστολ�? (με email) της ανακοίνωσης στους εγγεγραμμένους χρ�?στες";
$langUp = "Επάνω";
$langDown = "Κάτω";
$langNoAnnounce = "Δεν υπάρχουν ανακοινώσεις";
$langSureToDelAnnounce = "Είστε σίγουρος ότι θέλετε να διαγράψετε την ανακοίνωση";
$langSureToDelAnnounceAll = "Είστε σίγουρος ότι θέλετε να διαγράψετε όλες τις ανακοινώσεις";
$langAnn = "Ανακοινώθηκε την";
$langEmptyAnTitle = "Παρακαλώ πληκτρολογ�?στε τον τίτλο της ανακοίνωσης";
/*******************************************
* archive_course.inc.php
*******************************************/
$langArchiveCourse = "Αντίγραφο Ασφαλείας";
$langCreatedIn = "δημιουργ�?θηκε την";
$langCreateDirMainBase ="Δημιουργία του καταλόγου για την ανάκτηση της κεντρικ�?ς βάσης";
$langCreateDirCourseBase ="Δημιουργία του καταλόγου για την ανάκτηση των βάσεων των μαθημάτων";
$langCopyDirectoryCourse = "Αντιγραφ�? των αρχείων του μαθ�?ματος";
$langDisk_free_space = "Ελεύθερος χώρος";
$langBuildTheCompressedFile ="Δημιουργία του αρχείου αντίγραφου ασφαλείας";
$langFileCopied = "αρχεία αντιγράφηκαν";
$langArchiveLocation="�?οποθεσία";
$langSizeOf ="Μέγεθος του";
$langBackupSuccesfull = "Δημιουργ�?θηκε με επιτυχία το αντίγραφο ασφαλείας!";
$langBUCourseDataOfMainBase = "Αντίγραφο ασφαλείας των δεδομένων του μαθ�?ματος";
$langBackupOfDataBase="Αντίγραφο ασφαλείας της βάσης δεδομένων του μαθ�?ματος";
$langDownloadIt = "Κατεβάστε το";
$langBackupEnd = "Ολοκληρώθηκε το αντίγραφο ασφαλείας σε μορφ�?";

/*********************************************
* auth_methods.inc.php
**********************************************/
$langViaeClass = "μέσω πλατφόρμας";
$langViaPop = "με πιστοποίηση μέσω POP3";
$langViaImap = "με πιστοποίηση μέσω IMAP";
$langViaLdap = "με πιστοποίηση μέσω LDAP";
$langViaDB = "με πιστοποίηση μέσω άλλης Βάσης Δεδομένων";
$langHasActivate = "O τρόπος πιστοποίησης που επιλέξατε, έχει ενεργοποιηθεί";
$langAlreadyActiv = "O τρόπος πιστοποίησης που επιλέξατε, είναι �?δη ενεργοποιημένος";
$langErrActiv ="Σφάλμα! Ο τρόπος πιστοποίησης δεν μπορεί να ενεργοποιηθεί";
$langAuthSettings = "Ρυθμίσεις πιστοποίησης";
$langWrongAuth = "Πληκτρολογ�?σατε λάθος όνομα χρ�?στη / συνθηματικό";

/************************************************************
 * conference.inc.php
 ******************************************************************/

 $langConference = "�?ηλεσυνεργασία";
 $langWash = "Καθάρισμα";
 $langWashFrom = "Η κουβέντα καθάρισε από";
 $langSave = "Αποθ�?κευση";
 $langClearedBy = "καθαρισμός από";
 $langChatError = "Δεν είναι δυνατόν να ξεκιν�?σει η Ζωνταν�? �?ηλεσυνεργασία";
 $langsetvideo="Σύνδεσμος παρουσίασης βίντεο";
 $langButtonVideo="Μετάδοση";
 $langButtonPresantation="Μετάδοση";
 $langconference="Ενεργοποίηση τηλεδιάσκεψης";
 $langpresantation="Σύνδεσμος παρουσίασης ιστοσελίδας";
 $langVideo_content="<p align='justify'>Εδώ θα παρουσιαστεί το βίντεο αφού το ενεργοποι�?σει ο $langsTeacher.</p>";
 $langTeleconference_content1 = "<p align='justify'>Εδώ θα παρουσιαστεί η τηλεδιάσκεψη αφού την ενεργοποι�?σει ο $langsTeacher.</p>";
 $langTeleconference_content_noIE="<p align='justify'>Η τηλεδιάσκεψη ενεργοποιείται μόνο αν έχετε IE ως πλοηγό.</p>";
 $langWashVideo="Παύση μετάδοσης";
 $langPresantation_content="<p align='center'>Εδώ θα παρουσιαστεί μία ιστοσελίδα που θα επιλέξει ο $langsTeacher.</p>";
 $langWashPresanation="Παύση μετάδοσης";
 $langSaveChat="Αποθ�?κευση κουβέντας";
 $langSaveMessage="Η κουβέντα αποθηκεύθηκε στα Έγγραφα";
 $langSaveErrorMessage="Η κουβέντα δεν μπόρεσε να αποθηκευθεί";
 $langNoAliens = "Μόνο οι εγγεγραμμένοι χρ�?στες στην πλατφόρμα μπορούν να χρησιμοποιούν το υποσύστημα 'Κουβέντα' !";
 $langNoGuest = "Οι χρ�?στες - επισκέπτες δεν μπορούν να χρησιμοποι�?σουν το υποσύστημα 'Κουβέντα' !";

/*****************************************************************
* copyright.inc.php
******************************************************************/
$langCopyright = "Πληροφορίες Πνευματικών Δικαιωμάτων";
$langCopyrightNotice = '
Copyright © 2003 - 2008 <a href="http://www.gunet.gr/" target=_blank>GUnet</a>.<br>&nbsp;<br>
Η <a href="http://www.openeclass.org" target=_blank>πλατφόρμα Open eClass</a>
είναι ένα ολοκληρωμένο Σύστημα Διαχείρισης Ηλεκτρονικών Μαθημάτων και αποτελεί
την πρόταση του Ακαδημαϊκού Διαδικτύου GUnet για την υποστ�?ριξη της �?πηρεσίας
Ασύγχρονης �?ηλεκπαίδευσης. Aναπτύχθηκε και υποστηρίζεται ενεργά από την Ομάδα
Ασύγχρονης �?ηλεκπαίδευσης του GUnet και <a
href="http://download.eclass.gunet.gr" target="_blank">διανέμεται ελεύθερα</a>
ως Λογισμικό Ανοικτού Κώδικα σύμφωνα με τη γενικ�? δημόσια άδεια GNU General
Public License (GNU GPL).<br><br>
�?ο περιεχόμενο των Ηλεκτρονικών Μαθημάτων που φιλοξενεί η πλατφόρμα Open eClass, καθώς και τα πνευματικά δικαιώματα του υλικού αυτού, αν�?κουν στους συγγραφείς τους και το GUnet δεν διεκδικεί δικαιώματα σε αυτό. Για οποιαδ�?ποτε χρ�?ση �? αναδημοσίευση του περιεχομένου παρακαλούμε επικοινων�?στε με τους υπεύθυνους των αντίστοιχων Mαθημάτων.
';

/*******************************************************
* course_description.inc.php
*******************************************************/
$langCourseProgram = "Περιγραφ�? Μαθ�?ματος";
$langThisCourseDescriptionIsEmpty = "�?ο μάθημα δεν διαθέτει περιγραφ�?";
$langEditCourseProgram = "Δημιουργία και διόρθωση";
$langQuestionPlan = "Ερώτηση στον διδάσκοντα";
$langInfo2Say = "Πληροφορία για τους ".$langsOfStudentss;
$langAddCat = "Κατηγορία";
$langBackAndForget ="Ακύρωση και επιστροφ�?";
$langBlockDeleted = "Η περιγραφ�? διαγράφηκε!";

/********************************************************
* course_home.inc.php
*********************************************************/
$langAdminOnly="Μόνο για Διαχειριστές";
$langInLnk="Απενεργοποιημένοι σύνδεσμοι";
$langDelLk="Θέλετε πραγματικά να διαγράψετε αυτόν τον σύνδεσμο ?";
$langRemove="διαγραφ�?";
$langEnter ="Είσοδος";
$langUpdate = "Αλλαγ�? �?ίτλου";
$langIcon = "Εικονίδιο";
$langNameOfTheLink ="Όνομα Συνδέσμου";
$langRegistered = "εγγεγραμμένοι";
$langOneRegistered = "εγγεγραμμένος";
$langIdentity = "�?αυτότητα Μαθ�?ματος";
$langCourseS = "μάθημα";

/*********************************************
* course_info.inc.php
*********************************************/
$langCourseIden = "�?αυτότητα Μαθ�?ματος";
$langBackupCourse="Αντίγραφο ασφαλείας του μαθ�?ματος";
$langModifInfo="Διαχείριση Μαθ�?ματος";
$langModifDone="Η πληροφορία έχει αλλάξει";
$langHome="Επιστροφ�? στην αρχικ�? σελίδα";
$langCode="Κωδικός";
$langDelCourse="Διαγραφ�? του μαθ�?ματος";
$langDelUsers="Διαγραφ�? χρηστών από το μάθημα";
$langCourseTitle="�?ίτλος Μαθ�?ματος";
$langFaculty="�?μ�?μα";
$langDescription="Περιγραφ�?";
$langConfidentiality="Πρόσβαση στο μάθημα";
$langPrivOpen="Ελεύθερη Πρόσβαση (με εγγραφ�?) σε όσους διαθέτουν λογαριασμό στην πλατφόρμα";
$langForbidden="Μη επιτρεπτ�? ενέργεια";
$langConfTip="Επιλέξτε τον τύπο πρόσβασης του μαθ�?ματος από τους χρ�?στες.";
$langOptPassword = "Προαιρετικό συνθηματικό: ";
$langNoCourseTitle = "Δεν πληκτρολογ�?σατε τον τίτλο του μαθ�?ματος";

// delete_course.php
$langModifGroups="Ομάδες Εργασίας";
$langTheCourse="Tο μάθημα";
$langHasDel="έχει διαγραφεί";
$langByDel="Διαγράφοντας το μάθημα θα διαγραφούν μόνιμα όλα τα περιεχόμενα του και όλοι οι ".$langsStudents." που είναι γραμμένοι σε αυτό (δεν θα διαγραφούν από τα άλλα μαθ�?ματα).";
$langByDel_A="Θέλετε πράγματι να διαγράψετε το μάθημα:";
$langTipLang="Επιλέξτε την γλώσσα στην οποία θα εμφανίζονται τα μηνύματα του μαθ�?ματος.";
$langTipLang2="Επιλέξτε την γλώσσα στην οποία θα εμφανίζονται τα μηνύματα της πλατφόρμας.";

// deluser_course.php
$langConfirmDel = "Επιβεβαίωση διαγραφ�?ς μαθ�?ματος";
$langUserDel="Πρόκειται να διαγράψετε όλους τους ".$langsOfStudentss." από το μάθημα (δεν θα διαγραφούν από τα άλλα μαθ�?ματα).<p>Θέλετε πράγματι να προχωρ�?σετε στη διαγραφ�? τους από το μάθημα";

// refresh course.php
$langRefreshCourse = "Ανανέωση μαθ�?ματος";
$langRefreshInfo="Προκειμένου να προετοιμάσετε το μάθημα για μια νέα ομάδα ".$langsOfStudents." μπορείτε να διαγράψετε το παλιό περιεχόμενο.";
$langRefreshInfo_A="Επιλέξτε ποιες ενέργειες θέλετε να πραγματοποιηθούν";
$langUserDelCourse="Διαγραφ�? χρηστών από το μάθημα";
$langUserDelNotice = "Σημ.: Οι χρ�?στες δεν θα διαγραφούν από άλλα μαθ�?ματα";
$langAnnouncesDel = "Διαγραφ�? ανακοινώσεων του μαθ�?ματος";
$langAgendaDel = "Διαγραφ�? εγγραφών από την ατζέντα του μαθ�?ματος";
$langHideDocuments = "Απόκρυψη των εγγράφων του μαθ�?ματος";
$langHideWork = "Απόκρυψη των εργασιών του μαθ�?ματος";
$langSubmitActions = "Εκτέλεση ενεργειών";
$langOptions = "Επιλογές";
$langRefreshSuccess = "Η ανανέωση του μαθ�?ματος �?ταν επιτυχ�?ς. Εκτελέστηκαν οι ακόλουθες ενέργειες:";
$langUsersDeleted="Οι χρ�?στες διαγράφηκαν από το μάθημα";
$langAnnDeleted="Οι ανακοινώσεις διαγράφηκαν από το μάθημα";
$langAgendaDeleted="Οι εγγραφές της ατζέντας διαγράφηκαν από το μάθημα";
$langWorksDeleted="Οι εργασίες απενεργοποι�?θηκαν";
$langDocsDeleted="�?α έγγραφα απενεργοποι�?θηκαν";

/*****************************************************
* contact.inc.php
******************************************************/
$langContactProf = "Επικοινωνία με τους $langsTeachers";
$langEmailEmpty = "Η διεύθυνση ηλεκτρονικού ταχυδρομείου σας είναι κεν�?.
Για να μπορείτε να επικοινων�?σετε με τον $langsOfTeacher, θα πρέπει να έχετε ορίσει
τη διεύθυνσ�? σας, ώστε να μπορείτε να λάβετε απάντηση. Μπορείτε να ορίσετε τη
διεύθυνσ�? σας από την επιλογ�? <a href='%s'>«Αλλαγ�? του προφίλ μου»</a> στη
σελίδα του χαρτοφυλακίου σας.";

$langEmptyMessage = "Αφ�?σατε το κείμενο του μηνύματος κενό. �?ο μ�?νυμα δε στάλθηκε";
$langSendMessage = "Αποστολ�? μηνύματος";
$langContactMessage = "Επικοινων�?στε με τους υπεύθυνους $langsTeachers του μαθ�?ματος.
Εισάγετε το κείμενο του μηνύματός σας:";

$langSendingMessage = "�?ο μ�?νυμά σας αποστέλλεται προς:";
$langErrorSendingMessage = "Σφάλμα αποστολ�?ς! �?ο μ�?νυμα δε στάλθηκε.";
$langContactIntro = "Ο χρ�?στης της πλατφόρμας $siteName με όνομα %s
και διεύθυνση ηλεκτρονικού ταχυδρομείου <%s> σας έστειλε
το παρακάτω μ�?νυμα. Αν απαντ�?σετε στο μ�?νυμα αυτό, η απάντησ�?
σας θα σταλεί στον παραπάνω χρ�?στη.

%s
";

$langNonUserContact = "Για να επικοινων�?σετε με τους υπεύθυνους $langsTeachers
του μαθ�?ματος, θα πρέπει να έχετε λογαριασμό στο σύστημα και
να έχετε συνδεθεί. Παρακαλούμε επισκεφθείτε την <a href='%s'>αρχικ�? σελίδα</a>.";
$langIntroMessage = "Σύνταξη μηνύματος";
$langHeaderMessage = "Μ�?νυμα από $langsstudent_acc";


/****************************************************
* create_course.inc.php
*****************************************************/
$langDescrInfo="Σύντομη περιγραφ�? του μαθ�?ματος";
$langFieldsRequ="Όλα τα πεδία είναι υποχρεωτικά!";
$langFieldsOptional = "Προαιρετικά πεδία";
$langFieldsOptionalNote = "Σημ. μπορείτε να αλλάξετε οποιεσδ�?ποτε από τις πληροφορίες αργότερα";
$langEx="π.χ. <i>Ιστορία της �?έχνης</i>";
$langFac="Σχολ�? / �?μ�?μα";
$langDivision = "�?ομέας";
$langTargetFac="Η σχολ�? �? το τμ�?μα που υπάγεται το μάθημα";
$langDoubt="Αν δεν ξέρετε το κωδικό του μαθ�?ματος συμβουλευτείτε";
$langExFac = "* Αν επιθυμείτε να δημιουργ�?σετε μάθημα, σε άλλο τμ�?μα από αυτό που αν�?κετε, τότε επικοινων�?στε με
την Ομάδα Ασύγχρονης �?ηλεκπαίδευσης";
$langEmptyFields="Αφ�?σατε μερικά πεδία κενά!";
$langCreate="Δημιουργία";
$langCourseKeywords = "Λέξεις Κλειδιά:";
$langCourseAddon = "Συμπληρωματικά Στοιχεία:";
$langErrorDir = "Ο υποκατάλογος του μαθ�?ματος δεν δημιουργ�?θηκε και το μάθημα δεν θα λειτουργ�?σει!<br><br>Ελέγξτε τα δικαιώματα πρόσβασης του καταλόγου <em>courses</em>.";
$langSubsystems="Επιλέξτε τα υποσυστ�?ματα που θέλετε να ενεργοποι�?σετε για το νέο σας μάθημα:";
$langLanguageTip="Επιλέξτε σε ποια γλώσσα θα εμφανίζονται οι σελίδες του μαθ�?ματος";
$langAccess = "�?ύπος Πρόσβασης:";
$langAvailableTypes = "Διαθέσιμοι τύποι πρόσβασης";
$langModules = "�?ποσυστ�?ματα:";

// tables MySQL
$langForumLanguage="english";
$langTestForum="Γενικές συζητ�?σεις";
$langDelAdmin="Περιοχ�? συζητ�?σεων για κάθε θέμα που αφορά το μάθημα";
$langMessage="Όταν διαγράψετε τη δοκιμαστικ�? περιοχ�? συζητ�?σεων, θα διαγραφεί και το παρόν μ�?νυμα.";
$langExMessage="Παράδειγμα Μηνύματος";
$langAnonymous="Ανώνυμος";
$langExerciceEx="�?πόδειγμα άσκησης";
$langAntique="Ιστορία της αρχαίας φιλοσοφίας";
$langSocraticIrony="Η Σωκρατικ�? ειρωνεία είναι...";
$langManyAnswers="(περισσότερες από μία απαντ�?σεις μπορεί να είναι σωστές)";
$langRidiculise="Γελοιοποίηση του συνομιλητ�? σας προκειμένου να παραδεχτεί ότι κάνει λάθος.";
$langNoPsychology="Όχι, η Σωκρατικ�? ειρωνεία δεν είναι θέμα ψυχολογίας, αλλά σχετίζεται με την επιχειρηματολογία.";
$langAdmitError="Παραδοχ�? των δικών σας σφαλμάτων ώστε να ενθαρρύνετε το συνομιλητ�? σας να κάνει το ίδιο.";
$langNoSeduction="Όχι, η Σωκρατικ�? ειρωνεία δεν είναι μέθοδος αποπλάνησης, ούτε βασίζεται στο παράδειγμα.";
$langForce="Εξώθηση του συνομιλητ�? σας, με μια σειρά ερωτ�?σεων και υποερωτ�?σεων, να παραδεχτεί ότι δεν ξέρει ό,τι ισχυρίζεται πως ξέρει.";
$langIndeed="Πράγματι, η Σωκρατικ�? ειρωνεία είναι μια μέθοδος ερωτημάτων.";
$langContradiction="Χρ�?ση της αρχ�?ς της αποφυγ�?ς αντιφάσεων προκειμένου να οδηγ�?σετε τον συνομιλητ�? σας σε αδιέξοδο.";
$langNotFalse="Η απάντηση δεν είναι εσφαλμένη. Είναι αλ�?θεια ότι η αποκάλυψη της άγνοιας του συνομιλητ�? σας επιδεικνύει τα αντιφατικά συμπεράσματα που προκύπτουν από τις αρχικές παραδοχές του.";
$langDoc="Έγγραφα";
$langVideoLinks="Βιντεοσκοπημένα Μαθ�?ματα";
$langWorks="Εργασίες";
$langForums="Περιοχές Συζητ�?σεων";
$langExercices="Ασκ�?σεις";
$langAddPageHome="Ανέβασμα Ιστοσελίδας";
$langLinkSite="Προσθ�?κη συνδέσμου στην αρχικ�? σελίδα";
$langModifyInfo= "Διαχείριση Μαθ�?ματος";
$langDropBox = "Ανταλλαγ�? Αρχείων";
$langLearnPath = "Γραμμ�? Μάθησης";
$langWiki = "Σύστημα Wiki";
$langToolManagement = "Ενεργοποίηση Εργαλείων";
$langUsage = "Στατιστικά Χρ�?σης";
$langStats = "Στατιστικά";
$langVideoText="Παράδειγμα ενός αρχείου RealVideo. Μπορείτε να ανεβάσετε οποιοδ�?ποτε τύπο αρχείου βίντεο (.mov, .rm, .mpeg...), εφόσον οι ".$langsStudents." έχουν το αντίστοιχο plug-in για να το δούν";
$langGoogle="Γρ�?γορη και Πανίσχυρη μηχαν�?ς αναζ�?τησης";
$langIntroductionText="Εισαγωγικό κείμενο του μαθ�?ματος. Αντικαταστ�?τε το με το δικό σας, κάνοντας κλίκ στην <b>Αλλαγ�?</b>.";
$langIntroductionTwo="Αυτ�? η σελίδα επιτρέπει οποιοδ�?ποτε ".$langsOfStudent." να ανεβάσει ένα αρχείο στο μάθημα. Μπορείτε να στείλετε αρχεία HTML, μόνο αν δεν έχουν εικόνες.";
$langJustCreated="Μόλις δημιουργ�?σατε με επιτυχία το μάθημα με τίτλο ";

 // Groups
$langCreateCourseGroups="Ομάδες Χρηστών";
$langCatagoryMain="Αρχ�?";
$langCatagoryGroup="Συζ�?τησεις Ομάδων χρηστών";
$langNoGroup="Δεν έχουν οριστεί ομάδες χρηστών";

//neos odhgos dhmiourgias mathimaton
$langEnterMetadata="(Σημ.) μπορείτε να αλλάξετε διάφορες ρυθμίσεις του μαθ�?ματος μέσα από τη λειτουργία 'Διαχείριση Μαθ�?ματος'";
$langCreateCourse="Οδηγός δημιουργίας μαθ�?ματος";
$langCreateCourseStep="Β�?μα";
$langCreateCourseStep2="από";
$langCreateCourseStep1Title="Βασικά στοιχεία και πληροφορίες μαθ�?ματος";
$langCreateCourseStep2Title="Συμπληρωματικές πληροφορίες μαθ�?ματος";
$langCreateCourseStep3Title="�?ποσυστ�?ματα και τύπος πρόσβασης";
$langcourse_objectives="Στόχοι του μαθ�?ματος";
$langcourse_prerequisites="Προαπαιτούμενες γνώσεις";
$langNextStep="Επόμενο β�?μα";
$langPreviousStep="Προηγούμενο β�?μα";
$langFinalize="Δημιουργία μαθ�?ματος!";
$langCourseCategory="Η κατηγορία στην οποία αν�?κει το μάθημα";
$langProfessorsInfo="Ονοματεπώνυμα $langsOfTeachers του μαθ�?ματος χωρισμένα με κόμματα (π.χ.<i>Νίκος �?ζικόπουλος, Κώστας Αδαμόπουλος</i>)";
$langPublic="Ελεύθερη Πρόσβαση (χωρίς εγγραφ�?) από τη αρχικ�? σελίδα χωρίς συνθηματικό";
$langPrivate="Πρόσβαση στο μάθημα (για εγγραφ�?) έχουν μόνο όσοι βρίσκονται στη Λίστα Χρηστών του μαθ�?ματος";
$langPrivate_1="Πρόσβαση στο μάθημα";
$langPrivate_2="μόνο όσοι βρίσκονται στη Λίστα Χρηστών του μαθ�?ματος (με εγγραφ�?)";
$langPrivate_3="ελεύθερη πρόσβαση (χωρίς εγγραφ�?)";
$langAlertTitle = "Παρακαλώ συμπληρώστε τον τίτλο του μαθ�?ματος!";
$langAlertProf = "Παρακαλώ συμπληρώστε τον διδάσκοντα του μαθ�?ματος!";

/******************************************************
* document.inc.php
******************************************************/
$langUpload = "Ανέβασμα";
$langDownloadFile= "Ανέβασμα αρχείου στον εξυπηρέτη";
$langPathUploadFile= "Εντοπισμός θέσης του αρχείου στον Η/�? σας (τοπικά)";
$langCreateDir="Δημιουργία καταλόγου";
$langName="Όνομα";
$langNameDir="Όνομα νέου καταλόγου";
$langSize="Μέγεθος";
$langDate="Ημερομηνία";
$langMoveFrom = "Μετακίνηση του αρχείου";
$langRename="Μετονομασία";
$langOkComment="Επικύρωση αλλαγών";
$langVisible="Ορατό / Αόρατο";
$langCopy="Αντιγραφ�?";
$langNoSpace="Η αποστολ�? του αρχείου απέτυχε. Έχετε υπερβεί το μέγιστο επιτρεπτό
	χώρο. Για περισσότερες πληροφορίες, επικοινων�?στε με το διαχειριστ�? του συστ�?ματος.";
$langUnwantedFiletype='Μη αποδεκτός τύπος αρχείου';
$langDownloadEnd="�?ο ανέβασμα ολοκληρώθηκε";
$langFileExists="Δεν είναι δυνατ�? η λειτουργία.<br>�?πάρχει �?δη ένα αρχείο με το ίδιο όνομα.";
$langDocCopied="Tο έγγραφο αντιγράφηκε";
$langDocDeleted="�?ο έγγραφο διαγράφηκε";
$langElRen="Η μετονομασία έγινε";
$langDirCr="Ο κατάλογος δημιουργ�?θηκε";
$langDirMv="Η μετακίνηση ολοκληρώθηκε";
$langComMod="�?α σχόλια τροποποι�?θηκαν";
$langIn="στο";
$langNewDir="Όνομα του καινούριου καταλόγου";
$langImpossible="Δεν είναι δυνατ�? η λειτουργία";
$langViMod="Η ορατότητα του εγγράφου άλλαξε";
$langMoveOK="Η μεταφορά έγινε με επιτυχία!";
$langMoveNotOK="η μεταφορά δεν πραγματοποι�?θηκε!";
$langRoot = "Αρχικός κατάλογος";
$langNoDocuments = "Δεν υπάρχουν έγγραφα";

// Special for group documents
$langGroupSpace="Περιοχ�? ομάδας χρηστών";
$langGroupSpaceLink="Ομάδα χρηστών";
$langGroupForumLink="Περιοχ�? συζητ�?σεων ομάδας χρηστών";
$langZipNoPhp="�?ο αρχείο zip δεν πρέπει να περιέχει αρχεία .php";
$langUncompress="αποσυμπίεση του αρχείου (.zip) στον εξυπηρέτη";
$langDownloadAndZipEnd="�?ο αρχείο .zip ανέβηκε και αποσυμπιέστηκε";
$langPublish = "Δημοσίευση";
$langParentDir = "αρχικό κατάλογο";
$langInvalidDir = "Ακυρο �? μη υπαρκτό όνομα καταλόγου";
$langInvalidGroupDir = "Σφάλμα! Ο κατάλογος των ομάδων χρηστών δεν υπάρχει!";

//prosthikes gia v2 - metadata
$langCategory="Κατηγορία";
$langCreatorEmail="Ηλ. Διεύθυνση Συγγραφέα";
$langFormat="�?ύπος-Κατηγορία";
$langSubject="Θέμα";
$langAuthor="Συγγραφέας";
$langCopyrighted="Πνευματικά Δικαιώματα";
$langCopyrightedFree="Ελεύθερο";
$langCopyrightedNotFree="Προστατευμένο";
$langCopyrightedUnknown="Άγνωστο";
$langChangeMetadata="Αλλαγ�? πληροφοριών εγγράφου";
$langEditMeta="Επεξεργασία<br>Πληροφοριών";
$langCategoryExcercise="Άσκηση";
$langCategoryEssay="Εργασία";
$langCategoryDescription="Περιγραφ�? μαθ�?ματος";
$langCategoryExample="Παράδειγμα";
$langCategoryTheory="Θεωρία";
$langCategoryLecture="Διάλεξη";
$langCategoryNotes="Σημειώσεις";
$langCategoryOther="Άλλο";
$langNotRequired = "Η συμπλ�?ρωση των πεδίων είναι προαιρετικ�?";
$langCommands = "Ενέργειες";
$langQuotaBar = "Επισκόπηση αποθηκευτικού χώρου";
$langQuotaUsed = "Χρησιμοποιούμενος Χώρος";
$langQuotaTotal = "Συνολικός Διαθέσιμος Χώρος";
$langQuotaPercentage = "Ποσοστό χρ�?σης";
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
$dropbox_lang['dropbox'] = 'Χώρος Ανταλλαγ�?ς Αρχείων';
$dropbox_lang['help'] = 'Βο�?θεια';
$dropbox_lang['aliensNotAllowed'] = "Μόνο οι εγγεγραμμένοι χρ�?στες στην πλατφόρμα μπορούν να χρησιμοποιούν το dropbox. Δεν είστε εγγεγραμμένος χρ�?στης στην πλατφόρμα.";
$dropbox_lang['queryError'] = "Πρόβλημα στην βάση δεδομένων. Παρακαλώ επικοινων�?στε με τον διαχειριστ�? της πλατφόρμας.";
$dropbox_lang['generalError'] = "Παρουσιάστηκε σφάλμα. Παρακαλούμε επικοινων�?στε με τον διαχειριστ�? της πλατφόρμας.";
$dropbox_lang['badFormData'] = "Η αποστολ�? του αρχείου απέτυχε: �?α δεδομένα �?ταν με λάθος μορφ�?. Παρακαλούμε επικοινων�?στε με τον διαχειριστ�? της πλατφόρμας.";
$dropbox_lang['noUserSelected'] = "Παρακαλούμε επιλέξτε το χρ�?στη στον οποίο θέλετε να σταλεί το αρχείο.";
$dropbox_lang['noFileSpecified'] = "Δεν έχετε επιλέξει κάποιο αρχείο για να ανεβάσετε.";
$dropbox_lang['tooBig'] = "Δεν έχετε επιλέξει κάποιο αρχείο να ανεβάσετε �? το αρχείο υπερβαίνει το επιτρεπτό όριο σε μέγεθος.";
$dropbox_lang['uploadError'] = "Παρουσιάστηκε σφάλμα κατά το ανέβασμα του αρχείου. Παρακαλούμε επικοινων�?στε με τον διαχειριστ�? της πλατφόρμας.";
$dropbox_lang['errorCreatingDir'] = "Παρουσιάστηκε σφάλμα κατά τη δημιουργία καταλόγου. Παρακαλούμε επικοινων�?στε με τον διαχειριστ�? της πλατφόρμας.";
$dropbox_lang['installError'] = "Can't install the necessary tables for the dropbox module. Παρακαλώ επικοινων�?στε με τον διαχειριστ�? συστ�?ματος.";
$dropbox_lang['quotaError'] = "Έχετε ξεπεράσει το μέγιστο συνολικό επιτρεπτό μέγεθος αρχείων! �?ο ανέβασμα του αρχείου δεν πραγματοποι�?θηκε.";
$dropbox_lang['uploadFile'] = "Ανέβασμα αρχείου";
$dropbox_lang['authors'] = "Αποστολέας";
$dropbox_lang['description'] = "Περιγραφ�? αρχείου";
$dropbox_lang['sendTo'] = "Αποστολ�? στον/στην";
$dropbox_lang['receivedTitle'] = "Εισερχόμενα Αρχεία";
$dropbox_lang['sentTitle'] = "Απεσταλμένα Αρχεία";
$dropbox_lang['confirmDelete1'] = "Σημείωση: �?ο αρχείο ";
$dropbox_lang['confirmDelete2'] = " θα διαγραφεί μόνο από τον κατάλογό σας";
$dropbox_lang['all'] = "Σημείωση: �?α αρχεία θα διαγραφούν μόνο από τον κατάλογό σας";
$dropbox_lang['workDelete'] = "Διαγραφ�? από τον κατάλογο";
$dropbox_lang['sentBy'] = "Στάλθηκε από τον/την";
$dropbox_lang['sentTo'] = "Στάλθηκε στον/στην";
$dropbox_lang['sentOn'] = "την";
$dropbox_lang['anonymous'] = "ανώνυμος";
$dropbox_lang['ok'] = "Αποστολ�?";
$dropbox_lang['lastUpdated'] = "�?ελευταία ενημέρωση την";
$dropbox_lang['lastResent'] = "Last resent on";
$dropbox_lang['tableEmpty'] = "Ο κατάλογος είναι κενός.";
$dropbox_lang['overwriteFile'] = "Θέλετε να αντικαταστ�?σετε το προηγούμενο αρχείο που στείλατε;";
$dropbox_lang['orderBy'] = "�?αξινόμηση με βάση";
$dropbox_lang['lastDate'] = "την τελευταία ημερομηνία αποστολ�?ς";
$dropbox_lang['firstDate'] = "την πρώτη ημερομηνία αποστολ�?ς";
$dropbox_lang['title'] = "τον τίτλο";
$dropbox_lang['size'] = "το μέγεθος του αρχείου";
$dropbox_lang['author'] = "τον διδάσκοντα";
$dropbox_lang['sender'] = "τον αποστολέα";
$dropbox_lang['file'] = "Αρχείο";
$dropbox_lang['fileSize'] = "Μέγεθος";
$dropbox_lang['date'] = "Ημερομηνία";
$dropbox_lang['col_recipient'] = "Παραλ�?πτης";
$dropbox_lang['recipient'] = "τον παραλ�?πτη";
$dropbox_lang['docAdd'] = "�?ο αρχείο στάλθηκε με επιτυχία";
$dropbox_lang['fileDeleted'] = "�?ο επιλεγμένο αρχείο έχει διαγραφεί από το Χώρο Ανταλλαγ�?ς Αρχείων.";
$dropbox_lang['backList'] = "Επιστροφ�? στο Χώρο Ανταλλαγ�?ς Αρχείων";
$dropbox_lang['mailingAsUsername'] = "Mailing ";
$dropbox_lang['mailingInSelect'] = "---Mailing---";
$dropbox_lang['mailingSelectNoOther'] = "Η αποστολ�? μηνύματος δεν μπορεί να συνδιαστεί με αποστολ�? σε άλλους παραλ�?πτες";
$dropbox_lang['mailingNonMailingError'] = "Mailing cannot be overwritten by non-mailing and vice-versa";
$dropbox_lang['mailingExamine'] = "Examine mailing zip-file";
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
$dropbox_lang['mailingJustUploadNoOther'] = "�?ο ανέβασμα αρχείου δεν μπορεί να συνδυαστεί με αποστολ�? σε άλλους παραλ�?πτες";

/**********************************************************
* exercice.inc.php
**********************************************************/
$langExercicesView="Προβολ�? Ασκησης";
$langExercicesResult="Αποτελέσματα Ασκησης";
$langQuestion="Ερώτηση";
$langQuestions="Ερωτ�?σεις";
$langAnswer="Απάντηση";
$langAnswers="Απαντ�?σεις";
$langComment="Σχόλιο";
$langMaj="Ενημέρωση";
$langEvalSet="Ρυθμίσεις βαθμολογίας";
$langExercice="Ασκηση";
$langActive="ενεργό";
$langInactive="μη ενεργό";
$langNoEx="Δεν υπάρχει διαθέσιμη άσκηση";
$langNewEx="Νέα άσκηση";
$langExerciseType="�?ύπος Ασκ�?σεων";
$langExerciseName="Όνομα Ασκησης";
$langExerciseDescription="Περιγραφ�? Ασκησης";
$langSimpleExercise="Σε μία μόνο σελίδα";
$langSequentialExercise="Σε μία ερώτηση ανά σελίδα (στη σειρά)";
$langRandomQuestions="�?υχαίες Ερωτ�?σεις";
$langGiveExerciseName="Δώστε το όνομα της άσκησης";
$langGiveExerciseInts="�?α πεδία Χρονικός Περιορισμός & Επιτρεπόμενες Επαναλ�?ψεις πρέπει να είναι ακέραιοι αριθμοί";
$langQuestCreate="Δημιουργία ερωτ�?σεων";
$langExRecord="Η άσκηση σας αποθηκεύτηκε";
$langBackModif="Επιστροφ�? στην διόρθωση της άσκησης";
$langDoEx="Κάντε την άσκηση";
$langDefScor="Καθορίστε τις ρυθμίσεις βαθμών";
$langCreateModif="Δημιουργία / Αλλαγ�? των ερωτ�?σεων";
$langSub="�?πότιτλος";
$langNewQu="Νέα ερώτηση";
$langTrue="Σωστό";
$langMoreAnswers="+απαντ.";
$langLessAnswers="-απάντ.";
$langMoreElements="Προσθ�?κη";
$langLessElements="Αφαίρεση";
$langRecEx="Αποθ�?κευση άσκησης";
$langRecQu="Αποθ�?κευση ερώτησης";
$langRecAns="Αποθ�?κευση απαντ�?σεων";
$langIntroduction="Εισαγωγ�?";
$langTitleAssistant="Βοηθός δημιουργίας ασκ�?σεων";
$langQuesList="Κατάλογος ερωτ�?σεων";
$langSaveEx="Αποθ�?κευση απάντησης";
$langClose="Κλείσιμο";
$langFinish="�?έλος";
$langCancel="Ακύρωση";
$langQImage="Ερώτηση-Εικόνα";
$langAddQ="Προσθ�?κη ερώτησης";
$langInfoQuestion="Στοιχεία ερώτησης";
$langInfoExercise="Στοιχεία άσκησης";
$langAmong = "μεταξύ";
$langTake = "διάλεξε";

// admin.php
$langExerciseManagement="Διαχείριση Ασκησης";
$langExerciseModify="�?ροποποίηση Ασκησης";
$langQuestionManagement="Διαχείριση Ερώτησης";
$langQuestionNotFound="Δεν βρέθηκε η ερώτηση";
$langAlertAdmin="Παρακαλώ δηλώστε τουλάχιστον έναν διαχειριστ�? για το μάθημα!";

// question_admin.inc.php
$langNoAnswer="Δεν υπάρχει απάντηση αυτ�? την στιγμ�?!";
$langGoBackToQuestionPool="Επιστροφ�? στις διαθέσιμες ερωτ�?σεις";
$langGoBackToQuestionList="Επιστροφ�? στη λίστα ερωτ�?σεων";
$langQuestionAnswers="Απαντ�?σεις στην ερώτηση";
$langUsedInSeveralExercises="Προσοχ�?! H ερώτηση και οι απαντ�?σεις τις χρησιμοποιούνται σε αρκετές ασκ�?σεις. Θέλετε να τις αλλάξετε;";
$langModifyInAllExercises="σε όλες τις ασκ�?σεις";
$langModifyInThisExercise="μόνο στην τρέχουσα άσκηση";
$langQuestionView="Προβολ�?";

// statement_admin.inc.php
$langAnswerType="�?ύπος Απάντησης";
$langUniqueSelect="Πολλαπλ�?ς Επιλογ�?ς (Μοναδικ�? Απάντηση)";
$langMultipleSelect="Πολλαπλ�?ς Επιλογ�?ς (Πολλαπλές Απαντ�?σεις)";
$langFillBlanks="Συμπλ�?ρωμα Κενών";
$langMatching="�?αίριασμα";
$langAddPicture="Προσθ�?κη εικόνας";
$langReplacePicture="Αντικατάσταση της εικόνας";
$langDeletePicture="Διαγραφ�? της εικόνας";
$langQuestionDescription="Προαιρετικό σχόλιο";
$langGiveQuestion="Δώστε την ερώτηση";

// answer_admin.inc.php
$langWeightingForEachBlank="Δώστε ένα βάρος σε κάθε κενό";
$langUseTagForBlank="χρησιμοποι�?στε αγκύλες [...] για να ορίσετε ένα �? περισσότερα κενά";
$langQuestionWeighting="Βάρος";
$langTypeTextBelow="Πληκτρολογ�?στε το κείμενό σας παρακάτω";
$langDefaultTextInBlanks="Πρωτεύουσα της Ελλάδας είναι η [Αθ�?να].";
$langDefaultMatchingOptA="καλός";
$langDefaultMatchingOptB="όμορφη";
$langDefaultMakeCorrespond1="Ο πατέρας σου είναι";
$langDefaultMakeCorrespond2="Η μητέρα σου είναι";
$langDefineOptions="Καθορίστε τις επιλογές";
$langMakeCorrespond="Κάντε την αντιστοιχία";
$langFillLists="Συμπληρώστε τις δύο λίστες που ακολουθούν";
$langGiveText="Πληκτρολογ�?στε το κείμενο";
$langDefineBlanks="Ορίστε τουλάχιστον ένα κενό με αγκύλες [...]";
$langGiveAnswers="Δώστε τις απαντ�?σεις στις ερωτ�?σεις";
$langChooseGoodAnswer="Διαλέξτε την σωστ�? απάντηση";
$langChooseGoodAnswers="Διαλέξτε μία �? περισσότερες σωστές απαντ�?σεις";
$langColumnA="Στ�?λη Α";
$langColumnB="Στ�?λη B";
$langMoreLessChoices="Προσθ�?κη/Αφαίρεση επιλογών";

// question_list_admin.inc.php
$langQuestionList="Κατάλογος ερωτ�?σεων της άσκησης";
$langGetExistingQuestion="Ερώτηση από άλλη άσκηση";

// question_pool.php
$langQuestionPool="Διαθέσιμες Ερωτ�?σεις";
$langOrphanQuestions="Ερωτ�?σεις χωρίς απάντηση";
$langNoQuestion="Δεν έχουν ορισθεί ερωτ�?σεις για τη συγκεκριμένη άσκηση";
$langAllExercises="Όλες οι ασκ�?σεις";
$langFilter="Φιλτράρισμα";
$langGoBackToEx="Επιστροφ�? στην άσκηση";
$langReuse="Επαναχρησιμοποίηση";

// exercise_result.php
$langElementList="�?ο στοιχείο";
$langScore="Βαθμολογία";
$langQuestionScore="Βαθμολογία ερώτησης";
$langCorrespondsTo="Αντιστοιχεί σε";
$langExpectedChoice="Αναμενόμενη Απάντηση";
$langYourTotalScore="Συνολικ�? βαθμολογία άσκησης";

// exercice_submit.php
$langDoAnEx="Κάντε μια άσκηση";
$langCorrect="Σωστό";
$langExerciseNotFound="Η απάντηση δεν βρέθηκε";
$langAlreadyAnswered="Απαντ�?σατε �?δη στην ερώτηση";

// scoring.php & scoring_student.php
$langExerciseStart="Έναρξη";
$langExerciseEnd="Λ�?ξη";
$langExerciseConstrain="Χρονικός περιορισμός";
$langExerciseEg="π.χ.";
$langExerciseConstrainUnit="λεπτά";
$langExerciseConstrainExplanation="0 για καθόλου περιορισμό";
$langExerciseAttemptsAllowedExplanation="0 για απεριόριστο αριθμό επαναλ�?ψεων";
$langExerciseAttemptsAllowed="Επιτρεπόμενες επαναλ�?ψεις";
$langExerciseAttemptsAllowedUnit="φορές";
$langExerciseExpired="Έχετε φτάσει τον μέγιστο επιτρεπτό αριθμό επαναλ�?ψεων της άσκησης.";
$langExerciseExpiredTime="Έχετε ξεπεράσει το επιτρεπτό χρονικό όριο εκτέλεσης της άσκησης.";
$langExerciseLis="Λίστα ασκ�?σεων";
$langResults="Αποτελέσματα";
$langResultsFailed="Αποτυχία";
$langYourTotalScore2="Συνολικ�? βαθμολογία";
$langExerciseScores1="HTML";
$langExerciseScores2="Ποσοστιαία";
$langExerciseScores3="CSV";
$langExerciseSurname="Επώνυμο";

/***********************************************
* external_module.inc.php
***********************************************/
$langSubTitle="<br><strong>Σημείωση:</strong> Αν θέλετε να προσθέσετε ένα σύνδεσμο σε μια σελίδα,
	πηγαίνετε σε αυτ�? τη σελίδα, κάντε αποκοπ�? και επικόλληση τη διεύθυνσ�? της στη μπάρα των URL
	στο πάνω μέρος του browser και εισάγετέ το στο πεδίο \"Σύνδεσμος\" παρακάτω.<br><br>";
$langLink="Σύνδεσμος";
$langInvalidLink = "Ο σύνδεσμος (�? η περιγραφ�? του) είναι κενός και δεν προστέθηκε!";
$langNotAllowed = "Μη επιτρεπτ�? ενέργεια";

/***********************************************
* faculte.inc.php
***********************************************/
$langListFaculteActions="Κατάλογος Σχολών / �?μημάτων - Ενέργειες";
$langCodeFaculte1="Κωδικός Σχολ�?ς / �?μ�?ματος";
$langCodeFaculte2="(με λατινικούς χαρακτ�?ρες μόνο, π.χ. MATH)";
$langAddFaculte="Προσθ�?κη Σχολών / �?μημάτων";
$langFaculte1="Σχολ�? / �?μ�?μα";
$langFaculte2="(π.χ. Μαθηματικό)";
$langAddSuccess="Η εισαγωγ�? πραγματοποι�?θηκε με επιτυχία !";
$langNoSuccess="Πρόβλημα κατά την εισαγωγ�? των στοιχείων !";
$langProErase="�?πάρχουν διδασκόμενα μαθ�?ματα στο τμ�?μα αυτό !";
$langNoErase="Η διαγραφ�? του τμ�?ματος δεν είναι δυνατ�?.";
$langErase="�?ο τμ�?μα διαγράφηκε!";
$langFCodeExists= "Ο κωδικός που βάλατε υπάρχει �?δη! Δοκιμάστε ξανά επιλέγοντας διαφορετικό";
$langFaculteExists="Η σχολ�? / τμ�?μα που βάλατε υπάρχει �?δη! Δοκιμάστε ξανά επιλέγοντας διαφορετικό";
$langEmptyFaculte="Αφ�?σατε κάποιο από τα πεδία κενά! Δοκιμάστε ξανά";
$langGreekCode="Ο κωδικός που βάλατε περιέχει μη λατινικούς χαρακτ�?ρες!. Δοκιμάστε ξανά επιλέγοντας διαφορετικό";

/******************************************************
* forum_admin.inc.php
*******************************************************/
$langOrganisation="Διαχείριση περιοχών";
$langForCat="Περιοχές συζητ�?σεων της κατηγορίας";
$langBackCat="επιστροφ�? στις κατηγορίες";
$langForName="Όνομα περιοχ�?ς συζητ�?σεων";
$langFunctions="Λειτουργίες";
$langAddForum="Προσθ�?κη";
$langEditForum="�?ροποποίση";
$langAddForCat="Προσθ�?κη περιοχ�?ς συζητ�?σεων";
$langChangeCat="Αλλαγ�? της κατηγορίας";
$langChangeForum="�?ροποποίηση της περιοχ�?ς συζ�?τησης";
$langModCatName="Αλλαγ�? ονόματος κατηγορίας";
$langCat="Κατηγορία";
$langNameCatMod="�?ο όνομα της κατηγορίας έχει αλλάξει";
$langBack="Επιστροφ�?";
$langCatAdded="Προστέθηκε κατηγορία";
$langForCategories="Κατηγορίες περιοχών συζητ�?σεων";
$langAddForums="Για να προσθέσετε περιοχές συζητ�?σεων, κάντε κλίκ στο «Περιοχές συζητ�?σεων» στην κατηγορία της επιλογ�?ς σας. Μια κεν�? κατηγορία (χωρίς περιοχές) δεν θα φαίνεται στους ".$langsOfStudentss." ";
$langCategories="Κατηγορίες";
$langNbFor="Πλ�?θος συζητ�?σεων";
$langAddCategory="Προσθ�?κη κατηγορίας";
$langForumDataChanged = "�?α στοιχεία της περιοχ�?ς συζητ�?σεων έχουν αλλάξει";
$langForumCategoryAdded = "Προστέθηκε νέα περιοχ�? συζητ�?σεων στην κατηγορία που επιλέξατε";
$langForumDelete = "Η περιοχ�? συζητ�?σεων έχει διαγραφεί";
$langCatForumDelete = "Η κατηγορία της περιοχ�?ς συζητ�?σεων έχει διαγραφεί";
$langID = "Α/Α";
$langForumOpen = "Ανοικτ�?";
$langForumClosed = "Κλειστ�?";


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
$langDeleteGroups="Διαγραφ�? όλων των ομάδων χρηστών";
$langGroupsAdded="ομάδες χρηστών έχουν προστεθεί";
$langGroupAdded = "ομάδα χρηστών έχει προστεθεί";
$langGroupsDeleted="Ολες οι ομάδες χρηστών έχουν διαγραφεί";
$langGroupDel="Η ομάδα χρηστών διαγράφηκε";
$langGroupsEmptied="Όλες οι ομάδες χρηστών είναι άδειες";
$langEmtpyGroups="Εκκαθάριση όλων των ομάδων χρηστών";
$langGroupsFilled="Όλες οι ομάδες χρηστών έχουν συμπληρωθεί";
$langFillGroups="Συμπλ�?ρωση των ομάδων χρηστών";
$langGroupsProperties="Ρυθμίσεις ομάδες χρηστών";
$langStudentRegAllowed="Οι χρ�?στες επιτρέπεται να γραφτούν στις ομάδες";
$langStudentRegNotAllowed="Οι χρ�?στες δεν επιτρέπεται να γραφτούν στις ομάδες";
$langTools="Εργαλεία";
$langExistingGroups="�?πάρχουσες Ομάδες Χρηστών";
$langEdit="Διόρθωση";
$langDeleteGroupWarn = "Επιβεβαίωση διαγραφ�?ς της ομάδας χρηστών";
$langDeleteGroupAllWarn = "Επιβεβαίωση διαγραφ�?ς όλων των ομάδων χρηστών";

// Group Properties
$langGroupProperties="Ρυθμίσεις ομάδων χρηστών";
$langGroupAllowStudentRegistration="Οι ".$langsStudents." επιτρέπονται να εγγραφούν στις ομάδες χρηστών";
$langGroupStudentRegistrationType="Δυνατότητα Εγγραφ�?ς";
$langGroupPrivatise="Κλειστές περιοχές συζητ�?σεων ομάδων χρηστών";
$langGroupForum="Περιοχ�? συζητ�?σεων";
$langGroupPropertiesModified="Αλλάχτηκαν οι ρυθμίσεις της ομάδας χρηστών";

// Group space
$langGroupThisSpace="Περιοχ�? για την ομάδα χρηστών";
$langGroupName="Όνομα ομάδας χρηστών";
$langEditGroup="Διόρθωση της ομάδας χρηστών";
$langUncompulsory="(προαιρετικό)";
$langNoGroupStudents="Μη εγγεγραμμένοι ".$langsStudents." ";
$langGroupMembers="Μέλη ομάδας χρηστών";
$langGroupValidate="Επικύρωση";
$langGroupCancel="Ακύρωση";
$langGroupSettingsModified="Οι ρυθμίσεις της ομάδας χρηστών έχουν αλλάξει";
$langNameSurname="Όνομα Επίθετο";
$langAM="Αριμός Μητρώου";
$langEmail="email";
$langGroupStudentsInGroup=" ".$langsStudents." εγγεγραμμένοι σε ομάδες χρηστών";
$langGroupStudentsRegistered=" ".$langsStudents." εγγεγραμμένοι στο μάθημα";
$langGroupNoGroup="μη εγγεγραμμένοι ".$langsStudents." ";
$langGroupUsersList="Βλέπε <a href=../user/user.php>Χρ�?στες</a>";
$langGroupTooMuchMembers="Ο αριθμός που προτάθηκε υπερβαίνει το μέγιστο επιτρεπόμενο (μπορείτε να το αλλάξετε παρακάτω).
	Η σύνθεση της ομάδας δεν άλλαξε";
$langGroupTutor="Διδάσκοντας";
$langGroupNoTutor="κανένας";
$langGroupNone="δεν υπάρχει";
$langGroupNoneMasc="κανένας";
$langAddTutors="Διαχείριση καταλόγου χρηστών";
$langForumGroup="Περιοχ�? συζητ�?σεων της ομάδας";
$langMyGroup="η ομάδα μου";
$langOneMyGroups="ο επιβλέπων";
$langRegIntoGroup="Προσθέστε με στην ομάδα";
$langGroupNowMember="Είσαι τώρα μέλος της ομάδας";
$langPublicAccess="ανοικτό";
$langForumType="�?ύπος περιοχ�?ς συζητ�?σεων";
$langPropModify="Αλλαγ�? ρυθμίσεων";
$langGroupAccess="Πρόσβαση";
$langGroupFilledGroups="Οι ομάδες χρηστών έχουν συμπληρωθεί από ".$langsOfStudentss." που βρίσκονται στον κατάλογο «Χρ�?στες».";
$langGroupInfo = "Στοιχεία Ομάδας";

// group - email
$langEmailGroup = "Αποστολ�? e-mail στην ομάδα";
$langTypeMessage = "Πληκτρολογ�?στε το μ�?νυμά σας παρακάτω";
$langSend = "Αποστολ�?";
$langEmailSuccess = "�?ο e-mail σας στάλθηκε με επιτυχία !";
$langMailError = "Σφάλμα κατά την αποστολ�? e-mail !";
$langGroupMail = "Mail στην Ομάδα Χρηστών";
$langMailSubject = "Θέμα: ";
$langMailBody = "Μ�?νυμα: ";
$langProfLesson = "Διδάσκων του μαθ�?ματος";

/*****************************************************
* guest.inc.php
*****************************************************/
$langAskGuest="Πληκτρολογ�?στε το συνθηματικό του λογαριασμού επισκέπτη";
$langAddGuest="Προσθ�?κη χρ�?στη επισκέπτη";
$langGuestName="Επισκέπτης";
$langGuestSurname="Μαθ�?ματος";
$langGuestUserName="guest";
$langGuestExist="�?πάρχει �?δη ο λογαριασμός Επισκέπτη! Μπορείτε όμως αν θέλετε να αλλάξετε το συνθηματικό του.";
$langGuestSuccess="Ο λογαριασμός επισκέπτη (guest account) δημιουργ�?θηκε με επιτυχία !";
$langGuestFail="Πρόβλημα κατά την δημιουργία λογαριασμού επισκέπτη";
$langGuestChange="Η αλλαγ�? συνθηματικού επισκέπτη έγινε με επιτυχία!";

/********************************************************
* gunet.inc.php
********************************************************/
$infoprof="Σύντομα θα σας σταλεί e-mail από την Ομάδα Διαχείρισης της Πλατφόρμας Ασύγχρονης �?ηλεκπαίδευσης $siteName, με τα στοιχεία του λογαριασμού σας.";
$profinfo="Η ηλεκτρονικ�? πλατφόρμα $siteName διαθέτει 2 εναλλακτικούς τρόπους εγγραφ�?ς διδασκόντων";
$userinfo="Η ηλεκτρονικ�? πλατφόρμα $siteName διαθέτει 2 εναλλακτικούς τρόπους εγγραφ�?ς χρηστών:";
$regprofldap="Εγγραφ�? διδασκόντων που έχουν λογαριασμό στην �?πηρεσία Καταλόγου (LDAP Directory Service) του ιδρύματος που αν�?κουν";
$regldap="Εγγραφ�? χρηστών που έχουν λογαριασμό στην �?πηρεσία Καταλόγου (LDAP Directory Service) του ιδρύματος που αν�?κουν";
$regprofnoldap="Εγγραφ�? διδασκόντων που δεν έχουν λογαριασμό στην �?πηρεσία Καταλόγου του ιδρύματος που αν�?κουν";
$regnoldap="Εγγραφ�? χρηστών που δεν έχουν λογαριασμό στην �?πηρεσία Καταλόγου του ιδρύματος που αν�?κουν";
$mailbody1="\n$Institution\n\n";
$mailbody2="Ο Χρ�?στης\n\n";
$mailbody3="επιθυμεί να έχει πρόσβαση ";
$mailbody4="στην υπηρεσία Ασύγχρονης �?ηλεκπαίδευσης ";
$mailbody5="του $siteName";
$mailbody6="σαν ".$langsTeacher.".";
$mailbody7="Σχολ�? / �?μ�?μα:";
$mailbody8="ως ".$langsStudent.".";
$logo= "Πλατφόρμα Ασύγχρονης �?ηλεκπαίδευσης $siteName";
$gunet="Ομάδα Ασύγχρονης �?ηλεκπαίδευσης $siteName";
$sendinfomail="Αποστολ�? ενημερωτικού e-mail στους $langsTeachers του $siteName";
$infoabouteclass="Ενημερωτικό δελτίο πλατφόρμας $siteName";

// contact.php
$introcontact = "Μπορείτε να επικοινωνείτε με την Ομάδα �?ποστ�?ριξης της πλατφόρμας <b>".$siteName."</b> με τους παρακάτω τρόπους:";
$langPostMail="<b>�?αχυδρομικ�? Διεύθυνση:</b>";
$langPhone = "<b>�?ηλ:</b>";
$langFax = "<b>Fax:</b>";
$langForm="Συμπλ�?ρωση Φόρμας";
$langReturn="Eπιστροφ�?";

/************************************************************
* import.inc.php
************************************************************/
$langAddPage="Προσθ�?κη μιας σελίδας";
$langPageAdded="Η σελίδα προστέθηκε";
$langPageTitleModified="Ο τίτλος της σελίδας άλλαξε";
$langSendPage="Όνομα αρχείου της σελίδας";
$langCouldNotSendPage="�?ο αρχείο δεν είναι σε μορφ�? HTML και δεν �?ταν δυνατόν να σταλεί. Αν θέλετε να στείλετε αρχεία που
δεν είναι σε μορφ�? HTML (π.χ. PDF, Word, Power Point, Video, κ.λπ.)
χρησιμοποι�?στε τα <a href='../document/document.php'>Έγγραφα</a>.";
$langAddPageToSite="Προσθ�?κη μιας σελίδας σε ένα site";
$langCouldNot="�?ο αρχείο δεν �?ταν δυνατόν να σταλεί";
$langOkSent="<p><b>Η σελίδα σας στάλθηκε</b><br/><br/>Δημιουργ�?θηκε σύνδεσμος προς αυτ�?ν στο αριστερό μενού</p>";
$langTooBig="Δεν επιλέξατε κάποιο αρχείο για να στείλετε �? το αρχείο είναι πολύ μεγάλο �? δεν πληκτρολογ�?σατε τίτλο σελίδας";
$langExplanation_0="Αν έχετε  δημιουργ�?σει κάποια σελίδα για το μάθημα σας σε μορφ�? HTML (π.χ. \"my_page.htm\"), τότε μπορείτε να χρησιμοποι�?σετε την παρακάτω φόρμα για να κατασκευάσετε έναν σύνδεσμο στο μενού του μαθ�?ματος (αριστερά). Η σελίδα σας με αυτό τον τρόπο  δημοσιεύεται (ανεβαίνει) στην πλατφόρμα και εμφανίζεται μαζί με τα υπόλοιπα εργαλεία του μαθ�?ματος. <br/>Για μεγαλύτερη ευελιξία, ο σύνδεσμος αυτός μπορεί να γίνεται ενεργός/ανενεργός όπως τα υπόλοιπα εργαλεία.";
$langExplanation_1="Στοιχεία σελίδας";
$langExplanation_2="�?ο όνομα που θα εμφανίζεται στο αριστερό μενού.";
$langExplanation_3="Αν θέλετε να δημιουργ�?σετε συνδέσμους για αρχεία που <u>δεν</u> είναι σε μορφ�? HTML (π.χ. PDF, Word, Power Point, Video, κ.λπ.) τότε χρησιμοποι�?στε το υποσύστημα <a href='../document/document.php'>Έγγραφα</a>.";
$langExplanation_4="Στοιχεία εξωτερικού συνδέσμου";
$langNoticeExpl = "Σημ: �?ο μέγιστο επιτρεπτό μέγεθος του αρχείου της σελίδας είναι 20MB.";
$langPgTitle="�?ίτλος σελίδας";

/***************************************************************
* index.inc.php
***************************************************************/
$langHomePage = "Αρχικ�? Σελίδα";
$langInvalidId = "Λάθος στοιχεία.<br>Αν δεν είστε γραμμένος, συμπληρώστε τη
        <a href='modules/auth/registration.php'>φόρμα εγγραφ�?ς</a>.";
$langInvalidGuestAccount = "�?ο μάθημα για το οποίο έχει δημιουργηθεί ο λογαριασμός 'χρ�?στη επισκέπτη' δεν υπάρχει πλέον.";
$langAccountInactive1 = "Μη ενεργός λογαριασμός.";
$langAccountInactive2 = "Παρακαλώ επικοινων�?στε με τον διαχειριστ�? για την ενεργοποίηση του λογαριασμού σας";
$langMyCoursesProf="�?α μαθ�?ματα που υποστηρίζω (".$langTeacher.")";
$langMyCoursesUser="�?α μαθ�?ματα που παρακολουθώ (".$langStudent.")";
$langNoCourses="Δεν υπάρχουν μαθ�?ματα";
$langCourseCreate="Δημιουργία Μαθ�?ματος";
$langMyAgenda = "�?ο Ημερολόγιό μου";
$langMyStats = "Στατιστικά Χρ�?σης";   #ophelia 1-8-2006
$langMyAnnouncements = "Οι Ανακοινώσεις μου";
$langWelcome="τα μαθ�?ματα είναι διαθέσιμα παρακάτω. Άλλα μαθ�?ματα απαιτούν
όνομα χρ�?στη και συνθηματικό, τα οποία μπορείτε να τα αποκτ�?σετε κάνοντας κλίκ στην 'εγγραφ�?'. Οι καθηγητές
μπορούν να δημιουργ�?σουν μαθ�?ματα κάνοντας κλικ στην εγγραφ�? επίσης, αλλά επιλέγοντας ύστερα
'Δημιουργία μαθημάτων (καθηγητές)'.";
$langAdminTool = "Διαχείριση Πλατφόρμας";
$langUserName="Όνομα χρ�?στη (username)";
$langPass="Συνθηματικό (password)";
$langHelp="Βο�?θεια";
$langSelection="Επιλογ�?";
$langManagement="Διαχείριση";
$langMenu ="Μενού";
$langLogout="Έξοδος";
$langSupportForum="Περιοχ�? �?ποστ�?ριξης";
$langInvalidAuth = "Λάθος τρόπος πιστοποίησης";
$langContact = 'Επικοινωνία';
$langInfoPlat = '�?αυτότητα Πλατφόρμας';
$lang_forgot_pass = "Ξεχάσατε το συνθηματικό σας;";
$langNewAnnounce = "Νέα!";
$langUnregUser = "Διαγραφ�? λογαριασμού";
$langListFaculte = "Κατάλογος �?μημάτων";
$langListCourses = "Kατάλογος Μαθημάτων";
$langAsynchronous = "Ομάδα Ασύγχρονης �?ηλεκπαίδευσης";
$langUserLogin = "Σύνδεση χρ�?στη";
$langWelcomeToEclass = "Καλωσορίσατε στο ".$siteName."!";
$langWelcomeToPortfolio = "Καλωσορίσατε στο προσωπικό σας χαρτοφυλάκιο";
$langUnregCourse = "Απεγγραφ�? από μάθημα";
$langUnCourse = "Απεγγραφ�?";
$langCourseCode = "Μάθημα (Κωδικός)";
$langInfoAbout = "Η πλατφόρμα <strong>".$siteName."</strong> αποτελεί ένα ολοκληρωμένο Σύστημα Διαχείρισης Ηλεκτρονικών Μαθημάτων. Έχει σχεδιαστεί με προσανατολισμό την ενίσχυση της συμβατικ�?ς διδασκαλίας αξιοποιώντας την �?δη σε υψηλό βαθμό αφομοιωμένη στο χώρο της εκπαίδευσης πληροφορικ�? τεχνολογία. Ακολουθεί τη φιλοσοφία του λογισμικού ανοικτού κώδικα και υποστηρίζει την υπηρεσία Ασύγχρονης �?ηλεκπαίδευσης χωρίς περιορισμούς και δεσμεύσεις. Η πρόσβαση στην υπηρεσία γίνεται με τη χρ�?ση ενός απλού φυλλομετρητ�? (web browser) χωρίς την απαίτηση εξειδικευμένων τεχνικών γνώσεων.<br><br>
Στόχος είναι η ενίσχυση της εκπαιδευτικ�?ς διαδικασίας, προσφέροντας στους συμμετέχοντες ένα δυναμικό περιβάλλον αλληλεπίδρασης και συνεχούς επικοινωνίας ".$langsOfTeacher."  ".$langsOfStudent.". Ειδικότερα, επιτρέπει στον ".$langsOfTeacher." την ηλεκτρονικ�? οργάνωση, αποθ�?κευση και παρουσίαση του εκπαιδευτικού υλικού και παρέχει στον ".$langsstudent_acc." ένα εναλλακτικό κανάλι εξατομικευμένης μάθησης ανεξάρτητο από χωροχρονικές δεσμεύσεις.";

/*$langWelcomeStud = "<br>Καλωσ�?λθατε στο περιβάλλον της πλατφόρμας <b>$siteName</b>.<br><br>
                    Επιλέξτε \"Εγγραφ�? σε μάθημα\" για να παρακολουθ�?σετε τα διαθέσιμα ηλεκτρονικά μαθ�?ματα.";
$langWelcomeProf = "<br>Καλωσ�?λθατε στο περιβάλλον της πλατφόρμας <b>$siteName</b>.<br><br>
                    Επιλέξτε \"Δημιουργία Μαθ�?ματος\" για να δημιουργ�?σετε τα ηλεκτρονικά σας μαθ�?ματα.";
*/
$langWelcomeStud = "Επιλέξτε \"Εγγραφ�? σε μάθημα\" για να παρακολουθ�?σετε τα διαθέσιμα ηλεκτρονικά μαθ�?ματα.";
$langWelcomeProf = "Επιλέξτε \"Δημιουργία Μαθ�?ματος\" για να δημιουργ�?σετε τα ηλεκτρονικά σας μαθ�?ματα.";

/***********************************************************
* install.inc.php
***********************************************************/
$langTitleInstall = "Οδηγός εγκατάστασης Open eClass";
$langWelcomeWizard = "Καλωσορίσατε στον οδηγό εγκατάστασης του Open eClass!";
$langInstallProgress = "Πορεία εγκατάστασης";
$langThisWizard = "Ο οδηγός αυτός:";
$langWizardHelp1 = "Θα σας βοηθ�?σει να ορίσετε τις ρυθμίσεις για τη βάση δεδομένων";
$langWizardHelp2 = "Θα σας βοηθ�?σει να ορίσετε τις ρυθμίσεις της πλατφόρμας";
$langWizardHelp3 = "Θα δημιουργ�?σει το αρχείο <tt>config.php</tt>";
$langRequiredPHP = "Απαιτούμενα PHP modules";
$langOptionalPHP = "Προαιρετικά PHP modules";
$langOtherReq = "Άλλες απαιτ�?σεις συστ�?ματος";
$langInstallBullet1 = "Μια βάση δεδομένων MySQL, στην οποία έχετε λογαριασμό με δικαιώματα να δημιουργείτε και να διαγράφετε βάσεις δεδομένων.";
$langInstallBullet2 = "Δικαιώματα εγγραφ�?ς στον κατάλογο <tt>include/</tt>.";
$langInstallBullet3 = "Δικαιώματα εγγραφ�?ς στον κατάλογο όπου το Open eClass έχει αποσυμπιεστεί.";
$langCheckReq = "Έλεγχος προαπαιτούμενων προγραμμάτων για τη λειτουργία του Open eClass";
$langInfoLicence = "Tο Open eClass είναι ελεύθερη εφαρμογ�? και διανέμεται σύμφωνα με την άδεια GNU General Public Licence (GPL). <br>Παρακαλούμε διαβάστε την άδεια και κάνετε κλίκ στο 'Αποδοχ�?'";
$langAccept = "Αποδοχ�?";
$langEG	= "π.χ.";
$langDBHost = "Όνομα υπολογιστ�? της Βάσης Δεδομένων";
$langDBLogin = "Όνομα Χρ�?στη για τη Βάση Δεδομένων";
$langDBPassword	= "Συνθηματικό για τη Βάση Δεδομένων";
$langMainDB = "Κύρια Βάση Δεδομένων του Open eClass";
$langAllFieldsRequired	= "όλα τα πεδία είναι υποχρεωτικά";
$langPrintVers = "Εκτυπώσιμη μορφ�?";
$langLocalPath	= "Path του Open eClass στον εξυπηρετητ�?";
$langAdminEmail	= "Email Διαχειριστ�?";
$langAdminName = "Όνομα Διαχειριστ�?";
$langAdminSurname = "Επώνυμο Διαχειριστ�?";
$langAdminLogin	= "Όνομα Χρ�?στη του Διαχειριστ�?";
$langAdminPass	= "Συνθηματικό του Διαχειριστ�?";
$langHelpDeskPhone = "�?ηλέφωνο Helpdesk";
$langHelpDeskFax = "Αριθμός Fax Helpdesk";
$langHelpDeskEmail = "Email Helpdesk";
$langCampusName	= "Όνομα Πλατφόρμας";
$langInstituteShortName  = "Όνομα Ιδρύματος - Οργανισμού";
$langInstituteName = "Website Ιδρύματος - Οργανισμού";
$langInstitutePostAddress = "�?αχ. Διεύθυνση Ιδρύματος - Οργανισμού";
$langWarnHelpDesk = "Προσοχ�?: στο \"Email Helpdesk\" στέλνονται οι αιτ�?σεις καθηγητών για λογαριασμό στην πλατφόρμα";
$langDBSettingIntro = "�?ο πρόγραμμα εγκατάστασης θα δημιουργ�?σει την κύρια βάση δεδομένων του Open eClass. Έχετε υπ'όψιν σας ότι κατά τη λειτουργία της πλατφόρμας θα χρειαστεί να δημιουργηθούν νέες βάσεις δεδομένων (μία για κάθε μάθημα) ";
$langStep1 = "Β�?μα 1 από 6";
$langStep2 = "Β�?μα 2 από 6";
$langStep3 = "Β�?μα 3 από 6";
$langStep4 = "Β�?μα 4 από 6";
$langStep5  = "Β�?μα 5 από 6";
$langStep6 = "Β�?μα 6 από 6";
$langCfgSetting	= "Ρυθμίσεις Συστ�?ματος";
$langDBSetting = "Ρυθμίσεις της MySQL";
$langMainLang	= "Κύρια Γλώσσα Εγκατάστασης";
$langLicence = "Άδεια Χρ�?σης";
$langLastCheck = "�?ελευταίος έλεγχος πριν την εγκατάσταση";
$langRequirements = "Απαιτ�?σεις Συστ�?ματος";
$langInstallEnd	= "Ολοκλ�?ρωση Εγκατάστασης";
$langWarnConfig = "Προσοχ�? !! �?ο αρχείο <b>config.php</b> υπάρχει �?δη στο σύστημά σας!! �?ο πρόγραμμα εγκατάστασης δεν πραγματοποιεί αναβάθμιση. Αν θέλετε να ξανατρέξετε την εγκατάσταση της πλατφόρμας, παρακαλούμε διαγράψτε το αρχείο config.php!";
$langWarnConfig1 = "�?ο αρχείο <b>config.php</b> υπάρχει �?δη στο σύστημά σας";
$langWarnConfig2 = "Αν θέλετε να ξανατρέξετε την εγκατάσταση της πλατφόρμας, παρακαλούμε διαγράψτε το αρχείο <b>config.php</b>";
$langWarnConfig3 = "�?ο πρόγραμμα εγκατάστασης δεν πραγματοποιεί αναβάθμιση";
$langErrorConfig = "<br><b>Παρουσιάστηκε σφάλμα!</b><br><br>Δεν είναι δυνατ�? η δημιουργία του αρχείου config.php.<br><br>Παρακαλούμε ελέγξτε τα δικαιώματα πρόσβασης στους υποκαταλόγους του Open eClass και δοκιμάστε ξανά την εγκατάσταση.";
$langErrorMysql = "Η MySQL  δεν λειτουργεί �? το όνομα χρ�?στη/συνθηματικό δεν είναι σωστό.<br/>Παρακαλούμε ελέγξετε τα στοιχεία σας:";
$langBackStep3 = "Επιστροφ�? στο β�?μα 3";
$langBackStep3_2 = "Eπιστρέψτε στο β�?μα 3 για να τα διορθώσετε.";
$langNotNeedChange = "Δεν χρειάζεται να το αλλάξετε";
$langNeedChangeDB = "αν υπάρχει �?δη κάποια βάση δεδομένων με το όνομα eclass αλλάξτε το";
$langWillWrite = "�?α παρακάτω θα γραφτούν στο αρχείο <b>config.php</b>";
$langProtect = "Συμβουλ�?: Για να προστατέψετε το Open eClass, αλλάξτε τα δικαιώματα πρόσβασης των αρχείων
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
$langWarningInstall1 = "<b>Προσοχ�?!</b> Φαίνεται πως η επιλογ�? register_globals στο αρχείο php.ini δεν είναι ενεργοποιημένη. Χωρίς αυτ�?ν το Open eClass δεν μπορεί να λειτουργ�?σει. Παρακαλούμε διορθώστε το αρχείο php.ini ώστε να περιέχει τη γραμμ�?:</p> <p><b>register_globals = On</b></p><p>Πιθανόν επίσης να χρειάζονται και κάποιες άλλες αλλαγές. Διαβάστε τις οδηγίες εγκατάστασης στο αρχείο <a href='install.html'>install.html</a> και επανεκκινείστε τον οδηγό εγκατάστασης.";
$langWarningInstall2 = "<b>Προσοχ�?!</b> Φαίνεται πως η επιλογ�? short_open_tag στο αρχείο php.ini δεν είναι ενεργοποιημένη. Χωρίς αυτ�?ν το Open eClass δεν μπορεί να λειτουργ�?σει. Παρακαλούμε διορθώστε το αρχείο php.ini ώστε να περιέχει τη γραμμ�?:</p><p><b>short_open_tag = On</b></p><p>Πιθανόν επίσης να χρειάζονται και κάποιες άλλες αλλαγές. Διαβάστε τις οδηγίες εγκατάστασης στο αρχείο <a href='install.html'>install.html</a> και επανεκκινείστε τον οδηγό εγκατάστασης.";
$langWarningInstall3 = "<b>Προσοχ�?!</b> Φαίνεται πως ο οδηγός εγκατάστασης δεν έχει δικαιώματα δημιουργίας του κατάλογου <b>/config</b>.<br/>Χωρίς δικαιώματα δημιουργίας, ο οδηγός εγκατάστασης δεν μπορεί να συνεχίσει. Παρακαλούμε διορθώστε τα δικαιώματα.<br/>Πιθανόν επίσης να χρειάζονται και κάποιες άλλες αλλαγές. Διαβάστε τις οδηγίες εγκατάστασης στο αρχείο <a href='install.html'>install.html</a> και επανεκκινείστε τον οδηγό εγκατάστασης.";
$langWarningInstall4 = "<b>Προσοχ�?!</b> Φαίνεται πως ο οδηγός εγκατάστασης δεν έχει δικαιώματα δημιουργίας του κατάλογου <b>/courses</b>.<br/>Χωρίς δικαιώματα δημιουργίας, ο οδηγός εγκατάστασης δεν μπορεί να συνεχίσει. Παρακαλούμε διορθώστε τα δικαιώματα.<br/>Πιθανόν επίσης να χρειάζονται και κάποιες άλλες αλλαγές. Διαβάστε τις οδηγίες εγκατάστασης στο αρχείο <a href='install.html'>install.html</a> και επανεκκινείστε τον οδηγό εγκατάστασης.";
$langWarningInstall5 = "<b>Προσοχ�?!</b> Φαίνεται πως ο οδηγός εγκατάστασης δεν έχει δικαιώματα δημιουργίας του κατάλογου <b>/video</b>.<br/>Χωρίς δικαιώματα δημιουργίας, ο οδηγός εγκατάστασης δεν μπορεί να συνεχίσει. Παρακαλούμε διορθώστε τα δικαιώματα.<br/>Πιθανόν επίσης να χρειάζονται και κάποιες άλλες αλλαγές. Διαβάστε τις οδηγίες εγκατάστασης στο αρχείο <a href='install.html'>install.html</a> και επανεκκινείστε τον οδηγό εγκατάστασης.";
$langReviewSettings = "�?α στοιχεία που δηλώσατε είναι τα παρακάτω: (Εκτυπώστε τα αν θέλετε να θυμάστε το συνθηματικό του διαχειριστ�? και τις άλλες ρυθμίσεις)";
$langToReq = "Η εγγραφ�? χρηστών θα γίνεται με αίτηση προς τον διαχειριστ�? της πλατφόρμας";
$langLDAPBaseDn = "Base DN του LDAP Εξυπηρέτη";
$langChooseLang = "Επιλογ�? Γλώσσας";
$langExpPhpMyAdmin = "�?ο Open eClass θα εγκαταστ�?σει το δικό του διαχειριστικό εργαλείο μέσω web των βάσεων δεδομένων MySQL (<a href=\"http://www.phpmyadmin.net\" target=_blank>phpMyAdmin</a>) αλλά μπορείτε να χρησιμοποι�?σετε και το δικό σας.";
$langBeforeInstall1 = "Πριν προχωρ�?σετε στην εγκατάσταση τυπώστε και διαβάστε προσεκτικά τις ";
$langBeforeInstall2 = "Επίσης, γενικές οδηγίες για την πλατφόρμα μπορείτε να διαβάσετε ";
$langInstallInstr = "Οδηγίες Εγκατάστασης";
$langWithPHP = "με υποστ�?ριξη PHP";

/********************************************************
* learnpath.inc.php
*********************************************************/
$langAddComment = "Προσθ�?κη / αλλαγ�? σχολίου";
$langLearningModule = "Ενότητα";
$langLearningObjects = "Εκπαιδευτικά Αντικείμενα";
$langLearningObject = "Εκπαιδευτικό Αντικείμενο";
$langLearningObjectsInUse = "Εκπαιδευτικά Αντικείμενα σε χρ�?ση";
$langLearningObjectsInUse_sort = "Αντικείμενα σε χρ�?ση";
$langLearningPathStructure = "Δομ�? Γραμμ�?ς Μάθησης";
$langLearningPathConfigure = "Διαμόρφωση γραμμ�?ς μάθησης";
$langContents = "Περιεχόμενα";
$langLearningPathUploadFile= "Εντοπισμός θέσης του αρχείου γραμμ�?ς μάθησης στον Η/�? σας (τοπικά)";
$langAddModulesButton = "Προσθ�?κη επιλεγμένων";
$langAddOneModuleButton = "Προσθ�?κη ενότητας";
$langAlertBlockingMakedInvisible = "Αυτ�? η ενότητα είναι φραγμένη. Κάνοντας τη αόρατη, θα επιτραπεί στους ".$langsOfStudentss." η είσοδος στην επόμενη ενότητα χωρίς να χρειάζεται να ολοκληρώσουν την παρούσα. Επιβεβαιώστε την επιλογ�? σας";
$langAlertBlockingPathMadeInvisible = "Αυτ�? η γραμμ�? είναι φραγμένη. Κάνοντας την μη ορατ�? θα επιτραπεί στους ".$langsOfStudentss." η είσοδος στην επόμενη γραμμ�? χωρίς να χρειάζεται να ολοκληρώσουν την παρούσα. Επιβεβαιώστε την επιλογ�? σας";
$langAlreadyBrowsed = "Ολοκληρώθηκε";
$langAltMakeNotBlocking = "Αποδέσμευση";
$langAltScorm = "Scorm";
$langAreYouSureDeleteModule = "Είστε βέβαιοι για την συνολικ�? διαγραφ�? της ενότητας;";
$langAreYouSureToDeleteScorm = "H γραμμ�? μάθησης αποτελεί μέρος ενός πακέτου SCORM. Αν διαγράψετε αυτ�? τη γραμμ�?, όλες οι ενότητες που συμβαδίζουν με το SCORM και όλα τα σχετικά αρχεία θα διαγραφούν απο την πλατφόρμα. Σίγουρα θέλετε να διαγράψετε τη γραμμ�? μάθησης ";
$langAreYouSureToRemove = "Σίγουρα θέλετε να αφαιρέσετε την παρακάτω ενότητα από τη γραμμ�? μάθησης: ";
$langAreYouSureToRemoveLabel = "Διαγράφοντας μία ετικέτα θα διαγραφούν και όλες οι ενότητες �? οι ετικέτες που περιέχει.";
$langAreYouSureToRemoveSCORM = "Ενότητες σύμφωνες με το SCORM θα αφαιρεθούν οριστικά, όταν διαγράψετε τη γραμμ�? μάθησης.";
$langAreYouSureToRemoveStd = "Η ενότητα θα παραμείνει διαθέσιμη στην ομάδα των ενοτ�?των.";
$langBackModule = "Επιστροφ�? στη γραμμ�? μάθησης";
$langBackToLPAdmin = "Επιστροφ�? στη διαχείριση της γραμμ�?ς μάθησης";
$langBlock = "Φραγ�?";
$langBrowserCannotSeeFrames = "Ο browser σας δεν αναγνωρίζει frames.";
$langChangeRaw = "Αλλαγ�? του ελάχιστου αρχικού σημείου για να περάσει αυτ�? η ενότητα (ποσοστό): ";
$langChat = "Κουβεντούλα";
$langConfirmYourChoice = "Παρακαλώ επιβεβαιώστε την επιλογ�? σας";
$langCourseDescription = "Περιγραφ�? Μαθ�?ματος";
$langCourseDescriptionAsModule = "Χρ�?ση Περιγραφ�?ς Μαθ�?ματος";
$langCourseDescriptionAsModuleLabel = "Περιγραφ�?ς Μαθ�?ματος";
$langCourseHome = "Αρχικ�? σελίδα μαθ�?ματος";
$langCreateLabel = "Δημιουργία ετικέτας";
$langCreateNewLearningPath = "Δημιουργία νέας γραμμ�?ς μάθησης";
$langDOCUMENTTypeDesc = "Έγγραφο";
$langDefaultLearningPathComment = "Εισαγωγικό κείμενο της γραμμ�?ς μάθησης.";
$langDefaultModuleAddedComment = "Πρόσθετο εισαγωγικό κείμενο σχετικά με την παρουσία της ενότητας στη γραμμ�? μάθησης.";
$langDefaultModuleComment = "Εισαγωγικό κείμενο της ενότητας. Θα εμφανίζεται σε κάθε γραμμ�? μάθησης που θα περιέχει αυτ�? την ενότητα";
$langInstructions = "Οδηγίες";
$langModuleComment_inCurrentLP = "<u>Μόνο</u> για τη συγκεκριμένη γρ. μάθησης";
$langModuleComment_inCourse = "Εμφανίζεται με την <u>επαναχρησιμοποίηση</u> του αντικείμενου σε άλλη γραμμ�? μάθησης";
$langDescriptionCours = "Περιγραφ�? μαθ�?ματος";
$langDocInsertedAsModule = "έχει προστεθεί σαν ενότητα";
$langDocumentAlreadyUsed = "Αυτό το έγγραφο έχει �?δη χρησιμοποιηθεί σαν ενότητα σε αυτ�? τη γραμμ�? μάθησης";
$langDocumentAsModule = "Χρ�?ση Εγγράφου";
$langDocumentAsModuleLabel = "Εγγράφου";
$langDocumentInModule = "Έγγραφο σε ενότητα";
$langEXERCISETypeDesc = "Άσκηση πλατφόρμας";
$langEndOfSteps = "Κάντε κλίκ στη λ�?ξη αφού ολοκληρώσετε αυτό το τελευταίο β�?μα.";
$langErrorAssetNotFound = "�?ο στοιχείο δεν βρέθηκε: ";
$langErrorCopyAttachedFile = "Δεν είναι δυνατ�? η αντιγραφ�? αρχείου: ";
$langErrorCopyScormFiles = "Σφάλμα κατά την αντιγραφ�? των αναγκαίων αρχείων SCORM ";
$langErrorCopyingScorm = "Σφάλμα αντιγραφ�?ς περιεχομένου SCORM";
$langErrorCreatingDirectory = "Δεν είναι δυνατ�? η δημιουργία κατάλογου: ";
$langErrorCreatingFile = "Δεν είναι δυνατ�? η δημιουργία αρχείου: ";
$langErrorCreatingFrame = "Δεν είναι δυνατ�? η δημιουργία στα πλαίσια του αρχείου ";
$langErrorCreatingManifest = "Δεν είναι δυνατ�? η δημιουργία της προκ�?ρυξης SCORM (imsmanifest.xml)";
$langErrorCreatingScormArchive = "Δεν είναι δυνατ�? η δημιουργία του καταλόγου αρχείων SCORM ";
$langErrorEmptyName = "�?ο όνομα πρέπει να συμπληρωθεί";
$langErrorFileMustBeZip = "�?ο αρχείο πρέπει να είναι σε μορφ�? αρχείου .zip";
$langErrorInvalidParms = "Σφάλμα: μη έγκυρη παράμετρος (χρησιμοποι�?στε μόνο αριθμούς)";
$langErrorLoadingExercise = "Δεν είναι δυνατ�? η φόρτωση της άσκησης ";
$langErrorLoadingQuestion = "Δεν είναι δυνατ�? η φόρτωση της ερώτησης της άσκησης ";
$langErrorNameAlreadyExists = "Σφάλμα: �?ο όνομα υπάρχει �?δη στη γραμμ�? μάθησης �? στο σύνολο των ενοτ�?των ";
$langErrorNoModuleInPackage = "Δεν υπάρχει ενότητα στο πακέτο";
$langErrorNoZlibExtension = "Η επέκταση Zlib της php απαιτείται για τη χρ�?ση αυτού του εργαλείου. Παρακαλώ επικοινων�?στε με τον διαχειριστ�? της πλατφόρμας σας.";
$langErrorOpeningManifest = "Δεν μπορεί να βρεθεί το αρχείο <i>manifest</i> στο πακέτο.<br /> Αρχείο που δε βρέθηκε: imsmanifest.xml";
$langErrorOpeningXMLFile = "Δεν μπορεί να βρεθεί το δευτερεύον αρχείο έναρξης στο πακέτο.<br /> Αρχείο που δε βρέθηκε: ";
$langErrorReadingManifest = "Σφάλμα ανάγνωσης αρχείου <i>manifest</i>";
$langErrorReadingXMLFile = "Σφάλμα ανάγνωσης δευτερεύοντος αρχείου ρύθμισης έναρξης: ";
$langErrorReadingZipFile = "Σφάλμα ανάγνωσης αρχείου zip.";
$langErrorSql = "Σφάλμα στη δ�?λωση SQL";
$langErrorValuesInDouble = "Σφάλμα: μία �? δύο τιμές είναι διπλές";
$langErrortExtractingManifest = "Δεν μπορεί να εμφανιστεί απόσπασμα από το αρχείο zip.";
$langExAlreadyUsed = "Αυτ�? η άσκηση �?δη χρησιμοποιείται σαν ενότητα σε αυτ�? τη γραμμ�? μάθησης";
$langExInsertedAsModule = "έχει προστεθεί σαν ενότητα μαθ�?ματος της γραμμ�?ς μάθησης";
$langExercise = "Ασκ�?σεις";
$langExerciseAsModule = "Χρ�?ση Άσκησης";
$langExerciseAsModuleLabel = "Άσκησης";
$langExerciseCancelled = "Ακύρωση άσκησης, επιλέξτε την επόμενη ενότητα για να συνεχίσετε, κάνοντας κλίκ στο επόμενο β�?μα.";
$langExerciseDone = "Ολοκλ�?ρωση άσκησης, επιλέξτε την επόμενη ενότητα για να συνεχίσετε, κάνοντας κλίκ στο επόμενο β�?μα.";
$langExerciseInModule = "Ασκηση στην ενότητα";
$langExercises = "Ασκ�?σεις";
$langExport = "Εξαγωγ�?";
$langExport2004 = "Εξαγωγ�? σε πρότυπο SCORM 2004";
$langExport12 = "Εξαγωγ�? σε πρότυπο SCORM 1.2";
$langFailed = "Ολοκληρώθηκε ανεπιτυχώς";
$langFileScormError = "�?ο αρχείο που θα ενημερωθεί δεν είναι έγκυρο.";
$langFileName = "Όνομα αρχείου";
$langFullScreen = "Πλ�?ρης οθόνη ";
$langGlobalProgress = "Πρόοδος της γραμμ�?ς μάθησης: ";
$langImport = "Εισαγωγ�?";
$langInFrames = "Σε πλαίσια";
$langInfoProgNameTitle = "Πληροφορία";
$langInsertMyDescToolName = "Εισαγωγ�? περιγραφ�?ς μαθ�?ματος";
$langInsertMyDocToolName = "Εισαγωγ�? εγγράφου";
$langInsertMyExerciseToolName = "Εισαγωγ�? άσκησης";
$langInsertMyLinkToolName = "Εισαγωγ�? συνδέσμου";
$langInsertMyModuleToolName = "Εισαγωγ�? ενότητας";
$langInsertMyModulesTitle = "Επαναχρησιμοποίηση ενότητας του μαθ�?ματος";
$langInsertNewModuleName = "Εισαγωγ�? νέου ονόματος";
$langInstalled = "Η γραμμ�? μάθησης έχει εισαχθεί με επιτυχία.";
$langIntroLearningPath = "Χρησιμοπο�?στε αυτό το εργαλείο για να παρέχετε στους ".$langsOfStudentss." μια γραμμ�? μάθησης μεταξύ εγγράφων, ασκ�?σεων, σελίδες HTML, συνδέσεις κ.λπ.<br /><br />Εάν επιθυμείτε να παρουσιάσετε στους ".$langsOfStudentss." τη γραμμ�? μάθησης σας, κάντε κλικ παρακάτω.<br />";
$langLINKTypeDesc = "Σύνδεσμος";
$langLastName = "Επίθετο";
$langLastSessionTimeSpent = "�?ελευταία χρονικ�? συνεδρία";
$langLearningPath = "Γραμμ�? μάθησης";
$langLearningPaths = "Γραμμές μάθησης";
$langLearningPath1 = "γραμμ�?ς μάθησης";
$langLearningPathEmpty = "Η γραμμ�? μάθησης είναι κεν�?";
$langLearningPathList = "Διαθέσιμες γραμμές μάθησης";
$langLearningPathName = "Όνομα νέας γραμμ�?ς μάθησης";
$langLearningPathData = "Στοιχεία γραμμ�?ς μάθησης";
$langLearningObjectData = "Στοιχεία Εκπαιδευτικού Αντικείμενου";
$langLearningPathNotFound = "Η γραμμ�? μάθησης δεν βρέθηκε ";
$langLessonStatus = "Κατάσταση ενότητας";
$langLinkAlreadyUsed = "Αυτός ο σύνδεσμος �?δη χρησιμοποιείται σαν ενότητα σε αυτ�?ν τη γραμμ�? μάθησης";
$langLinkAsModule = "Χρ�?ση Συνδέσμου";
$langLinkAsModuleLabel = "Συνδέσμου";
$langLinkInsertedAsModule = "Έχει προστεθεί σαν ενότητα μαθ�?ματος αυτ�?ς της γραμμ�?ς μάθησης";
$langLogin = "Είσοδος";
$langMaxFileSize = "Μέγιστο μέγεθος αρχείου: ";
$langMinuteShort = "ελαχ.";
$langModuleMoved = "Μετακίνηση ενότητας";
$langModuleOfMyCourse = "Χρ�?ση ενότητας του μαθ�?ματος";
$langModuleOfMyCourseLabel = "Eτικέτας του μαθ�?ματος";
$langModuleOfMyCourseLabel_onom = "Eτικέτα του μαθ�?ματος";
$langModuleStillInPool = "Ενότητες αυτ�?ς της γραμμ�?ς θα είναι ακόμα διαθέσιμες στο σύνολο των ενοτ�?των";
$langModulesPoolToolName = "Σύνολο ενοτ�?των";
$langMyCourses = "�?α μαθ�?ματά μου";
$langNeverBrowsed = "Δεν έχει ολοκληρωθεί";
$langNewLabel = "Δημιουργία νέας ετικέτας";
$langLabel = "Eτικέτα";
$langNext = "Επόμενο";
$langNextPage = "Επόμενη Σελίδα";
$langNoEmail = "Δεν έχει οριστεί email";
$langNoLearningPath = "Δεν υπάρχουν γραμμές μάθησης";
$langNoModule = "Δεν έχουν χρησιμοποιηθεί εκπαιδευτικά αντικείμενα";
$langNoMoreModuleToAdd = "Όλες οι ενότητες αυτού του μαθ�?ματος �?δη χρησιμοποι�?θηκαν σε αυτ�? τη γραμμ�? μάθησης.";
$langNoStartAsset = "Δεν υπάρχει κανένα απόκτημα/στοιχείο έναρξης που να ορίζεται για αυτ�? την ενότητα.";
$langNotAttempted = "Δεν έχει επιχειρηθεί";
$langNotInstalled = "Προέκυψε σφάλμα. Η εισαγωγ�? της γραμμ�?ς μάθησης απέτυχε.";
$langOkChapterHeadAdded = "Ο τίτλος προστέθηκε: ";
$langOkDefaultCommentUsed = "προειδοποίηση: Η εγκατάσταση δε μπορεί να βρεί την περιγραφ�? της γραμμ�?ς μάθησης και έχει χρησιμοποι�?σει ένα προκαθορισμένο σχόλιο.  Θα πρέπει να το αλλάξετε";
$langOkDefaultTitleUsed = "προειδοποίηση: Η εγκατάσταση δε μπορεί να βρεί το όνομα της γραμμ�?ς μάθησης και έχει ορίσει καποιο προκαθορισμένο όνομα. Θα πρέπει να το αλλάξετε.";
$langOkFileReceived = "�?ο αρχείο ελ�?φθη: ";
$langOkManifestFound = "Η ανακοίνωση βρέθηκε σε αρχείο zip: ";
$langOkManifestRead = "H ανακοίνωση διαβάστηκε.";
$langOkModuleAdded = "Προσθ�?κη ενότητας: ";
$langOrder = "Εντολ�? ";
$langOtherCourses = "Λίστα Μαθημάτων";
$langPassed = "Ολοκληρώθηκε με επιτυχία";
$langPathContentTitle = "Περιεχόμενο γραμμ�?ς μάθησης";
$langPathsInCourseProg = "Πρόοδος μαθ�?ματος ";
$langPeriodDayShort = "μ.";
$langPeriodHourShort = "ω.";
$langPersoValue = "Αξιολόγηση";
$langPlatformAdministration = "Διαχείριση Πλατφόρμας";
$langPrevious = "Προηγούμενο";
$langPreviousPage = "Προηγούμενη Σελίδα";
$langProgInModuleTitle = "Η πρόοδος σου σε αυτ�? την ενότητα";
$langProgress = "Πρόοδος";
$langQuitViewer = "Επιστροφ�? στη λίστα";
$langRawHasBeenChanged = "Ο ελάχιστος βαθμός για προαγωγ�? έχει αλλαχθεί";
$langSCORMTypeDesc = "SCORM προσαρμοσμένο περιεχόμενο";
$langScormIntroTextForDummies = "�?α εισαγόμενα πακέτα πρέπει να αποτελούνται από ένα αρχείο zip και να είναι συμβατά με:
   <ul>
     <li> το SCORM 2004 �?</li>
     <li> το SCORM 1.2.</li>
   </ul>";
$langSecondShort = "δευτ.";
$langStartModule = "Έναρξη ενότητας";
$langStatsOfLearnPath = "Παρακολούθηση γραμμ�?ς μάθησης";
$langTrackAllPath = "Παρακολούθηση γραμμών μάθησης";
$langSwitchEditorToTextConfirm = "Η εντολ�? θα αφαιρέσει τη τρέχουσα διάταξη κειμένου. Θέλετε να συνεχίσετε;";
$langTextEditorDisable = "Απενεργοποίηση επεξεργαστ�? κειμένου";
$langTextEditorEnable = "Ενεργοποίηση επεξεργαστ�? κειμένου";
$langTimeInLearnPath = "Χρόνος στη γραμμ�? μάθησης";
$langTo = "στο";
$langTotalTimeSpent = "Σύνολο χρόνου";
$langTrackAllPathExplanation = "Πρόοδος ".$langsOfStudents;
$langTrackUser = "Πρόοδος ".$langOfStudent;
$langTracking = "Παρακολούθηση";
$langTypeOfModule = "�?ύπος ενότητας";
$langUnamedModule = "Ενότητα χωρίς όνομα";
$langUnamedPath = "Γραμμ�? χωρίς όνομα";
$langUseOfPool = "Μπορείτε να δείτε όλες τις διαθέσιμες ενότητες στο μάθημα. <br /> Όποια άσκηση �? έγγραφο έχει προστεθεί στη γραμμ�? μάθησης εμφανίζεται παρακάτω.";
$langUsedInLearningPaths = "Αριθμός διαδρομών μάθησης που χρησιμοποιούν αυτ�? την ενότητα: ";
$langView = "Εμφάνιση";
$langViewMode = "Παρουσίαση τρόπου";
$langVisibility = "Ορατό / Αόρατο";
$langWork = "Εργασίες ".$langOfStudents;
$langWrongOperation = "Λανθασμένη λειτουργία";
$langYourBestScore = "Η καλύτερη σου βαθμολογία";
$lang_enroll = "Eγγραφ�?";
$langimportLearningPath = "Εισαγωγ�? γραμμ�?ς μάθησης";
$langScormErrorExport = "Σφάλμα κατά την εξαγωγ�? του πακέτου SCORM";

/*************************************************
* lessontools.inc.php
**************************************************/
$langActiveTools="Ενεργά εργαλεία";
$langAdministrationTools="Εργαλεία διαχείρισης";
$langAdministratorTools="Εργαλεία διαχειριστ�?";
$langCourseTools="Εργαλεία μαθ�?ματος";

/**************************************************
* link.inc.php
***************************************************/
$langLinks="Σύνδεσμοι";
$langListDeleted="Ο κατάλογος έχει διαγραφεί";
$langLinkMod="Ο σύνδεσμος τροποποι�?θηκε";
$langLinkModify = "Αλλαγ�? σύνδεσμου";
$langLinkDeleted="Ο σύνδεσμος διαγράφηκε";
$langLinkName="Όνομα συνδέσμου";
$langLinkAdd="Προσθ�?κη συνδέσμου";
$langLinkAdded="Ο σύνδεσμος προστέθηκε";
$langLinkDelconfirm = "Θέλετε να διαγράψετε τον σύνδεσμο;";
$langCategoryName="Όνομα κατηγορίας";
$langCategoryAdd = "Προσθ�?κη κατηγορίας";
$langCategoryAdded = "Η κατηγορία προστέθηκε";
$langCategoryMod = "Αλλαγ�? κατηγορίας";
$langCategoryModded = "Η κατηγορία άλλαξε";
$langCategoryDel = "Διαγραφ�? κατηγορίας";
$langCategoryDeleted = "Η κατηγορία διαγράφηκε μαζί με όλους τους συνδέσμους της";
$langCatDel = "Οταν διαγράψετε μια κατηγορία, θα διαγραφούν όλοι οι σύνδεσμοι της κατηγορίας.\\n".
"Είστε βέβαιος ότι θέλετε να διαγράψετε την κατηγορία; ";
$langAllCategoryDel = "Διαγραφ�? όλων των καταλόγων και όλων των συνδέσμων";
$langAllCategoryDeleted = "Όλες οι κατηγορίες και όλοι οι σύνδεσμοι έχουν διαγραφεί";
$langGiveURL = "Δώστε το URL του συνδέσμου";
$langGiveCategoryName = "Όνομα κατηγορίας";
$langNoCategory = "Γενικοί σύνδεσμοι";
$langCategorisedLinks = "Κατηγοριοποιημένοι σύνδεσμοι";
$showall = "Εμφάνιση";
$shownone = "Απόκρυψη";
$langProfNoLinksExist = "<br />Δεν υπάρχουν σύνδεσμοι! <br /><br />Μπορείτε να χρησιμοποι�?σετε τις λειτουργίες του εργαλείου για να προσθέσετε σύνδεσμους.";
$langNoLinksExist = "Δεν έχουν προστεθεί σύνδεσμοι.";

/*****************************************************************
* lostpass.inc.php
*****************************************************************/
$lang_remind_pass = 'Ορισμός νέου συνθηματικού';
$lang_pass_intro = '<p>Αν έχετε ξεχάσει τα στοιχεία του λογαριασμού σας, συμπληρώστε το <em>όνομα χρ�?στη</em>
και την διεύθυνση ηλεκτρονικού ταχυδρομείου με την οποία είστε εγγεγραμμένος
(<em>προσοχ�?: αυτ�? που έχετε δηλώσει στην πλατφόρμα</em>).</p> <p>Στη συνέχεια θα παραλάβετε ένα μ�?νυμα σε αυτ�? τη
διεύθυνση με οδηγίες για να αλλάξετε το συνθηματικό σας.</p>';
$lang_pass_submit = 'Αποστολ�?';
$lang_pass_invalid_mail1 = 'H διεύθυνση ηλεκτρονικού ταχυδρομείου που δώσατε,';
$lang_pass_invalid_mail2 = 'δεν είναι έγκυρη. Αν κάνατε λάθος, δοκιμάστε ξανά. Διαφορετικά και εφόσον είστε βέβαιοι ότι έχετε λογαριασμό στην πλατφόρμα, παρακαλούμε να επικοινων�?σετε με τους διαχειριστές της πλατφόρμας';
$lang_pass_invalid_mail3 = 'δίνοντας τα στοιχεία σας όπως το ονοματεπώνυμο σας �?/και το όνομα χρ�?στη';
$langPassResetIntro ="
Έχει ζητηθεί να οριστεί νέο συνθηματικό πρόσβασης σας στην
πλατφόρμα τηλεκπαίδευσης $siteName. Αν δεν ζητ�?σατε εσείς αυτ�? την ενέργεια,
απλώς αγνο�?στε τις οδηγίες αυτού του μηνύματος και αναφέρετε το γεγονός αυτό
στο διαχειριστ�? του συστ�?ματος, στην διεύθυνση: ";
$langHowToResetTitle = "

===============================================================================
			Οδηγίες ορισμού νέου συνθηματικού
===============================================================================
";

$langPassResetGoHere = "
Για να ορίσετε νέο συνθηματικό πηγαίνετε στην παρακάτω διεύθυνση.
Αν δεν μπορείτε να μεταβείτε κάνοντας κλικ πάνω στη διεύθυνση αυτ�?, αντιγράψτε
την στη μπάρα διευθύνσεων του φυλλομετρητ�? σας. Η διεύθυνση αυτ�? έχει ισχύ
μίας (1) ώρας. Πέραν αυτού του χρονικού ορίου θα πρέπει να κάνετε από την αρχ�?
τη διαδικασία επανατοποθέτησης συνθηματικού.
";

$langPassEmail1 = "�?ο συνθηματικό σας έχει οριστεί ξανά επιτυχώς. �?ο νέο σας συνθηματικό είναι αυτό που ακολουθεί:";
$langPassEmail2 = "Για λόγους ασφάλειας, παρακαλούμε αλλάξτε το συνθηματικό το συντομότερο δυνατόν, σε κάτι
που μόνο εσείς το γνωρίζετε, μόλις συνδεθείτε στην πλατφόρμα.";
$langAccountResetSuccess1="Ο ορισμός νέου συνθηματικού σας έχει ολοκληρωθεί";
$langAccountResetInvalidLink="Ο σύνδεσμος που ακολουθ�?σατε δεν ισχύει πλέον. Παρακαλούμε επαναλάβετε από την αρχ�? την διαδικασία.";
$langAccountEmailError1 = "Παρουσιάστηκε σφάλμα κατά την αποστολ�? των στοιχείων σας";
$langAccountEmailError2 = "Δεν �?ταν δυνατ�? η αποστολ�? των οδηγιών επανατοποθέτησης του συνθηματικού σας στη διεύθυνση";
$langAccountEmailError3 = 'Αν χρειαστεί, μπορείτε να επικοινων�?σετε με τους διαχειριστές του συστ�?ματος στη διεύθυνση';
$lang_pass_email_ok = '�?α στοιχεία του λογαριασμού σας βρέθηκαν και στάλθηκαν
	μέσω ηλεκτρονικού ταχυδρομείου στη διεύθυνση';
$langAccountNotFound1 = 'Δε βρέθηκε λογαριασμός στο σύστημα με τη διεύθυνση ηλεκτρονικού ταχυδρομείου που δώσατε';
$langAccountNotFound2 = 'Αν παρόλα αυτά είστε σίγουρος ότι έχετε �?δη λογαριασμό, παρακαλούμε επικοινων�?στε με τους διαχειριστές του συστ�?ματος στη διεύθυνση ';
$langAccountNotFound3 = 'δίνοντας και στοιχεία που μπορούν να βοηθ�?σουν στο να βρούμε το λογαριασμό σας, όπως ονοματεπώνυμο, σχολ�?/τμ�?μα, κλπ.';
$lang_email = 'e-mail';
$lang_send = 'Αποστολ�?';
$lang_username="Όνομα χρ�?στη";
$langPassCannotChange1="�?ο συνθηματικό αυτού του λογαριασμού δεν μπορεί να αλλαχθεί.";
$langPassCannotChange2="Ο λογαριασμός αυτός χρησιμοποιεί εξωτερικ�? μέθοδο πιστοποίησης. Παρακαλούμε, επικοινων�?στε με το διαχειριστ�? στην διεύθυνση";
$langPassCannotChange3="για περισσότερες πληροφορίες.";

/******************************************************
* manual.inc.php
*******************************************************/
$langIntroMan = "Στην ενότητα αυτ�? υπάρχουν διαθέσιμα χρ�?σιμα εγχειρίδια που αφορούν την περιγραφ�?, τη λειτουργία και τις δυνατότητες της πλατφόρμας $siteName";
$langFinalDesc = "Αναλυτικ�? Περιγραφ�? $siteName";
$langShortDesc = "Σύντομη Περιγραφ�? $siteName";
$langManS = "Εγχειρίδιο $langOfStudent";
$langManT = "Εγχειρίδιο $langOfTeacher";
$langOr = "�?";
$langNote = "Σημείωση";
$langAcrobat = "Για να διαβάσετε τα αρχεία PDF μπορείτε να χρησιμοποι�?σετε το πρόγραμμα Acrobat Reader";
$langWhere ="που θα βρείτε";
$langHere = "εδώ";

/*********************************************************
* opencours.inc.php
*********************************************************/
$langListFac="Κατάλογος Μαθημάτων / Επιλογ�? �?μ�?ματος";
$listtomeis = "�?ομείς";
$langDepartmentsList = "Ακολουθεί ο κατάλογος τμημάτων του ιδρύματος.
	Επιλέξτε οποιοδ�?ποτε από αυτά για να δείτε τα διαθέσιμα σε αυτό μαθ�?ματα.";
$langWrongPassCourse = "Λάθος συνθηματικό για το μάθημα";
$langAvCourses = "διαθέσιμα μαθ�?ματα";
$langAvCours = "διαθέσιμο μάθημα";
$m['begin'] = 'αρχ�?';
$m['department'] = 'Σχολ�? / �?μ�?μα';
$m['lessoncode'] = 'Όνομα Μαθ�?ματος (κωδικός)';
$m['tomeis'] = '�?ομείς';
$m['tomeas'] = '�?ομέας';
$m['open'] = 'Ανοικτά μαθ�?ματα (Ελεύθερη Πρόσβαση)';
$m['restricted'] = 'Ανοικτά μαθ�?ματα με εγγραφ�? (Απαιτείται λογαριασμός χρ�?στη)';
$m['closed'] = 'Κλειστά μαθ�?ματα';
$m['title'] = '�?ίτλος';
$m['description'] = 'Περιγραφ�?';
$m['professor'] = $langTeacher;
$m['type']  = 'Κατηγορία μαθ�?ματος';
$m['pre']  = 'Προπτυχιακό';
$m['post']  = 'Μεταπτυχιακό';
$m['other']  = 'Αλλο';
$m['pres']  = 'Προπτυχιακά';
$m['posts']  = 'Μεταπτυχιακά';
$m['others']  = 'Αλλα';
$m['legend'] = '�?πόμνημα';
$m['legopen'] = 'Ανοικτό Μάθημα';
$m['legrestricted'] = 'Απαιτείται εγγραφ�?';
$m['legclosed'] = 'Κλειστό μάθημα';
$m['nolessons'] = 'Δεν υπάρχουν διαθέσιμα μαθ�?ματα!';
$m['type']="�?ύπος";
$m['name']="Μάθημα";
$m['code']="Κωδικός μαθ�?ματος";
$m['prof']=$langTeacher;
$m['mailprof'] = "Για να εγγραφείτε στο μάθημα θα πρέπει να στείλετε mail στον διδάσκοντα του μαθ�?ματος
κάνοντας κλικ";
$m['here'] = " εδώ.";
$m['unsub'] = "�?ο μάθημα είναι κλειστό και δεν μπορείτε να απεγγραφείτε";

/***************************************************************
* pedasugggest.inc.php
****************************************************************/

$titreBloc = array("Περιεχόμενο Μαθ�?ματος", "Εκπαιδευτικές Δραστηριότητες",
"Βοηθ�?ματα", "Ανθρώπινο Δυναμικό", "�?ρόποι αξιολόγησης / εξέτασης",
"Συμπληρωματικά Στοιχεία");
$titreBlocNotEditable = array(TRUE, TRUE, TRUE, TRUE, TRUE, FALSE);


/********************************************************************
* perso.inc.php
*********************************************************************/
$langPerso = "Αλλαγ�? εμφάνισης χαρτοφυλακίου";
$langMyPersoLessons = "�?Α ΜΑΘΗΜΑ�?Α ΜΟ�?";
$langMyPersoDeadlines = "ΟΙ ΔΙΟΡΙΕΣ ΜΟ�?";
$langMyPersoAnnouncements = "ΟΙ �?ΕΛΕ�?�?ΑΙΕΣ ΜΟ�? ΑΝΑΚΟΙΝΩΣΕΙΣ";
$langMyPersoDocs = "�?Α �?ΕΛΕ�?�?ΑΙΑ ΜΟ�? ΕΓΓΡΑΦΑ";
$langMyPersoAgenda = "Η Α�?ΖΕΝ�?Α ΜΟ�?";
$langMyPersoForum = "ΟΙ Σ�?ΖΗ�?ΗΣΕΙΣ ΜΟ�? - �?ΕΛΕ�?�?ΑΙΕΣ ΑΠΟΣ�?ΟΛΕΣ";
$langAssignment = "Εργασία";
$langDeadline = "Λ�?ξη";
$langNoEventsExist="Δεν υπάρχουν γεγονότα";
$langNoAssignmentsExist="Δεν υπάρχουν εργασίες προς παράδοση";
$langNoAnnouncementsExist="Δεν υπάρχουν ανακοινώσεις";
$langNoDocsExist="Δεν υπάρχουν έγγραφα";
$langNoPosts="Δεν υπάρχουν αποστολές στις περιοχές συζητ�?σεων";
$langNotEnrolledToLessons="Δεν είστε εγγεγραμμένος/η σε μαθ�?ματα";
$langMore="Περισσότερα";
$langSender="Αποστολέας";
$langUnknown="μη ορισμένη";
$langDuration="Διάρκεια";
$langClassic = "Συνοπτικό";
$langModern = "Αναλυτικό";

/***********************************************************
* phpbb.inc.php
************************************************************/
$langAdm="Διαχείριση";
$langQuote="quote";
$langEditDel="αλλαγ�?/διαγραφ�?";
$langSeen="�?ο έχουν δει";
$langLastMsg="�?ελευταίο μ�?νυμα";
$langLoginBeforePost1 = "Για να στείλετε μηνύματα, ";
$langLoginBeforePost2 = "πρέπει προηγουμένως να ";
$langLoginBeforePost3 = "κάνετε login στην πλατφόρμα";
$langPages = "Σελίδες";

// page_header.php

$langNewTopic="Νέο θέμα";
$langTopicData="Στοιχεία θέματος";
$langTopicAnswer="Απάντηση στο θέμα συζ�?τησης";
$langGroupDocumentsLink="Έγγραφα ομάδας ";
$l_forum 	= "Περιοχ�? συζητ�?σεων";
$l_forums	= "Περιοχές συζητ�?σεων";
$l_topic	= "Θέμα";
$l_topics 	= "Θέματα";
$l_replies	= "Απαντ�?σεις";
$l_poster	= "Αποστολέας";
$l_author	= "Συγγραφέας";
$l_views	= "Όψεις";
$l_post 	= "Αποστολ�?";
$l_posts 	= "Αποστολές";
$l_message	= "Μ�?νυμα";
$l_messages	= "Μηνύματα";
$l_all      = "όλα";
$l_pages    = "σελίδες";
$l_subject	= "Θέμα";
$l_body		= "Σώμα μηνύματος";
$l_from		= "Από";   // Message from
$l_moderator 	= "Συντονιστ�?ς";
$l_username 	= "Όνομα χρ�?στη";
$l_password 	= "Συνθηματικό";
$l_email 	= "Email";
$l_emailaddress	= "Διεύθυνση Email";
$l_preferences	= "Προτιμ�?σεις";
$l_postTitle	= "Θέμα δημοσίευσης";

$l_anonymous	= "Ανώνυμος";  // Post
$l_guest	= "Φιλοξενούμενος"; // Whosonline
$l_noposts	= "Δεν υπάρχουν αποστολές";
$l_joined	= "Προσχώρηση";
$l_gotopage	= "Π�?γαινε σε σελίδα";
$l_nextpage 	= "Επόμενη";
$l_prevpage     = "Προηγούμενη";
$l_go		= "Π�?γαινε";
$l_selectforum	= "Επιλογ�? $l_forum";
$l_date		= "Ημερομηνία";
$l_number	= "Αριθμός";
$l_name		= "Όνομα";
$l_options 	= "Επιλογές";
$l_submit	= "�?ποβολ�?";
$l_confirm 	= "Επιβεβαίωση";
$l_enter 	= "Είσοδος";
$l_by		= "από";
$l_ondate	= "στις";
$l_new          = "Νέο";
$l_html		= "HTML";
$l_bbcode	= "BBcode";
$l_smilies	= "Smilies";
$l_on		= "On";
$l_off		= "Off";
$l_yes		= "Ναι";
$l_no		= "Όχι";
$l_click 	= "Πατ�?στε";
$l_here 	= "εδώ";
$l_toreturn	= " για επιστροφ�?";
$l_returnindex	= "$l_toreturn στο ευρετ�?ριο περιοχών συζητ�?σεων.";
$l_returntopic	= "επιστροφ�? στην περιοχ�? συζητ�?σεων";
$l_error	= "Σφάλμα";
$l_tryagain	= "Παρακαλούμε επιστρέψτε στην προηγούμενη σελίδα και ξαναδοκιμάστε.";
$l_mismatch 	= "�?α συνθηματικά δεν είναι ίδια.";
$l_userremoved 	= "Ο χρ�?στης αυτός έχει διαγραφεί από τον κατάλογο χρηστών";
$l_wrongpass	= "Δώσατε λάθος συνθηματικό.";
$l_userpass	= "Παρακαλούμε δώστε το όνομα χρ�?στη και το συνθηματικό σας.";
$l_banned 	= "Σας έχει απαγορευτεί η πρόσβαση σε αυτ�? την περιοχ�?. Αν έχετε κάποια ερώτηση επικοινων�?στε με το διαχειριστ�? του συστ�?ματος.";
$l_enterpassword= "Πρέπει να δώσετε το συνθηματικό σας";
$l_nopost	= "Δεν έχετε δικαίωμα αποστολ�?ς μηνυμάτων σε αυτ�? την περιοχ�?.";
$l_noread	= "Δεν έχετε δικαίωμα ανάγνωσης αυτ�?ς της περιοχ�?ς.";
$l_lastpost 	= "�?ελευταία $l_post";
$l_sincelast	= "από την προηγούμενη επίσκεψ�? σας.";
$l_newposts 	= "�?πάρχουν νέα $l_posts $l_sincelast";
$l_nonewposts 	= "Δεν υπάρχουν νέα $l_posts $l_sincelast";
// Index page
$l_indextitle	= "Ευρετ�?ριο περιοχών συζητ�?σεων";
// Members and profile
$l_profile	= "Προφίλ";
$l_register	= "Καταχώρηση";
$l_onlyreq 	= "Απαιτείται μόνο αν αλλάζει";
$l_location 	= "Από";
$l_viewpostuser	= "Εμφάνιση μηνυμάτων μόνο αυτού του χρ�?στη";
$l_perday       = "$l_messages ανά ημέρα";
$l_oftotal      = "του συνόλου";
$l_url 		= "URL";
$l_icq 		= "ICQ";
$l_icqnumber	= "Αριθμός ICQ";
$l_icqadd	= "Προσθ�?κη";
$l_icqpager	= "Pager";
$l_aim 		= "AIM";
$l_yim 		= "YIM";
$l_yahoo 	= "Yahoo Messenger";
$l_msn 		= "MSN";
$l_messenger	= "MSN Messenger";
$l_website 	= "Διεύθυνση ιστοσελίδας";
$l_occupation 	= "Επάγγελμα";
$l_interests 	= "Ενδιαφέροντα";
$l_signature 	= "�?πογραφ�?";
$l_sigexplain 	= "Ένα κείμενο που επισυνάπτεται στο τέλος των μηνυμάτων σας.<BR>Μέγιστο μ�?κος 255 χαρακτ�?ρες!";
$l_usertaken	= "�?ο $l_username που επιλέξατε χρησιμοποιείται �?δη.";
$l_userdisallowed= "�?ο $l_username που επιλέξατε δεν επιτρέπεται από το διαχειριστ�?. $l_tryagain";
$l_infoupdated	= "Οι πληροφορίες σας ενημερώθηκαν";
$l_publicmail	= "Εμφάνιση της διεύθυνσης email σας στους άλλους χρ�?στες";
$l_itemsreq	= "�?α στοιχεία που σημειώνονται με * είναι υποχρεωτικά";

// Viewforum
$l_viewforum	= "Εμφάνιση περιοχ�?ς συζητ�?σεων";
$l_notopics	= "Δεν υπάρχουν θέματα σε αυτ�? την περιοχ�?. Μπορείτε να ξεκιν�?σετε ένα νέο.";
$l_noforum	= "Δεν υπάρχουν περιοχές συζ�?τησης σε αυτ�? την κατηγορία.";
$l_hotthres	= "To όριο των μηνυμάτων ξεπεράστηκε";
$l_islocked	= "�?ο θέμα είναι κλειδωμένο (δεν μπορούν να αποσταλούν νέα μηνύματα σε αυτό)";
$l_moderatedby	= "Συντονιστ�?ς: ";
// Private forums
$l_privateforum	= "Αυτ�? η περιοχ�? συζητ�?σεων είναι <b>προσωπικ�?</b>.";
$l_private 	= "$l_privateforum<br>Σημείωση: πρέπει να έχετε ενεργοποιημένα τα cookies για να χρησιμοποι�?σετε προσωπικές περιοχές.";
$l_noprivatepost = "$l_privateforum Δεν έχετε πρόσβαση αποστολ�?ς μηνυμάτων σε αυτ�? την περιοχ�?.";
// Viewtopic
$l_topictitle	= "Εμφάνιση θέματος";
$l_unregistered	= "Μη καταχωρημένος χρ�?στης";
$l_posted	= "Στάλθηκε";
$l_profileof	= "Εμφάνιση προφίλ του";
$l_viewsite	= "Μετάβαση στην ιστοσελίδα του";
$l_icqstatus	= "$l_icq status";  // ICQ status
$l_editdelete	= "Διόρθωση / διαγραφ�? του μηνύματος";
$l_replyquote	= "Απάντηση με παράθεση";
$l_viewip	= "Εμφάνιση IP αποστολέα (μόνο για διαχειριστές/συντονιστές)";
$l_locktopic	= "Κλείδωμα αυτού του θέματος";
$l_unlocktopic	= "Ξεκλείδωμα αυτού του θέματος";
$l_movetopic	= "Μεταφορά αυτού του θέματος";
$l_deletetopic	= "Διαγραφ�? αυτού του θέματος";

$l_ViewMessage	= "Εμφάνιση μηνύματος";

$langErrorConnectForumDatabase="Παρουσιάστηκε πρόβλημα. Αδύνατη η σύνδεση με τη βάση δεδομένων του Forum.";
$langErrorForumSelect= "Η περιοχ�? συζητ�?σεων που επιλέξατε δεν υπάρχει. Παρακαλώ προσπαθ�?στε ξανά.";
$langErrorTopicsQuery="Παρουσιάστηκε σφάλμα. Αδύνατη η εκτέλεση της εντολ�?ς σας στη βάση δεδομένων των θεμάτων.<br>";
$langErrorTopicsQueryDatabase="Παρουσιάστηκε σφάλμα. Αδύνατη η εκτέλεση της εντολ�?ς σας στη βάση δεδομένων των θεμάτων.";
$langUnableGetCategories="Αδύνατη η εμφάνιση κατηγοριών των περιοχών συζητ�?σεων ";
$langErrorGetForumData="Σφάλμα κατά την ανάκτηση δεδομένων των περιοχών συζητ�?σεων";
$langErrorConnectForumDatabase="Αδύνατη η σύνδεση με τη βάση δεδομένων των περιοχών συζητ�?σεων.";
$langErrorTopicSelect="Η περιοχ�? συζητ�?σεων που επιλέξατε δεν υπάρχει. Παρακαλώ προσπαθ�?στε ξανά.";
$langUnableEnterData="Αδύνατη η εισαγωγ�? δεδομένων στη βάση. Παρακαλώ προσπαθ�?στε ξανά.";
$langUnableEnterText="Αδύνατη η εισαγωγ�? κειμένου!<br>Αιτία";

// Functions
$l_loggedinas	= "Συνδεδεμένος ως";
$l_notloggedin	= "Μη συνδεδεμένος";
$l_logout	= "Αποσύνδεση";
$l_login	= "Σύνδεση";

// Page_header
$l_separator	= "» »";  // Included here because some languages have
		          // problems with high ASCII (Big-5 and the like).
$l_editprofile	= "Μεταβολ�? προφίλ";
$l_editprefs	= "Μεταβολ�? προτιμ�?σεων";
$l_search	= "Αναζ�?τηση";
$l_memberslist	= "Λίστα μελών";
$l_faq		= "FAQ";
$l_privmsgs	= "Προσωπικά μηνύματα";
$l_sendpmsg	= "Αποστολ�? προσωπικού μηνύματος";
$l_statsblock   = '$statsblock = "Οι χρ�?στες μας έχουν στείλει συνολικά -$total_posts- μηνύματα.<br>
Έχουμε -$total_users- καταχωρημένους χρ�?στες.<br>
Ο νεότερος καταχωρημένος χρ�?στης: -<a href=\"$profile_url\">$newest_user</a>-.<br>
-$users_online- ". ($users_online==1?"χρ�?στης":"χρ�?στες") ." <a href=\"$online_url\">διαβάζουν αυτ�? τη στιγμ�?</a> τις περιοχές συζητ�?σεων.<br>";';
$l_privnotify   = '$privnotify = "<br>Έχετε $new_message <a href=\"$privmsg_url\">".($new_message>1?"νέα προσωπικά μηνύματα":"νέο προσωπικό μ�?νυμα")."</a>.";';

// Page_tail
$l_adminpanel	= "Διαχείριση";
$l_poweredby	= "�?ποστηρίζεται από το";
$l_version	= "Έκδοση";

// Register
$l_notfilledin	= "Σφάλμα - δε συμπληρώσατε όλα τα απαιτούμενα πεδία";
$l_invalidname	= "�?ο όνομα χρ�?στη που επιλέξατε, χρησιμοποιείται �?δη.";
$l_disallowname	= "�?ο όνομα χρ�?στη δεν επιτρέπεται από τον διαχειριστ�?.";
$l_welcomesubj	= "Καλωσορίσατε στις περιοχές συζητ�?σεων";
$l_beenadded	= "Προστεθ�?κατε στη βάση δεδομένων.";
$l_thankregister= "Σας ευχαριστούμε για την εγγραφ�? σας!";
$l_useruniq	= "Πρέπει να είναι μοναδικό. Δε γίνεται δύο χρ�?στες να έχουν το ίδιο όνομα.";
$l_storecookie	= "Αποθ�?κευση του ονόματός σας σε ένα «cookie» για ένα χρόνο.";

// Prefs
$l_prefupdated	= "Οι προτιμ�?σεις ενημερώθηκαν. <a href=\"index.php\">Πιέστε εδώ για να επιστρέψετε</a> στην κεντρικ�? σελίδα";
$l_themecookie	= "ΣΗΜΕΙΩΣΗ: για να αλλάξετε την εμφάνιση των σελίδων πρέπει να έχετε τα cookies ενεργά.";
$l_alwayssig	= "Προσθ�?κη υπογραφ�?ς σε όλα τα μηνύματα";
$l_alwaysdisable= "Απενεργοποίηση παντού "; // Only used for next three strings
$l_alwayssmile = "Απενεργοποίηση των $l_smilies παντού";
$l_alwayshtml	= "Απενεργοποίηση της $l_html παντού";
$l_alwaysbbcode	= "Απενεργοποίηση του $l_bbcode παντού";
$l_boardtheme	= "Εμφάνιση περιοχ�?ς συζητ�?σεων";
$l_boardlang  = "Γλώσσα περιοχ�?ς συζητ�?σεων";
$l_nothemes	= "Δεν υπάρχουν ρυθμίσεις εμφάνισης στη βάση";
$l_saveprefs	= "Αποθ�?κευση προτιμ�?σεων";

// Search
$l_searchterms	= "Λέξεις κλειδιά";
$l_searchany	= "Αναζ�?τηση για ΟΠΟΙΟΝΔΗΠΟ�?Ε από τους όρους (Προκαθορισμένο)";
$l_searchall	= "Αναζ�?τηση για ΟΛΟ�?Σ τους όρους";
$l_searchallfrm	= "Αναζ�?τηση σε όλες τις περιοχές συζητ�?σεων";
$l_sortby	= "�?αξινόμηση κατα";
$l_searchin	= "Αναζ�?τηση σε";
$l_titletext	= "�?ίτλο και Κείμενο";
$l_nomatches	= "Δεν βρέθηκαν εγγραφές που να ταιριάζουν. Παρακαλώ διευρύνετε την αναζ�?τηση.";

// Whosonline
$l_whosonline	= "Ποιος είναι συνδεδεμένος;";
$l_nousers	= "Κανείς χρ�?στης δε διαβάζει αυτ�? τη στιγμ�? τις περιοχές συζητ�?σεων";

// Editpost
$l_notedit	= "Δεν μπορείτε να αλλάξετε μ�?νυμα που δεν είναι δικό σας.";
$l_permdeny	= "Δεν δώσατε το σωστό $l_password �? δεν έχετε το δικαίωμα να αλλάξετε αυτό το μ�?νυμα. $l_tryagain";
$l_editedby	= "�?ο $l_message διορθώθηκε από:";
$l_stored	= "�?ο $l_message αποθηκεύτηκε στη βάση.";
$l_viewmsg	= " για να εμφανίσετε το $l_message.";
$l_viewmsg1	= "εμφάνιση μηνύματος";
$l_deleted	= "�?ο μ�?νυμα διαγράφηκε.";
$l_nouser	= "�?ο $l_username δεν υπάρχει.";
$l_passwdlost	= "Ξέχασα το συνθηματικό μου!";
$l_delete	= "Διαγραφ�? του μηνύματος";

$l_disable	= "Απενεργοποίηση";
$l_onthispost	= "σε αυτό το μ�?νυμα";

$l_htmlis	= "$l_html ";
$l_bbcodeis	= "$l_bbcode ";

$l_notify	= "Ειδοποίηση μέσω email αν σταλούν απαντ�?σεις";

// Newtopic
$l_emptymsg	= "Για να στείλετε ένα μ�?νυμα πρέπει να γράψετε κάποιο κείμενο. Δεν μπορείτε να στείλετε κενό μ�?νυμα.";
$l_aboutpost	= "Σχετικά με την αποστολ�? μηνυμάτων";
$l_regusers	= "Όλοι οι <b>εγγεγραμμένοι</b> χρ�?στες";
$l_anonusers	= "Οι <b>ανώνυμοι</b> χρ�?στες";
$l_modusers	= "Μόνο οι <b>συντονιστές</b> και οι <b>διαχειριστές</b>";
$l_anonhint	= "<br>(για να στείλετε μ�?νυμα ανώνυμα απλώς μη δώσετε όνομα χρ�?στη και συνθηματικό)";
$l_inthisforum	= "μπορούν να στείλουν απαντ�?σεις και να ανοίξουν νέα θέματα εδώ";
$l_attachsig	= "Εμφάνιση υπογραφ�?ς <font size=-2>(Μπορεί να προστεθεί �? να αλλαχτεί στο προφίλ σας)</font>";
$l_cancelpost	= "Ακύρωση αποστολ�?ς";

// Reply
$l_nopostlock	= "Δεν μπορείτε να στείλετε απαντ�?σεις σε αυτό το θέμα, έχει κλειδωθεί.";
$l_topicreview  = "Ανασκόπηση θέματος";
$l_notifysubj	= "Στάλθηκε μια απάντηση στο θέμα σας.";
$l_quotemsg	= '[quote]\nΣτις $m[post_time], ο/η $m[username] έγραψε:\n$text\n[/quote]';

// Sendpmsg
$l_norecipient	= "Πρέπει να εισάγετε το όνομα χρ�?στη προς το οποίο θέλετε να στείλετε το μ�?νυμα.";
$l_sendothermsg	= "Αποστολ�? άλλου προσωπικού μηνύματος";
$l_cansend	= "μπορούν να στείλουν προσωπικά μηνύματα";  // All registered users can send PM's
$l_yourname	= "�?ο όνομα χρ�?στη σας";
$l_recptname	= "Όνομα χρ�?στη παραλ�?πτη";

// Replypmsg
$l_pmposted	= "Στάλθηκε απάντηση, πιέστε <a href=\"viewpmsg.php\">εδώ</a> για να δείτε τα προσωπικά σας μηνύματα";

// Viewpmsg
$l_nopmsgs	= "Δεν έχετε προσωπικά μηνύματα.";
$l_reply	= "Απάντηση";
$l_replyEdit	= "Αλλαγ�? απάντησης";

// Delpmsg
$l_deletesucces	= "Διαγραφ�? επιτυχ�?ς.";

// Smilies
$l_smilesym	= "�?ι να γράψετε";
$l_smileemotion	= "Συναίσθημα";
$l_smilepict	= "Εικόνα";

/*****************************************************************
* questionnaire.inc.php
******************************************************************/
$langCreateSurvey = 'Δημιουργία Έρευνας Μαθησιακού Προφίλ';
$langCreatePoll = 'Δημιουργία Ερωτηματολογίου';
$langEditPoll = '�?ροποποίηση Ερωτηματολογίου';
$langQuestionnaire = "Ερωτηματολόγια";
$langSurvey = "Ερωτηματολόγιο";
$langSurveys = "Ερωτηματολόγια";
$langParticipate = "Συμμετοχ�?";
$langSurveysActive = "Ενεργές Έρευνες Μαθησιακού Προφίλ";
$langSurveysInactive = "Ανενεργές Έρευνες Μαθησιακού Προφίλ";
$langSurveyNumAnswers = "Απαντ�?σεις";
$langSurveyDateCreated = "Δημιουργ�?θηκε την";
$langSurveyStart = "Ξεκίνησε την";
$langSurveyEnd = "και τελείωσε την";
$langSurveyOperations = "Λειτουργίες";
$langSurveyAddAnswer = "Προσθ�?κη Απαντ�?σεων";
$langSurveyType = "�?ύπος";
$langSurveyMC = "Πολλαπλ�?ς Επιλογ�?ς";
$langSurveyFillText = "Συμπληρώστε το κενό";
$langSurveyContinue = "Συνέχεια";
$langSurveyMoreAnswers ="+ απαντ�?σεις";
$langSurveyMoreQuestions = "+ ερωτ�?σεις";
$langSurveyCreated ="Η Έρευνα Μαθησιακού Προφίλ δημιουργ�?θηκε με επιτυχία.<br><br><a href=\"questionnaire.php\">Επιστροφ�?</a>";
$langSurveyCreator = "Δημιουργός";
$langSurveyCreationError = "Σφάλμα κατά την δημιουργία του Ερωτηματολογίου. Παρακαλώ προσπαθ�?στε ξανά.";
$langSurveyDeleted ="Η Έρευνα Μαθησιακού Προφίλ διαγράφηκε με επιτυχία.<br><br><a href=\"questionnaire.php\">Επιστροφ�?</a>.";
$langSurveyDeactivated ="Η Έρευνα Μαθησιακού Προφίλ απενεργοποι�?θηκε με επιτυχία.";
$langSurveyActivated ="Η Έρευνα Μαθησιακού Προφίλ ενεργοποι�?θηκε με επιτυχία.";
$langSurveySubmitted ="Ευχαριστούμε για την συμμετοχ�? σας!<br><br><a href=\"questionnaire.php\">Επιστροφ�?</a>.";
$langSurveyTotalAnswers = "Συνολικός αριθμός απαντ�?σεων";
$langSurveyNone = "Δεν έχουν δημιουργηθεί έρευνες μαθησιακού προφίλ για το μάθημα";
$langSurveyInactive = "Η Έρευνα Μαθησιακού Προφίλ έχει λ�?ξει �? δεν έχει ενεργοποιηθεί ακόμα.";
$langSurveyCharts = "Αποτελέσματα έρευνας";
$langQPref = "�?ι τύπο έρευνα μαθησιακού προφίλ επιθυμείτε;";
$langQPrefSurvey = "Έρευνα μαθησιακού προφίλ";
$langNamesSurvey = "Έρευνες Μαθησιακού Προφίλ";
$langHasParticipated = "Έχετε �?δη συμμετάσχει";
$langSurveyInfo ="Επιλέξτε ένα έτοιμο ερώτημα (σύμφωνα με το πρότυπο COLLES/ATTL)";
$langQQuestionNotGiven ="Δεν έχετε εισάγει την τελευταία ερώτηση.";
$langQFillInAllQs ="Παρακαλώ απαντ�?στε σε όλες τις ερωτ�?σεις.";

$langQuestion1= array('Σε αυτ�? την ενότητα, η προσπάθεια μου επικεντρώθηκε σε θέματα που με ενδιέφεραν.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);
$langQuestion2= array('Σε αυτ�? την ενότητα, αυτά που μαθαίνω έχουν να κάνουν με το επάγγελμά μου.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);
$langQuestion3= array('Σε αυτ�? την ενότητα, ασκώ κριτικ�? σκέψη.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);

$langQuestion4= array('Σε αυτ�? την ενότητα, συνεργάζομαι με τους συμφοιτητές μου.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);

$langQuestion5= array('Σε αυτ�? την ενότητα, η διδασκαλία κρίνεται ικανοποιητικ�?.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);

$langQuestion6= array('Σε αυτ�? την ενότητα, υπάρχει σωστ�? επικοινωνία με τον διδάσκοντα.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);
$langQuestion7= array('Προσπαθώ να βρίσκω λάθη στο σκεπτικό του συνομιλητ�? μου.'
					,'Σχεδόν ποτέ.'
					,'Σπάνια.'
					,'Μερικές φορές.'
					,'Συχνά.'
					,'Σχεδόν πάντα.'
					);
$langQuestion8= array('Όταν συζητώ μπαίνω στην θέση του συνομιλητ�? μου.'
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

$langQuestion10= array('Μου αρέσει να παίρνω τον ρόλο του συν�?γορου του διαβόλου.'
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
$langPollEnd = "Λ�?ξη";
$langPollEnded = "και τελείωσε την";
$langPollOperations = "Λειτουργίες";
$langPollNumAnswers = "Απαντ�?σεις";
$langPollAddAnswer = "Προσθ�?κη απαντ�?σεων";
$langPollType = "�?ύπος";
$langPollMC = "Πολλαπλ�?ς Επιλογ�?ς";
$langPollFillText = "Συμπληρώστε το κενό";
$langPollContinue = "Συνέχεια";
$langPollMoreAnswers ="+";
$langPollAddMultiple = "Νέα ερώτηση πολλαπλ�?ς επιλογ�?ς";
$langPollAddFill = "Νέα ερώτηση συμπλ�?ρωσης κενού";
$langPollCreated ="<p class='success_small'>�?ο Ερωτηματολόγιο δημιουργ�?θηκε με επιτυχία.<br /><a href=\"questionnaire.php\">Επιστροφ�?</a></p>";
$langPollEdited ="<p class='success_small'>�?ο Ερωτηματολόγιο τροποποι�?θηκε με επιτυχία.<br /><a href=\"questionnaire.php\">Επιστροφ�?</a></p>";
$langPollCreator = "Δημιουργός";
$langPollCreation = "Ημ/νία Δημιουργίας";
$langPollCreateDate = "�?ο Ερωτηματολόγιο δημιουργ�?θηκε την";
$langPollCreationError = "<p class='caution_small'>Σφάλμα κατά την δημιουργία του Ερωτηματολογίου. Παρακαλώ προσπαθ�?στε ξανά.</p>";
$langPollDeleted ="<p class='success_small'>�?ο Ερωτηματολόγιο διαγράφηκε με επιτυχία. <br><a href=\"questionnaire.php\">Επιστροφ�?</a></p>";
$langPollDeactivated ="<p class='success_small'>�?ο Ερωτηματολόγιο απενεργοποι�?θηκε με επιτυχία!</p>";
$langPollActivated ="<p class='success_small'>�?ο Ερωτηματολόγιο ενεργοποι�?θηκε με επιτυχία!</p>";
$langPollSubmitted ="<p class='success_small'>Ευχαριστούμε για την συμμετοχ�? σας!<br /><a href=\"questionnaire.php\">Επιστροφ�?</a></p>";
$langPollTotalAnswers = "Συνολικός αριθμός απαντ�?σεων";
$langPollNone = "Δεν υπάρχουν αυτ�? την στιγμ�? διαθέσιμα Ερωτηματολόγια.";
$langPollInactive = "<p class='caution_small'>�?ο Ερωτηματολόγιο έχει λ�?ξει �? δεν έχει ενεργοποιηθεί ακόμα.</p>";
$langPollHasEnded = "Έχει λ�?ξει";
$langPollCharts = "Αποτελέσματα Ερωτηματολογίου";
$langPollUnknown = "Δεν ξέρω / Δεν απαντώ";
$langIndividuals = "Αποτελέσματα ανά χρ�?στη";
$langCollectiveCharts = "Συγκεντρωτικά αποτελέσματα";
$langHasNotParticipated = "Δεν έχετε συμμετάσχει";
$langThereAreParticipants = "<p class='caution_small'>Στο Ερωτηματολόγιο έχουν �?δη συμμετάσχει χρ�?στες. <br />Η διόρθωση των στοιχείων δεν είναι δυνατ�?!</p>";
$langPollEmpty = "<p class='caution_small'>Παρακαλώ προσθέστε ερωτ�?σεις στο Ερωτηματολόγιο!</p>";
$langPollEmptyAnswers = "<p class='caution_small'>Σφάλμα! Δεν υπάρχουν απαντ�?σεις στην ερώτηση</p>";

/************************************************************
* registration.inc.php
*************************************************************/
$langSee = "Προεπισκόπηση";
$langNoSee = "Απαιτείται εγγραφ�?";
$langCourseName = "�?ίτλος Μαθ�?ματος";
$langCoursesLabel = '�?μ�?ματα';
$langNoCoursesAvailable = "Δεν υπάρχουν διαθέσιμα μαθ�?ματα για εγγραφ�?";
$langRegistration="Εγγραφ�?";
$langSurname="Επώνυμο";
$langUsername="Όνομα χρ�?στη (username)";
$langConfirmation="Επιβεβαίωση συνθηματικού";
$langUserNotice = "(μέχρι 20 χαρακτ�?ρες)";
$langEmailNotice = "�?ο e-mail δεν είναι απαραίτητο, αλλά χωρίς αυτό δε θα μπορείτε να λαμβάνετε
ανακοινώσεις, ούτε θα μπορείτε να χρησιμοποι�?σετε τη λειτουργία υπενθύμισης συνθηματικού.";
$langAm = "Αριθμός μητρώου";
$langDepartment="Σχολ�? / �?μ�?μα";
$langUserDetails = "Εγγραφ�? $langOfStudent";
$langSubmitNew = "�?ποβολ�? Αίτησης";

// newuser_second.php
$langPassTwice="Πληκτρολογ�?σατε δύο διαφορετικά συνθηματικά. Χρησιμοποι�?στε το πλ�?κτρο «επιστροφ�?» του browser σας και ξαναδοκιμάστε.";
$langUserFree="�?ο όνομα χρ�?στη που επιλέξατε χρησιμοποιείται!";
$langYourReg="Η εγγραφ�? σας στο";
$langDear="Αγαπητέ";
$langYouAreReg="\nΟ λογαριασμός σας στην πλατφόρμα";
$langSettings="δημιουργ�?θηκε με επιτυχία!\n�?α προσωπικά στοιχεία του λογαριασμού σας είναι τα εξ�?ς:\n\nΌνομα χρ�?στη:";
$langAddressOf="\n\nΗ διεύθυνση του";
$langProblem="\nΣτη περίπτωση που αντιμετωπίζετε προβλ�?ματα, επικοινων�?στε με την Ομάδα Ασύγχρονης �?ηλεκπαίδευσης";
$langFormula="\n\nΦιλικά,\n";
$langManager="\n�?πεύθυνος";
$langPersonalSettings="Οι προσωπικές σας ρυθμίσεις έχουν καταχωρηθεί και σας στάλθηκε ένα e-mail για να θυμάστε το όνομα χρ�?στη και το συνθηματικό σας.</p>";
$langPersonalSettingsMore= "Κάντε κλίκ <a href='../../index.php'>εδώ</a> για να εισέλθετε στο προσωπικό σας χαρτοφυλάκιο.<br>Εκεί μπορείτε:<ul><li>να περιηγηθείτε στο περιβάλλον της πλατφόρμας και τις προσωπικές σας επιλογές,</li><li>να επιλέξετε στον \"Κατάλογο Μαθημάτων\" τα μαθ�?ματα που επιθυμείτε να παρακολουθ�?σετε.</li><ul>";
$langYourRegTo="Ο κατάλογος μαθημάτων σας περιέχει";
$langIsReg="έχει ενημερωθεί";
$langCanEnter="Είσοδος στην ψηφιακ�? αίθουσα.";
$langChoice="Επιλογ�?";
$langLessonName="Όνομα μαθ�?ματος";

// profile.php
$langPassTwo="Έχετε πληκτρολογ�?σει δύο διαφορετικά νέα συνθηματικά";
$langAgain="Ξαναπροσπαθ�?στε!";
$langFields="Αφ�?σατε μερικά πεδία κενά";
$langEmailWrong="Η διεύθυνση ηλεκτρονικού ταχυδρομείου δεν είναι συμπληρωμένη �? περιέχει άκυρους χαρακτ�?ρες";
$langPassChanged="�?ο συνθηματικό πρόσβασης στην πλατφόρμα έχει αλλάξει";
$langPassOldWrong="�?ο παρόν συνθηματικό πρόσβασης που δώσατε είναι λάθος";
$langNewPass1="Νέο συνθηματικό";
$langNewPass2="Νέο συνθηματικό (ξανά)";
$langInvalidCharsPass="Έχετε χρησιμοποι�?σει μη επιτρεπτούς χαρακτ�?ρες στο συνθηματικό σας";
$langInvalidCharsUsername="Έχετε χρησιμοποι�?σει μη επιτρεπτούς χαρακτ�?ρες στο όνομα χρ�?στη σας";
$langProfileReg="Οι αλλαγές στο προφίλ σας αποθηκεύτηκαν";
$langOldPass="Παρόν συνθηματικό";
$langChangePass="Αλλαγ�? συνθηματικού πρόσβασης";

// user.php
$langNewUser = "Εγγραφ�? Χρ�?στη";
$langModRight="Αλλαγ�? των δικαιωμάτων διαχειριστ�? του";
$langNone="κανένας";
$langNoAdmin="δεν έχει<b>δικαιώματα διαχειριστ�? σε αυτό το site</b>";
$langAllAdmin="έχει τώρα<b>όλα τα δικαιώματα διαχειριστ�? σε αυτό το site</b>";
$langModRole="Αλλαγ�? του ρόλου του";
$langRole="Ρόλος";
$langIsNow="είναι τώρα";
$langInC="σε αυτό το μάθημα";
$langFilled="Εχετε αφ�?σει μερικά πεδία κενά.";
$langUserNo="�?ο όνομα χρ�?στη που διαλέξατε";
$langTaken="χρησιμοποιείται �?δη. Διαλέξτε άλλο.";
$langRegYou="σας έχει εγγράψει στο μάθημα";
$langTheU="Ο χρ�?στης";
$langAddedU="έχει προστεθεί. Στάλθηκε ένα email σε αυτόν";
$langAndP="και το συνθηματικό τους";
$langDereg="έχει διαγραφεί από αυτό το μάθημα";
$langAddAU="Προσθέστε ένα χρ�?στη";
$langAdmR="Δικαιώματα Διαχειριστ�?";
$langAddHereSomeCourses = "<p>Για να εγγραφείτε / απεγγραφείτε σε / από ένα μάθημα,
πρώτα επιλέξτε το τμ�?μα στο οποίο βρίσκεστε και στη συνέχεια επιλέξτε / αποεπιλέξτε το μάθημα.<br>
<p>Για να καταχωρηθούν οι προτιμ�?σεις σας πατ�?στε '�?ποβολ�? αλλαγών'</p><br>";
$langDeleteUser = "Είστε σίγουρος ότι θέλετε να διαγράψετε τον χρ�?στη";
$langDeleteUser2 = "από αυτό το μάθημα";

// adduser.php - added by adia 2003-02-21
$langAskUser = "Αναζητ�?στε τον χρ�?στη που θέλετε να προστεθεί. Ο χρ�?στης θα πρέπει να έχει �?δη λογαριασμό στην πλατφόρμα για να εγγραφεί στο μάθημά σας.";
$langAskManyUsers = "Πληκτρολογ�?στε το όνομα αρχείου χρηστών �? κάντε κλικ στο πλ�?κτρο \"Αναζ�?τηση\" για να το εντοπίσετε.";
$langAskManyUsers1 = "<strong>Σημείωση</strong>:<br />1) Οι χρ�?στες θα πρέπει να έχουν �?δη λογαριασμό στην πλατφόρμα για να γραφτούν στον μάθημά σας.";
$langAskManyUsers2 = "2) �?ο αρχείο χρηστών πρέπει να είναι απλό αρχείο κειμένου με τα ονόματα των χρηστών ένα ανά γραμμ�?. <br /><br />
<u>Παράδειγμα</u>:
    <br>
    eleni<br>
    nikos<br>
    spiros<br>
    ";
$langAddUser = "Προσθ�?κη ενός χρ�?στη";
$langAddManyUsers  = "Προσθ�?κη πολλών χρηστών";
$langOneUser = "ενός χρ�?στη";
$langManyUsers = "πολλών χρηστών";
$langGUser = "χρ�?στη επισκέπτη";
$langNoUsersFound = "Δε βρέθηκε κανένας χρ�?στης με τα στοιχεία που δώσατε �? ο χρ�?στης υπάρχει �?δη στο μάθημά σας.";
$langRegister = "Εγγραφ�? χρ�?στη στο μάθημα";
$langAdded = " προστέθηκε στο μάθημά σας.";
$langAddError = "Σφάλμα! Ο χρ�?στης δεν προστέθηκε στο μάθημα. Παρακαλούμε προσπαθ�?στε ξανά �? επικοινων�?στε με το διαχειριστ�? του συστ�?ματος.";
$langAddBack = "Επιστροφ�? στη σελίδα εγγραφ�?ς χρηστών";
$langAskUserFile = "Όνομα αρχείου";
$langFileNotAllowed = "Λάθος τύπος αρχείου! �?ο αρχείο χρηστών πρέπει να είναι απλό αρχείο κειμένου με τα ονόματα
των χρηστών ανά γραμμ�?";
$langUserNoExist = "Ο χρ�?στης δεν είναι γραμμένος στην πλατφόρμα";
$langUserAlready = "Ο χρ�?στης είναι �?δη γραμμένος στο μάθημά σας";
$langUserFile = "Όνομα αρχείου χρηστών";

// search_user.php
$langphone= "�?ηλέφωνο";
$langUserNoneMasc="-";
$langTutor="Εκπαιδευτ�?ς";
$langTutorDefinition="Διδάσκων (δικαίωμα να επιβλέπει τις ομάδες χρηστών)";
$langAdminDefinition="Διαχειριστ�?ς (δικαίωμα να αλλάζει το περιεχόμενο των μαθημάτων)";
$langDeleteUserDefinition="Διαγραφ�? (διαγραφ�? από τον κατάλογο χρηστών του <b>παρόντος</b> μαθ�?ματος)";
$langNoTutor = "δεν είναι διδάσκων σε αυτό το μάθημα";
$langYesTutor = "είναι διδάσκων σε αυτό το μάθημα";
$langUserRights="Δικαιώματα χρηστών";
$langNow="τώρα";
$langOneByOne="Προσθ�?κη χρ�?στη";
$langUserMany="Εισαγωγ�? καταλόγου χρηστών μέσω αρχείων κειμένου";
$langUserAddExplanation="κάθε γραμμ�? του αρχείου που θα στείλετε θα περιέχει 5 πεδία:
         <b>Όνομα&nbsp;&nbsp;&nbsp;Επίθετο&nbsp;&nbsp;&nbsp;
        Όνομα Χρ�?στη&nbsp;&nbsp;&nbsp;Συνθηματικό&nbsp;
        &nbsp;&nbsp;email</b> και θα ειναι χωρισμένο με tab.
        Οι χρ�?στες θα λάβουν ειδοποίηση μέσω email με το όνομα χρ�?στη / συνθηματικό.";
$langDownloadUserList="Ανέβασμα καταλόγου";
$langUserNumber="αριθμός";
$langGiveAdmin="Προσθ�?κη δικαίωματος";
$langRemoveRight="Αφαίρεση δικαίωματος";
$langGiveTutor="Προσθ�?κη δικαίωματος";
$langUserOneByOneExplanation="Αυτός (αυτ�?) θα λάβει ειδοποίηση μέσω email με όνομα χρ�?στη και συνθηματικό";
$langBackUser="Επιστροφ�? στη λίστα χρηστών";
$langUserAlreadyRegistered="Ενας χρ�?στης με ίδιο όνομα / επίθετο είναι �?δη γραμμένος σε αυτό το μάθημα.
                Δεν μπορείτε να τον (την) ξαναγράψετε.";
$langAddedToCourse="είναι �?δη γραμμένος στην πλατφόρμα αλλά όχι σε αυτό το μάθημα. �?ώρα έγινε.";
$langGroupUserManagement="Διαχείριση ομάδας χρηστών";
$langRegDone="Οι αλλαγές σας κατοχυρώθηκαν.";
$langPassTooEasy ="�?ο συνθηματικό σας είναι πολύ απλό. Χρησιμοποι�?στε ένα συνθηματικό σαν και αυτό";
$langChoiceLesson ="Επιλογ�? Μαθημάτων";
$langRegCourses = "Εγγραφ�? σε μάθημα";
$langChoiceDepartment ="Επιλογ�? �?μ�?ματος";
$langCoursesRegistered="Η εγγραφ�? σας στα μαθ�?ματα που επιλέξατε έγινε με επιτυχία!";
$langNoCoursesRegistered="<p>Δεν επιλέξατε μάθημα για εγγραφ�?.</p><p> Μπορείτε να εγγραφείτε σε μάθημα, την
επόμενη φορά που θα μπείτε στην πλατφόρμα.</p>";
$langIfYouWantToAddManyUsers="Αν θέλετε να προσθέσετε ένα κατάλογο με χρ�?στες στο μάθημά σας, παρακαλώ συμβουλευτείτε τον διαχειριστ�? συστ�?ματος.";
$langCourse="Μάθημα";
$langLastVisits="Οι τελευταίες μου επισκέψεις";
$langLastUserVisits= "Οι τελευταίες επισκέψεις του χρ�?στη ";
$langDumpUser="Κατάλογος χρηστών:";
$langCsv=" σε αρχείο csv";
$langFieldsMissing="Αφ�?σατε κάποιο(α) από τα υποχρεωτικά πεδία κενό(ά) !";
$langFillAgain="Παρακαλούμε ξανασυμπληρώστε την";
$langFillAgainLink="αίτηση";
$langReqRegProf="Αίτηση Εγγραφ�?ς $langOfTeacher";
$langProfUname="Επιθυμητό Όνομα Χρ�?στη (Username)";
$profreason="Αναφέρατε τους λόγους χρ�?σης της πλατφόρμας";
$langProfEmail="e-mail Χρ�?στη";
$reguserldap="Εγγραφ�? Χρ�?στη μέσω LDAP";
$langByLdap="Μέσω LDAP";
$langNewProf="Εισαγωγ�? στοιχείων νέου λογαριασμού $langsOfTeacher";
$profsuccess="Η δημιουργία νέου λογαριασμού $langsOfTeacher πραγματοποι�?θηκε με επιτυχία!";
$langDearProf="Αγαπητέ διδάσκοντα!";
$success="Η αποστολ�? των στοιχείων σας έγινε με επιτυχία!";
$click="Κάντε κλίκ";
$langBackPage="για να επιστρέψετε στην αρχικ�? σελίδα.";
$emailprompt="Δώστε την διεύθυνση e-mail σας:";
$ldapprompt="Δώστε το συνθηματικό LDAP σας:";
$univprompt="Επιλέξτε Πανεπιστημιακό Ίδρυμα";
$ldapnamesur="Ονοματεπώνυμο:";
$langInstitution='Ίδρυμα:';
$ldapuserexists="Στο σύστημα υπάρχει �?δη κάποιος χρ�?στης με τα στοιχεία που δώσατε.";
$ldapempty="Αφ�?σατε κάποιο από τα πεδία κενό!";
$ldapfound="πιστοποι�?θηκε και τα στοιχεία που έδωσε είναι σωστά";
$ldapchoice="Παρακαλούμε επιλέξτε το ίδρυμα στο οποίο αν�?κετε!";
$ldapnorecords="Δεν βρέθηκαν εγγραφές. Πιθανόν να δώσατε λάθος στοιχεία.";
$ldapwrongpasswd="�?ο συνθηματικό που δώσατε είναι λανθασμένο. Παρακαλούμε δοκιμάστε ξανά";
$ldapproblem="�?πάρχει πρόβλημα με τα στοιχεία του";
$ldapcontact="Παρακαλούμε επικοινων�?στε με τον διαχειριστ�? του εξυπηρέτη LDAP.";
$ldaperror="Δεν είναι δυνατ�? η σύνδεση στον εξυπηρέτη του LDAP.";
$ldapmailpass="�?ο συνθηματικό σας είναι το ίδιο με αυτό της υπηρεσίας e-mail.";
$ldapback="Επιστροφ�? στην";
$ldaplastpage="προηγούμενη σελίδα";
$mailsubject="Αίτηση ".$langOfTeacher." - �?πηρεσία Ασύγχρονης �?ηλεκπαίδευσης";
$mailsubject2="Αίτηση ".$langOfStudent." - �?πηρεσία Ασύγχρονης �?ηλεκπαίδευσης";
$contactphone="�?ηλέφωνο επικοινωνίας";
$contactpoint="Επικοινωνία";
$searchuser="Αναζ�?τηση Καθηγητών / Χρηστών";
$typeyourmessage="Πληκτρολογ�?στε το μ�?νυμά σας παρακάτω";
$emailsuccess="�?ο e-mail στάλθηκε!";
$langBackReq = "Επιστροφ�? στις Ανοικτές Αιτ�?σεις Καθηγητών";
$langTheTeacher = 'Ο διδάσκων';
$langTheUser = 'Ο χρ�?στης';
$langDestination = 'Παραλ�?πτης:';
$langAsProf = 'ως καθηγητ�?ς';
$langTel = '�?ηλ.';
$langPassSameAuth = '�?ο συνθηματικό σας είναι αυτό της υπηρεσίας πιστοποίησης του λογαριασμού σας.';
$langLdapRequest = '�?πάρχει �?δη μια αίτηση για τον χρ�?στη';
$langLDAPUser = 'Χρ�?στης LDAP';
$langLogIn = 'Σύνδεση';
$langLogOut = 'Αποσύνδεση';
$langAction = 'Ενέργεια';
$langRequiredFields = '�?α πεδία με (*) είναι υποχρεωτικά';
$langCourseVisits = "Επισκέψεις ανά μάθημα";

// user registration
$langAuthUserName = "Δώστε το όνομα χρ�?στη:";
$langAuthPassword = "Δώστε το συνθηματικό σας:";
$langAuthenticateVia = "πιστοποίηση μέσω";
$langAuthenticateVia2 = "Διαθέσιμοι τρόποι πιστοποίησης στο ίδρυμα";
$langCannotUseAuthMethods = "Η εγγραφ�? στην πλατφόρμα, προς το παρόν δεν επιτρέπεται. Παρακαλούμε, ενημερώστε το διαχειριστ�? του συστ�?ματος";
$langConfirmUser = "Έλεγχος Στοιχείων Χρ�?στη";
$langUserData = "Στοιχεία χρ�?στη";
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
$langDateCompleted = "Ημ/νία ολοκλ�?ρωσης";

$langDateRequest_small = "Aίτησης";
$langDateReject_small = "Aπόρριψης";
$langDateClosed_small = "Kλεισίματος";
$langDateCompleted_small = "Oλοκλ�?ρωσης";

$langRejectRequest = "Απόρριψη";
$langListRequest = "Λίστα Αιτ�?σεων";
$langTeacherRequestHasDeleted = "Η αίτηση του $langsOfTeacher διαγράφηκε!";
$langRejectRequestSubject = "Απόρριψη αίτησης εγγραφ�?ς στην Πλατφόρμα Ασύγχρονης �?ηλεκπαίδευσης";
$langGoingRejectRequest = "Πρόκειται να απορρίψετε την αίτηση $langsOfTeacher με στοιχεία:";
$langRequestSendMessage = "Αποστολ�? μηνύματος στο χρ�?στη στην διεύθυνση:";
$langRequestDisplayMessage = "στο μ�?νυμα θα αναφέρεται και το παραπάνω σχόλιο";
$langNoSuchRequest = "Δεν υπάρχει κάποια σχετικ�? αίτηση με αυτό το ID. Δεν είναι δυνατ�? η επεξεργασία της αίτησης.";
$langTeacherRequestHasRejected = "Η αίτηση του $langsOfTeacher απορρίφθηκε";
$langRequestMessageHasSent = " και στάλθηκε ενημερωτικό μ�?νυμα στη διεύθυνση ";
$langRequestHasRejected = "Η αίτησ�? σας για εγγραφ�? στην πλατφόρμα $siteName απορρίφθηκε.";
$langRegistrationDate = "Ημερομηνία εγγραφ�?ς";
$langExpirationDate = "Ημερομηνία λ�?ξης";
$langCourseRegistrationDate = "Ημ/νία εγγραφ�?ς στο μάθημα";
$langUnknownDate = "(άγνωστη)";
$langUserID = "Αριθμός χρ�?στη";
$langStudentParticipation = "Μαθ�?ματα στα οποία συμμετέχει ο χρ�?στης";
$langNoStudentParticipation = "Ο χρ�?στης δεν συμμετέχει σε κανένα μάθημα";
$langCannotDeleteAdmin = "Ο χρ�?στης αυτός (με user id = 1) είναι ο βασικός διαχειριστ�?ς της πλατφόρμας και δε διαγράφεται.";
$langExpireBeforeRegister = "Σφάλμα: H ημ/νια λ�?ξης είναι πρίν την ημ/νια εγγραφ�?ς";
$langSuccessfulUpdate = "�?α στοιχεία του χρ�?στη ενημερώθηκαν";
$langNoUpdate = "Δεν είναι εφικτ�? η ενημέρωση των στοιχείων για το χρ�?στη με id";
$langUpdateNoChange = "Δεν αλλάξατε κάποιο/κάποια από τα στοιχεία του χρ�?στη.";
$langError = "Σφάλμα";
$langRegistrationError = "Λάθος Ενέργεια. Επιστρέψτε στην αρχικ�? σελίδα της πλατφόρμας.";
$langUserNoRequests = "Δεν �?πάρχουν Ανοικτές Αιτ�?σεις Φοιτητών !";
$langCharactersNotAllowed = "Δεν επιτρέπονται στο password και στο username, οι χαρακτ�?ρες: ',\" �? \\";
$langStar2 = "Στα πεδία με (**) ";
$langEditUser = "Επεξεργασία στοιχείων χρ�?στη";
$langUnregForbidden = "Δεν επιτρέπεται να διαγράψετε τον χρ�?στη:";
$langUnregFirst = "Θα πρέπει να διαγράψετε πρώτα τον χρ�?στη από τα παρακάτω μαθ�?ματα:";
$langUnregTeacher = "Είναι ".$langsTeacher." στα παρακάτω μαθ�?ματα:";
$langPlease = "Παρακαλούμε";
$langOtherDepartments = "Εγγραφ�? σε μαθ�?ματα άλλων τμημάτων/σχολών";
$langNoLessonsAvailable = "Δεν υπάρχουν Διαθέσιμα Μαθ�?ματα.";
$langUserPermitions = "Δικαιώματα";

// formuser.php
$langUserRequest = "Αίτηση Δημιουργίας Λογαριασμού $langOfStudent";
$langUserFillData = "Συμπλ�?ρωση στοιχείων";
$langUserOpenRequests = "Ανοικτές αιτ�?σεις $langOfStudents";
$langWarnReject = "Πρόκειται να απορρίψετε την αίτηση $langsOfStudent";
$langWithDetails = "με στοιχεία";
$langNewUserDetails = "Στοιχεία Λογαριασμού Χρ�?στη-$langOfStudent";
$langInfoProfReq = "Αν επιθυμείτε να έχετε πρόβαση στην πλατφόρμα με δικαιώματα χρ�?στη - $langsOfTeacher, παρακαλώ συμπληρώστε την παρακάτω αίτηση. Η αίτηση θα σταλεί στον υπεύθυνο διαχειριστ�? ο οποίος θα δημιουργ�?σει το λογαριασμό και θα σας στείλει τα στοιχεία μέσω ηλεκτρονικού ταχυδρομείου.";
$langInfoStudReg = "Αν επιθυμείτε να έχετε πρόσβαση στην πλατφόρμα με δικαιώματα χρ�?στη - $langsOfStudent, παρακαλώ συμπληρώστε τα στοιχεία σας στην παρακάτω φόρμα. Ο λογαριασμός σας θα δημιουργηθεί αυτόματα.";
$langReason = "Αναφέρατε τους λόγους χρ�?σης της πλατφόρμας";
$langInfoStudReq = "Αν επιθυμείτε να έχετε πρόβαση στην πλατφόρμα με δικαιώματα χρ�?στη - ".$langsOfStudent .", παρακαλώ συμπληρώστε την παρακάτω αίτηση. Η αίτηση θα σταλεί στον υπεύθυνο διαχειριστ�? ο οποίος θα δημιουργ�?σει το λογαριασμό και θα σας στείλει τα στοιχεία μέσω ηλεκτρονικού ταχυδρομείου.";
$langInfoProf = "Σύντομα θα σας σταλεί mail από την Ομάδα Διαχείρισης της Πλατφόρμας Ασύγχρονης �?ηλεκπαίδευσης, με τα στοιχεία του λογαριασμού σας.";
$langDearUser = "Αγαπητέ χρ�?στη";
$langMailErrorMessage = "Παρουσιάστηκε σφάλμα κατά την αποστολ�? του μηνύματος.<br/>Η αίτησ�? σας καταχωρ�?θηκε στην πλατφόρμα, αλλά δεν στάλθηκε ενημερωτικό email στο διαχειριστ�? του συστ�?ματος. <br/>Παρακαλούμε επικοινων�?στε με το διαχειριστ�? στη διεύθυνση:";
$langUserSuccess = "Νέος λογαριασμός $langOfStudent";
$usersuccess="Η δημιουργία νέου λογαριασμού ".$langsOfStudent." πραγματοποι�?θηκε με επιτυχία!";
$langAsUser = "(Λογαριασμός $langOfStudent)";
$langChooseReg = "Επιλογ�? τρόπου εγγραφ�?ς";
$langTryAgain = "Δοκιμάστε ξανά!";
$langViaReq = "Εγγραφ�? χρηστών μέσω αίτησης";

/************************************************************
* restore_course.inc.php
*************************************************************/
$langFirstMethod = "1ος τρόπος";
$langSecondMethod = "2ος τρόπος";
$langRequest1 = "Κάντε κλικ στο Browse για να αναζητ�?σετε το αντίγραφο ασφαλείας του μαθ�?ματος που θέλετε να επαναφέρετε. Μετά κάντε κλίκ στο 'Αποστολ�?'. ";
$langRestore = "Επαναφορά";
$langRequest2 = "Αν το αντίγραφο ασφαλείας, από το οποίο θα ανακτ�?σετε το μάθημα, είναι μεγάλο σε μέγεθος και δεν μπορείτε να το ανεβάσετε, τότε μπορείτε να πληκτρολογ�?σετε τη ακριβ�? διαδρομ�? (path) που βρίσκεται το αρχείο στον server.";
$langRestoreStep1 = "1° Ανάκτηση μαθ�?ματος από αρχείο �? υποκατάλογο.";
$langFileNotFound = "�?ο αρχείο δεν βρέθηκε.";
$langFileSent = "Στάλθηκε ένα αρχείο";
$langFileSentName = "Όνομα:";
$langFileSentSize = "Μέγεθος:";
$langFileSentType = "�?ύπος:";
$langFileSentTName = "Προσωρινό όνομα:";
$langFileUnzipping = "Αποσυμπίεση του αρχείου";
$langEndFileUnzip = "�?έλος αποσυμπίεσης";
$langLesFound = "Μαθ�?ματα που βρέθηκαν μέσα στο αρχείο:";
$langLesFiles = "Αρχεία του μαθ�?ματος:";
$langInvalidCode = "Μη αποδεκτός κωδικός μαθ�?ματος";
$langCopyFiles = "�?α αρχεία του μαθ�?ματος αντιγράφτηκαν στο";
$langCourseExists = "�?πάρχει �?δη ένα μάθημα με αυτόν τον κωδικό !";
$langUserExists = "Στη πλατφόρμα υπάρχει �?δη ένας χρ�?στης με username";
$langUserExists2 = "Ονομάζεται";
$langWarning = "<em><font color='red'>ΠΡΟΣΟΧΗ!</font></em> Αν επιλέξετε να μην προστεθούν οι χρ�?στες του μαθ�?ματος και το αντίγραφο ασφαλείας του μαθ�?ματος, περιέχει υποσυστ�?ματα με πληροφορίες που σχετίζονται με τους χρ�?στες (π.χ. 'Εργασίες Φοιτητών', 'Χώρος Ανταλλαγ�?ς Αρχείων' �? 'Ομάδες Χρηστών') τότε οι πληροφορίες αυτές <b>ΔΕΝ</b> θα ανακτηθούν.";
$langUserWith = "Σφάλμα! Ο χρ�?στης με userid";
$langAlready = "�?δη προστέθηκε";
$langWithUsername = "Ο χρ�?στης με username";
$langUserisAdmin = "είναι διαχειριστ�?ς";
$langUsernameSame = "το username του παραμένει ίδιο.";
$langUName = "Ονομάζεται";
$langInfo1 = "�?ο αντίγραφο ασφαλείας που στείλατε, περιείχε τις παρακάτω πληροφορίες για το μάθημα.";
$langInfo2 = "Μπορείτε να αλλάξετε τον κωδικό του μαθ�?ματος και ότι άλλο θέλετε (π.χ. περιγραφ�?, καθηγητ�?ς κ.λπ.)";
$langCourseFac = "Σχολ�? / τμ�?μα ";
$langCourseOldFac = "Παλιά σχολ�? / τμ�?μα";
$langCourseVis = "�?ύπος πρόσβασης";
$langCourseType = "Προπτυχιακό / μεταπτυχιακό";
$langPrevId = "Προηγούμενο user_id";
$langNewId = "Καινούριο user_id";
$langUsersWillAdd = "Οι χρ�?στες του μαθ�?ματος θα προστεθούν";
$langUserPrefix = "Στα ονόματα χρηστών του μαθ�?ματος θα προστεθεί ένα πρόθεμα";
$langErrorLang = "Πρόβλημα! Δεν υπάρχουν γλώσσες για το μάθημα!";

/*****************************************************************
* search.inc.php
*****************************************************************/
$langDoSearch = "Εκτέλεση Αναζ�?τησης";
$langSearch_terms = "Όροι Αναζ�?τησης: ";
$langSearchIn = "Αναζ�?τηση σε: ";
$langSearchWith = "Αναζ�?τηση με κριτ�?ρια:";
$langNoResult = "Δεν βρέθηκαν αποτελέσματα";
$langIntroductionNote = "Εισαγωγικό Σημείωμα";
$langForum = "Περιοχ�? συζητ�?σεων";
$langOR = "�?ουλάχιστον έναν από τους όρους";
$langNOT = "Κανέναν από τους ακόλουθους όρους";
$langKeywords = "Λέξεις κλειδιά";
$langTitle_Descr = "αφορά τον τίτλο �? τμ�?μα από τον τίτλο του μαθ�?ματος";
$langKeywords_Descr = "κάποια λέξη �? οι λέξεις κλειδιά που προσδιορίζουν τη θεματικ�? περιοχ�? του μαθ�?ματος";
$langInstructor_Descr = "το όνομα �? τα ονόματα των καθηγητών του μαθ�?ματος";
$langCourseCode_Descr = "ο κωδικός του μαθ�?ματος";
$langAccessType = "�?ύπος Πρόσβασης";
$langTypeClosed = "Κλειστό";
$langTypeOpen = "Ανοικτό";
$langTypeRegistration = "Ανοικτό με εγγραφ�?";
$langTypesRegistration = "Ανοικτά με εγγραφ�?";
$langAllTypes = "(όλοι οι τύποι πρόσβασης)";
$langAllFacultes = "Σε όλες τις σχολές/τμ�?ματα";

/*****************************************************
* speedsubsribe.inc.php
******************************************************/
$langSpeedSubscribe = "Εγγραφ�? σαν διαχειριστ�?ς μαθ�?ματος";
$langPropositions="Κατάλογος με μελλοντικές προτάσεις ";
$langSuccess = "Η εγγραφ�? σας σαν διαχειριστ�?ς έγινε με επιτυχία";
$lang_subscribe_processing ="Διαδικασία Εγγραφ�?ς";
$langAuthRequest = "Απαιτείται εξακρίβωση στοιχείων";
$langAlreadySubscribe ="Είστε �?δη εγγεγραμμένος";
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
 $msgMonth = "μ�?να";
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
 $msgDaySort = "�?αξινόμηση σύμφωνα με την ημέρα";
 $msgMonthSort = "�?αξινόμηση σύμφωνα με το μ�?να";
 $msgCountrySort = "�?αξινόμηση σύμφωνα με τη χώρα";
 $msgOsSort = "�?αξινόμηση σύμφωνα με το λειτουργικό σύστημα";
 $msgBrowserSort = "�?αξινόμηση σύμφωνα με το Browser";
 $msgProviderSort = "�?αξινόμηση σύμφωνα με το παροχέα υπηρεσιών";
 $msgTotal = "Συνολικά";
 $msgBaseConnectImpossible = "Δεν είναι δυνατ�? η επιλογ�? βάσης δεδομένων";
 $msgSqlConnectImpossible = "Δεν είναι δυνατ�? η σύνδεση με τον εξυπηρέτη SQL";
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
 $msgDaysArray = array("Κυριακ�?","Δευτέρα","�?ρίτη","�?ετάρτη","Πέμπτη","Παρασκευ�?","Σάββατο");
 $msgDaysShortArray=array("Κ","Δ","�?","�?","Π","Π","Σ");
 $msgToday = "Σ�?μερα";
 $msgOther = "Αλλο";
 $msgUnknown = "Αγνωστο";
 $msgServerInfo = "Πληροφορίες για τον εξυπηρέτη της php";
 $msgStatBy = "Στατιστικά με";
 $msgVersion = "Webstats 1.30";
 $msgCreateCook = "<b>Διαχειριστ�?ς:</b> Ένα cookie έχει δημιουργηθεί στον υπολογιστ�? σας,<BR>
     Δεν θα εμφανίζεστε πλέον στα logs σας.<br><br><br><br>";
 $msgCreateCookError = "<b>Διαχειριστ�?ς:</b>το cookie δεν �?ταν δυνατόν να αποθηκευθεί στον υπολογιστ�? σας<br>
     Ελέγξτε τις ρυθμίσεις του browser και ανανεώστε ξανά τη σελίδα.<br><br><br><br>";

/************************************************************
* upgrade.inc.php
************************************************************/
$langUpgrade = "Αναβάθμιση των βάσεων δεδομένων";
$langExplUpgrade = "�?ο πρόγραμμα αναβάθμισης θα τροποποι�?σει το αρχείο ρυθμίσεων <em>config.php</em>.
   Επομένως πριν προχωρ�?σετε στην αναβάθμιση βεβαιωθείτε ότι ο web server
   μπορεί να έχει πρόσβαση στο <em>config.php</em>. Για λόγους ασφαλείας, οι
   τωρινές ρυθμίσεις του <em>config.php</em> θα κρατηθούν στο αρχείο
   <em>config_backup.php</em>.";
$langExpl2Upgrade = "Επίσης για λόγους ασφαλείας βεβαιωθείτε ότι έχετε κρατ�?σει αντίγραφα ασφαλείας των βάσεων δεδομένων.";
$langWarnUpgrade = "ΠΡΟΣΟΧΗ!";
$langExecTimeUpgrade = "ΠΡΟΣΟΧΗ! Για να ολοκληρωθεί η διαδικασία της αναβάθμισης βεβαιωθείτε ότι η μεταβλητ�? <em>max_execution_time</em> που ορίζεται στο <em>php.ini</em> είναι μεγαλύτερη από 300 (= 5 λεπτά). Αλλάξτε την τιμ�? της και ξαναξεκιν�?στε την διαδικασία αναβάθμισης";
$langUpgradeCont = "Για να προχωρ�?σετε στην αναβάθμιση της βάσης δεδομένων, δώστε το όνομα
   χρ�?στη και το συνθηματικό του διαχειριστ�? της πλατφόρμας:";
$langUpgDetails = "Στοιχεία Εισόδου";
$langUpgMan = "οδηγίες αναβάθμισης";
$langUpgLastStep = "πριν προχωρ�?σετε στο παρακάτω β�?μα.";
$langUpgToSee = "Για να δείτε τις αλλαγές-βελτιώσεις της καινούριας έκδοσης του eClass κάντε κλικ";
$langUpgRead = "Αν δεν το έχετε κάνει �?δη, παρακαλούμε διαβάσετε και ακολουθ�?στε τις";
$langSuccessOk = "Επιτυχία";
$langSuccessBad = "Σφάλμα �? δεν χρειάζεται τροποποίηση";
$langUpgAdminError = "�?α στοιχεία που δώσατε δεν αντιστοιχούν στο διαχειριστ�? του συστ�?ματος! Παρακαλούμε επιστρέψτε στην προηγούμενη σελίδα και ξαναδοκιμάστε.";
$langUpgNoVideoDir = "Ο υποκατάλογος 'video' δεν υπάρχει και δεν μπόρεσε να δημιουργηθεί. Ελέγξτε τα δικαιώματα πρόσβασης.";
$langUpgNoVideoDir2 = "�?πάρχει ένα αρχείο με όνομα 'video' που εμποδίζει! Θα πρέπει να το διαγράψετε.";
$langUpgNoVideoDir3 = "Δεν υπάρχει δικαίωμα εγγραφ�?ς στον υποκατάλογο 'video'!";
$langConfigError4 = "Δεν �?ταν δυνατ�? η πρόσβαση στον κατάλογο του αρχείο ρυθμίσεων config.php! Ελέγξτε τα δικαιώματα πρόσβασης.";
$langConfigError1 = "Δεν �?ταν δυνατ�? η λειτουργία αντιγράφου ασφαλείας του config.php! Ελέγξτε τα δικαιώματα πρόσβασης.";
$langConfigError2 = "�?ο αρχείο ρυθμίσεων config.php δεν μπόρεσε να διαβαστεί! Ελέγξτε τα δικαιώματα πρόσβασης.";
$langConfigError3 = "Δεν πραγματοποι�?θηκε η εγγραφ�? των αλλαγών στο αρχείο ρυθμίσεων config.php! Ελέγξτε τα δικαιώματα πρόσβασης.";
$langUpgradeSuccess = "Η αναβάθμιση των βάσεων δεδομένων του eClass πραγματοποι�?θηκε!";
$langUpgReady = "Είστε πλέον έτοιμοι να χρησιμοποι�?σετε την καινούρια έκδοση του Open eClass!";
$langUpgSucNotice = "Αν παρουσιάστηκε κάποιο σφάλμα, πιθανόν κάποιο μάθημα να μην δουλεύει εντελώς σωστά.<br>
                Σε αυτ�? την περίπτωση επικοινων�?στε μαζί μας στο <a href='mailto:eclass@gunet.gr'>eclass@gunet.gr</a><br>
                περιγράφοντας το πρόβλημα που παρουσιάστηκε<br> και στέλνοντας (αν είναι δυνατόν) όλα τα μηνύματα που εμφανίστηκαν στην οθόνη σας";
$langUpgCourse = "Αναβάθμιση μαθ�?ματος";
$langUpgFileNotRead = "To αρχείο δεν μπόρεσε να διαβαστεί. Ελέγξτε τα δικαιώματα πρόσβασης.";
$langUpgFileNotModify = "�?ο αρχείο δεν μπόρεσε να τροποποιηθεί. Ελέγξτε τα δικαιώματα πρόσβασης.";
$langUpgNotChDir = "Δεν πραγματοποι�?θηκε η αλλαγ�? στον κατάλογο αναβάθμισης! Ελέγξτε τα δικαιώματα πρόσβασης.";
$langUpgIndex = "�?ροποποίηση αρχείου index.php του μαθ�?ματος";
$langCheckPerm = "Ελέγξτε τα δικαιώματα πρόσβασης";
$langUpgNotIndex = "Δεν πραγματοποι�?θηκε η αλλαγ�? στον κατάλογο του μαθ�?ματος";
$langConfigFound = "Στο αρχείο ρυθμίσεων <tt>config.php</tt> βρέθηκαν τα παρακάτω στοιχεία επικοινωνίας.";
$langConfigMod = "Μπορείτε να τα αλλάξετε / συμπληρώσετε.";
$langUpgContact = "Στοιχεία Επικοινωνίας";
$langUpgAddress = "Διεύθυνση Ιδρύματος:";
$langUpgTel = "�?ηλ. Επικοινωνίας:";
$langUpgReg = "Εγγραφ�? Χρηστών";
$langTable = "Πίνακας";
$langToTable = "στον πίνακα";
$langAddField = "Προσθ�?κη πεδίου";
$langAfterField = "μετά το πεδίο";
$langToA = "σε";
$langRenameField = "Μετονομασία πεδίου";
$langOfTable = "του πίνακα";
$langDeleteField = "Διαγραφ�? πεδίου";
$langDeleteTable = "Διαγραφ�? πίνακα";
$langMergeTables = "Ενοποίηση των πινάκων";
$langIndexExists = "�?πάρχει �?δη κάποιο index στον πίνακα";
$langIndexAdded = "Προστέθηκε index στο πεδίο";
$langNotTablesList = "Πρόβλημα με την Β.Δ. Δεν �?ταν δυνατ�? η εύρεση των πινάκων";
$langNotMovedDir = "Προσοχ�?: Δεν �?ταν δυνατ�? η μεταφορά του υποκαταλόγου";
$langToDir = "στο φάκελο";
$langCorrectTableEntries = "Διόρθωση εγγραφών του πίνακα";
$langMoveIntroText = "Μεταφορά του εισαγωγικού κειμένου στον πίνακα";
$langEncodeDocuments = "Κωδικοποίηση των περιεχομένων του υποσυστ�?ματος 'Έγγραφα'";
$langEncodeGroupDocuments = "Κωδικοποίηση των περιεχομένων του υποσυστ�?ματος Ομάδες Χρηστών - 'Έγγραφα'";
$langWarnVideoFile = "Προσοχ�?: το αρχείο video";

/*******************************************************************
* toolmanagement.inc.php
********************************************************************/
$langTool = "Εργαλείο";
$langUploadPage = "Ανέβασμα ιστοσελίδας";
$langAddExtLink = "Προσθ�?κη εξωτερικού σύνδεσμου στο αριστερό μενού";
$langDeleteLink = "Είστε βέβαιος/η ότι θέλετε να διαγράψετε τον σύνδεσμο";
$langOperations="Ενέργειες σε εξωτερικούς σύνδεσμους";
$langInactiveTools = "Ανενεργά εργαλεία";
$langSubmitChanges = "�?ποβολ�? αλλαγών";

/********************************************************************
* trad4all.inc.php
*********************************************************************/
$iso639_2_code = "el";
$langNameOfLang['greek']="Ελληνικά";
$langNameOfLang['english']="Αγγλικά";
$langNameOfLang['french']="Γαλλλικά";
$charset = 'UTF-8';
$dateFormatShort =  "%b %d, %y";
$dateFormatLong  = '%A, %d %B %Y';
$dateTimeFormatLong  = '%d %B %Y / Ώρα: %R';
$timeNoSecFormat = '%R';
$langNoAdminAccess = '
		<p><b>Η σελίδα
		που προσπαθείτε να μπείτε απαιτεί όνομα
		χρ�?στη και συνθηματικό.</b> <br/>Η πλατφόρμα σας ανακατεύθυνε αυτόματα στην αρχικ�? σελίδα
		για να συνδεθείτε πρωτού προχωρ�?σετε σε άλλες ενέργειες. Πιθανόν να έληξε η σύνοδός σας.</p>
';

$langLoginRequired = '
		<p><b>Δεν είστε εγγεγραμμένος στο μάθημα και επομένως δεν μπορείτε να χρησιμοποι�?σετε το αντίστοιχο υποσύστημα.</b>
		Η πλατφόρμα σας ανακατεύθυνε αυτόματα στην αρχικ�? σελίδα
		για να εγγραφείτε στο μάθημα, αν η εγγραφ�? είναι ελεύθερη. </p>
';
$langSessionIsLost = "
		<p><b>Η σύνοδος σας έχει λ�?ξει. </b><br/>Η πλατφόρμα σας ανακατεύθυνε αυτόματα στην αρχικ�? σελίδα
		για να συνδεθείτε προτού προχωρ�?σετε σε άλλες ενέργειες.</p>
			";
$langCheckProf = "
		<p><b>Η ενέργεια που προσπαθ�?σατε να εκτελέσετε απαιτεί δικαιώματα καθηγητ�?. </b><br/>
		Η πλατφόρμα σας ανακατεύθυνε αυτόματα στην αρχικ�? σελίδα
		για να συνδεθείτε ξανά.</p>
";
$langLessonDoesNotExist = "
	<p><b>�?ο μάθημα που προσπαθ�?σατε να προσπελάσετε δεν υπάρχει.</b><br/>
	Αυτό μπορεί να συμβαίνει λόγω του ότι εκτελέσατε μια μη επιτρεπτ�? ενέργεια �? λόγω προβλ�?ματος
	στην πλατφόρμα.</p>
";
$langCheckAdmin = "
		<p><b>Η ενέργεια που προσπαθ�?σατε να εκτελέσετε απαιτεί δικαιώματα διαχειριστ�?. </b><br/>
		Η πλατφόρμα σας ανακατεύθυνε αυτόματα στην αρχικ�? σελίδα
		για να συνδεθείτε, εάν είστε ο διαχειριστ�?ς της πλατφόρμας.</p>
";
$langCheckGuest = "
		<p><b>Η ενέργεια που προσπαθ�?σατε να εκτελέσετε δεν είναι δυνατ�? με δικαιώματα επισκέπτη χρ�?στη. </b><br/>
		Για λόγους ασφάλειας η πλατφόρμα σας ανακατεύθυνε αυτόματα στην αρχικ�? σελίδα
		για να συνδεθείτε ξανά.</p>
";
$langCheckPublicTools = "
		<p><b>Προσπαθ�?σατε να αποκτ�?σετε πρόσβαση σε απενεργοποιημένο εργαλείο μαθ�?ματος. </b><br/>
		Για λόγους ασφάλειας η πλατφόρμα σας ανακατεύθυνε αυτόματα στην αρχικ�? σελίδα
		για να συνδεθείτε ξανά.</p>
";
$langUserBriefcase = "Χαρτοφυλάκιο χρ�?στη";
$langPersonalisedBriefcase = "Προσωπικό χαρτοφυλάκιο";
$langCopyrightFooter="Copyright &copy; 2003-2008 GUnet";
$langAdvancedSearch="Σύνθετη αναζ�?τηση";
$langTitle = "�?ίτλος";
$langType = "�?ύπος";
/***************************************************************
* unreguser.inc.php
****************************************************************/
$langBackHome = "Επιστροφ�? στην αρχικ�? σελίδα";
$langAdminNo = "Ο λογαριασμός του διαχειριστ�? της πλατφόρμας δεν μπορεί να διαγραφεί!";
$langExplain = "Για να διαγραφείτε από την πλατφόρμα, πρέπει πρώτα να απεγγραφείτε από τα μαθ�?ματα που είστε εγγεγραμμένος.";
$langConfirm = "Επιβεβαίωση διαγραφ�?ς λογαριασμού";
$langDelSuccess = "Ο λογαριασμός σας στην πλατφόρμα έχει διαγραφεί.";
$langThanks = "Ευχαριστούμε για τη χρ�?ση της πλατφόρμας!";
$langNotice = "Σημείωση";
$langModifProfile="Αλλαγ�? του προφίλ μου";

//unregcours.php
$langUnregCours = "Απεγγραφ�? από μάθημα";
$langCoursDelSuccess = "Η απεγγραφ�? σας από το μάθημα έγινε με επιτυχία";
$langCoursError = "Σφάλμα κατά την απεγγραφ�? του χρ�?στη";
$langConfirmUnregCours = "Θέλετε σίγουρα να απεγγραφείτε από το μάθημα με κωδικό";

/*******************************************************************
* usage.inc.php
********************************************************************/
 $langGDRequired = "Απαιτείται η βιβιλιοθ�?κη GD!";
 $langPersonalStats="�?α στατιστικά μου";
 $langUserLogins="Επισκέψεις χρηστών στο μάθημα";
 $langStartDate = "Ημερομηνία Έναρξης";
 $langEndDate = "Ημερομηνία Λ�?ξης";
 $langAllUsers = "Όλοι οι Χρ�?στες";
 $langAllCourses = "Όλα τα μαθ�?ματα";
 $langSubmit = "�?ποβολ�?";
 $langModule = "�?ποσύστημα";
 $langAllModules = "Όλα τα �?ποσυστ�?ματα";
 $langValueType = "Είδος Στατιστικών";
 $langQuantity = "Ποσοτικά";
 $langProportion = "Ποσοστιαία";
 $langStatsType = "Είδος Στατιστικών";
 $langTotalVisits = "Συνολικές Eπισκέψεις";
 $langVisits = "Αριθμός Επισκέψεων";
 $langFirstLetterUser = "Πρώτο Γράμμα Επωνύμου";
 $langFirstLetterCourse = "Πρώτο Γράμμα �?ίτλου";
 $langFavourite = "Προτίμηση �?ποσυστημάτων";
 $langFavouriteExpl = "Παρουσιάζεται η προτίμηση ενός χρ�?στη �? όλων των χρηστών στα υποσυστ�?ματα μέσα σε ένα χρονικό διάστημα.";
 $langOldStats = "Εμφάνιση παλιών στατιστικών";
 $langOldStatsExpl = "Παρουσιάζονται συγκεντρωτικά μηνιαία στατιστικά στοιχεία <u>παλιότερα των οκτώ μηνών</u>.";
 $langOldStatsLoginsExpl = "Παρουσιάζονται συγκεντρωτικά μηνιαία στατιστικά σχετικά με τις εισόδους στην πλατφόρμα παλιότερα των οκτώ μηνών.";
 $langInterval = "Διάστημα";
 $langDaily = "Ημερ�?σιο";
 $langWeekly = "Εβδομαδιαίο";
 $langMonthly = "Μηνιαίο";
 $langYearly = "Ετ�?σιο";
 $langSummary = "Συνολικά";
 $langDurationVisits = "Χρονικ�? Διάρκεια Επισκέψεων";
 $langDurationExpl = "Η χρονικ�? διάρκεια των επισκέψεων σε κάθε υποσύστημα είναι σε λεπτά της ώρας και υπολογίζεται κατά προσέγγιση.";
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
 $langPre = "Προπτυχιακό";
 $langPost = "Μεταπτυχιακό";
 $langHidden = "Κλειστό";
 $langPres = "Προπτυχιακά";
 $langPosts = "Μεταπτυχιακά";
 $langAddress = "Διεύθυνση";
 $langLoginDate = "Ημερ/νία εισόδου";
 $langNoLogins = "Δεν έχουν γίνει είσοδοι το συγκεκριμένο χρονικό διάστημα.";
 $langNoStatistics = "Δεν έχουν γίνει επισκέψεις το συγκεκριμένο χρονικό διάστημα.";
 $langStatAccueil = "Για το χρονικό διάστημα που ζητ�?θηκε, διατίθεται και η παρακάτω πληροφορία, για το σύνολο των χρηστών του μαθηματος:";
 $langHost = "�?πολογιστ�?ς";

 #for platform Statistics
 $langUsersCourse = "Χρ�?στες ανά μάθημα";
 $langVisitsCourseStats = "Επισκέψεις σε σελίδες μαθημάτων";
 $langUserStats = "Στατιστικά Χρ�?στη";
 $langTotalVisitsCourses = "Συνολικές επισκέψεις σε σελίδες μαθημάτων";

/****************************************************************
* video.inc.php
*****************************************************************/
$langFileNot="�?ο αρχείο δεν στάλθηκε";
$langTitleMod="Ο τίτλος του εγγράφου τροποποι�?θηκε";
$langFAdd="�?ο αρχείο προστέθηκε";
$langDelF="�?ο αρχείο διαγράφηκε";
$langAddV="Προσθ�?κη βίντεο";
$langAddVideoLink="Προσθ�?κη συνδέσμου βίντεο";
$langsendV="Αποστολ�? αρχείου �?χου �? βίντεο";
$langVideoTitle="�?ίτλος βίντεο";
$langDescr="Περιγραφ�?";
$langDelList="Διαγραφ�? όλων";
$langVideoMod = "�?α στοιχεία του συνδέσμου τροποποι�?θηκαν";
$langVideoDeleted = "Όλοι οι σύνδεσμοι διαγράφηκαν";
$langURL="Εξωτερικός σύνδεσμος προς τον εξυπηρετητ�? �?χου �? βίντεο";
$langcreator="Δημιουργός";
$langpublisher="Εκδότης";
$langdate="Ημερομηνία";
$langNoVideo = "Δεν υπάρχουν διαθέσιμα βίντεο για το μάθημα";
$langEmptyVideoTitle = "Παρακαλώ πληκτρολογ�?στε ένα τίτλο για το βίντεο σας";

/*************************************************************
* wiki.inc.php
**************************************************************/
$langAddImage = "Πρόσθεσε εικόνα";
$langAdministrator = "Διαχειριστ�?ς";
$langChangePwdexp = "Βάλτε δύο φορές νέο κωδικό (password) για να γίνει αλλαγ�?, αφ�?στε κενό για να κρατ�?σετε τον ίδιο";
$langChooseYourPassword = " Επέλεξε ένα όνομα χρ�?στη και έναν κωδικό πρόσβασης για το λογαριασμό χρ�?στη. ";
$langCloseWindow = "Κλείστε το παράθυρο";
$langCodeUsed = "Αυτός ο επίσημος κωδικός χρησιμοποιείται �?δη από άλλο χρ�?στη.";
$langContinue = " Συνέχεια ";
$langCourseManager = "Διαχειριστ�?ς μαθ�?ματος";
$langDelImage = "Διαγραφ�? εικόνας";
$langGroups = "Ομάδες Χρηστών";
$langGroup = "Ομάδα Χρηστών";
$langIs = "είναι";
$langLastname = "Επώνυμο";
$langLegendRequiredFields = "<span class=\"required\">*</span> δείχνει απαραίτητο πεδίο ";
$langMemorizeYourPassord = "Αποστ�?θισε τα, θα τα χρειαστείς την επόμενη φορά που θα μπεις σε αυτ�? τη σελίδα.";
$langModifyProfile = "Αλλαγ�? του προφίλ μου";
$langOfficialCode = "Κωδικός διαχείρισης";
$langOneResp = "Ενας από τους διαχειριστές του μαθ�?ματος";
$langPersonalCourseList = "Προσωπικ�? λίστα μαθ�?ματος";
$langPreview = "Παρουσίαση/Προβολ�?";
$langSaveChanges = "Αποθ�?κευση αλλαγών";
$langTheSystemIsCaseSensitive = "(γίνεται διάκριση μεταξύ κεφαλαίων και πεζών γραμμάτων.)";
$langUpdateImage = "Αλλαγ�? εικόνας";
$langUserIsPlaformAdmin = "είναι διαχειριστ�?ς της πλατφόρμας ";
$langUserid = "�?αυτότητα χρ�?στη";
$langWikiAccessControl = "Διαχείριση ελέγχου πρόσβασης ";
$langWikiAccessControlText = "Μπορείτε να θέσετε τα δικαιώματα πρόσβασης για τους χρ�?στες χρησιμοποιώντας το ακόλουθο πλέγμα: ";
$langWikiAllPages = "Όλες οι σελίδες";
$langWikiBackToPage = "Πίσω στη σελίδα";
$langWikiConflictHowTo = "<p><strong>Αλλάξτε τη σύγκρουση</strong> : Η σελίδα που πρσπαθείτε φαίνετε ότι έχει τροποποιηθεί από την τελευταία φορά που την τροποποίησες.<br /><br />
�?ι θέλετε να γίνει τώρα;<ul>
<li>Μπορείτε να αντιγράψετε/επικολλ�?σετε τις αλλαγές σας σε ένα κειμενογράφο (όπως το notepad) και κάντε κλίκ στο  'edit last version' για να προσπαθ�?σεις να προσθέσεις τις αλλαγές σου στην καινούργια έκδοση της σελίδας.</li>
<li>Μπορείς επίσης να πατ�?σεις στο άκυρο για να ακυρώσεις τις αλλαγές σου.</li>
</ul></p>";
$langWikiContentEmpty = "Αυτ�? η σελίδα είναι κεν�?, κάνε κλικ στο 'Edit this page' για να προσθεσεις περιεχομενο";
$langWikiCourseMembers = "Μέλη μαθ�?ματος ";
$langWikiCreateNewWiki = "Δημιουργ�?στε ένα νέο Wiki";
$langWikiCreatePrivilege = "Δημιουργ�?στε σελίδες ";
$langWikiCreationSucceed = "Η δημιουργία του Wiki �?ταν επιτυχημένη";
$langWikiDefaultDescription = "Εισάγετε την περιγραφ�? του νέου σας wiki έδω";
$langWikiDefaultTitle = "Καινούργιο Wiki";
$langWikiDeleteWiki = "Διαγραφ�? Wiki";
$langWikiDeleteWikiWarning = "ΠΡΟΕΙΔΟΠΟΙΗΣΗ: πρόκειται να διαγράψετε αυτό το wiki και όλες τις σελίδες του. Είστε βέβαιοι ότι θέλετε να συνεχίσετε;";
$langWikiDeletionSucceed = "Η διαγραφ�? του Wiki �?ταν επιτυχημένη";
$langWikiDescription = "Περιγραφ�? του Wiki";
$langWikiDescriptionForm = "Περιγραφ�? Wiki";
$langWikiDescriptionFormText = "Μπορείτε να επιλέξετε έναν τίτλο για το wiki : ";
$langWikiDiffAddedLine = "Προστιθέμενη γραμμ�? ";
$langWikiDiffDeletedLine = "Διαγραμμένη γραμμ�? ";
$langWikiDiffMovedLine = "Μετακινημένη γραμμ�? ";
$langWikiDiffUnchangedLine = "Αμετάβλητη γραμμ�? ";
$langWikiDifferenceKeys = "Κλειδιά :";
$langWikiDifferencePattern = "διαφορές μεταξύ της έκδοσης %1\$s τροποποιημένης από %2\$s και της έκδοσης %3\$s τροποποιημένης απο %4\$s";
$langWikiDifferenceTitle = "Διαφορές :";
$langWikiEditConflict = "Αλλαγ�? σύγκρουσης";
$langWikiEditLastVersion = "Αλλαγ�? τελευταίας έκδοσης";
$langWikiEditPage = "Αλλαγ�? της σελίδας";
$langWikiEditPrivilege = "Αλλαγ�? σελίδων";
$langWikiEditProperties = "Αλλαγ�? ιδιοτ�?των";
$langWikiEditionSucceed = "Η έκδοση Wiki είναι επιτυχημένη";
$langWikiGroupMembers = "Μέλη ομάδας";
$langWikiHelpAdminContent = "<h3>Βο�?θεια διαχείρισης Wiki</h3>
<dl class=\"Βο�?θεια wiki\">
<dt> Πώς να δημιουργ�?σετε έναν νέο Wiki ?</dt>
<dd> Κάντε κλίκ στη σύνδεση 'Create a new Wiki'. Μετά εισάγετε τις ιδιότητες του Wiki :
<ul>
<li><b> �?ίτλος του Wiki</b> : επιλέξτε έναν τίτλο για το Wiki</li>
<li><b> Περιγραφ�? του  Wiki</b> : επιλέξτε μια περιγραφ�? για το Wiki</li>
<li><b> Διαχείριση ελέγχου πρόσβασης </b> : θέστε τον έλεγχο πρόσβασης για τον Wiki επιλέγοντας/αποεπιλέγοντας το κουτί (δείτε πιο κάτω)</li>
</ul>
</dd>
<dt> Πώς να εισαγάγετε το Wiki ?</dt>
<dd> Κάντε κλικ στον τίτλο του Wiki στον κατάλογο.</dd>
<dt> Πώς να αλλάξετε τις ιδιότητες του Wiki ?</dt>
<dd>Κάντε κλικ στο εικονίδιο 'Properties' στην λίστα του Wiki και π�?γαινε στη φόρμα ιδιοτ�?των του Wiki.</dd>
<dt> Πώς να χρησιμοποι�?σει τις διοικητικές επιλογές ελέγχου πρόσβασης;</dt>
<dd> Μπορείτε να θέσετε τα δικαιώματα πρόσβασης για τους χρ�?στες με τον επιλογ�?/αποεπιλογ�? του κουτιού στο \"διοικητικό\" τμ�?μα ελέγχου πρόσβασης των ιδιοτ�?των Wiki.
 Μπορείτε να χορηγ�?σετε/μη χορηγ�?σετε πρόσβαση σε τρεις τύπους χρηστών:<ul>
<li><b> Μέλη μαθημάτων </b> : οι χρ�?στες εγγράφονται στη σειρά μαθημάτων (εκτός από τους διευθυντές μαθημάτων)</li>
<li><b> Μέλη ομάδας </b> (μόνο διαθέσιμο μεσα σε  μια ομάδα) : χρ�?στες που είναι μέλη της ομάδας (αναμείνετε τους δασκάλους ομάδας s)</li>
<li><b>Αλλοι χρ�?στες </b> : ανώνυμοι χρ�?στες �? χρ�?στες που δεν είναι μέλη σειράς μαθημάτων </li></ul>
Για κάθε τύπο χρηστών, μπορείτε να χορηγ�?σετε τον τύπο τρίων προνομίων για το Wiki(*) :<ul>
<li><b> Διαβάστε τις σελίδες </b> : ο χρ�?στης του δεδομένου τύπου μπορεί να διαβάσει τις σελίδες του Wiki</li>
<li><b>Αλλαγ�? σελίδων</b> : ο χρ�?στης του δεδομένου τύπου μπορεί να τροποποι�?σει το περιεχόμενο των σελίδων του Wiki</li>
<li><b> Δημιουργ�?στε τις σελίδες </b> : ο χρ�?στης του δεδομένου τύπου μπορεί να δημιουργ�?σει νέες σελίδες του Wiki</li>
</ul><small><em>(*) Σημειώστε ότι εάν ένας χρ�?στης δεν μπορεί να διαβάσει τις σελίδες του  Wiki, δεν μπορεί να τις αλλάξει �? να τις τροποποι�?σει. Σημειώστε ότι εάν ένας χρ�?στης δεν μπορεί να αλλαξει τις σελίδες του Wiki, δεν μπορεί να δημιουργ�?σει νέες σελίδες.</em></small></dd>
<dt> Πώς να διαγράψει το Wiki ?</dt>
<dd>Κάντε κλικ στο εικονίδιο 'Delete' στη στ�?λη για να σβ�?σετε το Wiki και όλες του τις σελίδες.</dd>
<dt> Πώς να πάρετε τον κατάλογο των σελίδων σε ένα Wiki ;</dt>
<dd>Κάντε κλικ στον αριθμό των σελίδως σε αυτό το Wiki στην λίστα των Wiki.</dd>
<dt> Πώς να πάρετε τον κατάλογο των  τελευταίων τροποποιημένων σελίδων σε ένα Wiki;</dt>
<dd>Κάντε κλικ στο εικονίδιο 'Recent changes' στη στ�?λη του καταλόγου του Wiki.</dd>
</dl>";
$langWikiHelpSyntax = "Σύνταξη του Wiki ";
$langWikiHelpSyntaxContent = "<h1>Σύνταξη Wiki </h1>
<h2>1. Βασικ�? σύνταξη </h2>
<dl class=\"Βο�?θεια wiki\">
<dt> Δημιουργία των σελίδων και των συνδέσεων wiki μεταξύ τους </dt>
<dd><strong>Λέξεις Wiki </strong> : Οι λέξεις Wiki είναι λέξεις που γράφονται όπως <em>Λέξη Wiki</em>. �?α Wiki2xhtml τους αναγνωρίζουν ατόματα ως συνδέσεις σελίδων Wiki. Για να δημιουργ�?σετε μια σελίδα wiki �? για να δημιουργ�?σετε μια σύνδεση με μια σελίδα wiki, τροποποι�?στε μια �?δη υπάρχουσα και προσθέστε το τίτλο στην σύνταξη του wiki, για παράδειγμα <em>Η σελίδα μου</em>, και μετά φύλαξε τη σελίδα. Wiki2xhtml θα αντικαταστ�?σει αυτόματα την λέξη<em>Η σελίδα μου</em> με μια σύνδεση με τη σελίδα Wiki <em>Η σελίδα μου</em>&nbsp;;</dd>
<dd><strong> συνδέσεις  Wiki </strong> : Οι συνδέσεις Wiki είναι όπως τους συνδέσμους υπερ-κειμένου (βλ. κατωτέρω) αναμένουν ότι δεν περιέχουν οποιοδ�?ποτε σχέδιο πρωτοκόλλου (όπως <em>http://</em> �? <em>ftp://</em>) και ότι αυτόματα αναγνωρίζουν συνδέσμους σε σελίδες  Wiki. Για να δημιουργ�?σετε μια νέα σελίδα �? να δημιουργ�?σετε μια σύνδεση με μια υπάρχουσα που χρησιμοποιεί τις συνδέσεις Wiki, αλλαξτε μια σελίδα και προσθέστε <code>[page title]</code> η <code>[name of link|title of page]</code> στα περιεχόμενα του. Μπορείτε επίσης να χρησιμοποι�?σετε αυτ�?ν την σύνταξη για να αλλάξετε το κείμενο μιας σύνδεσης WikiWord: <code>[όνομα συνδέσμου|WikiWord]</code>.</dd>
<dt> Σύνδεσμοι υπερ-κειμένου </dt>
<dd><code>[url]</code>, <code>[name|url]</code>, <code>[name|url|language]</code> or <code>[name|url|language|title]</code>.&nbsp;;</dd>
<dt> Συνυπολογισμός εικόνας </dt>
<dd><code>((url|alternate text))</code>, <code>((url| εναλλάσσομενο κείμενο |position))</code> ou <code>((url|alternate text|position|long description))</code>. <br /> �?ο επιχείρημα θέσης μπορεί να πάρει τις ακόλουθες τιμές : L (αριστερά), R (δεξιά) or C (κεντρικά).&nbsp;;</dd>
<dd> Μπορείτε να χρησιμοποι�?σετε τη σύνταξη ως συνδέσμους υπερ-κειμένου. Παραδείγματος χάριν <code>[τίτλος|image.gif]</code>. Αυτ�? η σύνταξη είναι αποδοκιμασμένη, σκεφτ�?τε να χρησιμποι�?σετε την προηγούμενη&nbsp;;</dd>
<dt> Σύνδεση με μια εικόνα </dt>
<dd> όπως τους συνδέσμους υπερ-κειμένου αλλά τεθειμένο 0 στο τέταρτο επιχείρημα για να αποφευχθεί η αναγνώριση εικόνας και να φταθεί ένας σύνδεσμος υπερ-κειμένου σε μια εικόνα. Παραδείγματος χάριν <code>[image|image.gif||0]</code> θα επιδείξει μια σύνδεση με την image.gif iαντι για επίδειξη της ίδιας της φωτογραφίας</dd>
<dt> Σχεδιάγραμμα </dt>
<dd><strong> Κυρτός </strong> : περιβάλτε το κείμενό σας με δύο ενιαία αποσπάσματα <code>'' κείμενο ''</code>&nbsp;;</dd>
<dd><strong>Εντονα</strong> : περιβάλτε το κείμενό σας με τρία ενιαία αποσπάσματα υπογραμμίζει <code>''' κείμενο '''</code>&nbsp;;</dd>
<dd><strong>�?πογράμμιση</strong> : περιβάλτε το κείμενό σας με δύο υπογραμμίζει <code>__ κείμενο __</code>&nbsp;;</dd>
<dd><strong> Γραμμ�?</strong> : περιβάλτε το κείμενό σας με δύο αρνητικά σύμβολα <code>-- κείμενο --</code>&nbsp;;</dd>
<dd><strong> �?ίτλος </strong> : <code>!!!</code>, <code>!!</code>, <code>!</code> αντίστοιχα για τους τίτλους, τους υποτίτλους και τους υπο-υπο-τίτλους &nbsp;;</dd>
<dt> Κατάλογος </dt>
<dd> γραμμ�? αρχίζοντας από <code>*</code> (άδιάτακτος κατάλογος) �? <code>#</code> (διαταγμένος κατάλογος). Μπορείτε να αναμίξετε τους καταλόγους (<code>*#*</code>) για να δημιουργ�?θούν πολυ - κατάλογοι επιπέδων.&nbsp;;</dd>
<dt> Παράγραφος </dt>
<dd> Χωριστές παράγραφοι με μια �? περισσότερες νέες γραμμές &nbsp;;</dd>
</dl>
<h2>2. Προχωρημένη σύνταξη </h2>
<dl class=\"Βο�?θεια wiki\">
<dt> �?ποσημείωση </dt>
<dd><code>\$\$ κείμενο υποσημειώσεων \$\$</code>&nbsp;;</dd>
<dt>προκαθοριμένο κείμενο </dt>
<dd> αρχίστε κάθε γραμμ�? του κείμενο με ένα κενό διάστημα &nbsp;;</dd>
<dt> Αναφέρετε φραγμού </dt>
<dd><code>&gt;</code> �? <code>;:</code> πριν από κάθε γραμμ�? &nbsp;;</dd>
<dt> Οριζόντια γραμμ�? </dt>
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
<dd><code>~στ�?ριγμα~</code>&nbsp;;</dd>
</dl>";
$langWikiIdenticalContent = " Ίδιο περιεχόμενο <br />καμιά αλλαγ�? δεν αποθηκεύτηκε";
$langWikiInvalidWikiId = "Μη έγκυρο Wiki Id";
$langWikiList = "Λίστα του Wiki";
$langWikiMainPage = "Κύρια σελίδα";
$langWikiMainPageContent = "Αυτη είναι η κύρια σελίδα του Wiki %s. Επέλεξε '''Αλλαγ�? της σελίδας''' για να τροποποι�?σεις το περιεχόμενο.";
$langWikiNoWiki = "Κανένα Wiki";
$langWikiNotAllowedToCreate = " Δεν επιτρέπεται να δημιουργ�?σεις σελίδα";
$langWikiNotAllowedToEdit = " Δεν επιτρέπεται να αλλάξεις τη σελίδα";
$langWikiNotAllowedToRead = "Δεν επιτρέπεται να διαβάσεις τη σελίδα";
$langWikiNumberOfPages = "Αριθμός σελίδων";
$langWikiOtherUsers = "Άλλοι χρ�?στες (*)";
$langWikiOtherUsersText = "(*) ανώνυμοι χρ�?στες και χρ�?στες που δεν είναι μέλη αυτού του μαθ�?ματος...";
$langWikiPageHistory = "Ιστορικό σελίδας";
$langWikiPageSaved = "Η σελίδα αποθηκεύτηκε";
$langWikiPreviewTitle = "Προεπισκόπηση : ";
$langWikiPreviewWarning = " ΠΡΟΕΙΔΟΠΟΙΗΣΗ: Αυτ�? η σελίδα αποτελεί προεπισκόπηση.  Οι τροποποι�?σεις σας στο wiki δεν έχουν αποθηκευτεί ακόμα ! Για να τις αποθηκεύσετε μη ξεχάσετε να κάνετε κλικ στο κουμπί 'save' στο τέλος της σελίδας.";
$langWikiProperties = "Ιδιότητες";
$langWikiReadPrivilege = "Διάβασε σελίδες";
$langWikiRecentChanges = "Πρόσφατες αλλαγές";
$langWikiRecentChangesPattern = "%1\$s τροποποι�?θηκε στις %2\$s από %3\$s";
$langWikiShowDifferences = "Δείξε τις διαφορές";
$langWikiTitle = "�?ίτλος του wiki";
$langWikiTitleEdit = "Wiki : Αλλάξετε τις ιδιότητες";
$langWikiTitleNew = "Wiki : Δημιούργησε καινούργιο Wiki";
$langWikiTitlePattern = "Wiki : %s";
$langWikiVersionInfoPattern = "(έκδοση από %1\$s τροποποιημένη απο%2\$s)";
$langWikiVersionPattern = "%1\$s απο %2\$s";
$lang_footer_p_CourseManager = "�?πεύθυνος για %s";
$lang_p_platformManager = "Διαχειριστ�?ς για το %s";
$langWikiUrl = "Πληκτρολογ�?στε τον σύνδεσμο";
$wiki_toolbar['Strongemphasis'] = "Έντονα";
$wiki_toolbar['Emphasis'] = "Πλαγιαστά";
$wiki_toolbar['Inserted'] = "Εισαγωγ�?";
$wiki_toolbar['Deleted'] = "Διαγραμμένα";
$wiki_toolbar['Inlinequote'] = "Σχόλιο";
$wiki_toolbar['Code'] = "Κώδικας";
$wiki_toolbar['Linebreak'] = "Αλλαγ�? γραμμ�?ς";
$wiki_toolbar['Blockquote'] = "Παράγραφος";
$wiki_toolbar['Preformatedtext'] = "Μορφοποιημένο κείμενο";
$wiki_toolbar['Unorderedlist'] = "Λίστα";
$wiki_toolbar['Orderedlist'] = "Διατεταγμένη λίστα";
$wiki_toolbar['Externalimage'] = "Εξωτερικ�? εικόνα";
$wiki_toolbar['Link'] = "Σύνδεσμος";

/*************************************************************
* work.inc.php
**************************************************************/
$langBackAssignment = "Επιστροφ�? στη σελίδα της εργασίας";
$m['activate'] = "Ενεργοποίηση";
$m['deactivate'] = "Απενεργοποίηση";
$m['deadline'] = "Προθεσμία υποβολ�?ς";
$m['username'] = "Όνομα ".$langsOfStudent." ";
$m['filename'] = "Όνομα αρχείου";
$m['sub_date'] = "Ημ/νία αποστολ�?ς";
$m['comments'] = "Σχόλια";
$m['gradecomments'] = "Σχόλια βαθμολογητ�?";
$m['addgradecomments'] = "Προσθ�?κη σχολίων βαθμολογητ�?";
$m['delete'] = "Διαγραφ�?";
$m['edit'] = "Αλλαγ�?";
$m['start_date'] = "Ημερομηνία έναρξης";
$m['grade'] = "Βαθμός";
$m['am'] = "Αρ. Mητρώου";
$m['yes'] = "Ναι";
$m['no'] = "Όχι";
$m['in'] = "σε";
$m['today'] = "σ�?μερα";
$m['tomorrow'] = "αύριο";
$m['expired'] = "έχει&nbsp;λ�?ξει";
$m['submitted'] = "Έχει&nbsp;αποσταλεί";
$m['select'] = "Επιλογ�?";
$m['groupsubmit'] = "�?ποβλ�?θηκε εκ μέρους της";
$m['ofgroup'] = "ομάδας";
$m['deleted_work_by_user'] = "Διαγράφηκε η προηγούμενη υποβληθείσα
	εργασία που είχατε στείλει με το αρχείο";
$m['deleted_work_by_group'] = "Διαγράφηκε η προηγούμενη εργασία που
	είχε υποβληθεί από κάποιο μέλος της ομάδας σας και βρισκόταν στο αρχείο";
$m['by_groupmate'] = 'Από άλλο μέλος της ομάδας σας';
$m['the_file'] = '�?ο αρχείο';
$m['was_submitted'] = 'υποβλ�?θηκε στην εργασία.';
$m['group_sub'] = 'Επιλέξτε αν θέλετε να υποβάλετε το αρχείο αυτό
	εκ μέρους της ομάδας σας';
$m['group'] = 'ομάδα';
$m['already_group_sub'] = 'Έχει �?δη υποβληθεί η εργασία αυτ�? από κάποιο
	μέλος της ομάδας σας';
$m['group_or_user'] = '�?ύπος εργασίας';
$m['group_work'] = 'Ομαδικ�?';
$m['user_work'] = 'Ατομικ�?';
$m['submitted_by_other_member'] = '�?ο αρχείο αυτό υποβλ�?θηκε από άλλο μέλος της';
$m['your_group'] = 'ομάδας σας';
$m['this_is_group_assignment'] = 'Η εργασία αυτ�? είναι ομαδικ�?. Για να
	στείλετε κάποιο αρχείο, πηγαίνετε στο';
$m['group_documents'] = 'χώρο αρχείων της ομάδας σας';
$m['select_publish'] = 'και επιλέξτε «Δημοσίευση» για το αρχείο που θέλετε.';
$m['noguest'] = 'Για να αποστείλετε εργασία πρέπει να συνδεθείτε ως κανονικός χρ�?στης.';
$m['one_submission'] = 'Έχει υποβληθεί μία εργασία';
$m['more_submissions'] = 'Έχουν υποβληθεί %d εργασίες';
$m['plainview'] = 'Συνοπτικ�? λίστα εργασιών - βαθμολογίας';
$m['WorkInfo']= 'Στοιχεία εργασίας';
$m['WorkView']= 'Προβολ�? εργασίας';
$m['WorkDelete']= 'Διαγραφ�? εργασίας';
$m['WorkEdit']= '�?ροποποίηση εργασίας';
$m['SubmissionWorkInfo']= 'Στοιχεία υποβολ�?ς εργασίας';
$m['SubmissionStatusWorkInfo']= 'Κατάσταση υποβολ�?ς εργασίας';
$langGroupWorkIntro = '
	Παρακάτω εμφανίζονται οι διαθέσιμες εργασίες που έχουν ανατεθεί
	στα πλαίσια αυτού του μαθ�?ματος. Παρακαλούμε επιλέξτε την εργασία όπου θέλετε
	να αποστείλετε το αρχείο ως εργασία της ομάδας σας, και συμπληρώστε
	τυχόν σχόλια που θέλετε να διαβάσει ο διδάσκων του μαθ�?ματος. Σημειώστε ότι
	αν στείλετε ένα αρχείο για εργασία που έχει �?δη υποβληθεί κάποιο αρχείο ως
	ομαδικ�? εργασία, είτε από εσάς είτε από κάποιο άλλο μέλος της ομάδας, το
	αρχείο αυτό θα χαθεί και θα αντικατασταθεί από το νέο. �?έλος,
	δεν μπορείτε να στείλετε αρχείο σε εργασία που έχει �?δη βαθμολογηθεί
	από τον διδάσκοντα.';

$langGroupWorkSubmitted = "Έχει&nbsp;αποσταλεί";
$langGroupWorkSubmitted1 = "ΔΕΝ έχει&nbsp;αποσταλεί";
$langGroupWorkDeadline_of_Submission = "Προθεσμία υποβολ�?ς";
$langEmptyAsTitle = "Δεν συμπληρώσατε τον τίτλο της εργασίας";
$langEditSuccess = "Η διόρθωση των στοιχείων της εργασίας έγινε με επιτυχία!";
$langEditError = "Παρουσιάστηκε πρόβλημα κατά την διόρθωση των στοιχείων !";
$langNewAssign = "Δημιουργία Εργασίας";
$langDeleted = "Η εργασία διαγράφηκε";
$langDelAssign = "Διαγραφ�? Εργασίας";
$langDelWarn1 = "Πρόκειται να διαγράψετε την εργασία με τίτλο";
$langDelSure = "Είστε σίγουρος;";
$langWorkFile = "Αρχείο";
$langZipDownload = "Κατέβασμα όλων των εργασιών σε αρχείο .zip";
$langDelWarn2 = "Έχει αποσταλεί μία εργασία ".$langsOfStudent.". �?ο αρχείο αυτό θα διαγραφεί!";
$langDelTitle = "Προσοχ�?!";
$langDelMany1 = "Έχουν αποσταλεί";
$langDelMany2 = "εργασίες ".$langsOfStudents.". �?α αρχεία αυτά θα διαγραφούν!";
$langSubmissions = "Εργασίες που έχουν υποβληθεί";
$langSubmitted = "Η εργασία αυτ�? έχει �?δη υποβληθεί.";
$langNotice2 = "Ημερομηνία αποστολ�?ς";
$langNotice3 = "Αν στείλετε κάποιο άλλο αρχείο, το αρχείο που υπάρχει
	αυτ�? τη στιγμ�? θα διαγραφεί και θα αντικατασταθεί με το νέο.";
$langSubmittedAndGraded = "Η εργασία αυτ�? έχει �?δη υποβληθεί και βαθμολογηθεί.";
$langSubmissionDescr = "Ο ".$langsStudent." %s, στις %s, έστειλε το αρχείο με όνομα \"%s\".";
$langEndDeadline = "(η προθεσμία έχει λ�?ξει)";
$langWEndDeadline = "(η προθεσμία λ�?γει αύριο)";
$langNEndDeadLine = "(η προθεσμία λ�?γει σ�?μερα)";
$langDays = "ημέρες)";
$langDaysLeft = "(απομένουν";
$langGrades = "H βαθμολογία σας κατοχυρώθηκε με επιτυχία";
$langUploadSuccess = "�?ο ανέβασμα της εργασίας σας ολοκληρώθηκε με επιτυχία !";
$langUploadError = "Πρόβλημα κατά το ανέβασμα της εργασίας!";
$langWorkGrade = "Η εργασία έχει βαθμολογηθεί με βαθμό";
$langGradeComments = "�?α σχόλια του βαθμολογητ�? �?ταν:";
$langGradeOk = "Καταχώρηση αλλαγών";
$langGroupSubmit = "Αποστολ�? ομαδικ�?ς εργασίας";
$langGradeWork = "Σχόλια βαθμολογίας";
$langUserOnly="Για να υποβάλλετε μια εργασία πρέπει να κάνετε login στη πλατφόρμα.";
$langNoSubmissions = "Δεν έχουν υποβληθεί εργασίες";
$langNoAssign = "Δεν υπάρχουν εργασίες";
$langWorkWrongInput = 'Ο βαθμός πρέπει να είναι νούμερο. Παρακαλώ επιστρέψτε και ξανασυμπληρώστε το πεδίο.';
$langWarnForSubmissions = "Αν έχουν υποβληθεί εργασίες, αυτες θα διαγραφούν";
$langAssignmentActivated = "Η εργασία ενεργοποι�?θηκε";
$langAssignmentDeactivated = "Η εργασία απενεργοποι�?θηκε";
$langSaved = "�?α στοιχεία της εργασίας αποθηκεύτηκαν";
$langExerciseNotPermit="Η υποβολ�? της εργασίας δεν επιτρέπεται!";
$langGraphResults="Κατανομ�? βαθμολογιών εργασίας";

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
$langUnableUpadatePost="Αδύνατο να ανανεωθεί το μ�?νυμά σας στη βάση δεδομένων ";
$langUnableUpadateTopic="Αδύνατο να ανανέωθεί το θέμα σας στη βάση δεδομένων";
$langUnableDeletePost="Αδύνατο να διαγραφεί το μ�?νυμα σας στη βάση δεδομένων";
$langPostRemoved="Αδύνατο να ανανεωθεί το προηγούμενο μηνυμα σας - το τελευταίο μ�?νυμα έχει μεταφερθεί ";
$langUnableDeleteTopic="Αδύνατο να διαγραφεί το θέμα απο τη βάση δεδομένων ";
$langTopicInformation="Δεν �?ταν δυνατ�? η ερώτηση στην βάση δεδομένων.";
$langErrorTopicSelect="�?ο θέμα της περιοχ�?ς συζητ�?σεων που επιλέξατε δεν υπάρχει. Παρακαλώ επιστρέψτε και προσπαθ�?στε πάλι.";
$langUserTopicInformation="<p>Δεν �?ταν δυνατ�? η ερώτηση στην βάση δεδομένων.";

/*************************************************************
newtopic.php
**************************************************************/
$langErrorDataForum="�?α δεδομένα του forum δεν είναι διαθέσιμα.";
$langErrorPost="Η περιοχ�? συζητ�?σεων που προσπαθείτε να συμμετάσχετε δεν υπάρχει. Παρακαλώ προσπαθ�?στε ξανά.";
$langErrorEnterTopic="Αδύνατη η εισαγωγ�? θέματος στη Βάση Δεδομένων.";
$langErrorEnterPost="Αδύνατη η εισαγωγ�? μυν�?ματος στη Βάση Δεδομένων.";
$langErrorEnterTextPost="Αδύνατη η εισαγωγ�? κειμένου!";
$langErrorEnterTopicTable="Αδύνατη η ανανέωση του θέματος!";
$langErrorUpdatePostCount="Αδύνατη η ανανέωση του μετρητ�? μυνημάτων .";

/*************************************************************
vietopic.php
**************************************************************/
$langErrorConnectPostDatabase="Παρουσιάστηκε πρόβλημα. Αδύνατη η σύνδεση με τη βάση δεδομένων των μυνημάτων.";

/*************************************************************
vietopic.php
**************************************************************/
$langAddTime="Προσθ�?κη ενεργού χρόνου στους απενεργοποιημένους λογαριασμούς.";
$langRealised="Πραγματοποι�?θηκαν";
$langUpdates="ενημερώσεις";
$langNoChanges="Πρόβλημα! Δεν πραγματοποι�?θηκε καμία αλλαγ�?!";
