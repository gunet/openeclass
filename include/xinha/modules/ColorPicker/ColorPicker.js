
  /**
   * Gogo Internet Services Color Picker Javascript Widget
   * colorPicker for short.
   *
   * @author James Sleeman <james@gogo.co.nz>
   * @date June, 2005
   *
   * The colorPicker class provides access to a color map for selecting
   * colors which will be passed back to a callback (usually such a callback would
   * write the RGB hex value returned into a field, but that's up to you).
   *
   * The color map presented is a standard rectangular pallate with 0->360 degrees of
   * hue on the Y axis and 0->100% saturation on the X axis, the value (brightness) is
   * selectable as a vertical column of grey values.  Also present is a one row of
   * white->grey->black for easy selection of these colors.
   *
   * A checkbox is presented, which when checked will force the palatte into "web safe"
   * mode, only colours in the "web safe palatte" of 216 colors will be shown, the palatte
   * is adjusted so that the normal blend of colours are "rounded" to the nearest web safe
   * one.  It should be noted that "web safe" colours really are a thing of the past,
   * not only can pretty much every body display several million colours, but it's actually
   * been found that of those 216 web safe colours only 20 to 30 are actually going to be
   * displayed equally on the majority of monitors, and those are mostly yellows!
   *
   * =Usage Example=
   * {{{
   *  <!-- Here is the field -->         <!-- And we will use this button to open the picker"
   *  <input type="text" id="myField" /> <input type="button" value="..." id="myButton" />
   *  <script>
   *    // now when the window loads link everything up
   *    window.onload = function()
   *    {
   *
   *      var myField  = document.getElementById('myField');  // Get our field
   *      var myButton = document.getElementById('myButton'); // And the button
   *      var myPicker = new colorPicker                      // Make a picker
   *        (
   *          {
   *              // Cellsize is the width and height of each colour cell
   *            cellsize: '5px',
   *              // Callback is the function to execute when we are done,
   *              // this one puts the color value into the field
   *            callback: function(color){myField.value=color},
   *              // Granularity defines the maximum number of colors per row/column
   *              // more colors (high number) gives a smooth gradient of colors
   *              // but it will take (much) longer to display, while a small number
   *              // displays quickly, but doesn't show as many different colors.
   *              // Experiement with it, 18 seems like a good number.
   *            granularity: 18,
   *              // Websafe specifies whether or not to include the Web Safe checkbox
   *            websafe: false,
   *              // Savecolors specifies the number of recently-selected colors to remember
   *            savecolors: 20
   *           }
   *        );
   *
   *      // And now hookup the button to open the picker,
   *      //  the function to do that is myPicker.open()
   *      //  it accepts two parameters, the "anchorage" and the element to anchor to,
   *      //  and an optional third parameter, an initial color code to show in 
   *      //  the text box and sample.
   *      //
   *      //  anchorage is made up of two of the keywords bottom,top,left and right
   *      //    left:   the left edge of the picker will align to the left edge of the element
   *      // or right:  the right edgeof the picker aligns to the right edge of the element
   *      //    top:    the picker will appear above the element
   *      // or bottom: the picker will appear below the element
   *
   *      myButton.onclick =
   *        function()
   *        {              // anchorage   , element to anchor to
   *          myPicker.open('bottom,right', myButton, initcolor)
   *        };
   *    }
   *  </script>
   * }}}
   */
  ColorPicker._pluginInfo =
  {
    name     : "colorPicker",
    version  : "1.0",
    developer: "James Sleeman",
    developer_url: "http://www.gogo.co.nz/",
    c_owner      : "Gogo Internet Services",
    license      : "htmlArea",
    sponsor      : "Gogo Internet Services",
    sponsor_url  : "http://www.gogo.co.nz/"
  };
  function ColorPicker() 
  {
	// dummy function for Xinha plugin api, note the different names
  }
  //the actual function is below
  Xinha.colorPicker = function (params)
  {
    // if the savedColors is empty, try to read the savedColors from cookie
    if ( Xinha.colorPicker.savedColors.length === 0 )
    {
      Xinha.colorPicker.loadColors();
    }
    var picker = this;
    var enablepick = false;
    var enablevalue = false;
    var pickrow = 0;
    var pickcol = 0;
    this.callback = params.callback?params.callback:function(color){alert('You picked ' + color );};
    this.websafe  = params.websafe?params.websafe:false;
    this.savecolors = params.savecolors? params.savecolors: 20;

    this.cellsize = parseInt(params.cellsize?params.cellsize:'10px', 10);
    this.side     = params.granularity?params.granularity:18;
    var valuecol = this.side + 1;
    var valuerow = this.side - 1;

    this.value = 1;
    this.saved_cells = null;
    this.table = document.createElement('table');
    this.table.className = "dialog";
    this.table.cellSpacing = this.table.cellPadding = 0;
    this.table.onmouseup = function()
    {
    	 enablepick = false;
    	 enablevalue = false;
    };
    this.tbody = document.createElement('tbody');
    this.table.appendChild(this.tbody);
    this.table.style.border = '1px solid WindowFrame';
    this.table.style.zIndex = '1000';
    // Add a title bar and close button
    var tr = document.createElement('tr');
    
    var td = document.createElement('td');
    td.colSpan = this.side;
    td.className= "title";
    td.style.fontFamily = 'small-caption,caption,sans-serif';
    td.style.fontSize = 'x-small';
    td.appendChild(document.createTextNode(Xinha._lc('Click a color...')));
    td.style.borderBottom = '1px solid WindowFrame';
    tr.appendChild(td);
    td = null;

    var td = document.createElement('td');
    td.className= "title";
    td.colSpan = 2;
    td.style.fontFamily = 'Tahoma,Verdana,sans-serif';
    td.style.borderBottom = '1px solid WindowFrame';
    td.style.paddingRight = '0';
    tr.appendChild(td);
    

    var but = document.createElement('div');
    but.title = Xinha._lc("Close");
    but.className= 'buttonColor';
    but.style.height = '11px';
    but.style.width = '11px';
    but.style.cursor = 'pointer';
    but.onclick = function() { picker.close(); };
    but.appendChild(document.createTextNode('\u00D7'));
    but.align = 'center';
    but.style.verticalAlign = 'top';
    but.style.position = 'relative';
    but.style.cssFloat = 'right';
    but.style.styleFloat = 'right';
    but.style.padding = '0';
    but.style.margin = '2px';
    but.style.backgroundColor = 'transparent';
    but.style.fontSize= '11px';
    if ( !Xinha.is_ie) but.style.lineHeight= '9px'; // line-height:9px is better for centering the x, but IE cuts it off at the bottom :(
    but.style.letterSpacing= '0';
    
        
    td.appendChild(but);

    this.tbody.appendChild(tr);
    but = tr = td = null;

    this.constrain_cb = document.createElement('input');
    this.constrain_cb.type = 'checkbox';

    this.chosenColor = document.createElement('input');
    this.chosenColor.type = 'text';
    this.chosenColor.maxLength = 7;
    this.chosenColor.style.width = '50px';
    this.chosenColor.style.fontSize = '11px';
    
    this.chosenColor.onchange = function()
      {
      	if(/#[0-9a-f]{6,6}/i.test(this.value))
      	{
    	      picker.backSample.style.backgroundColor = this.value;
          picker.foreSample.style.color = this.value;
      	}
      };

    this.backSample = document.createElement('div');
    this.backSample.appendChild(document.createTextNode('\u00A0'));
    this.backSample.style.fontWeight = 'bold';
    this.backSample.style.fontFamily = 'small-caption,caption,sans-serif';
    this.backSample.fontSize = 'x-small';

    this.foreSample = document.createElement('div');
    this.foreSample.appendChild(document.createTextNode(Xinha._lc('Sample')));
    this.foreSample.style.fontWeight = 'bold';
    this.foreSample.style.fontFamily = 'small-caption,caption,sans-serif';
    this.foreSample.fontSize = 'x-small';

    /** Convert a decimal number to a two byte hexadecimal representation.
      * Zero-pads if necessary.
      *
      * @param integer dec Integer from 0 -> 255
      * @returns string 2 character hexadecimal (zero padded)
      */
    function toHex(dec)
    {
      var h = dec.toString(16);
      if(h.length < 2) { h = '0' + h; }
      return h;
    }

    /** Convert a color object {red:x, green:x, blue:x} to an RGB hex triplet
     * @param object tuple {red:0->255, green:0->255, blue:0->255}
     * @returns string hex triplet (#rrggbb)
     */

    function tupleToColor(tuple)
    {
      return '#' + toHex(tuple.red) + toHex(tuple.green) + toHex(tuple.blue);
    }

    /** Determine the nearest power of a number to another number
     * (eg nearest power of 4 to 5 => 4, of 4 to 7 => 8)
     *
     * @usedby rgbToWebsafe
     * @param number num number to round to nearest power of <power>
     * @param number power number to find the nearest power of
     * @returns number Nearest power of <power> to num.
     */

    function nearestPowerOf(num,power)
    {
      return Math.round(Math.round(num / power) * power);
    }

    /** Concatenate the hex representation of dec to itself and return as an integer.
     *  eg dec = 10 -> A -> AA -> 170
     *
     * @usedby rgbToWebsafe
     * @param dec integer
     * @returns integer
     */

    function doubleHexDec(dec)
    {
      return parseInt(dec.toString(16) + dec.toString(16), 16);
    }

    /** Convert a given RGB color to the nearest "Web-Safe" color.  A websafe color only has the values
     *  00, 33, 66, 99, CC and FF for each of the red, green and blue components (thus 6 shades of each
     *  in combination to produce 6 * 6 * 6 = 216 colors).
     *
     * @param    color object {red:0->255, green:0->255, blue:0->255}
     * @returns  object {red:51|102|153|204|255, green:51|102|153|204|255, blue:51|102|153|204|255}
     */
    function rgbToWebsafe(color)
    {
      // For each take the high byte, divide by three, round and multiply by three before rounding again
      color.red   = doubleHexDec(nearestPowerOf(parseInt(toHex(color.red).charAt(0), 16), 3));
      color.blue  = doubleHexDec(nearestPowerOf(parseInt(toHex(color.blue).charAt(0), 16), 3));
      color.green = doubleHexDec(nearestPowerOf(parseInt(toHex(color.green).charAt(0), 16), 3));
      return color;
    }

    /** Convert a combination of hue, saturation and value into an RGB color.
     *  Hue is defined in degrees, saturation and value as a floats between 0 and 1 (0% -> 100%)
     *
     * @param h float angle of hue around color wheel 0->360
     * @param s float saturation of color (no color (grey)) 0->1 (vibrant)
     * @param v float value (brightness) of color (black) 0->1 (bright)
     * @returns object {red:0->255, green:0->255, blue:0->255}
     * @seealso http://en.wikipedia.org/wiki/HSV_color_space
     */
    function hsvToRGB(h,s,v)
    {
      var colors;
      if(s === 0)
      {
        // GREY
        colors = {red:v,green:v,blue:v};
      }
      else
      {
        h /= 60;
        var i = Math.floor(h);
        var f = h - i;
        var p = v * (1 - s);
        var q = v * (1 - s * f);
        var t = v * (1 - s * (1 - f) );
        switch(i)
        {
          case 0: colors =  {red:v, green:t, blue:p}; break;
          case 1: colors =  {red:q, green:v, blue:p}; break;
          case 2: colors =  {red:p, green:v, blue:t}; break;
          case 3: colors =  {red:p, green:q, blue:v}; break;
          case 4: colors =  {red:t, green:p, blue:v}; break;
          default:colors =  {red:v, green:p, blue:q}; break;
        }
      }
      colors.red = Math.ceil(colors.red * 255);
      colors.green = Math.ceil(colors.green * 255);
      colors.blue = Math.ceil(colors.blue * 255);
      return colors;
    }

    /** Open the color picker
     *
     * @param string anchorage pair of sides of element to anchor the picker to
     *   "top,left" "top,right" "bottom,left" or "bottom,right"
     * @param HTML_ELEMENT element the element to anchor the picker to sides of
     *
     * @note The element is just referenced here for positioning (anchoring), it
     * does not automatically get the color copied into it.  See the usage instructions
     * for the class.
     */

    this.open = function(anchorage,element,initcolor)
    {
      this.table.style.display = '';

      this.pick_color();
      if(initcolor && /#[0-9a-f]{6,6}/i.test(initcolor))
      {
        this.chosenColor.value = initcolor;
    	    this.backSample.style.backgroundColor = initcolor;
        this.foreSample.style.color = initcolor;
      }

      // Find position of the element
      this.table.style.position = 'absolute';
      var e = element;
      var top  = 0;
      var left = 0;
      do
      {
        top += e.offsetTop;
        left += e.offsetLeft;
        e = e.offsetParent;
      }
      while(e);

      var x, y;
      if(/top/.test(anchorage))
      {
        if(top - this.table.offsetHeight > 0)
        {
          this.table.style.top = (top - this.table.offsetHeight) + 'px';
        } 
        else
        {
        	  this.table.style.top = 0;
        }
      }
      else
      {
        this.table.style.top = (top + element.offsetHeight) + 'px';
      }

      if(/left/.test(anchorage))
      {
        this.table.style.left = left + 'px';
      }
      else
      {
        if(left - (this.table.offsetWidth - element.offsetWidth) > 0)
        {
          this.table.style.left = (left - (this.table.offsetWidth - element.offsetWidth)) + 'px';
        }
        else
        {
        	  this.table.style.left = 0;
        }
      }
     // IE ONLY - prevent windowed elements (<SELECT>) to render above the colorpicker
      /*@cc_on
      this.iframe.style.top = this.table.style.top;
      this.iframe.style.left = this.table.style.left;
      @*/
    };

    function pickCell(cell)
    {
        picker.chosenColor.value = cell.colorCode;
        picker.backSample.style.backgroundColor = cell.colorCode;
        picker.foreSample.style.color = cell.colorCode;
        if((cell.hue >= 195  && cell.saturation > 0.5) || 
        		(cell.hue === 0 && cell.saturation === 0 && cell.value < 0.5) || 
        		(cell.hue !== 0 && picker.value < 0.75))
        {
          cell.style.borderColor = '#fff';
        }
        else
        {
          cell.style.borderColor = '#000';
        }
        pickrow = cell.thisrow;
        pickcol = cell.thiscol;
     }
    
    function pickValue(cell)
    {
   //     cell.style.borderWidth = '1px';
   //     cell.style.borderStyle = 'solid';
        if(picker.value < 0.5)
        {
           cell.style.borderColor = '#fff';
        }
        else
        {
        	  cell.style.borderColor = '#000';
        }
        valuerow = cell.thisrow;
        valuecol = cell.thiscol;
        picker.chosenColor.value = picker.saved_cells[pickrow][pickcol].colorCode;
        picker.backSample.style.backgroundColor = picker.saved_cells[pickrow][pickcol].colorCode;
        picker.foreSample.style.color = picker.saved_cells[pickrow][pickcol].colorCode;
    }
    
    function unpickCell(row,col)
    {
    	  picker.saved_cells[row][col].style.borderColor = picker.saved_cells[row][col].colorCode;
    }
    
    /** Draw the color picker. */
    this.pick_color = function()
    {
      var rows, cols;
      var picker = this;
      var huestep = 359/(this.side);
      var saturstep = 1/(this.side - 1);
      var valustep  = 1/(this.side - 1);
      var constrain = this.constrain_cb.checked;
      

      if(this.saved_cells === null)
      {
        this.saved_cells = [];

        for(var row = 0; row < this.side; row++)
        {
          var tr = document.createElement('tr');
          this.saved_cells[row] = [];
          for(var col = 0; col < this.side; col++)
          {
            var td = document.createElement('td');
            if(constrain)
            {
              td.colorCode = tupleToColor(rgbToWebsafe(hsvToRGB(huestep*row, saturstep*col, this.value)));
            }
            else
            {
              td.colorCode = tupleToColor(hsvToRGB(huestep*row, saturstep*col, this.value));
            }
            this.saved_cells[row][col] = td;
            td.style.height = this.cellsize + 'px';
            td.style.width = this.cellsize -2 +'px';
            td.style.borderWidth = '1px';
            td.style.borderStyle = 'solid';
            td.style.borderColor = td.colorCode;
            td.style.backgroundColor = td.colorCode;
            if(row == pickrow && col == pickcol)
            {
              td.style.borderColor = '#000';
              this.chosenColor.value = td.colorCode;
              this.backSample.style.backgroundColor = td.colorCode;
              this.foreSample.style.color = td.colorCode;
            }
            td.hue = huestep * row;
            td.saturation = saturstep*col;
            td.thisrow = row;
            td.thiscol = col;
            td.onmousedown = function()
            {
              enablepick = true;
//            	 unpickCell(pickrow,pickcol);
              picker.saved_cells[pickrow][pickcol].style.borderColor = picker.saved_cells[pickrow][pickcol].colorCode;
              pickCell(this);
            };
            td.onmouseover = function()
            {
            	 if(enablepick)
            	 {
            	 	pickCell(this);
            	 }
            };
            td.onmouseout = function()
            {
            	 if(enablepick)
            	 {
    //            this.style.borderColor = picker.saved_cells[this.thisrow][this.thiscol].colorCode;
                this.style.borderColor = this.colorCode;
            	 }
            };
            td.ondblclick = function() { Xinha.colorPicker.remember(this.colorCode, picker.savecolors); picker.callback(this.colorCode); picker.close(); };
            td.appendChild(document.createTextNode(' '));
            td.style.cursor = 'pointer';
            tr.appendChild(td);
            td = null;
          }

          // Add a blank and then a value column
          var td = document.createElement('td');
          td.appendChild(document.createTextNode(' '));
          td.style.width = this.cellsize + 'px';
          tr.appendChild(td);
          td = null;

          var td = document.createElement('td');
          this.saved_cells[row][col+1] = td;
          td.appendChild(document.createTextNode(' '));
          td.style.width  = this.cellsize -2 + 'px';
          td.style.height = this.cellsize + 'px';
          td.constrainedColorCode  = tupleToColor(rgbToWebsafe(hsvToRGB(0,0,valustep*row)));
          td.style.backgroundColor = td.colorCode = tupleToColor(hsvToRGB(0,0,valustep*row));
          td.style.borderWidth = '1px';
          td.style.borderStyle = 'solid';
//          td.style.borderColor = td.style.backgroundColor;
          td.style.borderColor = td.colorCode;
          if(row == valuerow)
          {
            td.style.borderColor = 'black';
          }
          td.hue = huestep * row;
          td.saturation = saturstep*col;
          td.hsv_value = valustep*row;
          td.thisrow = row;
          td.thiscol = col + 1;
          td.onmousedown = function()
          {
            enablevalue = true;
//            unpickCell(valuerow,valuecol);
            picker.saved_cells[valuerow][valuecol].style.borderColor = picker.saved_cells[valuerow][valuecol].colorCode;
            picker.value = this.hsv_value; 
            picker.pick_color();
            pickValue(this);
          };
          td.onmouseover = function() {
            if(enablevalue)
            {
              picker.value = this.hsv_value; 
              picker.pick_color();
              pickValue(this);
            }
          };
          td.onmouseout = function()
          {
            if(enablevalue)
            {
       //       this.style.borderWidth = 0;
       //       this.style.borderStyle = 'none';
              this.style.borderColor = this.colorCode;//'';
            }
          };
          td.style.cursor = 'pointer';
          tr.appendChild(td);
          td = null;

          this.tbody.appendChild(tr);
          tr = null;
        }

        // Add one row of greys
        var tr = document.createElement('tr');
        this.saved_cells[row] = [];
        for(var col = 0; col < this.side; col++)
        {
          var td = document.createElement('td');
          if(constrain)
          {
            td.colorCode = tupleToColor(rgbToWebsafe(hsvToRGB(0, 0, valustep*(this.side-col-1))));
          }
          else
          {
            td.colorCode = tupleToColor(hsvToRGB(0, 0, valustep*(this.side-col-1)));
          }
          this.saved_cells[row][col] = td;
          td.style.height = this.cellsize + 'px';
          td.style.width = this.cellsize -2 +'px';
          td.style.borderWidth = '1px';
          td.style.borderStyle = 'solid';
          td.style.borderColor = td.colorCode;
          td.style.backgroundColor = td.colorCode;
          td.hue = 0;
          td.saturation = 0;
          td.value = valustep*(this.side-col-1);
          td.thisrow = row;
          td.thiscol = col;
          td.onmousedown = function()
          {
            enablepick = true;
  //          unpickCell(pickrow,pickcol);
            picker.saved_cells[pickrow][pickcol].style.borderColor = picker.saved_cells[pickrow][pickcol].colorCode;
            pickCell(this);
          };
          td.onmouseover = function()
          {
            if(enablepick)
            {
              pickCell(this);
            }
          };
          td.onmouseout = function()
          {
            if(enablepick)
            {
   //           this.style.borderColor = picker.saved_cells[this.thisrow][this.thiscol].colorCode;
              this.style.borderColor = this.colorCode;
        	   }
          };
          td.ondblclick = function() { Xinha.colorPicker.remember(this.colorCode, picker.savecolors); picker.callback(this.colorCode); picker.close(); };
          td.appendChild(document.createTextNode(' '));
          td.style.cursor = 'pointer';
          tr.appendChild(td);
          td = null;
        }
        this.tbody.appendChild(tr);
        tr = null;

        var tr = document.createElement('tr');
        var td = document.createElement('td');
        tr.appendChild(td);
        td.colSpan = this.side + 2;
        td.style.padding = '3px';

        if ( this.websafe )
        {
        var div = document.createElement('div');
        var label = document.createElement('label');
        label.appendChild(document.createTextNode(Xinha._lc('Web Safe: ')));

        this.constrain_cb.onclick = function() { picker.pick_color(); };
        label.appendChild(this.constrain_cb);
        label.style.fontFamily = 'small-caption,caption,sans-serif';
        label.style.fontSize = 'x-small';
        div.appendChild(label);
        td.appendChild(div);
        div = null;
        }

        var div = document.createElement('div');
        var label = document.createElement('label');
        label.style.fontFamily = 'small-caption,caption,sans-serif';
        label.style.fontSize = 'x-small';
        label.appendChild(document.createTextNode(Xinha._lc('Color: ')));
        label.appendChild(this.chosenColor);
        div.appendChild(label);
        var but = document.createElement('span');
        but.className = "buttonColor ";
        but.style.fontSize = '13px';
        but.style.width = '24px';
        but.style.marginLeft = '2px';
        but.style.padding = '0px 4px';
        but.style.cursor = 'pointer';
        but.onclick = function() { Xinha.colorPicker.remember(picker.chosenColor.value, picker.savecolors); picker.callback(picker.chosenColor.value); picker.close(); };
        but.appendChild(document.createTextNode('OK'));
        but.align = 'center';
        div.appendChild(but);
        td.appendChild(div);

        var sampleTable = document.createElement('table');
        sampleTable.style.width = '100%';
        var sampleBody = document.createElement('tbody');
        sampleTable.appendChild(sampleBody);
        var sampleRow = document.createElement('tr');
        sampleBody.appendChild(sampleRow);
        var leftSampleCell = document.createElement('td');
        sampleRow.appendChild(leftSampleCell);
        leftSampleCell.appendChild(this.backSample);
        leftSampleCell.style.width = '50%';
        var rightSampleCell = document.createElement('td');
        sampleRow.appendChild(rightSampleCell);
        rightSampleCell.appendChild(this.foreSample);
        rightSampleCell.style.width = '50%';

        td.appendChild(sampleTable);
        var savedColors = document.createElement('div');
        savedColors.style.clear = 'both';

        function createSavedColors(color)
        {
          var is_ie = false;
          /*@cc_on is_ie = true; @*/
          var div = document.createElement('div');
          div.style.width = picker.cellsize + 'px';//13px';
          div.style.height = picker.cellsize + 'px';//13px';
          div.style.margin = '1px';
          div.style.border = '1px solid black';
          div.style.cursor = 'pointer';
          div.style.backgroundColor = color;
          div.style[ is_ie ? 'styleFloat' : 'cssFloat'] = 'left';
     //     div.onclick = function() { picker.callback(color); picker.close(); };
          div.ondblclick = function() { picker.callback(color); picker.close(); };
   //       div.onmouseover = function()
          div.onclick = function()
          {
            picker.chosenColor.value = color;
            picker.backSample.style.backgroundColor = color;
            picker.foreSample.style.color = color;
          };
          savedColors.appendChild(div);
        }
        for ( var savedCols = 0; savedCols < Xinha.colorPicker.savedColors.length; savedCols++ )
        {
          createSavedColors(Xinha.colorPicker.savedColors[savedCols]);
        }
        td.appendChild(savedColors);

        this.tbody.appendChild(tr);
        document.body.appendChild(this.table);
        
        //put an iframe behind the table to mask select lists in ie
        // IE ONLY - prevent windowed elements (<SELECT>) to render above the colorpicker
        /*@cc_on
        if ( !this.iframe )
        {
        this.iframe = document.createElement('iframe');
        this.iframe.frameBorder = 0;
        this.iframe.src = "javascript:;";
        this.iframe.style.position = "absolute";
        this.iframe.style.width = this.table.offsetWidth;
        this.iframe.style.height = this.table.offsetHeight;
        document.body.insertBefore(this.iframe, this.table);
        }
        this.iframe.style.display = '';
        @*/
      }
      else
      {
        for(var row = 0; row < this.side; row++)
        {
          for(var col = 0; col < this.side; col++)
          {
            if(constrain)
            {
              this.saved_cells[row][col].colorCode = tupleToColor(rgbToWebsafe(hsvToRGB(huestep*row, saturstep*col, this.value)));
            }
            else
            {
              this.saved_cells[row][col].colorCode = tupleToColor(hsvToRGB(huestep*row, saturstep*col, this.value));
            }
            this.saved_cells[row][col].style.backgroundColor = this.saved_cells[row][col].colorCode;
            this.saved_cells[row][col].style.borderColor = this.saved_cells[row][col].colorCode;
          }
        }
        var pickcell = this.saved_cells[pickrow][pickcol];
        this.chosenColor.value = pickcell.colorCode;
        this.backSample.style.backgroundColor = pickcell.colorCode;
        this.foreSample.style.color = pickcell.colorCode;
        if((pickcell.hue >= 195  && pickcell.saturation > 0.5) || 
        		(pickcell.hue === 0 && pickcell.saturation === 0 && pickcell.value < 0.5) || 
        		(pickcell.hue !== 0 && picker.value < 0.75))
        {
           pickcell.style.borderColor = '#fff';
        }
        else
        {
        	  pickcell.style.borderColor = '#000';
        }
      }
    };

    /** Close the color picker */
    this.close = function()
    {
      this.table.style.display = 'none';
      // IE ONLY - prevent windowed elements (<SELECT>) to render above the colorpicker
      /*@cc_on
      if ( this.iframe ) { this.iframe.style.display = 'none'; }
      @*/
    };
  }

// array of the saved colors
Xinha.colorPicker.savedColors = [];

// add the color to the savedColors
Xinha.colorPicker.remember = function(color, savecolors)
{
  // check if this color is known
  for ( var i = Xinha.colorPicker.savedColors.length; i--; )
  {
    if ( Xinha.colorPicker.savedColors[i] == color )
    {
      return false;
    }
  }
  // insert the new color
  Xinha.colorPicker.savedColors.splice(0, 0, color);
  // limit elements
  Xinha.colorPicker.savedColors = Xinha.colorPicker.savedColors.slice(0, savecolors);
  //[mokhet] probably some more parameters to send to the cookie definition
  // like domain, secure and such, especially with https connection i presume
  // save the cookie
  var expdate = new Date();
  expdate.setMonth(expdate.getMonth() + 1);

  document.cookie = 'XinhaColorPicker=' + escape (Xinha.colorPicker.savedColors.join('-')) + ';expires=' + expdate.toGMTString();
  return true;
};

// try to read the colors from the cookie
Xinha.colorPicker.loadColors = function()
{
  var index = document.cookie.indexOf('XinhaColorPicker');
  if ( index != -1 )
  {
    var begin = (document.cookie.indexOf('=', index) + 1);
    var end = document.cookie.indexOf(';', index);
    if ( end == -1 ) { end = document.cookie.length; }
    Xinha.colorPicker.savedColors = unescape(document.cookie.substring(begin, end)).split('-');
  }
};

Xinha.colorPicker._lc = function(string) {
  return Xinha._lc(string);
}