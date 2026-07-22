@extends('admin.layout')

@section('title', 'Check-In')

@section('content')
<!-- Header -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Check-In</h1>
    <p class="text-gray-600 mt-1">Scan QR codes or search registrations to check in attendees</p>
</div>

<!-- Search Section -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- QR Code Scanner -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Scan QR Code
            </label>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                </svg>
                <p class="text-gray-600 mb-4">Position QR code in front of camera</p>
                <button type="button" id="startScanner" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
                    Start Scanner
                </button>
            </div>
            <div id="qrReader" class="mt-4 hidden"></div>
        </div>

        <!-- Manual Search -->
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                Search by Name, Email, or Registration Number
            </label>
            <form action="{{ route('admin.registrations.checkin') }}" method="GET" class="space-y-4">
                <input type="text" 
                       name="search" 
                       id="search" 
                       value="{{ request('search') }}"
                       placeholder="Enter name, email, or registration number"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
                    Search
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Registrations</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-indigo-100 p-3 rounded-lg">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Checked In</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['checked_in'] }}</p>
            </div>
            <div class="bg-green-100 p-3 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Not Checked In</p>
                <p class="text-2xl font-bold text-orange-600 mt-1">{{ $stats['not_checked_in'] }}</p>
            </div>
            <div class="bg-orange-100 p-3 rounded-lg">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Check-In Rate</p>
                <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $stats['rate'] }}%</p>
            </div>
            <div class="bg-indigo-100 p-3 rounded-lg">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Search Results -->
@if(isset($registrations) && $registrations->isNotEmpty())
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="p-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Search Results</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($registrations as $registration)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-medium text-gray-900">{{ $registration->registration_number }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-900">{{ $registration->first_name }} {{ $registration->last_name }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-600">{{ $registration->email }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-600">{{ $registration->registrationCategory->name ?? 'N/A' }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($registration->checked_in_at)
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Checked In
                            </span>
                        @else
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                Not Checked In
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        @if(!$registration->checked_in_at)
                            <form action="{{ route('admin.registrations.check-in', $registration) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                                    Check In
                                </button>
                            </form>
                        @else
                            <span class="text-sm text-gray-500">
                                Checked in {{ $registration->checked_in_at->diffForHumans() }}
                            </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@elseif(request('search'))
<div class="bg-white rounded-lg shadow-sm p-12 text-center">
    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
    </svg>
    <h3 class="text-lg font-semibold text-gray-800 mb-2">No Results Found</h3>
    <p class="text-gray-600">No registrations match your search criteria.</p>
</div>
@endif
@endsection

@section('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let html5QrCode;
    
    document.getElementById('startScanner').addEventListener('click', function() {
        const qrReader = document.getElementById('qrReader');
        qrReader.classList.remove('hidden');
        
        html5QrCode = new Html5Qrcode("qrReader");
        
        html5QrCode.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            },
            (decodedText, decodedResult) => {
                // QR code successfully scanned
                window.location.href = `/admin/registrations/checkin?qr=${encodeURIComponent(decodedText)}`;
            },
            (errorMessage) => {
                // Scanning error (can be ignored)
            }
        ).catch((err) => {
            console.error('Unable to start scanning', err);
            alert('Unable to access camera. Please check permissions.');
        });
        
        this.textContent = 'Stop Scanner';
        this.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
        this.classList.add('bg-red-600', 'hover:bg-red-700');
        
        this.onclick = function() {
            html5QrCode.stop().then(() => {
                qrReader.classList.add('hidden');
                this.textContent = 'Start Scanner';
                this.classList.remove('bg-red-600', 'hover:bg-red-700');
                this.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
            });
        };
    });
</script>
@endsection
