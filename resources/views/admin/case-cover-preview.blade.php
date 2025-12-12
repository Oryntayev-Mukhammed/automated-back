@php
    $resolved = old('case.cover_image')
        ?? ($case['cover_image'] ?? null)
        ?? ($translations['en']['cover_image'] ?? null)
        ?? ($translations['de']['cover_image'] ?? null);
@endphp

<div style="padding-left: 16px; display: flex; align-items: flex-start; justify-content: flex-start;">
    @if(!empty($resolved))
        <div style="width: 220px; height: 130px; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: #f9fafb;">
            <img src="{{ $resolved }}" alt="Cover preview" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
    @else
        <div style="width: 220px; height: 130px; border: 1px dashed #e5e7eb; border-radius: 8px; background: #f9fafb; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 13px;">
            No cover image
        </div>
    @endif
</div>
