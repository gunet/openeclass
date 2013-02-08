<?php

class Plotter {

    private $width;
    private $height;
    private $title;
    private $data;

    public function Plotter($width = 200, $height = 200) {
        $this->setDimension($width, $height);
        $this->data = array();
    }

    public function setDimension($width, $height) {
        $this->width = $width;
        $this->height = $height;
    }

    public function modDimension($width, $height) {
        $this->width += $width;
        $this->height += $height;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function addPoint($name, $value) {
        $this->data[$name] = $value;
    }

    public function growWithPoint($name, $value) {
        $this->modDimension(25, 0);
        $this->addPoint($name, $value);
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

            //     load_js('jquery');
            load_js('flot');

            $dataset = '[';
            foreach ($this->data as $name => $value) {
                $dataset .= '["' . $name . '", ' . $value . "], ";
            }
            if (strlen($dataset) > 1) {
                $dataset = substr($dataset, 0, -2);
            }
            $dataset .=']';

            return '
                
<div class="flot-container" style="width: ' . $this->width . 'px; height: ' . $this->height . 'px;">
<p class="flot-title">' . $this->title . '</p>
<div class="flot-placeholder" id="placeholder"></div>
</div>

<script type="text/javascript">
    $(function() {
        var data = ' . $dataset . ';
        $.plot("#placeholder", [ data ], {
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
                tickLength: 0
            }
        });
    });
</script>';
        }
    }

}

?>
