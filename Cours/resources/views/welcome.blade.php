<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Éditeur de texte</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- PDF.js pour la conversion PDF -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tesseract.js/4.1.1/tesseract.min.js"></script>

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                background-color: #f5f5f5;
                font-family: 'Figtree', sans-serif;
            }

            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 2rem;
            }

            #editor-container {
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                margin: 2rem auto;
                padding: 2rem;
                min-height: 600px;
                max-height: 800px;
                overflow-y: auto;
            }

            .tools {
                display: flex;
                gap: 1rem;
                flex-wrap: wrap;
                padding: 1rem;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
                margin-bottom: 1.5rem;
            }

            .tools select, .tools button, .tools input {
                padding: 0.5rem 1rem;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                background: white;
                transition: all 0.2s;
            }

            .tools button {
                min-width: 40px;
                font-weight: bold;
            }

            .tools button:hover {
                background: #f1f5f9;
                transform: translateY(-1px);
            }

            .tools button.active {
                background: #e2e8f0;
                color: #1a365d;
            }

            #text-editor {
                width: 100%;
                min-height: 500px;
                padding: 1.5rem;
                font-size: 16px;
                line-height: 1.6;
                border: none;
                outline: none;
                resize: vertical;
                background: white;
                border-radius: 6px;
                color: #000000;
                overflow-y: auto;
            }

            #text-editor img {
                max-width: 100%;
                height: auto;
                display: block;
                margin: 1rem 0;
            }

            .preview {
                background: white;
                border-radius: 8px;
                padding: 2rem;
                margin-top: 2rem;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                min-height: 200px;
                max-height: 800px;
                overflow-y: auto;
            }

            .pdf-preview {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 2rem;
            }

            .pdf-page {
                width: 100%;
                max-width: 800px;
                background: white;
                padding: 2rem;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                border-radius: 8px;
            }

            .pdf-navigation {
                display: flex;
                gap: 1rem;
                margin: 1rem 0;
            }

            .pdf-navigation button {
                padding: 0.5rem 1rem;
                background: #3b82f6;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                transition: background 0.2s;
            }

            .pdf-navigation button:hover {
                background: #2563eb;
            }

            .pdf-navigation button:disabled {
                background: #9ca3af;
                cursor: not-allowed;
            }

            .page-indicator {
                padding: 0.5rem 1rem;
                background: #f3f4f6;
                border-radius: 4px;
                font-weight: 500;
            }

            #validate-btn {
                background: #3b82f6;
                color: white;
                padding: 0.75rem 2rem;
                border-radius: 8px;
                border: none;
                font-weight: 600;
                transition: all 0.2s;
            }

            #validate-btn:hover {
                background: #2563eb;
                transform: translateY(-1px);
            }

            .dark-mode {
                background: #1a1a1a;
                color: #ffffff;
            }

            .dark-mode #editor-container,
            .dark-mode .tools,
            .dark-mode .preview {
                background: #2d2d2d;
                border-color: #404040;
            }

            .dark-mode #text-editor {
                background: #2d2d2d;
                color: #ffffff;
            }

            .dark-mode .tools select,
            .dark-mode .tools button {
                background: #404040;
                color: #ffffff;
                border-color: #505050;
            }

            .dark-mode .tools button:hover {
                background: #505050;
            }

            /* Style amélioré pour le sélecteur de couleur */
            #text-color {
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                width: 40px;
                height: 40px;
                padding: 0;
                border: 2px solid #e2e8f0;
                border-radius: 50%;
                cursor: pointer;
                overflow: hidden;
            }

            #text-color::-webkit-color-swatch-wrapper {
                padding: 0;
            }

            #text-color::-webkit-color-swatch {
                border: none;
                border-radius: 50%;
            }

            #text-color::-moz-color-swatch {
                border: none;
                border-radius: 50%;
            }

            #text-color:hover {
                transform: scale(1.1);
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }

            .pdf-page {
                margin-bottom: 20px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }

            #pdf-container {
                width: 100%;
                margin: 1rem 0;
            }

            .pdf-canvas {
                width: 100%;
                height: auto;
            }
        </style>
    </head>
    <body class="bg-gray-100 dark:bg-gray-900">
        <div class="container mx-auto px-4 py-8">
            <header class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Éditeur de texte</h1>
                @if (Route::has('login'))
                    <nav class="space-x-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Connexion</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Inscription</a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </header>

            <main>
                <div class="tools">
                    <select id="font-family" class="px-4 py-2 rounded border">
                        <option value="Arial">Arial</option>
                        <option value="Times New Roman">Times New Roman</option>
                        <option value="Courier New">Courier New</option>
                        <option value="Georgia">Georgia</option>
                        <option value="Verdana">Verdana</option>
                    </select>
                    <input type="number" id="font-size" class="px-4 py-2 rounded border" value="16" min="1" max="100">
                    <button id="bold-btn" class="tool-btn" title="Gras">B</button>
                    <button id="italic-btn" class="tool-btn" title="Italique">I</button>
                    <button id="underline-btn" class="tool-btn" title="Souligné">U</button>
                    <button id="align-left" class="tool-btn" title="Aligner à gauche">⫷</button>
                    <button id="align-center" class="tool-btn" title="Centrer">≣</button>
                    <button id="align-right" class="tool-btn" title="Aligner à droite">⫸</button>
                    <input type="color" id="text-color" value="#000000" title="Couleur du texte">
                    <input type="file" id="pdf-input" accept=".pdf" class="px-4 py-2 bg-gray-200 rounded">
                </div>

                <div id="editor-container">
                    <div id="pdf-container"></div>
                    <div id="text-editor" contenteditable="true" class="text-gray-800 dark:text-gray-200"></div>
                </div>

                <div class="flex justify-center mt-4">
                    <button id="validate-btn">
                        Valider
                    </button>
                </div>

                <div id="text-preview" class="preview">
                    <div class="pdf-preview">
                        <div class="pdf-navigation">
                            <button id="prev-page" disabled>Page précédente</button>
                            <span class="page-indicator">Page <span id="current-page">1</span> sur <span id="total-pages">1</span></span>
                            <button id="next-page">Page suivante</button>
                        </div>
                        <div id="preview-content" class="pdf-page">
                            <!-- Le contenu de la page sera inséré ici -->
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <script>
            const editor = document.getElementById('text-editor');
            const fontFamily = document.getElementById('font-family');
            const fontSize = document.getElementById('font-size');
            const boldBtn = document.getElementById('bold-btn');
            const italicBtn = document.getElementById('italic-btn');
            const underlineBtn = document.getElementById('underline-btn');
            const alignLeftBtn = document.getElementById('align-left');
            const alignCenterBtn = document.getElementById('align-center');
            const alignRightBtn = document.getElementById('align-right');
            const textColor = document.getElementById('text-color');
            const pdfContainer = document.getElementById('pdf-container');
            const prevPageBtn = document.getElementById('prev-page');
            const nextPageBtn = document.getElementById('next-page');
            const currentPageSpan = document.getElementById('current-page');
            const totalPagesSpan = document.getElementById('total-pages');
            const previewContent = document.getElementById('preview-content');
            
            let currentPage = 1;
            let pages = [];
            let pdfCanvases = [];

            // Fonction pour gérer l'état actif des boutons
            function toggleButtonState(button) {
                button.classList.toggle('active');
            }

            // Appliquer les styles de texte
            fontFamily.addEventListener('change', () => {
                document.execCommand('fontName', false, fontFamily.value);
            });

            fontSize.addEventListener('input', () => {
                document.execCommand('fontSize', false, fontSize.value);
            });

            boldBtn.addEventListener('click', () => {
                document.execCommand('bold', false, null);
                toggleButtonState(boldBtn);
            });

            italicBtn.addEventListener('click', () => {
                document.execCommand('italic', false, null);
                toggleButtonState(italicBtn);
            });

            underlineBtn.addEventListener('click', () => {
                document.execCommand('underline', false, null);
                toggleButtonState(underlineBtn);
            });

            alignLeftBtn.addEventListener('click', () => {
                document.execCommand('justifyLeft', false, null);
            });

            alignCenterBtn.addEventListener('click', () => {
                document.execCommand('justifyCenter', false, null);
            });

            alignRightBtn.addEventListener('click', () => {
                document.execCommand('justifyRight', false, null);
            });

            textColor.addEventListener('change', () => {
                document.execCommand('foreColor', false, textColor.value);
            });

            // Gestion du PDF
            document.getElementById('pdf-input').addEventListener('change', async (e) => {
                const file = e.target.files[0];
                if (file && file.type === 'application/pdf') {
                    const reader = new FileReader();
                    reader.onload = async function(event) {
                        const pdfData = new Uint8Array(event.target.result);
                        const pdf = await pdfjsLib.getDocument({data: pdfData}).promise;
                        
                        // Vider le conteneur PDF
                        pdfContainer.innerHTML = '';
                        editor.innerHTML = '';
                        pdfCanvases = [];
                        
                        // Afficher toutes les pages
                        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                            const page = await pdf.getPage(pageNum);
                            const canvas = document.createElement('canvas');
                            canvas.className = 'pdf-canvas';
                            pdfContainer.appendChild(canvas);
                            
                            const viewport = page.getViewport({scale: 1.5});
                            canvas.width = viewport.width;
                            canvas.height = viewport.height;
                            
                            const context = canvas.getContext('2d');
                            await page.render({
                                canvasContext: context,
                                viewport: viewport
                            }).promise;

                            pdfCanvases.push(canvas.toDataURL());

                            // Extraire le texte
                            const textContent = await page.getTextContent();
                            const text = textContent.items.map(item => item.str).join(' ');
                            
                            if (pageNum === 1) {
                                editor.innerHTML = text;
                            } else {
                                editor.innerHTML += '<br><br>' + text;
                            }
                        }
                    };
                    reader.readAsArrayBuffer(file);
                }
            });

            // Gestion du collage d'images
            editor.addEventListener('paste', (e) => {
                e.preventDefault();
                const items = (e.clipboardData || e.originalEvent.clipboardData).items;

                for (let item of items) {
                    if (item.type.indexOf('image') === 0) {
                        const blob = item.getAsFile();
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            const img = document.createElement('img');
                            img.src = event.target.result;
                            document.execCommand('insertHTML', false, img.outerHTML);
                        };
                        reader.readAsDataURL(blob);
                    }
                }
            });

            // Validation et affichage du contenu
            document.getElementById('validate-btn').addEventListener('click', () => {
                const content = editor.innerHTML;
                const wordsPerPage = 300;
                const words = content.split(/\s+/);
                
                // Diviser le contenu en pages
                pages = [];
                for (let i = 0; i < words.length; i += wordsPerPage) {
                    pages.push(words.slice(i, i + wordsPerPage).join(' '));
                }
                
                // Mettre à jour l'affichage
                currentPage = 1;
                totalPagesSpan.textContent = pages.length;
                updatePageDisplay();
                
                // Activer/désactiver les boutons de navigation
                updateNavigationButtons();
            });

            function updatePageDisplay() {
                previewContent.innerHTML = '';
                
                // Ajouter l'image du PDF si disponible
                if (pdfCanvases[currentPage - 1]) {
                    const img = document.createElement('img');
                    img.src = pdfCanvases[currentPage - 1];
                    img.style.width = '100%';
                    img.style.marginBottom = '20px';
                    previewContent.appendChild(img);
                }
                
                // Ajouter le texte
                const textDiv = document.createElement('div');
                textDiv.innerHTML = pages[currentPage - 1];
                previewContent.appendChild(textDiv);
                
                currentPageSpan.textContent = currentPage;
            }

            function updateNavigationButtons() {
                prevPageBtn.disabled = currentPage === 1;
                nextPageBtn.disabled = currentPage === pages.length;
            }

            // Navigation entre les pages
            prevPageBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    updatePageDisplay();
                    updateNavigationButtons();
                }
            });

            nextPageBtn.addEventListener('click', () => {
                if (currentPage < pages.length) {
                    currentPage++;
                    updatePageDisplay();
                    updateNavigationButtons();
                }
            });

            // Empêcher la perte du focus lors du clic sur les boutons
            document.querySelectorAll('.tool-btn').forEach(button => {
                button.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                });
            });
        </script>
    </body>
</html>
