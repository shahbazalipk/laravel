<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    @include('online.partials.seo-meta')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50">
<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl">
        <div class="mb-6 overflow-hidden rounded-2xl bg-white shadow-lg">
            <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-6 text-white sm:px-8">
                <h1 class="text-2xl font-bold sm:text-3xl">{{ $event->event_name ?: $event->title }}</h1>
                <p class="mt-2 text-sm text-indigo-100 sm:text-base">
                    {{ $event->seo_description ?: ($event->social_media_description ?: 'Complete your registration below') }}
                </p>
            </div>

            @include('online.partials.event-details')
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" data-testid="flash-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" data-testid="flash-error">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" data-testid="flash-errors" role="alert">
                <ul class="list-disc space-y-1 pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($draft)
            <div class="mb-4 flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 sm:flex-row sm:items-center sm:justify-between"
                 data-testid="single-page-resume-banner"
                 role="status">
                <div class="min-w-0">
                    <p class="font-semibold">You have a registration in progress</p>
                    <p class="mt-0.5 text-amber-900/80">
                        @if($draft->email)
                            Continuing as <strong class="font-medium">{{ $draft->email }}</strong>.
                        @else
                            Your previous answers are restored on this form.
                        @endif
                        Prefer to begin again with a different email?
                    </p>
                </div>
                <a href="{{ route('online.registration.new', ['slug' => $slug]) }}"
                   class="inline-flex shrink-0 items-center justify-center rounded-xl border border-amber-300 bg-white px-4 py-2 text-sm font-semibold text-amber-900 shadow-sm transition hover:bg-amber-100"
                   data-testid="single-page-start-fresh">
                    Start fresh
                </a>
            </div>
        @endif

        <div class="rounded-2xl bg-white p-5 shadow-lg sm:p-8" data-testid="single-page-panel">
            @if(!empty($awaitingVerification) && $draft)
                <div class="mb-6 rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
                    Enter the 6-digit code sent to <strong>{{ $draft->email }}</strong> to finish registration.
                </div>

                <form method="POST" action="{{ $verifyRoute }}" class="space-y-5" data-testid="single-page-otp-form">
                    @csrf
                    <div>
                        <label for="otp" class="mb-2 block text-sm font-medium text-slate-700">Verification code *</label>
                        <input id="otp"
                               type="text"
                               name="otp"
                               inputmode="numeric"
                               pattern="[0-9]{6}"
                               maxlength="6"
                               required
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-center text-lg tracking-[0.4em] focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                               data-testid="single-page-otp-input">
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700"
                            data-testid="single-page-otp-verify">
                        Verify & complete registration
                    </button>
                </form>

                <form method="POST" action="{{ $resendRoute }}" class="mt-4">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-indigo-600 hover:text-indigo-800" data-testid="single-page-otp-resend">
                        Resend code
                    </button>
                </form>
            @else
                <form method="POST"
                      action="{{ $storeRoute }}"
                      enctype="multipart/form-data"
                      class="space-y-8"
                      data-testid="single-page-form">
                    @csrf

                    <section class="space-y-4" aria-labelledby="contact-heading">
                        <div>
                            <h2 id="contact-heading" class="text-lg font-semibold text-slate-900">Contact</h2>
                            <p class="mt-1 text-sm text-slate-500">We use your email for confirmation and updates.</p>
                        </div>
                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email address *</label>
                            <input id="email"
                                   type="email"
                                   name="email"
                                   value="{{ old('email', $draft?->email ?? ($payload['email'] ?? '')) }}"
                                   required
                                   autocomplete="email"
                                   class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                   data-testid="single-page-email">
                        </div>
                    </section>

                    <section class="space-y-4 border-t border-slate-100 pt-8" aria-labelledby="category-heading">
                        <div>
                            <h2 id="category-heading" class="text-lg font-semibold text-slate-900">Category & pricing</h2>
                            <p class="mt-1 text-sm text-slate-500">Totals include applicable VAT based on category and event settings.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-4" data-testid="single-page-category-list">
                            @forelse($categories as $category)
                                @php $pricing = $category->pricing; @endphp
                                <label class="block cursor-pointer rounded-2xl border border-slate-200 p-4 transition hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/40">
                                    <div class="flex items-start gap-3">
                                        <input type="radio"
                                               name="registration_category_id"
                                               value="{{ $category->id }}"
                                               class="mt-1 category-radio"
                                               required
                                               data-needs-password="{{ $category->needs_password ? '1' : '0' }}"
                                               data-needs-membership="{{ $category->need_membership_id ? '1' : '0' }}"
                                               data-needs-professional="{{ $category->need_professional_student_id ? '1' : '0' }}"
                                               data-base="{{ $pricing['base_price'] }}"
                                               data-tax="{{ $pricing['tax_amount'] }}"
                                               data-total="{{ $pricing['total_amount'] }}"
                                               data-currency="{{ $pricing['currency'] }}"
                                               {{ (string) old('registration_category_id', $payload['registration_category_id'] ?? '') === (string) $category->id ? 'checked' : '' }}
                                               data-testid="single-page-category-{{ $category->id }}">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                                <div>
                                                    <p class="font-semibold text-slate-900">{{ $category->name }}</p>
                                                    @if($category->description)
                                                        <p class="mt-1 text-sm text-slate-500">{{ $category->description }}</p>
                                                    @endif
                                                </div>
                                                <div class="text-left sm:text-right">
                                                    <p class="text-lg font-bold text-indigo-700">
                                                        {{ number_format($pricing['total_amount'], 2) }} {{ $pricing['currency'] }}
                                                    </p>
                                                    @if($pricing['tax_amount'] > 0)
                                                        <p class="text-xs text-slate-500">
                                                            Includes {{ number_format($pricing['tax_amount'], 2) }} {{ $pricing['currency'] }} VAT
                                                        </p>
                                                    @else
                                                        <p class="text-xs text-slate-500">No VAT applied</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @empty
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                    No registration categories are available for this link.
                                </div>
                            @endforelse
                        </div>

                        <div id="categoryExtraFields" class="hidden space-y-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div id="passwordField" class="hidden">
                                <label class="mb-1 block text-sm font-medium text-slate-700">Category password *</label>
                                <input type="password" name="category_password" value="{{ old('category_password') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div id="membershipField" class="hidden">
                                <label class="mb-1 block text-sm font-medium text-slate-700">Membership ID *</label>
                                <input type="text" name="membership_id" value="{{ old('membership_id', $payload['membership_id'] ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div id="professionalField" class="hidden">
                                <label class="mb-1 block text-sm font-medium text-slate-700">Professional / Student ID *</label>
                                <input type="text" name="professional_student_id" value="{{ old('professional_student_id', $payload['professional_student_id'] ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4 border-t border-slate-100 pt-8" aria-labelledby="information-heading">
                        <div>
                            <h2 id="information-heading" class="text-lg font-semibold text-slate-900">Your information</h2>
                            <p class="mt-1 text-sm text-slate-500">Tell us about yourself and your organization.</p>
                        </div>

                        <div>
                            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Profile picture</h3>
                            <div class="flex flex-col items-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5">
                                <div id="preview" class="{{ !empty($payload['profile_picture']) ? '' : 'hidden' }}">
                                    <img id="previewImage"
                                         src="{{ !empty($payload['profile_picture']) ? storage_public_url($payload['profile_picture']) : '' }}"
                                         class="h-28 w-28 rounded-full border-4 border-white object-cover shadow"
                                         alt="Preview">
                                </div>

                                <div id="cameraSection" class="hidden w-full max-w-md">
                                    <video id="video" class="w-full rounded-xl" autoplay playsinline></video>
                                    <canvas id="canvas" class="hidden"></canvas>
                                    <div class="mt-3 flex flex-wrap justify-center gap-3">
                                        <button type="button" id="captureBtn" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white">Capture Photo</button>
                                        <button type="button" id="closeCameraBtn" class="rounded-lg bg-slate-600 px-4 py-2 text-sm font-medium text-white">Close Camera</button>
                                    </div>
                                </div>

                                <div id="uploadOptions" class="flex flex-wrap justify-center gap-3">
                                    <button type="button" id="openCameraBtn" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700">Capture Photo</button>
                                    <label class="cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700">
                                        Upload Photo
                                        <input id="fileInput" type="file" name="profile_picture" accept="image/*" class="hidden" data-testid="single-page-profile-upload">
                                    </label>
                                </div>
                                <input type="hidden" name="profile_picture_data" id="profilePictureData">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label for="first_name" class="mb-2 block text-sm font-medium text-slate-700">First name *</label>
                                <input id="first_name" name="first_name" type="text" required
                                       value="{{ old('first_name', $payload['first_name'] ?? '') }}"
                                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                                       data-testid="single-page-first-name">
                            </div>
                            <div>
                                <label for="last_name" class="mb-2 block text-sm font-medium text-slate-700">Last name *</label>
                                <input id="last_name" name="last_name" type="text" required
                                       value="{{ old('last_name', $payload['last_name'] ?? '') }}"
                                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                                       data-testid="single-page-last-name">
                            </div>
                            <div>
                                <label for="phone" class="mb-2 block text-sm font-medium text-slate-700">Phone *</label>
                                <input id="phone" name="phone" type="tel" required
                                       value="{{ old('phone', $payload['phone'] ?? '') }}"
                                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                                       data-testid="single-page-phone">
                            </div>
                            <div>
                                <label for="job_title" class="mb-2 block text-sm font-medium text-slate-700">Job title</label>
                                <input id="job_title" name="job_title" type="text"
                                       value="{{ old('job_title', $payload['job_title'] ?? '') }}"
                                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                            </div>
                            <div>
                                <label for="company_name" class="mb-2 block text-sm font-medium text-slate-700">Company *</label>
                                <input id="company_name" name="company_name" type="text" required
                                       value="{{ old('company_name', $payload['company_name'] ?? '') }}"
                                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                                       data-testid="single-page-company-name">
                            </div>
                            <div>
                                <label for="industry_id" class="mb-2 block text-sm font-medium text-slate-700">Industry *</label>
                                <select id="industry_id" name="industry_id" required
                                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                                        data-testid="single-page-industry">
                                    <option value="">Select industry</option>
                                    @foreach($industries as $industry)
                                        <option value="{{ $industry->id }}"
                                            {{ (string) old('industry_id', $payload['industry_id'] ?? '') === (string) $industry->id ? 'selected' : '' }}>
                                            {{ $industry->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if($customForms->isNotEmpty())
                            <div class="space-y-5">
                                @include('online.partials.custom-registration-forms')
                            </div>
                        @endif
                    </section>

                    <section class="space-y-4 border-t border-slate-100 pt-8" aria-labelledby="confirm-heading">
                        <div>
                            <h2 id="confirm-heading" class="text-lg font-semibold text-slate-900">Confirm & submit</h2>
                            <p class="mt-1 text-sm text-slate-500">Review the total and accept the terms to finish.</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <p class="text-sm text-slate-500">Selected total</p>
                                    <p class="text-xs text-slate-500" id="selectedCategoryHint">Choose a category to see pricing.</p>
                                </div>
                                <p class="text-2xl font-bold text-indigo-700" data-testid="single-page-total" id="selectedTotal">—</p>
                            </div>
                        </div>

                        <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                            <input type="checkbox"
                                   name="terms_accepted"
                                   value="1"
                                   required
                                   class="mt-1"
                                   data-testid="single-page-terms"
                                   {{ old('terms_accepted') ? 'checked' : '' }}>
                            <span>I accept the terms and conditions *</span>
                        </label>

                        <div id="paymentNotice" class="hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            After you submit, your registration will remain pending until payment is confirmed by the event team.
                        </div>

                        <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700 sm:w-auto"
                                data-testid="single-page-submit"
                                @disabled($categories->isEmpty())>
                            Complete registration
                        </button>
                    </section>
                </form>
            @endif
        </div>

        @include('online.partials.event-url-content')
        @include('online.partials.contact-footer')
    </div>
</div>

<script>
    function syncCategoryExtras() {
        const selected = document.querySelector('.category-radio:checked');
        const wrap = document.getElementById('categoryExtraFields');
        const passwordField = document.getElementById('passwordField');
        const membershipField = document.getElementById('membershipField');
        const professionalField = document.getElementById('professionalField');
        const selectedTotal = document.getElementById('selectedTotal');
        const selectedCategoryHint = document.getElementById('selectedCategoryHint');
        const paymentNotice = document.getElementById('paymentNotice');
        const submitBtn = document.querySelector('[data-testid="single-page-submit"]');

        if (!selected) {
            wrap?.classList.add('hidden');
            if (selectedTotal) selectedTotal.textContent = '—';
            if (selectedCategoryHint) selectedCategoryHint.textContent = 'Choose a category to see pricing.';
            paymentNotice?.classList.add('hidden');
            return;
        }

        const needsPassword = selected.dataset.needsPassword === '1';
        const needsMembership = selected.dataset.needsMembership === '1';
        const needsProfessional = selected.dataset.needsProfessional === '1';
        passwordField?.classList.toggle('hidden', !needsPassword);
        membershipField?.classList.toggle('hidden', !needsMembership);
        professionalField?.classList.toggle('hidden', !needsProfessional);
        wrap?.classList.toggle('hidden', !(needsPassword || needsMembership || needsProfessional));

        const total = Number(selected.dataset.total || 0);
        const currency = selected.dataset.currency || '';
        if (selectedTotal) {
            selectedTotal.textContent = `${Number(total).toFixed(2)} ${currency}`.trim();
        }
        if (selectedCategoryHint) {
            selectedCategoryHint.textContent = total > 0
                ? `Base ${Number(selected.dataset.base || 0).toFixed(2)} + VAT ${Number(selected.dataset.tax || 0).toFixed(2)}`
                : 'No payment required for this category.';
        }
        paymentNotice?.classList.toggle('hidden', !(total > 0));
        if (submitBtn) {
            submitBtn.textContent = total > 0 ? 'Submit registration' : 'Complete registration';
        }
    }

    document.querySelectorAll('.category-radio').forEach((input) => {
        input.addEventListener('change', syncCategoryExtras);
    });
    syncCategoryExtras();

    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const captureBtn = document.getElementById('captureBtn');
    const openCameraBtn = document.getElementById('openCameraBtn');
    const closeCameraBtn = document.getElementById('closeCameraBtn');
    const fileInput = document.getElementById('fileInput');
    const preview = document.getElementById('preview');
    const previewImage = document.getElementById('previewImage');
    const cameraSection = document.getElementById('cameraSection');
    const uploadOptions = document.getElementById('uploadOptions');
    const profilePictureData = document.getElementById('profilePictureData');
    let stream = null;

    openCameraBtn?.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
            cameraSection.classList.remove('hidden');
            uploadOptions.classList.add('hidden');
        } catch (err) {
            alert('Unable to access camera: ' + err.message);
        }
    });

    closeCameraBtn?.addEventListener('click', () => {
        if (stream) stream.getTracks().forEach(track => track.stop());
        cameraSection.classList.add('hidden');
        uploadOptions.classList.remove('hidden');
    });

    captureBtn?.addEventListener('click', () => {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        const dataUrl = canvas.toDataURL('image/png');
        profilePictureData.value = dataUrl;
        previewImage.src = dataUrl;
        preview.classList.remove('hidden');
        if (stream) stream.getTracks().forEach(track => track.stop());
        cameraSection.classList.add('hidden');
        uploadOptions.classList.remove('hidden');
    });

    fileInput?.addEventListener('change', (event) => {
        const file = event.target.files?.[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.src = e.target.result;
            preview.classList.remove('hidden');
            profilePictureData.value = '';
        };
        reader.readAsDataURL(file);
    });
</script>
</body>
</html>
