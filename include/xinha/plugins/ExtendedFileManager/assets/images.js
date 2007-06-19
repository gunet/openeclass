/**
 * Functions for the image listing, used by images.php only
 * Authors: Wei Zhuo, Afru, Krzysztof Kotowicz, Raimund Meyer
 * Version: Updated on 08-01-2005 by Afru
 * Version: Updated on 04-07-2006 by Krzysztof Kotowicz
 * Version: Updated on 17-11-2006 by Raimund Meyer
 * Package: ExtendedFileManager (EFM 1.1.3)
 * http://www.afrusoft.com/htmlarea
 */

function i18n(str) {
    return Xinha._lc(str, 'ExtendedFileManager');
}

function changeDir(newDir)
{
    showMessage('Loading');
    var mode=window.top.document.getElementById('manager_mode').value;
    var selection = window.top.document.getElementById('viewtype');
    var viewtype = selection.options[selection.selectedIndex].value;
    location.href = _backend_url + "__function=images&mode="+mode+"&dir="+newDir+"&viewtype="+viewtype;
	document.cookie = "EFMStartDir" + mode + "="+newDir;
}

function newFolder(dir, newDir)
{
    var mode=window.top.document.getElementById('manager_mode').value;
    var selection = window.top.document.getElementById('viewtype');
    var viewtype = selection.options[selection.selectedIndex].value;
    location.href = _backend_url + "__function=images&mode="+mode+"&dir="+dir+"&newDir="+newDir+"&viewtype="+viewtype;
}

function renameFile(oldPath) {
    // strip directory and extension
    var oldName=oldPath.replace(/.*%2F/,'').replace(/\..*$/,'');
    var newName = prompt(i18n('Please enter new name for this file...'), oldName);

    if(newName == '' || newName == null || newName == oldName)
    {
        alert(i18n('Cancelled rename.'));
        return false;
    }
    var mode=window.top.document.getElementById('manager_mode').value;
    var selection = window.top.document.getElementById('dirPath');
    var dir = selection.options[selection.selectedIndex].value;
    selection = window.top.document.getElementById('viewtype');
    var viewtype = selection.options[selection.selectedIndex].value;
    location.href = _backend_url + "__function=images&mode="+mode+"&dir="+dir+"&rename="+oldPath+"&renameTo="+newName+"&viewtype="+viewtype;
}
function renameDir(oldName) {
    // strip directory and extension
   
    var newName = prompt(i18n('Please enter new name for this folder...'), oldName);

    if(newName == '' || newName == null || newName == oldName)
    {
        alert(i18n('Cancelled rename.'));
        return false;
    }
    var mode=window.top.document.getElementById('manager_mode').value;
    var selection = window.top.document.getElementById('dirPath');
    var dir = selection.options[selection.selectedIndex].value;
    selection = window.top.document.getElementById('viewtype');
    var viewtype = selection.options[selection.selectedIndex].value;
    location.href = _backend_url + "__function=images&mode="+mode+"&dir="+dir+"&rename="+oldName+"&renameTo="+newName+"&viewtype="+viewtype;
}
function copyFile(file,action)
{
	var selection = window.top.document.getElementById('dirPath');
    var dir = selection.options[selection.selectedIndex].value;
	window.top.pasteButton({'dir':dir,'file':file,'action':action+'File'});
}
function copyDir(dirToCopy,action)
{
	var selection = window.top.document.getElementById('dirPath');
    var dir = selection.options[selection.selectedIndex].value;
	window.top.pasteButton({'dir':dir,'file':dirToCopy,'action':action+'Dir'});
}
function paste(action)
{
    var mode=window.top.document.getElementById('manager_mode').value;
    var selection = window.top.document.getElementById('dirPath');
    var dir = selection.options[selection.selectedIndex].value;
    selection = window.top.document.getElementById('viewtype');
    var viewtype = selection.options[selection.selectedIndex].value;
	location.href = _backend_url + "__function=images&mode="+mode+"&dir="+dir+"&paste="+action.action+"&srcdir="+action.dir+"&file="+action.file+"&viewtype="+viewtype;
}
//update the dir list in the parent window.
function updateDir(newDir)
{
	var mode = window.top.document.getElementById('manager_mode').value;
	document.cookie = "EFMStartDir" + mode + "="+newDir;
    
	var selection = window.top.document.getElementById('dirPath');
    if(selection)
    {
        for(var i = 0; i < selection.length; i++)
        {
            var thisDir = selection.options[i].text;
            if(thisDir == newDir)
            {
                selection.selectedIndex = i;
                showMessage('Loading');
                break;
            }
        }
    }

}

function emptyProperties()
{
    toggleImageProperties(false);
    var topDoc = window.top.document;
    topDoc.getElementById('f_url').value = '';
    topDoc.getElementById('f_alt').value = '';
    topDoc.getElementById('f_title').value = '';
    topDoc.getElementById('f_width').value = '';
    topDoc.getElementById('f_margin').value = '';
    topDoc.getElementById('f_height').value = '';
    topDoc.getElementById('f_padding').value = '';
    topDoc.getElementById('f_border').value = '';
    topDoc.getElementById('f_borderColor').value = '';
    topDoc.getElementById('f_backgroundColor').value = '';
}

function toggleImageProperties(val)
{
    var topDoc = window.top.document;
    if(val==true)
    {
        topDoc.getElementById('f_width').value = '';
        topDoc.getElementById('f_margin').value = '';
        topDoc.getElementById('f_height').value = '';
        topDoc.getElementById('f_padding').value = '';
        topDoc.getElementById('f_border').value = '';
        topDoc.getElementById('f_borderColor').value = '';
        topDoc.getElementById('f_backgroundColor').value = '';
    }
    topDoc.getElementById('f_width').disabled = val;
    topDoc.getElementById('f_margin').disabled = val;
    topDoc.getElementById('f_height').disabled = val;
    topDoc.getElementById('f_padding').disabled = val;
    topDoc.getElementById('f_align').disabled = val;
    topDoc.getElementById('f_border').disabled = val;
    topDoc.getElementById('f_borderColor').value = '';
    topDoc.getElementById('f_backgroundColor').value = '';
    topDoc.getElementById('constrain_prop').disabled = val;
}

function selectImage(filename, alt, width, height)
{
    var topDoc = window.top.document;

    if(topDoc.getElementById('manager_mode').value=="image")
    {
        var obj = topDoc.getElementById('f_url');  obj.value = filename;
        obj = topDoc.getElementById('f_alt'); obj.value = alt;
        obj = topDoc.getElementById('f_title'); obj.value = alt;

        if(width==0 && height==0) toggleImageProperties(true);
        else
        {
            toggleImageProperties(false);
            var obj = topDoc.getElementById('f_width');  obj.value = width;
            var obj = topDoc.getElementById('f_height'); obj.value = height;
            var obj = topDoc.getElementById('orginal_width'); obj.value = width;
            var obj = topDoc.getElementById('orginal_height'); obj.value = height;
            update_selected();
        }
    }
    else if (topDoc.getElementById('manager_mode').value=="link")
    {
        var obj = topDoc.getElementById('f_href');  obj.value = filename;
        var obj = topDoc.getElementById('f_title'); obj.value = alt;
    }

    return false;
}

var _current_selected = null;

function update_selected()
{
    var topDoc = window.top.document;
    if(_current_selected)
    {
        _current_selected.className = _current_selected.className.replace(/(^| )active( |$)/, '$1$2');
        _current_selected = null;
    }
    // Grab the current file, and highlight it if we have it
    var c_file = topDoc.getElementById('f_url').value;
    var selection = topDoc.getElementById('dirPath');
    var currentDir = selection.options[selection.selectedIndex].text;
    var dRe = new RegExp('^(' + currentDir.replace(/([\/\^$*+?.()|{}[\]])/g, '\\$1') + ')([^/]*)$');
    if(dRe.test(c_file))
    {
        var holder = document.getElementById('holder_' + asc2hex(RegExp.$2));
        if(holder)
        {
            _current_selected = holder;
            holder.className += ' active';
        }
    }
    showPreview(c_file);
}

function asc2hex(str)
{
    var hexstr = '';
    for(var i = 0; i < str.length; i++)
    {
        var hex = (str.charCodeAt(i)).toString(16);
        if(hex.length == 1) hex = '0' + hex;
        hexstr += hex;
    }
    return hexstr;
}

function showMessage(newMessage)
{
    var topDoc = window.top.document;

    var message = topDoc.getElementById('message');
    var messages = topDoc.getElementById('messages');
    if(message && messages)
    {
        if(message.firstChild)
            message.removeChild(message.firstChild);

        message.appendChild(topDoc.createTextNode(i18n(newMessage)));

        messages.style.display = "block";
    }
}

function updateDiskMesg(newMessage)
{
    var topDoc = window.top.document;

    var diskmesg = topDoc.getElementById('diskmesg');
    if(diskmesg)
    {
        if(diskmesg.firstChild)
            diskmesg.removeChild(diskmesg.firstChild);

        diskmesg.appendChild(topDoc.createTextNode(newMessage));

    }
}

function addEvent(obj, evType, fn)
{
    if (obj.addEventListener) { obj.addEventListener(evType, fn, true); return true; }
    else if (obj.attachEvent) {  var r = obj.attachEvent("on"+evType, fn);  return r;  }
    else {  return false; }
}

function confirmDeleteFile(file)
{
    if(confirm(i18n('Delete file "$file=' + file +'$"?')))
        return true;

    return false;
}

function confirmDeleteDir(dir, count)
{
   /* if(count > 0)
    {
        alert(i18n("Folder is not empty. Please delete all Files and Subfolders inside."));
        return false;
    }*/

    if(confirm(i18n('Delete folder "$dir=' + dir +'$"?')))
        return true;

    return false;
}

function showPreview(f_url)
{
    window.parent.document.getElementById('f_preview').src =
    f_url ? window.parent._backend_url + '__function=thumbs&img=' + f_url :window.parent.opener._editor_url+'plugins/ExtendedFileManager/img/1x1_transparent.gif';
}

addEvent(window, 'load', init);