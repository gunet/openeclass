<?

if (!isset($colorLight)) $colorLight = "#F5F5F5";
if (!isset($colorMedium)) $colorMedium = "#004571";
if (!isset($colorDark)) $colorDark = "#000066";
if (!isset($bannerPath)) $bannerPath = 'images/gunet/banner.jpg';
include 'config/config.php';

$support_forum_url = 'http://eclass.gunet.gr/teledu/index.htm';

$main_page_banner = '<img src="'.$bannerPath.'" title="GUnet e-Class">';

// The following text appears under the main site page and the user's home page:
$main_page_footer = '
<table width="600" cellpadding="6" cellspacing="2" align="center">
<tr><td valign="top"><font size="1" face="arial, helvetica">
        <hr noshade="noshade" size="1" width="600">
<img align=center src="images/eu.jpg" border=0>&nbsp;
<img align=center src="images/grflag.jpg" border=0>
&nbsp;<img align=center src="images/infosoc.jpg" border=0>
&nbsp;&nbsp;&nbsp;
Διαχείριση : <a href=mailto:'.$emailAdministrator.'>'.$langAsynchronous.'</a> 
&nbsp;&nbsp; &mdash; &nbsp;&nbsp;<a href="info/copyright.php">'.$langCopyright.'</a>
</font></td></tr>
</table>
';

// The text that appears in the main area of the index page:
// The %s is substituded with the number of open courses.
$main_text = '
<font face="Tahoma, arial, helvetica" size="2">
<p align="justify">'.$langInfo.'</p>
</font>
';

// The text that appears at the bottom of the right column:
$extra_text = '
';

?>
