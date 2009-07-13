<?
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
 * eClass manuals Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component offers  the platform's manuals.
 *
 */

$path2add=2;
include '../include/baseTheme.php';
$nameTools = $langManuals;

$tool_content = "";
$urlServerTemp = strrev(substr(strrev($urlServer),1));

$ext = "_".langname_to_code($language);

$tool_content .= <<<tCont
<p>$langIntroMan</p>
<ul class="listBullet">

<img src='../images/pdf.gif' border='0' title='$langFormatPDF' align='absmiddle'>&nbsp;&nbsp;<a href="$urlServerTemp/manuals/OpeneClass$ext.pdf" target=_blank class=mainpage>$langFinalDesc</a></img>
<br/><br/>
<img src='../images/pdf.gif' border='0' title=
'$langFormatPDF' align='absmiddle'>&nbsp;&nbsp;<a href="$urlServerTemp/manuals/OpeneClass_short$ext.pdf" target=_blank class=mainpage>$langShortDesc</a></img>
<br/><br/>
<img src='../images/pdf.gif' border='0
' title='$langFormatPDF' align='absmiddle'>&nbsp;&nbsp;<a href="$urlServerTemp/manuals/manT/OpeneClass_ManT$ext.pdf" target=_blank class=mainpage>$langManT</a></img>
<br/><br/>
<img src='../images/pdf.gif' border='0' title=
'$langFormatPDF' align='absmiddle'>&nbsp;&nbsp;<a href="$urlServerTemp/manuals/manS/OpeneClass_ManS$ext.pdf" target=_blank class=mainpage>$langManS</a></img>
<br/>

</ul>

<br/>
<p><b>$langNote: </b><br/>$langAcrobat <img src='../images/acrobat.png' width=15 height=15> $langWhere <a href="http://www.adobe.com/products/acrobat/readstep2.html" target=_blank><span class='explanationtext'>$langHere</span></a>.</p>

tCont;

if (isset($uid) and $uid) {
        draw($tool_content, 1);
} else {
        draw($tool_content, 0);
}

