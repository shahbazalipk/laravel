<h2 class="text-lg font-semibold text-gray-800 mb-4">Registration Settings</h2>

<div class="space-y-4">
        <label class="flex items-center">
            <input type="checkbox" 
                   name="registration_form_active" 
                   value="1"
                   {{ old('registration_form_active', $event->registration_form_active) ? 'checked' : '' }}
                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <span class="ml-2 text-sm text-gray-700">Registration Form Active</span>
        </label>

        <label class="flex items-center">
            <input type="checkbox" 
                   name="email_verification_required" 
                   value="1"
                   {{ old('email_verification_required', $event->email_verification_required) ? 'checked' : '' }}
                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <span class="ml-2 text-sm text-gray-700">Email Verification Required</span>
        </label>

        <label class="flex items-center">
            <input type="checkbox" 
                   name="code_verification_required" 
                   value="1"
                   {{ old('code_verification_required', $event->code_verification_required) ? 'checked' : '' }}
                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <span class="ml-2 text-sm text-gray-700">Code Verification Required</span>
        </label>

        <label class="flex items-center">
            <input type="checkbox" 
                   name="bulk_print_enabled" 
                   value="1"
                   {{ old('bulk_print_enabled', $event->bulk_print_enabled) ? 'checked' : '' }}
                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <span class="ml-2 text-sm text-gray-700">Bulk Print Enabled</span>
        </label>

        <label class="flex items-center">
            <input type="checkbox" 
                   name="reprint_enabled" 
                   value="1"
                   {{ old('reprint_enabled', $event->reprint_enabled) ? 'checked' : '' }}
                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <span class="ml-2 text-sm text-gray-700">Reprint Enabled</span>
        </label>

        <label class="flex items-center">
            <input type="checkbox" 
                   name="show_info_on_portal_background" 
                   value="1"
                   {{ old('show_info_on_portal_background', $event->show_info_on_portal_background) ? 'checked' : '' }}
                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <span class="ml-2 text-sm text-gray-700">Show Info on Portal Background</span>
        </label>
    </div>
