/** htmlArea - James' Fork - Linker Plugin **/
Linker._pluginInfo =
{
  name     : "Linker",
  version  : "1.0",
  developer: "James Sleeman",
  developer_url: "http://www.gogo.co.nz/",
  c_owner      : "Gogo Internet Services",
  license      : "htmlArea",
  sponsor      : "Gogo Internet Services",
  sponsor_url  : "http://www.gogo.co.nz/"
};

Xinha.loadStyle('dTree/dtree.css', 'Linker');

Xinha.Config.prototype.Linker =
{
  'treeCaption' : document.location.host,
  'backend' : _editor_url + 'plugins/Linker/scan.php',
  'backend_data' : null,
  'files' : null
};


function Linker(editor, args)
{
  this.editor  = editor;
  this.lConfig = editor.config.Linker;

  var linker = this;
  if(editor.config.btnList.createlink)
  {
    editor.config.btnList.createlink[3]
      =  function(e, objname, obj) { linker._createLink(linker._getSelectedAnchor()); };
  }
  else
  {
    editor.config.registerButton(
                                 'createlink', 'Insert/Modify Hyperlink', [_editor_url + "images/ed_buttons_main.gif",6,1], false,
                                 function(e, objname, obj) { linker._createLink(linker._getSelectedAnchor()); }
                                 );
  }

  // See if we can find 'createlink'
 editor.config.addToolbarElement("createlink", "createlink", 0);
}

Linker.prototype._lc = function(string)
{
  return Xinha._lc(string, 'Linker');
};

Linker.prototype._createLink = function(a)
{
  if(!a && this.editor.selectionEmpty(this.editor.getSelection()))
  {       
    alert(this._lc("You must select some text before making a new link."));
    return false;
  }

  var inputs =
  {
    type:     'url',
    href:     'http://www.example.com/',
    target:   '',
    p_width:  '',
    p_height: '',
    p_options: ['menubar=no','toolbar=yes','location=no','status=no','scrollbars=yes','resizeable=yes'],
    to:       'alice@example.com',
    subject:  '',
    body:     '',
    anchor:   ''
  };

  if(a && a.tagName.toLowerCase() == 'a')
  {
    var href =this.editor.fixRelativeLinks(a.getAttribute('href'));
    var m = href.match(/^mailto:(.*@[^?&]*)(\?(.*))?$/);
    var anchor = href.match(/^#(.*)$/);

    if(m)
    {
      // Mailto
      inputs.type = 'mailto';
      inputs.to = m[1];
      if(m[3])
      {
        var args  = m[3].split('&');
        for(var x = 0; x<args.length; x++)
        {
          var j = args[x].match(/(subject|body)=(.*)/);
          if(j)
          {
            inputs[j[1]] = decodeURIComponent(j[2]);
          }
        }
      }
    }
    else if (anchor)
    {
      //Anchor-Link
      inputs.type = 'anchor';
      inputs.anchor = anchor[1];
      
    }
    else
    {


      if(a.getAttribute('onclick'))
      {
        var m = a.getAttribute('onclick').match(/window\.open\(\s*this\.href\s*,\s*'([a-z0-9_]*)'\s*,\s*'([a-z0-9_=,]*)'\s*\)/i);

        // Popup Window
        inputs.href   = href ? href : '';
        inputs.target = 'popup';
        inputs.p_name = m[1];
        inputs.p_options = [ ];


        var args = m[2].split(',');
        for(var x = 0; x < args.length; x++)
        {
          var i = args[x].match(/(width|height)=([0-9]+)/);
          if(i)
          {
            inputs['p_' + i[1]] = parseInt(i[2]);
          }
          else
          {
            inputs.p_options.push(args[x]);
          }
        }
      }
      else
      {
        // Normal
        inputs.href   = href;
        inputs.target = a.target;
      }
    }
  }

  var linker = this;

  // If we are not editing a link, then we need to insert links now using execCommand
  // because for some reason IE is losing the selection between now and when doOK is
  // complete.  I guess because we are defocusing the iframe when we click stuff in the
  // linker dialog.

  this.a = a; // Why doesn't a get into the closure below, but if I set it as a property then it's fine?

  var doOK = function()
  {
    //if(linker.a) alert(linker.a.tagName);
    var a = linker.a;

    var values = linker._dialog.hide();
    var atr =
    {
      href: '',
      target:'',
      title:'',
      onclick:''
    };

    if(values.type == 'url')
    {
     if(values.href)
     {
       atr.href = values.href;
       atr.target = values.target;
       if(values.target == 'popup')
       {

         if(values.p_width)
         {
           values.p_options.push('width=' + values.p_width);
         }
         if(values.p_height)
         {
           values.p_options.push('height=' + values.p_height);
         }
         atr.onclick = 'try{if(document.designMode && document.designMode == \'on\') return false;}catch(e){} window.open(this.href, \'' + (values.p_name.replace(/[^a-z0-9_]/i, '_')) + '\', \'' + values.p_options.join(',') + '\');return false;';
       }
     }
    }
    else if(values.type == 'anchor')
    {
      if(values.anchor)
      {
        atr.href = values.anchor.value;
      }
    }
    else
    {
      if(values.to)
      {
        atr.href = 'mailto:' + values.to;
        if(values.subject) atr.href += '?subject=' + encodeURIComponent(values.subject);
        if(values.body)    atr.href += (values.subject ? '&' : '?') + 'body=' + encodeURIComponent(values.body);
      }
    }

    if(a && a.tagName.toLowerCase() == 'a')
    {
      if(!atr.href)
      {
        if(confirm(linker._dialog._lc('Are you sure you wish to remove this link?')))
        {
          var p = a.parentNode;
          while(a.hasChildNodes())
          {
            p.insertBefore(a.removeChild(a.childNodes[0]), a);
          }
          p.removeChild(a);
          linker.editor.updateToolbar();
          return;
        }
      }
      else
      {
        // Update the link
        for(var i in atr)
        {
          a.setAttribute(i, atr[i]);
        }
        
        // If we change a mailto link in IE for some hitherto unknown
        // reason it sets the innerHTML of the link to be the 
        // href of the link.  Stupid IE.
        if(Xinha.is_ie)
        {
          if(/mailto:([^?<>]*)(\?[^<]*)?$/i.test(a.innerHTML))
          {
            a.innerHTML = RegExp.$1;
          }
        }
      }
    }
    else
    {
      if(!atr.href) return true;

      // Insert a link, we let the browser do this, we figure it knows best
      var tmp = Xinha.uniq('http://www.example.com/Link');
      linker.editor._doc.execCommand('createlink', false, tmp);

      // Fix them up
      var anchors = linker.editor._doc.getElementsByTagName('a');
      for(var i = 0; i < anchors.length; i++)
      {
        var anchor = anchors[i];
        if(anchor.href == tmp)
        {
          // Found one.
          if (!a) a = anchor;
          for(var j in atr)
          {
            anchor.setAttribute(j, atr[j]);
          }
        }
      }
    }
    linker.editor.selectNodeContents(a);
    linker.editor.updateToolbar();
  };

  this._dialog.show(inputs, doOK);

};

Linker.prototype._getSelectedAnchor = function()
{
  var sel  = this.editor.getSelection();
  var rng  = this.editor.createRange(sel);
  var a    = this.editor.activeElement(sel);
  if(a != null && a.tagName.toLowerCase() == 'a')
  {
    return a;
  }
  else
  {
    a = this.editor._getFirstAncestor(sel, 'a');
    if(a != null)
    {
      return a;
    }
  }
  return null;
};

Linker.prototype.onGenerateOnce = function()
{
  this._dialog = new Linker.Dialog(this);
};
// Inline Dialog for Linker

Linker.Dialog_dTrees = [ ];


Linker.Dialog = function (linker)
{
  var  lDialog = this;
  this.Dialog_nxtid = 0;
  this.linker = linker;
  this.id = { }; // This will be filled below with a replace, nifty

  this.ready = false;
  this.files  = false;
  this.html   = false;
  this.dialog = false;

  // load the dTree script
  this._prepareDialog();

};

Linker.Dialog.prototype._prepareDialog = function()
{
  var lDialog = this;
  var linker = this.linker;

  // We load some stuff up int he background, recalling this function
  // when they have loaded.  This is to keep the editor responsive while
  // we prepare the dialog.
  if(typeof dTree == 'undefined')
  {
    Xinha._loadback(_editor_url + 'plugins/Linker/dTree/dtree.js',
                       function() {lDialog._prepareDialog(); }
                      );
    return;
  }

  if(this.files == false)
  {
    if(linker.lConfig.backend)
    {
        //get files from backend
        Xinha._postback(linker.lConfig.backend,
                          linker.lConfig.backend_data,
                          function(txt) {
                            try {
                                lDialog.files = eval(txt);
                            } catch(Error) {
                                lDialog.files = [ {url:'',title:Error.toString()} ];
                            }
                            lDialog._prepareDialog(); });
    }
    else if(linker.lConfig.files != null)
    {
        //get files from plugin-config
        lDialog.files = linker.lConfig.files;
        lDialog._prepareDialog();
    }
    return;
  }
  var files = this.files;

  if(this.html == false)
  {
    Xinha._getback(_editor_url + 'plugins/Linker/dialog.html', function(txt) { lDialog.html = txt; lDialog._prepareDialog(); });
    return;
  }
  var html = this.html;

  // Now we have everything we need, so we can build the dialog.
  var dialog = this.dialog = new Xinha.Dialog(linker.editor, this.html, 'Linker');
  var dTreeName = Xinha.uniq('dTree_');

  this.dTree = new dTree(dTreeName, _editor_url + 'plugins/Linker/dTree/');
  eval(dTreeName + ' = this.dTree');

  this.dTree.add(this.Dialog_nxtid++, -1, linker.lConfig.treeCaption , null, linker.lConfig.treeCaption);
  this.makeNodes(files, 0);

  // Put it in
  var ddTree = this.dialog.getElementById('dTree');
  //ddTree.innerHTML = this.dTree.toString();
  ddTree.innerHTML = '';
  ddTree.style.position = 'absolute';
  ddTree.style.left = 1 + 'px';
  ddTree.style.top =  0 + 'px';
  ddTree.style.overflow = 'auto';
  ddTree.style.backgroundColor = 'white';
  this.ddTree = ddTree;
  this.dTree._linker_premade = this.dTree.toString();

  var options = this.dialog.getElementById('options');
  options.style.position = 'absolute';
  options.style.top      = 0   + 'px';
  options.style.right    = 0   + 'px';
  options.style.width    = 320 + 'px';
  options.style.overflow = 'auto';

  // Hookup the resizer
  this.dialog.onresize = function()
    {
      var h = parseInt(dialog.height) - dialog.getElementById('h1').offsetHeight;
      var w = parseInt(dialog.width)  - 322 ;
      // An error is thrown with IE when trying to set a negative width or a negative height
      // But perhaps a width / height of 0 is not the minimum required we need to set
      if (w<0) w = 0;
      if (h<0) h = 0;
      options.style.height = ddTree.style.height = h + 'px';
      ddTree.style.width  = w + 'px';
    }

  this.ready = true;
};

Linker.Dialog.prototype.makeNodes = function(files, parent)
{
  for(var i = 0; i < files.length; i++)
  {
    if(typeof files[i] == 'string')
    {
      this.dTree.add(Linker.nxtid++, parent,
                     files[i].replace(/^.*\//, ''),
                     'javascript:document.getElementsByName(\'' + this.dialog.id.href + '\')[0].value=decodeURIComponent(\'' + encodeURIComponent(files[i]) + '\');document.getElementsByName(\'' + this.dialog.id.type + '\')[0].click();document.getElementsByName(\'' + this.dialog.id.href + '\')[0].focus();void(0);',
                     files[i]);
    }
    else if(files[i].length)
    {
      var id = this.Dialog_nxtid++;
      this.dTree.add(id, parent, files[i][0].replace(/^.*\//, ''), null, files[i][0]);
      this.makeNodes(files[i][1], id);
    }
    else if(typeof files[i] == 'object')
    {
      if(files[i].children) {
        var id = this.Dialog_nxtid++;
      } else {
        var id = Linker.nxtid++;
      }

      if(files[i].title) var title = files[i].title;
      else if(files[i].url) var title = files[i].url.replace(/^.*\//, '');
      else var title = "no title defined";
      if(files[i].url) var link = 'javascript:document.getElementsByName(\'' + this.dialog.id.href + '\')[0].value=decodeURIComponent(\'' + encodeURIComponent(files[i].url) + '\');document.getElementsByName(\'' + this.dialog.id.type + '\')[0].click();document.getElementsByName(\'' + this.dialog.id.href + '\')[0].focus();void(0);';
      else var link = '';
      
      this.dTree.add(id, parent, title, link, title);
      if(files[i].children) {
        this.makeNodes(files[i].children, id);
      }
    }
  }
};

Linker.Dialog.prototype._lc = Linker.prototype._lc;

Linker.Dialog.prototype.show = function(inputs, ok, cancel)
{
  if(!this.ready)
  {
    var lDialog = this;
    window.setTimeout(function() {lDialog.show(inputs,ok,cancel);},100);
    return;
  }

  if(this.ddTree.innerHTML == '')
  {
    this.ddTree.innerHTML = this.dTree._linker_premade;
  }

  if(inputs.type=='url')
  {
    this.dialog.getElementById('urltable').style.display = '';
    this.dialog.getElementById('mailtable').style.display = 'none';
    this.dialog.getElementById('anchortable').style.display = 'none';
  }
  else if(inputs.type=='anchor')
  {
    this.dialog.getElementById('urltable').style.display = 'none';
    this.dialog.getElementById('mailtable').style.display = 'none';
    this.dialog.getElementById('anchortable').style.display = '';
  }
  else
  {
    this.dialog.getElementById('urltable').style.display = 'none';
    this.dialog.getElementById('mailtable').style.display = '';
    this.dialog.getElementById('anchortable').style.display = 'none';
  }

  if(inputs.target=='popup')
  {
    this.dialog.getElementById('popuptable').style.display = '';
  }
  else
  {
    this.dialog.getElementById('popuptable').style.display = 'none';
  }
  
  var anchor = this.dialog.getElementById('anchor');
  for(var i=anchor.length;i>=0;i--) {
    anchor[i] = null;
  }

  var html = this.linker.editor.getHTML();  
  var anchors = new Array();

  var m = html.match(/<a[^>]+name="([^"]+)"/gi);
  if(m)
  {
    for(i=0;i<m.length;i++)
    {
        var n = m[i].match(/name="([^"]+)"/i);
        if(!anchors.contains(n[1])) anchors.push(n[1]);
    }
  }
  m = html.match(/id="([^"]+)"/gi);
  if(m)
  {
    for(i=0;i<m.length;i++)
    {
        n = m[i].match(/id="([^"]+)"/i);
        if(!anchors.contains(n[1])) anchors.push(n[1]);
    }
  }
  
  for(i=0;i<anchors.length;i++)
  {
    var opt = new Option(anchors[i],'#'+anchors[i],false,(inputs.anchor == anchors[i]));
    anchor[anchor.length] = opt;
  }

  //if no anchors found completely hide Anchor-Link
  if(anchor.length==0) {
    this.dialog.getElementById('anchorfieldset').style.display = "none";
  }
  
  // if we're not editing an existing link, hide the remove link button
  if (inputs.href == 'http://www.example.com/' && inputs.to == 'alice@example.com') { 
    this.dialog.getElementById('clear').style.display = "none";
  }
  else {
    this.dialog.getElementById('clear').style.display = "";
  }
  // Connect the OK and Cancel buttons
  var dialog = this.dialog;
  var lDialog = this;
  if(ok)
  {
    this.dialog.getElementById('ok').onclick = ok;
  }
  else
  {
    this.dialog.getElementById('ok').onclick = function() {lDialog.hide();};
  }

  if(cancel)
  {
    this.dialog.getElementById('cancel').onclick = cancel;
  }
  else
  {
    this.dialog.getElementById('cancel').onclick = function() { lDialog.hide()};
  }

  // Show the dialog
  this.linker.editor.disableToolbar(['fullscreen','linker']);

  this.dialog.show(inputs);

  // Init the sizes
  this.dialog.onresize();
};

Linker.Dialog.prototype.hide = function()
{
  this.linker.editor.enableToolbar();
  return this.dialog.hide();
};