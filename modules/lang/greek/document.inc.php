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

$langDoc="Έγγραφα";
$langDownloadFile= "Ανέβασμα αρχείου στον εξυπηρέτη";
$langDownload="Ανέβασμα";
$langCreateDir="Δημιουργία καταλόγου";
$langName="Όνομα";
$langNameDir="Όνομα του καινούριου καταλόγου";
$langSize="Μέγεθος";
$langDate="Ημερομηνία";
$langMove="Μετακίνηση";
$langMoveFrom = "Μετακίνηση του αρχείου";
$langRename="Μετονομασία";
$langComment="Σχόλια";
$langOkComment="Επικύρωση αλλαγών"; //"Προσθήκη / Αλλαγή";
$langSave = "Αποθήκευση";
$langVisible="Ορατό / Αόρατο";
$langCopy="Αντιγραφή";
$langTo="στο";
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
$langUp="Πάνω";
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
$langAreYouSureToDelete = "Είστε βέβαιος ότι θέλετε να διαγράψετε το: ";

$langPublish = "Δημοσίευση";
$langParentDir = "αρχικό κατάλογο";
$langNoticeGreek = "(*) Προσοχή! Το όνομα του αρχείου δεν πρέπει να περιέχει ελληνικούς χαρακτήρες";
$langInvalidDir = "Ακυρο ή μη υπαρκτό όνομα καταλόγου";

//prosthikes gia v2 - metadata
$langCategory="Κατηγορία";
//$langCreator=""; //den xrhsimopoieitai giati o creator einai o diaxeirisths pou kanei upload
$langCreatorEmail="Ηλ. Διεύθυνση Συγγραφέα";
$langDescription="Περιγραφή";
$langFormat="Τυπος-Κατηγορία";
// $langDate=""; //den xrhsimopieitai giati to pairnei aftomata
// $langFormat="";  //den xrhsimopoieitai. antistoixei se mime type
$langSubject="Θέμα";
$langAuthor="Συγγραφέας";
$langLanguage="Γλώσσα";
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
?>
