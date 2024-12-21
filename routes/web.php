<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>PDF Coordinate Finder</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.943/pdf.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.3/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.943/pdf.worker.min.js';
    </script>
    <style>
        .text-preview {
            position: absolute;
            pointer-events: none;
            white-space: nowrap;
            font-family: Arial, sans-serif;
            color: blue;
            opacity: 0.7;
        }
        .placed-text {
            position: absolute;
            pointer-events: none;
            white-space: nowrap;
            font-family: Arial, sans-serif;
            color: green;
            opacity: 0.7;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8" x-data="pdfViewer()">
        <div class="max-w-5xl mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">PDF Coordinate Finder</h1>
                <p class="text-gray-600">Load a PDF, select your input type, and click to place text or checkmarks</p>
            </div>

            <!-- Controls -->
            <div class="mb-6 bg-white p-6 rounded-lg shadow-sm space-y-4">
                <!-- Input Type Selection -->
                <div class="flex space-x-4">
                    <label class="flex items-center space-x-2">
                        <input type="radio"
                               name="inputType"
                               value="text"
                               x-model="inputType"
                               checked
                               class="text-blue-600">
                        <span>Add Text</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="radio"
                               name="inputType"
                               value="checkbox"
                               x-model="inputType"
                               class="text-blue-600">
                        <span>Add Checkbox (X)</span>
                    </label>
                </div>

                <!-- Text Input and Font Size -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="inputType === 'text'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Text to add:</label>
                        <input type="text"
                               x-model="textToAdd"
                               placeholder="Enter text"
                               class="w-full px-3 py-2 border rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Font Size (pt):</label>
                        <input type="number"
                               x-model="fontSize"
                               min="6"
                               max="72"
                               class="w-full px-3 py-2 border rounded-md">
                    </div>
                </div>

                <!-- File Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">PDF File:</label>
                    <input type="file"
                           accept=".pdf"
                           @change="loadPdf($event)"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
            </div>

            <!-- Status Message -->
            <div x-show="statusMessage"
                 :class="{
                     'bg-blue-50 text-blue-700 border-blue-200': statusType === 'info',
                     'bg-red-50 text-red-700 border-red-200': statusType === 'error',
                     'bg-green-50 text-green-700 border-green-200': statusType === 'success'
                 }"
                 class="mb-6 p-4 rounded-lg border"
                 x-text="statusMessage">
            </div>

            <!-- Page Controls -->
            <div x-show="pdfLoaded" class="mb-6 bg-white p-4 rounded-lg shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">Page <span x-text="pageNum"></span> of <span x-text="pageCount"></span></span>
                        <div class="space-x-2">
                            <button @click="prevPage"
                                    :disabled="pageNum <= 1"
                                    class="px-3 py-1 bg-gray-100 hover:bg-gray-200 disabled:opacity-50 rounded">
                                Previous
                            </button>
                            <button @click="nextPage"
                                    :disabled="pageNum >= pageCount"
                                    class="px-3 py-1 bg-gray-100 hover:bg-gray-200 disabled:opacity-50 rounded">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PDF Display -->
            <div class="relative bg-white rounded-lg shadow-lg border overflow-hidden"
                 id="pdfContainer"
                 @mousemove="updatePreview($event)"
                 @mouseleave="hidePreview()"
                 @click="addText($event)">
                <canvas id="pdfCanvas" class="block w-full"></canvas>

                <!-- Preview -->
                <div x-show="showPreview"
                     class="text-preview"
                     :style="previewStyle"
                     x-text="getPreviewText()">
                </div>

                <!-- Placed Items -->
                <template x-for="placement in textPlacements" :key="placement.id">
                    <div class="placed-text"
                         x-show="placement.page === pageNum"
                         :style="getPlacementStyle(placement)"
                         x-text="placement.text">
                    </div>
                </template>
            </div>

            <!-- Placements List -->
            <div x-show="textPlacements.length > 0" class="mt-6">
                <h3 class="text-lg font-semibold mb-2">Added Items:</h3>
                <div class="bg-white rounded-lg shadow-sm divide-y">
                    <template x-for="(placement, index) in textPlacements" :key="index">
                        <div class="p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-medium" x-text="placement.text"></p>
                                    <p class="text-sm text-gray-500">
                                        Page: <span x-text="placement.page"></span>,
                                        X: <span x-text="placement.x"></span>,
                                        Y: <span x-text="placement.y"></span>
                                        <span x-show="placement.type === 'text'">,
                                            Size: <span x-text="placement.fontSize"></span>pt
                                        </span>
                                    </p>
                                </div>
                                <button @click="removeText(index)"
                                        class="text-red-600 hover:text-red-800">
                                    Remove
                                </button>
                            </div>
                            <div>
                                <pre><code class="language-php" x-text="generateCode(placement)"></code></pre>
                                <button @click="copyCode(placement)"
                                        class="mt-2 px-3 py-1 text-sm bg-blue-50 text-blue-700 hover:bg-blue-100 rounded">
                                    Copy Code
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
<script>
        function pdfViewer() {
            return {
                pdfDoc: null,
                pageNum: 1,
                pageCount: 0,
                showPreview: false,
                textToAdd: '',
                fontSize: 12,
                inputType: 'text',
                textPlacements: [],
                statusMessage: '',
                statusType: 'info',
                pdfLoaded: false,

                getPreviewText() {
                    return this.inputType === 'checkbox' ? 'X' : this.textToAdd;
                },

                async loadPdf(event) {
                    const file = event.target.files[0];
                    if (!file) {
                        this.statusMessage = 'No file selected';
                        this.statusType = 'error';
                        return;
                    }

                    this.statusMessage = 'Loading PDF...';
                    this.statusType = 'info';
                    this.textPlacements = [];

                    const reader = new FileReader();

                    reader.onload = (e) => {
                        const typedarray = new Uint8Array(e.target.result);
                        const loadingTask = pdfjsLib.getDocument(typedarray);

                        loadingTask.promise.then(pdf => {
                            this.pdfDoc = pdf;
                            this.pageCount = pdf.numPages;
                            this.pageNum = 1;
                            this.pdfLoaded = true;
                            this.statusMessage = 'PDF loaded successfully!';
                            this.statusType = 'success';
                            this.renderPage();
                        }).catch(error => {
                            console.error('Error loading PDF:', error);
                            this.statusMessage = 'Error loading PDF: ' + error.message;
                            this.statusType = 'error';
                        });
                    };

                    reader.onerror = (error) => {
                        console.error('Error reading file:', error);
                        this.statusMessage = 'Error reading file: ' + error.message;
                        this.statusType = 'error';
                    };

                    reader.readAsArrayBuffer(file);
                },

                renderPage() {
                    if (!this.pdfDoc) {
                        this.statusMessage = 'No PDF loaded';
                        this.statusType = 'error';
                        return;
                    }

                    this.pdfDoc.getPage(this.pageNum).then(page => {
                        const canvas = document.getElementById('pdfCanvas');
                        const context = canvas.getContext('2d');

                        const viewport = page.getViewport(1.5);
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        const renderContext = {
                            canvasContext: context,
                            viewport: viewport
                        };

                        page.render(renderContext).then(() => {
                            this.statusMessage = `Showing page ${this.pageNum} of ${this.pageCount}`;
                            this.statusType = 'success';
                        }).catch(error => {
                            console.error('Error rendering page:', error);
                            this.statusMessage = 'Error rendering page: ' + error.message;
                            this.statusType = 'error';
                        });
                    }).catch(error => {
                        console.error('Error getting page:', error);
                        this.statusMessage = 'Error getting page: ' + error.message;
                        this.statusType = 'error';
                    });
                },

                nextPage() {
                    if (this.pageNum < this.pageCount) {
                        this.pageNum++;
                        this.renderPage();
                    }
                },

                prevPage() {
                    if (this.pageNum > 1) {
                        this.pageNum--;
                        this.renderPage();
                    }
                },

                updatePreview(event) {
                    if ((!this.textToAdd && this.inputType === 'text') || !this.inputType) return;

                    const canvas = document.getElementById('pdfCanvas');
                    const rect = canvas.getBoundingClientRect();

                    const x = event.clientX - rect.left;
                    const y = event.clientY - rect.top;

                    this.showPreview = true;
                    this.previewStyle = `left: ${x}px; top: ${y}px; font-size: ${this.fontSize * 1.5}px`;
                },

                hidePreview() {
                    this.showPreview = false;
                },

                addText(event) {
                    if ((!this.textToAdd && this.inputType === 'text') || !this.inputType) return;

                    const canvas = document.getElementById('pdfCanvas');
                    const rect = canvas.getBoundingClientRect();

                    const x = event.clientX - rect.left;
                    const y = event.clientY - rect.top;

                    const pdfX = Math.round(x / 1.5);
                    const pdfY = Math.round(y / 1.5);

                    this.textPlacements.push({
                        id: Date.now(),
                        text: this.inputType === 'checkbox' ? 'X' : this.textToAdd,
                        x: pdfX,
                        y: pdfY,
                        page: this.pageNum,
                        screenX: x,
                        screenY: y,
                        fontSize: this.fontSize,
                        type: this.inputType
                    });

                    setTimeout(() => Prism.highlightAll(), 100);
                },

                removeText(index) {
                    this.textPlacements.splice(index, 1);
                },

                getPlacementStyle(placement) {
                    return `left: ${placement.screenX}px; top: ${placement.screenY}px; font-size: ${placement.fontSize * 1.5}px`;
                },

                generateCode(placement) {
                    if (placement.type === 'checkbox') {
                        return `// Add X mark for checkbox
$pdf->setFont('Helvetica', '', 12);
$pdf->text(${placement.x}, ${placement.y}, 'X');`;
                    } else {
                        return `// Add text to PDF
$pdf->setFont('Helvetica', '', ${placement.fontSize});
$pdf->text(${placement.x}, ${placement.y}, "${placement.text}");`;
                    }
                },

                copyCode(placement) {
                    const code = this.generateCode(placement);
                    navigator.clipboard.writeText(code).then(() => {
                        this.statusMessage = 'Code copied to clipboard!';
                        this.statusType = 'success';
                        setTimeout(() => {
                            if (this.statusMessage === 'Code copied to clipboard!') {
                                this.statusMessage = '';
                            }
                        }, 2000);
                    });
                }
            }
        }
    </script>
</body>
</html>
HTML;
});
