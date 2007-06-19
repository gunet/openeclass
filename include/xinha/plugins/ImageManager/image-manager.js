/**
 * The ImageManager plugin javascript.
 * @author $Author$
 * @version $Id$
 * @package ImageManager
 */

/**
 * To Enable the plug-in add the following line before HTMLArea is initialised.
 *
 * HTMLArea.loadPlugin("ImageManager");
 *
 * Then configure the config.inc.php file, that is all.
 * For up-to-date documentation, please visit http://www.zhuo.org/htmlarea/
 */

/**
 * It is pretty simple, this file over rides the HTMLArea.prototype._insertImage
 * function with our own, only difference is the popupDialog url
 * point that to the php script.
 */

function ImageManager(editor)
{

}

ImageManager._pluginInfo = {
	name          : "ImageManager",
	version       : "1.0",
	developer     : "Xiang Wei Zhuo",
	developer_url : "http://www.zhuo.org/htmlarea/",
	license       : "htmlArea"
};


// CONFIGURATION README:
//
//  It's useful to pass the configuration to the backend through javascript
//  (this saves editing the backend config itself), this needs to be done
//  in a trusted/secure manner... here is how to do it..
//
//  1. You need to be able to put PHP in your xinha_config setup
//  2. In step 3 write something like
//  --------------------------------------------------------------
//  with (xinha_config.ImageManager)
//  { 
//    <?php 
//      require_once('/path/to/xinha/contrib/php-xinha.php');
//      xinha_pass_to_php_backend
//      (       
//        array
//        (
//         'images_dir' => '/home/your/directory',
//         'images_url' => '/directory'
//        )
//      )
//    ?>
//  }
//  --------------------------------------------------------------
//
//  this will work provided you are using normal file-based PHP sessions
//  (most likely), if not, you may need to modify the php-xinha.php
//  file to suit your setup.

HTMLArea.Config.prototype.ImageManager =
{
  'backend'    : _editor_url + 'plugins/ImageManager/backend.php?__plugin=ImageManager&',
  'backend_data' : null,
  
  // Deprecated method for passing config, use above instead!
  //---------------------------------------------------------
  'backend_config'     : null,
  'backend_config_hash': null,
  'backend_config_secret_key_location': 'Xinha:ImageManager'
  //---------------------------------------------------------
};

// Over ride the _insertImage function in htmlarea.js.
// Open up the ImageManger script instead.

HTMLArea.prototype._insertImage = function(image) {

	var editor = this;	// for nested functions
	var outparam = null;
	if (typeof image == "undefined") {
		image = this.getParentElement();
		if (image && !/^img$/i.test(image.tagName))
			image = null;
	}

	// the selection will have the absolute url to the image. 
	// coerce it to be relative to the images directory.
	//
	// FIXME: we have the correct URL, but how to get it to select?
	// FIXME: need to do the same for MSIE.

	if ( image )
		{

		outparam =
			{
			f_url    : HTMLArea.is_ie ? image.src : image.src,
			f_alt    : image.alt,
			f_border : image.style.borderWidth ? image.style.borderWidth : image.border,
			f_align  : image.align,
			f_padding: image.style.padding,
			f_margin : image.style.margin,
			f_width  : image.width,
			f_height  : image.height,
      f_backgroundColor: image.style.backgroundColor,
      f_borderColor: image.style.borderColor
			};

    function shortSize(cssSize)
    {
      if(/ /.test(cssSize))
      {
        var sizes = cssSize.split(' ');
        var useFirstSize = true;
        for(var i = 1; i < sizes.length; i++)
        {
          if(sizes[0] != sizes[i])
          {
            useFirstSize = false;
            break;
          }
        }
        if(useFirstSize) cssSize = sizes[0];
      }
      return cssSize;
    }
    outparam.f_border = shortSize(outparam.f_border);
    outparam.f_padding = shortSize(outparam.f_padding);
    outparam.f_margin = shortSize(outparam.f_margin);

		} // end of if we selected an image before raising the dialog.

	// the "manager" var is legacy code. Should probably reference the
	// actual config variable in each place .. for now this is good enough.

	// alert( "backend is '" + editor.config.ImageManager.backend + "'" );

	var manager = editor.config.ImageManager.backend + '__function=manager';
  if(editor.config.ImageManager.backend_config != null)
  {
    manager += '&backend_config='
      + encodeURIComponent(editor.config.ImageManager.backend_config);
    manager += '&backend_config_hash='
      + encodeURIComponent(editor.config.ImageManager.backend_config_hash);
    manager += '&backend_config_secret_key_location='
      + encodeURIComponent(editor.config.ImageManager.backend_config_secret_key_location);
  }
  
  if(editor.config.ImageManager.backend_data != null)
  {
    for ( var i in editor.config.ImageManager.backend_data )
    {
      manager += '&' + i + '=' + encodeURIComponent(editor.config.ImageManager.backend_data[i]);
    }
  }
  
	Dialog(manager, function(param) {
		if (!param) {	// user must have pressed Cancel
			return false;
		}
		var img = image;
		if (!img) {
			if (HTMLArea.is_ie) {
        var sel = editor._getSelection();
        var range = editor._createRange(sel);
        editor._doc.execCommand("insertimage", false, param.f_url);
				img = range.parentElement();
				// wonder if this works...
				if (img.tagName.toLowerCase() != "img") {
					img = img.previousSibling;
				}
			} else {
				img = document.createElement('img');
        img.src = param.f_url;
        editor.insertNodeAtSelection(img);
			}
		} else {			
			img.src = param.f_url;
		}
		
		for (field in param) {
			var value = param[field];
			switch (field) {
			    case "f_alt"    : img.alt	 = value; break;
			    case "f_border" :
          if(value.length)
          {           
            img.style.borderWidth = /[^0-9]/.test(value) ? value :  (parseInt(value) + 'px');
            if(img.style.borderWidth && !img.style.borderStyle)
            {
              img.style.borderStyle = 'solid';
            }
          }
          else
          {
            img.style.borderWidth = '';
            img.style.borderStyle = '';
          }
          break;
          
          case "f_borderColor": img.style.borderColor = value; break;
          case "f_backgroundColor": img.style.backgroundColor = value; break;
            
          case "f_padding": 
          {
            if(value.length)
            {
              img.style.padding = /[^0-9]/.test(value) ? value :  (parseInt(value) + 'px'); 
            }
            else
            {
              img.style.padding = '';
            }
          }
          break;
          
          case "f_margin": 
          {
            if(value.length)
            {
              img.style.margin = /[^0-9]/.test(value) ? value :  (parseInt(value) + 'px'); 
            }
            else
            {
              img.style.margin = '';
            }
          }
          break;
          
			    case "f_align"  : img.align	 = value; break;
            
          case "f_width" : 
          {
            if(!isNaN(parseInt(value))) { img.width  = parseInt(value); } else { img.width = ''; }
          }
          break;
          
				  case "f_height":
          {
            if(!isNaN(parseInt(value))) { img.height = parseInt(value); } else { img.height = ''; }
          }
          break;
			}

		}
		
		
	}, outparam);
};
