<x-filament-panels::page>
    {{ $this->leadInfolist($this->makeInfolist()) }}
    
    <div class="mt-6 flex gap-4">
        <x-filament::button
            href="{{ \App\Filament\App\Resources\LeadResource::getUrl('index') }}"
            tag="a"
            color="gray"
        >
            Back to List
        </x-filament::button>

        <x-filament::button
            href="{{ \App\Filament\App\Resources\LeadResource::getUrl('edit', ['record' => $record]) }}"
            tag="a"
            color="gray"
        >
            Edit Lead Details
        </x-filament::button>

        <x-filament::button
            href="{{ \App\Filament\App\Resources\LeadResource::getUrl('audit', ['record' => $record]) }}"
            tag="a"
            color="primary"
            icon="heroicon-m-chat-bubble-left-right"
        >
            Chat Audit
        </x-filament::button>
    </div>
</x-filament-panels::page>
