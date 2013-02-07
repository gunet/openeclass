<?php

require_once 'include/libchart/classes/libchart.php';

class Plotter {

    private $width;
    private $height;
    private $title;
    private $data;

    public function Plotter() {
        $this->setDimension(200, 200);
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

    public function plot($emptyerror) {
        if (count($this->data) == 0) {
            return $emptyerror;
        } else {
            global $course_code, $urlServer, $webDir;

            $chart_path = 'courses/' . $course_code . '/temp/chart_' . md5(serialize($this)) . '.png';

            $dataset = new XYDataSet();
            foreach ($this->data as $name => $value) {
                $dataset->addPoint(new Point($name, $value));
            }

            $chart = new VerticalBarChart($this->width, $this->height);
            $chart->setTitle($this->title);
            $chart->setDataSet($dataset);
            $chart->render($webDir . '/' . $chart_path);

            return '<p align="center"><img src="' . $urlServer . $chart_path . '" /></p>';
        }
    }

    public function plotJS($emptyerror) {
        if (count($this->data) == 0) {
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
