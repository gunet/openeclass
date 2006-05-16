<?php
/**
 * Dropbox module for Dokeos/Claroline
 * language file in English language. 
 * To make a version in your own language, you have 2 options:
 * 		- if you want to make use of the multilanguage tool in Claroline (this way you
 * 		can make 2 seperate courses in 2 different languages and Claroline will take
 * 		care of the translations) this file must be placed in the .../claroline/lang/English/
 * 		directory and the copy of this file that contains the translations must be placed in 
 * 		the .../claroline/lang/YourLang/ directory. Be sure to give the translated version the same 
 * 		name as this one.
 * 		- if you're sure you will only need the dropbox module in 1 language, you can just leave this
 * 		file in the current directory (.../claroline/plugin/dropbox/) and translate each variable into
 * 		the correct language.
 * 
 * @version 1.20
 * @copyright 2004
 * @author Jan Bols <jan@ivpv.UGent.be>
 * with contributions by Renι Haentjens <rene.haentjens@UGent.be> (see RH)
 */
/**
 * +----------------------------------------------------------------------+
 * |   This program is free software; you can redistribute it and/or      |
 * |   modify it under the terms of the GNU General Public License        |
 * |   as published by the Free Software Foundation; either version 2     |
 * |   of the License, or (at your option) any later version.             |
 * |                                                                      |
 * |   This program is distributed in the hope that it will be useful,    |
 * |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
 * |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
 * |   GNU General Public License for more details.                       |
 * |                                                                      |
 * |   You should have received a copy of the GNU General Public License  |
 * |   along with this program; if not, write to the Free Software        |
 * |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
 * |   02111-1307, USA. The GNU GPL license is also available through     |
 * |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
 * +----------------------------------------------------------------------+
 * |   Authors: Jan Bols          <jan@ivpv.UGent.be>              	      |
 * +----------------------------------------------------------------------+
 */

/*
* General variables
*/
$dropbox_lang["dropbox"] = 'Χώρος Ανταλλαγής Αρχείων';
$dropbox_lang["help"] = 'Βοήθεια';

/**
 * error variables
 */
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
/**
 * upload file variables
 */
$dropbox_lang['uploadFile'] = "Ανέβασμα αρχείου";
$dropbox_lang['authors'] = "Αποστολέας";
$dropbox_lang['description'] = "Περιγραφή αρχείου";
$dropbox_lang['sendTo'] = "Αποστολή στον/στην";

/**
 * Sent/Received list variables
 */
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

/**
 * Feedback variables
 */
$dropbox_lang['docAdd'] = "Το αρχείο στάλθηκε με επιτυχία";
$dropbox_lang['fileDeleted'] = "Το επιλεγμένο αρχείο έχει διαγραφεί από το Χώρο Ανταλλαγής Αρχείων.";
$dropbox_lang['backList'] = "Επιστροφή στο Χώρο Ανταλλαγής Αρχείων";

/**
 * RH: Mailing variables
 */
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

/**
 * RH: Just Upload
 */
$dropbox_lang['justUploadInSelect'] = "--- Ανέβασμα αρχείου ---";
$dropbox_lang['justUploadInList'] = "Ανέβασμα αρχείου από τον/την";
$dropbox_lang['mailingJustUploadNoOther'] = "Το ανέβασμα αρχείου δεν μπορεί να συνδιαστεί με αποστολή σε άλλους παραλήπτες";
?>
