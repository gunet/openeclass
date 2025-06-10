
// Drawing blanks in the circles shapes
function drawCircleWithBlank(x, y, radius, fillColor = 'rgba(207, 207, 207, 0.8)', strokeColor = 'red', label = '', ctx, dataAttrs = {}, Qid) {
    const container = document.getElementById('image-container-'+Qid);
    if (!ctx || !container) {
        console.error('Canvas context or container not found.');
        return;
    }

    // Draw the blank rectangle at the center
    const blankWidth = 100; // fixed size
    const blankHeight = 40;

    // Create overlay span positioned exactly at circle center
    const blankDiv = document.createElement('span');
    blankDiv.className = 'blank';

    for (const key in dataAttrs) {
        if (dataAttrs.hasOwnProperty(key)) {
            blankDiv.setAttribute('data-' + key, dataAttrs[key]);
        }
    }

    // Style the overlay span
    blankDiv.style.position = 'absolute';
    blankDiv.style.width = blankWidth + 'px';
    blankDiv.style.height = blankHeight + 'px';
    blankDiv.style.backgroundColor = 'white';
    blankDiv.style.border = '1px solid grey';
    blankDiv.style.boxSizing = 'border-box';
    blankDiv.style.cursor = 'pointer';
    // blankDiv.style.zIndex = 3;

    // Append overlay to container
    container.style.position = 'relative';
    container.appendChild(blankDiv);

    // Position the span relative to the container
    // Calculate top and left to center span on circle
    const left = x - blankWidth / 2;
    const top = y - blankHeight / 2;
    blankDiv.style.left = left + 'px';
    blankDiv.style.top = top + 'px';

}


// Drawing blanks in the rectangles shapes
function drawRectangleWithBlank(x, y, width, height, fillColor = 'rgba(207, 207, 207, 0.8)', borderColor = 'grey', label = '', ctx, dataAttrs = {}, Qid) {
    const container = document.getElementById('image-container-'+Qid);

    // Dimensions for the blank span
    const blankWidth = 100;
    const blankHeight = 40;

    // Center position for the blank rectangle
    const blankX = x + (width - blankWidth) / 2;
    const blankY = y + (height - blankHeight) / 2;


    // Create the overlay span
    const blankDiv = document.createElement('span');
    blankDiv.className = 'blank';

    for (const key in dataAttrs) {
        if (dataAttrs.hasOwnProperty(key)) {
            blankDiv.setAttribute('data-'+key, dataAttrs[key]);
        }
    }

    // Get container's position relative to viewport
    const containerRect = container.getBoundingClientRect();

    // Position the span relative to the container
    // Since the container's position is relative/absolute,
    // and the container's top-left is (0,0) for the overlay,
    // we offset by the container's position
    blankDiv.style.position = 'absolute';

    // Set the position relative to the container
    blankDiv.style.left = (containerRect.left + blankX - containerRect.left) + 'px'; // same as blankX
    blankDiv.style.top = (containerRect.top + blankY - containerRect.top) + 'px'; // same as blankY

    // For simplicity, just assign the position relative to container
    // because the container is positioned relatively
    blankDiv.style.left = blankX + 'px';
    blankDiv.style.top = blankY + 'px';

    // Set size and styles
    blankDiv.style.width = blankWidth + 'px';
    blankDiv.style.height = blankHeight + 'px';
    blankDiv.style.backgroundColor = 'white';
    blankDiv.style.border = '1px solid grey';
    blankDiv.style.boxSizing = 'border-box';
    blankDiv.style.cursor = 'pointer';
    // blankDiv.style.zIndex = 3;

    // Append overlay to container
    container.appendChild(blankDiv);
}

// Drawing blanks in the polygons shapes
function drawPolygonWithBlank(points, color, fillColor, label, ctx, dataAttrs = {},  Qid) {
    const container = document.getElementById('image-container-'+Qid);
    if (points.length < 2) return;
    //ctx.strokeStyle = color;
    //ctx.lineWidth = 2;
    //ctx.fillStyle = fillColor;
    // ctx.beginPath();
    // ctx.moveTo(points[0].x, points[0].y);
    // for (let i = 1; i < points.length; i++) {
    //     ctx.lineTo(points[i].x, points[i].y);
    // }
    // ctx.closePath();
    //ctx.fill(); 
    //ctx.stroke();

    // Compute simple center (average of points)
    const centerPolX = points.reduce((sum, p) => sum + p.x, 0) / points.length;
    const centerPolY = points.reduce((sum, p) => sum + p.y, 0) / points.length;

    // Dimensions for the blank span
    const blankWidth = 100;
    const blankHeight = 40;

    // Create overlay span positioned exactly at circle center
    const blankDiv = document.createElement('span');
    blankDiv.className = 'blank';

    for (const key in dataAttrs) {
        if (dataAttrs.hasOwnProperty(key)) {
            blankDiv.setAttribute('data-' + key, dataAttrs[key]);
        }
    }

    // Style the overlay span
    blankDiv.style.position = 'absolute';
    blankDiv.style.width = blankWidth + 'px';
    blankDiv.style.height = blankHeight + 'px';
    blankDiv.style.backgroundColor = 'white';
    blankDiv.style.border = '1px solid grey';
    blankDiv.style.boxSizing = 'border-box';
    blankDiv.style.cursor = 'pointer';
    // blankDiv.style.zIndex = 3;

    // Append overlay to container
    container.style.position = 'relative';
    container.appendChild(blankDiv);

    // Position the span relative to the container
    // Calculate top and left to center span on circle
    blankDiv.style.left = centerPolX - 25 + 'px';
    blankDiv.style.top = centerPolY - 25 + 'px';

}

// Loading shapes for displaying
function loadShapes(qID) {
    const canvas = $('#drawingCanvas-'+qID);
    const ctx = canvas[0].getContext('2d');

    // Clear existing shapes array
    shapes = [];

    // Clear canvas
    ctx.clearRect(0, 0, canvas.width(), canvas.height());

    // Parse shapes data from hidden input or server
    let shapesData;
    try {
        shapesData = JSON.parse($('#insertedMarkersAsJson-'+qID).val());
    } catch (e) {
        console.error('Invalid JSON data for shapes:', e);
        return;
    }

    // Populate shapes array and draw each shape
    if (shapesData) {
        shapesData.forEach(shape => {
            switch (shape.shape_type) {
                case 'circle':
                    if (shape.radius !== undefined) {
                        attributes = {
                                        'answer': shape.marker_id,
                                        'blank-id': shape.marker_id,
                                        'card-id': 'words_'+qID
                                    };
                        drawCircleWithBlank(shape.x, shape.y, shape.radius, 'rgba(207, 207, 207, 0.8)', 'grey', shape.marker_id, ctx, attributes, qID);
                    }
                    break;
                case 'rectangle':
                    if (shape.endY !== undefined && shape.endX !== undefined) {
                        const rectX = Math.min(shape.x, shape.endX);
                        const rectY = Math.min(shape.y, shape.endY);
                        const rectWidth = Math.abs(shape.endX - shape.x);
                        const rectHeight = Math.abs(shape.endY - shape.y);
                        attributes = {
                                        'answer': shape.marker_id,
                                        'blank-id': shape.marker_id,
                                        'card-id': 'words_'+qID
                                    };
                        drawRectangleWithBlank(rectX, rectY, rectWidth, rectHeight, 'rgba(207, 207, 207, 0.8)', 'grey', shape.marker_id, ctx, attributes, qID);
                    }
                    break;
                case 'polygon':
                    const inputString = shape.points;
                    const pairs = inputString.split(':');
                    const resultArray = pairs.map(pair => {
                    const [x, y] = pair.split(',');
                        return { x: parseFloat(x), y: parseFloat(y) };
                    });
                    if (Array.isArray(resultArray)) { console.log(resultArray);
                        attributes = {
                                        'answer': shape.marker_id,
                                        'blank-id': shape.marker_id,
                                        'card-id': 'words_'+qID
                                     };
                        drawPolygonWithBlank(resultArray, 'grey', shape.color, shape.marker_id, ctx, attributes, qID);
                    }
                    break;
            }
        });
    }
}

// Create blanks on the image regarding defined answers. (Drag and drop marker type of question in exercise).
function createMarkersBlanksOnImage() {
    var qID = $('.currentQuestion').val();
    const img = $('#img-quiz-'+qID);
    const canvas = $('#drawingCanvas-'+qID);

    // Set canvas size to match image
    const width = img.width();
    const height = img.height();
    canvas.attr({ width: width, height: height }).css({ width: width + 'px', height: height + 'px', display: 'block', position: 'absolute', top: img.position().top, left: img.position().left });

    // Load existing shapes
    loadShapes(qID);

    if (qID > 0) {
        // Remove the current question in order to get the next question.
        const hiddenInput = document.querySelector('input.currentQuestion');
        if (hiddenInput) {
            hiddenInput.remove();
        }
    }
}

// Calculate the user's answers
function user_answers_calculation(draggableItem) {
    var pool_id = draggableItem.attr('data-pool-id');
    const parts = pool_id.split('_');
    const number = parseInt(parts[1], 10);
    const arr = [];
    const blanks = document.querySelectorAll('.blank');
    blanks.forEach(blank => {
        const dataCardId = blank.getAttribute('data-card-id');
        const partscard = dataCardId.split('_');
        const cardId = parseInt(partscard[1], 10);
        if (cardId == number) {
            const dataAnswer = blank.getAttribute('data-answer');
            const draggable = blank.querySelector('.dropped-word');
            const dataWord = draggable ? draggable.getAttribute('data-word') : null;
            arr.push({ dataAnswer, dataWord });
        }
    });
    const jsonStr = JSON.stringify(arr);
    document.getElementById('arrInput_'+number).value = jsonStr;
}

// Initialize draggable pool words
function initializePoolDraggable() {
    $('.draggable').each(function() {
        $(this).draggable({
            revert: 'invalid',
            cursor: 'move',
            helper: 'clone',
            zIndex: 100,
            start: function(event, ui) {
                $(this).data('dragging', true);
            },
            stop: function(event, ui) {
                $(this).data('dragging', false);
                // Calculate the user's answers
                user_answers_calculation($(this));
            }
        });
    });
}


function drag_and_drop_process() {

    $(function() {

        // Initialize drag on pool items
        initializePoolDraggable();

        // Make blanks droppable
        $('.blank').droppable({
            accept: '.draggable',
            hoverClass: 'hovered',
            drop: function(event, ui) {
                var thisBlank = $(this);
                var thisCardOfBlank = $(this).attr('data-card-id');

                // If blank already has a word, do nothing
                if (thisBlank.children().length > 0) {
                    alert('The blank is not empty!');
                    return;
                }

                // Remove the dragged word from pool immediately
                var draggedWord = ui.draggable;

                // Do not drop a word to a blank of other question
                var word = draggedWord.clone();
                var poolOfWord = word.attr('data-pool-id');
                if (thisCardOfBlank!=poolOfWord){
                    alert('You are trying to fill in a blank to other question!');
                    return;
                }

                // Remove from pool
                draggedWord.remove();

                // Clone the dragged word for placement
                var word = draggedWord.clone();
                word.addClass('dropped-word');

                // Append to blank
                thisBlank.empty().append(word);

                // Calculate the user's answers
                setTimeout(function() {
                    user_answers_calculation(word);
                }, 500);

                // Make the dropped word draggable to allow removal
                word.draggable({
                    revert: 'invalid',
                    helper: 'clone',
                    zIndex: 100,
                    start: function(event, ui) {
                        $(this).data('dragging', true);
                    }
                });

                // Add click to remove the word and return it to pool
                word.on('click', function() {
                    // Get pool id
                    var pool_id = $(this).attr('data-pool-id');

                    // Remove the word from blank
                    $(this).remove();

                    // Return the original draggable to pool
                    $('#'+pool_id).append(draggedWord);

                    // Remove the 'dropped-word' class to make it draggable again
                    draggedWord.removeClass('dropped-word');

                    // Calculate the user's answers
                    user_answers_calculation($(this));

                    // Reinitialize all pool draggable items
                    initializePoolDraggable();
                });

            }
        });

    });
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// answer_admin_inc.php
// Draw a shape of a marker during creating an answer of a question. 

let currentShape = null;
let currentShapeId = null;
let isDrawing = false;
let startX = 0; 
let startY = 0;
let vertices = [];
let currentX = 0;
let currentY = 0;
let polygonPoints = [];
let currentMarker = 0;
let radiusOriginal = 0;
let shapes = [];


function drawCircle(x, y, radius, fillColor = 'rgba(255, 255, 255, 0.5)', strokeColor = 'grey', label = '', ctx) {
    ctx.fillStyle = fillColor;
    ctx.beginPath();
    ctx.arc(x, y, radius, 0, Math.PI * 2);
    ctx.fill();
    ctx.strokeStyle = strokeColor;
    ctx.lineWidth = 2;
    ctx.stroke();

    if (label) {
        ctx.fillStyle = 'black';
        ctx.font = '14px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(label, x, y);
    }

    radiusOriginal = radius;
}

function drawRectangle(x, y, width, height, fillColor = 'rgba(255, 255, 255, 0.5)', borderColor = 'grey', label = '', ctx) {
    ctx.fillStyle = fillColor;
    ctx.fillRect(x, y, width, height); // Fill background
    ctx.strokeStyle = borderColor;
    ctx.lineWidth = 2;
    ctx.strokeRect(x, y, width, height);

    if (label) {
        ctx.fillStyle = 'black'; // Text color
        ctx.font = '14px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        // Calculate center of rectangle
        const centerX = x + width / 2;
        const centerY = y + height / 2;

        ctx.fillText(label, centerX, centerY);
    }
}

function drawPolygon(points, color, fillColor, label, ctx) {
    if (points.length < 2) return;
    ctx.strokeStyle = color;
    ctx.lineWidth = 2;
    ctx.fillStyle = fillColor;
    ctx.beginPath();
    ctx.moveTo(points[0].x, points[0].y);
    for (let i = 1; i < points.length; i++) {
        ctx.lineTo(points[i].x, points[i].y);
    }
    ctx.closePath();
    ctx.fill(); 
    ctx.stroke();

    // Compute simple center (average of points)
    const centerPolX = points.reduce((sum, p) => sum + p.x, 0) / points.length;
    const centerPolY = points.reduce((sum, p) => sum + p.y, 0) / points.length;

    // Draw label at approximate center
    if (label) {
        ctx.fillStyle = 'black';
        ctx.font = '14px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(label, centerPolX, centerPolY);
    }

}

function redraw(ctx) {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    // Optionally, redraw existing shapes stored in an array
}

function getNumberOftheText(text) {
    const str = text;
    const match = str.match(/[\d.]+/);
    const number = match ? parseFloat(match[0]) : null;
    return number;
}

function loadShapesOnImage(questionId) {
    const canvas = $('#drawingCanvas-'+questionId);
    const ctx = canvas[0].getContext('2d');

    // Clear existing shapes array
    shapes = [];

    // Clear canvas
    ctx.clearRect(0, 0, canvas.width(), canvas.height());

    // Parse shapes data from hidden input or server
    let shapesData;
    try {
        shapesData = JSON.parse($('#insertedMarkersAsJson').val());
    } catch (e) {
        console.error('Invalid JSON data for shapes:', e);
        return;
    }

    // Populate shapes array and draw each shape
    if (shapesData) {
        shapesData.forEach(shape => {
            shapes.push(shape);
            switch (shape.marker_shape) {
                case 'circle':
                    if (shape.radius !== undefined) {
                        drawCircle(shape.x, shape.y, shape.radius, shape.color || 'rgba(255, 255, 255, 0.5)', 'grey', shape.marker_id, ctx);
                    }
                    break;
                case 'rectangle':
                    if (shape.width !== undefined && shape.height !== undefined) {
                        const rectX = Math.min(shape.x, shape.height);
                        const rectY = Math.min(shape.y, shape.width);
                        const rectWidth = Math.abs(shape.height - shape.x);
                        const rectHeight = Math.abs(shape.width - shape.y);
                        drawRectangle(rectX, rectY, rectWidth, rectHeight, shape.color, 'grey', shape.marker_id, ctx);
                    }
                    break;
                case 'polygon':
                    const inputString = shape.points;
                    const pairs = inputString.split(':');
                    const resultArray = pairs.map(pair => {
                    const [x, y] = pair.split(',');
                        return { x: parseFloat(x), y: parseFloat(y) };
                    });
                    if (Array.isArray(resultArray)) {
                        drawPolygon(resultArray, 'grey', shape.color, shape.marker_id, ctx);
                    }
                    break;
            }
        });
    }
}

function saveShape(vertices,qId,cCode) {
    // Send shape coordinates to server via AJAX to save
    $.ajax({
        url: 'save_dropZones.php?course_code='+cCode+'&questionId='+qId,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(vertices),
        success: function(response) {
            console.log(response);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error:', textStatus, errorThrown);
        }
    });
}

function enableDrawing(currentShape,questionId) {
    const container = $('#image-container-'+questionId);
    const img = $('#img-quiz-'+questionId);
    const width = img.width();
    const height = img.height();
    const canvas = $('#drawingCanvas-'+questionId);
    const ctx = canvas[0].getContext('2d');

    // Set canvas size
    canvas.attr({ width: width, height: height }).css({ width: width + 'px', height: height + 'px', display: 'block' });
    // Clear previous drawings
    //ctx.clearRect(0, 0, width, height);
    redrawAllShapes(ctx);

    $('#drawingCanvas-'+questionId).off(); // Remove previous event handlers to avoid stacking
    // Mousedown
    $('#drawingCanvas-'+questionId).on('mousedown', function(e) {
        if (!currentShape) return;
        isDrawing = true;
        startX = e.offsetX;
        startY = e.offsetY;

    });

    // Mousemove
    $('#drawingCanvas-'+questionId).on('mousemove', function(e) {
        if (!isDrawing || currentShape === 'polygon') return;

        currentX = e.offsetX;
        currentY = e.offsetY;

        //ctx.clearRect(0, 0, width, height); // Clear previous preview
        redrawAllShapes(ctx);

        // Draw shape preview
        if (currentShape === 'rectangle') {
            var textMarker = 'Marker:'+currentMarker;
            drawRectangle(startX, startY, currentX - startX, currentY - startY, 'rgba(255, 255, 255, 0.5)', 'grey', textMarker, ctx);
        } else if (currentShape === 'circle') {
            const radius = Math.hypot(currentX - startX, currentY - startY);
            var textMarker = 'Marker:'+currentMarker;
            radiusOriginal = radius;
            drawCircle(startX, startY, radius, 'rgba(255, 255, 255, 0.5)', 'grey', textMarker, ctx);
        }
    });

    // Mouseup
    $('#drawingCanvas-'+questionId).on('mouseup', function(e) {
        if (!isDrawing) return;
        isDrawing = false;

        //ctx.clearRect(0, 0, width, height); // Clear before final drawing
        redrawAllShapes(ctx);

        const endX = e.offsetX;
        const endY = e.offsetY;

        if (currentShape === 'rectangle') {
            var textMarker = 'Marker:'+currentMarker;
            drawRectangle(startX, startY, endX - startX, endY - startY, 'rgba(255, 255, 255, 0.5)', 'grey', textMarker, ctx);
            // Save shape data
            var coords = startX + ',' + startY + ':' + endX + ',' + endY;
            $('#shape-coordinates-'+currentMarker).val(coords);
        } else if (currentShape === 'circle') {
            const radius = Math.hypot(endX - startX, endY - startY);
            radiusOriginal = radius;
            var textMarker = 'Marker:'+currentMarker;
            drawCircle(startX, startY, radius, 'rgba(255, 255, 255, 0.5)', 'grey', textMarker, ctx);
            // Save shape data
            var coords = startX + ',' + startY + ':' + endX + ',' + endY;
            $('#shape-coordinates-'+currentMarker).val(coords);
        }
    });

    // For polygon: add points on click
    $('#drawingCanvas-'+questionId).off('click').on('click', function(e) {
        if (currentShape !== 'polygon') return;
        const x = e.offsetX;
        const y = e.offsetY;
        polygonPoints.push({ x, y });
        //ctx.clearRect(0, 0, width, height);
        redrawAllShapes(ctx);
        // Draw existing points
        var textMarker = 'Marker:'+currentMarker;
        drawPolygon(polygonPoints, 'grey', 'rgba(255, 255, 255, 0.5)', textMarker, ctx);
        // Save shape data
        var coordsArr = [];
        for (var i = 0; i < polygonPoints.length; i++) {
            coordsArr.push(polygonPoints[i].x + ',' + polygonPoints[i].y);
        }
        const jsonString = JSON.stringify(coordsArr);
        const arrJson = JSON.parse(jsonString);
        const resultCoords = arrJson.join(':');
        $('#shape-coordinates-'+currentMarker).val(resultCoords);

        // Draw current point
        ctx.fillStyle = 'blue';
        ctx.beginPath();
        ctx.arc(x, y, 3, 0, Math.PI * 2);
        ctx.fill();
    });

}

function redrawAllShapes(ctx) {
    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
    if (shapes.length > 0) {
        shapes.forEach(shape => {
            switch (shape.marker_shape) {
                case 'circle':
                    drawCircle(shape.x, shape.y, shape.radius, shape.color || 'rgba(255, 255, 255, 0.5)', 'grey', shape.marker_id, ctx);
                    break;
                case 'rectangle':
                    if (shape.width !== undefined && shape.height !== undefined) {
                        const rectX = Math.min(shape.x, shape.height);
                        const rectY = Math.min(shape.y, shape.width);
                        const rectWidth = Math.abs(shape.height - shape.x);
                        const rectHeight = Math.abs(shape.width - shape.y);
                        drawRectangle(rectX, rectY, rectWidth, rectHeight, shape.color, 'grey', shape.marker_id, ctx);
                    }
                    break;
                case 'polygon':
                    const inputString = shape.points;
                    const pairs = inputString.split(':');
                    const resultArray = pairs.map(pair => {
                    const [x, y] = pair.split(',');
                        return { x: parseFloat(x), y: parseFloat(y) };
                    });
                    if (Array.isArray(resultArray)) {
                        drawPolygon(resultArray, 'grey', shape.color, shape.marker_id, ctx);
                    }
                    break;
            }
        });
    }
}

// Call the appropiate function for creating and display shapes. You can remove a shape if you want.
function shapesCreationProcess() {

    $(function() {

        var questionId = $('.currentQuestionId').val();
        var courseCode = $('.currentCourseCode').val();

        const img = $('#img-quiz-'+questionId);
        const canvas = $('#drawingCanvas-'+questionId);

        // Set canvas size to match image
        const width = img.width();
        const height = img.height();
        canvas.attr({ width: width, height: height }).css({ width: width + 'px', height: height + 'px', display: 'block', position: 'absolute', top: img.position().top, left: img.position().left });

        // Load existing shapes
        loadShapesOnImage(questionId);

        // When shape is selected, enable drawing
        $('.shape-selection').on('change', function() {
            currentShape = $(this).val();
            currentMarker = getNumberOftheText($(this).attr('id'));
            if (currentShape) {
                enableDrawing(currentShape,questionId);
            } else {
                polygonPoints = [];
                $('#drawingCanvas-'+questionId).hide();
            }
        });

        $('.add-data-shape').on('click',function(e) {
            e.preventDefault();
            var addValuesId = $(this).attr('id');
            isDrawing = false;
            if (confirm('Do you want to proceed?')) {
                var number = getNumberOftheText(addValuesId);
                var markerAnswer = $('#marker-answer-'+number).val();
                var markerGrade = $('#marker-grade-'+number).val();
                var markerCoordinates = $('#shape-coordinates-'+number).val();
                var markerShape = $('#shapeType-'+number).val();
                

                if (markerAnswer && markerCoordinates) {
                    if (markerShape == 'circle' || markerShape == 'rectangle') {

                        // Replace colon with comma
                        const replacedStr = markerCoordinates.replace(/:/g, ',');
                        // Split the string into an array
                        const arr = replacedStr.split(',').map(Number);

                        vertices = [
                                        {'marker_id': number},
                                        {'marker_answer': markerAnswer},
                                        {'shape_type': markerShape},
                                        {'x': arr[0]},
                                        {'y': arr[1]},
                                        {'endX': arr[2]},
                                        {'endY': arr[3]},
                                        {'marker_grade': markerGrade},
                                        {'marker_radius': radiusOriginal}
                                    ];
                    } else if (markerShape == 'polygon') {
                        vertices = [
                                        {'marker_id': number},
                                        {'marker_answer': markerAnswer},
                                        {'shape_type': markerShape},
                                        {'points': markerCoordinates},
                                        {'marker_grade': markerGrade}
                                    ];
                    }
                    saveShape(vertices,questionId,courseCode);
                    window.location.reload();
                } else {
                    alert('Give an answer for this shape');
                    window.location.reload();
                }
            }
        });

        $('.delete-data-shape').on('click', function(e){
            e.preventDefault(); 
            var delValuesId = $(this).attr('id');
            isDrawing = false;
            var number = getNumberOftheText(delValuesId);
            if (confirm('Do you want to proceed?')) {
                $.ajax({
                    url: 'delete_marker.php?course_code='+courseCode+'&questionId='+questionId,
                    method: 'POST',
                    data: { marker_id: number },
                    success: function(response) {
                        console.log(response); // Handle response
                        alert('Marker deleted successfully!');
                        window.location.reload();
                    },
                    error: function() {
                        alert('Error deleting marker.');
                        window.location.reload();
                    }
                });
            }
        });

    });
}