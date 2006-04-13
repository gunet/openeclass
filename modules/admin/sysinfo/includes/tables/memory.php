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
$mem = $sysinfo->memory();

$ram .= create_bargraph($mem['ram']['percent'], $mem['ram']['percent'], $scale_factor);
$ram .= '&nbsp;&nbsp;' . $mem['ram']['percent'] . '% ';

$swap .= create_bargraph($mem['swap']['percent'], $mem['swap']['percent'], $scale_factor);

$swap .= '&nbsp;&nbsp;' . $mem['swap']['percent'] . '% ';


$_text = '<table width="100%" align="center">'
       . '<tr><td align="left" valign="top"><font size="-1"><b>' . $text['type'] . '</b></font></td>'
       . '<td align="left" valign="top"><font size="-1"><b>' . $text['percent'] . '</b></font></td>'
       . '<td align="right" valign="top"><font size="-1"><b>' . $text['free'] . '</b></font></td>'
       . '<td align="right" valign="top"><font size="-1"><b>' . $text['used'] . '</b></font></td>'
       . '<td align="right" valign="top"><font size="-1"><b>' . $text['size'] . '</b></font></td></tr>'

       . '<tr><td align="left" valign="top"><font size="-1">' . $text['phymem'] . '</font></td>'
       . '<td align="left" valign="top"><font size="-1">' . $ram . '</font></td>'
       . '<td align="right" valign="top"><font size="-1">' . format_bytesize($mem['ram']['t_free']) . '</font></td>'
       . '<td align="right" valign="top"><font size="-1">' . format_bytesize($mem['ram']['t_used']) . '</font></td>'
       . '<td align="right" valign="top"><font size="-1">' . format_bytesize($mem['ram']['total']) . '</font></td>'

       . '<tr><td align="left" valign="top"><font size="-1">' . $text['swap'] . '</font></td>'
       . '<td align="left" valign="top"><font size="-1">' . $swap . '</font></td>'
       . '<td align="right" valign="top"><font size="-1">' . format_bytesize($mem['swap']['free']) . '</font></td>'
       . '<td align="right" valign="top"><font size="-1">' . format_bytesize($mem['swap']['used']) . '</font></td>'
       . '<td align="right" valign="top"><font size="-1">' . format_bytesize($mem['swap']['total']) . '</font></td>'

       . '</table>';

$tpl->set_var('memory', makebox($text['memusage'], $_text, '100%'));

?>
