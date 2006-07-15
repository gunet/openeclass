<?PHP
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	Α full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

/**
 * General Error Component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract Outputs a message to the user's browser to inform him/her that an error occured
 *
 */
	$tool_content =  "
	<table cellpadding='6' cellspacing='0' border='0' width='650' bgcolor='#E6E6E6'>
        <tr bgcolor='navy'>
        <td valign='top' align='center'>
        <font color='white' face='arial, helvetica'>Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης e-Class</font>
        </td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr bgcolor='#E6E6E6'><td>
        <b>Η πλατφόρμα ασύγχρονης τηλεκπαίδευσης δεν λειτουργεί !</b>
        <p>Πιθανοί λόγοι:
        <ul><li>Υπάρχει πρόβλημα με την MySQL (επικοινωνήστε με το διαχειριστή του συστήματος).</li>
        <li>Υπάρχει πρόβλημα στις ρυθμίσεις του αρχείου <tt>config.php</tt></li></ul></p>
        </td>
        </tr>
        <tr bgcolor='#E6E6E6'>
        <td><p>Ένας πιθανός λόγος, επίσης, είναι ότι χρησιμοποιείτε την πλατφόρμα για πρώτη φορά.</p>
        Σε αυτή την περίπτωση κάντε κλίκ στον <a href=\"./install/\">Οδηγό Εγκατάστασης</a>
        για να ξεκινήσετε το πρόγραμμα εγκατάστασης.
        </td>
        </tr>
	</table>";
	echo $tool_content;
	exit();


?>