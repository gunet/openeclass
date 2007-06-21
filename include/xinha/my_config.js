xinha_editors = null;
xinha_init    = null;
xinha_config  = null;
xinha_plugins = null;

// This contains the names of textareas we will make into Xinha editors
xinha_init = xinha_init ? xinha_init : function()
{
	/** STEP 1 ***************************************************************
	 * First, what are the plugins you will be using in the editors on this
	 * page.  List all the plugins you will need, even if not all the editors
	 * will use all the plugins.
	 *
	 * The list of plugins below is a good starting point, but if you prefer
	 * a must simpler editor to start with then you can use the following 
	 * 
	 * xinha_plugins = xinha_plugins ? xinha_plugins : [ ];
	 *
	 * which will load no extra plugins at all.
	 ************************************************************************/

	xinha_plugins = xinha_plugins ? xinha_plugins :
	[
	 'ListType',
	 'Stylist',
	 'DynamicCSS',
	 'SuperClean'
	];

	// THIS BIT OF JAVASCRIPT LOADS THE PLUGINS, NO TOUCHING  :)
	if(!Xinha.loadPlugins(xinha_plugins, xinha_init)) return;

	/** STEP 2 ***************************************************************
	 * Now, what are the names of the textareas you will be turning into
	 * editors?
	 ************************************************************************/

	xinha_editors = xinha_editors ? xinha_editors :
	[
		'xinha'
	];

	/** STEP 3 ***************************************************************
	 * We create a default configuration to be used by all the editors.
	 * If you wish to configure some of the editors differently this will be
	 * done in step 5.
	 *
	 * If you want to modify the default config you might do something like this.
	 *
	 *   xinha_config = new Xinha.Config();
	 *   xinha_config.width  = '640px';
	 *   xinha_config.height = '420px';
	 *
	 *************************************************************************/

	 xinha_config = xinha_config ? xinha_config() : new Xinha.Config();


	 // Dimitris
	xinha_config.toolbar =
	[
			["separator","formatblock","bold","italic","underline","strikethrough"],
			["separator","forecolor","hilitecolor","textindicator"],
			["linebreak","separator","justifyleft","justifycenter","justifyright","justifyfull"],
			["separator","insertorderedlist","insertunorderedlist","outdent","indent"],
			["separator","inserthorizontalrule","createlink","inserttable"],
			["linebreak","separator","undo","redo","selectall"],
			["separator","killword", "removeformat", "htmlmode"]
	];

   xinha_config.formatblock =
  {
    "&mdash; Μορφοποίηση &mdash;": "",
    "Τίτλος"            : "h3",
    "Υπότιτλος"         : "h4",
    "Έντονος Υπότιτλος" : "h5",
    "Σημαντικό Κείμενο" : "h6",
    "Απλή Παράγραφος"   : "p",
    "Formatted"         : "pre"
  };
	 	

	/** STEP 4 ***************************************************************
	 * We first create editors for the textareas.
	 *
	 * You can do this in two ways, either
	 *
	 *   xinha_editors   = Xinha.makeEditors(xinha_editors, xinha_config, xinha_plugins);
	 *
	 * if you want all the editor objects to use the same set of plugins, OR;
	 *
	 *   xinha_editors = Xinha.makeEditors(xinha_editors, xinha_config);
	 *   xinha_editors['myTextArea'].registerPlugins(['Stylist','FullScreen']);
	 *   xinha_editors['anotherOne'].registerPlugins(['CSS','SuperClean']);
	 *
	 * if you want to use a different set of plugins for one or more of the
	 * editors.
	 ************************************************************************/

	xinha_editors   = Xinha.makeEditors(xinha_editors, xinha_config, xinha_plugins);

	/** STEP 5 ***************************************************************
	 * If you want to change the configuration variables of any of the
	 * editors,  this is the place to do that, for example you might want to
	 * change the width and height of one of the editors, like this...
	 *
	 *   xinha_editors.myTextArea.config.width  = '640px';
	 *   xinha_editors.myTextArea.config.height = '480px';
	 *
	 ************************************************************************/

	/** STEP 6 ***************************************************************
	 * Finally we "start" the editors, this turns the textareas into
	 * Xinha editors.
	 ************************************************************************/

	Xinha.startEditors(xinha_editors);
}

Xinha._addEvent(window,'load', xinha_init); // this executes the xinha_init function on page load 
																						// and does not interfere with window.onload properties set by other scripts
