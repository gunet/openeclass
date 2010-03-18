<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
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


/*===========================================================================
	showquota.php
	@last update: 18-07-2006 by Sakis Agorastos
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================
    @Description: A page that shows a table with statistic data and a
    gauge bar. The statistical data are transfered here with GET in
    $diskQuotaDocument and $diskUsed

    This scipt uses the 'gaugebar.php' class for the graphic gauge bar
==============================================================================*/

$require_current_course = TRUE;

include '../../include/baseTheme.php';
include 'gaugebar.php';

$navigation[]= array ("url"=>"document.php", "name"=> $langDoc);
$tool_content = "";
$nameTools = $langQuotaBar;

//diamorfwsh ths grafikhs mparas xrhsimopoioumenou kai eleftherou xwrou (me vash ta quotas) + ypologismos statistikwn stoixeiwn
   $oGauge = new myGauge(); //vrisketai sto arxeio 'gaugebar.php' & ginetai include parapanw
    // apodosh timwn gia thn mpara
	$fc = "#E6E6E6"; //foreground color
	$bc = "#4F76A3"; //background color
	$wi = 125; //width pixel
	$hi = 10; //width pixel
	$mi = 0;  //minimum value
	$ma = $diskQuotaDocument; //maximum value
	$cu = $diskUsed; //current value
	$oGauge->setValues($fc, $bc, $wi, $hi, $mi, $ma, $cu);
	//pososto xrhsimopoioumenou xorou se %
	$diskUsedPercentage = round(($diskUsed / $diskQuotaDocument) * 100)."%";
	//morfopoihsh tou synolikou diathesimou megethous tou quota
	$diskQuotaDocument = format_bytesize($diskQuotaDocument / 1024);
	//morfopoihsh tou synolikou megethous pou xrhsimopoieitai
	$diskUsed = format_bytesize($diskUsed / 1024);
	format_bytesize($diskUsed, '0');
	//telos diamorfwshs ths grafikh mparas kai twn arithmitikwn statistikwn stoixeiwn
	//ektypwsh pinaka me arithmitika stoixeia + thn grafikh bara
	$tool_content .= "
    <table class=\"FormData\">
    <tbody>
    <tr>
      	<th class='left' width='220'>$langQuotaUsed :</td>
      	<td>$diskUsed</td>
    </tr>
    <tr>
        <th class='left'>$langQuotaPercentage :</td>
        <td align='center'>";
	$tool_content .= $oGauge->display();
    	$tool_content .= "$diskUsedPercentage</td>
    </tr>
    <tr>
     	<th class='left'>$langQuotaTotal :</td>
      	<td align='center'>$diskQuotaDocument</td>
    </tr>
    </tbody>
    </table>";
$tmp_cwd = getcwd();
draw($tool_content, 2, 'documents', '');
?>
