<?php
 
 namespace App\Filament\App\Resources\LeadResource\Pages;
 
 use App\Filament\App\Resources\LeadResource;
 use App\Models\Lead;
 use Filament\Resources\Pages\Page;
 use Filament\Infolists\Infolist;
 use Filament\Infolists\Components\Section;
 use Filament\Infolists\Components\TextEntry;
 use Filament\Infolists\Components\ViewEntry;
 use Filament\Infolists\Components\Grid;
 
 class AnalysisLead extends Page
 {
     protected static string $resource = LeadResource::class;
 
     protected static string $view = 'filament.app.resources.lead-resource.pages.analysis-lead';
 
     public Lead $record;
 
     public function mount($record): void
     {
         if ($record instanceof Lead) {
             $this->record = $record;
         } else {
             $this->record = Lead::findOrFail($record);
         }
     }
 
     public function getTitle(): string
     {
         return "Scoring Analysis: " . $this->record->full_name;
     }
 
     public function leadInfolist(Infolist $infolist): Infolist
     {
         return $infolist
             ->record($this->record)
             ->schema([
                 Grid::make(3)
                     ->schema([
                         Section::make('Overall Rating')
                             ->schema([
                                 TextEntry::make('score_total')
                                     ->label('Index')
                                     ->weight('black')
                                     ->size('4xl')
                                     ->extraAttributes(fn ($record) => ['class' => $record->eng_color]),
                                 TextEntry::make('timeline_label')
                                     ->label('Market Timeline')
                                     ->extraAttributes(fn ($record) => [
                                         'class' => $record->timeline_color . " t-badge-base !text-xl !py-8 !w-auto !h-auto !min-w-[200px] shadow-sm"
                                     ]),
                             ])->columnSpan(1),
                         Section::make('Score Map')
                             ->schema([
                                 ViewEntry::make('thermal_breakdown')
                                     ->view('filament.app.resources.lead-resource.modals.score-analysis-infolist'),
                             ])->columnSpan(2),
                     ])
             ]);
     }
 }
