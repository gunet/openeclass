@extends('layouts.default')

@section('content')

    <main id="main-section-content" class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class='row m-auto'>
                <div class='col-12 d-flex justify-content-center overflow-auto'>
                    <div id="pdf-all-canvas" class="pdf-pages w-auto card cardPanel">
                        <p id="pdf-viewer-status" class="p-3 mb-0" role="status">Loading PDF…</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="{{ $urlAppend }}js/build_pdf/pdf.min.js"></script>
    <style>
        #pdf-all-canvas.pdf-pages { display: flex; flex-direction: column; align-items: center; gap: 24px; padding: 24px; background: #e9ecef; }
        #pdf-all-canvas .pdf-page { max-width: 100%; background: #fff; box-shadow: 0 2px 8px rgb(0 0 0 / 20%); }
        #pdf-all-canvas .pdf-page canvas { display: block; max-width: 100%; height: auto; }
    </style>

    <script>
        (async () => {
            const container = document.getElementById("pdf-all-canvas");
            const status = document.getElementById("pdf-viewer-status");

            try {
                pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ $urlAppend }}js/build_pdf/pdf.worker.min.js";
                const pdf = await pdfjsLib.getDocument(@json($url)).promise;
                status.remove();

                // Sequential rendering keeps pages in document order.
                for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                    const page = await pdf.getPage(pageNumber);
                    const viewport = page.getViewport({ scale: 1.5 });
                    const pageContainer = document.createElement("div");
                    pageContainer.className = "pdf-page";
                    pageContainer.setAttribute("role", "img");
                    pageContainer.setAttribute("aria-label", `PDF page ${pageNumber}`);

                    const canvas = document.createElement("canvas");
                    const context = canvas.getContext("2d");
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    pageContainer.appendChild(canvas);
                    container.appendChild(pageContainer);
                    await page.render({ canvasContext: context, viewport }).promise;
                }
            } catch (error) {
                console.error("Unable to display PDF:", error);
                status.textContent = "The PDF could not be loaded.";
                status.classList.add("text-danger");
                if (!status.isConnected) {
                    container.prepend(status);
                }
            }
        })();
    </script>

    
    <script>  
        var noPrint = true;
        var noCopy = true;
        var noScreenshot = true;
        var autoBlur = true;
    </script>
    <script type="text/javascript" src="{{ $urlAppend }}js/noscript/noscript.js"></script>

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
            if (e.ctrlKey && (e.key === 'PrintScreen' || e.key === 'F12' || e.key === 'u')) {
                $("#main-section-content").hide();
                e.cancelBubble = true;
                e.preventDefault();
                e.stopImmediatePropagation();
            }
        });
    </script>

    <script type="text/javascript" src="{{ $urlAppend }}js/shortcut/shortcut.min.js"></script>
    <script>
        shortcut("F12",function() {
            $("#main-section-content").hide();
        });
        shortcut("Ctrl+Shift+C",function() {
            $("#main-section-content").hide();
        });
        shortcut("Ctrl+Shift+I",function() {
            $("#main-section-content").hide();
        });
        shortcut("Ctrl+Shift+J",function() {
            $("#main-section-content").hide();
        });
        shortcut("Ctrl+Shift+U",function() {
            $("#main-section-content").hide();
        });
        shortcut("Command+Option+I",function() {
            $("#main-section-content").hide();
        });
        shortcut("Command+Shift+C",function() {
            $("#main-section-content").hide();
        });
    </script>
@endsection
