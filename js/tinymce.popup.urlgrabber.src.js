$(document).ready(function() {

    $("a.fileURL").click(function() {
        var URL = $(this).attr('href');
        var win = top.tinymce.activeEditor.windowManager.getParams().window;
        var inputId = top.tinymce.activeEditor.windowManager.getParams().input;
        
        // insert information now
        var ele = win.document.getElementById(inputId);
        ele.value = URL;
        
        // close popup window
        top.tinymce.activeEditor.windowManager.close();
        return false;
    });
});