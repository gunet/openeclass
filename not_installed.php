<?PHP
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

/*
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
    <title>Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης Open eClass</title>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
    <link href=\"./install/install.css\" rel=\"stylesheet\" type=\"text/css\" />
    <link href=\"./template/classic/tool_content.css\" rel=\"stylesheet\" type=\"text/css\" />
    <link href=\"./template/classic/perso.css\" rel=\"stylesheet\" type=\"text/css\" />

  </head>
  <body>
  <p>&nbsp;</p>
  <table width=\"65%\" class=\"FormData\" align=\"center\" style=\"border: 1px solid #edecdf;\">
  <thead>
  <tr>
    <td>&nbsp</td>
  </tr>
  <tr>
    <td><div align=\"center\"><img style='border:0px;' src='template/classic/img/caution_alert.gif' title='caution-alert'></div></td>
  </tr>
  <tr>
    <td><div align=\"center\"><h4>Η πλατφόρμα ασύγχρονης τηλεκπαίδευσης Open eClass δεν λειτουργεί !</h4></div></td>
  </tr>
  <tr>
    <td>

    <table width=\"100%\" class=\"FormInput\" align=\"center\">
    <tbody>
    <tr>
      <td width=\"40%\" class=\"odd\"><b>Πιθανοί λόγοι</b></td>
      <td><b>Αντιμετώπιση</b></td>
    </tr>
    <tr>
      <td class=\"odd\" class=\"left\">Υπάρχει πρόβλημα με την <b>MySQL</b>:</td>
      <td>Eπικοινωνήστε με το διαχειριστή του συστήματος.</td>
    </tr>
    <tr>
      <td class=\"odd\" class=\"left\">Πρόβλημα στο αρχείο \"<b>config.php</b>\":</td>
      <td>Το αρχείο δεν υπάρχει ή δεν μπορεί να διαβαστεί.</td>
    </tr>
    <tr>
      <td class=\"odd\" class=\"left\">Xρησιμοποιείτε την πλατφόρμα <br />για <b>πρώτη</b> φορά:</td>
      <td>Επιλέξτε τον <a href=\"./install/\" class=\"installer\"><br /><div align=\"center\">Οδηγό Εγκατάστασης</a></div><br /> για να ξεκινήσετε το πρόγραμμα εγκατάστασης</td>
    </tr>
    </tbody>
    </table>

    </td>
  </tr>
  </thead>
  </table>

  </body>
</html>
";
echo $tool_content;
exit();

?>
