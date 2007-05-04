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

// infocours.php

$langGeneralInfo = "Γενικές Πληροφορίες";
$langBackupCourse="Αντίγραφο ασφαλείας του μαθήματος";
$langModifInfo="Διαχείριση Μαθήματος";
$langModifDone="Η πληροφορία έχει αλλάξει";
$langHome="Επιστροφή στην αρχική σελίδα";
$langCode="Κωδικός Μαθήματος";
$langDelCourse="Διαγραφή του μαθήματος";
$langDelUsers="Διαγραφή χρηστών από το μάθημα";
$langProfessors="Καθηγητές";
$langTitle="Τίτλος Μαθήματος";
$langFaculty="Τμήμα";
$langDescription="Περιγραφή";
$langConfidentiality="Πρόσβαση στο μάθημα";
$langPublic="Ανοικτό (Ελεύθερη Πρόσβαση από τη αρχική σελίδα χωρίς συνθηματικό)";
$langPrivOpen="Ανοικτό με Εγγραφή (Ελεγχόμενη Πρόσβαση με ανοικτή εγγραφή)";
$langPrivate="Κλειστό (Πρόσβαση στο μάθημα έχουν μόνο οι χρήστες που βρίσκονται στη <a href=../user/user.php>Λίστα
Χρηστών</a>)";
$langForbidden="Μη επιτρεπτή";
$langLanguage="Γλώσσα του Μαθήματος";
$langConfTip="Εξ ορισμού, το μάθημα είναι προσπελάσιμο σε όλους του χρήστες. Αν θέλετε ελεγχόμενη πρόσβαση, μπορείτε να επιλέξετε 'Ανοικτό με Εγγραφή' και να ζητήσετε από τους χρήστες να εγγραφούν. Μόλις ολοκληρωθεί η εγγραφή, μπορείτε να επιλέξετε 'Κλειστό'.";
$langOptPassword = "Προαιρετικό συνθηματικό: ";
$langOtherActions = "Αλλες ενέργειες";
// delete_course.php
$langModifGroups="Ομάδες Εργασίας";
$langDelCourse="Διαγραφή ολόκληρου του μαθήματος";
$langCourse="Tο μάθημα";
$langHasDel="έχει διαγραφεί";
$langBack="Επιστροφή";
$langBackHome="Επιστροφή στην αρχική σελίδα του";
$langByDel="Διαγράφοντας το μάθημα θα διαγραφούν μόνιμα όλα τα περιεχόμενα του και όλοι οι φοιτητές που είναι γραμμένοι σε αυτό (δεν θα διαγραφούν από τα άλλα μαθήματα).<p>Θέλετε πράγματι να διαγράψετε το";
$langY="ΝΑΙ";
$langN="ΟΧΙ";

// deluser_course.php
$langConfirmDel = "Επιβεβαίωση διαγραφής μαθήματος";
$langUserDel="Πρόκειται να διαγράψετε όλους τους μαθητές από το μάθημα (δεν θα διαγραφτούν από τα άλλα μαθήματα).<p>Θέλετε πράγματι να προχωρήσετε στη διαγραφή τους από το μάθημα";

// refresh course.php
$langRefreshCourse = "Ανανέωση μαθήματος";

$langRefresh="Προκειμένου να προετοιμάσετε το μάθημα για μια νέα ομάδα φοιτητών μπορείτε να διαγράψετε το παλιό περιεχόμενο. Επιλέξτε ποιες ενέργειες θέλετε να πραγματοποιηθούν.";
$langUserDelCourse="Διαγραφή χρηστών από το μάθημα";
$langUserDelNotice = "Σημ.: Οι χρήστες δεν θα διαγραφτούν από τα άλλα μαθήματα";
$langAnnouncesDel = "Διαγραφή ανακοινώσεων του μαθήματος";
$langAgendaDel = "Διαγραφή εγγραφών από την ατζέντα του μαθήματος";
$langHideDocuments = "Απόκρυψη των εγγράφων του μαθήματος";
$langHideWork = "Απόκρυψη των εργασιών του μαθήματος";
$langSubmit = "Εκτέλεση ενεργειών";
$langActions = "Ενέργειες";
$langOptions = "Επιλογές";
$langRefreshSuccess = "Η ανανέωση του μαθήματος ήταν επιτυχής. Εκτελέσθηκαν οι ακόλουθες ενέργειες:";

$langUsersDeleted="Οι χρήστες διαγράφηκαν από το μάθημα";
$langAnnDeleted="Οι ανακοινώσεις διαγράφηκαν από το μάθημα";
$langAgendaDeleted="Οι εγγραφές της ατζέντας διαγράφηκαν από το μάθημα";
$langWorksDeleted="Οι εργασίες απενεργοποιήθηκαν";
$langDocsDeleted="Τα έγγραφα απενεργοποιήθηκαν";
?>
