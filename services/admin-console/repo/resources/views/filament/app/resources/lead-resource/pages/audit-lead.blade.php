<x-filament-panels::page>
    <div class="space-y-6">
        <header class="flex items-center justify-between">
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Chat Audit</h1>
        </header>

        <section class="p-10 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 bg-success-50 dark:bg-success-900/20 rounded-full flex items-center justify-center mb-4">
                <i data-lucide="messages-square" class="w-8 h-8 text-success-600"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Audit View Under Construction</h2>
            <p class="text-gray-500 max-w-sm">Here we will show the full chat history and the logic behind the automated qualification criteria.</p>
        </section>

        <div class="flex gap-4">
            <x-filament::button
                href="{{ \App\Filament\App\Resources\LeadResource::getUrl('index') }}"
                tag="a"
                color="gray"
            >
                Back to List
            </x-filament::button>

            <x-filament::button
                href="{{ \App\Filament\App\Resources\LeadResource::getUrl('analysis', ['record' => $record]) }}"
                tag="a"
                color="info"
            >
                View Scoring Analysis
            </x-filament::button>
        </div>
    </div>
    
    <script>
        if (window.lucide) {
            window.lucide.createIcons();
        }
    </script>
</x-filament-panels::page>
