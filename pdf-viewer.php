<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>PDF Viewer in JavaScript</title>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<!--<link rel="stylesheet" href="viewer.css" />-->
        <script src="https://cdn.jsdelivr.net/npm/@panzoom/panzoom@4.3.2/dist/panzoom.min.js"></script>
        <style>
            body {
                background: #fff;
            }
            header {
                display: flex;
                justify-content: space-between;
                padding: 2px 0px;
            }
            .previous, .next {
                padding: 15px;
                font-size: 24px;
                color: #000;
            }
            .zoom {
                cursor: pointer;
                padding: 5px;
                font-size: 20px;
            }
            #canvas {
                direction: ltr;
                transform-origin: top left;
                /*max-width: 100%;*/
                @media screen and (max-width: 768px) {}
            }
            #div-container {
                width: 500px;
                height: 500px;
            }
        </style>
	</head>
	<body>
        <?php
            $datum = time(); // Date
            $kw = date("W", $datum); // KW
            $year = date("Y"); // Year
            $kw = (int)$kw; // Integer remove first 0

            //$kw = ++$kw; // 2023 eine KW verschoben

            if(date('D') == 'Mon' || date('D') == 'Tue' || date('D') == 'Wed') { 
                $kw = --$kw; // KW minus 1
            }
        ?>
		<header>
            <!-- Navigate to the Previous page -->
            <div>
                <a href="#" class="previous round" id="prev_page">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>

            <!-- Navigate to a specific page -->
            <input type="number" value="1" id="current_page" style="display:none;" />

            <!-- Page Info -->
            <div>
                <span id="page_num"></span>/<span id="page_count"></span>
            </div>

            <!-- Zoom In and Out -->
            <div style="display:flex;">
                <div class="zoom" id="zoom_in">
                    <i class="fas fa-search-plus"></i>
                </div>

                <div class="zoom" id="zoom_out">
                    <i class="fas fa-search-minus"></i>
                </div>
            </div>

            <!-- Navigate to the Next page -->
            <div>
                <a href="#" class="next round" id="next_page">
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
		</header>
        <main style="display:flex;justify-content:center;">
            <div id="div-container">
                <!-- Canvas to place the PDF -->
                <canvas id="canvas" class="canvas__container"></canvas>
            </div>
        </main>

		<!-- Load PDF.js -->
        <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist/build/pdf.min.js"></script>

		<script>
            const pdf = '<?php echo 'https://www.steinermarco.de/mitteilungsblatt/pdf/'.$year.'/'.$kw.'.pdf';?>';
            const pageNum = document.querySelector('#page_num');
            const pageCount = document.querySelector('#page_count');
            const currentPage = document.querySelector('#current_page');
            const previousPage = document.querySelector('#prev_page');
            const nextPage = document.querySelector('#next_page');
            const zoomIn = document.querySelector('#zoom_in');
            const zoomOut = document.querySelector('#zoom_out');
            
            //var pdfjsLib = window['pdfjs-dist/build/pdf'];
            //pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://mozilla.github.io/pdf.js/build/pdf.worker.js';
            var PDFRenderingInProgress = false;
            var pdfDoc = null;
            var canvas = document.getElementById('canvas');
            var ctx = canvas.getContext('2d');
            var container = document.getElementById("div-container");
            var wheelTimeoutHandler = null;
            var wheelTimeout = 250; //ms

            const initialState = {
                pdfDoc: null,
                currentPage: 1,
                pageCount: 0,
                zoom: 1,
            };

            // Load the document.
            pdfjsLib
                .getDocument(pdf)
                .promise.then((data) => {
                    initialState.pdfDoc = data;
                    //console.log('pdfDocument', initialState.pdfDoc);

                    pageCount.textContent = initialState.pdfDoc.numPages;

                    renderPage(0.8);
                })
                .catch((err) => {
                    alert(err.message);
                });
            
            // Render the page.
            const renderPage = (scale) => {
                // Load the first page.
                //console.log(initialState.pdfDoc, 'pdfDoc');
                //if (scale < 1) scale = 1;
                initialState.pdfDoc
                    .getPage(initialState.currentPage)
                    .then((page) => {
                        //console.log('page', page);
                        //console.log('scale = ', scale);

                        var viewport = page.getViewport({scale: scale});
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;
                        var renderContext = {
                            canvasContext: ctx,
                            viewport: viewport
                        };

                        if (!PDFRenderingInProgress) {
                            PDFRenderingInProgress = true
                            var renderTask = page.render(renderContext);
                            renderTask.promise.then(function() {
                                PDFRenderingInProgress = false
                            })
                        }

                        pageNum.textContent = initialState.currentPage;
                    });
            };

            const showPrevPage = () => {
                if (initialState.pdfDoc === null || initialState.currentPage <= 1)
                    return;
                initialState.currentPage--;
                // Render the current page.
                currentPage.value = initialState.currentPage;
                renderPage(1);
            };

            const showNextPage = () => {
                if (
                    initialState.pdfDoc === null ||
                    initialState.currentPage >= initialState.pdfDoc._pdfInfo.numPages
                )
                    return;

                initialState.currentPage++;
                currentPage.value = initialState.currentPage;
                renderPage(1);
            };

            // Button events.
            previousPage.addEventListener('click', showPrevPage);
            nextPage.addEventListener('click', showNextPage);

            // Keypress event. Enter for Input
            currentPage.addEventListener('keypress', (event) => {
                if (initialState.pdfDoc === null) return;
                // Get the key code.
                const keycode = event.keyCode ? event.keyCode : event.which;

                if (keycode === 13) {
                    // Get the new page number and render it.
                    let desiredPage = currentPage.valueAsNumber;
                    initialState.currentPage = Math.min(
                        Math.max(desiredPage, 1),
                        initialState.pdfDoc._pdfInfo.numPages,
                    );

                    currentPage.value = initialState.currentPage;
                    renderPage(1);
                }
            });

            // Zoom events.
            zoomIn.addEventListener('click', () => {
                if (initialState.pdfDoc === null) return;
                initialState.zoom *= 4 / 3;
                console.log(initialState.zoom);
                renderPage(initialState.zoom);
            });

            zoomOut.addEventListener('click', () => {
                if (initialState.pdfDoc === null) return;
                initialState.zoom *= 2 / 3;
                renderPage(initialState.zoom);
            });

            function zoomWithWheel(event) {
                panzoom.zoomWithWheel(event)
                clearTimeout(wheelTimeoutHandler);
                wheelTimeoutHandler = setTimeout(function() {
                    canvas.style.transform = "scale("+1/panzoom.getScale()+")"
                    if (initialState.pdfDoc)
                        renderPage(panzoom.getScale());
                }, wheelTimeout)
            }
            var panzoom = Panzoom(container, {
                maxScale: 3,
                minScale: 0.8,
                duration: 800
                });
            container.parentElement.addEventListener('wheel', zoomWithWheel)
        </script>
	</body>
</html>