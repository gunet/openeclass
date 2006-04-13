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

require('./includes/os/class.BSD.common.inc.php');

class sysinfo extends bsd_common
{
    var $cpu_regexp;
    var $scsi_regexp;

    // Our contstructor
    // this function is run on the initialization of this class
    function sysinfo ()
    {
        $this->cpu_regexp = "CPU: (.*) \((.*)-MHz (.*)\)";
        $this->scsi_regexp = "^(.*): <(.*)> .*SCSI.*device";
    }

    function get_sys_ticks ()
    {
        $s = explode(' ', $this->grab_key('kern.boottime'));
        $a = ereg_replace('{ ', '', $s[3]);
        $sys_ticks = time() - $a;
        return $sys_ticks;
    }

    function network ()
    {
        $netstat = execute_program('netstat', '-nbdi | cut -c1-24,42- | grep Link');
        $lines = split("\n", $netstat);
        $results = array();
        for ($i = 0; $i < sizeof($lines); $i++) {
            $ar_buf = preg_split("/\s+/", $lines[$i]);
            if (!empty($ar_buf[0]) && !empty($ar_buf[3])) {
                $results[$ar_buf[0]] = array();

                $results[$ar_buf[0]]['rx_bytes'] = $ar_buf[5];
                $results[$ar_buf[0]]['rx_packets'] = $ar_buf[3];
                $results[$ar_buf[0]]['rx_errs'] = $ar_buf[4];
                $results[$ar_buf[0]]['rx_drop'] = $ar_buf[10];

                $results[$ar_buf[0]]['tx_bytes'] = $ar_buf[8];
                $results[$ar_buf[0]]['tx_packets'] = $ar_buf[6];
                $results[$ar_buf[0]]['tx_errs'] = $ar_buf[7];
                $results[$ar_buf[0]]['tx_drop'] = $ar_buf[10];

                $results[$ar_buf[0]]['errs'] = $ar_buf[4] + $ar_buf[7];
                $results[$ar_buf[0]]['drop'] = $ar_buf[10];
            }
        }
        return $results;
    }
}
