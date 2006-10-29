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
	<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
  <head>
    <title>Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης e-Class</title>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-7\" />
    <link href=\"./template/classic/tool_content.css\" rel=\"stylesheet\" type=\"text/css\" />
    <link href=\"./install/install.css\" rel=\"stylesheet\" type=\"text/css\" />
      
  </head>
  <body>
	<table width = \"99%\">
				<tbody>
					<tr>
						<td class=\"extraMessage\">
						<b>Η πλατφόρμα ασύγχρονης τηλεκπαίδευσης δεν λειτουργεί !</b>
        <p>Πιθανοί λόγοι:</p>
        <ul id=\"installBullet\"><li>Υπάρχει πρόβλημα με την MySQL (επικοινωνήστε με το διαχειριστή του συστήματος).</li>
        <li>Υπάρχει πρόβλημα στις ρυθμίσεις του αρχείου <b>config.php</b></li>
        
        <li>χρησιμοποιείτε την πλατφόρμα για πρώτη φορά 
        (Σε αυτή την περίπτωση κάντε κλίκ στον <a href=\"./install/\">Οδηγό Εγκατάστασης</a>
        για να ξεκινήσετε το πρόγραμμα εγκατάστασης).</li>
						</ul>
					</td>
					</tr>
				</tbody>
			</table>
			
	</body>
	</html>
   ";
	exit($tool_content);
	
?>