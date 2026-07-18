@extends('online.wizard.layout')

@section('title', 'Information')

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Registration information</h2>
        <p class="mt-1 text-sm text-slate-500">Tell us about yourself. You can refresh this page without losing progress.</p>
    </div>

    <form method="POST"
          action="{{ $wizardStepRoute('information', 'store') }}"
          enctype="multipart/form-data"
          class="space-y-6"
          data-testid="wizard-information-form">
        @csrf

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
                        <input id="fileInput" type="file" name="profile_picture" accept="image/*" class="hidden" data-testid="wizard-profile-upload">
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
                       data-testid="wizard-first-name">
            </div>
            <div>
                <label for="last_name" class="mb-2 block text-sm font-medium text-slate-700">Last name *</label>
                <input id="last_name" name="last_name" type="text" required
                       value="{{ old('last_name', $payload['last_name'] ?? '') }}"
                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                       data-testid="wizard-last-name">
            </div>
            <div>
                <label for="phone" class="mb-2 block text-sm font-medium text-slate-700">Phone *</label>
                <input id="phone" name="phone" type="tel" required
                       value="{{ old('phone', $payload['phone'] ?? '') }}"
                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                       data-testid="wizard-phone">
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
                       data-testid="wizard-company-name">
            </div>
            <div>
                <label for="industry_id" class="mb-2 block text-sm font-medium text-slate-700">Industry *</label>
                <select id="industry_id" name="industry_id" required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                        data-testid="wizard-industry">
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

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-between">
            <a href="{{ $wizardStepRoute('category') }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Back
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700"
                    data-testid="wizard-information-continue">
                Save & continue
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
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
@endpush
