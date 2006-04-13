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


// So that stupid warnings do not appear when we stats files that do not exist.
error_reporting(5);


// print out the bar graph
function create_bargraph ($percent, $a, $b, $type = "")
{
    if ($percent == 0) { 
        return '<img height="' . BAR_HEIGHT . '" src="templates/' . TEMPLATE_SET . '/images/bar_left.gif" alt="">'
              . '<img src="templates/' . TEMPLATE_SET . '/images/bar_middle.gif" height="' . BAR_HEIGHT . '" width="1" alt="">'
              . '<img src="templates/' . TEMPLATE_SET . '/images/bar_right.gif" height="' . BAR_HEIGHT . '" alt="">';
    } else if (($percent < 90) || ($type == "iso9660")) {
        return '<img height="' . BAR_HEIGHT . '" src="templates/' . TEMPLATE_SET . '/images/bar_left.gif" alt="">'
              . '<img src="templates/' . TEMPLATE_SET . '/images/bar_middle.gif" height="' . BAR_HEIGHT . '" width="' . ($a * $b) . '" alt="">'
              . '<img height="' . BAR_HEIGHT . '" src="templates/' . TEMPLATE_SET . '/images/bar_right.gif" alt="">';
    } else {
        return '<img height="' . BAR_HEIGHT . '" src="templates/' . TEMPLATE_SET . '/images/redbar_left.gif" alt="">'
             . '<img src="templates/' . TEMPLATE_SET . '/images/redbar_middle.gif" height="' . BAR_HEIGHT . '" width="' . ($a * $b) . '" alt="">'
             . '<img height="' . BAR_HEIGHT . '" src="templates/' . TEMPLATE_SET . '/images/redbar_right.gif" alt="">';
    }
}


// Find a system program.  Do path checking
function find_program ($program)
{
    $path = array('/bin', '/sbin', '/usr/bin', '/usr/sbin', '/usr/local/bin', '/usr/local/sbin');
    while ($this_path = current($path)) {
        if (is_executable("$this_path/$program")) {
        return "$this_path/$program";
    }
    next($path);
    }
    return;
}


// Execute a system program. return a trim()'d result.
// does very crude pipe checking.  you need ' | ' for it to work
// ie $program = execute_program('netstat', '-anp | grep LIST');
// NOT $program = execute_program('netstat', '-anp|grep LIST');
function execute_program ($program, $args = '')
{
    $buffer = '';
    $program = find_program($program);

    if (!$program) { return; }

    // see if we've gotten a |, if we have we need to do patch checking on the cmd
    if ($args) {
        $args_list = split(' ', $args);
        for ($i = 0; $i < count($args_list); $i++) {
            if ($args_list[$i] == '|') {
                $cmd = $args_list[$i+1];
                $new_cmd = find_program($cmd);
                $args = ereg_replace("\| $cmd", "| $new_cmd", $args);
            }
        }
    }

    //print "program: $program $args<br>";

    // we've finally got a good cmd line.. execute it
    if ($fp = popen("$program $args", 'r')) {
        while (!feof($fp)) {
            $buffer .= fgets($fp, 4096);
        }
        return trim($buffer);
    }
}


// function that emulate the compat_array_keys function of PHP4
// for PHP3 compatability
function compat_array_keys ($arr)
{
        $result = array();

        while (list($key, $val) = each($arr)) {
            $result[] = $key;
        }
        return $result;
}


// function that emulates the compat_in_array function of PHP4
// for PHP3 compatability
function compat_in_array ($value, $arr)
{
    while (list($key,$val) = each($arr)) {
        if ($value == $val) {
            return true;
        }
    }
    return false;
}


// A helper function, when passed a number representing KB,
// and optionally the number of decimal places required,
// it returns a formated number string, with unit identifier.
function format_bytesize ($kbytes, $dec_places = 2)
{
    global $text;
    if ($kbytes > 1048576) {
        $result  = sprintf('%.' . $dec_places . 'f', $kbytes / 1048576);
        $result .= '&nbsp;'.$text['gb'];
    } elseif ($kbytes > 1024) {
        $result  = sprintf('%.' . $dec_places . 'f', $kbytes / 1024);
        $result .= '&nbsp;'.$text['mb'];
    } else {
        $result  = sprintf('%.' . $dec_places . 'f', $kbytes);
        $result .= '&nbsp;'.$text['kb'];
    }
    return $result;
}

?>
