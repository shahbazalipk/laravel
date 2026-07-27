@extends('admin.layout')

@section('title', 'MCP')

@section('content')
<div class="mb-6" data-testid="mcp-settings-page">
    <h1 class="text-2xl font-bold text-gray-800">MCP</h1>
    <p class="mt-1 text-gray-600">
        Connect automation tools (such as n8n) to this event’s read-only operations API.
    </p>
</div>

@if(session('mcp_plain_text_token'))
    <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm"
         role="status"
         data-testid="mcp-new-token-alert">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-amber-900">Copy this token now</p>
                <p class="mt-1 text-sm text-amber-800">
                    It will not be shown again. Use it as a Bearer token with the MCP URL below.
                </p>
                <code id="mcp-new-token-value"
                      class="mt-3 block break-all rounded-xl bg-white px-3 py-3 font-mono text-xs text-slate-900 ring-1 ring-amber-200"
                      data-testid="mcp-new-token-value">{{ session('mcp_plain_text_token') }}</code>
            </div>
            <button type="button"
                    onclick="copyText('mcp-new-token-value', this)"
                    class="inline-flex shrink-0 items-center justify-center rounded-lg bg-amber-700 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-amber-800"
                    data-testid="copy-mcp-new-token">
                Copy token
            </button>
        </div>
    </div>
@endif

<div class="mb-6 rounded-xl bg-white p-6 shadow-sm" data-testid="mcp-url-card">
    <h2 class="text-lg font-semibold text-gray-900">MCP URL</h2>
    <p class="mt-1 text-sm text-gray-500">
        Use Streamable HTTP and send <span class="font-mono text-xs">Authorization: Bearer &lt;token&gt;</span>.
    </p>
    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <input id="mcp-url-input"
               type="text"
               readonly
               value="{{ $mcpUrl }}"
               class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 font-mono text-sm text-gray-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
               data-testid="mcp-url-input">
        <button type="button"
                onclick="copyText('mcp-url-input', this)"
                class="inline-flex shrink-0 items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700"
                data-testid="copy-mcp-url">
            Copy URL
        </button>
    </div>
    <p class="mt-3 text-xs text-gray-500">
        Scoped to event #{{ $event->id }} · organization #{{ $event->organization_id }}
    </p>
</div>

<div class="mb-6 rounded-xl bg-white p-6 shadow-sm" data-testid="mcp-generate-card">
    <h2 class="text-lg font-semibold text-gray-900">Generate access token</h2>
    <p class="mt-1 text-sm text-gray-500">
        Tokens are event-scoped and can only read operational data for this event.
    </p>

    <form method="POST"
          action="{{ route('admin.mcp.tokens.store') }}"
          class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
          data-testid="mcp-generate-form">
        @csrf

        <div class="sm:col-span-2">
            <label for="mcp_token_name" class="block text-sm font-medium text-gray-700">Name</label>
            <input id="mcp_token_name"
                   name="name"
                   type="text"
                   required
                   maxlength="100"
                   value="{{ old('name', 'n8n') }}"
                   placeholder="e.g. n8n production"
                   class="mt-1.5 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('name') border-red-400 @enderror"
                   data-testid="mcp-token-name">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="mcp_expires_in_days" class="block text-sm font-medium text-gray-700">Expires in (days)</label>
            <input id="mcp_expires_in_days"
                   name="expires_in_days"
                   type="number"
                   min="1"
                   max="3650"
                   value="{{ old('expires_in_days') }}"
                   placeholder="Never"
                   class="mt-1.5 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('expires_in_days') border-red-400 @enderror"
                   data-testid="mcp-token-expires">
            @error('expires_in_days')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-end">
            <button type="submit"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
                    data-testid="mcp-generate-submit">
                Generate token
            </button>
        </div>

        <div class="sm:col-span-2 lg:col-span-4">
            <p class="text-sm font-medium text-gray-700">Abilities</p>
            <label class="mt-2 inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox"
                       name="abilities[]"
                       value="registrations.read"
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                       {{ in_array('registrations.read', old('abilities', ['registrations.read']), true) ? 'checked' : '' }}
                       data-testid="mcp-ability-registrations-read">
                registrations.read — registration, payment, and check-in summaries
            </label>
            @error('abilities')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </form>
</div>

<div class="overflow-hidden rounded-xl bg-white shadow-sm" data-testid="mcp-tokens-table">
    <div class="border-b border-gray-100 px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-900">Existing tokens</h2>
        <p class="mt-1 text-sm text-gray-500">Plaintext values are never stored. Revoke any token that may be compromised.</p>
    </div>

    @if($tokens->isEmpty())
        <div class="px-6 py-12 text-center text-sm text-gray-500" data-testid="mcp-tokens-empty">
            No MCP tokens yet for this event.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Abilities</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Last used</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($tokens as $token)
                        <tr data-testid="mcp-token-row-{{ $token->id }}">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $token->name }}</div>
                                @if($token->expires_at)
                                    <div class="text-xs text-gray-500">Expires {{ $token->expires_at->format('M d, Y') }}</div>
                                @else
                                    <div class="text-xs text-gray-500">No expiry</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ implode(', ', $token->abilities ?? []) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($token->revoked_at)
                                    <span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800">Revoked</span>
                                @elseif($token->expires_at && $token->expires_at->isPast())
                                    <span class="inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Expired</span>
                                @else
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800">Active</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $token->last_used_at?->diffForHumans() ?? 'Never' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $token->created_at?->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                @if($token->revoked_at === null)
                                    <form method="POST"
                                          action="{{ route('admin.mcp.tokens.revoke', $token) }}"
                                          onsubmit="return confirm('Revoke this MCP token? Connected tools will stop working.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="font-medium text-red-600 transition hover:text-red-800"
                                                data-testid="revoke-mcp-token-{{ $token->id }}">
                                            Revoke
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    async function copyText(elementId, button) {
        const el = document.getElementById(elementId);
        const value = el?.value ?? el?.textContent ?? '';
        if (!value) return;

        try {
            await navigator.clipboard.writeText(value.trim());
        } catch (e) {
            if (el?.select) {
                el.select();
                document.execCommand('copy');
            }
        }

        const original = button.textContent;
        button.textContent = 'Copied!';
        setTimeout(() => { button.textContent = original; }, 1600);
    }
</script>
@endpush
