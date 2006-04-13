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

$scale_factor = 2;

$_text = '<table width="100%" align="center">'
       . '<tr><td align="left" valign="top"><font size="-1"><b>' . $text['mount'] . '</b></font></td>'
       . '<td align="left" valign="top"><font size="-1"><b>' . $text['type'] . '</b></font></td>'
       . '<td align="left" valign="top"><font size="-1"><b>' . $text['partition'] . '</b></font></td>'
       . '<td align="left" valign="top"><font size="-1"><b>' . $text['percent'] . '</b></font></td>'
       . '<td align="right" valign="top"><font size="-1"><b>' . $text['free'] . '</b></font></td>'
       . '<td align="right" valign="top"><font size="-1"><b>' . $text['used'] . '</b></font></td>'
       . '<td align="right" valign="top"><font size="-1"><b>' . $text['size'] . '</b></font></td></tr>';

$fs = $sysinfo->filesystems();

for ($i=0; $i<sizeof($fs); $i++) {
    $sum['size'] += $fs[$i]['size'];
    $sum['used'] += $fs[$i]['used'];
    $sum['free'] += $fs[$i]['free']; 

    $_text .= "\t<tr>\n";
    $_text .= "\t\t<td align=\"left\" valign=\"top\"><font size=\"-1\">" . $fs[$i]['mount'] . "</font></td>\n";
    $_text .= "\t\t<td align=\"left\" valign=\"top\"><font size=\"-1\">" . $fs[$i]['fstype'] . "</font></td>\n";
    $_text .= "\t\t<td align=\"left\" valign=\"top\"><font size=\"-1\">" . $fs[$i]['disk'] . "</font></td>\n";
    $_text .= "\t\t<td align=\"left\" valign=\"top\"><font size=\"-1\">";

    $_text .= create_bargraph($fs[$i]['percent'], $fs[$i]['percent'], $scale_factor, $fs[$i]['fstype']);

    $_text .= "&nbsp;" . $fs[$i]['percent'] . "</font></td>\n";
    $_text .= "\t\t<td align=\"right\" valign=\"top\"><font size=\"-1\">" . format_bytesize($fs[$i]['free']) . "</font></td>\n";
    $_text .= "\t\t<td align=\"right\" valign=\"top\"><font size=\"-1\">" . format_bytesize($fs[$i]['used']) . "</font></td>\n";
    $_text .= "\t\t<td align=\"right\" valign=\"top\"><font size=\"-1\">" . format_bytesize($fs[$i]['size']) . "</font></td>\n";
    $_text .= "\t</tr>\n";
}

$_text .= '<tr><td colspan="3" align="right" valign="top"><font size="-1"><i>' . $text['totals'] . ' :&nbsp;&nbsp;</i></font></td>';
$_text .= "\t\t<td align=\"left\" valign=\"top\"><font size=\"-1\">";

$sum_percent = round(($sum['used'] * 100) / $sum['size']);
$_text .= create_bargraph($sum_percent, $sum_percent, $scale_factor);

$_text .= "&nbsp;" . $sum_percent . "%" .  "</font></td>\n";

$_text .= '<td align="right" valign="top"><font size="-1">' . format_bytesize($sum['free']) . '</font></td>'
        . '<td align="right" valign="top"><font size="-1">' . format_bytesize($sum['used']) . '</font></td>'
        . '<td align="right" valign="top"><font size="-1">' . format_bytesize($sum['size']) . '</font></td></tr>'
        . '</table>';

$tpl->set_var('filesystems', makebox($text['fs'], $_text, '100%'));

?>
