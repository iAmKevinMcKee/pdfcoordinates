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
            <!-- Add to head section -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');

    .retro-bg {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    }

    .retro-border {
        border: 2px solid #00ff9d;
        box-shadow: 0 0 10px #00ff9d, inset 0 0 10px #00ff9d;
    }

    #pdfContainer {
        cursor: none;
    }

    .retro-input {
        background: #1a1a2e;
        border: 1px solid #00ff9d;
        color: #00ff9d;
        text-shadow: 0 0 5px #00ff9d;
    }

    .retro-input:focus {
        box-shadow: 0 0 15px #00ff9d;
        outline: none;
    }

    .retro-radio {
        appearance: none;
        width: 16px;
        height: 16px;
        border: 2px solid #00ff9d;
        border-radius: 50%;
        background: transparent;
        position: relative;
    }

    .retro-radio:checked::after {
        content: '';
        position: absolute;
        width: 8px;
        height: 8px;
        background: #00ff9d;
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        box-shadow: 0 0 8px #00ff9d;
    }

    .neon-text {
        color: #00ff9d;
        text-shadow: 0 0 5px #00ff9d;
    }

    .retro-card {
        background: rgba(26, 26, 46, 0.9);
        border: 1px solid #00ff9d;
        box-shadow: 0 0 10px rgba(0, 255, 157, 0.3);
    }

    .retro-button {
        background: transparent;
        border: 1px solid #00ff9d;
        color: #00ff9d;
        text-shadow: 0 0 5px #00ff9d;
        transition: all 0.3s ease;
    }

    .retro-button:hover {
        background: #00ff9d;
        color: #1a1a2e;
        box-shadow: 0 0 15px #00ff9d;
    }

    .file-input-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
    }

    .file-input-wrapper input[type="file"] {
        font-size: 100px;
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
    }
</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex h-screen overflow-hidden" x-data="pdfViewer()">
        <!-- Left Sidebar -->
<div class="w-96 h-screen overflow-y-auto border-r retro-bg p-6">
    <div class="mb-6">
        <h1 class="font-['Press_Start_2P'] text-xl neon-text mb-4">PDF.COORDINATES</h1>
        <p class="text-sm text-cyan-400">[ SYSTEM READY ] Load PDF and initialize coordinate mapping</p>
    </div>

    <!-- Controls -->
    <div class="space-y-6">
        <!-- File Input -->
        <div class="retro-card p-4">
            <label class="block text-sm neon-text font-['Press_Start_2P'] mb-2">SELECT PDF:</label>
            <div class="file-input-wrapper w-full">
                <button class="w-full retro-button px-4 py-2 rounded text-sm">
                    [ CHOOSE FILE ]
                </button>
                <input type="file" accept=".pdf" @change="loadPdf($event)"
                       class="cursor-pointer">
            </div>
        </div>
        <!-- Input Type Selection -->
        <div class="space-y-2 retro-card p-4">
            <label class="text-sm neon-text font-['Press_Start_2P']">MODE SELECT:</label>
            <div class="flex space-x-6 mt-3">
                <label class="flex items-center space-x-2">
                    <input type="radio" name="inputType" value="text" x-model="inputType" checked
                           class="retro-radio">
                    <span class="text-cyan-400">TEXT MODE</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="radio" name="inputType" value="checkbox" x-model="inputType"
                           class="retro-radio">
                    <span class="text-cyan-400">CHECK MODE</span>
                </label>
            </div>
        </div>

        <!-- Text Input and Font Size -->
        <div class="space-y-4 retro-card p-4">
            <div x-show="inputType === 'text'">
                <label class="block text-sm neon-text font-['Press_Start_2P'] mb-2">INPUT TEXT:</label>
                <input type="text" x-model="textToAdd" placeholder="ENTER TEXT HERE"
                       class="w-full px-3 py-2 retro-input rounded">
            </div>
            <div>
                <label class="block text-sm neon-text font-['Press_Start_2P'] mb-2">
                    <span x-text="inputType === 'checkbox' ? 'X SIZE' : 'FONT SIZE'"></span>:
                </label>
                <input type="number" x-model="fontSize" min="6" max="72"
                       class="w-full px-3 py-2 retro-input rounded">
            </div>
        </div>

        <!-- Text Font -->
        <div class="space-y-4 retro-card p-4">
            <label class="block text-sm neon-text font-['Press_Start_2P'] mb-2">FONT:</label>
            <select x-model="selectedFont"
                    class="w-full px-3 py-2 retro-input rounded">
                <option value="Helvetica">Helvetica</option>
                <option value="Times-Roman">Times Roman</option>
                <option value="Courier">Courier</option>
                <option value="Symbol">Symbol</option>
                <option value="ZapfDingbats">ZapfDingbats</option>
            </select>
        </div>

        <!-- Status Message -->
        <div x-show="statusMessage"
             :class="{
                 'bg-blue-900 border-cyan-400': statusType === 'info',
                 'bg-red-900 border-red-400': statusType === 'error',
                 'bg-green-900 border-green-400': statusType === 'success'
             }"
             class="p-4 rounded retro-border text-sm neon-text font-mono"
             x-text="statusMessage">
        </div>

        <!-- Placements List -->
        <!-- Add this just before the Placements List section in the sidebar -->
<div class="retro-card p-4">
    <button @click="showAllCode = true"
            class="w-full retro-button px-4 py-2 rounded text-sm">
        [ VIEW ALL CODE ]
    </button>
</div>
        <div x-show="textPlacements.length > 0" class="border-t border-cyan-400 pt-6">
            <h3 class="text-sm neon-text font-['Press_Start_2P'] mb-4">PLACED ELEMENTS:</h3>
            <div class="space-y-4">
                <template x-for="(placement, index) in textPlacements" :key="index">
                    <div class="retro-card p-4 rounded">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-mono neon-text" x-text="placement.text"></p>
                                <p class="text-xs text-cyan-400 font-mono">
                                    PG:<span x-text="placement.page"></span> |
                                    X:<span x-text="placement.x"></span> |
                                    Y:<span x-text="placement.y"></span>
                                    <span x-show="placement.type === 'text'"> |
                                        SIZE:<span x-text="placement.fontSize"></span>
                                    </span>
                                </p>
                            </div>
                            <button @click="removeText(index)"
                                    class="text-red-400 hover:text-red-300 text-sm retro-button px-2 py-1">
                                [DEL]
                            </button>
                        </div>
                        <div class="text-xs mt-3">
                            <pre><code class="language-php rounded bg-gray-900" x-text="generateCode(placement)"></code></pre>
                            <button @click="copyCode(placement)"
                                    class="mt-2 retro-button px-3 py-1 text-xs rounded">
                                [ COPY CODE ]
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
<!-- Add this at the end of the body, before the closing </body> tag -->
<div x-show="showAllCode"
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="retro-card p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm neon-text font-['Press_Start_2P']">ALL PLACEMENT CODE</h3>
            <button @click="showAllCode = false" class="retro-button px-2 py-1">
                [ CLOSE ]
            </button>
        </div>
        <pre><code class="language-php" x-text="generateAllCode()"></code></pre>
        <button @click="copyAllCode()"
                class="mt-4 w-full retro-button px-4 py-2 rounded text-sm">
            [ COPY ALL CODE ]
        </button>
    </div>
</div>

        <!-- Right Content Area -->
        <div class="flex-1 h-screen overflow-y-auto bg-gray-50">
    <!-- Page Controls -->
    <div x-show="pdfLoaded" class="sticky top-0 retro-bg border-b border-cyan-400 p-4 z-10">
        <div class="flex items-center justify-between max-w-3xl mx-auto">
            <span class="font-['Press_Start_2P'] text-sm text-cyan-400">PAGE <span x-text="pageNum"></span> / <span x-text="pageCount"></span></span>
            <div class="space-x-3">
                <button @click="prevPage"
                        :disabled="pageNum <= 1"
                        class="px-4 py-2 retro-button rounded disabled:opacity-30 disabled:cursor-not-allowed text-sm">
                    [ PREV ]
                </button>
                <button @click="nextPage"
                        :disabled="pageNum >= pageCount"
                        class="px-4 py-2 retro-button rounded disabled:opacity-30 disabled:cursor-not-allowed text-sm">
                    [ NEXT ]
                </button>
            </div>
        </div>
    </div>

    <!-- PDF Display -->
    <div class="relative bg-white p-4">
        <div class="max-w-3xl mx-auto">
            <div class="relative bg-white rounded-lg shadow-lg border-2 border-cyan-400 overflow-hidden"
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
        previewStyle: '',
        selectedFont: 'Helvetica',

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

        showAllCode: false,

        generateAllCode() {
            return this.textPlacements.map(placement => {
                return this.generateCode(placement);
            }).join('\n\n');
        },

        copyAllCode() {
            const code = this.generateAllCode();
            navigator.clipboard.writeText(code).then(() => {
                this.statusMessage = 'All code copied to clipboard!';
                this.statusType = 'success';
                setTimeout(() => {
                    if (this.statusMessage === 'All code copied to clipboard!') {
                        this.statusMessage = '';
                    }
                }, 2000);
            });
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

            const fontFamily = {
                'Helvetica': 'Arial, sans-serif',
                'Times-Roman': 'Times New Roman, serif',
                'Courier': 'Courier New, monospace',
                'Symbol': 'Symbol',
                'ZapfDingbats': 'ZapfDingbats'
            }[this.selectedFont] || 'Arial, sans-serif';

            const fontWeight = this.inputType === 'checkbox' ? 'bold' : 'normal';
            this.previewStyle = `left: ${x}px; top: ${y}px; font-size: ${this.fontSize * 1.5}px; font-family: ${fontFamily}; font-weight: ${fontWeight};`;
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

            // Convert screen coordinates to FPDI coordinates (multiplied by 0.3528 to convert pixels to points)
            const pdfX = Math.round(x * 0.3528);
            const pdfY = Math.round(y * 0.3528);

            this.textPlacements.push({
                id: Date.now(),
                text: this.inputType === 'checkbox' ? 'X' : this.textToAdd,
                x: pdfX,
                y: pdfY,
                page: this.pageNum,
                screenX: x,
                screenY: y,
                fontSize: this.fontSize,
                type: this.inputType,
                font: this.selectedFont,
            });

            setTimeout(() => Prism.highlightAll(), 100);
        },

        removeText(index) {
            this.textPlacements.splice(index, 1);
        },

        getPlacementStyle(placement) {
            const fontFamily = {
                'Helvetica': 'Arial, sans-serif',
                'Times-Roman': 'Times New Roman, serif',
                'Courier': 'Courier New, monospace',
            }[placement.font] || 'Arial, sans-serif';

            const fontWeight = placement.type === 'checkbox' ? 'bold' : 'normal';
            return `left: ${placement.screenX}px; top: ${placement.screenY}px; font-size: ${placement.fontSize * 1.5}px; font-family: ${fontFamily}; font-weight: ${fontWeight};`;
        },

        generateCode(placement) {
            let pageCode = placement.page > 1 ? `$tplIdx = $pdf->importPage(${placement.page});\n$pdf->AddPage();\n$pdf->useTemplate($tplIdx);\n` : '';

            if (placement.type === 'checkbox') {
                return `${pageCode}$pdf->SetFont('${placement.font}', 'B', ${placement.fontSize});\n$pdf->SetXY(${placement.x}, ${placement.y});\n$pdf->Write(0, 'X');`;
            } else {
                return `${pageCode}$pdf->SetFont('${placement.font}', '', ${placement.fontSize});\n$pdf->SetXY(${placement.x}, ${placement.y});\n$pdf->Write(0, '${placement.text}');`;
            }
        }

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
