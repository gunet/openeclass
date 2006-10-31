<?php
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*       Copyright(c) 2003-2006  Greek Universities Network - GUnet
*       Α full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:     Dimitris Tsachalis <ditsa@ccf.auth.gr>
*
*       For a full list of contributors, see "credits.txt".
*
*       This program is a free software under the terms of the GNU
*       (General Public License) as published by the Free Software
*       Foundation. See the GNU License for more details.
*       The full license can be read in "license.txt".
*
*       Contact address:        GUnet Asynchronous Teleteaching Group,
*                                               Network Operations Center, University of Athens,
*                                               Panepistimiopolis Ilissia, 15784, Athens, Greece
*                                               eMail: eclassadmin@gunet.gr
============================================================================*/
/**
 * conference
 * 
 * @author Dimitris Tsachalis <ditsa@ccf.auth.gr>
 * @version $Id$
 * 
 * @abstract 
 *
 */
 $langConference = "Τηλεσυνεργασία";
 $langWash = "Καθάρισμα";
 $langWashFrom = "Η κουβέντα καθάρισε από";
 $langSave = "Αποθήκευση";
 $langRefresh = "Ανανέωση";
 $langClearedBy = "καθαρισμός από";
 $langChatError = "Δεν είναι δυνατόν να ξεκινήσει η Ζωντανή Τηλεσυνεργασία";
 $langsetvideo="Σύνδεσμος παρουσίασης βίντεο";
 $langButtonVideo="Μετάδοση";
 $langButtonPresantation="Μετάδοση";
 $langconference="Ενεργοποίηση τηλεδιάσκεψης";
 $langpresantation="Σύνδεσμος παρουσίασης ιστοσελίδας";
 $langVideo_content="<p align='justify'>Εδώ θα παρουσιαστεί το βίντεο αφού την ενεργοποιήσει ο καθηγητής.</p>";
 $langTeleconference_content="<p align='justify'>Εδώ θα παρουσιαστεί η τηλεδιάσκεψη αφού την ενεργοποιήσει ο καθηγητής.</p>";
 $browser = get_browser(null, true);
 if($browser['browser']!="IE")
 	$langTeleconference_content.="<p  align='justify'>Η τηλεδιάσκεψη ενεργοποιείται μόνο αν έχετε IE ως πλοηγό.</p>";

 $langWashVideo="Παύση μετάδοσης";
 $langPresantation_content="<p align='center'>Εδώ θα παρουσιαστεί μία ιστοσελίδα που θα επιλέξει ο καθηγητής.</p>";
 $langWashPresanation="Παύση μετάδοσης";
?>
