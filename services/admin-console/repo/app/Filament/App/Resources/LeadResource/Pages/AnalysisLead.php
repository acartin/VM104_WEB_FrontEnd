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
use Filament\Infolists\Components\Group;
 
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
                Grid::make(10)
                    ->schema([
                        // Left Column (40%) - Stacked Sections
                        Grid::make(1)
                            ->schema([
                                Section::make('')
                                    ->compact()
                                    ->schema([
                                        // Property Value at the very top
                                        TextEntry::make('estimated_value')
                                            ->label('')
                                            ->money('USD')
                                            ->icon('heroicon-m-home')
                                            ->iconColor('primary')
                                            ->weight('bold')
                                            ->size('lg')
                                            ->columnSpanFull()
                                            ->visible(fn ($record) => $record->estimated_value > 0)
                                            ->extraAttributes([
                                                'title' => 'Estimated Property Value',
                                                'class' => 'property-value-large',
                                            ]),
                                        
                                        // Header Row: Gauge + Big Number
                                        ViewEntry::make('score_gauge')
                                            ->label('')
                                            ->view('filament.app.resources.lead-resource.infolists.lead-score-gauge'),

                                        // Profile Data (Integrated, no separator)
                                        Grid::make(1)
                                            ->schema([
                                                
                                                TextEntry::make('full_name')
                                                    ->label('')
                                                    ->icon('heroicon-m-user')
                                                    ->iconColor('gray')
                                                    ->weight('bold')
                                                    ->columnSpanFull()
                                                    ->extraAttributes(['title' => 'Full Name']),
                                                
                                                TextEntry::make('email')
                                                    ->label('')
                                                    ->icon('heroicon-m-envelope')
                                                    ->iconColor('gray')
                                                    ->size('sm')
                                                    ->copyable()
                                                    ->columnSpanFull()
                                                    ->extraAttributes(['title' => 'Email Address']),
                                                
                                                TextEntry::make('phone')
                                                    ->label('')
                                                    ->icon('heroicon-m-phone')
                                                    ->iconColor('gray')
                                                    ->size('sm')
                                                    ->columnSpanFull()
                                                    ->extraAttributes(['title' => 'Phone Number']),

                                                TextEntry::make('declared_income')
                                                    ->label('')
                                                    ->money('USD')
                                                    ->icon('heroicon-m-banknotes')
                                                    ->iconColor('success')
                                                    ->weight('medium')
                                                    ->columnSpanFull()
                                                    ->extraAttributes(['title' => 'Declared Annual Income']),
                                                
                                                TextEntry::make('current_debts')
                                                    ->label('')
                                                    ->money('USD')
                                                    ->icon('heroicon-m-credit-card')
                                                    ->iconColor('danger')
                                                    ->weight('medium')
                                                    ->columnSpanFull()
                                                    ->extraAttributes(['title' => 'Current Total Debts']),
                                                

                                            ]),
                                    ]),
                                
                                Section::make('Attribute Breakdown')
                                    ->icon('heroicon-m-chart-bar')
                                    ->compact()
                                    ->schema([
                                        ViewEntry::make('thermal_stats')
                                            ->label('')
                                            ->view('filament.app.resources.lead-resource.infolists.lead-thermal-stats'),
                                    ]),
                            ])
                            ->columnSpan(4),

                        // Right Column (60%)
                        Group::make()
                            ->schema([
                                // 1. AI Analysis & Insights
                                Section::make('AI Intelligence')
                                    ->icon('heroicon-m-sparkles')
                                    ->description('Resumen ejecutivo y análisis de sentimiento generado por el Bot.')
                                    ->schema([
                                        Grid::make(1)
                                            ->schema([
                                                TextEntry::make('sentiment_badge')
                                                    ->label('Sentimiento')
                                                    ->inlineLabel()
                                                    ->badge()
                                                    ->getStateUsing(fn ($record) => $record->conversations()->latest('last_message_at')->first()?->sentiment)
                                                    ->color(fn ($state) => match($state) {
                                                        'positive' => 'success',
                                                        'negative' => 'danger',
                                                        default => 'gray',
                                                    })
                                                    ->placeholder('Pendiente'),
                                            ]),

                                        TextEntry::make('ai_summary')
                                            ->label('')
                                            ->getStateUsing(fn ($record) => $record->conversations()->latest('last_message_at')->first()?->summary)
                                            ->prose()
                                            ->placeholder('Esperando resumen de la conversación del Bot...'),
                                    ])
                                    ->compact(),

                                // 2. Marketing Attribution (UTM Data)
                                Section::make('Atribución de Marketing')
                                    ->icon('heroicon-m-megaphone')
                                    ->compact()
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                ViewEntry::make('source.name')
                                                    ->label('Canal de Captación')
                                                    ->view('filament.app.resources.lead-resource.infolists.result-source-lucide')
                                                    ->columnSpan(1),

                                                TextEntry::make('utm_source')
                                                    ->label('UTM Source')
                                                    ->placeholder('-'),

                                                TextEntry::make('utm_medium')
                                                    ->label('Medio (Medium)')
                                                    ->placeholder('-'),

                                                TextEntry::make('utm_campaign')
                                                    ->label('Campaña')
                                                    ->weight('bold')
                                                    ->placeholder('-'),

                                                TextEntry::make('utm_content')
                                                    ->label('Contenido / Anuncio')
                                                    ->size('sm')
                                                    ->placeholder('-'),

                                                TextEntry::make('click_id')
                                                    ->label('ID de Click')
                                                    ->size('xs')
                                                    ->fontFamily('mono')
                                                    ->copyable()
                                                    ->placeholder('-'),
                                            ]),
                                    ]),

                                // 3. Property Interest Detail
                                Section::make('Propiedad de Interés')
                                    ->icon('heroicon-m-home-modern')
                                    ->compact()
                                    ->schema([
                                        TextEntry::make('source_property_url')
                                            ->label('URL de Origen')
                                            ->url(fn ($state) => $state, true)
                                            ->color('primary')
                                            ->size('sm')
                                            ->placeholder('URL no disponible'),

                                        TextEntry::make('property_snapshot.title')
                                            ->label('Título de la Propiedad')
                                            ->columnSpanFull()
                                            ->weight('bold')
                                            ->placeholder('-'),

                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('property_snapshot.bedrooms')
                                                    ->label('Habitaciones')
                                                    ->placeholder('-'),
                                                TextEntry::make('property_snapshot.bathrooms')
                                                    ->label('Baños')
                                                    ->placeholder('-'),
                                                TextEntry::make('property_snapshot.area_sqm')
                                                    ->label('Área (m²)')
                                                    ->placeholder('-'),
                                            ]),
                                    ])
                                    ->visible(fn ($record) => $record->source_property_ref || $record->source_property_url),

                                // 4. Recent Conversation Snippet
                                Section::make('Conversación Reciente')
                                    ->icon('heroicon-m-chat-bubble-left-right')
                                    ->compact()
                                    ->schema([
                                        TextEntry::make('last_message_preview')
                                            ->label('')
                                            ->getStateUsing(fn ($record) => $record->conversations()->latest('last_message_at')->first()?->getLastMessage()['text'])
                                            ->icon('heroicon-m-chat-bubble-bottom-center-text')
                                            ->iconColor('gray')
                                            ->prose()
                                            ->placeholder('Sin mensajes recientes'),
                                        
                                        TextEntry::make('last_interaction')
                                            ->label('')
                                            ->getStateUsing(function ($record) {
                                                $lastConv = $record->conversations()->latest('last_message_at')->first();
                                                if (!$lastConv) return null;
                                                return "Última interacción: " . $lastConv->last_message_at->diffForHumans();
                                            })
                                            ->size('xs')
                                            ->color('gray')
                                            ->visible(fn ($record) => $record->conversations()->exists()),
                                    ]),
                            ])
                            ->columnSpan(6),
                    ])
             ]);
     }
 }
