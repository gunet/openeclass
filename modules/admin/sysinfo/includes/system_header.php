<?php
//
// phpSysInfo - A PHP System Information Script
// http://phpsysinfo.sourceforge.net/
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// $Id$

header("Cache-Control: no-cache, must-revalidate");
if (!isset($charset)) { $charset='iso-8859-1'; }
header('Content-Type: text/html; charset='.$charset);

// timestamp
$timestamp = strftime ("%b %d %Y %H:%M:%S", time());

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
    <title><?php echo $text['title']; echo " -- ($timestamp)"; ?></title>

<?php
if (isset($refresh) && ($refresh = intval($refresh))) {
    echo "\t<meta http-equiv=\"Refresh\" content=\"$refresh\">\n";
}
if (file_exists("templates/$template/$template.css")) {
    echo '<link rel="STYLESHEET" type="text/css" href="templates/';
    echo $template.'/'.$template;
    echo '.css">'."\n";
}
?>

</head>

<?php
if (file_exists("templates/$template/images/$template" . "_background.gif")) {
  echo '<body background="templates/'.$template.'/images/'.$template.'_background.gif">';
}
else
{
  echo '<body>';
}
?>
