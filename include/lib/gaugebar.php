<?php

/* ========================================================================
 * Open eClass 2.4
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

/* ===========================================================================
  gaugebar.php
  @authors list: Agorastos Sakis <th_agorastos@hotmail.com>
  ==============================================================================
  @Description: The script contains a class which function returns HTML code
  of a graphic gauge bar with custom values.

  Here is an example for using this class:
  Main

  $oGauge = new myGauge();
  ============================================================================== */

class myGauge {

    // Default color
    public $BgColor = null, $FgColor = null;
    // CSS classes for background and foreground
    public $BgClass = 'gaugebar_bg', $FgClass = 'gaugebar_fg';
    // Default dimensions
    public $Width = 125, $Height = 10;
    // Default values
    public $MinVal = 0, $MaxVal = 100, $CurVal = 77;

    // Render this into HTML as a table.
    function display() {
        global $themeimg;

        // Normalize the properties.
        if ($this->MinVal > $this->MaxVal) {
            $temp_val = $this->MinVal;
            $this->MinVal = $this->MaxVal;
            $this->MaxVal = $temp_val;
        }

        if ($this->CurVal < $this->MinVal) {
            $this->CurVal = $this->MinVal;
        } elseif ($this->CurVal > $this->MaxVal) {
            $this->CurVal = $this->MaxVal;
        }

        // Figure out the percentage that the CurVal is within MinVal and MaxVal.
        $percentage_val = ($this->CurVal - $this->MinVal) / ($this->MaxVal - $this->MinVal);

        // Compute the first and second widths.
        $fg_width = Round($this->Width * $percentage_val);
        $bg_width = $this->Width - $fg_width;

        $RenderHtml = "<table class='tbl' cellspacing=0 cellpadding=0 width=" . $this->Width . "><tr>";
        if ($fg_width > 0) {
            $RenderHtml = $RenderHtml . "<td width=" . $fg_width . " height=" . $this->Height .
                    ($this->FgColor ? (" bgcolor='" . $this->FgColor . "'") : '') .
                    ($this->FgClass ? (" class='" . $this->FgClass . "'") : '') .
                    "><img src='$themeimg/shim.gif'></td>";
        }
        if ($bg_width > 0) {
            $RenderHtml = $RenderHtml . "<td width=" . $bg_width . " height=" . $this->Height .
                    ($this->BgColor ? (" bgcolor='" . $this->BgColor . "'") : '') .
                    ($this->BgClass ? (" class='" . $this->BgClass . "'") : '') .
                    "><img src='$themeimg/shim.gif'></td>";
        }
        $RenderHtml = $RenderHtml . "</tr></table>";
        return $RenderHtml;
    }

}
