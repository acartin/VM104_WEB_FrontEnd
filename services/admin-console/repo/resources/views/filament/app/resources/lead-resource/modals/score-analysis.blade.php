<div class="space-y-6">
    <!-- Global Performance -->
    <div class="grid grid-cols-2 gap-4">
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">Global Performance</h3>
            <div class='flex items-center gap-4 text-gray-900 dark:text-gray-100'>
                <div class='text-5xl font-black {{ $record->eng_color }}'>{{ $record->score_total }}</div>
                <div class='text-xs text-gray-400 uppercase tracking-widest leading-tight'>Qualified<br>Index</div>
            </div>
        </div>

        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-center">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">Market Timeline</h3>
            <div class='p-3 rounded-lg border-2 border-dashed flex items-center justify-center font-black text-lg {{ $record->timeline_color }}'>
                {{ $record->timeline_label }}
            </div>
        </div>
    </div>

    <!-- Thermal Breakdown -->
    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-50 dark:border-gray-700 pb-2">Thermal Breakdown</h3>
        
        <div class="grid gap-6">
            <!-- Engagement -->
            <div class='flex items-center justify-between text-gray-700 dark:text-gray-300'>
                <div class='flex items-center gap-3'>
                    <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-900 {{ $record->eng_color }}">
                        <i data-lucide='message-square-heart' class='w-5 h-5'></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold">Active Interaction</p>
                        <p class="text-[10px] text-gray-400">Response speed and chat health</p>
                    </div>
                </div>
                <span class='text-lg font-black'>{{ $record->score_engagement }} <span class="text-[10px] text-gray-400 font-medium">pts</span></span>
            </div>

            <!-- Finance -->
            <div class='flex items-center justify-between text-gray-700 dark:text-gray-300'>
                <div class='flex items-center gap-3'>
                    <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-900 {{ $record->fin_color }}">
                        <i data-lucide='{{ $record->fin_icon }}' class='w-5 h-5'></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold">Purchasing Power</p>
                        <p class="text-[10px] text-gray-400">Financial stability and budget match</p>
                    </div>
                </div>
                <span class='text-lg font-black'>{{ $record->score_finance }} <span class="text-[10px] text-gray-400 font-medium">pts</span></span>
            </div>

            <!-- Inventory Match -->
            <div class='flex items-center justify-between text-gray-700 dark:text-gray-300'>
                <div class='flex items-center gap-3'>
                    <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-900 {{ $record->match_color }}">
                        <i data-lucide='house-heart' class='w-5 h-5'></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold">Property Match</p>
                        <p class="text-[10px] text-gray-400">Alignment with available inventory</p>
                    </div>
                </div>
                <span class='text-lg font-black'>{{ $record->score_match }} <span class="text-[10px] text-gray-400 font-medium">pts</span></span>
            </div>

            <!-- Info Quality -->
            <div class='flex items-center justify-between text-gray-700 dark:text-gray-300'>
                <div class='flex items-center gap-3'>
                    <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-900 {{ $record->info_color }}">
                        <i data-lucide='message-square-text' class='w-5 h-5'></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold">Data Quality</p>
                        <p class="text-[10px] text-gray-400">Completeness of contact information</p>
                    </div>
                </div>
                <span class='text-lg font-black'>{{ $record->score_info }} <span class="text-[10px] text-gray-400 font-medium">pts</span></span>
            </div>
        </div>
    </div>
</div>
