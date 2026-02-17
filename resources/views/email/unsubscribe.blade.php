<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe from Email Communications</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            @if(isset($success) && $success)
                <!-- Success State -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Unsubscribed Successfully</h1>
                    <p class="text-gray-600">You have been unsubscribed from our email list.</p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <p class="text-gray-700 mb-4">
                        The email address <span class="font-semibold text-gray-900">{{ $email }}</span> has been removed from our mailing list.
                    </p>
                    <p class="text-gray-600 text-sm">
                        You will no longer receive email communications from us. This change is effective immediately.
                    </p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-900">
                        If you unsubscribed by mistake, please contact us to resubscribe.
                    </p>
                </div>
            @elseif(isset($error))
                <!-- Error State -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 rounded-full mb-4">
                        <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Invalid Link</h1>
                    <p class="text-gray-600">{{ $error }}</p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <p class="text-gray-700 mb-4">
                        The unsubscribe link you used is invalid or has expired.
                    </p>
                    <p class="text-gray-600 text-sm">
                        Please use the unsubscribe link from the most recent email you received, or contact us for assistance.
                    </p>
                </div>
            @else
                <!-- Unsubscribe Form -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-orange-100 rounded-full mb-4">
                        <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Unsubscribe</h1>
                    <p class="text-gray-600">We're sorry to see you go</p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    @if($errors->any())
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <p class="text-gray-700 mb-4">
                        You are about to unsubscribe the email address:
                    </p>
                    <p class="text-xl font-semibold text-gray-900 mb-6">{{ $email }}</p>

                    <form action="{{ route('email.unsubscribe.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="hash" value="{{ $hash }}">

                        <div class="mb-6">
                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Reason for unsubscribing (optional)
                            </label>
                            <select name="reason" 
                                    id="reason"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">Select a reason</option>
                                <option value="too_many_emails">Too many emails</option>
                                <option value="not_relevant">Content not relevant</option>
                                <option value="never_signed_up">I never signed up</option>
                                <option value="privacy_concerns">Privacy concerns</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <button type="submit" 
                                class="w-full px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            Confirm Unsubscribe
                        </button>
                    </form>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <p class="text-sm text-gray-600">
                        By clicking "Confirm Unsubscribe", you will no longer receive email communications from us. This action is effective immediately.
                    </p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
