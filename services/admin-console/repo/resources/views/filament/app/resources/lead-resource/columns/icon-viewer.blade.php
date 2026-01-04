<div class="flex items-center justify-center">
    @php
        $state = $getState();
        $icon = $icon ?? 'circle';
        $color = $color ?? '#9ca3af';
    @endphp

    <div class="inline-flex items-center justify-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium bg-gray-50 dark:bg-white/5 ring-1 ring-gray-200 dark:ring-white/10" style="width: 140px;">
        <i data-lucide="{{ $icon }}" class="w-3.5 h-3.5 flex-shrink-0" style="color: {{ $color }};"></i>
        <span class="truncate" style="color: {{ $color }};">{{ $state }}</span>
    </div>
</div>
