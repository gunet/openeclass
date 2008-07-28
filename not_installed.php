<?PHP
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
 * Not Installed Component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract Outputs a message to the user's browser to inform him/her that eclass
 * is not installed.
 *
 */
$tool_content = "
	
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
  <head>
    <title>Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης eClass</title>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
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
        <ul id=\"installBullet\">
        <li>Υπάρχει πρόβλημα με την MySQL (επικοινωνήστε με το διαχειριστή του συστήματος).</li>
        <li>Το αρχείο <b>config.php</b> δεν υπάρχει ή δεν μπορεί να διαβαστεί.</li>
        <li>Xρησιμοποιείτε την πλατφόρμα για πρώτη φορά 
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
echo $tool_content;
exit();

?>
