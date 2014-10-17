<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

class Plotter {

    private $width;
    private $height;
    private $title;
    private $data;
    private static $instanceCounter = 0;

    function __construct($width = "100%", $height = "300px") {
        $this->setDimension($width, $height);
        $this->data = array();
        self::$instanceCounter++;
    }

    public function setDimension($width, $height) {
        $this->width = $width;
        $this->height = $height;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function addPoint($name, $value) {
        $this->data[$name] = $value;
    }

    public function normalize() {
        $total = 0;
        foreach ($this->data as $name => $value) {
            $total += $value;
        }
        foreach ($this->data as $name => $value) {
            $this->data[$name] = $value * 100 / $total;
        }
    }

    public function isEmpty() {
        return $this->data == 0;
    }

    public function plot($emptyerror = "") {
        if ($this->isEmpty()) {
            return $emptyerror;
        } else {

            load_js('flot');

            $dataset = '[';
            foreach ($this->data as $name => $value) {
                $name = ellipsize($name, 17);
                $dataset .= '["' . $name . '", ' . $value . "], ";
            }
            if (strlen($dataset) > 1) {
                $dataset = substr($dataset, 0, -2);
            }
            $dataset .=']';

            return '
                
<div class="flot-container" style="width: ' . $this->width . '; height: ' . $this->height . ';">
<p class="flot-title">' . $this->title . '</p>
<div class="flot-placeholder" id="placeholder' . self::$instanceCounter . '"></div>
</div>

<script type="text/javascript">

    function doPlot(data) {
        $.plot("#placeholder' . self::$instanceCounter . '", [ data ], {
            series: {
                bars: {
                    show: true,
                    barWidth: 0.8,
                    align: "center"
                }
            }
            ,
            xaxis: {
                mode: "categories",
                labelAngle: -45,
                tickLength: 0
            }
        });
    }
    
    $(function() {
        var data = ' . $dataset . ';
        doPlot(data);
         window.onresize = function(event) {
            doPlot(data);
         }
    });
</script>';
        }
    }

}

?>
