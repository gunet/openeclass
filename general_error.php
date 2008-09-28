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
 * General Error Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract Outputs a message to the user's browser to inform him/her that an error occured
 *
 */
$tool_content =  "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
  <head>
    <title>Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης Open eClass</title>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
    <link href=\"./template/classic/tool_content.css\" rel=\"stylesheet\" type=\"text/css\" />
    <link href=\"./template/classic/perso.css\" rel=\"stylesheet\" type=\"text/css\" />
    <link href=\"./template/classic/theme.css\" rel=\"stylesheet\" type=\"text/css\" />
    <link href=\"./install/install.css\" rel=\"stylesheet\" type=\"text/css\" />

  </head>
  <body>

  <div class=\"outer\">

  <table width=\"99%\">
  <tbody>
  <tr>
    <td><img style='border:0px;' src='${urlServer}/template/classic/img/caution_alert.gif' title='caution-alert'></td>
    <td class=\"extraMessage\">
        <h1>Η πλατφόρμα ασύγχρονης τηλεκπαίδευσης δεν λειτουργεί!</h1>
        <p>Πιθανοί λόγοι:</p>
        <ul id=\"installBullet\">
          <li>Υπάρχει πρόβλημα με την MySQL (επικοινωνήστε με το διαχειριστή του συστήματος).</li>
          <li>Υπάρχει πρόβλημα στις ρυθμίσεις του αρχείου <b>config.php</b></li>
          <li>Xρησιμοποιείτε την πλατφόρμα για πρώτη φορά
              (Σε αυτή την περίπτωση κάντε κλίκ στον <a href=\"./install/\">Οδηγό Εγκατάστασης</a>
              για να ξεκινήσετε το πρόγραμμα εγκατάστασης).</li>
        </ul>
    </td>
  </tr>
  </tbody>
  </table>

  </div>
  </body>
</html>";
exit($tool_content);
?>
