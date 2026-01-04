@php
    $conversations = $record->conversations()->latest('last_message_at')->get();
    $activeConv = $conversations->first();
    $messages = $activeConv->messages ?? [];
@endphp

<x-filament-panels::page>
    <div class="flex items-center justify-between gap-3 mb-2">
        <x-filament::button
            color="gray"
            icon="heroicon-m-chevron-left"
            size="sm"
            onclick="window.history.back()"
            tag="button"
        >
            Back
        </x-filament::button>

        <div class="flex gap-3">
            <x-filament::button
                href="{{ \App\Filament\App\Resources\LeadResource::getUrl('index') }}"
                tag="a"
                color="gray"
                variant="link"
                size="sm"
            >
                Lead List
            </x-filament::button>
            <x-filament::button
                href="{{ \App\Filament\App\Resources\LeadResource::getUrl('analysis', ['record' => $record]) }}"
                tag="a"
                color="gray"
                variant="link"
                size="sm"
            >
                Scoring Analysis
            </x-filament::button>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6 items-start">
        
        <!-- CARD 1: LEAD DATA -->
        <x-filament::section icon="heroicon-m-identification">
            <x-slot name="heading">Lead Information</x-slot>

            <div class="space-y-6">
                <!-- Profile Mini-Header -->
                <div class="py-2">
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="font-bold text-lg dark:text-white leading-tight">{{ $record->full_name }}</h3>
                    </div>
                    @if($record->leadStatus)
                        <span class="text-[10px] bg-gray-100 dark:bg-white/5 text-gray-500 px-2 py-0.5 rounded font-bold uppercase tracking-wider">
                            {{ $record->leadStatus->name }}
                        </span>
                    @endif
                </div>

                <!-- Contact List -->
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                        <span class="text-sm dark:text-gray-300">{{ $record->phone ?? 'No phone' }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <i data-lucide="mail" class="w-4 h-4 text-gray-400"></i>
                        <span class="text-sm dark:text-gray-300 truncate">{{ $record->email ?? 'No email' }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <i data-lucide="hash" class="w-4 h-4 text-gray-400"></i>
                        <span class="text-sm dark:text-gray-300">{{ $record->source?->name ?? 'Web Direct' }}</span>
                    </div>
                </div>

                <!-- Property Snapshot -->
                @if($record->property_snapshot)
                <div class="pt-4 border-t border-gray-100 dark:border-white/5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-2">Property Interest</p>
                    <div class="bg-gray-50 dark:bg-white/5 p-3 rounded-lg">
                        <p class="text-sm font-bold dark:text-white leading-snug">{{ $record->property_snapshot['title'] ?? 'Property' }}</p>
                        <p class="text-[11px] text-gray-500 mt-1">
                            {{ ($record->property_snapshot['bedrooms'] ?? '?') }} Bed • {{ ($record->property_snapshot['bathrooms'] ?? '?') }} Bath
                        </p>
                    </div>
                </div>
                @endif
                
                <!-- Metrics -->
                <div class="pt-4 border-t border-gray-100 dark:border-white/5 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Sentiment</span>
                        @php
                            $sentMap = [
                                'positive' => ['label' => 'Positive', 'color' => 'success'],
                                'negative' => ['label' => 'Negative', 'color' => 'danger'],
                                'neutral' => ['label' => 'Neutral', 'color' => 'gray'],
                            ];
                            $sent = $sentMap[$activeConv?->sentiment] ?? $sentMap['neutral'];
                        @endphp
                        <x-filament::badge :color="$sent['color']" size="sm">{{ $sent['label'] }}</x-filament::badge>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Total Messages</span>
                        <div class="fi-in-text">{{ $activeConv?->total_messages ?? 0 }}</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Duration</span>
                        <div class="fi-in-text">
                            {{ $activeConv?->started_at && $activeConv?->ended_at ? $activeConv->started_at->diffInMinutes($activeConv->ended_at) . 'm' : '-' }}
                        </div>
                    </div>
                </div>

                <!-- Quick Action Buttons -->
                <div class="pt-6 border-t border-gray-100 dark:border-white/5 space-y-6">
                    
                    <!-- WORKFLOW MANAGEMENT -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Workflow Status</p>
                            <span class="text-[9px] text-gray-400 italic">
                                Idle: {{ ($record->updated_at instanceof \Carbon\Carbon) ? $record->updated_at->diffForHumans(null, true) : 'No action' }}
                            </span>
                        </div>
                        
                        <div class="flex flex-wrap gap-2">
                            @foreach(\App\Models\LeadStatus::orderBy('order')->get() as $status)
                                <button 
                                    wire:click="updateStatus('{{ $status->id }}')"
                                    @class([
                                        'flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all border',
                                        'ring-2 ring-primary-500/20 border-primary-500 bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400' => $record->status_id === $status->id,
                                        'border-gray-200 dark:border-white/5 bg-white dark:bg-white/5 text-gray-500 hover:border-gray-300 dark:hover:border-white/10' => $record->status_id !== $status->id,
                                    ])
                                >
                                    <i data-lucide="{{ $status->icon }}" class="w-3.5 h-3.5" x-data x-init="lucide.createIcons({ root: $el })"></i>
                                    {{ $status->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-3">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Quick Actions</p>
                    
                    <!-- NEW: Intent Highlight Block -->
                    @if($record->contactPreference && $record->contactPreference->slug !== 'none')
                        <div @class([
                            'mb-4 p-3 rounded-lg border flex flex-col gap-1',
                            'bg-success-50/50 border-success-100 dark:bg-success-500/10 dark:border-success-500/20' => $record->contactPreference->color === 'success',
                            'bg-info-50/50 border-info-100 dark:bg-info-500/10 dark:border-info-500/20' => $record->contactPreference->color === 'info',
                            'bg-warning-50/50 border-warning-100 dark:bg-warning-500/10 dark:border-warning-500/20' => $record->contactPreference->color === 'warning',
                            'bg-danger-50/50 border-danger-100 dark:bg-danger-500/10 dark:border-danger-500/20' => $record->contactPreference->color === 'danger',
                            'bg-primary-50/50 border-primary-100 dark:bg-primary-500/10 dark:border-primary-500/20' => $record->contactPreference->color === 'primary',
                            'bg-gray-50 dark:bg-white/5 border-gray-100 dark:border-white/5' => $record->contactPreference->color === 'gray',
                        ])>
                            <span class="text-[9px] font-bold uppercase tracking-tighter opacity-70">Recommended follow-up</span>
                            <div class="flex items-center gap-2">
                                <i 
                                    data-lucide="{{ $record->contactPreference->icon }}"
                                    x-data x-init="lucide.createIcons({ root: $el })"
                                    @class([
                                        'w-4 h-4',
                                        'text-success-600 dark:text-success-400' => $record->contactPreference->color === 'success',
                                        'text-info-600 dark:text-info-400' => $record->contactPreference->color === 'info',
                                        'text-warning-600 dark:text-warning-400' => $record->contactPreference->color === 'warning',
                                        'text-danger-600 dark:text-danger-400' => $record->contactPreference->color === 'danger',
                                        'text-primary-600 dark:text-primary-400' => $record->contactPreference->color === 'primary',
                                        'text-gray-500' => $record->contactPreference->color === 'gray',
                                    ])
                                ></i>
                                <span class="text-xs font-bold dark:text-white">{{ $record->contactPreference->name }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3">
                        <x-filament::button
                            href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $record->phone ?? '') }}"
                            tag="a" target="_blank" color="success" icon="heroicon-m-chat-bubble-left-right" size="sm" class="w-full"
                            :variant="($record->contactPreference?->slug === 'chat_msg') ? 'filled' : 'outline'"
                        >WhatsApp</x-filament::button>

                        <x-filament::button
                            href="tel:{{ $record->phone }}"
                            tag="a" color="info" icon="heroicon-m-phone" size="sm" class="w-full"
                            :variant="($record->contactPreference?->slug === 'voice_call') ? 'filled' : 'outline'"
                        >Call</x-filament::button>

                        <x-filament::button
                            href="mailto:{{ $record->email }}"
                            tag="a" color="gray" icon="heroicon-m-envelope" size="sm" class="w-full"
                            :variant="($record->contactPreference?->slug === 'email_info') ? 'filled' : 'outline'"
                        >Email</x-filament::button>

                        <x-filament::button
                            color="gray" icon="heroicon-m-calendar" size="sm" class="w-full"
                            tooltip="Schedule Appointment"
                            :color="in_array($record->contactPreference?->slug, ['meeting_pending', 'meeting_confirmed']) ? 'warning' : 'gray'"
                            :variant="in_array($record->contactPreference?->slug, ['meeting_pending', 'meeting_confirmed']) ? 'filled' : 'outline'"
                        >Schedule</x-filament::button>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- CARD 2: CHAT AUDIT -->
        <x-filament::section icon="heroicon-m-chat-bubble-left-right" class="h-full">
            <x-slot name="heading">Chat Audit</x-slot>

            <div class="h-[600px] overflow-y-auto pr-4 custom-scrollbar space-y-4 p-6 rounded-xl chat-background shadow-inner border border-gray-100 dark:border-white/5">
                @if(empty($messages))
                    <div class="flex flex-col items-center justify-center h-full opacity-30 text-gray-400">
                        <i data-lucide="message-square-off" class="w-10 h-10 mb-2"></i>
                        <p class="text-sm italic">No messages found</p>
                    </div>
                @else
                    @foreach($messages as $msg)
                        @php
                            $isLead = ($msg['sender'] ?? '') === 'lead';
                        @endphp
                        <div @class([
                            'flex flex-col mb-4',
                            'items-end' => $isLead,
                            'items-start' => !$isLead
                        ])>
                            <span class="text-[9px] font-bold uppercase opacity-40 mb-1 flex items-center gap-1">
                                <i data-lucide="{{ $isLead ? 'user' : 'bot' }}" class="w-2.5 h-2.5"></i>
                                {{ $isLead ? 'Lead' : 'Smart Bot' }}
                            </span>
                            <div @class([
                                'px-4 py-3 rounded-2xl text-sm max-w-[85%] shadow-sm transition-all hover:shadow-md',
                                'bg-white dark:bg-gray-800 dark:text-gray-200 border border-gray-100 dark:border-white/10 rounded-tl-none' => !$isLead,
                                'bg-primary-600 text-white rounded-tr-none' => $isLead
                            ])>
                                {{ $msg['text'] ?? '' }}
                                <div @class([
                                    'text-[8px] mt-1.5 opacity-60 block text-right font-medium',
                                    'text-white/80' => $isLead,
                                    'text-gray-500' => !$isLead
                                ])>
                                    {{ isset($msg['timestamp']) ? \Carbon\Carbon::parse($msg['timestamp'])->format('H:i') : '' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </x-filament::section>
    </div>

    <style>
        .chat-background {
            background-color: #f8fafc;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 20px 20px;
            position: relative;
        }

        .dark .chat-background {
            background-color: #1a1d21;
            background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
        }

        .chat-background::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at center, transparent 0%, rgba(0,0,0,0.02) 100%);
            pointer-events: none;
        }

        .dark .chat-background::after {
            background: radial-gradient(circle at center, transparent 0%, rgba(0,0,0,0.1) 100%);
        }

        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.08); border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.05); }
    </style>

    <script>
        if (window.lucide) {
            window.lucide.createIcons();
        }
    </script>
</x-filament-panels::page>
