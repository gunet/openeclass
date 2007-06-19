/**
 * Functions for the ImageManager, used by manager.php only	
 * @author $Author$
 * @version $Id$
 * @package ImageManager
 */
	
	//Translation
	function i18n(str) {
        return HTMLArea._lc(str, 'ImageManager');
	}


	//set the alignment options
	function setAlign(align) 
	{
		var selection = document.getElementById('f_align');
		for(var i = 0; i < selection.length; i++)
		{
			if(selection.options[i].value == align)
			{
				selection.selectedIndex = i;
				break;
			}
		}
	}

	//initialise the form
	init = function () 
	{
		__dlg_init(null, {width:600,height:460});

		__dlg_translate('ImageManager');
        
        // This is so the translated string shows up in the drop down.
        document.getElementById("f_align").selectedIndex = 1;
        document.getElementById("f_align").selectedIndex = 0;
        
    // Hookup color pickers
    var bgCol_pick = document.getElementById('bgCol_pick');
    var f_backgroundColor = document.getElementById('f_backgroundColor');
    var bgColPicker = new Xinha.colorPicker({cellsize:'5px',callback:function(color){f_backgroundColor.value=color;}});
    bgCol_pick.onclick = function() { bgColPicker.open('top,right', f_backgroundColor ); }

    var bdCol_pick = document.getElementById('bdCol_pick');
    var f_borderColor = document.getElementById('f_borderColor');
    var bdColPicker = new Xinha.colorPicker({cellsize:'5px',callback:function(color){f_borderColor.value=color;}});
    bdCol_pick.onclick = function() { bdColPicker.open('top,right', f_borderColor ); }



		var uploadForm = document.getElementById('uploadForm');
		if(uploadForm) uploadForm.target = 'imgManager';

		var param = window.dialogArguments;
		if (param) 
		{
      var image_regex = new RegExp( '(https?://[^/]*)?' + base_url.replace(/\/$/, '') );
      param.f_url = param.f_url.replace( image_regex, "" );

      // The image URL may reference one of the automatically resized images 
      // (when the user alters the dimensions in the picker), clean that up
      // so it looks right and we get back to a normal f_url
      var rd = (_resized_dir) ? _resized_dir.replace(Xinha.RE_Specials, '\\$1') + '/' : '';
      var rp = _resized_prefix.replace(Xinha.RE_Specials, '\\$1');
      var dreg = new RegExp('^(.*/)' + rd + rp + '_([0-9]+)x([0-9]+)_([^/]+)$');
  
      if(dreg.test(param.f_url))
      {
        param.f_url    = RegExp.$1 + RegExp.$4;
        param.f_width  = RegExp.$2;
        param.f_height = RegExp.$3;
      }
      
      for (var id in param)
      {
        if(id == 'f_align') continue;
        if(document.getElementById(id))
        {
          document.getElementById(id).value = param[id];
        }
      }



      document.getElementById("orginal_width").value = param["f_width"];
			document.getElementById("orginal_height").value = param["f_height"];
			setAlign(param["f_align"]);

      // Locate to the correct directory
      var dreg = new RegExp('^(.*/)([^/]+)$');
      if(dreg.test(param['f_url']))
      {
        changeDir(RegExp.$1);
        var dirPath = document.getElementById('dirPath');
        for(var i = 0; i < dirPath.options.length; i++)
        {
          if(dirPath.options[i].value == encodeURIComponent(RegExp.$1))
          {
            dirPath.options[i].selected = true;
            break;
          }
        }
      }
      document.getElementById('f_preview').src = _backend_url + '__function=thumbs&img=' + param.f_url;      
		}

		document.getElementById("f_alt").focus();

    // For some reason dialog is not shrinkwrapping correctly in IE so we have to explicitly size it for now.
    // if(HTMLArea.is_ie) window.resizeTo(600, 460);
	};


	function onCancel() 
	{
		__dlg_close(null);
		return false;
	}

	function onOK() 
	{
		// pass data back to the calling window
		var fields = ["f_url", "f_alt", "f_align", "f_width", "f_height", "f_padding", "f_margin", "f_border", "f_borderColor", "f_backgroundColor"];
		var param = new Object();
		for (var i in fields) 
		{
			var id = fields[i];
			var el = document.getElementById(id);
			if(id == "f_url" && el.value.indexOf('://') < 0 )
				{

				if ( el.value == "" )
					{
					alert( i18n("No Image selected.") );
					return( false );
					}

				param[id] = makeURL(base_url,el.value);
				}
			else if (el)
				param[id] = el.value;
      else alert("Missing " + fields[i]);

		}

    // See if we need to resize the image
    var origsize =
    {
      w:document.getElementById('orginal_width').value,
      h:document.getElementById('orginal_height').value
    }

    if(  (origsize.w != param.f_width)
      || (origsize.h != param.f_height) )
    {
      // Yup, need to resize
      var resized = HTMLArea._geturlcontent(_backend_url + '&__function=resizer&img=' + encodeURIComponent(document.getElementById('f_url').value) + '&width=' + param.f_width + '&height=' + param.f_height);
      // alert(resized);
      resized = eval(resized);
      if(resized)
      {
        param.f_url = makeURL(base_url, resized);
      }
    }


		__dlg_close(param);
		return false;
	}

	//similar to the Files::makeFile() in Files.php
	function makeURL(pathA, pathB) 
	{
		if(pathA.substring(pathA.length-1) != '/')
			pathA += '/';

		if(pathB.charAt(0) == '/');	
			pathB = pathB.substring(1);

		return pathA+pathB;
	}


	function updateDir(selection) 
	{
		var newDir = selection.options[selection.selectedIndex].value;
		changeDir(newDir);
	}

	function goUpDir() 
	{
		var selection = document.getElementById('dirPath');
		var currentDir = selection.options[selection.selectedIndex].text;
		if(currentDir.length < 2)
			return false;
		var dirs = currentDir.split('/');
		
		var search = '';

		for(var i = 0; i < dirs.length - 2; i++)
		{
			search += dirs[i]+'/';
		}

		for(var i = 0; i < selection.length; i++)
		{
			var thisDir = selection.options[i].text;
			if(thisDir == search)
			{
				selection.selectedIndex = i;
				var newDir = selection.options[i].value;
				changeDir(newDir);
				break;
			}
		}
	}

	function changeDir(newDir) 
	{
		if(typeof imgManager != 'undefined')
			imgManager.changeDir(newDir);
	}

	function toggleConstrains(constrains) 
	{
		var lockImage = document.getElementById('imgLock');
		var constrains = document.getElementById('constrain_prop');

		if(constrains.checked) 
		{
			lockImage.src = "img/locked.gif";	
			checkConstrains('width') 
		}
		else
		{
			lockImage.src = "img/unlocked.gif";	
		}
	}

	function checkConstrains(changed) 
	{
		//alert(document.form1.constrain_prop);
		var constrains = document.getElementById('constrain_prop');
		
		if(constrains.checked) 
		{
			var obj = document.getElementById('orginal_width');
			var orginal_width = parseInt(obj.value);
			var obj = document.getElementById('orginal_height');
			var orginal_height = parseInt(obj.value);

			var widthObj = document.getElementById('f_width');
			var heightObj = document.getElementById('f_height');
			
			var width = parseInt(widthObj.value);
			var height = parseInt(heightObj.value);

			if(orginal_width > 0 && orginal_height > 0) 
			{
				if(changed == 'width' && width > 0) {
					heightObj.value = parseInt((width/orginal_width)*orginal_height);
				}

				if(changed == 'height' && height > 0) {
					widthObj.value = parseInt((height/orginal_height)*orginal_width);
				}
			}			
		}
	}

	function showMessage(newMessage) 
	{
		var message = document.getElementById('message');
		var messages = document.getElementById('messages');
		if(message.firstChild)
			message.removeChild(message.firstChild);

		message.appendChild(document.createTextNode(i18n(newMessage)));
		
		messages.style.display = '';
	}

	function addEvent(obj, evType, fn)
	{ 
		if (obj.addEventListener) { obj.addEventListener(evType, fn, true); return true; } 
		else if (obj.attachEvent) {  var r = obj.attachEvent("on"+evType, fn);  return r;  } 
		else {  return false; } 
	} 

	function doUpload() 
	{
		
		var uploadForm = document.getElementById('uploadForm');
		if(uploadForm)
			showMessage('Uploading');
	}

	function refresh()
	{
		var selection = document.getElementById('dirPath');
		updateDir(selection);
	}


	function newFolder() 
	{
     var folder = prompt(i18n('Please enter name for new folder...'), i18n('Untitled'));
		var selection = document.getElementById('dirPath');
		var dir = selection.options[selection.selectedIndex].value;

				if(folder == thumbdir)
				{
					alert(i18n('Invalid folder name, please choose another folder name.'));
					return false;
				}

				if (folder && folder != '' && typeof imgManager != 'undefined') 
					imgManager.newFolder(dir, encodeURI(folder)); 
   }
	 addEvent(window, 'load', init);
