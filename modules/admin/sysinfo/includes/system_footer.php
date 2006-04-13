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

echo "<center>";

$update_form = "<form method=\"POST\" action=\"$PHP_SELF\">\n"
             . "\t" . $text['template'] . ":&nbsp;\n"
             . "\t<select name=\"template\">\n";

$dir = opendir('templates/');
while (($file = readdir($dir))!=false) {
    if ($file != 'CVS' && $file != '.' && $file != '..') {
        if ($template == $file) {
            $update_form .= "\t\t<option value=\"$file\" SELECTED>$file</option>\n";
        } else {
            $update_form .= "\t\t<option value=\"$file\">$file</option>\n";
        }
    }
}
closedir($dir);
$update_form .= "\t</select>\n";


$update_form .= "\t&nbsp;" . $text['language'] . ":&nbsp;\n"
             . "\t<select name=\"lng\">\n";

$dir = opendir('includes/lang/');
while (($file = readdir($dir))!=false) {
    if ($file != 'CVS' && $file != '.' && $file != '..') {
        $file = ereg_replace('.php', '', $file);
        if ($lng == $file) {
            $update_form .= "\t\t<option value=\"$file\" SELECTED>$file</option>\n";
        } else {
            $update_form .= "\t\t<option value=\"$file\">$file</option>\n";
        }
    }
}
closedir($dir);


$update_form .= "\t</select>\n"
              . "\t<input type=\"submit\" value=\"" . $text['submit']."\">\n"
              . "</form>\n";

print $update_form;
?>

</center>

<hr>
<?php echo $text['created']; ?> <a href="http://phpsysinfo.sourceforge.net">phpSysInfo 2.0</a>
</body>
</html>
