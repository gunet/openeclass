<?
/*
      +----------------------------------------------------------------------+
      | e-Class version 1.2                                                  |
      | based on CLAROLINE version 1.3.0 $Revision$                   |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      | Copyright (c) 2003 GUNet                                             |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | e-Class Authors:    Costas Tsibanis <costas@noc.uoa.gr>              |
      |                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
      |                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
      |                                                                      |
      | Claroline Authors:  Thomas Depraetere <depraetere@ipm.ucl.ac.be>     |
      |                     Hugues Peeters    <peeters@ipm.ucl.ac.be>        |
      |                     Christophe Gesché <gesche@ipm.ucl.ac.be>         |
      |                                                                      |
      +----------------------------------------------------------------------+
*/

$langFiles = 'copyright';

include '../../include/init.php';

$nameTools = $langCopyright;
begin_page();

?>
<tr>
<td>
<table cellpadding="3" cellspacing="0" border="0" width="100%">
<tr valign="top" bgcolor="<?= $color2 ?>">
<td>
<blockquote>
<font size="2" face="arial, helvetica">
<p align=justify><?= $langCopyrightNotice ?></p><br>
</font>
</blockquote>
<br>
</td>
</table>
</td>

</tr>
</table>
</body>
</html>
