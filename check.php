<?php

session_start();
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


define('HIDE_TOOL_TITLE', 1);

require_once 'include/baseTheme.php';

$pageName = $langCheckTools;

load_js('check.js');
$head_content .= <<<hContent
<style type="text/css">

ul.menu {
	list-style-type: none;
	padding: 0;
	margin-top: 10px;
	margin-left: 10px;
	text-align: left;
	font-size: 9pt;
}

li.mie {
	height: 20px;
	background-image: url(template/classic/img/check/ie.png);
	background-repeat: no-repeat;
	background-position: 0 .1em;
	padding: 0px 0px 0px 20px;
	margin: 1em 0;
	margin-left: 0px;
	margin-bottom: 4px;
	margin-top: 0px;
}

li.mfir {
	height: 20px;
	background-image: url(template/classic/img/check/firefox.png);
	background-repeat: no-repeat;
	background-position: 0 .1em;
	padding: 0px 0px 0px 20px;
	margin: 1em 0;
	margin-left: 0px;
	margin-bottom: 4px;
	margin-top: 0px;
}

li.msaf {
	height: 20px;
	background-image: url(template/classic/img/check/safari.png);
	background-repeat: no-repeat;
	background-position: 0 .06em;
	padding: 0px 0px 0px 20px;
	margin: 1em 0;
	margin-left: 0px;
	margin-bottom: 4px;
	margin-top: 0px;
}

li.mchr {
	height: 20px;
	background-image: url(template/classic/img/check/chrome.png);
	background-repeat: no-repeat;
	background-position: 0 .05em;
	padding: 0px 0px 0px 20px;
	margin: 1em 0;
	margin-left: 0px;
	margin-bottom: 4px;
	margin-top: 0px;
}

li.moper {
	height: 20px;
	background-image: url(template/classic/img/check/opera.png);
	background-repeat: no-repeat;
	background-position: 0 .09em;
	padding: 0px 0px 0px 20px;
	margin: 1em 0;
	margin-left: 0px;
	margin-bottom: 8px;
	margin-top: 0px;
}

.divmenu {
	margin-left: 0px;
}

ul.fmenu {
	list-style-type: none;
	padding: 0;
	margin: 0;
	text-align: left;
	font-size: 9pt;
	margin-top: 5px;
}

li.mflash {
	height: 20px;
	background-image: url(template/classic/img/check/FlashPlayer.png);
	background-repeat: no-repeat;
	background-position: 0 .1em;
	padding: 0px 0px 0px 20px;
	margin: 1em 0;
	margin-left: 10px;
	margin-bottom: 8px;
	margin-top: 5px;
	height: 20px;
}

ul.smenu {
	list-style-type: none;
	padding: 0;
	margin: 0;
	text-align: left;
	font-size: 9pt;
	margin-top: 5px;
}

li.sflash {
	height: 20px;
	background-image: url(template/classic/img/check/Shockwave.png);
	background-repeat: no-repeat;
	background-position: 0 .1em;
	padding: 0px 0px 0px 20px;
	margin: 1em 0;
	margin-left: 10px;
	margin-bottom: 8px;
	margin-top: 5px;
	height: 20px;
}

div.divpdfmenu {
	margin-top: 5px;
}

ul.pdfmenu {
	list-style-type: none;
	padding: 0;
	margin: 0;
	text-align: left;
	font-size: 9pt;
}

li.mpdf {
	height: 20px;
	background-image: url(template/classic/img/check/acrobat_reader.png);
	background-repeat: no-repeat;
	background-position: 0 .1em;
	padding: 0px 0px 0px 20px;
	margin: 1em 0;
	margin-left: 10px;
	margin-bottom: 8px;
	margin-top: 0px;
}

#divmenu {
	margin-top: 5px;
}

#divfmenu {
	margin-top: 5px;
}

</style>

<script type="text/javascript">
/* <![CDATA[ */

	$(document).ready(function() {
		check();
	});
	
/* ]]> */
</script>
hContent;

$tool_content .= <<<tContent
	<div id="checkcontainer">

		<div style="font-size: 10pt; text-align: justify; margin-left: 20px; margin-right: 20px;">
			<p style="font-size: 10pt; font-weight: bold;">$langCheckTools</p><br/>
			<p>$langCheckIntro</p><br/>
			<p>$langCheckIntro2</p>
			<br />
			<table class="table-default">
				<tbody>
					<tr>
						<th width="50%"><strong>$langSoftware</strong></th>
						<th width="50%"><strong>$langCheck</strong></th>
					</tr>
					<tr class="even">
						<td><strong><span style="font-size: 9pt;">$langBrowser:</span></strong>
							<div class="divmenu">
								<ul class="menu">
									<li class="mie">$langIE</li>
									<li class="mfir">$langFirefox</li>
									<li class="msaf">$langSafari</li>
									<li class="mchr">$langChrome</li>
									<li class="moper">$langOpera</li>
								</ul>
							</div></td>
						<td>
							<p id="browsersupported" style="display: none; color: green; font-size: 11px;">
								<img src="template/classic/img/tick_1.png" border="0" alt="browser supported"/> $langBrowserSupported
							</p>
							<p id="browsernotsupported" style="display: none; color: red; font-size: 11px;">
								<img src="template/classic/img/error.png" border="0" alt="browser not supported"/> $langBrowserNotSupported:<br /> <br />
								<a href="http://www.microsoft.com/windows/internet-explorer/worldwide-sites.aspx" target="_blank">Internet Explorer</a><br />
								<a href="http://www.mozilla.org" target="_blank">Mozilla Firefox</a><br />
								<a href="http://www.apple.com/safari/" target="_blank">Safari</a><br />
								<a href="http://www.google.com/chrome/eula.html" target="_blank">Chrome</a>
							</p>
						</td>
					</tr>
					<tr class="odd">
						<td><strong><span style="font-size: 9pt;">PDF Reader:</span></strong>
							<div class="divpdfmenu">
								<ul class="pdfmenu">
									<li class="mpdf">$langAcrobatReader</li>
								</ul>
							</div>
						</td>
						<td>
							<p id="acrobatreaderinstalled" style="display: none; color: green; font-size: 11px;">
								<img src="template/classic/img/tick_1.png" border="0" alt="acrobat reader installed"/> $langAcrobatReaderInstalled
							</p>
							<p id="acrobatreadernotinstalled" style="display: none; color: red; font-size: 11px;">
								<img src="template/classic/img/error.png" border="0" alt="acrobat reader not installed"/> $langAcrobatReaderNotInstalled
								<a href="http://get.adobe.com/reader/" target="_blank">$langHere</a>.
								$langAgreeAndInstall
							</p>
						</td>
					</tr>
					<tr class="even">
						<td><strong><span style="font-size: 9pt;">Video player:</span></strong>
							<div class="divfmenu">
								<ul class="fmenu">
									<li class="mflash">$langFlashPlayer</li>
								</ul>
							</div></td>
						<td>
							<p id="flashplayerinstalled" style="display: none; color: green; font-size: 11px;">
								<img src="template/classic/img/tick_1.png" border="0" alt="flash player installed"/> $langFlashPlayer
							</p>
							<p id="flashplayernotinstalled" style="display: none; color: red; font-size: 11px;">
								<img src="template/classic/img/error.png" border="0" alt="flash player not installed"/> $langFlashPlayerNotInstalled 
								<a href="http://get.adobe.com/flashplayer/" target="_blank">$langHere</a>.
								$langAgreeAndInstall
							</p>
						</td>
					</tr>
					<tr class="odd">
						<td><strong><span style="font-size: 9pt;">Multimedia player:</span></strong>
							<div class="divfmenu">
								<ul class="smenu">
									<li class="sflash">Adobe Shockwave Player</li>
								</ul>
							</div></td>
						<td>
							<p id="shockinstalled" style="display: none; color: green; font-size: 11px;">
								<img src="template/classic/img/tick_1.png" border="0" alt="shockwave installed"/> $langShockInstalled
							</p>
							<p id="shocknotinstalled" style="display: none; color: red; font-size: 11px;">
								<img src="template/classic/img/error.png" border="0" alt="shockwave not installed"/> $langShockNotInstalled
								<a href="http://get.adobe.com/shockwave/" target="_blank">$langHere</a>.
								$langAgreeAndInstall
							</p>
						</td>
					</tr>
				</tbody>
			</table>
			
			<br /> <br />
			
			<div id="notOK" style="text-align: justify;">
				<div style="text-align: justify;">
					<strong>$langCheckNotOk1</strong>
				</div>
				<div style="text-align: justify;">
					<ol>
						<li class="myLi">$langCheckNotOk2</li>
						<li class="myLi">$langCheckNotOk3</li>
						<li class="myLi">$langCheckNotOk4 <a href="check.php">$langHere</a>.
							$langCheckNotOk5
						</li>
					</ol>
				</div>
			</div>
			<p id="OK" style="display: none; text-align: justify;">
				<strong>$langCheckOk</strong>
			</p>
			<br />

		</div>

	</div>
tContent;

draw($tool_content, 0, null, $head_content);

