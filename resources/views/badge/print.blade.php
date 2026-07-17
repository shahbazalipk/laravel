<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Badge Printing - {{ $event->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body class="bg-gray-50">
    <!-- Event Header with Branding -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                @if($event->logo)
                    <img src="{{ storage_public_url($event->logo) }}" alt="{{ $event->name }}" class="h-20 mx-auto mb-4">
                @endif
                <h1 class="text-4xl font-bold">{{ $event->name }}</h1>
                @if($event->start_date && $event->end_date)
                    <p class="text-lg mt-2">
                        {{ \Carbon\Carbon::parse($event->start_date)->format('F d') }} - 
                        {{ \Carbon\Carbon::parse($event->end_date)->format('F d, Y') }}
                    </p>
                @endif
                @if($event->location)
                    <p class="text-sm mt-1 opacity-90">{{ $event->location }}</p>
                @endif
                <div class="mt-4">
                    <span class="px-4 py-2 bg-white bg-opacity-20 rounded-full text-sm font-semibold">
                        {{ $badgeUrl->name }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    @if($badgeUrl->enable_barcode_scanner)
                    <button onclick="switchTab('scanner')" 
                            id="tab-scanner"
                            class="tab-button active px-8 py-4 text-center border-b-2 font-medium text-sm">
                        <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                        QR Scanner
                    </button>
                    @endif
                    
                    @if($badgeUrl->enable_manual_input)
                    <button onclick="switchTab('manual')" 
                            id="tab-manual"
                            class="tab-button {{ !$badgeUrl->enable_barcode_scanner ? 'active' : '' }} px-8 py-4 text-center border-b-2 font-medium text-sm">
                        <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Manual Search
                    </button>
                    @endif
                    
                    <button onclick="switchTab('face')" 
                            id="tab-face"
                            class="tab-button px-8 py-4 text-center border-b-2 font-medium text-sm">
                        <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Face Recognition
                    </button>
                </nav>
            </div>
        </div>

        <!-- Tab Content -->
        
        <!-- QR Scanner Tab -->
        @if($badgeUrl->enable_barcode_scanner)
        <div id="content-scanner" class="tab-content">
            <div class="bg-white rounded-lg shadow-sm p-8">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Scan QR Code or Badge</h2>
                    <p class="text-gray-600">Point your camera at the QR code on the registration confirmation or badge</p>
                </div>

                <!-- Scanner Container -->
                <div class="max-w-2xl mx-auto">
                    <div id="qr-reader" class="rounded-lg overflow-hidden border-4 border-indigo-200"></div>
                    
                    <div class="mt-6 text-center">
                        <button id="start-scanner" onclick="startScanner()" 
                                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition font-semibold">
                            Start Scanner
                        </button>
                        <button id="stop-scanner" onclick="stopScanner()" 
                                class="hidden px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-semibold">
                            Stop Scanner
                        </button>
                    </div>

                    <!-- Scan Status -->
                    <div id="scan-status" class="mt-6 hidden">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-blue-800 text-center">
                                <svg class="animate-spin h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Manual Search Tab -->
        @if($badgeUrl->enable_manual_input)
        <div id="content-manual" class="tab-content {{ $badgeUrl->enable_barcode_scanner ? 'hidden' : '' }}">
            <div class="bg-white rounded-lg shadow-sm p-8">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Manual Search</h2>
                    <p class="text-gray-600">Search by email, phone, name, or registration number</p>
                </div>

                <div class="max-w-2xl mx-auto">
                    <div class="mb-6">
                        <label for="manual-search" class="block text-sm font-medium text-gray-700 mb-2">
                            Search Registration
                        </label>
                        <div class="flex gap-3">
                            <input type="text" 
                                   id="manual-search" 
                                   placeholder="Enter email, phone, name, or registration number..."
                                   class="flex-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <button onclick="manualSearch()" 
                                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition font-semibold">
                                Search
                            </button>
                        </div>
                    </div>

                    <!-- Search Results -->
                    <div id="manual-results" class="hidden">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Search Results</h3>
                        <div id="manual-results-container" class="space-y-3"></div>
                    </div>

                    <!-- No Results -->
                    <div id="manual-no-results" class="hidden">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <p class="text-sm text-yellow-800">No registrations found. Please try a different search term.</p>
                        </div>
                    </div>

                    <!-- Loading -->
                    <div id="manual-loading" class="hidden">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-blue-800 text-center">
                                <svg class="animate-spin h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Searching...
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Face Recognition Tab -->
        <div id="content-face" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-sm p-8">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Face Recognition</h2>
                    <p class="text-gray-600">Position your face in front of the camera for automatic detection</p>
                </div>

                <!-- Camera Container -->
                <div class="max-w-2xl mx-auto">
                    <div class="relative">
                        <video id="face-video" autoplay class="w-full rounded-lg bg-gray-900 border-4 border-indigo-200"></video>
                        <canvas id="face-canvas" class="hidden"></canvas>
                        
                        <!-- Face Detection Overlay -->
                        <div id="face-overlay" class="absolute inset-0 pointer-events-none">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-64 h-64 border-4 border-green-500 rounded-full opacity-50"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 text-center">
                        <button id="start-face" onclick="startFaceDetection()" 
                                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition font-semibold">
                            Start Face Detection
                        </button>
                        <button id="stop-face" onclick="stopFaceDetection()" 
                                class="hidden px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-semibold">
                            Stop Detection
                        </button>
                    </div>

                    <!-- Detection Status -->
                    <div id="face-status" class="mt-6 hidden">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <p class="text-sm text-green-800 text-center font-semibold">
                                Face detected! Searching database...
                            </p>
                        </div>
                    </div>

                    <!-- Face Results -->
                    <div id="face-results" class="mt-6 hidden">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Possible Matches</h3>
                        <div id="face-results-container" class="space-y-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .tab-button {
            color: #6B7280;
            border-color: transparent;
            transition: all 0.2s;
        }
        .tab-button:hover {
            color: #4F46E5;
            border-color: #E5E7EB;
        }
        .tab-button.active {
            color: #4F46E5;
            border-color: #4F46E5;
        }
    </style>

    <script>
        let html5QrCode = null;
        let faceStream = null;
        let faceDetectionInterval = null;
        let isProcessing = false;
        let lastScanTime = 0;
        const SCAN_COOLDOWN = 3000; // 3 seconds cooldown between scans

        // Tab Switching
        function switchTab(tab) {
            // Update tab buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            document.getElementById('tab-' + tab).classList.add('active');

            // Update content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.getElementById('content-' + tab).classList.remove('hidden');

            // Stop any active processes
            if (tab !== 'scanner' && html5QrCode) {
                stopScanner();
            }
            if (tab !== 'face' && faceStream) {
                stopFaceDetection();
            }
        }

        // Manual Search Functions
        function manualSearch() {
            const search = document.getElementById('manual-search').value.trim();
            if (!search) return;

            document.getElementById('manual-loading').classList.remove('hidden');
            document.getElementById('manual-results').classList.add('hidden');
            document.getElementById('manual-no-results').classList.add('hidden');

            fetch('{{ route('badge.search', $badgeUrl->slug) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ search })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('manual-loading').classList.add('hidden');
                
                if (data.registrations.length === 0) {
                    document.getElementById('manual-no-results').classList.remove('hidden');
                    return;
                }

                displayManualResults(data.registrations);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('manual-loading').classList.add('hidden');
                showMessage('Search failed. Please try again.', 'error');
            });
        }

        function displayManualResults(registrations) {
            const container = document.getElementById('manual-results-container');
            const resultsDiv = document.getElementById('manual-results');
            
            resultsDiv.classList.remove('hidden');
            
            container.innerHTML = registrations.map(reg => `
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            ${reg.profile_picture ? 
                                `<img src="${reg.profile_picture}" alt="${reg.full_name}" class="w-16 h-16 rounded-full object-cover">` :
                                `<div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-2xl text-gray-500">${reg.full_name.charAt(0)}</span>
                                </div>`
                            }
                            <div>
                                <h4 class="font-semibold text-gray-900">${reg.full_name}</h4>
                                <p class="text-sm text-gray-600">${reg.email}</p>
                                <p class="text-sm text-gray-600">${reg.company_name || 'N/A'}</p>
                                <div class="flex gap-2 mt-1">
                                    <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded">${reg.category}</span>
                                    ${reg.badge_printed ? 
                                        `<span class="px-2 py-0.5 text-xs bg-green-100 text-green-800 rounded">Printed: ${reg.badge_printed_at}</span>` :
                                        `<span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-800 rounded">Not Printed</span>`
                                    }
                                </div>
                            </div>
                        </div>
                        <button onclick="printBadgeDirectly('${reg.hash}')" 
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                            Print Badge
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Allow search on Enter key
        document.addEventListener('DOMContentLoaded', function() {
            const manualSearchInput = document.getElementById('manual-search');
            if (manualSearchInput) {
                manualSearchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        manualSearch();
                    }
                });
            }
        });

        // QR Scanner Functions
        function startScanner() {
            const config = { fps: 10, qrbox: { width: 250, height: 250 } };
            
            html5QrCode = new Html5Qrcode("qr-reader");
            
            html5QrCode.start(
                { facingMode: "environment" },
                config,
                onScanSuccess,
                onScanError
            ).then(() => {
                document.getElementById('start-scanner').classList.add('hidden');
                document.getElementById('stop-scanner').classList.remove('hidden');
            }).catch(err => {
                console.error('Scanner error:', err);
                alert('Could not start scanner. Please check camera permissions.');
            });
        }

        function stopScanner() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    html5QrCode = null;
                    document.getElementById('start-scanner').classList.remove('hidden');
                    document.getElementById('stop-scanner').classList.add('hidden');
                    document.getElementById('scan-status').classList.add('hidden');
                }).catch(err => {
                    console.error('Stop error:', err);
                });
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Check if already processing or within cooldown period
            const now = Date.now();
            if (isProcessing || (now - lastScanTime) < SCAN_COOLDOWN) {
                return;
            }
            
            isProcessing = true;
            lastScanTime = now;
            
            console.log('Scanned:', decodedText);
            
            // Pause scanner during processing
            if (html5QrCode) {
                html5QrCode.pause(true);
            }
            
            document.getElementById('scan-status').classList.remove('hidden');
            
            try {
                // Try to parse as JSON (from QR code)
                const data = JSON.parse(decodedText);
                if (data.registration_number) {
                    // Search by registration number
                    searchAndPrint(data.registration_number);
                    return;
                }
            } catch (e) {
                // Not JSON, might be a URL or hash
            }
            
            // Extract hash from URL or use directly
            let searchTerm = decodedText;
            if (decodedText.includes('/confirmation/')) {
                searchTerm = decodedText.split('/confirmation/')[1];
            }
            
            // Search and print
            searchAndPrint(searchTerm);
        }

        function onScanError(errorMessage) {
            // Ignore scan errors (too noisy)
        }

        // Face Detection Functions
        function startFaceDetection() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    faceStream = stream;
                    const video = document.getElementById('face-video');
                    video.srcObject = stream;
                    
                    document.getElementById('start-face').classList.add('hidden');
                    document.getElementById('stop-face').classList.remove('hidden');
                    
                    // Start periodic face detection
                    faceDetectionInterval = setInterval(detectFace, 3000);
                })
                .catch(err => {
                    console.error('Camera error:', err);
                    alert('Could not access camera. Please check permissions.');
                });
        }

        function stopFaceDetection() {
            if (faceStream) {
                faceStream.getTracks().forEach(track => track.stop());
                faceStream = null;
            }
            if (faceDetectionInterval) {
                clearInterval(faceDetectionInterval);
                faceDetectionInterval = null;
            }
            
            document.getElementById('start-face').classList.remove('hidden');
            document.getElementById('stop-face').classList.add('hidden');
            document.getElementById('face-status').classList.add('hidden');
            document.getElementById('face-results').classList.add('hidden');
        }

        function detectFace() {
            const video = document.getElementById('face-video');
            const canvas = document.getElementById('face-canvas');
            const context = canvas.getContext('2d');
            
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0);
            
            // Show detection status
            document.getElementById('face-status').classList.remove('hidden');
            
            // In production, send image to face recognition API
            // For now, just search for registrations with photos
            fetch('{{ route('badge.search-face', $badgeUrl->slug) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('face-status').classList.add('hidden');
                
                if (data.registrations.length > 0) {
                    displayFaceResults(data.registrations);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('face-status').classList.add('hidden');
            });
        }

        function displayFaceResults(registrations) {
            const container = document.getElementById('face-results-container');
            const resultsDiv = document.getElementById('face-results');
            
            resultsDiv.classList.remove('hidden');
            
            container.innerHTML = registrations.map(reg => `
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            ${reg.profile_picture ? 
                                `<img src="${reg.profile_picture}" alt="${reg.full_name}" class="w-16 h-16 rounded-full object-cover">` :
                                `<div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-2xl text-gray-500">${reg.full_name.charAt(0)}</span>
                                </div>`
                            }
                            <div>
                                <h4 class="font-semibold text-gray-900">${reg.full_name}</h4>
                                <p class="text-sm text-gray-600">${reg.company_name || 'N/A'}</p>
                                <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded">${reg.category}</span>
                            </div>
                        </div>
                        <button onclick="printBadgeDirectly('${reg.hash}')" 
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                            Print Badge
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Print Badge Function
        function searchAndPrint(searchTerm) {
            // First search for the registration
            fetch('{{ route('badge.search', $badgeUrl->slug) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ search: searchTerm })
            })
            .then(response => response.json())
            .then(data => {
                if (data.registrations.length > 0) {
                    // Found registration, print the first match
                    printBadgeDirectly(data.registrations[0].hash);
                } else {
                    showMessage('Registration not found', 'error');
                    resetScanner();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Failed to search registration', 'error');
                resetScanner();
            });
        }

        function printBadgeDirectly(hash) {
            const printUrl = '{{ route('badge.print', ['slug' => $badgeUrl->slug, 'hash' => '__HASH__']) }}'.replace('__HASH__', hash);
            fetch(printUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.text();
                }
                throw new Error('Print failed');
            })
            .then(html => {
                // Open badge in new window for printing
                const printWindow = window.open('', '_blank');
                printWindow.document.write(html);
                printWindow.document.close();
                
                // Show success message
                showMessage('Badge sent to printer!', 'success');
                
                // Reset scanner after successful print
                resetScanner();
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Failed to print badge. Please try again.', 'error');
                resetScanner();
            });
        }

        function resetScanner() {
            // Hide status
            document.getElementById('scan-status').classList.add('hidden');
            
            // Resume scanner after cooldown
            setTimeout(() => {
                isProcessing = false;
                if (html5QrCode) {
                    html5QrCode.resume();
                }
            }, SCAN_COOLDOWN);
        }

        function showMessage(message, type) {
            const div = document.createElement('div');
            div.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white`;
            div.textContent = message;
            document.body.appendChild(div);
            
            setTimeout(() => {
                div.remove();
            }, 3000);
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (html5QrCode) stopScanner();
            if (faceStream) stopFaceDetection();
        });
    </script>
</body>
</html>
