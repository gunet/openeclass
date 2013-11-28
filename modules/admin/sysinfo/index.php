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
//
// reassign HTTP variables (incase register_globals is off)
if (!empty($_GET))
    while (list($name, $value) = each($_GET))
        $$name = $value;
if (!empty($_POST))
    while (list($name, $value) = each($_POST))
        $$name = $value;

// Check to see if where running inside of phpGroupWare
if (isset($sessionid) && $sessionid && $kp3 && $domain) {
    define('PHPGROUPWARE', 1);
    $phpgw_info['flags'] = array(
        'currentapp' => 'phpsysinfo-dev'
    );
    include('../init.inc.php');
} else {
    define('PHPGROUPWARE', 0);
}

define('APP_ROOT', dirname(__FILE__));

// default to english, but this is negotiable.
if (!(isset($lng) && file_exists('./includes/lang/' . $lng . '.php'))) {
    // see if the browser knows the right languange.
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $plng = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        if (count($plng) > 0) {
            while (list($k, $v) = each($plng)) {
                $k = explode(';', $v, 1);
                $k = explode('-', $k[0]);
                if (file_exists('./includes/lang/' . $k[0] . '.php')) {
                    $lng = $k[0];
                    break;
                }
                $lng = 'en';
            }
        } else {
            $lng = 'en';
        }
    } else {
        $lng = 'en';
    }
}

// figure out if we got a template passed in the url
if (!(isset($template) && file_exists("templates/$template"))) {
    // default template we should use if we don't get a argument.
    define('TEMPLATE_SET', 'classic');
    $template = 'classic';
} else {
    define('TEMPLATE_SET', $template);
}

// If they have GD complied into PHP, find out the height of the image to make this cleaner
if (function_exists('getimagesize')) {
    $image_prop = getimagesize(APP_ROOT . '/templates/' . TEMPLATE_SET . '/images/bar_middle.gif');
    define('BAR_HEIGHT', $image_prop[1]);
    unset($image_prop);
} else {
    // Until they complie GD into PHP, this could look ugly
    define('BAR_HEIGHT', 16);
}

require('./includes/lang/' . $lng . '.php');   // get our language include
if (PHPGROUPWARE != 1) {
    require('./includes/class.Template.inc.php');  // template library
}
require('./includes/common_functions.php');    // Set of common functions used through out the app
// Figure out which OS where running on, and detect support
if (file_exists(dirname(__FILE__) . '/includes/os/class.' . PHP_OS . '.inc.php')) {
    require('./includes/os/class.' . PHP_OS . '.inc.php');
    $sysinfo = new sysinfo;
} else {
    echo '<center><b>Error: ' . PHP_OS . ' is not currently supported</b></center>';
    exit;
}


// fire up the template engine
$tpl = new Template(dirname(__FILE__) . '/templates/' . TEMPLATE_SET);
$tpl->set_file(array(
    'form' => 'form.tpl'
));

// print out a box of information
function makebox($title, $content) {
    $t = new Template(dirname(__FILE__) . '/templates/' . TEMPLATE_SET);

    $t->set_file(array(
        'box' => 'box.tpl'
    ));

    $t->set_var('title', $title);
    $t->set_var('content', $content);

    return $t->parse('out', 'box');
}

// let the page begin.
require('./includes/system_header.php');

$tpl->set_var('title', $text['title'] . ': ' . $sysinfo->chostname() . ' (' . $sysinfo->ip_addr() . ')');

require('./includes/tables/vitals.php');
require('./includes/tables/network.php');
require('./includes/tables/hardware.php');
require('./includes/tables/memory.php');
require('./includes/tables/filesystems.php');

// parse our the template
$tpl->pparse('out', 'form');

// finally our print our footer
if (PHPGROUPWARE == 1) {
    $phpgw->common->phpgw_footer();
} else {
    require('./includes/system_footer.php');
}
?>
