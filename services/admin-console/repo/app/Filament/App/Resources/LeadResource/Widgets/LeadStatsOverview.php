<?php

namespace App\Filament\App\Resources\LeadResource\Widgets;

use App\Models\Lead;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class LeadStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $tenantId = \Filament\Facades\Filament::getTenant()->id;

        return [
            Stat::make('New Leads Today', Lead::where('client_id', $tenantId)
                    ->whereDate('created_at', today())
                    ->count())
                ->description('Created today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Hot Leads', Lead::where('client_id', $tenantId)
                    ->where('score_total', '>', 80)
                    ->count())
                ->description('Score > 80 pts')
                ->descriptionIcon('heroicon-m-fire')
                ->color('danger')
                ->url(route('filament.app.resources.leads.index', [
                    'tenant' => \Filament\Facades\Filament::getTenant()->slug,
                    'tableSortColumn' => 'score_total',
                    'tableSortDirection' => 'desc',
                ])),

            Stat::make('In Chat', Lead::where('client_id', $tenantId)
                    ->whereHas('contactPreference', fn($q) => $q->where('slug', 'chat_msg'))
                    ->count())
                ->description('Messenger/WhatsApp')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('info')
                ->url($this->getFilteredUrl('chat_msg')),

            Stat::make('Video Calls', Lead::where('client_id', $tenantId)
                    ->whereHas('contactPreference', fn($q) => $q->where('slug', 'video_call'))
                    ->count())
                ->description('Ready for call')
                ->descriptionIcon('heroicon-m-video-camera')
                ->color('primary')
                ->url($this->getFilteredUrl('video_call')),

            Stat::make('Meetings', Lead::where('client_id', $tenantId)
                    ->whereHas('contactPreference', fn($q) => $q->whereIn('slug', ['meeting_pending', 'meeting_confirmed']))
                    ->count())
                ->description('Pending + Confirmed')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning')
                ->url($this->getFilteredUrl('meeting_pending', 'meeting_confirmed')),

            Stat::make('Avg Score', number_format(
                    Lead::where('client_id', $tenantId)->avg('score_total') ?? 0, 
                    0
                ) . ' pts')
                ->description('Overall quality')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('gray'),
        ];
    }

    protected function getFilteredUrl(...$slugs): string
    {
        $preferenceIds = \App\Models\ContactPreference::whereIn('slug', $slugs)->pluck('id')->toArray();
        
        return route('filament.app.resources.leads.index', [
            'tenant' => \Filament\Facades\Filament::getTenant()->slug,
            'tableFilters' => [
                'intent_filter' => [
                    'intent_ids' => $preferenceIds
                ]
            ]
        ]);
    }

    protected function getStatusFilteredUrl(...$slugs): string
    {
        $statusIds = \App\Models\LeadStatus::whereIn('slug', $slugs)->pluck('id')->toArray();
        
        return route('filament.app.resources.leads.index', [
            'tenant' => \Filament\Facades\Filament::getTenant()->slug,
            'tableFilters' => [
                'status_filter' => [
                    'status_ids' => $statusIds
                ]
            ]
        ]);
    }
}
