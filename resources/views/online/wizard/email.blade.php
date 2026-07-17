@extends('online.wizard.layout')

@section('title', 'Email')

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Start with your email</h2>
        <p class="mt-1 text-sm text-slate-500">We’ll save your progress so you can refresh or return later.</p>
    </div>

    @if(empty($awaitingVerification))
        <form method="POST"
              action="{{ $wizardStepRoute('email', 'store') }}"
              class="space-y-5"
              data-testid="wizard-email-form">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email address *</label>
                <input id="email"
                       type="email"
                       name="email"
                       value="{{ old('email', $draft?->email) }}"
                       required
                       autocomplete="email"
                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                       data-testid="wizard-email-input">
            </div>

            <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700 sm:w-auto"
                    data-testid="wizard-email-continue">
                Continue
            </button>
        </form>
    @else
        <div class="mb-6 rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
            Enter the 6-digit code sent to <strong>{{ $draft->email }}</strong>.
        </div>

        <form method="POST"
              action="{{ $wizardStepRoute('email', 'verify') }}"
              class="space-y-5"
              data-testid="wizard-otp-form">
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
                       data-testid="wizard-otp-input">
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700"
                        data-testid="wizard-otp-verify">
                    Verify & continue
                </button>
            </div>
        </form>

        <form method="POST" action="{{ $wizardStepRoute('email', 'resend') }}" class="mt-4">
            @csrf
            <button type="submit"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-800"
                    data-testid="wizard-otp-resend">
                Resend code
            </button>
        </form>
    @endif
@endsection
