$(document).ready(function() {
    $("a.fileURL").on('click', function(e) {
        e.preventDefault();

        var fileURL = $(this).attr('href');

        window.parent.postMessage({
            mceAction: 'fileSelected',
            url: fileURL,
            title: $(this).text() || ''
        }, '*');

        return false;
    });
});