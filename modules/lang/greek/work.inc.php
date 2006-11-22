<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                               |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$           |
          |   English Translation                                                |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Andrew Lynn       <Andrew.Lynn@strath.ac.uk>                |
      +----------------------------------------------------------------------+
 */


/***************************************************************
*                   Language translation
****************************************************************
GOAL
****
Translate the interface in chosen language

*****************************************************************/


// GENERIC

$langHelp="Βοήθεια";
$langSubmit='Αποστολή';
$langBackAssignment = "Επιστροφή στη σελίδα της εργασίας";
$langBack = "Επιστροφή";

// ------------------
// new messages


$m['title'] = "Εργασία";
$m['description'] = "Περιγραφή";
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
	να αποστείλετε το αρχείο "%s" ως εργασία της ομάδας σας, και συμπληρώστε
	τυχόν σχόλια που θέλετε να διαβάσει ο διδάσκων του μαθήματος. Σημειώστε ότι
	αν στείλετε ένα αρχείο για εργασία που έχει ήδη υποβληθεί κάποιο αρχείο ως
	ομαδική εργασία, είτε από εσάς είτε από κάποιο άλλο μέλος της ομάδας, το
	αρχείο αυτό θα χαθεί και θα αντικατασταθεί από το νέο. Τέλος,
	δεν μπορείτε να στείλετε αρχείο σε εργασία που έχει ήδη βαθμολογηθεί
	από τον διδάσκοντα.';

$langModify="Τροποποίηση";
$langDelete="Διαγραφή";
$langEdit = "Διόρθωση";
$langAdd = "Προσθήκη";
$langEditSuccess = "Η διόρθωση των στοιχείων της εργασίας έγινε με επιτυχία!";
$langEditError = "Παρουσιάστηκε πρόβλημα κατά την διόρθωση των στοιχείων !";
$langNewAssign = "Δημιουργία Εργασίας";
$langDeleted = "Η εργασία διαγράφτηκε";
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

$langWorks="Εργασίες Φοιτητών";
$langGroupSubmit = "Αποστολή ομαδικής εργασίας";
$langSubmit = "Αποστολή εργασίας";
$langGradeWork = "Σχόλια βαθμολογίας";

$langUserOnly="Για να υποβάλλετε μια εργασία πρέπει να κάνετε login στη πλατφόρμα.";

$langNoSubmissions = "Δεν έχουν υποβληθεί εργασίες";

$langNoAssign = "Δεν υπάρχουν εργασίες";

// work-old.php messages
$langProfOnly = 'Ο χώρος αυτός είναι διαθέσιμος μόνο στον διαχειριστή του
	μαθήματος. Παρακαλούμε επιστρέψτε στις <a href="work.php">εργασίες
	φοιτητών</a>';
$langWorksOld = 'Παλιές εργασίες';
$langOldWork = "<p>Υπάρχουν %d <a href='work_old.php'>παλιές εργασίες
	φοιτητών</a>.</p>\n";
$langWorkWrongInput = 'Ο βαθμός πρέπει να είναι νούμερο. Παρακαλώ επιστρέψτε και ξανασυμπληρώστε το πεδίο.';

?>
