# UI/UX Standards

This document defines the standard UI/UX patterns for all CRUD operations in the Laravel Event Manager. All new modules should follow these established patterns for consistency.

## Design System

### Color Palette
- **Primary**: Indigo (`#6366f1`, `indigo-600`)
- **Success**: Green (`#10b981`, `green-600`)
- **Warning**: Orange (`#f59e0b`, `orange-600`)
- **Danger**: Red (`#ef4444`, `red-600`)
- **Info**: Blue (`#3b82f6`, `blue-600`)
- **Gray Scale**: `gray-50` through `gray-900`

### Typography
- **Page Title**: `text-2xl font-bold text-gray-800`
- **Page Subtitle**: `text-gray-600 mt-1`
- **Section Headers**: `text-lg font-semibold text-gray-800`
- **Form Labels**: `text-sm font-medium text-gray-700 mb-2`
- **Helper Text**: `text-xs text-gray-400`
- **Table Headers**: `text-xs font-medium text-gray-500 uppercase tracking-wider`

### Spacing
- **Page Padding**: `p-6`
- **Card Padding**: `p-6`
- **Form Field Spacing**: `gap-6` (grid), `space-y-6` (stack)
- **Button Spacing**: `px-6 py-2` (standard), `px-4 py-2` (compact)

## Index Page Pattern

### Page Structure
```blade
@extends('admin.layout')

@section('title', 'Module Name')

@section('content')
<!-- Header Section -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Module Name</h1>
        <p class="text-gray-600 mt-1">Brief description of the module</p>
    </div>
    <a href="{{ route('admin.module.create') }}" 
       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add New Item
    </a>
</div>

<!-- Empty State OR Data Table -->
@if($items->isEmpty())
    <!-- Empty State -->
@else
    <!-- Data Table -->
@endif
@endsection
```

### Empty State
```blade
<div class="bg-white rounded-lg shadow-sm p-12 text-center">
    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <!-- Relevant icon -->
    </svg>
    <h3 class="text-lg font-semibold text-gray-800 mb-2">No Items Found</h3>
    <p class="text-gray-600 mb-4">Get started by creating your first item.</p>
    <a href="{{ route('admin.module.create') }}" 
       class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add First Item
    </a>
</div>
```

### Data Table
```blade
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Column Name
                </th>
                <!-- More columns -->
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($items as $item)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 whitespace-nowrap">
                    <!-- Cell content -->
                </td>
                <!-- More cells -->
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                        <!-- Action buttons -->
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Total Count -->
<div class="mt-4 text-sm text-gray-600">
    Total: {{ $items->count() }} item{{ $items->count() !== 1 ? 's' : '' }}
</div>
```

### Action Buttons (Table)
```blade
<!-- Edit Button -->
<a href="{{ route('admin.module.edit', $item) }}" 
   class="text-indigo-600 hover:text-indigo-900 transition">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
    </svg>
</a>

<!-- Delete Button -->
<form action="{{ route('admin.module.destroy', $item) }}" 
      method="POST" 
      onsubmit="return confirm('Are you sure you want to delete this item?');"
      class="inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="text-red-600 hover:text-red-900 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
    </button>
</form>
```

## Create/Edit Form Pattern

### Page Structure
```blade
@extends('admin.layout')

@section('title', 'Create/Edit Item')

@section('content')
<!-- Header with Back Button -->
<div class="mb-6">
    <div class="flex items-center mb-4">
        <a href="{{ route('admin.module.index') }}" 
           class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Create/Edit Item</h1>
            <p class="text-gray-600 mt-1">Form description</p>
        </div>
    </div>
</div>

<!-- Form Card -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.module.store') }}" method="POST">
        @csrf
        <!-- Form fields -->
    </form>
</div>
@endsection
```

### Form Field Types

#### Text Input
```blade
<div>
    <label for="field_name" class="block text-sm font-medium text-gray-700 mb-2">
        Field Label <span class="text-red-500">*</span>
    </label>
    <input type="text" 
           name="field_name" 
           id="field_name" 
           value="{{ old('field_name', $item->field_name ?? '') }}"
           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('field_name') border-red-500 @enderror"
           placeholder="Placeholder text"
           required>
    @error('field_name')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
```

#### Textarea
```blade
<div>
    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
        Description
    </label>
    <textarea name="description" 
              id="description" 
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror"
              placeholder="Optional description">{{ old('description', $item->description ?? '') }}</textarea>
    @error('description')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
```

#### Select Dropdown
```blade
<div>
    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
        Category <span class="text-red-500">*</span>
    </label>
    <select name="category_id" 
            id="category_id"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('category_id') border-red-500 @enderror"
            required>
        <option value="">Select a category</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" 
                    {{ old('category_id', $item->category_id ?? '') == $category->id ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    @error('category_id')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
```

#### Checkbox
```blade
<div>
    <label class="flex items-center">
        <input type="checkbox" 
               name="is_active" 
               value="1"
               {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}
               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
        <span class="ml-2 text-sm text-gray-700">Active (visible and usable)</span>
    </label>
</div>
```

#### Color Picker
```blade
<div>
    <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
        Color
    </label>
    <div class="flex items-center space-x-3">
        <input type="color" 
               name="color" 
               id="color" 
               value="{{ old('color', $item->color ?? '#6366f1') }}"
               class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
        <input type="text" 
               id="color-text"
               value="{{ old('color', $item->color ?? '#6366f1') }}"
               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
               readonly>
    </div>
    @error('color')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<script>
    const colorPicker = document.getElementById('color');
    const colorText = document.getElementById('color-text');
    colorPicker.addEventListener('input', function() {
        colorText.value = this.value;
    });
</script>
```

#### Date/Time Input
```blade
<div>
    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
        Start Time <span class="text-red-500">*</span>
    </label>
    <input type="datetime-local" 
           name="start_time" 
           id="start_time" 
           value="{{ old('start_time', $item->start_time?->format('Y-m-d\TH:i') ?? '') }}"
           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('start_time') border-red-500 @enderror"
           required>
    @error('start_time')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
```

#### Number Input
```blade
<div>
    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
        Sort Order
    </label>
    <input type="number" 
           name="sort_order" 
           id="sort_order" 
           value="{{ old('sort_order', $item->sort_order ?? 0) }}"
           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('sort_order') border-red-500 @enderror"
           placeholder="0">
    @error('sort_order')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
```

### Form Layout

#### Two-Column Grid
```blade
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Field 1 -->
    <div>...</div>
    <!-- Field 2 -->
    <div>...</div>
</div>
```

#### Full-Width Fields
```blade
<div class="mt-6">
    <!-- Full-width field like textarea -->
</div>
```

### Form Actions
```blade
<div class="mt-8 flex justify-end space-x-3">
    <a href="{{ route('admin.module.index') }}" 
       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
        Cancel
    </a>
    <button type="submit" 
            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
        Create/Update Item
    </button>
</div>
```

## Status Badges

### Active/Inactive Badge
```blade
@if($item->is_active)
    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
        Active
    </span>
@else
    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
        Inactive
    </span>
@endif
```

### Status Badge (Colored)
```blade
<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" 
      style="background-color: {{ $status->color }}20; color: {{ $status->color }}">
    {{ $status->name }}
</span>
```

## Visual Elements

### Color Indicator (Small Dot)
```blade
<div class="w-3 h-3 rounded-full" style="background-color: {{ $item->color }}"></div>
```

### Color Preview Box
```blade
<div class="w-8 h-8 rounded border-2 border-gray-200" style="background-color: {{ $item->color }}"></div>
```

### Icon Buttons
All icon buttons should use Heroicons (outline style) with consistent sizing:
- Table actions: `w-5 h-5`
- Page headers: `w-6 h-6`
- Large CTAs: `w-16 h-16`

## Interactive Elements

### Hover States
- Tables: `hover:bg-gray-50 transition`
- Buttons: `hover:bg-indigo-700 transition`
- Links: `hover:text-indigo-900 transition`

### Focus States
- Inputs: `focus:ring-2 focus:ring-indigo-500 focus:border-transparent`
- Buttons: `focus:outline-none focus:ring-2 focus:ring-indigo-500`

### Transitions
All interactive elements should have smooth transitions:
```blade
class="... transition"
```

## Flash Messages

Flash messages are handled in the layout (`admin.layout.blade.php`):

```blade
@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
        {{ session('error') }}
    </div>
@endif
```

## JavaScript Patterns

### Auto-Slug Generation
```javascript
const nameInput = document.getElementById('name');
const slugInput = document.getElementById('slug');

nameInput.addEventListener('input', function() {
    if (!slugInput.value || slugInput.dataset.autoGenerated) {
        const slug = this.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        slugInput.value = slug;
        slugInput.dataset.autoGenerated = 'true';
    }
});

slugInput.addEventListener('input', function() {
    if (this.value) {
        delete this.dataset.autoGenerated;
    }
});
```

### Color Picker Sync
```javascript
const colorPicker = document.getElementById('color');
const colorText = document.getElementById('color-text');

colorPicker.addEventListener('input', function() {
    colorText.value = this.value;
});
```

## Validation Patterns

### Controller Validation
```php
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'slug' => 'nullable|string|max:255',
    'color' => 'nullable|string|max:7',
    'description' => 'nullable|string',
    'is_active' => 'boolean',
    'sort_order' => 'nullable|integer',
]);
```

### Required Field Indicator
Always mark required fields with a red asterisk:
```blade
<label for="name" class="block text-sm font-medium text-gray-700 mb-2">
    Field Name <span class="text-red-500">*</span>
</label>
```

## Accessibility Guidelines

1. **Labels**: All form inputs must have associated labels
2. **Required Fields**: Use `required` attribute and visual indicator
3. **Error Messages**: Display validation errors below fields
4. **Focus Management**: Ensure logical tab order
5. **Color Contrast**: Maintain WCAG AA standards
6. **Alt Text**: Provide meaningful descriptions for icons (use aria-label when needed)

## Responsive Design

### Breakpoints
- Mobile: Default (< 768px)
- Tablet: `md:` (≥ 768px)
- Desktop: `lg:` (≥ 1024px)

### Grid Responsiveness
```blade
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Stacks on mobile, 2 columns on tablet+ -->
</div>
```

### Table Responsiveness
Tables should have horizontal scroll on mobile:
```blade
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <!-- Table content -->
        </table>
    </div>
</div>
```

## Performance Considerations

1. **Lazy Loading**: Use pagination for large datasets
2. **Eager Loading**: Load relationships in controllers to avoid N+1 queries
3. **Caching**: Cache frequently accessed data
4. **Asset Optimization**: Use CDN for Tailwind CSS in production

## Consistency Checklist

When creating a new CRUD module, ensure:

- [ ] Index page has header with title, subtitle, and "Add New" button
- [ ] Empty state is implemented with icon and CTA
- [ ] Data table uses standard styling and hover effects
- [ ] Action buttons (edit/delete) are in the rightmost column
- [ ] Create/Edit forms have back button in header
- [ ] Form fields follow standard patterns (labels, validation, error display)
- [ ] Form actions (Cancel/Submit) are right-aligned
- [ ] Success/error messages use flash sessions
- [ ] All interactive elements have hover and focus states
- [ ] Color scheme uses indigo as primary color
- [ ] Responsive design works on mobile, tablet, and desktop
- [ ] JavaScript enhancements are progressive (work without JS)
- [ ] Validation messages are clear and helpful
- [ ] Confirmation dialogs for destructive actions
- [ ] Total count displayed below tables

## Reference Implementation

The **Registration Statuses** module serves as the reference implementation for all these patterns. Refer to:
- `resources/views/admin/registration-statuses/index.blade.php`
- `resources/views/admin/registration-statuses/create.blade.php`
- `resources/views/admin/registration-statuses/edit.blade.php`
- `app/Http/Controllers/Admin/RegistrationStatusController.php`
- `app/Services/RegistrationStatusService.php`
- `app/Models/RegistrationStatus.php`
