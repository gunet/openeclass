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

class sysinfo
{
    // get our apache SERVER_NAME or vhost
    function vhostname ()
    {
        if (! ($result = getenv('SERVER_NAME'))) {
            $result = 'N.A.';
        }
        return $result;
    }

    // get our canonical hostname
    function chostname ()
    {
        if ($fp = fopen('/proc/sys/kernel/hostname','r')) {
            $result = trim(fgets($fp, 4096));
            fclose($fp);
            $result = gethostbyaddr(gethostbyname($result));
        } else {
            $result = 'N.A.';
        }
        return $result;
    }

    // get the IP address of our canonical hostname
    function ip_addr ()
    {
        if (!($result = getenv('SERVER_ADDR'))) {
            $result = gethostbyname($this->chostname());
        }
        return $result;
    }

    function kernel ()
    {
        if ($fd = fopen('/proc/version', 'r'))
        {
            $buf = fgets($fd, 4096);
            fclose($fd);

            if (preg_match('/version (.*?) /', $buf, $ar_buf)) {
                $result = $ar_buf[1];

                if (preg_match('/SMP/', $buf)) {
                    $result .= ' (SMP)';
                }
            } else {
                $result = 'N.A.';
            }
        } else {
            $result = 'N.A.';
        }
        return $result;
    }

    function uptime ()
    {
        global $text;
        $fd = fopen('/proc/uptime', 'r');
        $ar_buf = split(' ', fgets($fd, 4096));
        fclose($fd);

        $sys_ticks = trim($ar_buf[0]);

        $min   = $sys_ticks / 60;
        $hours = $min / 60;
        $days  = floor($hours / 24);
        $hours = floor($hours - ($days * 24));
        $min   = floor($min - ($days * 60 * 24) - ($hours * 60));

        if ($days != 0) {
            $result = "$days ".$text['days']." ";
        }

        if ($hours != 0) {
            $result .= "$hours ".$text['hours']." ";
        }
        $result .= "$min ".$text['minutes'];

        return $result;
    }

    function users ()
    {
        $who = split('=', execute_program('who', '-q'));
        $result = $who[1];
        return $result;
    }

    function loadavg ()
    {
        if ($fd = fopen('/proc/loadavg', 'r')) {
            $results = split(' ', fgets($fd, 4096));
            fclose($fd);
        } else {
            $results = array('N.A.','N.A.','N.A.');
        } 
        return $results;
    }

    function cpu_info ()
    {
        $results = array();
        $ar_buf = array();

        if ($fd = fopen('/proc/cpuinfo', 'r')) {
            while ($buf = fgets($fd, 4096)) {
                list($key, $value) = preg_split('/\s+:\s+/', trim($buf), 2);

                // All of the tags here are highly architecture dependant.
                // the only way I could reconstruct them for machines I don't
                // have is to browse the kernel source.  So if your arch isn't
                // supported, tell me you want it written in. <infinite@sigkill.com>
                switch ($key) {
                    case 'model name':
                        $results['model'] = $value;
                        break;
                    case 'cpu MHz':
                        $results['mhz'] = sprintf('%.2f', $value);
                        break;
                    case 'clock': // For PPC arch (damn borked POS)
                        $results['mhz'] = sprintf('%.2f', $value);
                        break;
                    case 'cpu': // For PPC arch (damn borked POS)
                        $results['model'] = $value;
                        break;
                    case 'revision': // For PPC arch (damn borked POS)
                        $results['model'] .= ' ( rev: ' . $value . ')';
                        break;
                    case 'cache size':
                        $results['cache'] = $value;
                        break;
                    case 'bogomips':
                        $results['bogomips'] += $value;
                        break;
                    case 'processor':
                        $results['cpus'] += 1;
                        break;
                }
            }
            fclose($fd);
        }

        $keys = compat_array_keys($results);
        $keys2be = array('model', 'mhz', 'cache', 'bogomips', 'cpus');

        while ($ar_buf = each($keys2be)) {
            if (! compat_in_array($ar_buf[1], $keys)) {
                $results[$ar_buf[1]] = 'N.A.';
            }
        }
        return $results;

    }

    function pci ()
    {
        $results = array();

        if ($fd = fopen('/proc/pci', 'r')) {
            while ($buf = fgets($fd, 4096)) {
                if (preg_match('/Bus/', $buf)) {
                    $device = 1;
                    continue;
                }

                if ($device) {
                    list($key, $value) = split(': ', $buf, 2);

                    if (!preg_match('/bridge/i', $key) && !preg_match('/USB/i', $key)) {
                        $results[] = preg_replace('/\([^\)]+\)\.$/', '', trim($value));
                    }
                    $device = 0;
                }
            }
        }
        return $results;
    }

    function ide ()
    {
        $results = array();

        $handle = opendir('/proc/ide');

        while ($file = readdir($handle)) {
            if (preg_match('/^hd/', $file)) {
                $results[$file] = array();

                // Check if device is CD-ROM (CD-ROM capacity shows as 1024 GB)
                if ($fd = fopen("/proc/ide/$file/media", 'r')) {
                    $results[$file]['media'] = trim(fgets($fd, 4096));
                    if ($results[$file]['media'] == 'disk') {
                        $results[$file]['media'] = 'Hard Disk';
                    }

                    if ($results[$file]['media'] == 'cdrom') {
                        $results[$file]['media'] = 'CD-ROM';
                    }
                    fclose($fd);
                }

                if ($fd = fopen("/proc/ide/$file/model", 'r')) {
                    $results[$file]['model'] = trim(fgets($fd, 4096));
                    if (preg_match('/WDC/', $results[$file]['model'])) {
                        $results[$file]['manufacture'] = 'Western Digital';

                    } elseif (preg_match('/IBM/', $results[$file]['model'])) {
                        $results[$file]['manufacture'] = 'IBM';

                    } elseif (preg_match('/FUJITSU/', $results[$file]['model'])) {
                        $results[$file]['manufacture'] = 'Fujitsu';

                    } else {
                        $results[$file]['manufacture'] = 'Unknown';
                    }

                    fclose($fd);
                }

                if ($fd = fopen("/proc/ide/$file/capacity", 'r')) {
                    $results[$file]['capacity'] = trim(fgets($fd, 4096));
                    if ($results[$file]['media'] == 'CD-ROM') {
                        unset($results[$file]['capacity']);
                    }
                    fclose($fd);
                }
            }
        }
        closedir($handle); 

        return $results;
    }

    function scsi ()
    {
        $results    = array();
        $dev_vendor = '';
        $dev_model  = '';
        $dev_rev    = '';
        $dev_type   = '';

        if ($fd = fopen('/proc/scsi/scsi', 'r')) {
            while ($buf = fgets($fd, 4096)) {
                if (preg_match('/Vendor/', $buf)) {
                    preg_match('/Vendor: (.*) Model: (.*) Rev: (.*)/i', $buf, $dev);
                    list($key, $value) = split(': ', $buf, 2);
                    $dev_str  = $value;
                    $get_type = 1;
                    continue;
                }

                if ($get_type) {
                    preg_match('/Type:\s+(\S+)/i', $buf, $dev_type);
                    $results[] = "$dev[1] $dev[2] ( $dev_type[1] )";
                    $get_type = 0;
                }
            }
        } 
        return $results;
    }

    function network ()
    {
        $results = array();

        if ($fd = fopen('/proc/net/dev', 'r')) {
            while ($buf = fgets($fd, 4096)) {
                if (preg_match('/:/', $buf)) {
                    list($dev_name, $stats_list) = preg_split('/:/', $buf, 2);
                    $stats = preg_split('/\s+/', trim($stats_list));
                    $results[$dev_name] = array();

                    $results[$dev_name]['rx_bytes']   = $stats[0];
                    $results[$dev_name]['rx_packets'] = $stats[1];
                    $results[$dev_name]['rx_errs']    = $stats[2];
                    $results[$dev_name]['rx_drop']    = $stats[3];

                    $results[$dev_name]['tx_bytes']   = $stats[8];
                    $results[$dev_name]['tx_packets'] = $stats[9];
                    $results[$dev_name]['tx_errs']    = $stats[10];
                    $results[$dev_name]['tx_drop']    = $stats[11];

                    $results[$dev_name]['errs']       = $stats[2] + $stats[10];
                    $results[$dev_name]['drop']       = $stats[3] + $stats[11];
                }
            }
        }
        return $results;
    }

    function memory ()
    {
        if ($fd = fopen('/proc/meminfo', 'r')) {
            while ($buf = fgets($fd, 4096)) {
                if (preg_match('/Mem:\s+(.*)$/', $buf, $ar_buf)) {
                    $ar_buf = preg_split('/\s+/', $ar_buf[1], 6);

                    $results['ram'] = array();

                    $results['ram']['total']   = $ar_buf[0] / 1024;
                    $results['ram']['used']    = $ar_buf[1] / 1024;
                    $results['ram']['free']    = $ar_buf[2] / 1024;
                    $results['ram']['shared']  = $ar_buf[3] / 1024;
                    $results['ram']['buffers'] = $ar_buf[4] / 1024;
                    $results['ram']['cached']  = $ar_buf[5] / 1024;

                    $results['ram']['t_used']  = $results['ram']['used'] - $results['ram']['cached'] - $results['ram']['buffers'];
                    $results['ram']['t_free']  = $results['ram']['total'] - $results['ram']['t_used'];
                    $results['ram']['percent'] = round(($results['ram']['t_used'] * 100) / $results['ram']['total']);
                }

                if (preg_match('/Swap:\s+(.*)$/', $buf, $ar_buf)) {
                    $ar_buf = preg_split('/\s+/', $ar_buf[1], 3);

                    $results['swap'] = array();

                    $results['swap']['total']   = $ar_buf[0] / 1024;
                    $results['swap']['used']    = $ar_buf[1] / 1024;
                    $results['swap']['free']    = $ar_buf[2] / 1024;
                    $results['swap']['percent'] = round(($ar_buf[1] * 100) / $ar_buf[0]);

                    // Get info on individual swap files
                    $swaps = file ('/proc/swaps');
                    $swapdevs = split("\n", $swaps);

                    for ($i = 1; $i < (sizeof($swapdevs) - 1); $i++) {
                        $ar_buf = preg_split('/\s+/', $swapdevs[$i], 6);

                        $results['devswap'][$i - 1] = array();
                        $results['devswap'][$i - 1]['dev']     = $ar_buf[0];
                        $results['devswap'][$i - 1]['total']   = $ar_buf[2];
                        $results['devswap'][$i - 1]['used']    = $ar_buf[3];
                        $results['devswap'][$i - 1]['free']    = ($results['devswap'][$i - 1]['total'] - $results['devswap'][$i - 1]['used']);
                        $results['devswap'][$i - 1]['percent'] = round(($ar_buf[3] * 100) / $ar_buf[2]);
                    }
                    break;
                }
            }
            fclose($fd);
        } else {
            $results['ram'] = array();
            $results['swap'] = array();
            $results['devswap'] = array();
        }
        return $results;
    }

    function filesystems ()
    {
        $df = execute_program('df', '-kP');
        $mounts = split("\n", $df);
        $fstype = array();

        if ($fd = fopen('/proc/mounts', 'r')) {
            while ($buf = fgets($fd, 4096)) {
                list($dev, $mpoint, $type) = preg_split('/\s+/', trim($buf), 4);
                $fstype[$mpoint] = $type;
                $fsdev[$dev] = $type;
            }
            fclose($fd);
        }

        for ($i = 1; $i < sizeof($mounts); $i++) {
            $ar_buf = preg_split('/\s+/', $mounts[$i], 6);

            $results[$i - 1] = array();

            $results[$i - 1]['disk'] = $ar_buf[0];
            $results[$i - 1]['size'] = $ar_buf[1];
            $results[$i - 1]['used'] = $ar_buf[2];
            $results[$i - 1]['free'] = $ar_buf[3];
            $results[$i - 1]['percent'] = round(($results[$i - 1]['used'] * 100) / $results[$i - 1]['size']) . '%';
            $results[$i - 1]['mount'] = $ar_buf[5];
            ($fstype[$ar_buf[5]]) ? $results[$i - 1]['fstype'] = $fstype[$ar_buf[5]] : $results[$i - 1]['fstype'] = $fsdev[$ar_buf[0]];
        }
        return $results;
    }

    // Return an array of ofr informtaion about
    // about network connections
    function sys_connections ()
    {
        $netstat = execute_program('netstat', '-n');
        $connections = explode("\n", $netstat);
        $return = array();

        reset($connections);
        while (list(, $connection) = each ($connections)) {
            if (stristr($connection, "ESTABLISHED")) {
                $return[$i]['prot']  = trim(substr($connection,0,5));
                $laddr = trim(substr($connection,20,23));
                //$return[$i]['laddr'] = @gethostbyaddr(substr($laddr, 0, strpos($laddr, ":"))); // uncomment for dns lookup
                $return[$i]['laddr'] = substr($laddr, 0, strpos($laddr, ":"));
                $lport = substr(strstr($laddr,":"),1);

                 // NOTE: getservbyport is a PHP 4 >= 4.0b4) function.
                if ($servname = getservbyport ($lport, $return[$i]['prot'])) {
                    $lport .= " ($servname)";
                }

                $return[$i]['lport'] = $lport;
                $faddr = trim(substr($connection,44,23));
                //$return[$i]['faddr'] = @gethostbyaddr(substr($faddr, 0, strpos($faddr, ":"))); // uncomment for dns lookup
                $return[$i]['faddr'] = substr($faddr, 0, strpos($faddr, ":"));
                $fport = substr(strstr($faddr,":"),1);

                // NOTE: getservbyport is a PHP 4 >= 4.0b4 function.
                if ($servname = getservbyport ($fport, $return[$i]['prot'])) {
                    $fport .= " ($servname)";
                }

                $return[$i]['fport'] = $fport;
                $i++;
            }
        }
        return $return;
    }
}       // End of class
