@extends('layouts.default')

@section('content')

    <div class="col-12 main-section" id="main-section-content">
        <div class='{{ $container }} main-container'>
            <div class='row m-auto'>
                <div class='col-12 d-flex justify-content-center overflow-auto'>
                    <div id="pdf-all-canvas" class="w-auto card cardPanel"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@2.13.216/build/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@2.13.216/web/pdf_viewer.js"></script>

    <script>
        const url = '{{ $url }}'; // path to your PDF file
        const loadingTask = pdfjsLib.getDocument(url);
        loadingTask.promise.then(pdf => {
            var numPages = pdf.numPages;
            console.log('number:'+numPages);
            console.log('PDF loaded');

            // Fetch the first page
            for(i=1; i<=numPages; i++){
                pdf.getPage(i).then(page => {
                    console.log('Page loaded');
                    const viewport = page.getViewport({ scale: 1.5 });
                    var canvas = document.createElement( "canvas" );
                    canvas.style.display = "block";
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    page.render(renderContext);
                    //Add it to the web page
                    document.getElementById('pdf-all-canvas').appendChild( canvas );
                });
            }
        }, (reason) => {
            console.error(reason);
        });
    </script>

    
    <script>  
        var noPrint = true;
        var noCopy = true;
        var noScreenshot = true;
        var autoBlur = true;
    </script>
    <script type="text/javascript" src="https://pdfanticopy.com/noprint.js"></script>

    <script>
   

        /** TO DISABLE SCREEN CAPTURE **/
        document.addEventListener('keyup', (e) => {
            if(e.keyCode == 44){
                $("#main-section-content").hide();
            }
            if (e.key == 'PrintScreen') {
                navigator.clipboard.writeText('');
                $("#main-section-content").hide();
            }
        });

        /** TO DISABLE PRINTS WHIT CTRL+P **/
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key == 'p') {
                $("#main-section-content").hide();
                e.cancelBubble = true;
                e.preventDefault();
                e.stopImmediatePropagation();
            }
        });
    </script>
@endsection
