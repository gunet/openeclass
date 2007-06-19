function FullScreen(editor, args)
{
  this.editor = editor;
  editor._superclean_on = false;
  cfg = editor.config;

  cfg.registerButton
  ( 'fullscreen',
    this._lc("Maximize/Minimize Editor"),
    [_editor_url + cfg.imgURL + 'ed_buttons_main.gif',8,0], true,
      function(e, objname, obj)
      {
        e._fullScreen();
      }
  );

  // See if we can find 'popupeditor' and replace it with fullscreen
  cfg.addToolbarElement("fullscreen", "popupeditor", 0);
}

FullScreen._pluginInfo =
{
  name     : "FullScreen",
  version  : "1.0",
  developer: "James Sleeman",
  developer_url: "http://www.gogo.co.nz/",
  c_owner      : "Gogo Internet Services",
  license      : "htmlArea",
  sponsor      : "Gogo Internet Services",
  sponsor_url  : "http://www.gogo.co.nz/"
};

FullScreen.prototype._lc = function(string) {
    return Xinha._lc(string, {url : _editor_url + 'modules/FullScreen/lang/',context:"FullScreen"});
};

/** fullScreen makes an editor take up the full window space (and resizes when the browser is resized)
 *  the principle is the same as the "popupwindow" functionality in the original htmlArea, except
 *  this one doesn't popup a window (it just uses to positioning hackery) so it's much more reliable
 *  and much faster to switch between
 */

Xinha.prototype._fullScreen = function()
{
  var e = this;
  function sizeItUp()
  {
    if(!e._isFullScreen || e._sizing) return false;
    e._sizing = true;
    // Width & Height of window
    var dim = Xinha.viewportSize();

    var h = dim.y - e.config.fullScreenMargins[0] -  e.config.fullScreenMargins[2];
    var w = dim.x - e.config.fullScreenMargins[1] -  e.config.fullScreenMargins[3];

    e.sizeEditor(w + 'px', h + 'px',true,true);
    e._sizing = false;
    if ( e._toolbarObjects.fullscreen ) e._toolbarObjects.fullscreen.swapImage([_editor_url + cfg.imgURL + 'ed_buttons_main.gif',9,0]); 
  }

  function sizeItDown()
  {
    if(e._isFullScreen || e._sizing) return false;
    e._sizing = true;
    e.initSize();
    e._sizing = false;
    if ( e._toolbarObjects.fullscreen ) e._toolbarObjects.fullscreen.swapImage([_editor_url + cfg.imgURL + 'ed_buttons_main.gif',8,0]); 
  }

  /** It's not possible to reliably get scroll events, particularly when we are hiding the scrollbars
   *   so we just reset the scroll ever so often while in fullscreen mode
   */
  function resetScroll()
  {
    if(e._isFullScreen)
    {
      window.scroll(0,0);
      window.setTimeout(resetScroll,150);
    }
  }

  if(typeof this._isFullScreen == 'undefined')
  {
    this._isFullScreen = false;
    if(e.target != e._iframe)
    {
      Xinha._addEvent(window, 'resize', sizeItUp);
    }
  }

  // Gecko has a bug where if you change position/display on a
  // designMode iframe that designMode dies.
  if(Xinha.is_gecko)
  {
    this.deactivateEditor();
  }

  if(this._isFullScreen)
  {
    // Unmaximize
    this._htmlArea.style.position = '';
    if (!Xinha.is_ie ) this._htmlArea.style.border   = '';

    try
    {
      if(Xinha.is_ie && document.compatMode == 'CSS1Compat')
      {
        var bod = document.getElementsByTagName('html');
      }
      else
      {
        var bod = document.getElementsByTagName('body');
      }
      bod[0].style.overflow='';
    }
    catch(e)
    {
      // Nutthin
    }
    this._isFullScreen = false;
    sizeItDown();

    // Restore all ancestor positions
    var ancestor = this._htmlArea;
    while((ancestor = ancestor.parentNode) && ancestor.style)
    {
      ancestor.style.position = ancestor._xinha_fullScreenOldPosition;
      ancestor._xinha_fullScreenOldPosition = null;
    }
    
    if ( Xinha.ie_version < 7 )
    {
      var selects = document.getElementsByTagName("select");
      for ( var i=0;i<selects.length;++i )
      {
        selects[i].style.visibility = 'visible';
      }
    }
    window.scroll(this._unScroll.x, this._unScroll.y);
  }
  else
  {

    // Get the current Scroll Positions
    this._unScroll =
    {
     x:(window.pageXOffset)?(window.pageXOffset):(document.documentElement)?document.documentElement.scrollLeft:document.body.scrollLeft,
     y:(window.pageYOffset)?(window.pageYOffset):(document.documentElement)?document.documentElement.scrollTop:document.body.scrollTop
    };


    // Make all ancestors position = static
    var ancestor = this._htmlArea;
    while((ancestor = ancestor.parentNode) && ancestor.style)
    {
      ancestor._xinha_fullScreenOldPosition = ancestor.style.position;
      ancestor.style.position = 'static';
    }
    // very ugly bug in IE < 7 shows select boxes through elements that are positioned over them
    if ( Xinha.ie_version < 7 )
    {
      var selects = document.getElementsByTagName("select");
      var s, currentEditor;
      for ( var i=0;i<selects.length;++i )
      {
        s = selects[i];
        currentEditor = false;
        while ( s = s.parentNode )
        {
          if ( s == this._htmlArea )
          {
            currentEditor = true;
            break;
          }
        }
        if ( !currentEditor && selects[i].style.visibility != 'hidden')
        {
          selects[i].style.visibility = 'hidden';
        }
      }
    }
    // Maximize
    window.scroll(0,0);
    this._htmlArea.style.position = 'absolute';
    this._htmlArea.style.zIndex   = 999;
    this._htmlArea.style.left     = e.config.fullScreenMargins[3] + 'px';
    this._htmlArea.style.top      = e.config.fullScreenMargins[0] + 'px';
    if ( !Xinha.is_ie ) this._htmlArea.style.border   = 'none';
    this._isFullScreen = true;
    resetScroll();

    try
    {
      if(Xinha.is_ie && document.compatMode == 'CSS1Compat')
      {
        var bod = document.getElementsByTagName('html');
      }
      else
      {
        var bod = document.getElementsByTagName('body');
      }
      bod[0].style.overflow='hidden';
    }
    catch(e)
    {
      // Nutthin
    }

    sizeItUp();
  }

  if(Xinha.is_gecko)
  {
    this.activateEditor();
  }
  this.focusEditor();
};