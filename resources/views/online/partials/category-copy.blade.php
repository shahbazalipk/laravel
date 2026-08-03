{{-- Category description + instruction fields configured in admin --}}
@if(filled($category->description))
    <p class="mt-1 text-sm text-slate-500" data-testid="category-description-{{ $category->id }}">
        {{ $category->description }}
    </p>
@endif

@if(filled($category->instructions_text))
    <p class="mt-2 text-sm font-medium text-slate-800" data-testid="category-instructions-text-{{ $category->id }}">
        {!! nl2br(e($category->instructions_text)) !!}
    </p>
@endif

@if(filled($category->instruction_description))
    <p class="mt-1 text-sm leading-relaxed text-slate-600" data-testid="category-instruction-description-{{ $category->id }}">
        {!! nl2br(e($category->instruction_description)) !!}
    </p>
@endif
