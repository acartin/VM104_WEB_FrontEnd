@php
    $icon = $getRecord()->source?->icon ?? 'help-circle';
    $name = $getState();
@endphp

<div class="flex items-center gap-2 py-1 text-gray-950 dark:text-white">
    {{-- Lucide Icon Rendered by JS --}}
    <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
    
    <span class="text-sm font-bold">{{ $name }}</span>
</div>

{{-- Ensure Lucide icons are re-initialized in case of livewire updates --}}
<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
