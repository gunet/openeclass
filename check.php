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
	background-image: url(template/default/img/check/ie.png);
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
	background-image: url(template/default/img/check/firefox.png);
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
	background-image: url(template/default/img/check/safari.png);
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
	background-image: url(template/default/img/check/chrome.png);
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
	background-image: url(template/default/img/check/opera.png);
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
	background-image: url(template/default/img/check/FlashPlayer.png);
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
	background-image: url(template/default/img/check/Shockwave.png);
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
	background-image: url(template/default/img/check/acrobat_reader.png);
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

$data['menuTypeID'] = 0;
view ('check', $data);

