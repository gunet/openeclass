<?php

class myGauge {

   // Default Color
   var $BgColor = "#FFFFFF", $FgColor = "#990000";
   // Default Dimensions.
   var $Width = 125, $Height = 10;
   // Default Values.
   var $MinVal = 0, $MaxVal = 100, $CurVal = 77;

   // Set values
   function setValues($fgc, $bgc, $wid, $hei, $min, $max, $cur) {
       $this->BgColor = $fgc;
       $this->FgColor = $bgc;
       $this->Width   = $wid;
       $this->Height  = $hei;
       $this->MinVal  = $min;
       $this->MaxVal  = $max;
       $this->CurVal  = $cur;
   }

   // Render this into HTML as a table.
   function display() {

       // Normalize the properties.
       if ($this->MinVal > $this->MaxVal) {
           $temp_val = $this->MinVal;
           $this->MinVal = $this->MaxVal;
           $this->MaxVal = $temp_val;
       }

       if ($this->CurVal < $this->MinVal) {
           $this->CurVal = $this->MinVal;
       }
       elseif ($this->CurVal > $this->MaxVal) {
           $this->CurVal = $this->MaxVal;
       }

       // Figure out the percentage that the CurVal is within MinVal and MaxVal.
       $percentage_val = ($this->CurVal - $this->MinVal) / ($this->MaxVal - $this->MinVal);

       // Compute the first and second widths.
       $fg_width = Round($this->Width * $percentage_val);
       $bg_width = $this->Width - $fg_width;

       $RenderHtml = "<table cellspacing=0 cellpadding=0 width=" . $this->Width . " height=" . $this->Height . "><tr>";
       if ($fg_width > 0) {
           $RenderHtml = $RenderHtml . "<td width=" . $fg_width . " height=" . $this->Height . " bgcolor=" . $this->FgColor .
               "><img src=\"/images/shim.gif\"></td>";
       }
       if ($bg_width > 0) {
           $RenderHtml = $RenderHtml . "<td width=" . $bg_width . " height=" .
           $this->Height . " bgcolor=" . $this->BgColor . "><img src=\"/images/shim.gif\"></td>";
       }
       $RenderHtml = $RenderHtml . "</tr></table>";
       
       return $RenderHtml;
       
   }

}

/* Main

//$oGauge = new myGauge();


// Use method setValues to set datamembers, like:
$fc = "#FFFFFF";
$bc = "#990000";
$wi = 125;
$hi = 10;
$mi = 0;
$ma = 100;
$cu = 47;
$oGauge->setValues($fc, $bc, $wi, $hi, $mi, $ma, $cu);
*/

?>

<!-- Display the gauge 
<table cellpadding=0 cellspacing=2 border="1">
   <tr>
       <td><?php //echo $tmp = $oGauge->display(); ?></td>
   </tr>
</table>

</body>
</html>-->