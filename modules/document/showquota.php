<?php
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    	Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    	Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

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
$langFiles = 'document';

include '../../include/baseTheme.php';
include 'gaugebar.php';


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
	$tool_content .= "<br>
	<table cellpadding = \"0\" cellspacing = \"0\" border = \"1\">
	<thead>
		<tr>
			<th>$langQuotaUsed</td>
			<th>$langQuotaPercentage</td>
			<th>$langQuotaTotal</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td align=\"center\">$diskUsed</td>
			<td align=\"center\">";
    		$tool_content .= $oGauge->display();
    		$tool_content .= "$diskUsedPercentage
    		</td>
    		<td align=\"center\">$diskQuotaDocument</td>
    	</tr>
    </tbody>
    </table>
    <a href=\"document.php\">$langBack</a>";

$tmp_cwd = getcwd();
draw($tool_content, 2, '', '');


// A helper function, when passed a number representing KB,
// and optionally the number of decimal places required,
// it returns a formated number string, with unit identifier.
function format_bytesize ($kbytes, $dec_places = 2)
{
    global $text;
    if ($kbytes > 1048576) {
        $result  = sprintf('%.' . $dec_places . 'f', $kbytes / 1048576);
        $result .= '&nbsp;Gb';
    } elseif ($kbytes > 1024) {
        $result  = sprintf('%.' . $dec_places . 'f', $kbytes / 1024);
        $result .= '&nbsp;Mb';
    } else {
        $result  = sprintf('%.' . $dec_places . 'f', $kbytes);
        $result .= '&nbsp;Kb';
    }
    return $result;
}


?>    		
