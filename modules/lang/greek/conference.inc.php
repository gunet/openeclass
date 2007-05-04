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
 $langSaveChat="Αποθήκευση κουβέντας";
 $langSaveMessage="Η κουβέντα αποθηκεύτηκε στα Έγγραφα.";
 $langSaveErrorMessage="Η κουβέντα δεν μπόρεσε να αποθηκευτή";
?>
